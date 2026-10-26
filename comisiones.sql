-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.4.3 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para cati
CREATE DATABASE IF NOT EXISTS `cati` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `cati`;

-- Volcando estructura para tabla cati.asignacion_permiso
CREATE TABLE IF NOT EXISTS `asignacion_permiso` (
  `Id` int unsigned NOT NULL AUTO_INCREMENT,
  `Clave` varchar(50) DEFAULT NULL,
  `Nombre` varchar(50) DEFAULT NULL,
  `Descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `IdUsuario` int NOT NULL,
  `IdTipoPermiso` int DEFAULT NULL,
  `IdNivelAcceso` int DEFAULT NULL,
  `IdTipoPermisoTemp` int DEFAULT NULL,
  `IdNivelAccesoTemp` int DEFAULT NULL,
  `IniPermisoTemp` datetime DEFAULT NULL,
  `FinPermisoTemp` datetime DEFAULT NULL,
  `Activo` tinyint DEFAULT NULL,
  `CreatedAt` datetime DEFAULT NULL,
  `UpdatedAt` datetime DEFAULT NULL,
  `DeletedAt` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) DEFAULT NULL,
  `UpdatedBy` varchar(50) DEFAULT NULL,
  `DeletedBy` varchar(50) DEFAULT NULL,
  `Versionn` int DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `FK_asignacion_permiso_usuario` (`IdUsuario`),
  KEY `FK_asignacion_permiso_nivel_acceso` (`IdNivelAcceso`),
  KEY `FK_asignacion_permiso_tipo_permiso` (`IdTipoPermiso`),
  KEY `FK_asignacion_permiso_tipo_permiso_2` (`IdTipoPermisoTemp`),
  KEY `FK_asignacion_permiso_nivel_acceso_2` (`IdNivelAccesoTemp`),
  CONSTRAINT `FK_asignacion_permiso_nivel_acceso` FOREIGN KEY (`IdNivelAcceso`) REFERENCES `nivel_acceso` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `FK_asignacion_permiso_nivel_acceso_2` FOREIGN KEY (`IdNivelAccesoTemp`) REFERENCES `nivel_acceso` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `FK_asignacion_permiso_tipo_permiso` FOREIGN KEY (`IdTipoPermiso`) REFERENCES `tipo_permiso` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `FK_asignacion_permiso_tipo_permiso_2` FOREIGN KEY (`IdTipoPermisoTemp`) REFERENCES `tipo_permiso` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `FK_asignacion_permiso_usuario` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.asignacion_permiso: ~5 rows (aproximadamente)
INSERT INTO `asignacion_permiso` (`Id`, `Clave`, `Nombre`, `Descripcion`, `IdUsuario`, `IdTipoPermiso`, `IdNivelAcceso`, `IdTipoPermisoTemp`, `IdNivelAccesoTemp`, `IniPermisoTemp`, `FinPermisoTemp`, `Activo`, `CreatedAt`, `UpdatedAt`, `DeletedAt`, `CreatedBy`, `UpdatedBy`, `DeletedBy`, `Versionn`) VALUES
	(1, 'APPN', NULL, 'Permiso 1 prueba area nelly: Asigancion de permiso Parcial Nelly', 1, 4, 1, NULL, NULL, NULL, NULL, 1, '2026-10-04 01:32:26', '2026-10-04 01:32:26', NULL, 'admin', 'admin', NULL, 1),
	(2, 'APPE', NULL, 'Permiso 2 prueba area estrellita: Asigancion de permiso Parcial Estrellita', 2, 4, 2, NULL, NULL, NULL, NULL, 1, '2026-10-04 10:54:02', NULL, NULL, 'admin', NULL, NULL, 1),
	(3, 'APPS', NULL, 'Permiso 3 prueba Saide: Asigancion depermiso Parcial Saide', 3, 4, 3, NULL, NULL, NULL, NULL, 1, '2026-10-04 10:55:50', NULL, NULL, 'admin', NULL, NULL, 1),
	(4, 'APTSU', NULL, 'Permiso Total Super Usuario: asigancion de permiso Total Super Usuario', 5, 4, 4, NULL, NULL, NULL, NULL, 1, '2026-10-04 15:43:41', NULL, NULL, 'SUsuario', NULL, NULL, 1),
	(5, 'APPA', NULL, 'Permiso Total Admin: asigancion de permiso Parcial Administdor', 6, 4, 5, NULL, NULL, NULL, NULL, 1, '2026-10-04 15:52:43', NULL, NULL, 'SUsuario', NULL, NULL, 1);

-- Volcando estructura para tabla cati.chat_conversacion
CREATE TABLE IF NOT EXISTS `chat_conversacion` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `IdUsuario1` int NOT NULL,
  `IdUsuario2` int NOT NULL,
  `IniciadoPor` int NOT NULL,
  `UltimoMensajeAt` datetime DEFAULT NULL,
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `UQ_chat_par` (`IdUsuario1`,`IdUsuario2`),
  KEY `IX_chat_u2` (`IdUsuario2`),
  CONSTRAINT `FK_chat_u1` FOREIGN KEY (`IdUsuario1`) REFERENCES `usuario` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_chat_u2` FOREIGN KEY (`IdUsuario2`) REFERENCES `usuario` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.chat_conversacion: ~3 rows (aproximadamente)
INSERT INTO `chat_conversacion` (`Id`, `IdUsuario1`, `IdUsuario2`, `IniciadoPor`, `UltimoMensajeAt`, `CreatedAt`, `OcultoU1`, `OcultoU2`) VALUES
	(1, 3, 4, 4, NULL, '2026-10-24 12:02:10', NULL, '2026-10-25 19:16:47'),
	(2, 1, 4, 4, '2026-10-25 15:56:21', '2026-10-24 12:02:23', NULL, NULL),
	(4, 1, 6, 1, NULL, '2026-10-25 18:16:45', NULL, NULL);

-- Volcando estructura para tabla cati.chat_mensaje
CREATE TABLE IF NOT EXISTS `chat_mensaje` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `IdConversacion` int NOT NULL,
  `IdRemitente` int NOT NULL,
  `Mensaje` text NOT NULL,
  `LeidoAt` datetime DEFAULT NULL,
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  KEY `IX_msg_conv` (`IdConversacion`,`Id`),
  KEY `FK_msg_remitente` (`IdRemitente`),
  CONSTRAINT `FK_msg_conv` FOREIGN KEY (`IdConversacion`) REFERENCES `chat_conversacion` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_msg_remitente` FOREIGN KEY (`IdRemitente`) REFERENCES `usuario` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.chat_mensaje: ~5 rows (aproximadamente)
INSERT INTO `chat_mensaje` (`Id`, `IdConversacion`, `IdRemitente`, `Mensaje`, `LeidoAt`, `CreatedAt`) VALUES
	(1, 2, 4, 'sabes que me falta por enviar en mi comision FCI-COMISION-2026-OCT-004', '2026-10-24 12:14:17', '2026-10-24 12:03:21'),
	(2, 2, 4, 'hola', '2026-10-24 18:17:37', '2026-10-24 18:16:32'),
	(3, 2, 4, 'que tal', '2026-10-25 15:55:08', '2026-10-25 15:55:06'),
	(4, 2, 1, 'me falta enviar algun reporte??', '2026-10-25 15:55:56', '2026-10-25 15:55:53'),
	(5, 2, 4, 'si en cuanto este te los mando', '2026-10-25 15:56:23', '2026-10-25 15:56:21');

-- Volcando estructura para tabla cati.comision
CREATE TABLE IF NOT EXISTS `comision` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `IdTipoComision` int NOT NULL,
  `IdPlantilla` int NOT NULL,
  `IdEstado` int NOT NULL,
  `IdFacultad` int NOT NULL,
  `NOficio` varchar(50) NOT NULL,
  `Asunto` varchar(255) NOT NULL,
  `Contexto` text,
  `Cierre` text,
  `FechaEmision` datetime NOT NULL,
  `FechaInicio` date NOT NULL,
  `FechaFin` date DEFAULT NULL,
  `DirectorNombre` varchar(150) NOT NULL,
  `DirectorCargo` varchar(50) DEFAULT NULL,
  `DirectorFirmaUrl` varchar(500) DEFAULT NULL,
  `DirectorSelloUrl` varchar(500) DEFAULT NULL,
  `FirmaElaboracion` varchar(100) NOT NULL,
  `Activo` tinyint DEFAULT '1',
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `DeletedAt` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) DEFAULT NULL,
  `UpdatedBy` varchar(50) DEFAULT NULL,
  `DeletedBy` varchar(50) DEFAULT NULL,
  `Versionn` int DEFAULT '1',
  `ReporteNombre` varchar(255) DEFAULT NULL,
  `ReporteRuta` varchar(500) DEFAULT NULL,
  `ReporteIdUsuario` int DEFAULT NULL,
  `ReporteAt` datetime DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `FK_comision_tipo` (`IdTipoComision`),
  KEY `FK_comision_plantilla` (`IdPlantilla`),
  KEY `FK_comision_estado` (`IdEstado`),
  KEY `FK_comision_facultad` (`IdFacultad`),
  KEY `FK_comision_reporte_usuario` (`ReporteIdUsuario`),
  CONSTRAINT `FK_comision_estado` FOREIGN KEY (`IdEstado`) REFERENCES `estado_comision` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `FK_comision_facultad` FOREIGN KEY (`IdFacultad`) REFERENCES `facultad` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `FK_comision_plantilla` FOREIGN KEY (`IdPlantilla`) REFERENCES `plantilla_documento` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `FK_comision_reporte_usuario` FOREIGN KEY (`ReporteIdUsuario`) REFERENCES `usuario` (`Id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_comision_tipo` FOREIGN KEY (`IdTipoComision`) REFERENCES `tipo_comision` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.comision: ~6 rows (aproximadamente)
INSERT INTO `comision` (`Id`, `IdTipoComision`, `IdPlantilla`, `IdEstado`, `IdFacultad`, `NOficio`, `Asunto`, `Contexto`, `Cierre`, `FechaEmision`, `FechaInicio`, `FechaFin`, `DirectorNombre`, `DirectorCargo`, `DirectorFirmaUrl`, `DirectorSelloUrl`, `FirmaElaboracion`, `Activo`, `CreatedAt`, `UpdatedAt`, `DeletedAt`, `CreatedBy`, `UpdatedBy`, `DeletedBy`, `Versionn`, `ReporteNombre`, `ReporteRuta`, `ReporteIdUsuario`, `ReporteAt`) VALUES
	(1, 3, 1, 3, 1, 'FCI-COMISION-2026-OCT-001', 'Comisión', NULL, ', con el propósito de fortalecer las competencias de los estudiantes próximos a egresar y contribuir a la mejora de los indicadores académicos de nuestra Facultad. Esta actividad se llevará a cabo {fechas}.', '2026-10-22 21:01:46', '2026-10-02', '2026-10-15', 'Dr. Jose Alonzo Perez Cruz', 'Director', 'https://www.nicepng.com/png/full/358-3588798_firma-del-presidente-signature.png', 'https://i.pinimg.com/736x/3b/e0/91/3be0912b73bd1a67cf18cc841137b315.jpg', 'C.c.p. Archivo FCI/jg', 1, '2026-10-22 21:01:46', '2026-10-24 21:23:58', NULL, 'JG1997', 'SU2003', NULL, 1, NULL, NULL, NULL, NULL),
	(2, 1, 1, 3, 1, 'FCI-COMISION-2026-OCT-002', 'Comisión', NULL, ', con el propósito de fortalecer las competencias de los estudiantes próximos a egresar y contribuir a la mejora de los indicadores académicos de nuestra Facultad. Esta actividad se llevará a cabo {fechas}.', '2026-10-22 21:12:44', '2026-10-02', '2026-10-15', 'Dr. Jose Alonzo Perez Cruz', 'Director', 'https://www.nicepng.com/png/full/358-3588798_firma-del-presidente-signature.png', 'https://i.pinimg.com/736x/3b/e0/91/3be0912b73bd1a67cf18cc841137b315.jpg', 'C.c.p. Archivo FCI/jg', 1, '2026-10-22 21:12:44', '2026-10-24 21:23:59', NULL, 'JG1997', 'SU2003', NULL, 1, NULL, NULL, NULL, NULL),
	(3, 3, 1, 1, 1, 'FCI-COMISION-2026-OCT-003', 'Comisión', NULL, ', con el propósito de fortalecer las competencias de los estudiantes próximos a egresar y contribuir a la mejora de los indicadores académicos de nuestra Facultad. Esta actividad se llevará a cabo {fechas}.', '2026-10-22 21:47:22', '2026-10-09', '2026-10-31', 'Dr. Jose Alonzo Perez Cruz', 'Director', 'https://www.nicepng.com/png/full/358-3588798_firma-del-presidente-signature.png', 'https://i.pinimg.com/736x/3b/e0/91/3be0912b73bd1a67cf18cc841137b315.jpg', 'C.c.p. Archivo FCI/ceghr', 1, '2026-10-22 21:47:22', '2026-10-24 21:24:00', NULL, 'JG1997', 'JG1997', NULL, 1, NULL, NULL, NULL, NULL),
	(4, 1, 1, 3, 1, 'FCI-COMISION-2026-OCT-004', 'Comisión', 'Impartir el taller de Preparación para el Examen General de Egreso de Licenciatura (EGEL) del programa educativo de Ingeniería en Sistemas Computacionales', ', con el propósito de fortalecer las competencias de los estudiantes próximos a egresar y contribuir a la mejora de los indicadores académicos de nuestra Facultad. Esta actividad se llevará a cabo {fechas}.', '2026-10-24 11:59:59', '2026-11-20', '2026-11-24', 'Dr. Jose Alonzo Perez Cruz', 'Director', 'https://www.nicepng.com/png/full/358-3588798_firma-del-presidente-signature.png', 'https://i.pinimg.com/736x/3b/e0/91/3be0912b73bd1a67cf18cc841137b315.jpg', 'C.c.p. Archivo FCI/ceghr', 1, '2026-10-24 11:59:59', '2026-10-24 21:24:01', NULL, 'JG1997', 'JG1997', NULL, 1, 'UNIVERSIDAD AUTONOMA DEL CARMEN.pdf', 'comisiones/FCI/FCI-COMISION-2026-OCT-004/reporte.pdf', 4, '2026-10-24 12:11:56'),
	(5, 3, 1, 1, 1, 'FCI-COMISION-2026-OCT-005', 'Comisión', 'reuniones y trabajos de academia ESTO ES UNA PRUEBA', ', con el propósito de fortalecer las competencias de los estudiantes próximos a egresar y contribuir a la mejora de los indicadores académicos de nuestra Facultad. Esta actividad se llevará a cabo {fechas}.', '2026-10-24 13:35:44', '2026-10-30', NULL, 'Dr. Jose Alonzo Perez Cruz', 'Director', 'https://www.nicepng.com/png/full/358-3588798_firma-del-presidente-signature.png', 'https://i.pinimg.com/736x/3b/e0/91/3be0912b73bd1a67cf18cc841137b315.jpg', 'C.c.p. Archivo FCI/ceghr', 1, '2026-10-24 13:35:44', '2026-10-24 21:24:01', NULL, 'JG1997', NULL, NULL, 1, NULL, NULL, NULL, NULL),
	(6, 10, 1, 3, 1, 'FCI-COMISION-2026-OCT-006', 'Comisión', '"Organizar el Ciclo de Cine", de la Facultad de Ciencias de la Información.', 'Esta actividad se llevará a cabo en la sala audiovisual de nuestra facultad, con el propósito de promover la convivencia de los alumnos universitarios y contribuir a su desarrollo humano y personal, mediante proyecciones que aborden temáticas de ciencia, tecnología, arte y valores humanos.', '2026-10-24 21:07:43', '2026-02-01', '2026-06-30', 'Dr. Jose Alonzo Perez Cruz', 'Director', 'https://www.nicepng.com/png/full/358-3588798_firma-del-presidente-signature.png', 'https://i.pinimg.com/736x/3b/e0/91/3be0912b73bd1a67cf18cc841137b315.jpg', 'C.c.p. Archivo FCI/niah', 1, '2026-10-24 21:07:43', '2026-10-24 21:26:29', NULL, 'RG1978', 'JR1970', NULL, 1, 'cartel unimap.pdf', 'comisiones/FCI/FCI-COMISION-2026-OCT-006/reporte.pdf', 4, '2026-10-24 21:26:29');

-- Volcando estructura para tabla cati.comision_log
CREATE TABLE IF NOT EXISTS `comision_log` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `IdComision` int NOT NULL,
  `IdEstado` int NOT NULL,
  `Accion` varchar(60) NOT NULL,
  `Descripcion` varchar(1000) DEFAULT NULL,
  `IdUsuario` int DEFAULT NULL,
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  KEY `IX_log_comision` (`IdComision`),
  KEY `FK_log_estado` (`IdEstado`),
  KEY `FK_log_usuario` (`IdUsuario`),
  CONSTRAINT `FK_log_comision` FOREIGN KEY (`IdComision`) REFERENCES `comision` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_log_estado` FOREIGN KEY (`IdEstado`) REFERENCES `estado_comision` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `FK_log_usuario` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`Id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.comision_log: ~20 rows (aproximadamente)
INSERT INTO `comision_log` (`Id`, `IdComision`, `IdEstado`, `Accion`, `Descripcion`, `IdUsuario`, `CreatedAt`) VALUES
	(1, 4, 1, 'Comisión creada', NULL, 1, '2026-10-24 11:59:59'),
	(2, 3, 1, 'Comisión anulada', NULL, 1, '2026-10-24 12:01:03'),
	(3, 4, 2, 'Comisión iniciada', NULL, 4, '2026-10-24 12:03:34'),
	(4, 4, 3, 'Reporte enviado', NULL, 4, '2026-10-24 12:11:56'),
	(5, 3, 1, 'Anulación revertida', NULL, 1, '2026-10-24 12:39:53'),
	(6, 4, 3, 'Comisión modificada', NULL, 1, '2026-10-24 12:45:22'),
	(7, 4, 2, 'Estado revertido', NULL, 1, '2026-10-24 12:56:59'),
	(8, 4, 3, 'Estado avanzado', NULL, 1, '2026-10-24 12:57:09'),
	(9, 5, 1, 'Comisión creada', NULL, 1, '2026-10-24 13:35:44'),
	(10, 2, 3, 'Constancia generada', NULL, 1, '2026-10-24 13:38:54'),
	(11, 6, 1, 'Comisión creada', NULL, 2, '2026-10-24 21:07:43'),
	(12, 6, 1, 'Comisión modificada', NULL, 2, '2026-10-24 21:11:50'),
	(13, 6, 1, 'Comisión modificada', NULL, 2, '2026-10-24 21:12:10'),
	(14, 6, 1, 'Comisión modificada', NULL, 2, '2026-10-24 21:20:38'),
	(15, 6, 1, 'Comisión modificada', NULL, 2, '2026-10-24 21:22:09'),
	(16, 6, 2, 'Comisión iniciada', NULL, 4, '2026-10-24 21:26:11'),
	(17, 6, 3, 'Reporte enviado', NULL, 4, '2026-10-24 21:26:29'),
	(18, 6, 3, 'Constancia generada', NULL, 2, '2026-10-24 21:27:03'),
	(19, 6, 3, 'Constancia eliminada', NULL, 2, '2026-10-24 21:30:00'),
	(20, 6, 3, 'Constancia generada', NULL, 2, '2026-10-24 21:30:11');

-- Volcando estructura para tabla cati.comision_profesor
CREATE TABLE IF NOT EXISTS `comision_profesor` (
  `IdComision` int NOT NULL,
  `IdUsuario` int NOT NULL,
  PRIMARY KEY (`IdComision`,`IdUsuario`),
  KEY `FK_cp_usuario` (`IdUsuario`),
  CONSTRAINT `FK_cp_comision` FOREIGN KEY (`IdComision`) REFERENCES `comision` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_cp_usuario` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.comision_profesor: ~8 rows (aproximadamente)
INSERT INTO `comision_profesor` (`IdComision`, `IdUsuario`) VALUES
	(2, 4),
	(3, 4),
	(4, 4),
	(6, 4),
	(1, 7),
	(2, 7),
	(3, 7),
	(5, 7);

-- Volcando estructura para tabla cati.constancia
CREATE TABLE IF NOT EXISTS `constancia` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `IdComision` int NOT NULL,
  `IdPlantilla` int NOT NULL,
  `Contexto` text,
  `Cierre` text,
  `FechaElaboracion` datetime NOT NULL,
  `DirectorNombre` varchar(150) NOT NULL,
  `DirectorCargo` varchar(50) DEFAULT NULL,
  `DirectorFirmaUrl` varchar(500) DEFAULT NULL,
  `DirectorSelloUrl` varchar(500) DEFAULT NULL,
  `FirmaElaboracion` varchar(100) NOT NULL,
  `Activo` tinyint DEFAULT '1',
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `DeletedAt` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) DEFAULT NULL,
  `UpdatedBy` varchar(50) DEFAULT NULL,
  `DeletedBy` varchar(50) DEFAULT NULL,
  `Versionn` int DEFAULT '1',
  PRIMARY KEY (`Id`),
  KEY `FK_constancia_comision` (`IdComision`),
  KEY `FK_constancia_plantilla` (`IdPlantilla`),
  CONSTRAINT `FK_constancia_comision` FOREIGN KEY (`IdComision`) REFERENCES `comision` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `FK_constancia_plantilla` FOREIGN KEY (`IdPlantilla`) REFERENCES `plantilla_documento` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.constancia: ~4 rows (aproximadamente)
