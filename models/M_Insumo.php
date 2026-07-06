<?php
// Cargar la conexión y la entidad del Insumo
require_once dirname(__DIR__) . '/config/conexion.php';
require_once dirname(__DIR__) . '/entities/Insumo.php';

/**
 * Modelo para la gestión del catálogo de Insumos
 * Permite registrar, listar, editar y deshabilitar artículos del catálogo.
 */
class M_Insumo {
    // Instancia única Singleton
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
     * Registra un nuevo insumo/artículo en el inventario con estado activo (1)
     * @param Insumo $insumo Entidad insumo con datos de categoría, unidad, precio y stock inicial
     * @return bool True en caso de éxito, False si ocurre algún error
     */
    public function registrar(Insumo $insumo) {
        try {
            $sql = "INSERT INTO insumos (id_categoria, id_unidad, nombre, precio_unitario, 
                    stock_piezas, contenido_estandar, estado) VALUES (?, ?, ?, ?, ?, ?, 1)";
            $stmt = $this->conexion->prepare($sql);
            
            $stmt->execute([
                $insumo->id_categoria,
                $insumo->id_unidad,
                $insumo->nombre,
                $insumo->precio_unitario,
                $insumo->stock_piezas,
                $insumo->contenido_estandar  // NULL = peso variable (pavos), número = peso fijo (sacos)
            ]);
            
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Lista todos los insumos activos en el inventario
     * Utiliza un doble INNER JOIN para obtener los nombres textuales de la categoría y la unidad de medida.
     * @return array Listado asociativo con detalles del insumo
     */
    public function listar() {
        try {
            $sql = "SELECT i.id_insumo, i.id_categoria, i.id_unidad, c.nombre AS categoria,
                    u.nombre AS unidad, u.abreviatura, i.nombre, i.precio_unitario,
                    i.stock_piezas, i.contenido_estandar, i.estado 
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

    /**
     * Obtiene la fila de base de datos de un insumo específico según su ID
     * @param int $id_insumo ID del insumo a consultar
     * @return array|false Fila de base de datos o False si ocurre un error
     */
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

    /**
     * Actualiza la información de un insumo (categoría, unidad de medida, nombre, precio y stock)
     * @param Insumo $insumo Entidad Insumo con los nuevos datos cargados
     * @return bool True en caso de éxito, False si ocurre algún error
     */
    public function actualizar(Insumo $insumo) {
        try {
            $sql = "UPDATE insumos SET id_categoria = ?, id_unidad = ?, nombre = ?, 
                    precio_unitario = ?, stock_piezas = ?, contenido_estandar = ? WHERE id_insumo = ?";
            $stmt = $this->conexion->prepare($sql);
            
            $stmt->execute([
                $insumo->id_categoria,
                $insumo->id_unidad,
                $insumo->nombre,
                $insumo->precio_unitario,
                $insumo->stock_piezas,
                $insumo->contenido_estandar,  // NULL = peso variable (pavos)
                $insumo->id_insumo
            ]);
            
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Realiza un borrado lógico del insumo estableciendo su estado en 0
     * @param int $id_insumo ID del insumo a desactivar
     * @return bool True en caso de éxito, False si ocurre algún error
     */
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