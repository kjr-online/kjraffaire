CREATE TABLE IF NOT EXISTS `llx_kjraffaire_instance` (
    `rowid` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
    `fk_affaire` INT,
    `fk_juridiction` INT,
    `fk_action_juridique` INT,
    `chambre` VARCHAR(255) ,
    `no_role` VARCHAR(255) ,
    `fk_socpeople_magistrat` INT ,
    `section` VARCHAR(255) ,
    `date_decision` DATE,
    `date_signification` DATE,
    `date_recours` DATE,
    `fk_soc_avocat_postulant` INT,
    `fk_socpeople_avocat_postulant` INT,

    PRIMARY KEY (`rowid`)) ENGINE = InnoDB;