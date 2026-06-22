<?php
require_once '../config/conexion.php';
require_once '../entities/Categoria.php';

class M_Categoria {
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

    public function registrar(Categoria $categoria) {
        try {
            $sql = "INSERT INTO categorias (nombre, descripcion, estado) VALUES (?, ?, 1)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$categoria->nombre, $categoria->descripcion]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listar() {
        try {
            $sql = "SELECT * FROM categorias WHERE estado = 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerPorId($id_categoria) {
        try {
            $sql = "SELECT * FROM categorias WHERE id_categoria = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$id_categoria]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function actualizar(Categoria $categoria) {
        try {
            $sql = "UPDATE categorias SET nombre = ?, descripcion = ? WHERE id_categoria = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                $categoria->nombre, 
                $categoria->descripcion, 
                $categoria->id_categoria
            ]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function eliminar($id_categoria) {
        try {
            $sql = "UPDATE categorias SET estado = 0 WHERE id_categoria = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$id_categoria]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>