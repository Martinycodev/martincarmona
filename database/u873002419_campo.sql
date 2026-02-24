-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 22-02-2026 a las 17:38:56
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
-- Estructura de tabla para la tabla `empresas`
--

CREATE TABLE `empresas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `dni` varchar(255) NOT NULL,
  `id_user` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `empresas`
--

INSERT INTO `empresas` (`id`, `nombre`, `dni`, `id_user`) VALUES
(0, 'Pedro Carmona Díaz', '25961919W', 4),
(1, 'Francisco José Peña Díaz', '78680834L', 4);

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
  `id_user` bigint(20) NOT NULL
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

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos`
--

CREATE TABLE `movimientos` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tipo` enum('ingreso','gasto') NOT NULL,
  `concepto` varchar(255) NOT NULL,
  `categoria` enum('personal','pago','impuestos','maquinaria','parcela','servicios','subvencion','otros') NOT NULL,
  `importe` decimal(10,2) NOT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  `trabajador_id` int(11) DEFAULT NULL,
  `vehiculo_id` int(11) DEFAULT NULL,
  `parcela_id` int(11) DEFAULT NULL,
  `estado` enum('pendiente','pagado') DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `movimientos`
--

INSERT INTO `movimientos` (`id`, `fecha`, `tipo`, `concepto`, `categoria`, `importe`, `proveedor_id`, `trabajador_id`, `vehiculo_id`, `parcela_id`, `estado`) VALUES
(20, '2025-09-01', 'gasto', 'Sueldo - September 2025', 'pago', 60.00, NULL, 27, NULL, NULL, 'pendiente'),
(21, '2025-09-24', 'ingreso', 'Ingreso tocho', 'personal', 300.00, NULL, NULL, NULL, NULL, 'pagado'),
(25, '2025-09-01', 'gasto', 'Sueldo - September 2025', 'pago', 60.00, NULL, 34, NULL, NULL, 'pendiente'),
(28, '2025-10-01', 'gasto', 'Sueldo - October 2025', 'pago', 27.69, NULL, 25, NULL, NULL, 'pendiente'),
(29, '2025-11-01', 'gasto', 'Sueldo - November 2025', 'pago', 45.00, NULL, 25, NULL, NULL, 'pendiente'),
(30, '2025-11-14', 'ingreso', 'gasto 2', 'personal', 33.00, NULL, NULL, NULL, NULL, 'pagado'),
(31, '2026-02-01', 'gasto', 'Sueldo - February 2026', 'pago', 27.69, NULL, 25, NULL, NULL, 'pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `parcelas`
--

CREATE TABLE `parcelas` (
  `nombre` varchar(2000) NOT NULL,
  `id` int(11) NOT NULL,
  `olivos` int(11) NOT NULL,
  `ubicacion` varchar(255) NOT NULL,
  `empresa` varchar(255) NOT NULL,
  `propietario` varchar(255) NOT NULL,
  `hidrante` int(11) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `id_user` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `parcelas`
--

INSERT INTO `parcelas` (`nombre`, `id`, `olivos`, `ubicacion`, `empresa`, `propietario`, `hidrante`, `descripcion`, `id_user`) VALUES
('Amarguillos (Loli)', 2, 250, 'Crta Marín', 'Pedro Carmona Díaz', 'Loli', 853, '', 4),
('Amarguillos (Paco)', 3, 200, 'Ctra Marín', 'Francisco José Peña Díaz', 'Paco', 855, '', 4),
('Camino Andujar (plantones)', 4, 206, 'Camino Andujar', 'Francisco José Peña Díaz', 'Paco', 835, '', 4),
('Camino Andújar Grande (abajo)', 5, 125, 'Camino Andujar', 'Pedro Carmona Díaz', 'Pedro', 849, '', 4),
('Camino Andújar Grande (arriba)', 6, 78, 'Camino Andujar', 'Pedro Carmona Díaz', 'Pedro', 772, '', 4),
('Camino Lopera', 7, 55, 'Camino Lopera', 'Pedro Carmona Díaz', 'Loli', 0, '', 4),
('Cañada de Pedro Díaz', 8, 145, 'Ctra Porcuna', 'Pedro Carmona Díaz', 'Loli', 0, '', 4),
('Cantarerías Pepe', 9, 0, '', 'Catalina Carmona (Pepe)', '', 0, '', 4),
('Cáritas / grilleros', 10, 230, 'Camino grilleros', 'Pedro Carmona Díaz', 'Caritas', 0, '', 4),
('Cochera', 11, 0, '', '', '', 0, '', 4),
('Coquijo', 12, 41, 'Camino de las 3 fuentes', 'Francisco José Peña Díaz', 'Paco', 0, '', 4),
('Cuesta de Juan Cobo 1/3(Paco)', 13, 233, 'Camino Andújar', 'Francisco José Peña Díaz', 'Paco', 841, '', 4),
('Cuesta de Juan Cobo 2/3(Pedro)', 14, 466, 'Camino Andújar', 'Pedro Carmona Díaz', 'Pedro', 841, '', 4),
('Estaca del Abuelo (Paco)', 15, 150, 'Camino las rellertas', 'Francisco José Peña Díaz', 'Paco', 0, '', 4),
('Estacá del Abuelo (Pedro)', 16, 64, 'camino de las rellertas', 'Pedro Carmona Díaz', 'Pedro', 0, '', 4),
('Garría / marín', 17, 100, 'Camino del Marín', 'Pedro Carmona Díaz', 'Pedro', 0, '23007A00100060QW', 4),
('Jurado', 18, 250, 'Camino arroyo Arjonilla', 'Francisco José Peña Díaz', 'Paco', 674, '', 4),
('La Reguera (Plantones)', 19, 400, 'Camino de Praena', 'Pedro Carmona Díaz', 'Pedro', 0, '', 4),
('Las cajas (San Jose)', 20, 738, 'Crta Lopera', 'Jose Ángel de la Brena Ruano', 'Jose Angel', 0, '', 4),
('Los Caranzos (Paco)', 21, 200, 'Ctra Arjona', 'Francisco José Peña Díaz', 'Paco', 0, '', 4),
('Los Caranzos (Pedro)', 22, 200, 'Ctra Arjona', 'Pedro Carmona Díaz', 'Pedro', 0, '', 4),
('Los torneros', 23, 450, 'Camino Andújar', 'Pedro Carmona Díaz', 'Cristobal', 865, '', 4),
('Majuelo', 24, 70, 'Camino fuente del escribano', 'Francisco José Peña Díaz', 'Paco', 0, '', 4),
('Monguía (Ctra Marmolejo)', 25, 150, 'Ctra marmolejo', 'Pedro Carmona Díaz', 'Pedro', 0, '', 4),
('Monte Pepe', 26, 0, '', 'Catalina Carmona (Pepe)', '', 0, '', 4),
('Oficina', 27, 0, '', '', '', 0, '', 4),
('Peña Rubias (Pedro)', 28, 170, 'Camino de fuente escribano', 'Pedro Carmona Díaz', 'Loli', 0, '', 4),
('Peñarubias (acantilado)', 29, 70, 'Camino fuente del escribano', 'Francisco José Peña Díaz', 'Paco', 0, '', 4),
('Peñarubias (Paco)', 30, 100, 'Camino fuente del escribano', 'Francisco José Peña Díaz', 'Paco', 0, '', 4),
('Platerilla Estaca del abuelo (pequeño)', 31, 45, 'Camino de las rellertas', 'Pedro Carmona Díaz', 'Pedro', 0, '', 4),
('Pocico (Loli)', 32, 150, 'Camino Pilar', 'Pedro Carmona Díaz', 'Loli', 604, '', 4),
('Pocico (Paco)', 33, 120, 'Camino Pilar', 'Francisco José Peña Díaz', 'Paco', 605, '', 4),
('Pocico (Pedro)', 34, 141, 'Camino Pilar', 'Pedro Carmona Díaz', 'Pedro', 605, '', 4),
('Pocico (Pedro) plantones', 35, 200, 'Camino pilar', 'Pedro Carmona Díaz', 'Pedro', 605, '', 4),
('Pocico tierra', 36, 0, '', 'Pedro Carmona Díaz', '', 0, '', 4),
('PradoPortales Pepe', 37, 0, '', 'Catalina Carmona (Pepe)', '', 0, '', 4),
('Praena Plantones grandes', 38, 280, 'Camino de Praena', 'Pedro Carmona Díaz', 'Pedro', 0, '', 4),
('Santa Cruz (San Jose)', 39, 550, 'Ctra Lopera', 'Jose Ángel de la Brena Ruano', 'Jose Angel', 0, '', 4),
('Silveria - caranzos (Paco)', 40, 0, 'Ctra Arjona', 'Francisco José Peña Díaz', 'Paco', 0, '', 4),
('Sotelo Arjona', 41, 400, 'Ctra Arjona', 'Pedro Carmona Díaz', 'Cristobal', 0, '', 4),
('Aguirre', 42, 72, 'Camino del pilar', 'Pedro Carmona Díaz', 'Loli', 0, '', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `parcela_empresas`
--

CREATE TABLE `parcela_empresas` (
  `parcela_id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL
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
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `id_user` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `riegos`
--

