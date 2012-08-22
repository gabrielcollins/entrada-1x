<?php

/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Cron job responsible for marking dead transactions and rolling back inventory for any associated items.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Brandon Thorn <brandon.thorn@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
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
require_once("library/Entrada/webconferencing/ConferenceFactory.inc.php");

$now = time();
$future = $now + 300;
$query = "  SELECT a.*,b.`software_model` FROM 
			`web_conferences` a 
			JOIN `conference_lu_software` b
			ON a.`csoftware_id` = b.`csoftware_id`
			WHERE `conference_start` < ".$db->qstr($future)."
			AND `conference_started` = '0'";
$results = $db->GetAll($query);
if ($results) {	
	foreach ($results as $result) {
		try{
			$c = ConferenceFactory::getConferenceModel($result['software_model']);
			$c->loadConference($result["wconference_id"]);
			if (!$c->isLIve()) {
				$c->createConferenceInstance();
				application_log("cron", "Action: Created Conference [". $result["wconference_id"]."] at ".date(DEFAULT_DATE_FORMAT,time()).". Specified start time: ".date(DEFAULT_DATE_FORMAT,$result["conference_start"]).".");
			} else {
				application_log("cron", "Action: Conference [". $result["wconference_id"]."] already created and is running..");
			}
		} catch (Exception $e) {
			application_log("cron", "Action: Error occured creating conference [". $result["wconference_id"]."]. Message is: ".$e->getMessage().".");
		}
	}
} else {
			application_log("cron", "Action: No conferences needing to be created.");
}
?>