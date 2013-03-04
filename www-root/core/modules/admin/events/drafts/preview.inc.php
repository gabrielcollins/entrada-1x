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
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <ryan.warner@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVENTS"))) {
	exit;
} else if ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
		header("Location: ".ENTRADA_URL);
		exit;
} else if (!$ENTRADA_ACL->amIAllowed('event', 'update', false)) {
	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else { 
	$BREADCRUMB[]	= array("url" => "", "title" => "Draft Schedule Preview");
	$draft_id = (int) $_GET["draft_id"];
	
	if (isset($_GET["mode"]) && $_GET["mode"] == "calendar-data") {
				
		ob_clear_open_buffers();
		
		$calendar_start = (int) $_GET["start"];
		$calendar_end = (int) $_GET["end"];
		
		$query = "	SELECT `devent_id`, `event_start`, `event_finish`, `event_title`, `event_location`, `updated_date`
					FROM `draft_events`
					WHERE `draft_id` = ".$db->qstr($draft_id)."
					AND `event_start` >= ".$db->qstr($calendar_start)."
					AND `event_finish` <= ".$db->qstr($calendar_end);
		
		$results = $db->GetAll($query);
		$i = 0;
		if ($results) {
			foreach ($results as $result) {
				$output[$i]["drid"]		= $i;
				$output[$i]["id"]		= $result["devent_id"];
				$output[$i]["start"]	= date("Y-m-d",$result["event_start"])."T".date("H:iP", $result["event_start"]);
				$output[$i]["end"]		= date("Y-m-d",$result["event_finish"])."T".date("H:iP", $result["event_finish"]);
				$output[$i]["title"]	= $result["event_title"];
				$output[$i]["loc"]		= $result["event_location"];
				$output[$i]["type"]		= '1';
				$output[$i]["updated"]	= $result["updated"];
				$i++;
			}
		}
		if ($output) {
			echo json_encode($output);
		}
		
		exit;
		
	}
	
	$query = "	SELECT `devent_id`, `event_start`, `event_finish`, `event_title`, `event_location`, `updated_date`
				FROM `draft_events` WHERE `draft_id` = ".$db->qstr($draft_id)." ORDER BY `event_start`";
	
	if ($results = $db->GetRow($query)) {
		$start = $results["event_start"];
	}
	
	$JQUERY[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/jquery/jquery.weekcalendar.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
	$JQUERY[] = "<link href=\"".ENTRADA_RELATIVE."/css/jquery/jquery.weekcalendar.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";

	?>
	<script type="text/javascript">
	var year = new Date().getFullYear();
	var month = new Date().getMonth();
	var day = new Date().getDate();

	jQuery(document).ready(function() {
		jQuery('#draftCalendar').weekCalendar({
			date : new Date(<?php echo ((($start) ? $start : time()) * 1000); ?>),
			dateFormat : 'M d, Y',
			height: function($calendar) {
				return 600;
			},
			daysToShow: 5,
			firstDayOfWeek: 1,
			useShortDayNames: true,
			allowCalEventOverlap: true,
			overlapEventsSeparate: false,
			timeslotsPerHour: 4,
			timeslotHeight: 19,
			buttons: false,
			readonly: true,
			businessHours : { start: 8, end: 18, limitDisplay : false },
			eventRender : function(calEvent, $event) {
				switch (calEvent.type) {
					case 3 :
						$event.find('.wc-time').css({'backgroundColor': '#5F718F', 'border':'1px solid #354868'});
						$event.css({'backgroundColor':'#7E92B5'});
					break;
					case 2 :
						$event.find('.wc-time').css({'backgroundColor':'#9E9E48', 'border':'1px solid #8A8A2D'});
						$event.css({'backgroundColor':'#B5B37E'});

						if (calEvent.updated) {
							calEvent.title += '<div class="wc-updated-event calEventUpdated' + calEvent.id + '"> Last updated ' + calEvent.updated + '</div>';
						}
					break;
					default :
					break;
				}
			},
			eventClick : function(calEvent, $event) {
				console.log(calEvent.id);
				window.location = "<?php echo ENTRADA_RELATIVE; ?>/admin/events?section=edit&mode=draft&id="+calEvent.id;
			},
			externalDates : function (calendar) {
				jQuery('#currentDateInfo').html(calendar.find('.wc-day-1').html() + ' - ' + calendar.find('.wc-day-5').html());
			},
			data : '<?php echo ENTRADA_RELATIVE; ?>/admin/events/drafts/preview?mode=calendar-data&draft_id=<?php echo $draft_id; ?>'
		});
	});

	function setDateValue(field, date) {
		timestamp = (getMSFromDate(date) * 1000);

		if (field.value != timestamp) {
			field.value = getMSFromDate(date);
			jQuery('#draftCalendar').weekCalendar('gotoWeek', new Date(timestamp));
		}

		return;
	}
	</script>
	<table style="width: 100%" cellspacing="0" cellpadding="0" border="0" summary="Weekly Student Calendar">
	<tr>
		<td style="text-align: left; vertical-align: middle; white-space: nowrap">
			<table style="width: 375px; height: 23px" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td style="width: 22px; height: 23px"><img src="<?php echo ENTRADA_URL; ?>/images/cal-back.gif" width="22" height="23" alt="Previous Week" title="Previous Week" border="0" class="wc-prev" onclick="jQuery('#draftCalendar').weekCalendar('prevWeek');" /></td>
				<td style="width: 271px; height: 23px; background: url('<?php echo ENTRADA_URL; ?>/images/cal-table-bg.gif'); text-align: center; font-size: 10px; color: #666666">
					<div id="currentDateInfo"></div>
				</td>
				<td style="width: 22px; height: 23px"><img src="<?php echo ENTRADA_URL; ?>/images/cal-next.gif" width="22" height="23" alt="Next Week" title="Next Week" border="0" class="wc-next" onclick="jQuery('#draftCalendar').weekCalendar('nextWeek');" /></td>
				<td style="width: 30px; height: 23px; text-align: right"><img src="<?php echo ENTRADA_URL; ?>/images/cal-home.gif" width="23" height="23" alt="Reset to this week" title="Reset to this week" border="0" class="wc-today" onclick="jQuery('#draftCalendar').weekCalendar('today');" /></td>
			</tr>
			</table>
		</td>
		<td style="text-align: right; vertical-align: middle; white-space: nowrap">
			<h1 style="margin: 8px 0"><strong>Draft</strong> Schedule Preview</h1>
		</td>
	</tr>
	</table>
	<div id="draftCalendar"></div>
	<br />
	<form>
		<input style="float:right;" type="button" value="Return" onclick="window.location = '<?php echo ENTRADA_RELATIVE; ?>/admin/events/drafts?section=edit&draft_id=<?php echo $draft_id; ?>';" />
	</form>
	<?php

}