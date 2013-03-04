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
} elseif (!$ENTRADA_ACL->amIAllowed('eventcontent', 'update', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

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
		$query		= "	SELECT a.*, b.`organisation_id`
						FROM `events` AS a
						LEFT JOIN `courses` AS b
						ON b.`course_id` = a.`course_id`
						WHERE a.`event_id` = ".$db->qstr($EVENT_ID);
		$event_info	= $db->GetRow($query);
		if ($event_info) {
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

				$LASTUPDATED = $event_info["updated_date"];

				/**
				 * Fetch event content history
                 * @todo This seems very strange to me.
				 */
				$query = "	SELECT a.`history_message` AS message, a.`history_timestamp` AS timestamp, CONCAT_WS(' ', b.`firstname`, b.`lastname`) AS `fullname`
							FROM `event_history` AS a
							LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
							ON a.`proxy_id` = b.`id`
							WHERE a.`event_id`  = ".$db->qstr($EVENT_ID)."
							ORDER BY `history_timestamp` DESC, `history_message` ASC";
				$history = $db->GetAll($query);

				if (!$history) {
					$query = "	SELECT CONCAT_WS(' ', `firstname`, `lastname`) AS `fullname`,
								$LASTUPDATED AS timestamp, 'created this learning event.' AS message
								FROM `".AUTH_DATABASE."`.`user_data`
								WHERE `id`  = ".$db->qstr($event_info["updated_by"]);
					$history = $db->GetAll($query);
					if(count($_POST)) {		// An update so add the create record for this event
						history_log($EVENT_ID, $history[0]["message"], $event_info["updated_by"], $LASTUPDATED);
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

				if (isset($_POST["clinical_presentations_submit"]) && $_POST["clinical_presentations_submit"]) {
					if (((isset($_POST["clinical_presentations"])) && (is_array($_POST["clinical_presentations"])) && (count($_POST["clinical_presentations"])))) {
						foreach ($_POST["clinical_presentations"] as $objective_id) {
							if ($objective_id = clean_input($objective_id, array("trim", "int"))) {
								$query	= "SELECT a.`objective_id`
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
					history_log($EVENT_ID, "updated clinical presentations.");
				} else {
					$query = "	SELECT a.`objective_id`
								FROM `event_objectives` AS a
								JOIN `course_objectives` AS b
								ON b.`course_id` = ".$event_info["course_id"]."
								AND a.`objective_id` = b.`objective_id`
								WHERE a.`objective_type` = 'event'
								AND b.`objective_type` = 'event'
								AND a.`event_id` = ".$db->qstr($EVENT_ID);
					$results = $db->GetAll($query);
					if ($results) {
						foreach ($results as $result) {
							$clinical_presentations[$result["objective_id"]] = $clinical_presentations_list[$result["objective_id"]];
						}
					}
				}

				/**
				 * Fetch the Curriculum Objective details.
				 */
				list($curriculum_objectives_list,$top_level_id) = courses_fetch_objectives($event_info["organisation_id"],array($event_info["course_id"]),-1, 1, false, false, $EVENT_ID, true);

				$curriculum_objectives = array();

				if (isset($_POST["checked_objectives"]) && ($checked_objectives = $_POST["checked_objectives"]) && (is_array($checked_objectives))) {
					foreach ($checked_objectives as $objective_id => $status) {
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
								ORDER BY a.`eventtype_order`
				";

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
								if (!($duration >= 30)) {
									$ERROR++;
									$ERRORSTR[] = "Event type <strong>durations</strong> may not be less than 30 minutes.";
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
								 * Teacher's Message
								 */
								if (event_text_change($EVENT_ID,"event_message")) {
									if (strlen($history_texts)>2) {
										$history_texts .= ":";
									}
									$history_texts .= "teacher's message";
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
								if ($db->AutoExecute("events", array("event_objectives" => $event_objectives, "event_description" => $event_description, "event_message" => $event_message, "event_finish" => $event_finish, "event_duration" => $event_duration, "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID()), "UPDATE", "`event_id` = ".$db->qstr($EVENT_ID))) {
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
											if (!$db->AutoExecute("event_objectives", array("event_id" => $EVENT_ID, "objective_id" => $objective_id, "objective_type" => "event", "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID()), "INSERT")) {
												$ERROR++;
												$ERRORSTR[] = "There was an error when trying to insert a &quot;clinical presentation&quot; into the system. System administrators have been informed of this error; please try again later.";

												application_log("error", "Unable to insert a new clinical presentation to the database when adding a new event. Database said: ".$db->ErrorMsg());
											}
										}
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
												$query	= "SELECT a.* FROM `global_lu_objectives` AS a
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
													if ((isset($value["major_topic"])) && ($value["major_topic"] == "major")) {
														if (!$db->AutoExecute("event_topics", array("event_id" => $EVENT_ID, "topic_id" => $topic_id, "topic_coverage" => "major", "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID()), "INSERT")) {
															$ERROR++;
															$ERRORSTR[] = "There was an error when trying to insert an Event Topic response into the system. System administrators have been informed of this error; please try again later.";

															application_log("error", "Unable to insert a new event_topic entry into the database while modifying event contents. Database said: ".$db->ErrorMsg());
														}
													} elseif ((isset($value["minor_topic"])) && ($value["minor_topic"] == "minor")) {
														if (!$db->AutoExecute("event_topics", array("event_id" => $EVENT_ID, "topic_id" => $topic_id, "topic_coverage" => "minor", "topic_time" => ((isset($value["minor_desc"])) ? (int) trim($value["minor_desc"]) : ""), "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID()), "INSERT")) {
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
						default :
							continue;
						break;
					}
				}
				?>
				<style type="text/css">
				textarea.expandable {
					width: 90%;
				}
				</style>
				<?php
				$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/elementresizer.js\"></script>";
				$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/wizard.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
				$HEAD[] = "<link href=\"".ENTRADA_URL."/css/wizard.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />";
				?>
				<iframe id="upload-frame" name="upload-frame" onload="frameLoad()" style="display: none;"></iframe>
				<a id="false-link" href="#placeholder"></a>
				<div id="placeholder" style="display: none"></div>
				<script type="text/javascript">
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

				function updateEdChecks(obj) {
					var element = obj.id;

					if (obj.value == 'minor') {
						var select_box = element + '_desc';

						if ($(element).checked == true) {
							$(select_box).disabled = false;
							$(select_box).fade({ duration: 0.3, to: 1.0 });

							$(element.replace(/minor/i, 'major')).checked = false;
						} else {
							$(select_box).value = '0';
							$(select_box).disabled = true;
							$(select_box).fade({ duration: 0.3, to: 0.25 });
						}
					} else {
						var select_box = element.replace(/major/i, 'minor_desc');

						$(select_box).value = '0';
						$(select_box).disabled = true;
						$(select_box).fade({ duration: 0.3, to: 0.25 });

						$(element.replace(/major/i, 'minor')).checked = false;
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
				<form action="<?php echo ENTRADA_URL; ?>/admin/events?<?php echo replace_query(); ?>" method="post"<?php echo (((is_array($clinical_presentations_list)) && (!empty($clinical_presentations_list))) ? " onsubmit=\"selIt()\"" : ""); ?>>
				<input type="hidden" name="type" value="content" />

				<a name="event-details-section"></a>
				<h2 title="Event Details Section">Event Details</h2>
				<div id="event-details-section">
					<table style="width: 100%" cellspacing="0" cellpadding="0" border="0" summary="Event Details">
						<colgroup>
							<col style="width: 20%" />
							<col style="width: 80%" />
						</colgroup>
						<tfoot>
							<tr>
								<td colspan="2" style="text-align: right; padding-top: 5px"><input type="submit" value="Save" /></td>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td>Event Date &amp; Time</td>
								<td><?php echo date(DEFAULT_DATE_FORMAT, $event_info["event_start"]); ?></td>
							</tr>
							<tr>
								<td>Event Duration</td>
								<td><?php echo (($event_info["event_duration"]) ? $event_info["event_duration"]." minutes" : "To Be Announced"); ?></td>
							</tr>
							<tr>
								<td>Event Location</td>
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
								<td style="vertical-align: top">Associated Faculty</td>
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
								<td style="vertical-align: top"><label for="eventtype_ids" class="form-required">Event Types</label></td>
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
                                                    Duration: <input class=\"duration_segment\" name=\"duration_segment[]\" onchange=\"cleanupList();\" value=\"".(int) $eventtype["duration"]."\"> minutes
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
								<td style="vertical-align: top">Event Description</td>
								<td>
									<textarea id="event_description" name="event_description" style="width: 100%; height: 150px" cols="70" rows="10"><?php echo html_encode(trim(strip_selected_tags($event_info["event_description"], array("font")))); ?></textarea>
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<a name="event-audience-section"></a>
				<h2 title="Event Audience Section">Event Audience</h2>
				<div id="event-audience-section">
					<table style="width: 100%" cellspacing="0" cellpadding="0" border="0" summary="Event Information">
						<colgroup>
							<col style="width: 20%" />
							<col style="width: 80%" />
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
							<?php
							switch ($event_audience_type) {
								case "grad_year" :
									echo "<tr>\n";
									echo "	<td>Associated Class</td>\n";
									echo "	<td>Class of ".$associated_grad_year."</td>\n";
									echo "</tr>\n";
								break;
								case "group_id" :
									echo "<tr>\n";
									echo "	<td style=\"vertical-align: top\">Associated Group</td>\n";
									echo "	<td style=\"vertical-align: top\">\n";
									echo "		<ul class=\"general-list\">";
										foreach ($associated_group_ids as $group_id) {
											echo "	<li>Group ID: ".$group_id."</li>\n";
										}
									echo "		</ul>\n";
									echo "	</td>\n";
									echo "</tr>\n";
								break;
								case "proxy_id" :
									echo "<tr>\n";
									echo "	<td style=\"vertical-align: top\">Associated Learner".((count($associated_proxy_ids) != 1) ? "s" : "")."</td>\n";
									echo "	<td style=\"vertical-align: top\">\n";
									echo "		<ul class=\"general-list\">";
										foreach ($associated_proxy_ids as $proxy_id) {
											/**
											 * @todo Once the group support has been added this will need to be changed.
											 */
											echo "	<li><a href=\"".ENTRADA_URL."/people?profile=".get_account_data("username", $proxy_id)."\" target=\"_blank\">".get_account_data("firstlast", $proxy_id)."</a></li>\n";
										}
									echo "		</ul>\n";
									echo "	</td>\n";
									echo "</tr>\n";
								break;
								case "organisation_id":
									echo "<tr>\n";
									echo "	<td>Associated Organisation</td>\n";
									echo "	<td>".$associated_organisation."</td>\n";
									echo "</tr>\n";
								break;
							}
							?>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<tr>
								<td style="vertical-align: top">
									Teacher's Message
									<div class="content-small" style="margin-top: 10px">
										<strong>Note:</strong> You can use this to provide your learners with instructions or information they need for this class.
									</div>
								</td>
								<td>
									<textarea id="event_message" name="event_message" style="width: 100%; height: 150px" cols="70" rows="10"><?php echo html_encode(trim(strip_selected_tags($event_info["event_message"], array("font")))); ?></textarea>
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<a name="event-objectives-section"></a>
				<h2 title="Event Objectives Section">Event Objectives</h2>
				<div id="event-objectives-section">
					<table style="width: 100%" cellspacing="0" cellpadding="0" border="0" summary="Event Objectives Information">
						<colgroup>
							<col style="width: 20%" />
							<col style="width: 80%" />
						</colgroup>
						<tfoot>
							<tr>
								<td colspan="2" style="text-align: right; padding-top: 5px"><input type="submit" value="Save" /></td>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td style="vertical-align: top"><label for="event_objectives" class="form-nrequired">Free-Text Objectives</label></td>
								<td>
									<textarea id="event_objectives" name="event_objectives" style="width: 100%; height: 150px" cols="70" rows="10"><?php echo html_encode(trim(strip_selected_tags($event_info["event_objectives"], array("font")))); ?></textarea>
								</td>
							</tr>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<?php
							if ((is_array($clinical_presentations_list)) && (!empty($clinical_presentations_list))) {
								?>
								<tr>
									<td style="vertical-align: top">
										Clinical Presentations
										<div class="content-small" style="margin-top: 5px">
											<strong>Note:</strong> For more detailed information please refer to the <a href="http://www.mcc.ca/Objectives_online/objectives.pl?lang=english&loc=contents" target="_blank" style="font-size: 11px">MCC Presentations for the Qualifying Examination</a>.
										</div>
									</td>
									<td>
										<select class="multi-picklist" id="PickList" name="clinical_presentations[]" multiple="multiple" size="5" style="width: 100%; margin-bottom: 5px">
										<?php
										if ((is_array($clinical_presentations)) && (count($clinical_presentations))) {
											foreach ($clinical_presentations as $objective_id => $presentation_name) {
												echo "<option value=\"".(int) $objective_id."\">".html_encode($presentation_name)."</option>\n";
											}
										}
										?>
										</select>
										<input type="hidden" value="1" name="clinical_presentations_submit" />
										<div style="float: left; display: inline">
											<input type="button" id="clinical_presentations_list_state_btn" class="button" value="Show List" onclick="toggle_list('clinical_presentations_list')" />
										</div>
										<div style="float: right; display: inline">
											<input type="button" id="clinical_presentations_list_remove_btn" class="button-remove" onclick="delIt()" value="Remove" />
											<input type="button" id="clinical_presentations_list_add_btn" class="button-add" onclick="addIt()" style="display: none" value="Add" />
										</div>
										<div id="clinical_presentations_list" style="clear: both; padding-top: 3px; display: none">
											<h2>Clinical Presentations List</h2>
											<select class="multi-picklist" id="SelectList" name="other_event_objectives_list" multiple="multiple" size="15" style="width: 100%">
											<?php
											foreach ($clinical_presentations_list as $objective_id => $presentation_name) {
												if (!array_key_exists($objective_id, $clinical_presentations)) {
													echo "<option value=\"".(int) $objective_id."\">".html_encode($presentation_name)."</option>\n";
												}
											}
											?>
											</select>
										</div>
										<script type="text/javascript">
										if ($('PickList')) {
											$('PickList').observe('keypress', function(event) {
												if (event.keyCode == Event.KEY_DELETE) {
													delIt();
												}
											});
										}

										if ($('SelectList')) {
											$('SelectList').observe('keypress', function(event) {
												if (event.keyCode == Event.KEY_RETURN) {
													addIt();
												}
											});
										}
										</script>
									</td>
								</tr>
								<tr>
									<td colspan="2">&nbsp;</td>
								</tr>
								<?php
							}

							if ((is_array($curriculum_objectives_list["used_ids"])) && (count($curriculum_objectives_list["used_ids"]))) {
								echo "<tr>\n";
								echo "	<td style=\"vertical-align: top;\">\n";
								echo "		<span class=\"form-nrequired\">Curriculum Objectives</span>\n";
								echo "		<div class=\"content-small\" style=\"margin-top: 5px\">\n";
								echo "			<strong>Note:</strong> Please check any curriculum objectives that are covered during this learning event.\n";
								echo "		</div>\n";
								echo "	</td>\n";
								echo "	<td style=\"vertical-align: top\">\n";
								echo "		<div id=\"course-objectives-section\">\n";
								echo "			<strong>The learner will be able to:</strong>\n";
								echo			event_objectives_in_list($curriculum_objectives_list, $top_level_id,$top_level_id, true, false, 1, false);
								echo "		</div>\n";
								echo "	</td>\n";
								echo "</tr>\n";
							}
							?>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
						</tbody>
					</table>
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
                            WHERE d.`event_id` = ".$db->qstr($EVENT_ID);
                $topic_results = $db->GetAll($query);
                if ($topic_results) {
                    ?>
                    <a name="event-topics-section"></a>
                    <h2 title="Event Topics Section">Event Topics</h2>
                    <div id="event-topics-section">
                        <div class="content-small" style="padding-bottom: 10px">Please select the topics that will be covered in your learning event and indicate if the amount of time that will be devoted.</div>
                        <table style="width: 100%" cellspacing="0" summary="List of ED10">
                            <colgroup>
                                <col style="width: 55%" />
                                <col style="width: 15%" />
                                <col style="width: 15%" />
                                <col style="width: 15%" />
                            </colgroup>
                            <tfoot>
                                <tr>
                                    <td colspan="4" style="text-align: right; padding-top: 5px"><input type="submit" value="Save" /></td>
                                </tr>
                            </tfoot>
                            <tr>
                                <td><span style="font-weight: bold; color: #003366;">Hot Topic</span></td>
                                <td><span style="font-weight: bold; color: #003366;">Major</span></td>
                                <td><span style="font-weight: bold; color: #003366;">Minor</span></td>
                                <td><span style="font-weight: bold; color: #003366;">Time</span></td>
                            </tr>
                            <?php
                            foreach ($topic_results as $topic_result) {
                                echo "<tr>\n";
                                echo "	<td>".html_encode($topic_result["topic_name"])."</td>\n";
                                echo "	<td>";
                                echo "		<input type=\"checkbox\" id=\"topic_".$topic_result["topic_id"]."_major\" name=\"event_topic[".$topic_result["topic_id"]."][major_topic]\" value=\"major\" onclick=\"updateEdChecks(this)\"".(($topic_result["topic_coverage"] == "major") ? " checked=\"checked\"" : "")." />";
                                echo "	</td>\n";
                                echo "	<td>";
                                echo "		<input type=\"checkbox\" id=\"topic_".$topic_result["topic_id"]."_minor\" name=\"event_topic[".$topic_result["topic_id"]."][minor_topic]\" value=\"minor\" onclick=\"updateEdChecks(this)\"".(($topic_result["topic_coverage"] == "minor") ? " checked=\"checked\"" : "")." />";
                                echo "	</td>\n";
                                echo "	<td>\n";
                                echo "		<select id=\"topic_".$topic_result["topic_id"]."_minor_desc\" name=\"event_topic[".$topic_result["topic_id"]."][minor_desc]\" class=\"ed_select_".(($topic_result["topic_coverage"] == "minor") ? "on" : "off")."\">" ;
                                echo "			<option value=\"0\"".((!(int) $topic_result["topic_time"]) ? " selected=\"selected\"" : "").">0</option>";
                                echo "			<option value=\"5\"".(($topic_result["topic_time"] == "5") ? " selected=\"selected\"" : "").">5</option>";
                                echo "			<option value=\"10\"".(($topic_result["topic_time"] == "10") ? " selected=\"selected\"" : "").">10</option>";
                                echo "			<option value=\"15\"".(($topic_result["topic_time"] == "15") ? " selected=\"selected\"" : "").">15</option>";
                                echo "			<option value=\"20\"".(($topic_result["topic_time"] == "20") ? " selected=\"selected\"" : "").">20</option>";
                                echo "			<option value=\"25\"".(($topic_result["topic_time"] == "25") ? " selected=\"selected\"" : "").">25</option>";
                                echo "			<option value=\"30\"".(($topic_result["topic_time"] == "30") ? " selected=\"selected\"" : "").">30</option>";
                                echo "			<option value=\"35\"".(($topic_result["topic_time"] == "35") ? " selected=\"selected\"" : "").">35</option>";
                                echo "			<option value=\"40\"".(($topic_result["topic_time"] == "40") ? " selected=\"selected\"" : "").">40</option>";
                                echo "			<option value=\"45\"".(($topic_result["topic_time"] == "45") ? " selected=\"selected\"" : "").">45</option>";
                                echo "			<option value=\"50\"".(($topic_result["topic_time"] == "50") ? " selected=\"selected\"" : "").">50</option>";
                                echo "			<option value=\"55\"".(($topic_result["topic_time"] == "55") ? " selected=\"selected\"" : "").">55</option>";
                                echo "			<option value=\"60\"".(($topic_result["topic_time"] == "60") ? " selected=\"selected\"" : "").">60</option>";
                                echo "		</select>";
                                echo "	</td>\n";
                                echo "</tr>\n";
                            }
                            echo "<tr><td colspan=\"2\">&nbsp;</td></tr>";
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
						<div style="float: right; margin-bottom: 5px">
							<ul class="page-action">
								<li><a href="#file-listing" onclick="openDialog('<?php echo ENTRADA_URL; ?>/api/file-wizard-event.api.php?action=add&id=<?php echo $EVENT_ID; ?>')">Add A File</a></li>
							</ul>
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
						echo "			".(($results) ? "<input type=\"button\" class=\"button\" value=\"Delete Selected\" onclick=\"confirmFileDelete()\" />" : "&nbsp;");
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
						<div style="float: right; margin-bottom: 5px">
							<ul class="page-action">
								<li><a href="#link-listing" onclick="openDialog('<?php echo ENTRADA_URL; ?>/api/link-wizard-event.api.php?action=add&id=<?php echo $EVENT_ID; ?>')">Add A Link</a></li>
							</ul>
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
						echo "			".(($results) ? "<input type=\"button\" class=\"button\" value=\"Delete Selected\" onclick=\"confirmLinkDelete()\" />" : "&nbsp;");
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
						<div style="float: right; margin-bottom: 5px">
							<ul class="page-action">
								<li><a href="<?php echo ENTRADA_URL; ?>/admin/quizzes?section=add">Create New Quiz</a></li>
								<li><a href="#quiz-listing" onclick="openDialog('<?php echo ENTRADA_URL; ?>/api/quiz-wizard.api.php?action=add&id=<?php echo $EVENT_ID; ?>')">Attach Existing Quiz</a></li>
							</ul>
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
						echo "			".(($results) ? "<input type=\"button\" class=\"button\" value=\"Detach Selected\" onclick=\"confirmQuizDelete()\" />" : "&nbsp;");
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
				</div>

				<script type="text/javascript">
				$$('select.ed_select_off').each(function(el) {
					$(el).disabled = true;
					$(el).fade({ duration: 0.3, to: 0.25 });
				});
				</script>
				<?php
				/**
				 * View event content history if applicable
				 */
				if ($history) {
                    ?>
                    <a name="event-history-section"></a>
                    <h2 title="Event History Section">Event History</h2>
                    <div id="event-history-section">
                        <p>
                        <?php
                        if (count($_POST)) {
                            $query = "	SELECT a.`history_message` AS message, a.`history_timestamp` AS timestamp, CONCAT_WS(' ', b.`firstname`, b.`lastname`) AS `fullname`
                                        FROM `event_history` AS a
                                        LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
                                        ON a.`proxy_id` = b.`id`
                                        WHERE a.`event_id`  = ".$db->qstr($EVENT_ID)."
                                        ORDER BY `history_timestamp` DESC, `history_message` ASC";
                            $history = $db->GetAll($query);
                        }
                        $previous_day = 0;
                        foreach ($history as $key => $result) {
                            $current_day = mktime(0, 0, 0, date("m",$result["timestamp"]), date("d", $result["timestamp"]), date("Y", $result["timestamp"]));
                            if ($current_day != $previous_day) {
                                $previous_day = $current_day;
                                if ($key > 0) {
                                    echo "</ul></p>";
                                }
                                echo "<strong>".date("F j, Y",$current_day)."</strong><ul class=\"history\">\n";
                            }
                            echo "<li>".date("g:ia ", $result["timestamp"]).$result["fullname"]." ".$result["message"]."</li>";
                        }
                        ?>
                        </ul></p>
                    </div>
                    <?php
				}

				/**
				 * Sidebar item that will provide the links to the different sections within this page.
				 */
				$sidebar_html  = "<ul class=\"menu\">\n";
				$sidebar_html .= "	<li class=\"link\"><a href=\"#event-details-section\" onclick=\"$('event-details-section').scrollTo(); return false;\" title=\"Event Details\">Event Details</a></li>\n";
				$sidebar_html .= "	<li class=\"link\"><a href=\"#event-audience-section\" onclick=\"$('event-audience-section').scrollTo(); return false;\" title=\"Event Audience\">Event Audience</a></li>\n";
				$sidebar_html .= "	<li class=\"link\"><a href=\"#event-objectives-section\" onclick=\"$('event-objectives-section').scrollTo(); return false;\" title=\"Event Objectives\">Event Objectives</a></li>\n";
				$sidebar_html .= "	<li class=\"link\"><a href=\"#event-resources-section\" onclick=\"$('event-resources-section').scrollTo(); return false;\" title=\"Event Resources\">Event Resources</a></li>\n";
                if ($history) {
                    $sidebar_html .= "	<li class=\"link\"><a href=\"#event-history-section\" onclick=\"$('event-history-section').scrollTo(); return false;\" title=\"Event History\">Event History</a></li>\n";
                }
				$sidebar_html .= "</ul>\n";

				new_sidebar_item("Page Anchors", $sidebar_html, "page-anchors", "open", "1.9");
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