<?php
/* Copyright (C) 2025 Eric PICABIA
 * Copyright (C) 2024		MDW							<mdeweerd@users.noreply.github.com>
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
 * \file    kjraffaire/class/actions_kjraffaire.class.php
 * \ingroup kjraffaire
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonhookactions.class.php';

/**
 * Class ActionsKjraffaire
 */
class ActionsKjraffaire extends CommonHookActions
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 * @var array Errors
	 */
	public $errors = array();


	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var ?string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var int		Priority of hook (50 is used if value is not defined)
	 */
	public $priority;


	/**
	 * Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}


	/**
	 * Execute action
	 *
	 * @param	array			$parameters		Array of parameters
	 * @param	CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param	string			$action      	'add', 'update', 'view'
	 * @return	int         					Return integer <0 if KO,
	 *                           				=0 if OK but we want to process standard actions too,
	 *                            				>0 if OK and we want to replace standard actions.
	 */
	public function getNomUrl($parameters, &$object, &$action)
	{
		global $db, $langs, $conf, $user;
		$this->resprints = '';
		return 0;
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             Return integer < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		// Il ne faut pas pouvoir les extrafields des affaires dans les listes de projets
        global $arrayfields,$contextpage;
        $extrafields_to_exclude = [
			'ef.type_affaire',
			'ef.fk_kjraffaire_dico_juridiction',
			'ef.fk_kjraffaire_dico_action_juridique',
			'ef.chambre',
			'ef.no_role',
			'ef.fk_soc_magistrat',
			'ef.fk_socpeople_magistrat',
			'ef.section',
			'ef.date_decision',
			'ef.date_signification',
			'ef.fk_soc_avocat_postulant',
			'ef.fk_socpeople_avocat_postulant'
		];
		if (!preg_match('/kjraffaire/', $_SERVER['PHP_SELF'])){
            foreach ($extrafields_to_exclude as $field) {
                if (isset($arrayfields[$field])) {
                    unset($arrayfields[$field]); 
                }
            }
        }

		// On redirige l'onglet Projets de la fiche tiers pour ne pas afficher les affaires
		if (in_array($parameters['context'], array('projectthirdparty:main'))) {
			$url=dol_buildpath('/kjraffaire/societe/project.php',1).'?socid='. GETPOSTINT('socid');
			header("Location: " . $url);
			exit();
		}
		// On redirige l'onglet Projets de la fiche contact pour ne pas afficher les affaires
		if (in_array($parameters['context'], array('projectcontact:main'))) {
			$url=dol_buildpath('/kjraffaire/contact/project.php',1).'?id='. GETPOSTINT('id');
			header("Location: " . $url);
			exit();
		}

		if (!$error) {
			$this->results = array('myreturn' => 999);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}


	/**
	 * Overloading the doMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             Return integer < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
			foreach ($parameters['toselect'] as $objectid) {
				// Do action on each object id
			}
		}

		if (!$error) {
			$this->results = array('myreturn' => 999);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}


	/**
	 * Overloading the addMoreMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             Return integer < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function addMoreMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter
		$disabled = 1;

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
			$this->resprints = '<option value="0"'.($disabled ? ' disabled="disabled"' : '').'>'.$langs->trans("KjraffaireMassAction").'</option>';
		}

		if (!$error) {
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}



	/**
	 * Execute action
	 *
	 * @param	array	$parameters     Array of parameters
	 * @param   Object	$object		   	Object output on PDF
	 * @param   string	$action     	'add', 'update', 'view'
	 * @return  int 		        	Return integer <0 if KO,
	 *                          		=0 if OK but we want to process standard actions too,
	 *  	                            >0 if OK and we want to replace standard actions.
	 */
	public function beforePDFCreation($parameters, &$object, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0;
		$deltemp = array();
		dol_syslog(get_class($this).'::executeHooks action='.$action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
		}

		return $ret;
	}

	/**
	 * Execute action
	 *
	 * @param	array	$parameters     Array of parameters
	 * @param   Object	$pdfhandler     PDF builder handler
	 * @param   string	$action         'add', 'update', 'view'
	 * @return  int 		            Return integer <0 if KO,
	 *                                  =0 if OK but we want to process standard actions too,
	 *                                  >0 if OK and we want to replace standard actions.
	 */
	public function afterPDFCreation($parameters, &$pdfhandler, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0;
		$deltemp = array();
		dol_syslog(get_class($this).'::executeHooks action='.$action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {
			// do something only for the context 'somecontext1' or 'somecontext2'
		}

		return $ret;
	}



	/**
	 * Overloading the loadDataForCustomReports function : returns data to complete the customreport tool
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             Return integer < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function loadDataForCustomReports($parameters, &$action, $hookmanager)
	{
		global $langs;

		$langs->load("kjraffaire@kjraffaire");

		$this->results = array();

		$head = array();
		$h = 0;

		if ($parameters['tabfamily'] == 'kjraffaire') {
			$head[$h][0] = dol_buildpath('/module/index.php', 1);
			$head[$h][1] = $langs->trans("Home");
			$head[$h][2] = 'home';
			$h++;

			$this->results['title'] = $langs->trans("Kjraffaire");
			$this->results['picto'] = 'kjraffaire@kjraffaire';
		}

		$head[$h][0] = 'customreports.php?objecttype='.$parameters['objecttype'].(empty($parameters['tabfamily']) ? '' : '&tabfamily='.$parameters['tabfamily']);
		$head[$h][1] = $langs->trans("CustomReports");
		$head[$h][2] = 'customreports';

		$this->results['head'] = $head;

		$arrayoftypes = array();
		//$arrayoftypes['kjraffaire_myobject'] = array('label' => 'MyObject', 'picto'=>'myobject@kjraffaire', 'ObjectClassName' => 'MyObject', 'enabled' => isModEnabled('kjraffaire'), 'ClassPath' => "/kjraffaire/class/myobject.class.php", 'langs'=>'kjraffaire@kjraffaire')

		$this->results['arrayoftype'] = $arrayoftypes;

		return 0;
	}



	/**
	 * Overloading the restrictedArea function : check permission on an object
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int 		      			  	Return integer <0 if KO,
	 *                          				=0 if OK but we want to process standard actions too,
	 *  	                            		>0 if OK and we want to replace standard actions.
	 */
	public function restrictedArea($parameters, &$action, $hookmanager)
	{
		global $user;

		if ($parameters['features'] == 'myobject') {
			if ($user->hasRight('kjraffaire', 'myobject', 'read')) {
				$this->results['result'] = 1;
				return 1;
			} else {
				$this->results['result'] = 0;
				return 1;
			}
		}

		return 0;
	}

	/**
	 * Execute action completeTabsHead
	 *
	 * @param   array           $parameters     Array of parameters
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         'add', 'update', 'view'
	 * @param   Hookmanager     $hookmanager    hookmanager
	 * @return  int                             Return integer <0 if KO,
	 *                                          =0 if OK but we want to process standard actions too,
	 *                                          >0 if OK and we want to replace standard actions.
	 */
	public function completeTabsHead(&$parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $conf, $user, $db;
		if (!isset($parameters['object']->element)) {
			return 0;
		}
		// Si fiche société recalcul du nombre de projets du tiers
		if ($parameters['object']->element=='societe') {
			$counter = count($parameters['head'])-1;
			$i=0;
			while ($i<$counter){
				if ($parameters['head'][$i][2]=='project'){
					$nbProjets=0;
					$sql = "SELECT COUNT(n.rowid) as nb";
					$sql .= " FROM ".MAIN_DB_PREFIX."projet as n";
					$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."projet_extrafields as e ON e.fk_object=n.rowid";
					$sql .= " WHERE fk_soc = ".((int) $object->id);
					$sql .= " AND (ISNULL(e.affaire) OR e.affaire = 0)";
					$sql .= " AND entity IN (".getEntity('project').")";
					$resql = $db->query($sql);
					if ($resql) {
						$obj = $db->fetch_object($resql);
						$nbProjets = $obj->nb;
					} else {
						dol_print_error($db);
					}
					$parameters['head'][$i][1]='Projets';
					if ($nbProjets>0){
						$parameters['head'][$i][1].=' <span class=badge marginleftonlyshort">'.$nbProjets.'</span>';
					}
				}
				if ($parameters['head'][$i][2]=='affairesSociete'){
					$nbProjets=0;
					$sql = "SELECT COUNT(n.rowid) as nb";
					$sql .= " FROM ".MAIN_DB_PREFIX."projet as n";
					$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."projet_extrafields as e ON e.fk_object=n.rowid";
					$sql .= " WHERE fk_soc = ".((int) $object->id);
					$sql .= " AND e.affaire = 1";
					$sql .= " AND entity IN (".getEntity('project').")";
					$resql = $db->query($sql);
					if ($resql) {
						$obj = $db->fetch_object($resql);
						$nbProjets = $obj->nb;
					} else {
						dol_print_error($db);
					}
					$parameters['head'][$i][1]='Affaires';
					if ($nbProjets>0){
						$parameters['head'][$i][1].=' <span class=badge marginleftonlyshort">'.$nbProjets.'</span>';
					}
				}
				$i++;
			}
		}

		// Si fiche contact recalcul du nombre de projets du contact
		if ($parameters['object']->element=='contact') {
			$counter = count($parameters['head'])-1;
			$i=0;
			while ($i<$counter){
				if ($parameters['head'][$i][2]=='project'){

					$nbProjets=0;
					$sql = "SELECT COUNT(ec.rowid) as nbProjets
					FROM ".MAIN_DB_PREFIX."element_contact ec
					INNER JOIN ".MAIN_DB_PREFIX."c_type_contact tc ON ec.fk_c_type_contact = tc.rowid
					WHERE ec.fk_socpeople = ".$db->escape($object->id)."
				  	AND tc.element = 'kjraffaire'";
					$resql = $db->query($sql);
					
					if ($resql) {
						$obj = $db->fetch_object($resql);
						if ($obj) {
							$nbProjets = $obj->nbProjets;
						}
					} else {
						dol_syslog("Error SQL pendant count projets: ".$db->lasterror(), LOG_ERR);
					}		
					$parameters['head'][$i][1]='Projets';
					if ($nbProjets>0){
						$parameters['head'][$i][1].=' <span class=badge marginleftonlyshort">'.$nbProjets.'</span>';
					}
				}
				if ($parameters['head'][$i][2]=='affairesContact'){
					$nbAffaires=0;
					$sql = "SELECT COUNT(ec.rowid) as nbAffaires
					FROM ".MAIN_DB_PREFIX."element_contact ec
					INNER JOIN ".MAIN_DB_PREFIX."c_type_contact tc ON ec.fk_c_type_contact = tc.rowid
					WHERE ec.fk_socpeople = ".$db->escape($object->id)."
				  	AND tc.element = 'project'";
					$resql = $db->query($sql);
					
					if ($resql) {
						$obj = $db->fetch_object($resql);
						if ($obj) {
							$nbAffaires = $obj->nbAffaires;
						}
					} else {
						dol_syslog("Error SQL pendant count affaires: ".$db->lasterror(), LOG_ERR);
					}		
					$parameters['head'][$i][1]='Affaires';
					if ($nbProjets>0){
						$parameters['head'][$i][1].=' <span class=badge marginleftonlyshort">'.$nbAffaires.'</span>';
					}
				}
				$i++;
			}
		}
		

		if ($parameters['mode'] == 'remove') {
			// used to make some tabs removed
			return 0;
		} elseif ($parameters['mode'] == 'add') {
			$langs->load('kjraffaire@kjraffaire');
			// used when we want to add some tabs
			$counter = count($parameters['head']);
			$element = $parameters['object']->element;
			$id = $parameters['object']->id;
			// verifier le type d'onglet comme member_stats où ça ne doit pas apparaitre
			// if (in_array($element, ['societe', 'member', 'contrat', 'fichinter', 'project', 'propal', 'commande', 'facture', 'order_supplier', 'invoice_supplier'])) {
			if (in_array($element, ['context1', 'context2'])) {
				$datacount = 0;

				$parameters['head'][$counter][0] = dol_buildpath('/kjraffaire/kjraffaire_tab.php', 1) . '?id=' . $id . '&amp;module='.$element;
				$parameters['head'][$counter][1] = $langs->trans('KjraffaireTab');
				if ($datacount > 0) {
					$parameters['head'][$counter][1] .= '<span class="badge marginleftonlyshort">' . $datacount . '</span>';
				}
				$parameters['head'][$counter][2] = 'kjraffaireemails';
				$counter++;
			}
			if ($counter > 0 && (int) DOL_VERSION < 14) {
				$this->results = $parameters['head'];
				// return 1 to replace standard code
				return 1;
			} else {
				// en V14 et + $parameters['head'] est modifiable par référence
				return 0;
			}
		} else {
			// Bad value for $parameters['mode']
			return -1;
		}
	}

	/* Add here any other hooked methods... */
    function printFieldListWhere($parameters, &$object, &$action, $hookmanager)
    {
        $error = 0; // Error counter

        //print_r($parameters['context']);exit;
		if (!preg_match('/kjraffaire/', $_SERVER['PHP_SELF'])){
			if (in_array('projectlist', explode(':', $parameters['context']))) {
				//pas bon car ajoute le code JS en debut de page...utilisation de printCommonFooter()
				$this->resprints = ' AND (isnull(ef.affaire) or ef.affaire<>1) ';
				return 0;
			}	
		}
        return 0;
    }

	public function createDictionaryFieldlist($parameters, &$object, &$action, $hookmanager)
    {
        global $db, $langs;

        if ($parameters['tabname'] === 'kjraffaire_dico_soustype_contact') {
            print '<td>';
            print '<input type="text" name="code" class="flat quatrevingtpercent" maxlength="64" value="">';
            print '</td>';

            print '<td>';
            print '<input type="text" name="libelle" class="flat quatrevingtpercent" maxlength="128" value="">';
            print '</td>';

            print '<td>';
            print '<select name="fk_type_contact" class="flat">';
            print '<option value="">-- Sélectionner --</option>';

            $sql = "SELECT rowid, libelle 
                    FROM ".MAIN_DB_PREFIX."c_type_contact 
                    WHERE active = 1 AND element = 'kjraffaire' AND source = 'external'
                    ORDER BY position ASC";
            $resql = $db->query($sql);

            if ($resql) {
                while ($obj = $db->fetch_object($resql)) {
                    print '<option value="'.$obj->rowid.'">'.$obj->libelle.'</option>';
                }
            } else {
                print '<option value="">'.$langs->trans("ErrorLoadingData").'</option>';
            }

            print '</select>';
            print '</td>';

            print '<td>';
            print '<input type="number" name="position" class="flat" value="0" min="0">';
            print '<input type="hidden" name="parent_type" value="999">';
            print '</td>';

            return 1;
        }

        return 0;
    }

}
