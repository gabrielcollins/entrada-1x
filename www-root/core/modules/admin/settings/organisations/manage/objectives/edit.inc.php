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
 * This file is used to edit objectives in the entrada.global_lu_objectives table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer:James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_OBJECTIVES"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('objective', 'update', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/settings/organisations/manage/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	
	if (isset($_GET["id"]) && ($id = clean_input($_GET["id"], array("notags", "trim")))) {
				$OBJECTIVE_ID = $id;
	}
	
	if ($OBJECTIVE_ID) {
		$query = "SELECT a.* FROM `global_lu_objectives` AS a
					JOIN `objective_organisation` AS b
					ON a.`objective_id` = b.`objective_id`
					WHERE a.`objective_id` = ".$db->qstr($OBJECTIVE_ID)."
					AND b.`organisation_id` = ".$db->qstr($ORGANISATION_ID)."
					AND a.`objective_active` = '1'";
		$objective_details	= $db->GetRow($query);
		if ($objective_details) {
			$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/settings/organisations/manage/objectives?".replace_query(array("section" => "edit")), "title" => "Editing Objective");
			
			// Error Checking
			switch ($STEP) {
				case 2:
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

					if (!$ERROR) {
						if ($objective_details["objective_order"] != $PROCESSED["objective_order"]) {
							$query = "SELECT a.`objective_id` FROM `global_lu_objectives` AS a
										JOIN `objective_organisation` AS b
										ON a.`objective_id` = b.`objective_id`
										WHERE a.`objective_parent` = ".$db->qstr($PROCESSED["objective_parent"])."
										AND b.`organisation_id` = ".$db->qstr($ORGANISATION_ID)."
										AND a.`objective_id` != ".$db->qstr($OBJECTIVE_ID)."
										AND a.`objective_order` >= ".$db->qstr($PROCESSED["objective_order"])."
										AND a.`objective_active` = '1'
										ORDER BY a.`objective_order` ASC";
							$objectives = $db->GetAll($query);
							if ($objectives) {
								$count = $PROCESSED["objective_order"];
								foreach ($objectives as $objective) {
									$count++;
									if (!$db->AutoExecute("global_lu_objectives", array("objective_order" => $count), "UPDATE", "`objective_id` = ".$db->qstr($objective["objective_id"]))) {
										$ERROR++;
										$ERRORSTR[] = "There was a problem updating this objective in the system. The system administrator was informed of this error; please try again later.";
					
										application_log("error", "There was an error updating an objective. Database said: ".$db->ErrorMsg());
									}
								}
							}
						}
					}

					if (!$ERROR) {
						$PROCESSED["updated_date"] = time();
						$PROCESSED["updated_by"] = $_SESSION["details"]["id"];
						
						if ($db->AutoExecute("global_lu_objectives", $PROCESSED, "UPDATE", "`objective_id` = ".$db->qstr($OBJECTIVE_ID))) {
							$url = ENTRADA_URL . "/admin/settings/organisations/manage/objectives?org=".$ORGANISATION_ID;
							
							$SUCCESS++;
							$SUCCESSSTR[] = "Your org id is ".$ORGANISATION_ID.". You have successfully updated <strong>".html_encode($PROCESSED["objective_name"])."</strong> in the system.<br /><br />You will now be redirected to the objectives index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";

							$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";
	
							application_log("success", "Objective [".$OBJECTIVE_ID."] updated in the system.");		
						} else {
							$ERROR++;
							$ERRORSTR[] = "There was a problem updating this objective in the system. The system administrator was informed of this error; please try again later.";
		
							application_log("error", "There was an error updating an objective. Database said: ".$db->ErrorMsg());
						}
					}

					if ($ERROR) {
						$STEP = 1;
					}
				break;
				case 1:
				default:
					$PROCESSED = $objective_details;
				break;
			}

			//Display Content
			switch ($STEP) {
				case 2:
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
				case 1:
					if ($ERROR) {
						echo display_error();
					}

					$HEAD[]	= "<script type=\"text/javascript\">
								function selectObjective(parent_id, objective_id) {
									new Ajax.Updater('selectObjectiveField', '".ENTRADA_URL."/api/objectives-list.api.php', {parameters: {'pid': parent_id, 'id': objective_id, 'organisation_id': ".$ORGANISATION_ID."}});
									return;
								}
								function selectOrder(objective_id, parent_id) {
									new Ajax.Updater('selectOrderField', '".ENTRADA_URL."/api/objectives-list.api.php', {parameters: {'id': objective_id, 'type': 'order', 'pid': parent_id, 'organisation_id': ".$ORGANISATION_ID."}});
									return;
								}
								</script>";
					$ONLOAD[] = "selectObjective(".(isset($PROCESSED["objective_parent"]) && $PROCESSED["objective_parent"] ? $PROCESSED["objective_parent"] : "0").", ".$OBJECTIVE_ID.")";
					$ONLOAD[] = "selectOrder(".$OBJECTIVE_ID.", ".(isset($PROCESSED["objective_parent"]) && $PROCESSED["objective_parent"] ? $PROCESSED["objective_parent"] : "0").")";
					?>
					<form action="<?php echo ENTRADA_URL."/admin/settings/organisations/manage/objectives"."?".replace_query(array("action" => "add", "step" => 2)); ?>" method="post">
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
							<td style="vertical-align: top"><label for="objective_id" class="form-required">Objective Order:</label></td>
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
		} else {
			$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/settings/organisations/manage/".$MODULE."\\'', 15000)";
	
			$ERROR++;
			$ERRORSTR[] = "In order to update an objective a valid objective identifier must be supplied. The provided ID does not exist in the system.";
	
			echo display_error();
	
			application_log("notice", "Failed to provide objective identifer when attempting to edit an objective.");
		}
	} else {
		$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/settings/organisations/manage/".$MODULE."\\'', 15000)";

		$ERROR++;
		$ERRORSTR[] = "In order to update an objective a valid objective identifier must be supplied.";

		echo display_error();

		application_log("notice", "Failed to provide objective identifer when attempting to edit an objective.");
	}
}
?>
