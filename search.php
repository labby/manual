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
 
function manual_search($func_vars) {
	extract($func_vars, EXTR_PREFIX_ALL, 'func');

	// how many lines of excerpt we want to have at most
	$max_excerpt_num = $func_default_max_excerpt;
	$divider = ".";
	$result = false;

	// fetch all active articles-posts (from active groups) in this section.
	$t = time();
	$table = TABLE_PREFIX."mod_manual_chapters";
	$query = $func_database->query("
		SELECT *
		FROM $table
		WHERE section_id='$func_section_id' AND active='1'
	");

	// now call print_excerpt() for every single post
	if($query->numRows() > 0) {
		while($res = $query->fetchRow()) {
			$mod_vars = array(
				'page_link' =>  $res['link'], // use direct link to manual-item
				'page_link_target' => "",
				'page_title' => $res['title'],
				'page_description' => $res['title'], // use manual-title as description
				'page_modified_when' => $res['modified_when'],
				'page_modified_by' => $res['modified_by'],
				'text' => $res['content'].$divider,
				'max_excerpt_num' => $max_excerpt_num
			);
			
			if(print_excerpt2($mod_vars, $func_vars)) {
				$result = true;
			}
		}
	}

	return $result;
}

?>
