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
 * Entrada upgrade helper.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Brandon Thorn <bt37@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 * 
*/

@set_time_limit(0);
@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../../../core",
    dirname(__FILE__) . "/../../../core/includes",
    dirname(__FILE__) . "/../../../core/library",
    get_include_path(),
)));

require_once("config/config.inc.php");
require_once "Zend/Loader/Autoloader.php";
$loader = Zend_Loader_Autoloader::getInstance();
require_once("config/settings.inc.php");
require_once("Entrada/adodb/adodb.inc.php");
require_once("functions.inc.php");
require_once("dbconnection.inc.php");

if((!isset($_SERVER["argv"])) || (@count($_SERVER["argv"]) < 1)) {
	echo "<html>\n";
	echo "<head>\n";
	echo "	<title>Processing Error</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	echo "This file should be run by command line only:";
	echo "<div style=\"font-family: monospace\">/usr/bin/php -f ".__FILE__."</div>";
	echo "</body>\n";
	echo "</html>\n";
	exit;
}

echo "\n\n";

$grad_years_array = array();

$query = "SELECT * FROM `".AUTH_DATABASE."`.`organisations`";
if ($organisations = $db->GetAll($query)) {
	foreach ($organisations as $organisation) {
		$query = "SELECT a.`role` FROM `".AUTH_DATABASE."`.`user_access` AS a
					JOIN `".AUTH_DATABASE."`.`user_data` AS b
					ON a.`user_id` = b.`id`
					WHERE b.`organisation_id` = ".$db->qstr($organisation["organisation_id"])."
					AND a.`group` = 'student'
					GROUP BY a.`role`
					ORDER BY a.`role` ASC";
		if ($grad_years = $db->GetAll($query)) {
			foreach ($grad_years as $grad_year) {
				$group = array(	"group_name" => $organisation["organisation_title"].": Class of ".$grad_year["role"],
								"group_type" => "cohort",
								"updated_date" => time(),
								"updated_by" => 3499
							);
				$group_organisation = array(
											"organisation_id" => $organisation["organisation_id"],
											"updated_date" => time(),
											"updated_by" => 3499
											);
				$group_id = 0;
				if ($db->AutoExecute("groups", $group, "INSERT") && ($group_id = $db->Insert_Id()) && ($group_organisation["group_id"] = $group_id) && $db->AutoExecute("group_organisations", $group_organisation, "INSERT")) {
					$grad_years_array[$organisation["organisation_id"]."-".$grad_year["role"]] = array("grad_year" => $grad_year["role"], "cohort" => $group_id, "organisation_id" => $organisation["organisation_id"]);
					$query = "SELECT a.`id` AS `proxy_id` FROM `".AUTH_DATABASE."`.`user_data` AS a
								JOIN `".AUTH_DATABASE."`.`user_access` AS b
								ON a.`id` = b.`user_id`
								WHERE b.`role` = ".$db->qstr($grad_year["role"])."
								AND b.`group` = 'student'
								AND a.`organisation_id` = ".$db->qstr($organisation["organisation_id"])."
								GROUP BY a.`id`";
					if ($students = $db->GetAll($query)) {
						foreach ($students as $student) {
							$group_member = array(	"proxy_id" => $student["proxy_id"],
													"group_id" => $group_id,
													"updated_date" => time(),
													"updated_by" => 3499
												);
							if (!$db->AutoExecute("group_members", $group_member, "INSERT")) {
								echo "Error adding proxy id [".$student["proxy_id"]."] to group [".$group_id."].\n";
							}
						}
					}
				}
				echo "Successfully added group: ".$group["group_name"]."\n";
			}
		}
	}
}

