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
 * @author Developer: Brandon Thorn <brandon.thorn@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
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

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
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

			$query = "	SELECT `assignments`.*,	`assessments`.`cohort`, `assessment_marking_schemes`.`id` AS `marking_scheme_id`, `assessment_marking_schemes`.`handler`, `assessment_marking_schemes`.`description` as `marking_scheme_description`
						FROM `assignments`
						LEFT JOIN `assessments`
						ON `assignments`.`assessment_id` = `assessments`.`assessment_id`
						LEFT JOIN `assessment_marking_schemes` ON `assessment_marking_schemes`.`id` = `assessments`.`marking_scheme_id`
						WHERE `assignments`.`assignment_id` = ".$db->qstr($ASSIGNMENT_ID);
			$assignment = $db->GetRow($query);
			if ($assignment) {				
				$COHORT = $assignment["cohort"];
				$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("section" => "grade", "id" => $COURSE_ID, "step" => false)), "title" => $assignment["assignment_title"]);
				courses_subnavigation($course_details);

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
				<div>
					<h1 class="event-title"><?php echo $assignment["assignment_title"]; ?></h1>
				</div>
				<?php
//				$query = "	SELECT b.`id` AS `proxy_id`, CONCAT_WS(', ', b.`lastname`, b.`firstname`) AS `fullname`, b.`number`, h.`grade_id` AS `grade_id`, h.`value` AS `grade_value`, i.`grade_weighting`, d.`updated_date` AS `submitted_date`
//							FROM `entrada_auth`.`user_data` AS b
//							JOIN `entrada_auth`.`user_access` AS c
//							ON c.`user_id` = b.`id` 
//							AND c.`app_id`= ".$db->qstr(AUTH_APP_ID)."
//							AND c.`account_active` = 'true'
//							AND (c.`access_starts` = '0' OR c.`access_starts`<= '1330627003')
//							AND (c.`access_expires` = '0' OR c.`access_expires`>= '1330627003')
//							JOIN `assignment_files` AS d
//							ON d.`proxy_id` = b.`id`
//							JOIN `assignments` AS e
//							ON d.`assignment_id` = e.`assignment_id`
//							AND d.`assignment_id` = ".$db->qstr($ASSIGNMENT_ID)."
//							JOIN `assessments` AS f
//							ON e.`assessment_id` = f.`assessment_id`
//							LEFT JOIN `group_members` AS g
//							ON g.`group_id` = f.`cohort`
//							AND g.`proxy_id` = b.`id`
//							LEFT JOIN `entrada`.`assessment_grades` AS h 
//							ON b.`id` = h.`proxy_id` 
//							AND h.`assessment_id` = ".$db->qstr($assignment["assessment_id"])."
//							LEFT JOIN `assessment_exceptions` AS i
//							ON b.`id` = i.`proxy_id`
//							AND h.`assessment_id` = i.`assessment_id`
//							ORDER BY b.`lastname` ASC, b.`firstname` ASC";
				$query = "	SELECT a.id AS `proxy_id`, CONCAT_WS(', ',a.`lastname`,a.`firstname`) AS `fullname`, c.`assessment_id`, a.`updated_date` AS `submitted_date`, b.`afile_id`, d.`grade_id`, d.`value` AS `grade_value`, f.`handler`, g.`grade_weighting`
							FROM `".AUTH_DATABASE."`.`user_data` AS a
							JOIN `assignment_files` AS b
							ON a.`id` = b.`proxy_id`
							AND b.`file_type` = 'submission'
							AND b.`assignment_id` = ".$db->qstr($ASSIGNMENT_ID)."
							JOIN `assignments` AS c
							ON b.`assignment_id` = c.`assignment_id`
							LEFT JOIN `assessment_grades` AS d 
							ON c.`assessment_id` = d.`assessment_id`
							AND d.`proxy_id` = a.`id`
							LEFT JOIN `assessments` AS e
							ON d.`assessment_id` = e.`assessment_id`
							LEFT JOIN `assessment_marking_schemes` AS f
							ON e.`marking_scheme_id` = f.`id`
							LEFT JOIN `assessment_exceptions` AS g
							ON g.`assessment_id` = d.`assessment_id`
							AND g.`proxy_id` = a.`id`";				
				$students = $db->GetAll($query);
												
				$query = "	SELECT * FROM `assessments` AS a
							JOIN `assessment_marking_schemes` AS b
							ON a.`marking_scheme_id` = b.`id`
							WHERE a.`assessment_id` = ".$db->qstr($assignment["assessment_id"]);
				$assessment = $db->GetRow($query);
				?>
				<div style="float: right; text-align: right; width:400px;">
					<ul class="page-action">
						<li><a href="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE . "/assignments/?" . replace_query(array("section" => "edit","assignment_id"=>$assignment["assignment_id"], "step" => false)); ?>" class="strong-green">Edit Assignment</a></li>
						<?php if($assignment["assessment_id"]){ ?>
						<li><a href="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE . "/assessments/?" . replace_query(array("section" => "edit","assessment_id"=>$assignment["assessment_id"], "step" => false)); ?>" class="strong-green">Edit Assessment</a></li>
						<?php } ?>
						<?php if (isset($assessment) && $assessment){ ?><li><a href="#" id="advanced-options" class="strong-green">Show Options</a></li><?php } ?>
					</ul>
				</div>
				<div style="clear: both;"></div>
				<?php
				$editable = $ENTRADA_ACL->amIAllowed(new GradebookResource($course_details["course_id"], $course_details["organisation_id"]), "update") ? "gradebook_editable" : "gradebook_not_editable";
				if ($students && count($students) >= 1): ?>
					<span id="assessment_name" style="display: none;"><?php echo $assignment["assignment_title"]; ?></span>
					<div id="assignment_submissions">
						<h2>Submissions <?php if(extension_loaded('zip')) { ?><a href="<?php echo ENTRADA_URL;?>/admin/gradebook/assignments?section=download-submissions&id=<?php echo $assignment["assignment_id"];?>"><span style="float:right;"><img src="<?php echo ENTRADA_URL;?>/templates/default/images/btn_save.gif" title="Download File" alt="Download File" width="15"/> Download All Submissions</span></a><?php } ?></h2>
						<div style="margin-bottom: 5px;">							
							<span class="content-small"><strong>Tip: </strong><?php echo $assignment["marking_scheme_description"]; ?></span>
						</div>				
						<table style="width: 100%;" class="tableList gradebook assignment <?php echo $editable; ?>">
							<colgroup>
								<col class="modified" style="width: 5%;">
								<col class="title" style="width: 45%;">
								<col class="grade" style="width: 20%;">								
								<col class="date" style="width: 30%;">
							</colgroup>
							<thead>
								<tr style="background-color:#ccc;">
									<td class="modified">&nbsp;</td>
									<td class="title">Student</td>
									<td class="grade-title">Grade</td>
									<td class="date">Submitted</td>
								</tr>
							</thead>
							<tbody>
								<?php								
								foreach ($students as $key => $student) {
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
										$grade_weighting = $student["grade_weighting"];
									}
									?>
									<tr id="grades<?php echo $student["proxy_id"]; ?>">
										<td><a href="<?php echo ENTRADA_URL;?>/admin/gradebook/assignments?section=download-submission&id=<?php echo $ASSIGNMENT_ID;?>&sid=<?php echo $student["proxy_id"]; ?>"><img src="<?php echo ENTRADA_URL;?>/templates/default/images/btn_save.gif" title="Download File" alt="Download File" width="15"/></a></td>
										<td><a href="<?php echo ENTRADA_URL."/profile/gradebook/assignments?section=view&id=".$ASSIGNMENT_ID."&pid=".$student["proxy_id"];?>"><?php echo $student["fullname"]; ?></a></td>
										<td>
											<?php if (isset($assessment) && $assessment) { ?>
											<span class="grade" id="grade_<?php echo $assignment["assessment_id"]; ?>_<?php echo $student["proxy_id"] ?>"
												data-grade-id="<?php echo $grade_id; ?>"
												data-assessment-id="<?php echo $assignment["assessment_id"]; ?>"
												data-proxy-id="<?php echo $student["proxy_id"] ?>"
											><?php echo $grade_value; ?></span>
											<span class="gradesuffix" <?php echo (($grade_value === "-") ? "style=\"display: none;\"" : "") ?>>
												<?php echo assessment_suffix($assessment); ?>
											</span>
											<span class="gradesuffix" style="float:right;">
												<img src="<?php echo ENTRADA_URL;?>/images/action-edit.gif" class="edit_grade" id ="edit_grade_<?php echo $assignment["assessment_id"]; ?>_<?php echo $student["proxy_id"] ?>" style="cursor:pointer;"/>
											</span>
											<?php } else { ?>
											No Assessment
											<?php } ?>
										</td>
										<td><?php echo date(DEFAULT_DATE_FORMAT,$student["submitted_date"]); ?></td>
										<?php
										if ($assessment["marking_scheme_id"] == 3) {
											?>
										<td id="percentage_<?php echo $assignment["assessment_id"]; ?>_<?php echo $student["proxy_id"] ?>"><?php echo round($student["grade_value"],2);?>%</td>
										<?php
										}
										?>
									</tr>
									<tr class="comment-row">
										<td colspan="4" style="text-align:right;">
											<a href="javascript:void(0);" class="view_comments" id="view_comments_<?php echo $student["proxy_id"] ?>">View Comments</a> &nbsp; <span class="leave_comment" id="leave_comment_<?php echo $student["proxy_id"] ?>">Leave Comment</span>
											<ul class="comments" id="comments_<?php echo $student["proxy_id"] ?>">
												<?php 
												$query = "	SELECT a.*, CONCAT_WS(' ', c.`firstname`, c.`lastname`) AS `commenter_fullname`, c.`username` AS `commenter_username` 
															FROM `assignment_comments` AS a 
															JOIN `assignment_files` AS b 
															ON a.`afile_id` = b.`afile_id` 
															LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS c
															ON a.`proxy_id` = c.`id` 
															WHERE b.`assignment_id` = ".$db->qstr($ASSIGNMENT_ID)." 
															AND b.`proxy_id` = ".$db->qstr($student["proxy_id"])."
															AND a.`comment_active` = '1'";
												$comment_results = $db->GetAll($query);
												if($comment_results){
												?>
												
												<?php
												foreach($comment_results as $result) {
													$comments++;
													?>
													<li><table style="width:100%;" class="discussions posts"><tr>
														<td style="border-bottom: none; border-right: none"><span class="content-small">By:</span> <a href="<?php echo ENTRADA_URL."/people?profile=".html_encode($result["commenter_username"]); ?>" style="font-size: 10px"><?php echo html_encode($result["commenter_fullname"]); ?></a></td>
														<td style="border-bottom: none">
															<div style="float: left">
																<span class="content-small"><strong>Commented:</strong> <?php echo date(DEFAULT_DATE_FORMAT, $result["updated_date"]); ?></span>
															</div>
															<div style="float: right">
															<?php
															echo (($result["proxy_id"] == $ENTRADA_USER->getProxyId()) ? " (<a class=\"action\" href=\"".ENTRADA_URL."/profile/gradebook/assignments?section=edit-comment&amp;id=".$assignment["assignment_id"]."&amp;cid=".$result["acomment_id"]."\">edit</a>)" : "");
															echo (($result["proxy_id"] == $ENTRADA_USER->getProxyId()) ? " (<a class= \"action delete\" id=\"delete_".$result["acomment_id"]."\" href=\"#delete_".$result["acomment_id"]."\">delete</a>)":"");// href=\"javascript:commentDelete('".$result["acomment_id"]."')\">delete</a>)" : "");
															?>
															</div>
														</td>
													</tr>
													<tr>
														<td colspan="2" class="content" style="border-bottom: 3px solid #EBEBEB;">
														<a name="comment-<?php echo (int) $result["cscomment_id"]; ?>"></a>
														<?php
															echo ((trim($result["comment_title"])) ? "<div style=\"font-weight: bold\">".html_encode(trim($result["comment_title"]))."</div>" : "");
															echo $result["comment_description"];

															if ($result["release_date"] != $result["updated_date"]) {
																echo "<div class=\"content-small\" style=\"margin-top: 15px\">\n";
																echo "	<strong>Last updated:</strong> ".date(DEFAULT_DATE_FORMAT, $result["updated_date"])." by ".(($result["proxy_id"] == $result["updated_by"]) ? html_encode($result["commenter_fullname"]) : html_encode(get_account_data("firstlast", $result["updated_by"]))).".";
																echo "</div>\n";
															}
														?>
														</td>
													</tr></table></li>
													<?php
												}
												?>
											    <li style="text-align:right;" class="list-action"><span class="leave_comment" id="leave_comment_<?php echo $student["proxy_id"] ?>">Leave Comment</span></li>
												<?php } ?>													
											</ul>
											<div class="new_comment" id="new_comment_<?php echo $student["proxy_id"] ?>">
												<table class="comment_form" style="width:100%;">
													<tr><td style="width:100%;"><label for="new_comment_title_<?php echo $student["proxy_id"] ?>">Comment Title:</label></td></tr>
													<tr><td style="width:100%;"><input type="text" id="new_comment_title_<?php echo $student["proxy_id"] ?>" class="new_comment_text"/></td></tr>
													<tr><td><label for="new_comment_desc_<?php echo $student["proxy_id"] ?>"  class="form-required">Comment Description:</label></td></tr><tr><td><textarea id="new_comment_desc_<?php echo $student["proxy_id"] ?>" class="expandable new_comment_text"></textarea></td></tr>
												</table>
											<input type="button" value="Cancel" id="cancel_comment_<?php echo $student["proxy_id"] ?>" class="cancel_comment" style="margin-right:5px;"/><input type="button" class="add_comment" value="Add Comment" id="add_comment_<?php echo $student["proxy_id"] ?>" style="float:right;"/>
											</div>
										</td>
									</tr>
									<?php
								}
								?>
							</tbody>
						</table>					
					</div>
					<script type="text/javascript">
						jQuery(document).ready(function(){

							jQuery('.edit_grade').click(function(e){
								var id = e.target.id.substring(5);
								jQuery('#'+id).trigger('click');
							});
						});

					</script>
					<div id="gradebook_stats" style="margin-top:14px;padding-left:15px;">
						<h2>Statistics</h2>
						<div id="graph"></div>
					 	<?php 
						switch($assessment["marking_scheme_id"]) {
							case 1:
							case 4:
								//pass/fail
								$grades = array(0,0,0);
								$unentered = 0;

								foreach ($students as $key => $student) {
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
											"xTicks": [{v:0, label:"<?php echo $assignment["marking_scheme_id"] == 4 ? "Complete" : "Pass" ?>"},
														{v:1, label:"<?php echo $assignment["marking_scheme_id"] == 4 ? "Incomplete" : "Fail" ?>"}]
										},
										$("graph"),
										[data]
									);
								</script>
								<br/>
								<p>Unentered Grades: <?php echo (int) $unentered; ?></p>
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
								foreach ($students as $key => $student) {
									if ($student["grade_value"] == "") {
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
											"xTicks": [{v:0, label:"0s"},
														{v:1, label:"10s"},
														{v:2, label:"20s"},
														{v:3, label:"30s"},
														{v:4, label:"40s"},
														{v:5, label:"50s"},
														{v:6, label:"60s"},
														{v:7, label:"70s"},
														{v:8, label:"80s"},
														{v:9, label:"90s"},
														{v:10, label:"100"}]
										},
										$("graph"),
										[data]
									);
								</script>
								<br/>
								<p>Unentered Grades: <?php echo (int) $unentered; ?></p>
								<p>Mean grade: <?php echo number_format(($entered > 0 ? $sum / $entered : 0), 0); ?>%</p>
								<p>Median grade: <?php echo $grade_values[floor(count($grade_values) / 2)]; ?>%</p>
								<?php
							break;
							default:
								echo "No statistics for this marking scheme.";
							break;
						}
						?>
						<button onclick="window.location='<?php echo ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("section" => "io", "download" => "csv", "assessment_ids" => $assignment["assessment_id"])); ?>'">Download CSV</button>
						<button onclick="location.reload(true)">Refresh</button>
						<div style="margin-top: 40px;">
							<h2>Grade Calculation Exceptions</h2>
							<p>
								You can use the following exception creator to modify the calculations used to create the students final grade in this course. 
							</p>
							
							<label for="student_exceptions" class="form-required">Student Name</label>
							<select name="student_exceptions" id="student_exceptions" style="width: 210px;" onchange="add_exception(this.options[this.selectedIndex].value, '<?php echo $assignment["assessment_id"]; ?>')">
							<option value="0">-- Select A Student --</option>
								<?php
								foreach ($students as $student) {
									if (!isset($student["grade_weighting"]) || $student["grade_weighting"] == NULL) {
										echo "<option value=\"".$student["proxy_id"]."\">".$student["fullname"]."</option>";
									}
								}
								?>
							</select>
							<br /><br /><br />
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
											<a style=\"cursor: pointer;\" onclick=\"delete_exception('".$student["proxy_id"]."', '".$assignment["assessment_id"]."');\" class=\"remove\">
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
							<br/>
						<h2>Import Grades</h2>
						<div id="display-notice-box" class="display-notice">
								<strong>Important Notes:</strong>
								<br/>Format for the CSV should be [Student Number, Grade] with each entry on a separate line (without the brackets). 
								<br/>Any grades entered will be overwritten if present in the CSV.
						</div>
						<form enctype="multipart/form-data" action="<?php echo ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("section" => "csv-upload", "assessment_id" => $assignment["assessment_id"])); ?>" method="POST">
							<input type="file" name ="file"/><br/><br/>
							<input type="submit" value ="Import CSV"/>
						</form>
					</div>
					<script type="text/javascript">
					jQuery(document).ready(function(){
						jQuery('.comments').hide();
						jQuery('.new_comment').hide();
						jQuery('#gradebook_stats').hide();
						jQuery('#advanced-options').click(function(){
							if (jQuery('#gradebook_stats').is(":visible")) {
								jQuery('#assignment_submissions').attr('class','');
								jQuery('#gradebook_stats').hide();
								jQuery('#advanced-options').text('Show Options');
							} else {
								jQuery('#assignment_submissions').attr('class','squeeze');
								jQuery('#gradebook_stats').show();	
								jQuery('#advanced-options').text('Hide Options');
							}
						});
						
						jQuery('.add_comment').click(function(){
							var id = jQuery(this).attr('id').substring(12);
							//var comment_desc = tinyMCE.get('new_comment_desc_'+id).getContent();
							var comment_desc = jQuery('#new_comment_desc_'+id).val();
							var comment_title = jQuery('#new_comment_title_'+id).val();
							jQuery.ajax({
								type: "POST",
								url: "<?php echo ENTRADA_URL;?>/api/ajax-comment.api.php",
								data: "comment_description="+comment_desc+"&comment_title="+comment_title+"&uid="+id+"&assignment_id=<?php echo $ASSIGNMENT_ID;?>&comment_type=assignment",
								success: function(data){
									try {
										var result = jQuery.parseJSON(data);
										if (result.error) {
											alert(result.error);
										} else {
											jQuery('#new_comment_desc_'+id).val('');
											jQuery('#new_comment_title_'+id).val('');											
											jQuery('#new_comment_'+id).hide();
											alert('Successfully sent message');
										}
									} catch(e) {
											jQuery('#new_comment_desc_'+id).val('');
											jQuery('#new_comment_title_'+id).val('');											
											jQuery('#new_comment_'+id).hide();
											jQuery(data).insertBefore(jQuery('#comments_'+id+' > .list-action'));
											
									}
								}
							  });
						});
						
						jQuery('.delete').live('click',function(){
							id = jQuery(this).attr('id').substring(7);
							jQuery("#dialog-confirm").dialog({
								resizable: false,
								height:180,
								modal: true,
								buttons: {
									'Delete': function() {
										window.location = '<?php echo ENTRADA_URL."/profile/gradebook/assignments";?>?section=delete-comment&id='+id+'&returnto=grade';
										return true;
									},
									Cancel: function() {
										jQuery(this).dialog('close');
									}
								}
								});
						});						
						
						jQuery('.view_comments').live('click',function(e){
							var id = e.target.id.substring(14);
							jQuery('#comments_'+id).show();
							jQuery('#view_comments_'+id).text('Hide Comments');
							jQuery('#view_comments_'+id).attr('class','hide_comments');
						});	

						jQuery('.hide_comments').live('click',function(e){
							var id = e.target.id.substring(14);
							jQuery('#comments_'+id).hide();
							jQuery('#view_comments_'+id).text('View Comments');
							jQuery('#view_comments_'+id).attr('class','view_comments');
						});		

						jQuery('.leave_comment').live('click',function(e){
							var id = e.target.id.substring(14);
							jQuery('#new_comment_'+id).show();
						});	

						jQuery('.cancel_comment').live('click',function(e){
							var id = e.target.id.substring(15);
							jQuery('#new_comment_'+id).hide();
							jQuery('#new_comment_text_'+id).val('');
						});	
					});
					</script>
					<div id="dialog-confirm" title="Delete?" style="display: none">
						<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>This item will be permanently deleted and cannot be recovered. Are you sure you want to delete it?</p>
					</div>
					<style>
					#gradebook_stats{
						width:265px;
					}
					.mceEditor{
						width:100%!important;
					}
					.squeeze{
						width:465px;
						float:left;
					}
					.leave_comment{
						cursor:pointer;
						color:#0000FF;
					}
					ul.comments{
						list-style-type: none;
						padding: 0;
						margin-left: 0;			
					}
					ul.comments li{
						width:100%;
						margin-bottom:5px;
						text-align:left;
					}
					ul.comments li.nocomments{
						border:none;
						background-color:yellow;
						text-align:center;
					}
					.comment_info,.comment_text{
						width:100%;
						float:left;
					}
					.discussions tr td{
						white-space: normal!important;
					}
					.comment_form{
						width:100%;
					}
					.comment_form tr td{
						border:none!important;
					}
					.new_comment_text{
						width:98%!important;
					}
					.clearfix{
						clear:both;
					}
					</style>
				<?php
				else:
				?>
				<div class="display-notice">No one has submitted their assignment yet.</div>
				<?php endif;
			} else {
				$ERROR++;
				$ERRORSTR[] = "In order to edit an assessment's grades you must provide a valid assignment identifier.";

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