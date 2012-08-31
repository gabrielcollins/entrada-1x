<?php

/**
 * This script adds an MTD event into the database.
 *
 * @author Don Zuiker <don.zuiker@queensu.ca>
 */
if ((!defined("COMMUNITY_INCLUDED")) || (!defined("IN_MTDTRACKING"))) {
	header("Location: " . COMMUNITY_URL);
	exit;
} elseif (!$COMMUNITY_LOAD) {
	exit;
} else {
	@set_include_path(implode(PATH_SEPARATOR, array(
						dirname(__FILE__) . "/../../../core/includes",
						get_include_path(),
					)));

	require_once("functions.inc.php");

	$PROCESSED["userId"] = $_SESSION["details"]["id"];
	$resident_id = $_GET["resident_id"];
	//if there is no match on non-numberic characters anywhere in the string then process the number.
	if (validate_integer_field($resident_id)) {
		$PROCESSED["resident_id"] = clean_input($_GET["resident_id"], array("trim", "int"));
	} else {
		$PROCESSED["resident_id"] = 0;
	}

	if ($PROCESSED["resident_id"]) {
		//Add this MTD to the schedule.
		$query = $queryResident = "SELECT *
		  FROM `" . AUTH_DATABASE . "`.`user_data_resident`
	      WHERE `user_data_resident`.`proxy_id` = " . $PROCESSED["resident_id"];

		$resident = $db->GetRow($query);
		if ($resident) {
			$SUCCESS++;
			$SUCCESSSTR[] = "<p>You have successfully entered a new MTD event.</p>";

			application_log("success", "Successfully entered an MTD for Resident ID " . $PROCESSED["resident_id"] . "by [" . $PROCESSED["userId"] . "].");
		} else {
			$resident_found = 0;
			echo "<div id=\"resident_not_found\">Resident Not Found.</div>" . $query;
			$ERROR++;
			$ERRORSTR[] = "Failed to add new MTD event." . $db->ErrorMsg();

			application_log("error", "Failed to enter an MTD Resident ID: " . $PROCESSED["resident_id"] . "by [" . $PROCESSED["userId"] . "]. Database said: " . $db->ErrorMsg());
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "Resident not found. (Error)";
	}

	if ($SUCCESS) {
		$full_name = $resident["first_name"] . " " . $resident["last_name"];

		$query = "SELECT *
				  FROM  `mtd_schools`
				  WHERE `id` = " . $resident["school_id"];

		$resident_school = $db->GetRow($query);

		$query = "SELECT *
				  FROM  `mtd_pgme_moh_programs`
				  WHERE `moh_program_id` = " . $resident["program_id"];

		$resident_program = $db->GetRow($query);

		$query = "SELECT *
				  FROM  `mtd_categories`
				  WHERE `id` = " . $resident["category_id"];

		$resident_category = $db->GetRow($query);


		echo "<div id=\"responseMsg\">
				<div id=\"school_id\">" . $resident["school_id"] . "</div>
				<div id=\"school_description\">" . $resident_school["school_description"] . "</div>
				<div id=\"program_id\">" . $resident["program_id"] . "</div>
				<div id=\"category_id\">" . $resident["category_id"] . "</div>
				<div id=\"full_name\">" . $full_name . "</div>
				<div id=\"student_no\">" . $resident["student_no"] . "</div>
				<div id=\"program_description\">" . $resident_program["pgme_program_name"] . "</div>
				<div id=\"category_description\">" . $resident_category["category_description"] . "</div>
				<div id=\"query\">" . $queryResident . "</div>
				<div id=\"resident_not_found\"></div>
			 </div>";
	}
	if ($NOTICE) {
		echo "<div id=\"responseMsg\"><div id=\"resident_not_found\">Resident not found.</div>" . display_notice() . "</div>";
	}
	if ($ERROR) {
		echo "<div id=\"responseMsg\"><div id=\"resident_not_found\">Resident not found. (ERROR) </div>" . display_error() . "</div>";
	}
}
?>