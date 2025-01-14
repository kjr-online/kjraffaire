CREATE TABLE IF NOT EXISTS llx_kjraffaire_visibility_group(
    rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
    affaire_id INTEGER NOT NULL UNIQUE,
    group_id varchar(255)
) ENGINE=innodb;