<?php
/* Copyright (C) 2025 Eric PICABIA
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    kjraffaire/lib/kjraffaire.lib.php
 * \ingroup kjraffaire
 * \brief   Library files with common functions for Kjraffaire
 */
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';

// Teste si l'utilisateur est affecté à la tache passée en paramètre
function EstAffecte($id){
	global $db,$user;
	$tasktmp = new Task($db);
	$tasktmp->fetch($id);
	$tasktmp->fetch_optionals();
	$contacts = $tasktmp->liste_contact(-1,'internal',1,'');
	foreach($contacts as $contact){
		if ($contact==$user->id){
			return True;
			break;
		}
	}
	return False;
}

function MajEtatTache ($id,$statut){
	global $db,$user;
	$tasktmp = new Task($db);
	$tasktmp->fetch($id);
	$tasktmp->fetch_optionals();
	switch ($statut) {
		case 'Ouverte':
			if (($tasktmp->array_options['options_etat']=='Saisie') ||(empty($tasktmp->array_options['options_etat']))){
				$contacts = $tasktmp->liste_contact(-1,'internal',1,'');
				foreach($contacts as $contact){
					if ($contact==$user->id){
						$tasktmp->array_options['options_etat']='Ouverte';
						$tasktmp->update();
						break;
					}
				}
			}
			break;
		case 'Cloturée':
		case 'Terminée':
			$tasktmp->array_options['options_etat']=$statut;
			$tasktmp->update();
			break;
		default:
			break;
	}
	

	return 0;
}

/**
 * Prepare admin pages header
 *
 * @return array
 */
