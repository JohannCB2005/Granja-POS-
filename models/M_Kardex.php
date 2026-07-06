<?php
// Requerir archivo de conexión centralizada
require_once dirname(__DIR__) . '/config/conexion.php';

/**
 * Modelo para la gestión y control del Kardex (Movimientos de Inventario)
 * Calcula y genera el historial de entradas, salidas y saldos en vivo de cada producto del inventario.
 */
class M_Kardex {
    // Instancia Singleton
    private static $instancia = null;
    // Conexión PDO
    private $conexion;

    // Constructor privado
    private function __construct() {
        $this->conexion = Conexion::singleton()->getConexion();
    }

    // Obtener la instancia única del modelo
    public static function singleton() {
        if (!isset(self::$instancia)) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    /**
     * Retorna todos los insumos activos con sus categorías y unidades para llenar el selector de productos en la UI del Kardex
     * @return array Listado asociativo de productos activos
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
     * Obtiene el historial de movimientos de inventario (Kardex) para un insumo específico.
     * Calcula la sumatoria histórica para reconstruir el saldo inicial del insumo hacia atrás.
     * Cada fila resultante contiene: fecha, tipo_doc, numero_doc, concepto,
     *   entrada_cant, entrada_cu, entrada_ct,
     *   salida_cant, salida_cu, salida_ct,
     *   saldo_cant, saldo_cu, saldo_ct
     * 
     * Los movimientos de salida se extraen de la tabla detalle_ventas.
     * 
     * @param int $id_insumo ID del insumo a analizar
     * @param string|null $desde Fecha de inicio del filtro
     * @param string|null $hasta Fecha de fin del filtro
     * @param string $tipo Tipo de movimiento a filtrar ('entrada', 'salida', 'todos')
     * @param string $busqueda Palabra clave para buscar por vendedor, cliente o código
     * @return array Historial detallado del Kardex y estadísticas de stock
     */
    public function obtenerMovimientos($id_insumo, $desde = null, $hasta = null, $tipo = 'todos', $busqueda = '') {
        try {
            // Inicializar parámetros de la consulta con el ID del insumo
            $params = [$id_insumo];

            // Generar filtros de fecha dinámicos en las consultas
            $fechaWhere = '';
            if ($desde) {
                $fechaWhere .= ' AND v.fecha >= ?';
                $params[] = $desde . ' 00:00:00';
            }
            if ($hasta) {
                $fechaWhere .= ' AND v.fecha <= ?';
                $params[] = $hasta . ' 23:59:59';
            }

            // Generar filtro de búsqueda textual dinámico
            $busquedaWhere = '';
            if ($busqueda) {
                $busquedaWhere = ' AND (v.id_venta LIKE ? OR p.nombres_razon_social LIKE ?)';
                $params[] = '%' . $busqueda . '%';
                $params[] = '%' . $busqueda . '%';
            }

            // 1. Consultar todos los movimientos de venta (salidas de stock) para este insumo
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

            // 2. Obtener los datos básicos de stock y unidad del insumo
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

            // 3. Reconstruir stock: Sumar todo lo vendido al stock actual para obtener el stock inicial (Apertura)
            $totalVendido = array_sum(array_column($salidas, 'salida_cant'));
            $stockActual = floatval($insumo['stock']);
            $stockInicial = $stockActual + $totalVendido;
            $precioUnit = floatval($insumo['precio_unitario']);

            // 4. Construir las filas del Kardex con balances acumulativos
            $rows = [];
            $saldoCant = 0;

            // Registrar fila de Saldo Inicial si no se está filtrando específicamente por salidas, búsquedas o fechas específicas
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
                // Si hay filtros activos, el saldo inicial se calcula igual para inicializar la cuenta acumulada
                $saldoCant = $stockInicial;
            }

            // 5. Procesar e insertar las salidas (ventas) restándolas del acumulado
            foreach ($salidas as $mov) {
                if ($tipo === 'entrada') continue; // Omitir si se filtra solo por entradas

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

            // Estadísticas resumidas finales del insumo
            $totalEntradaCant = $stockInicial; 
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
