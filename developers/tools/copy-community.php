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
 * Run this script to copy a community (specified by community id) to a new
 * community.
 *
 * @author Unit: Medical Education Technology Unit
 * @author Developer: Don Zuiker <don.zuiker@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 *
 */
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . "/includes");

@ini_set("auto_detect_line_endings", 1);
@ini_set("display_errors", 1);
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

$ERROR = false;

output_notice("This script is used to copy an existing community.");
print "\nStep 1 - Please enter the Community ID of the Community to copy: ";
fscanf(STDIN, "%d\n", $COMMUNITY_ID); // reads number from STDIN
$community = $db->GetRow("SELECT *
						  FROM `communities`
						  WHERE `community_id` = " . $db->qstr($COMMUNITY_ID));
while (!$community) {
	print "\nPlease ensure you enter a valid Community ID: " . $db->ErrorMsg();
	fscanf(STDIN, "%d\n", $COMMUNITY_ID); // reads number from STDIN
	$community = $db->GetRow("SELECT * FROM `communities` WHERE `community_id` = " . $db->qstr($COMMUNITY_ID));
}

output_notice("Step 2: The pages from the given Community ID are inserted as pages for the new Community Site.");

$public_view = 0;
$troll_view = 0;
$time = time();

$COMM_INSERT["community_parent"] = 0;
$COMM_INSERT["category_id"] = 22;
$COMM_INSERT["community_url"] = "/pgme_generic";
$COMM_INSERT["community_template"] = "pgcourse";
$COMM_INSERT["community_theme"] = "default";
$COMM_INSERT["community_shortname"] = "pgme_generic";
$COMM_INSERT["community_title"] = "Generic Program";
$COMM_INSERT["community_description"] = "This is the curriculum website for the Generic  program and is under construction at this time.";
$COMM_INSERT["community_keyword"] = "pgme postgrad medicine generic ";
$COMM_INSERT["community_email"] = " ";
$COMM_INSERT["community_website"] = "";
$COMM_INSERT["community_protected"] = 1;
$COMM_INSERT["community_registration"] = 4;
$COMM_INSERT["community_members"] = "";
$COMM_INSERT["community_active"] = 1;
$COMM_INSERT["community_opened"] = $time;
$COMM_INSERT["community_notifications"] = 0;
$COMM_INSERT["sub_communities"] = 0;
$COMM_INSERT["storage_usage"] = 3923656;
$COMM_INSERT["storage_max"] = 104857600;
$COMM_INSERT["updated_date"] = $time;
$COMM_INSERT["updated_by"] = 4264;

if (!(($db->AutoExecute("communities", $COMM_INSERT, "INSERT")) && ($new_community_id = $db->Insert_Id()))) {
	output_error("There was a problem while inserting the new community.");
	exit ();
}

print "\n\nNew community ID: " . $new_community_id;

/*
 * Activate each community module (all 7 that are currently available).
 * module_id - Module Name - page_type
 * 1 - Announcements - announcements
 * 2 - Discussions - discussions
 * 3 - Document Sharing - shares
 * 4 - Events - events
 * 5 - Galleries - galleries
 * 6 - Polling - polls
 * 7 - Quizzes - quizzes...need to confirm as many Quizzes in the database are of page_type = default
 * 
 */
$community_modules = array(1, 2 ,3, 4, 5, 6, 7);
foreach ($community_modules as $module_id) {
	if (!communities_module_activate($new_community_id, $module_id)) {
		echo("Unable to active module " . (int) $module_id . " for new community id " . (int) $new_community_id . ". Database said: " . $db->ErrorMsg());
	}
}

echo "\nCommunity Modules activated";

//Add _this_ user as a member
if (!$db->AutoExecute("community_members", array("community_id" => $new_community_id, "proxy_id" => 4264, "member_active" => 1, "member_joined" => time(), "member_acl" => 1), "INSERT")) {
	exit("Failed to insert you as a member of the new community (Community ID: " . $new_community_id);
}

//Set the Community Page options
$query = "INSERT INTO `community_page_options` (`community_id`, `option_title`) VALUES
									(" . $db->qstr($new_community_id) . ", 'show_announcements')";
if (!$db->Execute($query)) {
	echo("Could not add 'show_announcement` option for community [" . $new_community_id . "]. Database said: " . $db->ErrorMsg());
}
$query = "INSERT INTO `community_page_options` (`community_id`, `option_title`) VALUES
									(" . $db->qstr($new_community_id) . ", 'show_events')";
if (!$db->Execute($query)) {
	echo("Could not add 'show_event` option for community [" . $new_community_id . "]. Database said: " . $db->ErrorMsg());
}
$query = "INSERT INTO `community_page_options` (`community_id`, `option_title`) VALUES
									(" . $db->qstr($new_community_id) . ", 'show_history')";
if (!$db->Execute($query)) {
	echo("Could not add 'show_history` option for community [" . $new_community_id . "]. Database said: " . $db->ErrorMsg());
}

echo "\nCommunity Page Options inserted";

//fetch the community pages from the community to copy
$community_pages_arr = $db->GetAll("SELECT `parent_id`, `page_order`, `page_type`, `menu_title`, `page_title`, `page_url`, `page_content`, `page_active`, `page_visible`, `allow_member_view`, `allow_troll_view`, `allow_public_view`
									FROM `community_pages`
									WHERE `community_id` = " . $COMMUNITY_ID);

//re-insert each community page with the new community id.
if ($community_pages_arr) {
	$count = 0; //count the number of pages that are inserted
	
	foreach ($community_pages_arr as $page) {

		//Reconnect any subpages to the correct parent page.
		//Check to see if this is a subpage
		if ($page["parent_id"] != 0) {
			//echo "\nparent id: " . $page["parent_id"];
			$query = "SELECT `parent_id`, `page_order`, `page_type`, `menu_title`, `page_title`, `page_url`, `page_content`, `page_active`, `page_visible`, `allow_member_view`, `allow_troll_view`, `allow_public_view`
										FROM `community_pages`
										WHERE `community_id` = " . $COMMUNITY_ID . "
										AND cpage_id = " . $page["parent_id"];
			//echo "parent page query: ". $query;
			//Since this is a subpage, get the parent page
			$parent_page = $db->GetRow($query);

			//echo "\nParent page_url: " . $parent_page["page_url"];
			//Now that we know the parent page, we can get the new parent_id based on page_url
			//since page_url should be unique.
			$new_parent_id = $db->GetOne("SELECT `cpage_id`
										  FROM `community_pages`
										  WHERE `community_id` = " . $new_community_id . "
										  AND page_url = " . $db->qstr($parent_page["page_url"]));

			//Finally, we can update the subpage with the correct parent_id
			//echo "\nNew Parent ID: " . $new_parent_id;
			$page["parent_id"] = $new_parent_id;
		}

		//Insert the page if it is active
		if ($page["page_active"] != 0) {

			$query = "INSERT INTO `community_pages`
		(`community_id`,
		`parent_id`,
		`page_order`,
		`page_type`,
		`menu_title`,
		`page_title`,
		`page_url`,
		`page_content`,
		`page_active`,
		`page_visible`,
		`allow_member_view`,
		`allow_troll_view`,
		`allow_public_view`,
		`updated_date`,
		`updated_by`)
		VALUES(
		" . $db->qstr($new_community_id) . ",
		" . $db->qstr($page["parent_id"]) . ",
		" . $db->qstr($page["page_order"]) . ",
		" . $db->qstr($page["page_type"]) . ",
		" . $db->qstr($page["menu_title"]) . ",
		" . $db->qstr($page["page_title"]) . ",
		" . $db->qstr($page["page_url"]) . ",
		" . $db->qstr($page["page_content"]) . ",
		" . $db->qstr($page["page_active"]) . ",
		" . $db->qstr($page["page_visible"]) . ",
		" . $db->qstr($page["allow_member_view"]) . ",
		" . $db->qstr($page["allow_troll_view"]) . ",
		" . $db->qstr($page["allow_public_view"]) . ",
		" . $db->qstr($time) . ",
		4264)";

			if (!$db->Execute($query)) {
				output_error("There was a problem while copying the pages into the new community where Community ID is (" . $new_community_id . ").");
				print '\nError inserting community pages: ' . $db->ErrorMsg();
				exit ();
			}
		$count++;
		}
	}
}

echo "\n" . $count . " Community Pages copied";

echo "\n\nSuccesfully copied Community ID: " . $COMMUNITY_ID . " to new Community ID: " . $new_community_id . "\n";
exit();
?>