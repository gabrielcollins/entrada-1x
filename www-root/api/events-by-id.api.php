<?php
/**
 * Online Course Resources [Pre-Clerkship]
 * @author Unit: Medical Education Technology Unit
 * @author Director: Dr. Benjamin Chen <bhc@post.queensu.ca>
 * @author Developer: Matt Simpson <simpson@post.queensu.ca>
 * @version 3.0
 * @copyright Copyright 2006 Queen's University, MEdTech Unit
 *
 * $Id: personnel.api.php 1140 2010-04-27 18:59:15Z simpson $
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

if ((isset($_SESSION["isAuthorized"])) && ((bool) $_SESSION["isAuthorized"])) {
	if (isset($_POST["related_event_id"]) && ($tmp_input = clean_input($_POST["related_event_id"], array("trim", "int")))) {
		$event_id = $tmp_input;
	}
	if (isset($_GET["course_id"]) && ($tmp_input = clean_input($_GET["course_id"], array("trim", "int")))) {
		$course_id = $tmp_input;
	}
	if (isset($_GET["parent_id"]) && ($tmp_input = clean_input($_GET["parent_id"], array("trim", "int")))) {
		$parent_id = $tmp_input;
	}

	if ($event_id && $course_id) {
		$query = "SELECT * FROM `events` WHERE `event_id` = ".$db->qstr($event_id);
		echo "<ul>\n";
		$results = $db->GetAll($query);
		if ($results) {
			foreach($results as $result) {
				echo "\t<li id=\"".(int) $result["event_id"]."\">".html_encode($result["event_title"])."<span class=\"informal content-small\"><br />".($course_id == $result["course_id"] ? ($parent_id == $result["parent_id"] ? "Invalid event, already related" : "") : "Invalid event, wrong course")."</span></li>\n";
			}
		} else {
			echo "\t<li id=\"0\"><span class=\"informal\">Event ID &quot;<strong>".html_encode($event_id)."&quot;</strong> was not found</span></li>";
		}
		echo "</ul>";
	}
} else {
	application_log("error", "Events by id API accessed without valid session_id.");
}