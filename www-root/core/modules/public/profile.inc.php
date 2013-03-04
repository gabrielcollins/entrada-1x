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
 * This file gives Entrada users the ability to update their user profile.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if (!defined("PARENT_INCLUDED")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->isLoggedInAllowed('profile', 'read')) {
	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$GROUP."] and role [".$ROLE."] do not have access to this module [".$MODULE."]");
} else {
	define("IN_PROFILE", true);
	
	$VALID_MIME_TYPES			= array("image/pjpeg" => "jpg", "image/jpeg" => "jpg", "image/jpg" => "jpg", "image/gif" => "gif", "image/png" => "png");
	$VALID_MAX_FILESIZE			= 2097512; // 2MB
	$VALID_MAX_DIMENSIONS		= array("photo-width" => 216, "photo-height" => 300, "thumb-width" => 75, "thumb-height" => 104);
	$RENDER						= false;
	
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/profile", "title" => "My Profile");

	if (($router) && ($router->initRoute())) {
		$module_file = $router->getRoute();
		if ($module_file) {
		
			if (isset($ACTION)) {
				switch(trim(strtolower($ACTION))) {
					case "privacy-update" :
						profile_update_privacy();
					break;
					case "notifications-update" :
						profile_update_notifications();
					break;
					case "google-update" :
						profile_update_google();
					break;
					case "google-password-reset" :
						profile_update_google_password();
					break;
					case "privacy-google-update" :
						profile_update_google_privacy();
					break;
					case "profile-update" :
						profile_update_personal_info();
					break;
					case "assistant-add" :
						profile_add_assistant();
					break;
					case "assistant-remove" :
						profile_remove_assistant();
					break;
				}
			}
			add_profile_sidebar();
			
			require_once($module_file);
			
		}
	} else {
		$url = ENTRADA_URL."/public/".$MODULE;
		application_log("error", "The Entrada_Router failed to load a request. The user was redirected to [".$url."].");

		header("Location: ".$url);
		exit;
	}

}

/**
 * Creates the profile sidebar to appear on all profile pages. The sidebar content will vary depending on the permissions of the user.
 * 
 */
function add_profile_sidebar () {
	global $ENTRADA_ACL;
	$sidebar_html  = "<ul class=\"menu\">";
	$sidebar_html .= "	<li class=\"link\"><a href=\"".ENTRADA_URL."/profile\">Personal Information</a></li>\n";
	//$sidebar_html .= "	<li class=\"link\"><a href=\"".$this_module."?section=photo\">Profile Photo</a></li>\n";
	$sidebar_html .= "	<li class=\"link\"><a href=\"".ENTRADA_URL."/profile?section=privacy\">Privacy Settings</a></li>\n";
	if (((defined("COMMUNITY_NOTIFICATIONS_ACTIVE")) && ((bool) COMMUNITY_NOTIFICATIONS_ACTIVE)) || ((defined("NOTIFICATIONS_ACTIVE")) && ((bool) NOTIFICATIONS_ACTIVE))) {
		$sidebar_html .= "	<li class=\"link\"><a href=\"".ENTRADA_URL."/profile?section=notifications\">Manage My Notifications</a></li>\n";
	}
	if ($ENTRADA_ACL->isLoggedInAllowed('assistant_support', 'create')) {
		$sidebar_html .= "	<li class=\"link\"><a href=\"".ENTRADA_URL."/profile?section=assistants\">My Admin Assistants</a></li>\n";
	}

	if ($_SESSION["details"]["group"] == "student") {
		$sidebar_html .= "	<li class=\"link\"><a href=\"".ENTRADA_URL."/profile?section=mspr\">My MSPR</a></li>\n";
		$sidebar_html .= "	<li class=\"link\"><a href=\"".ENTRADA_URL."/profile/gradebook\">My Gradebooks</a></li>\n";
		$sidebar_html .= "	<li class=\"link\"><a href=\"".ENTRADA_URL."/profile/gradebook/assignments\">My Assignments</a></li>\n";
	}
	
	$sidebar_html .= "</ul>";

	new_sidebar_item("Profile", $sidebar_html, "profile-nav", "open");
}

/**
 * Processes the personal info update. source data retrieved from POST. modifies the $PROCESSED variable 
 */
