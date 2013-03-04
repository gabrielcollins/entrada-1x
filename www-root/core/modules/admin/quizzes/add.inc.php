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
} elseif (!$ENTRADA_ACL->amIAllowed('quiz', 'create', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("section" => "add")), "title" => "Adding Quiz");
	
	$PROCESSED["associated_proxy_ids"] = array($ENTRADA_USER->getActiveId());
	
	echo "<h1>Adding Quiz</h1>\n";

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
			if ((isset($_POST["associated_proxy_ids"]))) {
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
			if (!in_array($ENTRADA_USER->getActiveId(), $PROCESSED["associated_proxy_ids"])) {
				array_unshift($PROCESSED["associated_proxy_ids"], $ENTRADA_USER->getActiveId());
			}

			if (!$ERROR) {
				$PROCESSED["updated_date"]	= time();
				$PROCESSED["updated_by"]	= $ENTRADA_USER->getID();

				if ($db->AutoExecute("quizzes", $PROCESSED, "INSERT")) {
					if ($quiz_id = $db->Insert_Id()) {
						
						/**
						 * Add the quiz authors to the quiz_contacts table.
						 */
						if ((is_array($PROCESSED["associated_proxy_ids"])) && (count($PROCESSED["associated_proxy_ids"]))) {						
							foreach ($PROCESSED["associated_proxy_ids"] as $proxy_id) {
								if (!$db->AutoExecute("quiz_contacts", array("quiz_id" => $quiz_id, "proxy_id" => $proxy_id, "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID()), "INSERT")) {
									$ERROR++;
									$ERRORSTR[] = "There was an error while trying to attach a <strong>Quiz Author</strong> to this quiz.<br /><br />The system administrator was informed of this error; please try again later.";

									application_log("error", "Unable to insert a new quiz_contact record while adding a new quiz. Database said: ".$db->ErrorMsg());
								}
							}
						}

						application_log("success", "New quiz [".$quiz_id."] added to the system.");

						header("Location: ".ENTRADA_URL."/admin/".$MODULE."?section=add-question&id=".$quiz_id);
						exit;
					} else {
						$ERROR++;
						$ERRORSTR[] = "There was a problem inserting this quiz into the system. The system administrator was informed of this error; please try again later.";

						application_log("error", "There was an error inserting a quiz, as there was no insert ID. Database said: ".$db->ErrorMsg());
					}
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
			$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
			$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
			
			/**
			 * Load the rich text editor.
			 */
			load_rte();

			if ($ERROR) {
				echo display_error();
			}
			?>
			<form action="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?section=add&amp;step=2" method="post" id="addQuizForm">
			<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Adding Quiz">
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
								<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>'" />
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
					<td colspan="3"><h2>Quiz Information</h2></td>
				</tr>
				<tr>
					<td></td>
					<td><label for="quiz_title" class="form-required">Quiz Title</label></td>
					<td><input type="text" id="quiz_title" name="quiz_title" value="<?php echo html_encode($PROCESSED["quiz_title"]); ?>" maxlength="64" style="width: 95%" /></td>
				</tr>
				<tr>
					<td></td>
					<td style="vertical-align: top">
						<label for="quiz_description" class="form-nrequired">Quiz Description</label>
					</td>
					<td>
						<textarea id="quiz_description" name="quiz_description" style="width: 550px; height: 125px" cols="70" rows="10"><?php echo clean_input($PROCESSED["quiz_description"], array("trim", "allowedtags", "encode")); ?></textarea>
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
			</tbody>
			</table>
			</form>
			<?php
		break;
	}
}