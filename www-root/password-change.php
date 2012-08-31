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
 * A utility that allows local users to change their Entrada password.
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

/**
 * Ensure that SSL is enabled on this page.
 */
if(!isset($_SERVER["HTTPS"])) {
	header("Location: ".str_replace("http://", "https://", ENTRADA_URL)."/".basename(__FILE__).(($query = replace_query()) ? "?".$query : ""));
	exit;
}

/**
 * If there is a valid step, set it.
 */
if((isset($_POST["step"])) && ((int) trim($_POST["step"]))) {
	$STEP = (int) trim($_POST["step"]);
}

// Error Checking Step
switch($STEP) {
	case 2 :
		if((isset($_POST["username"])) && ($USERNAME = clean_input($_POST["username"], "credentials")) && (isset($_POST["password"])) && ($PASSWORD = clean_input($_POST["password"], "trim"))) {
			$query		= "SELECT * FROM `".AUTH_DATABASE."`.`user_data` WHERE `username` = ".$db->qstr($USERNAME, get_magic_quotes_gpc())." AND `password` = ".$db->qstr(md5($PASSWORD));
			$result		= $db->GetRow($query);
			if($result) {
				$PROXY_ID		= $result["id"];
				$FIRSTNAME		= $result["firstname"];
				$LASTNAME		= $result["lastname"];
				$USERNAME		= $result["username"];
				$EMAIL_ADDRESS	= $result["email"];

				if((!isset($_POST["npassword1"])) || (trim($_POST["npassword1"]) == "")) {
					$ERROR++;
					$ERRORSTR[] = "Please be sure to enter the new password for your account.";
				} else {
					if((!isset($_POST["npassword2"])) || (trim($_POST["npassword2"]) == "")) {
						$ERROR++;
						$ERRORSTR[] = "Please be sure to re-enter the new password for your account.";
					} else {
						$PASSWORD  = clean_input($_POST["npassword1"], "trim");
						$PASSWORD2 = clean_input($_POST["npassword2"], "trim");

						if($PASSWORD != $PASSWORD2) {
							$ERROR++;
							$ERRORSTR[] = "Your new passwords do not match, please re-enter your new password.";
						} else {
							if((strlen($PASSWORD) < 6) || (strlen($PASSWORD) > 24)) {
								$ERROR++;
								$ERRORSTR[] = "Your new password must be between 6 and 24 characters in length.";
							} else {
								$query = "UPDATE `".AUTH_DATABASE."`.`user_data` SET `password` = ".$db->qstr(md5($PASSWORD))." WHERE `id` = ".$db->qstr($PROXY_ID)." AND `username` = ".$db->qstr($USERNAME);
								if($db->Execute($query)) {
									$message  = "Hello ".$FIRSTNAME." ".$LASTNAME.",\n\n";
									$message .= "Your ".APPLICATION_NAME." Username is: ".$USERNAME."\n\n";
									$message .= "This is an automated e-mail to inform you that your ".APPLICATION_NAME." password\n";
									$message .= "has been successfully changed. No further action is needed, this message\n";
									$message .= "is for your information only.\n\n";
									$message .= "If you did not change the password for this account and you believe there\n";
									$message .= "has been a mistake, please forward this message along with a description of\n";
									$message .= "the problem to: ".$AGENT_CONTACTS["administrator"]["email"]."\n\n";
									$message .= "Best Regards,\n";
									$message .= $AGENT_CONTACTS["administrator"]["name"]."\n";
									$message .= $AGENT_CONTACTS["administrator"]["email"]."\n";
									$message .= ENTRADA_URL."\n\n";
									$message .= "Requested By:\t".$_SERVER["REMOTE_ADDR"]."\n";
									$message .= "Requested At:\t".date("r", time())."\n";

									@mail($EMAIL_ADDRESS, "Password Change Outcome - ".APPLICATION_NAME." Authentication System", $message, "From: \"".$AGENT_CONTACTS["administrator"]["name"]."\" <".$AGENT_CONTACTS["administrator"]["email"].">\nReply-To: \"".$AGENT_CONTACTS["administrator"]["name"]."\" <".$AGENT_CONTACTS["administrator"]["email"].">");

									$_SESSION = array();
									@session_destroy();

									$SUCCESS++;
									$SUCCESSSTR[] = "<strong>Your ".APPLICATION_NAME." password has been changed.</strong><br /><br />A notification e-mail with the result of this process has also been sent to <a href=\"mailto:".html_encode($EMAIL_ADDRESS)."\">".html_encode($EMAIL_ADDRESS)."</a>.";

									application_log("success", $USERNAME." [".$PROXY_ID."] changed their password.");
								} else {
									$ERROR++;
									$ERRORSTR[] = "We were unable to complete your password change request at this time, please try again later.<br /><br />The administrator has been informed of this error and will investigate promptly.";

									application_log("error", "Unable to change the password because of an update failure. Database said: ".$db->ErrorMsg());
								}
							}
						}
					}
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "The username / password combination that you have provided is incorrect; please try again.<br /><br />If you do not know your current password, please use the <a href=\"".PASSWORD_RESET_URL."\" style=\"font-weight: bold\">password reset tool</a>.";

				application_log("error", "Invalid username / password combination on password change page. Database said: ".$db->ErrorMsg());
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "Please enter your current ".APPLICATION_NAME." Username and Password into the form below.<br /><br />If you do not know your current password, please use the <a href=\"".PASSWORD_RESET_URL."\" style=\"font-weight: bold\">password reset tool</a>.";
		}

		if($ERROR) {
			$STEP = 1;
		}
	break;
	case 1 :
	default :
		application_log("access", "Password change page accessed.");
	break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo DEFAULT_CHARSET; ?>" />

	<title><?php echo APPLICATION_NAME; ?> Authentication System: Password Reset System</title>

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
					<h2>Password Change System</h2>
					<?php
					// Page Display Step
					switch($STEP) {
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
								This page allows you to change your <?php echo APPLICATION_NAME; ?> password. To begin please enter your <?php echo APPLICATION_NAME; ?> username, current password and desired password into the form below then click Change.
							</div>
							<form action="<?php echo html_encode(basename(__FILE__)); ?>" method="post">
							<input type="hidden" name="step" value="2" />
							<table style="width: 100%" cellspacing="1" cellpadding="1" border="0">
							<colgroup>
								<col style="width: 25%" />
								<col style="width: 75%" />
							</colgroup>
							<tfoot>
								<tr>
									<td colspan="2" style="padding-top: 15px; text-align: right">
										<input type="button" value="Cancel" class="button" onclick="window.location='<?php echo ENTRADA_URL; ?>'" />
										<input type="submit" class="button" value="Change" />
									</td>
								</tr>
							</tfoot>
							<tbody>
								<tr>
									<td><label for="username" class="form-required"><?php echo APPLICATION_NAME; ?> Username:</label></td>
									<td>
										<input type="text" id="username" name="username" value="<?php echo ((isset($_POST["username"])) ? html_encode(trim($_POST["username"])) : ""); ?>" style="width: 200px" maxlength="32" />
									</td>
								</tr>
								<tr>
									<td style="padding-bottom: 15px"><label for="password" class="form-required">Current Password:</label></td>
									<td style="padding-bottom: 15px">
										<input type="password" id="password" name="password" value="" style="width: 200px" maxlength="24" />
										<span class="content-small" style="padding-left: 5px">Don't know your current password? <a href="<?php echo ENTRADA_URL; ?>/password-reset.php" style="font-size: 10px">Click here</a></span>
									</td>
								</tr>
								<tr>
									<td><label for="npassword1" class="form-required">Enter New Password:</label></td>
									<td>
										<input type="password" id="npassword1" name="npassword1" value="" style="width: 200px" maxlength="24" />
										<span class="content-small" style="padding-left: 5px">Password must be 6 - 24 alphanumeric characters.</span>
									</td>
								</tr>
								<tr>
									<td><label for="npassword2" class="form-required">Re-enter New Password:</label></td>
									<td><input type="password" id="npassword2" name="npassword2" value="" style="width: 200px" maxlength="24" /></td>
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
</body>
</html>