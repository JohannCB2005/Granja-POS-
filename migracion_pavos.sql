USE granja_pos;

-- 1. Modificar tabla insumos
ALTER TABLE insumos 
  CHANGE COLUMN stock stock_piezas DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  ADD COLUMN contenido_estandar DECIMAL(10,2) DEFAULT NULL 
    COMMENT 'Peso estándar por pieza. NULL = peso variable (aves vivas).';

-- 2. Modificar tabla detalle_ventas
ALTER TABLE detalle_ventas
  CHANGE COLUMN cantidad piezas DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  ADD COLUMN peso_neto DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER piezas;

-- 3. Eliminar SPs antiguos
DROP PROCEDURE IF EXISTS sp_registrar_usuario;
DROP PROCEDURE IF EXISTS sp_registrar_venta;
DROP PROCEDURE IF EXISTS sp_anular_venta;

-- 4. Recrear Procedimientos Almacenados
DELIMITER $$

CREATE PROCEDURE sp_registrar_usuario(
    IN p_tipo_documento TINYINT, IN p_numero_documento VARCHAR(15), 
    IN p_nombres_razon_social VARCHAR(150), IN p_apellidos VARCHAR(100), 
    IN p_direccion VARCHAR(255), IN p_telefono VARCHAR(15), 
    IN p_id_rol INT, IN p_username VARCHAR(50), IN p_password VARCHAR(255)
)
BEGIN
    DECLARE v_id_persona INT DEFAULT NULL;
    DECLARE v_existe_usuario INT DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT id_persona INTO v_id_persona FROM personas WHERE numero_documento = p_numero_documento LIMIT 1;

    IF v_id_persona IS NOT NULL THEN
        SELECT COUNT(*) INTO v_existe_usuario FROM usuarios WHERE id_persona = v_id_persona;
        
        IF v_existe_usuario > 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Este documento ya pertenece a un usuario del sistema.';
        ELSE
            UPDATE personas SET nombres_razon_social = p_nombres_razon_social, apellidos = p_apellidos, direccion = p_direccion, telefono = p_telefono, estado = 1 WHERE id_persona = v_id_persona;
        END IF;
    ELSE
        INSERT INTO personas (tipo_documento, numero_documento, nombres_razon_social, apellidos, direccion, telefono, estado)
        VALUES (p_tipo_documento, p_numero_documento, p_nombres_razon_social, p_apellidos, p_direccion, p_telefono, 1);
        SET v_id_persona = LAST_INSERT_ID();
    END IF;

    INSERT INTO usuarios (id_persona, id_rol, username, password)
    VALUES (v_id_persona, p_id_rol, p_username, p_password);

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
    DECLARE v_piezas DECIMAL(10,2);
    DECLARE v_peso_neto DECIMAL(10,2);
    DECLARE v_precio DECIMAL(10,2);
    DECLARE v_subtotal DECIMAL(10,2);
    DECLARE v_stock_piezas_actual DECIMAL(10,2);

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
        SET v_id_insumo = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_json_detalles, CONCAT('$[', v_i, '].id_insumo'))) AS UNSIGNED);
        
        SET v_piezas = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_json_detalles, CONCAT('$[', v_i, '].piezas'))) AS DECIMAL(10,2));
        SET v_peso_neto = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_json_detalles, CONCAT('$[', v_i, '].peso_neto'))) AS DECIMAL(10,2));
        
        SET v_precio = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_json_detalles, CONCAT('$[', v_i, '].precio'))) AS DECIMAL(10,2));
        SET v_subtotal = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_json_detalles, CONCAT('$[', v_i, '].subtotal'))) AS DECIMAL(10,2));

        SELECT stock_piezas INTO v_stock_piezas_actual 
        FROM insumos WHERE id_insumo = v_id_insumo FOR UPDATE;

        IF v_stock_piezas_actual < v_piezas THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stock físico (piezas) insuficiente. Transacción cancelada.';
        END IF;

        INSERT INTO detalle_ventas (id_venta, id_insumo, piezas, peso_neto, precio_venta, subtotal)
        VALUES (v_id_venta, v_id_insumo, v_piezas, v_peso_neto, v_precio, v_subtotal);

        UPDATE insumos 
        SET stock_piezas = stock_piezas - v_piezas 
        WHERE id_insumo = v_id_insumo;
        
        SET v_i = v_i + 1;
    END WHILE;
    COMMIT;
END$$

CREATE PROCEDURE sp_anular_venta(IN p_id_venta INT)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_id_insumo INT;
    DECLARE v_piezas DECIMAL(10,2);

    DECLARE cur CURSOR FOR SELECT id_insumo, piezas FROM detalle_ventas WHERE id_venta = p_id_venta;
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
        FETCH cur INTO v_id_insumo, v_piezas;
        IF done THEN
            LEAVE read_loop;
        END IF;

        UPDATE insumos 
        SET stock_piezas = stock_piezas + v_piezas
        WHERE id_insumo = v_id_insumo;
    END LOOP;
    CLOSE cur;
    COMMIT;
END$$

DELIMITER ;

-- 5. Agregar la categoría Aves si no existe (el ID lo crea autoincremental)
INSERT INTO categorias (nombre, descripcion, estado) 
SELECT 'Aves', 'Pavos, gallinas y otras aves vivas', 1
WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE nombre = 'Aves');
