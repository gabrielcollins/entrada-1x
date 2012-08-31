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
 * This file is used to add events to the entrada.events table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2008 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_OBJECTIVES"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('objective', 'create', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/settings/organisations/manage/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/settings/organisations/manage/objectives?".replace_query(array("section" => "add"))."&amp;id=".$ORGANISATION_ID, "title" => "Adding Objective");
	
	// Error Checking
	switch ($STEP) {
		case 2 :
			/**
			 * Required field "objective_name" / Objective Name
			 */
			if (isset($_POST["objective_name"]) && ($objective_name = clean_input($_POST["objective_name"], array("notags", "trim")))) {
				$PROCESSED["objective_name"] = $objective_name;
			} else {
				$ERROR++;
				$ERRORSTR[] = "The <strong>Objective Name</strong> is a required field.";
			}

			/**
			 * Non-required field "objective_code" / Objective Code
			 */
			if (isset($_POST["objective_code"]) && ($objective_code = clean_input($_POST["objective_code"], array("notags", "trim")))) {
				$PROCESSED["objective_code"] = $objective_code;
			} else {
				$PROCESSED["objective_code"] = "";
			}

			/**
			 * Non-required field "objective_parent" / Objective Parent
			 */
			if (isset($_POST["objective_id"]) && ($objective_parent = clean_input($_POST["objective_id"], array("int")))) {
				$PROCESSED["objective_parent"] = $objective_parent;

				$_SESSION[APPLICATION_IDENTIFIER]["objectives"]["objective_parent"] = $objective_parent;
			} else {
				$PROCESSED["objective_parent"] = 0;
			}
		
			/**
			 * Required field "objective_order" / Objective Order
			 */
			if (isset($_POST["objective_order"]) && ($objective_order = clean_input($_POST["objective_order"], array("int")))) {
				$PROCESSED["objective_order"] = $objective_order;
			} else {
				$PROCESSED["objective_order"] = 0;
			}

			/**
			 * Non-required field "objective_description" / Objective Description
			 */
			if (isset($_POST["objective_description"]) && ($objective_description = clean_input($_POST["objective_description"], array("notags", "trim")))) {
				$PROCESSED["objective_description"] = $objective_description;
			} else {
				$PROCESSED["objective_description"] = "";
			}

			if (!$ERROR && $objective_details["objective_order"] != $PROCESSED["objective_order"]) {
				$query = "SELECT a.`objective_id` FROM `global_lu_objectives` AS a
							JOIN `objective_organisation` AS b
							ON a.`objective_id` = b.`objective_id`
							WHERE a.`objective_parent` = ".$db->qstr($PROCESSED["objective_parent"])."
							AND a.`objective_order` >= ".$db->qstr($PROCESSED["objective_order"])."
							AND a.`objective_active` = '1'
							AND b.`organisation_id` = ".$db->qstr($ORGANISATION_ID)."
							ORDER BY a.`objective_order` ASC";
				$objectives = $db->GetAll($query);
				if ($objectives) {
					$count = $PROCESSED["objective_order"];
					foreach ($objectives as $objective) {
						$count++;
						if (!$db->AutoExecute("global_lu_objectives", array("objective_order" => $count), "UPDATE", "`objective_id` = ".$db->qstr($objective["objective_id"]))) {
							$ERROR++;
							$ERRORSTR[] = "There was a problem adding this objective to the system. The system administrator was informed of this error; please try again later.";
		
							application_log("error", "There was an error updating an objective. Database said: ".$db->ErrorMsg());
						}
					}
				}
			}
			
			if (!$ERROR) {
				$PROCESSED["updated_date"] = time();
				$PROCESSED["updated_by"] = $_SESSION["details"]["id"];

				if ($db->AutoExecute("global_lu_objectives", $PROCESSED, "INSERT")) {
					if ($OBJECTIVE_ID = $db->Insert_Id()) {
						if ($PROCESSED["objective_parent"] == 0) {
							$params = array("objective_id"=>$OBJECTIVE_ID,"organisation_id"=>$ORGANISATION_ID);
							if($db->AutoExecute("objective_organisation", $params, "INSERT")){
								$url = ENTRADA_URL . "/admin/settings/organisations/manage/objectives?org=".$ORGANISATION_ID;
								$SUCCESS++;
								$SUCCESSSTR[] = "You have successfully added <strong>".html_encode($PROCESSED["objective_name"])."</strong> to the system.<br /><br />You will now be redirected to the objectives index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
								$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";
								application_log("success", "New Objective [".$OBJECTIVE_ID."] added to the system.");
							} else{
								$ERROR++;
								$ERRORSTR[] = "There was a problem associating this objective to your organisation. The system administrator was informed of this error; please try again later.";

								application_log("error", "There was an error associating an objective with an organisation. Database said: ".$db->ErrorMsg());
							}
						} else {
							$params = array("objective_id"=>$OBJECTIVE_ID,"organisation_id"=>$ORGANISATION_ID);
							if($db->AutoExecute("objective_organisation", $params, "INSERT")) {
								$url = ENTRADA_URL . "/admin/settings/organisations/manage/objectives?org=".$ORGANISATION_ID;
								$SUCCESS++;
								$SUCCESSSTR[] = "You have successfully added <strong>".html_encode($PROCESSED["objective_name"])."</strong> to the system.<br /><br />You will now be redirected to the objectives index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
								$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";
								application_log("success", "New Objective [".$OBJECTIVE_ID."] added to the system.");
							} else{
								$ERROR++;
								$ERRORSTR[] = "There was a problem associating this objective to your organisation. The system administrator was informed of this error; please try again later.";

								application_log("error", "There was an error associating an objective with an organisation. Database said: ".$db->ErrorMsg());
							}
						}
					}
				} else {
					$ERROR++;
					$ERRORSTR[] = "There was a problem inserting this objective into the system. The system administrator was informed of this error; please try again later.";

					application_log("error", "There was an error inserting an objective. Database said: ".$db->ErrorMsg());
				}
			}

			if ($ERROR) {
				$STEP = 1;
			}
		break;
		case 1 :
		default :
			/*if (isset($_SESSION[APPLICATION_IDENTIFIER]["objectives"]["objective_parent"]) && ($objective_parent = (int) $_SESSION[APPLICATION_IDENTIFIER]["objectives"]["objective_parent"])) {
				$PROCESSED["objective_parent"] = $objective_parent;
				
			} else {*/
				$PROCESSED["objective_parent"] = 0;
			/*}*/
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
			<form action="<?php echo ENTRADA_URL."/admin/settings/organisations/manage/objectives"."?".replace_query(array("action" => "add", "step" => 2))."&org=".$ORGANISATION_ID; ?>" method="post">
			<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Adding Page">
			<colgroup>
				<col style="width: 30%" />
				<col style="width: 70%" />
			</colgroup>
			<thead>
				<tr>
					<td colspan="2"><h1>Objective Details</h1></td>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="2" style="padding-top: 15px; text-align: right">
						<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/settings/organisations/manage/objectives?org=<?php echo $ORGANISATION_ID;?>'" />
                        <input type="submit" class="button" value="<?php echo $translate->_("global_button_save"); ?>" />                           
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td colspan="2">
						<?php 
							$objective_name = $translate->_("events_filter_controls");
							add_notice("<strong>Note:</strong><br/>If this is a parent for Curriculum Objectives please label it '".html_encode($objective_name["co"]["global_lu_objectives_name"])."'.<br/>If this is a parent for Mandates Objectives (such as MCC Objectives) please label it '".html_encode($objective_name["cp"]["global_lu_objectives_name"])."'.");
							echo display_notice();
						?>
					</td>
				</tr>
				<tr>
					<td><label for="objective_name" class="form-required">Objective Name:</label></td>
					<td><input type="text" id="objective_name" name="objective_name" value="<?php echo ((isset($PROCESSED["objective_name"])) ? html_encode($PROCESSED["objective_name"]) : ""); ?>" maxlength="60" style="width: 300px" /></td>
				</tr>
				<tr>
					<td><label for="objective_code" class="form-nrequired">Objective Code:</label></td>
					<td><input type="text" id="objective_code" name="objective_code" value="<?php echo ((isset($PROCESSED["objective_code"])) ? html_encode($PROCESSED["objective_code"]) : ""); ?>" maxlength="100" style="width: 300px" /></td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td style="vertical-align: top; padding-top: 15px"><label for="objective_id" class="form-required">Objective Parent:</label></td>
					<td style="vertical-align: top"><div id="selectObjectiveField"></div></td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td style="vertical-align: top"><label for="objective_order" class="form-required">Objective Order:</label></td>
					<td style="vertical-align: top"><div id="selectOrderField"></div></td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td style="vertical-align: top;"><label for="objective_description" class="form-nrequired">Objective Description: </label></td>
					<td>
						<textarea id="objective_description" name="objective_description" style="width: 98%; height: 200px" rows="20" cols="70"><?php echo ((isset($PROCESSED["objective_description"])) ? html_encode($PROCESSED["objective_description"]) : ""); ?></textarea>
					</td>
				</tr>
			</tbody>
			</table>
			</form>
			<?php
		default:
		break;
	}
}
?>
