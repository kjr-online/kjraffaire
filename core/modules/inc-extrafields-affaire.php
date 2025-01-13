<?php

// Ajout des champs extras pour les affaires dans les projets

include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
$extrafields = new ExtraFields($this->db);
$result0=$extrafields->addExtraField(
    'affaire', 
    "Affaire", 
    'boolean', 
    100,  
    0, 
    'projet',
    0, 
    0, 
    '', 
    array('options'=>array(1=>1)), 1, '', 0, 0, '', '', 'kjraffaire@kjraffaire', 'isModEnabled("kjraffaire")'
);

// Ajout extrafield pour saisie du type d'affaire :
$result = $extrafields->addExtraField(
    'type_affaire',
    "Type d'affaire",
    'sellist',
    101,
    '',
    'projet',
    0,
    0,
    '',
    array('options' => array('kjraffaire_dico_type_affaire:label:rowid::(active:=:1)' => null)),
    1,
    '',
    1,
    '',
    '',
    '1',
    'kjraffaire@kjraffaire',
    'isModEnabled("kjraffaire")',
    0,
    1
);