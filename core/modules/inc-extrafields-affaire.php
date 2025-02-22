<?php

include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

//========================================
// Ajout des champs extras pour les tâches
//========================================
$extrafields = new ExtraFields($this->db);
$params = array(
    'options' => array('Saisie' => 'Saisie', 'Ouverte' => 'Ouverte','En cours' => 'En cours','Terminée' => 'Terminée','Cloturée' => 'Cloturée')
);

$result=$extrafields->addExtraField(
    'etat', 
    'Etat', 
    'select', 
    100,  
    0, 
    'projet_task',
    0, 
    0, 
    '', 
    $params,
    1, 
    '', 
    1, 
    0, 
    '', 
    '', 
    'kjraffaire@kjraffaire', 
    'isModEnabled("kjraffaire")'
);


//===========================================================
// Ajout des champs extras pour les affaires dans les projets
//===========================================================

$extrafields = new ExtraFields($this->db);
$result=$extrafields->addExtraField(
    'affaire', 
    "Affaire", 
    'boolean', 
    100,  
    0, 
    'projet',
    0, 
    0, 
    '', 
    array('options'=>array(1=>1)), 
    1, 
    '', 
    0, 
    0, 
    '', 
    '', 
    'kjraffaire@kjraffaire', 
    'isModEnabled("kjraffaire")'
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
    '($object->array_options[\'options_affaire\']==1)?1:0',
    '',
    '',
    '1',
    'kjraffaire@kjraffaire',
    'isModEnabled("kjraffaire")',
    0,
    1
);

// contentieux O/N
$result=$extrafields->addExtraField(
    'contentieux', 
    "Contentieux", 
    'boolean', 
    101,  
    0, 
    'projet',
    0, 
    0, 
    '', 
    array('options'=>array(1=>1)), 
    1, 
    '', 
    '($object->array_options[\'options_affaire\']==1)?1:0',
    0, 
    '', 
    '', 
    'kjraffaire@kjraffaire', 
    'isModEnabled("kjraffaire")'
);
