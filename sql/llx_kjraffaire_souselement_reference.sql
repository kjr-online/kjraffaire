CREATE TABLE llx_kjraffaire_souselement_reference (
    rowid INT AUTO_INCREMENT PRIMARY KEY,
    id_element_contact INT NOT NULL,
    reference VARCHAR(255),
    FOREIGN KEY (id_element_contact) REFERENCES llx_element_contact (rowid) ON DELETE CASCADE
) ENGINE=InnoDB;