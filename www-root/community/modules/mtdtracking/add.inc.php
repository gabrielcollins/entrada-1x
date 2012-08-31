<?php

/**
 * This script adds an MTD event into the database.
 *
 * @author Don Zuiker <don.zuiker@queensu.ca>
 */
if ((!defined("COMMUNITY_INCLUDED")) || (!defined("IN_MTDTRACKING"))) {
	header("Location: " . COMMUNITY_URL);
	exit;
} else {
	$PROCESSED["creator_id"] = $_SESSION["details"]["id"];

	$PROCESSED["resident_proxy_id"] = clean_input($_POST["resident_proxy_id"], array("notags", "trim", "int"));

	//double check that the resident exists
	$query = "SELECT *
				  FROM `" . AUTH_DATABASE . "`.`user_data_resident`
				  WHERE `proxy_id` = " . $PROCESSED["resident_proxy_id"];

	$resident = $db->GetRow($query);

	if ($resident) {
		$PROCESSED["resident_id"] = $resident["proxy_id"];
		$PROCESSED["home_program_id"] = $resident["program_id"];
		$PROCESSED["home_school_id"] = $resident["school_id"];
		$PROCESSED["category_id"] = $resident["category_id"];
	} else {
		$ERROR++;
		$ERRORSTR[] = "Resident not found.";
	}

	if (isset($_POST["mtdlocation_duration_order"])) {
		$mtdlocation_duration_order = clean_input($_POST["mtdlocation_duration_order"], array("notags", "trim"));
		$location_ids = explode(",", $mtdlocation_duration_order);
		$mtdlocation_durations = ((isset($_POST["duration_segment"]) && is_array($_POST["duration_segment"])) ? $_POST["duration_segment"] : array());

		if (!is_null($mtdlocation_duration_order) && $mtdlocation_duration_order != "" && (is_array($location_ids)) && (count($location_ids))) {
			$count = 0;
			$total_time = 0;
			foreach ($location_ids as $order => $mtdlocation_id) {
				if (($mtdlocation_id = clean_input($mtdlocation_id, array("trim", "int"))) && ($duration = clean_input($mtdlocation_durations[$order], array("trim")))) {
					$query = "SELECT `facility_name` FROM `mtd_facilities` WHERE `id` = " . $db->qstr($mtdlocation_id);
					$result = $db->GetRow($query);
					if ($result) {
						$LOCALE_DURATION["locale_durations"][$count] = array($mtdlocation_id, $duration);
						$total_time += $duration;
						$count++;
					} else {
						$ERROR++;
						$ERRORSTR[] = "One of the <strong>locations</strong> you specified was invalid.";
					}
				} else {
					$ERROR++;
					$ERRORSTR[] = "Both a location and a duration of greater than 0 must be specified for every location entry.";
				}
			}

			if ($total_time > 100) {
				$ERROR++;
				$ERRORSTR[] = "The total time spent cannot be greater than 100%.";
			} else if ($total_time == 0) {
				$ERROR++;
				$ERRORSTR[] = "The total time spent cannot be 0%.";
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "At least one <strong>Location</strong> is required.";
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "At least one <strong>Location</strong> is required.";
	}


	$block = clean_input($_POST["block_list"], array("notags", "trim", "nows"));
	$year = clean_input($_POST["year"], array("notags", "trim", "nows"));

	$start_date = clean_input($_POST["start_date"], array("notags", "trim", "nows"));
	$end_date = clean_input($_POST["end_date"], array("notags", "trim", "nows"));
	
	if (is_null($year) || $year == "") {
		$ERROR++;
		$ERRORSTR[] = "A year must be selected.";
	}
	else if ((is_null($block) || $block == "") && (is_null($start_date) || $start_date == "") && (is_null($end_date) || $end_date == "")) {
		$ERROR++;
		$ERRORSTR[] = "You must choose a block OR a start and end date.";
	} else if ($block && ($start_date || $end_date)) {
		$ERROR++;
		$ERRORSTR[] = "You must choose a block OR a start and end date, not both.";
	} else if (is_null($block) || $block == "") {
		if (is_null($start_date) || $start_date == "") {
			$ERROR++;
			$ERRORSTR[] = "Start date is required.";
		} else if (is_null($end_date) || $end_date == "") {
			$ERROR++;
			$ERRORSTR[] = "End date is required.";
		} else {
			if (!validate_date_format($start_date)) {
				$ERROR++;
				$ERRORSTR[] = "Invalid start date format.";
			} else if (!validate_date_format($end_date)) {
				$ERROR++;
				$ERRORSTR[] = "Invalid end date format.";
			} else {
				if (validate_start_end_dates($start_date, $end_date)) {
					$PROCESSED["start_date"] = $start_date;
					$PROCESSED["end_date"] = $end_date;
				} else {
					$ERROR++;
					$ERRORSTR[] = "Start date cannot be after the end date.";
				}
			}
		}
	} else if ((is_null($start_date) || $start_date == "") && (is_null($end_date) || $end_date == "") && (!is_null($block) || $block != "")) {
		//Retrieve block dates from the database based on the block selected.
		//1. Determine the year we are in. e.g. "2010-2011"
		//2. select the start and end dates based on the selected block.
		$query = "SELECT * 
				  FROM `pg_blocks` 
				  WHERE `block_name` = " . $db->qstr($block) . "
				  AND year = " . $db->qstr($year);
		$result = $db->GetRow($query);

		if ($result) {
			$PROCESSED["start_date"] = $result["start_date"];
			$PROCESSED["end_date"] = $result["end_date"];
		} else {
			$ERROR++;
			$ERRORSTR[] = "Block start and end dates not found.";
		}
	}

	$PROCESSED["type_code"] = clean_input($_POST["type_code"], array("notags", "trim"));
	if (!$PROCESSED["type_code"]) {
		$ERROR++;
		$ERRORSTR[] = "Type is required.";
	}

	$PROCESSED["service_id"] = validate_integer_field($_POST["service_id"]);
	if (!$PROCESSED["service_id"]) {
		$PROCESSED["service_id"] = validate_integer_field($_POST["mtdservice_select"]);
		if (!$PROCESSED["service_id"]) {
			$ERROR++;
			$ERRORSTR[] = "No service code found.";
		}
	}

	if (!$ERROR) {

		//Validate that there is no overlapp of dates for this resident that exceed 100% time.
		$start_date_time = strtotime($PROCESSED["start_date"]);
		$end_date_time = strtotime($PROCESSED["end_date"]);
		$temp_date = $start_date_time;

		while ($temp_date <= $end_date_time) {

			$temp_date_str = date('Y-m-d', $temp_date);

			$query = "select *
							  from mtd_schedule m1
							  where " . $db->qstr($temp_date_str) . " BETWEEN m1.start_date AND m1.end_date
							  AND m1.resident_id = " . $db->qstr($resident["proxy_id"]);

			$results = $db->GetAll($query);

			if ($results) {
				$sum = 0;
				foreach ($results as $result) {
					$query = "select sum(percent_time) from mtd_locale_duration where schedule_id = " . $result["id"];
					$sum = $sum + $db->GetOne($query);
				}

				if ($sum + $total_time > 100) {
					$query = "SELECT service_description
							  FROM  `mtd_moh_service_codes`
							  WHERE `id` = " . $db->qstr($result["service_id"]);
					$service_program = $db->GetOne($query);
					$ERROR++;
					$ERRORSTR[] = "\nThe entry on the date, " . $temp_date_str . " exceeds 100% time as a result of an overlapping entry from " . $service_program;
				}
			}
			$temp_date = strtotime("+1 day", $temp_date);
		}
	}

	if (!$ERROR) {
		//Add this MTD to the schedule.
		if ($db->AutoExecute(DATABASE_NAME . ".mtd_schedule", $PROCESSED, "INSERT")) {
			$new_schedule_id = $db->Insert_Id();
			//echo "<div id=\"responseMsg\">New Schedule ID: " . $new_schedule_id . " Length: " . sizeof($LOCALE_DURATION["locale_durations"]) . "</div>";
			foreach ($LOCALE_DURATION["locale_durations"] as $locale) {
				$LOCALE_PROCESSED["schedule_id"] = $new_schedule_id;
				$LOCALE_PROCESSED["location_id"] = $locale[0];
				$LOCALE_PROCESSED["percent_time"] = $locale[1];
				$result = $db->AutoExecute(DATABASE_NAME . ".mtd_locale_duration", $LOCALE_PROCESSED, "INSERT");
				if (!$result) {
					$ERROR++;
					$ERRORSTR[] = "At least one <strong>Location</strong> is required.";
				}
			}

			if (!$ERROR) {
				$SUCCESS++;
				$SUCCESSSTR[] = "<p>You have successfully entered a new MTD event.</p>";
			}

			application_log("success", "Successfully entered an MTD for CMPA No: " . $PROCESSED["cmpa_no"] . "by [" . $PROCESSED["userId"] . "].");
		} else {
			$ERROR++;
			$ERRORSTR[] = "Failed to add new MTD event." . $db->ErrorMsg();

			application_log("error", "Failed to enter an MTD for CMPA No: " . $PROCESSED["cmpa_no"] . "by [" . $PROCESSED["userId"] . "]. Database said: " . $db->ErrorMsg());
		}
	}

	if ($SUCCESS) {
		echo "<div id=\"responseMsg\">" . display_success() . "</div>";
	}
	if ($NOTICE) {
		echo "<div id=\"responseMsg\">" . display_notice() . "</div>";
	}
	if ($ERROR) {
		echo "<div id=\"responseMsg\">" . display_error() . "</div>";
	}
}

function validate_start_end_dates($start_date, $end_date) {
	if ($start_date > $end_date) {
		return false;
	}

	return true;
}

/**
 * This function checks that the date is in the format YYYY-MM-DD.
 *
 * @param <String> $in_date
 * @return <boolean> true if date is valid as per format above.
 */
function validate_date_format($in_date) {
	$in_date_arr = explode("-", $in_date);
	$year = $in_date_arr[0];
	$month = $in_date_arr[1];
	$day = $in_date_arr[2];
	if (strlen($year) != 4) {
		return false;
	} else if (strlen($month) != 2) {
		return false;
	} else if (strlen($day) != 2) {
		return false;
	} else if (!checkdate($month, $day, $year)) {
		return false;
	} else {
		return true;
	}
}
