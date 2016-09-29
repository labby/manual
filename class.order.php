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
/*

Ordering class

This class will be used to change the order of an item in a table
which contains a special order field (type must be integer)

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

define('ORDERING_CLASS_LOADED', true);

// Load the other required class files if they are not already loaded
if(!defined('DATABASE_CLASS_LOADED')) {
	require_once(LEPTON_PATH."/framework/class.database.php");
}

class order {
	
	// Get the db values
	function __construct($table, $order_field, $id_field = 'id', $common_field, $section_id) {
		$this->table = $table;
		$this->order_field = $order_field;
		$this->id_field = $id_field;
		$this->common_field = $common_field;
		$this->section_id = $section_id;
	}
	
	// Move a row up
	function move_up($id) {
		global $database;
		$section_id = $this->section_id;
		// Get current order
		$query_order = "SELECT ".$this->order_field.",".$this->common_field." FROM ".$this->table." WHERE ".$this->id_field." = '$id'";
		$get_order = $database->query($query_order);
		$fetch_order = $get_order->fetchRow();
		$order = $fetch_order[$this->order_field];
		$parent = $fetch_order[$this->common_field];
		// Find out what row is before current one
		$query_previous = "SELECT ".$this->id_field.",".$this->order_field." FROM ".$this->table." WHERE ".$this->order_field." < '$order' AND ".$this->common_field." = '$parent' AND section_id = '$section_id' ORDER BY ".$this->order_field." DESC LIMIT 1";
		$get_previous = $database->query($query_previous);
		if($get_previous->numRows() > 0) {
			// Change the previous row to the current order
			$fetch_previous = $get_previous->fetchRow();
			$previous_id = $fetch_previous[$this->id_field];
			$decremented_order = $fetch_previous[$this->order_field];
			$query = "UPDATE ".$this->table." SET ".$this->order_field." = '$order' WHERE ".$this->id_field." = '$previous_id' LIMIT 1";
			$database->query($query);
			// Change the row we want to the decremented order
			$query = "UPDATE ".$this->table." SET ".$this->order_field." = '$decremented_order' WHERE ".$this->id_field." = '$id' LIMIT 1";
			$database->query($query);
			
			if($database->is_error()) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
	// Move a row up
	function move_down($id) {
		global $database;
		$section_id = $this->section_id;
		// Get current order
		$query_order = "SELECT ".$this->order_field.",".$this->common_field." FROM ".$this->table." WHERE ".$this->id_field." = '$id'";
		$get_order = $database->query($query_order);
		$fetch_order = $get_order->fetchRow();
		$order = $fetch_order[$this->order_field];
		$parent = $fetch_order[$this->common_field];
		// Find out what row is before current one
		$query_next = "SELECT $this->id_field,".$this->order_field." FROM ".$this->table." WHERE ".$this->order_field." > '$order' AND ".$this->common_field." = '$parent' AND section_id = '$section_id' ORDER BY ".$this->order_field." ASC LIMIT 1";
		$get_next = $database->query($query_next);
		if($get_next->numRows() > 0) {
			// Change the previous row to the current order
			$fetch_next = $get_next->fetchRow();
			$next_id = $fetch_next[$this->id_field];
			$incremented_order = $fetch_next[$this->order_field];
			$query = "UPDATE ".$this->table." SET ".$this->order_field." = '$order' WHERE ".$this->id_field." = '$next_id' LIMIT 1";
			$database->query($query);
			// Change the row we want to the decremented order
			$query = "UPDATE ".$this->table." SET ".$this->order_field." = '$incremented_order' WHERE ".$this->id_field." = '$id' LIMIT 1";
			$database->query($query);
			if($database->is_error()) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
	
	// Get new number for order
	function get_new($cf_value) {
		global $database;
		$section_id = $this->section_id;
		// Get last order
		$query_last = "SELECT ".$this->order_field." FROM ".$this->table." WHERE ".$this->common_field." = '$cf_value' AND section_id = '$section_id' ORDER BY ".$this->order_field." DESC LIMIT 1";
		$get_last = $database->query($query_last);
		if($get_last->numRows() > 0) {
			$fetch_last = $get_last->fetchRow();
			$last_order = $fetch_last[$this->order_field];
			return $last_order+1;
		} else {
			return 1;
		}
	}
	
	// Clean ordering (should be called if a row in the middle has been deleted)
	function clean($cf_value) {
		global $database;
		$section_id = $this->section_id;
		// Loop through all records and give new order
		$query_all = "SELECT * FROM ".$this->table." WHERE ".$this->common_field." = '$cf_value' AND section_id = '$section_id' ORDER BY ".$this->order_field." ASC";
		$get_all = $database->query($query_all);
		if($get_all->numRows() > 0) {
			$count = 1;
			while($row = $get_all->fetchRow()) {
				// Update row with new order
				$database->query("UPDATE ".$this->table." SET ".$this->order_field." = '$count' WHERE ".$this->id_field." = '".$row[$this->id_field]."'");
				$count = $count+1;
			}
		} else {
			 return true;
		}
	}
	
}

?>