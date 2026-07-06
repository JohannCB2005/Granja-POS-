<?php
require_once dirname(__DIR__) . '/config/conexion.php';

class M_Caja {
    private static $instancia;
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

    public function obtenerCajaAbierta($id_usuario) {
        try {
            $sql = "SELECT * FROM cajas WHERE id_usuario = ? AND estado = 1 ORDER BY id_caja DESC LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$id_usuario]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    public function abrirCaja($id_usuario, $monto_apertura) {
        try {
            // Verificar que no tenga ya una caja abierta
            if ($this->obtenerCajaAbierta($id_usuario)) {
                return false;
            }
            $sql = "INSERT INTO cajas (id_usuario, monto_apertura, estado) VALUES (?, ?, 1)";
            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([$id_usuario, $monto_apertura]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function cerrarCaja($id_caja, $id_usuario, $monto_cierre, $observaciones) {
        try {
            $sql_caja = "SELECT fecha_apertura FROM cajas WHERE id_caja = ? AND id_usuario = ? AND estado = 1";
            $stmt_caja = $this->conexion->prepare($sql_caja);
            $stmt_caja->execute([$id_caja, $id_usuario]);
            $caja = $stmt_caja->fetch();

            if (!$caja) return false;

            $fecha_apertura = $caja['fecha_apertura'];
            $fecha_cierre = date('Y-m-d H:i:s');

            $sql_ventas = "SELECT SUM(total) as total_ventas, COUNT(id_venta) as num_ventas 
                           FROM ventas 
                           WHERE id_usuario = ? AND estado = 1 AND fecha BETWEEN ? AND ?";
            $stmt_ventas = $this->conexion->prepare($sql_ventas);
            $stmt_ventas->execute([$id_usuario, $fecha_apertura, $fecha_cierre]);
            $res_ventas = $stmt_ventas->fetch();

            $total_ventas = $res_ventas['total_ventas'] ? floatval($res_ventas['total_ventas']) : 0.00;
            $num_ventas = $res_ventas['num_ventas'] ? intval($res_ventas['num_ventas']) : 0;

            $sql_ap = "SELECT monto_apertura FROM cajas WHERE id_caja = ?";
            $stmt_ap = $this->conexion->prepare($sql_ap);
            $stmt_ap->execute([$id_caja]);
            $monto_apertura = floatval($stmt_ap->fetchColumn());

            $diferencia = $monto_cierre - ($monto_apertura + $total_ventas);

            $sql_upd = "UPDATE cajas SET 
                        monto_cierre = ?, 
                        fecha_cierre = ?, 
                        total_ventas = ?, 
                        num_ventas = ?, 
                        diferencia = ?, 
                        observaciones = ?, 
                        estado = 0 
                        WHERE id_caja = ?";
            
            $stmt_upd = $this->conexion->prepare($sql_upd);
            return $stmt_upd->execute([
                $monto_cierre, $fecha_cierre, $total_ventas, $num_ventas, 
                $diferencia, $observaciones, $id_caja
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listarPorFecha($fecha, $id_usuario = null) {
        try {
            $sql = "SELECT c.*, u.username, CONCAT(p.nombres_razon_social, ' ', IFNULL(p.apellidos, '')) as nombre_completo
                    FROM cajas c
                    INNER JOIN usuarios u ON c.id_usuario = u.id_usuario
                    INNER JOIN personas p ON u.id_persona = p.id_persona
                    WHERE DATE(c.fecha_apertura) = ?";
            
            $params = [$fecha];

            if ($id_usuario) {
                $sql .= " AND c.id_usuario = ?";
                $params[] = $id_usuario;
            }

            $sql .= " ORDER BY c.fecha_apertura DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function calcularVentasAcumuladas($id_usuario, $fecha_apertura) {
        try {
            $sql = "SELECT SUM(total) as total_ventas 
                    FROM ventas 
                    WHERE id_usuario = ? AND estado = 1 AND fecha >= ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$id_usuario, $fecha_apertura]);
            $res = $stmt->fetchColumn();
            return $res ? floatval($res) : 0.00;
        } catch (PDOException $e) {
            return 0.00;
        }
    }
}
?>
