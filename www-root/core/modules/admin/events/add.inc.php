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
 * This file is used to add events to the entrada.events table.
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
} elseif (!$ENTRADA_ACL->amIAllowed("event", "create", false)) {

	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/eventtypes_list.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	echo "<script language=\"text/javascript\">var DELETE_IMAGE_URL = '".ENTRADA_URL."/images/action-delete.gif';</script>";

	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/events?".replace_query(array("section" => "add")), "title" => "Adding Event");

	$PROCESSED["associated_faculty"] = array();
	$PROCESSED["event_audience_type"] = "course";
	$PROCESSED["associated_cohort_ids"] = array();
	$PROCESSED["associated_cgroup_ids"] = array();
	$PROCESSED["associated_proxy_ids"] = array();
	$PROCESSED["event_types"] = array();

	if (isset($_GET["mode"]) && $_GET["mode"] == "draft") {
		if (isset($_GET["draft_id"]) && (int) $_GET["draft_id"] != 0) {
			$draft_id = (int) $_GET["draft_id"];

			$query = "SELECT `draft_id`, `status` FROM `drafts` WHERE `draft_id` = ".$db->qstr($draft_id);
			$draft_info = $db->GetAssoc($query);

			if (!empty($draft_info) && array_key_exists($draft_id, $draft_info)) {
				switch ($draft_info[$draft_id]) {
					case "approved" :
						add_error("The specified draft has been approved for importation. To add a new event the draft must be <a href=\"".ENTRADA_URL."/admin/events/drafts?section=status&action=reopen&draft_id=".$draft_id."\">reopened</a>.");
					case "open" :
					default :
						$tables['events']		= 'draft_events';
						$tables['audience']		= 'draft_audience';
						$tables['contacts']		= 'draft_contacts';
						$tables['event_types']	= 'draft_eventtypes';
						$is_draft = true;
					break;
				}
			} else {
				add_error("The specified draft id does not exist.");
			}

		} else {
			add_error("A draft id has not been specified.");
		}
	} else {
		$tables['events']		= 'events';
		$tables['audience']		= 'event_audience';
		$tables['contacts']		= 'event_contacts';
		$tables['objectives']	= 'event_objectives';
		$tables['topics']		= 'event_topics';
		$tables['links']		= 'event_links';
		$tables['files']		= 'event_files';
		$tables['event_types']	= 'event_eventtypes';
	}

	echo "<h1>Adding Event</h1>\n";

	// Error Checking
	switch($STEP) {
		case 2 :
			/**
			 * Required field "course_id" / Course
			 */
			if ((isset($_POST["course_id"])) && ($course_id = clean_input($_POST["course_id"], array("int")))) {
				$query	= "	SELECT * FROM `courses`
							WHERE `course_id` = ".$db->qstr($course_id)."
							AND (`course_active` = '1')";
				$result	= $db->GetRow($query);
				if ($result) {
					if ($ENTRADA_ACL->amIAllowed(new EventResource(null, $course_id, $ENTRADA_USER->getActiveOrganisation()), "create")) {
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

									if ($duration <= LEARNING_EVENT_MIN_DURATION) {
										add_error("The duration of <strong>".html_encode($eventtype_title)."</strong> (".numeric_suffix(($order + 1))." <strong>Event Type</strong> entry) must be greater than ".LEARNING_EVENT_MIN_DURATION.".");
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
            $PROCESSED["recurring_events"] = array();
			if (isset($_POST["recurring_event_start"]) && is_array($_POST["recurring_event_start"]) && !empty($_POST["recurring_event_start"])) {
                foreach ($_POST["recurring_event_start"] as $key => $event_start) {
					if (isset($_POST["recurring_event_start_time"][$key]) && $tmp_input = clean_input($_POST["recurring_event_start_time"][$key], array("trim", "striptags"))) {
						$time = $tmp_input;
					} else {
						$time = "00:00";
					}
					$time = strtotime($event_start . " " . $time);
					if ($time) {
						$recurring_event_date = $time;
					} else {
						add_error("One of the <strong>recurring events</strong> did not have a valid start date, please fill out a Event Start for <strong>Event ".($key+1)."</strong> under the Recurring Events now.");
					}
                    if (isset($_POST["recurring_event_title"][$key]) && ($recurring_event_title = clean_input($_POST["recurring_event_title"][$key], array("notags", "trim")))) {
                        $event_finish = $recurring_event_date;
                        $event_duration = 0;
                        foreach($PROCESSED["event_types"] as $event_type) {
                            $event_finish += $event_type[1]*60;
                            $event_duration += $event_type[1];
                        }
                        $PROCESSED["recurring_events"][] = array( "event_title" => $recurring_event_title, 
                                                                "event_start" => $recurring_event_date, 
                                                                "event_finish" => $event_finish, 
                                                                "event_duration" => $event_duration);
                    } else {
                        add_error("One of the <strong>recurring events</strong> did not have a valid title, please fill out a title for <strong>Event ".($key+1)."</strong> under the Recurring Events now.");
                    }
                }
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
						$PROCESSED["contact_role"][(int) $contact_order] = $_POST["faculty_role"][(int)$contact_order];
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
													AND (`active` = '1')";
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
				if ($is_draft) {
					$PROCESSED["draft_id"] = $draft_id;
				}
				$PROCESSED["updated_date"]	= time();
				$PROCESSED["updated_by"]	= $ENTRADA_USER->getID();

				$PROCESSED["event_finish"] = $PROCESSED["event_start"];
				$PROCESSED["event_duration"] = 0;
				foreach($PROCESSED["event_types"] as $event_type) {
					$PROCESSED["event_finish"] += $event_type[1]*60;
					$PROCESSED["event_duration"] += $event_type[1];
				}

				$PROCESSED["eventtype_id"] = $PROCESSED["event_types"][0][0];

				if ($db->AutoExecute($tables["events"], $PROCESSED, "INSERT")) {
					if ($EVENT_ID = $db->Insert_Id()) {
						if ($is_draft) {
							$EVENT_ID = 0;
							$devent_id = $db->Insert_ID();
						};
						foreach($PROCESSED["event_types"] as $event_type) {
							$type_details = array("event_id" => $EVENT_ID, "eventtype_id" => $event_type[0], "duration" => $event_type[1]);
							if ($is_draft) {
								$type_details["devent_id"] = $devent_id;
							}
							if (!$db->AutoExecute($tables["event_types"], $type_details, "INSERT")) {
								add_error("There was an error while trying to save the selected <strong>Event Type</strong> for this event.<br /><br />The system administrator was informed of this error; please try again later.");

								application_log("error", "Unable to insert a new event_eventtype record while adding a new event. Database said: ".$db->ErrorMsg());
							}
						}

						/**
						 * If there are faculty associated with this event, add them
						 * to the event_contacts table.
						 */
						if ((is_array($PROCESSED["associated_faculty"])) && (count($PROCESSED["associated_faculty"]))) {
							foreach($PROCESSED["associated_faculty"] as $contact_order => $proxy_id) {
								$contact_details =  array("event_id" => $EVENT_ID, "proxy_id" => $proxy_id, "contact_role"=>$PROCESSED["contact_role"][$contact_order],"contact_order" => (int) $contact_order, "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID());
								if ($is_draft) {
									$contact_details["devent_id"] =  $devent_id;
								}
								if (!$db->AutoExecute($tables["contacts"], $contact_details, "INSERT")) {
									add_error("There was an error while trying to attach an <strong>Associated Faculty</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.");

									application_log("error", "Unable to insert a new event_contact record while adding a new event. Database said: ".$db->ErrorMsg());
								}
							}
						}

						switch ($PROCESSED["event_audience_type"]) {
							case "course" :
								/**
								 * Course ID (there is only one at this time, but this processes more than 1).
								 */
								if (count($PROCESSED["associated_course_ids"])) {
									foreach($PROCESSED["associated_course_ids"] as $course_id) {
										$audience_details = array("event_id" => $EVENT_ID, "audience_type" => "course_id", "audience_value" => (int) $course_id, "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID());
										if ($is_draft) {
											$audience_details["devent_id"] =  $devent_id;
										}
										if (!$db->AutoExecute($tables["audience"], $audience_details, "INSERT")) {
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
										$audience_details = array("event_id" => $EVENT_ID, "audience_type" => "cohort", "audience_value" => (int) $group_id, "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID());
										if ($is_draft) {
											$audience_details["devent_id"] = $devent_id;
										}
										if (!$db->AutoExecute($tables["audience"], $audience_details, "INSERT")) {
											$ERROR++;
											$ERRORSTR[] = "There was an error while trying to attach the selected <strong>Cohort</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.";

											application_log("error", "Unable to insert a new event_audience, cohort record while adding a new event. Database said: ".$db->ErrorMsg());
										}
									}
								}

								/**
								 * Course Groups
								 */
								if (count($PROCESSED["associated_cgroup_ids"])) {
									foreach($PROCESSED["associated_cgroup_ids"] as $cgroup_id) {
										$audience_details = array("event_id" => $EVENT_ID, "audience_type" => "group_id", "audience_value" => (int) $cgroup_id, "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID());
										if ($is_draft) {
											$audience_details["devent_id"] = $devent_id;
										}
										if (!$db->AutoExecute($tables["audience"], $audience_details, "INSERT")) {
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
										$audience_details = array("event_id" => $EVENT_ID, "audience_type" => "proxy_id", "audience_value" => (int) $proxy_id, "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID());
										if ($is_draft) {
											$audience_details["devent_id"] = $devent_id;
										}
										if (!$db->AutoExecute($tables["audience"], $audience_details, "INSERT")) {
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
                        
                        if (isset($PROCESSED["recurring_events"]) && @count($PROCESSED["recurring_events"]) && !$ERROR) {
                            $query = "UPDATE `events` SET `recurring_id` = ".$db->qstr($EVENT_ID)." WHERE `event_id` = ".$db->qstr($EVENT_ID);
                            $db->Execute($query);
                            $RECURRING_EVENT_RECORD = array();
                            foreach ($PROCESSED["recurring_events"] AS $PROCESSED_RECURRING) {
                                $PROCESSED_RECURRING = array_merge($PROCESSED, $PROCESSED_RECURRING);
                                $PROCESSED_RECURRING["recurring_id"] = $EVENT_ID;
                                if ($db->AutoExecute($tables["events"], $PROCESSED_RECURRING, "INSERT")) {
                                    if ($RECURRING_EVENT_ID = $db->Insert_Id()) {
                                        if ($is_draft) {
                                            $RECURRING_EVENT_ID = 0;
                                            $devent_id = $db->Insert_ID();
                                        };
                                        foreach($PROCESSED["event_types"] as $event_type) {
                                            $type_details = array("event_id" => $RECURRING_EVENT_ID, "eventtype_id" => $event_type[0], "duration" => $event_type[1]);
                                            if ($is_draft) {
                                                $type_details["devent_id"] = $devent_id;
                                            }
                                            if (!$db->AutoExecute($tables["event_types"], $type_details, "INSERT")) {
                                                application_log("error", "Unable to insert a new event_eventtype record while adding a new event. Database said: ".$db->ErrorMsg());
                                            }
                                        }

                                        /**
                                         * If there are faculty associated with this event, add them
                                         * to the event_contacts table.
                                         */
                                        if ((is_array($PROCESSED["associated_faculty"])) && (count($PROCESSED["associated_faculty"]))) {
                                            foreach($PROCESSED["associated_faculty"] as $contact_order => $proxy_id) {
                                                $contact_details =  array("event_id" => $RECURRING_EVENT_ID, "proxy_id" => $proxy_id, "contact_role"=>$PROCESSED["contact_role"][$contact_order],"contact_order" => (int) $contact_order, "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID());
                                                if ($is_draft) {
                                                    $contact_details["devent_id"] =  $devent_id;
                                                }
                                                if (!$db->AutoExecute($tables["contacts"], $contact_details, "INSERT")) {
                                                    application_log("error", "Unable to insert a new event_contact record while adding a new event. Database said: ".$db->ErrorMsg());
                                                }
                                            }
                                        }

                                        switch ($PROCESSED["event_audience_type"]) {
                                            case "course" :
                                                /**
                                                 * Course ID (there is only one at this time, but this processes more than 1).
                                                 */
                                                if (count($PROCESSED["associated_course_ids"])) {
                                                    foreach($PROCESSED["associated_course_ids"] as $course_id) {
                                                        $audience_details = array("event_id" => $RECURRING_EVENT_ID, "audience_type" => "course_id", "audience_value" => (int) $course_id, "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID());
                                                        if ($is_draft) {
                                                            $audience_details["devent_id"] =  $devent_id;
                                                        }
                                                        if (!$db->AutoExecute($tables["audience"], $audience_details, "INSERT")) {
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
                                                        $audience_details = array("event_id" => $RECURRING_EVENT_ID, "audience_type" => "cohort", "audience_value" => (int) $group_id, "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID());
                                                        if ($is_draft) {
                                                            $audience_details["devent_id"] = $devent_id;
                                                        }
                                                        if (!$db->AutoExecute($tables["audience"], $audience_details, "INSERT")) {
                                                            $ERROR++;
                                                            $ERRORSTR[] = "There was an error while trying to attach the selected <strong>Cohort</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.";

                                                            application_log("error", "Unable to insert a new event_audience, cohort record while adding a new event. Database said: ".$db->ErrorMsg());
                                                        }
                                                    }
                                                }

                                                /**
                                                 * Course Groups
                                                 */
                                                if (count($PROCESSED["associated_cgroup_ids"])) {
                                                    foreach($PROCESSED["associated_cgroup_ids"] as $cgroup_id) {
                                                        $audience_details = array("event_id" => $RECURRING_EVENT_ID, "audience_type" => "group_id", "audience_value" => (int) $cgroup_id, "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID());
                                                        if ($is_draft) {
                                                            $audience_details["devent_id"] = $devent_id;
                                                        }
                                                        if (!$db->AutoExecute($tables["audience"], $audience_details, "INSERT")) {
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
                                                        $audience_details = array("event_id" => $RECURRING_EVENT_ID, "audience_type" => "proxy_id", "audience_value" => (int) $proxy_id, "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID());
                                                        if ($is_draft) {
                                                            $audience_details["devent_id"] = $devent_id;
                                                        }
                                                        if (!$db->AutoExecute($tables["audience"], $audience_details, "INSERT")) {
                                                            $ERROR++;
                                                            $ERRORSTR[] = "There was an error while trying to attach the selected <strong>Proxy ID</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.";

                                                            application_log("error", "Unable to insert a new event_audience, proxy_id record while adding a new event. Database said: ".$db->ErrorMsg());
                                                        }
                                                    }
                                                }
                                            break;
                                        }
                                    }
                                }
                            }
                        }

						switch($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"]) {
							case "content" :
								$url	= ENTRADA_URL."/admin/events?section=content&id=".$EVENT_ID;
								$msg	= "You will now be redirected to the event content page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
							break;
							case "new" :
								$url	= ENTRADA_URL."/admin/events?section=add";
								$msg	= "You will now be redirected to add another new event; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
							break;
							case "copy" :
								$url	= ENTRADA_URL."/admin/events?section=add";
								$msg	= "You will now be redirected to add a copy of the last event; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
								$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["copy"] = $PROCESSED;
							break;
							case "draft" :
								$url	= ENTRADA_URL."/admin/events/drafts?section=edit&draft_id=".$draft_id;
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
							if($result){
								$COMMUNITY_ID = $result["community_id"];
								$PAGE_ID = $result["cpage_id"];
								communities_log_history($COMMUNITY_ID, $PAGE_ID, $EVENT_ID, "community_history_add_learning_event", 1);
							}

							$SUCCESS++;
							$SUCCESSSTR[] = "You have successfully added <strong>".html_encode($PROCESSED["event_title"])."</strong> to the system.<br /><br />".$msg;
							$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";

							application_log("success", "New event [".$EVENT_ID."] added to the system.");
						}
					}
				} else {
					add_error("There was a problem inserting this event into the system. The system administrator was informed of this error; please try again later.");

					application_log("error", "There was an error inserting a event. Database said: ".$db->ErrorMsg());
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
            $HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
            $HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/jquery/jquery.timepicker.js\"></script>\n";

            if (isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"]) && $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "copy") {
                $PROCESSED = $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["copy"];
            } else {
                if (isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["copy"])) {
                    unset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["copy"]);
                }
            }

            if (!isset($PROCESSED["course_id"]) || !$PROCESSED["course_id"]) {
                $ONLOAD[] = "selectEventAudienceOption('".$PROCESSED["event_audience_type"]."')";
            }

            /**
             * Compiles the full list of faculty members.
             */
            $FACULTY_LIST = array();
            $query = "SELECT a.`id` AS `proxy_id`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, a.`organisation_id`
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
            $query = "SELECT a.`id` AS `proxy_id`, b.`role`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, a.`organisation_id`
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

            if (isset($PROCESSED["course_id"])) {
                /**
                 * Compiles the list of groups.
                 */
                $GROUP_LIST = array();
                $query = "SELECT *
                            FROM `course_groups`
                            WHERE `course_id` = ".$db->qstr($PROCESSED["course_id"])."
                            AND (`active` = '1')
                            ORDER BY LENGTH(`group_name`), `group_name` ASC";
                $results = $db->GetAll($query);
                if ($results) {
                    foreach($results as $result) {
                        $GROUP_LIST[$result["cgroup_id"]] = $result;
                    }
                }
            }

            /**
             * Compiles the list of groups.
             */
            $COHORT_LIST = array();
            $query = "SELECT *
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
                    if ($ENTRADA_ACL->amIAllowed("resourceorganisation".$result["organisation_id"], "create")) {
                        $organisation_categories[$result["organisation_id"]] = array("text" => $result["organisation_title"], "value" => "organisation_".$result["organisation_id"], "category"=>true);
                    }
                }
            }
            ?>
            <form action="<?php echo ENTRADA_URL; ?>/admin/events?section=add&amp;step=2<?php echo ($is_draft == "true") ? "&mode=draft&draft_id=".$draft_id : ""; ?>" method="post" id="addEventForm" class="form-horizontal">
                <div class="control-group">
                    <label for="course_id" class="control-label form-required">Select Course:</label>
                    <div class="controls">
                        <?php
                                $query = "SELECT `course_id`, `course_name`, `course_code`, `course_active`
                                            FROM `courses`
                                            WHERE `organisation_id` = " . $db->qstr($ENTRADA_USER->getActiveOrganisation()) . "
                                            AND (`course_active` = '1')
                                            ORDER BY `course_code`, `course_name` ASC";
                                $results = $db->GetAll($query);
                                if ($results) {
                                    ?>
                                    <select id="course_id" name="course_id" style="width: 97%">
                                        <option value="0">-- Select the course this event belongs to --</option>
                                        <?php
                                        foreach($results as $result) {
                                            if ($ENTRADA_ACL->amIAllowed(new EventResource(null, $result["course_id"], $ENTRADA_USER->getActiveOrganisation()), "create")) {
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
                                    });
                                    </script>
                                    <?php
                                } else {
                                    echo display_error("You do not have any courses availabe in the system at this time, please add a course prior to adding learning events.");
                                }
                                ?>
                    </div>
                </div>
                <div class="control-group">
                    <label for="event_title" class="control-label form-required">Event Title:</label>
                    <div class="controls">
                        <div id="course_id_path" class="content-small"><?php echo (isset($PROCESSED["course_id"]) && $PROCESSED["course_id"] ? fetch_course_path($PROCESSED["course_id"]) : ""); ?></div>
                        <input type="text" id="event_title" name="event_title" value="<?php echo ((isset($PROCESSED["event_title"]) && $PROCESSED["event_title"]) ? html_encode($PROCESSED["event_title"]) : ""); ?>" maxlength="255" style="width: 95%; font-size: 150%; padding: 3px" />
                    </div>
                </div>
                <div class="control-group">
                    <table>
                        <?php echo generate_calendars("event", "Event Date", true, true, ((isset($PROCESSED["event_start"])) ? $PROCESSED["event_start"] : 0)); ?>
                    </table>
                </div>
                <div class="control-group">
                    <label for="repeat_frequency" class="control-label form-nrequired">Repeat Frequency</label>
                    <div class="controls">
                        <select name="repeat_frequency" id="repeat_frequency">
                            <option value="none">None</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                        <button class="btn pull-right" type="button" id="rebuild_button" style="display: none;" onclick="jQuery('#repeat_frequency').trigger('change')">Rebuild Recurring Events List</button>
                    </div>
                </div>
                <div class="space-below pad-left large"<?php echo (isset($PROCESSED["recurring_events"]) && @count($PROCESSED["recurring_events"]) ? "" : "style=\"display: none;\""); ?> id="recurring-events-list">
                    <?php 
                    if (isset($PROCESSED["recurring_events"]) && @count($PROCESSED["recurring_events"])) {
                        $ONLOAD[] = "jQuery('.inpage-datepicker').datepicker({
                                        dateFormat: 'yy-mm-dd',
                                        maxDate: add_year(new Date('".date("Y-m-d", $PROCESSED["event_start"])."')),
                                        minDate: '".date("Y-m-d", $PROCESSED["event_start"])."'
                                    })";
                        $ONLOAD[] = "jQuery('.timepicker').timepicker({
                                        showPeriodLabels: false
                                    })";
                        $ONLOAD[] = "jQuery('.inpage-add-on').on('click', function() {
                                        if ($(this).siblings('input').is(':enabled')) {
                                            $(this).siblings('input').focus();
                                        }
                                    })";
                        foreach ($PROCESSED["recurring_events"] as $key => $recurring_event) {
                            ?>
                            <div id="recurring-event-<?php echo ($key + 1); ?>" class="row-fluid pad-above<?php echo ($key % 2 == 0 ? " odd" : ""); ?>">
                                <span class="span3 content-small pad-left">
                                    Event <?php echo ($key + 1); ?>:
                                </span>
                                <span class="span8">
                                    <div class="row-fluid">
                                        <label for="recurring_event_title_<?php echo ($key + 1); ?>" class="span2 form-required">Title:</label>
                                        <span class="span7">
                                            <input type="text" id="recurring_event_title_<?php echo ($key + 1); ?>" name="recurring_event_title[]" value="<?php echo html_encode($recurring_event["event_title"]); ?>" maxlength="255" style="width: 95%; font-size: 150%; padding: 3px" />
                                        </span>
                                    </div>
                                    <div class="row-fluid">
                                        <label class="span2" for="recurring_event_start_<?php echo ($key + 1); ?>">Event Start:</label>
                                        <span class="span7">
                                            <div class="input-append">
                                                <input type="text" class="input-small inpage-datepicker" value="<?php echo date("Y-m-d", $recurring_event["event_start"]); ?>" name="recurring_event_start[]" id="recurring_event_start_<?php echo ($key + 1); ?>" />
                                                <span class="add-on pointer inpage-add-on"><i class="icon-calendar"></i></span>
                                            </div>
                                            &nbsp;
                                            <div class="input-append">
                                                <input type="text" class="input-mini timepicker" value="<?php echo date("H:i", $recurring_event["event_start"]); ?>" name="recurring_event_start_time[]" id="recurring_event_start_time_<?php echo ($key + 1); ?>" />
                                                <span class="add-on pointer inpage-add-on"><i class="icon-time"></i></span>
                                            </div>
                                        </span>
                                    </div>
                                </span>
                                <span class="span1 pad-right">
                                    <button type="button" class="close" onclick="jQuery('#recurring-event-<?php echo ($key + 1); ?>').remove()">×</button>
                                </span>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
                <div class="control-group">
                    <label for="event_location" class="control-label form-nrequired">Event Location:</label>
                    <div class="controls">
                        <input type="text" id="event_location" name="event_location" value="<?php echo (isset($PROCESSED["event_location"]) && $PROCESSED["event_location"] ? html_encode($PROCESSED["event_location"]) : ""); ?>" maxlength="255"/>
                    </div>
                </div>
                <div class="control-group">
                    <label for="eventtype_ids" class="control-label form-required">Event Types:</label>
                    <div class="controls">
                        <?php
                                $query = "	SELECT a.* FROM `events_lu_eventtypes` AS a
                                            LEFT JOIN `eventtype_organisation` AS b
                                            ON a.`eventtype_id` = b.`eventtype_id`
                                            LEFT JOIN `".AUTH_DATABASE."`.`organisations` AS c
                                            ON c.`organisation_id` = b.`organisation_id`
                                            WHERE b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
                                            AND a.`eventtype_active` = '1'
                                            ORDER BY a.`eventtype_order` ASC";
                                $results = $db->GetAll($query);
                                if ($results) {
                                    ?>
                                    <select id="eventtype_ids" name="eventtype_ids">
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
                                <div id="duration_notice" style="margin-top: 5px">
                                    <div class="alert alert-info">
                                         <strong>Please Note:</strong> Select all of the different segments taking place within this learning event. When you select an event type it will appear below, and allow you to change the order and duration of each segment.
                                     </div>
                                </div>
                                <ol id="duration_container" class="sortableList" style="display: none;">
                                <?php
                                if (is_array($PROCESSED["event_types"])) {
                                    foreach($PROCESSED["event_types"] as $eventtype) {
                                        echo "<li id=\"type_".$eventtype[0]."\" class=\"\">".$eventtype[2]."
                                                <a href=\"#\" onclick=\"$(this).up().remove(); cleanupList(); return false;\" class=\"remove\"><img src=\"".ENTRADA_URL."/images/action-delete.gif\"></a>
                                                <span class=\"duration_segment_container\">Duration: <input type=\"text\" class=\"input-mini duration_segment\" name=\"duration_segment[]\" onchange=\"cleanupList();\" value=\"".$eventtype[1]."\"> minutes</span>
                                                </li>";
                                    }
                                }
                                ?>
                                </ol>
                                <div id="total_duration" class="content-small">Total time: 0 minutes.</div>
                                <input id="eventtype_duration_order" name="eventtype_duration_order" style="display: none;">
                    </div>
                </div>
                <div class="control-group">
                    <label for="faculty_name" class="control-label form-nrequired">Associated Faculty:</label>
                    <div class="controls">
                        <input type="text" id="faculty_name" name="fullname" autocomplete="off" placeholder="Example: <?php echo html_encode($ENTRADA_USER->getLastname().", ".$ENTRADA_USER->getFirstname()); ?>" />
                        <?php
                        $ONLOAD[] = "faculty_list = new AutoCompleteList({ type: 'faculty', url: '". ENTRADA_RELATIVE ."/api/personnel.api.php?type=faculty', remove_image: '". ENTRADA_RELATIVE ."/images/action-delete.gif'})";
                        ?>
                        <div class="autocomplete" id="faculty_name_auto_complete"></div>
                        <input type="hidden" id="associated_faculty" name="associated_faculty" />
                        <input type="button" class="btn" id="add_associated_faculty" value="Add" />
                        <ul id="faculty_list" class="menu" style="margin-top: 15px">
                            <?php
                            if (is_array($PROCESSED["associated_faculty"]) && count($PROCESSED["associated_faculty"])) {
                                foreach ($PROCESSED["associated_faculty"] as $faculty) {
                                    if ((array_key_exists($faculty, $FACULTY_LIST)) && is_array($FACULTY_LIST[$faculty])) {
                                        ?>
                                        <li class="user" id="faculty_<?php echo $FACULTY_LIST[$faculty]["proxy_id"]; ?>" style="cursor: move;margin-bottom:10px;width:350px;"><?php echo $FACULTY_LIST[$faculty]["fullname"]; ?><select name="faculty_role[]" class="input-medium" style="float:right;margin-right:30px;margin-top:-5px;"><option value="teacher" <?php if($PROCESSED["display_role"][$faculty] == "teacher") echo "SELECTED";?>>Teacher</option><option value="tutor" <?php if($PROCESSED["display_role"][$faculty] == "tutor") echo "SELECTED";?>>Tutor</option><option value="ta" <?php if($PROCESSED["display_role"][$faculty] == "ta") echo "SELECTED";?>>Teacher's Assistant</option><option value="auditor" <?php if($PROCESSED["display_role"][$faculty] == "auditor") echo "SELECTED";?>>Auditor</option></select><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="faculty_list.removeItem('<?php echo $FACULTY_LIST[$faculty]["proxy_id"]; ?>');" class="list-cancel-image" /></li>
                                        <?php
                                    }
                                }
                            }
                            ?>
                        </ul>
                        <input type="hidden" id="faculty_ref" name="faculty_ref" value="" />
                        <input type="hidden" id="faculty_id" name="faculty_id" value="" />
                    </div>
                </div>
                <div id="audience-options"<?php echo ((!$PROCESSED["event_audience_type"]) ? " style=\"display: none\"" : ""); ?>>
                    <?php
                    if (isset($PROCESSED["course_id"]) && $PROCESSED["course_id"]) {
                        require_once(ENTRADA_ABSOLUTE."/core/modules/admin/events/api-audience-options.inc.php");
                    }
                    ?>
                </div>
                <h2>Time Release Options</h2>
                <div class="control-group">
                    <table>
                        <?php echo generate_calendars("viewable", "", true, false, ((isset($PROCESSED["release_date"])) ? $PROCESSED["release_date"] : 0), true, false, ((isset($PROCESSED["release_until"])) ? $PROCESSED["release_until"] : 0)); ?>
                    </table>
                </div>
                <div class="control-group">
                    <a class="btn" href="<?php echo ENTRADA_RELATIVE; ?>/admin/events">Cancel</a>
                    <div class="pull-right">
                        <?php
                        if (isset($is_draft) && $is_draft) {
                            echo "<input type=\"hidden\" name=\"post_action\" id=\"post_action\" value=\"draft\" />";
                        } else {
                            ?>
                            <span class="content-small">After saving:</span>
                            <select id="post_action" name="post_action">
                                <option value="content"<?php echo (((!isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"])) || ($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "content")) ? " selected=\"selected\"" : ""); ?>>Add content to event</option>
                                <option value="new"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "new") ? " selected=\"selected\"" : ""); ?>>Add another event</option>
                                <option value="copy"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "copy") ? " selected=\"selected\"" : ""); ?>>Add a copy of this event</option>
                                <option value="index"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "index") ? " selected=\"selected\"" : ""); ?>>Return to event list</option>
                            </select>
                            <?php
                        }
                        ?>
                        <input type="submit" class="btn btn-primary" value="Save" />
                    </div>
                </div>
            </form>
            <div id="recurringModal" class="modal hide fade" style="width: 450px;" tabindex="-1" role="dialog" aria-labelledby="recurringModalLabel" aria-hidden="true">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h3 id="recurringModalLabel">Recurring Event Frequency</h3>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer" style="text-align: left;">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button class="btn btn-primary pull-right" id="submitFrequency">Set Frequency</button>
                </div>
            </div>
            <div id="messages" rel="popover">
                &nbsp;
            </div>
            <div id="recurring-event-skeleton" style="display: none;">
                <div id="recurring-event-%event_num%" class="row-fluid pad-above%event_class%">
                    <span class="span3 content-small pad-left">
                        Event %event_num%:
                    </span>
                    <span class="span8">
                        <div class="row-fluid">
                            <label for="recurring_event_title_%event_num%" class="span2 form-required">Title:</label>
                            <span class="span7">
                                <input type="text" id="recurring_event_title_%event_num%" name="recurring_event_title[]" value="%event_title%" maxlength="255" style="width: 95%; font-size: 150%; padding: 3px" />
                            </span>
                        </div>
                        <div class="row-fluid">
                            <label class="span2" for="recurring_event_start_%event_num%">Event Start:</label>
                            <span class="span7">
                                <div class="input-append">
                                    <input type="text" class="input-small inpage-datepicker" value="%event_date%" onchange="checkEventDate('%event_num%')" name="recurring_event_start[]" id="recurring_event_start_%event_num%" />
                                    <span class="add-on pointer inpage-add-on"><i class="icon-calendar"></i></span>
                                </div>
                                &nbsp;
                                <div class="input-append">
                                    <input type="text" class="input-mini timepicker" value="%event_time%" name="recurring_event_start_time[]" id="recurring_event_start_time_%event_num%" />
                                    <span class="add-on pointer inpage-add-on"><i class="icon-time"></i></span>
                                </div>
                            </span>
                        </div>
                    </span>
                    <span class="span1 pad-right">
                        <button type="button" class="close" onclick="jQuery('#recurring-event-%event_num%').remove()">&times;</button>
                    </span>
                </div>
            </div>
            <script type="text/javascript">
            var multiselect = [];
            var audience_type;

            function showMultiSelect() {
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
                                'event_audience_cohorts' : cohorts,
                                'event_audience_course_groups' : course_groups,
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


//				var prevDate = '';
//				var prevTime = '00:00 AM';
//				var t = self.setInterval("checkDifference()", 1500);


//				Event.observe('event_audience_type_course','change',checkConflict);
//				Event.observe('associated_grad_year','change',checkConflict);
//				Event.observe('associated_organisation_id','change',checkConflict);
//				Event.observe('student_list','change',checkConflict)
//				Event.observe('eventtype_ids','change',checkConflict)
//				//Event.observe('event_start_date','keyup',checkConflict);


//				function checkDifference(){
//					if($('event_start_date').value !== prevDate){
//						prevDate = $('event_start_date').value;
//						checkConflict();
//					}
//					else if($('event_start_display').innerHTML !== prevTime){
//						prevTime = $('event_start_display').innerHTML;
//						checkConflict();
//					}
//				}
//				function checkConflict(){
//					new Ajax.Request('<?php echo ENTRADA_URL;?>/api/learning-event-conflicts.api.php',
//					{
//						method:'post',
//						parameters: $("addEventForm").serialize(true),
//						onSuccess: function(transport){
//						var response = transport.responseText || null;
//						if(response !==null){
//							var g = new k.Growler();
//							g.smoke(response,{life:7});
//						}
//						},
//						onFailure: function(){ alert('Unable to check if a conflict exists.') }
//					});
//				}

                function add_year(date) {
                    return new Date((date.getFullYear() + 1), date.getMonth(), date.getDate());
                }

                jQuery(document).ready(function($){
                    var _old_toggle = $.fn.button.prototype.constructor.Constructor.prototype.toggle;

                    $.fn.button.prototype.constructor.Constructor.prototype.toggle = function () {
                        _old_toggle.apply(this);
                        this.$element.trigger('active');
                    }
                    $('html').click(function(e) {
                        $('#repeat_frequency').popover('hide');
                        $('#repeat_frequency').show();
                    });
                    $('#repeat_frequency').popover({
                        trigger: 'manual',
                        placement: 'right',
                        title: 'Error',
                        content: 'Please ensure you select a valid event start date prior to selecting a repeat frequency.',
                        template: '<div class=\"popover alert alert-error\"><div class=\"arrow\"></div><div class=\"popover-inner\"><div class=\"popover-content\"><p></p></div></div></div>'
                    }).click(function(e) {
                        $(this).popover('toggle');
                        e.stopPropagation();
                    });
                    $('#repeat_frequency').on('change', function(){
                        if ($('#repeat_frequency').val() != 'none') {
                            if ($('#event_start_date').val()) {
                                if (!$('#rebuild_button').is(':visible')) {
                                    $('#rebuild_button').show();
                                }
                                var date = new Date($('#event_start_date').val());
                                var datestring = (date.getTime() / 1000);
                                $('#recurringModal').modal({
                                    remote: '<?php echo ENTRADA_URL ?>/admin/events?section=api-repeat-period&action=select&event_start=' + datestring + '&frequency=' + ($('#repeat_frequency').val() && $('#repeat_frequency').val() != 'none' ? $('#repeat_frequency').val() : 'daily')
                                });
                            } else {
                                $('#repeat_frequency option').eq(0).prop('selected',true);
                                $('#repeat_frequency').popover('show');
                            }
                        } else {
                            if ($('#rebuild_button').is(':visible')) {
                                $('#rebuild_button').hide();
                            }
                            $('#recurring-events-list').html("");
                        }
                    });
                    $('#recurringModal').on('shown', function () {
                        $('.toggle-days').live('active', function(event) {
                            event.preventDefault();
                            if ($(this).hasClass('active') && $('#weekday_'+ $(this).data("value")).length < 1) {
                                $('#days-container').append('<input type="hidden" value="'+ $(this).data("value") +'" name="weekdays[]" id="weekday_'+ $(this).data("value") +'" />');
                            } else if (!$(this).hasClass('active') && $('#weekday_'+ $(this).data("value")).length > 0) {
                                $('#weekday_'+ $(this).data("value")).remove();
                            }
                        });
                        $('.datepicker').datepicker({
                            dateFormat: 'yy-mm-dd',
                            maxDate: add_year(new Date($('#event_start_date').val())),
                            minDate: new Date($('#event_start_date').val())
                        });
                        $('.add-on').on('click', function() {
                            if ($(this).siblings('input').is(':enabled')) {
                                $(this).siblings('input').focus();
                            }
                        });
                    });
                    $('#recurringModal').on('hidden', function () {
                        $(this).data('modal', null);
                    });
                    $('#submitFrequency').on('click', function () {
                        $.ajax({
                            type: "POST",
                            url: '<?php echo ENTRADA_URL ?>/admin/events?section=api-repeat-period&action=results',
                            data: $('#recurring-form').serialize(),
                            success: function (data) {
                                var result = jQuery.parseJSON(data);
                                if (result.status == 'success') {
                                    if ($('.inpage-datepicker').length > 0) {
                                        $('.inpage-datepicker').datepicker('destroy');
                                        $(".timepicker").timepicker('destroy');
                                    }
                                    jQuery('#recurring-events-list').html('<h3 class="space-below">Recurring Events</h3>');
                                    for (var i = 1; i <= result.events.length; i++) {
                                        var string = jQuery('#recurring-event-skeleton').html();
                                        var class_string = (result.events[(i - 1)].restricted ? ' restricted' : '')+(i % 2 > 0 ? ' odd' : '');
                                        string = string.replace(/%event_class%/g, class_string);
                                        string = string.replace(/%event_num%/g, i);
                                        string = string.replace(/%event_title%/g, jQuery('#event_title').val());
                                        string = string.replace(/%event_time%/g, jQuery('#event_start_hour').val() + ":" + jQuery('#event_start_min').val());
                                        string = string.replace(/%event_date%/g, result.events[(i - 1)].date);
                                        jQuery('#recurring-events-list').append(string);
                                    }
                                    jQuery('#recurring-events-list').show();
                                    $('.inpage-datepicker').datepicker({
                                        dateFormat: 'yy-mm-dd',
                                        maxDate: add_year(new Date($('#event_start_date').val())),
                                        minDate: $('#event_start_date').val()
                                    });
                                    $(".timepicker").timepicker({
                                        showPeriodLabels: false
                                    });
                                    $('.inpage-add-on').on('click', function() {
                                        if ($(this).siblings('input').is(':enabled')) {
                                            $(this).siblings('input').focus();
                                        }
                                    });
                                    $('#recurringModal').modal('hide');
                                    $('.restricted').popover({
                                        trigger: 'manual',
                                        placement: 'right',
                                        title: 'Error',
                                        content: 'This day is restricted. Please ensure this is the correct date before continuing.',
                                        template: '<div class=\"popover alert alert-error\"><div class=\"arrow\"></div><div class=\"popover-inner\"><div class=\"popover-content\"><p></p></div></div></div>'
                                    }).click(function(e) {
                                        $(this).popover('hide');
                                        $(this).show();
                                        e.stopPropagation();
                                    });
                                    $('.restricted').popover('show');
                                } else {
                                    $('#error-messages').html(result.message);
                                }
                            },
                            error: function () {
                                alert("error");
                            }
                        });
                    });
                });
                
                function checkEventDate(event_num) {
                    var date = new Date(jQuery('#recurring_event_start_'+event_num).val());
                    var event_date = (date.getTime() / 1000) + (date.getTimezoneOffset() * 60);
                    jQuery.ajax({
                            type: "POST",
                            url: '<?php echo ENTRADA_URL ?>/admin/events?section=api-check-date',
                            data: 'event_start='+event_date+'&organisation_id=<?php echo $ENTRADA_USER->getActiveOrganisation(); ?>',
                            success: function (data) {
                                if (data == 'Found') {
                                    if (!jQuery('#recurring-event-'+event_num).hasClass('restricted')) {
                                        jQuery('#recurring-event-'+event_num).addClass('restricted');
                                    }
                                    jQuery('.restricted').popover('destroy');
                                    jQuery('.restricted').popover({
                                        trigger: 'manual',
                                        placement: 'right',
                                        title: 'Error',
                                        content: 'This day is restricted. Please ensure this is the correct date before continuing.',
                                        template: '<div class=\"popover alert alert-error\"><div class=\"arrow\"></div><div class=\"popover-inner\"><div class=\"popover-content\"><p></p></div></div></div>'
                                    }).click(function(e) {
                                        jQuery(this).popover('hide');
                                        jQuery(this).show();
                                        e.stopPropagation();
                                    });
                                    jQuery('.restricted').popover('show');
                                } else if (jQuery('#recurring-event-'+event_num).hasClass('restricted')) {
                                    jQuery('#recurring-event-'+event_num).removeClass('restricted');
                                }
                            },
                            error: function () {
                                alert("error");
                            }
                        });
                }
            </script>
            <?php
		break;
	}
}
