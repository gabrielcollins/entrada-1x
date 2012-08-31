#!/usr/bin/php
<?php
/**
 * Entrada Tools [ http://www.entrada-project.org ]
 *
 * Entrada is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Entrada is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	 See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Entrada.	If not, see <http://www.gnu.org/licenses/>.
 *
 * Tools: Planetary Collision. Used for merging two previously independent 
 * Entrada installations into one big clusterfuck of a database. 
 *
 * @author Unit: Medical Education Technology Unit
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 */

/**
 * MERGE NOTES
 * Ensure courses.parent_id field is gone
 * Ensure courses.course_active (int default 1) is present
*/
set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__)."/includes");

set_time_limit(0);
error_reporting(E_ALL);
ini_set("display_errors", true);
require_once("classes/adodb/adodb.inc.php");
$ADODB_QUOTE_FIELDNAMES = true;

$COLLIDE_ID			= "collisionconstant";
$DBUSER 			= "hbrundage";
$PASSWORD 			= "balls";
$HOST 				= "developer.qmed.ca";
$DATABASE_TYPE		= "mysql";

$PRIMARY_DB_CONF		= array("host" => $HOST, "database_prefix" => "shared_test_", "user" => $DBUSER, "pass"=>$PASSWORD, "app_id" => 1);
$SECONDARY_DB_CONF		= array("host" => $HOST, "database_prefix" => "rehab_test_"	, "user" => $DBUSER, "pass"=>$PASSWORD, "app_id" => 700);
$DESTINATION_DB_CONF	= array("host" => $HOST, "database_prefix" => "destination_", "user" => $DBUSER, "pass"=>$PASSWORD);

$DB_CONNECTIONS = array();

$MERGE = array("truncate" 		=> true, 
				"users" 		=> true, 
				"passwords" 	=> true, 
				"auth" 			=> true, 
				"clerkship" 	=> true, 
				"communities" 	=> true, 
				"courses" 		=> true, 
				"support" 		=> true
			);

