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
} elseif (!$ENTRADA_ACL->amIAllowed("eportfolio", "read", false)) {
	$ERROR++;
	$ERRORSTR[]	= "You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {

	$request = strtoupper(clean_input($_SERVER['REQUEST_METHOD'], "alpha"));
	
	$request_var = "_".$request;
	
	$method = clean_input(${$request_var}["method"], array("trim", "striptags"));

	switch ($request) {
		case "POST" :
			switch ($method) {
				case "create-entry" :
					if(${$request_var}["pfartifact_id"] && $tmp_input = clean_input(${$request_var}["pfartifact_id"], "int")) {
						$PROCESSED["pfartifact_id"] = $tmp_input;
					}
					
					if(${$request_var}["description"] && $tmp_input = clean_input(${$request_var}["description"], array("trim", "striptags"))) {
						$PROCESSED["description"] = $tmp_input;
					}
					
					if (isset($PROCESSED["pfartifact_id"])) {
						
						$PROCESSED["proxy_id"] = $ENTRADA_USER->getID(); // @todo: this needs to be fixed
						$PROCESSED["submitted_date"] = time();
						$PROCESSED["reviewed_date"] = "0";
						$PROCESSED["flag"] = "0";
						$PROCESSED["flagged_by"] = "0";
						$PROCESSED["flagged_date"] = "0";
						$PROCESSED["order"] = "0";
						$PROCESSED["updated_date"] = date(time());
						$PROCESSED["updated_by"] = $ENTRADA_USER->getID();
						$PROCESSED["_edata"] = serialize(array("description" => $PROCESSED["description"]));
						
						$pentry = new Models_Eportfolio_Entry();
						
						if ($pentry->fromArray($PROCESSED)->insert()) {
							echo json_encode(array("status" => "success", "data" => array("pentry_id" => $pentry->getID(), "edata" => $pentry->getEdataDecoded())));
						} else {
							echo json_encode(array("error" => "error", "data" => "Unable to create portfolio entry."));
						}
						
					} else {
						echo json_encode(array("status" => "error", "data" => "No portfolio folder artifact ID provided or invalid portfolio folder artifact ID provided."));
					}
				break;
				case "create-folder" :
					
				break;
			}
		break;
		case "GET" :
			switch ($method) {
				case "get-portfolio" :
					if (${$request_var}["portfolio_id"] && $tmp_input = clean_input(${$request_var}["portfolio_id"], "int")) {
						$PROCESSED["portfolio_id"] = $tmp_input;
					}

					if ($PROCESSED["portfolio_id"]) {
						$portfolio = Models_Eportfolio::fetchRow($PROCESSED["portfolio_id"]);
						if ($portfolio) {
							$p_data = $portfolio->toArray();
							echo json_encode(array("status" => "success", "data" => $p_data));
						} else {
							echo json_encode(array("status" => "error", "data" => "No portfolio found with this portfolio ID."));
						}
					} else {
						echo json_encode(array("status" => "error", "data" => "Invalid portfolio ID."));
					}
				break;
				case "get-artifacts" :
					$artifacts = Models_Eportfolio_Artifact::fetchAll();
					if ($artifacts) {
						foreach ($artifacts as $artifact) {
							$a_data[] = $artifact->toArray();
						}
						echo json_encode(array("status" => "success", "data" => $a_data));
					} else {
						echo json_encode(array("error" => "success", "data" => "Could not find any artifacts."));
					}
				break;
				case "get-folders" :
					if (${$request_var}["portfolio_id"] && $tmp_input = clean_input(${$request_var}["portfolio_id"], "int")) {
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
				case "get-folder" :
					if (${$request_var}["pfolder_id"] && $tmp_input = clean_input(${$request_var}["pfolder_id"], "int")) {
						$PROCESSED["pfolder_id"] = $tmp_input;
					}
					
					if ($PROCESSED["pfolder_id"]) {
						$folder = Models_Eportfolio_Folder::fetchRow($PROCESSED["pfolder_id"]);
						if ($folder) {
							$f_data = $folder->toArray();
							echo json_encode(array("status" => "success", "data" => $f_data));
						} else {
							echo json_encode(array("status" => "error", "data" => "No folder found with this ID."));
						}
					} else {
						echo json_encode(array("status" => "error", "data" => "No portfolio folder ID or invalid portfolio folder ID."));
					}
				break;
				case "get-folder-artifacts" :
					if (${$request_var}["pfolder_id"] && $tmp_input = clean_input(${$request_var}["pfolder_id"], "int")) {
						$PROCESSED["pfolder_id"] = $tmp_input;
					}
					
					if ($PROCESSED["pfolder_id"]) {
						$folder_artifacts = Models_Eportfolio_Folder_Artifact::fetchAll($PROCESSED["pfolder_id"]);
						if ($folder_artifacts) {
							$fa_data = array();
							foreach ($folder_artifacts as $folder_artifact) {
								$fa_data[] = $folder_artifact->toArray();
							}
							echo json_encode(array("status" => "success", "data" => $fa_data));
						} else {
							echo json_encode(array("status" => "error", "data" => "No artifacts attached to this portfolio folder ID."));
						}
					} else {
						echo json_encode(array("status" => "error", "data" => "No portfolio folder ID or invalid portfolio folder ID."));
					}
				break;
				case "get-folder-artifact" :
					if (${$request_var}["pfartifact_id"] && $tmp_input = clean_input(${$request_var}["pfartifact_id"], "int")) {
						$PROCESSED["pfartifact_id"] = $tmp_input;
					}
					
					if ($PROCESSED["pfartifact_id"]) {
						$folder_artifact = Models_Eportfolio_Folder_Artifact::fetchRow($PROCESSED["pfartifact_id"]);
						if ($folder_artifact) {
							$fa_data = $folder_artifact->toArray();
							echo json_encode(array("status" => "success", "data" => $fa_data));
						} else {
							echo json_encode(array("status" => "error", "data" => "No artifacts attached to this portfolio folder ID."));
						}
					} else {
						echo json_encode(array("status" => "error", "data" => "No portfolio folder artifact ID or invalid portfolio folder artifact ID."));
					}
				break;
			}
		break;
	}
	
}