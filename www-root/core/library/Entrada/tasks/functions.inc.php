<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Functions for tasks module that need to be accessible by both admin and public sections of the module
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
*/

require_once("Models/utility/Collection.class.php");
require_once("Models/utility/SimpleCache.class.php");

require_once("Models/users/User.class.php");
require_once("Models/users/Users.class.php");
require_once("Models/users/Assistant.class.php");
require_once("Models/users/UserPhoto.class.php");
require_once("Models/users/UserPhotos.class.php");
require_once("Models/users/Cohort.class.php");
require_once("Models/organisations/Organisation.class.php");
require_once("Models/organisations/Organisations.class.php");
require_once("Models/users/Department.class.php");
require_once("Models/users/Departments.class.php");
require_once("Models/events/EventContacts.class.php");
require_once("Models/events/Event.class.php");
require_once("Models/events/Events.class.php");
require_once("Models/courses/Course.class.php");
require_once("Models/courses/Courses.class.php");

require_once("Models/regions/Region.class.php");
require_once("Models/regions/Country.class.php");

require_once("Models/tasks/Tasks.class.php");
require_once("Models/tasks/Task.class.php");
require_once("Models/tasks/TaskOwners.class.php");
require_once("Models/tasks/TaskRecipients.class.php");
require_once("Models/tasks/TaskCompletions.class.php");
require_once("Models/tasks/TaskAssociatedFaculty.class.php");
require_once("Models/tasks/TaskVerifiers.class.php");

/**
 * Sends email based on the specified type using templates from TEMPLATE_ABSOLUTE/email directory
 * @param string $type One of "confirm", "request", "denial"
 * @param array $to associative array consisting of firstname, lastname, and email
 * @param array $keywords Associative array of keywords mapped to the replacement contents
 */
function task_verification_notification($type="",$to = array(), $keywords = array()) {
	global $AGENT_CONTACTS;
	if (!is_array($to) || !isset($to["email"]) || !valid_address($to["email"]) || !isset($to["firstname"]) || !isset($to["lastname"])) {
		application_log("error", "Attempting to send a task_verification_notification() however the recipient information was not complete.");
		
		return false;
	}
	
	if (!in_array($type, array("confirm", "request", "denial"))) {
		application_log("error", "Encountered an unrecognized notification type [".$type."] when attempting to send a task_verification_notification().");

		return false;
	}
	
	
	$xml_file = TEMPLATE_ABSOLUTE."/email/task-verification-".$type.".xml";
	
	try {
		require_once("Models/utility/Template.class.php");
		require_once("Models/utility/TemplateMailer.class.php");
		$template = new Template($xml_file);
		$mail = new TemplateMailer(new Zend_Mail());
		$mail->addHeader("X-Section", "Tasks Module", true);
		
		$from = array("email"=>$AGENT_CONTACTS["agent-notifications"]["email"], "firstname"=> "Task System","lastname"=>"");
		if ($mail->send($template,$to,$from,DEFAULT_LANGUAGE,$keywords)) {
			return true;
		} else {
			add_notice("We were unable to e-mail a task notification <strong>".$to["email"]."</strong>.<br /><br />A system administrator was notified of this issue, but you may wish to contact this individual manually and let them know their task verification status.");
			application_log("error", "Unable to send task verification notification to [".$to["email"]."] / type [".$type."]. Zend_Mail said: ".$mail->ErrorInfo);
		}
					
	} catch (Exception $e) {
		application_log("error", "Unable to load the XML file [".$xml_file."] or the XML file did not contain the language requested [".DEFAULT_LANGUAGE."], when attempting to send a regional education notification.");
	}

	return false;
}

/**
 * Generates the list of successful verifications when verifying task completion in bulk. 
 * @param array $task_successes 2-dimensional array consisting of "task name" => array_of_recipients pairs
 */
function generate_bulk_task_verify_success_list($task_successes) {
	$success_listing = "";
	foreach($task_successes as $task_title=>$recipients) {
		$success_listing .= "<div class='success_task'><span class='task_title'>".html_encode($task_title)."</span><ul>";
		foreach ($recipients as $recipient) {
			$success_listing .= "<li>".$recipient."</li>";
		}
		$success_listing .= "</ul></div>";
	}
	return $success_listing;
}

