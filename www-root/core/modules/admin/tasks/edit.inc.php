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
} elseif (!$ENTRADA_ACL->amIAllowed(new TaskResource($TASK_ID, null, $ORGANISATION_ID), "update", true)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	
	if ($TASK_ID && ($task = Task::get($TASK_ID))) {
	
		//var_dump($task);
		$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/tasks?section=edit&id=".$TASK_ID, "title" => "Edit Task: " .  $task->getTitle());
	
		$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
		
		//set defaults
		$PROCESSED = array();
		$PROCESSED['title'] = $task->getTitle();
		$course = $task->getCourse();
		$PROCESSED['course_id'] = ($course) ? $course->getID() : null;
		$PROCESSED['deadline'] = $task->getDeadline();
		$PROCESSED['time_required'] = $task->getDuration();
		$PROCESSED['description'] = $task->getDescription();
		$PROCESSED['release_start'] = $task->getReleaseStart();
		$PROCESSED['release_finish'] = $task->getReleaseFinish();

		$recipients = TaskRecipients::get($task->getID());
		$recipient = $recipients->current();
		if ($recipient instanceof User) {
			$recipient_type = TASK_RECIPIENT_USER;
		} elseif ($recipient instanceof Cohort ) {
			$recipient_type = TASK_RECIPIENT_CLASS;
		} elseif ($recipient instanceof Organisation) {
			$recipient_type = TASK_RECIPIENT_ORGANISATION;
		}
		$PROCESSED['task_recipient_type'] = $recipient_type;
		
		$faculty = TaskAssociatedFaculty::get($TASK_ID);
		$PROCESSED["associated_faculty"] = array();
		if ($faculty) {
			foreach($faculty as $faculty_member) {
				$PROCESSED["associated_faculty"][] = $faculty_member->getID();
			}
		}
		
		$verifiers = TaskVerifiers::get($TASK_ID);
		$PROCESSED["associated_verifier"] = array();
		if ($verifiers) {
			foreach($verifiers as $verifier) {
				$PROCESSED["associated_verifier"][] = $verifier->getID();
			}
		}
		
		foreach ($recipients as $recipient ){
			if ($recipient instanceof User) {
				$PROCESSED["associated_individual"][] = $recipient->getID();
			} elseif($recipient instanceof Cohort) {
				$PROCESSED["associated_cohorts"][] = $recipient->getCohort();
				$PROCESSED["associated_cohort"] = $recipient->getCohort(); //XXX for compaibility with single select style (before new multi-select widget) 
			} elseif($recipient instanceof Organisation) {
				$PROCESSED["associated_organisation_id"] = $recipient->getID();
			}
			
		}
		
		$PROCESSED["completion_comment_policy"] = $task->getCompletionCommentPolicy();
		$PROCESSED["rejection_comment_policy"] = $task->getRejectionCommentPolicy();
		$PROCESSED["faculty_selection_policy"] = $task->getFacultySelectionPolicy();
		$PROCESSED["task_verification_notification"] = $task->getVerificationNotificationPolicy();
		$PROCESSED["task_verification_type"] = $task->getVerificationType();
		
		switch($_POST['action']) {
			case 'Save':
				
				
				$PROCESSED = validate_task_details_inputs();
	
				process_task_common_errors($PROCESSED);
				
				//echo "<pre>".print_r($PROCESSED, true)."</pre>";
				
				//Error processing comeplete
				if (!has_error()) {
					//first create the task, then add the owners, and finally, if verification is required add those records.
		
					$org_id = ($PROCESSED["associated_organisation_id"] ? $PROCESSED["associated_organisation_id"] : $ORGANISATION_ID); //default to associated organisation as user may have access to multiple
					
					$task_values = array(
						"updater_id" => $PROXY_ID,
						"title" => $PROCESSED['title'],
						"deadline" => $PROCESSED['deadline'],
						"duration" => $PROCESSED['time_required'],
						"description" => $PROCESSED["description"],
						"release_start" => $PROCESSED["release_start"],
						"release_finish" => $PROCESSED["release_finish"],
						"organisation_id" => $org_id,
						"faculty_selection_policy" => $PROCESSED["faculty_selection_policy"],
						"completion_comment_policy" => $PROCESSED["completion_comment_policy"],
						"rejection_comment_policy" => $PROCESSED["rejection_comment_policy"],
						"verification_type" => $PROCESSED["task_verification_type"],
						"verification_notification_policy" => array_reduce($PROCESSED["task_verification_notification"], 'or_bin', 0)
					);
					
					$task->update($task_values);
					if (!has_error()) {
						
						$owners = array();
							
						//doesn't change creator owner. only course for now
						
						$course = $task->getCourse();
						if ($course) {
							$course_id = $course->getID();
						} else {
							$course_id = null;
						}
						
						if ($PROCESSED["course_id"] && ($PROCESSED["course_id"] != $course_id)) { // different course specified
							if ($course_id) { //if there was an old course specified
								TaskOwners::remove($TASK_ID,array(array("type" => TASK_OWNER_COURSE, "id" => $course_id)));
							} 
								
							TaskOwners::add($TASK_ID,array(array("type" => TASK_OWNER_COURSE, "id" => $PROCESSED["course_id"])));
						} elseif ($course_id && !$PROCESSED["course_id"]) { //removing course
							TaskOwners::remove($TASK_ID, array(array("type" => TASK_OWNER_COURSE, "id" => $course_id)));
						}
	
						
						//remove all of the old recipients
						
						TaskRecipients::remove($TASK_ID);
						
						//and add the new ones
						$recipients = array();
						switch($PROCESSED['task_recipient_type']) {
							case TASK_RECIPIENT_USER:
								foreach($PROCESSED["associated_individual"] as $proxy_id) {
									$recipients[] = array("type" => TASK_RECIPIENT_USER, "id" => $proxy_id);	
								}
								break;
							case TASK_RECIPIENT_CLASS:
								foreach($PROCESSED["associated_cohort"] as $cohort) {
									$recipients[] = array("type" => TASK_RECIPIENT_CLASS, "id" => $cohort);	
								}
								break;
							case TASK_RECIPIENT_ORGANISATION:
								$recipients[] = array("type"=>TASK_RECIPIENT_ORGANISATION, "id" => $PROCESSED["associated_organisation_id"]);
								break;
						}
						TaskRecipients::add($TASK_ID,$recipients);
						
						
						TaskAssociatedFaculty::remove($TASK_ID);
						TaskAssociatedFaculty::add($TASK_ID,$PROCESSED['associated_faculty']);
						
						TaskVerifiers::remove($TASK_ID);
						if (TASK_VERIFICATION_OTHER == $PROCESSED["task_verification_type"]) {
							TaskVerifiers::add($TASK_ID, $PROCESSED['associated_verifier']);
						}
						if (!has_error()) {
							$url = ENTRADA_URL."/admin/tasks";
							header( "refresh:5;url=".$url );
										
							clear_success(); //clear sucess messages from models.
							add_success("<p>You have successfully updated the <strong>".$PROCESSED['title']."</strong> task information.</p><p>You will now be redirected to the <strong>Manage Tasks</strong> page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\">click here</a> to continue.</p>");
							display_status_messages();
							break;
						}
					}
				}
			default:
		
				load_rte(); //load the Rich Text Editor
				$ONLOAD[]	= "selectTaskRecipientsOption('".$PROCESSED["task_recipient_type"]."')";
				$ONLOAD[]	= "selectTaskVerificationOption('".$PROCESSED["task_verification_type"]."')";
	?>
	
	<h1><?php echo $translate->translate("task_heading_edit"); ?></h1>
	
	<?php display_status_messages(); ?>
	
	<form id="new_task_form" action="<?php echo ENTRADA_URL; ?>/admin/tasks?section=edit&id=<?php echo $TASK_ID; ?>" method="post">
		<table class="task_form">
			<colgroup>
				<col width="3%"></col>
				<col width="25%"></col>
				<col width="72%"></col>
			</colgroup>
			<tfoot>
				<tr>
					<td colspan="3" style="padding-top: 25px">
						<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td style="width: 25%; text-align: left">
									<input type="button" class="button" value="<?php echo $translate->translate("task_button_cancel"); ?>" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/tasks'" />
								</td>
								<td style="width: 75%; text-align: right; vertical-align: middle">
									<span class="content-small">After saving:</span>
									<select id="post_action" name="post_action">
										<option value="new"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "new") ? " selected=\"selected\"" : ""); ?>>Add another task</option>
										<option value="index"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "index") ? " selected=\"selected\"" : ""); ?>>Return to task list</option>
									</select>
									<input type="hidden" name="action" value="Save" />
									<input type="submit" class="button" value="<?php echo $translate->translate("task_button_save"); ?>" />
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td>&nbsp;</td>
					<td >
						<label for="title" class="form-required"><?php echo $translate->translate("task_field_title"); ?></label>
					</td>
					<td >
						<input id="title" name="title" type="text" maxlength="255" style="width: 250px; vertical-align: middle;" value="<?php echo html_encode($PROCESSED["title"]); ?>"></input>	
					</td>
				</tr>
				<?php
					echo generate_calendar("deadline",$translate->translate("task_field_deadline"),false,$PROCESSED['deadline'],true,false,false,false,false);
				?>
				<tr>
					<td>&nbsp;</td>
					<td >
						<label for="time_required" class="form-nrequired"><?php echo $translate->translate("task_field_time_required"); ?></label>
					</td>
					<td >
						<input id="time_required" name="time_required" type="text" maxlength="4096" style="width: 5em; vertical-align: middle;" value="<?php echo $PROCESSED['time_required']; ?>"></input>	<?php echo $translate->translate("task_misc_minutes"); ?>
					</td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td></td>
					<td><label for="course_id" class="form-nrequired"><?php echo $translate->translate("task_field_course"); ?></label></td>
					<td>
						<select id="course_id" name="course_id" style="width: 95%">
						<option value="0"><?php echo $translate->translate("task_option_course_none"); ?></option>
						<?php
						$query		= "	SELECT * FROM `courses` 
										WHERE `course_active` = '1'
										AND `organisation_id` = " . $ENTRADA_USER->getActiveOrganisation() . "
										ORDER BY `course_name` ASC";
						$results	= $db->GetAll($query);
						if($results) {
							foreach($results as $result) {
								if ($ENTRADA_ACL->amIAllowed(new TaskResource(null, $result["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), "create")) {
									echo "<option value=\"".(int) $result["course_id"]."\"".(($PROCESSED["course_id"] == $result["course_id"]) ? " selected=\"selected\"" : "").">".html_encode($result["course_name"])."</option>\n";
								}
							}
						}
						?>
						</select>
					</td>
				</tr>
				<tr class="task_faculty">
					<td>&nbsp;</td>
					<td style="vertical-align: top"><label for="associated_faculty_ids" class="form-nrequired"><?php echo $translate->translate("task_field_associated_faculty"); ?></label></td>
					<td>
						<input type="text" id="faculty_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle" />
						<?php
							$ONLOAD[] = "window.faculty_list = new AutoCompleteList({ type: 'faculty', url: '". ENTRADA_RELATIVE ."/api/personnel.api.php?type=faculty', remove_image: '". ENTRADA_RELATIVE ."/images/action-delete.gif'})";
						?>
						<div class="autocomplete" id="faculty_name_auto_complete"></div><script type="text/javascript"></script>
						<input type="hidden" id="associated_faculty" name="associated_faculty" />
						<input type="button" class="button-sm" id="add_associated_faculty" value="<?php echo $translate->translate("task_button_add"); ?>" style="vertical-align: middle" />
						<span class="content-small"><?php echo str_replace("%MY_FULLNAME%", html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]), $translate->translate("task_instructions_faculty_name")); ?></span>
						<ul id="faculty_list" class="menu" style="margin-top: 15px">
							<?php
							if (is_array($PROCESSED["associated_faculty"]) && count($PROCESSED["associated_faculty"])) {
								foreach ($PROCESSED["associated_faculty"] as $proxy_id) {
									if ($faculty = User::get($proxy_id)) {
										?>
										<li class="community" id="faculty_<?php echo $faculty->getID(); ?>" style="cursor: move;"><?php echo $faculty->getFullname(); ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" class="list-cancel-image" onclick="window.faculty_list.removeItem('<?php echo $faculty->getID(); ?>');"/></li>
										<?php
									}
								}
							}
							?>
						</ul>
						<input type="hidden" id="faculty_ref" name="faculty_ref" value="" />
						<input type="hidden" id="faculty_id" name="faculty_id" value="" />
					</td>
				</tr>
				
				<tr>
					<td colspan="3">
						<h2><?php echo $translate->translate("task_heading_description"); ?></h2>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td colspan="2">
						<textarea id="description" name="description" style="width: 100%; height: 100px;" cols="65" rows="20"><?php echo html_encode(trim(strip_selected_tags($PROCESSED['description'], array("font")))); ?></textarea>	
					</td>
				</tr>
				<tr>
					<td colspan="3"><h2><?php echo $translate->translate("task_heading_recipients"); ?></h2></td>
				</tr>
				<tr>
					<td style="vertical-align: top"><input type="radio" name="task_recipient_type" id="task_recipient_type_<?php echo TASK_RECIPIENT_CLASS; ?>" value="<?php echo TASK_RECIPIENT_CLASS; ?>" onclick="selectTaskRecipientsOption('<?php echo TASK_RECIPIENT_CLASS; ?>')" style="vertical-align: middle"<?php echo (($PROCESSED["task_recipient_type"] == TASK_RECIPIENT_CLASS) ? " checked=\"checked\"" : ""); ?> /></td>
					<td colspan="2" style="padding-bottom: 15px">
						<label for="task_recipient_type_<?php echo TASK_RECIPIENT_CLASS; ?>" class="radio-group-title"><?php echo $translate->translate("task_field_recipients_class"); ?></label>
						<div class="content-small"><?php echo $translate->translate("task_instructions_recipients_class"); ?></div>
					</td>
				</tr>
				<tr class="task_recipient <?php echo TASK_RECIPIENT_CLASS; ?>_recipient">
					<td></td>
					<td><label for="associated_cohorts" class="form-required"><?php echo $translate->translate("task_field_cohort"); ?></label></td>
					<td>
						<select id="associated_cohorts" name="associated_cohorts" style="width: 203px">
						<?php
						$cohorts = groups_get_all_cohorts($ENTRADA_USER->getActiveOrganisation());
						if (isset($cohorts) && !empty($cohorts)) {
							foreach ($cohorts as $cohort) {
								echo "<option value=\"".$cohort["group_id"]."\"".(($PROCESSED["associated_cohort"] == $cohort["group_id"]) ? " selected=\"selected\"" : "").">".html_encode($cohort["group_name"])."</option>\n";
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
					<td style="vertical-align: top"><input type="radio" name="task_recipient_type" id="task_recipient_type_<?php echo TASK_RECIPIENT_USER; ?>" value="<?php echo TASK_RECIPIENT_USER; ?>" onclick="selectTaskRecipientsOption('<?php echo TASK_RECIPIENT_USER; ?>')" style="vertical-align: middle"<?php echo (($PROCESSED["task_recipient_type"] == TASK_RECIPIENT_USER) ? " checked=\"checked\"" : ""); ?> /></td>
					<td colspan="2" style="padding-bottom: 15px">
						<label for="task_recipient_type_<?php echo TASK_RECIPIENT_USER; ?>" class="radio-group-title"><?php echo $translate->translate("task_field_recipients_students"); ?></label> 
						<div class="content-small"><?php echo $translate->translate("task_instructions_recipients_students"); ?></div>
					</td>
				</tr>
				<tr class="task_recipient <?php echo TASK_RECIPIENT_USER; ?>_recipient">
					<td></td>
					<td style="vertical-align: top"><label for="associated_proxy_ids" class="form-required"><?php echo $translate->translate("task_field_associated_students"); ?></label></td>
					<td>
						<input type="text" id="individual_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle" />
						<?php
							$ONLOAD[] = "window.individual_list = new AutoCompleteList({ type: 'individual', url: '". ENTRADA_RELATIVE ."/api/personnel.api.php?type=student', remove_image: '". ENTRADA_RELATIVE ."/images/action-delete.gif'})";
						?>
						<div class="autocomplete" id="individual_name_auto_complete"></div><script type="text/javascript"></script>
						<input type="hidden" id="associated_individual" name="associated_individual" />
						<input type="button" class="button-sm" id="add_associated_individual" value="Add" style="vertical-align: middle" />
						<span class="content-small"><?php echo str_replace("%MY_FULLNAME%", html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]), $translate->translate("task_instructions_associated_students")); ?></span>
						<ul id="individual_list" class="menu" style="margin-top: 15px">
							<?php
							if (is_array($PROCESSED["associated_individual"]) && count($PROCESSED["associated_individual"])) {
								foreach ($PROCESSED["associated_individual"] as $proxy_id) {
									if ($individual = User::get($proxy_id)) {
										?>
										<li class="community" id="individual_<?php echo $individual->getID(); ?>" style="cursor: move;"><?php echo $individual->getFullname(); ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" class="list-cancel-image" onclick="window.individual_list.removeItem('<?php echo $individual->getID(); ?>');"/></li>
										<?php
									}
								}
							}
							?>
						</ul>
						<input type="hidden" id="individual_ref" name="individual_ref" value="" />
						<input type="hidden" id="individual_id" name="individual_id" value="" />
					</td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<?php if (/*disabled*/false && $ENTRADA_ACL->amIAllowed(new TaskResource(null, null, $ORGANISATION_ID), 'create')) { ?>
				<tr>
					<td style="vertical-align: top"><input type="radio" name="task_recipient_type" id="task_recipient_type_<?php echo TASK_RECIPIENT_ORGANISATION; ?>" value="<?php echo TASK_RECIPIENT_ORGANISATION; ?>" onclick="selectTaskRecipientsOption('<?php echo TASK_RECIPIENT_ORGANISATION; ?>')" style="vertical-align: middle"<?php echo (($PROCESSED["task_recipient_type"] == TASK_RECIPIENT_ORGANISATION) ? " checked=\"checked\"" : ""); ?> /></td>
					<td colspan="2" style="padding-bottom: 15px">
						<label for="task_recipient_type_<?php echo TASK_RECIPIENT_ORGANISATION; ?>" class="radio-group-title"><?php echo $translate->translate("task_field_recipients_organisation"); ?></label>
						<div class="content-small"><?php echo $translate->translate("task_instructions_recipients_organisation"); ?></div>
					</td>
				</tr>
				<tr class="task_recipient <?php echo TASK_RECIPIENT_ORGANISATION; ?>_recipient">
					<td></td>
					<td><label for="associated_organisation_id" class="form-required"><?php echo $translate->translate("task_field_organisation"); ?></label></td>
					<td>
						<select id="associated_organisation_id" name="associated_organisation_id" style="width: 203px">
							<?php
							$organisations = Organisations::get();
							if ($organisations) {
								foreach($organisations as $organisation) {
									$organisation_id = $organisation->getID();
									if ($ENTRADA_ACL->amIAllowed(new TaskResource(null, null, $organisation_id), 'create')) { 
										$organisation_title = $organisation->getTitle();
										echo build_option($organisation_id, html_encode($organisation_title), ($PROCESSED["associated_organisation_id"] == $organisation_id) );
									}
								}
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<?php } ?>
				<tr>
					<td colspan="3"><h2 class="collapsed" title="<?php echo $translate->translate("task_heading_completion_options"); ?>"><?php echo $translate->translate("task_heading_completion_options"); ?></h2></td>
				</tr>
			</tbody>
			<tbody id="<?php echo strtolower(str_replace(" ", "-",$translate->translate("task_heading_completion_options"))); ?>">
				<tr>
					<td>&nbsp;</td>
					<td><?php echo $translate->translate("task_field_faculty_selection"); ?></td>
					<td><select id="faculty_selection_policy" name="faculty_selection_policy" style="width: 203px">
							<?php
							foreach (array(TASK_FACULTY_SELECTION_OFF, TASK_FACULTY_SELECTION_ALLOW, TASK_FACULTY_SELECTION_REQUIRE) as $policy) {
								echo build_option($policy, $translate->translate("task_option_faculty_selection_".$policy), $PROCESSED["faculty_selection_policy"] == $policy);
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><?php echo $translate->translate("task_field_completion_comments"); ?></td>
					<td><select id="completion_comment_policy" name="completion_comment_policy" style="width: 203px">
							<?php
							foreach (array(TASK_COMMENT_NONE, TASK_COMMENT_ALLOW, TASK_COMMENT_REQUIRE) as $policy) {
								echo build_option($policy, $translate->translate("task_option_complete_".$policy), $PROCESSED["completion_comment_policy"] == $policy);
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><?php echo $translate->translate("task_field_rejection_comments"); ?></td>
					<td><select id="rejection_comment_policy" name="rejection_comment_policy" style="width: 203px">
							<?php
							foreach (array(TASK_COMMENT_NONE, TASK_COMMENT_ALLOW, TASK_COMMENT_REQUIRE) as $policy) {
								echo build_option($policy, $translate->translate("task_option_complete_".$policy), $PROCESSED["rejection_comment_policy"] == $policy);
							}
							?>
						</select>
					</td>
				</tr>
			</tbody>
			<tbody>
				<tr>
					<td colspan="3"><h2 class="collapsed" title="<?php echo $translate->translate("task_heading_verification_options"); ?>"><?php echo $translate->translate("task_heading_verification_options"); ?></h2></td>
				</tr>
			</tbody>
			<tbody id="<?php echo strtolower(str_replace(" ", "-",$translate->translate("task_heading_verification_options"))); ?>">
				<tr>
					<td style="vertical-align: top"><input type="radio" name="task_verification_type" id="task_verification_type_<?php echo TASK_VERIFICATION_NONE; ?>" value="<?php echo TASK_VERIFICATION_NONE; ?>" onclick="selectTaskVerificationOption('<?php echo TASK_VERIFICATION_NONE; ?>', event)" style="vertical-align: middle"<?php echo (($PROCESSED["task_verification_type"] == TASK_VERIFICATION_NONE) ? " checked=\"checked\"" : ""); ?> /></td>
					<td colspan="2" style="padding-bottom: 15px">
						<label for="task_verification_type_<?php echo TASK_VERIFICATION_NONE; ?>" class="radio-group-title"><?php echo $translate->translate("task_field_verification_none"); ?></label>
						<div class="content-small"><?php echo $translate->translate("task_instructions_verification_none"); ?></div>
					</td>
				</tr>
				<tr class="<?php echo TASK_VERIFICATION_FACULTY; ?>_anchor">
					<td style="vertical-align: top"><input type="radio" name="task_verification_type" id="task_verification_type_<?php echo TASK_VERIFICATION_FACULTY; ?>" value="<?php echo TASK_VERIFICATION_FACULTY; ?>" onclick="selectTaskVerificationOption('<?php echo TASK_VERIFICATION_FACULTY; ?>', event)" style="vertical-align: middle"<?php echo (($PROCESSED["task_verification_type"] == TASK_VERIFICATION_FACULTY) ? " checked=\"checked\"" : ""); ?> /></td>
					<td colspan="2" style="padding-bottom: 15px">
						<label for="task_verification_type_<?php echo TASK_VERIFICATION_FACULTY; ?>" class="radio-group-title"><?php echo $translate->translate("task_field_verification_faculty"); ?></label>
						<div class="content-small"><?php echo $translate->translate("task_instructions_verification_faculty"); ?></div>
					</td>
				</tr>
				<tr>
					<td style="vertical-align: top"><input type="radio" name="task_verification_type" id="task_verification_type_<?php echo TASK_VERIFICATION_OTHER; ?>" value="<?php echo TASK_VERIFICATION_OTHER; ?>" onclick="selectTaskVerificationOption('<?php echo TASK_VERIFICATION_OTHER; ?>', event)" style="vertical-align: middle"<?php echo (($PROCESSED["task_verification_type"] == TASK_VERIFICATION_OTHER) ? " checked=\"checked\"" : ""); ?> /></td>
					<td colspan="2" style="padding-bottom: 15px">
						<label for="task_verification_type_<?php echo TASK_VERIFICATION_OTHER; ?>" class="radio-group-title"><?php echo $translate->translate("task_field_verification_other"); ?></label> 
						<div class="content-small"><?php echo $translate->translate("task_instructions_verification_other"); ?></div>
					</td>
				</tr>
				<tr class="task_verification <?php echo TASK_VERIFICATION_OTHER; ?>_verification <?php echo TASK_VERIFICATION_OTHER; ?>_anchor">
					<td></td>
					<td style="vertical-align: top"><label for="verifier_proxy_ids" class="form-required"><?php echo $translate->translate("task_field_verification_other_names"); ?></label></td>
					<td>
						<input type="text" id="verifier_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle" />
						<?php
							$ONLOAD[] = "window.verifier_list = new AutoCompleteList({ type: 'verifier', url: '". ENTRADA_RELATIVE ."/api/personnel.api.php?type=facultyorstaff', remove_image: '". ENTRADA_RELATIVE ."/images/action-delete.gif', limit: 1})";
						?>
						<div class="autocomplete" id="verifier_name_auto_complete"></div><script type="text/javascript"></script>
						<input type="hidden" id="associated_verifier" name="associated_verifier" />
						<input type="button" class="button-sm" id="add_associated_verifier" value="<?php echo $translate->translate("task_button_add"); ?>" style="vertical-align: middle" />
						<ul id="verifier_list" class="menu" style="margin-top: 15px">
							<?php
							if (is_array($PROCESSED["associated_verifier"]) && count($PROCESSED["associated_verifier"])) {
								foreach ($PROCESSED["associated_verifier"] as $proxy_id) {
									if ($verifier = User::get($proxy_id)) {
										?>
										<li class="community" id="verifier_<?php echo $verifier->getID(); ?>" style="cursor: move;"><?php echo $verifier->getFullname(); ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" class="list-cancel-image" onclick="window.verifier_list.removeItem('<?php echo $verifier->getID(); ?>');"/></li>
										<?php
									}
								}
							}
							?>
						</ul>
						<input type="hidden" id="verifier_ref" name="verifier_ref" value="" />
						<input type="hidden" id="verifier_id" name="verifier_id" value="" />
					</td>
				</tr>
				<tr class="active_verification_options">
					<td>&nbsp;</td>
					<td colspan="2">
						<h3><?php echo $translate->translate("task_field_notification_types"); ?></h3>
						<table>
							<colgroup>
								<col width="3%"></col>
								<col width="25%"></col>
								<col width="72%"></col>
							</colgroup>
							<tbody>
								<tr>
									<td><input type="checkbox" name="task_verification_notification[]" value="<?php echo TASK_VERIFICATION_NOTIFICATION_EMAIL; ?>" <?php if ($PROCESSED['task_verification_notification'] && ($PROCESSED['task_verification_notification'] & TASK_VERIFICATION_NOTIFICATION_EMAIL) ) { echo "checked='checked'"; } ?> /></td>
									<td colspan="2"><?php echo $translate->translate("task_field_verification_notificaiton_email"); ?></td>
								</tr>
								<tr>
									<td><input type="checkbox" name="task_verification_notification[]" disabled="disabled" value="<?php echo TASK_VERIFICATION_NOTIFICATION_DASHBOARD; ?>"  <?php if ($PROCESSED['task_verification_notification'] && ($PROCESSED['task_verification_notification'] & TASK_VERIFICATION_NOTIFICATION_DASHBOARD) ) { echo "checked='checked'"; } ?> /></td>
									<td colspan="2"><?php echo $translate->translate("task_field_verification_notification_dashboard"); ?></td>
								</tr>
								<tr><td colspan="3">&nbsp;</td></tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
			<tbody>
				<tr>
					<td colspan="3"><h2><?php echo $translate->translate("task_heading_time_release_options"); ?></h2></td>
				</tr>
					<?php echo generate_calendars("release", "", true, true, ((isset($PROCESSED["release_start"])) ? $PROCESSED["release_start"] : time()), true, false, ((isset($PROCESSED["release_finish"])) ? $PROCESSED["release_finish"] : 0)); ?>
			</tbody>
		</table>
	</form>
	<script type="text/javascript">
		function selectTaskRecipientsOption(type) {
			$$('.task_recipient').invoke('hide');
			$$('.'+type+'_recipient').invoke('show');
		}

		function selectTaskVerificationOption(type, event) {
			$$('.task_verification').invoke('hide');
			$$('.'+type+'_verification').invoke('show');
			switch(type) {
				case '<?php echo TASK_VERIFICATION_FACULTY; ?>':
				case '<?php echo TASK_VERIFICATION_OTHER; ?>':
					var target = $$('.'+type+'_anchor');
					if (target) target = target[0];
					else return;
					$$('.active_verification_options').each(function(element){
						target.insert({after:element});
					});
					$$('.active_verification_options').invoke('show');
					break;
				default:
					$$('.active_verification_options').invoke('hide');
			}
		}
	</script>
	<?php
		} 
	} else {
		header( "refresh:15;url=".ENTRADA_URL."/admin/".$MODULE );
		
		add_error("In order to edit a task you must provide a valid task identifier.");

		echo display_error();

		application_log("notice", "Failed to provide valid task identifer when attempting to edit a task.");
	}
}