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
 * The default file that is loaded when /admin/communities is accessed.
 *
 * @author Organisation: Univeristy of Calgary
 * @author Unit: Faculty of Medicine
 * @author Developer: Howard Lu <yhlu@ucalgary.ca>
 * @copyright Copyright 2010 University of Calgary. All Rights Reserved.
 *
*/

if (!defined("IN_COMMUNITIES")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("communityadmin", "read", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$GROUP."] and role [".$ROLE."] does not have access to this module [".$MODULE."]");
} else {
	/**
	 * Update requested column to sort by.
	 * Valid: director, name
	 */

	$search_type		= "browse-newest";
	$browse_number		= 25;
	$results_per_page	= 25;
	$search_query		= "";
	$search_query_text	= "";
	$query_counter		= "";
	$query_search		= "";
	$show_results		= false;

	$admin_wording = "Administrator View";
	$admin_url = ENTRADA_URL."/admin/communities";

	$sidebar_html  = "<ul class=\"menu\">\n";
	$sidebar_html .= "	<li class=\"off\"><a href=\"".ENTRADA_URL."/communities"."\">Student View</a></li>\n";
	if (($admin_wording) && ($admin_url)) {
		$sidebar_html .= "<li class=\"on\"><a href=\"".$admin_url."\">".html_encode($admin_wording)."</a></li>\n";
	}
	$sidebar_html .= "</ul>\n";

	new_sidebar_item("Display Style", $sidebar_html, "display-style", "open");
	/**
	 * Determine the type of search that is requested.
	 */
	if ((isset($_GET["type"])) && (in_array(trim($_GET["type"]), array("search", "browse-group", "browse-dept")))) {
		$search_type = clean_input($_GET["type"], "trim");
	}

	if (isset($_GET["sb"])) {
		if (@in_array(trim($_GET["sb"]), array("community_title", "community_opened", "category_title"))) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= trim($_GET["sb"]);
		}

		$_SERVER["QUERY_STRING"] = replace_query(array("sb" => false));
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

	?>
	<h1>Manage Communities</h1>
	<?php
	/**
	 * Update requested order to sort by.
	 * Valid: asc, desc
	 */
	if(isset($_GET["so"])) {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"] = ((strtolower($_GET["so"]) == "desc") ? "DESC" : "ASC");
	} else {
		if(!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"])) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"] = "ASC";
		}
	}

	$scheduler_communities = array(
				"duration_start" => 0,
				"duration_end" => 0,
				"total_rows" => 0,
				"total_pages" => 0,
				"page_current" => 0,
				"page_previous" => 0,
				"page_next" => 0,
				"communities" => array()
			);

	/**
	 * Provide the queries with the columns to order by.
	 */
	switch ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) {
		case "community_title" :
			$sort_by = "a.`community_title` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["communities"]["so"]).", a.`community_title` ASC";
		break;
		case "community_opened" :
			$sort_by = "a.`community_opened` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["communities"]["so"]).", a.`community_opened` ASC";
		break;
		case "category_title" :
		default :
			$sort_by = "b.`category_title` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["communities"]["so"]).", b.`category_title` ASC";
		break;
	}
	
	/**** Query ***/
	$query_count = "SELECT COUNT(`community_id`) AS `total_rows`
				FROM `communities` AS a
				LEFT JOIN `communities_categories` AS b
				ON a.`category_id` = b.`category_id`
				WHERE `community_active` = '1'";

	$query_communities = "	SELECT a.`community_id`, a.`community_opened`, a.`community_title`, a.`community_shortname`, b.`category_title`
				FROM `communities` AS a
				LEFT JOIN `communities_categories` AS b
				ON a.`category_id` = b.`category_id`
				WHERE `community_active` = '1'";

	switch ($search_type) {
		case "browse-newest" :
			if ((isset($_GET["n"])) && ($number = clean_input($_GET["n"], array("trim", "int"))) && ($number > 0) && ($number <= 100)) {
				$browse_number = $number;
			}

			if (!$ERROR) {
				$search_query_text = "Newest ".(int) $browse_number." User".(($browse_number != 1) ? "s" : "");

				$query_counter	= "SELECT ".(int) $browse_number." AS `total_rows`";
				$query_search	= "	SELECT a.*, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`account_active`, b.`access_starts`, b.`access_expires`, b.`last_login`, b.`role`, b.`group`
									FROM `".AUTH_DATABASE."`.`user_data` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
									ON b.`user_id` = a.`id`
									AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
									WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
									ORDER BY `id` DESC
									LIMIT 0, ".(int) $browse_number;
			}
		break;
		case "search" :
		default :
			if ((isset($_GET["q"])) && ($query = clean_input($_GET["q"], array("trim", "notags")))) {
				$search_query = $query;
				$search_query_text = html_encode($query);
			}

			$sql_ext = " and (a.`community_title` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
					OR b.`category_title` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%").")";
			$query_count = $query_count.$sql_ext;
			$query_communities = $query_communities.$sql_ext;
		break;
	}


	$query_communities = $query_communities."ORDER BY %s LIMIT %s, %s";
	//Zend_Debug::dump($query_communities);


	/**
	 * Get the total number of results using the generated queries above and calculate the total number
	 * of pages that are available based on the results per page preferences.
	 */
	$result_count = $db->GetRow($query_count);

	if ($result_count) {
		$scheduler_communities["total_rows"] = (int) $result_count["total_rows"];

		if ($scheduler_communities["total_rows"] <= $_SESSION[APPLICATION_IDENTIFIER]["communities"]["pp"]) {
			$scheduler_communities["total_pages"] = 1;
		} elseif (($scheduler_communities["total_rows"] % $_SESSION[APPLICATION_IDENTIFIER]["communities"]["pp"]) == 0) {
			$scheduler_communities["total_pages"] = (int) ($scheduler_communities["total_rows"] / $_SESSION[APPLICATION_IDENTIFIER]["communities"]["pp"]);
		} else {
			$scheduler_communities["total_pages"] = (int) ($scheduler_communities["total_rows"] / $_SESSION[APPLICATION_IDENTIFIER]["communities"]["pp"]) + 1;
		}
	} else {
		$scheduler_communities["total_rows"] = 0;
		$scheduler_communities["total_pages"] = 1;
	}
	
	/**
	 * Check if pv variable is set and see if it's a valid page, other wise page 1 it is.
	 */
	if (isset($_GET["pv"])) {
		$scheduler_communities["page_current"] = (int) trim($_GET["pv"]);

		if (($scheduler_communities["page_current"] < 1) || ($scheduler_communities["page_current"] > $scheduler_communities["total_pages"])) {
			$scheduler_communities["page_current"] = 1;
		}
	} else {
		$scheduler_communities["page_current"] = 1;
	}

	$scheduler_communities["page_previous"] = (($scheduler_communities["page_current"] > 1) ? ($scheduler_communities["page_current"] - 1) : false);
	$scheduler_communities["page_next"] = (($scheduler_communities["page_current"] < $scheduler_communities["total_pages"]) ? ($scheduler_communities["page_current"] + 1) : false);

	/**
	 * Provides the first parameter of MySQLs LIMIT statement by calculating which row to start results from.
	 */
	$limit_parameter = (int) (($_SESSION[APPLICATION_IDENTIFIER]["communities"]["pp"] * $scheduler_communities["page_current"]) - $_SESSION[APPLICATION_IDENTIFIER]["communities"]["pp"]);

	/**
	 * Provide the previous query so we can have previous / next event links on the details page.
	 */
	$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["communities"]["previous_query"]["query"] = $query_communities;
	$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["communities"]["previous_query"]["total_rows"] = $scheduler_communities["total_rows"];

	$query_communities = sprintf($query_communities, $sort_by, $limit_parameter, $_SESSION[APPLICATION_IDENTIFIER]["communities"]["pp"]);
	$scheduler_communities["communities"] = $db->GetAll($query_communities);
	?>

	<h2>Community Search</h2>
	<form action="<?php echo ENTRADA_URL; ?>/admin/communities" method="get">
	<input type="hidden" name="type" value="search" />
	<table style="width: 100%" cellspacing="1" cellpadding="1" border="0" summary="Search For Community">
	<colgroup>
		<col style="width: 17%" />
		<col style="width: 48%" />
		<col style="width: 35%" />
	</colgroup>
	<tbody>
		<tr>
			<td style="vertical-align: top"><label for="q" class="form-required">Community Search</label></td>
			<td style="vertical-align: top">
				<input type="text" id="q" name="q" value="<?php echo html_encode($search_query); ?>" style="width: 325px" />
				<div class="content-small" style="margin-top: 10px">
					<strong>Note:</strong> You can search for community title, or Category title.
				</div>
			</td>
			<td style="vertical-align: top">
				<input type="submit" class="button" value="Search" />
				<?php
				if ($search_query != "") {
					?>
					<input type="button" class="button" value="Show All"  onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/communities'"/>
					<?php
				}
				?>
			</td>
		</tr>
	</tbody>
	</table>
	</form>
	<br /><br />
	<?php
	if ($scheduler_communities["total_pages"] > 1) {
		echo "<div class=\"fright\" style=\"margin-bottom: 10px\">\n";
		echo "<form action=\"".ENTRADA_URL."/admin/communities\" method=\"get\" id=\"pageSelector\">\n";
		echo "<span style=\"width: 20px; vertical-align: middle; margin-right: 3px; text-align: left\">\n";
		if ($scheduler_communities["page_previous"]) {
			echo "<a href=\"".ENTRADA_URL."/admin/communities?".replace_query(array("pv" => $scheduler_communities["page_previous"]))."\"><img src=\"".ENTRADA_URL."/images/record-previous-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Back to page ".$scheduler_communities["page_previous"].".\" title=\"Back to page ".$scheduler_communities["page_previous"].".\" style=\"vertical-align: middle\" /></a>\n";
		} else {
			echo "<img src=\"".ENTRADA_URL."/images/record-previous-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
		}
		echo "</span>";
		echo "<span style=\"vertical-align: middle\">\n";
		echo "<select name=\"pv\" onchange=\"$('pageSelector').submit();\"".(($scheduler_communities["total_pages"] <= 1) ? " disabled=\"disabled\"" : "").">\n";
		for($i = 1; $i <= $scheduler_communities["total_pages"]; $i++) {
			echo "<option value=\"".$i."\"".(($i == $scheduler_communities["page_current"]) ? " selected=\"selected\"" : "").">".(($i == $scheduler_communities["page_current"]) ? " Viewing" : "Jump To")." Page ".$i."</option>\n";
		}
		echo "</select>\n";
		echo "</span>\n";
		echo "<span style=\"width: 20px; vertical-align: middle; margin-left: 3px; text-align: right\">\n";
		if ($scheduler_communities["page_current"] < $scheduler_communities["total_pages"]) {
			echo "<a href=\"".ENTRADA_URL."/admin/communities?".replace_query(array("pv" => $scheduler_communities["page_next"]))."\"><img src=\"".ENTRADA_URL."/images/record-next-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Forward to page ".$scheduler_communities["page_next"].".\" title=\"Forward to page ".$scheduler_communities["page_next"].".\" style=\"vertical-align: middle\" /></a>";
		} else {
			echo "<img src=\"".ENTRADA_URL."/images/record-next-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
		}
		echo "</span>\n";
		echo "</form>\n";
		echo "</div>\n";
		echo "<div class=\"clear\"></div>\n";
	}

	if (count($scheduler_communities["communities"])) {
		if ($ENTRADA_ACL->amIAllowed("communityadmin", "delete", false)) : ?>
		<form action="<?php echo ENTRADA_URL; ?>/admin/communities?section=deactivate" method="post">
		<?php endif; ?>
		<table class="tableList" cellspacing="0" cellpadding="1" summary="List of communities">
			<colgroup>
				<col class="modified" />
				<col class="title" />
				<col class="title" />
				<col class="date" />
				<col class="attachment" />
				<col class="attachment" />
			</colgroup>
			<thead>
				<tr>
					<td class="modified">&nbsp;</td>
					<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "community_title") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("community_title", "Community Title"); ?></td>
					<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "category_title") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("category_title", "Category"); ?></td>
					<td class="date<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "community_opened") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("community_opened", "Creation Date"); ?></td>
					<td class="attachment">&nbsp;</td>
					<td class="attachment">&nbsp;</td>
				</tr>
			</thead>
			<?php if ($ENTRADA_ACL->amIAllowed("communityadmin", "delete", false)) : ?>
			<tfoot>
				<tr>
					<td></td>
					<td colspan="5" style="padding-top: 10px">
						<input type="submit" class="button" value="Deactivate" />
					</td>
				</tr>
			</tfoot>
			<?php endif; ?>
			<tbody>
			<?php
			foreach ($scheduler_communities["communities"] as $result) {
				$url = ENTRADA_URL."/communities?section=modify&community=".$result["community_id"];

				echo "<tr id=\"community-".$result["community_id"]."\">\n";
				echo "	<td class=\"modified\"><input type=\"checkbox\" name=\"checked[]\" value=\"".$result["community_id"]."\" /></td>\n";
				echo "	<td class=\"title\"><a href=\"".$url."\">".html_encode($result["community_title"])."</a></td>\n";
				echo "	<td class=\"title\"><a href=\"".$url."\">".html_encode($result["category_title"])."</a></td>\n";
				echo "	<td class=\"date\"><a href=\"".$url."\">".date(DEFAULT_DATE_FORMAT, $result["community_opened"])."</a></td>\n";
				echo "	<td class=\"attachment\"><a href=\"".ENTRADA_URL."/communities?section=members&community=".$result["community_id"]."\"><img src=\"".ENTRADA_URL."/images/headshot-male.gif\" width=\"16\" height=\"16\" alt=\"Manage Community Members\" title=\"Manage Community Members\" border=\"0\" /></a></td>\n";
				echo "	<td class=\"attachment\"><a href=\"".ENTRADA_URL."/communities?section=modify&community=".$result["community_id"]."\"><img src=\"".ENTRADA_URL."/images/action-edit.gif\" width=\"16\" height=\"16\" alt=\"Manage Community\" title=\"Manage Community\" border=\"0\" /></a></td>\n";
				echo "</tr>\n";
			}
			?>
			</tbody>
		</table>
		<?php if ($ENTRADA_ACL->amIAllowed("communityadmin", "delete", false)) : ?>
		</form>
		<?php
		endif;
	} else {
		?>
		<div class="display-notice">
			<h3>No Available communities</h3>
			There are currently no available communities in the system. To begin click the <strong>Add New Evaluation</strong> link above.
		</div>
		<?php
	}

	echo "<form action=\"\" method=\"get\">\n";
	echo "<input type=\"hidden\" id=\"dstamp\" name=\"dstamp\" value=\"".html_encode($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"])."\" />\n";
	echo "</form>\n";

	/**
	 * Sidebar item that will provide another method for sorting, ordering, etc.
	 */
	$sidebar_html  = "Sort columns:\n";
	$sidebar_html .= "<ul class=\"menu\">\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) == "community_title") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/communities?".replace_query(array("sb" => "community_title"))."\" title=\"Sort by Evaluation Title\">by community title</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) == "community_opened") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/communities?".replace_query(array("sb" => "community_opened"))."\" title=\"Sort by Date &amp; Time\">by date &amp; time</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) == "category_title") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/communities?".replace_query(array("sb" => "category_title"))."\" title=\"Sort by category_title\">by category title</a></li>\n";
	$sidebar_html .= "</ul>\n";
	$sidebar_html .= "Order columns:\n";
	$sidebar_html .= "<ul class=\"menu\">\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) == "asc") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/communities?".replace_query(array("so" => "asc"))."\" title=\"Ascending Order\">in ascending order</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) == "desc") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/communities?".replace_query(array("so" => "desc"))."\" title=\"Descending Order\">in descending order</a></li>\n";
	$sidebar_html .= "</ul>\n";
	$sidebar_html .= "Rows per page:\n";
	$sidebar_html .= "<ul class=\"menu\">\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "5") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/communities?".replace_query(array("pp" => "5"))."\" title=\"Display 5 Rows Per Page\">5 rows per page</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "15") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/communities?".replace_query(array("pp" => "15"))."\" title=\"Display 15 Rows Per Page\">15 rows per page</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "25") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/communities?".replace_query(array("pp" => "25"))."\" title=\"Display 25 Rows Per Page\">25 rows per page</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "50") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/communities?".replace_query(array("pp" => "50"))."\" title=\"Display 50 Rows Per Page\">50 rows per page</a></li>\n";
	$sidebar_html .= "</ul>\n";

	new_sidebar_item("Sort Results", $sidebar_html, "sort-results", "open");

	$ONLOAD[] = "initList()";
}