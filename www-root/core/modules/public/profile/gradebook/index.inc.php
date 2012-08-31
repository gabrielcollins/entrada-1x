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

if((!defined("PARENT_INCLUDED")) || (!defined("IN_PUBLIC_GRADEBOOK"))) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
}
?>
<h1>My Gradebooks</h1>
<?php
	
/**
 * Update requested column to sort by.
 * Valid: date, teacher, title, phase
 */
if (isset($_GET["sb"])) {
	if (in_array(trim($_GET["sb"]), array("title", "assessments"))) {
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
	default :
		$sort_by = "`course_name` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
	break;
	case "code" :
		$sort_by = "`course_code` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
	break;
	case "assessments" :
		$sort_by = "`assessments` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
	break;
}

$cohort = groups_get_cohort($_SESSION["details"]["id"]);
$query = "	SELECT a.*, COUNT(b.`assessment_id`) AS `assessments` 
			FROM `courses` AS a
			JOIN `assessments` AS b
			ON a.`course_id` = b.`course_id`
			AND b.`cohort` = ".$db->qstr($cohort["group_id"])."
			AND (b.`release_date` = '0' OR b.`release_date` <= ".$db->qstr(time()).")
			AND (b.`release_until` = '0' OR b.`release_until` > ".$db->qstr(time()).")
			AND b.`show_learner` = '1'
			GROUP BY a.`course_id`
			ORDER BY ".$sort_by;
$results = $db->GetAll($query);
if ($results) {
	?>
	<table class="tableList" cellspacing="0" summary="List of Gradebooks">
		<colgroup>
			<col class="modified" />
			<col class="date-small" />
			<col class="title" />
			<col class="general" />
		</colgroup>
		<thead>
			<tr>
				<td class="modified">&nbsp;</td>
				<td class="date-small<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "code") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo public_order_link("code", "Course Code"); ?></td>
				<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "title") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo public_order_link("title", "Course Title"); ?></td>
				<td class="general<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "assessments") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo public_order_link("assessments", "Assessments"); ?></td>
				<?php
				if (defined("GRADEBOOK_DISPLAY_WEIGHTED_TOTAL") && GRADEBOOK_DISPLAY_WEIGHTED_TOTAL) {
					?>
					<td class="general">Weighted Total</td>
					<?php
				}
				?>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach ($results as $result) {
			echo "<tr id=\"gradebook-".$result["course_id"]."\"".((!$result["course_active"]) ? " class=\"disabled\"" : "").">\n";
			echo "	<td".((!$result["course_active"]) ? " class=\"disabled\"" : "").">&nbsp;</td>\n";
			echo "	<td".((!$result["course_active"]) ? " class=\"disabled\"" : "")."><a href=\"".ENTRADA_URL."/".$MODULE."/gradebook?section=view&amp;id=".$result["course_id"]."\">".html_encode($result["course_code"])."</a></td>\n";
			echo "	<td".((!$result["course_active"]) ? " class=\"disabled\"" : "")."><a href=\"".ENTRADA_URL."/".$MODULE."/gradebook?section=view&amp;id=".$result["course_id"]."\">".html_encode($result["course_name"])."</a></td>\n";
			echo "	<td".((!$result["course_active"]) ? " class=\"disabled\"" : "")."><a href=\"".ENTRADA_URL."/".$MODULE."/gradebook?section=view&amp;id=".$result["course_id"]."\">".($result["assessments"])."</a></td>\n";
			if (defined("GRADEBOOK_DISPLAY_WEIGHTED_TOTAL") && GRADEBOOK_DISPLAY_WEIGHTED_TOTAL) {
				$gradebook = gradebook_get_weighted_grades($result["course_id"], $_SESSION["details"]["role"], $_SESSION["details"]["id"]);
				echo "	<td>".trim($gradebook["grade"])." / ".trim($gradebook["total"])."</td>\n";
			}
			echo "</tr>\n";
		}
		?>
		</tbody>
	</table>
	<?php
} else {
	echo "<div class=\"display-notice\">";
	echo "	<h3>No Course Gradebooks Available</h3>";
	echo "	There are currently no assessments in the system for your graduating year.";
	echo "</div>";
}

?>