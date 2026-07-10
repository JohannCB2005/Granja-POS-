<?php
// Cargar dependencias para la transacción de ventas
require_once dirname(__DIR__) . '/config/conexion.php';
require_once dirname(__DIR__) . '/entities/Venta.php';

/**
 * Modelo para el procesamiento de Ventas
 * Gestiona el registro masivo con transacciones (a nivel de procedimiento almacenado), listado y anulación de ventas.
 */
class M_Venta {
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
            $miclase = __CLASS__;
            self::$instancia = new $miclase;
        }
        return self::$instancia;
    }

    /**
     * Registra una venta completa llamando al procedimiento almacenado 'sp_registrar_venta'
     * El procedimiento recibe el carrito en formato JSON para insertar las líneas de detalle
     * y restar el stock correspondiente de forma segura y atómica.
     * @param Venta $venta Entidad Venta completa con su lista de líneas de detalle
     * @return int|bool Retorna el ID de la venta creada si fue exitoso, o False en caso de error
     */
    public function registrar(Venta $venta) {
        try {
            $sql = "CALL sp_registrar_venta(?, ?, ?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            
            // Extraemos los detalles de la entidad y armamos el arreglo para el JSON del SP
            $detallesArray = [];
            foreach ($venta->detalles as $detalle) {
                $detallesArray[] = [
                    'id_insumo' => $detalle->id_insumo,
                    'piezas'    => $detalle->piezas,      // Unidades físicas que salen del inventario
                    'peso_neto' => $detalle->peso_neto,   // Peso real en Kg (pavos) o calculado (sacos)
                    'precio'    => $detalle->precio_venta,
                    'subtotal'  => $detalle->subtotal
                ];
            }
            
            $jsonDetalles = json_encode($detallesArray);

            // Ejecutar el SP con todos los parámetros
            $stmt->execute([
                $venta->id_usuario,
                $venta->id_cliente,
                $venta->tipo_comprobante,
                $venta->total,
                $jsonDetalles
            ]);
            
            // Consultar la última venta insertada por el usuario para retornar su ID
            $stmtId = $this->conexion->prepare("SELECT id_venta FROM ventas WHERE id_usuario = ? ORDER BY id_venta DESC LIMIT 1");
            $stmtId->execute([$venta->id_usuario]);
            $row = $stmtId->fetch();
            return $row ? $row['id_venta'] : true;
            
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Anula una venta registrada en el sistema llamando a 'sp_anular_venta'
     * Este procedimiento revierte el estado de la venta a inactivo y devuelve el stock al Kardex/Insumos.
     * @param int $id_venta ID de la venta a anular
     * @return bool True en caso de éxito, False si ocurre un error
     */
    public function anular($id_venta) {
        try {
            $sql = "CALL sp_anular_venta(?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$id_venta]);
            
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtiene el listado histórico de ventas del sistema
     * Si se pasa el ID de un vendedor, filtra únicamente sus ventas (Restricción del rol vendedor).
     * @param int|null $id_usuario ID opcional del vendedor
     * @return array Listado asociativo de ventas
     */
    public function listar($id_usuario = null) {
        try {
            $sql = "SELECT v.id_venta, v.tipo_comprobante, v.fecha, v.total, v.estado,
                           u.username AS vendedor, 
                           IF(p.apellidos IS NOT NULL AND p.apellidos != '', 
                              CONCAT(p.apellidos, ', ', p.nombres_razon_social), 
                              p.nombres_razon_social) AS cliente, 
                           p.numero_documento
                     FROM ventas v
                     INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
                     INNER JOIN clientes c ON v.id_cliente = c.id_cliente
                     INNER JOIN personas p ON c.id_persona = p.id_persona";
            
            if ($id_usuario !== null) {
                $sql .= " WHERE v.id_usuario = ?";
            }
            
            $sql .= " ORDER BY v.fecha DESC";
            
            $stmt = $this->conexion->prepare($sql);
            if ($id_usuario !== null) {
                $stmt->execute([$id_usuario]);
            } else {
                $stmt->execute();
            }
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtiene todas las líneas de detalle (insumo, cantidad, precio, subtotal y unidad) de una venta específica
     * @param int $id_venta ID de la venta a consultar
     * @return array Listado de productos e importes de la venta
     */
    public function obtenerDetallesPorVenta($id_venta) {
        try {
            $sql = "SELECT dv.piezas, dv.peso_neto, dv.precio_venta, dv.subtotal, 
                    i.nombre AS insumo_nombre, i.contenido_estandar, um.abreviatura
                    FROM detalle_ventas dv
                    INNER JOIN insumos i ON dv.id_insumo = i.id_insumo
                    INNER JOIN unidades_medida um ON i.id_unidad = um.id_unidad
                    WHERE dv.id_venta = ?";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$id_venta]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>