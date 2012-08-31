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
 * This file is loaded when someone opens the Feedback Agent.
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
require_once("Entrada/phpmailer/class.phpmailer.php");

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
	$PAGE_META["title"]		= "Automated Page Feedback";
	$ENCODED_INFORMATION	= "";

	if((isset($_GET["step"])) && ((int) trim($_GET["step"]))) {
		$STEP = (int) trim($_GET["step"]);
	}

	if(isset($_GET["enc"])) {
		$ENCODED_INFORMATION = trim($_GET["enc"]);
	} elseif(isset($_POST["action"])) {
		$ENCODED_INFORMATION = trim($_POST["enc"]);
	}
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php echo DEFAULT_CHARSET; ?>" />

		<title>%TITLE%</title>

		<meta name="description" content="%DESCRIPTION%" />
		<meta name="keywords" content="%KEYWORDS%" />

		<meta name="robots" content="noindex, nofollow" />

		<meta name="MSSmartTagsPreventParsing" content="true" />
		<meta http-equiv="imagetoolbar" content="no" />

		<link href="<?php echo ENTRADA_URL; ?>/css/common.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" type="text/css" media="all" />
		<link href="<?php echo ENTRADA_URL; ?>/css/print.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" type="text/css" media="print" />

		<link href="<?php echo ENTRADA_URL; ?>/images/favicon.ico" rel="shortcut icon" type="image/x-icon" />
		<link href="<?php echo ENTRADA_URL; ?>/w3c/p3p.xml" rel="P3Pv1" type="text/xml" />

		<script type="text/javascript" src="<?php echo ENTRADA_URL; ?>/javascript/scriptaculous/prototype.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
		<script type="text/javascript" src="<?php echo ENTRADA_URL; ?>/javascript/scriptaculous/scriptaculous.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>

		<script type="text/javascript" src="<?php echo ENTRADA_URL; ?>/javascript/common.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>

		%HEAD%

		<style type="text/css">
		body {
			overflow:	hidden;
			margin:		0px;
			padding:	0px;
		}
		</style>

		<script type="text/javascript">
		function submitFeedback() {
			document.getElementById('form-submitting').style.display = 'block';

			document.getElementById('feedback-form').submit();

			return;
		}
		</script>
	</head>
	<body>
	<?php
	switch($STEP) {
		case "2" :
			$extracted_information	= false;
			$tmp_information		= @unserialize(@base64_decode($ENCODED_INFORMATION));

			if((@is_array($tmp_information)) && (@count($tmp_information))) {
				$extracted_information = $tmp_information;
				unset($tmp_information);
			}
			
			if(strpos($extracted_information["url"], "annualreport") === false) {
				$message  = "Attention ".$AGENT_CONTACTS["administrator"]["name"]."\n";
			} else {
				$message  = "Attention ".$AGENT_CONTACTS["annualreport-support"]["name"]."\n";
			}
			$message .= "The following feedback information has been submitted:\n";
			$message .= "=======================================================\n\n";
			$message .= "Submitted At:\t\t".date("r", time())."\n";
			$message .= "Submitted By:\t\t".$_SESSION["details"]["firstname"]." ".$_SESSION["details"]["lastname"]." [".$_SESSION["details"]["username"]."]\n";
			$message .= "E-Mail Address:\t\t".$_SESSION["details"]["email"]."\n\n";
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

			$mail = new phpmailer();
			$mail->PluginDir		= ENTRADA_ABSOLUTE."/includes/classes/phpmailer/";
			$mail->SetLanguage("en", ENTRADA_ABSOLUTE."/includes/classes/phpmailer/language/");

			$mail->IsSendmail();
			$mail->Sendmail		= SENDMAIL_PATH;

			$mail->Priority		= 3;
			$mail->CharSet		= "iso-8859-1";
			$mail->Encoding		= "8bit";
			$mail->WordWrap		= 76;

			$mail->From     	= (($_SESSION["details"]["email"]) ? $_SESSION["details"]["email"] : $AGENT_CONTACTS["administrator"]["email"]);
			$mail->FromName		= $_SESSION["details"]["firstname"]." ".$_SESSION["details"]["lastname"];
			$mail->Sender		= $mail->From;
			$mail->AddReplyTo($mail->From, $mail->FromName);

			$mail->AddCustomHeader("X-Originating-IP: ".$_SERVER["REMOTE_ADDR"]);
			$mail->AddCustomHeader("X-Section: Feedback System");

			$mail->Subject	= "New Feedback Submission - ".APPLICATION_NAME;
			$mail->Body		= $message;
			$mail->ClearAddresses();
			if(strpos($extracted_information["url"], "annualreport") === false) {
				$mail->AddAddress($AGENT_CONTACTS["agent-feedback"]["email"], $AGENT_CONTACTS["agent-feedback"]["name"]);
			} else {
				$mail->AddAddress($AGENT_CONTACTS["annualreport-support"]["email"], $AGENT_CONTACTS["annualreport-support"]["name"]);
			}
			if($mail->Send()) {
				$SUCCESS++;
				$SUCCESSSTR[]	= "Thank-you for providing us with your valuable feedback. If we have questions regarding any of the information you provided, we will get in touch with you via e-mail.<br /><br />Once again, thank-you for using our automated feedback system and feel free to submit comments any time.";
			} else {
				$ERROR++;
				$ERRORSTR[]	= "We apologize however, we are unable to submit your feedback at this time due to a problem with the mail server.<br /><br />The system administrator has been informed of this error, please try again later.";

				application_log("error", "Unable to send feedback with the feedback agent. PHPMailer said: ".$mail->ErrorInfo);
			}
			?>
			<div id="wizard-header" style="position: absolute; top: 0px; left: 0px; width: 100%; height: 25px; background-color: #003366; padding: 4px 4px 4px 10px; overflow: hidden; white-space: nowrap">
				<span class="content-heading" style="color: #FFFFFF"><?php echo $PAGE_META["title"]; ?></span>
			</div>
			<div id="wizard-body" style="position: absolute; top: 35px; left: 0px; width: 452px; height: 440px; padding-left: 15px; overflow: auto">
				<?php
				if($ERROR) {
					echo "<h2>Feedback Submission Failure</h2>\n";

					echo display_error();
				} elseif($SUCCESS) {
					echo "<h2>Feedback Submitted Successfully</h2>\n";

					echo display_success();
				}
				?>

				To <strong>send new feedback</strong> or <strong>close this window</strong> please use the buttons below.
			</div>
			<div id="wizard-footer" style="position: absolute; top: 465px; left: 0px; width: 100%; height: 40px; border-top: 2px #CCCCCC solid; padding: 4px 4px 4px 10px">
				<table style="width: 452px" cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td style="width: 180px; text-align: left">
						<input type="button" class="button" value="Close" onclick="closeWindow()" />
					</td>
					<td style="width: 272px; text-align: right">
						<input type="button" class="button" value="New Feedback" onclick="window.location='<?php echo ENTRADA_URL; ?>/agent-feedback.php?enc=<?php echo $ENCODED_INFORMATION; ?>'" />
					</td>
				</tr>
				</table>
			</div>
			<?php
		break;
		case "1" :
		default :
			?>
			<form id="feedback-form" action="<?php echo ENTRADA_URL; ?>/agent-feedback.php?step=2&amp;enc=<?php echo $ENCODED_INFORMATION; ?>" method="post" style="display: inline">
			<div id="form-processing" style="display: block; position: absolute; top: 0px; left: 0px; width: 485px; height: 555px">
				<div id="wizard-header" style="position: absolute; top: 0px; left: 0px; width: 100%; height: 25px; background-color: #003366; padding: 4px 4px 4px 10px">
					<span class="content-heading" style="color: #FFFFFF"><?php echo $PAGE_META["title"]; ?></span>
				</div>
				<div id="wizard-body" style="position: absolute; top: 35px; left: 0px; width: 452px; height: 440px; padding-left: 15px; overflow: auto">
					<h2>Your Feedback is Important</h2>
					<table style="width: 100%" cellspacing="1" cellpadding="1" border="0">
					<colgroup>
						<col style="width: 25%" />
						<col style="width: 75%" />
					</colgroup>
					<thead>
						<tr>
							<td colspan="2" style="padding-bottom: 15px">
								<img src="<?php echo ENTRADA_URL; ?>/images/feedback.gif" width="48" height="48" alt="Give Feedback" title="Give Feedback" align="right" />
								This form is provided so you can efficiently provide our developers with important feedback regarding this application. Whether you are reporting a bug, feature request or just general feedback, all messages are important to us and appreciated.
							</td>
						</tr>
					</thead>
					<tbody>
					<tr>
						<td><span class="form-nrequired">Your Name:</span></td>
						<td><a href="mailto:<?php echo html_encode($_SESSION["details"]["email"]); ?>"><?php echo html_encode($_SESSION["details"]["firstname"]." ".$_SESSION["details"]["lastname"]); ?></a></td>
					</tr>
					<tr>
						<td><span class="form-nrequired">Your E-Mail:</span></td>
						<td><a href="mailto:<?php echo html_encode($_SESSION["details"]["email"]); ?>"><?php echo html_encode($_SESSION["details"]["email"]); ?></a></td>
					</tr>
					<tr>
						<td colspan="2" style="padding-top: 15px">
							<label for="feedback" class="form-required">Comment or Feedback:</label>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<textarea id="feedback" name="feedback" style="width: 98%; height: 115px"></textarea>
							<div style="margin-top: 15px" class="content-small"><strong>Please note:</strong> If you are submitting a bug or problem, please try to be specific as to the issue. If possible also let us know how to recreate the problem.</div>
						</td>
					</tr>
					</tbody>
					</table>
				</div>
				<div id="wizard-footer" style="position: absolute; top: 465px; left: 0px; width: 100%; height: 40px; border-top: 2px #CCCCCC solid; padding: 4px 4px 4px 10px">
					<table style="width: 100" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td style="width: 180px; text-align: left">
							<input type="button" class="button" value="Close" onclick="closeWindow()" />
						</td>
						<td style="width: 272px; text-align: right">
							<input type="button" class="button" value="Submit" onclick="submitFeedback()" />
						</td>
					</tr>
					</table>
				</div>
			</div>
			<div id="form-submitting" style="display: none; position: absolute; top: 0px; left: 0px;  background-color: #FFFFFF; opacity:.90; filter: alpha(opacity=90); -moz-opacity: 0.90">
				<div style="display: table; width: 485px; height: 555px; _position: relative; overflow: hidden">
					<div style="_position: absolute; _top: 50%; display: table-cell; vertical-align: middle;">
						<div style="_position: relative; _top: -50%; width: 100%; text-align: center">
							<span style="color: #003366; font-size: 18px; font-weight: bold">
								<img src="<?php echo ENTRADA_URL; ?>/images/loading.gif" width="32" height="32" alt="Feedback Sending" title="Please wait while your feedback is submitted" style="vertical-align: middle" /> Please Wait: feedback is being sent
							</span>
						</div>
					</div>
				</div>
			</div>
			</form>
			<?php
		break;
	}
	?>
	</body>
	</html>
	<?php
}