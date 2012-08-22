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
 * Loads the Learning Event quiz wizard when a teacher / director wants to
 * attach a quiz on the Manage Events > Content page.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @author Developer: Brandon Thorn <brandon.thorn@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 *
*/

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
require_once("library/Entrada/webconferencing/ConferenceFactory.inc.php");
ob_start("on_checkout");

if((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	echo "<div id=\"scripts-on-open\" style=\"display: none;\">\n";
	echo "alert('It appears as though your session has expired; you will now be taken back to the login page.');\n";
	echo "if(window.opener) {\n";
	echo "	window.opener.location = '".ENTRADA_URL.((isset($_SERVER["REQUEST_URI"])) ? "?url=".rawurlencode(clean_input($_SERVER["REQUEST_URI"], array("nows", "url"))) : "")."';\n";
	echo "	top.window.close();\n";
	echo "} else {\n";
	echo "	window.location = '".ENTRADA_URL.((isset($_SERVER["REQUEST_URI"])) ? "?url=".rawurlencode(clean_input($_SERVER["REQUEST_URI"], array("nows", "url"))) : "")."';\n";
	echo "}\n";
	echo "</div>\n";
	exit;
} else {
	$ACTION				= "add";
	$CONFERENCE_TYPE	= "event";
	$RECORD_ID			= 0;
	$CONFERENCE_ID			= 0;

	if (isset($_GET["action"])) {
		$ACTION	= trim($_GET["action"]);
	}

	if ((isset($_POST["step"])) && ($tmp_step = clean_input($_POST["step"], "int"))) {
		$STEP = $tmp_step;
	} else {
		$STEP = 0;
	}

	if ((isset($_GET["id"])) && ((int) trim($_GET["id"]))) {
		$RECORD_ID	= (int) trim($_GET["id"]);
	}

	if ((isset($_GET["cid"])) && ((int) trim($_GET["cid"]))) {
		$CONFERENCE_ID = (int) trim($_GET["cid"]);
	}
	
	/**
	 * Only option right now. If/when more get added to the system allow the wizard to make the option selectable. 
	 */
	$PROCESSED["conference_software"] = "bigbluebutton";
	$PROCESSED["csoftware_id"] = 1;
	
	$PROCESSED["attached_type"] = $CONFERENCE_TYPE;
	$PROCESSED["attached_id"] = $RECORD_ID;
	
	$modal_onload = array();
	?>
	<div id="uploading-window" style="width: 100%; height: 100%;">
		<div style="display: table; width: 100%; height: 100%; _position: relative; overflow: hidden">
			<div style=" _position: absolute; _top: 50%;display: table-cell; vertical-align: middle;">
				<div style="_position: relative; _top: -50%; width: 100%; text-align: center">
					<span style="color: #003366; font-size: 18px; font-weight: bold">
						<img src="<?php echo ENTRADA_URL; ?>/images/loading.gif" width="32" height="32" alt="File Saving" title="Please wait while changes are being saved." style="vertical-align: middle" /> Please Wait: changes are being saved.
					</span>
				</div>
			</div>
		</div>
	</div>
	<?php
	if ($CONFERENCE_TYPE == "event") {
		if ($RECORD_ID) {
			$query			= "	SELECT a.*, b.`organisation_id`
								FROM `events` AS a
								LEFT JOIN `courses` AS b
								ON b.`course_id` = a.`course_id`
								WHERE a.`event_id` = ".$db->qstr($RECORD_ID)."
								AND b.`course_active` = '1'";
			$event_record	= $db->GetRow($query);
			if($event_record) {
				$access_allowed = false;
				if (!$ENTRADA_ACL->amIAllowed(new EventContentResource($RECORD_ID, $event_record["course_id"], $event_record["organisation_id"]), "update")) {
					$query = "SELECT * FROM `events` WHERE `parent_id` = ".$db->qstr($RECORD_ID);
					if ($sessions = $db->GetAll($query)) {
						foreach ($sessions as $session) {
							if ($ENTRADA_ACL->amIAllowed(new EventContentResource($session["event_id"], $event_record["course_id"], $event_record["organisation_id"]), "update")) {
								$access_allowed = true;
							}
						}
					}
				} else {
					$access_allowed = true;
				}
				if (!$access_allowed) {
					$modal_onload[]	= "closeWizard()";

					$ERROR++;
					$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module. If you believe you are receiving this message in error please contact the MEdTech Unit at 613-533-6000 x74918 and we can assist you.";

					echo display_error();

					application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to the file wizard.");
				} else {
					$conference = false;
					switch($ACTION) {
						/**
						 * Edit and Create use the same code except that the edit preloads the $PROCESSED array with data and uses update instead of insert.
						 */
						case "edit" :
						if ($CONFERENCE_ID) {
							if($conference = ConferenceFactory::getConferenceById($CONFERENCE_ID)){
								$PROCESSED["conference_software"] = $conference->software_model;
							} else {
								add_error("No conference found with speficified ID. Unable to edit conference.");
								
							}
						} else {
							add_error("Invalid conference ID specified. Unable to edit conference.");
						}
						case "create" :
							switch($STEP) {
								case 4:
								case 3:
//									if (!$conference) {
//										$conference = ConferenceFactory::getConferenceModel($PROCESSED["conference_software"]);
//									}
//									if ($conference) {
//										try{
//											if (!$conference->processForm($_POST) ){
//												add_error("Invalid data provided. Please try again.");
//											}
//										}catch(Exception $e){
//											add_error($e->getMessage());
//										}										
//									} else {
//										add_error("Invalid conference software selected.");
//									}
								case 2:
									//return array("start" => $timestamp_start, "finish" => $timestamp_finish);
									$accessible = validate_calendars("accessible",false,false,true);
									$conf = validate_calendars("conference",true,false,true);
									if (isset($conf["start"]) && $conf["start"]) {
										$PROCESSED["conference_start"] = $conf["start"];
									} else {
										$PROCESSED["conference_start"] = 0;
										add_error("<strong>Conference Start</strong> is a required field.");
									}
									if (isset($accessible["start"]) && $accessible["start"]) {
										$PROCESSED["release_date"] = $accessible["start"];
									} else {
										$PROCESSED["release_date"] = 0;
									}
									if (isset($accessible["finish"]) && $accessible["finish"]) {
										$PROCESSED["release_until"] = $accessible["finish"];
									} else {
										$PROCESSED["release_until"] = 0;
									}																		
									
									/**
									 * Optional Field "Conference Duration": default is 6 hours
									 */
									if (isset($_POST["conference_duration"]) && $tmp_input = clean_input($_POST["conference_duration"],array("int"))) {
										$PROCESSED["conference_duration"] = $tmp_input;
									} else {
										$PROCESSED["conference_duration"] = 360;
									}
									
									$end = (int)$PROCESSED["conference_start"] + $PROCESSED["conference_duration"];
									
									if ($PROCESSED["release_date"] && $PROCESSED["release_date"] > $PROCESSED["conference_start"]) {
										add_error("The release start date must be before the start date.");
									}
									if ($PROCESSED["release_until"] && $PROCESSED["release_until"] < $end) {
										add_error("The release end date must be after the conference ends.");
									}
									
								case 1:
									/**
									 * Required Field "Conference Title"
									 */
									if (isset($_POST["conference_title"]) && $tmp_input = clean_input($_POST["conference_title"],array("trim","notags"))) {
										$PROCESSED["conference_title"] = $tmp_input;
									} elseif($STEP >= 1){
										add_error("<strong>Conference Title</strong> is a required field.");
									}
									/**
									 * Optional Field "Conference Description"
									 */
									if (isset($_POST["conference_description"]) && $tmp_input = clean_input($_POST["conference_description"],array("trim","notags"))) {
										$PROCESSED["conference_description"] = $tmp_input;
									} else {
										$PROCESSED["conference_description"] = "";
									}
								default:
									$PROCESSED["conference_type"] = $CONFERENCE_TYPE;
									$PROCESSED["conference_type_id"] = $RECORD_ID;									
								break;
							}
							if(!$ERROR){
								$STEP++;
							}
							// Display Create Step
							switch($STEP) {
								case 4 :																	
								case 3:
									if (!$conference && !$conference = ConferenceFactory::getConferenceModel($PROCESSED["conference_software"])) {
										add_error("Invalid conference software selected.");
									} else {
										/**
										 * If Step 3 has already been submitted and processed it will now be step 4. 
										 * Alternatively it may be step 3 but there is no additional form so the conference can be created/updated here.
										 */
										if ($STEP > 3 || !$conference->third_form) {											
											try{
												if (!$conference->processForm($_POST) ){
													add_error("Invalid data provided. Please try again.");
												}
											}catch(Exception $e){
												add_error($e->getMessage());
											}
											if (!$ERROR) {
												if ($ACTION == "create") {												
													try {
														if ($conference->createConference($PROCESSED)) {
															add_success("Successfully created conference.");
														} else {
															add_error("Unknown error occurred while creating conference. Please try again.");
														}												
													} catch (Exception $e) {
														add_error($e->getMessage());
													}
												} elseif ($ACTION == "edit") {
													try {
														if ($conference->updateConference($PROCESSED) ) {
															add_success("Successfully updated conference.");
														} else {
															add_error("Unknown error occurred while updating conference. Please try again.");
														}
													} catch (Exception $e) {
														add_error($e->getMessage());
													}
												}
											}
											?>
									<div class="modal-dialog" id="conference-wizard">
										<form id="wizard-form" target="upload-frame" action="#" method="post">
										<div id="wizard">
											<div id="header">
												<span class="content-heading" style="color: #FFFFFF">Conference Wizard</span> <span style="font-size: 11px; color: #FFFFFF"><strong><?php echo $ACTION == 'create'?'Creating':'Updating';?></strong> conference</span>
											</div>
											<div id="body">
												<h2>Step <?php echo $STEP;?>: <?php echo $ACTION == 'create'?'Creating':'Updating';?> Conference</h2>
												<?php
												if ($ERROR) {
													echo display_error();
												}

												if ($NOTICE) {
													echo display_notice();
												}
												
												if ($SUCCESS) {
													echo display_success();
												}
												?>
											</div>
											<div id="footer">
												<input type="hidden" name="go_back" id="go_back" value="0" />
												<input type="hidden" name="go_forward" id="go_forward" value="0" />
												<button id="close-button" onclick="closeWizard()">Close</button>
											</div>
										</div>
										</form>
									</div>	<?php											
										} else {
											/**
											 * If this is running it means the step is 3 (2 has been processed and 3 is being displayed for the first time or with an error)
											 * and that there is a third form to be displayed.
											 */
											?>
									<div class="modal-dialog" id="conference-wizard">
										<form id="wizard-form" target="upload-frame" action="<?php echo ENTRADA_URL; ?>/api/conference-wizard.api.php?type=<?php echo $CONFERENCE_TYPE; ?>&amp;action=<?php echo $ACTION;?>&amp;id=<?php echo $RECORD_ID; ?>&amp;cid=<?php echo $CONFERENCE_ID; ?>" method="post">
										<input type="hidden" name="step" value="<?php echo $STEP; ?>" />
										<?php
										foreach ($PROCESSED as $key => $value) {
											echo "<input type=\"hidden\" name=\"".html_encode($key)."\" value=\"".html_encode($value)."\" />\n";
										}
										?>
										<div id="wizard">
											<div id="header">
												<span class="content-heading" style="color: #FFFFFF">Conference Wizard</span> <span style="font-size: 11px; color: #FFFFFF"><strong><?php echo $ACTION == 'create'?'Creating':'Updating';?></strong> conference</span>
											</div>
											<div id="body">
												<h2>Step 3: Software Specific Fields</h2>
												<?php
												if ($ERROR) {
													echo display_error();
												}

												if ($NOTICE) {
													echo display_notice();
												}
												?>
											<?php
											$conference->printForm();
											?>
											</div>
											<div id="footer">
												<input type="hidden" name="go_back" id="go_back" value="0" />
												<input type="hidden" name="go_forward" id="go_forward" value="0" />
												<button id="close-button" onclick="closeWizard()">Close</button>
												<input type="button" id="next-button" name="next_button" onclick="conferenceNextStep()" value="Finish" />
												<input type="button" id="back-button" name="back_button" onclick="conferencePrevStep()" value="Previous Step" />
											</div>
										</div>
										</form>
									</div>													
											<?php
										}
									}
									break;
								case 2 :
									/**
									 * Unset variables set by this page or they get posted twice.
									 */
									unset($PROCESSED["accessible_start"]);
									unset($PROCESSED["accessible_start_date"]);
									unset($PROCESSED["accessible_start_hour"]);
									unset($PROCESSED["accessible_start_min"]);

									unset($PROCESSED["accessible_finish"]);
									unset($PROCESSED["accessible_finish_date"]);
									unset($PROCESSED["accessible_finish_hour"]);
									unset($PROCESSED["accessible_finish_min"]);
									?>
									<div class="modal-dialog" id="conference-wizard">
										<form id="wizard-form" target="upload-frame" action="<?php echo ENTRADA_URL; ?>/api/conference-wizard.api.php?type=<?php echo $CONFERENCE_TYPE; ?>&amp;action=<?php echo $ACTION;?>&amp;id=<?php echo $RECORD_ID; ?>&amp;cid=<?php echo $CONFERENCE_ID; ?>" method="post">
										<input type="hidden" name="step" value="<?php echo $STEP; ?>" />
										<?php
										foreach ($PROCESSED as $key => $value) {
											echo "<input type=\"hidden\" name=\"".html_encode($key)."\" value=\"".html_encode($value)."\" />\n";
										}
										?>
										<div id="wizard">
											<div id="header">
												<span class="content-heading" style="color: #FFFFFF">Conference Wizard</span> <span style="font-size: 11px; color: #FFFFFF"><strong><?php echo $ACTION == 'create'?'Creating':'Updating';?></strong> conference</span>
											</div>
											<div id="body">
												<h2>Step 2: Conference Availability</h2>
												<?php
												if ($ERROR) {
													echo display_error();
												}

												if ($NOTICE) {
													echo display_notice();
												}
												?>
												<div class="wizard-question">
													<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Start Information">
													<colgroup>
														<col style="width: 3%" />
														<col style="width: 15%" />
														<col style="width: 82%" />
													</colgroup>											
													<tr>
														<td colspan="3">When should this conference open for learners and moderators.</td>
													</tr>
													<?php
														echo generate_calendars("conference", "Conference", true, true, ((isset($PROCESSED["conference_start"])) ? $PROCESSED["conference_start"] : 0)); 
													?>
													</table>
												</div>
												<div class="wizard-question">													
													<div class="response-area">
														<label for="conference_duration" class="form-nrequired">Conference Duration</label><br />
														<input type="text" id="conference_duration" name="conference_duration" value="<?php echo (isset($PROCESSED["conference_duration"]) && $PROCESSED["conference_duration"])?clean_input($PROCESSED["conference_duration"], array("trim", "allowedtags", "encode")):360; ?>" maxlength="128" style="width: 96%;" />
													</div>
													<div>How long (in minutes) is this conference? The conference will automatically exit after this amount of time. If not specified 6 hours will be allotted.</div>
												</div>
												<div class="wizard-question">
													<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Time Release Information">
													<colgroup>
														<col style="width: 3%" />
														<col style="width: 15%" />
														<col style="width: 82%" />
													</colgroup>
													<tr>
														<td colspan="3"><h2>Time Release Options</h2></td>
													</tr>
													<tr>
														<td colspan="3">When should this conference become visible for learners? If no date is entered it will be visible immediately.</td>
													</tr>
													<?php
														echo generate_calendars("accessible", "Accessible", true, false, ((isset($PROCESSED["release_date"])) ? $PROCESSED["release_date"] : 0), true, false, ((isset($PROCESSED["release_until"])) ? $PROCESSED["release_until"] : 0), true, true); 
													?>
													</table>
												</div>
											</div>
											<div id="footer">
												<input type="hidden" name="go_back" id="go_back" value="0" />
												<input type="hidden" name="go_forward" id="go_forward" value="0" />
												<button id="close-button" onclick="closeWizard()">Close</button>
												<input type="button" id="next-button" name="next_button" onclick="conferenceNextStep()" value="Finish" />
												<input type="button" id="back-button" name="back_button" onclick="conferencePrevStep()" value="Previous Step" />
											</div>
										</div>
										</form>
									</div>							
									<?php
								break;
								case 1 :
								default :
									/**
									 * Load the rich text editor.
									 */
									load_rte();
									?>

									<div class="modal-dialog" id="conference-wizard">
										<form id="wizard-form" target="upload-frame" action="<?php echo ENTRADA_URL; ?>/api/conference-wizard.api.php?type=<?php echo $CONFERENCE_TYPE; ?>&amp;action=<?php echo $ACTION;?>&amp;id=<?php echo $RECORD_ID; ?>&amp;cid=<?php echo $CONFERENCE_ID; ?>" method="post">
										<input type="hidden" name="step" value="<?php echo $STEP; ?>" />
										<?php
										foreach ($PROCESSED as $key => $value) {
											echo "<input type=\"hidden\" name=\"".html_encode($key)."\" value=\"".html_encode($value)."\" />\n";
										}
										?>
										<div id="wizard">
											<div id="header">
												<span class="content-heading" style="color: #FFFFFF">Conference Wizard</span> <span style="font-size: 11px; color: #FFFFFF"><strong><?php echo $ACTION == 'create'?'Creating':'Updating';?></strong> conference</span>
											</div>
											<div id="body">
												<h2>Step 1: Basic Conference Information</h2>
												<?php
												if ($ERROR) {
													echo display_error();
												}
												if ($NOTICE) {
													echo display_notice();
												}
												?>												
												<div class="wizard-question">
													<div class="response-area">
														<label for="conference_title" class="form-required">Conference Title</label><br />
														<input type="text" id="conference_title" name="conference_title" value="<?php echo ((isset($PROCESSED["conference_title"])) ? html_encode($PROCESSED["conference_title"]) : ""); ?>" maxlength="128" style="width: 96%;" />
													</div>
												</div>
												<div class="wizard-question">
													<div class="response-area">
														<label for="conference_description" class="form-nrequired">Conference Description</label><br />
														<textarea id="conference_description" name="conference_description" style="width: 99%; height: 225px"><?php echo isset($PROCESSED["conference_description"])?clean_input($PROCESSED["conference_description"], array("trim", "allowedtags", "encode")):''; ?></textarea>
													</div>
												</div>
											</div>
											<div id="footer">
												<input type="hidden" name="go_forward" id="go_forward" value="0" />
												<button id="close-button" onclick="closeWizard()">Close</button>
												<input type="button" id="next-button" name="next_button" onclick="conferenceNextStep()" value="Next Step" />
											</div>
										</div>
										</form>
									</div>
									<?php
								break;
							}							
						break;
					}
					?>
					<div id="scripts-on-open" style="display: none;">
					<?php
						foreach ($modal_onload as $string) {
							echo $string.";\n";
						}
					?>
					function selectedTimeframe(timeframe) {
						switch (timeframe) {
							case 'pre' :
								$('accessible_start').checked	= false;
								$('accessible_finish').checked	= true;

								dateLock('accessible_start');
								dateLock('accessible_finish');

								$('accessible_start_date').value	= '';
								$('accessible_start_hour').value	= '00';
								$('accessible_start_min').value		= '00';

								$('accessible_finish_date').value	= '<?php echo date("Y-m-d", $event_record["event_finish"]); ?>';
								$('accessible_finish_hour').value	= '<?php echo date("G", $event_record["event_finish"]); ?>';
								$('accessible_finish_min').value	= '<?php echo (int) date("i", $event_record["event_finish"]); ?>';
							break;
							case 'during' :
								$('accessible_start').checked	= true;
								$('accessible_finish').checked	= true;

								dateLock('accessible_start');
								dateLock('accessible_finish');

								$('accessible_start_date').value	= '<?php echo date("Y-m-d", $event_record["event_start"]); ?>';
								$('accessible_start_hour').value	= '<?php echo date("G", $event_record["event_start"]); ?>';
								$('accessible_start_min').value		= '<?php echo (int) date("i", $event_record["event_start"]); ?>';

								$('accessible_finish_date').value	= '<?php echo date("Y-m-d", $event_record["event_finish"]); ?>';
								$('accessible_finish_hour').value	= '<?php echo date("G", $event_record["event_finish"]); ?>';
								$('accessible_finish_min').value	= '<?php echo (int) date("i", $event_record["event_finish"]); ?>';
							break;
							case 'post' :
								$('accessible_start').checked	= true;
								$('accessible_finish').checked	= false;

								dateLock('accessible_start');
								dateLock('accessible_finish');

								$('accessible_start_date').value	= '<?php echo date("Y-m-d", $event_record["event_start"]); ?>';
								$('accessible_start_hour').value	= '<?php echo date("G", $event_record["event_start"]); ?>';
								$('accessible_start_min').value		= '<?php echo (int) date("i", $event_record["event_start"]); ?>';

								$('accessible_finish_date').value	= '';
								$('accessible_finish_hour').value	= '00';
								$('accessible_finish_min').value	= '00';
							break;
							default :
								$('accessible_start').checked	= false;
								$('accessible_finish').checked	= false;

								dateLock('accessible_start');
								dateLock('accessible_finish');

								$('accessible_start_date').value	= '';
								$('accessible_start_hour').value	= '00';
								$('accessible_start_min').value		= '00';

								$('accessible_finish_date').value	= '';
								$('accessible_finish_hour').value	= '00';
								$('accessible_finish_min').value	= '00';
							break;
						}

						updateTime('accessible_start');
						updateTime('accessible_finish');
					}
					</div>
					<?php
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "The provided event identifier does not exist in this system.";

				echo display_error();

				application_log("error", "Conference wizard was accessed without a valid event id.");
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "You must provide an event identifier when using the conference wizard.";

			echo display_error();

			application_log("error", "Conference wizard was accessed without any event id.");
		}	
	} else {
			$ERROR++;
			$ERRORSTR[] = "Invalid conference type spefified.";

			echo display_error();

			application_log("error", "Conference type was invalid.");
	}
	
}