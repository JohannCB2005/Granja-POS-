<?php
require_once dirname(__DIR__) . '/config/conexion.php';
require_once dirname(__DIR__) . '/entities/Vale.php';

class M_Vale {
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

    public function emitir(Vale $vale) {
        try {
            $sql = "INSERT INTO vales (codigo, id_trabajador, monto, fecha_emision, fecha_vencimiento, estado, id_usuario_emisor, campana) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                $vale->codigo, 
                $vale->id_trabajador, 
                $vale->monto, 
                $vale->fecha_emision, 
                $vale->fecha_vencimiento, 
                $vale->estado, 
                $vale->id_usuario_emisor, 
                $vale->campana
            ]);
            return $this->conexion->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function emisionMasiva($campana, $tipo_vale, $monto, $id_insumo_especie, $cantidad_especie, $fecha_vencimiento, $filtro_tipo_trabajador, $filtro_dependencia, $id_usuario) {
        try {
            $this->conexion->beginTransaction();

            // Build query based on filters
            $sqlClientes = "SELECT t.id_trabajador, p.numero_documento 
                            FROM trabajadores t
                            INNER JOIN personas p ON t.id_persona = p.id_persona
                            WHERE 1=1";
                            
            $params = [];
            if ($filtro_tipo_trabajador) {
                $sqlClientes .= " AND t.id_tipo_trabajador = ?";
                $params[] = $filtro_tipo_trabajador;
            }
            if ($filtro_dependencia) {
                $sqlClientes .= " AND t.id_dependencia = ?";
                $params[] = $filtro_dependencia;
            }

            $stmtClientes = $this->conexion->prepare($sqlClientes);
            $stmtClientes->execute($params);
            $clientes = $stmtClientes->fetchAll();

            $sqlInsert = "INSERT INTO vales (codigo, tipo_vale, id_trabajador, monto, id_insumo_especie, cantidad_especie, fecha_emision, fecha_vencimiento, estado, id_usuario_emisor, campana) 
                          VALUES (?, ?, ?, ?, ?, ?, CURDATE(), ?, 1, ?, ?)";
            $stmtInsert = $this->conexion->prepare($sqlInsert);

            $creados = 0;
            $secuencial = 1;
            // Let's generate prefix from campana
            $prefijoCampana = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $campana), 0, 5));

            foreach ($clientes as $c) {
                $dni_ult4 = substr($c['numero_documento'], -4);
                $codigo = sprintf("%s-%s-%03d", $prefijoCampana, $dni_ult4, $secuencial);

                $stmtInsert->execute([
                    $codigo,
                    $tipo_vale,
                    $c['id_trabajador'],
                    $monto,
                    $id_insumo_especie,
                    $cantidad_especie,
                    $fecha_vencimiento,
                    $id_usuario,
                    $campana
                ]);
                $creados++;
                $secuencial++;
            }

            $this->conexion->commit();
            return ['ok' => true, 'creados' => $creados];
        } catch (Exception $e) {
            $this->conexion->rollBack();
            return ['ok' => false, 'mensaje' => $e->getMessage()];
        }
    }

    public function buscarPorCodigo($codigo) {
        try {
            $sql = "SELECT v.*, p.nombres_razon_social, p.apellidos, p.numero_documento,
                           i.nombre as insumo_especie_nombre 
                    FROM vales v
                    INNER JOIN trabajadores t ON v.id_trabajador = t.id_trabajador
                    INNER JOIN personas p ON t.id_persona = p.id_persona
                    LEFT JOIN insumos i ON v.id_insumo_especie = i.id_insumo
                    WHERE v.codigo = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$codigo]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    public function canjear($id_vale) {
        try {
            $sql = "UPDATE vales SET estado = 0 WHERE id_vale = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$id_vale]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listar() {
        try {
            $sql = "SELECT v.*, p.nombres_razon_social, p.apellidos, p.numero_documento, d.nombre as dependencia 
                    FROM vales v
                    INNER JOIN trabajadores t ON v.id_trabajador = t.id_trabajador
                    INNER JOIN personas p ON t.id_persona = p.id_persona
                    LEFT JOIN dependencias d ON t.id_dependencia = d.id_dependencia
                    ORDER BY v.fecha_emision DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function reportePorDependencia($campana, $id_dependencia, $estado) {
        try {
            $sql = "SELECT p.nombres_razon_social, p.apellidos, p.numero_documento, 
                           d.nombre as dependencia, v.codigo, v.monto, v.estado 
                    FROM vales v
                    INNER JOIN trabajadores t ON v.id_trabajador = t.id_trabajador
                    INNER JOIN personas p ON t.id_persona = p.id_persona
                    LEFT JOIN dependencias d ON t.id_dependencia = d.id_dependencia
                    WHERE 1=1";
            $params = [];
            
            if ($campana) {
                $sql .= " AND v.campana = ?";
                $params[] = $campana;
            }
            if ($id_dependencia) {
                $sql .= " AND t.id_dependencia = ?";
                $params[] = $id_dependencia;
            }
            if ($estado !== '' && $estado !== null) {
                $sql .= " AND v.estado = ?";
                $params[] = $estado;
            }

            $sql .= " ORDER BY d.nombre, p.apellidos";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function eliminarSeleccionados($ids) {
        if (empty($ids)) return false;
        try {
            $inQuery = implode(',', array_fill(0, count($ids), '?'));
            $sql = "DELETE FROM vales WHERE id_vale IN ($inQuery) AND estado = 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($ids);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
