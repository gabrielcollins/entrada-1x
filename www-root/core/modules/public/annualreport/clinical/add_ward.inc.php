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
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Andrew Dos-Santos <andrew.dos-santos@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/
if((!defined("PARENT_INCLUDED")) || (!defined("IN_ANNUAL_REPORT"))) {
	exit;
} else if((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL.((isset($_SERVER["REQUEST_URI"])) ? "?url=".rawurlencode(clean_input($_SERVER["REQUEST_URI"], array("nows", "url"))) : ""));
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('annualreport', 'update')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	// Meta information for this page.
	$PAGE_META["title"]			= "Add Ward Supervision";
	$PAGE_META["service"]			= "Ward Supervision portion of your annual report should be entered / located here.";
	$PAGE_META["keywords"]		= "";
	
	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/annualreport/research?section=add_ward", "title" => "Add Ward Supervision");
	
	// This grid should be expanded upon redirecting back to the clinical index.
	$_SESSION["clinical_expand_grid"] = "ward_grid";
	
	echo "<h1>Add Ward Supervision</h1>";
	// Error Checking
	switch($STEP) {
		case 2 :			
			/**
			 * Required field "service" / Service			 
			 */
			if((isset($_POST["service"])) && ($service = clean_input($_POST["service"], array("notags", "trim")))) {
				$PROCESSED["service"] = $service;
			} else {
				$ERROR++;
				$ERRORSTR[] = "The <strong>Service</strong> field is required.";
			}
			/**
			 * Required field "average_patients" / Average Patients
			 */
			if((isset($_POST["average_patients"])) && ($average_patients = clean_input($_POST["average_patients"], array("int")))) {
				$PROCESSED["average_patients"] = $average_patients;
			} else {
				$ERROR++;
				$ERRORSTR[] = "The <strong>Average Patients</strong> field is required.";
			}
			/**
			 * Required field "months" / Months
			 */
			if((isset($_POST["months"])) && ($months = clean_input($_POST["months"], array("int")))) {
				$PROCESSED["months"] = $months;
			} else {
				$ERROR++;
				$ERRORSTR[] = "The <strong>Months</strong> field is required.";
			}
			/**
			 * Required field "average_clerks" / Average Clerks
			 */
			if((isset($_POST["average_clerks"])) && ($average_clerks = clean_input($_POST["average_clerks"], array("int")))) {
				$PROCESSED["average_clerks"] = $average_clerks;
			} else {
			    if(trim($_POST["average_clerks"] === "0"))
				{
					$PROCESSED["average_clerks"] = trim($_POST["average_clerks"]);
				}
				else if(trim($_POST["average_clerks"]) == '')
				{
					$PROCESSED["average_clerks"] = "0";
				}
				else {
					$ERROR++;
					$ERRORSTR[] = "The <strong>Average Clerks</strong> field is required.";
				}
			}
			/**
			 * Required field "year_reported" / Year Reported.
			 */
			if((isset($_POST["year_reported"])) && ($year_reported = clean_input($_POST["year_reported"], array("int")))) {
				$PROCESSED["year_reported"] = $year_reported;
			} else {
				$ERROR++;
				$ERRORSTR[] = "The <strong>Year Reported</strong> field is required.";
			}
			
			if(isset($_POST["post_action"])) {
				switch($_POST["post_action"]) {					
					case "new" :
						$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "new";
					break;
					case "index" :
					default :
						$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "index";
					break;
				}
			} else {
				$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "index";
			}

			if(!$ERROR) {
				$PROCESSED["updated_date"]	= time();
				$PROCESSED["updated_by"]	= $_SESSION["details"]["id"];
				$PROCESSED["proxy_id"]		= $_SESSION[APPLICATION_IDENTIFIER]['tmp']['proxy_id'];				
				
				if($db->AutoExecute("ar_ward_supervision", $PROCESSED, "INSERT")) {
					$EVENT_ID = $db->Insert_Id();
						switch($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"]) {
							case "new" :
								$url 	= ENTRADA_URL."/annualreport/clinical?section=add_ward";
								$msg	= "You will now be redirected to add another new record; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
							break;
							case "index" :
							default :
								$url 	= ENTRADA_URL."/annualreport/clinical";
								$msg	= "You will now be redirected to the clinical page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
							break;
						}

						$SUCCESS++;
						$SUCCESSSTR[]  = "You have successfully added information pertaining to <strong>".html_encode($PROCESSED["service"])."</strong> to the system.<br /><br />".$msg;
						$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000);";

						application_log("success", "Ward Supervision [".$EVENT_ID."] added to the system.");					

				} else {
					$ERROR++;
					$ERRORSTR[] = "There was a problem inserting this record into the system. The MEdIT Unit was informed of this error; please try again later.";

					application_log("error", "There was an error inserting an Ward Supervision record. Database said: ".$db->ErrorMsg());
				}
			} else {
				$STEP = 1;
			}
		break;
		case 1 :
		default :
			continue;
		break;
	}

	// Display Content
	switch($STEP) {
		case 2 :
			if($SUCCESS) {
				echo display_success();
			}
			if($NOTICE) {
				echo display_notice();
			}
			if($ERROR) {
				echo display_error();
			}
		break;
		case 1 :
		default :
			if($ERROR) {
				echo display_error();
			}
			?>
			<form action="<?php echo ENTRADA_URL; ?>/annualreport/clinical?section=add_ward&amp;step=2" method="post">
			<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Adding Ward Supervision">
			<colgroup>
				<col style="width: 3%" />
				<col style="width: 20%" />
				<col style="width: 77%" />
			</colgroup>
			<tr>
				<td colspan="3"><h2>Details</h2></td>
			</tr>
			<tr>
				<td></td>
				<td><label for="service" class="form-required">Service</label></td>
				<td><input type="text" id="service" name="service" value="<?php echo html_encode($PROCESSED["service"]); ?>" maxlength="150" style="width: 95%" /></td>
			</tr>
			<tr>
				<td></td>
				<td style="vertical-align: top"><label for="average_patients" class="form-required">Average Patients</label></td>				
				<td><input type="text" id="average_patients" name="average_patients" value="<?php echo html_encode($PROCESSED["average_patients"]); ?>" maxlength="5" style="width: 40px" /></td>
			</tr>
			<tr>
				<td></td>
				<td style="vertical-align: top"><label for="months" class="form-required">Months</label></td>				
				<td><input type="text" id="months" name="months" value="<?php echo html_encode($PROCESSED["months"]); ?>" maxlength="2" style="width: 40px" /></td>
			</tr>
			<tr>
				<td></td>
				<td style="vertical-align: top"><label for="average_clerks" class="form-required">Average Clerks/Residents</label></td>				
				<td><input type="text" id="average_clerks" name="average_clerks" value="<?php echo html_encode($PROCESSED["average_clerks"]); ?>" maxlength="5" style="width: 40px" /></td>
			</tr>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<tr>
				<td></td>
				<td><label for="year_reported" class="form-required">Report Year</label></td>
				<td><select name="year_reported" id="year_reported" style="vertical-align: middle">
				<?php
					for($i=$AR_PAST_YEARS; $i<=$AR_FUTURE_YEARS; $i++)
					{
						if(isset($PROCESSED["year_reported"]) && $PROCESSED["year_reported"] != '')
						{
							$defaultYear = $PROCESSED["year_reported"];
						}
						else 
						{
							$defaultYear = $AR_CUR_YEAR;
						}
						echo "<option value=\"".$i."\"".(($defaultYear == $i) ? " selected=\"selected\"" : "").">".$i."</option>\n";
					}
					echo "</select>";
				?>
				</td>
			</tr>
			<tr>
				<td colspan="3" style="padding-top: 25px">
					<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td style="width: 25%; text-align: left">
							<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/annualreport/clinical'" />
						</td>
						<td style="width: 75%; text-align: right; vertical-align: middle">
							<span class="content-small">After saving:</span>
							<select id="post_action" name="post_action">							
							<option value="new"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "add") ? " selected=\"selected\"" : ""); ?>>Add More Clinical</option>
							<option value="index"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "index") ? " selected=\"selected\"" : ""); ?>>Return to Clinical list</option>
							</select>
							<input type="submit" class="button" value="Save" />
						</td>
					</tr>
					</table>
				</td>
			</tr>
			</table>
			</form>
			<br /><br />
			<?php
		break;
	}
}
?>