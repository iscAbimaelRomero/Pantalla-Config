-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-09-2025 a las 22:01:34
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
-- Base de datos: `pantalla`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas_configuracion_clientes`
--

CREATE TABLE `empresas_configuracion_clientes` (
  `empresa_id` int(11) NOT NULL,
  `dias_ctespr` int(11) DEFAULT 365,
  `nventa_ctespr` int(11) DEFAULT -1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresas_configuracion_clientes`
--

INSERT INTO `empresas_configuracion_clientes` (`empresa_id`, `dias_ctespr`, `nventa_ctespr`) VALUES
(1, 15, -1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas_configuracion_impresion`
--

CREATE TABLE `empresas_configuracion_impresion` (
  `empresa_id` int(11) NOT NULL,
  `marginPrint` varchar(10) DEFAULT '0px'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresas_configuracion_impresion`
--

INSERT INTO `empresas_configuracion_impresion` (`empresa_id`, `marginPrint`) VALUES
(1, '0px');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas_configuracion_inventario`
--

CREATE TABLE `empresas_configuracion_inventario` (
  `empresa_id` int(11) NOT NULL,
  `seg_insumos` tinyint(1) NOT NULL DEFAULT 0,
  `seg_rf` int(11) DEFAULT 2,
  `seg_crf` int(11) DEFAULT 2
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresas_configuracion_inventario`
--

INSERT INTO `empresas_configuracion_inventario` (`empresa_id`, `seg_insumos`, `seg_rf`, `seg_crf`) VALUES
(1, 0, 2, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas_configuracion_notificaciones`
--

CREATE TABLE `empresas_configuracion_notificaciones` (
  `empresa_id` int(11) NOT NULL,
  `notif_cumple` tinyint(1) NOT NULL DEFAULT 0,
  `notif_inactiv` tinyint(1) NOT NULL DEFAULT 0,
  `periodSinActiv` int(11) DEFAULT 21,
  `tiempo_notificacion_1` varchar(20) DEFAULT '0',
  `status_send_1` varchar(50) DEFAULT '-Indistinto-',
  `send_type_1` varchar(50) DEFAULT '- Todos -',
  `tiempo_notificacion_2` varchar(20) DEFAULT '0',
  `status_send_2` varchar(50) DEFAULT '-Indistinto-',
  `send_type_2` varchar(50) DEFAULT '- Todos -'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresas_configuracion_notificaciones`
--

INSERT INTO `empresas_configuracion_notificaciones` (`empresa_id`, `notif_cumple`, `notif_inactiv`, `periodSinActiv`, `tiempo_notificacion_1`, `status_send_1`, `send_type_1`, `tiempo_notificacion_2`, `status_send_2`, `send_type_2`) VALUES
(1, 0, 0, 21, '0', '-Indistinto-', '- Todos -', '0', '-Indistinto-', '- Todos -');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas_principal`
--

CREATE TABLE `empresas_principal` (
  `id` int(11) NOT NULL,
  `nombre_empresa` varchar(255) DEFAULT NULL,
  `nit_empresa` varchar(50) DEFAULT NULL,
  `domicilio_empresa` varchar(255) DEFAULT NULL,
  `nombre_contacto` varchar(150) DEFAULT NULL,
  `tel1_contacto` varchar(50) DEFAULT NULL,
  `tel2_contacto` varchar(50) DEFAULT NULL,
  `email_contacto` varchar(150) DEFAULT NULL,
  `nota_especial` text DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `id_handel_base` varchar(50) DEFAULT NULL,
  `id_usuario_contacto` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresas_principal`
--

INSERT INTO `empresas_principal` (`id`, `nombre_empresa`, `nit_empresa`, `domicilio_empresa`, `nombre_contacto`, `tel1_contacto`, `tel2_contacto`, `email_contacto`, `nota_especial`, `activa`, `id_handel_base`, `id_usuario_contacto`) VALUES
(1, 'SyServ', '900.123.456-7', 'Calle Ficticia #123, Ciudad Digital', '', '', '', '', '', 0, NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `empresas_configuracion_clientes`
--
ALTER TABLE `empresas_configuracion_clientes`
  ADD PRIMARY KEY (`empresa_id`);

--
-- Indices de la tabla `empresas_configuracion_impresion`
--
ALTER TABLE `empresas_configuracion_impresion`
  ADD PRIMARY KEY (`empresa_id`);

--
-- Indices de la tabla `empresas_configuracion_inventario`
--
ALTER TABLE `empresas_configuracion_inventario`
  ADD PRIMARY KEY (`empresa_id`);

--
-- Indices de la tabla `empresas_configuracion_notificaciones`
--
ALTER TABLE `empresas_configuracion_notificaciones`
  ADD PRIMARY KEY (`empresa_id`);

--
-- Indices de la tabla `empresas_principal`
--
ALTER TABLE `empresas_principal`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `empresas_principal`
--
ALTER TABLE `empresas_principal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `empresas_configuracion_clientes`
--
ALTER TABLE `empresas_configuracion_clientes`
  ADD CONSTRAINT `fk_clientes_empresas_principal` FOREIGN KEY (`empresa_id`) REFERENCES `empresas_principal` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `empresas_configuracion_impresion`
--
ALTER TABLE `empresas_configuracion_impresion`
  ADD CONSTRAINT `fk_impresion_empresas_principal` FOREIGN KEY (`empresa_id`) REFERENCES `empresas_principal` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `empresas_configuracion_inventario`
--
ALTER TABLE `empresas_configuracion_inventario`
  ADD CONSTRAINT `fk_inventario_empresas_principal` FOREIGN KEY (`empresa_id`) REFERENCES `empresas_principal` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `empresas_configuracion_notificaciones`
--
ALTER TABLE `empresas_configuracion_notificaciones`
  ADD CONSTRAINT `fk_notificaciones_empresas_principal` FOREIGN KEY (`empresa_id`) REFERENCES `empresas_principal` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
