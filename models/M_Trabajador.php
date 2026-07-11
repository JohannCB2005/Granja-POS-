<?php
require_once dirname(__DIR__) . '/config/conexion.php';

class M_Trabajador {
    private static $instancia = null;
    private $db;

    private function __construct() {
        $this->db = Conexion::singleton()->getConexion();
    }

    public static function singleton() {
        if (!isset(self::$instancia)) {
            $miclase = __CLASS__;
            self::$instancia = new $miclase;
        }
        return self::$instancia;
    }

    public function listar() {
        try {
            $sql = "SELECT t.id_trabajador, p.numero_documento, p.nombres_razon_social, p.apellidos, 
                           tt.nombre AS tipo_trabajador, d.nombre AS dependencia, t.codigo_planilla
                    FROM trabajadores t
                    INNER JOIN personas p ON t.id_persona = p.id_persona
                    LEFT JOIN tipos_trabajador tt ON t.id_tipo_trabajador = tt.id_tipo
                    LEFT JOIN dependencias d ON t.id_dependencia = d.id_dependencia
                    WHERE t.estado = 1";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function eliminar($ids) {
        try {
            if (empty($ids)) return 0;
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "DELETE FROM trabajadores WHERE id_trabajador IN ($placeholders)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($ids);
            return $stmt->rowCount();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
?>