function profile_update_personal_info() {
	global $db, $PROCESSED, $PROFILE_NAME_PREFIX, $ERROR, $ERRORSTR, $SUCCESS, $SUCCESSSTR, $NOTICE, $NOTICESTR, $PROCESSED_PHOTO, $PROCESSED_PHOTO_STATUS, $PROCESSED_NOTIFICATIONS, $VALID_MIME_TYPES, $ENTRADA_USER;
	if ((isset($_POST["prefix"])) && (@in_array(trim($_POST["prefix"]), $PROFILE_NAME_PREFIX))) {
		$PROCESSED["prefix"] = trim($_POST["prefix"]);
	} else {
		$PROCESSED["prefix"] = "";
	}

	if ((isset($_POST["office_hours"])) && ($office_hours = clean_input($_POST["office_hours"], array("notags","encode", "trim"))) && ($_SESSION["details"]["group"] != "student")) {
		$PROCESSED["office_hours"] = ((strlen($office_hours) > 100) ? substr($office_hours, 0, 97)."..." : $office_hours);
	} else {
		$PROCESSED["office_hours"] = "";
	}
		
	if($_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"] == "faculty") {
		if ((isset($_POST["email"])) && ($email = clean_input($_POST["email"], "trim", "lower"))) {
			if (@valid_address($email)) {
				$PROCESSED["email"] = $email;
			} else {
				$ERROR++;
				$ERRORSTR[] = "The primary e-mail address you have provided is invalid. Please make sure that you provide a properly formatted e-mail address.";
			}
		} else { 
			$ERROR++;
			$ERRORSTR[] = "The primary e-mail address is a required field.";
		}
	}
	
	if ((isset($_POST["email_alt"])) && ($_POST["email_alt"] != "")) {
		if (@valid_address(trim($_POST["email_alt"]))) {
			$PROCESSED["email_alt"] = strtolower(trim($_POST["email_alt"]));
		} else {
			$ERROR++;
			$ERRORSTR[] = "The secondary e-mail address you have provided is invalid. Please make sure that you provide a properly formatted e-mail address or leave this field empty if you do not wish to display one.";
		}
	} else {
		$PROCESSED["email_alt"] = "";
	}

	if ((isset($_POST["telephone"])) && (strlen(trim($_POST["telephone"])) >= 10) && (strlen(trim($_POST["telephone"])) <= 25)) {
		$PROCESSED["telephone"] = strtolower(trim($_POST["telephone"]));
	} else {
		$PROCESSED["telephone"] = "";
	}

	if ((isset($_POST["fax"])) && (strlen(trim($_POST["fax"])) >= 10) && (strlen(trim($_POST["fax"])) <= 25)) {
		$PROCESSED["fax"] = strtolower(trim($_POST["fax"]));
	} else {
		$PROCESSED["fax"] = "";
	}

	if ((isset($_POST["address"])) && (strlen(trim($_POST["address"])) >= 6) && (strlen(trim($_POST["address"])) <= 255)) {
		$PROCESSED["address"] = ucwords(strtolower(trim($_POST["address"])));
	} else {
		$PROCESSED["address"] = "";
	}

	if ((isset($_POST["city"])) && (strlen(trim($_POST["city"])) >= 3) && (strlen(trim($_POST["city"])) <= 35)) {
		$PROCESSED["city"] = ucwords(strtolower(trim($_POST["city"])));
	} else {
		$PROCESSED["city"] = "";
	}

	if ((isset($_POST["postcode"])) && (strlen(trim($_POST["postcode"])) >= 5) && (strlen(trim($_POST["postcode"])) <= 12)) {
		$PROCESSED["postcode"] = strtoupper(trim($_POST["postcode"]));
	} else {
		$PROCESSED["postcode"] = "";
	}

	if ((isset($_POST["country_id"])) && ($tmp_input = clean_input($_POST["country_id"], "int"))) {
		$query = "SELECT * FROM `global_lu_countries` WHERE `countries_id` = ".$db->qstr($tmp_input);
		$result = $db->GetRow($query);
		if ($result) {
			$PROCESSED["country_id"] = $tmp_input;
		} else {
			$ERROR++;
			$ERRORSTR[] = "The selected country does not exist in our countries database. Please select a valid country.";

			application_log("error", "Unknown countries_id [".$tmp_input."] was selected. Database said: ".$db->ErrorMsg());
		}
	} else {
		$ERROR++;
		$ERRORSTR[]	= "You must select a country.";
	}

	if ((isset($_POST["prov_state"])) && ($tmp_input = clean_input($_POST["prov_state"], array("trim", "notags")))) {
		$PROCESSED["province_id"] = 0;
		$PROCESSED["province"] = "";

		if (ctype_digit($tmp_input) && ($tmp_input = (int) $tmp_input)) {
			if ($PROCESSED["country_id"]) {
				$query = "SELECT * FROM `global_lu_provinces` WHERE `province_id` = ".$db->qstr($tmp_input)." AND `country_id` = ".$db->qstr($PROCESSED["country_id"]);
				$result = $db->GetRow($query);
				if (!$result) {
					$ERROR++;
					$ERRORSTR[] = "The province / state you have selected does not appear to exist in our database. Please selected a valid province / state.";
				}
			}

			$PROCESSED["province_id"] = $tmp_input;
		} else {
			$PROCESSED["province"] = $tmp_input;
		}

		$PROCESSED["prov_state"] = ($PROCESSED["province_id"] ? $PROCESSED["province_id"] : ($PROCESSED["province"] ? $PROCESSED["province"] : ""));
	}
	
	if ((!$ERROR) && (isset($_FILES["photo_file"])) && ($_FILES["photo_file"]["error"] != 4)) {
		switch($_FILES["photo_file"]["error"]) {
			case 0 :
				$photo_mimetype = clean_input($_FILES["photo_file"]["type"], array("trim", "lowercase"));
				if (in_array($photo_mimetype, array_keys($VALID_MIME_TYPES))) {
					if (($photo_filesize = (int) trim($_FILES["photo_file"]["size"])) <= $VALID_MAX_FILESIZE) {
						$PROCESSED_PHOTO["photo_mimetype"]	= $photo_mimetype;
						$PROCESSED_PHOTO["photo_filesize"]	= $photo_filesize;

						$photo_file_extension = strtoupper($VALID_MIME_TYPES[strtolower(trim($_FILES["photo_file"]["type"]))]);

						if ((!defined("STORAGE_USER_PHOTOS")) || (!@is_dir(STORAGE_USER_PHOTOS)) || (!@is_writable(STORAGE_USER_PHOTOS))) {
							$ERROR++;
							$ERRORSTR[] = "There is a problem with the gallery storage directory on the server; the system administrator has been informed of this error, please try again later.";

							application_log("error", "The community gallery storage path [".STORAGE_USER_PHOTOS."] does not exist or is not writable.");
						}
					}
				} else {
					$ERROR++;
					$ERRORSTR[] = "The file that you have uploaded does not appear to be a valid image. Please ensure that you upload a JPEG, GIF or PNG file.";
				}
				break;
			case 1 :
			case 2 :
				$ERROR++;
				$ERRORSTR[] = "The photo that was uploaded is larger than ".readable_size($VALID_MAX_FILESIZE).". Please make the photo smaller and try again.";
				break;
			case 3 :
				$ERROR++;
				$ERRORSTR[]	= "The photo that was uploaded did not complete the upload process or was interrupted; please try again.";
				break;
			case 6 :
			case 7 :
				$ERROR++;
				$ERRORSTR[]	= "Unable to store the new photo file on the server; the system administrator has been informed of this error, please try again later.";

				application_log("error", "Community photo file upload error: ".(($_FILES["filename"]["error"] == 6) ? "Missing a temporary folder." : "Failed to write file to disk."));
				break;
			default :
				application_log("error", "Unrecognized photo file upload error number [".$_FILES["filename"]["error"]."].");
				break;
		}

		if (!$ERROR) {
			$PROCESSED_PHOTO["proxy_id"]			= $ENTRADA_USER->getID();
			$PROCESSED_PHOTO["photo_active"]		= 1;
			$PROCESSED_PHOTO["photo_type"]			= 1;
			$PROCESSED_PHOTO["updated_date"]		= time();

			if ($photo_id = $db->GetOne("SELECT `photo_id` FROM `".AUTH_DATABASE."`.`user_photos` WHERE `proxy_id` = ".$db->qstr($ENTRADA_USER->getID())." AND `photo_type` = 1")) {
				if ($db->AutoExecute(AUTH_DATABASE.".user_photos", $PROCESSED_PHOTO, "UPDATE", "photo_id = ".$photo_id)) {
					if ($photo_id) {
						if (process_user_photo($_FILES["photo_file"]["tmp_name"], $photo_id)) {
							$SUCCESS++;
							$SUCCESSSTR[]	= "You have successfully uploaded a new profile photo.";
						}
					}
				}
			} else {
				if ($db->AutoExecute(AUTH_DATABASE.".user_photos", $PROCESSED_PHOTO, "INSERT")) {
					if ($photo_id = $db->Insert_Id()) {
						if (process_user_photo($_FILES["photo_file"]["tmp_name"], $photo_id)) {
							$SUCCESS++;
							$SUCCESSSTR[]	= "You have successfully uploaded a new profile photo.";
						}
					}
				}
			}
		}
	}
	
	if ($_FILES["photo_file"]["error"] == 4 && isset($_POST["deactivate_photo"])) {
		$PROCESSED_PHOTO_STATUS = array();
		$PROCESSED_PHOTO_STATUS["photo_active"] = 0;
	} elseif ($_FILES["photo_file"]["error"] == 4 && !isset($_POST["deactivate_photo"])) {
		$PROCESSED_PHOTO_STATUS = array();
		$PROCESSED_PHOTO_STATUS["photo_active"] = 1;
	} elseif ($_FILES["photo_file"]["error"] != 4 && isset($_POST["deactivate_photo"]) && $_POST["deactivate_photo"]) {
		$NOTICE++;
		$NOTICESTR[] = "You cannot deactivate a newly uploaded photo, please try again without uploading a new photo.";
	}

	if (isset($PROCESSED_PHOTO_STATUS) && !$db->AutoExecute(AUTH_DATABASE.".user_photos", $PROCESSED_PHOTO_STATUS, "UPDATE", "proxy_id=".$db->qstr($ENTRADA_USER->getID())." AND photo_type = 1")) {
		$ERROR++;
		$ERRORSTR[] = "There was an issue trying to deactivate your current photo.";
	}

	//if (isset($_POST["tab"]) && $_POST["tab"] != "profile-photo" && $_POST["tab"] != "notifications") {
		if (!$ERROR) {			
			if ($db->AutoExecute(AUTH_DATABASE.".user_data", $PROCESSED, "UPDATE", "`id` = ".$db->qstr($ENTRADA_USER->getID()))) {
				$SUCCESS++;
				$SUCCESSSTR[] = "Your account profile has been successfully updated.";

				application_log("success", "User successfully updated their profile.");

			} else {
				$ERROR++;
				$ERRORSTR[] = "We were unfortunately unable to update your profile at this time. The system administrator has been informed of the problem, please try again later.";

				application_log("error", "Unable to update user profile. Database said: ".$db->ErrorMsg());
			}
		}
	//}
}

