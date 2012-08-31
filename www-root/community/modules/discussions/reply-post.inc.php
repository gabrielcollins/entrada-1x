<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Used to reply to existing posts within a forum in a community.
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

echo "<h1>Reply To Post</h1>\n";

if ($RECORD_ID) {
	$query			= "
					SELECT a.*, b.`forum_title`, CONCAT_WS(' ', c.`firstname`, c.`lastname`) AS `poster_fullname`, c.`username` AS `poster_username`, d.`notify_active`
					FROM `community_discussion_topics` AS a
					LEFT JOIN `community_discussions` AS b
					ON a.`cdiscussion_id` = b.`cdiscussion_id`
					LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS c
					ON a.`proxy_id` = c.`id`
					LEFT JOIN `community_notify_members` AS d
					ON a.`cdtopic_id` = d.`record_id`
					AND d.`community_id` = a.`community_id`
					AND d.`notify_type` = 'reply'
					AND d.`proxy_id` = ".$db->qstr($_SESSION["details"]["id"])."
					WHERE a.`proxy_id` = c.`id`
					AND a.`community_id` = ".$db->qstr($COMMUNITY_ID)."
					AND b.`cpage_id` = ".$db->qstr($PAGE_ID)." 
					AND a.`cdtopic_id` = ".$db->qstr($RECORD_ID)."
					AND a.`cdtopic_parent` = '0'
					AND a.`topic_active` = '1'
					AND b.`forum_active` = '1'";
	$topic_record	= $db->GetRow($query);
	if ($topic_record) {
		if (isset($topic_record["notify_active"])) {
			$notifications = ($topic_record["notify_active"] ? true : false);
			if ($topic_record["notify_active"] != null) {
				$notify_record_exists = true;
			}
		} else {
			$notifications = false;
			$notify_record_exists = false;
		}
		if (discussions_module_access($topic_record["cdiscussion_id"], "reply-post")) {
			$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-forum&id=".$topic_record["cdiscussion_id"], "title" => limit_chars($topic_record["forum_title"], 16));
			$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-post&id=".$RECORD_ID, "title" => limit_chars($topic_record["topic_title"], 16));
			$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=reply-post&id=".$RECORD_ID, "title" => "Reply To Post");

			communities_load_rte();

			// Error Checking
			switch($STEP) {
				case 2 :
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
						$ERRORSTR[] = "The <strong>Post Body</strong> field is required, this is your reply to the post.";
					}
					
					if (COMMUNITY_NOTIFICATIONS_ACTIVE && $_SESSION["details"]["notifications"] && isset($_POST["enable_notifications"])) {
						$notifications = $_POST["enable_notifications"];
					} elseif (!isset($notifications)) {
						$notifications = false;
					}
					
					if (!$ERROR) {
						$PROCESSED["cdtopic_parent"]	= $RECORD_ID;
						$PROCESSED["cdiscussion_id"]	= $topic_record["cdiscussion_id"];
						$PROCESSED["community_id"]		= $COMMUNITY_ID;
						$PROCESSED["proxy_id"]			= $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"];
						$PROCESSED["topic_title"]		= "";
						$PROCESSED["topic_active"]		= 1;
						$PROCESSED["release_date"]		= time();
						$PROCESSED["release_until"]		= 0;
						$PROCESSED["updated_date"]		= time();
						$PROCESSED["updated_by"]		= $_SESSION["details"]["id"];

						if ($db->AutoExecute("community_discussion_topics", $PROCESSED, "INSERT")) {
							if ($TOPIC_ID = $db->Insert_Id()) {
								if ($_SESSION["details"]["notifications"] && COMMUNITY_NOTIFICATIONS_ACTIVE && isset($notifications) && $notify_record_exists) {
									$db->Execute("UPDATE `community_notify_members` SET `notify_active` = '".($notifications ? "1" : "0")."' WHERE `proxy_id` = ".$db->qstr($_SESSION["details"]["id"])." AND `record_id` = ".$db->qstr($RECORD_ID)." AND `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `notify_type` = 'reply'");
								} elseif (isset($notifications) && !$notify_record_exists && COMMUNITY_NOTIFICATIONS_ACTIVE && $_SESSION["details"]["notifications"]) {
									$db->Execute("INSERT INTO `community_notify_members` (`proxy_id`, `record_id`, `community_id`, `notify_type`, `notify_active`) VALUES (".$db->qstr($_SESSION["details"]["id"]).", ".$db->qstr($RECORD_ID).", ".$db->qstr($COMMUNITY_ID).", 'reply', '".($notifications ? "1" : "0")."')");
								}
								$url			= COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-post&id=".$RECORD_ID."#post-".$TOPIC_ID;
								$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";

								$SUCCESS++;
								$SUCCESSSTR[]	= "You have successfully replied to ".html_encode($topic_record["topic_title"]).".<br /><br />You will now be redirected back to this thread now; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
								add_statistic("community:".$COMMUNITY_ID.":discussions", "post_add", "cdtopic_id", $TOPIC_ID);
								communities_log_history($COMMUNITY_ID, $PAGE_ID, $TOPIC_ID, "community_history_add_reply", 1, $RECORD_ID);
							}
						}

						if (!$SUCCESS) {
							$ERROR++;
							$ERRORSTR[] = "There was a problem inserting this discussion post reply into the system. The MEdTech Unit was informed of this error; please try again later.";

							application_log("error", "There was an error inserting a discussion post reply. Database said: ".$db->ErrorMsg());
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
								community_notify($COMMUNITY_ID, $TOPIC_ID, "reply", COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-post&id=".$RECORD_ID, $RECORD_ID);
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
					<form action="<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?section=reply-post&amp;id=<?php echo $RECORD_ID; ?>&amp;step=2" method="post">
					<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Reply To Post">
					<tfoot>
						<tr>
							<td style="padding-top: 15px; text-align: right">
                                <input type="submit" class="button" value="<?php echo $translate->_("global_button_save"); ?>" />                        
							</td>
						</tr>
					</tfoot>
					<tbody>
						<tr>
							<td><h2>Your Reply To: <small><?php echo html_encode($topic_record["topic_title"]); ?></small></h2></td>
						</tr>
						<tr>
							<td>
								<textarea id="topic_description" name="topic_description" style="width: 100%; height: 200px" cols="68" rows="12"><?php echo ((isset($PROCESSED["topic_description"])) ? html_encode($PROCESSED["topic_description"]) : ""); ?></textarea>
							</td>
						</tr>
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
					if (discussion_topic_module_access($RECORD_ID, "view-post")) {
						?>
						<br />
						<h2>Original Post: <small><?php echo html_encode($topic_record["topic_title"]); ?></small></h2>
						<table class="discussions posts" style="width: 100%" cellspacing="0" cellpadding="0" border="0">
						<colgroup>
							<col style="width: 30%" />
							<col style="width: 70%" />
						</colgroup>
						<tr>
							<td style="border-bottom: none; border-right: none"><span class="content-small">By:</span> <a href="<?php echo ENTRADA_URL."/people?profile=".html_encode($topic_record["poster_username"]); ?>" style="font-size: 10px"><?php echo html_encode($topic_record["poster_fullname"]); ?></a></td>
							<td style="border-bottom: none">
								<div style="float: left">
									<span class="content-small"><strong>Posted:</strong> <?php echo date(DEFAULT_DATE_FORMAT, $topic_record["updated_date"]); ?></span>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="2" class="content">
								<?php echo $topic_record["topic_description"]; ?>
							</td>
						</tr>
						</table>
						<?php
					}
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
		application_log("error", "The provided discussion post id was invalid [".$RECORD_ID."] (Reply Post).");

		header("Location: ".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL);
		exit;
	}
} else {
	application_log("error", "No discussion post id was provided to reply. (Reply Post)");

	header("Location: ".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL);
	exit;
}
?>