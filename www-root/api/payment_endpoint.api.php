<?php
/**
 * Payment Endpoint
 * @author Unit: Medical Education Technology Unit
  * @author Developer: Brandon Thorn <brandon.thorn@queensu.ca>
 * @copyright Copyright 2012 Queen's University, MEdTech Unit
*/

@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    dirname(__FILE__) . "/../core/includes",
    dirname(__FILE__) . "/../core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");
/**
 * @todo: figure out a way to add a static getTransactionById() function. 
 * The issue is that the model is decided by the item and if there's more than one it could have different models. 
 */
if (isset($_GET["m"]) && $model = clean_input($_GET["m"], array("trim"))) {
	if ($p = PaymentFactory::getPaymentModel($model)) {
		try {
			if($payment_details = $p->parseResponse($_POST)){
				application_log("payment","[SUCCESS] Successfully updated transaction [".$p->ptransaction_id."]");
				/**
				 * parseResponse() should load the transaction_items variable, but since its implementation specific it can't be assumed that it got loaded
				 */
				
				if (!isset($p->transaction_items) || !is_array($p->transaction_items)) {
					 $p->transaction_items = $p->fetchTransactionItems();
				}

				if (count($p->transaction_items) > 1) {
					header("Location: ".ENTRADA_URL."/payments");
				} else {	
					switch($p->transaction_items[0]["item_type"]){
						case 'course':
							$url = "/courses?id=".$p->transaction_items[0]["item_value"];
							break;
						default:
							$url = "/payments";
							break;
					}
					header("Location: ".ENTRADA_URL.$url);
				}

			}
			header("Location: ".ENTRADA_URL."/payments");
		} catch(Exception $e) {
			application_log("payment","[ERROR] Error occurred while processing payment request from ".$_SERVER["REMOTE_ADDR"].". Error message: ".$e->getMessage());
		}
	} else {
		echo "Invalid data provided.";
		application_log("payment","[ERROR] Error occurred while processing payment request from ".$_SERVER["REMOTE_ADDR"].". Missing or invalid model parameter provided. Paramter provided: ".$model);
	}
} else {
	echo "You are not authorized to access this file or have provided invalid data.";
	application_log("payment","[ERROR] Error occurred while processing payment request from ".$_SERVER["REMOTE_ADDR"].". Missing or invalid model parameter provided.");
}
exit;