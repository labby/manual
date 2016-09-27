<?php

/*

 Website Baker Project <http://www.websitebaker.org/>
 Copyright (C) 2004-2006, Ryan Djurovich

 Website Baker is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Website Baker is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Website Baker; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

require('../../config.php');

// Get id
if(!isset($_POST['chapter_id']) OR !is_numeric($_POST['chapter_id'])) {
	header("Location: ".ADMIN_URL."/pages/index.php");
} else {
	$id = $_POST['chapter_id'];
	$chapter_id = $id;
}

// Include WB admin wrapper script
$update_when_modified = true; // Tells script to update when this page was last updated
require(WB_PATH.'/modules/admin.php');

require_once(WB_PATH."/include/jscalendar/jscalendar-functions.php");

// Validate all fields
if($admin->get_post('title') == '') {
	$admin->print_error($MESSAGE['GENERIC']['FILL_IN_ALL'], WB_URL.'/modules/manual/modify_chapter.php?page_id='.$page_id.'&section_id='.$section_id.'&chapter_id='.$id);
} else {
	$title = addslashes($admin->get_post('title'));
	$description = addslashes($admin->get_post('description'));
	$content =addslashes($admin->get_post('content'));	
	$parent = $admin->get_post('parent');
	$old_parent = $admin->get_post('old_parent');
	$active = $admin->get_post('active');
	$old_link = $admin->get_post('link');
	$position = $admin->get_post('position');
	$modified_when = $admin->get_post('modified_when');
	if(trim($modified_when) == '0' OR trim($modified_when) == '') {
		$modified_when = time();
	} else {
		$modified_when = jscalendar_to_timestamp($modified_when);
		}
	$modified_by = $admin->get_post('modified_by'); 
}

// Check if parent has changed
if($old_parent != $parent and $parent != '') {
	// Include the ordering class
	require(WB_PATH.'/modules/manual/class.order.php');
	// Get new order
	$order = new order(TABLE_PREFIX.'mod_manual_chapters', 'position', 'chapter_id', 'parent', $section_id);
	$position = $order->get_new($parent);
}

// Get parent title and link (if there is a parent)
if($parent != 0) {
	$query_parent = $database->query("SELECT chapter_id,title,level,link FROM ".TABLE_PREFIX."mod_manual_chapters WHERE chapter_id = '$parent' LIMIT 1");
	$fetch_parent = $query_parent->fetchRow();
	$parent_title = $fetch_parent['title'];
	$parent_link = $fetch_parent['link'];
	$parent_id = $fetch_parent['chapter_id'];
	$parent_level = $fetch_parent['level'];
	$level = 1;
	// Check for second level?
	if ($parent_level != 0 ) {
		$query_parent = $database->query("SELECT chapter_id,title,level, link FROM ".TABLE_PREFIX."mod_manual_chapters WHERE chapter_id = '$parent_id' LIMIT 1");
		$fetch_parent = $query_parent->fetchRow();
		if ($parent != 0) {
//			$parent_id = $fetch_parent['chapter_id'];
			$parent_link = $fetch_parent['link'];
			$parent_title2 = $fetch_parent['title'];
			$level = 2;
		}
	}
} else {
	$parent_title = '';
	$parent_link = '';
	$parent_id = '';
	$level = 0;
}

// Get page link URL
$query_page = $database->query("SELECT level,link FROM ".TABLE_PREFIX."pages WHERE page_id = '$page_id'");
$page = $query_page->fetchRow();
$page_level = $page['level'];
$page_link = $page['link'];
$chapter_name = $page_link.'/';

// Include WB functions file
require(WB_PATH.'/framework/summary.functions.php');

// Work-out what the link should be
if(page_filename($parent_title) != '') {
	$chapter_link = $parent_link.'/'.page_filename($title);
} else {
	$chapter_link = $chapter_name.page_filename($title);
}

if(!file_exists(WB_PATH.PAGES_DIRECTORY.$chapter_name)) {
	mkdir(WB_PATH.PAGES_DIRECTORY.$chapter_name);
}
// Create access dir for parent
if(!file_exists(WB_PATH.PAGES_DIRECTORY.$parent_link.'/') AND page_filename($parent_title) != '') {
	mkdir(WB_PATH.PAGES_DIRECTORY.$parent_link.'/');
}

if(!is_writable(WB_PATH.PAGES_DIRECTORY.$chapter_name)) {
	$admin->print_error($MESSAGE['PAGES']['CANNOT_CREATE_ACCESS_FILE']);
} elseif($old_link != $chapter_link OR !file_exists(WB_PATH.PAGES_DIRECTORY.$chapter_link.PAGE_EXTENSION)) {
	// We need to create a new file
	// First, delete old file if it exists
	if(file_exists(WB_PATH.PAGES_DIRECTORY.$old_link.PAGE_EXTENSION)) {
		unlink(WB_PATH.PAGES_DIRECTORY.$old_link.PAGE_EXTENSION);
	}
	// Specify the filename
	$filename = PAGES_DIRECTORY.$chapter_link.PAGE_EXTENSION;
	// Write to the filename
	create_access_file2 ($filename,$page_id, $section_id, $chapter_id) ;

	// Move a directory for this page
	if(file_exists(WB_PATH.PAGES_DIRECTORY.$old_link.'/') AND is_dir(WB_PATH.PAGES_DIRECTORY.$old_link.'/') AND $old_link <> $chapter_link AND $old_link != '' ) {
		rename(WB_PATH.PAGES_DIRECTORY.$old_link.'/', WB_PATH.PAGES_DIRECTORY.$chapter_link.'/');
	}
	// Update any pages that had the old link with the new one
	$old_link_len = strlen($old_link);
	if ($old_link != '' ) {
		$query_subs = $database->query("SELECT chapter_id,link FROM ".TABLE_PREFIX."mod_manual_chapters WHERE link LIKE '%$old_link/%' AND section_id='$section_id' ORDER BY LEVEL ASC");
		if($query_subs->numRows() > 0) {
			while($sub = $query_subs->fetchRow()) {
				// Double-check to see if it contains old link
				if(substr($sub['link'], 0, $old_link_len) == $old_link) {
					// Get new link
					$replace_this = $old_link;
					$old_sub_link_len =strlen($sub['link']);
					$new_sub_link = $chapter_link.'/'.substr($sub['link'],$old_link_len+1,$old_sub_link_len);
					// Update link
					$database->query("UPDATE ".TABLE_PREFIX."mod_manual_chapters  SET link = '$new_sub_link' WHERE chapter_id = '".$sub['chapter_id']."' LIMIT 1");
					// Re-write the access file for this page
					$old_subpage_file = WB_PATH.PAGES_DIRECTORY.$new_sub_link.PAGE_EXTENSION;
					if(file_exists($old_subpage_file)) {
						unlink($old_subpage_file);
					}
					create_access_file2(PAGES_DIRECTORY.$new_sub_link.PAGE_EXTENSION, $page_id, $section_id, $sub['chapter_id']);
				}
			}
		}
	}
}

// Update row
$database->query("UPDATE ".TABLE_PREFIX."mod_manual_chapters SET parent = '$parent', title = '$title', level = '$level', link = '$chapter_link', position = '$position', description = '$description', `content` = '$content', active = '$active', modified_when = '".time()."', modified_by = '".$admin->get_user_id()."' WHERE chapter_id = '$chapter_id'");

// Check if there is a db error, otherwise say successful
if($database->is_error()) {
	$admin->print_error($database->get_error(), WB_URL.'/modules/manual/modify_chapter.php?page_id='.$page_id.'&section_id='.$section_id.'&chapter_id='.$id);
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
require("'.$index_location.'config.php");
require(WB_PATH."/index.php");
?>';
	$handle = fopen(WB_PATH.$filename, 'w');
	fwrite($handle, $file_content);
	fclose($handle);
}
?>