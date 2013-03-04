<?php
/**
 * Online Course Resources [Pre-Clerkship]
 * @author Unit: Medical Education Technology Unit
 * @author Director: Dr. Benjamin Chen <bhc@post.queensu.ca>
 * @author Developer: Matt Simpson <simpson@post.queensu.ca>
 * @version 3.0
 * @copyright Copyright 2006 Queen's University, MEdTech Unit
 *
 * $Id: personnel.api.php 1140 2010-04-27 18:59:15Z simpson $
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

if ((isset($_SESSION["isAuthorized"])) && ((bool) $_SESSION["isAuthorized"])) {
	if (isset($_POST["fullname"]) && ($tmp_input = clean_input($_POST["fullname"], array("trim", "notags")))) {
		$fullname = $tmp_input;
	} elseif (isset($_GET["fullname"]) && ($tmp_input = clean_input($_GET["fullname"], array("trim", "notags")))) {
		$fullname = $tmp_input;
	} else {
		$fullname = "";
	}

	if (isset($_POST["type"]) && ($tmp_input = clean_input($_POST["type"], array("trim", "notags")))) {
		$type = $tmp_input;
	} elseif (isset($_GET["type"]) && ($tmp_input = clean_input($_GET["type"], array("trim", "notags")))) {
		$type = $tmp_input;
	} else {
		$type = "";
	}

	if ($fullname) {
		$query = "	SELECT a.`id` AS `proxy_id`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, a.`email`
					FROM `".AUTH_DATABASE."`.`user_data` AS a
					LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
					ON b.`user_id` = a.`id`
					WHERE CONCAT_WS(', ', a.`lastname`, a.`firstname`) LIKE ".$db->qstr("%".$fullname."%")."
					AND (b.`group` <> 'guest')";
		switch ($type) {
			case "facultyorstaff":
				$query .= "	AND (b.`group` = 'faculty' OR (b.`group` = 'resident' AND b.`role` = 'lecturer') OR b.`group` = 'staff' OR b.`group` = 'medtech')";
			break;
			case "staff":
				$query .= "	AND (b.`group` = 'staff' OR b.`group` = 'medtech')";
			break;
			case "faculty" :
			case "evalfaculty" :
				$query .= "	AND (b.`group` = 'faculty' OR (b.`group` = 'resident' AND b.`role` = 'lecturer'))";
			break;
			case "resident" :
			case "postgrad" :
				$query .= "	AND b.`group` = 'resident'";
			break;
			case "undergrad" :
			case "student" :
			case "clerk" :
				$query .= "	AND b.`group` = 'student' AND b.`role` >= '".(date("Y") - ((date("m") < 7) ? 2 : 1))."'";
			break;
			case "learners" :
				$query .= "	AND (b.`group` = 'resident' OR (b.`group` = 'student' AND b.`role` >= '".(date("Y") - ((date("m") < 7) ? 2 : 1))."'))";
			break;
			case "director" :
				$query .= "	AND b.`group` = 'faculty' AND (b.`role` = 'director' OR b.`role` = 'admin')";
			break;
			case "coordinator" :
				$query .= "	AND b.`group` = 'staff' AND b.`role` = 'admin'";
			break;
			case "evaluators" :
				$evaluator_ids_string = "";
				if (isset($_GET["id"]) && ($evaluation_id = clean_input($_GET["id"], "int"))) {
					require_once("Models/evaluation/Evaluation.class.php");
					$evaluators = Evaluation::getEvaluators($evaluation_id);
					if ($evaluators) {
						foreach ($evaluators as $evaluator) {
							$evaluator_ids_string .= ($evaluator_ids_string ? ", " : "").$db->qstr($evaluator["proxy_id"]);
						}
					}
				}
				$query .= " AND a.`id` IN (".$evaluator_ids_string.")";
			break;
		}
		$query .= "	AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
					AND b.`account_active` = 'true'
					AND (b.`access_starts`='0' OR b.`access_starts` <= ".$db->qstr(time()).")
					AND (b.`access_expires`='0' OR b.`access_expires` >= ".$db->qstr(time()).")
					GROUP BY a.`id`
					ORDER BY `fullname` ASC";
		echo "<ul>\n";
		$results = $db->GetAll($query);
		if ($results) {
			foreach($results as $result) {
				echo "\t<li id=\"".(int) $result["proxy_id"]."\">".html_encode($result["fullname"])."<span class=\"informal content-small\"><br />".html_encode($result["email"])."</span></li>\n";
			}
		} else {
			echo "\t<li id=\"0\"><span class=\"informal\">&quot;<strong>".html_encode($fullname)."&quot;</strong> was not found</span></li>";
		}
		echo "</ul>";
	}
} else {
	application_log("error", "Personnel API accessed without valid session_id.");
}