<?php
// Cargar conexión de base de datos y la entidad correspondiente
require_once dirname(__DIR__) . '/config/conexion.php';
require_once dirname(__DIR__) . '/entities/Cliente.php';

/**
 * Modelo para la gestión de Clientes
 * La lógica de sp_registrar_cliente ha sido migrada a PHP/PDO nativo.
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
     * Registra o reactiva un cliente de forma transaccional.
     * Equivalente a: sp_registrar_cliente
     *
     * Flujo:
     *   1. Busca persona por número de documento.
     *   2a. Si NO existe → inserta persona + cliente nuevos.
     *   2b. Si existe:
     *       - Actualiza los datos de la persona y la reactiva (estado=1).
     *       - Si ya tiene registro de cliente → actualiza tipo_cliente.
     *       - Si no tiene registro de cliente → inserta en 'clientes'.
     *
     * @param Cliente $cliente Entidad cliente con los datos personales y de cliente
     * @return bool|string Retorna true si fue exitoso o el mensaje del error en caso contrario
     */
    public function registrarCliente(Cliente $cliente) {
        try {
            $this->conexion->beginTransaction();

            // PASO 1: Buscar persona por documento
            $stmtBuscar = $this->conexion->prepare(
                "SELECT id_persona FROM personas WHERE numero_documento = ? LIMIT 1"
            );
            $stmtBuscar->execute([$cliente->numero_documento]);
            $persona = $stmtBuscar->fetch();

            if ($persona === false) {
                // PASO 2a: Persona nueva → insertar persona y luego cliente
                $stmtPer = $this->conexion->prepare(
                    "INSERT INTO personas (tipo_documento, numero_documento, nombres_razon_social, apellidos, direccion, telefono, estado)
                     VALUES (?, ?, ?, ?, ?, ?, 1)"
                );
                $stmtPer->execute([
                    $cliente->tipo_documento, $cliente->numero_documento,
                    $cliente->nombres_razon_social, $cliente->apellidos,
                    $cliente->direccion, $cliente->telefono
                ]);
                $id_persona = (int) $this->conexion->lastInsertId();

                $stmtCli = $this->conexion->prepare(
                    "INSERT INTO clientes (id_persona, tipo_cliente) VALUES (?, ?)"
                );
                $stmtCli->execute([$id_persona, $cliente->tipo_cliente]);

            } else {
                // PASO 2b: Persona existente → actualizar y reactivar
                $id_persona = $persona['id_persona'];

                $stmtUpd = $this->conexion->prepare(
                    "UPDATE personas SET tipo_documento=?, nombres_razon_social=?, apellidos=?, direccion=?, telefono=?, estado=1
                     WHERE id_persona=?"
                );
                $stmtUpd->execute([
                    $cliente->tipo_documento, $cliente->nombres_razon_social,
                    $cliente->apellidos, $cliente->direccion,
                    $cliente->telefono, $id_persona
                ]);

                // ¿Ya existe como cliente?
                $stmtExiste = $this->conexion->prepare(
                    "SELECT id_cliente FROM clientes WHERE id_persona = ? LIMIT 1"
                );
                $stmtExiste->execute([$id_persona]);
                $clienteRow = $stmtExiste->fetch();

                if ($clienteRow === false) {
                    // No existía como cliente: insertar
                    $stmtCli = $this->conexion->prepare(
                        "INSERT INTO clientes (id_persona, tipo_cliente) VALUES (?, ?)"
                    );
                    $stmtCli->execute([$id_persona, $cliente->tipo_cliente]);
                } else {
                    // Ya existe: solo actualizar tipo y campos planilla
                    $stmtUpdCli = $this->conexion->prepare(
                        "UPDATE clientes SET tipo_cliente=? WHERE id_cliente=?"
                    );
                    $stmtUpdCli->execute([$cliente->tipo_cliente, $clienteRow['id_cliente']]);
                }
            }

            $this->conexion->commit();
            return true;

        } catch (Exception $e) {
            if ($this->conexion->inTransaction()) $this->conexion->rollBack();
            // Retornar el string del error para que el controlador lo detalle al frontend
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
                    WHERE p.estado = 1 AND c.tipo_cliente != 3";
            
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