<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2016 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2023      Charlene Benke       <charlene@patas_monkey.com>
 * Copyright (C) 2023      Christian Foellmann  <christian@foellmann.de>
 * Copyright (C) 2024      MDW                  <mdeweerd@users.noreply.github.com>
 * Copyright (C) 2024      Frédéric France      <frederic.france@free.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       htdocs/projet/card.php
 *	\ingroup    projet
 *	\brief      Project card
 */

// Load Dolibarr environment
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/project/modules_project.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/kjraffaire/lib/kjraffaire.lib.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';

// Load translation files required by the page
$langsLoad = array('projects', 'companies');
if (isModEnabled('eventorganization')) {
	$langsLoad[] = 'eventorganization';
}

$langs->loadLangs($langsLoad);

$id = GETPOSTINT('id');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$backtopagejsfields = GETPOST('backtopagejsfields', 'alpha');
$cancel = GETPOST('cancel', 'alpha');
$confirm = GETPOST('confirm', 'aZ09');

$dol_openinpopup = '';
if (!empty($backtopagejsfields)) {
	$tmpbacktopagejsfields = explode(':', $backtopagejsfields);
	$dol_openinpopup = preg_replace('/[^a-z0-9_]/i', '', $tmpbacktopagejsfields[0]);
}

$status = GETPOSTINT('status');
$opp_status = GETPOSTINT('opp_status');
$opp_percent = price2num(GETPOST('opp_percent', 'alphanohtml'));
$objcanvas = GETPOST("objcanvas", "alphanohtml");
$comefromclone = GETPOST("comefromclone", "alphanohtml");
$date_start = dol_mktime(0, 0, 0, GETPOSTINT('projectstartmonth'), GETPOSTINT('projectstartday'), GETPOSTINT('projectstartyear'));
$date_end = dol_mktime(0, 0, 0, GETPOSTINT('projectendmonth'), GETPOSTINT('projectendday'), GETPOSTINT('projectendyear'));
$date_start_event = dol_mktime(GETPOSTINT('date_start_eventhour'), GETPOSTINT('date_start_eventmin'), GETPOSTINT('date_start_eventsec'), GETPOSTINT('date_start_eventmonth'), GETPOSTINT('date_start_eventday'), GETPOSTINT('date_start_eventyear'), 'tzuserrel');
$date_end_event = dol_mktime(GETPOSTINT('date_end_eventhour'), GETPOSTINT('date_end_eventmin'), GETPOSTINT('date_end_eventsec'), GETPOSTINT('date_end_eventmonth'), GETPOSTINT('date_end_eventday'), GETPOSTINT('date_end_eventyear'), 'tzuserrel');
$location = GETPOST('location', 'alphanohtml');
$fk_project = GETPOSTINT('fk_project');


$mine = GETPOST('mode') == 'mine' ? 1 : 0;
//if (! $user->rights->projet->all->lire) $mine=1;	// Special for projects

$object = new Project($db);
$extrafields = new ExtraFields($db);

// Load object
if ($id > 0 || !empty($ref)) {
	$ret = $object->fetch($id, $ref); // If we create project, ref may be defined into POST but record does not yet exists into database
	if ($ret > 0) {
		$object->fetch_thirdparty();
		if (getDolGlobalString('PROJECT_ALLOW_COMMENT_ON_PROJECT') && method_exists($object, 'fetchComments') && empty($object->comments)) {
			$object->fetchComments();
		}
		$id = $object->id;
	}
}

// fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

// Security check
$socid = GETPOSTINT('socid');
restrictedArea($user, 'projet', $object->id, 'projet&project');

if ($id == '' && $ref == '' && ($action != "create" && $action != "add" && $action != "update" && !GETPOST("cancel"))) {
	accessforbidden();
}

$permissiontoadd = $user->hasRight('projet', 'creer');
$permissiontodelete = $user->hasRight('projet', 'supprimer');
$permissiondellink = $user->hasRight('projet', 'creer');	// Used by the include of actions_dellink.inc.php


/* Actions */

ob_start();

