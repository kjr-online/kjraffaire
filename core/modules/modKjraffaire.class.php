<?php
/* Copyright (C) 2004-2018  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019  Nicolas ZABOURI         <info@inovea-conseil.com>
 * Copyright (C) 2019-2024  Frédéric France         <frederic.france@free.fr>
 * Copyright (C) 2025 Eric PICABIA
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
 * 	\defgroup   kjraffaire     Module Kjraffaire
 *  \brief      Kjraffaire module descriptor.
 *
 *  \file       htdocs/kjraffaire/core/modules/modKjraffaire.class.php
 *  \ingroup    kjraffaire
 *  \brief      Description and activation file for module Kjraffaire
 */
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';


/**
 *  Description and activation class for module Kjraffaire
 */
class modKjraffaire extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $langs, $conf;

		$this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 491060; // TODO Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve an id number for your module

		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'kjraffaire';

		// Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
		// It is used to group modules by family in module setup page
		$this->family = "other";

		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '90';

		// Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		//$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));
		// Module label (no space allowed), used if translation string 'ModuleKjraffaireName' not found (Kjraffaire is name of module).
		$this->name = preg_replace('/^mod/i', '', get_class($this));

		// DESCRIPTION_FLAG
		// Module description, used if translation string 'ModuleKjraffaireDesc' not found (Kjraffaire is name of module).
		$this->description = "KjraffaireDescription";
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = "KjraffaireDescription";

		// Author
		$this->editor_name = 'KJR Online';
		$this->editor_url = 'kjr-online.fr';		// Must be an external online web site
		$this->editor_squarred_logo = '';					// Must be image filename into the module/img directory followed with @modulename. Example: 'myimage.png@kjraffaire'

		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated', 'experimental_deprecated' or a version string like 'x.y.z'
		$this->version = '0.0.1';
		// Url to the file with your last numberversion of this module
		//$this->url_last_version = 'http://www.example.com/versionmodule.txt';

		// Key used in llx_const table to save module status enabled/disabled (where KJRAFFAIRE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		// To use a supported fa-xxx css style of font awesome, use this->picto='xxx'
		$this->picto = 'fa-briefcase';

		// Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array(
			// Set this to 1 if module has its own trigger directory (core/triggers)
			'triggers' => 1,
			// Set this to 1 if module has its own login method file (core/login)
			'login' => 0,
			// Set this to 1 if module has its own substitution function file (core/substitutions)
			'substitutions' => 0,
			// Set this to 1 if module has its own menus handler directory (core/menus)
			'menus' => 0,
			// Set this to 1 if module overwrite template dir (core/tpl)
			'tpl' => 0,
			// Set this to 1 if module has its own barcode directory (core/modules/barcode)
			'barcode' => 0,
			// Set this to 1 if module has its own models directory (core/modules/xxx)
			'models' => 0,
			// Set this to 1 if module has its own printing directory (core/modules/printing)
			'printing' => 0,
			// Set this to 1 if module has its own theme directory (theme)
			'theme' => 0,
			// Set this to relative path of css file if module has its own css file
			'css' => array(
				//    '/kjraffaire/css/kjraffaire.css.php',
			),
			// Set this to relative path of js file if module must load a js on all pages
			'js' => array(
				//   '/kjraffaire/js/kjraffaire.js.php',
			),
			// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context to 'all'
			/* BEGIN MODULEBUILDER HOOKSCONTEXTS */
			'hooks' => array(
				'all'
				//   'data' => array(
				//       'hookcontext1',
				//       'hookcontext2',
				//   ),
				//   'entity' => '0',
			),
			/* END MODULEBUILDER HOOKSCONTEXTS */
			// Set this to 1 if features of module are opened to external users
			'moduleforexternal' => 0,
			// Set this to 1 if the module provides a website template into doctemplates/websites/website_template-mytemplate
			'websitetemplates' => 0
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/kjraffaire/temp","/kjraffaire/subdir");
		$this->dirs = array("/kjraffaire/temp");

		// Config pages. Put here list of php page, stored into kjraffaire/admin directory, to use to setup module.
		$this->config_page_url = array("setup.php@kjraffaire");

		// Dependencies
		// A condition to hide module
		$this->hidden = getDolGlobalInt('MODULE_KJRAFFAIRE_DISABLED'); // A condition to disable module;
		// List of module class names that must be enabled if this module is enabled. Example: array('always'=>array('modModuleToEnable1','modModuleToEnable2'), 'FR'=>array('modModuleToEnableFR')...)
		$this->depends = array();
		// List of module class names to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
		$this->requiredby = array();
		// List of module class names this module is in conflict with. Example: array('modModuleToDisable1', ...)
		$this->conflictwith = array();

		// The language file dedicated to your module
		$this->langfiles = array("kjraffaire@kjraffaire");

		// Prerequisites
		$this->phpmin = array(7, 1); // Minimum version of PHP required by module
		$this->need_dolibarr_version = array(19, -3); // Minimum version of Dolibarr required by module
		$this->need_javascript_ajax = 0;

		// Messages at activation
		$this->warnings_activation = array(); // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
		$this->warnings_activation_ext = array(); // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
		//$this->automatic_activation = array('FR'=>'KjraffaireWasAutomaticallyActivatedBecauseOfYourCountryChoice');
		//$this->always_enabled = true;								// If true, can't be disabled

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(1 => array('KJRAFFAIRE_MYNEWCONST1', 'chaine', 'myvalue', 'This is a constant to add', 1),
		//                             2 => array('KJRAFFAIRE_MYNEWCONST2', 'chaine', 'myvalue', 'This is another constant to add', 0, 'current', 1)
		// );
		$this->const = array();

		// Some keys to add into the overwriting translation tables
		/*$this->overwrite_translation = array(
			'en_US:ParentCompany'=>'Parent company or reseller',
			'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
		)*/

		if (!isModEnabled("kjraffaire")) {
			$conf->kjraffaire = new stdClass();
			$conf->kjraffaire->enabled = 0;
		}

		// Array to add new pages in new tabs
		/* BEGIN MODULEBUILDER TABS */
		$this->tabs = array();
		/* END MODULEBUILDER TABS */
		// Example:
		// To add a new tab identified by code tabname1
		// $this->tabs[] = array('data'=>'objecttype:+tabname1:Title1:mylangfile@kjraffaire:$user->hasRight('kjraffaire', 'read'):/kjraffaire/mynewtab1.php?id=__ID__');
		// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
		// $this->tabs[] = array('data'=>'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@kjraffaire:$user->hasRight('othermodule', 'read'):/kjraffaire/mynewtab2.php?id=__ID__',
		// To remove an existing tab identified by code tabname
		// $this->tabs[] = array('data'=>'objecttype:-tabname:NU:conditiontoremove');
		//
		// Where objecttype can be
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
		// 'contact'          to add a tab in contact view
		// 'contract'         to add a tab in contract view
		// 'group'            to add a tab in group view
		// 'intervention'     to add a tab in intervention view
		// 'invoice'          to add a tab in customer invoice view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'member'           to add a tab in foundation member view
		// 'opensurveypoll'	  to add a tab in opensurvey poll view
		// 'order'            to add a tab in sale order view
		// 'order_supplier'   to add a tab in supplier order view
		// 'payment'		  to add a tab in payment view
		// 'payment_supplier' to add a tab in supplier payment view
		// 'product'          to add a tab in product view
		// 'propal'           to add a tab in propal view
		// 'project'          to add a tab in project view
		// 'stock'            to add a tab in stock view
		// 'thirdparty'       to add a tab in third party view
		// 'user'             to add a tab in user view


		// Dictionnaires
		$this->dictionaries=array(
			'langs'=>'kjraffaire@kjraffaire',
			// List of tables we want to see into dictonnary editor
			'tabname'=>array(
				"kjraffaire_dico_type_affaire",
				"kjraffaire_dico_action_juridique",
				"kjraffaire_dico_juridiction",
				"kjraffaire_dico_soustype_contact",
			),
			// Label of tables
			'tablib'=>array(
				"Type affaire",
				"Action juridique",
				"Juridiction",
				"Sous-types de contact - Affaire",
			),
			// Request to select fields
			'tabsql'=>array(
				'SELECT f.rowid as rowid, f.label, f.active FROM '.MAIN_DB_PREFIX.'kjraffaire_dico_type_affaire as f',
				'SELECT f.rowid as rowid, f.label, f.active FROM '.MAIN_DB_PREFIX.'kjraffaire_dico_action_juridique as f',
				'SELECT f.rowid as rowid, f.nom_etablissement, f.active FROM '.MAIN_DB_PREFIX.'kjraffaire_dico_juridiction as f',
				'SELECT s.rowid AS rowid, s.code, s.libelle, t.libelle AS parent_type, s.fk_type_contact, s.position, s.active FROM '.MAIN_DB_PREFIX.'kjraffaire_dico_soustype_contact s LEFT JOIN '.MAIN_DB_PREFIX.'c_type_contact t ON s.fk_type_contact = t.rowid',
		   ),
		 	// Sort order
		 	'tabsqlsort'=>array(
				"label ASC",
				"label ASC",
				"nom_etablissement ASC",
				"position ASC",
			),
		 	// List of fields (result of select to show dictionary)
		 	'tabfield'=>array(
				"label",
				"label",
				"nom_etablissement",
				"code,libelle,parent_type,position",
			),
		 	// List of fields (list of fields to edit a record)
		 	'tabfieldvalue'=>array(
				"label",
				"label",
				"nom_etablissement",
				"code,libelle,fk_type_contact,position",
			),
		 	// List of fields (list of fields for insert)
		 	'tabfieldinsert'=>array(
				"label",
				"label",
				"nom_etablissement",
				"code,libelle,fk_type_contact,position",
			),
		 	// Name of columns with primary key (try to always name it 'rowid')
		 	'tabrowid'=>array(
				"rowid",
				"rowid",
				"rowid",
				"rowid",
			),
		 	// Condition to show each dictionary
		 	'tabcond'=>array(
				isModEnabled('kjraffaire'), 
				isModEnabled('kjraffaire'),
				isModEnabled('kjraffaire'),
				isModEnabled('kjraffaire'),
			),
		 	// Tooltip for every fields of dictionaries: DO NOT PUT AN EMPTY ARRAY
		 	'tabhelp'=>array(
				array('code'=>$langs->trans('CodeTooltipHelp'), 'field2' => 'field2tooltip'),
				array('code'=>$langs->trans('CodeTooltipHelp'), 'field2' => 'field2tooltip'),
				array('code'=>$langs->trans('CodeTooltipHelp'), 'field2' => 'field2tooltip'),
				array(
					'code' => $langs->trans('CodeTooltipHelp'),
					'libelle' => $langs->trans('LibelleTooltipHelp'),
					'fk_type_contact' => $langs->trans('TypeParentTooltipHelp'),
					'position' => $langs->trans('PositionTooltipHelp'),
				),
			),
		);

		// Initialisation du dictionnaire
        if (!empty($GLOBALS['elementList'])) {
            $this->addMoreElementList($GLOBALS['elementList']);
        }
		/* Fin Dictionnaires */
		 

		// Boxes/Widgets
		// Add here list of php file(s) stored in kjraffaire/core/boxes that contains a class to show a widget.
		/* BEGIN MODULEBUILDER WIDGETS */
		$this->boxes = array(
			//  0 => array(
			//      'file' => 'kjraffairewidget1.php@kjraffaire',
			//      'note' => 'Widget provided by Kjraffaire',
			//      'enabledbydefaulton' => 'Home',
			//  ),
			//  ...
		);
		/* END MODULEBUILDER WIDGETS */

		// Cronjobs (List of cron jobs entries to add when module is enabled)
		// unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
		/* BEGIN MODULEBUILDER CRON */
		$this->cronjobs = array(
			//  0 => array(
			//      'label' => 'MyJob label',
			//      'jobtype' => 'method',
			//      'class' => '/kjraffaire/class/myobject.class.php',
			//      'objectname' => 'MyObject',
			//      'method' => 'doScheduledJob',
			//      'parameters' => '',
			//      'comment' => 'Comment',
			//      'frequency' => 2,
			//      'unitfrequency' => 3600,
			//      'status' => 0,
			//      'test' => 'isModEnabled("kjraffaire")',
			//      'priority' => 50,
			//  ),
		);
		/* END MODULEBUILDER CRON */
		// Example: $this->cronjobs=array(
		//    0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'isModEnabled("kjraffaire")', 'priority'=>50),
		//    1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>'isModEnabled("kjraffaire")', 'priority'=>50)
		// );

		// Permissions provided by this module
		$this->rights = array();
		$r = 0;
		// Add here entries to declare new permissions
		/* BEGIN MODULEBUILDER PERMISSIONS */
		/*
		$o = 1;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", ($o * 10) + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Read objects of Kjraffaire'; // Permission label
		$this->rights[$r][4] = 'myobject';
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->hasRight('kjraffaire', 'myobject', 'read'))
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", ($o * 10) + 2); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update objects of Kjraffaire'; // Permission label
		$this->rights[$r][4] = 'myobject';
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->hasRight('kjraffaire', 'myobject', 'write'))
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", ($o * 10) + 3); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete objects of Kjraffaire'; // Permission label
		$this->rights[$r][4] = 'myobject';
		$this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->hasRight('kjraffaire', 'myobject', 'delete'))
		$r++;
		*/
		/* END MODULEBUILDER PERMISSIONS */

        include DOL_DOCUMENT_ROOT.'/custom/kjraffaire/menu/menus.php';

		// Exports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER EXPORT MYOBJECT */
		/*
		$langs->load("kjraffaire@kjraffaire");
		$this->export_code[$r] = $this->rights_class.'_'.$r;
		$this->export_label[$r] = 'MyObjectLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r] = $this->picto;
		// Define $this->export_fields_array, $this->export_TypeFields_array and $this->export_entities_array
		$keyforclass = 'MyObject'; $keyforclassfile='/kjraffaire/class/myobject.class.php'; $keyforelement='myobject@kjraffaire';
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		//$this->export_fields_array[$r]['t.fieldtoadd']='FieldToAdd'; $this->export_TypeFields_array[$r]['t.fieldtoadd']='Text';
		//unset($this->export_fields_array[$r]['t.fieldtoremove']);
		//$keyforclass = 'MyObjectLine'; $keyforclassfile='/kjraffaire/class/myobject.class.php'; $keyforelement='myobjectline@kjraffaire'; $keyforalias='tl';
		//include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		$keyforselect='myobject'; $keyforaliasextra='extra'; $keyforelement='myobject@kjraffaire';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$keyforselect='myobjectline'; $keyforaliasextra='extraline'; $keyforelement='myobjectline@kjraffaire';
		//include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$this->export_dependencies_array[$r] = array('myobjectline'=>array('tl.rowid','tl.ref')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		//$this->export_special_array[$r] = array('t.field'=>'...');
		//$this->export_examplevalues_array[$r] = array('t.field'=>'Example');
		//$this->export_help_array[$r] = array('t.field'=>'FieldDescHelp');
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'kjraffaire_myobject as t';
		//$this->export_sql_end[$r]  .=' LEFT JOIN '.MAIN_DB_PREFIX.'kjraffaire_myobject_line as tl ON tl.fk_myobject = t.rowid';
		$this->export_sql_end[$r] .=' WHERE 1 = 1';
		$this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('myobject').')';
		$r++; */
		/* END MODULEBUILDER EXPORT MYOBJECT */

		// Imports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER IMPORT MYOBJECT */
		/*
		$langs->load("kjraffaire@kjraffaire");
		$this->import_code[$r] = $this->rights_class.'_'.$r;
		$this->import_label[$r] = 'MyObjectLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->import_icon[$r] = $this->picto;
		$this->import_tables_array[$r] = array('t' => MAIN_DB_PREFIX.'kjraffaire_myobject', 'extra' => MAIN_DB_PREFIX.'kjraffaire_myobject_extrafields');
		$this->import_tables_creator_array[$r] = array('t' => 'fk_user_author'); // Fields to store import user id
		$import_sample = array();
		$keyforclass = 'MyObject'; $keyforclassfile='/kjraffaire/class/myobject.class.php'; $keyforelement='myobject@kjraffaire';
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinimport.inc.php';
		$import_extrafield_sample = array();
		$keyforselect='myobject'; $keyforaliasextra='extra'; $keyforelement='myobject@kjraffaire';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinimport.inc.php';
		$this->import_fieldshidden_array[$r] = array('extra.fk_object' => 'lastrowid-'.MAIN_DB_PREFIX.'kjraffaire_myobject');
		$this->import_regex_array[$r] = array();
		$this->import_examplevalues_array[$r] = array_merge($import_sample, $import_extrafield_sample);
		$this->import_updatekeys_array[$r] = array('t.ref' => 'Ref');
		$this->import_convertvalue_array[$r] = array(
			't.ref' => array(
				'rule'=>'getrefifauto',
				'class'=>(!getDolGlobalString('KJRAFFAIRE_MYOBJECT_ADDON') ? 'mod_myobject_standard' : getDolGlobalString('KJRAFFAIRE_MYOBJECT_ADDON')),
				'path'=>"/core/modules/kjraffaire/".(!getDolGlobalString('KJRAFFAIRE_MYOBJECT_ADDON') ? 'mod_myobject_standard' : getDolGlobalString('KJRAFFAIRE_MYOBJECT_ADDON')).'.php',
				'classobject'=>'MyObject',
				'pathobject'=>'/kjraffaire/class/myobject.class.php',
			),
			't.fk_soc' => array('rule' => 'fetchidfromref', 'file' => '/societe/class/societe.class.php', 'class' => 'Societe', 'method' => 'fetch', 'element' => 'ThirdParty'),
			't.fk_user_valid' => array('rule' => 'fetchidfromref', 'file' => '/user/class/user.class.php', 'class' => 'User', 'method' => 'fetch', 'element' => 'user'),
			't.fk_mode_reglement' => array('rule' => 'fetchidfromcodeorlabel', 'file' => '/compta/paiement/class/cpaiement.class.php', 'class' => 'Cpaiement', 'method' => 'fetch', 'element' => 'cpayment'),
		);
		$this->import_run_sql_after_array[$r] = array();
		$r++; */
		/* END MODULEBUILDER IMPORT MYOBJECT */
	}

	/**
	 *  Function called when module is enabled.
	 *  The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *  It also creates data directories
	 *
	 *  @param      string  $options    Options when enabling module ('', 'noboxes')
	 *  @return     int             	1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		global $conf, $langs, $db;

		//$result = $this->_load_tables('/install/mysql/', 'kjraffaire');
		$result = $this->_load_tables('/kjraffaire/sql/');
		if ($result < 0) {
			return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')
		}

		// Create extrafields during init
		//include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		//$extrafields = new ExtraFields($this->db);
		//$result0=$extrafields->addExtraField('kjraffaire_separator1', "Separator 1", 'separator', 1,  0, 'thirdparty',   0, 0, '', array('options'=>array(1=>1)), 1, '', 1, 0, '', '', 'kjraffaire@kjraffaire', 'isModEnabled("kjraffaire")');
		//$result1=$extrafields->addExtraField('kjraffaire_myattr1', "New Attr 1 label", 'boolean', 1,  3, 'thirdparty',   0, 0, '', '', 1, '', -1, 0, '', '', 'kjraffaire@kjraffaire', 'isModEnabled("kjraffaire")');
		//$result2=$extrafields->addExtraField('kjraffaire_myattr2', "New Attr 2 label", 'varchar', 1, 10, 'project',      0, 0, '', '', 1, '', -1, 0, '', '', 'kjraffaire@kjraffaire', 'isModEnabled("kjraffaire")');
		//$result3=$extrafields->addExtraField('kjraffaire_myattr3', "New Attr 3 label", 'varchar', 1, 10, 'bank_account', 0, 0, '', '', 1, '', -1, 0, '', '', 'kjraffaire@kjraffaire', 'isModEnabled("kjraffaire")');
		//$result4=$extrafields->addExtraField('kjraffaire_myattr4', "New Attr 4 label", 'select',  1,  3, 'thirdparty',   0, 1, '', array('options'=>array('code1'=>'Val1','code2'=>'Val2','code3'=>'Val3')), 1,'', -1, 0, '', '', 'kjraffaire@kjraffaire', 'isModEnabled("kjraffaire")');
		//$result5=$extrafields->addExtraField('kjraffaire_myattr5', "New Attr 5 label", 'text',    1, 10, 'user',         0, 0, '', '', 1, '', -1, 0, '', '', 'kjraffaire@kjraffaire', 'isModEnabled("kjraffaire")');

		// Ajout des champs extras des affaires dans projet
		include_once "inc-extrafields-affaire.php";

		// Permissions
		$this->remove($options);

		
		// Activation automatique modules

		require_once DOL_DOCUMENT_ROOT.'/core/modules/modAgenda.class.php';
		require_once DOL_DOCUMENT_ROOT.'/core/modules/modProjet.class.php';
		require_once DOL_DOCUMENT_ROOT.'/core/modules/modSociete.class.php';
		require_once DOL_DOCUMENT_ROOT.'/core/modules/modFacture.class.php';
		require_once DOL_DOCUMENT_ROOT.'/core/modules/modService.class.php';

		// --- Module Agenda ---
		dolibarr_set_const($db, 'MAIN_MODULE_AGENDA', '1', 'yesno', 0, '', $conf->entity);
		$conf->global->MAIN_MODULE_AGENDA = 1;
		$agenda = new modAgenda($db);
		$agenda->init();
	
		// --- Module Projet ---
		dolibarr_set_const($db, 'MAIN_MODULE_PROJET', '1', 'yesno', 0, '', $conf->entity);
		$conf->global->MAIN_MODULE_PROJECT = 1;
		$proj = new modProjet($db);
		$proj->init();
	
		// --- Module Tiers ---
		dolibarr_set_const($db, 'MAIN_MODULE_SOCIETE', '1', 'yesno', 0, '', $conf->entity);
		$conf->global->MAIN_MODULE_SOCIETE = 1;
		$soc = new modSociete($db);
		$soc->init();
	
		// --- Module Factures / Avoirs ---
		dolibarr_set_const($db, 'MAIN_MODULE_FACTURE', '1', 'yesno', 0, '', $conf->entity);
		$conf->global->MAIN_MODULE_FACTURE = 1;
		$fact = new modFacture($db);
		$fact->init();
	
		// --- Module Services ---
		dolibarr_set_const($db, 'MAIN_MODULE_SERVICE', '1', 'yesno', 0, '', $conf->entity);
		$conf->global->MAIN_MODULE_SERVICE = 1;
		$serv = new modService($db);
		$serv->init();
		
		$sql = array();

		// Document templates
		$moduledir = dol_sanitizeFileName('kjraffaire');
		$myTmpObjects = array();
		$myTmpObjects['MyObject'] = array('includerefgeneration'=>0, 'includedocgeneration'=>0);

		foreach ($myTmpObjects as $myTmpObjectKey => $myTmpObjectArray) {
			if ($myTmpObjectKey == 'MyObject') {
				continue;
			}
			if ($myTmpObjectArray['includerefgeneration']) {
				$src = DOL_DOCUMENT_ROOT.'/install/doctemplates/'.$moduledir.'/template_myobjects.odt';
				$dirodt = DOL_DATA_ROOT.'/doctemplates/'.$moduledir;
				$dest = $dirodt.'/template_myobjects.odt';

				if (file_exists($src) && !file_exists($dest)) {
					require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
					dol_mkdir($dirodt);
					$result = dol_copy($src, $dest, 0, 0);
					if ($result < 0) {
						$langs->load("errors");
						$this->error = $langs->trans('ErrorFailToCopyFile', $src, $dest);
						return 0;
					}
				}

				$sql = array_merge($sql, array(
					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'standard_".strtolower($myTmpObjectKey)."' AND type = '".$this->db->escape(strtolower($myTmpObjectKey))."' AND entity = ".((int) $conf->entity),
					"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('standard_".strtolower($myTmpObjectKey)."', '".$this->db->escape(strtolower($myTmpObjectKey))."', ".((int) $conf->entity).")",
					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'generic_".strtolower($myTmpObjectKey)."_odt' AND type = '".$this->db->escape(strtolower($myTmpObjectKey))."' AND entity = ".((int) $conf->entity),
					"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('generic_".strtolower($myTmpObjectKey)."_odt', '".$this->db->escape(strtolower($myTmpObjectKey))."', ".((int) $conf->entity).")"
				));
			}
		}
		
		return $this->_init($sql, $options);
	}

	/**
	 *  Function called when module is disabled.
	 *  Remove from database constants, boxes and permissions from Dolibarr database.
	 *  Data directories are not deleted
	 *
	 *  @param      string	$options    Options when enabling module ('', 'noboxes')
	 *  @return     int                 1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();
		return $this->_remove($sql, $options);
	}

	public function addMoreElementList(&$elementList)
	{
		global $langs;
		$langs->load("kjraffaire@<kjraffaire>");
		$elementList['kjraffaire'] = img_picto('', 'object_generic', 'class="pictofixedwidth"') . $langs->trans('kjraffaire');
	}
}
