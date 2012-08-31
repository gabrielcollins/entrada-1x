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
 * This file is used to author and share quizzes with other folks who have
 * administrative permissions in the system.
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
} elseif (!$ENTRADA_ACL->amIAllowed('quiz', 'update', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	if ($RECORD_ID) {
		$query = "	SELECT a.*
					FROM `quizzes` AS a
					WHERE a.`quiz_id` = ".$db->qstr($RECORD_ID)."
					AND a.`quiz_active` = '1'";
		$quiz_record = $db->GetRow($query);
		if ($quiz_record && $ENTRADA_ACL->amIAllowed(new QuizResource($quiz_record["quiz_id"]), "update")) {
			$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/".$MODULE."?section=edit&id=".$RECORD_ID, "title" => limit_chars($quiz_record["quiz_title"], 32));

			$PROCESSED["associated_proxy_ids"] = array();

			/**
			 * Load the rich text editor.
			 */
			load_rte();

			// Error Checking
			switch ($STEP) {
				case 2 :
					/**
					 * Required field "quiz_title" / Quiz Title.
					 */
					if ((isset($_POST["quiz_title"])) && ($tmp_input = clean_input($_POST["quiz_title"], array("notags", "trim")))) {
						$PROCESSED["quiz_title"] = $tmp_input;
					} else {
						$ERROR++;
						$ERRORSTR[] = "The <strong>Quiz Title</strong> field is required.";
					}

					/**
					 * Non-Required field "quiz_description" / Quiz Description.
					 */
					if ((isset($_POST["quiz_description"])) && ($tmp_input = clean_input($_POST["quiz_description"], array("trim", "allowedtags")))) {
						$PROCESSED["quiz_description"] = $tmp_input;
					} else {
						$PROCESSED["quiz_description"] = "";
					}

					/**
					 * Required field "associated_proxy_ids" / Quiz Authors (array of proxy ids).
					 * This is actually accomplished after the quiz is inserted below.
					 */
					if((isset($_POST["associated_proxy_ids"]))) {
						$associated_proxy_ids = explode(",", $_POST["associated_proxy_ids"]);
						foreach($associated_proxy_ids as $contact_order => $proxy_id) {
							if($proxy_id = clean_input($proxy_id, array("trim", "int"))) {
								$PROCESSED["associated_proxy_ids"][(int) $contact_order] = $proxy_id;
							}
						}
					}

					/**
					 * The current quiz author must be in the quiz author list.
					 */
					if (!in_array($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"], $PROCESSED["associated_proxy_ids"])) {
						array_unshift($PROCESSED["associated_proxy_ids"], $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]);

						$NOTICE++;
						$NOTICESTR[] = "You cannot remove yourself as a <strong>Quiz Author</strong>.";
					}

					/**
					 * Get a list of all current quiz authors, and then check to see if
					 * one quiz author is attempting to remove any other quiz authors. If
					 * they are attempting to remove an existing quiz author, then we need
					 * to check and see if that quiz author has already assigned this quiz
					 * to any of their learning events. If they have, then they cannot be
					 * removed because it will pose a data integrity problem.
					 */
					$query		= "SELECT `proxy_id` FROM `quiz_contacts` WHERE `quiz_id` = ".$db->qstr($RECORD_ID);
					$results	= $db->GetAll($query);
					if ($results) {
						foreach ($results as $result) {
							if (!in_array($result["proxy_id"], $PROCESSED["associated_proxy_ids"])) {
								$query		= "	SELECT b.`proxy_id`
												FROM `attached_quizzes` AS a
												LEFT JOIN `event_contacts` AS b
												ON a.`content_type` = 'event' 
												AND a.`content_id` = b.`event_id`
												LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS c
												ON b.`proxy_id` = c.`id`
												WHERE a.`quiz_id` = ".$db->qstr($RECORD_ID)."
												AND b.`proxy_id` = ".$db->qstr($result["proxy_id"]);
								$sresult	= $db->GetRow($query);
								if ($sresult) {
									$PROCESSED["associated_proxy_ids"][] = $result["proxy_id"];

									$NOTICE++;
									$NOTICESTR[] = "Unable to remove <strong>".html_encode(get_account_data("fullname", $result["proxy_id"]))."</strong> from the <strong>Quiz Authors</strong> section because they have already attached this quiz to one or more events or communities.";
								}
							}
						}
					}

					if (!$ERROR) {
						$PROCESSED["updated_date"] = time();
						$PROCESSED["updated_by"] = $_SESSION["details"]["id"];

						if ($db->AutoExecute("quizzes", $PROCESSED, "UPDATE", "`quiz_id` = ".$db->qstr($RECORD_ID))) {
							/**
							 * Delete existing quiz contacts, so we can re-add them.
							 */
							$query = "DELETE FROM `quiz_contacts` WHERE `quiz_id` = ".$db->qstr($RECORD_ID);
							$db->Execute($query);

							/**
							 * Add the updated quiz authors to the quiz_contacts table.
							 */
							if ((is_array($PROCESSED["associated_proxy_ids"])) && !empty($PROCESSED["associated_proxy_ids"])) {
								foreach ($PROCESSED["associated_proxy_ids"] as $proxy_id) {
									if (!$db->AutoExecute("quiz_contacts", array("quiz_id" => $RECORD_ID, "proxy_id" => $proxy_id, "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
										$ERROR++;
										$ERRORSTR[] = "There was an error while trying to attach a <strong>Quiz Author</strong> to this quiz.<br /><br />The system administrator was informed of this error; please try again later.";

										application_log("error", "Unable to insert a new quiz_contact record while adding a new quiz. Database said: ".$db->ErrorMsg());
									}
								}
							}

							$SUCCESS++;
							$SUCCESSSTR[] = "The <strong>Quiz Information</strong> section has been successfully updated.";

							application_log("success", "Quiz information for quiz_id [".$quiz_id."] was updated.");
						} else {
							$ERROR++;
							$ERRORSTR[] = "There was a problem updating this quiz. The system administrator was informed of this error; please try again later.";

							application_log("error", "There was an error updating quiz information for quiz_id [".$quiz_id."]. Database said: ".$db->ErrorMsg());
						}
					}
				break;
				case 1 :
				default :
					$PROCESSED = $quiz_record;

					$query = "SELECT `proxy_id` FROM `quiz_contacts` WHERE `quiz_id` = ".$db->qstr($RECORD_ID);
					$results = $db->GetAll($query);
					if ($results) {
						foreach ($results as $result) {
							$PROCESSED["associated_proxy_ids"][] = $result["proxy_id"];
						}
					}
				break;
			}

			// Display Content
			switch ($STEP) {
				case 2 :
				case 1 :
				default :
					if (!$ALLOW_QUESTION_MODIFICATIONS) {
						echo display_notice(array("Please note this quiz has already been attempted by at least one person, therefore the questions cannot be modified.<br /><br />If you would like to make modifications to the quiz questions, you must copy it first <em>(using the Copy Quiz button below)</em> and then make your modifications."));
					}

					$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
					$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
					?>
					<form action="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?section=edit&amp;id=<?php echo $RECORD_ID; ?>" method="post" id="editQuizForm" onsubmit="picklist_select('proxy_id')">
					<input type="hidden" name="step" value="2" />
					<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Editing Quiz">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 20%" />
						<col style="width: 77%" />
					</colgroup>
					<thead>
						<tr>
							<td colspan="3">
								<a name="quiz_information_section"></a><h2 id="quiz_information_section" title="Quiz Information">Quiz Information</h2>
								<?php
								if ($SUCCESS) {
									fade_element("out", "display-success-box");
									echo display_success();
								}

								if ($NOTICE) {
									fade_element("out", "display-notice-box", 100, 15000);
									echo display_notice();
								}

								if ($ERROR) {
									echo display_error();
								}
								?>
							</td>
						</tr>
					</thead>
					<tbody id="quiz-information">
						<tr>
							<td></td>
							<td><label for="quiz_title" class="form-required">Quiz Title</label></td>
							<td><input type="text" id="quiz_title" name="quiz_title" value="<?php echo html_encode($PROCESSED["quiz_title"]); ?>" maxlength="64" style="width: 96%" /></td>
						</tr>
						<tr>
							<td></td>
							<td style="vertical-align: top">
								<label for="quiz_description" class="form-nrequired">Quiz Description</label>
							</td>
							<td>
								<textarea id="quiz_description" name="quiz_description" style="width: 550px; height: 125px" cols="70" rows="10"><?php echo clean_input($PROCESSED["quiz_description"], array("trim", "encode")); ?></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td style="vertical-align: top">
								<label for="associated_proxy_ids" class="form-required">Quiz Authors</label>
								<div class="content-small" style="margin-top: 15px">
									<strong>Tip:</strong> Select any other individuals you would like to give access to assigning or modifying this quiz.
								</div>
							</td>
							<td style="vertical-align: top">
								<input type="text" id="author_name" name="fullname" size="30" autocomplete="off" style="width: 203px" />
								<?php
								$ONLOAD[] = "author_list = new AutoCompleteList({ type: 'author', url: '". ENTRADA_RELATIVE ."/api/personnel.api.php?type=facultyorstaff', remove_image: '". ENTRADA_RELATIVE ."/images/action-delete.gif'})";
								?>
								<div class="autocomplete" id="author_name_auto_complete"></div>
								<input type="hidden" id="associated_author" name="associated_proxy_ids" value="" />
								<input type="button" class="button-sm" id="add_associated_author" value="Add" style="vertical-align: middle" />
								<span class="content-small">(<strong>Example:</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?>)</span>
								<ul id="author_list" class="menu" style="margin-top: 15px">
									<?php
									if (is_array($PROCESSED["associated_proxy_ids"]) && !empty($PROCESSED["associated_proxy_ids"])) {
										$selected_authors = array();

										$query = "	SELECT `id` AS `proxy_id`, CONCAT_WS(', ', `lastname`, `firstname`) AS `fullname`, `organisation_id`
													FROM `".AUTH_DATABASE."`.`user_data`
													WHERE `id` IN (".implode(", ", $PROCESSED["associated_proxy_ids"]).")
													ORDER BY `lastname` ASC, `firstname` ASC";
										$results = $db->GetAll($query);
										if ($results) {
											foreach ($results as $result) {
												$selected_authors[$result["proxy_id"]] = $result;
											}

											unset($results);
										}

										foreach ($PROCESSED["associated_proxy_ids"] as $proxy_id) {
											if ($proxy_id = (int) $proxy_id) {
												if (array_key_exists($proxy_id, $selected_authors)) {
													?>
													<li class="community" id="author_<?php echo $proxy_id; ?>" style="cursor: move;"><?php echo $selected_authors[$proxy_id]["fullname"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="author_list.removeItem('<?php echo $proxy_id; ?>');" class="list-cancel-image" /></li>
													<?php
												}
											}
										}
									}
									?>
								</ul>
								<input type="hidden" id="author_ref" name="author_ref" value="" />
								<input type="hidden" id="author_id" name="author_id" value="" />
							</td>
						</tr>
						<tr>
							<td colspan="3" style="padding: 25px 0px 25px 0px">
								<div style="float: left">
									<button href="#disable-quiz-confirmation-box" id="quiz-control-disable">Disable Quiz</button>
								</div>
								<div style="float: right; text-align: right">
									<button href="#copy-quiz-confirmation-box" id="quiz-control-copy">Copy Quiz</button>
									<input type="submit" class="button" value="Save Changes" />
								</div>
								<div class="clear"></div>
							</td>
						</tr>
					</tbody>
					</table>
					</form>

					<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Quiz Questions">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 17%" />
						<col style="width: 80%" />
					</colgroup>
					<thead>
						<tr>
							<td colspan="3">
								<a name="quiz_questions_section"></a><h2 id="quiz_questions_section" title="Quiz Content Questions">Quiz Questions</h2>
							</td>
						</tr>
					</thead>
					<tbody id="quiz-content-questions">
						<tr>
							<td colspan="3">
								<?php
								if ($ALLOW_QUESTION_MODIFICATIONS) {
									?>
									<div style="padding-bottom: 2px">
										<ul class="page-action">
											<li><a href="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?section=add-question&amp;id=<?php echo $RECORD_ID; ?>">Add New Question</a></li>
										</ul>
									</div>
									<?php
								}

								$query		= "	SELECT a.*
												FROM `quiz_questions` AS a
												WHERE a.`quiz_id` = ".$db->qstr($RECORD_ID)."
												AND a.`question_active` = '1'
												ORDER BY a.`question_order` ASC";
								$questions	= $db->GetAll($query);
								if ($questions) {
									?>
									<div class="quiz-questions" id="quiz-content-questions-holder">
										<ol class="questions" id="quiz-questions-list">
										<?php
										foreach ($questions as $question) {
											echo "<li id=\"question_".$question["qquestion_id"]."\" class=\"question\" style=\"display: list-item; vertical-align: top;\">";
											echo "	<div class=\"question".((!$ALLOW_QUESTION_MODIFICATIONS) ? " noneditable" : "")."\">\n";

													if ($ALLOW_QUESTION_MODIFICATIONS) {
														echo "	<div style=\"float: right\">\n";
														echo "		<a href=\"".ENTRADA_URL."/admin/".$MODULE."?section=edit-question&amp;id=".$question["qquestion_id"]."\"><img class=\"question-controls\" src=\"".ENTRADA_URL."/images/action-edit.gif\" alt=\"Edit Question\" title=\"Edit Question\" /></a>";
														echo "		<a id=\"question_delete_".$question["qquestion_id"]."\" class=\"question-controls-delete\" href=\"#delete-question-confirmation-box\" title=\"".$question["qquestion_id"]."\"><img class=\"question-controls\" src=\"".ENTRADA_URL."/images/action-delete.gif\" alt=\"Delete Question\" title=\"Delete Question\" /></a>";
														echo "	</div>\n";
													}

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
															echo "<li class=\"".(($response["response_correct"] == 1) ? "display-correct" : "display-incorrect")."\">".clean_input($response["response_text"], (($response["response_is_html"] == 1) ? "trim" : "encode"))."</li>\n";
														}
													}
													echo "	</ul>\n";
													echo "</li>\n";
												}
												?>
											</ol>
										</div>
										<?php
										if ($ALLOW_QUESTION_MODIFICATIONS) {
											?>
											<div id="delete-question-confirmation-box" class="modal-confirmation">
												<h1>Delete Quiz <strong>Question</strong> Confirmation</h1>
												Do you really wish to remove this question from your quiz?
												<div class="body">
													<div id="delete-question-confirmation-content" class="content"></div>
												</div>
												If you confirm this action, the question will be permanently removed.
												<div class="footer">
													<input type="button" class="button" value="Close" onclick="Control.Modal.close()" style="float: left; margin: 8px 0px 4px 10px" />
													<input type="button" class="button" value="Confirm" onclick="deleteQuizQuestion(deleteQuestion_id)" style="float: right; margin: 8px 10px 4px 0px" />
												</div>
											</div>
											<script type="text/javascript" defer="defer">
												var deleteQuestion_id = 0;
												
												document.observe('dom:loaded', function() {
													try {
														Sortable.create('quiz-questions-list', { handles : $$('#quiz-questions-list div.question'), onUpdate : updateQuizQuestionOrder });
														$$('a.question-controls-delete').each(function(obj) {
															new Control.Modal(obj.id, {
																overlayOpacity:	0.75,
																closeOnClick:	'overlay',
																className:		'modal-confirmation',
																fade:			true,
																fadeDuration:	0.30,
																beforeOpen: function() {
																	deleteQuestion_id = obj.readAttribute('title');
																	$('delete-question-confirmation-content').innerHTML = $('question_text_' + obj.readAttribute('title')).innerHTML;
																},
																afterClose: function() {
																	deleteQuestion_id = 0;
																	$('delete-question-confirmation-content').innerHTML = '';
																}
															});
														});
													} catch (e) {
														clog(e); 
													}
												});

												function updateQuizQuestionOrder() {
													new Ajax.Request('<?php echo ENTRADA_URL."/admin/".$MODULE; ?>', {
														method: 'post',
														parameters: { section : 'order-question', id : <?php echo $RECORD_ID; ?>, result : Sortable.serialize('quiz-questions-list', { name : 'order' }) },
														onSuccess: function(transport) {
															if (!transport.responseText.match(200)) {
																new Effect.Highlight('quiz-content-questions-holder', { startcolor : '#FFD9D0' });
															}
														},
														onError: function() {
															new Effect.Highlight('quiz-content-questions-holder', { startcolor : '#FFD9D0' });
														}
													});
												}

												function deleteQuizQuestion(qquestion_id) {
													Control.Modal.close();
													$('question_' + qquestion_id).fade({ duration: 0.3 });

													new Ajax.Request('<?php echo ENTRADA_URL."/admin/".$MODULE; ?>', {
														method: 'post',
														parameters: { section: 'delete-question', id: qquestion_id },
														onSuccess: function(transport) {
															if (transport.responseText.match(200)) {
																$('question_' + qquestion_id).remove();

																if ($$('#quiz-questions-list li.question').length == 0) {
																	$('display-no-question-message').show();
																}
															} else {
																if ($$('#question_' + qquestion_id + ' .display-error').length == 0) {
																	var errorString	= 'Unable to delete this question at this time.<br /><br />The system administrator has been notified of this error, please try again later.';
																	var errorMsg	= new Element('div', { 'class': 'display-error' }).update(errorString);

																	$('question_' + qquestion_id).insert(errorMsg);
																}

																$('question_' + qquestion_id).appear({ duration: 0.3 });

																new Effect.Highlight('question_' + qquestion_id, { startcolor : '#FFD9D0' });
															}
														},
														onError: function() {
															$('question_' + qquestion_id).appear({ duration: 0.3 });

															new Effect.Highlight('question_' + qquestion_id, { startcolor : '#FFD9D0' });
														}
													});
												}
											</script>
										<?php
										}
									} else {
										$ONLOAD[] = "$('display-no-question-message').show()";
									}
									?>
								<div id="display-no-question-message" class="display-generic" style="display: none">
									There are currently <strong>no quiz questions</strong> associated with this quiz.<br /><br />To create questions in this quiz click the <strong>Add Question</strong> link above.
								</div>
							</td>
						</tr>
					</tbody>
					</table>
					<div id="disable-quiz-confirmation-box" class="modal-confirmation">
						<h1>Disable <strong>Quiz</strong> Confirmation</h1>
											Do you really wish to disable this quiz?
						<div class="body">
							<div id="disable-quiz-confirmation-content" class="content">
								<strong><?php echo html_encode($PROCESSED["quiz_title"]); ?></strong>
							</div>
						</div>
											If you confirm this action, this quiz will not be available to learners.
						<div class="footer">
							<input type="button" class="button" value="Close" onclick="Control.Modal.close()" style="float: left; margin: 8px 0px 4px 10px" />
							<input type="button" class="button" value="Confirm" onclick="Control.Modal.close(); window.location = '<?php echo ENTRADA_URL."/admin/".$MODULE."?section=disable&amp;id=".$RECORD_ID; ?>'" style="float: right; margin: 8px 10px 4px 0px" />
						</div>
					</div>
					<div id="copy-quiz-confirmation-box" class="modal-confirmation">
						<form action="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?section=copy&amp;id=<?php echo $RECORD_ID; ?>" method="post" id="copyQuizForm">
							<h1>Copy <strong>Quiz</strong> Confirmation</h1>
							<div class="display-generic">If you would like to create a new quiz based on the existing questions in this quiz, please provide a new title and press <strong>Copy Quiz</strong>.</div>
							<div class="body">
								<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Copying Quiz">
									<colgroup>
										<col style="width: 30%" />
										<col style="width: 70%" />
									</colgroup>
									<tbody>
										<tr>
											<td><span class="form-nrequired">Current Quiz Title</span></td>
											<td><?php echo html_encode($PROCESSED["quiz_title"]); ?></td>
										</tr>
										<tr>
											<td><label for="quiz_title" class="form-required">New Quiz Title</label></td>
											<td><input type="text" id="quiz_title" name="quiz_title" value="<?php echo html_encode($PROCESSED["quiz_title"]); ?>" maxlength="64" style="width: 96%" /></td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="footer">
								<input type="button" value="Cancel" onclick="Control.Modal.close()" style="float: left; margin: 8px 0px 4px 10px" />
								<input type="submit" value="Copy Quiz" style="float: right; margin: 8px 10px 4px 0px" />
							</div>
						</form>
					</div>
					<script type="text/javascript" defer="defer">
						document.observe('dom:loaded', function() {
							try {
								// Modal control for deleting quiz.
								new Control.Modal('quiz-control-disable', {
									overlayOpacity:	0.75,
									closeOnClick:	'overlay',
									className:		'modal-confirmation',
									fade:			true,
									fadeDuration:	0.30
								});
		
								// Modal control for copying quiz.
								new Control.Modal('quiz-control-copy', {
									overlayOpacity:	0.75,
									closeOnClick:	'overlay',
									className:		'modal-confirmation',
									fade:			true,
									fadeDuration:	0.30
								});
							} catch (e) { 
								clog(e); 
							}
						});
					</script>

					<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Learning Events">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 17%" />
						<col style="width: 80%" />
					</colgroup>
					<thead>
						<tr>
							<td colspan="3">
								<a name="learning_events_section"></a><h2 id="learning_events_section" title="Learning Events">Learning Events</h2>
							</td>
						</tr>
					</thead>
					<tbody id="learning-events">
						<tr>
							<td colspan="3">
								<?php
								/**
								 * If there are no questions in this quiz, then
								 * a generic notice is spit out that gives the
								 * user information on when they can assign this
								 * quiz to a learning event.
								 */
								if (!(int) count($questions)) {
									?>
									<div class="display-generic">
										Once you create questions for this quiz you will be able to assign it to learning events you are teaching.
									</div>
									<?php
								} else {
									?>
									<div style="margin-bottom: 10px">
										<ul class="page-action">
											<li><a href="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?section=attach&amp;id=<?php echo $RECORD_ID; ?>">Attach To Learning Event</a></li>
										</ul>
									</div>
									<?php
									$query		= "	SELECT a.*, b.`event_id`, b.`course_id`, b.`eventtype_id`, b.`event_title`, b.`event_start`, b.`event_duration`, c.`course_name`, c.`course_code`
													FROM `attached_quizzes` AS a
													JOIN `events` AS b
													ON a.`content_type` = 'event' 
													AND	b.`event_id` = a.`content_id`
													JOIN `courses` AS c
													ON c.`course_id` = b.`course_id`
													WHERE a.`quiz_id` = ".$db->qstr($RECORD_ID)."
													AND c.`course_active` = '1'
													ORDER BY b.`event_start` DESC";
									$results	= $db->GetAll($query);
									if($results) {
										?>
										<table class="tableList" cellspacing="0" summary="List of Learning Events">
										<colgroup>
											<col class="modified" />
											<col class="date" />
											<col class="title" />
											<col class="title" />
											<col class="completed" />
										</colgroup>
										<thead>
											<tr>
												<td class="modified">&nbsp;</td>
												<td class="date sortedDESC" style="border-left: 1px solid #999999"><div class="noLink">Date &amp; Time</div></td>
												<td class="title">Event Title</td>
												<td class="title">Quiz Title</td>
												<td class="completed">Completed</td>
											</tr>
										</thead>
										<tbody>
											<?php
											foreach($results as $result) {
												$url = ENTRADA_URL."/admin/events?section=content&id=".$result["event_id"];
												$completed_attempts = $db->GetOne("SELECT COUNT(DISTINCT `proxy_id`) FROM `quiz_progress` WHERE `progress_value` = 'complete' AND `aquiz_id` = ".$db->qstr($result["aquiz_id"]));
												
												echo "<tr id=\"event-".$result["event_id"]."\" class=\"event\">\n";
												echo "	<td class=\"modified\">\n";
												if ($completed_attempts > 0) {
													echo "	<a href=\"".ENTRADA_URL."/admin/quizzes?section=results&amp;id=".$result["aquiz_id"]."\"><img src=\"".ENTRADA_URL."/images/view-stats.gif\" width=\"16\" height=\"16\" alt=\"View results of ".html_encode($result["quiz_title"])."\" title=\"View results of ".html_encode($result["quiz_title"])."\" style=\"vertical-align: middle\" border=\"0\" /></a>\n";
												} else {
													echo "	<img src=\"".ENTRADA_URL."/images/view-stats-disabled.gif\" width=\"16\" height=\"16\" alt=\"No completed quizzes at this time.\" title=\"No completed quizzes at this time.\" style=\"vertical-align: middle\" border=\"0\" />\n";
												}
												echo "	</td>\n";
												echo "	<td class=\"date\"><a href=\"".$url."\" title=\"Event Date\">".date(DEFAULT_DATE_FORMAT, $result["event_start"])."</a></td>\n";
												echo "	<td class=\"title\"><a href=\"".$url."\" title=\"Event Title: ".html_encode($result["event_title"])."\">".html_encode($result["event_title"])."</a></td>\n";
												echo "	<td class=\"title\"><a href=\"".$url."\" title=\"Quiz Title: ".html_encode($result["quiz_title"])."\">".html_encode($result["quiz_title"])."</a></td>\n";
												echo "	<td class=\"completed\">".(int) $completed_attempts."</td>\n";
												echo "</tr>\n";
											}
											?>
										</tbody>
										</table>
										<?php
									} else {
										$NOTICE++;
										$NOTICESTR[] = "This quiz is not currently attached to any learning events.<br /><br />To add this quiz to an event you are teaching, click the <strong>Attach To Learning Event</strong> link above.";

										echo display_notice();
									}
								}
								?>
							</td>
						</tr>
					</tbody>
					<tbody id="community-pages">
						<tr>
							<td colspan="3">
								&nbsp;
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<a name="community_pages_section"></a><h2 id="community_pages_section" title="Community Pages">Community Pages</h2>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<?php
								/**
								 * If there are no questions in this quiz, then
								 * a generic notice is spit out that gives the
								 * user information on when they can assign this
								 * quiz to a learning event.
								 */
								if (!(int) count($questions)) {
									?>
									<div class="display-generic">
										Once you create questions for this quiz you will be able to assign it to pages in communities you administrate.
									</div>
									<?php
								} else {
									?>
									<div style="margin-bottom: 10px">
										<ul class="page-action">
											<li><a href="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?section=attach&amp;community=true&amp;id=<?php echo $RECORD_ID; ?>">Attach To Community Page</a></li>
										</ul>
									</div>
									<?php
									$query		= "	SELECT a.*, b.`community_id`, b.`community_url`, b.`community_title`, CONCAT('[', b.`community_title`, '] ', bp.`menu_title`) AS `page_title`, bp.`page_url`
													FROM `attached_quizzes` AS a
													JOIN `communities` AS b
													ON a.`content_type` = 'community_page' 
													JOIN `community_pages` AS bp
													ON a.`content_type` = 'community_page' 
													AND	bp.`cpage_id` = a.`content_id`
													AND bp.`community_id` = b.`community_id`
													WHERE a.`quiz_id` = ".$db->qstr($RECORD_ID)."
													AND b.`community_active` = '1'
													AND bp.`page_active` = '1'
													ORDER BY b.`community_title` ASC";
									$results	= $db->GetAll($query);
									if($results) {
										?>
										<table class="tableList" cellspacing="0" summary="List of Community Pages">
										<colgroup>
											<col class="modified" />
											<col class="title" />
											<col class="title" />
											<col class="completed" />
										</colgroup>
										<thead>
											<tr>
												<td class="modified">&nbsp;</td>
												<td class="title sortedASC">Community Page</td>
												<td class="title">Quiz Title</td>
												<td class="completed">Completed</td>
											</tr>
										</thead>
										<tbody>
											<?php
											foreach($results as $result) {
												$url = ENTRADA_URL."/community".$result["community_url"].":".$result["page_url"];
												$completed_attempts = $db->GetOne("SELECT COUNT(DISTINCT `proxy_id`) FROM `quiz_progress` WHERE `progress_value` = 'complete' AND `aquiz_id` = ".$db->qstr($result["aquiz_id"]));

												echo "<tr id=\"community-page-".$result["cpage_id"]."\" class=\"community-page\">\n";
												echo "	<td class=\"modified\">\n";
												if ($completed_attempts > 0) {
													echo "	<a href=\"".ENTRADA_URL."/admin/quizzes?section=results&amp;community=true&amp;id=".$result["aquiz_id"]."\"><img src=\"".ENTRADA_URL."/images/view-stats.gif\" width=\"16\" height=\"16\" alt=\"View results of ".html_encode($result["quiz_title"])."\" title=\"View results of ".html_encode($result["quiz_title"])."\" style=\"vertical-align: middle\" border=\"0\" /></a>\n";
												} else {
													echo "	<img src=\"".ENTRADA_URL."/images/view-stats-disabled.gif\" width=\"16\" height=\"16\" alt=\"No completed quizzes at this time.\" title=\"No completed quizzes at this time.\" style=\"vertical-align: middle\" border=\"0\" />\n";
												}
												echo "	</td>\n";
												echo "	<td class=\"title\"><a href=\"".$url."\" title=\"Community Page: ".html_encode($result["page_title"])."\">".html_encode($result["page_title"])."</a></td>\n";
												echo "	<td class=\"title\"><a href=\"".$url."\" title=\"Quiz Title: ".html_encode($result["quiz_title"])."\">".html_encode($result["quiz_title"])."</a></td>\n";
												echo "	<td class=\"completed\">".(int) $completed_attempts."</td>\n";
												echo "</tr>\n";
											}
											?>
										</tbody>
										</table>
										<?php
									} else {
										$NOTICESTR = array();
										$NOTICE = 1;
										$NOTICESTR[] = "This quiz is not currently attached to any community pages.<br /><br />To add this quiz to an page you are have administrative rights to, click the <strong>Attach To Community Page</strong> link above.";

										echo display_notice();
									}
								}
								?>
							</td>
						</tr>
					</tbody>
					</table>
					<?php
					/**
					 * Sidebar item that will provide the links to the different sections within this page.
					 */
					$sidebar_html  = "<ul class=\"menu\">\n";
					$sidebar_html .= "	<li class=\"link\"><a href=\"#quiz_information_section\" onclick=\"$('quiz_information_section').scrollTo(); return false;\" title=\"Quiz Information\">Quiz Information</a></li>\n";
					$sidebar_html .= "	<li class=\"link\"><a href=\"#quiz_questions_section\" onclick=\"$('quiz_questions_section').scrollTo(); return false;\" title=\"Quiz Questions\">Quiz Questions</a></li>\n";
					$sidebar_html .= "	<li class=\"link\"><a href=\"#learning_events_section\" onclick=\"$('learning_events_section').scrollTo(); return false;\" title=\"Learning Events\">Learning Events</a></li>\n";
					$sidebar_html .= "</ul>\n";

					new_sidebar_item("Page Anchors", $sidebar_html, "page-anchors", "open", "1.9");
				break;
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "In order to edit a quiz, you must provide a valid quiz identifier.";

			echo display_error();

			application_log("notice", "Failed to provide a valid quiz identifer [".$RECORD_ID."] when attempting to edit a quiz.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to edit a quiz, you must provide a quiz identifier.";

		echo display_error();

		application_log("notice", "Failed to provide a quiz identifier to edit a quiz.");
	}
}