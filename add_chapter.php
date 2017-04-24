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

// Include WB admin wrapper script
require(LEPTON_PATH.'/modules/admin.php');

// Include the ordering class
require(LEPTON_PATH.'/modules/manual/class.order.php');

//	Get the "new" position ... append to the last of the current page/section
$last_chapter = array();
$database->execute_query(
	"SELECT `position` FROM `".TABLE_PREFIX."mod_manual_chapters` WHERE `section_id`=".$section_id." AND `page_id`=".$page_id." AND `parent`=0 ORDER BY `position` DESC limit 1",
	true,
	$last_chapter,
	false
);

$position = (0 === count($last_chapter)) ? 1 : $last_chapter['position'] +1; 

// Insert new row into the database
$fields = array(
	'section_id'	=> $section_id,
	'page_id'	=> $page_id,
	'position'	=> $position,
	'parent'	=> 0, // as we generate a new one we've got no parent at this time!
	'active'	=> 1, // the new chapter is active by default
	'modified_when'	=> time(), // current time
	'modified_by'	=> $admin->get_user_id(),
	'link'			=> "/"	// !
);

$database->build_and_execute(
	"insert",
	TABLE_PREFIX."mod_manual_chapters",
	$fields
);

// Get the (chapter-)id for the following links
$chapter_id = $database->get_one("SELECT LAST_INSERT_ID()");

// Say that a new record has been added, then redirect to modify page
if($database->is_error()) {
	$admin->print_error($database->get_error(), LEPTON_URL.'/modules/manual/modify_chapter.php?page_id='.$page_id.'&section_id='.$section_id.'&chapter_id='.$chapter_id);
} else {
	$admin->print_success($TEXT['SUCCESS'], LEPTON_URL.'/modules/manual/modify_chapter.php?page_id='.$page_id.'&section_id='.$section_id.'&chapter_id='.$chapter_id);
}

// Print admin footer
$admin->print_footer();

?>