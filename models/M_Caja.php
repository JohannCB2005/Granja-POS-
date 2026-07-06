<?php
// Cargar archivo de conexión centralizada
require_once dirname(__DIR__) . '/config/conexion.php';

/**
 * Modelo para el control de sesiones de Caja (Apertura y Cierre)
 * Gestiona el balance de dinero en caja por usuario y calcula diferencias al cerrar.
 */
class M_Caja {
    // Instancia estática del patrón Singleton
    private static $instancia;
    // Conexión PDO
    private $conexion;

    // Constructor privado
    private function __construct() {
        $this->conexion = Conexion::singleton()->getConexion();
    }

    // Obtener instancia única del modelo
    public static function singleton() {
        if (!isset(self::$instancia)) {
            $miclase = __CLASS__;
            self::$instancia = new $miclase;
        }
        return self::$instancia;
    }

    /**
     * Obtiene la sesión de caja que se encuentre abierta (estado = 1) para un usuario específico
     * @param int $id_usuario ID del usuario a consultar
     * @return array|null Fila de la base de datos o null si no tiene caja abierta
     */
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

    /**
     * Registra la apertura de caja de un usuario con un monto inicial
     * Valida que no exista ya otra caja abierta para el mismo usuario.
     * @param int $id_usuario ID del usuario
     * @param float $monto_apertura Saldo inicial con el que abre la caja
     * @return bool True si abre con éxito, False si ya tiene caja abierta o falla
     */
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

    /**
     * Cierra la sesión de caja del usuario, calculando automáticamente las ventas acumuladas,
     * el total esperado en efectivo y la diferencia (sobrante o faltante).
     * @param int $id_caja ID de la sesión de caja a cerrar
     * @param int $id_usuario ID del usuario dueño de la caja
     * @param float $monto_cierre Dinero físico real reportado por el usuario
     * @param string $observaciones Comentarios u observaciones sobre el cuadre de caja
     * @return bool True en caso de éxito, False si no existe la caja o falla la DB
     */
    public function cerrarCaja($id_caja, $id_usuario, $monto_cierre, $observaciones) {
        try {
            // 1. Obtener la fecha de apertura para delimitar el cálculo de ventas
            $sql_caja = "SELECT fecha_apertura FROM cajas WHERE id_caja = ? AND id_usuario = ? AND estado = 1";
            $stmt_caja = $this->conexion->prepare($sql_caja);
            $stmt_caja->execute([$id_caja, $id_usuario]);
            $caja = $stmt_caja->fetch();

            if (!$caja) return false;

            $fecha_apertura = $caja['fecha_apertura'];
            $fecha_cierre = date('Y-m-d H:i:s');

            // 2. Calcular la suma total de las ventas activas y número de tickets creados por el usuario en este rango de tiempo
            $sql_ventas = "SELECT SUM(total) as total_ventas, COUNT(id_venta) as num_ventas 
                           FROM ventas 
                           WHERE id_usuario = ? AND estado = 1 AND fecha BETWEEN ? AND ?";
            $stmt_ventas = $this->conexion->prepare($sql_ventas);
            $stmt_ventas->execute([$id_usuario, $fecha_apertura, $fecha_cierre]);
            $res_ventas = $stmt_ventas->fetch();

            $total_ventas = $res_ventas['total_ventas'] ? floatval($res_ventas['total_ventas']) : 0.00;
            $num_ventas = $res_ventas['num_ventas'] ? intval($res_ventas['num_ventas']) : 0;

            // 3. Obtener el monto de apertura original
            $sql_ap = "SELECT monto_apertura FROM cajas WHERE id_caja = ?";
            $stmt_ap = $this->conexion->prepare($sql_ap);
            $stmt_ap->execute([$id_caja]);
            $monto_apertura = floatval($stmt_ap->fetchColumn());

            // 4. Calcular diferencia: Dinero reportado - (Monto apertura + Ventas registradas en sistema)
            $diferencia = $monto_cierre - ($monto_apertura + $total_ventas);

            // 5. Actualizar la fila en cajas cerrando el estado (estado = 0)
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

    /**
     * Lista todas las sesiones de cajas abiertas y cerradas para una fecha específica (Filtro del Administrador)
     * Permite también opcionalmente filtrar por un usuario específico.
     * @param string $fecha Fecha a consultar (Formato YYYY-MM-DD)
     * @param int|null $id_usuario ID del usuario opcional para el filtro
     * @return array Listado de sesiones de caja con datos de la persona
     */
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

    /**
     * Calcula dinámicamente las ventas acumuladas hechas por un usuario desde su hora de apertura de caja
     * @param int $id_usuario ID del usuario
     * @param string $fecha_apertura Fecha y hora de apertura
     * @return float Sumatoria total de las ventas
     */
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
