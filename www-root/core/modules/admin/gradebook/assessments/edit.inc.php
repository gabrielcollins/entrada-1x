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
	header("Location: " . ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("gradebook", "update", false)) {
	$ONLOAD[] = "setTimeout('window.location=\\'" . ENTRADA_URL . "/admin/" . $MODULE . "\\'', 15000)";

	$ERROR++;
	$ERRORSTR[] = "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:" . html_encode($AGENT_CONTACTS["administrator"]["email"]) . "\">" . html_encode($AGENT_CONTACTS["administrator"]["name"]) . "</a> for assistance.";

	echo display_error();

	application_log("error", "Group [" . $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"] . "] and role [" . $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"] . "] does not have access to this module [" . $MODULE . "]");
} elseif (!isset($ASSESSMENT_ID)) {
	$ERROR++;
	$ERRORSTR[] = "In order to edit an assessment in a gradebook you must provide a valid assessment identifier. The provided ID is invalid.";

	echo display_error();

	application_log("notice", "Failed to provide assessment identifier when attempting to edit an assessment");
} else {
	$query = "	SELECT * FROM `assessments` 
						WHERE `assessment_id` = " . $db->qstr($ASSESSMENT_ID);
	$assessment_details = $db->GetRow($query);

	if ($assessment_details) {
		if ($COURSE_ID) {
			$COURSE_ID = $assessment_details["course_id"]; // Ensure (for permissions and data congruency) that the course_id is actually that of the assessment
			$query = "	SELECT * FROM `courses` 
								WHERE `course_id` = " . $db->qstr($COURSE_ID) . "
								AND `course_active` = '1'";
			$course_details = $db->GetRow($query);

			$HEAD[] = "<script type=\"text/javascript\">var DELETE_IMAGE_URL = '".ENTRADA_URL."/images/action-delete.gif';</script>";
			$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/picklist.js\"></script>\n";
			$HEAD[]	= "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";	

			$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("section" => "grade", "id" => $COURSE_ID, "step" => false)), "title" => "Grading Assessment");

			
			$m_query = "	SELECT * FROM `assessment_marking_schemes` 
								WHERE `enabled` = 1;";
			$MARKING_SCHEMES = $db->GetAll($m_query);

			$assessment_options_query = "SELECT `id`, `title`, `active`
									 FROM `assessments_lu_meta_options`
									 WHERE `active` = '1'";
			$assessment_options = $db->GetAll($assessment_options_query);
			if ($course_details && $MARKING_SCHEMES && $ENTRADA_ACL->amIAllowed(new GradebookResource($course_details["course_id"], $course_details["organisation_id"]), "update")) {

				function return_id($arr) {
					return $arr["id"];
				}

				$MARKING_SCHEME_IDS = array_map("return_id", $MARKING_SCHEMES);
				$BREADCRUMB[] = array("url" => ENTRADA_URL . "/admin/" . $MODULE . "?" . replace_query(array("section" => "edit", "id" => $COURSE_ID, "step" => false)), "title" => "Editing Assessment");
				
				// Error Checking
				switch ($STEP) {
					case 2 :
						
						$clinical_presentations = array();
					
						if (isset($_POST["clinical_presentations"])) {
								$tmp_input = $_POST["clinical_presentations"];
								foreach ($tmp_input as $presentation) {
									$PROCESSED["clinical_presentations"][] = clean_input($presentation, "int");
								}
						}
						
						if (isset($_POST["checked_objectives"]) && ($checked_objectives = $_POST["checked_objectives"]) && (is_array($checked_objectives))) {
							foreach ($checked_objectives as $objective_id => $status) {
								if ($objective_id = (int) $objective_id) {
									if (isset($_POST["objective_text"][$objective_id]) && ($tmp_input = clean_input($_POST["objective_text"][$objective_id], array("notags")))) {
										$objective_text = $tmp_input;
									} else {
										$objective_text = false;
									}
									$PROCESSED["curriculum_objectives"][$objective_id] = $objective_text;
								}
							}
						}
						
						if (isset($_POST["associated_audience"]) && $_POST["associated_audience"] == "manual_select") {

							if((isset($_POST["cohort"])) && ($cohort = clean_input($_POST["cohort"], "credentials"))) {
								$PROCESSED["cohort"] = $cohort;
							} else {
								$ERROR++;
								$ERRORSTR[] = "You must select an <strong>Audience</strong> for this assessment.";
							}
						} elseif($group_id = (int)$_POST["associated_audience"]) {
							$PROCESSED["cohort"] = $group_id;
						} else {
								$ERROR++;
								$ERRORSTR[] = "You must select an <strong>Audience</strong> for this assessment.";
						}

						if ((isset($_POST["name"])) && ($name = clean_input($_POST["name"], array("notags", "trim")))) {
							$PROCESSED["name"] = $name;
						} else {
							$ERROR++;
							$ERRORSTR[] = "You must supply a valid <strong>Name</strong> for this assessment.";
						}

						if ((isset($_POST["grade_weighting"])) && ($_POST["grade_weighting"] !== NULL)) {
							$PROCESSED["grade_weighting"] = clean_input($_POST["grade_weighting"], "float");
						} else {
							$ERROR++;
							$ERRORSTR[] = "You must supply a <strong>Grade Weighting</strong> for this assessment.";
						}
						if ((isset($_POST["description"])) && ($description = clean_input($_POST["description"], array("notags", "trim")))) {
							$PROCESSED["description"] = $description;
						} else {
							$PROCESSED["description"] = "";
						}
						if ((isset($_POST["type"])) && ($type = clean_input($_POST["type"], array("trim")))) {
							if ((@in_array($type, $ASSESSMENT_TYPES))) {
								$PROCESSED["type"] = $type;
							} else {
								$ERROR++;
								$ERRORSTR[] = "You must supply a valid <strong>Type</strong> for this assessment. The submitted type is invalid.";
							}
						} else {
							$ERROR++;
							$ERRORSTR[] = "You must pick a valid <strong>Type</strong> for this assessment.";
						}

						if ((isset($_POST["marking_scheme_id"])) && ($marking_scheme_id = clean_input($_POST["marking_scheme_id"], array("int")))) {
							if (@in_array($marking_scheme_id, $MARKING_SCHEME_IDS)) {
								$PROCESSED["marking_scheme_id"] = $marking_scheme_id;
							} else {
								$ERROR++;
								$ERRORSTR[] = "The <strong>Marking Scheme</strong> you selected does not exist or is not enabled.";
							}
						} else {
							$ERROR++;
							$ERRORSTR[] = "The <strong>Marking Scheme</strong> field is a required field.";
						}
						//Show in learner gradebook check
						if ((isset($_POST["show_learner_option"]))) {
							switch ($show_learner_option = clean_input($_POST["show_learner_option"], array("trim", "int"))) {
								case 0 :
									$PROCESSED["show_learner"] = $show_learner_option;
									$PROCESSED["release_date"] = 0;
									$PROCESSED["release_until"] = 0;
								break;
								case 1 :
									$PROCESSED["show_learner"] = $show_learner_option;
									$release_dates = validate_calendars("show", false, false);
									if ((isset($release_dates["start"])) && ((int) $release_dates["start"])) {
										$PROCESSED["release_date"]	= (int) $release_dates["start"];
									} else {
										$PROCESSED["release_date"]	= 0;
									}
									if ((isset($release_dates["finish"])) && ((int) $release_dates["finish"])) {
										$PROCESSED["release_until"]	= (int) $release_dates["finish"];
									} else {
										$PROCESSED["release_until"]	= 0;
									}
								break;
								default :
									$PROCESSED["show_learner"] = 0;
								break;
							}
						}
						//narrative check
						if ((isset($_POST["narrative_assessment"])) && ($narrative = clean_input($_POST["narrative_assessment"], array("trim", "int")))) {
							$PROCESSED["narrative"] = $narrative;
						} else {
							$PROCESSED["narrative"] = 0;
						}
						//optional/required check
						if ((isset($_POST["assessment_required"]))) {
							switch (clean_input($_POST["assessment_required"], array("trim", "int"))) {
								case 0 :
									$PROCESSED["required"] = 0;
									break;
								case 1 :
									$PROCESSED["required"] = 1;
									break;
								default :
									break;
							}
						}
						//characteristic check
						if ((isset($_POST["assessment_characteristic"])) && ($assessment_characteristic = clean_input($_POST["assessment_characteristic"], array("trim", "int"))) == 0) {
							$ERROR++;
							$ERRORSTR[] = "The <strong>Assessment Characteristic</strong> field is a required field.";
						} else if ((isset($_POST["assessment_characteristic"])) && ($assessment_characteristic = clean_input($_POST["assessment_characteristic"], array("trim", "int")))) {
							$PROCESSED["characteristic_id"] = $assessment_characteristic;
						}
						//extended options check
						if ((is_array($_POST["option"])) && (count($_POST["option"]))) {
							$assessment_options_selected = array();
							foreach ($_POST["option"] as $option_id) {
								if ($option_id = (int) $option_id) {
									$query = "SELECT * FROM `assessments_lu_meta_options` 
											  WHERE id = " . $db->qstr($option_id) . "
											  AND `active` = '1'";
									$results = $db->GetAll($query);
									if ($results) {
										$assessment_options_selected[] = $option_id;
									}
								}
							}
						}
						// Sometimes requried field "number grade points total". Specifies what a numeric marking scheme assessment is "out of".
						// Only required when marking scheme is numeric, ID 3, hardcoded.
						if ((isset($_POST["numeric_grade_points_total"])) && ($points_total = clean_input($_POST["numeric_grade_points_total"], array("notags", "trim")))) {
							$PROCESSED["numeric_grade_points_total"] = $points_total;
						} else {
							$PROCESSED["numeric_grade_points_total"] = "";
							if (isset($PROCESSED["marking_scheme_id"])) {
								// Numberic marking scheme, hardcoded, lame
								if ($PROCESSED["marking_scheme_id"] == 3) {
									$ERROR++;
									$ERRORSTR[] = "The <strong>Maximum Points</strong> field is a required field when using the <strong>Numeric</strong> marking scheme.";
								}
							}
						}
						if (isset($_POST["post_action"])) {
							if (@in_array($_POST["post_action"], array("new", "index", "parent", "grade"))) {
								$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = $_POST["post_action"];
							} else {
								$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "index";
							}
						} else {
							$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "index";
						}
						
						if (!$ERROR) {
							$PROCESSED["updated_date"] = time();
							$PROCESSED["updated_by"] = $_SESSION["details"]["id"];
							$PROCESSED["course_id"] = $COURSE_ID;

							if ($db->AutoExecute("assessments", $PROCESSED, "UPDATE", "`assessment_id` = " . $db->qstr($assessment_details["assessment_id"]))) {
								if ($assessment_options) {
									foreach ($assessment_options as $assessment_option) {
										$query = "SELECT * FROM `assessments` WHERE assessment_id =" . $ASSESSMENT_ID;
										$results = $db->GetRow($query);
										if ($results) {
											$PROCESSED["assessment_id"] = $results["assessment_id"];
											$PROCESSED["option_id"] = $assessment_option["id"];
											if (is_array($assessment_options_selected) && in_array($assessment_option["id"], $assessment_options_selected)) {
												$PROCESSED["option_active"] = 1;
											} else {
												$PROCESSED["option_active"] = 0;
											}
											$db->AutoExecute("assessment_options", $PROCESSED, "UPDATE", "`assessment_id` = " . $db->qstr($assessment_details["assessment_id"]) . "AND `option_id` = " . $db->qstr($assessment_option["id"]));
										}
									}
								}
								
								$query = "DELETE FROM `assessment_objectives` WHERE `objective_type` = 'clinical_presentation' AND `assessment_id` = ".$db->qstr($ASSESSMENT_ID);
								if ($db->Execute($query)) {
									if ((is_array($PROCESSED["clinical_presentations"])) && (count($PROCESSED["clinical_presentations"]))) {
										foreach ($PROCESSED["clinical_presentations"] as $objective_id) {
											if (!$db->AutoExecute("assessment_objectives", array("assessment_id" => $ASSESSMENT_ID, "objective_id" => $objective_id, "objective_type" => "clinical_presentation", "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
												add_error("There was an error when trying to insert a &quot;clinical presentation&quot; into the system. System administrators have been informed of this error; please try again later.");
												application_log("error", "Unable to insert a new clinical presentation to the database when adding a new event. Database said: ".$db->ErrorMsg());
											}
										}
									}
								}
								
								$query = "DELETE FROM `assessment_objectives` WHERE `objective_type` = 'curricular_objective' AND `assessment_id` = ".$db->qstr($ASSESSMENT_ID);
								if ($db->Execute($query)) {
									if ((is_array($PROCESSED["curriculum_objectives"]) && count($PROCESSED["curriculum_objectives"]))) {
										foreach ($PROCESSED["curriculum_objectives"] as $objective_key => $objective_text) {
											if (!$db->AutoExecute("assessment_objectives", array("assessment_id" => $ASSESSMENT_ID, "objective_id" => $objective_key, "objective_details" => $objective_text, "objective_type" => "curricular_objective", "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
												add_error("There was an error when trying to insert a &quot;clinical presentation&quot; into the system. System administrators have been informed of this error; please try again later.");
												application_log("error", "Unable to insert a new clinical presentation to the database when adding a new event. Database said: ".$db->ErrorMsg());
											}
										}
									}
								}
								
								switch ($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"]) {
									case "grade" :
										$url = ENTRADA_URL . "/admin/gradebook/assessments?" . replace_query(array("step" => false, "section" => "grade", "assessment_id" => $ASSESSMENT_ID));
										$msg = "You will now be redirected to the <strong>Grade Assessment</strong> page for \"<strong>" . $PROCESSED["name"] . "</strong>\"; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"" . $url . "\" style=\"font-weight: bold\">click here</a> to continue.";
										break;
									case "new" :
										$url = ENTRADA_URL . "/admin/gradebook/assessments?" . replace_query(array("step" => false, "section" => "add"));
										$msg = "You will now be redirected to another <strong>Add Assessment</strong> page for the " . $course_details["course_name"] . " gradebook; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"" . $url . "\" style=\"font-weight: bold\">click here</a> to continue.";
										break;
									case "parent" :
										$url = ENTRADA_URL . "/admin/" . $MODULE;
										$msg = "You will now be redirected to the <strong>Gradebook</strong> index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"" . $url . "\" style=\"font-weight: bold\">click here</a> to continue.";
										break;
									case "index" :
									default :
										$url = ENTRADA_URL . "/admin/gradebook?" . replace_query(array("step" => false, "section" => "view", "assessment_id" => false));
										$msg = "You will now be redirected to the <strong>assessment index</strong> page for " . $course_details["course_name"] . "; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"" . $url . "\" style=\"font-weight: bold\">click here</a> to continue.";
										break;
								}

								$SUCCESS++;
								$SUCCESSSTR[] = $msg;
								$ONLOAD[] = "setTimeout('window.location=\\'" . $url . "\\'', 5000)";
							} else {
								$ERROR++;
								$ERRORSTR[] = "There was a problem updating this assessment in the system. The administrators have been informed of this error; please try again later.";

								application_log("error", "There was an error inserting an assessment. Database said: " . $db->ErrorMsg());
							}
						}

						if ($ERROR) {
							$STEP = 1;
						}
						break;
					case 1 :
					default :
						$PROCESSED = $assessment_details;
						$query = "SELECT * FROM `assessment_options` WHERE `assessment_id` =" . $db->qstr($ASSESSMENT_ID);
						$extended_options = $db->GetAll($query);
						foreach ($extended_options as $extended_option) {
							if ($extended_option["option_active"] == 1) {
								$assessment_options_selected[] = $extended_option["option_id"];
							} else {
								$assessment_options_selected[] = 0;
							}
						}
						break;
				}

				// Display Content
				switch ($STEP) {
					case 2 :
						if ($SUCCESS) {
							echo display_success();
						}
						if ($NOTICE) {
							echo display_notice();
						}
						if ($ERROR) {
							echo display_error();
						}
						break;
					case 1 :
					default :
						
						/** 
						* Fetch the Clinical Presentation details.
						*/
						$clinical_presentations_list = array();
						$clinical_presentations = array();

						$results = fetch_clinical_presentations();
						
						if ($results) {
							foreach ($results as $result) {
								$clinical_presentations_list[$result["objective_id"]] = $result["objective_name"];
							}
						} else {
							$clinical_presentations_list = false;
						}
						
						$query =	"SELECT a.`objective_id`, b.`objective_name`
									 FROM `course_objectives` AS a
									 JOIN `global_lu_objectives` AS b
									 ON a.`objective_id` = b.`objective_id`
									 WHERE a.`course_id` = ".$COURSE_ID."
									 AND a.`objective_type` = 'event'";
						
						$course_clinical_presentations = $db->GetAssoc($query);

						if (isset($_POST["clinical_presentations_submit"]) && $_POST["clinical_presentations_submit"]) {
							if (((isset($_POST["clinical_presentations"])) && (is_array($_POST["clinical_presentations"])) && (count($_POST["clinical_presentations"])))) {
								foreach ($_POST["clinical_presentations"] as $objective_id) {
									if ($objective_id = clean_input($objective_id, array("trim", "int"))) {
										$query	= "SELECT a.`objective_id`
													FROM `global_lu_objectives` AS a
													JOIN `course_objectives` AS b
													ON b.`course_id` = ".$COURSE_ID."
													AND a.`objective_id` = b.`objective_id`
													JOIN `objective_organisation` AS c
													ON a.`objective_id` = c.`objective_id`
													WHERE a.`objective_id` = ".$db->qstr($objective_id)."
													AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
													AND b.`objective_type` = 'event'
													AND a.`objective_active` = '1'";
										$result	= $db->GetRow($query);
										if ($result) {
											$clinical_presentations[$objective_id] = $clinical_presentations_list[$objective_id];
										}
									}
								}
							} else {
								$clinical_presentations = array();
							}
						} else {
							$query	 = "SELECT `objective_id`
										FROM `assessment_objectives`
										WHERE `assessment_id` = ".$ASSESSMENT_ID."
										AND `objective_type` = 'clinical_presentation'";
							$results = $db->GetAll($query);
							if ($results) {
								foreach ($results as $result) {
									$clinical_presentations[$result["objective_id"]] = $clinical_presentations_list[$result["objective_id"]];
								}
							}
						}
						
						/**
						* Fetch the Curriculum Objective details.
						*/
						list($curriculum_objectives_list,$top_level_id) = courses_fetch_objectives($ENTRADA_USER->getActiveOrganisation(),array($COURSE_ID),-1, 1, false, false, 0, true);
				
						$curriculum_objectives = array();

						if (isset($_POST["checked_objectives"]) && ($checked_objectives = $_POST["checked_objectives"]) && (is_array($checked_objectives))) {
							foreach ($checked_objectives as $objective_id => $status) {
								if ($objective_id = (int) $objective_id) {
									if (isset($_POST["objective_text"][$objective_id]) && ($tmp_input = clean_input($_POST["objective_text"][$objective_id], array("notags")))) {
										$objective_text = $tmp_input;
									} else {
										$objective_text = false;
									}

									$curriculum_objectives[$objective_id] = $objective_text;
								}
							}
						}
						
						$query = "SELECT `objective_id`, `objective_details` FROM `assessment_objectives` WHERE `assessment_id` = ".$db->qstr($ASSESSMENT_ID)." AND `objective_type` = 'curricular_objective'";
						$results = $db->GetAll($query);
						if ($results) {
							foreach ($results as $result) {
								$curriculum_objectives_list["objectives"][$result["objective_id"]]["event_objective"] = true;
								$curriculum_objectives_list["objectives"][$result["objective_id"]]["event_objective_details"] = $result["objective_details"];
							}
						}
						
						?>
						<h1>Edit Assessment</h1>
						<?php
						if ($ERROR) {
							echo display_error();
						}
						?>
						<form action="<?php echo ENTRADA_URL; ?>/admin/gradebook/assessments?<?php echo replace_query(array("step" => 2)); ?>" method="post" onsubmit="selIt()">
							<h2>Assessment Details</h2>
							<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Editing Assessment">
								<colgroup>
									<col style="width: 3%" />
									<col style="width: 22%" />
									<col style="width: 75%" />
								</colgroup>
								<tbody>
									<tr>
										<td></td>
										<td><label class="form-nrequired">Course Name</label></td>
										<td>
											<a href="<?php echo ENTRADA_URL; ?>/admin/gradebook?<?php echo replace_query(array("step" => false, "section" => "view")); ?>"><?php echo html_encode($course_details["course_name"]); ?></a>
										</td>
									</tr>
									<?php 					
									$query = "SELECT * FROM `groups` WHERE `group_type` = 'course_list' AND `group_value` = ".$db->qstr($COURSE_ID);
									$course_list = $db->GetRow($query);
									if($course_list){
									?>
									<tr>
										<td colspan="3">&nbsp;</td>
									</tr>
									<tr>
										<td><input type="radio" name="associated_audience" id="course_list" value ="<?php echo $course_list["group_id"];?>" checked="checked"/></td>
										<td><label for="cohort" class="form-required">Course List</label></td>
										<td>
											<span class="radio-group-title">All Learners in the <?php echo $course_details["course_code"];?> Course List Group</span>
											<div class="content-small">This assessment is intended for all learners that are members of the <?php echo $course_details["course_code"];?> Course List.</div>
										</td>
									</tr>
									<?php
									}
									?>									
									<tr>
										<td colspan="3">&nbsp;</td>
									</tr>
									<tr>
										<td><input type="radio" name="associated_audience" id="manual_select" value="manual_select"<?php echo ((!$course_list || $course_list["group_id"] != $PROCESSED["cohort"])?" checked=\"checked\"":"");?>/></td>
										<td><label for="cohort" class="form-required">Cohort</label></td>
										<td>
											<select id="cohort" name="cohort" style="width: 250px">
												<?php
												$active_cohorts = groups_get_all_cohorts($ENTRADA_USER->getActiveOrganisation());
												if (isset($active_cohorts) && !empty($active_cohorts)) {
													foreach ($active_cohorts as $cohort) {
														echo "<option value=\"" . $cohort["group_id"] . "\"" . (($PROCESSED["cohort"] == $cohort["group_id"]) ? " selected=\"selected\"" : "") . ">" . html_encode($cohort["group_name"]) . "</option>\n";
													}
												}
												?>
											</select>
										</td>
									</tr>
									<tr>
										<td colspan="3">&nbsp;</td>
									</tr>
									<tr>
										<td></td>
										<td><label for="name" class="form-required">Assessment Name</label></td>
										<td><input type="text" id="name" name="name" value="<?php echo html_encode($PROCESSED["name"]); ?>" maxlength="64" style="width: 243px" /></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<td style="vertical-align: top"><label for="description" class="form-nrequired">Assessment Description</label></td>
										<td><textarea id="description" name="description" class="expandable" style="width: 99%; height: 150px"><?php echo html_encode($PROCESSED["description"]); ?></textarea></td>
									</tr>
									<tr>
										<td colspan="3">&nbsp;</td>
									</tr>
									<tr>
										<td></td>
										<td><label for="grade_weighting" class="form-nrequired">Assessment Weighting</label></td>
										<td>
											<input type="text" id="grade_weighting" name="grade_weighting" value="<?php echo (int) html_encode($PROCESSED["grade_weighting"]); ?>" maxlength="5" style="width: 40px" />
											<span class="content-small"><strong>Tip:</strong> The percentage or numeric value of the final grade this assessment is worth.</span>
										</td>
									</tr>
								</tbody>
								<tbody id="assessment_required_options">
									<tr>
										<td>&nbsp;</td>
										<td colspan="2" style="padding-top: 10px">
											<label class="form-nrequired" for="assessment_required_0">Is this assessment <strong>optional</strong> or <strong>required</strong>?</label>
											<div style="margin: 5px 0 0 25px">
												<input type="radio" name="assessment_required" value="0" id="assessment_required_0" <?php echo (($PROCESSED["required"] == 0)) ? " checked=\"checked\"" : "" ?> /> <label class="form-nrequired" for="assessment_required_0">Optional</label><br />
												<input type="radio" name="assessment_required" value="1" id="assessment_required_1" <?php echo (($PROCESSED["required"] == 1)) ? " checked=\"checked\"" : "" ?> /> <label class="form-nrequired" for="assessment_required_1">Required</label>
											</div>
										</td>
									</tr>
								</tbody>
								<tbody>
									<tr>
										<td colspan="3">&nbsp;</td>
									</tr>
									<tr>
										<td></td>
										<td><label for="assessment_characteristic" class="form-required">Characteristic</label></td>
										<td>
											<select id="assessment_characteristic" name="assessment_characteristic">
												<option value="">-- Select Assessment Characteristic --</option>
												<?php
												$query = "	SELECT *
												FROM `assessments_lu_meta`
												WHERE `organisation_id` = " . $db->qstr($ENTRADA_USER->getActiveOrganisation()) . "
												AND `active` = '1'
												ORDER BY `type` ASC, `title` ASC";
												$assessment_characteristics = $db->GetAll($query);
												if ($assessment_characteristics) {
													$type = "";
													foreach ($assessment_characteristics as $key => $characteristic) {
														if ($type != $characteristic["type"]) {
															if ($key) {
																echo "</optgroup>";
															}
															echo "<optgroup label=\"" . ucwords(strtolower($characteristic["type"])) . "s\">";

															$type = $characteristic["type"];
														}

														echo "<option value=\"" . $characteristic["id"] . "\" assessmenttype=\"" . $characteristic["type"] . "\"" . (($PROCESSED["characteristic_id"] == $characteristic["id"]) ? " selected=\"selected\"" : "") . ">" . $characteristic["title"] . "</option>";
													}
													echo "</optgroup>";
												}
												?>
											</select>
										</td>
									</tr>
								</tbody>
								<tbody id="assessment_options" style="display: none;">
									<tr>
										<td></td>
										<td style="vertical-align: top;"><label for="extended_option1" class="form-nrequired">Extended Options</label></td>
										<td>
											<?php
											$query = "SELECT `id`, `title` FROM `assessments_lu_meta_options`";
											$assessment_options = $db->GetAll($query);
											if ($assessment_options) {
												foreach ($assessment_options as $assessment_option) {
													echo "<input type=\"checkbox\" value=\"" . $assessment_option["id"] . "\" name=\"option[]\"" . (((in_array($assessment_option["id"], $assessment_options_selected))) ? " checked=\"checked\"" : "") . " id=\"extended_option" . $assessment_option["id"] . "\"/><label for=\"extended_option" . $assessment_option["id"] . "\">" . $assessment_option["title"] . "</label><br />";
												}
											}
											?>
										</td>
									</tr>
									<tr>
										<td colspan="3">&nbsp;</td>
									</tr>
								</tbody>
								<tbody>
									<tr>
										<td></td>
										<td><label for="marking_scheme_id" class="form-required">Marking Scheme</label></td>
										<td>
											<select id="marking_scheme_id" name="marking_scheme_id" style="width: 203px">
												<?php
												foreach ($MARKING_SCHEMES as $scheme) {
													echo "<option value=\"" . $scheme["id"] . "\"" . (($PROCESSED["marking_scheme_id"] == $scheme["id"]) ? " selected=\"selected\"" : "") . ">" . $scheme["name"] . "</option>";
												}
												?>
											</select>
										</td>
									</tr>
									<tr id="numeric_marking_scheme_details" style="display: none;">
										<td></td>
										<td><label for="numeric_grade_points_total" class="form-required">Maximum Points</label></td>
										<td>
											<input type="text" id="numeric_grade_points_total" name="numeric_grade_points_total" value="<?php echo html_encode($PROCESSED["numeric_grade_points_total"]); ?>" maxlength="5" style="width: 50px" />
											<span class="content-small"><strong>Tip:</strong> Maximum points possible for this assessment (i.e. <strong>20</strong> for &quot;X out of 20).</span>
										</td>
									</tr>
									<tr>
										<td></td>
										<td><label for="type" class="form-required">Assessment Type</label></td>
										<td>
											<select id="type" name="type" style="width: 203px">
												<?php
												foreach ($ASSESSMENT_TYPES as $type) {
													echo "<option value=\"" . $type . "\"" . (($PROCESSED["type"] == $type) ? " selected=\"selected\"" : "") . ">" . $type . "</option>";
												}
												?>
											</select>
										</td>
									</tr>
									<tr>
										<td colspan="3">&nbsp;</td>
									</tr>
								</tbody>
								<tbody>
									<tr>
										<td colspan="3"><input type="radio" name="show_learner_option" value="0" id="show_learner_option_0" <?php echo (($PROCESSED["show_learner"] == 0)) ? " checked=\"checked\"" : "" ?> style="margin-right: 5px;" /><label class="form-nrequired" for="show_learner_option_0">Don't Show this Assessment in Learner Gradebook</label></td>
									</tr>
									<tr>
										<td colspan="3"><input type="radio" name="show_learner_option" value="1" id="show_learner_option_1" <?php echo (($PROCESSED["show_learner"] == 1)) ? " checked=\"checked\"" : "" ?> style="margin-right: 5px;" /><label class="form-nrequired" for="show_learner_option_1">Show this Assessment in Learner Gradebook</label></td>
									</tr>
									<tr>
										<td colspan="3">&nbsp;</td>
									</tr>
								</tbody>
								<tbody id="gradebook_release_options" style="display: none;">
									<tr>
										<td></td>
										<td><?php echo generate_calendars("show", "", true, true, ((isset($PROCESSED["release_date"])) ? $PROCESSED["release_date"] : time()), true, false, ((isset($PROCESSED["release_until"])) ? $PROCESSED["release_until"] : 0), true, false, " in Gradebook After", " in Gradebook Until"); ?></td>
										<td></td>
									</tr>
									<tr>
										<td colspan="3">&nbsp;</td>
									</tr>
								</tbody>
								<tbody>
									<tr>
										<td><input type="checkbox" id="narrative_assessment" name="narrative_assessment" value="1" <?php echo (($PROCESSED["narrative"] == 1)) ? " checked=\"checked\"" : ""?> /></td>
										<td colspan="2">
											<label for="narrative_assessment" class="form-nrequired">This is a <strong>narrative assessment</strong>.</label>
										</td>
									</tr>
								</tbody>
							</table>
							<script type="text/javascript" charset="utf-8">
								jQuery(function($) {
									jQuery('#marking_scheme_id').change(function() {
										if(jQuery(':selected', this).val() == 3 || jQuery(':selected', this).text() == "Numeric") {
											jQuery('#numeric_marking_scheme_details').show();
										} else {
											jQuery('#numeric_marking_scheme_details').hide();
										}
									}).trigger('change');
												
									jQuery('#grade_weighting').keyup(function() {
										if (parseInt(jQuery('#grade_weighting').val())) {
											jQuery('#assessment_required_1').attr('checked', 'checked');
											jQuery('#assessment_required_options').hide();
														
										} else {
											jQuery('#assessment_required_0').attr('checked', 'checked');
											jQuery('#assessment_required_options').show();
														
										}
									});
												
									jQuery('#grade_weighting').ready(function() {
										if (parseInt(jQuery('#grade_weighting').val())) {
											jQuery('#assessment_required_1').attr('checked', 'checked');
											jQuery('#assessment_required_options').hide();
														
										} else {
											jQuery('#assessment_required_options').show();
														
										}
									});
												
									jQuery('#assessment_characteristic').change(function (){
										jQuery('#assessment_options input:[type=checkbox]').removeAttr('checked');
										var assessmentType = jQuery('#assessment_characteristic option:selected').attr('assessmenttype');
										if(assessmentType == 'exam' || assessmentType == 'quiz') {
											jQuery('#assessment_options').show();
										} else {
											jQuery('#assessment_options').hide();
										}
									});
												
									jQuery('#assessment_characteristic').ready(function (){
										var assessmentType = jQuery('#assessment_characteristic option:selected').attr('assessmenttype');
										if(assessmentType == 'exam' || assessmentType == 'quiz') {
											jQuery('#assessment_options').show();
										} else {
											jQuery('#assessment_options').hide();
										}
									});
									
									jQuery("input[name='show_learner_option']").change(function(){
										if (jQuery("input[name='show_learner_option']:checked").val() == 1) {
											jQuery('#gradebook_release_options').show();
										}
										else if (jQuery("input[name='show_learner_option']:checked").val() == 0) {
											jQuery('#gradebook_release_options').hide();
										}
									});

									jQuery(document).ready(function(){
										if (jQuery("input[name='show_learner_option']:checked").val() == 1) {
											jQuery('#gradebook_release_options').show();
										}
										else if (jQuery("input[name='show_learner_option']:checked").val() == 0) {
											jQuery('#gradebook_release_options').hide();
										}
									});
										
								});
							</script>
							<?php
							list($course_objectives,$top_level_id) = courses_fetch_objectives($ENTRADA_USER->getActiveOrganisation(), array($ASSESSMENT_ID), -1, 0, false, $posted_objectives, 0, false);
							require_once(ENTRADA_ABSOLUTE."/javascript/courses.js.php");
							$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
							?>
							<a name="course-objectives-section"></a>
							<h2 title="Course Objectives Section">Assessment Objectives</h2>
							<div id="course-objectives-section">
								<input type="hidden" id="objectives_head" name="course_objectives" value="" />
								<?php
								if (is_array($course_objectives["primary_ids"])) {
									foreach ($course_objectives["primary_ids"] as $objective_id) {
										echo "<input type=\"hidden\" class=\"primary_objectives\" id=\"primary_objective_".$objective_id."\" name=\"primary_objectives[]\" value=\"".$objective_id."\" />\n";
									}
								}
								if (is_array($course_objectives["secondary_ids"])) {
									foreach ($course_objectives["secondary_ids"] as $objective_id) {
										echo "<input type=\"hidden\" class=\"secondary_objectives\" id=\"secondary_objective_".$objective_id."\" name=\"secondary_objectives[]\" value=\"".$objective_id."\" />\n";
									}
								}
								if (is_array($course_objectives["tertiary_ids"])) {
									foreach ($course_objectives["tertiary_ids"] as $objective_id) {
										echo "<input type=\"hidden\" class=\"tertiary_objectives\" id=\"tertiary_objective_".$objective_id."\" name=\"tertiary_objectives[]\" value=\"".$objective_id."\" />\n";
									}
								}
								?>
								<table style="width: 100%" cellspacing="0" cellpadding="2" border="0">
								<colgroup>
									<col width="3%" />
									<col width="22%" />
									<col width="75%" />
								</colgroup>
								<tbody>
									<tr>
										<td>&nbsp;</td>
										<td style="vertical-align: top">
											Clinical Presentations
											<div class="content-small" style="margin-top: 5px">
												<strong>Note:</strong> For more detailed information please refer to the <a href="http://www.mcc.ca/Objectives_online/objectives.pl?lang=english&loc=contents" target="_blank" style="font-size: 11px">MCC Objectives for the Qualifying Examination</a>.
											</div>
										</td>
										<td id="mandated_objectives_section">
											<select class="multi-picklist" id="PickList" name="clinical_presentations[]" multiple="multiple" size="5" style="width: 100%; margin-bottom: 5px">
											<?php
											if ((is_array($clinical_presentations)) && (count($clinical_presentations))) {
												foreach ($clinical_presentations as $objective_id => $presentation_name) {
													echo "<option value=\"".(int) $objective_id."\">".html_encode($presentation_name)."</option>\n";
												}
											}
											?>
											</select>
											<div style="float: left; display: inline">
												<input type="button" id="clinical_presentations_list_state_btn" class="button" value="Show List" onclick="toggle_list('clinical_presentations_list')" />
											</div>
											<div style="float: right; display: inline">
												<input type="button" id="clinical_presentations_list_remove_btn" class="button-remove" onclick="delIt()" value="Remove" />
												<input type="button" id="clinical_presentations_list_add_btn" class="button-add" onclick="addIt()" style="display: none" value="Add" />
											</div>
											<div id="clinical_presentations_list" style="clear: both; padding-top: 3px; display: none">
												<h2>Clinical Presentations List</h2>
												<select class="multi-picklist" id="SelectList" name="other_event_objectives_list" multiple="multiple" size="15" style="width: 100%">
												<?php
												if ((is_array($course_clinical_presentations)) && (count($course_clinical_presentations))) {	
													$ONLOAD[] = "$('clinical_presentations_list').style.display = 'none'";
													foreach ($course_clinical_presentations as $objective_id => $presentation_name) {
														if (!array_key_exists($objective_id, $clinical_presentations)) {
															echo "<option value=\"".(int) $objective_id."\">".html_encode($presentation_name)."</option>\n";
														}
													}
												}
												?>
												</select>
											</div>
											<input type="hidden" value="1" name="clinical_presentations_submit" />
											<script type="text/javascript">
												function movedObjective() {
													alert("overwritten");
												}
												
												if($('PickList')){
													$('PickList').observe('keypress', function(event) {
														if (event.keyCode == Event.KEY_DELETE) {
															delIt();
														}
													});
												}
												if($('SelectList')){
													$('SelectList').observe('keypress', function(event) {
														if (event.keyCode == Event.KEY_RETURN) {
															addIt();
														}
													});
												}
											</script>
										</td>
									</tr>
									<tr>
								<td colspan="3">&nbsp;</td>
							</tr><pre>
							<?php 
							if ((is_array($curriculum_objectives_list["used_ids"])) && (count($curriculum_objectives_list["used_ids"]))) { 
							?>
							<tr>
								<td></td>
								<td style="vertical-align: top;">
									<span class="form-nrequired">Curriculum Objectives</span>
									<div class="content-small" style="margin-top: 5px">
										<strong>Note:</strong> Please check any curriculum objectives that are covered during this learning event.
									</div>
									</td>
								<td style="vertical-align: top;">
									<div id="course-objectives-section">
										<strong>The learner will be able to:</strong>
										<?php echo event_objectives_in_list($curriculum_objectives_list, $top_level_id,$top_level_id, true, false, 1, false); ?>
									</div>
								</td>
							</tr>
							<?php } ?>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>			
								</tbody>
								</table>
							</div>
							<div style="padding-top: 25px">
								<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
									<tr>
										<td style="width: 25%; text-align: left">
											<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/gradebook?<?php echo replace_query(array("step" => false, "section" => "view", "assessment_id" => false)); ?>'" />
										</td>
										<td style="width: 75%; text-align: right; vertical-align: middle">
											<span class="content-small">After saving:</span>
											<select id="post_action" name="post_action">
												<option value="grade"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "grade") ? " selected=\"selected\"" : ""); ?>>Grade assessment</option>
												<option value="new"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "new") ? " selected=\"selected\"" : ""); ?>>Add another assessment</option>
												<option value="index"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "index") ? " selected=\"selected\"" : ""); ?>>Return to assessment list</option>
												<option value="parent"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "parent") ? " selected=\"selected\"" : ""); ?>>Return to all gradebooks list</option>
											</select>
											<input type="submit" class="button" value="Save" />
										</td>
									</tr>
								</table>
							</div>
						</form>
					<?php
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "In order to edit an assessment in a gradebook you must provide a valid course identifier. The provided ID does not exist in this system.";

				echo display_error();

				application_log("notice", "Failed to provide a valid course identifier when attempting to edit an assessment");
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "In order to edit an assessment in a gradebook you must provide a valid course identifier. The provided ID does not exist in this system.";

			echo display_error();

			application_log("notice", "Failed to provide course identifier when attempting to edit an assessment");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to edit an assessment in a gradebook you must provide a valid assessment identifier. The provided ID is invalid.";

		echo display_error();

		application_log("notice", "Failed to provide assessment identifier when attempting to edit an assessment");
	}
}
?>