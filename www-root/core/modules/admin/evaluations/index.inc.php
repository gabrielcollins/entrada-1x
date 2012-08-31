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
 * The default file that is loaded when /admin/evaluations is accessed.
 *
 * @author Organisation: Univeristy of Calgary
 * @author Unit: Faculty of Medicine
 * @author Developer: Howard Lu <yhlu@ucalgary.ca>
 * @copyright Copyright 2010 University of Calgary. All Rights Reserved.
 *
*/
if (!defined("IN_EVALUATIONS")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("evaluation", "update", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$GROUP."] and role [".$ROLE."] does not have access to this module [".$MODULE."]");
} else {
	
	/**
	 * Update requested column to sort by.
	 * Valid: date, teacher, title, phase
	 */
	if(isset($_GET["sb"])) {
		if(in_array(trim($_GET["sb"]), array("title" , "evaluation_start", "evaluation_finish", "evaluation_type"))) {
			if (trim($_GET["sb"]) == "title") {
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= "title";
			} elseif (trim($_GET["sb"]) == "evaluation_start") {
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= "evaluation_start";
			} elseif (trim($_GET["sb"]) == "evaluation_finish") {
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= "evaluation_finish";
			} elseif (trim($_GET["sb"]) == "evaluation_type") {
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= "evaluation_type";
			}
		}
	} else {
		if(!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"])) {
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= "title";
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
	<h1>Manage Evaluations</h1>
	<?php

	if ($ENTRADA_ACL->amIAllowed("evaluation", "create", false)) {
		?>
		<div style="float: right">
			<ul class="page-action">
				<li><a href="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?section=add" class="strong-green">Add New Evaluation</a></li>
			</ul>
		</div>
		<div style="clear: both"></div>
		<?php
	}

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
	
	$scheduler_evaluations = array(
				"duration_start" => 0,
				"duration_end" => 0,
				"total_rows" => 0,
				"total_pages" => 0,
				"page_current" => 0,
				"page_previous" => 0,
				"page_next" => 0,
				"evaluations" => array()
			);

	/**
	 * Provide the queries with the columns to order by.
	 */
	switch ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) {
		case "title" :
			$sort_by = "a.`evaluation_title` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["so"]).", a.`evaluation_start` ASC";
		break;
		case "evaluation_start" :
			$sort_by = "a.`evaluation_start` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["so"]).", a.`evaluation_title` ASC";
		break;
		case "evaluation_finish" :
			$sort_by = "a.`evaluation_finish` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["so"]).", a.`evaluation_title` ASC";
		break;
		case "type" :
		default :
			$sort_by = "`evaluation_type` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["so"]).", a.`evaluation_start` ASC";
		break;
	}
	
    /**** Query ***/
    $query_count = "SELECT COUNT(`evaluation_id`) AS `total_rows`
					FROM `evaluations`
					WHERE `evaluation_active` = '1'";
    
    $query_evaluations = "	SELECT a.`evaluation_id`, a.`evaluation_title`, a.`evaluation_active`, a.`evaluation_start`, a.`evaluation_finish`, c.`target_shortname` AS `evaluation_type`
							FROM `evaluations` AS a
							JOIN `evaluation_targets` AS b
							ON a.`evaluation_id` = b.`evaluation_id`
							JOIN `evaluations_lu_targets` AS c
							ON b.`target_id` = c.`target_id`
							GROUP BY a.`evaluation_id`
							ORDER BY %s
							LIMIT %s, %s";

	/**
	 * Get the total number of results using the generated queries above and calculate the total number
	 * of pages that are available based on the results per page preferences.
	 */
	$result_count = $db->GetRow($query_count);

	if ($result_count) {
		$scheduler_evaluations["total_rows"] = (int) $result_count["total_rows"];

		if ($scheduler_evaluations["total_rows"] <= $_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["pp"]) {
			$scheduler_evaluations["total_pages"] = 1;
		} elseif (($scheduler_evaluations["total_rows"] % $_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["pp"]) == 0) {
			$scheduler_evaluations["total_pages"] = (int) ($scheduler_evaluations["total_rows"] / $_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["pp"]);
		} else {
			$scheduler_evaluations["total_pages"] = (int) ($scheduler_evaluations["total_rows"] / $_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["pp"]) + 1;
		}
	} else {
		$scheduler_evaluations["total_rows"] = 0;
		$scheduler_evaluations["total_pages"] = 1;
	}
	/**
	 * Check if pv variable is set and see if it's a valid page, other wise page 1 it is.
	 */
	if (isset($_GET["pv"])) {
		$scheduler_evaluations["page_current"] = (int) trim($_GET["pv"]);

		if (($scheduler_evaluations["page_current"] < 1) || ($scheduler_evaluations["page_current"] > $scheduler_evaluations["total_pages"])) {
			$scheduler_evaluations["page_current"] = 1;
		}
	} else {
		$scheduler_evaluations["page_current"] = 1;
	}

	$scheduler_evaluations["page_previous"] = (($scheduler_evaluations["page_current"] > 1) ? ($scheduler_evaluations["page_current"] - 1) : false);
	$scheduler_evaluations["page_next"] = (($scheduler_evaluations["page_current"] < $scheduler_evaluations["total_pages"]) ? ($scheduler_evaluations["page_current"] + 1) : false);

	/**
	 * Provides the first parameter of MySQLs LIMIT statement by calculating which row to start results from.
	 */
	$limit_parameter = (int) (($_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["pp"] * $scheduler_evaluations["page_current"]) - $_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["pp"]);

	/**
	 * Provide the previous query so we can have previous / next event links on the details page.
	 */
	$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["evaluations"]["previous_query"]["query"] = $query_evaluations;
	$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["evaluations"]["previous_query"]["total_rows"] = $scheduler_evaluations["total_rows"];

	$query_evaluations = sprintf($query_evaluations, $sort_by, $limit_parameter, $_SESSION[APPLICATION_IDENTIFIER]["evaluations"]["pp"]);
	$scheduler_evaluations["evaluations"] = $db->GetAll($query_evaluations);
	
	if ($scheduler_evaluations["total_pages"] > 1) {
		echo "<div class=\"fright\" style=\"margin-bottom: 10px\">\n";
		echo "<form action=\"".ENTRADA_URL."/admin/evaluations\" method=\"get\" id=\"pageSelector\">\n";
		echo "<span style=\"width: 20px; vertical-align: middle; margin-right: 3px; text-align: left\">\n";
		if ($scheduler_evaluations["page_previous"]) {
			echo "<a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("pv" => $scheduler_evaluations["page_previous"]))."\"><img src=\"".ENTRADA_URL."/images/record-previous-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Back to page ".$scheduler_evaluations["page_previous"].".\" title=\"Back to page ".$scheduler_evaluations["page_previous"].".\" style=\"vertical-align: middle\" /></a>\n";
		} else {
			echo "<img src=\"".ENTRADA_URL."/images/record-previous-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
		}
		echo "</span>";
		echo "<span style=\"vertical-align: middle\">\n";
		echo "<select name=\"pv\" onchange=\"$('pageSelector').submit();\"".(($scheduler_evaluations["total_pages"] <= 1) ? " disabled=\"disabled\"" : "").">\n";
		for($i = 1; $i <= $scheduler_evaluations["total_pages"]; $i++) {
			echo "<option value=\"".$i."\"".(($i == $scheduler_evaluations["page_current"]) ? " selected=\"selected\"" : "").">".(($i == $scheduler_evaluations["page_current"]) ? " Viewing" : "Jump To")." Page ".$i."</option>\n";
		}
		echo "</select>\n";
		echo "</span>\n";
		echo "<span style=\"width: 20px; vertical-align: middle; margin-left: 3px; text-align: right\">\n";
		if ($scheduler_evaluations["page_current"] < $scheduler_evaluations["total_pages"]) {
			echo "<a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("pv" => $scheduler_evaluations["page_next"]))."\"><img src=\"".ENTRADA_URL."/images/record-next-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Forward to page ".$scheduler_evaluations["page_next"].".\" title=\"Forward to page ".$scheduler_evaluations["page_next"].".\" style=\"vertical-align: middle\" /></a>";
		} else {
			echo "<img src=\"".ENTRADA_URL."/images/record-next-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
		}
		echo "</span>\n";
		echo "</form>\n";
		echo "</div>\n";
		echo "<div class=\"clear\"></div>\n";
	}

	if (count($scheduler_evaluations["evaluations"])) {
		if ($ENTRADA_ACL->amIAllowed("evaluation", "delete", false)) : ?>
		<form action="<?php echo ENTRADA_URL; ?>/admin/evaluations?section=delete" method="post">
		<?php endif; ?>
		<table class="tableList" cellspacing="0" cellpadding="1" summary="List of Evaluations">
			<colgroup>
				<col class="modified" />
				<col class="title" />
				<col class="date" />
				<col class="targets" />
				<col class="date-smallest" />
				<col class="attachment" />
			</colgroup>
			<thead>
				<tr>
					<td class="modified">&nbsp;</td>
					<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "title") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("title", "Title"); ?></td>
					<td class="date<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "evaluation_start") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("evaluation_start", "Evaluation Start"); ?></td>
					<td class="date<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "evaluation_finish") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("evaluation_finish", "Evaluation Finish"); ?></td>
					<td class="date-smallest<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "type") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("type", "Evaluation Type"); ?></td>
					<td class="attachment">&nbsp;</td>
				</tr>
			</thead>
			<?php if ($ENTRADA_ACL->amIAllowed("evaluation", "delete", false)) : ?>
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
			foreach ($scheduler_evaluations["evaluations"] as $result) {
				$url = ENTRADA_URL."/admin/evaluations?section=progress&evaluation=".$result["evaluation_id"];
				
				echo "<tr id=\"evaluation-".$result["evaluation_id"]."\" class=\"evaluation\">\n";
				echo "	<td class=\"modified\"><input type=\"checkbox\" name=\"checked[]\" value=\"".$result["evaluation_id"]."\" /></td>\n";
				echo "	<td class=\"title\"><a href=\"".$url."\">".html_encode($result["evaluation_title"])."</a></td>\n";
				echo "	<td class=\"date\"><a href=\"".$url."\">".date(DEFAULT_DATE_FORMAT, $result["evaluation_start"])."</a></td>\n";
				echo "	<td class=\"target\"><a href=\"".$url."\">".date(DEFAULT_DATE_FORMAT, $result["evaluation_finish"])."</a></td>\n";
				echo "	<td class=\"date-smallest\"><a href=\"".$url."\">".html_encode($result["evaluation_type"])."</a></td>\n";
				echo "	<td class=\"attachment\"><a href=\"".ENTRADA_URL."/admin/evaluations?section=edit&id=".$result["evaluation_id"]."\"><img src=\"".ENTRADA_URL."/images/action-edit.gif\" width=\"16\" height=\"16\" alt=\"Manage Evaluation Detail\" title=\"Manage Evaluation Detail\" border=\"0\" /></a></td>\n";
				echo "</tr>\n";
			}
			?>
			</tbody>
		</table>
		<?php if ($ENTRADA_ACL->amIAllowed("evaluation", "delete", false)) : ?>
		</form>
		<?php
		endif;
	} else {
		?>
		<div class="display-notice">
			<h3>No Available Evaluations</h3>
			There are currently no available evaluations in the system. To begin click the <strong>Add New Evaluation</strong> link above.
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
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) == "title") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("sb" => "title"))."\" title=\"Sort by Evaluation Title\">by evaluation title</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) == "evaluation_start") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("sb" => "evaluation_start"))."\" title=\"Sort by Evaluation Start\">by evaluation start</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) == "evalluation_finish") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("sb" => "evaluation_finish"))."\" title=\"Sort by Evaluation Finish\">by evaluation finish</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) == "evaluation_type") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("sb" => "evaluation_type"))."\" title=\"Sort by Evaluation Type\">by evaluation type</a></li>\n";
	$sidebar_html .= "</ul>\n";
	$sidebar_html .= "Order columns:\n";
	$sidebar_html .= "<ul class=\"menu\">\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) == "asc") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("so" => "asc"))."\" title=\"Ascending Order\">in ascending order</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) == "desc") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("so" => "desc"))."\" title=\"Descending Order\">in descending order</a></li>\n";
	$sidebar_html .= "</ul>\n";
	$sidebar_html .= "Rows per page:\n";
	$sidebar_html .= "<ul class=\"menu\">\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "5") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("pp" => "5"))."\" title=\"Display 5 Rows Per Page\">5 rows per page</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "15") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("pp" => "15"))."\" title=\"Display 15 Rows Per Page\">15 rows per page</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "25") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("pp" => "25"))."\" title=\"Display 25 Rows Per Page\">25 rows per page</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "50") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("pp" => "50"))."\" title=\"Display 50 Rows Per Page\">50 rows per page</a></li>\n";
	$sidebar_html .= "</ul>\n";

	new_sidebar_item("Sort Results", $sidebar_html, "sort-results", "open");

	$ONLOAD[] = "initList()";
}