<?php

/**
 *  @module         news
 *  @version        see info.php of this module
 *  @author         Ryan Djurovich, Rob Smith, Dietrich Roland Pehlke, Christian M. Stefan (Stefek), Jurgen Nijhuis (Argos), LEPTON Project
 *  @copyright      2004-2010 Ryan Djurovich, Rob Smith, Dietrich Roland Pehlke, Christian M. Stefan (Stefek), Jurgen Nijhuis (Argos) 
 * 	@copyright      2010-2016 LEPTON Project 
 *  @license        GNU General Public License
 *  @license terms  see info.php of this module
 *  @platform       see info.php of this module
 * 
 */

global $lepton_filemanager;
if (!is_object($lepton_filemanager)) require_once( "../../framework/class.lepton.filemanager.php" );

$basepath = "/modules/manual/";

$files_to_register = array(
	$basepath.'add_chapter.php',	
	$basepath.'add.php',
	$basepath.'class.order.php',
	$basepath.'delete_chapter.php',
	$basepath.'delete.php',
	$basepath.'install.php',
	$basepath.'modify_chapter.php',
	$basepath.'modify_settings.php',
	$basepath.'move_down.php',
	$basepath.'move_up.php',
	$basepath.'save_chapter.php',
	$basepath.'save_settings.php',
	$basepath.'uninstall.php',
	$basepath.'update_manual.php',
	$basepath.'view.php'
);

$lepton_filemanager->register( $files_to_register );

?>