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

class manual_position

{    
    public static function move_to( $aChapterID=0, $aTargetPosition=0 )
    {
  		$database = LEPTON_database::getInstance();
  		
  		if( 0 === $aChapterID) return false;
  		if( 0 === $aTargetPosition) return false;
  		
  		$chapter_info = array();
  		$database->execute_query(
  			"SELECT `position`,`parent`,`section_id` FROM `".TABLE_PREFIX."mod_manual_chapters` WHERE `chapter_id`=".$aChapterID,
  			true,
  			$chapter_info,
  			false
  		);  
    	
    	if( 0 === count($chapter_info)) return false;
    	
    	$actual_position = $chapter_info['position'];
    	
    	if($actual_position === $aTargetPosition) return false;
    	
    	$sql_condition =" AND `section_id`=".$chapter_info['section_id']." AND `parent`=".$chapter_info['parent']." AND `chapter_id` <> ".$aChapterID;
    	
    	if( $actual_position < $aTargetPosition)
    	{
    		// move "up" to a "heighter" position
    		$query = "update `".TABLE_PREFIX."mod_manual_chapters` SET `position`=(position -1) WHERE `position` > ".$actual_position." AND `position` <= ".$aTargetPosition.$sql_condition;

    	} else {

    		// move "down" to a "lower" position
    		$query = "update `".TABLE_PREFIX."mod_manual_chapters` SET `position`=(position +1) WHERE `position` < ".$actual_position." AND `position` >= ".$aTargetPosition.$sql_condition; 		
    	}
    	
    	$database->simple_query( $query );
    	
    	$database->simple_query("update `".TABLE_PREFIX."mod_manual_chapters` set `position`= ".$aTargetPosition." WHERE `chapter_id`=".$aChapterID);
    	
    	return true;
    }

	public static function rearrange( $aSectionID = 0, $aRootID = 0 )
	{
		$database = LEPTON_database::getInstance();
		
		$all = array();
		$database->execute_query(
			"SELECT `chapter_id`,`position` FROM `".TABLE_PREFIX."mod_manual_chapters` WHERE (`parent`=".$aRootID." AND `section_id`=".$aSectionID.") ORDER BY `position`",
			true,
			$all,
			true
		);
		
		$new_position = 1;
		foreach($all as $chapter)
		{
			$database->simple_query(
				"UPDATE `".TABLE_PREFIX."mod_manual_chapters` set `position`=? WHERE `chapter_id`=?",
				array( $new_position, $chapter['chapter_id'] )
			);
			$new_position++;
			
			//	Any "sub-chapters" for this root-element?
			self::rearrange( $aSectionID, $chapter['chapter_id'] );
		}
	}
}
