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
 * This section is loaded when an individual wants to attempt a quiz.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if((!defined("PARENT_INCLUDED")) || (!defined("IN_PUBLIC_GRADEBOOK"))) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
}
/**
 * Update requested column to sort by.
 * Valid: date, teacher, title, phase
 */
if (isset($_GET["sb"])) {
	if (in_array(trim($_GET["sb"]), array("title", "type"))) {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = trim($_GET["sb"]);
	}

	$_SERVER["QUERY_STRING"] = replace_query(array("sb" => false));
} else {
	if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"])) {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = "title";
	}
}

/**
 * Update requested order to sort by.
 * Valid: asc, desc
 */
if (isset($_GET["so"])) {
	$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"] = ((strtolower($_GET["so"]) == "desc") ? "desc" : "asc");

	$_SERVER["QUERY_STRING"] = replace_query(array("so" => false));
} else {
	if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"])) {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"] = "asc";
	}
}

/**
 * Provide the queries with the columns to order by.
 */
switch ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) {
	case "title" :
		$sort_by = "`name` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
	break;
	case "type" :
		$sort_by = "`type` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
	break;
	default :
		$sort_by = "`order`";
	break;
}

if ($COURSE_ID) {
	
	$query = "	SELECT b.*, c.*, d.`handler` 
				FROM `courses` AS a
				JOIN `assessments` AS b
				ON a.`course_id` = b.`course_id`
				AND b.`cohort` = ".$db->qstr($ENTRADA_USER->getCohort())."
				JOIN `assessment_grades` AS c
				ON b.`assessment_id` = c.`assessment_id`
				AND c.`proxy_id` = ".$db->qstr($_SESSION["details"]["id"])."
				JOIN `assessment_marking_schemes` AS d
				ON b.`marking_scheme_id` = d.`id`
				WHERE a.`course_id` = ".$db->qstr($COURSE_ID)."
				AND (b.`release_date` = '0' OR b.`release_date` <= ".$db->qstr(time()).")
				AND (b.`release_until` = '0' OR b.`release_until` >= ".$db->qstr(time()).")
				AND b.`show_learner` = '1'
				ORDER BY ".$sort_by;
	$results = $db->GetAll($query);
	if ($results) {
		?>
		<h1><?php echo fetch_course_title($COURSE_ID); ?> Gradebook</h1>
		<table class="tableList" cellspacing="0" summary="List of Assessments">
			<colgroup>
				<col class="modified" />
				<col class="title" />
				<col class="general" />
				<col class="date-small" />
				<col class="gradebook" />
			</colgroup>
			<thead>
				<tr>
					<td class="modified">&nbsp;</td>
					<td class="title<?php echo (isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) && ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "title") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo public_order_link("title", "Assessment Title"); ?></td>
					<td class="general<?php echo (isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) && ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "type") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo public_order_link("type", "Assessment Type"); ?></td>
					<td class="date-small">Assessment Mark</td>
					<?php
					if (defined("GRADEBOOK_DISPLAY_WEIGHTED_TOTAL") && GRADEBOOK_DISPLAY_WEIGHTED_TOTAL) {
						?>
						<td class="date-small">Weighted Mark</td>
						<?php
					}
					?>
					<td class="gradebook">Percent Mark</td>
				</tr>
			</thead>
			<tbody>
			<?php
			foreach ($results as $result) {
				if (isset($result["value"])) {
					$grade_value = format_retrieved_grade($result["value"], $result);
				} else {
					$grade_value = "-";
				}
				echo "<tr id=\"gradebook-".$result["course_id"]."\">\n";
				echo "	<td>&nbsp;</td>\n";
				echo "	<td>".html_encode($result["name"])."</td>\n";
				echo "	<td>".($result["type"])."</td>\n";
				echo "	<td>".trim($grade_value).assessment_suffix($result)."</td>\n";
				if (defined("GRADEBOOK_DISPLAY_WEIGHTED_TOTAL") && GRADEBOOK_DISPLAY_WEIGHTED_TOTAL) {
					$gradebook = gradebook_get_weighted_grades($result["course_id"], $_SESSION["details"]["role"], $_SESSION["details"]["id"], $result["assessment_id"]);
					echo "	<td>".trim($gradebook["grade"])." / ".trim($gradebook["total"])."</td>\n";
				}
				echo "	<td style=\"text-align: right;\">".(($grade_value === "-") ? "-" : (($result["handler"] == "Numeric" ? ($result["value"] === "0" ? "0" : trim(trim(number_format(($grade_value / $result["numeric_grade_points_total"] * 100), 2), "0"), "."))."%" : (($result["handler"] == "Percentage" ? ("N/A") : $grade_value)))))."</td>\n";
				echo "</tr>\n";
			}
			?>
			</tbody>
		</table>
		<?php
	}
} else {
	$ERROR++;
	$ERRORSTR[] = "In order to review a gradebook, you must provide a valid course identifier.";

	echo display_error();

	application_log("error", "Failed to provide an course_id [".$COURSE_ID."] when attempting to view a gradebook.");
}