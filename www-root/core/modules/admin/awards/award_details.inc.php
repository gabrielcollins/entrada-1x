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
 * The default file that is loaded when /admin/users is accessed.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/
if ((!defined("PARENT_INCLUDED")) || (!defined("IN_AWARDS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("awards", "update", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {

	if (isset($_GET["id"]) && ($tmp_input = clean_input($_GET["id"], array("trim", "int")))) {
		$award_id = $tmp_input;
	} else {
		$award_id = 0;
	}
	if ($award_id) {

		require_once("Models/awards/InternalAwards.class.php");
	
		process_manage_award_details();

		
		$award = InternalAward::get($award_id);
		
		echo "<div id=\"award_messages\">";
		display_status_messages();
		echo "</div>";
		
		$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/awards?section=award_details&id=".$award_id, "title" => "Award: " . $award->getTitle());

		$PAGE_META["title"]			= "Award Details: " . $award->getTitle();
		$PAGE_META["description"]	= "";
		$PAGE_META["keywords"]		= "";


		$HEAD[] = "<script language='javascript' src='".ENTRADA_URL."/javascript/ActiveDataEntryProcessor.js'></script>";
		$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/tabpane/tabpane.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
		$HEAD[] = "<link href=\"".ENTRADA_URL."/css/tabpane.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
		$HEAD[] = "<style type=\"text/css\"> .dynamic-tab-pane-control .tab-page {height:auto;}</style>\n";
		?>
<h1>Award: <?php  echo $award->getTitle(); ?></h1>
<div class="tab-pane" id="award-details-tabs">
<div class="tab-page" style="padding-bottom: 80px">
<h2 class="tab">Award Details</h2>
<h2>Award Details</h2>

		<?php echo award_details_edit($award); ?></div>
<div class="tab-page" style="padding-bottom: 80px">
<h2 class="tab">Award Recipients</h2>

		<?php
		$show_add_recipient_form =  ($_GET['show'] != "add_recipient");
		?>


<form id="add_award_recipient_form" name="add_award_recipient_form"
	action="<?php echo ENTRADA_URL; ?>/admin/awards?section=award_details&id=<?php echo $award_id; ?>&tab=recipients"
	method="post"
	<?php if ($show_add_recipient_form) { echo "style=\"display:none;\""; }   ?>>
<input type="hidden" name="action" value="add_award_recipient"></input>
<input type="hidden" name="award_id" value="<?php echo $award_id; ?>"></input>

	
<table class="award_recipients" style="width:100%;">
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
			<td colspan="2">
				<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/awards?section=award_details&id=<?php echo $award_id; ?>'" />
			</td>
			<td style="padding-top: 5px; text-align: right;">	
				<input type="submit" class="button" value="Add Recipient" />
			</td>
		</tr>
	</tfoot>
	<tbody>
	


		<tr>
			<td>&nbsp;</td>
			<td><label for="internal_award_user_name" class="form-required">Student:</label>
			</td>
			<td>
				<input type="hidden" id="internal_award_user_id" name="internal_award_user_id" value="" />
				<input type="hidden" id="internal_award_user_ref" name="internal_award_user_ref" value="" />
						
				<input type="text" id="internal_award_user_name" name="fullname" size="30" value="" autocomplete="off" style="width: 203px; vertical-align: middle" onkeyup="checkStudent()" />
				<div class="autocomplete" id="internal_award_user_name_auto_complete"></div>
			
				<span class="content-small">(<strong>Example:</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?>)</span>
			</td>
		</tr>

		<tr>
			<td>&nbsp;</td>
			<td style="vertical-align: top;"><label for="internal_award_year"
				class="form-required">Year</label></td>
			<td><select name="internal_award_year">
			<?php

			$cur_year = (int) date("Y");
			$start_year = $cur_year - 4;
			$end_year = $cur_year + 4;

			for ($opt_year = $start_year; $opt_year <= $end_year; ++$opt_year) {
				echo build_option($opt_year, $opt_year, $opt_year == $cur_year);
			}

			?>
			</select></td>
		</tr>
	</tbody>
</table>


<div class="clear">&nbsp;</div>
</form>
<div id="add_award_recipient_link" style="float: right;<?php if (!$show_add_recipient_form) { echo "display:none;"; }   ?>">
<ul class="page-action">
	<li><a id="add_award_recipient"
		href="<?php echo ENTRADA_URL; ?>/admin/awards?section=award_details&show=add_recipient&id=<?php echo $award_id; ?>"
		class="strong-green">Add Award Recipient</a></li>
</ul>
</div>


<div class="clear">&nbsp;</div>
<h2>Award Recipients</h2>




<div id="award_recipients"><?php echo award_recipients_list($award);?></div>
<script language="javascript">
	
	function addRecipient(event) {
		if (!((document.getElementById('internal_award_user_id') != null) && (document.getElementById('internal_award_user_id').value != ''))) {
				alert('You can only add studets as award recipients if they exist in this system.\n\nIf you are typing in their name properly (Lastname, Firstname) and their name does not show up in the list, then chances are that they do not exist in our system.\n\nPlease contact Denise Jones in the Undergrad office (613-533-6000 x77804) to get an account for the requested individual.\n\nImportant: When you see the correct name pop-up in the list as you type, make sure you select the name with your mouse, do not press the Enter button.');
			Event.stop(event);
			return false;
			
		}
	}
	
	function selectStudent(id) {
		if ((id != null) && (document.getElementById('internal_award_user_id') != null)) {
			document.getElementById('internal_award_user_id').value = id;
		}
	}
	function copyStudent() {
		if ((document.getElementById('internal_award_user_name') != null) && (document.getElementById('internal_award_user_ref') != null)) {
			document.getElementById('internal_award_user_ref').value = document.getElementById('internal_award_user_name').value;
		}
	
		return true;
	}
	
	function checkStudent() {
		if ((document.getElementById('internal_award_user_name') != null) && (document.getElementById('internal_award_user_ref') != null) && (document.getElementById('internal_award_user_id') != null)) {
			if (document.getElementById('internal_award_user_name').value != document.getElementById('internal_award_user_ref').value) {
				document.getElementById('internal_award_user_id').value = '';
			}
		}
	
		return true;
	}

	new Ajax.Autocompleter(	'internal_award_user_name', 
			'internal_award_user_name_auto_complete', 
			'<?php echo webservice_url("personnel"); ?>', 
			{	frequency: 0.2, 
				parameters: "type=learners",
				minChars: 2, 
				afterUpdateElement: function (text, li) {
					selectStudent(li.id); copyStudent();
				}
			});

	var award_recipient_comments = new ActiveDataEntryProcessor({
		url : '<?php echo webservice_url("awards"); ?>',
		data_destination: $('#award_recipients'),
		new_form: $('#add_award_recipient_form'),
		remove_forms_selector: '.remove_award_recipient_form',
		new_button: $('#add_award_recipient_link'),
		hide_button: $('#hide_award_recipient'),
		messages: $('#award_messages')
		
	});

	$('add_award_recipient_form').observe('submit',addRecipient);
	</script>
</div>
</div>

			<?php
	}
}