<?php

/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Serves the categories list up in a select box.
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
						dirname(__FILE__) . "/../core",
						dirname(__FILE__) . "/../core/includes",
						dirname(__FILE__) . "/../core/library",
						get_include_path(),
					)));

	/**
	 * Include the Entrada init code.
	 */
	require_once("init.inc.php");

	date_default_timezone_set(DEFAULT_TIMEZONE);

	session_start();

	$proxy_id = $_GET['id'];
	$args = $_GET['t'];
	$rid = $_GET["rid"];

	$args = explode(",", $args);
	$table = "mtd_schedule";
	$table2 = "mtd_locale_duration";

	if (strpos($rid, "|") !== false) {
		$ids = explode("|", $rid);

		for ($i = 0; $i < count($ids); $i++) {

			$query = "DELETE FROM `" . DATABASE_NAME . "`.`" . $table . "`
			WHERE `id` = " . $db->qstr($ids[$i]);

			if ($db->Execute($query)) {
				$query = "DELETE FROM `" . DATABASE_NAME . "`.`" . $table2 . "`WHERE `schedule_id` = " . $db->qstr($id);
				if (!$db->Execute($query)) {
					echo $db->ErrorMsg();
					exit;
				}
			} else {
				echo $db->ErrorMsg();
				exit;
			}
		}
	} else {
		$id = $rid;

		$query = "DELETE FROM `" . DATABASE_NAME . "`.`" . $table . "`
		WHERE `id` = " . $db->qstr($id);

		if ($db->Execute($query)) {
			$query = "DELETE FROM `" . DATABASE_NAME . "`.`" . $table2 . "`WHERE `schedule_id` = " . $db->qstr($id);
			if (!$db->Execute($query)) {
				echo $db->ErrorMsg();
				exit;
			}
		} else {
			echo $db->ErrorMsg();
			exit;
		}
	}
}
?>