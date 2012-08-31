<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Used to create folders within a specific page of a community. This action is
 * available only to community administrators.
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

echo "<h1>Add Shared Folder</h1>\n";

$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=add-folder", "title" => "Add Shared Folder");

// Error Checking
switch($STEP) {
	case 2 :
		/**
		 * Required field "title" / Folder Title.
		 */
		if ((isset($_POST["folder_title"])) && ($title = clean_input($_POST["folder_title"], array("notags", "trim")))) {
			$PROCESSED["folder_title"] = $title;
		} else {
			$ERROR++;
			$ERRORSTR[] = "The <strong>Folder Title</strong> field is required.";
		}

		/**
		 * Non-Required field "description" / Folder Description.
		 */
		if ((isset($_POST["folder_description"])) && ($description = clean_input($_POST["folder_description"], array("notags", "trim")))) {
			$PROCESSED["folder_description"] = $description;
		} else {
			$PROCESSED["folder_description"] = "";
		}

		/**
		 * Non-Required field "folder_icon" / Folder Icon.
		 */
		if ((isset($_POST["folder_icon"])) && ($folder_icon = clean_input($_POST["folder_icon"], "int")) && ($folder_icon > 0) && ($folder_icon <= 6)) {
			$PROCESSED["folder_icon"] = $folder_icon;
		} else {
			$PROCESSED["folder_icon"] = 1;
		}

		/**
		 * Permission checking for member access.
		 */
		if ((isset($_POST["allow_member_read"])) && (clean_input($_POST["allow_member_read"], array("int")) == 1)) {
			$PROCESSED["allow_member_read"]		= 1;
		} else {
			$PROCESSED["allow_member_read"]		= 0;
		}
		if ((isset($_POST["allow_member_upload"])) && (clean_input($_POST["allow_member_upload"], array("int")) == 1)) {
			$PROCESSED["allow_member_upload"]	= 1;
		} else {
			$PROCESSED["allow_member_upload"]	= 0;
		}
		if ((isset($_POST["allow_member_comment"])) && (clean_input($_POST["allow_member_comment"], array("int")) == 1)) {
			$PROCESSED["allow_member_comment"]	= 1;
		} else {
			$PROCESSED["allow_member_comment"]	= 0;
		}

		/**
		 * Permission checking for troll access.
		 * This can only be done if the community_registration is set to "Open Community"
		 */
		if (!(int) $community_details["community_registration"]) {
			if ((isset($_POST["allow_troll_read"])) && (clean_input($_POST["allow_troll_read"], array("int")) == 1)) {
				$PROCESSED["allow_troll_read"]		= 1;
			} else {
				$PROCESSED["allow_troll_read"]		= 0;
			}
			if ((isset($_POST["allow_troll_upload"])) && (clean_input($_POST["allow_troll_upload"], array("int")) == 1)) {
				$PROCESSED["allow_troll_upload"]	= 1;
			} else {
				$PROCESSED["allow_troll_upload"]	= 0;
			}
			if ((isset($_POST["allow_troll_comment"])) && (clean_input($_POST["allow_troll_comment"], array("int")) == 1)) {
				$PROCESSED["allow_troll_comment"]	= 1;
			} else {
				$PROCESSED["allow_troll_comment"]	= 0;
			}
		} else {
			$PROCESSED["allow_troll_read"]			= 0;
			$PROCESSED["allow_troll_upload"]		= 0;
			$PROCESSED["allow_troll_comment"]		= 0;
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
			$PROCESSED["allow_public_upload"]	= 0;
			$PROCESSED["allow_public_comment"]	= 0;
		} else {
			$PROCESSED["allow_public_read"]		= 0;
			$PROCESSED["allow_public_upload"]	= 0;
			$PROCESSED["allow_public_comment"]	= 0;
		}

		/**
		 * Email Notificaions.
		 */
		if(isset($_POST["admin_notify"]) || isset($_POST["member_notify"])) {
			$PROCESSED["admin_notifications"] = $_POST["admin_notify"] + $_POST["member_notify"];
		} else {
			$PROCESSED["admin_notifications"] = 0;
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
			$PROCESSED["community_id"]		= $COMMUNITY_ID;
			$PROCESSED["proxy_id"]			= $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"];
			$PROCESSED["folder_active"]		= 1;
			$PROCESSED["updated_date"]		= time();
			$PROCESSED["updated_by"]		= $_SESSION["details"]["id"];
			$PROCESSED["cpage_id"]			= $PAGE_ID;

			if ($db->AutoExecute("community_shares", $PROCESSED, "INSERT")) {
				if ($FOLDER_ID = $db->Insert_Id()) {
					$url			= COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL;
					$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";

					$SUCCESS++;
					$SUCCESSSTR[]	= "You have successfully added a new shared folder to the community.<br /><br />You will now be redirected to the index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
					add_statistic("community:".$COMMUNITY_ID.":shares", "folder_add", "cshare_id", $FOLDER_ID);
					communities_log_history($COMMUNITY_ID, $PAGE_ID, $FOLDER_ID, "community_history_add_share", 1);
				}
			}

			if (!$SUCCESS) {
				$ERROR++;
				$ERRORSTR[] = "There was a problem inserting this folder into the system. The MEdTech Unit was informed of this error; please try again later.";

				application_log("error", "There was an error inserting a shared folder. Database said: ".$db->ErrorMsg());
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
		}
	break;
	case 1 :
	default :
		if ((!isset($PROCESSED["folder_icon"])) || (!(int) $PROCESSED["folder_icon"]) || ($PROCESSED["folder_icon"] < 1) || ($PROCESSED["folder_icon"] > 6) ) {
			$PROCESSED["folder_icon"] = 1;
		}

		$ONLOAD[] = "updateFolderIcon('".$PROCESSED["folder_icon"]."')";

		if ($ERROR) {
			echo display_error();
		}
		if ($NOTICE) {
			echo display_notice();
		}
		?>
		<script type="text/javascript">
		var folder_icon_number	= '<?php echo $PROCESSED["folder_icon"]; ?>';
		</script>
		<form action="<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?section=add-folder&amp;step=2" method="post">
		<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Add Shared Folder">
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
				<td colspan="3"><h2>Folder Details</h2></td>
			</tr>
			<tr>
				<td colspan="2"><label for="folder_title" class="form-required">Folder Title</label></td>
				<td><input type="text" id="folder_title" name="folder_title" value="<?php echo ((isset($PROCESSED["folder_title"])) ? html_encode($PROCESSED["folder_title"]) : ""); ?>" maxlength="64" style="width: 95%" /></td>
			</tr>
			<tr>
				<td colspan="2" style="vertical-align: top !important"><label for="folder_description" class="form-nrequired">Folder Description</label></td>
				<td style="vertical-align: top">
					<textarea id="folder_description" name="folder_description" style="width: 95%; height: 60px" cols="50" rows="5"><?php echo ((isset($PROCESSED["folder_description"])) ? html_encode($PROCESSED["folder_description"]) : ""); ?></textarea>
				</td>
			</tr>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2"><label for="folder_icon" class="form-nrequired">Folder Icon</label></td>
				<td style="vertical-align: middle">
					<input type="hidden" id="folder_icon" name="folder_icon" value="<?php echo $PROCESSED["folder_icon"]; ?>" />
					<div id="color-icon-list">
						<img id="folder-icon-1" src="<?php echo COMMUNITY_URL."/templates/".$COMMUNITY_TEMPLATE."/images/list-folder-1.gif"; ?>" width="32" height="32" alt="Folder Icon 1" title="Folder Icon 1" onclick="updateFolderIcon('1')" />
						<img id="folder-icon-2" src="<?php echo COMMUNITY_URL."/templates/".$COMMUNITY_TEMPLATE."/images/list-folder-2.gif"; ?>" width="32" height="32" alt="Folder Icon 2" title="Folder Icon 2" onclick="updateFolderIcon('2')" />
						<img id="folder-icon-3" src="<?php echo COMMUNITY_URL."/templates/".$COMMUNITY_TEMPLATE."/images/list-folder-3.gif"; ?>" width="32" height="32" alt="Folder Icon 3" title="Folder Icon 3" onclick="updateFolderIcon('3')" />
						<img id="folder-icon-4" src="<?php echo COMMUNITY_URL."/templates/".$COMMUNITY_TEMPLATE."/images/list-folder-4.gif"; ?>" width="32" height="32" alt="Folder Icon 4" title="Folder Icon 4" onclick="updateFolderIcon('4')" />
						<img id="folder-icon-5" src="<?php echo COMMUNITY_URL."/templates/".$COMMUNITY_TEMPLATE."/images/list-folder-5.gif"; ?>" width="32" height="32" alt="Folder Icon 5" title="Folder Icon 5" onclick="updateFolderIcon('5')" />
						<img id="folder-icon-6" src="<?php echo COMMUNITY_URL."/templates/".$COMMUNITY_TEMPLATE."/images/list-folder-6.gif"; ?>" width="32" height="32" alt="Folder Icon 6" title="Folder Icon 6" onclick="updateFolderIcon('6')" />
					</div>
				</td>
			</tr>
			<tr>
				<td colspan="3"><h2>Folder Permissions</h2></td>
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
							<td style="border-left: none">Browse Folder</td>
							<td style="border-left: none">Upload Files</td>
							<td style="border-left: none">Allow Comments</td>
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
							<td><input type="checkbox" id="allow_member_upload" name="allow_member_upload" value="1"<?php echo (((!isset($PROCESSED["allow_member_upload"])) || ((isset($PROCESSED["allow_member_upload"])) && ($PROCESSED["allow_member_upload"] == 1))) ? " checked=\"checked\"" : ""); ?> /></td>
							<td class="on"><input type="checkbox" id="allow_member_comment" name="allow_member_comment" value="1"<?php echo (((!isset($PROCESSED["allow_member_comment"])) || ((isset($PROCESSED["allow_member_comment"])) && ($PROCESSED["allow_member_comment"] == 1))) ? " checked=\"checked\"" : ""); ?> /></td>
						</tr>
						<?php if (!(int) $community_details["community_registration"]) :  ?>
						<tr>
							<td class="left"><strong>Browsing Non-Members</strong></td>
							<td class="on"><input type="checkbox" id="allow_troll_read" name="allow_troll_read" value="1"<?php echo (((!isset($PROCESSED["allow_troll_read"])) || ((isset($PROCESSED["allow_troll_read"])) && ($PROCESSED["allow_troll_read"] == 1))) ? " checked=\"checked\"" : ""); ?> /></td>
							<td><input type="checkbox" id="allow_troll_upload" name="allow_troll_upload" value="1"<?php echo (((isset($PROCESSED["allow_troll_upload"])) && ($PROCESSED["allow_troll_upload"] == 1)) ? " checked=\"checked\"" : ""); ?> /></td>
							<td class="on"><input type="checkbox" id="allow_troll_comment" name="allow_troll_comment" value="1"<?php echo (((isset($PROCESSED["allow_troll_comment"])) && ($PROCESSED["allow_troll_comment"] == 1)) ? " checked=\"checked\"" : ""); ?> /></td>
						</tr>
						<?php endif; ?>
						<?php if (!(int) $community_details["community_protected"]) :  ?>
						<tr>
							<td class="left"><strong>Non-Authenticated / Public Users</strong></td>
							<td class="on"><input type="checkbox" id="allow_public_read" name="allow_public_read" value="1"<?php echo (((isset($PROCESSED["allow_public_read"])) && ($PROCESSED["allow_public_read"] == 1)) ? " checked=\"checked\"" : ""); ?> /></td>
							<td><input type="checkbox" id="allow_public_upload" name="allow_public_upload" value="0" onclick="noPublic(this)" /></td>
							<td class="on"><input type="checkbox" id="allow_public_comment" name="allow_public_comment" value="0" onclick="noPublic(this)" /></td>
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
?>
