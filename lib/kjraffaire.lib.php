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
	$nbContacts = 0;
	// Enable caching of project count Contacts
	require_once DOL_DOCUMENT_ROOT.'/core/lib/memory.lib.php';
	$cachekey = 'count_contacts_project_'.$project->id;
	$dataretrieved = dol_getcache($cachekey);

	if (!is_null($dataretrieved)) {
		$nbContacts = $dataretrieved;
	} else {
		$nbContacts = count($project->liste_contact(-1, 'internal')) + count($project->liste_contact(-1, 'external'));
		dol_setcache($cachekey, $nbContacts, 120);	// If setting cache fails, this is not a problem, so we do not test result.
	}
	$head[$h][0] = DOL_URL_ROOT.'/custom/kjraffaire/affaire/contact.php?id='.((int) $project->id).($moreparam ? '&'.$moreparam : '');
	$head[$h][1] = $langs->trans("AffaireContact");
	if ($nbContacts > 0) {
		$head[$h][1] .= '<span class="badge marginleftonlyshort">'.$nbContacts.'</span>';
	}
	$head[$h][2] = 'contact';
	$h++;

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
		$head[$h][0] = DOL_URL_ROOT.'/projet/tasks.php?id='.((int) $project->id).($moreparam ? '&'.$moreparam : '');
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

		$head[$h][0] = DOL_URL_ROOT.'/projet/tasks/time.php?withproject=1&projectid='.((int) $project->id).($moreparam ? '&'.$moreparam : '');
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
		$head[$h][0] = DOL_URL_ROOT.'/projet/element.php?id='.$project->id;
		$head[$h][1] = $langs->trans("ProjectOverview");
		if ($nbElements > 0) {
			$head[$h][1] .= '<span class="badge marginleftonlyshort">'.$nbElements.'</span>';
		}
		$head[$h][2] = 'element';
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
		$head[$h][0] = DOL_URL_ROOT.'/projet/note.php?id='.$project->id;
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
	$head[$h][0] = DOL_URL_ROOT.'/projet/document.php?id='.$project->id;
	$head[$h][1] = $langs->trans('Documents');
	if (($totalAttached) > 0) {
		$head[$h][1] .= '<span class="badge marginleftonlyshort">'.($totalAttached).'</span>';
	}
	$head[$h][2] = 'document';
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

	$head[$h][0] = DOL_URL_ROOT.'/projet/messaging.php?id='.$project->id;
	$head[$h][1] = $langs->trans("Events");
	if (isModEnabled('agenda') && ($user->hasRight('agenda', 'myactions', 'read') || $user->hasRight('agenda', 'allactions', 'read'))) {
		$head[$h][1] .= '/';
		$head[$h][1] .= $langs->trans("Agenda");
	}
	$head[$h][2] = 'agenda';
	$h++;

	complete_head_from_modules($conf, $langs, $project, $head, $h, 'project', 'add', 'external');

	complete_head_from_modules($conf, $langs, $project, $head, $h, 'project', 'remove');

	return $head;
}