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
 * The default file that is loaded when /admin/users is accessed.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_USERS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("user", "update", false)) {
	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	add_manage_user_sidebar();
	/**
	 * Add this for the tabs.
	 */
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/tabpane/tabpane.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
	$HEAD[] = "<link href=\"".ENTRADA_URL."/css/tabpane.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";

	$search_type		= "browse-newest";
	$browse_number		= 25;
	$results_per_page	= 25;
	$search_query		= "";
	$search_query_text	= "";
	$query_counter		= "";
	$query_search		= "";
	$show_results		= false;
	
	/**
	 * Determine the type of search that is requested.
	 */
	if ((isset($_GET["type"])) && (in_array(trim($_GET["type"]), array("search", "browse-group", "browse-dept")))) {
		$search_type = clean_input($_GET["type"], "trim");
	}

	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/selectchained.js\"></script>\n";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/picklist.js\"></script>\n";
	
	$i = count($HEAD);
	$HEAD[$i]  = "<script type=\"text/javascript\">\n";
	$HEAD[$i] .= "addListGroup('account_type', 'cs-top');\n";
	if (is_array($SYSTEM_GROUPS)) {
		$item = 1;
		foreach ($SYSTEM_GROUPS as $group => $roles) {
			$HEAD[$i] .= "addList('cs-top', '".ucwords($group)."', '".$group."', 'cs-sub-".$item."', ".(((isset($_GET["g"])) && ($_GET["g"] == $group)) ? "1" : "0").");\n";
			$HEAD[$i] .= "addOption('cs-sub-".$item."', '-- Any --', 'any', ".(((!isset($_GET["r"])) || ((isset($_GET["r"])) && ($_GET["r"] == 'any'))) ? "1" : "0").");\n";
			if (is_array($roles) && count($roles)) {
				foreach ($roles as $role) {
					$HEAD[$i] .= "addOption('cs-sub-".$item."', '".ucwords($role)."', '".$role."', ".(((isset($_GET["r"])) && ($_GET["r"] == $role)) ? "1" : "0").");\n";
				}
			}
			$item++;
		}
	}
	$HEAD[$i] .= "</script>\n";

	$ONLOAD[] = "initListGroup('account_type', $('group'), $('role'))";
			
	switch ($search_type) {
		case "browse-group" :
			$browse_group	= false;
			$browse_role	= false;

			if ((isset($_GET["g"])) && (isset($SYSTEM_GROUPS[$group = clean_input($_GET["g"], "credentials")]))) {
				$browse_group = $group;
				$search_query_text	= html_encode(ucwords($group));
				if ((isset($_GET["r"])) && (@in_array($role = clean_input($_GET["r"], "credentials"), $SYSTEM_GROUPS[$browse_group]))) {
					$browse_role = $role;
					$search_query_text.= " &rarr; ".html_encode(ucwords($role));
				} else {
					$search_query_text.= " &rarr; Any Class";
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "To browse a group, you must select a group from the group select list.";
			}

			if (!$ERROR) {
				$query_counter	= "	SELECT COUNT(a.`id`) AS `total_rows`
									FROM `".AUTH_DATABASE."`.`user_data` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
									ON b.`user_id` = a.`id`
									AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
									WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
									AND b.`group` = ".$db->qstr($browse_group)."
									".(($browse_role) ? "AND b.`role` = ".$db->qstr($browse_role) : "");
				$query_search	= "	SELECT a.*, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`account_active`, b.`access_starts`, b.`access_expires`, b.`last_login`, b.`role`, b.`group`
									FROM `".AUTH_DATABASE."`.`user_data` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
									ON b.`user_id` = a.`id`
									AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
									WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
									AND b.`group` = ".$db->qstr($browse_group)."
									".(($browse_role) ? "AND b.`role` = ".$db->qstr($browse_role) : "")."
									ORDER BY `fullname` ASC
									LIMIT %s, %s";
			}
		break;
		case "browse-dept" :
			$browse_dept = 0;

			if ((isset($_GET["d"])) && ($department = clean_input($_GET["d"], array("trim", "int")))) {
				$query = "SELECT * FROM `".AUTH_DATABASE."`.`departments` WHERE `department_id` = ".$db->qstr($department);
				$result = $db->GetRow($query);
				if ($result) {
					$browse_department = $department;
					$search_query_text = html_encode($result["department_title"]);
				} else {
					$ERROR++;
					$ERRORSTR[] = "The department you have provided does not exist. Please ensure that you select a valid department from the department list.";
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "To browse a department, you must select a department from the department selection list.";
			}

			if (!$ERROR) {
				$query_counter = "	SELECT COUNT(a.`id`) AS `total_rows`
									FROM `".AUTH_DATABASE."`.`user_data` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
									ON b.`user_id` = a.`id`
									AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
									LEFT JOIN `".AUTH_DATABASE."`.`user_departments` AS c
									ON c.`user_id` = a.`id`
									WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
									AND c.`dep_id` = ".$db->qstr($browse_department);
				$query_search = "	SELECT a.*, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`account_active`, b.`access_starts`, b.`access_expires`, b.`last_login`, b.`role`, b.`group`
									FROM `".AUTH_DATABASE."`.`user_data` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
									ON b.`user_id` = a.`id`
									AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
									LEFT JOIN `".AUTH_DATABASE."`.`user_departments` AS c
									ON c.`user_id` = a.`id`
									WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
									AND c.`dep_id` = ".$db->qstr($browse_department)."
									ORDER BY `fullname` ASC
									LIMIT %s, %s";
			}
		break;
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

			if (isset($_GET["search-type"]) && $_GET["search-type"]) {
				if ($_GET["search-type"] == "all") {
					$query_counter	= "	SELECT COUNT(a.`id`) AS `total_rows`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON b.`user_id` = a.`id`
										AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										WHERE (a.`number` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`username` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`email` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`firstname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`lastname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%").")";
					$query_search	= "	SELECT a.*, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`account_active`, b.`access_starts`, b.`access_expires`, b.`last_login`, b.`role`, b.`group`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON b.`user_id` = a.`id`
										AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										WHERE (a.`number` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`username` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`email` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`firstname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`lastname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%").")
										ORDER BY `fullname` ASC
										LIMIT %s, %s";
				} elseif ($_GET["search-type"] == "active") {
					$query_counter	= "	SELECT COUNT(a.`id`) AS `total_rows`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON b.`user_id` = a.`id`
										AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										AND b.`account_active` = 'true'
										AND b.`access_starts` < ".$db->qstr(time())."
										AND (b.`access_expires` > ".$db->qstr(time())." OR b.`access_expires` = 0) AND
										(a.`number` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`username` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`email` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`firstname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`lastname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%").")";
					$query_search	= "	SELECT a.*, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`account_active`, b.`access_starts`, b.`access_expires`, b.`last_login`, b.`role`, b.`group`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON b.`user_id` = a.`id`
										AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										AND b.`account_active` = 'true'
										AND b.`access_starts` < ".$db->qstr(time())."
										AND (b.`access_expires` > ".$db->qstr(time())." OR b.`access_expires` = 0) AND
										(a.`number` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`username` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`email` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`firstname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`lastname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%").")
										ORDER BY `fullname` ASC
										LIMIT %s, %s";
				} elseif ($_GET["search-type"] == "inactive") {
					$query_counter	= "	SELECT COUNT(a.`id`) AS `total_rows`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON b.`user_id` = a.`id`
										AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										AND (b.`account_active` = 'false'
										OR (b.`access_starts` > ".$db->qstr(time())."
										OR (b.`access_expires` < ".$db->qstr(time())." AND b.`access_expires` != 0))) AND
										(a.`number` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`username` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`email` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`firstname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`lastname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%").")";
					$query_search	= "	SELECT a.*, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`account_active`, b.`access_starts`, b.`access_expires`, b.`last_login`, b.`role`, b.`group`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON b.`user_id` = a.`id`
										AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										AND (b.`account_active` = 'false'
										OR (b.`access_starts` > ".$db->qstr(time())."
										OR (b.`access_expires` < ".$db->qstr(time())." AND b.`access_expires` != 0))) AND
										(a.`number` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`username` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`email` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`firstname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`lastname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%").")
										ORDER BY `fullname` ASC
										LIMIT %s, %s";
				} elseif ($_GET["search-type"] == "new") {
					$query_counter	= "	SELECT COUNT(a.`id`) AS `total_rows`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON b.`user_id` = a.`id`
										AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										WHERE b.`app_id` IS NULL AND
										(a.`number` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`username` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`email` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`firstname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`lastname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%").")";
					$query_search	= "	SELECT a.*, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`account_active`, b.`access_starts`, b.`access_expires`, b.`last_login`, b.`role`, b.`group`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON b.`user_id` = a.`id`
										AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										WHERE b.`app_id` IS NULL AND
										(a.`number` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`username` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`email` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`firstname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`lastname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%").")
										ORDER BY `fullname` ASC
										LIMIT %s, %s";
				}

				$sidebar_html  = "<div style=\"margin: 2px 0px 10px 3px; font-size: 10px\">\n";
				$sidebar_html .= "	<div><img src=\"".ENTRADA_URL."/images/legend-active-member.gif\" width=\"14\" height=\"14\" alt=\"\" title=\"\" style=\"vertical-align: middle\" /> Active Member</div>\n";
				$sidebar_html .= "	<div><img src=\"".ENTRADA_URL."/images/legend-inactive-member.gif\" width=\"14\" height=\"14\" alt=\"\" title=\"\" style=\"vertical-align: middle\" /> Inactive Member</div>\n";
				$sidebar_html .= "	<div><img src=\"".ENTRADA_URL."/images/legend-non-member.gif\" width=\"14\" height=\"14\" alt=\"\" title=\"\" style=\"vertical-align: middle\" /> Non-Member</div>\n";
				$sidebar_html .= "</div>\n";

				new_sidebar_item("Members Legend", $sidebar_html, "member-legend", "open");
			}
		break;
	}

	/**
	 * Get the total number of results using the generated queries above and calculate the total number
	 * of pages that are available based on the results per page preferences.
	 */
	$result = ((USE_CACHE) ? $db->CacheGetRow(CACHE_TIMEOUT, $query_counter) : $db->GetRow($query_counter));
	if ($result) {
		$total_rows	= $result["total_rows"];

		if ($total_rows <= $results_per_page) {
			$total_pages = 1;
		} elseif (($total_rows % $results_per_page) == 0) {
			$total_pages = (int) ($total_rows / $results_per_page);
		} else {
			$total_pages = (int) ($total_rows / $results_per_page) + 1;
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
	?>
	<h1><?php echo $MODULES[strtolower($MODULE)]["title"]; ?></h1>

	<div style="float: right">
		<ul class="page-action">
			<li><a href="<?php echo ENTRADA_URL; ?>/admin/users?section=add" class="strong-green">Add New User</a></li>
		</ul>
	</div>
	<div style="clear: both"></div>

	<style type="text/css">
	.dynamic-tab-pane-control .tab-page {
		height: 150px;
	}
	</style>
	<div class="tab-pane" id="user-tabs">
		<div class="tab-page">
			<h2 class="tab">Newest Users</h2>
			<form action="<?php echo ENTRADA_URL; ?>/admin/users" method="get">
			<input type="hidden" name="type" value="browse-newest" />
			<table style="width: 100%" cellspacing="1" cellpadding="1" border="0" summary="Browse Newest Users">
			<colgroup>
				<col style="width: 3%" />
				<col style="width: 25%" />
				<col style="width: 72%" />
			</colgroup>
			<tfoot>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
						<input type="submit" class="button" value="Show" />
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td>&nbsp;</td>
					<td style="vertical-align: top"><label for="number" class="form-required">Number of Newest Users:</label></td>
					<td>
						<select id="number" name="n" style="width: 100px">
						<option value="25">25</option>
						<option value="50">50</option>
						<option value="75">75</option>
						<option value="100">100</option>
						</select>
					</td>
				</tr>
			</tbody>
			</table>
			</form>
		</div>
		<div class="tab-page">
			<h2 class="tab">User Search</h2>
			<form action="<?php echo ENTRADA_URL; ?>/admin/users" method="get">
			<input type="hidden" name="type" value="search" />
			<table style="width: 100%" cellspacing="1" cellpadding="1" border="0" summary="Search For User">
			<colgroup>
				<col style="width: 3%" />
				<col style="width: 25%" />
				<col style="width: 72%" />
			</colgroup>
			<tfoot>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
						<input type="submit" class="button" value="Search" />
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td>&nbsp;</td>
					<td style="vertical-align: top"><label for="q" class="form-required">User Search:</label></td>
					<td>
						<input type="text" id="q" name="q" value="<?php echo html_encode($search_query); ?>" style="width: 350px" />
						<div class="content-small" style="margin-top: 10px">
							<strong>Note:</strong> You can search for name, username, e-mail address or staff / student number.
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td style="vertical-align: top"><label for="search-type" class="form-required">Search Type:</label></td>
					<td>
						<select name="search-type">
							<option value="all" <?php echo ((isset($_GET["search-type"]) && $_GET["search-type"] == "all") || (!isset($_GET["search-type"])) ? "selected=\"true\" " : ""); ?>>All Users</option>
							<option value="active" <?php echo (isset($_GET["search-type"]) && $_GET["search-type"] == "active" ? "selected=\"true\" " : ""); ?>>Users With Active Membership</option>
							<option value="inactive" <?php echo (isset($_GET["search-type"]) && $_GET["search-type"] == "inactive" ? "selected=\"true\" " : ""); ?>>Users With Inactive Membership </option>
							<option value="new" <?php echo (isset($_GET["search-type"]) && $_GET["search-type"] == "new" ? "selected=\"true\" " : ""); ?>>Users With No Membership</option>
						</select>
					</td>
				</tr>
			</tbody>
			</table>
			</form>
		</div>
		<div class="tab-page">
			<h2 class="tab">Browse Groups</h2>
			<form action="<?php echo ENTRADA_URL; ?>/admin/users" method="get">
			<input type="hidden" name="type" value="browse-group" />
			<table style="width: 100%" cellspacing="1" cellpadding="1" border="0" summary="Browse By Groups">
			<colgroup>
				<col style="width: 3%" />
				<col style="width: 25%" />
				<col style="width: 72%" />
			</colgroup>
			<tfoot>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
						<input type="submit" class="button" value="Browse" />
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td>&nbsp;</td>
					<td><label for="group" class="form-required">Browse Group:</label></td>
					<td>
						<select id="group" name="g" style="width: 209px"></select>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><label for="role" class="form-nrequired">Browse Role:</label></td>
					<td>
						<select id="role" name="r" style="width: 209px"></select>
					</td>
				</tr>
			</tbody>
			</table>
			</form>
		</div>
		<div class="tab-page">
			<h2 class="tab">Browse Departments</h2>
			<form action="<?php echo ENTRADA_URL; ?>/admin/users" method="get">
			<input type="hidden" name="type" value="browse-dept" />
			<table style="width: 100%" cellspacing="1" cellpadding="1" border="0" summary="Browse By Department">
			<colgroup>
				<col style="width: 3%" />
				<col style="width: 25%" />
				<col style="width: 72%" />
			</colgroup>
			<tfoot>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
						<input type="submit" class="button" value="Browse" />
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td>&nbsp;</td>
					<td><label for="department" class="form-required">Browse Department:</label></td>
					<td>
						<select id="department" name="d" style="width: 95%">
						<?php
						$query		= "
									SELECT a.`department_id`, a.`department_title`, a.`organisation_id`, b.`entity_title`, c.`organisation_title`
									FROM `".AUTH_DATABASE."`.`departments` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`entity_type` AS b
									ON a.`entity_id` = b.`entity_id`
									LEFT JOIN `".AUTH_DATABASE."`.`organisations` AS c
									ON a.`organisation_id` = c.`organisation_id`
									ORDER BY c.`organisation_title` ASC, a.`department_title`";
						$results	= $db->GetAll($query);
						if ($results) {
							foreach ($results as $result) {
								echo "<option value=\"".(int) $result["department_id"]."\"".(((isset($browse_department)) && ((int) $browse_department) && ($browse_department == $result["department_id"])) ? " selected=\"selected\"" : "").">".html_encode(limit_chars($result["organisation_title"], 11)).": ".html_encode($result["department_title"])." ".(($result["entity_title"]) ? "(".html_encode($result["entity_title"]).")" : "")."</option>\n";
							}
						}
						?>
						</select>
					</td>
				</tr>
			</tbody>
			</table>
			</form>
		</div>
	</div>
	<script type="text/javascript">setupAllTabs(true);</script>
	<?php
	if (($search_type) && (!$ERROR)) {
		if ($total_pages > 1) {
			echo "<br />\n";
			echo "<div style=\"text-align: right\">\n";
			echo "	<form action=\"".ENTRADA_URL."/admin/".$MODULE."\" method=\"get\" id=\"pageSelector\" style=\"display: inline\">\n";
			echo "	<span style=\"width: 20px; vertical-align: middle; margin-right: 3px; text-align: left\">\n";
			if ($page_previous) {
				echo "<a href=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("pv" => $page_previous))."\"><img src=\"".ENTRADA_URL."/images/record-previous-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Back to page ".$page_previous.".\" title=\"Back to page ".$page_previous.".\" style=\"vertical-align: middle\" /></a>\n";
			} else {
				echo "<img src=\"".ENTRADA_URL."/images/record-previous-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
			}
			echo "	</span>";
			echo "	<span style=\"vertical-align: middle\">\n";
			echo "	<select name=\"pv\" onchange=\"window.location = '".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("pv" => false))."&amp;pv='+this.options[this.selectedIndex].value;\"".(($total_pages <= 1) ? " disabled=\"disabled\"" : "").">\n";
			for($i = 1; $i <= $total_pages; $i++) {
				echo "<option value=\"".$i."\"".(($i == $page_current) ? " selected=\"selected\"" : "").">".(($i == $page_current) ? " Viewing" : "Jump To")." Page ".$i."</option>\n";
			}
			echo "	</select>\n";
			echo "	</span>\n";
			echo "	<span style=\"width: 20px; vertical-align: middle; margin-left: 3px; text-align: right\">\n";
			if ($page_current < $total_pages) {
				echo "<a href=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("pv" => $page_next))."\"><img src=\"".ENTRADA_URL."/images/record-next-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Forward to page ".$page_next.".\" title=\"Forward to page ".$page_next.".\" style=\"vertical-align: middle\" /></a>";
			} else {
				echo "<img src=\"".ENTRADA_URL."/images/record-next-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
			}
			echo "	</span>\n";
			echo "	</form>\n";
			echo "</div>\n";
		}
		/**
		 * Provides the first parameter of MySQLs LIMIT statement by calculating which row to start results from.
		 */
		$limit_parameter = (int) (($results_per_page * $page_current) - $results_per_page);
		$query	= sprintf($query_search, $limit_parameter, $results_per_page);
		$results	= $db->GetAll($query);
		
		if ($results) {
			?>
			<div style="margin-top: 10px; background-color: #FAFAFA; padding: 3px; border: 1px #9D9D9D solid; border-bottom: none">
				<img src="<?php echo ENTRADA_URL; ?>/images/lecture-info.gif" width="15" height="15" alt="" title="" style="vertical-align: middle" />
				<?php echo "Found ".$total_rows." user".(($total_rows != 1) ? "s" : "")." matching &quot;<strong>".($search_query_text)."</strong>&quot; in the user management system."; ?>
			</div>
			<form action="<?php echo ENTRADA_URL; ?>/admin/users?section=delete" method="post">
			<table class="tableList" cellspacing="0" summary="List of Users">
			<colgroup>
				<col class="modified" />
				<col class="title" />
				<col class="general" />
				<col class="general" />
				<col class="date" />
			</colgroup>
			<thead>
				<tr>
					<td class="modified">&nbsp;</td>
					<td class="title sortedASC" style="font-size: 12px"><div class="noLink">Full Name</div></td>
					<td class="general" style="font-size: 12px">Username</td>
					<td class="general" style="font-size: 12px">Group &amp; Role</td>
					<td class="date" style="font-size: 12px">Last Login</td>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td></td>
					<td colspan="4" style="padding-top: 10px">
						<input type="submit" class="button" value="Delete Selected" />
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php
				foreach ($results as $result) {
					$can_login	= true;
					$url		= ENTRADA_URL."/admin/users/manage?id=".$result["id"];
					$add_url		= ENTRADA_URL."/admin/users?section=add&amp;id=".$result["id"];

					if ($result["account_active"] == "false") {
						$can_login = false;
					}
					
					if (($access_starts = (int) $result["access_starts"]) && ($access_starts > time())) {
						$can_login = false;
					}
					if (($access_expires = (int) $result["access_expires"]) && ($access_expires < time())) {
						$can_login = false;
					}
					if ($result["account_active"]) {
						echo "<tr class=\"user".((!$can_login) ? " na" : "")."\">\n";
						echo "	<td class=\"modified\">".($ENTRADA_ACL->amIAllowed(new UserResource($result["id"], $result['organisation_id']), 'delete') ? "<input type=\"checkbox\" name=\"delete[]\" value=\"".$result["id"]."\" />" : '')."</td>\n";
						echo "	<td class=\"title\">".(($url) ? "<a href=\"".$url."\" title=\"Edit Account: ".html_encode($result["fullname"])."\">" : "").html_encode($result["fullname"]).(($url) ? "</a>" : "")."</td>\n";
						echo "	<td class=\"general\">".(($url) ? "<a href=\"".$url."\" title=\"Edit Account: ".html_encode($result["fullname"])."\">" : "").html_encode($result["username"]).(($url) ? "</a>" : "")."</td>\n";
						echo "	<td class=\"general\">".(($url) ? "<a href=\"".$url."\" title=\"Edit Account: ".html_encode($result["fullname"])."\">" : "").ucwords($result["group"])." &rarr; ".ucwords($result["role"]).(($url) ? "</a>" : "")."</td>\n";
						echo "	<td class=\"date\">".(($url) ? "<a href=\"".$url."\" title=\"Edit Account: ".html_encode($result["fullname"])."\">" : "").(((int) $result["last_login"]) ? date(DEFAULT_DATE_FORMAT, (int) $result["last_login"]) : "Never Logged In").(($url) ? "</a>" : "")."</td>\n";
						echo "</tr>\n";
					} else {
						echo "<tr class=\"user disabled\">\n";
						echo "	<td class=\"modified\">".($ENTRADA_ACL->amIAllowed(new UserResource($result["id"], $result['organisation_id']), 'create') ? "<a class=\"strong-green\" href=\"".$add_url."\" ><img style=\"border: none;\" src=\"".ENTRADA_URL."/images/btn_add.gif\" /></a>" : '')."</td>\n";
						echo "	<td class=\"title content-small\">".html_encode($result["fullname"])."</td>\n";
						echo "	<td class=\"general content-small\">".html_encode($result["username"])."</td>\n";
						echo "	<td class=\"general\">&nbsp;</td>\n";
						echo "	<td class=\"date\">&nbsp;</td>\n";
						echo "</tr>\n";
					}
				}
				?>
			</tbody>
			</table>
			</form>
			<?php
		} else {
			echo "<div class=\"display-notice\">\n";
			echo "	<h3>No Matching People</h3>\n";
			echo "	There are no people in the system found which contain matches to &quot;<strong>".($search_query_text)."</strong>&quot;.<br /><br />";
			echo "	You can add a new users by clicking the <strong>Add New User</strong> link.\n";
			echo "</div>\n";
		}
	}
}