CREATE TABLE `riegos` (
  `id` int(11) NOT NULL,
  `hidrante` varchar(50) DEFAULT NULL,
  `propiedad` varchar(255) DEFAULT NULL,
  `cantidad_fin` float DEFAULT NULL,
  `cantidad_ini` float DEFAULT NULL,
  `dias` int(11) DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `fecha_ini` date DEFAULT NULL,
  `total_m3` float DEFAULT NULL,
  `id_user` bigint(20) NOT NULL DEFAULT 4
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

--
-- Volcado de datos para la tabla `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('1uYDEBdI4aLlCZwzUCX3ngSo900bo6nQ3Pcx7mbV', 0, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoib3ZYZjhKRTNad0VwaXBzbGh2a3RuVXVRN2J3amZpaTN4TEVocG5XOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHA6Ly9sb2NhbGhvc3QvY2FtcG8vcHVibGljL3RyYWJhamFkb3JlcyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjA7fQ==', 1746882950),
('Kc38JDPTdhzOIJdTNF6g4BHq3W3LhB2S6zKV4NYx', 0, '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Mobile Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQ1dRY0I1bEJVemE1MDlZNENHZXk1bVRqM2czS1RhU25wVmJFeFNhNCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly9sb2NhbGhvc3QvY2FtcG8vcHVibGljL2Rhc2hib2FyZCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjA7fQ==', 1746866922);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

CREATE TABLE `tareas` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `titulo` varchar(150) NOT NULL DEFAULT '',
  `descripcion` varchar(255) DEFAULT NULL,
  `horas` decimal(5,2) DEFAULT 0.00 COMMENT 'Horas totales de la tarea',
  `updated_at` date NOT NULL,
  `created_at` date NOT NULL,
  `id_user` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tareas`
--

INSERT INTO `tareas` (`id`, `fecha`, `titulo`, `descripcion`, `horas`, `updated_at`, `created_at`, `id_user`) VALUES
(3, '2025-05-16', 'nada', 'nada', 4.00, '2025-05-08', '2025-05-08', 4),
(5, '2025-05-14', 'adfadfadfa', 'adfadfadfa', 4.00, '2025-05-10', '2025-05-10', 4),
(6, '2025-08-12', 'hola el 12', 'hola el 12', 4.00, '2025-08-30', '0000-00-00', 4),
(7, '2025-08-16', 'fvczzv', 'fvczzv', 3.00, '2025-08-30', '2025-08-16', 4),
(14, '2025-08-14', 'arreglar averia', 'arreglar averia', 3.00, '2025-08-16', '2025-08-16', 4),
(16, '2025-08-20', 'afdsfadaf', 'afdsfadaf', 1.00, '2025-08-20', '2025-08-20', 4),
(17, '2025-08-20', 'adfadf pedro', 'adfadf pedro', 2.00, '2025-08-20', '2025-08-20', 4),
(18, '2025-08-13', 'trabajos', 'trabajos', 3.00, '2025-08-20', '2025-08-20', 4),
(19, '2025-08-08', 'desnate', 'desnate', 3.00, '2025-08-20', '2025-08-20', 4),
(20, '2025-08-22', 'Prueba de hoy', 'Prueba de hoy', 3.00, '2025-08-22', '2025-08-22', 4),
(21, '2025-08-22', 'segunda prueba de hoy', 'segunda prueba de hoy', 3.00, '2025-08-30', '2025-08-22', 4),
(22, '2025-08-18', 'nueva', 'nueva', 3.00, '2025-08-22', '2025-08-22', 4),
(23, '2025-08-02', 'hola', 'hola', 3.00, '2025-08-30', '2025-08-30', 4),
(24, '2025-08-04', 'Quitar vareta en el campo', 'Quitar vareta en el campo', 4.00, '2025-08-30', '2025-08-30', 4),
(26, '2025-08-05', 'prueba de fuego', 'prueba de fuego', 3.00, '2025-08-30', '2025-08-30', 4),
(42, '2025-09-19', 'poner 1 parcela', 'poner 1 parcela', 1.00, '2025-09-24', '2025-09-24', 4),
(60, '2025-09-24', 'vareta', 'vareta', 4.00, '2025-09-24', '2025-09-24', 4),
(61, '2025-09-24', 'Prueba de 2 trabajadores', 'Prueba de 2 trabajadores', 6.50, '2025-09-24', '2025-09-24', 4),
(62, '2025-09-22', 'Elmakkid', 'Elmakkid', 2.00, '2025-09-24', '2025-09-24', 4),
(66, '2025-09-25', 'arreglar plant', 'arreglar plant', 3.00, '2025-09-25', '2025-09-25', 4),
(67, '2025-09-25', 'arreglar plant', 'arreglar plant', 6.50, '2025-09-25', '2025-09-25', 4),
(71, '2025-10-22', 'holas', 'holas', 3.00, '2025-10-22', '2025-10-22', 4),
(72, '2025-11-13', 'cosas', 'cosas', 3.00, '2025-11-13', '2025-11-13', 4),
(73, '2026-02-14', 'fuimos a cortar con la motosierra', 'fuimos a cortar con la motosierra', 0.00, '2026-02-22', '2026-02-09', 4),
(74, '2026-02-11', 'limpiar la cochera', 'limpiar la cochera', 3.00, '2026-02-22', '2026-02-09', 4),
(75, '2026-02-09', 'ir al riego', 'ir al riego', 3.00, '2026-02-09', '2026-02-09', 4),
(76, '2026-02-22', 'Prueba de fuego', 'hola cara cola', 2.00, '2026-02-22', '2026-02-22', 4),
(77, '2026-02-18', '', '', 0.00, '2026-02-22', '2026-02-22', 4);

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

--
-- Volcado de datos para la tabla `tarea_parcelas`
--

INSERT INTO `tarea_parcelas` (`id`, `tarea_id`, `parcela_id`, `superficie_trabajada`, `created_at`) VALUES
(3, 26, 33, NULL, '2025-08-30 14:19:09'),
(5, 14, 21, NULL, '2025-08-30 14:22:59'),
(6, 16, 42, NULL, '2025-08-30 14:22:59'),
(7, 17, 36, NULL, '2025-08-30 14:22:59'),
(8, 18, 4, NULL, '2025-08-30 14:22:59'),
(9, 19, 7, NULL, '2025-08-30 14:22:59'),
(10, 20, 12, NULL, '2025-08-30 14:22:59'),
(12, 22, 12, NULL, '2025-08-30 14:22:59'),
(13, 23, 34, NULL, '2025-08-30 14:22:59'),
(29, 21, 34, NULL, '2025-08-30 14:56:39'),
(30, 24, 32, NULL, '2025-08-30 14:56:54'),
(32, 7, 2, NULL, '2025-08-30 16:12:00'),
(44, 42, 17, NULL, '2025-09-24 08:20:06'),
(54, 60, 33, NULL, '2025-09-24 10:58:30'),
(55, 61, 42, NULL, '2025-09-24 11:03:41'),
(56, 62, 7, NULL, '2025-09-24 11:53:12'),
(58, 66, 32, NULL, '2025-09-25 09:38:19'),
(59, 67, 42, NULL, '2025-09-25 09:39:04'),
(61, 71, 33, NULL, '2025-10-22 10:53:03'),
(62, 72, 34, NULL, '2025-11-13 21:09:27'),
(63, 73, 42, NULL, '2026-02-09 14:23:13'),
(66, 74, 11, NULL, '2026-02-09 14:38:22'),
(67, 75, 25, NULL, '2026-02-09 14:39:52'),
(68, 76, 4, NULL, '2026-02-22 15:04:47'),
(69, 73, 6, NULL, '2026-02-22 17:36:14'),
(70, 76, 2, NULL, '2026-02-22 17:36:37');

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

--
-- Volcado de datos para la tabla `tarea_trabajadores`
--

INSERT INTO `tarea_trabajadores` (`id`, `tarea_id`, `trabajador_id`, `horas_asignadas`, `created_at`) VALUES
(1, 3, 25, 4.00, '2025-08-30 13:55:56'),
(2, 5, 26, 4.00, '2025-08-30 13:55:56'),
(7, 17, 26, 2.00, '2025-08-30 13:55:56'),
(8, 18, 32, 3.00, '2025-08-30 13:55:56'),
(9, 19, 27, 3.00, '2025-08-30 13:55:56'),
(10, 20, 25, 3.00, '2025-08-30 13:55:56'),
(12, 22, 39, 3.00, '2025-08-30 13:55:56'),
(16, 26, 25, 3.00, '2025-08-30 14:19:09'),
(31, 21, 25, 3.00, '2025-08-30 14:56:39'),
(32, 21, 40, 3.00, '2025-08-30 14:56:39'),
(33, 24, 26, 4.00, '2025-08-30 14:56:54'),
(36, 7, 40, 3.00, '2025-08-30 16:11:59'),
(37, 7, 45, 3.00, '2025-08-30 16:11:59'),
(50, 42, 40, 1.00, '2025-09-24 08:20:06'),
(68, 60, 25, 4.00, '2025-09-24 10:58:30'),
(69, 61, 26, 6.50, '2025-09-24 11:03:41'),
(70, 61, 27, 6.50, '2025-09-24 11:03:41'),
(71, 62, 28, 2.00, '2025-09-24 11:53:11'),
(75, 66, 34, 3.00, '2025-09-25 09:38:18'),
(76, 67, 34, 6.50, '2025-09-25 09:39:04'),
(81, 71, 25, 3.00, '2025-10-22 10:53:03'),
(82, 72, 25, 3.00, '2025-11-13 21:09:27'),
(83, 73, 25, 0.00, '2026-02-09 14:23:13'),
(85, 74, 25, 3.00, '2026-02-09 14:38:21'),
(86, 75, 25, 3.00, '2026-02-09 14:39:52'),
(87, 76, 43, 2.00, '2026-02-22 15:04:44');

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

--
-- Volcado de datos para la tabla `tarea_trabajos`
--

INSERT INTO `tarea_trabajos` (`id`, `tarea_id`, `trabajo_id`, `horas_trabajo`, `precio_hora`, `created_at`) VALUES
(1, 3, 1, 4.00, NULL, '2025-08-30 13:55:56'),
(2, 5, 1, 4.00, NULL, '2025-08-30 13:55:56'),
(5, 14, 3, 3.00, NULL, '2025-08-30 13:55:56'),
(6, 16, 1, 1.00, NULL, '2025-08-30 13:55:56'),
(7, 17, 1, 2.00, NULL, '2025-08-30 13:55:56'),
(8, 18, 1, 3.00, NULL, '2025-08-30 13:55:56'),
(9, 19, 13, 3.00, NULL, '2025-08-30 13:55:56'),
(10, 20, 1, 3.00, NULL, '2025-08-30 13:55:56'),
(12, 22, 7, 3.00, NULL, '2025-08-30 13:55:56'),
(16, 26, 4, 3.00, NULL, '2025-08-30 14:19:09'),
(18, 23, 4, 3.00, NULL, '2025-08-30 14:22:59'),
(30, 21, 6, 3.00, NULL, '2025-08-30 14:56:39'),
(31, 24, 4, 4.00, NULL, '2025-08-30 14:56:54'),
(33, 7, 1, 3.00, NULL, '2025-08-30 16:12:00'),
(42, 42, 34, 1.00, NULL, '2025-09-24 08:20:06'),
(60, 60, 4, 4.00, NULL, '2025-09-24 10:58:30'),
(61, 61, 4, 6.50, NULL, '2025-09-24 11:03:41'),
(62, 62, 4, 2.00, NULL, '2025-09-24 11:53:12'),
(66, 66, 9, 3.00, NULL, '2025-09-25 09:38:19'),
(67, 67, 4, 6.50, NULL, '2025-09-25 09:39:04'),
(72, 71, 4, 3.00, NULL, '2025-10-22 10:53:03'),
(73, 72, 29, 3.00, NULL, '2025-11-13 21:09:27'),
(74, 73, 36, 0.00, NULL, '2026-02-09 14:23:13'),
(75, 74, 51, 3.00, NULL, '2026-02-09 14:38:22'),
(76, 75, 1, 3.00, NULL, '2026-02-09 14:39:52'),
(77, 76, 57, 2.00, NULL, '2026-02-22 15:04:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajadores`
--

CREATE TABLE `trabajadores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(2000) NOT NULL,
  `dni` varchar(255) DEFAULT NULL,
  `ss` varchar(255) DEFAULT NULL,
  `alta_ss` date DEFAULT NULL,
  `cuadrilla` tinyint(1) NOT NULL DEFAULT 0,
  `id_user` bigint(20) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL COMMENT 'Ruta de la foto del trabajador',
  `apellidos` varchar(100) DEFAULT NULL COMMENT 'Apellidos del trabajador',
  `email` varchar(100) DEFAULT NULL COMMENT 'Email del trabajador',
  `direccion` text DEFAULT NULL COMMENT 'Dirección del trabajador',
  `especialidad` varchar(100) DEFAULT NULL COMMENT 'Especialidad del trabajador',
  `fecha_contratacion` date DEFAULT NULL COMMENT 'Fecha de contratación',
  `estado` enum('activo','inactivo') DEFAULT 'activo' COMMENT 'Estado del trabajador'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `trabajadores`
--

INSERT INTO `trabajadores` (`id`, `nombre`, `dni`, `ss`, `alta_ss`, `cuadrilla`, `id_user`, `telefono`, `foto`, `apellidos`, `email`, `direccion`, `especialidad`, `fecha_contratacion`, `estado`) VALUES
(25, 'Martín Carmona López', '53598764R', '231011344450', NULL, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo'),
(26, 'Pedro Carmona Díaz', '25961919W', NULL, NULL, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo'),
(27, 'Mohammed Louar', '53914211A', NULL, NULL, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo'),
(28, 'ElMakki Louaar', 'X839004P', NULL, NULL, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo'),
(29, 'Rabha Errafai', 'X5917993R', '231038134436', NULL, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo'),
(30, 'Rafaela Escobedo Fernández', '26236788K', NULL, NULL, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo'),
(31, 'Antonio Fernández Cortés', '26001221C', NULL, NULL, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo'),
(32, 'Ignacio Ramirez Serrano', '52547045A', NULL, NULL, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo'),
(33, 'Radouane Louar', '53917977C', 'AN1079493610', NULL, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo'),
(34, 'Jose Tomás Navarro Casado', '53910245Q', '231035142489', NULL, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo'),
(35, 'Mateo Victor García', '53593089F', '231036938306', NULL, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo'),
(36, 'Juana Maria Navarro Casado', '52559571V', '231035035991', NULL, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo'),
(37, 'Manuel Navarro Casado', '53596580W', '231035142388', NULL, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo'),
(38, 'Maria del Rocio Quero Sabalete', '53594744Y', '231034291721', NULL, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo'),
(39, 'Miguel Angel Raigón', NULL, NULL, NULL, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo'),
(40, 'Juan Antonio Espino', NULL, NULL, NULL, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo'),
(41, 'Andrés cooperativa', NULL, NULL, NULL, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo'),
(42, 'José Maria Maño', NULL, NULL, NULL, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo'),
(43, 'Francisco Javier Ruiz Lara', '53911180P', '231048738657', NULL, 0, 4, '', NULL, NULL, NULL, NULL, NULL, NULL, 'activo'),
(44, 'Manuel Porrillo', NULL, NULL, NULL, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo'),
(45, 'Miguel Angel Porrillo', NULL, NULL, NULL, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo'),
(46, 'Luis Porrillo', NULL, NULL, NULL, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo'),
(47, 'Miguel Porrillo Vecino', NULL, NULL, NULL, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajos`
--

CREATE TABLE `trabajos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `precio_hora` float DEFAULT NULL,
  `id_user` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `trabajos`
--

INSERT INTO `trabajos` (`id`, `nombre`, `descripcion`, `precio_hora`, `id_user`) VALUES
(1, 'Abrir Riego', 'abrir hidrante', 9.23, 4),
(2, 'Echar a andar riego', '', 0, 4),
(3, 'Estirar gomas', '', 0, 4),
(4, 'Quitar vareta', '', 9.23, 4),
(6, 'Echar abono con riego', '', 1, 4),
(7, 'Desbrozar con tractor', '', 0, 4),
(8, 'Pasar grada pinches', '', 0, 4),
(9, 'Arreglar Plantones', '', 0, 4),
(10, 'Arreglo tractor', '', 0, 4),
(11, 'Quitar hierba con desbrozadora', '', 0, 4),
(12, 'Recoger o acordonar desnate', '', 9.23, 4),
(13, 'Cortar desnate', '', 0, 4),
(14, 'Picar desnate', '', 0, 4),
(15, 'Pasar itv maquinaria', '', 0, 4),
(16, 'Papeleo y burocracia', '', 0, 4),
(17, 'Pasar Rulo tractor', '', 0, 4),
(18, 'Quemar vareta', '', 0, 4),
(19, 'Pasar Rastra Tractor', '', 0, 4),
(20, 'Soplar suelo sopladora', '', 9.23, 4),
(21, 'Soplar suelo tractor', '', 0, 4),
(23, 'Ordenar', '', 0, 4),
(24, 'Formación', '', 0, 4),
(25, 'Recoger aceituna', '', 0, 4),
(26, 'Compras', '', 0, 4),
(27, 'Preparar cuba', '', 0, 4),
(28, 'Echar sulfato con atomizadora', '', NULL, 4),
(29, 'Echar estiercol', '', NULL, 4),
(30, 'Echar herbicida con tractor', '', NULL, 4),
(31, 'Hacer suelos con mano hierro', '', NULL, 4),
(32, 'Echar herbicida con mochila', '', NULL, 4),
(33, 'Echar hoja con tractor', '', NULL, 4),
(34, 'Cuentas', '', NULL, 4),
(35, 'Hacer Cuentas', '', NULL, 4),
(36, 'Corta', '', NULL, 4),
(37, 'Echar abono con abonadora', '', NULL, 4),
(38, 'Arreglar Máquinas', '', NULL, 4),
(39, 'Escamujar', '', NULL, 4),
(40, 'Cargar palos', '', NULL, 4),
(41, 'Echar abono jarrillos', '', NULL, 4),
(42, 'llevar remolque palos', '', NULL, 4),
(43, 'Acordonar Ramón', '', NULL, 4),
(44, 'Picar Ramón', '', NULL, 4),
(45, 'Corta plantones', '', NULL, 4),
(46, 'medir terreno', '', NULL, 4),
(47, 'Recoger raigones', '', NULL, 4),
(48, 'Preparar mochilas', '', NULL, 4),
(49, 'Cargar paja', '', NULL, 4),
(50, 'recoger gomas', '', NULL, 4),
(51, 'Limpiar cuadra', '', NULL, 4),
(52, 'Mantenimiento vehiculos', '', NULL, 4),
(53, 'Regar plantones', '', NULL, 4),
(54, 'Plantar plantones', '', NULL, 4),
(55, 'Pasar Grada discos tractor', '', NULL, 4),
(56, 'Abrir zanja retro', '', NULL, 4),
(57, 'Arreglar avería riego', '', NULL, 4),
(58, 'Cerrar Riego', '', NULL, 4);

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

--
-- Volcado de datos para la tabla `trabajos_trabajadores`
--

INSERT INTO `trabajos_trabajadores` (`id`, `trabajo_id`, `trabajador_id`, `precio_tarea`, `pagado`, `created_at`, `updated_at`) VALUES
(1, 1, 25, 9.23, 0.00, '2025-09-24 10:25:30', '2025-09-24 10:25:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `name`, `email`, `password`, `updated_at`, `created_at`) VALUES
(4, 'Martin', 'martin.carmona.lopez@gmail.com', '$2y$10$9qBIpzfttNEkdhMgMcMYCuKbNTMvzco.nJz/Os69h1eBpvMrv2isC', NULL, '2025-08-14 15:28:33');

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
  `id_user` bigint(20) NOT NULL
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
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `id user` (`id_user`);

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
  ADD KEY `fk_mov_parcela` (`parcela_id`);

--
-- Indices de la tabla `parcelas`
--
ALTER TABLE `parcelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_parcelas_user` (`id_user`),
  ADD KEY `id` (`id`);

--
-- Indices de la tabla `parcela_empresas`
--
ALTER TABLE `parcela_empresas`
  ADD PRIMARY KEY (`parcela_id`,`empresa_id`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_proveedores_user` (`id_user`);

--
-- Indices de la tabla `riegos`
--
ALTER TABLE `riegos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_riegos_user` (`id_user`);

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
  ADD KEY `fk_tareas_user` (`id_user`);

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
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT de la tabla `herramientas`
--
ALTER TABLE `herramientas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `movimientos`
--
ALTER TABLE `movimientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `parcelas`
--
ALTER TABLE `parcelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT de la tabla `tarea_imagenes`
--
ALTER TABLE `tarea_imagenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tarea_parcelas`
--
ALTER TABLE `tarea_parcelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT de la tabla `tarea_trabajadores`
--
ALTER TABLE `tarea_trabajadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT de la tabla `tarea_trabajos`
--
ALTER TABLE `tarea_trabajos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT de la tabla `trabajadores`
--
ALTER TABLE `trabajadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de la tabla `trabajos`
--
ALTER TABLE `trabajos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT de la tabla `trabajos_trabajadores`
--
ALTER TABLE `trabajos_trabajadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD CONSTRAINT `id user` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id`);

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
-- Filtros para la tabla `parcelas`
--
ALTER TABLE `parcelas`
  ADD CONSTRAINT `fk_parcelas_user` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD CONSTRAINT `fk_proveedores_user` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id`);

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
-- Filtros para la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD CONSTRAINT `fk_vehiculos_user` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
