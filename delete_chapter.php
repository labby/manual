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

// Get id
if(!isset($_GET['chapter_id']) OR !is_numeric($_GET['chapter_id'])) {
	header("Location: ".ADMIN_URL."/pages/index.php");
} else {
	$chapter_id = $_GET['chapter_id'];
}

// Include WB admin wrapper script
$update_when_modified = true; // Tells script to update when this page was last updated
require(LEPTON_PATH.'/modules/admin.php');

// Get chapter details
$query_details = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters WHERE chapter_id = '$chapter_id'");
if($query_details->numRows() > 0) {
	$get_details = $query_details->fetchRow();
	$parent = $get_details['parent'];
} else {
	$admin->print_error($TEXT['NOT_FOUND'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
}

// Unlink chapter access file
if(is_writable(LEPTON_PATH.PAGES_DIRECTORY.$get_details['link'].PAGE_EXTENSION)) {
	unlink(LEPTON_PATH.PAGES_DIRECTORY.$get_details['link'].PAGE_EXTENSION);
	if(is_writable(LEPTON_PATH.PAGES_DIRECTORY.$get_details['link'])) {
		rmdir(LEPTON_PATH.PAGES_DIRECTORY.$get_details['link']);
	}
}

// Delete chapter
$database->query("DELETE FROM ".TABLE_PREFIX."mod_manual_chapters WHERE chapter_id = '$chapter_id' LIMIT 1");

// Include the ordering class or clean-up ordering
require(LEPTON_PATH.'/modules/manual/class.order.php');
$order = new order(TABLE_PREFIX.'mod_manual_chapters', 'position', 'chapter_id', 'parent', $section_id);
$order->clean($get_details['parent']);

// Check if there is a db error, otherwise say successful
if($database->is_error()) {
	$admin->print_error($database->get_error(), LEPTON_URL.'/modules/modify_chapter.php?page_id='.$page_id.'&section_id='.$section_id.'&chapter_id='.$chapter_id);
} else {
	$admin->print_success($TEXT['SUCCESS'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
}

// Print admin footer
$admin->print_footer();

?>