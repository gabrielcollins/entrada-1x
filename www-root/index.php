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
 * Serves as the main Entrada "public" request controller file.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/core",
    dirname(__FILE__) . "/core/includes",
    dirname(__FILE__) . "/core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

ob_start("on_checkout");

$PROCEED_TO = rtrim(((isset($_GET["url"])) ? clean_input($_GET["url"], "trim") : ((isset($_SERVER["REQUEST_URI"])) ? clean_input($_SERVER["REQUEST_URI"], "trim") : false)), "/");

$PATH_INFO = ((isset($_SERVER["PATH_INFO"])) ? clean_input($_SERVER["PATH_INFO"], array("url", "lowercase")) : "");
$PATH_SEPARATED = explode("/", $PATH_INFO);

/**
 * Process CAS authentication
 */
if ((defined("AUTH_ALLOW_CAS")) && (AUTH_ALLOW_CAS == true)) {
	if ((!isset($_SESSION["isAuthorized"])) || (!(bool) $_SESSION["isAuthorized"])) {
		if (($ACTION == "cas") || (isset($_COOKIE[AUTH_CAS_COOKIE]))) {
			phpCAS::forceAuthentication();
		}

		if (phpCAS::isSessionAuthenticated()) {
			if (isset($_SESSION[AUTH_CAS_SESSION][AUTH_CAS_ID])) {
				$result = cas_credentials($_SESSION[AUTH_CAS_SESSION][AUTH_CAS_ID]);
				if ($result) {
					$CAS_AUTHENTICATED = true;

					$username	= $result["username"];
					$password	= $result["password"];

					$ACTION		= "login";
				}
			} else {
				phpCAS::logout(ENTRADA_URL."?action=cas&state=failed");
			}
		}

		if (($ACTION == "cas") && (isset($_GET["state"])) && ($_GET["state"] == "failed")) {
			$ERROR++;
			$ERRORSTR[]	= "Your login credentials are not recognized.<br /><br />Please contact a system administrator for further information.";

			$ACTION		= "login";
		}
	}
}

