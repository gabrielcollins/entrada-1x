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
 * This module allows authenticated users to search the user database for
 * specific people, or browse faculty by department / students by year, etc.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
 * @version $Id: people.inc.php 1171 2010-05-01 14:39:27Z ad29 $
*/
if (!defined("PARENT_INCLUDED")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->amIAllowed('people', 'read')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	/**
	 * Meta information for this page if they are able to use this module.
	 */
	$PAGE_META["title"]			= "People Search";
	$PAGE_META["description"]	= "Allowing you to search the School of Medicine for a specific person or people.";
	$PAGE_META["keywords"]		= "";

	$is_administrator = $ENTRADA_ACL->amIallowed('user', 'update');
	
	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/people", "title" => "People Search");
	
	$PROCESSED		= array();
	$PREFERENCES	= preferences_load($MODULE);

	$ORGANISATION_ID = $ENTRADA_USER->getActiveOrganisation();
	$organisation_query = "SELECT * FROM `".AUTH_DATABASE."`.`organisations`";
	$ORGANISATIONS = $db->GetAll($organisation_query);
	$ORGANISATION_BY_ID = array();
	foreach($ORGANISATIONS as $o) {
		$ORGANISATIONS_BY_ID[$o["organisation_id"]] = $o;
	}
	$search_query	= "";
	$plaintext_query = "";
	$year_offset = (strtotime("July 15th, ".date("Y", time())) < time() ? 1 : 0);
	
	/**
	 * Update requsted number of profiles per page.
	 * Valid: any integer really.
	 */
	if (((isset($_POST["pp"])) && ($integer = (int) trim($_POST["pp"]))) || ((isset($_GET["pp"])) && ($integer = (int) trim($_GET["pp"])))) {
		if (($integer > 0) && ($integer <= 250)) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] = $integer;
		}
	
		$_SERVER["QUERY_STRING"] = replace_query(array("pp" => false));
	} else {
		if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"])) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] = 5;
		}
	}

	/**
	 * The query that is actually be searched for.
	 */

	if ((isset($_POST["id"])) && (trim($_POST["id"]))) {
		$load_profile = clean_input($_POST["id"], "int");
	} elseif ((isset($_GET["id"])) && (trim($_GET["id"]))) {
		$load_profile = clean_input($_GET["id"], "int");
	}

	if ((isset($_POST["profile"])) && (trim($_POST["profile"]))) {
		$load_profile = clean_input($_POST["profile"], array("credentials"));
	} elseif ((isset($_GET["profile"])) && (trim($_GET["profile"]))) {
		$load_profile = clean_input($_GET["profile"], array("credentials"));
	}

	if (isset($load_profile) && $load_profile) {
		$query_profile	= "
						SELECT a.*, b.`group`, b.`role`
						FROM `".AUTH_DATABASE."`.`user_data` AS a
						LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
						ON b.`user_id` = a.`id`
						WHERE  b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
						AND b.`account_active` = 'true'
						AND (b.`access_starts` = '0' OR b.`access_starts` < ".$db->qstr(time()).")
						AND (b.`access_expires` = '0' OR b.`access_expires` >= ".$db->qstr(time()).")
						AND ".((is_numeric($load_profile)) ? "a.`id` = ".$db->qstr((int) $load_profile) : "a.`username` = ".$db->qstr($load_profile));
	}
	
	/**
	 * Determine the type of search that is requested.
	 */
	if ((isset($_POST["type"])) && (in_array(trim($_POST["type"]), array("search", "browse-group", "browse-dept")))) {
		$search_type = clean_input($_POST["type"], "trim");	
	} elseif ((isset($_GET["type"])) && (in_array(trim($_GET["type"]), array("search", "browse-group", "browse-dept")))) {
		$search_type = clean_input($_GET["type"], "trim");	
	}
	
	if (isset($search_type) && $search_type) {
		switch ($search_type) {
			case "browse-group" :
				$PROCESSED["organisation"]	= false;
				$PROCESSED["group"]			= false;
				$PROCESSED["role"]			= false;
				
				if ((isset($_POST["g"])) && (isset($SYSTEM_GROUPS[$group = clean_input($_POST["g"], "credentials")]))) {
					$PROCESSED["group"]	= $group;
					$search_query_text	= html_encode(ucwords($group));

					if (($PROCESSED["group"] == "student") && (isset($_POST["r"])) && ($role = clean_input($_POST["r"], "alphanumeric"))) {
						$PROCESSED["role"] = $role;
						
						$search_query_text	.= " &rArr; ".html_encode(ucwords($role));
					}
					
					$search_query = $search_query_text;
					
				} elseif ((isset($_GET["g"])) && (isset($SYSTEM_GROUPS[$group = clean_input($_GET["g"], "credentials")]))) {
					$PROCESSED["group"]	= $group;
					$search_query_text	= html_encode(ucwords($group));
					
					if (($PROCESSED["group"] == "student") && (isset($_GET["r"])) && ($role = clean_input($_GET["r"], "alphanumeric"))) {
						$PROCESSED["role"] = $role;
						
						$search_query_text	.= " &rArr; ".html_encode(ucwords($role));
					}
					
					$search_query = $search_query_text;
					
				} else {
					$ERROR++;
					$ERRORSTR[] = "To browse a group, you must select a group from the group select list.";	
				}
				
				if(($organisation = $ENTRADA_USER->getActiveOrganisation()) && isset($ORGANISATIONS_BY_ID[$organisation])) {
					$PROCESSED["organisation"] = $organisation;
					$search_query .= " in ".$ORGANISATIONS_BY_ID[$organisation]["organisation_title"];
				} else {
					$ERROR++;
					$ERRORSTR[] = "To browse a group, you must select a organisation from the organisation select list.";
				}
				
				if (!$ERROR) {
					if ($PROCESSED["group"] != "student") {
						$query_search	= "SELECT a.*, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`account_active`, b.`access_starts`, b.`access_expires`, b.`last_login`, b.`role`, b.`group`
											FROM `".AUTH_DATABASE."`.`user_data` AS a
											LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
											ON b.`user_id` = a.`id`
											AND b.`app_id` IN (".AUTH_APP_IDS_STRING.")
											WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
											AND b.`organisation_id` = ".$db->qstr($PROCESSED["organisation"])."
											AND b.`group` ".($PROCESSED["group"] == "staff" ? "IN ('staff', 'medtech')" : "= ".$db->qstr($PROCESSED["group"]))."
											".(($PROCESSED["role"]) ? "AND b.`role` = ".$db->qstr($PROCESSED["role"]) : "")."
											GROUP BY a.`id`
											ORDER BY `fullname` ASC
											LIMIT %s, %s";
						$query_count	= "SELECT a.*, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`account_active`, b.`access_starts`, b.`access_expires`, b.`last_login`, b.`role`, b.`group`
											FROM `".AUTH_DATABASE."`.`user_data` AS a
											LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
											ON b.`user_id` = a.`id`
											AND b.`app_id` IN (".AUTH_APP_IDS_STRING.")
											WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
											AND b.`organisation_id` = ".$db->qstr($PROCESSED["organisation"])."
											AND b.`group` ".($PROCESSED["group"] == "staff" ? "IN ('staff', 'medtech')" : "= ".$db->qstr($PROCESSED["group"]))."
											".(($PROCESSED["role"]) ? "AND b.`role` = ".$db->qstr($PROCESSED["role"]) : "")."
											GROUP BY a.`id`
											ORDER BY `fullname` ASC";
					} else {
						$search_query = groups_get_name($PROCESSED["role"])." in ".$ORGANISATIONS_BY_ID[$organisation]["organisation_title"];
						$query_search	= "SELECT a.*, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`account_active`, b.`access_starts`, b.`access_expires`, b.`last_login`, b.`role`, b.`group`
											FROM `".AUTH_DATABASE."`.`user_data` AS a
											LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
											ON b.`user_id` = a.`id`
											AND b.`app_id` IN (".AUTH_APP_IDS_STRING.")
											JOIN `group_members` AS c
											ON a.`id` = c.`proxy_id`
											AND c.`member_active` = 1
											JOIN `groups` AS d
											ON c.`group_id` = d.`group_id`
											AND d.`group_active` = 1
											WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
											AND b.`organisation_id` = ".$db->qstr($PROCESSED["organisation"])."
											AND b.`group` = ".$db->qstr($PROCESSED["group"])."
											AND d.`group_id` = ".$db->qstr($PROCESSED["role"])."
											GROUP BY a.`id`
											ORDER BY `fullname` ASC
											LIMIT %s, %s";
						$query_count	= "SELECT a.*, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`account_active`, b.`access_starts`, b.`access_expires`, b.`last_login`, b.`role`, b.`group`
											FROM `".AUTH_DATABASE."`.`user_data` AS a
											LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
											ON b.`user_id` = a.`id`
											AND b.`app_id` IN (".AUTH_APP_IDS_STRING.")
											JOIN `group_members` AS c
											ON a.`id` = c.`proxy_id`
											AND c.`member_active` = 1
											JOIN `groups` AS d
											ON c.`group_id` = d.`group_id`
											AND d.`group_active` = 1
											WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
											AND b.`organisation_id` = ".$db->qstr($PROCESSED["organisation"])."
											AND b.`group` = ".$db->qstr($PROCESSED["group"])."
											AND d.`group_id` = ".$db->qstr($PROCESSED["role"])."
											GROUP BY a.`id`
											ORDER BY `fullname` ASC";
					}
				}
			break;
			case "browse-dept" :
				$browse_dept = 0;
				
				if ((isset($_POST["d"])) && ($department = clean_input($_POST["d"], array("trim", "int")))) {
					$query	= "	SELECT a.`department_id`, a.`department_title`, a.`organisation_id`, b.`entity_title`, c.`organisation_title`
								FROM `".AUTH_DATABASE."`.`departments` AS a
								LEFT JOIN `".AUTH_DATABASE."`.`entity_type` AS b
								ON a.`entity_id` = b.`entity_id`
								LEFT JOIN `".AUTH_DATABASE."`.`organisations` AS c
								ON a.`organisation_id` = c.`organisation_id`
								WHERE a.`department_id` = ".$db->qstr($department)."
								ORDER BY c.`organisation_title` ASC, a.`department_title`";
					$result = $db->GetRow($query);
					if ($result) {
						$browse_department	= $department;
						$search_query_text	= html_encode($result["department_title"]);
						$search_query		= $search_query_text;
					} else {
						$ERROR++;
						$ERRORSTR[] = "The department you have provided does not exist. Please ensure that you select a valid department from the department list.";
					}
				} elseif ((isset($_GET["d"])) && ($department = clean_input($_GET["d"], array("trim", "int")))) {
					$query	= "	SELECT a.`department_id`, a.`department_title`, a.`organisation_id`, b.`entity_title`, c.`organisation_title`
								FROM `".AUTH_DATABASE."`.`departments` AS a
								LEFT JOIN `".AUTH_DATABASE."`.`entity_type` AS b
								ON a.`entity_id` = b.`entity_id`
								LEFT JOIN `".AUTH_DATABASE."`.`organisations` AS c
								ON a.`organisation_id` = c.`organisation_id`
								WHERE a.`department_id` = ".$db->qstr($department)."
								ORDER BY c.`organisation_title` ASC, a.`department_title`";
					$result = $db->GetRow($query);
					if ($result) {
						$browse_department	= $department;
						$search_query_text	= html_encode($result["department_title"]);
						$search_query		= $search_query_text;
					} else {
						$ERROR++;
						$ERRORSTR[] = "The department you have provided does not exist. Please ensure that you select a valid department from the department list.";
					}
				} else {
					$ERROR++;
					$ERRORSTR[] = "To browse a department, you must select a department from the department selection list.";	
				}
				
				if (!$ERROR) {
					
					$query_search	= "	SELECT a.*, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`account_active`, b.`access_starts`, b.`access_expires`, b.`last_login`, b.`role`, b.`group`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON b.`user_id` = a.`id`
										AND b.`app_id` IN (".AUTH_APP_IDS_STRING.")
										LEFT JOIN `".AUTH_DATABASE."`.`user_departments` AS c
										ON c.`user_id` = a.`id`
										WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
										AND c.`dep_id` = ".$db->qstr($browse_department)."
										GROUP BY a.`id`
										ORDER BY `fullname` ASC
										LIMIT %s, %s";
					$query_count	= "	SELECT a.*, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`account_active`, b.`access_starts`, b.`access_expires`, b.`last_login`, b.`role`, b.`group`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON b.`user_id` = a.`id`
										AND b.`app_id` IN (".AUTH_APP_IDS_STRING.")
										LEFT JOIN `".AUTH_DATABASE."`.`user_departments` AS c
										ON c.`user_id` = a.`id`
										WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
										AND c.`dep_id` = ".$db->qstr($browse_department)."
										GROUP BY a.`id`
										ORDER BY `fullname` ASC";
				}
			break;
			case "search" :
			default :
				$group_string			= "";
				$role_string			= "";
				if ((isset($_REQUEST["q"])) && ($query = clean_input($_REQUEST["q"], array("trim", "notags")))) {
					$search_query		= $query;
					$plaintext_query	= $search_query;
					$search_query_text	= html_encode($query);
				}
				
				if (isset($_REQUEST["search_groups"]) && ($search_groups = explode(",", $_REQUEST["search_groups"]))) {
					foreach ($search_groups as $group) {
						if ($group_string && ($group = clean_input($group, "credentials"))) {
							$group_string .= ", ".$db->qstr($group);
							if ($group == "staff") {
								$group_string .= ", 'medtech'";
							}
						} elseif (($group = clean_input($group, "credentials"))) {
							$group_string = $db->qstr($group);
							if ($group == "staff") {
								$group_string .= ", 'medtech'";
							}
						}
					}
				} else {
					$group_string = "'staff', 'medtech', 'faculty', 'resident'";
				}
				
				if (isset($_REQUEST["search_classes"]) && ($search_classes = explode(",", $_REQUEST["search_classes"]))) {
					foreach ($search_classes as $class) {
						if ($role_string && ($role = clean_input($class, "credentials"))) {
							$role_string .= ", ".$db->qstr($role);
						} elseif (($role = clean_input($class, "credentials"))) {
							$role_string = $db->qstr($role);
						}
					}
				} else {
					$role_string = "'".(date("Y", time()) + $year_offset)."', '".(date("Y", time()) + $year_offset + 1)."', '".(date("Y", time()) + $year_offset + 2)."', '".(date("Y", time()) + $year_offset + 3)."'";
				}
				
				if (isset($_REQUEST["search_alumni"]) && $_REQUEST["search_alumni"]) {
					$query = "	SELECT UNIQUE(`role`) FROM `".AUTH_DATABASE."`.`user_access`
								WHERE `group` = 'student'
								AND `role` < ".$db->qstr((date("Y", time()) + $year_offset));
					$roles = $db->GetAll($query);
					if ($roles) {
						foreach ($roles as $role) {
							if ($role_string) {
								$role_string .= ", ".$db->qstr($role["role"]);
							} else {
								$role_string = $db->qstr($role["role"]);
							}
						}
					}
				}

				$query_search	= "	SELECT a.*, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`account_active`, b.`access_starts`, b.`access_expires`, b.`last_login`, b.`role`, b.`group`
									FROM `".AUTH_DATABASE."`.`user_data` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
									ON b.`user_id` = a.`id`
									AND b.`app_id` IN (".AUTH_APP_IDS_STRING.")
									WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
									AND b.`organisation_id` IN (" . $ENTRADA_USER->getActiveOrganisation() . ")
									AND (b.`group` ".($group_string && $role_string ? "IN (".$group_string.")									
									OR (b.`group` = 'student' 
										AND b.`role` IN (".$role_string.")))" : ($role_string ? "= 'student' 
									AND b.`role` IN (".$role_string."))" : ( $group_string ? "IN (".$group_string."))" : "!= 'guest')")))."
									AND (a.`number` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
									OR a.`username` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
									OR a.`email` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
									OR CONCAT_WS(' ', a.`firstname`, a.`lastname`) LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%").")
									GROUP BY a.`id`
									ORDER BY `fullname` ASC
									LIMIT %s, %s";
				
				$query_count	= "	SELECT a.*, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`account_active`, b.`access_starts`, b.`access_expires`, b.`last_login`, b.`role`, b.`group`
									FROM `".AUTH_DATABASE."`.`user_data` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
									ON b.`user_id` = a.`id`
									AND b.`app_id` IN (".AUTH_APP_IDS_STRING.")
									WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
									AND a.`organisation_id` IN (" . $ORGANISATION_ID . ")
									AND (b.`group` ".($group_string && $role_string ? "IN (".$group_string.")
									OR (b.`group` = 'student' 
										AND b.`role` IN (".$role_string.")))" : ($role_string ? "= 'student' 
									AND b.`role` IN (".$role_string."))" : ( $group_string ? "IN (".$group_string."))" : "!= 'guest')")))."
									AND (a.`number` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
									OR a.`username` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
									OR a.`email` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
									OR CONCAT_WS(' ', a.`firstname`, a.`lastname`) LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%").")
									GROUP BY a.`id`
									ORDER BY `fullname` ASC, FIELD(b.`app_id`, ".AUTH_APP_IDS_STRING.")";
			break;
		}

		$results	= $db->GetAll($query_count);
		/**
		 * Get the total number of results using the generated queries above and calculate the total number
		 * of pages that are available based on the results per page preferences.
		 */
		$result 	= count($results);
		if ($result) {
			$total_rows	= $result;

			if ($total_rows <= $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) {
				$total_pages = 1;
			} elseif (($total_rows % $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == 0) {
				$total_pages = (int) ($total_rows / $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
			} else {
				$total_pages = (int) ($total_rows / $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) + 1;
			}
		} else {
			$total_rows		= 0;
			$total_pages	= 1;
		}

		/**
		 * Check if pv variable is set and see if it's a valid page, other wise page 1 it is.
		 */
		if (isset($_POST["pv"])) {
			$page_current = (int) trim($_POST["pv"]);
	
			if (($page_current < 1) || ($page_current > $total_pages)) {
				$page_current = 1;
			}
		} elseif (isset($_GET["pv"])) {
			$page_current = (int) trim($_GET["pv"]);
	
			if (($page_current < 1) || ($page_current > $total_pages)) {
				$page_current = 1;
			}
		} else {
			$page_current = 1;
		}	

		$page_previous	= (($page_current > 1) ? ($page_current - 1) : false);
		$page_next		= (($page_current < $total_pages) ? ($page_current + 1) : false);
	}

	$HEAD[] = "<link href=\"".ENTRADA_URL."/css/tabpane.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
	$HEAD[] = "<style type=\"text/css\">.dynamic-tab-pane-control .tab-page { height:auto; }</style>\n";

	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/tabpane/tabpane.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/selectchained.js\"></script>\n";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/picklist.js\"></script>\n";

	/**
	 * Check if preferences need to be updated on the server at this point.
	 */
	preferences_update($MODULE, $PREFERENCES);

	$student_classes = array();
	$active_cohorts = groups_get_active_cohorts($ENTRADA_USER->getActiveOrganisation());
	if (isset($active_cohorts) && !empty($active_cohorts)) {
		foreach ($active_cohorts as $cohort) {
			$student_classes[$cohort["group_id"]] = $cohort["group_name"];
		}
	}
	
	$browse_people		= array();
	$browse_people[]	= array(
							"value"		=> "student",
							"title"		=> "Browse Students",
							"options"	=> $student_classes
							);
	$browse_people[]	= array(
							"value"		=> "resident",
							"title"		=> "Browse Residents",
							"options"	=> array("resident" => "Show All Residents")
							);
	$browse_people[]	= array(
							"value"		=> "faculty",
							"title"		=> "Browse Faculty",
							"options"	=> array("faculty" => "Show All Faculty")
							);
	$browse_people[]	= array(
							"value"		=> "staff",
							"title"		=> "Browse Staff",
							"options"	=> array("staff" => "Show All Staff")
							);

	$i = count($HEAD);
	$HEAD[$i]  = "<script type=\"text/javascript\">\n";
	$HEAD[$i] .= "addListGroup('account_type', 'cs-top');\n";
	if (is_array($browse_people)) {
		foreach ($browse_people as $key => $result) {
				$HEAD[$i] .= "addList('cs-top', '".$result["title"]."', '".$result["value"]."', 'cs-sub-".$key."', ".(((isset($PROCESSED["group"])) && ($PROCESSED["group"] == $result["value"])) ? "1" : "0").");\n";
				if (is_array($result["options"])) {
					foreach ($result["options"] as $option => $value) {
						$HEAD[$i] .= "addOption('cs-sub-".$key."', '".$value."', '".$option."', ".(((isset($PROCESSED["role"])) && ($PROCESSED["role"] == $option)) ? "1" : "0").");\n";
					}
				}
		}
	}
	$HEAD[$i] .= "</script>\n";

	$ONLOAD[] = "initListGroup('account_type', $('group'), $('role'))";
	$ONLOAD[] = "toggle_visibility_checkbox($('send_notification'), 'send_notification_msg')";
	
	if ($ERROR) {
		echo display_error();	
	}
	
	if ($NOTICE) {
		echo display_notice();	
	}
	?>
	<div class="tab-pane" id="people-search-tabs">
		<div class="tab-page">
			<h2 class="tab">People Search</h2>
			<style type="text/css">
			#advanced_mode, #basic_mode {
				margin-left: 20px;
				font-size: 11px;
				text-decoration: none;
				width: 110px;
				cursor: pointer;
			}

			#advanced_mode > span, #basic_mode > span {
				color: #003366;
			}
			</style>
			<?php
			if ((isset($_REQUEST["search_groups"]) && $_REQUEST["search_groups"] != "faculty,resident,staff") || (isset($_REQUEST["search_classes"]) && $_REQUEST["search_classes"] != "2010,2011,2012,2013") || (isset($_REQUEST["search_alumni"]) && $_REQUEST["search_alumni"])) {
				$ONLOAD[] = "toggle_search('advanced')";
			} else {
				$ONLOAD[] = "toggle_search('basic')";
			}
			?>
			<script type="text/javascript">
			function toggle_search(searchType) {
				$('basic_mode').hide();
				$('advanced_mode').hide();
				$('advanced_search').hide();

				if (searchType == 'advanced') {
					$('advanced_mode').show();
					$('advanced_search').show();

				} else {
					$('basic_mode').show();
				}
			}
			</script>
			<form id="search_form" action="<?php echo ENTRADA_URL; ?>/people" method="get">
			<input type="hidden" name="pv" id="search_pv" value="<?php echo ($page_current ? $page_current : 1);?>" />
			<input type="hidden" name="pp" id="search_pp" value="<?php echo $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]; ?>" />
			<input type="hidden" name="type" value="search" />
			<table style="width: 100%" cellspacing="1" cellpadding="1" border="0" summary="Search For User">
			<colgroup>
				<col style="width: 3%" />
				<col style="width: 20%" />
				<col style="width: 77%" />
			</colgroup>
			<tfoot>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
						<input type="submit" class="button" value="Search" />
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td>&nbsp;</td>
					<td style="vertical-align: top"><label for="q" class="form-required">People Search:</label></td>
					<td>
						<input type="text" id="q" name="q" value="<?php echo html_encode($plaintext_query); ?>" style="width: 300px" />
						
						<span id="advanced_mode" onclick="toggle_search('basic')" style="display: none">
							<img style="margin-top: 3px" src="<?php echo ENTRADA_URL; ?>/images/arrow-asc.gif" width="9" height="7" alt="Advanced Search" /> <span>Advanced Search</span>
						</span>
						<span id="basic_mode" onclick="toggle_search('advanced')">
							<img style="margin-top: 5px" src="<?php echo ENTRADA_URL; ?>/images/arrow-desc.gif" width="9" height="7" alt="Basic Search" /> <span>Advanced Search</span>
						</span>
						<div class="content-small" style="margin-top: 10px">
							<strong>Note:</strong> You can search for name, username, e-mail address or staff / student number.
						</div>
					</td>
				</tr>
			</tbody>
			<tbody id="advanced_search" style="display: none">
				<tr>
					<td>&nbsp;</td>
					<td style="padding-top: 15px; vertical-align: top;"><label class="form-required">Groups to search:</label></td>
					<td style="padding-top: 15px;">
						<script type="text/javascript">
							function addSomething(which) {
								$('search_'+which).value = "0";
								$$('.search_'+which).each( function (e) {
									if (e.checked) {
										if ($('search_'+which).value != '0') {
											$('search_'+which).value += ","+e.value;
										} else {
											$('search_'+which).value = e.value;
										}
									}
								});
							}
							function addClass() {
								addSomething('classes');
							}
							function addGroup() {
								addSomething('groups');
							}
							function addOrganisation() {
								addSomething('organisations');
							}
						</script>
						<div>
							<input type="hidden" name="search_groups" id="search_groups" value="<?php echo (isset($_GET["search_groups"]) ? $_GET["search_groups"] : "faculty,resident,staff"); ?>" />
							<input type="hidden" name="search_organisations" id="search_organisations" value="<?php echo (isset($_GET["search_organisations"]) ? $_GET["search_organisations"] : $ORGANISATION_ID); ?>" />
							<input type="hidden" name="search_classes" id="search_classes" value="<?php echo (isset($_GET["search_classes"]) ? $_GET["search_classes"] : (date("Y", time()) + $year_offset).",".(date("Y", time()) + $year_offset + 1).",".(date("Y", time()) + $year_offset + 2).",".(date("Y", time()) + $year_offset + 3)); ?>" />

							<table style="width: 350px; padding-left: 50px;">
								<tr>
									<td><input id="alumni" type="checkbox" <?php echo (isset($_REQUEST["search_alumni"]) && $_REQUEST["search_alumni"] ? "checked=\"checked\" " : ""); ?>value="1" name="search_alumni" /><label class="content-small" for="alumni"> Alumni</label></td>
									<td><input class="search_groups" id="faculty" type="checkbox" <?php echo ((isset($_REQUEST["search_groups"]) && is_array(explode(',', $_REQUEST["search_groups"])) && array_search("faculty", (explode(',', $_REQUEST["search_groups"]))) !== false) || (isset($_REQUEST["search_groups"]) && $_REQUEST["search_groups"] == "faculty") || (!isset($_REQUEST["search_groups"]) && !isset($_REQUEST["search_classes"]) && !isset($_REQUEST["search_alumni"])) ? "checked=\"checked\" " : ""); ?>value="faculty" onclick="addGroup()" /><label class="content-small" for="faculty"> Faculty</label></td>
								</tr>
								<tr>
									<td><input class="search_groups" id="resident" type="checkbox" <?php echo ((isset($_REQUEST["search_groups"]) && is_array(explode(',', $_REQUEST["search_groups"])) && array_search("resident", (explode(',', $_REQUEST["search_groups"]))) !== false) || (isset($_REQUEST["search_groups"]) && $_REQUEST["search_groups"] == "resident") || (!isset($_REQUEST["search_groups"]) && !isset($_REQUEST["search_classes"]) && !isset($_REQUEST["search_alumni"])) ? "checked=\"checked\" " : ""); ?>value="resident" onclick="addGroup()" /><label class="content-small" for="resident"> Residents</label></td>
									<td><input class="search_groups" id="staff" type="checkbox" <?php echo ((isset($_REQUEST["search_groups"]) && is_array(explode(',', $_REQUEST["search_groups"])) && array_search("staff", (explode(',', $_REQUEST["search_groups"]))) !== false) || (isset($_REQUEST["search_groups"]) && $_REQUEST["search_groups"] == "staff") || (!isset($_REQUEST["search_groups"]) && !isset($_REQUEST["search_classes"]) && !isset($_REQUEST["search_alumni"])) ? "checked=\"checked\" " : ""); ?>value="staff" onclick="addGroup()" /><label class="content-small" for="staff"> Staff</label></td>
								</tr>
								<tr>
									<td><input class="search_classes" id="class_<?php echo (date("Y", time()) + $year_offset); ?>" type="checkbox" <?php echo ((isset($_REQUEST["search_classes"]) && is_array(explode(',', $_REQUEST["search_classes"])) && array_search((date("Y", time()) + $year_offset), (explode(',', $_REQUEST["search_classes"]))) !== false) || (isset($_REQUEST["search_classes"]) && $_REQUEST["search_classes"] == (date("Y", time()) + $year_offset)) || (!isset($_REQUEST["search_groups"]) && !isset($_REQUEST["search_classes"]) && !isset($_REQUEST["search_alumni"])) ? "checked=\"checked\" " : ""); ?>value="<?php echo (date("Y", time()) + $year_offset); ?>" onclick="addClass()" /><label class="content-small" for="class_<?php echo (date("Y", time()) + $year_offset); ?>"> Class of <?php echo (date("Y", time()) + $year_offset); ?></label></td>
									<td><input class="search_classes" id="class_<?php echo (date("Y", time()) + $year_offset + 1); ?>" type="checkbox" <?php echo ((isset($_REQUEST["search_classes"]) && is_array(explode(',', $_REQUEST["search_classes"])) && array_search((date("Y", time()) + $year_offset + 1), (explode(',', $_REQUEST["search_classes"]))) !== false) || (isset($_REQUEST["search_classes"]) && $_REQUEST["search_classes"] == (date("Y", time()) + $year_offset + 1)) || (!isset($_REQUEST["search_groups"]) && !isset($_REQUEST["search_classes"]) && !isset($_REQUEST["search_alumni"])) ? "checked=\"checked\" " : ""); ?>value="<?php echo (date("Y", time()) + $year_offset + 1); ?>" onclick="addClass()" /><label class="content-small" for="class_<?php echo (date("Y", time()) + $year_offset + 1); ?>"> Class of <?php echo (date("Y", time()) + $year_offset + 1); ?></label></td>
								</tr>
								<tr>
									<td><input class="search_classes" id="class_<?php echo (date("Y", time()) + $year_offset + 2); ?>" type="checkbox" <?php echo ((isset($_REQUEST["search_classes"]) && is_array(explode(',', $_REQUEST["search_classes"])) && array_search((date("Y", time()) + $year_offset + 2), (explode(',', $_REQUEST["search_classes"]))) !== false) || (isset($_REQUEST["search_classes"]) && $_REQUEST["search_classes"] == (date("Y", time()) + $year_offset + 2)) || (!isset($_REQUEST["search_groups"]) && !isset($_REQUEST["search_classes"]) && !isset($_REQUEST["search_alumni"])) ? "checked=\"checked\" " : ""); ?>value="<?php echo (date("Y", time()) + $year_offset + 2); ?>" onclick="addClass()" /><label class="content-small" for="class_<?php echo (date("Y", time()) + $year_offset + 2); ?>"> Class of <?php echo (date("Y", time()) + $year_offset + 2); ?></label></td>
									<td><input class="search_classes" id="class_<?php echo (date("Y", time()) + $year_offset + 3); ?>" type="checkbox" <?php echo ((isset($_REQUEST["search_classes"]) && is_array(explode(',', $_REQUEST["search_classes"])) && array_search((date("Y", time()) + $year_offset + 3), (explode(',', $_REQUEST["search_classes"]))) !== false) || (isset($_REQUEST["search_classes"]) && $_REQUEST["search_classes"] == (date("Y", time()) + $year_offset + 3)) || (!isset($_REQUEST["search_groups"]) && !isset($_REQUEST["search_classes"]) && !isset($_REQUEST["search_alumni"])) ? "checked=\"checked\" " : ""); ?>value="<?php echo (date("Y", time()) + $year_offset + 3); ?>" onclick="addClass()" /><label class="content-small" for="class_<?php echo (date("Y", time()) + $year_offset + 3); ?>"> Class of <?php echo (date("Y", time()) + $year_offset + 3); ?></label></td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
			</tbody>
			</table>
			</form>
		</div>
		<div class="tab-page">
			<h2 class="tab">Browse People</h2>
			<form id="browse-group_form" action="<?php echo ENTRADA_URL; ?>/people" method="get">
			<input type="hidden" name="type" value="browse-group" />
			<input type="hidden" name="pv" id="browse-group_pv" value="<?php echo ($page_current ? $page_current : 1);?>" />
			<input type="hidden" name="pp" id="browse-group_pp" value="<?php echo $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]; ?>" />
			<table style="width: 100%" cellspacing="1" cellpadding="1" border="0" summary="Browse By Groups">
			<colgroup>
				<col style="width: 3%" />
				<col style="width: 25%" />
				<col style="width: 72%" />
			</colgroup>
			<tfoot>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
						<input type="submit" class="button" value="Browse" />
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td>&nbsp;</td>
					<td><label for="group" class="form-required">Browse Group:</label></td>
					<td>
						<select id="group" name="g" style="width: 209px"></select>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><label for="role" class="form-nrequired">Browse Role:</label></td>
					<td>
						<select id="role" name="r" style="width: 209px"></select>
					</td>
				</tr>
			</tbody>
			</table>
			</form>
		</div>
		<div class="tab-page">
			<h2 class="tab">Browse Departments</h2>
			<form id="browse-dept_form" action="<?php echo ENTRADA_URL; ?>/people" method="get">
			<input type="hidden" name="type" value="browse-dept" />
			<input type="hidden" name="pv" id="browse-dept_pv" value="<?php echo ($page_current ? $page_current : 1);?>" />
			<input type="hidden" name="pp" id="browse-dept_pp" value="<?php echo $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]; ?>" />
			<table style="width: 100%" cellspacing="1" cellpadding="1" border="0" summary="Browse By Department">
			<colgroup>
				<col style="width: 3%" />
				<col style="width: 25%" />
				<col style="width: 72%" />
			</colgroup>
			<tfoot>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
						<input type="submit" class="button" value="Browse" />
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td>&nbsp;</td>
					<td><label for="department" class="form-required">Browse Department:</label></td>
					<td>
						<select id="department" name="d" style="width: 95%">
						<?php
						$query = "	SELECT a.`department_id`, a.`department_title`, a.`organisation_id`, b.`entity_title`, c.`organisation_title`
									FROM `".AUTH_DATABASE."`.`departments` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`entity_type` AS b
									ON a.`entity_id` = b.`entity_id`
									LEFT JOIN `".AUTH_DATABASE."`.`organisations` AS c
									ON a.`organisation_id` = c.`organisation_id`
									WHERE a.`department_active` = '1'
									ORDER BY c.`organisation_title` ASC, a.`department_title`";
						$results	= $db->GetAll($query);
						if ($results) {
							$organisation_title = "";

							foreach ($results as $key => $result) {
								if ($organisation_title != $result["organisation_title"]) {
									if ($key) {
										echo "</optgroup>";
									}
									echo "<optgroup label=\"".html_encode($result["organisation_title"])."\">";

									$organisation_title = $result["organisation_title"];
								}

								echo "<option value=\"".(int) $result["department_id"]."\"".(((isset($browse_department)) && ((int) $browse_department) && ($browse_department == $result["department_id"])) ? " selected=\"selected\"" : "").">".html_encode($result["department_title"])."</option>\n";
							}
							echo "</optgroup>";
						}
						?>
						</select>
					</td>
				</tr>
			</tbody>
			</table>
			</form>
		</div>
	</div>
	<script type="text/javascript">setupAllTabs(true);</script>
	<?php
	if (($search_query) || (isset($load_profile) && $load_profile)) {
		if ($search_query) {
			if ($total_pages > 1) {
				echo "<br />\n";
				echo "<div style=\"text-align: right\">\n";
				echo "<form action=\"".ENTRADA_URL."/".$MODULE."\" method=\"get\" id=\"pageSelector\" style=\"display: inline\">\n";
				echo "<span style=\"width: 20px; vertical-align: middle; margin-right: 3px; text-align: left\">\n";
				if ($page_previous) {
					echo "<a href=\"".ENTRADA_URL."/".$MODULE."?".replace_query(array("pv" => $page_previous))."\"><img src=\"".ENTRADA_URL."/images/record-previous-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Back to page ".$page_previous.".\" title=\"Back to page ".$page_previous.".\" style=\"vertical-align: middle\" /></a>\n";
				} else {
					echo "<img src=\"".ENTRADA_URL."/images/record-previous-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
				}
				echo "</span>";
				echo "<span style=\"vertical-align: middle\">\n";
				echo "<select name=\"pv\" onchange=\"window.location = '".ENTRADA_URL."/".$MODULE."?".replace_query(array("pv" => false))."&amp;pv='+this.options[this.selectedIndex].value;\"".(($total_pages <= 1) ? " disabled=\"disabled\"" : "").">\n";
				for ($i = 1; $i <= $total_pages; $i++) {
					echo "<option value=\"".$i."\"".(($i == $page_current) ? " selected=\"selected\"" : "").">".(($i == $page_current) ? " Viewing" : "Jump To")." Page ".$i."</option>\n";
				}
				echo "</select>\n";
				echo "</span>\n";
				echo "<span style=\"width: 20px; vertical-align: middle; margin-left: 3px; text-align: right\">\n";
				if ($page_current < $total_pages) {
					echo "<a href=\"".ENTRADA_URL."/".$MODULE."?".replace_query(array("pv" => $page_next))."\"><img src=\"".ENTRADA_URL."/images/record-next-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Forward to page ".$page_next.".\" title=\"Forward to page ".$page_next.".\" style=\"vertical-align: middle\" /></a>";
				} else {
					echo "<img src=\"".ENTRADA_URL."/images/record-next-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
				}
				echo "</span>\n";
				echo "</form>\n";
				echo "</div>\n";
			}
			/**
			 * Provides the first parameter of MySQLs LIMIT statement by calculating which row to start results from.
			 */
			$limit_parameter 	= (int) (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] * $page_current) - $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);

			$query_search		= sprintf($query_search, $limit_parameter, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
			$results			= $db->GetAll($query_search);
		} elseif ($load_profile) {
			$results			= $db->GetAll($query_profile);
			if (!$results) {
				$query_profile	= "
								SELECT a.*, b.`group`, b.`role`
								FROM `".AUTH_DATABASE."`.`user_data` AS a
								LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
								ON b.`user_id` = a.`id`
								WHERE  b.`app_id` IN (".AUTH_APP_IDS_STRING.")
								AND b.`account_active` = 'true'
								AND (b.`access_starts` = '0' OR b.`access_starts` < ".$db->qstr(time()).")
								AND (b.`access_expires` = '0' OR b.`access_expires` >= ".$db->qstr(time()).")
								AND ".((is_numeric($load_profile)) ? "a.`id` = ".$db->qstr((int) $load_profile) : "a.`username` = ".$db->qstr($load_profile))."
								GROUP BY a.`id`";
				$results		= $db->GetAll($query_profile);
			}
			$search_query		= $load_profile;
			$total_rows 		= 1;
			$limit_parameter	= 5;
			$total_pages		= 1;
		}
		
		if ($results) {
			echo "<br />\n";
			echo "<div class=\"searchTitle\" style=\"margin: auto;\">\n";
			echo "	<table style=\"width: 100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
			echo "	<tbody>\n";
			echo "		<tr>\n";
			echo "			<td style=\"font-size: 14px; font-weight: bold; color: #003366\">People Search Results:</td>\n";
			echo "			<td style=\"text-align: right; font-size: 10px; color: #666666; overflow: hidden; white-space: nowrap\">".$total_rows." Result".(($total_rows != 1) ? "s" : "")." Found. Results ".($limit_parameter + 1)." - ".((($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] + $limit_parameter) <= $total_rows) ? ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] + $limit_parameter) : $total_rows)." for &quot;<strong>".$search_query."</strong>&quot; shown below.</td>\n";
			echo "		</tr>\n";
			echo "		</tbody>\n";
			echo "	</table>\n";
			echo "</div>";
			
			foreach ($results as $key => $result) {
				echo "<div id=\"result-".$result["id"]."\" style=\"width: 100%; padding: 5px 0px 5px 5px; line-height: 16px; text-align: left; border-bottom: 1px solid rgb(204, 204, 204);".($key % 2 == 1 ? "background-color: rgb(238, 238, 238);" : "")."\">\n";
				echo "	<table style=\"width: 100%;\" class=\"profile-card\">\n";
				echo "	<colgroup>\n";
				echo "		<col style=\"width: 15%\" />\n";
				echo "		<col style=\"width: 25%\" />\n";
				echo "		<col style=\"width: 38%\" />\n";
				echo "		<col style=\"width: 22%\" />\n";
				echo "	<colgroup>";
				echo "	<tr>";
				echo "		<td>";
				echo "			<div id=\"img-holder-".$result["id"]."\" class=\"img-holder\">\n";
				
				$offical_file_active	= false;
				$uploaded_file_active	= false;

				/**
				 * If the photo file actually exists, and either
				 * 	If the user is in an administration group, or
				 *  If the user is trying to view their own photo, or
				 *  If the proxy_id has their privacy set to "Any Information"
				 */
				if ((@file_exists(STORAGE_USER_PHOTOS."/".$result["id"]."-official")) && ($ENTRADA_ACL->amIAllowed(new PhotoResource($result["id"], (int) $result["privacy_level"], "official"), "read"))) {
					$offical_file_active	= true;
				}

				/**
				 * If the photo file actually exists, and
				 * If the uploaded file is active in the user_photos table, and
				 * If the proxy_id has their privacy set to "Basic Information" or higher.
				 */
				$query			= "SELECT `photo_active` FROM `".AUTH_DATABASE."`.`user_photos` WHERE `photo_type` = '1' AND `photo_active` = '1' AND `proxy_id` = ".$db->qstr($result["id"]);
				$photo_active	= $db->GetOne($query);
				if ((@file_exists(STORAGE_USER_PHOTOS."/".$result["id"]."-upload")) && ($photo_active) && ($ENTRADA_ACL->amIAllowed(new PhotoResource($result["id"], (int) $result["privacy_level"], "upload"), "read"))) {
					$uploaded_file_active = true;
				}

				if ($offical_file_active) {
					echo "		<img id=\"official_photo_".$result["id"]."\" class=\"official\" src=\"".webservice_url("photo", array($result["id"], "official"))."\" width=\"72\" height=\"100\" alt=\"".html_encode($result["prefix"]." ".$result["firstname"]." ".$result["lastname"])."\" title=\"".html_encode($result["prefix"]." ".$result["firstname"]." ".$result["lastname"])."\" />\n";
				}
 
				if ($uploaded_file_active) {
					echo "		<img id=\"uploaded_photo_".$result["id"]."\" class=\"uploaded\" src=\"".webservice_url("photo", array($result["id"], "upload"))."\" width=\"72\" height=\"100\" alt=\"".html_encode($result["prefix"]." ".$result["firstname"]." ".$result["lastname"])."\" title=\"".html_encode($result["prefix"]." ".$result["firstname"]." ".$result["lastname"])."\" />\n";
				}

				if (($offical_file_active) || ($uploaded_file_active)) {
					echo "		<a id=\"zoomin_photo_".$result["id"]."\" class=\"zoomin\" onclick=\"growPic($('official_photo_".$result["id"]."'), $('uploaded_photo_".$result["id"]."'), $('official_link_".$result["id"]."'), $('uploaded_link_".$result["id"]."'), $('zoomout_photo_".$result["id"]."'));\">+</a>";	
					echo "		<a id=\"zoomout_photo_".$result["id"]."\" class=\"zoomout\" onclick=\"shrinkPic($('official_photo_".$result["id"]."'), $('uploaded_photo_".$result["id"]."'), $('official_link_".$result["id"]."'), $('uploaded_link_".$result["id"]."'), $('zoomout_photo_".$result["id"]."'));\"></a>";
				} else {
					echo "		<img src=\"".ENTRADA_URL."/images/headshot-male.gif\" width=\"72\" height=\"100\" alt=\"No Photo Available\" title=\"No Photo Available\" />\n";
				}
				
				if (($offical_file_active) && ($uploaded_file_active)) {
					echo "		<a id=\"official_link_".$result["id"]."\" class=\"img-selector one\" onclick=\"showOfficial($('official_photo_".$result["id"]."'), $('official_link_".$result["id"]."'), $('uploaded_link_".$result["id"]."'));\" href=\"javascript: void(0);\">1</a>";
					echo "		<a id=\"uploaded_link_".$result["id"]."\" class=\"img-selector two\" onclick=\"hideOfficial($('official_photo_".$result["id"]."'), $('official_link_".$result["id"]."'), $('uploaded_link_".$result["id"]."'));\" href=\"javascript: void(0);\">2</a>";
				}

				echo "			</div>\n";
				echo "		</td>\n";
				echo "		<td style=\"font-size: 12px; color: #003366; vertical-align: top\">";
				echo "			<div style=\"font-weight: bold; font-size: 13px;\">".html_encode((($result["prefix"]) ? $result["prefix"]." " : "").$result["firstname"]." ".$result["lastname"])."</div>";
				if($departmentResults = get_user_departments($result["id"])) {
					echo "			<div class=\"content-small\" style=\"margin-bottom: 15px\">";
					$deptCtr = 0;
					foreach($departmentResults as $key => $departmentValue) {
						if ($deptCtr == 0) {
							$deptCtr++;
							echo ucwords($departmentValue["department_title"]);
						} else {
							$deptCtr++;
							echo "<br />".ucwords($departmentValue["department_title"]);
						}
					}
				} else {
					if ($result["group"] == "student") {
						$cohort = groups_get_cohort($result["id"]);
					}
					echo "			<div class=\"content-small\" style=\"margin-bottom: 15px\">".ucwords($result["group"])." > ".($result["group"] == "student" ? $cohort["group_name"] : ucwords($result["role"]));
				}
				echo (isset($ORGANISATIONS_BY_ID[$result["organisation_id"]]) ? "<br />".$ORGANISATIONS_BY_ID[$result["organisation_id"]]["organisation_title"] : "")."</div>\n";
				if ($result["privacy_level"] > 1 || $is_administrator) {
					echo "			<a href=\"mailto:".html_encode($result["email"])."\" style=\"font-size: 10px;\">".html_encode($result["email"])."</a><br />\n";
					
					if ($result["email_alt"]) {
						echo "		<a href=\"mailto:".html_encode($result["email_alt"])."\" style=\"font-size: 10px;\">".html_encode($result["email_alt"])."</a>\n";
					}
				}
				echo "		</td>\n";
				echo "		<td style=\"padding-top: 1.3em;\">\n";
				echo "			<div>\n";
				echo "				<table class=\"address-info\" style=\"width: 100%;\">\n";
				if (($result["privacy_level"] > 2 || $is_administrator)) {
					if ($result["telephone"]) {
						echo "			<tr>\n";
						echo "				<td style=\"width: 30%;\">Telephone: </td>\n";
						echo "				<td>".html_encode($result["telephone"])."</td>\n";
						echo "			</tr>\n";
					}
					if ($result["fax"]) {
						echo "			<tr>\n";
						echo "				<td>Fax: </td>\n";
						echo "				<td>".html_encode($result["fax"])."</td>\n";
						echo "			</tr>\n\n";
					}
					if ($result["address"] && $result["city"]) {
						echo "			<tr>\n";
						echo "				<td><br />Address: </td>\n";
						echo "				<td><br />".html_encode($result["address"])."</td>\n";
						echo "			</tr>\n";
						echo "			<tr>\n";
						echo "				<td>&nbsp;</td>\n";
						echo "				<td>".html_encode($result["city"].($result["city"] && $result["province"] ? ", ".$result["province"] : ""))."</td>\n";
						echo "			</tr>\n";
						echo "			<tr>\n";
						echo "				<td>&nbsp;</td>\n";
						echo "				<td>".html_encode($result["country"].($result["country"] && $result["postcode"] ? ", ".$result["postcode"] : ""))."</td>\n";
						echo "			</tr>\n";
					}
					if ($result["office_hours"]) {
						echo "			<tr><td colspan=\"2\">&nbsp;</td></tr>";
						echo "			<tr>\n";
						echo "				<td>Office Hours: </td>\n";
						echo "				<td>".nl2br(html_encode($result["office_hours"]))."</td>\n";
						echo "			</tr>\n\n";
					}
				}
				echo "				</table>\n";
				echo "			</div>\n";
				echo "		</td>\n";
				echo "		<td style=\"padding-top: 1.3em; vertical-align: top\">\n";
				
				$query		= "	SELECT CONCAT_WS(' ', b.`firstname`, b.`lastname`) AS `fullname`, b.`email`
								FROM `permissions` AS a
								LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
								ON b.`id` = a.`assigned_to`
								WHERE a.`assigned_by`=".$db->qstr($result["id"])."
								AND (a.`valid_from` = '0' OR a.`valid_from` <= ".$db->qstr(time()).") AND (a.`valid_until` = '0' OR a.`valid_until` > ".$db->qstr(time()).")
								ORDER BY `valid_until` ASC";
				$assistants	= $db->GetAll($query);
				if ($assistants) {
					echo "		<span class=\"content-small\">Administrative Assistants:</span>\n";
					echo "		<ul class=\"assistant-list\">";
					foreach ($assistants as $assistant) {
						echo "		<li><a href=\"mailto:".html_encode($assistant["email"])."\">".html_encode($assistant["fullname"])."</a></li>";
					}
					echo "		</ul>";
				}
				echo "		</td>\n";
				echo "	</tr>\n";
				echo "	</table>\n";
				echo "</div>\n";
			}
			
		} else {
			echo "<div class=\"display-notice\">\n";
			echo "	<h3>No Matching People</h3>\n";
			echo "	There are no people in the system found which contain matches to &quot;<strong>".$search_query."</strong>&quot;.";
			echo "</div>\n";
		}
		if ($total_pages > 1) {
			echo "<br />\n";
			echo "<div style=\"text-align: right\">\n";
			echo "<form action=\"".ENTRADA_URL."/".$MODULE."\" method=\"get\" id=\"pageSelector\" style=\"display: inline\">\n";
			echo "<span style=\"width: 20px; vertical-align: middle; margin-right: 3px; text-align: left\">\n";
			if ($page_previous) {
				echo "<a href=\"".ENTRADA_URL."/".$MODULE."?".replace_query(array("pv" => $page_previous))."\"><img src=\"".ENTRADA_URL."/images/record-previous-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Back to page ".$page_previous.".\" title=\"Back to page ".$page_previous.".\" style=\"vertical-align: middle\" /></a>\n";
			} else {
				echo "<img src=\"".ENTRADA_URL."/images/record-previous-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
			}
			echo "</span>";
			echo "<span style=\"vertical-align: middle\">\n";
			echo "<select name=\"pv\" onchange=\"window.location = '".ENTRADA_URL."/".$MODULE."?".replace_query(array("pv" => false))."&amp;pv='+this.options[this.selectedIndex].value;\"".(($total_pages <= 1) ? " disabled=\"disabled\"" : "").">\n";
			for ($i = 1; $i <= $total_pages; $i++) {
				echo "<option value=\"".$i."\"".(($i == $page_current) ? " selected=\"selected\"" : "").">".(($i == $page_current) ? " Viewing" : "Jump To")." Page ".$i."</option>\n";
			}
			echo "</select>\n";
			echo "</span>\n";
			echo "<span style=\"width: 20px; vertical-align: middle; margin-left: 3px; text-align: right\">\n";
			if ($page_current < $total_pages) {
				echo "<a href=\"".ENTRADA_URL."/".$MODULE."?".replace_query(array("pv" => $page_next))."\"><img src=\"".ENTRADA_URL."/images/record-next-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Forward to page ".$page_next.".\" title=\"Forward to page ".$page_next.".\" style=\"vertical-align: middle\" /></a>";
			} else {
				echo "<img src=\"".ENTRADA_URL."/images/record-next-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
			}
			echo "</span>\n";
			echo "</form>\n";
			echo "</div>\n";
		}
	}
	
	/**
	 * Sidebar item that will provide another method for sorting, ordering, etc.
	 */
	$sidebar_html  = "<ul class=\"menu\">\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "5") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/people?".replace_query(array("pp" => "5"))."\" title=\"Display 5 Profiles Per Page\">5 profiles per page</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "15") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/people?".replace_query(array("pp" => "15"))."\" title=\"Display 15 Profiles Per Page\">15 profiles per page</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "25") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/people?".replace_query(array("pp" => "25"))."\" title=\"Display 25 Profiles Per Page\">25 profiles per page</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "50") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/people?".replace_query(array("pp" => "50"))."\" title=\"Display 50 Profiles Per Page\">50 profiles per page</a></li>\n";
	$sidebar_html .= "</ul>\n";

	new_sidebar_item("Profiles Per Page", $sidebar_html, "sort-results", "open");	
}
?>