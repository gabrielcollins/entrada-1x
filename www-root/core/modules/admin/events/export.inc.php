<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Entrada is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Entrada is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This file displays the list of learning events that match any requested
 * filters. Data is pulled from the entrada.events table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVENTS"))) {
	exit;
} else if ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
		header("Location: ".ENTRADA_URL);
		exit;
} else if (!$ENTRADA_ACL->amIAllowed("eventcontent", "update", false)) {
	$ERROR++;
	$ERRORSTR[] = "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	/**
	 * Process any sorting or pagination requests.
	 */
	events_process_sorting();

	/**
	 * Process any filter requests.
	 */
	events_process_filters($ACTION, "admin");

	/**
	 * Check if preferences need to be updated.
	 */
	preferences_update($MODULE, $PREFERENCES);
	
	$csv_headings = array(
		"event_id" => "Original Event",
		"parent_id" => "Parent Event",
		"event_term" => "Term",
		"course_code" => "Course Code",
		"course_name" => "Course Name",
		"event_start_date" => "Date",
		"event_start_time" => "Start Time",
		"total_duration" => "Total Duration",
		"event_type_durations" => "Event Type Durations",
		"event_types" => "Event Types",
		"event_title" => "Event Title",
		"event_location" => "Location",
		"audience_cohorts" => "Audience (Cohorts)",
		"audience_students" => "Audience (Students)",
		"staff_numbers" => "Teacher Numbers",
		"staff_names" => "Teacher Names"
	);
	
	$csv_delimiter = ",";
	$csv_enclosure = '"';
	
	/**
	 * Fetch all of the events that apply to the current filter set.
	 */
	$learning_events = events_fetch_filtered_events(
			$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"],
			$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"],
			$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"],
			$ENTRADA_USER->getActiveOrganisation(),
			$_SESSION[APPLICATION_IDENTIFIER]["events"]["sb"],
			$_SESSION[APPLICATION_IDENTIFIER]["events"]["so"],
			$_SESSION[APPLICATION_IDENTIFIER]["events"]["dtype"],
			$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"],
			0,
			$_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"],
			false);

	if (!empty($learning_events["events"])) {
		/**
		 * Clears all open buffers so we can return a plain response for the Javascript.
		 */
		ob_clear_open_buffers();
	

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=\"schedule-export-".date("Y-m-d").".csv\"");
		header("Content-Transfer-Encoding: binary");		
	
		$fp = fopen("php://output", "w");
	
		// Output CSV headings
		fputcsv($fp, $csv_headings, $csv_delimiter, $csv_enclosure);
		
		foreach ($learning_events["events"] as $event) {
			$event_type_durations = array();
			$event_types = array();
			$audience_cohorts = array();
			$audience_students = array();
			$staff_numbers = array();
			$staff_names = array();


			// Event Type Durations, and Event Types
			$query = "	SELECT a.`duration`, b.`eventtype_title`
						FROM `event_eventtypes` AS a
						JOIN `events_lu_eventtypes` AS b
						ON b.`eventtype_id` = a.`eventtype_id`
						WHERE a.`event_id` = ".$db->qstr($event["event_id"]);
			$results = $db->GetAll($query);
			if ($results) {
				foreach ($results as $key => $result) {
					$event_type_durations[$key] = $result["duration"];
					$event_types[$key] = $result["eventtype_title"];
				}
			}

			// Event Audience (Cohorts, Student Numbers, Course Codes)
			$query = "	SELECT a.`audience_type`, a.`audience_value`
						FROM `event_audience` AS a
						WHERE a.`event_id` = ".$db->qstr($event["event_id"]);
			$results = $db->GetAll($query);
			if ($results) {
				foreach ($results as $result) {
					switch ($result["audience_type"]) {
						case "cohort" :
							$query = "SELECT `group_name` FROM `groups` WHERE `group_id` = ".$db->qstr($result["audience_value"]);
							$audience = $db->GetRow($query);
							if ($audience) {
								$audience_cohorts[] = $audience["group_name"];
							}
						break;
						case "proxy_id" :
							$query = "SELECT `number` FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($result["audience_value"]);
							$audience = $db->GetRow($query);
							if ($audience) {
								$audience_students[] = (int) $audience["number"];
							}
						break;
						default :
							continue;
						break;
					}
				}
			}
			
			// Staff Numbers, and Names
			$query = "	SELECT b.`number`, CONCAT(b.`firstname`, ' ', b.`lastname`) AS `fullname`
						FROM `event_contacts` AS a
						JOIN `".AUTH_DATABASE."`.`user_data` AS b
						ON b.`id` = a.`proxy_id`
						WHERE a.`event_id` = ".$db->qstr($event["event_id"])."
						AND `contact_role` = 'teacher'
						ORDER BY `contact_order` ASC";
			$results = $db->GetAll($query);
			if ($results) {
				foreach ($results as $key => $result) {
					$staff_numbers[$key] = (int) $result["number"];
					$staff_names[$key] = $result["fullname"];
				}
			}
			
			$row = array(
				"event_id" => (int) $event["event_id"],
				"parent_id" => (int) $event["parent_id"],
				"event_term" => $event["event_term"],
				"course_code" => $event["course_code"],
				"course_name" => $event["course_name"],
				"event_start_date" => date("Y-m-d", $event["event_start"]),
				"event_start_time" => date("H:i", $event["event_start"]),
				"total_duration" => (($event["event_finish"] - $event["event_start"]) / 60),
				"event_type_durations" => implode("; ", $event_type_durations),
				"event_types" => implode("; ", $event_types),
				"event_title" => $event["event_title"],
				"event_location" => $event["event_location"],
				"audience_cohorts" => implode("; ", $audience_cohorts),
				"audience_students" => implode("; ", $audience_students),
				"staff_numbers" => implode("; ", $staff_numbers),
				"staff_names" => implode("; ", $staff_names)
			);
			
			fputcsv($fp, $row, $csv_delimiter, $csv_enclosure);
		}
		
		fclose($fp);
		exit;
	} else {
		header("Location: ".ENTRADA_URL."/events");
	}
}