/**
 * Returns false if the $value is not a valid recipient type; returns $value otherwise
 * @param unknown_type $value
 * @return mixed
 */
function validate_recipient_type($value) {
	return validate_in_array($value, array(TASK_RECIPIENT_CLASS, TASK_RECIPIENT_USER, TASK_RECIPIENT_ORGANISATION));
}

/**
 * Returns false if the $value is not a valid comment policy; returns $value otherwise
 * @param unknown_type $value
 * @return mixed
 */
function validate_comment_policy($value) {
	return validate_in_array($value, array(TASK_COMMENT_ALLOW, TASK_COMMENT_NONE, TASK_COMMENT_REQUIRE));
}

/**
 * Returns false if the $value is not a valid faculty selection policy; returns $value otherwise
 * @param unknown_type $value
 * @return mixed
 */
function validate_faculty_selection_policy($value) {
	return validate_in_array($value, array(TASK_FACULTY_SELECTION_OFF, TASK_FACULTY_SELECTION_ALLOW, TASK_FACULTY_SELECTION_REQUIRE));
}

/**
 * Returns false if the $value is not a valid verification type; returns $value otherwise
 * @param unknown_type $value
 * @return mixed
 */
function validate_verification_type($value) {
	return validate_in_array($value, array(TASK_VERIFICATION_NONE, TASK_VERIFICATION_FACULTY, TASK_VERIFICATION_OTHER));
}


/**
 * Returns false if the $value is not a comma separated string of numbers or if the numbers are wildly invalid; returns $value otherwise
 * @param unknown_type $value
 * @return mixed
 */
function validate_verification_notification($value) {
	var_dump($value);
	$clean = filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0), 'flags', FILTER_REQUIRE_ARRAY));
	if (!$clean || !is_array($clean) || in_array(false, $clean)) { return TASK_DEFAULT_VERIFICATION_NOTIFICATION; }
	
	$notification_pol = array_reduce($clean, 'or_bin', 0);
	
	return array_pop($notification_pol);
}

function validate_task_details_inputs() {
	$args = array(
		'task_id' => array('filter' => FILTER_VALIDATE_INT, 'options' => array('min_range' => 1)),
		'title' => FILTER_SANITIZE_STRING,
		'time_required' => FILTER_SANITIZE_NUMBER_INT,
		'description' => array('filter'=> FILTER_CALLBACK, 'options' =>'allowed_tags'),
		'task_recipient_type' => array('filter'=> FILTER_CALLBACK, 'options' =>'validate_recipient_type'),
		'associated_faculty' => array('filter' => FILTER_CALLBACK, 'options' => 'validate_user_ids'),
		'associated_individual' => array('filter' => FILTER_CALLBACK, 'options' => 'validate_user_ids'),
		'associated_verifier' => array('filter' => FILTER_CALLBACK, 'options' => 'validate_user_ids'),
		'course_id' => array('filter'=> FILTER_VALIDATE_INT, 'options'=>array('min_range' => 0)),
		'faculty_selection_policy' => array('filter' => FILTER_CALLBACK, 'options' => 'validate_faculty_selection_policy'),
		'completion_comment_policy' => array('filter' => FILTER_CALLBACK, 'options' => 'validate_comment_policy'),
		'rejection_comment_policy' => array('filter' => FILTER_CALLBACK, 'options' => 'validate_comment_policy'),
		'task_verification_type' => array('filter' => FILTER_CALLBACK, 'options' => 'validate_verification_type'),
		'task_verification_notification' => array('filter'=> FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY, 'options'=>array('min_range' => 0)),
		'associated_cohorts' => FILTER_SANITIZE_INT, //could be more aggressive here but there's no permissionissues or db integrity issue particularly
		'associated_organisation_id' => array('filter' => FILTER_CALLBACK, 'options' => 'validate_organisation_id')
	);
	
	
	return filter_input_array(INPUT_POST, $args);
} 

