<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Displayed the event calendar index, which will hopefully be a real calendar
 * sooner rather than later.
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

$HEAD[] = "<link href=\"".ENTRADA_URL."/javascript/calendar/css/xc2_default.css\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />";
$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/config/xc2_default.js\"></script>\n";
$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/script/xc2_inpage.js\"></script>\n";
$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/script/xc2_timestamp.js\"></script>\n";

if (!$RECORD_ID) {
	/**
	 * Update requested length of time to display.
	 * Valid: day, week, month, year
	 */
	if (isset($_GET["dtype"])) {
		if (in_array(trim($_GET["dtype"]), array("day", "week", "month", "year"))) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"] = trim($_GET["dtype"]);
		}
	} else {
		if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"])) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"] = "week";
		}
	}
	
	if (isset($_GET["dstamp"])) {
		$integer = (int) trim($_GET["dstamp"]);
		if ($integer) {
			$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"] = $integer;
		}
	
		$_SERVER["QUERY_STRING"] = replace_query(array("dstamp" => false));
	} else {
		if (!isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"])) {
			$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"] = time();
		}
	}
	
	/**
	 * This fetches the unix timestamps from the first and last second of the day, week, month, year, etc.
	 */
	$display_duration = fetch_timestamps($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"], $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"]);
	
	/**
	 * Update requsted number of rows per page.
	 * Valid: any integer really.
	 */
	if ((isset($_GET["pp"])) && ((int) trim($_GET["pp"]))) {
		$integer = (int) trim($_GET["pp"]);
	
		if (($integer > 0) && ($integer <= 250)) {
			$_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["pp"] = $integer;
		}
	
		$_SERVER["QUERY_STRING"] = replace_query(array("pp" => false));
	} else {
		if (!isset($_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["pp"])) {
			$_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["pp"] = 5;
		}
	}
	
	/**
	 * Get the total number of results using the generated queries above and calculate the total number
	 * of pages that are available based on the results per page preferences.
	 */
	$query	= "	SELECT COUNT(*) AS `total_rows`
				FROM `community_events`
				WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)."
				AND `cpage_id` = ".$db->qstr($PAGE_ID)."
				".( $PAGE_OPTIONS["moderate_posts"] == 1 ? "AND `pending_moderation` = '0'" : "")."
				AND `event_active` = '1'
				AND (`release_date` = '0' OR `release_date` <= '".time()."')
				AND (`release_until` = '0' OR `release_until` > '".time()."')
				".(isset($display_duration) && $display_duration ? "AND `event_start` BETWEEN ".$db->qstr($display_duration["start"])." AND ".$db->qstr($display_duration["end"]) : "").";";
	$result	= $db->GetRow($query);
	if ($result) {
		$total_rows	= $result["total_rows"];
	
		if ($total_rows <= $_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["pp"]) {
			$total_pages = 1;
		} elseif (($total_rows % $_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["pp"]) == 0) {
			$total_pages = (int) ($total_rows / $_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["pp"]);
		} else {
			$total_pages = (int) ($total_rows / $_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["pp"]) + 1;
		}
	} else {
		$total_rows		= 0;
		$total_pages	= 1;
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
	
	if ($total_pages > 1) {
		$pagination = new Pagination($page_current, $_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["pp"], $total_rows, COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL, replace_query());
	}
	
	/**
	 * Provides the first parameter of MySQLs LIMIT statement by calculating which row to start results from.
	 */
	$limit_parameter = (int) (($_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["pp"] * $page_current) - $_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["pp"]);
}
/**
 * Add the javascript for deleting events.
 */
