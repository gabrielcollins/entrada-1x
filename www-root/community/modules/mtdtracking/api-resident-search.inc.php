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
if ((!defined("COMMUNITY_INCLUDED")) || !defined("IN_MTDTRACKING")) {
	header("Location: " . COMMUNITY_URL);
	exit;
} else {

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

	/**
	 * Clears all open buffers so we can return a plain response for the Javascript.
	 */
	ob_clear_open_buffers();

	if (isset($_POST["resident_name"]) && ($tmp_input = clean_input($_POST["resident_name"], array("trim", "notags")))) {
		$resident_name = $tmp_input;
	} elseif (isset($_GET["resident_name"]) && ($tmp_input = clean_input($_GET["resident_name"], array("trim", "notags")))) {
		$resident_name = $tmp_input;
	} else {
		$resident_name = "";
	}

	if ($resident_name) {
		$query = "	SELECT a.`proxy_id`, CONCAT_WS(', ', a.`last_name`, a.`first_name`) AS `resident_name`, b.pgme_program_name
					FROM `" . AUTH_DATABASE . "`.`user_data_resident` a,
						 `" . DATABASE_NAME . "`.`mtd_pgme_moh_programs` b
				    WHERE CONCAT_WS(', ', a.`last_name`, a.`first_name`) LIKE " . $db->qstr("%" . $resident_name . "%") . "
						  AND b.`moh_program_id` = a.`program_id`
					ORDER BY `resident_name` ASC";
		echo "<ul>\n";
		$results = $db->GetAll($query);
		if ($results) {
			foreach ($results as $result) {
				echo "\t<li id=\"" . (int) $result["proxy_id"] . "\">" . html_encode($result["resident_name"]) . "<span class=\"informal content-small\"><br />" . html_encode($result["pgme_program_name"]) . "</span></li>\n";
			}
		} else {
			echo "\t<li id=\"0\"><span class=\"informal\">&quot;<strong>" . html_encode($resident_name) . "&quot;</strong> was not found</span></li>";
		}
		echo "</ul>";
	}
}
exit();
?>
