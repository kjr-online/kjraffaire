CREATE TABLE llx_kjraffaire_element_contact (
    rowid INT AUTO_INCREMENT PRIMARY KEY,
    element_id INT NOT NULL,
    fk_c_type_contact INT NOT NULL,
    fk_societe INT,
    fk_socpeople INT,
    fonction VARCHAR(50),
    niveau INT,
    reference varchar(50),
    fk_idpere INT,
    FOREIGN KEY (fk_c_type_contact) REFERENCES llx_c_type_contact(rowid),
    FOREIGN KEY (fk_societe) REFERENCES llx_societe(rowid),
    FOREIGN KEY (fk_socpeople) REFERENCES llx_socpeople(rowid),
    FOREIGN KEY (fk_idpere) REFERENCES llx_kjraffaire_element_contact(rowid)
) ENGINE=InnoDB;