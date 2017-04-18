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

// Get id
if(!isset($_GET['chapter_id']) OR !is_numeric($_GET['chapter_id'])) {
	header("Location: ".ADMIN_URL."/pages/index.php");
} else {
	$chapter_id = ($_GET['chapter_id']);
}

// Include WB admin wrapper script
require(LEPTON_PATH.'/modules/admin.php');

$oManual = manual::getInstance();
$all_chapters = $oManual->get_manual_by_sectionID( $section_id );
// die(LEPTON_tools::display($all_chapters));
	
// Get current values for this chapter-id
$fetch_content = $all_chapters[ $chapter_id ];

// $chapter_id = $fetch_content['chapter_id'];
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

function parent_list($parent, $deep=0) {
	global $all_chapters, $chapter_id, $fetch_content;
	$subchapter_marker = "";
	for($i=0; $i< $deep; $i++) $subchapter_marker .= "- ";
	
	foreach($all_chapters as $key=>$val ){
		
		if($key == $parent) continue;
		
		if($parent == $val['parent'])
		{
			echo "\n<option value='".$key."' ".( ($key == $chapter_id) ? " disabled='disabled' " : "").( ($key == $fetch_content['parent']) ? " selected='selected' " : ""  ).">".$subchapter_marker.$val['title']."</option>\n";
		
			if($deep < 3)
			{
				parent_list( $val['chapter_id'], $deep+1);
			}
		}
	}
}
parent_list(0,1);
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