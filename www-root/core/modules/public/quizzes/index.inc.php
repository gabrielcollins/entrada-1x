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
 * This is the default section that is loaded when the quizzes module is
 * accessed without a defined section.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if((!defined("PARENT_INCLUDED")) || (!defined("IN_PUBLIC_QUIZZES"))) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
}
?>
<h1>My Quizzes</h1>
<?php

/**
 * @todo This should be a list of all of the quizzes the person was *supposed* to do
 * so it should go through each semester and each course and show all events that have
 * quizzes attached and then show what grade they got on the attempts they were
 * supposed to list.
 */
$query		= "	SELECT a.`quiz_score`, a.`quiz_value`, a.`updated_date` AS `quiz_completed_date`, b.*, c.`course_id`, c.`event_title`, c.`event_start`, d.`quiztype_title`
				FROM `quiz_progress` AS a
				LEFT JOIN `attached_quizzes` AS b
				ON b.`aquiz_id` = a.`aquiz_id`
				LEFT JOIN `events` AS c
				ON a.`content_type` = 'event' 
				AND c.`event_id` = a.`content_id`
				LEFT JOIN `quizzes_lu_quiztypes` AS d
				ON d.`quiztype_id` = b.`quiztype_id`
				WHERE a.`proxy_id` = ".$db->qstr($_SESSION["details"]["id"])."
				AND a.`progress_value` = 'complete'
				ORDER BY c.`course_id` ASC, c.`event_id` ASC, d.`quiztype_title` ASC, b.`quiz_title` ASC";
$results	= $db->GetAll($query);
if ($results) {
	$event_id			= 0;
	$curriculum_paths	= array();
	?>
	<table class="tableList" cellspacing="0" summary="List of Events">
	<colgroup>
		<col class="modified" />
		<col class="general" />
		<col class="date" />
		<col class="title" />
		<col class="responses" />
		<col class="responses" />
	</colgroup>
	<thead>
		<tr>
			<td class="modified">&nbsp;</td>
			<td class="general">Quiz Type</td>
			<td class="date">Completed Date</td>
			<td class="title">Quiz Title</td>
			<td class="responses">Score</td>
			<td class="responses">Percent</td>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach ($results as $result) {
		$percentage = ((round(($result["quiz_score"] / $result["quiz_value"]), 2)) * 100);

		if ($result["course_id"]) {
			if (!isset($curriculum_paths[$result["course_id"]])) {
				$curriculum_path = curriculum_hierarchy($result["course_id"]);

				$curriculum_paths[$result["course_id"]] = $curriculum_path;
			} else {
				$curriculum_path = $curriculum_paths[$result["course_id"]];
			}

			if ((is_array($curriculum_path)) && (count($curriculum_path))) {
				$course_title = implode(" &gt; ", $curriculum_path);
			}
		} else {
			$course_title = "No Associated Course";
		}

		if ($result["event_id"] != $event_id) {
			$event_id = $result["event_id"];
			echo "<tr>\n";
			echo "	<td colspan=\"6\">\n";
			echo "		".$course_title." &gt; <a href=\"".ENTRADA_URL."/events?id=".$result["event_id"]."\" style=\"font-weight: bold\">".html_encode($result["event_title"])."</a> <span class=\"content-small\">".date(DEFAULT_DATE_FORMAT, $result["event_start"])."</span>\n";
			echo "	</td>\n";
			echo "</tr>\n";
		}
		
		echo "<tr>\n";
		echo "	<td>&nbsp;</td>\n";
		echo "	<td>".html_encode($result["quiztype_title"])."</td>\n";
		echo "	<td>".date(DEFAULT_DATE_FORMAT, $result["quiz_completed_date"])."</td>\n";
		echo "	<td>".html_encode($result["quiz_title"])."</td>\n";
		echo "	<td>".$result["quiz_score"]."/".$result["quiz_value"]."</td>\n";
		echo "	<td>".$percentage."%</td>\n";
		echo "</tr>\n";
	}
	?>
	</tbody>
	</table>
	<?php
}
?>