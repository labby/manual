<?php

/**
 *  @module         manual
 *  @version        see info.php of this module
 *  @authors        Ryan Djurovich, Chio Maisriml, Thomas Hornik, Dietrich Roland Pehlke
 *  @copyright      2004-2016 Ryan Djurovich, Matthias Gallas, Uffe Christoffersen, pcwacht, Rob Smith, Aldus, erpe
 *  @license        GNU General Public License
 *  @license terms  see info.php of this module
 *  @platform       see info.php of this module
 *
 */
 
 
 // include class.secure.php to protect this file and the whole CMS!
if (defined('LEPTON_PATH')) {   
   include(LEPTON_PATH.'/framework/class.secure.php');
} else {
   $oneback = "../";
   $root = $oneback;
   $level = 1;
   while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
      $root .= $oneback;
      $level += 1;
   }
   if (file_exists($root.'/framework/class.secure.php')) {
      include($root.'/framework/class.secure.php');
   } else {
      trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
   }
}
// end include class.secure.php
 
 
$module_directory	= 'manual';
$module_name		= 'Manual';
$module_function	= 'page';
$module_version		= '2.8.1';
$module_platform	= '2.x';
$module_home		= 'https://github.com/labby/manual, http://www.lepton-cms.com/lepador/modules/manual.php';
$module_guid		= '34EA748F-D867-4E57-AE3E-15F703C4046D';
$module_author		= 'Ryan Djurovich, Matthias Gallas, Uffe Christoffersen, pcwacht, Rob Smith, erpe(last)';
$module_license		= 'GNU General Public License';
$module_description	= 'This module allows you to create and manage user manuals easily';

?>