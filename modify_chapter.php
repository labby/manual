<?php

/**
 *  @module         manual
 *  @version        see info.php of this module
 *  @authors        Ryan Djurovich, Chio Maisriml, Thomas Hornik, Dietrich Roland Pehlke
 *  @copyright      2004-2016 Ryan Djurovich, Matthias Gallas, Uffe Christoffersen, pcwacht, Rob Smith, Aldus, erpe
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

// Get id
if(!isset($_GET['chapter_id']) OR !is_numeric($_GET['chapter_id'])) {
	header("Location: ".ADMIN_URL."/pages/index.php");
} else {
	$chapter_id = $_GET['chapter_id'];
}

// Include WB admin wrapper script
require(LEPTON_PATH.'/modules/admin.php');

// Get header and footer
$query_content = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters WHERE chapter_id = '$chapter_id'");
$fetch_content = $query_content->fetchRow();
$chapter_id = $fetch_content['chapter_id'];
$content = (htmlspecialchars($fetch_content['content']));

if (!defined('WYSIWYG_EDITOR') OR WYSIWYG_EDITOR=="none" OR !file_exists(LEPTON_PATH.'/modules/'.WYSIWYG_EDITOR.'/include.php')) {
	function show_wysiwyg_editor($name,$id,$content,$width,$height) {
		echo '<textarea name="'.$name.'" id="'.$id.'" style="width: '.$width.'; height: '.$height.';">'.$content.'</textarea>';
	}
} else {
	$id_list=array("content");
			require(LEPTON_PATH.'/modules/'.WYSIWYG_EDITOR.'/include.php');
}
?>

<?php // include jscalendar-setup
	$jscal_use_time = true; // whether to use a clock, too
	require_once(LEPTON_PATH."/include/jscalendar/wb-setup.php");
?>

<form name="modify" action="<?php echo LEPTON_URL; ?>/modules/manual/save_chapter.php" method="post" style="margin: 0;">

<input type="hidden" name="section_id" value="<?php echo $section_id; ?>">
<input type="hidden" name="page_id" value="<?php echo $page_id; ?>">
<input type="hidden" name="chapter_id" value="<?php echo $chapter_id; ?>">
<input type="hidden" name="link" value="<?php echo $fetch_content['link']; ?>">
<input type="hidden" name="position" value="<?php echo $fetch_content['position']; ?>">
<input type="hidden" name="old_parent" value="<?php echo $fetch_content['parent']; ?>">
<?php
// Get if current chapter is parent?
$current_level = '0'; // Asume no children
$chapter_parent2 = $chapter_parent = 0;
$query = "SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters  WHERE parent = '$chapter_id'";
$get_chapters = $database->query($query);
$get_chapter = $get_chapters->fetchRow();
$chapter_parent = $get_chapter['chapter_id'];
if ($chapter_parent!=0) {
	$current_level = '1'; // Asume 1 children
	// Is parent allso parent?
	$query = "SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters  WHERE parent = '$chapter_parent'";
	$get_chapters = $database->query($query);
	$get_chapter = $get_chapters->fetchRow();
	$chapter_parent2 = $get_chapter['chapter_id'];
	if ($chapter_parent2!=0) {
 		$current_level = '2';
	}
}
?>

<table cellpadding="4" cellspacing="0" border="0" width="100%">
<tr>
	<td width="80"><?php echo $TEXT['TITLE']; ?>:</td>
	<td>
		<input type="text" name="title" value="<?php echo stripslashes($fetch_content['title']); ?>" style="width: 100%;" maxlength="255" />
	</td>
</tr>
<tr>
	<td><?php echo $TEXT['PARENT']; ?>:</td>
	<td>
		<select name="parent" style="width: 100%;">
			<option value=""><?php echo $TEXT['NONE']; ?></option>
			<?php
function parent_list($parent) {
	global $admin, $database, $section_id, $fetch_content, $current_level;
	$query = "SELECT * FROM ".TABLE_PREFIX."mod_manual_chapters  WHERE parent = '$parent' AND active = '1' AND section_id = '$section_id' AND title != '' ORDER BY position ASC";
	$get_chapters = $database->query($query);
	while($chapter = $get_chapters->fetchRow()) {
		// Stop users from adding pages with a level of more than the set page level limit
		if($chapter['level']+1 < (3-$current_level) ) { // CHAPTER_LEVEL_LIMIT
			// Title -'s prefix
			$title_prefix = '';
			for($i = 1; $i <= $chapter['level']; $i++) { $title_prefix .= ' - '; }
				if ($fetch_content['chapter_id'] <> $chapter['chapter_id']) {
					$title = $title_prefix.$chapter['title'];
					echo '<option value="'.$chapter['chapter_id'].'"';
					if($fetch_content['parent'] == $chapter['chapter_id']) { echo 'selected'; } 
					echo '>'.$title.'</option>';
				}
		parent_list($chapter['chapter_id']);
		}
	}
}
parent_list(0);
			?>
		</select>
	</td>
</tr>



<tr>
	<td><?php echo $TEXT['ACTIVE']; ?>:</td>
	<td>
		<input type="radio" name="active" id="active_true" value="1" <?php if($fetch_content['active'] == 1) { echo ' checked'; } ?> />
		<a href="#" onclick="javascript: document.getElementById('active_true').checked = true;">
		<?php echo $TEXT['YES']; ?>
		</a>
		-
		<input type="radio" name="active" id="active_false" value="0" <?php if($fetch_content['active'] == 0) { echo ' checked'; } ?> />
		<a href="#" onclick="javascript: document.getElementById('active_false').checked = true;">
		<?php echo $TEXT['NO']; ?>
		</a>
	</td>
</tr>
<tr>
	<td><?php echo $TEXT['USERNAME']; ?>:</td>
	<td>
		<select name="modified_by" style="width: 120px;">
			<?php
			$query = "SELECT user_id, username, display_name FROM ".TABLE_PREFIX."users ORDER BY username";
			$users = $database->query($query);
			if($users->numRows() > 0) {
				while($user = $users->fetchRow()) {
					?>
					<option value="<?php echo $user['user_id']; ?>"<?php if($fetch_content['modified_by'] == $user['user_id']) { echo ' selected'; } ?>><?php echo $user['display_name']; ?></option>
					<?php
				}
			}
			?>
		</select>
	</td>
</tr>
<tr>
	<td><?php echo $TEXT['DATE']; ?>:</td>
	<td><input type="text" id="modified_when" name="modified_when" value="<?php if($fetch_content['modified_when']==0) echo ""; else echo date($jscal_format, $fetch_content['modified_when'])?>" style="width: 120px;" />
		<img src="<?php echo THEME_URL ?>/images/clock_16.png" id="trigger_start" style="cursor: pointer;" title="<?php echo $TEXT['CALENDAR']; ?>" onmouseover="this.style.background='lightgrey';" onmouseout="this.style.background=''" />
		<img src="<?php echo THEME_URL ?>/images/clock_del_16.png" style="cursor: pointer;" title="<?php echo $TEXT['DELETE_DATE']; ?>" onmouseover="this.style.background='lightgrey';" onmouseout="this.style.background=''" onclick="document.modify.modified_when.value=''" />
	</td>
</tr>
<tr>
	<td valign="top"><?php echo $TEXT['DESCRIPTION']; ?>:</td>
	<td>
		<textarea id="no_wysiwyg" name="description" style="width: 100%; height: 50px;"><?php echo stripslashes($fetch_content['description']); ?></textarea>
	</td>
</tr>
<tr>
	<td colspan="2">
		<?php
			show_wysiwyg_editor("content","content",$content,"100%","400px");
		?>
	</td>
</tr>
</table>

<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td align="left">
			<input name="save" type="submit" value="<?php echo $TEXT['SAVE']; ?>" style="width: 100px; margin-top: 5px;"></form>
		</td>
		<td align="right">
			<input type="button" value="<?php echo $TEXT['CANCEL']; ?>" onclick="javascript: window.location = '<?php echo ADMIN_URL; ?>/pages/modify.php?page_id=<?php echo $page_id; ?>';" style="width: 100px; margin-top: 5px;" />
		</td>
	</tr>
</table>

<?php
// now add the calendar -- remember to to set the range to [1970, 2037] if the date is used as timestamp!
?>
<script type="text/javascript">
	Calendar.setup(
		{
			inputField  : "modified_when",
			ifFormat    : "<?php echo $jscal_ifformat ?>",
			button      : "trigger_start",
			firstDay    : <?php echo $jscal_firstday ?>,
			<?php if(isset($jscal_use_time) && $jscal_use_time==TRUE) { ?>
				showsTime   : "true",
				timeFormat  : "24",
			<?php } ?>
			date        : "<?php echo $jscal_today ?>",
			range       : [2000, 2037],
			step        : 1
		}
	);
</script>

<?php

// Print admin footer
$admin->print_footer();

?>