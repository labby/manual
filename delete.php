<?php

/**
 *  @module         manual
 *  @version        see info.php of this module
 *  @authors        Ryan Djurovich, Chio Maisriml, Thomas Hornik, Dietrich Roland Pehlke
 *  @copyright      2004-2017 Ryan Djurovich, Matthias Gallas, Uffe Christoffersen, pcwacht, Rob Smith, Aldus, erpe
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

// Remove chapter acces files
$query_details = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters WHERE section_id = '$section_id'");
if($query_details->numRows() > 0) {
	$get_details = $query_details->fetchRow();
	// Unlink chapter access file
	if(is_writable(LEPTON_PATH.PAGES_DIRECTORY.$get_details['link'].PAGE_EXTENSION)) {
		unlink(LEPTON_PATH.PAGES_DIRECTORY.$get_details['link'].PAGE_EXTENSION);
	}
}
// Remove chapter subdirs second level files
$query_details = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters WHERE section_id = '$section_id' AND level = 2 ");
if($query_details->numRows() > 0) {
	$get_details = $query_details->fetchRow();
	// Unlink chapter access dir
	if(is_writable(LEPTON_PATH.PAGES_DIRECTORY.$get_details['link'])) {
		rmdir(LEPTON_PATH.PAGES_DIRECTORY.$get_details['link']);
	}
}
// Remove chapter subdirs first level files
$query_details = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters WHERE section_id = '$section_id' AND level = 1 ");
if($query_details->numRows() > 0) {
	$get_details = $query_details->fetchRow();
	// Unlink chapter access dir
	if(is_writable(LEPTON_PATH.PAGES_DIRECTORY.$get_details['link'])) {
		rmdir(LEPTON_PATH.PAGES_DIRECTORY.$get_details['link']);
	}
}

// Delete records from the database
$database->query("DELETE FROM ".TABLE_PREFIX."mod_manual_settings WHERE section_id = '$section_id'");
$database->query("DELETE FROM ".TABLE_PREFIX."mod_manual_chapters WHERE section_id = '$section_id'");

?>