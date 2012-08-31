<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Used to add discussion posts to a particular forum in a community.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
*/

if ((!defined("COMMUNITY_INCLUDED")) || (!defined("IN_DISCUSSIONS"))) {
	exit;
} elseif (!$COMMUNITY_LOAD) {
	exit;
}

$HEAD[] = "<link href=\"".ENTRADA_URL."/javascript/calendar/css/xc2_default.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />";
$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/config/xc2_default.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/script/xc2_inpage.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
$HEAD[] = "<script type=\"text/javascript\" src=\"".COMMUNITY_URL."/javascript/discussions.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";

echo "<h1>New Discussion Post</h1>\n";

if ($RECORD_ID) {
	$query				= "SELECT * FROM `community_discussions` WHERE `cdiscussion_id` = ".$db->qstr($RECORD_ID)." AND `cpage_id` = ".$db->qstr($PAGE_ID)." AND `community_id` = ".$db->qstr($COMMUNITY_ID);
	$discussion_record	= $db->GetRow($query);
	if ($discussion_record) {
		if (discussions_module_access($RECORD_ID, "add-post")) {
			$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-forum&id=".$discussion_record["cdiscussion_id"], "title" => limit_chars($discussion_record["forum_title"], 32));
			$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=add-post&id=".$RECORD_ID, "title" => "New Discussion Post");

			communities_load_rte();

			// Error Checking
			switch($STEP) {
				case 2 :
					/**
					 * Required field "title" / Forum Title.
					 */
					if ((isset($_POST["topic_title"])) && ($title = clean_input($_POST["topic_title"], array("notags", "trim")))) {
						$PROCESSED["topic_title"] = $title;
					} else {
						$ERROR++;
						$ERRORSTR[] = "The <strong>Post Title</strong> field is required.";
					}

					/**
					 * Non-Required field "description" / Forum Description.
					 * Security Note: I guess I do not need to html_encode the data in the description because
					 * the bbcode parser takes care of this. My other option would be to html_encode, then html_decode
					 * but I think I'm going to trust the bbcode parser right now. Other scaries would be XSS in PHPMyAdmin...
					 */
					if ((isset($_POST["topic_description"])) && ($description = clean_input($_POST["topic_description"], array("trim", "allowedtags")))) {
						$PROCESSED["topic_description"] = $description;
					} else {
						$ERROR++;
						$ERRORSTR[] = "The <strong>Post Body</strong> field is required, this is the body of your post.";
					}

					if (COMMUNITY_NOTIFICATIONS_ACTIVE && $_SESSION["details"]["notifications"] && isset($_POST["enable_notifications"])) {
						$notifications = $_POST["enable_notifications"];
					} else {
						$notifications = false;
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
						$PROCESSED["cdtopic_parent"]	= 0;
						$PROCESSED["cdiscussion_id"]	= $RECORD_ID;
						$PROCESSED["community_id"]		= $COMMUNITY_ID;
						$PROCESSED["proxy_id"]			= $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"];
						$PROCESSED["topic_active"]		= 1;
						$PROCESSED["updated_date"]		= time();
						$PROCESSED["updated_by"]		= $_SESSION["details"]["id"];

						if ($db->AutoExecute("community_discussion_topics", $PROCESSED, "INSERT")) {
							if ($TOPIC_ID = $db->Insert_Id()) {
								if (isset($notifications) && COMMUNITY_NOTIFICATIONS_ACTIVE && $_SESSION["details"]["notifications"]) {
									$db->Execute("INSERT INTO `community_notify_members` (`proxy_id`, `record_id`, `community_id`, `notify_type`, `notify_active`) VALUES (".$db->qstr($PROCESSED["proxy_id"]).", ".$db->qstr($TOPIC_ID).", ".$db->qstr($COMMUNITY_ID).", 'reply', '".($notifications ? "1" : "0")."')");
								}
								$url			= COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-post&id=".$TOPIC_ID;
								$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";

								$SUCCESS++;
								$SUCCESSSTR[]	= "You have successfully created a new discussion post.<br /><br />You will now be redirected to this thread; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
								add_statistic("community:".$COMMUNITY_ID.":discussions", "post_add", "cdtopic_id", $TOPIC_ID);
								communities_log_history($COMMUNITY_ID, $PAGE_ID, $TOPIC_ID, "community_history_add_post", 1, $RECORD_ID);
							}
						}

						if (!$SUCCESS) {
							$ERROR++;
							$ERRORSTR[] = "There was a problem inserting this discussion post into the system. The MEdTech Unit was informed of this error; please try again later.";

							application_log("error", "There was an error inserting a discussion forum post. Database said: ".$db->ErrorMsg());
						}
					}

					if ($ERROR) {
						$STEP = 1;
					}
				break;
				case 1 :
				default :
					continue;
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
							if (COMMUNITY_NOTIFICATIONS_ACTIVE) {
								community_notify($COMMUNITY_ID, $TOPIC_ID, "post", COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-post&id=".$TOPIC_ID, $RECORD_ID, $PROCESSED["release_date"]);
							}
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
					<form action="<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?section=add-post&amp;id=<?php echo $RECORD_ID; ?>&amp;step=2" method="post">
					<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Add Discussion Post">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 20%" />
						<col style="width: 77%" />
					</colgroup>
					<tfoot>
						<tr>
							<td colspan="3" style="padding-top: 15px; text-align: right">
								<input type="submit" class="button" value="Save" />
							</td>
						</tr>
					</tfoot>
					<tbody>
						<tr>
							<td colspan="3"><h2>Discussion Post Details</h2></td>
						</tr>
						<tr>
							<td colspan="2"><label for="topic_title" class="form-required">Post Title</label></td>
							<td style="text-align: right"><input type="text" id="topic_title" name="topic_title" value="<?php echo ((isset($PROCESSED["topic_title"])) ? html_encode($PROCESSED["topic_title"]) : ""); ?>" maxlength="128" style="width: 95%" /></td>
						</tr>
						<tr>
							<td colspan="3"><label for="topic_description" class="form-required">Post Body</label></td>
						</tr>
						<tr>
							<td colspan="3">
								<textarea id="topic_description" name="topic_description" style="width: 100%; height: 200px" cols="68" rows="12"><?php echo ((isset($PROCESSED["topic_description"])) ? html_encode($PROCESSED["topic_description"]) : ""); ?></textarea>
							</td>
						</tr>
						<?php if (COMMUNITY_NOTIFICATIONS_ACTIVE && $_SESSION["details"]["notifications"]) { ?>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr>
							<td><input type="checkbox" name="enable_notifications" <?php echo ($notifications ? "checked=\"checked\"" : ""); ?>/></td>
							<td colspan="2"><label for="enable_notifications" class="form-nrequired">Receieve notifications when this users reply to this thread</label></td>
						</tr>
						<?php } ?>
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
			if ($ERROR) {
				echo display_error();
			}
			if ($NOTICE) {
				echo display_notice();
			}
		}
	} else {
		application_log("error", "The provided discussion forum id was invalid [".$RECORD_ID."] (Add Post).");

		header("Location: ".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL);
		exit;
	}
} else {
	application_log("error", "No discussion forum id was provided to post against. (Add Post)");

	header("Location: ".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL);
	exit;
}
?>