function get_db_connection($which, $database_name) {
	global $DB_CONNECTIONS, $DATABASE_TYPE;
	$key = $which.$database_name;
	if(isset($DB_CONNECTIONS[$key])) {
		return $DB_CONNECTIONS[$key];
	} else {
		$conf_name = strtoupper($which) . "_DB_CONF";
		global $$conf_name;
		$pref = $$conf_name;
		// Set up new connection
		$db = &NewADOConnection($DATABASE_TYPE);
		$db->NConnect($pref["host"], $pref["user"], $pref["pass"], $pref["database_prefix"].$database_name);
		$db->SetFetchMode(ADODB_FETCH_ASSOC);
		$DB_CONNECTIONS[$key] = $db;
		return $db;
	}
}
function transform_values($fields, $transforms) {
	global $PROXY_ID_MAP, $COLLIDE_ID;
	foreach($fields as $name => &$field) {
		if(isset($transforms[$name])) {
			$t = $transforms[$name];
			if($t === false) {
				unset($fields[$name]);
			}
			if(is_numeric($field) && $field != 0) {
				$field = $field+$t;
			}
			if($t == $COLLIDE_ID) {
				$field = get_primary_proxy_id($field);
			}
		}
	}
	return $fields;
}
function process_transforms($transforms) {
	if(isset($transforms["secondary"])) {
		return $transforms;
	} else {
		return array("secondary" => $transforms, "primary" => array());
	}
}
function process_skip_ids($skip_ids) {
	if(empty($skip_ids)) {
		return array("primary" => array(), "secondary" => array(), "field_name" => "id");
	}
	if(isset($skip_ids["primary"])) {
		return $skip_ids;
	} else {
		return array("primary" => array(), "secondary" => $skip_ids, "field_name" => (isset($skip_ids["field_name"]) ? $skip_ids["field_name"] : "id"));
	}
}
function process_record($rs, $field_name, $skip_ids) {
	if(isset($rs->fields[$field_name])) {
		return ! in_array($rs->fields[$field_name], $skip_ids);
	} else {
		return true;
	}
}
function move_table($source, $which_db, $table, $transforms = array(), $skip_id_fieldname = "", $skip_ids = array(), $only = array()) {	
	// Get DBS from global pool
	$source_db = get_db_connection($source, $which_db);
 	$destination_db = get_db_connection("destination", $which_db);

	// Find all the records to be moved
	$sql = "SELECT * FROM $table;";
	$rs = $source_db->Execute($sql);

	echo "Moving $source records in $table from ".$source_db->database." to ".$destination_db->database.".\n";

	$i = 0;
	while (!$rs->EOF) {
		//Decide if this record is to be processed 
		if(process_record($rs, $skip_id_fieldname, $skip_ids)) {
			$sql = $source_db->GetInsertSQL($rs, transform_values($rs->fields, $transforms));
			$success = $destination_db->Execute($sql);
			if(!$success) {
				echo "---------Error!! \n";
				var_dump($sql);
				echo "DB Said: " . $destination_db->ErrorMsg() . "\n";
				break;
			}
			//Count up
			$i++;
			if($i % 500 == 0) {
				echo $i." ";
			}
		}
		$rs->MoveNext();	
	}
	echo "\n $i records moved. \n";
	$rs->Close();
}
function merge_tables($which_db, $table, $transforms = array(), $skip_ids = array(), $only = array()) {	
	$transforms = process_transforms($transforms);
	$skip_ids = process_skip_ids($skip_ids);
	foreach(array("primary", "secondary") as $source) {
		move_table($source, $which_db, $table, $transforms[$source], $skip_ids["field_name"], $skip_ids[$source], $only);
	}	
}
function move_table_array($source, $which_db, $tables) {
	foreach($tables as $table) {
		move_table($source, $which_db, $table);
	}
}
function merge_table_array($which_db, $tables, $transforms) {
	foreach($tables as $table) {
		merge_tables($which_db, $table, $transforms);
	}
}
function copy_all_tables($source, $which_db) {
	foreach(get_all_tables($source, $which_db) as $table) {
		move_table($source, $which_db, $table);
	}
}
function truncate_all_tables($set, $which_db) {
	$db = get_db_connection($set, $which_db);
	foreach(get_all_tables($set, $which_db) as $table) {
		$db->Execute("TRUNCATE TABLE `".$table."`");
	}
}
function get_all_tables($source, $which_db) {
	$conf_name = strtoupper($source) . "_DB_CONF";
	global $$conf_name;
	$pref = $$conf_name;
	$source_db_name = $pref["database_prefix"].$which_db;

	$source_db = get_db_connection($source, $which_db);
	$tables_raw = $source_db->GetAll("SHOW TABLES");
	$tables = array();
	foreach($tables_raw as $array) {
		$tables[] = $array["Tables_in_".$source_db_name];
	}
	return $tables;
}
function build_proxy_id_map() {
	// $PROXY_ID_MAP[secondary] = primary
	global $PRIMARY_DB_CONF, $SECONDARY_DB_CONF;
	$primary_db = $PRIMARY_DB_CONF["database_prefix"]."auth";
	$secondary_db = $SECONDARY_DB_CONF["database_prefix"]."auth";
	$db = get_db_connection("destination", "auth");
		
	$sql = "SELECT a . id as primary_id , b . id as secondary_id 
			FROM $primary_db.user_data AS a
			LEFT JOIN $secondary_db.user_data AS b ON b.username = a.username
			WHERE b.username IS NOT NULL";
			
	$ids = $db->GetAll($sql);
	
	$map = array();
	foreach($ids as $key => $fields) {
		$map[$fields["secondary_id"]] = $fields["primary_id"];
	}
	return $map;
}
function get_primary_proxy_id($secondary_id) {
	global $PROXY_ID_DELTA, $PROXY_ID_MAP;
	if(isset($PROXY_ID_MAP[$secondary_id])) {
		return $PROXY_ID_MAP[$secondary_id];
	} else {
		return $secondary_id + $PROXY_ID_DELTA;
	}
}
function get_max($set, $which_db, $table, $field) {
	$data = get_db_connection($set, $which_db)->GetRow("SELECT MAX($field) FROM $table");
	return $data["MAX($field)"];
}
function get_deltas($db, $schema, &$transforms, &$to_transform) {
	foreach($schema as $var => $details) {
		$max = get_max("primary", $db, $details[0], $details[1]);
		if(!isset($max)) {
			define($var, 0);
		} else {
			define($var, $max);
		}
		
		if(constant($var) === null) {
			echo "Error getting delta.\n";
			var_dump($max);
			var_dump($var);
			var_dump($details);
		}
		
		if($details[2] == true) {
			$transforms[$details[1]] = constant($var);
		}
		
		if($details[3] == true) {
			$to_transform[] = $details[0];
		}
	}
	return true;
}
function merge_schema($db, $schema, &$transforms) {
	$to_transform = array();
	get_deltas($db, $schema, $transforms, $to_transform);
	merge_table_array($db, $to_transform, $transforms);
}

