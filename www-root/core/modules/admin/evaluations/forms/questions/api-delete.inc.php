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
 * This file looks a bit different because it is called only by AJAX requests
 * and returns status codes based on it's ability to complete the requested
 * action. In this case, the requested action is to delete quiz questions.
 *
 * 0	Unable to start processing request.
 * 200	There were no errors, everything was deactivated successfully.
 * 400	Cannot delete question becuase no id was provided.
 * 401	Cannot delete question because quiz could not be found.
 * 402	Cannot delete question, because it's in use.
 * 403	There were errors in the delete SQL execution, check the error_log.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVALUATIONS"))) {
	/**
	 * @exception 0: Unable to start processing request.
	 */
	echo 0;
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	/**
	 * @exception 0: Unable to start processing request.
	 */
	echo 0;
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("evaluationformquestion", "delete", false)) {
	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");

	/**
	 * @exception 0: Unable to start processing request.
	 */
	echo 0;
	exit;
} else {
	$RECORD_ID = 0;

	if (isset($_POST["record"]) && ($tmp_input = clean_input($_POST["record"], "int"))) {
		$RECORD_ID = $tmp_input;
	}

	if ($FORM_ID && $RECORD_ID) {
		$query = "	SELECT a.`form_title`, a.`form_description`, b.*
					FROM `evaluation_forms` AS a
					LEFT JOIN `evaluation_form_questions` AS b
					ON b.`eform_id` = b.`eform_id`
					WHERE a.`eform_id` = ".$db->qstr($FORM_ID)."
					AND a.`form_active` = '1'
					AND b.`efquestion_id` = ".$db->qstr($RECORD_ID);
		$question_record = $db->GetRow($query);
		if ($question_record) {
			if ($ENTRADA_ACL->amIAllowed(new EvaluationFormResource($question_record["eform_id"]), "update")) {
				if ($ALLOW_QUESTION_MODIFICATIONS) {
					/**
					 * Clears all open buffers so we can return a simple REST response.
					 */
					ob_clear_open_buffers();
					if($ENTRADA_ACL->amIAllowed(new EvaluationFormQuestionResource($FORM_ID, $question_record["efquestion_id"]), "delete")) {
						$query	= "DELETE FROM `evaluation_form_responses` WHERE `efquestion_id` = ".$db->qstr($RECORD_ID);
						if ($db->Execute($query)) {
							$query	= "DELETE FROM `evaluation_form_questions` WHERE `efquestion_id` = ".$db->qstr($RECORD_ID);
							if ($db->Execute($query)) {
								application_log("success", "Question id [".$RECORD_ID."] was removed from evaluation form id [".$FORM_ID."].");

								echo 200;
								exit;
							} else {
								application_log("error", "Unable to delete question id [".$RECORD_ID."] from evaluation form id [".$FORM_ID."]. Database said: ".$db->ErrorMsg());

								/**
								 * @exception 403: Cannot delete question, because of an SQL error.
								 */
								echo 403;
								exit;
							}
						} else {
							application_log("error", "Unable to delete question id [".$RECORD_ID."] from evaluation form id [".$FORM_ID."]. Database said: ".$db->ErrorMsg());

							/**
							 * @exception 403: Cannot delete question, because of an SQL error.
							 */
							echo 403;
							exit;
						}
					}
				} else {
					application_log("error", "Unable to delete question id [".$RECORD_ID."] from evaluation form id [".$FORM_ID."] because the question is in use.");

					/**
					 * Clears all open buffers so we can return a simple REST response.
					 */
					ob_clear_open_buffers();

					/**
					 * @exception 402: Cannot delete question, because it's in use.
					 */
					echo 402;
					exit;
				}
			} else {
				application_log("error", "Unable to delete question id [".$RECORD_ID."] from evaluation form id [".$FORM_ID."] because users does not have proper ACL to do so.");

				/**
				 * Clears all open buffers so we can return a simple REST response.
				 */
				ob_clear_open_buffers();

				/**
				 * @exception 404: Cannot delete question, because no ACL to do so.
				 */
				echo 404;
				exit;
			}
		} else {
			application_log("error", "Unable to delete question id [".$RECORD_ID."] from evaluation form id [".$FORM_ID."] because the question_id could not be found. ".$query);

			/**
			 * Clears all open buffers so we can return a simple REST response.
			 */
			ob_clear_open_buffers();

			/**
			 * @exception 401: Cannot delete order because evaluation form could not be found.
			 */
			echo 401;
			exit;
		}
	} else {
		application_log("error", "Unable to delete question id [".$RECORD_ID."] from evaluation form id [".$FORM_ID."] because the question_id was provided.");

		/**
		 * Clears all open buffers so we can return a simple REST response.
		 */
		ob_clear_open_buffers();

		/**
		 * @exception 400: Cannot delete question becuase no id was provided.
		 */
		echo 400;
		exit;
	}
}
echo 0;
exit;