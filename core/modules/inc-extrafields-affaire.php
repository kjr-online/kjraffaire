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

