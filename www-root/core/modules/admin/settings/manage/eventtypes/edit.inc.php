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

	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/settings/manage/eventtypes?".replace_query(array("section" => "edit"))."&amp;org=".$ORGANISATION_ID, "title" => "Edit Event Type");
	
	if (isset($_GET["type_id"]) && ($type = clean_input($_GET["type_id"], array("notags", "trim")))) {
		$PROCESSED["eventtype_id"] = $type;
	}
	
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
			
			if (!$ERROR) {
				
				// Check to see if the eventtype_id is used in more than one organisation
				$query = "	SELECT `organisation_id`, `eventtype_id`
							FROM `eventtype_organisation` WHERE `eventtype_id` = ".$db->qstr($PROCESSED["eventtype_id"]);
				$results = $db->GetAssoc($query);

				if (count($results) > 1) {
					// if the eventtype_id is used in multiple organisations we are going to create a new entry and remove the old one
					$action = "INSERT";
					$where = FALSE;
					
					// we need a list of event_ids that are associated with this eventtype_id
					$query = "	SELECT b.`event_id`, c.`eventtype_id`
								FROM `courses` AS a
								LEFT JOIN `events` AS b
								ON a.`course_id` = b.`course_id`
								LEFT JOIN `event_eventtypes` AS c
								ON b.`event_id` = c.`event_id`
								WHERE a.`organisation_id` = ".$db->qstr($ORGANISATION_ID)."
								AND c.`eventtype_id` = ".$db->qstr($PROCESSED["eventtype_id"]);
					$events_list = $db->GetAssoc($query);
				} else {
					// if the eventtype_id is not in multiple organisations update it as normal
					$action = "UPDATE";
					$where = "`eventtype_id` = ".$db->qstr($PROCESSED["eventtype_id"]);
				}
				
				$params = array("eventtype_title" => $PROCESSED["eventtype_title"],"eventtype_description"=>$PROCESSED["eventtype_description"], "updated_date"=>time(),"updated_by"=>$ENTRADA_USER->getID());
				
				if ($db->AutoExecute("`events_lu_eventtypes`", $params, $action, $where)) {
					
					if ($action == "INSERT") {
						// if creating a new eventtype we will need to delete the old one, then update all of the previously fetched events to the new one.
						$eventtype_id = $db->Insert_ID();
						
						$query = "	DELETE FROM `eventtype_organisation`
									WHERE `eventtype_id` = ".$db->qstr($PROCESSED["eventtype_id"])."
									AND `organisation_id` = ".$db->qstr($ORGANISATION_ID);
						$db->Execute($query);
						
						$query = "	INSERT INTO `eventtype_organisation` (`eventtype_id`, `organisation_id`)
									VALUES (".$db->qstr($eventtype_id).", ".$db->qstr($ORGANISATION_ID).")";
						$db->Execute($query);
						
						$query = "	UPDATE `event_eventtypes`
									SET `eventtype_id` = ".$db->qstr($eventtype_id)."
									WHERE `event_id` IN ('".implode("', '", array_keys($events_list))."')";
						$db->Execute($query);
					}
					
					$url = ENTRADA_URL . "/admin/settings/manage/eventtypes?org=".$ORGANISATION_ID;
					$SUCCESS++;
					$SUCCESSSTR[] = "You have successfully added <strong>".html_encode($PROCESSED["eventtype_title"])."</strong> to the system.<br /><br />You will now be redirected to the Event Types index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
					$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";
					application_log("success", "New Event Type [".$PROCESSED["eventtype_id"]."] added to the system.");
				} else {
					$ERROR++;
					$ERRORSTR[] = "There was a problem inserting this objective into the system. The system administrator was informed of this error; please try again later.".$db->ErrorMsg();

					application_log("error", "There was an error inserting an objective. Database said: ".$db->ErrorMsg());
				}
			}

			if ($ERROR) {
				$STEP = 1;
			}
		break;
		case 1 :
		default :

			
			$query = "SELECT * FROM `events_lu_eventtypes` WHERE `eventtype_id` = ".$db->qstr($PROCESSED["eventtype_id"]);
			$result = $db->GetRow($query);
			if($result){
				$PROCESSED["eventtype_title"] = $result["eventtype_title"];
				$PROCESSED["eventtype_description"] = $result["eventtype_description"];				
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
						
			?>
			<form action="<?php echo ENTRADA_URL."/admin/settings/manage/eventtypes"."?".replace_query(array("action" => "edit", "step" => 2))."&org=".$ORGANISATION_ID; ?>" method="post">
			<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Editing Page">
			<colgroup>
				<col style="width: 30%" />
				<col style="width: 70%" />
			</colgroup>
			<thead>
				<tr>
					<td colspan="2"><h1>Event Type Details</h1></td>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="2" style="padding-top: 15px; text-align: right">
						<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/settings/manage/eventtypes?org=<?php echo $ORGANISATION_ID;?>'" />
                        <input type="submit" class="button" value="<?php echo $translate->_("global_button_save"); ?>" />                           
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td><label for="eventtype_title" class="form-required">Event Type	 Name:</label></td>
					<td><input type="text" id="eventtype_title" name="eventtype_title" value="<?php echo ((isset($PROCESSED["eventtype_title"])) ? html_encode($PROCESSED["eventtype_title"]) : ""); ?>" maxlength="60" style="width: 300px" /></td>
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
