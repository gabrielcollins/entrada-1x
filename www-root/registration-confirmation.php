<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Entrada is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Entrada is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Entrada.  If not, see <http://www.gnu.org/licenses/>.
 *
 * A utility that allows local users to confirm their registration in the system.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
*/

@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/core",
    dirname(__FILE__) . "/core/includes",
    dirname(__FILE__) . "/core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

ob_start();

if(!function_exists("valid_address")) {
	function valid_address($address) {
		return ((eregi("^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$", $address)) ? true : false);
	}
}

if(!function_exists("get_hash")) {
	function get_hash() {
		global $db;

		do {
			$hash = md5(uniqid(rand(), 1));
		} while($db->GetRow("SELECT `id` FROM `".AUTH_DATABASE."`.`registration_confirmation` WHERE `hash` = ".$db->qstr($hash)));

		return $hash;
	}
}

$EMAIL_ADDRESS		= "";
$EMAIL_ADDRESSES	= array();
$STEP				= 1;
$ERROR				= 0;
$ERRORSTR			= array();
$NOTICE				= 0;
$NOTICESTR			= array();
$SUCCESS			= 0;
$SUCCESSSTR			= array();
/**
* Ensure that SSL is enabled on this page.
*/
if(!isset($_SERVER["HTTPS"])) {
	header("Location: ".str_replace("http://", "https://", ENTRADA_URL)."/".basename(__FILE__).(($query = replace_query()) ? "?".$query : ""));
	exit;
}

if((isset($_GET["hash"])) && (trim($_GET["hash"]) != "")) {
	$STEP = 3;
} elseif((isset($_GET["email_address"])) && (trim($_GET["email_address"]) != "")) {
	$STEP = 2;
} else {
	$STEP = 1;
}