function process_task_common_errors(array &$inputs) {
	global $translate, $ENTRADA_ACL;
	
	/** specially validated fields NOTE: validate_calendar(s) includes error reporting **/ 

	$deadline = validate_calendar("Deadline","deadline", true, false);
	if((isset($deadline)) && ((int) $deadline)) {
		$inputs["deadline"] = (int) $deadline;
	}

	$release = validate_calendars("release",true,false,true);
	if ($release) {
		if ($release['start']) {
			$inputs['release_start'] = $release['start']; 
		}
		if ($release['finish']) {
			$inputs['release_finish'] = $release['finish']; 
		}
		
	}

	/** General Info **/ 
	
	if (0 === mb_strlen($inputs['title'])) {
		add_error($translate->translate("task_title_too_short"));
	} elseif (Task::TITLE_MAX_LENGTH < mb_strlen($inputs['title'])) {
		add_notice($translate->translate("task_title_too_long"));
		$inputs['title'] = mb_substr($inputs['title'], 0, TASK::TITLE_MAX_LENGTH);
	}
	
	if (0 > $inputs['time_required']) { //can't be negative
		add_error($translate->translate("task_time_required_invalid"));
	} 
	
	if (Task::DURATION_MAX < $inputs['time_required']) {
		add_error(str_replace("%MAX_TIME_REQUIRED%",Task::DURATION_MAX,$translate->translate("task_time_required_too_long")));
	}
	
	/** Recipient Options **/
	
	if (is_null($inputs['task_recipient_type'])) {
		add_error($translate->translate("task_recipient_type_invalid"));
	} elseif (TASK_RECIPIENT_USER === $inputs['task_recipient_type']) {
		if (is_null($inputs['associated_individual'])) {
			add_error($translate->translate("task_recipient_individual_empty"));
		}
	} elseif (TASK_RECIPIENT_CLASS === $inputs['task_recipient_type']) {
		if (false === $inputs['associated_cohorts']) {
			add_error($translate->translate("task_recipient_cohort_missing"));
		}
	} elseif (TASK_RECIPIENT_ORGANISATION === $inputs['task_recipient_type']) {
		if (is_null($inputs["associated_organisation_id"])) {
			add_error($translate->translate("task_organisation_invalid"));					
		} elseif(!$ENTRADA_ACL->amIAllowed('resourceorganisation'.$inputs["associated_organisation_id"], 'create')) {
			add_error($translate->translate("task_organisation_permission_fail"));					
		}
	}
	
	if (is_null($inputs['course_id'])) {
		add_error($translate->translate("task_course_invalid"));
		$inputs['course_id'] = 0; //reset to none	
	} else {
		if (($inputs['course_id'] !== 0) && !$ENTRADA_ACL->amIAllowed(new TaskResource(null,$course_id,$ORGANISATION_ID), "create")) {
			add_error($translate->translate("task_course_permission_fail"));
			//add_error("You do not have permission to add a task for the course you selected. <br />Please re-select the course you would like to associate with this task.");
			application_log("error", "A program coordinator attempted to add a task to a course [".$course_id."] they were not the coordinator of.");
			$inputs['course_id'] = 0; //reset to none	
		}
	}
	
	/** Task Completion Options **/
	
	if (is_null($inputs['completion_comment_policy'])) {
		add_error($translate->translate("task_completion_comment_policy_invalid"));
	}
	
	if (is_null($inputs['rejection_comment_policy'])) {
		add_error($translate->translate("task_rejection_comment_policy_invalid"));
	}
	
	/** Veririfcation options **/
	
	if (is_null($inputs['task_verification_type'])) {
		add_error($translate->translate("task_verification_type_invalid"));
	} elseif ((is_null($inputs['associated_faculty'])) && (TASK_VERIFICATION_FACULTY === $inputs['task_verification_type'])) {
		add_error($translate->translate("task_no_faculty_and_faculty_verification"));
	} elseif ((is_null($inputs['associated_verifier'])) && (TASK_VERIFICATION_OTHER === $inputs['task_verification_type'])) {
		add_error($translate->translate("task_verification_no_verifier"));
	}
}