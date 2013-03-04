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
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <ryan.warner@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVENTS"))) {
	exit;
} else if ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
		header("Location: ".ENTRADA_URL);
		exit;
} else if (!$ENTRADA_ACL->amIAllowed('event', 'create', false)) {
	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[]	= array("url" => "", "title" => "Create New Draft Schedule");
	
	switch ($STEP) {
		case 2 :
			// error checking / sanitization
			if (isset($_POST["draft_name"]) && !empty($_POST["draft_name"])) {
				$PROCESSED["draft_name"] = clean_input($_POST["draft_name"], array("trim"));
			} else {
				add_error("A draft title is required.");
			}
			
			if (isset($_POST["draft_description"]) && !empty($_POST["draft_description"])) {
				$PROCESSED["draft_description"] = clean_input($_POST["draft_description"], array("nohtml"));
			}
			if (isset($_POST["course_ids"])) {
				foreach ($_POST["course_ids"] as $course_id) {
					$PROCESSED["course_ids"][] = (int) $course_id;
				}
			}
			
			/**
			 * Non-required field "draft_start_date" / Draft Start (validated through validate_calendars function).
			 * Non-required field "draft_finish_date" / Draft Finish (validated through validate_calendars function).
			 */
			$draft_date = validate_calendars("copy", true, true, false);
			if ((isset($draft_date["start"])) && ((int) $draft_date["start"])) {
				$PROCESSED["draft_start_date"] = (int) $draft_date["start"];
			} else {
				$PROCESSED["draft_start_date"] = 0;
			}
			if ((isset($draft_date["finish"])) && ((int) $draft_date["finish"])) {
				$PROCESSED["draft_finish_date"] = (int) $draft_date["finish"];
			} else {
				$PROCESSED["draft_finish_date"] = 0;
			}
			
			/**
			 * Required field "new_start" / Event Date & Time Start (validated through validate_calendars function).
			 */
			$start_date = validate_calendars("new", true, false, false);
			if ((isset($start_date["start"])) && ((int) $start_date["start"])) {
				$PROCESSED["new_start_day"] = (int) $start_date["start"];
			}
			
			if (has_error()) {
				$STEP = 1;
			} else {
				
				// create the draft
				$query = "	INSERT INTO `drafts` (`status`, `name`, `description`, `created`) 
							VALUES ('open', ".$db->qstr($PROCESSED["draft_name"]).", ".$db->qstr($PROCESSED["draft_description"]).", ".$db->qstr(time()).")";
				$result = $db->Execute($query);
				$draft_id = $db->Insert_ID();
				
				// grant the active user permission to work on the draft
				$query = "	INSERT INTO `draft_creators` (`draft_id`, `proxy_id`) 
							VALUES (".$db->qstr($draft_id).", ".$db->qstr($ENTRADA_USER->getActiveId()).")";
				$result = $db->Execute($query);
				
				if ($PROCESSED["course_ids"]) {
					foreach ($PROCESSED["course_ids"] as $course_id) {
						// copy the events into the drafts table
						$query = "	SELECT *
									FROM `events` AS a
									WHERE a.`course_id` = ".$db->qstr($course_id)."
									AND a.`event_start` >= ".$db->qstr($PROCESSED["draft_start_date"])."
									AND a.`event_finish` <= ".$db->qstr($PROCESSED["draft_finish_date"])."
									ORDER BY a.`event_start`"; 
						$events = $db->GetAll($query);

						$date_diff = (int) ($PROCESSED["new_start_day"] - $events[0]["event_start"]);

						foreach ($events as $event) {
							$event["draft_id"] = $draft_id;

							// adds the offset time to the event year and week, preserves the day of the week
							$event["event_start"]  = strtotime((date("o", ($event["event_start"] + $date_diff)))."-W".date("W", ($event["event_start"] + $date_diff))."-".date("w", $event["event_start"])." ".date("H:i",$event["event_start"]));
							$event["event_finish"] = strtotime((date("o", ($event["event_finish"] + $date_diff)))."-W".date("W", ($event["event_finish"] + $date_diff))."-".date("w", $event["event_finish"])." ".date("H:i",$event["event_finish"]));
							
							if (!$db->AutoExecute("draft_events", $event, 'INSERT')) {
								add_error("An error occured, an administrator has been notified. Please try again later.");
								application_log("error", "An error occured when inserting an event into a draft event schedule. DB said: ".$db->ErrorMsg());
							} else {
								$devent_id = $db->Insert_ID();
							

								// copy the audience for the event
								$query = "	SELECT * 
											FROM `event_audience`
											WHERE `event_id` = ".$db->qstr($event["event_id"]);
								$audiences = $db->GetAll($query);
								if ($audiences) {
									foreach ($audiences as $audience) {
										$audience["devent_id"] = $devent_id;
										if (!$db->AutoExecute("draft_audience", $audience, 'INSERT')) {
											add_error("An error occured, an administrator has been notified. Please try again later.");
											application_log("error", "An error occured when inserting a draft event audience into a draft event schedule. DB said: ".$db->ErrorMsg());
										}
									}
								}

								// copy the contacts for the event
								$query = "	SELECT * 
											FROM `event_contacts`
											WHERE `event_id` = ".$db->qstr($event["event_id"]);
								$contacts = $db->GetAll($query);
								if ($contacts) {
									foreach ($contacts as $contact) {
										$contact["devent_id"] = $devent_id;
										if (!$db->AutoExecute("draft_contacts", $contact, 'INSERT')) {
											add_error("An error occured, an administrator has been notified. Please try again later.");
											application_log("error", "An error occured when inserting a draft event contact into a draft event schedule. DB said: ".$db->ErrorMsg());
										}
									}
								}

								// copy the eventtypes for the event
								$query = "	SELECT * 
											FROM `event_eventtypes`
											WHERE `event_id` = ".$db->qstr($event["event_id"]);
								$eventtypes = $db->GetAll($query);
								if ($eventtypes) {
									foreach ($eventtypes as $eventtype) {
										$eventtype["devent_id"] = $devent_id;
										if (!$db->AutoExecute("draft_eventtypes", $eventtype, 'INSERT')) {
											add_error("An error occured, an administrator has been notified. Please try again later.");
											application_log("error", "An error occured when inserting a draft eventtype into a draft event schedule. DB said: ".$db->ErrorMsg());
										}
									}
								}
							}
						}

					}
				}
				if (!$ERROR) {
					add_success("This draft was successfully created, you will be redirected in 5 seconds. If you are not redirected please <a href=\"".ENTRADA_URL."/admin/events/drafts?section=edit&amp;draft_id=".$draft_id."\">Click Here</a>.");
					display_success();
					$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/events/drafts?section=edit&draft_id=".$draft_id."\\'', 5000)";
				} else {
					add_error("An error occured while creating this draft. The system administrator has been notified, please try again later.");
					application_log("error", "Error ocurred when creating draft [".$draft_id."]. DB said ".$db->ErrorMsg());
				}
			}
		break;
		case 1 :
		default :
			continue;
		break;
	}
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
			
			if (has_error()) {
				echo display_error();
			}
			if (has_notice()) {
				echo display_notice();
			}
			$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
			$HEAD[]		= "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/picklist.js\"></script>\n";
			$ONLOAD[]	= "$('courses_list').style.display = 'none'";
			/**
			* Fetch all courses into an array that will be used.
			*/
			$query = "SELECT * FROM `courses` WHERE `organisation_id` = ".$ENTRADA_USER->getActiveOrganisation()." ORDER BY `course_code` ASC";
			$courses = $db->GetAll($query);
			if ($courses) {
				foreach ($courses as $course) {
					$course_list[$course["course_id"]] = array("code" => $course["course_code"], "name" => $course["course_name"]);
				}
			}
			?>
			<style type="text/css">
				.new_start_day .ui-datepicker-calendar tbody tr:hover td a {background: url("images/ui-bg_flat_55_fbec88_40x100.png") repeat-x scroll 50% 50% #FBEC88;border: 1px solid #FAD42E;color: #363636;}
			</style>
			<h1>Create New Draft Schedule</h1>
			<div class="no-printing">
				<form action="<?php echo ENTRADA_RELATIVE; ?>/admin/events/drafts?section=create-draft&step=2" method="post" onsubmit="selIt()">
					
					
					<table style="width: 100%" cellspacing="0" cellpadding="2" border="0">
						<colgroup>
							<col style="width: 3%" />
							<col style="width: 20%" />
							<col style="width: 77%" />
						</colgroup>
						<tbody>
							<tr>
								<td colspan="3"><h2>Draft Details</h2></td>
							</tr>
							<tr>
								<td></td>
								<td style="vertical-align: top;"><label class="form-required">Draft Name</label></td>
								<td style="vertical-align: top;"><input type="text" style="width: 95%; padding: 3px" maxlength="255" value="" name="draft_name" id="draft_name"></td>
							</tr>
							<tr>
								<td></td>
								<td style="vertical-align: top;"><label class="form-nrequired">Description</label></td>
								<td style="vertical-align: top;"><textarea type="text" style="width: 95%; padding: 3px; height:60px;" value="" name="draft_description" id="draft_description"></textarea></td>
							</tr>
						</tbody>
					</table>
					<table style="width: 100%" cellspacing="0" cellpadding="2" border="0">
						<colgroup>
							<col style="width: 3%" />
							<col style="width: 20%" />
							<col style="width: 77%" />
						</colgroup>
						<tbody>
							<tr>
								<td colspan="3"><h2>Learning Events</h2></td>
							</tr>
							<tr>
								<td></td>
								<td style="vertical-align: top;"><label class="form-nrequired">Courses Included</label></td>
								<td style="vertical-align: top;">
									<?php
									echo "<select class=\"multi-picklist\" id=\"PickList\" name=\"course_ids[]\" multiple=\"multiple\" size=\"5\" style=\"width: 100%; margin-bottom: 5px\">\n";
											if ((is_array($PROCESSED["course_ids"])) && (count($PROCESSED["course_ids"]))) {
												foreach ($PROCESSED["course_ids"] as $course_id) {
													echo "<option value=\"".(int) $course_id."\">".html_encode($course_list[$course_id]["code"] . " - " . $course_list[$course_id]["name"])."</option>\n";
												}
											}
									echo "</select>\n";
									echo "<div style=\"float: left; display: inline\">\n";
									echo "	<input type=\"button\" id=\"courses_list_state_btn\" class=\"button\" value=\"Show List\" onclick=\"toggle_list('courses_list')\" />\n";
									echo "</div>\n";
									echo "<div style=\"float: right; display: inline\">\n";
									echo "	<input type=\"button\" id=\"courses_list_remove_btn\" class=\"button-remove\" onclick=\"delIt()\" value=\"Remove\" />\n";
									echo "	<input type=\"button\" id=\"courses_list_add_btn\" class=\"button-add\" onclick=\"addIt()\" style=\"display: none\" value=\"Add\" />\n";
									echo "</div>\n";
									echo "<div id=\"courses_list\" style=\"clear: both; padding-top: 3px; display: none\">\n";
									echo "	<h2>Courses List</h2>\n";
									echo "	<select class=\"multi-picklist\" id=\"SelectList\" name=\"other_courses_list\" multiple=\"multiple\" size=\"15\" style=\"width: 100%\">\n";
											if ((is_array($course_list)) && (count($course_list))) {
												foreach ($course_list as $course_id => $course) {
													if (!is_array($PROCESSED["course_ids"])) {
														$PROCESSED["course_ids"] = array();
													}
													if (!in_array($course_id, $PROCESSED["course_ids"])) {
														echo "<option value=\"".(int) $course_id."\">".html_encode($course_list[$course_id]["code"] . " - " . $course_list[$course_id]["name"])."</option>\n";
													}
												}
											}
									echo "	</select>\n";
									echo "	</div>\n";
									echo "	<script type=\"text/javascript\">\n";
									echo "	\$('PickList').observe('keypress', function(event) {\n";
									echo "		if (event.keyCode == Event.KEY_DELETE) {\n";
									echo "			delIt();\n";
									echo "		}\n";
									echo "	});\n";
									echo "	\$('SelectList').observe('keypress', function(event) {\n";
									echo "	    if (event.keyCode == Event.KEY_RETURN) {\n";
									echo "			addIt();\n";
									echo "		}\n";
									echo "	});\n";
									echo "	</script>\n";
									?>
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<?php echo generate_calendars("copy", "", true, true, ((isset($PROCESSED["draft_start"])) ? $PROCESSED["draft_start"] : strtotime("September 1st, ".(date("o") - 1))), true, true, ((isset($PROCESSED["draft_finish"])) ? $PROCESSED["draft_finish"] : time()), false); ?>
							<tr>
								<td colspan="2">&nbsp;</td>
								<td><span class="content-small"><strong>NOTE:</strong> All events in the included courses between these two dates will be copied into this draft.</span></td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<?php echo generate_calendars("new", "", true, true, ((isset($PROCESSED["new_start_day"])) ? $PROCESSED["new_start_day"] : ((isset($PROCESSED["draft_start"])) ? strtotime(date("Y-m-d", strtotime($PROCESSED["draft_start"])) . " +1 year") : strtotime("September 1st, ".(date("o"))))), false, false, 0, false); ?>
							<tr>
								<td colspan="3" style="text-align: right; padding-top: 10px"><input type="submit" class="button" value="Create" /></td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>
		<?php
		break;
	}
}