<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Allows administrators to edit existing events in a community calendar.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
*/

if ((!defined("COMMUNITY_INCLUDED")) || (!defined("IN_EVENTS"))) {
	exit;
} elseif (!$COMMUNITY_LOAD) {
	exit;
}

communities_load_rte();

$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=edit&amp;id=".$RECORD_ID, "title" => "Edit Event");

$HEAD[] = "<link href=\"".ENTRADA_URL."/javascript/calendar/css/xc2_default.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />";
$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/config/xc2_default.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/script/xc2_inpage.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/community/javascript/events.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";

echo "<h1>Edit Event</h1>\n";

if ($RECORD_ID) {
	$query			= "	SELECT a.* FROM `community_events` as a
						LEFT JOIN `communities` AS b ON a.`community_id` = b.`community_id`
						WHERE a.`community_id` = ".$db->qstr($COMMUNITY_ID)."
						AND a.`cpage_id` = ".$db->qstr($PAGE_ID)."
						AND a.`event_active` = '1'
						AND a.`cevent_id` = ".$db->qstr($RECORD_ID);
	$event_record	= $db->GetRow($query);
	if ($event_record) {
		// Error Checking
		switch ($STEP) {
			case 2 :
				/**
				 * Required field "title" / Event Title.
				 */
				if ((isset($_POST["event_title"])) && ($title = clean_input($_POST["event_title"], array("notags", "trim")))) {
					$PROCESSED["event_title"] = $title;
				} else {
					$ERROR++;
					$ERRORSTR[] = "The <strong>Event Title</strong> field is required.";
				}

				/**
				 * Non-Required field "event_location" / Event Location.
				 */
				if ((isset($_POST["event_location"])) && ($event_location = clean_input($_POST["event_location"], array("notags", "trim")))) {
					$PROCESSED["event_location"] = $event_location;
				} else {
					$PROCESSED["event_location"] = "";
				}
				
				$event_dates = validate_calendars("event", true, true);
				if ((isset($event_dates["start"])) && ((int) $event_dates["start"])) {
					$PROCESSED["event_start"] = (int) $event_dates["start"];
				} else {
					$ERROR++;
					$ERRORSTR[] = "The <strong>Event Start</strong> field is required if this is to appear on the calendar.";
				}
				
				if ((isset($event_dates["finish"])) && ((int) $event_dates["finish"])) {
					$PROCESSED["event_finish"] = (int) $event_dates["finish"];
				} else {
					$ERROR++;
					$ERRORSTR[] = "The <strong>Event Finish</strong> field is required if this is to appear on the calendar.";
				}

				/**
				 * Non-Required field "event_description" / Event Details / Description.
				 */
				if ((isset($_POST["event_description"])) && ($description = clean_input($_POST["event_description"], array("trim", "allowedtags")))) {
					$PROCESSED["event_description"] = $description;
				} else {
					$PROCESSED["event_description"] = "";
				}
				
				/**
				 * Required field "release_from" / Release Start (validated through validate_calendars function).
				 * Non-required field "release_until" / Release Finish (validated through validate_calendars function).
				 */
				$release_dates = validate_calendars("release", true, false);
				if ((isset($release_dates["start"])) && ((int) $release_dates["start"])) {
					$PROCESSED["release_date"]	= (int) $release_dates["start"];
				} else {
					$ERROR++;
					$ERRORSTR[] = "The <strong>Release Start</strong> field is required.";
				}
				
				if ((isset($release_dates["finish"])) && ((int) $release_dates["finish"])) {
					$PROCESSED["release_until"]	= (int) $release_dates["finish"];
				} else {
					$PROCESSED["release_until"]	= 0;
				}

				if (!$ERROR) {
					$PROCESSED["community_id"]	= $COMMUNITY_ID;
					$PROCESSED["updated_date"]	= time();
					$PROCESSED["updated_by"]	= $_SESSION["details"]["id"];

					if (!$COMMUNITY_ADMIN) {
						$PROCESSED["pending_moderation"] = 1;
					} else {
						$PROCESSED["pending_moderation"] = 0;
					}
					
					if ($db->AutoExecute("community_events", $PROCESSED, "UPDATE", "`cevent_id` = ".$db->qstr($RECORD_ID)." AND `event_active` = '1' AND `community_id` = ".$db->qstr($COMMUNITY_ID))) {
						if ($PROCESSED["release_date"] != $event_record["release_date"] && COMMUNITY_NOTIFICATIONS_ACTIVE) {
							$notification = $db->GetRow("SELECT * FROM `community_notifications` WHERE `record_id` = ".$db->qstr($RECORD_ID)." AND `type` = 'event'");
							if ($notification) {
								$notification["release_time"] = $PROCESSED["release_date"];
								$db->AutoExecute("community_notifications", $notification, "UPDATE", "`cnotification_id` = ".$db->qstr($notification["cnotification_id"]));
							}
						}
						$url			= COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL;

						$SUCCESS++;
						if (!$COMMUNITY_ADMIN && ($PAGE_OPTIONS["moderate_posts"] == 1)) {
							$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 15000)";
							$SUCCESSSTR[]	= "You have successfully updated this event, however because you are not an administrator your changes must be reviewed before the event will appear on the page again.<br /><br />You will now be redirected to the index; this will happen <strong>automatically</strong> in 15 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
						} else {
							$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";
							$SUCCESSSTR[]	= "You have successfully updated this event.<br /><br />You will now be redirected to the index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
						}
						add_statistic("community:".$COMMUNITY_ID.":events", "edit", "cevent_id", $RECORD_ID);
						communities_log_history($COMMUNITY_ID, $PAGE_ID, $RECORD_ID, "community_history_edit_event", 1);
					}

					if (!$SUCCESS) {
						$ERROR++;
						$ERRORSTR[] = "There was a problem updating this event in the system. The MEdTech Unit was informed of this error; please try again later.";

						application_log("error", "There was an error updating an event. Database said: ".$db->ErrorMsg());
					}
				}

				if ($ERROR) {
					$STEP = 1;
				}
			break;
			case 1 :
			default :
				if (!$COMMUNITY_ADMIN && $PAGE_OPTIONS["moderate_posts"] == 1) {
					$NOTICE++;
					$NOTICESTR[] = "Editing this post will result in it not being displayed on the page until an administrator reviews the changes.";
				}
				$PROCESSED = $event_record;
			break;
		}

		// Page Display
		switch ($STEP) {
			case 2 :
				if ($NOTICE) {
					echo display_notice();
				}
				if ($SUCCESS) {
					echo display_success();
				}
			break;
			case 1 :
			default :
				if ($ERROR) {
					echo display_error();
				}
				if ($NOTICE) {
					echo display_notice();
				}
				?>
				<form action="<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?section=edit&amp;id=<?php echo $RECORD_ID; ?>&amp;step=2" method="post">
				<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Edit Event">
				<colgroup>
					<col style="width: 3%" />
					<col style="width: 20%" />
					<col style="width: 77%" />
				</colgroup>
				<tfoot>
					<tr>
						<td colspan="3" style="padding-top: 15px; text-align: right">
                            <input type="submit" class="button" value="<?php echo $translate->_("global_button_save"); ?>" />                     
						</td>
					</tr>
				</tfoot>
				<tbody>
					<tr>
						<td colspan="3"><h2>Event Details</h2></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><label for="event_title" class="form-required">Event Title</label></td>
						<td><input type="text" id="event_title" name="event_title" value="<?php echo ((isset($PROCESSED["event_title"])) ? html_encode($PROCESSED["event_title"]) : ""); ?>" maxlength="128" style="width: 96%; float: left;" /></td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><label for="event_location" class="form-nrequired">Event Location</label></td>
						<td><input type="text" id="event_location" name="event_location" value="<?php echo ((isset($PROCESSED["event_location"])) ? html_encode($PROCESSED["event_location"]) : ""); ?>" maxlength="128" style="width: 170px" /> <span class="content-small">(<strong>e.g.</strong> Bracken Library, Room 102)</span></td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<?php 
						echo generate_calendars("event", "", true, true, ((isset($PROCESSED["event_start"])) ? $PROCESSED["event_start"] : 0), true, true, ((isset($PROCESSED["event_finish"])) ? $PROCESSED["event_finish"] : 0)); 
					?>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td colspan="2"><label for="event_description" class="form-nrequired">Event Details / Description</label></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td colspan="2">
							<textarea id="event_description" name="event_description" style="width: 98%; height: 200px" cols="70" rows="10"><?php echo ((isset($PROCESSED["event_description"])) ? html_encode($PROCESSED["event_description"]) : ""); ?></textarea>
						</td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="3"><h2>Time Release Options</h2></td>
					</tr>
					<?php echo generate_calendars("release", "", true, true, ((isset($PROCESSED["release_date"])) ? $PROCESSED["release_date"] : time()), true, false, ((isset($PROCESSED["release_until"])) ? $PROCESSED["release_until"] : 0)); ?>
				</tbody>
				</table>
				</form>
				<?php
			break;
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "The announcment record id that you have provided does not exist in the system. Please provide a valid record id to proceed.";

		echo display_error();

		application_log("error", "The provided event record id was invalid [".$RECORD_ID."].");
	}
} else {
	$ERROR++;
	$ERRORSTR[] = "Please provide a valid event record id to proceed.";

	echo display_error();

	application_log("error", "No event record id was provided to edit.");
}
?>