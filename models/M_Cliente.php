<?php
// Cargar conexión de base de datos y la entidad correspondiente
require_once dirname(__DIR__) . '/config/conexion.php';
require_once dirname(__DIR__) . '/entities/Cliente.php';

/**
 * Modelo para la gestión de Clientes
 * Interactúa con las tablas 'clientes' y 'personas' para registrar y actualizar clientes.
 */
class M_Cliente {
    // Instancia estática para el patrón Singleton
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
     * Registra un cliente llamando al procedimiento almacenado 'sp_registrar_cliente'
     * @param Cliente $cliente Entidad cliente con los datos personales y de cliente
     * @return bool|string Retorna true si fue exitoso o el mensaje del error de la BD en caso contrario
     */
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
            // Retornar el string del error para que el controlador lo pase y detalle al frontend
            return $e->getMessage();
        }
    }

    /**
     * Lista todos los clientes activos relacionando las tablas clientes y personas
     * @return array Listado asociativo con nombres, apellidos, documento y tipo de cliente
     */
    public function listarClientes() {
        try {
            // INNER JOIN para traer los datos humanos desde la tabla unificada de personas
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

    /**
     * Obtiene los datos completos de un cliente según su ID
     * @param int $id_cliente ID del cliente a buscar
     * @return array|false Fila de la base de datos o False si ocurre un error
     */
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

    /**
     * Busca y obtiene los datos de un cliente activo por su número de documento (DNI/RUC)
     * @param string $numero_documento Número de documento a consultar
     * @return array|false Fila de la base de datos o False si no existe o está inactivo
     */
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

    /**
     * Actualiza la información personal del cliente (tabla personas) y su tipo de cliente (tabla clientes)
     * Realiza una transacción de base de datos para garantizar la consistencia en ambas tablas.
     * @param Cliente $cliente Entidad cliente con los datos modificados
     * @return bool True en caso de éxito, False en caso de fallo
     */
    public function actualizarCliente(Cliente $cliente) {
        try {
            $this->conexion->beginTransaction();

            // 1. Actualizar la tabla general de personas relacionada al cliente
            $sql = "UPDATE personas SET nombres_razon_social = ?, apellidos = ?, direccion = ?, telefono = ? 
                     WHERE id_persona = (SELECT id_persona FROM clientes WHERE id_cliente = ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                $cliente->nombres_razon_social, $cliente->apellidos, 
                $cliente->direccion, $cliente->telefono, $cliente->id_cliente
            ]);

            // 2. Actualizar el tipo de cliente en la tabla clientes
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

    /**
     * Realiza la desactivación/borrado lógico de un cliente estableciendo el estado de su persona a 0
     * @param int $id_cliente ID del cliente a desactivar
     * @return bool True en caso de éxito, False si ocurre algún error
     */
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