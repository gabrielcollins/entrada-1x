<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Controller file responsible for serving all communities and directing all
 * requests to the correct file.
 * 
 * @author Organisation: Queen's University
 * @author Unit: Medical Education Technology Unit
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
 * $Id: index.php 1191 2010-05-13 17:11:26Z hbrundage $
*/

@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    dirname(__FILE__) . "/../core/includes",
    dirname(__FILE__) . "/../core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

require_once("Entrada/smarty/Smarty.class.php");

ob_start("on_checkout");

$PAGE_ID = 0;
$PAGE_CONTENT = "";
$PAGE_PROTECTED = false;
$PAGE_ACTIVE = false;
$MENU_TITLE = "";

$COMMUNITY_ID = 0;
$COMMUNITY_URL = "";
$COMMUNITY_TEMPLATE	= "default";	// The default template (in the templates directory) to load.
$COMMUNITY_THEME = "default";		// Optioanl default theme to load within a template.

$COMMUNITY_PAGES = array();
$COMMUNITY_MODULE = "default";		// Default module to load when a community starts.
$HOME_PAGE = false;
					
$MODULE_ID = 0;
$MODULE_TITLE = "";
$MODULE_PERMISSIONS = array();

if (!isset($RECORD_ID)) {
	$RECORD_ID = 0;
}

$COMMUNITY_LOAD = false;			// Security setting stating that the community should not load.
$LOGGED_IN = (((isset($_SESSION["isAuthorized"])) && ((bool) $_SESSION["isAuthorized"])) ? true : false);

$COMMUNITY_MEMBER_SINCE = 0;		// Unix timestamp of date that they joined the community.
$COMMUNITY_MEMBER = false;			// Users are not members by defalt.
$COMMUNITY_ADMIN = false;			// Users are not community administrators by default.

$PROCEED_TO = ((isset($_GET["url"])) ? trim($_GET["url"]) : ((isset($_SERVER["REQUEST_URI"])) ? trim($_SERVER["REQUEST_URI"]) : false));

/**
 * For backwards compatibility so pre-1.0 links still work properly.
 */
if ($ACTION && !isset($_GET["section"])) {
	$SECTION = $ACTION;
}

/**
 * Check for PATH_INFO to process the url and get the module.
 */
if (isset($_SERVER["PATH_INFO"])) {
	$tmp_page = "";
	$tmp_url = array();
	$path_info = explode(":", clean_input($_SERVER["PATH_INFO"], array("trim", "lower")));

	/**
	 * Check if there is any path details provided
	 */
	if ((isset($path_info[0])) && ($tmp_path = explode("/", $path_info[0])) && (is_array($tmp_path))) {
		foreach ($tmp_path as $directory) {
			$directory = clean_input($directory, array("trim", "credentials"));
			if ($directory) {
				$tmp_url[] = $directory;
			}
		}

		if ((is_array($tmp_url)) && (count($tmp_url))) {
			$COMMUNITY_URL = "/".implode("/", $tmp_url);
		}
	}

	/**
	 * Check if there is a requested page. This is done by looking for the colon set in the path_info.
	 */
	if ((isset($path_info[1])) && ($tmp_page = clean_input($path_info[1], array("trim")))) {
		$PAGE_URL = $tmp_page;
	}
}

$query = "	SELECT a.`community_protected`, b.`allow_public_view`
			FROM `communities` AS a
			LEFT JOIN `community_pages` AS b
			ON b.`community_id` = a.`community_id`
			WHERE `community_url` = ".$db->qstr($COMMUNITY_URL)."
			AND `page_url` = ".$db->qstr($PAGE_URL);
$page_permissions = $db->GetRow($query);

$PAGE_PROTECTED = ($page_permissions && ($page_permissions["community_protected"] == 1 || $page_permissions["allow_public_view"] == 0) ? true : false);

