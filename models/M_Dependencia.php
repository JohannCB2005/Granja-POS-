<?php
require_once dirname(__DIR__) . '/config/conexion.php';

class M_Dependencia {
    private $conexion;
    private static $instancia = null;

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
            $sql = "SELECT id_dependencia, nombre, estado FROM dependencias ORDER BY nombre";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function registrar($nombre) {
        try {
            $sql = "INSERT INTO dependencias (nombre, estado) VALUES (?, 1)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$nombre]);
            return $this->conexion->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
