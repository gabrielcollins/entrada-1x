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
 * You should have received a copy of the GNU General Public License
 * along with Entrada.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Allows students to delete an elective in the system if it has not yet been approved.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Don Zuiker <don.zuiker@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 *
 */
if (!defined("IN_MTDTRACKING")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: " . ENTRADA_URL);
	exit;
} else {
	@set_include_path(implode(PATH_SEPARATOR, array(
						dirname(__FILE__) . "/../../../core/includes",
						get_include_path(),
					)));

	require_once("functions.inc.php");

	$usersId = $_SESSION["details"]["id"];
	$PROCESSED["service_id"] = validate_integer_field($_GET["service_id"]);

	$year = clean_input($_GET["year"], array("notags", "trim", "nows"));

	if (!$year) {
		$current_date = date("Y-m-d");
		$date_arr = date_parse($current_date);

		if ($date_arr["month"] >= 7) {
			$year = $date_arr["year"] . "-" . strval(intval($date_arr["year"]) + 1);
		} else {
			$year = strval(intval($date_arr["year"]) - 1) . "-" . $date_arr["year"];
		}
	}

	$year_min = substr($year, 0, 4);
	$year_max = substr($year, 5, 4);

	ob_clear_open_buffers();
	$query = "SELECT `mtd_facilities`.`facility_name`, `user_data_resident`.`first_name`,
				 `user_data_resident`.`last_name`, `mtd_schedule`.`start_date`,
				 `mtd_schedule`.`end_date`, `user_data_resident`.`cpso_no`,
				 `user_data_resident`.`student_no`,`mtd_schools`.`school_code`,
				 `mtd_locale_duration`.`percent_time`, `mtd_moh_program_codes`.`program_code`,
				 `mtd_moh_service_codes`.`service_code`, `mtd_categories`.`category_code`,
				 `mtd_type`.`type_description`
		  FROM  `" . DATABASE_NAME . "`.`mtd_schedule`,
				`" . DATABASE_NAME . "`.`mtd_facilities`,
				`" . AUTH_DATABASE . "`.`user_data_resident`,
				`" . DATABASE_NAME . "`.`mtd_locale_duration`,
				`" . DATABASE_NAME . "`.`mtd_schools`,
				`" . DATABASE_NAME . "`.`mtd_moh_program_codes`,
				`" . DATABASE_NAME . "`.`mtd_moh_service_codes`,
				`" . DATABASE_NAME . "`.`mtd_categories`,
				`" . DATABASE_NAME . "`.`mtd_type`
	      WHERE `mtd_schedule`.`id` = `mtd_locale_duration`.`schedule_id`
		  AND `mtd_facilities`.`id` = `mtd_locale_duration`.`location_id`
		  AND `mtd_schedule`.`service_id` = '" . $PROCESSED["service_id"] . "'
		  AND `mtd_schedule`.`resident_id` = `user_data_resident`.`proxy_id`
		  AND `mtd_schools`.`id` = `user_data_resident`.`school_id`
		  AND `mtd_moh_program_codes`.`id` = `user_data_resident`.`program_id`
		  AND `mtd_moh_service_codes`.`id` = `mtd_schedule`.`service_id`
		  AND `mtd_categories`.`id` = `user_data_resident`.`category_id`
		  AND `mtd_schedule`.`type_code` = `mtd_type`.`type_code`
		  AND date_format(`mtd_schedule`.`start_date`, '%Y-%m-%d') between '" . $year_min . "-07-01' AND '" . $year_max . "-06-30'
		  ORDER BY start_date DESC";

	$results = $db->GetAll($query);

	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=\"".date("Ymd")."-mtd_schedule.csv\"");
	header("Content-Transfer-Encoding: binary");

	echo "\"School_Code\",\"CPSO_No\",\"Student_No\",\"Program_Code\",\"First_Name\",\"Last_Name\",\"Category_Code\",\"Service_Code\",\"Location\",\"Start_Date\",\"End_Date\",\"Percent_Time\",\"Type\"\n";

	if ($results) {
		//an array for holding each line of the result set before echoing it
		//to the buffer.
		$line = array();
		foreach ($results as $result) {

			$line["school_code"] = $result["school_code"];
			$line["cpso_no"] = $result["cpso_no"];
			$line["student_no"] = $result["student_no"];
			$line["program_code"] = $result["program_code"];
			$line["first_name"] = $result["first_name"];
			$line["last_name"] = $result["last_name"];
			$line["category_code"] = $result["category_code"];
			$line["service_code"] = $result["service_code"];
			$line["facility_name"] = $result["facility_name"];
			$line["start_date"] = $result["start_date"];
			$line["end_date"] = $result["end_date"];
			$line["percent_time"] = $result["percent_time"];
			$line["type_description"] = $result["type_description"];
			echo implode(",", $line) . "\n";
		}
	} else {
		echo "No results";
	}
	exit();
}
?>