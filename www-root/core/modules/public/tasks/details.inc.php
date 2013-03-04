<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Allows administrators to edit users from the entrada_auth.user_data table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_TASKS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed(new TaskResource($TASK_ID, null, $ORGANISATION_ID), "read") && (!$ENTRADA_ACL->amIAllowed(new TaskVerificationResource($TASK_ID, null, $PROXY_ID, $ORGANISATION_ID), "update"))) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	
	
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	
	if ($TASK_ID && ($task = Task::get($TASK_ID))) {
		
		$BREADCRUMB[] = array("url" => ENTRADA_URL."/tasks?section=details&id=".$TASK_ID, "title" => "Task Details");
		
		$user = User::get($PROXY_ID);
	
		$course = $task->getCourse();
		$completion = TaskCompletion::get($task->getID(), $user->getID());
		
		$PROCESSED['associated_faculty'] = 0;
		
		switch($_POST['action']) {
			case "Submit":
				if (!isset($_POST['task_completed']) || $_POST['task_completed'] != "completed") {
					add_error("You must check the box \"I have completed this task\" when indicating the task is completed.");
				} else {
					$PROCESSED['task_completed'] = "completed";
				}
				
				$facsecpol = $task->getFacultySelectionPolicy();
				$assocfac = $task->getAssociatedFaculty();
				if ((0 < count($assocfac)) && (TASK_FACULTY_SELECTION_OFF != $facsecpol)) { //first determine if faculty selection is possible
					if (isset($_POST['associated_faculty'])) {
						$faculty_id = $_POST['associated_faculty'];
					} else {
						$faculty_id = 0;
					}
					
					//is it required and is one set?
					if ((TASK_FACULTY_SELECTION_REQUIRE == $facsecpol) && (!$faculty_id)) {
						add_error("This task requires selection of the associated faculty. Please choose one of the faculty from the list and re-submit.");
					} else {
						$id_list = array(0);
						foreach($assocfac as $faculty) {
							$id_list[] = $faculty->getID();
						}
						//is the selected one set within the list?
						if (in_array($faculty_id, $id_list)) {
							$PROCESSED['associated_faculty'] = $faculty_id;
						} else {
							add_error("Provided Faculty ID not found in list. Please choose one of the faculty from the list and re-submit.");
						}
					}
					
				} else {
					$PROCESSED['associated_faculty'] = 0;
				}
				
				$verification_type = $task->getVerificationType();
				switch ($verification_type) {
					case TASK_VERIFICATION_NONE:
						//self verification
						$verifier_id = $user->getID();
						$verification_date = time();
						break;
					case TASK_VERIFICATION_FACULTY:
						$verifier_id = $PROCESSED['associated_faculty'];
						$verifier = User::get($verifier_id);
						if (!$verifier) add_error("Provided Faculty ID not found in list. Please choose one of the faculty from the list and re-submit.");
						$verification_date = null;
						break;
					case TASK_VERIFICATION_OTHER:
						$verifiers = TaskVerifiers::get($TASK_ID);
						$verifier = $verifiers[0];
						$verifier_id = $verifier->getID();
						$verification_date = null;
						break;
				}
				
				$comment_pol = $task->getCompletionCommentPolicy();
				$comment = filter_input(INPUT_POST,"completion_comment", FILTER_SANITIZE_STRING);
				switch($comment_pol) {
					case TASK_COMMENT_NONE:
						$completion_comment = null;
						break;
					case TASK_COMMENT_REQUIRE:
						if (!$comment) {
							add_error("A comment is required for the completion of this task.");
						}
					case TASK_COMMENT_ALLOW:
						$completion_comment = $comment;
						$PROCESSED["completion_comment"] = $completion_comment; 
				}
				
				
				if (!has_error()) { 
					
					$rejection_comment = $completion->getRejectionComment();
					$rejection_date = $completion->getRejectionDate();
					
					$update_data = array(
						"verifier_id" => $verifier_id, 
						"verified_date" => $verified_date, 
						"completed_date" => time(), 
						"faculty_id" => $PROCESSED['associated_faculty'], 
						"completion_comment" => $completion_comment, 
						"rejection_comment" => $rejection_comment, 
						"rejection_date" => $rejection_date
					);	
					$completion->update($update_data);
					
					$notification_types = $task->getVerificationNotificationPolicy();
					if ((TASK_VERIFICATION_NOTIFICATION_EMAIL & $notification_types)&&(TASK_VERIFICATION_NONE != $verification_type)) {
						
						task_verification_notification(	"request",
														array(
															"firstname" => $verifier->getFirstname(),
															"lastname" => $verifier->getLastname(),
															"email" => $verifier->getEmail()),
														array(
															"to_fullname" => $verifier->getFirstname(). " " . $verifier->getLastname(),
															"from_firstname" => $user->getFirstname(),
															"from_lastname" => $user->getLastname(),
															"task_title" => $task->getTitle(),
															"application_name" => APPLICATION_NAME . " Task System",
															"task_verification_url" => ENTRADA_URL."/tasks?section=verify&id=".$task->getID()."&recipient=".$user->getID()
															));
					}
				}
				
				if (!has_error()) {
					clear_success();
					$page_title = "Task Details";
					$url = ENTRADA_URL."/tasks?section=details&id=".$TASK_ID;
					add_success("<p>You have successfully submitted <strong>completion</strong> of the <strong>".html_encode($task->getTitle())."</strong> task.</p><p>You will now be redirected to the <strong>".$page_title."</strong> page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\">click here</a> to continue.</p>");
					header( "refresh:5;url=".$url );
					display_status_messages();
					break;
				}
				
			default:
		?>
		
		<h1>Task Details: <?php echo html_encode($task->getTitle()); ?></h1>
		<?php display_status_messages(); ?>
		
		<form method="post">
			<input type="hidden" name="task_id" value="<?php echo $TASK_ID; ?>" />
			<table id="task_details">
				<colgroup>
					<col width="3%"></col>
					<col width="25%"></col>
					<col width="72%"></col>
				</colgroup>
				<tbody>
					<?php if ($course = $task->getCourse()) {
						$course_title = $course->getTitle();
						$course_id = $course->getID();
						?>
					<tr>
						<td>&nbsp;</td>
						<td>Course</td>
						<td><a href="<?php echo ENTRADA_URL; ?>/courses?id=<?php echo $course_id; ?>"><?php echo html_encode($course_title); ?></a></td>
					</tr>
					<?php 
						}
						if ($time_required = $task->getDuration()) {
					?>
					<tr>
						<td>&nbsp;</td>
						<td>Estimated Time Required</td>
						<td><?php echo html_encode($time_required); ?> minutes</td>
					</tr>
					<?php	
						}
						if ($deadline = $task->getDeadline()) {	
					?>
					<tr>
						<td>&nbsp;</td>
						<td>Deadline</td>
						<td><?php echo date(DEFAULT_DATE_FORMAT,$task->getDeadline()); ?></td>
					</tr>
					<?php } ?>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<?php
						if ($description = $task->getDescription()) {
					?>
					<tr>
						<td>&nbsp;</td>
						<td colspan="2">
							<h2>Description</h2>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td colspan="2"><?php echo clean_input($description,array("allowedtags")); ?></td>
					</tr>
					<?php 
						} 
						if ($task->isRecipient($user)) { //might be allowed to view, but not actually a recipient. e.g. admin. 
					?>
					<tr>
						<td colspan="3"><h2>Task Completion</h2></td>
					</tr>
					<?php if ($completion->isCompleted()) { ?>
					<tr>
						<td>&nbsp;</td>
						<td>Task Completed</td>
						<td><?php echo date(DEFAULT_DATE_FORMAT,$completion->getCompletedDate()); ?></td>
					</tr>
					<?php
						$facsecpol = $task->getFacultySelectionPolicy();
						$faculty = $completion->getFaculty();
						if (($faculty) && (TASK_FACULTY_SELECTION_OFF != $facsecpol)) {
							?>
					<tr>
						<td>&nbsp;</td>
						<td>Associated Faculty</td>
						<td>
							<?php
								echo $faculty->getFullname();
							?>
						</td>
					</tr>	
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
							<?
						}
					
					} else {
					?> 
					<?php
						$facsecpol = $task->getFacultySelectionPolicy();
						$assocfac = $task->getAssociatedFaculty();
						if ((0 < count($assocfac)) && (TASK_FACULTY_SELECTION_OFF != $facsecpol)) {
							?>
					<tr>
						<td>&nbsp;</td>
						<td>Associated Faculty</td>
						<td>
							<select name="associated_faculty">
							<?php
							echo build_option("0","None");
							foreach ($assocfac as $faculty) {
								echo build_option($faculty->getID(), $faculty->getFullname(), $faculty->getID() == $PROCESSED['associated_faculty']);
							}
							?>
							</select>			
						</td>
					</tr>	
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<?
						}

						$comment_policy = $task->getCompletionCommentPolicy();
						if (TASK_COMMENT_NONE != $comment_policy) {
							
						?>
					<tr>
						<td>&nbsp;</td>
						<td valign="top"><label for="completion_comment">Additional Comments</label></td>
						<td>
							<textarea name="completion_comment" style="width: 30em; height: 15ex;" maxlength="500"><?php echo $PROCESSED['completion_commens']; ?></textarea>		
						</td>
					</tr>	
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
							<?
						}
					?>
					<tr>
						<td>
							<input type="checkbox" name="task_completed" id="task_completed" value="completed" <?php echo ($PROCESSED['task_completed'] == 'completed') ? "checked=\"checked\"" : "" ; ?> />
						</td>
						<td colspan="2"><label for="task_completed">I confirm that I have completed the objectives of this task</label></td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
					<td colspan="3">
					<input type="Submit" value="Submit" name="action"></input>
					</td> 
					</tr>
				</tfoot>
					<?php } 
					}
					?>
			</table>
		</form>
		<?php
		}
		
	} else {
		header( "refresh:15;url=".ENTRADA_URL."/".$MODULE );
		
		add_error("In order to view task details you must provide a valid task identifier.");

		echo display_error();

		application_log("notice", "Failed to provide valid task identifer when attempting to view a task.");
	}
}
