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
 * API to handle interaction with eportfolio module.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <ryan.warner@queensu.ca>
 * @copyright Copyright 2013 Queen's University. All Rights Reserved.
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

if((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("gradebook", "update", false)) {
	$ERROR++;
	$ERRORSTR[]	= "You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {

	$request = strtoupper(clean_input($_SERVER['REQUEST_METHOD'], "alpha"));
	$method = clean_input($_POST["method"], array("trim", "striptags"));
	
	switch ($request) {
		case "POST" :
			switch ($method) {
				case "create-folder" :
					
				break;
			}
		break;
		case "GET" :
			switch ($method) {
				case "get-folders" :
					if ($_POST["portfolio_id"] && $tmp_input = clean_input($_POST["portfolio_id"], "int")) {
						$PROCESSED["portfolio_id"] = $tmp_input;
					}

					if ($PROCESSED["portfolio_id"]) {
						$folders = Models_Eportfolio_Folder::fetchAll($PROCESSED["portfolio_id"]);
						if ($folders) {
							$f_data = array();
							foreach ($folders as $folder) {
								$f_data[$folder->getID()] = $folder->toArray();
							}
							echo json_encode(array("status" => "success", "data" => $f_data));
						} else {
							echo json_encode(array("status" => "error", "data" => "No folders attached to this portfolio ID."));
						}
					} else {
						echo json_encode(array("status" => "error", "data" => "Invalid portfolio ID."));
					}
				break;
			}
		break;
	}
	
}