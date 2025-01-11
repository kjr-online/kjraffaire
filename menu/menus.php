<?php

$this->menu = array();
$r = 0;
$this->menu[$r++] = array(
    'fk_menu'=>'', 
    'type'=>'top', 
    'titre'=>'ModuleKjraffaireName',
    'prefix' => img_picto('', $this->picto, 'class="pictofixedwidth valignmiddle"'),
    'mainmenu'=>'kjraffaire',
    'leftmenu'=>'',
    'url'=>'/kjraffaire/affaire/list.php',
    'langs'=>'kjraffaire@kjraffaire', 
    'position'=>1000 + $r,
    'enabled'=>'isModEnabled("kjraffaire")', 
    'perms'=>'1', 
    'target'=>'',
    'user'=>0, 
);
/* END MODULEBUILDER TOPMENU */

/* BEGIN MODULEBUILDER LEFTMENU MYOBJECT */

$this->menu[$r++]=array(
    'fk_menu'=>'fk_mainmenu=kjraffaire',
    'type'=>'left',
    'titre'=>'Affaires',
    'prefix' => img_picto('', $this->picto, 'class="pictofixedwidth valignmiddle paddingright"'),
	'mainmenu'=>'kjraffaire',
    'leftmenu'=>'kjraffaire_index',
    'url'=>'/kjraffaire/affaire/list.php',
    'langs'=>'kjraffaire@kjraffaire',
    'position'=>1000+$r,
    'enabled'=>'isModEnabled("kjraffaire")', 
    'perms'=>'',
    'target'=>'',
    'user'=>0,
);

$this->menu[$r++]=array(
    'fk_menu'=>'fk_mainmenu=kjraffaire,fk_leftmenu=kjraffaire_index',
    'type'=>'left',
    'titre'=>'Nouvelle affaire',
    'mainmenu'=>'kjraffaire',
    'leftmenu'=>'kjraffaire_new',
    'url'=>'/kjraffaire/affaire/card.php?action=create',
    'langs'=>'kjraffaire@kjraffaire',
    'position'=>1000+$r,
    'enabled'=>'isModEnabled("kjraffaire")',
    'perms'=>'',
    'target'=>'',
    'user'=>0,
);

$this->menu[$r++]=array(
    'fk_menu'=>'fk_mainmenu=kjraffaire,fk_leftmenu=kjraffaire_index',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
    'type'=>'left',			                // This is a Left menu entry
    'titre'=>'Liste',
    'mainmenu'=>'kjraffaire',
    'leftmenu'=>'kjraffaire_list2',
    'url'=>'/kjraffaire/affaire/list.php',
    'langs'=>'kjraffaire@kjraffaire',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
    'position'=>1000+$r,
    'enabled'=>'isModEnabled("kjraffaire")', // Define condition to show or hide menu entry. Use 'isModEnabled("kjraffaire")' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
    'perms'=>'',
    'target'=>'',
    'user'=>0,				                // 0=Menu for internal users, 1=external users, 2=both
);