/*
 *	View
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);
$userstatic = new User($db);

$title = 'Affaire - '.$object->ref.(!empty($object->thirdparty->name) ? ' - '.$object->thirdparty->name : '').(!empty($object->title) ? ' - '.$object->title : '');
if (getDolGlobalString('MAIN_HTML_TITLE') && preg_match('/projectnameonly/', getDolGlobalString('MAIN_HTML_TITLE'))) {
	$title = $object->ref.(!empty($object->thirdparty->name) ? ' - '.$object->thirdparty->name : '').(!empty($object->title) ? ' - '.$object->title : '');
}

$help_url = "EN:Module_Projects|FR:Module_Projets|ES:M&oacute;dulo_Proyectos|DE:Modul_Projekte";

llxHeader("", $title, $help_url);

$titleboth = $langs->trans("LeadsOrProjects");
$titlenew = $langs->trans("NewLeadOrProject"); // Leads and opportunities by default
if (!getDolGlobalInt('PROJECT_USE_OPPORTUNITIES')) {
	$titleboth = $langs->trans("Projects");
	$titlenew = $langs->trans("NewProject");
}
if (getDolGlobalInt('PROJECT_USE_OPPORTUNITIES') == 2) { // 2 = leads only
	$titleboth = $langs->trans("Leads");
	$titlenew = $langs->trans("NewLead");
}

if ($object->id > 0) {
	/*
	 * Show
	 */

	$res = $object->fetch_optionals();

	// To verify role of users
	$userAccess = $object->restrictedProjectArea($user, 'read');
	$userWrite  = $object->restrictedProjectArea($user, 'write');
	$userDelete = $object->restrictedProjectArea($user, 'delete');
	//print "userAccess=".$userAccess." userWrite=".$userWrite." userDelete=".$userDelete;

	print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';
	print '<input type="hidden" name="comefromclone" value="'.$comefromclone.'">';

	$head = affaire_prepare_head($object);
    
	$object->array_options['options_instance'] = 1;

	print dol_get_fiche_head($head, 'intance', $langs->trans("Project"), -1, ($object->public ? 'fa-briefcase' : 'fa-briefcase'));

	// Instance card

	if (!empty($_SESSION['pageforbacktolist']) && !empty($_SESSION['pageforbacktolist']['project'])) {
		$tmpurl = $_SESSION['pageforbacktolist']['project'];
		$tmpurl = preg_replace('/__SOCID__/', (string) $object->socid, $tmpurl);
		$linkback = '<a href="'.$tmpurl.(preg_match('/\?/', $tmpurl) ? '&' : '?'). 'restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';
	} else {
		$linkback = '<a href="'.DOL_URL_ROOT.'/custom/kjraffaire/affaire/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';
	}

	$morehtmlref = '<div class="refidno">';
	// Title
	$morehtmlref .= dol_escape_htmltag($object->title);
	$morehtmlref .= '<br>';
	// Thirdparty
	if (!empty($object->thirdparty->id) && $object->thirdparty->id > 0) {
		$morehtmlref .= $object->thirdparty->getNomUrl(1, 'project');
	}
	// Parent
	if (getDolGlobalInt('PROJECT_ENABLE_SUB_PROJECT')) {
		if (!empty($object->fk_project) && $object->fk_project) {
			$parent = new Project($db);
			$parent->fetch($object->fk_project);
			$morehtmlref .= $langs->trans("Child of").' '.$parent->getNomUrl(1, 'project').' '.$parent->title;
		}
	}
	$morehtmlref .= '</div>';

	// Define a complementary filter for search of next/prev ref.
	if (!$user->hasRight('projet', 'all', 'lire')) {
		$objectsListId = $object->getProjectsAuthorizedForUser($user, 0, 0);
		$object->next_prev_filter = "rowid IN (".$db->sanitize(count($objectsListId) ? implode(',', array_keys($objectsListId)) : '0').")";
	}

    $object->picto = 'fa-briefcase';
    $object->element = 'fa-briefcase';
	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);
    $object->element = 'project';

    print '<hr/><br/><div class="fichecenter">';

    print '<table class="centpercent notopnoleftnoright table-fiche-title"><tbody><tr class="titre">';
    print '<td class="nobordernopadding valignmiddle col-title">'.$langs->trans("Instances associées").'</td>';
    print '<td class="nobordernopadding titre_right wordbreakimp right valignmiddle col-right">';
    print '</td></tr></tbody></table>';

    print '<div class="div-table-responsive">';
	print '<table class="tagtable liste" style="font-size: 12px;">';
	print '<tr class="liste_titre">';
	print '<th style="width: 150px;">Juridiction</th><th style="width: 120px;">Action<br/>Juridique</th><th style="width: 60px;">Chambre</th><th style="width: 40px;">No Rôle</th><th style="width: 10px;">Magistrat</th><th style="width: 90px;">Section</th><th style="width: 10px;">Date Décision</th><th style="width: 10px;">Date Signification</th><th style="width: 10px;">Date Recours</th><th style="width: 8px;">Avocat Postulant</th><th style="width: 8px;"></th>';
	print '</tr>';

    $sql_juridictions = "SELECT rowid, nom_etablissement FROM ".MAIN_DB_PREFIX."kjraffaire_dico_juridiction ORDER BY nom_etablissement ASC";
	$resql_juridictions = $db->query($sql_juridictions);
	$juridictions = [];
	while ($obj = $db->fetch_object($resql_juridictions)) {
		$juridictions[$obj->rowid] = $obj->nom_etablissement;
	}

    $sql_action_juridique = "SELECT rowid, label FROM ".MAIN_DB_PREFIX."kjraffaire_dico_action_juridique ORDER BY label ASC";
	$resql_action_juridique = $db->query($sql_action_juridique);
	$action_juridique = [];
	while ($obj = $db->fetch_object($resql_action_juridique)) {
		$action_juridique[$obj->rowid] = $obj->label;
	}
    $sql = "SELECT * FROM `".MAIN_DB_PREFIX."kjraffaire_instance` WHERE `fk_affaire`=".$object->id;
    $resql = $db->query($sql);
    if ($resql) {
        while ($obj = $db->fetch_object($resql)) {
            $edit_mode = ($_GET['action'] === 'edit_instance' && $_GET['instance_id'] == $obj->rowid);
            print '<tr class="oddeven">';
            if ($edit_mode) {
                print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
                print '<input type="hidden" name="token" value="'.newToken().'">';
                print '<input type="hidden" name="action" value="update_instance">';
                print '<input type="hidden" name="id" value="'.$object->id.'">';
                print '<input type="hidden" name="instance_id" value="'.$obj->rowid.'">';
                print '<td>'.$form->selectarray("fk_juridiction_update", $juridictions, $obj->fk_juridiction, 1, '', '', 'style="width: 150px; font-size: 12px;"').'</td>';
                print '<td>'.$form->selectarray("fk_action_juridique_update", $action_juridique, $obj->fk_action_juridique, 1, '', '', 'style="width: 120px; font-size: 12px;"').'</td>';
                print '<td><input type="text" name="chambre" value="'.$obj->chambre.'" style="width: 60px;"></td>';
                print '<td><input type="text" name="no_role" value="'.$obj->no_role.'" style="width: 40px;"></td>';
                print '<td>'.$form->select_contact('', $obj->fk_soc_avocat_postulant, "fk_socpeople_magistrat_update", 1, '', 1, '', 0, '', 1, '', '', 'style="width: 120px; font-size: 12px;"', '', 1).'</td>';
                print '<td><input type="text" name="section" value="'.$obj->section.'" style="width: 90px;"></td>';
                print '<td><input type="date" name="date_decision" value="'.$obj->date_decision.'" style="width: 80px;"></td>';
                print '<td><input type="date" name="date_signification" value="'.$obj->date_signification.'" style="width: 80px;"></td>';
                print '<td><input type="date" name="date_recours" value="'.$obj->date_recours.'" style="width: 80px;"></td>';
                print '<td>'.$form->select_contact('', $obj->fk_soc_avocat_postulant, 'fk_soc_avocat_postulant_update', 1, '', 1, '', 0, '', 1, '', '', 'style="width: 120px; font-size: 12px;"', '', 1).'</td>';
                print '<td class="right">';
                print '<button type="submit" class="button">Enregistrer</button>';
                print ' <a href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'" class="button">Annuler</a>';
                print '</td>';
                print '</form>';
            } else {
                print '<td>'.($juridictions[$obj->fk_juridiction] ?? '').'</td>';
                print '<td>'.($action_juridique[$obj->fk_action_juridique] ?? '').'</td>';
                print '<td>'.$obj->chambre.'</td>';
                print '<td>'.$obj->no_role.'</td>';
                if (!empty($obj->fk_socpeople_magistrat)) {
                    $contact = new Contact($db);
                    if ($contact->fetch($obj->fk_socpeople_magistrat) > 0) {
                        $societe = new Societe($db);
                        if ($societe->fetch($contact->socid) > 0) {
                            $contact_name = $contact->getFullName($langs). ' ('.$societe->name.')';
                        } else {
                            $contact_name = $contact->getFullName($langs);
                        }
                    } else {
                        $contact_name = '';
                    }
                    print '<td>'.$contact_name.'</td>';
                } else {
                    print '<td></td>';
                }
                print '<td>'.$obj->section.'</td>';
                print '<td>'.dol_print_date($obj->date_decision, 'day').'</td>';
                print '<td>'.dol_print_date($obj->date_signification, 'day').'</td>';
                print '<td>'.dol_print_date($obj->date_recours, 'day').'</td>';
                if (!empty($obj->fk_soc_avocat_postulant)) {
                    $contact = new Contact($db);
                    if ($contact->fetch($obj->fk_soc_avocat_postulant) > 0) {
                        $societe = new Societe($db);
                        if ($societe->fetch($contact->socid) > 0) {
                            $contact_name = $contact->getFullName($langs). ' ('.$societe->name.')';
                        } else {
                            $contact_name = $contact->getFullName($langs);
                        }
                    } else {
                        $contact_name = '';
                    }
                    print '<td>'.$contact_name.'</td>';
                } else {
                    print '<td></td>';
                }
                print '<td class="right">';
                print '<a href="'.$_SERVER["PHP_SELF"].'?action=edit_instance&id='.$object->id.'&instance_id='.$obj->rowid.'" class="editfielda">'.img_edit().'</a>';
                print ' <form method="POST" action="'.$_SERVER["PHP_SELF"].'" style="display:inline;">';
                print '<input type="hidden" name="token" value="'.newToken().'">';
                print '<input type="hidden" name="action" value="delete_instance">';
                print '<input type="hidden" name="id" value="'.$object->id.'">';
                print '<input type="hidden" name="instance_id" value="'.$obj->rowid.'">';
                print '<button type="submit" class="button-delete">'.img_delete().'</button>';
                print '</form>';
                print '</td>';
            }
            print '</tr>';
        }
        // Ligne pour ajouter une nouvelle instance
        print '<tr class="oddeven" style="background: #f9f9f9; border-top: 2px solid #ddd;">';
        print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
        print '<input type="hidden" name="token" value="'.newToken().'">';
        print '<input type="hidden" name="action" value="save_instance">';
        print '<input type="hidden" name="id" value="'.$object->id.'">';
        print '<td>'.$form->selectarray("fk_juridiction", $juridictions, '', 1, 0, 0, 'style="width: 150px; font-size: 12px;"', 0, 0, 0, '', '', 1).'</td>';
        print '<td>'.$form->selectarray("fk_action_juridique", $action_juridique, '', 1, 0, 0, 'style="width: 120px; font-size: 12px;"', 0, 0, 0, '', '', 1).'</td>';
        print '<td><input type="text" name="chambre" placeholder="Nouvelle chambre" style="width: 60px; font-size: 12px;"></td>';
        print '<td><input type="text" name="no_role" placeholder="N° de rôle" style="width: 40px; font-size: 12px;"></td>';
        print '<td>'.$form->select_contact('', "fk_socpeople_magistrat", "fk_socpeople_magistrat", 1, '', 1, '', 0, '', 1, '', '', 'style="width: 120px; font-size: 12px;"', 0, 1).'</td>';
        print '<td><input type="text" name="section" placeholder="Section" style="width: 90px; font-size: 12px;"></td>';
        print '<td><input type="date" name="date_decision" style="width: 75px; font-size: 12px;"></td>';
        print '<td><input type="date" name="date_signification" style="width: 75px; font-size: 12px;"></td>';
        print '<td><input type="date" name="date_recours" style="width: 75px; font-size: 12px;"></td>';
        print '<td>'.$form->select_contact('', "fk_soc_avocat_postulant", "fk_soc_avocat_postulant", 1, '', 1, '', 0, '', 1, '', '', 'style="width: 120px; font-size: 12px;"', 0, 1).'</td>';
        print '<td class="right"><button type="submit" class="button" style="font-size: 15px; padding: 4px 15px; min-width: 45px;"><b>+</b></button></td>';
        print '</form>';
        print '</tr>';
    } else {
        print '<tr><td colspan="5" class="center">Aucune instance trouvée</td></tr>';
    }

    print '</table>';
    print '</div>';
    print '<div class="clearboth"></div>';
	print '</div>';
	print '</div>';
	print '<div class="clearboth"></div>';

	print dol_get_fiche_end();

	print '</form>';
} else {
	print $langs->trans("RecordNotFound");
}

