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
 * This file is used to edit existing events in the entrada.events table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 */

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVENTS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("event", "update", false)) {

	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/eventtypes_list.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	echo "<script language=\"text/javascript\">var DELETE_IMAGE_URL = '".ENTRADA_URL."/images/action-delete.gif';</script>";

	if (isset($_GET["mode"]) && $_GET["mode"] == "draft") {
		$is_draft				= true;

		$tables['events']		= 'draft_events';
		$tables['audience']		= 'draft_audience';
		$tables['contacts']		= 'draft_contacts';
		$tables['event_types']	= 'draft_eventtypes';

		$devent_id				= (int) $_GET["id"];
		$where_query			= 'WHERE `devent_id` = '.$db->qstr($devent_id);
	} else {
		$tables['events']		= 'events';
		$tables['audience']		= 'event_audience';
		$tables['contacts']		= 'event_contacts';
		$tables['event_types']	= 'event_eventtypes';
		$where_query			= 'WHERE `event_id` = '.$db->qstr($EVENT_ID);
	}

	if ($EVENT_ID) {
		$query = "	SELECT a.*, b.`organisation_id`
					FROM `".$tables['events']."` AS a
					LEFT JOIN `courses` AS b
					ON b.`course_id` = a.`course_id`".
					$where_query;
		$event_info	= $db->GetRow($query);

		if ($event_info) {

			if (!$ENTRADA_ACL->amIAllowed(new EventResource($event_info["event_id"], $event_info["course_id"], $event_info["organisation_id"]), 'update')) {
				application_log("error", "A program coordinator attempted to edit an event [".$EVENT_ID."] that they were not the coordinator for.");
				header("Location: ".ENTRADA_URL."/admin/".$MODULE);
				exit;
			} else {
				$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/events?".replace_query(array("section" => "edit", "id" => $EVENT_ID)), "title" => "Editing Event");

				$PROCESSED["associated_faculty"] = array();
				$PROCESSED["event_audience_type"] = "course";
				$PROCESSED["associated_cohort_ids"] = array();
				$PROCESSED["associated_cgroup_ids"] = array();
				$PROCESSED["associated_proxy_ids"] = array();
				$PROCESSED["event_types"] = array();

				if (!$is_draft) {
					events_subnavigation($event_info,'edit');
				} else {
					$EVENT_ID = $event_info["event_id"];
				}

				echo "<h1>Editing Event</h1>\n";

				// Error Checking
				switch($STEP) {
					case 2 :
						/**
						 * Required field "course_id" / Course
						 */
						if ((isset($_POST["course_id"])) && ($course_id = clean_input($_POST["course_id"], array("int")))) {
							$query	= "	SELECT * FROM `courses`
										WHERE `course_id` = ".$db->qstr($course_id)."
										AND (`course_active` = '1' OR `course_id` = ".$db->qstr($event_info["course_id"]).")";
							$result	= $db->GetRow($query);
							if ($result) {
								if ($ENTRADA_ACL->amIAllowed(new EventResource(null, $course_id, $event_info["organisation_id"]), "create")) {
									$PROCESSED["course_id"] = $course_id;
								} else {
									add_error("You do not have permission to add an event for the course you selected. <br /><br />Please re-select the course you would like to place this event into.");
									application_log("error", "A program coordinator attempted to add an event to a course [".$course_id."] they were not the coordinator of.");
								}
							} else {
								add_error("The <strong>Course</strong> you selected does not exist.");
							}
						} else {
							add_error("The <strong>Course</strong> field is a required field.");
						}

						/**
						 * Required field "event_title" / Event Title.
						 */
						if ((isset($_POST["event_title"])) && ($event_title = clean_input($_POST["event_title"], array("notags", "trim")))) {
							$PROCESSED["event_title"] = $event_title;
						} else {
							add_error("The <strong>Event Title</strong> field is required.");
						}

						/**
						 * Required field "event_start" / Event Date & Time Start (validated through validate_calendars function).
						 */
						$start_date = validate_calendars("event", true, false);
						if ((isset($start_date["start"])) && ((int) $start_date["start"])) {
							$PROCESSED["event_start"] = (int) $start_date["start"];
						}

						/**
						 * Non-required field "event_location" / Event Location
						 */
						if ((isset($_POST["event_location"])) && ($event_location = clean_input($_POST["event_location"], array("notags", "trim")))) {
							$PROCESSED["event_location"] = $event_location;
						} else {
							$PROCESSED["event_location"] = "";
						}

						/**
						 * Required fields "eventtype_id" / Event Type
						 */
						if (isset($_POST["eventtype_duration_order"]) && ($tmp_duration_order = clean_input($_POST["eventtype_duration_order"], "trim")) && isset($_POST["duration_segment"]) && ($tmp_duration_segment = $_POST["duration_segment"])) {
							$event_types = explode(",", $tmp_duration_order);
							$eventtype_durations = $tmp_duration_segment;

							if (is_array($event_types) && !empty($event_types)) {
								foreach($event_types as $order => $eventtype_id) {
									$eventtype_id = clean_input($eventtype_id, array("trim", "int"));
									if ($eventtype_id) {
										$query = "SELECT `eventtype_title` FROM `events_lu_eventtypes` WHERE `eventtype_id` = ".$db->qstr($eventtype_id);
										$eventtype_title = $db->GetOne($query);
										if ($eventtype_title) {
											if (isset($eventtype_durations[$order])) {
												$duration = clean_input($eventtype_durations[$order], array("trim", "int"));

												if ($duration <= 0) {
													add_error("The duration of <strong>".html_encode($eventtype_title)."</strong> (".numeric_suffix(($order + 1))." <strong>Event Type</strong> entry) must be greater than zero.");
												}
											} else {
												$duration = 0;

												add_error("The duration of <strong>".html_encode($eventtype_title)."</strong> (".numeric_suffix(($order + 1))." <strong>Event Type</strong> entry) was not provided.");
											}

											$PROCESSED["event_types"][] = array($eventtype_id, $duration, $eventtype_title);
										} else {
											add_error("One of the <strong>event types</strong> you specified was invalid.");
										}
									}
								}
							}
						}

						if (!isset($PROCESSED["event_types"]) || !is_array($PROCESSED["event_types"]) || empty($PROCESSED["event_types"])) {
							add_error("The <strong>Event Types</strong> field is required.");
						}

						/**
						 * Non-required field "associated_faculty" / Associated Faculty (array of proxy ids).
						 * This is actually accomplished after the event is inserted below.
						 */
						if ((isset($_POST["associated_faculty"]))) {
							$associated_faculty = explode(",", $_POST["associated_faculty"]);
							foreach($associated_faculty as $contact_order => $proxy_id) {
								if ($proxy_id = clean_input($proxy_id, array("trim", "int"))) {
									$PROCESSED["associated_faculty"][(int) $contact_order] = $proxy_id;
									$PROCESSED["contact_role"][(int)$contact_order] = $_POST["faculty_role"][(int) $contact_order];
									$PROCESSED["display_role"][$proxy_id] = $_POST["faculty_role"][(int) $contact_order];
								}
							}
						}

						if (isset($_POST["event_audience_type"]) && ($tmp_input = clean_input($_POST["event_audience_type"], "alphanumeric"))) {
							$PROCESSED["event_audience_type"] = $tmp_input;
						}

						switch ($PROCESSED["event_audience_type"]) {
							case "course" :
								$PROCESSED["associated_course_ids"][] = $PROCESSED["course_id"];
							break;
							case "custom" :
								/**
								 * Cohorts.
								 */
								if ((isset($_POST["event_audience_cohorts"]))) {
									$associated_audience = explode(',', $_POST["event_audience_cohorts"]);
									if ((isset($associated_audience)) && (is_array($associated_audience)) && (count($associated_audience))) {
										foreach($associated_audience as $audience_id) {
											if (strpos($audience_id, "group") !== false) {
												if ($group_id = clean_input(preg_replace("/[a-z_]/", "", $audience_id), array("trim", "int"))) {
													$query = "	SELECT *
																FROM `groups`
																WHERE `group_id` = ".$db->qstr($group_id)."
																AND `group_type` = 'cohort'
																AND `group_active` = 1";
													$result	= $db->GetRow($query);
													if ($result) {
														$PROCESSED["associated_cohort_ids"][] = $group_id;
													}
												}
											}
										}
									}
								}

								/**
								 * Course Groups
								 */
								if (isset($_POST["event_audience_course_groups"]) && isset($PROCESSED["course_id"]) && $PROCESSED["course_id"]) {
									$associated_audience = explode(',', $_POST["event_audience_course_groups"]);
									if ((isset($associated_audience)) && (is_array($associated_audience)) && (count($associated_audience))) {
										foreach($associated_audience as $audience_id) {
											if (strpos($audience_id, "cgroup") !== false) {
												if ($cgroup_id = clean_input(preg_replace("/[a-z_]/", "", $audience_id), array("trim", "int"))) {
													$query = "	SELECT *
																FROM `course_groups`
																WHERE `cgroup_id` = ".$db->qstr($cgroup_id)."
																AND `course_id` = ".$db->qstr($PROCESSED["course_id"])."
																AND (`active` = '1' OR `course_id` = ".$db->qstr($event_info["course_id"]).")";
													$result	= $db->GetRow($query);
													if ($result) {
														$PROCESSED["associated_cgroup_ids"][] = $cgroup_id;
													}
												}
											}
										}
									}
								}

								/**
								 * Learners
								 */
								if ((isset($_POST["event_audience_students"]))) {
									$associated_audience = explode(',', $_POST["event_audience_students"]);
									if ((isset($associated_audience)) && (is_array($associated_audience)) && (count($associated_audience))) {
										foreach($associated_audience as $audience_id) {
											if (strpos($audience_id, "student") !== false) {
												if ($proxy_id = clean_input(preg_replace("/[a-z_]/", "", $audience_id), array("trim", "int"))) {
													$query = "	SELECT a.*
																FROM `".AUTH_DATABASE."`.`user_data` AS a
																LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
																ON a.`id` = b.`user_id`
																WHERE a.`id` = ".$db->qstr($proxy_id)."
																AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
																AND b.`account_active` = 'true'
																AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
																AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")";
													$result	= $db->GetRow($query);
													if ($result) {
														$PROCESSED["associated_proxy_ids"][] = $proxy_id;
													}
												}
											}
										}
									}
								}
							break;
							default :
								add_error("Unknown event audience type provided. Unable to proceed.");
							break;
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

						if (isset($_POST["post_action"])) {
							switch($_POST["post_action"]) {
								case "content" :
									$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "content";
								break;
								case "new" :
									$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "new";
								break;
								case "copy" :
									$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "copy";
								break;
								case "draft" :
									$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "draft";
								break;
								case "index" :
								default :
									$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "index";
								break;
							}
						} else {
							$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "content";
						}

						if (!$ERROR) {
							$PROCESSED["updated_date"]	= time();
							$PROCESSED["updated_by"]	= $ENTRADA_USER->getID();

							$PROCESSED["event_finish"] = $PROCESSED["event_start"];
							$PROCESSED["event_duration"] = 0;
							foreach($PROCESSED["event_types"] as $event_type) {
								$PROCESSED["event_finish"] += $event_type[1]*60;
								$PROCESSED["event_duration"] += $event_type[1];
							}

							$PROCESSED["eventtype_id"] = $PROCESSED["event_types"][0][0];

							if ($db->AutoExecute($tables["events"], $PROCESSED, "UPDATE", str_replace("WHERE", "", $where_query))) {
								$query = "DELETE FROM `".$tables["event_types"]."` ".$where_query;
								if ($db->Execute($query)) {
									foreach($PROCESSED["event_types"] as $event_type) {
										$eventtype_data = array("event_id" => $EVENT_ID, "eventtype_id" => $event_type[0], "duration" => $event_type[1]);
										if ($is_draft) {
											$eventtype_data["devent_id"] = $devent_id;
										}
										if (!$db->AutoExecute($tables["event_types"], $eventtype_data, "INSERT")) {
											add_error("There was an error while trying to save the selected <strong>Event Type</strong> for this event.<br /><br />The system administrator was informed of this error; please try again later.");

											application_log("error", "Unable to insert a new event_eventtype record while adding a new event. Database said: ".$db->ErrorMsg());
										}
									}
								} else {
									add_error("There was an error while trying to update the selected <strong>Event Types</strong> for this event.<br /><br />The system administrator was informed of this error; please try again later.");

									application_log("error", "Unable to delete any eventtype records while editing an event. Database said: ".$db->ErrorMsg());
								}

								/**
								 * If there are faculty associated with this event, add them
								 * to the event_contacts table.
								 */
								$query = "DELETE FROM `".$tables["contacts"]."` ".$where_query;
								if ($db->Execute($query)) {
									if ((is_array($PROCESSED["associated_faculty"])) && (count($PROCESSED["associated_faculty"]))) {
										foreach($PROCESSED["associated_faculty"] as $contact_order => $proxy_id) {
											$contact_data = array("event_id" => $EVENT_ID, "proxy_id" => $proxy_id,"contact_role"=>$PROCESSED["contact_role"][$contact_order], "contact_order" => (int) $contact_order, "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID());
											if ($is_draft) {
												$contact_data["devent_id"] = $devent_id;
											}
											if (!$db->AutoExecute($tables["contacts"], $contact_data, "INSERT")) {
												add_error("There was an error while trying to attach an <strong>Associated Faculty</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.");

												application_log("error", "Unable to insert a new event_contact record while adding a new event. Database said: ".$db->ErrorMsg());
											}
										}
									}
								}

								$query = "DELETE FROM `".$tables["audience"]."` ".$where_query;
								if ($db->Execute($query)) {
									switch ($PROCESSED["event_audience_type"]) {
										case "course" :
											/**
											 * Course ID (there is only one at this time, but this processes more than 1).
											 */
											if (count($PROCESSED["associated_course_ids"])) {
												foreach($PROCESSED["associated_course_ids"] as $course_id) {
													$audience_data = array("event_id" => $EVENT_ID, "audience_type" => "course_id", "audience_value" => (int) $course_id, "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID());
													if ($is_draft) {
														$audience_data["devent_id"] = $devent_id;
													}
													if (!$db->AutoExecute($tables["audience"], $audience_data, "INSERT")) {
														add_error("There was an error while trying to attach the <strong>Course ID</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.");

														application_log("error", "Unable to insert a new event_audience, course_id record while adding a new event. Database said: ".$db->ErrorMsg());
													}
												}
											}
										break;
										case "custom" :
											/**
											 * Cohort
											 */
											if (count($PROCESSED["associated_cohort_ids"])) {
												foreach($PROCESSED["associated_cohort_ids"] as $group_id) {
													$audience_data = array("event_id" => $EVENT_ID, "audience_type" => "cohort", "audience_value" => (int) $group_id, "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID());
													if ($is_draft) {
														$audience_data["devent_id"] = $devent_id;
													}
													if (!$db->AutoExecute($tables["audience"], $audience_data, "INSERT")) {
														$ERROR++;
														$ERRORSTR[] = "There was an error while trying to attach the selected <strong>Cohort</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.".$db->ErrorMsg();

														application_log("error", "Unable to insert a new event_audience, cohort record while adding a new event. Database said: ".$db->ErrorMsg());
													}
												}
											}

											/**
											 * Course Groups
											 */
											if (count($PROCESSED["associated_cgroup_ids"])) {
												foreach($PROCESSED["associated_cgroup_ids"] as $cgroup_id) {
													$audience_data = array("event_id" => $EVENT_ID, "audience_type" => "group_id", "audience_value" => (int) $cgroup_id, "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID());
													if ($is_draft) {
														$audience_data["devent_id"] = $devent_id;
													}
													if (!$db->AutoExecute($tables["audience"], $audience_data, "INSERT")) {
														$ERROR++;
														$ERRORSTR[] = "There was an error while trying to attach the selected <strong>Group</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.";

														application_log("error", "Unable to insert a new event_audience, group_id record while adding a new event. Database said: ".$db->ErrorMsg());
													}
												}
											}

											/**
											 * Learners
											 */
											if (count($PROCESSED["associated_proxy_ids"])) {
												foreach($PROCESSED["associated_proxy_ids"] as $proxy_id) {
													$audience_data = array("event_id" => $EVENT_ID, "audience_type" => "proxy_id", "audience_value" => (int) $proxy_id, "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID());
													if ($is_draft) {
														$audience_data["devent_id"] = $devent_id;
													}
													if (!$db->AutoExecute($tables["audience"], $audience_data, "INSERT")) {
														$ERROR++;
														$ERRORSTR[] = "There was an error while trying to attach the selected <strong>Proxy ID</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.";

														application_log("error", "Unable to insert a new event_audience, proxy_id record while adding a new event. Database said: ".$db->ErrorMsg());
													}
												}
											}
										break;
										default :
											add_error("There was no audience information provided, so this event is without an audience.");
										break;
									}
									/**
									 * Remove attendance records for anyone who is no longer a valid audience member of the course.
									 */
									$audience = events_fetch_event_audience_attendance($EVENT_ID);
									if ($audience) {
										$valid_audience = array();
										foreach ($audience as $learner){
											$valid_audience[] = $learner["id"];

										}

										if (!empty($valid_audience)) {
											$query = "DELETE FROM `event_attendance` WHERE `event_id` = ".$db->qstr($EVENT_ID)." AND `proxy_id` NOT IN (".implode(",", $valid_audience).")";
											$db->Execute($query);
										}

									} else {
										$query = "DELETE FROM `event_attendance` WHERE `event_id` = ".$db->qstr($EVENT_ID);
										$db->Execute($query);
									}

								} else {
									application_log("error", "Unable to delete audience details from event_audience table during an edit. Database said: ".$db->ErrorMsg());
								}

								switch($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"]) {
									case "content" :
										$url	= ENTRADA_URL."/admin/events?section=content&id=".$EVENT_ID;
										$msg	= "You will now be redirected to the event content page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
									break;
									case "new" :
										$url	= ENTRADA_URL."/admin/events?section=add";
										$msg	= "You will now be redirected to add a new event; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
									break;
									case "copy" :
										$url	= ENTRADA_URL."/admin/events?section=add";
										$msg	= "You will now be redirected to add a copy of the last event; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
										$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["copy"] = $PROCESSED;
									break;
									case "draft" :
										$url	= ENTRADA_URL."/admin/events/drafts?section=edit&draft_id=".$event_info["draft_id"];
										$msg	= "You will now be redirected to the draft managment page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
									break;
									case "index" :
									default :
										$url	= ENTRADA_URL."/admin/events";
										$msg	= "You will now be redirected to the event index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
									break;
								}

								if (!$ERROR) {
									$query = "	SELECT b.*
												FROM `community_courses` AS a
												LEFT JOIN `community_pages` AS b
												ON a.`community_id` = b.`community_id`
												LEFT JOIN `community_page_options` AS c
												ON b.`community_id` = c.`community_id`
												WHERE c.`option_title` = 'show_history'
												AND c.`option_value` = 1
												AND b.`page_url` = 'course_calendar'
												AND b.`page_active` = 1
												AND a.`course_id` = ".$db->qstr($PROCESSED["course_id"]);
									$result = $db->GetRow($query);
									if ($result) {
										$COMMUNITY_ID = $result["community_id"];
										$PAGE_ID = $result["cpage_id"];
										communities_log_history($COMMUNITY_ID, $PAGE_ID, $EVENT_ID, "community_history_edit_learning_event", 1);
									}

									$SUCCESS++;
									$SUCCESSSTR[] = "You have successfully edited <strong>".html_encode($PROCESSED["event_title"])."</strong> in the system.<br /><br />".$msg;
									$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";

									application_log("success", "Event [".$EVENT_ID."] has been modified.");
								}
							} else {
								add_error("There was a problem updating this event in the system. The system administrator was informed of this error; please try again later.".$db->ErrorMsg());

								application_log("error", "There was an error updating event_id [".$EVENT_ID."]. Database said: ".$db->ErrorMsg());
							}
						}

						if ($ERROR) {
							$STEP = 1;
						}
					break;
					case 1 :
					default :
						$PROCESSED	= $event_info;

						/**
						 * Add existing event type segments to the processed array.
						 */
						$query = "	SELECT a.`eventtype_id`, a.`duration`, b.`eventtype_title`
									FROM `".$tables["event_types"]."` AS a
									LEFT JOIN `events_lu_eventtypes` AS b
									ON b.`eventtype_id` = a.`eventtype_id` ".
									$where_query."
									ORDER BY a.`eventtype_id` ASC";
						$results = $db->GetAll($query);
						if ($results) {
							foreach ($results as $contact_order => $result) {
								$PROCESSED["event_types"][] = array($result["eventtype_id"], $result["duration"], $result["eventtype_title"]);
							}
						}

						/**
						 * Add any existing associated faculty from the event_contacts table
						 * into the $PROCESSED["associated_faculty"] array.
						 */
						$query = "SELECT * FROM `".$tables["contacts"]."` ".$where_query." ORDER BY `contact_order` ASC";
						$results = $db->GetAll($query);
						if ($results) {
							foreach($results as $contact_order => $result) {
								$PROCESSED["associated_faculty"][(int) $contact_order] = $result["proxy_id"];
								$PROCESSED["display_role"][(int)$result["proxy_id"]] = $result["contact_role"];
							}
						}

						$query = "SELECT * FROM `".$tables["audience"]."` ".$where_query;
						$results = $db->GetAll($query);
						if ($results) {
							$PROCESSED["event_audience_type"] = "custom";

							foreach($results as $result) {
								switch($result["audience_type"]) {
									case "course_id" :
										$PROCESSED["event_audience_type"] = "course";

										$PROCESSED["associated_course_ids"] = (int) $result["audience_value"];
									break;
									case "cohort" :
										$PROCESSED["associated_cohort_ids"][] = (int) $result["audience_value"];
									break;
									case "group_id" :
										$PROCESSED["associated_cgroup_ids"][] = (int) $result["audience_value"];
									break;
									case "proxy_id" :
										$PROCESSED["associated_proxy_ids"][] = (int) $result["audience_value"];
									break;
								}
							}
						}
					break;
				}

				// Display Content
				switch($STEP) {
					case 2 :
						display_status_messages();
					break;
					case 1 :
					default :

						$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";

						$LASTUPDATED = $result["updated_date"];

						/**
						 * Compiles the full list of faculty members.
						 */
						$FACULTY_LIST = array();
						$query = "	SELECT a.`id` AS `proxy_id`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, a.`organisation_id`
									FROM `".AUTH_DATABASE."`.`user_data` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
									ON b.`user_id` = a.`id`
									WHERE b.`app_id` = '".AUTH_APP_ID."'
									AND (b.`group` = 'faculty' OR (b.`group` = 'resident' AND b.`role` = 'lecturer'))
									ORDER BY a.`lastname` ASC, a.`firstname` ASC";
						$results = $db->GetAll($query);
						if ($results) {
							foreach($results as $result) {
								$FACULTY_LIST[$result["proxy_id"]] = array('proxy_id'=>$result["proxy_id"], 'fullname'=>$result["fullname"], 'organisation_id'=>$result['organisation_id']);
							}
						}

						/**
						 * Compiles the list of students.
						 */
						$STUDENT_LIST = array();
						$query = "	SELECT a.`id` AS `proxy_id`, b.`role`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, a.`organisation_id`
									FROM `".AUTH_DATABASE."`.`user_data` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
									ON a.`id` = b.`user_id`
									WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
									AND b.`account_active` = 'true'
									AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
									AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
									AND b.`group` = 'student'
									AND b.`role` >= '".(date("Y") - ((date("m") < 7) ?  2 : 1))."'
									ORDER BY b.`role` ASC, a.`lastname` ASC, a.`firstname` ASC";
						$results = $db->GetAll($query);
						if ($results) {
							foreach($results as $result) {
								$STUDENT_LIST[$result["proxy_id"]] = array("proxy_id" => $result["proxy_id"], "fullname" => $result["fullname"], "organisation_id" => $result["organisation_id"]);
							}
						}

						/**
						 * Compiles the list of groups.
						 */
						$GROUP_LIST = array();
						$query = "	SELECT *
									FROM `course_groups`
									WHERE `course_id` = ".$db->qstr($PROCESSED["course_id"])."
									AND (`active` = '1' OR `course_id` = ".$db->qstr($event_info["course_id"]).")
									ORDER BY LENGTH(`group_name`), `group_name` ASC";
						$results = $db->GetAll($query);
						if ($results) {
							foreach($results as $result) {
								$GROUP_LIST[$result["cgroup_id"]] = $result;
							}
						}

						/**
						 * Compiles the list of groups.
						 */
						$COHORT_LIST = array();
						$query = "	SELECT *
									FROM `groups`
									WHERE `group_active` = '1'
									AND `group_type` = 'cohort'
									ORDER BY `group_name` ASC";
						$results = $db->GetAll($query);
						if ($results) {
							foreach($results as $result) {
								$COHORT_LIST[$result["group_id"]] = $result;
							}
						}

						if ($ERROR) {
							echo display_error();
						}

						$query = "SELECT `organisation_id`, `organisation_title` FROM `".AUTH_DATABASE."`.`organisations` ORDER BY `organisation_title` ASC";
						$organisation_results = $db->GetAll($query);
						if ($organisation_results) {
							$organisations = array();
							foreach ($organisation_results as $result) {
								if ($ENTRADA_ACL->amIAllowed('resourceorganisation'.$result["organisation_id"], 'create')) {
									$organisation_categories[$result["organisation_id"]] = array('text' => $result["organisation_title"], 'value' => 'organisation_'.$result["organisation_id"], 'category'=>true);
								}
							}
						}
						?>
						<form name="editEventForm" id="editEventForm" action="<?php echo ENTRADA_URL; ?>/admin/events?<?php echo replace_query(array("step" => 2)); ?>" method="post">
							<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Editing Event">
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
														<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/events<?php echo (($is_draft) ? "/drafts?section=edit&draft_id=".$event_info["draft_id"] : "" ); ?>'" />
													</td>
													<td style="width: 75%; text-align: right; vertical-align: middle">
														<?php if (!$is_draft) { ?><span class="content-small">After saving:</span>
														<select id="post_action" name="post_action">
															<option value="content"<?php echo (((!isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"])) || ($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "content")) ? " selected=\"selected\"" : ""); ?>>Add content to event</option>
															<option value="new"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "new") ? " selected=\"selected\"" : ""); ?>>Add another event</option>
															<option value="copy"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "copy") ? " selected=\"selected\"" : ""); ?>>Add a copy of this event</option>
															<option value="index"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "index") ? " selected=\"selected\"" : ""); ?>>Return to event list</option>
														</select><?php } else { ?>
														<input type="hidden" id="post_action" name="post_action" value="draft" />
														<?php } ?>
														<input type="submit" class="button" value="Save" />
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</tfoot>
								<tbody>
									<tr>
										<td></td>
										<td><label for="course_id" class="form-required">Select Course</label></td>
										<td>
											<?php
											$query	= "	SELECT `course_id`, `course_name`, `course_code`, `course_active`
														FROM `courses`
														WHERE `organisation_id` = ".$db->qstr($event_info["organisation_id"])."
														AND (`course_active` = '1' OR `course_id` = ".$db->qstr($event_info["course_id"]).")
														ORDER BY `course_code`, `course_name` ASC";
											$results	= $db->GetAll($query);
											if ($results) {
												?>
												<select id="course_id" name="course_id" style="width: 97%">
													<option value="0">-- Select the course this event belongs to --</option>
													<?php
													foreach($results as $result) {
														if ($ENTRADA_ACL->amIAllowed(new EventResource(null, $result["course_id"], $event_info["organisation_id"]), "create") || $ENTRADA_ACL->amIAllowed(new EventClosedResource($EVENT_ID, $result["course_id"], $event_info["organisation_id"]), "update")) {
															echo "<option value=\"".(int) $result["course_id"]."\"".(($PROCESSED["course_id"] == $result["course_id"]) ? " selected=\"selected\"" : "").">".html_encode(($result["course_code"] ? $result["course_code"].": " : "").$result["course_name"])."</option>\n";
                                                        }
													}
													?>
												</select>
												<script type="text/javascript">
												jQuery('#course_id').change(function() {
													var course_id = jQuery('#course_id option:selected').val();

													if (course_id) {
														jQuery('#course_id_path').load('<?php echo ENTRADA_RELATIVE; ?>/admin/events?section=api-course-path&id=' + course_id);
													}

													updateAudienceOptions();
													generateEventAutocomplete();
												});
												</script>
												<?php
											} else {
												echo display_error("You do not have any courses availabe in the system at this time, please add a course prior to adding learning events.");
											}
											?>
										</td>
									</tr>
									<tr>
										<td colspan="3">&nbsp;</td>
									</tr>
									<tr>
										<td></td>
										<td style="vertical-align: top"><label for="event_title" class="form-required">Event Title</label></td>
										<td>
											<div id="course_id_path" class="content-small"><?php echo fetch_course_path($PROCESSED["course_id"]); ?></div>
											<input type="text" id="event_title" name="event_title" value="<?php echo html_encode($PROCESSED["event_title"]); ?>" maxlength="255" style="width: 95%; font-size: 150%; padding: 3px" />
										</td>
									</tr>
									<tr>
										<td colspan="3">&nbsp;</td>
									</tr>
									<?php echo generate_calendars("event", "Event Date & Time", true, true, ((isset($PROCESSED["event_start"])) ? $PROCESSED["event_start"] : 0)); ?>
									<tr>
										<td></td>
										<td><label for="event_location" class="form-nrequired">Event Location</label></td>
										<td><input type="text" id="event_location" name="event_location" value="<?php echo html_encode($PROCESSED["event_location"]); ?>" maxlength="255" style="width: 200px" /></td>
									</tr>
									<tr>
										<td colspan="3">&nbsp;</td>
									</tr>
									<tr>
										<td></td>
										<td style="vertical-align: top"><label for="eventtype_ids" class="form-required">Event Types</label></td>
										<td>
											<?php
											$query = "	SELECT a.* FROM `events_lu_eventtypes` AS a
														LEFT JOIN `eventtype_organisation` AS b
														ON a.`eventtype_id` = b.`eventtype_id`
														LEFT JOIN `".AUTH_DATABASE."`.`organisations` AS c
														ON c.`organisation_id` = b.`organisation_id`
														WHERE b.`organisation_id` = ".$db->qstr($event_info["organisation_id"])."
														AND a.`eventtype_active` = '1'
														ORDER BY a.`eventtype_order` ASC";
											$results = $db->GetAll($query);
											if ($results) {
												?>
												<select id="eventtype_ids" name="eventtype_ids" style="width: 270px">
													<option value="-1">-- Add event segment --</option>
													<?php
													$event_types = array();
													foreach($results as $result) {
														$title = html_encode($result["eventtype_title"]);
														echo "<option value=\"".$result["eventtype_id"]."\">".$title."</option>";
													}
												?>
												</select>
												<?php
											} else {
												echo display_error("No Event Types were found. You will need to add at least one Event Type before continuing.");
											}
											?>
											<div id="duration_notice" class="content-small"><div style="margin: 5px 0 5px 0"><strong>Note:</strong> Select all of the different segments taking place within this learning event. When you select an event type it will appear below, and allow you to change the order and duration of each segment.</div></div>
											<hr />
											<ol id="duration_container" class="sortableList" style="display: none;">
											<?php
		                                    if (is_array($PROCESSED["event_types"])) {
												foreach ($PROCESSED["event_types"] as $eventtype) {
													echo "<li id=\"type_".$eventtype[0]."\" class=\"\">".$eventtype[2]."
															<a href=\"#\" onclick=\"$(this).up().remove(); cleanupList(); return false;\" class=\"remove\"><img src=\"".ENTRADA_URL."/images/action-delete.gif\"></a>
															<span class=\"duration_segment_container\">Duration: <input class=\"duration_segment\" name=\"duration_segment[]\" onchange=\"cleanupList();\" value=\"".$eventtype[1]."\"> minutes</span>
															</li>";
	                                        	}
											}
											?>
											</ol>
											<div id="total_duration" class="content-small">Total time: 0 minutes.</div>
											<input id="eventtype_duration_order" name="eventtype_duration_order" style="display: none;">
										</td>
									</tr>
									<tr>
										<td colspan="3">&nbsp;</td>
									</tr>
									<tr>
										<td></td>
										<td style="vertical-align: top"><label for="faculty_name" class="form-nrequired">Associated Faculty</label></td>
										<td>
											<input type="text" id="faculty_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle" />
											<?php
											$ONLOAD[] = "faculty_list = new AutoCompleteList({ type: 'faculty', url: '". ENTRADA_RELATIVE ."/api/personnel.api.php?type=faculty', remove_image: '". ENTRADA_RELATIVE ."/images/action-delete.gif'})";
											?>
											<div class="autocomplete" id="faculty_name_auto_complete"></div>
											<input type="hidden" id="associated_faculty" name="associated_faculty" />
											<input type="button" class="button-sm" id="add_associated_faculty" value="Add" style="vertical-align: middle" />
											<span class="content-small">(<strong>Example:</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?>)</span>
											<script type="text/javascript">
											jQuery(function(){
												jQuery("#faculty_list img.list-cancel-image").live("click", function(){
													var proxy_id = jQuery(this).attr("rel");
													if ($('faculty_'+proxy_id)) {
														var associated_faculty = jQuery("#associated_faculty").val().split(",");
														var remove_index = associated_faculty.indexOf(proxy_id);

														associated_faculty.splice(remove_index, 1);

														jQuery("#associated_faculty").val(associated_faculty.join());

														$('faculty_'+proxy_id).remove();
													}
												});
											});
											</script>
											<ul id="faculty_list" class="menu" style="margin-top: 15px">
												<?php
												if (is_array($PROCESSED["associated_faculty"]) && count($PROCESSED["associated_faculty"])) {
													foreach ($PROCESSED["associated_faculty"] as $faculty) {
														if ((array_key_exists($faculty, $FACULTY_LIST)) && is_array($FACULTY_LIST[$faculty])) {
															?>
															<li class="user" id="faculty_<?php echo $FACULTY_LIST[$faculty]["proxy_id"]; ?>" style="cursor: move;margin-bottom:10px;width:350px;"><?php echo $FACULTY_LIST[$faculty]["fullname"]; ?><select name ="faculty_role[]" style="float:right;margin-right:30px;margin-top:-5px;"><option value="teacher" <?php if($PROCESSED["display_role"][$faculty] == "teacher") echo "SELECTED";?>>Teacher</option><option value="tutor" <?php if($PROCESSED["display_role"][$faculty] == "tutor") echo "SELECTED";?>>Tutor</option><option value="ta" <?php if($PROCESSED["display_role"][$faculty] == "ta") echo "SELECTED";?>>Teacher's Assistant</option><option value="auditor" <?php if($PROCESSED["display_role"][$faculty] == "auditor") echo "SELECTED";?>>Auditor</option></select><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" rel="<?php echo $FACULTY_LIST[$faculty]["proxy_id"]; ?>" class="list-cancel-image" /></li>
															<?php
														}
													}
												}
												?>
											</ul>
											<input type="hidden" id="faculty_ref" name="faculty_ref" value="" />
											<input type="hidden" id="faculty_id" name="faculty_id" value="" />
										</td>
									</tr>
								</tbody>
								<tbody id="audience-options">
								<?php
								if ($PROCESSED["course_id"]) {
									require_once(ENTRADA_ABSOLUTE."/core/modules/admin/events/api-audience-options.inc.php");
								}
								?>
								</tbody>
								<?php if (!$is_draft) { ?>
								<tbody>
									<tr>
										<td>&nbsp;</td>
										<td colspan="2">
											<div id="related_events">
												<?php
												if (!$PROCESSED["parent_id"]) {
													require_once("modules/admin/events/api-related-events.inc.php");
												} else {
													$query = "SELECT * FROM `".$tables['events']."` ".$where_query;
													$related_event = $db->GetRow($query);
													if ($related_event) {
														?>
														<div style="margin-top: 15px;">
															<div style="width: 21%; position: relative; float: left;">
																<span class="form-nrequired">Parent Event</span>
															</div>
															<div style="width: 72%; float: left;" id="related_events_list">
																<ul class="menu">
																	<li class="community" id="related_event_<?php echo $related_event["event_id"]; ?>" style="margin-bottom: 5px; width: 550px; height: 1.5em;">
																		<a href="<?php echo ENTRADA_URL; ?>/admin/events?id=<?php echo $related_event["event_id"] ?>&section=edit">
																			<div style="width: 300px; position: relative; float:left; margin-left: 15px;">
																				<?php echo $related_event["event_title"]; ?>
																			</div>
																			<div style="float: left;">
																				<?php
																					echo date(DEFAULT_DATE_FORMAT, $related_event["event_start"]);
																				?>
																			</div>
																		</a>
																	</li>
																</ul>
															</div>
														</div>
														<?php
													}
												}
												?>
											</div>
										</td>
									</tr>
								</tbody>
								<?php } ?>
								<tbody>
									<tr>
										<td colspan="3"><h2>Time Release Options</h2></td>
									</tr>
									<?php echo generate_calendars("viewable", "", true, false, ((isset($PROCESSED["release_date"])) ? $PROCESSED["release_date"] : 0), true, false, ((isset($PROCESSED["release_until"])) ? $PROCESSED["release_until"] : 0)); ?>
								</tbody>
							</table>
						</form>

						<script type="text/javascript">
							var multiselect = [];
							var audience_type;

							function showMultiSelect() {
                                if ($('display-notice-box')) {
                                    $('display-notice-box').hide();
                                }

								$$('select_multiple_container').invoke('hide');
								audience_type = $F('audience_type');
								course_id = $F('course_id');
								var cohorts = $('event_audience_cohorts').value;
								var course_groups = $('event_audience_course_groups').value;
								var students = $('event_audience_students').value;

								if (multiselect[audience_type]) {
									multiselect[audience_type].container.show();
								} else {
									if (audience_type) {
										new Ajax.Request('<?php echo ENTRADA_RELATIVE; ?>/admin/events?section=api-audience-selector', {
											evalScripts : true,
											parameters: {
												'options_for' : audience_type,
												'course_id' : course_id,
												'event_id' : '<?php echo $EVENT_ID; ?>',
												'event_audience_cohorts' : cohorts,
												'event_audience_course_groups' : '<?php echo $course_groups; ?>',
												'event_audience_students' : students
											},
											method: 'post',
											onLoading: function() {
												$('options_loading').show();
											},
											onSuccess: function(response) {
												if (response.responseText) {
													$('options_container').insert(response.responseText);

													if ($(audience_type + '_options')) {

														$(audience_type + '_options').addClassName('multiselect-processed');

														multiselect[audience_type] = new Control.SelectMultiple('event_audience_'+audience_type, audience_type + '_options', {
															checkboxSelector: 'table.select_multiple_table tr td input[type=checkbox]',
															nameSelector: 'table.select_multiple_table tr td.select_multiple_name label',
															filter: audience_type + '_select_filter',
															resize: audience_type + '_scroll',
															afterCheck: function(element) {
																var tr = $(element.parentNode.parentNode);
																tr.removeClassName('selected');

																if (element.checked) {
																	tr.addClassName('selected');

																	addAudience(element.id, audience_type);
																} else {
																	removeAudience(element.id, audience_type);
																}
															}
														});

														if ($(audience_type + '_cancel')) {
															$(audience_type + '_cancel').observe('click', function(event) {
																this.container.hide();

																$('audience_type').options.selectedIndex = 0;
																$('audience_type').show();

																return false;
															}.bindAsEventListener(multiselect[audience_type]));
														}

														if ($(audience_type + '_close')) {
															$(audience_type + '_close').observe('click', function(event) {
																this.container.hide();

																$('audience_type').clear();

																return false;
															}.bindAsEventListener(multiselect[audience_type]));
														}

														multiselect[audience_type].container.show();
													}
												} else {
													new Effect.Highlight('audience_type', {startcolor: '#FFD9D0', restorecolor: 'true'});
													new Effect.Shake('audience_type');
												}
											},
											onError: function() {
												alert("There was an error retrieving the requested audience. Please try again.");
											},
											onComplete: function() {
												$('options_loading').hide();
											}
										});
									}
								}
								return false;
							}

							function addAudience(element, audience_id) {
								if (!$('audience_'+element)) {
									$('audience_list').innerHTML += '<li class="' + (audience_id == 'students' ? 'user' : 'group') + '" id="audience_'+element+'" style="cursor: move;">'+$($(element).value+'_label').innerHTML+'<img src="<?php echo ENTRADA_RELATIVE; ?>/images/action-delete.gif" onclick="removeAudience(\''+element+'\', \''+audience_id+'\');" class="list-cancel-image" /></li>';
									$$('#audience_list div').each(function (e) { e.hide(); });

									Sortable.destroy('audience_list');
									Sortable.create('audience_list');
								}
							}

							function removeAudience(element, audience_id) {
								$('audience_'+element).remove();
								Sortable.destroy('audience_list');
								Sortable.create('audience_list');
								if ($(element)) {
									$(element).checked = false;
								}
								var audience = $('event_audience_'+audience_id).value.split(',');
								for (var i = 0; i < audience.length; i++) {
									if (audience[i] == element) {
										audience.splice(i, 1);
										break;
									}
								}
								$('event_audience_'+audience_id).value = audience.join(',');
							}

							function removeRelatedEvent(event_id) {
								var updater = new Ajax.Updater('related_events', '<?php echo ENTRADA_URL."/admin/events?section=api-related-events";?>',{
									evalScripts: true,
									method:'post',
									parameters: {
										'ajax' : 1,
										'course_id' : $('course_id').value,
										'event_id' : '<?php echo $EVENT_ID; ?>',
										'remove_id' : event_id,
										'related_event_ids_clean' : $F('related_event_ids_clean')
									},
									onLoading: function (transport) {
										$('related_events_list').innerHTML = '<br /><br /><span class="content-small" style="align: center;">Loading... <img src="<?php echo ENTRADA_URL; ?>/images/indicator.gif" style="vertical-align: middle;" /></span>';
									},
									onComplete: function (transport) {
										generateEventAutocomplete();
									}
								});
							}

							function addRelatedEvent(event_id) {
								var updater = new Ajax.Updater('related_events', '<?php echo ENTRADA_URL."/admin/events?section=api-related-events";?>',{
									evalScripts: true,
									method:'post',
									parameters: {
										'ajax' : 1,
										'course_id' : $('course_id').value,
										'event_id' : '<?php echo $EVENT_ID; ?>',
										'add_id' : event_id,
										'related_event_ids_clean' : $F('related_event_ids_clean')
									},
									onLoading: function (transport) {
										$('related_events_list').innerHTML = '<br /><br /><span class="content-small" style="align: center;">Loading... <img src="<?php echo ENTRADA_URL; ?>/images/indicator.gif" style="vertical-align: middle;" /></span>';
									},
									onComplete: function (transport) {
										generateEventAutocomplete();
									}
								});
							}

							var events_updater = null;
							function generateEventAutocomplete() {
								events_updater = new Ajax.Autocompleter('related_event_id', 'events_autocomplete',
								'<?php echo ENTRADA_URL; ?>/api/events-by-id.api.php?parent_id='+$('parent_id').value+'&course_id='+$('course_id').options[$('course_id').selectedIndex].value,
								{
									frequency: 0.2,
									minChars: 1,
									afterUpdateElement: function (text, li) {
										addRelatedEvent(li.id);
									}
								});
							}

							function selectEventAudienceOption(type) {
								if (type == 'custom' && !jQuery('#event_audience_type_custom_options').is(":visible")) {
									jQuery('#event_audience_type_custom_options').slideDown();
								} else if (type != 'custom' && jQuery('#event_audience_type_custom_options').is(":visible")) {
									jQuery('#event_audience_type_custom_options').slideUp();
								}
							}

							function updateAudienceOptions() {
								if ($F('course_id') > 0)  {

									var selectedCourse = '';

									var currentLabel = $('course_id').options[$('course_id').selectedIndex].up().readAttribute('label');

									if (currentLabel != selectedCourse) {
										selectedCourse = currentLabel;
										var cohorts = ($('event_audience_cohorts') ? $('event_audience_cohorts').getValue() : '');
										var course_groups = ($('event_audience_course_groups') ? $('event_audience_course_groups').getValue() : '');
										var students = ($('event_audience_students') ? $('event_audience_students').getValue() : '');

										$('audience-options').show();
										$('audience-options').update('<tr><td colspan="2">&nbsp;</td><td><div class="content-small" style="vertical-align: middle"><img src="<?php echo ENTRADA_RELATIVE; ?>/images/indicator.gif" width="16" height="16" alt="Please Wait" title="" style="vertical-align: middle" /> Please wait while <strong>audience options</strong> are being loaded ... </div></td></tr>');

										new Ajax.Updater('audience-options', '<?php echo ENTRADA_RELATIVE; ?>/admin/events?section=api-audience-options', {
											evalScripts : true,
											parameters : {
												ajax : 1,
												course_id : $F('course_id'),
												event_audience_students: students,
												event_audience_course_groups: course_groups,
												event_audience_cohorts: cohorts
											},
											onSuccess : function (response) {
												if (response.responseText == "") {
													$('audience-options').update('');
													$('audience-options').hide();
												}
											},
											onFailure : function () {
												$('audience-options').update('');
												$('audience-options').hide();
											}
										});
									}
								} else {
									$('audience-options').update('');
									$('audience-options').hide();
								}
							}
						</script>
						<?php
					break;
				}
			}
		} else {
			add_error("In order to edit a event you must provide a valid event identifier. The provided ID does not exist in this system.");

			echo display_error();

			application_log("notice", "Failed to provide a valid event identifer when attempting to edit a event.");
		}
	} else {
		add_error("In order to edit a event you must provide the events identifier.");

		echo display_error();

		application_log("notice", "Failed to provide event identifer when attempting to edit a event.");
	}
}