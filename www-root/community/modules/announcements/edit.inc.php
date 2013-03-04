<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Used to edit announcements in a particular community.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
*/

if ((!defined("COMMUNITY_INCLUDED")) || (!defined("IN_ANNOUNCEMENTS"))) {
	exit;
} elseif (!$COMMUNITY_LOAD) {
	exit;
}

communities_load_rte();

$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=edit&amp;id=".$RECORD_ID, "title" => "Edit Announcement");

$HEAD[] = "<link href=\"".ENTRADA_URL."/javascript/calendar/css/xc2_default.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />";
$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/config/xc2_default.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/script/xc2_inpage.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";

echo "<h1>Edit Announcement</h1>\n";


if ($RECORD_ID) {
	$query					= "	SELECT a.* FROM `community_announcements` as a
								LEFT JOIN `communities` AS b ON a.`community_id` = b.`community_id`
								WHERE a.`community_id` = ".$db->qstr($COMMUNITY_ID)."
								AND a.`announcement_active` = '1'
								AND a.`cpage_id` = ".$db->qstr($PAGE_ID)."
								AND a.`cannouncement_id` = ".$db->qstr($RECORD_ID);
 	$announcement_record	= $db->GetRow($query);
	if ($announcement_record) {
		// Error Checking
		switch($STEP) {
			case 2 :
				/**
				 * Required field "title" / Announcement Title.
				 */
				if ((isset($_POST["announcement_title"])) && ($title = clean_input($_POST["announcement_title"], array("notags", "trim")))) {
					$PROCESSED["announcement_title"] = $title;
				} else {
					$ERROR++;
					$ERRORSTR[] = "The <strong>Announcement Title</strong> field is required.";
				}

				/**
				 * Required field "description" / Announcement Body.
				 */
				if ((isset($_POST["announcement_description"])) && ($description = clean_input($_POST["announcement_description"], array("trim", "allowedtags")))) {
					$PROCESSED["announcement_description"] = $description;
				} else {
					$ERROR++;
					$ERRORSTR[] = "The <strong>Announcement Body</strong> field is required.";
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
					$PROCESSED["updated_by"]	= $ENTRADA_USER->getID();
					if (!$COMMUNITY_ADMIN) {
						$PROCESSED["pending_moderation"] = 1;
					} else {
						$PROCESSED["pending_moderation"] = 0;
					}


					if ($db->AutoExecute("community_announcements", $PROCESSED, "UPDATE", "`cannouncement_id` = ".$db->qstr($RECORD_ID)." AND `announcement_active` = '1' AND `community_id` = ".$db->qstr($COMMUNITY_ID))) {
						if ($PROCESSED["release_date"] != $announcement_record["release_date"] && COMMUNITY_NOTIFICATIONS_ACTIVE) {
							$notification = $db->GetRow("SELECT * FROM `community_notifications` WHERE `record_id` = ".$db->qstr($RECORD_ID)." AND `type` = 'announcement'");
							if ($notification) {
								$notification["release_time"] = $PROCESSED["release_date"];
								$db->AutoExecute("community_notifications", $notification, "UPDATE", "`cnotification_id` = ".$db->qstr($notification["cnotification_id"]));
							}
						}
						$url			= COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL;

						$SUCCESS++;
						if (!$COMMUNITY_ADMIN && ($PAGE_OPTIONS["moderate_posts"] == 1)) {
							$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 15000)";
							$SUCCESSSTR[]	= "You have successfully updated this announcement, however because you are not an administrator your changes must be reviewed before the announcement will appear on the page again.<br /><br />You will now be redirected to the index; this will happen <strong>automatically</strong> in 15 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
						} else {
							$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";
							$SUCCESSSTR[]	= "You have successfully updated this announcement.<br /><br />You will now be redirected to the index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
						}
						add_statistic("community:".$COMMUNITY_ID.":announcements", "edit", "cannouncement_id", $RECORD_ID);
						communities_log_history($COMMUNITY_ID, $PAGE_ID, $RECORD_ID, "community_history_edit_announcement", 1);
					}

					if (!$SUCCESS) {
						$ERROR++;
						$ERRORSTR[] = "There was a problem updating this announcement in the system. The MEdTech Unit was informed of this error; please try again later.";

						application_log("error", "There was an error updating an announcement. Database said: ".$db->ErrorMsg());
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
				$PROCESSED = $announcement_record;
			break;
		}

		// Page Display
		switch($STEP) {
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
				<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Edit Announcement">
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
						<td colspan="3"><h2>Announcement Details</h2></td>
					</tr>
					<tr>
						<td colspan="2"><label for="announcement_title" class="form-required">Announcement Title</label></td>
						<td style="text-align: right"><input type="text" id="announcement_title" name="announcement_title" value="<?php echo ((isset($PROCESSED["announcement_title"])) ? html_encode($PROCESSED["announcement_title"]) : ""); ?>" maxlength="128" style="width:96%; float: left;" /></td>
					</tr>
					<tr>
						<td colspan="3"><label for="announcement_description" class="form-required">Announcement Body</label></td>
					</tr>
					<tr>
						<td colspan="3">
							<textarea id="announcement_description" name="announcement_description" style="width: 98%; height: 200px" cols="70" rows="10"><?php echo ((isset($PROCESSED["announcement_description"])) ? html_encode($PROCESSED["announcement_description"]) : ""); ?></textarea>
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
		$ERRORSTR[] = "The announcement record id that you have provided does not exist in the system. Please provide a valid record id to proceed.";

		echo display_error();

		application_log("error", "The provided announcement record id was invalid [".$RECORD_ID."].");
	}
} else {
	$ERROR++;
	$ERRORSTR[] = "Please provide a valid announcement record id to proceed.";

	echo display_error();

	application_log("error", "No announcement record id was provided to edit.");
}
?>