if ($ACTION == "login") {
	require_once("Entrada/xoft/xoft.class.php");
	require_once("Entrada/authentication/authentication.class.php");

	if ((!defined("AUTH_ALLOW_CAS")) || (!AUTH_ALLOW_CAS) || (!$CAS_AUTHENTICATED)) {
		$username = clean_input($_POST["username"], "credentials");
		$password = clean_input($_POST["password"], "trim");

		// Check for locked-out-edness before doing anything else
		$lockout_query	= "	SELECT a.`id`, a.`login_attempts`, a.`locked_out_until`
							FROM `".AUTH_DATABASE."`.`user_access` as a
							LEFT JOIN `".AUTH_DATABASE."`.`user_data` as b
							ON b.`id` = a.`user_id`
							WHERE b.`username` = ".$db->qstr($username)."
							AND a.`app_id` = ".$db->qstr(AUTH_APP_ID);
		$lockout_result = $db->GetRow($lockout_query);
		if ($lockout_result) {
			$USER_ACCESS_ID = $lockout_result["id"];
			$LOGIN_ATTEMPTS = (isset($lockout_result["login_attempts"]) ? $lockout_result["login_attempts"] : 0);
			if (isset($lockout_result["locked_out_until"])) {
				// User has been locked out, is it still valid?
				if ($lockout_result["locked_out_until"] < time()) {
					// User's lockout has expired, remove it
					if (!$db->Execute("UPDATE `".AUTH_DATABASE."`.`user_access` SET `locked_out_until` = NULL, `login_attempts` = NULL WHERE `id` = ".$lockout_result["id"])) {
						application_log("error", "The system was unable to reset the lockout time for user [".$username."] after it expired.");
					}
				} else {
					$ERROR++;
					$ERRORSTR[] = "Your access to this system has been locked due to too many failed login attempts. You may try again at " . date("g:iA ", $lockout_result["locked_out_until"]);
					application_log("error", "User[".$username."] tried to access account after being locked out.");
				}
			}
		}

		// Check for SESSION lockout also
		if ((isset($_SESSION["auth"])) && (isset($_SESSION["auth"]["locked_out_until"]))) {
			if ($_SESSION["auth"]["locked_out_until"] < time()) {
				unset($_SESSION["auth"]["locked_out_until"]);
			} else {
				$ERROR++;
				$ERRORSTR[] = "Your access to this system has been locked due to too many failed login attempts. You may try again at " . date("g:iA ", $lockout_result["locked_out_until"]);
				application_log("error", "User[".$username."] tried to access account after being SESSION locked out.");
			}
		}

		if (isset($_SESSION["auth"]["login_attempts"]) && $_SESSION["auth"]["login_attempts"] > $LOGIN_ATTEMPTS) {
			$LOGIN_ATTEMPTS = $_SESSION["auth"]["login_attempts"];
		}
	}

	// Only even try to authorized if not locked out
	if ($ERROR == 0) {
		$auth = new AuthSystem((((defined("AUTH_DEVELOPMENT")) && (AUTH_DEVELOPMENT != "")) ? AUTH_DEVELOPMENT : AUTH_PRODUCTION));
		$auth->setAppAuthentication(AUTH_APP_ID, AUTH_USERNAME, AUTH_PASSWORD);
		$auth->setEncryption(AUTH_ENCRYPTION_METHOD);
		$auth->setUserAuthentication($username, $password, AUTH_METHOD);
		$result = $auth->Authenticate(
			array(
				"id",
				"access_id",
				"prefix",
				"firstname",
				"lastname",
				"email",
				"telephone",
				"role",
				"group",
				"organisation_id",
				"access_starts",
				"access_expires",
				"last_login",
				"privacy_level",
				"private_hash",
				"private-allow_podcasting",
				"acl"
			)
		);
	}

	if ($ERROR == 0 && $result["STATUS"] == "success") {
		if (isset($USER_ACCESS_ID)) {
			if (!$db->Execute("UPDATE `".AUTH_DATABASE."`.`user_access` SET `login_attempts` = NULL WHERE `id` = ".(int) $USER_ACCESS_ID." AND `app_id` = ".$db->qstr(AUTH_APP_ID))) {
				application_log("error", "Unable to incrememnt the login attempt counter for user [".$username."]. Database said ".$db->ErrorMsg());
			}
		}

		$GUEST_ERROR = false;
		if ($result["GROUP"] == "guest") {
			$query				= "	SELECT COUNT(*) AS total
									FROM `community_members`
									WHERE `proxy_id` = ".$db->qstr($result["ID"])."
									AND `member_active` = 1";
			$community_result	= $db->GetRow($query);
			if ((!$community_result) || ($community_result["total"] == 0)) {
				// This guest user doesn't belong to any communities, don't let them log in.
				$GUEST_ERROR = true;
			}
		}

		if (($result["ACCESS_STARTS"]) && ($result["ACCESS_STARTS"] > time())) {
			$ERROR++;
			$ERRORSTR[] = "Your access to this system does not start until ".date("r", $result["ACCESS_STARTS"]);

			application_log("error", "User[".$username."] tried to access account prior to activation date.");
		} elseif (($result["ACCESS_EXPIRES"]) && ($result["ACCESS_EXPIRES"] < time())) {
			$ERROR++;
			$ERRORSTR[] = "Your access to this system expired on ".date("r", $result["ACCESS_EXPIRES"]);

			application_log("error", "User[".$username."] tried to access account after expiration date.");
		} elseif ($GUEST_ERROR) {
			$ERROR++;
			$ERRORSTR[] = "To log in using guest credentials you must be a member of at least one community.";
			application_log("error", "Guest user[".$username."] tried to log in and isn't a member of any communities.");
		} else {
			if (function_exists("adodb_session_regenerate_id")) {
				adodb_session_regenerate_id();
			} else {
				session_regenerate_id();
			}

			application_log("access", "User[".$username."] successfully logged in.");


			// If $ENTRADA_USER was previously initialized in init.inc.php before the
			// session was authorized it is set to false and needs to be re-initialized.
			if ($ENTRADA_USER == false) {
				$ENTRADA_USER = User::get($result["ID"]);
			}

			$_SESSION["isAuthorized"] = true;
			$_SESSION["details"] = array();
			$_SESSION["details"]["app_id"] = (int) AUTH_APP_ID;
			$_SESSION["details"]["id"] = $result["ID"];
			$_SESSION["details"]["access_id"] = $result["ACCESS_ID"];
			$_SESSION["details"]["username"] = $username;
			$_SESSION["details"]["prefix"] = $result["PREFIX"];
			$_SESSION["details"]["firstname"] = $result["FIRSTNAME"];
			$_SESSION["details"]["lastname"] = $result["LASTNAME"];
			$_SESSION["details"]["email"] = $result["EMAIL"];
			$_SESSION["details"]["telephone"] = $result["TELEPHONE"];
			$_SESSION["details"]["role"] = $result["ROLE"];
			$_SESSION["details"]["group"] = $result["GROUP"];
			$_SESSION["details"]["organisation_id"] = $result["ORGANISATION_ID"];
			$_SESSION["details"]["expires"] = $result["ACCESS_EXPIRES"];
			$_SESSION["details"]["lastlogin"] = $result["LAST_LOGIN"];
			$_SESSION["details"]["privacy_level"] = $result["PRIVACY_LEVEL"];
			$_SESSION["details"]["private_hash"] = $result["PRIVATE_HASH"];
			$_SESSION["details"]["allow_podcasting"] = false;

			if ((isset($ENTRADA_CACHE)) && (!DEVELOPMENT_MODE)) {
				if (!($ENTRADA_CACHE->test("acl_"  . AUTH_APP_ID . "_" . $ENTRADA_USER->getID()))) {
					$ENTRADA_ACL = new Entrada_Acl($_SESSION["details"]);
					$ENTRADA_CACHE->save($ENTRADA_ACL, "acl_" . AUTH_APP_ID . "_" . $ENTRADA_USER->getID());
				} else {
					$ENTRADA_ACL = $ENTRADA_CACHE->load("acl_" . AUTH_APP_ID . "_" . $ENTRADA_USER->getID());
				}
			} else {
				$ENTRADA_ACL = new Entrada_Acl($_SESSION["details"]);
			}

			if (isset($result["PRIVATE-ALLOW_PODCASTING"])) {
				if ((int) trim($result["PRIVATE-ALLOW_PODCASTING"])) {
					$_SESSION["details"]["allow_podcasting"] = (int) trim($result["PRIVATE-ALLOW_PODCASTING"]);
				} elseif (trim(strtolower($result["PRIVATE-ALLOW_PODCASTING"])) == "all") {
					$_SESSION["details"]["allow_podcasting"] = "all";
				}
			}

			/**
			 * Any custom session information that needs to be set on a per-group basis.
			 */
			switch ($_SESSION["details"]["group"]) {
				case "student" :
					if ((!isset($result["ROLE"])) || (!clean_input($result["ROLE"], "alphanumeric"))) {
						$_SESSION["details"]["grad_year"] = fetch_first_year();
					} else {
						$_SESSION["details"]["grad_year"] = $result["ROLE"];
					}
				break;
				case "medtech" :
					/**
					 * If you're in MEdTech, always assign a graduating year,
					 * because we normally see more than normal users.
					 */
					$_SESSION["details"]["grad_year"] = fetch_first_year();
				break;
				case "staff" :
				case "faculty" :
				default :
					continue;
				break;
			}

			$_SESSION["permissions"] = permissions_load();

			$auth->updateLastLogin();
		}

		$query = "SELECT `email_updated`, `google_id`, `notifications` FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($ENTRADA_USER->getID());
		$result	= $db->GetRow($query);
		if (($result) && ($result["google_id"])) {
			$_SESSION["details"]["google_id"] = $result["google_id"];
		} else {
			$_SESSION["details"]["google_id"] = false;
		}

		if ($result) {
			$_SESSION["details"]["notifications"] = $result["notifications"];
			if (!isset($result["email_updated"]) || $result["email_updated"] == "" || (($result["email_updated"] - mktime()) / 86400 >= 365)) {
				$_SESSION["details"]["email_updated"] = false;
			} else {
				$_SESSION["details"]["email_updated"] = true;
			}
		}

		if ((!(int) $_SESSION["details"]["privacy_level"]) || (((bool) $GOOGLE_APPS["active"]) && (in_array($_SESSION["details"]["group"], $GOOGLE_APPS["groups"])) && (!$_SESSION["details"]["google_id"]))) {
			/**
			 * They need to be re-directed to the firstlogin module.
			 */
			$PATH_SEPARATED[1] = "firstlogin";
			$MODULE = "firstlogin";

			if (((bool) $GOOGLE_APPS["active"]) && (in_array($_SESSION["details"]["group"], $GOOGLE_APPS["groups"])) && (!$_SESSION["details"]["google_id"])) {
				$_SESSION["details"]["google_id"] = "opt-in";
			}
		} elseif ($PROCEED_TO) {
			header("Location: ".((isset($_SERVER["HTTPS"])) ? "https" : "http")."://".$_SERVER["HTTP_HOST"].clean_input(rawurldecode($PROCEED_TO), array("nows", "url")));
			exit;
		}
	} else {
		/**
		 * There can only be auth errors if not already locked out, so only fandangle this stuff
		 * if no errors have been encountered before trying to authenticate.
		 */
		if ($ERROR == 0) {
			$remaining_attempts = (AUTH_MAX_LOGIN_ATTEMPTS - $LOGIN_ATTEMPTS);

			$ERROR++;
			$ERRORSTR[$ERROR] = $result["MESSAGE"];

			if ($remaining_attempts == 0) {
				$ERRORSTR[$ERROR] .= "<br /><br />This is your <strong>last login attempt</strong> before your account is locked for ".round((AUTH_LOCKOUT_TIMEOUT / 60))." minutes.";
			} elseif ($remaining_attempts <= (AUTH_MAX_LOGIN_ATTEMPTS - 1)) {
				$ERRORSTR[$ERROR] .= "<br /><br />You have <strong>".$remaining_attempts." attempt".(($remaining_attempts != 1) ? "s" : "")."</strong> remaining before your account is locked for ".round((AUTH_LOCKOUT_TIMEOUT / 60))." minutes.";
			}

			application_log("access", $result["MESSAGE"]);

			if (isset($USER_ACCESS_ID)) {
				if ($LOGIN_ATTEMPTS >= AUTH_MAX_LOGIN_ATTEMPTS) {
					// Lock this user out
					if (!$db->Execute("UPDATE `".AUTH_DATABASE."`.`user_access` SET `locked_out_until` = ".(time()+AUTH_LOCKOUT_TIMEOUT).", `login_attempts` = NULL  WHERE `id` = ".$USER_ACCESS_ID)) {
						application_log("error", "Unable to incrememnt the login attempt counter for user [".$username."]. Database said ".$db->ErrorMsg());
					}
				} else {
					if (!$db->Execute("UPDATE `".AUTH_DATABASE."`.`user_access` SET `login_attempts` = ".($LOGIN_ATTEMPTS+1)." WHERE `id`=".$USER_ACCESS_ID)) {
						application_log("error", "Unable to incrememnt the login attempt counter for user [".$username."]. Database said ".$db->ErrorMsg());
					}
				}
			} else {
				if ((isset($_SESSION["auth"])) && (isset($_SESSION["auth"]["login_attempts"]))) {
					if ($_SESSION["auth"]["login_attempts"] >= AUTH_MAX_LOGIN_ATTEMPTS) {
						$_SESSION["auth"]["login_attempts"] = 0;
						$_SESSION["auth"]["locked_out_until"] = (time() + AUTH_LOCKOUT_TIMEOUT);
					} else {
						$_SESSION["auth"]["login_attempts"]++;
					}
				} else {
					$_SESSION["auth"]["login_attempts"] = 1;
				}
			}
		}
	}

	unset($result, $username, $password);

} elseif ($ACTION == "logout") {
	users_online("logout");

	$_SESSION = array();
	unset($_SESSION);
	session_destroy();

	if ((defined("AUTH_ALLOW_CAS")) && (AUTH_ALLOW_CAS == true)) {
		phpCAS::logout(ENTRADA_URL);
	}

	header("Location: ".ENTRADA_URL);
	exit;
}

