<?php
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

$isAuthenticated = false;
$user_details = array();
if (isset($_POST["method"]) && $tmp_input = clean_input($_POST["method"], "alphanumeric")) {
	$method = $tmp_input;
}

if (isset($_POST["notice_id"]) && $tmp_input = clean_input($_POST["notice_id"], "alphanumeric")) {
	$notice_id = $tmp_input;
}

if (isset($_POST["total_notices"]) && $tmp_input = clean_input($_POST["total_notices"], "alphanumeric")) {
	$total_notices = $tmp_input;
}

if (isset($_POST["username"]) && isset($_POST["password"]) && !empty($_POST["username"]) && !empty($_POST["password"])) {
	require_once("Entrada/authentication/authentication.class.php");
	$username = clean_input($_POST["username"], "credentials");
	$password = clean_input($_POST["password"], "trim");


	$auth = new AuthSystem((((defined("AUTH_DEVELOPMENT")) && (AUTH_DEVELOPMENT != "")) ? AUTH_DEVELOPMENT : AUTH_PRODUCTION));
	$auth->setAppAuthentication(AUTH_APP_ID, AUTH_USERNAME, AUTH_PASSWORD);
	$auth->setEncryption(AUTH_ENCRYPTION_METHOD);
	$auth->setUserAuthentication($username, $password, AUTH_METHOD);
	$result = $auth->Authenticate(
		array(
			"id",
			"firstname",
			"lastname",
			"role",
			"group",
			"organisation_id",
			"private_hash"
		)
	);

	if ($ERROR == 0 && $result["STATUS"] == "success") {

		$GUEST_ERROR = false;

		if ($result["GROUP"] == "guest") {
			$query = "	SELECT COUNT(*) AS total
						FROM `community_members`
						WHERE `proxy_id` = ".$db->qstr($result["ID"])."
						AND `member_active` = 1";
			$community_result	= $db->GetRow($query);
			if ((!$community_result) || ($community_result["total"] == 0)) {
				/**
				* This guest user doesn't belong to any communities, don't let them log in.
				*/
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

			application_log("access", "User[".$username."] successfully logged in.");
			$isAuthenticated  = true;
			$user_details["authenticated"] = true;
			$user_details["id"] = $result["ID"];
			$user_details["firstname"] = $result["FIRSTNAME"];
			$user_details["lastname"] = $result["LASTNAME"];
			$user_details["role"] = $result["ROLE"];
			$user_details["group"] = $result["GROUP"];
			$user_details["organisation_id"] = $result["ORGANISATION_ID"];
			$user_details["private_hash"] = $result["PRIVATE_HASH"];
		}
	}

	unset($result, $username, $password);
} else {
	/**
 	 * Authenticate the user via their provided private hash.
	 */
	if (isset($_POST["hash"]) && $tmp_input = clean_input($_POST["hash"], "alphanumeric")) {
		$query = "SELECT a.`id`, a.`username`, a.`firstname`, a.`lastname`, a.`email`, a.`grad_year`, b.`role`, b.`group`, a.`organisation_id`, b.`access_expires`
				FROM `".AUTH_DATABASE."`.`user_data` AS a
				LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
				ON b.`user_id` = a.`id`
				WHERE b.`private_hash` = ".$db->qstr($tmp_input)."
				AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
				AND b.`account_active` = 'true'
				AND (b.`access_starts`='0' OR b.`access_starts` <= ".$db->qstr(time()).")
				AND (b.`access_expires`='0' OR b.`access_expires` >= ".$db->qstr(time()).")
				GROUP BY a.`id`";
		$result = $db->GetRow($query);
		if ($result) {
			$isAuthenticated = true;
			$user_details["id"] = $result["id"];
			$user_details["firstname"] = $result["firstname"];
			$user_details["lastname"] = $result["lastname"];
			$user_details["role"] = $result["role"];
			$user_details["group"] = $result["group"];
			$user_details["organisation_id"] = $result["organisation_id"];
		}
	}

}
if ($isAuthenticated) {
	
	$ENTRADA_USER = User::get($user_details["id"]);
	$user_details["access_id"] = $ENTRADA_USER->getAccessId();
	
	$ENTRADA_ACL = new Entrada_Acl($user_details);
	
	switch ($method) {
		case "hash" :
			echo $user_details["private_hash"];
			break;
		case "credentials" :
			echo true;
			break;
		case "agenda":
			$user_proxy_id = $user_details["id"];
			$user_role = $user_details["role"];
			$user_group = $user_details["group"];
			$user_organisation_id = $user_details["organisation_id"];

			$event_start = strtotime("-12 months 00:00:00");
			$event_finish = strtotime("+12 months 23:59:59");
			$learning_events = events_fetch_filtered_events(
				$user_proxy_id,
				$user_group,
				$user_role,
				$user_organisation_id,
				"date",
				"asc",
				"custom",
				$event_start,
				$event_finish,
				events_filters_defaults($user_proxy_id, $user_group, $user_role),
				true,
				1,
				1750);

			$events = array();
			if (!empty($learning_events["events"])) {
				foreach ($learning_events["events"] as $drid => $event) {
					$cal_type = 1;
					$cal_updated = "";

					if ($event["audience_type"] == "proxy_id") {
						$cal_type = 3;
					}

					if (((int) $event["last_visited"]) && ((int) $event["last_visited"] < (int) $event["updated_date"])) {
						$cal_type = 2;

						$cal_updated = date(DEFAULT_DATE_FORMAT, $event["updated_date"]);
					}

					$events[] = array(
								"id" => $event["event_id"],
								"start_date"	=> date("o-m-d G:i", $event["event_start"]),
								"end_date" => date("o-m-d G:i", $event["event_finish"]),
								"text" => strip_tags($event["event_title"]),
								"details" => $event["event_description"]. "<br /><b>Event Duration: </b>". $event["event_duration"] . " minutes <br /><b>Location: </b>". ($event["event_location"] == "" ? "To be announced" : $event["event_location"]) ."<br /><a href='https://meds.queensu.ca/central/events?id=".$event["event_id"]."' data-role='button' class='back' rel='external' target='_blank'>Review Learning Event</a>",
					);
				}
			}

			echo json_encode($events);
			break;
		case "notices" :
			$user_proxy_id = $user_details["id"];
			$user_role = $user_details["role"];
			$user_group = $user_details["group"];
			$user_organisation_id = $user_details["organisation_id"];
			switch ($user_group) {
				case "alumni" :
					$corrected_role = "students";
				break;
				case "faculty" :
					$corrected_role = "faculty";
				break;
				case "medtech" :
					$corrected_role = "medtech";
				break;
				case "resident" :
					$corrected_role = "resident";
				break;
				case "staff" :
					$corrected_role = "staff";
				break;
				case "student" :
				default :
					$cohort = groups_get_cohort($user_proxy_id);
					$corrected_role = "students";
				break;
			}
			$query = "	SELECT a.*, b.`statistic_id`, MAX(b.`timestamp`) AS `last_read`
						FROM `notices` AS a
						LEFT JOIN `statistics` AS b
						ON b.`module` = 'notices'
						AND b.`proxy_id` = ".$db->qstr($user_proxy_id)."
						AND b.`action` = 'read'
						AND b.`action_field` = 'notice_id'
						AND b.`action_value` = a.`notice_id`
						LEFT JOIN `notice_audience` AS c
						ON a.`notice_id` = c.`notice_id`
						WHERE (
							c.`audience_type` = 'all:users'
							".($corrected_role == "medtech" ? "OR c.`audience_type` LIKE '%all%' OR c.`audience_type` = 'cohorts'" : "OR c.`audience_type` = 'all:".$corrected_role."'")."
							OR
							((
								c.`audience_type` = 'students'
								OR c.`audience_type` = 'faculty'
								OR c.`audience_type` = 'staff')
								AND c.`audience_value` = ".$db->qstr($user_proxy_id)."
							)
							OR ((
								c.`audience_type` = 'cohorts'
								OR c.`audience_type` = 'course_list')
								AND c.`audience_value` IN (
									SELECT `group_id`
									FROM `group_members`
									WHERE `proxy_id` = ".$db->qstr($user_proxy_id).")
							)
						)
						AND (a.`organisation_id` IS NULL
						OR a.`organisation_id` = ".$db->qstr($user_organisation_id).")
						AND (a.`display_from`='0'
						OR a.`display_from` <= '".time()."')
						AND (a.`display_until`='0'
						OR a.`display_until` >= '".time()."')
						AND a.`organisation_id` = ".$db->qstr($user_organisation_id)."
						GROUP BY a.`notice_id`
						ORDER BY a.`updated_date` DESC, a.`display_until` ASC";
			$notices_to_display = array();
			$results = $db->GetAll($query);
			$previous_notices = array();
			
			if ($results) {
				$rows = 0;
				foreach ($results as $result) {
					if ((!$result["statistic_id"]) || ($result["last_read"] <= $result["updated_date"])) {
						$result['notice_status'] = 'new';
						$result["updated_date"] = date(DEFAULT_DATE_FORMAT, $result["updated_date"]);
						$notices_to_display[] = $result;
					} else {
						$result['notice_status'] = 'read';
						$result["updated_date"] = date(DEFAULT_DATE_FORMAT, $result["updated_date"]);
						$notices_to_display[] = $result;
					}
					$rows ++;
				}
				
				if ($rows != $total_notices) {
					echo json_encode($notices_to_display, JSON_FORCE_OBJECT);
				} else {
					echo 'false';
				}
			}
			break;
		case 'mark':
			add_statistic("notices", "read", "notice_id", $notice_id, $user_details['id']);
			echo $notice_id;
			break;
	}
}
?>