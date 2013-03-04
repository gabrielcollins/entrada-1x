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
 * This file is used to add AAMC Curriculum Inventory records to the
 * entrada.reports_aamc_ci table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <simpson@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_AAMC_CI"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("report", "read", false)) {
	$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/events?".replace_query(array("section" => "add")), "title" => "Adding Event");

	$PROCESSED["organisation_id"] = (int) $ENTRADA_USER->getActiveOrganisation();

	$org = Organisation::get($PROCESSED["organisation_id"]);

	echo "<h1>Create AAMC Curriculum Inventory Report</h1>\n";

	// Error Checking
	switch($STEP) {
		case 2 :
			/**
			 * Required field "report_title" / Report Title.
			 */
			if ((isset($_POST["report_title"])) && ($report_title = clean_input($_POST["report_title"], array("notags", "trim")))) {
				$query = "SELECT * FROM `reports_aamc_ci` WHERE `report_title` LIKE ".$db->qstr($report_title);
				$result = $db->GetRow($query);
				if (!$result) {
					$PROCESSED["report_title"] = $report_title;
				} else {
					add_error("The <strong>Report Title</strong> field that you entered is not unique.");
				}
			} else {
				add_error("The <strong>Report Title</strong> field is required.");
			}

			/**
			 * Non-Required field "report_description" / Report Description.
			 */
			if ((isset($_POST["report_description"])) && ($report_description = clean_input($_POST["report_description"], array("allowedtags", "trim")))) {
				$PROCESSED["report_description"] = $report_description;
			} else {
				$PROCESSED["report_description"] = "";
			}

			/**
			 * Non-Required field "report_supporting_link" / Supporting Link.
			 */
			if ((isset($_POST["report_supporting_link"])) && ($report_supporting_link = clean_input($_POST["report_supporting_link"], array("notags", "trim"))) && ($report_supporting_link != "http://")) {
				$PROCESSED["report_supporting_link"] = $report_supporting_link;
			} else {
				$PROCESSED["report_supporting_link"] = "";
			}

			/**
			 * Required field "report_date" / Event Date & Time Start.
			 */
			$report_date = validate_calendars("report", true, false, false);
			if ((isset($report_date["start"])) && ((int) $report_date["start"])) {
				$PROCESSED["report_date"] = (int) $report_date["start"];
			} else {
				$PROCESSED["report_date"] = 0;

				add_error("You must provide a reporting date for this report.");
			}

			/**
			 * Required field "event_start" / Event Date & Time Start.
			 */
			$period_date = validate_calendars("period", true, true, false);
			if ((isset($period_date["start"])) && ((int) $period_date["start"])) {
				$PROCESSED["report_start"] = (int) $period_date["start"];
			} else {
				$PROCESSED["report_start"] = 0;

				add_error("You must provide a reporting start date.");
			}
			if ((isset($period_date["finish"])) && ((int) $period_date["finish"])) {
				$PROCESSED["report_finish"] = (int) $period_date["finish"];
			} else {
				$PROCESSED["report_finish"] = 0;

				add_error("You must provide a reporting finish date.");
			}

			/**
			 * Non-required field "event_location" / Event Location
			 */
			if ((isset($_POST["event_location"])) && ($event_location = clean_input($_POST["event_location"], array("notags", "trim")))) {
				$PROCESSED["event_location"] = $event_location;
			} else {
				$PROCESSED["event_location"] = "";
			}

			if (!$ERROR) {
				$PROCESSED["updated_date"] = time();
				$PROCESSED["updated_by"] = $ENTRADA_USER->getID();

				if ($db->AutoExecute("reports_aamc_ci", $PROCESSED, "INSERT")) {
					if ($report_id = $db->Insert_Id()) {
						$url = ENTRADA_URL . "/admin/reports/aamc";

						add_success("You have successfully created <strong>".html_encode($PROCESSED["report_title"])."</strong>.<br /><br />You will now be redirected back to the report index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.");

						$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";

						application_log("success", "New AAMC curriculum inventory report [".$report_id."] created.");
					}
				} else {
					add_error("There was a problem creating this report at this time. The system administrator was informed of this error; please try again later.");

					application_log("error", "There was an error inserting an AAMC curriculum inventory report. Database said: ".$db->ErrorMsg());
				}
			}

			if ($ERROR) {
				$STEP = 1;
			}
		break;
		case 1 :
		default :
			$timestamp = time();
			$start_year = (date("Y") - ($timestamp < strtotime(ACADEMIC_YEAR_START_DATE) ? 1 : 0));

			$PROCESSED["report_date"] = $timestamp;


			$PROCESSED["report_start"]	= strtotime("September 1st ".$start_year." 00:00:00");
			$PROCESSED["report_finish"] = strtotime("August 31st ".($start_year + 1)." 23:59:59");

			$PROCESSED["report_title"] = $org->getAAMCInstitutionName() . " Curriculum " . date("Y", $PROCESSED["report_start"]) . "-" . date("Y", $PROCESSED["report_finish"]);
		break;
	}

	// Display Content
	switch($STEP) {
		case 2 :
			display_status_messages();
		break;
		case 1 :
		default :
			display_status_messages();

			load_rte("basic");

			?>
			<form action="<?php echo ENTRADA_RELATIVE; ?>/admin/reports/aamc?section=add&amp;step=2" method="post" id="addAAMCCiReport">
				<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Creating AAMC Curriclum Inventory Report">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 20%" />
						<col style="width: 77%" />
					</colgroup>
					<tfoot>
						<tr>
							<td colspan="3" style="padding-top: 25px">
								<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
									<tbody>
										<tr>
											<td style="width: 25%; text-align: left">
												<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_RELATIVE; ?>/admin/reports/aamc'" />
											</td>
											<td style="width: 75%; text-align: right; vertical-align: middle">
												<input type="submit" class="button" value="Create" />
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tfoot>
					<tbody>
						<tr>
							<td></td>
							<td style="vertical-align: top"><label for="report_title" class="form-required">Report Title</label></td>
							<td>
								<div id="course_id_path" class="content-small"><?php echo $org->getAAMCInstitutionName(); ?> &gt; <?php echo $org->getAAMCProgramName(); ?></div>
								<input type="text" id="report_title" name="report_title" value="<?php echo ((isset($PROCESSED["report_title"]) && $PROCESSED["report_title"]) ? html_encode($PROCESSED["report_title"]) : ""); ?>" maxlength="255" style="width: 99%; font-size: 150%; padding: 3px" />
							</td>
						</tr>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr>
							<td></td>
							<td style="vertical-align: top"><label for="report_description" class="form-nrequired">Report Description</label></td>
							<td>
								<textarea id="report_description" name="report_description" style="width: 100%; height: 150px" cols="70" rows="10"><?php echo html_encode(trim(strip_selected_tags($event_info["event_description"], array("font")))); ?></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr>
							<td></td>
							<td><label for="report_supporting_link" class="form-nrequired">Supporting Link</label></td>
							<td>
								<input type="text" id="report_supporting_link" name="report_supporting_link" value="<?php echo ((isset($PROCESSED["report_supporting_link"]) && $PROCESSED["report_supporting_link"]) ? html_encode($PROCESSED["report_supporting_link"]) : "http://"); ?>" maxlength="255" style="width: 99%;" />
							</td>
						</tr>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<?php echo generate_calendars("report", "Reporting Date", true, true, ((isset($PROCESSED["report_date"])) ? $PROCESSED["report_date"] : 0), false, false, 0, false, false, ""); ?>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<?php echo generate_calendars("period", "Reporting Period", true, true, ((isset($PROCESSED["report_start"])) ? $PROCESSED["report_start"] : 0), true, true, ((isset($PROCESSED["report_finish"])) ? $PROCESSED["report_finish"] : 0), false); ?>
					</tbody>
				</table>
			</form>
			<?php
		break;
	}
}