$mtime = microtime(); 
$mtime = explode(" ",$mtime); 
$mtime = $mtime[1] + $mtime[0]; 
$starttime = $mtime;

// General Structure:
// 1. Truncate existing tables
// 2.1 Build a proxy ID map based on user name, finding existing users in both databases and mapping them to one user in the destination
// 2.2 Merge user data and user access tables, converting duplicate user data records into two user access records
// 3. Merge the auth database by merging all the tables and then copying the most complete permission set
// 4. Copy the clerkship database since rehab doesn't use it
// 5. Merge the communities database
// 6. Merge the rest of the primary database (courses, events, whatnot)
// 7. Merge the support tables (global look up tables)
// 8. Add in the application and starting records for Nursing

$data = get_db_connection("primary", "auth")->GetRow("SELECT MAX(id) FROM user_data");
$PROXY_ID_DELTA = $data["MAX(id)"];

if($MERGE["truncate"] == true) {
	foreach(array("auth", "clerkship", "entrada") as $which) {
		echo "Truncating tables in $which \n";
		truncate_all_tables("destination", $which);
	}	
}

if($MERGE["users"] == true) {
	echo " =============   Merging users   =========== \n";
	$PROXY_ID_MAP = build_proxy_id_map($PROXY_ID_DELTA);
	merge_tables("auth", "user_data", array("id" => $COLLIDE_ID), array_keys($PROXY_ID_MAP));
	merge_tables("auth", "user_access", array("id" => false, "user_id" => $COLLIDE_ID));
	foreach($PROXY_ID_MAP as $secondary_id => $primary_id) {
		$access = get_db_connection("destination", "auth")->GetAll("SELECT * FROM user_access WHERE user_id = ".$primary_id." AND app_id = ".$PRIMARY_DB_CONF["app_id"]);
		if(isset($access[0])) {
			get_db_connection("destination", "auth")->AutoExecute("user_access", array("user_id" => $primary_id, "app_id" => $SECONDARY_DB_CONF["app_id"], "account_active" => true, "access_starts" => time(), "access_expires" => 0, "role" => $access[0]["role"], "group" => $access[0]["group"]), "INSERT");
		}
	}
	if($MERGE["passwords"] == true) {
		get_db_connection("destination", "auth")->Execute("UPDATE user_data SET password = '5f4dcc3b5aa765d61d8327deb882cf99';");
	}
}

