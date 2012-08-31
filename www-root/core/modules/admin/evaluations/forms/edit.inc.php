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
 * This file is used to author evaluation forms.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVALUATIONS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("evaluationform", "update", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	if ($FORM_ID) {
		$query = "	SELECT a.*
					FROM `evaluation_forms` AS a
					WHERE `eform_id` = ".$db->qstr($FORM_ID)."
					AND `form_active` = '1'";
		$form_record = $db->GetRow($query);
		if ($form_record && $ENTRADA_ACL->amIAllowed(new EvaluationFormResource($form_record["form_id"]), "update")) {
			$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/evaluations/forms?section=edit&id=".$FORM_ID, "title" => limit_chars($form_record["form_title"], 32));

			/**
			 * Load the rich text editor.
			 */
			load_rte();

			// Error Checking
			switch ($STEP) {
				case 2 :
					/**
					 * Required field "target_id" / Form Type.
					 */
					if (isset($_POST["target_id"]) && ($tmp_input = clean_input($_POST["target_id"], "int")) && array_key_exists($tmp_input, $EVALUATION_TARGETS)) {
						$PROCESSED["target_id"] = $tmp_input;
					} else {
						$ERROR++;
						$ERRORSTR[] = "The <strong>Form Type</strong> field is required.";
					}

					/**
					 * Required field "form_title" / Form Title.
					 */
					if ((isset($_POST["form_title"])) && ($tmp_input = clean_input($_POST["form_title"], array("notags", "trim")))) {
						$PROCESSED["form_title"] = $tmp_input;
					} else {
						$ERROR++;
						$ERRORSTR[] = "The <strong>Form Title</strong> field is required.";
					}

					/**
					 * Non-Required field "form_description" / Form Description.
					 */
					if ((isset($_POST["form_description"])) && ($tmp_input = clean_input($_POST["form_description"], array("trim", "allowedtags")))) {
						$PROCESSED["form_description"] = $tmp_input;
					} else {
						$PROCESSED["form_description"] = "";
					}

					if (!$ERROR) {
						$PROCESSED["updated_date"] = time();
						$PROCESSED["updated_by"] = $_SESSION["details"]["id"];

						if ($db->AutoExecute("evaluation_forms", $PROCESSED, "UPDATE", "`eform_id` = ".$db->qstr($FORM_ID))) {
							$SUCCESS++;
							$SUCCESSSTR[] = "The <strong>Form Information</strong> section has been successfully updated.";

							application_log("success", "Form information for form_id [".$FORM_ID."] was updated.");
						} else {
							$ERROR++;
							$ERRORSTR[] = "There was a problem updating this evaluation form. The system administrator was informed of this error; please try again later.";

							application_log("error", "There was an error updating evaluation form information for form_id [".$FORM_ID."]. Database said: ".$db->ErrorMsg());
						}
					}
				break;
				case 1 :
				default :
					$PROCESSED = $form_record;
				break;
			}

			// Display Content
			switch ($STEP) {
				case 2 :
				case 1 :
				default :
					if (!$ALLOW_QUESTION_MODIFICATIONS) {
						echo display_notice(array("Please note this evaluation form has alreay been used in an evaluation, therefore the questions cannot be modified.<br /><br />If you would like to make modifications to the form you must copy it first <em>(using the Copy Form button below)</em> and then make your modifications."));
					}

					$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
					$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
					?>
					<form action="<?php echo ENTRADA_URL; ?>/admin/evaluations/forms?section=edit&amp;id=<?php echo $FORM_ID; ?>" method="post" id="editEvaluationFormForm">
					<input type="hidden" name="step" value="2" />
					<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Editing Evaluation Form">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 20%" />
						<col style="width: 77%" />
					</colgroup>
					<thead>
						<tr>
							<td colspan="3">
								<a name="form_information_section"></a><h2 id="form_information_section" title="Evaluation Form Information">Evaluation Form Information</h2>
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
					<tbody id="form-information">
						<tr>
							<td></td>
							<td><label for="target_id" class="form-required">Form Type</label></td>
							<td>
								<select id="target_id" name="target_id" style="width: 250px;">
									<option value="0">-- Select Form Type --</option>
									<?php
									if ($EVALUATION_TARGETS && is_array($EVALUATION_TARGETS) && !empty($EVALUATION_TARGETS)) {
										foreach ($EVALUATION_TARGETS as $target) {
											echo "<option value=\"".$target["target_id"]."\"".(($PROCESSED["target_id"] == $target["target_id"]) ? " selected=\"selected\"" : "").">".html_encode($target["target_title"])."</option>";
										}
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr>
							<td></td>
							<td><label for="form_title" class="form-required">Form Title</label></td>
							<td><input type="text" id="form_title" name="form_title" value="<?php echo html_encode($PROCESSED["form_title"]); ?>" maxlength="64" style="width: 95%" /></td>
						</tr>
						<tr>
							<td></td>
							<td style="vertical-align: top">
								<label for="form_description" class="form-nrequired">Form Description</label>
							</td>
							<td>
								<textarea id="form_description" name="form_description" style="width: 550px; height: 125px" cols="70" rows="10"><?php echo clean_input($PROCESSED["form_description"], array("trim", "encode")); ?></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="3" style="padding: 25px 0px 25px 0px">
								<div style="float: left">
									<button href="#disable-form-confirmation-box" id="form-control-disable">Disable Form</button>
								</div>
								<div style="float: right; text-align: right">
									<button href="#copy-form-confirmation-box" id="form-control-copy">Copy Form</button>
									<input type="submit" class="button" value="Save Changes" />
								</div>
								<div class="clear"></div>
							</td>
						</tr>
					</tbody>
					</table>
					</form>

					<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Evaluation Form Questions">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 17%" />
						<col style="width: 80%" />
					</colgroup>
					<thead>
						<tr>
							<td colspan="3">
								<a name="form_questions_section"></a><h2 id="form_questions_section" title="Evaluation Form Questions">Evaluation Form Questions</h2>
							</td>
						</tr>
					</thead>
					<tbody id="evaluation-form-questions">
						<tr>
							<td colspan="3">
								<?php
								if ($ALLOW_QUESTION_MODIFICATIONS) {
									?>
									<div style="padding-bottom: 2px">
										<ul class="page-action">
											<li><a href="<?php echo ENTRADA_URL; ?>/admin/evaluations/forms/questions?id=<?php echo $FORM_ID; ?>&amp;section=add">Add New Question</a></li>
										</ul>
									</div>
									<?php
								}

								$query = "	SELECT a.*
											FROM `evaluation_form_questions` AS a
											WHERE a.`eform_id` = ".$db->qstr($FORM_ID)."
											ORDER BY a.`question_order` ASC";
								$questions = $db->GetAll($query);
								if ($questions) {
									?>
									<div id="form-content-questions-holder">
										<ol id="form-questions-list">
										<?php
										foreach ($questions as $key => $question) {
											$question_number = ($key + 1);

											echo "<li id=\"question_".$question["efquestion_id"]."\"".(($key % 2) ? " class=\"odd\"" : "").">";
											if ($ALLOW_QUESTION_MODIFICATIONS) {
												echo "<div class=\"controls\">\n";
												echo "	<a href=\"".ENTRADA_URL."/admin/evaluations/forms/questions?id=".$FORM_ID."&amp;section=edit&amp;record=".$question["efquestion_id"]."\"><img class=\"question-controls\" src=\"".ENTRADA_URL."/images/action-edit.gif\" alt=\"Edit Question\" title=\"Edit Question\" /></a>";
												echo "	<a id=\"question_delete_".$question["efquestion_id"]."\" class=\"question-controls-delete\" href=\"#delete-question-confirmation-box\" title=\"".$question["efquestion_id"]."\"><img class=\"question-controls\" src=\"".ENTRADA_URL."/images/action-delete.gif\" alt=\"Delete Question\" title=\"Delete Question\" /></a>";
												echo "</div>\n";
											}
											echo "	<div id=\"question_text_".$question["efquestion_id"]."\" class=\"question\">\n";
											echo "		".clean_input($question["question_text"], "specialchars");
											echo "	</div>\n";
											echo "	<div class=\"responses\">\n";
											$query = "	SELECT a.*
														FROM `evaluation_form_responses` AS a
														WHERE a.`efquestion_id` = ".$db->qstr($question["efquestion_id"])."
														ORDER BY a.`response_order` ASC";
											$responses = $db->GetAll($query);
											if ($responses) {
												$response_width = floor(100 / count($responses));

												foreach ($responses as $response) {
													echo "<div style=\"width: ".$response_width."%\">\n";
													echo "	<label for=\"".$form_record["eform_id"]."_".$response["efquestion_id"]."_".$response["efresponse_id"]."\">".clean_input($response["response_text"], "specialchars")."</label><br />";
													echo "	<input type=\"radio\" style=\"margin-top: 5px\" id=\"".$form_record["eform_id"]."_".$response["efquestion_id"]."_".$response["efresponse_id"]."\" name=\"form[".$form_record["eform_id"]."][".$response["efquestion_id"]."]\" />";
													echo "</div>\n";
												}
											}
											echo "	</div>\n";
											echo "	<div class=\"clear\"></div>";
											echo "	<div class=\"comments\">";
											echo "	<label for=\"".$form_record["eform_id"]."_".$response["efquestion_id"]."_".$response["efresponse_id"]."_comment\" class=\"form-nrequired\">Comments:</label>";
											echo "	<textarea id=\"".$form_record["eform_id"]."_".$response["efquestion_id"]."_".$response["efresponse_id"]."_comment\" class=\"expandable\" style=\"width:95%; height:40px;\"></textarea>";
											echo "	</div>";
											echo "</li>\n";
										}
										?>
										</ol>
									</div>
									<?php
									if ($ALLOW_QUESTION_MODIFICATIONS) {
										?>
										<div id="delete-question-confirmation-box" class="modal-confirmation">
											<h1>Delete Form <strong>Question</strong> Confirmation</h1>
											Do you really wish to remove this question from your evaluation form?
											<div class="body">
												<div id="delete-question-confirmation-content" class="content"></div>
											</div>
											If you confirm this action, the question will be permanently removed.
											<div class="footer">
												<input type="button" class="button" value="Close" onclick="Control.Modal.close()" style="float: left; margin: 8px 0px 4px 10px" />
												<input type="button" class="button" value="Confirm" onclick="deleteFormQuestion(deleteQuestion_id)" style="float: right; margin: 8px 10px 4px 0px" />
											</div>
										</div>
										<script type="text/javascript" defer="defer">
											var deleteQuestion_id = 0;

											Sortable.create('form-questions-list', { handles : $$('#form-questions-list div.question'), onUpdate : updateFormQuestionOrder });

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

											function updateFormQuestionOrder() {
												new Ajax.Request('<?php echo ENTRADA_URL; ?>/admin/evaluations/forms/questions', {
													method: 'post',
													parameters: { id : <?php echo $FORM_ID; ?>, section : 'api-order', result : Sortable.serialize('form-questions-list', { name : 'order' }) },
													onSuccess: function(transport) {
														var count = 0;
														$$('#form-questions-list li').each(function(obj) {
															if (obj.hasClassName('odd')) {
																obj.removeClassName('odd');
															}

															if (!(count % 2)) {
																obj.addClassName('odd');
															}
															count++;
														});
														if (!transport.responseText.match(200)) {
															new Effect.Highlight('form-content-questions-holder', { startcolor : '#FFD9D0' });
														}
													},
													onError: function() {
														new Effect.Highlight('form-content-questions-holder', { startcolor : '#FFD9D0' });
													}
												});
											}

											function deleteFormQuestion(efquestion_id) {
												Control.Modal.close();
												$('question_' + efquestion_id).fade({ duration: 0.3 });

												new Ajax.Request('<?php echo ENTRADA_URL; ?>/admin/evaluations/forms/questions', {
													method: 'post',
													parameters: { id: '<?php echo $FORM_ID; ?>', section: 'api-delete', record: efquestion_id },
													onSuccess: function(transport) {
														if (transport.responseText.match(200)) {
															$('question_' + efquestion_id).remove();

															if ($$('#form-questions-list li').length == 0) {
																$('form-content-questions-holder').hide();
																$('display-no-question-message').show();
															}
														} else {
															if ($$('#question_' + efquestion_id + ' .display-error').length == 0) {
																var errorString = 'Unable to delete this question at this time.<br /><br />The system administrator has been notified of this error, please try again later.';
																var errorMsg = new Element('div', { 'class': 'display-error' }).update(errorString);

																$('question_' + efquestion_id).insert(errorMsg);
															}

															$('question_' + efquestion_id).appear({ duration: 0.3 });

															new Effect.Highlight('question_' + efquestion_id, { startcolor : '#FFD9D0' });
														}
													},
													onError: function() {
														$('question_' + efquestion_id).appear({ duration: 0.3 });

														new Effect.Highlight('question_' + efquestion_id, { startcolor : '#FFD9D0' });
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
									There are currently <strong>no questions</strong> associated with this evaluation form.<br /><br />To create questions in this form click the <strong>Add Question</strong> link above.
								</div>
							</td>
						</tr>
					</tbody>
					</table>
					<div id="disable-form-confirmation-box" class="modal-confirmation">
						<form action="<?php echo ENTRADA_URL; ?>/admin/evaluations/forms?section=delete" method="post" id="disableEvaluationFormForm">
							<input type="hidden" name="delete[]" value="<?php echo $FORM_ID; ?>" />
							<input type="hidden" name="confirmed" value="1" />
							<h1>Disable <strong>Form</strong> Confirmation</h1>
							Do you really wish to disable this evaluation form?
							<div class="body">
								<div id="disable-form-confirmation-content" class="content">
									<strong><?php echo html_encode($PROCESSED["form_title"]); ?></strong>
								</div>
							</div>
							If you confirm this action, this form will not be available for evaluations.
							<div class="footer">
								<input type="button" value="Close" onclick="Control.Modal.close()" style="float: left; margin: 8px 0px 4px 10px" />
								<input type="submit" value="Confirm" style="float: right; margin: 8px 10px 4px 0px" />
							</div>
						</form>
					</div>
					<div id="copy-form-confirmation-box" class="modal-confirmation">
						<form action="<?php echo ENTRADA_URL; ?>/admin/evaluations/forms?section=copy&amp;id=<?php echo $FORM_ID; ?>" method="post" id="copyEvaluationForm">
							<h1>Copy <strong>Form</strong> Confirmation</h1>
							<div id="copy-form-message-holder" class="display-generic">If you would like to create a new form based on the existing questions in this form, please provide a new title and press <strong>Copy Form</strong>.</div>
							<div class="body">
								<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Copying Form">
									<colgroup>
										<col style="width: 30%" />
										<col style="width: 70%" />
									</colgroup>
									<tbody>
										<tr>
											<td><span class="form-nrequired">Current Form Title</span></td>
											<td><?php echo html_encode($PROCESSED["form_title"]); ?></td>
										</tr>
										<tr>
											<td><label for="form_title" class="form-required">New Form Title</label></td>
											<td><input type="text" id="form_title" name="form_title" value="<?php echo html_encode($PROCESSED["form_title"]); ?>" maxlength="64" style="width: 96%" /></td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="footer">
								<input type="button" value="Cancel" onclick="Control.Modal.close()" style="float: left; margin: 8px 0px 4px 10px" />
								<input type="submit" value="Copy Form" style="float: right; margin: 8px 10px 4px 0px" />
							</div>
						</form>
					</div>
					<script type="text/javascript" defer="defer">
						// Modal control for deleting form.
						new Control.Modal('form-control-disable', {
							overlayOpacity:	0.75,
							closeOnClick:	'overlay',
							className:		'modal-confirmation',
							fade:			true,
							fadeDuration:	0.30
						});

						// Modal control for copying form.
						new Control.Modal('form-control-copy', {
							overlayOpacity:	0.75,
							closeOnClick:	'overlay',
							className:		'modal-confirmation',
							fade:			true,
							fadeDuration:	0.30
						});
					</script>

					
					<?php
					/**
					 * Sidebar item that will provide the links to the different sections within this page.
					 */
					$sidebar_html  = "<ul class=\"menu\">\n";
					$sidebar_html .= "	<li class=\"link\"><a href=\"#form_information_section\" onclick=\"$('form_information_section').scrollTo(); return false;\" title=\"Form Information\">Form Information</a></li>\n";
					$sidebar_html .= "	<li class=\"link\"><a href=\"#form_questions_section\" onclick=\"$('form_questions_section').scrollTo(); return false;\" title=\"Form Questions\">Form Questions</a></li>\n";
					$sidebar_html .= "</ul>\n";

					new_sidebar_item("Page Anchors", $sidebar_html, "page-anchors", "open", "1.9");
				break;
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "In order to edit an evaluation form, you must provide a valid identifier.";

			echo display_error();

			application_log("notice", "Failed to provide a valid identifer [".$FORM_ID."] when attempting to edit an evaluation form.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to edit an evaluation form you must provide an identifier.";

		echo display_error();

		application_log("notice", "Failed to provide a form identifier when editing an evaluation form.");
	}
}