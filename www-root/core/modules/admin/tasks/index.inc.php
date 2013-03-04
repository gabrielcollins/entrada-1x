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
} elseif (!$ENTRADA_ACL->amIAllowed("task", "create", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {

	$ENTRADA_USER = User::get($PROXY_ID);
	
	$sort_by = 'title';
	$sort_order = 'asc';
	
	if (isset($_GET['sb'])) {
		$sort_by = $_GET['sb'];
	}
	if (isset($_GET['so'])) {
		$sort_order = $_GET['so'];
	} 
	$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = $sort_by;
	$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"] = $sort_order;
	$temp_tasks = Tasks::getAll(array('order_by' => $sort_by, 'dir' => $sort_order/*, 'limit' => 25, 'offset'=>0*/ )); //no limit for now. TODO work on pagination later.
	
	//now to trim the list down to ones we can access to update
	$tasks = new Tasks();
	foreach ($temp_tasks as $task) {
		if ($ENTRADA_ACL->amIAllowed(new TaskResource($task->getID(),null,$ORGANISATION_ID), "update")) {
			$tasks->push($task);
		}
	}
	
	?>
	
	<h1>Manage Tasks</h1>
	
	<div id="add_new_task_link" style="float: right;">
	<ul class="page-action">
		<li><a id="add_new_task"
			href="<?php echo ENTRADA_URL; ?>/admin/tasks?section=create"
			class="strong-green">Add new task</a></li>
	</ul>
	</div>
	<div class="clear">&nbsp;</div>
	<?php 
	if (count($tasks) > 0 ) {
	?>
	<!--  Include something similar to learning event calendar/range select here -->
	<form method="post" action="<?php echo ENTRADA_URL; ?>/admin/tasks?section=delete">
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
					<td class="modified"><input type="checkbox" id="check_all" title="Select all" /></td>
					<td class="deadline<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "deadline") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("deadline", "Deadline"); ?></td>
					<td class="course">Course</td>
					<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "title") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("title", " Task Title"); ?></td>
					<td class="attachment">&nbsp;</td>
				</tr>
			</thead>
			<?php if ($ENTRADA_ACL->amIAllowed("task", "delete", false)) : ?>
			<tfoot>
				<tr>
					<td>&nbsp;</td>
					<td colspan="4" style="padding-top: 10px">
						<input type="submit" class="button" value="Delete Selected" />
					</td>
				</tr>
			</tfoot>
			<?php endif; ?>
			
			<tbody>
			<?php foreach ($tasks as $task) { 
				
				?>
				<tr>
					<td>
						<?php if ($ENTRADA_ACL->amIAllowed(new TaskResource($task->getID(),null,$ORGANISATION_ID), "delete",true)) { ?>
						<input type="checkbox" name="tasks[]" value="<?php echo $task->getID(); ?>" />
						<?php } ?>
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
	<script type="text/javascript">
	function checkAll(event) {
		var state = Event.findElement(event).checked;
		$$("#task_list_admin tbody input[type=checkbox]").reject(isDisabled).each(function (el) { el.checked=state; });
	}

	function areAllChecked() {
		return $$("#task_list_admin tbody input[type=checkbox]").reject(isDisabled).pluck("checked").all();
	}

	function isDisabled(el) {
		return el.disabled;
	}

	function setCheckAll() {
		var state = areAllChecked();
		$$("#task_list_admin thead input[type=checkbox]").each(function (el) { el.checked=state; });
	}

	document.observe("dom:loaded",function() { 
			$$("#task_list_admin tbody input[type=checkbox]").invoke("observe","click",setCheckAll);
			$$("#task_list_admin thead input[type=checkbox]").invoke("observe","click",checkAll);
		});
	</script>
	<?php
	} else {
		?>
		<div class="display-notice"><h3>No Matching Tasks</h3>
			<?php
			$message = "There are no tasks scheduled."; 
		
			echo $message; 
			?>
		</div>
		<?php
	}
}