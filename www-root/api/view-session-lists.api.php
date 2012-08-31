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
} elseif (!$ENTRADA_ACL->amIAllowed('eventcontent', 'update', false)) {
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
	
	if (isset($_POST["session_id"]) && $_POST["session_id"]) {
		$session_id = clean_input($_POST["session_id"], array("trim", "int"));
	} elseif (isset($_GET["session_id"]) && $_GET["session_id"]) {
		$session_id = clean_input($_GET["session_id"], array("trim", "int"));
	} else {
		$session_id = false;
	}
	
	$query 	= "SELECT * FROM `events`
			WHERE `parent_id` = ".$db->qstr($event_id)."
			ORDER BY `event_start` ASC";
	if ($event_sessions = $db->GetAll($query)) {
		foreach ($event_sessions as $session) {
			if ($session["event_id"] == $session_id) {
				$selected_session_id = $session_id;
			}
		}
		$count = 0;
		$page_count = 1;
		foreach ($event_sessions as $key => $session) {
			$count++;
			if ($count > 15) {
				$count = 1;
				$page_count++;
			}
			if (!isset($selected_session_id) && $key == 0 || $session["event_id"] == $selected_session_id) {
				$chosen_page = $page_count;
			}
		}
		$count = 0;
		$page_count = 1;
		?>
		<div class="session-list" id="page-1" style="width: 100%;<?php echo ($chosen_page == 1 ? "" : " display: none;"); ?>">
		<?php 
		if ($session_id === 0) {
			$selected_session_id = 0;
		}
		?>
		<input type="hidden" id="session-count" name="event_children" value="<?php echo (int)count($event_sessions); ?>" />		
		<?php
		foreach ($event_sessions as $key => $result) {
			if (!isset($selected_session_id) && $key == 0 || $result["event_id"] == $selected_session_id) {
				$selected = true;
				echo "<input type=\"hidden\" value=\"".$result["event_id"]."\" id=\"current-session\"  name=\"current_session\" />";
			} else {
				$selected = false;
			}
			$count++;
			if ($count > 15) {
				$count = 1;
				$page_count++;
				echo "</div>\n";
				echo "<div class=\"session-list\"".($page_count != $chosen_page ? " style=\"display: none;\"" : "")." id=\"page-".$page_count."\">\n";
			}
			if ($ENTRADA_ACL->amIAllowed(new EventResource($result["event_id"], $result["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update') || $ENTRADA_ACL->amIAllowed(new EventContentResource($result["event_id"], $result["course_id"], $result["organisation_id"]), "update")) {
				?>
				<div id="session-line-<?php echo $result["event_id"]; ?>" class="event-session enabled<?php echo $selected ? " selected" : ""; ?>">
					<div id="session-<?php echo $result["event_id"]; ?>" onclick="loadSession(<?php echo $result["event_id"]; ?>)" class="session-entry">
						<?php
						echo limit_chars($result["event_title"], 21);
						?>
					</div>
					<input id="session-name-<?php echo $result["event_id"]; ?>" value="<?php echo $result["event_title"]; ?>" onchange="saveSessionName()" type="text" style="width: 95%; background-color: #EEEEEE; display: none;" />
				</div>
				<?php
			} else {?>
				<div id="session-line-<?php echo $result["event_id"]; ?>" class="event-session disabled">
					<div id="session-<?php echo $result["event_id"]; ?>" class="session-entry">
						<?php
						echo limit_chars($result["event_title"], 21);
						?>
					</div>
				</div>
				<?php
			}
		}
		?>
		<?php
	} else {
		?>
		<input type="hidden" id="current-page" name="current_pages" value="1" />		
		<input type="hidden" value="0" id="current-session" name="current_session" />
		<input type="hidden" id="session-count" name="event_children" value="1" />
		<div id="session-line-0" class="event-session enabled selected">
			<div id="session-0" onclick="loadSession(0)" class="session-entry">
				Session 1
			</div>
			<input id="session-name-0" value="Session 1" onchange="saveSessionName()" type="text" style="width: 95%; background-color: #EEEEEE; display: none;" />
		</div>
		<?php
	}
	?>
	</div>
	<?php
}
?>
