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
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 */

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_GRADEBOOK"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("gradebook", "update", false)) {
	$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[] = "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {

	$ASSESSMENT_IDS = array();
	if (isset($_GET["assessment_ids"]) && ($tmp_input = explode(",", $_GET["assessment_ids"]))) {
		foreach ($tmp_input as $assessment_id) {
			$assessment_id = clean_input($assessment_id, array("trim", "int"));

			if ($assessment_id) {
				$ASSESSMENT_IDS[] = $assessment_id;
			}
		}
	}

	if ((isset($_GET["cohort"])) && ($cohort = clean_input($_GET["cohort"], array("trim", "int")))) {
		$COHORT = $cohort;
	}
	
	if ($COURSE_ID) {
		$query = "	SELECT * FROM `courses`
					WHERE `course_id` = ".$db->qstr($COURSE_ID)."
					AND `course_active` = '1'";
		$course_details	= $db->GetRow($query);
		
		if ($course_details && $ENTRADA_ACL->amIAllowed(new GradebookResource($course_details["course_id"], $course_details["organisation_id"]), "read")) {
			if (!empty($ASSESSMENT_IDS)) {
				$query = "	SELECT `assessments`.*,`assessment_marking_schemes`.`id` as `marking_scheme_id`, `assessment_marking_schemes`.`handler`
							FROM `assessments`
							LEFT JOIN `assessment_marking_schemes` ON `assessment_marking_schemes`.`id` = `assessments`.`marking_scheme_id`
							WHERE `assessments`.`assessment_id` IN (".implode(",", $ASSESSMENT_IDS).")";
			} else {
				$query = "	SELECT `assessments`.*,`assessment_marking_schemes`.`id` as `marking_scheme_id`, `assessment_marking_schemes`.`handler`
							FROM `assessments`
							LEFT JOIN `assessment_marking_schemes` ON `assessment_marking_schemes`.`id` = `assessments`.`marking_scheme_id`
							WHERE `assessments`.`cohort` = ".$db->qstr($COHORT);
			}
			$assessments = $db->GetAll($query);
			if ($assessments) {
				// CSV Download
				$groups = array();
				$query = "SELECT b.`id` AS `proxy_id`, CONCAT_WS(', ', b.`lastname`, b.`firstname`) AS `fullname`, b.`number`, c.`group`, c.`role`";

				foreach ($assessments as $key => $assessment) {
					$groups[] = $assessment["cohort"];
					$query .= ", assessment_$key.`grade_id` AS `".$key."_grade_id`, assessment_$key.`value` AS `".$key."_grade_value` ";
				}
				
				$query .= "	FROM `".AUTH_DATABASE."`.`user_data` AS b
							JOIN `".AUTH_DATABASE."`.`user_access` AS c
							ON c.`user_id` = b.`id` AND c.`app_id`=".$db->qstr(AUTH_APP_ID)."
							JOIN `group_members` AS d
							ON b.`id` = d.`proxy_id`
							AND c.`account_active`='true'
							AND (c.`access_starts`='0' OR c.`access_starts`<=".$db->qstr(time()).")
							AND (c.`access_expires`='0' OR c.`access_expires`>=".$db->qstr(time()).") ";
				foreach ($assessments as $key => $assessment) {
					$query .= " LEFT JOIN `".DATABASE_NAME."`.`assessment_grades` AS assessment_$key ON b.`id` = assessment_$key.`proxy_id` AND assessment_$key.`assessment_id` = ".$db->qstr($assessment["assessment_id"])."\n";
				}
				
				if (isset($COHORT)) {
					$query .= " WHERE c.`group` = 'student' AND d.`group_id` = ".$db->qstr($COHORT);
				} else {					
					$query .= " WHERE c.`group` = 'student' AND d.`group_id` IN (".implode(",", $groups).")";
				}
				$students = $db->GetAll($query);
				
				ob_start();
				echo "\"Number\",\"Fullname\"";
				foreach ($assessments as $key => $assessment) {
					echo ",\"".trim($assessment["name"])." (".trim($assessment["type"]).")\"";
				}
				if (defined("GRADEBOOK_DISPLAY_WEIGHTED_TOTAL") && GRADEBOOK_DISPLAY_WEIGHTED_TOTAL) {
				echo ",\"Weighted Total\"";
				}
				echo "\n";
				if (count($students) >= 1) {
					foreach ($students as $student) {
						$proxy_id	= $student["proxy_id"];

						$cols = array();
						$cols[]	= trim((($student["group"] == "student") ? $student["number"] : 0));
						$cols[]	= trim($student["fullname"]);
						if (defined("GRADEBOOK_DISPLAY_WEIGHTED_TOTAL") && GRADEBOOK_DISPLAY_WEIGHTED_TOTAL) {
							$weighted_total = 0;
							$weighted_total_max = 0;
						}
						foreach ($assessments as $key => $assessment) {
							$cols[] = trim(format_retrieved_grade($student[$key."_grade_value"], $assessment) . assessment_suffix($assessment));
							if (defined("GRADEBOOK_DISPLAY_WEIGHTED_TOTAL") && GRADEBOOK_DISPLAY_WEIGHTED_TOTAL) {
								$weighted_total_max += $assessment["grade_weighting"];
								$weighted_total += (($assessment["handler"] == "Numeric" ? ($student[$key."_grade_value"] / $assessment["numeric_grade_points_total"]) : (($assessment["handler"] == "Percentage" ? ((float)$student[$key."_grade_value"] / 100.0) : $student[$key."_grade_value"])))) * $assessment["grade_weighting"];
							}
						}
						if (defined("GRADEBOOK_DISPLAY_WEIGHTED_TOTAL") && GRADEBOOK_DISPLAY_WEIGHTED_TOTAL) {
							$cols[] = number_format($weighted_total, 2)." / ".$weighted_total_max;
						}
						echo "\"".implode("\",\"", $cols)."\"", "\n";
					}
				}
				$contents = ob_get_contents();

				ob_clear_open_buffers();
				
				if (isset($COHORT)) {
					$filename = date("Y-m-d")."_".useable_filename($course_details["course_code"]."_".clean_input(groups_get_name($COHORT), array("trim", "file"))."_gradebook").".csv\"";
				} else {
					$filename = date("Y-m-d")."_".useable_filename($course_details["course_code"]."_gradebook").".csv\"";
				}
				
				header("Pragma: public");
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Type: application/force-download");
				header("Content-Type: application/octet-stream");
				header("Content-Type: text/csv");
				header("Content-Disposition: attachment; filename=\"".$filename);
				header("Content-Length: ".strlen($contents));
				header("Content-Transfer-Encoding: binary\n");

				echo $contents;
				exit;
			} else {
				$ERROR++;
				$ERRORSTR[] = "In order to view an assessment's grades you must provide some valid assessment identifiers.";

				echo display_error();

				application_log("notice", "Failed to provide a valid assessment identifiers when attempting to view an assessment's grades.");
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "You don't have permission to view this gradebook.";

			echo display_error();

			application_log("error", "User tried to view gradebook without permission.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to export a gradebook you must provide a valid course identifier.";

		echo display_error();

		application_log("notice", "Failed to provide course identifier when attempting to export an gradebook's grades.");
	}
}