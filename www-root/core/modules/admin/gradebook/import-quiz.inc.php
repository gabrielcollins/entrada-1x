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
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
		
	$assessment_id = (int) $_POST["assessment_id"];
	$course_id = (int) $_POST["course_id"];
	
	$url = ENTRADA_URL."/admin/gradebook/assessments?section=grade&id=".$course_id."&assessment_id=".$assessment_id;
	
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("section" => "grade", "id" => $COURSE_ID, "step" => false)), "title" => "Grading Assessment");
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("section" => "import-quiz", "id" => $COURSE_ID, "step" => false)), "title" => "Importing Quiz Results");
	
	echo "<h1>Import Quiz results into Gradebook Assessment</h1>";
		
	/*
	 *  fetch the quiz attached to the assessment
	 */
	$query = "	SELECT * 
				FROM `attached_quizzes` 
				WHERE `content_type` = 'assessment' 
				AND `content_id` = ".$db->qstr($assessment_id);
	
	if ($aquiz = $db->GetRow($query)) {
		
		echo "<h2>Importing ".$aquiz["quiz_title"]."</h2>";
		
		/*
		 *  fetch the proxy_ids for the assessment
		 */
		$query = "	SELECT a.`assessment_id`, a.`name`, GROUP_CONCAT(b.`proxy_id` SEPARATOR ',') AS `group_members`, a.`grade_threshold`
					FROM `assessments` AS a
					LEFT JOIN `group_members` AS b
					ON a.`cohort` = b.`group_id`
					WHERE `assessment_id` = ".$db->qstr($assessment_id);	
		
		if ($assessment = $db->GetRow($query)) {
			
			$audience_members = explode(",", $assessment["group_members"]);

			/*
			 *  fetch the existing assessment grades
			 */
			$query = "	SELECT `proxy_id`, `grade_id`, `value` 
						FROM `assessment_grades` 
						WHERE `assessment_id` = ".$db->qstr($assessment_id)." 
						AND `proxy_id` IN (".$assessment["group_members"].")";
			$grades = $db->GetAssoc($query);
			
			/*
			 *  fetch the attached quiz responses
			 */
			$query = "	SELECT a.`proxy_id`, a.* 
						FROM `quiz_progress` AS a
						WHERE a.`quiz_id` = ".$db->qstr($aquiz["quiz_id"])."
						AND a.`progress_value` = 'complete'
						AND a.`proxy_id` IN (".$assessment["group_members"].")
						ORDER BY a.`updated_date` DESC";
			$responses = $db->GetAssoc($query);
			
			if ($responses) {
				
				foreach ($audience_members as $member) {

					if ($responses[$member]) {
						
						$PROCESSED["assessment_id"] = (int) $assessment_id;
						$PROCESSED["proxy_id"]		= (int) $member;
						$PROCESSED["value"]			= ($responses[$member]["quiz_score"] / $responses[$member]["quiz_value"]) * 100;

						if ($PROCESSED["value"] < $assessment["grade_threshold"]) {
							$PROCESSED["threshold_notified"] = 0;
						}

						if ($grades[$member]) {
							$PROCESSED["grade_id"] = $grades[$member]["grade_id"];
							$db->AutoExecute("assessment_grades",$PROCESSED,"UPDATE","`grade_id`=".$db->qstr($PROCESSED["grade_id"]));
						} else {
							$db->AutoExecute("assessment_grades",$PROCESSED,"INSERT");
						}

						unset($PROCESSED);
					
					}
					
				}
				
				if (!$ERROR) {
					add_success("Successfully imported results from <strong>".$aquiz["quiz_title"]."</strong> into <strong>".$assessment["name"]."</strong>.<br /><br />You will now be redirected to the <strong>Grade Assessment</strong> page for <strong>".$assessment["name"]."</strong>. This will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue now.");
				}
				
			} else {
				add_error("No students have completed the quiz <strong>".$aquiz["quiz_title"]."</strong>.<br /><br />You will now be redirected to the <strong>Grade Assessment</strong> page for <strong>".$assessment["name"]."</strong>. This will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue now.");
			}
			
		}
		
	} else {
		add_error("The assessment ".$assessment["name"]." does not have a quiz attached, results can not be imported.<br /><br />You will now be redirected to the <strong>Grade Assessment</strong> page for <strong>".$assessment["name"]."</strong>. This will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue now.");
	}
	
	if ($ERROR) {
		echo display_error();
	}
	if ($NOTICE) {
		echo display_notice();
	}
	if ($SUCCESS) {
		echo display_success();
	}
	
	$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";
	
}