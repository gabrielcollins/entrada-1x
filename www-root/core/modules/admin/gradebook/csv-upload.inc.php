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
	if (isset($_GET["assessment_id"]) && $tmp_input = (int)$_GET["assessment_id"]) {
		$ASSESSMENT_ID = $tmp_input;
	}

	if ($ASSESSMENT_ID) {
		   	$url = ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("step" => false, "section" => "grade", "assessment_id" => $ASSESSMENT_ID));			
		   	if ($_FILES["file"]["error"] > 0) {
				add_error("Error occurred while uploading file.");
				//$_FILES["file"]["error"]
			} elseif(!in_array($_FILES["file"]["type"], array("text/csv", "application/vnd.ms-excel","text/comma-separated-values","application/csv", "application/excel", "application/vnd.ms-excel", "application/vnd.msexcel","application/octet-stream"))) {
				add_error("Invalid <strong>file type</strong> uploaded. Must be a CSV file in the proper format.");
			} else {

				$lines = file($_FILES["file"]["tmp_name"]);
				$PROCESSED["assessment_id"] = $ASSESSMENT_ID;

				$assessment = $db->GetRow("SELECT `cohort`,`name` FROM `assessments` WHERE `assessment_id` = ".$db->qstr($ASSESSMENT_ID));
				$GROUP = $assessment["cohort"];
				$ASSESSMENT_NAME = $assessment["name"];
				if ($GROUP) {
					foreach($lines as $key=>$line){
						$member_found = false;
						$line_data = explode(",",$line);			
						if (is_int($stud_num = (int)$line_data[0]) && $PROCESSED["value"] = clean_input($line_data[1],array("trim","notags"))) {
							if(is_numeric($PROCESSED["value"])){


								$query = "SELECT * FROM `".AUTH_DATABASE."`.`user_data` WHERE `number` = ".$db->qstr($stud_num);

								$users = $db->GetAll($query);
								if ($users) {
									foreach ($users as $user) {
										$query = "SELECT * FROM `group_members` WHERE `group_id` = ".$db->qstr($GROUP)." AND `proxy_id` = ".$db->qstr($user["id"])." AND `member_active` = '1'";
										$member = $db->GetRow($query);
										if ($member) {
											$PROCESSED["proxy_id"] = $member["proxy_id"];
											$member_found = true;
											break;
										}
									}
									if ($member_found) {
										//$db->AutoExecute("assessment_grades",$PROCESSED,"INSERT");
										$query = "SELECT * FROM `assessment_grades` WHERE `assessment_id` = ".$db->qstr($ASSESSMENT_ID)." AND `proxy_id` = ".$db->qstr($member["proxy_id"]);
										$grade = $db->GetRow($query);
										
										if ($PROCESSED["value"] < $assessment["grade_threshold"]) {
											$PROCESSED["threshold_notified"] = 0;
										}
										
										if ($grade) {
											$db->AutoExecute("assessment_grades",$PROCESSED,"UPDATE","`grade_id`=".$db->qstr($grade["grade_id"]));
										} else {
											$db->AutoExecute("assessment_grades",$PROCESSED,"INSERT");
										}

									} else {
										add_error("Student on line ".$key." is not registered in the class.");
									}
								} else {
									add_error("Student on line ".$key." is not registered in the system.");
								}
							} else {
								add_error("Invalid data on line ".$key.":".$line.".");
							}
						} else {
							add_error("Invalid data on line ".$key.":".$line.".");
						}
					}

					add_success("Successfully updated <strong>Gradebook</strong>. You will now be redirected to the <strong>Grade Assessment</strong> page for <strong>".$ASSESSMENT_NAME. "</strong>. This will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue now.");
					$COURSE_ID = (int)$_GET["id"];
					if (isset($_GET["assignment_id"]) && $ASSIGNMENT_ID = (int)$_GET["assignment_id"]) {
						$url = ENTRADA_URL."/admin/gradebook/assignments?section=grade&id=".$COURSE_ID."&assignment_id=".$ASSIGNMENT_ID;
					} else {
						$url = ENTRADA_URL."/admin/gradebook/assessments?section=grade&id=".$COURSE_ID."&assessment_id=".$ASSESSMENT_ID;
					}
				} else {
					add_error("Invalid <strong>Assessment ID</strong> provided. You will now be redirected to the <strong>Gradebook Index</strong>. This will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue now.");
				}
			}

		} else {
			$url = ENTRADA_URL."/admin/gradebook";
			add_error("<strong>Assessment ID</strong> is required. You will now be redirected to the <strong>Gradebook Index</strong>. This will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue now.");
	}
	if($ERROR){
		echo display_error();
	}
	if($SUCCESS){
		echo display_success();
	}
	$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";
}