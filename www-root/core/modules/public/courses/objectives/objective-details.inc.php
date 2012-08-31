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
 * This file displays the list of objectives pulled 
 * from the entrada.global_lu_objectives table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if((!defined("PARENT_INCLUDED")) || (!defined("IN_OBJECTIVES"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
		header("Location: ".ENTRADA_URL);
		exit;
} else {
	if ((isset($OBJECTIVE_ID) && $OBJECTIVE_ID) && (!isset($API) || !$API)) {
		$query = "	SELECT a.*".(isset($COURSE_ID) && $COURSE_ID ? ", b.`objective_details`" : "")." 
					FROM `global_lu_objectives` AS a
					".(isset($COURSE_ID) && $COURSE_ID ? "
					LEFT JOIN `course_objectives` AS b
					ON a.`objective_id` = b.`objective_id`
					AND b.`course_id` = ".$db->qstr($COURSE_ID) : "")."
					JOIN `objective_organisation` AS c
					ON a.`objective_id` = c.`objective_id`
					AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
					WHERE a.`objective_id` = ".$db->qstr($OBJECTIVE_ID)."
					AND a.`objective_active` = 1";
		$objective = $db->GetRow($query);
		$BREADCRUMB[] = array("url" => "", "title" => html_encode($objective["objective_name"])." Details");
		echo "<h1>".html_encode($objective["objective_name"])."</h2>\n";
		if (isset($objective["objective_details"]) && $objective["objective_details"]) {
			echo "<p>".$objective["objective_details"]."</p>\n";
		} elseif (isset($objective["objective_description"]) && $objective["objective_description"]) {
			echo "<p>".$objective["objective_description"]."</p>\n";
		} else {
			$NOTICE++;
			$NOTICESTR[] = "No details were found for this objective.";
			echo display_notice();
		}
	} elseif ((isset($OBJECTIVE_ID) && $OBJECTIVE_ID) && $API) {
		
		$query = "	SELECT a.*".(isset($COURSE_ID) && $COURSE_ID ? ", b.`objective_details`" : "")." 
					FROM `global_lu_objectives` AS a
					".(isset($COURSE_ID) && $COURSE_ID ? "
					LEFT JOIN `course_objectives` AS b
					ON a.`objective_id` = b.`objective_id`
					AND b.`course_id` = ".$db->qstr($COURSE_ID) : "")."
					JOIN `objective_organisation` AS c
					ON a.`objective_id` = c.`objective_id`
					AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
					WHERE a.`objective_id` = ".$db->qstr($OBJECTIVE_ID)."
					AND a.`objective_active` = 1";
		$objective = $db->GetRow($query);
		ob_clean();
		echo "<h2>".$objective["objective_name"]."</h2>\n";
		echo "<img style=\"position: absolute; top: 5px; right: 10px; cursor: pointer;\" src=\"".ENTRADA_URL."/images/window_close.gif\" onclick=\"Control.Modal.close()\"/>\n";
		if (isset($objective["objective_details"]) && $objective["objective_details"]) {
			echo "<p>".$objective["objective_details"]."</p>\n";
		} elseif (isset($objective["objective_description"]) && $objective["objective_description"]) {
			echo "<p>".$objective["objective_description"]."</p>\n";
		} else {
			$NOTICE++;
			$NOTICESTR[] = "No details were found for this objective.";
			echo display_notice();
		}
		exit;
	}
}