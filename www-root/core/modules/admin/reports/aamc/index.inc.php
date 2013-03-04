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
 * @author Developer: Matt Simpson <simpson@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_AAMC_CI"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("report", "read", false)) {
	$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	?>

	<h1>AAMC Curriculum Inventory Reports</h1>
	<div style="float: right">
		<ul class="page-action">
			<li><a href="<?php echo ENTRADA_URL; ?>/admin/reports/aamc?section=add" class="strong-green">Create New Report</a></li>
		</ul>
	</div>
	<div style="clear: both"></div>

	<?php
	$query = "SELECT * FROM `reports_aamc_ci` WHERE `report_active` = '1' ORDER BY `report_date` DESC";
	$results = $db->GetAll($query);
	if ($results) {
		?>
		<form action="<?php echo ENTRADA_URL; ?>/admin/reports/aamc?section=delete" method="post">
		<table class="tableList" cellspacing="0" summary="List of AAMC Curriculum Inventory Reports">
			<colgroup>
				<col class="modified" />
				<col class="date" />
				<col class="title" />
				<col class="general" />
			</colgroup>
			<thead>
				<tr>
					<td class="modified">&nbsp;</td>
					<td class="date sortedDESC">Report Date</td>
					<td class="title">Report Title</td>
					<td class="general">Status</td>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td></td>
					<td colspan="3" style="padding-top: 10px">
						<input type="submit" class="button" value="Delete Selected" />
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php
			foreach ($results as $result) {
				$url = ENTRADA_RELATIVE . "/admin/reports/aamc/manage?id=".$result["raci_id"];

				echo "<tr>\n";
				echo "	<td class=\"modified\"><input type=\"checkbox\" name=\"delete[]\" value=\"".$result["raci_id"]."\" /></td>\n";
				echo "	<td class=\"date\"><a href=\"".$url."\">".date("Y-m-d", $result["report_date"])."</a></td>\n";
				echo "	<td class=\"title\"><a href=\"".$url."\">".html_encode($result["report_title"])."</a></td>\n";
				echo "	<td class=\"general\"><a href=\"".$url."\">".ucwords(strtolower($result["report_status"]))."</a></td>\n";
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
			<h3>No Available AAMC Curriculum Inventory Reports</h3>
			<p>There are currently no AAMC Curriculum Inventory reports in the system. To create a new report, click the <strong>Create New Report</strong> link above.</p>
		</div>
		<?php
	}
}
