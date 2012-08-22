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
} elseif($PAYMENT_ID) {
	$BREADCRUMB[] = array("url" => ENTRADA_RELATIVE."/admin/payments?id=".$PAYMENT_ID, "title" => "Transaction #".$PAYMENT_ID);	
	$BREADCRUMB[] = array("url" => ENTRADA_RELATIVE."/admin/payments?section=edit&id=".$PAYMENT_ID, "title" => "Edit Transaction");	
	$PROCESSED["organisation_id"] = $ENTRADA_USER->getOrganisationId();
	switch($STEP){
		case 2:

			if (isset($_POST["payment_status"]) && $tmp = clean_input($_POST["payment_status"],array("notags","trim"))) {
				$PROCESSED["payment_status"] = strtolower($tmp);
			} else {
				add_error("The <strong>Payment Status</strong> is a required field.");
			}
			if (isset($_POST["payment_method"]) && $tmp = clean_input($_POST["payment_method"],array("notags","trim"))) {
				$PROCESSED["payment_method"] = strtolower($tmp);
			} else {
				add_error("The <strong>Payment Method</strong> is a required field.");
			}
			
			if (!$ERROR) {
				if($db->AutoExecute("payment_transactions",$PROCESSED,"UPDATE","`ptransaction_id` = ".$PAYMENT_ID)){
					add_success("Successfully updated transaction");
					onload_redirect(ENTRADA_URL."/admin/payments?id=".$PAYMENT_ID);
				} else {
					add_error("Error occurred while updating transaction.");
				}
			}
			
			if($ERROR){
				$STEP = 1;
				$query = "	SELECT *FROM `payment_transactions`
							WHERE `ptransaction_id` = ".$db->qstr($PAYMENT_ID);
				if ($result = $db->GetRow($query)) {
					$PROCESSED["proxy_id"] = $result["proxy_id"];
					$PROCESSED["transaction_amount"] = $result["transaction_amount"];
				}								
			}
			break;
		case 1:
		default:
			$query = "	SELECT *FROM `payment_transactions`
						WHERE `ptransaction_id` = ".$db->qstr($PAYMENT_ID);
			if ($result = $db->GetRow($query)) {
				$PROCESSED["proxy_id"] = $result["proxy_id"];
				$PROCESSED["transaction_amount"] = $result["transaction_amount"];
				$PROCESSED["payment_status"] = $result["payment_status"];
				$PROCESSED["payment_method"] = $result["payment_method"];	
			}
			break;
	}
	
	switch($STEP){
		case 2:
			if($SUCCESS){
				echo display_success();
			}
			if($NOTICE){
				echo display_notice();
			}			
			break;
		case 1:
		default:

			if($ERROR){
				echo display_error();
			}			
	?>

	<h1>Edit Transaction</h1>
	<form action="<?php echo ENTRADA_URL;?>/admin/payments?id=<?php echo $PAYMENT_ID;?>&section=edit&step=2" method="post">
		<table style="width:100%;">
			<colgroup>
				<col style="width: 3%" />
				<col style="width: 20%" />
				<col style="width: 77%" />
			</colgroup>
			<tfoot>
				<tr>
					<td colspan="3" style="padding-top: 25px">
						<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td style="width: 25%; text-align: left">
									<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/payments?id=<?php echo $PAYMENT_ID;?>'" />
								</td>
								<td style="width: 75%; text-align: right; vertical-align: middle">
									<input type="submit" class="button" value="Save" />
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</tfoot>		
			<tbody>
				<tr>
					<td></td>
					<td><label for="cost">Transaction Amount</label></td>
					<td>
						<?php echo '$'.number_format($PROCESSED["transaction_amount"], 2, '.',',');?>
					</td>
				</tr>
				<tr>
					<td></td>
					<td><label for="payment_method" class="form-required">Payment Status</label></td>
					<td>	
						<select name="payment_status">						
							<option value="0">Select Payment Status</option>
							<option value="approved"<?php echo $PROCESSED["payment_status"] == "approved"?' selected="selected"':'';?>>Approved</option>
							<option value="pending"<?php echo $PROCESSED["payment_status"] == "pending"?' selected="selected"':'';?>>Pending</option>
							<option value="denied"<?php echo $PROCESSED["payment_status"] == "denied"?' selected="selected"':'';?>>Denied</option>
							<option value="expired"<?php echo $PROCESSED["payment_status"] == "expired"?' selected="selected"':'';?>>Expired</option>
							<option value="cancelled"<?php echo $PROCESSED["payment_status"] == "cancelled"?' selected="selected"':'';?>>Cancelled</option>
						</select>
					</td>
				</tr>
				<tr>
					<td></td>
					<td><label for="payment_method" class="form-required">Payment Method</label></td>
					<td>	
						<select name="payment_method">						
							<option value="0">Select Payment Method</option>
							<option value="cheque"<?php echo $PROCESSED["payment_method"] == "cheque"?' selected="selected"':'';?>>Cheque</option>
							<option value="cash"<?php echo $PROCESSED["payment_method"] == "cash"?' selected="selected"':'';?>>Cash</option>
							<option value="debit"<?php echo $PROCESSED["payment_method"] == "debit"?' selected="selected"':'';?>>Debit</option>
							<option value="VISA"<?php echo $PROCESSED["payment_method"] == "VISA"?' selected="selected"':'';?>>VISA</option>
							<option value="Mastercard"<?php echo $PROCESSED["payment_method"] == "Mastercard"?' selected="selected"':'';?>>Mastercard</option>
							<option value="AMEX"<?php echo $PROCESSED["payment_method"] == "AMEX"?' selected="selected"':'';?>>American Express</option>
							<option value="other"<?php echo $PROCESSED["payment_method"] == "other" || !$PROCESSED["payment_method"]?' selected="selected"':'';?>>Other</option>
						</select>
					</td>
				</tr>
				<tr>
					<td></td>
					<td style="vertical-align: top"><label for="faculty_name" class="form-nrequired">Paying User</label></td>
					<td>
						<?php 
							$query = "	SELECT CONCAT_WS(', ',`lastname`,`firstname`) as `fullname`
										FROM `".AUTH_DATABASE."`.`user_data`
										WHERE `id` = ".$db->qstr($PROCESSED["proxy_id"]);
							$fullname = $db->GetOne($query); ?>
							<a href="<?php echo ENTRADA_URL;?>/people?id=<?php echo $PROCESSED["proxy_id"];?>"><?php echo $fullname; ?></a>
					</td>
				</tr>				
			</tbody>
		</table>		
	</form>
	<h2>Transaction Items</h2>
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
					<td><?php echo '$'.number_format($payment_item["item_cost"], 2, '.',','); ?></td>
				</tr>

		<?php	
				$total += $payment_item["item_cost"];
				} ?>
				<tr>
					<td></td>
					<td><strong>Total Cost:</strong></td>
					<td><?php echo '$'.number_format($total, 2, '.',',');?></td>
				</tr>
			</tbody>
		</table>
	<?php
	} else {
		echo display_notice("No items where recorded on this transaction.");
	}
	?>
	<script type="text/javascript">	
		jQuery('#item_type').change(function() {
			var item_type_value = jQuery('#item_type option:selected').val();

			if (item_type_value) {
				new Ajax.Updater('item_value_row', '<?php echo ENTRADA_RELATIVE; ?>/admin/payments/catalog?section=api-catalog-item-options', {
					evalScripts : true,
					parameters : {
						ajax : 1,
						ignore_prev : 1,
						item_type : item_type_value,
					},
					onSuccess : function (response) {
						if (response.responseText == "") {
							$('item_value_row').update('');
							$('item_value_row').hide();
						} else {
								$('item_value_row').show();
						}
					},
					onFailure : function () {
						$('item_value_row').update('');
						$('item_value_row').hide();
					}
				});
			}
		});

		jQuery('#quantity_unlimited').mousedown(function() {
			if(jQuery("#quantity_unlimited:checked").length > 0){
				jQuery('#quantity').removeAttr('disabled');
			}else{
				jQuery('#quantity').attr('disabled','disabled');			
			}
		});
	</script>
	<?php
			break;
	}
} else {
	echo display_error("No ID provided.");
	onload_redirect(ENTRADA_URL."/admin/payments");
}