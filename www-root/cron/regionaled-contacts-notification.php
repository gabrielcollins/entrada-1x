<?php

/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Cron job responsible for emailing apartment contacts once a month with the
 * scheduled occupants for the month.
 * 
 * Setup to run the first of each month in CRON.
 *
 * @author Organisation: Queen's University
 * @author Unit: Faculty of Medicine
 * @author Developer: Brandon Thorn <brandon.thorn@queensu.ca>
 * @copyright Copyright 2011 Queen's Univerity. All Rights Reserved.
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

global $ENTRADA_ACTIVE_TEMPLATE;

$search = array("%CONTACT_NAME%", "%APARTMENT_TITLE%", "%OCCUPANTS_AND_DATES%", "%REGIONALED_CONTACT%", "%APPLICATION_NAME%");


$mail = new Zend_Mail();
$mail->addHeader("X-Section", "Regional Education Notification System", true);
$mail->setFrom($AGENT_CONTACTS["agent-regionaled"]["email"], $AGENT_CONTACTS["agent-regionaled"]["name"]);


$query = "	SELECT `apartment_id`, 
					`apartment_title`, 
					CONCAT_WS(' ',super_firstname, super_lastname) AS `super_full`, 
					`super_email`, 
					CONCAT_WS(' ',keys_firstname,keys_lastname) AS `keys_full`, 
					`keys_email` 
				FROM `".CLERKSHIP_DATABASE."`.`apartments`";


$apartments = $db->GetAll($query);

foreach ($apartments as $apartment) {
	$mail->clearSubject();
	$mail->setSubject("Monthly Apartment Notification for Apartment '{$apartment['apartment_title']}'");
	$query = "	SELECT CONCAT_WS(' ', b.firstname, b.lastname) as occupant_full, 
					b.`email` as occupant_email, 
					a.`occupant_title`, 
					FROM_UNIXTIME(a.inhabiting_start) AS inhabiting_start, 
					FROM_UNIXTIME(a.inhabiting_finish) AS inhabiting_finish 
				FROM `".CLERKSHIP_DATABASE."`.`apartment_schedule` as a 
				LEFT JOIN `".AUTH_DATABASE."`.`user_data` as b 
				ON a.`proxy_id` = b.`id` 
				WHERE a.`apartment_id` = ".$apartment['apartment_id']."
				AND MONTH(FROM_UNIXTIME(a.`inhabiting_start`)) = MONTH(NOW());";

	$occupants = $db->GetAll($query);

	if ($occupants) {

		$occupants_list = "";
		foreach ($occupants as $occupant) {
			if ($occupant["occupant_title"] != null && $occupant["occupant_title"] != "") {
				$occupant_name = $occupant["occupant_title"];
			} else {
				$occupant_name = $occupant["occupant_full"];
			}

			$occupants_list .= $occupant_name." will be staying from ".date("l F j Y @ g:i A",  strtotime($occupant["inhabiting_start"]))." to ".date("l F j Y @ g:i A",  strtotime($occupant["inhabiting_finish"])).".\n";
		}
		if (!isset($occupants_email)) {
			$occupants_email = file_get_contents(ENTRADA_ABSOLUTE . "/templates/" . $ENTRADA_ACTIVE_TEMPLATE . "/email/regionaled-apartment-occupants-monthly-notification.txt");
		}
		
		//send to super
		$replace = array($apartment["super_full"], $apartment["apartment_title"], $occupants_list, $AGENT_CONTACTS["agent-regionaled"]["name"], "Entrada");
		$mail->setBodyText(str_replace($search, $replace, $occupants_email));
		$mail->clearRecipients();
		$mail->addTo($apartment["super_email"], $apartment["super_full"]);
		$mail->send();
		
		//send to agent contact
		$replace = array($AGENT_CONTACTS["agent-regionaled"]["name"], $apartment["apartment_title"], $occupants_list, $AGENT_CONTACTS["agent-regionaled"]["name"], "Entrada");
		$mail->setBodyText(str_replace($search, $replace, $occupants_email));
		$mail->clearRecipients();
		$mail->addTo($AGENT_CONTACTS["agent-regionaled"]["email"],$AGENT_CONTACTS["agent-regionaled"]["name"]);
		$mail->send();
				
		//if key contact differs from super, send to key contact
		if ($apartment["keys_full"] != $apartment["super_full"]) {
			$replace = array($apartment["keys_full"], $apartment["apartment_title"], $occupants_list, $AGENT_CONTACTS["agent-regionaled"]["name"], "Entrada");
			$mail->setBodyText(str_replace($search, $replace, $occupants_email));
			$mail->clearRecipients();
			$mail->addTo($apartment["keys_email"], $apartment["keys_full"]);
			$mail->send();

		}
	} else {
		if (!isset($no_occupants_email)) {
			$no_occupants_email = file_get_contents(ENTRADA_ABSOLUTE . "/templates/" . $ENTRADA_ACTIVE_TEMPLATE . "/email/regionaled-apartment-no-occupants-monthly-notification.txt");
		}

		$replace = array($apartment["super_full"], $apartment["apartment_title"], "", $AGENT_CONTACTS["agent-regionaled"]["name"], "Entrada");
		//send to super
		$mail->setBodyText(str_replace($search, $replace, $no_occupants_email));
		$mail->clearRecipients();
		$mail->addTo($apartment["super_email"], $apartment["super_full"]);
		$mail->send();
		
		//send to agent contact
		$replace = array($AGENT_CONTACTS["agent-regionaled"]["name"], $apartment["apartment_title"], "", $AGENT_CONTACTS["agent-regionaled"]["name"], "Entrada");
		$mail->setBodyText(str_replace($search, $replace, $no_occupants_email));
		$mail->clearRecipients();
		$mail->addTo($AGENT_CONTACTS["agent-regionaled"]["email"], $AGENT_CONTACTS["agent-regionaled"]["name"]);
		$mail->send();
		
		//if super differs from key contact, send to key contact as well
		if ($apartment["keys_full"] != $apartment["super_full"]) {
			$replace = array($apartment["keys_full"], $apartment["apartment_title"], "", $AGENT_CONTACTS["agent-regionaled"]["name"], "Entrada");
			$mail->setBodyText(str_replace($search, $replace, $no_occupants_email));
			$mail->clearRecipients();
			$mail->addTo($apartment["keys_email"], $apartment["keys_full"]);
			$mail->send();
		}
	}
}
?>
