<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Cron job responsible for adding users to the google mail-list.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2009 Queen's University. All Rights Reserved.
 *
*/
@set_time_limit(0);
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

$query		= "
			SELECT a.`community_id`,
			COUNT(DISTINCT(c.`proxy_id`)) AS `total_members`,
			COUNT(DISTINCT(b.`chistory_id`)) AS `history_records`,
			((COUNT(a.`community_id`)) * (COUNT(DISTINCT(b.`chistory_id`)) / COUNT(DISTINCT(c.`proxy_id`)))) AS `activity_rating`
			FROM `communities` AS a
			LEFT JOIN `community_history` AS b
			ON a.`community_id` = b.`community_id`
			LEFT JOIN `community_members` AS c
			ON a.`community_id` = c.`community_id`
			WHERE a.`community_active` = '1'
			AND b.`history_timestamp` >= '".strtotime("-60 days", strtotime("00:00:00"))."'
			AND c.`member_active` = '1'
			GROUP BY a.`community_id`
			ORDER BY `activity_rating` DESC
			LIMIT 0, 10";
$results = $db->GetAll($query);
if($results) {
	$db->Execute("DELETE FROM `communities_most_active`");
	$count = 0;
	foreach ($results as $result) {
		if ($result["community_id"]) {
			$db->AutoExecute("communities_most_active", array("community_id" => $result["community_id"], "activity_order" => $count), "INSERT");
			$count++;
		}
	}
}
	
?>