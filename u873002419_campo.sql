-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-09-2025 a las 07:39:41
-- Versión del servidor: 10.11.10-MariaDB-log
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
  `nombre` int(11) NOT NULL,
  `dni` int(11) NOT NULL,
  `id_user` bigint(20) NOT NULL
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
  `categoria` enum('personal','gasto','impuestos','maquinaria','parcela','servicios','subvencion','otros') NOT NULL,
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
(1, '2025-09-05', 'ingreso', 'hola', 'personal', 0.09, NULL, NULL, NULL, NULL, 'pendiente'),
(2, '2025-09-05', 'ingreso', 'pago a pepe', 'servicios', 7777.00, NULL, NULL, NULL, NULL, 'pendiente'),
(3, '2025-09-05', 'gasto', 'hola', 'personal', 300.00, NULL, NULL, NULL, NULL, 'pagado');

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
  `dueño` varchar(255) NOT NULL,
  `hidrante` int(11) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `id_user` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `parcelas`
--

INSERT INTO `parcelas` (`nombre`, `id`, `olivos`, `ubicacion`, `empresa`, `dueño`, `hidrante`, `descripcion`, `id_user`) VALUES
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
  `descripcion` varchar(255) DEFAULT NULL,
  `horas` decimal(5,2) DEFAULT 0.00 COMMENT 'Horas totales de la tarea',
  `updated_at` date NOT NULL,
  `created_at` date NOT NULL,
  `id_user` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tareas`
--

INSERT INTO `tareas` (`id`, `fecha`, `descripcion`, `horas`, `updated_at`, `created_at`, `id_user`) VALUES
(3, '2025-05-16', 'nada', 4.00, '2025-05-08', '2025-05-08', 4),
(5, '2025-05-14', 'adfadfadfa', 4.00, '2025-05-10', '2025-05-10', 4),
(6, '2025-08-12', 'hola el 12', 4.00, '2025-08-30', '0000-00-00', 4),
(7, '2025-08-16', 'fvczzv', 3.00, '2025-08-30', '2025-08-16', 4),
(14, '2025-08-14', 'arreglar averia', 3.00, '2025-08-16', '2025-08-16', 4),
(16, '2025-08-20', 'afdsfadaf', 1.00, '2025-08-20', '2025-08-20', 4),
(17, '2025-08-20', 'adfadf pedro', 2.00, '2025-08-20', '2025-08-20', 4),
(18, '2025-08-13', 'trabajos', 3.00, '2025-08-20', '2025-08-20', 4),
(19, '2025-08-08', 'desnate', 3.00, '2025-08-20', '2025-08-20', 4),
(20, '2025-08-22', 'Prueba de hoy', 3.00, '2025-08-22', '2025-08-22', 4),
(21, '2025-08-22', 'segunda prueba de hoy', 3.00, '2025-08-30', '2025-08-22', 4),
(22, '2025-08-18', 'nueva', 3.00, '2025-08-22', '2025-08-22', 4),
(23, '2025-08-02', 'hola', 3.00, '2025-08-30', '2025-08-30', 4),
(24, '2025-08-04', 'Quitar vareta en el campo', 4.00, '2025-08-30', '2025-08-30', 4),
(26, '2025-08-05', 'prueba de fuego', 3.00, '2025-08-30', '2025-08-30', 4),
(27, '2025-08-05', 'prueba de fuego', 3.00, '2025-08-30', '2025-08-30', 4),
(30, '2025-08-31', 'prueba de mañana', 3.00, '2025-08-30', '2025-08-30', 4),
(33, '2025-08-29', 'prueba de ayer', 3.00, '2025-08-30', '2025-08-30', 4),
(34, '2025-08-30', 'Prueba de hoy', 1.00, '2025-08-30', '2025-08-30', 4),
(35, '2025-09-01', 'Prueba de fuego', 2.00, '2025-08-30', '2025-08-30', 4),
(36, '2025-08-30', 'creado en tareas', 1.00, '2025-08-30', '2025-08-30', 4),
(37, '2025-09-05', 'quitar vareta', 2.00, '2025-09-05', '2025-09-05', 4),
(38, '2025-09-08', 'fue guay', 3.00, '2025-09-08', '2025-09-08', 4);

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
(4, 27, 33, NULL, '2025-08-30 14:19:10'),
(5, 14, 21, NULL, '2025-08-30 14:22:59'),
(6, 16, 42, NULL, '2025-08-30 14:22:59'),
(7, 17, 36, NULL, '2025-08-30 14:22:59'),
(8, 18, 4, NULL, '2025-08-30 14:22:59'),
(9, 19, 7, NULL, '2025-08-30 14:22:59'),
(10, 20, 12, NULL, '2025-08-30 14:22:59'),
(12, 22, 12, NULL, '2025-08-30 14:22:59'),
(13, 23, 34, NULL, '2025-08-30 14:22:59'),
(22, 30, 42, NULL, '2025-08-30 14:32:02'),
(25, 33, 9, NULL, '2025-08-30 14:34:00'),
(26, 34, 24, NULL, '2025-08-30 14:37:36'),
(28, 35, 2, NULL, '2025-08-30 14:50:14'),
(29, 21, 34, NULL, '2025-08-30 14:56:39'),
(30, 24, 32, NULL, '2025-08-30 14:56:54'),
(32, 7, 2, NULL, '2025-08-30 16:12:00'),
(33, 36, 42, NULL, '2025-08-30 16:17:02'),
(34, 37, 28, NULL, '2025-09-05 20:16:29'),
(35, 38, 42, NULL, '2025-09-08 21:48:12');

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
(17, 27, 25, 3.00, '2025-08-30 14:19:09'),
(21, 30, 25, 3.00, '2025-08-30 14:32:02'),
(22, 30, 26, 3.00, '2025-08-30 14:32:02'),
(26, 33, 40, 3.00, '2025-08-30 14:34:00'),
(27, 34, 25, 1.00, '2025-08-30 14:37:36'),
(29, 35, 26, 2.00, '2025-08-30 14:50:14'),
(30, 35, 25, 2.00, '2025-08-30 14:50:14'),
(31, 21, 25, 3.00, '2025-08-30 14:56:39'),
(32, 21, 40, 3.00, '2025-08-30 14:56:39'),
(33, 24, 26, 4.00, '2025-08-30 14:56:54'),
(36, 7, 40, 3.00, '2025-08-30 16:11:59'),
(37, 7, 45, 3.00, '2025-08-30 16:11:59'),
(38, 36, 25, 1.00, '2025-08-30 16:17:02'),
(39, 37, 27, 2.00, '2025-09-05 20:16:29'),
(40, 37, 34, 2.00, '2025-09-05 20:16:29'),
(41, 38, 25, 3.00, '2025-09-08 21:48:12');

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
(17, 27, 4, 3.00, NULL, '2025-08-30 14:19:10'),
(18, 23, 4, 3.00, NULL, '2025-08-30 14:22:59'),
(23, 30, 1, 3.00, NULL, '2025-08-30 14:32:02'),
(26, 33, 14, 3.00, NULL, '2025-08-30 14:34:01'),
(27, 34, 57, 1.00, NULL, '2025-08-30 14:37:36'),
(29, 35, 18, 2.00, NULL, '2025-08-30 14:50:14'),
(30, 21, 6, 3.00, NULL, '2025-08-30 14:56:39'),
(31, 24, 4, 4.00, NULL, '2025-08-30 14:56:54'),
(33, 7, 1, 3.00, NULL, '2025-08-30 16:12:00'),
(34, 36, 34, 1.00, NULL, '2025-08-30 16:17:02'),
(35, 37, 4, 2.00, NULL, '2025-09-05 20:16:29'),
(36, 38, 1, 3.00, NULL, '2025-09-08 21:48:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajadores`
--

