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
} elseif (!$ENTRADA_ACL->amIAllowed("configuration", "create",false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {


	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/settings/organisations/manage/eventtypes?".replace_query(array("section" => "add"))."&amp;org=".$ORGANISATION_ID, "title" => "Add Event Type");
	
	// Error Checking
	switch ($STEP) {
		case 2 :
			/**
			 * Required field "objective_name" / Objective Name
			 */
			if (isset($_POST["eventtype_title"]) && ($eventtype_title = clean_input($_POST["eventtype_title"], array("notags", "trim")))) {
				$PROCESSED["eventtype_title"] = $eventtype_title;
			} else {
				$ERROR++;
				$ERRORSTR[] = "The <strong>Event Type Name</strong> is a required field.";
			}

			/**
			 * Non-required field "objective_description" / Objective Description
			 */
			if (isset($_POST["eventtype_description"]) && ($eventtype_description = clean_input($_POST["eventtype_description"], array("notags", "trim")))) {
				$PROCESSED["eventtype_description"] = $eventtype_description;
			} else {
				$PROCESSED["eventtype_description"] = "";
			}
			
			$PROCESSED["eventtype_active"] = 1;
			$PROCESSED["eventtype_order"] = 0;
			
			if (!$ERROR) {
				$PROCESSED["updated_date"] = time();
				$PROCESSED["updated_by"] = $_SESSION["details"]["id"];

				if ($db->AutoExecute("events_lu_eventtypes", $PROCESSED, "INSERT")) {
					if ($EVENTTYPE_ID = $db->Insert_Id()) {
						$params = array("eventtype_id"=>$EVENTTYPE_ID,"organisation_id"=>$ORGANISATION_ID);
						
						if($db->AutoExecute("eventtype_organisation", $params, "INSERT")){
							$url = ENTRADA_URL . "/admin/settings/organisations/manage/eventtypes?org=".$ORGANISATION_ID;
							$SUCCESS++;
							$SUCCESSSTR[] = "You have successfully added <strong>".html_encode($PROCESSED["eventtype_title"])."</strong> to the system.<br /><br />You will now be redirected to the Event Types index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
							$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";
							application_log("success", "New Event Type [".$EVENTTYPE_ID."] added to the system.");
						}
					}
					else {
						$ERROR++;
						$ERRORSTR[] = "There was a problem inserting this event type into the system. The system administrator was informed of this error; please try again later.";
						application_log("error", "There was an error inserting an event type. Database said: ".$db->ErrorMsg());
					}
				} else {
					$ERROR++;
					$ERRORSTR[] = "There was a problem inserting this event typve into the system. The system administrator was informed of this error; please try again later.";

					application_log("error", "There was an error inserting an event type. Database said: ".$db->ErrorMsg());
				}
			}

			if ($ERROR) {
				$STEP = 1;
			}
		break;
		case 1 :
		default :

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
			<form action="<?php echo ENTRADA_URL."/admin/settings/organisations/manage/eventtypes"."?".replace_query(array("action" => "add", "step" => 2))."&org=".$ORGANISATION_ID; ?>" method="post">
			<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Adding Page">
			<colgroup>
				<col style="width: 30%" />
				<col style="width: 70%" />
			</colgroup>
			<thead>
				<tr>
					<td colspan="2"><h1>Add Event Type</h1></td>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="2" style="padding-top: 15px; text-align: right">
						<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/settings/organisations/manage/eventtypes?org=<?php echo $ORGANISATION_ID;?>'" />
                        <input type="submit" class="button" value="<?php echo $translate->_("global_button_save"); ?>" />                           
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td><label for="eventtype_title" class="form-required">Event Type	 Name:</label></td>
					<td><input type="text" id="eventtype_title" name="eventtype_title" value="<?php echo ((isset($PROCESSED["eventtype_name"])) ? html_encode($PROCESSED["eventtype_title"]) : ""); ?>" maxlength="60" style="width: 300px" /></td>
				</tr>
				<tr>
					<td style="vertical-align: top;"><label for="eventtype_description" class="form-nrequired">Event Type Description: </label></td>
					<td>
						<textarea id="eventtype_description" name="eventtype_description" style="width: 98%; height: 200px" rows="20" cols="70"><?php echo ((isset($PROCESSED["eventtype_description"])) ? html_encode($PROCESSED["eventtype_description"]) : ""); ?></textarea>
					</td>
				</tr>

			</tbody>
			</table>
			</form>
			<?php
		break;
	}

}
