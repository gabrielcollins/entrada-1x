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
	if ((isset($COURSE_ID) && $COURSE_ID)) {
		$BREADCRUMB[] = array("url" => ENTRADA_URL."/courses/objectives?section=course-objectives&cid=".$COURSE_ID, "title" => $module_singular_name . " Objectives");
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

		$primary = $secondary = $tertiary = array();

		$query = "SELECT * FROM `global_lu_objectives` AS a
					JOIN `objective_organisation` AS b
					ON a.`objective_id` = b.`objective_id`
					AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
					WHERE a.`objective_parent` IN (
						SELECT `objective_id` FROM `global_lu_objectives`
						WHERE `objective_parent` = ".$db->qstr(CURRICULAR_OBJECTIVES_PARENT_ID)."
					)
					AND a.`objective_active` = 1
					ORDER BY a.`objective_order` ASC";
		$competencies = $db->GetAll($query);
		foreach ($competencies as $competency) {
			$query = "SELECT `objective_id` FROM `global_lu_objectives` AS a
						JOIN `objective_organisation` AS b
						ON a.`objective_id` = b.`objective_id`
						AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
						WHERE a.`objective_parent` = ".$db->qstr($competency["objective_id"])."
						AND a.`objective_active` = 1
						UNION
						SELECT a.`objective_id` AS `objective_id` FROM `global_lu_objectives` AS a
						JOIN `global_lu_objectives` AS b
						ON a.`objective_parent` = b.`objective_parent`
						AND a.`objective_active` = 1
						AND b.`objective_active` = 1
						JOIN `objective_organisation` AS c
						ON a.`objective_id` = c.`objective_id`
						AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
						WHERE b.`objective_parent` = ".$db->qstr($competency["objective_id"]);
			$competency_objectives = $db->GetAll($query);
			$competency_objective_ids_string = false;
			if ($competency_objectives) {
				foreach ($competency_objectives as $objective) {
					if (!$competency_objective_ids_string) {
						$competency_objective_ids_string = $db->qstr($objective["objective_id"]);
					} else {
						$competency_objective_ids_string .= ", ".$db->qstr($objective["objective_id"]);
					}
				}
			}
			$query = "	SELECT * FROM `course_objectives` AS a
						JOIN `global_lu_objectives` AS b
						ON a.`objective_id` = b.`objective_id`
						JOIN `objective_organisation` AS c
						ON b.`objective_id` = c.`objective_id`
						AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
						WHERE a.`course_id` = ".$db->qstr($COURSE_ID)."
						AND b.`objective_active` = 1
						AND b.`objective_parent` NOT IN (".$objective_ids_string.")
						".($competency_objective_ids_string ? "AND b.`objective_id` IN (".$competency_objective_ids_string.")" : "")."
						AND a.`objective_type` = 'course'
						UNION
						SELECT a.*, b.* FROM `course_objectives` AS a
						JOIN `global_lu_objectives` AS b
						ON a.`objective_id` = b.`objective_id`
						JOIN `global_lu_objectives` AS c
						ON b.`objective_parent` = c.`objective_id`
						JOIN `objective_organisation` AS d
						ON b.`objective_id` = d.`objective_id`
						AND d.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
						WHERE a.`course_id` = ".$db->qstr($COURSE_ID)."
						AND b.`objective_parent` NOT IN (".$objective_ids_string.")
						AND c.`objective_parent` NOT IN (".$objective_ids_string.")
						".($competency_objective_ids_string ? "AND c.`objective_id` IN (".$competency_objective_ids_string.")" : "")."
						AND a.`objective_type` = 'course'
						AND b.`objective_active` = 1
						AND c.`objective_active` = 1
						ORDER BY `importance` ASC, `objective_order` ASC";
			$objectives_array = $db->GetAll($query);
			if ($objectives_array) {
				foreach ($objectives_array AS $objective) {
					$query = "	SELECT a.*, b.`objective_details` FROM `global_lu_objectives` AS a
								LEFT JOIN `course_objectives` AS b
								ON a.`objective_id` = b.`objective_id`
								AND b.`course_id` = ".$db->qstr($COURSE_ID)."
								AND b.`objective_type` = 'course'
								JOIN `objective_organisation` AS c
								ON b.`objective_id` = c.`objective_id`
								AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
								WHERE a.`objective_parent` = ".$db->qstr($objective["objective_id"])."
								AND a.`objective_active` = 1
								ORDER BY b.`importance` ASC, a.`objective_order` ASC";
					$child_objectives = $db->GetAll($query);
					$objectives[$objective["objective_id"]]["children"] = array();
	
					foreach ($child_objectives as $child_objective) {
						$objectives[$objective["objective_id"]]["children"][] = $child_objective;
					}
					$objectives[$objective["objective_id"]]["objective"] = $objective;				
				}
			} else {
				$query = "	SELECT a.*, b.* FROM `course_objectives` AS a
							JOIN `global_lu_objectives` AS b
							ON a.`objective_id` = b.`objective_id`
							JOIN `global_lu_objectives` AS c
							ON c.`objective_parent` = b.`objective_id`
							JOIN `objective_organisation` AS d
							ON b.`objective_id` = d.`objective_id`
							AND d.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
							WHERE a.`course_id` = ".$db->qstr($COURSE_ID)."
							AND b.`objective_active` = 1
							AND c.`objective_active` = 1
							AND b.`objective_parent` NOT IN (".$objective_ids_string.")
							".($competency_objective_ids_string ? "AND b.`objective_id` IN (".$competency_objective_ids_string.")" : "")."
							AND a.`objective_type` = 'course'
							ORDER BY a.`importance` ASC, b.`objective_order` ASC";
				$objectives_array = $db->GetAll($query);
				if ($objectives_array) {
					foreach ($objectives_array AS $objective) {
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
									ORDER BY a.`importance` ASC,a.`objective_order` ASC";
						$child_objectives = $db->GetAll($query);
						$objectives[$objective["objective_id"]]["children"] = array();
		
						foreach ($child_objectives as $child_objective) {
							$objectives[$objective["objective_id"]]["children"][] = $child_objective;
						}
						$objectives[$objective["objective_id"]]["objective"] = $objective;				
					}
				}
			}
		}
		
		foreach ($competencies as $competency) {
			$competencies_array[$competency["objective_id"]] = $competency;
		}
		$last_competency = false;
		$last_importance = false;
		$upper_level_id = false;
		$collapse = false;
		$course = $db->GetRow("SELECT * FROM `courses` WHERE `course_id` = ".$db->qstr($COURSE_ID));

		echo "<h1>".html_encode($course["course_name"])."</h1>";
		$additional_level = false;
		$level_added = false;
		for ($i = 1; $i <= 3; $i++) {
			foreach ($objectives as $objective_id => $objective) {
				if ($objective["objective"]["importance"] == $i) {
					
					if (array_key_exists($objective["objective"]["objective_parent"], $competencies_array)) {
						$query = "	SELECT a.* FROM `global_lu_objectives` AS a
									JOIN `objective_organisation` AS b
									ON a.`objective_id` = b.`objective_id`
									AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
									WHERE a.`objective_id` = ".$db->qstr($objective["objective"]["objective_parent"])."
									AND a.`objective_active` = 1";
						$competency = $db->GetRow($query);
					} else {
						$query = "	SELECT b.* FROM `global_lu_objectives` AS a
									JOIN `global_lu_objectives` AS b
									ON a.`objective_parent` = b.`objective_id`
									JOIN `objective_organisation` AS c
									ON b.`objective_id` = c.`objective_id`
									AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
									WHERE b.`objective_id` = ".$db->qstr($objective["objective"]["objective_parent"])."
									AND a.`objective_active` = 1
									AND b.`objective_active` = 1
									AND b.`objective_parent` = ".$db->qstr(CURRICULAR_OBJECTIVES_PARENT_ID);
						$competency = $db->GetRow($query);
						if (!$competency) {
							$query = "	SELECT c.* FROM `global_lu_objectives` AS a
										JOIN `global_lu_objectives` AS b
										ON a.`objective_parent` = b.`objective_id`
										JOIN `global_lu_objectives` AS c
										ON b.`objective_parent` = c.`objective_id`
										AND a.`objective_active` = 1
										AND b.`objective_active` = 1
										AND c.`objective_active` = 1
										JOIN `objective_organisation` AS d
										ON c.`objective_id` = d.`objective_id`
										AND d.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
										WHERE b.`objective_id` = ".$db->qstr($objective["objective"]["objective_parent"]);
							$competency = $db->GetRow($query);
						}
					}
					if ($objective["objective"]["objective_parent"] != $competency["objective_id"]) {
						$additional_level = true;
					} else {
						$additional_level = false;
					}
					if (!$last_importance || $last_importance != $objective["objective"]["importance"]) {
						if ($collapse) {
							echo "</div>\n";
						}
						echo "<h2 title=\"".($objective["objective"]["importance"] == 3 ? "Tertiary" : ($objective["objective"]["importance"] == 2 ? "Secondary" : "Primary"))." Objectives Section\" class=\"".(isset($collapse) && $collapse ? "collapsed" : "expanded")."\">".($objective["objective"]["importance"] == 3 ? "Tertiary" : ($objective["objective"]["importance"] == 2 ? "Secondary" : "Primary"))." Objectives</h2>\n";
						echo "<div id=\"".($objective["objective"]["importance"] == 3 ? "tertiary" : ($objective["objective"]["importance"] == 2 ? "secondary" : "primary"))."-objectives-section\">\n";
						$last_competency = false;
						$collapse = true;
					}
					if (!$last_competency || ($competency["objective_id"] != $last_competency)) {
						echo "<h3>".html_encode($competency["objective_name"])." Objectives</h3>\n";
					}
					echo "<ul>\n";
					
					if ((!$additional_level && $upper_level_id) || ($additional_level && $upper_level_id && ($upper_level_id != $objective["objective"]["objective_parent"]))) {
						echo "</li>\n";
						echo "</ul>\n";
						$upper_level_id = false;
					}
					if ($additional_level && !$upper_level_id) {
						$query = "SELECT a.* FROM `global_lu_objectives` AS a
									JOIN `objective_organisation` AS b
									ON a.`objective_id` = b.`objective_id`
									AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
									WHERE a.`objective_id` = ".$db->qstr($objective["objective"]["objective_parent"])." 
									AND a.`objective_active` = 1";
						$upper_objective = $db->GetRow($query);
						if ($upper_objective) {
							echo "<li>\n<a title=\"View events in this course related to this objective.\" href=\"".ENTRADA_URL."/courses/objectives?section=course-objective-events&cid=".$COURSE_ID."&oid=".$upper_objective["objective_id"]."\">".$upper_objective["objective_name"]."</a><div class=\"content-small\">".(isset($upper_objective["objective_details"]) && $upper_objective["objective_details"] ? $upper_objective["objective_details"] : $upper_objective["objective_description"])."</div>\n";
							echo "<ul>\n";
							$upper_level_id = $upper_objective["objective_id"];
						}
					}
					if (!$additional_level) {
						echo "<li>\n<a title=\"View events in this course related to this objective.\" href=\"".ENTRADA_URL."/courses/objectives?section=course-objective-events&cid=".$COURSE_ID."&oid=".$objective["objective"]["objective_id"]."\">".$objective["objective"]["objective_name"]."</a><div style=\"color: #000;\" class=\"content-small\">".(isset($objective["objective"]["objective_details"]) && $objective["objective"]["objective_details"] ? $objective["objective"]["objective_details"] : $objective["objective"]["objective_description"])."</div>\n";
					} else {
						$letter = preg_split('/[\.]+[\d]?/', $objective["objective"]["objective_name"]);
						echo "<li>\n".$letter[1].". <span style=\"color: #000;\" class=\"content-small\">".(isset($objective["objective"]["objective_details"]) && $objective["objective"]["objective_details"] ? $objective["objective"]["objective_details"] : $objective["objective"]["objective_description"])." <a class=\"content-small external\" title=\"View events in this course related to this objective.\" href=\"".ENTRADA_URL."/courses/objectives?section=course-objective-events&cid=".$COURSE_ID."&oid=".$objective["objective"]["objective_id"]."\">".$objective["objective"]["objective_name"]."</a></span>\n";
					}
					if (isset($objective["children"]) && count($objective["children"])) {
						echo "<ul class=\"pad\">\n";
						foreach ($objective["children"] as $objective_child) {
							$child_letter = preg_split('/[\.]+[\d]?/', $objective_child["objective_name"]);
							echo "<li class=\"pad-top\">".$child_letter[1].". <span style=\"color: #000;\" class=\"content-small\">".(isset($objective_child["objective_details"]) && $objective_child["objective_details"] ? $objective_child["objective_details"] : $objective_child["objective_description"])." <a class=\"content-small external\" title=\"View events in this course related to this objective.\" href=\"".ENTRADA_URL."/courses/objectives?section=course-objective-events&cid=".$COURSE_ID."&oid=".$objective_child["objective_id"]."\">".$objective_child["objective_name"]."</a></span></li>\n";
						}
						echo "</ul>\n";
					}
					echo "</li>\n";
					echo "</ul>\n";
					$last_importance = $objective["objective"]["importance"];	
					$last_competency = $competency["objective_id"];		
				}
			}
		}
	}
}