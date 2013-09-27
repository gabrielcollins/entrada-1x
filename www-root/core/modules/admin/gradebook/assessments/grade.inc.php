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
	
	if (isset($_POST["ajax"]) && $_POST["ajax"] === "ajax") {
		
		ob_clear_open_buffers();
		
			$method = clean_input($_POST["method"], array("trim", "striptags"));

			switch ($method) {
				case "store-resubmit" :
					
					if (isset($_POST["aovalue_id"]) && $tmp_input = clean_input($_POST["aovalue_id"], "int")) {
						$PROCESSED["aovalue_id"] = $tmp_input;
						$MODE = "UPDATE";
						$WHERE = "`aovalue_id` = ".$db->qstr($tmp_input);
					} else {
						$MODE = "INSERT";
						$WHERE = "'1' = '1'";
					}
					if (isset($_POST["proxy_id"]) && $tmp_input = clean_input($_POST["proxy_id"], "int")) {
						$PROCESSED["proxy_id"] = $tmp_input;
					} else {
						add_error("Invalid proxy ID supplied.");
					}
					if (isset($_POST["aoption_id"]) && $tmp_input = clean_input($_POST["aoption_id"], "int")) {
						$PROCESSED["aoption_id"] = $tmp_input;
					} else {
						add_error("Invalid assessment option ID provided.");
					}
					if (isset($_POST["value"]) && $tmp_input = clean_input($_POST["value"], "int")) {
						$PROCESSED["value"] = clean_input($_POST["value"], "int");
					} else {
						$PROCESSED["value"] = 0;
					}
					
					if (!$ERROR) {
						if ($db->AutoExecute("assessment_option_values", $PROCESSED, $MODE, $WHERE)) {
							if (!isset($PROCESSED["aovalue_id"])) {
								$PROCESSED["aovalue_id"] = $db->Insert_ID();
							}
							echo json_encode(array("status" => "success", "data" => $PROCESSED));
						} else {
							echo json_encode(array("status" => "error", "data" => $ERRORSTR));
						}
					} else {
						echo json_encode(array("status" => "error", "data" => $ERRORSTR));
					}
					
				break;
				case "store-late" :
					
					if (isset($_POST["aovalue_id"]) && $tmp_input = clean_input($_POST["aovalue_id"], "int")) {
						$PROCESSED["aovalue_id"] = $tmp_input;
						$MODE = "UPDATE";
						$WHERE = "`aovalue_id` = ".$db->qstr($tmp_input);
					} else {
						$MODE = "INSERT";
						$WHERE = "'1' = '1'";
					}
					if (isset($_POST["proxy_id"]) && $tmp_input = clean_input($_POST["proxy_id"], "int")) {
						$PROCESSED["proxy_id"] = $tmp_input;
					} else {
						add_error("Invalid proxy ID supplied.");
					}
					if (isset($_POST["aoption_id"]) && $tmp_input = clean_input($_POST["aoption_id"], "int")) {
						$PROCESSED["aoption_id"] = $tmp_input;
					} else {
						add_error("Invalid assessment option ID provided.");
					}
					if (isset($_POST["value"]) && $_POST["value"] == "1") {
						$PROCESSED["value"] = 1;
					} else {
						$PROCESSED["value"] = 0;
					}
					
					if (!$ERROR) {
						if ($db->AutoExecute("assessment_option_values", $PROCESSED, $MODE, $WHERE)) {
							if (!isset($PROCESSED["aovalue_id"])) {
								$PROCESSED["aovalue_id"] = $db->Insert_ID();
							}
							echo json_encode(array("status" => "success", "data" => $PROCESSED));
						} else {
							echo json_encode(array("status" => "error", "data" => $ERRORSTR));
						}
					} else {
						echo json_encode(array("status" => "error", "data" => $ERRORSTR));
					}
					
				break;
			}
		
		exit;
	}
	
	/**
	 * Add PlotKit to the beginning of the $HEAD array.
	 */
	array_unshift($HEAD,
		"<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/MochiKit/MochiKit.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/PlotKit/excanvas.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/PlotKit/Base.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/PlotKit/Layout.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/PlotKit/Canvas.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/PlotKit/SweetCanvas.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/PlotKit/EasyPlot.js\"></script>"
		);
		
	if ($COURSE_ID) {
		$query			= "	SELECT * FROM `courses` 
							WHERE `course_id` = ".$db->qstr($COURSE_ID)."
							AND `course_active` = '1'";
		$course_details	= $db->GetRow($query);
		
		if ($course_details && $ENTRADA_ACL->amIAllowed(new GradebookResource($course_details["course_id"], $course_details["organisation_id"]), "read")) {
			$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("section" => "grade", "id" => $COURSE_ID, "step" => false)), "title" => "Grading Assessment");

			$query = "	SELECT a.*, b.`id` as `marking_scheme_id`, b.`handler`, b.`description` as `marking_scheme_description`, c.`type` as `assessment_type`
						FROM `assessments` AS a
						LEFT JOIN `assessment_marking_schemes` AS b
                        ON b.`id` = a.`marking_scheme_id`
						LEFT JOIN `assessments_lu_meta` AS c
                        ON c.`id` = a.`characteristic_id`
						WHERE a.`assessment_id` = ".$db->qstr($ASSESSMENT_ID);
			$assessment = $db->GetRow($query);
			if ($assessment) {
				$query = "SELECT `option_id`, `aoption_id` FROM `assessment_options` WHERE `assessment_id` = ".$db->qstr($ASSESSMENT_ID)." AND `option_active` = '1'";
				$assessment_options = $db->GetAssoc($query);
				
				$COHORT = $assessment["cohort"];
				
				$query = "	SELECT b.`id` AS `proxy_id`, CONCAT_WS(', ', b.`lastname`, b.`firstname`) AS `fullname`, b.`number`
							FROM `".AUTH_DATABASE."`.`user_data` AS b
							JOIN `".AUTH_DATABASE."`.`user_access` AS c
							ON c.`user_id` = b.`id` 
							AND c.`app_id` IN (".AUTH_APP_IDS_STRING.")
							AND c.`account_active` = 'true'
							AND (c.`access_starts` = '0' OR c.`access_starts`<=".$db->qstr(time()).")
							AND (c.`access_expires` = '0' OR c.`access_expires`>=".$db->qstr(time()).")
							JOIN `group_members` AS c
							ON b.`id` = c.`proxy_id`
							WHERE c.`group` = 'student'
							AND c.`group_id` = ".$db->qstr($COHORT)."
							AND c.`member_active` = '1' 
							ORDER BY b.`lastname` ASC, b.`firstname` ASC";
				$students = $db->GetAll($query);
				
				foreach ($students as $key => &$student) {
					$query = "SELECT `grade_id`, `value` AS `grade_value` FROM `assessment_grades`
								WHERE `proxy_id` = ".$db->qstr($student["proxy_id"])."
								AND `assessment_id` = ".$db->qstr($assessment["assessment_id"]);
					$grade = $db->GetRow($query);

					$student["grade_id"] = (isset($grade["grade_id"]) ? $grade["grade_id"] : NULL);
					$student["grade_value"] = (isset($grade["grade_value"]) ? $grade["grade_value"] : NULL);

					$query = "SELECT `grade_weighting` FROM `assessment_exceptions`
								WHERE `proxy_id` = ".$db->qstr($student["proxy_id"])."
								AND `assessment_id` = ".$db->qstr($assessment["assessment_id"]);
					$weight = $db->GetOne($query);

					$student["grade_weighting"] = (isset($weight) ? $weight : NULL);
				}
				
				courses_subnavigation($course_details,"gradebook");

				echo "<div class=\"content-small\">";
				if ($COURSE_ID) {
					$curriculum_path = curriculum_hierarchy($COURSE_ID);
					if ((is_array($curriculum_path)) && (count($curriculum_path))) {
						echo implode(" &gt; ", $curriculum_path);
					}
				} else {
					echo "No Associated Course";
				}
				echo "</div>\n";
				?>
				<style type="text/css">
				.sortableList li {
					width: 100%;
				}	
				</style>
				<h1 class="event-title"><?php echo $assessment["name"]; ?> (<?php echo groups_get_name($assessment["cohort"]); ?>)</h1>
				<?php if (!empty($assessment["description"])) { ?><p style="margin-top: 0;"><?php echo $assessment["description"]; ?></p><?php } ?>
				<div class="row-fluid">
					<div class="pull-right">
						<div class="btn-group">
							<a class="btn dropdown-toggle" data-toggle="dropdown" href="#"> Import / Export<span class="caret"></span></a>
							<ul class="dropdown-menu">
							<?php if ($assessment["assessment_type"] == "quiz" && !empty($attached_quizzes)) { ?>
								<li><a href="#" id="import-quiz-button">Import grades from attached Quiz</a></li>
							<?php } ?>
								<li><a href="#" id="import-csv-button">Import grades from CSV file</a></li>
								<li><a href="#" onclick="window.location='<?php echo ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("section" => "io", "download" => "csv", "assessment_ids" => $ASSESSMENT_ID)); ?>'; return false;">Export grades to CSV file</a></li>
							</ul>
						</div>
						<a href="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE . "/assessments/?" . replace_query(array("section" => "edit", "step" => false)); ?>" class="btn btn-success">Edit Assessment</a>
					</div>
				</div>
				<div class="row-fluid">
					<h2>Assessment Statistics</h2>
					<div id="graph" class="pull-right"></div>
					<p><strong class="span3">Assessment Type:</strong> <?php echo $assessment["type"]; ?> Assessment</p>
					<p><strong class="span3">Assessment Weighting:</strong> <?php echo $assessment["grade_weighting"]."%"; ?></p>
					<script type="text/javascript">
						var marking_scheme_id = "<?php echo $assessment["marking_scheme_id"]; ?>";
					</script>
					<?php 
					switch($assessment["marking_scheme_id"]) {
						case 1:
						case 4:
							//pass/fail
							$grades = array(0,0,0);
							$unentered = 0;
                            
                            foreach ($students as $key => &$student) {
								if ($student["grade_value"] == "") {
									$unentered++;
								} elseif ($student["grade_value"] > 50){
									$grades[0]++;
								} else {
									$grades[1]++;
								}
							}

							$grade_data = array();
							foreach ($grades as $key => $grade) {
								$grade_data[] = "[$key, $grade]";
							}
							?>
							<script type="text/javascript" charset="utf-8">
								var data = [<?php echo implode(", ", $grade_data); ?>];
								var plotter = PlotKit.EasyPlot(
									"pie",
									{
										"xTicks": [{v:0, label:"<?php echo $assessment["marking_scheme_id"] == 4 ? "Complete" : "Pass" ?>"},
													{v:1, label:"<?php echo $assessment["marking_scheme_id"] == 4 ? "Incomplete" : "Fail" ?>"}]
									},
									$("graph"),
									[data]
								);
							</script>
							<p><strong class="span3">Unentered grades:</strong> <?php echo (int) $unentered; ?></p>
							<?php
						break;
						case 2:
						case 3:
							// Percentage (numeric interpreted as percentage)
							$grades = array(0,0,0,0,0,0,0,0,0,0,0);

							$sum = 0;

							$entered = 0;
							$unentered = 0;

							$grade_values = array();
							foreach ($students as $key => &$student) {
								if (!isset($student["grade_value"]) || !$student["grade_value"]) {
									//$grades[11]++;
									$unentered++;
								} else {
									$sum += $student["grade_value"];
									$entered++;
									$grade_values[] = $student["grade_value"];

									$key = floor($student["grade_value"] / 10);
									$grades[$key]++;
								}
							}
                            
							$grade_data = array();
							foreach ($grades as $key => $grade) {
								$grade_data[] = "[$key, $grade]";
							}
							sort($grade_values);
							?>
							<script type="text/javascript" charset="utf-8">
								var data = [<?php echo implode(", ", $grade_data); ?>];
								var plotter = PlotKit.EasyPlot(
									"bar",
									{
										"xTicks": [ {v:0,  label:"0s"},
													{v:1,  label:"10s"},
													{v:2,  label:"20s"},
													{v:3,  label:"30s"},
													{v:4,  label:"40s"},
													{v:5,  label:"50s"},
													{v:6,  label:"60s"},
													{v:7,  label:"70s"},
													{v:8,  label:"80s"},
													{v:9,  label:"90s"},
													{v:10, label:"100"}]
									},
									$("graph"),
									[data]
								);
							</script>
							<p><strong class="span3">Unentered grades:</strong> <?php echo (int) $unentered; ?></p>
							<p><strong class="span3">Mean grade:</strong> <?php echo number_format(($entered > 0 ? $sum / $entered : 0), 0); ?>%</p>
							<p><strong class="span3">Median grade:</strong> <?php echo $grade_values[floor(count($grade_values) / 2)]; ?>%</p>
							<?php
						break;
						default:
							echo "No statistics for this marking scheme.";
						break;
					}
					?>
				</div>
				<?php if ($students && @count($students) >= 1) { ?>
						<div class="row-fluid">
							<?php

							if (isset($_POST["error_grades"]) && @count($_POST["error_grades"])) {
								$error_grades = $_POST["error_grades"];
								add_notice((count($_POST["error_grades"]) > 1 ? "Errors were encountered while importing the CSV.<br /><br /> Please manually update the grades of each of the <strong>".count($_POST["error_grades"])." highlighted students</strong>, otherwise those students' grades will be left as what they were prior to the CSV import process." : "An error was encountered while importing the CSV.<br /><br /> Please manually update the grade of the <strong>highlighted student</strong>, otherwise the grade for that student will be left as it was prior to the CSV import process."));
								echo display_notice();
							}

							$editable = $ENTRADA_ACL->amIAllowed(new GradebookResource($course_details["course_id"], $course_details["organisation_id"]), "update") ? "gradebook_editable" : "gradebook_not_editable";

								?>
								<div id="gradebook_grades" class="span12">
									<h2>Grades</h2>
									<div style="margin-bottom: 5px;">
										<span class="content-small"><strong>Tip: </strong><?php echo $assessment["marking_scheme_description"]; ?></span>
									</div>				
									<table class="gradebook single <?php echo $editable; ?>">
										<tbody>
											<?php
											foreach ($students as $key => &$student) {
												$query = "SELECT `aoption_id`, `value`, `aovalue_id` FROM `assessment_option_values` WHERE `proxy_id` = ".$db->qstr($student["proxy_id"])." AND `aoption_id` IN (".implode(",", $assessment_options).")";
												$option_values = $db->GetAssoc($query);

												if (isset($student["grade_id"])) {
													$grade_id = $student["grade_id"];
												} else {
													$grade_id = "";
												}

												if (isset($student["grade_value"])) {
													$grade_value = format_retrieved_grade($student["grade_value"], $assessment);
												} else {
													$grade_value = "-";
												}
												if (isset($student["grade_weighting"]) && $student["grade_weighting"]) {
													$grade_weighting = $student["grade_weighting"];
												} else {
													$grade_weighting = $assessment["grade_weighting"];
												}
												?>
												<tr id="grades<?php echo $student["proxy_id"]; ?>"<?php echo (isset($error_grades[$student["proxy_id"]]) ? " class=\"highlight\"" : "") ?>>
													<td><?php echo $student["fullname"]; ?></td>
													<td><?php echo $student["number"]; ?></td>
													<td>
														<span class="grade" id="grade_<?php echo $assessment["assessment_id"]; ?>_<?php echo $student["proxy_id"] ?>"
															data-grade-id="<?php echo $grade_id; ?>"
															data-assessment-id="<?php echo $assessment["assessment_id"]; ?>"
															data-proxy-id="<?php echo $student["proxy_id"] ?>"
															style="float:left;"
														><?php echo (!isset($error_grades[$student["proxy_id"]]) ? $grade_value : $error_grades[$student["proxy_id"]]); ?></span>
														<span class="gradesuffix" <?php echo (($grade_value === "-") ? "style=\"display: none;\"" : "") ?> style="float:left;">
															<?php echo assessment_suffix($assessment); ?>
														</span>
														<span class="gradesuffix" style="float:right;">
															<i class="icon-edit edit_grade" id ="edit_grade_<?php echo $assessment["assessment_id"]; ?>_<?php echo $student["proxy_id"] ?>"></i>
														</span>
													</td>
													<?php if ($assessment["marking_scheme_id"] == 3) { ?>
													<td id="percentage_<?php echo $assessment["assessment_id"]; ?>_<?php echo $student["proxy_id"] ?>"><?php echo round($student["grade_value"],2);?>%</td>
													<?php } ?>
													<?php if (array_key_exists("6", $assessment_options)) { ?>
													<td class="resubmissions">
														<span class="resubmit pull-left" data-id="<?php echo $assessment_options[6]; ?>" data-proxy-id="<?php echo $student["proxy_id"]; ?>" data-aovalue-id="<?php echo !empty($option_values[$assessment_options[6]]["aovalue_id"]) ? $option_values[$assessment_options[6]]["aovalue_id"] : "" ?>"><?php echo empty($option_values[$assessment_options[6]]["value"]) ? "-" : $option_values[$assessment_options[6]]["value"]; ?></span><i class="resubmit-button icon-edit pull-right"></i>                                                        
													</td>
													<?php } ?>
                                                    <?php if (array_key_exists("5", $assessment_options)) { ?>
													<td class="late-submissions" style="text-align: center; vertical-align: middle;">
														<input type="checkbox" data-id="<?php echo $assessment_options[5]; ?>" data-proxy-id="<?php echo $student["proxy_id"]; ?>" data-aovalue-id="<?php echo !empty($option_values[$assessment_options[5]]["aovalue_id"]) ? $option_values[$assessment_options[5]]["aovalue_id"] : "" ?>" <?php echo $option_values[$assessment_options[5]]["value"] == "1" ? "checked=\"checked\"" : ""; ?> />
													</td>
													<?php } ?>
												</tr>
												<?php
											}
											?>
										</tbody>
									</table>
								</div>
						</div>
                        <script type="text/javascript">
                            jQuery(document).ready(function(){
                                jQuery('.edit_grade').click(function(e){
                                    var id = e.target.id.substring(5);
                                    jQuery('#'+id).trigger('click');
                                });
                            });

                        </script>
						
						<div id="import-csv" style="display:none;">
							<h2>Import grades from CSV</h2>
							<div id="display-notice-box" class="display-notice">
								<ul>
								<li><strong>Important Notes:</strong>
									<br />Format for the CSV should be [Student Number, Grade] with each entry on a separate line (without the brackets). 
									<br />Any grades entered will be overwritten if present in the CSV.</li>
								</ul>
							</div>
							<form enctype="multipart/form-data" action="<?php echo ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("section" => "csv-upload", "assessment_id" => $ASSESSMENT_ID)); ?>" method="POST">
								<input type="file" name="file"/>
							</form>
						</div>

						<?php 
						if ($assessment["assessment_type"] == "quiz") { 
						   $query = "SELECT * FROM `assessment_attached_quizzes` 
										WHERE `assessment_id` = ".$db->qstr($ASSESSMENT_ID);
							$attached_quizzes = $db->GetAll($query);
							if ($attached_quizzes) {
								if (count($attached_quizzes) == 1) {
									$attached_quiz = $attached_quizzes[0];
								}
								?>
								<div id="import-quiz" style="display:none;">
									<h2>Import grades from attached quiz</h2>
									<div id="display-notice-box" class="display-notice">
										<ul>
										<li><strong>Important Notes:</strong><br />
											This will import the results for the attached questions from <?php echo (isset($attached_quiz) && $attached_quiz ? "the quiz <strong>". $attached_quiz["quiz_title"] ."</strong>" : "<strong>".count($attached_quizzes)."</strong> attached quizzes"); ?>. Any existing grades will be overwritten during this import process. Only students who have completed the quiz will be graded.</li>
										</ul>
									</div>
									<form action="<?php echo ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("section" => "import-quiz", "assessment_id" => $ASSESSMENT_ID)); ?>" method="POST" class="row-fluid">
										<label class="span4 form-required" for="import_type">Import action</label>
										<span class="span7 offset1">
											<select name="import_type" id="import_type">
												<option value="all">Average of all attempts</option>
												<option value="first">First attempt</option>
												<option value="last">Last attempt</option>
												<option value="best">Best (highest marked) attempt</option>
											</select>
										</span>
										<input type="hidden" name="course_id" value="<?php echo $assessment["course_id"]; ?>" />
										<input type="hidden" name="assessment_id" value="<?php echo $assessment["assessment_id"]; ?>" />
									</form>
								</div>
								<?php 
							}
						} ?>

						<script type="text/javascript">
						jQuery(function(){
							jQuery("#import-csv-button, #import-quiz-button").live("click", function(){
								if (jQuery(this).attr("id") == "import-quiz-button") {
									var target = jQuery("#import-quiz");
									var title = "Import Quiz";
									var height = 365;
								} else if (jQuery(this).attr("id") == "import-csv-button") {
									var target = jQuery("#import-csv");
									var title = "Import CSV";
									var height = 350;
								}
								var importResults = target.dialog({
									modal:		true,
									resizable:	false,
									draggable:	false,
									width: 500,
									height: height,
									title: title,
									buttons: [
										{
											text: "Ok",
											click: function() { 
												importResults.children("form").submit();
											}
										},
										{
											text: "Cancel",
											click: function() {
												importResults.dialog("close");
												importResults.dialog("destroy");
											}
										}
									]
								});
								return false;
							});
						});
						</script>
						
                        <div id="gradebook_stats" class="space-above">
							<h2>Grade Calculation Exceptions</h2>
							<p>You can use the following exception creator to modify the calculations used to create the students final grade in this course.</p>

							<label for="student_exceptions" class="form-required">Student Name</label>
							<select name="student_exceptions" id="student_exceptions" style="width: 210px;" onchange="add_exception(this.options[this.selectedIndex].value, '<?php echo $assessment["assessment_id"]; ?>')">
							<option value="0">-- Select A Student --</option>
								<?php
								foreach ($students as $student) {
									if (!isset($student["grade_weighting"]) || $student["grade_weighting"] == NULL) {
										echo "<option value=\"".$student["proxy_id"]."\">".$student["fullname"]."</option>";
									}
								}
								?>
							</select>
							
							<script type="text/javascript">
							var updating = false;
							function delete_exception (proxy_id, assessment_id) {

								var anOption = document.createElement('option');
								anOption.value = proxy_id;
								anOption.innerHTML = $(proxy_id+'_name').innerHTML;
								$('student_exceptions').appendChild(anOption);

								new Ajax.Updater('exception_container', '<?php echo ENTRADA_URL; ?>/api/assessment-weighting-exception.api.php', 
									{
										method:	'post',
										parameters: 'remove=1&assessment_id='+assessment_id+'&proxy_id='+proxy_id
									}
								);
							}

							function modify_exception (proxy_id, assessment_id) {
								if (!updating) {
									updating = true;
									setTimeout('modify_exception_ajax('+proxy_id+', '+assessment_id+')', 2000);
								}

							}

							function modify_exception_ajax(proxy_id, assessment_id) {
								var grade_weighting = $('student_exception_'+proxy_id).value;
								new Ajax.Updater('exception_container', '<?php echo ENTRADA_URL; ?>/api/assessment-weighting-exception.api.php', 
									{
										method:	'post',
										parameters: 'assessment_id='+assessment_id+'&proxy_id='+proxy_id+'&grade_weighting='+grade_weighting,
										onComplete: function () {
											$('student_exception_'+proxy_id).focus();
											updating = false;
										}
									}
								)
							}

							function add_exception (proxy_id, assessment_id) {
								new Ajax.Updater('exception_container', '<?php echo ENTRADA_URL; ?>/api/assessment-weighting-exception.api.php', 
									{
										method:	'post',
										parameters: 'assessment_id='+assessment_id+'&proxy_id='+proxy_id+'&grade_weighting=0'
									}
								);
								var children = $('student_exceptions').childNodes;
								var numchildren = children.length;

								for (var i = 0; i < numchildren; i++) {
									if (children[i].value == proxy_id) {
										$('student_exceptions').removeChild(children[i]);
										break;
									}
								}
							}
							</script>
							<h3>Students with modified weighting:</h3>
							<ol id="exception_container" class="sortableList">
								<?php
								$exceptions_exist = false;
								foreach ($students as $student) {
									if (isset($student["grade_weighting"]) && $student["grade_weighting"] !== NULL) {
										$exceptions_exist = true;
										echo "<li id=\"proxy_".$student["proxy_id"]."\"><span id=\"".$student["proxy_id"]."_name\">".$student["fullname"]."</span>
											<a style=\"cursor: pointer;\" onclick=\"delete_exception('".$student["proxy_id"]."', '".$assessment["assessment_id"]."');\" class=\"remove\">
												<img src=\"".ENTRADA_URL."/images/action-delete.gif\">
											</a>
											<span class=\"duration_segment_container\">
												Weighting: <input class=\"duration_segment\" id=\"student_exception_".$student["proxy_id"]."\" name=\"student_exception[]\" onkeyup=\"modify_exception('".$student["proxy_id"]."', '".$assessment["assessment_id"]."', this.value);\" value=\"".$student["grade_weighting"]."\">
											</span>
										</li>";
									}
								}
								if (!$exceptions_exist) {
									echo "<div class=\"display-notice\">There are currently no students with custom grade weighting in the system for this assessment.</div>";
								}
								?>
							</ol>
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="display-notice">There are no students in the system for this assessment's Cohort: <strong><?php echo groups_get_name($COHORT); ?></strong>.</div>
                        <?php
                    }
                    ?>
                </div>    
                <?php
			} else {
				$ERROR++;
				$ERRORSTR[] = "In order to edit an assessment's grades you must provide a valid assessment identifier.";

				echo display_error();

				application_log("notice", "Failed to provide a valid assessment identifier when attempting to edit an assessment's grades.");
			}

		} else {
			$ERROR++;
			$ERRORSTR[] = "You don't have permission to edit this gradebook.";

			echo display_error();

			application_log("error", "User tried to edit gradebook without permission.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to edit a course you must provide a valid course identifier.";

		echo display_error();

		application_log("notice", "Failed to provide course identifier when attempting to edit an assessment's grades.");
	}
}
?>