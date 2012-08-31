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
 * The default file that is loaded when /admin/groups is accessed.
 *
 * @author Organisation: Univeristy of Calgary
 * @author Unit: Faculty of Medicine
 * @author Developer: Doug Hall <yhlu@ucalgary.ca>
 * @copyright Copyright 2010 University of Calgary. All Rights Reserved.
 *
*/
if (!defined("IN_GROUPS")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("group", "update", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$GROUP."] and role [".$ROLE."] does not have access to this module [".$MODULE."]");
} else {
	/**
	 * Update requested column to sort by.
	 * Valid: director, name
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

	$admin_wording = "Administrator View";
	$admin_url = ENTRADA_URL."/admin/groups";

	/**
	 * Determine the type of search that is requested.
	 */
	if ((isset($_GET["type"])) && (in_array(trim($_GET["type"]), array("search", "browse-group", "browse-dept")))) {
		$search_type = clean_input($_GET["type"], "trim");
	}

	if (isset($_GET["sb"])) {
		if (@in_array(trim($_GET["sb"]), array("group_name", "members", "updated_date"))) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= trim($_GET["sb"]);
		}

		$_SERVER["QUERY_STRING"] = replace_query(array("sb" => false));
	} else {
		if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"])) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = "group_name";
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
	 * Check if preferences need to be updated on the server at this point.
	 */
	preferences_update($MODULE, $PREFERENCES);

	?>
	<h1>Manage Groups</h1>
		<div style="float: right">
			<ul class="page-action">
				<li><a href="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?section=add" class="strong-green">Add Group</a></li>
			</ul>
		</div>
		<div style="clear: both"></div> 

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

	$scheduler_groups = array(
				"duration_start" => 0,
				"duration_end" => 0,
				"total_rows" => 0,
				"total_pages" => 0,
				"page_current" => 0,
				"page_previous" => 0,
				"page_next" => 0,
				"groups" => array()
			);

	/**
	 * Provide the queries with the columns to order by.
	 */
	switch ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) {
		case "group_name" :
			$sort_by = "a.`group_name` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["groups"]["so"]).", a.`group_name` ASC";
		break;
		case "members" :
			$sort_by = "`members` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["groups"]["so"]).", `members` ASC";
		break;
		case "updated_date" :
		default :
			$sort_by = "a.`updated_date` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["groups"]["so"]).", a.`updated_date` ASC";
		break;
	}
	
	$query_count = "	SELECT COUNT(a.`group_id`) AS `total_rows`
						FROM `groups` AS a
						JOIN `group_organisations` AS b
						ON b.`group_id` = a.`group_id`
						WHERE a.`group_active` = '1'
						AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation());

	$query_groups = "	SELECT a.*, COUNT(b.`gmember_id`) AS members, CASE WHEN (MIN(b.`member_active`) = 0) THEN 1 ELSE 0 END AS `inactive`
						FROM `groups` AS a
						LEFT JOIN `group_members` b
						ON b.`group_id` = a.`group_id`
						AND b.`member_active` = 1
						JOIN `group_organisations` AS c
						ON c.`group_id` = a.`group_id`
						WHERE a.`group_active` = '1'
						AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation());
	
	switch ($search_type) {
		case "search" :
		default :
			if ((isset($_GET["q"])) && ($query = clean_input($_GET["q"], array("trim", "notags")))) {
				$search_query = $query;
				$search_query_text = html_encode($query);
			}

			$sql_ext = "	and (`group_name` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%").")  ";
			$query_count = $query_count.$sql_ext;
			$query_groups = $query_groups.$sql_ext;
		break;
	}

	$query_groups = $query_groups." GROUP By a.`group_id` ORDER BY %s LIMIT %s, %s";

	/**
	 * Get the total number of results using the generated queries above and calculate the total number
	 * of pages that are available based on the results per page preferences.
	 */
	$result_count = $db->GetRow($query_count);

	if ($result_count) {
		$scheduler_groups["total_rows"] = (int) $result_count["total_rows"];

		if ($scheduler_groups["total_rows"] <= $_SESSION[APPLICATION_IDENTIFIER]["groups"]["pp"]) {
			$scheduler_groups["total_pages"] = 1;
		} elseif (($scheduler_groups["total_rows"] % $_SESSION[APPLICATION_IDENTIFIER]["groups"]["pp"]) == 0) {
			$scheduler_groups["total_pages"] = (int) ($scheduler_groups["total_rows"] / $_SESSION[APPLICATION_IDENTIFIER]["groups"]["pp"]);
		} else {
			$scheduler_groups["total_pages"] = (int) ($scheduler_groups["total_rows"] / $_SESSION[APPLICATION_IDENTIFIER]["groups"]["pp"]) + 1;
		}
	} else {
		$scheduler_groups["total_rows"] = 0;
		$scheduler_groups["total_pages"] = 1;
	}
	/**
	 * Check if pv variable is set and see if it's a valid page, other wise page 1 it is.
	 */
	if (isset($_GET["pv"])) {
		$scheduler_groups["page_current"] = (int) trim($_GET["pv"]);

		if (($scheduler_groups["page_current"] < 1) || ($scheduler_groups["page_current"] > $scheduler_groups["total_pages"])) {
			$scheduler_groups["page_current"] = 1;
		}
	} else {
		$scheduler_groups["page_current"] = 1;
	}

	$scheduler_groups["page_previous"] = (($scheduler_groups["page_current"] > 1) ? ($scheduler_groups["page_current"] - 1) : false);
	$scheduler_groups["page_next"] = (($scheduler_groups["page_current"] < $scheduler_groups["total_pages"]) ? ($scheduler_groups["page_current"] + 1) : false);

	/**
	 * Provides the first parameter of MySQLs LIMIT statement by calculating which row to start results from.
	 */
	$limit_parameter = (int) (($_SESSION[APPLICATION_IDENTIFIER]["groups"]["pp"] * $scheduler_groups["page_current"]) - $_SESSION[APPLICATION_IDENTIFIER]["groups"]["pp"]);

	/**
	 * Provide the previous query so we can have previous / next event links on the details page.
	 */
	$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["groups"]["previous_query"]["query"] = $query_groups;
	$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["groups"]["previous_query"]["total_rows"] = $scheduler_groups["total_rows"];

	$query_groups = sprintf($query_groups, $sort_by, $limit_parameter, $_SESSION[APPLICATION_IDENTIFIER]["groups"]["pp"]);
	$scheduler_groups["groups"] = $db->GetAll($query_groups);
