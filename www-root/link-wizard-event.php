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
 * Loads the Learning Event link wizard when a teacher / director wants to add /
 * edit a linked resource on the Manage Events > Content page.
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
	$ACTION				= "add";
	$EVENT_ID			= 0;
	$ELINK_ID			= 0;
	$JS_INITSTEP		= 1;

	if(isset($_GET["action"])) {
		$ACTION	= trim($_GET["action"]);
	}

	if((isset($_GET["step"])) && ((int) trim($_GET["step"]))) {
		$STEP	= (int) trim($_GET["step"]);
	}

	if((isset($_GET["id"])) && ((int) trim($_GET["id"]))) {
		$EVENT_ID	= (int) trim($_GET["id"]);
	}

	if((isset($_GET["lid"])) && ((int) trim($_GET["lid"]))) {
		$ELINK_ID	= (int) trim($_GET["lid"]);
	}

	$PAGE_META["title"] = "Link Wizard";

	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php echo DEFAULT_CHARSET; ?>" />

		<title>%TITLE%</title>

		<meta name="description" content="%DESCRIPTION%" />
		<meta name="keywords" content="%KEYWORDS%" />

		<meta name="robots" content="index, follow" />

		<meta name="MSSmartTagsPreventParsing" content="true" />
		<meta http-equiv="imagetoolbar" content="no" />


		<link href="<?php echo ENTRADA_URL; ?>/javascript/calendar/css/xc2_default.css" rel="stylesheet" type="text/css" media="all" />

		<link href="<?php echo ENTRADA_URL; ?>/css/common.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" type="text/css" media="all" />
		<link href="<?php echo ENTRADA_URL; ?>/css/print.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" type="text/css" media="print" />
		<link href="<?php echo ENTRADA_URL; ?>/css/wizard.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" type="text/css" media="all" />

		<link href="<?php echo ENTRADA_URL; ?>/images/favicon.ico" rel="shortcut icon" type="image/x-icon" />
		<link href="<?php echo ENTRADA_URL; ?>/w3c/p3p.xml" rel="P3Pv1" type="text/xml" />

		<script type="text/javascript" src="<?php echo ENTRADA_URL; ?>/javascript/calendar/config/xc2_default.js"></script>
		<script type="text/javascript" src="<?php echo ENTRADA_URL; ?>/javascript/calendar/script/xc2_inpage.js"></script>

		%HEAD%

		<script type="text/javascript" src="<?php echo ENTRADA_URL; ?>/javascript/scriptaculous/prototype.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
		<script type="text/javascript" src="<?php echo ENTRADA_URL; ?>/javascript/scriptaculous/scriptaculous.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
		<script type="text/javascript" src="<?php echo ENTRADA_URL; ?>/javascript/common.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
		<script type="text/javascript" src="<?php echo ENTRADA_URL; ?>/javascript/wizard.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	</head>
	<body>
	<?php
	if($EVENT_ID) {
		$query	= "	SELECT a.*, b.`organisation_id`
					FROM `events` AS a
					LEFT JOIN `courses` AS b
					ON b.`course_id` = a.`course_id`
					WHERE a.`event_id` = ".$db->qstr($EVENT_ID)."
					AND b.`course_active` = '1'";
		$result	= $db->GetRow($query);
		if($result) {
			$query = "SELECT * FROM `events` WHERE `parent_id` = ".$db->qstr($EVENT_ID);
			$access_allowed = false;
			if (!$ENTRADA_ACL->amIAllowed(new EventContentResource($EVENT_ID, $result["course_id"], $result["organisation_id"]), "update")) {
				if ($sessions = $db->GetAll($query)) {
					foreach ($sessions as $session) {
						if ($ENTRADA_ACL->amIAllowed(new EventContentResource($session["event_id"], $result["course_id"], $result["organisation_id"]), "update")) {
							$access_allowed = true;
						}
					}
				}
			} else {
				$access_allowed = true;
			}
			if (!$access_allowed) {
				$ONLOAD[]		= "closeWizard()";

				$ERROR++;
				$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module. If you believe you are receiving this message in error please contact the MEdTech Unit at 613-533-6000 x74918 and we can assist you.";

				echo display_error();

				application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to the link wizard.");
			} else {
				switch($ACTION) {
					case "edit" :
						/**
						 * Edit link form.
						 */
						if($ELINK_ID) {
							$query	= "SELECT * FROM `event_links` WHERE `event_id` = ".$db->qstr($EVENT_ID)." AND `elink_id` = ".$db->qstr($ELINK_ID);
							$result	= $db->GetRow($query);
							if($result) {
								// Error Checking
								switch($STEP) {
									case 2 :
										/**
										 * In this error checking we are working backwards along the internal javascript
										 * steps timeline. This is so the JS_INITSTEP variable is set to the lowest page
										 * number that contains errors.
										 */

										$PROCESSED["event_id"] = $EVENT_ID;

										/**
										 * Step 2 Error Checking
										 * Because this unsets the $ERRORSTR array, only do this if there is not already an error.
										 * PITA, I know.
										 */
										if((isset($_POST["timedrelease"])) && ($_POST["timedrelease"] == "yes")) {
											$timed_release		= validate_calendars("valid", false, false);

											if($ERROR) {
												$ONLOAD[]		= "alert('".addslashes($ERRORSTR[0])."')";

												$ERROR			= 0;
												$ERRORSTR		= array();

												$ERROR++;
												$ERRORSTR[]		= "q4";
												$JS_INITSTEP	= 2;
											}

											if((isset($timed_release["start"])) && ((int) $timed_release["start"])) {
												$PROCESSED["release_date"] = (int) $timed_release["start"];
											}

											if((isset($timed_release["finish"])) && ((int) $timed_release["finish"])) {
												$PROCESSED["release_until"] = (int) $timed_release["finish"];
											}
										} else {
											$PROCESSED["release_date"]	= 0;
											$PROCESSED["release_until"]	= 0;
										}

										/**
										 * Step 3 Error Checking
										 */
										if((isset($_POST["link"])) && ($tmp_input = clean_input($_POST["link"], array("notags", "nows"))) && ($tmp_input != "http://")) {
											$PROCESSED["link"] = $tmp_input;
										} else {
											$ERROR++;
											$ERRORSTR[] = "q5";
											$JS_INITSTEP = 3;
										}

										if((isset($_POST["link_title"])) && ($tmp_input = clean_input($_POST["link_title"], array("trim", "notags")))) {
											$PROCESSED["link_title"] = $tmp_input;
										} else {
											$PROCESSED["link_title"] = "";
										}

										if((isset($_POST["link_notes"])) && ($tmp_input = clean_input($_POST["link_notes"], array("trim", "notags")))) {
											$PROCESSED["link_notes"] = $tmp_input;
										} else {
											$ERROR++;
											$ERRORSTR[] = "q7";
											$JS_INITSTEP = 3;
										}

										/**
										 * Step 1 Error Checking
										 */
										if((isset($_POST["proxify"])) && ($_POST["proxify"] == "yes") && ($PROXY_URLS["default"]["active"] != "")) {
											$PROCESSED["proxify"] = 1;
										} else {
											$PROCESSED["proxify"] = 0;
										}

										if((isset($_POST["required"])) && ($_POST["required"] == "yes")) {
											$PROCESSED["required"] = 1;
										} else {
											$PROCESSED["required"] = 0;
										}

										$PROCESSED["updated_date"]	= time();
										$PROCESSED["updated_by"]	= $_SESSION["details"]["id"];

										if(!$ERROR) {
											if($db->AutoExecute("event_links", $PROCESSED, "UPDATE", "elink_id = ".$db->qstr($ELINK_ID)." AND event_id = ".$db->qstr($EVENT_ID))) {
												last_updated("event", $EVENT_ID);

												application_log("success", "Link ID ".$ELINK_ID." was successfully update to the database for event [".$EVENT_ID."].");
											} else {
												$ONLOAD[]		= "alert('This update was not successfully saved. The MEdTech Unit has been informed of this error, please try again later.')";

												$ERROR++;
												$ERRORSTR[]		= "q5";
												$JS_INITSTEP	= 3;

												application_log("error", "Unable to update this record in the database for event ID [".$EVENT_ID."] and link ID [".$ELINK_ID."]. Database said: ".$db->ErrorMsg());
											}
										}


										if($ERROR) {
											$STEP = 1;
										}
									break;
									case 1 :
									default :
										/**
										 * Since this is the first step, simply fill $PROCESSED with the $result value.
										 */

										$PROCESSED = $result;

										if(((int) $PROCESSED["release_date"]) || ((int) $PROCESSED["release_until"])) {
											$show_timed_release	= true;
										} else {
											$show_timed_release = false;
										}
									break;
								}

								// Display Edit Step
								switch($STEP) {
									case 2 :
										$ONLOAD[] = "parentReload()";
										?>
										<div id="wizard">
											<div id="header">
												<span class="content-heading" style="color: #FFFFFF">Link Wizard</span> <span style="font-size: 11px; color: #FFFFFF"><strong>Editing</strong> <?php echo html_encode($PROCESSED["link_title"]); ?></span>
											</div>
											<div id="body">
												<h2>Link Updated Successfully</h2>
	
												<div class="display-success">
													You have successfully updated <strong><?php echo html_encode($PROCESSED["link_title"]); ?></strong> in this event.
												</div>
	
												To <strong>re-edit this link</strong> or <strong>close this window</strong> please use the buttons below.
											</div>
											<div id="footer">
												<input type="button" class="button" value="Close" onclick="closeWizard()" style="float: left; margin: 4px 0px 4px 10px" />
												<input type="button" class="button" value="Re-Edit Link" onclick="window.location='<?php echo ENTRADA_URL; ?>/link-wizard-event.php?action=edit&amp;id=<?php echo $EVENT_ID; ?>&amp;lid=<?php echo $ELINK_ID; ?>'" style="float: right; margin: 4px 10px 4px 0px" />
											</div>
										</div>
										<?php
									break;
									case 1 :
									default :
										$ONLOAD[] = "initStep(".$JS_INITSTEP.")";

										if(((isset($_POST["timedrelease"])) && ($_POST["timedrelease"] == "yes")) || ((isset($show_timed_release)) && ($show_timed_release))) {
											$ONLOAD[] = "timedRelease('block')";
										} else {
											$ONLOAD[] = "timedRelease('none')";
										}
										?>
										<div id="wizard">
											<form id="wizard-form" action="<?php echo ENTRADA_URL; ?>/link-wizard-event.php?action=edit&amp;id=<?php echo $EVENT_ID; ?>&amp;lid=<?php echo $ELINK_ID; ?>&amp;step=2" method="post" style="display: inline">
											<div id="header">
												<span class="content-heading" style="color: #FFFFFF">Link Wizard</span> <span style="font-size: 11px; color: #FFFFFF"><strong>Editing</strong> <?php echo html_encode($PROCESSED["link_title"]); ?></span>
											</div>
											<div id="body">
												<h2 id="step-title"></h2>
												<div id="step1" style="display: none">
													<div id="q1" class="wizard-question<?php echo ((in_array("q1", $ERRORSTR)) ? " display-error" : ""); ?>">
														<div style="font-size: 14px">Does this link require the proxy to be enabled?</div>
														<div style="padding-left: 65px">
															<input type="radio" id="proxify_no" name="proxify" value="no"<?php echo (((!isset($PROCESSED["proxify"])) || (!$PROCESSED["proxify"]) || ($PROXY_URLS["default"]["active"] == "")) ? " checked=\"checked\"" : ""); ?> /> <label for="proxify_no">no</label><br />
															<?php if($PROXY_URLS["default"]["active"] != "") : ?>
															<input type="radio" id="proxify_yes" name="proxify" value="yes"<?php echo (($PROCESSED["proxify"] == 1) ? " checked=\"checked\"" : ""); ?> /> <label for="proxify_yes">yes</label><br />
															<?php endif; ?>
														</div>
													</div>

													<div id="q2" class="wizard-question<?php echo ((in_array("q2", $ERRORSTR)) ? " display-error" : ""); ?>">
														<div style="font-size: 14px">Is the use of this resource required or optional by the learner?</div>
														<div style="padding-left: 65px">
															<input type="radio" id="required_no" name="required" value="no"<?php echo (((!isset($PROCESSED["required"])) || (!$PROCESSED["required"])) ? " checked=\"checked\"" : ""); ?> /> <label for="required_no">optional</label><br />
															<input type="radio" id="required_yes" name="required" value="yes"<?php echo (($PROCESSED["required"] == 1) ? " checked=\"checked\"" : ""); ?> /> <label for="required_yes">required</label><br />
														</div>
													</div>
												</div>

												<div id="step2" style="display: none">
													<div id="q4" class="wizard-question<?php echo ((in_array("q4", $ERRORSTR)) ? " display-error" : ""); ?>">
														<div style="font-size: 14px">Would you like to add timed release dates to this link?</div>
														<div style="padding-left: 65px">
															<input type="radio" id="timedrelease_no" name="timedrelease" value="no" onclick="timedRelease('none')"<?php echo ((((!isset($_POST["timedrelease"])) || ($_POST["timedrelease"] == "no")) && ((!isset($show_timed_release)) || (!$show_timed_release))) ? " checked=\"checked\"" : ""); ?> /> <label for="timedrelease_no">No, this link is accessible any time.</label><br />
															<input type="radio" id="timedrelease_yes" name="timedrelease" value="yes" onclick="timedRelease('block')"<?php echo ((((isset($_POST["timedrelease"])) && ($_POST["timedrelease"] == "yes")) || ((isset($show_timed_release)) && ($show_timed_release))) ? " checked=\"checked\"" : ""); ?> /> <label for="timedrelease_yes">Yes, this link has timed release information.</label><br />
														</div>

														<div id="timed-release-info" style="display: none">
															<br />
															By checking the box on the left, you will enable the ability to select release / revoke dates and times for this link.
															<br /><br />
															<table style="width: 100%" cellspacing="0" cellpadding="4" border="0" summary="Timed Release Information">
															<colgroup>
																<col style="width: 3%" />
																<col style="width: 30%" />
																<col style="width: 67%" />
															</colgroup>
															<?php echo generate_calendars("valid", "Accessible", true, false, ((isset($PROCESSED["release_date"])) ? $PROCESSED["release_date"] : 0), true, false, ((isset($PROCESSED["release_until"])) ? $PROCESSED["release_until"] : 0), true, true); ?>
															</table>
														</div>
													</div>
												</div>

												<div id="step3" style="display: none">
													<div id="q5" class="wizard-question<?php echo ((in_array("q5", $ERRORSTR)) ? " display-error" : ""); ?>">
														<div style="font-size: 14px">Please provide the full URL of the link:</div>
														<div style="padding-left: 65px; padding-right: 10px; padding-top: 10px">
															<label for="link" class="form-required">Link URL:</label> <span class="content-small"><strong>Example:</strong> http://meds.queensu.ca</span><br />
															<input type="text" id="link" name="link" value="<?php echo ((isset($PROCESSED["link"])) ? html_encode($PROCESSED["link"]) : ""); ?>" maxlength="500" style="width: 350px;" />
														</div>
													</div>
													<div id="q6" class="wizard-question<?php echo ((in_array("q6", $ERRORSTR)) ? " display-error" : ""); ?>">
														<div style="font-size: 14px">You can <span style="font-style: oblique">optionally</span> provide a different title for this link.</div>
														<div style="padding-left: 65px; padding-right: 10px; padding-top: 10px">
															<label for="link_title" class="form-nrequired">Link Title:</label> <span class="content-small"><strong>Example:</strong> Faculty of Health Sciences</span><br />
															<input type="text" id="link_title" name="link_title" value="<?php echo ((isset($PROCESSED["link_title"])) ? html_encode($PROCESSED["link_title"]) : ""); ?>" maxlength="128" style="width: 350px;" />
														</div>
													</div>
													<div id="q7" class="wizard-question<?php echo ((in_array("q7", $ERRORSTR)) ? " display-error" : ""); ?>">
														<div style="font-size: 14px">You <span style="font-style: oblique">must</span> provide a description for this link as well.</div>
														<div style="padding-left: 65px; padding-right: 10px; padding-top: 10px">
															<label for="link_notes" class="form-required">Link Description:</label><br />
															<textarea id="link_notes" name="link_notes" style="width: 350px; height: 75px"><?php echo ((isset($PROCESSED["link_notes"])) ? html_encode($PROCESSED["link_notes"]) : ""); ?></textarea>
														</div>
													</div>
												</div>
											</div>
											<div id="footer">
												<input type="button" class="button" value="Close" onclick="closeWizard()" style="float: left; margin: 4px 0px 4px 10px" />
												<input type="button" class="button" id="next-button" value="Next Step" onclick="nextStep()" style="float: right; margin: 4px 10px 4px 0px" />
												<input type="button" class="button" id="back-button" value="Previous Step" onclick="prevStep()" style="display: none; float: right; margin: 4px 10px 4px 0px" />
											</div>
											<div id="uploading-window">
												<div style="display: table; width: 485px; height: 555px; _position: relative; overflow: hidden">
													<div style=" _position: absolute; _top: 50%;display: table-cell; vertical-align: middle;">
														<div style="_position: relative; _top: -50%; width: 100%; text-align: center">
															<span style="color: #003366; font-size: 18px; font-weight: bold">
																<img src="<?php echo ENTRADA_URL; ?>/images/loading.gif" width="32" height="32" alt="Link Saving" title="Please wait while changes are being saved." style="vertical-align: middle" /> Please Wait: changes are being saved.
															</span>
														</div>
													</div>
												</div>
											</div>
											</form>
										</div>
										<?php
									break;
								}
							} else {
								$ERROR++;
								$ERRORSTR[] = "The provided link identifier does not exist in the provided event.";

								echo display_error();

								application_log("error", "Link wizard was accessed with a link id that was not found in the database.");
							}
						} else {
							$ERROR++;
							$ERRORSTR[] = "You must provide a link identifier when using the link wizard.";

							echo display_error();

							application_log("error", "Link wizard was accessed without any link id.");
						}
					break;
					case "add" :
					default :
						/**
						 * Add link form.
						 */

						// Error Checking
						switch($STEP) {
							case 2 :
								/**
								 * In this error checking we are working backwards along the internal javascript
								 * steps timeline. This is so the JS_INITSTEP variable is set to the lowest page
								 * number that contains errors.
								 */

								$PROCESSED["event_id"] = $EVENT_ID;

								/**
								 * Step 2 Error Checking
								 * Because this unsets the $ERRORSTR array, only do this if there is not already an error.
								 * PITA, I know.
								 */
								if((isset($_POST["timedrelease"])) && ($_POST["timedrelease"] == "yes")) {
									$timed_release = validate_calendars("valid", false, false);

									if($ERROR) {
										$ONLOAD[]		= "alert('".addslashes($ERRORSTR[0])."')";

										$ERROR			= 0;
										$ERRORSTR		= array();

										$ERROR++;
										$ERRORSTR[]		= "q4";
										$JS_INITSTEP	= 2;
									}

									if((isset($timed_release["start"])) && ((int) $timed_release["start"])) {
										$PROCESSED["release_date"]	= (int) $timed_release["start"];
									}

									if((isset($timed_release["finish"])) && ((int) $timed_release["finish"])) {
										$PROCESSED["release_until"]	= (int) $timed_release["finish"];
									}
								}

								if(!$ERROR) {
									/**
									 * Step 3 Error Checking
									 */
									if((isset($_POST["link"])) && (trim($_POST["link"])) && (trim($_POST["link"]) != "http://")) {
										$PROCESSED["link"]	= trim($_POST["link"]);
									} else {
										$ERROR++;
										$ERRORSTR[]		= "q5";
										$JS_INITSTEP	= 3;
									}

									if((isset($_POST["link_title"])) && (trim($_POST["link_title"]))) {
										$PROCESSED["link_title"]	= trim($_POST["link_title"]);
									} else {
										$PROCESSED["link_title"]	= "";
									}

									if((isset($_POST["link_notes"])) && (trim($_POST["link_notes"]))) {
										$PROCESSED["link_notes"]	= trim($_POST["link_notes"]);
									} else {
										$ERROR++;
										$ERRORSTR[]		= "q7";
										$JS_INITSTEP	= 3;
									}

									/**
									 * Step 1 Error Checking
									 */
									if((isset($_POST["proxify"])) && ($_POST["proxify"] == "yes")) {
										$PROCESSED["proxify"] = 1;
									} else {
										$PROCESSED["proxify"] = 0;
									}

									if((isset($_POST["required"])) && ($_POST["required"] == "yes")) {
										$PROCESSED["required"] = 1;
									} else {
										$PROCESSED["required"] = 0;
									}

									$PROCESSED["updated_date"]	= time();
									$PROCESSED["updated_by"]	= $_SESSION["details"]["id"];

									if(!$ERROR) {
										$query	= "SELECT * FROM `event_links` WHERE `event_id` = ".$db->qstr($EVENT_ID)." AND `link` = ".$db->qstr($PROCESSED["link"]);
										$result	= $db->GetRow($query);
										if($result) {
											$ONLOAD[]		= "alert('A link to ".addslashes($PROCESSED["link"])." already exists in this teaching event.')";

											$ERROR++;
											$ERRORSTR[]		= "q5";
											$JS_INITSTEP	= 3;
										} else {
											if(($db->AutoExecute("event_links", $PROCESSED, "INSERT")) && ($ELINK_ID = $db->Insert_Id())) {
												last_updated("event", $EVENT_ID);
											} else {
												$ONLOAD[]		= "alert('The new link was not successfully saved. The MEdTech Unit has been informed of this error, please try again later.')";

												$ERROR++;
												$ERRORSTR[]		= "q5";
												$JS_INITSTEP	= 3;

												application_log("error", "Unable to insert the link into the database for event ID [".$EVENT_ID."]. Database said: ".$db->ErrorMsg());
											}
										}
									}
								}

								if($ERROR) {
									$STEP = 1;
								}
							break;
							case 1 :
							default :
								continue;
							break;
						}

						// Display Add Step
						switch($STEP) {
							case 2 :
								$ONLOAD[] = "parentReload()";
								?>
								<div id="wizard">
									<div id="header">
										<span class="content-heading" style="color: #FFFFFF">Link Wizard</span> <span style="font-size: 11px; color: #FFFFFF"><strong>Adding</strong> new event link</span>
									</div>
									<div id="body">
										<h2>Link Added Successfully</h2>
	
										<div class="display-success">
											You have successfully added <strong><?php echo html_encode($PROCESSED["link"]); ?></strong> to this event.
										</div>
	
										To <strong>add another link</strong> or <strong>close this window</strong> please use the buttons below.
									</div>
									<div id="footer">
										<input type="button" class="button" value="Close" onclick="closeWizard()" style="float: left; margin: 4px 0px 4px 10px" />
										<input type="button" class="button" value="Add Another Link" onclick="window.location='<?php echo ENTRADA_URL; ?>/link-wizard-event.php?id=<?php echo $EVENT_ID; ?>&amp;action=add'" style="float: right; margin: 4px 10px 4px 0px" />
									</div>
								</div>
								<?php
							break;
							case 1 :
							default :
								$ONLOAD[] = "initStep(".$JS_INITSTEP.")";

								if((isset($_POST["timedrelease"])) && ($_POST["timedrelease"] == "yes")) {
									$ONLOAD[] = "timedRelease('block')";
								} else {
									$ONLOAD[] = "timedRelease('none')";
								}
								?>
								<div id="wizard">
									<form id="wizard-form" action="<?php echo ENTRADA_URL; ?>/link-wizard-event.php?action=add&amp;id=<?php echo $EVENT_ID; ?>&amp;step=2" method="post" style="display: inline">
									<div id="header">
										<span class="content-heading" style="color: #FFFFFF">Link Wizard</span> <span style="font-size: 11px; color: #FFFFFF"><strong>Adding</strong> new event link</span>
									</div>
									<div id="body">
										<h2 id="step-title"></h2>
										<div id="step1" style="display: none">
											<div id="q1" class="wizard-question<?php echo ((in_array("q1", $ERRORSTR)) ? " display-error" : ""); ?>">
												<div style="font-size: 14px">Does this link require the proxy to be enabled?</div>
												<div style="padding-left: 65px">
													<input type="radio" id="proxify_no" name="proxify" value="no"<?php echo (((!isset($PROCESSED["proxify"])) || (!$PROCESSED["proxify"])) ? " checked=\"checked\"" : ""); ?> /> <label for="proxify_no">no</label><br />
													<input type="radio" id="proxify_yes" name="proxify" value="yes"<?php echo (($PROCESSED["proxify"] == 1) ? " checked=\"checked\"" : ""); ?> /> <label for="proxify_yes">yes</label><br />
												</div>
											</div>

											<div id="q2" class="wizard-question<?php echo ((in_array("q2", $ERRORSTR)) ? " display-error" : ""); ?>">
												<div style="font-size: 14px">Is the use of this resource required or optional by the learner?</div>
												<div style="padding-left: 65px">
													<input type="radio" id="required_no" name="required" value="no"<?php echo (((!isset($PROCESSED["required"])) || (!$PROCESSED["required"])) ? " checked=\"checked\"" : ""); ?> /> <label for="required_no">optional</label><br />
													<input type="radio" id="required_yes" name="required" value="yes"<?php echo (($PROCESSED["required"] == 1) ? " checked=\"checked\"" : ""); ?> /> <label for="required_yes">required</label><br />
												</div>
											</div>
										</div>

										<div id="step2" style="display: none">
											<div id="q4" class="wizard-question<?php echo ((in_array("q4", $ERRORSTR)) ? " display-error" : ""); ?>">
												<div style="font-size: 14px">Would you like to add timed release dates to this link?</div>
												<div style="padding-left: 65px">
													<input type="radio" id="timedrelease_no" name="timedrelease" value="no" onclick="timedRelease('none')"<?php echo (((!isset($_POST["timedrelease"])) || ($_POST["timedrelease"] == "no")) ? " checked=\"checked\"" : ""); ?> /> <label for="timedrelease_no">No, this link is accessible any time.</label><br />
													<input type="radio" id="timedrelease_yes" name="timedrelease" value="yes" onclick="timedRelease('block')"<?php echo (((isset($_POST["timedrelease"])) && ($_POST["timedrelease"] == "yes")) ? " checked=\"checked\"" : ""); ?> /> <label for="timedrelease_yes">Yes, this link has timed release information.</label><br />
												</div>

												<div id="timed-release-info" style="display: none">
													<br />
													By checking the box on the left, you will enable the ability to select release / revoke dates and times for this link.
													<br /><br />
													<table style="width: 100%" cellspacing="0" cellpadding="4" border="0" summary="Timed Release Information">
													<colgroup>
														<col style="width: 3%" />
														<col style="width: 30%" />
														<col style="width: 67%" />
													</colgroup>
													<?php echo generate_calendars("valid", "Accessible", true, false, ((isset($PROCESSED["release_date"])) ? $PROCESSED["release_date"] : 0), true, false, ((isset($PROCESSED["release_until"])) ? $PROCESSED["release_until"] : 0), true, true); ?>
													</table>
												</div>
											</div>
										</div>

										<div id="step3" style="display: none">
											<div id="q5" class="wizard-question<?php echo ((in_array("q5", $ERRORSTR)) ? " display-error" : ""); ?>">
												<div style="font-size: 14px">Please provide the full URL of the link:</div>
												<div style="padding-left: 65px; padding-right: 10px; padding-top: 10px">
													<label for="link" class="form-required">Link URL:</label> <span class="content-small"><strong>Example:</strong> http://meds.queensu.ca</span><br />
													<input type="text" id="link" name="link" value="<?php echo ((isset($PROCESSED["link"])) ? html_encode($PROCESSED["link"]) : "http://"); ?>" maxlength="500" style="width: 350px;" />
												</div>
											</div>
											<div id="q6" class="wizard-question<?php echo ((in_array("q6", $ERRORSTR)) ? " display-error" : ""); ?>">
												<div style="font-size: 14px">You can <span style="font-style: oblique">optionally</span> provide a different title for this link.</div>
												<div style="padding-left: 65px; padding-right: 10px; padding-top: 10px">
													<label for="link_title" class="form-nrequired">Link Title:</label> <span class="content-small"><strong>Example:</strong> Faculty of Health Sciences</span><br />
													<input type="text" id="link_title" name="link_title" value="<?php echo ((isset($PROCESSED["link_title"])) ? html_encode($PROCESSED["link_title"]) : ""); ?>" maxlength="128" style="width: 350px;" />
												</div>
											</div>
											<div id="q7" class="wizard-question<?php echo ((in_array("q7", $ERRORSTR)) ? " display-error" : ""); ?>">
												<div style="font-size: 14px">You <span style="font-style: oblique">must</span> provide a description for this link as well.</div>
												<div style="padding-left: 65px; padding-right: 10px; padding-top: 10px">
													<label for="link_notes" class="form-required">Link Description:</label><br />
													<textarea id="link_notes" name="link_notes" style="width: 350px; height: 75px"><?php echo ((isset($PROCESSED["link_notes"])) ? html_encode($PROCESSED["link_notes"]) : ""); ?></textarea>
												</div>
											</div>
										</div>
									</div>
									<div id="footer">
										<input type="button" class="button" value="Close" onclick="closeWizard()" style="float: left; margin: 4px 0px 4px 10px" />
										<input type="button" class="button" id="next-button" value="Next Step" onclick="nextStep()" style="float: right; margin: 4px 10px 4px 0px" />
										<input type="button" class="button" id="back-button" value="Previous Step" onclick="prevStep()" style="display: none; float: right; margin: 4px 10px 4px 0px" />
									</div>
									<div id="uploading-window">
										<div style="display: table; width: 485px; height: 555px; _position: relative; overflow: hidden">
											<div style=" _position: absolute; _top: 50%;display: table-cell; vertical-align: middle;">
												<div style="_position: relative; _top: -50%; width: 100%; text-align: center">
													<span style="color: #003366; font-size: 18px; font-weight: bold">
														<img src="<?php echo ENTRADA_URL; ?>/images/loading.gif" width="32" height="32" alt="Link Uploading" title="Please wait while this link is being added." style="vertical-align: middle" /> Please Wait: this link is being added.
													</span>
												</div>
											</div>
										</div>
									</div>
									</form>
								</div>
								<?php
							break;
						}
					break;
				}
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "The provided event identifier does not exist in this system.";

			echo display_error();

			application_log("error", "Link wizard was accessed without a valid event id.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "You must provide an event identifier when using the link wizard.";

		echo display_error();

		application_log("error", "Link wizard was accessed without any event id.");
	}
	?>
	</body>
	</html>
	<?php
}