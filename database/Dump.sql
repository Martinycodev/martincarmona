-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-03-2026 a las 22:08:53
-- Versión del servidor: 11.8.3-MariaDB-log
-- Versión de PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `u873002419_campo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `campanas`
--

CREATE TABLE `campanas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `precio_venta` decimal(8,4) DEFAULT NULL,
  `id_user` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `campana_registros`
--

CREATE TABLE `campana_registros` (
  `id` int(11) NOT NULL,
  `campana_id` int(11) NOT NULL,
  `parcela_id` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `kilos` decimal(10,2) NOT NULL DEFAULT 0.00,
  `rendimiento_pct` decimal(5,2) DEFAULT NULL,
  `beneficio` decimal(12,2) DEFAULT NULL,
  `id_user` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `calidad` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos_parcelas`
--

CREATE TABLE `documentos_parcelas` (
  `id` int(11) NOT NULL,
  `parcela_id` int(11) NOT NULL,
  `tipo` enum('escritura','permiso_riego','otro') NOT NULL DEFAULT 'otro',
  `nombre` varchar(255) NOT NULL,
  `archivo` varchar(255) NOT NULL,
  `id_user` bigint(20) NOT NULL,
  `created_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fitosanitarios_aplicaciones`
--

CREATE TABLE `fitosanitarios_aplicaciones` (
  `id` int(11) NOT NULL,
  `parcela_id` int(11) DEFAULT NULL,
  `producto` varchar(255) NOT NULL,
  `fecha` date NOT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `tarea_id` int(11) DEFAULT NULL,
  `id_user` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fitosanitarios_inventario`
--

CREATE TABLE `fitosanitarios_inventario` (
  `id` int(11) NOT NULL,
  `producto` varchar(255) NOT NULL,
  `fecha_compra` date DEFAULT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `unidad` varchar(50) DEFAULT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  `id_user` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `herramientas`
--

CREATE TABLE `herramientas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `fecha_compra` date DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `id_user` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `instrucciones_pdf` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos`
--

CREATE TABLE `movimientos` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tipo` enum('ingreso','gasto') NOT NULL,
  `concepto` varchar(255) NOT NULL,
  `categoria` enum('personal','pago','impuestos','maquinaria','parcela','servicios','subvencion','otros','compras','reparaciones','inversiones','seguros','gestoria','labores_terceros','subvenciones','liquidacion_aceite') NOT NULL,
  `importe` decimal(10,2) NOT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  `trabajador_id` int(11) DEFAULT NULL,
  `vehiculo_id` int(11) DEFAULT NULL,
  `parcela_id` int(11) DEFAULT NULL,
  `estado` enum('pendiente','pagado') DEFAULT 'pendiente',
  `cuenta` enum('banco','efectivo') NOT NULL DEFAULT 'banco',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones_config`
--

CREATE TABLE `notificaciones_config` (
  `id` int(11) NOT NULL,
  `id_user` bigint(20) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `dias_antelacion` int(11) NOT NULL DEFAULT 7
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_mensuales_trabajadores`
--

CREATE TABLE `pagos_mensuales_trabajadores` (
  `id` int(11) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `month` tinyint(2) NOT NULL COMMENT '1-12',
  `year` smallint(4) NOT NULL COMMENT 'Ej: 2026',
  `importe_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pagado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_pago` date DEFAULT NULL,
  `id_user` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `parcelas`
--

CREATE TABLE `parcelas` (
  `nombre` varchar(255) NOT NULL,
  `id` int(11) NOT NULL,
  `olivos` int(11) NOT NULL,
  `ubicacion` varchar(255) NOT NULL,
  `propietario` varchar(255) NOT NULL,
  `propietario_id` int(11) DEFAULT NULL,
  `hidrante` int(11) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `referencia_catastral` varchar(50) DEFAULT NULL,
  `tipo_olivos` varchar(100) DEFAULT NULL,
  `año_plantacion` year(4) DEFAULT NULL,
  `tipo_plantacion` enum('tradicional','intensivo','superintensivo') DEFAULT NULL,
  `riego_secano` enum('riego','secano') DEFAULT NULL,
  `corta` enum('par','impar','siempre') DEFAULT NULL,
  `id_user` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `num_municipio` varchar(10) DEFAULT NULL,
  `num_poligono` varchar(10) DEFAULT NULL,
  `num_parcela` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `propietarios`
--

CREATE TABLE `propietarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `apellidos` varchar(255) DEFAULT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `imagen_dni_anverso` varchar(255) DEFAULT NULL,
  `imagen_dni_reverso` varchar(255) DEFAULT NULL,
  `id_user` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `id_user` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `razon_social` varchar(255) DEFAULT NULL,
  `cif` varchar(20) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `contacto_principal` varchar(150) DEFAULT NULL,
  `sector` varchar(100) DEFAULT NULL,
  `productos_servicios` text DEFAULT NULL,
  `condiciones_pago` varchar(255) DEFAULT NULL,
  `estado` varchar(50) NOT NULL DEFAULT 'activo',
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recordatorios`
--

CREATE TABLE `recordatorios` (
  `id` int(11) NOT NULL,
  `id_user` bigint(20) NOT NULL,
  `tipo` enum('itv','cuentas','fitosanitario','personalizado') NOT NULL DEFAULT 'personalizado',
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_aviso` date NOT NULL,
  `fecha_referencia` date DEFAULT NULL,
  `entidad_id` int(11) DEFAULT NULL,
  `leido` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `riegos`
--

CREATE TABLE `riegos` (
  `id` int(11) NOT NULL,
  `hidrante` varchar(50) DEFAULT NULL,
  `propiedad` varchar(255) DEFAULT NULL,
  `parcela_id` int(11) DEFAULT NULL,
  `cantidad_fin` float DEFAULT NULL,
  `cantidad_ini` float DEFAULT NULL,
  `dias` int(11) DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `fecha_ini` date DEFAULT NULL,
  `total_m3` float DEFAULT NULL,
  `id_user` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `riego_parcelas`
--

CREATE TABLE `riego_parcelas` (
  `riego_id` int(11) NOT NULL,
  `parcela_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

CREATE TABLE `tareas` (
  `id` int(11) NOT NULL,
  `fecha` date DEFAULT NULL,
  `estado` enum('realizada','pendiente') NOT NULL DEFAULT 'realizada',
  `titulo` varchar(150) NOT NULL DEFAULT '',
  `descripcion` varchar(255) DEFAULT NULL,
  `horas` decimal(5,2) DEFAULT 0.00 COMMENT 'Horas totales de la tarea',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `id_user` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarea_imagenes`
--

CREATE TABLE `tarea_imagenes` (
  `id` int(11) NOT NULL,
  `tarea_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarea_parcelas`
--

CREATE TABLE `tarea_parcelas` (
  `id` int(11) NOT NULL,
  `tarea_id` int(11) NOT NULL,
  `parcela_id` int(11) NOT NULL,
  `superficie_trabajada` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarea_trabajadores`
--

CREATE TABLE `tarea_trabajadores` (
  `id` int(11) NOT NULL,
  `tarea_id` int(11) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `horas_asignadas` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarea_trabajos`
--

CREATE TABLE `tarea_trabajos` (
  `id` int(11) NOT NULL,
  `tarea_id` int(11) NOT NULL,
  `trabajo_id` int(11) NOT NULL,
  `horas_trabajo` decimal(5,2) DEFAULT NULL,
  `precio_hora` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `temporadas_riego`
--

CREATE TABLE `temporadas_riego` (
  `id` int(11) NOT NULL,
  `anio` int(4) NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `id_user` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajadores`
--

CREATE TABLE `trabajadores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `dni` varchar(255) DEFAULT NULL,
  `ss` varchar(255) DEFAULT NULL,
  `alta_ss` date DEFAULT NULL,
  `baja_ss` date DEFAULT NULL,
  `cuadrilla` tinyint(1) NOT NULL DEFAULT 0,
  `id_user` bigint(20) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL COMMENT 'Ruta de la foto del trabajador',
  `apellidos` varchar(100) DEFAULT NULL COMMENT 'Apellidos del trabajador',
  `email` varchar(100) DEFAULT NULL COMMENT 'Email del trabajador',
  `direccion` text DEFAULT NULL COMMENT 'Dirección del trabajador',
  `especialidad` varchar(100) DEFAULT NULL COMMENT 'Especialidad del trabajador',
  `fecha_contratacion` date DEFAULT NULL COMMENT 'Fecha de contratación',
  `estado` enum('activo','inactivo') DEFAULT 'activo' COMMENT 'Estado del trabajador',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `imagen_dni_anverso` varchar(255) DEFAULT NULL,
  `imagen_dni_reverso` varchar(255) DEFAULT NULL,
  `imagen_ss` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_baja` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajos`
--

CREATE TABLE `trabajos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `precio_hora` float NOT NULL DEFAULT 0,
  `id_user` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `documento` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajos_trabajadores`
--

CREATE TABLE `trabajos_trabajadores` (
  `id` int(11) NOT NULL,
  `trabajo_id` int(11) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `precio_tarea` decimal(10,2) DEFAULT 0.00 COMMENT 'Precio acordado por la tarea',
  `pagado` decimal(10,2) DEFAULT 0.00 COMMENT 'Cantidad ya pagada',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `rol` enum('empresa','admin','propietario','trabajador') NOT NULL DEFAULT 'empresa',
  `propietario_id` int(11) DEFAULT NULL,
  `trabajador_id` int(11) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

CREATE TABLE `vehiculos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `matricula` varchar(50) DEFAULT NULL,
  `precio_seguro` decimal(10,2) DEFAULT NULL,
  `fecha_matriculacion` date DEFAULT NULL,
  `seguro` varchar(100) DEFAULT NULL,
  `pasa_itv` date DEFAULT NULL,
  `id_user` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ficha_tecnica` varchar(255) DEFAULT NULL,
  `poliza_seguro` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `campanas`
--
ALTER TABLE `campanas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `campana_registros`
--
ALTER TABLE `campana_registros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_campana_registros_campana` (`campana_id`),
  ADD KEY `fk_campana_registros_parcela` (`parcela_id`);

--
-- Indices de la tabla `documentos_parcelas`
--
ALTER TABLE `documentos_parcelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_docs_parcela` (`parcela_id`),
  ADD KEY `idx_docs_user` (`id_user`);

--
-- Indices de la tabla `fitosanitarios_aplicaciones`
--
ALTER TABLE `fitosanitarios_aplicaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_fitosan_ap_parcela` (`parcela_id`),
  ADD KEY `fk_fitosan_ap_tarea` (`tarea_id`);

--
-- Indices de la tabla `fitosanitarios_inventario`
--
ALTER TABLE `fitosanitarios_inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_fitosan_proveedor` (`proveedor_id`);

--
-- Indices de la tabla `herramientas`
--
ALTER TABLE `herramientas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_herramientas_user` (`id_user`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `movimientos`
--
ALTER TABLE `movimientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_mov_proveedor` (`proveedor_id`),
  ADD KEY `fk_mov_trabajador` (`trabajador_id`),
  ADD KEY `fk_mov_vehiculo` (`vehiculo_id`),
  ADD KEY `fk_mov_parcela` (`parcela_id`),
  ADD KEY `idx_movimientos_fecha` (`fecha`);

--
-- Indices de la tabla `notificaciones_config`
--
ALTER TABLE `notificaciones_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_tipo` (`id_user`,`tipo`);

--
-- Indices de la tabla `pagos_mensuales_trabajadores`
--
ALTER TABLE `pagos_mensuales_trabajadores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_trabajador_month_year` (`trabajador_id`,`month`,`year`),
  ADD KEY `fk_pmt_trabajador` (`trabajador_id`),
  ADD KEY `fk_pmt_user` (`id_user`);

--
-- Indices de la tabla `parcelas`
--
ALTER TABLE `parcelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_parcelas_user` (`id_user`),
  ADD KEY `idx_parcelas_propietario` (`propietario`),
  ADD KEY `fk_parcelas_propietario` (`propietario_id`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `propietarios`
--
ALTER TABLE `propietarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_propietarios_id_user` (`id_user`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_proveedores_user` (`id_user`);

--
-- Indices de la tabla `recordatorios`
--
ALTER TABLE `recordatorios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_fecha` (`id_user`,`fecha_aviso`),
  ADD KEY `idx_user_leido` (`id_user`,`leido`);

--
-- Indices de la tabla `riegos`
--
ALTER TABLE `riegos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_riegos_user` (`id_user`),
  ADD KEY `fk_riegos_parcela` (`parcela_id`);

--
-- Indices de la tabla `riego_parcelas`
--
ALTER TABLE `riego_parcelas`
  ADD PRIMARY KEY (`riego_id`,`parcela_id`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tareas_user` (`id_user`),
  ADD KEY `idx_tareas_fecha` (`fecha`);

--
-- Indices de la tabla `tarea_imagenes`
--
ALTER TABLE `tarea_imagenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tarea_id` (`tarea_id`);

--
-- Indices de la tabla `tarea_parcelas`
--
ALTER TABLE `tarea_parcelas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tarea_parcela` (`tarea_id`,`parcela_id`),
  ADD KEY `fk_tarea_parcelas_tarea` (`tarea_id`),
  ADD KEY `fk_tarea_parcelas_parcela` (`parcela_id`);

--
-- Indices de la tabla `tarea_trabajadores`
--
ALTER TABLE `tarea_trabajadores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tarea_trabajador` (`tarea_id`,`trabajador_id`),
  ADD KEY `fk_tarea_trabajadores_tarea` (`tarea_id`),
  ADD KEY `fk_tarea_trabajadores_trabajador` (`trabajador_id`);

--
-- Indices de la tabla `tarea_trabajos`
--
ALTER TABLE `tarea_trabajos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tarea_trabajo` (`tarea_id`,`trabajo_id`),
  ADD KEY `fk_tarea_trabajos_tarea` (`tarea_id`),
  ADD KEY `fk_tarea_trabajos_trabajo` (`trabajo_id`);

--
-- Indices de la tabla `temporadas_riego`
--
ALTER TABLE `temporadas_riego`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_anio_user` (`anio`,`id_user`);

--
-- Indices de la tabla `trabajadores`
--
ALTER TABLE `trabajadores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_trabajadores_user` (`id_user`),
  ADD KEY `idx_trabajadores_estado` (`estado`),
  ADD KEY `idx_trabajadores_id_user` (`id_user`);

--
-- Indices de la tabla `trabajos`
--
ALTER TABLE `trabajos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_trabajos_user` (`id_user`);

--
-- Indices de la tabla `trabajos_trabajadores`
--
ALTER TABLE `trabajos_trabajadores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_trabajo_trabajador` (`trabajo_id`,`trabajador_id`),
  ADD KEY `idx_trabajos_trabajadores_trabajador` (`trabajador_id`),
  ADD KEY `idx_trabajos_trabajadores_trabajo` (`trabajo_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_usuarios_propietario` (`propietario_id`),
  ADD KEY `fk_usuarios_trabajador` (`trabajador_id`);

--
-- Indices de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_vehiculos_user` (`id_user`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `campanas`
--
ALTER TABLE `campanas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `campana_registros`
--
ALTER TABLE `campana_registros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `documentos_parcelas`
--
ALTER TABLE `documentos_parcelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `fitosanitarios_aplicaciones`
--
ALTER TABLE `fitosanitarios_aplicaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `fitosanitarios_inventario`
--
ALTER TABLE `fitosanitarios_inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `herramientas`
--
ALTER TABLE `herramientas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimientos`
--
ALTER TABLE `movimientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones_config`
--
ALTER TABLE `notificaciones_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos_mensuales_trabajadores`
--
ALTER TABLE `pagos_mensuales_trabajadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `parcelas`
--
ALTER TABLE `parcelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `propietarios`
--
ALTER TABLE `propietarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recordatorios`
--
ALTER TABLE `recordatorios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `riegos`
--
ALTER TABLE `riegos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tareas`
--
ALTER TABLE `tareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tarea_imagenes`
--
ALTER TABLE `tarea_imagenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tarea_parcelas`
--
ALTER TABLE `tarea_parcelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tarea_trabajadores`
--
ALTER TABLE `tarea_trabajadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tarea_trabajos`
--
ALTER TABLE `tarea_trabajos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `temporadas_riego`
--
ALTER TABLE `temporadas_riego`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trabajadores`
--
ALTER TABLE `trabajadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trabajos`
--
ALTER TABLE `trabajos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trabajos_trabajadores`
--
ALTER TABLE `trabajos_trabajadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `campana_registros`
--
ALTER TABLE `campana_registros`
  ADD CONSTRAINT `fk_campana_registros_campana` FOREIGN KEY (`campana_id`) REFERENCES `campanas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_campana_registros_parcela` FOREIGN KEY (`parcela_id`) REFERENCES `parcelas` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `documentos_parcelas`
--
ALTER TABLE `documentos_parcelas`
  ADD CONSTRAINT `fk_docs_parcela` FOREIGN KEY (`parcela_id`) REFERENCES `parcelas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `fitosanitarios_aplicaciones`
--
ALTER TABLE `fitosanitarios_aplicaciones`
  ADD CONSTRAINT `fk_fitosan_ap_parcela` FOREIGN KEY (`parcela_id`) REFERENCES `parcelas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_fitosan_ap_tarea` FOREIGN KEY (`tarea_id`) REFERENCES `tareas` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `fitosanitarios_inventario`
--
ALTER TABLE `fitosanitarios_inventario`
  ADD CONSTRAINT `fk_fitosan_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `herramientas`
--
ALTER TABLE `herramientas`
  ADD CONSTRAINT `fk_herramientas_user` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `movimientos`
--
ALTER TABLE `movimientos`
  ADD CONSTRAINT `fk_mov_parcela` FOREIGN KEY (`parcela_id`) REFERENCES `parcelas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_mov_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_mov_trabajador` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_mov_vehiculo` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `pagos_mensuales_trabajadores`
--
ALTER TABLE `pagos_mensuales_trabajadores`
  ADD CONSTRAINT `fk_pmt_trabajador` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pmt_user` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `parcelas`
--
ALTER TABLE `parcelas`
  ADD CONSTRAINT `fk_parcelas_propietario` FOREIGN KEY (`propietario_id`) REFERENCES `propietarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_parcelas_user` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD CONSTRAINT `fk_proveedores_user` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `riegos`
--
ALTER TABLE `riegos`
  ADD CONSTRAINT `fk_riegos_parcela` FOREIGN KEY (`parcela_id`) REFERENCES `parcelas` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD CONSTRAINT `fk_tareas_user` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `tarea_parcelas`
--
ALTER TABLE `tarea_parcelas`
  ADD CONSTRAINT `fk_tarea_parcelas_parcela` FOREIGN KEY (`parcela_id`) REFERENCES `parcelas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tarea_parcelas_tarea` FOREIGN KEY (`tarea_id`) REFERENCES `tareas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tarea_trabajadores`
--
ALTER TABLE `tarea_trabajadores`
  ADD CONSTRAINT `fk_tarea_trabajadores_tarea` FOREIGN KEY (`tarea_id`) REFERENCES `tareas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tarea_trabajadores_trabajador` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tarea_trabajos`
--
ALTER TABLE `tarea_trabajos`
  ADD CONSTRAINT `fk_tarea_trabajos_tarea` FOREIGN KEY (`tarea_id`) REFERENCES `tareas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tarea_trabajos_trabajo` FOREIGN KEY (`trabajo_id`) REFERENCES `trabajos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `trabajadores`
--
ALTER TABLE `trabajadores`
  ADD CONSTRAINT `fk_trabajadores_user` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `trabajos`
--
ALTER TABLE `trabajos`
  ADD CONSTRAINT `fk_trabajos_user` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `trabajos_trabajadores`
--
ALTER TABLE `trabajos_trabajadores`
  ADD CONSTRAINT `trabajos_trabajadores_ibfk_1` FOREIGN KEY (`trabajo_id`) REFERENCES `trabajos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trabajos_trabajadores_ibfk_2` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_propietario` FOREIGN KEY (`propietario_id`) REFERENCES `propietarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_usuarios_trabajador` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD CONSTRAINT `fk_vehiculos_user` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
