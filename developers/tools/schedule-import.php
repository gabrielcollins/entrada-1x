#!/usr/local/zend/bin/php
<?php
/**
 * Entrada Tools [ http://www.entrada-project.org ]
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
 * You should have received a copy of the GNU General Public License
 * along with Entrada.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Import Events Utility
 *
 * This is a script that you can use to import learning events into the
 * database. You can either import a standard CSV file (example provided in
 * import_events_example.csv) or you can import an updated version of a file
 * that was exported using the provided schedule-export.php tool.
 *
 * Instructions:
 * 0. Backup the file system and databases *always* before importing.
 *
 * 1. Run "./schedule-import.php -validate /path/to/file.csv" to validate all of
 *    the data in the rows of your CSV file.
 *
 * 2. Run "./schedule-import.php -testimport /path/to/file.csv " to run a data
 *    entry test. WARNING! This will make changes to your database and should be
 *    used prior to importing to production to ensure there are no SQL errors.
 *    This will not actually copy the files from the file system.
 *
 * 3. After you have run -validate, and -testimport you can run
 *    "./schedule-import.php -import /path/to/file.csv", which will actually
 *    create all of the new entries in the entrada.events table as well as
 *    copy any of the files from copied learning events.
 *
 * @author Unit: Medical Education Technology Unit
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 */

set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__)."/includes");

@ini_set("auto_detect_line_endings", 1);
@ini_set("magic_quotes_runtime", 0);
set_time_limit(0);

if ((!isset($_SERVER["argv"])) || (@count($_SERVER["argv"]) < 1)) {
	echo "<html>\n";
	echo "<head>\n";
	echo "	<title>Processing Error</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	echo "This file should be run by command line only.";
	echo "</body>\n";
	echo "</html>\n";
	exit;
}

require_once("classes/adodb/adodb.inc.php");
require_once("config.inc.php");
require_once("dbconnection.inc.php");
require_once("functions.inc.php");

$ENABLE_LOGGING			= true;		// true | false to enable | disable logging.

$COPY_EVENT_TOPICS		= true;		// Copy hot topics data from old events.
$COPY_EVENT_FILES		= true;		// Copy event files from old events.
$COPY_EVENT_LINKS		= true;		// Copy event links from old events.
$COPY_EVENT_OBJECTIVES	= true;		// Copy event objectives from old events.

$parents_processed		= array();

$ACTION					= ((isset($_SERVER["argv"][1])) ? trim($_SERVER["argv"][1]) : "-usage");
$CSV_FILE				= (((isset($_SERVER["argv"][2])) && (trim($_SERVER["argv"][2]) != "")) ? trim($_SERVER["argv"][2]) : false);

$skip_file_copy			= false;	// Do not change this, it is set with the -testimport option.
$LOG_FILENAME			= dirname(__FILE__)."/data/".basename(__FILE__)."_".date("Y-m-d")."_log.txt";

$SUCCESS				= 0;
$NOTICE					= 0;
$ERROR					= 0;

$cache_courses			= array();
$cache_eventtypes		= array();
$cache_audience_cohorts	= array();
$cache_audience_students = array();
$cache_teacher_numbers	= array();