INSERT INTO `constancia` (`Id`, `IdComision`, `IdPlantilla`, `Contexto`, `Cierre`, `FechaElaboracion`, `DirectorNombre`, `DirectorCargo`, `DirectorFirmaUrl`, `DirectorSelloUrl`, `FirmaElaboracion`, `Activo`, `CreatedAt`, `UpdatedAt`, `DeletedAt`, `CreatedBy`, `UpdatedBy`, `DeletedBy`, `Versionn`) VALUES
	(1, 1, 2, NULL, '. Esta actividad se enmarca en el plan de desarrollo de la Facultad.', '2026-10-22 21:34:17', 'Dr. Jose Alonzo Perez Cruz', 'Director', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTsBB58qlamAk5eHoP7AZEUZuu3JGvVloILo1JNTnNUYQ&s=10', 'https://i.pinimg.com/736x/3b/e0/91/3be0912b73bd1a67cf18cc841137b315.jpg', 'C.c.p. Archivo FGPSU/s', 1, '2026-10-22 21:34:17', '2026-10-24 17:56:27', NULL, 'SU2003', NULL, NULL, 1),
	(2, 2, 2, NULL, '. Esta actividad se enmarca en el plan de desarrollo de la Facultad.', '2026-10-24 13:38:49', 'Dr. Jose Alonzo Perez Cruz', 'Director', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTsBB58qlamAk5eHoP7AZEUZuu3JGvVloILo1JNTnNUYQ&s=10', 'https://i.pinimg.com/736x/3b/e0/91/3be0912b73bd1a67cf18cc841137b315.jpg', 'C.c.p. Archivo FCI/ceghr', 1, '2026-10-24 13:38:49', '2026-10-24 17:56:27', NULL, 'JG1997', NULL, NULL, 1),
	(3, 6, 2, '"Organizar el Ciclo de Cine", de la Facultad de Ciencias de la Información.', '', '2026-10-24 21:26:58', 'Dr. Jose Alonzo Perez Cruz', 'Director', 'https://www.nicepng.com/png/full/358-3588798_firma-del-presidente-signature.png', 'https://i.pinimg.com/736x/3b/e0/91/3be0912b73bd1a67cf18cc841137b315.jpg', 'C.c.p. Archivo FCI/niah', 0, '2026-10-24 21:26:58', '2026-10-24 21:30:00', '2026-10-24 21:30:00', 'RG1978', NULL, 'RG1978', 1),
	(4, 6, 2, '"Organizar el Ciclo de Cine", de la Facultad de Ciencias de la Información.', '', '2026-10-24 21:30:05', 'Dr. Jose Alonzo Perez Cruz', 'Director', 'https://www.nicepng.com/png/full/358-3588798_firma-del-presidente-signature.png', 'https://i.pinimg.com/736x/3b/e0/91/3be0912b73bd1a67cf18cc841137b315.jpg', 'C.c.p. Archivo FCI/niah', 1, '2026-10-24 21:30:05', NULL, NULL, 'RG1978', NULL, NULL, 1);

-- Volcando estructura para tabla cati.estado_comision
CREATE TABLE IF NOT EXISTS `estado_comision` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(50) NOT NULL,
  `Activo` tinyint DEFAULT '1',
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(50) DEFAULT 'admin',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.estado_comision: ~5 rows (aproximadamente)
INSERT INTO `estado_comision` (`Id`, `Nombre`, `Activo`, `CreatedAt`, `CreatedBy`) VALUES
	(1, 'Sin iniciar', 1, '2026-10-22 19:26:26', 'admin'),
	(2, 'En Proceso', 1, '2026-10-22 19:26:26', 'admin'),
	(3, 'Finalizado', 1, '2026-10-22 19:26:26', 'admin'),
	(4, 'Rechazado', 1, '2026-10-24 11:35:31', 'admin'),
	(5, 'Refutado', 1, '2026-10-24 11:35:31', 'admin');

-- Volcando estructura para tabla cati.facultad
CREATE TABLE IF NOT EXISTS `facultad` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Clave` varchar(50) DEFAULT NULL,
  `Nombre` varchar(50) DEFAULT NULL,
  `Descripcion` varchar(500) DEFAULT NULL,
  `Activo` tinyint DEFAULT NULL,
  `CreatedAt` datetime DEFAULT NULL,
  `UpdatedAt` datetime DEFAULT NULL,
  `DeletedAt` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) DEFAULT NULL,
  `UpdatedBy` varchar(50) DEFAULT NULL,
  `DeletedBy` varchar(50) DEFAULT NULL,
  `Versionn` int DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.facultad: ~3 rows (aproximadamente)
INSERT INTO `facultad` (`Id`, `Clave`, `Nombre`, `Descripcion`, `Activo`, `CreatedAt`, `UpdatedAt`, `DeletedAt`, `CreatedBy`, `UpdatedBy`, `DeletedBy`, `Versionn`) VALUES
	(1, 'FCI', 'Facultad de Ciencias de la Informacion', 'Facultad de Ciencias de la Informacion', 1, '2026-10-04 15:07:44', NULL, NULL, 'admin', NULL, NULL, 1),
	(2, 'FCEA', 'Facultad de Ciencias Economico Administrativas', 'Facultad de Ciencias Economico Administrativas', 1, '2026-10-04 15:08:28', NULL, NULL, 'admin', NULL, NULL, 1),
	(3, 'FGPSU', 'Facultad global', 'Facultad global con permiso de super usuario y admin', 1, NULL, NULL, NULL, 'admin', NULL, NULL, 1);

-- Volcando estructura para tabla cati.modulo
CREATE TABLE IF NOT EXISTS `modulo` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Clave` varchar(50) DEFAULT NULL,
  `Nombre` varchar(50) DEFAULT NULL,
  `Descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `UrlLogo` varchar(200) DEFAULT NULL,
  `Activo` tinyint DEFAULT NULL,
  `CreatedAt` datetime DEFAULT NULL,
  `UpdatedAt` datetime DEFAULT NULL,
  `DeletedAt` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) DEFAULT NULL,
  `UpdatedBy` varchar(50) DEFAULT NULL,
  `DeletedBy` varchar(50) DEFAULT NULL,
  `Versionn` int DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.modulo: ~4 rows (aproximadamente)
INSERT INTO `modulo` (`Id`, `Clave`, `Nombre`, `Descripcion`, `UrlLogo`, `Activo`, `CreatedAt`, `UpdatedAt`, `DeletedAt`, `CreatedBy`, `UpdatedBy`, `DeletedBy`, `Versionn`) VALUES
	(1, 'MSC', 'SisComisiones', 'Modulo de Comisiones Para Profesores y Secretarias', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQGEBJkCL-xp2pcIQezMXTQTrDWwGya-LGB157tgWDaSQ&s', 1, '2026-10-04 00:04:28', '2026-10-04 00:04:28', NULL, 'admin', 'admin', NULL, 1),
	(2, 'MP', 'CATI', 'Modulo principal para invitados y usuarios', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQGEBJkCL-xp2pcIQezMXTQTrDWwGya-LGB157tgWDaSQ&s', 1, '2026-10-04 15:11:24', NULL, NULL, 'admin', NULL, NULL, 1),
	(3, 'MGSU', 'Global', 'Modulo Global Acceso a todos super usuario', 'https://static.wikia.nocookie.net/reinoanimalia/images/f/f8/Hiena_rayada.png/revision/latest?cb=20260321224523&path-prefix=es', 1, '2026-10-04 15:31:48', NULL, NULL, 'SUsuario', NULL, NULL, 1),
	(4, 'MA', 'Administracion', 'Modulo para el administrador', 'https://www.unacar.mx/unacar/Documentos/logotipo22-26/EDUCANDO-VF-SLOGAN-(IDENTIDAD)-NEGRO.png', 1, '2026-10-04 15:35:10', NULL, NULL, 'SUsuario', NULL, NULL, 1);

-- Volcando estructura para tabla cati.nivel_acceso
CREATE TABLE IF NOT EXISTS `nivel_acceso` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Clave` varchar(50) DEFAULT NULL,
  `Nombre` varchar(50) DEFAULT NULL,
  `Descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `IdModulo` int NOT NULL,
  `Activo` tinyint DEFAULT NULL,
  `CreatedAt` datetime DEFAULT NULL,
  `UpdatedAt` datetime DEFAULT NULL,
  `DeletedAt` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) DEFAULT NULL,
  `UpdatedBy` varchar(50) DEFAULT NULL,
  `DeletedBy` varchar(50) DEFAULT NULL,
  `Versionn` int DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `FK_nivel_acceso_modulo` (`IdModulo`),
  CONSTRAINT `FK_nivel_acceso_modulo` FOREIGN KEY (`IdModulo`) REFERENCES `modulo` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.nivel_acceso: ~5 rows (aproximadamente)
INSERT INTO `nivel_acceso` (`Id`, `Clave`, `Nombre`, `Descripcion`, `IdModulo`, `Activo`, `CreatedAt`, `UpdatedAt`, `DeletedAt`, `CreatedBy`, `UpdatedBy`, `DeletedBy`, `Versionn`) VALUES
	(1, 'MCNA1', 'MCNA1', 'Nivel de acceso 1 del modulo de comisiones', 1, 1, '2026-10-04 01:14:58', '2026-10-04 01:15:00', NULL, 'admin', 'admin', NULL, 1),
	(2, 'MCNA2', 'MCNA2', 'Nivel de acceso 2 del modulo de comisiones', 1, 1, '2026-10-04 01:17:18', '2026-10-04 01:17:20', NULL, 'admin', 'admin', NULL, 1),
	(3, 'MCNA3', 'MCNA3', 'Nivel de acceso 3 del modulo de comisiones', 1, 1, '2026-10-04 01:17:59', '2026-10-04 01:18:00', NULL, 'admin', 'admin', NULL, 1),
	(4, 'MGNATSU', 'MGNATSU', 'Nivel de acceso Super Usuario, Modulo Global Nivel de acceso Totao sUPER user', 3, 1, '2026-10-04 15:37:42', NULL, NULL, 'SUsuario', NULL, NULL, 1),
	(5, 'MANAT', 'MANAT', 'Nivel de acceso total del modulo de Administracion', 4, 1, '2026-10-04 15:40:00', NULL, NULL, 'SUsuario', NULL, NULL, 1);

-- Volcando estructura para tabla cati.notificacion
CREATE TABLE IF NOT EXISTS `notificacion` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `IdUsuario` int NOT NULL,
  `Tipo` varchar(30) NOT NULL,
  `Titulo` varchar(150) NOT NULL,
  `Mensaje` varchar(500) DEFAULT NULL,
  `Url` varchar(500) DEFAULT NULL,
  `Clave` varchar(120) DEFAULT NULL,
  `LeidaAt` datetime DEFAULT NULL,
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `UQ_notif_clave` (`IdUsuario`,`Clave`),
  KEY `IX_notif_usuario` (`IdUsuario`,`LeidaAt`),
  CONSTRAINT `FK_notif_usuario` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.notificacion: ~22 rows (aproximadamente)
INSERT INTO `notificacion` (`Id`, `IdUsuario`, `Tipo`, `Titulo`, `Mensaje`, `Url`, `Clave`, `LeidaAt`, `CreatedAt`) VALUES
	(1, 4, 'asignacion', 'Nueva comisión asignada', 'Se te asignó la comisión FCI-COMISION-2026-OCT-004: Comision', 'http://127.0.0.1:8000/mi-panel/4', NULL, '2026-10-24 12:03:29', '2026-10-24 12:00:05'),
	(2, 3, 'estado', 'Comisión iniciada', 'FCI-COMISION-2026-OCT-004 · En Proceso', 'http://127.0.0.1:8000/modulos/MSC/comisiones/4', NULL, NULL, '2026-10-24 12:03:34'),
	(3, 1, 'estado', 'Comisión iniciada', 'FCI-COMISION-2026-OCT-004 · En Proceso', 'http://127.0.0.1:8000/modulos/MSC/comisiones/4', NULL, NULL, '2026-10-24 12:03:34'),
	(4, 3, 'finalizada', 'Reporte enviado', 'FCI-COMISION-2026-OCT-004 · Finalizado', 'http://127.0.0.1:8000/modulos/MSC/comisiones/4', NULL, NULL, '2026-10-24 12:11:56'),
	(5, 1, 'finalizada', 'Reporte enviado', 'FCI-COMISION-2026-OCT-004 · Finalizado', 'http://127.0.0.1:8000/modulos/MSC/comisiones/4', NULL, '2026-10-24 12:55:53', '2026-10-24 12:11:56'),
	(6, 4, 'estado', 'Estado revertido', 'FCI-COMISION-2026-OCT-004 · En Proceso', 'http://127.0.0.1:8000/mi-panel/4', NULL, NULL, '2026-10-24 12:56:59'),
	(7, 3, 'estado', 'Estado revertido', 'FCI-COMISION-2026-OCT-004 · En Proceso', 'http://127.0.0.1:8000/modulos/MSC/comisiones/4', NULL, NULL, '2026-10-24 12:56:59'),
	(8, 4, 'finalizada', 'Estado avanzado', 'FCI-COMISION-2026-OCT-004 · Finalizado', 'http://127.0.0.1:8000/mi-panel/4', NULL, NULL, '2026-10-24 12:57:09'),
	(9, 3, 'finalizada', 'Estado avanzado', 'FCI-COMISION-2026-OCT-004 · Finalizado', 'http://127.0.0.1:8000/modulos/MSC/comisiones/4', NULL, NULL, '2026-10-24 12:57:09'),
	(10, 7, 'asignacion', 'Nueva comisión asignada', 'Se te asignó la comisión FCI-COMISION-2026-OCT-005: Comision', 'http://127.0.0.1:8000/mi-panel/5', NULL, NULL, '2026-10-24 13:35:49'),
	(11, 4, 'constancia', 'Constancia generada', 'Comisión FCI-COMISION-2026-OCT-002', 'http://127.0.0.1:8000/mi-panel/2', NULL, NULL, '2026-10-24 13:38:54'),
	(12, 7, 'constancia', 'Constancia generada', 'Comisión FCI-COMISION-2026-OCT-002', 'http://127.0.0.1:8000/mi-panel/2', NULL, NULL, '2026-10-24 13:38:54'),
	(13, 4, 'asignacion', 'Nueva comisión asignada', 'Se te asignó la comisión FCI-COMISION-2026-OCT-006: Comision', 'http://127.0.0.1:8000/mi-panel/6', NULL, NULL, '2026-10-24 21:07:49'),
	(14, 4, 'vencimiento', 'Comisión vencida', 'FCI-COMISION-2026-OCT-006 venció el 30/06/26', 'http://127.0.0.1:8000/mi-panel/6', 'venc:6:2026-06-30:v', NULL, '2026-10-24 21:25:36'),
	(15, 3, 'vencimiento', 'Comisión vencida', 'FCI-COMISION-2026-OCT-006 venció el 30/06/26', 'http://127.0.0.1:8000/modulos/MSC/comisiones/6', 'venc:6:2026-06-30:v', NULL, '2026-10-24 21:25:36'),
	(16, 2, 'vencimiento', 'Comisión vencida', 'FCI-COMISION-2026-OCT-006 venció el 30/06/26', 'http://127.0.0.1:8000/modulos/MSC/comisiones/6', 'venc:6:2026-06-30:v', NULL, '2026-10-24 21:25:36'),
	(17, 3, 'estado', 'Comisión iniciada', 'FCI-COMISION-2026-OCT-006 · En Proceso', 'http://127.0.0.1:8000/modulos/MSC/comisiones/6', NULL, NULL, '2026-10-24 21:26:11'),
	(18, 2, 'estado', 'Comisión iniciada', 'FCI-COMISION-2026-OCT-006 · En Proceso', 'http://127.0.0.1:8000/modulos/MSC/comisiones/6', NULL, NULL, '2026-10-24 21:26:11'),
	(19, 3, 'finalizada', 'Reporte enviado', 'FCI-COMISION-2026-OCT-006 · Finalizado', 'http://127.0.0.1:8000/modulos/MSC/comisiones/6', NULL, NULL, '2026-10-24 21:26:29'),
	(20, 2, 'finalizada', 'Reporte enviado', 'FCI-COMISION-2026-OCT-006 · Finalizado', 'http://127.0.0.1:8000/modulos/MSC/comisiones/6', NULL, NULL, '2026-10-24 21:26:29'),
	(21, 4, 'constancia', 'Constancia generada', 'Comisión FCI-COMISION-2026-OCT-006', 'http://127.0.0.1:8000/mi-panel/6', NULL, NULL, '2026-10-24 21:27:03'),
	(22, 4, 'constancia', 'Constancia generada', 'Comisión FCI-COMISION-2026-OCT-006', 'http://127.0.0.1:8000/mi-panel/6', NULL, NULL, '2026-10-24 21:30:11');

-- Volcando estructura para tabla cati.plantilla_documento
CREATE TABLE IF NOT EXISTS `plantilla_documento` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `TipoDocumento` varchar(50) NOT NULL,
  `NombreUniversidad` varchar(255) DEFAULT 'UNIVERSIDAD AUTÓNOMA DEL CARMEN',
  `Facultad` varchar(255) DEFAULT 'FACULTAD DE CIENCIAS DE LA INFORMACIÓN',
  `Lugar` varchar(255) DEFAULT 'Ciudad del Carmen, Campeche',
  `LogoIzquierdo` varchar(500) DEFAULT NULL,
  `LogoDerecho` varchar(500) DEFAULT NULL,
  `Introduccion` text,
  `Cierre` text,
  `Despedida` text,
  `Activo` tinyint DEFAULT '1',
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `DeletedAt` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) DEFAULT NULL,
  `UpdatedBy` varchar(50) DEFAULT NULL,
  `DeletedBy` varchar(50) DEFAULT NULL,
  `Versionn` int DEFAULT '1',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.plantilla_documento: ~2 rows (aproximadamente)
INSERT INTO `plantilla_documento` (`Id`, `TipoDocumento`, `NombreUniversidad`, `Facultad`, `Lugar`, `LogoIzquierdo`, `LogoDerecho`, `Introduccion`, `Cierre`, `Despedida`, `Activo`, `CreatedAt`, `UpdatedAt`, `DeletedAt`, `CreatedBy`, `UpdatedBy`, `DeletedBy`, `Versionn`) VALUES
	(1, 'Comisión', 'UNIVERSIDAD AUTÓNOMA DEL CARMEN', 'FACULTAD DE CIENCIAS DE LA INFORMACIÓN', 'Ciudad del Carmen, Campeche', 'https://www.unacar.mx/unacar/imagenes/escudo_unacar.png', 'https://www.unacar.mx/unacar/Documentos/logotipo22-26/EDUCANDO-VF-SLOGAN-(IDENTIDAD)-NEGRO.png', 'Por medio de la presente se le informa que ha sido comisionado para ', '', 'Agradecemos de antemano su colaboración y compromiso. Sin otro particular, reciba un cordial saludo.', 1, '2026-10-22 19:31:48', '2026-10-24 21:23:19', NULL, 'admin', NULL, NULL, 1),
	(2, 'Constancia', 'UNIVERSIDAD AUTÓNOMA DEL CARMEN', 'FACULTAD DE CIENCIAS DE LA INFORMACIÓN', 'Ciudad del Carmen, Campeche', 'https://www.unacar.mx/unacar/imagenes/escudo_unacar.png', 'https://www.unacar.mx/unacar/Documentos/logotipo22-26/EDUCANDO-VF-SLOGAN-(IDENTIDAD)-NEGRO.png', 'Por medio de la presente, se hace constar que atendió satisfactoriamente la comisión asignada por la Facultad, la cual consistió en ', '', 'Sin otro particular, me despido enviándoles un cordial saludo.', 1, '2026-10-22 19:33:17', '2026-10-24 20:52:17', NULL, 'admin', NULL, NULL, 1);

-- Volcando estructura para tabla cati.rol
CREATE TABLE IF NOT EXISTS `rol` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Clave` varchar(50) NOT NULL,
  `Nombre` varchar(80) NOT NULL,
  `Orden` int NOT NULL DEFAULT '99',
  `Activo` tinyint DEFAULT '1',
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(50) DEFAULT 'admin',
  PRIMARY KEY (`Id`),
  UNIQUE KEY `UQ_rol_clave` (`Clave`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.rol: ~6 rows (aproximadamente)
INSERT INTO `rol` (`Id`, `Clave`, `Nombre`, `Orden`, `Activo`, `CreatedAt`, `CreatedBy`) VALUES
	(1, 'SuperUsuario', 'Super usuario', 1, 1, '2026-10-24 17:56:27', 'admin'),
	(2, 'Administrador', 'Administrador', 2, 1, '2026-10-24 17:56:27', 'admin'),
	(3, 'Director', 'Director', 3, 1, '2026-10-24 17:56:27', 'admin'),
	(4, 'SecretariaAcademica', 'Secretaría Académica', 4, 1, '2026-10-24 17:56:27', 'admin'),
	(5, 'Secretaria', 'Secretaria', 5, 1, '2026-10-24 17:56:27', 'admin'),
	(6, 'Profesor', 'Profesor', 6, 1, '2026-10-24 17:56:27', 'admin');

-- Volcando estructura para tabla cati.tipo_comision
CREATE TABLE IF NOT EXISTS `tipo_comision` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(100) NOT NULL,
  `Descripcion` varchar(255) DEFAULT NULL,
  `Activo` tinyint DEFAULT '1',
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `DeletedAt` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) DEFAULT NULL,
  `UpdatedBy` varchar(50) DEFAULT NULL,
  `DeletedBy` varchar(50) DEFAULT NULL,
  `Versionn` int DEFAULT '1',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.tipo_comision: ~15 rows (aproximadamente)
INSERT INTO `tipo_comision` (`Id`, `Nombre`, `Descripcion`, `Activo`, `CreatedAt`, `UpdatedAt`, `DeletedAt`, `CreatedBy`, `UpdatedBy`, `DeletedBy`, `Versionn`) VALUES
	(1, 'Sinodales', '', 1, '2026-10-22 19:26:26', '2026-10-24 20:41:17', NULL, 'admin', NULL, NULL, 1),
	(2, 'Revisores', '', 1, '2026-10-22 19:26:26', '2026-10-24 20:41:19', NULL, 'admin', NULL, NULL, 1),
	(3, 'Director', '', 1, '2026-10-22 19:26:26', '2026-10-24 20:41:20', NULL, 'admin', NULL, NULL, 1),
	(4, 'Codirector', '', 1, '2026-10-24 20:32:57', '2026-10-24 20:41:21', NULL, 'admin', NULL, NULL, 1),
	(5, 'Asesor', '', 1, '2026-10-24 20:33:33', '2026-10-24 20:41:22', NULL, NULL, NULL, NULL, 1),
	(6, 'Comite de Esdeped', NULL, 1, '2026-10-24 20:34:22', NULL, NULL, NULL, NULL, NULL, 1),
	(7, 'Promocion Oferta Educativa', NULL, 1, '2026-10-24 20:34:41', NULL, NULL, NULL, NULL, NULL, 1),
	(8, 'Feria de Posgrado', NULL, 1, '2026-10-24 20:34:57', NULL, NULL, NULL, NULL, NULL, 1),
	(9, 'Seguimiento de Egresados', NULL, 1, '2026-10-24 20:35:13', NULL, NULL, NULL, NULL, NULL, 1),
	(10, 'Eventos FCI', NULL, 1, '2026-10-24 20:35:25', NULL, NULL, NULL, NULL, NULL, 1),
	(11, 'Examen de Admision', NULL, 1, '2026-10-24 20:35:42', NULL, NULL, NULL, NULL, NULL, 1),
	(12, 'Propedeutico', NULL, 1, '2026-10-24 20:36:06', NULL, NULL, NULL, NULL, NULL, 1),
	(13, 'Comision Interna', NULL, 1, '2026-10-24 20:36:19', NULL, NULL, NULL, NULL, NULL, 1),
	(14, 'Gestoria', NULL, 1, '2026-10-24 20:36:30', NULL, NULL, NULL, NULL, NULL, 1),
	(15, 'Academia', NULL, 1, '2026-10-24 20:36:41', NULL, NULL, NULL, NULL, NULL, 1);

-- Volcando estructura para tabla cati.tipo_permiso
CREATE TABLE IF NOT EXISTS `tipo_permiso` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Clave` varchar(50) DEFAULT NULL,
  `Nombre` varchar(50) DEFAULT NULL,
  `Descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `Activo` tinyint DEFAULT NULL,
  `CreatedAt` datetime DEFAULT NULL,
  `UpdatedAt` datetime DEFAULT NULL,
  `DeletedAt` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) DEFAULT NULL,
  `UpdatedBy` varchar(50) DEFAULT NULL,
  `DeletedBy` varchar(50) DEFAULT NULL,
  `Versionn` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.tipo_permiso: ~4 rows (aproximadamente)
INSERT INTO `tipo_permiso` (`Id`, `Clave`, `Nombre`, `Descripcion`, `Activo`, `CreatedAt`, `UpdatedAt`, `DeletedAt`, `CreatedBy`, `UpdatedBy`, `DeletedBy`, `Versionn`) VALUES
	(1, 'R', 'R', 'Leer', 1, '2026-10-04 00:39:17', '2026-10-04 00:39:17', NULL, 'admin', 'admin', NULL, '1'),
	(2, 'CR', 'CR', 'Crear y Leer', 1, '2026-10-04 00:43:23', '2026-10-04 00:43:23', NULL, 'admin', 'admin', NULL, '1'),
	(3, 'CRU', 'CRU', 'Crear, leer y actualizar', 1, '2026-10-04 00:45:05', '2026-10-04 00:45:05', NULL, 'admin', 'admin', NULL, '1'),
	(4, 'CRUD', 'CRUD', 'Crear, leer, actualizar y eliminar', 1, '2026-10-04 00:45:29', '2026-10-04 00:45:29', NULL, 'admin', 'admin', NULL, '1');

-- Volcando estructura para tabla cati.usuario
CREATE TABLE IF NOT EXISTS `usuario` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(50) DEFAULT NULL,
  `Apellido` varchar(50) DEFAULT NULL,
  `Matricula` varchar(20) DEFAULT NULL,
  `Correo` varchar(50) DEFAULT NULL,
  `Iniciales` varchar(10) DEFAULT NULL,
  `Contrasena` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `IdRol` int NOT NULL,
  `GradoAcademico` varchar(50) DEFAULT NULL,
  `Genero` tinyint DEFAULT NULL,
  `IdFacultad` int DEFAULT NULL,
  `Activo` tinyint DEFAULT NULL,
  `CreatedAt` datetime DEFAULT NULL,
  `UpdatedAt` datetime DEFAULT NULL,
  `DeletedAt` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) DEFAULT NULL,
  `UpdatedBy` varchar(50) DEFAULT NULL,
  `DeletedBy` varchar(50) DEFAULT NULL,
  `Versionn` int DEFAULT NULL,
  `FirmaUrl` varchar(500) DEFAULT NULL,
  `SelloUrl` varchar(500) DEFAULT NULL,
  `FotoUrl` varchar(500) DEFAULT NULL,
  `UltimaConexion` datetime DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `FK_usuario_facultad` (`IdFacultad`),
  KEY `FK_usuario_rol` (`IdRol`),
  CONSTRAINT `FK_usuario_facultad` FOREIGN KEY (`IdFacultad`) REFERENCES `facultad` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `FK_usuario_rol` FOREIGN KEY (`IdRol`) REFERENCES `rol` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci CHECKSUM=1;

