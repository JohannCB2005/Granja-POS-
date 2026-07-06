<?php
// Cargar conexion y entidad correspondiente
require_once dirname(__DIR__) . '/config/conexion.php';
require_once dirname(__DIR__) . '/entities/Usuario.php';

/**
 * Modelo para la gestión de Usuarios y Login
 * Interactúa con la base de datos para manejar credenciales, datos de personas y control anti fuerza bruta.
 */
class M_Usuario {
    // Instancia única del patrón Singleton
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
     * Registra un nuevo usuario llamando al procedimiento almacenado 'sp_registrar_usuario'
     * Este procedimiento realiza la inserción unificada en las tablas personas y usuarios.
     * @param Usuario $usuario Entidad usuario con datos personales, credenciales y rol
     * @return bool True en caso de éxito, False si ocurre algún error
     */
    public function registrarUsuario(Usuario $usuario) {
        try {
            $sql = "CALL sp_registrar_usuario(?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            
            $stmt->bindParam(1, $usuario->tipo_documento);
            $stmt->bindParam(2, $usuario->numero_documento);
            $stmt->bindParam(3, $usuario->nombres_razon_social);
            $stmt->bindParam(4, $usuario->apellidos);
            $stmt->bindParam(5, $usuario->direccion);
            $stmt->bindParam(6, $usuario->telefono);
            $stmt->bindParam(7, $usuario->id_rol);
            $stmt->bindParam(8, $usuario->username);
            $stmt->bindParam(9, $usuario->password); 

            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            return false; 
        }
    }

    /**
     * Obtiene el listado de todos los usuarios activos relacionando personas y roles
     * @return array Listado de usuarios
     */
    public function listarUsuarios() {
        try {
            $sql = "SELECT p.tipo_documento, p.nombres_razon_social, p.apellidos, p.numero_documento, p.telefono, 
                        u.id_usuario, u.username, r.nombre AS rol, p.estado 
                    FROM usuarios u
                    INNER JOIN personas p ON u.id_persona = p.id_persona
                    INNER JOIN roles r ON u.id_rol = r.id_rol
                    WHERE p.estado = 1"; 
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(); 
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Busca los datos de un usuario por su ID
     * @param int $id_usuario ID único del usuario
     * @return array|false Fila de base de datos o False en caso de error
     */
    public function obtenerUsuarioPorId($id_usuario) {
        try {
            $sql = "SELECT p.*, u.*, r.nombre AS rol 
                    FROM usuarios u
                    INNER JOIN personas p ON u.id_persona = p.id_persona
                    INNER JOIN roles r ON u.id_rol = r.id_rol
                    WHERE u.id_usuario = ?";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(1, $id_usuario);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Actualiza la información personal en la tabla personas y los campos de usuario (rol/username)
     * Utiliza una transacción atómica para garantizar que ambas tablas se actualicen de manera segura.
     * @param Usuario $usuario Entidad con los nuevos datos cargados
     * @return bool True en caso de éxito, False en caso de error
     */
    public function actualizarUsuario(Usuario $usuario) {
        try {
            $this->conexion->beginTransaction();

            // 1. Actualizar Persona asociada al usuario
            $sql = "UPDATE personas SET tipo_documento = ?, numero_documento = ?, nombres_razon_social = ?, apellidos = ?, telefono = ?, direccion = ? 
                    WHERE id_persona = (SELECT id_persona FROM usuarios WHERE id_usuario = ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                $usuario->tipo_documento, 
                $usuario->numero_documento, 
                $usuario->nombres_razon_social, 
                $usuario->apellidos, 
                $usuario->telefono, 
                $usuario->direccion, 
                $usuario->id_usuario
            ]);

            // 2. Actualizar Usuario (Rol y Nombre de Usuario)
            $sql = "UPDATE usuarios SET id_rol = ?, username = ? WHERE id_usuario = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$usuario->id_rol, $usuario->username, $usuario->id_usuario]);

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            $this->conexion->rollBack();
            return false;
        }
    }

    /**
     * Desactiva de manera lógica a un usuario cambiando su estado en la tabla personas
     * @param int $id_usuario ID del usuario
     * @param int $nuevoEstado Nuevo estado (0 para inactivo, 1 para activo)
     * @return bool True en caso de éxito, False si ocurre algún error
     */
    public function EliminarUsuario($id_usuario, $nuevoEstado) {
        try {
            $sql = "UPDATE personas SET estado = ? 
                    WHERE id_persona = (SELECT id_persona FROM usuarios WHERE id_usuario = ?)";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$nuevoEstado, $id_usuario]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Recupera el hash de contraseña y los datos del usuario para el proceso de Login
     * Solo permite autenticación si el estado de la cuenta en la tabla personas es activo (p.estado = 1).
     * @param string $username Nombre de usuario escrito
     * @return array|false Fila de la base de datos o False si no existe o está inactivo
     */
    public function verificarLogin($username) {
        try {
            $sql = "SELECT u.id_usuario, p.nombres_razon_social, p.apellidos, u.username, u.password, r.nombre AS rol, p.estado 
                    FROM usuarios u 
                    INNER JOIN personas p ON u.id_persona = p.id_persona 
                    INNER JOIN roles r ON u.id_rol = r.id_rol 
                    WHERE u.username = ? AND p.estado = 1";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(1, $username);
            $stmt->execute();
            
            return $stmt->fetch();

        } catch (PDOException $e) {
            return false;
        }
    }
}
?>