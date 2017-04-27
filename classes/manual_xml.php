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

class manual_xml extends manual
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
    
    public function toXML( $all_chapters )
    {
    
    	$xml = "<?xml version='1.0' standalone='yes'?>\n";
    	$xml .= "<chapters>";
    	foreach( $all_chapters as $key=>$chapter)
    	{
    		$block = "\t<chapter>\n";
    		$block .= "\t\t<title>".$chapter['title']."</title>\n";
    		$block .= "\t\t<description>".htmlentities($chapter['description'])."\n\t\t</description>\n";
    		$block .= "\t\t<content>".htmlentities($chapter['content'])."\n\t\t</content>\n";	
    		$block .= "\t</chapter>\n";
    		
    		$xml .= $block;	
    	}
    	$xml .= "</chapters>\n";
    	
    	return $xml;
    }
}