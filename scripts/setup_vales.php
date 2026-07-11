<?php
require_once dirname(__DIR__) . '/config/conexion.php';
try {
    $db = Conexion::singleton()->getConexion();
    $db->exec("
    CREATE TABLE IF NOT EXISTS vales (
        id_vale INT AUTO_INCREMENT PRIMARY KEY,
        id_cliente INT NOT NULL,
        kilos_cubiertos DECIMAL(10,2) NOT NULL DEFAULT 10.00,
        fecha_emision DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        estado TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Pendiente, 0=Canjeado',
        id_venta_canje INT NULL,
        FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
        FOREIGN KEY (id_venta_canje) REFERENCES ventas(id_venta)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Table vales created successfully.\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
