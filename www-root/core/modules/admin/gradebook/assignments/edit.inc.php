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
} elseif (!$ENTRADA_ACL->amIAllowed("gradebook", "create", false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	
	if(isset($_GET["assignment_id"]) && $tmp_id = clean_input($_GET["assignment_id"],"int")){
		$ASSIGNMENT_ID = $tmp_id;
		$query = "SELECT * FROM `assignment_contacts` WHERE `assignment_id` = ".$db->qstr($ASSIGNMENT_ID)." AND `proxy_id` = ".$db->qstr($ENTRADA_USER->getProxyId());
		$IS_CONTACT = $db->GetRow($query);
	}
	
	if ($COURSE_ID) {
		if($ASSIGNMENT_ID){
			if($IS_CONTACT){
				$query = "	SELECT * FROM `courses`
							WHERE `course_id` = ".$db->qstr($COURSE_ID)."
							AND `course_active` = '1'";
				$course_details	= $db->GetRow($query);

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

					// Error Checking
					switch($STEP) {
						case 2 :
							if(isset($_POST["assignment_title"]) && $tmp_title = clean_input($_POST["assignment_title"],array("trim","notags"))){
								$PROCESSED["assignment_title"] = $tmp_title;
							}else{
								$ERROR++;
								$ERRORSTR[] = "Assignment Title is a required Field.";
							}

							if(isset($_POST["assignment_description"]) && $tmp_desc = clean_input($_POST["assignment_description"],array("trim","notags"))){
								$PROCESSED["assignment_description"] = $tmp_desc;
							}else{
								$PROCESSED["assignment_description"] = "";
							}

							if(isset($_POST["assignment_uploads"]) && $tmp_uploads = clean_input($_POST["assignment_uploads"],array("trim","notags"))){
								$PROCESSED["assignment_uploads"] = $tmp_uploads == "allow"?0:1;
							}else{
								$PROCESSED["assignment_uploads"] = 1;
							}

							if(isset($_POST["assessment_id"]) && $tmp_ass = clean_input($_POST["assessment_id"],array("trim","notags"))){
								$PROCESSED["assessment_id"] = $tmp_ass;
							}else{
								$PROCESSED["assessment_id"] = 0;
							}

							if($PROCESSED["assessment_id"] === "N"){
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
													if (in_array($assessment_option["id"], $assessment_options_selected)) {
														$PROCESSED["option_active"] = 1;
													} else {
														$PROCESSED["option_active"] = 0;
													}
													$db->AutoExecute("assessment_options", $PROCESSED, "UPDATE", "`assessment_id` = " . $db->qstr($assessment_details["assessment_id"]) . "AND `option_id` = " . $db->qstr($assessment_option["id"]));
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
							}


							/**
							 * Required field "event_start" / Event Date & Time Start (validated through validate_calendars function).
							 */
							$release_date = validate_calendars("viewable", false, false,true);
							$due_date = validate_calendars("due", false, false,true);

							if ((isset($release_date["start"])) && ((int) $release_date["start"])) {
								$PROCESSED["release_date"] = (int) $release_date["start"];
							} else {
								$PROCESSED["release_date"] = 0;
							}

							if ((isset($release_date["finish"])) && ((int) $release_date["finish"])) {
								$PROCESSED["release_until"] = (int) $release_date["finish"];
							} else {
								$PROCESSED["release_until"] = 0;
							}

							if ((isset($due_date["finish"])) && ((int) $due_date["finish"])) {
								$PROCESSED["due_date"] = (int) $due_date["finish"];
							} else {
								$PROCESSED["due_date"] = 0;
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
							if(!$ERROR){

								$PROCESSED["updated_date"]	= time();
								$PROCESSED["updated_by"]	= $_SESSION["details"]["id"];
								$PROCESSED["course_id"]		= $COURSE_ID;

								if ($db->AutoExecute("assignments", $PROCESSED, "UPDATE","`assignment_id` = ".$db->qstr($ASSIGNMENT_ID))) {
									$query = "DELETE FROM `assignment_contacts` WHERE `assignment_id` = ".$db->qstr($ASSIGNMENT_ID);
									if($db->Execute($query)) {
										$PROCESSED["assignment_id"] = $ASSIGNMENT_ID;
										$PROCESSED["proxy_id"] = $ENTRADA_USER->getProxyId();
										$PROCESSED["contact_order"] = 0;
										$PROCESSED["updated_date"]	= time();
										$PROCESSED["updated_by"] = $ENTRADA_USER->getProxyId();
										if ($db->AutoExecute("assignment_contacts", $PROCESSED, "INSERT")) {
											if ((isset($_POST["associated_director"])) && ($associated_directors = explode(",", $_POST["associated_director"])) && (@is_array($associated_directors)) && (@count($associated_directors))) {
												$order = 0;
												foreach($associated_directors as $proxy_id) {
													if ($proxy_id = clean_input($proxy_id, array("trim", "int"))) {
														if($proxy_id != $ENTRADA_USER->getProxyId()){
															if (!$db->AutoExecute("assignment_contacts", array("assignment_id" => $ASSIGNMENT_ID, "proxy_id" => $proxy_id, "contact_order" => $order+1, "updated_date"=>time(),"updated_by"=>$ENTRADA_USER->getProxyId()), "INSERT")) {
																add_error("There was an error when trying to insert a &quot;" . $module_singular_name . " Director&quot; into the system. The system administrator was informed of this error; please try again later.");

																application_log("error", "Unable to insert a new course_contact to the database when updating an event. Database said: ".$db->ErrorMsg());
															} else {
																$order++;	
															}
														}
													}
												}
											}																		
											application_log("success", "Successfully added assignment ID [".$ASSIGNMENT_ID."]");
										} else {
											application_log("error", "Unable to fetch the newly inserted assignment identifier for this assignment.");
										}
									} else {
										application_log("error", "Unable to update assignment contacts.");
									}
								} else {
									echo 'failed';
									application_log("error", "Unable to fetch the newly inserted assignment identifier for this assignment.");
								}

								switch($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"]) {
									case "grade" :
										$url = ENTRADA_URL."/admin/gradebook/assignments?".replace_query(array("step" => false, "section" => "grade", "assignment_id" => $ASSIGNMENT_ID,"id"=>$COURSE_ID));
										$msg = "You will now be redirected to the <strong>Grade Assignment</strong> page for \"<strong>".$PROCESSED["name"] . "</strong>\"; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
									break;
									case "new" :
										$url = ENTRADA_URL."/admin/gradebook/assignments?".replace_query(array("step" => false, "section" => "add","id"=>$COURSE_ID));
										$msg = "You will now be redirected to another <strong>Add Assignment</strong> page for the ". $course_details["course_name"] . " gradebook; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
									break;
									case "parent" :
										$url = ENTRADA_URL."/admin/".$MODULE;
										$msg = "You will now be redirected to the <strong>Gradebook</strong> index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
									break;
									case "index" :
									default :
										$url = ENTRADA_URL."/admin/gradebook?".replace_query(array("step" => false, "section" => "view", "assignment_id" => false));
										$msg = "You will now be redirected to the <strong>assignment index</strong> page for ". $course_details["course_name"] . "; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
									break;
								}
								$SUCCESS++;
								$SUCCESSSTR[] 	= $msg;
								$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";
							}
							if ($ERROR) {
								$STEP = 1;
							}
						break;
						case 1 :							
						default :
							$query = "SELECT * FROM `assignments` WHERE `assignment_id` = ".$db->qstr($ASSIGNMENT_ID);
							if($assignment_record = $db->GetRow($query)){
								$PROCESSED["assignment_id"] = $assignment_record["assignment_id"];
								$PROCESSED["assignment_title"] = $assignment_record["assignment_title"];
								$PROCESSED["assignment_description"] = $assignment_record["assignment_description"];
								$PROCESSED["assignment_uploads"] = $assignment_record["assignment_uploads"];
								$PROCESSED["assessment_id"] = $assignment_record["assessment_id"];
								$PROCESSED["release_date"] = $assignment_record["release_date"];
								$PROCESSED["release_until"] = $assignment_record["release_until"];
								$PROCESSED["due_date"] = $assignment_record["due_date"];
							}
							continue;
						break;
					}
										$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/gradebook/assignments?".replace_query(array("section" => "grade", "id" => $COURSE_ID, "assignment_id"=>$PROCESSED["assignment_id"],"step" => false)), "title" => $PROCESSED["assignment_title"]);
										$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("section" => "edit", "id" => $COURSE_ID, "step" => false)), "title" => "Editing Assignment");
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
							$assignment_directors = array();
							$query	= "	SELECT `".AUTH_DATABASE."`.`user_data`.`id` AS `proxy_id`, CONCAT_WS(', ', `".AUTH_DATABASE."`.`user_data`.`lastname`, `".AUTH_DATABASE."`.`user_data`.`firstname`) AS `fullname`, `".AUTH_DATABASE."`.`organisations`.`organisation_id`
										FROM `".AUTH_DATABASE."`.`user_data`
										LEFT JOIN `".AUTH_DATABASE."`.`user_access`
										ON `".AUTH_DATABASE."`.`user_access`.`user_id` = `".AUTH_DATABASE."`.`user_data`.`id`
										LEFT JOIN `".AUTH_DATABASE."`.`organisations`
										ON `".AUTH_DATABASE."`.`user_data`.`organisation_id` = `".AUTH_DATABASE."`.`organisations`.`organisation_id`
										WHERE `".AUTH_DATABASE."`.`user_access`.`group` = 'faculty'
										AND (`".AUTH_DATABASE."`.`user_access`.`role` = 'director' OR `".AUTH_DATABASE."`.`user_access`.`role` = 'admin')
										AND `".AUTH_DATABASE."`.`user_access`.`app_id` = '".AUTH_APP_ID."'
										AND `".AUTH_DATABASE."`.`user_access`.`account_active` = 'true'
										ORDER BY `fullname` ASC";
							$results = ((USE_CACHE) ? $db->CacheGetAll(AUTH_CACHE_TIMEOUT, $query) : $db->GetAll($query));
							if ($results) {
								foreach($results as $result) {
									$assignment_directors[$result["proxy_id"]] = array('proxy_id'=>$result["proxy_id"], 'fullname'=>$result["fullname"], 'organisation_id'=>$result['organisation_id']);
								}
								$DIRECTOR_LIST = $assignment_directors;
							}							
							
							
							/**
							 * Non-required field "associated_faculty" / Associated Faculty (array of proxy ids).
							 * This is actually accomplished after the event is inserted below.
							 */
							if ((isset($_POST["associated_director"]))) {
								$associated_director = explode(',', $_POST["associated_director"]);
								foreach($associated_director as $contact_order => $proxy_id) {
									if ($proxy_id = clean_input($proxy_id, array("trim", "int"))) {
										$chosen_course_directors[(int) $contact_order] = $proxy_id;
									}
								}
							} else {
								$query = "SELECT * FROM `assignment_contacts` WHERE `assignment_id` = ".$db->qstr($ASSIGNMENT_ID)." ORDER BY `contact_order` ASC";
								$results = $db->GetAll($query);
								if ($results) {
									foreach ($results as $result) {
										$chosen_course_directors[$result["contact_order"]] = $result["proxy_id"];
									}
								}
							}
							
						?>
						<h1>Edit Assignment</h1>
						<?php
						if ($ERROR) {
							echo display_error();
						}
						?>
						<form action="<?php echo ENTRADA_URL; ?>/admin/gradebook/assignments?<?php echo replace_query(array("step" => 2)); ?>" method="post">
							<h2>Assignment Details</h2>
							<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Adding Assignment">
								<colgroup>
									<col style="width: 3%" />
									<col style="width: 22%" />
									<col style="width: 75%" />
								</colgroup>
								<tbody>

									<tr>
										<td></td>
										<td><label class="form-nrequired">Assignment Name</label></td>
										<td>
											<input type="text" name="assignment_title" style="width: 243px" value="<?php echo ($PROCESSED["assignment_title"]?$PROCESSED["assignment_title"]:"");?>"/>
										</td>
									</tr>							
									<tr>
										<td></td>
										<td style="vertical-align: top;"><label class="form-nrequired">Associated Assessment</label></td>
										<td>
											<select name="assessment_id" id="assessment-selector" style="width: 250px">
												<option value="0"<?php echo $PROCESSED["assessment_id"] == 0?' selected="selected"':'';?>>No Assessment</option>
												<option value="N"<?php echo !isset($PROCESSED["assessment_id"])?' selected="selected"':'';?>>New Assessment</option>
												<?php 
													$query = "SELECT * FROM `assessments` WHERE `course_id` = ".$db->qstr($COURSE_ID);
													$course_assessments = $db->GetAll($query);
													if($course_assessments){
														foreach($course_assessments as $course_assessment){
															?><option value="<?php echo $course_assessment["assessment_id"];?>"<?php echo ($PROCESSED["assessment_id"] && $PROCESSED["assessment_id"] == $course_assessment["assessment_id"]?" selected=\"selected\"":"");?>><?php echo $course_assessment["name"];?></option><?php
														}
													}	
												?>
											</select>
											<div class="content-small">The assessment determines how the assignment should be marked. You can either select one if it already exists, or create a new one now.</div>
										</td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<td style="vertical-align:top">Assignment Contacts</td>
										<td>
										<script type="text/javascript">
											var sortables = new Array();
											function updateOrder(type) {
												$('associated_'+type).value = Sortable.sequence(type+'_list');
											}

											function addItem(type) {
												if (($(type+'_id') != null) && ($(type+'_id').value != '') && ($(type+'_'+$(type+'_id').value) == null)) {
													var li = new Element('li', {'class':'community', 'id':type+'_'+$(type+'_id').value, 'style':'cursor: move;'}).update($(type+'_name').value);
													$(type+'_name').value = '';
													li.insert({bottom: '<img src=\"<?php echo ENTRADA_URL; ?>/images/action-delete.gif\" class=\"list-cancel-image\" onclick=\"removeItem(\''+$(type+'_id').value+'\', \''+type+'\')\" />'});
													$(type+'_id').value	= '';
													$(type+'_list').appendChild(li);
													sortables[type] = Sortable.destroy($(type+'_list'));
													Sortable.create(type+'_list', {onUpdate : function(){updateOrder(type);}});
													updateOrder(type);
												} else if ($(type+'_'+$(type+'_id').value) != null) {
													alert('Important: Each user may only be added once.');
													$(type+'_id').value = '';
													$(type+'_name').value = '';
													return false;
												} else if ($(type+'_name').value != '' && $(type+'_name').value != null) {
													alert('Important: When you see the correct name pop-up in the list as you type, make sure you select the name with your mouse, do not press the Enter button.');
													return false;
												} else {
													return false;
												}
											}

											function addItemNoError(type) {
												if (($(type+'_id') != null) && ($(type+'_id').value != '') && ($(type+'_'+$(type+'_id').value) == null)) {
													addItem(type);
												}
											}

											function copyItem(type) {
												if (($(type+'_name') != null) && ($(type+'_ref') != null)) {
													$(type+'_ref').value = $(type+'_name').value;
												}

												return true;
											}

											function checkItem(type) {
												if (($(type+'_name') != null) && ($(type+'_ref') != null) && ($(type+'_id') != null)) {
													if ($(type+'_name').value != $(type+'_ref').value) {
														$(type+'_id').value = '';
													}
												}

												return true;
											}

											function removeItem(id, type) {
												if ($(type+'_'+id)) {
													$(type+'_'+id).remove();
													Sortable.destroy($(type+'_list'));
													Sortable.create(type+'_list', {onUpdate : function (type) {updateOrder(type)}});
													updateOrder(type);
												}
											}

											function selectItem(id, type) {
												if ((id != null) && ($(type+'_id') != null)) {
													$(type+'_id').value = id;
												}
											}

											function loadCurriculumPeriods(ctype_id) {
												var updater = new Ajax.Updater('curriculum_type_periods', '<?php echo ENTRADA_URL."/api/curriculum_type_periods.api.php"; ?>',{
													method:'post',
													parameters: {
														'ctype_id': ctype_id
													},
													onFailure: function(transport){
														$('curriculum_type_periods').update(new Element('div', {'class':'display-error'}).update('No Periods were found for this Curriculum Category.'));
													}
												});
											}								
											</script>
											<input type="text" id="director_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle" onkeyup="checkItem('director')" onblur="addItemNoError('director')" />
											<script type="text/javascript">
												$('director_name').observe('keypress', function(event){
													if (event.keyCode == Event.KEY_RETURN) {
														addItem('director');
														Event.stop(event);
													}
												});
											</script>
											<?php
											$ONLOAD[] = "Sortable.create('director_list', {onUpdate : function() {updateOrder('director')}})";
											$ONLOAD[] = "$('associated_director').value = Sortable.sequence('director_list')";
											?>
											<div class="autocomplete" id="director_name_auto_complete"></div><script type="text/javascript">new Ajax.Autocompleter('director_name', 'director_name_auto_complete', '<?php echo ENTRADA_RELATIVE; ?>/api/personnel.api.php?type=director', {frequency: 0.2, minChars: 2, afterUpdateElement: function (text, li) {selectItem(li.id, 'director'); copyItem('director');}});</script>
											<input type="hidden" id="associated_director" name="associated_director" />
											<input type="button" class="button-sm" onclick="addItem('director');" value="Add" style="vertical-align: middle" />
											<span class="content-small">(<strong>Example:</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?>)</span>
											<ul id="director_list" class="menu" style="margin-top: 15px">
												<?php
												if (is_array($chosen_course_directors) && count($chosen_course_directors)) {
													foreach ($chosen_course_directors as $director) {
														if ((array_key_exists($director, $DIRECTOR_LIST)) && is_array($DIRECTOR_LIST[$director])) {
															?>
																<li class="community" id="director_<?php echo $DIRECTOR_LIST[$director]["proxy_id"]; ?>" style="cursor: move;"><?php echo $DIRECTOR_LIST[$director]["fullname"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" class="list-cancel-image" onclick="removeItem('<?php echo $DIRECTOR_LIST[$director]["proxy_id"]; ?>', 'director');"/></li>								
															<?php
														}
													}
												}
												?>
											</ul>
											<input type="hidden" id="director_ref" name="director_ref" value="" />
											<input type="hidden" id="director_id" name="director_id" value="" />
										</td>
									</tr>							
									<tr>
										<td>&nbsp;</td>
										<td style="vertical-align: top">Assignment Description</td>
										<td>
											<textarea id="assignment_description" name="assignment_description" style="width: 100%; height: 150px" cols="70" rows="10"><?php echo html_encode(trim(strip_selected_tags($PROCESSED["assignment_description"], array("font")))); ?></textarea>
										</td>
									</tr>
									<tr>
										<td></td>
										<td style="vertical-align: top;"><label class="form-nrequired">Allow Revisions</label></td>
										<td>
											<table>
												<tbody>
														<tr>
															<td style="vertical-align: top"><input type="radio" name="assignment_uploads" value="allow" style="vertical-align: middle" <?php echo $PROCESSED["assignment_uploads"] == 0?"checked=\"checked\"":"";?>></td>
															<td colspan="2" style="padding-bottom: 15px">
																<label for="event_audience_type_course" class="radio-group-title">Allow Submission Revision</label>
																<div class="content-small">Allow students to upload a newer version of their assignment after they've already made their submission as long as its still before the due date.</div>
															</td>
														</tr>
														<tr>
															<td style="vertical-align: top"><input type="radio" name="assignment_uploads" value="deny" style="vertical-align: middle;" <?php echo $PROCESSED["assignment_uploads"] == 1?"checked=\"checked\"":"";?>></td>
															<td colspan="2" style="padding-bottom: 15px">
																<label for="event_audience_type_course" class="radio-group-title">Do Not Allow Submission Revision</label>
																<div class="content-small">Do not allow students to upload a newer version of their assignment after they've already uploaded one.</div>
															</td>
														</tr>

												</tbody>
											</table>
										</td>
									</tr>
									<?php echo generate_calendars("viewable", "", true, false, ((isset($PROCESSED["release_date"])) ? $PROCESSED["release_date"] : 0), true, false, ((isset($PROCESSED["release_until"])) ? $PROCESSED["release_until"] : 0)); ?>
									<?php echo generate_calendars("due", "", false, false,  0, true, false,  ((isset($PROCESSED["due_date"])) ? $PROCESSED["due_date"] : 0)); ?>
								</tbody>
							</table>
							<div id="assessment-section">
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
							</div>
							<script type="text/javascript" charset="utf-8">
								
								jQuery('#assessment-section').hide();

								jQuery(document).ready(function(){
									if(jQuery('#assessment-selector').val() == 'N'){
										jQuery('#assessment-section').slideDown('slow');
									}else{
										jQuery('#assessment-section').slideUp('slow');
									}
								});

								jQuery('#assessment-selector').bind('change',function(){
									if(jQuery(this).val() == 'N'){
										jQuery('#assessment-section').slideDown('slow');
									}else{
										jQuery('#assessment-section').slideUp('slow');
									}
								});	
								
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
					$ERRORSTR[] = "In order to add an assignment to a gradebook you must provide a valid course identifier. The provided ID does not exist in this system.";

					echo display_error();

					application_log("notice", "Failed to provide a valid course identifier when attempting to add an assignment");
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "In order to edit an assignment you must be assigned as an 'Assignment Contact'. You do not have access to edit this assignment.";

				echo display_error();

				application_log("notice", "Not an Assignment Contact for the specified assignment.");
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "In order to edit an assignment you must provide a valid assignment identifier. The provided ID does not exist in this system.";

			echo display_error();

			application_log("notice", "Failed to provide assignment identifier when attempting to edit an assignment");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to edit an assignment you must provide a valid course identifier. The provided ID does not exist in this system.";

		echo display_error();

		application_log("notice", "Failed to provide course identifier when attempting to edit an assignment");
	}
}