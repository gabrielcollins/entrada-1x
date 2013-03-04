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

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_NOTICES"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("notice", "update", false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	/**
	 * This query will automatically remove notices that have expired more than 5 days ago.
	 */
	if ((defined("DEVELOPMENT_MODE")) && (DEVELOPMENT_MODE == false)) {
		$query = "DELETE FROM `notices` WHERE `display_until` <= '".strtotime("-5 days", strtotime("00:00:00"))."'";
		$db->Execute($query);
	}

	/**
	 * Update requested column to sort by.
	 * Valid: date, teacher, title, phase
	 */
	if (isset($_GET["sb"])) {
		if (@in_array(trim($_GET["sb"]), array("date", "summary"))) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = trim($_GET["sb"]);
		}

		$_SERVER["QUERY_STRING"] = replace_query(array("sb" => false));
	} else {
		if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"])) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = "date";
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
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"] = "desc";
		}
	}

	/**
	 * Check if preferences need to be updated on the server at this point.
	 */
	preferences_update($MODULE, $PREFERENCES);

	/**
	 * Provide the queries with the columns to order by.
	 */
	switch($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) {
		case "summary" :
			$SORT_BY = "`notices`.`notice_summary` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]).", `notices`.`display_until` ASC";
		break;
		case "date" :
		default :
			$SORT_BY = "`notices`.`display_until` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
		break;
	}
	?>
	<h1><?php echo $MODULES[strtolower($MODULE)]["title"]; ?></h1>

	<div class="display-generic">
		These notices will be displayed to the user directly on their <?php echo APPLICATION_NAME; ?> dashboard as well as on a publicly accessible RSS feed. <strong>Please note</strong> we do not recommend posting confidential information inside these notices.
	</div>
	<?php
	if ($ENTRADA_ACL->amIAllowed("notice", "create", false)) {
		?>
		<div style="float: right">
			<ul class="page-action">
				<li><a href="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?section=add" class="strong-green">Add New Notice</a></li>
			</ul>
		</div>
		<div style="clear: both"></div>
		<?php
	}

	$query = "	SELECT `notices`.*, `organisations`.`organisation_title`
				FROM `notices`
				JOIN `".AUTH_DATABASE."`.`organisations`
				ON `".AUTH_DATABASE."`.`organisations`.`organisation_id` = `notices`.`organisation_id`
				WHERE `notices`.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
				ORDER BY ".$SORT_BY;
	$results = $db->GetAll($query);
	if ($results) {
		?>
		<form action="<?php echo ENTRADA_URL; ?>/admin/notices?section=delete" method="post">
			<table class="tableList" cellspacing="0" summary="List of Notices">
				<colgroup>
					<col class="modified" />
					<col class="date" />
					<col class="title" />
				</colgroup>
				<thead>
					<tr>
						<td class="modified">&nbsp;</td>
						<td class="date<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "date") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("date", "Display Until"); ?></td>
						<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "summary") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("summary", "Notice"); ?></td>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td></td>
						<td colspan="2" style="padding-top: 10px">
							<input type="submit" class="button" value="Delete Selected" />
						</td>
					</tr>
				</tfoot>
				<tbody>
					<?php
					foreach ($results as $result) {
						$url = ENTRADA_URL."/admin/notices?section=edit&amp;id=".$result["notice_id"];
						$expired = false;

						if (($display_until = (int) $result["display_until"]) && ($display_until < time())) {
							$expired = true;
						}

						echo "<tr id=\"notice-".$result["notice_id"]."\" class=\"notice".(($expired) ? " na" : "")."\">\n";
						echo "	<td class=\"modified\"><input type=\"checkbox\" name=\"delete[]\" value=\"".$result["notice_id"]."\" /></td>\n";
						echo "	<td class=\"date\">".(($url) ? "<a href=\"".$url."\">" : "").date(DEFAULT_DATE_FORMAT, $result["display_until"]).(($url) ? "</a>" : "")."</td>\n";
						echo "	<td class=\"title content-small\">".limit_chars(strip_tags($result["notice_summary"]), 125, false, true)."</td>\n";
//						echo "	<td class=\"accesses\" style=\"text-align: center\">".count_notice_reads($result["notice_id"])."</td>\n";
						echo "</tr>\n";
					}
					?>
				</tbody>
			</table>
		</form>
		<?php
	} else {
		?>
		<div class="display-notice">
			<h3>No Available Notices</h3>
			There are currently no notices registered in the system. To add a notice click the <strong>Add Notice</strong> link.
		</div>
		<?php
	}
}