function format_import_row($number, $row = array()) {
	global $db, $cache_courses, $cache_eventtypes, $cache_audience_cohorts, $cache_audience_students, $cache_teacher_numbers;

	if (!is_array($row)) {
		return false;
	}

	$output = array();
	$skip_row = false;

	/*
	* 0		Original Event
	* 1		Parent Event
	* 2		Term
	* 3		Course Code
	* 4		Course Name
	* 5		Date
	* 6		Start Time
	* 7		Total Duration
	* 8		Event Type Durations
	* 9		Event Types
	* 10	Event Title
	* 11	Location
	* 12	Audience (Cohorts)
	* 13	Audience (Students)
	* 14	Teacher Numbers
	* 15	Teacher Names
	*/

	$event_id = ((isset($row[0])) ? clean_input($row[0], "int") : 0);			// 0	Original Event
	$parent_id = ((isset($row[1])) ? clean_input($row[1], "int") : 0);			// 1	Parent Event
	$term = ((isset($row[2])) ? clean_input($row[2]) : "");						// 2	Term (not used by import tool)
	$course_code = ((isset($row[3])) ? clean_input($row[3]) : "");				// 3	Course Code
	$course_name = ((isset($row[4])) ? clean_input($row[4]) : "");				// 4	Course Name (used only in validation)
	$event_date = ((isset($row[5])) ? clean_input($row[5]) : "");				// 5	Date
	$event_start_time = ((isset($row[6])) ? clean_input($row[6]) : "");			// 6	Start Time
	$total_duration = ((isset($row[7])) ? clean_input($row[7]) : "");			// 7	Total Duration (used only in validation)
	$event_durations = ((isset($row[8])) ? clean_input($row[8]) : 0);			// 8	Event Type Durations
	$event_types = ((isset($row[9])) ? clean_input($row[9]) : "");				// 9	Event Types
	$event_title = ((isset($row[10])) ? clean_input($row[10]) : "");			// 10	Event Title
	$event_location = ((isset($row[11]) && (trim($row[11]) != "")) ? clean_input($row[11]) : "TBA");	// 11	Location
	$audience_cohorts = ((isset($row[12]) && (trim($row[12]) != "")) ? clean_input($row[12]) : "");		// 12	Audience (Cohorts)
	$audience_students = ((isset($row[13]) && (trim($row[13]) != "")) ? clean_input($row[13]) : "");	// 13	Audience (Students)
	$teacher_numbers = ((isset($row[14]) && (trim($row[14]) != "")) ? clean_input($row[14]) : "");		// 14	Teacher Numbers

	$original_event_ids = array();
	$parent_id = 0;
	$course_id = 0;
	$event_duration = 0;
	$event_start = 0;
	$event_type_durations = array();
	$event_eventtypes = array();
	$organisation_id = 0;
	$event_audience = array("cohorts" => array(), "students" => array());
	$event_contacts = array();

	// Original Event ID
	if ($event_id) {
		$pieces = explode(";", $event_id);
		if ((is_array($pieces)) && (!empty($pieces))) {
			foreach ($pieces as $id) {
				$id = clean_input($id, array("nows", "int"));
				if ($id) {
					if (validate_event_id($id)) {
						$original_event_ids[] = $id;
					} else {
						output_notice("[Row ".$number."]\tA provided event id [".$id."] does not exist and will be skipped.");
					}
				}
			}
		}
	}

	// Parent ID
	if ($parent_id) {
		if (!validate_event_id($parent_id)) {
			output_notice("[Row ".$number."]\tThe provided parent id [".$parent_id."] does not exist and will be skipped.");

			$parent_id = 0;
		}
	}

	// Course Code
	if ($course_code) {
		if (array_key_exists($course_code, $cache_courses)) {
			$course = $cache_courses[$course_code];
		} else {
			$course = fetch_course($course_code);
			$cache_courses[$course_code] = $course;
		}

		if ($course) {
			$course_id = $course["course_id"];
			$organisation_id = $course["organisation_id"];

			if (strtolower($course_name) != strtolower($course["course_name"])) {
				output_notice("[Row ".$number."]\tThe provided course name [".$course_name."] does not match the course name of the provided course code [".$course_code."].");
			}

			if (!$organisation_id) {
				$skip_row = true;

				output_error("[Row ".$number."]\tUnable to locate the organisation id for course code [".$course_code."].");
			}
		} else {
			$skip_row = true;

			output_error("[Row ".$number."]\tThe provided course code [".$course_code."] was not found, or the course is not active.");
		}
	} else {
		$skip_row = true;

		output_error("[Row ".$number."]\tA course code was not provided for this event.");
	}

	// Event Date
	$event_start = strtotime($event_date." ".$event_start_time);
	if (!$event_start) {
		$skip_row = true;

		output_error("[Row ".$number."]\tUnable to create a UNIX timestamp for [".$event_date." ".$event_start_time."].");
	} else {
		$test_year = (int) date("Y", $event_start);
		$test_hour = (int) date("G", $event_start);

		if (($test_hour < 8) || ($test_hour > 18)) {
			output_notice("[Row ".$number."]\tYou are importing an event where the hour takes place out of the normal range 8AM - 6PM [".date("r", $event_start)."].");
		}

		if ($event_start < time()) {
			output_notice("[Row ".$number."]\tYou are importing an event that took place before today [".date("r", $event_start)."].");
		}

		if ($event_start > strtotime("+1 year")) {
			output_notice("[Row ".$number."]\tYou are importing an event that takes place over 1 year away [".date("r", $event_start)."].");
		}
	}

	// Event Type Durations
	if ($event_durations) {
		$pieces = explode(";", $event_durations);
		if ((is_array($pieces)) && (!empty($pieces))) {
			foreach ($pieces as $key => $tmp_event_duration) {
				$tmp_event_duration = clean_input($tmp_event_duration, array("nows", "int"));
				if ($tmp_event_duration) {
					$event_duration += $tmp_event_duration;
					$event_type_durations[$key] = $tmp_event_duration;
				} else {
					output_notice("[Row ".$number."]\tEvent type duration #".($key+1)." is invalid [".$tmp_event_duration."] and will be skipped.");
				}
			}
		}
	}

	if ($event_duration != $total_duration) {
		output_notice("[Row ".$number."]\tThe specified total duration [".$total_duration."] does not equal the calculated duration [".$event_duration."].");
	}

	// Event Types
	if ($event_types) {
		$pieces = explode(";", $event_types);
		if ((is_array($pieces)) && (!empty($pieces))) {
			foreach ($pieces as $key => $tmp_event_type) {
				$tmp_event_type = clean_input($tmp_event_type, array("notags", "trim"));
				if ($tmp_event_type) {
					if (array_key_exists($tmp_event_type, $cache_eventtypes)) {
						$eventtype_id = $cache_eventtypes[$tmp_event_type];
					} else {
						$eventtype_id = get_eventtype_id($tmp_event_type);
						$cache_eventtypes[$tmp_event_type] = $eventtype_id;
					}

					if (!$eventtype_id) {
						output_notice("[Row ".$number."]\tEvent type #".($key+1)." is invalid [".$tmp_event_type."] and will be skipped.");
					} else {
						if (isset($event_type_durations[$key])) {
							$event_eventtypes[] = array("eventtype_id" => $eventtype_id, "duration" => $event_type_durations[$key]);
						}
					}
				}
			}
		}
	}

	// Event Title
	if ($event_title == "") {
		$skip_row = true;

		output_error("[Row ".$number."]\tThe event title is missing for this event.");
	}

	// Audience (Cohorts)
	if ($audience_cohorts) {
		$pieces = explode(";", $audience_cohorts);
		if ((is_array($pieces)) && (!empty($pieces))) {
			foreach ($pieces as $key => $cohort) {
				$cohort = clean_input($cohort, array("notags", "trim"));

				if (array_key_exists($cohort, $cache_audience_cohorts)) {
					$group_id = $cache_audience_cohorts[$cohort];
				} else {
					$group_id = fetch_cohort_group_id($organisation_id, $cohort);
					$cache_audience_cohorts[$cohort] = $group_id;
				}

				if ($group_id) {
					if (!in_array($group_id, $event_audience["cohorts"])) {
						$event_audience["cohorts"][] = $group_id;
					} else {
						output_notice("[Row ".$number."]\tCohort [".$cohort."] was already present in this event so it was skipped.");
					}
				} else {
					$skip_row = true;

					output_error("[Row ".$number."]\tUnable to locate an active group_id for audience (cohort) #".($key+1).".");
				}
			}
		}
	}

	// Audience (Students)
	if ($audience_students) {
		$pieces = explode(";", $audience_students);
		if ((is_array($pieces)) && (!empty($pieces))) {
			foreach ($pieces as $key => $student_number) {
				$student_number = clean_input($student_number, array("int"));

				if (array_key_exists($student_number, $cache_audience_students)) {
					$proxy_id = $cache_audience_students[$student_number];
				} else {
					$proxy_id = get_proxy_id($student_number);
					$cache_audience_students[$student_number] = $proxy_id;
				}

				if ($proxy_id) {
					if (!in_array($proxy_id, $event_audience["students"])) {
						$event_audience["students"][] = $proxy_id;
					} else {
						output_notice("[Row ".$number."]\tStudent [".$student_number."] was already present in this event so he/she was skipped.");
					}
				} else {
					$skip_row = true;

					output_error("[Row ".$number."]\tUnable to locate a system account for audience (student) #".($key+1).".");
				}
			}
		}
	}

	if (empty($event_audience) || (empty($event_audience["cohorts"]) && empty($event_audience["students"]))) {
		$skip_row = true;

		output_error("[Row ".$number."]\tThere was not a valid audience specified for this event.");
	}

	// Teacher Numbers
	if ($teacher_numbers) {
		$pieces = explode(";", $teacher_numbers);
		if ((is_array($pieces)) && (!empty($pieces))) {
			foreach ($pieces as $key => $teacher_number) {
				$teacher_number = clean_input($teacher_number, array("int"));

				if (array_key_exists($teacher_number, $cache_teacher_numbers)) {
					$proxy_id = $cache_teacher_numbers[$teacher_number];
				} else {
					$proxy_id = get_proxy_id($teacher_number);
					$cache_teacher_numbers[$teacher_number] = $proxy_id;
				}

				if ($proxy_id) {
					if (!in_array($proxy_id, $event_contacts)) {
						$event_contacts[] = $proxy_id;
					} else {
						output_notice("[Row ".$number."]\tTeacher [".$teacher_number."] was already present in this event so he/she was skipped.");
					}
				} else {
					$skip_row = true;

					output_error("[Row ".$number."]\tUnable to locate a system account for teacher number #".($key+1).".");
				}
			}
		}
	}


	$output = array (
		"skip" => $skip_row,
		"original_event_ids" => $original_event_ids,
		"events" => array (
			"parent_id" => $parent_id,
			"course_id" => $course_id,
			"event_title" => $event_title,
			"event_location" => $event_location,
			"event_start" => $event_start,
			"event_finish" => ($event_start + ($event_duration * 60)),
			"event_duration" => $event_duration
		),
		"event_eventtypes" => $event_eventtypes,
		"event_audience" => $event_audience,
		"event_contacts" => $event_contacts,
	);

	return $output;
}


