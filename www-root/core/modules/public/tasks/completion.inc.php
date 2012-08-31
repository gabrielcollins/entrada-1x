<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.finglan@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_TASKS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed(new TaskVerificationResource($TASK_ID, null, $PROXY_ID,$ORGANISATION_ID), "update")) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	
	
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	
	if ($TASK_ID && ($task = Task::get($TASK_ID))) {
		
		$BREADCRUMB[] = array("url" => ENTRADA_URL."/tasks?section=verification_designated", "title" => "Designated Task Verification");
		$BREADCRUMB[] = array("url" => ENTRADA_URL."/tasks?section=completion&id=".$TASK_ID, "title" => "Task Completion Information");
		
		$task_completions = TaskCompletions::getByTask($TASK_ID, array("order_by" => array(array("lastname","asc"), array("firstname", "asc") ) ));
		
		$user = User::get($PROXY_ID);
		
		$facsecpol = $task->getFacultySelectionPolicy();
		$assocfac = $task->getAssociatedFaculty();
		$faculty_selection = ((0 < count($assocfac)) && (TASK_FACULTY_SELECTION_OFF != $facsecpol));
		
		$PROCESSED = array();
		foreach ($task_completions as $task_completion) {
			$faculty = $task_completion->getFaculty();
			$recipient = $task_completion->getRecipient();
			$faculty_id = $faculty ? $faculty->getID() : 0;
			$PROCESSED['associated_faculty_'.$recipient->getID()] = $faculty_id;
		}	 				
		
		switch ($_POST['action']) {
			case "Reject":
				break;
			case "Update":
				
				//There are two kinds of data that can be changed on the form: verification of completion and associated faculty. (rejection is handled by different forms) 
				//Two minimize the number of transactions, we want to build the update data and replace where necessary. 
				//since we already have the existing completion information, and we have called for the faculty user objects we can compare submissions to the current
				//state at low cost. 

				$updates = array();
				
				foreach ($task_completions as $task_completion) {
					$recipient = $task_completion->getRecipient();
					$recipient_id = $recipient->getID();
					$verifier = $task_completion->getVerifier();
					if ($verifier) {
						$verified_date = $task_completion->getVerifiedDate();
						$verifier_id = $verifier->getID();
					} else {
						$verified_date = null;
						$verifier_id = null;
					}
					$completed_date = $task_completion->getCompletedDate(); 
					$completion_comment = $task_completion->getCompletionComment();
					$rejection_comment = $task_completion->getRejectionComment();
					$rejection_date = $task_completion->getRejectionDate();
					$faculty = $task_completion->getFaculty();
					if ($faculty) {
						$faculty_id = $faculty->getID();
					} else {
						$faculty_id = 0;
					}
					$updates[$recipient_id] = array(
						"recipient_id" => $recipient_id,
						"verifier_id" => $verifier_id, 
						"verified_date" => $verified_date, 
						"completed_date" => $completed_date, 
						"faculty_id" => $faculty_id, 
						"completion_comment" => $completion_comment, 
						"rejection_comment" => $rejection_comment, 
						"rejection_date" => $rejection_date,
						"modified" => 0
					);	
				}
				$cur_time = time(); //avoids minor variances due to script execution time 
				
				//first we build update rows for verification 
				$recipients_to_complete = $_POST['complete_verify'];
				if ($recipients_to_complete && is_array($recipients_to_complete)){
					foreach ($recipients_to_complete as $recipient_id) {
						$updates[$recipient_id]["verifier_id"] = $user->getID();
						$updates[$recipient_id]["verified_date"] = $cur_time;
						$updates[$recipient_id]["completed_date"] = ($updates[$recipient_id]["completed_date"]) ? ($updates[$recipient_id]["completed_date"]) : $cur_time;
						$updates[$recipient_id]["modified"] = 1;
					}
				}

				//DECISION NOTE: Faculty can be changed regardless of whether or not the task is verified complete.  
				
				$associated_faculty_ids = array();
				foreach ($task_completions as $task_completion) {
					$recipient = $task_completion->getRecipient();
					$recipient_id = $recipient->getID();
					
					$faculty = $task_completion->getFaculty();
					if ($faculty) {
						$faculty_id = $faculty->getID();
					} else {
						$faculty_id = 0;
					}
					
					$new_faculty_id = filter_input(INPUT_POST,"associated_faculty_".$recipient_id, FILTER_SANITIZE_NUMBER_INT);
					if (!is_null($new_faculty_id) && ($faculty_id != $new_faculty_id )) {
						$updates[$recipient_id]["faculty_id"] = $new_faculty_id;
						$updates[$recipient_id]["modified"] = 1;
					}
				}
				
				$task_successes = array();
				foreach ($updates as $update) {
					if ($update["modified"]) {
						$recipient_id = $update["recipient_id"];
						$recipient = User::get($recipient_id);
						$completion = TaskCompletion::get($TASK_ID,$recipient_id);
						$completion->update($update);
					
						//design decision: disabled as students should no longer be aware of verification processes. only when something gets rejected
						//$verification_date = $task_completion->getVerifiedDate();
//						if ($verification_date != $update["verification_date"]) {
//							task_verification_notification(	"confirm",
//														array(
//															"firstname" => $recipient->getFirstname(),
//															"lastname" => $recipient->getLastname(),
//															"email" => $recipient->getEmail()),
//														array(
//															"to_fullname" => $recipient->getFirstname(). " " . $recipient->getLastname(),
//															"from_firstname" => $user->getFirstname(),
//															"from_lastname" => $user->getLastname(),
//															"task_title" => $task->getTitle(),
//															"application_name" => APPLICATION_NAME . " Task System"
//															));
//						}
						$task_successes[$task->getTitle()][] = $recipient->getFirstname(). " " . $recipient->getLastname();
					}
				}
				
				$page_title = html_encode($task->getTitle()). " Completion Information";
				$url = ENTRADA_URL."/tasks?section=completion&id=".$TASK_ID;
				
				if (count($task_successes[$task->getTitle()]) == 0) {
					error_redirect($url, $page_title, "<p>No changes were made; nothing to do.</p>");
					break;
				}
				
				if (!has_error()) {
					clear_success();
					
					$success_listing = generate_bulk_task_verify_success_list($task_successes);
					
					success_redirect($url, $page_title, "<p>You have successfully updated task completion information for</p>".$success_listing);
					
					break;
				}
				
			default: 
			?>	
			<h1><?php echo html_encode($task->getTitle());?>: Completion Information</h1>
			<?php display_status_messages();
			?>
			
			<form id="completion_list" method="post">
				<input type="hidden" name="task_id" value="<?php echo $task->getID(); ?>" />
				<table class="tableList" cellspacing="0" cellpadding="1" summary="List of Events">
					<colgroup>
						<col class="general" width="3%" />
						<col class="general" width="32%" />
						<col class="general" width="32%" />
						<col class="general" width="33%" />
					</colgroup>
					<tfoot>
						<tr>
						<td colspan="5">
						<input type="submit" value="Update" name="action" />
						</td>
						</tr>
					</tfoot>
					<thead>
						<tr>
							<td><input type="checkbox" id="check_all" title="Select all" /></td>
							<td>Recipient</td>
							<td>Task Completion</td>
							<td>Associated Faculty</td>
						</tr>
					</thead>
					<tbody>
						<?php 
							foreach ($task_completions as $task_completion) {
								$recipient = $task_completion->getRecipient();
								$verifier = $task_completion->getVerifier();
								$v_date = $task_completion->getVerifiedDate();		
								$faculty = $task_completion->getFaculty();	 
								
								$rejected_date = $task_completion->getRejectionDate();
								$completed_date = $task_completion->getCompletedDate();
								if ($rejected_date){
									if ($rejected_date > $completed_date) {
										$rowclass=" class=\"rejected\"";
									} else {
										$rowclass=" class=\"resubmit\"";
									}
								} else {
									$rowclass="";
								}
						?>
						<tr<?php echo $rowclass;?>>
							<td>
								<?php if ($verifier && $v_date) { ?>
								<img src="<?php echo ENTRADA_URL?>/images/task_completed.png" alt="Task Completed" title="Task Completed" />
								<?php } else { ?> 
								<input type="checkbox" name="complete_verify[]" value="<?php echo $recipient->getID(); ?>" />
								<?php } ?>
							</td>
							<td>
								<a href="<?php echo ENTRADA_URL; ?>/tasks?section=verify&id=<?php echo $task->getID(); ?>&recipient=<?php echo $recipient->getID(); ?>"><?php echo $recipient->getFullname(); ?></a> 
							</td>
							<td>
								<?php echo ($task_completion->isCompleted())? date(DEFAULT_DATE_FORMAT, $task_completion->getCompletedDate()) : "&nbsp;"?>
							</td>
							<td>
								<?php 
									if ($faculty_selection) {
									?>
										<select class="associated_faculty_select" name="associated_faculty_<?php echo $recipient->getID(); ?>">
										<?php
										echo build_option("0","None");
										foreach ($assocfac as $faculty) {
											echo build_option($faculty->getID(), $faculty->getFullname(), $faculty->getID() == $PROCESSED['associated_faculty_'.$recipient->getID()]);
										}
										?>
										</select>	
										<?php 
									}
								?>
							</td>
							<td>
								<a href="#" class="reject_button <?php echo ($task_completion->isVerified())? "verified": ($task_completion->isRejected()) ? "rejected": "" ; ?>">Reject</a> 
							</td>
						</tr>
						<?php 
							} 
						?>
					</tbody>
				</table>
			</form>
			<script type="text/javascript">
			function checkAll(event) {
				var state = Event.findElement(event).checked;
				$$("#completion_list tbody input[type=checkbox]").reject(isDisabled).each(function (el) { el.checked=state; });
			}
	
			function areAllChecked() {
				return $$("#completion_list tbody input[type=checkbox]").reject(isDisabled).pluck("checked").all();
			}
	
			function isDisabled(el) {
				return el.disabled;
			}
	
			function setCheckAll() {
				var state = areAllChecked();
				$$("#completion_list thead input[type=checkbox]").each(function (el) { el.checked=state; });
			}

			function checkRelated(event) {
				var el = Event.findElement(event);
				var row = el.up("tr");
				var box = row.down("input[type=checkbox]");
				box.checked="checked";
				setCheckAll();
			}
	
			document.observe("dom:loaded",function() { 
					$$("#completion_list tbody input[type=checkbox]").invoke("observe","click",setCheckAll);
					$$("#completion_list thead input[type=checkbox]").invoke("observe","click",checkAll);
					//$$("#completion_list tbody select").invoke("observe","change",checkRelated); //Disabled for design reasons. May be unexpected behaviour. 
					
				});
			</script>
			<?
		}
	} else {
		header( "refresh:15;url=".ENTRADA_URL."/admin/".$MODULE );
		
		add_error("In order to edit a task you must provide a valid task identifier.");

		echo display_error();

		application_log("notice", "Failed to provide valid task identifer when attempting to edit a task.");
	}
}
		