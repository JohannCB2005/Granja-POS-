<?php
require_once dirname(__DIR__) . '/config/conexion.php';

class M_TipoTrabajador {
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
            $sql = "SELECT id_tipo, nombre, estado FROM tipos_trabajador WHERE estado = 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>
