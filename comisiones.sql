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
  `IdTipoPermiso` int NOT NULL,
  `IdNivelAcceso` int NOT NULL,
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

-- Volcando estructura para tabla cati.comision
CREATE TABLE IF NOT EXISTS `comision` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `IdTipoComision` int NOT NULL,
  `IdPlantilla` int NOT NULL,
  `IdEstado` int NOT NULL,
  `IdFacultad` int NOT NULL,
  `NOficio` varchar(50) NOT NULL,
  `Asunto` varchar(255) NOT NULL,
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
  PRIMARY KEY (`Id`),
  KEY `FK_comision_tipo` (`IdTipoComision`),
  KEY `FK_comision_plantilla` (`IdPlantilla`),
  KEY `FK_comision_estado` (`IdEstado`),
  KEY `FK_comision_facultad` (`IdFacultad`),
  CONSTRAINT `FK_comision_estado` FOREIGN KEY (`IdEstado`) REFERENCES `estado_comision` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `FK_comision_facultad` FOREIGN KEY (`IdFacultad`) REFERENCES `facultad` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `FK_comision_plantilla` FOREIGN KEY (`IdPlantilla`) REFERENCES `plantilla_documento` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `FK_comision_tipo` FOREIGN KEY (`IdTipoComision`) REFERENCES `tipo_comision` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.comision: ~0 rows (aproximadamente)
