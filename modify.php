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

//	removes empty entries from the table so they will not be displayed
//		Aldus: 2017-04-27 - Mit einem Fuß im Grab!
$database->simple_query(
	"DELETE FROM `".TABLE_PREFIX."mod_manual_chapters` WHERE `page_id` = '?' and title=''",
	array($page_id)
);

//	Load Language file
require_once __DIR__."/register_language.php";

/**
 *	Get the template engine
 */
require( dirname(__FILE__)."/register_parser.php" );

/**
 *	Get the manual data
 */
$oManual = manual::getInstance();
$all_chapters = $oManual->get_manual_by_sectionID( $section_id );

$chapter_tree = array();
$oManual->build_backend_tree( $all_chapters, $chapter_tree, 0);

//	Add some vars as globals as we need them inside a recursive macro inside the backend-template.
$oTwig->registerGlobals( array(
	"section_id"	=> $section_id,
	"page_id"		=> $page_id,
	"leptoken"		=> LEPTON_tools::get_leptoken(),
	"TEXT"			=> $TEXT,
	"MLTEXT"		=> $MLTEXT
	)
);
	
$page_values = array(
	'chapter_tree'	=> $chapter_tree
);

echo $oTwig->render(
	"@manual/modify_ul.lte",
	$page_values
);

