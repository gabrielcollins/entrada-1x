<?php
/**
 * MEdTech Central [Clerkship]
 * @author Unit: Medical Information Technology Unit
 * @author Director: Lewis Tomalty
 * @author Developer: Andrew Dos-Santos <andrew.dos-santos@queensu.ca>
 * @version 3.0
 * @copyright Copyright 2009 Queen's University, MEdTech Unit
 *
 * $Id: notification-electives-approval.php 368 2009-01-06 10:02:32Z simpson $
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
require_once("Entrada/phpmailer/class.phpmailer.php");

/**
 * NOTIFICATION CONFIGURATION OPTIONS
 */

$NOTIFICATION_MESSAGE		 	 = array();
$NOTIFICATION_MESSAGE["subject"] = "Clerkship Elecitves Awaiting Approval";

$NOTIFICATION_MESSAGE["textbody"] = file_get_contents(ENTRADA_ABSOLUTE."/templates/default/email/electives-approval-notification.txt");

$NOTIFICATION_REMINDERS = array();

/**
 * END OF NOTIFICATION CONFIGURATION OPTIONS
 */

$START_OF_TODAY		= strtotime("00:00:00");

// Setup PHPMailer to do the work.
$mail				= new PHPMailer();
$mail->PluginDir	= ENTRADA_ABSOLUTE."/includes/classes/phpmailer/";
$mail->SetLanguage("en", ENTRADA_ABSOLUTE."/includes/classes/phpmailer/language/");

$mail->IsSendmail();
$mail->Sendmail		= SENDMAIL_PATH;

$mail->Priority		= 3;
$mail->CharSet		= DEFAULT_CHARSET;
$mail->Encoding		= "8bit";
$mail->WordWrap		= "76";

$mail->From     	= $AGENT_CONTACTS["administrator"]["email"];
$mail->FromName		= $AGENT_CONTACTS["administrator"]["name"];

$mail->Sender		= $AGENT_CONTACTS["administrator"]["email"];
$needToSend 		= false;

$output = array();
$query		= "SELECT COUNT(event_id) as total FROM `".CLERKSHIP_DATABASE."`.`events` WHERE `event_type` = \"elective\" AND `event_status` = \"approval\"";

$result	= $db->GetRow($query);
if($result["total"] > 9) {
	$needToSend 	= true;
} else {
	$twoWeeksAgo = strtotime("-2 weeks");
	
	$query		= "SELECT * FROM `".CLERKSHIP_DATABASE."`.`events` WHERE `event_type` = \"elective\" AND `event_status` = \"approval\" AND `modified_last` < ".$db->qstr($twoWeeksAgo);
	
	if($result	= $db->GetRow($query)) {
		$needToSend 	= true;
	}
}

$mail->From     	= $AGENT_CONTACTS["administrator"]["email"];
$mail->FromName		= $AGENT_CONTACTS["administrator"]["name"];
	
$mail->Sender		= $AGENT_CONTACTS["administrator"]["email"];

$search		= array(
				"%TO_NAME%",
				"%EVENT_LINK%"
				);

$replace	= array(
				$AGENT_CONTACTS["agent-clerkship"]["name"],
				ENTRADA_URL."/admin/clerkship/electives"
				);

$mail->Subject	= $NOTIFICATION_MESSAGE["subject"];

$mail->Body	= str_replace($search, $replace, $NOTIFICATION_MESSAGE["textbody"]);

if($needToSend) {
	$email_address	= get_account_data("email", $AGENT_CONTACTS["agent-clerkship"]["director_ids"]["0"]);
	$name			= get_account_data("firstlast", $AGENT_CONTACTS["agent-clerkship"]["director_ids"]["0"]);
	$mail->AddAddress($email_address, $name);
	
	if($mail->Send()) {
		application_log("notification", "SUCCESS: Sent electives approval reminder to undergrad.");
	} else {
		application_log("notification", "FAILURE: Unable to send electives approval reminder to undergrad.");
	}
}

$mail->ClearAllRecipients();
$mail->ClearAttachments(); 
?>