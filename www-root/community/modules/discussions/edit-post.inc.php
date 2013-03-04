<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Used to edit existing discussion posts within a forum in a community. This
 * action can be called by either a community administrator, or by the user who
 * originally posted the topic.
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

echo "<h1>Edit Post</h1>\n";

if ($RECORD_ID) {
	$query			= "
					SELECT a.*, b.`forum_title`, b.`admin_notifications`, CONCAT_WS(' ', c.`firstname`, c.`lastname`) AS `poster_fullname`, c.`username` AS `poster_username`, d.`notify_active`, e.`notify_active` AS `parent_notify` 
					FROM `community_discussion_topics` AS a
					LEFT JOIN `community_discussions` AS b
					ON a.`cdiscussion_id` = b.`cdiscussion_id`
					LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS c
					ON a.`proxy_id` = c.`id`
					LEFT JOIN `community_notify_members` AS d
					ON a.`cdtopic_id` = d.`record_id`
					AND d.`community_id` = a.`community_id`
					AND d.`notify_type` = 'reply'
					AND d.`proxy_id` = ".$db->qstr($ENTRADA_USER->getID())."
					LEFT JOIN `community_notify_members` AS e
					ON a.`cdtopic_parent` = e.`record_id`
					AND e.`community_id` = a.`community_id`
					AND e.`notify_type` = 'reply'
					AND e.`proxy_id` = ".$db->qstr($ENTRADA_USER->getID())."
					WHERE a.`proxy_id` = c.`id`
					AND b.`cpage_id` = ".$db->qstr($PAGE_ID)." 
					AND a.`community_id` = ".$db->qstr($COMMUNITY_ID)."
					AND a.`cdtopic_id` = ".$db->qstr($RECORD_ID)."
					AND a.`topic_active` = '1'
					AND b.`forum_active` = '1'";
	$topic_record	= $db->GetRow($query);
	if ($topic_record) {
		if (isset($topic_record["notify_active"])) {
			$notifications = ($topic_record["notify_active"] ? true : false);
			if ($topic_record["notify_active"] != null) {
				$notify_record_exists = true;
			}
		} elseif (isset($topic_record["parent_notify"])) {
			$notifications = ($topic_record["parent_notify"] ? true : false);
			if ($topic_record["parent_notify"] != null) {
				$notify_record_exists = true;
			}
		} else {
			$notifications = false;
			$notify_record_exists = false;
		}
		if (discussion_topic_module_access($RECORD_ID, "edit-post")) {
			$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-forum&id=".$topic_record["cdiscussion_id"], "title" => limit_chars($topic_record["forum_title"], 16));
			if (!$topic_record["cdtopic_parent"]) {
				$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-post&id=".$RECORD_ID, "title" => limit_chars($topic_record["topic_title"], 16));
			} else {
				$parent_title = $db->GetOne("SELECT `topic_title` FROM `community_discussion_topics` WHERE `cdtopic_id` = ".$topic_record["cdtopic_parent"]);
				$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-post&id=".$topic_record["cdtopic_parent"], "title" => limit_chars($parent_title, 12)." Reply");
			}
			$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=edit-post&id=".$RECORD_ID, "title" => "Edit Post");

			/**
			 * This is used to determine what information is displayed to the user
			 * since this one file (edit-post) is used to edit both topic posts and replies.
			 */
			$POST_TYPE = ((!(int) $topic_record["cdtopic_parent"]) ? "post" : "reply");

			communities_load_rte();

			if ($POST_TYPE == "post") {
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
						
						/**
						 * Non-required field "anonymous" / Should posts be displayed anonymously to non-admins
						 */
						if (defined('COMMUNITY_DISCUSSIONS_ANON') && COMMUNITY_DISCUSSIONS_ANON && (isset($_POST["anonymous"])) && ((int) $_POST["anonymous"])) {
							$PROCESSED["anonymous"]	= 1;
						} else {
							$PROCESSED["anonymous"]	= 0;
						}	
						
						/**
						 * Email Notificaions.
						 */
						if(isset($_POST["enable_notifications"])) {
							$notifications = $_POST["enable_notifications"];
						} else {
							$notifications = 0;
						}																													   /**
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
							$PROCESSED["updated_date"]		= time();
							$PROCESSED["updated_by"]		= $ENTRADA_USER->getID();
							
							if ($db->AutoExecute("community_discussion_topics", $PROCESSED, "UPDATE", "`cdtopic_id` = ".$db->qstr($RECORD_ID)." AND `community_id` = ".$db->qstr($COMMUNITY_ID))) {
								if (COMMUNITY_NOTIFICATIONS_ACTIVE) {
									if ($PROCESSED["release_date"] != $topic_record["release_date"]) {
										$notification = $db->GetRow("SELECT * FROM `community_notifications` WHERE `record_id` = ".$db->qstr($RECORD_ID)." AND `type` = 'post'");
										if ($notification) {
											$notification["release_time"] = $PROCESSED["release_date"];
											$db->AutoExecute("community_notifications", $notification, "UPDATE", "`cnotification_id` = ".$db->qstr($notification["cnotification_id"]));
										}
									}
									if (isset($notifications) && $notify_record_exists && $_SESSION["details"]["notifications"] && COMMUNITY_NOTIFICATIONS_ACTIVE) {
										$db->Execute("UPDATE `community_notify_members` SET `notify_active` = '".($notifications ? "1" : "0")."' WHERE `proxy_id` = ".$db->qstr($ENTRADA_USER->getID())." AND `record_id` = ".$db->qstr($RECORD_ID)." AND `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `notify_type` = 'reply'");
									} elseif (isset($notifications) && !$notify_record_exists && COMMUNITY_NOTIFICATIONS_ACTIVE && $_SESSION["details"]["notifications"]) {
										$db->Execute("INSERT INTO `community_notify_members` (`proxy_id`, `record_id`, `community_id`, `notify_type`, `notify_active`) VALUES (".$db->qstr($ENTRADA_USER->getID()).", ".$db->qstr($RECORD_ID).", ".$db->qstr($COMMUNITY_ID).", 'reply', '".($notifications ? "1" : "0")."')");
									}
								}
								$url			= COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-post&id=".$RECORD_ID;
								$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";

								$SUCCESS++;
								$SUCCESSSTR[]	= "You have successfully updated your discussion post.<br /><br />You will now be redirected to this thread; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
								add_statistic("community_discussions", "post_edit", "cdtopic_id", $RECORD_ID);
								communities_log_history($COMMUNITY_ID, $PAGE_ID, $RECORD_ID, "community_history_edit_post", 0, $topic_record["cdiscussion_id"]);
							}

							if (!$SUCCESS) {
								$ERROR++;
								$ERRORSTR[] = "There was a problem updating this discussion post, perhaps there were no changes? The MEdTech Unit was informed of this error; please try again later.";

								application_log("error", "There was an error updating a discussion forum post. Database said: ".$db->ErrorMsg());
							}
						}

						if ($ERROR) {
							$STEP = 1;
						}
					break;
					case 1 :
					default :
						$PROCESSED = $topic_record;
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
						<form action="<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?section=edit-post&amp;id=<?php echo $RECORD_ID; ?>&amp;step=2" method="post">
						<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Edit Discussion Post">
						<colgroup>
							<col style="width: 3%" />
							<col style="width: 20%" />
							<col style="width: 77%" />
						</colgroup>
						<tfoot>
							<tr>
								<td colspan="3" style="padding-top: 15px; text-align: right">
									<input type="button" class="button" value="<?php echo $translate->_("global_button_cancel"); ?>" onclick="window.location='<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-post&id=".$RECORD_ID; ?>'" />
                                    <input type="submit" class="button" value="<?php echo $translate->_("global_button_save"); ?>" />                           
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
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<?php if (defined('COMMUNITY_DISCUSSIONS_ANON') && COMMUNITY_DISCUSSIONS_ANON) { ?>
							<tr>
								<td><input type="checkbox" name="anonymous" <?php echo (isset($PROCESSED["anonymous"]) && $PROCESSED["anonymous"] ? "checked=\"checked\"" : ""); ?> value="1"/></td>
								<td colspan="2"><label for="anonymous" class="form-nrequired">Hide name from non-administrator users</label></td>
							</tr>									
							<?php } ?>
							<?php
							if (COMMUNITY_NOTIFICATIONS_ACTIVE && $_SESSION["details"]["notifications"]) {
								?>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td><input type="checkbox" name="enable_notifications" id="enable_notifications" <?php echo ($notifications ? "checked=\"checked\"" : ""); ?>/></td>
									<td colspan="2"><label for="enable_notifications" class="form-nrequired">Receieve notifications when this users reply to this thread</label></td>
								</tr>
								<?php
							}
							?>
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
				// Error Checking
				switch($STEP) {
					case 2 :
						/**
						 * Non-Required field "description" / Forum Description.
						 * Security Note: I guess I do not need to html_encode the data in the description because
						 * the bbcode parser takes care of this. My other option would be to html_encode, then html_decode
						 * but I think I'm going to trust the bbcode parser right now. Other scaries would be XSS in PHPMyAdmin...
						 */
						if ((isset($_POST["topic_description"])) && ($description = clean_input($_POST["topic_description"], array("trim")))) {
							$PROCESSED["topic_description"] = $description;
						} else {
							$ERROR++;
							$ERRORSTR[] = "The <strong>Post Body</strong> field is required, this is your reply to the post.";
						}

						/**
						 * Non-required field "anonymous" / Should posts be displayed anonymously to non-admins
						 */
						if (defined('COMMUNITY_DISCUSSIONS_ANON') && COMMUNITY_DISCUSSIONS_ANON && (isset($_POST["anonymous"])) && ((int) $_POST["anonymous"])) {
							$PROCESSED["anonymous"]	= 1;
						} else {
							$PROCESSED["anonymous"]	= 0;
						}							

						if (COMMUNITY_NOTIFICATIONS_ACTIVE && $_SESSION["details"]["notifications"] && isset($_POST["enable_notifications"])) {
							$notifications = $_POST["enable_notifications"];
						} else {
							$notifications = false;
						}
						
						if (!$ERROR) {
							$PROCESSED["updated_date"]		= time();
							$PROCESSED["updated_by"]		= $ENTRADA_USER->getID();

							if ($db->AutoExecute("community_discussion_topics", $PROCESSED, "UPDATE", "`cdtopic_id` = ".$db->qstr($RECORD_ID)." AND `community_id` = ".$db->qstr($COMMUNITY_ID))) {
								if (COMMUNITY_NOTIFICATIONS_ACTIVE) {
									if ($PROCESSED["release_date"] != $topic_record["release_date"]) {
										$notification = $db->GetRow("SELECT * FROM `community_notifications` WHERE `record_id` = ".$db->qstr($topic_record["cdtopic_parent"])." AND `type` = 'post'");
										if ($notification) {
											$notification["release_time"] = $PROCESSED["release_date"];
											$db->AutoExecute("community_notifications", $notification, "UPDATE", "`cnotification_id` = ".$db->qstr($notification["cnotification_id"]));
										}
									}
									if (isset($notifications) && $notify_record_exists && COMMUNITY_NOTIFICATIONS_ACTIVE && $_SESSION["details"]["notifications"] && COMMUNITY_NOTIFICATIONS_ACTIVE) {
										$db->Execute("UPDATE `community_notify_members` SET `notify_active` = '".($notifications ? "1" : "0")."' WHERE `proxy_id` = ".$db->qstr($ENTRADA_USER->getID())." AND `record_id` = ".$db->qstr($topic_record["cdtopic_parent"])." AND `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `notify_type` = 'reply'");
									} elseif (isset($notifications) && !$notify_record_exists && COMMUNITY_NOTIFICATIONS_ACTIVE && $_SESSION["details"]["notifications"]) {
										$db->Execute("INSERT INTO `community_notify_members` (`proxy_id`, `record_id`, `community_id`, `notify_type`, `notify_active`) VALUES (".$db->qstr($ENTRADA_USER->getID()).", ".$db->qstr($topic_record["cdtopic_parent"]).", ".$db->qstr($COMMUNITY_ID).", 'reply', '".($notifications ? "1" : "0")."')");
									}
								}
								$url			= COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-post&id=".$topic_record["cdtopic_parent"]."#post-".$RECORD_ID;
								$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";

								$SUCCESS++;
								$SUCCESSSTR[]	= "You have successfully updated your discussion post reply.<br /><br />You will now be redirected to this thread; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
								add_statistic("community:".$COMMUNITY_ID.":discussions", "post_edit", "cdtopic_id", $RECORD_ID);
								communities_log_history($COMMUNITY_ID, $PAGE_ID, $RECORD_ID, "community_history_edit_reply", 0, $topic_record["cdtopic_parent"]);
							}

							if (!$SUCCESS) {
								$ERROR++;
								$ERRORSTR[] = "There was a problem updating this discussion post reply, perhaps there were no changes?. The MEdTech Unit was informed of this error; please try again later.";

								application_log("error", "There was an error updating a discussion post reply. Database said: ".$db->ErrorMsg());
							}
						}

						if ($ERROR) {
							$STEP = 1;
						}
					break;
					case 1 :
					default :
						$PROCESSED = $topic_record;
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
						<form action="<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?section=edit-post&amp;id=<?php echo $RECORD_ID; ?>&amp;step=2" method="post">
						<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Edit Discussion Pos">
						<tfoot>
							<tr>
								<td style="padding-top: 15px; text-align: right">
									<input type="button" class="button" value="<?php echo $translate->_("global_button_cancel"); ?>" onclick="window.location='<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-post&id=".$topic_record["cdtopic_parent"]."#post-".$RECORD_ID; ?>'" />
									<input type="submit" class="button" value="<?php echo $translate->_("global_button_save"); ?>" />                           
								</td>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td><h2>Current Reply</h2></td>
							</tr>
							<tr>
								<td>
									<textarea id="topic_description" name="topic_description" style="width: 100%; height: 200px" cols="68" rows="12"><?php echo ((isset($PROCESSED["topic_description"])) ? html_encode($PROCESSED["topic_description"]) : ""); ?></textarea>
								</td>
							</tr>
							<?php if (defined('COMMUNITY_DISCUSSIONS_ANON') && COMMUNITY_DISCUSSIONS_ANON) { ?>
							<tr>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td><input type="checkbox" name="anonymous" <?php echo (isset($PROCESSED["anonymous"]) && $PROCESSED["anonymous"] ? "checked=\"checked\"" : ""); ?> value="1"/><label for="anonymous" class="form-nrequired">Hide name from non-administrator users</label></td>
							</tr>		
							<?php } ?>
							<?php if (COMMUNITY_NOTIFICATIONS_ACTIVE && $_SESSION["details"]["notifications"]) { ?>
							<tr>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td>
									<input type="checkbox" name="enable_notifications" <?php echo ($notifications ? "checked=\"checked\"" : ""); ?>/>
									<label for="enable_notifications" class="form-nrequired">Receieve notifications when this users reply to this thread</label></td>
							</tr>
							<?php } ?>
						</tbody>
						</table>
						</form>
						<?php
						if (discussion_topic_module_access($topic_record["cdtopic_parent"], "view-post")) {
							$query	= "
									SELECT a.*, b.`forum_title`, CONCAT_WS(' ', c.`firstname`, c.`lastname`) AS `poster_fullname`, c.`username` AS `poster_username`
									FROM `community_discussion_topics` AS a
									LEFT JOIN `community_discussions` AS b
									ON a.`cdiscussion_id` = b.`cdiscussion_id`
									LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS c
									ON a.`proxy_id` = c.`id`
									WHERE a.`proxy_id` = c.`id`
									AND a.`community_id` = ".$db->qstr($COMMUNITY_ID)."
									AND b.`cpage_id` = ".$db->qstr($PAGE_ID)." 
									AND a.`cdtopic_id` = ".$db->qstr($topic_record["cdtopic_parent"])."
									AND a.`topic_active` = '1'
									AND b.`forum_active` = '1'";
							$result = $db->GetRow($query);
							if ($result) {
								?>
								<br />
								<h2>Original Post: <small><?php echo html_encode($result["topic_title"]); ?></small></h2>
								<table class="discussions posts" style="width: 100%" cellspacing="0" cellpadding="0" border="0">
								<colgroup>
									<col style="width: 30%" />
									<col style="width: 70%" />
								</colgroup>
								<tr>
									<td style="border-bottom: none; border-right: none"><?php echo get_online_status($result["proxy_id"], "image"); ?> <a href="<?php echo ENTRADA_URL."/people?profile=".html_encode($result["poster_username"]); ?>" style="font-weight: bold; text-decoration: underline"><?php echo html_encode($result["poster_fullname"]); ?></a></td>
									<td style="border-bottom: none">
										<div style="float: left">
											<span class="content-small"><strong>Posted:</strong> <?php echo date(DEFAULT_DATE_FORMAT, $result["updated_date"]); ?></span>
										</div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="content">
										<?php echo $result["topic_description"]; ?>
									</td>
								</tr>
								</table>
								<?php
							}
						}
					break;
				}
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
		application_log("error", "The provided discussion post id was invalid [".$RECORD_ID."] (Edit Post).");
		header("Location: ".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL);
		exit;
	}
} else {
	application_log("error", "No discussion post id was provided to edit. (Edit Post)");
	header("Location: ".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL);
	exit;
}
?>