//	Zend_Debug::dump($scheduler_groups);

	?>
	<style type="text/css">
	.dynamic-tab-pane-control .tab-page {
		height: 100px;
	}
	</style>
	<div class="tab-pane" id="user-tabs">
		<div class="tab-page">
			<h2 class="tab">Group Search</h2>
			<form action="<?php echo ENTRADA_URL; ?>/admin/groups" method="get">
			<input type="hidden" name="type" value="search" />
			<table style="width: 100%" cellspacing="1" cellpadding="1" border="0" summary="Search for Groups">
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
						<input type="button" class="button" value="Show All"  onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/groups'"/>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td>&nbsp;</td>
					<td style="vertical-align: top"><label for="q" class="form-required">Group Search:</label></td>
					<td>
						<input type="text" id="q" name="q" value="<?php echo html_encode($search_query); ?>" style="width: 350px" />
						<div class="content-small" style="margin-top: 10px">
							<strong>Note:</strong> You can search for Group name.
						</div>
					</td>
				</tr>
			</tbody>
			</table>
			</form>
		</div>
	</div>
	
	<script type="text/javascript">setupAllTabs(true);</script>
	<?php
	echo "<p />";
	if ($scheduler_groups["total_pages"] > 1) {
		echo "<div class=\"fright\" style=\"margin-bottom: 10px\">\n";
		echo "<form action=\"".ENTRADA_URL."/admin/groups\" method=\"get\" id=\"pageSelector\">\n";
		echo "<span style=\"width: 20px; vertical-align: middle; margin-right: 3px; text-align: left\">\n";
		if ($scheduler_groups["page_previous"]) {
			echo "<a href=\"".ENTRADA_URL."/admin/groups?".replace_query(array("pv" => $scheduler_groups["page_previous"]))."\"><img src=\"".ENTRADA_URL."/images/record-previous-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Back to page ".$scheduler_groups["page_previous"].".\" title=\"Back to page ".$scheduler_groups["page_previous"].".\" style=\"vertical-align: middle\" /></a>\n";
		} else {
			echo "<img src=\"".ENTRADA_URL."/images/record-previous-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
		}
		echo "</span>";
		echo "<span style=\"vertical-align: middle\">\n";
		echo "<select name=\"pv\" onchange=\"$('pageSelector').submit();\"".(($scheduler_groups["total_pages"] <= 1) ? " disabled=\"disabled\"" : "").">\n";
		for($i = 1; $i <= $scheduler_groups["total_pages"]; $i++) {
			echo "<option value=\"".$i."\"".(($i == $scheduler_groups["page_current"]) ? " selected=\"selected\"" : "").">".(($i == $scheduler_groups["page_current"]) ? " Viewing" : "Jump To")." Page ".$i."</option>\n";
		}
		echo "</select>\n";
		echo "</span>\n";
		echo "<span style=\"width: 20px; vertical-align: middle; margin-left: 3px; text-align: right\">\n";
		if ($scheduler_groups["page_current"] < $scheduler_groups["total_pages"]) {
			echo "<a href=\"".ENTRADA_URL."/admin/groups?".replace_query(array("pv" => $scheduler_groups["page_next"]))."\"><img src=\"".ENTRADA_URL."/images/record-next-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Forward to page ".$scheduler_groups["page_next"].".\" title=\"Forward to page ".$scheduler_groups["page_next"].".\" style=\"vertical-align: middle\" /></a>";
		} else {
			echo "<img src=\"".ENTRADA_URL."/images/record-next-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
		}
		echo "</span>\n";
		echo "</form>\n";
		echo "</div>\n";
		echo "<div class=\"clear\"></div>\n";
	}

	if ($scheduler_groups["groups"] && count($scheduler_groups["groups"])) {
		if ($ENTRADA_ACL->amIAllowed("group", "delete", false)) : ?>
		<form id="frmSelect"  action="<?php echo ENTRADA_URL; ?>/admin/groups?section=manage" method="post">
		<?php endif; ?>
		<table class="tableList" cellspacing="0" cellpadding="1" summary="List of groups">
			<colgroup>
				<col class="modified" />
				<col class="community_title" />
				<col class="community_shortname" />
				<col class="community_opened" />
				<col class="attachment" />
			</colgroup>
			<thead>
				<tr>
					<td class="modified">&nbsp;</td>
					<td class="community_title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "group_name") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("group_name", "Group Name"); ?></td>
					<td class="community_shortname<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "members") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("members", "Number of members"); ?></td>
					<td class="community_opened<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "updated_date") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("updated_date", "Updated Date"); ?></td>
					<td class="attachment">&nbsp;</td>
				</tr>
			</thead>
			<?php if ($ENTRADA_ACL->amIAllowed("group", "delete", false) or $ENTRADA_ACL->amIAllowed("group", "update", false)) : ?>
			<tfoot>
				<tr>
					<td />
						<?php
						$colspan = 4;
						if ($ENTRADA_ACL->amIAllowed("group", "delete", false)) {
							$colspan--;
							?>
							<td style="padding-top: 10px"><input type="submit" class="button" value="Delete Selected"  onClick="$('frmSelect').action ='<?php echo ENTRADA_URL; ?>/admin/groups?section=manage'" /></td>
							<?php
						}
						if ($ENTRADA_ACL->amIAllowed("group", "update", false)) {
							$colspan--;
							?>
							<td style="padding-top: 10px">
								<input type="submit" class="button" value="Edit Selected" onClick="$('frmSelect').action ='<?php echo ENTRADA_URL; ?>/admin/groups?section=edit'" />
							</td>
							<?php
						}
						if ($ENTRADA_ACL->amIAllowed("group", "read", false)) {
							$colspan--;
							?>
							<td style="padding-top: 10px">
								<input type="submit" class="button" value="Export" onClick="$('frmSelect').action ='<?php echo ENTRADA_URL; ?>/admin/groups?section=csv'" />
							</td>
							<?php
						}
						echo "<td colspan=\"$colspan\" />";
						?>
				</tr>
			</tfoot>
			<?php endif; ?>
			<tbody>
			<?php
			foreach ($scheduler_groups["groups"] as $result) {
				$url = ENTRADA_URL."/admin/groups?section=edit&ids=".$result["group_id"];


				echo "<tr id=\"group-".$result["group_id"]."\" class=\"group".((!$result["group_active"]) ? " na" : (($result["inactive"]) ? " np" : ""))."\">\n";
				echo "	<td class=\"modified\"><input type=\"checkbox\" name=\"checked[]\" value=\"".$result["group_id"]."\" /></td>\n";
				echo "	<td class=\"community_title\"><a href=\"".$url."\">".html_encode($result["group_name"])."</a></td>\n";
				echo "	<td class=\"community_shortname\"><a href=\"".$url."\">".$result["members"]."</a></td>\n";
				echo "	<td class=\"date\"><a href=\"".$url."\">".date("M jS Y", $result["updated_date"])."</a></td>\n";
				echo "	<td class=\"attachment\"><a href=\"".ENTRADA_URL."/admin/groups?section=edit&ids=".$result["group_id"]."\"><img src=\"".ENTRADA_URL."/images/action-edit.gif\" width=\"16\" height=\"16\" alt=\"Manage Group\" title=\"Manage Group\" border=\"0\" /></a></td>\n";
				echo "</tr>\n";
			}
			?>
			</tbody>
		</table>
		<?php if ($ENTRADA_ACL->amIAllowed("group", "delete", false)) : ?>
		</form>
		<?php
		endif;
	} else {
		?>
		<div class="display-notice">
			<h3>No Available Groups</h3>
			There are currently no available small groups in the system. To begin click the <strong>Add Group</strong> link above.
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
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) == "group_name") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/groups?".replace_query(array("sb" => "group_name"))."\" title=\"Sort by Category\">by name</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) == "members") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/groups?".replace_query(array("sb" => "members"))."\" title=\"Sort by Number of Groups\">by number of members</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) == "updated_date") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/groups?".replace_query(array("sb" => "updated_date"))."\" title=\"Sort by Date &amp; Time\">by date &amp; time</a></li>\n";
	$sidebar_html .= "</ul>\n";
	$sidebar_html .= "Order columns:\n";
	$sidebar_html .= "<ul class=\"menu\">\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) == "asc") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/groups?".replace_query(array("so" => "asc"))."\" title=\"Ascending Order\">in ascending order</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) == "desc") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/groups?".replace_query(array("so" => "desc"))."\" title=\"Descending Order\">in descending order</a></li>\n";
	$sidebar_html .= "</ul>\n";
	$sidebar_html .= "Rows per page:\n";
	$sidebar_html .= "<ul class=\"menu\">\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "5") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/groups?".replace_query(array("pp" => "5"))."\" title=\"Display 5 Rows Per Page\">5 rows per page</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "15") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/groups?".replace_query(array("pp" => "15"))."\" title=\"Display 15 Rows Per Page\">15 rows per page</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "25") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/groups?".replace_query(array("pp" => "25"))."\" title=\"Display 25 Rows Per Page\">25 rows per page</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "50") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/groups?".replace_query(array("pp" => "50"))."\" title=\"Display 50 Rows Per Page\">50 rows per page</a></li>\n";
	$sidebar_html .= "</ul>\n";

	new_sidebar_item("Sort Results", $sidebar_html, "sort-results", "open");

	$ONLOAD[] = "initList()";
}
