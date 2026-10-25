-- =====================================================================
-- CATI · cambios de base de datos (ejecutar UNA sola vez sobre `cati`)
-- =====================================================================
USE `cati`;

-- 1) Usuario: firma y sello (los usa el Director en oficios y constancias)
ALTER TABLE `usuario`
  ADD COLUMN `FirmaUrl` varchar(500) DEFAULT NULL AFTER `GradoAcademico`,
  ADD COLUMN `SelloUrl` varchar(500) DEFAULT NULL AFTER `FirmaUrl`;

-- 2) Nuevos estados (1-3 son los principales; 4 y 5 son excepciones con descripción)
INSERT INTO `estado_comision` (`Id`, `Nombre`, `Activo`, `CreatedBy`) VALUES
  (4, 'Rechazado', 1, 'admin'),
  (5, 'Refutado',  1, 'admin');

-- 3) Comisión: facultad, contexto (va en el documento, no en la plantilla), cargo y reporte del profesor
ALTER TABLE `comision`
  ADD COLUMN `IdFacultad`       int          DEFAULT NULL AFTER `IdEstado`,
  ADD COLUMN `Contexto`         text         NULL         AFTER `Asunto`,
  ADD COLUMN `DirectorCargo`    varchar(50)  DEFAULT NULL AFTER `DirectorNombre`,
  ADD COLUMN `ReporteNombre`    varchar(255) DEFAULT NULL,
  ADD COLUMN `ReporteRuta`      varchar(500) DEFAULT NULL,
  ADD COLUMN `ReporteIdUsuario` int          DEFAULT NULL,
  ADD COLUMN `ReporteAt`        datetime     DEFAULT NULL,
  ADD CONSTRAINT `FK_comision_facultad`         FOREIGN KEY (`IdFacultad`)       REFERENCES `facultad` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_comision_reporte_usuario`  FOREIGN KEY (`ReporteIdUsuario`) REFERENCES `usuario`  (`Id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- 4) Constancia: contexto propio y cargo del director
ALTER TABLE `constancia`
  ADD COLUMN `Contexto`      text        NULL         AFTER `IdPlantilla`,
  ADD COLUMN `DirectorCargo` varchar(50) DEFAULT NULL AFTER `DirectorNombre`;

-- 5) Plantilla: el contexto sale de la plantilla; las fechas se agregan al final del cierre desde el código
ALTER TABLE `plantilla_documento` DROP COLUMN `Contexto`;
UPDATE `plantilla_documento`
   SET `Cierre` = ', con el propósito de fortalecer las competencias de los estudiantes próximos a egresar y contribuir a la mejora de los indicadores académicos de nuestra Facultad.'
 WHERE `Id` = 1;

-- 6) Bitácora de estados (guarda la descripción de rechazos/refutaciones)
CREATE TABLE IF NOT EXISTS `comision_log` (
  `Id`          int NOT NULL AUTO_INCREMENT,
  `IdComision`  int NOT NULL,
  `IdEstado`    int NOT NULL,
  `Accion`      varchar(60) NOT NULL,
  `Descripcion` varchar(1000) DEFAULT NULL,
  `IdUsuario`   int DEFAULT NULL,
  `CreatedAt`   datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  KEY `IX_log_comision` (`IdComision`),
  CONSTRAINT `FK_log_comision` FOREIGN KEY (`IdComision`) REFERENCES `comision` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_log_estado`   FOREIGN KEY (`IdEstado`)   REFERENCES `estado_comision` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `FK_log_usuario`  FOREIGN KEY (`IdUsuario`)  REFERENCES `usuario` (`Id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 7) Notificaciones generales (Clave evita duplicados, p. ej. avisos de vencimiento)
CREATE TABLE IF NOT EXISTS `notificacion` (
  `Id`        int NOT NULL AUTO_INCREMENT,
  `IdUsuario` int NOT NULL,
  `Tipo`      varchar(30) NOT NULL,
  `Titulo`    varchar(150) NOT NULL,
  `Mensaje`   varchar(500) DEFAULT NULL,
  `Url`       varchar(500) DEFAULT NULL,
  `Clave`     varchar(120) DEFAULT NULL,
  `LeidaAt`   datetime DEFAULT NULL,
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `UQ_notif_clave` (`IdUsuario`, `Clave`),
  KEY `IX_notif_usuario` (`IdUsuario`, `LeidaAt`),
  CONSTRAINT `FK_notif_usuario` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 8) Chat entre usuarios (IdUsuario1 < IdUsuario2; IniciadoPor decide si el SU aparece como "Desconocido")
CREATE TABLE IF NOT EXISTS `chat_conversacion` (
  `Id`              int NOT NULL AUTO_INCREMENT,
  `IdUsuario1`      int NOT NULL,
  `IdUsuario2`      int NOT NULL,
  `IniciadoPor`     int NOT NULL,
  `UltimoMensajeAt` datetime DEFAULT NULL,
  `CreatedAt`       datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `UQ_chat_par` (`IdUsuario1`, `IdUsuario2`),
  KEY `IX_chat_u2` (`IdUsuario2`),
  CONSTRAINT `FK_chat_u1` FOREIGN KEY (`IdUsuario1`) REFERENCES `usuario` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_chat_u2` FOREIGN KEY (`IdUsuario2`) REFERENCES `usuario` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `chat_mensaje` (
  `Id`             int NOT NULL AUTO_INCREMENT,
  `IdConversacion` int NOT NULL,
  `IdRemitente`    int NOT NULL,
  `Mensaje`        text NOT NULL,
  `LeidoAt`        datetime DEFAULT NULL,
  `CreatedAt`      datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  KEY `IX_msg_conv` (`IdConversacion`, `Id`),
  CONSTRAINT `FK_msg_conv`      FOREIGN KEY (`IdConversacion`) REFERENCES `chat_conversacion` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_msg_remitente` FOREIGN KEY (`IdRemitente`)    REFERENCES `usuario` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
