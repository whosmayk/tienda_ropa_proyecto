-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-05-2026 a las 05:39:43
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tienda_ropa_test`
--
CREATE DATABASE IF NOT EXISTS `tienda_ropa_test` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `tienda_ropa_test`;

DELIMITER $$
--
-- Procedimientos
--
DROP PROCEDURE IF EXISTS `actualizar_precio_prenda`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `actualizar_precio_prenda` (IN `p_id_prenda` INT, IN `p_nuevo_precio` DECIMAL(10,2), IN `p_id_empleado` INT, IN `p_usuario` VARCHAR(50))   BEGIN
    DECLARE v_precio_anterior DECIMAL(10,2);
    SELECT precio INTO v_precio_anterior FROM prenda WHERE id_prenda = p_id_prenda;
    UPDATE prenda SET precio = p_nuevo_precio WHERE id_prenda = p_id_prenda;
    INSERT INTO actualizacion (fecha, precio_anterior, precio_nuevo, id_prenda, id_empleado)
    VALUES (NOW(), v_precio_anterior, p_nuevo_precio, p_id_prenda, p_id_empleado);
    INSERT INTO bitacora_sistema (usuario, accion, tabla_afectada)
    VALUES (p_usuario, CONCAT('Actualizacion de precio id_prenda: ', p_id_prenda, ' de ', v_precio_anterior, ' a ', p_nuevo_precio), 'prenda');
END$$

DROP PROCEDURE IF EXISTS `sp_categoria_nueva`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_categoria_nueva` (IN `p_nombre` VARCHAR(50), IN `p_descripcion` TEXT)   BEGIN
    INSERT INTO categoria (nombre, descripcion) VALUES (p_nombre, p_descripcion);
    SELECT LAST_INSERT_ID() AS id_categoria_nueva, p_nombre, p_descripcion;
END$$

DROP PROCEDURE IF EXISTS `sp_movimientos_por_prenda`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_movimientos_por_prenda` (IN `p_id_prenda` INT)   BEGIN
    SELECT id_movimiento, fecha, tipo_movimiento, cantidad, id_empleado
    FROM movimiento_stock
    WHERE id_prenda = p_id_prenda
    ORDER BY fecha DESC;
END$$

DROP PROCEDURE IF EXISTS `sp_recalcular_stock_prenda`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_recalcular_stock_prenda` (IN `p_id_prenda` INT)   BEGIN
    UPDATE prenda
    SET stock_actual = (
        SELECT COALESCE(SUM(
            CASE WHEN tipo_movimiento = 'salida' THEN -cantidad ELSE cantidad END
        ), 0)
        FROM movimiento_stock WHERE id_prenda = p_id_prenda
    )
    WHERE id_prenda = p_id_prenda;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actualizacion`
--

DROP TABLE IF EXISTS `actualizacion`;
CREATE TABLE `actualizacion` (
  `id_actualizacion` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `precio_anterior` decimal(10,2) NOT NULL,
  `precio_nuevo` decimal(10,2) NOT NULL,
  `id_prenda` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `actualizacion`
--

INSERT INTO `actualizacion` (`id_actualizacion`, `fecha`, `precio_anterior`, `precio_nuevo`, `id_prenda`, `id_empleado`) VALUES
(1, '2026-02-07 20:24:25', 420.00, 450.00, 1, 1),
(2, '2026-02-07 20:24:25', 600.00, 650.50, 2, 4),
(3, '2026-02-07 20:24:25', 1100.00, 1200.00, 3, 1),
(4, '2026-02-07 20:24:25', 320.00, 350.00, 4, 4),
(5, '2026-02-07 20:24:25', 380.00, 400.00, 5, 1),
(6, '2026-02-18 21:55:06', 1200.00, 1350.00, 3, 1),
(8, '2026-05-06 21:03:00', 1500.00, 1450.00, 39, 2),
(9, '2026-05-06 21:05:17', 1450.00, 1475.00, 39, 2),
(10, '2026-05-06 21:27:59', 1475.00, 1500.00, 39, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora_sistema`
--

