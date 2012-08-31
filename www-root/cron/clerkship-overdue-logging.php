<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Cron job responsible for adding users to the google mail-list.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2009 Queen's University. All Rights Reserved.
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
global $ENTRADA_ACTIVE_TEMPLATE;

$query		= "	SELECT a.*, b.`etype_id` as `proxy_id`, c.*, CONCAT_WS(' ', e.`firstname`, e.`lastname`) as `fullname`, MIN(a.`event_start`) as `start`, MAX(a.`event_finish`) AS `finish`
				FROM `".CLERKSHIP_DATABASE."`.`events` AS a
				JOIN `".CLERKSHIP_DATABASE."`.`event_contacts` AS b
				ON b.`event_id` = a.`event_id`
				JOIN `".CLERKSHIP_DATABASE."`.`global_lu_rotations` AS c
				ON a.`rotation_id` = c.`rotation_id`
				JOIN `".AUTH_DATABASE."`.`user_data` AS e
				ON b.`etype_id` = e.`id`
				JOIN `".AUTH_DATABASE."`.`user_access` AS f
				ON e.`id` = f.`user_id`
				AND f.`app_id` = '".AUTH_APP_ID."'
				WHERE b.`econtact_type` = 'student'
				AND f.`group` >= 'student'
				AND f.`role` >= ".$db->qstr(CLERKSHIP_FIRST_CLASS)."
				AND c.`rotation_id` > 0
				AND c.`rotation_id` < ".$db->qstr(MAX_ROTATION)."
				GROUP BY b.`etype_id`, a.`rotation_id`
				ORDER BY `fullname` ASC";

$results = $db->GetAll($query);

