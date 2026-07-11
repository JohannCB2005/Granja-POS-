<?php
require_once dirname(__DIR__) . '/config/conexion.php';
require_once dirname(__DIR__) . '/entities/Facultad.php';

class M_Facultad {
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

    public function listar() {
        try {
            $sql = "SELECT * FROM facultades WHERE estado = 1 ORDER BY nombre ASC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function registrar($nombre) {
        try {
            $sql = "INSERT INTO facultades (nombre, estado) VALUES (?, 1)";
            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([$nombre]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
