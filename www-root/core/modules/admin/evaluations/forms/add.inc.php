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
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("section" => "add")), "title" => "Add Evaluation Form");
	
	echo "<h1>Add Evaluation Form</h1>\n";

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
				$PROCESSED["form_parent"] = 0;
				$PROCESSED["form_active"] = 1;
				$PROCESSED["updated_date"] = time();
				$PROCESSED["updated_by"] = $_SESSION["details"]["id"];

				if ($db->AutoExecute("evaluation_forms", $PROCESSED, "INSERT") && ($eform_id = $db->Insert_Id())) {
					application_log("success", "New evaluation form [".$eform_id."] was added to the system.");

					header("Location: ".ENTRADA_URL."/admin/evaluations/forms/questions?id=".$eform_id."&section=add");
					exit;
				} else {
					$ERROR++;
					$ERRORSTR[] = "There was a problem inserting this quiz into the system. The system administrator was informed of this error; please try again later.";

					application_log("error", "There was an error inserting a quiz. Database said: ".$db->ErrorMsg());
				}
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

	// Display Content
	switch ($STEP) {
		case 2 :
			if ($SUCCESS) {
				echo display_success();
			}
			if ($NOTICE) {
				echo display_notice();
			}
			if ($ERROR) {
				echo display_error();
			}
		break;
		case 1 :
		default :
			/**
			 * Load the rich text editor.
			 */
			load_rte();

			if ($ERROR) {
				echo display_error();
			}
			?>
			<form action="<?php echo ENTRADA_URL; ?>/admin/evaluations/forms?section=add&amp;step=2" method="post" id="addEvaluationForm">
				<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Adding Evaluation Form">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 20%" />
						<col style="width: 77%" />
					</colgroup>
					<tfoot>
						<tr>
							<td colspan="3" style="padding-top: 50px">
								<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
								<tr>
									<td style="width: 25%; text-align: left">
										<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/evaluations/forms'" />
									</td>
									<td style="width: 75%; text-align: right; vertical-align: middle">
										<input type="submit" class="button" value="Proceed" />
									</td>
								</tr>
								</table>
							</td>
						</tr>
					</tfoot>
					<tbody>
						<tr>
							<td colspan="3"><h2>Evaluation Form Information</h2></td>
						</tr>
						<tr>
							<td></td>
							<td><label for="target_id" class="form-required">Form Type</label></td>
							<td>
								<select id="target_id" name="target_id" style="width: 250px;">
									<option value="0">-- Select Form Type --</option>
									<?php
									if ($EVALUATION_TARGETS && is_array($EVALUATION_TARGETS) && !empty($EVALUATION_TARGETS)) {
										foreach ($EVALUATION_TARGETS as $target) {
											echo "<option value=\"".$target["target_id"]."\">".html_encode($target["target_title"])."</option>";
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
								<textarea id="form_description" name="form_description" style="width: 550px; height: 125px" cols="70" rows="10"><?php echo clean_input($PROCESSED["form_description"], array("trim", "allowedtags", "encode")); ?></textarea>
							</td>
						</tr>
					</tbody>
				</table>
			</form>
			<?php
		break;
	}
}