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
 * @author Developer: Ryan Warner <rw65@queensu.ca>
 * @copyright Copyright 2013 Queen's University. All Rights Reserved.
 *
*/

if((!defined("PARENT_INCLUDED")) || (!defined("IN_CURRICULUM"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("search", "read")) {
	add_error("Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {

	$PAGE_META["title"]			= "Curriculum Explorer";
	$PAGE_META["description"]	= "Allowing you to browse the curriculum by objective set, course, and date.";
	$PAGE_META["keywords"]		= "";

	$BREADCRUMB[] = array("url" => ENTRADA_RELATIVE."/curriculum/explorer", "title" => "Explorer");

	if (isset($_GET["mode"]) && $_GET["mode"] == "ajax") {
		$MODE = "ajax";
	}

	if (isset($_GET["id"]) && ($tmp_input = clean_input($_GET["id"], array("int")))) {
		$PROCESSED["id"] = $tmp_input;
		$query = "SELECT * FROM `global_lu_objectives` WHERE `objective_id` = ".$db->qstr($PROCESSED["id"]);
		$objective_info = $db->GetRow($query);
		$objective_name = $objective_info["objective_name"];
		$objective_description = $objective_info["objective_description"];
	}

	if (isset($MODE) && $MODE == "ajax") {

		if (!$PROCESSED["id"] && $_GET["objective_parent"] && ($tmp_input = clean_input($_GET["objective_parent"], array("int")))) {
			$PROCESSED["objective_parent"] = $tmp_input;
		} else {
			$PROCESSED["objective_parent"] = $PROCESSED["id"];
		}
		if ($_GET["year"] && ($tmp_input = clean_input($_GET["year"], array("int")))) {
			$PROCESSED["year"] = $tmp_input;
			$SEARCH_DURATION["start"]	= mktime(0, 0, 0, 9, 1, $PROCESSED["year"]);
			$SEARCH_DURATION["end"]		= strtotime("+1 year", $SEARCH_DURATION["start"]);
		}
		if ($_GET["course_id"] && ($tmp_input = clean_input($_GET["course_id"], array("int")))) {
			$PROCESSED["course_id"] = $tmp_input;
		}
		if ($_GET["group_id"] && ($tmp_input = clean_input($_GET["group_id"], array("int")))) {
			$PROCESSED["group_id"] = $tmp_input;
		}
		if ($_GET["count"] && ($tmp_input = clean_input($_GET["count"], array("int")))) {
			$PROCESSED["count"] = $tmp_input;
		}

		ob_clear_open_buffers();
		
		if ($PROCESSED["count"] == 1 || $PROCESSED["count"] == 2) {
			$query = "	SELECT b.`course_id`, b.`course_name`, `course_code`
						FROM `course_objectives` AS a
						JOIN `courses` As b
						ON a.`course_id` = b.`course_id`
						JOIN `objective_organisation` AS c
						ON a.`objective_id` = c.`objective_id`
						WHERE a.`objective_id` = ".$db->qstr($PROCESSED["objective_parent"])."
						AND b.`course_active` = 1
						AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation()).
						($PROCESSED["course_id"] ? " AND (a.`course_id` = " . $db->qstr($PROCESSED["course_id"]).", '1' = '1'))" : "");
			$mapped_courses = $db->GetAll($query);
		}

		if ($PROCESSED["count"] == 1 || $PROCESSED["count"] == 3) {
			$query = "	SELECT c.`event_id`, c.`event_title`, c.`event_start`, d.`objective_name`, e.`course_code`, e.`course_name`, d.`objective_description`
						FROM `event_objectives` AS a
						JOIN `objective_organisation` AS b
						ON a.`objective_id` = b.`objective_id`
						JOIN `events` AS c
						ON a.`event_id` = c.`event_id`
						JOIN `global_lu_objectives` AS d
						ON a.`objective_id` = d.`objective_id`
						JOIN `courses` AS e
						ON c.`course_id` = e.`course_id`
						JOIN `event_audience` AS f
						ON c.`event_id` = f.`event_id` ".
						($PROCESSED["group_id"] ? " AND f.`audience_type` = 'cohort' " : "")."
						WHERE a.`objective_id` = ".$db->qstr($PROCESSED["objective_parent"])."
						AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation()).
						($PROCESSED["year"] ? " AND (IF (c.`event_id` IS NOT NULL, c.`event_start` BETWEEN ".$db->qstr($SEARCH_DURATION["start"])." AND ".$db->qstr($SEARCH_DURATION["end"]).", '1' = '1'))" : "").
						($PROCESSED["course_id"] ? " AND (IF (c.`course_id` IS NOT NULL, c.`course_id` = " . $db->qstr($PROCESSED["course_id"]).", '1' = '1'))" : "").
						($PROCESSED["group_id"] ? " AND f.`audience_value` = " . $db->qstr($PROCESSED["group_id"]) : "")."
						ORDER BY c.`course_id`, c.`event_start` DESC";
			$event_objectives = $db->GetAll($query);
		}

		$query = "	SELECT a.`objective_id`, a.`objective_name`, a.`objective_parent`

					FROM `global_lu_objectives` AS a
					JOIN `objective_organisation` AS b
					ON a.`objective_id` = b.`objective_id`

					WHERE a.`objective_parent` = " . $db->qstr(($objective_info["objective_parent"] != 0 && $PROCESSED["objective_parent"] == $PROCESSED["id"] ? $objective_info["objective_parent"] : $PROCESSED["objective_parent"])) . "
					AND a.`objective_active` = '1'
					AND b.`organisation_id` = " . $db->qstr($ENTRADA_USER->getActiveOrganisation())."

					GROUP BY a.`objective_id`
					ORDER BY a.`objective_id` ASC";
		$child_objectives = $db->GetAll($query);

		if ($child_objectives) {
			$i = 0;
			foreach ($child_objectives as $child) {
				if ($PROCESSED["count"] == 1 || $PROCESSED["count"] == 2) {
					$course_count = array_sum(count_objective_child_courses($child["objective_id"]));
				} else {
					$course_count = 0;
				}
				$child_objectives[$i]["course_count"] = $course_count;

				if ($PROCESSED["count"] == 1 || $PROCESSED["count"] == 3) {
					$event_count = array_sum(count_objective_child_events($child["objective_id"], $SEARCH_DURATION["start"], $SEARCH_DURATION["end"], $PROCESSED["course_id"], $PROCESSED["group_id"]));
				} else {
					$event_count = 0;
				}
				$child_objectives[$i]["event_count"] = $event_count;

				$i++;
			}
		}

		$objective_parents = fetch_objective_parents($PROCESSED["objective_parent"]);
		if ($objective_parents) {
			$flattened_objectives = flatten_array($objective_parents);
			for ($i = 0; $i <= count($flattened_objectives); $i++) {
				if ($i % 2 == 0 && (!empty($flattened_objectives[$i]) && ($flattened_objectives[$i] != $PROCESSED["objective_parent"] || count($objective_parents) == 2))) {
					$o_breadcrumb[] = "<a class=\"objective-link\" href=\"".ENTRADA_RELATIVE. "/curriculum/explorer?objective_parent=".($flattened_objectives[$i+2] ? $flattened_objectives[$i+2] : 0)."&id=" . $flattened_objectives[$i]."&step=2\" data-id=\"".$flattened_objectives[$i]."\">".$flattened_objectives[$i+1]."</a>";
				} else if ($i % 2 == 0) {
					$o_breadcrumb[] = $flattened_objectives[$i+1];
				}
			}

			if ($o_breadcrumb) {
				$breadcrumb = implode(" / ", array_reverse($o_breadcrumb));
			} else {
				$breadcrumb = null;
			}
		}

		if ($event_objectives) {
			if (!$objective_name) {
				$objective_name = $event_objectives[0]["objective_name"];
				$objective_description = $event_objectives[0]["objective_description"];
			}
			foreach ($event_objectives as $objective) {
				$events[$objective["course_code"] . ": " . $objective["course_name"]][] = $objective;
			}
		}

		echo json_encode(array("status" => "success", "objective_parent" => $PROCESSED["objective_parent"], "events" => $events, "courses" => $mapped_courses, "child_objectives" => $child_objectives, "objective_name" => $objective_name, "objective_description" => $objective_description, "breadcrumb" => $breadcrumb));

		exit;

	}

	switch ($STEP) {
		case 2 :
			/*
			 * Objective Set ID
			 */
			if (isset($_GET["objective_parent"]) && ($tmp_input = clean_input($_GET["objective_parent"], array("int")))) {
				$PROCESSED["objective_parent"] = $tmp_input;
			}

			/*
			 * Course ID
			 */
			if (isset($_GET["course_id"]) && ($tmp_input = clean_input($_GET["course_id"], array("int")))) {
				$PROCESSED["course_id"] = $tmp_input;
			}

			/*
			 * Academic Year
			 */
			if (isset($_GET["year"]) && ($tmp_input = clean_input($_GET["year"], array("int")))) {
				$PROCESSED["year"] = $tmp_input;
				$SEARCH_DURATION["start"]	= mktime(0, 0, 0, 9, 1, $PROCESSED["year"]);
				$SEARCH_DURATION["end"]		= strtotime("+1 year", $SEARCH_DURATION["start"]);
			}

			/*
			 * Count
			 */
			if (isset($_GET["count"]) && ($tmp_input = clean_input($_GET["count"], array("int")))) {
				$PROCESSED["count"] = $tmp_input;
			}
			
			if (isset($_GET["group_id"]) && $tmp_input = clean_input($_GET["group_id"], "int")) {
				$PROCESSED["group_id"] = $tmp_input;
			}
		break;
		case 1 :
		default :
            continue;
		break;
	}

    search_subnavigation("explorer");
	?>
	<h1>Curriculum Explorer</h1>
	<form action="<?php echo ENTRADA_RELATIVE; ?>/curriculum/explorer" method="GET">
		<input type="hidden" name="step" value="2" />
		<table style="width: 100%" cellspacing="1" cellpadding="1" border="0">
			<colgroup>
				<col style=" width: 20%" />
				<col style=" width: 22%" />
				<col style=" width: 5%" />
				<col style=" width: 20%" />
				<col style=" width: 23%" />
			</colgroup>
			<tbody>
				<tr>
					<td><label for="objective-set" style="font-weight: bold; margin-right: 5px; white-space: nowrap">Objective Set:</label></td>
					<td>
						<select id="objective-set" name="objective_parent" >
							<?php
                            $query = "	SELECT a.* FROM `global_lu_objectives` AS a
                                        LEFT JOIN `objective_organisation` AS b
                                        ON a.`objective_id` = b.`objective_id`
                                        JOIN `objective_audience` AS c
                                        ON a.`objective_id` = c.`objective_id`
                                        WHERE a.`objective_parent` = '0'
                                        AND a.`objective_active` = '1'
                                        AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
                                        AND c.`audience_value` = 'all'
                                        ORDER BY a.`objective_order` ASC";
							
                            $objective_sets = $db->GetAssoc($query);
                            if ($objective_sets) {
                                foreach ($objective_sets as $objective_id => $objective_set) {
                                    ?>
                                    <option value="<?php echo $objective_id; ?>" <?php echo ($objective_id == $PROCESSED["objective_parent"]) ? " selected=\"selected\"" : "" ; ?>><?php echo (!empty($objective_set["objective_code"]) ? $objective_set["objective_code"] . " - " : "") . $objective_set["objective_name"]; ?></option>
                                    <?php
                                }
                            }
							?>
						</select>
					</td>
					<td>&nbsp;</td>
					<td><label for="count" style="font-weight: bold; margin-right: 5px; white-space: nowrap">Include:</label></td>
					<td>
						<select id="count" name="count" >
							<option value="1"<?php echo ($PROCESSED["count"] == "1" ? " selected=\"selected\"" : ""); ?>>Mapped Courses &amp; Learning Events</option>
							<option value="2"<?php echo ($PROCESSED["count"] == "2" ? " selected=\"selected\"" : ""); ?>>Mapped Courses Only</option>
							<option value="3"<?php echo ($PROCESSED["count"] == "3" ? " selected=\"selected\"" : ""); ?>>Mapped Learning Events Only</option>
						</select>
					</td>
				<tr>
					<td><label for="course" style="font-weight: bold; margin-right: 5px; white-space: nowrap">Course:</label></td>
					<td>
						<select id="course" name="course_id" >
							<option value="0">-- All Courses --</option>
							<?php
							$query = "	SELECT * FROM `courses`
										WHERE `organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
										AND `course_active` = '1'
										ORDER BY `course_code` ASC";
							$courses = $db->GetAll($query);
							if ($courses) {
								foreach ($courses as $course) {
									?>
                                    <option value="<?php echo $course["course_id"]; ?>" <?php echo ($course["course_id"] == $PROCESSED["course_id"]) ? " selected=\"selected\"" : "" ; ?>><?php echo (!empty($course["course_code"]) ? $course["course_code"] . " - " : "") . $course["course_name"]; ?></option>
									<?php
								}
							}
							?>
						</select>
					</td>
					<td>&nbsp;</td>
					<td><label for="year" style="font-weight: bold; margin-right: 5px; white-space: nowrap">Academic Year:</label></td>
					<td>
						<select id="year" name="year" >
							<option value="0"<?php echo ((!$SEARCH_YEAR)? " selected=\"selected\"" : ""); ?>>-- All Years --</option>
							<?php
							$start_year = (fetch_first_year() - 3);
							for ($year = $start_year; $year >= ($start_year - 3); $year--) {
                                ?>
								<option value="<?php echo $year; ?>"  <?php echo ($year == $PROCESSED["year"]) ? " selected=\"selected\"" : "" ; ?>><?php echo $year ."/" . ($year + 1); ?></option>
                                <?php
                            }
                            ?>
						</select>
					</td>
				</tr>
				<tr>
					<td><label for="cohort" style="font-weight: bold; margin-right: 5px; white-space: nowrap">Cohort:</label></td>
					<td>
						<select id="cohort" name="group_id" >
							<option value="0">-- All Cohorts --</option>
							<?php
							$query = "	SELECT a.*
										FROM `groups` AS a
										JOIN `group_organisations` AS b
										ON a.`group_id` = b.`group_id`
										WHERE b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
										AND a.`group_active` = 1
										ORDER BY `group_name` DESC";
							$cohorts = $db->GetAll($query);
							if ($cohorts) {
								foreach ($cohorts as $cohort) {
									?>
                                    <option value="<?php echo $cohort["group_id"]; ?>" <?php echo ($cohort["group_id"] == $PROCESSED["group_id"]) ? " selected=\"selected\"" : "" ; ?>><?php echo $cohort["group_name"]; ?></option>
									<?php
								}
							}
							?>
						</select>
					</td>
					<td>&nbsp;</td>
					<td colspan="2" align="right"><input type="submit" class="btn btn-primary" value="Browse" /></td>
				</tr>
			</tbody>
		</table>
	</form>
	<?php $badge_settings = (array) $translate->_("curriculum_explorer"); ?>
	<script type="text/javascript">
    var SITE_URL = "<?php echo ENTRADA_URL; ?>";
    var YEAR = "<?php echo $PROCESSED["year"]; ?>";
    var COURSE = "<?php echo $PROCESSED["course_id"]; ?>";
	var COHORT = "<?php echo $PROCESSED["group_id"]; ?>";
    var OBJECTIVE_PARENT = "<?php echo $PROCESSED["objective_parent"]; ?>";
    var COUNT = "<?php echo $PROCESSED["count"]; ?>";
	var BADGE_SUCCESS = "<?php echo $badge_settings["badge-success"]; ?>";
	var BADGE_WARNING = "<?php echo $badge_settings["badge-warning"]; ?>";
	var BADGE_IMPORTANT = "<?php echo $badge_settings["badge-important"]; ?>";
	var current_total = 0;
	</script>
	<script type="text/javascript" src="<?php echo ENTRADA_URL; ?>/javascript/curriculumexplorer.js" /></script>
	<script type="text/javascript">
	jQuery(function(){
		if (location.hash.length <= 0) {
			location.hash = "id-"+OBJECTIVE_PARENT;
		}
		var id = parseInt(location.hash.substring(4, location.hash.length));
		jQuery.getJSON("<?php echo ENTRADA_RELATIVE; ?>/curriculum/explorer?mode=ajax&id="+id + "&year=" + YEAR + "&course_id=" + COURSE + "&count=" + COUNT + "&group_id=" + COHORT, function(data) {
			var link = jQuery(document.createElement("a")).addClass(".objective-link").attr("data-id", "<?php echo $PROCESSED["id"]; ?>").html(data.objective_name);
			current_total = 0;
			jQuery.each(data.child_objectives, function (i, v) {
				current_total = current_total + v.event_count + v.course_count;
			});
			renderDOM(data, link);
			if (jQuery(".objective-link[data-id="+id+"]").length > 0) {
				jQuery(".objective-link[data-id="+id+"]").not(".back").addClass("active");
			}
		});
	});
	</script>
	<?php
	switch ($STEP) {
		case 2 :
            ?>
			<div id="objective-breadcrumb">
				<a class="objective-link" href="#" data-id="<?php echo $PROCESSED["objective_parent"]; ?>"><?php echo $objective_sets[$PROCESSED["objective_parent"]]["objective_name"]; ?></a>
			</div>
            <div id="objective-browser">
                
                <div id="objective-list">

                </div>
                <div id="objective-container">
                    <div id="objective-details"></div>
                </div>
            </div>
            <?php
		break;
	}
}