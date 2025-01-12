CREATE TABLE IF NOT EXISTS llx_kjraffaire_dico_type_affaire( 
    rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
    label varchar(255), 
    active integer 
) ENGINE=innodb;