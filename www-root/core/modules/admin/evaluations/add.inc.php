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
 * This file is used to create new evaluations in the entrada.evaluations table.
 *
 * @author Organisation: University of Calgary
 * @author Unit: School of Medicine
 * @author Developer: Howard Lu <yhlu@ucalgary.ca>
 * @copyright Copyright 2010 University of Calgary. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVALUATIONS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("evaluation", "create", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/evaluations?section=add", "title" => "Create Evaluation");

	echo "<h1>Create Evaluation</h1>\n";

	$PROCESSED["evaluation_evaluators"] = array();
	$PROCESSED["evaluation_targets"] = array();

	// Error Checking
	switch($STEP) {
		case 2 :
			/**
			 * Processing for evaluations table.
			 */

			/**
			 * Required field "evaluation_title" / Evaluation Title.
			 */
			if ((isset($_POST["evaluation_title"])) && ($evaluation_title = clean_input($_POST["evaluation_title"], array("notags", "trim")))) {
				$PROCESSED["evaluation_title"] = $evaluation_title;
			} else {
				add_error("The <strong>Evaluation Title</strong> field is required.");
			}

			/**
			 * Non-required field "evaluation_description" / Special Instructions.
			 */
			if ((isset($_POST["evaluation_description"])) && ($evaluation_description = clean_input($_POST["evaluation_description"], array("notags", "trim")))) {
				$PROCESSED["evaluation_description"] = $evaluation_description;
			} else {
				$PROCESSED["evaluation_description"] = "";
			}

			$evaluation_target_id = 0;
			$evaluation_target_type = "";

			/**
			 * Required field "eform_id" / Evaluation Form
			 */
			if (isset($_POST["eform_id"]) && ($eform_id = clean_input($_POST["eform_id"], "int"))) {
				$query = "	SELECT a.*, b.`target_id`, b.`target_shortname`
							FROM `evaluation_forms` AS a
							LEFT JOIN `evaluations_lu_targets` AS b
							ON b.`target_id` = a.`target_id`
							WHERE a.`eform_id` = ".$db->qstr($eform_id)."
							AND a.`form_active` = '1'";
				$result = $db->GetRow($query);
				if ($result) {
					$evaluation_target_id = $result["target_id"];
					$evaluation_target_type = $result["target_shortname"];

					$PROCESSED["eform_id"] = $eform_id;
				} else {
					add_error("The <strong>Evaluation Form</strong> that you selected is not currently available for use.");
				}
			} else {
				add_error("You must select an <strong>Evaluation Form</strong> to use during this evaluation.");
			}

			/**
			 * Non-required field "release_date" / Viewable Start (validated through validate_calendars function).
			 * Non-required field "release_until" / Viewable Finish (validated through validate_calendars function).
			 */
			$viewable_date = validate_calendars("evaluation", false, false);
			if ((isset($viewable_date["start"])) && ((int) $viewable_date["start"])) {
				$PROCESSED["evaluation_start"] = (int) $viewable_date["start"];
			} else {
				$PROCESSED["evaluation_start"] = 0;
			}
			if ((isset($viewable_date["finish"])) && ((int) $viewable_date["finish"])) {
				$PROCESSED["evaluation_finish"] = (int) $viewable_date["finish"];
			} else {
				$PROCESSED["evaluation_finish"] = 0;
			}

			/**
			 * Required field "min_submittable" / Min Submittable
			 */
			if (isset($_POST["min_submittable"]) && ($min_submittable = clean_input($_POST["min_submittable"], "int")) && ($min_submittable >= 1)) {
				$PROCESSED["min_submittable"] = $min_submittable;
			} else {
				add_error("The evaluation <strong>Min Submittable</strong> field is required and must be greater than 1.");
			}

			/**
			 * Required field "max_submittable" / Max Submittable
			 */
			if (isset($_POST["max_submittable"]) && ($max_submittable = clean_input($_POST["max_submittable"], "int")) && ($max_submittable <= 99)) {
				$PROCESSED["max_submittable"] = $max_submittable;
			} else {
				add_error("The evaluation <strong>Max Submittable</strong> field is required and must be less than 99.");
			}

			if ($PROCESSED["min_submittable"] > $PROCESSED["max_submittable"]) {
				add_error("Your <strong>Min Submittable</strong> value may not be greater than your <strong>Max Submittable</strong> value.");
			}

			/**
			 * Required field "max_submittable" / Max Submittable
			 */
			if (isset($_POST["max_submittable"]) && ($max_submittable = clean_input($_POST["max_submittable"], "int")) && ($max_submittable <= 99)) {
				$PROCESSED["max_submittable"] = $max_submittable;
			} else {
				add_error("The evaluation <strong>Max Submittable</strong> field is required and must be less than 99.");
			}

			/**
			 * Non-required field "release_date" / Viewable Start (validated through validate_calendars function).
			 * Non-required field "release_until" / Viewable Finish (validated through validate_calendars function).
			 */
			$viewable_date = validate_calendars("viewable", false, false);
			if ((isset($viewable_date["start"])) && ((int) $viewable_date["start"])) {
				$PROCESSED["release_date"] = (int) $viewable_date["start"];
			} else {
				$PROCESSED["release_date"] = 0;
			}
			if ((isset($viewable_date["finish"])) && ((int) $viewable_date["finish"])) {
				$PROCESSED["release_until"] = (int) $viewable_date["finish"];
			} else {
				$PROCESSED["release_until"] = 0;
			}

			/**
			 * Processing for evaluation_targets table.
			 */
			switch ($evaluation_target_type) {
				case "course" :
					if (isset($_POST["course_ids"]) && is_array($_POST["course_ids"]) && !empty($_POST["course_ids"])) {
						foreach ($_POST["course_ids"] as $course_id) {
							$course_id = clean_input($course_id, "int");
							if ($course_id) {
								$query = "SELECT `course_id` FROM `courses` WHERE `course_id` = ".$db->qstr($course_id);
								$result = $db->GetRow($query);
								if ($result) {
									$PROCESSED["evaluation_targets"][] = $result["course_id"];
								}
							}
						}

						if (empty($PROCESSED["evaluation_targets"])) {
							add_error("You must select at least one <strong>course</strong> that you would like to have evaluated.");
						}
					} else {
						add_error("You must select <strong>which courses</strong> you would like to have evaluated.");
					}
				break;
				case "teacher" :
					if (isset($_POST["teacher_ids"]) && is_array($_POST["teacher_ids"]) && !empty($_POST["teacher_ids"])) {
						foreach ($_POST["teacher_ids"] as $proxy_id) {
							$proxy_id = clean_input($proxy_id, "int");
							if ($proxy_id) {
								$query = "	SELECT a.`id` AS `proxy_id`
											FROM `".AUTH_DATABASE."`.`user_data` AS a
											LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
											ON b.`user_id` = a.`id`
											WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
											AND (b.`group` = 'faculty' OR 
												(b.`group` = 'resident' AND b.`role` = 'lecturer')
											)
											AND a.`id` = ".$db->qstr($proxy_id);
								$result = $db->GetRow($query);
								if ($result) {
									$PROCESSED["evaluation_targets"][] = $result["proxy_id"];
								}
							}
						}

						if (empty($PROCESSED["evaluation_targets"])) {
							add_error("You must select at least one <strong>teacher</strong> that you would like to have evaluated.");
						}
					} else {
						add_error("You must select <strong>which teachers</strong> you would like to have evaluated.");
					}
				break;
				default :
					add_error("The form type you have selected is currently unavailable. The system administrator has been notified of this issue, please try again later.");

					application_log("error", "Unaccounted for target_shortname [".$evaluation_target_type."] encountered. An update to add.inc.php is required.");
				break;
			}

			/**
			 * Processing for evaluation_evaluators table.
			 */
			if (isset($_POST["target_group_type"]) && in_array($_POST["target_group_type"], array("cohort", "percentage", "proxy_id"))) {
				switch ($_POST["target_group_type"]) {
					case "cohort" :
						if (isset($_POST["cohort"]) && ($cohort = clean_input($_POST["cohort"], array("trim", "int")))) {
							$PROCESSED["evaluation_evaluators"][] = array("evaluator_type" => "cohort", "evaluator_value" => $cohort);
						} else {
							add_error("Please provide a valid cohort to complete this evaluation.");
						}
					break;
					case "percentage" :
						if (isset($_POST["percentage_cohort"]) && ($cohort = clean_input($_POST["percentage_cohort"], array("alphanumeric")))) {
							$percentage = clean_input($_POST["percentage_percent"], "int");
							if (($percentage >= 100) || ($percentage < 1)) {
								$percentage = 100;

								$PROCESSED["evaluation_evaluators"][] = array("evaluator_type" => "cohort", "evaluator_value" => $cohort);
							} else {
								$query = "	SELECT a.`id` AS `proxy_id`
											FROM `".AUTH_DATABASE."`.`user_data` AS a
											JOIN `".AUTH_DATABASE."`.`user_access` AS b
											ON b.`user_id` = a.`id`
											JOIN `group_members` AS c
											ON a.`id` = c.`proxy_id`
											WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
											AND b.`account_active` = 'true'
											AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
											AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
											AND b.`group` = 'student'
											AND c.`group_id` = ".$db->qstr($cohort);
								$results = $db->GetAll($query);
								if ($results) {
									$total_students = count($results);
								}

								$percentage = round($total_students * $percentage / 100);

								$query .= "	ORDER BY RAND()
											LIMIT 0, ".$percentage;

								$results = $db->GetAll($query);
								if ($results) {
									foreach ($results as $result) {
										$PROCESSED["evaluation_evaluators"][] = array("evaluator_type" => "proxy_id", "evaluator_value" => $result["proxy_id"]);
									}
								}
							}
						} else {
							add_error("Please provide a valid class to complete this evaluation.");
						}
					break;
					case "proxy_id" :
							if ((isset($_POST["associated_student"]))) {
								$evaluator_values = array();

								$associated_student = explode(",", $_POST["associated_student"]);

								if (is_array($associated_student) && !empty($associated_student)) {
									foreach($associated_student as $proxy_id) {
										$proxy_id = clean_input($proxy_id, "int");

										if ($proxy_id) {
											$evaluator_values[] = $proxy_id;
										}
									}
								}
								
								if (!empty($evaluator_values)) {
									$evaluator_values = array_unique($evaluator_values);
									
									$query = "	SELECT a.`id` AS `proxy_id`
												FROM `".AUTH_DATABASE."`.`user_data` AS a
												LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
												ON b.`user_id` = a.`id`
												WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
												AND b.`account_active` = 'true'
												AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
												AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
												AND a.`id` IN (".implode(", ", $evaluator_values).")";
									$results = $db->GetAll($query);
									if ($results) {
										foreach ($results as $result) {
											$PROCESSED["evaluation_evaluators"][] = array("evaluator_type" => "proxy_id", "evaluator_value" => $result["proxy_id"]);
										}
									}
								}
							} else {
								add_error("You must select at least one individual to act as an evaluator.");
							}
					break;
				}

				if (empty($PROCESSED["evaluation_evaluators"])) {
					add_error("Please select an appropriate type of evaluator (i.e. entire class, percentage, etc).");
				}
			} else {
				add_error("Please select an appropriate type of evaluator (i.e. entire class, percentage, etc).");
			}

			if (!$ERROR) {
				$PROCESSED["evaluation_active"] = 1;
				$PROCESSED["updated_date"] = time();
				$PROCESSED["updated_by"] = $_SESSION["details"]["id"];

				/**
				 * Insert the evaluation record into the evalutions table.
				 */
				if ($db->AutoExecute("evaluations", $PROCESSED, "INSERT") && ($evaluation_id = $db->Insert_Id())) {
					/**
					 * Insert the target records into the evaluation_targets table.
					 */
					if (!empty($PROCESSED["evaluation_targets"])) {
						foreach ($PROCESSED["evaluation_targets"] as $target_value) {
							$record = array(
								"evaluation_id" => $evaluation_id,
								"target_id" => $evaluation_target_id,
								"target_value" => $target_value,
								"target_active" => 1,
								"updated_date" => time(),
								"updated_by" => $_SESSION["details"]["id"]
							);

							if (!$db->AutoExecute("evaluation_targets", $record, "INSERT") || (!$etarget_id = $db->Insert_Id())) {
								add_error("Unable to attach an evaluation target to this evaluation. The system administrator has been notified of this error, please try again later.");
								application_log("Unable to attach target_id [".$evaluation_target_id."] / target_value [".$target_value."] to evaluation_id [".$evaluation_id."]. Database said: ".$db->ErrorMsg());
							}
						}
					}

					/**
					 * Insert the target records into the evaluation_targets table.
					 */
					if (!empty($PROCESSED["evaluation_evaluators"])) {
						foreach ($PROCESSED["evaluation_evaluators"] as $result) {
							$record = array(
								"evaluation_id" => $evaluation_id,
								"evaluator_type" => $result["evaluator_type"],
								"evaluator_value" => $result["evaluator_value"],
								"updated_date" => time(),
								"updated_by" => $_SESSION["details"]["id"]
							);

							if (!$db->AutoExecute("evaluation_evaluators", $record, "INSERT") || (!$eevaluator_id = $db->Insert_Id())) {
								add_error("Unable to attach an evaluation target to this evaluation. The system administrator has been notified of this error, please try again later.");
								application_log("Unable to attach target_id [".$evaluation_target_id."] / target_value [".$target_value."] to evaluation_id [".$evaluation_id."]. Database said: ".$db->ErrorMsg());
							}
						}
					}

					$url = ENTRADA_URL."/admin/evaluations";
					$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";
					add_success("You have successfully added <strong>".html_encode($PROCESSED["evaluation_title"])."</strong> to the system.<br /><br />You will now be redirected to the evaluation index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.");

					application_log("success", "New evaluation [".$evaluation_id."] added to the system.");
				} else {
					add_error("Unable to create new evaluation record at this time. The system administrator has been notified of this error, please try again later.");

					application_log("Unable to create new evaluation record. Database said: ".$db->ErrorMsg());
				}

				/**
				 * If there are errors, remove the already inserted records.
				 */
				if ($ERROR && $evaluation_id) {
					$db->Execute("DELETE FROM `evaluations` WHERE `evaluation_id` = ".$db->qstr($evaluation_id));
					$db->Execute("DELETE FROM `evaluation_targets` WHERE `evaluation_id` = ".$db->qstr($evaluation_id));
					$db->Execute("DELETE FROM `evaluation_evaluators` WHERE `evaluation_id` = ".$db->qstr($evaluation_id));
				}
			}

			if ($ERROR) {
				$STEP = 1;
			}
		break;
		case 1 :
		default :
			continue;
		break;
	}

	// Display Content
	switch($STEP) {
		case 2 :
			display_status_messages();
		break;
		case 1 :
		default :
			$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
			$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/picklist.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";

			if (has_error() || has_notice()) {
				echo display_status_messages();
			}
			?>
			<script type="text/javascript">
			function updateFormOptions() {
				if ($F('eform_id') > 0)  {

					var currentLabel = $('eform_id').options[$('eform_id').selectedIndex].up().readAttribute('label');

					if (currentLabel != selectedFormType) {
						selectedFormType = currentLabel;

						$('evaluation_options').show();
						$('evaluation_options').update('<tr><td colspan="2">&nbsp;</td><td><div class="content-small" style="vertical-align: middle"><img src="<?php echo ENTRADA_RELATIVE; ?>/images/indicator.gif" width="16" height="16" alt="Please Wait" title="" style="vertical-align: middle" /> Please wait while <strong>evaluation options</strong> are loaded ... </div></td></tr>');

						new Ajax.Updater('evaluation_options', '<?php echo ENTRADA_RELATIVE; ?>/admin/evaluations?section=api-form-options', {
							evalScripts : true,
							parameters : {
								ajax : 1,
								form_id : $F('eform_id')
							},
							onSuccess : function (response) {
								if (response.responseText == "") {
									$('evaluation_options').update('');
									$('evaluation_options').hide();
								}
							},
							onFailure : function (response) {
								$('evaluation_options').update('');
								$('evaluation_options').hide();
							}
						});
					}
				} else {
					$('evaluation_options').update('');
					$('evaluation_options').hide();
				}
			}

			function selectTargetGroupOption(type) {
				$$('input[type=radio][value=' + type + ']').each(function(el) {
					$(el.id).checked = true;
				});

				$$('.target_group').invoke('hide');
				$$('.' + type + '_audience').invoke('show');
			}
			</script>
			<form action="<?php echo ENTRADA_URL; ?>/admin/evaluations?section=add&amp;step=2" method="post" name="addEvaluationForm" id="addEvaluationForm">
				<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Creating An Evaluation">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 20%" />
						<col style="width: 77%" />
					</colgroup>
					<tfoot>
						<tr>
							<td colspan="3" style="padding-top: 50px">
								<input type="button" class="fleft" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/evaluations'" />
								<input type="submit" class="fright" value="Proceed" />
								<div class="clear"></div>
							</td>
						</tr>
					</tfoot>
					<tbody>
						<tr>
							<td colspan="3"><h2>Evaluation Details</h2></td>
						</tr>
						<tr>
							<td></td>
							<td><label for="evaluation_title" class="form-required">Evaluation Title</label></td>
							<td><input type="text" id="evaluation_title" name="evaluation_title" value="<?php echo html_encode($PROCESSED["evaluation_title"]); ?>" maxlength="255" style="width: 95%" /></td>
						</tr>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr>
							<td></td>
							<td style="vertical-align: top">
								<label for="evaluation_description" class="form-nrequired">Special Instructions</label>
								<div class="content-small" style="margin-right:3px"><strong>Note:</strong> Special instructions will appear at the top of the evaluation form.</div>
							</td>
							<td>
								<textarea id="evaluation_description" name="evaluation_description" class="expandable" style="width: 94%; height:50px" cols="50" rows="15"><?php echo html_encode($PROCESSED["evaluation_description"]); ?></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="3"><h2>Evaluation Form Options</h2></td>
						</tr>
						<tr>
							<td></td>
							<td><label for="eform_id" class="form-required">Evaluation Form</label></td>
							<td>
								<select id="eform_id" name="eform_id" style="width:328px">
								<option value="0"> -- Select Evaluation Form -- </option>
								<?php
								$query	= "	SELECT a.*, b.`target_shortname`, b.`target_title`
											FROM `evaluation_forms` AS a
											LEFT JOIN `evaluations_lu_targets` AS b
											ON b.`target_id` = a.`target_id`
											WHERE a.`form_active` = '1'
											ORDER BY b.`target_title` ASC";
								$results = $db->GetAll($query);
								if ($results) {
									$total_forms = count($results);
									$optgroup_label = "";

									foreach ($results as $key => $result) {
										if ($result["target_title"] != $optgroup_label) {
											$optgroup_label = $result["target_title"];
											if ($key > 0) {
												echo "</optgroup>";
											}
											echo "<optgroup label=\"".html_encode($optgroup_label)." Forms\">";
										}
										echo "<option value=\"".(int) $result["eform_id"]."\"".(($PROCESSED["eform_id"] == $result["eform_id"]) ? " selected=\"selected\"" : "")."> ".html_encode($result["form_title"])."</option>";
									}
									echo "</optgroup>";
								}
								?>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
					</tbody>
					<tbody id="evaluation_options"<?php echo ((!$PROCESSED["eform_id"]) ? " style=\"display: none\"" : ""); ?>>
					<?php
					if ($PROCESSED["eform_id"]) {
						require_once(ENTRADA_ABSOLUTE."/core/modules/admin/evaluations/api-form-options.inc.php");
					}
					?>
					</tbody>
					<tbody>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr>
							<td></td>
							<td><label for="min_submittable" class="form-required">Min Submittable</label></td>
							<td>
								<input type="text" id="min_submittable" name="min_submittable" value="<?php echo (isset($PROCESSED["min_submittable"]) ? $PROCESSED["min_submittable"] : 1); ?>" maxlength="2" style="width: 30px; margin-right: 10px" />
								<span class="content-small"><strong>Tip:</strong> The minimum number of times each evaluator must complete this evaluation.</span>
							</td>
						</tr>
						<tr>
							<td></td>
							<td><label for="max_submittable" class="form-required">Max Submittable</label></td>
							<td>
								<input type="text" id="max_submittable" name="max_submittable" value="<?php echo (isset($PROCESSED["max_submittable"]) ? $PROCESSED["max_submittable"] : 1); ?>" maxlength="2" style="width: 30px; margin-right: 10px" />
								<span class="content-small"><strong>Tip:</strong> The maximum number of times each evaluator may complete this evaluation.</span>
							</td>
						</tr>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<?php echo generate_calendars("evaluation", "Evaluation", true, true, ((isset($PROCESSED["evaluation_start"])) ? $PROCESSED["evaluation_start"] : 0), true, true, ((isset($PROCESSED["evaluation_finish"])) ? $PROCESSED["evaluation_finish"] : 0)); ?>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="3"><h2>Time Release Options</h2></td>
						</tr>
						<?php echo generate_calendars("viewable", "", true, false, ((isset($PROCESSED["release_date"])) ? $PROCESSED["release_date"] : 0), true, false, ((isset($PROCESSED["release_until"])) ? $PROCESSED["release_until"] : 0)); ?>
					</tbody>
				</table>
			</form>
			<script type="text/javascript">
			document.observe("dom:loaded", function() {
				$('eform_id').observe('change', function() {
					updateFormOptions();
				});

				$('addEvaluationForm').observe('submit', function() {
					selIt();
				});

				selectedFormType = (($('eform_id') && $('eform_id').selectedIndex) ? $('eform_id').options[$('eform_id').selectedIndex].up().readAttribute('label') : '');
			});
			</script>
			<?php
		break;
	}
}
