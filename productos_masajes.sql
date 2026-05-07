-- =====================================================
-- Base de Datos: Aplicación Web de Productos para Masajes
-- =====================================================

-- Crear y seleccionar la base de datos
CREATE DATABASE IF NOT EXISTS masajes_products
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE masajes_products;

-- =====================================================
-- Tabla 1: Categorías de Productos  (lado "1" de la relación 1:N)
-- =====================================================
CREATE TABLE IF NOT EXISTS categorias (
    id_categoria   INT          PRIMARY KEY AUTO_INCREMENT,
    nombre_categoria VARCHAR(100) NOT NULL UNIQUE,
    descripcion    TEXT,
    fecha_creacion TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    activo         BOOLEAN     DEFAULT TRUE
) ENGINE=InnoDB;

-- =====================================================
-- Tabla 2: Productos  (lado "N" de la relación 1:N)
-- Un producto pertenece a UNA categoría.
-- Una categoría puede tener MUCHOS productos.
-- =====================================================
CREATE TABLE IF NOT EXISTS productos (
    id_producto          INT           PRIMARY KEY AUTO_INCREMENT,
    nombre_producto      VARCHAR(150)  NOT NULL,
    descripcion          VARCHAR(500),
    precio               DECIMAL(10,2) NOT NULL,
    cantidad_stock       INT           DEFAULT 0,
    id_categoria         INT           NOT NULL,
    imagen_url           VARCHAR(255),
    beneficios           TEXT,
    instrucciones_uso    TEXT,
    marca                VARCHAR(100),
    presentacion         VARCHAR(100),
    fecha_creacion       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    activo               BOOLEAN       DEFAULT TRUE,

    -- *** Clave Foránea: relación 1:N con categorias ***
    CONSTRAINT FK_producto_categoria
        FOREIGN KEY (id_categoria)
        REFERENCES categorias(id_categoria)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- Índices para optimizar búsquedas
-- =====================================================
CREATE INDEX IF NOT EXISTS idx_productos_categoria ON productos(id_categoria);
CREATE INDEX IF NOT EXISTS idx_productos_nombre    ON productos(nombre_producto);
CREATE INDEX IF NOT EXISTS idx_productos_activo    ON productos(activo);
CREATE INDEX IF NOT EXISTS idx_categorias_activo   ON categorias(activo);

-- =====================================================
-- Datos de ejemplo — Categorías
-- =====================================================
INSERT INTO categorias (nombre_categoria, descripcion) VALUES
('Aceites y Lociones',    'Aceites y lociones corporales para masajes relajantes'),
('Velas Aromáticas',      'Velas aromáticas para crear ambiente de relajación'),
('Herramientas de Masaje','Rodillos, piedras y herramientas especializadas');

-- =====================================================
-- Datos de ejemplo — Productos
-- =====================================================
INSERT INTO productos
    (nombre_producto, descripcion, precio, cantidad_stock, id_categoria,
     marca, presentacion, beneficios, instrucciones_uso)
VALUES
('Aceite de Almendra Dulce',
 'Aceite de almendra pura para masajes corporales',
 25.99, 50, 1,
 'NaturalCare', 'Botella 250ml',
 'Hidratación profunda, suaviza la piel',
 'Aplicar sobre la piel limpia con movimientos circulares'),

('Aceite de Coco Orgánico',
 'Aceite de coco virgen prensado en frío',
 22.50, 40, 1,
 'OrgánicoPuro', 'Frasco 200ml',
 'Propiedades antiinflamatorias',
 'Calentar ligeramente antes de usar'),

('Vela de Lavanda',
 'Vela aromática de lavanda para relajación',
 15.99, 100, 2,
 'AromaRelax', 'Vela 180g',
 'Reduce estrés y ansiedad',
 'Encender y dejar quemar 2-3 horas'),

('Vela de Eucalipto',
 'Aroma refrescante y descongestivo',
 15.99, 85, 2,
 'AromaRelax', 'Vela 180g',
 'Descongestiona y energiza',
 'Mantener alejada de corrientes de aire'),

('Rodillo Masajeador de Espuma',
 'Rodillo profesional para automasaje',
 34.99, 30, 3,
 'FitPro', 'Cilindro 33cm',
 'Alivia tensión muscular',
 'Usar sobre músculos principales del cuerpo'),

('Kit Piedras Calientes',
 'Juego de piedras basálticas para masaje termal',
 45.50, 20, 3,
 'TherapyStones', 'Juego de 12 piezas',
 'Relajación profunda',
 'Calentar en agua a 50-55 °C antes de usar');

-- =====================================================
-- Verificar datos insertados
-- =====================================================
SELECT * FROM categorias;

-- Ver productos con su categoría (JOIN 1:N)
SELECT
    p.id_producto,
    p.nombre_producto,
    p.precio,
    p.cantidad_stock,
    c.nombre_categoria,
    p.marca
FROM productos p
INNER JOIN categorias c ON p.id_categoria = c.id_categoria
WHERE p.activo = TRUE
ORDER BY c.nombre_categoria, p.nombre_producto;
