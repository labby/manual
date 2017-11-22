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
	header("Location: ".ADMIN_URL."/pages/index.php");
} else {
	$chapter_id = $_GET['chapter_id'];
}

// Include WB admin wrapper script
$update_when_modified = true; // Tells script to update when this page was last updated
require(LEPTON_PATH.'/modules/admin.php');


$oManual = manual::getInstance();
$all_chapters = $oManual->get_manual_by_sectionID( $section_id );
// die(LEPTON_tools::display($all_chapters));

// Get current values for this chapter-id
if( !isset($all_chapters[ $chapter_id ]))
{
	$admin->print_error($TEXT['NOT_FOUND'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
} else {
	$actual_chapter_content = $all_chapters[ $chapter_id ];
}

// Unlink chapter access file
$root_page_link = $database->get_one("SELECT `link` FROM `".TABLE_PREFIX."pages` WHERE `page_id`=".$page_id);
$root = $oManual->get_root( $all_chapters, $chapter_id);
$full_path = LEPTON_PATH.PAGES_DIRECTORY.$root_page_link.$root.".php";

if(file_exists( $full_path ))
{
	unlink($full_path);
}

// Related folder?
$full_path = LEPTON_PATH.PAGES_DIRECTORY.$root_page_link.$root."/";
if(file_exists($full_path))
{
	LEPTON_handle::register("rm_full_dir");
	rm_full_dir( $full_path );
}

//	get the "childs"
$all_childs = array();

function manual_get_childs( &$allChildsStorage, $aID )
{
	global $all_chapters;
	
	foreach($all_chapters as $key=>$ref)
	{
		if($key === $aID) continue;
		
		if( $ref['parent']	=== $aID)
		{
			$allChildsStorage[] = $ref['chapter_id'];
			
			manual_get_childs( $allChildsStorage, $ref['chapter_id'] );
		}
	}
}

manual_get_childs( $all_childs, $chapter_id ); 

foreach( $all_childs as $temp_id )
{
	$database->simple_query(
		"DELETE FROM `".TABLE_PREFIX."mod_manual_chapters` WHERE `chapter_id` = ? LIMIT 1",
		array( $temp_id )
	);
}

// Delete chapter entry in the database
$database->simple_query(
	"DELETE FROM `".TABLE_PREFIX."mod_manual_chapters` WHERE `chapter_id` = ? LIMIT 1",
	array( $chapter_id )
);

manual_position::rearrange( $section_id, 0 );

// Check if there is a db error, otherwise say successful
if($database->is_error()) {
	$admin->print_error($database->get_error(), LEPTON_URL.'/modules/modify_chapter.php?page_id='.$page_id.'&section_id='.$section_id.'&chapter_id='.$chapter_id);
} else {
	$admin->print_success($TEXT['SUCCESS'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
}

// Print admin footer
$admin->print_footer();

?>