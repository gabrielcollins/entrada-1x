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
 * @author Organisation: Queen's University
 * @author Unit: MEdTech Unit
 * @author Developer: Brandon Thorn <brandon.thorn@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_CONFIGURATION"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("configuration", "update",false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	
	$POPTION_ID = (int)$_GET["type_id"];
	if ($POPTION_ID) {
		$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/settings/organisations/manage/payments?".replace_query(array("section" => "edit"))."&amp;org=".$ORGANISATION_ID, "title" => "Edit Payment Method");

		// Error Checking
		switch ($STEP) {
			case 2 :
				/**
				 * Required field "objective_name" / Objective Name
				 */
				if (isset($_POST["payment_name"]) && ($payment_name = clean_input($_POST["payment_name"], array("notags", "trim")))) {
					$PROCESSED["payment_name"] = $payment_name;
				} else {
					$ERROR++;
					$ERRORSTR[] = "The <strong>Payment Method Name</strong> is a required field.";
				}

				/**
				 * Non-required field "objective_description" / Objective Description
				 */
				$mandatory_keys = array();
				if (isset($_POST["ptype_id"]) && ($payment_service = (int)$_POST["ptype_id"])) {
					$PROCESSED["ptype_id"] = $payment_service;
					$query = "SELECT `ptkey_id` FROM `payment_lu_type_keys` WHERE `ptype_id` = ".$db->qstr($payment_service)." AND `key_required` = '1'";
					$results = $db->GetAll($query);
					if ($results) {
						foreach($results as $result){
							$mandatory_keys[] = $result["ptkey_id"];
						}
					}

				} else {
					$ERROR++;
					$ERRORSTR[] = "The <strong>Payment Service</strong> is a required field.";
				}

				if (isset($_POST["option_keys"]) && is_array($_POST["option_keys"])) {
					foreach ($_POST["option_keys"] as $key=>$value) {
						$key = (int)$key;
						$clean_value = clean_input($value, array("notags", "trim"));
						$PROCESSED["option_keys"][$key] = $clean_value;

						if(in_array($key, $mandatory_keys) && !$clean_value){
							$ERROR++;
							$ERRORSTR[] = "Missing required information for selected Payment Method.";
						}					
					}
				}

				$PROCESSED["organisation_id"] = $ORGANISATION_ID;		
				$PROCESSED["payment_active"] = 1;		
				if (!$ERROR) {
					$PROCESSED["updated_date"] = time();
					$PROCESSED["updated_by"] = $ENTRADA_USER->getID();

					if ($db->AutoExecute("payment_options", $PROCESSED, "UPDATE","`poption_id` = ".$db->qstr($POPTION_ID))) {
						$query = "DELETE FROM `payment_option_keys` WHERE `poption_id` = ".$db->qstr($POPTION_ID);
						$db->Execute($query);
						if ($PROCESSED["option_keys"]) {							
							foreach($PROCESSED["option_keys"] as $key=>$value) {
								$params = array("poption_id"=>$POPTION_ID,"ptkey_id"=>$key,"key_value"=>$value);

								if($db->AutoExecute("payment_option_keys", $params, "INSERT")){

								}
							}
						}
						$url = ENTRADA_URL . "/admin/settings/organisations/manage/payments?org=".$ORGANISATION_ID;
						$SUCCESS++;
						$SUCCESSSTR[] = "You have successfully added <strong>".html_encode($PROCESSED["payment_name"])."</strong> to the system.<br /><br />You will now be redirected to the Payments index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
						$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";
						application_log("success", "New Payment Method [".$POPTION_ID."] added to the system.");											
					} else {
						$ERROR++;
						$ERRORSTR[] = "There was a problem inserting this payment method into the system. The system administrator was informed of this error; please try again later.";

						application_log("error", "There was an error inserting an payment method. Database said: ".$db->ErrorMsg());
					}
				}

				if ($ERROR) {
					$STEP = 1;
				}
			break;
			case 1 :
			default :
				$query = "SELECT * FROM `payment_options` WHERE `poption_id` = ".$db->qstr($POPTION_ID);
				$result = $db->GetRow($query);
				if ($result) {
					$PROCESSED["payment_name"] = $result["payment_name"];
					$PROCESSED["ptype_id"] = $result["ptype_id"];
					$query = "SELECT * FROM `payment_option_keys` WHERE `poption_id` = ".$db->qstr($POPTION_ID);
					$results = $db->GetAll($query);
					if ($results) {
						foreach($results as $result){
							$PROCESSED["option_keys"][$result["ptkey_id"]] = $result["key_value"];
						}
					}
				} else {
						$ERROR++;
						$ERRORSTR[] = "Invalid ID provided.";				
				}
			break;
		}
		
		// Display Content
		switch ($STEP) {
			case 2 :
				if ($SUCCESS) {
					echo display_success();
				}

				if ($NOTICE) {
					echo display_notice();
				}

				if ($ERROR) {
					echo display_error();
				}
			break;
			case 1 :
			default:	
				if ($ERROR) {
					echo display_error();
				}

				$HEAD[]	= "	<script type=\"text/javascript\">
							var organisation_id = ".$ORGANISATION_ID.";
							function selectObjective(parent_id, objective_id) {
								new Ajax.Updater('selectObjectiveField', '".ENTRADA_URL."/api/objectives-list.api.php', {parameters: {'pid': parent_id, 'organisation_id': ".$ORGANISATION_ID."}});
								return;
							}
							function selectOrder(parent_id) {
								new Ajax.Updater('selectOrderField', '".ENTRADA_URL."/api/objectives-list.api.php', {parameters: {'type': 'order', 'pid': parent_id, 'organisation_id': ".$ORGANISATION_ID."}});
								return;
							}
							</script>";
				$ONLOAD[] = "selectObjective(".(isset($PROCESSED["objective_parent"]) && $PROCESSED["objective_parent"] ? $PROCESSED["objective_parent"] : "0").")";
				$ONLOAD[] = "selectOrder(".(isset($PROCESSED["objective_parent"]) && $PROCESSED["objective_parent"] ? $PROCESSED["objective_parent"] : "0").")";

				?>
				<form action="<?php echo ENTRADA_URL."/admin/settings/organisations/manage/payments"."?".replace_query(array("action" => "edit", "step" => 2))."&org=".$ORGANISATION_ID; ?>" method="post">
				<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Adding Page">
				<colgroup>
					<col style="width: 30%" />
					<col style="width: 70%" />
				</colgroup>
				<thead>
					<tr>
						<td colspan="2"><h1>Edit Payment Method</h1></td>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="2" style="padding-top: 15px; text-align: right">
							<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/settings/organisations/manage/payments?org=<?php echo $ORGANISATION_ID;?>'" />
							<input type="submit" class="button" value="<?php echo $translate->_("global_button_save"); ?>" />                           
						</td>
					</tr>
				</tfoot>
				<tbody>
					<tr>
						<td><label for="payment_name" class="form-required">Payment Name:</label></td>
						<td><input type="text" id="payment_name" name="payment_name" value="<?php echo ((isset($PROCESSED["payment_name"])) ? html_encode($PROCESSED["payment_name"]) : ""); ?>" maxlength="60" style="width: 300px" /></td>
					</tr>
					<tr>
						<td style="vertical-align: top;"><label for="ptype_id" class="form-required">Payment Service: </label></td>
						<td>
							<?php
							$query = "SELECT * FROM `payment_lu_types`";
							$services = $db->GetAll($query);
							if ($services) {
							?>	
							<select name="ptype_id" id="ptype_id" style="width:306px;">
								<option value="0">--Select Payment Service--</option>
								<?php 							
								foreach($services as $service){
									?><option value="<?php echo $service["ptype_id"];?>"<?php echo (isset($PROCESSED["ptype_id"]) && $PROCESSED["ptype_id"] == $service["ptype_id"])?' selected="selected"':'';?>><?php echo $service["payment_name"];?></option><?php
								}
								?>
							</select>
							<?php } else { 
								echo display_notice("There are no Payment Services. You will need to have your System Administrator setup one.");
							} ?>
						</td>
					</tr>
				</tbody>
				<tbody id="payment_keys_sections">
					<?php 
					if (isset($PROCESSED["ptype_id"])) {
						$query = "SELECT * FROM `payment_lu_type_keys` WHERE `ptype_id` = ".$db->qstr($PROCESSED["ptype_id"]);
						if($results = $db->GetAll($query)) {
							foreach ($results as $key=>$value) {
							?>
							<tr>
								<td><label for="payment_key_<?php echo $value["ptkey_id"];?>" class="form-<?php echo $value['key_required']?'':'n';?>required"><?php echo ucwords($value["key_name"]);?>:</label></td>
								<td><input type="text" id="payment_key_<?php echo $value["ptkey_id"];?>" name="option_keys[<?php echo $value["ptkey_id"];?>]" value="<?php echo ((isset($PROCESSED["option_keys"][$value["ptkey_id"]])) ? html_encode($PROCESSED["option_keys"][$value["ptkey_id"]]) : ""); ?>" maxlength="60" style="width: 300px" /></td>
							</tr>				
							<?php
							}
						}
					}
					?>
				</tbody>
				</table>
				</form>
				<script type="text/javascript">
					jQuery(document).ready(function(){
						jQuery('#ptype_id').change(function(){
							var ptype_id = jQuery(this).val();
							if(ptype_id != 0){
								jQuery.ajax({
									type: "POST",
									url: "<?php echo ENTRADA_URL;?>/api/payment-method-keys.api.php",
									data: "ptype_id="+ptype_id,
									success: function(data){
										try{
											var result = $.parseJSON(data);										
											alert(result.error);
										}catch(e){
											jQuery('#payment_keys_sections').html(data);
											jQuery('#payment_keys_sections').show();										
										}
									}
									});
							} else {
								jQuery('#payment_keys_sections').hide();
							}
						});
					});
				</script>
				<?php
			break;
		}
	} else {
		$url = ENTRADA_URL . "/admin/settings/organisations/manage/payments?org=".$ORGANISATION_ID;
		echo display_error("No Payment Method Identifier provided.<br /><br />You will now be redirected to the Payments index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.");
		$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";
	}
}
