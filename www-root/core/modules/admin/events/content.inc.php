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
 * This file is used to modify content (i.e. goals, objectives, file resources
 * etc.) within a learning event from the entrada.events table.
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
} elseif (!$ENTRADA_ACL->amIAllowed("eventcontent", "update", false)) {
	$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/eventtypes_list.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	?>
	<script type="text/javascript">
		var EVENT_LIST_STATIC_TOTAL_DURATION = true;
	</script>
	<?php
	if ($EVENT_ID) {
		$HEAD[] = "<script type=\"text/javascript\">var SITE_URL = '".ENTRADA_URL."';</script>";
		$HEAD[]	= "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/objectives.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
		$HEAD[]	= "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/objectives_event.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";

		$query		= "	SELECT a.*, b.`organisation_id`
						FROM `events` AS a
						LEFT JOIN `courses` AS b
						ON b.`course_id` = a.`course_id`
						WHERE a.`event_id` = ".$db->qstr($EVENT_ID);
		$event_info	= $db->GetRow($query);
		if ($event_info) {
            if ($event_info["recurring_id"]) {
                $query = "SELECT * FROM `events`
                            WHERE `recurring_id` = ".$db->qstr($event_info["recurring_id"])."
                            AND `event_id` != ".$db->qstr($EVENT_ID);
                $recurring_events = $db->GetAll($query);
            } else {
                $recurring_events = false;
            }
			$PROCESSED["objectives_release_date"] = $event_info["objectives_release_date"];
			$COURSE_ID = $event_info["course_id"];
			if (!$ENTRADA_ACL->amIAllowed(new EventContentResource($event_info["event_id"], $event_info["course_id"], $event_info["organisation_id"]), "update")) {
				application_log("error", "Someone attempted to modify content for an event [".$EVENT_ID."] that they were not the coordinator for.");

				header("Location: ".ENTRADA_URL."/admin/".$MODULE);
				exit;
			} else {
				$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/events?".replace_query(array("section" => "content", "id" => $EVENT_ID)), "title" => "Event Content");

				$HEAD[]	= "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/picklist.js\"></script>\n";

				/**
				 * Load the rich text editor.
				 */
				load_rte();

				/**
				 * Fetch event content history
				 */
				$history = $db->GetRow("SELECT * FROM `event_history` WHERE `event_id`  = ".$db->qstr($EVENT_ID));

				if (!$history) { // Create the first history record of the event's creation when another user updates the event
					if(count($_POST) && ($ENTRADA_USER->getID() != $event_info["updated_by"])) {	// Ignore starting history when it's the sole author initially adding content.
						history_log($EVENT_ID, 'created this learning event.', $event_info["updated_by"], $event_info["updated_date"]);
					}
				}

				if (($event_info["release_date"]) && ($event_info["release_date"] > time())) {
					$NOTICE++;
					$NOTICESTR[] = "This event is not yet visible to students due to Time Release Options set by an administrator. The release date is set to ".date("r", $event_info["release_date"]);
				}

				if (($event_info["release_until"]) && ($event_info["release_until"] < time())) {
					$NOTICE++;
					$NOTICESTR[] = "This event is no longer visible to students due to Time Release Options set by an administrator. The expiry date was set to ".date("r", $event_info["release_until"]);
				}

				/**
				 * Fetch the event audience information.
				 */
				$event_audience_type		= "";
				$associated_grad_year		= "";
				$associated_group_ids		= array();
				$associated_proxy_ids		= array();
				$associated_organisation	= "";

				$query		= "SELECT * FROM `event_audience` WHERE `event_id` = ".$db->qstr($EVENT_ID);
				$results	= $db->GetAll($query);
				if ($results) {
					$event_audience_type = $results[0]["audience_type"];

					foreach ($results as $result) {
						if ($result["audience_type"] == $event_audience_type) {
							switch ($result["audience_type"]) {
								case "grad_year" :
									$associated_grad_year = clean_input($result["audience_value"], "alphanumeric");
								break;
								case "group_id" :
									$associated_group_ids[] = (int) $result["audience_value"];
								break;
								case "proxy_id" :
									$associated_proxy_ids[] = (int) $result["audience_value"];
								break;
								case "organisation_id" :
									$query = "SELECT `organisation_title` FROM `".AUTH_DATABASE."`.`organisations` WHERE `organisation_id` = ".$db->qstr($result["audience_value"]);
									$associated_organisation = $db->GetOne($query);
								break;
							}
						}
					}
				}

				/**
				 * Fetch the Clinical Presentation details.
				 */
				$clinical_presentations_list = array();
				$clinical_presentations = array();

				$results = fetch_clinical_presentations(0, array(), $event_info["course_id"]);
				if ($results) {
					foreach ($results as $result) {
						$clinical_presentations_list[$result["objective_id"]] = $result["objective_name"];
					}
				}

                if (((isset($_POST["clinical_presentations"])) && (is_array($_POST["clinical_presentations"])) && (count($_POST["clinical_presentations"])))) {
                    foreach ($_POST["clinical_presentations"] as $objective_id) {
                        if ($objective_id = clean_input($objective_id, array("trim", "int"))) {
                            $query	= "	SELECT a.`objective_id`
                                    FROM `global_lu_objectives` AS a
                                    JOIN `course_objectives` AS b
                                    ON b.`course_id` = ".$event_info["course_id"]."
                                    AND a.`objective_id` = b.`objective_id`
                                    JOIN `objective_organisation` AS c
                                    ON a.`objective_id` = c.`objective_id`
                                    WHERE a.`objective_id` = ".$db->qstr($objective_id)."
                                    AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
                                    AND b.`objective_type` = 'event'
                                    AND a.`objective_active` = '1'";
                            $result	= $db->GetRow($query);
                            if ($result) {
                                $clinical_presentations[$objective_id] = $clinical_presentations_list[$objective_id];
                            }
                        }
                    }
                } else {
                    $clinical_presentations = array();
                }

                /**
				 * Fetch the Curriculum Objective details.
				 */
				list($curriculum_objectives_list,$top_level_id) = courses_fetch_objectives($event_info["organisation_id"],array($event_info["course_id"]),-1, 1, false, false, $EVENT_ID, true);
				$curriculum_objectives = array();

				if (isset($_POST["checked_objectives"]) && ($checked_objectives = $_POST["checked_objectives"]) && (is_array($checked_objectives))) {
					foreach ($checked_objectives as $objective_id) { // => $status
						if ($objective_id = (int) $objective_id) {
							if (isset($_POST["objective_text"][$objective_id]) && ($tmp_input = clean_input($_POST["objective_text"][$objective_id], array("notags")))) {
								$objective_text = $tmp_input;
							} else {
								$objective_text = false;
							}

							$curriculum_objectives[$objective_id] = $objective_text;
						}
					}
					history_log($EVENT_ID, "updated clinical objectives.");
				}

				$query = "SELECT `objective_id` FROM `event_objectives` WHERE `event_id` = ".$db->qstr($EVENT_ID)." AND `objective_type` = 'course'";
				$results = $db->GetAll($query);
				if ($results) {
					foreach ($results as $result) {
						$curriculum_objectives_list["objectives"][$result["objective_id"]]["event_objective"] = true;
					}
				}

				/**
				 * Fetch the event type information.
				 */
				$event_eventtypes_list	= array();
				$event_eventtypes		= array();

				$query		= "	SELECT a.* FROM `events_lu_eventtypes` AS a
								LEFT JOIN `eventtype_organisation` AS c
								ON a.`eventtype_id` = c.`eventtype_id`
								LEFT JOIN `".AUTH_DATABASE."`.`organisations` AS b
								ON b.`organisation_id` = c.`organisation_id`
								WHERE b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
								AND a.`eventtype_active` = '1'
								ORDER BY a.`eventtype_order`";
				$results	= $db->GetAll($query);
				if ($results) {
					foreach ($results as $result) {
						$event_eventtypes_list[$result["eventtype_id"]] = $result["eventtype_title"];
					}
				}

				$query		= "SELECT a.*, b.`eventtype_title` FROM `event_eventtypes` AS a LEFT JOIN `events_lu_eventtypes` AS b ON a.`eventtype_id` = b.`eventtype_id` WHERE a.`event_id` = ".$db->qstr($EVENT_ID)." ORDER BY `eeventtype_id` ASC";
				$results	= $db->GetAll($query);
				$initial_duration = 0;
				if ($results) {
					foreach ($results as $result) {
						$initial_duration += $result["duration"];
						//$event_eventtypes[] = array($result["eventtype_id"], $result["duration"], $event_eventtypes_list[$result["eventtype_id"]]);
						$event_eventtypes[] = $result;
					}
				}
				?>
				<script type="text/javascript" charset="utf-8">
					var INITIAL_EVENT_DURATION = <?php echo $initial_duration; ?>
				</script>
				<?php
				if (isset($_POST["eventtype_duration_order"])) {
					$old_event_eventtypes = $event_eventtypes;
					$event_eventtypes = array();
					$eventtype_durations = $_POST["duration_segment"];

					$event_types = explode(",", trim($_POST["eventtype_duration_order"]));

					if ((is_array($event_types)) && (count($event_types))) {
						foreach ($event_types as $order => $eventtype_id) {
							if (($eventtype_id = clean_input($eventtype_id, array("trim", "int"))) && ($duration = clean_input($eventtype_durations[$order], array("trim", "int")))) {
								if (!($duration >= LEARNING_EVENT_MIN_DURATION)) {
									$ERROR++;
									$ERRORSTR[] = "Event type <strong>durations</strong> may not be less than ".LEARNING_EVENT_MIN_DURATION." minutes.";
								}

								$query	= "SELECT `eventtype_title` FROM `events_lu_eventtypes` WHERE `eventtype_id` = ".$db->qstr($eventtype_id);
								$result	= $db->GetRow($query);
								if ($result) {
									$event_eventtypes[] = array("eventtype_id"=>$eventtype_id, "duration"=>$duration, "eventtype_title"=>$result["eventtype_title"]);
								}
							}
						}

						$event_duration	= 0;
						$old_event_duration = 0;
						foreach($event_eventtypes as $event_type) {
							$event_duration += $event_type["duration"];
						}

						foreach($old_event_eventtypes as $event_type) {
							$old_event_duration += $event_type["duration"];
						}

						if($old_event_duration != $event_duration) {
							$ERROR++;
							$ERRORSTR[] = "The modified <strong>Event Types</strong> duration specified is different than the exisitng one, please ensure the event's duration remains the same.";
						}
					} else {
						$ERROR++;
						$ERRORSTR[] = "At least one event type in the <strong>Event Types</strong> field is required.";
					}
				}



				if (isset($_POST["type"])) {
					switch ($_POST["type"]) {
						case "content" :

                            /**
                            * Event objective release date
                            */
                            $PROCESSED["objectives_release_date"] = 0;
                            if (isset($_POST["delay_release"]) && $tmp_input = clean_input($_POST["delay_release"], array("int"))) {
                                $PROCESSED["delay_release"] = $tmp_input;
                                $release_date = validate_calendar("Delay release until", "delay_release_option", true, true);
                                if (!$ERROR) {
                                    if ($release_date >= time()) {
                                        $PROCESSED["objectives_release_date"] = (int) $release_date;
                                    } else {
                                        add_error("<strong>Objective release date</strong> must on or after the current date and time.");
                                    }
                                }
                            }

                            if(!$ERROR) {

                                $history_texts = " [";
                                /**
                                 * Event Description
                                 */
                                if (event_text_change($EVENT_ID,"event_description")) {
                                    $history_texts .= "event description";
                                }
                                if ((isset($_POST["event_description"])) && (clean_input($_POST["event_description"], array("notags", "nows")))) {
                                    $event_description = clean_input($_POST["event_description"], array("allowedtags"));
                                } else {
                                    $event_description = "";
                                }

                                /**
                                 * Free-Text Objectives
                                 */
                                if (event_text_change($EVENT_ID,"event_objectives")) {
                                    if (strlen($history_texts)>2) {
                                        $history_texts .= ":";
                                    }
                                    $history_texts .= "event objectives";
                                }
                                if ((isset($_POST["event_objectives"])) && (clean_input($_POST["event_objectives"], array("notags", "nows")))) {
                                    $event_objectives = clean_input($_POST["event_objectives"], array("allowedtags"));
                                } else {
                                    $event_objectives = "";
                                }

                                /**
                                 * Required Preparation
                                 */
                                if (event_text_change($EVENT_ID,"event_message")) {
                                    if (strlen($history_texts)>2) {
                                        $history_texts .= ":";
                                    }
                                    $history_texts .= "Required Preparation";
                                }
                                if ((isset($_POST["event_message"])) && (clean_input($_POST["event_message"], array("notags", "nows")))) {
                                    $event_message = clean_input($_POST["event_message"], array("allowedtags"));
                                } else {
                                    $event_message = "";
                                }

                                $event_finish	= $event_info["event_start"];
                                $event_duration	= 0;

                                foreach($event_eventtypes as $event_type) {
                                    $event_finish += ($event_type["duration"] * 60);
                                    $event_duration += $event_type["duration"];
                                }

                                /**
                                 * Update base Learning Event.
                                 */
                                if ($db->AutoExecute("events", array("event_objectives" => $event_objectives, "objectives_release_date" => $PROCESSED["objectives_release_date"] , "event_description" => $event_description, "event_message" => $event_message, "event_finish" => $event_finish, "event_duration" => $event_duration, "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID()), "UPDATE", "`event_id` = ".$db->qstr($EVENT_ID))) {
                                    $SUCCESS++;
                                    $SUCCESSSTR[] = "You have successfully updated the event details for this learning event.";

                                    application_log("success", "Updated learning event content.");

                                } else {
                                    application_log("error", "Failed to update learning event content. Database said: ".$db->ErrorMsg());
                                }

                                /**
                                 * Update Event Types.
                                 */

                                $query = "DELETE FROM `event_eventtypes` WHERE `event_id` = ".$db->qstr($EVENT_ID);
                                if ($db->Execute($query)) {
                                    foreach ($event_eventtypes as $event_type) {
                                        if (!$db->AutoExecute("event_eventtypes", array("event_id" => $EVENT_ID, "eventtype_id" => $event_type["eventtype_id"], "duration" => $event_type["duration"]), "INSERT")) {
                                            $ERROR++;
                                            $ERRORSTR[] = "There was an error while trying to save the selected <strong>Event Type</strong> for this event.<br /><br />The system administrator was informed of this error; please try again later.";

                                            application_log("error", "Unable to insert a new event_eventtype record while adding a new event. Database said: ".$db->ErrorMsg());
                                        }
                                    }
                                } else {
                                    $ERROR++;
                                    $ERRORSTR[] = "There was an error while trying to update the selected <strong>Event Types</strong> for this event.<br /><br />The system administrator was informed of this error; please try again later.";

                                    application_log("error", "Unable to delete any eventtype records while editing an event. Database said: ".$db->ErrorMsg());
                                }

                                /**
                                 * Update Clinical Presentations.
                                 */
                                $query = "DELETE FROM `event_objectives` WHERE `objective_type` = 'event' AND `event_id` = ".$db->qstr($EVENT_ID);
                                if ($db->Execute($query)) {
                                    if ((is_array($clinical_presentations)) && (count($clinical_presentations))) {
                                        foreach ($clinical_presentations as $objective_id => $presentation_name) {
                                            if (isset($_POST["objective_text"][$objective_id]) && ($tmp_input = clean_input($_POST["objective_text"][$objective_id], array("notags")))) {
                                                $objective_text = $tmp_input;
                                            } else {
                                                $objective_text = false;
                                            }
                                            if (!$db->AutoExecute("event_objectives", array("event_id" => $EVENT_ID, "objective_details" => $objective_text, "objective_id" => $objective_id, "objective_type" => "event", "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID()), "INSERT")) {
                                                $ERROR++;
                                                $ERRORSTR[] = "There was an error when trying to insert a &quot;clinical presentation&quot; into the system. System administrators have been informed of this error; please try again later.";

                                                application_log("error", "Unable to insert a new clinical presentation to the database when adding a new event. Database said: ".$db->ErrorMsg());
                                            }
                                        }
                                        history_log($EVENT_ID, "updated clinical presentations.");
                                    }
                                }

                                /**
                                 * Update Curriculum Objectives.
                                 */
                                $query = "DELETE FROM `event_objectives` WHERE `objective_type` = 'course' AND `event_id` = ".$db->qstr($EVENT_ID);
                                if ($db->Execute($query)) {
                                    if ((isset($curriculum_objectives)) && (is_array($curriculum_objectives)) && (count($curriculum_objectives))) {
                                        foreach ($curriculum_objectives as $objective_id => $objective_text) {
                                            if ($objective_id = (int) $objective_id) {
                                                $query	= "	SELECT a.* FROM `global_lu_objectives` AS a
                                                        JOIN `objective_organisation` AS b
                                                        ON a.`objective_id` = b.`objective_id`
                                                        WHERE a.`objective_id` = ".$db->qstr($objective_id)."
                                                        AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
                                                        AND a.`objective_active` = '1'";
                                                $result	= $db->GetRow($query);
                                                if ($result) {
                                                    if (!$db->AutoExecute("event_objectives", array("event_id" => $EVENT_ID, "objective_details" => $objective_text, "objective_id" => $objective_id, "objective_type" => "course", "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID()), "INSERT")) {
                                                        $ERROR++;
                                                        $ERRORSTR[] = "There was an error when trying to insert a &quot;course objective&quot; into the system. System administrators have been informed of this error; please try again later.";

                                                        application_log("error", "Unable to insert a new course objective to the database when adding a new event. Database said: ".$db->ErrorMsg());
                                                    }
                                                }
                                            }
                                        }

                                        /**
                                         * Changes have been made so update the $curriculum_objectives_list variable.
                                         */
                                        list($curriculum_objectives_list,$top_level_id) = courses_fetch_objectives($event_info["organisation_id"],array($event_info["course_id"]), -1, 1, false, false, $EVENT_ID, true);
                                    }
                                }

                                /**
                                 * Update Event Topics information.
                                 */
                                $query = "DELETE FROM `event_topics` WHERE `event_id` = ".$db->qstr($EVENT_ID);
                                if ($db->Execute($query)) {
                                    if ((isset($_POST["event_topic"])) && (is_array($_POST["event_topic"])) && (count($_POST["event_topic"]))) {
                                        foreach ($_POST["event_topic"] as $topic_id => $value) {
                                            if ($topic_id = clean_input($topic_id, array("trim", "int"))) {
                                                $squery		= "SELECT * FROM `events_lu_topics` WHERE `topic_id` = ".$db->qstr($topic_id);
                                                $sresult	= $db->GetRow($squery);
                                                if ($sresult) {
                                                    if ($value == "major") {
                                                        if (!$db->AutoExecute("event_topics", array("event_id" => $EVENT_ID, "topic_id" => $topic_id, "topic_coverage" => "major", "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID()), "INSERT")) {
                                                            $ERROR++;
                                                            $ERRORSTR[] = "There was an error when trying to insert an Event Topic response into the system. System administrators have been informed of this error; please try again later.";

                                                            application_log("error", "Unable to insert a new event_topic entry into the database while modifying event contents. Database said: ".$db->ErrorMsg());
                                                        }
                                                    } elseif ($value == "minor") {
                                                        if (!$db->AutoExecute("event_topics", array("event_id" => $EVENT_ID, "topic_id" => $topic_id, "topic_coverage" => "minor", "topic_time" => "0", "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID()), "INSERT")) {
                                                            $ERROR++;
                                                            $ERRORSTR[] = "There was an error when trying to insert an Event Topic response into the system. System administrators have been informed of this error; please try again later.";

                                                            application_log("error", "Unable to insert a new event_topic response to the database while modifying event contents. Database said: ".$db->ErrorMsg());
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }

                                /**
                                 * Refresh the event_info array based on new details.
                                 */
                                $query = "	SELECT a.*, b.`organisation_id`
                                            FROM `events` AS a
                                            LEFT JOIN `courses` AS b
                                            ON b.`course_id` = a.`course_id`
                                            WHERE a.`event_id` = ".$db->qstr($EVENT_ID);
                                $event_info	= $db->GetRow($query);
                                if (!$event_info) {
                                    application_log("error", "After updating the text content of event_id [".$EVENT_ID."] the select query failed.");
                                }
                                history_log($EVENT_ID, "updated event content".(strlen($history_texts)>2?" $history_texts].":"."));
                            }
						break;
                        case "cascade" :
                            if ($event_info && isset($_POST["recurring_event_ids"]) && @count($_POST["recurring_event_ids"])) {
                                $recurring_events = array();
                                $query = "SELECT * FROM `events`
                                            WHERE `recurring_id` = ".$db->qstr($event_info["recurring_id"])."
                                            AND `event_id` != ".$db->qstr($EVENT_ID)."
                                            ORDER BY `event_start` ASC";
                                $temp_recurring_events = $db->GetAll($query);
                                if ($temp_recurring_events) {
                                    foreach ($temp_recurring_events as $temp_event) {
                                        if (in_array($temp_event["event_id"], $_POST["recurring_event_ids"])) {
                                            $recurring_events[] = $temp_event;
                                        }
                                    }
                                }
                                $PROCESSED = $event_info;
                                unset($PROCESSED["recurring_id"]);
                                if ($recurring_events) {
                                    if (isset($_POST["fields"]) && in_array("mapped_objectives", $_POST["fields"])) {
                                        $PROCESSED["mapped_objectives"] = array();
                                        $query = "SELECT `objective_id`, `objective_details`, `objective_type` FROM `event_objectives` WHERE `event_id` = ".$db->qstr($EVENT_ID);
                                        $results = $db->GetAll($query);
                                        if ($results) {
                                            foreach ($results as $result) {
                                                $PROCESSED["mapped_objectives"][] = $result;
                                            }
                                        }
                                    }
                                    if (isset($_POST["fields"]) && in_array("event_topics", $_POST["fields"])) {
                                        $PROCESSED["event_topics"] = array();
                                        $query = "SELECT `topic_id`, `topic_coverage`, `topic_time` FROM `event_topics` WHERE `event_id` = ".$db->qstr($EVENT_ID);
                                        $results = $db->GetAll($query);
                                        if ($results) {
                                            foreach ($results as $result) {
                                                $PROCESSED["event_topics"][] = $result;
                                            }
                                        }
                                    }

                                    foreach ($recurring_events as $order => $recurring_event) {
                                        if (isset($PROCESSED["mapped_objectives"])) {
                                            $query = "DELETE FROM `event_objectives` WHERE `event_id` = ".$db->qstr($recurring_event["event_id"]);
                                            if ($db->Execute($query)) {
                                                if (@count($PROCESSED["mapped_objectives"])) {
                                                    foreach($PROCESSED["mapped_objectives"] as $event_objective) {
                                                        $event_objective_data = $event_objective;
                                                        $event_objective_data["event_id"] = $recurring_event["event_id"];
                                                        $event_objective_data["updated_date"] = time();
                                                        $event_objective_data["updated_by"] = $ENTRADA_USER->GetID();
                                                        if (!$db->AutoExecute("`event_objectives`", $event_objective_data, "INSERT")) {
                                                            add_error("There was an error while trying to save the selected <strong>Event Objective</strong> for a recurring event.<br /><br />The system administrator was informed of this error; please try again later.");

                                                            application_log("error", "Unable to insert a new event_objectives record while editing a recurring event. Database said: ".$db->ErrorMsg());
                                                        }
                                                    }
                                                }
                                            } else {
                                                add_error("There was an error while trying to update the selected <strong>Event Objectives</strong> for a recurring event.<br /><br />The system administrator was informed of this error; please try again later.");

                                                application_log("error", "Unable to delete any event_objectives records while editing an event. Database said: ".$db->ErrorMsg());
                                            }
                                        }
                                        if (isset($PROCESSED["event_topics"])) {
                                            $query = "DELETE FROM `event_topics` WHERE `event_id` = ".$db->qstr($recurring_event["event_id"]);
                                            if ($db->Execute($query)) {
                                                if (@count($PROCESSED["event_topics"])) {
                                                    foreach($PROCESSED["event_topics"] as $event_topic) {
                                                        $event_topic_data = $event_topic;
                                                        $event_topic_data["event_id"] = $recurring_event["event_id"];
                                                        $event_topic_data["updated_date"] = time();
                                                        $event_topic_data["updated_by"] = $ENTRADA_USER->GetID();
                                                        if (!$db->AutoExecute("`event_topics`", $event_topic_data, "INSERT")) {
                                                            add_error("There was an error while trying to save the selected <strong>Event Topic</strong> for a recurring event.<br /><br />The system administrator was informed of this error; please try again later.");

                                                            application_log("error", "Unable to insert a new event_topics record while editing a recurring event. Database said: ".$db->ErrorMsg());
                                                        }
                                                    }
                                                }
                                            } else {
                                                add_error("There was an error while trying to update the selected <strong>Event Topics</strong> for a recurring event.<br /><br />The system administrator was informed of this error; please try again later.");

                                                application_log("error", "Unable to delete any event_topics records while editing an event. Database said: ".$db->ErrorMsg());
                                            }
                                        }
                                        if (isset($_POST["recurring_event_start_".$recurring_event["event_id"]]) && $_POST["recurring_event_start_".$recurring_event["event_id"]] && strtotime($_POST["recurring_event_start_".$recurring_event["event_id"]])) {
                                            $event_start = trim($_POST["recurring_event_start_".$recurring_event["event_id"]]);
                                            if (isset($_POST["recurring_event_start_time_".$recurring_event["event_id"]]) && $tmp_input = clean_input($_POST["recurring_event_start_time_".$recurring_event["event_id"]], array("trim", "striptags"))) {
                                                $time = $tmp_input;
                                            } else {
                                                $time = "00:00";
                                            }
                                            $time = strtotime($event_start . " " . $time);
                                        }
                                        if ($time) {
                                            $recurring_event_date = $time;
                                        } else {
                                            add_error("One of the <strong>recurring events</strong> did not have a valid start date, please fill out a Event Start for <strong>Event ".($order+1)."</strong> under the Recurring Events now.");
                                        }
                                        if (isset($_POST["recurring_event_title_".$recurring_event["event_id"]]) && ($recurring_event_title = clean_input($_POST["recurring_event_title_".$recurring_event["event_id"]], array("notags", "trim")))) {
                                            $event_finish = $recurring_event_date;
                                            $event_duration = 0;
                                            foreach($PROCESSED["event_types"] as $event_type) {
                                                $event_finish += $event_type[1]*60;
                                                $event_duration += $event_type[1];
                                            }

                                            $PROCESSED_RECURRING_EVENT = array(     "event_title" => $recurring_event_title,
                                                                                    "event_start" => $recurring_event_date,
                                                                                    "event_finish" => $event_finish,
                                                                                    "event_duration" => $event_duration);
                                        } else {
                                            add_error("One of the <strong>recurring events</strong> did not have a valid title, please fill out a title for <strong>Event ".($order+1)."</strong> under the Recurring Events now.");
                                        }
                                        if (isset($PROCESSED["event_description"])) {
                                            $PROCESSED_RECURRING_EVENT["event_description"] = $PROCESSED["event_description"];
                                        }
                                        if (isset($PROCESSED["event_message"])) {
                                            $PROCESSED_RECURRING_EVENT["event_message"] = $PROCESSED["event_message"];
                                        }
                                        if (isset($PROCESSED["event_objectives"])) {
                                            $PROCESSED_RECURRING_EVENT["event_objectives"] = $PROCESSED["event_objectives"];
                                        }
                                        if (!has_error()) {
                                            if (!$db->AutoExecute("`events`", $PROCESSED_RECURRING_EVENT, "UPDATE", "`event_id` = ".$db->qstr($recurring_event["event_id"]))) {
                                                add_error("There was an error while trying to save changes to the selected <strong>Recurring Event</strong>.<br /><br />The system administrator was informed of this error; please try again later.");

                                                application_log("error", "Unable to update an event record while editing a recurring event. Database said: ".$db->ErrorMsg());
                                            }
                                        }

                                    }
                                    if (!has_error()) {
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
                                                    AND a.`course_id` = ".$db->qstr($event_info["course_id"]);
                                        $result = $db->GetRow($query);
                                        if ($result) {
                                            $COMMUNITY_ID = $result["community_id"];
                                            $PAGE_ID = $result["cpage_id"];
                                            communities_log_history($COMMUNITY_ID, $PAGE_ID, $event_info["recurring_id"], "community_history_edit_recurring_events", 1);
                                        }

                                        $SUCCESS++;
                                        $SUCCESSSTR[] = "You have successfully edited the recurring events associated with <strong>".html_encode($event_info["event_title"])."</strong> in the system.";

                                        application_log("success", "Recurring Events [".$event_info["recurring_id"]."] have been modified.");
                                    }
                                }
                            }
                            if ($ERROR) {
                                $_POST["cascade"] = true;
                            }
                        break;
						case "files" :
							$FILE_IDS = array();

							if ((!isset($_POST["delete"])) || (!is_array($_POST["delete"])) || (!count($_POST["delete"]))) {
								$ERROR++;
								$ERRORSTR[] = "You must select at least 1 file to delete by checking the checkbox to the left the file title.";

								application_log("notice", "User pressed the Delete Selected button without selecting any files to delete.");
							} else {
								foreach ($_POST["delete"] as $efile_id) {
									$efile_id = clean_input($efile_id, "int");
									if ($efile_id) {
										$FILE_IDS[] = $efile_id;
									}
								}

								if (count($FILE_IDS) < 1) {
									$ERROR++;
									$ERRORSTR[] = "There were no valid file identifiers provided to delete.";
								} else {
									foreach ($FILE_IDS as $efile_id) {
										$query		= "SELECT * FROM `event_files` WHERE `efile_id` = ".$db->qstr($efile_id)." AND `event_id` = ".$db->qstr($EVENT_ID);
										$sresult	= $db->GetRow($query);
										if ($sresult) {
											$query = "DELETE FROM `event_files` WHERE `efile_id` = ".$db->qstr($efile_id)." AND `event_id` = ".$db->qstr($EVENT_ID);
											if ($db->Execute($query)) {
												if ($db->Affected_Rows()) {
													if (@unlink(FILE_STORAGE_PATH."/".$efile_id)) {
														$SUCCESS++;
														$SUCCESSSTR[] = "Successfully deleted ".$sresult["file_name"]." from this event.";

														application_log("success", "Deleted ".$sresult["file_name"]." [ID: ".$efile_id."] from filesystem.");
													}

													application_log("success", "Deleted ".$sresult["file_name"]." [ID: ".$efile_id."] from database.");
												} else {
													application_log("error", "Trying to delete ".$sresult["file_name"]." [ID: ".$efile_id."] from database, but there were no rows affected. Database said: ".$db->ErrorMsg());
												}
											} else {
												$ERROR++;
												$ERRORSTR[] = "We are unable to delete ".$sresult["file_name"]." from the event at this time. The MEdTech Unit has been informed of the error, please try again later.";

												application_log("error", "Trying to delete ".$sresult["file_name"]." [ID: ".$efile_id."] from database, but the execute statement returned false. Database said: ".$db->ErrorMsg());
											}
										}
									}
									history_log($EVENT_ID, "deleted ". count($FILE_IDS) ." resource files".($FILE_IDS>1?"s":""));
								}
							}
						break;
						case "links" :
							$LINK_IDS = array();

							if ((!isset($_POST["delete"])) || (!is_array($_POST["delete"])) || (!@count($_POST["delete"]))) {
								$ERROR++;
								$ERRORSTR[] = "You must select at least 1 link to delete by checking the box to the left the link.";

								application_log("notice", "User pressed the Delete Selected button without selecting any links to delete.");
							} else {
								foreach ($_POST["delete"] as $elink_id) {
									$elink_id = clean_input($elink_id, "int");
									if ($elink_id) {
										$LINK_IDS[] = $elink_id;
									}
								}

								if (count($LINK_IDS) < 1) {
									$ERROR++;
									$ERRORSTR[] = "There were no valid link identifiers provided to delete.";
								} else {
									foreach ($LINK_IDS as $elink_id) {
										$query		= "SELECT * FROM `event_links` WHERE `elink_id` = ".$db->qstr($elink_id)." AND `event_id` = ".$db->qstr($EVENT_ID);
										$sresult	= $db->GetRow($query);
										if ($sresult) {
											$query = "DELETE FROM `event_links` WHERE `elink_id` = ".$db->qstr($elink_id)." AND `event_id` = ".$db->qstr($EVENT_ID);
											if ($db->Execute($query)) {
												if ($db->Affected_Rows()) {
													application_log("success", "Deleted ".$sresult["link"]." [ID: ".$elink_id."] from database.");
												} else {
													application_log("error", "Trying to delete ".$sresult["link"]." [ID: ".$elink_id."] from database, but there were no rows affected. Database said: ".$db->ErrorMsg());
												}
											} else {
												$ERROR++;
												$ERRORSTR[] = "We are unable to delete ".$sresult["link"]." from the event at this time. System administrators have been informed of the error, please try again later.";

												application_log("error", "Trying to delete ".$sresult["link"]." [ID: ".$elink_id."] from database, but the execute statement returned false. Database said: ".$db->ErrorMsg());
											}
										}
									}
									history_log($EVENT_ID, "deleted ". count($LINK_IDS) ." resource files".($LINK_IDS>1?"s":""));
								}
							}
						break;
						case "quizzes" :
							$QUIZ_IDS = array();

							if ((!isset($_POST["delete"])) || (!is_array($_POST["delete"])) || (!@count($_POST["delete"]))) {
								$ERROR++;
								$ERRORSTR[] = "You must select at least 1 quiz to detach by checking the box to the left the quiz.";

								application_log("notice", "User pressed the Detach Selected button without selecting any quizzes to detach.");
							} else {
								foreach ($_POST["delete"] as $aquiz_id) {
									$aquiz_id = clean_input($aquiz_id, "int");
									if ($aquiz_id) {
										$QUIZ_IDS[] = $aquiz_id;
									}
								}

								if (count($QUIZ_IDS) < 1) {
									$ERROR++;
									$ERRORSTR[] = "There were no valid quiz identifiers provided to detach.";
								} else {
									foreach ($QUIZ_IDS as $aquiz_id) {
										$query	= "SELECT * FROM `attached_quizzes` WHERE `aquiz_id` = ".$db->qstr($aquiz_id)." AND `content_type` = 'event' AND `content_id` = ".$db->qstr($EVENT_ID);
										$result	= $db->GetRow($query);
										if ($result) {
											$query = "DELETE FROM `attached_quizzes` WHERE `aquiz_id` = ".$db->qstr($aquiz_id)." AND `content_type` = 'event' AND `content_id` = ".$db->qstr($EVENT_ID);
											if ($db->Execute($query)) {
												if ($db->Affected_Rows()) {
													application_log("success", "Detached quiz [".$result["quiz_id"]."] from event [".$EVENT_ID."].");
												} else {
													application_log("error", "Failed to detach quiz [".$result["quiz_id"]."] from event [".$EVENT_ID."]. Database said: ".$db->ErrorMsg());
												}
											} else {
												$ERROR++;
												$ERRORSTR[] = "We are unable to detach <strong>".html_encode($result["quiz_title"])."</strong> from this event. The system administrator has been informed of the error; please try again later.";

												application_log("error", "Failed to detach quiz [".$result["quiz_id"]."] from event [".$EVENT_ID."]. Database said: ".$db->ErrorMsg());
											}
										}
									}
									history_log($EVENT_ID, "deleted ". count($QUIZ_IDS) ." ".($QUIZ_IDS>1?"zes":""));
								}
							}
						break;
                        case "lti" :
                            $LTI_IDS = array();

                            if((!isset($_POST["delete"])) || (!is_array($_POST["delete"])) || (!@count($_POST["delete"]))) {
                                $ERROR++;
                                $ERRORSTR[] = "You must select at least 1 LTI Provider to delete by checking the checkbox to the left the LTI Provider.";

                                application_log("notice", "User pressed the Delete LTI Provider button without selecting any files to delete.");
                            } else {
                                foreach($_POST["delete"] as $lti_id) {
                                    $lti_id = (int) trim($lti_id);
                                    if($lti_id) {
                                        $LTI_IDS[] = (int) trim($lti_id);
                                    }
                                }

                                if(!@count($LTI_IDS)) {
                                    $ERROR++;
                                    $ERRORSTR[] = "There were no valid LTI Provider identifiers provided to delete.";
                                } else {
                                    foreach($LTI_IDS as $lti_id) {
                                        $query	= "SELECT * FROM `event_lti_consumers` WHERE `id`=".$db->qstr($lti_id)." AND `event_id`=".$db->qstr($EVENT_ID);
                                        $sresult	= $db->GetRow($query);
                                        if($sresult) {
                                            $query = "DELETE FROM `event_lti_consumers` WHERE `id`=".$db->qstr($lti_id)." AND `event_id`=".$db->qstr($EVENT_ID);
                                            if($db->Execute($query)) {
                                                if($db->Affected_Rows()) {
                                                    application_log("success", "Deleted course ".$sresult["lti_title"]." [ID: ".$lti_id."] from database.");
                                                } else {
                                                    application_log("error", "Trying to delete course ".$sresult["lti_title"]." [ID: ".$lti_id."] from database, but there were no rows affected. Database said: ".$db->ErrorMsg());
                                                }
                                            } else {
                                                $ERROR++;
                                                $ERRORSTR[] = "We are unable to delete ".$sresult["lti_title"]." from the course at this time. The system administrator has been informed of the error, please try again later.";

                                                application_log("error", "Trying to delete course ".$sresult["lti_title"]." [ID: ".$link_id."] from database, but the execute statement returned false. Database said: ".$db->ErrorMsg());
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

                if (!isset($_POST["cascade"]) || !$_POST["cascade"]) {
                    ?>
                    <style type="text/css">
                    textarea.expandable {
                        width: 90%;
                    }
                    </style>
                    <?php
                    $HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/wizard.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
                    $HEAD[] = "<link href=\"".ENTRADA_URL."/css/wizard.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />";
                    ?>
                    <iframe id="upload-frame" name="upload-frame" onload="frameLoad()" style="display: none;"></iframe>
                    <a id="false-link" href="#placeholder"></a>
                    <div id="placeholder" style="display: none"></div>
                    <script type="text/javascript">
                    jQuery(document).ready(function () {
                        jQuery("#delay_release_option_date").css("margin", "0");
                        jQuery("#delay_release").is(":checked") ? jQuery("#delay_release_controls").show() : jQuery("#delay_release_controls").hide();
                        jQuery("#delay_release").on("click", function() {
                            jQuery("#delay_release_controls").toggle(this.checked);
                        });

                        jQuery(".remove-hottopic").on("click", function(e) {
                            jQuery("#topic_"+jQuery(this).attr("data-id")+"_major").removeAttr("checked");
                            jQuery("#topic_"+jQuery(this).attr("data-id")+"_minor").removeAttr("checked");
                            e.preventDefault();
                        });
				    });

                    var ajax_url = '';
                    var modalDialog;
                    document.observe('dom:loaded', function() {
                        modalDialog = new Control.Modal($('false-link'), {
                            position:		'center',
                            overlayOpacity:	0.75,
                            closeOnClick:	'overlay',
                            className:		'modal',
                            fade:			true,
                            fadeDuration:	0.30,
                            beforeOpen: function(request) {
                                eval($('scripts-on-open').innerHTML);
                            },
                            afterClose: function() {
                                if (uploaded == true) {
                                    location.reload();
                                }
                            }
                        });
                    });

                    function openDialog (url) {
                        if (url) {
                            ajax_url = url;
                            new Ajax.Request(ajax_url, {
                                method: 'get',
                                onComplete: function(transport) {
                                    modalDialog.container.update(transport.responseText);
                                    modalDialog.open();
                                    var windowHeight = jQuery(window).outerHeight();
                                    var modalHeight = jQuery("#placeholder.modal").outerHeight();
                                    if (modalHeight >= windowHeight) {
                                        jQuery(document).scrollTop(0);
                                    }
                                }
                            });
                        } else {
                            $('scripts-on-open').update();
                            modalDialog.open();
                        }
                    }

                    function confirmFileDelete() {
                        ask_user = confirm("Press OK to confirm that you would like to delete the selected file or files from this event, otherwise press Cancel.");

                        if (ask_user == true) {
                            $('file-listing').submit();
                        } else {
                            return false;
                        }
                    }

                    function confirmLinkDelete() {
                        ask_user = confirm("Press OK to confirm that you would like to delete the selected link or links from this event, otherwise press Cancel.");

                        if (ask_user == true) {
                            $('link-listing').submit();
                        } else {
                            return false;
                        }
                    }

                    function confirmQuizDelete() {
                        ask_user = confirm("Press OK to confirm that you would like to detach the selected quiz or quizzes from this event, otherwise press Cancel.");

                        if (ask_user == true) {
                            $('quiz-listing').submit();
                        } else {
                            return false;
                        }
                    }

                    function confirmLTIDelete() {
                        ask_user = confirm("Press OK to confirm that you would like to delete the selected LTI Provider or LTI Providers from this event, otherwise press Cancel.");

                        if (ask_user == true) {
                            $('lti-listing').submit();
                        } else {
                            return false;
                        }
                    }

                    function updateEdChecks(obj) {
                        return true;
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

                    function submitAndCascade() {
                        jQuery('#cascade').val(1);
                        jQuery('#content_form').submit();
                    }
                    </script>
                    <?php
                    events_subnavigation($event_info,'content');


                    echo "<div class=\"content-small\">".fetch_course_path($event_info["course_id"])."</div>\n";
                    echo "<h1 id=\"page-top\" class=\"event-title\">".html_encode($event_info["event_title"])."</h1>\n";

                    if ($SUCCESS) {
                        fade_element("out", "display-success-box");
                        echo display_success();
                    }

                    if ($NOTICE) {
                        echo display_notice();
                    }

                    if ($ERROR) {
                        echo display_error();
                    }
                    ?>
                    <form id="content_form" action="<?php echo ENTRADA_URL; ?>/admin/events?<?php echo replace_query(); ?>" method="post">
                        <input type="hidden" name="type" value="content" />
                        <input type="hidden" id="cascade" name="cascade" value="0" />
                        <a name="event-details-section"></a>
                        <h2 title="Event Details Section">Event Details</h2>
                        <div id="event-details-section">
                            <table style="width: 100%" cellspacing="0" cellpadding="0" border="0" summary="Event Details" class="table">
                                <colgroup>
                                    <col style="width: 20%" />
                                    <col style="width: 80%" />
                                </colgroup>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" style="text-align: right; padding-top: 5px">
                                            <?php
                                            if ($recurring_events) {
                                                ?>
                                                <div class="dropdown btn-group">
                                                    <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">
                                                        Save
                                                        <span class="caret"></span>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="#" onclick="jQuery('#content_form').submit()">and return to this page</a></li>
                                                        <li><a href="#" onclick="submitAndCascade()">and update recurring events</a></li>
                                                    </ul>
                                                </div>
                                                <?php
                                            } else {
                                                ?>
                                                <input type="submit" class="btn btn-primary" value="Save" />
                                                <?php
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <tr>
                                        <td>Event Date &amp; Time:</td>
                                        <td><?php echo date(DEFAULT_DATE_FORMAT, $event_info["event_start"]); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Event Duration:</td>
                                        <td><?php echo (($event_info["event_duration"]) ? $event_info["event_duration"]." minutes" : "To Be Announced"); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Event Location:</td>
                                        <td><?php echo (($event_info["event_location"]) ? $event_info["event_location"] : "To Be Announced"); ?></td>
                                    </tr>
                                    <?php
                                    if ($event_audience_type == "grad_year") {
                                        $query		= "	SELECT a.`event_id`, a.`event_title`, b.`audience_value` AS `event_grad_year`
                                                        FROM `events` AS a
                                                        LEFT JOIN `event_audience` AS b
                                                        ON b.`event_id` = a.`event_id`
                                                        JOIN `courses` AS c
                                                        ON a.`course_id` = c.`course_id`
                                                        AND c.`organisation_id` = ".$db->qstr($event_info["organisation_id"])."
                                                        WHERE (a.`event_start` BETWEEN ".$db->qstr($event_info["event_start"])." AND ".$db->qstr(($event_info["event_finish"] - 1)).")
                                                        AND a.`event_id` <> ".$db->qstr($event_info["event_id"])."
                                                        AND b.`audience_type` = 'grad_year'
                                                        AND b.`audience_value` = ".$db->qstr((int) $associated_grad_year)."
                                                        ORDER BY a.`event_title` ASC";
                                        $results	= $db->GetAll($query);
                                        if ($results) {
                                            echo "<tr>\n";
                                            echo "	<td colspan=\"2\">&nbsp;</td>\n";
                                            echo "</tr>\n";
                                            echo "<tr>\n";
                                            echo "	<td style=\"vertical-align: top\">Overlapping Event".((count($results) != 1) ? "s" : "")."</td>\n";
                                            echo "	<td>\n";
                                            foreach ($results as $result) {
                                                echo "	<a href=\"".ENTRADA_URL."/admin/events?id=".$result["event_id"]."&section=content\">".html_encode($result["event_title"])."</a><br />\n";
                                            }
                                            echo "	</td>\n";
                                            echo "</tr>\n";
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td colspan="2">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: top">Associated Faculty:</td>
                                        <td>
                                            <?php
                                            $query		= "	SELECT a.`proxy_id`, CONCAT_WS(' ', b.`firstname`, b.`lastname`) AS `fullname`, a.`contact_role`, b.`email`
                                                            FROM `event_contacts` AS a
                                                            LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
                                                            ON b.`id` = a.`proxy_id`
                                                            WHERE a.`event_id` = ".$db->qstr($event_info["event_id"])."
                                                            AND b.`id` IS NOT NULL
                                                            ORDER BY a.`contact_order` ASC";
                                            $results	= $db->GetAll($query);
                                            if ($results) {
                                                foreach ($results as $key => $result) {
                                                    echo "<a href=\"mailto:".html_encode($result["email"])."\">".html_encode($result["fullname"])."</a> - ".(($result["contact_role"] == "ta")?"Teacher's Assistant":html_encode(ucwords($result["contact_role"])))."<br />\n";
                                                }
                                            } else {
                                                echo "To Be Announced";
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: top"><label for="eventtype_ids" class="form-required">Event Types:</label></td>
                                        <td style="padding-bottom: 15px">
                                            <select id="eventtype_ids" name="eventtype_ids">
                                                <option id="-1"> -- Pick a type to add -- </option>
                                                <?php
                                                if ((is_array($event_eventtypes_list)) && (count($event_eventtypes_list))) {
                                                    foreach ($event_eventtypes_list as $eventtype_id => $eventtype_title) {
                                                        echo "<option value=\"".$eventtype_id."\">".html_encode($eventtype_title)."</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                            <div id="duration_notice" class="content-small">Use the list above to select the different components of this event. When you select one, it will appear here and you can change the order and duration.</div>
                                            <?php
                                            echo "<ol id=\"duration_container\" class=\"sortableList\" style=\"display: none\">\n";
                                            if (is_array($event_eventtypes)) {
                                                foreach ($event_eventtypes as $eventtype) {
                                                    echo "<li id=\"type_".(int) $eventtype["eventtype_id"]."\" class=\"\">".html_encode($eventtype["eventtype_title"])."
                                                        <a href=\"#\" onclick=\"$(this).up().remove(); cleanupList(); return false;\" class=\"remove\">
                                                            <img src=\"".ENTRADA_URL."/images/action-delete.gif\">
                                                        </a>
                                                        <span class=\"duration_segment_container\">
                                                            Duration: <input type=\"text\" class=\"input-mini duration_segment\" name=\"duration_segment[]\" onchange=\"cleanupList();\" value=\"".(int) $eventtype["duration"]."\"> minutes
                                                        </span>
                                                    </li>";
                                                }
                                            echo "</ol>";
                                            }
                                            ?>
                                            <div id="total_duration" class="content-small">Total time: 0 minutes.</div>
                                            <input id="eventtype_duration_order" name="eventtype_duration_order" style="display: none;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <label for="event_description" class="form-nrequired">Event Description:</label><br />
                                            <textarea id="event_description" name="event_description" style="width: 100%; height: 100px" cols="70" rows="10"><?php echo html_encode(trim(strip_selected_tags($event_info["event_description"], array("font")))); ?></textarea>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan="2">
                                            <label for="event_message" class="form-nrequired">Required Preparation:</label><br />
                                            <textarea id="event_message" name="event_message" style="width: 100%; height: 100px" cols="70" rows="10"><?php echo html_encode(trim(strip_selected_tags($event_info["event_message"], array("font")))); ?></textarea>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <a name="event-objectives-section"></a>
                        <h2 title="Event Objectives Section">Event Objectives</h2>
                        <div id="event-objectives-section">
                            <div class="row-fluid">
                                <label class="checkbox">
                                    <input type="checkbox" id="delay_release" name="delay_release" value="1" <?php echo ($event_info["objectives_release_date"] != 0 || isset($PROCESSED["delay_release"]) ? " checked=\"checked\"" : "") ?> />
                                    Delay the release of all objectives
                                </label>
                                <div id="delay_release_controls" class="space-below">
                                    <?php echo generate_calendar("delay_release_option", "Delay release until", true, $PROCESSED["objectives_release_date"], true, false, false, false, false); ?>
                                </div>
                            </div>

                            <label for="event_objectives" class="form-nrequired">Free-Text Objectives</label><br />
                            <textarea id="event_objectives" name="event_objectives" style="width: 100%; height: 100px" cols="70" rows="10"><?php echo html_encode(trim(strip_selected_tags($event_info["event_objectives"], array("font")))); ?></textarea>

                            <?php
                            $query = "	SELECT a.* FROM `global_lu_objectives` a
                                        JOIN `objective_audience` b
                                        ON a.`objective_id` = b.`objective_id`
                                        AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
                                        WHERE (
                                                (b.`audience_value` = 'all')
                                                OR
                                                (b.`audience_type` = 'course' AND b.`audience_value` = ".$db->qstr($COURSE_ID).")
                                                OR
                                                (b.`audience_type` = 'event' AND b.`audience_value` = ".$db->qstr($EVENT_ID).")
                                            )
                                        AND a.`objective_parent` = '0'
                                        AND a.`objective_active` = '1'";
                            $objectives = $db->GetAll($query);

                            if ($objectives) {
                                $objective_name = $translate->_("events_filter_controls");
                                $hierarchical_name = $objective_name["co"]["global_lu_objectives_name"];
                                ?>
                                <style type="text/css">
                                    .mapped-objective{
                                        padding-left: 30px!important;
                                    }
                                </style>
                                <div class="objectives half left">
                                    <h3>Objective Sets</h3>
                                    <ul class="tl-objective-list" id="objective_list_0">
                            <?php		foreach($objectives as $objective){
                                            ?>

                                            <li class = "objective-container objective-set"
                                                id = "objective_<?php echo $objective["objective_id"]; ?>"
                                                data-list="<?php echo $objective["objective_name"] == $hierarchical_name?'hierarchical':'flat'; ?>"
                                                data-id="<?php echo $objective["objective_id"]; ?>">
                                                <?php $title = ($objective["objective_code"]?$objective["objective_code"].': '.$objective["objective_name"]:$objective["objective_name"]); ?>
                                                <div 	class="objective-title"
                                                        id="objective_title_<?php echo $objective["objective_id"]; ?>"
                                                        data-title="<?php echo $title;?>"
                                                        data-id = "<?php echo $objective["objective_id"]; ?>"
                                                        data-code = "<?php echo $objective["objective_code"]; ?>"
                                                        data-name = "<?php echo $objective["objective_name"]; ?>"
                                                        data-description = "<?php echo $objective["objective_description"]; ?>">
                                                    <h4><?php echo $title; ?></h4>
                                                </div>
                                                <div class="objective-controls" id="objective_controls_<?php echo $objective["objective_id"];?>">
                                                </div>
                                                <div class="objective-children" id="children_<?php echo $objective["objective_id"]; ?>">
                                                    <ul class="objective-list" id="objective_list_<?php echo $objective["objective_id"]; ?>"></ul>
                                                </div>
                                            </li>
                            <?php 		} ?>
                                    </ul>
                                </div>


                    <?php   $query = "	SELECT a.*, COALESCE(b.`objective_details`,a.`objective_description`) AS `objective_description`, COALESCE(b.`objective_type`,c.`objective_type`) AS `objective_type`,
                                        b.`importance`,c.`objective_details`, COALESCE(c.`eobjective_id`,0) AS `mapped`,
                                        COALESCE(b.`cobjective_id`,0) AS `mapped_to_course`
                                        FROM `global_lu_objectives` a
                                        LEFT JOIN `course_objectives` b
                                        ON a.`objective_id` = b.`objective_id`
                                        AND b.`course_id` = ".$db->qstr($COURSE_ID)."
                                        LEFT JOIN `event_objectives` c
                                        ON c.`objective_id` = a.`objective_id`
                                        AND c.`event_id` = ".$db->qstr($EVENT_ID)."
                                        WHERE a.`objective_active` = '1'
                                        AND (c.`event_id` = ".$db->qstr($EVENT_ID)." OR b.`course_id` = ".$db->qstr($COURSE_ID).")
                                        GROUP BY a.`objective_id`
                                        ORDER BY a.`objective_id` ASC";
                            $mapped_objectives = $db->GetAll($query);
                            $primary = false;
                            $secondary = false;
                            $tertiary = false;
                            $hierarchical_objectives = array();
                            $flat_objectives = array();
                            $explicit_event_objectives = false;//array();
                            $mapped_event_objectives = array();
                            if ($mapped_objectives) {
                                foreach ($mapped_objectives as $objective) {
                                    //if its mapped to the event, but not the course, then it belongs in the event objective list
                                    //echo $objective["objective_name"].' is '.$objective["mapped"].' and '.$objective["mapped_to_course"]."<br/>";
                                    if ($objective["mapped"] && !$objective["mapped_to_course"]) {
                                        if (!event_objective_parent_mapped_course($objective["objective_id"],$EVENT_ID)) {
                                            $explicit_event_objectives[] = $objective;
                                        } else {
                                            if ($objective["objective_type"] == "course") {
                                                //$objective_id = $objective["objective_id"];
                                                $hierarchical_objectives[] = $objective;
                                            } else {
                                                $flat_objectives[] = $objective;
                                            }
                                        }
                                    } else {
                                        if ($objective["objective_type"] == "course") {
                                            //$objective_id = $objective["objective_id"];
                                            $hierarchical_objectives[] = $objective;
                                        } else {
                                            $flat_objectives[] = $objective;
                                        }
                                    }

                                    if ($objective["mapped"]) {
                                        $mapped_event_objectives[] = $objective;
                                    }
                                }
                            }
                            ?>

                            <div class="mapped_objectives right droppable" id="mapped_objectives" data-resource-type="event" data-resource-id="<?php echo $EVENT_ID;?>">
                                <h3>Mapped Objectives</h3>
                                <div class="row-fluid space-below">
                                    <a href="javascript:void(0)" class="mapping-toggle btn btn-success btn-small pull-right" data-toggle="show" id="toggle_sets"><i class="icon-plus-sign icon-white"></i> Map Additional Objectives</a>
                                </div>
                                <p class="well well-small content-small">
                                    <strong>Helpful Tip:</strong> Click <strong>Map Additional Objectives</strong> to view the list of available objectives. Select an objective from the list on the left and it will be mapped to the event.
                                </p>
                                <?php
                                if ($hierarchical_objectives) {
                                    // function loads bottom leaves and displays them
                                    event_objectives_display_leafs($hierarchical_objectives, $COURSE_ID, $EVENT_ID);
                                }
                                if ($flat_objectives) {
                                    ?>
                                    <div id="clinical-list-wrapper">
                                        <a name="clinical-objective-list"></a>
                                        <h2 id="flat-toggle"  title="Clinical Objective List" class="<?php echo empty($objective_name["cp"]["global_lu_objectives_name"]) ? "collapsed" : ""; ?> list-heading"><?php echo $objective_name["cp"]["global_lu_objectives_name"] ? $objective_name["cp"]["global_lu_objectives_name"] : "Other Objectives"; ?></h2>
                                        <div id="clinical-objective-list">
                                            <ul class="objective-list mapped-list" id="mapped_flat_objectives" data-importance="flat">
                                                <?php
                                                foreach ($flat_objectives as $objective) {
                                                    $title = ($objective["objective_code"]?$objective["objective_code"].': '.$objective["objective_name"]:$objective["objective_name"]);
                                                    ?>
                                                    <li class = "mapped-objective"
                                                        id = "mapped_objective_<?php echo $objective["objective_id"]; ?>"
                                                        data-id = "<?php echo $objective["objective_id"]; ?>"
                                                        data-title="<?php echo $title;?>"
                                                        data-description="<?php echo htmlentities($objective["objective_description"]);?>">
                                                        <strong><?php echo $title; ?></strong>
                                                        <div class="objective-description">
                                                            <?php
                                                            $set = fetch_objective_set_for_objective_id($objective["objective_id"]);
                                                            if ($set) {
                                                                echo "From the Objective Set: <strong>".$set["objective_name"]."</strong><br/>";
                                                            }

                                                            echo $objective["objective_description"];
                                                            ?>
                                                        </div>

                                                        <div class="event-objective-controls">
                                                            <input type="checkbox" class="checked-mapped" id="check_mapped_<?php echo $objective['objective_id'];?>" value="<?php echo $objective['objective_id'];?>" <?php echo $objective["mapped"]?' checked="checked"':''; ?>/>
                                                        </div>
                                                    </li>
                                                    <?php
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>

                                <div id="event-list-wrapper" <?php echo ($explicit_event_objectives)?'':' style="display:none;"';?>>
                                    <a name="event-objective-list"></a>
                                    <h2 id="event-toggle"  title="Event Objective List" class="collapsed list-heading">Event Specific Objectives</h2>
                                    <div id="event-objective-list">
                                        <ul class="objective-list mapped-list" id="mapped_event_objectives" data-importance="event">
                                            <?php
                                            if ($explicit_event_objectives) {
                                                foreach ($explicit_event_objectives as $objective) {
                                                    $title = ($objective["objective_code"] ? $objective["objective_code"] . ': ' . $objective["objective_name"] : $objective["objective_name"]);
                                                    ?>
                                                    <li class = "mapped-objective"
                                                        id = "mapped_objective_<?php echo $objective["objective_id"]; ?>"
                                                        data-id = "<?php echo $objective["objective_id"]; ?>"
                                                        data-title="<?php echo $title;?>"
                                                        data-description="<?php echo htmlentities($objective["objective_description"]);?>"
                                                        data-mapped="<?php echo $objective["mapped_to_course"]?1:0;?>">
                                                        <strong><?php echo $title; ?></strong>
                                                        <div class="objective-description">
                                                            <?php
                                                            $set = fetch_objective_set_for_objective_id($objective["objective_id"]);
                                                            if ($set) {
                                                                echo "From the Objective Set: <strong>".$set["objective_name"]."</strong><br/>";
                                                            }

                                                            echo $objective["objective_description"];
                                                            ?>
                                                        </div>

                                                        <div class="event-objective-controls">
                                                            <img 	src="<?php echo ENTRADA_URL;?>/images/action-delete.gif"
                                                                    class="objective-remove list-cancel-image"
                                                                    id="objective_remove_<?php echo $objective["objective_id"];?>"
                                                                    data-id="<?php echo $objective["objective_id"];?>">
                                                        </div>
                                                    </li>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                                <select id="checked_objectives_select" name="checked_objectives[]" multiple="multiple" style="display:none;">
                                    <?php
                                    if ($mapped_event_objectives) {
                                        foreach ($mapped_event_objectives as $objective) {
                                            if ($objective["objective_type"] == "course") {
                                                $title = ($objective["objective_code"] ? $objective["objective_code"] . ': ' . $objective["objective_name"] : $objective["objective_name"]);
                                                ?>
                                                <option value = "<?php echo $objective["objective_id"]; ?>" selected="selected"><?php echo $title; ?></option>
                                                <?php
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                                <select id="clinical_objectives_select" name="clinical_presentations[]" multiple="multiple" style="display:none;">
                                    <?php
                                    if ($mapped_event_objectives) {
                                        foreach($mapped_event_objectives as $objective){
                                            if($objective["objective_type"] == "event") {
                                                $title = ($objective["objective_code"]?$objective["objective_code"].': '.$objective["objective_name"]:$objective["objective_name"]);
                                                ?>
                                                <option value = "<?php echo $objective["objective_id"]; ?>" selected="selected"><?php echo $title; ?></option>
                                                <?php
                                            }
                                        }
                                    }
                                    ?>
                                </select>

                            </div>
                            <div style="clear:both;"></div>
                            <div class="pull-right">
                                <?php
                                if ($recurring_events) {
                                    ?>
                                    <div class="dropdown btn-group">
                                        <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">
                                            Save
                                            <span class="caret"></span>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li><a href="#" onclick="jQuery('#content_form').submit()">and return to this page</a></li>
                                            <li><a href="#" onclick="submitAndCascade()">and update recurring events</a></li>
                                        </ul>
                                    </div>
                                    <?php
                                } else {
                                    ?>
                                    <input type="submit" class="btn btn-primary" value="Save" />
                                    <?php
                                }
                                ?>
                            </div>

                            <?php 	} 	?>
                        </div>

                        <?php
                        $query = "	SELECT a.`topic_id`,a.`topic_name`, e.`topic_coverage`,e.`topic_time`
                                    FROM `events_lu_topics` AS a
                                    LEFT JOIN `topic_organisation` AS b
                                    ON a.`topic_id` = b.`topic_id`
                                    LEFT JOIN `courses` AS c
                                    ON b.`organisation_id` = c.`organisation_id`
                                    LEFT JOIN `events` AS d
                                    ON c.`course_id` = d.`course_id`
                                    LEFT JOIN `event_topics` AS e
                                    ON d.`event_id` = e.`event_id`
                                    AND a.`topic_id` = e.`topic_id`
                                    WHERE d.`event_id` = ".$db->qstr($EVENT_ID)."
                                    ORDER BY a.`topic_name`";
                        $topic_results = $db->GetAll($query);
                        if ($topic_results) {
                            ?>
                            <a name="event-topics-section"></a>
                            <h2 title="Event Topics Section">Event Topics</h2>
                            <div id="event-topics-section">
                                <div class="content-small">
                                    <p>Please check off a topic as <strong>MAJOR</strong> if it is encompassed in a learning objective of your session, or if you have taught enough about it that it would be reasonable to include an assessment item about the topic.</p>
                                    <p>Please check off a topic as <strong>MINOR</strong> if you mentioned the topic but only briefly.</p>
                                </div>
                                <table style="width: 100%" cellspacing="0" summary="List of ED10">
                                    <colgroup>
                                        <col style="width: 76%" />
                                        <col style="width: 8%" />
                                        <col style="width: 8%" />
                                        <col style="width: 8%" />
                                    </colgroup>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" style="text-align: right; padding-top: 5px">
                                                <?php
                                                if ($recurring_events) {
                                                    ?>
                                                    <div class="dropdown btn-group">
                                                        <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">
                                                            Save
                                                            <span class="caret"></span>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li><a href="#" onclick="jQuery('#content_form').submit()">and return to this page</a></li>
                                                            <li><a href="#" onclick="submitAndCascade()">and update recurring events</a></li>
                                                        </ul>
                                                    </div>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <input type="submit" class="btn btn-primary" value="Save" />
                                                    <?php
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    </tfoot>
                                    <tr>
                                        <td><span style="font-weight: bold; color: #003366;">Hot Topic</span></td>
                                        <td align="center"><span style="font-weight: bold; color: #003366;">Major</span></td>
                                        <td align="center"><span style="font-weight: bold; color: #003366;">Major</span></td>
                                        <td align="center"><span style="font-weight: bold; color: #003366;">Remove</span></td>
                                    </tr>
                                    <?php
                                    foreach ($topic_results as $topic_result) {
                                        echo "<tr>\n";
                                        echo "	<td>".html_encode($topic_result["topic_name"])."</td>\n";
                                        echo "	<td align=\"center\">";
                                        echo "		<input type=\"radio\" id=\"topic_".$topic_result["topic_id"]."_major\" name=\"event_topic[".$topic_result["topic_id"]."]\" value=\"major\" onclick=\"updateEdChecks(this)\"".(($topic_result["topic_coverage"] == "major") ? " checked=\"checked\"" : "")." />";
                                        echo "	</td>\n";
                                        echo "	<td align=\"center\">";
                                        echo "		<input type=\"radio\" id=\"topic_".$topic_result["topic_id"]."_minor\" name=\"event_topic[".$topic_result["topic_id"]."]\" value=\"minor\" ".(($topic_result["topic_coverage"] == "minor") ? " checked=\"checked\"" : "")."/>";
                                        echo "	</td>\n";
                                        echo "  <td align=\"center\"><a href=\"#\" class=\"remove-hottopic\" data-id=\"".$topic_result["topic_id"]."\"><i class=\"icon-remove\"></i></a></td>";
                                        echo "</tr>\n";
                                    }
                                    echo "<tr><td colspan=\"3\">&nbsp;</td></tr>";
                                    ?>
                                </table>
                            </div>
                            <?php
                        }
                        ?>
                    </form>

                    <a name="event-resources-section"></a>
                    <h2 title="Event Resources Section">Event Resources</h2>
                    <div id="event-resources-section">
                        <div style="margin-bottom: 15px">
                            <div style="float: left; margin-bottom: 5px">
                                <h3>Attached Files</h3>
                            </div>
                            <div class="pull-right">
                                <input type="button" class="btn" onclick="openDialog('<?php echo ENTRADA_URL; ?>/api/file-wizard-event.api.php?action=add&id=<?php echo $EVENT_ID; ?>')" value="Add A File" />
                            </div>
                            <div class="clear"></div>
                            <?php
                            $query		= "	SELECT *
                                            FROM `event_files`
                                            WHERE `event_id` = ".$db->qstr($EVENT_ID)."
                                            ORDER BY `file_category` ASC, `file_title` ASC";
                            $results	= $db->GetAll($query);
                            echo "<form id=\"file-listing\" action=\"".ENTRADA_URL."/admin/events?".replace_query()."\" method=\"post\">\n";
                            echo "<input type=\"hidden\" name=\"type\" value=\"files\" />\n";
                            echo "<table class=\"tableList\" cellspacing=\"0\" summary=\"List of Attached Files\">\n";
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
                            echo "			".(($results) ? "<input type=\"button\" class=\"btn btn-danger\" value=\"Delete Selected\" onclick=\"confirmFileDelete()\" />" : "&nbsp;");
                            echo "		</td>\n";
                            echo "	</tr>\n";
                            echo "</tfoot>\n";
                            echo "<tbody>\n";
                            if ($results) {
                                foreach ($results as $result) {
                                    $filename	= $result["file_name"];
                                    $parts		= pathinfo($filename);
                                    $ext		= $parts["extension"];

                                    echo "<tr>\n";
                                    echo "	<td class=\"modified\" style=\"width: 50px; white-space: nowrap\">\n";
                                    echo "		<input type=\"checkbox\" name=\"delete[]\" value=\"".$result["efile_id"]."\" style=\"vertical-align: middle\" />\n";
                                    echo "		<a href=\"".ENTRADA_URL."/file-event.php?id=".$result["efile_id"]."\" target=\"_blank\"><img src=\"".ENTRADA_URL."/images/btn_save.gif\" width=\"16\" height=\"16\" alt=\"Download ".html_encode($result["file_name"])." to your computer.\" title=\"Download ".html_encode($result["file_name"])." to your computer.\" style=\"vertical-align: middle\" border=\"0\" /></a>\n";
                                    echo "	</td>\n";
                                    echo "	<td class=\"file-category\">".((isset($RESOURCE_CATEGORIES["event"][$result["file_category"]])) ? html_encode($RESOURCE_CATEGORIES["event"][$result["file_category"]]) : "Unknown Category")."</td>\n";
                                    echo "	<td class=\"title\">\n";
                                    echo "		<img src=\"".ENTRADA_URL."/serve-icon.php?ext=".$ext."\" width=\"16\" height=\"16\" alt=\"".strtoupper($ext)." Document\" title=\"".strtoupper($ext)." Document\" style=\"vertical-align: middle\" />";
                                    echo "		<a href=\"#file-listing\" onclick=\"openDialog('".ENTRADA_URL."/api/file-wizard-event.api.php?action=edit&id=".$EVENT_ID."&fid=".$result["efile_id"]."')\" title=\"Click to edit ".html_encode($result["file_title"])."\" style=\"font-weight: bold\">".html_encode($result["file_title"])."</a>";
                                    echo "	</td>\n";
                                    echo "	<td class=\"date-small\"><span class=\"content-date\">".(((int) $result["release_date"]) ? date(DEFAULT_DATE_FORMAT, $result["release_date"]) : "No Restrictions")."</span></td>\n";
                                    echo "	<td class=\"date-small\"><span class=\"content-date\">".(((int) $result["release_until"]) ? date(DEFAULT_DATE_FORMAT, $result["release_until"]) : "No Restrictions")."</span></td>\n";
                                    echo "	<td class=\"accesses\" style=\"text-align: center\">".$result["accesses"]."</td>\n";
                                    echo "</tr>\n";
                                }
                            } else {
                                echo "<tr>\n";
                                echo "	<td colspan=\"6\">\n";
                                echo "		<div class=\"display-generic\">\n";
                                echo "			There have been no files added to this event. To <strong>add a new file</strong>, simply click the Add File button.\n";
                                echo "		</div>\n";
                                echo "	</td>\n";
                                echo "</tr>\n";
                            }
                            echo "</tbody>\n";
                            echo "</table>\n";
                            echo "</form>\n";
                            ?>
                        </div>

                        <div style="margin-bottom: 15px">
                            <div style="float: left; margin-bottom: 5px">
                                <h3>Attached Links</h3>
                            </div>

                            <div class="pull-right">
                                <input type="button" class="btn" onclick="openDialog('<?php echo ENTRADA_URL; ?>/api/link-wizard-event.api.php?action=add&id=<?php echo $EVENT_ID; ?>')" value="Add A Link" />
                            </div>
                            <div class="clear"></div>
                            <?php
                            $query		= "	SELECT *
                                            FROM `event_links`
                                            WHERE `event_id`=".$db->qstr($EVENT_ID)."
                                            ORDER BY `link_title` ASC";
                            $results	= $db->GetAll($query);
                            echo "<form id=\"link-listing\" action=\"".ENTRADA_URL."/admin/events?".replace_query()."\" method=\"post\">\n";
                            echo "<input type=\"hidden\" name=\"type\" value=\"links\" />\n";
                            echo "<table class=\"tableList\" cellspacing=\"0\" summary=\"List of Attached Links\">\n";
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
                            echo "		<td class=\"title sortedASC\"><div class=\"noLink\">Linked Resources</div></td>\n";
                            echo "		<td class=\"date-small\">Accessible Start</td>\n";
                            echo "		<td class=\"date-small\">Accessible Finish</td>\n";
                            echo "		<td class=\"accesses\">Hits</td>\n";
                            echo "	</tr>\n";
                            echo "</thead>\n";
                            echo "<tfoot>\n";
                            echo "	<tr>\n";
                            echo "		<td>&nbsp;</td>\n";
                            echo "		<td colspan=\"4\" style=\"padding-top: 10px\">\n";
                            echo "			".(($results) ? "<input type=\"button\" class=\"btn btn-danger\" value=\"Delete Selected\" onclick=\"confirmLinkDelete()\" />" : "&nbsp;");
                            echo "		</td>\n";
                            echo "	</tr>\n";
                            echo "</tfoot>\n";
                            echo "<tbody>\n";
                            if ($results) {
                                foreach ($results as $result) {
                                    echo "<tr>\n";
                                    echo "	<td class=\"modified\" style=\"width: 50px; white-space: nowrap\">\n";
                                    echo "		<input type=\"checkbox\" name=\"delete[]\" value=\"".$result["elink_id"]."\" style=\"vertical-align: middle\" />\n";
                                    echo "		<a href=\"".ENTRADA_URL."/link-event.php?id=".$result["elink_id"]."\" target=\"_blank\"><img src=\"".ENTRADA_URL."/images/url-visit.gif\" width=\"16\" height=\"16\" alt=\"Visit ".html_encode($result["link"])."\" title=\"Visit ".html_encode($result["link"])."\" style=\"vertical-align: middle\" border=\"0\" /></a>\n";
                                    echo "	</td>\n";
                                    echo "	<td class=\"title\" style=\"white-space: normal; overflow: visible\">\n";
                                    echo "		<a href=\"#link-listing\" onclick=\"openDialog('".ENTRADA_URL."/api/link-wizard-event.api.php?action=edit&id=".$EVENT_ID."&lid=".$result["elink_id"]."')\" title=\"Click to edit ".html_encode($result["link"])."\" style=\"font-weight: bold\">".(($result["link_title"] != "") ? html_encode($result["link_title"]) : $result["link"])."</a>\n";
                                    echo "	</td>\n";
                                    echo "	<td class=\"date-small\"><span class=\"content-date\">".(((int) $result["release_date"]) ? date(DEFAULT_DATE_FORMAT, $result["release_date"]) : "No Restrictions")."</span></td>\n";
                                    echo "	<td class=\"date-small\"><span class=\"content-date\">".(((int) $result["release_until"]) ? date(DEFAULT_DATE_FORMAT, $result["release_until"]) : "No Restrictions")."</span></td>\n";
                                    echo "	<td class=\"accesses\" style=\"text-align: center\">".$result["accesses"]."</td>\n";
                                    echo "</tr>\n";
                                }
                            } else {
                                echo "<tr>\n";
                                echo "	<td colspan=\"5\">\n";
                                echo "		<div class=\"display-generic\">\n";
                                echo "			There have been no links added to this event. To <strong>add a new link</strong>, simply click the Add Link button.\n";
                                echo "		</div>\n";
                                echo "	</td>\n";
                                echo "</tr>\n";
                            }
                            echo "</tbody>\n";
                            echo "</table>\n";
                            echo "</form>\n";
                            ?>
                        </div>

                        <div style="margin-bottom: 15px">
                            <div style="float: left; margin-bottom: 5px">
                                <h3>Attached Quizzes</h3>
                            </div>

                            <div class="pull-right">
                                    <input type="button" class="btn" onclick="window.location = '<?php echo ENTRADA_URL; ?>/admin/quizzes?section=add'" value="Create New Quiz" />
                                    <input type="button" class="btn" onclick="openDialog('<?php echo ENTRADA_URL; ?>/api/quiz-wizard.api.php?action=add&id=<?php echo $EVENT_ID; ?>')" value="Attach Existing Quiz" />
                            </div>
                            <div class="clear"></div>
                            <?php
                            $query		= "	SELECT a.*, b.`quiztype_title`
                                            FROM `attached_quizzes` AS a
                                            LEFT JOIN `quizzes_lu_quiztypes` AS b
                                            ON b.`quiztype_id` = a.`quiztype_id`
                                            WHERE a.`content_type` = 'event'
                                            AND a.`content_id` = ".$db->qstr($EVENT_ID)."
                                            ORDER BY b.`quiztype_title` ASC, a.`quiz_title` ASC";
                            $results	= $db->GetAll($query);
                            echo "<form id=\"quiz-listing\" action=\"".ENTRADA_URL."/admin/events?".replace_query()."\" method=\"post\">\n";
                            echo "<input type=\"hidden\" name=\"type\" value=\"quizzes\" />\n";
                            echo "<table class=\"tableList\" cellspacing=\"0\" summary=\"List of Attached Quizzes\">\n";
                            echo "<colgroup>\n";
                            echo "	<col class=\"modified\" style=\"width: 50px\"  />\n";
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
                            echo "		<td class=\"title\">Quiz Title</td>\n";
                            echo "		<td class=\"date-small\">Accessible Start</td>\n";
                            echo "		<td class=\"date-small\">Accessible Finish</td>\n";
                            echo "		<td class=\"accesses\">Done</td>\n";
                            echo "	</tr>\n";
                            echo "</thead>\n";
                            echo "<tfoot>\n";
                            echo "	<tr>\n";
                            echo "		<td>&nbsp;</td>\n";
                            echo "		<td colspan=\"5\" style=\"padding-top: 10px\">\n";
                            echo "			".(($results) ? "<input type=\"button\" class=\"btn btn-danger\" value=\"Detach Selected\" onclick=\"confirmQuizDelete()\" />" : "&nbsp;");
                            echo "		</td>\n";
                            echo "	</tr>\n";
                            echo "</tfoot>\n";
                            echo "<tbody>\n";
                            if ($results) {
                                foreach ($results as $result) {
                                    $completed_attempts = $db->GetOne("SELECT COUNT(DISTINCT `proxy_id`) FROM `quiz_progress` WHERE `progress_value` = 'complete' AND `aquiz_id` = ".$db->qstr($result["aquiz_id"]));
                                    echo "<tr>\n";
                                    echo "	<td class=\"modified\" style=\"width: 50px; white-space: nowrap\">\n";
                                    echo "		<input type=\"checkbox\" name=\"delete[]\" value=\"".$result["aquiz_id"]."\" style=\"vertical-align: middle\" />\n";
                                    if ($completed_attempts > 0) {
                                        echo "	<a href=\"".ENTRADA_URL."/admin/quizzes?section=results&amp;id=".$result["aquiz_id"]."\"><img src=\"".ENTRADA_URL."/images/view-stats.gif\" width=\"16\" height=\"16\" alt=\"View results of ".html_encode($result["quiz_title"])."\" title=\"View results of ".html_encode($result["quiz_title"])."\" style=\"vertical-align: middle\" border=\"0\" /></a>\n";
                                    } else {
                                        echo "	<img src=\"".ENTRADA_URL."/images/view-stats-disabled.gif\" width=\"16\" height=\"16\" alt=\"No completed quizzes at this time.\" title=\"No completed quizzes at this time.\" style=\"vertical-align: middle\" border=\"0\" />\n";
                                    }
                                    echo "	</td>\n";
                                    echo "	<td class=\"file-category\">".html_encode($result["quiztype_title"])."</td>\n";
                                    echo "	<td class=\"title\" style=\"white-space: normal; overflow: visible\">\n";
                                    echo "		<a href=\"#quiz-listing\" onclick=\"openDialog('".ENTRADA_URL."/api/quiz-wizard.api.php?action=edit&id=".$EVENT_ID."&qid=".$result["aquiz_id"]."')\" title=\"Click to edit ".html_encode($result["quiz_title"])."\" style=\"font-weight: bold\">".html_encode($result["quiz_title"])."</a>\n";
                                    echo "	</td>\n";
                                    echo "	<td class=\"date-small\"><span class=\"content-date\">".(((int) $result["release_date"]) ? date(DEFAULT_DATE_FORMAT, $result["release_date"]) : "No Restrictions")."</span></td>\n";
                                    echo "	<td class=\"date-small\"><span class=\"content-date\">".(((int) $result["release_until"]) ? date(DEFAULT_DATE_FORMAT, $result["release_until"]) : "No Restrictions")."</span></td>\n";
                                    echo "	<td class=\"accesses\" style=\"text-align: center\">".$completed_attempts."</td>\n";
                                    echo "</tr>\n";
                                }
                            } else {
                                echo "<tr>\n";
                                echo "	<td colspan=\"6\">\n";
                                echo "		<div class=\"display-generic\" style=\"white-space: normal\">\n";
                                echo "			There have been no quizzes attached to this event. To <strong>create a new quiz</strong> click the <a href=\"".ENTRADA_URL."/admin/quizzes\" style=\"font-weight: bold\">Manage Quizzes</a> tab, and then to attach the quiz to this event click the <strong>Attach Quiz</strong> button below.\n";
                                echo "		</div>\n";
                                echo "	</td>\n";
                                echo "</tr>\n";
                            }
                            echo "</tbody>\n";
                            echo "</table>\n";
                            echo "</form>\n";
                            ?>
                        </div>

                        <div style="margin-bottom: 15px">
                            <div style="float: left; margin-bottom: 5px">
                                <h3>Attached LTI Providers</h3>
                            </div>
                            <div class="pull-right">
                                <a href="#page-top" onclick="openDialog('<?php echo ENTRADA_URL; ?>/api/lti-wizard-event.api.php?action=add&id=<?php echo $EVENT_ID; ?>')" class="btn">Add LTI Provider</a>
                            </div>
                            <div class="clear"></div>
                            <?php
                            $query		= "SELECT *
                                           FROM `event_lti_consumers`
                                           WHERE `event_id` = ".$db->qstr($EVENT_ID)."
                                           ORDER BY `lti_title` ASC";
                            $results	= $db->GetAll($query);
                            ?>
                            <form id="lti-listing" action="<?php echo ENTRADA_URL; ?>/admin/events?<?php echo replace_query(); ?>" method="post">
                                <input type="hidden" name="type" value="lti" />
                                <table class="tableList" cellspacing="0" summary="List of Attached LTI Providers">
                                    <colgroup>
                                        <col class="modified wide"/>
                                        <col class="title" />
                                        <col class="title" />
                                        <col class="date" />
                                        <col class="date" />
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <td class="modified">&nbsp;</td>
                                            <td class="title sortedASC"><div class="noLink">LTI Provider Title</div></td>
                                            <td class="title">Launch URL</td>
                                            <td class="date-small">Accessible Start</td>
                                            <td class="date-small">Accessible Finish</td>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td colspan="4" style="padding-top: 10px">
                                                <?php
                                                echo (($results) ? "<input type=\"button\" class=\"btn btn-danger\" value=\"Delete Selected\" onclick=\"confirmLTIDelete()\" />" : "&nbsp;")."\n";
                                                ?>
                                            </td>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                    <?php
                                    if($results) {
                                        foreach($results as $result) { ?>
                                            <tr>
                                                <td class="modified wide">
                                                    <input type="checkbox" name="delete[]" value="<?php echo $result["id"];?>"/>
                                                </td>
                                                <td class="title">
                                                    <a 	href="#"
                                                          onclick="openDialog('<?php echo ENTRADA_URL;?>/api/lti-wizard-event.api.php?action=edit&id=<?php echo $EVENT_ID."&ltiid=".$result["id"];?>')"
                                                          title="Click to edit <?php echo html_encode($result["lti_title"]); ?>">
                                                        <strong>
                                                            <?php echo (($result["lti_title"] != "") ? html_encode($result["lti_title"]) : $result["lti_title"]);?>
                                                        </strong>
                                                    </a>
                                                </td>
                                                <td class="title">
                                                    <?php echo (($result["launch_url"] != "") ? html_encode($result["launch_url"]) : $result["launch_url"]);?>
                                                </td>
                                                <td class="date-small">
                                                    <span class="content-date">
                                                        <?php echo (((int) $result["valid_from"]) ? date(DEFAULT_DATE_FORMAT, $result["valid_from"]) : "No Restrictions");?>
                                                    </span>
                                                </td>
                                                <td class="date-small">
                                                    <span class="content-date">
                                                        <?php echo (((int) $result["valid_until"]) ? date(DEFAULT_DATE_FORMAT, $result["valid_until"]) : "No Restrictions");?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                    } else { ?>
                                        <tr>
                                            <td colspan="5">
                                                <div class="display-generic" style="white-space: normal">
                                                    There have been no LTI Providers added to this event. To <strong>add a new LTI Provider</strong> simply click the Add LTI Provider button.
                                                </div>
                                            </td>
                                        </tr>
                                    <?php
                                    } ?>
                                    </tbody>
                                </table>
                            </form>
                        </div>
                    </div>

                    <script type="text/javascript">
                    $$('select.ed_select_off').each(function(el) {
                        $(el).disabled = true;
                        $(el).fade({ duration: 0.3, to: 0.25 });
                    });
                    </script>
                    <?php
                    /**
                     * Sidebar item that will provide the links to the different sections within this page.
                     */
                    $sidebar_html  = "<ul class=\"menu\">\n";
                    $sidebar_html .= "	<li class=\"link\"><a href=\"#event-details-section\" onclick=\"$('event-details-section').scrollTo(); return false;\" title=\"Event Details\">Event Details</a></li>\n";
                    $sidebar_html .= "	<li class=\"link\"><a href=\"#event-audience-section\" onclick=\"$('event-audience-section').scrollTo(); return false;\" title=\"Event Audience\">Event Audience</a></li>\n";
                    $sidebar_html .= "	<li class=\"link\"><a href=\"#event-objectives-section\" onclick=\"$('event-objectives-section').scrollTo(); return false;\" title=\"Event Objectives\">Event Objectives</a></li>\n";
                    $sidebar_html .= "	<li class=\"link\"><a href=\"#event-resources-section\" onclick=\"$('event-resources-section').scrollTo(); return false;\" title=\"Event Resources\">Event Resources</a></li>\n";
                    $sidebar_html .= "</ul>\n";

                    new_sidebar_item("Page Anchors", $sidebar_html, "page-anchors", "open", "1.9");
                } else {
                    display_status_messages();

                    echo "<h1>Select which events to update:</h1>";

                    $query = "	SELECT a.*, b.`organisation_id`
                                FROM `events` AS a
                                LEFT JOIN `courses` AS b
                                ON b.`course_id` = a.`course_id`
                                WHERE `event_id` = ".$db->qstr($EVENT_ID);
                    $event_info	= $db->GetRow($query);

                   $query = "SELECT * FROM `events`
                                WHERE `recurring_id` = ".$db->qstr($event_info["recurring_id"])."
                                AND `event_id` != ".$db->qstr($EVENT_ID)."
                                ORDER BY `event_start` ASC";
                    $recurring_events = $db->getAll($query);

                    if ($event_info && $recurring_events) {
                        $ONLOAD[] = "jQuery('.inpage-datepicker').datepicker({
                                        dateFormat: 'yy-mm-dd'
                                    })";
                        $ONLOAD[] = "jQuery('.timepicker').timepicker({
                                        showPeriodLabels: false
                                    })";
                        $ONLOAD[] = "jQuery('.inpage-add-on').on('click', function() {
                                        if (jQuery(this).siblings('input').is(':enabled')) {
                                            jQuery(this).siblings('input').focus();
                                        }
                                    })";
                        $ONLOAD[] = "jQuery('.restricted').popover({
                                        trigger: 'manual',
                                        placement: 'right',
                                        title: 'Error',
                                        content: 'This day is restricted. Please ensure this is the correct date before continuing.',
                                        template: '<div class=\"popover alert alert-error\"><div class=\"arrow\"></div><div class=\"popover-inner\"><div class=\"popover-content\"><p></p></div></div></div>'
                                    }).click(function(e) {
                                        jQuery(this).popover('hide');
                                        jQuery(this).show();
                                        e.stopPropagation();
                                    })";
                        $ONLOAD[] = "jQuery('.restricted').popover('show')";
                        ?>
                        <script type="text/javascript">
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
                        <form name="editEventForm" id="editEventForm" action="<?php echo ENTRADA_URL; ?>/admin/events?<?php echo replace_query(); ?>" method="post" class="form-horizontal">
                        <input type="hidden" name="type" value="cascade" />
                            <div id="recurring-events-list">
                                <?php
                                $restricted_days = Models_RestrictedDays::fetchAll($ENTRADA_USER->getActiveOrganisation());
                                foreach ($recurring_events as $key => $recurring_event) {
                                    $restricted = false;
                                    if ($restricted_days && @count($restricted_days)) {
                                        $date_string = date("Y-m-d", $recurring_event["event_start"]);
                                        foreach ($restricted_days as $restricted_day) {
                                            $restricted_string = date("Y-m-d", $restricted_day->getCalculatedDate(date("Y", $recurring_event["event_start"]), date("n", $recurring_event["event_start"]), $recurring_event["event_start"]));
                                            if ($restricted_string == $date_string) {
                                                $restricted = true;
                                                break;
                                            }
                                        }
                                    }
                                    ?>
                                    <div id="recurring-event-<?php echo ($recurring_event["event_id"]); ?>" class="row-fluid pad-above<?php echo ($key % 2 == 0 ? " odd" : "").($restricted ? " restricted" : ""); ?>">
                                        <label class="span3 content-small pad-left" for="recurring_event_<?php echo $recurring_event["event_id"]; ?>">
                                            <input type="checkbox" name="recurring_event_ids[]" id="recurring_event_<?php echo $recurring_event["event_id"]; ?>" value="<?php echo $recurring_event["event_id"]."\"".(!isset($PROCESSED["recurring_event_ids"]) || in_array($recurring_event["event_id"], $PROCESSED["recurring_event_ids"]) ? " checked=\"checked" : ""); ?>" />
                                            Event <?php echo ($key + 1); ?>:
                                        </label>
                                        <span class="span8">
                                            <div class="row-fluid">
                                                <label for="recurring_event_title_<?php echo $recurring_event["event_id"]; ?>" class="span2 form-required">Title:</label>
                                                <span class="span7">
                                                    <input type="text" id="recurring_event_title_<?php echo $recurring_event["event_id"]; ?>" name="recurring_event_title_<?php echo $recurring_event["event_id"]; ?>" value="<?php echo html_encode($recurring_event["event_title"]); ?>" maxlength="255" style="width: 95%; font-size: 150%; padding: 3px" />
                                                </span>
                                            </div>
                                            <div class="row-fluid">
                                                <label class="span2" for="recurring_event_start_<?php echo $recurring_event["event_id"]; ?>">Event Start:</label>
                                                <span class="span7">
                                                    <div class="input-append">
                                                        <input type="text" class="input-small inpage-datepicker" value="<?php echo date("Y-m-d", $recurring_event["event_start"]); ?>" name="recurring_event_start_<?php echo $recurring_event["event_id"]; ?>" onchange="checkEventDate('<?php echo $recurring_event["event_id"]; ?>')" id="recurring_event_start_<?php echo $recurring_event["event_id"]; ?>" />
                                                        <span class="add-on pointer inpage-add-on"><i class="icon-calendar"></i></span>
                                                    </div>
                                                    &nbsp;
                                                    <div class="input-append">
                                                        <input type="text" class="input-mini timepicker" value="<?php echo date("H:i", $recurring_event["event_start"]); ?>" name="recurring_event_start_time_<?php echo $recurring_event["event_id"]; ?>" id="recurring_event_start_time_<?php echo $recurring_event["event_id"]; ?>" />
                                                        <span class="add-on pointer inpage-add-on"><i class="icon-time"></i></span>
                                                    </div>
                                                </span>
                                            </div>
                                        </span>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php

                            echo "<h1>Select which fields to update:</h1>";
                            ?>
                            <div class="row-fluid space-above">
                                <label for="field_event_description" class="span3 content-small pad-left">
                                    <input type="checkbox" name="fields[]" id="field_event_description" value="event_description"<?php echo (!isset($PROCESSED["fields"]) || in_array("event_description", $PROCESSED["fields"]) ? " checked=\"checked\"" : ""); ?> />
                                    Event Description
                                </label>
                                <span class="span9">
                                    <span>
                                        <?php
                                        echo $event_info["event_description"];
                                        ?>
                                    </span>
                                </span>
                            </div>
                            <div class="row-fluid space-above">
                                <label for="field_event_message" class="span3 content-small pad-left">
                                    <input type="checkbox" name="fields[]" id="field_event_message" value="event_message"<?php echo (!isset($PROCESSED["fields"]) || in_array("event_message", $PROCESSED["fields"]) ? " checked=\"checked\"" : ""); ?> />
                                    Required Preparation
                                </label>
                                <span class="span9">
                                    <span>
                                        <?php
                                        echo $event_info["event_message"];
                                        ?>
                                    </span>
                                </span>
                            </div>
                            <div class="row-fluid space-above">
                                <label for="field_event_objectives" class="span3 content-small pad-left">
                                    <input type="checkbox" name="fields[]" id="field_event_objectives" value="event_objectives"<?php echo (!isset($PROCESSED["fields"]) || in_array("event_objectives", $PROCESSED["fields"]) ? " checked=\"checked\"" : ""); ?> />
                                    Free-Text Objectives
                                </label>
                                <span class="span9">
                                    <span>
                                        <?php
                                        echo $event_info["event_objectives"];
                                        ?>
                                    </span>
                                </span>
                            </div>
                            <div class="row-fluid space-above">
                                <label for="field_mapped_objectives" class="span3 content-small pad-left">
                                    <input type="checkbox" name="fields[]" id="field_mapped_objectives" value="mapped_objectives"<?php echo (!isset($PROCESSED["fields"]) || in_array("mapped_objectives", $PROCESSED["fields"]) ? " checked=\"checked\"" : ""); ?> />
                                    Mapped Objectives
                                </label>
                                <span class="span9">
                                    <span>
                                        <?php
                                        $query = "	SELECT a.*, COALESCE(b.`objective_details`,a.`objective_description`) AS `objective_description`, COALESCE(b.`objective_type`,c.`objective_type`) AS `objective_type`,
                                                    b.`importance`,c.`objective_details`, COALESCE(c.`eobjective_id`,0) AS `mapped`,
                                                    COALESCE(b.`cobjective_id`,0) AS `mapped_to_course`
                                                    FROM `global_lu_objectives` a
                                                    LEFT JOIN `course_objectives` b
                                                    ON a.`objective_id` = b.`objective_id`
                                                    AND b.`course_id` = ".$db->qstr($COURSE_ID)."
                                                    LEFT JOIN `event_objectives` c
                                                    ON c.`objective_id` = a.`objective_id`
                                                    AND c.`event_id` = ".$db->qstr($EVENT_ID)."
                                                    WHERE a.`objective_active` = '1'
                                                    AND (c.`event_id` = ".$db->qstr($EVENT_ID)." OR b.`course_id` = ".$db->qstr($COURSE_ID).")
                                                    GROUP BY a.`objective_id`
                                                    ORDER BY a.`objective_id` ASC";
                                        $mapped_objectives = $db->GetAll($query);
                                        $primary = false;
                                        $secondary = false;
                                        $tertiary = false;
                                        $hierarchical_objectives = array();
                                        $flat_objectives = array();
                                        $show_flat_objectives = false;
                                        $explicit_event_objectives = false;//array();
                                        $mapped_event_objectives = array();
                                        if ($mapped_objectives) {
                                            foreach ($mapped_objectives as $objective) {
                                                //if its mapped to the event, but not the course, then it belongs in the event objective list
                                                //echo $objective["objective_name"].' is '.$objective["mapped"].' and '.$objective["mapped_to_course"]."<br/>";
                                                if ($objective["mapped"] && !$objective["mapped_to_course"]) {
                                                    if (!($importance = event_objective_parent_mapped_course($objective["objective_id"],$EVENT_ID))) {
                                                        $explicit_event_objectives[] = $objective;
                                                    } else {
                                                        if ((!isset($objective["importance"]) || !$objective["importance"]) && $importance) {
                                                            $objective["importance"] = $importance;
                                                        }
                                                        if ($objective["objective_type"] == "course") {
                                                            //$objective_id = $objective["objective_id"];
                                                            $hierarchical_objectives[] = $objective;
                                                        } else {
                                                            $flat_objectives[] = $objective;
                                                            if ($objective["mapped"]) {
                                                                $show_flat_objectives = true;
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    if ($objective["objective_type"] == "course") {
                                                        //$objective_id = $objective["objective_id"];
                                                        $hierarchical_objectives[] = $objective;
                                                    } else {
                                                        $flat_objectives[] = $objective;
                                                        if ($objective["mapped"]) {
                                                            $show_flat_objectives = true;
                                                        }
                                                    }
                                                }

                                                if ($objective["mapped"]) {
                                                    $mapped_event_objectives[] = $objective;
                                                }
                                            }
                                        }
                                        if ($hierarchical_objectives) {
                                            // function loads bottom leaves and displays them
                                            event_objectives_display_leafs($hierarchical_objectives, $COURSE_ID, $EVENT_ID, false);
                                        } else {
                                            echo "<h2>Event Objectives</h2>";
                                            echo display_notice("No <strong>Primary</strong>, <strong>Secondary, or <strong>Tertiary</strong> Objectives have been mapped to this event.");
                                        }
                                        if ($flat_objectives) {
                                            ?>
                                            <div id="clinical-list-wrapper">
                                                <a name="clinical-objective-list"></a>
                                                <h2 id="flat-toggle"  title="Clinical Objective List" class="list-heading"><?php echo ($objective_name["cp"]["global_lu_objectives_name"] ? $objective_name["cp"]["global_lu_objectives_name"] : "Other Objectives"); ?></h2>
                                                <div id="clinical-objective-list">
                                                    <?php
                                                    if ($show_flat_objectives) {
                                                        ?>
                                                        <ul class="objective-list mapped-list" id="mapped_flat_objectives" data-importance="flat">
                                                            <?php
                                                            foreach ($flat_objectives as $objective) {
                                                                if ($objective["mapped"]) {
                                                                    $title = ($objective["objective_code"]?$objective["objective_code"].': '.$objective["objective_name"]:$objective["objective_name"]);
                                                                    ?>
                                                                    <li class = "mapped-objective"
                                                                        id = "mapped_objective_<?php echo $objective["objective_id"]; ?>"
                                                                        data-id = "<?php echo $objective["objective_id"]; ?>"
                                                                        data-title="<?php echo $title;?>"
                                                                        data-description="<?php echo htmlentities($objective["objective_description"]);?>">
                                                                        <strong><?php echo $title; ?></strong>
                                                                        <div class="objective-description">
                                                                            <?php
                                                                            $set = fetch_objective_set_for_objective_id($objective["objective_id"]);
                                                                            if ($set) {
                                                                                echo "From the Objective Set: <strong>".$set["objective_name"]."</strong><br/>";
                                                                            }

                                                                            echo $objective["objective_description"];
                                                                            ?>
                                                                        </div>
                                                                    </li>
                                                                    <?php
                                                                }
                                                            }
                                                            ?>
                                                        </ul>
                                                        <?php
                                                    } else {
                                                        echo display_notice("No <strong>".($objective_name["cp"]["global_lu_objectives_name"] ? $objective_name["cp"]["global_lu_objectives_name"] : "Other Objectives")."</strong> have been mapped to this event.");
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                        ?>

                                        <div id="event-list-wrapper">
                                            <a name="event-objective-list"></a>
                                            <h2 id="event-toggle"  title="Event Objective List" class="list-heading">Event Specific Objectives</h2>
                                            <div id="event-objective-list">
                                                <?php
                                                if ($explicit_event_objectives) {
                                                    ?>
                                                    <ul class="objective-list mapped-list" id="mapped_event_objectives" data-importance="event">
                                                        <?php
                                                        foreach ($explicit_event_objectives as $objective) {
                                                            $title = ($objective["objective_code"] ? $objective["objective_code"] . ': ' . $objective["objective_name"] : $objective["objective_name"]);
                                                            ?>
                                                            <li class = "mapped-objective"
                                                                id = "mapped_objective_<?php echo $objective["objective_id"]; ?>"
                                                                data-id = "<?php echo $objective["objective_id"]; ?>"
                                                                data-title="<?php echo $title;?>"
                                                                data-description="<?php echo htmlentities($objective["objective_description"]);?>"
                                                                data-mapped="<?php echo $objective["mapped_to_course"]?1:0;?>">
                                                                <strong><?php echo $title; ?></strong>
                                                                <div class="objective-description">
                                                                    <?php
                                                                    $set = fetch_objective_set_for_objective_id($objective["objective_id"]);
                                                                    if ($set) {
                                                                        echo "From the Objective Set: <strong>".$set["objective_name"]."</strong><br/>";
                                                                    }

                                                                    echo $objective["objective_description"];
                                                                    ?>
                                                                </div>
                                                            </li>
                                                            <?php
                                                        }
                                                        ?>
                                                    </ul>
                                                    <?php
                                                } else {
                                                    echo display_notice("No <strong>Event Specific Objectives</strong> have been mapped to this event.");
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </span>
                                </span>
                            </div>
                            <div class="row-fluid space-above">
                                <label for="field_event_topics" class="span3 content-small pad-left">
                                    <input type="checkbox" name="fields[]" id="field_event_topics" value="event_topics"<?php echo (!isset($PROCESSED["fields"]) || in_array("event_topics", $PROCESSED["fields"]) ? " checked=\"checked\"" : ""); ?> />
                                    Event Topics
                                </label>
                                <span class="span9">
                                    <span>
                                        <?php
                                        $query = "	SELECT a.`topic_id`, a.`topic_name`, e.`topic_coverage`,e.`topic_time`
                                                    FROM `events_lu_topics` AS a
                                                    LEFT JOIN `topic_organisation` AS b
                                                    ON a.`topic_id` = b.`topic_id`
                                                    LEFT JOIN `courses` AS c
                                                    ON b.`organisation_id` = c.`organisation_id`
                                                    LEFT JOIN `events` AS d
                                                    ON c.`course_id` = d.`course_id`
                                                    JOIN `event_topics` AS e
                                                    ON d.`event_id` = e.`event_id`
                                                    AND a.`topic_id` = e.`topic_id`
                                                    WHERE d.`event_id` = ".$db->qstr($EVENT_ID)."
                                                    ORDER BY a.`topic_name`";
                                        $topic_results = $db->GetAll($query);
                                        if ($topic_results) {
                                            ?>
                                            <table style="width: 100%" cellspacing="0">
                                                <colgroup>
                                                    <col style="width: 80%" />
                                                    <col style="width: 10%" />
                                                    <col style="width: 10%" />
                                                </colgroup>
                                                <tr>
                                                    <td colspan="3">
                                                        <h2>Event Topics</h2>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><span style="font-weight: bold; color: #003366;">Hot Topic</span></td>
                                                    <td><span style="font-weight: bold; color: #003366;">Major</span></td>
                                                    <td><span style="font-weight: bold; color: #003366;">Minor</span></td>
                                                </tr>
                                                <?php
                                                    foreach ($topic_results as $topic_result) {
                                                        echo "<tr>\n";
                                                        echo "	<td>".html_encode($topic_result["topic_name"])."</td>\n";
                                                        echo "	<td>".(($topic_result["topic_coverage"] == "major") ? "<img src=\"".ENTRADA_URL."/images/question-correct.gif"."\" />" : "" )."</td>\n";
                                                        echo "	<td>".(($topic_result["topic_coverage"] == "minor") ? "<img src=\"".ENTRADA_URL."/images/question-correct.gif"."\" />": "" )."</td>\n";
                                                        echo "</tr>\n";
                                                    }
                                                    echo "<tr><td colspan=\"2\">&nbsp;</td></tr>";
                                                ?>
                                            </table>
                                            <?php
                                        } else {
                                            echo display_notice("No Event Topics found associated with this event.");
                                        }
                                        ?>
                                    </span>
                                </span>
                            </div>
                            <div class="control-group space-above">
                                <a class="btn" href="<?php echo ENTRADA_RELATIVE; ?>/admin/events?section=content&id=<?php echo $EVENT_ID; ?>">Cancel</a>
                                <div class="pull-right">
                                    <input type="submit" class="btn btn-primary" value="Save" />
                                </div>
                            </div>
                        </form>
                        <?php
                    }
                }
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "In order to edit a event you must provide a valid event identifier. The provided ID does not exist in this system.";

			echo display_error();

			application_log("notice", "Failed to provide a valid event identifer when attempting to edit a event.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to edit a event you must provide the events identifier.";

		echo display_error();

		application_log("notice", "Failed to provide event identifer when attempting to edit a event.");
	}
}