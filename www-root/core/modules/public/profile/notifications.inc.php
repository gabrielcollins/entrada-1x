<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * This file gives Entrada users the ability to update their user profile.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/
if (!defined("IN_PROFILE")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->isLoggedInAllowed('profile', 'read')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {

	$PAGE_META["title"]			= "Community Notifications";
	$PAGE_META["description"]	= "";
	$PAGE_META["keywords"]		= "";

	$PROXY_ID					= $_SESSION["details"]["id"];
	
	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/profile?section=notifications", "title" => "Community Notifications");

	$PROCESSED		= array();

	if ((is_array($_SESSION["permissions"])) && ($total_permissions = count($_SESSION["permissions"]) > 1)) {
		$sidebar_html  = "The following individual".((($total_permissions - 1) != 1) ? "s have" : " has")." given you access to their ".APPLICATION_NAME." permission levels:";
		$sidebar_html .= "<ul class=\"menu\">\n";
		foreach ($_SESSION["permissions"] as $proxy_id => $result) {
			if ($proxy_id != $_SESSION["details"]["id"]) {
				$sidebar_html .= "<li class=\"checkmark\"><strong>".html_encode($result["fullname"])."</strong><br /><span class=\"content-small\">Exp: ".(($result["expires"]) ? date("D M d/y", $result["expires"]) : "Unknown")."</span></li>\n";
			}
		}
		$sidebar_html .= "</ul>\n";

		new_sidebar_item("Delegated Permissions", $sidebar_html, "delegated-permissions", "open");
	}


	$SCRIPT[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/profile.js\"></script>";

	?>

	<h1>Community Notifications</h1>

	<?php
	if ($ERROR) {
		fade_element("out", "display-error-box");
		echo display_error();
	}

	if ($SUCCESS) {
		fade_element("out", "display-success-box");
		echo display_success();
	}

	if ($NOTICE) {
		fade_element("out", "display-notice-box");
		echo display_notice();
	}

	
	$query	= "SELECT * FROM `".AUTH_DATABASE."`.`user_data` WHERE `".AUTH_DATABASE."`.`user_data`.`id`=".$db->qstr($_SESSION["details"]["id"]);
	$result	= $db->GetRow($query);
	if ($result) {
			
			?>
			
			<?php
			if ((defined("COMMUNITY_NOTIFICATIONS_ACTIVE")) && ((bool) COMMUNITY_NOTIFICATIONS_ACTIVE)) {
				?>
					<form action="<?php echo ENTRADA_URL; ?>/profile?section=notifications" method="post">
					<input type="hidden" name="action" value="notifications-update" />
					<table style="width: 100%;" cellspacing="1" cellpadding="1" border="0" summary="My MEdTech Profile">
					<thead>
						<tr>
							<td>
								<table style="width: 100%" cellspacing="4" cellpadding="2" border="0">
									<colgroup>
										<col style="width: 3%" />
										<col style="width: 97%" />
									</colgroup>
									<tbody>
										<tr>
											<td style="vertical-align: top"><input type="radio" id="enabled-notifications" name="enable-notifications" onclick="$('notifications-toggle').show()" value="1"<?php echo ($result["notifications"] ? " checked=\"checked\"" : ""); ?> /></td>
											<td style="vertical-align: top">
												<label for="enabled-notifications"><strong>Enable</strong> Community Notifications</label><br />
												<span class="content-small">You will be able to receive notifications from communities and enable notifications for different types of content.</span>
											</td>
										</tr>
										<tr>
											<td style="vertical-align: top"><input type="radio" id="disabled-notifications" name="enable-notifications" onclick="$('notifications-toggle').hide()" value="0"<?php echo (!$result["notifications"] ? " checked=\"checked\"" : ""); ?> /></td>
											<td style="vertical-align: top">
												<label for="disabled-notifications"><strong>Disable</strong> Community Notifications</label><br />
												<span class="content-small">You will no longer receive notifications from any communities and will not be able to enable notifications for any content.</span>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
								<input type="submit" class="button" value="Save Changes" />
							</td>
						</tr>
					</tfoot>
					<tbody id="notifications-toggle"<?php echo (($result["notifications"]) ? "" : " style=\"display: none\""); ?>>
						<tr>
							<td>
								<h2>Notification Options</h2>
								Please select which notifications you would like to receive for each community you are a member of. If you are a community administrator, then you will also have the option of being notified when members join or leave your community.
								<?php
								$query = "	SELECT DISTINCT(a.`community_id`), a.`member_acl`, e.`community_title`, b.`notify_active` AS `announcements`, c.`notify_active` AS `events`, d.`notify_active` AS `polls`, f.`notify_active` AS `members`
											FROM `community_members` AS a
											LEFT JOIN `community_notify_members` AS b
											ON a.`community_id` = b.`community_id`
											AND a.`proxy_id` = b.`proxy_id`
											AND b.`notify_type` = 'announcement'
											LEFT JOIN `community_notify_members` AS c
											ON a.`community_id` = c.`community_id`
											AND a.`proxy_id` = c.`proxy_id`
											AND c.`notify_type` = 'event'
											LEFT JOIN `community_notify_members` AS d
											ON a.`community_id` = d.`community_id`
											AND a.`proxy_id` = d.`proxy_id`
											AND d.`notify_type` = 'poll'
											LEFT JOIN `communities` AS e
											ON a.`community_id` = e.`community_id`
											LEFT JOIN `community_notify_members` AS f
											ON a.`community_id` = f.`community_id`
											AND a.`proxy_id` = f.`proxy_id`
											AND f.`notify_type` = 'members'
											WHERE a.`proxy_id` = ".$db->qstr($PROXY_ID)."
											AND a.`member_active` = '1'";
								$community_notifications = $db->GetAll($query);
								if ($community_notifications) {
									?>
									<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
									<tbody>
										<tr>
											<td style="width: 50%; vertical-align: top;">
												<ul class="notify-communities">
												<?php
												$count = 0;
												foreach ($community_notifications as $key => $community) {
													$count++;
													if (($count != ((int)(round(count($community_notifications)/2))+1))) {
														?>
														<li>
															<strong><?php echo $community["community_title"]; ?></strong>
															<ul class="notifications">
																<li><label><input type="checkbox" name="notify_announcements[<?php echo $community["community_id"]; ?>]" value="1"<?php echo (!isset($community["announcements"]) || $community["announcements"] == 1 ? " checked=\"checked\"" : ""); ?> /> Announcements</label></li>
																<li><label><input type="checkbox" name="notify_events[<?php echo $community["community_id"]; ?>]" value="1" <?php echo (!isset($community["events"]) || $community["events"] == 1 ? " checked=\"checked\"" : ""); ?> /> Events</label></li>
																<li><label><input type="checkbox" name="notify_polls[<?php echo $community["community_id"]; ?>]" value="1" <?php echo (!isset($community["polls"]) || $community["polls"] == 1 ? " checked=\"checked\"" : ""); ?> /> Polls</label></li>
																<?php
																if ($community["member_acl"]) {
																	?>
																	<li><label><input type="checkbox" name="notify_members[<?php echo $community["community_id"]; ?>]" value="1" <?php echo ($community["members"] == 1 ? " checked=\"checked\"" : ""); ?> /> Members Joining / Leaving (Admin Only)</label></li>
																	<?php
																}
																?>
															</ul>
														</li>
														<?php
													} else {
														?>
															</ul>
														</td>
														<td style="width: 50%; vertical-align: top">
															<ul class="notify-communities">
																<li>
																	<strong><?php echo $community["community_title"]; ?></strong>
																	<ul class="notifications">
																		<li><label><input type="checkbox" name="notify_announcements[<?php echo $community["community_id"]; ?>]" value="1"<?php echo (!isset($community["announcements"]) || $community["announcements"] == 1 ? " checked=\"checked\"" : ""); ?> /> Announcements</label></li>
																		<li><label><input type="checkbox" name="notify_events[<?php echo $community["community_id"]; ?>]" value="1" <?php echo (!isset($community["events"]) || $community["events"] == 1 ? " checked=\"checked\"" : ""); ?> /> Events</label></li>
																		<li><label><input type="checkbox" name="notify_polls[<?php echo $community["community_id"]; ?>]" value="1" <?php echo (!isset($community["polls"]) || $community["polls"] == 1 ? " checked=\"checked\"" : ""); ?> /> Polls</label></li>
																		<?php
																		if ($community["member_acl"]) {
																			?>
																			<li><label><input type="checkbox" name="notify_members[<?php echo $community["community_id"]; ?>]" value="1" <?php echo ($community["members"] == 1 ? " checked=\"checked\"" : ""); ?> /> Members Joining / Leaving (Admin Only)</label></li>
																			<?php
																		}
																		?>
																	</ul>
																</li>
																<?php
													}
												}
												?>
												</ul>
											</td>
										</tr>
									</tbody>
									</table>
									<?php
								} else {
									$NOTICE++;
									$NOTICESTR[] = "You are not currently a member of any communities, so community e-mail notifications will not be sent to you.";

									echo display_notice();
								}
								?>
							</td>
						</tr>
					</tbody>
					</table>
					</form>
				<?php
			} else {
				add_notice("Community Notifications are not available at this time.");
				display_status_messages();
			}
	} else {
		$NOTICE++;
		$NOTICESTR[]	= "Unfortunately your ".APPLICATION_NAME." profile is not accessible at this time, please try again later.";

		echo display_notice();

		application_log("error", "A user profile was not available in the database? Database said: ".$db->ErrorMsg());
	}
}
?>