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
 * Admin section of the courses module which allows
 * users with access to edit the content of a course.
 *
 * @author Organisation: Queen's University
 * @author Unit: Medical Education Technology Unit
 * @author Director: Dr. Benjamin Chen <bhc@post.queensu.ca>
 * @author Developer: Matt Simpson <simpson@post.queensu.ca>
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @version 3.0
 * @copyright Copyright 2010 Queen's University, MEdTech Unit
 *
 * $Id: content.inc.php 1169 2010-05-01 14:18:49Z simpson $
*/
if((!defined("PARENT_INCLUDED")) || (!defined("IN_COURSES"))) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif($ENTRADA_ACL->amIAllowed('coursecontent', 'update ', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	if($COURSE_ID) {
		if(!$ORGANISATION_ID){
			$query = "SELECT `organisation_id` FROM `courses` WHERE `course_id` = ".$db->qstr($COURSE_ID);
			$result = $db->GetOne($query);
			if($result)
				$ORGANISATION_ID = (int)$result;
			else
				$ORGANISATION_ID = 1;
		}
		
		
		list($course_objectives,$top_level_id) = courses_fetch_objectives($ORGANISATION_ID,array($COURSE_ID),-1, 1, false, false, 0, true);
		
		$query			= "	SELECT * FROM `courses` 
							WHERE `course_id` = ".$db->qstr($COURSE_ID)."
							AND `course_active` = '1'";
		$course_details	= $db->GetRow($query);
		if($course_details) {
			if(!$ENTRADA_ACL->amIAllowed(new CourseContentResource($course_details["course_id"], $course_details["organisation_id"]), 'update')) {
				application_log("error", "A program coordinator attempted to modify content for a course [".$COURSE_ID."] that they were not the coordinator of.");
				
				header("Location: ".ENTRADA_URL."/admin/".$MODULE);
				exit;
			} else {
				$query	= "	SELECT a.*, b.`community_url`, c.`cpage_id`
							FROM `community_courses` AS a
							JOIN `communities` AS b
							ON a.`community_id` = b.`community_id`
							JOIN `community_pages` AS c
							ON a.`community_id` = c.`community_id`
							WHERE a.`course_id` = ".$db->qstr($COURSE_ID);
				$result = $db->getRow($query);
				if ($result) {
					header("Location: ".ENTRADA_URL."/community".$result["community_url"].":pages");
					exit;
				} else {
					$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("section" => "content", "id" => $COURSE_ID)), "title" => $module_singular_name . " Content");
	
					$PROCESSED		= $course_details;
					/**
					 * If the type variable is set, there should be some work to do.
					 */
					if(isset($_POST["type"])) {
						switch($_POST["type"]) {
							case "text" :
								$PROCESSED["updated_date"]	= time();
								$PROCESSED["updated_by"]	= $_SESSION["details"]["id"];
								
								/**
								 * Not-Required: course_url | External Website Url
								 */
								if((isset($_POST["course_url"])) && ($tmp_input = clean_input($_POST["course_url"], array("notags", "nows"))) && ($tmp_input != "http://")) {
									$PROCESSED["course_url"] = $tmp_input;
								} else {
									$PROCESSED["course_url"] = "";
								}
								
								/**
								 * Not-Required: course_description | Course Description
								 */
								if((isset($_POST["course_description"])) && (clean_input($_POST["course_description"], array("notags", "nows")))) {
									$PROCESSED["course_description"] = clean_input($_POST["course_description"], array("allowedtags"));
								} else {
									$PROCESSED["course_description"] = "";
								}
								
								/**
								 * Not-Required: course_objectives | Course Objectives
								 */
								if((isset($_POST["course_objectives"])) && (clean_input($_POST["course_objectives"], array("notags", "nows")))) {
									$PROCESSED["course_objectives"] = clean_input($_POST["course_objectives"], array("allowedtags"));
								} else {
									$PROCESSED["course_objectives"] = "";
								}
	
								/**
								 * Not-Required: course_message | Director's Message
								 */
								if((isset($_POST["course_message"])) && (clean_input($_POST["course_message"], array("notags", "nows")))) {
									$PROCESSED["course_message"] = clean_input($_POST["course_message"], array("allowedtags"));
								} else {
									$PROCESSED["course_message"] = "";
								}
								
								if($db->AutoExecute("courses", $PROCESSED, "UPDATE", "`course_id` = ".$db->qstr($COURSE_ID))) {
									
									$SUCCESS++;
									$SUCCESSSTR[] = "You have successfully updated the <strong>".html_encode($course_details["course_name"])."</strong> " . $module_singular_name . " details section.";
	
									application_log("success", "Successfully updated course_id [".$COURSE_ID."] course details.");
								} else {
									if($db->ErrorMsg()) {
										application_log("error", "Failed to update the course page content for course_id [".$COURSE_ID."]. Database said: ".$db->ErrorMsg());
									}
								}
							break;
							case "objectives" :
								if (isset($_POST["course_objectives"]) && ($objectives = $_POST["course_objectives"]) && (is_array($objectives))) {
									foreach ($objectives as $objective => $status) {
										if ($objective) {
											if (isset($_POST["objective_text"][$objective]) && $_POST["objective_text"][$objective]) {
												$objective_text = clean_input($_POST["objective_text"][$objective], array("notags"));
											} else {
												$objective_text = false;
											}
											$PROCESSED_OBJECTIVES[$objective] = $objective_text;
										}
									}
								}

								if (is_array($PROCESSED_OBJECTIVES)) {
									foreach ($PROCESSED_OBJECTIVES as $objective_id => $objective) {
										$objective_found = $db->GetOne("SELECT `objective_id` FROM `course_objectives` WHERE `objective_id` = ".$db->qstr($objective_id)." AND `course_id` = ".$db->qstr($COURSE_ID));
										if ($objective_found) {
											$db->AutoExecute("course_objectives", array("objective_details" => $objective, "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "UPDATE", "`objective_id` = ".$db->qstr($objective_id)." AND `course_id` = ".$db->qstr($COURSE_ID));
										} else {
											$db->AutoExecute("course_objectives", array("course_id" => $COURSE_ID, "objective_id" => $objective_id, "objective_details" => $objective, "importance" => 0, "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT");
										}

									}
								}
	
								$SUCCESS++;
								$SUCCESSSTR[] = "You have successfully updated the <strong>".html_encode($course_details["course_name"])."</strong> " . $module_singular_name . " objectives section.";
	
								application_log("success", "Successfully updated course_id [".$COURSE_ID."] course objectives.");
							break;
							case "files" :
								$FILE_IDS = array();
	
								if((!isset($_POST["delete"])) || (!is_array($_POST["delete"])) || (!@count($_POST["delete"]))) {
									$ERROR++;
									$ERRORSTR[] = "You must select at least 1 file to delete by checking the checkbox to the left the file title.";
	
									application_log("notice", "User pressed the Delete file button without selecting any files to delete.");
								} else {
									foreach($_POST["delete"] as $file_id) {
										$file_id = (int) trim($file_id);
										if($file_id) {
											$FILE_IDS[] = (int) trim($file_id);
										}
									}
	
									if(!@count($FILE_IDS)) {
										$ERROR++;
										$ERRORSTR[] = "There were no valid file identifiers provided to delete.";
									} else {
										foreach($FILE_IDS as $file_id) {
											$query	= "SELECT * FROM `course_files` WHERE `id`=".$db->qstr($file_id)." AND `course_id`=".$db->qstr($COURSE_ID);
											$sresult	= $db->GetRow($query);
											if($sresult) {
												$query = "DELETE FROM `course_files` WHERE `id`=".$db->qstr($file_id)." AND `course_id`=".$db->qstr($COURSE_ID);
												if($db->Execute($query)) {
													if($db->Affected_Rows()) {
														if(@unlink(FILE_STORAGE_PATH."/C".$file_id)) {
															$SUCCESS++;
															$SUCCESSSTR[] = "Successfully deleted ".$sresult["file_name"]." from this course.";
	
															application_log("success", "Deleted ".$sresult["file_name"]." [ID: ".$file_id."] from filesystem.");
														}
	
														application_log("success", "Deleted ".$sresult["file_name"]." [ID: ".$file_id."] from database.");
													} else {
														application_log("error", "Trying to delete ".$sresult["file_name"]." [ID: ".$file_id."] from database, but there were no rows affected. Database said: ".$db->ErrorMsg());
													}
												} else {
													$ERROR++;
													$ERRORSTR[] = "We are unable to delete ".$sresult["file_name"]." from the course at this time. The system administrator has been informed of the error, please try again later.";
	
													application_log("error", "Trying to delete ".$sresult["file_name"]." [ID: ".$file_id."] from database, but the execute statement returned false. Database said: ".$db->ErrorMsg());
												}
											}
										}
									}
								}
							break;
							case "links" :
								$LINK_IDS = array();
	
								if((!isset($_POST["delete"])) || (!is_array($_POST["delete"])) || (!@count($_POST["delete"]))) {
									$ERROR++;
									$ERRORSTR[] = "You must select at least 1 link to delete by checking the checkbox to the left the link.";
	
									application_log("notice", "User pressed the Delete link button without selecting any files to delete.");
								} else {
									foreach($_POST["delete"] as $link_id) {
										$link_id = (int) trim($link_id);
										if($link_id) {
											$LINK_IDS[] = (int) trim($link_id);
										}
									}
	
									if(!@count($LINK_IDS)) {
										$ERROR++;
										$ERRORSTR[] = "There were no valid link identifiers provided to delete.";
									} else {
										foreach($LINK_IDS as $link_id) {
											$query	= "SELECT * FROM `course_links` WHERE `id`=".$db->qstr($link_id)." AND `course_id`=".$db->qstr($COURSE_ID);
											$sresult	= $db->GetRow($query);
											if($sresult) {
												$query = "DELETE FROM `course_links` WHERE `id`=".$db->qstr($link_id)." AND `course_id`=".$db->qstr($COURSE_ID);
												if($db->Execute($query)) {
													if($db->Affected_Rows()) {
														application_log("success", "Deleted course ".$sresult["link"]." [ID: ".$link_id."] from database.");
													} else {
														application_log("error", "Trying to delete course ".$sresult["link"]." [ID: ".$link_id."] from database, but there were no rows affected. Database said: ".$db->ErrorMsg());
													}
												} else {
													$ERROR++;
													$ERRORSTR[] = "We are unable to delete ".$sresult["link"]." from the course at this time. The system administrator has been informed of the error, please try again later.";
	
													application_log("error", "Trying to delete course ".$sresult["link"]." [ID: ".$link_id."] from database, but the execute statement returned false. Database said: ".$db->ErrorMsg());
												}
											}
										}
									}
								}
							break;
							default :
								continue;
							break;
						}
					}
	
					/**
					 * Load the rich text editor.
					 */
					load_rte();
	
					$LASTUPDATED		= $course_details["updated_date"];
	
					$OTHER_DIRECTORS	= array();
														
					?>
					<script type="text/javascript">
					function openFileWizard(cid, fid, action) {
						if(!action) {
							action = 'add';
						}
					
						if(!cid) {
							return;
						} else {
							var windowW = 485;
							var windowH = 585;
					
							var windowX = (screen.width / 2) - (windowW / 2);
							var windowY = (screen.height / 2) - (windowH / 2);
					
							fileWizard = window.open('<?php echo ENTRADA_URL; ?>/file-wizard-course.php?action=' + action + '&id=' + cid + ((fid) ? '&fid=' + fid : ''), 'fileWizard', 'width='+windowW+', height='+windowH+', scrollbars=no, resizable=yes');
							fileWizard.blur();
							window.focus();
					
							fileWizard.resizeTo(windowW, windowH);
							fileWizard.moveTo(windowX, windowY);
					
							fileWizard.focus();
						}
					}
					
					function openLinkWizard(cid, lid, action){
						if(!action) {
							action = 'add';
						}
					
						if(!cid) {
							return;
						} else {
							var windowW = 485;
							var windowH = 585;
					
							var windowX = (screen.width / 2) - (windowW / 2);
							var windowY = (screen.height / 2) - (windowH / 2);
					
							linkWizard = window.open('<?php echo ENTRADA_URL; ?>/link-wizard-course.php?action=' + action + '&id=' + cid + ((lid) ? '&lid=' + lid : ''), 'linkWizard', 'width='+windowW+', height='+windowH+', scrollbars=no, resizable=yes');
							linkWizard.blur();
							window.focus();
					
							linkWizard.resizeTo(windowW, windowH);
							linkWizard.moveTo(windowX, windowY);
					
							linkWizard.focus();
						}
					}
	
					function confirmFileDelete() {
						ask_user = confirm("Press OK to confirm that you would like to delete the selected file or files from this course, otherwise press Cancel.");
	
						if (ask_user == true) {
							$('file-listing').submit();
						} else {
							return false;
						}
					}
	
					function confirmLinkDelete() {
						ask_user = confirm("Press OK to confirm that you would like to delete the selected link or links from this course, otherwise press Cancel.");
	
						if (ask_user == true) {
							$('link-listing').submit();
						} else {
							return false;
						}
					}
					
					var text = new Array();
					
					function objectiveClick(element, id, default_text) {
						if (element.checked) {
							var textarea = document.createElement('textarea');
							textarea.name = 'objective_text['+id+']';
							textarea.id = 'objective_text_'+id;
							if (text[id] != null) {
								textarea.innerHTML = text[id];
							} else {
								textarea.innerHTML = default_text;
							}
							textarea.className = "expandable objective";
							$('objective_'+id+"_append").insert({after: textarea});
							setTimeout('new ExpandableTextarea($("objective_text_'+id+'"));', 100);
						} else {
							if ($('objective_text_'+id)) {
								text[id] = $('objective_text_'+id).value;
								$('objective_text_'+id).remove();
							}
						}
					}
					</script>				
					<?php
	
					$sub_query		= "SELECT `proxy_id` FROM `course_contacts` WHERE `course_contacts`.`course_id`=".$db->qstr($COURSE_ID)." AND `course_contacts`.`contact_type` = 'director' ORDER BY `contact_order` ASC";
					$sub_results	= $db->GetAll($sub_query);
					if($sub_results) {
						foreach($sub_results as $sub_result) {
							$OTHER_DIRECTORS[] = $sub_result["proxy_id"];
						}
					}
					require_once(ENTRADA_ABSOLUTE."/javascript/courses.js.php");

					courses_subnavigation($course_details);

					echo "<h1>".html_encode($course_details["course_name"])."</h1>\n";
	
					if($SUCCESS) {
						echo display_success();
					}
	
					if($NOTICE) {
						echo display_notice();
					}
	
					if($ERROR) {
						echo display_error();
					}
	
					?>
					<a name="course-details-section"></a>
					<h2 title="Course Details Section"><?php echo $module_singular_name; ?> Details</h2>
					<div id="course-details-section">
						<form action="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?<?php echo replace_query(); ?>" method="post">
						<input type="hidden" name="type" value="text" />
						<table style="width: 100%" cellspacing="0" cellpadding="2" border="0">
							<colgroup>
								<col width="22%" />
								<col width="78%" />
							</colgroup>
							<tfoot>
								<tr>
									<td colspan="2" style="text-align: right; padding-top: 5px"><input type="submit" value="Save" /></td>
								</tr>
							</tfoot>
							<tbody>
							<?php
							echo "<tr>\n";
							echo "	<td><label for=\"course_url\" class=\"form-nrequired\">External Website URL</label></td>\n";
							echo "	<td><input type=\"text\" id=\"course_url\" name=\"course_url\" value=\"".((isset($PROCESSED["course_url"]) && ($PROCESSED["course_url"] != "")) ? html_encode($PROCESSED["course_url"]) : "http://")."\" style=\"width: 450px\" />
									<br /><span class=\"content-small\"><strong>Example:</strong> http://meds.queensu.ca</span></td>\n";
							echo "</tr>\n";
							echo "<tr>\n";
							echo "	<td>&nbsp;</td>\n";
							echo "	<td><span class=\"content-small\"><strong>Please Note:</strong> If you have an external " . strtolower($module_singular_name) . " website or have created a Community for your course, please enter the URL here and a link will be automatically created on the public side.</span></td>\n";
							echo "</tr>\n";
							echo "<tr>\n";
							echo "	<td colspan=\"2\">&nbsp;</td>\n";
							echo "</tr>\n";
							echo "<tr>\n";
							echo "	<td style=\"vertical-align: top\">" . $module_singular_name . " Directors</td>\n";
							echo "	<td>\n";
										$squery		= "	SELECT a.`proxy_id`, CONCAT_WS(' ', b.`firstname`, b.`lastname`) AS `fullname`, b.`email`
														FROM `course_contacts` AS a
														LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
														ON b.`id` = a.`proxy_id`
														WHERE a.`course_id` = ".$db->qstr($course_details["course_id"])."
														AND a.`contact_type` = 'director'
														AND b.`id` IS NOT NULL
														ORDER BY a.`contact_order` ASC";
										$results	= $db->GetAll($squery);
										if($results) {
											foreach($results as $key => $sresult) {
												echo "<a href=\"mailto:".html_encode($sresult["email"])."\">".html_encode($sresult["fullname"])."</a><br />\n";
											}
										} else {
											echo "To Be Announced";
										}
							echo "		</td>\n";
							echo "	</tr>\n";

							echo "	<tr>\n";
							echo "		<td style=\"vertical-align: top\">Curriculum Coordinators</td>\n";
							echo "		<td>\n";
										$squery		= "	SELECT a.`proxy_id`, CONCAT_WS(' ', b.`firstname`, b.`lastname`) AS `fullname`, b.`email`
														FROM `course_contacts` AS a
														LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
														ON b.`id` = a.`proxy_id`
														WHERE a.`course_id` = ".$db->qstr($course_details["course_id"])."
														AND a.`contact_type` = 'ccoordinator'
														AND b.`id` IS NOT NULL
														ORDER BY a.`contact_order` ASC";
										$results	= $db->GetAll($squery);
										if($results) {
											foreach($results as $key => $sresult) {
												echo "<a href=\"mailto:".html_encode($sresult["email"])."\">".html_encode($sresult["fullname"])."</a><br />\n";
											}
										} else {
											echo "To Be Announced";
										}
							echo "		</td>\n";
							echo "	</tr>\n";

							if((int) $course_details["pcoord_id"]) {
								echo "<tr>\n";
								echo "    <td>Program Coordinator</td>\n";
								echo "    <td><a href=\"mailto:".get_account_data("email", $course_details["pcoord_id"])."\">".get_account_data("fullname", $course_details["pcoord_id"])."</a></td>\n";
								echo "</tr>\n";
							}

							if((int) $course_details["evalrep_id"]) {
								echo "<tr>\n";
								echo "    <td>Evaluation Rep</td>\n";
								echo "    <td><a href=\"mailto:".get_account_data("email", $course_details["evalrep_id"])."\">".get_account_data("fullname", $course_details["evalrep_id"])."</a></td>\n";
								echo "</tr>\n";
							}

							if((int) $course_details["studrep_id"]) {
								echo "<tr>\n";
								echo "    <td>Student Rep</td>\n";
								echo "    <td><a href=\"mailto:".get_account_data("email", $course_details["studrep_id"])."\">".get_account_data("fullname", $course_details["studrep_id"])."</a></td>\n";
								echo "</tr>\n";
							}
							echo "<tr>\n";
							echo "	<td colspan=\"2\">&nbsp;</td>\n";
							echo "</tr>\n";
							echo "<tr>\n";
							echo "	<td style=\"vertical-align: top\"><label for=\"course_description\" class=\"form-nrequired\">" . $module_singular_name . " Description</label></td>\n";
							echo "	<td>\n";
							echo "		<textarea id=\"course_description\" name=\"course_description\" style=\"width: 100%; height: 150px\" cols=\"70\" rows=\"10\">".((isset($PROCESSED["course_description"])) ? html_encode(trim(strip_selected_tags($PROCESSED["course_description"], array("font")))) : "")."</textarea>";
							echo "	</td>\n";
							echo "</tr>\n";
							echo "<tr>\n";
							echo "	<td colspan=\"2\">&nbsp;</td>\n";
							echo "</tr>\n";
							echo "<tr>\n";
							echo "	<td style=\"vertical-align: top\"><label for=\"course_message\" class=\"form-nrequired\">Director's Message</label></td>\n";
							echo "	<td>\n";
							echo "		<textarea id=\"course_message\" name=\"course_message\" style=\"width: 100%; height: 150px\" cols=\"70\" rows=\"10\">".((isset($PROCESSED["course_message"])) ? html_encode(trim(strip_selected_tags($PROCESSED["course_message"], array("font")))) : "")."</textarea>";
							echo "	</td>\n";
							echo "</tr>\n";
							?>
							</tbody>
						</table>
						</form>
					</div>
					<?php
					$query = "	SELECT COUNT(*) FROM course_objectives WHERE course_id = ".$db->qstr($COURSE_ID);
					$result = $db->GetOne($query);
					
					
					if ($result) {
						?>
						<a name="course-objectives-section"></a>
						<h2 title="Course Objectives Section"><?php echo $module_singular_name; ?> Objectives</h2>
						<div id="course-objectives-section">
							<form action="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?<?php echo replace_query(); ?>" method="post">
							<input type="hidden" name="type" value="objectives" />
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
							?>
							<table style="width: 100%" cellspacing="0" cellpadding="2" border="0">
							<colgroup>
								<col width="22%" />
								<col width="78%" />
							</colgroup>
							<tfoot>
								<tr>
									<td colspan="2" style="text-align: right; padding-top: 5px"><input type="submit" value="Save" /></td>
								</tr>
							</tfoot>
							<tbody>
								<tr>
									<td colspan="2">&nbsp;</td>
								</tr>
								<tr>
									<td colspan="2">
										<?php
										echo "<h3>Clinical Presentations</h3>";
										 
										$query = "	SELECT b.*
													FROM `course_objectives` AS a
													JOIN `global_lu_objectives` AS b
													ON a.`objective_id` = b.`objective_id`
													JOIN `objective_organisation` AS c
													ON b.`objective_id` = c.`objective_id`
													WHERE a.`objective_type` = 'event'
													AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
													AND b.`objective_active` = '1'
													AND a.`course_id` = ".$db->qstr($COURSE_ID)."
													GROUP BY b.`objective_id`
													ORDER BY b.`objective_order`";
										$results = $db->GetAll($query);
																				
										if ($results) {
											echo "<ul class=\"objectives\">\n";
											foreach ($results as $result) {
												if ($result["objective_name"]) {
													echo "	<li>".$result["objective_name"]."</li>\n";
												}
											}
											echo "</ul>\n";
										} else {
											echo "<div class=\"display-notice\">While clinical presentations may be used to illustrate concepts in this course, there are no specific presentations from the Medical Council of Canada that have been selected.</div>";
										}
										?>
									</td>
								</tr>
								<tr>
									<td colspan="2">&nbsp;</td>
								</tr>
								<tr>
									<td colspan="2">
										<div id="objectives_list">
										<h3>Curriculum Objectives</h3>
										<strong>The learner will be able to:</strong>
										<?php echo event_objectives_in_list($course_objectives, $top_level_id, $top_level_id, true, false, 1, true, true, "primary", false, $COURSE_ID); ?>
										</div>
									</td>
								</tr>
							</tbody>
							</table>
							</form>
						</div>
						<?php
						if ((@is_array($edit_ajax)) && (@count($edit_ajax))) {
							echo "<script type=\"text/javascript\">\n";
							foreach ($edit_ajax as $objective_id) {
								echo "var editor_".$objective_id." = new Ajax.InPlaceEditor('objective_description_".$objective_id."', '".ENTRADA_RELATIVE."/api/objective-details.api.php', { rows: 7, cols: 62, okText: \"Save Changes\", cancelText: \"Cancel Changes\", externalControl: \"edit_mode_".$objective_id."\", submitOnBlur: \"true\", callback: function(form, value) { return 'id=".$objective_id."&cids=".$COURSE_ID."&objective_details='+escape(value) } });\n";
							}
							echo "</script>\n";
						}
					}
					?>
					<a name="course-resources-section"></a>
					<h2 title="Course Resources Section"><?php echo $module_singular_name; ?> Resources</h2>
					<div id="course-resources-section">
						<div style="margin-bottom: 15px">
							<div style="float: left; margin-bottom: 5px">
								<h3>Attached Files</h3>
							</div>
							<div style="float: right; margin-bottom: 5px">
								<ul class="page-action">
									<li><a href="javascript: openFileWizard('<?php echo $COURSE_ID; ?>', 0, 'add')">Add A File</a></li>
								</ul>
							</div>
							<div class="clear"></div>
							<?php
							$query		= "SELECT * FROM `course_files` WHERE `course_id`=".$db->qstr($COURSE_ID)." ORDER BY `file_category` ASC, `file_title` ASC";
							$results	= ((USE_CACHE) ? $db->CacheGetAll(CACHE_TIMEOUT, $query) : $db->GetAll($query));
							echo "<form id=\"file-listing\" action=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query()."\" method=\"post\">\n";
							echo "<input type=\"hidden\" name=\"type\" value=\"files\" />\n";
							echo "<table class=\"tableList\" cellspacing=\"0\" summary=\"List of Files\">\n";
							echo "<colgroup>\n";
							echo "	<col class=\"modified\" style=\"width: 50px\" />\n";
							echo "	<col class=\"file-category\" />\n";
							echo "	<col class=\"title\" />\n";
							echo "	<col class=\"date\" />\n";
							echo "	<col class=\"date\" />\n";
							echo "	<col class=\"accesses\" />\n";
							echo "</colgroup>\n";
							echo "<thead>\n";
							echo "	<tr>\n";
							echo "		<td class=\"modified\">&nbsp;</td>\n";
							echo "		<td class=\"file-category sortedASC\"><div class=\"noLink\">Category</div></td>\n";
							echo "		<td class=\"title\">File Title</td>\n";
							echo "		<td class=\"date-small\">Accessible Start</td>\n";
							echo "		<td class=\"date-small\">Accessible Finish</td>\n";
							echo "		<td class=\"accesses\">Saves</td>\n";
							echo "	</tr>\n";
							echo "</thead>\n";
							echo "<tfoot>\n";
							echo "	<tr>\n";
							echo "		<td>&nbsp;</td>\n";
							echo "		<td colspan=\"5\" style=\"padding-top: 10px\">\n";
							echo			(($results) ? "<input type=\"button\" class=\"button\" value=\"Delete Selected\" onclick=\"confirmFileDelete()\" />" : "&nbsp;")."\n";
							echo "		</td>\n";
							echo "	</tr>\n";
							echo "</tfoot>\n";
							echo "<tbody>\n";
							if($results) {
								foreach($results as $result) {
									$filename	= $result["file_name"];
									$parts		= pathinfo($filename);
									$ext		= $parts["extension"];
	
									echo "<tr id=\"file-".$result["id"]."\">\n";
									echo "	<td class=\"modified\" style=\"width: 50px; white-space: nowrap\">\n";
									echo "		<input type=\"checkbox\" name=\"delete[]\" value=\"".$result["id"]."\" style=\"vertical-align: middle\" />\n";
									echo "		<a href=\"".ENTRADA_URL."/file-course.php?id=".$result["id"]."\"><img src=\"".ENTRADA_URL."/images/btn_save.gif\" width=\"16\" height=\"16\" alt=\"Download ".html_encode($result["file_name"])." to your computer.\" title=\"Download ".html_encode($result["file_name"])." to your computer.\" style=\"vertical-align: middle\" border=\"0\" /></a>\n";
									echo "	</td>\n";
									echo "	<td class=\"file-category\">".((isset($RESOURCE_CATEGORIES["course"][$result["file_category"]])) ? html_encode($RESOURCE_CATEGORIES["course"][$result["file_category"]]) : "Unknown Category")."</td>\n";
									echo "	<td class=\"title\">\n";
									echo "		<img src=\"".ENTRADA_URL."/serve-icon.php?ext=".$ext."\" width=\"16\" height=\"16\" alt=\"".strtoupper($ext)." Document\" title=\"".strtoupper($ext)." Document\" style=\"vertical-align: middle\" />";
									echo "		<a href=\"javascript: openFileWizard('".$COURSE_ID."', '".$result["id"]."', 'edit')\" title=\"Click to edit ".html_encode($result["file_title"])."\" style=\"font-weight: bold\">".html_encode($result["file_title"])."</a>";
									echo "	</td>\n";
									echo "	<td class=\"date-small\"><span class=\"content-date\">".(((int) $result["valid_from"]) ? date(DEFAULT_DATE_FORMAT, $result["valid_from"]) : "No Restrictions")."</span></td>\n";
									echo "	<td class=\"date-small\"><span class=\"content-date\">".(((int) $result["valid_until"]) ? date(DEFAULT_DATE_FORMAT, $result["valid_until"]) : "No Restrictions")."</span></td>\n";
									echo "	<td class=\"accesses\" style=\"text-align: center\">".$result["accesses"]."</td>\n";
									echo "</tr>\n";
								}
							} else {
								echo "<tr>\n";
								echo "	<td colspan=\"6\">\n";
								echo "		<div class=\"content-small\" style=\"margin-top: 3px; margin-bottom: 5px\">There have been no files added to this " . strtolower($module_singular_name) . ". To <strong>add a new file</strong>, simply click the Add File button.</div>\n";
								echo "	</td>\n";
								echo "</tr>\n";
							}
							echo "	</tbody>\n";
							echo "	</table>\n";
							echo "	</form>\n";
							?>
						</div>
	
						<div style="margin-bottom: 15px">
							<div style="float: left; margin-bottom: 5px">
								<h3>Attached Links</h3>
							</div>
							<div style="float: right; margin-bottom: 5px">
								<ul class="page-action">
									<li><a href="javascript: openLinkWizard('<?php echo $COURSE_ID; ?>', 0, 'add')">Add A Link</a></li>
								</ul>
							</div>
							<div class="clear"></div>
							<?php
							$query		= "SELECT * FROM `course_links` WHERE `course_id`=".$db->qstr($COURSE_ID)." ORDER BY `link_title` ASC";
							$results	= ((USE_CACHE) ? $db->CacheGetAll(CACHE_TIMEOUT, $query) : $db->GetAll($query));
							echo "<form id=\"link-listing\" action=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query()."\" method=\"post\">\n";
							echo "<input type=\"hidden\" name=\"type\" value=\"links\" />\n";
							echo "<table class=\"tableList\" cellspacing=\"0\" summary=\"List of Linked Resources\">\n";
							echo "<colgroup>\n";
							echo "	<col class=\"modified\" style=\"width: 50px\" />\n";
							echo "	<col class=\"title\" />\n";
							echo "	<col class=\"date\" />\n";
							echo "	<col class=\"date\" />\n";
							echo "	<col class=\"accesses\" />\n";
							echo "</colgroup>\n";
							echo "<thead>\n";
							echo "	<tr>\n";
							echo "		<td class=\"modified\">&nbsp;</td>\n";
							echo "		<td class=\"title sortedASC\"><div class=\"noLink\">Linked Resource</div></td>\n";
							echo "		<td class=\"date-small\">Accessible Start</td>\n";
							echo "		<td class=\"date-small\">Accessible Finish</td>\n";
							echo "		<td class=\"accesses\">Hits</td>\n";
							echo "	</tr>\n";
							echo "</thead>\n";
							echo "<tfoot>\n";
							echo "	<tr>\n";
							echo "		<td>&nbsp;</td>\n";
							echo "		<td colspan=\"4\" style=\"padding-top: 10px\">\n";
							echo 			(($results) ? "<input type=\"button\" class=\"button\" value=\"Delete Selected\" onclick=\"confirmLinkDelete()\" />" : "&nbsp;")."\n";
							echo "		</td>\n";
							echo "	</tr>\n";
							echo "</tfoot>\n";
							echo "<tbody>\n";
							if($results) {
								foreach($results as $result) {
									echo "<tr>\n";
									echo "	<td class=\"modified\" style=\"width: 50px; white-space: nowrap\">\n";
									echo "		<input type=\"checkbox\" name=\"delete[]\" value=\"".$result["id"]."\" style=\"vertical-align: middle\" />\n";
									echo "		<a href=\"".ENTRADA_URL."/link-course.php?id=".$result["id"]."\" target=\"_blank\"><img src=\"".ENTRADA_URL."/images/url-visit.gif\" width=\"16\" height=\"16\" alt=\"Visit ".html_encode($result["link"])."\" title=\"Visit ".html_encode($result["link"])."\" style=\"vertical-align: middle\" border=\"0\" /></a>\n";
									echo "	</td>\n";
									echo "	<td class=\"title\" style=\"white-space: normal; overflow: visible\">\n";
									echo "		<a href=\"javascript: openLinkWizard('".$COURSE_ID."', '".$result["id"]."', 'edit')\" title=\"Click to edit ".html_encode($result["link"])."\" style=\"font-weight: bold\">".(($result["link_title"] != "") ? html_encode($result["link_title"]) : $result["link"])."</a>\n";
									echo "	</td>\n";
									echo "	<td class=\"date-small\"><span class=\"content-date\">".(((int) $result["valid_from"]) ? date(DEFAULT_DATE_FORMAT, $result["valid_from"]) : "No Restrictions")."</span></td>\n";
									echo "	<td class=\"date-small\"><span class=\"content-date\">".(((int) $result["valid_until"]) ? date(DEFAULT_DATE_FORMAT, $result["valid_until"]) : "No Restrictions")."</span></td>\n";
									echo "	<td class=\"accesses\" style=\"text-align: center\">".$result["accesses"]."</td>\n";
									echo "</tr>\n";
								}
							} else {
								echo "<tr>\n";
								echo "	<td colspan=\"5\">\n";
								echo "		<div class=\"content-small\" style=\"margin-top: 3px; margin-bottom: 5px\">There have been no links added to this " . strtolower($module_singular_name) . ". To <strong>add a new link</strong>, simply click the Add Link button.</div>\n";
								echo "	</td>\n";
								echo "</tr>\n";
							}
							echo "</tbody>\n";
							echo "</table>\n";
							echo "</form>\n";
							?>
						</div>
					</div>
					<?php
					/**
					 * Sidebar item that will provide the links to the different sections within this page.
					 */
					$sidebar_html  = "<ul class=\"menu\">\n";
					$sidebar_html .= "	<li class=\"link\"><a href=\"#course-details-section\" onclick=\"$('course-details-section').scrollTo(); return false;\" title=\"Course Details\">" . $module_singular_name . " Details</a></li>\n";
					$sidebar_html .= "	<li class=\"link\"><a href=\"#course-objectives-section\" onclick=\"$('course-objectives-section').scrollTo(); return false;\" title=\"Course Objectives\">" . $module_singular_name . " Objectives</a></li>\n";
					$sidebar_html .= "	<li class=\"link\"><a href=\"#course-resources-section\" onclick=\"$('course-resources-section').scrollTo(); return false;\" title=\"Course Resources\">" . $module_singular_name . " Resources</a></li>\n";
					$sidebar_html .= "</ul>\n";
		
					new_sidebar_item("Page Anchors", $sidebar_html, "page-anchors", "open", "1.9");
					
					/**
					 * Sidebar item that will provide link to reports.
					 */
					$sidebar_html  = "<ul class=\"menu\">\n";
					$sidebar_html .= "	<li class=\"link\"><a href=\"".ENTRADA_URL."/admin/courses?id=".$COURSE_ID."&section=course-eventtype-report\" title=\"Event Types Report\">Event Types Report</a></li>\n";
					$sidebar_html .= "</ul>\n";
		
					new_sidebar_item("Reports", $sidebar_html, "reports", "open", "1.9");
					
				}
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "In order to edit a course you must provide a valid course identifier. The provided ID does not exist in this system.";

			echo display_error();

			application_log("notice", "Failed to provide a valid course identifer when attempting to edit a course.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to edit a course you must provide the course identifier.";

		echo display_error();

		application_log("notice", "Failed to provide course identifer when attempting to edit a course.");
	}
}
?>