if (!$LOGGED_IN && (isset($_GET["auth"]) && $_GET["auth"] == "true")) {
	if (!isset($_SERVER["PHP_AUTH_USER"])) {
		http_authenticate();
	} else {
		require_once("Entrada/authentication/authentication.class.php");
	
		$username = clean_input($_SERVER["PHP_AUTH_USER"], "credentials");
		$password = clean_input($_SERVER["PHP_AUTH_PW"], "trim");
	
		$auth = new AuthSystem((((defined("AUTH_DEVELOPMENT")) && (AUTH_DEVELOPMENT != "")) ? AUTH_DEVELOPMENT : AUTH_PRODUCTION));	
		$auth->setAppAuthentication(AUTH_APP_ID, AUTH_USERNAME, AUTH_PASSWORD);
		$auth->setEncryption(AUTH_ENCRYPTION_METHOD);
		$auth->setUserAuthentication($username, $password, AUTH_METHOD);
		$result = $auth->Authenticate(array("id", "firstname", "lastname", "email", "role", "group", "username", "prefix". "telephone", "expires", "lastlogin", "privacy_level"));
	
		$ERROR = 0;
		if ($result["STATUS"] == "success") {
			if (($result["ACCESS_STARTS"]) && ($result["ACCESS_STARTS"] > time())) {
				$ERROR++;
				application_log("error", "User[".$username."] tried to access account prior to activation date.");
			} elseif (($result["ACCESS_EXPIRES"]) && ($result["ACCESS_EXPIRES"] < time())) {
				$ERROR++;
				application_log("error", "User[".$username."] tried to access account after expiration date.");
			} else {
				$_SESSION["isAuthorized"] = true;
				$_SESSION["details"]["app_id"] = AUTH_APP_ID;
				$_SESSION["details"]["id"] = $result["ID"];
				$_SESSION["details"]["firstname"] = $result["FIRSTNAME"];
				$_SESSION["details"]["lastname"] = $result["LASTNAME"];
				$_SESSION["details"]["email"] = $result["EMAIL"];
				$_SESSION["details"]["role"] = $result["ROLE"];
				$_SESSION["details"]["group"] = $result["GROUP"];
				$_SESSION["details"]["privacy_level"] = $result["PRIVACY_LEVEL"];

				$query = "	SELECT * FROM
							`".AUTH_DATABASE."`.`user_data` AS a
							JOIN `".AUTH_DATABASE."`.`user_access` AS b
							ON a.`id` = b.`user_id`
							AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
							WHERE a.`id` = ".$db->qstr($_SESSION["details"]["id"]);
				$userinfo = $db->GetRow($query);
				if ($userinfo) {
					$_SESSION["details"]["username"] = $userinfo["username"];
					$_SESSION["details"]["expires"] = $userinfo["access_expires"];
					$_SESSION["details"]["lastlogin"] = $userinfo["last_login"];
					$_SESSION["details"]["telephone"] = $userinfo["telephone"];
					$_SESSION["details"]["prefix"] = $userinfo["prefix"];
					$_SESSION["details"]["notifications"] = $userinfo["notifications"];
				}
			}
		} else {
			$ERROR++;
			application_log("access", $result["MESSAGE"]);
		}
		
		if ($ERROR) {
			http_authenticate();
		}
		unset($username, $password);
	}
}

/**
 * Setup Smarty template engine.
 */
$smarty = new Smarty();
$smarty->template_dir = COMMUNITY_ABSOLUTE."/templates/".$COMMUNITY_TEMPLATE;
$smarty->compile_dir = CACHE_DIRECTORY;
$smarty->compile_id = md5($smarty->template_dir);
$smarty->cache_dir = CACHE_DIRECTORY;

/**
 * Check if the community url has been set by the above code.
 */
