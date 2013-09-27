<?php
/**
 * Online Course Resources [Pre-Clerkship]
 * Module:	Reports
 * Area:		Admin
 * @author Unit: Medical Education Technology Unit
 * @author Director: Dr. Benjamin Chen <bhc@post.queensu.ca>
 * @author Developer: Matt Simpson <simpson@post.queensu.ca>
 * @version 3.0
 * @copyright Copyright 2007 Queen's University, MEdTech Unit
 *
 * $Id: report-by-event-types.inc.php 992 2009-12-22 16:26:26Z simpson $
 */

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_REPORTS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_RELATIVE);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("report", "read", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]." and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[]	= array("url" => "", "title" => "Learning Event Types by Course");
	
	/**
	 * Add PlotKit to the beginning of the $HEAD array.
	 */
	array_unshift($HEAD,
		"<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/MochiKit/MochiKit.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/PlotKit/excanvas.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/PlotKit/Base.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/PlotKit/Layout.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/PlotKit/Canvas.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/PlotKit/SweetCanvas.js\"></script>"
		);
	
	$HEAD[]		= "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/picklist.js\"></script>\n";
	$ONLOAD[]	= "$('courses_list').style.display = 'none'";
	
	?>
	</style>	
	<div class="no-printing">
		<h2>Reporting Dates</h2>
		<form action="<?php echo ENTRADA_RELATIVE; ?>/admin/reports?section=<?php echo $SECTION; ?>&step=2" method="post" onsubmit="selIt()" class="form-horizontal">
			<div class="control-group">
				<table>
					<tr>
						<?php echo generate_calendars("reporting", "Reporting Date", true, true, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_start"], true, true, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_finish"]); ?>
					</tr>
				</table>
			</div>
			<div class="row-fluid">
				<div class="pull-right">
					<input type="submit" class="btn btn-primary" value="Create Report" />
				</div>
			</div>
		</form>
	</div>
	<?php
	if ($STEP == 2) {
		
		$query = "SELECT a.`id`, a.`title`, a.`start`, a.`end`, 
						CONCAT(b.`lastname`, ', ', b.`firstname`) AS `student_name`, 
						IF (a.`preceptor_proxy_id` IS NULL OR a.`preceptor_proxy_id` = '', 
							CONCAT(a.`preceptor_lastname`, ', ', a.`preceptor_firstname`), 
							CONCAT(c.`lastname`, ', ', c.`firstname`)) AS `preceptor_name` 
					FROM `student_observerships` AS a
					JOIN `".AUTH_DATABASE."`.`user_data` AS b
					ON a.`student_id` = b.`id`
					JOIN `".AUTH_DATABASE."`.`user_access` AS d
					ON a.`student_id` = d.`user_id`
					AND d.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
					LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS c
					ON a.`preceptor_proxy_id` = c.`id`
					WHERE a.`start` BETWEEN ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_start"])." AND ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_finish"])."
					GROUP BY a.`id`
					ORDER BY a.`start`";
		$results = $db->GetAll($query);
				
		echo "<h2 style=\"page-break-before: avoid\">Observerships within date range:</h2>";
		echo "<div class=\"content-small\" style=\"margin-bottom: 10px\">\n";
		echo "	<strong>Date Range:</strong> ".date(DEFAULT_DATE_FORMAT, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_start"])." <strong>to</strong> ".date(DEFAULT_DATE_FORMAT, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_finish"]).".";
		echo "</div>\n";
		
		if ($results) {
			?>
				<table width="100%" cellpadding="0" cellspacing="0" class="table table-bordered table-striped">
					<thead>
						<th>Observership</th>
						<th>Start</th>
						<th>End</th>
						<th>Student Name</th>
						<th>Preceptor</th>
					</thead>
					<tbody>
						<?php foreach ($results as $result) { ?>
						<tr>
							<td><?php echo $result["title"]; ?></td>
							<td><?php echo date("Y-m-d", $result["start"]); ?></td>
							<td><?php echo !empty($result["end"]) ? date("Y-m-d", $result["end"]) : date("Y-m-d", $result["start"]); ?></td>
							<td><?php echo $result["student_name"]; ?></td>
							<td><?php echo $result["preceptor_name"]; ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			<?php
		} else {
			add_notice("No student observerships were found within this date range. Please review the selected date range and run the report again. If you received this message in error please contact an administrator for assistance.");
			echo display_notice();
		}
		
	}
}
?>