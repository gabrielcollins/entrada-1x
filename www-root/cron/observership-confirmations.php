<?php

/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Cron job responsible for emailing observership preceptors.
 *
 * Setup to run daily in CRON.
 *
 * @author Organisation: Queen's University
 * @author Unit: Faculty of Medicine
 * @author Developer: Ryan Warner <ryan.warner@queensu.ca>
 * @copyright Copyright 2012 Queen's Univerity. All Rights Reserved.
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
require_once(ENTRADA_CORE."/library/Models/utility/Editable.interface.php");
require_once(ENTRADA_CORE."/library/Models/mspr/Observership.class.php");

/*
 * Fetch the unconfirmed observerships whose preceptors have not been notified. 
 */
$query = "	SELECT *
			FROM `student_observerships` 
			WHERE ((FROM_UNIXTIME(`start`) <= NOW() AND FROM_UNIXTIME(`end`) <= NOW()) OR (FROM_UNIXTIME(`start`) <= NOW() AND `end` IS NULL))
				AND (`notice_sent` = '0' OR DATEDIFF(NOW(), FROM_UNIXTIME(`notice_sent`)) >= '7')
				AND `status` = 'UNCONFIRMED'";

$results = $db->GetAll($query);

if ($results) {
	foreach ($results as $result) {
		/*
		 * Create the observership object, send the notification, and update it with the new time.
		 */
		$obs = Observership::fromArray($result);

		$result["notice_sent"] = time();

		if (sendNotification($obs)) {
			$obs->update($result);
		};
	}
}

function sendNotification($obs) {
	global $AGENT_CONTACTS;
	if ($obs instanceof Observership) {
		
		$observer = User::get($obs->getStudentID());
		
		if (($preceptor = $obs->getPreceptor()) && $preceptor instanceof User) {
			$preceptor_email = $preceptor->getEmail();
			$preceptor_name = ($preceptor->getPrefix() ? $preceptor->getPrefix()." " : "") . $preceptor->getFirstname() . " " . $preceptor->getLastname();
		} else {
			$preceptor_email = $obs->getPreceptorEmail();
			$preceptor_name = ($obs->getPreceptorPrefix() ? $obs->getPreceptorPrefix()." " : "") . $obs->getPreceptorFirstname() . " " . $obs->getPreceptorLastname();
		}

		if ($preceptor_email) {
		
			$message[] = $preceptor_name.",<br /><br />";
			$message[] = "This automated message is being sent to confirm the following observership:<br /><br />\n";
			$message[] = "<table width=\"60%\" border=\"1\">\n";

			$message[] = "\t<tr>\n";
			$message[] = "\t\t<td>Observer:</td>\n";
			$message[] = "\t\t<td>".$obs->getUser()->getFullname(false)."</td>\n";
			$message[] = "\t</tr>\n";

			$message[] = "\t<tr>\n";
			$message[] = "\t\t<td>Title</td>\n";
			$message[] = "\t\t<td>".$obs->getTitle()."</td>\n";
			$message[] = "\t</tr>\n";

			$message[] = "\t<tr>\n";
			$message[] = "\t\t<td>Site</td>\n";
			$message[] = "\t\t<td>".$obs->getSite()."</td>\n";
			$message[] = "\t</tr>\n";

			$message[] = "\t<tr>\n";
			$message[] = "\t\t<td>Location</td>\n";
			$message[] = "\t\t<td>".$obs->getLocation()."</td>\n";
			$message[] = "\t</tr>\n";

			$message[] = "\t<tr>\n";
			$message[] = "\t\t<td>Period</td>\n";
			$message[] = "\t\t<td>".$obs->getPeriod()."</td>\n";
			$message[] = "\t</tr>\n";

			$message[] = "</table><br />\n";
			$message[] = "Please confirm or reject the observership with this link:<br /><br />\n";
			$message[] = "<a href=\"" . ENTRADA_URL . "/confirm_observership?unique_id=" . $obs->getUniqueID() . "\">" . ENTRADA_URL . "/confirm_observership?unique_id=" . $obs->getUniqueID() ."</a>.<br /><br />\n";
			$message[] = "A reminder notice will be sent in 7 days.<br /><br />\n";
			$message[] = "Thank you,";

			$mail = new Zend_Mail();
			$mail->addHeader("X-Section", "Observership Confirmation", true);
			$mail->setFrom($AGENT_CONTACTS["general-contact"]["email"], $AGENT_CONTACTS["general-contact"]["name"]);
			$mail->setSubject("Observership Confirmation");
			$mail->setBodyHtml(implode($message));
			$mail->addTo($preceptor_email, $preceptor_name);

			if ($mail->send()) {
				return true;
			} else {
				add_error("Failed to send confirmation request.");
				application_log("error", "Unable to send observership [observership_id: ".$obs->getID()."] confirmation request.");
				return false;
			}
		
		}
		
	} else {
		application_log("error", "Non-observership object passed to sendNotification function, unable to send observership confirmation request.");
		return false;
	}
}

?>