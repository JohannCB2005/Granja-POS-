CREATE DATABASE IF NOT EXISTS granja_pos;
USE granja_pos;

-- 1. TABLA ROLES (Admin, Vendedor)
CREATE TABLE roles (
    id_rol INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    PRIMARY KEY (id_rol)
) ENGINE=InnoDB;

-- 2. TABLA USUARIOS (Los trabajadores que acceden al sistema)
CREATE TABLE usuarios (
    id_usuario INT NOT NULL AUTO_INCREMENT,
    id_rol INT NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    estado TINYINT(1) DEFAULT 1, -- 1: Activo, 0: Inactivo
    PRIMARY KEY (id_usuario),
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
) ENGINE=InnoDB;

-- 3. TABLA CLIENTES (Los compradores)
CREATE TABLE clientes (
    id_cliente INT NOT NULL AUTO_INCREMENT,
    dni VARCHAR(15) NOT NULL UNIQUE,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    telefono VARCHAR(15),
    estado TINYINT(1) DEFAULT 1, -- Agregado para Soft Delete (1: Activo, 0: Inactivo)
    PRIMARY KEY (id_cliente)
) ENGINE=InnoDB;

-- 4. TABLA CATEGORIAS (Para organizar los insumos en la 3NF)
CREATE TABLE categorias (
    id_categoria INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255),
    PRIMARY KEY (id_categoria)
) ENGINE=InnoDB;

-- 5. TABLA INSUMOS (El inventario de la granja)
CREATE TABLE insumos (
    id_insumo INT NOT NULL AUTO_INCREMENT,
    id_categoria INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    estado TINYINT(1) DEFAULT 1, -- 1: Disponible, 0: Agotado/Descontinuado
    PRIMARY KEY (id_insumo),
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
) ENGINE=InnoDB;

-- 6. TABLA VENTAS (La cabecera de la factura/boleta)
CREATE TABLE ventas (
    id_venta INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL, 
    id_cliente INT NOT NULL, 
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL,
    estado TINYINT(1) DEFAULT 1, -- Agregado (1: Completada, 0: Anulada)
    PRIMARY KEY (id_venta),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
) ENGINE=InnoDB;

-- 7. TABLA DETALLE_VENTAS (El "carrito" guardado. Rompe la relación Muchos a Muchos entre Ventas e Insumos)
CREATE TABLE detalle_ventas (
    id_detalle INT NOT NULL AUTO_INCREMENT,
    id_venta INT NOT NULL,
    id_insumo INT NOT NULL,
    cantidad INT NOT NULL,
    precio_venta DECIMAL(10,2) NOT NULL, -- Guardamos el precio al momento de la venta por si a futuro el insumo cambia de precio
    subtotal DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id_detalle),
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta) ON DELETE CASCADE,
    FOREIGN KEY (id_insumo) REFERENCES insumos(id_insumo)
) ENGINE=InnoDB;