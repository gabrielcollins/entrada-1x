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
	if ((isset($COURSE_ID) && $COURSE_ID) && (isset($COMPETENCY_ID) && $COMPETENCY_ID)) {
		$BREADCRUMB[] = array("url" => "", "title" => "Course Objectives by Competency");
		?>
		<style type="text/css">
		li.pad-top {
			margin-bottom: 10px;
		}

		ul.pad {
			padding-top: 10px;
			padding-bottom: 10px;
		}
		</style>
		<?php
		$objective_ids_string = objectives_build_course_objectives_id_string($COURSE_ID);
		$competency_ids_string = objectives_build_objective_descendants_id_string($COMPETENCY_ID);

		$primary = $secondary = $tertiary = array();

		$query = "	SELECT * FROM `course_objectives` AS a
					JOIN `global_lu_objectives` AS b
					ON a.`objective_id` = b.`objective_id`
					JOIN `objective_organisation` AS c
					ON b.`objective_id` = c.`objective_id`
					AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
					WHERE a.`course_id` = ".$db->qstr($COURSE_ID)."
					AND (
						b.`objective_id` IN (".$competency_ids_string.")
						AND b.`objective_parent` NOT IN (".$objective_ids_string.")
					)
					AND a.`objective_type` = 'course'
					AND b.`objective_active` = 1
					ORDER BY a.`importance` ASC";
		$objectives = $db->GetAll($query);
		if ($objectives) {
			foreach ($objectives AS $objective) {
				$query = "	SELECT a.*, b.`objective_details` FROM `global_lu_objectives` AS a
							LEFT JOIN `course_objectives` AS b
							ON a.`objective_id` = b.`objective_id`
							AND b.`course_id` = ".$db->qstr($COURSE_ID)."
							AND b.`objective_type` = 'course'
							JOIN `objective_organisation` AS c
							ON a.`objective_id` = c.`objective_id`
							AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
							WHERE a.`objective_parent` = ".$db->qstr($objective["objective_id"])."
							AND a.`objective_active` = 1
							ORDER BY a.`objective_order` ASC";
				$child_objectives = $db->GetAll($query);
				if ($objective["importance"] == 1) {
					$primary[$objective["objective_id"]]["children"] = array();
				} elseif ($objective["importance"] == 2) {
					$secondary[$objective["objective_id"]]["children"] = array();
				} elseif ($objective["importance"] == 3) {
					$tertiary[$objective["objective_id"]]["children"] = array();
				}

				foreach ($child_objectives as $child_objective) {
					if ($objective["importance"] == 1) {
						$primary[$objective["objective_id"]]["children"][] = $child_objective;
					} elseif ($objective["importance"] == 2) {
						$secondary[$objective["objective_id"]]["children"][] = $child_objective;
					} elseif ($objective["importance"] == 3) {
						$tertiary[$objective["objective_id"]]["children"][] = $child_objective;
					}
				}

				if ($objective["importance"] == 1) {
					$primary[$objective["objective_id"]]["objective"] = $objective;
				} elseif ($objective["importance"] == 2) {
					$secondary[$objective["objective_id"]]["objective"] = $objective;
				} elseif ($objective["importance"] == 3) {
					$tertiary[$objective["objective_id"]]["objective"] = $objective;
				}
			}
		}
		
		$competency = $db->GetRow("SELECT a.* 
									FROM `global_lu_objectives` AS a
									JOIN `objective_organisation` AS b
									ON a.`objective_id` = b.`objective_id`
									AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
									WHERE a.`objective_id` = ".$db->qstr($COMPETENCY_ID))." 
									AND a.`objective_active` = 1";
		$course = $db->GetRow("SELECT * FROM `courses` WHERE `course_id` = ".$db->qstr($COURSE_ID));

		echo "<h1>".html_encode($course["course_name"])."</h1>";
		echo "<h2>".html_encode($competency["objective_name"])." Objectives</h2>\n";

		if ($primary) {
			echo "<h3>Primary Objectives</h3>\n";
			echo "<ul>\n";
			foreach ($primary as $objective) {
				echo "<li>\n<a title=\"View events in this course related to this objective.\" href=\"".ENTRADA_URL."/courses/objectives?section=course-objective-events&cid=".$COURSE_ID."&oid=".$objective["objective"]["objective_id"]."\">".$objective["objective"]["objective_name"]."</a><div class=\"content-small\">".(isset($objective["objective"]["objective_details"]) && $objective["objective"]["objective_details"] ? $objective["objective"]["objective_details"] : $objective["objective"]["objective_description"])."</div>\n";
				if (isset($objective["children"]) && count($objective["children"])) {
					echo "<ul class=\"pad\">\n";
					foreach ($objective["children"] as $objective_child) {
						echo "<li class=\"pad-top\"><a title=\"View events in this course related to this objective.\" href=\"".ENTRADA_URL."/courses/objectives?section=course-objective-events&cid=".$COURSE_ID."&oid=".$objective_child["objective_id"]."\">".$objective_child["objective_name"]."</a><div class=\"content-small\">".(isset($objective_child["objective_details"]) && $objective_child["objective_details"] ? $objective_child["objective_details"] : $objective_child["objective_description"])."</div></li>\n";
					}
					echo "</ul>\n";
				}
				echo "</li>\n";
			}
			echo "</ul>\n";
		}
		if ($secondary) {
			echo "<h3>Secondary Objectives</h3>\n";
			echo "<ul>\n";
			foreach ($secondary as $objective) {
				echo "<li>\n<a title=\"View events in this course related to this objective.\" href=\"".ENTRADA_URL."/courses/objectives?section=course-objective-events&cid=".$COURSE_ID."&oid=".$objective["objective"]["objective_id"]."\">".$objective["objective"]["objective_name"]."</a><div class=\"content-small\">".(isset($objective["objective"]["objective_details"]) && $objective["objective"]["objective_details"] ? $objective["objective"]["objective_details"] : $objective["objective"]["objective_description"])."</div>\n";
				if (isset($objective["children"]) && count($objective["children"])) {
					echo "<ul class=\"pad\">\n";
					foreach ($objective["children"] as $objective_child) {
						echo "<li class=\"pad-top\"><a title=\"View events in this course related to this objective.\" href=\"".ENTRADA_URL."/courses/objectives?section=course-objective-events&cid=".$COURSE_ID."&oid=".$objective_child["objective_id"]."\">".$objective_child["objective_name"]."</a><div class=\"content-small\">".(isset($objective_child["objective_details"]) && $objective_child["objective_details"] ? $objective_child["objective_details"] : $objective_child["objective_description"])."</div></li>\n";
					}
					echo "</ul>\n";
				}
				echo "</li>\n";
			}
			echo "</ul>\n";
		}
		if ($tertiary) {
			echo "<h3>Tertiary Objectives</h3>\n";
			echo "<ul>\n";
			foreach ($tertiary as $objective) {
				echo "<li>\n<a title=\"View events in this course related to this objective.\" href=\"".ENTRADA_URL."/courses/objectives?section=course-objective-events&cid=".$COURSE_ID."&oid=".$objective["objective"]["objective_id"]."\">".$objective["objective"]["objective_name"]."</a><div class=\"content-small\">".(isset($objective["objective"]["objective_details"]) && $objective["objective"]["objective_details"] ? $objective["objective"]["objective_details"] : $objective["objective"]["objective_description"])."</div>\n";
				if (isset($objective["children"]) && count($objective["children"])) {
					echo "<ul class=\"pad\">\n";
					foreach ($objective["children"] as $objective_child) {
						echo "<li class=\"pad-top\"><a title=\"View events in this course related to this objective.\" href=\"".ENTRADA_URL."/courses/objectives?section=course-objective-events&cid=".$COURSE_ID."&oid=".$objective_child["objective_id"]."\">".$objective_child["objective_name"]."</a><div class=\"content-small\">".(isset($objective_child["objective_details"]) && $objective_child["objective_details"] ? $objective_child["objective_details"] : $objective_child["objective_description"])."</div></li>\n";
					}
					echo "</ul>\n";
				}
				echo "</li>\n";
			}
			echo "</ul>\n";
		}
	}
}