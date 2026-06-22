<?php
require_once dirname(__DIR__) . '/config/conexion.php';

class M_Kardex {
    private static $instancia = null;
    private $conexion;

    private function __construct() {
        $this->conexion = Conexion::singleton()->getConexion();
    }

    public static function singleton() {
        if (!isset(self::$instancia)) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    /**
     * Returns all active insumos with their category and unit for the product selector.
     */
    public function listarInsumos() {
        try {
            $sql = "SELECT i.id_insumo, i.nombre, c.nombre AS categoria, u.abreviatura,
                           i.precio_unitario, i.stock
                    FROM insumos i
                    INNER JOIN categorias c ON i.id_categoria = c.id_categoria
                    INNER JOIN unidades_medida u ON i.id_unidad = u.id_unidad
                    WHERE i.estado = 1
                    ORDER BY c.nombre, i.nombre";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Returns the Kardex movement lines for a given insumo.
     * Each line contains: fecha, tipo_doc, numero_doc, concepto,
     *   entrada_cant, entrada_cu, entrada_ct,
     *   salida_cant, salida_cu, salida_ct,
     *   saldo_cant, saldo_cu, saldo_ct
     *
     * Movements come from detalle_ventas (salidas).
     * For a complete system we'd also have compras/ajustes,
     * but we simulate an initial "Saldo Inicial" based on current stock + sold quantities.
     */
    public function obtenerMovimientos($id_insumo, $desde = null, $hasta = null, $tipo = 'todos', $busqueda = '') {
        try {
            // Build base params
            $params = [$id_insumo];

            // Fecha filter for VENTAS
            $fechaWhere = '';
            if ($desde) {
                $fechaWhere .= ' AND v.fecha >= ?';
                $params[] = $desde . ' 00:00:00';
            }
            if ($hasta) {
                $fechaWhere .= ' AND v.fecha <= ?';
                $params[] = $hasta . ' 23:59:59';
            }

            // Search filter
            $busquedaWhere = '';
            if ($busqueda) {
                $busquedaWhere = ' AND (v.id_venta LIKE ? OR p.nombres_razon_social LIKE ?)';
                $params[] = '%' . $busqueda . '%';
                $params[] = '%' . $busqueda . '%';
            }

            // Get all sale movements (salidas) for the insumo
            $sql = "SELECT
                        v.fecha,
                        CASE v.tipo_comprobante
                            WHEN 1 THEN 'Boleta'
                            WHEN 2 THEN 'Factura'
                            WHEN 3 THEN 'Nota de Venta'
                            ELSE 'Comprobante'
                        END AS tipo_doc,
                        LPAD(v.id_venta, 6, '0') AS numero_doc,
                        CONCAT('Venta a ', p.nombres_razon_social, IFNULL(CONCAT(' ', p.apellidos), '')) AS concepto,
                        dv.cantidad AS salida_cant,
                        dv.precio_venta AS costo_unit,
                        dv.subtotal AS salida_ct,
                        'salida' AS tipo_movimiento
                    FROM detalle_ventas dv
                    INNER JOIN ventas v ON dv.id_venta = v.id_venta
                    INNER JOIN clientes cl ON v.id_cliente = cl.id_cliente
                    INNER JOIN personas p ON cl.id_persona = p.id_persona
                    WHERE dv.id_insumo = ?
                      AND v.estado = 1
                      {$fechaWhere}
                      {$busquedaWhere}
                    ORDER BY v.fecha ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($params);
            $salidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get insumo base info
            $infoStmt = $this->conexion->prepare(
                "SELECT i.nombre, c.nombre AS categoria, u.abreviatura, i.precio_unitario, i.stock
                 FROM insumos i
                 INNER JOIN categorias c ON i.id_categoria = c.id_categoria
                 INNER JOIN unidades_medida u ON i.id_unidad = u.id_unidad
                 WHERE i.id_insumo = ?"
            );
            $infoStmt->execute([$id_insumo]);
            $insumo = $infoStmt->fetch(PDO::FETCH_ASSOC);

            if (!$insumo) return ['error' => 'Insumo no encontrado'];

            // Calculate total sold quantity to derive initial stock
            $totalVendido = array_sum(array_column($salidas, 'salida_cant'));
            $stockActual = floatval($insumo['stock']);
            $stockInicial = $stockActual + $totalVendido;
            $precioUnit = floatval($insumo['precio_unitario']);

            // Build Kardex rows with running balance
            $rows = [];
            $saldoCant = 0;

            // Only show initial entry if not filtering by type = 'salida'
            if ($tipo !== 'salida' && !$desde && !$busqueda) {
                $saldoCant = $stockInicial;
                $rows[] = [
                    'fecha'         => null,
                    'tipo_doc'      => 'Saldo Inicial',
                    'numero_doc'    => '—',
                    'concepto'      => 'Stock de apertura',
                    'entrada_cant'  => $stockInicial,
                    'entrada_cu'    => $precioUnit,
                    'entrada_ct'    => $stockInicial * $precioUnit,
                    'salida_cant'   => null,
                    'salida_cu'     => null,
                    'salida_ct'     => null,
                    'saldo_cant'    => $saldoCant,
                    'saldo_cu'      => $precioUnit,
                    'saldo_ct'      => $saldoCant * $precioUnit,
                    'tipo_movimiento' => 'entrada',
                ];
            } else {
                // If filtering, we need to compute the saldo before the filtered period
                $saldoCant = $stockInicial;
                // Subtract only the filtered salidas from initial to get pre-filter balance
                // Actually just start from initial and let the loop handle it
            }

            // Apply movements
            foreach ($salidas as $mov) {
                if ($tipo === 'entrada') continue; // Skip salidas if only showing entradas

                $cant = floatval($mov['salida_cant']);
                $cu   = floatval($mov['costo_unit']);
                $saldoCant -= $cant;

                $rows[] = [
                    'fecha'         => $mov['fecha'],
                    'tipo_doc'      => $mov['tipo_doc'],
                    'numero_doc'    => 'V-' . $mov['numero_doc'],
                    'concepto'      => $mov['concepto'],
                    'entrada_cant'  => null,
                    'entrada_cu'    => null,
                    'entrada_ct'    => null,
                    'salida_cant'   => $cant,
                    'salida_cu'     => $cu,
                    'salida_ct'     => $mov['salida_ct'],
                    'saldo_cant'    => $saldoCant,
                    'saldo_cu'      => $cu,
                    'saldo_ct'      => $saldoCant * $cu,
                    'tipo_movimiento' => 'salida',
                ];
            }

            // Summary stats
            $totalEntradaCant = $stockInicial; // All stock came in at some point
            $totalSalidaCant  = $totalVendido;

            return [
                'insumo'           => $insumo,
                'rows'             => $rows,
                'total_entrada_cant' => $totalEntradaCant,
                'total_salida_cant'  => $totalSalidaCant,
                'stock_actual'       => $stockActual,
                'precio_unitario'    => $precioUnit,
            ];

        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
?>
