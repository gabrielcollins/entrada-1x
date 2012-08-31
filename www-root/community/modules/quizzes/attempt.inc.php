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
 * This section is loaded when an individual wants to attempt a quiz.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("COMMUNITY_INCLUDED")) || (!defined("IN_QUIZZES"))) {
    exit;
} elseif (!$COMMUNITY_LOAD) {
	exit;
}
if ($LOGGED_IN) {
	if ($RECORD_ID) {
		$query			= "	SELECT a.*, c.`community_title` AS `content_title`, c.`community_url`, cp.`page_url`, d.`quiztype_code`, d.`quiztype_title`
							FROM `attached_quizzes` AS a
							LEFT JOIN `quizzes` AS b
							ON a.`quiz_id` = b.`quiz_id`
							LEFT JOIN `community_pages` AS cp
							ON a.`content_type` = 'community_page' 
							AND a.`content_id` = cp.`cpage_id`
							LEFT JOIN `communities` AS c
							ON cp.`community_id` = c.`community_id`
							LEFT JOIN `quizzes_lu_quiztypes` AS d
							ON d.`quiztype_id` = a.`quiztype_id`
							WHERE a.`aquiz_id` = ".$db->qstr($RECORD_ID)."
							AND cp.`page_url` = ".$db->qstr($PAGE_URL)."
							AND b.`quiz_active` = '1'";
		$quiz_record	= $db->GetRow($query);
		if ($quiz_record) {
			$BREADCRUMB[]	= array("url" => ENTRADA_URL."/community".$quiz_record["community_url"].":".$quiz_record["page_url"], "title" => limit_chars($quiz_record["content_title"], 32));
			$BREADCRUMB[]	= array("url" => ENTRADA_URL."/community".$quiz_record["community_url"].":".$quiz_record["page_url"]."?section=attempt&id=".$RECORD_ID, "title" => limit_chars($quiz_record["quiz_title"], 32));
	
			/**
			 * Providing there is no release date, or the release date is in the past
			 * on both the quiz and the event, allow them to continue.
			 */
			if ((((int) $quiz_record["release_date"] === 0) || ($quiz_record["release_date"] <= time())) && ($QUIZ_TYPE == "community_page" || (((int) $quiz_record["event_release_date"] === 0) || ($quiz_record["event_release_date"] <= time())))) {
				/**
				 * Providing there is no expiry date, or the expiry date is in the
				 * future on both the quiz and the event, allow them to continue.
				 */
				if ((((int) $quiz_record["release_until"] === 0) || ($quiz_record["release_until"] > time())) && ($QUIZ_TYPE == "community_page" || (((int) $quiz_record["event_release_until"] === 0) || ($quiz_record["event_release_until"] > time())))) {
					/**
					 * Get the number of completed attempts this user has made.
					 */
					$completed_attempts = quiz_fetch_attempts($RECORD_ID);
	
					/**
					 * Providing they can still still make attempts at this quiz, allow them to continue.
					 */
					if (((int) $quiz_record["quiz_attempts"] === 0) || ($completed_attempts < $quiz_record["quiz_attempts"])) {
						$problem_questions = array();
	
						echo "<h1>".html_encode($quiz_record["quiz_title"])."</h1>";
	
						// Error checking
						switch ($STEP) {
							case 2 :
								/**
								 * Check to see if they currently have any quiz attempts underway, if they do then
								 * restart their session, otherwise start them a new session.
								 */
								$query				= "	SELECT *
														FROM `quiz_progress`
														WHERE `aquiz_id` = ".$db->qstr($RECORD_ID)."
														AND `proxy_id` = ".$db->qstr($_SESSION["details"]["id"])."
														AND `progress_value` = 'inprogress'
														ORDER BY `updated_date` ASC";
								$progress_record	= $db->GetRow($query);
								if ($progress_record) {
									$qprogress_id		= $progress_record["qprogress_id"];
									$quiz_start_time	= $progress_record["updated_date"];
									$quiz_end_time		= (((int) $quiz_record["quiz_timeout"]) ? ($quiz_start_time + ($quiz_record["quiz_timeout"] * 60)) : 0);
									$quiz_score			= 0;
									$quiz_value			= 0;
	
									/**
									 * Check if there is a timeout set, and if the current time is less than the timeout.
									 */
									if ((!$quiz_end_time) || (time() <= ($quiz_end_time + 30))) {
										if ((isset($_POST["responses"])) && (is_array($_POST["responses"])) && (count($_POST["responses"]) > 0)) {
											/**
											 * Get a list of all of the questions in this quiz so we
											 * can run through a clean set of questions.
											 */
											$query		= "	SELECT a.*
															FROM `quiz_questions` AS a
															WHERE a.`quiz_id` = ".$db->qstr($progress_record["quiz_id"])."
															AND a.`question_active` = '1'
															ORDER BY a.`question_order` ASC";
											$questions	= $db->GetAll($query);
											if ($questions) {
												if ((count($_POST["responses"])) != (count($questions))) {
													$ERROR++;
													$ERRORSTR[] = "In order to submit your quiz for marking, you must first answer all of the questions.";
												}
	
												foreach ($questions as $question) {
													/**
													 * Checking to see if the qquestion_id was submitted with the
													 * response $_POST, and if they've actually answered the question.
													 */
													if ((isset($_POST["responses"][$question["qquestion_id"]])) && ($qqresponse_id = clean_input($_POST["responses"][$question["qquestion_id"]], "int"))) {
														if (!quiz_save_response($qprogress_id, $progress_record["aquiz_id"], $progress_record["content_id"], $progress_record["quiz_id"], $question["qquestion_id"], $qqresponse_id, $QUIZ_TYPE)) {
															$ERROR++;
															$ERRORSTR[] = "A problem was found storing a question response, please verify your responses and try again.";
	
															$problem_questions[] = $question["qquestion_id"];
														}
													} else {
														$ERROR++;
	
														$problem_questions[] = $question["qquestion_id"];
													}
												}
											} else {
												$ERROR++;
												$ERRORSTR[] = "An error occurred while attempting to save your quiz responses. The system administrator has been notified of this error; please try again later.";
	
												application_log("error", "Unable to find any quiz questions for quiz_id [".$progress_record["quiz_id"]."]. Database said: ".$db->ErrorMsg());
											}
	
											/**
											 * We can now safely say that all questions have valid responses
											 * and that we have stored those responses quiz_progress_responses table.
											 */
											if (!$ERROR) {
												$PROCESSED = quiz_load_progress($qprogress_id);
	
												foreach ($questions as $question) {
													$question_correct	= false;
													$question_points	= 0;
	
													$query		= "	SELECT a.*
																	FROM `quiz_question_responses` AS a
																	WHERE a.`qquestion_id` = ".$db->qstr($question["qquestion_id"])."
																	AND a.`response_active` = '1'
																	ORDER BY ".(($question["randomize_responses"] == 1) ? "RAND()" : "a.`response_order` ASC");
													$responses	= $db->GetAll($query);
													if ($responses) {
														foreach ($responses as $response) {
															$response_selected	= false;
															$response_correct	= false;
	
															if ($PROCESSED[$question["qquestion_id"]] == $response["qqresponse_id"]) {
																$response_selected = true;
	
																if ($response["response_correct"] == 1) {
																	$response_correct	= true;
																	$question_correct	= true;
																	$question_points	= $question["question_points"];
																} else {
																	$response_correct	= false;
																}
															}
														}
													}
	
													$quiz_score += $question_points;
													$quiz_value += $question["question_points"];
												}
	
												$quiz_progress_array	= array (
																			"progress_value" => "complete",
																			"content_id" => $quiz_record["content_id"],
																			"content_type" => $QUIZ_TYPE,
																			"quiz_score" => $quiz_score,
																			"quiz_value" => $quiz_value,
																			"updated_date" => time(),
																			"updated_by" => $_SESSION["details"]["id"]
																		);
	
												if ($db->AutoExecute("quiz_progress", $quiz_progress_array, "UPDATE", "qprogress_id = ".$db->qstr($qprogress_id))) {
													/**
													 * Add a completed quiz statistic.
													 */
													add_statistic("events", "quiz_complete", "aquiz_id", $RECORD_ID);
	
													/**
													 * Increase the number of completed attempts this quiz has had.
													 */
													if (!$db->AutoExecute("attached_quizzes", array("accesses" => ($quiz_record["accesses"] + 1)), "UPDATE", "aquiz_id = ".$db->qstr($RECORD_ID))) {
														application_log("error", "Unable to increment the total number of accesses (the number of completed quizzes) of aquiz_id [".$RECORD_ID."].");
													}
	
													application_log("success", "Proxy_id [".$_SESSION["details"]["id"]."] has completed aquiz_id [".$RECORD_ID."].");
													
													/**
													 * Check if this is a formative quiz, or a summative quiz that has passed it's release date (not likely)
													 * then forward the user on the quiz results section; otherwise, let them know that their quiz
													 * has been accepted and return them to the event page.
													 */
													if (($quiz_record["quiztype_code"] == "immediate") || (($quiz_record["quiztype_code"] == "delayed") && (((int) $quiz_record["release_until"] === 0) || ($quiz_record["release_until"] <= time())))) {
														header("Location: ".ENTRADA_URL."/community".$quiz_record["community_url"].":".$quiz_record["page_url"]."?section=results&id=".$progress_record["qprogress_id"]);
														exit;
													} else {
														$url = ENTRADA_URL."/community".$quiz_record["community_url"].":".$quiz_record["page_url"];
	
														$SUCCESS++;
														$SUCCESSSTR[] = "Thank-you for completing the <strong>".html_encode($quiz_record["quiz_title"])."</strong> quiz. Your responses have been successfully recorded, and your grade and any feedback will be released <strong>".date(DEFAULT_DATE_FORMAT, $quiz_record["release_until"])."</strong>.<br /><br />You will now be redirected back to the learning event; this will happen <strong>automatically</strong> in 15 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
	
														$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 15000)";
													}
												} else {
													application_log("error", "Unable to record the final quiz results for aquiz_id [".$RECORD_ID."] in the quiz_progress table. Database said: ".$db->ErrorMsg());
	
													$ERROR++;
													$ERRORSTR[] = "We were unable to record the final results for this quiz at this time. Please be assured that your responses are saved, but you will need to come back to this quiz to re-submit it. This problem has been reported to a system administrator; please try again later.";
	
													echo display_error();
												}
											}
										} else {
											$ERROR++;
											$ERRORSTR[] = "In order to submit your quiz for marking, you must first answer some of the questions.";
										}
									} else {
										$quiz_progress_array	= array (
																	"progress_value" => "expired",
																	"content_id" => $quiz_record["content_id"],
																	"content_type" => $QUIZ_TYPE,
																	"quiz_score" => "0",
																	"quiz_value" => "0",
																	"updated_date" => time(),
																	"updated_by" => $_SESSION["details"]["id"]
																);
	
										if (!$db->AutoExecute("quiz_progress", $quiz_progress_array, "UPDATE", "qprogress_id = ".$db->qstr($qprogress_id))) {
											application_log("error", "Unable to update the qprogress_id [".$qprogress_id."] to expired. Database said: ".$db->ErrorMsg());
										}
	
										$completed_attempts += 1;
	
										$ERROR++;
										$ERRORSTR[] = "We were unable to save your previous quiz attempt because your time limit expired <strong>".date(DEFAULT_DATE_FORMAT, $quiz_end_time)."</strong>, and you submitted your quiz <strong>".date(DEFAULT_DATE_FORMAT)."</strong>.";
	
										application_log("notice", "Unable to save qprogress_id [".$qprogress_id."] because it expired.");
									}
								} else {
									$ERROR++;
									$ERRORSTR[] = "We were unable to locate a quiz that is currently in progress.<br /><br />If you pressed your web-browsers back button, please refrain from doing this when you are posting quiz information.";
									
									application_log("error", "Unable to locate a quiz currently in progress when attempting to save a quiz.");
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
	
						if (((int) $quiz_record["quiz_attempts"] === 0) || ($completed_attempts < $quiz_record["quiz_attempts"])) {
							// Display Content
							switch ($STEP) {
								case 2 :
									if ($SUCCESS) {
										echo display_success();
									}
								break;
								case 1 :
								default :
									/**
									 * Check to see if they currently have any quiz attempts underway, if they do then
									 * restart their session, otherwise start them a new session.
									 */
									$query				= "	SELECT *
															FROM `quiz_progress`
															WHERE `aquiz_id` = ".$db->qstr($RECORD_ID)."
															AND `proxy_id` = ".$db->qstr($_SESSION["details"]["id"])."
															AND `progress_value` = 'inprogress'
															ORDER BY `updated_date` ASC";
									$progress_record	= $db->GetRow($query);
									if ($progress_record) {
										$qprogress_id		= $progress_record["qprogress_id"];
										$quiz_start_time	= $progress_record["updated_date"];
									} else {
										$quiz_start_time		= time();
										$quiz_progress_array	= array (
																	"aquiz_id" => $RECORD_ID,
																	"content_id" => $quiz_record["content_id"],
																	"content_type" => $QUIZ_TYPE,
																	"quiz_id" => $quiz_record["quiz_id"],
																	"proxy_id" => $_SESSION["details"]["id"],
																	"progress_value" => "inprogress",
																	"quiz_score" => 0,
																	"quiz_value" => 0,
																	"updated_date" => $quiz_start_time,
																	"updated_by" => $_SESSION["details"]["id"]
																);
										if ($db->AutoExecute("quiz_progress", $quiz_progress_array, "INSERT"))  {
											$qprogress_id = $db->Insert_Id();
										} else {
											$ERROR++;
											$ERRORSTR[] = "Unable to create a progress entry for this quiz, it is not advisable to continue at this time. The system administrator was notified of this error; please try again later.";
	
											application_log("error", "Unable to create a quiz_progress entery when attempting complete a quiz. Database said: ".$db->ErrorMsg());
										}
									}
	
									if ($qprogress_id) {
										add_statistic("events", "quiz_view", "aquiz_id", $RECORD_ID);
	
										$quiz_timeout			= (((int) $quiz_record["quiz_timeout"]) ? ($quiz_record["quiz_timeout"] * 60) : 0);
										$quiz_end_time			= (($quiz_timeout) ? ($quiz_start_time + $quiz_timeout) : 0);
	
										/**
										 * Check to see if the release_until date is before the current end_time,
										 * if it is, shorten the $quiz_end_time to the release_until date.
										 */
										if (($quiz_end_time) && ((int) $quiz_record["release_until"]) && ($quiz_end_time > $quiz_record["release_until"])) {
											$quiz_end_time = $quiz_record["release_until"];
										}
	
										$quiz_time_remaining	= ($quiz_end_time - time());
										$ajax_load_progress		= quiz_load_progress($qprogress_id);
	
										if ($quiz_end_time) {
											?>
											<div id="display-quiz-timeout" class="display-generic">
												You have until <strong><?php echo date(DEFAULT_DATE_FORMAT, $quiz_end_time); ?></strong> to complete this quiz.
	
												<div id="quiz-timer" style="margin-top: 15px; display: none"></div>
											</div>
											<script type="text/javascript">
											function quizTimeout(timeout) {
												this.timeout = timeout;
	
												function countdown() {
													output	= new Array();
													if (this.timeout > 0) {
														if (this.timeout <= 10) {
															if ($('display-quiz-timeout').hasClassName('display-notice')) {
																$('display-quiz-timeout').removeClassName('display-notice');
															}
	
															if (!$('display-quiz-timeout').hasClassName('display-error')) {
																$('display-quiz-timeout').addClassName('display-error');
															}
	
															Effect.Pulsate('display-quiz-timeout', { pulses: 2, duration: 1 });
														} else if ((this.timeout <= 60) && ((this.timeout % 10) == 0)) {
															Effect.Pulsate('display-quiz-timeout', { pulses: 3, duration: 1, from: 0.2 });
														} else if (this.timeout <= 120) {
															if ($('display-quiz-timeout').hasClassName('display-generic')) {
																$('display-quiz-timeout').removeClassName('display-generic');
															}
	
															if (!$('display-quiz-timeout').hasClassName('display-notice')) {
																$('display-quiz-timeout').addClassName('display-notice');
															}
														}
	
														seconds	= Math.floor(this.timeout / 1) % 60;
														minutes	= Math.floor(this.timeout / 60) % 60;
														hours	= Math.floor(this.timeout / 3600) % 24;
														days	= Math.floor(this.timeout / 86400) % 86400;
	
														if (days > 0) {
															output[output.length] = days + ' day' + ((days != 1) ? 's' : '');
														}
														if (hours > 0) {
															output[output.length] = hours + ' hour' + ((hours != 1) ? 's' : '');
														}
														if (minutes > 0) {
															output[output.length] = minutes + ' minute' + ((minutes != 1) ? 's' : '');
														}
	
														output[output.length]		= ((output.length > 0) ? ' and ' : '') + seconds + ' second' + ((seconds != 1) ? 's' : '');
	
														$('quiz-timer').innerHTML	= output.join(', ');
	
														this.timeout	= (this.timeout - 1);
														countdown.timer	= setTimeout(countdown, 1000);
													} else {
														$('quiz-timer').innerHTML = 'Unfortunately your time limit has expired. There is a 30 second grace period, please submit your quiz immediately.';
													}
												}
	
												$('quiz-timer').show();
												countdown();
											}
	
											quizTimeout('<?php echo $quiz_time_remaining; ?>');
											</script>
											<?php
										}
										?>
										<div id="display-unsaved-warning" class="display-notice" style="display: none">
											<ul>
												<li><strong>Warning Unsaved Response:</strong><br />Your response to the question indicated by a yellow background was not automatically saved.</li>
											</ul>
										</div>
										<?php
										if ($ERROR) {
											echo display_error();
										}
										if ($NOTICE) {
											echo display_notice();
										}
										if (clean_input($quiz_record["quiz_notes"], array("notags", "nows")) != "") {
											echo clean_input($quiz_record["quiz_notes"], "allowedtags");
										}
										?>
										<form action="<?php echo ENTRADA_URL."/community".$quiz_record["community_url"].":".$quiz_record["page_url"]; ?>?section=attempt&amp;id=<?php echo $RECORD_ID; ?>" method="post">
										<input type="hidden" name="step" value="2" />
										<?php
										$query				= "	SELECT a.*
																FROM `quiz_questions` AS a
																WHERE a.`quiz_id` = ".$db->qstr($quiz_record["quiz_id"])."
																AND a.`question_active` = '1'
																ORDER BY a.`question_order` ASC";
										$questions			= $db->GetAll($query);
										$total_questions	= 0;
										if ($questions) {
											$total_questions = count($questions);
											?>
											<div class="quiz-questions" id="quiz-content-questions-holder">
												<ol class="questions" id="quiz-questions-list">
												<?php
												foreach ($questions as $question) {
													echo "<li id=\"question_".$question["qquestion_id"]."\"".((in_array($question["qquestion_id"], $problem_questions)) ? " class=\"notice\"" : "").">";
													echo "	<div class=\"question noneditable\">\n";
													echo "		<span id=\"question_text_".$question["qquestion_id"]."\" class=\"question\">".clean_input($question["question_text"], "trim")."</span>";
													echo "	</div>\n";
													echo "	<ul class=\"responses\">\n";
													$query		= "	SELECT a.*
																	FROM `quiz_question_responses` AS a
																	WHERE a.`qquestion_id` = ".$db->qstr($question["qquestion_id"])."
																	AND a.`response_active` = '1'
																	ORDER BY ".(($question["randomize_responses"] == 1) ? "RAND()" : "a.`response_order` ASC");
													$responses	= $db->GetAll($query);
													if ($responses) {
														foreach ($responses as $response) {
															echo "<li>";
															echo "	<input type=\"radio\" id=\"response_".$question["qquestion_id"]."_".$response["qqresponse_id"]."\" name=\"responses[".$question["qquestion_id"]."]\" value=\"".$response["qqresponse_id"]."\"".(($ajax_load_progress[$question["qquestion_id"]] == $response["qqresponse_id"]) ? " checked=\"checked\"" : "")." onclick=\"((this.checked == true) ? storeResponse('".$question["qquestion_id"]."', '".$response["qqresponse_id"]."') : false)\" />";
															echo "	<label for=\"response_".$question["qquestion_id"]."_".$response["qqresponse_id"]."\">".clean_input($response["response_text"], (($response["response_is_html"] == 1) ? "trim" : "encode"))."</label>";
															echo "</li>\n";
														}
													}
													echo "	</ul>\n";
													echo "</li>\n";
												}
												?>
												</ol>
											</div>
											<?php
										} else {
											$ERROR++;
											$ERRORSTR[] = "There are no questions currently available for under this quiz. This problem has been reported to a system administrator; please try again later.";
	
											application_log("error", "Unable to locate any questions for quiz [".$quiz_record["quiz_id"]."]. Database said: ".$db->ErrorMsg());
										}
										?>
										<div style="border-top: 2px #CCCCCC solid; margin-top: 10px; padding-top: 10px">
											<input type="button" style="float: left; margin-right: 10px" onclick="window.location = '<?php echo ENTRADA_URL."/community".$quiz_record["community_url"].":".$quiz_record["page_url"]; ?>'" value="Exit Quiz" />
											<input type="submit" style="float: right" value="Submit Quiz" />
										</div>
										<div class="clear"></div>
										</form>
										<script type="text/javascript">
										function storeResponse(qid, rid) {
											new Ajax.Request('<?php echo ENTRADA_URL."/community".$quiz_record["community_url"].":".$quiz_record["page_url"]; ?>?section=save-response', {
												method: 'post',
												parameters: { 'id' : '<?php echo $RECORD_ID; ?>', 'qid' : qid, 'rid' : rid },
												onSuccess: function(transport) {
													if (transport.responseText.match(200)) {
														$('question_' + qid).removeClassName('notice');
	
														if ($$('#quiz-questions-list li.notice').length <= 0) {
															$('display-unsaved-warning').fade({ duration: 0.5 });
														}
													} else {
														$('question_' + qid).addClassName('notice');
	
														if ($('display-unsaved-warning').style.display == 'none') {
															$('display-unsaved-warning').appear({ duration: 0.5 });
														}
													}
												},
												onError: function() {
														$('question_' + qid).addClassName('notice');
	
														if ($('display-unsaved-warning').style.display == 'none') {
															$('display-unsaved-warning').appear({ duration: 0.5 });
														}
												}
											});
										}
										</script>
										<?php
										$sidebar_html = quiz_generate_description($quiz_record["required"], $quiz_record["quiztype_code"], $quiz_record["quiz_timeout"], $total_questions, $quiz_record["quiz_attempts"], $quiz_record["timeframe"]);
										new_sidebar_item("Quiz Statement", $sidebar_html, "page-anchors", "open", "1.9");
									} else {
										$ERROR++;
										$ERRORSTR[] = "Unable to locate your progress information for this quiz at this time. The system administrator has been notified of this error; please try again later.";
	
										echo display_error();
	
										application_log("error", "Failed to locate a qprogress_id [".$qprogress_id."] (either existing or created) when attempting to complete aquiz_id [".$RECORD_ID."] (quiz_id [".$quiz_record["quiz_id"]."] / event_id [".$quiz_record["content_id"]."]).");
									}
								break;
							}
						} else {
							$ERROR++;
							$ERRORSTR[] = "You were only able to attempt this quiz a total of <strong>".(int) $quiz_record["quiz_attempts"]." time".(($quiz_record["quiz_attempts"] != 1) ? "s" : "")."</strong>, and time limit for your final attempt expired before completion.<br /><br />Please contact a teacher if you require further assistance.";
	
							echo display_error();
	
							application_log("notice", "Someone attempted to complete aquiz_id [".$RECORD_ID."] (quiz_id [".$quiz_record["quiz_id"]."] / event_id [".$quiz_record["content_id"]."]) more than the total number of possible attempts [".$quiz_record["quiz_attempts"]."] after their final attempt expired.");
						}
					} else {
						$NOTICE++;
						$NOTICESTR[] = "You were only able to attempt this quiz a total of <strong>".(int) $quiz_record["quiz_attempts"]." time".(($quiz_record["quiz_attempts"] != 1) ? "s" : "")."</strong>.<br /><br />Please contact a teacher if you require further assistance.";
	
						echo display_notice();
	
						application_log("notice", "Someone attempted to complete aquiz_id [".$RECORD_ID."] (quiz_id [".$quiz_record["quiz_id"]."] / event_id [".$quiz_record["content_id"]."]) more than the total number of possible attempts [".$quiz_record["quiz_attempts"]."].");
					}
				} else {
					$NOTICE++;
					$NOTICESTR[] = "You were only able to attempt this quiz until <strong>".date(DEFAULT_DATE_FORMAT, $quiz_record["release_until"])."</strong>.<br /><br />Please contact a teacher if you require further assistance.";
	
					echo display_notice();
	
					application_log("error", "Someone attempted to complete aquiz_id [".$RECORD_ID."] (quiz_id [".$quiz_record["quiz_id"]."] / event_id [".$quiz_record["content_id"]."] after the release date.");
				}
			} else {
				$NOTICE++;
				$NOTICESTR[] = "You cannot attempt this quiz until <strong>".date(DEFAULT_DATE_FORMAT, $quiz_record["release_date"])."</strong>.<br /><br />Please contact a teacher if you require further assistance.";
	
				echo display_notice();
	
				application_log("error", "Someone attempted to complete aquiz_id [".$RECORD_ID."] (quiz_id [".$quiz_record["quiz_id"]."] / event_id [".$quiz_record["content_id"]."] prior to the release date.");
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "In order to attempt a quiz, you must provide a valid quiz identifier.";
	
			echo display_error();
	
			application_log("error", "Failed to provide a valid aquiz_id identifer [".$RECORD_ID."] when attempting to take a quiz.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to attempt a quiz, you must provide a valid quiz identifier.";
	
		echo display_error();
	
		application_log("error", "Failed to provide an aquiz_id identifier when attempting to take a quiz.");
	}
} else {
	$ERROR++;
	$ERRORSTR[] = "You must be logged in to attempt a quiz.";

	echo display_error();

	application_log("notice", "User not logged in while attempting to take a quiz.");
}