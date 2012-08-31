#!/usr/bin/php
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
 * Run this script to connect a course (or multiple courses) to a community,
 * thus making transforming the Community into a "Course Website".
 *   
 * @author Unit: Medical Education Technology Unit
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 */

set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__)."/includes");

@ini_set("auto_detect_line_endings", 1);
@ini_set("display_errors", 1);
@ini_set("magic_quotes_runtime", 0);
set_time_limit(0);

if((!isset($_SERVER["argv"])) || (@count($_SERVER["argv"]) < 1)) {
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

$ERROR = false;

output_notice("This script is used to generate the needed data to make a community into a 'course community'. The only information you need to perform this task is the ID of the community and the IDs of any courses you wish to link to it.");
output_notice("Step 1: Enter the community and course ids.");
print "\nPlease enter Community ID: ";
fscanf(STDIN, "%d\n", $COMMUNITY_ID); // reads number from STDIN
$community = $db->GetRow("SELECT * FROM `communities` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID));
while (!$community) {
	print "\nPlease ensure you enter a valid Community ID: ".$db->ErrorMsg();
	fscanf(STDIN, "%d\n", $COMMUNITY_ID); // reads number from STDIN
	$community = $db->GetRow("SELECT * FROM `communities` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID));
}

print "\nPlease enter Course ID: ";
fscanf(STDIN, "%d\n", $COURSE_ID); // reads number from STDIN
$result = $db->GetRow("SELECT * FROM `courses` WHERE `course_id` = ".$db->qstr($COURSE_ID));
while (!$result) {
	print "\nPlease ensure you enter a valid Course ID: ";
	fscanf(STDIN, "%d\n", $COURSE_ID); // reads number from STDIN
	$result = $db->GetRow("SELECT * FROM `courses` WHERE `course_id` = ".$db->qstr($COURSE_ID));
}
$COURSE_IDS = array();
$COURSE_IDS_STRING = "";
while ($COURSE_ID != 0) {
	if (array_search($COURSE_ID, $COURSE_IDS) === false) {
		$COURSE_IDS[] = $COURSE_ID;
		if (!$COURSE_IDS_STRING) {
			$COURSE_IDS_STRING = $db->qstr($COURSE_ID);
		} else {
			$COURSE_IDS_STRING .= ", ".$db->qstr($COURSE_ID);
		}
	}
	$COURSE_ID = 0;
	print "\nYou  may now enter an additional Course ID (enter 0 to continue): ";
	fscanf(STDIN, "%d\n", $COURSE_ID); // reads number from STDIN
	if ($COURSE_ID) {
		$result = $db->GetRow("SELECT * FROM `courses` WHERE `course_id` = ".$db->qstr($COURSE_ID));
		while (!$result) {
			print "\nPlease ensure you enter a valid Course ID (enter 0 to continue): ";
			fscanf(STDIN, "%d\n", $COURSE_ID); // reads number from STDIN
			if ($COURSE_ID != 0) {
				$result = $db->GetRow("SELECT * FROM `courses` WHERE `course_id` = ".$db->qstr($COURSE_ID));
			} else {
				$result = true;
			}
		}
	}
}
output_notice("Step 2: The courses are linked to the community.");
foreach ($COURSE_IDS as $course_id) {
	if (!$db->Execute("INSERT INTO `community_courses` (`community_id`, `course_id`) VALUES (".$db->qstr($COMMUNITY_ID).", ".$db->qstr($course_id).")")) {
		output_error("There was a problem while creating the link between the community and the course with ID (".$COURSE_ID.").");
		$ERROR = true;
	}
}

output_notice("Step 3: The pages needed in course communities are created in the selected community, and all the existing pages are placed after them.");

$public_view = 0;
$troll_view = 0;

$time = time();

$query = "	INSERT INTO `community_pages` (`community_id`, `parent_id`, `page_order`, `page_type`, `menu_title`, `page_title`, `page_url`, `page_content`, `page_active`, `page_visible`, `allow_member_view`, `allow_troll_view`, `allow_public_view`, `updated_date`, `updated_by`) VALUES
			(".$db->qstr($COMMUNITY_ID).", 0, 0, 'course', 'Home', 'Home', '', ' ', 1, 1, 1, ".$db->qstr($troll_view).", ".$db->qstr($public_view).", ".$db->qstr($time).", 1),
			(".$db->qstr($COMMUNITY_ID).", 0, 1, 'course', 'Rationale', 'Rationale', 'rationale', ' ', 1, 1, 1, ".$db->qstr($troll_view).", ".$db->qstr($public_view).", ".$db->qstr($time).", 1),
			(".$db->qstr($COMMUNITY_ID).", 0, 2, 'course', 'Learning Outcomes', 'Learning Outcomes', 'outcomes', ' ', 1, 1, 1, ".$db->qstr($troll_view).", ".$db->qstr($public_view).", ".$db->qstr($time).", 1),
			(".$db->qstr($COMMUNITY_ID).", 0, 3, 'course', 'Evaluation and Grading', 'Evaluation and Grading', 'evaluation', ' ', 1, 1, 1, ".$db->qstr($troll_view).", ".$db->qstr($public_view).", ".$db->qstr($time).", 1),
			(".$db->qstr($COMMUNITY_ID).", 0, 4, 'course', 'Course Materials', 'Course Materials', 'materials', ' ', 1, 1, 1, ".$db->qstr($troll_view).", ".$db->qstr($public_view).", ".$db->qstr($time).", 1),
			(".$db->qstr($COMMUNITY_ID).", 0, 5, 'course', 'Practice Schedule', 'Practice Schedule', 'practice_schedule', ' ', 1, 1, 1, ".$db->qstr($troll_view).", ".$db->qstr($public_view).", ".$db->qstr($time).", 1),
			(".$db->qstr($COMMUNITY_ID).", 0, 6, 'course', 'Course Calendar', 'Course Calendar', 'course_calendar', ' ', 1, 1, 1, ".$db->qstr($troll_view).", ".$db->qstr($public_view).", ".$db->qstr($time).", 1)";
if (!$db->Execute($query)) {
	output_error("There was a problem while creating the default pages in Community ID (".$COMMUNITY_ID.").");
	$ERROR = true;
}
if (!$ERROR) {
	$current_pages = $db->GetAll("SELECT `cpage_id`, `page_order`, `page_url`, `parent_id` FROM `community_pages` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `page_type` != 'course'");

	$course_pages = $db->GetAll("SELECT `page_url` FROM `community_pages` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `page_type` = 'course'");

	$restricted_urls = array();
	if ($course_pages) {
		foreach ($course_pages as $page) {
			$restricted_urls[] = $page["page_url"];
		}
	}
	if ($current_pages) {
		foreach ($current_pages as $page) {
			if (!((int)$page["parent_id"])) {
				$page["page_order"] = ((int)$page["page_order"]) + 12;
			}
			if (!$page["page_url"]) {
				$page["page_url"] = "home_old";
				$page["page_order"] = ((int)$page["page_order"]) - 1;
			} elseif (array_search($page["page_url"], $restricted_urls) !== false) {
				$page["page_url"] .= "_old";
			}
			$db->AutoExecute("community_pages", $page, "UPDATE", "`community_id` = ".$db->qstr($COMMUNITY_ID)." AND `cpage_id` = ".$db->qstr($page["cpage_id"]));
			output_error($db->ErrorMsg());
			
		}
	}
}

output_notice("Step 4: All Course Directors, Curriculum Coordinators and Program Coordinators are being added as administrators in the community.");

$new_contact_ids = array();
$existing_contact_ids = array();

$query = "	SELECT `proxy_id` FROM `course_contacts`
			WHERE `course_id` IN (".$COURSE_IDS_STRING.")
			AND `contact_type` IN ('director', 'cdirector', 'ccoordinator')";
$results = $db->GetAll($query);
if ($results) {
	foreach ($results as $result) {
		if ($result["proxy_id"]) {
			$new_contact_ids[] = $result["proxy_id"];
		}
	}
}

$query = "	SELECT `pcoord_id` FROM `courses` 
			WHERE `course_id` IN (".$COURSE_IDS_STRING.")";
$results = $db->GetAll($query);
$results = $db->GetAll($query);
if ($results) {
	foreach ($results as $result) {
		if ($result["pcoord_id"]) {
			$new_contact_ids[] = $result["pcoord_id"];
		}
	}
}

$query = "	SELECT * FROM `community_members` 
			WHERE `community_id` = ".$db->qstr($COMMUNITY_ID);
$results = $db->GetAll($query);
if ($results) {
	foreach ($results as $result) {
		if (array_search($result["proxy_id"], $new_contact_ids) !== false) {
			$existing_contact_ids[] = $result["proxy_id"];
			unset($new_contact_ids[array_search($result["proxy_id"], $new_contact_ids)]);
		}
	}
}

foreach ($new_contact_ids as $proxy_id) {
	if (!$db->AutoExecute("community_members", array("proxy_id" => $proxy_id, "community_id" => $COMMUNITY_ID, "member_active" => 1, "member_joined" => time(), "member_acl" => 1), "INSERT")) {
		output_error("There was a problem while giving administrative rights to a user ID (".$result["proxy_id"].") in the Community ID (".$COMMUNITY_ID.").");
		$ERROR = true;
	}
}

foreach ($existing_contact_ids as $proxy_id) {
	if (!$db->AutoExecute("community_members", array("member_active" => 1, "member_acl" => 1), "UPDATE", "`proxy_id` = ".$db->qstr($proxy_id)." AND `community_id` = ".$db->qstr($COMMUNITY_ID))) {
		output_error("There was a problem while giving administrative rights to a user ID (".$proxy_id.") in the Community ID (".$COMMUNITY_ID.").");
		$ERROR = true;
	}
}

if (!$ERROR) {
	output_notice("Step 5: Once all the needed data has been successfully created, the community is updated to use the 'course' theme and template.");
	$db->Execute("UPDATE `communities` SET `community_theme` = 'course', `community_template` = 'course' WHERE `community_id` = ".$db->qstr($COMMUNITY_ID));
	output_notice("All queries were run successfully, the community with ID (".$COMMUNITY_ID.") is now linked with the courses with IDs (".implode(", ", $COURSE_IDS).").");
}
print "\n\n";
?>