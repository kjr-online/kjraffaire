CREATE TABLE IF NOT EXISTS llx_kjraffaire_dico_fonction_contact( 
    rowid INTEGER AUTO_INCREMENT PRIMARY KEY,        -- ID
    code VARCHAR(50) NOT NULL UNIQUE,                -- Code fonction
    active INTEGER
) ENGINE=innodb;