function kjraffaireAdminPrepareHead()
{
	global $langs, $conf;

	// global $db;
	// $extrafields = new ExtraFields($db);
	// $extrafields->fetch_name_optionals_label('myobject');

	$langs->load("kjraffaire@kjraffaire");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/kjraffaire/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	/*
	$head[$h][0] = dol_buildpath("/kjraffaire/admin/myobject_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ExtraFields");
	$nbExtrafields = is_countable($extrafields->attributes['myobject']['label']) ? count($extrafields->attributes['myobject']['label']) : 0;
	if ($nbExtrafields > 0) {
		$head[$h][1] .= ' <span class="badge">' . $nbExtrafields . '</span>';
	}
	$head[$h][2] = 'myobject_extrafields';
	$h++;
	*/

	$head[$h][0] = dol_buildpath("/kjraffaire/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@kjraffaire:/kjraffaire/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@kjraffaire:/kjraffaire/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'kjraffaire@kjraffaire');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'kjraffaire@kjraffaire', 'remove');

	return $head;
}


/**
 * Header des pages tâches
*/
function kjrtache_prepare_head(Task $task, $moreparam = '')
{
	global $db, $langs, $conf, $user;

	$h = 0;
	$head = array();

	$head[$h][0] = DOL_URL_ROOT.'/custom/kjraffaire/tache/task.php?id='.((int) $task->id).($moreparam ? '&'.$moreparam : '');
	$head[$h][1] = $langs->trans("Tâche");
	$head[$h][2] = '';
	$h++;

	$nbContacts = 0;
	// Enable caching of project count Contacts
	require_once DOL_DOCUMENT_ROOT.'/core/lib/memory.lib.php';
	$cachekey = 'count_contacts_project_'.$task->id;
	$dataretrieved = dol_getcache($cachekey);

	$sql = "SELECT COUNT(ec.rowid) as nbContacts
        FROM ".MAIN_DB_PREFIX."element_contact ec
        INNER JOIN ".MAIN_DB_PREFIX."c_type_contact tc ON ec.fk_c_type_contact = tc.rowid
        WHERE ec.element_id = ".$db->escape($project->id)."
          AND tc.element = 'kjraffaire'";
	$resql = $db->query($sql);

	if ($resql) {
		$obj = $db->fetch_object($resql);
		if ($obj) {
			$nbContacts = $obj->nbContacts;
		}
	} else {
		dol_syslog("Error SQL pendant count contacts: ".$db->lasterror(), LOG_ERR);
	}
	$head[$h][0] = DOL_URL_ROOT.'/custom/kjraffaire/tache/contact.php?id='.((int) $task->id).($moreparam ? '&'.$moreparam : '');
	$head[$h][1] = $langs->trans("Contacts de la tâche");
	if ($nbContacts > 0) {
		$head[$h][1] .= '<span class="badge marginleftonlyshort">'.$nbContacts.'</span>';
	}
	$head[$h][2] = 'contact';
	$h++;

	$head[$h][0] = DOL_URL_ROOT.'/custom/kjraffaire/tache/time.php?id='.((int) $task->id).($moreparam ? '&'.$moreparam : '');
	$head[$h][1] = $langs->trans("Temps consommé");
	$head[$h][2] = 'kjr_time';
	$h++;

	
	if (!getDolGlobalString('MAIN_DISABLE_NOTES_TAB')) {
		$nbNote = 0;
		if (!empty($task->note_private)) {
			$nbNote++;
		}
		if (!empty($task->note_public)) {
			$nbNote++;
		}
		$head[$h][0] = DOL_URL_ROOT.'/custom/kjraffaire/tache/note.php?id='.$task->id;
		$head[$h][1] = $langs->trans('Notes');
		if ($nbNote > 0) {
			$head[$h][1] .= '<span class="badge marginleftonlyshort">'.$nbNote.'</span>';
		}
		$head[$h][2] = 'notes';
		$h++;
	}

	// Attached files and Links
	$totalAttached = 0;
	// Enable caching of thirdrparty count attached files and links
	require_once DOL_DOCUMENT_ROOT.'/core/lib/memory.lib.php';
	$cachekey = 'count_attached_project_'.$task->id;
	$dataretrieved = dol_getcache($cachekey);
	if (!is_null($dataretrieved)) {
		$totalAttached = $dataretrieved;
	} else {
		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
		require_once DOL_DOCUMENT_ROOT.'/core/class/link.class.php';
		$upload_dir = $conf->task->multidir_output[empty($task->entity) ? 1 : $task->entity]."/".dol_sanitizeFileName($task->ref);
		$nbFiles = count(dol_dir_list($upload_dir, 'files', 0, '', '(\.meta|_preview.*\.png)$'));
		$nbLinks = Link::count($db, $task->element, $task->id);
		$totalAttached = $nbFiles + $nbLinks;
		dol_setcache($cachekey, $totalAttached, 120);		// If setting cache fails, this is not a problem, so we do not test result.
	}
	$h++;

	return $head;
}





/**
 * Header des pages affaires
*/
function affaire_prepare_head(Project $project, $moreparam = '')
{
	global $db, $langs, $conf, $user;

	$h = 0;
	$head = array();

	$head[$h][0] = DOL_URL_ROOT.'/custom/kjraffaire/affaire/card.php?id='.((int) $project->id).($moreparam ? '&'.$moreparam : '');
	$head[$h][1] = $langs->trans("Affaire");
	$head[$h][2] = 'project';
	$h++;

	// Test si "Type d'affaire" = "Procédure", dans ce cas affichage de l'onglet :
	require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
	$extrafields = new ExtraFields($db);
	$extrafields->fetch_name_optionals_label('project');

	if (!empty($project->array_options) && isset($project->array_options['options_type_affaire'])) {
		$typeAffaireId = (int) $project->array_options['options_type_affaire']; // ID extrafield

		// Req pour récup la valeur texte du dictionnaire
		$sql = "SELECT label FROM ".MAIN_DB_PREFIX."kjraffaire_dico_type_affaire WHERE rowid = ".$typeAffaireId." AND active = 1";
		$resql = $db->query($sql);

		if ($resql) {
			$obj = $db->fetch_object($resql);
			if ($obj && $obj->label == 'Procédure') {
				$head[$h][0] = DOL_URL_ROOT.'/custom/kjraffaire/affaire/instance.php?id='.$project->id;
				$head[$h][1] = "Instance";
				$head[$h][2] = 'instance';
				$h++;
			}
		} else {
			dol_syslog("Erreur SQL : ".$db->lasterror(), LOG_ERR);
		}
	}

	$nbContacts = 0;
	// Enable caching of project count Contacts
	require_once DOL_DOCUMENT_ROOT.'/core/lib/memory.lib.php';
	$cachekey = 'count_contacts_project_'.$project->id;
	$dataretrieved = dol_getcache($cachekey);

	$sql = "SELECT COUNT(ec.rowid) as nbContacts
        FROM ".MAIN_DB_PREFIX."element_contact ec
        INNER JOIN ".MAIN_DB_PREFIX."c_type_contact tc ON ec.fk_c_type_contact = tc.rowid
        WHERE ec.element_id = ".$db->escape($project->id)."
          AND tc.element = 'kjraffaire'";
	$resql = $db->query($sql);

	if ($resql) {
		$obj = $db->fetch_object($resql);
		if ($obj) {
			$nbContacts = $obj->nbContacts;
		}
	} else {
		dol_syslog("Error SQL pendant count contacts: ".$db->lasterror(), LOG_ERR);
	}

	$head[$h][0] = DOL_URL_ROOT.'/custom/kjraffaire/affaire/contact.php?id='.((int) $project->id).($moreparam ? '&'.$moreparam : '');
	$head[$h][1] = $langs->trans("AffaireContact");
	if ($nbContacts > 0) {
		$head[$h][1] .= '<span class="badge marginleftonlyshort">'.$nbContacts.'</span>';
	}
	$head[$h][2] = 'contact';
	$h++;	

	/*
	$head[$h][0] = DOL_URL_ROOT.'/custom/kjraffaire/affaire/contact2.php?id='.((int) $project->id).($moreparam ? '&'.$moreparam : '');
	$head[$h][1] = $langs->trans("AffaireContact");
	if ($nbContacts > 0) {
		$head[$h][1] .= '<span class="badge marginleftonlyshort">'.$nbContacts.'</span>';
	}
	$head[$h][2] = 'contact';
	$h++;
	*/

	if (!getDolGlobalString('PROJECT_HIDE_TASKS')) {
		// Then tab for sub level of projet, i mean tasks
		$nbTasks = 0;
		// Enable caching of project count Tasks
		require_once DOL_DOCUMENT_ROOT.'/core/lib/memory.lib.php';
		$cachekey = 'count_tasks_project_'.$project->id;
		$dataretrieved = dol_getcache($cachekey);

		if (!is_null($dataretrieved)) {
			$nbTasks = $dataretrieved;
		} else {
			require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
			$taskstatic = new Task($db);
			$nbTasks = count($taskstatic->getTasksArray(0, 0, $project->id, 0, 0));
			dol_setcache($cachekey, $nbTasks, 120);	// If setting cache fails, this is not a problem, so we do not test result.
		}
		$head[$h][0] = DOL_URL_ROOT.'/custom/kjraffaire/tache/tasks.php?id='.((int) $project->id).($moreparam ? '&'.$moreparam : '');
		$head[$h][1] = $langs->trans("Tasks");
		if ($nbTasks > 0) {
			$head[$h][1] .= '<span class="badge marginleftonlyshort">'.($nbTasks).'</span>';
		}
		$head[$h][2] = 'tasks';
		$h++;

		$nbTimeSpent = 0;
		// Enable caching of project count Timespent
		$cachekey = 'count_timespent_project_'.$project->id;
		$dataretrieved = dol_getcache($cachekey);
		if (!is_null($dataretrieved)) {
			$nbTimeSpent = $dataretrieved;
		} else {
			$sql = "SELECT t.rowid";
			//$sql .= " FROM ".MAIN_DB_PREFIX."element_time as t, ".MAIN_DB_PREFIX."projet_task as pt, ".MAIN_DB_PREFIX."user as u";
			//$sql .= " WHERE t.fk_user = u.rowid AND t.fk_task = pt.rowid";
			$sql .= " FROM ".MAIN_DB_PREFIX."element_time as t, ".MAIN_DB_PREFIX."projet_task as pt";
			$sql .= " WHERE t.fk_element = pt.rowid";
			$sql .= " AND t.elementtype = 'task'";
			$sql .= " AND pt.fk_projet =".((int) $project->id);
			$resql = $db->query($sql);
			if ($resql) {
				$obj = $db->fetch_object($resql);
				if ($obj) {
					$nbTimeSpent = 1;
					dol_setcache($cachekey, $nbTimeSpent, 120);	// If setting cache fails, this is not a problem, so we do not test result.
				}
			} else {
				dol_print_error($db);
			}
		}

		$head[$h][0] = DOL_URL_ROOT.'/custom/kjraffaire/tache/time.php?withproject=1&projectid='.((int) $project->id).($moreparam ? '&'.$moreparam : '');
		$head[$h][1] = $langs->trans("TimeSpent");
		if ($nbTimeSpent > 0) {
			$head[$h][1] .= '<span class="badge marginleftonlyshort">...</span>';
		}
		$head[$h][2] = 'timespent';
		$h++;
	}

	if (isModEnabled("supplier_proposal") || isModEnabled("supplier_order") || isModEnabled("supplier_invoice")
		|| isModEnabled("propal") || isModEnabled('order')
		|| isModEnabled('invoice') || isModEnabled('contract')
		|| isModEnabled('intervention') || isModEnabled('agenda') || isModEnabled('deplacement') || isModEnabled('stock')) {
		$nbElements = 0;
		// Enable caching of thirdrparty count Contacts
		$cachekey = 'count_elements_project_'.$project->id;
		$dataretrieved = dol_getcache($cachekey);
		if (!is_null($dataretrieved)) {
			$nbElements = $dataretrieved;
		} else {
			if (isModEnabled('stock')) {
				$nbElements += $project->getElementCount('stock', 'entrepot', 'fk_project');
			}
			if (isModEnabled("propal")) {
				$nbElements += $project->getElementCount('propal', 'propal');
			}
			if (isModEnabled('order')) {
				$nbElements += $project->getElementCount('order', 'commande');
			}
			if (isModEnabled('invoice')) {
				$nbElements += $project->getElementCount('invoice', 'facture');
			}
			if (isModEnabled('invoice')) {
				$nbElements += $project->getElementCount('invoice_predefined', 'facture_rec');
			}
			if (isModEnabled('supplier_proposal')) {
				$nbElements += $project->getElementCount('proposal_supplier', 'supplier_proposal');
			}
			if (isModEnabled("supplier_order")) {
				$nbElements += $project->getElementCount('order_supplier', 'commande_fournisseur');
			}
			if (isModEnabled("supplier_invoice")) {
				$nbElements += $project->getElementCount('invoice_supplier', 'facture_fourn');
			}
			if (isModEnabled('contract')) {
				$nbElements += $project->getElementCount('contract', 'contrat');
			}
			if (isModEnabled('intervention')) {
				$nbElements += $project->getElementCount('intervention', 'fichinter');
			}
			if (isModEnabled("shipping")) {
				$nbElements += $project->getElementCount('shipping', 'expedition');
			}
			if (isModEnabled('mrp')) {
				$nbElements += $project->getElementCount('mrp', 'mrp_mo', 'fk_project');
			}
			if (isModEnabled('deplacement')) {
				$nbElements += $project->getElementCount('trip', 'deplacement');
			}
			if (isModEnabled('expensereport')) {
				$nbElements += $project->getElementCount('expensereport', 'expensereport');
			}
			if (isModEnabled('don')) {
				$nbElements += $project->getElementCount('donation', 'don');
			}
			if (isModEnabled('loan')) {
				$nbElements += $project->getElementCount('loan', 'loan');
			}
			if (isModEnabled('tax')) {
				$nbElements += $project->getElementCount('chargesociales', 'chargesociales');
			}
			if (isModEnabled('project')) {
				$nbElements += $project->getElementCount('project_task', 'projet_task');
			}
			if (isModEnabled('stock')) {
				$nbElements += $project->getElementCount('stock_mouvement', 'stock');
			}
			if (isModEnabled('salaries')) {
				$nbElements += $project->getElementCount('salaries', 'payment_salary');
			}
			if (isModEnabled("bank")) {
				$nbElements += $project->getElementCount('variouspayment', 'payment_various');
			}
			dol_setcache($cachekey, $nbElements, 120);	// If setting cache fails, this is not a problem, so we do not test result.
		}
		/*$head[$h][0] = DOL_URL_ROOT.'/projet/element.php?id='.$project->id;
		$head[$h][1] = $langs->trans("ProjectOverview");
		if ($nbElements > 0) {
			$head[$h][1] .= '<span class="badge marginleftonlyshort">'.$nbElements.'</span>';
		}
		$head[$h][2] = 'element';*/
		$h++;
	}

	if (isModEnabled('ticket') && $user->hasRight('ticket', 'read')) {
		require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
		$Tickettatic = new Ticket($db);
		$nbTicket = $Tickettatic->getCountOfItemsLinkedByObjectID($project->id, 'fk_project', 'ticket');
		$head[$h][0] = DOL_URL_ROOT.'/ticket/list.php?projectid='.((int) $project->id);
		$head[$h][1] = $langs->trans("Ticket");
		if ($nbTicket > 0) {
			$head[$h][1] .= '<span class="badge marginleftonlyshort">'.($nbTicket).'</span>';
		}
		$head[$h][2] = 'ticket';
		$h++;
	}

	if (isModEnabled('eventorganization') && !empty($project->usage_organize_event)) {
		$langs->load('eventorganization');
		$head[$h][0] = DOL_URL_ROOT . '/eventorganization/conferenceorbooth_list.php?projectid=' . $project->id;
		$head[$h][1] = $langs->trans("EventOrganization");

		// Enable caching of conf or booth count
		$nbConfOrBooth = 0;
		$nbAttendees = 0;
		require_once DOL_DOCUMENT_ROOT.'/core/lib/memory.lib.php';
		$cachekey = 'count_conferenceorbooth_'.$project->id;
		$dataretrieved = dol_getcache($cachekey);
		if (!is_null($dataretrieved)) {
			$nbConfOrBooth = $dataretrieved;
		} else {
			require_once DOL_DOCUMENT_ROOT.'/eventorganization/class/conferenceorbooth.class.php';
			$conforbooth = new ConferenceOrBooth($db);
			$result = $conforbooth->fetchAll('', '', 0, 0, '(t.fk_project:=:'.((int) $project->id).")");
			//,
			if (!is_array($result) && $result < 0) {
				setEventMessages($conforbooth->error, $conforbooth->errors, 'errors');
			} else {
				$nbConfOrBooth = count($result);
			}
			dol_setcache($cachekey, $nbConfOrBooth, 120);	// If setting cache fails, this is not a problem, so we do not test result.
		}
		$cachekey = 'count_attendees_'.$project->id;
		$dataretrieved = dol_getcache($cachekey);
		if (!is_null($dataretrieved)) {
			$nbAttendees = $dataretrieved;
		} else {
			require_once DOL_DOCUMENT_ROOT.'/eventorganization/class/conferenceorboothattendee.class.php';
			$conforboothattendee = new ConferenceOrBoothAttendee($db);
			$result = $conforboothattendee->fetchAll('', '', 0, 0, '(t.fk_project:=:'.((int) $project->id).')');

			if (!is_array($result) && $result < 0) {
				setEventMessages($conforboothattendee->error, $conforboothattendee->errors, 'errors');
			} else {
				$nbAttendees = count($result);
			}
			dol_setcache($cachekey, $nbAttendees, 120);	// If setting cache fails, this is not a problem, so we do not test result.
		}
		if ($nbConfOrBooth > 0 || $nbAttendees > 0) {
			$head[$h][1] .= '<span class="badge marginleftonlyshort">';
			$head[$h][1] .= '<span title="'.dol_escape_htmltag($langs->trans("ConferenceOrBooth")).'">'.$nbConfOrBooth.'</span>';
			$head[$h][1] .= ' + ';
			$head[$h][1] .= '<span title="'.dol_escape_htmltag($langs->trans("Attendees")).'">'.$nbAttendees.'</span>';
			$head[$h][1] .= '</span>';
		}
		$head[$h][2] = 'eventorganisation';
		$h++;
	}

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	// $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
	// $this->tabs = array('entity:-tabname);   												to remove a tab
	complete_head_from_modules($conf, $langs, $project, $head, $h, 'project', 'add', 'core');


	if (!getDolGlobalString('MAIN_DISABLE_NOTES_TAB')) {
		$nbNote = 0;
		if (!empty($project->note_private)) {
			$nbNote++;
		}
		if (!empty($project->note_public)) {
			$nbNote++;
		}
		$head[$h][0] = DOL_URL_ROOT.'/custom/kjraffaire/affaire/note.php?id='.$project->id;
		$head[$h][1] = $langs->trans('Notes');
		if ($nbNote > 0) {
			$head[$h][1] .= '<span class="badge marginleftonlyshort">'.$nbNote.'</span>';
		}
		$head[$h][2] = 'notes';
		$h++;
	}

	// Attached files and Links
	$totalAttached = 0;
	// Enable caching of thirdrparty count attached files and links
	require_once DOL_DOCUMENT_ROOT.'/core/lib/memory.lib.php';
	$cachekey = 'count_attached_project_'.$project->id;
	$dataretrieved = dol_getcache($cachekey);
	if (!is_null($dataretrieved)) {
		$totalAttached = $dataretrieved;
	} else {
		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
		require_once DOL_DOCUMENT_ROOT.'/core/class/link.class.php';
		$upload_dir = $conf->project->multidir_output[empty($project->entity) ? 1 : $project->entity]."/".dol_sanitizeFileName($project->ref);
		$nbFiles = count(dol_dir_list($upload_dir, 'files', 0, '', '(\.meta|_preview.*\.png)$'));
		$nbLinks = Link::count($db, $project->element, $project->id);
		$totalAttached = $nbFiles + $nbLinks;
		dol_setcache($cachekey, $totalAttached, 120);		// If setting cache fails, this is not a problem, so we do not test result.
	}
	/*$head[$h][0] = DOL_URL_ROOT.'/projet/document.php?id='.$project->id;
	$head[$h][1] = $langs->trans('Documents');
	if (($totalAttached) > 0) {
		$head[$h][1] .= '<span class="badge marginleftonlyshort">'.($totalAttached).'</span>';
	}
	$head[$h][2] = 'document';*/
	$h++;

	// Manage discussion
	if (getDolGlobalString('PROJECT_ALLOW_COMMENT_ON_PROJECT')) {
		$nbComments = 0;
		// Enable caching of thirdrparty count attached files and links
		require_once DOL_DOCUMENT_ROOT.'/core/lib/memory.lib.php';
		$cachekey = 'count_attached_project_'.$project->id;
		$dataretrieved = dol_getcache($cachekey);
		if (!is_null($dataretrieved)) {
			$nbComments = $dataretrieved;
		} else {
			$nbComments = $project->getNbComments();
			dol_setcache($cachekey, $nbComments, 120);		// If setting cache fails, this is not a problem, so we do not test result.
		}
		$head[$h][0] = DOL_URL_ROOT.'/projet/comment.php?id='.$project->id;
		$head[$h][1] = $langs->trans("CommentLink");
		if ($nbComments > 0) {
			$head[$h][1] .= '<span class="badge marginleftonlyshort">'.$nbComments.'</span>';
		}
		$head[$h][2] = 'project_comment';
		$h++;
	}

	/*$head[$h][0] = DOL_URL_ROOT.'/projet/messaging.php?id='.$project->id;
	$head[$h][1] = $langs->trans("Events");
	if (isModEnabled('agenda') && ($user->hasRight('agenda', 'myactions', 'read') || $user->hasRight('agenda', 'allactions', 'read'))) {
		$head[$h][1] .= '/';
		$head[$h][1] .= $langs->trans("Agenda");
	}
	$head[$h][2] = 'agenda';*/
	$h++;

	complete_head_from_modules($conf, $langs, $project, $head, $h, 'project', 'add', 'external');

	complete_head_from_modules($conf, $langs, $project, $head, $h, 'project', 'remove');

	return $head;
}

/**
 * 		Show html area for list of projects
 *
 *		@param	Conf		$conf			Object conf
 * 		@param	Translate	$langs			Object langs
 * 		@param	DoliDB		$db				Database handler
 * 		@param	Object		$object			Third party object
 *      @param  string		$backtopage		Url to go once contact is created
 *      @param  int         $nocreatelink   1=Hide create project link
 *      @param	string		$morehtmlright	More html on right of title
 *      @return	int
 */
function kjr_show_projects($conf, $langs, $db, $object, $backtopage = '', $nocreatelink = 0, $morehtmlright = '')
{
	global $user, $action, $hookmanager, $form, $massactionbutton, $massaction, $arrayofselected, $arrayofmassactions;

	$i = -1;

	if (isModEnabled('project') && $user->hasRight('projet', 'lire')) {
		$langs->load("projects");

		$newcardbutton = '';
		if (isModEnabled('project') && $user->hasRight('projet', 'creer') && empty($nocreatelink)) {
			$newcardbutton .= dolGetButtonTitle($langs->trans('AddProject'), '', 'fa fa-plus-circle', DOL_URL_ROOT.'/projet/card.php?socid='.$object->id.'&amp;action=create&amp;backtopage='.urlencode($backtopage));
		}

		print "\n";
		print load_fiche_titre($langs->trans("ProjectsDedicatedToThisThirdParty"), $newcardbutton.$morehtmlright, '');

		print '<div class="div-table-responsive">'."\n";
		print '<table class="noborder centpercent">';

		$sql  = "SELECT p.rowid as id, p.entity, p.title, p.ref, p.public, p.dateo as do, p.datee as de, p.fk_statut as status, p.fk_opp_status, p.opp_amount, p.opp_percent, p.tms as date_modification, p.budget_amount";
		$sql .= ", cls.code as opp_status_code";
		$sql .= " FROM ".MAIN_DB_PREFIX."projet as p";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_lead_status as cls on p.fk_opp_status = cls.rowid";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."projet_extrafields as pe on p.rowid = pe.fk_object";
		$sql .= " WHERE p.fk_soc = ".((int) $object->id);
		$sql .= " AND p.entity IN (".getEntity('project').")";
		$sql .= " AND pe.affaire IS NULL";
		$sql .= " ORDER BY p.dateo DESC";

		$result = $db->query($sql);
		if ($result) {
			$num = $db->num_rows($result);

			print '<tr class="liste_titre">';
			if (getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN')) {
				print '<td class="center">';
				$selectedfields = (is_array($arrayofmassactions) && count($arrayofmassactions) ? $form->showCheckAddButtons('checkforselect', 1) : '');
				print $selectedfields;
				print '</td>';
			}
			print '<td>'.$langs->trans("Ref").'</td>';
			print '<td>'.$langs->trans("Name").'</td>';
			print '<td class="center">'.$langs->trans("DateStart").'</td>';
			print '<td class="center">'.$langs->trans("DateEnd").'</td>';
			print '<td class="right">'.$langs->trans("OpportunityAmountShort").'</td>';
			print '<td class="center">'.$langs->trans("OpportunityStatusShort").'</td>';
			print '<td class="right">'.$langs->trans("OpportunityProbabilityShort").'</td>';
			print '<td class="right">'.$langs->trans("Status").'</td>';
			if (!getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN')) {
				print '<td class="center">';
				$selectedfields = (is_array($arrayofmassactions) && count($arrayofmassactions) ? $form->showCheckAddButtons('checkforselect', 1) : '');
				print $selectedfields;
				print '</td>';
			}
			print '</tr>';

			if ($num > 0) {
				require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';

				$projecttmp = new Project($db);

				$i = 0;

				while ($i < $num) {
					$obj = $db->fetch_object($result);
					$projecttmp->fetch($obj->id);

					// To verify role of users
					$userAccess = $projecttmp->restrictedProjectArea($user);

					if ($user->hasRight('projet', 'lire') && $userAccess > 0) {
						print '<tr class="oddeven">';

						if (getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN')) {
							print '<td class="nowrap center actioncolumn">';
							if ($massactionbutton || $massaction) {
								$selected = 0;
								if (in_array($obj->id, $arrayofselected)) {
									$selected = 1;
								}
								print '<input id="cb'.$obj->id.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->id.'"'.($selected ? ' checked="checked"' : '').'>';
							}
							print '</td>';
						}
						// Ref
						print '<td class="nowraponall">';
						print $projecttmp->getNomUrl(1, '', 0, '', '-', 0, 1, '', 'project:'.$_SERVER["PHP_SELF"].'?socid=__SOCID__');
						print '</td>';

						// Label
						print '<td class="tdoverflowmax200" title="'.dol_escape_htmltag($obj->title).'">'.dol_escape_htmltag($obj->title).'</td>';
						// Date start
						print '<td class="center">'.dol_print_date($db->jdate($obj->do), "day").'</td>';
						// Date end
						print '<td class="center">'.dol_print_date($db->jdate($obj->de), "day").'</td>';
						// Opp amount
						print '<td class="right">';
						if ($obj->opp_status_code) {
							print '<span class="amount">'.price($obj->opp_amount, 1, '', 1, -1, -1, '').'</span>';
						}
						print '</td>';
						// Opp status
						print '<td class="center">';
						if ($obj->opp_status_code) {
							print $langs->trans("OppStatus".$obj->opp_status_code);
						}
						print '</td>';
						// Opp percent
						print '<td class="right">';
						if ($obj->opp_percent) {
							print price($obj->opp_percent, 1, '', 1, 0).'%';
						}
						print '</td>';
						// Status
						print '<td class="right">'.$projecttmp->getLibStatut(5).'</td>';

						// Action column
						if (!getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN')) {
							print '<td class="nowrap center actioncolumn">';
							if ($massactionbutton || $massaction) {
								$selected = 0;
								if (in_array($obj->id, $arrayofselected)) {
									$selected = 1;
								}
								print '<input id="cb'.$obj->id.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->id.'"'.($selected ? ' checked="checked"' : '').'>';
							}
							print '</td>';
						}
						print '</tr>';
					}
					$i++;
				}
			} else {
				print '<tr class="oddeven"><td colspan="9"><span class="opacitymedium">'.$langs->trans("None").'</span></td></tr>';
			}
			$db->free($result);
		} else {
			dol_print_error($db);
		}

		//projects linked to that thirdpart because of a people of that company is linked to a project
		if (getDolGlobalString('PROJECT_DISPLAY_LINKED_BY_CONTACT')) {
			print "\n";
			print load_fiche_titre($langs->trans("ProjectsLinkedToThisThirdParty"), '', '');


			print '<div class="div-table-responsive">'."\n";
			print '<table class="noborder centpercent">';

			$sql  = "SELECT p.rowid as id, p.entity, p.title, p.ref, p.public, p.dateo as do, p.datee as de, p.fk_statut as status, p.fk_opp_status, p.opp_amount, p.opp_percent, p.tms as date_update, p.budget_amount";
			$sql .= ", cls.code as opp_status_code";
			$sql .= " FROM ".MAIN_DB_PREFIX."projet as p";
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_lead_status as cls on p.fk_opp_status = cls.rowid";
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."element_contact as ec on p.rowid = ec.element_id";
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."socpeople as sc on ec.fk_socpeople = sc.rowid";
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_type_contact as tc on ec.fk_c_type_contact = tc.rowid";
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."projet_extrafields as pe on p.rowid = pe.fk_object";
			$sql .= " WHERE sc.fk_soc = ".((int) $object->id);
			$sql .= " AND p.entity IN (".getEntity('project').")";
			$sql .= " AND tc.element = 'project' AND tc.source = 'external'";
			$sql .= " AND pe.affaire IS NULL";
			$sql .= " ORDER BY p.dateo DESC";

			$result = $db->query($sql);
			if ($result) {
				$num = $db->num_rows($result);

				print '<tr class="liste_titre">';
				print '<td>'.$langs->trans("Ref").'</td>';
				print '<td>'.$langs->trans("Name").'</td>';
				print '<td class="center">'.$langs->trans("DateStart").'</td>';
				print '<td class="center">'.$langs->trans("DateEnd").'</td>';
				print '<td class="right">'.$langs->trans("OpportunityAmountShort").'</td>';
				print '<td class="center">'.$langs->trans("OpportunityStatusShort").'</td>';
				print '<td class="right">'.$langs->trans("OpportunityProbabilityShort").'</td>';
				print '<td class="right">'.$langs->trans("Status").'</td>';
				print '</tr>';

				if ($num > 0) {
					require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';

					$projecttmp = new Project($db);

					$i = 0;

					while ($i < $num) {
						$obj = $db->fetch_object($result);
						$projecttmp->fetch($obj->id);

						// To verify role of users
						$userAccess = $projecttmp->restrictedProjectArea($user);

						if ($user->rights->projet->lire && $userAccess > 0) {
							print '<tr class="oddeven">';

							// Ref
							print '<td class="nowraponall">';
							print $projecttmp->getNomUrl(1, '', 0, '', '-', 0, 1, '', 'project:'.$_SERVER["PHP_SELF"].'?socid=__SOCID__');
							print '</td>';

							// Label
							print '<td class="tdoverflowmax200" title="'.dol_escape_htmltag($obj->title).'">'.dol_escape_htmltag($obj->title).'</td>';
							// Date start
							print '<td class="center">'.dol_print_date($db->jdate($obj->do), "day").'</td>';
							// Date end
							print '<td class="center">'.dol_print_date($db->jdate($obj->de), "day").'</td>';
							// Opp amount
							print '<td class="right">';
							if ($obj->opp_status_code) {
								print '<span class="amount">'.price($obj->opp_amount, 1, '', 1, -1, -1, '').'</span>';
							}
							print '</td>';
							// Opp status
							print '<td class="center">';
							if ($obj->opp_status_code) {
								print $langs->trans("OppStatus".$obj->opp_status_code);
							}
							print '</td>';
							// Opp percent
							print '<td class="right">';
							if ($obj->opp_percent) {
								print price($obj->opp_percent, 1, '', 1, 0).'%';
							}
							print '</td>';
							// Status
							print '<td class="right">'.$projecttmp->getLibStatut(5).'</td>';

							print '</tr>';
						}
						$i++;
					}
				} else {
					print '<tr class="oddeven"><td colspan="8"><span class="opacitymedium">'.$langs->trans("None").'</span></td></tr>';
				}
				$db->free($result);
			} else {
				dol_print_error($db);
			}
		}

		$parameters = array('sql' => $sql, 'function' => 'show_projects');
		$reshook = $hookmanager->executeHooks('printFieldListFooter', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		print $hookmanager->resPrint;

		print "</table>";
		print '</div>';

		print "<br>\n";
	}

	return $i;
}

/**
 * 		Show html area for list of projects
 *
 *		@param	Conf		$conf			Object conf
 * 		@param	Translate	$langs			Object langs
 * 		@param	DoliDB		$db				Database handler
 * 		@param	Object		$object			Third party object
 *      @param  string		$backtopage		Url to go once contact is created
 *      @param  int         $nocreatelink   1=Hide create project link
 *      @param	string		$morehtmlright	More html on right of title
 *      @return	int
 */
function kjr_show_affaires($conf, $langs, $db, $object, $backtopage = '', $nocreatelink = 0, $morehtmlright = '')
{
	global $user, $action, $hookmanager, $form, $massactionbutton, $massaction, $arrayofselected, $arrayofmassactions;

	$i = -1;

	if (isModEnabled('project') && $user->hasRight('projet', 'lire')) {
		$langs->load("projects");

		$newcardbutton = '';
		if (isModEnabled('project') && $user->hasRight('projet', 'creer') && empty($nocreatelink)) {
			$newcardbutton .= dolGetButtonTitle($langs->trans('Nouvelle Affaire'), '', 'fa fa-plus-circle', DOL_URL_ROOT.'/projet/card.php?socid='.$object->id.'&amp;action=create&amp;backtopage='.urlencode($backtopage));
		}

		print "\n";
		print load_fiche_titre($langs->trans("Affaires dédiées à ce tiers"), $newcardbutton.$morehtmlright, '');

		print '<div class="div-table-responsive">'."\n";
		print '<table class="noborder centpercent">';

		$sql  = "SELECT p.rowid as id, p.entity, p.title, p.ref, p.public, p.dateo as do, p.datee as de, p.fk_statut as status, p.fk_opp_status, p.opp_amount, p.opp_percent, p.tms as date_modification, p.budget_amount";
		$sql .= ", cls.code as opp_status_code";
		$sql .= " FROM ".MAIN_DB_PREFIX."projet as p";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_lead_status as cls on p.fk_opp_status = cls.rowid";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."projet_extrafields as pe on p.rowid = pe.fk_object";
		$sql .= " WHERE p.fk_soc = ".((int) $object->id);
		$sql .= " AND p.entity IN (".getEntity('project').")";
		$sql .= " AND pe.affaire = 1";
		$sql .= " ORDER BY p.dateo DESC";

		$result = $db->query($sql);
		if ($result) {
			$num = $db->num_rows($result);

			print '<tr class="liste_titre">';
			if (getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN')) {
				print '<td class="center">';
				$selectedfields = (is_array($arrayofmassactions) && count($arrayofmassactions) ? $form->showCheckAddButtons('checkforselect', 1) : '');
				print $selectedfields;
				print '</td>';
			}
			print '<td>'.$langs->trans("Ref").'</td>';
			print '<td>'.$langs->trans("Name").'</td>';
			print '<td class="right">'.$langs->trans("Status").'</td>';
			if (!getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN')) {
				print '<td class="center">';
				$selectedfields = (is_array($arrayofmassactions) && count($arrayofmassactions) ? $form->showCheckAddButtons('checkforselect', 1) : '');
				print $selectedfields;
				print '</td>';
			}
			print '</tr>';

			if ($num > 0) {
				require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';

				$projecttmp = new Project($db);

				$i = 0;

				while ($i < $num) {
					$obj = $db->fetch_object($result);
					$projecttmp->fetch($obj->id);

					// To verify role of users
					$userAccess = $projecttmp->restrictedProjectArea($user);

					if ($user->hasRight('projet', 'lire') && $userAccess > 0) {
						print '<tr class="oddeven">';

						if (getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN')) {
							print '<td class="nowrap center actioncolumn">';
							if ($massactionbutton || $massaction) {
								$selected = 0;
								if (in_array($obj->id, $arrayofselected)) {
									$selected = 1;
								}
								print '<input id="cb'.$obj->id.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->id.'"'.($selected ? ' checked="checked"' : '').'>';
							}
							print '</td>';
						}
						// Ref
						print '<td class="nowraponall">';

						$iconStyle = $object->public == 1 
							? 'color: #986C6A;'
							: ($object->public == 0 
								? 'color: #6C6AA8;'
								: 'color: #6CA89C;');
			
						$url = dol_buildpath('/custom/kjraffaire/affaire/card.php', 1) . '?id=' . $obj->id . '&save_lastsearch_values=1';
			
						print '<i class="fas fa-briefcase paddingrightonly valignmiddle" style="'.$iconStyle.'"></i>';
						print '<a href="'.$url.'">'.htmlspecialchars($obj->ref).'</a>';
			
						print '</td>';

						// Label
						print '<td class="tdoverflowmax200" title="'.dol_escape_htmltag($obj->title).'">'.dol_escape_htmltag($obj->title).'</td>';
						// Status
						print '<td class="right">'.$projecttmp->getLibStatut(5).'</td>';

						// Action column
						if (!getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN')) {
							print '<td class="nowrap center actioncolumn">';
							if ($massactionbutton || $massaction) {
								$selected = 0;
								if (in_array($obj->id, $arrayofselected)) {
									$selected = 1;
								}
								print '<input id="cb'.$obj->id.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->id.'"'.($selected ? ' checked="checked"' : '').'>';
							}
							print '</td>';
						}
						print '</tr>';
					}
					$i++;
				}
			} else {
				print '<tr class="oddeven"><td colspan="9"><span class="opacitymedium">'.$langs->trans("None").'</span></td></tr>';
			}
			$db->free($result);
		} else {
			dol_print_error($db);
		}

		//projects linked to that thirdpart because of a people of that company is linked to a project
		if (getDolGlobalString('PROJECT_DISPLAY_LINKED_BY_CONTACT')) {
			print "\n";
			print load_fiche_titre($langs->trans("ProjectsLinkedToThisThirdParty"), '', '');


			print '<div class="div-table-responsive">'."\n";
			print '<table class="noborder centpercent">';

			$sql  = "SELECT p.rowid as id, p.entity, p.title, p.ref, p.public, p.dateo as do, p.datee as de, p.fk_statut as status, p.fk_opp_status, p.opp_amount, p.opp_percent, p.tms as date_update, p.budget_amount";
			$sql .= ", cls.code as opp_status_code";
			$sql .= " FROM ".MAIN_DB_PREFIX."projet as p";
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_lead_status as cls on p.fk_opp_status = cls.rowid";
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."element_contact as ec on p.rowid = ec.element_id";
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."socpeople as sc on ec.fk_socpeople = sc.rowid";
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_type_contact as tc on ec.fk_c_type_contact = tc.rowid";
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."projet_extrafields as pe on p.rowid = pe.fk_object";
			$sql .= " AND pe.affaire = 1";
			$sql .= " WHERE sc.fk_soc = ".((int) $object->id);
			$sql .= " AND p.entity IN (".getEntity('project').")";
			$sql .= " AND tc.element = 'project' AND tc.source = 'external'";
			$sql .= " ORDER BY p.dateo DESC";

			$result = $db->query($sql);
			if ($result) {
				$num = $db->num_rows($result);

				print '<tr class="liste_titre">';
				print '<td>'.$langs->trans("Ref").'</td>';
				print '<td>'.$langs->trans("Name").'</td>';
				print '<td class="center">'.$langs->trans("DateStart").'</td>';
				print '<td class="center">'.$langs->trans("DateEnd").'</td>';
				print '<td class="right">'.$langs->trans("OpportunityAmountShort").'</td>';
				print '<td class="center">'.$langs->trans("OpportunityStatusShort").'</td>';
				print '<td class="right">'.$langs->trans("OpportunityProbabilityShort").'</td>';
				print '<td class="right">'.$langs->trans("Status").'</td>';
				print '</tr>';

				if ($num > 0) {
					require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';

					$projecttmp = new Project($db);

					$i = 0;

					while ($i < $num) {
						$obj = $db->fetch_object($result);
						$projecttmp->fetch($obj->id);

						// To verify role of users
						$userAccess = $projecttmp->restrictedProjectArea($user);

						if ($user->rights->projet->lire && $userAccess > 0) {
							print '<tr class="oddeven">';

							// Ref
							print '<td class="nowraponall">';
							print $projecttmp->getNomUrl(1, '', 0, '', '-', 0, 1, '', 'project:'.$_SERVER["PHP_SELF"].'?socid=__SOCID__');
							print '</td>';

							// Label
							print '<td class="tdoverflowmax200" title="'.dol_escape_htmltag($obj->title).'">'.dol_escape_htmltag($obj->title).'</td>';
							// Date start
							print '<td class="center">'.dol_print_date($db->jdate($obj->do), "day").'</td>';
							// Date end
							print '<td class="center">'.dol_print_date($db->jdate($obj->de), "day").'</td>';
							// Opp amount
							print '<td class="right">';
							if ($obj->opp_status_code) {
								print '<span class="amount">'.price($obj->opp_amount, 1, '', 1, -1, -1, '').'</span>';
							}
							print '</td>';
							// Opp status
							print '<td class="center">';
							if ($obj->opp_status_code) {
								print $langs->trans("OppStatus".$obj->opp_status_code);
							}
							print '</td>';
							// Opp percent
							print '<td class="right">';
							if ($obj->opp_percent) {
								print price($obj->opp_percent, 1, '', 1, 0).'%';
							}
							print '</td>';
							// Status
							print '<td class="right">'.$projecttmp->getLibStatut(5).'</td>';

							print '</tr>';
						}
						$i++;
					}
				} else {
					print '<tr class="oddeven"><td colspan="8"><span class="opacitymedium">'.$langs->trans("None").'</span></td></tr>';
				}
				$db->free($result);
			} else {
				dol_print_error($db);
			}
		}

		$parameters = array('sql' => $sql, 'function' => 'show_projects');
		$reshook = $hookmanager->executeHooks('printFieldListFooter', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		print $hookmanager->resPrint;

		print "</table>";
		print '</div>';

		print "<br>\n";
	}

	return $i;
}

/**
 * 		Show html area for list of projects
 *
 *		@param	Conf		$conf			Object conf
 * 		@param	Translate	$langs			Object langs
 * 		@param	DoliDB		$db				Database handler
 * 		@param	Object		$object			Third party object
 *      @param  string		$backtopage		Url to go once contact is created
 *      @param  int         $nocreatelink   1=Hide create project link
 *      @param	string		$morehtmlright	More html on right of title
 *      @return	int
 */
function kjr_show_contacts_affaires($conf, $langs, $db, $object, $backtopage = '', $nocreatelink = 0, $morehtmlright = '')
{
	global $user;

	$i = -1;

	if (isModEnabled('project') && $user->hasRight('projet', 'lire')) {
		$langs->load("projects");

		$newcardbutton = '';
		if (isModEnabled('project') && $user->hasRight('projet', 'creer') && empty($nocreatelink)) {
			$newcardbutton .= dolGetButtonTitle($langs->trans('AddProject'), '', 'fa fa-plus-circle', DOL_URL_ROOT.'/projet/card.php?socid='.$object->id.'&amp;action=create&amp;backtopage='.urlencode($backtopage));
		}

		print "\n";
		print load_fiche_titre($langs->trans("Affaires ayant ce contact"), $newcardbutton.$morehtmlright, '');
		print '<div class="div-table-responsive">';
		print "\n".'<table class="noborder" width=100%>';

		$sql  = 'SELECT p.rowid as id, p.entity, p.title, p.ref, p.public, p.dateo as do, p.datee as de, p.fk_statut as status, p.fk_opp_status, p.opp_amount, p.opp_percent, p.tms as date_modification, p.budget_amount';
		$sql .= ', cls.code as opp_status_code, ctc.libelle as type_label';
		$sql .= ' FROM '.MAIN_DB_PREFIX.'projet as p';
		$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_lead_status as cls on p.fk_opp_status = cls.rowid';
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."projet_extrafields as pe on p.rowid = pe.fk_object";
		$sql .= ' INNER JOIN '.MAIN_DB_PREFIX.'element_contact as cc ON (p.rowid = cc.element_id)';
		$sql .= ' INNER JOIN '.MAIN_DB_PREFIX.'c_type_contact as ctc ON (ctc.rowid = cc.fk_c_type_contact)';
		$sql .= " WHERE cc.fk_socpeople = ".((int) $object->id);
		$sql .= " AND p.entity IN (".getEntity('project').")";
		$sql .= " AND pe.affaire = 1";
		$sql .= " ORDER BY p.dateo DESC";
		$result = $db->query($sql);
		if ($result) {
			$num = $db->num_rows($result);

			print '<tr class="liste_titre">';
			print '<td>'.$langs->trans("Ref").'</td>';
			print '<td>'.$langs->trans("Name").'</td>';
			print '<td>'.$langs->trans("ContactType").'</td>';
			print '<td class="right">'.$langs->trans("Status").'</td>';
			print '</tr>';

			if ($num > 0) {
				require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';

				$projecttmp = new Project($db);

				$i = 0;

				while ($i < $num) {
					$obj = $db->fetch_object($result);
					$projecttmp->fetch($obj->id);

					// To verify role of users
					$userAccess = $projecttmp->restrictedProjectArea($user);

					if ($user->hasRight('projet', 'lire') && $userAccess > 0) {
						print '<tr class="oddeven">';

						// Ref
						print '<td>';
						$iconStyle = $object->public == 1 
						? 'color: #986C6A;'
						: ($object->public == 0 
							? 'color: #6C6AA8;'
							: 'color: #6CA89C;');
		
						$url = dol_buildpath('/custom/kjraffaire/affaire/card.php', 1) . '?id=' . $obj->id . '&save_lastsearch_values=1';
		
						print '<i class="fas fa-briefcase paddingrightonly valignmiddle" style="'.$iconStyle.'"></i>';
						print '<a href="'.$url.'">'.htmlspecialchars($obj->ref).'</a>';
						print '</td>';

						// Label
						print '<td>'.dol_escape_htmltag($obj->title).'</td>';
						print '<td>'.dol_escape_htmltag($obj->type_label).'</td>';
						// Status
						print '<td class="right">'.$projecttmp->getLibStatut(5).'</td>';

						print '</tr>';
					}
					$i++;
				}
			} else {
				print '<tr class="oddeven"><td colspan="8"><span class="opacitymedium">'.$langs->trans("None").'</span></td></tr>';
			}
			$db->free($result);
		} else {
			dol_print_error($db);
		}
		print "</table>";
		print '</div>';

		print "<br>\n";
	}

	return $i;
}


/**
 * Show task lines with a particular parent
 *
 * @param	string	   	$inc				    Line number (start to 0, then increased by recursive call)
 * @param   string		$parent				    Id of parent task to show (0 to show all)
 * @param   Task[]		$lines				    Array of lines
 * @param   int			$level				    Level (start to 0, then increased/decrease by recursive call), or -1 to show all level in order of $lines without the recursive groupment feature.
 * @param 	string		$var				    Color
 * @param 	int			$showproject		    Show project columns
 * @param	int			$taskrole			    Array of roles of user for each tasks
 * @param	string		$projectsListId		    List of id of project allowed to user (string separated with comma)
 * @param	int			$addordertick		    Add a tick to move task
 * @param   int         $projectidfortotallink  0 or Id of project to use on total line (link to see all time consumed for project)
 * @param   string      $dummy					Not used.
 * @param   int         $showbilltime           Add the column 'TimeToBill' and 'TimeBilled'
 * @param   array       $arrayfields            Array with displayed column information
 * @param   array       $arrayofselected        Array with selected fields
 * @return	int									Nb of tasks shown
 */
function affaireLinesa(&$inc, $parent, &$lines, &$level, $var, $showproject, &$taskrole, $projectsListId = '', $addordertick = 0, $projectidfortotallink = 0, $dummy = '', $showbilltime = 0, $arrayfields = array(), $arrayofselected = array())
{
	global $user, $langs, $conf, $db, $hookmanager;
	global $projectstatic, $taskstatic, $extrafields;

	$lastprojectid = 0;

	$projectsArrayId = explode(',', $projectsListId);

	$numlines = count($lines);

	// We declare counter as global because we want to edit them into recursive call
	global $total_projectlinesa_spent, $total_projectlinesa_planned, $total_projectlinesa_spent_if_planned, $total_projectlinesa_declared_if_planned, $total_projectlinesa_tobill, $total_projectlinesa_billed, $total_budget_amount;
	global $totalarray;

	if ($level == 0) {
		$total_projectlinesa_spent = 0;
		$total_projectlinesa_planned = 0;
		$total_projectlinesa_spent_if_planned = 0;
		$total_projectlinesa_declared_if_planned = 0;
		$total_projectlinesa_tobill = 0;
		$total_projectlinesa_billed = 0;
		$total_budget_amount = 0;
		$totalarray = array();
	}

	for ($i = 0; $i < $numlines; $i++) {
		if ($parent == 0 && $level >= 0) {
			$level = 0; // if $level = -1, we don't use sublevel recursion, we show all lines
		}

		// Process line
		// print "i:".$i."-".$lines[$i]->fk_project.'<br>';
		if ($lines[$i]->fk_task_parent == $parent || $level < 0) {       // if $level = -1, we don't use sublevel recursion, we show all lines
			// Show task line.
			$showline = 1;
			$showlineingray = 0;

			// If there is filters to use
			if (is_array($taskrole)) {
				// If task not legitimate to show, search if a legitimate task exists later in tree
				if (!isset($taskrole[$lines[$i]->id]) && $lines[$i]->id != $lines[$i]->fk_task_parent) {
					// So search if task has a subtask legitimate to show
					$foundtaskforuserdeeper = 0;
					searchTaskInChild($foundtaskforuserdeeper, $lines[$i]->id, $lines, $taskrole);
					//print '$foundtaskforuserpeeper='.$foundtaskforuserdeeper.'<br>';
					if ($foundtaskforuserdeeper > 0) {
						$showlineingray = 1; // We will show line but in gray
					} else {
						$showline = 0; // No reason to show line
					}
				}
			} else {
				// Caller did not ask to filter on tasks of a specific user (this probably means he want also tasks of all users, into public project
				// or into all other projects if user has permission to).
				if (!$user->hasRight('projet', 'all', 'lire')) {
					// User is not allowed on this project and project is not public, so we hide line
					if (!in_array($lines[$i]->fk_project, $projectsArrayId)) {
						// Note that having a user assigned to a task into a project user has no permission on, should not be possible
						// because assignment on task can be done only on contact of project.
						// If assignment was done and after, was removed from contact of project, then we can hide the line.
						$showline = 0;
					}
				}
			}

			if ($showline) {
				// Break on a new project
				if ($parent == 0 && $lines[$i]->fk_project != $lastprojectid) {
					$var = !$var;
					$lastprojectid = $lines[$i]->fk_project;
				}

				print '<tr class="oddeven" id="row-'.$lines[$i]->id.'">'."\n";

				$projectstatic->id = $lines[$i]->fk_project;
				$projectstatic->ref = $lines[$i]->projectref;
				$projectstatic->public = $lines[$i]->public;
				$projectstatic->title = $lines[$i]->projectlabel;
				$projectstatic->usage_bill_time = $lines[$i]->usage_bill_time;
				$projectstatic->status = $lines[$i]->projectstatus;

				$taskstatic->id = $lines[$i]->id;
				$taskstatic->ref = $lines[$i]->ref;
				$taskstatic->label = (!empty($taskrole[$lines[$i]->id]) ? $langs->trans("YourRole").': '.$taskrole[$lines[$i]->id] : '');
				$taskstatic->projectstatus = $lines[$i]->projectstatus;
				$taskstatic->progress = $lines[$i]->progress;
				$taskstatic->fk_statut = $lines[$i]->status;	// deprecated
				$taskstatic->status = $lines[$i]->status;
				$taskstatic->date_start = $lines[$i]->date_start;
				$taskstatic->date_end = $lines[$i]->date_end;
				$taskstatic->datee = $lines[$i]->date_end; // deprecated
				$taskstatic->planned_workload = $lines[$i]->planned_workload;
				$taskstatic->duration_effective = $lines[$i]->duration_effective;
				$taskstatic->budget_amount = $lines[$i]->budget_amount;

				// Action column
				if (getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN')) {
					print '<td class="nowrap center">';
					$selected = 0;
					if (in_array($lines[$i]->id, $arrayofselected)) {
						$selected = 1;
					}
					print '<input id="cb' . $lines[$i]->id . '" class="flat checkforselect" type="checkbox" name="toselect[]" value="' . $lines[$i]->id . '"' . ($selected ? ' checked="checked"' : '') . '>';
					print '</td>';
				}

				if ($showproject) {
					// Project ref
					print '<td class="nowraponall">';
					//if ($showlineingray) print '<i>';
					if ($lines[$i]->public || in_array($lines[$i]->fk_project, $projectsArrayId) || $user->hasRight('projet', 'all', 'lire')) {
						print $projectstatic->getNomUrl(1);
					} else {
						print $projectstatic->getNomUrl(1, 'nolink');
					}
					//if ($showlineingray) print '</i>';
					print "</td>";

					// Project status
					print '<td>';
					$projectstatic->statut = $lines[$i]->projectstatus;
					print $projectstatic->getLibStatut(2);
					print "</td>";
				}

				// Ref of task
				if (count($arrayfields) > 0 && !empty($arrayfields['t.ref']['checked'])) {
					print '<td class="nowraponall">';
					if ($showlineingray) {
						print '<i>'.img_object('', 'projecttask').' '.$lines[$i]->ref.'</i>';
					} else {
						//print $taskstatic->getNomUrl(1, 'withproject');
						print '<a href="'.DOL_URL_ROOT.'/custom/kjraffaire/tache/task.php?id='.$lines[$i]->id.'&withproject=0">'.$lines[$i]->ref.'</a>';
					}
					print '</td>';
				}

				// Title of task
				if (count($arrayfields) > 0 && !empty($arrayfields['t.label']['checked'])) {
					$labeltoshow = '';
					if ($showlineingray) {
						$labeltoshow .= '<i>';
					}
					//else print '<a href="'.DOL_URL_ROOT.'/projet/tasks/task.php?id='.$lines[$i]->id.'&withproject=1">';
					for ($k = 0; $k < $level; $k++) {
						$labeltoshow .= '<div class="marginleftonly">';
					}
					$labeltoshow .= dol_escape_htmltag($lines[$i]->label);
					for ($k = 0; $k < $level; $k++) {
						$labeltoshow .= '</div>';
					}
					if ($showlineingray) {
						$labeltoshow .= '</i>';
					}
					print '<td class="tdoverflowmax200" title="'.dol_escape_htmltag($labeltoshow).'">';
					print $labeltoshow;
					print "</td>\n";
				}

				if (count($arrayfields) > 0 && !empty($arrayfields['t.description']['checked'])) {
					print '<td class="tdoverflowmax200" title="'.dol_escape_htmltag($lines[$i]->description).'">';
					print $lines[$i]->description;
					print "</td>\n";
				}

				// Date start
				if (count($arrayfields) > 0 && !empty($arrayfields['t.dateo']['checked'])) {
					print '<td class="center nowraponall">';
					print dol_print_date($lines[$i]->date_start, 'dayhour');
					print '</td>';
				}

				// Date end
				if (count($arrayfields) > 0 && !empty($arrayfields['t.datee']['checked'])) {
					print '<td class="center nowraponall">';
					print dol_print_date($lines[$i]->date_end, 'dayhour');
					if ($taskstatic->hasDelay()) {
						print img_warning($langs->trans("Late"));
					}
					print '</td>';
				}

				$plannedworkloadoutputformat = 'allhourmin';
				$timespentoutputformat = 'allhourmin';
				if (getDolGlobalString('PROJECT_PLANNED_WORKLOAD_FORMAT')) {
					$plannedworkloadoutputformat = getDolGlobalString('PROJECT_PLANNED_WORKLOAD_FORMAT');
				}
				if (getDolGlobalString('PROJECT_TIMES_SPENT_FORMAT')) {
					$timespentoutputformat = getDolGlobalString('PROJECT_TIME_SPENT_FORMAT');
				}

				// Planned Workload (in working hours)
				if (count($arrayfields) > 0 && !empty($arrayfields['t.planned_workload']['checked'])) {
					print '<td class="right">';
					$fullhour = convertSecondToTime($lines[$i]->planned_workload, $plannedworkloadoutputformat);
					$workingdelay = convertSecondToTime($lines[$i]->planned_workload, 'all', 86400, 7); // TODO Replace 86400 and 7 to take account working hours per day and working day per weeks
					if ($lines[$i]->planned_workload != '') {
						print $fullhour;
						// TODO Add delay taking account of working hours per day and working day per week
						//if ($workingdelay != $fullhour) print '<br>('.$workingdelay.')';
					}
					//else print '--:--';
					print '</td>';
				}

				// Time spent
				if (count($arrayfields) > 0 && !empty($arrayfields['t.duration_effective']['checked'])) {
					print '<td class="right">';
					if ($showlineingray) {
						print '<i>';
					} else {
						print '<a href="'.DOL_URL_ROOT.'/custom/kjraffaire/tache/time.php?id='.$lines[$i]->id.($showproject ? '' : '&withproject=1').'">';
					}
					if ($lines[$i]->duration_effective) {
						print convertSecondToTime($lines[$i]->duration_effective, $timespentoutputformat);
					} else {
						print '--:--';
					}
					if ($showlineingray) {
						print '</i>';
					} else {
						print '</a>';
					}
					print '</td>';
				}

				// Progress calculated (Note: ->duration_effective is time spent)
				if (count($arrayfields) > 0 && !empty($arrayfields['t.progress_calculated']['checked'])) {
					$s = '';
					$shtml = '';
					if ($lines[$i]->planned_workload || $lines[$i]->duration_effective) {
						if ($lines[$i]->planned_workload) {
							$s = round(100 * (float) $lines[$i]->duration_effective / (float) $lines[$i]->planned_workload, 2).' %';
							$shtml = $s;
						} else {
							$s = $langs->trans('WorkloadNotDefined');
							$shtml = '<span class="opacitymedium">'.$s.'</span>';
						}
					}
					print '<td class="right tdoverflowmax100" title="'.dol_escape_htmltag($s).'">';
					print $shtml;
					print '</td>';
				}

				// Progress declared
				if (count($arrayfields) > 0 && !empty($arrayfields['t.progress']['checked'])) {
					print '<td class="right">';
					if ($lines[$i]->progress != '') {
						print getTaskProgressBadge($taskstatic);
					}
					print '</td>';
				}

				// resume
				if (count($arrayfields) > 0 && !empty($arrayfields['t.progress_summary']['checked'])) {
					print '<td class="right">';
					if ($lines[$i]->progress != '' && $lines[$i]->duration_effective) {
						print getTaskProgressView($taskstatic, false, false);
					}
					print '</td>';
				}

				if ($showbilltime) {
					// Time not billed
					if (count($arrayfields) > 0 && !empty($arrayfields['t.tobill']['checked'])) {
						print '<td class="right">';
						if ($lines[$i]->usage_bill_time) {
							print convertSecondToTime($lines[$i]->tobill, 'allhourmin');
							$total_projectlinesa_tobill += $lines[$i]->tobill;
						} else {
							print '<span class="opacitymedium">'.$langs->trans("NA").'</span>';
						}
						print '</td>';
					}

					// Time billed
					if (count($arrayfields) > 0 && !empty($arrayfields['t.billed']['checked'])) {
						print '<td class="right">';
						if ($lines[$i]->usage_bill_time) {
							print convertSecondToTime($lines[$i]->billed, 'allhourmin');
							$total_projectlinesa_billed += $lines[$i]->billed;
						} else {
							print '<span class="opacitymedium">'.$langs->trans("NA").'</span>';
						}
						print '</td>';
					}
				}

				// Budget task
				if (count($arrayfields) > 0 && !empty($arrayfields['t.budget_amount']['checked'])) {
					print '<td class="center">';
					if ($lines[$i]->budget_amount) {
						print '<span class="amount">'.price($lines[$i]->budget_amount, 0, $langs, 1, 0, 0, $conf->currency).'</span>';
						$total_budget_amount += $lines[$i]->budget_amount;
					}
					print '</td>';
				}

				// Contacts of task
				if (count($arrayfields) > 0 && !empty($arrayfields['c.assigned']['checked'])) {
					print '<td class="center">';
					$ifisrt = 1;
					foreach (array('internal', 'external') as $source) {
						//$tab = $lines[$i]->liste_contact(-1, $source);
						$tab = $lines[$i]->liste_contact(-1, $source, 0, '', 1);

						$numcontact = count($tab);
						if (!empty($numcontact)) {
							foreach ($tab as $contacttask) {
								//var_dump($contacttask);
								if ($source == 'internal') {
									$c = new User($db);
								} else {
									$c = new Contact($db);
								}
								$c->fetch($contacttask['id']);
								if (!empty($c->photo)) {
									if (get_class($c) == 'User') {
										print $c->getNomUrl(-2, '', 0, 0, 24, 1, '', ($ifisrt ? '' : 'notfirst'));
									} else {
										print $c->getNomUrl(-2, '', 0, '', -1, 0, ($ifisrt ? '' : 'notfirst'));
									}
								} else {
									if (get_class($c) == 'User') {
										print $c->getNomUrl(2, '', 0, 0, 24, 1, '', ($ifisrt ? '' : 'notfirst'));
									} else {
										print $c->getNomUrl(2, '', 0, '', -1, 0, ($ifisrt ? '' : 'notfirst'));
									}
								}
								$ifisrt = 0;
							}
						}
					}
					print '</td>';
				}

				// Extra fields
				$extrafieldsobjectkey = $taskstatic->table_element;
				$extrafieldsobjectprefix = 'efpt.';
				$obj = $lines[$i];
				include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_print_fields.tpl.php';
				// Fields from hook
				$parameters = array('arrayfields' => $arrayfields, 'obj' => $lines[$i]);
				$reshook = $hookmanager->executeHooks('printFieldListValue', $parameters); // Note that $action and $object may have been modified by hook
				print $hookmanager->resPrint;

				// Tick to drag and drop
				print '<td class="tdlineupdown center"></td>';

				// Action column
				if (!getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN')) {
					print '<td class="nowrap center">';
					$selected = 0;
					if (in_array($lines[$i]->id, $arrayofselected)) {
						$selected = 1;
					}
					print '<input id="cb' . $lines[$i]->id . '" class="flat checkforselect" type="checkbox" name="toselect[]" value="' . $lines[$i]->id . '"' . ($selected ? ' checked="checked"' : '') . '>';

					print '</td>';
				}

				print "</tr>\n";

				if (!$showlineingray) {
					$inc++;
				}

				if ($level >= 0) {    // Call sublevels
					$level++;
					if ($lines[$i]->id) {
						projectLinesa($inc, $lines[$i]->id, $lines, $level, $var, $showproject, $taskrole, $projectsListId, $addordertick, $projectidfortotallink, '', $showbilltime, $arrayfields);
					}
					$level--;
				}

				$total_projectlinesa_spent += $lines[$i]->duration_effective;
				$total_projectlinesa_planned += $lines[$i]->planned_workload;
				if ($lines[$i]->planned_workload) {
					$total_projectlinesa_spent_if_planned += $lines[$i]->duration_effective;
				}
				if ($lines[$i]->planned_workload) {
					$total_projectlinesa_declared_if_planned += (float) $lines[$i]->planned_workload * $lines[$i]->progress / 100;
				}
			}
		} else {
			//$level--;
		}
	}

	// Total line
	if (($total_projectlinesa_planned > 0 || $total_projectlinesa_spent > 0 || $total_projectlinesa_tobill > 0 || $total_projectlinesa_billed > 0 || $total_budget_amount > 0)
		&& $level <= 0) {
		print '<tr class="liste_total nodrag nodrop">';

		if (getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN')) {
			print '<td class="liste_total"></td>';
		}

		print '<td class="liste_total">'.$langs->trans("Total").'</td>';
		if ($showproject) {
			print '<td></td><td></td>';
		}
		if (count($arrayfields) > 0 && !empty($arrayfields['t.label']['checked'])) {
			print '<td></td>';
		}
		if (count($arrayfields) > 0 && !empty($arrayfields['t.description']['checked'])) {
			print '<td></td>';
		}
		if (count($arrayfields) > 0 && !empty($arrayfields['t.dateo']['checked'])) {
			print '<td></td>';
		}
		if (count($arrayfields) > 0 && !empty($arrayfields['t.datee']['checked'])) {
			print '<td></td>';
		}
		if (count($arrayfields) > 0 && !empty($arrayfields['t.planned_workload']['checked'])) {
			print '<td class="nowrap liste_total right">';
			print convertSecondToTime($total_projectlinesa_planned, 'allhourmin');
			print '</td>';
		}
		if (count($arrayfields) > 0 && !empty($arrayfields['t.duration_effective']['checked'])) {
			print '<td class="nowrap liste_total right">';
			if ($projectidfortotallink > 0) {
				print '<a href="'.DOL_URL_ROOT.'/projet/tasks/time.php?projectid='.$projectidfortotallink.($showproject ? '' : '&withproject=1').'">';
			}
			print convertSecondToTime($total_projectlinesa_spent, 'allhourmin');
			if ($projectidfortotallink > 0) {
				print '</a>';
			}
			print '</td>';
		}

		if ($total_projectlinesa_planned) {
			$totalAverageDeclaredProgress = round(100 * $total_projectlinesa_declared_if_planned / $total_projectlinesa_planned, 2);
			$totalCalculatedProgress = round(100 * $total_projectlinesa_spent / $total_projectlinesa_planned, 2);

			// this conf is actually hidden, by default we use 10% for "be careful or warning"
			$warningRatio = getDolGlobalString('PROJECT_TIME_SPEND_WARNING_PERCENT') ? (1 + $conf->global->PROJECT_TIME_SPEND_WARNING_PERCENT / 100) : 1.10;

			// define progress color according to time spend vs workload
			$progressBarClass = 'progress-bar-info';
			$badgeClass = 'badge ';

			if ($totalCalculatedProgress > $totalAverageDeclaredProgress) {
				$progressBarClass = 'progress-bar-danger';
				$badgeClass .= 'badge-danger';
			} elseif ($totalCalculatedProgress * $warningRatio >= $totalAverageDeclaredProgress) { // warning if close at 1%
				$progressBarClass = 'progress-bar-warning';
				$badgeClass .= 'badge-warning';
			} else {
				$progressBarClass = 'progress-bar-success';
				$badgeClass .= 'badge-success';
			}
		}

		// Computed progress
		if (count($arrayfields) > 0 && !empty($arrayfields['t.progress_calculated']['checked'])) {
			print '<td class="nowrap liste_total right">';
			if ($total_projectlinesa_planned) {
				print $totalCalculatedProgress.' %';
			}
			print '</td>';
		}

		// Declared progress
		if (count($arrayfields) > 0 && !empty($arrayfields['t.progress']['checked'])) {
			print '<td class="nowrap liste_total right">';
			if ($total_projectlinesa_planned) {
				print '<span class="'.$badgeClass.'" >'.$totalAverageDeclaredProgress.' %</span>';
			}
			print '</td>';
		}


		// Progress
		if (count($arrayfields) > 0 && !empty($arrayfields['t.progress_summary']['checked'])) {
			print '<td class="right">';
			if ($total_projectlinesa_planned) {
				print '</span>';
				print '    <div class="progress sm" title="'.$totalAverageDeclaredProgress.'%" >';
				print '        <div class="progress-bar '.$progressBarClass.'" style="width: '.$totalAverageDeclaredProgress.'%"></div>';
				print '    </div>';
				print '</div>';
			}
			print '</td>';
		}

		if ($showbilltime) {
			if (count($arrayfields) > 0 && !empty($arrayfields['t.tobill']['checked'])) {
				print '<td class="nowrap liste_total right">';
				print convertSecondToTime($total_projectlinesa_tobill, 'allhourmin');
				print '</td>';
			}
			if (count($arrayfields) > 0 && !empty($arrayfields['t.billed']['checked'])) {
				print '<td class="nowrap liste_total right">';
				print convertSecondToTime($total_projectlinesa_billed, 'allhourmin');
				print '</td>';
			}
		}

		// Budget task
		if (count($arrayfields) > 0 && !empty($arrayfields['t.budget_amount']['checked'])) {
			print '<td class="nowrap liste_total center">';
			if (strcmp((string) $total_budget_amount, '')) {
				print price($total_budget_amount, 0, $langs, 1, 0, 0, $conf->currency);
			}
			print '</td>';
		}

		// Contacts of task for backward compatibility,
		if (getDolGlobalString('PROJECT_SHOW_CONTACTS_IN_LIST')) {
			print '<td></td>';
		}
		// Contacts of task
		if (count($arrayfields) > 0 && !empty($arrayfields['c.assigned']['checked'])) {
			print '<td></td>';
		}

		// Check if Extrafields is totalizable
		if (!empty($extrafields->attributes['projet_task']['totalizable'])) {
			foreach ($extrafields->attributes['projet_task']['totalizable'] as $key => $value) {
				if (!empty($arrayfields['efpt.'.$key]['checked']) && $arrayfields['efpt.'.$key]['checked'] == 1) {
					print '<td class="right">';
					if ($value == 1) {
						print empty($totalarray['totalizable'][$key]['total']) ? '' : $totalarray['totalizable'][$key]['total'];
					}
					print '</td>';
				}
			}
		}

		// Column for the drag and drop
		print '<td class="liste_total"></td>';

		if (!getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN')) {
			print '<td class="liste_total"></td>';
		}

		print '</tr>';
	}

	return $inc;
}

/**
 * Return HTML table with list of projects and number of opened tasks
 *
 * @param	DoliDB	$db					Database handler
 * @param	Form	$form				Object form
 * @param   int		$socid				Id thirdparty
 * @param   int		$projectsListId     Id of project I have permission on
 * @param   int		$mytasks            Limited to task I am contact to
 * @param	int		$status				-1=No filter on statut, 0 or 1 = Filter on status
 * @param	array	$listofoppstatus	List of opportunity status
 * @param   array   $hiddenfields       List of info to not show ('projectlabel', 'declaredprogress', '...', )
 * @param	int		$max				Max nb of record to show in HTML list
 * @return	void
 */
function print_projecttasks_array_horsaffaires($db, $form, $socid, $projectsListId, $mytasks = 0, $status = -1, $listofoppstatus = array(), $hiddenfields = array(), $max = 0)
{
	global $langs, $conf, $user;
	global $theme_datacolor;

	$maxofloop = (!getDolGlobalString('MAIN_MAXLIST_OVERLOAD') ? 500 : $conf->global->MAIN_MAXLIST_OVERLOAD);

	require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';

	$listofstatus = array_keys($listofoppstatus);

	if (is_array($listofstatus) && getDolGlobalString('USE_COLOR_FOR_PROSPECTION_STATUS')) {
		// Define $themeColorId and array $statusOppList for each $listofstatus
		$themeColorId = 0;
		$statusOppList = array();
		foreach ($listofstatus as $oppStatus) {
			$oppStatusCode = dol_getIdFromCode($db, $oppStatus, 'c_lead_status', 'rowid', 'code');
			if ($oppStatusCode) {
				$statusOppList[$oppStatus]['code'] = $oppStatusCode;
				$statusOppList[$oppStatus]['color'] = isset($theme_datacolor[$themeColorId]) ? implode(', ', $theme_datacolor[$themeColorId]) : '';
			}
			$themeColorId++;
		}
	}

	$projectstatic = new Project($db);
	$thirdpartystatic = new Societe($db);

	$sortfield = '';
	$sortorder = '';
	$project_year_filter = 0;

	$title = $langs->trans("Projects");
	if (strcmp((string) $status, '') && $status >= 0) {
		$title = $langs->trans("Projects").' '.$langs->trans($projectstatic->labelStatus[$status]);
	}

	print '<!-- print_projecttasks_array -->';
	print '<div class="div-table-responsive-no-min">';
	print '<table class="noborder centpercent">';

	$sql = " FROM ".MAIN_DB_PREFIX."projet as p";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."projet_extrafields as e ON e.fk_object=p.rowid";
	if ($mytasks) {
		$sql .= ", ".MAIN_DB_PREFIX."projet_task as t";
		$sql .= ", ".MAIN_DB_PREFIX."element_contact as ec";
		$sql .= ", ".MAIN_DB_PREFIX."c_type_contact as ctc";
	} else {
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."projet_task as t ON p.rowid = t.fk_projet";
	}
	$sql .= " WHERE p.entity IN (".getEntity('project').")";
	$sql .= " AND (e.affaire is null OR e.affaire=0) ";
	$sql .= " AND p.rowid IN (".$db->sanitize($projectsListId).")";
	if ($socid) {
		$sql .= "  AND (p.fk_soc IS NULL OR p.fk_soc = 0 OR p.fk_soc = ".((int) $socid).")";
	}
	if ($mytasks) {
		$sql .= " AND p.rowid = t.fk_projet";
		$sql .= " AND ec.element_id = t.rowid";
		$sql .= " AND ec.fk_socpeople = ".((int) $user->id);
		$sql .= " AND ec.fk_c_type_contact = ctc.rowid"; // Replace the 2 lines with ec.fk_c_type_contact in $arrayidtypeofcontact
		$sql .= " AND ctc.element = 'project_task'";
	}
	if ($status >= 0) {
		$sql .= " AND p.fk_statut = ".(int) $status;
	}
	if (getDolGlobalString('PROJECT_LIMIT_YEAR_RANGE')) {
		$project_year_filter = GETPOST("project_year_filter");
		//Check if empty or invalid year. Wildcard ignores the sql check
		if ($project_year_filter != "*") {
			if (empty($project_year_filter) || !ctype_digit($project_year_filter)) {
				$project_year_filter = date("Y");
			}
			$sql .= " AND (p.dateo IS NULL OR p.dateo <= ".$db->idate(dol_get_last_day($project_year_filter, 12, false)).")";
			$sql .= " AND (p.datee IS NULL OR p.datee >= ".$db->idate(dol_get_first_day($project_year_filter, 1, false)).")";
		}
	}

	// Get id of project we must show tasks
	$arrayidofprojects = array();
	$sql1 = "SELECT p.rowid as projectid";
	$sql1 .= $sql;
	$resql = $db->query($sql1);
	if ($resql) {
		$i = 0;
		$num = $db->num_rows($resql);
		while ($i < $num) {
			$objp = $db->fetch_object($resql);
			$arrayidofprojects[$objp->projectid] = $objp->projectid;
			$i++;
		}
	} else {
		dol_print_error($db);
	}
	if (empty($arrayidofprojects)) {
		$arrayidofprojects[0] = -1;
	}

	// Get list of project with calculation on tasks
	$sql2 = "SELECT p.rowid as projectid, p.ref, p.title, p.fk_soc,";
	$sql2 .= " s.rowid as socid, s.nom as socname, s.name_alias,";
	$sql2 .= " s.code_client, s.code_compta, s.client,";
	$sql2 .= " s.code_fournisseur, s.code_compta_fournisseur, s.fournisseur,";
	$sql2 .= " s.logo, s.email, s.entity,";
	$sql2 .= " p.fk_user_creat, p.public, p.fk_statut as status, p.fk_opp_status as opp_status, p.opp_percent, p.opp_amount,";
	$sql2 .= " p.dateo, p.datee,";
	$sql2 .= " COUNT(t.rowid) as nb, SUM(t.planned_workload) as planned_workload, SUM(t.planned_workload * t.progress / 100) as declared_progess_workload";
	$sql2 .= " FROM ".MAIN_DB_PREFIX."projet as p";
	$sql2 .= " LEFT JOIN ".MAIN_DB_PREFIX."projet_extrafields as e ON e.fk_object=p.rowid";
	$sql2 .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON s.rowid = p.fk_soc";
	$sql2 .= " LEFT JOIN ".MAIN_DB_PREFIX."projet_task as t ON p.rowid = t.fk_projet";
	$sql2 .= " WHERE p.rowid IN (".$db->sanitize(implode(',', $arrayidofprojects)).")";
	$sql2 .= " AND (e.affaire is null OR e.affaire=0) ";
	$sql2 .= " GROUP BY p.rowid, p.ref, p.title, p.fk_soc, s.rowid, s.nom, s.name_alias, s.code_client, s.code_compta, s.client, s.code_fournisseur, s.code_compta_fournisseur, s.fournisseur,";
	$sql2 .= " s.logo, s.email, s.entity, p.fk_user_creat, p.public, p.fk_statut, p.fk_opp_status, p.opp_percent, p.opp_amount, p.dateo, p.datee";
	$sql2 .= " ORDER BY p.title, p.ref";

	$resql = $db->query($sql2);
	if ($resql) {
		$othernb = 0;
		$total_task = 0;
		$total_opp_amount = 0;
		$ponderated_opp_amount = 0;
		$total_plannedworkload = 0;
		$total_declaredprogressworkload = 0;

		$num = $db->num_rows($resql);
		$nbofloop = min($num, (!getDolGlobalString('MAIN_MAXLIST_OVERLOAD') ? 500 : $conf->global->MAIN_MAXLIST_OVERLOAD));
		$i = 0;

		print '<tr class="liste_titre">';
		print_liste_field_titre($title.'<a href="'.DOL_URL_ROOT.'/projet/list.php?search_status='.((int) $status).'"><span class="badge marginleftonlyshort">'.$num.'</span></a>', $_SERVER["PHP_SELF"], "", "", "", "", $sortfield, $sortorder);
		print_liste_field_titre("ThirdParty", $_SERVER["PHP_SELF"], "", "", "", "", $sortfield, $sortorder);
		if (getDolGlobalString('PROJECT_USE_OPPORTUNITIES')) {
			if (!in_array('prospectionstatus', $hiddenfields)) {
				print_liste_field_titre("OpportunityStatus", "", "", "", "", 'style="max-width: 100px"', $sortfield, $sortorder, 'center ');
			}
			print_liste_field_titre($form->textwithpicto($langs->trans("Amount"), $langs->trans("OpportunityAmount").' ('.$langs->trans("Tooltip").' = '.$langs->trans("OpportunityWeightedAmount").')'), "", "", "", "", 'style="max-width: 100px"', $sortfield, $sortorder, 'right ');
			//print_liste_field_titre('OpportunityWeightedAmount', '', '', '', '', 'align="right"', $sortfield, $sortorder);
		}
		if (!getDolGlobalString('PROJECT_HIDE_TASKS')) {
			print_liste_field_titre("Tasks", "", "", "", "", 'align="right"', $sortfield, $sortorder);
			if (!in_array('plannedworkload', $hiddenfields)) {
				print_liste_field_titre("PlannedWorkload", "", "", "", "", 'style="max-width: 100px"', $sortfield, $sortorder, 'right ');
			}
			if (!in_array('declaredprogress', $hiddenfields)) {
				print_liste_field_titre("%", "", "", "", "", '', $sortfield, $sortorder, 'right ', $langs->trans("ProgressDeclared"));
			}
		}
		if (!in_array('projectstatus', $hiddenfields)) {
			print_liste_field_titre("Status", "", "", "", "", '', $sortfield, $sortorder, 'right ');
		}
		print "</tr>\n";

		while ($i < $nbofloop) {
			$objp = $db->fetch_object($resql);

			if ($max && $i >= $max) {
				$othernb++;
				$i++;
				$total_task += $objp->nb;
				$total_opp_amount += $objp->opp_amount;
				$opp_weighted_amount = $objp->opp_percent * $objp->opp_amount / 100;
				$ponderated_opp_amount += price2num($opp_weighted_amount);
				$plannedworkload = $objp->planned_workload;
				$total_plannedworkload += $plannedworkload;
				$declaredprogressworkload = $objp->declared_progess_workload;
				$total_declaredprogressworkload += $declaredprogressworkload;
				continue;
			}

			$projectstatic->id = $objp->projectid;
			$projectstatic->user_author_id = $objp->fk_user_creat;
			$projectstatic->public = $objp->public;

			// Check is user has read permission on project
			$userAccess = $projectstatic->restrictedProjectArea($user);
			if ($userAccess >= 0) {
				$projectstatic->ref = $objp->ref;
				$projectstatic->status = $objp->status;
				$projectstatic->title = $objp->title;
				$projectstatic->date_end = $db->jdate($objp->datee);
				$projectstatic->date_start = $db->jdate($objp->dateo);

				print '<tr class="oddeven">';

				print '<td class="tdoverflowmax150">';
				print $projectstatic->getNomUrl(1, '', 0, '', '-', 0, -1, 'nowraponall');
				if (!in_array('projectlabel', $hiddenfields)) {
					print '<br><span class="opacitymedium small">'.dol_escape_htmltag($objp->title).'</span>';
				}
				print '</td>';

				print '<td class="nowraponall tdoverflowmax100">';
				if ($objp->fk_soc > 0) {
					$thirdpartystatic->id = $objp->socid;
					$thirdpartystatic->name = $objp->socname;
					//$thirdpartystatic->name_alias = $objp->name_alias;
					//$thirdpartystatic->code_client = $objp->code_client;
					$thirdpartystatic->code_compta = $objp->code_compta;
					$thirdpartystatic->client = $objp->client;
					//$thirdpartystatic->code_fournisseur = $objp->code_fournisseur;
					$thirdpartystatic->code_compta_fournisseur = $objp->code_compta_fournisseur;
					$thirdpartystatic->fournisseur = $objp->fournisseur;
					$thirdpartystatic->logo = $objp->logo;
					$thirdpartystatic->email = $objp->email;
					$thirdpartystatic->entity = $objp->entity;
					print $thirdpartystatic->getNomUrl(1);
				}
				print '</td>';

				if (getDolGlobalString('PROJECT_USE_OPPORTUNITIES')) {
					if (!in_array('prospectionstatus', $hiddenfields)) {
						print '<td class="center tdoverflowmax75">';
						// Because color of prospection status has no meaning yet, it is used if hidden constant is set
						if (!getDolGlobalString('USE_COLOR_FOR_PROSPECTION_STATUS')) {
							$oppStatusCode = dol_getIdFromCode($db, $objp->opp_status, 'c_lead_status', 'rowid', 'code');
							if ($langs->trans("OppStatus".$oppStatusCode) != "OppStatus".$oppStatusCode) {
								print $langs->trans("OppStatus".$oppStatusCode);
							}
						} else {
							if (isset($statusOppList[$objp->opp_status])) {
								$oppStatusCode = $statusOppList[$objp->opp_status]['code'];
								$oppStatusColor = $statusOppList[$objp->opp_status]['color'];
							} else {
								$oppStatusCode = dol_getIdFromCode($db, $objp->opp_status, 'c_lead_status', 'rowid', 'code');
								$oppStatusColor = '';
							}
							if ($oppStatusCode) {
								if (!empty($oppStatusColor)) {
									print '<a href="'.dol_buildpath('/projet/list.php?search_opp_status='.$objp->opp_status, 1).'" style="display: inline-block; width: 4px; border: 5px solid rgb('.$oppStatusColor.'); border-radius: 2px;" title="'.$langs->trans("OppStatus".$oppStatusCode).'"></a>';
								} else {
									print '<a href="'.dol_buildpath('/projet/list.php?search_opp_status='.$objp->opp_status, 1).'" title="'.$langs->trans("OppStatus".$oppStatusCode).'">'.$oppStatusCode.'</a>';
								}
							}
						}
						print '</td>';
					}

					print '<td class="right">';
					if ($objp->opp_percent && $objp->opp_amount) {
						$opp_weighted_amount = $objp->opp_percent * $objp->opp_amount / 100;
						$alttext = $langs->trans("OpportunityWeightedAmount").' '.price($opp_weighted_amount, 0, '', 1, -1, 0, $conf->currency);
						$ponderated_opp_amount += price2num($opp_weighted_amount);
					}
					if ($objp->opp_amount) {
						print '<span class="amount" title="'.$alttext.'">'.$form->textwithpicto(price($objp->opp_amount, 0, '', 1, -1, 0), $alttext).'</span>';
					}
					print '</td>';
				}

				if (!getDolGlobalString('PROJECT_HIDE_TASKS')) {
					print '<td class="right">'.$objp->nb.'</td>';

					$plannedworkload = $objp->planned_workload;
					$total_plannedworkload += $plannedworkload;
					if (!in_array('plannedworkload', $hiddenfields)) {
						print '<td class="right nowraponall">'.($plannedworkload ? convertSecondToTime($plannedworkload) : '').'</td>';
					}
					if (!in_array('declaredprogress', $hiddenfields)) {
						$declaredprogressworkload = $objp->declared_progess_workload;
						$total_declaredprogressworkload += $declaredprogressworkload;
						print '<td class="right nowraponall">';
						//print $objp->planned_workload.'-'.$objp->declared_progess_workload."<br>";
						print($plannedworkload ? round(100 * $declaredprogressworkload / $plannedworkload, 0).'%' : '');
						print '</td>';
					}
				}

				if (!in_array('projectstatus', $hiddenfields)) {
					print '<td class="right">';
					print $projectstatic->getLibStatut(3);
					print '</td>';
				}

				print "</tr>\n";

				$total_task += $objp->nb;
				$total_opp_amount += $objp->opp_amount;
			}

			$i++;
		}

		if ($othernb) {
			print '<tr class="oddeven">';
			print '<td class="nowrap" colspan="5">';
			print '<span class="opacitymedium">'.$langs->trans("More").'...'.($othernb < $maxofloop ? ' ('.$othernb.')' : '').'</span>';
			print '</td>';
			print "</tr>\n";
		}

		print '<tr class="liste_total">';
		print '<td>'.$langs->trans("Total")."</td><td></td>";
		if (getDolGlobalString('PROJECT_USE_OPPORTUNITIES')) {
			if (!in_array('prospectionstatus', $hiddenfields)) {
				print '<td class="liste_total"></td>';
			}
			print '<td class="liste_total right">';
			//$form->textwithpicto(price($ponderated_opp_amount, 0, '', 1, -1, -1, $conf->currency), $langs->trans("OpportunityPonderatedAmountDesc"), 1);
			print $form->textwithpicto(price($total_opp_amount, 0, '', 1, -1, 0), $langs->trans("OpportunityPonderatedAmountDesc").' : '.price($ponderated_opp_amount, 0, '', 1, -1, 0, $conf->currency));
			print '</td>';
		}
		if (!getDolGlobalString('PROJECT_HIDE_TASKS')) {
			print '<td class="liste_total right">'.$total_task.'</td>';
			if (!in_array('plannedworkload', $hiddenfields)) {
				print '<td class="liste_total right">'.($total_plannedworkload ? convertSecondToTime($total_plannedworkload) : '').'</td>';
			}
			if (!in_array('declaredprogress', $hiddenfields)) {
				print '<td class="liste_total right">'.($total_plannedworkload ? round(100 * $total_declaredprogressworkload / $total_plannedworkload, 0).'%' : '').'</td>';
			}
		}
		if (!in_array('projectstatus', $hiddenfields)) {
			print '<td class="liste_total"></td>';
		}
		print '</tr>';

		$db->free($resql);
	} else {
		dol_print_error($db);
	}

	print "</table>";
	print '</div>';

	if (getDolGlobalString('PROJECT_LIMIT_YEAR_RANGE')) {
		//Add the year filter input
		print '<form method="get" action="'.$_SERVER["PHP_SELF"].'">';
		print '<table width="100%">';
		print '<tr>';
		print '<td>'.$langs->trans("Year").'</td>';
		print '<td class="right"><input type="text" size="4" class="flat" name="project_year_filter" value="'.((int) $project_year_filter).'"/>';
		print "</tr>\n";
		print '</table></form>';
	}
}
