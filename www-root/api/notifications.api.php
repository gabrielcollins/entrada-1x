<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * ??? @todo Please document this.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
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

if (isset($_GET["action"]) && $_GET["action"] == "view") {
	if ((isset($_GET["type"]) && ($notify_type = clean_input($_GET["type"], array("string", "nows"))))
		&& (isset($_GET["id"]) && ($record_id = clean_input($_GET["id"], array("int"))))
		&& (isset($_GET["community_id"]) && ($community_id = clean_input($_GET["community_id"], array("int"))))) {
		$active = $db->GetOne("SELECT `notify_active` FROM `community_notify_members` WHERE `proxy_id` = ".$db->qstr($_SESSION["details"]["id"])." AND `community_id` = ".$db->qstr($community_id)." AND `record_id` = ".$db->qstr($record_id)." AND `notify_type` = ".$db->qstr($notify_type));
		if ($active == null && ($notify_type == "announcements" || $notify_type == "events")) {
			$active = true;
		}
		echo "<span style=\"cursor: pointer;\" onclick=\"promptNotifications(".(isset($active) && $active == 1 ? "'1'" : "'0'").")\"><img src=\"".ENTRADA_URL."/images/email-".(isset($active) && $active == 1 ? "off.gif\" /> Unsubscribe to E-Mail" : "on.gif\" /> Subscribe to E-Mail")."</span>";
	}
} elseif (isset($_GET["action"]) && $_GET["action"] == "edit") {
	if ((isset($_GET["type"]) && ($notify_type = clean_input($_GET["type"], array("string", "nows"))))
		&& (isset($_GET["id"]) && ($record_id = clean_input($_GET["id"], array("int"))))
		&& (isset($_GET["community_id"]) && ($community_id = clean_input($_GET["community_id"], array("int"))))) {
		if (isset($_GET["active"]) && $_GET["active"]) {
			$notify_active = 1;
		} else {
			$notify_active = 0;
		}
		$current_notify = $db->GetOne("SELECT `proxy_id` FROM `community_notify_members` WHERE `proxy_id` = ".$db->qstr($_SESSION["details"]["id"])." AND `community_id` = ".$db->qstr($community_id)." AND `record_id` = ".$db->qstr($record_id)." AND `notify_type` = ".$db->qstr($notify_type));
		if ($current_notify) {
			if ($db->Execute("UPDATE `community_notify_members` SET `notify_active` = ".$db->qstr($notify_active)." WHERE `proxy_id` = ".$db->qstr($_SESSION["details"]["id"])." AND `community_id` = ".$db->qstr($community_id)." AND `record_id` = ".$db->qstr($record_id)." AND `notify_type` = ".$db->qstr($notify_type))) {
				echo ($notify_active == 1 ? "Activation" : "Deactivation")." of notifications for this resource successful.";
			} else {
				echo "There was an issue while trying to ".($notify_active ? "activate" : "deactivate")." notifications for this resource.";
			}
		} else {
			if ($db->Execute("INSERT INTO `community_notify_members` (`notify_active`, `proxy_id`, `community_id`, `record_id`, `notify_type`) VALUES (".$db->qstr($notify_active).", ".$db->qstr($_SESSION["details"]["id"]).", ".$db->qstr($community_id).", ".$db->qstr($record_id).", ".$db->qstr($notify_type).")")) {
				echo ($notify_active == 1 ? "Activation" : "Deactivation")." of notifications for this resource successful.";
			} else {
				echo "There was an issue while trying to ".($notify_active ? "activate" : "deactivate")." notifications for this resource.";
			}
		}
	}
}
?>