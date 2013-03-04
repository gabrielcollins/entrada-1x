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
 * @author Developer: Ryan Warner <ryan.warner@queensu.ca>
 * @copyright Copyright 2012 Queen's Univerity. All Rights Reserved.
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

$search = array("%CONTACT_NAME%", "%APARTMENT_TITLE%", "%OCCUPANTS_AND_DATES%", "%REGIONALED_CONTACT%", "%REGIONALED_EMAIL%", "%APPLICATION_NAME%");
$date = time();
$months = Array ($date, $date+2629743, $date+(2629743*2), $date+(2629743*3));
$previous_contact = ((($apartments[0]["super_full"] != $apartments[0]["keys_full"]) & (!empty($apartments[0]["keys_full"]))) ? $apartments[0]["keys_full"] : $apartments[0]["super_full"] );
$previous_email = ((($apartments[0]["super_full"] != $apartments[0]["keys_full"]) & (!empty($apartments[0]["keys_full"]))) ? $apartments[0]["keys_email"] : $apartments[0]["super_email"] );
$msg_body = "";
$contact_id = 0;

$mail = new Zend_Mail();
$mail->addHeader("X-Section", "Regional Education Notification System", true);
$mail->setFrom($AGENT_CONTACTS["agent-regionaled"]["email"], $AGENT_CONTACTS["agent-regionaled"]["name"]);

// fetch the apartments
$query = "	SELECT `apartment_id`, 
					`apartment_title`, 
					CONCAT_WS(' ',super_firstname, super_lastname) AS `super_full`, 
					`super_email`, 
					CONCAT_WS(' ',keys_firstname,keys_lastname) AS `keys_full`, 
					`keys_email`,
					b.`region_name`
				FROM `".CLERKSHIP_DATABASE."`.`apartments`
				JOIN `".CLERKSHIP_DATABASE."`.`regions` AS b
				ON `apartments`.`region_id` = b.`region_id`
				WHERE `apartments`.`available_finish` >= ".$db->qstr(time())."
				OR `apartments`.`available_finish` = 0
				ORDER BY `apartments`.`region_id`, `keys_full`";
$apartments = $db->GetAll($query);

foreach ($apartments as $apartment) {

	$current_contact = ((($apartment["super_full"] != $apartment["keys_full"]) & (!empty($apartment["keys_full"]))) ? $apartment["keys_full"] : $apartment["super_full"] );
	$current_email = ((($apartment["super_full"] != $apartment["keys_full"]) & (!empty($apartment["keys_full"]))) ? $apartment["keys_email"] : $apartment["super_email"] );
	if ($previous_contact != $current_contact) {
		$messages[$contact_id]["contact_name"] = $previous_contact;
		$messages[$contact_id]["contact_email"] = $previous_email;
		$messages[$contact_id]["region"] = $previous_region;
		
		$msg_body .= $no_occupants;
		$messages[$contact_id]["body"] = $msg_body;
		
		$msg_body = "";
		$no_occupants = "";
		$contact_id ++;
	}
	
	// fetch the apartment occupants
	$query = "	SELECT CONCAT_WS(' ', b.firstname, b.lastname) as occupant_full, 
					b.`email` as occupant_email, 
					a.`occupant_title`, 
					FROM_UNIXTIME(a.inhabiting_start) AS inhabiting_start, 
					FROM_UNIXTIME(a.inhabiting_finish) AS inhabiting_finish 
				FROM `".CLERKSHIP_DATABASE."`.`apartment_schedule` as a 
				LEFT JOIN `".AUTH_DATABASE."`.`user_data` as b 
				ON a.`proxy_id` = b.`id` 
				WHERE a.`apartment_id` = ".$apartment['apartment_id']."
				AND MONTH(FROM_UNIXTIME(a.`inhabiting_finish`)) IN (MONTH(FROM_UNIXTIME('".implode("')), MONTH(FROM_UNIXTIME('", $months)."')))
				AND YEAR(FROM_UNIXTIME(a.`inhabiting_finish`)) IN (YEAR(FROM_UNIXTIME('".implode("')), YEAR(FROM_UNIXTIME('", $months)."')))
				ORDER BY a.`inhabiting_start` ASC;";
	
	$occupants = $db->GetAll($query);
	
	if ($occupants) {
		$msg_body .= "The following learners are scheduled to stay in <strong>".$apartment["apartment_title"]."</strong>.<br /><br />";
		$occupants_list = "<table width=\"100%\" cellpadding=\"0\" border=\"1\">\n";
		$occupants_list .= "\t\t<td width=\"40%\" valign=\"top\"><strong>Occupant Name</strong></td>\n";
		$occupants_list .= "\t\t<td width=\"30%\" valign=\"top\"><strong>Starting Date</strong></td>\n";
		$occupants_list .= "\t\t<td width=\"30%\" valign=\"top\"><strong>Ending Date</strong></td>\n";
		$occupants_list .= "\t</tr>\n";

		foreach ($occupants as $occupant) {
			if (!empty($occupant["occupant_title"])) {
				$occupant_name = $occupant["occupant_title"];
			} else {
				$occupant_name = $occupant["occupant_full"];
			}
			$occupants_list .= "\t<tr>\n";
			$occupants_list .= "\t\t<td>".$occupant_name."</td>\n";
			$occupants_list .= "\t\t<td>".date("l, F j, Y",  strtotime($occupant["inhabiting_start"]))."</td>\n";
			$occupants_list .= "\t\t<td>".date("l, F j, Y",  strtotime($occupant["inhabiting_finish"]))."</td>\n";
			$occupants_list .= "\t</tr>\n";
		}
		$occupants_list .= "</table><br />\n";

		$msg_body .= $occupants_list;
	} else {
		$no_occupants .= "There are currently no learners are scheduled to stay in <strong>".$apartment["apartment_title"]."</strong>.<br /><br />";
	}
	

	
	$previous_contact = $current_contact;
	$previous_email = $current_email;
	$previous_region = $apartment["region_name"];
}

$contact_id ++;
$messages[$contact_id]["contact_name"] =  $previous_contact;
$messages[$contact_id]["contact_email"] =  $previous_email;
$messages[$contact_id]["region"] =  $previous_region;
$messages[$contact_id]["body"] = $msg_body;
		
foreach ($messages as $message_id => $message) {
	if (!isset($occupants_email)) {
		$occupants_email = nl2br(file_get_contents(ENTRADA_ABSOLUTE . "/templates/" . $ENTRADA_ACTIVE_TEMPLATE . "/email/regionaled-apartment-occupants-monthly-notification.txt"));
	}
	$replace = array($message["contact_name"], $message["region"], $message["body"], $AGENT_CONTACTS["agent-regionaled"]["name"], $AGENT_CONTACTS["agent-regionaled"]["email"], "Entrada");
	$mail->clearSubject();
	$mail->setSubject("Queen's Monthly Accomodation Schedule for {$message['region']}");
	$mail->setBodyHtml(str_replace($search, $replace, $occupants_email));
	$mail->clearRecipients();
	$mail->addTo($message["contact_email"], $message["contact_name"]);
	$mail->addCc("regional@queensu.ca", "Regional Education Office");
	$mail->send();
}
?>
