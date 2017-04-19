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
<h2><?php echo $TEXT['MODIFY'].'/'.$TEXT['DELETE'].' '.$MLTEXT['CHAPTERS']; ?></h2>
<?php
$all_chapters = array();
$database->execute_query(
	"SELECT * FROM `".TABLE_PREFIX."mod_manual_chapters` WHERE `parent` = 0 AND section_id = ".$section_id." ORDER BY position ASC",
	true,
	$all_chapters,
	true
);
$num_chapters = count($all_chapters);

if($num_chapters > 0) {
	?>
	<table cellpadding="2" cellspacing="0" border="0" width="100%">
	<tr height="0">
		<td width="20"></td>
		<td width="20"></td>
		<td width="20"></td>
		<td width="20"></td>
		<td width="20"></td>
		<td></td>
		<td width="80"></td>
		<td width="20"></td>
		<td width="20"></td>
		<td width="20"></td>
	</tr>
	<?php
	foreach($all_chapters as &$chapter)
	{
		?>
		<tr class="row_a">
			<td width="20" style="padding-left: 5px;">
				<a href="<?php echo LEPTON_URL; ?>/modules/manual/modify_chapter.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MODIFY']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/modify_16.png" border="0" alt="Modify - " />
				</a>
			</td>
			<td colspan="5">
				<a href="<?php echo LEPTON_URL; ?>/modules/manual/modify_chapter.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MODIFY']; ?>">
					<?php echo $chapter['title']; ?>
				</a>
			</td>
			<td width="80">
				<?php echo $TEXT['ACTIVE'].': '; if($chapter['active'] == 1) { echo $TEXT['YES']; } else { echo $TEXT['NO']; } ?>
			</td>
			<td width="20">
			<?php if($chapter['position'] != 1) { ?>
				<a href="<?php echo LEPTON_URL; ?>/modules/manual/move_up.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MOVE_UP']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/up_16.png" border="0" alt="^" />
				</a>
			<?php } ?>
			</td>
			<td width="20">
			<?php if($chapter['position'] != $num_chapters) { ?>
				<a href="<?php echo LEPTON_URL; ?>/modules/manual/move_down.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MOVE_DOWN']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/down_16.png" border="0" alt="v" />
				</a>
			<?php } ?>
			</td>
			<td width="20">
				<?php
				$query_sub_chapters = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters WHERE parent = '".$chapter['chapter_id']."' AND section_id = '$section_id' ORDER BY position ASC");
				$num_sub_chapters = $query_sub_chapters->numRows();
				if($num_sub_chapters == 0) {
				?>
				<a href="javascript: confirm_link('<?php echo $TEXT['ARE_YOU_SURE']; ?>', '<?php echo LEPTON_URL; ?>/modules/manual/delete_chapter.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>');" title="<?php echo $TEXT['DELETE']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/delete_16.png" border="0" alt="X" />
				</a>
				<?php
				}
				?>
			</td>
		</tr>
		<?php
		if($num_sub_chapters > 0) {	
			while($chapter = $query_sub_chapters->fetchRow()) {
				?>
				<tr class="row_a">
					<td width="20" align="center">-</td>
					<td width="20" style="padding-left: 5px;">
						<a href="<?php echo LEPTON_URL; ?>/modules/manual/modify_chapter.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MODIFY']; ?>">
							<img src="<?php echo THEME_URL; ?>/images/modify_16.png" border="0" alt="Modify - " />
						</a>
					</td>
					<td colspan="4">
						<a href="<?php echo LEPTON_URL; ?>/modules/manual/modify_chapter.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MODIFY']; ?>">
							<?php echo $chapter['title']; ?>
						</a>
					</td>
					<td width="80">
						<?php echo $TEXT['ACTIVE'].': '; if($chapter['active'] == 1) { echo $TEXT['YES']; } else { echo $TEXT['NO']; } ?>
					</td>
					<td width="20">
					<?php if($chapter['position'] != 1) { ?>
						<a href="<?php echo LEPTON_URL; ?>/modules/manual/move_up.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MOVE_UP']; ?>">
							<img src="<?php echo THEME_URL; ?>/images/up_16.png" border="0" alt="^" />
						</a>
					<?php } ?>
					</td>
					<td width="20">
					<?php if($chapter['position'] != $num_chapters) { ?>
						<a href="<?php echo LEPTON_URL; ?>/modules/manual/move_down.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MOVE_DOWN']; ?>">
							<img src="<?php echo THEME_URL; ?>/images/down_16.png" border="0" alt="v" />
						</a>
					<?php } ?>
					</td>
			<td width="20">
				<?php
				$query_sub2_chapters = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters WHERE parent = '".$chapter['chapter_id']."' AND section_id = '$section_id' ORDER BY position ASC");
				$num_sub2_chapters = $query_sub2_chapters->numRows();
				if($num_sub2_chapters == 0) {
				?>
				<a href="javascript: confirm_link('<?php echo $TEXT['ARE_YOU_SURE']; ?>', '<?php echo LEPTON_URL; ?>/modules/manual/delete_chapter.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>');" title="<?php echo $TEXT['DELETE']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/delete_16.png" border="0" alt="X" />
				</a>
				<?php
				}
				?>
			</td>
		</tr>
		<?php
		if($num_sub2_chapters > 0) {	
			while($chapter = $query_sub2_chapters->fetchRow()) {
				?>
				<tr class="row_a">
					<td width="20" align="center">-</td>
					<td width="20" align="center">-</td>
					<td width="20" style="padding-left: 5px;">
						<a href="<?php echo LEPTON_URL; ?>/modules/manual/modify_chapter.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MODIFY']; ?>">
							<img src="<?php echo THEME_URL; ?>/images/modify_16.png" border="0" alt="Modify - " />
						</a>
					</td>
					<td colspan ="3" align="left">
						<a href="<?php echo LEPTON_URL; ?>/modules/manual/modify_chapter.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MODIFY']; ?>">
							<?php echo $chapter['title']; ?>
						</a>
					</td>
					<td width="80">
						<?php echo $TEXT['ACTIVE'].': '; if($chapter['active'] == 1) { echo $TEXT['YES']; } else { echo $TEXT['NO']; } ?>
					</td>
					<td width="20">
					<?php if($chapter['position'] != 1) { ?>
						<a href="<?php echo LEPTON_URL; ?>/modules/manual/move_up.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MOVE_UP']; ?>">
							<img src="<?php echo THEME_URL; ?>/images/up_16.png" border="0" alt="^" />
						</a>
					<?php } ?>
					</td>
					<td width="20">
					<?php if($chapter['position'] != $num_chapters) { ?>
						<a href="<?php echo LEPTON_URL; ?>/modules/manual/move_down.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MOVE_DOWN']; ?>">
							<img src="<?php echo THEME_URL; ?>/images/down_16.png" border="0" alt="v" />
						</a>
					<?php } ?>
					</td>
					<td width="20">
						<a href="javascript: confirm_link('<?php echo $TEXT['ARE_YOU_SURE']; ?>', '<?php echo LEPTON_URL; ?>/modules/manual/delete_chapter.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>');" title="<?php echo $TEXT['DELETE']; ?>">
							<img src="<?php echo THEME_URL; ?>/images/delete_16.png" border="0" alt="X" />
						</a>
					</td>
				</tr>
				<?php
			}
			}
			}
		}
	}
	?>
	</table>
	<?php
}
?>