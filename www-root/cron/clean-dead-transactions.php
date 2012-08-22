<?php

/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Cron job responsible for marking dead transactions and rolling back inventory for any associated items.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Brandon Thorn <brandon.thorn@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 *
*/
@set_time_limit(0);
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


$now = time();
$query = "  SELECT * FROM `payment_transactions` WHERE `payment_status` = 'pending' AND `updated_date` < ".$db->qstr($now);
$results = $db->GetAll($query);
if ($results) {
	$p = PaymentFactory::getPaymentModel('generic');
	foreach($results as $result){
		$p->updatePaymentStatus("expired",$result["ptransaction_id"]);
		application_log("cron", "Action: Updated Transaction [". $result["ptransaction_id"]."] to expired. Pending since ".date(DEFAULT_DATE_FORMAT,$result["updated_date"]));
	}
}
?>