<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * This file displays Evaluation form response rates for the current
 * program website.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Don Zuiker <don.zuiker@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 *
 */
if ((!defined("COMMUNITY_INCLUDED")) || (!defined("IN_PGRESPONSE"))) {
	header("Location: " . COMMUNITY_URL);
	exit;
} elseif (!$COMMUNITY_LOAD) {
	exit;
} elseif (true) { ?>
	<p>Sorry, response rates are not available on this site at the moment.</p>
<?php
} else {
	$community_title = $community_details["community_title"];
	$query = "SELECT *
			  FROM  `pg_one45_community`
			  WHERE `community_name` like " . $db->qstr(trim($community_title) . "%");

	//echo $query . "\n";
	$results = $db->GetAll($query);

	$one45_names_array = array();
	foreach ($results as $result) {
		$one45_names_array[] = $db->qstr($result["one45_name"]);
	}

	$query = "SELECT max(gen_date)
			  FROM  `pg_eval_response_rates`";

	$max_gen_date = $db->GetOne($query);

	$query = "SELECT percent_complete
			  FROM  `pg_eval_response_rates`
			  WHERE `program_name` = 'TOTALS'
			  AND `response_type` = 'RESIDENT'
			  AND gen_date = '" . $max_gen_date . "'";

	$total_resident_response_rate = $db->GetOne($query);

	$query = "SELECT percent_complete
			  FROM  `pg_eval_response_rates`
			  WHERE `program_name` = 'TOTALS'
			  AND `response_type` = 'FACULTY'
			  AND gen_date = '" . $max_gen_date . "'";

	$total_faculty_response_rate = $db->GetOne($query);
?>
	<h3>The following ITER and Faculty Evaluation Completion Rate data covers blocks 1 and 2 as of <?php echo date_format(date_create($max_gen_date), "F j, Y"); ?></h3>
	<br />
<?php
	$response_rate_result_flag = 0;//If true then the PGME Overall response rates will be shown too.
	
	foreach ($one45_names_array as $one45_name) {
		$resident_completed = 0;
		$resident_distributed = 0;
		$resident_percent_complete = 0;
		$faculty_completed = 0;
		$faculty_distributed = 0;
		$faculty_percent_complete = 0;
		// Get the response rates for this program
		$query = "SELECT *
			  FROM  `pg_eval_response_rates`
			  WHERE `program_name` = " . $one45_name . "
			  AND gen_date = '" . $max_gen_date . "'";

		$results = $db->GetAll($query);

		foreach ($results as $result) {

			if ($result["response_type"] == "Resident") {
				$resident_completed = $result["completed"];
				$resident_distributed = $result["distributed"];
				$resident_percent_complete = $result["percent_complete"];
			} else if ($result["response_type"] == "Faculty") {
				$faculty_completed = $result["completed"];
				$faculty_distributed = $result["distributed"];
				$faculty_percent_complete = $result["percent_complete"];
			}
		}

		if ($resident_distributed && $faculty_distributed) {
			$response_rate_result_flag = 1; //This program has at least one set of results.
?>
			<strong><?php echo substr($one45_name, 1, strlen($one45_name) - 2) ?></strong>
			<br />
			<br />
			<div id="resident_response">
				<strong>Faculty and Rotation Evaluation Forms</strong>
				<table>
					<tr><td>Completed:</td> <td><?php echo $resident_completed ?></td></tr>
					<tr><td>Distributed:</td> <td><?php echo $resident_distributed ?></td></tr>
					<tr><td>Response Rate:</td> <td><?php echo $resident_percent_complete ?>%</td></tr>
				</table>
			</div>
			<br />
			<div id="faculty_response">
				<strong>In-training Evaluation Reports (ITER)</strong>
				<table>
					<tr><td>Completed:</td> <td><?php echo $faculty_completed ?></td></tr>
					<tr><td>Distributed:</td> <td><?php echo $faculty_distributed ?></td></tr>
					<tr><td>Response Rate:</td> <td><?php echo $faculty_percent_complete ?>%</td></tr>
				</table>
			</div>
			<hr />
			<br />
<?php
		}
	}
	if ($response_rate_result_flag && $total_resident_response_rate && $total_faculty_response_rate) {
?>
		<div id="total_response">
			<strong>Overall Postgrad Medicine Totals</strong>
			<table>
				<tr><td>Resident's Evaluation of Faculty Forms Completion Rate:</td> <td><?php echo $total_resident_response_rate ?>%</td></tr>
				<tr><td>In-training Evaluation Reports Completion Rate:</td> <td><?php echo $total_faculty_response_rate ?>%</td></tr>
			</table>
		</div>
<?php
	} else {
?>
		<p>Sorry, there are no response rates to display for your program.</p>
<?php
	}
}