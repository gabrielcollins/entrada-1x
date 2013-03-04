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

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {

	$PAGE_META["title"]			= "My Administrative Assistants";
	$PAGE_META["description"]	= "";
	$PAGE_META["keywords"]		= "";

	$PROXY_ID					= $ENTRADA_USER->getID();
	$VALID_MIME_TYPES			= array("image/pjpeg" => "jpg", "image/jpeg" => "jpg", "image/jpg" => "jpg", "image/gif" => "gif", "image/png" => "png");
	$VALID_MAX_FILESIZE			= 2097512; // 2MB
	$VALID_MAX_DIMENSIONS		= array("photo-width" => 216, "photo-height" => 300, "thumb-width" => 75, "thumb-height" => 104);
	$RENDER						= false;

	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/profile?section=assistants", "title" => "My Administrative Assistants");

	$PROCESSED		= array();

	if ((is_array($_SESSION["permissions"])) && ($total_permissions = count($_SESSION["permissions"]) > 1)) {
		$sidebar_html  = "The following individual".((($total_permissions - 1) != 1) ? "s have" : " has")." given you access to their ".APPLICATION_NAME." permission levels:";
		$sidebar_html .= "<ul class=\"menu\">\n";
		foreach ($_SESSION["permissions"] as $access_id => $result) {
			if ($access_id != $ENTRADA_USER->getDefaultAccessId()) {
				$sidebar_html .= "<li class=\"checkmark\"><strong>".html_encode($result["fullname"])."</strong><br /><span class=\"content-small\">Exp: ".(($result["expires"]) ? date("D M d/y", $result["expires"]) : "Unknown")."</span></li>\n";
			}
		}
		$sidebar_html .= "</ul>\n";

		new_sidebar_item("Delegated Permissions", $sidebar_html, "delegated-permissions", "open");
	}

	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/profile.js\"></script>";
	
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
	$query	= "SELECT * FROM `".AUTH_DATABASE."`.`user_data` WHERE `".AUTH_DATABASE."`.`user_data`.`id`=".$db->qstr($ENTRADA_USER->getID());
	$result	= $db->GetRow($query);
	if ($result) {
	
	
				$HEAD[] = "<link href=\"".ENTRADA_URL."/javascript/calendar/css/xc2_default.css\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />";
				$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/config/xc2_default.js\"></script>";
				$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/script/xc2_inpage.js\"></script>";
			if ($ENTRADA_ACL->isLoggedInAllowed('assistant_support', 'create')) {
				?>
				<script type="text/javascript">
				function addAssistant() {
					if ((document.getElementById('assistant_id') != null) && (document.getElementById('assistant_id').value != '')) {
						document.getElementById('assisant_add_form').submit();
					} else {
						alert('You can only add people as assistants to your profile if they already exist in the system.\n\nIf you are typing in their name properly (Lastname, Firstname) and their name does not show up in the list then chances are that they do not exist in our system.\n\nPlease Note: When you see the correct name pop-up in the list as you type, make sure you select the name with your mouse, do not press the Enter button.');

						return false;
					}
				}

				function copyAssistant() {
					if ((document.getElementById('assistant_name') != null) && (document.getElementById('assistant_ref') != null)) {
						document.getElementById('assistant_ref').value = document.getElementById('assistant_name').value;
					}

					return true;
				}

				function checkAssistant() {
					if ((document.getElementById('assistant_name') != null) && (document.getElementById('assistant_ref') != null) && (document.getElementById('assistant_id') != null)) {
						if (document.getElementById('assistant_name').value != document.getElementById('assistant_ref').value) {
							document.getElementById('assistant_id').value = '';
						}
					}

					return true;
				}

				function confirmRemoval() {
					ask_user = confirm("Press OK to confirm that you would like to remove the ability for the selected individuals to access your permission levels, otherwise press Cancel.");

					if (ask_user == true) {
						document.getElementById('assisant_remove_form').submit();
					} else {
						return false;
					}
				}

				function selectAssistant(id) {
					if ((id != null) && (document.getElementById('assistant_id') != null)) {
						document.getElementById('assistant_id').value = id;
					}
				}
				</script>
					<h1 style="margin-top: 0px">My Admin Assistants</h1>
					This section allows you to assign other <?php echo APPLICATION_NAME; ?> users access privileges to <strong>your</strong> <?php echo APPLICATION_NAME; ?> account permissions. This powerful feature should be used very carefully because when you assign someone privileges to your account, they will be able to do <strong>everything in this system</strong> that you are able to do using their own account.
					<br /><br />
					<form action="<?php echo ENTRADA_URL; ?>/profile?section=assistants" method="post" id="assisant_add_form">
					<input type="hidden" name="action" value="assistant-add" />
					<input type="hidden" id="assistant_ref" name="assistant_ref" value="" />
					<input type="hidden" id="assistant_id" name="assistant_id" value="" />
					<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Adding Event">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 20%" />
						<col style="width: 77%" />
					</colgroup>
					<tfoot>
						<tr>
							<td colspan="3" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
								<input type="button" class="button" value="Add Assistant" onclick="addAssistant()" />
							</td>
						</tr>
					</tfoot>
					<tbody>
						<tr>
							<td>&nbsp;</td>
								<td><label for="assistant_name" class="form-required">Assistants Fullname:</label></td>
							<td>
								<input type="text" id="assistant_name" name="fullname" size="30" value="" autocomplete="off" style="width: 203px; vertical-align: middle" onkeyup="checkAssistant()" />
								<div class="autocomplete" id="assistant_name_auto_complete"></div><script type="text/javascript">new Ajax.Autocompleter('assistant_name', 'assistant_name_auto_complete', '<?php echo ENTRADA_RELATIVE; ?>/api/personnel.api.php', {frequency: 0.2, minChars: 2, afterUpdateElement: function (text, li) {selectAssistant(li.id); copyAssistant();}});</script>
								<span class="content-small">(<strong>Example:</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?>)</span>
							</td>
						</tr>
									<?php echo generate_calendars("valid", "Access", true, true, $start_time = ((isset($PROCESSED["valid_from"])) ? $PROCESSED["valid_from"] : mktime(0, 0, 0, date("n", time()), date("j", time()), date("Y", time()))), true, true, ((isset($PROCESSED["valid_until"])) ? $PROCESSED["valid_until"] : strtotime("+1 week 23 hours 59 minutes 59 seconds", $start_time))); ?>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
					</tbody>
					</table>
					</form>
					<br /><br />
					<?php
					$query		= "	SELECT a.`permission_id`, a.`assigned_to`, a.`valid_from`, a.`valid_until`, CONCAT_WS(', ', b.`lastname`, b.`firstname`) AS `fullname`
									FROM `permissions` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
									ON b.`id` = a.`assigned_to`
									WHERE a.`assigned_by`=".$db->qstr($ENTRADA_USER->getID())."
									ORDER BY `valid_until` ASC";
					$results	= $db->GetAll($query);
					if ($results) {
						?>
						<form action="<?php echo ENTRADA_URL; ?>/profile?section=assistants" method="post" id="assisant_remove_form">
						<input type="hidden" name="action" value="assistant-remove" />
						<table class="tableList" cellspacing="0" summary="List of Assistants">
						<colgroup>
							<col class="modified" />
							<col class="title" />
							<col class="date" />
							<col class="date" />
						</colgroup>
						<thead>
							<tr>
								<td class="modified">&nbsp;</td>
											<td class="title">Assistants Fullname</td>
								<td class="date">Access Starts</td>
								<td class="date sortedASC"><div class="noLink">Access Finishes</div></td>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="4" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
									<input type="button" class="button" value="Remove Assistant" onclick="confirmRemoval()" />
								</td>
							</tr>
						</tfoot>
						<tbody>
						<?php
						foreach ($results as $result) {
							echo "<tr>\n";
							echo "	<td class=\"modified\"><input type=\"checkbox\" name=\"remove[".$result["assigned_to"]."]\" value=\"".$result["permission_id"]."\" /></td>\n";
							echo "	<td class=\"title\">".html_encode($result["fullname"])."</td>\n";
							echo "	<td class=\"date\">".date(DEFAULT_DATE_FORMAT, $result["valid_from"])."</td>\n";
							echo "	<td class=\"date\">".date(DEFAULT_DATE_FORMAT, $result["valid_until"])."</td>\n";
							echo "</tr>\n";
						}
						?>
						</tbody>
						</table>
						</form>
						<?php
					} else {
						$NOTICE++;
						$NOTICESTR[] = "You currently have no assistants / administrative support staff setup for access to your permissions.";

						echo display_notice();
					}
			}
	} else {
		$NOTICE++;
		$NOTICESTR[]	= "Unfortunately your ".APPLICATION_NAME." profile is not accessible at this time, please try again later.";

		echo display_notice();

		application_log("error", "A user profile was not available in the database? Database said: ".$db->ErrorMsg());
	}
}
?>