if (isset($_SESSION["isAuthorized"]) && (bool) $_SESSION["isAuthorized"] && isset($ENTRADA_USER)) {
	if (($cached_user = $ENTRADA_CACHE->load("user_".AUTH_APP_ID."_".$ENTRADA_USER->getID())) && $cached_user != $ENTRADA_USER) {
		$ENTRADA_CACHE->save($ENTRADA_USER, "user_".AUTH_APP_ID."_".$ENTRADA_USER->getID(), array("auth"), 300);
	}
}

if ((!isset($_SESSION["isAuthorized"])) || (!(bool) $_SESSION["isAuthorized"])) {
	if (isset($PATH_SEPARATED[1])) {
		switch ($PATH_SEPARATED[1]) {
			case "confirm_observership" :
				$MODULE = "confirm_observership";
			break;
			case "privacy_policy" :
				$MODULE = "privacy_policy";
			break;
			case "help" :
				$MODULE = "help";
			break;
			default :
				$MODULE = "login";
			break;
		}
	}
} else {
	if (($_SESSION["details"]["expires"] && ($_SESSION["details"]["expires"] <= time())) || !isset($_SESSION["details"]["app_id"]) || ($_SESSION["details"]["app_id"] != AUTH_APP_ID)) {
		header("Location: ".ENTRADA_URL."/?action=logout");
		exit;
	}

	/**
	 * This function controls setting the permission masking feature.
	 */
	permissions_mask();

	/**
	 * This function updates the users_online table.
	 */
	users_online();

	/**
	 * This section of code sets the $MODULE variable.
	 */
	if ((isset($PATH_SEPARATED[1])) && (trim($PATH_SEPARATED[1]) != "")) {
		$MODULE = $PATH_SEPARATED[1]; // This is sanitized when $PATH_SEPARATED is created.
	} else {
		$MODULE = "dashboard"; // This is the default file that will be launched upon successful login.
	}

	/**
	 * This section of code sets the $SUBMODULE variable.
	 */
	if ((isset($PATH_SEPARATED[2])) && (trim($PATH_SEPARATED[2]) != "")) {
		$SUBMODULE = $PATH_SEPARATED[2]; // This is sanitized when $PATH_SEPARATED is created.
	} else {
		$SUBMODULE = false; // This is the default file that will be launched upon successful login.
	}

	/**
	 * This is a simple re-direct to catch admin without slash on the end.
	 */
	if ($MODULE == "admin") {
		header("Location: ".ENTRADA_URL."/admin/");
		exit;
	}

	/**
	 * This sends guests on their way to their communities and prevents them from seeing any other part of the site.
	 */
	if ((($MODULE !== "communities") || ((!isset($_GET["section"])) || ($_GET["section"] != "leave"))) && ($_SESSION["details"]["group"] == "guest") && ($_SESSION["details"]["role"] == "communityinvite")) {
		$query	= "	SELECT a.`community_id`, b.`community_url`
					FROM `community_members`AS a
					LEFT JOIN `communities` AS b
					ON a.`community_id` = b.`community_id`
					WHERE a.`proxy_id` = ".$db->qstr($ENTRADA_USER->getID())."
					AND a.`member_active` = 1
					ORDER BY a.`member_joined`";
		$result	= $db->GetRow($query);
		if ($result) {
			/**
			 * This guest belongs to at least one community
			 */
			header("Location: ".ENTRADA_URL."/community".$result["community_url"]);
			exit;
		} elseif (isset($_SESSION["isAuthorized"]) && $_SESSION["isAuthorized"] == true) {
			header("Location: ".ENTRADA_URL."/?action=logout");
			exit;
		}
	}

	/**
	 * This section of code is only activated if the user is changing their privacy_level.
	 * The real work is actually done in modules/public/profile.inc.php; however, I need the
	 * session data to be properly set so the page tabs display the correct information.
	 */
	if ((isset($_POST["privacy_level"])) && ($privacy_level = (int) trim($_POST["privacy_level"]))) {
		if ($privacy_level > MAX_PRIVACY_LEVEL) {
			$privacy_level = MAX_PRIVACY_LEVEL;
		}

		$_SESSION["details"]["privacy_level"] = $privacy_level;
	}
}

