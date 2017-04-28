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
 
/**	*******************************
 *	Try to get the template-engine.
 *
 *	Make your basic settings for your module-backend interface(-s) here.
 *
 */

$oTwig = lib_twig_box::getInstance();

$temp = explode( DIRECTORY_SEPARATOR, __DIR__);
$module_directory = array_pop( $temp );

$oTwig->registerPath( LEPTON_PATH."/templates/".DEFAULT_THEME."/backend/".$module_directory."/", $module_directory );

$oTwig->registerPath( dirname(__FILE__)."/templates/backend/", $module_directory );
$oTwig->registerPath( dirname(__FILE__)."/templates/", $module_directory );

if(defined("PAGE_ID"))
{
	$page_template = $database->get_one("SELECT `template` FROM `".TABLE_PREFIX."pages` WHERE `page_id`=".PAGE_ID);
	$oTwig->registerPath( LEPTON_PATH."/templates/".( $page_template == "" ? DEFAULT_TEMPLATE : $page_template)."/frontend/".$module_directory."/", $module_directory );
}	
?>