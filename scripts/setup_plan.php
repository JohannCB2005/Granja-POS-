<?php
require_once dirname(__DIR__) . '/config/conexion.php';
try {
    $db = Conexion::singleton()->getConexion();
    
    // 1. Tipos de trabajador
    $db->exec("
    CREATE TABLE IF NOT EXISTS tipos_trabajador (
      id_tipo INT(11) NOT NULL AUTO_INCREMENT,
      nombre VARCHAR(50) NOT NULL,
      estado TINYINT(1) DEFAULT 1,
      PRIMARY KEY (id_tipo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $db->exec("INSERT INTO tipos_trabajador (nombre) VALUES ('Docente/Nombrado'), ('Personal CAS') ON DUPLICATE KEY UPDATE id_tipo=id_tipo;");

    // 2. Dependencias (Facultades + Oficinas)
    $db->exec("
    CREATE TABLE IF NOT EXISTS dependencias (
      id_dependencia INT(11) NOT NULL AUTO_INCREMENT,
      nombre VARCHAR(200) NOT NULL,
      estado TINYINT(1) DEFAULT 1,
      PRIMARY KEY (id_dependencia)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 3. Nuevos campos en clientes
    // Add columns if they don't exist
    $checkCols = $db->query("SHOW COLUMNS FROM clientes LIKE 'id_tipo_trabajador'")->fetch();
    if (!$checkCols) {
        $db->exec("
        ALTER TABLE clientes
        ADD COLUMN id_tipo_trabajador INT(11) NULL AFTER tipo_cliente,
        ADD COLUMN id_dependencia INT(11) NULL AFTER id_tipo_trabajador,
        ADD COLUMN codigo_planilla VARCHAR(20) NULL AFTER id_dependencia;
        ");
        
        $db->exec("
        ALTER TABLE clientes
        ADD CONSTRAINT fk_cliente_tipo_trabajador FOREIGN KEY (id_tipo_trabajador) REFERENCES tipos_trabajador(id_tipo),
        ADD CONSTRAINT fk_cliente_dependencia FOREIGN KEY (id_dependencia) REFERENCES dependencias(id_dependencia);
        ");
    }

    // 4. Tabla de vales
    // Drop existing vales if it doesn't match the new schema
    $db->exec("DROP TABLE IF EXISTS vales");
    $db->exec("
    CREATE TABLE vales (
      id_vale INT(11) NOT NULL AUTO_INCREMENT,
      codigo VARCHAR(50) NOT NULL UNIQUE,
      id_cliente INT(11) NOT NULL,
      monto DECIMAL(10,2) NOT NULL,
      fecha_emision DATE NOT NULL,
      fecha_vencimiento DATE NOT NULL,
      estado TINYINT(1) DEFAULT 1,
      id_usuario_emisor INT(11) NOT NULL,
      campana VARCHAR(100) NULL,
      PRIMARY KEY (id_vale),
      FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
      FOREIGN KEY (id_usuario_emisor) REFERENCES usuarios(id_usuario)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 5. Método de pago y pago dividido en ventas
    $checkVentaCols = $db->query("SHOW COLUMNS FROM ventas LIKE 'pago_efectivo'")->fetch();
    if (!$checkVentaCols) {
        // Add id_vale and pago_efectivo, pago_vale
        $db->exec("
        ALTER TABLE ventas
        ADD COLUMN id_vale INT(11) NULL AFTER metodo_pago,
        ADD COLUMN pago_efectivo DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER id_vale,
        ADD COLUMN pago_vale DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER pago_efectivo;
        ");

        $db->exec("
        ALTER TABLE ventas
        ADD CONSTRAINT fk_venta_vale FOREIGN KEY (id_vale) REFERENCES vales(id_vale);
        ");
    }

    echo "Tablas creadas y modificadas correctamente.\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
