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
require(WB_PATH.'/framework/functions.php');
 
$database = new database(DB_URL);

echo "<BR><B>Adding new field to database table mod_manual_settings</B><BR>";

if($database->query("ALTER TABLE `".TABLE_PREFIX."mod_manual_settings` ADD `format` TEXT NOT NULL AFTER `footer`")) {
	echo 'Database Field format added successfully<br />';
}
echo mysql_error().'<br />';

// UPDATING DATA INTO FIELDS
echo "<BR>";

// These are the default setting
$manual_style = '<style type=\"text/css\">

.MLheader {
  color: #999999;
  font-size: 14px;
  font-weight: bold;
}

.MLpage_header {
  border-bottom: 1px solid #CCCCCC;
  margin-top: 5px;
}

.MLtitle {
  padding-bottom: 2px;
  color: #666666;
  font-weight: bold;
  font-size: 18px;
}

.MLdescription {
  color: #666666;
  padding-bottom: 2px;
}

.MLindex_table ol {
  margin: 0;
  padding-left: 10px;
}

.MLchapter {
  color: #666666;
  font-size: 11px;
  font-weight: bold;
}

.MLsubchapt {
  color: #666666;
  font-size: 10px;
  font-weight: bold;
}

.MLIndex_bg {
  background-color: #F0F0F0;
}

.MLupdated {
  color: #666666;
  padding-bottom: 5px;
}

.MLfooter {
  color: #999999;
  font-size: 14px;
  font-weight: bold;
}

#MLchapt_lnk, #MLpage_chapt_lnk, #MLsubchapt_lnk {
   color: #666666;
  text-decoration: underline;
}

#MLprev_link_top, #MLnext_link_top, #MLindex_link_top {
  color: #666666;
  font-size: 11px;
  font-weight: bold;
 }

#MLprev_link_btm, #MLnext_link_btm, #MLindex_link_btm {
  color: #666666;
  font-size: 11px;
  font-weight: bold;
}

</style>';

// Insert default settings into database
$query_dates = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_settings where section_id != 0 and page_id != 0");
while($result = $query_dates->fetchRow()) {
	
	echo "<B>Add default settings data to database for manual section_id= ".$result['section_id']."</b><BR>";
	$section_id = $result['section_id'];

	if($database->query("UPDATE `".TABLE_PREFIX."mod_manual_settings` SET `format` = '$manual_style' WHERE `section_id` = $section_id")) {
		echo 'Database data format added successfully<br>';
	}
	echo mysql_error().'<br />';
	
}

?>