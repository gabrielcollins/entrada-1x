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
 * This file is used to display events from the entrada.events table.
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
} else {
	$PAYMENT_ID = 0;
	$ACTION = false;
	$BREADCRUMB[] = array("url" => ENTRADA_RELATIVE."/payments", "title" => "Payments");	
	if (isset($_GET["id"]) && $id = (int)$_GET["id"]) {
		$PAYMENT_ID = $id;
	}
	if (isset($_GET["action"]) && $action = clean_input($_GET["action"],array("trim","notags"))) {
		$ACTION = $action;
	}
	
	if ($PAYMENT_ID) {
		$BREADCRUMB[] = array("url" => ENTRADA_RELATIVE."/payments?id=".$PAYMENT_ID, "title" => "Transaction ".$PAYMENT_ID);	
		?>
		<h1>Transaction <?php echo "#".$PAYMENT_ID;?></h1>
		<?php		
		$query = "	SELECT * FROM `payment_transactions` WHERE `ptransaction_id` = ".$db->qstr($PAYMENT_ID)." AND `proxy_id` = ".$db->qstr($ENTRADA_USER->getId());
		if ($payment_details = $db->GetRow($query)) {
			if ($ACTION) {
				switch ($ACTION) {
					case 'cancel':
						if($db->AutoExecute("payment_transactions",array('payment_status'=>'cancelled'),"UPDATE","`ptransaction_id` = ".$db->qstr($PAYMENT_ID))){
							
							$query = "	SELECT a.`proxy_id`, c.* FROM 
										`payment_transactions` a
										JOIN `payment_transaction_items` b
										ON a.`ptransaction_id` = b.`ptransaction_id`
										JOIN `payment_catalog` c
										ON b.`ptransaction_id` = ".$db->qstr($PAYMENT_ID)."
										AND b.`pcatalog_id` = c.`pcatalog_id`";
							if($catalog_items = $db->GetAll($query)) {
								foreach($catalog_items as $catalog_item){
									if($catalog_item["quantity"] != -1){
										$db->AutoExecute("payment_catalog",array("quantity"=>($catalog_item["quantity"]+1)),"UPDATE","`pcatalog_id` = ".$db->qstr($catalog_item["pcatalog_id"]));
									}
									switch($catalog_item["item_type"]){
										case 'course':
											$query = "	DELETE FROM `course_audience` 
														WHERE `course_id` = ".$db->qstr($catalog_item["item_value"])." 
														AND `audience_value` = ".$db->qstr($catalog_item["proxy_id"])." 
														AND `audience_active` = '2'";
											$db->Execute($query);
											break;
										default:
											break;
									}
								}
							}	
							echo display_success("Successfully cancelled transaction.");
						}
						break;
					default:
						echo display_error("Invalid action provided.");
						break;
				}
			} else {
			?>
				<ul class="unstyled">
					<li><strong>Status:</strong> <?php echo ucwords($payment_details["payment_status"]);?></li>
					<li><strong>Payment Created:</strong> <?php echo date(DEFAULT_DATE_FORMAT,$payment_details["created_date"]);?></li>
					<?php if($payment_details["created_date"] != $payment_details["updated_date"]) { ?>
					<li><strong>Payment Updated:</strong> <?php echo date(DEFAULT_DATE_FORMAT,$payment_details["created_date"]);?></li>
					<?php } ?>
				</ul>
				<?php
				$query = "	SELECT * FROM `payment_transaction_items` a
							JOIN `payment_catalog` b
							ON a.`pcatalog_id` = b.`pcatalog_id`
							WHERE a.`ptransaction_id` = ".$db->qstr($PAYMENT_ID);
				if ($payment_items = $db->GetAll($query)) { ?>
					<table class="tableList">
						<colgroup>
							<col class="modified">				
							<col class="general">				
							<col class="general">				
						</colgroup>
						<thead>
							<tr>
								<td class="modified">&nbsp;</td>
								<td class="general">Item</td>
								<td class="general">Item Cost</td>
							</tr>
						</thead>
						<tbody>
					<?php	
							$total = 0;
							foreach($payment_items as $payment_item){ 
								switch($payment_item["item_type"]){
									case 'course':
										$query = "SELECT `course_name` FROM `courses` WHERE `course_id` = ".$db->qstr($payment_item["item_value"]);
										if ($name = $db->GetOne($query)) {
											$display_name = "<strong>Course:</strong> ".$name;
										} else {
											$display_name = "Course";
										}
										break;
									default:
										$display_name = 'Default'.ucwords($payment_item["item_type"]);
										break;
								}
								?>
							<tr>
								<td>&nbsp;</td>
								<td><?php echo $display_name; ?></td>
								<td><?php echo '$'.ucwords($payment_item["item_cost"]); ?></td>
							</tr>

					<?php	
							$total += $payment_item["item_cost"];
							} ?>
							<tr>
								<td></td>
								<td><strong>Total Cost:</strong></td>
								<td><?php echo '$'.$total;?></td>
							</tr>
						</tbody>
					</table>
				<?php
				} else {
					echo display_notice("No items where recorded on this transaction.");
				}
			}
		} else {
			echo display_error("You do not have access to this transaction.");
		}
	} else {
		?>
		<h1>Purchases</h1>
		<?php
		$query = "	SELECT * FROM `payment_transactions` WHERE `proxy_id` = ".$db->qstr($ENTRADA_USER->getId())." ORDER BY `updated_date` DESC";
		if ($payments = $db->GetAll($query)) {
			?>
			<table class="tableList">
				<colgroup>
					<col class="modified">
					<col class="general">
					<col class="general">
					<col class="general">
					<col class="date">
					<col class="date">
				</colgroup>			
				<thead>
					<tr>
						<td class="modified">&nbsp;</td>
						<td class="general">Payment ID</dh>
						<td class="general">Payment Method</td>
						<td class="general">Payment Status</td>
						<td class="date">Payment Date</td>
						<td class="date">Last Update</td>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach($payments as $payment) {
						?>
					<tr>
						<td></td>
						<td><a href="<?php echo ENTRADA_URL."/payments?id=".$payment["ptransaction_id"];?>"><?php echo "#".$payment["ptransaction_id"];?></a></td>
						<td><a href="<?php echo ENTRADA_URL."/payments?id=".$payment["ptransaction_id"];?>"><?php echo $payment["payment_method"];?></a></td>
						<td><a href="<?php echo ENTRADA_URL."/payments?id=".$payment["ptransaction_id"];?>"><?php echo ucwords($payment["payment_status"]);?></a></td>
						<td><a href="<?php echo ENTRADA_URL."/payments?id=".$payment["ptransaction_id"];?>"><?php echo date(DEFAULT_DATE_FORMAT,$payment["created_date"]);?></a></td>
						<td><a href="<?php echo ENTRADA_URL."/payments?id=".$payment["ptransaction_id"];?>"><?php echo date(DEFAULT_DATE_FORMAT,$payment["updated_date"]);?></a></td>
					</tr>
						<?php
					}
					?>
				</tbody>
			</table>
			<?php		
		} else {
			echo display_notice("No Transactions Have Occured.");
		}
	}
	
}