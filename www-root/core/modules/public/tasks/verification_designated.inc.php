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
	
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/tasks?section=verification_designated", "title" => "Designated Task Verification");
			
	$user = User::get($PROXY_ID);
	
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
	
	$tasks = TaskVerifiers::getTasksByVerifier($user->getID(), array("dir"=>"desc", "order_by"=>"deadline"));
   		
		?>	
		<h1>Designated Verification Tasks</h1>
		<?php display_status_messages();?>
		
		
		<table class="tableList" id="task_list" cellspacing="0" cellpadding="1" summary="List of Events">
			<colgroup>
				<col class="deadline" />
				<col class="course" />
				<col class="title" />
				<col class="attachment" />
			</colgroup>
			<thead>
				<tr>
					<td class="deadline<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "deadline") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("deadline", "Deadline"); ?></td>
					<td class="course">Course</td>
					<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "title") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("title", " Task Title"); ?></td>
					<td class="attachment">&nbsp;</td>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($tasks as $task) { 
				
				?>
				<tr>
					<td><a href="?section=completion&id=<?php echo $task->getID(); ?>"><?php echo ($task->getDeadline()) ? date(DEFAULT_DATE_FORMAT,$task->getDeadline()) : ""; ?></a></td>
					<td><?php 
						$course = $task->getCourse();
						if ($course) {
							?><a href="?section=completion&id=<?php echo $task->getID(); ?>">
							<?php echo $course->getTitle(); ?></a>
						<?php
						}
					?></td>
					<td><a href="?section=completion&id=<?php echo $task->getID(); ?>"><?php echo $task->getTitle(); ?></a></td>
					<td><a href="?section=completion&id=<?php echo $task->getID(); ?>" ><img src="<?php echo ENTRADA_URL; ?>/images/edit_list.png" title="Edit task completion information" alt="Edit task completion information"/></a></td>
				</tr>
			<?php } ?>
			</tbody>	
		</table>
		
			
	<?php
		
}
