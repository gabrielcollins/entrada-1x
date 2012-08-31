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
 * This API file returns an HTML table of the possible targets for the selected
 * evaluation form. For instance, if the selected form is a course evaluation
 * it will return HTML used by the administrator to select which course / courses
 * they wish to evaluate.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 */

if (!defined("IN_EVALUATIONS")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("evaluation", "create", false)) {
	add_error("You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$GROUP."] and role [".$ROLE."] do not have access to this module [".$MODULE."]");
} else {
	if (isset($_POST["ajax"]) && ($_POST["ajax"] == 1)) {
		$use_ajax = true;
	} else {
		$use_ajax = false;
	}

	if ($use_ajax) {
		/**
		 * Clears all open buffers so we can return a plain response for the Javascript.
		 */
		ob_clear_open_buffers();

		$PROCESSED = array();
		$PROCESSED["eform_id"] = 0;

		if (isset($_POST["form_id"]) && ($tmp_input = clean_input($_POST["form_id"], "int"))) {
			$PROCESSED["eform_id"] = $tmp_input;
		}
	}

	if ($PROCESSED["eform_id"]) {
		$query = "	SELECT b.*
					FROM `evaluation_forms` AS a
					LEFT JOIN `evaluations_lu_targets` AS b
					ON b.`target_id` = a.`target_id`
					WHERE a.`form_active` = '1'
					AND b.`target_active` = '1'
					AND a.`eform_id` = ".$db->qstr($PROCESSED["eform_id"]);
		$target_details = $db->GetRow($query);
		if ($target_details) {
			switch ($target_details["target_shortname"]) {
				case "course" :
					$courses_list = array();

					$query = "	SELECT `course_id`, `organisation_id`, `course_code`, `course_name`
								FROM `courses`
								WHERE `organisation_id`=".$ENTRADA_USER->getActiveOrganisation()."
								AND `course_active` = '1'
								ORDER BY `course_code` ASC, `course_name` ASC";
					$results = $db->GetAll($query);
					if ($results) {
						foreach ($results as $result) {
							if ($ENTRADA_ACL->amIAllowed(new CourseResource($result["course_id"], $result["organisation_id"]), "read")) {
								$courses_list[$result["course_id"]] = ($result["course_code"]." - ".$result["course_name"]);
							}
						}
					}
					?>
					<tr>
						<td></td>
						<td style="vertical-align: top">
							<label for="PickList" class="form-required">Select Courses</label>
							<div class="content-small"><strong>Hint:</strong> Select the course or courses you would like to have evaluated.</div>
						</td>
						<td style="vertical-align: top">
							<select class="multi-picklist" id="PickList" name="course_ids[]" multiple="multiple" size="4" style="width: 100%; margin-bottom: 5px">
							<?php
							if ((is_array($PROCESSED["evaluation_targets"])) && (!empty($PROCESSED["evaluation_targets"]))) {
								foreach ($PROCESSED["evaluation_targets"] as $course_id) {
									echo "<option value=\"".(int) $course_id."\">".html_encode($courses_list[$course_id])."</option>\n";
								}
							}
							?>
							</select>
							<div style="float: left; display: inline">
								<input type="button" id="courses_list_state_btn" class="button" value="Show List" onclick="toggle_list('courses_list')" />
							</div>
							<div style="float: right; display: inline">
								<input type="button" id="courses_list_remove_btn" class="button-remove" onclick="delIt()" value="Remove" />
								<input type="button" id="courses_list_add_btn" class="button-add" onclick="addIt()" style="display: none" value="Add" />
							</div>
							<div id="courses_list" style="clear: both; padding-top: 3px; display: none">
								<h2>Course List</h2>
								<select class="multi-picklist" id="SelectList" name="other_courses_list" multiple="multiple" size="15" style="width: 100%">
								<?php
								foreach ($courses_list as $course_id => $course_name) {
									if (!in_array($course_id, $PROCESSED["evaluation_targets"])) {
										echo "<option value=\"".(int) $course_id."\">".html_encode($course_name)."</option>\n";
									}
								}
								?>
								</select>
							</div>
						</td>
					</tr>
					<?php
				break;
				case "teacher" :
					$teachers_list = array();

					$query = "	SELECT a.`id` AS `proxy_id`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`
								FROM `".AUTH_DATABASE."`.`user_data` AS a
								LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
								ON b.`user_id` = a.`id`
								LEFT JOIN `event_contacts` AS c
								ON c.`proxy_id` = a.`id`
								LEFT JOIN `events` AS d
								ON d.`event_id` = c.`event_id`
								WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
								AND (b.`group` = 'faculty' OR 
									(b.`group` = 'resident' AND b.`role` = 'lecturer')
								)
								AND d.`event_finish` >= ".$db->qstr(strtotime("-12 months"))."
								GROUP BY a.`id`
								ORDER BY a.`lastname` ASC, a.`firstname` ASC";
					$results = $db->GetAll($query);
					if ($results) {
						foreach ($results as $result) {
							$teachers_list[$result["proxy_id"]] = $result["fullname"];
						}
					}
					?>
					<tr>
						<td></td>
						<td style="vertical-align: top">
							<label for="PickList" class="form-required">Select Teachers</label>
							<div class="content-small"><strong>Hint:</strong> Select the teacher or teachers you would like to have evaluated.</div>
						</td>
						<td style="vertical-align: top">
							<select class="multi-picklist" id="PickList" name="teacher_ids[]" multiple="multiple" size="4" style="width: 100%; margin-bottom: 5px">
							<?php
							if ((is_array($PROCESSED["evaluation_targets"])) && (!empty($PROCESSED["evaluation_targets"]))) {
								foreach ($PROCESSED["evaluation_targets"] as $proxy_id) {
									echo "<option value=\"".(int) $proxy_id."\">".html_encode($teachers_list[$proxy_id])."</option>\n";
								}
							}
							?>
							</select>
							<div style="float: left; display: inline">
								<input type="button" id="teachers_list_state_btn" class="button" value="Show List" onclick="toggle_list('teachers_list')" />
							</div>
							<div style="float: right; display: inline">
								<input type="button" id="teachers_list_remove_btn" class="button-remove" onclick="delIt()" value="Remove" />
								<input type="button" id="teachers_list_add_btn" class="button-add" onclick="addIt()" style="display: none" value="Add" />
							</div>
							<div id="teachers_list" style="clear: both; padding-top: 3px; display: none">
								<h2>Course List</h2>
								<select class="multi-picklist" id="SelectList" name="other_teachers_list" multiple="multiple" size="15" style="width: 100%">
								<?php
								foreach ($teachers_list as $proxy_id => $teacher_name) {
									if (!in_array($proxy_id, $PROCESSED["evaluation_targets"])) {
										echo "<option value=\"".(int) $proxy_id."\">".html_encode($teacher_name)."</option>\n";
									}
								}
								?>
								</select>
							</div>
						</td>
					</tr>
					<?php
				break;
				case "student" :
				case "rotation_core" :
				case "rotation_elective" :
				case "preceptor" :
				case "peer" :
				case "self" :
					?>
					<tr>
						<td colspan="2">&nbsp;</td>
						<td>
							<?php echo display_notice("The target that you have selected is not currently available."); ?>
						</td>
					</tr>
					<?php
				break;
				default :
					application_log("error", "Unaccounted for target_shortname [".$target_details["target_shortname"]."] encountered. An update to api-targets.inc.php is required.");
				break;
			}

			/**
			 * This will eventually need to be moved up into the above switch, or brought into a class
			 * that should have been written for this.
			 */
			?>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<tr>
				<td></td>
				<td style="vertical-align: top">
					<label for="PickList" class="form-required">Select Students</label>
					<div class="content-small"><strong>Hint:</strong> Select the student or students you would like to evaluate the teachers above.</div>
				</td>
				<td style="vertical-align: top">
					<table style="width: 100%" cellspacing="0" cellpadding="0">
						<colgroup>
							<col style="width: 4%" />
							<col style="width: 96%" />
						</colgroup>
						<tbody>
							<tr>
								<td style="vertical-align: top"><input type="radio" name="target_group_type" id="target_group_type_cohort" value="cohort" onclick="selectTargetGroupOption(this.value)" style="vertical-align: middle" /></td>
								<td style="padding-bottom: 15px">
									<label for="target_group_type_cohort" class="radio-group-title">Entire class must complete this evaluation</label>
									<div class="content-small">This evaluation must be completed by everyone in the selected class.</div>
								</td>
							</tr>
							<tr class="target_group cohort_audience">
								<td></td>
								<td style="vertical-align: middle" class="content-small">
									<label for="cohort" class="form-required">All students in</label>
									<select id="cohort" name="cohort" style="width: 203px; vertical-align: middle">
										<?php
										$active_cohorts = groups_get_active_cohorts($ENTRADA_USER->getActiveOrganisation());
										if (isset($active_cohorts) && !empty($active_cohorts)) {
											foreach ($active_cohorts as $cohort) {
												echo "<option value=\"".$cohort["group_id"]."\"".((($PROCESSED["evaluation_evaluators"][0]["evaluator_type"] == "cohort") && ($PROCESSED["evaluation_evaluators"][0]["evaluator_value"] == $cohort["group_id"])) ? " selected=\"selected\"" : "").">".html_encode($cohort["group_name"])."</option>\n";
											}
										}
										?>
									</select>
								</td>
							</tr>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<tr>
								<td style="vertical-align: top"><input type="radio" name="target_group_type" id="target_group_type_percentage" value="percentage" onclick="selectTargetGroupOption(this.value)" style="vertical-align: middle" /></td>
								<td style="padding-bottom: 15px">
									<label for="target_group_type_percentage" class="radio-group-title">Percentage of class must complete this evaluation</label>
									<div class="content-small">This evaluation must be completed by certain percentage of students in the selected class.</div>
								</td>
							</tr>
							<tr class="target_group percentage_audience">
								<td>&nbsp;</td>
								<td style="vertical-align: middle" class="content-small">
									<input type="text" class="percentage" id="percentage_percent" name="percentage_percent" style="width: 30px; vertical-align: middle" maxlength="3" value="100" /> <label for="percentage_cohort" class="form-required">of the</label>
									<select id="percentage_cohort" name="percentage_cohort" style="width: 203px; vertical-align: middle">
									<?php
									$active_cohorts = groups_get_active_cohorts($ENTRADA_USER->getActiveOrganisation());
									if (isset($active_cohorts) && !empty($active_cohorts)) {
										foreach ($active_cohorts as $cohort) {
											echo "<option value=\"".$cohort["group_id"]."\">".html_encode($cohort["group_name"])."</option>\n";
										}
									}
									?>
									</select>
								</td>
							</tr>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<tr >
								<td style="vertical-align: top"><input type="radio" name="target_group_type" id="target_group_type_proxy_id" value="proxy_id" onclick="selectTargetGroupOption(this.value)" style="vertical-align: middle" /></td>
								<td style="padding-bottom: 15px">
									<label for="target_group_type_proxy_id" class="radio-group-title">Selected students must complete this evaluation</label>
									<div class="content-small">This evaluation must be completed only by the selected individuals.</div>
								</td>
							</tr>
							<tr class="target_group proxy_id_audience">
								<td>&nbsp;</td>
								<td style="vertical-align: middle" class="content-small">
									<label for="student_name" class="form-required">Student Name</label>

									<input type="text" id="student_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle" />
									<div class="autocomplete" id="student_name_auto_complete"></div>

									<input type="hidden" id="associated_student" name="associated_student" />
									<input type="button" class="button-sm" id="add_associated_student" value="Add" style="vertical-align: middle" />
									<span class="content-small" style="margin-left: 3px; padding-top: 5px"><strong>e.g.</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?></span>
									<ul id="student_list" class="menu" style="margin-top: 15px">
										<?php
										if (($PROCESSED["evaluation_evaluators"][0]["evaluator_type"] == "proxy_id") && is_array($PROCESSED["evaluation_evaluators"]) && !empty($PROCESSED["evaluation_evaluators"])) {
											foreach ($PROCESSED["evaluation_evaluators"] as $evaluator) {
												$proxy_id = (int) $evaluator["evaluator_value"];
												?>
												<li class="community" id="student_<?php echo $proxy_id; ?>" style="cursor: move;"><?php echo get_account_data("fullname", $proxy_id); ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="student_list.removeItem('<?php echo $proxy_id; ?>');" class="list-cancel-image" /></li>
												<?php
											}
										}
										?>
									</ul>
									<input type="hidden" id="student_ref" name="student_ref" value="" />
									<input type="hidden" id="student_id" name="student_id" value="" />
								</td>
							</tr>
						</tbody>
					</table>
					<script type="text/javascript" defer="defer">
					selectTargetGroupOption('<?php echo (isset($PROCESSED["evaluation_evaluators"][0]["evaluator_type"]) ? $PROCESSED["evaluation_evaluators"][0]["evaluator_type"] : 'cohort'); ?>');
					student_list = new AutoCompleteList({ type: 'student', url: '<?php echo ENTRADA_RELATIVE; ?>/api/personnel.api.php?type=student', remove_image: '<?php echo ENTRADA_RELATIVE; ?>/images/action-delete.gif' });
					</script>
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * If we are return this via Javascript,
	 * exit now so we don't get the entire page.
	 */
	if ($use_ajax) {
		exit;
	}
}