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

class manual
{
	/**
     * @var Singleton The reference to *Singleton* instance of this class
     */
    private static $instance;
	
	/**
	 *	Return the »internal« instance
	 *
	 */
	public static function getInstance()
    {
        if (null === static::$instance)
        {
            static::$instance = new static();
        }
        
        return static::$instance;
    }
    
    public function get_manual_by_sectionID( $iSecId = 0 )
    {
    
    	$database = LEPTON_database::getInstance();
    	$all = array();
    	$database->execute_query(
    		"SELECT * FROM `".TABLE_PREFIX."mod_manual_chapters` WHERE `section_id`=".$iSecId." ORDER BY `parent`,`position`",
    		true,
    		$all,
    		true
    	);
    	
    	$aRetVal = array();
    	foreach($all as &$ref)
    	{
    		$aRetVal[ $ref['chapter_id'] ] = $ref;	
    	}
    	
    	return $aRetVal;
    }
    
    public function test_root( $sPath="")
    {
    	// $full_filepath = LEPTON_PATH.PAGES_DIRECTORY.$page_link.$temp_root."/";
    	$basepath = LEPTON_PATH.PAGES_DIRECTORY;
    	$sub_part = str_replace( LEPTON_PATH.PAGES_DIRECTORY."/", "", $sPath);
    	$elements = explode("/", $sub_part);
    	foreach($elements as $folder){
    		$basepath .= "/".$folder;
    		if(!file_exists($basepath))
    		{
    			if(true === mkdir( $basepath, 0755 ))
    			{
    				copy(
    					LEPTON_PATH."/backend/pages/master_index.php",
    					$basepath."/index.php"
    				);
    			}
    		}
    	}
    }
    
    public function get_root( &$allChapters, $aChapterID)
    {
    	$root = "";
    	do
    	{
    		$parent = $allChapters[ $aChapterID ]['parent'];
    		$root = $allChapters[ $aChapterID ]['link'].$root;
    		$aChapterID = $parent;
    	} while ( $parent != 0 );
    	
    	return $root;
    }
    
    public function get_sub_chapters( &$allChapters, $aChapterID)
    {
    	$returnVal = array();
    	foreach($allChapters as $key=>$data) {
    		if($data['parent'] === $aChapterID)
    		{
    			$returnVal[ $key ] = $data;
    		}
    	}
   		return $returnVal; 
    }

	public function get_siblings( $aParentID = 0)
	{
		$database = LEPTON_database::getInstance();
		$all = array();
		$database->execute_query(
			"SELECT `chapter_id`,`link`,`position`,`title` FROM `".TABLE_PREFIX."mod_manual_chapters` WHERE `parent`=".$aParentID." AND `active`='1' ORDER BY `position`",
			true,
			$all,
			true
		);
		return $all;
	}
	
	public function build_tree( &$allChapters, &$aTreeStorage=array() , $aChapterID=0)
	{
		global $wb;
		foreach($allChapters as $key => $currentChapter)
		{
			if($currentChapter['parent'] == $aChapterID)
			{
				//	Build the comlete link incl. the "root"
				$currentChapter['link']	= page_link( $wb->page['link']. $this->get_root( $allChapters, $key ) );
				
				//	get subchapters
				$currentChapter['subchapters'] = array();
				
				/*
				foreach($sub_chapters as $subkey => $subdata) {
					// $this->build_tree( $allChapters, $currentChapter['subchapters'], $subdata['chapter_id']);
					if(!isset($currentChapter['subchapters'][ $subkey ]))
					{
						$subdata['link'] = page_link( $wb->page['link']. $this->get_root( $allChapters, $subkey ) );
						$subdata['subchapters']	= array();
						$currentChapter['subchapters'][ $subkey ] = $subdata;
					
					}
				}
				*/
				
				$aTreeStorage[ $key ] = $currentChapter;
				
				$sub_chapters = $this->get_sub_chapters( $allChapters, $currentChapter['chapter_id']);
				foreach($sub_chapters as $subkey => $subdata) {
					// $this->build_tree( $allChapters, $aTreeStorage[ $key ]['subchapters'], $subdata['chapter_id']);
					
					$subdata['link'] = page_link( $wb->page['link']. $this->get_root( $allChapters, $subkey ) );
					$subdata['subchapters']	= array();
					
					$this->build_tree( $allChapters, $subdata['subchapters'], $subdata['chapter_id']);
					
					$aTreeStorage[ $key ]['subchapters'][ $subkey ] = $subdata;
					
					
				}
			}
		}
	}
}


