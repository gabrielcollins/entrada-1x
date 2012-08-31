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
 * Loads the Course file wizard when a course director wants to add / edit
 * a file on the Manage Courses > Content page.
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
	$IDENTIFIER			= 0;
	$FILE_ID			= 0;
	$JS_INITSTEP		= 1;

	if(isset($_GET["action"])) {
		$ACTION	= trim($_GET["action"]);
	}

	if((isset($_GET["step"])) && ((int) trim($_GET["step"]))) {
		$STEP	= (int) trim($_GET["step"]);
	}

	if((isset($_GET["id"])) && ((int) trim($_GET["id"]))) {
		$IDENTIFIER	= (int) trim($_GET["id"]);
	}

	if((isset($_GET["fid"])) && ((int) trim($_GET["fid"]))) {
		$FILE_ID	= (int) trim($_GET["fid"]);
	}

	$PAGE_META["title"] = "File Wizard";

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
	if($IDENTIFIER) {
		$query	= "	SELECT * FROM `courses` 
					WHERE `course_id` = ".$db->qstr($IDENTIFIER)."
					AND `course_active` = '1'";
		$result	= $db->GetRow($query);
		if($result) {
			if(!$ENTRADA_ACL->amIAllowed(new CourseContentResource($IDENTIFIER, $result['organisation_id']), 'update')) {
				$ONLOAD[]	= "closeWizard()";

				$ERROR++;
				$ERRORSTR[]	= "Your MEdTech account does not have the permissions required to use this feature of this module. If you believe you are receiving this message in error please contact the MEdTech Unit at 613-533-6000 x74918 and we can assist you.";

				echo display_error();

				application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to the file wizard.");
			} else {
				switch($ACTION) {
					case "edit" :
						/**
						 * Edit file form.
						 */

						if($FILE_ID) {
							$query	= "SELECT * FROM `course_files` WHERE `course_id` = ".$db->qstr($IDENTIFIER)." AND `id` = ".$db->qstr($FILE_ID);
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

										$PROCESSED["course_id"] = $IDENTIFIER;

										/**
										 * Step 3 Error Checking
										 */
										if((isset($_POST["update_file"])) && ($_POST["update_file"] == "yes")) {
											if(isset($_FILES["filename"])) {
												switch($_FILES["filename"]["error"]) {
													case 0 :
														$PROCESSED["file_type"]		= trim($_FILES["filename"]["type"]);
														$PROCESSED["file_size"]		= (int) trim($_FILES["filename"]["size"]);
														$PROCESSED["file_name"]		= useable_filename(trim($_FILES["filename"]["name"]));
													break;
													case 1 :
													case 2 :
														$ERROR++;
														$ERRORSTR[]		= "q5";
														$JS_INITSTEP	= 3;
													break;
													case 3 :
														$ONLOAD[]		= "alert('The file that uploaded did not complete the upload process or was interupted. Please try again.')";

														$ERROR++;
														$ERRORSTR[]		= "q5";
														$JS_INITSTEP	= 3;
													break;
													case 4 :
														$ONLOAD[]		= "alert('You did not select a file on your computer to upload. Please select a local file.')";

														$ERROR++;
														$ERRORSTR[]		= "q5";
														$JS_INITSTEP	= 3;
													break;
													case 6 :
													case 7 :
														$ONLOAD[]		= "alert('Unable to store the new file on the server; the MEdTech Unit has been informed of this error, please try again later.')";

														$ERROR++;
														$ERRORSTR[]		= "q5";
														$JS_INITSTEP	= 3;

														application_log("error", "File upload error: ".(($_FILES["filename"]["error"] == 6) ? "Missing a temporary folder." : "Failed to write file to disk."));
													break;
													default :
														application_log("error", "Unrecognized file upload error number [".$_FILES["filename"]["error"]."].");
													break;
												}
											} else {
												$ONLOAD[]		= "alert('To upload a file to this course you must select a file to upload from your computer.')";

												$ERROR++;
												$ERRORSTR[]		= "q5";
												$JS_INITSTEP	= 3;
											}
										}

										if ((isset($_POST["file_title"])) && ($tmp_input = clean_input($_POST["file_title"], array("trim", "notags")))) {
											$PROCESSED["file_title"]	= $tmp_input;
										} else {
											$PROCESSED["file_title"]	= $PROCESSED["file_name"];
										}

										if((isset($_POST["file_notes"])) && ($tmp_input = clean_input($_POST["file_notes"], array("trim", "notags")))) {
											$PROCESSED["file_notes"]	= $tmp_input;
										} else {
											$ERROR++;
											$ERRORSTR[]		= "q7";
											$JS_INITSTEP	= 3;
										}

										/**
										 * Step 2 Error Checking
										 * Because this unsets the $ERRORSTR array, only do this if there is not already an error.
										 * PITA, I know.
										 */
										if(!$ERROR) {
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
													$PROCESSED["valid_from"]	= (int) $timed_release["start"];
												} else {
													$PROCESSED["valid_from"]	= 0;
												}

												if((isset($timed_release["finish"])) && ((int) $timed_release["finish"])) {
													$PROCESSED["valid_until"] = (int) $timed_release["finish"];
												} else {
													$PROCESSED["valid_until"] = 0;
												}
											} else {
												$PROCESSED["valid_from"]	= 0;
												$PROCESSED["valid_until"]	= 0;
											}
										}

										/**
										 * Step 1 Error Checking
										 */
										if((isset($_POST["file_category"])) && (@array_key_exists(trim($_POST["file_category"]), $RESOURCE_CATEGORIES["course"]))) {
											$PROCESSED["file_category"] = trim($_POST["file_category"]);
										} else {
											$ERROR++;
											$ERRORSTR[]		= "q1";
											$JS_INITSTEP	= 1;
										}

										if((isset($_POST["access_method"])) && ($_POST["access_method"] == 1)) {
											$PROCESSED["access_method"] = 1;
										} else {
											$PROCESSED["access_method"] = 0;
										}

										if((isset($_POST["required"])) && ($_POST["required"] == "yes")) {
											$PROCESSED["required"] = 1;
										} else {
											$PROCESSED["required"] = 0;
										}

										if((isset($_POST["timeframe"])) && (@array_key_exists(trim($_POST["timeframe"]), $RESOURCE_TIMEFRAMES["course"]))) {
											$PROCESSED["timeframe"] = trim($_POST["timeframe"]);
										} else {
											$ERROR++;
											$ERRORSTR[]		= "q3";
											$JS_INITSTEP	= 1;
										}

										$PROCESSED["updated_date"]	= time();
										$PROCESSED["updated_by"]	= $_SESSION["details"]["id"];

										if(!$ERROR) {
											if((isset($_POST["update_file"])) && ($_POST["update_file"] == "yes")) {
												$query	= "SELECT * FROM `course_files` WHERE `course_id`=".$db->qstr($IDENTIFIER)." AND `file_name`=".$db->qstr($PROCESSED["file_name"])." AND `id`<>".$db->qstr($FILE_ID);
												$result	= $db->GetRow($query);
												if($result) {
													$ONLOAD[]		= "alert('A file named ".addslashes($PROCESSED["file_name"])." already exists in this course.\\n\\nIf this is an updated version, please delete the old file before adding this one.')";

													$ERROR++;
													$ERRORSTR[]		= "q5";
													$JS_INITSTEP	= 3;
												} else {
													if($db->AutoExecute("course_files", $PROCESSED, "UPDATE", "id=".$db->qstr($FILE_ID)." AND course_id=".$db->qstr($IDENTIFIER))) {
														if((@is_dir(FILE_STORAGE_PATH)) && (@is_writable(FILE_STORAGE_PATH))) {
															if(@file_exists(FILE_STORAGE_PATH."/C".$FILE_ID)) {
																application_log("notice", "File ID [".$FILE_ID."] already existed and was overwritten with newer file.");
															}

															if(@move_uploaded_file($_FILES["filename"]["tmp_name"], FILE_STORAGE_PATH."/C".$FILE_ID)) {
																application_log("success", "File ID ".$FILE_ID." was successfully added to the database and filesystem for course [".$IDENTIFIER."].");
															} else {
																$ONLOAD[]		= "alert('The new file was not successfully saved. The MEdTech Unit has been informed of this error, please try again later.')";

																$ERROR++;
																$ERRORSTR[]		= "q5";
																$JS_INITSTEP	= 3;

																application_log("error", "The move_uploaded_file function failed to move temporary file over to final location.");
															}
														} else {
															$ONLOAD[]		= "alert('The new file was not successfully saved. The MEdTech Unit has been informed of this error, please try again later.')";

															$ERROR++;
															$ERRORSTR[]		= "q5";
															$JS_INITSTEP	= 3;

															application_log("error", "Either the FILE_STORAGE_PATH doesn't exist on the server or is not writable by PHP.");
														}
													} else {
														$ONLOAD[]		= "alert('The new file was not successfully saved. The MEdTech Unit has been informed of this error, please try again later.')";

														$ERROR++;
														$ERRORSTR[]		= "q5";
														$JS_INITSTEP	= 3;

														application_log("error", "Unable to insert the file into the database for course ID [".$IDENTIFIER."]. Database said: ".$db->ErrorMsg());
													}
												}
											} else {
												if($db->AutoExecute("course_files", $PROCESSED, "UPDATE", "id=".$db->qstr($FILE_ID)." AND course_id=".$db->qstr($IDENTIFIER))) {
													application_log("success", "File ID ".$FILE_ID." was successfully update to the database and filesystem for course [".$IDENTIFIER."].");
												} else {
													$ONLOAD[]		= "alert('This update was not successfully saved. The MEdTech Unit has been informed of this error, please try again later.')";

													$ERROR++;
													$ERRORSTR[]		= "q5";
													$JS_INITSTEP	= 3;

													application_log("error", "Unable to update this record in the database for course ID [".$IDENTIFIER."] and file ID [".$FILE_ID."]. Database said: ".$db->ErrorMsg());
												}

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

										if(((int) $PROCESSED["valid_from"]) || ((int) $PROCESSED["valid_until"])) {
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
												<span class="content-heading" style="color: #FFFFFF">File Wizard</span> <span style="font-size: 11px; color: #FFFFFF"><strong>Editing</strong> <?php echo html_encode($PROCESSED["file_title"]); ?></span>
											</div>
											<div id="body">
												<h2>File Updated Successfully</h2>
	
												<div class="display-success">
													You have successfully updated <strong><?php echo html_encode($PROCESSED["file_title"]); ?></strong> in this course.
												</div>
	
												To <strong>re-edit this file</strong> or <strong>close this window</strong> please use the buttons below.
											</div>
											<div id="footer">
												<input type="button" class="button" value="Close" onclick="closeWizard()" style="float: left; margin: 4px 0px 4px 10px" />
												<input type="button" class="button" value="Re-Edit File" onclick="window.location='<?php echo ENTRADA_URL; ?>/file-wizard-course.php?action=edit&amp;id=<?php echo $IDENTIFIER; ?>&amp;fid=<?php echo $FILE_ID; ?>'" style="float: right; margin: 4px 10px 4px 0px" />
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

										if((isset($_POST["update_file"])) && ($_POST["update_file"] == "yes")) {
											$ONLOAD[] = "updateFile('block')";
										} else {
											$ONLOAD[] = "updateFile('none')";
										}
										?>
										<div id="wizard">
											<form id="wizard-form" action="<?php echo ENTRADA_URL; ?>/file-wizard-course.php?action=edit&amp;id=<?php echo $IDENTIFIER; ?>&amp;fid=<?php echo $FILE_ID; ?>&amp;step=2" method="post" enctype="multipart/form-data" style="display: inline">
											<input type="hidden" name="MAX_UPLOAD_FILESIZE" value="<?php echo MAX_UPLOAD_FILESIZE; ?>" />
											<div id="header">
												<span class="content-heading" style="color: #FFFFFF">File Wizard</span> <span style="font-size: 11px; color: #FFFFFF"><strong>Editing</strong> <?php echo html_encode($PROCESSED["file_title"]); ?></span>
											</div>
											<div id="body">
												<h2 id="step-title"></h2>
												<div id="step1" style="display: none">
													<div id="q1" class="wizard-question<?php echo ((in_array("q1", $ERRORSTR)) ? " display-error" : ""); ?>">
														<div style="font-size: 14px">What type of file is this document?</div>
														<div style="padding-left: 65px">
															<?php
															if(@count($RESOURCE_CATEGORIES["course"])) {
																foreach($RESOURCE_CATEGORIES["course"] as $key => $value) {
																	echo "<input type=\"radio\" id=\"file_category_".$key."\" name=\"file_category\" value=\"".$key."\" style=\"vertical-align: middle\"".((isset($PROCESSED["file_category"])) ? (($PROCESSED["file_category"] == $key) ? " checked=\"checked\"" : "") : (($key == "other") ? " checked=\"checked\"" : ""))." /> <label for=\"file_category_".$key."\">".$value."</label><br />";
																}
															}
															?>
														</div>
													</div>

													<div id="q1b" class="wizard-question<?php echo ((in_array("q1b", $ERRORSTR)) ? " display-error" : ""); ?>">
														<div style="font-size: 14px">How do you want people to view this file?</div>
														<div style="padding-left: 65px">
															<input type="radio" id="access_method_0" name="access_method" value="0" style="vertical-align: middle"<?php echo (((!isset($PROCESSED["access_method"])) || ((isset($PROCESSED["access_method"])) && (!(int) $PROCESSED["access_method"]))) ? " checked=\"checked\"" : ""); ?> /> <label for="access_method_0">Download it to their computer first, then open it.</label><br />
															<input type="radio" id="access_method_1" name="access_method" value="1" style="vertical-align: middle"<?php echo (((isset($PROCESSED["access_method"])) && ((int) $PROCESSED["access_method"])) ? " checked=\"checked\"" : ""); ?> /> <label for="access_method_1">Attempt to view it directly in the web-browser.</label><br />
														</div>
													</div>

													<div id="q2" class="wizard-question<?php echo ((in_array("q2", $ERRORSTR)) ? " display-error" : ""); ?>">
														<div style="font-size: 14px">Is the use of this resource required or optional by the learner?</div>
														<div style="padding-left: 65px">
															<input type="radio" id="required_no" name="required" value="no"<?php echo (((!isset($PROCESSED["required"])) || (!$PROCESSED["required"])) ? " checked=\"checked\"" : ""); ?> /> <label for="required_no">optional</label><br />
															<input type="radio" id="required_yes" name="required" value="yes"<?php echo (($PROCESSED["required"] == 1) ? " checked=\"checked\"" : ""); ?> /> <label for="required_yes">required</label><br />
														</div>
													</div>

													<div id="q3" class="wizard-question<?php echo ((in_array("q3", $ERRORSTR)) ? " display-error" : ""); ?>">
														<div style="font-size: 14px">When should this resource be used by the learner?</div>
														<div style="padding-left: 65px">
															<?php
															if(@count($RESOURCE_TIMEFRAMES["course"])) {
																foreach($RESOURCE_TIMEFRAMES["course"] as $key => $value) {
																	echo "<input type=\"radio\" id=\"timeframe_".$key."\" name=\"timeframe\" value=\"".$key."\" style=\"vertical-align: middle\"".((isset($PROCESSED["timeframe"])) ? (($PROCESSED["timeframe"] == $key) ? " checked=\"checked\"" : "") : (($key == "none") ? " checked=\"checked\"" : ""))." /> <label for=\"timeframe_".$key."\">".$value."</label><br />";
																}
															}
															?>
														</div>
													</div>
												</div>

												<div id="step2" style="display: none">
													<div id="q4" class="wizard-question<?php echo ((in_array("q4", $ERRORSTR)) ? " display-error" : ""); ?>">
														<div style="font-size: 14px">Would you like to add timed release dates to this file?</div>
														<div style="padding-left: 65px">
															<input type="radio" id="timedrelease_no" name="timedrelease" value="no" onclick="timedRelease('none')"<?php echo ((((!isset($_POST["timedrelease"])) || ($_POST["timedrelease"] == "no")) && ((!isset($show_timed_release)) || (!$show_timed_release))) ? " checked=\"checked\"" : ""); ?> /> <label for="timedrelease_no">No, this file is accessible any time.</label><br />
															<input type="radio" id="timedrelease_yes" name="timedrelease" value="yes" onclick="timedRelease('block')"<?php echo ((((isset($_POST["timedrelease"])) && ($_POST["timedrelease"] == "yes")) || ((isset($show_timed_release)) && ($show_timed_release))) ? " checked=\"checked\"" : ""); ?> /> <label for="timedrelease_yes">Yes, this file has timed release information.</label><br />
														</div>

														<div id="timed-release-info" style="display: none">
															<br />
															By checking the box on the left, you will enable the ability to select release / revoke dates and times for this file.
															<br /><br />
															<table style="width: 100%" cellspacing="0" cellpadding="4" border="0" summary="Timed Release Information">
															<colgroup>
																<col style="width: 3%" />
																<col style="width: 30%" />
																<col style="width: 67%" />
															</colgroup>
															<?php echo generate_calendars("valid", "Accessible", true, false, ((isset($PROCESSED["valid_from"])) ? $PROCESSED["valid_from"] : 0), true, false, ((isset($PROCESSED["valid_until"])) ? $PROCESSED["valid_until"] : 0), true); ?>
															</table>
														</div>
													</div>
												</div>

												<div id="step3" style="display: none">
													<div id="q5" class="wizard-question<?php echo ((in_array("q5", $ERRORSTR)) ? " display-error" : ""); ?>">
														<div style="font-size: 14px">Would you like to replace the current file with a new one?</div>
														<div style="padding-left: 65px">
															<input type="radio" id="update_file_no" name="update_file" value="no" onclick="updateFile('none')"<?php echo (((!isset($_POST["update_file"])) || ($_POST["update_file"] == "no")) ? " checked=\"checked\"" : ""); ?> /> <label for="update_file_no">No, I do not wish to <span style="font-style: oblique">replace</span> current file.</label><br />
															<input type="radio" id="update_file_yes" name="update_file" value="yes" onclick="updateFile('block')"<?php echo (((isset($_POST["update_file"])) && ($_POST["update_file"] == "yes")) ? " checked=\"checked\"" : ""); ?> /> <label for="update_file_yes">Yes, I would like to <span style="font-style: oblique">replace</span> the existing file.</label><br />
														</div>
														<div id="upload-new-file" style="display: none">
															<br />
															<div style="font-size: 14px">Please select the new file to upload from your computer:</div>
															<div style="padding-left: 65px; padding-right: 10px; padding-top: 10px">
																<input type="file" id="filename" name="filename" value="" size="25" onchange="grabFilename()" /><br />
															</div>
														</div>
													</div>
													<div id="q6" class="wizard-question<?php echo ((in_array("q6", $ERRORSTR)) ? " display-error" : ""); ?>">
														<div style="font-size: 14px">You can <span style="font-style: oblique">optionally</span> provide a different title for this file.</div>
														<div style="padding-left: 65px; padding-right: 10px; padding-top: 10px">
															<label for="file_title" class="form-required">File Title:</label> <span class="content-small"><strong>Example:</strong> Video Of Procedure 1</span><br />
															<input type="text" id="file_title" name="file_title" value="<?php echo ((isset($PROCESSED["file_title"])) ? html_encode($PROCESSED["file_title"]) : ""); ?>" maxlength="128" style="width: 350px;" />
														</div>
													</div>
													<div id="q7" class="wizard-question<?php echo ((in_array("q7", $ERRORSTR)) ? " display-error" : ""); ?>">
														<div style="font-size: 14px">You <span style="font-style: oblique">must</span> provide a description for this file as well.</div>
														<div style="padding-left: 65px; padding-right: 10px; padding-top: 10px">
															<label for="file_notes" class="form-required">File Description:</label><br />
															<textarea id="file_notes" name="file_notes" style="width: 350px; height: 75px"><?php echo ((isset($PROCESSED["file_notes"])) ? html_encode($PROCESSED["file_notes"]) : ""); ?></textarea>
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
																<img src="<?php echo ENTRADA_URL; ?>/images/loading.gif" width="32" height="32" alt="File Saving" title="Please wait while changes are being saved." style="vertical-align: middle" /> Please Wait: changes are being saved.
															</span>
															<br /><br />
															This can take time depending on your connection speed and the filesize.
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
								$ERRORSTR[] = "The provided file identifier does not exist in the provided course.";

								echo display_error();

								application_log("error", "File wizard was accessed with a file id that was not found in the database.");
							}
						} else {
							$ERROR++;
							$ERRORSTR[] = "You must provide a file identifier when using the file wizard.";

							echo display_error();

							application_log("error", "File wizard was accessed without any file id.");
						}
					break;
					case "add" :
					default :
						/**
						 * Add file form.
						 */

						// Error Checking
						switch($STEP) {
							case 2 :
								/**
								 * In this error checking we are working backwards along the internal javascript
								 * steps timeline. This is so the JS_INITSTEP variable is set to the lowest page
								 * number that contains errors.
								 */

								$PROCESSED["course_id"] = $IDENTIFIER;

								/**
								 * Step 3 Error Checking
								 */
								if(isset($_FILES["filename"])) {
									switch($_FILES["filename"]["error"]) {
										case 0 :
											$PROCESSED["file_type"]		= trim($_FILES["filename"]["type"]);
											$PROCESSED["file_size"]		= (int) trim($_FILES["filename"]["size"]);
											$PROCESSED["file_name"]		= useable_filename(trim($_FILES["filename"]["name"]));

											if((isset($_POST["file_title"])) && (trim($_POST["file_title"]))) {
												$PROCESSED["file_title"]	= trim($_POST["file_title"]);
											} else {
												$PROCESSED["file_title"]	= $PROCESSED["file_name"];
											}
										break;
										case 1 :
										case 2 :
											$ERROR++;
											$ERRORSTR[]		= "q5";
											$JS_INITSTEP	= 3;
										break;
										case 3 :
											$ONLOAD[]		= "alert('The file that uploaded did not complete the upload process or was interupted. Please try again.')";

											$ERROR++;
											$ERRORSTR[]		= "q5";
											$JS_INITSTEP	= 3;
										break;
										case 4 :
											$ONLOAD[]		= "alert('You did not select a file on your computer to upload. Please select a local file.')";

											$ERROR++;
											$ERRORSTR[]		= "q5";
											$JS_INITSTEP	= 3;
										break;
										case 6 :
										case 7 :
											$ONLOAD[]		= "alert('Unable to store the new file on the server; the MEdTech Unit has been informed of this error, please try again later.')";

											$ERROR++;
											$ERRORSTR[]		= "q5";
											$JS_INITSTEP	= 3;

											application_log("error", "File upload error: ".(($_FILES["filename"]["error"] == 6) ? "Missing a temporary folder." : "Failed to write file to disk."));
										break;
										default :
											application_log("error", "Unrecognized file upload error number [".$_FILES["filename"]["error"]."].");
										break;
									}
								} else {
									$ONLOAD[]		= "alert('To upload a file to this course you must select a file to upload from your computer.')";

									$ERROR++;
									$ERRORSTR[]		= "q5";
									$JS_INITSTEP	= 3;
								}

								if((isset($_POST["file_notes"])) && (trim($_POST["file_notes"]))) {
									$PROCESSED["file_notes"]	= trim($_POST["file_notes"]);
								} else {
									$ERROR++;
									$ERRORSTR[]		= "q7";
									$JS_INITSTEP	= 3;
								}

								/**
								 * Step 2 Error Checking
								 * Because this unsets the $ERRORSTR array, only do this if there is not already an error.
								 * PITA, I know.
								 */
								if(!$ERROR) {
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
											$PROCESSED["valid_from"]	= (int) $timed_release["start"];
										}

										if((isset($timed_release["finish"])) && ((int) $timed_release["finish"])) {
											$PROCESSED["valid_until"]	= (int) $timed_release["finish"];
										}
									}
								}

								/**
								 * Step 1 Error Checking
								 */
								if((isset($_POST["file_category"])) && (@array_key_exists(trim($_POST["file_category"]), $RESOURCE_CATEGORIES["course"]))) {
									$PROCESSED["file_category"] = trim($_POST["file_category"]);
								} else {
									$ERROR++;
									$ERRORSTR[]		= "q1";
									$JS_INITSTEP	= 1;
								}

								if((isset($_POST["access_method"])) && ($_POST["access_method"] == 1)) {
									$PROCESSED["access_method"] = 1;
								} else {
									$PROCESSED["access_method"] = 0;
								}

								if((isset($_POST["required"])) && ($_POST["required"] == "yes")) {
									$PROCESSED["required"] = 1;
								} else {
									$PROCESSED["required"] = 0;
								}

								if((isset($_POST["timeframe"])) && (@array_key_exists(trim($_POST["timeframe"]), $RESOURCE_TIMEFRAMES["course"]))) {
									$PROCESSED["timeframe"] = trim($_POST["timeframe"]);
								} else {
									$ERROR++;
									$ERRORSTR[]		= "q3";
									$JS_INITSTEP	= 1;
								}

								$PROCESSED["updated_date"]	= time();
								$PROCESSED["updated_by"]	= $_SESSION["details"]["id"];

								if(!$ERROR) {
									$query	= "SELECT * FROM `course_files` WHERE `course_id`=".$db->qstr($IDENTIFIER)." AND `file_name`=".$db->qstr($PROCESSED["file_name"]);
									$result	= $db->GetRow($query);
									if($result) {
										$ONLOAD[]		= "alert('A file named ".addslashes($PROCESSED["file_name"])." already exists in this teaching course.\\n\\nIf this is an updated version, please delete the old file before adding this one.')";

										$ERROR++;
										$ERRORSTR[]		= "q5";
										$JS_INITSTEP	= 3;
									} else {
										if(($db->AutoExecute("course_files", $PROCESSED, "INSERT")) && ($FILE_ID = $db->Insert_Id())) {
											if((@is_dir(FILE_STORAGE_PATH)) && (@is_writable(FILE_STORAGE_PATH))) {
												if(@file_exists(FILE_STORAGE_PATH."/C".$FILE_ID)) {
													application_log("notice", "File ID [".$FILE_ID."] already existed and was overwritten with newer file.");
												}

												if(@move_uploaded_file($_FILES["filename"]["tmp_name"], FILE_STORAGE_PATH."/C".$FILE_ID)) {
													application_log("success", "File ID ".$FILE_ID." was successfully added to the database and filesystem for course [".$IDENTIFIER."].");
												} else {
													$ONLOAD[]		= "alert('The new file was not successfully saved. The MEdTech Unit has been informed of this error, please try again later.')";

													$ERROR++;
													$ERRORSTR[]		= "q5";
													$JS_INITSTEP	= 3;

													application_log("error", "The move_uploaded_file function failed to move temporary file over to final location.");
												}
											} else {
												$ONLOAD[]		= "alert('The new file was not successfully saved. The MEdTech Unit has been informed of this error, please try again later.')";

												$ERROR++;
												$ERRORSTR[]		= "q5";
												$JS_INITSTEP	= 3;

												application_log("error", "Either the FILE_STORAGE_PATH doesn't exist on the server or is not writable by PHP.");
											}
										} else {
											$ONLOAD[]		= "alert('The new file was not successfully saved. The MEdTech Unit has been informed of this error, please try again later.')";

											$ERROR++;
											$ERRORSTR[]		= "q5";
											$JS_INITSTEP	= 3;

											application_log("error", "Unable to insert the file into the database for course ID [".$IDENTIFIER."]. Database said: ".$db->ErrorMsg());
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
										<span class="content-heading" style="color: #FFFFFF">File Wizard</span> <span style="font-size: 11px; color: #FFFFFF"><strong>Adding</strong> new course file</span>
									</div>
									<div id="body">
										<h2>File Added Successfully</h2>
	
										<div class="display-success">
											You have successfully added <strong><?php echo html_encode($PROCESSED["file_title"]); ?></strong> to this course.
										</div>
	
										To <strong>add another file</strong> or <strong>close this window</strong> please use the buttons below.
									</div>
									<div id="footer">
										<input type="button" class="button" value="Close" onclick="closeWizard()" style="float: left; margin: 4px 0px 4px 10px"/>
										<input type="button" class="button" value="Add Another File" onclick="window.location='<?php echo ENTRADA_URL; ?>/file-wizard-course.php?id=<?php echo $IDENTIFIER; ?>&amp;action=add'" style="float: right; margin: 4px 10px 4px 0px" />
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
									<form id="wizard-form" action="<?php echo ENTRADA_URL; ?>/file-wizard-course.php?action=add&amp;id=<?php echo $IDENTIFIER; ?>&amp;step=2" method="post" enctype="multipart/form-data" style="display: inline">
									<input type="hidden" name="MAX_UPLOAD_FILESIZE" value="<?php echo MAX_UPLOAD_FILESIZE; ?>" />
									<div id="header">
										<span class="content-heading" style="color: #FFFFFF">File Wizard</span> <span style="font-size: 11px; color: #FFFFFF"><strong>Adding</strong> new course file</span>
									</div>
									<div id="body">
										<h2 id="step-title"></h2>
										<div id="step1" style="display: none">
											<div id="q1" class="wizard-question<?php echo ((in_array("q1", $ERRORSTR)) ? " display-error" : ""); ?>">
												<div style="font-size: 14px">What type of file are you adding?</div>
												<div style="padding-left: 65px">
													<?php
													if(@count($RESOURCE_CATEGORIES["course"])) {
														foreach($RESOURCE_CATEGORIES["course"] as $key => $value) {
															echo "<input type=\"radio\" id=\"file_category_".$key."\" name=\"file_category\" value=\"".$key."\" style=\"vertical-align: middle\"".((isset($PROCESSED["file_category"])) ? (($PROCESSED["file_category"] == $key) ? " checked=\"checked\"" : "") : (($key == "other") ? " checked=\"checked\"" : ""))." /> <label for=\"file_category_".$key."\">".$value."</label><br />";
														}
													}
													?>
												</div>
											</div>

											<div id="q1b" class="wizard-question<?php echo ((in_array("q1b", $ERRORSTR)) ? " display-error" : ""); ?>">
												<div style="font-size: 14px">How do you want people to view this file?</div>
												<div style="padding-left: 65px">
													<input type="radio" id="access_method_0" name="access_method" value="0" style="vertical-align: middle"<?php echo (((!isset($PROCESSED["access_method"])) || ((isset($PROCESSED["access_method"])) && (!(int) $PROCESSED["access_method"]))) ? " checked=\"checked\"" : ""); ?> /> <label for="access_method_0">Download it to their computer first, then open it.</label><br />
													<input type="radio" id="access_method_1" name="access_method" value="1" style="vertical-align: middle"<?php echo (((isset($PROCESSED["access_method"])) && ((int) $PROCESSED["access_method"])) ? " checked=\"checked\"" : ""); ?> /> <label for="access_method_1">Attempt to view it directly in the web-browser.</label><br />
												</div>
											</div>

											<div id="q2" class="wizard-question<?php echo ((in_array("q2", $ERRORSTR)) ? " display-error" : ""); ?>">
												<div style="font-size: 14px">Is the use of this resource required or optional by the learner?</div>
												<div style="padding-left: 65px">
													<input type="radio" id="required_no" name="required" value="no"<?php echo (((!isset($PROCESSED["required"])) || (!$PROCESSED["required"])) ? " checked=\"checked\"" : ""); ?> /> <label for="required_no">optional</label><br />
													<input type="radio" id="required_yes" name="required" value="yes"<?php echo (($PROCESSED["required"] == 1) ? " checked=\"checked\"" : ""); ?> /> <label for="required_yes">required</label><br />
												</div>
											</div>

											<div id="q3" class="wizard-question<?php echo ((in_array("q3", $ERRORSTR)) ? " display-error" : ""); ?>">
												<div style="font-size: 14px">When should this resource be used by the learner?</div>
												<div style="padding-left: 65px">
													<?php
													if(@count($RESOURCE_TIMEFRAMES["course"])) {
														foreach($RESOURCE_TIMEFRAMES["course"] as $key => $value) {
															echo "<input type=\"radio\" id=\"timeframe_".$key."\" name=\"timeframe\" value=\"".$key."\" style=\"vertical-align: middle\"".((isset($PROCESSED["timeframe"])) ? (($PROCESSED["timeframe"] == $key) ? " checked=\"checked\"" : "") : (($key == "none") ? " checked=\"checked\"" : ""))." /> <label for=\"timeframe_".$key."\">".$value."</label><br />";
														}
													}
													?>
												</div>
											</div>
										</div>

										<div id="step2" style="display: none">
											<div id="q4" class="wizard-question<?php echo ((in_array("q4", $ERRORSTR)) ? " display-error" : ""); ?>">
												<div style="font-size: 14px">Would you like to add timed release dates to this file?</div>
												<div style="padding-left: 65px">
													<input type="radio" id="timedrelease_no" name="timedrelease" value="no" onclick="timedRelease('none')"<?php echo (((!isset($_POST["timedrelease"])) || ($_POST["timedrelease"] == "no")) ? " checked=\"checked\"" : ""); ?> /> <label for="timedrelease_no">No, this file is accessible any time.</label><br />
													<input type="radio" id="timedrelease_yes" name="timedrelease" value="yes" onclick="timedRelease('block')"<?php echo (((isset($_POST["timedrelease"])) && ($_POST["timedrelease"] == "yes")) ? " checked=\"checked\"" : ""); ?> /> <label for="timedrelease_yes">Yes, this file has timed release information.</label><br />
												</div>

												<div id="timed-release-info" style="display: none">
													<br />
													By checking the box on the left, you will enable the ability to select release / revoke dates and times for this file.
													<br /><br />
													<table style="width: 100%" cellspacing="0" cellpadding="4" border="0" summary="Timed Release Information">
													<colgroup>
														<col style="width: 3%" />
														<col style="width: 30%" />
														<col style="width: 67%" />
													</colgroup>
													<?php echo generate_calendars("valid", "Accessible", true, false, ((isset($PROCESSED["valid_from"])) ? $PROCESSED["valid_from"] : 0), true, false, ((isset($PROCESSED["valid_until"])) ? $PROCESSED["valid_until"] : 0), true); ?>
													</table>
												</div>
											</div>
										</div>

										<div id="step3" style="display: none">
											<div id="q5" class="wizard-question<?php echo ((in_array("q5", $ERRORSTR)) ? " display-error" : ""); ?>">
												<div style="font-size: 14px">Please select the file to upload from your computer:</div>
												<div style="padding-left: 65px; padding-right: 10px; padding-top: 10px">
													<input type="file" id="filename" name="filename" value="" size="25" onchange="grabFilename()" /><br /><br />
													<?php
													if((isset($PROCESSED["file_name"])) && (!in_array("q5", $ERRORSTR))) {
														echo "<div class=\"display-notice\" style=\"margin-bottom: 0px\">Since there was an error in your previous request, you will need to re-select the local file from your computer in order to upload it. We apologize for the inconvenience; however, this is a security precaution.</div>";
													} else {
														echo "<span class=\"content-small\"><strong>Notice:</strong> The maximum filesize of this file must be less than ".readable_size(MAX_UPLOAD_FILESIZE).". If this file is larger than ".readable_size(MAX_UPLOAD_FILESIZE).", you will need to either compress it or split the file up into smaller files, otherwise the upload will fail.</span>";
													}
													?>
												</div>
											</div>
											<div id="q6" class="wizard-question<?php echo ((in_array("q6", $ERRORSTR)) ? " display-error" : ""); ?>">
												<div style="font-size: 14px">You can <span style="font-style: oblique">optionally</span> provide a different title for this file.</div>
												<div style="padding-left: 65px; padding-right: 10px; padding-top: 10px">
													<label for="file_title" class="form-required">File Title:</label> <span class="content-small"><strong>Example:</strong> Video Of Procedure 1</span><br />
													<input type="text" id="file_title" name="file_title" value="<?php echo ((isset($PROCESSED["file_title"])) ? html_encode($PROCESSED["file_title"]) : ""); ?>" maxlength="128" style="width: 350px;" />
												</div>
											</div>
											<div id="q7" class="wizard-question<?php echo ((in_array("q7", $ERRORSTR)) ? " display-error" : ""); ?>">
												<div style="font-size: 14px">You <span style="font-style: oblique">must</span> provide a description for this file as well.</div>
												<div style="padding-left: 65px; padding-right: 10px; padding-top: 10px">
													<label for="file_notes" class="form-required">File Description:</label><br />
													<textarea id="file_notes" name="file_notes" style="width: 350px; height: 75px"><?php echo ((isset($PROCESSED["file_notes"])) ? html_encode($PROCESSED["file_notes"]) : ""); ?></textarea>
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
														<img src="<?php echo ENTRADA_URL; ?>/images/loading.gif" width="32" height="32" alt="File Uploading" title="Please wait while this file is being uploaded." style="vertical-align: middle" /> Please Wait: this file is being uploaded.
													</span>
													<br /><br />
													This can take time depending on your connection speed and the filesize.
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
			$ERRORSTR[] = "The provided course identifier does not exist in this system.";

			echo display_error();

			application_log("error", "File wizard was accessed without a valid course id.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "You must provide an course identifier when using the file wizard.";

		echo display_error();

		application_log("error", "File wizard was accessed without any course id.");
	}
	?>
	</body>
	</html>
	<?php
}