INSERT INTO `comision` (`Id`, `IdTipoComision`, `IdPlantilla`, `IdEstado`, `IdFacultad`, `NOficio`, `Asunto`, `FechaEmision`, `FechaInicio`, `FechaFin`, `DirectorNombre`, `DirectorCargo`, `DirectorFirmaUrl`, `DirectorSelloUrl`, `FirmaElaboracion`, `Activo`, `CreatedAt`, `UpdatedAt`, `DeletedAt`, `CreatedBy`, `UpdatedBy`, `DeletedBy`, `Versionn`) VALUES
	(1, 3, 1, 3, 1, 'FCI-COMISION-2026-OCT-001', 'Comision', '2026-10-22 21:01:46', '2026-10-02', '2026-10-15', 'Dr. Jose Alonzo Perez Cruz', 'Director', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTsBB58qlamAk5eHoP7AZEUZuu3JGvVloILo1JNTnNUYQ&s=10', 'https://i.pinimg.com/736x/3b/e0/91/3be0912b73bd1a67cf18cc841137b315.jpg', 'C.c.p. Archivo FCI/jg', 1, '2026-10-22 21:01:46', '2026-10-22 21:34:15', NULL, 'JG1997', 'SU2003', NULL, 1),
	(2, 1, 1, 1, 1, 'FCI-COMISION-2026-OCT-002', 'Comision', '2026-10-22 21:12:44', '2026-10-02', '2026-10-15', 'Dr. Jose Alonzo Perez Cruz', 'Director', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTsBB58qlamAk5eHoP7AZEUZuu3JGvVloILo1JNTnNUYQ&s=10', 'https://i.pinimg.com/736x/3b/e0/91/3be0912b73bd1a67cf18cc841137b315.jpg', 'C.c.p. Archivo FCI/jg', 1, '2026-10-22 21:12:44', '2026-10-22 21:33:47', NULL, 'JG1997', NULL, NULL, 1),
	(3, 3, 1, 1, 1, 'FCI-COMISION-2026-OCT-003', 'lol', '2026-10-22 21:47:22', '2026-10-09', '2026-10-31', 'Dr. Jose Alonzo Perez Cruz', 'Director', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTsBB58qlamAk5eHoP7AZEUZuu3JGvVloILo1JNTnNUYQ&s=10', 'https://i.pinimg.com/736x/3b/e0/91/3be0912b73bd1a67cf18cc841137b315.jpg', 'C.c.p. Archivo FCI/ceghr', 1, '2026-10-22 21:47:22', NULL, NULL, 'JG1997', NULL, NULL, 1);

-- Volcando estructura para tabla cati.comision_profesor
CREATE TABLE IF NOT EXISTS `comision_profesor` (
  `IdComision` int NOT NULL,
  `IdUsuario` int NOT NULL,
  PRIMARY KEY (`IdComision`,`IdUsuario`),
  KEY `FK_cp_usuario` (`IdUsuario`),
  CONSTRAINT `FK_cp_comision` FOREIGN KEY (`IdComision`) REFERENCES `comision` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_cp_usuario` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.comision_profesor: ~0 rows (aproximadamente)
INSERT INTO `comision_profesor` (`IdComision`, `IdUsuario`) VALUES
	(2, 4),
	(3, 4),
	(1, 7),
	(2, 7),
	(3, 7);

-- Volcando estructura para tabla cati.constancia
CREATE TABLE IF NOT EXISTS `constancia` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `IdComision` int NOT NULL,
  `IdPlantilla` int NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.constancia: ~0 rows (aproximadamente)
INSERT INTO `constancia` (`Id`, `IdComision`, `IdPlantilla`, `FechaElaboracion`, `DirectorNombre`, `DirectorCargo`, `DirectorFirmaUrl`, `DirectorSelloUrl`, `FirmaElaboracion`, `Activo`, `CreatedAt`, `UpdatedAt`, `DeletedAt`, `CreatedBy`, `UpdatedBy`, `DeletedBy`, `Versionn`) VALUES
	(1, 1, 2, '2026-10-22 21:34:17', 'Dr. Jose Alonzo Perez Cruz', 'Director', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTsBB58qlamAk5eHoP7AZEUZuu3JGvVloILo1JNTnNUYQ&s=10', 'https://i.pinimg.com/736x/3b/e0/91/3be0912b73bd1a67cf18cc841137b315.jpg', 'C.c.p. Archivo FGPSU/s', 1, '2026-10-22 21:34:17', NULL, NULL, 'SU2003', NULL, NULL, 1);

-- Volcando estructura para tabla cati.estado_comision
CREATE TABLE IF NOT EXISTS `estado_comision` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(50) NOT NULL,
  `Activo` tinyint DEFAULT '1',
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(50) DEFAULT 'admin',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.estado_comision: ~3 rows (aproximadamente)
INSERT INTO `estado_comision` (`Id`, `Nombre`, `Activo`, `CreatedAt`, `CreatedBy`) VALUES
	(1, 'Iniciado', 1, '2026-10-22 19:26:26', 'admin'),
	(2, 'En Proceso', 1, '2026-10-22 19:26:26', 'admin'),
	(3, 'Finalizado', 1, '2026-10-22 19:26:26', 'admin');

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
	(5, 'MANAT', 'MANAT', 'Nvel de acceso total del modulo de Administracion', 4, 1, '2026-10-04 15:40:00', NULL, NULL, 'SUsuario', NULL, NULL, 1);

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
  `Contexto` text,
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

-- Volcando datos para la tabla cati.plantilla_documento: ~0 rows (aproximadamente)
INSERT INTO `plantilla_documento` (`Id`, `TipoDocumento`, `NombreUniversidad`, `Facultad`, `Lugar`, `LogoIzquierdo`, `LogoDerecho`, `Introduccion`, `Contexto`, `Cierre`, `Despedida`, `Activo`, `CreatedAt`, `UpdatedAt`, `DeletedAt`, `CreatedBy`, `UpdatedBy`, `DeletedBy`, `Versionn`) VALUES
	(1, 'Comision', 'UNIVERSIDAD AUTÓNOMA DEL CARMEN', 'FACULTAD DE CIENCIAS DE LA INFORMACIÓN', 'Ciudad del Carmen, Campeche', 'https://www.unacar.mx/unacar/imagenes/escudo_unacar.png', 'https://www.unacar.mx/unacar/Documentos/logotipo22-26/EDUCANDO-VF-SLOGAN-(IDENTIDAD)-NEGRO.png', 'Por medio de la presente se le informa que ha sido comisionado para ', NULL, ', con el propósito de fortalecer las competencias de los estudiantes próximos a egresar y contribuir a la mejora de los indicadores académicos de nuestra Facultad. Esta actividad se llevará a cabo {fechas}.', 'Agradecemos de antemano su colaboración y compromiso. Sin otro particular, reciba un cordial saludo.', 1, '2026-10-22 19:31:48', '2026-10-22 21:03:19', NULL, 'admin', NULL, NULL, 1),
	(2, 'Constancia', 'UNIVERSIDAD AUTÓNOMA DEL CARMEN', 'FACULTAD DE CIENCIAS DE LA INFORMACIÓN', 'Ciudad del Carmen, Campeche', 'https://www.unacar.mx/unacar/imagenes/escudo_unacar.png', 'https://www.unacar.mx/unacar/Documentos/logotipo22-26/EDUCANDO-VF-SLOGAN-(IDENTIDAD)-NEGRO.png', 'Por medio de la presente, se hace constar que atendió satisfactoriamente la comisión asignada por la Facultad, la cual consistió en ', NULL, '. Esta actividad se enmarca en el plan de desarrollo de la Facultad.', 'Sin otro particular, me despido enviándoles un cordial saludo.', 1, '2026-10-22 19:33:17', '2026-10-22 21:03:20', NULL, 'admin', NULL, NULL, 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.tipo_comision: ~3 rows (aproximadamente)
INSERT INTO `tipo_comision` (`Id`, `Nombre`, `Descripcion`, `Activo`, `CreatedAt`, `UpdatedAt`, `DeletedAt`, `CreatedBy`, `UpdatedBy`, `DeletedBy`, `Versionn`) VALUES
	(1, 'Análisis EGEL', 'Análisis de los resultados del Examen General de Egreso', 1, '2026-10-22 19:26:26', NULL, NULL, 'admin', NULL, NULL, 1),
	(2, 'Taller EGEL', 'Impartir el Taller de Preparación para el EGEL', 1, '2026-10-22 19:26:26', NULL, NULL, 'admin', NULL, NULL, 1),
	(3, 'Academia', 'Reuniones y trabajos de academia', 1, '2026-10-22 19:26:26', NULL, NULL, 'admin', NULL, NULL, 1);

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
  `Rol` varchar(50) DEFAULT NULL,
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
  PRIMARY KEY (`Id`),
  KEY `FK_usuario_facultad` (`IdFacultad`),
  CONSTRAINT `FK_usuario_facultad` FOREIGN KEY (`IdFacultad`) REFERENCES `facultad` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci CHECKSUM=1;

-- Volcando datos para la tabla cati.usuario: ~6 rows (aproximadamente)
INSERT INTO `usuario` (`Id`, `Nombre`, `Apellido`, `Matricula`, `Correo`, `Iniciales`, `Contrasena`, `Rol`, `GradoAcademico`, `Genero`, `IdFacultad`, `Activo`, `CreatedAt`, `UpdatedAt`, `DeletedAt`, `CreatedBy`, `UpdatedBy`, `DeletedBy`, `Versionn`, `FirmaUrl`, `SelloUrl`) VALUES
	(1, 'Chuina Estrellita Guadalupe', 'Hernandez Rosado', 'JG1997', 'juani@gmail.com', 'ceghr', '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 'Secretaria', 'Mtra', 1, 1, 1, '2026-10-03 23:46:57', '2026-10-03 23:46:57', NULL, 'admin', 'admin', NULL, 1, NULL, NULL),
	(2, 'Nelly Isabel', 'Angel Hernandez', 'RG1978', 'Rosy@gmail.com', 'niah', '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 'Secretaria', 'Mtra', 1, 1, 1, '2026-10-03 23:46:57', '2026-10-04 01:11:12', NULL, 'admin', 'admin', NULL, 1, NULL, NULL),
	(3, 'Saide Dariola', 'Duran Martin', 'RG1995', 'Rosarito@gmail.com', 'sddm', '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 'SecretariaAcademica', 'Dra', 1, 1, 1, '2026-10-03 23:46:57', '2026-10-03 23:46:57', NULL, 'admin', 'admin', NULL, 1, NULL, NULL),
	(4, 'Jose Angel', 'Perez Rejon', 'JR1970', 'jose@gmail.com', 'japr', '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 'Profesor', 'Dr', 0, 1, 1, '2026-10-04 01:20:05', '2026-10-04 01:20:05', NULL, 'admin', 'admin', NULL, 1, NULL, NULL),
	(5, 'SUsuario', NULL, 'SU2003', 'cg7030295@gmail.com', NULL, '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 'SuperUsuario', 'Goat', 0, 3, 1, '2026-10-04 15:28:54', NULL, NULL, 'SUsuario', NULL, NULL, 1, NULL, NULL),
	(6, 'Administrador', '1', 'A2003', 'cg7030296@gmail.com', NULL, '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 'Administrador', 'Admin', 0, 1, 1, '2026-10-04 15:46:28', NULL, NULL, 'SUsuario', NULL, NULL, 1, NULL, NULL),
	(7, 'Guerra', 'Guerrero', 'GG1234', 'gg@gmail.com', NULL, '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 'Profesor', 'Dr', 0, 1, 1, '2026-10-22 18:21:58', '2026-10-22 18:21:59', NULL, 'admin', NULL, NULL, 1, NULL, NULL),
	(8, 'Jose Alonzo', 'Perez Cruz', 'JAPC2025', 'jperez@unacar.mx', 'japc', '$2y$10$YOLl8FTfQVJEGwF8YMAQuuAOSfMi2ysQtn6Y.V6NAjayPGhKkKDza', 'Director', 'Dr', 0, 1, 1, '2026-10-22 19:26:26', NULL, NULL, 'admin', NULL, NULL, 1, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTsBB58qlamAk5eHoP7AZEUZuu3JGvVloILo1JNTnNUYQ&s=10', 'https://i.pinimg.com/736x/3b/e0/91/3be0912b73bd1a67cf18cc841137b315.jpg');

-- Volcando estructura para tabla cati.usuario_tipo_comision
CREATE TABLE IF NOT EXISTS `usuario_tipo_comision` (
  `IdUsuario` int NOT NULL,
  `IdTipoComision` int NOT NULL,
  PRIMARY KEY (`IdUsuario`,`IdTipoComision`),
  KEY `FK_utc_tipo_comision` (`IdTipoComision`),
  CONSTRAINT `FK_utc_tipo_comision` FOREIGN KEY (`IdTipoComision`) REFERENCES `tipo_comision` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_utc_usuario` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla cati.usuario_tipo_comision: ~3 rows (aproximadamente)
INSERT INTO `usuario_tipo_comision` (`IdUsuario`, `IdTipoComision`) VALUES
	(1, 1),
	(2, 2),
	(1, 3);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