/**
 * Make sure that the login page is accessed via SSL if either the AUTH_FORCE_SSL is not defined in
 * the settings.inc.php file or it's set to true.
 */
if (($MODULE == "login") && (!isset($_SERVER["HTTPS"])) && ((!defined("AUTH_FORCE_SSL")) || (AUTH_FORCE_SSL))) {
	header("Location: ".str_replace("http://", "https://", strtolower(ENTRADA_URL)."/?url=".rawurlencode($PROCEED_TO)));
	exit;
}

define("PARENT_INCLUDED", true);

require_once (ENTRADA_ABSOLUTE."/templates/".$ENTRADA_ACTIVE_TEMPLATE."/layouts/public/header.tpl.php");

switch ($MODULE) {
	case "confirm_observership" :
		require_once(ENTRADA_ABSOLUTE.DIRECTORY_SEPARATOR."default-pages".DIRECTORY_SEPARATOR."confirm_observership.inc.php");
	break;
	case "privacy_policy" :
		require_once(ENTRADA_ABSOLUTE.DIRECTORY_SEPARATOR."default-pages".DIRECTORY_SEPARATOR."privacy_policy.inc.php");
	break;
	case "help" :
		require_once(ENTRADA_ABSOLUTE.DIRECTORY_SEPARATOR."default-pages".DIRECTORY_SEPARATOR."help.inc.php");
	break;
	case "login" :
		require_once(ENTRADA_ABSOLUTE.DIRECTORY_SEPARATOR."default-pages".DIRECTORY_SEPARATOR."login.inc.php");
	break;
	default :
		/*
		$excused_proxy_ids = array();
		if ($_SESSION["details"]["group"] == "student" && $MODULE != "evaluations" && !in_array($ENTRADA_USER->getID(), $excused_proxy_ids)) {
			$cohort = groups_get_cohort($ENTRADA_USER->getID());
			$query = "SELECT * FROM `evaluations` AS a
						JOIN `evaluation_evaluators` AS b
						ON a.`evaluation_id` = b.`evaluation_id`
						WHERE
						(
							(
								b.`evaluator_type` = 'proxy_id'
								AND b.`evaluator_value` = ".$db->qstr($ENTRADA_USER->getID())."
							)
							OR
							(
								b.`evaluator_type` = 'organisation_id'
								AND b.`evaluator_value` = ".$db->qstr($_SESSION["details"]["organisation_id"])."
							)".($_SESSION["details"]["group"] == "student" ? " OR (
								b.`evaluator_type` = 'cohort'
								AND b.`evaluator_value` = ".$db->qstr($cohort["group_id"])."
							)" : "")."
						)
						AND a.`evaluation_finish` < ".$db->qstr(time())."
						AND a.`evaluation_active` = 1
						GROUP BY a.`evaluation_id`
						ORDER BY a.`evaluation_finish` ASC";

			$evaluations = $db->GetAll($query);
			if ($evaluations) {
				foreach ($evaluations as $evaluation) {
					require_once("Models/evaluation/Evaluation.class.php");
					$overdue = Evaluation::getUserOverdueEvaluation($evaluation, array("eevaluator_id" => $evaluation["eevaluator_id"]), array("id" => $ENTRADA_USER->getID()));
					if ($overdue) {
						header("Location: ".ENTRADA_URL."/evaluations?section=attempt&id=".$evaluation["evaluation_id"]);
						exit;
					}
				}
			}
		}
		*/

		/**
		 * Initialize Entrada_Router so it can load the requested modules.
		 */
		$router = new Entrada_Router();
		$router->setBasePath(ENTRADA_CORE.DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."public");
		$router->setSection($SECTION);

		if (($router) && ($route = $router->initRoute($MODULE))) {
			/**
			 * Responsible for displaying the permission masks sidebar item
			 * if they have more than their own permission set available.
			 */
			if ((isset($_SESSION["permissions"])) && (is_array($_SESSION["permissions"])) && (count($_SESSION["permissions"]) > 1)) {
				$sidebar_html  = "<form id=\"masquerade-form\" action=\"".ENTRADA_URL."\" method=\"get\">\n";
				$sidebar_html .= "<label for=\"permission-mask\">Available permission masks:</label><br />";
				$sidebar_html .= "<select id=\"permission-mask\" name=\"mask\" style=\"width: 100%\" onchange=\"window.location='".ENTRADA_URL."/".$MODULE."/?".str_replace("&#039;", "'", replace_query(array("mask" => "'+this.options[this.selectedIndex].value")))."\">\n";
				$display_masks = false;
				$added_users = array();
				foreach ($_SESSION["permissions"] as $access_id => $result) {
					if (is_int($access_id) && ((isset($result["mask"]) && $result["mask"]) || $access_id == $ENTRADA_USER->getDefaultAccessId()) && array_search($result["id"], $added_users) === false) {
						if (isset($result["mask"]) && $result["mask"]) {
							$display_masks = true;
						}
						$added_users[] = $result["id"];
						$sidebar_html .= "<option value=\"".(($access_id == $ENTRADA_USER->getDefaultAccessId()) ? "close" : $result["permission_id"])."\"".(($result["id"] == $ENTRADA_USER->getActiveId()) ? " selected=\"selected\"" : "").">".html_encode($result["fullname"]) . "</option>\n";
					}
				}
				$sidebar_html .= "</select>\n";
				$sidebar_html .= "</form>\n";
				if ($display_masks) {
					new_sidebar_item("Permission Masks", $sidebar_html, "permission-masks", "open");
				}
				unset($query);
			}

			$module_file = $router->getRoute();
			if ($module_file) {
				require_once($module_file);
			}
		} else {
			$url = ENTRADA_URL;
			application_log("error", "The Entrada_Router failed to load a request. The user was redirected to [".$url."].");

			header("Location: ".$url);
			exit;
		}
	break;
}