if ($_POST['action'] === 'save_instance') {
    $sql = "INSERT INTO `".MAIN_DB_PREFIX."kjraffaire_instance` (fk_affaire, fk_juridiction, fk_action_juridique, chambre, no_role, fk_socpeople_magistrat, section, date_decision, date_signification, date_recours, fk_soc_avocat_postulant, fk_socpeople_avocat_postulant) VALUES (".
        "'".$db->escape($_POST['id'])."', '".$db->escape($_POST['fk_juridiction'])."', '".$db->escape($_POST['fk_action_juridique'])."', '".$db->escape($_POST['chambre'])."', '".$db->escape($_POST['no_role'])."', '".$db->escape($_POST['fk_socpeople_magistrat'])."', '".$db->escape($_POST['section'])."', '".$db->escape($_POST['date_decision'])."', '".$db->escape($_POST['date_signification'])."', '".$db->escape($_POST['date_recours'])."', '".$db->escape($_POST['fk_soc_avocat_postulant'])."', '".$db->escape($_POST['fk_socpeople_avocat_postulant'])."')";
    $db->query($sql);
    header("Location: " . $_SERVER["PHP_SELF"] . "?id=" . $_POST['id']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update_instance') {
    $sql = "UPDATE `".MAIN_DB_PREFIX."kjraffaire_instance` SET ".
        "fk_juridiction='".$db->escape($_POST['fk_juridiction_update'])."', ".
        "fk_action_juridique='".$db->escape($_POST['fk_action_juridique_update'])."', ".
        "chambre='".$db->escape($_POST['chambre'])."', ".
        "no_role='".$db->escape($_POST['no_role'])."', ".
        "fk_socpeople_magistrat='".$db->escape($_POST['fk_socpeople_magistrat_update'])."', ".
        "section='".$db->escape($_POST['section'])."', ".
        "date_decision='".$db->escape($_POST['date_decision'])."', ".
        "date_signification='".$db->escape($_POST['date_signification'])."', ".
        "date_recours='".$db->escape($_POST['date_recours'])."', ".
        "fk_soc_avocat_postulant='".$db->escape($_POST['fk_soc_avocat_postulant_update'])."', ".
        "fk_socpeople_avocat_postulant='".$db->escape($_POST['fk_socpeople_avocat_postulant_update'])."' ".
        "WHERE rowid='".$db->escape($_POST['instance_id'])."'";
    $db->query($sql);
    setEventMessages('Instance modifiée', null, 'mesgs');
    $url = dol_buildpath("/custom/kjraffaire/affaire/instance.php", 1) . "?id=" . $_POST['id'];
    header("Location: " . $url);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'delete_instance') {
    $sql = "DELETE FROM `".MAIN_DB_PREFIX."kjraffaire_instance` WHERE rowid='".$db->escape($_POST['instance_id'])."'";
    $db->query($sql);
    header("Location: " . $_SERVER["PHP_SELF"] . "?id=" . $_POST['id']);
    exit;
}

// End of page
llxFooter();
$db->close();
