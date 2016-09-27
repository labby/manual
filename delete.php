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

// Must include code to stop this file being access directly
if(defined('WB_PATH') == false) { 
	exit("Cannot access this file directly"); 
}

// Remove chapter acces files
$query_details = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters WHERE section_id = '$section_id'");
if($query_details->numRows() > 0) {
	$get_details = $query_details->fetchRow();
	// Unlink chapter access file
	if(is_writable(WB_PATH.PAGES_DIRECTORY.$get_details['link'].PAGE_EXTENSION)) {
		unlink(WB_PATH.PAGES_DIRECTORY.$get_details['link'].PAGE_EXTENSION);
	}
}
// Remove chapter subdirs second level files
$query_details = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters WHERE section_id = '$section_id' AND level = 2 ");
if($query_details->numRows() > 0) {
	$get_details = $query_details->fetchRow();
	// Unlink chapter access dir
	if(is_writable(WB_PATH.PAGES_DIRECTORY.$get_details['link'])) {
		rmdir(WB_PATH.PAGES_DIRECTORY.$get_details['link']);
	}
}
// Remove chapter subdirs first level files
$query_details = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters WHERE section_id = '$section_id' AND level = 1 ");
if($query_details->numRows() > 0) {
	$get_details = $query_details->fetchRow();
	// Unlink chapter access dir
	if(is_writable(WB_PATH.PAGES_DIRECTORY.$get_details['link'])) {
		rmdir(WB_PATH.PAGES_DIRECTORY.$get_details['link']);
	}
}

// Delete records from the database
$database->query("DELETE FROM ".TABLE_PREFIX."mod_manual_settings WHERE section_id = '$section_id'");
$database->query("DELETE FROM ".TABLE_PREFIX."mod_manual_chapters WHERE section_id = '$section_id'");

?>