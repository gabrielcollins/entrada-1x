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
} elseif (!$ENTRADA_ACL->amIAllowed("task", "delete", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/tasks?section=delete", "title" => "Delete Tasks");
	
	$ORGANISATION_ID = $_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["organisation_id"];
		
	require_once("Models/tasks/Tasks.class.php");
	require_once("Models/users/User.class.php");
	
	$ENTRADA_USER = User::get($PROXY_ID);
	
	if (isset($_POST['tasks']) && is_array($_POST['tasks'])) {
		$task_ids = $_POST['tasks'];
		$tasks = new Tasks();
		foreach ($task_ids as $task_id) {
			$task = Task::get($task_id);
			if ($ENTRADA_ACL->amIAllowed(New TaskResource($task_id,null,$ORGANISATION_ID), "delete")) {
				$tasks->push($task);
			} else {
				add_notice("You do not have permission to delete the task ".html_encode($task->getTitle())." so it was omitted from this list.");
			}
		}
		if (isset($_POST['delete']) && ($_POST['delete'] == 'Confirm Delete')) {
			foreach ($tasks as $task) {
				$task->delete();
				if (has_success()) {
					clear_success();
					$delete_successes[] = html_encode($task->getTitle());
				}
			}
			clear_success();
			
			if ($delete_successes) {
				$success_listing = "<ul>";
				foreach ($delete_successes as $delete_success) {
					$success_listing .= "<li><strong>".$delete_success."</strong></li>";
				}
				$success_listing .= "</ul>";
				$url = ENTRADA_URL."/admin/tasks";
				$page_title = "Manage Tasks";
				add_success("<p>You have successfully <strong>deleted</strong> the following tasks:</p>".$success_listing."<p>You will now be redirected to the <strong>".$page_title."</strong> page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\">click here</a> to continue.</p>");
				header( "refresh:5;url=".$url );
			}
			display_status_messages();
			$done = true;
		}
	}
	if (!$done) {
		?>
		
		<h1>Delete Tasks</h1>
		
		<?php display_status_messages(); ?>
		
		<?php 
			$total_tasks = count($tasks);
			if ($total_tasks > 0) { 
				$ONLOAD[] = "task_check_all = new CheckboxCheckAll($('task_list_admin').down('thead input[type=checkbox]',0),'#task_list_admin tbody input[type=checkbox]');";
				echo display_notice(array("Please review the following task".(($total_tasks > 1) ? "s" : "")." to ensure that you wish to <strong>permanently delete</strong> ".(($total_tasks > 1) ? "them" : "it").".<br /><br />This will also remove any attached resources, task completion info, etc. and this action cannot be undone."));
		?>
		
		
		<form method="post">
			<table class="tableList" id="task_list_admin" cellspacing="0" cellpadding="1" summary="List of Events">
				<colgroup>
					<col width="3%" />
					<col class="deadline" />
					<col class="course" />
					<col class="title" />
					<col class="attachment" />
				</colgroup>
				<thead>
					<tr>
						<td class="modified"><input type="checkbox" id="check_all" title="Select all" checked="checked" /></td>
						<td class="deadline<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "deadline") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("deadline", "Deadline"); ?></td>
						<td class="course">Course</td>
						<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "title") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("title", " Task Title"); ?></td>
						<td class="attachment">&nbsp;</td>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td>&nbsp;</td>
						<td colspan="4" style="padding-top: 10px">
							<input type="submit" name="delete" value="Confirm Delete" />
						</td>
					</tr>
				</tfoot>
				<tbody>
			<?php foreach ($tasks as $task) { ?>
				<tr>
					<td>
						<input type="checkbox" name="tasks[]" value="<?php echo $task->getID(); ?>" checked="checked" />
					</td>
					<td><a href="<?php echo ENTRADA_URL; ?>/admin/tasks?section=edit&id=<?php echo $task->getID(); ?>"><?php echo ($task->getDeadline()) ? date(DEFAULT_DATE_FORMAT,$task->getDeadline()) : ""; ?></a></td>
					<td><?php 
						$course = $task->getCourse();
						if ($course) {
							?><a href="<?php echo ENTRADA_URL; ?>/admin/tasks?section=edit&id=<?php echo $task->getID(); ?>">
							<?php echo $course->getTitle(); ?></a>
						<?php
						}
					?></td>
					<td><a href="<?php echo ENTRADA_URL; ?>/admin/tasks?section=edit&id=<?php echo $task->getID(); ?>"><?php echo $task->getTitle(); ?></a></td>
					<td><a href="<?php echo ENTRADA_URL; ?>/admin/tasks?section=completion&id=<?php echo $task->getID(); ?>" ><img src="<?php echo ENTRADA_URL; ?>/images/edit_list.png" title="Edit task completion information" alt="Edit task completion information"/></a></td>
				</tr>
			<?php } ?>
			</tbody>	
			</table>
		</form>
		<?php
		} else {
			$url = ENTRADA_URL."/admin/tasks";
			$page_title = "Manage Tasks";
				
			header( "refresh:5;url=".$url );
			echo display_notice(array("<p>No tasks were selected for deletion.</p><p>You will now be redirected to the <strong>".$page_title."</strong> page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\">click here</a> to continue.</p> "));
		}
	}
}
