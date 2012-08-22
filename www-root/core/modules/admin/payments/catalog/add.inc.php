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
	$BREADCRUMB[] = array("url" => ENTRADA_RELATIVE."/payments/catalog?section=add", "title" => "Add Catalog Item");	
	$PROCESSED["organisation_id"] = $ENTRADA_USER->getOrganisationId();
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
			if (isset($_POST["item_cost"]) && $tmp = clean_input($_POST["item_cost"],array("notags","trim"))) {
				$PROCESSED["item_cost"] = $tmp;
			} else {
				add_error("The <strong>Item Cost</strong> is a required field.");
			}
			if (isset($_POST["quantity_unlimited"]) && (int)$_POST["quantity_unlimited"] == 1){
				$PROCESSED["quantity"] = -1;
			} else if (isset($_POST["quantity"]) && $tmp = (int)$_POST["quantity"]) {
				$PROCESSED["quantity"] = $tmp;
			} else {
				add_error("The <strong>Quantity</strong> is a required field.");
			}			
			
			if (isset($_POST["poption_id"]) && $tmp = (int)$_POST["poption_id"]) {
				$PROCESSED["poption_id"] = $tmp;
			} else {
				add_error("The <strong>Payment Option</strong> is a required field.");
			}			
			
			if (!$ERROR) {
				$PROCESSED["updated_date"] = time();
				$PROCESSED["updated_by"] = $ENTRADA_USER->getId();
				$PROCESSED["active"] = 1;
				if ($db->AutoExecute("payment_catalog",$PROCESSED,"INSERT")) {
					add_success("Successfully added catalog item.");
					$url = ENTRADA_URL."/admin/payments/catalog";
					if(isset($_POST["post_action"]) && $action = clean_input($POST["post_action"],array("trim","notags"))){
						switch($action){
							case 'add':
								$url = ENTRADA_URL."/admin/payments/catalog?section=add";
								break;
							case 'index':
							default:
								$url = ENTRADA_URL."/admin/payments/catalog";
								break;
						}
					}
					onload_redirect($url);
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
	?>

	<h1>Add New Item</h1>
	<form action="<?php echo ENTRADA_URL;?>/admin/payments/catalog?section=add&step=2" method="post">
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
										<option value="new"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "new") ? " selected=\"selected\"" : ""); ?>>Add another item</option>									
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
				<tr>
					<td></td>
					<td><label for="cost" class="form-required">Cost</label></td>
					<td>
						<input type="text" name="item_cost" value="<?php echo isset($PROCESSED["item_cost"])?$PROCESSED["item_cost"]:'';?>"/>
					</td>
				</tr>
				<tr>
					<td></td>
					<td><label for="quanity" class="form-required">Quantity</label></td>
					<td>
						<input type="text" name="quantity" id ="quantity"value="<?php echo (isset($PROCESSED["quantity"]) && $PROCESSED["quantity"] && $PROCESSED["quantity"] != -1)?$PROCESSED["quantity"]:'';?>"<?php echo (isset($PROCESSED["quantity"]) && $PROCESSED["quantity"] && $PROCESSED["quantity"] == -1)?' disabled':'';?>/>
						<input type="checkbox" name="quantity_unlimited"  id="quantity_unlimited" value="1"<?php echo (isset($PROCESSED["quantity"]) && $PROCESSED["quantity"] && $PROCESSED["quantity"] == -1)?' checked="checked"':'';?>/> <label for="quantity_unlimited">Unlimited Quantity</label>
					</td>
				</tr>
				<tr>
					<td></td>
					<td><label for="poption_id" class="form-required">Payment Option</label></td>
					<td>
							<?php
							$query = "	SELECT * FROM `payment_options` 
										WHERE `organisation_id` = ".$db->qstr($PROCESSED["organisation_id"])." 
										AND `payment_active` = '1'";
							if ($options = $db->GetAll($query)) {
								?>
								<select name="poption_id">						
									<option value="0">Select Payment Option</option>
									<?php
									foreach($options as $option){
										?><option value="<?php echo $option["poption_id"];?>"<?php echo $option["poption_id"] == $PROCESSED["poption_id"]?' selected="selected"':'';?>><?php echo $option["payment_name"];?></option><?php
									}
									?>
								</select>
								<?php
							} else {
								echo display_notice("No Payment Options Have Been Configured.");
							}
							?>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	<script type="text/javascript">
	jQuery('#item_type').change(function() {
		var item_type_value = jQuery('#item_type option:selected').val();

		if (item_type_value) {
			new Ajax.Updater('item_value_row', '<?php echo ENTRADA_RELATIVE; ?>/admin/payments/catalog?section=api-catalog-item-options', {
				evalScripts : true,
				parameters : {
					ajax : 1,
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