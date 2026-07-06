<?php
require_once dirname(__DIR__) . '/config/conexion.php';
require_once dirname(__DIR__) . '/entities/Usuario.php';

class M_Usuario {
    private static $instancia = null;
    private $conexion;

    private function __construct() {
        // Heredamos la conexión centralizada
        $this->conexion = Conexion::singleton()->getConexion();
    }

    public static function singleton() {
        if (!isset(self::$instancia)) {
            $miclase = __CLASS__;
            self::$instancia = new $miclase;
        }
        return self::$instancia;
    }

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
            return true; // Éxito

        } catch (PDOException $e) {
            echo $e->getMessage(); 
            return false; 
        }
    }

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

    public function actualizarUsuario(Usuario $usuario) {
        try {
            $this->conexion->beginTransaction();

            // 1. Actualizar Persona
            $sql = "UPDATE personas SET tipo_documento = ?, numero_documento = ?, nombres_razon_social = ?, apellidos = ?, telefono = ?, direccion = ? 
                    WHERE id_persona = (SELECT id_persona FROM usuarios WHERE id_usuario = ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$usuario->tipo_documento, $usuario->numero_documento, $usuario->nombres_razon_social, $usuario->apellidos, $usuario->telefono, $usuario->direccion, $usuario->id_usuario]);

            // 2. Actualizar Usuario (Rol y Username)
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

    // Método para eliminar (cambiar estado) de un usuario
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

    // Método para el Login
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