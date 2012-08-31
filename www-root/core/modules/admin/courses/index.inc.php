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
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_COURSES"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->amIAllowed('coursecontent', 'update', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	/**
	 * Update requested column to sort by.
	 * Valid: director, name
	 */
	if (isset($_GET["sb"])) {
		if (@in_array(trim($_GET["sb"]), array("type", "name", "director", "notices"))) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= trim($_GET["sb"]);
		}

		$_SERVER["QUERY_STRING"]	= replace_query(array("sb" => false));
	} else {
		if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"])) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = "name";
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
	 * Update requsted number of rows per page.
	 * Valid: any integer really.
	 */
	if ((isset($_GET["pp"])) && ((int) trim($_GET["pp"]))) {
		$integer = (int) trim($_GET["pp"]);

		if (($integer > 0) && ($integer <= 250)) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] = $integer;
		}

		$_SERVER["QUERY_STRING"] = replace_query(array("pp" => false));
	} else {
		if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"])) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] = DEFAULT_ROWS_PER_PAGE;
		}
	}

	/**
	 * Update requsted organisation filter
	 * Valid: any integer really.
	 */
	/*
	if(isset($_GET['organisation_id'])) {
		if($_GET['organisation_id'] == 'all') {
			$organisation_id = null;
		} else if((int) trim($_GET["organisation_id"])) {
				$organisation_id = (int) trim($_GET["organisation_id"]);
				$organisation_where = '`organisation_id` = '.$organisation_id;
			}

		$_SERVER["QUERY_STRING"] = replace_query(array("organisation_id" => false));
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"] = $organisation_id;
	} else {
		if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"])) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"] = $_SESSION['details']['organisation_id'];
			$organisation_id = $_SESSION['details']['organisation_id'];
		} else {
			$organisation_id = $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"];
		}
		$organisation_where = '`organisation_id` = '.$organisation_id;
	}*/

	/**
	 * Check if preferences need to be updated on the server at this point.
	 */
	preferences_update($MODULE, $PREFERENCES);

	/**
	 * Provide the queries with the columns to order by.
	 */
	switch($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) {
		case "director" :
			$SORT_BY	= "`fullname` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]).", `courses`.`course_name` ASC";
			break;
		case "notices" :
			$SORT_BY	= "`courses`.`notifications` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
			break;
		case "type" :
			$SORT_BY	= "`curriculum_lu_types`.`curriculum_type_name` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
		break;
		case "name" :
		default :
			$SORT_BY	= "`courses`.`course_name` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
		break;
	}

	$organisation_where ="`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation());
	
	/**
	 * Get the total number of results using the generated queries above and calculate the total number
	 * of pages that are available based on the results per page preferences.
	 */
	if ($ENTRADA_ACL->amIAllowed('course', 'update', false)) {
		$query	= "	SELECT COUNT(*) AS `total_rows` FROM `courses` WHERE `courses`.`course_active` = '1'".(isset($organisation_where) ? ' AND `courses`.'.$organisation_where : '');
	} else {
		$query	= "	SELECT COUNT(*) AS `total_rows`
					FROM `courses` AS a
					LEFT JOIN `course_contacts` AS b
					ON b.`course_id` = a.`course_id`
					AND b.`proxy_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])."
					AND b.`contact_type` = 'director'
					LEFT JOIN `community_courses` AS c
					ON c.`course_id` = a.`course_id`
					LEFT JOIN `community_members` AS d
					ON d.`community_id` = c.`community_id`
					AND d.`proxy_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])."
					WHERE 
					(
						a.`pcoord_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])."
						OR b.`proxy_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])."
						OR d.`member_acl` = '1'
					)
					".(isset($organisation_where) ? ' AND `a`.'.$organisation_where : '')."
					AND a.`course_active` = '1'";
	}
	$result = $db->GetRow($query);
	if ($result) {
		$TOTAL_ROWS	= $result["total_rows"];

		if ($TOTAL_ROWS <= $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) {
			$TOTAL_PAGES = 1;
		} elseif (($TOTAL_ROWS % $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == 0) {
			$TOTAL_PAGES = (int) ($TOTAL_ROWS / $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
		} else {
			$TOTAL_PAGES = (int) ($TOTAL_ROWS / $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) + 1;
		}
	} else {
		$TOTAL_ROWS		= 0;
		$TOTAL_PAGES	= 1;
	}

	/**
	 * Check if pv variable is set and see if it's a valid page, other wise page 1 it is.
	 */
	if (isset($_GET["pv"])) {
		$PAGE_CURRENT = (int) trim($_GET["pv"]);

		if (($PAGE_CURRENT < 1) || ($PAGE_CURRENT > $TOTAL_PAGES)) {
			$PAGE_CURRENT = 1;
		}
	} else {
		$PAGE_CURRENT = 1;
	}

	$PAGE_PREVIOUS	= (($PAGE_CURRENT > 1) ? ($PAGE_CURRENT - 1) : false);
	$PAGE_NEXT	= (($PAGE_CURRENT < $TOTAL_PAGES) ? ($PAGE_CURRENT + 1) : false);

	echo "<h1>Manage " . $module_title . "</h1>\n";

	if ($ENTRADA_ACL->amIAllowed('course', 'create', false)) {
		?>
		<div style="float: right">
			<ul class="page-action">
				<li><a href="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?section=add" class="strong-green">Add New <?php echo $module_singular_name; ?></a></li>
			</ul>
		</div>
		<div style="clear: both"></div>
		<?php
	}
	?>
	<table style="clear: both; width: 100%; margin-bottom: 10px" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td style="width: 100%; text-align: right">
			<div style="white-space: nowrap">
					<?php
					if ($TOTAL_PAGES > 1) {
						echo "<form action=\"".ENTRADA_URL."/admin/".$MODULE."\" method=\"get\" id=\"pageSelector\" style=\"display:inline;\">\n";
						echo "<span style=\"width: 20px; vertical-align: middle; margin-right: 3px; text-align: left\">\n";
						if ($PAGE_PREVIOUS) {
							echo "<a href=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("pv" => $PAGE_PREVIOUS))."\"><img src=\"".ENTRADA_URL."/images/record-previous-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Back to page ".$PAGE_PREVIOUS.".\" title=\"Back to page ".$PAGE_PREVIOUS.".\" style=\"vertical-align: middle\" /></a>\n";
						} else {
							echo "<img src=\"".ENTRADA_URL."/images/record-previous-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
						}
						echo "</span>";
						echo "<span style=\"vertical-align: middle\">\n";
						echo "<select name=\"pv\" onchange=\"$('pageSelector').submit();\"".(($TOTAL_PAGES <= 1) ? " disabled=\"disabled\"" : "").">\n";
						for($i = 1; $i <= $TOTAL_PAGES; $i++) {
							echo "<option value=\"".$i."\"".(($i == $PAGE_CURRENT) ? " selected=\"selected\"" : "").">".(($i == $PAGE_CURRENT) ? " Viewing" : "Jump To")." Page ".$i."</option>\n";
						}
						echo "</select>\n";
						echo "</span>\n";
						echo "<span style=\"width: 20px; vertical-align: middle; margin-left: 3px; text-align: right\">\n";
						if ($PAGE_CURRENT < $TOTAL_PAGES) {
							echo "<a href=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("pv" => $PAGE_NEXT))."\"><img src=\"".ENTRADA_URL."/images/record-next-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Forward to page ".$PAGE_NEXT.".\" title=\"Forward to page ".$PAGE_NEXT.".\" style=\"vertical-align: middle\" /></a>";
						} else {
							echo "<img src=\"".ENTRADA_URL."/images/record-next-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
						}
						echo "</span>\n";
						echo "</form>\n";
					}
					echo "</div>\n";
					?>
		</td>
	</tr>
</table>
	<?php
	/**
	 * Provides the first parameter of MySQLs LIMIT statement by calculating which row to start results from.
	 */
	$limit_parameter = (int) (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] * $PAGE_CURRENT) - $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
	
	if ($ENTRADA_ACL->amIAllowed("course", "update", false)) {
		$query	= "	SELECT `courses`.`course_id`,  `courses`.`organisation_id`, `courses`.`course_name`, `courses`.`course_code`, `courses`.`course_url`, `courses`.`notifications`, `curriculum_lu_types`.`curriculum_type_name`, CONCAT_WS(', ', `".AUTH_DATABASE."`.`user_data`.`lastname`, `".AUTH_DATABASE."`.`user_data`.`firstname`) AS `fullname`
					FROM `courses`
					LEFT JOIN `curriculum_lu_types`
					ON `curriculum_lu_types`.`curriculum_type_id` = `courses`.`curriculum_type_id`
					LEFT JOIN `course_contacts`
					ON `course_contacts`.`course_id` = `courses`.`course_id`
					AND `course_contacts`.`contact_type` = 'director'
					LEFT JOIN `".AUTH_DATABASE."`.`user_data`
					ON `".AUTH_DATABASE."`.`user_data`.`id` = `course_contacts`.`proxy_id`
					WHERE `courses`.`course_active` = '1'
					".(isset($organisation_where) ? " AND `courses`.".$organisation_where : "")."
					GROUP BY `courses`.`course_id`
					ORDER BY %s LIMIT %s, %s";
	} else {
		$query	= "	SELECT `courses`.`course_id`, `courses`.`organisation_id`, `courses`.`course_name`, `courses`.`course_code`, `courses`.`course_url`, `courses`.`notifications`, `curriculum_lu_types`.`curriculum_type_name`, CONCAT_WS(', ', `".AUTH_DATABASE."`.`user_data`.`lastname`, `".AUTH_DATABASE."`.`user_data`.`firstname`) AS `fullname`
					FROM `courses`
					LEFT JOIN `course_contacts`
					ON `course_contacts`.`course_id` = `courses`.`course_id`
					AND `course_contacts`.`contact_type` = 'director'
					LEFT JOIN `curriculum_lu_types`
					ON `curriculum_lu_types`.`curriculum_type_id` = `courses`.`curriculum_type_id`
					LEFT JOIN `".AUTH_DATABASE."`.`user_data`
					ON `".AUTH_DATABASE."`.`user_data`.`id` = `course_contacts`.`proxy_id`
					LEFT JOIN `community_courses`
					ON `community_courses`.`course_id` = `courses`.`course_id`
					LEFT JOIN `community_members`
					ON `community_members`.`community_id` = `community_courses`.`community_id`
					AND `community_members`.`proxy_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])."
					WHERE 
					(
						`courses`.`pcoord_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])."
						OR `course_contacts`.`proxy_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])."
						OR `community_members`.`member_acl` = '1'
					)
					AND `courses`.`course_active` = '1'
					".(isset($organisation_where) ? ' AND `courses`.'.$organisation_where : '')."
					GROUP BY `courses`.`course_id`
					ORDER BY %s LIMIT %s, %s";
	}

	$query		= sprintf($query, $SORT_BY, $limit_parameter, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
	$results	= $db->GetAll($query);
	if ($results) {
		if ($ENTRADA_ACL->amIAllowed('course', 'delete', false)) : ?>
		<form action="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?section=delete" method="post">
		<?php endif; ?>
		<table class="tableList" cellspacing="0" summary="List of Courses">
		<colgroup>
			<col class="modified" />
			<col class="general" />
			<col class="title" />
			<col class="teacher" />
			<col class="notices" />
			<col class="grades" />
		</colgroup>
		<thead>
			<tr>
				<td class="modified">&nbsp;</td>
				<td class="general<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "type") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("type", "Category"); ?></td>
				<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "name") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("name", $module_singular_name . " Name"); ?></td>
				<td class="teacher<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "director") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("director", $module_singular_name . " Director"); ?></td>
				<td class="notices<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "notices") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("notices", "Notices"); ?></td>
				<td class="grades">&nbsp;</td>
			</tr>
		</thead>
		<?php if ($ENTRADA_ACL->amIAllowed('course', 'delete', false)) : ?>
		<tfoot>
			<tr>
				<td></td>
				<td colspan="5" style="padding-top: 10px">
					<input type="submit" class="button" value="Delete Selected" />
				</td>
			</tr>
		</tfoot>
		<?php endif; ?>
		<tbody>
		<?php
		if ((@count($results) == 1) && !($ENTRADA_ACL->amIAllowed(new CourseResource($results[0]["course_id"], $results[0]["organisation_id"]), 'update'))) {
			header("Location: ".ENTRADA_URL."/admin/".$MODULE."?section=content&id=".$results[0]["course_id"]);
			exit;
		}

		foreach ($results as $result) {
			$url			= "";
			$administrator	= false;

			if ($ENTRADA_ACL->amIAllowed(new CourseResource($result["course_id"], $result["organisation_id"]), 'update')) {
				$allowed_ids	= array($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]);
				$administrator	= true;
				$url			= ENTRADA_URL."/admin/".$MODULE."?section=edit&amp;id=".$result["course_id"];
			} else if ($ENTRADA_ACL->amIAllowed(new CourseContentResource($result["course_id"], $result["organisation_id"]), 'update')) {
				$url = ENTRADA_URL."/admin/".$MODULE."?section=content&amp;id=".$result["course_id"];
			}

			echo "<tr id=\"course-".$result["course_id"]."\" class=\"course".((!$url) ? " np" : "")."\">\n";
			echo "	<td class=\"modified\">".(($administrator) ? "<input type=\"checkbox\" name=\"delete[]\" value=\"".$result["course_id"]."\" />" : "<img src=\"".ENTRADA_URL."/images/pixel.gif\" width=\"19\" height=\"19\" alt=\"\" title=\"\" />")."</td>\n";
			echo "	<td class=\"general".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Course Name: ".html_encode($result["course_name"])."\">" : "").html_encode($result["curriculum_type_name"]).(($url) ? "</a>" : "")."</td>\n";
			echo "	<td class=\"title".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."&amp;org_id=".$result["organisation_id"]."\" title=\"Course Name: ".html_encode($result["course_name"])."\">" : "").html_encode($result["course_name"].(($result["course_code"]) ? ": ".$result["course_code"] : "")).(($url) ? "</a>" : "")."</td>\n";
			echo "	<td class=\"teacher".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Course Director: ".html_encode($result["fullname"])."\">" : "").html_encode($result["fullname"]).(($url) ? "</a>" : "")."</td>\n";
			echo "	<td class=\"notices\">".(((int) $result["notifications"]) ? "Yes" : "<strong>No</strong>")."</td>\n";
			echo "	<td class=\"grades\">".(($url) ? "<a href=\"".ENTRADA_URL."/admin/".$MODULE."?section=content&amp;id=".$result["course_id"]."\"><img src=\"".ENTRADA_URL."/images/event-contents.gif\" width=\"16\" height=\"16\" alt=\"Manage " . $module_singular_name . " Content\" title=\"Manage ". $module_singular_name . " Content\" border=\"0\" /></a>" : "<img src=\"".ENTRADA_URL."/images/pixel.gif\" width=\"16\" height=\"16\" alt=\"\" title=\"\" />");
			if ($ENTRADA_ACL->amIAllowed(new GradebookResource($result["course_id"], $result["organisation_id"]), "read")) {
				echo "&nbsp;<a href=\"".ENTRADA_URL."/admin/gradebook?section=view&amp;id=".$result["course_id"]."\"><img src=\"".ENTRADA_URL."/images/book_go.png\" width=\"16\" height=\"16\" alt=\"View Gradebook\" title=\"View Gradebook\" border=\"0\" /></a>";				
			}
			echo "	</td>\n";
			echo "</tr>\n";
		}
		?>
		</tbody>
		</table>
		<?php if ($ENTRADA_ACL->amIAllowed('course', 'delete', false)) : ?>
		</form>
		<?php
		endif;
	} else {
		?>
		<div class="display-notice">
			<h3>No Available <?php echo $module_title; ?></h3>

			<?php if ($ENTRADA_ACL->amIAllowed('course', 'create', false)) : ?>
			There are currently no <?php echo strtolower($module_title); ?> available in the system.
			<br /><br />
			You should start by adding a new <?php echo strtolower($module_singular_name); ?> by clicking the <strong>Add <?php echo $module_title; ?></strong> link above.
			<?php else : ?>
			It appears that there are no <?php echo strtolower($module_title); ?> that you are able access in the system.
			<br /><br />
			If you believe you are receiving this message in error, please contact an administrator.
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Sidebar item that will provide another method for sorting, ordering, etc.
	 */
	$sidebar_html  = "Sort columns:\n";
	$sidebar_html .= "<ul class=\"menu\">\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) == "name") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("sb" => "name"))."\" title=\"Sort by ". $module_singular_name . " Name\">by course name</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) == "director") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("sb" => "director"))."\" title=\"Sort by ". $module_singular_name . " Director\">by course director</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) == "notices") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("sb" => "notices"))."\" title=\"Sort by Notices\">by notices</a></li>\n";
	$sidebar_html .= "</ul>\n";
	$sidebar_html .= "Order columns:\n";
	$sidebar_html .= "<ul class=\"menu\">\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) == "asc") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("so" => "asc"))."\" title=\"Ascending Order\">in ascending order</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) == "desc") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("so" => "desc"))."\" title=\"Descending Order\">in descending order</a></li>\n";
	$sidebar_html .= "</ul>\n";
	$sidebar_html .= "Rows per page:\n";
	$sidebar_html .= "<ul class=\"menu\">\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "5") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("pp" => "5"))."\" title=\"Display 5 Rows Per Page\">5 rows per page</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "15") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("pp" => "15"))."\" title=\"Display 15 Rows Per Page\">15 rows per page</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "25") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("pp" => "25"))."\" title=\"Display 25 Rows Per Page\">25 rows per page</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "50") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("pp" => "50"))."\" title=\"Display 50 Rows Per Page\">50 rows per page</a></li>\n";
	$sidebar_html .= "</ul>\n";

	new_sidebar_item("Sort Results", $sidebar_html, "sort-results", "open");
}
