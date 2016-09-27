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

if(defined('WB_URL')) {
	
	$database->query("DROP TABLE IF EXISTS `".TABLE_PREFIX."mod_manual_chapters`");
	$mod_manual_chapters = 'CREATE TABLE `'.TABLE_PREFIX.'mod_manual_chapters` ('
					. ' `chapter_id` INT NOT NULL auto_increment,'
					. ' `section_id` INT NOT NULL DEFAULT \'0\','
					. ' `page_id` INT NOT NULL DEFAULT \'0\','
					. ' `active` INT NOT NULL DEFAULT \'0\','
					. ' `parent` INT NOT NULL DEFAULT \'0\','
					. ' `root_parent` INT NOT NULL DEFAULT \'0\','
					. ' `level` INT NOT NULL DEFAULT \'0\','
					. ' `position` INT NOT NULL DEFAULT \'0\','
					. ' `link` TEXT NOT NULL ,'
					. ' `title` VARCHAR( 255 ) NOT NULL DEFAULT \'\','
					. ' `description` TEXT NOT NULL ,'
					. ' `content` TEXT NOT NULL ,'
					. ' `modified_when` INT NOT NULL DEFAULT \'0\','
					. ' `modified_by` INT NOT NULL DEFAULT \'0\','
					. ' PRIMARY KEY ( `chapter_id` ) )'
					. ' ';
	$database->query($mod_manual_chapters);
	
	$database->query("DROP TABLE IF EXISTS `".TABLE_PREFIX."mod_manual_settings`");
	$mod_manual_settings = 'CREATE TABLE `'.TABLE_PREFIX.'mod_manual_settings` ('
					. ' `setting_id` INT NOT NULL auto_increment,'
					. ' `section_id` INT NOT NULL DEFAULT \'0\','
					. ' `page_id` INT NOT NULL DEFAULT \'0\','
					. ' `header` TEXT NOT NULL ,'
					. ' `footer` TEXT NOT NULL ,'
					. ' `format` TEXT NOT NULL ,'
					. ' PRIMARY KEY ( `setting_id` ) )'
					. ' ';
	$database->query($mod_manual_settings);
	
	
	// Insert info into the search table
	// Module query info
	$field_info = array();
	$field_info['page_id'] = 'page_id';
	$field_info['title'] = 'page_title';
	$field_info['link'] = 'link';
	$field_info = serialize($field_info);
	$database->query("INSERT INTO ".TABLE_PREFIX."search (name,value,extra) VALUES ('module', 'manual', '$field_info')");
	// Query start
	$query_start_code = "SELECT [TP]pages.page_id, [TP]pages.page_title,	[TP]pages.link	FROM [TP]mod_manual_chapters, [TP]pages WHERE ";
	$database->query("INSERT INTO ".TABLE_PREFIX."search (name,value,extra) VALUES ('query_start', '$query_start_code', 'manual')");
	// Query body
	$query_body_code = "
	[TP]pages.page_id = [TP]mod_manual_chapters.page_id AND [TP]mod_manual_chapters.title [O] \'[W][STRING][W]\' AND [TP]pages.searching = \'1\'
	OR [TP]pages.page_id = [TP]mod_manual_chapters.page_id AND [TP]mod_manual_chapters.description [O] \'[W][STRING][W]\' AND [TP]pages.searching = \'1\'
	OR [TP]pages.page_id = [TP]mod_manual_chapters.page_id AND [TP]mod_manual_chapters.content [O] \'[W][STRING][W]\' AND [TP]pages.searching = \'1\'";
	$database->query("INSERT INTO ".TABLE_PREFIX."search (name,value,extra) VALUES ('query_body', '$query_body_code', 'manual')");
	// Query end
	$query_end_code = "";	
	$database->query("INSERT INTO ".TABLE_PREFIX."search (name,value,extra) VALUES ('query_end', '$query_end_code', 'manual')");
	
	// Insert blank row (there needs to be at least on row for the search to work)
	$database->query("INSERT INTO ".TABLE_PREFIX."mod_manual_chapters (page_id,section_id) VALUES ('0','0')");
	$database->query("INSERT INTO ".TABLE_PREFIX."mod_manual_settings (page_id,section_id) VALUES ('0','0')");
	
}

?>