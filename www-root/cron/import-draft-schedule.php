<?php

/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Cron job responsible for importing draft schedule information into system
 * 
 * Setup to run the end of each week in CRON.
 *
 * @author Organisation: Queen's University
 * @author Unit: Faculty of Medicine
 * @author Developer: Ryan Warner <ryan.warner@queensu.ca>
 * @copyright Copyright 2012 Queen's Univerity. All Rights Reserved.
 *
 */
@set_time_limit(0);
@set_include_path(implode(PATH_SEPARATOR, array(
	dirname(__FILE__) . "/../core",
	dirname(__FILE__) . "/../core/includes",
	dirname(__FILE__) . "/../core/library",
	get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

if ((@is_dir(CACHE_DIRECTORY)) && (@is_writable(CACHE_DIRECTORY))) {
		/**
		 * Lock present: application busy: quit
		 */
		if (!file_exists(CACHE_DIRECTORY."/import_draft.lck")) {
			if (@file_put_contents(CACHE_DIRECTORY."/import_draft.lck", "L_O_C_K")) {
				application_log("notice", "Draft import lock file created.");
				/*
				 * Fetch approved drafts
				 */
				$query = "	SELECT *
							FROM `drafts`
							WHERE `status` = 'approved'";
				if ($drafts = $db->GetAll($query)) {

					application_log("notice", "Draft schedule importer found ".count($drafts)." approved drafts and started importing.");

					foreach ($drafts as $draft) {
						$msg[$draft["draft_id"]][] = "Imported draft: \"".$draft["draf_title"]."\" on ".date("Y-m-d H:i", time());
						$notification_events = "";

						/*
						 *  fetch the draft events
						 */

						$query = "	SELECT a.`proxy_id`, CONCAT(b.`firstname`, ' ', b.`lastname`) AS `name`, b.`email`
									FROM `draft_creators` AS a
									JOIN `".AUTH_DATABASE."`.`user_data` AS b
									ON a.`proxy_id` = b.`id`
									WHERE `draft_id` = ".$db->qstr($draft["draft_id"]);
						$draft_creators = $db->GetAll($query);

						$query = "	SELECT `option`, `value`
									FROM `draft_options` 
									WHERE `draft_id` = ".$db->qstr($draft["draft_id"]);
						$draft_options = $db->GetAssoc($query);
						
						$query = "	SELECT *
									FROM `draft_events`
									WHERE `draft_id` = ".$db->qstr($draft["draft_id"])."
									ORDER BY `parent_id` ASC";
						$events = $db->GetAll($query);
						if ($events) {

							application_log("notice", "Draft schedule importer found ".count($events)." events in draft ".$draft["draft_id"].".");

							foreach ($events as $event) {

								if ($event["event_id"]) {
									$old_event_id = $event["event_id"];
									$old_events[$old_event_id] = $event;
									unset($event["event_id"]);
								} else {
									$old_event_id = false;
								}

								$event["updated_date"]	= time();
								$event["updated_by"]	= $draft_creators[0]["proxy_id"];
								if (empty($event["event_children"])) {
									$event["event_children"] = 0;
								}
								echo $event["parent_id"]."-";
								if (isset($event["parent_id"]) && in_array($event["parent_id"], $old_events[$event["parent_id"]])) {
									$event["parent_id"] = $old_events[$event["parent_id"]]["new_event_id"];
								}
								echo $event["parent_id"]."\n";
								if ($db->AutoExecute("`events`", $event, 'INSERT')) {
									$event_id = $db->Insert_ID();
									$old_events[$old_event_id]["new_event_id"] = $event_id;
									application_log("success", "Successfully created event [".$event_id."]");
								} else {
									$error++;
									application_log("error", "Error inserting event [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
								}

								$notification_events .= $event["event_title"]." at ".date("Y-m-d H:i", $event["event_start"])."\n";

								/*
								*  add the eventtypes associated with the draft event
								*/
								$query = "	SELECT *
											FROM `draft_eventtypes`
											WHERE `devent_id` = ".$db->qstr($event["devent_id"]);
								if ($eventtypes = $db->GetAll($query)) {
									application_log("notice", "Found ".count($eventtypes)." eventtypes for draft event [".$event["devent_id"]."].");
									foreach ($eventtypes as $eventtype) {
										$eventtype["event_id"]		= $event_id;
										unset($eventtype["deventtype_id"]);
										unset($eventtype["eeventtype_id"]);
										unset($eventtype["devent_id"]);
										if ($db->AutoExecute("`event_eventtypes`", $eventtype, "INSERT")) {
											application_log("success", "Successfully inserted eventtype [".$db->Insert_ID()."] for event [".$event_id."].");
										} else {
											$error++;
											application_log("error", "Error inserting event_eventtype [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
										}
									}
								} else {
									application_log("notice", "Found no eventtypes for draft event [".$event["devent_id"]."].");
								}


								/*
								*  add the event contacts associated with the draft event
								*/
								$query = "	SELECT *
											FROM `draft_contacts`
											WHERE `devent_id` = ".$db->qstr($event["devent_id"]);
								if ($eventcontacts = $db->GetAll($query)) {
									application_log("notice", "Found ".count($eventcontacts)." event contacts for draft event [".$event["devent_id"]."].");
									foreach ($eventcontacts as $contact) {
										$contact["event_id"] = $event_id;
										$contact["updated_date"] = time();
										$contact["updated_by"] =  $draft_creators[0]["proxy_id"];
										unset($contact["dcontact_id"]);
										unset($contact["econtact_id"]);
										unset($contact["devent_id"]);
										if ($db->AutoExecute("`event_contacts`", $contact, "INSERT")) {
											application_log("success", "Successfully inserted event contact [".$db->Insert_ID()."] for event [".$event_id."].");
											$msg[$draft["draft_id"]]["contacts"][$contact["proxy_id"]][] = $contact["email"];
											$msg[$draft["draft_id"]]["contacts"][$contact["proxy_id"]][] = $contact["fullname"];
										} else {
											$error++;
											application_log("error", "Error inserting event_contact [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
										}
									}
								}

								/*
								* add the event audience associated with the draft event
								*/
								$query = "	SELECT *
											FROM `draft_audience`
											WHERE `devent_id` = ".$db->qstr($event["devent_id"]);
								if ($eventaudience = $db->GetAll($query)) {
									application_log("notice", "Found ".count($eventaudience)." event audience members for draft event [".$event["devent_id"]."].");
									foreach ($eventaudience as $audience) {

										$audience["event_id"] = $event_id;
										$audience["updated_date"] = time();
										$audience["updated_by"] =  $draft_creators[0]["proxy_id"];
										unset($audience["daudience_id"]);
										unset($audience["eaudience_id"]);
										unset($audience["devent_id"]);

										if ($db->AutoExecute("`event_audience`", $audience, "INSERT")) {
											application_log("success", "Successfully inserted event audience [".$db->Insert_ID()."] for event [".$event_id."].");
										} else {
											$error++;
											application_log("error", "Error inserting event_audience [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
										}
									}
								}

								if ($old_event_id) {

									if ($draft_options["files"]) {
										/*
										*  add the event files associated with the event
										*/
										$query = "	SELECT *
													FROM `event_files`
													WHERE `event_id` = ".$db->qstr($old_event_id)."
													AND `file_category` != 'podcast'";
										if ($event_files = $db->GetAll($query)) {
											application_log("notice", "Found ".count($event_files)." event files attached to original event [".$old_event_id."], will be ported over to new event [".$event_id."].");
											foreach ($event_files as $file) {
												$old_event_file = (int) $file["efile_id"];
												unset($file["efile_id"]);
												$file["event_id"]		= $event_id;
												$file["accesses"]		= 0;
												$file["updated_by"]		= $draft_creators[0]["proxy_id"];
												if ($db->AutoExecute("`event_files`", $file, "INSERT")) {
													application_log("success", "Successfully inserted file [".$db->Insert_ID()."] from old event [".$old_event_id."], for new event [".$event_id."].");

													$new_file_id = (int) $db->Insert_ID();
													if (file_exists(FILE_STORAGE_PATH."/".$old_event_file)) {
														if (copy(FILE_STORAGE_PATH."/".$old_event_file, FILE_STORAGE_PATH."/".$new_file_id)) {
															application_log("success", "Successfully copied file [".$old_event_file."] to file [".$new_file_id."], for new event [".$event_id."].");
															$copied_files[] = $processed_file["file_name"];
														} else {
															application_log("success", "Failed to copy file [".$old_event_file."] to file [".$new_file_id."].");
														}
													}
												} else {
													$error++;
													application_log("error", "Error inserting event_files [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
												}
											}
										} else {
											application_log("notice", "Found no event files attached to original event [".$old_event_id."].");
										}
									}
									
									if ($draft_options["links"]) {
										/*
										*  add the event links associated with the event
										*/
										$query = "	SELECT *
													FROM `event_links`
													WHERE `event_id` = ".$db->qstr($old_event_id);
										if ($event_links = $db->GetAll($query)) {
											application_log("notice", "Found ".count($event_links)." event links attached to original event [".$old_event_id."], will be ported over to new event [".$event_id."].");
											foreach ($event_links as $link) {
												unset($link["elink_id"]);
												$link["event_id"]		= $event_id;
												$link["accesses"]		= 0;
												$file["updated_by"]		= $draft_creators[0]["proxy_id"];
												if ($db->AutoExecute("`event_links`", $link, "INSERT")) {
													application_log("success", "Successfully inserted link [".$db->Insert_ID()."] from old event [".$old_event_id."], for new event [".$event_id."].");
												} else {
													$error++;
													application_log("error", "Error inserting event_links [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
												}
											}
										} else {
											application_log("notice", "Found no event links attached to original event [".$old_event_id."].");
										}
									}
									
									if ($draft_options["objectives"]) {
										/*
										 *  add the event objectives associated with the draft event
										 */
										$query = "	SELECT *
													FROM `event_objectives`
													WHERE `event_id` = ".$db->qstr($old_event_id);
										if ($event_objectives = $db->GetAll($query)) {
											foreach ($event_objectives as $objective) {
												unset($objective["eobjective_id"]);
												$objective["event_id"]		= $event_id;
												$objective["updated_by"]	= $draft_creators[0]["proxy_id"];
												if ($db->AutoExecute("`event_objectives`", $objective, "INSERT")) {
													application_log("success", "Successfully inserted objective [".$db->Insert_ID()."] from old event [".$old_event_id."], for new event [".$event_id."].");
												} else {
													$error++;
													application_log("error", "Error inserting event_objectives [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
												}
											}
										} else {
											application_log("notice", "Found no event objectives attached to original event [".$old_event_id."].");
										}
									}
									
									if ($draft_options["topics"]) {
										/*
										 *  add the event topics associated with the draft event
										 */
										$query = "	SELECT *
													FROM `event_topics`
													WHERE `event_id` = ".$db->qstr($old_event_id);
										if ($event_topics = $db->GetAll($query)) {
											foreach ($event_topics as $topic) {
												unset($topic["eobjective_id"]);
												$topic["event_id"]		= $event_id;
												$topic["updated_by"]	= $draft_creators[0]["proxy_id"];
												if ($db->AutoExecute("`event_objectives`", $topic, "INSERT")) {
													application_log("success", "Successfully inserted topic [".$db->Insert_ID()."] from old event [".$old_event_id."], for new event [".$event_id."].");
												} else {
													$error++;
													application_log("error", "Error inserting event_objectives [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
												}
											}
										} else {
											application_log("notice", "Found no event topics attached to original event [".$old_event_id."].");
										}
									}
									
									if ($draft_options["quizzes"]) {
										/*
										*  add the event objectives associated with the draft event
										*/
										$query = "	SELECT *
													FROM `attached_quizzes`
													WHERE `content_type` = 'event'
													AND `content_id` = ".$db->qstr($old_event_id);
										if ($event_quizzes = $db->GetAll($query)) {
											foreach ($event_quizzes as $quiz) {
												unset($quiz["aquiz_id"]);
												$quiz["content_id"]		= $event_id;
												$quiz["accesses"]		= 0;
												$quiz["updated_by"]	= $draft_creators[0]["proxy_id"];
												if ($db->AutoExecute("`attached_quizzes`", $quiz, "INSERT")) {
													application_log("success", "Successfully inserted quiz [".$db->Insert_ID()."] from old event [".$old_event_id."], for new event [".$event_id."].");
												} else {
													$error++;
													application_log("error", "Error inserting event_objectives [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
												}
											}
										} else {
											application_log("notice", "Found no event quizzes attached to original event [".$old_event_id."].");
										}
									}

								}

								if (!$error) {
									$count++;
									$msg[$draft["draft_id"]][] = $event["event_title"]." - ".date("Y-m-d H:i",$event["event_start"]);
								}
								
								unset ($old_event_id);
							}

						} else {
							application_log("error", "Draft [".$draft["draft_id"]."] did not contain any events.");
						}

						if (!$error) {
							// notify the draft creators that their draft has been imported
							$message = "This email is to notify you that the draft learning event schedule \"".$draft["name"]."\" was successfully imported on ".date("Y-m-d H:i", time()).".\n\n";
							$message .= "The following learning events were imported into the system:\n";
							$message .= "------------------------------------------------------------\n\n";
							$message .= $notification_events;

							if ($draft_creators) {
								$mail = new Zend_Mail();
								$mail->addHeader("X-Section", "Learning Events Notification System", true);
								$mail->setFrom($AGENT_CONTACTS["administrator"]["email"], $AGENT_CONTACTS["administrator"]["name"]);
								$mail->clearSubject();
								$mail->setSubject("Draft Learning Event Schedule Imported");
								$mail->setBodyText($message);
								$mail->clearRecipients();

								foreach ($draft_creators as $result) {
									$mail->addTo($result["email"], $result["name"]);
								}

								if ($mail->send()) {
									application_log("success", "Successfully sent email to draft [".$draft_id."] creators.");
								} else {
									application_log("error", "Failed to sent email to draft [".$draft_id."] creators.");
								}
							}
							
							$query = "UPDATE `drafts` SET `status` = 'closed' WHERE `draft_id` = ".$db->qstr($draft["draft_id"]);
							if ($db->Execute($query)) {
							   /*
								* draft successfully imported and draft deleted from drafts tables
								*/
								application_log("success", "Successfully closed draft [draft_id-".$draft["draft_id"]."]. ".$count." records imported.");
							} else {
								/*
								 * something went wrong
								 */
								application_log("error", "Failed to close draft [draft_id-".$draft["draft_id"]."], DB said: ".$db->ErrorMsg());
							}
						}
					}
				} else {
					application_log("notice", "Draft schedule importer found no approved drafts and exited.");
				}
				
				if (unlink(CACHE_DIRECTORY."/import_draft.lck")) {
					application_log("success", "Lock file deleted.");
				} else {
					application_log("error", "Unable to delete draft import lock file: ".CACHE_DIRECTORY."/import_draft.lck");
				}
			} else {
				application_log("error", "Could not write draft import lock file, exiting.");
			}
		} else {
			application_log("error", "Draft import lock file found, exiting.");
		}
} else {
	application_log("error", "Error with cache directory [".CACHE_DIRECTORY."], not found or not writable.");
}