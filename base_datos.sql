-- ==============================================================================
-- SISTEMA DE VENTAS DE INSUMOS DE GRANJA (POS)
-- SCRIPT DE BASE DE DATOS FINAL (Limpiado y Ordenado para el IDE)
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS granja_pos DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE granja_pos;

-- ==========================================
-- 1. TABLAS INDEPENDIENTES (Catálogos)
-- ==========================================

CREATE TABLE roles (
  id_rol INT(11) NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(50) NOT NULL,
  PRIMARY KEY (id_rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE unidades_medida (
  id_unidad INT(11) NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(50) NOT NULL,
  abreviatura VARCHAR(10) NOT NULL,
  estado TINYINT(1) DEFAULT 1,
  PRIMARY KEY (id_unidad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categorias (
  id_categoria INT(11) NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  descripcion VARCHAR(255) DEFAULT NULL,
  estado TINYINT(1) DEFAULT 1,
  PRIMARY KEY (id_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE personas (
  id_persona INT(11) NOT NULL AUTO_INCREMENT,
  tipo_documento TINYINT(1) NOT NULL DEFAULT 1,
  numero_documento VARCHAR(15) NOT NULL UNIQUE,
  nombres_razon_social VARCHAR(150) NOT NULL,
  apellidos VARCHAR(100) DEFAULT NULL,
  direccion VARCHAR(255) DEFAULT NULL,
  telefono VARCHAR(15) DEFAULT NULL,
  estado TINYINT(1) DEFAULT 1,
  PRIMARY KEY (id_persona)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ==========================================
-- 2. TABLAS DEPENDIENTES (Subtipos e Inventario)
-- ==========================================

CREATE TABLE usuarios (
  id_usuario INT(11) NOT NULL AUTO_INCREMENT,
  id_persona INT(11) NOT NULL UNIQUE,
  id_rol INT(11) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  PRIMARY KEY (id_usuario),
  FOREIGN KEY (id_persona) REFERENCES personas(id_persona) ON DELETE CASCADE,
  FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE clientes (
  id_cliente INT(11) NOT NULL AUTO_INCREMENT,
  id_persona INT(11) NOT NULL UNIQUE,
  tipo_cliente TINYINT(1) DEFAULT 1,
  PRIMARY KEY (id_cliente),
  FOREIGN KEY (id_persona) REFERENCES personas(id_persona) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE insumos (
  id_insumo INT(11) NOT NULL AUTO_INCREMENT,
  id_categoria INT(11) NOT NULL,
  id_unidad INT(11) NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  stock DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  estado TINYINT(1) DEFAULT 1,
  PRIMARY KEY (id_insumo),
  FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria),
  FOREIGN KEY (id_unidad) REFERENCES unidades_medida(id_unidad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ==========================================
-- 3. TABLAS TRANSACCIONALES (Ventas)
-- ==========================================

CREATE TABLE ventas (
  id_venta INT(11) NOT NULL AUTO_INCREMENT,
  id_usuario INT(11) NOT NULL,
  id_cliente INT(11) NOT NULL,
  tipo_comprobante TINYINT(1) NOT NULL DEFAULT 1,
  fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
  total DECIMAL(10,2) NOT NULL,
  estado TINYINT(1) DEFAULT 1,
  PRIMARY KEY (id_venta),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
  FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE detalle_ventas (
  id_detalle INT(11) NOT NULL AUTO_INCREMENT,
  id_venta INT(11) NOT NULL,
  id_insumo INT(11) NOT NULL,
  cantidad DECIMAL(10,2) NOT NULL,
  precio_venta DECIMAL(10,2) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id_detalle),
  FOREIGN KEY (id_venta) REFERENCES ventas(id_venta) ON DELETE CASCADE,
  FOREIGN KEY (id_insumo) REFERENCES insumos(id_insumo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ==========================================
-- 4. DATA SEMILLA (Valores por defecto)
-- ==========================================

INSERT INTO roles (id_rol, nombre) VALUES
(1, 'Administrador'),
(2, 'Vendedor');

INSERT INTO unidades_medida (id_unidad, nombre, abreviatura, estado) VALUES
(1, 'Unidad', 'UN', 1),
(2, 'Kilogramo', 'Kg', 1),
(3, 'Saco', 'SC', 1),
(4, 'Litro', 'L', 1),
(5, 'Gramo', 'g', 1),
(6, 'Paquete', 'PQ', 1);

INSERT INTO personas (id_persona, tipo_documento, numero_documento, nombres_razon_social, apellidos, direccion, telefono, estado) VALUES
(1, 1, '77777777', 'Max', 'Admin', NULL, '900000000', 1),
(2, 1, '00000000', 'Público General', '', NULL, NULL, 1);

INSERT INTO usuarios (id_usuario, id_persona, id_rol, username, password) VALUES
(1, 1, 1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO clientes (id_cliente, id_persona, tipo_cliente) VALUES
(1, 2, 1);


-- ==========================================
-- 5. PROCEDIMIENTOS ALMACENADOS
-- ==========================================

DELIMITER $$

CREATE PROCEDURE sp_registrar_usuario(
    IN p_tipo_documento TINYINT, IN p_numero_documento VARCHAR(15), 
    IN p_nombres_razon_social VARCHAR(150), IN p_apellidos VARCHAR(100), 
    IN p_direccion VARCHAR(255), IN p_telefono VARCHAR(15), 
    IN p_id_rol INT, IN p_username VARCHAR(50), IN p_password VARCHAR(255)
)
BEGIN
    DECLARE v_id_persona INT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;
    INSERT INTO personas (tipo_documento, numero_documento, nombres_razon_social, apellidos, direccion, telefono, estado)
    VALUES (p_tipo_documento, p_numero_documento, p_nombres_razon_social, p_apellidos, p_direccion, p_telefono, 1);
    
    SET v_id_persona = LAST_INSERT_ID();

    INSERT INTO usuarios (id_persona, id_rol, username, password)
    VALUES (v_id_persona, p_id_rol, p_username, p_password);
    COMMIT;
END$$

CREATE PROCEDURE sp_registrar_cliente(
    IN p_tipo_documento TINYINT, IN p_numero_documento VARCHAR(15), 
    IN p_nombres_razon_social VARCHAR(150), IN p_apellidos VARCHAR(100), 
    IN p_direccion VARCHAR(255), IN p_telefono VARCHAR(15), IN p_tipo_cliente TINYINT
)
BEGIN
    DECLARE v_id_persona INT DEFAULT NULL;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    -- Check if a persona with this document number already exists
    SELECT id_persona INTO v_id_persona FROM personas WHERE numero_documento = p_numero_documento LIMIT 1;

    IF v_id_persona IS NULL THEN
        -- Persona does not exist: insert into personas first
        INSERT INTO personas (tipo_documento, numero_documento, nombres_razon_social, apellidos, direccion, telefono, estado)
        VALUES (p_tipo_documento, p_numero_documento, p_nombres_razon_social, p_apellidos, p_direccion, p_telefono, 1);
        SET v_id_persona = LAST_INSERT_ID();
    ELSE
        -- Persona exists but may have been soft-deleted: restore and update their data
        UPDATE personas SET
            tipo_documento       = p_tipo_documento,
            nombres_razon_social = p_nombres_razon_social,
            apellidos            = p_apellidos,
            direccion            = p_direccion,
            telefono             = p_telefono,
            estado               = 1
        WHERE id_persona = v_id_persona;
    END IF;

    -- Insert into clientes (will fail with FK/UNIQUE if already a client, which is correct)
    INSERT INTO clientes (id_persona, tipo_cliente)
    VALUES (v_id_persona, p_tipo_cliente);

    COMMIT;
END$$

CREATE PROCEDURE sp_registrar_venta(
    IN p_id_usuario INT, IN p_id_cliente INT, IN p_tipo_comprobante TINYINT, 
    IN p_total DECIMAL(10,2), IN p_json_detalles JSON
)
BEGIN
    DECLARE v_id_venta INT;
    DECLARE v_i INT DEFAULT 0;
    DECLARE v_count INT;
    DECLARE v_id_insumo INT;
    DECLARE v_cantidad DECIMAL(10,2);
    DECLARE v_precio DECIMAL(10,2);
    DECLARE v_subtotal DECIMAL(10,2);
    DECLARE v_stock_actual DECIMAL(10,2);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;
    INSERT INTO ventas (id_usuario, id_cliente, tipo_comprobante, total, estado)
    VALUES (p_id_usuario, p_id_cliente, p_tipo_comprobante, p_total, 1);
    
    SET v_id_venta = LAST_INSERT_ID();
    SET v_count = JSON_LENGTH(p_json_detalles);

    WHILE v_i < v_count DO
        SET v_id_insumo = JSON_UNQUOTE(JSON_EXTRACT(p_json_detalles, CONCAT('$[', v_i, '].id_insumo')));
        SET v_cantidad = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_json_detalles, CONCAT('$[', v_i, '].cantidad'))) AS DECIMAL(10,2));
        SET v_precio = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_json_detalles, CONCAT('$[', v_i, '].precio'))) AS DECIMAL(10,2));
        SET v_subtotal = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_json_detalles, CONCAT('$[', v_i, '].subtotal'))) AS DECIMAL(10,2));

        SELECT stock INTO v_stock_actual FROM insumos WHERE id_insumo = v_id_insumo FOR UPDATE;

        IF v_stock_actual < v_cantidad THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stock insuficiente. Transacción cancelada.';
        END IF;

        INSERT INTO detalle_ventas (id_venta, id_insumo, cantidad, precio_venta, subtotal)
        VALUES (v_id_venta, v_id_insumo, v_cantidad, v_precio, v_subtotal);

        UPDATE insumos SET stock = stock - v_cantidad WHERE id_insumo = v_id_insumo;
        SET v_i = v_i + 1;
    END WHILE;
    COMMIT;
END$$

CREATE PROCEDURE sp_anular_venta(IN p_id_venta INT)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_id_insumo INT;
    DECLARE v_cantidad DECIMAL(10,2);

    DECLARE cur CURSOR FOR SELECT id_insumo, cantidad FROM detalle_ventas WHERE id_venta = p_id_venta;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;
    UPDATE ventas SET estado = 0 WHERE id_venta = p_id_venta;

    OPEN cur;
    read_loop: LOOP
        FETCH cur INTO v_id_insumo, v_cantidad;
        IF done THEN
            LEAVE read_loop;
        END IF;

        UPDATE insumos SET stock = stock + v_cantidad WHERE id_insumo = v_id_insumo;
    END LOOP;
    CLOSE cur;
    COMMIT;
END$$
DELIMITER ;

-- ==========================================
-- 6. TABLA DE CAJAS
-- ==========================================

CREATE TABLE cajas (
  id_caja       INT(11) NOT NULL AUTO_INCREMENT,
  id_usuario    INT(11) NOT NULL,
  monto_apertura DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  fecha_apertura DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  monto_cierre   DECIMAL(10,2) DEFAULT NULL,
  fecha_cierre   DATETIME DEFAULT NULL,
  total_ventas   DECIMAL(10,2) DEFAULT NULL,     -- calculado al cerrar
  num_ventas     INT(11) DEFAULT NULL,            -- calculado al cerrar
  diferencia     DECIMAL(10,2) DEFAULT NULL,      -- sobrante(+) / faltante(-)
  observaciones  TEXT DEFAULT NULL,               -- nota opcional al cerrar
  estado         TINYINT(1) NOT NULL DEFAULT 1,   -- 1=Abierta, 0=Cerrada
  PRIMARY KEY (id_caja),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;