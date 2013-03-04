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
 * This file is loaded when someone opens the Anonymous Feedback Agent.
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

ob_start("on_checkout");

if((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"DTD/xhtml1-transitional.dtd\">\n";
	echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">\n";
	echo "<body>\n";
	echo "<script type=\"text/javascript\">\n";
	echo "alert('It appears as though your session has expired; you will now be taken back to the login page.');\n";
	echo "if(window.opener) {\n";
	echo "	window.opener.location = '".ENTRADA_URL.((isset($_SERVER["REQUEST_URI"])) ? "?url=".rawurlencode(clean_input($_SERVER["REQUEST_URI"], array("nows", "url"))) : "")."';\n";
	echo "	top.window.close();\n";
	echo "} else {\n";
	echo "	window.location = '".ENTRADA_URL.((isset($_SERVER["REQUEST_URI"])) ? "?url=".rawurlencode(clean_input($_SERVER["REQUEST_URI"], array("nows", "url"))) : "")."';\n";
	echo "}\n";
	echo "</script>\n";
	echo "</body>\n";
	echo "</html>\n";
	exit;
} else {
	
	global $translate;
	
	$ENCODED_INFORMATION = "";

	if((isset($_GET["step"])) && ((int) trim($_GET["step"]))) {
		$STEP = (int) trim($_GET["step"]);
	}

	if(isset($_POST["enc"])) {
		$ENCODED_INFORMATION = trim($_POST["enc"]);
	} elseif(isset($_POST["action"])) {
		$ENCODED_INFORMATION = trim($_POST["enc"]);
	}
	
	if (isset($_POST["who"])) {
		$WHO = clean_input($_POST["who"], array("trim", "striptags"));
	} else {
		/*
		 * If $_POST["who"] is not set the file was opened in a window from a legacy call.
		 */
		$WHO = "system";
		?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
	<head lang="en-US" dir="ltr">
		<meta http-equiv="Content-Type" content="text/html; charset=<?php echo DEFAULT_CHARSET; ?>" /> 
		<title>Feedback for MEdTech Central</title>
		<meta name="description" content="%DESCRIPTION%" />
		<meta name="keywords" content="%KEYWORDS%" />
		<meta name="author" content="Medical Education Technology Unit, Queen's University" />
		<meta name="copyright" content="Copyright (c) 2010 Queen's University. All Rights Reserved." />
		<meta name="robots" content="index,follow" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<link rel="shortcut icon" href="<?php echo TEMPLATE_RELATIVE; ?>/images/favicon.ico" />
		<link rel="icon" href="<?php echo TEMPLATE_RELATIVE; ?>/images/favicon.ico" type="image/x-icon" />
		<link href="<?php echo ENTRADA_RELATIVE; ?>/css/common.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" type="text/css" media="all" />
<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/jquery/jquery.min.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
<script type="text/javascript">
jQuery(function(){
	jQuery("input[value=Close]").live("click", function() {
		window.close();
	});
	jQuery("#feedback-form input[value=Submit]").live("click", function() {
		jQuery("#feedback-form").submit();
	});
});
</script>
	</head>
	<body style="<?php if ($STEP == 2) { echo "padding:20px;"; } ?>">
		<?php
	}

	$feedback_form = $translate->_("global_feedback_widget");
	
	if ($feedback_form["global"][$WHO]["form"]) {
		$form_content = $feedback_form["global"][$WHO]["form"];
	} else if ($feedback_form[$ENTRADA_USER->getGroup()][$WHO]["form"]) {
		$form_content = $feedback_form[$ENTRADA_USER->getGroup()][$WHO]["form"];
	} else {
		add_error("There was a problem loading the feedback form for the contact you selected. A system administrator has been informed, please try again later.");
	}
	
	if (!$ERROR) {

		switch($STEP) {
			case "2" :

				if (!empty($form_content["recipients"])) {
					if (isset($_POST["hide_identity"]) && $_POST["hide_identity"]) {
						$email_address = $AGENT_CONTACTS["administrator"]["email"];
						$fullname = "Anonymous Student";
					} else {
						$email_address = $_SESSION["details"]["email"];
						$fullname = $_SESSION["details"]["firstname"]." ".$_SESSION["details"]["lastname"];
					}

					$extracted_information	= false;
					$tmp_information		= @unserialize(@base64_decode($ENCODED_INFORMATION));

					if((@is_array($tmp_information)) && (@count($tmp_information))) {
						$extracted_information = $tmp_information;
						unset($tmp_information);
					}

					$message  = "Attention ".$AGENT_CONTACTS["agent-anonymous-feedback"]["name"]."\n";
					$message .= "The following student feedback information has been submitted:\n";
					$message .= "=======================================================\n\n";
					$message .= "Submitted At:\t\t".date("r", time())."\n";
					$message .= "Student Feedback / Comments:\n";
					$message .= "-------------------------------------------------------\n";
					$message .= clean_input($_POST["feedback"], array("trim", "emailcontent"))."\n\n";
					$message .= "=======================================================";

					$mail = new Zend_Mail("iso-8859-1");

					$mail->addHeader("X-Priority", "3");
					$mail->setFrom($email_address, $fullname);
					$mail->addHeader("X-Originating-IP", $_SERVER["REMOTE_ADDR"]);
					$mail->addHeader("X-Section", "Student Feedback System");

					$mail->setSubject("New Student Feedback Submission - ".APPLICATION_NAME);

					foreach ($form_content["recipients"] as $email => $name) {
						$mail->addTo($email, $name);
					}

					$message = "The following feedback information has been submitted:\n";
					$message .= "=======================================================\n\n";
					$message .= "Submitted At:\t\t".date("r", time())."\n";
					$message .= "Submitted By:\t\t".$fullname." [".((isset($_POST["hide_identity"])) ? "withheld" : $_SESSION["details"]["username"])."]\n";
					$message .= "E-Mail Address:\t\t".$email_address."\n\n";
					$message .= "Comments / Feedback:\n";
					$message .= "-------------------------------------------------------\n";
					$message .= clean_input($_POST["feedback"], array("trim", "emailcontent"))."\n\n";
					$message .= "Web-Browser / OS:\n";
					$message .= "-------------------------------------------------------\n";
					$message .= clean_input($_SERVER["HTTP_USER_AGENT"], array("trim", "emailcontent"))."\n\n";
					$message .= "URL Sent From:\n";
					$message .= "-------------------------------------------------------\n";
					$message .= ((isset($_SERVER["HTTPS"])) ? "https" : "http")."://".$_SERVER["HTTP_HOST"].clean_input($extracted_information["url"], array("trim", "emailcontent"))."\n\n";
					$message .= "=======================================================";

					$mail->setBodyText($message);

					if($mail->Send()) {
						add_success("Thank-you for providing us with your valuable feedback.<br /><br />Once again, thank-you for using our automated anonymous feedback system and feel free to submit comments any time.");
						echo display_success();
						echo "<div style=\"text-align:right;\"><input type=\"button\" value=\"Close\" /></a>";
					} else {
						add_error("We apologize however, we are unable to submit your feedback at this time due to a problem with the mail server.<br /><br />The system administrator has been informed of this error, please try again later.");
						echo display_error();
						application_log("error", "Unable to send anonymous feedback with the anonymous feedback agent.");
					}
				} else {
					add_error("We apologize however, we are unable to submit your feedback at this time due to a problem with the mail server.<br /><br />The system administrator has been informed of this error, please try again later.");
					echo display_error();
					application_log("error", "An error ocurred when trying to send feedback to agent [".$WHO."], no recipients found in language file.");
				}
			break;
			case "1" :
			default :
				?>
				<form id="feedback-form" action="<?php echo ENTRADA_URL; ?>/agent-feedback.php?step=2" method="post" style="display: inline">
				<?php if (isset($_POST["who"])) { ?><input type="hidden" name="who" value="<?php echo $WHO; ?>" /><?php } ?>
				<?php if (isset($_POST["enc"])) { ?><input type="hidden" name="enc" value="<?php echo $ENCODED_INFORMATION; ?>" /><?php } ?>
				<div id="form-processing" style="display: block; position: absolute; top: 0px; left: 0px; width: 485px; height: 515px">
					<div id="wizard-header" style="position: absolute; top: 0px; left: 0px; width: 100%; height: 25px; background-color: #003366; padding: 4px 4px 4px 10px">
						<span class="content-heading" style="color: #FFFFFF"><?php echo $form_content["title"]; ?></span>
					</div>
					<div id="wizard-body" style="position: absolute; top: 35px; left: 0px; width: 452px; height: 380px; padding-left: 15px; overflow: auto">
						<h2>Your Feedback is Important</h2>
						<table style="width: 100%" cellspacing="1" cellpadding="1" border="0">
						<colgroup>
							<col style="width: 25%" />
							<col style="width: 75%" />
						</colgroup>
						<thead>
							<tr>
								<td colspan="2" style="padding-bottom: 15px">
									<?php echo $form_content["description"]; ?>
								</td>
							</tr>
						</thead>
						<tbody>
						<?php if ($form_content["anon"]) { ?>
						<tr>
							<td colspan="2">
								<input type="checkbox" value="1" id="hide_identity" name="hide_identity" checked="checked" />
								<label style="text-align: left;" for="hide_identity" class="form-nrequired"><?php echo $form_content["anon-text"]; ?></label>
							</td>
						</tr>
						<?php } ?>
						<tr>
							<td colspan="2" style="padding-top: 15px">
								<label for="feedback" class="form-required">Feedback or Comments:</label>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<textarea id="feedback" name="feedback" style="width: 98%; height: 115px" maxlength="750"></textarea>
							</td>
						</tr>
						</tbody>
						</table>
					</div>
					<div id="wizard-footer" style="position: absolute; top: 415px; left: 0px; width: 100%; height: 40px; border-top: 2px #CCCCCC solid; padding: 4px 4px 4px 10px">
						<table style="width: 100" cellspacing="0" cellpadding="0" border="0">
						<tr>
							<td style="width: 180px; text-align: left">
								<input type="button" class="button" value="Close" />
							</td>
							<td style="width: 272px; text-align: right">
								<input type="button" class="button" value="Submit" />
							</td>
						</tr>
						</table>
					</div>
				</div
				</form>
				<?php
			break;
		}
	} else {
		echo display_error();
		echo "<input type=\"button\" value=\"Submit\" />";
	}
	
	if (!isset($_POST["who"])) {
		?>
	</body>
</html>
		<?php
	}
	
}