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

// Load Language file
if(LANGUAGE_LOADED) {
	if(!file_exists(LEPTON_PATH.'/modules/manual/languages/'.LANGUAGE.'.php')) {
		require_once(LEPTON_PATH.'/modules/manual/languages/EN.php');
	} else {
		require_once(LEPTON_PATH.'/modules/manual/languages/'.LANGUAGE.'.php');
	}
}

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

// Check if we should show the "contents" page or the actual chapter
if(defined('CHAPTER_ID')) {

	// Get chapter content
	$get_content = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters WHERE chapter_id = '".CHAPTER_ID."'");
	$fetch_content = $get_content->fetchRow();
	$title = stripslashes($fetch_content['title']);
	$description = stripslashes($fetch_content['description']);
	$content = $fetch_content['content'];
	$wb->preprocess($content);
	$parent = $fetch_content['parent'];
	$level = $fetch_content['level'];
	$position = $fetch_content['position'];
	$modified_when = $fetch_content['modified_when'];
	$modified_by = $fetch_content['modified_by'];
	
	// Get number of chapters
	$get_num_chapters = $database->query("SELECT chapter_id FROM ".TABLE_PREFIX."mod_manual_chapters WHERE section_id = '$section_id' AND parent = '$parent'");
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
				<a ID="MLpage_chapt_lnk" href="<?php echo page_link($chapter['link']); ?>">
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
	<?php echo $MLTEXT['LASTUPDATED']; ?>&nbsp;<?php echo $modified_user; ?>&nbsp;
	<?php echo $MLTEXT['ON']; ?>&nbsp;<?php echo gmdate(DATE_FORMAT, $modified_when+TIMEZONE); ?>&nbsp;
	<?php echo $MLTEXT['AT']; ?>&nbsp;<?php echo gmdate(TIME_FORMAT, $modified_when+TIMEZONE); ?>
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
	// Show "contents" page
	echo '<span class="MLheader">'.$header.'</span>';
	
	// Get chapters list
	$get_chapters = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters WHERE section_id = '$section_id' AND parent = '0' AND active = '1' ORDER BY position ASC");
	if($get_chapters->numRows() > 0) {
		?>
		<ol class="MLindex_table">
		<?php
		while($chapter = $get_chapters->fetchRow()) {
			?>
			<li class="MLchapter">
				<a ID="MLchapt_lnk" href="<?php echo page_link($chapter['link']); ?>">
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
			// Get sub-chapters
			$get_sub_chapters = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters WHERE section_id = '$section_id' AND parent = '".$chapter['chapter_id']."' AND active = '1' ORDER BY position ASC");
			if($get_sub_chapters->numRows() > 0) {
				?>
				<ol>
				<?php
				while($chapter = $get_sub_chapters->fetchRow()) {
					?>
					<li class="MLsubchapt">
						<a ID="MLsubchapt_lnk" href="<?php echo page_link($chapter['link']); ?>">
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
					// Get sub-chapters
					$get_sub2_chapters = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters WHERE section_id = '$section_id' AND parent = '".$chapter['chapter_id']."' AND active = '1' ORDER BY position ASC");
					if($get_sub2_chapters->numRows() > 0) {
						?>
						<ol>
						<?php
						while($chapter2 = $get_sub2_chapters->fetchRow()) {
							?>
							<li class="MLsub2chapt">
								<a ID="MLsub2chapt_lnk" href="<?php echo page_link($chapter2['link']); ?>">
									<?php echo stripslashes($chapter2['title']); ?>
								</a>
								<?php
								$description = stripslashes($chapter2['description']);
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

				}
				?>
				</ol>
				<?php
			}
		}
		?>
		</ol>
		<?php
		
	} else {
		echo $MLTEXT['UNDERCONSTRUCTION'];
	}
	
	// Print footer
	echo '<br /><span class="MLfooter">'.$footer.'</span>';
}

?>