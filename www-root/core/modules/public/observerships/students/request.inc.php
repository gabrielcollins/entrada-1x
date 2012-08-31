<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * This file gives Entrada users the ability to update their user profile.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 * @version $Id: profile.inc.php 1114 2010-04-09 18:15:05Z finglanj $
 */

if (!defined("IN_OBSERVERSHIPS_STUDENTS")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->isLoggedInAllowed('observerships', 'read',true) || $_SESSION["details"]["group"] != "student") {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";
	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
}  else {
	
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/observerships?section=completed", "title" => "Completed Observerships");
	
	$PAGE_META["title"]			= "Completed Observerships";
	$PAGE_META["description"]	= "";
	$PAGE_META["keywords"]		= "";

	$PROXY_ID					= $_SESSION["details"]["id"];
	
	if ((is_array($_SESSION["permissions"])) && ($total_permissions = count($_SESSION["permissions"]) > 1)) {
		$sidebar_html  = "The following individual".((($total_permissions - 1) != 1) ? "s have" : " has")." given you access to their ".APPLICATION_NAME." permission levels:";
		$sidebar_html .= "<ul class=\"menu\">\n";
		foreach ($_SESSION["permissions"] as $proxy_id => $result) {
			if ($proxy_id != $_SESSION["details"]["id"]) {
				$sidebar_html .= "<li class=\"checkmark\"><strong>".html_encode($result["fullname"])."</strong><br /><span class=\"content-small\">Exp: ".(($result["expires"]) ? date("D M d/y", $result["expires"]) : "Unknown")."</span></li>\n";
			}
		}
		$sidebar_html .= "</ul>\n";

		new_sidebar_item("Delegated Permissions", $sidebar_html, "delegated-permissions", "open");
	}
	
	require_once(ENTRADA_ABSOLUTE."/core/library/Models/ClinicalFacultyMembers.class.php");
	$faculty_members = ClinicalFacultyMembers::get();
	require_once(ENTRADA_ABSOLUTE."/core/library/Models/ObservershipDisciplines.class.php");
	$observership_disciplines = ObservershipDisciplines::get();
	
	$HEAD[] = "
	<style>
	.preceptor_other_field td {
		padding-left: 1em;
	} 
	</style>";
	
	$HEAD[] = "<link href=\"".ENTRADA_RELATIVE."/javascript/calendar/css/xc2_default.css\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/calendar/config/xc2_default.js\"></script>";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/calendar/script/xc2_inpage.js\"></script>";
	$HEAD[]	= "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/calendar/script/xc2_timestamp.js\"></script>";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/calendar/script/mod_time.js\"></script>";
	
	
	
	display_status_messages();
	
?>
<script type="text/javascript">

	xcDateFormat="yyyy-mm-dd hr:mi";
	xcMods[9].order = 1;
	xcFootTagSwitch=[0, 0, 0, 0, 0, 0, 0, 0];

	xcHours=["00", "01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "13","14","15","16","17","18","19","20","21","22","23"]; 
	xcMinutes=["00", "30"]; // simplified
		
	function preceptor_change() {
		if ($('preceptor_id').value === "other") {
			$$('.preceptor_other_field').invoke('show');
		} else {
			$$('.preceptor_other_field').invoke('hide');
		}
		
	}

	document.observe("dom:loaded",function() {
		preceptor_change();
		$('preceptor_id').observe('change',preceptor_change);
		provStateFunction($F($('observership_request_form')['country_id']))
		$('start_date').observe('focus',function(e) {
			showCalendar('',this,this,'2004-07-01','holder',0,30,1);
		}.bind($('start_date')));
	});


	function provStateFunction(country_id) {
		var url='<?php echo webservice_url("province"); ?>';
		<?php
		    if ($PROCESSED["province"] || $PROCESSED["province_id"]) {
				$source_arr = $PROCESSED;
		    } else {
		    	$source_arr = $result;
		    }
		    $province = $source_arr["province"];
		    $province_id = $source_arr["province_id"];
		    $prov_state = ($province) ? $province : $province_id;
		?>
		
		url = url + '?countries_id=' + country_id + '&prov_state=<?php echo $prov_state; ?>';
		new Ajax.Updater($('prov_state_div'), url,
			{
				method:'get',
				onComplete: function (init_run) {
					
					if ($('prov_state').type == 'select-one') {
						$('prov_state_label').removeClassName('form-nrequired');
						$('prov_state_label').addClassName('form-required');
						if (!init_run) 
							$("prov_state").selectedIndex = 0;
						
						
					} else {
						
						$('prov_state_label').removeClassName('form-required');
						$('prov_state_label').addClassName('form-nrequired');
						if (!init_run) 
							$("prov_state").clear();
						
						
					}
				}.curry(!provStateFunction.initialzed)
			});
		provStateFunction.initialzed = true;
		
	}
	provStateFunction.initialzed = false;

</script>
	
<h1>Request Observership</h1>

<form id="observership_request_form" name="observership_request_form" action="<?php echo ENTRADA_URL; ?>/observerships?section=request" method="post">
	<input type="hidden" name="action" value="request_observership"></input>
	
	<table>
		<colgroup>
			<col width="3%"></col>
			<col width="25%"></col>
			<col width="72%"></col>
		</colgroup>
		<tfoot>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
					<input type="submit" class="button" value="Submit Request" />
				</td>
			</tr>
		</tfoot>
		<tbody>
			<tr id="preceptor_selector">
				<td>&nbsp;</td>
				<td><label class="form-required" for="preceptor_id">Preceptor:</label></td>
				<td>
					<select name="preceptor_id" id="preceptor_id">
					<?php
					foreach ($faculty_members as $faculty_member) {
						echo build_option($faculty_member->getID() ,"Dr. " . $faculty_member->getFullname());
					} 
					?>
						<option value="other">Other</option>
					</select>
				</td>
			</tr>
			<tr class="preceptor_other_field">
				<td>&nbsp;</td>
				<td>
					<label class="form-required" for="preceptor_name">Preceptor Name:</label>
				</td>
				<td>
					<input type="text" name="preceptor_name"></input>
				</td>
			</tr>
			<tr class="preceptor_other_field">
				<td>&nbsp;</td>
				<td>
					<label class="form-required" for="preceptor_email">Preceptor E-mail:</label>
				</td>
				<td>
					<input type="text" name="preceptor_email"></input>
				</td>
			</tr>
			<tr class="preceptor_other_field">
				<td>&nbsp;</td>
				<td><label for="country_id" class="form-required">Country</label></td>
				<td>
					<?php
					$countries = fetch_countries();
					if ((is_array($countries)) && (count($countries))) {
						
						$country_id = ($PROCESSED["country_id"])?$PROCESSED["country_id"]:$result["country_id"];
						
						echo "<select id=\"country_id\" name=\"country_id\" style=\"width: 256px\" onchange=\"provStateFunction(this.value);\">\n";
						echo "<option value=\"0\"".((!country_id) ? " selected=\"selected\"" : "").">-- Select Country --</option>\n";
						foreach ($countries as $country) {
							echo "<option value=\"".(int) $country["countries_id"]."\"".(($country_id == $country["countries_id"]) ? " selected=\"selected\"" : "").">".html_encode($country["country"])."</option>\n";
						}
						echo "</select>\n";
					} else {
						echo "<input type=\"hidden\" id=\"country_id\" name=\"country_id\" value=\"0\" />\n";
						echo "Country information not currently available.\n";
					}
					?>
				</td>
			</tr>
			<tr class="preceptor_other_field">
				<td>&nbsp;</td>
				<td><label id="prov_state_label" for="prov_state_div" class="form-nrequired">Province / State</label></td>
				<td>
					<div id="prov_state_div">Please select a <strong>Country</strong> from above first.</div>
				</td>
			</tr>
			<tr class="preceptor_other_field">
				<td>&nbsp;</td>
				<td><label for="city" class="form-required">City:</label></td>
				<td>
					<input type="text" id="city" name="city" value="<?php echo html_encode($result["city"]); ?>" style="width: 250px; vertical-align: middle" maxlength="35" />
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><label for="discipline" class="form-required">Discipline:</label></td>
				<td>
					<select name="preceptor_id" id="preceptor_id">
					<?php
					foreach ($observership_disciplines as $discipline) {
						echo build_option($discipline->getID() ,$discipline->getTitle());
					} 
					?>
					</select>
				</td>
			</tr>	
			<tr>
				<td>&nbsp;</td>
				<td><label for="site" class="form-required">Site:</label></td>
				<td>
					<input type="text" id="site" name="site" style="width: 250px; vertical-align: middle" maxlength="255" /> <span class="content-small"><strong>Example: </strong>Kingston General Hospital</span>
				</td>
			</tr>
			<tr>
			<td>&nbsp;</td>
			<td><label class="form-required" for="start_date">Start Date</label></td>
			<td><input id="start_date" name="start_date"></input></td>
			</tr>
			<tr>
			<td>&nbsp;</td>
			<td><label class="form-required" for="end_date">End Date</label></td>
			<td><input name="end_date" id="end_date"></input></td>
			</tr>
		</tbody>
	
	</table>	

	<div class="clear">&nbsp;</div>
</form>

Form table with:<br/>
<br/>
faculty drop-down<br />start date picker<br />end date picker<br /><br />Post-back <br />-&gt; success<br />-&gt; Error (dates incorrect, faculty info not selected...)<br />-&gt; Confirmation after notice (not for credit because....)<br /> 

<?php
}
