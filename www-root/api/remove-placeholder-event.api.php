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
	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."], and the ability to remove a placeholder event.");
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('event', 'create', false)) {
	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."], and the ability to remove a placeholder event.");
	exit;
} else {
	if ($_GET["event_id"] && $event_id = clean_input($_GET["event_id"], "int")) {
		$query = "DELETE FROM `events` WHERE `event_id` = ".$db->qstr($event_id)." AND `event_title` = 'Placeholder Event'";
		if ($db->Execute($query)) {
			application_log("success", "User [".$ENTRADA_USER->getID()."] successfully removed a placeholder event [".$event_id."] when leaving the event manage page.");
		} else {
			application_log("error", "User [".$ENTRADA_USER->getID()."] was unable to remove a placeholder event [".$event_id."] when leaving the event manage page. Database said: ".$db->ErrorMsg());
		}
	}
}
?>
