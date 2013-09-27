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
	if (in_array(trim($_GET["sb"]), array("title", "code", "date"))) {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["assignments"]["sb"] = trim($_GET["sb"]);
	}

	$_SERVER["QUERY_STRING"] = replace_query(array("sb" => false));
} else {
	if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["assignments"]["sb"])) {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["assignments"]["sb"] = "date";
	}
}

/**
 * Update requested order to sort by.
 * Valid: asc, desc
 */
if (isset($_GET["so"])) {
	$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["assignments"]["so"] = ((strtolower($_GET["so"]) == "desc") ? "desc" : "asc");

	$_SERVER["QUERY_STRING"] = replace_query(array("so" => false));
} else {
	if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["assignments"]["so"])) {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["assignments"]["so"] = "desc";
	}
}

/**
 * Provide the queries with the columns to order by.
 */
switch ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["assignments"]["sb"]) {
	case "title" :
		$sort_by = "e.`assignment_title` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["assignments"]["so"]);
	break;
	case "code" :
		$sort_by = "c.`course_code` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["assignments"]["so"]);
	break;
	case "date" :
	default :
		$sort_by = "e.`due_date` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["assignments"]["so"]);
	break;
}
	$group_ids = groups_get_enrolled_group_ids($ENTRADA_USER->getId());
	$group_ids_string = implode(', ',$group_ids);

	$courses = groups_get_enrolled_course_ids($ENTRADA_USER->getID());
	$query = "SELECT c.`course_code`, e.`assignment_id`, e.`assignment_title`, e.`due_date`, h.`grade_id` AS `grade_id`, h.`value` AS `grade_value`, i.`grade_weighting` AS `submitted_date`
						FROM `assignments` AS e
						JOIN `courses` AS c
						ON e.`course_id` = c.`course_id`
						LEFT JOIN `assessments` AS f
						ON e.`assessment_id` = f.`assessment_id`
						AND f.`cohort` IN (". $group_ids_string .")
						LEFT JOIN `assessment_grades` AS h
						ON h.`proxy_id` = ".$db->qstr($ENTRADA_USER->getID())."
						AND h.`assessment_id` = e.`assessment_id`
						LEFT JOIN `assessment_exceptions` AS i
						ON h.`proxy_id` = i.`proxy_id`
						AND h.`assessment_id` = i.`assessment_id`
						WHERE c.`course_id` IN (".(implode(',',$courses)).")
						AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
                        ORDER BY ".$sort_by;
	$assignments = $db->GetAll($query);
	if($assignments) {
        ?>
        <table class="tableList" cellspacing="0" summary="List of Gradebooks">
            <colgroup>
                <col class="date-small" />
                <col class="title" />
                <col class="general" />
            </colgroup>
            <thead>
                <tr>
                    <td class="borderl title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["assignments"]["sb"] == "title") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["assignments"]["so"]) : ""); ?>"><?php echo public_order_link("title", "Assignment Title", ENTRADA_RELATIVE."/profile/gradebook/assignments"); ?></td>
                    <td class="date-small<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["assignments"]["sb"] == "code") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["assignments"]["so"]) : ""); ?>"><?php echo public_order_link("code", "Course Code", ENTRADA_RELATIVE."/profile/gradebook/assignments"); ?></td>
                    <td class="general">Grade</td>
                    <td class="general<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["assignments"]["sb"] == "date") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["assignments"]["so"]) : ""); ?>"><?php echo public_order_link("date", "Due Date", ENTRADA_RELATIVE."/profile/gradebook/assignments"); ?></td>
                </tr>
            </thead>
            <tbody>
            <?php
                foreach ($assignments as $result) {
                    ?>
                    <tr id="gradebook-1">
                        <td><a href="<?php echo ENTRADA_RELATIVE."/".$MODULE;?>/gradebook/assignments?section=view&amp;assignment_id=<?php echo $result["assignment_id"];?>"><?php echo html_encode($result["assignment_title"]);?></a></td>
                        <td><a href="<?php echo ENTRADA_RELATIVE."/".$MODULE;?>/gradebook/assignments?section=view&amp;assignment_id=<?php echo $result["assignment_id"];?>"><?php echo html_encode($result["course_code"]);?></a></td>
                        <td><a href="<?php echo ENTRADA_RELATIVE."/".$MODULE;?>/gradebook/assignments?section=view&amp;assignment_id=<?php echo $result["assignment_id"];?>"><?php echo (isset($result["grade_value"]) ? $result["grade_value"] : "NA"); ?></a></td>
                        <td><a href="<?php echo ENTRADA_RELATIVE."/".$MODULE;?>/gradebook/assignments?section=view&amp;assignment_id=<?php echo $result["assignment_id"];?>"><?php echo ($result["due_date"]==0?'-':date(DEFAULT_DATE_FORMAT,$result["due_date"]));?></a></td>
                    </tr>
                    <?php
                }
            ?>
            </tbody>
        </table>
        <?php
    } else {
        ?>
        <div class="display-generic">
            There have been no assignments assigned to you in the system just yet.
        </div>
        <?php
    }