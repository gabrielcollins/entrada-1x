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
 * Allows students to add electives to the system which still need to be approved.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2009 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_CLERKSHIP"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('logbook', 'read')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/public/clerkship/logbook", "title" => "View Entries");

	if (isset($_GET["id"]) && ((int)$_GET["id"])) {
		$PROXY_ID = $_GET["id"];
		$student = false;
	} else {
		$PROXY_ID = $_SESSION["details"]["id"];
		$student = true;
	}
	
	/**
	 * Update requested column to sort by.
	 * Valid: date, teacher, title, phase
	 */
	if(isset($_GET["sb"])) {
		if(in_array(trim($_GET["sb"]), array("rotation" , "location", "site", "patient", "date", "age"))) {
			if (trim($_GET["sb"]) == "rotation") {
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["value"]	= "e.`rotation_title`";
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= "e.`rotation_title`";
			} elseif (trim($_GET["sb"]) == "location") {
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["value"]	= "b.`location`";
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= "b.`location`";
			} elseif (trim($_GET["sb"]) == "site") {
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["value"]	= "c.`site_name`";
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= "c.`site_name`";
			} elseif (trim($_GET["sb"]) == "patient") {
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["value"]	= "a.`patient_info`";
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= "a.`patient_info`";
			} elseif (trim($_GET["sb"]) == "date") {
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["value"]	= "a.`encounter_date`";
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= "a.`encounter_date`";
			} elseif (trim($_GET["sb"]) == "age") {
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["value"]	= "f.`age`";
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= "f.`agerange_id`";
			}
		}
	} else {
		if(!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"])) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["value"] = "e.`rotation_title`";
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = "e.`rotation_title`";
		}
		$_GET["sb"] = "rotation";
	}
				
	$query = "	SELECT `rotation_title`, `rotation_id`
				FROM `".CLERKSHIP_DATABASE."`.`global_lu_rotations`";
	$rotations = $db->GetAll($query);
	$rotation_names = Array();
	foreach ($rotations as $rotation) {
		$rotation_names[$rotation["rotation_id"]] = $rotation["rotation_title"];
	}
	
	$clerk_name = $db->GetOne("	SELECT CONCAT_WS(' ', `firstname`, `lastname`) as `fullname` 
								FROM `".AUTH_DATABASE."`.`user_data`
								WHERE `id` = ".$db->qstr($PROXY_ID));
	
	echo "<h1>".$clerk_name."'s Logged Encounters</h1>\n";
	if (isset($rotation_name) && $rotation_name) {
		echo "<h2>For ".$rotation_name." Rotation</h2>";
	}

	$query = "	SELECT ".$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["value"]." AS `sort_by`, a.`lentry_id`, e.`rotation_id`, a.`entry_active`
				FROM `".CLERKSHIP_DATABASE."`.`logbook_entries` AS a 
				LEFT JOIN `".CLERKSHIP_DATABASE."`.`logbook_lu_locations` AS b
				ON a.`llocation_id` = b.`llocation_id`
				LEFT JOIN `".CLERKSHIP_DATABASE."`.`logbook_lu_sites` AS c
				ON a.`lsite_id` = c.`lsite_id`
				LEFT JOIN `".CLERKSHIP_DATABASE."`.`events` AS d
				ON a.`rotation_id` = d.`event_id`
				LEFT JOIN `".CLERKSHIP_DATABASE."`.`global_lu_rotations` AS e
				ON d.`rotation_id` = e.`rotation_id`
				WHERE a.`proxy_id` = ".$db->qstr($PROXY_ID)."
				ORDER BY ".$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]." ASC";
	$results = $db->GetAll($query);
	if ($results) {
		$rotation_ids = Array();
		foreach ($results as $result) {
			if (array_search($result["rotation_id"], $rotation_ids) === false) {
				$rotation_ids[] = $result["rotation_id"];
			}
		}
		
		if (!$student) {
			$query = "	SELECT a.`course_id`, b.`organisation_id` 
						FROM `".CLERKSHIP_DATABASE."`.`global_lu_rotations` AS a
						LEFT JOIN `".DATABASE_NAME."`.`courses` AS b
						ON a.`course_id` = b.`course_id`";
			$courses = $db->GetAll($query);
			$allow_view = false;
			foreach ($courses as $course) {
				if ($ENTRADA_ACL->amIAllowed(new CourseContentResource($course["course_id"], $course["organisation_id"]), 'update')) {
					$allow_view = true;
				}
			}
		}
		
		if ($student || $allow_view) {
			?>
			<script type="text/javascript" />
			function loadEntry (entry_id) {
				new Ajax.Updater({ success: 'entry' }, '<?php echo ENTRADA_RELATIVE; ?>/clerkship/logbook?section=entryapi&id='+entry_id, {
					onCreate: function () {
						$('entry').innerHTML = '<br/><br/><span class="content-small" style="align: center;">Loading... <img src="<?php echo ENTRADA_URL; ?>/images/indicator.gif" style="vertical-align: middle;" /></span>';
					}
				});
				if ($('entry-'+entry_id).hasClassName('flagged') == false) {
					$("current-entry").value = entry_id;
				} else {
					$("current-entry").value = 0;
				}
				$$('.selected-entry').each(function (e) { e.removeClassName('selected-entry'); });
				$('entry-'+entry_id).addClassName('selected-entry');
			}
			function lastPage() {
				if (Number($('current-page').value) > 1) {
					$('page-' + $('current-page').value).hide();
					$('current-page').value = Number($('current-page').value) - 1;
					$('page-' + $('current-page').value).show();
				}
			}
			function nextPage() {
				if (Number($('current-page').value) < $('max-page').value) {
					$('page-' + $('current-page').value).hide();
					$('current-page').value = Number($('current-page').value) + 1;
					$('page-' + $('current-page').value).show();
				}
			}
			</script>
			<input id="current-entry" type="hidden" value="0" />
			<div style="clear: both"></div>
			<table style="width: 100%;">
				<colgroup>
				    <col style="width: 25%" />
				    <col style="width: 75%" />
				</colgroup>
				<tbody>
					<tr>
						<td>
							<label for="view-type">View Encounters By: </label>
						</td>
						<td>
							<select name="view-type" id="view-type" onchange="window.location = '<?php echo ENTRADA_URL."/clerkship/logbook?".replace_query(array("sb" => false)); ?>&sb='+this.options[this.selectedIndex].value;">
								<option value="rotation"<?php echo (isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) && $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "e.`rotation_title`" ? " selected=\"selected\"" : "")?>>Rotation</option>
								<option value="date"<?php echo (isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) && $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "a.`encounter_date`" ? " selected=\"selected\"" : "")?>>Encounter Date</option>
								<option value="location"<?php echo (isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) && $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "b.`location`" ? " selected=\"selected\"" : "")?>>Location</option>
								<option value="site"<?php echo (isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) && $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "c.`site_name`" ? " selected=\"selected\"" : "")?>>Site</option>
								<option value="patient"<?php echo (isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) && $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "a.`patient_info`" ? " selected=\"selected\"" : "")?>>Patient</option>
								<option value="age"<?php echo (isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) && $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "f.`agerange_id`" ? " selected=\"selected\"" : "")?>>Patient Age</option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
			<br/>
			<?php
			if ($student) {
				?>
				<div style="float: right; margin-bottom: 5px">
					<div id="module-content">
						<ul class="page-action">
							<li>
								<a href="<?php echo ENTRADA_URL."/clerkship/logbook?section=add";?>" class="strong-green">Log Encounter</a>
							</li>
						</ul>
					</div>
				</div>
				<?php 
			}
			?>
			<div style="width: 100%;">
				<div style="width: 25%; float: left; height: 600px;">
					<h3 style="float: left;">Encounters</h3>
					<?php 
					if (count($results) > 20) {
						echo "<div id=\"pagination-links\" style=\"float: left; margin-left: 30px;\">\n";
						echo "<a href=\"javascript:lastPage()\">&lt;&lt;</a> ";
						echo "<a href=\"javascript:nextPage()\">&gt;&gt;</a>";
						echo "</div>";
					}
					?>
					<div style="clear: both"></div>
					<input type="hidden" value="1" id="current-page" />
					<ul class="encounter-list" id="page-1">
						<?php 
						$count = 0;
						$page_count = 1;
						foreach ($results as $result) {
							$count++;
							if ($count > 20) {
								$count = 1;
								$page_count++;
								echo "</ul>\n";
								echo "<ul class=\"encounter-list\" style=\"display: none;\" id=\"page-".$page_count."\">\n";
							}
							?>
							<li class="logbook-entry<?php echo (!$result["entry_active"] ? " flagged" : ""); ?>">
								<a id="entry-<?php echo $result["lentry_id"]; ?>" onclick="loadEntry(<?php echo $result["lentry_id"]; ?>)" class="logbook-entry">
									<?php
									if (in_array($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"], Array("b.`location`", "c.`site_name`", "a.`patient_info`", "e.`rotation_title`"))) {
										echo ($result["sort_by"] ? $result["sort_by"] : "No ".ucfirst($_GET["sb"])." Set");
									} elseif ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "a.`encounter_date`") {
										echo date(DEFAULT_DATE_FORMAT, $result["sort_by"]);
									}
									?>
								</a>
							</li>
							<?php
						}
						?>
					</ul>
					<input type="hidden" value="<?php echo $page_count; ?>" id="max-page" />
				</div>
				<div style="width: 70%; border: 1px solid #CCCCCC; float: left; height: 600px; overflow: auto; padding: 5px 10px 0px 10px;" id="entry">
					<div class="display-notice">Select an entry on the left to view the encounter details here.</div>
				</div>
			</div>
			<div style="clear: both"></div>
			<div style="width: 98%; text-align: right; margin-top: 10px;">
			<?php
			if ($allow_view || $student) {
				?>
				<input type="button" value="Download All" onclick="window.location = '<?php echo ENTRADA_URL."/clerkship/logbook?section=csv&id=".$PROXY_ID;?>'" />
				<?php 
			}
			if ($allow_view && !$student) {
				?>
				<input style="margin-left: 20px;" type="button" value="Deactivate Entry" onclick="if ($('current-entry').value != '0') {window.location = '<?php echo ENTRADA_URL."/clerkship/logbook?section=flag&entry_id="?>'+$('current-entry').value;} else {alert('You must select an active entry before selecting the Deactivate Entry button.');}" />
				<?php 
			}
			if ($student) {
				?>
				<input style="margin-left: 20px;" type="button" value="Edit Entry" onclick="if ($('current-entry').value != '0') {window.location = '<?php echo ENTRADA_URL."/clerkship/logbook?section=edit&id="?>'+$('current-entry').value;} else {alert('You must select an active entry before selecting the Edit Entry button.');}" />
				<?php 
			}
			?>
			</div>
			<?php
			$sidebar_html  = "<div style=\"margin: 2px 0px 10px 3px; font-size: 10px\">\n";
			$sidebar_html .= "	<div><img src=\"".ENTRADA_URL."/images/legend-active-member.gif\" width=\"14\" height=\"14\" alt=\"\" title=\"\" style=\"vertical-align: middle\" /> Active Entry</div>\n";
			$sidebar_html .= "	<div><img src=\"".ENTRADA_URL."/images/legend-not-accessible.gif\" width=\"14\" height=\"14\" alt=\"\" title=\"\" style=\"vertical-align: middle\" /> Deactivated Entry</div>\n";
			$sidebar_html .= "</div>\n";
			
			new_sidebar_item("Logbook Encounters", $sidebar_html, "objective-legend", "open");
			
		} else {
			$ERROR++;
			$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";
			echo display_error();
		}
	} else {
		$ERROR++;
		if (array_key_exists($rotation_id, $rotation_names) && $rotation_names[$rotation_id]) {
			$ERRORSTR[]	= "No clerkship logbook entries for this rotation [".$rotation_names[$rotation_id]."] have been found for this user in the system. You may view all entries for all rotations by clicking <a href=\"".ENTRADA_URL."/clerkship/logbook?".replace_query(array("rotation" => false))."\" />here</a>.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";
		} else {
			$ERRORSTR[]	= "No clerkship logbook entries have been found for this user in the system.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";
		}
		echo display_error();
	}
	
}