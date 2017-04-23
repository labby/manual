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

// Get id
if(!isset($_GET['chapter_id']) OR !is_numeric($_GET['chapter_id'])) {
	header("Location: index.php");
} else {
	$chapter_id = intval($_GET['chapter_id']);
	$target_pos = intval($_GET['pos']);
}

// Include WB admin wrapper script
$update_when_modified = true; // Tells script to update when this page was last updated
require(LEPTON_PATH.'/modules/admin.php');

// Include the ordering class
#require(LEPTON_PATH.'/modules/manual/class.order.php');

// Create new order object an reorder
#$order = new order($table, 'position', $id_field, 'parent', $section_id);
$is_ok = manual_position::move_to( $chapter_id, $target_pos );

if( true === $is_ok ) {
	$admin->print_success($TEXT['SUCCESS'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
} else {
	$admin->print_error($TEXT['ERROR']."[1]", ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
}

// Print admin footer
$admin->print_footer();

?>