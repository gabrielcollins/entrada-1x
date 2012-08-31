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
 * Allows students to add electives to the system which still need to be approved.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2009 Queen's University. All Rights Reserved.
 *
 * @version $Id: index.php 600 2009-08-12 15:19:17Z simpson $
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_CLERKSHIP"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('logbook', 'read')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]." and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	if (isset($_GET["id"]) && (clean_input($_GET["id"], "int"))) {
		$RECORD_ID = clean_input($_GET["id"], "int");
	} elseif (isset($_POST["id"]) && (clean_input($_POST["id"], "int"))) {
		$RECORD_ID = clean_input($_POST["id"], "int");
	}
	if ($RECORD_ID) {
		$PROCESSED = $db->GetRow("SELECT * FROM `".CLERKSHIP_DATABASE."`.`logbook_entries` WHERE `lentry_id` = ".$db->qstr($RECORD_ID)." AND `proxy_id` = ".$db->qstr($_SESSION["details"]["id"])." AND `entry_active` = 1");
		if ($PROCESSED) {
			$PROCESSED_OBJECTIVES = $db->GetAll("SELECT * FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_objectives` WHERE `lentry_id` = ".$db->qstr($RECORD_ID));
			$PROCESSED_PROCEDURES = $db->GetAll("SELECT * FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_procedures` WHERE `lentry_id` = ".$db->qstr($RECORD_ID));

			$BREADCRUMB[]	= array("url" => ENTRADA_URL."/clerkship/logbook", "title" => "Manage Logbook");
			$BREADCRUMB[]	= array("url" => ENTRADA_URL."/clerkship/logbook?section=add", "title" => "Editing Patient Encounter");
		
			echo "<h1>Editing Patient Encounter</h1>\n";
			if ((isset($_POST["rotation_id"])) && ($rotation_id = clean_input($_POST["rotation_id"], "int"))) {
				$PROCESSED["rotation_id"] = $rotation_id;
			}
			// Error Checking
			switch ($STEP) {
				case 2 :	
				
				/**
				 * Non-required field "patient" / Patient.
				 */
				if ((isset($_POST["patient_id"])) && ($patient_id = clean_input($_POST["patient_id"], Array("notags","trim")))) {
					if (strlen($patient_id) <= 30) {
						$PROCESSED["patient_info"] = $patient_id;
					} else {
						$ERROR++;
						$ERRORSTR[] = "The length of the <strong>Patient ID</strong> field cannot exceed 30 characters.";
					}
				}
				
				/**
				 * Non-required field "participation_level" / Participation Level.
				 */
				if ((isset($_POST["participation_level"])) && ($participation_level = clean_input($_POST["participation_level"], Array("int")))) {
					if ($participation_level == 1 || $participation_level == 2) {
						$PROCESSED["participation_level"] = $participation_level;
					} else {
						$PROCESSED["participation_level"] = 2;
					}
				} else {
					$PROCESSED["participation_level"] = 2;
				}
				
				/**
				 * Required field "gender" / Gender.
				 */
				if ((isset($_POST["gender"])) && ($gender = ($_POST["gender"] == "m" ? "m" : "f"))) {
					$PROCESSED["gender"] = $gender;
				} else {
					$PROCESSED["gender"] = "";
				}
				
				/**
				 * Required field "agerange" / Age Range.
				 */
				if ((isset($_POST["agerange"])) && ($agerange = clean_input($_POST["agerange"], "int"))) {
					$PROCESSED["agerange_id"] = $agerange;
				} else {
					$ERROR++;
					$ERRORSTR[] = "The <strong>Age Range</strong> field is required.";
				}
					
				/**
				 * Required field "institution" / Institution.
				 */
				if ((isset($_POST["institution_id"])) && ($institution_id = clean_input($_POST["institution_id"], "int"))) {
					$PROCESSED["lsite_id"] = $institution_id;
				} else {
					$ERROR++;
					$ERRORSTR[] = "The <strong>Institution</strong> field is required.";
				}
				
				/**
				 * Required field "location" / Location.
				 */
				if ((isset($_POST["llocation_id"])) && ($location_id = clean_input($_POST["llocation_id"], "int"))) {
					$PROCESSED["llocation_id"] = $location_id;
				} else {
					$ERROR++;
					$ERRORSTR[] = "The <strong>Setting</strong> field is required.";
				}
				
				/**
				 * Required field "reflection" / Reflection on learning experience.
				 */
				if ((isset($_POST["reflection"])) && ($reflection = clean_input($_POST["reflection"], Array("trim", "notags")))) {
					$PROCESSED["reflection"] = $reflection;
				} else {
					$ERROR++;
					$ERRORSTR[] = "The <strong>Reflection on learning experience</strong> field is required. Please include at least a short description of this encounter before continuing.";
				}
		
				/**
				 * Non-required field "comments" / Comments.
				 */
				if ((isset($_POST["comments"])) && ($comments = clean_input($_POST["comments"], Array("trim", "notags")))) {
					$PROCESSED["comments"] = $comments;
				} else {
					$PROCESSED["comments"] = "";
				}
				
				/**
				 * Required field "objectives" / Objectives
				 */
				$PROCESSED_OBJECTIVES = Array();
				if (is_array($_POST["objectives"]) && count($_POST["objectives"])) {
					foreach ($_POST["objectives"] as $objective_id) {
						$PROCESSED_OBJECTIVES[] = Array ("objective_id" => $objective_id);
					}
				}
				
				/**
				 * Non-required field "procedures" / procedures
				 */
				$PROCESSED_PROCEDURES = Array();
				if (is_array($_POST["procedures"]) && count($_POST["procedures"]) && (@count($_POST["procedures"]) == @count($_POST["proc_participation_level"]))) {
					foreach ($_POST["procedures"] as $procedure_id) {
						$PROCESSED_PROCEDURES[] = Array (	"lprocedure_id" => $procedure_id, 
														"level" => $_POST["proc_participation_level"][$procedure_id]
													  );
					}
				}
				
				$encounter_date = validate_calendar("", "encounter", true);	
				if ((isset($encounter_date)) && ((int) $encounter_date)) {
					$PROCESSED["encounter_date"]	= (int) $encounter_date;
				} else {
					$PROCESSED["encounter_date"]	= 0;
				}
				
				if (!$ERROR && (!isset($_POST["allow_save"]) || $_POST["allow_save"])) {
					
					$PROCESSED["proxy_id"] = $_SESSION["details"]["id"];
					
					if ($db->AutoExecute("`".CLERKSHIP_DATABASE."`.`logbook_entries`", $PROCESSED, "UPDATE", "`lentry_id` = ".$db->qstr($RECORD_ID))) {
						$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["chosen_institution"] = $PROCESSED["lsite_id"];
						$db->Execute("DELETE FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_objectives` WHERE `lentry_id` = ".$db->qstr($RECORD_ID));
						foreach ($PROCESSED_OBJECTIVES as $objective) {
							$objective["lentry_id"] = $RECORD_ID;
							$db->AutoExecute("`".CLERKSHIP_DATABASE."`.`logbook_entry_objectives`", $objective, "INSERT");
						}
						$db->Execute("DELETE FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_procedures` WHERE `lentry_id` = ".$db->qstr($RECORD_ID));
						foreach ($PROCESSED_PROCEDURES as $procedure) {
							$procedure["lentry_id"] = $RECORD_ID;
							$db->AutoExecute("`".CLERKSHIP_DATABASE."`.`logbook_entry_procedures`", $procedure, "INSERT");
						}
						
						$url = ENTRADA_URL."/".$MODULE."/logbook";
						$SUCCESS++;
						$SUCCESSSTR[]  	= "You have successfully edited this <strong>Patient Encounter</strong> in the system.<br /><br />Please <a href=\"".$url."\">click here</a> to proceed to the Logged Encounters page or you will be automatically forwarded in 5 seconds.";
						$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";
		
						application_log("success", "New patient encounter [".$ENTRY_ID."] added to the system.");
					} else {
						$ERROR++;
						$ERRORSTR[] = "There was a problem editing this patient encounter in the system. The MEdTech Unit was informed of this error; please try again later.";
		
						application_log("error", "There was an error editing a clerkship logbook entry. Database said: ".$db->ErrorMsg());
					}
				}
				
				if ($ERROR || (isset($_POST["allow_save"]) && !$_POST["allow_save"])) {
					$STEP = 1;
				}
				
				break;
				case 1 :
				default :
					continue;
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

					$NOTICE++;
					$NOTICESTR[] = "If you encounter any issues while using the <strong>Clerkship Logbook</strong>, or have any suggestions to improve the process of adding encounters for you, please contact <a href=\"mailto: james.ellis@queensu.ca\">James Ellis</a> at the MEdTech Unit.";
					echo display_notice();
					
					$HEAD[] 		= "<link href=\"".ENTRADA_URL."/javascript/calendar/css/xc2_default.css\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />";
					$HEAD[] 		= "<script language=\"javascript\" type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/config/xc2_default.js\"></script>\n";
					$HEAD[] 		= "<script language=\"javascript\" type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/script/xc2_inpage.js\"></script>\n";
					require_once(WEBSITE_ABSOLUTE."/javascript/logbook.js.php");			
					if ($ERROR && (!isset($_POST["allow_save"]) || $_POST["allow_save"])) {
						echo display_error();
					}
					?>
					<div id="hoverbox" style="display: none;">
						<span class="content-small">
							This reflection should be a short entry explaining your experiences with the patient. 
							It should be no more than approximately 100 words, and you may use initials to refer to the patient, 
							but no complete data such as their name or record number.
							<br/><br/>
							For example: 
							<br/><br/>
							I spent the evening following Ms. J's labour and participated in her delivery. I was able to do 
							a cervical exam and now feel much more confident in my ability to do this task.  I found that 
							reviewing my Phase II E notes about normal delivery was very useful to reinforce this experience 
							and have also read the relevant chapter in the recommended text again following this L+D shift.
						</span>
					</div>
					<form id="addEncounterForm" action="<?php echo ENTRADA_URL; ?>/clerkship/logbook?<?php echo replace_query(array("step" => 2)); ?>" method="post" onsubmit="$('rotation_id').enable()">
					<input type="hidden" name="id" value="<?php echo $RECORD_ID; ?>" />
					<input type="hidden" value="1" name="allow_save" id="allow_save" />
					<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Adding Patient Encounter">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 20%" />
						<col style="width: 77%" />
					</colgroup>
					
					<tfoot>
						<tr>
							<td colspan="3" style="padding-top: 25px">
								<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
								<tr>
									<td style="width: 25%; text-align: left">
										<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/clerkship'" />
									</td>
									<td style="width: 75%; text-align: right; vertical-align: middle">
										<input type="submit" class="button" value="Submit" />
									</td>
								</tr>
								</table>
							</td>
						</tr>
					</tfoot>
					<tbody>
						<tr>
							<td colspan="3"><h2>Encounter Details</h2></td>
						</tr>
						<?php
							echo generate_calendar("encounter", "Encounter Date", true, ((isset($PROCESSED["encounter_date"])) ? $PROCESSED["encounter_date"] : time()), true);
						?>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr>
							<td></td>
							<td><label for="rotation_id" class="form-required">Rotation</label></td>
							<td>
								<?php 
								$query	= "SELECT a.* FROM `".CLERKSHIP_DATABASE."`.`events` AS a LEFT JOIN `".CLERKSHIP_DATABASE."`.`event_contacts` AS b ON a.`event_id` = b.`event_id` WHERE b.`etype_id` = ".$db->qstr($_SESSION["details"]["id"])." AND a.`event_id` = ".$db->qstr(((int)$PROCESSED["rotation_id"]))." AND a.`event_type` = 'clinical'";
								$found	= ($db->GetRow($query) ? true : false);
								?>
								<select id="rotation_id" name="rotation_id" style="width: 95%<?php echo ($found ? "; display: none" : ""); ?>" onchange="$('allow_save').value = '0';$('addEncounterForm').submit();">
								<option value="0">-- Select Rotation --</option>
								<?php
								$query		= "SELECT a.* FROM `".CLERKSHIP_DATABASE."`.`events` AS a LEFT JOIN `".CLERKSHIP_DATABASE."`.`event_contacts` AS b ON a.`event_id` = b.`event_id` WHERE b.`etype_id` = ".$db->qstr($_SESSION["details"]["id"])." AND a.`event_type` = 'clinical'";
								$results	= $db->GetAll($query);
								if ($results) {
									foreach ($results as $result) {
										echo "<option value=\"".(int) $result["event_id"]."\"".(isset($PROCESSED["rotation_id"]) && $PROCESSED["rotation_id"] == (int)$result["event_id"] ? " selected=\"selected\"" : "").">".$result["event_title"]."</option>\n";
										if (isset($PROCESSED["rotation_id"]) && $PROCESSED["rotation_id"] == (int)$result["event_id"]) {
											$rotation_title = $result["event_title"];
										}
									}
								}
								?>
								</select>
								<?php
								if ($found && isset($rotation_title) && $rotation_title) {
									echo "<div id=\"rotation-title\" style=\"width: 95%\"><span>".$rotation_title."</span><img src=\"".ENTRADA_URL."/images/action-edit.gif\" style=\"float: right; cursor: pointer\" onclick=\"$('rotation-title').hide(); $('rotation_id').show();\"/></div>";
								}
								?>
							</td>
						</tr>
						<tr>
							<td></td>
							<td><label for="institution_id" class="form-required">Institution</label></td>
							<td>
								<select id="institution_id" name="institution_id" style="width: 95%">
								<option value="0">-- Select Institution --</option>
								<?php
								$query		= "SELECT * FROM `".CLERKSHIP_DATABASE."`.`logbook_lu_sites` WHERE `site_type` = ".$db->qstr(CLERKSHIP_SITE_TYPE);
								$results	= $db->GetAll($query);
								if ($results) {
									foreach ($results as $result) {
										echo "<option value=\"".(int) $result["lsite_id"]."\"".((isset($PROCESSED["lsite_id"]) && $PROCESSED["lsite_id"] == $result["lsite_id"]) || (!isset($PROCESSED["lsite_id"]) && isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["chosen_institution"]) && $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["chosen_institution"] == $result["lsite_id"]) ? " selected=\"selected\"" : "").">".$result["site_name"]."</option>\n";
									}
								}
								?>
								</select>
							</td>
						</tr>
						<tr>
							<td></td>
							<td><label for="llocation_id" class="form-required">Setting</label></td>
							<td>
								<select id="llocation_id" name="llocation_id" style="width: 95%">
								<option value="0">-- Select Setting --</option>
								<?php
								$query	= "SELECT * FROM `".CLERKSHIP_DATABASE."`.`categories` WHERE `category_type` = ".$db->qstr($CLERKSHIP_CATEGORY_TYPE_ID)." AND `category_name` = ".$db->qstr("Class of ".$_SESSION["details"]["grad_year"]);
								$result	= $db->GetRow($query);
								if ($result) {
									$query		= "SELECT * FROM `".CLERKSHIP_DATABASE."`.`logbook_lu_locations`";
									$results	= $db->GetAll($query);
									if ($results) {
										foreach ($results as $result) {
											echo "<option value=\"".(int) $result["llocation_id"]."\"".(isset($PROCESSED["llocation_id"]) && $PROCESSED["llocation_id"] == (int)$result["llocation_id"] ? " selected=\"selected\"" : "").">".$result["location"]."</option>\n";
										}
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
							<td><label for="patient_id" class="form-nrequired">Patient ID</label></td>
							<td>
							<input type="text" id="patient_id" name="patient_id" value="<?php echo html_encode($PROCESSED["patient_info"]); ?>" maxlength="50" style="width: 250px" />
							</td>
						</tr>
						<tr>
							<td></td>
							<td><label for="agerange" class="form-required">Patient Age Range</label></td>
							<td>
								<select id="agerange" name="agerange" style="width: 257px">
								<?php
								if (((int)$_GET["event"]) || $PROCESSED["rotation_id"]) {
									$query = "SELECT `category_type` FROM `".CLERKSHIP_DATABASE."`.`categories` AS a LEFT JOIN `".CLERKSHIP_DATABASE."`.`events` AS b ON a.`category_id` = b.`category_id` WHERE b.`event_id` = ".$db->qstr((((int)$_GET["event"]) ? ((int)$_GET["event"]) : $PROCESSED["rotation_id"]));
									$category_type = $db->GetOne($query);
									if ($category_type == "family medicine") {
										$agerange_cat = "5";
									} else {
										$agerange_cat = "0";
									}
								} else {
									$agerange_cat = "0";
								}
								$query		= "SELECT * FROM `".CLERKSHIP_DATABASE."`.`logbook_lu_agerange` WHERE `rotation_id` = ".$db->qstr($agerange_cat);
								$results	= $db->GetAll($query);
								if ($results) {
									echo "<option value=\"0\"".((!isset($PROCESSED["agerange_id"])) ? " selected=\"selected\"" : "").">-- Select Age Range --</option>\n";
									foreach ($results as $result) {
										echo "<option value=\"".(int) $result["agerange_id"]."\"".(isset($PROCESSED["agerange_id"]) && $PROCESSED["agerange_id"] == (int)$result["agerange_id"] ? " selected=\"selected\"" : "").">".$result["age"]."</option>\n";
									}
								} else {
									echo "<option value=\"0\"".((!isset($PROCESSED["agerange_id"])) ? " selected=\"selected\"" : "").">-- Age Range --</option>\n";
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
							<td style="vertical-align: top"><label for="gender" class="form-nrequired">Patient Gender</label></td>
							<td style="vertical-align: top">
								<input type="radio" name="gender" id="gender_female" value="f"<?php echo (((!isset($PROCESSED["gender"])) || ((isset($PROCESSED["gender"])) && ($PROCESSED["gender"]) == "f")) ? " checked=\"checked\"" : ""); ?> /> <label for="gender_female">Female</label><br />
								<input type="radio" name="gender" id="gender_male" value="m"<?php echo (((isset($PROCESSED["gender"])) && $PROCESSED["gender"] == "m") ? " checked=\"checked\"" : ""); ?> /> <label for="gender_male">Male</label>
							</td>
						</tr>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr>
							<td></td>
							<td style="vertical-align: top"><label for="participation_level" class="form-nrequired">Level of Participation</label></td>
							<td style="vertical-align: top">
								<select id="participation_level" name="participation_level" style="width: 257px;">
									<option value="0">-- Select a Level of Participation --</option>
									<option value="1"<?php echo (((int)$PROCESSED["participation_level"]) == 1 ? " selected=\"selected\"" : ""); ?>>Assisted</option>
									<option value="2"<?php echo (((int)$PROCESSED["participation_level"]) == 2 ? " selected=\"selected\"" : ""); ?>>Participated</option>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr>
							<td></td>
							<td style="vertical-align: top">
								<div style="position: relative; text-align: left;">
									<label for="objective_id" class="form-required">Clinical Presentations</label>
									<br/><br/>
									<span style="display: none;" id="objective-loading" class="content-small">Loading... <img src="<?php echo ENTRADA_URL; ?>/images/indicator.gif" style="vertical-align: middle;" /></span>
								</div>
							</td>
							<td>
								<?php 
								$query		= "	SELECT c.`rotation_id`
												FROM `".CLERKSHIP_DATABASE."`.`events` AS a
												JOIN `".CLERKSHIP_DATABASE."`.`event_contacts` AS b
												ON b.`event_id` = a.`event_id`
												LEFT JOIN `".CLERKSHIP_DATABASE."`.`categories` AS c
												ON a.`category_id` = c.`category_id`
												WHERE b.`econtact_type` = 'student'
												AND b.`etype_id` = ".$db->qstr($_SESSION["details"]["id"])."
												AND a.`event_finish` < ".$db->qstr(time())."
												GROUP BY c.`rotation_id`";
								$rotations = $db->GetAll($query);
								if ($rotations) {
									$past_rotations = "";
									foreach ($rotations as $row) {
										if ($row["rotation_id"]) {
											if ($past_rotations) {
												$past_rotations .= ",".$db->qstr($row["rotation_id"]);
											} else {
												$past_rotations = $db->qstr($row["rotation_id"]);
											}
										}
									}
									$query = "	SELECT a.`objective_id`, COUNT(b.`objective_id`) AS `recorded`, a.`number_required` AS `required`
												FROM `".CLERKSHIP_DATABASE."`.`logbook_mandatory_objectives` AS a
												LEFT JOIN `".CLERKSHIP_DATABASE."`.`logbook_entry_objectives` AS b
												ON a.`objective_id` = b.`objective_id`
												AND b.`lentry_id` NOT IN 
												(
													SELECT `lentry_id` FROM `".CLERKSHIP_DATABASE."`.`logbook_entries`
													WHERE `entry_active` = '0' 
													AND `proxy_id` = ".$db->qstr($_SESSION["details"]["id"])."
												)
												AND b.`lentry_id` IN
												(
													SELECT `lentry_id` FROM `".CLERKSHIP_DATABASE."`.`logbook_entries`
													WHERE `entry_active` = '1' 
													AND `proxy_id` = ".$db->qstr($_SESSION["details"]["id"])."
												)
												WHERE a.`rotation_id` IN (".$past_rotations.")
												GROUP BY a.`objective_id`";
									$results = $db->GetAll($query);
									if ($results) {
										foreach ($results as $objective) {
											if ($objective["required"] > $objective["recorded"] && $objective["objective_id"]) {
												if ($objective_ids) {
													$objective_ids .= ",".$db->qstr($objective["objective_id"]);
												} else {
													$objective_ids = $db->qstr($objective["objective_id"]);
												}
											}
										}
									}
									$query = "	SELECT a.`lprocedure_id`, COUNT(b.`lprocedure_id`) AS `recorded`, a.`number_required` AS `required`
												FROM `".CLERKSHIP_DATABASE."`.`logbook_preferred_procedures` AS a
												LEFT JOIN `".CLERKSHIP_DATABASE."`.`logbook_entry_procedures` AS b
												ON a.`lprocedure_id` = b.`lprocedure_id`
												AND b.`lentry_id` NOT IN 
												(
													SELECT `lentry_id` FROM `".CLERKSHIP_DATABASE."`.`logbook_entries`
													WHERE `entry_active` = '0' 
													AND `proxy_id` = ".$db->qstr($_SESSION["details"]["id"])."
												)
												AND b.`lentry_id` IN
												(
													SELECT `lentry_id` FROM `".CLERKSHIP_DATABASE."`.`logbook_entries`
													WHERE `entry_active` = '1' 
													AND `proxy_id` = ".$db->qstr($_SESSION["details"]["id"])."
												)
												WHERE a.`rotation_id` IN (".$past_rotations.")
												AND a.`grad_year_min` <= ".$db->qstr(get_account_data("grad_year", $_SESSION["details"]["id"]))."
												GROUP BY a.`lprocedure_id`";
									$results = $db->GetAll($query);
									if ($results) {
										foreach ($results as $procedure) {
											if ($procedure["required"] > $procedure["recorded"]) {
												if ($procedure_ids) {
													$procedure_ids .= ",".$db->qstr($procedure["lprocedure_id"]);
												} else {
													$procedure_ids = $db->qstr($procedure["lprocedure_id"]);
												}
											}
										}
									}
								}
								$query = "SELECT * FROM `".CLERKSHIP_DATABASE."`.`global_lu_rotations` WHERE `rotation_id` = (SELECT `rotation_id` FROM `".CLERKSHIP_DATABASE."`.`categories` WHERE `category_id` = (SELECT `category_id` FROM `".CLERKSHIP_DATABASE."`.`events` WHERE `event_id` = ".$db->qstr($PROCESSED["rotation_id"])."))";
								$rotation = $db->GetRow($query); 
								if ($rotation) {
									$rotation_id = $rotation["rotation_id"];
									?>
									<input type="radio" name="objective_display_type" id="objective_display_type_rotation" onclick="showRotationObjectives()" checked="checked" /> <label for="objective_display_type_rotation">Show only clinical presentations for <span id="rotation_title_display" style="font-weight: bold"><?php echo $rotation["rotation_title"]; ?></span></label><br />
									<input type="radio" name="objective_display_type" id="objective_display_type_all" onclick="showAllObjectives()" /> <label for="objective_display_type_all">Show all clinical presentations</label><br />
									<?php
									if (isset($objective_ids) && $objective_ids) {
									?>
										<input type="radio" name="objective_display_type" id="objective_display_type_deficient" onclick="showDeficientObjectives()" /> <label for="objective_display_type_deficient">Show only clinical presentations which are deficient from past rotations.</label>
									<?php
									}
									?>
									<br /><br />
									<?php
								} elseif (isset($objective_ids) && $objective_ids) {
									?>
									<input type="radio" name="objective_display_type" id="objective_display_type_all" onclick="showAllObjectives()" checked="checked" /> <label for="objective_display_type_all">Show all clinical presentations</label><br />
									<input type="radio" name="objective_display_type" id="objective_display_type_deficient" onclick="showDeficientObjectives()" /> <label for="objective_display_type_deficient">Show only clinical presentations which are deficient from past rotations.</label>
									<br /><br />
									<?php
								}
								echo "<select id=\"rotation_objective_id\" name=\"rotation_objective_id\" onchange=\"addObjective(this.value, 0)\" style=\"width: 95%;\">\n";
								echo "<option value=\"0\"".((!isset($PROCESSED["objective_id"])) ? " selected=\"selected\"" : "").">-- Select Clinical Presentation --</option>\n";
								$query		= "	SELECT DISTINCT * FROM `global_lu_objectives` 
												WHERE `objective_parent` = '200' 
												AND 
												(
													`objective_id` IN 
													(
														SELECT `objective_id` FROM `".CLERKSHIP_DATABASE."`.`logbook_mandatory_objectives` 
														WHERE `rotation_id` = ".$db->qstr($rotation_id)." 
													)
												)
												ORDER BY `objective_name`";
								$results	= $db->GetAll($query);
								if ($results) {
									foreach ($results as $result) {
										echo "<option id=\"rotation-obj-item-".$result["objective_id"]."\" value=\"".(int) $result["objective_id"]."\">".html_encode($result["objective_name"])."</option>\n";
										$children = $db->GetAll("SELECT * FROM `global_lu_objectives` WHERE `objective_parent` = ".$db->qstr($result["objective_id"]));
										if ($children) {
											foreach ($children as $child) {
												echo "<option id=\"rotation-obj-item-".$child["objective_id"]."\" value=\"".(int) $child["objective_id"]."\">".html_encode($child["objective_name"])."</option>\n";
											}
										}
									}
								}
								echo "</select>\n";
								
								echo "<select id=\"deficient_objective_id\" name=\"deficient_objective_id\" onchange=\"addObjective(this.value, 0)\" style=\"width: 95%; display: none;\">\n";
								echo "<option value=\"0\"".((!isset($PROCESSED["objective_id"])) ? " selected=\"selected\"" : "").">-- Select Clinical Presentation --</option>\n";
								
								$query		= "	SELECT DISTINCT * FROM `global_lu_objectives` 
												WHERE `objective_parent` = '200' 
												AND 
												(
													`objective_id` IN (".$objective_ids.")
												)
												ORDER BY `objective_name`";
								$results	= $db->GetAll($query);
								if ($results) {
									foreach ($results as $result) {
										echo "<option id=\"deficient-obj-item-".$result["objective_id"]."\" value=\"".(int) $result["objective_id"]."\">".html_encode($result["objective_name"])."</option>\n";
										$children = $db->GetAll("SELECT * FROM `global_lu_objectives` WHERE `objective_parent` = ".$db->qstr($result["objective_id"]));
										if ($children) {
											foreach ($children as $child) {
												echo "<option id=\"deficient-obj-item-".$child["objective_id"]."\" value=\"".(int) $child["objective_id"]."\">".html_encode($child["objective_name"])."</option>\n";
											}
										}
									}
								}
								echo "</select>\n";
								echo "<select id=\"all_objective_id\" name=\"all_objective_id\" onchange=\"addObjective(this.value, 0)\" style=\"width: 95%; display: none;\">\n";
								echo "<option value=\"0\"".((!isset($PROCESSED["objective_id"])) ? " selected=\"selected\"" : "").">-- Select Clinical Presentation --</option>\n";
								$query		= "SELECT * FROM `global_lu_objectives` WHERE `objective_parent` = '200' ORDER BY `objective_name`";
								$results	= $db->GetAll($query);
								if ($results) {
									foreach ($results as $result) {
										echo "<option id=\"all-obj-item-".$result["objective_id"]."\" value=\"".(int) $result["objective_id"]."\">".html_encode($result["objective_name"])."</option>\n";
										$children = $db->GetAll("SELECT * FROM `global_lu_objectives` WHERE `objective_parent` = ".$db->qstr($result["objective_id"]));
										if ($children) {
											foreach ($children as $child) {
												echo "<option id=\"all-obj-item-".$child["objective_id"]."\" value=\"".(int) $child["objective_id"]."\">".html_encode($child["objective_name"])."</option>\n";
											}
										}
									}
								}
								echo "</select>\n";
								?>
							</td>
						</tr>
						<tr>
							<td colspan="2">&nbsp;</td>
							<td>
								<table class="tableList objectives"<?php echo !is_array($PROCESSED_OBJECTIVES) || !count($PROCESSED_OBJECTIVES) ? " style=\"display: none;\"" : ""; ?> cellspacing="0" cellpadding="0" border="0" id="objective-list">
								<colgroup>
									<col style="width: 8%" />
									<col style="width: 92%" />
								</colgroup>
								<tfoot>
									<tr>
										<td colspan="2"><input type="button" value="Remove Selected" onclick="removeObjectives()"/></td>
									</tr>
								</tfoot>
								<tbody id="objective-list">
								<?php 
								if (is_array($PROCESSED_OBJECTIVES) && count($PROCESSED_OBJECTIVES)) { 
									foreach ($PROCESSED_OBJECTIVES as $objective_id) {
										$objective = $db->GetRow("SELECT * FROM `global_lu_objectives` WHERE `objective_id` = ".$db->qstr($objective_id["objective_id"])." AND (`objective_parent` = '200' OR `objective_parent` IN (SELECT `objective_id` FROM `global_lu_objectives` WHERE `objective_parent` = '200'))");
										if ($objective) {
										?>
											<tr id="objective_<?php echo $objective_id["objective_id"]; ?>_row">
												<td><input type="checkbox" class="objective_delete" value="<?php echo $objective_id["objective_id"]; ?>" /></td>
												<td>
													<label for="delete_objective_<?php echo $objective_id["objective_id"]; ?>"><?php echo $objective["objective_name"]?></label>
													<input type="hidden" name="objectives[<?php echo $objective_id["objective_id"]; ?>]" value="<?php echo $objective_id["objective_id"]; ?>" />
												</td>
											</tr>
										<?php 
										}
									}
								} 
								?>
								</tbody>
								</table>
							</td>
						</tr>
						<tr>
							<td></td>
							<td style="vertical-align: top;">
								<div style="position: relative; text-align: left;">
									<label for="procedure_id" class="form-required">Clinical Tasks</label>
									<br/><br/>
									<span style="display: none;" id="procedure-loading" class="content-small">Loading... <img src="<?php echo ENTRADA_URL; ?>/images/indicator.gif" style="vertical-align: middle;" /></span>
								</div>
							</td>
								<td style="vertical-align: top;">
									<input type="hidden" id="default_procedure_involvement" value="Assisted" />
									<?php
										$query = "	SELECT DISTINCT a.* FROM `".CLERKSHIP_DATABASE."`.`logbook_lu_procedures` AS a
													LEFT JOIN `".CLERKSHIP_DATABASE."`.`logbook_preferred_procedures` AS b
													ON b.`lprocedure_id` = a.`lprocedure_id`
													WHERE a.`lprocedure_id` IN (".$procedure_ids.")
													AND b.`grad_year_min` <= ".$db->qstr(get_account_data("grad_year", $_SESSION["details"]["id"]));
										$deficient_procedures = $db->GetAll($query);
										if ($rotation) {
											$query = "	SELECT DISTINCT a.* FROM `".CLERKSHIP_DATABASE."`.`logbook_lu_procedures` AS a
														LEFT JOIN `".CLERKSHIP_DATABASE."`.`logbook_preferred_procedures` AS b
														ON b.`lprocedure_id` = a.`lprocedure_id`
														WHERE b.`rotation_id` = ".$db->qstr($rotation_id)."
														AND b.`grad_year_min` <= ".$db->qstr(get_account_data("grad_year", $_SESSION["details"]["id"]));
											$preferred_procedures = $db->GetAll($query);
											if ($preferred_procedures) {
												?>
												<input type="radio" name="procedure_display_type" id="procedure_display_type_rotation" onclick="showRotationProcedures()" checked="checked" /> <label for="procedure_display_type_rotation">Show only clinical tasks for <span id="rotation_title_display" style="font-weight: bold"><?php echo $rotation["rotation_title"]; ?></span></label><br/>
												<input type="radio" name="procedure_display_type" id="procedure_display_type_all" onclick="showAllProcedures()" /> <label for="procedure_display_type_all">Show all clinical tasks</label><br />
												<?php
												if ($deficient_procedures) {
												?>
													<input type="radio" name="procedure_display_type" id="procedure_display_type_deficient" onclick="showDeficientProcedures()" /> <label for="procedure_display_type_deficient">Show only clinical tasks which are deficient from past rotations.</label>
												<?php
												}
												?>
												<br /><br />
												<?php
											} elseif ($deficient_procedures) {
											?>
												<input type="radio" name="procedure_display_type" id="procedure_display_type_all" onclick="showAllProcedures()" /> <label for="procedure_display_type_all">Show all clinical tasks</label><br />
												<input type="radio" name="procedure_display_type" id="procedure_display_type_deficient" onclick="showDeficientProcedures()" /> <label for="procedure_display_type_deficient">Show only clinical tasks which are deficient from past rotations.</label>
											<?php
											}
										}
									echo "<select id=\"rotation_procedure_id\" name=\"rotation_procedure_id\" onchange=\"addProcedure(this.value, 0)\" style=\"width: 95%;".(!isset($preferred_procedures) || !$preferred_procedures ? " display: none;" : "")."\">\n";
									echo "<option value=\"0\"".((!isset($PROCESSED["objective_id"])) ? " selected=\"selected\"" : "").">-- Select Clinical Tasks --</option>\n";
									if ($preferred_procedures) {
										foreach ($preferred_procedures as $result) {
											echo "<option id=\"rotation-proc-item-".$result["lprocedure_id"]."\" value=\"".(int) $result["lprocedure_id"]."\">".html_encode($result["procedure"])."</option>\n";
										}
									}
									echo "</select>\n";
									echo "<select id=\"deficient_procedure_id\" name=\"deficient_procedure_id\" onchange=\"addProcedure(this.value, 0)\" style=\"width: 95%; display: none;\">\n";
									echo "<option value=\"0\"".((!isset($PROCESSED["objective_id"])) ? " selected=\"selected\"" : "").">-- Select Clinical Tasks --</option>\n";
									if ($deficient_procedures) {
										foreach ($deficient_procedures as $result) {
											echo "<option id=\"deficient-proc-item-".$result["lprocedure_id"]."\" value=\"".(int) $result["lprocedure_id"]."\">".html_encode($result["procedure"])."</option>\n";
										}
									}
									echo "</select>\n";
									$query = "SELECT * FROM `".CLERKSHIP_DATABASE."`.`logbook_lu_procedures` ORDER BY `procedure`";
									$results = $db->GetAll($query);
									echo "<select id=\"all_procedure_id\" style=\"width: 95%;".(isset($preferred_procedures) && $preferred_procedures ? " display: none;" : "")."\" name=\"all_procedure_id\" onchange=\"addProcedure(this.value, 0)\">\n";
									echo "<option value=\"0\"".((!isset($PROCESSED["procedure_id"])) ? " selected=\"selected\"" : "").">-- Select Clinical Tasks --</option>\n";
									if ($results) {
										foreach ($results as $result) {
											echo "<option id=\"all-proc-item-".$result["lprocedure_id"]."\" value=\"".(int) $result["lprocedure_id"]."\">".html_encode($result["procedure"])."</option>\n";
										}
									}
									echo "</select>\n";
									?>
								</td>
						</tr>
						<tr>
							<td colspan="2">&nbsp;</td>
							<td>
								<table class="tableList procedures"<?php echo !is_array($PROCESSED_PROCEDURES) || !count($PROCESSED_PROCEDURES) ? " style=\"display: none;\"" : ""; ?> cellspacing="0" cellpadding="0" border="0" id="procedure-list">
								<colgroup>
									<col style="width: 8%" />
									<col style="width: 57%" />
									<col style="width: 35%" />
								</colgroup>
								<tfoot>
									<tr>
										<td colspan="3"><input type="button" value="Remove Selected" onclick="removeProcedures()"/></td>
									</tr>
								</tfoot>
								<tbody id="procedure-list">
								<?php 
								if (is_array($PROCESSED_PROCEDURES) && count($PROCESSED_PROCEDURES)) { 
									foreach ($PROCESSED_PROCEDURES as $procedure_id) {
										$procedure = $db->GetRow("SELECT * FROM `".CLERKSHIP_DATABASE."`.`logbook_lu_procedures` WHERE `lprocedure_id` = ".$db->qstr($procedure_id["lprocedure_id"])." ORDER BY `procedure`");
										if ($procedure) {
										?>
											<tr id="procedure_<?php echo $procedure_id["lprocedure_id"]; ?>_row">
												<td><input type="checkbox" class="procedure_delete" value="<?php echo $procedure_id["lprocedure_id"]; ?>" /></td>
												<td class="left"><label for="delete_procedure_<?php echo $procedure_id["lprocedure_id"]; ?>"><?php echo $procedure["procedure"]?></label></td>
												<td style="text-align: right">
													<input type="hidden" name="procedures[<?php echo $procedure_id["lprocedure_id"]; ?>]" value="<?php echo $procedure_id["lprocedure_id"]; ?>" />
													<select name="proc_participation_level[<?php echo $procedure_id["lprocedure_id"]; ?>]" id="proc_<?php echo $procedure_id["lprocedure_id"]; ?>_participation_level" style="width: 150px">
														<option value="1" <?php echo ($procedure_id["level"] == 1 || (!$procedure_id["level"]) ? "selected=\"selected\"" : ""); ?>>Observed</option>
														<option value="2" <?php echo ($procedure_id["level"] == 2 ? "selected=\"selected\"" : ""); ?>>Performed with help</option>
														<option value="3" <?php echo ($procedure_id["level"] == 3 ? "selected=\"selected\"" : ""); ?>>Performed independently</option>
													</select>
												</td>
											</tr>
										<?php 
										}
									}
								} 
								?>
								</tbody>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr>
							<td></td>
							<td style="vertical-align: top"><label for="reflection" class="form-required">Reflection on learning experience </label><a id="tooltip" href="#hoverbox"><img style="border: none;" src="<?php echo ENTRADA_URL; ?>/images/btn_help.gif"/></a></td>
							<td>
								<textarea id="reflection" name="reflection" class="expandable"  maxlength="300" style="width: 95%"><?php echo ((isset($PROCESSED["reflection"])) ? html_encode($PROCESSED["reflection"]) : ""); ?></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr>
							<td></td>
							<td style="vertical-align: top"><label for="comments" class="form-nrequired">Additional Comments </label></td>
							<td>
								<textarea id="comments" name="comments" class="expandable"  maxlength="300" style="width: 95%"><?php echo ((isset($PROCESSED["comments"])) ? html_encode($PROCESSED["comments"]) : ""); ?></textarea>
							</td>
						</tr>					
					</tbody>
					</table>
					</form>
					<?php
				break;
			}
		} else {
			$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

			$ERROR++;
			$ERRORSTR[]	= "This Entry ID is not valid.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";
		
			echo display_error();
		
			application_log("error", "Error, invalid Entry ID [".$RECORD_ID."] supplied for editing a clerkship elective in module [".$MODULE."].");
		}
	} else {
		$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

		$ERROR++;
		$ERRORSTR[]	= "You must provide a valid Entry ID.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";
	
		echo display_error();
	
		application_log("error", "Error, invalid Entry ID [".$RECORD_ID."] supplied for clerkship logbook in module [".$MODULE."].");
	}
}
?>