function profile_update_privacy() {
	/**
	 * This actually changes the privacy settings in their profile.
	 * Note: The sessions variable ($_SESSION["details"]["privacy_level"]) is actually being
	 * changed in index.php on line 268, so that the proper tabs are displayed.
	 */
	global $db, $ERROR, $ERRORSTR, $ENTRADA_USER;
	
	if ((isset($_POST["privacy_level"])) && ($privacy_level = (int) trim($_POST["privacy_level"]))) {
		if ($privacy_level > MAX_PRIVACY_LEVEL) {
			$privacy_level = MAX_PRIVACY_LEVEL;
		}
		if ($db->AutoExecute(AUTH_DATABASE.".user_data", array("privacy_level" => $privacy_level), "UPDATE", "`id` = ".$db->qstr($ENTRADA_USER->getID()))) {
			if ((isset($_POST["redirect"])) && (trim($_POST["redirect"]) != "")) {
				header("Location: ".((isset($_SERVER["HTTPS"])) ? "https" : "http")."://".$_SERVER["HTTP_HOST"].clean_input(rawurldecode($_POST["redirect"]), array("nows", "url")));
				exit;
			} else {
				header("Location: ".ENTRADA_URL);
				exit;
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "We were unfortunately unable to update your privacy settings at this time. The system administrator has been informed of the error, please try again later.";

			application_log("error", "Unable to update privacy setting. Database said: ".$db->ErrorMsg());
		}

	}
}

function profile_update_google_privacy() {
	global $db, $GOOGLE_APPS, $ERROR, $ERRORSTR, $SUCCESS, $SUCCESSSTR, $ENTRADA_USER;
	
	if ((bool) $GOOGLE_APPS["active"]) {
		/**
		 * This actually creates a Google Hosted Apps account associated with their profile.
		 * Note: The sessions variable ($_SESSION["details"]["google_id"]) is being
		 * changed in index.php on line 242 to opt-in, which is merely used in the logic
		 * of the first-login page, but only if the user has no google id and hasn't opted out.
		 */
		if (isset($_POST["google_account"])) {
			if ((int) trim($_POST["google_account"])) {
				if (google_create_id()) {
					$SUCCESS++;
					$SUCCESSSTR[] = "<strong>Your new ".$GOOGLE_APPS["domain"]."</strong> account has been created!</strong><br /><br />An e-mail will be sent to ".$_SESSION["details"]["email"]." shortly, containing further instructions regarding account activation.";
				}
			} else {
				$db->Execute("UPDATE `".AUTH_DATABASE."`.`user_data` SET `google_id` = 'opt-out' WHERE `id` = ".$db->qstr($ENTRADA_USER->getID()));
			}
		}
	}

	/**
	 * This actually changes the privacy settings in their profile.
	 * Note: The sessions variable ($_SESSION["details"]["privacy_level"]) is actually being
	 * changed in index.php on line 268, so that the proper tabs are displayed.
	 */
	if ((isset($_POST["privacy_level"])) && ($privacy_level = (int) trim($_POST["privacy_level"]))) {
		if ($privacy_level > MAX_PRIVACY_LEVEL) {
			$privacy_level = MAX_PRIVACY_LEVEL;
		}
		if (!$db->AutoExecute(AUTH_DATABASE.".user_data", array("privacy_level" => $privacy_level), "UPDATE", "`id` = ".$db->qstr($ENTRADA_USER->getID()))){
			$ERROR++;
			$ERRORSTR[] = "We were unfortunately unable to update your privacy settings at this time. The system administrator has been informed of the error, please try again later.";

			application_log("error", "Unable to update privacy setting. Database said: ".$db->ErrorMsg());
		}
	}
}

function profile_update_google() {
	global $db, $GOOGLE_APPS, $ERROR, $ERRORSTR, $SUCCESS, $SUCCESSSTR, $ENTRADA_USER;
		
	if ((bool) $GOOGLE_APPS["active"]) {
		/**
		 * This actually creates a Google Hosted Apps account associated with their profile.
		 * Note: The sessions variable ($_SESSION["details"]["google_id"]) is being
		 * changed in index.php on line 242 to opt-in, which is merely used in the logic
		 * of the first-login page, but only if the user has no google id and hasn't opted out.
		 */
		if (isset($_POST["google_account"])) {
			if ((int) trim($_POST["google_account"])) {
				if (google_create_id()) {
					$SUCCESS++;
					$SUCCESSSTR[] = "<strong>Your new ".$GOOGLE_APPS["domain"]."</strong> account has been created!</strong><br /><br />An e-mail will be sent to ".$_SESSION["details"]["email"]." shortly, containing further instructions regarding account activation.";

					if ((isset($_POST["ajax"])) && ($_POST["ajax"] == "1")) {
						// Clear any open buffers and push through only the success message.
						ob_clear_open_buffers();
						echo display_success($SUCCESSSTR);
						exit;
					}
				} else {
					if ((isset($_POST["ajax"])) && ($_POST["ajax"] == "1")) {
						// $ERRORSTR is set by the google_create_id() function.
						// Clear any open buffers and push through only the error message.
						ob_clear_open_buffers();
						echo display_error($ERRORSTR);
						exit;
					}
				}
			} else {
				$db->Execute("UPDATE `".AUTH_DATABASE."`.`user_data` SET `google_id` = 'opt-out' WHERE `id` = ".$db->qstr($ENTRADA_USER->getID()));
			}
		}
	}
}

function profile_update_google_password() {
	global $db, $GOOGLE_APPS, $ERROR, $ERRORSTR, $SUCCESS, $SUCCESSSTR;

	ob_clear_open_buffers();

	if ((bool) $GOOGLE_APPS["active"]) {
		if (isset($_POST["password"]) && ($tmp_input = clean_input($_POST["password"], "trim"))) {
			if (google_reset_password($tmp_input)) {
				echo 1;
				exit;
			}
		}
	}

	echo 0;
	exit;
}

function profile_add_assistant() {
	global $db, $PROCESSED, $ERROR, $ERRORSTR, $SUCCESS, $SUCCESSSTR, $ENTRADA_ACL, $ENTRADA_USER;
	
	if ($ENTRADA_ACL->isLoggedInAllowed('assistant_support', 'create')) {
		$access_timeframe = validate_calendars("valid", true, true);

		if (!$ERROR) {
			if ((isset($access_timeframe["start"])) && ((int) $access_timeframe["start"])) {
				$PROCESSED["valid_from"] = (int) $access_timeframe["start"];
			}

			if ((isset($access_timeframe["finish"])) && ((int) $access_timeframe["finish"])) {
				$PROCESSED["valid_until"] = (int) $access_timeframe["finish"];
			}

			if ((isset($_POST["assistant_id"])) && ($proxy_id = (int) trim($_POST["assistant_id"]))) {
				if ($proxy_id != $ENTRADA_USER->getID()) {
					$query	= "
						SELECT a.`id` AS `proxy_id`, CONCAT_WS(' ', a.`firstname`, a.`lastname`) AS `fullname`
						FROM `".AUTH_DATABASE."`.`user_data` AS a
						LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
						ON b.`user_id` = a.`id` AND b.`app_id`='1' AND b.`account_active`='true' AND b.`group`<>'student'
						WHERE a.`id`=".$db->qstr($proxy_id);

					$result	= $db->GetRow($query);
					if ($result) {
						$PROCESSED["assigned_by"]	= $ENTRADA_USER->getID();
						$PROCESSED["assigned_to"]	= $result["proxy_id"];
						$fullname					= $result["fullname"];

						$query	= "SELECT * FROM `permissions` WHERE `assigned_by`=".$db->qstr($PROCESSED["assigned_by"])." AND `assigned_to`=".$db->qstr($PROCESSED["assigned_to"]);
						$result	= $db->GetRow($query);
						if ($result) {
							if ($db->AutoExecute("permissions", $PROCESSED, "UPDATE", "permission_id=".$db->qstr($result["permission_id"]))) {
								$SUCCESS++;
								$SUCCESSSTR[] = "You have successfully updated <strong>".html_encode($fullname)."'s</strong> access permissions to your account.";

								application_log("success", "Updated permissions for proxy_id [".$PROCESSED["assigned_by"]."] who is allowing [".$PROCESSED["assigned_by"]."] accecss to their account from ".date(DEFAULT_DATE_FORMAT, $PROCESSED["valid_from"])." until ".date(DEFAULT_DATE_FORMAT, $PROCESSED["valid_until"]));
							} else {
								$ERROR++;
								$ERRORSTR[] = "We were unable to update <strong>".html_encode($fullname)."'s</strong> access permissions to your account at this time. The system administrator has been informed of this, please try again later.";

								application_log("error", "Unable to update permissions for proxy_id [".$PROCESSED["assigned_by"]."] who is allowing [".$PROCESSED["assigned_by"]."] accecss to their account. Database said: ".$db->ErrorMsg());
							}
						} else {
							if ($db->AutoExecute("permissions", $PROCESSED, "INSERT")) {
								$SUCCESS++;
								$SUCCESSSTR[] = "You successfully gave <strong>".html_encode($fullname)."</strong> access permissions to your account.";

								application_log("success", "Added permissions for proxy_id [".$PROCESSED["assigned_by"]."] who is allowing [".$PROCESSED["assigned_by"]."] accecss to their account from ".date(DEFAULT_DATE_FORMAT, $PROCESSED["valid_from"])." until ".date(DEFAULT_DATE_FORMAT, $PROCESSED["valid_until"]));
							} else {
								$ERROR++;
								$ERRORSTR[] = "We were unable to give <strong>".html_encode($fullname)."</strong> access permissions to your account at this time. The system administrator has been informed of this, please try again later.";

								application_log("error", "Unable to insert permissions for proxy_id [".$PROCESSED["assigned_by"]."] who is allowing [".$PROCESSED["assigned_by"]."] accecss to their account. Database said: ".$db->ErrorMsg());
							}
						}
					} else {
						$ERROR++;
						$ERRORSTR[] = "The person that have selected to add as an assistant either does not exist in this system, or their account is not currently active.<br /><br />Please contact Denise Jones in the Undergrad office (613-533-6000 x77804) to get an account for the requested individual.";
					}
				} else {
					$ERROR++;
					$ERRORSTR[] = "You cannot add yourself as your own assistant, there is no need to do so.";
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "You must enter, then select the name of the person you wish to give access to your account permissions.";
			}
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "Your account does not have the required access levels to add assistants to your profile.";

		application_log("error", "User tried to add assistants to profile without an acceptable group & role.");
	}
}

function profile_remove_assistant () {
	global $db, $PROCESSED, $ERROR, $ERRORSTR, $SUCCESS, $SUCCESSSTR, $ENTRADA_ACL, $ENTRADA_USER;
	
	if ($ENTRADA_ACL->isLoggedInAllowed('assistant_support', 'delete')) {
		if ((isset($_POST["remove"])) && (@is_array($_POST["remove"])) && (@count($_POST["remove"]))) {
			foreach ($_POST["remove"] as $assigned_to => $permission_id) {
				$permission_id = (int) trim($permission_id);
				if ($permission_id) {
					if ($db->Execute("DELETE FROM `permissions` WHERE `permission_id`=".$db->qstr($permission_id)." AND `assigned_by`=".$db->qstr($ENTRADA_USER->getID()))) {

						$SUCCESS++;
						$SUCCESSSTR[] = "You have successfully removed ".get_account_data("fullname", (int) $assigned_to)." from to accessing your permission levels.";

						application_log("success", "Removed assigned_to [".$assigned_to."] permissions from proxy_id [".$ENTRADA_USER->getID()."] account.");
					} else {
						$ERROR++;
						$ERRORSTR[] = "Unable to remove ".get_account_data("fullname", (int) $assigned_to)." from to accessing your permission levels. The system administrator has been informed of this error; however, if this is urgent, please contact us be telephone at: 613-533-6000 x74918.";

						application_log("error", "Failed to remove assigned_to [".$assigned_to."] permissions from proxy_id [".$ENTRADA_USER->getID()."] account. Database said: ".$db->ErrorMsg());
					}
				}
			}
		} else {

		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "Your account does not have the required access levels to remove assistants from your profile.";

		application_log("error", "User tried to remove assistants from profile without an acceptable group & role.");
	}
}

function profile_update_notifications() {
	global $db, $PROCESSED, $ERROR, $ERRORSTR, $SUCCESS, $SUCCESSSTR, $ENTRADA_ACL, $ENTRADA_USER;

	if ($_POST["enable-notifications"] == 1) {
		if ($_POST["notify_announcements"] && is_array($_POST["notify_announcements"])) {
			$notify_announcements = $_POST["notify_announcements"]; 
		} else {
			$notify_announcements = array();
		}
		if ($_POST["notify_events"] && is_array($_POST["notify_events"])) {
			$notify_events = $_POST["notify_events"];
		} else {
			$notify_events = array();
		}
		if ($_POST["notify_polls"] && is_array($_POST["notify_polls"])) {
			$notify_polls = $_POST["notify_polls"];
		} else {
			$notify_polls = array();
		}
		if ($_POST["notify_members"] && is_array($_POST["notify_members"])) {
			$notify_members = $_POST["notify_members"];
		} else {
			$notify_members = array();
		}
		
		$user_notifications = $db->GetOne("SELECT `notifications` FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($ENTRADA_USER->getID()));
		if (((int)$user_notifications) != 1) {
			if (!$db->Execute("UPDATE `".AUTH_DATABASE."`.`user_data` SET `notifications` = '1' WHERE `id` = ".$db->qstr($ENTRADA_USER->getID()))) {
				$ERROR++;
				application_log("error", "Notification settings for the Proxy ID [".$ENTRADA_USER->getID()."] could not be activated. Database said: ".$db->ErrorMsg());
			}
		}
		
		$query = "SELECT `community_id` FROM `community_members` WHERE `proxy_id` = ".$db->qstr($ENTRADA_USER->getID())." AND `member_active` = '1'";
		$communities = $db->GetAll($query);
		if ($communities) {
			foreach ($communities as $community) {
				$PROCESSED_NOTIFICATIONS[$community["community_id"]]["announcements"] = (isset($notify_announcements[$community["community_id"]]) && $notify_announcements[$community["community_id"]] ? 1 : 0);
				$PROCESSED_NOTIFICATIONS[$community["community_id"]]["events"] = (isset($notify_events[$community["community_id"]]) && $notify_events[$community["community_id"]] ? 1 : 0);
				$PROCESSED_NOTIFICATIONS[$community["community_id"]]["polls"] = (isset($notify_polls[$community["community_id"]]) && $notify_polls[$community["community_id"]] ? 1 : 0);
				$PROCESSED_NOTIFICATIONS[$community["community_id"]]["members"] = (isset($notify_members[$community["community_id"]]) && $notify_members[$community["community_id"]] ? 1 : 0);
			}
		}
		if ($PROCESSED_NOTIFICATIONS && is_array($PROCESSED_NOTIFICATIONS)) {
			if ($db->Execute("DELETE FROM `community_notify_members` WHERE `proxy_id` = ".$db->qstr($ENTRADA_USER->getID())." AND `notify_type` IN ('announcement', 'event', 'poll', 'members')")) {
				foreach ($PROCESSED_NOTIFICATIONS as $community_id => $notify) {
					if (!$ERROR) {
						if (!$db->Execute("	INSERT INTO `community_notify_members` 
											(`proxy_id`, `community_id`, `record_id`, `notify_type`, `notify_active`) VALUES 
											(".$db->qstr($ENTRADA_USER->getID()).", ".$db->qstr($community_id).", ".$db->qstr($community_id).", 'announcement', ".$notify["announcements"]."),
											(".$db->qstr($ENTRADA_USER->getID()).", ".$db->qstr($community_id).", ".$db->qstr($community_id).", 'event', ".$notify["events"]."),
											(".$db->qstr($ENTRADA_USER->getID()).", ".$db->qstr($community_id).", ".$db->qstr($community_id).", 'members', ".$notify["members"]."),
											(".$db->qstr($ENTRADA_USER->getID()).", ".$db->qstr($community_id).", ".$db->qstr($community_id).", 'poll', ".$notify["polls"].")")) {
							$ERROR++;
							application_log("error", "Community notifications settings for proxy ID [".$ENTRADA_USER->getID()."] could not be updated. Database said: ".$db->ErrorMsg());
						}
					}
				}
				if (!$ERROR) {
					$SUCCESS++;
					$SUCCESSSTR[] = "Your community notification settings have been successfully updated.";
				}
			} else {
				$ERROR++;
				application_log("error", "Community notifications settings for proxy ID [".$ENTRADA_USER->getID()."] could not be deleted. Database said: ".$db->ErrorMsg());
			}
		}
		if ($ERROR) {
			$ERRORSTR[] = "There was an issue while attempting to set your notification settings. The system administrator has been informed of the problem, please try again later.";	
		}
	} else {
		$user_notifications = $db->GetOne("SELECT `notifications` FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($ENTRADA_USER->getID()));
		if (((int)$user_notifications) != 0) {
			if (!$db->Execute("UPDATE `".AUTH_DATABASE."`.`user_data` SET `notifications` = '0' WHERE `id` = ".$db->qstr($ENTRADA_USER->getID()))) {
				$ERROR++;
				application_log("error", "Notification settings for the Proxy ID [".$ENTRADA_USER->getID()."] could not be deactivated. Database said: ".$db->ErrorMsg());
			}
		}
	}
}

?>