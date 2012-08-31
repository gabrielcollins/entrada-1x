<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Display Clerkship logbook entries in various order.
 *
 * @author Organisation: University of Calgary
 * @author Unit: Undergraduate Medical Education
 * @author Developer: Doug Hall <hall@ucalgary.ca>
 * @copyright Copyright 2009 University of Calgary. All Rights Reserved.
 *
 * $Id: view-entries.api.php 1 2009-11-20 19:36:06Z hall $
*/

@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    dirname(__FILE__) . "/../core/includes",
    dirname(__FILE__) . "/../core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

if ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('event', 'delete', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	if (isset($_POST["event_id"]) && $_POST["event_id"]) {
		$event_id = clean_input($_POST["event_id"], array("trim", "int"));
	} elseif (isset($_GET["id"]) && $_GET["id"]) {
		$event_id = clean_input($_GET["id"], array("trim", "int"));
	} else {
		$event_id = false;
	}
	
	
	if($event_id = (int) $event_id) {
		$query	= "	SELECT a.`event_id`, a.`course_id`, a.`event_title`, b.`organisation_id`
					FROM `events` AS a
					LEFT JOIN `courses` AS b
					ON b.`course_id` = a.`course_id`
					WHERE a.`event_id` = ".$db->qstr($event_id);
		$result	= $db->GetRow($query);
		if ($result) {
			if($ENTRADA_ACL->amIAllowed(new EventResource($result["event_id"], $result["course_id"], $result["organisation_id"]), 'delete')) {
				/**
				 * Check to see if any quizzes are attached to this event.
				 */
				$query		= "	SELECT a.*
								FROM `attached_quizzes` AS a
								LEFT JOIN `quiz_progress` AS b
								ON b.`aquiz_id` = a.`aquiz_id`
								WHERE a.`content_type` = 'event' 
								AND a.`content_id` = ".$db->qstr($event_id);
				$quizzes	= $db->GetAll($query);
				if (($quizzes) && (count($quizzes) > 0)) {
					$ERROR++;
					$ERRORSTR[] = "You cannot delete <a href=\"".ENTRADA_URL."/admin/events?section=content&amp;id=".$event_id."\" style=\"font-weight: bold\">".html_encode($result["event_title"])."</a> at this time because there are quizzes attached. If you need to delete this event please remove any attached quizzes first.";
				} else {

					/**
					 * Remove all records from event_audience table.
					 */
					$query		= "SELECT * FROM `event_audience` WHERE `event_id` = ".$db->qstr($event_id);
					$results	= $db->GetAll($query);
					if($results) {
						foreach($results as $result) {
							$removed[$event_id][$result["audience_type"]][] = $result["audience_value"];
						}

						$query = "DELETE FROM `event_audience` WHERE `event_id` = ".$db->qstr($event_id);
						$db->Execute($query);
					}

					/**
					 * Remove all records from event_contacts table.
					 */
					$query		= "SELECT * FROM `event_contacts` WHERE `event_id` = ".$db->qstr($event_id);
					$results	= $db->GetAll($query);
					if($results) {
						foreach($results as $result) {
							$removed[$event_id]["contacts"][] = $result["proxy_id"];
						}

						$query = "DELETE FROM `event_contacts` WHERE `event_id` = ".$db->qstr($event_id);
						$db->Execute($query);
					}

					/**
					 * Remove all records from event_discussions table.
					 */
					$query		= "SELECT * FROM `event_discussions` WHERE `event_id` = ".$db->qstr($event_id);
					$results	= $db->GetAll($query);
					if($results) {
						foreach($results as $result) {
							$removed[$event_id]["discussion_title"][] = $result["discussion_title"];
						}

						$query = "DELETE FROM `event_discussions` WHERE `event_id` = ".$db->qstr($event_id);
						$db->Execute($query);
					}

					/**
					 * Remove all records from event_ed10 table.
					 */
					$query		= "SELECT * FROM `event_ed10` WHERE `event_id` = ".$db->qstr($event_id);
					$results	= $db->GetAll($query);
					if($results) {
						foreach($results as $result) {
							$removed[$event_id]["ed10_id"][] = $result["ed10_id"];
						}

						$query = "DELETE FROM `event_ed10` WHERE `event_id` = ".$db->qstr($event_id);
						$db->Execute($query);
					}

					/**
					 * Remove all records from event_ed11 table.
					 */
					$query		= "SELECT * FROM `event_ed11` WHERE `event_id` = ".$db->qstr($event_id);
					$results	= $db->GetAll($query);
					if($results) {
						foreach($results as $result) {
							$removed[$event_id]["ed11_id"][] = $result["ed11_id"];
						}

						$query = "DELETE FROM `ed11_id` WHERE `event_id` = ".$db->qstr($event_id);
						$db->Execute($query);
					}

					/**
					 * Remove event_id record from events table.
					 */
					$query		= "SELECT * FROM `events` WHERE `event_id` = ".$db->qstr($event_id);
					$results	= $db->GetAll($query);
					if($results) {
						foreach($results as $result) {
							$removed[$event_id]["event_title"] = $result["event_title"];
						}
						$query = "DELETE FROM `events` WHERE `event_id` = ".$db->qstr($event_id);
						$db->Execute($query);
					}
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "You do not have the permissions required to delete <a href=\"".ENTRADA_URL."/admin/events?section=content&amp;id=".$event_id."\" style=\"font-weight: bold\">".html_encode($result["event_title"])."</a>.<br /><br />If you believe you are receiving this message in error, please contact the administrator.";
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "Unable to remove the requested session from the system.<br /><br />The system administrator has been informed of this issue and will address it shortly; please try again later.";

			application_log("error", "Failed to remove all events from the remove request. Database said: ".$db->ErrorMsg());
		}
		if (!$ERROR) {
			$SUCCESS++;
			$SUCCESSSTR[$SUCCESS]  = "You have successfully removed a session from the system:";
			$SUCCESSSTR[$SUCCESS] .= "<div style=\"padding-left: 15px; padding-bottom: 15px; font-family: monospace\">\n";
			$SUCCESSSTR[$SUCCESS] .= html_encode($result["event_title"]);
			$SUCCESSSTR[$SUCCESS] .= "</div>\n";

			fade_element("out", "display-success-box");
			echo display_success();
			application_log("success", "Successfully removed event id: ".$event_id);
		} else {
			fade_element("out", "display-error-box");
			echo display_error();
		}
	}
}
?>