DROP TABLE IF EXISTS `bitacora_sistema`;
CREATE TABLE `bitacora_sistema` (
  `id_bitacora` int(11) NOT NULL,
  `usuario` varchar(50) DEFAULT NULL,
  `accion` varchar(100) DEFAULT NULL,
  `tabla_afectada` varchar(50) DEFAULT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `bitacora_sistema`
--

INSERT INTO `bitacora_sistema` (`id_bitacora`, `usuario`, `accion`, `tabla_afectada`, `fecha_hora`) VALUES
(1, 'root', 'Inserción de nueva prenda: Camisa Oxford', 'prenda', '2026-03-23 22:51:16'),
(2, 'admin', 'Actualización de precio id_prenda: 3', 'actualizacion', '2026-03-23 22:51:16'),
(3, 'root', 'Eliminación de proveedor id: 6', 'proveedor', '2026-03-23 22:51:16'),
(4, 'user_ventas', 'Registro de movimiento de stock: entrada', 'movimiento_stock', '2026-03-23 22:51:16'),
(5, 'admin', 'Modificación de categoría: Caballero', 'categoria', '2026-03-23 22:51:16'),
(6, 'admin_tienda', 'Insercion de nueva prenda: Hoodie Cactus Jack', 'prenda', '2026-05-06 21:02:26'),
(7, 'root@localhost', 'Actualizacion de precio id_prenda: 39 de 1500.00 a 1450.00', 'prenda', '2026-05-06 21:03:00'),
(8, 'root@localhost', 'Actualizacion de precio id_prenda: 39 de 1450.00 a 1475.00', 'prenda', '2026-05-06 21:05:17'),
(9, 'empleado_tienda', 'Actualizacion de precio id_prenda: 39 de 1475.00 a 1500.00', 'prenda', '2026-05-06 21:27:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

DROP TABLE IF EXISTS `categoria`;
CREATE TABLE `categoria` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`id_categoria`, `nombre`, `descripcion`) VALUES
(1, 'Caballero', 'Ropa formal, informal, casual y de etiqueta para hombres'),
(2, 'Dama', 'Vestidos, blusas y tendencias de temporada para mujeres'),
(3, 'Infantil', 'Prendas cómodas y duraderas para niños de 2 a 12 años'),
(4, 'Deportiva', 'Ropa técnica de alto rendimiento'),
(5, 'Accesorios', 'Complementos como cinturones, bufandas y joyería básica');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `color`
--

DROP TABLE IF EXISTS `color`;
CREATE TABLE `color` (
  `id_color` int(11) NOT NULL,
  `nombre` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `color`
--

INSERT INTO `color` (`id_color`, `nombre`) VALUES
(1, 'Negro Nocturno'),
(2, 'Blanco Pureza'),
(3, 'Azul Marino'),
(4, 'Rojo Pasión'),
(5, 'Gris Oxford');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleado`
--

DROP TABLE IF EXISTS `empleado`;
CREATE TABLE `empleado` (
  `id_empleado` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `puesto` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleado`
--

INSERT INTO `empleado` (`id_empleado`, `nombre`, `puesto`) VALUES
(1, 'Karina Sánchez', 'gerente'),
(2, 'Miguel Esparza', 'empleado'),
(3, 'Kennia De luna', 'gerente'),
(4, 'Mariana Juárez', 'gerente'),
(5, 'Guadalupe Hernández', 'empleado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimiento_stock`
--

DROP TABLE IF EXISTS `movimiento_stock`;
CREATE TABLE `movimiento_stock` (
  `id_movimiento` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `tipo_movimiento` varchar(10) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `id_prenda` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movimiento_stock`
--

INSERT INTO `movimiento_stock` (`id_movimiento`, `fecha`, `tipo_movimiento`, `cantidad`, `id_prenda`, `id_empleado`) VALUES
(1, '2026-02-07 20:24:25', 'entrada', 10, 1, 2),
(2, '2026-02-07 20:24:25', 'salida', 5, 2, 3),
(3, '2026-02-07 20:24:25', 'ajuste', -2, 3, 1),
(4, '2026-02-07 20:24:25', 'entrada', 20, 4, 5),
(5, '2026-02-07 20:24:25', 'salida', 1, 5, 2),
(66, '2025-10-20 09:00:00', 'entrada', 10, 21, 2),
(67, '2025-10-20 14:30:00', 'salida', 2, 22, 3),
(68, '2025-10-21 08:15:00', 'entrada', 50, 23, 1),
(70, '2025-10-21 16:45:00', 'ajuste', -1, 25, 4),
(71, '2025-10-22 10:20:00', 'entrada', 20, 26, 1),
(72, '2026-01-22 13:10:00', 'salida', 3, 27, 5),
(73, '2026-01-23 09:45:00', 'entrada', 15, 28, 2),
(74, '2026-01-23 18:20:00', 'salida', 1, 29, 3),
(75, '2026-01-24 07:30:00', 'entrada', 30, 30, 4),
(76, '2026-01-24 15:00:00', 'salida', 10, 31, 2),
(77, '2026-01-25 11:15:00', 'entrada', 5, 32, 1),
(78, '2026-01-25 12:00:00', 'salida', 2, 33, 5),
(79, '2026-01-26 10:00:00', 'entrada', 12, 34, 2),
(80, '2026-01-26 16:30:00', 'salida', 4, 35, 3),
(81, '2026-02-18 22:11:01', 'ajuste', 5, 34, 1),
(82, '2026-02-24 07:26:22', 'entrada', 15, 36, 2),
(83, '2026-02-24 10:26:42', 'entrada', 10, 38, 2),
(84, '2026-03-23 23:05:14', 'entrada', 10, 1, 1),
(85, '2026-05-06 21:29:48', 'entrada', 10, 39, 2);

--
-- Disparadores `movimiento_stock`
--
DROP TRIGGER IF EXISTS `trg_actualizar_stock_delete`;
DELIMITER $$
CREATE TRIGGER `trg_actualizar_stock_delete` AFTER DELETE ON `movimiento_stock` FOR EACH ROW BEGIN
    UPDATE prenda
    SET stock_actual = stock_actual +
        CASE WHEN OLD.tipo_movimiento = 'salida' THEN OLD.cantidad ELSE -OLD.cantidad END
    WHERE id_prenda = OLD.id_prenda;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `trg_actualizar_stock_insert`;
DELIMITER $$
CREATE TRIGGER `trg_actualizar_stock_insert` AFTER INSERT ON `movimiento_stock` FOR EACH ROW BEGIN
    UPDATE prenda
    SET stock_actual = stock_actual +
        CASE WHEN NEW.tipo_movimiento = 'salida' THEN -NEW.cantidad ELSE NEW.cantidad END
    WHERE id_prenda = NEW.id_prenda;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prenda`
--

DROP TABLE IF EXISTS `prenda`;
CREATE TABLE `prenda` (
  `id_prenda` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock_actual` int(11) DEFAULT 0,
  `id_categoria` int(11) NOT NULL,
  `id_talla` int(11) NOT NULL,
  `id_color` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prenda`
--

INSERT INTO `prenda` (`id_prenda`, `nombre`, `precio`, `stock_actual`, `id_categoria`, `id_talla`, `id_color`) VALUES
(1, 'Camisa Oxford Slim', 450.00, 35, 1, 2, 3),
(2, 'Pantalón Jean Clásico', 650.50, 40, 1, 3, 1),
(3, 'Vestido de Gala Rojo', 1350.00, 10, 2, 2, 4),
(4, 'Playera Deportiva Pro', 350.00, 50, 4, 1, 2),
(5, 'Sudadera Infantil Hoodie', 400.00, 15, 3, 2, 5),
(21, 'Blusa Seda Elegante', 550.00, 15, 2, 1, 2),
(22, 'Chaqueta Cuero Sintético', 1200.00, 8, 1, 3, 1),
(23, 'Short Deportivo Runner', 280.00, 45, 4, 2, 3),
(25, 'Jeans Skinny Fit Dama', 750.00, 22, 2, 2, 3),
(26, 'Playera Básica Cuello V', 190.00, 60, 1, 2, 2),
(27, 'Vestido Midi Estampado', 890.00, 12, 2, 3, 4),
(28, 'Sudadera con Capucha Gris', 500.00, 18, 1, 3, 5),
(29, 'Pijama Térmica Infantil', 350.00, 30, 3, 2, 3),
(30, 'Gorra Deportiva Ajustable', 250.00, 25, 5, 3, 1),
(31, 'Suéter Lana Merino', 950.00, 10, 1, 3, 5),
(32, 'Falda Plisada Coreana', 480.00, 20, 2, 1, 4),
(33, 'Conjunto Deportivo Yoga', 720.00, 15, 4, 2, 1),
(34, 'Camiseta Manga Larga', 320.00, 45, 3, 3, 2),
(35, 'Cinturón Cuero Café', 300.00, 25, 5, 3, 5),
(36, 'Conjunto Pijama Negra', 250.00, 30, 1, 3, 1),
(38, 'Conjunto Pijama Negra', 220.00, 20, 1, 8, 1),
(39, 'Hoodie Cactus Jack', 1500.00, 10, 1, 8, 1);

--
-- Disparadores `prenda`
--
DROP TRIGGER IF EXISTS `trg_bitacora_cambio_precio`;
DELIMITER $$
CREATE TRIGGER `trg_bitacora_cambio_precio` AFTER UPDATE ON `prenda` FOR EACH ROW BEGIN
    IF OLD.precio <> NEW.precio THEN
        INSERT INTO bitacora_sistema (usuario, accion, tabla_afectada)
        VALUES (COALESCE(@usuario_app, CURRENT_USER()), CONCAT('Actualizacion de precio id_prenda: ', NEW.id_prenda, ' de ', OLD.precio, ' a ', NEW.precio), 'prenda');
    END IF;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `trg_prenda_insert`;
DELIMITER $$
CREATE TRIGGER `trg_prenda_insert` AFTER INSERT ON `prenda` FOR EACH ROW BEGIN
    INSERT INTO bitacora_sistema (usuario, accion, tabla_afectada)
    VALUES (COALESCE(@usuario_app, CURRENT_USER()), CONCAT('Insercion de nueva prenda: ', NEW.nombre), 'prenda');
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

DROP TABLE IF EXISTS `proveedor`;
CREATE TABLE `proveedor` (
  `id_proveedor` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` char(10) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedor`
--

INSERT INTO `proveedor` (`id_proveedor`, `nombre`, `telefono`, `direccion`) VALUES
(1, 'Casimires y Trajes Lucerna', '4499156551', 'Calle Francisco I. Madero 102 A, Aguascalientes'),
(2, 'Uniformes América', '4494357284', 'Av. Convención 1914 Ote. 105-A, Aguascalientes'),
(3, 'La Charrita de Aguascalientes', '4493110929', 'Calle Del Carmen 313, Aguascalientes'),
(4, 'Maquilas Textiles Arenas', '4492994799', 'Paseo de la Explanada 145, Aguascalientes'),
(5, 'Fábrica de Tejido de Punto y Confecciones Ofelia S.A. de C.V.', '4499730249', 'Av. Francisco I. Madero 810, Aguascalientes');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro`
--

DROP TABLE IF EXISTS `registro`;
CREATE TABLE `registro` (
  `id_registro` int(11) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `id_prenda` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL,
  `id_proveedor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `registro`
--

INSERT INTO `registro` (`id_registro`, `fecha_registro`, `id_prenda`, `id_empleado`, `id_proveedor`) VALUES
(1, '2026-02-07 20:24:25', 1, 1, 1),
(2, '2026-02-07 20:24:25', 2, 4, 2),
(3, '2026-02-07 20:24:25', 3, 1, 3),
(4, '2026-02-07 20:24:25', 4, 4, 4),
(5, '2026-02-07 20:24:25', 5, 1, 5),
(7, '2026-02-24 07:34:28', 36, 2, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `talla`
--

DROP TABLE IF EXISTS `talla`;
CREATE TABLE `talla` (
  `id_talla` int(11) NOT NULL,
  `talla` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `talla`
--

INSERT INTO `talla` (`id_talla`, `talla`) VALUES
(1, 'ch'),
(2, 'm'),
(3, 'g'),
(7, 'ech'),
(8, 'eg');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_historial_precios`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `vw_historial_precios`;
CREATE TABLE `vw_historial_precios` (
`id_actualizacion` int(11)
,`fecha` datetime
,`prenda` varchar(100)
,`precio_anterior` decimal(10,2)
,`precio_nuevo` decimal(10,2)
,`empleado` varchar(100)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_movimientos_stock`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `vw_movimientos_stock`;
CREATE TABLE `vw_movimientos_stock` (
`id_movimiento` int(11)
,`fecha` datetime
,`tipo_movimiento` varchar(10)
,`cantidad` int(11)
,`prenda` varchar(100)
,`empleado` varchar(100)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_prendas_detalle`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `vw_prendas_detalle`;
CREATE TABLE `vw_prendas_detalle` (
`id_prenda` int(11)
,`nombre` varchar(100)
,`precio` decimal(10,2)
,`stock_actual` int(11)
,`categoria` varchar(50)
,`talla` varchar(10)
,`color` varchar(30)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_registro_proveedores`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `vw_registro_proveedores`;
CREATE TABLE `vw_registro_proveedores` (
`id_registro` int(11)
,`fecha_registro` datetime
,`prenda` varchar(100)
,`empleado` varchar(100)
,`proveedor` varchar(100)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_historial_precios`
--
DROP TABLE IF EXISTS `vw_historial_precios`;

DROP VIEW IF EXISTS `vw_historial_precios`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_historial_precios`  AS SELECT `a`.`id_actualizacion` AS `id_actualizacion`, `a`.`fecha` AS `fecha`, `p`.`nombre` AS `prenda`, `a`.`precio_anterior` AS `precio_anterior`, `a`.`precio_nuevo` AS `precio_nuevo`, `e`.`nombre` AS `empleado` FROM ((`actualizacion` `a` join `prenda` `p` on(`a`.`id_prenda` = `p`.`id_prenda`)) join `empleado` `e` on(`a`.`id_empleado` = `e`.`id_empleado`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_movimientos_stock`
--
DROP TABLE IF EXISTS `vw_movimientos_stock`;

DROP VIEW IF EXISTS `vw_movimientos_stock`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_movimientos_stock`  AS SELECT `m`.`id_movimiento` AS `id_movimiento`, `m`.`fecha` AS `fecha`, `m`.`tipo_movimiento` AS `tipo_movimiento`, `m`.`cantidad` AS `cantidad`, `p`.`nombre` AS `prenda`, `e`.`nombre` AS `empleado` FROM ((`movimiento_stock` `m` join `prenda` `p` on(`m`.`id_prenda` = `p`.`id_prenda`)) join `empleado` `e` on(`m`.`id_empleado` = `e`.`id_empleado`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_prendas_detalle`
--
DROP TABLE IF EXISTS `vw_prendas_detalle`;

DROP VIEW IF EXISTS `vw_prendas_detalle`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_prendas_detalle`  AS SELECT `p`.`id_prenda` AS `id_prenda`, `p`.`nombre` AS `nombre`, `p`.`precio` AS `precio`, `p`.`stock_actual` AS `stock_actual`, `c`.`nombre` AS `categoria`, `t`.`talla` AS `talla`, `co`.`nombre` AS `color` FROM (((`prenda` `p` join `categoria` `c` on(`p`.`id_categoria` = `c`.`id_categoria`)) join `talla` `t` on(`p`.`id_talla` = `t`.`id_talla`)) join `color` `co` on(`p`.`id_color` = `co`.`id_color`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_registro_proveedores`
--
DROP TABLE IF EXISTS `vw_registro_proveedores`;

DROP VIEW IF EXISTS `vw_registro_proveedores`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_registro_proveedores`  AS SELECT `r`.`id_registro` AS `id_registro`, `r`.`fecha_registro` AS `fecha_registro`, `p`.`nombre` AS `prenda`, `e`.`nombre` AS `empleado`, `pr`.`nombre` AS `proveedor` FROM (((`registro` `r` join `prenda` `p` on(`r`.`id_prenda` = `p`.`id_prenda`)) join `empleado` `e` on(`r`.`id_empleado` = `e`.`id_empleado`)) join `proveedor` `pr` on(`r`.`id_proveedor` = `pr`.`id_proveedor`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actualizacion`
--
ALTER TABLE `actualizacion`
  ADD PRIMARY KEY (`id_actualizacion`),
  ADD KEY `fk_act_prenda` (`id_prenda`),
  ADD KEY `fk_act_emp` (`id_empleado`);

--
-- Indices de la tabla `bitacora_sistema`
--
ALTER TABLE `bitacora_sistema`
  ADD PRIMARY KEY (`id_bitacora`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `color`
--
ALTER TABLE `color`
  ADD PRIMARY KEY (`id_color`);

--
-- Indices de la tabla `empleado`
--
ALTER TABLE `empleado`
  ADD PRIMARY KEY (`id_empleado`);

--
-- Indices de la tabla `movimiento_stock`
--
ALTER TABLE `movimiento_stock`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `fk_mov_prenda` (`id_prenda`),
  ADD KEY `fk_mov_emp` (`id_empleado`),
  ADD KEY `idx_movimiento_fecha` (`fecha`);

--
-- Indices de la tabla `prenda`
--
ALTER TABLE `prenda`
  ADD PRIMARY KEY (`id_prenda`),
  ADD KEY `fk_prenda_talla` (`id_talla`),
  ADD KEY `fk_prenda_color` (`id_color`),
  ADD KEY `idx_prenda_nombre` (`nombre`),
  ADD KEY `idx_prenda_cat_talla` (`id_categoria`,`id_talla`);

--
-- Indices de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`id_proveedor`),
  ADD UNIQUE KEY `telefono` (`telefono`);

--
-- Indices de la tabla `registro`
--
ALTER TABLE `registro`
  ADD PRIMARY KEY (`id_registro`),
  ADD KEY `fk_reg_prenda` (`id_prenda`),
  ADD KEY `fk_reg_emp` (`id_empleado`),
  ADD KEY `fk_reg_prov` (`id_proveedor`);

--
-- Indices de la tabla `talla`
--
ALTER TABLE `talla`
  ADD PRIMARY KEY (`id_talla`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actualizacion`
--
ALTER TABLE `actualizacion`
  MODIFY `id_actualizacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `bitacora_sistema`
--
ALTER TABLE `bitacora_sistema`
  MODIFY `id_bitacora` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `color`
--
ALTER TABLE `color`
  MODIFY `id_color` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `empleado`
--
ALTER TABLE `empleado`
  MODIFY `id_empleado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `movimiento_stock`
--
ALTER TABLE `movimiento_stock`
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT de la tabla `prenda`
--
ALTER TABLE `prenda`
  MODIFY `id_prenda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `registro`
--
ALTER TABLE `registro`
  MODIFY `id_registro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `talla`
--
ALTER TABLE `talla`
  MODIFY `id_talla` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actualizacion`
--
ALTER TABLE `actualizacion`
  ADD CONSTRAINT `fk_act_emp` FOREIGN KEY (`id_empleado`) REFERENCES `empleado` (`id_empleado`),
  ADD CONSTRAINT `fk_act_prenda` FOREIGN KEY (`id_prenda`) REFERENCES `prenda` (`id_prenda`);

--
-- Filtros para la tabla `movimiento_stock`
--
ALTER TABLE `movimiento_stock`
  ADD CONSTRAINT `fk_mov_emp` FOREIGN KEY (`id_empleado`) REFERENCES `empleado` (`id_empleado`),
  ADD CONSTRAINT `fk_mov_prenda` FOREIGN KEY (`id_prenda`) REFERENCES `prenda` (`id_prenda`);

--
-- Filtros para la tabla `prenda`
--
ALTER TABLE `prenda`
  ADD CONSTRAINT `fk_prenda_cat` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`),
  ADD CONSTRAINT `fk_prenda_color` FOREIGN KEY (`id_color`) REFERENCES `color` (`id_color`),
  ADD CONSTRAINT `fk_prenda_talla` FOREIGN KEY (`id_talla`) REFERENCES `talla` (`id_talla`);

--
-- Filtros para la tabla `registro`
--
ALTER TABLE `registro`
  ADD CONSTRAINT `fk_reg_emp` FOREIGN KEY (`id_empleado`) REFERENCES `empleado` (`id_empleado`),
  ADD CONSTRAINT `fk_reg_prenda` FOREIGN KEY (`id_prenda`) REFERENCES `prenda` (`id_prenda`),
  ADD CONSTRAINT `fk_reg_prov` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedor` (`id_proveedor`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
