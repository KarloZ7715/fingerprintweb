-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-11-2025 a las 00:18:38
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
-- Base de datos: `supertiendasis_control`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`436573`@`%` PROCEDURE `actualizar_estado_alarmas` ()   BEGIN
    UPDATE alarma
    SET estado =
        CASE
            WHEN TIME(CONVERT_TZ(NOW(), '+00:00', '-05:00')) BETWEEN h_encendido AND h_apagado
                 AND estado != 'Activa'
                THEN 'En Espera'
            ELSE
                CASE WHEN estado != 'Activa' THEN 'Apagada' ELSE estado END
        END;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administrador`
--

CREATE TABLE `administrador` (
  `id` int(11) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `primer_nombre` varchar(50) DEFAULT NULL,
  `primer_apellido` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administrador`
--

INSERT INTO `administrador` (`id`, `cedula`, `primer_nombre`, `primer_apellido`, `email`, `password`, `remember_token`, `telefono`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Carlos', 'Cordero', 'carlos152924@gmail.com', '$2y$12$CXCtzF7yhMVG0Wyc/kMsKeP4cgZO223niiv/odgWDWtzMy5FLr.Ca', '1RWnQ8iZcAr8QgIGVWfyupiWwYYKK9j2JJKtvQ3MaOWax5fpAoohCndVxcMf', NULL, '2025-11-02 21:21:38', '2025-11-07 21:18:00'),
(2, NULL, NULL, NULL, 'brayan@gmail.com', '$2y$12$IdVH021IsBiFxdBJ/1Shee6PeH/nJrAR5YbZdh0HhfvAF25TcoIoS', 'ZsNapJ2qiqlpyQsBy2yNKtdhTfvi2WerAkRdAF6hWWVAWfn3liP6Mnc8CoJv', NULL, '2025-11-07 00:11:14', '2025-11-07 00:11:14'),
(3, NULL, NULL, NULL, 'brayan3@gmail.com', '$2y$12$bW8c15X0Q1Xs6GM7n15LROWKGYmlfjgNp0ofuT92vE2LIR2NHcc8e', NULL, NULL, '2025-11-07 02:57:50', '2025-11-07 02:57:50'),
(5, NULL, NULL, NULL, 'admin@admin.com', '$2y$12$iuUF0sMxrgq34p0HCdnYCe7nq1a4X5Z9uxV4e6q.W16ko6R3ivkWu', NULL, NULL, '2025-11-08 14:50:52', '2025-11-08 14:50:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alarma`
--

CREATE TABLE `alarma` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `duracion` int(10) NOT NULL,
  `estado` enum('Apagada','En Espera','Activa') NOT NULL DEFAULT 'Apagada',
  `h_encendido` time NOT NULL,
  `h_apagado` time NOT NULL,
  `sucursal_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `alarma`
--

INSERT INTO `alarma` (`id`, `nombre`, `duracion`, `estado`, `h_encendido`, `h_apagado`, `sucursal_id`, `created_at`, `updated_at`) VALUES
(1, 'Alarma Principal', 5, 'Apagada', '10:00:00', '02:30:00', 1, '2025-11-06 23:03:01', '2025-11-11 06:14:28'),
(8, 'la d', 3, 'En Espera', '10:43:53', '10:43:58', 1, '2025-11-07 20:44:00', '2025-11-11 06:05:10'),
(11, 'K', 3, 'En Espera', '11:13:38', '12:13:38', 1, '2025-11-07 13:13:38', '2025-11-11 05:26:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia`
--

CREATE TABLE `asistencia` (
  `id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL COMMENT 'Relación directa con empleado',
  `horario_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Horario contra el que se valida',
  `fecha` date NOT NULL COMMENT 'Fecha del registro',
  `hora_registro` time NOT NULL COMMENT 'Hora exacta del registro',
  `huella_id` int(11) DEFAULT NULL,
  `estado` enum('Puntual','Tarde','Ausente','Justificado') NOT NULL DEFAULT 'Puntual',
  `tipo` enum('Entrada','Salida') NOT NULL,
  `minutos_diferencia` int(11) NOT NULL DEFAULT 0 COMMENT 'Minutos de diferencia con horario esperado',
  `observaciones` text DEFAULT NULL,
  `justificada` tinyint(1) NOT NULL DEFAULT 0,
  `motivo_justificacion` text DEFAULT NULL,
  `justificado_por` int(11) DEFAULT NULL COMMENT 'Admin que justificó',
  `fecha_justificacion` timestamp NULL DEFAULT NULL,
  `metodo_registro` enum('Huella','Manual','Emergencia') NOT NULL DEFAULT 'Huella',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `fecha_hora_registro` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia_historico`
--

CREATE TABLE `asistencia_historico` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `asistencia_id` int(11) NOT NULL COMMENT 'ID del registro de asistencia modificado',
  `empleado_id` int(11) NOT NULL COMMENT 'ID del empleado (redundante pero útil)',
  `campo_modificado` varchar(50) NOT NULL COMMENT 'Nombre del campo que cambió',
  `valor_anterior` text DEFAULT NULL COMMENT 'Valor antes del cambio',
  `valor_nuevo` text DEFAULT NULL COMMENT 'Valor después del cambio',
  `modificado_por` int(11) DEFAULT NULL COMMENT 'ID del admin que hizo el cambio',
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Cuándo se hizo el cambio'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-esp32_ip', 's:12:\"192.168.1.29\";', 1762880830);

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
-- Estructura de tabla para la tabla `configuracion_asistencia`
--

CREATE TABLE `configuracion_asistencia` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sucursal_id` int(11) DEFAULT NULL COMMENT 'NULL = configuración global',
  `tolerancia_entrada_global` int(11) NOT NULL DEFAULT 15 COMMENT 'Minutos permitidos de retraso por defecto',
  `tolerancia_salida_global` int(11) NOT NULL DEFAULT 15 COMMENT 'Minutos permitidos de salida temprana por defecto',
  `requiere_marcacion_salida` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Si es obligatorio marcar salida',
  `permite_marcacion_manual` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Si admin puede marcar manualmente',
  `requiere_justificacion_ausencia` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Si ausencias deben ser justificadas',
  `notificar_tardanzas` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Enviar notificaciones por tardanzas',
  `notificar_ausencias` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Enviar notificaciones por ausencias',
  `email_notificaciones` varchar(100) DEFAULT NULL COMMENT 'Email para recibir notificaciones',
  `generar_reporte_diario` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Generar reporte al final del día',
  `generar_reporte_semanal` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Generar reporte semanal',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuracion_asistencia`
--

INSERT INTO `configuracion_asistencia` (`id`, `sucursal_id`, `tolerancia_entrada_global`, `tolerancia_salida_global`, `requiere_marcacion_salida`, `permite_marcacion_manual`, `requiere_justificacion_ausencia`, `notificar_tardanzas`, `notificar_ausencias`, `email_notificaciones`, `generar_reporte_diario`, `generar_reporte_semanal`, `created_at`, `updated_at`) VALUES
(1, NULL, 15, 15, 1, 0, 1, 1, 1, NULL, 0, 1, '2025-11-07 23:27:32', '2025-11-07 23:27:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contacto_emergencia`
--

CREATE TABLE `contacto_emergencia` (
  `id` int(11) NOT NULL,
  `nombre_completo` varchar(150) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `sucursal_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `contacto_emergencia`
--

INSERT INTO `contacto_emergencia` (`id`, `nombre_completo`, `telefono`, `correo`, `sucursal_id`, `created_at`, `updated_at`) VALUES
(1, 'Brayan Araujo', '+573104991318', 'brayanjesusaraujovega@gmail.com', 1, '2025-11-07 20:12:41', NULL),
(3, 'Maria K', '4343434', 'VelezMaria@gmail.com', 1, '2025-11-30 22:51:21', '2025-11-08 15:07:03'),
(5, 'Jhon Quiceno', '3222590930', 'quicenojale@gmail.com', 1, '2025-11-08 15:04:36', '2025-11-08 18:31:43'),
(6, 'Carlos CC', '300 1234567', 'carlos152429@gmail.com', 1, '2025-11-11 05:27:48', '2025-11-11 06:12:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleado`
--

CREATE TABLE `empleado` (
  `id` int(11) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `primer_nombre` varchar(50) DEFAULT NULL,
  `segundo_nombre` varchar(50) DEFAULT NULL,
  `primer_apellido` varchar(50) DEFAULT NULL,
  `segundo_apellido` varchar(50) DEFAULT NULL,
  `codigo_pais` varchar(4) NOT NULL DEFAULT '57' COMMENT 'Código de país para teléfono (ej: 57 para Colombia)',
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `estado` enum('Activo','Inactivo','Suspendido','Vacaciones','Pendiente_Huella') DEFAULT 'Activo',
  `sucursal_id` int(11) DEFAULT NULL,
  `horario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `foto_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleado`
--

INSERT INTO `empleado` (`id`, `cedula`, `primer_nombre`, `segundo_nombre`, `primer_apellido`, `segundo_apellido`, `codigo_pais`, `telefono`, `email`, `estado`, `sucursal_id`, `horario_id`, `foto_url`, `created_at`, `updated_at`) VALUES
(1, '10000001', 'Juan', NULL, 'Pérez', 'Kun', '1', '1234567890', 'Juan@ejemplo.com', 'Activo', 1, NULL, NULL, '2025-10-20 22:00:00', '2025-11-11 04:45:25'),
(2, '10000002', 'María', NULL, 'García', NULL, '57', '1234567891', NULL, 'Activo', 1, NULL, NULL, '2025-10-20 22:00:00', NULL),
(3, '10000003', 'Luis', NULL, 'Rodríguez', NULL, '57', '1234567892', NULL, 'Activo', 2, NULL, NULL, '2025-10-20 22:00:00', NULL),
(4, '10000004', 'Ana', NULL, 'Martínez', NULL, '57', '1234567893', NULL, 'Activo', 2, NULL, NULL, '2025-10-20 22:00:00', NULL),
(5, '10000005', 'Carlos', NULL, 'López', NULL, '57', '1234567894', NULL, 'Activo', 3, NULL, NULL, '2025-10-20 22:00:00', NULL),
(6, '10000006', 'Laura', NULL, 'Gómez', NULL, '57', '1234567895', NULL, 'Activo', 3, NULL, NULL, '2025-10-20 22:00:00', NULL),
(7, '10000007', 'Miguel', NULL, 'Morales', NULL, '57', '1234567896', NULL, 'Activo', 4, NULL, NULL, '2025-10-20 22:00:00', NULL),
(8, '10000008', 'Sofía', NULL, 'Ramos', NULL, '57', '1234567897', NULL, 'Activo', 4, NULL, NULL, '2025-10-20 22:00:00', NULL),
(9, '10000009', 'Pedro', NULL, 'Jiménez', NULL, '57', '1234567898', NULL, 'Activo', 5, NULL, NULL, '2025-10-20 22:00:00', NULL),
(10, '10000010', 'Valentina', NULL, 'Castro', 'Jiménez', '57', '3001234567', 'vale@correo.com', 'Suspendido', 1, 1, NULL, '2025-10-20 22:00:00', '2025-11-11 04:07:59'),
(12, '1234567890', 'Luis', 'Alberto', 'Lanz', 'Lopez', '57', '3121234567', 'Luis@gmail.com', 'Activo', 1, 2, NULL, '2025-11-08 05:35:42', '2025-11-11 04:03:52'),
(24, '55555555', 'Carlos', NULL, 'ejemplo ', 'ejemplo', '57', '3001234567', 'ejemplo@huella.com', 'Activo', 1, NULL, NULL, '2025-11-11 03:06:58', '2025-11-11 04:03:44'),
(25, '1002005555', 'Carlos', NULL, 'Cordero', 'Ejemplo', '57', '3201234567', 'ejemplo@huella2.com', 'Inactivo', 1, 3, NULL, '2025-11-11 03:37:33', '2025-11-11 15:07:32'),
(29, '1234123456', 'Kevin', NULL, 'ejemplo ', 'Ejemplo', '57', '13212345678', 'ejemplo@huella3.com', 'Activo', 1, NULL, NULL, '2025-11-11 16:07:22', '2025-11-11 16:07:22');

--
-- Disparadores `empleado`
--
DELIMITER $$
CREATE TRIGGER `sync_huella_estado_on_empleado_update` AFTER UPDATE ON `empleado` FOR EACH ROW BEGIN
                -- Si el empleado pasa a Inactivo, todas sus huellas también
                IF NEW.estado = "Inactivo" AND OLD.estado != "Inactivo" THEN
                    UPDATE huella 
                    SET estado = "Inactiva", updated_at = CURRENT_TIMESTAMP
                    WHERE empleado_id = NEW.id;
                END IF;
                
                -- Si el empleado pasa a Suspendido, bloquear huellas
                IF NEW.estado = "Suspendido" AND OLD.estado != "Suspendido" THEN
                    UPDATE huella 
                    SET estado = "Bloqueada", updated_at = CURRENT_TIMESTAMP
                    WHERE empleado_id = NEW.id;
                END IF;
                
                -- Si el empleado vuelve a Activo, reactivar huellas
                IF NEW.estado = "Activo" AND OLD.estado IN ("Inactivo", "Suspendido") THEN
                    UPDATE huella 
                    SET estado = "Activa", updated_at = CURRENT_TIMESTAMP
                    WHERE empleado_id = NEW.id AND estado IN ("Inactiva", "Bloqueada");
                END IF;
            END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `envio`
--

CREATE TABLE `envio` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) DEFAULT NULL,
  `contacto_id` int(11) DEFAULT NULL,
  `fecha_envio` datetime DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL COMMENT 'Enviado | Fallido',
  `forma` varchar(20) DEFAULT 'Correo' COMMENT 'Correo,SMS, LLamada',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `envio`
--

INSERT INTO `envio` (`id`, `evento_id`, `contacto_id`, `fecha_envio`, `estado`, `forma`, `updated_at`, `created_at`) VALUES
(1, 3, 1, '2025-11-07 21:13:30', 'Enviado', 'Vista', NULL, NULL),
(3, 26, 1, '2025-11-08 04:28:55', 'Enviado', 'Correo', '2025-11-08 03:28:55', '2025-11-08 03:28:55'),
(4, 27, 1, '2025-11-08 04:30:01', 'Respondida', 'LLamada', '2025-11-08 03:30:01', '2025-11-08 03:30:01'),
(5, 28, 1, '2025-11-08 04:34:28', 'Rechazada', 'LLamada', '2025-11-08 03:34:28', '2025-11-08 03:34:28'),
(6, 30, 1, '2025-11-08 04:51:58', 'Rechada', 'LLamada', '2025-11-08 03:51:58', '2025-11-08 03:51:58'),
(7, 30, 3, '2025-11-08 04:51:59', 'Enviado', 'Correo', '2025-11-08 03:51:59', '2025-11-08 03:51:59'),
(8, 31, 1, '2025-11-08 00:19:42', 'Pendiente', 'Correo', '2025-11-08 04:19:42', '2025-11-08 04:19:42'),
(9, 32, 1, '2025-11-08 00:23:57', 'Pendiente', 'Correo', '2025-11-08 04:23:58', '2025-11-08 04:23:58'),
(10, 33, 1, '2025-11-08 00:26:48', 'Pendiente', 'Correo', '2025-11-08 04:26:48', '2025-11-08 04:26:48'),
(11, 33, 3, '2025-11-08 00:26:48', 'Pendiente', 'Correo', '2025-11-08 04:26:50', '2025-11-08 04:26:50'),
(12, 34, 1, '2025-11-08 00:30:33', 'Pendiente', 'Correo', '2025-11-08 04:30:34', '2025-11-08 04:30:34'),
(13, 34, 3, '2025-11-08 00:30:33', 'Pendiente', 'Correo', '2025-11-08 04:30:36', '2025-11-08 04:30:36'),
(14, 35, 1, '2025-11-08 10:30:47', 'Pendiente', 'Correo', '2025-11-08 14:30:47', '2025-11-08 14:30:47'),
(15, 35, 3, '2025-11-08 10:30:47', 'Pendiente', 'Correo', '2025-11-08 14:30:50', '2025-11-08 14:30:50'),
(16, 37, 1, '2025-11-08 11:04:38', 'Pendiente', 'Correo', '2025-11-08 15:04:39', '2025-11-08 15:04:39'),
(17, 37, 3, '2025-11-08 11:04:38', 'Pendiente', 'Correo', '2025-11-08 15:04:41', '2025-11-08 15:04:41'),
(18, 38, 1, '2025-11-08 11:05:10', 'Pendiente', 'Correo', '2025-11-08 15:05:11', '2025-11-08 15:05:11'),
(19, 38, 3, '2025-11-08 11:05:10', 'Pendiente', 'Correo', '2025-11-08 15:05:13', '2025-11-08 15:05:13'),
(20, 39, 1, '2025-11-08 11:06:15', 'Pendiente', 'Correo', '2025-11-08 15:06:16', '2025-11-08 15:06:16'),
(21, 39, 3, '2025-11-08 11:06:15', 'Pendiente', 'Correo', '2025-11-08 15:06:18', '2025-11-08 15:06:18'),
(22, 40, 1, '2025-11-08 11:07:18', 'Pendiente', 'Correo', '2025-11-08 15:07:18', '2025-11-08 15:07:18'),
(23, 40, 3, '2025-11-08 11:07:18', 'Pendiente', 'Correo', '2025-11-08 15:07:20', '2025-11-08 15:07:20'),
(24, 40, 5, '2025-11-08 11:07:18', 'Pendiente', 'Correo', '2025-11-08 15:07:21', '2025-11-08 15:07:21'),
(25, 41, 1, '2025-11-08 11:09:50', 'Pendiente', 'Correo', '2025-11-08 15:09:53', '2025-11-08 15:09:53'),
(26, 41, 3, '2025-11-08 11:09:50', 'Pendiente', 'Correo', '2025-11-08 15:09:55', '2025-11-08 15:09:55'),
(27, 41, 5, '2025-11-08 11:09:50', 'Pendiente', 'Correo', '2025-11-08 15:09:56', '2025-11-08 15:09:56'),
(28, 97, 1, '2025-11-08 12:13:18', 'Enviado', 'Correo', '2025-11-08 16:13:20', '2025-11-08 16:13:18'),
(29, 97, 3, '2025-11-08 12:13:18', 'Enviado', 'Correo', '2025-11-08 16:13:21', '2025-11-08 16:13:21'),
(30, 97, 5, '2025-11-08 12:13:18', 'Enviado', 'Correo', '2025-11-08 16:13:23', '2025-11-08 16:13:22'),
(31, 99, 1, '2025-11-08 12:16:20', 'Enviado', 'Correo', '2025-11-08 16:16:23', '2025-11-08 16:16:21'),
(32, 99, 3, '2025-11-08 12:16:20', 'Enviado', 'Correo', '2025-11-08 16:16:24', '2025-11-08 16:16:23'),
(33, 99, 5, '2025-11-08 12:16:20', 'Enviado', 'Correo', '2025-11-08 16:16:26', '2025-11-08 16:16:24'),
(34, 100, 1, '2025-11-08 12:17:17', 'Enviado', 'Correo', '2025-11-08 16:17:20', '2025-11-08 16:17:18'),
(35, 100, 3, '2025-11-08 12:17:17', 'Enviado', 'Correo', '2025-11-08 16:17:21', '2025-11-08 16:17:20'),
(36, 100, 5, '2025-11-08 12:17:17', 'Enviado', 'Correo', '2025-11-08 16:17:23', '2025-11-08 16:17:22'),
(37, 101, 1, '2025-11-08 12:18:53', 'Enviado', 'Correo', '2025-11-08 16:19:00', '2025-11-08 16:18:54'),
(38, 101, 3, '2025-11-08 12:18:53', 'Enviado', 'Correo', '2025-11-08 16:19:02', '2025-11-08 16:19:01'),
(39, 101, 5, '2025-11-08 12:18:53', 'Enviado', 'Correo', '2025-11-08 16:19:04', '2025-11-08 16:19:02'),
(40, 102, 1, '2025-11-08 12:21:07', 'Enviado', 'Correo', '2025-11-08 16:21:09', '2025-11-08 16:21:07'),
(41, 102, 3, '2025-11-08 12:21:07', 'Enviado', 'Correo', '2025-11-08 16:21:11', '2025-11-08 16:21:10'),
(42, 102, 5, '2025-11-08 12:21:07', 'Enviado', 'Correo', '2025-11-08 16:21:12', '2025-11-08 16:21:11'),
(43, 103, 1, '2025-11-08 12:22:04', 'Enviado', 'Correo', '2025-11-08 16:22:06', '2025-11-08 16:22:04'),
(44, 103, 3, '2025-11-08 12:22:04', 'Enviado', 'Correo', '2025-11-08 16:22:08', '2025-11-08 16:22:07'),
(45, 103, 5, '2025-11-08 12:22:04', 'Enviado', 'Correo', '2025-11-08 16:22:09', '2025-11-08 16:22:08'),
(46, 104, 1, '2025-11-08 12:22:41', 'Enviado', 'Correo', '2025-11-08 16:22:44', '2025-11-08 16:22:42'),
(47, 104, 3, '2025-11-08 12:22:41', 'Enviado', 'Correo', '2025-11-08 16:22:46', '2025-11-08 16:22:44'),
(48, 104, 5, '2025-11-08 12:22:41', 'Enviado', 'Correo', '2025-11-08 16:22:47', '2025-11-08 16:22:46'),
(49, 2, 1, '2025-11-08 14:30:40', 'Enviado', 'Correo', '2025-11-08 18:30:44', '2025-11-08 18:30:41'),
(50, 2, 3, '2025-11-08 14:30:40', 'Enviado', 'Correo', '2025-11-08 18:30:45', '2025-11-08 18:30:44'),
(51, 2, 5, '2025-11-08 14:30:40', 'Enviado', 'Correo', '2025-11-08 18:30:47', '2025-11-08 18:30:46'),
(52, 3, 1, '2025-11-08 14:34:39', 'Enviado', 'Correo', '2025-11-08 18:34:42', '2025-11-08 18:34:40'),
(53, 3, 3, '2025-11-08 14:34:39', 'Enviado', 'Correo', '2025-11-08 18:34:43', '2025-11-08 18:34:42'),
(54, 3, 5, '2025-11-08 14:34:39', 'Enviado', 'Correo', '2025-11-08 18:34:45', '2025-11-08 18:34:44'),
(55, 4, 1, '2025-11-08 14:43:10', 'Enviado', 'Correo', '2025-11-08 18:43:13', '2025-11-08 18:43:11'),
(56, 4, 3, '2025-11-08 14:43:10', 'Enviado', 'Correo', '2025-11-08 18:43:15', '2025-11-08 18:43:13'),
(57, 4, 5, '2025-11-08 14:43:10', 'Enviado', 'Correo', '2025-11-08 18:43:16', '2025-11-08 18:43:15'),
(58, 16, 1, '2025-11-11 01:04:30', 'Enviado', 'Correo', '2025-11-11 06:04:30', '2025-11-11 06:04:30'),
(59, 16, 3, '2025-11-11 01:04:30', 'Enviado', 'Correo', '2025-11-11 06:04:30', '2025-11-11 06:04:30'),
(60, 16, 5, '2025-11-11 01:04:30', 'Enviado', 'Correo', '2025-11-11 06:04:30', '2025-11-11 06:04:30'),
(61, 16, 6, '2025-11-11 01:04:30', 'Enviado', 'Correo', '2025-11-11 06:04:30', '2025-11-11 06:04:30'),
(62, 20, 1, '2025-11-11 01:14:24', 'Enviado', 'Correo', '2025-11-11 06:14:24', '2025-11-11 06:14:24'),
(63, 20, 3, '2025-11-11 01:14:24', 'Enviado', 'Correo', '2025-11-11 06:14:24', '2025-11-11 06:14:24'),
(64, 20, 5, '2025-11-11 01:14:24', 'Enviado', 'Correo', '2025-11-11 06:14:24', '2025-11-11 06:14:24'),
(65, 20, 6, '2025-11-11 01:14:24', 'Enviado', 'Correo', '2025-11-11 06:14:24', '2025-11-11 06:14:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evento`
--

CREATE TABLE `evento` (
  `id` int(11) NOT NULL,
  `fecha_evento` datetime DEFAULT NULL,
  `alarma_id` int(11) DEFAULT NULL,
  `Evento` text NOT NULL,
  `Accion` text NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` varchar(20) DEFAULT NULL,
  `notificar` varchar(10) DEFAULT 'NO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `evento`
--

INSERT INTO `evento` (`id`, `fecha_evento`, `alarma_id`, `Evento`, `Accion`, `updated_at`, `created_at`, `notificar`) VALUES
(3, '2025-11-08 14:34:39', 8, 'Activar', 'Alarma activada: Se ha detectado movimiento.', NULL, NULL, 'NO'),
(4, '2025-11-08 14:43:10', 8, 'Activar', 'Alarma activada: Se ha detectado movimiento.', NULL, NULL, 'NO'),
(15, '2025-11-11 00:28:19', 1, 'Esperar', 'Alarma puesta en espera manualmente', NULL, NULL, 'NO'),
(16, '2025-11-11 01:04:30', 1, 'Movimiento', 'Alarma activada: Se ha detectado movimiento.', NULL, NULL, 'NO'),
(17, '2025-11-11 01:04:42', 1, 'Desactivar', 'Alarma apagada manualmente', NULL, NULL, 'NO'),
(18, '2025-11-11 01:05:10', 8, 'Esperar', 'Alarma puesta en espera manualmente', NULL, NULL, 'NO'),
(19, '2025-11-11 01:14:03', 1, 'Esperar', 'Alarma puesta en espera manualmente', NULL, NULL, 'NO'),
(20, '2025-11-11 01:14:24', 1, 'Movimiento', 'Alarma activada: Se ha detectado movimiento.', NULL, NULL, 'NO'),
(21, '2025-11-11 01:14:28', 1, 'Desactivar', 'Alarma apagada manualmente', NULL, NULL, 'NO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horario`
--

CREATE TABLE `horario` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL COMMENT 'Ej: Turno Mañana, Turno Tarde',
  `descripcion` text DEFAULT NULL,
  `hora_entrada` time NOT NULL COMMENT 'Hora esperada de entrada',
  `hora_salida` time NOT NULL COMMENT 'Hora esperada de salida',
  `tolerancia_entrada` int(11) NOT NULL DEFAULT 15 COMMENT 'Minutos permitidos de retraso',
  `tolerancia_salida` int(11) NOT NULL DEFAULT 15 COMMENT 'Minutos permitidos de salida temprana',
  `dias_laborables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '{"lunes": true, "martes": true, ...}' CHECK (json_valid(`dias_laborables`)),
  `requiere_entrada` tinyint(1) NOT NULL DEFAULT 1,
  `requiere_salida` tinyint(1) NOT NULL DEFAULT 1,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `sucursal_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `horario`
--

INSERT INTO `horario` (`id`, `nombre`, `descripcion`, `hora_entrada`, `hora_salida`, `tolerancia_entrada`, `tolerancia_salida`, `dias_laborables`, `requiere_entrada`, `requiere_salida`, `activo`, `sucursal_id`, `created_at`, `updated_at`) VALUES
(1, 'Turno Mañana', 'Personal de 7am a 3pm', '07:00:00', '15:00:00', 15, 15, '{\"lunes\":true,\"martes\":true,\"miercoles\":true,\"jueves\":true,\"viernes\":true,\"sabado\":false,\"domingo\":false}', 1, 1, 1, NULL, '2025-11-07 23:09:15', '2025-11-12 17:10:30'),
(2, 'Turno Tarde', 'Personal de 3pm a 11pm', '15:00:00', '23:00:00', 10, 10, '{\"lunes\":true,\"martes\":true,\"miercoles\":true,\"jueves\":true,\"viernes\":true,\"sabado\":true,\"domingo\":false}', 1, 1, 1, NULL, '2025-11-07 23:09:15', '2025-11-07 23:09:15'),
(3, 'Administrativo', 'Personal administrativo', '08:00:00', '17:00:00', 15, 30, '{\"lunes\":true,\"martes\":true,\"miercoles\":true,\"jueves\":true,\"viernes\":true,\"sabado\":false,\"domingo\":false}', 1, 1, 1, NULL, '2025-11-07 23:09:15', '2025-11-07 23:09:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horario_old`
--

CREATE TABLE `horario_old` (
  `id` int(11) NOT NULL,
  `tipo` varchar(20) DEFAULT NULL COMMENT 'Alarma | Empleado',
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `dias` varchar(100) DEFAULT NULL,
  `alarma_id` int(11) DEFAULT NULL,
  `empleado_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `huella`
--

CREATE TABLE `huella` (
  `id` int(11) NOT NULL,
  `numero_slot` int(11) NOT NULL COMMENT 'Posición en memoria del sensor (1-300)',
  `tipo_dedo` enum('Pulgar','Indice','Medio','Anular','Meñique') NOT NULL DEFAULT 'Indice',
  `mano` enum('Izquierda','Derecha') NOT NULL DEFAULT 'Derecha',
  `empleado_id` int(11) DEFAULT NULL,
  `fecha_enrolamiento` timestamp NULL DEFAULT NULL,
  `enrolado_por` int(11) DEFAULT NULL COMMENT 'ID del admin que enroló',
  `calidad` int(11) DEFAULT NULL COMMENT 'Calidad de la huella (1-299)',
  `estado` enum('Activa','Inactiva','Bloqueada') NOT NULL DEFAULT 'Activa',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `huella`
--

INSERT INTO `huella` (`id`, `numero_slot`, `tipo_dedo`, `mano`, `empleado_id`, `fecha_enrolamiento`, `enrolado_por`, `calidad`, `estado`, `created_at`, `updated_at`) VALUES
(2, 0, 'Indice', 'Derecha', 24, '2025-11-11 03:06:58', 1, 150, 'Activa', '2025-11-11 03:06:58', '2025-11-11 03:06:58'),
(3, 1, 'Indice', 'Derecha', 25, '2025-11-11 03:37:33', 1, 150, 'Inactiva', '2025-11-11 03:37:33', '2025-11-11 15:07:32'),
(7, 2, 'Pulgar', 'Izquierda', 29, '2025-11-11 16:07:22', 1, 150, 'Activa', '2025-11-11 16:07:22', '2025-11-11 16:07:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
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
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_11_02_000001_update_administrador_table_for_authentication', 2),
(32, '2025_11_07_232148_update_empleado_table_add_fields', 3),
(33, '2025_11_07_232202_recreate_horario_table', 3),
(34, '2025_11_07_232212_update_huella_table_add_fields', 3),
(35, '2025_11_07_232226_update_asistencia_table_add_fields', 4),
(36, '2025_11_07_232234_create_asistencia_historico_table', 5),
(37, '2025_11_07_232242_create_configuracion_asistencia_table', 6),
(38, '2025_11_07_232250_create_sync_huella_estado_trigger', 6),
(39, '2025_11_07_232547_add_foreign_key_empleado_horario', 7),
(40, '2025_11_08_141104_add_pendiente_huella_estado_to_empleado_table', 8),
(41, '2025_11_10_134536_remove_template_huella_from_huella_table', 9),
(42, '2025_11_10_230029_add_codigo_pais_to_empleado_table', 10);

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
('DkdDRpo6k9GoSdG53GfYtGTayZcEYdLSVPc4NNOi', 1, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiTXdtMFFYR01hNjZCVmxSbURNTnpMZXhLaDBCTnYyUGVnQjVRVjlnUyI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJENYQ3R6Rjd5aE1WRzBXeWMva01zS2VQNGNnWk8yMjNuaWl2L29kZ1dEV3R6TXk1RkxyLkNhIjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozNjoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2FkbWluL2hvcmFyaW9zIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo2OiJ0YWJsZXMiO2E6Mzp7czo0MDoiMmQ5YmRlOTNjNzEyMzljYzg1NTIyMTYzYzNiMjg3MzRfY29sdW1ucyI7YToxMDp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjY6Im5vbWJyZSI7czo1OiJsYWJlbCI7czo2OiJOb21icmUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEyOiJob3JhX2VudHJhZGEiO3M6NToibGFiZWwiO3M6NzoiRW50cmFkYSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTE6ImhvcmFfc2FsaWRhIjtzOjU6ImxhYmVsIjtzOjY6IlNhbGlkYSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTg6InRvbGVyYW5jaWFfZW50cmFkYSI7czo1OiJsYWJlbCI7czoxMjoiVG9sLiBFbnRyYWRhIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MDt9aTo0O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE3OiJ0b2xlcmFuY2lhX3NhbGlkYSI7czo1OiJsYWJlbCI7czoxMToiVG9sLiBTYWxpZGEiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjowO31pOjU7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTU6ImRpYXNfbGFib3JhYmxlcyI7czo1OiJsYWJlbCI7czoxNjoiRMOtYXMgTGFib3JhYmxlcyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjA7fWk6NjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNToiZW1wbGVhZG9zX2NvdW50IjtzOjU6ImxhYmVsIjtzOjE5OiJFbXBsZWFkb3MgQXNpZ25hZG9zIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MDt9aTo3O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE1OiJzdWN1cnNhbC5ub21icmUiO3M6NToibGFiZWwiO3M6ODoiU3VjdXJzYWwiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjowO31pOjg7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6NjoiYWN0aXZvIjtzOjU6ImxhYmVsIjtzOjY6IkFjdGl2byI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjk7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6ImNyZWF0ZWRfYXQiO3M6NToibGFiZWwiO3M6NjoiQ3JlYWRvIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MDtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MTt9fXM6NDA6IjljZWJiZjE2YTNmODI0ZmNiY2RjNGUwMDEzN2U5MTc4X2NvbHVtbnMiO2E6Nzp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjY6ImNlZHVsYSI7czo1OiJsYWJlbCI7czo3OiJDw6lkdWxhIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNToibm9tYnJlX2NvbXBsZXRvIjtzOjU6ImxhYmVsIjtzOjE1OiJOb21icmUgQ29tcGxldG8iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjU6ImVtYWlsIjtzOjU6ImxhYmVsIjtzOjY6IkNvcnJlbyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjA7fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNzoidGVsZWZvbm9fY29tcGxldG8iO3M6NToibGFiZWwiO3M6OToiVGVsw6lmb25vIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MDt9aTo0O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjY6ImVzdGFkbyI7czo1OiJsYWJlbCI7czo2OiJFc3RhZG8iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo1O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE1OiJzdWN1cnNhbC5ub21icmUiO3M6NToibGFiZWwiO3M6ODoiU3VjdXJzYWwiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjowO31pOjY7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6ImNyZWF0ZWRfYXQiO3M6NToibGFiZWwiO3M6MTA6IlJlZ2lzdHJhZG8iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjowO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjoxO319czo0MDoiMDU1ZTZhYTRlOWEzZjk2MTg4MmY2YTA4NTg3MDRiZmNfY29sdW1ucyI7YTo2OntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTI6ImZlY2hhX2V2ZW50byI7czo1OiJsYWJlbCI7czoxMjoiRmVjaGEgZXZlbnRvIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMzoiQWxhcm1hLm5vbWJyZSI7czo1OiJsYWJlbCI7czoxNToiQWxhcm1hIGFzb2NpYWRhIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo2OiJBY2Npb24iO3M6NToibGFiZWwiO3M6NjoiQWNjaW9uIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMjoiZW52aW9zX2Zvcm1hIjtzOjU6ImxhYmVsIjtzOjE1OiJGb3JtYSBkZSBlbnbDrW8iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo0O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEzOiJlbnZpb3NfZXN0YWRvIjtzOjU6ImxhYmVsIjtzOjE3OiJFc3RhZG8gZGVsIGVudsOtbyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjU7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTU6ImVudmlvc19jb250YWN0byI7czo1OiJsYWJlbCI7czo5OiJFbnZpYWRvIGEiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9fX19', 1762967491),
('KeEj7IvuSAKCmJpqGXgw1DunUiBNIZFHwsgnSPKh', 1, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiM2lNWWhqZk1aZ0hTZDl0b0tlbWRuWnlZNzV5TUtDREdBSGhucGFudyI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJENYQ3R6Rjd5aE1WRzBXeWMva01zS2VQNGNnWk8yMjNuaWl2L29kZ1dEV3R6TXk1RkxyLkNhIjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo0NDoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2FkbWluL2VtcGxlYWRvcy9jcmVhdGUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjY6InRhYmxlcyI7YToxOntzOjQwOiI5Y2ViYmYxNmEzZjgyNGZjYmNkYzRlMDAxMzdlOTE3OF9jb2x1bW5zIjthOjc6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo2OiJjZWR1bGEiO3M6NToibGFiZWwiO3M6NzoiQ8OpZHVsYSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTU6Im5vbWJyZV9jb21wbGV0byI7czo1OiJsYWJlbCI7czoxNToiTm9tYnJlIENvbXBsZXRvIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo1OiJlbWFpbCI7czo1OiJsYWJlbCI7czo2OiJDb3JyZW8iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjowO31pOjM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTc6InRlbGVmb25vX2NvbXBsZXRvIjtzOjU6ImxhYmVsIjtzOjk6IlRlbMOpZm9ubyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjA7fWk6NDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo2OiJlc3RhZG8iO3M6NToibGFiZWwiO3M6NjoiRXN0YWRvIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNToic3VjdXJzYWwubm9tYnJlIjtzOjU6ImxhYmVsIjtzOjg6IlN1Y3Vyc2FsIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MDt9aTo2O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjU6ImxhYmVsIjtzOjEwOiJSZWdpc3RyYWRvIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MDtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MTt9fX1zOjg6ImZpbGFtZW50IjthOjA6e319', 1762875134),
('lmhAbMd7i7SSIOmpfIJeN59VlMawihDIXh2hp9Mz', 1, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoiTkJnc3ZncmlSN0VMU1JqMVNXOFVWVjhHOEU2Mko5Wjg3MXYyZ3pvbCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM5OiJodHRwOi8vMTkyLjE2OC4xLjY6ODAwMC9hZG1pbi9lbXBsZWFkb3MiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjYwOiIkMnkkMTIkQ1hDdHpGN3loTVZHMFd5Yy9rTXNLZVA0Y2daTzIyM25paXYvb2RnV0RXdHpNeTVGTHIuQ2EiO3M6ODoiZmlsYW1lbnQiO2E6MDp7fXM6NjoidGFibGVzIjthOjE6e3M6NDA6IjljZWJiZjE2YTNmODI0ZmNiY2RjNGUwMDEzN2U5MTc4X2NvbHVtbnMiO2E6Nzp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjY6ImNlZHVsYSI7czo1OiJsYWJlbCI7czo3OiJDw6lkdWxhIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNToibm9tYnJlX2NvbXBsZXRvIjtzOjU6ImxhYmVsIjtzOjE1OiJOb21icmUgQ29tcGxldG8iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjU6ImVtYWlsIjtzOjU6ImxhYmVsIjtzOjY6IkNvcnJlbyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjA7fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNzoidGVsZWZvbm9fY29tcGxldG8iO3M6NToibGFiZWwiO3M6OToiVGVsw6lmb25vIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MDt9aTo0O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjY6ImVzdGFkbyI7czo1OiJsYWJlbCI7czo2OiJFc3RhZG8iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo1O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE1OiJzdWN1cnNhbC5ub21icmUiO3M6NToibGFiZWwiO3M6ODoiU3VjdXJzYWwiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjowO31pOjY7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6ImNyZWF0ZWRfYXQiO3M6NToibGFiZWwiO3M6MTA6IlJlZ2lzdHJhZG8iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjowO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjoxO319fX0=', 1762877251);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sucursal`
--

CREATE TABLE `sucursal` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `administrador_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sucursal`
--

INSERT INTO `sucursal` (`id`, `nombre`, `direccion`, `administrador_id`, `created_at`, `updated_at`) VALUES
(1, 'Super tienda', 'Certe', 1, '2025-11-06 23:02:31', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `administrador_email_unique` (`email`);

--
-- Indices de la tabla `alarma`
--
ALTER TABLE `alarma`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sucursal_id` (`sucursal_id`);

--
-- Indices de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `huella_id` (`huella_id`),
  ADD KEY `idx_asistencia_empleado` (`empleado_id`),
  ADD KEY `idx_asistencia_horario` (`horario_id`),
  ADD KEY `idx_asistencia_fecha` (`fecha`),
  ADD KEY `idx_asistencia_empleado_fecha` (`empleado_id`,`fecha`),
  ADD KEY `fk_asistencia_admin` (`justificado_por`);

--
-- Indices de la tabla `asistencia_historico`
--
ALTER TABLE `asistencia_historico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_historico_asistencia` (`asistencia_id`),
  ADD KEY `idx_historico_empleado` (`empleado_id`),
  ADD KEY `idx_historico_admin` (`modificado_por`),
  ADD KEY `idx_historico_fecha` (`fecha_modificacion`);

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
-- Indices de la tabla `configuracion_asistencia`
--
ALTER TABLE `configuracion_asistencia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_config_sucursal` (`sucursal_id`);

--
-- Indices de la tabla `contacto_emergencia`
--
ALTER TABLE `contacto_emergencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sucursal_id` (`sucursal_id`);

--
-- Indices de la tabla `empleado`
--
ALTER TABLE `empleado`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_empleado_cedula` (`cedula`),
  ADD KEY `sucursal_id` (`sucursal_id`),
  ADD KEY `idx_empleado_horario` (`horario_id`);

--
-- Indices de la tabla `envio`
--
ALTER TABLE `envio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contacto_id` (`contacto_id`);

--
-- Indices de la tabla `evento`
--
ALTER TABLE `evento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alarma_id` (`alarma_id`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `horario`
--
ALTER TABLE `horario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_horario_sucursal` (`sucursal_id`),
  ADD KEY `idx_horario_activo` (`activo`);

--
-- Indices de la tabla `horario_old`
--
ALTER TABLE `horario_old`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alarma_id` (`alarma_id`),
  ADD KEY `empleado_id` (`empleado_id`);

--
-- Indices de la tabla `huella`
--
ALTER TABLE `huella`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `huella_numero_slot_unique` (`numero_slot`),
  ADD UNIQUE KEY `empleado_id` (`empleado_id`),
  ADD KEY `idx_huella_slot` (`numero_slot`),
  ADD KEY `fk_huella_admin` (`enrolado_por`),
  ADD KEY `idx_huella_empleado` (`empleado_id`),
  ADD KEY `idx_huella_estado` (`estado`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indices de la tabla `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `sucursal`
--
ALTER TABLE `sucursal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `administrador_id` (`administrador_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administrador`
--
ALTER TABLE `administrador`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `alarma`
--
ALTER TABLE `alarma`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asistencia_historico`
--
ALTER TABLE `asistencia_historico`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuracion_asistencia`
--
ALTER TABLE `configuracion_asistencia`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `contacto_emergencia`
--
ALTER TABLE `contacto_emergencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `empleado`
--
ALTER TABLE `empleado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `envio`
--
ALTER TABLE `envio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT de la tabla `evento`
--
ALTER TABLE `evento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `horario`
--
ALTER TABLE `horario`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `horario_old`
--
ALTER TABLE `horario_old`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `huella`
--
ALTER TABLE `huella`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `sucursal`
--
ALTER TABLE `sucursal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alarma`
--
ALTER TABLE `alarma`
  ADD CONSTRAINT `alarma_ibfk_1` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursal` (`id`);

--
-- Filtros para la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD CONSTRAINT `fk_asistencia_admin` FOREIGN KEY (`justificado_por`) REFERENCES `administrador` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_asistencia_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleado` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_asistencia_horario` FOREIGN KEY (`horario_id`) REFERENCES `horario` (`id`),
  ADD CONSTRAINT `fk_asistencia_huella` FOREIGN KEY (`huella_id`) REFERENCES `huella` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `asistencia_historico`
--
ALTER TABLE `asistencia_historico`
  ADD CONSTRAINT `fk_historico_admin` FOREIGN KEY (`modificado_por`) REFERENCES `administrador` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_historico_asistencia` FOREIGN KEY (`asistencia_id`) REFERENCES `asistencia` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_historico_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleado` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `configuracion_asistencia`
--
ALTER TABLE `configuracion_asistencia`
  ADD CONSTRAINT `fk_config_sucursal` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursal` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `contacto_emergencia`
--
ALTER TABLE `contacto_emergencia`
  ADD CONSTRAINT `contacto_emergencia_ibfk_1` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursal` (`id`);

--
-- Filtros para la tabla `empleado`
--
ALTER TABLE `empleado`
  ADD CONSTRAINT `fk_empleado_horario` FOREIGN KEY (`horario_id`) REFERENCES `horario` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `envio`
--
ALTER TABLE `envio`
  ADD CONSTRAINT `envio_ibfk_2` FOREIGN KEY (`contacto_id`) REFERENCES `contacto_emergencia` (`id`);

--
-- Filtros para la tabla `huella`
--
ALTER TABLE `huella`
  ADD CONSTRAINT `fk_huella_admin` FOREIGN KEY (`enrolado_por`) REFERENCES `administrador` (`id`) ON DELETE SET NULL;

DELIMITER $$
--
-- Eventos
--
CREATE DEFINER=`436573`@`%` EVENT `evento_actualizar_estado_alarmas` ON SCHEDULE EVERY 1 MINUTE STARTS '2025-11-08 18:57:47' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    CALL actualizar_estado_alarmas();
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
