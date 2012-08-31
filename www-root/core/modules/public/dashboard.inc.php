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
 * This is the main dashboard that people see when they log into Entrada
 * and have not requested another page or module.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if (!defined("PARENT_INCLUDED")) exit;
if (!$ENTRADA_ACL->amIAllowed("dashboard", "read")) {

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$DISPLAY_DURATION		= array();
	$notice_where_clause	= "";
	$poll_where_clause		= "";
	$PREFERENCES			= preferences_load("dashboard");

	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/tabpane/tabpane.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/rssreader.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";

	$HEAD[] = "<link href=\"".ENTRADA_RELATIVE."/css/tabpane.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />";

	$HEAD[] = "<link href=\"".ENTRADA_RELATIVE."/javascript/calendar/css/xc2_default.css\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/calendar/config/xc2_default.js\"></script>";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/calendar/script/xc2_inpage.js\"></script>";
	$HEAD[]	= "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/calendar/script/xc2_timestamp.js\"></script>";

	//$JQUERY[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/jquery/jquery.min.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
	//$JQUERY[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/jquery/jquery-ui.min.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
	$JQUERY[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/jquery/jquery.weekcalendar.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
	$JQUERY[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/jquery/jquery.qtip.min.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
	//$JQUERY[] = "<script type=\"text/javascript\">jQuery.noConflict();</script>";
	//$JQUERY[] = "<link href=\"".ENTRADA_RELATIVE."/css/jquery/jquery-ui.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
	$JQUERY[] = "<link href=\"".ENTRADA_RELATIVE."/css/jquery/jquery.weekcalendar.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";

	/**
	 * Fetch the latest feeds and links for this user.
	 */
	$dashboard_feeds = dashboard_fetch_feeds();
	$dashboard_links = dashboard_fetch_links();
	/**
	 * Display current weather conditions in the sidebar.
	 */
	$sidebar_html = display_weather();
	if ($sidebar_html != "") {
		new_sidebar_item("Weather Forecast", display_weather(), "weather", "open");
	}

	/**
	 * If user is a member of any communities, show them here.
	 */
	$query 		= "	SELECT b.`community_id`, b.`community_url`, b.`community_title`
					FROM `community_members` AS a
					LEFT JOIN `communities` AS b
					ON b.`community_id` = a.`community_id`
					WHERE a.`proxy_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])."
					AND a.`member_active` = '1'
					AND b.`community_active` = '1'
					AND b.`community_template` <> 'course'
					ORDER BY b.`community_title` ASC
					LIMIT 0, 11";
	$results	= $db->GetAll($query);
	if ($results) {
		$sidebar_html  = "<ul class=\"menu\">\n";
		foreach ($results as $key => $result) {
			if ($key < 10) {
				$sidebar_html .= "<li class=\"community\"><a href=\"".ENTRADA_URL."/community".$result["community_url"]."\">".html_encode($result["community_title"])."</a></li>\n";
			} else {
				$sidebar_html .= "<li><a href=\"".ENTRADA_URL."/communities\">more ...</a></li>\n";
				break;
			}
		}
		$sidebar_html .= "</ul>\n";
		new_sidebar_item("My Communities", $sidebar_html, "my-communities", "open");
	} else {
		$sidebar_html  = "<div style=\"text-align: center\">\n";
		$sidebar_html .= "	<a href=\"".ENTRADA_URL."/podcasts\"><img src=\"".ENTRADA_URL."/images/podcast-dashboard-image.jpg\" width=\"149\" height=\"99\" alt=\"MEdTech Podcasts\" title=\"Subscribe to our Podcast feed.\" border=\"0\"></a><br />\n";
		$sidebar_html .= "	<a href=\"".ENTRADA_URL."/podcasts\" style=\"color: #557CA3; font-size: 14px\">Podcasts Available</a>";
		$sidebar_html .= "</div>\n";
		new_sidebar_item("Podcasts in iTunes", $sidebar_html, "podcast-bar", "open");
	}

	switch ($ACTION) {
		case "read" :
			if ((isset($_POST["mark_read"])) && (is_array($_POST["mark_read"]))) {
				foreach ($_POST["mark_read"] as $notice_id) {
					if ($notice_id = (int) $notice_id) {
						add_statistic("notices", "read", "notice_id", $notice_id);
					}
				}
			}
			
			$_SERVER["QUERY_STRING"] = replace_query(array("action" => false));
		break;
		default :
			continue;
		break;
	}

	switch ($_SESSION["details"]["group"]) {
		case "alumni" :
			$rss_feed_name = "alumni";
			$notice_where_clause = "(a.`target` = 'all' OR a.`target` = 'alumni' OR a.`target` = ".$db->qstr("proxy_id:".((int) $_SESSION["details"]["id"])).")";
			$poll_where_clause = "(a.`poll_target` = 'all' OR a.`poll_target` = 'alumni')";;
			$corrected_role = "students";
		break;
		case "faculty" :
			$rss_feed_name = "faculty";
			$notice_where_clause = "(a.`target` = 'all' OR a.`target` = 'faculty' OR a.`target` = ".$db->qstr("proxy_id:".((int) $_SESSION["details"]["id"])).")";
			$poll_where_clause = "(a.`poll_target` = 'all' OR a.`poll_target` = 'faculty')";;
			$corrected_role = "faculty";
		break;
		case "medtech" :
			$rss_feed_name = "medtech";
			$notice_where_clause = "(a.`target` NOT LIKE 'proxy_id:%' OR a.`target` = ".$db->qstr("proxy_id:".((int) $_SESSION["details"]["id"])).")";
			$poll_where_clause = "(a.`poll_target` = 'all' OR a.`poll_target` = 'staff')";;
			$corrected_role = "medtech";
		break;
		case "resident" :
			$rss_feed_name = "resident";
			$notice_where_clause = "(a.`target` = 'all' OR a.`target` = 'resident' OR a.`target` = ".$db->qstr("proxy_id:".((int) $_SESSION["details"]["id"])).")";
			$poll_where_clause = "(a.`poll_target` = 'all' OR a.`poll_target` = 'resident')";;
			$corrected_role = "resident";
		break;
		case "staff" :
			$rss_feed_name = "staff";
			$notice_where_clause = "(a.`target` = 'all' OR a.`target` = 'staff' OR a.`target` = ".$db->qstr("proxy_id:".((int) $_SESSION["details"]["id"])).")";
			$poll_where_clause = "(a.`poll_target` = 'all' OR a.`poll_target` = 'staff')";;
			$corrected_role = "staff";
		break;
		case "student" :
		default :
			$cohort = groups_get_cohort($_SESSION["details"]["id"]);
			$rss_feed_name = clean_input((isset($_SESSION["details"]["grad_year"]) && $_SESSION["details"]["grad_year"] ? $_SESSION["details"]["grad_year"] : "default"), "alphanumeric");
			$notice_where_clause = "(a.`target`='cohort:".clean_input($cohort["group_id"], "alphanumeric")."' OR a.`target` = 'all' OR a.`target` = 'students' OR a.`target` = ".$db->qstr("proxy_id:".((int) $_SESSION["details"]["id"])).")";
			$poll_where_clause = "(a.`poll_target_type` = 'cohort' AND a.`poll_target`='".clean_input($cohort["group_id"], "alphanumeric")."' OR a.`poll_target` = 'all' OR a.`poll_target` = 'students')";
			$corrected_role = "students";
		break;
	}
	$notice_where_clause .= "AND (a.`organisation_id` IS NULL OR a.`organisation_id` = ".$_SESSION["details"]["organisation_id"].")";

	if (!isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"][$MODULE]["poll_id"])) {
		$query = "	SELECT a.`poll_id`
					FROM `poll_questions` AS a
					LEFT JOIN `poll_results` AS b
					ON b.`poll_id` = a.`poll_id`
					AND b.`proxy_id` = ".$db->qstr($_SESSION["details"]["id"])."
					WHERE b.`result_id` IS NULL
					AND (`poll_from` = '0' OR `poll_from` <= '".time()."')
					AND (`poll_until` = '0' OR `poll_until` >= '".time()."')
					".(($poll_where_clause) ? "AND ".$poll_where_clause : "")."
					ORDER BY RAND() LIMIT 1";
		$result	= $db->GetRow($query);
		if ($result) {
			$_SESSION[APPLICATION_IDENTIFIER]["tmp"][$MODULE]["poll_id"] = $result["poll_id"];
		} else {
			$query = "	SELECT a.`poll_id`
						FROM `poll_questions` AS a
						LEFT JOIN `poll_results` AS b
						ON b.`poll_id` = a.`poll_id`
						WHERE b.`result_id` IS NOT NULL
						AND (`poll_from` = '0' OR `poll_from` <= '".time()."')
						AND (`poll_until` = '0' OR `poll_until` >= '".time()."')
						ORDER BY RAND() LIMIT 1";
			$result	= $db->GetRow($query);
			if ($result) {
				$_SESSION[APPLICATION_IDENTIFIER]["tmp"][$MODULE]["poll_id"] = $result["poll_id"];
			} else {
				$_SESSION[APPLICATION_IDENTIFIER]["tmp"][$MODULE]["poll_id"] = 0;
			}
		}
	}

	if ($_SESSION[APPLICATION_IDENTIFIER]["tmp"][$MODULE]["poll_id"]) {
		$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/poll-js.php\"></script>\n";

		new_sidebar_item("Quick Polls", poll_display($_SESSION[APPLICATION_IDENTIFIER]["tmp"][$MODULE]["poll_id"]), "quick-poll", "open");
	}

	if ((defined("ENABLE_NOTICES")) && (ENABLE_NOTICES)) {
		$notices_to_display = array();
		$query = "	SELECT a.*, b.`statistic_id`, MAX(b.`timestamp`) AS `last_read`
					FROM `notices` AS a
					LEFT JOIN `statistics` AS b
					ON b.`module` = 'notices'
					AND b.`proxy_id` = ".$db->qstr($_SESSION["details"]["id"])."
					AND b.`action` = 'read'
					AND b.`action_field` = 'notice_id'
					AND b.`action_value` = a.`notice_id`
					LEFT JOIN `notice_audience` AS c 
					ON a.`notice_id` = c.`notice_id` 
					WHERE (
						c.`audience_type` = 'all:users'
						".($corrected_role == "medtech" ? "OR c.`audience_type` LIKE '%all%' OR c.`audience_type` = 'cohorts'" : "OR c.`audience_type` = 'all:".$corrected_role."'")."
						OR
						((
							c.`audience_type` = 'students' 
							OR c.`audience_type` = 'faculty' 
							OR c.`audience_type` = 'staff') 
							AND c.`audience_value` = ".$db->qstr($_SESSION["details"]["id"])."
						) 
						OR ((
							c.`audience_type` = 'cohorts' 
							OR c.`audience_type` = 'course_list') 
							AND c.`audience_value` IN (
								SELECT `group_id` 
								FROM `group_members` 
								WHERE `proxy_id` = ".$db->qstr($_SESSION["details"]["id"]).")
						)
					) 
					AND (a.`organisation_id` IS NULL 
					OR a.`organisation_id` = ".$db->qstr($_SESSION["details"]["organisation_id"]).") 
					AND (a.`display_from`='0' 
					OR a.`display_from` <= '".time()."') 
					AND (a.`display_until`='0' 
					OR a.`display_until` >= '".time()."') 
					AND a.`organisation_id` = ".$db->qstr($_SESSION["details"]["organisation_id"])."
					GROUP BY a.`notice_id`
					ORDER BY a.`updated_date` DESC, a.`display_until` ASC";
		
		$results = $db->GetAll($query);
		if ($results) {
			foreach ($results as $result) {
				if ((!$result["statistic_id"]) || ($result["last_read"] <= $result["updated_date"])) {
					$notices_to_display[] = $result;
				}
			}
			unset($results);
		}

		if (($notices_to_display) && ($total_notices = @count($notices_to_display))) {
			?>
			<div class="display-notice" style="color: #333333; max-height: 200px; overflow: auto">
				<div style="float: right"><a href="<?php echo ENTRADA_URL; ?>/rss/<?php echo $_SESSION["details"]["username"]; ?>.rss" target="_blank" style="color: #666666; font-size: 10px; text-decoration: none">RSS feed available</a> <a href="<?php echo ENTRADA_URL; ?>/rss/<?php echo $_SESSION["details"]["username"]; ?>.rss" target="_blank"><img src="<?php echo ENTRADA_URL; ?>/images/rss-enabled.gif" width="11" height="11" alt="RSS Icon" title="Notices are RSS enabled" border="0" /></a></div>
				<h2>New <?php echo APPLICATION_NAME; ?> Notice<?php echo (($total_notices != 1) ? "s" : ""); ?></h2>
				<form action="<?php echo ENTRADA_URL; ?>/dashboard?action=read" method="post">
					<table style="width: 97%" cellspacing="2" cellpadding="2" border="0" summary="New Notice<?php echo (($total_notices != 1) ? "s" : ""); ?>">
						<colgroup>
							<col style="width: 25%" />
							<col style="width: 75%" />
						</colgroup>
						<tfoot>
							<tr>
								<td colspan="2" style="text-align: right; padding-right: 15px">
									<input type="submit" class="button" value="Mark as Read" />
								</td>
							</tr>
						</tfoot>
						<tbody>
							<?php
							foreach ($notices_to_display as $result) {
								echo "<tr>\n";
								echo "	<td style=\"vertical-align: top; white-space: nowrap; font-size: 12px\">\n";
								echo "		<input type=\"checkbox\" name=\"mark_read[]\" id=\"notice_msg_".(int) $result["notice_id"]."\" value=\"".(int) $result["notice_id"]."\" style=\"vertical-align: middle\" /> ";
								echo "		<label for=\"notice_msg_".(int) $result["notice_id"]."\">".date(DEFAULT_DATE_FORMAT, $result["updated_date"])."</label>\n";
								echo "	</td>\n";
								echo "	<td style=\"padding-top: 3px; vertical-align: top; white-space: normal; font-size: 12px\">".$result["notice_summary"]."</td>\n";
								echo "</tr>\n";
							}
							?>
						</tbody>
					</table>
				</form>
			</div>
			<?php
		}
	}

	switch ($_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]) {
		case "medtech" :
		case "student" :
			$BREADCRUMB[] = array("url" => ENTRADA_URL, "title" => "Student Dashboard");

			/**
			 * How did this person not get assigned this already? Mak'em new.
			 */
			if (!isset($cohort) || !$cohort) {
				$query = "SELECT * 
						FROM `groups`
						WHERE `group_id` = ".$db->qstr(fetch_first_cohort());
				$cohort = $db->GetRow($query);
			}
			
			$HEAD[]	= "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"Notices\" href=\"".ENTRADA_URL."/notices/".$cohort["group_id"]."\" />";

			if (!isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"])) {
				$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"] = time();
			}


			$display_schedule_tabs	= false;

			if ($ENTRADA_ACL->amIAllowed("clerkship", "read")) {
				$query = "	SELECT a.*, c.`region_name`, d.`aschedule_id`, d.`apartment_id`, e.`rotation_title`
							FROM `".CLERKSHIP_DATABASE."`.`events` AS a
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`event_contacts` AS b
							ON b.`event_id` = a.`event_id`
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`regions` AS c
							ON c.`region_id` = a.`region_id`
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`apartment_schedule` AS d
							ON d.`event_id` = a.`event_id`
							AND d.`proxy_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])."
							AND d.`aschedule_status` = 'published'
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`global_lu_rotations` AS e
							ON e.`rotation_id` = a.`rotation_id`
							WHERE a.`event_finish` >= ".$db->qstr(strtotime("00:00:00"))."
							AND (a.`event_status` = 'published' OR a.`event_status` = 'approval')
							AND b.`econtact_type` = 'student'
							AND b.`etype_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])."
							ORDER BY a.`event_start` ASC";
				$clerkship_schedule	= $db->GetAll($query);
				if ($clerkship_schedule) {
					$display_schedule_tabs = true;
				}
			}

			if ($display_schedule_tabs) {
				?>
				<div class="tab-pane" id="clerkship-schedule">
					<div class="tab-page" style="height: auto;">
						<h2 class="tab">Clerkship Schedule</h2>
						<?php
						if ($clerkship_schedule) {
							?>
							<div class="display-notice">
								<strong>Notice:</strong> Keeping the Undergrad office informed of clerkship schedule changes is very important. This information is used to ensure you can graduate; therefore, if you see any inconsistencies, please let us know immediately: <a href="javascript:sendClerkship('<?php echo ENTRADA_URL; ?>/agent-clerkship.php')">click here</a>.
							</div>
							<h2>Remaining Clerkship Rotations</h2>
							<div style="float: right; margin-bottom: 5px">
								<div id="module-content">
									<ul class="page-action">
										<li>
											<a href="<?php echo ENTRADA_URL."/clerkship/electives?section=add";?>" class="strong-green">Add Elective</a>
										</li>
									</ul>
								</div>
							</div>
							<?php
							$query = "	SELECT `rotation_id` 
										FROM `".CLERKSHIP_DATABASE."`.`events`
										WHERE `event_id` = ".$db->qstr($clerkship_schedule[0]["event_id"]);
							$ROTATION_ID = $db->GetOne($query); 
							?>
							<div style="float: right; margin-bottom: 5px">
								<div id="module-content">
									<ul class="page-action">
										<li>
											<a href="<?php echo ENTRADA_URL."/clerkship/logbook?section=add&event=".$clerkship_schedule[0]["event_id"];?>" class="strong-green">Log Encounter</a>
										</li>
									</ul>
								</div>
							</div>
							<div style="clear: both"></div>
							<table class="tableList" cellspacing="0" summary="List of Remaining Clerkship Rotations">
								<colgroup>
									<col class="modified" />
									<col class="type" />
									<col class="title" />
									<col class="region" />
									<col class="date-smallest" />
									<col class="date-smallest" />
								</colgroup>
								<thead>
									<tr>
										<td class="modified">&nbsp;</td>
										<td class="type">Event Type</td>
										<td class="title">Rotation Name</td>
										<td class="region">Region</td>
										<td class="date-smallest">Start Date</td>
										<td class="date-smallest">Finish Date</td>
									</tr>
								</thead>
								<tbody>
								<?php
								foreach ($clerkship_schedule as $result) {
									if ((time() >= $result["event_start"]) && (time() <= $result["event_finish"])) {
										$bgcolour = "#E7ECF4";
										$is_here = true;
									} else {
										$bgcolour = "#FFFFFF";
										$is_here = false;
									}

									if ((int) $result["aschedule_id"]) {
										$apartment_available = true;
										$click_url = ENTRADA_URL."/regionaled/view?id=".$result["aschedule_id"];
									} else {
										$apartment_available = false;
										$click_url = "";
									}

									if (!isset($result["region_name"]) || $result["region_name"] == "") {
										$result_region = clerkship_get_elective_location($result["event_id"]);
										$result["region_name"] = $result_region["region_name"];
										$result["city"] = $result_region["city"];
									} else {
										$result["city"] = "";
									}

									$event_title = clean_input($result["event_title"], array("htmlbrackets", "trim"));
								
									$cssclass = "";
									$skip = false;

									if ($result["event_type"] == "elective") {
										switch ($result["event_status"]) {
											case "approval":
												$elective_word = "Pending";
												$cssclass = " class=\"in_draft\"";
												$click_url = ENTRADA_URL."/clerkship/electives?section=edit&id=".$result["event_id"];
												$skip = false;
											break;
											case "published":
												$elective_word = "Approved";
												$cssclass = " class=\"published\"";
												$click_url = ENTRADA_URL."/clerkship/electives?section=view&id=".$result["event_id"];
												$skip = false;
											break;
											case "trash":
												$elective_word = "Rejected";
												$cssclass = " class=\"rejected\"";
												$click_url = ENTRADA_URL."/clerkship/electives?section=edit&id=".$result["event_id"];
												$skip = true;
											break;
											default:
												$elective_word = "";
												$cssclass = "";
											break;
										}

										$elective = true;
									} else {
										$elective = false;
										$skip = false;
									}

									if (!$skip) {
										echo "<tr".(($is_here) && $cssclass != " class=\"in_draft\"" ? " class=\"current\"" : $cssclass).">\n";
										echo "	<td class=\"modified\">".(($apartment_available) ? "<a href=\"".$click_url."\">" : "")."<img src=\"".ENTRADA_URL."/images/".(($apartment_available) ? "housing-icon-small.gif" : "pixel.gif")."\" width=\"16\" height=\"16\" alt=\"".(($apartment_available) ? "Detailed apartment information available." : "")."\" title=\"".(($apartment_available) ? "Detailed apartment information available." : "")."\" style=\"border: 0px\" />".(($apartment_available) ? "</a>" : "")."</td>\n";
										echo "	<td class=\"type\">".(($apartment_available || $elective) ? "<a href=\"".$click_url."\" style=\"font-size: 11px\">" : "").(($elective) ? "Elective".(($elective_word != "") ? " (".$elective_word.")" : "") : "Core Rotation").(($apartment_available || $elective) ? "</a>" : "")."</td>\n";
										echo "	<td class=\"title\"><span title=\"".$event_title."\">".(($apartment_available) ? "<a href=\"".$click_url."\" style=\"font-size: 11px\">" : "").limit_chars(html_decode($event_title), 55).(($apartment_available) ? "</a>" : "")."</span></td>\n";
										echo "	<td class=\"region\">".(($apartment_available || $elective) ? "<a href=\"".$click_url."\" style=\"font-size: 11px\">" : "").html_encode((($result["city"] == "") ? limit_chars(($result["region_name"]), 30) : $result["city"])).(($apartment_available || $elective) ? "</a>" : "")."</td>\n";
										echo "	<td class=\"date-smallest\">".(($apartment_available) ? "<a href=\"".$click_url."\" style=\"font-size: 11px\">" : "").date("D M d/y", $result["event_start"]).(($apartment_available) ? "</a>" : "")."</td>\n";
										echo "	<td class=\"date-smallest\">".(($apartment_available) ? "<a href=\"".$click_url."\" style=\"font-size: 11px\">" : "").date("D M d/y", $result["event_finish"]).(($apartment_available) ? "</a>" : "")."</td>\n";
										echo "</tr>\n";
									}
								}
								?>
								</tbody>
							</table>
							<div style="margin-top: 15px; text-align: right">
								<a href="<?php echo ENTRADA_URL; ?>/clerkship" style="font-size: 11px">Click here to view your full schedule.</a>
							</div>
							<?php
						}
						?>
					</div>
					<div class="tab-page" style="height: auto">
						<h2 class="tab">Learning Event Schedule</h2>
						<?php
			}
			if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 6.") !== false) {
				echo display_error(array("Unfortunately you are using <strong>Internet Explorer 6.0</strong> as your web-browser to view this site and we are unable to display a dashboard calendar that is compatible with this browser.<br /><br />To view your learning events, please click the <a href=\"".ENTRADA_RELATIVE."/events\" style=\"font-weight: bold\">Learning Events</a> tab at the top."));
			} else {
				?>
				<script type="text/javascript">
				var year = new Date().getFullYear();
				var month = new Date().getMonth();
				var day = new Date().getDate();

				jQuery(document).ready(function() {
					jQuery('#dashboardCalendar').weekCalendar({
						date : new Date(<?php echo ((($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"]) ? $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"] : time()) * 1000); ?>),
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

							$event.find('.wc-time,.wc-title').qtip({
								content: {
									text: '<img class="throbber" src="<?php echo ENTRADA_RELATIVE; ?>/images/throbber.gif" alt="Loading..." />',
									url: '<?php echo ENTRADA_RELATIVE; ?>/api/events.api.php?id=' + calEvent.id + (calEvent.drid != 'undefined' ? '&drid=' + calEvent.drid : ''),
									title: {
										text: '<a href="<?php echo ENTRADA_RELATIVE; ?>/events?' + (calEvent.drid != 'undefined' ? 'drid=' + calEvent.drid : 'id=' + calEvent.id) + '">' + calEvent.title + '</a>',
										button: 'Close'
									}
								},
								position: {
									corner: {
										target: 'topMiddle',
										tooltip: 'topMiddle'
									},
									adjust: {
										screen: true
									}
								},
								show: {
									when: 'click',
									solo: true
								},
								hide: 'unfocus',
								style: {
									tip: true,
									border: { width: 0, radius: 4 },
									name: 'light',
									width: 485
								}
							});
						},
						eventClick : function(calEvent, $event) {
							if (calEvent.type == 2) {
								$event.find('.wc-time').animate({'backgroundColor':'#2B72D0', 'border':'1px solid #1B62C0'}, 500);
								$event.animate({'backgroundColor':'#68A1E5'}, 500);
								$event.find('.calEventUpdated' + calEvent.id).fadeOut(500);
							}
						},
						externalDates : function (calendar) {
							jQuery('#currentDateInfo').html(calendar.find('.wc-day-1').html() + ' - ' + calendar.find('.wc-day-5').html());
						},
						data : '<?php echo ENTRADA_RELATIVE; ?>/calendars/<?php echo html_encode($_SESSION["details"]["username"]); ?>.json'
					});
				});

				function setDateValue(field, date) {
					timestamp = (getMSFromDate(date) * 1000);

					if (field.value != timestamp) {
						field.value = getMSFromDate(date);
						jQuery('#dashboardCalendar').weekCalendar('gotoWeek', new Date(timestamp));
					}

					return;
				}
				</script>
				<table style="width: 100%" cellspacing="0" cellpadding="0" border="0" summary="Weekly Student Calendar">
				<tr>
					<td style="text-align: left; vertical-align: middle; white-space: nowrap">
						<table style="width: 375px; height: 23px" cellspacing="0" cellpadding="0" border="0">
						<tr>
							<td style="width: 22px; height: 23px"><img src="<?php echo ENTRADA_URL; ?>/images/cal-back.gif" width="22" height="23" alt="Previous Week" title="Previous Week" border="0" class="wc-prev" onclick="jQuery('#dashboardCalendar').weekCalendar('prevWeek');" /></td>
							<td style="width: 271px; height: 23px; background: url('<?php echo ENTRADA_URL; ?>/images/cal-table-bg.gif'); text-align: center; font-size: 10px; color: #666666">
								<div id="currentDateInfo"></div>
							</td>
							<td style="width: 22px; height: 23px"><img src="<?php echo ENTRADA_URL; ?>/images/cal-next.gif" width="22" height="23" alt="Next Week" title="Next Week" border="0" class="wc-next" onclick="jQuery('#dashboardCalendar').weekCalendar('nextWeek');" /></td>
							<td style="width: 30px; height: 23px; text-align: right"><img src="<?php echo ENTRADA_URL; ?>/images/cal-home.gif" width="23" height="23" alt="Reset to this week" title="Reset to this week" border="0" class="wc-today" onclick="jQuery('#dashboardCalendar').weekCalendar('today');" /></td>
							<td style="width: 30px; height: 23px; text-align: right"><img src="<?php echo ENTRADA_URL; ?>/images/cal-calendar.gif" width="23" height="23" alt="Show Calendar" title="Show Calendar" onclick="showCalendar('', document.getElementById('dstamp'), document.getElementById('dstamp'), '<?php echo html_encode($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"]); ?>', 'calendar-holder', 8, 8, 1)" style="cursor: pointer" id="calendar-holder" /></td>
						</tr>
						</table>
					</td>
					<td style="text-align: right; vertical-align: middle; white-space: nowrap">
						<h1 style="margin: 8px 0"><strong>My</strong> Schedule</h1>
					</td>
				</tr>
				</table>
				<div id="dashboardCalendar"></div>
				<div style="text-align: right; margin-top: 5px">
					<a href="<?php echo str_ireplace(array("https://", "http://"), "webcal://", ENTRADA_URL); ?>/calendars<?php echo ((isset($_SESSION["details"]["private_hash"])) ? "/private-".html_encode($_SESSION["details"]["private_hash"]) : ""); ?>/<?php echo html_encode($_SESSION["details"]["username"]); ?>.ics" class="feeds ics">Subscribe to Calendar</a>
				</div>
				<?php
			}
			if ($display_schedule_tabs) {
					?>
					</div>
				</div>
				<script type="text/javascript">setupAllTabs(true);</script>
				<?php
			}
			echo "<form action=\"\" method=\"get\">\n";
			echo "<input type=\"hidden\" id=\"dstamp\" name=\"dstamp\" value=\"".html_encode($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"])."\" />\n";
			echo "</form>\n";

			$sidebar_html  = "<div><img src=\"".ENTRADA_URL."/images/legend-class-event.gif\" width=\"14\" height=\"14\" alt=\"\" title=\"\" style=\"vertical-align: middle\" /> entire class event</div>\n";
			$sidebar_html .= "<div><img src=\"".ENTRADA_URL."/images/legend-individual.gif\" width=\"14\" height=\"14\" alt=\"\" title=\"\" style=\"vertical-align: middle\" /> individual learning event</div>\n";
			$sidebar_html .= "<div><img src=\"".ENTRADA_URL."/images/legend-updated.gif\" width=\"14\" height=\"14\" alt=\"\" title=\"\" style=\"vertical-align: middle\" /> recently updated event</div>\n";

			new_sidebar_item("Learning Event Legend", $sidebar_html, "event-legend", "open");
		break;
		case "resident" :
		case "faculty" :
			$BREADCRUMB[] = array("url" => ENTRADA_URL, "title" => ucwords($_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"])." Dashboard");

			/**
			 * Update requested timestamp to display.
			 * Valid: Unix timestamp
			 */
			if ((isset($_GET["dlength"])) && ($dlength = (int)	trim($_GET["dlength"])) && ($dlength >= 1) && ($dlength <= 4)) {
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dlength"] = $dlength;

				$_SERVER["QUERY_STRING"] = replace_query(array("dlength" => false));
			} else {
				if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dlength"])) {
					$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dlength"] = 2; // Defaults to this term.
				}
			}

			switch ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dlength"]) {
				case 1 :	// Last Term
					if (date("n", time()) <= 6) {
						$DISPLAY_DURATION["start"]	= mktime(0, 0, 0, 7, 1, (date("Y", time()) - 1));
						$DISPLAY_DURATION["end"]	= mktime(0, 0, 0, 12, 31, (date("Y", time()) - 1));
					} else {
						$DISPLAY_DURATION["start"]	= mktime(0, 0, 0, 1, 1, date("Y", time()));
						$DISPLAY_DURATION["end"]	= mktime(0, 0, 0, 6, 30, date("Y", time()));
					}
					break;
				case 3 :	// This Month
					$DISPLAY_DURATION["start"]		= mktime(0, 0, 0, date("n", time()), 1, date("Y", time()));
					$DISPLAY_DURATION["end"]		= mktime(0, 0, 0, date("n", time()), date("t", time()), date("Y", time()));
					break;
				case 4 :	// Next Term
					if (date("n", time()) <= 6) {
						$DISPLAY_DURATION["start"]	= mktime(0, 0, 0, 7, 1, date("Y", time()));
						$DISPLAY_DURATION["end"]	= mktime(0, 0, 0, 12, 31, date("Y", time()));
					} else {
						$DISPLAY_DURATION["start"]	= mktime(0, 0, 0, 1, 1, (date("Y", time()) + 1));
						$DISPLAY_DURATION["end"]	= mktime(0, 0, 0, 6, 30, (date("Y", time()) + 1));
					}
					break;
				case 2 :	// This Term
				default :
					if (date("n", time()) <= 6) {
						$DISPLAY_DURATION["start"]	= mktime(0, 0, 0, 1, 1, date("Y", time()));
						$DISPLAY_DURATION["end"]	= mktime(0, 0, 0, 6, 30, date("Y", time()));
					} else {
						$DISPLAY_DURATION["start"]	= mktime(0, 0, 0, 7, 1, date("Y", time()));
						$DISPLAY_DURATION["end"]	= mktime(0, 0, 0, 12, 31, date("Y", time()));
					}
					break;
			}

			$query		= "	SELECT a.*, e.`course_code`, CONCAT_WS(', ', c.`lastname`, c.`firstname`) AS `fullname`, MAX(d.`timestamp`) AS `last_visited`
							FROM `events` AS a
							JOIN `event_contacts` AS b
							ON b.`event_id` = a.`event_id`
							JOIN `".AUTH_DATABASE."`.`user_data` AS c
							ON c.`id` = b.`proxy_id`
							LEFT JOIN `statistics` AS d
							ON d.`module` = 'events'
							AND d.`proxy_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])."
							AND d.`action` = 'view'
							AND d.`action_field` = 'event_id'
							AND d.`action_value` = a.`event_id`
							JOIN `courses` AS e
							ON e.`course_id` = a.`course_id`
							WHERE (a.`event_start` BETWEEN ".$db->qstr($DISPLAY_DURATION["start"])." AND ".$db->qstr($DISPLAY_DURATION["end"]).")
							AND b.`proxy_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])."
							GROUP BY a.`event_id`
							ORDER BY a.`event_start` ASC";
			$results	= $db->GetAll($query);
			$TOTAL_ROWS	= @count($results);
			?>
			<table style="width: 100%" cellspacing="0" cellpadding="0" border="0" summary="Weekly Student Calendar">
				<tr>
					<td style="padding-bottom: 3px; text-align: left; vertical-align: middle; white-space: nowrap">
						<h1>My Teaching Events</h1>
					</td>
					<td style="padding-bottom: 3px; text-align: right; vertical-align: middle; white-space: nowrap">
						<form id="dlength_form" action="<?php echo ENTRADA_URL; ?>" method="get">
							<label for="dlength" class="content-small">Events taking place:</label>
							<select id="dlength" name="dlength" onchange="document.getElementById('dlength_form').submit()">
								<option value="1"<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dlength"] == 1) ? " selected=\"selected\"" : ""); ?>>Last Term</option>
								<option value="2"<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dlength"] == 2) ? " selected=\"selected\"" : ""); ?>>This Term</option>
								<option value="3"<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dlength"] == 3) ? " selected=\"selected\"" : ""); ?>>This Month</option>
								<option value="4"<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dlength"] == 4) ? " selected=\"selected\"" : ""); ?>>Next Term</option>
							</select>
						</form>
					</td>
				</tr>
			</table>
			<?php
			if ($results) {
				?>
				<div id="list-of-learning-events" style="max-height: 300px; overflow: auto">
					<div style="background-color: #FAFAFA; padding: 3px; border: 1px #9D9D9D solid; border-bottom: none">
						<img src="<?php echo ENTRADA_URL; ?>/images/lecture-info.gif" width="15" height="15" alt="" title="" style="vertical-align: middle" />
										<?php echo "Found ".$TOTAL_ROWS." event".(($TOTAL_ROWS != 1) ? "s" : "")." from <strong>".date("D, M jS, Y", $DISPLAY_DURATION["start"])."</strong> to <strong>".date("D, M jS, Y", $DISPLAY_DURATION["end"])."</strong>.\n"; ?>
					</div>
					<table class="tableList" cellspacing="0" summary="List of Learning Events">
						<colgroup>
							<col class="modified" />
							<col class="date" />
							<col class="course-code" />
							<col class="title" />
							<col class="attachment" />
						</colgroup>
						<thead>
							<tr>
								<td class="modified" id="colModified">&nbsp;</td>
								<td class="date sortedASC"><div class="noLink">Date &amp; Time</div></td>
								<td class="course-code">Course</td>
								<td class="title">Event Title</td>
								<td class="attachment">&nbsp;</td>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($results as $result) {
								if (((!$result["release_date"]) || ($result["release_date"] <= time())) && ((!$result["release_until"]) || ($result["release_until"] >= time()))) {
									$attachments	= attachment_check($result["event_id"]);
									$url			= ENTRADA_URL."/admin/events?section=content&id=".$result["event_id"];

									if (((int) $result["last_visited"]) && ((int) $result["last_visited"] < (int) $result["updated_date"])) {
										$is_modified = true;
										$modified++;
									} else {
										$is_modified = false;
									}

									echo "<tr id=\"event-".$result["event_id"]."\" class=\"event\">\n";
									echo "	<td class=\"modified\">".(($is_modified) ? "<img src=\"".ENTRADA_URL."/images/lecture-modified.gif\" width=\"15\" height=\"15\" alt=\"This event has been modified since your last visit on ".date(DEFAULT_DATE_FORMAT, $result["last_visited"]).".\" title=\"This event has been modified since your last visit on ".date(DEFAULT_DATE_FORMAT, $result["last_visited"]).".\" style=\"vertical-align: middle\" />" : "<img src=\"".ENTRADA_URL."/images/pixel.gif\" width=\"15\" height=\"15\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />")."</td>\n";
									echo "	<td class=\"date\"><a href=\"".$url."\">".date(DEFAULT_DATE_FORMAT, $result["event_start"])."</a></td>\n";
									echo "	<td class=\"course-code\"><a href=\"".$url."\">".html_encode($result["course_code"])."</a></td>\n";
									echo "	<td class=\"title\"><a href=\"".$url."\" title=\"Event Title: ".html_encode($result["event_title"])."\">".html_encode($result["event_title"])."</a></td>\n";
									echo "	<td class=\"attachment\">".(($attachments) ? "<img src=\"".ENTRADA_URL."/images/attachment.gif\" width=\"16\" height=\"16\" alt=\"Contains ".$attachments." attachment".(($attachments != 1) ? "s" : "")."\" title=\"Contains ".$attachments." attachment".(($attachments != 1) ? "s" : "")."\" />" : "<img src=\"".ENTRADA_URL."/images/pixel.gif\" width=\"16\" height=\"16\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />")."</td>\n";
									echo "</tr>\n";
								}
							}
							?>
						</tbody>
					</table>
				</div>
				<?php
			} else {
				?>
				<div style="padding: 10px; background-color: #FAFAFA; border: 1px #9D9D9D solid">
					There is no record of any teaching events in the system for
					<?php
					switch ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dlength"]) {
						case 1 :
							echo "<strong>last term</strong>.";
						break;
						case 3 :
							echo "<strong>this month</strong>.";
						break;
						case 4 :
							echo "<strong>next term</strong>.";
						break;
						case 2 :
						default :
							echo "<strong>this term</strong>.";
						break;
					}
					?>
					<br /><br />
					You can switch the display period by selecting a different date period in the &quot;Events taking place&quot; box above.
				</div>
				<?php
			}
			?>
			<div style="text-align: right; margin-top: 5px">
				<a href="<?php echo str_ireplace(array("https://", "http://"), "webcal://", ENTRADA_URL); ?>/calendars<?php echo ((isset($_SESSION["details"]["private_hash"])) ? "/private-".html_encode($_SESSION["details"]["private_hash"]) : ""); ?>/<?php echo html_encode($_SESSION["details"]["username"]); ?>.ics" class="feeds ics">Subscribe to Calendar</a>
			</div>
			<?php
		break;
		case "staff" :
		default :
			continue;
		break;
	}

	/**
	 * Add the dashboard links to the Helpful Links sidebar item.
	 */
	if ((is_array($dashboard_links)) && (count($dashboard_links))) {
		$sidebar_html  = "<ul class=\"menu\">";
		foreach ($dashboard_links as $link) {
			if ((trim($link["title"])) && (trim($link["url"]))) {
				$sidebar_html .= "<li class=\"link\"><a href=\"".html_encode($link["url"])."\" title=\"".(isset($link["description"]) && ($link["description"]) ? html_encode($link["description"]) : html_encode($link["title"]))."\"".(($link["target"]) ? " target=\"".html_encode($link["target"])."\"" : "").">".html_encode($link["title"])."</a></li>\n";
			}
		}
		$sidebar_html .= "</ul>";

		new_sidebar_item("Helpful Links", $sidebar_html, "helpful-links", "open");
	}

	/**
	 * Check if preferences need to be updated on the server at this point.
	 */
	preferences_update($MODULE, $PREFERENCES);
	?>
	<div class="rss-add">
		<a id="add-rss-feeds-link" href="#edit-rss-feeds" class="feeds add-rss">Add RSS Feed</a>
		<a id="edit-rss-feeds-link" href="#edit-rss-feeds" class="feeds edit-rss">Modify RSS Feeds</a>
		
		<div id="rss-edit-details" class="display-generic" style="display: none;">
			While you are in <strong>edit mode</strong> you can rearrange the feeds below by dragging them to your preferred location. You can also <a href="#edit-rss-feeds" id="rss-feed-reset">reset this page to the default RSS feeds</a> if you would like. <span id="rss-save-results">&nbsp;</span>
		</div>
		<div id="rss-add-details">
			<form id="rss-add-form">
				<table style="width: 450px;" cellspacing="0" cellpadding="2" border="0" summary="Adding Dashboard RSS Feed">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 25%" />
						<col style="width: 72%" />
					</colgroup>
					<tr>						
						<td colspan="3">
							<h2 style="margin-top: 0">Add RSS Feed</h2>
							<p>You can add your own external news feeds to your dashboard by providing both a title, and the full URL to your valid RSS feed.</p>
						</td>
					</tr>
					<tr>						
						<td id="rss-add-status" colspan="3"></td>
					</tr>
					<tr>
						<td></td>
						<td><label for="rss-add-title" class="form-required">RSS Feed Title</label></td>
						<td><input id="rss-add-title" style="width: 98%" /></td>
					</tr>
					<tr>
						<td></td>
						<td><label id="rss-add-url-label" for="rss-add-url" class="form-required">RSS Feed URL</label></td>
						<td><input id="rss-add-url" style="width: 98%" value="http://" /></td>
					</tr>
					<tr>
						<td colspan="3">
							<table style="margin-top: 15px; width: 100%" cellspacing="0" cellpadding="0" border="0">
								<tr>
									<td style="width: 25%; text-align: left">
										<input type="button" class="button" value="Cancel" id="add-rss-feeds-close-link"/>
									</td>
									<td style="width: 75%; text-align: right; vertical-align: middle">
										<input type="submit" id="rss-add-button" value="Add">
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</form>
		</div>
	</div>
	<script type="text/javascript">
		var CROSS_DOMAIN_PROXY_URL = "<?php echo ENTRADA_URL."/serve-remote-feed.php"; ?>";
		var SPINNER_URL = "<?php echo ENTRADA_URL."/images/loading.gif" ?>";
		var DASHBOARD_API_URL = "<?php echo ENTRADA_URL."/api/dashboard.api.php"; ?>";
		var SUCCESS_IMAGE_URL = "<?php echo ENTRADA_URL."/images/question-correct.gif"; ?>";
		var ERROR_IMAGE_URL = "<?php echo ENTRADA_URL."/images/question-correct.gif"; ?>";
	</script>
	<div id="dashboard-syndicated-content" style="width: 750px">
		<ul id="rss-list-1" class="rss-list first">
			
			<?php
			if ((is_array($dashboard_feeds)) && (count($dashboard_feeds))) {
				$list_2 = false;
				if (!isset($_SESSION[APPLICATION_IDENTIFIER]["dashboard"]["feed_break"]) || $_SESSION[APPLICATION_IDENTIFIER]["dashboard"]["feed_break"] < 0) {
					$break = count($dashboard_feeds)/2;
				} else {
					$break = $_SESSION[APPLICATION_IDENTIFIER]["dashboard"]["feed_break"];
				}

				for ($i = 0; $i < count($dashboard_feeds); $i++) {
					
					if ($i >= $break && !$list_2) {
						$list_2 = true;
						echo "</ul>
						<ul id=\"rss-list-2\" class=\"rss-list\">\n";
					}
					
					$feed = $dashboard_feeds[$i];
					echo "<li> \n";
					echo "<h2 class=\"rss-title\"><a href=\"".$feed["url"]."\" title=\"".$feed["title"]."\" target=\"_blank\">".$feed["title"]."</a></h2>\n";
					echo "<div class=\"rss-content\" data-feedurl=\"".$feed["url"]."\"></div>\n";

					if (isset($feed["removable"]) && $feed["removable"] == true) {
						echo "<a href=\"#\" class=\"rss-remove-link\">Remove This Feed</a>\n";
					}

					echo "</li>\n";
				}
				if (!$list_2) {
					$list_2 = true;
					echo "</ul>
					<ul id=\"rss-list-2\" class=\"rss-list\">\n";
				}
			}
			?>
		</ul>
		<div class="clear"></div>
	</div>
	<?php
}