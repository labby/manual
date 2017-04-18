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

}


