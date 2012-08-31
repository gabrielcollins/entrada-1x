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
 * This file is used to add events to the entrada.events table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if((!defined("PARENT_INCLUDED")) || (!defined("IN_REPORTS"))) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->amIAllowed('reportindex', 'read', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	?>
	<style type="text/css">
		ol.system-reports li {
			width:			70%;
			color:			#666666;
			font-size:		12px;
			padding:		0px 15px 15px 0px;
			margin-left:	5px;
		}
		
		ol.system-reports li a {
			font-size:		13px;
			font-weight:	bold;
		}
	</style>
	<h1><?php echo $MODULES[strtolower($MODULE)]["title"]; ?></h1>

	<h2 style="color: #669900">Learning Event Reports</h2>
	<ol class="system-reports">
		<li>
			<a href="<?php echo ENTRADA_URL; ?>/admin/reports?section=event-types-by-course">Learning Event Types by Course</a><br />
			A detailed report containing a learning event type breakdown by Course.
		</li>
		<li>
			<a href="<?php echo ENTRADA_URL; ?>/admin/reports?section=course-summary">Course Summary Report</a><br />
			A report containing a summary of objectives, presentations, and hot topics for each learning event.
		</li>
		<li>
			<a href="<?php echo ENTRADA_URL; ?>/admin/reports?section=curriculum-review">Curriculum Review Report</a><br />
			A report containing a summary of objectives, and presentations for each event.
		</li>
	</ol>

	<h2 style="color: #669900">Teaching Event Reports</h2>
	<ol class="system-reports">
		<li>
			<a href="<?php echo ENTRADA_URL; ?>/admin/reports?section=teaching-report-by-course">Teaching Report By Course (hourly)</a><br />
			A teaching report that shows how many hours faculty are teaching across all courses.
		</li>
		<li>
			<a href="<?php echo ENTRADA_URL; ?>/admin/reports?section=teaching-report-by-faculty">Teaching Report By Faculty Member (hourly)</a><br />
			A teaching report that shows how many hours faculty are teaching in Clinical Skills, Expanded Clinical Skills, PBL, etc.
		</li>
		<li>
			<a href="<?php echo ENTRADA_URL; ?>/admin/reports?section=teaching-report-by-department">Faculty Teaching Report By Department (half days)</a><br />
			A teaching report that shows how many hours faculty are teaching different event types broken down by department and division.
		</li>
		<li>
			<a href="<?php echo ENTRADA_URL; ?>/admin/reports?section=teaching-faculty-contact-details">Teaching Faculty Contact Details</a><br />
			Contact information for teachers who have taught between the selected time period.
		</li>
	</ol>

	<h2 style="color: #669900">Learner Incident Reports</h2>
	<ol class="system-reports">
		<li>
			<a href="<?php echo ENTRADA_URL; ?>/admin/reports?section=incident-report-by-follow-up">Open Incidents By Follow-Up Date</a><br />
			A report showing all open student incidents that have a follow-up date during the given time period.
		</li>
		<li>
			<a href="<?php echo ENTRADA_URL; ?>/admin/reports?section=incident-report-by-date">Open Incidents By Incident Date</a><br />
			A report showing all open incidents that were entered / started during the given time period.
		</li>
	</ol>

	<h2 style="color: #669900">Usage Reports</h2>
	<ol class="system-reports">
		<li>
			<a href="<?php echo ENTRADA_URL; ?>/admin/reports?section=report-on-podcasting">Podcast Usage Report</a><br />
			A detailed report showing the usage statistics about all included podcasts.
		</li>
	</ol>
	<?php
}
?>