foreach ($grad_years_array as $grad_year_group) {
	$query = "SELECT b.* FROM `events` AS a
				JOIN `event_audience` AS b
				ON a.`event_id` = b.`event_id`
				JOIN `courses` AS c
				ON a.`course_id` = c.`course_id`
				WHERE c.`organisation_id` = ".$db->qstr($grad_year_group["organisation_id"])."
				AND b.`audience_type` = 'grad_year'
				AND b.`audience_value` = ".$db->qstr($grad_year_group["grad_year"]);
	$event_audiences = $db->GetAll($query);
	if ($event_audiences) {
		foreach ($event_audiences as $event_audience) {
			$event_audience["audience_value"] = $grad_year_group["cohort"];
			$event_audience["audience_type"] = "cohort";
			if (!$db->AutoExecute("event_audience", $event_audience, "UPDATE", "`eaudience_id` = ".$db->qstr($event_audience["eaudience_id"]))) {
				echo "Unable to update event audience. Database said: ".$db->ErrorMsg();
				echo "\nExiting now...\n";
				exit;
			}
		}
	} elseif ($error_msg = $db->ErrorMsg()) {
		echo "\nUnable to fetch event audiences from the system. Database said: ".$error_msg;
	}
	
	$query = "SELECT b.* FROM `tasks` AS a
				JOIN `task_recipients` AS b
				ON a.`task_id` = b.`task_id`
				WHERE a.`organisation_id` = ".$db->qstr($grad_year_group["organisation_id"])."
				AND b.`recipient_type` = 'grad_year'
				AND b.`recipient_id` = ".$db->qstr($grad_year_group["grad_year"]);
	$task_recipients = $db->GetAll($query);
	if ($task_recipients) {
		foreach ($task_recipients as $task_recipient) {
			$task_recipient["recipient_id"] = $grad_year_group["cohort"];
			$task_recipient["recipient_type"] = "cohort";
			if (!$db->AutoExecute("task_recipients", $task_recipient, "UPDATE", "`task_id` = ".$db->qstr($task_recipient["task_id"])." AND `recipient_id` = ".$db->qstr($grad_year_group["grad_year"]))) {
				echo "Unable to update task recipient. Database said: ".$db->ErrorMsg();
				echo "\nExiting now...\n";
				exit;
			}
		}
	} elseif ($error_msg = $db->ErrorMsg()) {
		echo "\nUnable to fetch tasks from the system. Database said: ".$error_msg;
	}
	
	$query = "SELECT a.* FROM `evaluations` AS a
				JOIN `evaluation_targets` AS b
				ON a.`evaluation_id` = b.`evaluation_id`
				AND b.`target_id` = 1
				JOIN `courses` AS c
				ON b.`target_id` = c.`course_id`
				WHERE c.`organisation_id` = ".$db->qstr($grad_year_group["organisation_id"])."
				GROUP BY b.`evaluation_id`, c.`organisation_id`
				UNION
				SELECT a.* FROM `evaluations` AS a
				JOIN `evaluation_targets` AS b
				ON a.`evaluation_id` = b.`evaluation_id`
				AND b.`target_id` = 2
				JOIN `".AUTH_DATABASE."`.`user_data` AS c
				ON b.`target_id` = c.`id`
				WHERE c.`organisation_id` = ".$db->qstr($grad_year_group["organisation_id"])."
				GROUP BY b.`evaluation_id`, c.`organisation_id`";
	
	if ($evaluations = $db->GetAll($query)) {
		foreach ($evaluations as $evaluation) {
			$query = "SELECT b.* FROM `evaluations` AS a
						JOIN `evaluation_evaluators` AS b
						ON a.`evaluation_id` = b.`evaluation_id`
						WHERE a.`evaluation_id` = ".$db->qstr($evaluation["evaluation_id"])."
						AND b.`evaluator_type` = 'grad_year'
						AND b.`evaluator_value` = ".$db->qstr($grad_year_group["grad_year"]);
			if ($evaluators = $db->GetAll($query)) {
				foreach ($evaluators as $evaluator) {
					$evaluator["evaluator_value"] = $grad_year_group["cohort"];
					$evaluator["evaluator_type"] = "cohort";
					if (!$db->AutoExecute("evaluation_evaluators", $evaluator, "UPDATE", "`eevaluator_id` = ".$db->qstr($evaluator["eevaluator_id"]))) {
						echo "Unable to update evaluation evaluator. Database said: ".$db->ErrorMsg();
						echo "\nExiting now...\n";
						exit;
					}
				}
			}
		}
	} elseif ($error_msg = $db->ErrorMsg()) {
		echo "\nUnable to fetch evaluators from the system. Database said: ".$error_msg;
	}
	
	$query = "SELECT a.* FROM `assessments` AS a
				JOIN `courses` AS b
				ON a.`course_id` = b.`course_id`
				WHERE b.`organisation_id` = ".$db->qstr($grad_year_group["organisation_id"])."
				AND a.`cohort` = ".$db->qstr($grad_year_group["grad_year"]);
	$assessments = $db->GetAll($query);
	if ($assessments) {
		foreach ($assessments as $assessment) {
			$assessment["cohort"] = $grad_year_group["cohort"];
			if (!$db->AutoExecute("assessments", $assessment, "UPDATE", "`assessment_id` = ".$db->qstr($assessment["assessment_id"]))) {
				echo "Unable to update assessment. Database said: ".$db->ErrorMsg();
				echo "\nExiting now...\n";
				exit;
			}
		}
	} elseif ($error_msg = $db->ErrorMsg()) {
		echo "\nUnable to fetch assessments from the system. Database said: ".$error_msg;
	}
	
	$query = "SELECT * FROM `poll_questions`
				WHERE `poll_target` = ".$db->qstr($grad_year_group["grad_year"]);
	$polls = $db->GetAll($query);
	if ($polls) {
		foreach ($polls as $poll) {
			$poll["poll_target"] = $grad_year_group["cohort"];
			$poll["poll_target_type"] = "cohort";
			if (!$db->AutoExecute("poll_questions", $poll, "UPDATE", "`poll_id` = ".$db->qstr($poll["poll_id"]))) {
				echo "Unable to update poll_question. Database said: ".$db->ErrorMsg();
				echo "\nExiting now...\n";
				exit;
			}
		}
	} elseif ($error_msg = $db->ErrorMsg()) {
		echo "\nUnable to fetch polls from the system. Database said: ".$error_msg;
	}
}


echo "\n\nCompleted migration!\n\n";