if (communities_module_access($COMMUNITY_ID, $MODULE_ID, "delete")) {
	?>
	<script type="text/javascript">
		function eventDelete(id) {
			Dialog.confirm('Do you really wish to delete '+ $('event-' + id + '-title').innerHTML +' from this community?',
				{
					id:				'requestDialog',
					width:			350,
					height:			75,
					title:			'Delete Confirmation',
					className:		'medtech',
					okLabel:		'Yes',
					cancelLabel:	'No',
					closable:		'true',
					buttonClass:	'button small',
					ok:				function(win) {
										window.location = '<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?<?php echo (($page_current > 1) ? "pv=".$page_current."&" : ""); ?>action=delete&id='+id;
										return true;
									}
				}
			);
		}
	</script>
	<?php
}
?>
<script type="text/javascript">
function setDateValue(field, date) {
	timestamp = getMSFromDate(date);
	if (field.value != timestamp) {
		window.location = '<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?".(($_SERVER["QUERY_STRING"] != "") ? replace_query(array("dstamp" => false))."&" : ""); ?>dstamp='+timestamp;
	}
	return;
}
</script>
<div id="module-header">
	<?php
	if (isset($total_pages) && $total_pages > 1) {
		echo "<div id=\"pagination-links\">\n";
		echo "Pages: ".$pagination->GetPageLinks();
		echo "</div>\n";
	}
	?>
	<a href="<?php echo COMMUNITY_URL."/feeds".$COMMUNITY_URL.":".$PAGE_URL."/rss"; ?>" class="feeds rss">Subscribe to RSS</a>
	<a href="<?php echo COMMUNITY_URL."/feeds".$COMMUNITY_URL.":".$PAGE_URL."/calendar.ics"; ?>" class="feeds ics">Subscribe to Calendar</a>
	<?php if (COMMUNITY_NOTIFICATIONS_ACTIVE && $LOGGED_IN && $_SESSION["details"]["notifications"]) { ?>
		<div id="notifications-toggle" style="display: inline; padding-top: 4px;"></div>
		<script type="text/javascript">
		function promptNotifications(enabled) {
			Dialog.confirm('Do you really wish to '+ (enabled == 1 ? "stop" : "begin") +' receiving notifications for new events in this community?',
				{
					id:				'requestDialog',
					width:			350,
					height:			75,
					title:			'Notification Confirmation',
					className:		'medtech',
					okLabel:		'Yes',
					cancelLabel:	'No',
					closable:		'true',
					buttonClass:	'button small',
					destroyOnClose:	true,
					ok:				function(win) {
										new Window(	{
														id:				'resultDialog',
														width:			350,
														height:			75,
														title:			'Notification Result',
														className:		'medtech',
														okLabel:		'close',
														buttonClass:	'button small',
														resizable:		false,
														draggable:		false,
														minimizable:	false,
														maximizable:	false,
														recenterAuto:	true,
														destroyOnClose:	true,
														url:			'<?php echo ENTRADA_URL."/api/notifications.api.php?community_id=".$COMMUNITY_ID."&id=".$COMMUNITY_ID; ?>&type=event&action=edit&active='+(enabled == 1 ? '0' : '1'),
														onClose:			function () {
																			new Ajax.Updater('notifications-toggle', '<?php echo ENTRADA_URL."/api/notifications.api.php?community_id=".$COMMUNITY_ID."&id=".$COMMUNITY_ID; ?>&type=event&action=view');
																		}
													}
										).showCenter();
										return true;
									}
				}
			);
		}
		
		</script>
		<?php
		$ONLOAD[] = "new Ajax.Updater('notifications-toggle', '".ENTRADA_URL."/api/notifications.api.php?community_id=".$COMMUNITY_ID."&id=".$COMMUNITY_ID."&type=event&action=view')";
	}
	?>
</div>

