<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Entrada Chase Payment Implementation
 *
 * Generic Conference implementation. Abstract functions just return true.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Brandon Thorn <bt37@qmed.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 */
class Generic extends BaseConferenceModel{
	
	function __construct($options = false) {
	}
	
	function parseResponse($post,$trans_id){	
		return true;
	}	
	
	function printForm(){
		return true;
	}
	
	function buildURL($action){
		return true;
	}
	
}

?>
