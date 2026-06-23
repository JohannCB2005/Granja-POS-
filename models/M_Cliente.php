<?php
require_once dirname(__DIR__) . '/config/conexion.php';
require_once dirname(__DIR__) . '/entities/Cliente.php';

class M_Cliente {
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

    public function registrarCliente(Cliente $cliente) {
        try {
            $sql = "CALL sp_registrar_cliente(?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            
            $stmt->execute([
                $cliente->tipo_documento,
                $cliente->numero_documento,
                $cliente->nombres_razon_social,
                $cliente->apellidos,
                $cliente->direccion,
                $cliente->telefono,
                $cliente->tipo_cliente
            ]);
            
            return true;
        } catch (PDOException $e) {
            // Return error string so controller can pass it to the frontend
            return $e->getMessage();
        }
    }

    // Listar Clientes: INNER JOIN para traer los datos humanos desde personas
    public function listarClientes() {
        try {
            $sql = "SELECT p.*, c.id_cliente, c.tipo_cliente 
                    FROM clientes c
                    INNER JOIN personas p ON c.id_persona = p.id_persona
                    WHERE p.estado = 1";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // Obtener por ID
    public function obtenerClientePorId($id_cliente) {
        try {
            $sql = "SELECT p.*, c.* FROM clientes c
                    INNER JOIN personas p ON c.id_persona = p.id_persona
                    WHERE c.id_cliente = ?";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$id_cliente]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    // Obtener por Documento (DNI/RUC)
    public function obtenerClientePorDocumento($numero_documento) {
        try {
            $sql = "SELECT p.*, c.id_cliente, c.tipo_cliente FROM clientes c
                    INNER JOIN personas p ON c.id_persona = p.id_persona
                    WHERE p.numero_documento = ? AND p.estado = 1";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$numero_documento]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    // Actualizar Cliente
    public function actualizarCliente(Cliente $cliente) {
        try {
            $this->conexion->beginTransaction();

            $sql = "UPDATE personas SET nombres_razon_social = ?, apellidos = ?, direccion = ?, telefono = ? 
                     WHERE id_persona = (SELECT id_persona FROM clientes WHERE id_cliente = ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                $cliente->nombres_razon_social, $cliente->apellidos, 
                $cliente->direccion, $cliente->telefono, $cliente->id_cliente
            ]);

            $sql = "UPDATE clientes SET tipo_cliente = ? WHERE id_cliente = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$cliente->tipo_cliente, $cliente->id_cliente]);

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            $this->conexion->rollBack();
            return false;
        }
    }

    // Eliminar Cliente (Borrado Lógico)
    public function eliminarCliente($id_cliente) {
        try {
            $sql = "UPDATE personas SET estado = 0 
                    WHERE id_persona = (SELECT id_persona FROM clientes WHERE id_cliente = ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$id_cliente]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>