if($MERGE["auth"] == true) {
	echo " =============    Merging auth   ============ \n";
	// Merge departments
	$data = get_db_connection("primary", "auth")->GetRow("SELECT MAX(department_id) FROM departments");
	$DEPARTMENT_ID_DELTA = $data["MAX(department_id)"];

	merge_tables("auth", "departments", array("department_id" => $DEPARTMENT_ID_DELTA));
	merge_tables("auth", "user_departments", array("udep_id" => false, "user_id" => $COLLIDE_ID, "dep_id" => $DEPARTMENT_ID_DELTA));

	merge_tables("auth", "organisations");
	merge_tables("auth", "registered_apps");
	
	// Merge user photos
	merge_tables("auth", "user_photos", array("photo_id" => false, "proxy_id" => $COLLIDE_ID));

	//Merge user preferences
	merge_tables("auth", "user_preferences", array("preference_id" => false, "proxy_id" => $COLLIDE_ID));
	
	move_table_array("primary", "auth", array("acl_permissions", "statistics", "user_incidents", "entity_type")); // None of this stuff from rehab is present/different
}

if($MERGE["clerkship"] == true) {
	echo " ==========   Merging clerkship   ========== \n";
	copy_all_tables("primary", "clerkship"); // Rehab has no clerkship tables at all
}

if($MERGE["communities"] == true) {
	echo " ============   Merging entrada   ========== \n";
	# 	Constant to assign delta to	,						table,								field,		transform all, basic merge
	$schema = array(
		"COMMUNITY_ID_DELTA"						=> array("communities", 					"community_id", 	true, false),
		"COMMUNITY_PAGE_ID_DELTA" 					=> array("community_pages", 				"cpage_id", 		true, false),
		"COMMUNITY_ANNOUNCEMENT_ID_DELTA" 			=> array("community_announcements", 		"cannouncement_id", true, true),
		"COMMUNITY_DISCUSSION_ID_DELTA"				=> array("community_discussions", 			"cdiscussion_id", 	true, true),
		"COMMUNITY_DISCUSSION_TOPIC_ID_DELTA" 		=> array("community_discussion_topics", 	"cdtopic_id", 		true, true),
		"COMMUNITY_EVENT_ID_DELTA" 					=> array("community_events", 				"cevent_id", 		true, true),
		"COMMUNITY_GALLERY_ID_DELTA" 				=> array("community_galleries", 			"cgallery_id", 		true, true),
		"COMMUNITY_GALLERY_PHOTO_ID_DELTA" 			=> array("community_gallery_photos", 		"cgphoto_id", 		true, true),
		"COMMUNITY_GALLERY_COMMENT_ID_DELTA" 		=> array("community_gallery_comments", 		"cgcomment_id", 	true, true),
		"COMMUNITY_HISTORY_ID_DELTA" 				=> array("community_history",	 			"chistory_id", 		true, true),
		"COMMUNITY_MAILING_LIST_MEMBER_ID_DELTA"	=> array("community_mailing_list_members", 	"cmlmember_id", 	true, true),
		"COMMUNITY_MAILING_LIST_ID_DELTA"	 		=> array("community_mailing_lists",			"cmlist_id", 		true, true),
		"COMMUNITY_MEMBER_ID_DELTA" 				=> array("community_members", 				"cmember_id", 		true, true),
		"COMMUNITY_MODULE_ID_DELTA"					=> array("community_modules", 				"cmodule_id", 		true, true),
		"COMMUNITY_NOTIFICATION_ID_DELTA"			=> array("community_notifications", 		"cnotification_id", true, false), // Needs polymorph callback
		"COMMUNITY_PAGE_OPTION_DELTA"				=> array("community_page_options", 			"cpoption_id", 		true, true),
		"COMMUNITY_POLL_ID_DELTA"					=> array("community_polls", 				"cpolls_id", 		true, true),
		"COMMUNITY_POLL_ACCESS_ID_DELTA"			=> array("community_polls_access", 			"cpaccess_id",		true, true),
		"COMMUNITY_POLL_QUESTION_ID_DELTA"			=> array("community_polls_questions", 		"cpquestion_id", 	true, true),
		"COMMUNITY_POLL_RESPONSE_ID_DELTA"			=> array("community_polls_responses", 		"cpresponses_id",	true, true),
		"COMMUNITY_POLL_RESULT_ID_DELTA"			=> array("community_polls_results", 		"cpresults_id", 	true, true),
		"COMMUNITY_SHARE_ID_DELTA"					=> array("community_shares", 				"cshare_id", 		true, true),
		"COMMUNITY_SHARE_COMMENT_ID_DELTA"			=> array("community_share_comments",		"cscomment_id", 	true, true),
		"COMMUNITY_SHARE_FILE_ID_DELTA"				=> array("community_share_files", 			"csfile_id", 		true, true),
		"COMMUNITY_SHARE_FILE_VERSION_ID_DELTA"		=> array("community_share_file_versions",	"csfversion_id",	true, true),
		"CRON_COMMUNITY_NOTIFICATION_ID_DELTA"		=> array("cron_community_notifications",	"ccnotification_id",true, true),
	);
	
	$community_transforms = array("proxy_id" => $COLLIDE_ID, "updated_by" => $COLLIDE_ID);
	merge_schema("entrada", $schema, $community_transforms);
	
	//Parents in primary remain in tact, rehab has no parents. Categories are consistent across installations, so pluck the complete set from primary.
	merge_tables("entrada", "communities", array("community_id" => COMMUNITY_ID_DELTA, "updated_by" => $COLLIDE_ID));
	merge_tables("entrada", "community_pages", array_merge($community_transforms, array("parent_id" => COMMUNITY_PAGE_ID_DELTA)));
	
	move_table("primary", "entrada", "communities_categories");
	move_table("primary", "entrada", "communities_modules");
	
	move_table("primary", "entrada", "community_courses"); // Rehab has no community courses
		
	// Needs Polymorphic proc
	// merge_tables("entrada", "community_history", array_merge());
		
	// Needs Polymorphic proc	
	// merge_tables("entrada", "community_notifications", array_merge($community_transforms, array("author_id" => $COLLIDE_ID)));
	
	// Should need polymorphic proc but rehab doesn't have this table, so just move it
	move_table("primary", "entrada", "community_notify_members");

	
	// Skip community permissions?
		
}