require_once(ENTRADA_ABSOLUTE."/templates/".$ENTRADA_ACTIVE_TEMPLATE."/layouts/public/footer.tpl.php");

/**
 * Add the Feedback Sidebar Window.
 * @todo Change this to be on the right hand side of every page in the bottom
 * right corner, even as you scroll, like many other sites & applications.
 *
 */
if ((isset($_SESSION["isAuthorized"])) && ($_SESSION["isAuthorized"])) {
	add_task_sidebar();

	system_feedback_sidebar($ENTRADA_USER->getGroup());

	/**
	 * Create the Organisation side bar.
	 * If the org request attribute is set then change the current org id for this user.
	 */
	if (($ENTRADA_USER->getAllOrganisations() && count($ENTRADA_USER->getAllOrganisations()) > 1) || ($ENTRADA_USER->getOrganisationGroupRole() && max(array_map('count', $ENTRADA_USER->getOrganisationGroupRole())) > 1)) {
		$org_group_role = $ENTRADA_USER->getOrganisationGroupRole();
		$sidebar_html = "<ul class=\"menu none\">\n";
		foreach ($ENTRADA_USER->getAllOrganisations() as $key => $organisation_title) {
			if ($key == $ENTRADA_USER->getActiveOrganisation()) {
				$sidebar_html .= "<li><a href=\"" . ENTRADA_URL . "/" . $MODULE . "/" . "?organisation_id=" . $key . "\"><img src=\"".ENTRADA_RELATIVE."/images/checkbox-on.gif\" alt=\"\" /> <span>" . html_encode($organisation_title) . "</span></a></li>\n";
				if ($org_group_role && !empty($org_group_role)) {
					foreach($org_group_role[$key] as $group_role) {
						if ($group_role["access_id"] == $ENTRADA_USER->getAccessId()) {
							$sidebar_html .= "<li style=\"padding-left: 15px;\"><a href=\"" . ENTRADA_URL . "/" . $MODULE . "/" . "?" . replace_query(array("organisation_id" => $key, "ua_id" => $group_role["access_id"])) . "\"><img src=\"".ENTRADA_RELATIVE."/images/checkbox-on.gif\" alt=\"\" /> <span>" . html_encode(ucfirst($group_role["group"]) . " - " . ucfirst($group_role["role"])) . "</span></a></li>\n";
						} else {
							$sidebar_html .= "<li style=\"padding-left: 15px;\"><a href=\"" . ENTRADA_URL . "/" . $MODULE . "/" . "?" . replace_query(array("organisation_id" => $key, "ua_id" => $group_role["access_id"])) . "\"><img src=\"".ENTRADA_RELATIVE."/images/checkbox-off.gif\" alt=\"\" /> <span>" . html_encode(ucfirst($group_role["group"]) . " - " . ucfirst($group_role["role"])) . "</span></a></li>\n";						}
					}
				}
			} else {
				$sidebar_html .= "<li><a href=\"" . ENTRADA_URL . "/" . $MODULE . "/" . "?organisation_id=" . $key . "\"><img src=\"".ENTRADA_RELATIVE."/images/checkbox-off.gif\" alt=\"\" /> <span>" . html_encode($organisation_title) . "</span></a></li>\n";
			}
		}
		$sidebar_html .= "</ul>\n";
		new_sidebar_item("Organisations", $sidebar_html, "org-switch", "open", SIDEBAR_PREPEND);
	}
}