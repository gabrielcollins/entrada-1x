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
 * Displays accommodation details to the user based on a particular event_id.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2009 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_CLERKSHIP"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
}
$BREADCRUMB[]	= array("url" => "", "title" => "Clinical Presentations List");

if (isset($_GET["rotation"]) && (clean_input($_GET["rotation"], "int"))) {
	$rotation = clean_input($_GET["rotation"], "int");
	$query = "	SELECT a.* 
				FROM `global_lu_objectives` AS a
				JOIN `".CLERKSHIP_DATABASE."`.`logbook_mandatory_objectives` AS b
				ON a.`objective_id` = b.`objective_id`
				AND `rotation_id` = ".$db->qstr($rotation)."
				AND a.`objective_active` = '1'
				JOIN `objective_organisation` AS c
				ON a.`objective_id` = c.`objective_id`
				AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."";
} else {
	$rotation = false;
	$query = "	SELECT a.*, b.`rotation_id`
				FROM `global_lu_objectives` AS a
				JOIN `".CLERKSHIP_DATABASE."`.`logbook_mandatory_objectives` AS b
				ON a.`objective_id` = b.`objective_id`
				AND a.`objective_active` = '1'
				JOIN `objective_organisation` AS c
				ON a.`objective_id` = c.`objective_id`
				AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
				ORDER BY b.`rotation_id` ASC";
}
$objectives = $db->GetAll($query);
if ($objectives) {
	echo "<ul style=\"list-style=\"none\">\n";
	if ($rotation) {
		echo "<h2>".$db->GetOne("SELECT `rotation_title` FROM `".CLERKSHIP_DATABASE."`.`global_lu_rotations` WHERE `rotation_id` = ".$db->qstr($rotation))."</h2>";
	} else {
		$last = 0;
	}
	foreach ($objectives as $objective) {
		if (isset($objective["rotation_id"]) && $objective["rotation_id"] != $last) {
			$last = $objective["rotation_id"];
			echo "<h2>".$db->GetOne("SELECT `rotation_title` FROM `".CLERKSHIP_DATABASE."`.`global_lu_rotations` WHERE `rotation_id` = ".$db->qstr($last))."</h2>";
		}
		echo "<img src=\"".ENTRADA_URL."/images/checkbox-off.gif\" /> ".$objective["objective_name"]."<br />\n";
	}
	echo "</ul>\n";
}