-- Volcando datos para la tabla cati.usuario: ~11 rows (aproximadamente)
INSERT INTO `usuario` (`Id`, `Nombre`, `Apellido`, `Matricula`, `Correo`, `Iniciales`, `Contrasena`, `IdRol`, `GradoAcademico`, `Genero`, `IdFacultad`, `Activo`, `CreatedAt`, `UpdatedAt`, `DeletedAt`, `CreatedBy`, `UpdatedBy`, `DeletedBy`, `Versionn`, `FirmaUrl`, `SelloUrl`, `FotoUrl`, `UltimaConexion`) VALUES
	(1, 'Chuina Estrellita Guadalupe', 'Hernandez Rosado', 'JG1997', 'juani@gmail.com', 'ceghr', '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 5, 'Mtra', 1, 1, 1, '2026-10-03 23:46:57', '2026-10-25 15:51:38', NULL, 'admin', 'RG1995', NULL, 1, NULL, NULL, 'image/usuarios/FCI/Secretaria/JG1997.webp', '2026-10-25 19:30:24'),
	(2, 'Nelly Isabel', 'Angel Hernandez', 'RG1978', 'Rosy@gmail.com', 'niah', '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 5, 'Mtra', 1, 1, 1, '2026-10-03 23:46:57', '2026-10-04 01:11:12', NULL, 'admin', 'admin', NULL, 1, NULL, NULL, NULL, '2026-10-24 21:41:11'),
	(3, 'Saide Dariola', 'Duran Martin', 'RG1995', 'Rosarito@gmail.com', 'sddm', '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 4, 'Dra', 1, 1, 1, '2026-10-03 23:46:57', '2026-10-03 23:46:57', NULL, 'admin', 'admin', NULL, 1, NULL, NULL, NULL, '2026-10-25 19:46:23'),
	(4, 'Jose Angel', 'Perez Rejon', 'JR1970', 'jose@gmail.com', 'japr', '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 6, 'Dr', 0, 1, 1, '2026-10-04 01:20:05', '2026-10-25 15:48:28', NULL, 'admin', 'JR1970', NULL, 1, NULL, NULL, 'image/usuarios/FCI/Profesor/JR1970.jpg', '2026-10-25 19:34:49'),
	(5, 'SUsuario', NULL, 'SU2003', 'cg7030295@gmail.com', NULL, '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 1, 'Goat', 0, 3, 1, '2026-10-04 15:28:54', '2026-10-24 19:06:34', NULL, 'SUsuario', 'SU2003', NULL, 1, NULL, NULL, 'image/usuarios/FGPSU/SuperUsuario/SU2003.png', '2026-10-25 19:42:07'),
	(6, 'Administrador', '1', 'A2003', 'cg7030296@gmail.com', NULL, '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 2, 'Admin', 0, 1, 1, '2026-10-04 15:46:28', NULL, NULL, 'SUsuario', NULL, NULL, 1, NULL, NULL, NULL, '2026-10-25 19:31:41'),
	(7, 'Guerra', 'Guerrero', 'GG1234', 'gg@gmail.com', NULL, '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 6, 'Dr', 0, 1, 1, '2026-10-22 18:21:58', '2026-10-22 18:21:59', NULL, 'admin', NULL, NULL, 1, NULL, NULL, NULL, NULL),
	(8, 'Jose Alonzo', 'Perez Cruz', 'JAPC2025', 'jperez@unacar.mx', 'japc', '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 3, 'Dr', 0, 1, 1, '2026-10-22 19:26:26', NULL, NULL, 'admin', NULL, NULL, 1, 'https://www.nicepng.com/png/full/358-3588798_firma-del-presidente-signature.png', 'https://i.pinimg.com/736x/3b/e0/91/3be0912b73bd1a67cf18cc841137b315.jpg', NULL, NULL),
	(9, 'Rubi', 'Ramon', 'RR1972', 'rubi@gmail.com', 'rr', '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 6, 'Dra', 1, 1, 1, '2026-10-25 18:09:32', NULL, NULL, 'admin', NULL, NULL, 1, NULL, NULL, NULL, NULL),
	(10, 'Roxana', NULL, 'RJ1978', 'roxana@gmail.com', 'rjs', '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 6, 'Mtra', 1, 2, 1, '2026-10-25 18:16:23', NULL, NULL, 'admin', 'SU2003', NULL, 1, NULL, NULL, NULL, NULL),
	(11, 'Juanjo', NULL, 'JU9344', 'juanjo@gmail.com', 'jjo', NULL, 5, 'Mtro', 0, 3, 1, '2026-10-25 18:16:21', NULL, NULL, 'admin', NULL, NULL, 1, NULL, NULL, NULL, NULL);

-- Volcando estructura para tabla cati.usuario_tipo_comision
CREATE TABLE IF NOT EXISTS `usuario_tipo_comision` (
  `IdUsuario` int NOT NULL,
  `IdTipoComision` int NOT NULL,
  PRIMARY KEY (`IdUsuario`,`IdTipoComision`),
  KEY `FK_utc_tipo_comision` (`IdTipoComision`),
  CONSTRAINT `FK_utc_tipo_comision` FOREIGN KEY (`IdTipoComision`) REFERENCES `tipo_comision` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_utc_usuario` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.usuario_tipo_comision: ~15 rows (aproximadamente)
INSERT INTO `usuario_tipo_comision` (`IdUsuario`, `IdTipoComision`) VALUES
	(1, 1),
	(1, 2),
	(1, 3),
	(1, 4),
	(1, 5),
	(2, 6),
	(2, 7),
	(2, 8),
	(2, 9),
	(2, 10),
	(2, 11),
	(2, 12),
	(2, 13),
	(2, 14),
	(2, 15);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
