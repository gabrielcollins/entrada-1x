<?php
/**
 * Learning Events Schedule live-edit API
 * @author Unit: Medical Education Technology Unit
 * @author Director: Ryan Warner <ryan.warner@queensu.ca>
 * @copyright Copyright 2012 Queen's University, MEdTech Unit
 * 
 * $Id: learning-events-schedule.api.php 2012-05-30 15:20:37Z rw65 $
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


if((isset($_SESSION["isAuthorized"])) && ((bool) $_SESSION["isAuthorized"])) {
	if (isset($_POST["action"]) && !empty($_POST["action"])) {
		$PROCESSED["action"] = clean_input($_POST["action"], array("striptags", "trim"));
		$PROCESSED["data"] = clean_input($_POST["data"], "striptags");
		$PROCESSED["devent_id"] = (int) $_POST["id"];
		switch ($PROCESSED["action"]) {
			case "date" :
				$query = "	SELECT `event_start`
							FROM `draft_events`
							WHERE `devent_id` = ".$db->qstr($PROCESSED["devent_id"]);
				$old_timestamp = $db->GetRow($query);
				if ($PROCESSED["data"]) {
					$PROCESSED["data"] .= " ".date("H:i:s", $old_timestamp["event_start"]);

					$query = "	UPDATE `draft_events`
								SET `event_start` = ".$db->qstr(strtotime($PROCESSED["data"]))."
								WHERE `devent_id` = ".$db->qstr($PROCESSED["devent_id"]);
					if ($db->Execute($query)) {
						echo "<a href=\"".ENTRADA_URL."/admin/events?section=edit&mode=draft&id=".$PROCESSED["devent_id"]."\">".date("Y-m-d", strtotime($PROCESSED["data"]))."</a>";
					} else {
						application_log("error", "An error ocurred when updating the draft_events table when updating event [devent_id: ".$PROCESSED["devent_id"]."]. Database said: ".$db->ErrorMsg());
					}
				} else {
					echo "<a href=\"".ENTRADA_URL."/admin/events?section=edit&mode=draft&id=".$PROCESSED["devent_id"]."\">".date("Y-m-d", $old_timestamp["event_start"])."</a>";
				}
			break;
			case "time" :
				$query = "	SELECT `event_start`
							FROM `draft_events`
							WHERE `devent_id` = ".$db->qstr($PROCESSED["devent_id"]);
				$old_timestamp = $db->GetRow($query);
				$PROCESSED["data"] = substr($PROCESSED["data"], 0, 5);
				if (preg_match("/([01][0-9]|2[0-3]):[0-5][0-9]/i", $PROCESSED["data"]) || empty($PROCESSED["data"])) {
					if ($PROCESSED["data"]) {
						$PROCESSED["data"] = date("Y-m-d", $old_timestamp["event_start"])." ".$PROCESSED["data"];

						$query = "	UPDATE `draft_events`
									SET `event_start` = ".$db->qstr(strtotime($PROCESSED["data"]))."
									WHERE `devent_id` = ".$db->qstr($PROCESSED["devent_id"]);
						if ($db->Execute($query)) {
							echo "<a href=\"".ENTRADA_URL."/admin/events?section=edit&mode=draft&id=".$PROCESSED["devent_id"]."\" class=\"time\">".date("H:i", strtotime($PROCESSED["data"]))."</a>";
						} else {
							application_log("error", "An error ocurred when updating the draft_events table when updating event [devent_id: ".$PROCESSED["devent_id"]."]. Database said: ".$db->ErrorMsg());
						}
					} else {
						echo "<a href=\"".ENTRADA_URL."/admin/events?section=edit&mode=draft&id=".$PROCESSED["devent_id"]."\" class=\"time\" rel=\"".date("H:i", $old_timestamp["event_start"])."\">".date("H:i", $old_timestamp["event_start"])."</a>";
					}
				} else { 
					echo "<script type=\"text/javascript\">alert('24 hour format required');</script>";
					echo "<a href=\"".ENTRADA_URL."/admin/events?section=edit&mode=draft&id=".$PROCESSED["devent_id"]."\" class=\"time\" rel=\"".date("H:i", $old_timestamp["event_start"])."\">".date("H:i", $old_timestamp["event_start"])."</a>";
				}
			break;
			case "title" :
				$query = "	SELECT `event_title`
							FROM `draft_events`
							WHERE `devent_id` = ".$db->qstr($PROCESSED["devent_id"]);
				$old_title = $db->GetRow($query);
				if (!empty($PROCESSED["data"])) {
					$query = "	UPDATE `draft_events`
							SET `event_title` = ".$db->qstr($PROCESSED["data"])."
								WHERE `devent_id` = ".$db->qstr($PROCESSED["devent_id"]);
					if ($db->Execute($query)) {
						echo "<a href=\"".ENTRADA_URL."/admin/events?section=edit&mode=draft&id=".$PROCESSED["devent_id"]."\">".$PROCESSED["data"]."</a>";
					} else {
						application_log("error", "An error ocurred when updating the draft_events table when updating event [devent_id: ".$PROCESSED["devent_id"]."]. Database said: ".$db->ErrorMsg());
					}
				} else {
					echo "<a href=\"".ENTRADA_URL."/admin/events?section=edit&mode=draft&id=".$PROCESSED["devent_id"]."\">".$old_title["event_title"]."</a>";
				}
				
			break;
			default:
				echo "0";
			break;
		}
	}
}