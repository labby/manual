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

// Load Language file
if(LANGUAGE_LOADED) {
	if(!file_exists(WB_PATH.'/modules/manual/languages/'.LANGUAGE.'.php')) {
		require_once(WB_PATH.'/modules/manual/languages/EN.php');
	} else {
		require_once(WB_PATH.'/modules/manual/languages/'.LANGUAGE.'.php');
	}
}

//removes empty entries from the table so they will not be displayed
$database->query("DELETE FROM ".TABLE_PREFIX."mod_manual_chapters WHERE page_id = '$page_id' and title=''");
?>

<table cellpadding="0" cellspacing="0" border="0" width="100%" style="border-bottom: 1px solid #AAAAAA;">
<tr>
	<td width="50%">
		<input type="button" name="add_chapter" value="<?php echo $TEXT['ADD'].' '.$MLTEXT['CHAPTERS']; ?>" onclick="javascript: window.location = '<?php echo WB_URL; ?>/modules/manual/add_chapter.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>';" style="width: 100%;" />	
	</td>
	<td>
		<input type="button" name="settings" value="<?php echo $TEXT['SETTINGS']; ?>" onclick="javascript: window.location = '<?php echo WB_URL; ?>/modules/manual/modify_settings.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>';" style="width: 100%;" />
	</td>
</tr>
</table>
<h2><?php echo $TEXT['MODIFY'].'/'.$TEXT['DELETE'].' '.$MLTEXT['CHAPTERS']; ?></h2>
<?php
$query_chapters = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters WHERE parent = 0 AND section_id = '$section_id' ORDER BY position ASC");
$num_chapters = $query_chapters->numRows();
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
	while($chapter = $query_chapters->fetchRow()) {
		?>
		<tr class="row_a">
			<td width="20" style="padding-left: 5px;">
				<a href="<?php echo WB_URL; ?>/modules/manual/modify_chapter.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MODIFY']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/modify_16.png" border="0" alt="Modify - " />
				</a>
			</td>
			<td colspan="5">
				<a href="<?php echo WB_URL; ?>/modules/manual/modify_chapter.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MODIFY']; ?>">
					<?php echo $chapter['title']; ?>
				</a>
			</td>
			<td width="80">
				<?php echo $TEXT['ACTIVE'].': '; if($chapter['active'] == 1) { echo $TEXT['YES']; } else { echo $TEXT['NO']; } ?>
			</td>
			<td width="20">
			<?php if($chapter['position'] != 1) { ?>
				<a href="<?php echo WB_URL; ?>/modules/manual/move_up.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MOVE_UP']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/up_16.png" border="0" alt="^" />
				</a>
			<?php } ?>
			</td>
			<td width="20">
			<?php if($chapter['position'] != $num_chapters) { ?>
				<a href="<?php echo WB_URL; ?>/modules/manual/move_down.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MOVE_DOWN']; ?>">
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
				<a href="javascript: confirm_link('<?php echo $TEXT['ARE_YOU_SURE']; ?>', '<?php echo WB_URL; ?>/modules/manual/delete_chapter.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>');" title="<?php echo $TEXT['DELETE']; ?>">
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
						<a href="<?php echo WB_URL; ?>/modules/manual/modify_chapter.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MODIFY']; ?>">
							<img src="<?php echo THEME_URL; ?>/images/modify_16.png" border="0" alt="Modify - " />
						</a>
					</td>
					<td colspan="4">
						<a href="<?php echo WB_URL; ?>/modules/manual/modify_chapter.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MODIFY']; ?>">
							<?php echo $chapter['title']; ?>
						</a>
					</td>
					<td width="80">
						<?php echo $TEXT['ACTIVE'].': '; if($chapter['active'] == 1) { echo $TEXT['YES']; } else { echo $TEXT['NO']; } ?>
					</td>
					<td width="20">
					<?php if($chapter['position'] != 1) { ?>
						<a href="<?php echo WB_URL; ?>/modules/manual/move_up.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MOVE_UP']; ?>">
							<img src="<?php echo THEME_URL; ?>/images/up_16.png" border="0" alt="^" />
						</a>
					<?php } ?>
					</td>
					<td width="20">
					<?php if($chapter['position'] != $num_chapters) { ?>
						<a href="<?php echo WB_URL; ?>/modules/manual/move_down.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MOVE_DOWN']; ?>">
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
				<a href="javascript: confirm_link('<?php echo $TEXT['ARE_YOU_SURE']; ?>', '<?php echo WB_URL; ?>/modules/manual/delete_chapter.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>');" title="<?php echo $TEXT['DELETE']; ?>">
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
						<a href="<?php echo WB_URL; ?>/modules/manual/modify_chapter.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MODIFY']; ?>">
							<img src="<?php echo THEME_URL; ?>/images/modify_16.png" border="0" alt="Modify - " />
						</a>
					</td>
					<td colspan ="3" align="left">
						<a href="<?php echo WB_URL; ?>/modules/manual/modify_chapter.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MODIFY']; ?>">
							<?php echo $chapter['title']; ?>
						</a>
					</td>
					<td width="80">
						<?php echo $TEXT['ACTIVE'].': '; if($chapter['active'] == 1) { echo $TEXT['YES']; } else { echo $TEXT['NO']; } ?>
					</td>
					<td width="20">
					<?php if($chapter['position'] != 1) { ?>
						<a href="<?php echo WB_URL; ?>/modules/manual/move_up.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MOVE_UP']; ?>">
							<img src="<?php echo THEME_URL; ?>/images/up_16.png" border="0" alt="^" />
						</a>
					<?php } ?>
					</td>
					<td width="20">
					<?php if($chapter['position'] != $num_chapters) { ?>
						<a href="<?php echo WB_URL; ?>/modules/manual/move_down.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" title="<?php echo $TEXT['MOVE_DOWN']; ?>">
							<img src="<?php echo THEME_URL; ?>/images/down_16.png" border="0" alt="v" />
						</a>
					<?php } ?>
					</td>
					<td width="20">
						<a href="javascript: confirm_link('<?php echo $TEXT['ARE_YOU_SURE']; ?>', '<?php echo WB_URL; ?>/modules/manual/delete_chapter.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>');" title="<?php echo $TEXT['DELETE']; ?>">
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