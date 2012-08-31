<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Allows administrators to edit user incidents in the entrada_auth.user_incidents table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_USERS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("user", "update", false)) {
	$ERROR++;
	$ERRORSTR[]	= "You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$GROUP."] and role [".$ROLE."] do not have access to this module [".$MODULE."]");
} else {
	define("IN_MANAGE_USER", true);
	
	$query = "	SELECT * FROM `".AUTH_DATABASE."`.`user_access` AS a
				LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
				ON a.`user_id` = `b`.`id`
				WHERE b.`id` = ?
				AND a.`app_id` = ?";
	$user_record = $db->GetRow($query, array($PROXY_ID, AUTH_APP_ID));
	if (($user_record) && ($router) && ($router->initRoute())) {
		$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/users/manage?id=".$PROXY_ID, "title" => "Manage ".html_encode($user_record["firstname"]." ".$user_record["lastname"]));

		$module_file = $router->getRoute();
		if ($module_file) {
		
			add_user_management_sidebar();
			if ($user_record['group'] == 'student') {
				add_student_management_sidebar();
			}
			require_once($module_file);
		}
	} else {
		$url = ENTRADA_URL."/admin/".$MODULE;
		application_log("error", "The Entrada_Router failed to load a request. The user was redirected to [".$url."].");

		header("Location: ".$url);
		exit;
	}
}

/**
 * Creates the profile sidebar to appear on all profile pages. The sidebar content will vary depending on the permissions of the user.
 * 
 */
function add_user_management_sidebar () {
	global $ENTRADA_ACL, $PROXY_ID;
	$baseurl = ENTRADA_URL."/admin/users/manage";
	$sidebar_html  = "<ul class=\"menu\">";
	$sidebar_html .= "	<li class=\"link\"><a href=\"".$baseurl."?id=".$PROXY_ID."\">Overview</a></li>\n";
	//TODO -- uncomment when ready $sidebar_html .= "	<li class=\"link\"><a href=\"".$baseurl."/report?id=".$PROXY_ID."\">User Report</a></li>\n";
	$sidebar_html .= "	<li class=\"link\"><a href=\"".$baseurl."?section=edit&id=".$PROXY_ID."\">Edit Profile</a></li>\n";
	$sidebar_html .= "	<li class=\"link\"><a href=\"".$baseurl."/metadata?id=".$PROXY_ID."\">Edit Meta Data</a></li>\n";
	$sidebar_html .= "	<li class=\"link\"><a href=\"".$baseurl."/incidents?id=".$PROXY_ID."\">Incidents</a></li>\n";
	$sidebar_html .= "</ul>";

	new_sidebar_item("User Management", $sidebar_html, "user-management-nav", "open");
}

function add_student_management_sidebar () {
	global $ENTRADA_ACL, $PROXY_ID;
	$baseurl = ENTRADA_URL."/admin/users/manage/students";
	$sidebar_html  = "<ul class=\"menu\">";
	$sidebar_html .= "	<li class=\"link\"><a href=\"".$baseurl."?section=mspr&id=".$PROXY_ID."\">MSPR</a></li>\n";
	$sidebar_html .= "	<li class=\"link\"><a href=\"".$baseurl."?section=leavesofabsence&id=".$PROXY_ID."\">Leaves of Absence</a></li>\n";
	$sidebar_html .= "	<li class=\"link\"><a href=\"".$baseurl."?section=formalremediation&id=".$PROXY_ID."\">Formal Remediation</a></li>\n";
	$sidebar_html .= "	<li class=\"link\"><a href=\"".$baseurl."?section=disciplinaryactions&id=".$PROXY_ID."\">Disciplinary Actions</a></li>\n";
	$sidebar_html .= "</ul>";

	new_sidebar_item("Student Management", $sidebar_html, "student-management-nav", "open");
}

function add_mspr_management_sidebar () {
	global $ENTRADA_ACL, $PROXY_ID;
	$user = User::get($PROXY_ID);
	$year = $user->getGradYear();
	$sidebar_html  = "<ul class=\"menu\">";
	$sidebar_html .= "	<li class=\"link\"><a href=\"".ENTRADA_URL."/admin/users/manage/students?section=mspr-options&id=".$PROXY_ID."\">MSPR Options</a></li>\n";
	$sidebar_html .= "	<li class=\"link\"><a href=\"".ENTRADA_URL."/admin/users/manage/students?section=mspr-revisions&id=".$PROXY_ID."\">MSPR File Revisions</a></li>\n";
	$sidebar_html .= "	<li class=\"link\"><a href=\"".ENTRADA_URL."/admin/mspr?mode=year&year=".$year ."\">Manage Class of ". $year ." MSPRs</a></li>\n";
	$sidebar_html .= "	<li class=\"link\"><a href=\"".ENTRADA_URL."/admin/mspr?mode=all\">Manage All MSPRs Requiring Attention</a></li>\n";
	
	$sidebar_html .= "</ul>";

	new_sidebar_item("MSPR Management", $sidebar_html, "mspr-management-nav", "open");
}

?>