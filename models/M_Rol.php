<?php
// Requerir archivo de conexión centralizada
require_once dirname(__DIR__) . '/config/conexion.php';
require_once dirname(__DIR__) . '/entities/Rol.php';

/**
 * Modelo para la gestión de Roles
 * Se encarga de interactuar con la tabla de roles en la base de datos.
 */
class M_Rol {
    // Instancia estática para el patrón Singleton
    private static $instancia = null;
    // Manejador de la conexión PDO
    private $conexion;

    // Constructor privado para evitar instanciación externa
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
     * Lista todos los roles disponibles en el sistema
     * @return array Listado de roles (ID, Nombre de rol)
     */
    public function listar() {
        try {
            $sql = "SELECT * FROM roles";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>