<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Used to edit existing discussion forums within a community. This action is
 * only available to community administrators.
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

echo "<h1>Edit Discussion Forum</h1>\n";

if ($RECORD_ID) {
	$query				= "SELECT * FROM `community_discussions` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `cpage_id` = ".$db->qstr($PAGE_ID)." AND `cdiscussion_id` = ".$db->qstr($RECORD_ID);
	$discussion_record	= $db->GetRow($query);
	if ($discussion_record) {
		if ((int) $discussion_record["forum_active"]) {
			$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-forum&id=".$RECORD_ID, "title" => limit_chars($discussion_record["forum_title"], 32));
			$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=edit-forum&amp;id=".$RECORD_ID, "title" => "Edit Discussion Forum");

			// Error Checking
			switch($STEP) {
				case 2 :
					/**
					 * Required field "title" / Forum Title.
					 */
					if ((isset($_POST["forum_title"])) && ($title = clean_input($_POST["forum_title"], array("notags", "trim")))) {
						$PROCESSED["forum_title"] = $title;
					} else {
						$ERROR++;
						$ERRORSTR[] = "The <strong>Forum Title</strong> field is required.";
					}

					/**
					 * Non-Required field "description" / Forum Description.
					 */
					if ((isset($_POST["forum_description"])) && ($description = clean_input($_POST["forum_description"], array("notags", "trim")))) {
						$PROCESSED["forum_description"] = $description;
					} else {
						$PROCESSED["forum_description"] = "";
					}

					/**
					 * Permission checking for member access.
					 */
					if ((isset($_POST["allow_member_read"])) && (clean_input($_POST["allow_member_read"], array("int")) == 1)) {
						$PROCESSED["allow_member_read"]	= 1;
					} else {
						$PROCESSED["allow_member_read"]	= 0;
					}
					if ((isset($_POST["allow_member_post"])) && (clean_input($_POST["allow_member_post"], array("int")) == 1)) {
						$PROCESSED["allow_member_post"]	= 1;
					} else {
						$PROCESSED["allow_member_post"]	= 0;
					}
					if ((isset($_POST["allow_member_reply"])) && (clean_input($_POST["allow_member_reply"], array("int")) == 1)) {
						$PROCESSED["allow_member_reply"]	= 1;
					} else {
						$PROCESSED["allow_member_reply"]	= 0;
					}

					/**
					 * Permission checking for troll access.
					 * This can only be done if the community_registration is set to "Open Community"
					 */
					if (!(int) $community_details["community_registration"]) {
						if ((isset($_POST["allow_troll_read"])) && (clean_input($_POST["allow_troll_read"], array("int")) == 1)) {
							$PROCESSED["allow_troll_read"]	= 1;
						} else {
							$PROCESSED["allow_troll_read"]	= 0;
						}
						if ((isset($_POST["allow_troll_post"])) && (clean_input($_POST["allow_troll_post"], array("int")) == 1)) {
							$PROCESSED["allow_troll_post"]	= 1;
						} else {
							$PROCESSED["allow_troll_post"]	= 0;
						}
						if ((isset($_POST["allow_troll_reply"])) && (clean_input($_POST["allow_troll_reply"], array("int")) == 1)) {
							$PROCESSED["allow_troll_reply"]	= 1;
						} else {
							$PROCESSED["allow_troll_reply"]	= 0;
						}
					} else {
						$PROCESSED["allow_troll_read"]		= 0;
						$PROCESSED["allow_troll_post"]		= 0;
						$PROCESSED["allow_troll_reply"]		= 0;
					}

					/**
					 * Permission checking for public access.
					 * This can only be done if the community_protected is set to "Public Community"
					 */
					if (!(int) $community_details["community_protected"]) {
						if ((isset($_POST["allow_public_read"])) && (clean_input($_POST["allow_public_read"], array("int")) == 1)) {
							$PROCESSED["allow_public_read"]	= 1;
						} else {
							$PROCESSED["allow_public_read"]	= 0;
						}
						$PROCESSED["allow_public_post"]		= 0;
						$PROCESSED["allow_public_reply"]	= 0;
					} else {
						$PROCESSED["allow_public_read"]		= 0;
						$PROCESSED["allow_public_post"]		= 0;
						$PROCESSED["allow_public_reply"]	= 0;
					}

					/**
					 * Email Notificaions.
					 */
					if(isset($_POST["admin_notifications"])) {
						$PROCESSED["admin_notifications"] = $_POST["admin_notifications"];
					} elseif(isset($_POST["admin_notify"]) || isset($_POST["member_notify"])) {
						$PROCESSED["admin_notifications"] = $_POST["admin_notify"] + $_POST["member_notify"];
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
						$PROCESSED["updated_date"]	= time();
						$PROCESSED["updated_by"]	= $ENTRADA_USER->getID();

						if ($db->AutoExecute("community_discussions", $PROCESSED, "UPDATE", "`community_id` = ".$db->qstr($COMMUNITY_ID)." AND `cdiscussion_id` = ".$db->qstr($RECORD_ID))) {
							$url			= COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL;
							$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";

							$SUCCESS++;
							$SUCCESSSTR[]	= "You have successfully updated the <strong>".html_encode($PROCESSED["forum_title"])."</strong> discussion forum.<br /><br />You will now be redirected to the index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
							add_statistic("community:".$COMMUNITY_ID.":discussions", "forum_edit", "cdiscussion_id", $RECORD_ID);
							communities_log_history($COMMUNITY_ID, $PAGE_ID, $RECORD_ID, "community_history_edit_forum", 1);
						}

						if (!$SUCCESS) {
							$ERROR++;
							$ERRORSTR[] = "There was a problem inserting this forum into the system. The MEdTech Unit was informed of this error; please try again later.";

							application_log("error", "There was an error inserting a discussion forum. Database said: ".$db->ErrorMsg());
						}
					}

					if ($ERROR) {
						$STEP = 1;
					}
				break;
				case 1 :
				default :
					$PROCESSED = $discussion_record;
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
					<form action="<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?section=edit-forum&amp;id=<?php echo $RECORD_ID; ?>&amp;step=2" method="post">
					<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Edit Discussion Forum">
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
							<td colspan="3"><h2>Forum Details</h2></td>
						</tr>
						<tr>
							<td colspan="2"><label for="forum_title" class="form-required">Forum Title</label></td>
							<td style="text-align: right"><input type="text" id="forum_title" name="forum_title" value="<?php echo ((isset($PROCESSED["forum_title"])) ? html_encode($PROCESSED["forum_title"]) : ""); ?>" maxlength="64" style="width: 95%" /></td>
						</tr>
						<tr>
							<td colspan="2" style="vertical-align: top !important"><label for="forum_description" class="form-nrequired">Forum Description</label></td>
							<td style="text-align: right; vertical-align: top">
								<textarea id="forum_description" name="forum_description" style="width: 95%; height: 60px" cols="50" rows="5"><?php echo ((isset($PROCESSED["forum_description"])) ? html_encode($PROCESSED["forum_description"]) : ""); ?></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="3"><h2>Forum Permissions</h2></td>
						</tr>
						<tr>
							<td colspan="3">
								<table class="permissions" style="width: 100%" cellspacing="0" cellpadding="0" border="0">
								<colgroup>
									<col style="width: 40%" />
									<col style="width: 20%" />
									<col style="width: 20%" />
									<col style="width: 20%" />
								</colgroup>
								<thead>
									<tr>
										<td>Group</td>
										<td style="border-left: none">View Forum</td>
										<td style="border-left: none">Write New Posts</td>
										<td style="border-left: none">Reply To Posts</td>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td class="left"><strong>Community Administrators</strong></td>
										<td class="on"><input type="checkbox" id="allow_admin_read" name="allow_admin_read" value="1" checked="checked" onclick="this.checked = true" /></td>
										<td><input type="checkbox" id="allow_admin_post" name="allow_admin_post" value="1" checked="checked" onclick="this.checked = true" /></td>
										<td class="on"><input type="checkbox" id="allow_admin_reply" name="allow_admin_reply" value="1" checked="checked" onclick="this.checked = true" /></td>
									</tr>
									<tr>
										<td class="left"><strong>Community Members</strong></td>
										<td class="on"><input type="checkbox" id="allow_member_read" name="allow_member_read" value="1"<?php echo (((!isset($PROCESSED["allow_member_read"])) || ((isset($PROCESSED["allow_member_read"])) && ($PROCESSED["allow_member_read"] == 1))) ? " checked=\"checked\"" : ""); ?> /></td>
										<td><input type="checkbox" id="allow_member_post" name="allow_member_post" value="1"<?php echo (((!isset($PROCESSED["allow_member_post"])) || ((isset($PROCESSED["allow_member_post"])) && ($PROCESSED["allow_member_post"] == 1))) ? " checked=\"checked\"" : ""); ?> /></td>
										<td class="on"><input type="checkbox" id="allow_member_reply" name="allow_member_reply" value="1"<?php echo (((!isset($PROCESSED["allow_member_reply"])) || ((isset($PROCESSED["allow_member_reply"])) && ($PROCESSED["allow_member_reply"] == 1))) ? " checked=\"checked\"" : ""); ?> /></td>
									</tr>
									<?php if (!(int) $community_details["community_registration"]) :  ?>
									<tr>
										<td class="left"><strong>Browsing Non-Members</strong></td>
										<td class="on"><input type="checkbox" id="allow_troll_read" name="allow_troll_read" value="1"<?php echo (((!isset($PROCESSED["allow_troll_read"])) || ((isset($PROCESSED["allow_troll_read"])) && ($PROCESSED["allow_troll_read"] == 1))) ? " checked=\"checked\"" : ""); ?> /></td>
										<td><input type="checkbox" id="allow_troll_post" name="allow_troll_post" value="1"<?php echo (((isset($PROCESSED["allow_troll_post"])) && ($PROCESSED["allow_troll_post"] == 1)) ? " checked=\"checked\"" : ""); ?> /></td>
										<td class="on"><input type="checkbox" id="allow_troll_reply" name="allow_troll_reply" value="1"<?php echo (((isset($PROCESSED["allow_troll_reply"])) && ($PROCESSED["allow_troll_reply"] == 1)) ? " checked=\"checked\"" : ""); ?> /></td>
									</tr>
									<?php endif; ?>
									<?php if (!(int) $community_details["community_protected"]) :  ?>
									<tr>
										<td class="left"><strong>Non-Authenticated / Public Users</strong></td>
										<td class="on"><input type="checkbox" id="allow_public_read" name="allow_public_read" value="1"<?php echo (((isset($PROCESSED["allow_public_read"])) && ($PROCESSED["allow_public_read"] == 1)) ? " checked=\"checked\"" : ""); ?> /></td>
										<td><input type="checkbox" id="allow_public_post" name="allow_public_post" value="0" onclick="noPublic(this)" /></td>
										<td class="on"><input type="checkbox" id="allow_public_reply" name="allow_public_reply" value="0" onclick="noPublic(this)" /></td>
									</tr>
									<?php endif; ?>
								</tbody>
								</table>
							</td>
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
			$NOTICE++;
			$NOTICESTR[] = "The discussion forum that you are trying to edit was deactivated <strong>".date(DEFAULT_DATE_FORMAT, $discussion_record["updated_date"])."</strong> by <strong>".html_encode(get_account_data("firstlast", $discussion_record["updated_by"]))."</strong>.<br /><br />If there has been a mistake or you have questions relating to this issue please contact the MEdTech Unit directly.";

			echo display_notice();

			application_log("error", "The discussion forum record id [".$RECORD_ID."] is deactivated; however, ".$_SESSION["details"]["firstname"]." ".$_SESSION["details"]["lastname"]." [".$ENTRADA_USER->getID()."] has tried to edit it.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "The discussion forum id that you have provided does not exist in the system. Please provide a valid record id to proceed.";

		echo display_error();

		application_log("error", "The provided discussion forum id was invalid [".$RECORD_ID."] (Edit Forum).");
	}
} else {
	$ERROR++;
	$ERRORSTR[] = "Please provide a valid discussion forum id to proceed.";

	echo display_error();

	application_log("error", "No discussion forum id was provided to edit. (Edit Forum)");
}
?>