// Error Checking Step
switch($STEP) {
	case 3 :
		if(($pieces = @explode(":", rawurldecode(trim($_GET["hash"])))) && (@is_array($pieces)) && (@count($pieces) == 2)) {
			$PROXY_ID	= (int) trim($pieces[0]);
			$HASH		= trim($pieces[1]);

			$query		= "SELECT * FROM `".AUTH_DATABASE."`.`registration_confirmation` WHERE `user_id` = ".$db->qstr($PROXY_ID, get_magic_quotes_gpc())." AND `hash` = ".$db->qstr($HASH, get_magic_quotes_gpc());
			$result 	= $db->GetRow($query);
			if($result) {
				if((int) $result["complete"]) {
					$ERROR++;
					$ERRORSTR[] = "<strong>Your account has already been activated.</strong><br /><br />".(defined('PASSWORD_RESET_URL') && PASSWORD_RESET_URL?"If you have forgotten your password, please <a href=\"".PASSWORD_RESET_URL."\" style=\"font-weight: bold\">click here</a> to start the reset your password.":"");
				} else {
					$query = "	SELECT * FROM `".AUTH_DATABASE."`.`user_access` WHERE `user_id` = ".$db->qstr($PROXY_ID)." AND `account_active` = 'false'";
					if($results = $db->GetAll($query)){
						foreach($results as $access_result){
							if(!$db->AutoExecute("`".AUTH_DATABASE."`.`user_access`",array("account_active"=>'true'),"UPDATE","`id` = ".$db->qstr($access_result["id"]))){
								$ERROR++;
								//$ERRORSTR[] = "<strong>Error activating account for application ".$access_result['app_id'].".</strong>");
							}
						}
					}
					if(!$ERROR){

						$db->AutoExecute( "`".AUTH_DATABASE."`.`registration_confirmation`",array("completed"=>1),"UPDATE","`id` = ".$db->qstr($result["id"]));
						$SUCCESS++;
						$SUCCESSSTR[] = "<strong>Your account is now active.</strong><br/><br/><a href=\"".ENTRADA_URL."\">Click here</a> to return to the ".APPLICATION_NAME." login screen where you can log into your account.";
					}
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "<strong>There is a problem with your hash code.</strong><br /><br />If you are trying to confirm your ".APPLICATION_NAME." account, copy and paste the entire link from the e-mail you have received into the browser location bar. Sometimes if you click the link from your e-mail client, it may not include the entire address.";
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "<strong>There is a problem with your hash code.</strong><br /><br />If you are trying to confirm your ".APPLICATION_NAME." account, copy and paste the entire link from the e-mail you have received into the browser location bar. Sometimes if you click the link from your e-mail client, it may not include the entire address.";
		}

		if($ERROR) {
			$STEP = 1;
		}
	break;
	case 2 :
		if((!isset($_GET["email_address"])) || (!$EMAIL_ADDRESS = clean_input($_GET["email_address"], array("lower", "nows", "notags"))) || (!valid_address($EMAIL_ADDRESS))) {
			$ERROR++;
			$ERRORSTR[] = "Please provide a valid e-mail address into the e-mail address field .";
		} else {
			$EMAIL_ADDRESSES[] = $EMAIL_ADDRESS;

			if($pieces = explode("@", $EMAIL_ADDRESS)) {
				$netid		= $pieces[0];
				$hostname	= $pieces[1];
				
				switch($hostname) {
					case "ucalgary.ca" :
						$EMAIL_ADDRESSES[] = $netid."@ucalgary.ca";
					break;
					case "med.ucalgary.ca" :
						$EMAIL_ADDRESSES[] = $netid."@med.ucalgary.ca";
					break;
					default :
						continue;
					break;
				}
			}

			$query	= "SELECT `id`, `username`, `email`, `firstname`, `lastname` FROM `".AUTH_DATABASE."`.`user_data` WHERE `email` IN ('".implode("', '", $EMAIL_ADDRESSES)."');";
			$result	= $db->GetRow($query);
			if($result) {
				$PROXY_ID		= (int) $result["id"];
				$USERNAME		= $result["username"];
				$FIRSTNAME		= $result["firstname"];
				$LASTNAME		= $result["lastname"];
				$EMAIL_ADDRESS	= $result["email"];
				$HASH			= get_hash();

				$processed				= array();
				$processed["ip"]		= $_SERVER["REMOTE_ADDR"];
				$processed["date"]		= time();
				$processed["user_id"]	= $PROXY_ID;
				$processed["hash"]		= $HASH;
				$processed["complete"]	= 0;

				if($db->AutoExecute("`".AUTH_DATABASE."`.`registration_confirmation`", $processed, "INSERT")) {
					$message  = "Hello ".$FIRSTNAME." ".$LASTNAME.",\n\n";
					$message .= "This is an automated e-mail containing instructions to help you login to your ".APPLICATION_NAME." account.\n\n";
					$message .= "Your ".APPLICATION_NAME." Username is: ".$USERNAME."\n\n";
					$message .= "Please visit the following link to confirm to your account:\n";
					$message .= str_replace("http://", "https://", ENTRADA_URL)."/".basename(__FILE__)."?hash=".rawurlencode($PROXY_ID.":".$HASH)."\n\n";
					$message .= "Please Note:\n";
					$message .= "This confirmation link will be valid for the next 3 days. If you do not confirm your\n";
					$message .= "account within this time period, you will need to reinitate this process.\n\n";
					$message .= "If you did not register an account in ".APPLICATION_NAME." and you believe\n";
					$message .= "there has been a mistake, DO NOT click the above link. Please forward this\n";
					$message .= "message along with a description of the problem to: ".$AGENT_CONTACTS["administrator"]["email"]."\n\n";
					$message .= "Best Regards,\n";
					$message .= $AGENT_CONTACTS["administrator"]["name"]."\n";
					$message .= $AGENT_CONTACTS["administrator"]["email"]."\n";
					$message .= ENTRADA_URL."\n\n";
					$message .= "Requested By:\t".$_SERVER["REMOTE_ADDR"]."\n";
					$message .= "Requested At:\t".date("r", time())."\n";

					if(@mail($EMAIL_ADDRESS, "Registration Confirmation - ".APPLICATION_NAME." Authentication System", $message, "From: \"".$AGENT_CONTACTS["administrator"]["name"]."\" <".$AGENT_CONTACTS["administrator"]["email"].">\nReply-To: \"".$AGENT_CONTACTS["administrator"]["name"]."\" <".$AGENT_CONTACTS["administrator"]["email"].">")) {
						$SUCCESS++;
						$SUCCESSSTR[] = "Hello <strong>".html_encode($FIRSTNAME." ".$LASTNAME).",</strong><br />A registration authorisation e-mail has just been sent to <strong>".html_encode($EMAIL_ADDRESS)."</strong>. This e-mail contains further instructions on confirming your account, so please check your e-mail in a few minutes.";

						application_log("notice", "A registration confirmation e-mail has just been sent for ".$USERNAME." [".$PROXY_ID."].");
					} else {
						$ERROR++;
						$ERRORSTR[] = "We were unable to send you your registration authorisation e-mail at this time due to an unrecoverable error. The administrator has been notified of this error and will investigate the issue shortly.<br /><br />Please try again later, we apologize for any inconvenience this may have caused.";

						application_log("error", "Unable to send registration confirmation notice as PHP's mail() function failed to initialize.");
					}

					$_SESSION = array();
					@session_destroy();
				} else {
					$ERROR++;
					$ERRORSTR[] =	"An error occurred while generating a registration authorisation e-mail at this time due to an unrecoverable error but your account has been created. "
									.(defined('REGISTRATION_CONFIRMATION_URL') && REGISTRATION_CONFIRMATION_URL?"Please <a href=\"".REGISTRATION_CONFIRMATION_URL."\">click here</a> to request a confirmation email and we will resend you one. "
									:"You can request a confirmation email be sent from the login page.")
									."An administrator has been made aware of this error and will resolve it as soon as possible.";
					application_log("error", "Unable to send registration confirmation notice because a registration_confirmation record could not be created. Database said: ".$db->ErrorMsg());					
				}
			} else {
				$ERROR++;
				$ERRORSTR[]	= "Your e-mail address (<strong>".htmlentities($EMAIL_ADDRESS)."</strong>) could not be found in our system. Please be sure that you have entered your registration e-mail address correctly <br /><br/> If you believe there is a problem, please contact us: <a href=\"mailto:".$AGENT_CONTACTS["administrator"]["email"]."\">".$AGENT_CONTACTS["administrator"]["email"]."</a>";

				application_log("notice", "Unable to locate an e-mail address [".$EMAIL_ADDRESS."] in the database to confirm account.");
			}
		}

		if($ERROR) {
			$STEP = 1;
		}
	break;
	case 1 :
	default :
		application_log("access", "Password reset page has been accessed.");
	break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo DEFAULT_CHARSET; ?>" />

	<title><?php echo APPLICATION_NAME; ?> Authentication System: Password Reset</title>

	<meta name="robots" content="noindex, nofollow" />

	<link href="<?php echo ENTRADA_RELATIVE; ?>/css/common.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" type="text/css" />

	<link href="<?php echo ENTRADA_RELATIVE; ?>/images/favicon.ico" rel="shortcut icon" type="image/x-icon" />
	<link href="<?php echo ENTRADA_RELATIVE; ?>/w3c/p3p.xml" rel="P3Pv1" type="text/xml" />
</head>
<body>
<table style="width: 950px" cellspacing="0" cellpadding="0" border="0">
	<tbody>
		<tr>
			<td style="width: 200px">
				&nbsp;
			</td>
			<td style="width: 750px; vertical-align: top; text-align: left; padding-left: 5px; padding-top: 5px; background-color: #FFFFFF">
				<div style="width: 750px">
					<h1><?php echo APPLICATION_NAME; ?> Authentication System</h1>
					<h2>Registration Confirmation</h2>
					<?php
					// Page Display Step
					switch($STEP) {
						case 3 :
							if($ERROR) {
								echo display_error();
							}
							if($NOTICE) {
								echo display_error();
							}
							if($SUCCESS) {
								echo display_success();
							}
						break;
						case 2 :
							if($ERROR) {
								echo display_error();
							}
							if($NOTICE) {
								echo display_error();
							}
							if($SUCCESS) {
								echo display_success();
							}
						break;
						case 1 :
						default :
							if($ERROR) {
								echo display_error();
							}
							if($NOTICE) {
								echo display_error();
							}
							if($SUCCESS) {
								echo display_success();
							}
							?>
							<div class="display-notice" style="padding: 10px">
								This page allows you to resend your registration confirmation if your account isn't already activated. To begin please enter your official e-mail address into the textbox below and click <strong>Continue</strong>. The system will search for your e-mail address, and send you further instructions.
							</div>
							<form action="<?php echo html_encode(basename(__FILE__)); ?>" method="get">
							<table style="width: 100%" cellspacing="1" cellpadding="1" border="0">
							<colgroup>
								<col style="width: 25%" />
								<col style="width: 75%" />
							</colgroup>
							<tbody>
								<tr>
									<td><label for="email_address" style="font-weight: bold">Registration E-Mail Address:</label></td>
									<td>
										<input type="text" id="email_address" style="width: 275px; vertical-align: middle" name="email_address" value="<?php echo ((isset($_POST["email_address"])) ? html_encode($_POST["email_address"]) : ""); ?>" />
										<input type="submit" class="button" value="Continue" style="vertical-align: middle; margin-left: 15px" />
									</td>
								</tr>
							</tbody>
							</table>
							</form>
							<?php
						break;
					}
					?>
				</div>
			</td>
		</tr>
	</tbody>
</table>
</body>
</html>