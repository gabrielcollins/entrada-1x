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
} elseif (!$ENTRADA_ACL->amIAllowed("configuration", "read",false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {

	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/settings/organisations/manage/hottopics?".replace_query(array("section" => "edit"))."&amp;org=".$ORGANISATION_ID, "title" => "Edit Hot Topic");
	
	if ((isset($_GET["topic_id"])) && ($topic = clean_input($_GET["topic_id"], array("notags", "trim")))) {
		$PROCESSED["topic_id"] = $topic;
	}
	
	
	// Error Checking
	switch ($STEP) {
		case 2 :
			/**
			 * Required field "objective_name" / Objective Name
			 */
			if (isset($_POST["topic_name"]) && ($topic_name = clean_input($_POST["topic_name"], array("notags", "trim")))) {
				$PROCESSED["topic_name"] = $topic_name;
			} else {
				$ERROR++;
				$ERRORSTR[] = "The <strong>Hot Topic Name</strong> is a required field.";
			}

			/**
			 * Non-required field "objective_description" / Objective Description
			 */
			if (isset($_POST["topic_description"]) && ($topic_description = clean_input($_POST["topic_description"], array("notags", "trim")))) {
				$PROCESSED["topic_description"] = $topic_description;
			} else {
				$PROCESSED["topic_description"] = "";
			}

		
			if (!$ERROR) {
				
				$params = array("topic_name" => $PROCESSED["topic_name"],"topic_description"=>$PROCESSED["topic_description"], "updated_date"=>time(),"updated_by"=>$_SESSION["details"]["id"]);
				
				if ($db->AutoExecute("`events_lu_topics`", $params, "UPDATE","`topic_id`=".$db->qstr($PROCESSED["topic_id"]))) {

							$url = ENTRADA_URL . "/admin/settings/organisations/manage/hottopics?org=".$ORGANISATION_ID;
							$SUCCESS++;
							$SUCCESSSTR[] = "You have successfully added <strong>".html_encode($PROCESSED["topic_name"])."</strong> to the system.<br /><br />You will now be redirected to the Hot Topics index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
							$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";
							application_log("success", "New Event Type [".$PROCESSED["topic_id"]."] added to the system.");

				} else {
					$ERROR++;
					$ERRORSTR[] = "There was a problem inserting this Hot Topic into the system. The system administrator was informed of this error; please try again later.";

					application_log("error", "There was an error inserting a Hot Topic. Database said: ".$db->ErrorMsg());
				}
			}

			if ($ERROR) {
				$STEP = 1;
			}
		break;
		case 1 :
		default :

			
			$query = "SELECT * FROM `events_lu_topics` WHERE `topic_id` = ".$db->qstr($PROCESSED["topic_id"]);
			$result = $db->GetRow($query);
			if($result){
				$PROCESSED["topic_name"] = $result["topic_name"];
				$PROCESSED["topic_description"] = $result["topic_description"];				
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
			<form action="<?php echo ENTRADA_URL."/admin/settings/organisations/manage/hottopics"."?".replace_query(array("action" => "edit", "step" => 2))."&org=".$ORGANISATION_ID; ?>" method="post">
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
						<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/settings/organisations/manage/hottopics?org=<?php echo $ORGANISATION_ID;?>'" />
                        <input type="submit" class="button" value="<?php echo $translate->_("global_button_save"); ?>" />                           
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td><label for="topic_name" class="form-required">Hot Topic Name:</label></td>
					<td><input type="text" id="topic_name" name="topic_name" value="<?php echo ((isset($PROCESSED["topic_name"])) ? html_encode($PROCESSED["topic_name"]) : ""); ?>" maxlength="60" style="width: 300px" /></td>
				</tr>
				<tr>
					<td style="vertical-align: top;"><label for="topic_description" class="form-nrequired">Hot Topic Description: </label></td>
					<td>
						<textarea id="topic_description" name="topic_description" style="width: 98%; height: 200px" rows="20" cols="70"><?php echo ((isset($PROCESSED["topic_description"])) ? html_encode($PROCESSED["topic_description"]) : ""); ?></textarea>
					</td>
				</tr>
			</tbody>
			</table>
			</form>
			<?php
		break;
	}

}

