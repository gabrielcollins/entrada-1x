<?php
/**
 * Online Course Resources [Pre-Clerkship]
 * @author Unit: Medical Education Technology Unit
 * @author Director: Dr. Benjamin Chen <bhc@post.queensu.ca>
 * @author Developer: Matt Simpson <simpson@post.queensu.ca>
 * @version 3.0
 * @copyright Copyright 2006 Queen's University, MEdTech Unit
 *
 * $Id: community-activities.php 1103 2010-04-05 15:20:37Z simpson $
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

/**
 * This is sort of a large query, so we are going to run it in CRON
 * every hour and cache the results so that it is faster for the users
 * on the Communities page. If you change that query *AT ALL* (including simple
 * spacing changes, change this and vise-versa! Don't forget!
 * 
 * I can't figure out why this query is so slow...
 * 
 */
				$query		= "
							SELECT a.`community_id`, a.`community_url`, a.`community_title`,
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
$db->CacheGetAll(10, $query);
?>