if($MERGE["courses"] == true) {
	echo "MAKE SURE THE REHAB COURSES.PARENT_ID FIELD IS GONE BEFORE ATTEMPTING MERGE! \n";
	echo " ============   Merging courses   ========== \n";
	# 	Constant to assign delta to	,				table,							field,		transform all, basic merge
	$schema = array(
		"COURSE_ID_DELTA"						=> array("courses", 					"course_id", 		true, true),
		"COURSE_CONTACT_ID_DELTA"				=> array("course_contacts",				"contact_id",	 	true, true),
		"COURSE_FILE_ID_DELTA"					=> array("course_files",				"id",			 	false, false),
		"COURSE_LINK_ID_DELTA"					=> array("course_links",				"id",		 		false, false),
		"COURSE_OBJECTIVE_ID_DELTA"				=> array("course_objectives",			"cobjective_id",	true, true), //objective_id is to events_lu_objectives
		"EVENT_ID_DELTA"						=> array("events",						"event_id",			true, true),
		"EVENT_AUDIENCE_ID_DELTA"				=> array("event_audience",				"eaudience_id",		true, true),
		"EVENT_CONTACT_ID_DELTA"				=> array("event_contacts",				"econtact_id",		true, true),
		"EVENT_DISCUSSION_ID_DELTA"				=> array("event_discussions",			"ediscussion_id",	true, true), //parent_id is not transformed, always 0
		"EVENT_ED10_ID_DELTA"					=> array("event_ed10",					"eed10_id",			true, true),
		"EVENT_ED11_ID_DELTA"					=> array("event_ed11",					"eed11_id",			true, true),
		"EVENT_FILE_ID_DELTA"					=> array("event_files",					"efile_id",			true, true),
		"EVENT_LINK_ID_DELTA"					=> array("event_links",					"elink_id",			true, true),
		"EVENT_QUIZ_ID_DELTA"					=> array("event_quizzes",				"equiz_id",			true, true), //relies on quiz_id, quiz_type_id
		"EVENT_QUIZ_PROGRESS_ID_DELTA"			=> array("event_quiz_progress",			"eqprogress_id",	true, true), //relies on quiz_id
		"EVENT_QUIZ_RESPONSES_ID_DELTA"			=> array("event_quiz_responses",		"eqresponse_id",	true, true), //relies on quiz_id, qquestion_id, qqresponse_id
		"EVENT_RELATED_ID"						=> array("event_related",				"erelated_id",		true, true), //should be polymorphic but rehab has no records
		"EVENT_RECURRING_ID_DELTA"				=> array("events_recurring",			"recurring_id",		true, true),
		"QUIZ_ID_DELTA"							=> array("quizzes",						"quiz_id",			true, true),
		"QUIZ_CONTACT_ID_DELTA"					=> array("quiz_contacts",				"qcontact_id",		true, true),
		"QUIZ_QUESTION_ID_DELTA"				=> array("quiz_questions",				"qquestion_id",		true, true),
		"QUIZ_QUESTION_RESPONSE_ID_DELTA"		=> array("quiz_question_responses",		"qqresponse_id",	true, true),
	);
	
	$course_transforms = array("proxy_id" => $COLLIDE_ID, "director_id" => $COLLIDE_ID, "pcoord_id" => $COLLIDE_ID, "evalrep_id" => $COLLIDE_ID, "studrep_id" => $COLLIDE_ID, "updated_by" => $COLLIDE_ID);
	merge_schema("entrada", $schema, $course_transforms);
	
	merge_tables("entrada", "course_files", array_merge($course_transforms, array("id" => COURSE_FILE_ID_DELTA)));
	merge_tables("entrada", "course_links", array_merge($course_transforms, array("id" => COURSE_LINK_ID_DELTA)));
	
	// Lookups
	move_table_array("primary", "entrada", array("events_lu_ed10", "events_lu_ed11", "events_lu_objectives", "events_lu_eventtypes", "quizzes_lu_questiontypes", "quizzes_lu_quiztypes", "curriculum_lu_types"));
	#merge_tables("primary", "events", array_merge($course_transforms));	
}

