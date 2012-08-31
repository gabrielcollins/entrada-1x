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
	if ((isset($COMPETENCY_ID) && $COMPETENCY_ID)) {
		$BREADCRUMB[] = array("url" => "", "title" => "Courses by Competency");
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
		$query = "SELECT a.* FROM `global_lu_objectives` AS a
					JOIN `objective_organisation` AS b
					ON a.`objective_id` = b.`objective_id`
					AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
					WHERE a.`objective_id` = ".$db->qstr($COMPETENCY_ID)." 
					AND a.`objective_active` = 1";
		$competency = $db->GetRow($query);
		if ($competency) {
			echo "<h1>Courses containing <strong>".$competency["objective_name"]."</strong> objectives</h1>\n";
			$courses = objectives_competency_courses($COMPETENCY_ID);
			$show_primary = $show_secondary = $show_tertiary = false;
			foreach ($courses as $course) {
				if ($course["importance"] == 1) {
					$show_primary = true;
				} elseif ($course["importance"] == 2) {
					$show_secondary = true;
				} elseif ($course["importance"] == 3) {
					$show_tertiary = true;
				}
			}
			if ($show_primary) {
				echo "<h2>Objectives linked as Primary</h2>\n";;
				echo "\n<ul>\n";
				foreach ($courses as $course) {
					if ($course["importance"] == 1) {
						echo "<li><a href=\"".ENTRADA_URL."/courses/objectives?section=course-competency-objectives&id=".$COMPETENCY_ID."&cid=".$course["course_id"]."\" style=\"text-decoration: none;\">".$course["course_name"]."</a></li>\n";
					}
				}
				echo "\n</ul><br/>\n";
			}
			if ($show_secondary) {
				echo "<h2>Objectives linked as Secondary</h2>\n";;
				echo "\n<ul>\n";
				foreach ($courses as $course) {
					if ($course["importance"] == 2) {
						echo "<li><a href=\"".ENTRADA_URL."/courses/objectives?section=course-competency-objectives&id=".$COMPETENCY_ID."&cid=".$course["course_id"]."\" style=\"text-decoration: none;\">".$course["course_name"]."</a></li>\n";
					}
				}
				echo "\n</ul><br/>\n";
			}
			if ($show_tertiary) {
				echo "<h2>Objectives linked as Tertiary</h2>\n";;
				echo "\n<ul>\n";
				foreach ($courses as $course) {
					if ($course["importance"] == 3) {
						echo "<li><a href=\"".ENTRADA_URL."/courses/objectives?section=course-competency-objectives&id=".$COMPETENCY_ID."&cid=".$course["course_id"]."\" style=\"text-decoration: none;\">".$course["course_name"]."</a></li>\n";
					}
				}
				echo "\n</ul>\n";
			}
		}
	}
}