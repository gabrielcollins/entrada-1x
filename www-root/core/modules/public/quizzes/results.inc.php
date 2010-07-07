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

if((!defined("PARENT_INCLUDED")) || (!defined("IN_PUBLIC_QUIZZES"))) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
}

if ($RECORD_ID) {
	$query			= "	SELECT a.`quiz_score`, a.`quiz_value`, a.`proxy_id`, b.*, d.`event_title`, d.`event_start`, d.`event_finish`, d.`release_date` AS `event_release_date`, d.`release_until` AS `event_release_until`, d.`course_id`, e.`organisation_id`, f.`quiztype_code`
						FROM `event_quiz_progress` AS a
						LEFT JOIN `event_quizzes` AS b
						ON b.`equiz_id` = a.`equiz_id`
						LEFT JOIN `quizzes` AS c
						ON c.`quiz_id` = a.`quiz_id`
						LEFT JOIN `events` AS d
						ON d.`event_id` = a.`event_id`
						LEFT JOIN `courses` AS e
						ON e.`course_id` = a.`event_id`
						LEFT JOIN `quizzes_lu_quiztypes` AS f
						ON f.`quiztype_id` = b.`quiztype_id`
						WHERE a.`eqprogress_id` = ".$db->qstr($RECORD_ID)."
						AND c.`quiz_active` = '1'
						AND e.`course_active` = '1'";
	$quiz_record	= $db->GetRow($query);
	if ($quiz_record) {
		$is_administrator = false;
		
		if ($ENTRADA_ACL->amIAllowed(new EventContentResource($quiz_record["event_id"], $quiz_record["course_id"], $quiz_record["organisation_id"]), "update")) {
			$is_administrator	= true;
		}

		if (($is_administrator) || ($quiz_record["proxy_id"] == $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])) {
			$respondent_name = get_account_data("firstlast", $quiz_record["proxy_id"]);

			$BREADCRUMB[]	= array("url" => ENTRADA_URL."/events?id=".$quiz_record["event_id"], "title" => limit_chars($quiz_record["event_title"], 32));
			$BREADCRUMB[]	= array("url" => ENTRADA_URL."/".$MODULE."?section=results&id=".$RECORD_ID, "title" => limit_chars($quiz_record["quiz_title"], 32));

			if ($is_administrator) {
				$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/".$MODULE."?section=results&id=".$quiz_record["equiz_id"], "title" => "Quiz Results");
				$BREADCRUMB[] = array("url" => "", "title" => $respondent_name);
			}

			/**
			 * Providing there is no expiry date, or the expiry date is in the
			 * future on both the quiz and the event, allow them to continue.
			 */
			if (($is_administrator) || ($quiz_record["quiztype_code"] == "immediate") || (($quiz_record["quiztype_code"] == "delayed") && (((int) $quiz_record["release_until"] === 0) || ($quiz_record["release_until"] <= time())))) {
				$quiz_score = $quiz_record["quiz_score"];
				$quiz_value	= $quiz_record["quiz_value"];

				$query		= "	SELECT a.*
								FROM `quiz_questions` AS a
								WHERE a.`quiz_id` = ".$db->qstr($quiz_record["quiz_id"])."
								ORDER BY a.`question_order` ASC";
				$questions	= $db->GetAll($query);
				if ($questions) {
					$PROCESSED = quiz_load_progress($RECORD_ID);

					/**
					 * Calculates the percentage for display purposes.
					 */
					$quiz_percentage = ((round(($quiz_score / $quiz_value), 2)) * 100);

					if ($quiz_percentage >= 70) {
						$display_class	= "success";
					} elseif (($quiz_percentage > 50) && ($quiz_percentage < 70)) {
						$display_class	= "notice";
					} else {
						$display_class	= "error";
					}

					echo "<h1>".html_encode($quiz_record["quiz_title"])."</h1>";
					?>
					<div class="display-<?php echo $display_class; ?>">
							<h3><?php echo html_encode($respondent_name); ?> Quiz Results:</h3>
						<div style="font-size: 200%; margin-bottom: 10px">
							You got <strong><?php echo $quiz_score; ?>/<?php echo $quiz_value; ?></strong> on this quiz, which is <strong><?php echo $quiz_percentage; ?>%</strong>.
						</div>
					</div>

					<div class="quiz-questions" id="quiz-content-questions-holder">
						<ol class="questions" id="quiz-questions-list">
						<?php
						foreach ($questions as $question) {
							$question_correct	= false;
							$question_feedback	= "";

							echo "<li id=\"question_".$question["qquestion_id"]."\">";
							echo "	<div class=\"question noneditable\">\n";
							echo "		<span id=\"question_text_".$question["qquestion_id"]."\" class=\"question\">".clean_input($question["question_text"], "allowedtags")."</span>";
							echo "	</div>\n";
							echo "	<ul class=\"responses\">\n";
							$query		= "	SELECT a.*
											FROM `quiz_question_responses` AS a
											WHERE a.`qquestion_id` = ".$db->qstr($question["qquestion_id"])."
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
										} else {
											$response_correct	= false;
										}

										if ($tmp_input = clean_input($response["response_feedback"], array("notags", "trim"))) {
											$question_feedback = $tmp_input;
										}
									}

									echo "<li".(($response_selected) ? " class=\"selected ".(($response_correct) ? "correct" : "incorrect")."\"" : (($response["response_correct"] == 1) ? " class=\"correct\"" : "")).">";
									echo	clean_input($response["response_text"], (($response["response_is_html"] == 1) ? "allowedtags" : "encode"));

									if ($response_selected) {
										if ($response["response_correct"] == 1) {
											echo "<img class=\"question-response-indicator\" src=\"".ENTRADA_URL."/images/question-response-correct.gif\" alt=\"Correct\" title=\"Correct\" />";
										} else {
											echo "<img class=\"question-response-indicator\" src=\"".ENTRADA_URL."/images/question-response-incorrect.gif\" alt=\"Incorrect\" title=\"Incorrect\" />";
										}
									}
									echo "</li>\n";
								}
							}
							echo "	</ul>\n";
							if ($question_feedback != "") {
								echo "	<div class=\"display-generic\" style=\"margin-left: 65px; padding: 10px\">\n";
								echo "		<strong>Question Feedback:</strong><br />";
								echo		$question_feedback;
								echo "	</div>";
							}
							echo "</li>\n";
						}
						?>
						</ol>
					</div>
					<div style="border-top: 2px #CCCCCC solid; margin-top: 10px; padding-top: 10px">
						<span class="content-small">Reference ID: <?php echo $RECORD_ID; ?></span>
						<button style="float: right" onclick="window.location = '<?php echo ENTRADA_URL; ?>/events?id=<?php echo $quiz_record["event_id"]; ?>'">Exit Quiz</button>
					</div>
					<div class="clear"></div>
					<?php
				} else {
					application_log("error", "Unable to locate any questions for quiz [".$quiz_record["quiz_id"]."]. Database said: ".$db->ErrorMsg());

					$ERROR++;
					$ERRORSTR[] = "There are no questions currently available for under this quiz. This problem has been reported to a system administrator; please try again later.";

					echo display_error();
				}
			} else {
				$NOTICE++;
				$NOTICESTR[] = "You will not be able to review your quiz results until after <strong>".date(DEFAULT_DATE_FORMAT, $quiz_record["release_until"])."</strong>.<br /><br />Please contact a teacher if you require further assistance.";

				echo display_notice();

				application_log("error", "Someone attempted to review results of eqprogress_id [".$RECORD_ID."] (quiz_id [".$quiz_record["quiz_id"]."] / event_id [".$quiz_record["event_id"]."]) after the release date.");
			}
		} else {
			application_log("error", "Someone attempted to review results of eprogress_id [".$RECORD_ID."] that they were not entitled to view.");

			header("Location: ".ENTRADA_URL."/events?id=".$quiz_record["event_id"]);
			exit;
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to review a quiz, you must provide a valid attempt identifier.";

		echo display_error();

		application_log("error", "Failed to provide a valid eqprogress_id [".$RECORD_ID."] when attempting to view quiz results.");
	}
} else {
	$ERROR++;
	$ERRORSTR[] = "In order to review a quiz, you must provide a valid attempt identifier.";

	echo display_error();

	application_log("error", "Failed to provide an eqprogress_id [".$RECORD_ID."] when attempting to view quiz results.");
}