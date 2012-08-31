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
 * A utility that allows local users to reset their Entrada password if they
 * have forgotten it.
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
		} while($db->GetRow("SELECT `id` FROM `".AUTH_DATABASE."`.`password_reset` WHERE `hash` = ".$db->qstr($hash)));

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
	if((isset($_POST["step"])) && ((int) trim($_POST["step"]) == 4)) {
		$STEP = 4;
	} else {
		$STEP = 3;
	}
} elseif((isset($_GET["email_address"])) && (trim($_GET["email_address"]) != "")) {
	$STEP = 2;
} else {
	$STEP = 1;
}

// Error Checking Step
switch($STEP) {
	case 4 :
		if(($pieces = @explode(":", rawurldecode(trim($_GET["hash"])))) && (@is_array($pieces)) && (@count($pieces) == 2)) {
			$PROXY_ID	= (int) trim($pieces[0]);
			$HASH		= trim($pieces[1]);

			$query		= "SELECT * FROM `".AUTH_DATABASE."`.`password_reset` WHERE `user_id` = ".$db->qstr($PROXY_ID, get_magic_quotes_gpc())." AND `hash` = ".$db->qstr($HASH, get_magic_quotes_gpc());
			$result 	= $db->GetRow($query);
			if($result) {
				if(!(int) $result["complete"]) {
					$query		= "SELECT `username`, `firstname`, `lastname`, `email` FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($PROXY_ID, get_magic_quotes_gpc());
					$result		= $db->GetRow($query);
					if($result) {
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
										$query = "UPDATE `".AUTH_DATABASE."`.`user_data` SET `password` = '".md5($PASSWORD)."' WHERE `id` = ".$db->qstr($PROXY_ID, get_magic_quotes_gpc())." AND `username` = ".$db->qstr($USERNAME, get_magic_quotes_gpc());
										if($db->Execute($query)) {
											$query = "UPDATE `".AUTH_DATABASE."`.`password_reset` SET `complete` = '1' WHERE `user_id` = ".$db->qstr($PROXY_ID, get_magic_quotes_gpc())." AND `hash` = ".$db->qstr($HASH, get_magic_quotes_gpc());
											if(!$db->Execute($query)) {
												application_log("error", "Unable to set the password complete status to 1. Database said: ".$db->ErrorMsg());
											}

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

											@mail($EMAIL_ADDRESS, "Password Reset Outcome - ".APPLICATION_NAME." Authentication System", $message, "From: \"".$AGENT_CONTACTS["administrator"]["name"]."\" <".$AGENT_CONTACTS["administrator"]["email"].">\nReply-To: \"".$AGENT_CONTACTS["administrator"]["name"]."\" <".$AGENT_CONTACTS["administrator"]["email"].">");

											$_SESSION = array();
											@session_destroy();

											$SUCCESS++;
											$SUCCESSSTR[] = "<strong>Your ".APPLICATION_NAME." password has been reset.</strong><br /><br />A notification e-mail with the result of this process has also been sent to <a href=\"mailto:".html_encode($EMAIL_ADDRESS)."\">".html_encode($EMAIL_ADDRESS)."</a>.";

											application_log("success", $USERNAME." [".$PROXY_ID."] reset their password.");
										} else {
											$ERROR++;
											$ERRORSTR[] = "We were unable to complete your password reset request at this time, please try again later.<br /><br />The administrator has been informed of this error and will investigate promptly.";

											application_log("error", "Unable to reset the password because of an update failure. Database said: ".$db->ErrorMsg());
										}
									}
								}
							}
						}
					} else {
						$ERROR++;
						$ERRORSTR[] = "There was a problem with this password reset entry. Please start the process again: <a href=\"".html_encode(basename(__FILE__))."\" style=\"font-weight: bold\">click here</a> to start again.";

						application_log("error", "There is a problem with a password reset entry. Hash: ".trim($_GET["hash"]));
					}
				} else {
					$ERROR++;
					$ERRORSTR[] = "<strong>Your password has already been reset.</strong><br /><br />If you have forgotten your password again, please <a href=\"".html_encode(basename(__FILE__))."\" style=\"font-weight: bold\">click here</a> to start the password reset process again.";

					application_log("error", "Password has already been reset, but is hitting step 4 still. Hash: ".trim($_GET["hash"]));
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "<strong>There is a problem with your hash code.</strong><br /><br />If you are trying to reset your ".APPLICATION_NAME." password, copy and paste the entire link from the e-mail you have received into the browser location bar. Sometimes if you click the link from your e-mail client, it may not include the entire address.";

				application_log("error", "A bad hash code is hitting step 4. Hash: ".trim($_GET["hash"]));
			}
		} else {
			$ERROR++;
			$ERRORSTR[]	= "<strong>There is a problem with your hash code.</strong><br /><br />If you are trying to reset your ".APPLICATION_NAME." password, copy and paste the entire link from the e-mail you have received into the browser location bar. Sometimes if you click the link from your e-mail client, it may not include the entire address.";

			application_log("error", "A bad hash code is hitting step 4. Hash: ".trim($_GET["hash"]));
		}
	break;
	case 3 :
		if(($pieces = @explode(":", rawurldecode(trim($_GET["hash"])))) && (@is_array($pieces)) && (@count($pieces) == 2)) {
			$PROXY_ID	= (int) trim($pieces[0]);
			$HASH		= trim($pieces[1]);

			$query		= "SELECT * FROM `".AUTH_DATABASE."`.`password_reset` WHERE `user_id` = ".$db->qstr($PROXY_ID, get_magic_quotes_gpc())." AND `hash` = ".$db->qstr($HASH, get_magic_quotes_gpc());
			$result 	= $db->GetRow($query);
			if($result) {
				if((int) $result["complete"]) {
					$ERROR++;
					$ERRORSTR[] = "<strong>Your password has already been reset.</strong><br /><br />If you have forgotten your password again, please <a href=\"".html_encode(basename(__FILE__))."\" style=\"font-weight: bold\">click here</a> to start the password reset process again.";
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "<strong>There is a problem with your hash code.</strong><br /><br />If you are trying to reset your ".APPLICATION_NAME." password, copy and paste the entire link from the e-mail you have received into the browser location bar. Sometimes if you click the link from your e-mail client, it may not include the entire address.";
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "<strong>There is a problem with your hash code.</strong><br /><br />If you are trying to reset your ".APPLICATION_NAME." password, copy and paste the entire link from the e-mail you have received into the browser location bar. Sometimes if you click the link from your e-mail client, it may not include the entire address.";
		}

		if($ERROR) {
			$STEP = 1;
		}
	break;
	case 2 :
		if((!isset($_GET["email_address"])) || (!$EMAIL_ADDRESS = clean_input($_GET["email_address"], array("lower", "nows", "notags"))) || (!valid_address($EMAIL_ADDRESS))) {
			$ERROR++;
			$ERRORSTR[] = "Please provide a valid e-mail address into the e-mail address field (i.e first.lastName@med.ucalgary.ca).";
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

				if($db->AutoExecute("`".AUTH_DATABASE."`.`password_reset`", $processed, "INSERT")) {
					$message  = "Hello ".$FIRSTNAME." ".$LASTNAME.",\n\n";
					$message .= "This is an automated e-mail containing instructions to help you set or reset\n";
					$message .= "your ".APPLICATION_NAME." password.\n\n";
					$message .= "Your ".APPLICATION_NAME." Username is: ".$USERNAME."\n\n";
					$message .= "Please visit the following link to assign a new password to your account:\n";
					$message .= str_replace("http://", "https://", ENTRADA_URL)."/".basename(__FILE__)."?hash=".rawurlencode($PROXY_ID.":".$HASH)."\n\n";
					$message .= "Please Note:\n";
					$message .= "This password link will be valid for the next 3 days. If you do reset your\n";
					$message .= "password within this time period, you will need to reinitate this process.\n\n";
					$message .= "If you did not request a password reset for this account and you believe\n";
					$message .= "there has been a mistake, DO NOT click the above link. Please forward this\n";
					$message .= "message along with a description of the problem to: ".$AGENT_CONTACTS["administrator"]["email"]."\n\n";
					$message .= "Best Regards,\n";
					$message .= $AGENT_CONTACTS["administrator"]["name"]."\n";
					$message .= $AGENT_CONTACTS["administrator"]["email"]."\n";
					$message .= ENTRADA_URL."\n\n";
					$message .= "Requested By:\t".$_SERVER["REMOTE_ADDR"]."\n";
					$message .= "Requested At:\t".date("r", time())."\n";

					if(@mail($EMAIL_ADDRESS, "Password Reset - ".APPLICATION_NAME." Authentication System", $message, "From: \"".$AGENT_CONTACTS["administrator"]["name"]."\" <".$AGENT_CONTACTS["administrator"]["email"].">\nReply-To: \"".$AGENT_CONTACTS["administrator"]["name"]."\" <".$AGENT_CONTACTS["administrator"]["email"].">")) {
						$SUCCESS++;
						$SUCCESSSTR[] = "Hello <strong>".html_encode($FIRSTNAME." ".$LASTNAME).",</strong><br />A password reset authorisation e-mail has just been sent to <strong>".html_encode($EMAIL_ADDRESS)."</strong>. This e-mail contains further instructions on resetting your password, so please check your e-mail in a few minutes.";

						application_log("notice", "A password reset e-mail has just been sent for ".$USERNAME." [".$PROXY_ID."].");
					} else {
						$ERROR++;
						$ERRORSTR[] = "We were unable to send you your password reset authorisation e-mail at this time due to an unrecoverable error. The administrator has been notified of this error and will investigate the issue shortly.<br /><br />Please try again later, we apologize for any inconvenience this may have caused.";

						application_log("error", "Unable to send password reset notice as PHP's mail() function failed to initialize.");
					}

					$_SESSION = array();
					@session_destroy();
				} else {
					$ERROR++;
					$ERRORSTR[] = "We were unable to reset your password at this time due to an unrecoverable error. The administrator has been notified of this error and will investigate the issue shortly.<br /><br />Please try again later, we apologize for any inconvenience this may have caused.";

					application_log("error", "Unable to insert password reset query into ".AUTH_DATABASE.".password_reset table. Database said: ".$db->ErrorMsg());
				}
			} else {
				$ERROR++;
				$ERRORSTR[]	= "Your e-mail address (<strong>".htmlentities($EMAIL_ADDRESS)."</strong>) could not be found in our system. Please be sure that you have entered your official e-mail address correctly <br /><br />(Notice: For Classes 2008, 2009 and 2010 your official e-mail address is your __@med.ucalgary.ca. For Classes 2011 and onwards, your official e-mail address is your __@ucalgary.ca account).<br /><br />If you believe there is a problem, please contact us: <a href=\"mailto:".$AGENT_CONTACTS["administrator"]["email"]."\">".$AGENT_CONTACTS["administrator"]["email"]."</a>";

				application_log("notice", "Unable to locate an e-mail address [".$EMAIL_ADDRESS."] in the database to reset password.");
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
					<h2>Password Reset</h2>
					<?php
					// Page Display Step
					switch($STEP) {
						case 4 :
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
						case 3 :
							if($pieces = @explode(":", rawurldecode(trim($_GET["hash"])))) {
								if(count($pieces) == 2) {
									$PROXY_ID	= (int) trim($pieces[0]);
									$HASH		= clean_input($pieces[1], array("credentials"));

									$query		= "SELECT * FROM `".AUTH_DATABASE."`.`password_reset` WHERE `user_id` = ".$db->qstr($PROXY_ID, get_magic_quotes_gpc())." AND `hash` = ".$db->qstr($HASH, get_magic_quotes_gpc());
									$result		= $db->GetRow($query);
									if($result) {
										if(!(int) $result["complete"]) {
											$query	= "SELECT `username`,`firstname` FROM `".AUTH_DATABASE."`.`user_data` WHERE `id`=".$db->qstr($PROXY_ID, get_magic_quotes_gpc());
											$result	= $db->GetRow($query);
											if($result) {
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
												Welcome to the password reset program <strong><?php echo html_encode($result["firstname"]); ?></strong>. Using the form below enter the new password that you would like for the <?php echo APPLICATION_NAME; ?> Authentication System. <span style="background-color: #FFFFCC; padding-left: 5px; padding-right: 5px">Please be aware that your password must be between 6 and 24 characters.</span>
												<br /><br />
												<form action="<?php echo html_encode(basename(__FILE__)); ?>?hash=<?php echo rawurlencode(rawurldecode(trim($_GET["hash"]))); ?>" method="post">
												<input type="hidden" name="step" value="4" />
												<table style="width: 100%" cellspacing="1" cellpadding="1" border="0">
												<colgroup>
													<col style="width: 25%" />
													<col style="width: 75%" />
												</colgroup>
												<tfoot>
													<tr>
														<td colspan="2" style="padding-top: 15px; text-align: right">
															<input type="button" value="Cancel" class="button" onclick="window.location='<?php echo ENTRADA_RELATIVE; ?>'" />
															<input type="submit" class="button" value="Change" />
														</td>
													</tr>
												</tfoot>
												<tbody>
													<tr>
														<td style="padding-bottom: 10px"><span class="form-nrequired"><?php echo APPLICATION_NAME; ?> Username:</span></td>
														<td style="padding-bottom: 10px"><strong><?php echo html_encode($result["username"]); ?></strong></td>
													</tr>
													<tr>
														<td><label for="npassword1" class="form-required">Enter New Password:</label></td>
														<td>
															<input type="password" id="npassword1" name="npassword1" value="" style="width: 200px" maxlength="24" />
															<span class="content-small" style="padding-left: 5px">(<strong>Notice:</strong> your password must be 6 to 24 characters.)</span>
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
											} else {
												$ERROR++;
												$ERRORSTR[] = "Sorry, there was a problem with this password reset entry. Please start the process again: <a href=\"".html_encode(basename(__FILE__))."\" style=\"font-weight: bold\">click here</b></a>.";

												echo display_error($ERRORSTR);
											}
										} else {
											$ERROR++;
											$ERRORSTR[] = "The password reset details that you have provided have already been completed. If you've forgotten your ".APPLICATION_NAME." password again, please create a new password reset entry by starting the process over again. <a href=\"".html_encode(basename(__FILE__))."\" style=\"font-weight: bold\">Click here</a> to start again.";

											echo display_error($ERRORSTR);
										}
									} else {
										$ERROR++;
										$ERRORSTR[] = "There seems to be a problem with the URL that you are visiting. Please be sure that if you are trying to reset your ".APPLICATION_NAME." password, that you copy and paste the entire address (URL) string into your browser window. Sometimes if you just click the link your e-mail client may not include the whole link in what you click.";

										echo display_error($ERRORSTR);
									}
								} else {
									$ERROR++;
									$ERRORSTR[] = "There seems to be a problem with the URL that you are visiting. Please be sure that if you are trying to reset your ".APPLICATION_NAME." password, that you copy and paste the entire address (URL) string into your browser window. Sometimes if you just click the link your e-mail client may not include the whole link in what you click.";

									echo display_error($ERRORSTR);
								}
							} else {
								$ERROR++;
								$ERRORSTR[] = "There seems to be a problem with the URL that you are visiting. Please be sure that if you are trying to reset your ".APPLICATION_NAME." password, that you copy and paste the entire address (URL) string into your browser window. Sometimes if you just click the link your e-mail client may not include the whole link in what you click.";

								echo display_error($ERRORSTR);
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
								This page allows you to reset your <?php echo APPLICATION_NAME; ?> password. To begin please enter your official e-mail address into the textbox below and click <strong>Continue</strong>. The system will search for your e-mail address, and send you further instructions.
							</div>
							<form action="<?php echo html_encode(basename(__FILE__)); ?>" method="get">
							<table style="width: 100%" cellspacing="1" cellpadding="1" border="0">
							<colgroup>
								<col style="width: 25%" />
								<col style="width: 75%" />
							</colgroup>
							<tbody>
								<tr>
									<td><label for="email_address" style="font-weight: bold">Official E-Mail Address:</label></td>
									<td>
										<input type="text" id="email_address" style="width: 275px; vertical-align: middle" name="email_address" value="<?php echo ((isset($_POST["email_address"])) ? html_encode($_POST["email_address"]) : ""); ?>" />
										<input type="submit" class="button" value="Continue" style="vertical-align: middle; margin-left: 15px" />
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td style="padding-top: 10px">
										<div class="content-small" style="width: 75%">
											<strong>Notice:</strong> Your official e-mail address is the one registered with the institution.
										</div>
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