if($MERGE["support"] == true) {
	echo " ============   Merging support   ========== \n";
	# 	Constant to assign delta to	,				table,							field,		transform all, basic merge
	$schema = array(
		"NOTICE_ID_DELTA"						=> array("notices",			"notice_id",		true, true), // should be polymorphic but rehab has no records
		"PERMISSIONS_ID_DELTA"					=> array("permissions",		"permission_id",	true, false),
		"POLL_ANSWER_ID_DELTA"					=> array("poll_answers",	"answer_id",		true, true), 
		"POLL_QUESTION_ID_DELTA"				=> array("poll_questions",	"poll_id",			true, true), 
		"POLL_RESULT_ID_DELTA"					=> array("poll_results",	"result_id",		true, true), 
		"STATISTIC_ID_DELTA"					=> array("statistics",		"statistic_id",		true, false), 
	);
	
	$support_transforms = array("proxy_id" => $COLLIDE_ID, "updated_by" => $COLLIDE_ID); #"director_id" => $COLLIDE_ID, "pcoord_id" => $COLLIDE_ID, "evalrep_id" => $COLLIDE_ID, "studrep_id" => $COLLIDE_ID, 
	merge_schema("entrada", $schema, $support_transforms);
	
	merge_tables("entrada", "permissions", array_merge($support_transforms, array("assigned_by" => $COLLIDE_ID, "assigned_to" => $COLLIDE_ID)));
	
	// Support Tables
	move_table_array("primary", "entrada", array("filetypes", "global_lu_countries", "global_lu_disciplines", "global_lu_objectives", "global_lu_provinces", "global_lu_schools"));
}

$mtime = microtime();
$mtime = explode(" ",$mtime); 
$mtime = $mtime[1] + $mtime[0]; 
$endtime = $mtime; 
$totaltime = ($endtime - $starttime); 
echo "Finished colliding worlds in $totaltime seconds.\n"; 