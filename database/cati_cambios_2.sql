-- Ejecutar UNA sola vez sobre `cati` (después de importar comisiones.sql)
USE `cati`;

-- 1) Tabla de roles
CREATE TABLE IF NOT EXISTS `rol` (
  `Id`        int NOT NULL AUTO_INCREMENT,
  `Clave`     varchar(50) NOT NULL,
  `Nombre`    varchar(80) NOT NULL,
  `Orden`     int NOT NULL DEFAULT 99,
  `Activo`    tinyint DEFAULT 1,
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(50) DEFAULT 'admin',
  PRIMARY KEY (`Id`),
  UNIQUE KEY `UQ_rol_clave` (`Clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `rol` (`Id`,`Clave`,`Nombre`,`Orden`) VALUES
  (1,'SuperUsuario','Super usuario',1),
  (2,'Administrador','Administrador',2),
  (3,'Director','Director',3),
  (4,'SecretariaAcademica','Secretaría Académica',4),
  (5,'Secretaria','Secretaria',5),
  (6,'Profesor','Profesor',6);

-- 2) Usuario: vínculo a rol, foto y última conexión
ALTER TABLE `usuario`
  ADD COLUMN `IdRol` int NULL AFTER `Rol`,
  ADD COLUMN `FotoUrl` varchar(500) NULL,
  ADD COLUMN `UltimaConexion` datetime NULL;

UPDATE `usuario` u JOIN `rol` r ON r.`Clave` = u.`Rol` SET u.`IdRol` = r.`Id`;

ALTER TABLE `usuario`
  MODIFY `IdRol` int NOT NULL,
  ADD CONSTRAINT `FK_usuario_rol` FOREIGN KEY (`IdRol`) REFERENCES `rol` (`Id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  DROP COLUMN `Rol`;

-- 3) Cierre propio en comisión y constancia
ALTER TABLE `comision`   ADD COLUMN `Cierre` text NULL AFTER `Contexto`;
ALTER TABLE `constancia` ADD COLUMN `Cierre` text NULL AFTER `Contexto`;
UPDATE `comision`   c JOIN `plantilla_documento` p ON p.`Id` = c.`IdPlantilla` SET c.`Cierre` = p.`Cierre`;
UPDATE `constancia` k JOIN `plantilla_documento` p ON p.`Id` = k.`IdPlantilla` SET k.`Cierre` = p.`Cierre`;

-- 4) Permisos temporales sin permiso base
ALTER TABLE `asignacion_permiso`
  MODIFY `IdTipoPermiso` int NULL,
  MODIFY `IdNivelAcceso` int NULL;
