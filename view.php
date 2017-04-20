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
	
	return true;
	
	// Get number of chapters
	$get_num_chapters = $database->query("SELECT `chapter_id` FROM `".TABLE_PREFIX."mod_manual_chapters` WHERE `section_id` = '".$section_id."' AND parent = '".$parent."'");
	$num_chapters = $get_num_chapters->numRows();

	// Get sub chapters
	if($level < 2) {  // // CHAPTER_LEVEL_LIMIT
		$query_subs = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters WHERE section_id = '$section_id' AND parent = '".CHAPTER_ID."' ORDER BY position ASC");
	}
	
	// Get previous chapter details
	$previous_position = $fetch_content['position']-1;
	$get_previous = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters WHERE position = '$previous_position' AND active = '1' AND parent = '$parent' AND section_id = '$section_id' LIMIT 1");
	if($get_previous->numRows() > 0) {
		$fetch_previous = $get_previous->fetchRow();
		$previous_id = $fetch_previous['chapter_id'];
		$previous_title = stripslashes($fetch_previous['title']);
		$previous_link = page_link($fetch_previous['link']);
		$previous_description = stripslashes($fetch_previous['description']);
	} else {
      $previous_id = 0;
	  if($previous_position == 0 and $parent != 0) {
      $get_previous = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters WHERE chapter_id = '$parent' AND active = '1' AND section_id = '$section_id' LIMIT 1");
      if($get_previous->numRows() > 0) {
        $fetch_previous = $get_previous->fetchRow();
        $previous_title = stripslashes($fetch_previous['title']);
        $previous_link = page_link($fetch_previous['link']);
      }
      $previous_id = $parent;
	  }
	}
	
	// Get next chapter details
	if($parent == 0 AND $query_subs->numRows()) {
		// Get first sub chapter
		$next_position = 1;
		$get_next = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters WHERE position = '$next_position' AND parent = '".CHAPTER_ID."' AND active = '1' AND section_id = '$section_id' ORDER BY position ASC LIMIT 1");
		if($get_next->numRows() > 0) {
			$fetch_next = $get_next->fetchRow();
			$next_id = $fetch_next['chapter_id'];
			$next_title = stripslashes($fetch_next['title']);
			$next_link = page_link($fetch_next['link']);
			$next_description = stripslashes($fetch_next['description']);
		} else {
			$next_id = 0;
		}
	} elseif($parent != 0 AND $position == $num_chapters) {
		// Get next main chapter (we are at the end of these sub chapters)
		// First get parent position
		$get_parent = $database->query("SELECT position FROM ".TABLE_PREFIX."mod_manual_chapters WHERE chapter_id = '$parent' AND active = '1' AND section_id = '$section_id' LIMIT 1");
		$fetch_parent = $get_parent->fetchRow();
		$parent_position = $fetch_parent['position'];
		$next_position = $parent_position+1;
		$get_next = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters WHERE position = '$next_position' AND parent = '0' AND active = '1' AND section_id = '$section_id' LIMIT 1");
		if($get_next->numRows() > 0) {
			$fetch_next = $get_next->fetchRow();
			$next_id = $fetch_next['chapter_id'];
			$next_title = stripslashes($fetch_next['title']);
			$next_link = page_link($fetch_next['link']);
			$next_description = stripslashes($fetch_next['description']);
		} else {
			$next_id = 0;
		}
	} else {
		// Get next main chapter
		$next_position = $fetch_content['position']+1;
		$get_next = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters WHERE position = '$next_position' AND parent = '$parent' AND active = '1' AND section_id = '$section_id' LIMIT 1");
		if($get_next->numRows() > 0) {
			$fetch_next = $get_next->fetchRow();
			$next_id = $fetch_next['chapter_id'];
			$next_title = stripslashes($fetch_next['title']);
			$next_link = page_link($fetch_next['link']);
			$next_description = stripslashes($fetch_next['description']);
		} else {
			$next_id = 0;
		}
	}
	
	?>
	<table class="MLIndex_bg" cellpadding="0" cellspacing="0" border="0" width="99%">
	<tr>
		<td align="left" valign="bottom" width="30%">
			<?php if($previous_id != 0) { ?>
			<a ID="MLprev_link_top" href="<?php echo $previous_link; ?>"><< <?php echo $previous_title; ?></a>
			<?php } ?>
		</td>
		<td class="MLindex" align="center" valign="top">
			<?php
			$query_link = $database->query("SELECT link FROM ".TABLE_PREFIX."pages WHERE page_id = '".PAGE_ID."' LIMIT 1");
			if($query_link->numRows() > 0) {
				$fetch_link = $query_link->fetchRow();
				?>
				<a ID="MLindex_link_top" href="<?php echo page_link($fetch_link['link']); ?>"><?php echo $MLTEXT['INDEX']; ?></a>
				<?php
			}
			?>
		</td>
		<td align="right" valign="bottom" width="30%">
			<?php if($next_id != 0 and $next_title != '') { ?>
			<a ID="MLnext_link_top" href="<?php echo $next_link; ?>"><?php echo $next_title; ?> >></a>
			<?php } ?>
		</td>
	</tr>
	</table>
	
	<table class="MLpage_header" cellpadding="0" cellspacing="0" border="0" width="99%">
	<tr>
		<td class="MLtitle">
			<?php echo $title; ?>
		</td>
		<td class="MLdescription" align="right">
			<?php echo $description; ?>
		</td>
	</tr>
	</table>
	<br />
	<?php
	
	echo $content;
	
	if($level < 2 AND $query_subs->numRows() > 0) {
		?>
		<br />
		<ol class="MLindex_table">
		<?php
		while($chapter = $query_subs->fetchRow()) {
			?>
			<li class="MLchapter">
				<a ID="MLpage_chapt_lnk" href="<?php echo page_link($chapter['link']); ?>
					<?php echo stripslashes($chapter['title']); ?>
				</a>
				<?php
				$description = stripslashes($chapter['description']);
				if($description != '') {
					echo '<br />'.$description;
				}
				?>
			</li>
			<?php
		}
		?>
		</ol>
		<?php
	}
	
	?>
	<br />
	<br />
	<?php
	$get_modified_user = $database->query("SELECT display_name,username FROM ".TABLE_PREFIX."users WHERE user_id = '".$modified_by."' LIMIT 1");
	if($get_modified_user->numRows() > 0) {
		$fetch_modified_user = $get_modified_user->fetchRow();
		$modified_user = $fetch_modified_user['display_name'].' ('.$fetch_modified_user['username'].')';
	} else {
		$modified_user = $TEXT['UNKOWN'];
	}
	?>
	<font class="MLupdated">
	<?php 
		$oDate = lib_lepton::getToolInstance("datetools");
		$oDate->set_core_language( LANGUAGE );
		
		$oDate->setFormat( $oDate->CORE_date_formats[ DATE_FORMAT ] );
		$modify_when_date = $oDate->toHTML( $modified_when );
		
		$oDate->setFormat( $oDate->CORE_time_formats[ TIME_FORMAT ] );
		$modify_when_time = $oDate->toHTML( $modified_when );
		
	?>
	<?php echo $MLTEXT['LASTUPDATED']; ?>&nbsp;<?php echo $modified_user; ?>&nbsp;
	<?php echo $MLTEXT['ON']; ?>&nbsp;<?php echo $modify_when_date; ?>&nbsp;
	<?php echo $MLTEXT['AT']; ?>&nbsp;<?php echo $modify_when_time; ?>
	</font>
	<br />
	<table class="MLIndex_bg" cellpadding="0" cellspacing="0" border="0" width="99%">
	<tr>
		<td align="left" valign="bottom" width="30%">
			<?php if($previous_id != 0) { ?>
			<a ID="MLprev_link_btm" href="<?php echo $previous_link; ?>"><< <?php echo $previous_title; ?></a>
			<?php } ?>
		</td>
		<td align="center" valign="top">
			<?php
			$query_link = $database->query("SELECT link FROM ".TABLE_PREFIX."pages WHERE page_id = '".PAGE_ID."' LIMIT 1");
			if($query_link->numRows() > 0) {
				$fetch_link = $query_link->fetchRow();
				?>
				<a ID="MLindex_link_btm" href="<?php echo page_link($fetch_link['link']); ?>"><?php echo $MLTEXT['INDEX']; ?></a>
				<?php
			}
			?>
		</td>
		<td align="right" valign="bottom" width="30%">
			<?php if($next_id != 0 and $next_title != '') { ?>
			<a ID="MLnext_link_btm" href="<?php echo $next_link; ?>"><?php echo $next_title; ?> >></a>
			<?php } ?>
		</td>
	</tr>
	</table>
	<?php
	
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