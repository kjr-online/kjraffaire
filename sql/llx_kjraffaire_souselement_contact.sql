CREATE TABLE llx_kjraffaire_souselement_contact (
    rowid INT AUTO_INCREMENT PRIMARY KEY,
    id_element_contact INT NOT NULL,
    id_soustype_contact INT NOT NULL,
    FOREIGN KEY (id_element_contact) REFERENCES llx_element_contact (rowid) ON DELETE CASCADE,
    FOREIGN KEY (id_soustype_contact) REFERENCES llx_kjraffaire_dico_soustype_contact (rowid) ON DELETE CASCADE
) ENGINE=InnoDB;