<?php
/**
 * Online Course Resources [Pre-Clerkship]
 * @author Unit: Medical Education Technology Unit
 * @author Director: Dr. Benjamin Chen <bhc@post.queensu.ca>
 * @author Developer: Matt Simpson <simpson@post.queensu.ca>
 * @version 3.0
 * @copyright Copyright 2006 Queen's University, MEdTech Unit
 *
 * $Id: community-quota.php 1103 2010-04-05 15:20:37Z simpson $
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

$today = mktime(0, 0, 0, date("m"), date("d"), date("y"));

$query = "	SELECT * FROM `course_audience` WHERE `enroll_finish` < ".$db->qstr($today)." AND `enroll_finish` != 0 AND `audience_active` = 1";
$results = $db->GetAll($query);

if ($results) {
	foreach($results as $result){
		$query = " UPDATE `course_audience` SET `audience_active` = 0 WHERE `caudience_id` = ".$db->qstr($result["caudience_id"]);
		if(!$db->Execute($query)){
			echo "Unable to de-activate id: ".$result["caudience_id"];
		}
	}
}

?>