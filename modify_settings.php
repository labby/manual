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

// Include WB admin wrapper script
require(LEPTON_PATH.'/modules/admin.php');

// include core functions of WB 2.7 to edit the optional module CSS files (frontend.css, backend.css)
include_once(LEPTON_PATH .'/framework/summary.module_edit_css.php');


// Load Language file
if(LANGUAGE_LOADED) {
	if(!file_exists(LEPTON_PATH.'/modules/manual/languages/'.LANGUAGE.'.php')) {
		require_once(LEPTON_PATH.'/modules/manual/languages/EN.php');
	} else {
		require_once(LEPTON_PATH.'/modules/manual/languages/'.LANGUAGE.'.php');
	}
}

// Set raw html <'s and >'s to be replace by friendly html code
$raw = array('<', '>');
$friendly = array('&lt;', '&gt;');

// check if backend.css file needs to be included into the <body></body> of modify.php
if(!method_exists($admin, 'register_backend_modfiles') && file_exists(LEPTON_PATH ."/modules/form/backend.css")) {
	echo '<style type="text/css">';
	include(LEPTON_PATH .'/modules/form/backend.css');
	echo "\n</style>\n";
}

if (!defined('WYSIWYG_EDITOR') OR WYSIWYG_EDITOR=="none" OR !file_exists(LEPTON_PATH.'/modules/'.WYSIWYG_EDITOR.'/include.php')) {
	function show_wysiwyg_editor($name,$id,$content,$width,$height) {
		echo '<textarea name="'.$name.'" id="'.$id.'" style="width: '.$width.'; height: '.$height.';">'.$content.'</textarea>';
	}
} else {
	$id_list=array("short","long");
			require(LEPTON_PATH.'/modules/'.WYSIWYG_EDITOR.'/include.php');
}

// Get header and footer
$query_content = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_manual_settings WHERE section_id = '$section_id'");
$fetch_content = $query_content->fetchRow();
$header = htmlspecialchars($fetch_content['header']);
$footer = htmlspecialchars($fetch_content['footer']);

?>
<h2><?php echo $MLTEXT['SETTINGS']; ?></h2>
<?php
// include the button to edit the optional module CSS files (function added with WB 2.7)
// Note: CSS styles for the button are defined in backend.css (div class="mod_moduledirectory_edit_css")
// Place this call outside of any <form></form> construct!!!
if(function_exists('edit_module_css')) {
	edit_module_css('manual');
}
?>


<form name="modify" action="<?php echo LEPTON_URL; ?>/modules/manual/save_settings.php" method="post" style="margin: 0;">

<input type="hidden" name="section_id" value="<?php echo $section_id; ?>">
<input type="hidden" name="page_id" value="<?php echo $page_id; ?>">

<table class="row_a" cellpadding="2" cellspacing="0" border="0" width="100%">
<tr>
	<td class="setting_name" ><?php echo $TEXT['HEADER']; ?>:</td>
</tr>
<tr>
	<td>
		<?php
		show_wysiwyg_editor("header","header",$header,"100%","235px");
		?>
	</td>
</tr>
<tr>
	<td class="setting_name" ><?php echo $TEXT['FOOTER']; ?>:</td>
</tr>
<tr>
	<td>
		<?php
		show_wysiwyg_editor("footer","footer",$footer,"100%","235px");
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

// Print admin footer
$admin->print_footer();

?>