<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Entrada Base Payment Model Class
 *
 * Used as the blueprint for Payment models. Cannot be instantiated.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Brandon Thorn <bt37@qmed.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 */
abstract class BasePaymentModel{

	private $payment_amount = 0;
	private $payment_destination = '';
	
	abstract function makePayment();
	
	/**
	 * Sets/returns the value for the payment amount.
	 * @param type $amount
	 * @return type 
	 */
	public function paymentAmount($amount = false){
		if ($amount) {
			$payment_amount = $amount;
		}
		return $payment_amount;
	}
	
	/**
	 * Sets/returns the value for the payment destination
	 * @param type $dest
	 * @return type 
	 */
	public function paymentDest($dest = false){
		if ($dest) {
			$payment_destination = $dest;
		}
		return $payment_destination;
	}
	
}

?>
