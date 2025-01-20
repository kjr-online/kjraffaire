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

// Mode édition d'un champ extrafield
if ($action == 'edit_extrafield' && $user->rights->projet->creer) {
    $object->array_options['options_instance'] = 1;
    $key = GETPOST('key', 'alpha');
    if (isset($extrafields->attributes['projet']['label'][$key])) {
        $extrafield_edit_key = $key;
    } else {
        setEventMessages($langs->trans("InvalidExtraFieldKey"), null, 'errors');
    }
}

// Mise à jour de l'extrafield après soumission du formulaire
if ($action == 'update_extrafield' && $user->rights->projet->creer) {
    $object->array_options['options_instance'] = 1;
    $key = GETPOST('key', 'alpha');
    if ($extrafields->attributes['projet']['type'][$key] == 'date') {
        $value = GETPOST('options_'.$key.'options_','alpha');
        if (!empty($value)) {
            // Convertir en timestamp
            $dateParts = explode('/', $value);
            if (count($dateParts) === 3) {
                $day = (int) $dateParts[0];
                $month = (int) $dateParts[1];
                $year = (int) $dateParts[2];
                $value = dol_mktime(0, 0, 0, $month, $day, $year);
            } else {
                $value = '';
            }
        }
    } else {
        $value = GETPOST('options_'.$key.'options_', 'alpha');
    }
    if (isset($extrafields->attributes['projet']['label'][$key])) {
        $object->array_options['options_'.$key] = $value; // Mettre à jour la valeur dans l'objet
        $result = $object->update($user); // Sauvegarder
        if ($result >= 0) {
            setEventMessages($langs->trans("ExtraFieldUpdated"), null, 'mesgs');
        } else {
            setEventMessages($object->error, $object->errors, 'errors');
        }
    } else {
        setEventMessages($langs->trans("InvalidExtraFieldKey"), null, 'errors');
    }
    header('Location: '.$_SERVER['PHP_SELF'].'?id='.$object->id);
    exit;
}

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
	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);

    // Liste des extrafields à afficher dans chaque colonne
    $extrafields_left = ['fk_kjraffaire_dico_juridiction', 'fk_kjraffaire_dico_action_juridique', 'chambre', 'no_role', 'fk_soc_magistrat', 'fk_socpeople_magistrat'];
    $extrafields_right = ['section', 'date_decision', 'date_signification', 'date_recours', 'fk_soc_avocat_postulant', 'fk_socpeople_avocat_postulant'];

    // Liste des clés déjà utilisées et à exclure
    $used_extrafields = array_merge($extrafields_left, $extrafields_right);
    $excluded_extrafields = ['affaire', 'type_affaire'];

    // Récupération des extrafields restants, en excluant ceux à ignorer
    $remaining_extrafields = array_diff(
        array_keys($extrafields->attributes['projet']['label']),
        array_merge($used_extrafields, $excluded_extrafields)
    );

    print '<hr/><br/><div class="fichecenter">';

    // Colonne gauche
    print '<div class="fichehalfleft">';
    print '<table class="border tableforfield centpercent">';

    // Liste des extrafields à gauche
    foreach ($extrafields_left as $key) {
        if (isset($extrafields->attributes['projet']['label'][$key])) {
            $label = $langs->trans($extrafields->attributes['projet']['label'][$key]);
            $value = $object->array_options['options_'.$key];

            print '<tr>';
            print '<td class="titlefield">'.$label.'</td>';
            print '<td>';

            // Mode édition pour ce champ
            if (isset($extrafield_edit_key) && $extrafield_edit_key === $key) {
                print '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
                print '<input type="hidden" name="token" value="'.newToken().'">';
                print '<input type="hidden" name="action" value="update_extrafield">';
                print '<input type="hidden" name="id" value="'.$object->id.'">';
                print '<input type="hidden" name="key" value="'.$key.'">';
                print $extrafields->showInputField($key, $object->array_options['options_'.$key], '', 'options_', '', '', $object->id, $object->table_element);
                print ' <button type="submit" class="button">'.$langs->trans("Save").'</button>';
                print ' <a href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'" class="button">'.$langs->trans("Cancel").'</a>';
                print '</form>';
            } else {
                // Affichage en mode lecture
                if ($extrafields->attributes['projet']['type'][$key] === 'date' && !empty($value)) {
                    print dol_print_date($value, 'day');
                } elseif ($extrafields->attributes['projet']['type'][$key] === 'sellist' && !empty($value)) {
                    $options = $extrafields->attributes['projet']['param'][$key]['options'];
                    if (!empty($options)) {
                        $option_parts = explode(':', key($options));
                        if (count($option_parts) >= 3) {
                            $table = $option_parts[0];
                            $field_label = $option_parts[1];
                            $field_id = $option_parts[2];
                            $sql = "SELECT ".$db->escape($field_label)." as label FROM ".MAIN_DB_PREFIX.$table." WHERE ".$db->escape($field_id)." = ".((int) $value);
                            $resql = $db->query($sql);
                            if ($resql) {
                                $obj = $db->fetch_object($resql);
                                if ($obj) {
                                    print $langs->trans($obj->label);
                                } else {
                                    print $langs->trans("Unknown"); // Affiche une erreur si aucun libellé trouvé
                                }
                            } else {
                                print $langs->trans("Error");
                            }
                        } else {
                            print $langs->trans("Error");
                        }
                    } else {
                        print $langs->trans("NoOption");
                    }
                } else {
                    print ($value === "0" || empty($value)) ? '' : nl2br($value);
                }
                print ' <a href="'.$_SERVER['PHP_SELF'].'?action=edit_extrafield&key='.$key.'&id='.$object->id.'" class="editfielda">'.img_edit($langs->transnoentitiesnoconv('Edit')).'</a>';
            }

            print '</td>';
            print '</tr>';
        }
    }

    print '</table>';
    print '</div>';

    // Colonne droite
    print '<div class="fichehalfright">';
    print '<table class="border tableforfield centpercent">';

    // Liste des extrafields à droite
    foreach ($extrafields_right as $key) {
        if (isset($extrafields->attributes['projet']['label'][$key])) {
            $label = $langs->trans($extrafields->attributes['projet']['label'][$key]);
            $value = $object->array_options['options_'.$key];

            print '<tr>';
            print '<td class="titlefield">'.$label.'</td>';
            print '<td>';

            // Mode édition pour ce champ
            if (isset($extrafield_edit_key) && $extrafield_edit_key === $key) {
                print '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
                print '<input type="hidden" name="token" value="'.newToken().'">';
                print '<input type="hidden" name="action" value="update_extrafield">';
                print '<input type="hidden" name="id" value="'.$object->id.'">';
                print '<input type="hidden" name="key" value="'.$key.'">';
                print $extrafields->showInputField($key, $object->array_options['options_'.$key], '', 'options_', '', '', $object->id, $object->table_element);
                print ' <button type="submit" class="button">'.$langs->trans("Save").'</button>';
                print ' <a href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'" class="button">'.$langs->trans("Cancel").'</a>';
                print '</form>';
            } else {
                // Affichage en mode lecture
                if ($extrafields->attributes['projet']['type'][$key] === 'date' && !empty($value)) {
                    print dol_print_date($value, 'day');
                } elseif ($extrafields->attributes['projet']['type'][$key] === 'sellist' && !empty($value)) {
                    $options = $extrafields->attributes['projet']['param'][$key]['options'];
                    if (!empty($options)) {
                        $option_parts = explode(':', key($options));
                        if (count($option_parts) >= 3) {
                            $table = $option_parts[0];
                            $field_label = $option_parts[1];
                            $field_id = $option_parts[2];
                            $sql = "SELECT ".$db->escape($field_label)." as label FROM ".MAIN_DB_PREFIX.$table." WHERE ".$db->escape($field_id)." = ".((int) $value);
                            $resql = $db->query($sql);
                            if ($resql) {
                                $obj = $db->fetch_object($resql);
                                if ($obj) {
                                    print $langs->trans($obj->label);
                                } else {
                                    print $langs->trans("Unknown");
                                }
                            } else {
                                print $langs->trans("Error");
                            }
                        } else {
                            print $langs->trans("Error");
                        }
                    } else {
                        print $langs->trans("NoOption");
                    }
                } else {
                    print ($value === "0" || empty($value)) ? '' : nl2br($value);
                }
                print ' <a href="'.$_SERVER['PHP_SELF'].'?action=edit_extrafield&key='.$key.'&id='.$object->id.'" class="editfielda">'.img_edit($langs->transnoentitiesnoconv('Edit')).'</a>';
            }

            print '</td>';
            print '</tr>';
        }
    }

    print '</table>';
    print '</div>';
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

// End of page
llxFooter();
$db->close();
