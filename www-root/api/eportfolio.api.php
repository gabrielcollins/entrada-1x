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
	
	if (isset(${$request_var}["proxy_id"]) && $tmp_input = clean_input(${$request_var}["proxy_id"], "int")) {
		$proxy_id = $tmp_input;
	}

	switch ($request) {
		case "POST" :
			switch ($method) {
				case "media-entry" :
				case "create-entry" :
					
					if(${$request_var}["pfartifact_id"] && $tmp_input = clean_input(${$request_var}["pfartifact_id"], "int")) {
						$PROCESSED["pfartifact_id"] = $tmp_input;
					} else {
						add_error("Invalid portfolio entry artifact id.");
					}
					
					if(${$request_var}["description"] && $tmp_input = clean_input(${$request_var}["description"], array("trim"))) {
						$PROCESSED["description"] = $tmp_input;
					} else {
						$PROCESSED["description"] = "";
					}
					
					if(${$request_var}["title"] && $tmp_input = clean_input(${$request_var}["title"], array("trim", "striptags"))) {
						$PROCESSED["title"] = $tmp_input;
					}
					
					if (${$request_var}["type"] && $tmp_input = clean_input(${$request_var}["type"], array("trim", "striptags"))) {
						$PROCESSED["type"] = $tmp_input;
					}
					
					if (isset($_FILES) && $_FILES["file"]["name"] && $tmp_input = clean_input($_FILES["file"]["name"], array("trim", "striptags"))) {
						$PROCESSED["filename"] = preg_replace('/[^a-zA-Z0-9-_\.]/', '', str_replace(" ", "-", trim($tmp_input)));
						
						$allowed_mime_types = array(
							"image/jpeg", "image/png", "application/pdf", 
							"application/x-pdf", "application/excel", 
							"application/vnd.ms-excel", "application/msword", 
							"application/mspowerpoint", "application/vnd.ms-powerpoint", 
							"text/richtext", "application/rtf", "application/x-rtf"
						);
						
						$finfo = new finfo(FILEINFO_MIME);

						$type = $finfo->file($_FILES["file"]["tmp_name"]);
						
						$mime_type = explode("; ", $type);
						
						if (!in_array($mime_type[0], $allowed_mime_types)) {
							add_error("Invalid file type. ".$mime_type[0]);
						}
					}
					
					if (isset($PROCESSED["pfartifact_id"]) && !$ERROR) {
						
						$PROCESSED["proxy_id"] = $ENTRADA_USER->getID(); // @todo: this needs to be fixed
						$PROCESSED["submitted_date"] = time();
						$PROCESSED["reviewed_date"] = "0";
						$PROCESSED["flag"] = "0";
						$PROCESSED["flagged_by"] = "0";
						$PROCESSED["flagged_date"] = "0";
						$PROCESSED["order"] = "0";
						$PROCESSED["updated_date"] = date(time());
						$PROCESSED["updated_by"] = $ENTRADA_USER->getID();
						$_edata = array();
						$_edata["description"] = $PROCESSED["description"];
						$_edata["title"] = $PROCESSED["title"];
						if ($PROCESSED["filename"]) {
							$_edata["filename"] = $PROCESSED["filename"];
						}
						$PROCESSED["_edata"] = serialize($_edata);
						
						$pentry = new Models_Eportfolio_Entry();
						
						if ($pentry->fromArray($PROCESSED)->insert()) {
							if ($PROCESSED["filename"]) {
								if ($pentry->saveFile($_FILES["file"]["tmp_name"])) {
									if (isset($_POST["isie"]) && $_POST["isie"] == "isie") {
										header('Location: '.ENTRADA_URL.'/profile/eportfolio#'.$pfolder->getID());
									} else {
										echo json_encode(array("status" => "success", "data" => array("pentry_id" => $pentry->getID(), "edata" => $pentry->getEdataDecoded(), "submitted_date" => $PROCESSED["submitted_date"])));
									}
								} else {
									if (isset($_POST["isie"]) && $_POST["isie"] == "isie") {
										header('Location: '.ENTRADA_URL.'/profile/eportfolio#'.$pfolder->getID());
									} else {
										echo json_encode(array("status" => "error", "data" => "Failed to save file"));
									}
								}
							} else {
								echo json_encode(array("status" => "success", "data" => array("pentry_id" => $pentry->getID(), "edata" => $pentry->getEdataDecoded(), "submitted_date" => $PROCESSED["submitted_date"])));
							}
						} else {
							if (isset($_POST["isie"]) && $_POST["isie"] == "isie") {
								header('Location: '.ENTRADA_URL.'/profile/eportfolio#'.$pfolder->getID());
							} else {
								echo json_encode(array("error" => "error", "data" => "Unable to create portfolio entry."));
							}
						}
						
					} else {
						echo json_encode(array("status" => "error", "data" => $ERRORSTR));
					}
				break;
				case "create-artifact" :
					if(${$request_var}["pfolder_id"] && $tmp_input = clean_input(${$request_var}["pfolder_id"], "int")) {
						$PROCESSED["pfolder_id"] = $tmp_input;
					} else {
						add_error("Invalid portolio folder ID provided.");
					}
					
					if(${$request_var}["description"] && $tmp_input = clean_input(${$request_var}["description"], array("trim", "striptags"))) {
						$PROCESSED["description"] = $tmp_input;
					}
					
					if(${$request_var}["title"] && $tmp_input = clean_input(${$request_var}["title"], array("trim", "striptags"))) {
						$PROCESSED["title"] = $tmp_input;
					} else {
						add_error("You must provide a <strong>Title</strong> for this Artifact.");
					}
					
					if(${$request_var}["start_date"] && $tmp_input = strtotime(clean_input(${$request_var}["start_date"], array("trim", "striptags")))) {
						$PROCESSED["start_date"] = $tmp_input;
					} else {
						$PROCESSED["start_date"] = 0;
					}
					
					if(${$request_var}["finish_date"] && $tmp_input = strtotime(clean_input(${$request_var}["finish_date"], array("trim", "striptags")))) {
						$PROCESSED["finish_date"] = $tmp_input;
					} else {
						$PROCESSED["finish_date"] = 0;
					}
					
					if (!$ERROR) {
						
						$PROCESSED["artifact_id"] = 2;
						$PROCESSED["proxy_id"] = $ENTRADA_USER->getID(); // @todo: this needs to be fixed
						$PROCESSED["submitted_date"] = time();
						$PROCESSED["updated_date"] = date(time());
						$PROCESSED["updated_by"] = $ENTRADA_USER->getID();
						$PROCESSED["order"] = 0;
						
						$pentry = new Models_Eportfolio_Folder_Artifact();
						
						if ($pentry->fromArray($PROCESSED)->insert()) {
							echo json_encode(array("status" => "success", "data" => array("pentry_id" => $pentry->getID(), "title" => $pentry->getTitle(), "edata" => $pentry->getEdataDecoded())));
						} else {
							echo json_encode(array("error" => "error", "data" => "Unable to create portfolio entry., DB said: ".$db->ErrorMsg()));
						}
						
					} else {
						echo json_encode(array("status" => "error", "data" => $ERRORSTR));
					}
				break;
				case "create-folder" :
					if(${$request_var}["portfolio_id"] && $tmp_input = clean_input(${$request_var}["portfolio_id"], "int")) {
						$PROCESSED["portfolio_id"] = $tmp_input;
					} else {
						add_error("Invalid portfolio ID.");
					}
					
					if(${$request_var}["description"] && $tmp_input = clean_input(${$request_var}["description"], array("trim", "striptags"))) {
						$PROCESSED["description"] = $tmp_input;
					}
					
					if(${$request_var}["title"] && $tmp_input = clean_input(${$request_var}["title"], array("trim", "striptags"))) {
						$PROCESSED["title"] = $tmp_input;
					} else {
						add_error("Invalid title.");
					}
					
					if(${$request_var}["allow_learner_artifacts"] && $tmp_input = clean_input(${$request_var}["allow_learner_artifacts"], array("int"))) {
						$PROCESSED["allow_learner_artifacts"] = $tmp_input;
					} else {
						$PROCESSED["allow_learner_artifacts"] = 0;
					}
					
					if (isset($PROCESSED["portfolio_id"])) {
						$PROCESSED["order"] = 1;
						$PROCESSED["updated_by"] = $ENTRADA_USER->getID();
						$PROCESSED["updated_date"] = time();
						
						$pfolder = new Models_Eportfolio_Folder();
						
						if ($pfolder->fromArray($PROCESSED)->insert()) {
							echo json_encode(array("status" => "success", "data" => $pfolder->toArray()));
						} else {
							echo json_encode(array("error" => "error", "data" => "Unable to create portfolio entry."));
						}
						
					} else {
						echo json_encode(array("status" => "error", "data" => $ERRORSTR));
					}
				break;
				case "add-pentry-comment" :
					if(${$request_var}["entry-comment"] && $tmp_input = clean_input(${$request_var}["entry-comment"], array("trim", "allowedtags"))) {
						$PROCESSED["comment"] = $tmp_input;
					} else {
						add_error("The comment field can not be empty.");
					}
					
					if(${$request_var}["pentry_id"] && $tmp_input = clean_input(${$request_var}["pentry_id"], array("int"))) {
						$PROCESSED["pentry_id"] = $tmp_input;
					} else {
						add_error("An error occured attempting to attach comment to entry.");
					}
					
					if (!$ERROR) {
						
						$PROCESSED["proxy_id"] = $ENTRADA_USER->getID();
						$PROCESSED["submitted_date"] = time();
						$PROCESSED["flag"] = 0;
						$PROCESSED["active"] = 1;
						$PROCESSED["updated_date"] = date(time());
						$PROCESSED["updated_by"] = $ENTRADA_USER->getID();
						
						$comment = new Models_Eportfolio_Entry_Comment($PROCESSED);
						
						if ($comment->insert()) {
							echo json_encode(array("status" => "success", "data" => $comment->toArray()));
						} else {
							echo json_encode(array("status" => "error", "data" => "An error occurred when attempting to store the comment."));
						}
					}
				break;
				case "create-portfolio" :
					
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
				case "get-portfolio-members" :
					if (${$request_var}["portfolio_id"] && $tmp_input = clean_input(${$request_var}["portfolio_id"], "int")) {
						$PROCESSED["portfolio_id"] = $tmp_input;
					}
					
					$flagged = false;
					if (${$request_var}["flagged"] && ${$request_var}["flagged"] == true) {
						$flagged = true;
					}

					if ($PROCESSED["portfolio_id"]) {
						$portfolio = Models_Eportfolio::fetchRow($PROCESSED["portfolio_id"]);
						if ($portfolio) {
							$group = $portfolio->getGroup($flagged);
							echo json_encode(array("status" => "success", "data" => $group));
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

					$flagged = false;
					if (${$request_var}["flagged"] && ${$request_var}["flagged"] == true) {
						$flagged = true;
					}
					
					if (${$request_var}["proxy_id"] && $tmp_input = clean_input(${$request_var}["proxy_id"], "int")) {
						$PROCESSED["proxy_id"] = $tmp_input;
					}
					
					if ($PROCESSED["portfolio_id"]) {
						$folders = Models_Eportfolio_Folder::fetchAll($PROCESSED["portfolio_id"], $flagged, $PROCESSED["proxy_id"]);
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
						$folder_artifacts = Models_Eportfolio_Folder_Artifact::fetchAll($PROCESSED["pfolder_id"], (isset($proxy_id) ? $proxy_id : NULL));
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
				case "get-artifact-entries" :
					if (isset(${$request_var}["pfartifact_id"]) && $tmp_input = clean_input(${$request_var}["pfartifact_id"], "int")) {
						$PROCESSED["pfartifact_id"] = $tmp_input;
					}
					
					if ($PROCESSED["pfartifact_id"]) { 
						$artifact_entries = Models_Eportfolio_Entry::fetchAll($PROCESSED["pfartifact_id"], (isset($proxy_id) ? $proxy_id : NULL));
						if ($artifact_entries) {
							$ae_data = array();
							$i = 0;
							foreach ($artifact_entries as $artifact_entry) {
								$ae_data[$i]["entry"] = $artifact_entry->toArray();
								$comments = $artifact_entry->getComments();
								if ($comments) {
									$j = 0;
									foreach ($comments as $comment) {
										$commentor = User::get($comment->getProxyID());
										$comments_array[$j] = $comment->toArray();
										$comments_array[$j]["submitted_date"] = date("Y-m-d H:i", $comments_array[$j]["submitted_date"]);
										$comments_array[$j]["commentor"] = $commentor->getFullname(false);
										$j++;
									}
									$ae_data[$i]["comments"] = $comments_array;
								}
								$i++;
							}
							echo json_encode(array("status" => "success", "data" => $ae_data));
						} else {
							echo json_encode(array("status" => "error", "data" => "No entries attached to this artifact."));
						}
					} else {
						echo json_encode(array("status" => "error", "data" => "No artifact ID or invalid artifact ID."));
					}
				break;
				case "get-entry" :
					if (isset(${$request_var}["pentry_id"]) && $tmp_input = clean_input(${$request_var}["pentry_id"], "int")) {
						$PROCESSED["pentry_id"] = $tmp_input;
					}
					
					if ($PROCESSED["pentry_id"]) {
						$entry = Models_Eportfolio_Entry::fetchRow($PROCESSED["pentry_id"]);
						
						if ($entry) {
							$e_data = $entry->toArray();
							echo json_encode(array("status" => "success", "data" => $e_data));
						} else {
							echo json_encode(array("status" => "error", "data" => "No entry found with this ID."));
						}
					} else {
						echo json_encode(array("status" => "error", "data" => "No entry ID or invalid entry ID."));
					}
				break;
			}
		break;
	}
	
}