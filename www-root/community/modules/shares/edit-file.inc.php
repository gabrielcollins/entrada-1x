<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Used to edit existing files in a community. This action can be called by
 * either the user who originally uploaded the file or by any community
 * administrator.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
*/

if ((!defined("COMMUNITY_INCLUDED")) || (!defined("IN_SHARES"))) {
	exit;
} elseif (!$COMMUNITY_LOAD) {
	exit;
}

$HEAD[] = "<link href=\"".ENTRADA_URL."/javascript/calendar/css/xc2_default.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />";
$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/config/xc2_default.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/script/xc2_inpage.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
$HEAD[] = "<script type=\"text/javascript\" src=\"".COMMUNITY_URL."/javascript/shares.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";

echo "<h1>Edit File</h1>\n";

if ($RECORD_ID) {
	$query			= "
					SELECT a.*, b.`folder_title`, b.`admin_notifications`
					FROM `community_share_files` AS a
					LEFT JOIN `community_shares` AS b
					ON a.`cshare_id` = b.`cshare_id`
					WHERE a.`community_id` = ".$db->qstr($COMMUNITY_ID)."
					AND a.`csfile_id` = ".$db->qstr($RECORD_ID)."
					AND b.`cpage_id` = ".$db->qstr($PAGE_ID)."
					AND a.`file_active` = '1'
					AND b.`folder_active` = '1'";
	$file_record	= $db->GetRow($query);
	if ($file_record) {
		if ((int) $file_record["file_active"]) {
			if (shares_file_module_access($RECORD_ID, "edit-file")) {
				$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-folder&id=".$file_record["cshare_id"], "title" => limit_chars($file_record["folder_title"], 32));
				$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-file&id=".$RECORD_ID, "title" => limit_chars($file_record["file_title"], 32));
				$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=edit-file&amp;id=".$RECORD_ID, "title" => "Edit File");

				// Error Checking
				switch($STEP) {
					case 2 :
						/**
						 * Required field "title" / File Title.
						 */
						if ((isset($_POST["file_title"])) && ($title = clean_input($_POST["file_title"], array("notags", "trim")))) {
							$PROCESSED["file_title"] = $title;
						} elseif ((isset($PROCESSED["file_filename"])) && ($PROCESSED["file_filename"])) {
							$PROCESSED["file_title"] = $PROCESSED["file_filename"];
						} else {
							$ERROR++;
							$ERRORSTR[] = "The <strong>File Title</strong> field is required.";
						}

						/**
						 * Non-Required field "description" / File Description.
						 *
						 */
						if ((isset($_POST["file_description"])) && ($description = clean_input($_POST["file_description"], array("notags", "trim")))) {
							$PROCESSED["file_description"] = $description;
						} else {
							$PROCESSED["file_description"] = "";
						}

						/**
						 * Non-Required field "access_method" / View Method.
						 */
						if ((isset($_POST["access_method"])) && (clean_input($_POST["access_method"], array("int")) == 1)) {
							$PROCESSED["access_method"]	= 1;
						} else {
							$PROCESSED["access_method"]	= 0;
						}
					
						/**
						 * Permission checking for member access.
						 */
						if ((isset($_POST["allow_member_revision"])) && (clean_input($_POST["allow_member_revision"], array("int")) == 1)) {
							$PROCESSED["allow_member_revision"]	= 1;
						} else {
							$PROCESSED["allow_member_revision"]	= 0;
						}

						/**
						 * Permission checking for troll access.
						 * This can only be done if the community_registration is set to "Open Community"
						 */
						if (!(int) $community_details["community_registration"]) {
							if ((isset($_POST["allow_troll_revision"])) && (clean_input($_POST["allow_troll_revision"], array("int")) == 1)) {
								$PROCESSED["allow_troll_revision"]	= 1;
							} else {
								$PROCESSED["allow_troll_revision"]	= 0;
							}
						} else {
							$PROCESSED["allow_troll_revision"]		= 0;
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
							$PROCESSED["updated_date"]		= time();
							$PROCESSED["updated_by"]		= $_SESSION["details"]["id"];

							if ($db->AutoExecute("community_share_files", $PROCESSED, "UPDATE", "`community_id` = ".$db->qstr($COMMUNITY_ID)." AND `csfile_id` = ".$db->qstr($RECORD_ID))) {
								if (COMMUNITY_NOTIFICATIONS_ACTIVE) {
									if ($PROCESSED["release_date"] != $file_record["release_date"]) {
										$notification = $db->GetRow("SELECT * FROM `community_notifications` WHERE `record_id` = ".$db->qstr($RECORD_ID)." AND `type` = 'file'");
										if ($notification) {
											$notification["release_time"] = $PROCESSED["release_date"];
											$db->AutoExecute("community_notifications", $notification, "UPDATE", "`cnotification_id` = ".$db->qstr($notification["cnotification_id"]));
										}
									}
								}
								$url			= COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-file&id=".$RECORD_ID;
								$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";

								$SUCCESS++;
								$SUCCESSSTR[]	= "You have successfully updated this file.<br /><br />You will now be redirected to this file page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
								add_statistic("community:".$COMMUNITY_ID.":shares", "file_edit", "csfile_id", $RECORD_ID);
								communities_log_history($COMMUNITY_ID, $PAGE_ID, $RECORD_ID, "community_history_edit_file", 1, $file_record["cshare_id"]);
							}
						}

						if ($ERROR) {
							$STEP = 1;
						}
					break;
					case 1 :
					default :
						$PROCESSED = $file_record;
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
						<form id="upload-file-form" action="<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?section=edit-file&amp;id=<?php echo $RECORD_ID; ?>&amp;step=2" method="post" enctype="multipart/form-data">
						<input type="hidden" name="MAX_UPLOAD_FILESIZE" value="<?php echo $VALID_MAX_FILESIZE; ?>" />
						<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Edit File">
						<colgroup>
							<col style="width: 3%" />
							<col style="width: 20%" />
							<col style="width: 77%" />
						</colgroup>
						<tfoot>
							<tr>
								<td colspan="3" style="padding-top: 15px; text-align: right">
									<div id="display-upload-button">
										<input type="button" class="button" value="Save" onclick="uploadFile()" />
									</div>
								</td>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td colspan="3"><h2>File Details</h2></td>
							</tr>
							<tr>
								<td colspan="2"><label for="file_title" class="form-required">File Title</label></td>
								<td><input type="text" id="file_title" name="file_title" value="<?php echo ((isset($PROCESSED["file_title"])) ? html_encode($PROCESSED["file_title"]) : ""); ?>" maxlength="64" style="width: 95%" /></td>
							</tr>
							<tr>
								<td colspan="2" style="vertical-align: top"><label for="file_description" class="form-nrequired">File Description</label></td>
								<td style="vertical-align: top">
									<textarea id="file_description" name="file_description" style="width: 95%; height: 60px" cols="50" rows="5"><?php echo ((isset($PROCESSED["file_description"])) ? html_encode($PROCESSED["file_description"]) : ""); ?></textarea>
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="2" style="vertical-align: top"><label for="access_method" class="form-nrequired">Access Method</label></td>
								<td>
									<input type="radio" id="access_method_0" name="access_method" value="0" style="vertical-align: middle"<?php echo (((!isset($PROCESSED["access_method"])) || ((isset($PROCESSED["access_method"])) && (!(int) $PROCESSED["access_method"]))) ? " checked=\"checked\"" : ""); ?> /> <label for="access_method_0" style="vertical-align: middle" class="content-small">Download this file to their computer first, then open it.</label><br />
									<input type="radio" id="access_method_1" name="access_method" value="1" style="vertical-align: middle"<?php echo (((isset($PROCESSED["access_method"])) && ((int) $PROCESSED["access_method"])) ? " checked=\"checked\"" : ""); ?> /> <label for="access_method_1" style="vertical-align: middle" class="content-small">Attempt to view it directly in the web-browser.</label><br />
								</td>
							</tr>
							<tr>
								<td colspan="3" style="padding-top: 15px">
									<div class="content-small">
										<strong>Notice:</strong> If you are trying to replace the file that users download, you need to click <a href="<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?section=add-revision&id=<?php echo $RECORD_ID; ?>" style="font-size: 11px">Upload Revised File</a>.
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="3"><h2>File Permissions</h2></td>
							</tr>
							<tr>
								<td><input type="checkbox" id="allow_member_revision" name="allow_member_revision" value="1"<?php echo (((isset($PROCESSED["allow_member_revision"])) && ($PROCESSED["allow_member_revision"] == 1)) ? " checked=\"checked\"" : ""); ?> /></td>
								<td colspan="2"><label for="allow_member_revision" class="form-nrequired">Allow <strong>other community members</strong> to upload newer versions of this file.</label></td>
							</tr>
							<?php if (!(int) $community_details["community_registration"]) :  ?>
							<tr>
								<td><input type="checkbox" id="allow_troll_revision" name="allow_troll_revision" value="1"<?php echo (((isset($PROCESSED["allow_troll_revision"])) && ($PROCESSED["allow_troll_revision"] == 1)) ? " checked=\"checked\"" : ""); ?> /></td>
								<td colspan="2"><label for="allow_troll_revision" class="form-nrequired">Allow <strong>non-community members</strong> to upload newer versions of this file.</label></td>
							</tr>
							<?php endif; ?>
							<tr>
								<td colspan="3"><h2>Time Release Options</h2></td>
							</tr>
							<?php echo generate_calendars("release", "", true, true, ((isset($PROCESSED["release_date"])) ? $PROCESSED["release_date"] : time()), true, false, ((isset($PROCESSED["release_until"])) ? $PROCESSED["release_until"] : 0)); ?>
						</tbody>
						</table>
						</form>
						<div id="display-upload-status" style="display: none">
							<div style="text-align: left; background-color: #EEEEEE; border: 1px #666666 solid; padding: 10px">
								<div style="color: #003366; font-size: 18px; font-weight: bold">
									<img src="<?php echo ENTRADA_URL; ?>/images/loading.gif" width="32" height="32" alt="File Uploading" title="Please wait while this file is being uploaded." style="vertical-align: middle" /> Please Wait: this file is being uploaded.
								</div>
								<br /><br />
								This can take time depending on your connection speed and the filesize.
							</div>
						</div>
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
			$NOTICE++;
			$NOTICESTR[] = "The file that you are trying to edit was deactivated <strong>".date(DEFAULT_DATE_FORMAT, $file_record["updated_date"])."</strong> by <strong>".html_encode(get_account_data("firstlast", $file_record["updated_by"]))."</strong>.<br /><br />If there has been a mistake or you have questions relating to this issue please contact the MEdTech Unit directly.";

			echo display_notice();

			application_log("error", "The file record id [".$RECORD_ID."] is deactivated; however, ".$_SESSION["details"]["firstname"]." ".$_SESSION["details"]["lastname"]." [".$_SESSION["details"]["id"]."] has tried to edit it.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "The file id that you have provided does not exist in the system. Please provide a valid record id to proceed.";

		echo display_error();

		application_log("error", "The provided file id was invalid [".$RECORD_ID."] (Edit File).");
	}
} else {
	$ERROR++;
	$ERRORSTR[] = "Please provide a valid file id to proceed.";

	echo display_error();

	application_log("error", "No file id was provided to edit. (Edit File)");
}
?>