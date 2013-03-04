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
} elseif (!$ENTRADA_ACL->amIAllowed("taskverification", "update", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	
	$ORGANISATION_ID = $_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["organisation_id"];
	
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	
	require_once("Models/courses/Courses.class.php");
	require_once("Models/organisations/Organisations.class.php");
	require_once("Models/tasks/Tasks.class.php");
	require_once("Models/tasks/TaskOwners.class.php");
	require_once("Models/tasks/TaskRecipients.class.php");
	require_once("Models/tasks/TaskCompletions.class.php");
	require_once("Models/users/User.class.php");
	require_once("Models/users/Cohort.class.php");
	
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/tasks?section=verification", "title" => "Task Verification");
			
	$user = User::get($PROXY_ID);
	
	$task_completions = TaskCompletions::getByVerifier($user->getID(), array("order_by" => array(array("lastname","asc"), array("firstname", "asc")), "where" => "`verified_date` IS NULL" ));
			
	$BULK_COMPLETE = "Verify Complete";
		
	switch ($_POST['action']) {
		case $BULK_COMPLETE:
			$verifications = $_POST['complete_verify'];
			if (!$verifications || !is_array($verifications)){
				add_error("No task recipients were selected for task verification");
			} else {
				$task_successes = array();
				foreach ($verifications as $verification_pair) {
					list($task_id,$recipient_id) = explode("_",$verification_pair);
					$recipient = User::get($recipient_id);
					$task = Task::get($task_id);
					$completion = TaskCompletion::get($task_id,$recipient_id);
					$completion->update($completion->getCompletedDate(),$PROXY_ID,time());
					task_verification_notification(	"confirm",
													array(
														"firstname" => $recipient->getFirstname(),
														"lastname" => $recipient->getLastname(),
														"email" => $recipient->getEmail()),
													array(
														"to_fullname" => $recipient->getFirstname(). " " . $recipient->getLastname(),
														"from_firstname" => $user->getFirstname(),
														"from_lastname" => $user->getLastname(),
														"task_title" => $task->getTitle(),
														"application_name" => APPLICATION_NAME . " Task System"
														));
					$task_successes[$task->getTitle()][] = $recipient->getFirstname(). " " . $recipient->getLastname();
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
				
					$success_listing = generate_bulk_task_verify_success_list($task_successes);
					add_success("<p>You have successfully <strong>verified</strong> completion for the following:</p>".$success_listing."<p>You will now be redirected to the <strong>".$page_title."</strong> page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\">click here</a> to continue.</p>");
					header( "refresh:5;url=".$url );
					display_status_messages();
					break;
				}
			}
		default: 
		?>	
		<h1>Task Verification</h1>
		<?php display_status_messages();?>
		
		<form id="completion_list" method="post">
			<table class="tableList" cellspacing="0" cellpadding="1" summary="List of Events">
				<colgroup>
					<col class="general" width="3%" />
					<col class="general" width="35%" />
					<col class="general" width="35%" />
					<col class="general" width="27%" />
				</colgroup>
				<tfoot>
					<tr>
					<td colspan="5">
					<input type="submit" value="<?php echo $BULK_COMPLETE; ?>" name="action" />
					</td>
					</tr>
				</tfoot>
				<thead>
					<tr>
						<td><input type="checkbox" id="check_all" /></td>
						<td>Recipient</td>
						<td>Task</td>
						<td>Task Completion</td>
					</tr>
				</thead>
				<tbody>
					<?php 
						foreach ($task_completions as $task_completion) {
							$recipient = $task_completion->getRecipient();
							$task = $task_completion->getTask();			 
					?>
					<tr>
						<td>
							<input type="checkbox" name="complete_verify[]" value="<?php echo $task->getID() . "_" . $recipient->getID(); ?>" />
						</td>
						<td>
							<a href="<?php echo ENTRADA_URL; ?>/tasks?section=verify&id=<?php echo $task->getID(); ?>&recipient=<?php echo $recipient->getID(); ?>"><?php echo $recipient->getFullname(); ?></a> 
						</td>
						<td>
							<a href="<?php echo ENTRADA_URL; ?>/tasks?section=verify&id=<?php echo $task->getID(); ?>&recipient=<?php echo $recipient->getID(); ?>"><?php echo $task->getTitle(); ?></a>
						</td>
						<td>
							<a href="<?php echo ENTRADA_URL; ?>/tasks?section=verify&id=<?php echo $task->getID(); ?>&recipient=<?php echo $recipient->getID(); ?>"><?php echo date(DEFAULT_DATE_FORMAT,$task_completion->getCompletedDate()); ?></a>
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
			//var state = $$("#mspr-class-list thead input[type=checkbox]").pluck("checked").any();
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

		document.observe("dom:loaded",function() { 
				$$("#completion_list tbody input[type=checkbox]").invoke("observe","click",setCheckAll);
				$$("#completion_list thead input[type=checkbox]").invoke("observe","click",checkAll);
			});
		</script>		
	<?php
		}
}
