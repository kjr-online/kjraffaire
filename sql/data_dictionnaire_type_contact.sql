-- Types de contacts : Interne --
INSERT INTO llx_c_type_contact (code, libelle, element, source, active, position)
SELECT 'AFFAIRECONTRIBUTORINTERNE', 'Contributeur', 'kjraffaire', 'internal', 1, 1
WHERE NOT EXISTS (
    SELECT 1 FROM llx_c_type_contact WHERE code = 'AFFAIRECONTRIBUTORINTERNE' AND element = 'kjraffaire'
);

INSERT INTO llx_c_type_contact (code, libelle, element, source, active, position)
SELECT 'AFFAIRELEADERINTERNE', 'Chef de projet', 'kjraffaire', 'internal', 1, 2
WHERE NOT EXISTS (
    SELECT 1 FROM llx_c_type_contact WHERE code = 'AFFAIRELEADERINTERNE' AND element = 'kjraffaire'
);

-- Types de contacts : Externe --
INSERT INTO llx_c_type_contact (code, libelle, element, source, active, position)
SELECT 'AFFAIRECONTRIBUTOREXTERNE', 'Contributeur', 'kjraffaire', 'external', 1, 1
WHERE NOT EXISTS (
    SELECT 1 FROM llx_c_type_contact WHERE code = 'AFFAIRECONTRIBUTOREXTERNE' AND element = 'kjraffaire'
);

INSERT INTO llx_c_type_contact (code, libelle, element, source, active, position)
SELECT 'AFFAIRELEADEREXTERNE', 'Chef de projet', 'kjraffaire', 'external', 1, 2
WHERE NOT EXISTS (
    SELECT 1 FROM llx_c_type_contact WHERE code = 'AFFAIRELEADEREXTERNE' AND element = 'kjraffaire'
);

INSERT INTO llx_c_type_contact (code, libelle, element, source, active, position)
SELECT 'ADVERSAIRES', 'Adversaires', 'kjraffaire', 'external', 1, 3
WHERE NOT EXISTS (
    SELECT 1 FROM llx_c_type_contact WHERE code = 'ADVERSAIRES' AND element = 'kjraffaire'
);

INSERT INTO llx_c_type_contact (code, libelle, element, source, active, position)
SELECT 'CLIENTS', 'Clients', 'kjraffaire', 'external', 1, 4
WHERE NOT EXISTS (
    SELECT 1 FROM llx_c_type_contact WHERE code = 'CLIENTS' AND element = 'kjraffaire'
);

INSERT INTO llx_c_type_contact (code, libelle, element, source, active, position)
SELECT 'AUTRES', 'Autres', 'kjraffaire', 'external', 1, 5
WHERE NOT EXISTS (
    SELECT 1 FROM llx_c_type_contact WHERE code = 'AUTRES' AND element = 'kjraffaire'
);


-- Types de sous-contacts --
INSERT INTO llx_kjraffaire_dico_soustype_contact (code, libelle, fk_type_contact, active, position)
SELECT 'EXPERTS', 'Experts', rowid, 1, 1
FROM llx_c_type_contact
WHERE code = 'AUTRES' AND element = 'kjraffaire' AND source = 'external'
LIMIT 1;

INSERT INTO llx_kjraffaire_dico_soustype_contact (code, libelle, fk_type_contact, active, position)
SELECT 'HUISSIERS', 'Huissiers', rowid, 1, 2
FROM llx_c_type_contact
WHERE code = 'AUTRES' AND element = 'kjraffaire' AND source = 'external'
LIMIT 1;
