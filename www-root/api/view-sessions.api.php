<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Display Clerkship logbook entries in various order.
 *
 * @author Organisation: University of Calgary
 * @author Unit: Undergraduate Medical Education
 * @author Developer: Doug Hall <hall@ucalgary.ca>
 * @copyright Copyright 2009 University of Calgary. All Rights Reserved.
 *
 * $Id: view-entries.api.php 1 2009-11-20 19:36:06Z hall $
*/

@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    dirname(__FILE__) . "/../core/includes",
    dirname(__FILE__) . "/../core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

if ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('eventcontent', 'update', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	
	if (isset($_POST["event_id"]) && $_POST["event_id"]) {
		$event_id = clean_input($_POST["event_id"], array("trim", "int"));
	} elseif (isset($_GET["id"]) && $_GET["id"]) {
		$event_id = clean_input($_GET["id"], array("trim", "int"));
	} else {
		$event_id = false;
	}
	
	$temp_event_id = $event_id;
	
	if (isset($_POST["parent_id"]) && $_POST["parent_id"]) {
		$parent_id = clean_input($_POST["parent_id"], array("trim", "int"));
	} elseif (isset($_GET["parent_id"]) && $_GET["parent_id"]) {
		$parent_id = clean_input($_GET["parent_id"], array("trim", "int"));
	} else {
		$parent_id = false;
	}
	
	if (isset($_POST["hide_controls"]) && $_POST["hide_controls"]) {
		$display_controls = false;
	} else {
		$display_controls = true;
	}
	
	if ($event_id) {
		$query = "	SELECT a.*, b.`organisation_id`
					FROM `events` AS a
					JOIN `events` AS aa
					ON a.`parent_id` = aa.`event_id`
					LEFT JOIN `courses` AS b
					ON b.`course_id` = aa.`course_id`
					WHERE a.`event_id` = ".$db->qstr($event_id)."
					AND b.`course_active` = '1'";
		$event_info	= $db->GetRow($query);	
		$course_id = $event_info["course_id"];
	} else {
		$event_info = false; 
	}
	
	if ((isset($parent_id) && $parent_id) || (isset($event_info["parent_id"]) && $event_info["parent_id"])) {
		$query = "SELECT a.*, b.`organisation_id`
					FROM `events` AS a
					LEFT JOIN `courses` AS b
					ON b.`course_id` = a.`course_id`
					WHERE a.`event_id` = ".$db->qstr((isset($parent_id) && $parent_id) ? $parent_id : $event_info["parent_id"])."
					AND b.`course_active` = '1'";
		$parent_info = $db->GetRow($query);
		$course_id = $parent_info["course_id"];
	} else {
		$parent_info = false; 
	}
	
	if ($ENTRADA_ACL->amIAllowed(new EventContentResource($event_info["event_id"], $parent_info["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update') || $ENTRADA_ACL->amIAllowed(new EventContentResource($parent_info["event_id"], $parent_info["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update')) {
		if (isset($_POST["step"]) && $_POST["step"] && $_POST["step"] == 2) {
			
			if ($ENTRADA_ACL->amIAllowed(new EventResource($event_info["event_id"], $event_info["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update')) {
				/**
				 * Required field "event_start" / Event Date & Time Start (validated through validate_calendars function).
				 */
				$start_date = validate_calendars("event", true, false);
				if ((isset($start_date["start"])) && ((int) $start_date["start"])) {
					$PROCESSED["event_start"] = (int) $start_date["start"];
				}
			
				/**
				 * Non-required field "event_location" / Event Location
				 */
				if ((isset($_POST["event_location"])) && ($event_location = clean_input($_POST["event_location"], array("notags", "trim")))) {
					$PROCESSED["event_location"] = $event_location;
				}
				
				/**
				 * Required field "event_title" / Event Title.
				 */
				if ((isset($_POST["event_title"])) && ($event_title = clean_input($_POST["event_title"], array("notags", "trim")))) {
					$PROCESSED["event_title"] = $event_title;
				}
				
				if ((isset($_POST["students_session_audience"]))) {
					$associated_audience = explode(',', $_POST["students_session_audience"]);
					if ((isset($associated_audience)) && (is_array($associated_audience)) && (count($associated_audience))) {
						foreach($associated_audience as $audience_id) {
							if (strpos($audience_id, "student") !== false) {
								if ($proxy_id = clean_input(preg_replace("/[a-z_]/", "", $audience_id), array("trim", "int"))) {
									$query = "	SELECT a.*
												FROM `".AUTH_DATABASE."`.`user_data` AS a
												LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
												ON a.`id` = b.`user_id`
												WHERE a.`id` = ".$db->qstr($proxy_id)."
												AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
												AND b.`account_active` = 'true'
												AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
												AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")";
									$result	= $db->GetRow($query);
									if ($result) {
										$PROCESSED["associated_proxy_ids"][] = $proxy_id;
									}
								}
							}
						}
					}
				}
				
				if ((isset($_POST["groups_session_audience"]))) {
					$associated_audience = explode(',', $_POST["groups_session_audience"]);
					if ((isset($associated_audience)) && (is_array($associated_audience)) && (count($associated_audience))) {
						foreach($associated_audience as $audience_id) {
							if (strpos($audience_id, "group") !== false) {
								if ($group_id = clean_input(preg_replace("/[a-z_]/", "", $audience_id), array("trim", "int"))) {
									$query = "	SELECT *
												FROM `groups`
												WHERE `group_id` = ".$db->qstr($group_id)."
												AND `group_active` = 1";
									$result	= $db->GetRow($query);
									if ($result) {
										$PROCESSED["associated_group_ids"][] = $group_id;
									}
								}
							}
						}
					}
				}
				
				if ((isset($_POST["course_session_audience"])) && $_POST["course_session_audience"]) {
					if ($course_id || ($course_id = $PROCESSED["course_id"])) {
						$query = "SELECT *
									FROM `courses`
									WHERE `course_id` = ".$db->qstr($course_id)."
									AND `course_active` = 1";
						$result	= $db->GetRow($query);
						if ($result) {
							$PROCESSED["associated_course_ids"][] = $course_id;
						}
					}
				}
				
				if (!count($PROCESSED["associated_group_ids"]) && !count($PROCESSED["associated_proxy_ids"]) && !count($PROCESSED["associated_course_ids"])) {
					$ERROR++;
					$ERRORSTR[] = "You have not chosen an <strong>Audience</strong> for the session you were editing, please choose an audience type from the drop-down.";
				}
				
				/**
				 * Non-required field "associated_faculty" / Associated Faculty (array of proxy ids).
				 * This is actually accomplished after the event is inserted below.
				 */
				if ((isset($_POST["associated_session_faculty"]))) {
					$associated_session_faculty = explode(',',$_POST["associated_session_faculty"]);
					foreach($associated_session_faculty as $contact_order => $proxy_id) {
						if ($proxy_id = clean_input($proxy_id, array("trim", "int"))) {
							$PROCESSED["associated_faculty"][(int) $contact_order] = $proxy_id;
						}
					}
				}
			}
			
			/**
			 * Event Description
			 */
			if ((isset($_POST["session_description"])) && (clean_input($_POST["session_description"], array("notags", "nows")))) {
				$PROCESSED["event_description"] = clean_input($_POST["session_description"], array("allowedtags"));
			}
			
			/**
			 * Include Parent Event's Description
			 */
			if ((isset($_POST["include_parent_description"])) && (clean_input($_POST["include_parent_description"], array("nows", "lower"))) == "true") {
				$PROCESSED["include_parent_description"] = 1;
			} else {
				$PROCESSED["include_parent_description"] = 0;
			}
			/**
			 * Teacher's Message
			 */
			if ((isset($_POST["session_message"])) && (clean_input($_POST["session_message"], array("notags", "nows")))) {
				$PROCESSED["event_message"] = clean_input($_POST["session_message"], array("allowedtags"));
			}
			
			/**
			 * Include Parent Event's Teacher's Message
			 */
			if ((isset($_POST["include_parent_message"])) && (clean_input($_POST["include_parent_message"], array("nows", "lower"))) == "true") {
				$PROCESSED["include_parent_message"] = 1;
			} else {
				$PROCESSED["include_parent_message"] = 0;
			}
				
			
			if (!$ERROR) {
				$PROCESSED["updated_date"] = time();
				$PROCESSED["updated_by"] = $_SESSION["details"]["id"];
				$PROCESSED["parent_id"] = $parent_id;
				if ($ENTRADA_ACL->amIAllowed(new EventResource($event_info["event_id"], $event_info["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update')) {
					$PROCESSED["event_finish"] = $PROCESSED["event_start"];
					$PROCESSED["event_duration"] = 0;
				
					/**
					 * Add existing event type segments to the processed array.
					 */
					$query = "	SELECT *
								FROM `event_eventtypes` AS `types`
								LEFT JOIN `events_lu_eventtypes` AS `lu_types`
								ON `lu_types`.`eventtype_id` = `types`.`eventtype_id`
								WHERE `event_id` = ".$db->qstr($parent_id)."
								ORDER BY `types`.`eeventtype_id` ASC";
					$results = $db->GetAll($query);
					if ($results) {
						foreach ($results as $contact_order => $result) {
							$parent_info["event_types"][] = array($result["eventtype_id"], $result["duration"], $result["eventtype_title"]);
						}
					}
					if (isset($parent_info["event_types"])) {
						foreach($parent_info["event_types"] as $event_type) {
							$PROCESSED["event_finish"] += $event_type[1]*60;
							$PROCESSED["event_duration"] += $event_type[1];
						}
					}
				}
				$query = "SELECT `course_id` FROM `events` WHERE `event_id` = ".$db->qstr($parent_id);
				if ($course_id = $db->GetOne($query)) {
					$PROCESSED["course_id"] = ((int) $course_id);
				}
	
				$PROCESSED["eventtype_id"] = 0;
				if (($event_info && $db->AutoExecute("events", $PROCESSED, "UPDATE", "`event_id` = ".$db->qstr($event_id))) || ((!$event_info && $db->AutoExecute("events", $PROCESSED, "INSERT")))) {
					if (!$event_info) {
						$event_id = $db->Insert_Id();
					}
					if ($ENTRADA_ACL->amIAllowed(new EventResource($event_info["event_id"], $event_info["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update')) {
						$query = "DELETE FROM `event_audience` WHERE `event_id` = ".$db->qstr($event_id);
						if ($db->Execute($query)) {
							if (count($PROCESSED["associated_course_ids"])) {
								if ($PROCESSED["associated_course_ids"]) {
									if (!$db->AutoExecute("event_audience", array("event_id" => $event_id, "audience_type" => "course_id", "audience_value" => (int) $course_id, "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
										$ERROR++;
										$ERRORSTR[] = "There was an error while trying to attach the selected <strong>Course List</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.";
		
										application_log("error", "Unable to insert a new event_audience, course_id record while adding a new event. Database said: ".$db->ErrorMsg());
									}
								}
							}
							if (count($PROCESSED["associated_group_ids"])) {
								foreach($PROCESSED["associated_group_ids"] as $group_id) {
									if (!$db->AutoExecute("event_audience", array("event_id" => $event_id, "audience_type" => "group_id", "audience_value" => (int) $group_id, "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
										$ERROR++;
										$ERRORSTR[] = "There was an error while trying to attach the selected <strong>Group</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.";
		
										application_log("error", "Unable to insert a new event_audience, group_id record while adding a new event. Database said: ".$db->ErrorMsg());
									}
								}
							}
							if (isset($PROCESSED["associated_proxy_ids"]) && count($PROCESSED["associated_proxy_ids"])) {
								foreach($PROCESSED["associated_proxy_ids"] as $proxy_id) {
									if (!$db->AutoExecute("event_audience", array("event_id" => $event_id, "audience_type" => "proxy_id", "audience_value" => (int) $proxy_id, "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
										$ERROR++;
										$ERRORSTR[] = "There was an error while trying to attach the selected <strong>Proxy ID</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.";
		
										application_log("error", "Unable to insert a new event_audience, proxy_id record while adding a new event. Database said: ".$db->ErrorMsg());
									}
								}
							}
							if ((!isset($PROCESSED["associated_proxy_ids"]) || !count($PROCESSED["associated_proxy_ids"])) && (!isset($PROCESSED["associated_group_ids"]) || !count($PROCESSED["associated_group_ids"])) && (!isset($PROCESSED["associated_course_ids"]) || !count($PROCESSED["associated_course_ids"]))) {
								application_log("error", "No audience added for event_id [".$event_id."].");
							}
						} else {
							application_log("error", "Unable to delete audience details from event_audience table during an edit. Database said: ".$db->ErrorMsg());
						}
		
						/**
						 * If there are faculty associated with this event, add them
						 * to the event_contacts table.
						 */
						$query = "DELETE FROM `event_contacts` WHERE `event_id` = ".$db->qstr($event_id);
						if ($db->Execute($query)) {
							if (isset($PROCESSED["associated_faculty"]) && (is_array($PROCESSED["associated_faculty"])) && (count($PROCESSED["associated_faculty"]))) {
								foreach($PROCESSED["associated_faculty"] as $contact_order => $proxy_id) {
									if (!$db->AutoExecute("event_contacts", array("event_id" => $event_id, "proxy_id" => $proxy_id, "contact_order" => (int) $contact_order, "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
										$ERROR++;
										$ERRORSTR[] = "There was an error while trying to attach an <strong>Associated Faculty</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.";
		
										application_log("error", "Unable to insert a new event_contact record while adding a new event. Database said: ".$db->ErrorMsg());
									}
								}
							}
						}
					}
	
					$SUCCESS++;
					if ($event_info) {
						$SUCCESSSTR[] = "You have successfully edited <strong>".html_encode((isset($PROCESSED["event_title"]) && $PROCESSED["event_title"] ? $PROCESSED["event_title"] : $event_info["event_title"]))."</strong> in the system.";
					} else {
						$SUCCESSSTR[] = "You have successfully created a new session [<strong>".html_encode($PROCESSED["event_title"])."</strong>] in the system.";
					}
	
					application_log("success", "Event [".$event_id."] has been modified.");
				} else {
					$ERROR++;
					$ERRORSTR[] = "There was a problem updating this event in the system. The system administrator was informed of this error; please try again later.";
	
					application_log("error", "There was an error updating event_id [".$event_id."]. Database said: ".$db->ErrorMsg());
				}
			} else {
				application_log("error", print_r($ERRORSTR, true));
			}
			if (isset($_POST["new"]) && $_POST["new"]) {
				$PROCESSED = array("event_start" => $PROCESSED["event_start"]);
				if (!$temp_event_id) {
					echo "<input type=\"hidden\" value=\"".$event_id."\" id=\"updated_session_id\" />";
				}
				$event_id = 0;
			}
		} else {
			$PROCESSED	= $event_info;
			if (isset($_POST["event_start"]) && $_POST["event_start"]) {
				/**
				 * Required field "event_start" / Event Date & Time Start (validated through validate_calendars function).
				 */
				$start_date = validate_calendars("event", false, false);
				if ((isset($start_date["start"])) && ((int) $start_date["start"])) {
					$PROCESSED["event_start"] = (int) $start_date["start"];
				}
			}
		}
		if ($parent_id) {
			$query = "SELECT COUNT(*) FROM `events` WHERE `parent_id` = ".$db->qstr($parent_id);
			$session_count = $db->GetOne($query);
			$session_count = ($session_count ? $session_count + 1 : 1);
		} else {
			$session_count = 1;
		}
		/**
		 * Compiles the full list of faculty members.
		 */
		$FACULTY_LIST = array();
		$query = "	SELECT a.`id` AS `proxy_id`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, a.`organisation_id`
					FROM `".AUTH_DATABASE."`.`user_data` AS a
					LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
					ON b.`user_id` = a.`id`
					WHERE b.`app_id` = '".AUTH_APP_ID."'
					AND (b.`group` = 'faculty' OR (b.`group` = 'resident' AND b.`role` = 'lecturer'))
					ORDER BY a.`lastname` ASC, a.`firstname` ASC";
		$results = $db->GetAll($query);
		if ($results) {
			foreach($results as $result) {
				$FACULTY_LIST[$result["proxy_id"]] = array('proxy_id'=>$result["proxy_id"], 'fullname'=>$result["fullname"], 'organisation_id'=>$result['organisation_id']);
			}
		}
		/**
		 * Compiles the list of students.
		 */
		$STUDENT_LIST = array();
		$query = "	SELECT a.`id` AS `proxy_id`, b.`role`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, a.`organisation_id`
					FROM `".AUTH_DATABASE."`.`user_data` AS a
					LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
					ON a.`id` = b.`user_id`
					WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
					AND b.`account_active` = 'true'
					AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
					AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
					AND b.`group` = 'student'
					AND b.`role` >= '".(date("Y") - ((date("m") < 7) ?  2 : 1))."'
					ORDER BY b.`role` ASC, a.`lastname` ASC, a.`firstname` ASC";
		$results = $db->GetAll($query);
		if ($results) {
			foreach($results as $result) {
				$STUDENT_LIST[$result["proxy_id"]] = array('proxy_id'=>$result["proxy_id"], 'fullname'=>$result["fullname"], 'organisation_id'=>$result['organisation_id']);
			}
		}
		/**
		 * Compiles the list of groups.
		 */
		$GROUP_LIST = array();
		$query = "	SELECT *
					FROM `groups`
					WHERE `group_active` = '1'
					ORDER BY `group_name`";
		$results = $db->GetAll($query);
		if ($results) {
			foreach($results as $result) {
				$GROUP_LIST[$result["group_id"]] = $result;
			}
		}
	
		/**
		 * Add any existing associated faculty from the event_contacts table
		 * into the $PROCESSED["associated_faculty"] array.
		 */
		$query = "SELECT * FROM `event_contacts` WHERE `event_id` = ".$db->qstr($event_id)." ORDER BY `contact_order` ASC";
		$results = $db->GetAll($query);
		if ($results) {
			foreach($results as $contact_order => $result) {
				$PROCESSED["associated_faculty"][(int) $contact_order] = $result["proxy_id"];
			}
		}
		$query = "SELECT * FROM `event_audience` WHERE `event_id` = ".$db->qstr($event_id);
		$results = $db->GetAll($query);
		if ($results) {
			/**
			 * Set the audience_type.
			 */
			$PROCESSED["event_audience_type"] = $results[0]["audience_type"];
			
			foreach($results as $result) {
				switch($result["audience_type"]) {
					case "course_id" :
						$PROCESSED["associated_course_ids"][] = (int) $result["audience_value"];
					break;
					case "group_id" :
						$PROCESSED["associated_group_ids"][] = (int) $result["audience_value"];
					break;
					case "proxy_id" :
						$PROCESSED["associated_proxy_ids"][] = (int) $result["audience_value"];
					break;
					case "cohort" :
						$query = "SELECT `group_id` FROM `groups` WHERE `group_id` = ".$db->qstr($result["audience_value"])." AND `group_active` = 1";
						$group_id = $db->GetOne($query);
						if ($group_id) {
							$PROCESSED["associated_group_ids"][] = (int) $group_id;
						}
					break;
				}
			}
		}
		if ($SUCCESS) {
			echo display_success();
			echo "<input type=\"hidden\" id=\"success\" value=\"1\">";
		}
		if ($ERROR) {
			echo display_error();
			echo "<input type=\"hidden\" id=\"success\" value=\"0\">";
		}
		if ($NOTICE) {
			echo display_notice();
		}
		if ($display_controls) {
			?>
			<div style="position: relative;">
				<?php
		        $query 	= "	SELECT * FROM `groups`
		        		WHERE `group_active` = 1
		        		ORDER BY `group_name`";
		        $group_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
		        if ($group_results) {
		            foreach ($group_results as $r) {
						$checked = (isset($PROCESSED["associated_group_ids"]) && array_search($r["group_id"], $PROCESSED["associated_group_ids"]) !== false ? "checked=\"checked\"" : "");
		                $groups[$r["group_id"]] = array('text' => $r['group_name'], 'value' => 'group_'.$r['group_id'], 'checked' => $checked);
		            }
		            echo lp_multiple_select_popup('groups', $groups, array('title'=>'Select Groups:', 'cancel_text'=>'Close', 'cancel'=>true, 'class'=>'audience_dialog'));
		        }
				
		        $query 	= "	SELECT CONCAT_WS(' ', a.`firstname`, a.`lastname`) AS `fullname`, a.`id` AS `proxy_id`, b.`role` FROM `".AUTH_DATABASE."`.`user_data` AS a
		        		JOIN `".AUTH_DATABASE."`.`user_access` AS b
		        		ON a.`id` = b.`user_id`
		        		AND b.`app_id` IN (".AUTH_APP_IDS_STRING.")
		        		WHERE b.`group` = 'student'
						AND b.`role` >= '".(date("Y") - ((date("m") < 7) ?  2 : 1))."'
		        		AND b.`account_active` = 'true'
		        		AND (b.`access_starts` <= ".$db->qstr(time())." OR b.`access_starts` = 0)
		        		AND (b.`access_expires` >= ".$db->qstr(time())." OR b.`access_expires` = 0)";
		        $student_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
		        if ($student_results) {
		            foreach ($student_results as $r) {
	                    $checked = (isset($PROCESSED["associated_proxy_ids"]) && array_search($r["proxy_id"], $PROCESSED["associated_proxy_ids"]) !== false ? "checked=\"checked\"" : "");
		
		                $students[$r["role"]]['options'][] = array('text' => $r['fullname'], 'value' => 'proxy_'.$r['proxy_id'], 'checked' => $checked);
		            }
		            echo lp_multiple_select_popup('students', $students, array('title'=>'Select Students:', 'cancel_text'=>'Close', 'cancel'=>true, 'class'=>'audience_dialog'));
		        }
				?>
			</div>
			<input type="hidden" name="step" value="2" />
			<input type="hidden" name="session_id" value="<?php echo $event_id; ?>" />
			<input type="hidden" value="<?php echo (isset($PROCESSED["event_title"]) && $PROCESSED["event_title"] ? $PROCESSED["event_title"] : "Session ".$session_count); ?>" id="session_title" name="session_title" />
			<table style="width: 100%" cellspacing="0" cellpadding="0" border="0" summary="Event Session Information">
				<colgroup>
					<col style="width: 5%" />
					<col style="width: 20%" />
					<col style="width: 75%" />
				</colgroup>
				<?php
				if ($ENTRADA_ACL->amIAllowed(new EventResource($PROCESSED["event_id"], $PROCESSED["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update')) {
					echo generate_calendar("event_start", "Date and Time", true, $PROCESSED["event_start"]);
				?>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td></td>
					<td><label for="event_location" class="form-nrequired">Session Location</label></td>
					<td><input type="text" id="event_location" name="event_location" value="<?php echo $PROCESSED["event_location"]; ?>" maxlength="255" style="width: 203px" /></td>
				</tr>
				<?php
				} else {
				?>
				<tr>
					<td colspan="3"><h3>Session Details</h3></td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>Event Date &amp; Time</td>
					<td><?php echo date(DEFAULT_DATE_FORMAT, $PROCESSED["event_start"]); ?></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>Event Location</td>
					<td><?php echo (($PROCESSED["event_location"]) ? $PROCESSED["event_location"] : "To Be Announced"); ?></td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td style="vertical-align: top">Associated Faculty</td>
					<td>
						<?php
						$query		= "	SELECT a.`proxy_id`, CONCAT_WS(' ', b.`firstname`, b.`lastname`) AS `fullname`, a.`contact_role`, b.`email`
										FROM `event_contacts` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
										ON b.`id` = a.`proxy_id`
										WHERE a.`event_id` = ".$db->qstr($PROCESSED["event_id"])."
										AND b.`id` IS NOT NULL
										ORDER BY a.`contact_order` ASC";
						$session_contacts	= $db->GetAll($query);
						if ($session_contacts) {
							foreach ($session_contacts as $key => $fresult) {
								echo "<a href=\"mailto:".html_encode($fresult["email"])."\">".html_encode($fresult["fullname"])."</a> - ".(($fresult["contact_role"] == "ta")?"Teacher's Assistant":html_encode(ucwords($fresult["contact_role"])))."<br />\n";
							}
						} else {
							echo "To Be Announced";
						}
						?>
					</td>
				</tr>
				<?php
				}
				?>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td></td>
					<td style="vertical-align: top">Session Description</td>
					<td>
						<textarea id="session_description" name="session_description" style="width: 90%; height: 80px" cols="70" rows="10"><?php echo html_encode(trim(strip_selected_tags($PROCESSED["event_description"], array("font")))); ?></textarea>
					</td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td><input type="checkbox" value="1" id="include_parent_description" name="include_parent_description"<?php echo (!isset($PROCESSED["include_parent_description"]) || $PROCESSED["include_parent_description"] ? " checked=\"checked\"" : "" ); ?> /></td>
					<td colspan="2">
						<label for="include_parent_description" class="form-nrequired">Include <strong>Event Description</strong> from parent event</label>
					</td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td></td>
					<td style="vertical-align: top">
						Teacher's Message
						<div class="content-small" style="margin-top: 10px">
							<strong>Note:</strong> You can use this to provide your learners with instructions or information they need for this class.
						</div>
					</td>
					<td>
						<textarea id="session_message" name="session_message" style="width: 90%; height: 80px" cols="70" rows="10"><?php echo html_encode(trim(strip_selected_tags($PROCESSED["event_message"], array("font")))); ?></textarea>
					</td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td><input type="checkbox" value="1" id="include_parent_message" name="include_parent_message"<?php echo (!isset($PROCESSED["include_parent_message"]) || $PROCESSED["include_parent_message"] ? " checked=\"checked\"" : "" ); ?> /></td>
					<td colspan="2">
						<label for="include_parent_message" class="form-nrequired">Include <strong>Teacher's Message</strong> from parent event</label>
					</td>
				</tr>
				<?php
				if ($ENTRADA_ACL->amIAllowed(new EventResource($PROCESSED["event_id"], $PROCESSED["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update')) {
				?>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td></td>
					<td style="vertical-align: top"><label for="session_faculty_name" class="form-nrequired">Associated Faculty</label></td>
					<td>
						<input type="text" id="session_faculty_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle" />
						<div class="autocomplete" id="session_faculty_name_auto_complete"></div>
						<input type="hidden" id="associated_session_faculty" name="associated_session_faculty" />
						<input type="button" class="button-sm" id="add_associated_session_faculty" value="Add" style="vertical-align: middle" />
						<span class="content-small">(<strong>Example:</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?>)</span>
						<ul id="session_faculty_list" class="menu" style="margin-top: 15px">
							<?php
							$ONLOAD[] = "session_faculty_list = new AutoCompleteList({ type: 'session_faculty', url: '". ENTRADA_RELATIVE ."/api/personnel.api.php?type=faculty', remove_image: '". ENTRADA_RELATIVE ."/images/action-delete.gif'})";
							if (is_array($PROCESSED["associated_faculty"]) && count($PROCESSED["associated_faculty"])) {
								foreach ($PROCESSED["associated_faculty"] as $faculty) {
									if ((array_key_exists($faculty, $FACULTY_LIST)) && is_array($FACULTY_LIST[$faculty])) {
										?>
										<li class="community" id="session_faculty_<?php echo $FACULTY_LIST[$faculty]["proxy_id"]; ?>" style="cursor: move;"><?php echo $FACULTY_LIST[$faculty]["fullname"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="session_faculty_list.removeItem('<?php echo $FACULTY_LIST[$faculty]["proxy_id"]; ?>');" class="list-cancel-image" /></li>
										<?php
									}
								}
							}
							?>
						</ul>
						<input type="hidden" id="session_faculty_ref" name="session_faculty_ref" value="" />
						<input type="hidden" id="session_faculty_id" name="session_faculty_id" value="" />
					</td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td></td>
					<td><label for="audience_type" class="form-nrequired">Audience Type</label></td>
					<td>
						<?php
						$query = "SELECT * FROM `event_audience` WHERE `event_id` = ".$db->qstr($PROCESSED["event_id"]);
						$audience_results = $db->GetAll($query);
						if ($audience_results) {
							/**
							 * Set the audience_type.
							 */
							$PROCESSED["event_audience_type"] = $audience_results[0]["audience_type"];
							$PROCESSED["associated_course_ids"] = array();
							$PROCESSED["associated_group_ids"] = array();
							$PROCESSED["associated_proxy_ids"] = array();
							
							foreach($audience_results as $audience) {
								switch($audience["audience_type"]) {
									case "course_id" :
										$PROCESSED["associated_course_ids"][] = (int) $audience["audience_value"];
									break;
									case "group_id" :
										$PROCESSED["associated_group_ids"][] = (int) $audience["audience_value"];
									break;
									case "proxy_id" :
										$PROCESSED["associated_proxy_ids"][] = (int) $audience["audience_value"];
									break;
									case "cohort" :
										$query = "SELECT `group_id` FROM `groups` WHERE `group_id` = ".$db->qstr($PROCESSED["audience_value"])." AND `group_active` = 1";
										$group_id = $db->GetOne($query);
										if ($group_id) {
											$PROCESSED["associated_group_ids"][] = (int) $group_id;
										}
									break;
								}
							}
						}
						$group_ids_string = "";
						$student_ids_string = "";
						if (isset($PROCESSED["associated_course_ids"]) && $PROCESSED["associated_course_ids"]) {
							$course_audience_included = true;
						} else {
							$course_audience_included = false;
						}
						foreach ($PROCESSED["associated_group_ids"] as $group_id) {
							if ($group_ids_string) {
								$group_ids_string .= ",group_".$group_id;
							} else {
								$group_ids_string = "group_".$group_id; 
							}
						}
						foreach ($PROCESSED["associated_proxy_ids"] as $proxy_id) {
							if ($student_ids_string) {
								$student_ids_string .= ",student_".$proxy_id;
							} else {
								$student_ids_string = "student_".$proxy_id; 
							}
						}
						?>
						<input type="hidden" id="groups_audience_head" name="groups_session_audience" value="<?php echo $group_ids_string; ?>" />
						<input type="hidden" id="students_audience_head" name="students_session_audience" value="<?php echo $student_ids_string; ?>" />
						<input type="hidden" id="course_audience_head" name="course_session_audience" value="<?php echo $course_audience_included ? "1" : "0"; ?>" />
						<script type="text/javascript">
							var multiselect = [];
							var id;
							function showMultiSelect() {
								id = $F('audience_select');
								if (id != 'course') {
									$$('select_multiple_container').invoke('hide');
									if (multiselect[id]) {
										$('audience_select').hide();
										multiselect[id].container.show();
										multiselect[id].container.down("input").activate();
									} else {
										if ($(id+'_options')) {
											$(id+'_options').addClassName('multiselect-processed');
											multiselect[id] = new Control.SelectMultiple(id+'_audience_head',id+'_options',{
												checkboxSelector: 'table.select_multiple_table tr td input[type=checkbox]',
												nameSelector: 'table.select_multiple_table tr td.select_multiple_name label',
												resize: id+'_scroll',
												afterCheck: function(element) {
													if (element.checked) {
														addAudience(element.id, id);
													} else {
														removeAudience(element.id, id);
													}
												}
											});
	
											$(id+'_cancel').observe('click',function(event){
												this.container.hide();
												$('audience_select').show();
												$('audience_select').options.selectedIndex = 0;
												return false;
											}.bindAsEventListener(multiselect[id]));
	
											$('audience_select').hide();
											multiselect[id].container.show();
											multiselect[id].container.down("input").activate();
										}
									}
								} else if (!$('audience_course')) {
									if (!$('audience_course') || !$('audience_course').value) {
										$('audience_list').innerHTML += '<li class="community" id="audience_course" style="cursor: move;">'+$('event_course_name').value+'<img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeAudience(\'course\', \'course\');" class="list-cancel-image" /></li>';
										$$('#audience_list div').each(function (e) { e.hide(); });
										Sortable.destroy('audience_list');
										Sortable.create('audience_list');
										$('audience_select').options.selectedIndex = 0;
										$('course_audience_head').value = 1;
									}
								}
								return false;
							}
						</script>
						<select id="audience_select" onchange="showMultiSelect()">
							<option value="">- Select Audience Type -</option>
							<option value="groups">Groups</option>
							<option value="students">Individual Students</option>
							<option value="course">Course List</option>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						&nbsp;
					</td>
					<td>
						<div style="position: relative; width: 60%">
							<input type="hidden" id="associated_audience" name="associated_audience" />
							<ul class="menu" id="audience_list">
								<?php
								$ONLOAD[] = "Sortable.create('audience_list')";
								if (is_array($PROCESSED["associated_proxy_ids"]) && count($PROCESSED["associated_proxy_ids"])) {
									foreach ($PROCESSED["associated_proxy_ids"] as $student) {
										if ((array_key_exists($student, $STUDENT_LIST)) && is_array($STUDENT_LIST[$student])) {
											?>
											<li class="community" id="audience_student_<?php echo $STUDENT_LIST[$student]["proxy_id"]; ?>" style="cursor: move;"><?php echo $STUDENT_LIST[$student]["fullname"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeAudience('student_<?php echo $STUDENT_LIST[$student]["proxy_id"]; ?>', 'students');" class="list-cancel-image" /></li>
											<?php
										}
									}
								}
								if (is_array($PROCESSED["associated_course_ids"]) && count($PROCESSED["associated_course_ids"])) {
									?>
									<li class="community" id="audience_course" style="cursor: move;"><?php echo $course_name; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeAudience('course', 'course');" class="list-cancel-image" /></li>
									<?php
								}
								if (is_array($PROCESSED["associated_group_ids"]) && count($PROCESSED["associated_group_ids"])) {
									foreach ($PROCESSED["associated_group_ids"] as $group) {
										if ((array_key_exists($group, $GROUP_LIST)) && is_array($GROUP_LIST[$group])) {
											?>
											<li class="community" id="audience_group_<?php echo $GROUP_LIST[$group]["group_id"]; ?>" style="cursor: move;"><?php echo $GROUP_LIST[$group]["group_name"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeAudience('group_<?php echo $GROUP_LIST[$group]["group_id"]; ?>', 'groups');" class="list-cancel-image" /></li>
											<?php
										}
									}
								}
								
								if (!(is_array($PROCESSED["associated_proxy_ids"]) && count($PROCESSED["associated_proxy_ids"])) && !(is_array($PROCESSED["associated_group_ids"]) && count($PROCESSED["associated_group_ids"])) && !(is_array($PROCESSED["associated_course_ids"]) && count($PROCESSED["associated_course_ids"]))) {
									$NOTICE++;
									$NOTICESTR[] = "No audience has been selected for this event.";
									echo display_notice();
								}
								?>
							</ul>
						</div>
					</td>
				</tr>
				<?php
				}
				?>
			</table>
			<?php
		}
	} else {
		$ERROR++;
		$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

		echo display_error();
	}
}
?>
