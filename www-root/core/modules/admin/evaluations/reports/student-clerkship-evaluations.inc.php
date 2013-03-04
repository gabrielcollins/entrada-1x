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
 * This file is used to add events to the entrada.events table.
 *
 * @author Organisation: Queen's University
 * @author Unit: Undergraduate Medical Education
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 *
*/
if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVALUATIONS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("evaluation", "read", false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	switch($STEP) {
		case "3" :
			$ITEM_IDS		= array();
			$CATEGORY_IDS	= array();
			$REGION_IDS		= array();
			$TEACHER_IDS	= array();
	
			$FORM_ID		= (int) trim($_GET["form_id"]);
	
			if(!$FORM_ID) {
				$ERROR++;
				$ERRORSTR[] = "There was no form identifier supplied for this step. Please ensure you are using the interface.";
	
				system_log_data("error", "The generate reporting page (step 3) was accessed without a valid form_id.");
			}
	
			if(@is_array($_GET["category_ids"])) {
				foreach($_GET["category_ids"] as $item_id => $category_id) {
					$item_id		= (int) trim($item_id);
					$category_id	= (int) trim($category_id);
	
					if($item_id && $category_id) {
						$ITEM_IDS[]				= $item_id;
						$CATEGORY_IDS[$item_id]	= $category_id;
					}
				}
			}
	
			if(@is_array($_GET["region_ids"])) {
				foreach($_GET["region_ids"] as $region_id) {
					$region_id = (int) trim($region_id);
					if($region_id) {
						$REGION_IDS[] = $region_id;
					}
				}
			}
			if((!@count($REGION_IDS)) || ($_GET["region_type"] == "all")) {
				// Get All Regions?
			}
	
			if(@is_array($_GET["teacher_ids"]) && ($_SESSION["details"]["group"] != "faculty")) {
				foreach($_GET["teacher_ids"] as $teacher_id) {
					$teacher_id = (int) trim($teacher_id);
					if($teacher_id) {
						$TEACHER_IDS[] = $teacher_id;
					}
				}
			} elseif ($_SESSION["details"]["group"] == "faculty") {
				$TEACHER_IDS[] = $ENTRADA_USER->getID();
			}
			if((!@count($TEACHER_IDS)) || ($_GET["teacher_type"] == "all")) {
				// Get All Teachers?
			}
	
			if(@count($CATEGORY_IDS) < 1) {
				$ERROR++;
				$ERRORSTR[] = "You must select a category for every evaluation in your list.";
	
				system_log_data("error", "The generate reporting page (step 3) was accessed without any valid item_ids and/or category_ids.");
			}
	
			if($ERROR) {
				$STEP = 2;
			}
		break;
		case "2" :
			$ITEM_IDS = array();
	
			if(isset($_GET["category_id"]) && ($category_id = trim(preg_replace("/[^\d]/", "", $_GET["category_id"]))) && ($type = preg_replace("/[\d]/", "", $_GET["category_id"]))) {
				$type = ($type == "r" ? "Rotation" : "Teacher");
				$query = "SELECT `item_id` FROM `".CLERKSHIP_DATABASE."`.`evaluations`
							WHERE `category_id` IN (
								SELECT a.`category_id` FROM `".CLERKSHIP_DATABASE."`.`categories` AS a
								WHERE a.`category_parent` = ".$db->qstr($category_id)."
								UNION
								SELECT a.`category_id` FROM `".CLERKSHIP_DATABASE."`.`categories` AS a
								JOIN `".CLERKSHIP_DATABASE."`.`categories` AS b
								ON a.`category_parent` = b.`category_id`
								WHERE b.`category_parent` = ".$db->qstr($category_id)."
							)
							AND (`item_title` LIKE '%".$type." Evaluation'".($type == "Teacher" ? "
							OR `item_title` LIKE '%Preceptor Evaluation')" : ")");
				if ($results = $db->GetAll($query)) {
					foreach ($results as $result) {
						$ITEM_IDS[] = $result["item_id"];
					}
				}
			}
	
			if(@count($ITEM_IDS) < 1) {
				$ERROR++;
				$ERRORSTR[] = "You must select at least one evaluation in order to generate a report.";
	
				system_log_data("error", "The generate reporting page was accessed without any valid item_ids.");
			}
	
			if($ERROR) {
				$STEP = 1;
			}
		break;
		case "1" :
		default :
			// No error checking for step 1.
		break;
	}
	
	// Step Display
	switch($STEP) {
		case "3" :
			switch($_GET["report_style"]) {
				case "summary" :
					$report_timer_start		= getmicrotime();
					$headings				= array();
					$reports				= array();
	
					$category_titles		= array();
					$report_results			= array();
	
					$total_headings			= 0;
	
					$report_title			= "";
	
					// Setup the basis for the questions asked and possible answers.
					$query		= "SELECT `question_id`, `question_text` 
									FROM `".CLERKSHIP_DATABASE."`.`eval_questions` 
									WHERE `form_id` = ".$db->qstr($FORM_ID);
					$results	= $db->GetAll($query);
					$qnum		= 1;
					if($results) {
						foreach($results as $result) {
							$question_id						= (int) $result["question_id"];
							$reports[$question_id]["qnum"]		= $qnum;
							$reports[$question_id]["question"]	= $result["question_text"];
	
							$query		= "SELECT `answer_id`, `answer_label`, `answer_value` 
											FROM `".CLERKSHIP_DATABASE."`.`eval_answers` 
											WHERE `question_id` = ".$db->qstr($question_id);
							$answers	= $db->GetAll($query);
							if($answers) {
								foreach($answers as $answer) {
									if(($answer["answer_value"] != "0") && (!@in_array($answer["answer_label"], $headings))) {
										$headings[$answer["answer_value"]] = $answer["answer_label"];
									}
	
									$reports[$question_id]["answers"][$answer["answer_value"]]["id"]		= $answer["answer_id"];
									$reports[$question_id]["answers"][$answer["answer_value"]]["label"]		= $answer["answer_label"];
									$reports[$question_id]["answers"][$answer["answer_value"]]["result"]	= 0;
								}
							}
							$qnum++;
						}
					}
					@ksort($headings);
					$total_headings		= @count($headings);
	
					// Get the title of this form for use as the $report_title.
					$query				= "SELECT `form_title` 
											FROM `".CLERKSHIP_DATABASE."`.`eval_forms` 
											WHERE `form_id` = ".$db->qstr($FORM_ID);
					$result				= $db->GetRow($query);
					if($result) {
						$report_title	= $result["form_title"];
					} else {
						$report_title	= "Unknown Report";
					}
	
					foreach($CATEGORY_IDS as $item_id => $category_id) {
						$query	= "SELECT * FROM `".CLERKSHIP_DATABASE."`.`categories` 
									WHERE `category_parent` = ".$db->qstr($category_id);
						$result	= $db->GetRow($query);
						if($result) {
							clerkship_generate_included_categories($category_id, 0);
						} else {
							$category_name = clerkship_categories_name($category_id);
	
							$report_results[$category_name]["indent"]			= 0;
							$report_results[$category_name]["category_ids"][]	= (int) $category_id;
						}
						$category_titles[]	= clerkship_categories_title($category_id);
					}
	
					echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
					echo "<tr>\n";
					echo "	<td style=\"width: 55%; vertical-align: top\">\n";
					echo "		<div class=\"content-heading\">".$report_title."</div>\n";
					echo "		<div class=\"content-small\" style=\"padding-left: 20px; margin-bottom: 15px\">\n";
					echo "			<span style=\"font-weight: bold\">Summating results using:</span><br />\n";
					echo 			implode("<br />", $category_titles)."<br /><br />\n";
					echo "			<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
					echo "			<tr>\n";
					echo "				<td class=\"small-grey\" style=\"font-weight: bold; white-space: nowrap\">Compiled on:</td>\n";
					echo "				<td class=\"small-grey\" style=\"padding-left: 5px; white-space: nowrap\">".date("r", time())."</td>\n";
					echo "			</tr>\n";
					echo "			<tr>\n";
					echo "				<td class=\"small-grey\" style=\"font-weight: bold; white-space: nowrap\">Generated by:</td>\n";
					echo "				<td class=\"small-grey\" style=\"padding-left: 5px; white-space: nowrap\">MEdTech Unit [p: ".$ENTRADA_USER->getID()."]</td>\n";
					echo "			</tr>\n";
					echo "			</table>\n";
					echo "		</div>\n";
					echo "	</td>\n";
					echo "	<td style=\"width: 30%; vertical-align: top\">&nbsp;</td>\n";
					echo "	<td style=\"width: 15%; vertical-align: top; text-align: right; padding-top: 5px; padding-right: 5px\" rowspan=\"2\">\n";
					echo "		<img src=\"".ENTRADA_URL."/images/queens_logo.gif\" width=\"137\" height=\"95\" alt=\"Queen's University Logo\" title=\"Queen's University\" />\n";
					echo "	</td>\n";
					echo "</tr>\n";
					echo "<tr>\n";
					echo "	<td style=\"vertical-align: top\">\n";
								echo "<div class=\"content-subheading\">Questions Asked:</div>\n";
								echo "<ol style=\"margin-top: 5px; padding-left: 20px\">\n";
								foreach($reports as $key => $value) {
									echo "<li>".$value["question"]."</li>\n";
								}
								echo "</ol>\n";
					echo "	</td>\n";
					echo "	<td style=\"vertical-align: top; padding-left: 20px\" colspan=\"2\">\n";
					echo "		<div class=\"content-subheading\" style=\"margin-bottom: 5px\">Answer Scale:</div>\n";
					echo "		1 = Strongly Disagree<br />\n";
					echo "		2 = Disagree<br />\n";
					echo "		3 = Neutral<br />\n";
					echo "		4 = Agree<br />\n";
					echo "		5 = Strongly Agree<br />\n";
					echo "	</td>\n";
					echo "</tr>\n";
					echo "</table>\n";
					echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\" border=\"0\">\n";
					echo "<tr>\n";
					echo "	<td style=\"border-bottom: 1px #CCCCCC dotted; border-right: 1px #CCCCCC dotted\">&nbsp;</td>\n";
					echo "	<td class=\"content-subheading\" style=\"font-family: serif; font-size: 18px; width: 65px; border-bottom: 1px #CCCCCC dotted; border-right: 1px #CCCCCC dotted; text-align: center; font-style: oblique\">n</td>\n";
							foreach($reports as $key => $value) {
								echo "<td class=\"content-subheading\" style=\"width: 65px; border-bottom: 1px #CCCCCC dotted; border-right: 1px #CCCCCC dotted; text-align: center\">Q".$value["qnum"]."</td>\n";
							}
					echo "</tr>\n";
	
					foreach($report_results as $category_name => $category_info) {
						$sub_category_ids	= array();
						$row_result			= array();
						$row_reports		= array();
	
						foreach($category_info["category_ids"] as $category_id) {
							$sub_category_ids[] = (int) $category_id;
							clerkship_categories_inarray($category_id);
						}
	
						// Get the results of all completed evaluations from this evaluation for the specified categories.
						$query	= "SELECT a.`completed_id`
									FROM `".CLERKSHIP_DATABASE."`.`eval_completed` AS a
									LEFT JOIN `".CLERKSHIP_DATABASE."`.`notifications` AS b
									ON b.`notification_id` = a.`notification_id`
									LEFT JOIN `".CLERKSHIP_DATABASE."`.`events` AS c
									ON c.`event_id` = b.`event_id`
									LEFT JOIN `".CLERKSHIP_DATABASE."`.`eval_approved` AS d
									ON d.`notification_id` = a.`notification_id`
									WHERE d.`notification_id` IS NOT NULL 
									AND b.`event_id` <> '0' 
									AND ".((@count($REGION_IDS) > 0) ? "c.`region_id` IN (".implode(", ", $REGION_IDS).") AND " : "").((@count($TEACHER_IDS) > 0) ? "a.`instructor_id` IN (".implode(", ", $TEACHER_IDS).") AND " : "")."b.`item_id` IN (".implode(", ", $ITEM_IDS).") AND b.`category_id` IN (".implode(", ", $sub_category_ids).")";
						$results	= $db->GetAll($query);
						if($results) {
							foreach($results as $result) {
								$query	= "SELECT a.`result_value`, b.`answer_id`, b.`answer_value`, c.`question_id`
											FROM `".CLERKSHIP_DATABASE."`.`eval_results` AS a
											LEFT JOIN `".CLERKSHIP_DATABASE."`.`eval_answers` AS b
											ON b.`answer_id` = a.`answer_id`
											LEFT JOIN `".CLERKSHIP_DATABASE."`.`eval_questions` AS c
											ON c.`question_id` = b.`question_id`
											LEFT JOIN `".CLERKSHIP_DATABASE."`.`eval_approved` AS d
											ON d.`completed_id` = a.`completed_id`
											WHERE d.`completed_id` IS NOT NULL 
											AND a.`completed_id` = ".$db->qstr($result["completed_id"]);
	
								$answers	= $db->GetAll($query);
								if($answers) {
									foreach($answers as $answer) {
										if($answer["answer_value"] > 0) {
											$reports[$answer["question_id"]]["answers"][$answer["answer_value"]]["result"]++;
											if (!isset($row_reports[$answer["question_id"]])) {
												$row_reports[$answer["question_id"]] = array();
											}
											if (!isset($row_reports[$answer["question_id"]][$answer["answer_value"]])) {
												$row_reports[$answer["question_id"]][$answer["answer_value"]] = 0;
											}
											$row_reports[$answer["question_id"]][$answer["answer_value"]]++;
										}
									}
								}
							}
	
							foreach($row_reports as $question_id => $row_report) {
	
								$total_people	= 0;
								$total_answer	= 0;
	
								for($i = 1; $i <= $total_headings; $i++) {
									$total_people += (int) (isset($row_report[$i]) ? $row_report[$i] : 0);
									$total_answer += (int) ($i * (isset($row_report[$i]) ? $row_report[$i] : 0));
								}
	
								$mean = number_format((($total_people > 0) ? round(($total_answer / $total_people), 1) : 0), 1);
								$row_result["n"]								= $total_people;
								$row_result["mean"][$reports[$question_id]["qnum"]]	= $mean;
							}
						}
						echo "<tr>\n";
						echo "	<td style=\"width: 20%; white-space: nowrap; border-left: 1px #CCCCCC solid; border-bottom: 1px #666666 solid; border-right: 1px #CCCCCC solid\"><span style=\"font-family: monospace\">".str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $category_info["indent"])."</span><img src=\"".ENTRADA_URL."/images/arrow-right.gif\" width=\"5\" height=\"9\" border=\"0\" alt=\"View\" style=\"padding-right: 5px\" />".$category_name."</td>\n";
						echo "	<td style=\"text-align: center; border-bottom: 1px #666666 solid; border-right: 1px #CCCCCC solid\">".(!isset($row_result["n"]) || ($row_result["n"] == 0) ? "&nbsp;" : $row_result["n"])."</td>\n";
							for($col = 1; $col <= @count($reports); $col++) {
								$mean = (int) (isset($row_result["mean"][$col]) ? $row_result["mean"][$col] : 0);
								echo "<td style=\"text-align: center; border-bottom: 1px #666666 solid; border-right: 1px #CCCCCC solid\">".(!isset($row_result["n"]) || ($row_result["n"] < 5) ? "&nbsp;" : $row_result["mean"][$col])."</td>\n";
							}
						echo "</tr>\n";
					}
					echo "</table>";
					if(@count($REGION_IDS) > 0) {
						$query	= "SELECT `region_name` FROM `".CLERKSHIP_DATABASE."`.`regions` 
									WHERE `region_id` IN (".implode(", ", $REGION_IDS).") 
									ORDER BY `region_name` ASC";
						$results	= $db->GetAll($query);
						if($results) {
							$region_names = array();
							foreach($results as $result) {
								$region_names[] = html_encode($result["region_name"]);
							}
							echo "<br />\n";
							echo "<div class=\"content-small\">\n";
							echo "		<span style=\"font-weight: bold\">Regions included:</span> ".implode(", ", $region_names);
							echo "</div>\n";
						}
					}
					if(@count($TEACHER_IDS) > 0) {
						$query	= "SELECT CONCAT(`firstname`, ' ', `lastname`) AS `fullname` FROM `".AUTH_DATABASE."`.`user_data` 
									WHERE `id` IN (".implode(", ", $TEACHER_IDS).") 
									ORDER BY `fullname` ASC";
						$results	= $db->GetAll($query);
						if($results) {
							$teacher_names = array();
							foreach($results as $result) {
								$teacher_names[] = html_encode($result["fullname"]);
							}
							echo "<div class=\"content-small\" style=\"margin-top: 20px\">\n";
							echo "		<span style=\"font-weight: bold\">Teachers included:</span> ".implode(", ", $teacher_names);
							echo "</div>\n";
						}
					}
	
					$report_timer_end		= getmicrotime();
					system_log_data("report", "[success] ".$_SESSION["details"]["firstname"]." ".$_SESSION["details"]["lastname"]." (".$ENTRADA_USER->getID().") successfully generated summary report; runtime was ".number_format(($report_timer_end - $report_timer_start), 2)." seconds). ".((@count($region_names)) ? "Regions: ".implode(", ", $region_names) : "All Regions").". ".((@count($teacher_names)) ? "Teachers: ".implode(", ", $teacher_names) : "All Teachers").".");
				break;
				case "details" :
					$report_timer_start		= getmicrotime();
	
					// Check to see if they are allowed to generated unfiltered reports.
					if((@is_array($ADMINISTRATION[$_SESSION["details"]["group"]][$_SESSION["details"]["role"]]["options"][$MODULE])) && (@in_array("allow-unfiltered", $ADMINISTRATION[$_SESSION["details"]["group"]][$_SESSION["details"]["role"]]["options"][$MODULE])) && ($_GET["filter_comments"] != "1")) {
						$filter_array		= array();
					} else {
						$filter_array		= filtered_words();
					}
	
					$headings				= array();
					$reports				= array();
					$sub_category_ids		= array();
	
					$total_notifications	= 0;
					$total_responses		= 0;
					$total_headings			= 0;
	
					$report_title			= "";
					$category_titles		= array();
	
					$comment_ids			= array();
	
					$HEAD[] = "	<script type=\"text/javascript\">
									var showComments = 'false';
									function toggle_comments() {
										if(showComments == 'false') {
											if(!comment_ids.length) {
												document.getElementById(comment_ids).style.display = '';
											} else {
												for (i = 0; i < comment_ids.length; i++) {
													document.getElementById(comment_ids[i]).style.display = '';
												}
											}
											showComments = 'true';
											return;
										} else {
											if(!comment_ids.length) {
												document.getElementById(comment_ids).style.display = 'none';
											} else {
												for (i = 0; i < comment_ids.length; i++) {
													document.getElementById(comment_ids[i]).style.display = 'none';
												}
											}
											showComments = 'false';
											return;
										}
									}
								</script>";
					
					$sidebar_html	 = "	<div id=\"sidebar-panel-".$i."\">\n";
					$sidebar_html	.= "		<div id=\"sidebar-panel-".$i."-header\" class=\"accordionTabTitleBar\">Report Options</div>\n";
					$sidebar_html	.= "		<div id=\"sidebar-panel-".$i."-content\" class=\"accordionTabContentBox\">\n";
					$sidebar_html	.= "			<div class=\"accordionTabContentPadding\">\n";
					$sidebar_html	.= "				<form>\n";
					$sidebar_html	.= "				<input type=\"checkbox\" id=\"selectall\" name=\"selectall\" value=\"1\" onclick=\"toggle_comments('comment_ids')\" style=\"vertical-align: middle\" /> <label for=\"selectall\" style=\"font-size: 11px\">Display Comments</label>\n";
					$sidebar_html	.= "				</form>\n";
					$sidebar_html	.= "			</div>\n";
					$sidebar_html	.= "		</div>\n";
					$sidebar_html	.= "	</div>\n";
	
					new_sidebar_item("Report Options", $sidebar_html, "report-options", "open");
	
					// Setup the basis for the questions asked and possible answers.
					$query	= "SELECT `question_id`, `question_text` FROM `".CLERKSHIP_DATABASE."`.`eval_questions` 
								WHERE `form_id` = ".$db->qstr($FORM_ID);
					$results	= $db->GetAll($query);
					if($results) {
						foreach($results as $result) {
							$question_id					= (int) $result["question_id"];
							$reports[$question_id]["question"]	= $result["question_text"];
	
							$query	= "SELECT `answer_id`, `answer_label`, `answer_value` FROM `".CLERKSHIP_DATABASE."`.`eval_answers` 
										WHERE `question_id` = ".$db->qstr($question_id);
							$answers	= $db->GetAll($query);
							if($answers) {
								foreach($answers as $answer) {
									if(($answer["answer_value"] != "0") && (!@in_array($answer["answer_label"], $headings))) {
										$headings[$answer["answer_value"]] = $answer["answer_label"];
									}
	
									$reports[$question_id]["answers"][$answer["answer_value"]]["id"]			= $answer["answer_id"];
									$reports[$question_id]["answers"][$answer["answer_value"]]["label"]		= $answer["answer_label"];
									$reports[$question_id]["answers"][$answer["answer_value"]]["result"]		= 0;
									$reports[$question_id]["answers"][$answer["answer_value"]]["comments"]	= array();
								}
							}
						}
					}
					@ksort($headings);
					$total_headings		= @count($headings);
	
					// Get the title of this form for use as the $report_title.
					$query				= "SELECT `form_title` FROM `".CLERKSHIP_DATABASE."`.`eval_forms` 
											WHERE `form_id` = ".$db->qstr($FORM_ID);
					$result				= $db->GetRow($query);
					$report_title		= $result["form_title"];
	
					// Get all the sub_categories for all of the selected categories.
					foreach($CATEGORY_IDS as $item_id => $category_id) {
						$sub_category_ids[]	= $category_id; // Should this be here?
	
						categories_inarray($category_id);
						$category_titles[]	= clerkship_categories_title($category_id);
					}
	
					// Get the total number of notifications sent out from this evaluation for the specified categories.
					$query				= "SELECT COUNT(*) AS `total_notifications`
											FROM `".CLERKSHIP_DATABASE."`.`notifications` AS a
											LEFT JOIN `".CLERKSHIP_DATABASE."`.`events` AS b
											ON b.`event_id` = a.`event_id`
											LEFT JOIN `".CLERKSHIP_DATABASE."`.`eval_completed` AS c
											ON c.`notification_id` = a.`notification_id`
											WHERE a.`event_id` <> '0' 
											AND ".((@count($REGION_IDS) > 0) ? "b.`region_id` IN (".implode(", ", $REGION_IDS).") AND " : "").((@count($TEACHER_IDS) > 0) ? "c.`instructor_id` IN (".implode(", ", $TEACHER_IDS).") AND " : "")."a.`item_id` IN (".implode(", ", $ITEM_IDS).") AND a.`category_id` IN (".implode(", ", $sub_category_ids).")";
					$result				= $db->GetRow($query);
					$total_notifications	= (int) $result["total_notifications"];
	
					// Get the results of all completed evaluations from this evaluation for the specified categories.
					$query	= "SELECT a.`completed_id`
								FROM `".CLERKSHIP_DATABASE."`.`eval_completed` AS a
								LEFT JOIN `".CLERKSHIP_DATABASE."`.`notifications` AS b
								ON b.`notification_id` = a.`notification_id`
								LEFT JOIN `".CLERKSHIP_DATABASE."`.`events` AS c
								ON c.`event_id` = b.`event_id`
								LEFT JOIN `".CLERKSHIP_DATABASE."`.`eval_approved` AS d
								ON d.`completed_id` = a.`completed_id`
								WHERE d.`completed_id` IS NOT NULL 
								AND b.`event_id` <> '0' AND".((@count($REGION_IDS) > 0) ? " c.`region_id` IN (".implode(", ", $REGION_IDS).") AND" : "").((@count($TEACHER_IDS) > 0) ? " a.`instructor_id` IN (".implode(", ", $TEACHER_IDS).") AND" : "")." b.`item_id` IN (".implode(", ", $ITEM_IDS).") AND b.`category_id` IN (".implode(", ", $sub_category_ids).")";
					$results	= $db->GetAll($query);
					if($results) {
						$total_responses = @count($results);
	
						foreach($results as $result) {
							$query	= "SELECT a.`result_value`, b.`answer_id`, b.`answer_value`, c.`question_id`
										FROM `".CLERKSHIP_DATABASE."`.`eval_results` AS a
										LEFT JOIN `".CLERKSHIP_DATABASE."`.`eval_answers` AS b
										ON b.`answer_id` = a.`answer_id`
										LEFT JOIN `".CLERKSHIP_DATABASE."`.`eval_questions` AS c
										ON c.`question_id` = b.`question_id`
										LEFT JOIN `".CLERKSHIP_DATABASE."`.`eval_approved` AS d
										ON d.`completed_id` = a.`completed_id`
										WHERE d.`completed_id` IS NOT NULL 
										AND a.`completed_id` = ".$db->qstr($result["completed_id"]);
							$answers	= $db->GetAll($query);
							if($answers) {
								foreach($answers as $answer) {
									if($answer["answer_value"] == "0") {
										$reports[$answer["question_id"]]["answers"][$answer["answer_value"]]["comments"][] = preg_replace($filter_array, "'<span class=\"filtered-text\">'.str_repeat(\"*\", 8).'</span>'", $answer["result_value"]);
									} else {
										$reports[$answer["question_id"]]["answers"][$answer["answer_value"]]["result"]++;
									}
								}
							}
						}
					}
	
					echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
					echo "<tr>\n";
					echo "	<td style=\"width: 55%; vertical-align: top\">\n";
					echo "		<div class=\"content-heading\">".$report_title."</div>\n";
					echo "		<div class=\"content-small\" style=\"padding-left: 20px; margin-bottom: 15px\">\n";
					echo "			<span style=\"font-weight: bold\">Summating results using:</span><br />\n";
					echo 			implode("<br />", $category_titles)."<br /><br />\n";
					echo "			<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
					echo "			<tr>\n";
					echo "				<td class=\"small-grey\" style=\"font-weight: bold; white-space: nowrap\">Compiled on:</td>\n";
					echo "				<td class=\"small-grey\" style=\"padding-left: 5px; white-space: nowrap\">".date("r", time())."</td>\n";
					echo "			</tr>\n";
					echo "			<tr>\n";
					echo "				<td class=\"small-grey\" style=\"font-weight: bold; white-space: nowrap\">Generated by:</td>\n";
					echo "				<td class=\"small-grey\" style=\"padding-left: 5px; white-space: nowrap\">MEdTech Unit [p: ".$ENTRADA_USER->getID()."]</td>\n";
					echo "			</tr>\n";
					echo "			</table>\n";
					echo "		</div>\n";
					echo "	</td>\n";
					echo "	<td style=\"width: 30%; vertical-align: top\">&nbsp;</td>\n";
					echo "	<td style=\"width: 15%; vertical-align: top; text-align: right; padding-top: 5px; padding-right: 5px\" rowspan=\"2\">\n";
					echo "		<img src=\"".ENTRADA_URL."/images/queens_logo.gif\" width=\"137\" height=\"95\" alt=\"Queen's University Logo\" title=\"Queen's University\" />\n";
					echo "	</td>\n";
					echo "</tr>\n";
					echo "</table>\n";
	
					echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\" border=\"0\">\n";
					echo "<tr>\n";
					echo "	<td style=\"border-bottom: 1px #CCCCCC dotted; border-right: 1px #CCCCCC dotted\" class=\"content-date\">".$total_responses." response".(($total_responses == 1) ? "" : "s")." out of ".$total_notifications." notification".(($total_notifications == 1) ? "" : "s").".</td>\n";
							for($i = 1; $i <= $total_headings; $i++) {
								echo "<td class=\"content-subheading\" style=\"width: 65px; border-bottom: 1px #CCCCCC dotted; border-right: 1px #CCCCCC dotted; text-align: center\">".$headings[$i]."</td>\n";
							}
					echo "	<td style=\"color: #333333; background-color: #CCCCCC; font-weight: bold; white-space: nowrap; text-align: center\">Mean</td>\n";
					echo "<tr>\n";
	
					foreach($reports as $key => $report) {
						$total_people	= 0;
						$total_answer	= 0;
	
						$comment_id	= "report-comments-".$key;
						$comment_ids[]	= $comment_id;
	
						echo "<tr>\n";
						echo "	<td style=\"padding-top: 5px; border-bottom: 1px #CCCCCC dotted; border-right: 1px #CCCCCC dotted\">".$report["question"]."</td>\n";
	
						for($i = 1; $i <= $total_headings; $i++) {
							$total_people = ($total_people + $report["answers"][$i]["result"]);
							$total_answer = ($total_answer + ($i * $report["answers"][$i]["result"]));
							echo "<td style=\"text-align: center; border-bottom: 1px #CCCCCC dotted; border-right: 1px #CCCCCC dotted\">".$report["answers"][$i]["result"]."</td>\n";
						}
	
						echo "	<td style=\"color: #FFFFFF; background-color: #666666; font-weight: bold; white-space: nowrap; text-align: center\">".number_format((($total_people > 0) ? round(($total_answer / $total_people), 1) : 0), 1)."</td>\n";
						echo "</tr>\n";
						echo "<tbody id=\"".$comment_id."\" style=\"display: none\">\n";
						echo "<tr>\n";
						echo "	<td colspan=\"".($total_headings + 2)."\" style=\"border-bottom: 1px #CCCCCC dotted; border-right: 1px #CCCCCC dotted; padding-right: 10px\">\n";
								if(@count($report["answers"][0]["comments"]) > 0) {
									echo "	<span style=\"padding-left: 20px; font-weight: bold\" class=\"small-grey\">Comments:</span>\n";
									echo "	<ul style=\"margin-top: 3px\" class=\"small-grey\">\n";
									foreach($report["answers"][0]["comments"] as $comment) {
										echo "	<li>".$comment."</li>\n";
									}
									echo "	</ul>\n";
								} else {
									echo "&nbsp;";
								}
						echo "	</td>\n";
						echo "</tr>\n";
						echo "</tbody>\n";
					}
					echo "</table>\n";
	
					if(@count($comment_ids) > 0) {
						$i = @count($HEAD);
						$HEAD[$i]  = "\n<script language=\"JavaScript\" type=\"text/javascript\">\n";
						$HEAD[$i] .= "\tvar comment_ids = new Array(".@count($comment_ids).");\n";
						foreach($comment_ids as $key => $comment_id) {
							$HEAD[$i] .= "\tcomment_ids[".$key."] = '".$comment_id."';\n";
						}
						$HEAD[$i] .= "</script>\n";
					}
	
					if(@count($REGION_IDS) > 0) {
						$query	= "SELECT `region_name` FROM `".CLERKSHIP_DATABASE."`.`regions` 
									WHERE `region_id` IN (".implode(", ", $REGION_IDS).") 
									ORDER BY `region_name` ASC";
						$results	= $db->GetAll($query);
						if($results) {
							$region_names = array();
							foreach($results as $result) {
								$region_names[] = html_encode($result["region_name"]);
							}
							echo "<br />\n";
							echo "<div class=\"content-small\">\n";
							echo "		<span style=\"font-weight: bold\">Regions included:</span> ".implode(", ", $region_names);
							echo "</div>\n";
						}
					}
					if(@count($TEACHER_IDS) > 0) {
						$query	= "SELECT CONCAT(`firstname`, ' ', `lastname`) AS `fullname` FROM `".AUTH_DATABASE."`.`user_data` 
									WHERE `id` IN (".implode(", ", $TEACHER_IDS).") 
									ORDER BY `fullname` ASC";
						$results	= $db->GetAll($query);
						if($results) {
							$teacher_names = array();
							foreach($results as $result) {
								$teacher_names[] = html_encode($result["fullname"]);
							}
							echo "<div class=\"content-small\" style=\"margin-top: 20px\">\n";
							echo "		<span style=\"font-weight: bold\">Teachers included:</span> ".implode(", ", $teacher_names);
							echo "</div>\n";
						}
					}
					$report_timer_end = getmicrotime();
					system_log_data("report", "[success] ".$_SESSION["details"]["firstname"]." ".$_SESSION["details"]["lastname"]." (".$ENTRADA_USER->getID().") successfully generated detailed report; runtime was ".number_format(($report_timer_end - $report_timer_start), 2)." seconds. ".((@count($region_names)) ? "Regions: ".implode(", ", $region_names) : "All Regions").". ".((@count($teacher_names)) ? "Teachers: ".implode(", ", $teacher_names) : "All Teachers").".");
				break;
				default :
					$ERROR++;
					$ERRORSTR[] = "Unrecognized report type, please navigate using the interface.";
	
					echo display_error($ERRORSTR);
	
					system_log_data("error", "Invalid report type was specified in step 3.");
				break;
			}
		break;
		case "2" :
			$form_id		= 0;
			$form_type		= "";
			$query		= "SELECT a.*, b.`form_type`
							FROM `".CLERKSHIP_DATABASE."`.`evaluations` AS a
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`eval_forms` AS b
							ON b.`form_id` = a.`form_id`
							WHERE a.`item_status` <> 'cancelled'
							AND a.`item_id` IN (".implode(", ", $ITEM_IDS).")
							ORDER BY a.`item_title` ASC";
			$results	= $db->GetAll($query);
			if($results) {
				foreach($results as $result) {
					if(!$form_id) {
						$form_id	= (int) $result["form_id"];
						$form_type	= trim($result["form_type"]);
					} elseif(($form_id != (int) $result["form_id"]) || ($form_type != $result["form_type"])) {
						$ERROR++;
						$ERRORSTR[] = "To produce a summative report you must select evaluations which have used the same form to evaluate the students with.";
					}
				}
	
				if($ERROR) {
					echo display_error($ERRORSTR);
				} else {
					echo "<div class=\"content-heading\">Summative Report Generation</div>\n";
					echo "<div class=\"content-small\" style=\"padding-left: 20px; margin-bottom: 15px\">\n";
					echo "	<span style=\"font-weight: bold\">Summating results using:</span><br />\n";
						foreach($results as $result) {
							echo $result["item_title"]."<br />";
						}
					echo "</div>\n";
	
					$query			= "SELECT `category_id`, `category_name`, `category_parent`, `category_start`, `category_finish`, `category_status` 
										FROM `".CLERKSHIP_DATABASE."`.`categories` 
										WHERE `category_status` <> 'trash' 
										ORDER BY `category_order` ASC";
					$all_categories	= $db->GetAll($query);
	
					echo "<form action=\"".ENTRADA_URL."/admin/evaluations/reports\" method=\"get\">\n";
					echo "<input type=\"hidden\" name=\"section\" value=\"student-clerkship-evaluations\" />\n";
					echo "<input type=\"hidden\" name=\"step\" value=\"3\" />\n";
					echo "<input type=\"hidden\" name=\"form_id\" value=\"".$form_id."\" />\n";
					echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\" border=\"0\">\n";
					echo "<tr>\n";
					echo "	<td colspan=\"2\">\n";
					echo "		<div class=\"content-subheading\" style=\"border-bottom: 1px #CCCCCC dotted; margin-bottom: 10px\">Category Selection</div>\n";
					echo "		You must choose a category to include for each evaluation you have chosen to include in this report. You would generally want to choose the same category for each evaluation.";
					echo "	</td>\n";
					echo "</tr>\n";
					foreach($results as $result) {
						echo "<tr>\n";
						echo "	<td style=\"width: 50%; text-align: left\"><label for=\"cat-".$result["item_id"]."\" class=\"form-required\">".html_encode($result["item_title"])."</label></td>\n";
						echo "	<td style=\"width: 50%; text-align: left\">\n";
									if($all_categories) {
										$cat_output = clerkship_categories_inselect($all_categories, $result["category_id"], array((isset($_GET["category_ids"][$result["item_id"]]) ? $_GET["category_ids"][$result["item_id"]] : NULL)), 0, array(), false);
										if(trim($cat_output) != "") {
											echo "<select id=\"cat-".$result["item_id"]."\" name=\"category_ids[".$result["item_id"]."]\" style=\"width: 300px\">\n";
											echo "<option value=\"".$result["category_id"]."\">-- All Displayed Categories --</option>\n";
											echo $cat_output;
											echo "<option value=\"0\">No Categories For This Stream</option>\n";
											echo "</select>\n";
										}
									} else {
										$ERROR++;
										$ERRORSTR[] = "There are no categories to display reports for.";
	
										echo display_error($ERRORSTR);
									}
						echo "	</td>\n";
						echo "</tr>\n";
					}
					echo "<tr>\n";
					echo "	<td colspan=\"2\">&nbsp</td>\n";
					echo "</tr>\n";
					echo "<tr>\n";
					echo "	<td colspan=\"2\">\n";
					echo "		<div class=\"content-subheading\" style=\"border-bottom: 1px #CCCCCC dotted\">Reporting Options</div>\n";
					echo "	</td>\n";
					echo "</tr>\n";
	
					// Check to see if they are allowed to generated unfiltered reports.
					if((@is_array($ADMINISTRATION[$_SESSION["details"]["group"]][$_SESSION["details"]["role"]]["options"][$MODULE])) && (@in_array("allow-unfiltered", $ADMINISTRATION[$_SESSION["details"]["group"]][$_SESSION["details"]["role"]]["options"][$MODULE]))) {
						echo "<tr>\n";
						echo "	<td colspan=\"2\"><input type=\"checkbox\" id=\"filter_comments\" name=\"filter_comments\" value=\"1\" style=\"vertical-align: middle\" checked=\"checked\" /> <label for=\"filter_comments\" class=\"form-nrequired\">Filter comments in this report.</label></td>\n";
						echo "</tr>\n";
						echo "<tr>\n";
						echo "	<td colspan=\"2\">&nbsp;</td>\n";
						echo "</tr>\n";
					}
	
					echo "<tr>\n";
					echo "	<td><label for=\"report_style\" class=\"form-nrequired\">Select the style of this report:</label></td>\n";
					echo "	<td>\n";
					echo "		<select id=\"report_style\" name=\"report_style\" style=\"width: 300px\">\n";
					echo "			<option value=\"details\"".(((!$_GET["report_style"]) || ($_GET["report_style"] == "details")) ? " SELECTED" : "").">Detailed Report</option>\n";
					echo "			<option value=\"summary\"".(($_GET["report_style"] == "summary") ? " SELECTED" : "").">Summary Report</option>\n";
					echo "		</select>\n";
					echo "	</td>\n";
					echo "</tr>\n";
					echo "<tr>\n";
					echo "	<td colspan=\"2\">&nbsp;</td>\n";
					echo "</tr>\n";
	
					echo "<tr>\n";
					echo "	<td style=\"vertical-align: top\">\n";
					echo "		<input type=\"radio\" id=\"region_type_all\" name=\"region_type\" value=\"all\" style=\"vertical-align: middle\" checked=\"checked\" onclick=\"document.getElementById('show_regions').style.display='none'\" /> <label for=\"region_type_all\" class=\"form-nrequired\">Include all regions with this report.</label><br />\n";
					echo "		<input type=\"radio\" id=\"region_type_specified\" name=\"region_type\" value=\"specified\" style=\"vertical-align: middle\" onclick=\"document.getElementById('show_regions').style.display='block'\" /> <label for=\"region_type_specified\" class=\"form-nrequired\">Specify which regions to include with this report.</label><br />\n";
					echo "	</td>\n";
					echo "	<td style=\"vertical-align: top\">\n";
					echo "		<div id=\"show_regions\" style=\"display: none\">\n";
					echo "			<select id=\"region_ids\" name=\"region_ids[]\" style=\"width: 300px; height: 225px\" multiple=\"multiple\">\n";
					$query	= "SELECT * FROM `".CLERKSHIP_DATABASE."`.`regions` 
								ORDER BY `region_name` ASC";
					$results	= $db->GetAll($query);
					if($results) {
						foreach($results as $result) {
							echo "		<option value=\"".$result["region_id"]."\">".html_encode($result["region_name"])."</option>\n";
						}
					}
					echo "			</select>\n";
					echo "		</div>\n";
					echo "	</td>\n";
					echo "</tr>\n";
					echo "<tr>\n";
					echo "	<td colspan=\"2\">&nbsp;</td>\n";
					echo "</tr>\n";
	
					if ($form_type == "teacher" && ($_SESSION["details"]["group"] != "faculty")) {
						echo "<tr>\n";
						echo "	<td style=\"vertical-align: top\">\n";
						echo "		<input type=\"radio\" id=\"teacher_type_all\" name=\"teacher_type\" value=\"all\" style=\"vertical-align: middle\" checked=\"checked\" onclick=\"document.getElementById('show_teachers').style.display='none'\" /> <label for=\"teacher_type_all\" class=\"form-nrequired\">Include all teachers with this report.</label><br />\n";
						echo "		<input type=\"radio\" id=\"teacher_type_specified\" name=\"teacher_type\" value=\"specified\" style=\"vertical-align: middle\" onclick=\"document.getElementById('show_teachers').style.display='block'\" /> <label for=\"teacher_type_specified\" class=\"form-nrequired\">Specify which teachers to include with this report.</label><br />\n";
						echo "	</td>\n";
						echo "	<td style=\"vertical-align: top\">\n";
						echo "		<div id=\"show_teachers\" style=\"display: none\">\n";
						echo "			<select id=\"teacher_ids\" name=\"teacher_ids[]\" style=\"width: 300px; height: 225px\" multiple=\"multiple\">\n";
						$query	= "SELECT `id` AS `proxy_id`, CONCAT_WS(', ', `lastname`, `firstname`) AS `fullname` 
									FROM `".AUTH_DATABASE."`.`user_data` 
									LEFT JOIN `".CLERKSHIP_DATABASE."`.`eval_completed` 
									ON `".CLERKSHIP_DATABASE."`.`eval_completed`.`instructor_id` = `".AUTH_DATABASE."`.`user_data`.`id` 
									WHERE `".CLERKSHIP_DATABASE."`.`eval_completed`.`instructor_id` NOT LIKE 'OT-' 
									AND `".CLERKSHIP_DATABASE."`.`eval_completed`.`instructor_id` <> '0' 
									GROUP BY `".CLERKSHIP_DATABASE."`.`eval_completed`.`instructor_id` 
									ORDER BY `fullname`";
						$results	= $db->GetAll($query);
						if($results) {
							foreach($results as $result) {
								echo "		<option value=\"".$result["proxy_id"]."\">".html_encode($result["fullname"])."</option>\n";
							}
						}
						echo "			</select>\n";
						echo "		</div>\n";
						echo "	</td>\n";
						echo "</tr>\n";
						echo "<tr>\n";
						echo "	<td colspan=\"2\">&nbsp;</td>\n";
						echo "</tr>\n";
					} elseif ($form_type == "teacher") {
						echo "<input type=\"hidden\" name=\"teacher_ids[]\" value=\"".$ENTRADA_USER->getID()."\" />";
						echo "<input type=\"hidden\" name=\"teacher_type\" value=\"specified\" />";
					}
					echo "<tr>\n";
					echo "	<td colspan=\"2\" style=\"text-align: right\">\n";
					echo "		<input type=\"submit\" class=\"button\" style=\"background-image: url('".ENTRADA_URL."/images/btn_bg.gif')\" value=\"Generate Report\" />\n";
					echo "	</td>\n";
					echo "</tr>\n";
					echo "</table>\n";
					echo "</form>\n";
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "There were no evaluations matching any of the identifiers that were provided. Please try again.";
	
				echo display_error($ERRORSTR);
	
				system_log_data("error", "The generate reporting page was accessed with item_ids; however the query produced no results. Database said: ".$db->ErrorMsg());
			}
		break;
		case "1" :
		default :
			?>
			<div class="content-heading">Generate Reports</div>
			To generate a new report select the evaluation or evaluations that you would like to tabulate results from.
			<br /><br />
			<?php
			if($ERROR) {
				echo display_error($ERRORSTR);
			}
			if($SUCCESS) {
				echo display_success($SUCCESSSTR);
			}
			?>
			<form action="<?php echo ENTRADA_URL; ?>/admin/evaluations/reports" method="get">
			<input type="hidden" name="section" value="student-clerkship-evaluations" />
			<input type="hidden" name="step" value="2" />
			<table width="100%" cellspacing="0" cellpadding="2" border="0">
			<tr>
				<td width="20%" style="vertical-align: top"><label for="item_ids" class="form-required">Running Evaluation:</label></td>
				<td width="80%">
					<?php
					$query		= "SELECT * FROM `".CLERKSHIP_DATABASE."`.`categories` WHERE `category_name` LIKE 'Class of %'";
					$results	= $db->GetAll($query);
					if($results) {
						echo "<select id=\"category_id\" name=\"category_id\" size=\"10\" style=\"width: 100%; height: 225px\">\n";
						foreach($results as $result) {
							
							$query = "SELECT `item_id` FROM `".CLERKSHIP_DATABASE."`.`evaluations`
										WHERE `category_id` IN (
											SELECT a.`category_id` FROM `".CLERKSHIP_DATABASE."`.`categories` AS a
											WHERE a.`category_parent` = ".$db->qstr($result["category_id"])."
											UNION
											SELECT a.`category_id` FROM `".CLERKSHIP_DATABASE."`.`categories` AS a
											JOIN `".CLERKSHIP_DATABASE."`.`categories` AS b
											ON a.`category_parent` = b.`category_id`
											WHERE b.`category_parent` = ".$db->qstr($result["category_id"])."
										)
										AND (`item_title` LIKE '%Rotation Evaluation'
										OR `item_title` LIKE '%Teacher Evaluation'
										OR `item_title` LIKE '%Preceptor Evaluation')";
							$found = $db->GetRow($query);
							if ($found) {
								if ($_SESSION["details"]["group"] != "faculty") {
									echo "<option value=\"".$result["category_id"]."r\">".html_encode($result["category_name"])." Rotation Evaluations</option>\n";
								}
								echo "<option value=\"".$result["category_id"]."t\">".html_encode($result["category_name"])." Teacher Evaluations</option>\n";
							}
						}
						echo "</select>\n";
					}
					?>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="right">
					<input type="submit" class="button" style="background-image: url('<?php echo ENTRADA_URL; ?>/images/btn_bg.gif')" value="Proceed" />
				</td>
			</tr>
			<tr>
			</table>
			</form>
			<?php
		break;
	}
}