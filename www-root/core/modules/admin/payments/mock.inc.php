<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Entrada is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Entrada is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Entrada.  If not, see <http://www.gnu.org/licenses/>.
 *
 * This file is used to mock payment data locally.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Brandon Thorn <brandon.thorn@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 *
*/

if (!defined("PARENT_INCLUDED")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_RELATIVE);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("event", "read", false)) {
	add_error("Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} elseif (false) {// || !defined('DEVELOPMENT_MODE') || !DEVELOPMENT_MODE) {
	add_error("This module is only available in development environments.");
	echo display_error();

	application_log("error", "Payment data mocker being accessed in production mode.");	
} else {
	$BREADCRUMB[] = array("url" => ENTRADA_RELATIVE."/payments?section=add", "title" => "Add Offline Transaction");	
	?>
	<h2>Payment Data Mocker</h2>
	<?php 
		echo display_notice("This data mocker only works for the Chase Exact Hosted model and only in development environments. Will flesh it out to work with other systems if the need persists.");
	?>
	<form action="<?php echo ENTRADA_URL."/api/payment_endpoint.api.php?m=chaseexacthosted";?>" method="POST">
		<label for="x_invoice_num">X_Invoice_Num:</label>
		<input name="x_invoice_num"/><br/>
		
		<label for="x_response_code">X_Response_Code:</label>
		<input name="x_response_code"/><br/>
		
		<label for="x_reference_3">X_Reference_3(hash):</label>
		<input name="x_reference_3"/><br/>
		<input name="x_trans_id" type="hidden" value="<?php echo md5("x_trans_id".microtime(true));?>"/>
		
		<input type="submit" value="Submit"/>
	</form>
	
<?php } ?>