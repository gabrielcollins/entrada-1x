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
 * This file is used to display events from the entrada.events table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if (!defined("PARENT_INCLUDED")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_RELATIVE);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("event", "read", false)) {
	add_error("Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} elseif(defined('ALLOW_WEB_CONFERENCING') && ALLOW_WEB_CONFERENCING) {
	require_once("library/Entrada/webconferencing/ConferenceFactory.inc.php");
	$CONFERENCE_ID		= 0;
	if ((isset($_GET["id"])) && ((int) trim($_GET["id"]))) {
		$CONFERENCE_ID	= (int) trim($_GET["id"]);
	}

	
	if ($CONFERENCE_ID) {
		$conference = ConferenceFactory::getConferenceById($CONFERENCE_ID);
		$action = "attendee";
		$return_url = "/";
		switch($conference->attached_type){
			case 'event':
				$return_url = "/events?id=".$conference->attached_id;
				$query = "	SELECT a.*,b.`organisation_id` 
							FROM `events` a 
							JOIN `courses` b
							ON a.`course_id` = b.`course_id`
							WHERE a.`event_id` = ".$db->qstr($conference->attached_id);
				$event_info = $db->GetRow($query);
				if($ENTRADA_ACL->amIAllowed(new EventContentResource($event_info["event_id"], $event_info["course_id"], $event_info["organisation_id"]), "update")){
					$action = "admin";
				}
				break;
		}
		if ($conference->isLive()) {
			if ($url = $conference->buildURL($action)) {
				header('Location: '.$url);
			}else {
				echo display_error("Unable to build the URL for selected conference. <a href=\"".ENTRADA_URL.$return_url."\">Click here</a> to return.");	
			}
		} else {
				echo display_error("This conference is not live. If you believe you are seeing this message in error, please contact an administrator. Otherwise you can <a href=\"".ENTRADA_URL.$return_url."\">click here</a> to return.");	
		}
	} else {
		echo display_error("Invalid conference ID provided.");
	}
} else {
		echo display_error("Web conferencing is not enabled. Please contact an administrator.");
}