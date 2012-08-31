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
} elseif (!$ENTRADA_ACL->amIAllowed(new TaskVerificationResource($TASK_ID, $RECIPIENT_ID, $PROXY_ID), "update")) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	
	$ORGANISATION_ID = $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"];
	
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	
	if ($TASK_ID && ($task = Task::get($TASK_ID))) {
		if ($RECIPIENT_ID && ($recipient = User::get($RECIPIENT_ID))) {
		
			$BREADCRUMB[] = array("url" => ENTRADA_URL."/tasks?section=verify&id=".$TASK_ID."&recipient=".$RECIPIENT_ID, "title" => "Verify Task Completion");
			
			$user = User::get($PROXY_ID);
		
			$course = $task->getCourse();
			$completion = TaskCompletion::get($TASK_ID, $RECIPIENT_ID);
			$faculty = $completion->getFaculty();
			
			$completion_comment = $completion->getCompletionComment();
			$rejection_comment = $completion->getRejectionComment(); 
			$verification_type = $task->getVerificationType();
			
			$rej_com_pol = $task->getRejectionCommentPolicy();

			$facsecpol = $task->getFacultySelectionPolicy();
			$assocfac = $task->getAssociatedFaculty();
			
			$PROCESSED['rejection_comment'] = $rejection_comment;
			$PROCESSED['task_verify'] = 1;
			
			if ($faculty) {
				$PROCESSED['associated_faculty'] = $faculty->getID();
			}
			
			//now to determine if the user is an owner, or only an assoicated faculty
			if (TASK_VERIFICATION_FACULTY == $verification_type ) {
				$faculty_override = ($task->isOwner($user)); 
			} else {
				//or possibly there are no faculty to select
				$faculty_override = (TASK_FACULTY_SELECTION_OFF != $facsecpol) && (count($assocfac) > 0);
			}
				
			if ($completion) {
				switch($_POST['action']) {
					case "Submit":
						if (TASK_VERIFICATION_NONE !== $verification_type) {
							if (isset($_POST['task_verify'])) {
								switch ($_POST['task_verify']) {
									case 1:
										$mode = "verify";
										
										//reset rejection status 
										$rejection_comment = null;
										$rejection_date = null;
										
										$verifier_id = $user->getID();
										$verified_date = time();
										
										$completed_date = $completion->getCompletedDate();
										$completion_comment = $completion->getCompletionComment();
										
										if ($faculty_override) {
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
										} 
										
										$faculty_id = $PROCESSED['associated_faculty'];
										
										if (!has_error()) {
										$update_data = array(
											"verifier_id" => $verifier_id, 
											"verified_date" => $verified_date, 
											"completed_date" => $completed_date, 
											"faculty_id" => $faculty_id, 
											"completion_comment" => $completion_comment, 
											"rejection_comment" => $rejection_comment, 
											"rejection_date" => $rejection_date
										);	
										$completion->update($update_data);
										
										//Design decision: Disabled to cut down on volume of emails -- REMOVE when confirmed
//										task_verification_notification(	"confirm",
//																		array(
//																			"firstname" => $recipient->getFirstname(),
//																			"lastname" => $recipient->getLastname(),
//																			"email" => $recipient->getEmail()),
//																		array(
//																			"to_fullname" => $recipient->getFirstname(). " " . $recipient->getLastname(),
//																			"from_firstname" => $user->getFirstname(),
//																			"from_lastname" => $user->getLastname(),
//																			"task_title" => $task->getTitle(),
//																			"application_name" => APPLICATION_NAME . " Task System"
//																			));
										}
										break;
									case 0:
										$mode = "decline";
										
										$rejection_comment = filter_input(INPUT_POST,"reason", FILTER_SANITIZE_STRING);
										
										$rejection_date = time();
										
										//reset verification if any
										$verifier_id = null;
										$verified_date = null;
										
										$completed_date = $completion->getCompletedDate();
										$completion_comment = $completion->getCompletionComment();
										
										$faculty = $completion->getFaculty();
										if ($faculty) {
											$faculty_id = $faculty->getID();
										} else {
											$faculty_id = null;
										}
										$update_data = array(
											"verifier_id" => $verifier_id, 
											"verified_date" => $verified_date, 
											"completed_date" => $completed_date, 
											"faculty_id" => $faculty_id, 
											"completion_comment" => $completion_comment, 
											"rejection_comment" => $rejection_comment, 
											"rejection_date" => $rejection_date
										);	
										$completion->update($update_data);
										
										//check against policy
										if (!$rejection_comment && (TASK_COMMENT_REQUIRE == $rej_com_pol)) {
											add_error("A reason is required when declining verification for this task.");
										}
										
										task_verification_notification(	"denial",
																		array(
																			"firstname" => $recipient->getFirstname(),
																			"lastname" => $recipient->getLastname(),
																			"email" => $recipient->getEmail()),
																		array(
																			"to_fullname" => $recipient->getFirstname(). " " . $recipient->getLastname(),
																			"from_firstname" => $user->getFirstname(),
																			"from_lastname" => $user->getLastname(),
																			"task_title" => $task->getTitle(),
																			"application_name" => APPLICATION_NAME . " Task System",
																			"reason" => $rejection_comment
																			));
										break;
									default:
										add_error("Unknown verification type selected.");
								}
							}
						} else {
							add_error("This task does not require verification.");
						}
						
						if (!has_error()) {
							clear_success();
							$task_verifications = TaskCompletions::getByVerifier($user->getID(), array("where" => "`verified_date` IS NULL" ));
							$has_verification_requests = (count($task_verifications) > 0);
		
							if ($has_verification_requests) {
								$url = ENTRADA_URL."/tasks?section=verification";
								$page_title = "Task Verification";
							} else {
								$url = ENTRADA_URL."/tasks";
								$page_title = "Task List";
							}
							
							header( "refresh:5;url=".$url );
							switch($mode) {
								case "verify":
									add_success("<p>You have successfully <strong>verified</strong> completion of the <strong>".html_encode($task->getTitle())."</strong> task by <strong>". $recipient->getFirstname() . " " . $recipient->getLastname() ."</strong>.</p><p>You will now be redirected to the <strong>".$page_title."</strong> page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\">click here</a> to continue.</p>");
									break;
								case "decline":
									add_success("<p>You have successfully <strong>declined verification</strong> of completion of the <strong>".html_encode($task->getTitle())."</strong> task by <strong>". $recipient->getFirstname() . " " . $recipient->getLastname() ."</strong>.</p><p>You will now be redirected to the <strong>".$page_title."</strong> page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\">click here</a> to continue.</p>");
									break;
							}
							display_status_messages();
							break;
						}
						
					default:
						if (!($completion->isVerified())) {
						?>
				<h1>Task Verification Request</h1>
				
				<?php display_status_messages(); ?>
				<?php echo display_person($recipient); ?>
				<p><a href="<?php echo ENTRADA_URL; ?>/people?id=<?php echo $recipient->getID(); ?>"><?php echo $recipient->getFirstname() . " " . $recipient->getLastname(); ?></a> has asked you to verify that he or she completed the task, <a href="<?php echo ENTRADA_URL; ?>/tasks?section=details&id=<?php echo $TASK_ID; ?>"><?php echo $task->getTitle(); ?></a>.</p>
				<?php
					if ($faculty_override) { 
						if ($faculty) {
				?> 
				<p>The task recipient specified <?php echo $faculty->getName(); ?> as associated faculty. If this is not correct, please select a different faculty member from the list below.</p>
				<?php
						} else {
				?>
				<p>The task recipient did not specify any associated faculty. If this is not correct, please select a faculty member from the list below.</p>
				<?php
						}
					}
					if ($completion_comment) {
				?>
				<p>The task recipient included a comment in their request:</p>
				<blockquote class="completion_comment"><?php echo nl2br(html_encode($completion_comment)); ?></blockquote>
				<?php
					}
					if ($rejection_comment && ($task->isOwner($user) || $task->isVerifier($user))) {
				?>
				<p>A task verifier included a comment in their rejection:</p>
				<blockquote class="rejection_comment"><?php echo nl2br(html_encode($rejection_comment)); ?></blockquote>
				<?php
					}
				?>
				<form method="post"  id="task_verify_form">
					<input type="hidden" name="task_id" value="<?php echo $TASK_ID; ?>"/>
					<input type="hidden" name="recipient_id" value="<?php echo $RECIPIENT_ID; ?>"/>
					<table id="verify_task">
						<colgroup>
							<col width="3%"></col>
							<col width="25%"></col>
							<col width="72%"></col>
						</colgroup>
						<tbody>
							<tr>
								<td>
									<input type="radio" id="task_verify_yes" name="task_verify" value="1" checked="checked" />
								</td>
								<td colspan="2">
									<label for="task_verify_yes" class="form-nrequired">Task Completed</label>
								</td>
							</tr>
							<tr>
								<td>
									<input type="radio" id="task_verify_no" name="task_verify" value="0" />
								</td>
								<td colspan="2">
									<label for="task_verify_no" class="form-nrequired">Task Not Completed</label>
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<?php if ($faculty_override) { ?>
							<tr>
								<td>
									&nbsp;
								</td>
								<td>
									<label for="associated_faculty" class="form-nrequired">Associated Faculty</label> 
								</td>
								<td>
									<select class="associated_faculty_select" name="associated_faculty">
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
							<?php } ?>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="3">
									<input type="submit" name="action" value="Submit" />
								</td>
							</tr>
						</tfoot>
					</table>
				</form>
				
				<h2 title="Task Details" class="collapsed">Task Details: <?php echo html_encode($task->getTitle()); ?></h2>
					<div id="task-details">
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
						?>
					</table>
					</div>
					
					<form id="decline_verification_form" method="post">
						<input type="hidden" name="task_id" value="<?php echo $TASK_ID; ?>"/>
						<input type="hidden" name="recipient_id" value="<?php echo $RECIPIENT_ID; ?>"/>
						<input type="hidden" id="task_verify" name="task_verify" value="1" />
						<input type="hidden" id="task_verify_details" name="reason" value="" />
						<input type="hidden" name="action" value="Submit" />
					</form>
			
					<div id="reject-verify-box" class="modal-confirmation" style="height: <?php echo (TASK_COMMENT_NONE != $rej_com_pol) ? "39" : "23"; ?>ex">
						<h1>Decline <strong>Verification</strong> Request</h1>
						<div class="display-notice">
							Please confirm that you <strong>do not</strong> wish to verify the completion of this task by <?php echo $recipient->getFirstname() . " " . $recipient->getLastname(); ?>.
						</div>
						<?php
							switch($rej_com_pol) {
								case TASK_COMMENT_NONE:
									break;
								case TASK_COMMENT_REQUIRE:
									$comment_required = true; //fall through
								case TASK_COMMENT_ALLOW:	
						?>
						<p>
							<label for="reject-verify-details" class="form<?php echo ($comment_required) ? "-required": "";?>">Please provide an explanation for this decision<?php echo ($comment_required) ? " (required)": "";?>:</label><br />
							<textarea id="reject-verify-details" name="reject_verify_details" style="width: 99%; height: 75px" cols="45" rows="5"></textarea>
						</p>
						<?php
							}
						?>
						<div class="footer">
							<button class="left" onclick="Control.Modal.close()">Close</button>
							<button class="right" id="reject-verify-confirm">Submit</button>
						</div>
					</div>
			
					<script type="text/javascript">
					
					document.observe('dom:loaded', function() {
						var verify_modal = new Control.Modal('reject-verify-box', {
							overlayOpacity:	0.75,
							closeOnClick:	'overlay',
							className:		'modal-confirmation',
							fade:			true,
							fadeDuration:	0.30
						});
	
						$('task_verify_form').observe('submit',function (e) {
							if ($('task_verify_no').checked) {
								Event.stop(e);
								verify_modal.open();
							}
						});
						
			
						Event.observe('reject-verify-confirm', 'click', function() {
							$('task_verify').setValue('0');
			
							if ($('reject-verify-details')) {
								$('task_verify_details').setValue($('reject-verify-details').getValue());
							}
							$('decline_verification_form').submit();
						});
					});
					</script>
						<?php
						} else {
						?>
					<h1>Task Verification</h1>
					
					<div>Completion of this task has already been verified for <?php echo $recipient->getFirstname() . " " . $recipient->getLastname(); ?>.</div>
							
						<?php
						}
				}
			} else {
				header( "refresh:15;url=".ENTRADA_URL."/".$MODULE );
				
				add_error("In order to verify a task you must provide valid recipient and task identifiers.");
		
				echo display_error();
		
				application_log("notice", "Failed to provide valid recipient and task identifers when attempting to verify a task.");
			}
		} else {
			header( "refresh:15;url=".ENTRADA_URL."/".$MODULE );
			
			add_error("In order to verify a task you must provide a valid recipient identifier.");
	
			echo display_error();
	
			application_log("notice", "Failed to provide valid recipient identifer when attempting to verify a task.");
		}
	} else {
		header( "refresh:15;url=".ENTRADA_URL."/".$MODULE );
		
		add_error("In order to view task details you must provide a valid task identifier.");

		echo display_error();

		application_log("notice", "Failed to provide valid task identifer when attempting to view a task.");
	}
}