if ($results) {
	$db->Execute("DELETE FROM `".CLERKSHIP_DATABASE."`.`logbook_overdue`");
	foreach ($results as $result) {
		if ($result["rotation_id"] && ($result["finish"] < time() || ((time() - $result["start"]) > (($result["finish"] - $result["start"]) * $result["percent_period_complete"] / 100)))) {
			$query = "	SELECT * FROM `".CLERKSHIP_DATABASE."`.`logbook_mandatory_objectives`
						WHERE `rotation_id` = ".$db->qstr($result["rotation_id"])."
						AND `grad_year_min` <= ".$db->qstr(get_account_data("grad_year", $result["proxy_id"]));
			$oresults = $db->GetAll($query);
			if ($oresults) {
				$total_required = 0;
				$total_logged = 0;
				$objective_string = "";
				foreach ($oresults as $objective) {
					if ($objective_string) {
						$objective_string .= ",".$db->qstr($objective["objective_id"]);
					} else {
						$objective_string = $db->qstr($objective["objective_id"]);
					}
					$objectives[$objective["objective_id"]] = $objective["number_required"];
					$total_required += $objective["number_required"];
				}
				if ($objective_string) {
					$query = "	SELECT COUNT(a.`objective_id`) as number_logged, a.`objective_id` FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_objectives` AS a
								LEFT JOIN `".CLERKSHIP_DATABASE."`.`logbook_entries` AS b
								ON a.`lentry_id` = b.`lentry_id`
								WHERE a.`objective_id` IN  (".$objective_string.")
								AND b.`proxy_id` = ".$db->qstr($result["proxy_id"])."
								AND b.`entry_active` = 1
								GROUP BY a.`objective_id`";
					$numbers_logged = $db->GetAll($query);
					if ($numbers_logged) {
						foreach ($numbers_logged as $number_logged) {
							if ($number_logged > $objectives[$number_logged["objective_id"]]) {
								$total_logged += $objectives[$number_logged["objective_id"]];
							} else {
								$total_logged += $number_logged["number_logged"];
							}
						}
					}
					if (((($total_logged / $total_required * 100) < $result["percent_required"]) || ($result["finish"] < time() && $total_logged < $total_required)) && (((($result["finish"] - $result["start"]) / ($result["finish"] - time()) * 100) >= $result["percent_period_complete"]) || $result["finish"] < time())) {
						$overdue_logging = array(
													"proxy_id" => $result["proxy_id"],
													"rotation_id" => $result["rotation_id"],
													"event_id" => $result["event_id"],
													"logged_required" => $total_required,
													"logged_completed" => $total_logged
												);
						$db->AutoExecute(CLERKSHIP_DATABASE.".logbook_overdue", $overdue_logging, "INSERT");
						//@todo: notify the clerk, and the rotation's admin assistant of their being in arrears, only once per week, per rotation
						if (defined("CLERKSHIP_EMAIL_NOTIFICATIONS") && CLERKSHIP_EMAIL_NOTIFICATIONS) {
							$mail = new Zend_Mail();
							$mail->addHeader("X-Originating-IP", $_SERVER["REMOTE_ADDR"]);
							$mail->addHeader("X-Section", "Clerkship Notification System",true);
							$mail->clearFrom();
							$mail->clearSubject();
							$mail->setFrom($AGENT_CONTACTS["agent-notifications"]["email"], APPLICATION_NAME.' Clerkship System');
							$mail->setSubject("Clerkship Logbook Deficiency Notification");
							$NOTIFICATION_MESSAGE		 	 = array();
											
							$query			= "     SELECT CONCAT_WS(' ', `firstname`, `lastname`) as `fullname`, `email`, `id` 
												FROM `".AUTH_DATABASE."`.`user_data`
												WHERE `id` = ".$db->quote($result["proxy_id"]);
							$clerk			= $db->GetRow($query);
							
							$query 			= "	SELECT a.`rotation_title`, c.`email`, CONCAT_WS(' ', c.`firstname`, c.`lastname`) as `fullname`, b.`pcoord_id`
												FROM `".CLERKSHIP_DATABASE."`.`global_lu_rotations` AS a
												LEFT JOIN `courses` AS b
												ON a.`course_id` = b.`course_id`
												LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS c
												ON b.`pcoord_id` = c.`id`
												WHERE a.`rotation_id` = ".$db->quote($result["rotation_id"])."
												AND b.`course_active` = '1'";
							$rotation	= $db->GetRow($query);
							
							if ($rotation) {
								$query = "	SELECT `notified_date` FROM `".CLERKSHIP_DATABASE."`.`logbook_notification_history`
											WHERE `clerk_id` = ".$db->qstr($result["proxy_id"])."
											AND `proxy_id` = ".$db->qstr($rotation["pcoord_id"])."
											AND `rotation_id` = ".$db->quote($result["rotation_id"])."
											ORDER BY `notified_date` DESC
											LIMIT 1";
								$last_notified = $db->GetOne($query);
								
								if ($last_notified <= (strtotime("-1 week"))) {
																	
									$search	= array(
														"%CLERK_FULLNAME%",
														"%ROTATION_TITLE%",
														"%PROFILE_URL%",
														"%ROTATION_OVERVIEW_URL%",
														"%ENTRY_MANAGEMENT_URL%",
														"%APPLICATION_NAME%",
														"%ENTRADA_URL%"
													);
									$replace	= array(
														$clerk["fullname"],
														$rotation["rotation_title"],
														ENTRADA_URL."/people?id=".$result["proxy_id"],
														ENTRADA_URL."/clerkship/logbook?section=view&id=".$result["proxy_id"]."&core=".$result["rotation_id"],
														ENTRADA_URL."/clerkship/logbook?id=".$result["proxy_id"]."&sb=rotation&rotation=".$result["rotation_id"],
														APPLICATION_NAME,
														ENTRADA_URL
													);
									
									$NOTIFICATION_MESSAGE["textbody"] = file_get_contents(WEBSITE_ABSOLUTE."/templates/".$ENTRADA_ACTIVE_TEMPLATE."/email/clerkship-deficiency-admin-notification.txt");
									$mail->setBodyText(clean_input(str_replace($search, $replace, $NOTIFICATION_MESSAGE["textbody"]), array("postclean")));
									
									if ($rotation["pcoord_id"]) {
										$NOTICE = Array(
															"target" => "proxy_id:".$rotation["pcoord_id"],
															"notice_summary" => clean_input(str_replace($search, $replace, "The clerk [%CLERK_FULLNAME%] has not met the logging requirements for their current rotation [%ROTATION_TITLE%] after the allotted time. Please review their logbook entries and progress via the <a href=\"%ROTATION_OVERVIEW_URL%\">Rotation progress</a> section."), array("postclean")),
															"display_from" => time(),
															"display_until" => strtotime("+1 week"),
															"updated_date" => time(),
															"updated_by" => 3499,
															"organisation_id" => 1
														);
										if($db->AutoExecute("notices", $NOTICE, "INSERT")) {
											if($NOTICE_ID = $db->Insert_Id()) {
												application_log("success", "Successfully added notice ID [".$NOTICE_ID."]");
											} else {
												application_log("error", "Unable to fetch the newly inserted notice identifier for this notice.");
											}
										} else {
											application_log("error", "Unable to insert new notice into the system. Database said: ".$db->ErrorMsg());
										}
									}
									
									$mail->clearRecipients();
									if (strlen($rotation['email'])) {
										$mail->addTo($rotation['email'], $rotation['fullname']);
										$sent = true;
										try {
											$mail->send();
										}
										catch (Exception $e) {
											$sent = false;
										}
										if($sent) {
											application_log("success", "Sent overdue logging notification to Program Coordinator ID [".$rotation["pcoord_id"]."].");
										} else {
											application_log("error", "Unable to send overdue logging notification to Program Coordinator ID [".$rotation["pcoord_id"]."].");
										}
										$NOTICE_HISTORY = Array(
																"clerk_id" => $result["proxy_id"],
																"proxy_id" => $rotation["pcoord_id"],
																"rotation_id" => $result["rotation_id"],
																"notified_date" => time()
																);
										if($db->AutoExecute(CLERKSHIP_DATABASE.".logbook_notification_history", $NOTICE_HISTORY, "INSERT")) {
											if($HISTORY_ID = $db->Insert_Id()) {
												application_log("success", "Successfully added notification history ID [".$HISTORY_ID."]");
											} else {
												application_log("error", "Unable to fetch the newly inserted notification history identifier for this notice.");
											}
										} else {
											application_log("error", "Unable to insert new notification history record into the system. Database said: ".$db->ErrorMsg());
										}
									}
								}
								$query = "	SELECT `notified_date` FROM `".CLERKSHIP_DATABASE."`.`logbook_notification_history`
											WHERE `clerk_id` = ".$db->qstr($result["proxy_id"])."
											AND `proxy_id` = ".$db->qstr($result["proxy_id"])."
											AND `rotation_id` = ".$db->quote($result["rotation_id"])."
											ORDER BY `notified_date` DESC
											LIMIT 1";
								$last_notified = $db->GetOne($query);
								
								if ($last_notified <= (strtotime("-1 week"))) {
									$NOTIFICATION_MESSAGE["textbody"] = file_get_contents(WEBSITE_ABSOLUTE."/templates/".$ENTRADA_ACTIVE_TEMPLATE."/email/clerkship-deficiency-clerk-notification.txt");
									
									$search	= array(
														"%ROTATION_TITLE%",
														"%ROTATION_OVERVIEW_URL%",
														"%ENTRY_MANAGEMENT_URL%",
														"%APPLICATION_NAME%",
														"%ENTRADA_URL%"
													);
													
									$replace	= array(
														$rotation["rotation_title"],
														ENTRADA_URL."/clerkship/logbook?section=view&core=".$result["rotation_id"],
														ENTRADA_URL."/clerkship/logbook?sb=rotation&rotation=".$result["rotation_id"],
														APPLICATION_NAME,
														ENTRADA_URL
													);
									
									$mail->setBodyText(clean_input(str_replace($search, $replace, $NOTIFICATION_MESSAGE["textbody"]), array("postclean")));
									
									$NOTICE = Array(
														"target" => "proxy_id:".$result["proxy_id"],
														"notice_summary" => clean_input(str_replace($search, $replace, "It has come to our attention that you have not met the logging requirements for the [%ROTATION_TITLE%] rotation after the allotted time. Please review your logbook entries and progress, and make note of how you plan to solve this problem via the <a href=\"%ROTATION_OVERVIEW_URL%\">Rotation progress</a> section."), array("postclean")),
														"display_from" => time(),
														"display_until" => strtotime("+1 week"),
														"updated_date" => time(),
														"updated_by" => 3499,
														"organisation_id" => 1
													);
									
									if($db->AutoExecute("notices", $NOTICE, "INSERT")) {
										if($NOTICE_ID = $db->Insert_Id()) {
											application_log("success", "Successfully added notice ID [".$NOTICE_ID."]");
										} else {
											application_log("error", "Unable to fetch the newly inserted notice identifier for this notice.");
										}
									} else {
										application_log("error", "Unable to insert new notice into the system. Database said: ".$db->ErrorMsg());
									}
									$mail->clearRecipients();
									if (strlen($clerk['email'])) {
										$mail->addTo($clerk['email'], $clerk['fullname']);
										$sent = true;
										try {
											$mail->send();
										}
										catch (Exception $e) {
											$sent = false;
										}
										if($sent) {
											application_log("success", "Sent overdue logging notification to clerk ID [".$clerk["id"]."].");
										} else {
											application_log("error", "Unable to send overdue logging notification to clerk ID [".$clerk["id"]."].");
										}
										$NOTICE_HISTORY = Array(
																"clerk_id" => $result["proxy_id"],
																"proxy_id" => $result["proxy_id"],
																"rotation_id" => $result["rotation_id"],
																"notified_date" => time()
																);
										if($db->AutoExecute(CLERKSHIP_DATABASE.".logbook_notification_history", $NOTICE_HISTORY, "INSERT")) {
											if($HISTORY_ID = $db->Insert_Id()) {
												application_log("success", "Successfully added notification history ID [".$HISTORY_ID."]");
											} else {
												application_log("error", "Unable to fetch the newly inserted notification history identifier for this notice.");
											}
										} else {
											application_log("error", "Unable to insert new notification history record into the system. Database said: ".$db->ErrorMsg());
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
}
?>
