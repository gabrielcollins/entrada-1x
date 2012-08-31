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
 * @author Developer: Brandon Thorn <brandon.thorn@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 *
*/

if((!defined("PARENT_INCLUDED")) || (!defined("IN_PUBLIC_GRADEBOOK"))) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
}
?>
<h1>My Assignments</h1>
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
			GROUP BY a.`course_id`
			ORDER BY ".$sort_by;
$results = $db->GetAll($query);
if (true) {
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
				<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "title") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo public_order_link("title", "Assignment Title"); ?></td>
				<td class="date-small<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "code") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo public_order_link("code", "Course Code"); ?></td>
				<?php
				if (true) {
					?>
					<td class="general">Grade</td>
					<?php
				}
				?>
				<td class="general<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "assessments") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo public_order_link("assessments", "Due Date"); ?></td>

			</tr>
		</thead>
		<tbody>
		<?php
		
		//$query = "SELECT a.*, b.`course_code` FROM	`assignments` AS a JOIN `courses` AS b ON a.`course_id` = b.`course_id` WHERE `assignment_active` = '1'";
		$query = "	SELECT c.`course_code`, e.`assignment_id`, e.`assignment_title`,e.`due_date`, h.`grade_id` AS `grade_id`, h.`value` AS `grade_value`, i.`grade_weighting` AS `submitted_date`
							FROM `assignments` AS e
							JOIN `courses` AS c
							ON e.`course_id` = c.`course_id`
							LEFT JOIN `assessments` AS f							
							ON e.`assessment_id` = f.`assessment_id`
							JOIN `groups` AS g 
							ON f.`cohort` = g.`group_id`
							JOIN `group_members` AS m
							ON g.`group_id` = m.`group_id`
							AND m.`proxy_id` = ".$db->qstr($ENTRADA_USER->getProxyId())."
							LEFT JOIN `assessment_grades` AS h 
							ON m.`proxy_id` = h.`proxy_id`
							AND h.`assessment_id` = e.`assessment_id`
							LEFT JOIN `assessment_exceptions` AS i
							ON m.`proxy_id` = i.`proxy_id`
							AND h.`assessment_id` = i.`assessment_id`;";
		$results = $db->GetAll($query);
		if($results){
			foreach ($results as $result) {
				echo "<tr id=\"gradebook-1\">\n";
				echo "	<td>&nbsp;</td>\n";
				echo "	<td><a href=\"".ENTRADA_URL."/".$MODULE."/gradebook/assignments?section=view&amp;id=".$result["assignment_id"]."\">".html_encode($result["assignment_title"])."</a></td>\n";
				echo "	<td><a href=\"".ENTRADA_URL."/".$MODULE."/gradebook/assignments?section=view&amp;id=".$result["assignment_id"]."\">".html_encode($result["course_code"])."</a></td>\n";
				echo "	<td><a href=\"".ENTRADA_URL."/".$MODULE."/gradebook/assignments?section=view&amp;id=".$result["assignment_id"]."\">".(isset($result["grade_value"])?$result["grade_value"]:'NA')."</a></td>\n";
				echo "	<td><a href=\"".ENTRADA_URL."/".$MODULE."/gradebook/assignments?section=view&amp;id=".$result["assignment_id"]."\">".($result["due_date"]==0?'No due date':date('M d,Y h:i a',$result["due_date"]))."</a></td>\n";

				echo "</tr>\n";
			}
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