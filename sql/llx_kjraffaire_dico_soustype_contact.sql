CREATE TABLE IF NOT EXISTS llx_kjraffaire_dico_soustype_contact( 
    rowid INT AUTO_INCREMENT PRIMARY KEY,        -- ID
    code VARCHAR(50) NOT NULL UNIQUE,            -- Code sous-type
    libelle VARCHAR(255) NOT NULL,               -- Libellé sous-type
    fk_type_contact INT NOT NULL,                -- ID type parent (relation avec c_type_contact)
    active TINYINT(1) DEFAULT 1,                 -- Statut actif (1 = actif)
    position INT DEFAULT 0,                      -- Position
    FOREIGN KEY (fk_type_contact) REFERENCES llx_c_type_contact(rowid) ON DELETE CASCADE
) ENGINE=innodb;