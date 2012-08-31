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
 * This file is used to copy an existing evaluation form.
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
} elseif (!$ENTRADA_ACL->amIAllowed("evaluationform", "create", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	if ($FORM_ID) {
		$query = "	SELECT a.*
					FROM `evaluation_forms` AS a
					WHERE a.`eform_id` = ".$db->qstr($FORM_ID)."
					AND a.`form_active` = '1'";
		$form_record = $db->GetRow($query);
		if ($form_record && $ENTRADA_ACL->amIAllowed(new EvaluationFormResource($form_record["eform_id"]), 'update')) {
			$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/evaluations/forms?section=edit&id=".$FORM_ID, "title" => limit_chars($form_record["form_title"], 32));
			$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/evaluations/forms?section=copy&id=".$FORM_ID, "title" => "Copying Quiz");

			/**
			 * Required field "form_title" / Quiz Title.
			 */
			if ((isset($_POST["form_title"])) && ($tmp_input = clean_input($_POST["form_title"], array("notags", "trim")))) {
				$PROCESSED["target_id"] = $form_record["target_id"];
				$PROCESSED["form_title"] = $tmp_input;
				$PROCESSED["form_description"] = $form_record["form_description"];
				$PROCESSED["form_active"] = 1;
				$PROCESSED["updated_date"] = time();
				$PROCESSED["updated_by"] = $_SESSION["details"]["id"];

				if ($db->AutoExecute("evaluation_forms", $PROCESSED, "INSERT") && ($new_eform_id = $db->Insert_Id())) {
					$query = "	SELECT *
								FROM `evaluation_form_questions`
								WHERE `eform_id` = ".$db->qstr($FORM_ID);
					$questions = $db->GetAll($query);
					if ($questions) {
						$new_efquestion_ids = array();

						foreach ($questions as $question) {
							$query = "	INSERT INTO `evaluation_form_questions` VALUES (
											NULL,
											'".$new_eform_id."',
											".$db->qstr($question["questiontype_id"]).",
											".$db->qstr($question["question_text"]).",
											".$db->qstr($question["question_order"])."
										)";
							if (($db->Execute($query)) && ($new_efquestion_id = $db->Insert_Id())) {
								$query = "	INSERT INTO `evaluation_form_responses`
											SELECT NULL, '".$new_efquestion_id."', `response_text`, `response_order`, `response_is_html`, `minimum_passing_level`
											FROM `evaluation_form_responses`
											WHERE `efquestion_id` = ".$db->qstr($question["efquestion_id"]);
								if (($db->Execute($query)) && ($db->Affected_Rows() > 0)) {
									/**
									 * Add this new efquestion_id to the $new_efquestion_ids array.
									 */
									$new_efquestion_ids[] = $new_efquestion_id;
								} else {
									$ERROR++;

									application_log("error", "Unable to insert new evaluation_form_responses record when attempting to copy responses for efquestion_id [".$question["efquestion_id"]."] from eform_id [".$FORM_ID."]. Database said: ".$db->ErrorMsg());
								}
							} else {
								$ERROR++;

								application_log("error", "Unable to insert new evaluation_form_questions record when attempting to copy eform_id [".$FORM_ID."]. Database said: ".$db->ErrorMsg());
							}
						}

						if ($ERROR) {
							if (count($new_efquestion_ids) > 0) {
								$query = "DELETE FROM `evaluation_form_responses` WHERE `efquestion_id` IN (".implode(", ", $new_efquestion_ids).")";
								$db->Execute($query);
							}

							$query = "DELETE FROM `evaluation_form_questions` WHERE `eform_id` = ".$db->qstr($new_eform_id);
							$db->Execute($query);

							$query = "DELETE FROM `evaluation_forms` WHERE `eform_id` = ".$db->qstr($new_eform_id);
							$db->Execute($query);

							$ERROR++;
							$ERRORSTR[] = "There was a problem creating the new evaluation form at this time. The system administrator was informed of this error; please try again later.";

							application_log("error", "There was an error inserting a new copied evaluation form. Database said: ".$db->ErrorMsg());
						}
					}
				} else {
					$ERROR++;
					$ERRORSTR[] = "There was a problem creating the new evaluation form at this time. The system administrator was informed of this error; please try again later.";

					application_log("error", "There was an error inserting a new copied evaluation form. Database said: ".$db->ErrorMsg());
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "Unable to copy this evaluation form because the <strong>New Form Title</strong> field is required, and was not provided.";
			}

			if (!$ERROR) {
				$url = ENTRADA_URL."/admin/evaluations/forms?section=edit&id=".$new_eform_id;

				$SUCCESS++;
				$SUCCESSSTR[] = "You have successfully created a new evaluation form (<strong>".html_encode($PROCESSED["form_title"])."</strong>) based on <strong>".html_encode($form_record["form_title"])."</strong>.<br /><br />You will now be redirected to the <strong>newly copied</strong> form; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";

				application_log("success", "Original eform_id [".$FORM_ID."] has successfully been copied to new eform_id [".$new_eform_id."].");

				echo display_success();
			} else {
				$url = ENTRADA_URL."/admin/evaluations/forms?section=edit&id=".$form_record["eform_id"];

				$ERRORSTR[(count($ERRORSTR) - 1)] .= "<br /><br />You will now be redirected to the <strong>original</strong> quiz; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
				
				echo display_error();
			}

			$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";
		} else {
			$ERROR++;
			$ERRORSTR[] = "In order to copy an evaluation form, you must provide a valid identifier.";

			echo display_error();

			application_log("notice", "Failed to provide a valid identifer [".$FORM_ID."] when attempting to copy an evaluation form.");
		}
	} else {
		add_error("In order to copy an evaluation form you must provide an identifier.");

		echo display_error();

		application_log("notice", "User failed to provide a form identifier to copy an evaluation form.");
	}
}