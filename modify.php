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

//	Load Language file
require_once __DIR__."/register_language.php";

/**
 *	Get the template engine
 */
global $parser, $loader;
require( dirname(__FILE__)."/register_parser.php" );

//	removes empty entries from the table so they will not be displayed
$database->simple_query(
	"DELETE FROM `".TABLE_PREFIX."mod_manual_chapters` WHERE `page_id` = '?' and title=''",
	array($page_id)
);

?>

<table cellpadding="0" cellspacing="0" border="0" width="100%" style="border-bottom: 1px solid #AAAAAA;">
<tr>
	<td width="50%">
		<input type="button" name="add_chapter" value="<?php echo $TEXT['ADD'].' '.$MLTEXT['CHAPTERS']; ?>" onclick="javascript: window.location = '<?php echo LEPTON_URL; ?>/modules/manual/add_chapter.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>';" style="width: 100%;" />	
	</td>
	<td>
		<input type="button" name="settings" value="<?php echo $TEXT['SETTINGS']; ?>" onclick="javascript: window.location = '<?php echo LEPTON_URL; ?>/modules/manual/modify_settings.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>';" style="width: 100%;" />
	</td>
</tr>
</table>

<?php
	
	$oManual = manual::getInstance();
	$all_chapters = $oManual->get_manual_by_sectionID( $section_id );
	
	$chapter_tree = array();
	$oManual->build_backend_tree( $all_chapters, $chapter_tree, 0);
	
	// echo LEPTON_tools::display( $chapter_tree );
	
	$parser->AddGlobal("section_id", $section_id);
	$parser->AddGlobal("page_id", $page_id);
	$parser->AddGlobal("leptoken", LEPTON_tools::get_leptoken());
	$parser->AddGlobal("TEXT", $TEXT);
	
	$page_values = array(
		// 'leptoken'	=> LEPTON_tools::get_leptoken(),
		// 'section_id'	=> $section_id,
		// 'page_id'		=> $page_id,
		// 'TEXT'	=> $TEXT,
		'chapter_tree'	=> $chapter_tree
	);
	
	echo $parser->render(
		"@manual/modify_ul.lte",
		$page_values
	);
	
	return true;
?>
