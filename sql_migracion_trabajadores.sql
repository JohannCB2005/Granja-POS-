-- 1. Crear tabla trabajadores
CREATE TABLE IF NOT EXISTS trabajadores (
  id_trabajador INT(11) NOT NULL AUTO_INCREMENT,
  id_persona INT(11) NOT NULL,
  id_tipo_trabajador INT(11) NOT NULL,
  id_dependencia INT(11) NULL,
  codigo_planilla VARCHAR(20) NULL,
  estado TINYINT(1) DEFAULT 1,
  PRIMARY KEY (id_trabajador),
  UNIQUE KEY (id_persona),
  FOREIGN KEY (id_persona) REFERENCES personas(id_persona) ON DELETE CASCADE,
  FOREIGN KEY (id_tipo_trabajador) REFERENCES tipos_trabajador(id_tipo),
  FOREIGN KEY (id_dependencia) REFERENCES dependencias(id_dependencia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Migrar datos de clientes a trabajadores (para los que ya estaban registrados)
INSERT IGNORE INTO trabajadores (id_persona, id_tipo_trabajador, id_dependencia, codigo_planilla, estado)
SELECT c.id_persona, c.id_tipo_trabajador, c.id_dependencia, c.codigo_planilla, 1
FROM clientes c
WHERE c.tipo_cliente = 3 AND c.id_tipo_trabajador IS NOT NULL;

-- 3. Alterar ventas para soportar al trabajador
ALTER TABLE ventas ADD COLUMN id_trabajador INT(11) NULL AFTER id_cliente;
ALTER TABLE ventas MODIFY COLUMN id_cliente INT(11) NULL;
ALTER TABLE ventas ADD CONSTRAINT fk_venta_trabajador FOREIGN KEY (id_trabajador) REFERENCES trabajadores(id_trabajador);

-- 4. Alterar vales para pertenecer al trabajador
ALTER TABLE vales DROP FOREIGN KEY vales_ibfk_1;
ALTER TABLE vales CHANGE COLUMN id_cliente id_trabajador INT(11) NOT NULL;
ALTER TABLE vales ADD CONSTRAINT fk_vale_trabajador FOREIGN KEY (id_trabajador) REFERENCES trabajadores(id_trabajador);

-- 5. Actualizar vales: Agregar campos de Especie
ALTER TABLE vales
ADD COLUMN tipo_vale TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Monetario, 2=Especie' AFTER codigo,
ADD COLUMN id_insumo_especie INT(11) NULL COMMENT 'Solo si tipo_vale=2' AFTER monto,
ADD COLUMN cantidad_especie DECIMAL(10,2) NULL COMMENT 'Kg o Unidades (ej. 8.00)' AFTER id_insumo_especie;

ALTER TABLE vales ADD CONSTRAINT fk_vale_insumo FOREIGN KEY (id_insumo_especie) REFERENCES insumos(id_insumo);
