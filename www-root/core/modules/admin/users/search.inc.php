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
 * Used by the add / edit users sections to search for a number / username
 * that exists in the database already. This file outputs JSON data only, and
 * does not use any templates.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_USERS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("user", "update", false)) {
	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	/**
	 * Clears all open buffers so we can return a simple JSON response.
	 */
	ob_clear_open_buffers();
	
	if (isset($_GET["number"]) && ($number = (clean_input($_GET["number"], array("int"))))) {
		$query = "	SELECT a.*, b.`account_active`
					FROM `".AUTH_DATABASE."`.`user_data` AS a
					LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
					ON a.`id` = b.`user_id`
					AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
					WHERE `number` = ".$db->qstr($number);
		$result = $db->GetRow($query);
		if ($result) {
			if ($result["account_active"] == "true") {
				$result["message"] = "<ul><li>A user with that id has been found in the system, this existing user already has access to this application. Please click <strong><a onclick=\"$$('input [type: text]').each(function (e) { if (e.disabled) e.enable().value = ''; }); $('display-notice').remove(); $$('.departments').each( function (e) { e.show(); }); $('prefix').enable(); $('access_finish_date').disabled = !$('access_finish').checked;\">here</a></strong> to choose another user.</li></ul>";
			} else {
				$result["message"] = "<ul><li>A user with that id has been found in the system, this existing user will be given access to this application if you select &quot;Add User&quot; now, otherwise click <strong><a onclick=\"$$('input [type: text]').each(function (e) { if (e.disabled) e.enable().value = ''; }); $('display-notice').remove(); $$('.departments').each( function (e) { e.show(); }); $('prefix').enable(); $('access_finish_date').disabled = !$('access_finish').checked;\">here</a></strong> to choose another user.</li></ul>";
			}

			header("Content-type: application/json");
			echo json_encode($result);
		}
	} elseif (isset($_GET["username"]) && ($username = (clean_input($_GET["username"], array("credentials"))))) {
		$query = "	SELECT a.*, b.`account_active`
					FROM `".AUTH_DATABASE."`.`user_data` AS a
					LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
					ON a.`id` = b.`user_id`
					AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
					WHERE `username` = ".$db->qstr($username);
		$result = $db->GetRow($query);
		if ($result) {
			if ($result["account_active"] == "true") {
				$result["message"] = "<ul><li>A user with that id has been found in the system, this existing user already has access to this application. Please click <strong><a onclick=\"$$('input [type: text]').each(function (e) { if (e.disabled) e.enable().value = ''; }); $('display-notice').remove(); $$('.departments').each( function (e) { e.show(); }); $('prefix').enable(); $('access_finish_date').disabled = !$('access_finish').checked;\">here</a></strong> to choose another user.</li></ul>";
			} else {
				$result["message"] = "<ul><li>A user with that id has been found in the system, this existing user will be given access to this application if you select &quot;Add User&quot; now, otherwise click <strong><a onclick=\"$$('input [type: text]').each(function (e) { if (e.disabled) e.enable().value = ''; }); $('display-notice').remove(); $$('.departments').each( function (e) { e.show(); }); $('prefix').enable(); $('access_finish_date').disabled = !$('access_finish').checked;\">here</a></strong> to choose another user.</li></ul>";
			}

			header("Content-type: application/json");
			echo json_encode($result);
		}
	} elseif (isset($_GET["id"]) && ($proxy_id = (clean_input($_GET["id"], array("int"))))) {
		$query = "	SELECT a.*, b.`account_active`
					FROM `".AUTH_DATABASE."`.`user_data` AS a
					LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
					ON a.`id` = b.`user_id`
					AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
					WHERE a.`id` = ".$db->qstr($proxy_id);
		$result = $db->getRow($query);
		if ($result) {
			if ($result["account_active"] == "true") {
				$result["message"] = "<ul><li>A user with that id has been found in the system, this existing user already has access to this application. Please click <strong><a onclick=\"$$('input [type: text]').each(function (e) { if (e.disabled) e.enable().value = ''; }); $('display-notice').remove(); $$('.departments').each( function (e) { e.show(); }); $('prefix').enable(); $('access_finish_date').disabled = !$('access_finish').checked;\">here</a></strong> to choose another user.</li></ul>";
			} else {
				$result["message"] = "<ul><li>A user with that id has been found in the system, this existing user will be given access to this application if you select &quot;Add User&quot; now, otherwise click <strong><a onclick=\"$$('input [type: text]').each(function (e) { if (e.disabled) e.enable().value = ''; }); $('display-notice').remove(); $$('.departments').each( function (e) { e.show(); }); $('prefix').enable(); $('access_finish_date').disabled = !$('access_finish').checked;\">here</a></strong> to choose another user.</li></ul>";
			}

			header("Content-type: application/json");
			echo json_encode($result);
		}
	}
	exit;
}