<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Entrada Chase Payment Implementation
 *
 * Implementation of the Chase Payment Model.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Brandon Thorn <bt37@qmed.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 */
class Generic extends BasePaymentModel{
	
	function __construct($options = false) {
	  $this->payment_type = 'generic';
	}
	
	function parseResponse($post,$trans_id){	
	}	
	
	function printForm(){

	}
	
}

?>