switch ($ACTION) {
	case "-testimport" :
		$skip_file_copy = true;

	case "-import" :
		$handle = fopen($CSV_FILE, "r");
		if ($handle) {
			$row_count	= 0;

			while (($row = fgetcsv($handle)) !== false) {
				$row_count++;

				/**
				 * We do not want the first row to be imported because it should
				 * be the CSV heading titles.
				 */
				if ($row_count > 1) {
					$event = format_import_row($row_count, $row);

					if ($event) {
						$historical								= get_event_data($event["original_event_ids"]);

						$processed_event						= array();
						$processed_event["parent_id"]			= $event["events"]["parent_id"];
						$processed_event["recurring_id"]		= 0;
						$processed_event["region_id"]			= ((isset($historical["region_id"])) ? $historical["region_id"] : 0);
						$processed_event["course_id"]			= $event["events"]["course_id"];
						$processed_event["course_num"]			= ""; // Not used.
						$processed_event["event_phase"]			= ""; // Not used.
						$processed_event["event_title"]			= $event["events"]["event_title"];
						$processed_event["event_description"]	= ((isset($historical["event_description"])) ? $historical["event_description"] : "");
						$processed_event["event_goals"]			= ((isset($historical["event_goals"])) ? $historical["event_goals"] : "");
						$processed_event["event_objectives"]	= ((isset($historical["event_objectives"])) ? $historical["event_objectives"] : "");
						$processed_event["event_message"]		= ((isset($historical["event_message"])) ? $historical["event_message"] : "");
						$processed_event["event_location"]		= $event["events"]["event_location"];
						$processed_event["event_start"]			= $event["events"]["event_start"];
						$processed_event["event_finish"]		= $event["events"]["event_finish"];
						$processed_event["event_duration"]		= $event["events"]["event_duration"];
						$processed_event["release_date"]		= ((isset($historical["release_date"])) ? (int) $historical["release_date"] : 0);
						$processed_event["release_until"]		= ((isset($historical["release_until"])) ? (int) $historical["release_until"] : 0);
						$processed_event["updated_date"]		= time();
						$processed_event["updated_by"]			= 1;

						if (($db->AutoExecute("events", $processed_event, "INSERT")) && ($new_event_id = $db->Insert_Id())) {
							output_success("[Row ".$row_count."]\tImported [".$processed_event["event_title"]."] as event_id [".$new_event_id."] for course_id [".$event["events"]["course_id"]."] on ".date("r", $event["events"]["event_start"]));

							/**
							 * Attach event types to new event.
							 */
							if (isset($event["event_eventtypes"]) && is_array($event["event_eventtypes"]) && !empty($event["event_eventtypes"])) {
								foreach ($event["event_eventtypes"] as $key => $eventtype) {
									$eventtype["event_id"] = $new_event_id;
									if ($db->AutoExecute("event_eventtypes", $eventtype, "INSERT")) {
										output_success("[Row ".$row_count."]\tAttached eventtype_id [".$eventtype["eventtype_id"]."] to event_id [".$new_event_id."] for [".$eventtype["duration"]."] minutes.");
									}
								}
							}

							/**
							 * Attach audience cohort information to new event.
							 */
							if (isset($event["event_audience"]["cohorts"]) && is_array($event["event_audience"]["cohorts"]) && !empty($event["event_audience"]["cohorts"])) {
								foreach ($event["event_audience"]["cohorts"] as $group_id) {
									$processed_audience = array();
									$processed_audience["event_id"] = $new_event_id;
									$processed_audience["audience_type"] = "cohort";
									$processed_audience["audience_value"] = $group_id;
									$processed_audience["updated_date"] = time();
									$processed_audience["updated_by"] = 1;

									if ($db->AutoExecute("event_audience", $processed_audience, "INSERT")) {
										output_success("[Row ".$row_count."]\tAttached cohort group_id [".$group_id."] to event_id [".$new_event_id."].");
									}
								}
							}

							/**
							 * Attach audience student information to new event.
							 */
							if (isset($event["event_audience"]["students"]) && is_array($event["event_audience"]["students"]) && !empty($event["event_audience"]["students"])) {
								foreach ($event["event_audience"]["students"] as $proxy_id) {
									$processed_audience = array();
									$processed_audience["event_id"] = $new_event_id;
									$processed_audience["audience_type"] = "proxy_id";
									$processed_audience["audience_value"] = $proxy_id;
									$processed_audience["updated_date"] = time();
									$processed_audience["updated_by"] = 1;

									if ($db->AutoExecute("event_audience", $processed_audience, "INSERT")) {
										output_success("[Row ".$row_count."]\tAttached student proxy_id [".$proxy_id."] to event_id [".$new_event_id."].");
									}
								}
							}

							/**
							 * Attach teachers to new event.
							 */
							if (isset($event["event_contacts"]) && is_array($event["event_contacts"]) && !empty($event["event_contacts"])) {
								foreach ($event["event_contacts"] as $key => $proxy_id) {
									$processed_contact = array();
									$processed_contact["event_id"] = $new_event_id;
									$processed_contact["proxy_id"] = $proxy_id;
									$processed_contact["contact_role"] = "teacher";
									$processed_contact["contact_order"] = $key;
									$processed_contact["updated_date"] = time();
									$processed_contact["updated_by"] = 1;

									if ($db->AutoExecute("event_contacts", $processed_contact, "INSERT")) {
										output_success("[Row ".$row_count."]\tAttached proxy_id [".$proxy_id."] to event_id [".$new_event_id."] as teacher #".$key.".");
									}
								}
							}

							if (isset($event["original_event_ids"]) && is_array($event["original_event_ids"]) && !empty($event["original_event_ids"])) {
								foreach ($event["original_event_ids"] as $original_event_id) {
									$historical	= get_event_data($original_event_id);

									/**
									 * Attach any hot topic data from the previous event to this new event.
									 */
									if ($COPY_EVENT_TOPICS) {
										$query = "SELECT * FROM `event_topics` WHERE `event_id` = ".$db->qstr($original_event_id);
										$results = $db->GetAll($query);
										if ($results) {
											$copied_topic	= array();

											foreach ($results as $result) {
												if (!in_array($result["topic_id"], $copied_topic)) {
													$processed_topic					= array();
													$processed_topic["event_id"]		= $new_event_id;
													$processed_topic["topic_id"]		= $result["topic_id"];
													$processed_topic["topic_coverage"]	= $result["topic_coverage"];
													$processed_topic["topic_time"]		= $result["topic_time"];
													$processed_topic["updated_date"]	= $result["updated_date"];
													$processed_topic["updated_by"]		= $result["updated_by"];

													if (($db->AutoExecute("event_topics", $processed_topic, "INSERT")) && ($new_etopic_id = $db->Insert_Id())) {
														output_success("[Row ".$row_count."]\tCopied topic_id [".$processed_topic["topic_id"]."] to new event_id [".$new_event_id."].");

														$copied_topic[] = $processed_topic["topic_id"];
													} else {
														output_error("[Row ".$row_count."]\tUnable to copy topic_id [".$processed_topic["topic_id"]."] to new event_id [".$new_event_id."]. Database said: ".$db->ErrorMsg());
													}
												}
											}
										}
									}

									/**
									 * Attach any files from the previous event to this new event.
									 */
									if ($COPY_EVENT_FILES) {
										$query		= "SELECT * FROM `event_files` WHERE `event_id` = ".$db->qstr($original_event_id);
										$results	= $db->GetAll($query);
										if ($results) {
											$copied_files	= array();

											foreach ($results as $result) {
												if ((!in_array($result["file_name"], $copied_files)) && ($result["file_category"] != "podcast")) {
													$old_event_file = FILE_STORAGE_PATH."/".$result["efile_id"];

													if ((@file_exists($old_event_file)) || ((bool) $skip_file_copy)) {
														$processed_file						= array();
														$processed_file["event_id"]			= $new_event_id;
														$processed_file["required"]			= $result["required"];
														$processed_file["timeframe"]		= $result["timeframe"];
														$processed_file["file_category"]	= $result["file_category"];
														$processed_file["file_type"]		= $result["file_type"];
														$processed_file["file_size"]		= $result["file_size"];
														$processed_file["file_name"]		= $result["file_name"];
														$processed_file["file_title"]		= $result["file_title"];
														$processed_file["file_notes"]		= $result["file_notes"];
														$processed_file["access_method"]	= $result["access_method"];
														$processed_file["accesses"]			= 0;
														$processed_file["release_date"]		= ((((int) $result["release_date"]) && (isset($historical["event_start"]))) ? offset_validity($historical["event_start"], $processed_event["event_start"], $result["release_date"]) : 0);
														$processed_file["release_until"]	= ((((int) $result["release_until"]) && (isset($historical["event_start"]))) ? offset_validity($historical["event_start"], $processed_event["event_start"], $result["release_until"]) : 0);
														$processed_file["updated_date"]		= $result["updated_date"];
														$processed_file["updated_by"]		= $result["updated_by"];

														if (($db->AutoExecute("event_files", $processed_file, "INSERT")) && ($new_file_id = $db->Insert_Id())) {
															output_success("[Row ".$row_count."]\tCopied file [".$processed_file["file_name"]."] to new event_id [".$new_event_id."] in database.");

															if (!(bool) $skip_file_copy) {
																if (copy($old_event_file, FILE_STORAGE_PATH."/".$new_file_id)) {
																	output_success("[Row ".$row_count."]\tCopied file [".$processed_file["file_name"]."] to new event_id [".$new_event_id."] in filesystem.");

																	$copied_files[] = $processed_file["file_name"];
																} else {
																	output_error("[Row ".$row_count."]\tUnable to copy file [".$processed_file["file_name"]."] to new event_id [".$new_event_id."] in filesystem.");
																}
															} else {
																$copied_files[] = $processed_file["file_name"];
															}
														} else {
															output_error("[Row ".$row_count."]\tUnable to copy file [".$processed_file["file_name"]."] to new event_id [".$new_event_id."] in database. Database said: ".$db->ErrorMsg());
														}
													} else {
														output_error("[Row ".$row_count."]\tThe old event file [".$old_event_file."] does not exist in the filesystem, so it cannot be copied.");
													}
												}
											}
										}
									}

									/**
									 * Attach any links from the previous event to this new event.
									 */
									if ($COPY_EVENT_LINKS) {
										$query		= "SELECT * FROM `event_links` WHERE `event_id` = ".$db->qstr($original_event_id);
										$results	= $db->GetAll($query);
										if ($results) {
											$copied_links	= array();

											foreach ($results as $result) {
												if (!in_array($result["link"], $copied_links)) {
													$processed_link						= array();
													$processed_link["event_id"]			= $new_event_id;
													$processed_link["required"]			= $result["required"];
													$processed_link["timeframe"]		= $result["timeframe"];
													$processed_link["proxify"]			= $result["proxify"];
													$processed_link["link"]				= $result["link"];
													$processed_link["link_title"]		= $result["link_title"];
													$processed_link["link_notes"]		= $result["link_notes"];
													$processed_link["accesses"]			= 0;
													$processed_link["release_date"]		= (((int) $result["release_date"]) ? offset_validity($historical["event_start"], $processed_event["event_start"], $result["release_date"]) : 0);
													$processed_link["release_until"]	= (((int) $result["release_until"]) ? offset_validity($historical["event_start"], $processed_event["event_start"], $result["release_until"]) : 0);
													$processed_link["updated_date"]		= $result["updated_date"];
													$processed_link["updated_by"]		= $result["updated_by"];

													if (($db->AutoExecute("event_links", $processed_link, "INSERT")) && ($new_link_id = $db->Insert_Id())) {
														output_success("[Row ".$row_count."]\tCopied link [".$processed_link["link_title"]."] to new event_id [".$new_event_id."].");

														$copied_links[] = $processed_link["link"];
													} else {
														output_error("[Row ".$row_count."]\tUnable to copy link [".$processed_link["link_title"]."] to new event_id [".$new_event_id."]. Database said: ".$db->ErrorMsg());
													}
												}
											}
										}
									}

									/**
									 * Attach any objectives from the previous event to this new event.
									 */
									if ($COPY_EVENT_OBJECTIVES) {
										$query		= "SELECT * FROM `event_objectives` WHERE `event_id` = ".$db->qstr($original_event_id);
										$results	= $db->GetAll($query);
										if ($results) {
											$copied_objectives	= array();

											foreach ($results as $result) {
												if (!in_array($result["objective_id"], $copied_objectives)) {
													$processed_objective = array();
													$processed_objective["event_id"] = $new_event_id;
													$processed_objective["objective_id"] = $result["objective_id"];
													$processed_objective["objective_details"] = $result["objective_details"];
													$processed_objective["objective_type"] = $result["objective_type"];
													$processed_objective["updated_date"] = $result["updated_date"];
													$processed_objective["updated_by"] = $result["updated_by"];

													if (($db->AutoExecute("event_objectives", $processed_objective, "INSERT")) && ($new_eobjective_id = $db->Insert_Id())) {
														output_success("[Row ".$row_count."]\tCopied objective_id [".$processed_objective["objective_id"]."] to new event_id [".$new_event_id."].");

														$copied_objectives[] = $processed_objective["objective_id"];
													} else {
														output_error("[Row ".$row_count."]\tUnable to copy objective_id [".$processed_objective["objective_id"]."] to new event_id [".$new_event_id."]. Database said: ".$db->ErrorMsg());
													}
												}
											}
										}
									}

									/**
									 * Add a line to the related linking to previous copied lecture.
									 */
									$processed_related						= array();
									$processed_related["event_id"]			= $new_event_id;
									$processed_related["related_type"]		= "event_id";
									$processed_related["related_value"]		= $original_event_id;
									$processed_related["updated_date"]		= time();
									$processed_related["updated_by"]		= 1;

									if ($db->AutoExecute("event_related", $processed_related, "INSERT")) {
										output_success("[Row ".$row_count."]\tAdded relationship between old event_id [".$original_event_id."] and new event_id [".$new_event_id."].");
									} else {
										output_error("[Row ".$row_count."]\tUnable to add relationship between old event_id [".$original_event_id."] and new event_id [".$new_event_id."]. Database said: ".$db->ErrorMsg());
									}
								}
							}
						} else {
							output_error("[Row ".$row_count."]\tUnable to import this learning event on ".date("r", $processed_event["event_start"]).". Database said: ".$db->ErrorMsg());
						}
					}
				}
			}
			fclose($handle);
		} else {
			output_error("Unable to open the provided CSV file [".$CSV_FILE."].");
		}
	break;
	case "-validate" :
		$handle = fopen($CSV_FILE, "r");
		if ($handle) {
			$row_count	= 0;

			while (($row = fgetcsv($handle)) !== false) {
				$row_count++;

				/**
				 * We do not want the first row to be imported because it should
				 * be the CSV heading titles.
				 */
				if ($row_count > 1) {
					$result = format_import_row($row_count, $row);

					if ($result["skip"] == true) {
						output_error("[Row ".$row_count."]\tThis row will be skipped.");
					}
				}
			}
			fclose($handle);
		} else {
			output_error("Unable to open the provided CSV file [".$CSV_FILE."].");
		}

		if ((!$SUCCESS) && (!$NOTICE) && (!$ERROR)) {
			echo "\nThis CSV file has been parsed, but there does not not appear to be any errors or notices.";
		}
	break;
	case "-usage" :
	default :
		echo "\nUsage: schedule-import.php [options] /path/to/import-file.csv";
		echo "\n   -usage               Brings up this help screen.";
		echo "\n   -validate            Validates the import file.";
		echo "\n   -testimport			Proceeds with live import, but does not copy any files.";
		echo "\n   -import              Proceeds with live import.";
	break;
}
echo "\n\n";
?>