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
if(!isset($_POST['chapter_id']) OR !is_numeric($_POST['chapter_id'])) {
	header("Location: ".ADMIN_URL."/pages/index.php");
} else {
	$id = $_POST['chapter_id'];
	$chapter_id = $id;
}

// Include WB admin wrapper script
$update_when_modified = true; // Tells script to update when this page was last updated
require(LEPTON_PATH.'/modules/admin.php');

require_once(LEPTON_PATH."/include/jscalendar/jscalendar-functions.php");

// Validate all fields
if($admin->get_post('title') == '') {
	$admin->print_error($MESSAGE['GENERIC']['FILL_IN_ALL'], LEPTON_URL.'/modules/manual/modify_chapter.php?page_id='.$page_id.'&section_id='.$section_id.'&chapter_id='.$id);
} else {
	$title 	= addslashes($admin->get_post('title'));
	$description = addslashes($admin->get_post('description'));
	$content =addslashes($admin->get_post('content'));	
	$parent = $admin->get_post('parent');
	$old_parent = $admin->get_post('old_parent');
	$active = $admin->get_post('active');
	$old_link = $admin->get_post('link');
	$position = $admin->get_post('position');
	$modified_when = trim( $admin->get_post('modified_when') );
	
	if( ( $modified_when == '0' ) OR ( $modified_when == '') ) {
		$modified_when = time();
	} else {
		$modified_when = jscalendar_to_timestamp($modified_when);
	}
	$modified_by = $admin->get_post('modified_by'); 
}

// Get page link URL
$page = array();
$database->execute_query(
	"SELECT `level`,`link` FROM `".TABLE_PREFIX."pages` WHERE `page_id` = ".$page_id,
	true,
	$page,
	false
);
$page_level = $page['level'];
$page_link	= $page['link'];

if($parent == "") $parent = 0;

$oManual = manual::getInstance();
$all_chapters = $oManual->get_manual_by_sectionID( $section_id );

$origin_data = $all_chapters[ $chapter_id ];
	
$temp_root = "";

if($parent > 0)
{
	$temp_parent = $parent;
	
	while($temp_parent != 0)
	{
		$temp_root = $all_chapters[ $temp_parent ]['link'].$temp_root;
		$temp_parent = $all_chapters[ $temp_parent ]['parent'];
	}

	$oManual->test_root( LEPTON_PATH.PAGES_DIRECTORY.$page_link.$temp_root ); // !
}

/**
 *	looks a little bit oversized, but we've to look also to the "old" parent root!
 *
 */
if($parent === $origin_data['parent'])
{
	$temp_root_origin = $temp_root;

} else {
	// parent has changed!
	$temp_parent_origin = $origin_data['parent'];
	$temp_root_origin = "";
	while($temp_parent_origin != 0)
	{
		$temp_root_origin = $all_chapters[ $temp_parent_origin ]['link'].$temp_root_origin;
		$temp_parent_origin = $all_chapters[ $temp_parent_origin ]['parent'];
	}

}

// Work-out what the link should be
if(function_exists("save_filename"))
{
	$temp_filename = save_filename($title);
} else {
	// backward compatible to L* < 2.4.x
	$temp_filename = page_filename($title);
}

// here we go
$full_filepath = LEPTON_PATH.PAGES_DIRECTORY.$page_link.$temp_root."/".$temp_filename.".php";

// has the filename changed?
if( ($origin_data['link'] != "/".$temp_filename) && ( $origin_data['link'] != "/" ) )
{
	$look_up = LEPTON_PATH.PAGES_DIRECTORY.$page_link.$temp_root_origin.$origin_data['link'].".php";
	if(file_exists($look_up))
	{
		rename(
			$look_up,
			$full_filepath
		);
	}
	//	also the associated directory?
	$look_up = LEPTON_PATH.PAGES_DIRECTORY.$page_link.$temp_root_origin.$origin_data['link'];
	if(file_exists($look_up))
	{
		rename(
			$look_up,
			LEPTON_PATH.PAGES_DIRECTORY.$page_link.$temp_root."/".$temp_filename
		);
	}
}

if(!file_exists($full_filepath))
{
	$pages_dir_depth = count(explode('/',$page_link.$temp_root."/".$temp_filename));
	for($i=1, $path_add=""; $i < $pages_dir_depth; $i++, $path_add .= "../"); 

$file_content = ''.
'<?php
	//	#manual 2.8.2
	$page_id = '.$page_id.';
	$section_id = '.$section_id.';
	$chapter_id = '.$chapter_id.';
	define("CHAPTER_ID", '.$chapter_id.');
	require "'.$path_add.'index.php";
?>';
	if( false === file_put_contents( $full_filepath, $file_content) )
	{
		die("Problem with: ".$full_filepath);
	}
}

// Update row
$fields = array(
	"parent"	=> $parent,
	"title"		=> $title,
	"level"		=> $level,
	"link"		=> "/".$temp_filename,	// !
	"position"	=> $position,
	"description"	=> $description,
	"content"	=> $content,
	"active"	=> $active,
	"modified_when" => time(),
	"modified_by"	=> $admin->get_user_id()
);

$database->build_and_execute(
	"update",
	TABLE_PREFIX."mod_manual_chapters",
	$fields,
	"`chapter_id` = ".$chapter_id
);

// Check if there is a db error, otherwise say successful
if($database->is_error()) {
	$admin->print_error($database->get_error(), LEPTON_URL.'/modules/manual/modify_chapter.php?page_id='.$page_id.'&section_id='.$section_id.'&chapter_id='.$id);
} else {
    $admin->print_success($TEXT['SUCCESS'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
}

// Print admin footer
$admin->print_footer();

function create_access_file2 ($filename,$page_id,$section_id,$chapter_id) {	// Write to the filename

	// The depth of the page directory in the directory hierarchy
	// '/pages' is at depth 1
	$pages_dir_depth=count(explode('/',$filename))-3;
	// Work-out how many ../'s we need to get to the index page
	$index_location = '../';
	for($i = 0; $i < $pages_dir_depth; $i++) {
		$index_location .= '../';
	}
	$file_content = ''.
'<?php
$page_id = '.$page_id.';
$section_id = '.$section_id.';
$chapter_id = '.$chapter_id.';
define("CHAPTER_ID", $chapter_id);
require "'.$index_location.'config.php";
require LEPTON_PATH."/index.php";
?>';
	$handle = fopen(LEPTON_PATH.$filename, 'w');
	if($handle)
	{
		fwrite($handle, $file_content);
		fclose($handle);
	} else {
		die("Problem with: ".$filename);
	}
}
?>