CREATE TABLE `trabajadores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(2000) NOT NULL,
  `dni` varchar(255) DEFAULT NULL,
  `ss` varchar(255) DEFAULT NULL,
  `id_user` bigint(20) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `trabajadores`
--

INSERT INTO `trabajadores` (`id`, `nombre`, `dni`, `ss`, `id_user`, `telefono`) VALUES
(25, 'Martín Carmona López', '53598764R', '231011344450', 4, NULL),
(26, 'Pedro Carmona Díaz', '25961919W', NULL, 4, NULL),
(27, 'Mohammed Louar', '53914211A', NULL, 4, NULL),
(28, 'ElMakki Louaar', 'X839004P', NULL, 4, NULL),
(29, 'Rabha Errafai', 'X5917993R', '231038134436', 4, NULL),
(30, 'Rafaela Escobedo Fernández', '26236788K', NULL, 4, NULL),
(31, 'Antonio Fernández Cortés', '26001221C', NULL, 4, NULL),
(32, 'Ignacio Ramirez Serrano', '52547045A', NULL, 4, NULL),
(33, 'Radouane Louar', '53917977C', 'AN1079493610', 4, NULL),
(34, 'Jose Tomás Navarro Casado', '53910245Q', '231035142489', 4, NULL),
(35, 'Mateo Victor García', '53593089F', '231036938306', 4, NULL),
(36, 'Juana Maria Navarro Casado', '52559571V', '231035035991', 4, NULL),
(37, 'Manuel Navarro Casado', '53596580W', '231035142388', 4, NULL),
(38, 'Maria del Rocio Quero Sabalete', '53594744Y', '231034291721', 4, NULL),
(39, 'Miguel Angel Raigón', NULL, NULL, 4, NULL),
(40, 'Juan Antonio Espino', NULL, NULL, 4, NULL),
(41, 'Andrés cooperativa', NULL, NULL, 4, NULL),
(42, 'José Maria Maño', NULL, NULL, 4, NULL),
(43, 'Francisco Javier Ruiz Lara', '53911180P', '231048738657', 4, ''),
(44, 'Manuel Porrillo', NULL, NULL, 4, NULL),
(45, 'Miguel Angel Porrillo', NULL, NULL, 4, NULL),
(46, 'Luis Porrillo', NULL, NULL, 4, NULL),
(47, 'Miguel Porrillo Vecino', NULL, NULL, 4, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajos`
--

