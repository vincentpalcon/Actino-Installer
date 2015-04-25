SET FOREIGN_KEY_CHECKS = 0;

/* Tables */
DROP TABLE IF EXISTS `{PREFIX}example_installation`;
CREATE TABLE `{PREFIX}example_installation` (
  `id_example`  int AUTO_INCREMENT NOT NULL,
  `name`        varchar(100) NOT NULL,
  PRIMARY KEY (`id_example`)
) ENGINE = InnoDB;

SET FOREIGN_KEY_CHECKS = 1;