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
 * This file is used to author and copy an existing quiz.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_QUIZZES"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('quiz', 'create', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	if ($RECORD_ID) {
		$query			= "	SELECT a.*
							FROM `quizzes` AS a
							WHERE a.`quiz_id` = ".$db->qstr($RECORD_ID)."
							AND a.`quiz_active` = '1'";
		$quiz_record	= $db->GetRow($query);
		if ($quiz_record && $ENTRADA_ACL->amIAllowed(new QuizResource($quiz_record["quiz_id"]), 'update')) {
			$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/".$MODULE."?section=edit&id=".$RECORD_ID, "title" => limit_chars($quiz_record["quiz_title"], 32));
			$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/".$MODULE."?section=copy&id=".$RECORD_ID, "title" => "Copying Quiz");

			/**
			 * Required field "quiz_title" / Quiz Title.
			 */
			if ((isset($_POST["quiz_title"])) && ($tmp_input = clean_input($_POST["quiz_title"], array("notags", "trim")))) {
				$PROCESSED["quiz_title"]		= $tmp_input;
				$PROCESSED["quiz_description"]	= $quiz_record["quiz_description"];
				$PROCESSED["quiz_active"]		= 1;
				$PROCESSED["updated_date"]		= time();
				$PROCESSED["updated_by"]		= $ENTRADA_USER->getID();

				if($ENTRADA_ACL->amIAllowed('quiz', 'create')) {
					if ($db->AutoExecute("quizzes", $PROCESSED, "INSERT")) {
						if ($new_quiz_id = $db->Insert_Id()) {
							$query = "	INSERT INTO `quiz_contacts`
										SELECT NULL, '".$new_quiz_id."', `proxy_id`, '".time()."', ".$db->qstr($ENTRADA_USER->getID())."
										FROM `quiz_contacts`
										WHERE `quiz_id` = ".$db->qstr($RECORD_ID);
							if (($db->Execute($query)) && ($db->Affected_Rows() > 0)) {
								$query		= "	SELECT *
												FROM `quiz_questions`
												WHERE `quiz_id` = ".$db->qstr($RECORD_ID)."
												AND `question_active` = '1'";
								$questions	= $db->GetAll($query);
								if ($questions) {
									$new_qquestion_ids = array();

									foreach ($questions as $question) {
										$query = "	INSERT INTO `quiz_questions` VALUES (
														NULL,
														'".$new_quiz_id."',
														".$db->qstr($question["questiontype_id"]).",
														".$db->qstr($question["question_text"]).",
														".$db->qstr($question["question_points"]).",
														".$db->qstr($question["question_order"]).",
														'1',
														".$db->qstr($question["randomize_responses"])."
													)";
										if (($db->Execute($query)) && ($new_qquestion_id = $db->Insert_Id())) {
											$query = "	INSERT INTO `quiz_question_responses`
														SELECT NULL, '".$new_qquestion_id."', `response_text`, `response_order`, `response_correct`, `response_is_html`, `response_feedback`, `response_active`
														FROM `quiz_question_responses`
														WHERE `qquestion_id` = ".$db->qstr($question["qquestion_id"]);
											if (($db->Execute($query)) && ($db->Affected_Rows() > 0)) {
												/**
												 * Add this new qquestion_id to the $new_qquestion_ids array.
												 */
												$new_qquestion_ids[] = $new_qquestion_id;
											} else {
												$ERROR++;

												application_log("error", "Unable to insert new quiz_question_responses record when attempting to copy responses for qquestion_id [".$question["qquestion_id"]."] from quiz_id [".$RECORD_ID."]. Database said: ".$db->ErrorMsg());
											}
										} else {
											$ERROR++;

											application_log("error", "Unable to insert new quiz_questions record when attempting to copy quiz_id [".$RECORD_ID."]. Database said: ".$db->ErrorMsg());
										}
									}

									if ($ERROR) {
										if (count($new_qquestion_ids) > 0) {
											$query = "DELETE FROM `quiz_question_responses` WHERE `qquestion_id` IN (".implode(", ", $new_qquestion_ids).")";
											$db->Execute($query);
										}

										$query = "DELETE FROM `quiz_questions` WHERE `quiz_id` = ".$db->qstr($new_quiz_id);
										$db->Execute($query);

										$query = "DELETE FROM `quiz_contacts` WHERE `quiz_id` = ".$db->qstr($new_quiz_id);
										$db->Execute($query);

										$query = "DELETE FROM `quizzes` WHERE `quiz_id` = ".$db->qstr($new_quiz_id);
										$db->Execute($query);

										$ERROR++;
										$ERRORSTR[] = "There was a problem creating the new quiz at this time. The system administrator was informed of this error; please try again later.";
									}
								}
							} else {
								$query = "DELETE FROM `quizzes` WHERE `quiz_id` = ".$db->qstr($new_quiz_id);
								$db->Execute($query);

								$ERROR++;
								$ERRORSTR[] = "Unable to copy the existing quiz authors from the original quiz. The system administrator was informed of this error; please try again later.";

								application_log("error", "Unable to copy any quiz authors when attempting to copy quiz_id [".$RECORD_ID."] authors to quiz_id [".$new_quiz_id."]. Database said: ".$db->ErrorMsg());
							}
						} else {
							$ERROR++;
							$ERRORSTR[] = "There was a problem creating the new quiz at this time. The system administrator was informed of this error; please try again later.";

							application_log("error", "There was an error inserting a copied quiz, as there was no new_quiz_id available from Insert_Id(). Database said: ".$db->ErrorMsg());
						}
					} else {
						$ERROR++;
						$ERRORSTR[] = "There was a problem creating the new quiz at this time. The system administrator was informed of this error; please try again later.";

						application_log("error", "There was an error inserting a new copied quiz. Database said: ".$db->ErrorMsg());
					}
				} else {
					$ERROR++;
					$ERRORSTR[] = "You do not have permission to create a new quiz with these parameters. ";

					application_log("error", "There was an error inserting a new copied quiz due to lack of permissions");
				}

			} else {
				$ERROR++;
				$ERRORSTR[] = "Unable to copy this quiz because the <strong>New Quiz Title</strong> field is required, and was not provided.";
			}



			if (!$ERROR) {
				$url = ENTRADA_URL."/admin/".$MODULE."?section=edit&id=".$new_quiz_id;

				$SUCCESS++;
				$SUCCESSSTR[] = "You have successfully created a new quiz (<strong>".html_encode($PROCESSED["quiz_title"])."</strong>) based on <strong>".html_encode($quiz_record["quiz_title"])."</strong>.<br /><br />You will now be redirected to the <strong>newly copied</strong> quiz; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";

				application_log("success", "Original quiz_id [".$RECORD_ID."] has successfully been copied to new quiz_id [".$new_quiz_id."].");

				echo display_success();
			} else {
				$url = ENTRADA_URL."/admin/".$MODULE."?section=edit&id=".$quiz_record["quiz_id"];

				$ERRORSTR[(count($ERRORSTR) - 1)] .= "<br /><br />You will now be redirected to the <strong>original</strong> quiz; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
				
				echo display_error();
			}

			$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";
		} else {
			$ERROR++;
			$ERRORSTR[] = "In order to copy a quiz, you must provide a valid quiz identifier.";

			echo display_error();

			application_log("notice", "Failed to provide a valid quiz identifer [".$RECORD_ID."] when attempting to copy a quiz.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to copy a quiz, you must provide a quiz identifier.";

		echo display_error();

		application_log("notice", "Failed to provide a quiz identifier to copy a quiz.");
	}
}
