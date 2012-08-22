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
	$BREADCRUMB[] = array("url" => ENTRADA_RELATIVE."/payments?section=add", "title" => "Add Offline Transaction");	
	$PROCESSED["organisation_id"] = $ENTRADA_USER->getOrganisationId();
	/**
	 * used for the api-catalog-item-options requirement below
	 */
	$ignore_prev = true;
	switch($STEP){
		case 2:
			if (isset($_POST["item_type"]) && $tmp = clean_input($_POST["item_type"],array("notags","trim"))) {
				$PROCESSED["item_type"] = $tmp;
			} else {
				add_error("The <strong>Item Type</strong> is a required field.");
			}
			if (isset($_POST["item_value"]) && $tmp = clean_input($_POST["item_value"],array("notags","trim"))) {
				$PROCESSED["item_value"] = $tmp;
			} else {
				$display = "Item Value";
				if (isset($PROCESSED["item_value"])) {
					switch($PROCESSED["item_value"]){
						case 'course':
							$display = "Course";
							break;
						default:
							break;
					}
				}
				add_error("The <strong>".$display."</strong> is a required field.");
			}
			if (isset($_POST["transaction_amount"]) && $tmp = clean_input($_POST["transaction_amount"],array("trim","float"))) {
				$PROCESSED["transaction_amount"] = $tmp;
			} else {
				add_error("The <strong>Transaction Amount</strong> is a required field and must be a valid number.");
			}
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
			if (isset($_POST["associated_user"]) && $id = (int)$_POST["associated_user"]){
				$PROCESSED["proxy_id"] = $id;
			} else {
				add_error("The <strong>Paying User</strong> is a required field.");
			}				
			
			if (!$ERROR) {
				
				$query = "	SELECT `pcatalog_id` FROM `payment_catalog` 
							WHERE `item_type` = ".$db->qstr($PROCESSED["item_type"])."
							AND `item_value` = ".$db->qstr($PROCESSED["item_value"]);
				if ($item_details = $db->GetRow($query)){
//					
//					switch($PROCESSED["item_type"]){
//						case "course":
//							$query = "SELECT `course_name` FROM `courses` WHERE `course_id` = ".$db->qstr($PROCESSED["item_value"]);
//							$item_name = "Course: ".$db->GetOne($query);
//							break;
//						default:
//							$item_name = "Item ".$item_details["pcatalog_id"];							
//							break;
//					}
					$p = PaymentFactory::getPaymentModel('generic');
					$p->addLineItem(array('name'=>'','value'=>$item_details['item_cost'],'catalog_id'=>$item_details['pcatalog_id']));
					$p->proxyId($PROCESSED['proxy_id']);
					$p->paymentAmount($PROCESSED["transaction_amount"]);
					$p->paymentMethod($PROCESSED["payment_method"]);
					try{
					if ($transaction_id = $p->createPayment($PROCESSED["payment_status"])) {
						add_success("Successfully added new offline transaction.");
						if(isset($_POST["post_action"]) && $action = clean_input($_POST["post_action"],array("trim","notags"))) {
							switch($action){
								case 'view':
									$url = ENTRADA_URL."/admin/payments?id=".$transaction_id;
									break;
								case 'new':
									$url = ENTRADA_URL."/admin/payments?section=add";
									break;
								case 'index':
								default:
									$url = ENTRADA_URL."/admin/payments";
									break;
							}
						}else {
							$url = ENTRADA_URL."/admin/payments";
						}
						onload_redirect($url);
					}
					}catch(Exception $e){
						add_error($e->getMessage());
					}
				} else {
					add_error("Invalid item selected.");
				}
				
			}
			
			if($ERROR){
				$STEP = 1;
			}
			break;
		case 1:
		default:
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
			
			$USER_LIST = array();
			$query = "	SELECT a.`id` AS `proxy_id`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, a.`organisation_id`
						FROM `".AUTH_DATABASE."`.`user_data` AS a
						LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
						ON b.`user_id` = a.`id`
						WHERE b.`app_id` = '".AUTH_APP_ID."'
						ORDER BY a.`lastname` ASC, a.`firstname` ASC";
			$results = $db->GetAll($query);
			if ($results) {
				foreach($results as $result) {
					$USER_LIST[$result["proxy_id"]] = array('proxy_id'=>$result["proxy_id"], 'fullname'=>$result["fullname"], 'organisation_id'=>$result['organisation_id']);
				}
			}			
			$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";	
			$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
			echo "<script language=\"text/javascript\">var DELETE_IMAGE_URL = '".ENTRADA_URL."/images/action-delete.gif';</script>";
	?>

	<h1>Add Offline Transaction</h1>
	<form action="<?php echo ENTRADA_URL;?>/admin/payments?section=add&step=2" method="post">
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
									<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/events'" />
								</td>
								<td style="width: 75%; text-align: right; vertical-align: middle">
									<?php if ($is_draft) { 
										echo "<input type=\"hidden\" name=\"post_action\" id=\"post_action\" value=\"draft\" />";
									} else { ?>
									<span class="content-small">After saving:</span>
									<select id="post_action" name="post_action">
										<option value="index"<?php echo ((!isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"])) || ($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "index") ? " selected=\"selected\"" : ""); ?>>Return to catalog</option>
										<option value="new"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "new") ? " selected=\"selected\"" : ""); ?>>Add another transaction</option>									
									</select>
									<?php } ?>
									<input type="submit" class="button" value="Save" />
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</tfoot>		
			<tbody>
				<tr>
					<td colspan="3"><h2>Transaction Details</h2></td>
				</tr>				
				<tr>
					<td></td>
					<td><label for="cost" class="form-required">Transaction Amount</label></td>
					<td>
						<input type="text" name="transaction_amount" value="<?php echo isset($PROCESSED["transaction_amount"])?$PROCESSED["transaction_amount"]:'';?>"/>
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
							<option value="other"<?php echo $PROCESSED["payment_method"] == "other"?' selected="selected"':'';?>>Other</option>
						</select>
					</td>
				</tr>
				<tr>
					<td></td>
					<td style="vertical-align: top"><label for="faculty_name" class="form-required">Paying User</label></td>
					<td>
						<input type="text" id="user_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle" />
						<?php
						$ONLOAD[] = "user_list = new AutoCompleteList({ type: 'user', url: '". ENTRADA_RELATIVE ."/api/personnel.api.php', remove_image: '". ENTRADA_RELATIVE ."/images/action-delete.gif'})";
						?>
						<div class="autocomplete" id="user_name_auto_complete"></div>
						<input type="hidden" id="associated_user" name="associated_user" />
						<input type="button" class="button-sm" id="add_associated_user" value="Add" style="vertical-align: middle" />
						<span class="content-small" id="user_desc">(<strong>Example:</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?>)</span>
						<ul id="user_list" class="menu">
							<?php
							if (isset($PROCESSED["proxy_id"]) && $PROCESSED["proxy_id"]) {
								if ((array_key_exists($PROCESSED["proxy_id"], $USER_LIST)) && is_array($USER_LIST[$PROCESSED["proxy_id"]])) {
									?>
									<li class="user" id="user_<?php echo $USER_LIST[$PROCESSED["proxy_id"]]["proxy_id"]; ?>" style="cursor: move;margin-bottom:10px;width:350px;">
									<?php echo $USER_LIST[$PROCESSED["proxy_id"]]["fullname"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="user_list.removeItem('<?php echo $USER_LIST[$PROCESSED["proxy_id"]]["proxy_id"]; ?>');" class="list-cancel-image" />
									</li>
									<?php
								}
							}
							?>
						</ul>
						<input type="hidden" id="user_ref" name="user_ref" value="" />
						<input type="hidden" id="user_id" name="user_id" value="" />
					</td>
				</tr>
				<tr>
					<td colspan="3"><h2>Transaction Item</h2></td>
				</tr>
				<tr>
					<td></td>
					<td><label for="item_type" class="form-required">Item Type</label></td>
					<td>
						<select name="item_type" id="item_type">
							<option value="0">Select Type</option>
							<option value="course"<?php echo isset($PROCESSED["item_type"]) && $PROCESSED["item_type"] == "course"?' selected="selected"':'';?>>Course</option>
						</select>
					</td>
				</tr>
				<tr id="item_value_row">
					<?php
					if ($PROCESSED["item_type"]) {
						require_once(ENTRADA_ABSOLUTE."/core/modules/admin/payments/catalog/api-catalog-item-options.inc.php");
					}
					?>				
				</tr>				
			</tbody>
		</table>
	</form>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			toggleUserInput();
		});
		jQuery('#user_list').change(function(){
			toggleUserInput();
		});
		function toggleUserInput(){
			if(jQuery('#user_list > li').length > 0){
				jQuery('#user_name').hide();
				jQuery('#add_associated_user').hide();
				jQuery('#user_desc').hide();
			}else{
				jQuery('#user_name').show();
				jQuery('#add_associated_user').show();				
				jQuery('#user_desc').show();				
			}			
		}
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
}