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


// juridiction fk_kjraffaire_dico_juridiction (lien vers table dictionnaire)
if (!isset($existingFields['fk_kjraffaire_dico_juridiction'])) {
    $res = $extrafields->addExtraField( 'fk_kjraffaire_dico_juridiction','Juridiction','sellist',102,'','projet', 0, 0, '', array('options' => array('kjraffaire_dico_juridiction:nom_etablissement:rowid::(active:=:1)' => null)), 1, '','($object->array_options[\'options_instance\']==1)?1:0' );
    if ($res < 0) { dol_syslog("Erreur lors de l'ajout du champ extra fk_kjraffaire_dico_juridiction : " . $extrafields->error, LOG_ERR); return -1; }
}

// action : fk_kjraffaire_dico_action_juridique (lien vers table du dictionnaire)
if (!isset($existingFields['fk_kjraffaire_dico_action_juridique'])) {
    $res = $extrafields->addExtraField( 'fk_kjraffaire_dico_action_juridique','Action juridique','sellist',103,'','projet', 0, 0, '', array('options' => array('kjraffaire_dico_action_juridique:label:rowid::(active:=:1)' => null)), 1, '','($object->array_options[\'options_instance\']==1)?1:0' );
    if ($res < 0) { dol_syslog("Erreur lors de l'ajout du champ extra fk_kjraffaire_dico_action_juridique : " . $extrafields->error, LOG_ERR); return -1; }
}

// chambre : varchar(30)
if (!isset($existingFields['chambre'])) {
    $params = array(
        'options' => array('label'=>'chambre', 'type'=>'varchar(30)')
    );
    $res = $extrafields->addExtraField(
        'chambre','Chambre','varchar',104,30,'projet',0,0,'', $params,1,'','($object->array_options[\'options_instance\']==1)?1:0'
    );
    if ($res < 0) {
        // Gestion des erreurs
        dol_syslog("Erreur lors de l'ajout du champ extra sous-titre : " . $extrafields->error, LOG_ERR);
        return -1;
    }
}


// no role : varchar(30)
if (!isset($existingFields['no_role'])) {
    $params = array(
        'options' => array('label'=>'no_role', 'type'=>'varchar(30)')
    );
    $res = $extrafields->addExtraField(
        'no_role','No role','varchar',105,30,'projet',0,0,'', $params,1,'','($object->array_options[\'options_instance\']==1)?1:0'
    );
    if ($res < 0) {
        // Gestion des erreurs
        dol_syslog("Erreur lors de l'ajout du champ extra no_role : " . $extrafields->error, LOG_ERR);
        return -1;
    }
}


// magistrat (société) : fk_magistrat (lien vers societe)
if (!isset($existingFields['fk_soc_magistrat'])) {
    $res = $extrafields->addExtraField( 'fk_soc_magistrat','Magistrat (société)','sellist',106,'','projet', 0, 0, '', array('options' => array('societe:nom:rowid::(status:=:1)' => null)), 1, '','($object->array_options[\'options_instance\']==1)?1:0' );
    if ($res < 0) { dol_syslog("Erreur lors de l'ajout du champ extra fk_soc_magistrat : " . $extrafields->error, LOG_ERR); return -1; }
}

// magistrat (contact) : fk_magistrat (lien vers contact)
if (!isset($existingFields['fk_socpeople_magistrat'])) {
    $res = $extrafields->addExtraField( 'fk_socpeople_magistrat','Magistrat (contact)','sellist',107,'','projet', 0, 0, '', array('options' => array('socpeople:lastname:rowid::(statut:=:1)' => null)), 1, '','($object->array_options[\'options_instance\']==1)?1:0' );
    if ($res < 0) { dol_syslog("Erreur lors de l'ajout du champ extra fk_socpeople_magistrat : " . $extrafields->error, LOG_ERR); return -1; }
}

// section : varchar(30)
if (!isset($existingFields['section'])) {
    $params = array(
        'options' => array('label'=>'section', 'type'=>'varchar(30)')
    );
    $res = $extrafields->addExtraField(
        'section','Section','varchar',108,30,'projet',0,0,'', $params,1,'','($object->array_options[\'options_instance\']==1)?1:0'
    );
    if ($res < 0) {
        // Gestion des erreurs
        dol_syslog("Erreur lors de l'ajout du champ extra section : " . $extrafields->error, LOG_ERR);
        return -1;
    }
}


// date décision : date
if (!isset($existingFields['date_decision'])) {
    $res = $extrafields->addExtraField('date_decision','Date décision','date',109,'','projet',0,0,'',
        array('options' => array()),
        1,'','($object->array_options[\'options_instance\']==1)?1:0');
    if ($res < 0) {dol_syslog("Erreur lors de l'ajout du champ extra Date décision : " . $extrafields->error, LOG_ERR);return -1;}
}

// date signification : date
if (!isset($existingFields['date_signification'])) {
    $res = $extrafields->addExtraField('date_signification','Date signification','date',110,'','projet',0,0,'',
        array('options' => array()),
        1,'','($object->array_options[\'options_instance\']==1)?1:0');
    if ($res < 0) {dol_syslog("Erreur lors de l'ajout du champ extra Date signification : " . $extrafields->error, LOG_ERR);return -1;}
}

// date recours : date
if (!isset($existingFields['date_recours'])) {
    $res = $extrafields->addExtraField('date_recours','Date recours','date',111,'','projet',0,0,'',
        array('options' => array()),
        1,'','($object->array_options[\'options_instance\']==1)?1:0');
    if ($res < 0) {dol_syslog("Erreur lors de l'ajout du champ extra Date recours : " . $extrafields->error, LOG_ERR);return -1;}
}

// avocat postulant (société) : fk_soc_avocat_postulant (lien vers societe)
if (!isset($existingFields['fk_soc_avocat_postulant'])) {
    $res = $extrafields->addExtraField( 'fk_soc_avocat_postulant','Avocat postulant (société)','sellist',112,'','projet', 0, 0, '', array('options' => array('societe:nom:rowid::(status:=:1)' => null)), 1, '','($object->array_options[\'options_instance\']==1)?1:0' );
    if ($res < 0) { dol_syslog("Erreur lors de l'ajout du champ extra fk_soc_avocat_postulant : " . $extrafields->error, LOG_ERR); return -1; }
}


// avocat postulant (société) : fk_socpeople_avocat_postulant (lien vers societe)
if (!isset($existingFields['fk_socpeople_avocat_postulant'])) {
    $res = $extrafields->addExtraField( 'fk_socpeople_avocat_postulant','Avocat postulant (contact)','sellist',113,'','projet', 0, 0, '', array('options' => array('socpeople:lastname:rowid::(statut:=:1)' => null)), 1, '','($object->array_options[\'options_instance\']==1)?1:0' );
    if ($res < 0) { dol_syslog("Erreur lors de l'ajout du champ extra fk_socpeople_avocat_postulant : " . $extrafields->error, LOG_ERR); return -1; }
}

