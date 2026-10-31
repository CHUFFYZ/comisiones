-- Ejecutar UNA sola vez sobre `cati`
USE `cati`;

-- 1) Cierre de comisiones (solo el super usuario puede revertirlo)
ALTER TABLE `comision`
  ADD COLUMN `Cerrada`    tinyint  NOT NULL DEFAULT 0,
  ADD COLUMN `CerradaAt`  datetime NULL,
  ADD COLUMN `CerradaPor` int      NULL,
  ADD CONSTRAINT `FK_comision_cerrada_por` FOREIGN KEY (`CerradaPor`) REFERENCES `usuario` (`Id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- 2) Áreas (tipos de comisión) que otorga cada nivel de acceso
CREATE TABLE IF NOT EXISTS `nivel_acceso_tipo_comision` (
  `IdNivelAcceso` int NOT NULL,
  `IdTipoComision` int NOT NULL,
  PRIMARY KEY (`IdNivelAcceso`,`IdTipoComision`),
  KEY `FK_natc_tipo` (`IdTipoComision`),
  CONSTRAINT `FK_natc_nivel` FOREIGN KEY (`IdNivelAcceso`)  REFERENCES `nivel_acceso` (`Id`)  ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_natc_tipo`  FOREIGN KEY (`IdTipoComision`) REFERENCES `tipo_comision` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
