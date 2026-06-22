<?php
require_once dirname(__DIR__) . '/config/conexion.php';
require_once dirname(__DIR__) . '/entities/Insumo.php';

class M_Insumo {
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

    // 1. Registrar Insumo (Incluye el nuevo id_unidad)
    public function registrar(Insumo $insumo) {
        try {
            $sql = "INSERT INTO insumos (id_categoria, id_unidad, nombre, precio_unitario, 
                    stock, estado) VALUES (?, ?, ?, ?, ?, 1)";
            $stmt = $this->conexion->prepare($sql);
            
            $stmt->execute([
                $insumo->id_categoria,
                $insumo->id_unidad,
                $insumo->nombre,
                $insumo->precio_unitario,
                $insumo->stock
            ]);
            
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // 2. Listar Insumos (Doble INNER JOIN para traer nombres de categoría y de unidad)
    public function listar() {
        try {
            $sql = "SELECT i.id_insumo, c.nombre AS categoria, u.nombre AS unidad, 
                    u.abreviatura, i.nombre, i.precio_unitario, i.stock, i.estado 
                    FROM insumos i
                    INNER JOIN categorias c ON i.id_categoria = c.id_categoria
                    INNER JOIN unidades_medida u ON i.id_unidad = u.id_unidad
                    WHERE i.estado = 1";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // 3. Obtener Insumo por ID
    public function obtenerPorId($id_insumo) {
        try {
            $sql = "SELECT * FROM insumos WHERE id_insumo = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$id_insumo]);
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    // 4. Actualizar Insumo
    public function actualizar(Insumo $insumo) {
        try {
            $sql = "UPDATE insumos SET id_categoria = ?, id_unidad = ?, nombre = ?, 
                    precio_unitario = ?, stock = ? WHERE id_insumo = ?";
            $stmt = $this->conexion->prepare($sql);
            
            $stmt->execute([
                $insumo->id_categoria,
                $insumo->id_unidad,
                $insumo->nombre,
                $insumo->precio_unitario,
                $insumo->stock,
                $insumo->id_insumo
            ]);
            
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // 5. Eliminar (Borrado Lógico)
    public function eliminar($id_insumo) {
        try {
            $sql = "UPDATE insumos SET estado = 0 WHERE id_insumo = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$id_insumo]);
            
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>