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
 * This file displays the list of learning events that match any requested
 * filters. Data is pulled from the entrada.events table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVENTS"))) {
	exit;
} else if ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
		header("Location: ".ENTRADA_URL);
		exit;
} else if (!$ENTRADA_ACL->amIAllowed('eventcontent', 'update', false)) {
	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/script/xc2_timestamp.js\"></script>\n";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";

	/**
	 * Process any sorting or pagination requests.
	 */
	events_process_sorting();

	/**
	 * Process any filter requests.
	 */
	events_process_filters($ACTION, "admin");

	/**
	 * Check if preferences need to be updated.
	 */
	preferences_update($MODULE, $PREFERENCES);

	/**
	 * Fetch all of the events that apply to the current filter set.
	 */
	$learning_events = events_fetch_filtered_events(
			$ENTRADA_USER->getActiveId(),
			$ENTRADA_USER->getActiveGroup(),
			$ENTRADA_USER->getActiveRole(),
			$ENTRADA_USER->getActiveOrganisation(),
			$_SESSION[APPLICATION_IDENTIFIER]["events"]["sb"],
			$_SESSION[APPLICATION_IDENTIFIER]["events"]["so"],
			$_SESSION[APPLICATION_IDENTIFIER]["events"]["dtype"],
			$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"],
			0,
			$_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"],
			true,
			(isset($_GET["pv"]) ? (int) trim($_GET["pv"]) : 1),
			$_SESSION[APPLICATION_IDENTIFIER]["events"]["pp"],
            false,
            false);

	echo "<h1>".$MODULES[strtolower($MODULE)]["title"]."</h1>";

	/**
	 * Output the filter HTML.
	 */
	events_output_filter_controls("admin");

	if ($ENTRADA_ACL->amIAllowed("event", "create", false)) {
		?>
		<div style="float: right">
			<ul class="page-action">
				<li><a href="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?section=add" class="strong-green">Add New Event</a></li>
			</ul>
		</div>
		<div style="clear: both"></div>
		<?php
	}

	/**
	 * Output the calendar controls and pagination.
	 */
	events_output_calendar_controls("admin");

	if (!empty($learning_events["events"])) {
		if ($ENTRADA_ACL->amIAllowed("event", "delete", false) or $ENTRADA_ACL->amIAllowed("event", "create", false)) : ?>
		<form name="frmSelect" action="<?php echo ENTRADA_URL; ?>/admin/events?section=delete" method="post">
		<?php endif; ?>
		<div class="tableListTop">
			<img src="<?php echo ENTRADA_URL; ?>/images/lecture-info.gif" width="15" height="15" alt="" title="" style="vertical-align: middle" />
			<?php
			switch ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"]) {
				case "day" :
					echo "Found ".$learning_events["total_rows"]." event".(($learning_events["total_rows"] != 1) ? "s" : "")." that take place on <strong>".date("D, M jS, Y", $learning_events["duration_start"])."</strong>.\n";
				break;
				case "month" :
					echo "Found ".$learning_events["total_rows"]." event".(($learning_events["total_rows"] != 1) ? "s" : "")." that take place during <strong>".date("F", $learning_events["duration_start"])."</strong> of <strong>".date("Y", $learning_events["duration_start"])."</strong>.\n";
				break;
				case "year" :
					echo "Found ".$learning_events["total_rows"]." event".(($learning_events["total_rows"] != 1) ? "s" : "")." that take place during <strong>".date("Y", $learning_events["duration_start"])."</strong>.\n";
				break;
				default :
				case "week" :
					echo "Found ".$learning_events["total_rows"]." event".(($learning_events["total_rows"] != 1) ? "s" : "")." from <strong>".date("D, M jS, Y", $learning_events["duration_start"])."</strong> to <strong>".date("D, M jS, Y", $learning_events["duration_end"])."</strong>.\n";
				break;
			}
			?>
		</div>
		<table class="tableList" cellspacing="0" cellpadding="1" summary="List of Events">
			<colgroup>
				<col class="modified" />
				<col class="date" />
				<col class="term" />
				<col class="teacher" />
				<col class="title" />
				<col class="attachment" />
			</colgroup>
			<thead>
				<tr>
					<td class="modified">&nbsp;</td>
					<td class="date<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "date") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("date", "Date &amp; Time"); ?></td>
					<td class="term<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "term") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("term", "Term"); ?></td>
					<td class="teacher<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "teacher") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("teacher", "Teacher"); ?></td>
					<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "title") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("title", "Event Title"); ?></td>
					<td class="attachment">&nbsp;</td>
				</tr>
			</thead>
			<?php if ($ENTRADA_ACL->amIAllowed("event", "delete", false) or $ENTRADA_ACL->amIAllowed("event", "create", false)) : ?>
			<tfoot>
				<tr>
					<td></td>
					<td style="padding-top: 10px" colspan="3">
						<?php
						if ($ENTRADA_ACL->amIAllowed("event", "delete", false)) {
							?>
							<input type="submit" class="button" value="Delete Selected" />
							<?php
						}
						if ($ENTRADA_ACL->amIAllowed("event", "create", false)) {
							?>
							<input type="submit" class="button" value="Copy Selected"  onClick="document.frmSelect.action ='<?php echo ENTRADA_URL; ?>/admin/events?section=copy'" />
							<?php
						}
						?>
					</td>
					<td style="padding-top: 10px; text-align: right" colspan="2">
						<?php
						if ($ENTRADA_ACL->amIAllowed("event", "delete", false)) {
							?>
							<input type="button" value="Export Results" onclick="window.location='<?php echo ENTRADA_URL . "/admin/events/export"; ?>';" />
							<?php
						}
						?>
					</td>
				</tr>
			</tfoot>
			<?php endif; ?>
			<tbody>
			<?php

			$count_modified = 0;

			foreach ($learning_events["events"] as $result) {
				$url = "";
				$accessible = true;
				$administrator = false;
				if ($ENTRADA_ACL->amIAllowed(new EventResource($result["event_id"], $result["course_id"], $result["organisation_id"]), "update")) {
					$administrator = true;
					$url = ENTRADA_URL."/admin/events?section=edit&amp;id=".$result["event_id"];
				} else if ($ENTRADA_ACL->amIAllowed(new EventContentResource($result["event_id"], $result["course_id"], $result["organisation_id"]), "update")) {
					$url = ENTRADA_URL."/admin/events?section=content&amp;id=".$result["event_id"];
				}

				if ((($result["release_date"]) && ($result["release_date"] > time())) || (($result["release_until"]) && ($result["release_until"] < time()))) {
					$accessible = false;
				}

				echo "<tr id=\"event-".$result["event_id"]."\" class=\"event".((!$url) ? " np" : ((!$accessible) ? " na" : ""))."\">\n";
				echo "	<td class=\"modified\">".(($administrator) ? "<input type=\"checkbox\" name=\"checked[]\" value=\"".$result["event_id"]."\" />" : "<img src=\"".ENTRADA_URL."/images/pixel.gif\" width=\"19\" height=\"19\" alt=\"\" title=\"\" />")."</td>\n";
				echo "	<td class=\"date".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Event Date\">" : "").date(DEFAULT_DATE_FORMAT, $result["event_start"]).(($url) ? "</a>" : "")."</td>\n";
				echo "	<td class=\"term".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Intended For ".html_encode($result["event_term"])."\">" : "").html_encode($result["event_term"]).(($url) ? "</a>" : "")."</td>\n";
				echo "	<td class=\"teacher".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Primary Teacher: ".html_encode($result["fullname"])."\">" : "").html_encode($result["fullname"]).(($url) ? "</a>" : "")."</td>\n";
				echo "	<td class=\"title".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Event Title: ".html_encode($result["event_title"])."\">" : "").html_encode($result["event_title"]).(($url) ? "</a>" : "")."</td>\n";
				echo "	<td class=\"attachment\">".(($url) ? "<a href=\"".ENTRADA_URL."/admin/events?section=content&amp;id=".$result["event_id"]."\"><img src=\"".ENTRADA_URL."/images/event-contents.gif\" width=\"16\" height=\"16\" alt=\"Manage Event Content\" title=\"Manage Event Content\" border=\"0\" /></a>" : "<img src=\"".ENTRADA_URL."/images/pixel.gif\" width=\"16\" height=\"16\" alt=\"\" title=\"\" />")."</td>\n";
				echo "</tr>\n";
			}
			?>
			</tbody>
		</table>
		<?php if ($ENTRADA_ACL->amIAllowed("event", "delete", false)) : ?>
		</form>
		<?php
		endif;
	} else {
		$filters_applied = (((isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["filters"])) && ($filters_total = @count($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["filters"]))) ? true : false);
		?>
		<div class="display-notice">
			<h3>No Matching Events</h3>
			There are no learning events scheduled
			<?php
			switch ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"]) {
				case "day" :
					echo "that take place on <strong>".date(DEFAULT_DATE_FORMAT, $learning_events["duration_start"])."</strong>";
				break;
				case "month" :
					echo "that take place during <strong>".date("F", $learning_events["duration_start"])."</strong> of <strong>".date("Y", $learning_events["duration_start"])."</strong>";
				break;
				case "year" :
					echo "that take place during <strong>".date("Y", $learning_events["duration_start"])."</strong>";
				break;
				default :
				case "week" :
					echo "from <strong>".date(DEFAULT_DATE_FORMAT, $learning_events["duration_start"])."</strong> to <strong>".date(DEFAULT_DATE_FORMAT, $learning_events["duration_end"])."</strong>";
				break;
				default :
					continue;
				break;
			}
			echo (($filters_applied) ? " that also match the supplied &quot;Show Only&quot; restrictions" : "") ?>.
			<br /><br />
			If this is unexpected there are a few things that you can check:
			<ol>
				<li style="padding: 3px">Make sure that you are browsing the intended time period. For example, if you trying to browse <?php echo date("F", time()); ?> of <?php echo date("Y", time()); ?>, make sure that the results bar above says &quot;... takes place in <strong><?php echo date("F", time()); ?></strong> of <strong><?php echo date("Y", time()); ?></strong>&quot;.</li>
				<?php
				if ($filters_applied) {
					echo "<li style=\"padding: 3px\">You also have ".$filters_total." filter".(($filters_total != 1) ? "s" : "")." applied to the event list. you may wish to remove ".(($filters_total != 1) ? "one or more of these" : "it")." by clicking the link in the &quot;Showing Events That Include&quot; box above.</li>";
				}
				?>
			</ol>
		</div>
		<?php
	}

	echo "<form action=\"\" method=\"get\">\n";
	echo "<input type=\"hidden\" id=\"dstamp\" name=\"dstamp\" value=\"".html_encode($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"])."\" />\n";
	echo "</form>\n";

	/**
	 * Output the sidebar for sorting and legend.
	 */
	events_output_sidebar("admin");

	$ONLOAD[] = "initList()";
}