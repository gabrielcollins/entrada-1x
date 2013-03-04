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
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 */

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_GRADEBOOK"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->amIAllowed("gradebook", "update", false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	/**
	 * Update requested column to sort by.
	 * Valid: director, name
	 */
	if (isset($_GET["sb"])) {
		if (@in_array(trim($_GET["sb"]), array("type", "code", "name"))) {
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

	/**
	 * Check if preferences need to be updated on the server at this point.
	 */
	preferences_update($MODULE, $PREFERENCES);

	/**
	 * Provide the queries with the columns to order by.
	 */
	switch($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) {
		case "type" :
			$sort_by = "c.`curriculum_type_name` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
		break;
		case "code" :
			$sort_by = "a.`course_code` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
		break;
		case "name" :
		default :
			$sort_by = "a.`course_name` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
		break;
	}

	/**
	 * Get the total number of results using the generated queries above and calculate the total number
	 * of pages that are available based on the results per page preferences.
	 */
	if ($ENTRADA_ACL->amIAllowed("course", "update", false)) {
		$query	= "	SELECT COUNT(*) AS `total_rows`
					FROM `courses` AS a
					LEFT JOIN `curriculum_lu_types` AS b
					on b.`curriculum_type_id` = a.`curriculum_type_id`
					WHERE a.`course_active` = '1'
					AND a.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation());
	} else {
		$query	= "	SELECT COUNT(*) AS `total_rows`
					FROM `courses` AS a
					LEFT JOIN `course_contacts` AS b
					ON b.`course_id` = a.`course_id`
					AND b.`proxy_id` = ".$db->qstr($ENTRADA_USER->getActiveId())."
					AND b.`contact_type` = 'director'
					LEFT JOIN `community_courses` AS c
					ON c.`course_id` = a.`course_id`
					LEFT JOIN `community_members` AS d
					ON d.`community_id` = c.`community_id`
					AND d.`proxy_id` = ".$db->qstr($ENTRADA_USER->getActiveId())."
					LEFT JOIN `curriculum_lu_types` AS e
					on e.`curriculum_type_id` = a.`curriculum_type_id`
					WHERE (
						a.`pcoord_id` = ".$db->qstr($ENTRADA_USER->getActiveId())."
						OR b.`proxy_id` = ".$db->qstr($ENTRADA_USER->getActiveId())."
						OR d.`member_acl` = '1'
					)
					AND a.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
					AND a.`course_active` = '1'";
	}
	$result = $db->GetRow($query);
	if ($result) {
		$total_rows	= $result["total_rows"];

		if ($total_rows <= $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) {
			$total_pages = 1;
		} elseif (($total_rows % $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == 0) {
			$total_pages = (int) ($total_rows / $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
		} else {
			$total_pages = (int) ($total_rows / $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) + 1;
		}
	} else {
		$total_rows = 0;
		$total_pages = 1;
	}

	/**
	 * Check if pv variable is set and see if it's a valid page, other wise page 1 it is.
	 */
	if (isset($_GET["pv"])) {
		$page_current = (int) trim($_GET["pv"]);

		if (($page_current < 1) || ($page_current > $total_pages)) {
			$page_current = 1;
		}
	} else {
		$page_current = 1;
	}

	$page_previous = (($page_current > 1) ? ($page_current - 1) : false);
	$page_next = (($page_current < $total_pages) ? ($page_current + 1) : false);

	echo "<h1>Gradebooks</h1>";
	?>
	<table style="clear: both; width: 100%; margin-bottom: 10px" cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td style="width: 100%; text-align: right">
				<?php
				echo "<div style=\"white-space: nowrap\">\n";
				if ($total_pages > 1) {
					echo "<form action=\"" . ENTRADA_URL . "/admin/" . $MODULE . "\" method=\"get\" id=\"pageSelector\" style=\"display:inline;\">\n";
					echo "<span style=\"width: 20px; vertical-align: middle; margin-right: 3px; text-align: left\">\n";
					if ($page_previous) {
						echo "<a href=\"" . ENTRADA_URL . "/admin/" . $MODULE . "?" . replace_query(array("pv" => $page_previous)) . "\"><img src=\"" . ENTRADA_URL . "/images/record-previous-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Back to page " . $page_previous . ".\" title=\"Back to page " . $page_previous . ".\" style=\"vertical-align: middle\" /></a>\n";
					} else {
						echo "<img src=\"" . ENTRADA_URL . "/images/record-previous-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
					}
					echo "</span>";
					echo "<span style=\"vertical-align: middle\">\n";
					echo "<select name=\"pv\" onchange=\"$('pageSelector').submit();\"" . (($total_pages <= 1) ? " disabled=\"disabled\"" : "") . ">\n";
					for ($i = 1; $i <= $total_pages; $i++) {
						echo "<option value=\"" . $i . "\"" . (($i == $page_current) ? " selected=\"selected\"" : "") . ">" . (($i == $page_current) ? " Viewing" : "Jump To") . " Page " . $i . "</option>\n";
					}
					echo "</select>\n";
					echo "</span>\n";
					echo "<span style=\"width: 20px; vertical-align: middle; margin-left: 3px; text-align: right\">\n";
					if ($page_current < $total_pages) {
						echo "<a href=\"" . ENTRADA_URL . "/admin/" . $MODULE . "?" . replace_query(array("pv" => $page_next)) . "\"><img src=\"" . ENTRADA_URL . "/images/record-next-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Forward to page " . $page_next . ".\" title=\"Forward to page " . $page_next . ".\" style=\"vertical-align: middle\" /></a>";
					} else {
						echo "<img src=\"" . ENTRADA_URL . "/images/record-next-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
					}
					echo "</span>\n";
				}
				echo "</form>\n";
				echo "</div>\n";
				?>
			</td>
		</tr>
	</table>
	<?php
	/**
	 * Provides the first parameter of MySQLs LIMIT statement by calculating which row to start results from.
	 */
	$limit_parameter = (int) (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] * $page_current) - $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);

	if ($ENTRADA_ACL->amIAllowed("course", "update", false)) {
		$query	= "	SELECT a.`course_id`, a.`organisation_id`, a.`course_name`, a.`course_code`, a.`course_url`, a.`notifications`, c.`curriculum_type_name`, CONCAT_WS(', ', d.`lastname`, d.`firstname`) AS `fullname`
					FROM `courses` AS a
					LEFT JOIN `course_contacts` AS b
					ON b.`course_id` = a.`course_id`
					AND b.`contact_type` = 'director'
					AND b.`contact_order` = 0
					LEFT JOIN `curriculum_lu_types` AS c
					ON c.`curriculum_type_id` = a.`curriculum_type_id`
					LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS d
					ON d.`id` = b.`proxy_id`
					WHERE a.`course_active` = '1'
					AND a.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
					ORDER BY %s LIMIT %s, %s";
	} else {
		$query	= "	SELECT DISTINCT(a.`course_id`), a.`organisation_id`, a.`course_name`, a.`course_code`, a.`course_url`, a.`notifications`, c.`curriculum_type_name`, CONCAT_WS(', ', d.`lastname`, d.`firstname`) AS `fullname`
					FROM `courses` AS a
					LEFT JOIN `course_contacts` AS b
					ON b.`course_id` = a.`course_id`
					AND b.`contact_type` = 'director'
					AND b.`contact_order` = 0
					LEFT JOIN `curriculum_lu_types` AS c
					ON c.`curriculum_type_id` = a.`curriculum_type_id`
					LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS d
					ON d.`id` = b.`proxy_id`
					LEFT JOIN `community_courses` AS e
					ON e.`course_id` = a.`course_id`
					LEFT JOIN `community_members` AS f
					ON f.`community_id` = e.`community_id`
					AND f.`proxy_id` = ".$db->qstr($ENTRADA_USER->getActiveId())."
					WHERE
					(
						a.`pcoord_id` = ".$db->qstr($ENTRADA_USER->getActiveId())."
						OR b.`proxy_id` = ".$db->qstr($ENTRADA_USER->getActiveId())."
						OR f.`member_acl` = '1'
					)
					AND a.`course_active` = '1'
					AND a.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
					ORDER BY %s LIMIT %s, %s";
	}

	$query		= sprintf($query, $sort_by, $limit_parameter, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
	$results	= $db->GetAll($query);
	if ($results) {
	?>
		<table class="tableList" cellspacing="0" summary="List of Gradebooks">
		<colgroup>
			<col class="general" />
			<col class="general" />
			<col class="title" />
		</colgroup>
		<thead>
			<tr>
				<td class="general borderl<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "type") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("type", "Category"); ?></td>
				<td class="general<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "code") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("code", "Code"); ?></td>
				<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "name") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("name", "Name"); ?></td>
			</tr>
		</thead>
		<tbody>
		<?php
		if ((@count($results) == 1) && !($ENTRADA_ACL->amIAllowed(new CourseResource($results[0]["course_id"], $results[0]["organisation_id"]), "update"))) {
			header("Location: ".ENTRADA_URL."/admin/".$MODULE."?section=view&id=".$results[0]["course_id"]);
			exit;
		}

		foreach ($results as $result) {
			$url			= "";
			$administrator	= false;

			if ($ENTRADA_ACL->amIAllowed(new GradebookResource($result["course_id"], $result["organisation_id"]), "update")) {
				$allowed_ids	= array($ENTRADA_USER->getActiveId());
				$administrator	= true;
				$url			= ENTRADA_URL."/admin/gradebook/?section=view&amp;id=".$result["course_id"];
			}

			echo "<tr id=\"course-".$result["course_id"]."\" class=\"course".((!$url) ? " np" : "")."\">\n";
			echo "	<td class=\"general".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Gradebook for: ".html_encode($result["course_name"])."\">" : "").html_encode($result["curriculum_type_name"]).(($url) ? "</a>" : "")."</td>\n";
			echo "	<td class=\"code".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Gradebook for: ".html_encode($result["course_name"])."\">" : "").html_encode($result["course_code"]).(($url) ? "</a>" : "")."</td>\n";
			echo "	<td class=\"title".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Gradebook for: ".html_encode($result["course_name"])."\">" : "").html_encode($result["course_name"]).(($url) ? "</a>" : "")."</td>\n";
			echo "</tr>\n";
		}
		?>
		</tbody>
		</table>
		<?php
	} else {
		?>
		<div class="display-notice">
			<h3>No Available Courses</h3>
		</div>
		<?php
	}

	/**
	 * Sidebar item that will provide another method for sorting, ordering, etc.
	 */
	$sidebar_html  = "Sort columns:\n";
	$sidebar_html .= "<ul class=\"menu\">\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) == "type") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("sb" => "type"))."\" title=\"Sort by Category\">by category</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) == "code") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("sb" => "code"))."\" title=\"Sort by Code\">by code</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) == "name") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("sb" => "name"))."\" title=\"Sort by Name\">by name</a></li>\n";
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
?>