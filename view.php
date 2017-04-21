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

// Get Settings
$fetch_settings = array();
$database->execute_query(
	"SELECT * FROM `".TABLE_PREFIX."mod_manual_settings` WHERE `section_id` = ".$section_id,
	true,
	$fetch_settings,
	false
);
if(count($fetch_settings) > 0) {
	$header = $fetch_settings['header'];
	$footer = $fetch_settings['footer'];
} else {
	$header = '';
	$footer = '';
}		

$oManual = manual::getInstance();
$all_chapters = $oManual->get_manual_by_sectionID( $section_id );

if( 0 === count($all_chapters) )
{
	echo $MLTEXT['UNDERCONSTRUCTION'];
	return false;
}

/**
 *	Get the template engine
 */
global $parser, $loader;
require( dirname(__FILE__)."/register_parser.php" );
	
// Check if we should show the "contents" page or the actual chapter
if(defined('CHAPTER_ID')) {

	if( true === $oManual->detail_shown )
	{
		return true;
	}
	
	$oManual->detail_shown = true;
	
	// Get chapter content
	$chapter_content = array();
	$database->execute_query(
		"SELECT * FROM `".TABLE_PREFIX."mod_manual_chapters` WHERE `chapter_id` = ".CHAPTER_ID,
		true,
		$chapter_content,
		false
	);
	
	//	pre process some data
	$wb->preprocess( $chapter_content['content'] );
	
	$oDate = lib_lepton::getToolInstance("datetools");
	$oDate->set_core_language( LANGUAGE );
		
	$oDate->setFormat( $oDate->CORE_date_formats[ DATE_FORMAT ] );
	$modify_when_date = $oDate->toHTML( $chapter_content['modified_when'] );
		
	$oDate->setFormat( $oDate->CORE_time_formats[ TIME_FORMAT ] );
	$modify_when_time = $oDate->toHTML( $chapter_content['modified_when'] );
	
	// user
	$modified_user = array();
	$database->execute_query(
		"SELECT `display_name`,`username` FROM `".TABLE_PREFIX."users` WHERE `user_id` = ".$chapter_content['modified_by'],
		true,
		$modified_user,
		false
	);
	
	/**
	 *	Any siblings for this chapter?
	 */
	$siblings = $oManual->get_siblings( $chapter_content['parent'] );
	
	$sibling_list = array(
		'first'	=> 0,
		'prev'	=> 0,
		'next'	=> 0,
		'last'	=> 0
	);
	
	$num_of_siblings = count($siblings); 
	if( $num_of_siblings > 1 )
	{
		
		/**
		 *	Geet the actual relative list position
		 *
		 */
		for( $i=0; $i < $num_of_siblings; $i++ ){
			if( $siblings[ $i]['chapter_id'] === $chapter_content['chapter_id'] )
			{
				$current_pos = $i;
				break;
			}
		} 
		
		if($current_pos > 0)
		{
			// get prev
			$sibling_list['prev'] = array(
				'title'	=> $siblings[$current_pos-1]['title'],
				'link'	=> page_link($wb->page['link'].($oManual->get_root( $all_chapters, $siblings[$current_pos-1]['chapter_id'] ))),
				'id'	=> $siblings[$current_pos-1]['chapter_id']
			);
		}
		
		if($current_pos < $num_of_siblings-2) // ! keep in mind, we need the pre-last position
		{
			// get next!
			$sibling_list['next'] = array(
				'title'	=> $siblings[$current_pos+1]['title'],
				'link'	=> page_link($wb->page['link'].($oManual->get_root( $all_chapters, $siblings[$current_pos+1]['chapter_id'] ))),
				'id'	=> $siblings[$current_pos+1]['chapter_id']
			);

		}
		
		if($siblings[0]['chapter_id'] != $chapter_content['chapter_id'])
		{
			$sibling_list['first'] = array(
				'title'	=> $siblings[0]['title'],
				'link'	=> page_link($wb->page['link'].($oManual->get_root( $all_chapters, $siblings[0]['chapter_id'] ))),
				'id'	=> $siblings[0]['chapter_id']
			);
		}
		
		if($siblings[ $num_of_siblings-1 ]['chapter_id'] != $chapter_content['chapter_id'])
		{
			$sibling_list['last'] = array(
				'title'	=> $siblings[ $num_of_siblings-1 ]['title'],
				'link'	=> page_link( $wb->page['link'].$oManual->get_root( $all_chapters, $siblings[ $num_of_siblings-1 ]['chapter_id'] ) ),
				'id'	=> $siblings[ $num_of_siblings-1 ]['chapter_id']
			);
		}
	}
	
	/**
	 *		Here we go to display the details
	 */
	$page_data = array(
		'MLTEXT'			=> $MLTEXT,
		'main_index'		=> page_link( $wb->page['link'] ),
		'chapter_content'	=> $chapter_content,
		'modify_when_date'	=> $modify_when_date,
		'modify_when_time'	=> $modify_when_time,
		'modified_user'		=> $modified_user,
		'sibling_list'		=> $sibling_list
	);
	
	echo $parser->render(
		"@manual/chapter_page.lte",
		$page_data
	);
	
} else {

	// List all chapters for this section
	
	$chapter_tree = array();
	$oManual->build_tree( $all_chapters, $chapter_tree, 0);

	$page_data = array(
		'header'	=> $header,
		'footer'	=> $footer,
		'chapter_tree' => $chapter_tree
	);
	
	echo $parser->render(
		"@manual/view.lte",
		$page_data
	);	

}

?>