if ($COMMUNITY_URL) {
	$query = "SELECT * FROM `communities` WHERE `community_url` = ".$db->qstr($COMMUNITY_URL);
	$community_details = $db->GetRow($query);
	if (($community_details) && ($COMMUNITY_ID = (int) $community_details["community_id"])) {
		if ((int) $community_details["community_active"]) {
			if (isset($PAGE_URL)) {
				switch ($PAGE_URL) {
					case "pages" :
						$COMMUNITY_MODULE = "pages";
						$PAGE_ACTIVE = true;
					break;
					case "members" :
						$COMMUNITY_MODULE = "members";
						$PAGE_ACTIVE = true;
					break;
					default :
						$query = "SELECT `cpage_id`, `page_type`, `page_content`, `page_active`, `menu_title` FROM `community_pages` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `page_url` = ".$db->qstr($PAGE_URL);
						$result = $db->GetRow($query);
						if ($result) {
							$PAGE_ID = $result["cpage_id"];
							$COMMUNITY_MODULE = $result["page_type"];
							$MENU_TITLE = $result["menu_title"];
							if (((int)$result["page_active"]) == '1') {
								$PAGE_ACTIVE = true;
								if ($COMMUNITY_MODULE == "url") {
									header("Location: ".$result["page_content"]);
									exit;
								}
							}
						} else {
							$query = "SELECT `page_type`, `page_url`, `page_active`, `menu_title` FROM `community_pages` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `page_type` = ".$db->qstr(($PAGE_URL == "calendar" ? "events" : $PAGE_URL))." ORDER BY `page_order` ASC";
							$result	= $db->GetRow($query);
							if ($result) {
								if (((int)$result["page_active"]) == '1') { 
									$PAGE_ACTIVE = true;
									$PAGE_ID = $result["cpage_id"];
									$COMMUNITY_MODULE = $result["page_type"];
									$MENU_TITLE = $result["menu_title"];
									$PAGE_URL = $result["page_url"];
								}
							} else {
								$COMMUNITY_MODULE = "default";
							}
						}
					break;
				}
			} else {
				$query = "SELECT `cpage_id`, `page_type`, `page_content`, `menu_title` FROM `community_pages` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `page_url` = ''";
				$result	= $db->GetRow($query);
				if ($result) {
					$PAGE_ID = $result["cpage_id"];
					$COMMUNITY_MODULE = $result["page_type"];
					$MENU_TITLE = $result["menu_title"];
					$PAGE_ACTIVE = true;
					$HOME_PAGE = true;
					if ($COMMUNITY_MODULE == "url") {
						header("Location: ".$result["page_content"]);
						exit;
					}
				}
			}
			
			
			$default_course_pages = array(	"teaching_strategies",
											"prerequisites",
											"course_aims",
											"assessment_strategies",
											"assessment_strategies/course_integration",
											"resources",
											"expectations_of_students",
											"expectations_of_faculty");
			if ($COMMUNITY_MODULE == "course" && array_search($PAGE_URL, $default_course_pages) !== false) {
				$COMMUNITY_MODULE = "default";
			}

			if ($PAGE_ID) {
				$query = "SELECT * FROM `community_page_options` WHERE `cpage_id` = ".$db->qstr($PAGE_ID);

				$results = $db->GetAll($query);

				if ($results) {
					foreach ($results as $result) {
						$PAGE_OPTIONS[$result["option_title"]] = $result["option_value"];
					}
				}
			}

			if (((int) $community_details["community_protected"]) && (!$LOGGED_IN)) {
				/**
				 * This is a protected community and user is not currently authenticated.
				 * Send the user to the login page, and provide the url variable so they return here when finished.
				 */
				header("Location: ".ENTRADA_URL."/?url=".rawurlencode($PROCEED_TO));
				exit;
			} else {
				
				/**
				 * Check if they are currently authenticated, if they are lets see if they are a member and / or admin user.
				 */
				if ($LOGGED_IN) {
					/**
					 * This initializes the $USER_ACCESS variable to 1; for the access of a "troll"
					 */
					$USER_ACCESS = 1;
					
					
					/**
					 * This function controls setting the permission masking feature.
					 */
					permissions_mask();

					/**
					 * This function updates the users_online table.
					 */
					users_online();
					$query	= "SELECT * FROM `community_members` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `proxy_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])." AND `member_active` = '1'";
					$result	= $db->GetRow($query);
					if ($result) {
						$COMMUNITY_MEMBER = true;
						$COMMUNITY_MEMBER_SINCE = $result["member_joined"];

						if ($result["member_acl"] == 1) {
							/**
							 * $USER_ACCESS variable to 3; for the access of a community administrator.
							 */
							$USER_ACCESS = 3;
							
							$COMMUNITY_ADMIN = true;
						} else {
							/**
							 * $USER_ACCESS variable to 2; for the access of a community member.
							 */
							$USER_ACCESS = 2;
						}
					}
				} else {
					$USER_ACCESS = 0;
				}

				/**
				 * Check if the template is set in the database.
				 */
				if ((isset($community_details["community_template"])) && (is_dir(ENTRADA_ABSOLUTE."/community/templates/".$community_details["community_template"]))) {
					$COMMUNITY_TEMPLATE = $community_details["community_template"];
					$smarty->template_dir = COMMUNITY_ABSOLUTE."/templates/".$COMMUNITY_TEMPLATE;
					$smarty->compile_id = md5($smarty->template_dir);
				}

				/**
				 * Get a list of modules which are enabled.
				 */
				$COMMUNITY_MODULES = communities_fetch_modules($COMMUNITY_ID);
				$COMMUNITY_PAGES = communities_fetch_pages($COMMUNITY_ID, $USER_ACCESS);
				
				/**
				 * Loading Prototype
				 */
				$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/scriptaculous/prototype.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
				$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/scriptaculous/scriptaculous.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
				$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/common.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
				$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/windows/window.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
				$HEAD[] = "<link href=\"".ENTRADA_URL."/css/windows/default.css\" rel=\"stylesheet\" type=\"text/css\" />";
				$HEAD[] = "<link href=\"".ENTRADA_URL."/css/windows/medtech.css\" rel=\"stylesheet\" type=\"text/css\" />";

				/**
				 * Start another output buffer to collect the page contents.
				 */
				ob_start();
				if ((!(int) $community_details["community_protected"]) && (!$LOGGED_IN)) {
					/**
					 * Since this community is not protected, and the user is not logged in, load
					 * the community, which should be in read-only mode.
					 */
					$COMMUNITY_LOAD = true;
				} else {
					if (!$COMMUNITY_MEMBER) {
						$ALLOW_MEMBERSHIP = true;
						switch ($community_details["community_registration"]) {
							case 0 :	// Open Community
							case 1 :	// Open Registration
								continue;
							break;
							case 2 :	// Selected Group Registration
								$ALLOW_MEMBERSHIP = false;

								if (($community_details["community_members"] != "") && ($community_members = @unserialize($community_details["community_members"])) && (is_array($community_members)) && (count($community_members))) {
									if (in_array($_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"], $community_members)) {
										$ALLOW_MEMBERSHIP = true;
									} else {
										foreach ($community_members as $member_group) {
											if ($member_group) {
												$pieces = explode("_", $member_group);
			
												if ((isset($pieces[0])) && ($group = trim($pieces[0]))) {
													if ($_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"] == $group) {
														if ((isset($pieces[1])) && ($role = trim($pieces[1]))) {
															if ($_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"] == $role) {
																$ALLOW_MEMBERSHIP = true;
																break;
															}
														} else {
															$ALLOW_MEMBERSHIP = true;
															break;
														}
													}
												}
											}
										}
									}
								}
							break;
							case 3 :	// Selected Community Registration
								$ALLOW_MEMBERSHIP = false;
			
								if (($community_details["community_members"] != "") && ($community_members = @unserialize($community_details["community_members"])) && (is_array($community_members)) && (count($community_members))) {
									$query	= "SELECT * FROM `community_members` WHERE `proxy_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])." AND `member_active` = '1' AND `community_id` IN ('".implode("', '", $community_members)."')";
									$result	= $db->GetRow($query);
									if ($result) {
										$ALLOW_MEMBERSHIP = true;
									}
								}
							break;
							case 4 : // Private Community Registration
								$ALLOW_MEMBERSHIP = false;
							break;
							default :
							break;
						}
						if (((int) $community_details["community_protected"]) && ((int) $community_details["community_registration"])) {
							header("Location: ".ENTRADA_URL."/communities?section=join&community=".$community_details["community_id"]);
							exit;
						}
						if ($ALLOW_MEMBERSHIP) {
							new_sidebar_item("Join Community", "Join this community to access more community features.<div style=\"margin-top: 10px; text-align: center\"><a href=\"".ENTRADA_URL."/communities?section=join&community=".$community_details["community_id"]."\" style=\"font-weight: bold\">Click here to join</a></div>", "join-page-box", "open");
						}
					}
					$COMMUNITY_LOAD = true;
				}

				/**
				 * If everything is good to go, load the community page.
				 */
				if ($COMMUNITY_LOAD) {
					if (@file_exists(ENTRADA_ABSOLUTE."/community/templates/".$COMMUNITY_TEMPLATE."/includes/config.inc.php")) {
						require_once(ENTRADA_ABSOLUTE."/community/templates/".$COMMUNITY_TEMPLATE."/includes/config.inc.php");
					}

					/**
					 * Responsible for displaying the permission masks sidebar item
					 * if they have more than their own permission set available.
					 */
					if (($LOGGED_IN) && (isset($_SESSION["permissions"])) && (@is_array($_SESSION["permissions"])) && (@count($_SESSION["permissions"]) > 1)) {
						$sidebar_html  = "<form id=\"masquerade-form\" action=\"".COMMUNITY_URL.$COMMUNITY_URL."\" method=\"get\">\n";
						$sidebar_html .= "<label for=\"permission-mask\">Available permission masks:</label><br />";
						$sidebar_html .= "<select id=\"permission-mask\" name=\"mask\" style=\"width: 160px\" onchange=\"window.location='".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?".str_replace("&#039;", "'", replace_query(array("mask" => "'+this.options[this.selectedIndex].value")))."\">\n";
						foreach ($_SESSION["permissions"] as $proxy_id => $result) {
							$sidebar_html .= "<option value=\"".(($proxy_id == $_SESSION["details"]["id"]) ? "close" : $result["permission_id"])."\"".(($proxy_id == $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]) ? " selected=\"selected\"" : "").">".html_encode($result["fullname"])."</option>\n";
						}
						$sidebar_html .= "</select>\n";
						$sidebar_html .= "</form>\n";

						new_sidebar_item("Permission Masks", $sidebar_html, "permission-masks", "open");
					}

					if (($LOGGED_IN) && ($COMMUNITY_ADMIN)) {
						$sidebar_html  = "<ul class=\"menu\">\n";
						$sidebar_html .= "	<li class=\"admin\"><a href=\"".ENTRADA_URL."/communities?section=modify&amp;community=".$COMMUNITY_ID."\" style=\"font-weight: bold\">Manage Community</a></li>\n";
						$sidebar_html .= "	<li class=\"admin\"><a href=\"".ENTRADA_URL."/communities?section=members&amp;community=".$COMMUNITY_ID."\" style=\"font-weight: bold\">Manage Members</a></li>\n";
						$sidebar_html .= "	<li class=\"admin\"><a href=\"".COMMUNITY_URL.$COMMUNITY_URL.":pages\" style=\"font-weight: bold\">Manage Pages</a></li>\n";
						$sidebar_html .= "	<li class=\"admin\"><a href=\"".ENTRADA_URL."/communities/reports?community=".$COMMUNITY_ID."\" style=\"font-weight: bold\">Community Reports</a></li>\n";
						$sidebar_html .= "</ul>\n";

						new_sidebar_item("Admin Centre", $sidebar_html, "community-admin", "open");
					}

					/**
					 * Show the links back to Entrada if the user is logged in.
					 */
					if ($LOGGED_IN && (!defined("HIDE_NAV") || !HIDE_NAV)) {
						$sidebar_html  = "<ul class=\"menu\">\n";
						$sidebar_html .= "	<li class=\"nav\"><a href=\"".ENTRADA_URL."/dashboard\">Dashboard</a></li>\n";
						$sidebar_html .= "	<li class=\"nav\"><a href=\"".ENTRADA_URL."/communities\">Communities</a></li>\n";
						$sidebar_html .= "	<li class=\"nav\"><a href=\"".ENTRADA_URL."/courses\">Courses</a></li>\n";
						$sidebar_html .= "	<li class=\"nav\"><a href=\"".ENTRADA_URL."/events\">Learning Events</a></li>\n";
						$sidebar_html .= "	<li class=\"nav\"><a href=\"".ENTRADA_URL."/search\">Curriculum Search</a></li>\n";
						$sidebar_html .= "	<li class=\"nav\"><a href=\"".ENTRADA_URL."/people\">People Search</a></li>\n";
						$sidebar_html .= "	<li class=\"nav\"><a href=\"".ENTRADA_URL."/library\">Library</a></li>\n";
						$sidebar_html .= "	<li class=\"nav\" style=\"margin-top: 5px\"><a href=\"".ENTRADA_URL."/?action=logout\">Logout</a></li>\n";
						$sidebar_html .= "</ul>\n";
	
						new_sidebar_item(APPLICATION_NAME, $sidebar_html, "entrada-navigation", "open");
					}
					
					/**
					 * Show a login back if the user is not logged in.
					 */
					if (!$LOGGED_IN) {
						new_sidebar_item("Community Login", "Log in using your ".APPLICATION_NAME." account to access more community features.<div style=\"margin-top: 10px; text-align: center\"><a href=\"".ENTRADA_URL."/?url=".rawurlencode($PROCEED_TO)."\" style=\"font-weight: bold\">Click here to login</a></a>", "login-page-box", "open");
					}

					/**
					 * Show the members membership details if they are logged in
					 * and are a community member.
					 */
					if (($LOGGED_IN) && ($COMMUNITY_MEMBER)) {
						$sidebar_html = "<span class=\"content-small\">My Membership</span>\n";
						$sidebar_html  .= "<ul class=\"menu\">\n";
						$sidebar_html .= "	<li class=\"community\"><a href=\"".ENTRADA_URL."/profile\">".html_encode($_SESSION["details"]["firstname"]." ".$_SESSION["details"]["lastname"])."</a>";
						if ($COMMUNITY_MEMBER_SINCE) {
							$sidebar_html .= "	<br />Joined: ".date("Y-m-d", $COMMUNITY_MEMBER_SINCE);
						}
						$sidebar_html .= "	</li>";
						$sidebar_html .= "</ul>\n";
						$sidebar_html .= "<ul class=\"menu\">\n";
						$sidebar_html .= "	<li class=\"on\"><a href=\"".ENTRADA_URL."/communities?section=leave&amp;community=".$COMMUNITY_ID."\">Quit This Community</a></li>";
						$sidebar_html .= "</ul>\n";
						$sidebar_html .= "<hr/>\n";
						$sidebar_html .= "<ul class=\"menu\"><li class=\"community\"><a href=\"".ENTRADA_URL."/community".$COMMUNITY_URL.":members\">View All Members</a></li></ul>\n";
						if ($MAILING_LISTS["active"]) {
							$query = "SELECT * FROM `community_mailing_lists` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID);
							$mail_list = $db->GetRow($query);
							if ($mail_list && $mail_list["list_type"] != "inactive") {
								$sidebar_html .= "<ul class=\"menu\" style=\"padding-left: 0px;\">\n";
								$sidebar_html .= "	<li class=\"status-online\" style=\"font-weight: strong;\">Mailing List Active</li>\n";
								$sidebar_html .= "	<li class=\"none\">".($mail_list["list_type"] == "announcements" ? "Announcement" : "Discussion")." List</li>\n";
								if ($mail_list["list_type"] == "discussion" || $COMMUNITY_ADMIN) {
									$sidebar_html .= "	<li class=\"none\"><a href=\"mailto:".$mail_list["list_name"]."@".$GOOGLE_APPS["domain"]."\">Send Message</a></li>\n";
								}
								$sidebar_html .= "</ul>\n";
							} elseif ($COMMUNITY_ADMIN) {
								$sidebar_html .= "<ul class=\"menu\" style=\"padding-left: 0px;\">\n";
								$sidebar_html .= "	<li class=\"status-offline\" style=\"font-weight: strong;\">Mailing List Not Active</li>\n";
								$sidebar_html .= "</ul>\n";
							}
						}
						new_sidebar_item("This Community", $sidebar_html, "community-my-membership", "open");
					}
					
					if ((($PAGE_ACTIVE) && ((in_array($COMMUNITY_MODULE, array("default", "members", "pages", "course"))) || (array_key_exists($PAGE_URL, $COMMUNITY_PAGES["exists"])) && (array_key_exists($COMMUNITY_MODULE, $COMMUNITY_MODULES["enabled"])))) || $HOME_PAGE) {
						define("COMMUNITY_INCLUDED", true);
						
						if ((array_key_exists($PAGE_URL, $COMMUNITY_PAGES["enabled"])) || ($HOME_PAGE) || ($COMMUNITY_MODULE == "members") || (($COMMUNITY_MODULE == "pages") && ($USER_ACCESS == 3))) {
						    /**
	                         * ID of the record which can be set in the URL and used to edit or delete a page, etc.
	                         * Used within modules and actions.
	                         */
	                        if ((isset($_GET["id"])) && ((int) trim($_GET["id"])) && !((int) $RECORD_ID)) {
	                            $RECORD_ID = (int) trim($_GET["id"]);
	                        }
	
							if (!in_array($COMMUNITY_MODULE, array("pages", "members", "default", "course"))) {
								$query	= "SELECT `module_id` FROM `communities_modules` WHERE `module_shortname` = ".$db->qstr($COMMUNITY_MODULE);
								$result	= $db->GetRow($query);
								if ($result) {
									$MODULE_ID	= $result["module_id"];
								} else {
									$ERROR++;
									$ERRORSTR[]	= "We were unable to load the selected page at this time. The system administrator has been notified of the error, please try again later.";
									$MODULE_ID	= 0;
									
									application_log("error", "Unable to locate and load a selected community module [".$COMMUNITY_PAGES["details"][$PAGE_URL]["page_type"]."] in community_id [".$COMMUNITY_ID."].");
								}
							}
							
							$MODULE_TITLE	= (isset($COMMUNITY_PAGES["details"][$PAGE_URL]) ? $COMMUNITY_PAGES["details"][$PAGE_URL]["menu_title"] : "Pages");

							if ((@file_exists($module_file = COMMUNITY_ABSOLUTE.DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR.$COMMUNITY_MODULE.".inc.php")) && (@is_readable($module_file))) {
								require_once($module_file);
							} else {
								$ONLOAD[]	= "setTimeout('window.location=\\'".COMMUNITY_URL.$COMMUNITY_URL."\\'', 5000)";
								$ERROR++;
								$ERRORSTR[] = "The module you are attempting to access is not currently available.<br /><br />You will be automatically redirected in 5 seconds or <a href=\"".COMMUNITY_URL.$COMMUNITY_URL."\" style=\"font-weight: bold\">click here</a> to proceed.";
	
								echo display_error();
	
								application_log("error", "Unable to load specified module: ".$COMMUNITY_MODULE);
							}
						} else {
							$ONLOAD[]	= "setTimeout('window.location=\\'".COMMUNITY_URL.$COMMUNITY_URL."\\'', 5000)";
	
							$ERROR++;
							$ERRORSTR[] = "You do not have access to this page. Please contact a community administrator for assistance.<br /><br />You will be automatically redirected in 5 seconds or <a href=\"".COMMUNITY_URL.$COMMUNITY_URL."\" style=\"font-weight: bold\">click here</a> to proceed.";
	
							echo display_error();
						}
					} else {
						$url		= COMMUNITY_URL.$COMMUNITY_URL;
						$ONLOAD[]	= "setTimeout('window.location=\\'".$url."\\'', 5000)";
					
						$ERROR++;
						$ERRORSTR[]	= "The page you have requested does not currently exist within this community.<br /><br />You will now be redirected to the index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
						
						echo "<h1>Page Not Found: <strong>404 Error</strong></h1>\n";
						
						echo display_error();
					}
				}

				/**
				 * End the middle output buffer after the contents are collected and stored in the $PAGE_CONTENT variable.
				 */
				$PAGE_CONTENT = ob_get_contents();
				ob_end_clean();

				if (($COMMUNITY_MODULE != "default") && ($COMMUNITY_MODULE != "pages") && ($COMMUNITY_MODULE != "members") && ($SECTION == "index") && (array_key_exists($COMMUNITY_MODULE, $COMMUNITY_MODULES["enabled"]))) {
					$page_text	= "";
					
					$query	= "SELECT `cpage_id`, `page_title`, `page_content` FROM `community_pages` WHERE `page_url` = ".(isset($PAGE_URL) && $PAGE_URL ? $db->qstr($PAGE_URL) : "''")." AND `community_id` = ".$db->qstr($COMMUNITY_ID);
					$result	= $db->GetRow($query);
					if ($result) {
						if (trim($result["page_title"]) != "") {
							$page_text .= "<h1>".html_encode($result["page_title"])."</h1>";
						}
						
						if (trim($result["page_content"]) != "") {
							$page_text .= $result["page_content"]."\n<br /><br />\n";
						}
					}
					$PAGE_CONTENT	= $page_text.$PAGE_CONTENT;

				}
				
				$PAGE_META["title"] = $community_details["community_title"];
				$PAGE_META["description"] = trim(str_replace(array("\t", "\n", "\r"), " ", html_encode(strip_tags($community_details["community_description"]))));
				$PAGE_META["keywords"] = trim(str_replace(array("\t", "\n", "\r"), " ", html_encode(strip_tags($community_details["community_keywords"]))));"";
				
				$member_name = html_encode($_SESSION["details"]["firstname"]." ".$_SESSION["details"]["lastname"]);
				$date_joined = "Joined: ".date("Y-m-d", $COMMUNITY_MEMBER_SINCE);
				
				$smarty->assign("template_relative", COMMUNITY_RELATIVE."/templates/".$COMMUNITY_TEMPLATE);
				$smarty->assign("sys_community_relative", COMMUNITY_RELATIVE);
				
				$smarty->assign("sys_system_navigator", load_system_navigator());
				$smarty->assign("sys_profile_url", ENTRADA_URL."/profile");
				$smarty->assign("sys_website_url", ENTRADA_URL);

				$smarty->assign("site_template", $COMMUNITY_TEMPLATE);
				$smarty->assign("site_theme", ((isset($community_details["community_theme"])) ? $community_details["community_theme"] : ""));
				
				$smarty->assign("site_default_charset", DEFAULT_CHARSET);

				$smarty->assign("site_community_url", COMMUNITY_URL.$COMMUNITY_URL);
				$smarty->assign("site_community_relative", COMMUNITY_RELATIVE.$COMMUNITY_URL);
				$smarty->assign("site_community_title", html_encode($community_details["community_title"]));
				$smarty->assign("site_community_module", $COMMUNITY_MODULE);

				$smarty->assign("site_total_members", communities_count_members());
				$smarty->assign("site_total_admins", communities_count_members(1));

				$smarty->assign("site_primary_navigation", $COMMUNITY_PAGES["navigation"]);
				$show_tertiary_sideblock = false;
				foreach ($COMMUNITY_PAGES["navigation"] as $top_level_page) {
					if (count($top_level_page["link_children"]) > 0) {
						foreach ($top_level_page["link_children"] as $child_page) {
							if (($child_page["link_selected"] || $child_page["child_selected"]) && count($child_page["link_children"])) {
								$show_tertiary_sideblock = true;
							}
						}
					}
				}
				$smarty->assign("show_tertiary_sideblock", $show_tertiary_sideblock);
				$smarty->assign("site_navigation_items_per_column", 4);
				$smarty->assign("site_breadcrumb_trail", "%BREADCRUMB%");

				if (($COMMUNITY_MODULE != "pages") && ($COMMUNITY_MODULE != "members") && ($SECTION == "index")) {
					$query = "	SELECT `cpage_id`
								FROM `community_pages`
								WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)."
								AND `page_url` = ".$db->qstr($PAGE_URL);
					$result = $db->GetRow($query);
					if ($result) {
						$smarty->assign("child_nav", communities_page_children_in_list($result["cpage_id"]));
					} else {
						$smarty->assign("child_nav", "");	
					}
				}

				$smarty->assign("community_id", $COMMUNITY_ID);
				$smarty->assign("page_title", "%TITLE%");
				$smarty->assign("page_description", "%DESCRIPTION%");
				$smarty->assign("page_keywords", "%KEYWORDS%");
				$smarty->assign("page_head", "%HEAD%");
				$smarty->assign("page_sidebar", "%SIDEBAR%");
				$smarty->assign("page_content", $PAGE_CONTENT);
				
				$smarty->assign("user_is_anonymous", (($LOGGED_IN) ? false : true));
				$smarty->assign("is_logged_in", $LOGGED_IN);
				$smarty->assign("user_is_member", $COMMUNITY_MEMBER);
				$smarty->assign("user_is_admin", $COMMUNITY_ADMIN);
				$smarty->assign("date_joined", $date_joined);
				$smarty->assign("member_name", $member_name);
				
				$smarty->display("index.tpl");
			}
		} else {
			/**
			 * No Longer Active.
			 *
			 */
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");

			$ERROR++;
			$ERRORSTR[] = "<strong>The community that you are trying to access is no longer active.</strong><br /><br />Please use the <a href=\"".ENTRADA_URL."/communities\">Communities Search</a> feature to find the community that you are looking for.";
	
			$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/communities\\'', 10000)";
	
			$smarty->assign("template_relative", COMMUNITY_RELATIVE."/templates/".$COMMUNITY_TEMPLATE);
			$smarty->assign("page_title", "Community Not Active");
			$smarty->assign("page_content", display_error());
	
			$smarty->display("error.tpl");
	
			application_log("notice", "Community [".$COMMUNITY_URL."] is no longer active.");
		}
	} else {
		/**
		 * 404 Not Found Community
		 *
		 */
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");

		$ERROR++;
		$ERRORSTR[] = "The community URL that you are trying to access does not exist or has been removed the system. Please use the <a href=\"".ENTRADA_URL."/communities\">Communities Search</a> feature to find the community that you are looking for.";

		$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/communities\\'', 15000)";

		$smarty->assign("template_relative", COMMUNITY_RELATIVE."/templates/".$COMMUNITY_TEMPLATE);
		$smarty->assign("page_title", "Community Not Found");
		$smarty->assign("page_content", display_error());

		$smarty->display("error.tpl");

		application_log("notice", "Community [".$COMMUNITY_URL."] does not exist.");
	}
} else {
	header("Location: ".ENTRADA_URL."/communities");
	exit;
}
?>