CREATE TABLE `trabajos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `id_user` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `trabajos`
--

INSERT INTO `trabajos` (`id`, `nombre`, `id_user`) VALUES
(1, 'Dar vuelta Riego', 4),
(2, 'Echar a andar riego', 4),
(3, 'Estirar gomas', 4),
(4, 'Quitar vareta', 4),
(6, 'Echar abono con riego', 4),
(7, 'Desbrozar con tractor', 4),
(8, 'Pasar grada pinches', 4),
(9, 'Arreglar Plantones', 4),
(10, 'Arreglo tractor', 4),
(11, 'Quitar hierba con desbrozadora', 4),
(12, 'Recoger o acordonar desnate', 4),
(13, 'Cortar desnate', 4),
(14, 'Picar desnate', 4),
(15, 'Pasar itv maquinaria', 4),
(16, 'Papeleo y burocracia', 4),
(17, 'Pasar Rulo tractor', 4),
(18, 'Quemar vareta', 4),
(19, 'Pasar Rastra Tractor', 4),
(20, 'Soplar suelo sopladora', 4),
(21, 'Soplar suelo tractor', 4),
(23, 'Ordenar', 4),
(24, 'Formación', 4),
(25, 'Recoger aceituna', 4),
(26, 'Compras', 4),
(27, 'Preparar cuba', 4),
(28, 'Echar sulfato con atomizadora', 4),
(29, 'Echar estiercol', 4),
(30, 'Echar herbicida con tractor', 4),
(31, 'Hacer suelos con mano hierro', 4),
(32, 'Echar herbicida con mochila', 4),
(33, 'Echar hoja con tractor', 4),
(34, 'Cuentas', 4),
(35, 'Hacer Cuentas', 4),
(36, 'Corta', 4),
(37, 'Echar abono con abonadora', 4),
(38, 'Arreglar Máquinas', 4),
(39, 'Escamujar', 4),
(40, 'Cargar palos', 4),
(41, 'Echar abono jarrillos', 4),
(42, 'llevar remolque palos', 4),
(43, 'Acordonar Ramón', 4),
(44, 'Picar Ramón', 4),
(45, 'Corta plantones', 4),
(46, 'medir terreno', 4),
(47, 'Recoger raigones', 4),
(48, 'Preparar mochilas', 4),
(49, 'Cargar paja', 4),
(50, 'recoger gomas', 4),
(51, 'Limpiar cuadra', 4),
(52, 'Mantenimiento vehiculos', 4),
(53, 'Regar plantones', 4),
(54, 'Plantar plantones', 4),
(55, 'Pasar Grada discos tractor', 4),
(56, 'Abrir zanja retro', 4),
(57, 'Arreglar avería riego', 4);

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
  ADD KEY `fk_trabajadores_user` (`id_user`);

--
-- Indices de la tabla `trabajos`
--
ALTER TABLE `trabajos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_trabajos_user` (`id_user`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- AUTO_INCREMENT de la tabla `tareas`
--
ALTER TABLE `tareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `tarea_parcelas`
--
ALTER TABLE `tarea_parcelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `tarea_trabajadores`
--
ALTER TABLE `tarea_trabajadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `tarea_trabajos`
--
ALTER TABLE `tarea_trabajos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `trabajadores`
--
ALTER TABLE `trabajadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de la tabla `trabajos`
--
ALTER TABLE `trabajos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

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
-- Filtros para la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD CONSTRAINT `fk_vehiculos_user` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
