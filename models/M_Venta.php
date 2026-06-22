<?php
require_once '../config/conexion.php';
require_once '../entities/Venta.php';

class M_Venta {
    private static $instancia = null;
    private $conexion;

    private function __construct() {
        $this->conexion = Conexion::singleton()->getConexion();
    }

    public static function singleton() {
        if (!isset(self::$instancia)) {
            $miclase = __CLASS__;
            self::$instancia = new $miclase;
        }
        return self::$instancia;
    }

    // 1. Registrar Venta (Usa el PA con JSON)
    public function registrar(Venta $venta) {
        try {
            $sql = "CALL sp_registrar_venta(?, ?, ?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            
            // Extraemos los detalles de la entidad y armamos un arreglo plano para el JSON
            $detallesArray = [];
            foreach ($venta->detalles as $detalle) {
                $detallesArray[] = [
                    'id_insumo' => $detalle->id_insumo,
                    'cantidad' => $detalle->cantidad,
                    'precio' => $detalle->precio_venta,
                    'subtotal' => $detalle->subtotal
                ];
            }
            
            $jsonDetalles = json_encode($detallesArray);

            $stmt->execute([
                $venta->id_usuario,
                $venta->id_cliente,
                $venta->tipo_comprobante,
                $venta->total,
                $jsonDetalles
            ]);
            
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // 2. Anular Venta (Usa el PA para devolver el stock)
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

    // 3. Listar Ventas (Historial general con cruce de datos)
    public function listar() {
        try {
            $sql = "SELECT v.id_venta, v.tipo_comprobante, v.fecha, v.total, v.estado,
                           u.username AS cajero, 
                           p.nombres_razon_social AS cliente_nombre, p.numero_documento 
                    FROM ventas v
                    INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
                    INNER JOIN clientes c ON v.id_cliente = c.id_cliente
                    INNER JOIN personas p ON c.id_persona = p.id_persona
                    ORDER BY v.fecha DESC";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // 4. Ver Detalle de una Venta (Para cuando le den clic a "Ver Boleta")
    public function obtenerDetallesPorVenta($id_venta) {
        try {
            $sql = "SELECT dv.cantidad, dv.precio_venta, dv.subtotal, 
                    i.nombre AS insumo, um.abreviatura AS unidad
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