<div style="padding-top: 10px; clear: both">
	<?php
	if (communities_module_access($COMMUNITY_ID, $MODULE_ID, "add")) {
		?>
		<div style="float: right">
			<ul class="page-action">
				<li><a href="<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?section=add">Add Event</a></li>
			</ul>
		</div>
		<div style="clear: both"></div>
		<?php
	}
	if ($RECORD_ID) {
		$query	= "	SELECT a.*, CONCAT_WS(' ', b.`firstname`, b.`lastname`) AS `fullname`, b.`username`
					FROM `community_events` AS a
					LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
					ON a.`proxy_id` = b.`id`
					WHERE a.`community_id` = ".$db->qstr($COMMUNITY_ID)."
					".( $PAGE_OPTIONS["moderate_posts"] == 1 ? "AND a.`pending_moderation` = '0'" : "")."
					AND a.`event_active` = '1'
					AND a.`cpage_id` = ".$db->qstr($PAGE_ID)."
					AND a.`cevent_id` = ".$db->qstr($RECORD_ID);
		$result	= $db->GetRow($query);
		if ($result) {
			$allow_to_load = true;
			if (!$COMMUNITY_ADMIN) {
				if ((!$release_date = (int) $result["release_date"]) || ($release_date <= time())) {
					if ((!$release_until = (int) $result["release_until"]) || ($release_until > time())) {
						/**
						 * You're good to go, no further checks at this time.
						 * If you need to add more checks, this is there they would go.
						 */
					} else {
						$NOTICE++;
						$NOTICESTR[]	= "This event was only accessible until <strong>".date(DEFAULT_DATE_FORMAT, $release_until)."</strong>.<br /><br />Please contact your community administrators for further assistance.";
	
						$allow_to_load	= false;
					}
				} else {
					$NOTICE++;
					$NOTICESTR[]	= "This event will not be accessible until <strong>".date(DEFAULT_DATE_FORMAT, $release_date)."</strong>.<br /><br />Please check back at this time, thank-you.";
	
					$allow_to_load	= false;
				}
			}

			if (!$allow_to_load) {
				echo display_notice();
			} else {
				$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?id=".$RECORD_ID, "title" => limit_chars($result["event_title"], 32));
	
				/**
				 * If there is time release properties, display them to the browsing users.
				 */
				if (($release_date = (int) $result["release_date"]) && ($release_date > time())) {
					$NOTICE++;
					$NOTICESTR[] = "This discussion post will not be accessible to others until <strong>".date(DEFAULT_DATE_FORMAT, $release_date)."</strong>.";
				} elseif ($release_until = (int) $result["release_until"]) {
					if ($release_until > time()) {
						$NOTICE++;
						$NOTICESTR[] = "This event will be accessible until <strong>".date(DEFAULT_DATE_FORMAT, $release_until)."</strong>.";
					} else {
						/**
						 * Only administrators or people who wrote the post will get this.
						 */
						$NOTICE++;
						$NOTICESTR[] = "This event was only accessible until <strong>".date(DEFAULT_DATE_FORMAT, $release_until)."</strong> by others.";
					}
				}
	
				if ($NOTICE) {
					echo display_notice();
				}

				$RECORD_AUTHOR = $result["proxy_id"];
				echo "<div id=\"event-".(int) $result["cevent_id"]."\" class=\"event calendar\">\n";
				echo "	<a name=\"event-".(int) $result["cevent_id"]."\"></a>\n";
				echo "<h2 id=\"event-".(int) $result["cevent_id"]."-title\">".html_encode($result["event_title"])."</h2>\n";
				echo "<div class=\"tagline\">\n";
				echo "	Released ".date("F dS, Y", $result["release_date"])." by <strong>".html_encode($result["fullname"])."</strong>";
				echo 	((communities_module_access($COMMUNITY_ID, $MODULE_ID, "edit")) ? " (<a class=\"action\" href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=edit&amp;id=".$result["cevent_id"]."\">edit</a>)" : "");
				echo 	((communities_module_access($COMMUNITY_ID, $MODULE_ID, "delete")) ? " (<a class=\"action\" href=\"javascript:eventDelete('".$result["cevent_id"]."')\">delete</a>)" : "");
				echo "</div>\n";
				echo "<br />\n";
				echo (isset($result["event_location"]) && trim($result["event_location"]) != "" ? "<span style=\"font-weight: bold;\">Location: </span>".$result["event_location"]."<br /><br />" : "");
				echo "<span style=\"font-weight: bold;\">From: </span>".date(DEFAULT_DATE_FORMAT, $result["event_start"])."<br /><span style=\"font-weight: bold;\">To: </span>".date(DEFAULT_DATE_FORMAT, $result["event_finish"])."\n";
				echo "<br />\n";
				echo "<h3>Description:</h3> ".strip_tags($result["event_description"], $ALLOWED_HTML_TAGS);
				echo "</div>";
				if ($LOGGED_IN) {
					add_statistic("community_events", "view", "cevent_id", $result["cevent_id"]);
				}
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "The event that you are looking for does not exist in this community.";

			echo display_error();
		}
	} else {
		?>
		<table style="width: 298px; height: 23px" cellspacing="0" cellpadding="0" border="0" summary="Display Duration Type">
		<tr>
			<td style="width: 22px; height: 23px"><?php echo "<a href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?".replace_query(array("dstamp" => ($display_duration["start"] - 2)))."\" title=\"Previous ".ucwords($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"])."\"><img src=\"".ENTRADA_URL."/images/cal-back.gif\" border=\"0\" width=\"22\" height=\"23\" alt=\"Previous ".ucwords($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"])."\" title=\"Previous ".ucwords($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"])."\" /></a>"; ?></td>
			<td style="width: 47px; height: 23px"><?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"] == "day") ? "<img src=\"".ENTRADA_URL."/images/cal-day-on.gif\" width=\"47\" height=\"23\" border=\"0\" alt=\"Day View\" title=\"Day View\" />" : "<a href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?".replace_query(array("dtype" => "day"))."\"><img src=\"".ENTRADA_URL."/images/cal-day-off.gif\" width=\"47\" height=\"23\" border=\"0\" alt=\"Day View\" title=\"Day View\" /></a>"); ?></td>
			<td style="width: 47px; height: 23px"><?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"] == "week") ? "<img src=\"".ENTRADA_URL."/images/cal-week-on.gif\" width=\"47\" height=\"23\" border=\"0\" alt=\"Week View\" title=\"Week View\" />" : "<a href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?".replace_query(array("dtype" => "week"))."\"><img src=\"".ENTRADA_URL."/images/cal-week-off.gif\" width=\"47\" height=\"23\" border=\"0\" alt=\"Week View\" title=\"Week View\" /></a>"); ?></td>
			<td style="width: 47px; height: 23px"><?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"] == "month") ? "<img src=\"".ENTRADA_URL."/images/cal-month-on.gif\" width=\"47\" height=\"23\" border=\"0\" alt=\"Month View\" title=\"Month View\" />" : "<a href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?".replace_query(array("dtype" => "month"))."\"><img src=\"".ENTRADA_URL."/images/cal-month-off.gif\" width=\"47\" height=\"23\" border=\"0\" alt=\"Month View\" title=\"Month View\" /></a>"); ?></td>
			<td style="width: 47px; height: 23px"><?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"] == "year") ? "<img src=\"".ENTRADA_URL."/images/cal-year-on.gif\" width=\"47\" height=\"23\" border=\"0\" alt=\"Year View\" title=\"Year View\" />" : "<a href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?".replace_query(array("dtype" => "year"))."\"><img src=\"".ENTRADA_URL."/images/cal-year-off.gif\" width=\"47\" height=\"23\" border=\"0\" alt=\"Year View\" title=\"Year View\" /></a>"); ?></td>
			<td style="width: 47px; height: 23px; border-left: 1px #9D9D9D solid"><?php echo "<a href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?".replace_query(array("dstamp" => ($display_duration["end"] + 1)))."\" title=\"Following ".ucwords($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"])."\"><img src=\"".ENTRADA_URL."/images/cal-next.gif\" border=\"0\" width=\"22\" height=\"23\" alt=\"Following ".ucwords($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"])."\" title=\"Following ".ucwords($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"])."\" /></a>"; ?></td>
			<td style="width: 33px; height: 23px; text-align: right"><a href="<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?<?php echo replace_query(array("dstamp" => time())); ?>"><img src="<?php echo ENTRADA_URL; ?>/images/cal-home.gif" width="23" height="23" alt="Reset to display current calendar <?php echo $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"]; ?>." title="Reset to display current calendar <?php echo $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"]; ?>." border="0" /></a></td>
			<td style="width: 33px; height: 23px; text-align: right"><img src="<?php echo ENTRADA_URL; ?>/images/cal-calendar.gif" width="23" height="23" alt="Show Calendar" title="Show Calendar" onclick="showCalendar('', document.getElementById('dstamp'), document.getElementById('dstamp'), '<?php echo html_encode($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"]); ?>', 'calendar-holder', 8, 8, 1)" style="cursor: pointer" id="calendar-holder" /></td>
		</tr>
		</table>
		<?php
		if ($COMMUNITY_ADMIN && ($PAGE_OPTIONS["moderate_posts"] == 1)) {
			$query		= "	SELECT COUNT(`cevent_id`)
							FROM `community_events`
							WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)."
							AND `event_active` = '1'
							AND `pending_moderation` = '1'
							AND `cpage_id` = ".$db->qstr($PAGE_ID);

			$pending_moderation = $db->GetOne($query);
			if ($pending_moderation) {
				$NOTICE++;
				$NOTICESTR[] = (($pending_moderation > 1) ? ((int)$pending_moderation)." events are" : ((int)$pending_moderation)." event is")." pending moderation. Click <a href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=moderate\">here</a> to begin moderating.";
				echo display_notice();
				$NOTICE--;
				array_pop($NOTICESTR);
			}
		}

		$query		= "	SELECT a.*, CONCAT_WS(' ', b.`firstname`, b.`lastname`) AS `fullname`, b.`username`
						FROM `community_events` AS a
						LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
						ON a.`proxy_id` = b.`id`
						WHERE a.`community_id` = ".$db->qstr($COMMUNITY_ID)."
						".( $PAGE_OPTIONS["moderate_posts"] == 1 ? "AND a.`pending_moderation` = '0'" : "")."
						AND a.`event_active` = '1'
						AND (a.`release_date` = '0' OR a.`release_date` <= '".time()."')
						AND (a.`release_until` = '0' OR a.`release_until` > '".time()."')
						".(isset($display_duration) && $display_duration ? "AND a.`event_start` BETWEEN ".$db->qstr($display_duration["start"])." AND ".$db->qstr($display_duration["end"]) : "")."
						AND a.`cpage_id` = ".$db->qstr($PAGE_ID)."
						ORDER BY a.`event_start` ASC
						LIMIT ".$limit_parameter.", ".$_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["pp"];
		$results	= $db->GetAll($query);
		if ($results) {
			$last_date 		= 0;
			$total_events	= @count($results);
			
			echo "<table class=\"calendar\" style=\"width: 99%\">\n";
			echo "<colgroup>\n";
			echo "	<col style=\"width: 30%\" />\n";
			echo "	<col style=\"width: 70%\" />\n"; 
			echo "</colgroup>\n";
			echo "<tbody>\n";
			
			foreach ($results as $key => $result) {
				if (($last_date < strtotime("00:00:00", $result["event_start"])) || ($last_date > strtotime("23:59:59", $result["event_start"]))) {
					$last_date = $result["event_start"];
					echo "<tr>\n";
					echo "	<td colspan=\"2\" style=\"border: none\"><h3 style=\"border: none\">".date("l F dS Y", $result["event_start"])."</h3></td>\n";
					echo "</tr>\n";
				}
				echo "<tr>\n";
				echo "	<td style=\"font-family: monospace\">\n";
					if (strtotime("00:00:00", $result["event_start"]) != strtotime("00:00:00", $result["event_finish"])) {
						echo date(DEFAULT_DATE_FORMAT, $result["event_start"])."<br />";
						echo date(DEFAULT_DATE_FORMAT, $result["event_finish"]);
					} else {
						echo date("H:i", $result["event_start"])." - ".date("H:i", $result["event_finish"]);
					}
					if (isset($result["event_location"]) && trim($result["event_location"]) != "") {
						echo "\n<br /><br />Location: ".$result["event_location"];
					}
				$RECORD_AUTHOR = $result["proxy_id"];
				echo "	</td>\n";
				echo "	<td style=\"padding-bottom: 15px\">\n";
				echo "		<a href=\"".COMMUNITY_RELATIVE.$COMMUNITY_URL.":".$PAGE_URL."?id=".$result["cevent_id"]."\" id=\"event-".$result["cevent_id"]."-title\">".html_encode($result["event_title"])."</a>\n";
				echo 	((communities_module_access($COMMUNITY_ID, $MODULE_ID, "edit")) ? " (<a class=\"action\" href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=edit&amp;id=".$result["cevent_id"]."\">edit</a>)" : "");
				echo 	((communities_module_access($COMMUNITY_ID, $MODULE_ID, "delete")) ? " (<a class=\"action\" href=\"javascript:eventDelete('".$result["cevent_id"]."')\">delete</a>)" : "");
				echo "		<div class=\"content-small\">".limit_chars(strip_tags(str_replace("<br />", " ", $result["event_description"])), 150)."</div>";
				echo "	</td>\n";
				echo "</tr>\n";
			}
			echo "</tbody>\n";
			echo "</table>\n";
			if ($LOGGED_IN) {
				add_statistic("community:".$COMMUNITY_ID.":events", "view", "community_id", $COMMUNITY_ID);
			}
		} else {
			$NOTICE++;
			$NOTICESTR[] = "<strong>No Events Available</strong><br />There are no calendar events on this page that take place from <strong>".date(DEFAULT_DATE_FORMAT, $display_duration["start"])."</strong> until <strong>".date(DEFAULT_DATE_FORMAT, $display_duration["end"])."</strong>.<br /><br />You may want to view a different ".$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"]." or check back later.";

			echo display_notice();
		}
	}
?>
</div>
<form action="#" method="get">
	<input type="hidden" id="dstamp" name="dstamp" value="<?php echo html_encode($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"]); ?>" />
</form>