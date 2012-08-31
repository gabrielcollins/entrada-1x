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
 * api for modifying gradebook assessment exceptions
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
require_once("Entrada/gradebook/handlers.inc.php");

if((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("gradebook", "update", false)) {
	$ERROR++;
	$ERRORSTR[]	= "You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	ob_clear_open_buffers();
	if (isset($_POST["proxy_id"]) && $_POST["proxy_id"]) {
		$proxy_id = clean_input($_POST["proxy_id"], "int");
	}
	
	if (isset($_POST["assessment_id"]) && $_POST["assessment_id"]) {
		$assessment_id = clean_input($_POST["assessment_id"], "int");
	}
	
	if (isset($_POST["grade_weighting"])) {
		$grade_weighting = clean_input($_POST["grade_weighting"], "int");
	}
	
	if (isset($_POST["remove"]) && $_POST["remove"] == "1") {
		$remove = true;
	} else {
		$remove = false;
	}
	
	if ($assessment_id && $proxy_id) {
		$query = "	SELECT a.`grade_weighting`, b.`id`, CONCAT_WS(', ', b.`lastname`, b.`firstname`) AS `fullname`, c.`grade_weighting` AS `custom_weighting`
					FROM `assessments` AS a
					JOIN `".AUTH_DATABASE."`.`user_data` AS b
					ON b.`id` = ".$db->qstr($proxy_id)."
					LEFT JOIN `assessment_exceptions` AS c
					ON b.`id` = c.`proxy_id`
					AND a.`assessment_id` = c.`assessment_id`
					WHERE a.`assessment_id` = ".$db->qstr($assessment_id);
		$result = $db->GetRow($query);
		if ($result) {
			if ($remove && isset($result["custom_weighting"]) && $result["custom_weighting"] !== NULL) {
				$query = "	DELETE FROM `assessment_exceptions` 
							WHERE `proxy_id` = ".$db->qstr($proxy_id)."
							AND `assessment_id` = ".$db->qstr($assessment_id);
				if (!$db->Execute($query)) {
					application_log("error", "An error was encountered while attempting to delete an assessment [".$assessment_id."] exception for user [".$proxy_id."]. Database said: ".$db->ErrorMsg());
				}
			} elseif (isset($grade_weighting) && (($grade_weighting != $result["grade_weighting"] && $grade_weighting != $result["custom_weighting"]) || $grade_weighting === 0)) {
				if (isset($result["custom_weighting"]) && ($result["custom_weighting"] === "0" || $result["custom_weighting"])) {
					$query = "	UPDATE `assessment_exceptions` 
								SET `grade_weighting` = ".$db->qstr($grade_weighting)."
								WHERE `assessment_id` = ".$db->qstr($assessment_id)."
								AND `proxy_id` = ".$db->qstr($proxy_id);
					if (!$db->Execute($query)) {
						application_log("error", "An error was encountered while attempting to update an assessment [".$assessment_id."] exception for user [".$proxy_id."]. Database said: ".$db->ErrorMsg());
					}
				} elseif (!isset($result["custom_weighting"]) || !$result["custom_weighting"]) {
					$query = "	INSERT INTO `assessment_exceptions` 
								(`assessment_id`, `proxy_id`, `grade_weighting`) 
								VALUES 
								(".$db->qstr($assessment_id).", ".$db->qstr($proxy_id).", ".$db->qstr($grade_weighting).")";
					if (!$db->Execute($query)) {
						application_log("error", "An error was encountered while attempting to insert an assessment [".$assessment_id."] exception for user [".$proxy_id."]. Database said: ".$db->ErrorMsg());
					}
				}
			}
		}
		$query = "	SELECT a.`id` AS `proxy_id`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`grade_weighting`
					FROM `".AUTH_DATABASE."`.`user_data` AS a
					JOIN `assessment_exceptions` AS b
					ON a.`id` = b.`proxy_id`
					AND b.`assessment_id` = ".$db->qstr($assessment_id)."
					ORDER BY a.`lastname`";
		$students = $db->GetAll($query);
		if ($students) {
			foreach ($students as $student) {
				if (isset($student["grade_weighting"]) && $student["grade_weighting"] !== NULL) {
					echo "	<li id=\"proxy_".$student["proxy_id"]."\"><span id=\"".$student["proxy_id"]."_name\">".$student["fullname"]."</span>
								<a style=\"cursor: pointer;\" onclick=\"delete_exception('".$student["proxy_id"]."', '".$assessment_id."');\" class=\"remove\">
								<img src=\"".ENTRADA_URL."/images/action-delete.gif\">
								</a>
								<span class=\"duration_segment_container\">
									Weighting: <input class=\"duration_segment\" id=\"student_exception_".$student["proxy_id"]."\" name=\"student_exception[]\" onkeyup=\"modify_exception('".$student["proxy_id"]."', '".$assessment_id."', this.value);\" value=\"".$student["grade_weighting"]."\">
								</span>
							</li>";
				}
			}
		} else {
			echo "<div class=\"display-notice\">There are currently no students with custom grade weighting in the system for this assessment.</div>";
		}
	}
}

exit;