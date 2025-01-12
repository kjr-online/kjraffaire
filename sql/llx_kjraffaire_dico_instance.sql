CREATE TABLE IF NOT EXISTS llx_kjraffaire_dico_instance( 
    rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
    typeinstance varchar(10),
    code_insee varchar(10),
    code_orig varchar(1),
    num varchar(10),
    nom_etablissement varchar(255),
    numero_et_libelle_voie varchar(255),
    lieu_dit varchar(255),
    code_postal varchar(25),
    ligne_d_acheminement varchar(50),
    pays_ou_denomination_tom_com varchar(255),
    coordonnees_x double(24,8),
    coordonnees_y double(24,8),
    nu_tel varchar(20),
    adresse_mail varchar(128),
    active integer 
) ENGINE=innodb;