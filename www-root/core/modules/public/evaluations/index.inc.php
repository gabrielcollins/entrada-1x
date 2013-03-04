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
 * This is the default section that is loaded when the quizzes module is
 * accessed without a defined section.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if((!defined("PARENT_INCLUDED")) || (!defined("IN_PUBLIC_EVALUATIONS"))) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
}

require_once("Models/evaluation/Evaluation.class.php");

if (isset($_GET["view"]) && $_GET["view"] == "review") {
	$view = "review";
} elseif (isset($_GET["view"]) && $_GET["view"] == "attempt") { 
	$view = "attempt";
} else {
	$view = "index";
}

$evaluations = Evaluation::getEvaluatorEvaluations();
$review_evaluations = Evaluation::getReviewerEvaluations();
$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/jquery/jquery.dataTables.min.js\"></script>";

if (isset($_GET["request"]) && $_GET["request"]) {
	if (isset($RECORD_ID) && $RECORD_ID) {
		$query = "SELECT `evaluation_title` FROM `evaluations` WHERE `evaluation_id` = ".$db->qstr($RECORD_ID);
		$evaluation_title = $db->GetOne($query);
		$target_evaluations = Evaluation::getTargetEvaluations();
		$found = false;
		if ($target_evaluations) {
			foreach ($target_evaluations as $target_evaluation) {
				if ($target_evaluation["evaluation_id"] == $RECORD_ID) {
					$found = true;
				}
			}
		}
		if ($evaluation_title && $found) {
			if (isset($_POST["associated_evaluator"]) && $_POST["associated_evaluator"]) {
				$associated_evaluators = explode(",", $_POST["associated_evaluator"]);
				$notifications_sent = 0;
				$proxy_id = 0;
				foreach($associated_evaluators as $proxy_id) {
					if ($proxy_id = clean_input($proxy_id, array("trim", "int"))) {
						$query = "SELECT *, COUNT(a.`evaluation_id`) AS `completed_evaluations` FROM `evaluation_progress` AS a
									JOIN `evaluations` AS b
									ON a.`evaluation_id` = b.`evaluation_id`
									WHERE a.`evaluation_id` = ".$db->qstr($RECORD_ID)."
									AND a.`proxy_id` = ".$db->qstr($proxy_id)."
									AND a.`progress_value` = 'complete'
									GROUP BY a.`evaluation_id`";
						$all_targets_progress = $db->GetRow($query);
						if (!$all_targets_progress || $all_targets_progress["max_submittable"] == 0 || $all_targets_progress["completed_evaluations"] < $all_targets_progress["max_submittable"]) {
							$query = "SELECT * FROM `evaluation_progress` AS a
										JOIN `evaluations` AS b
										ON a.`evaluation_id` = b.`evaluation_id`
										WHERE a.`evaluation_id` = ".$db->qstr($RECORD_ID)."
										AND a.`proxy_id` = ".$db->qstr($proxy_id)."
										AND a.`target_record_id` = ".$db->qstr($ENTRADA_USER->getId())."
										AND a.`progress_value` = 'complete'
										GROUP BY a.`evaluation_id`";
							$evaluation_progress = $db->GetRow($query);
							if (!$evaluation_progress || $evaluation_progress["allow_repeat_targets"] == 1) {
								require_once("Models/notifications/Notification.class.php");
								require_once("Models/notifications/NotificationUser.class.php");
								$notification_user = NotificationUser::get($proxy_id, "evaluation_request", $RECORD_ID, $ENTRADA_USER->getId());
								if (!$notification_user) {
									$notification_user = NotificationUser::add($proxy_id, "evaluation_request", $RECORD_ID, $ENTRADA_USER->getId());
								}
								if (Notification::add($notification_user->getID(), $ENTRADA_USER->getId(), $RECORD_ID)) {
									$notifications_sent++;
								} else {
									add_error("An issue was encountered while attempting to send a notification to a user [".get_account_data("wholename", $proxy_id)."] requesting that they complete an evaluation [".$evaluation_title."] for you. The system administrator has been notified of this error, please try again later.");
									application_log("Unable to send notification requesting an evaluation be completed to evaluator [".$proxy_id."] for evaluation_id [".$RECORD_ID."].");
								}
							} else {
								add_error("The selected evaluator [".get_account_data("wholename", $proxy_id)."] has already completed this evaluation [".$evaluation_title."] for you, and is unable to attempt it again.");
							}
						} else {
							add_error("The selected evaluator [".get_account_data("wholename", $proxy_id)."] has already completed this evaluation [".$evaluation_title."] the maximum number of times, and is therefore unable to attempt it again.");
						}
					}
				}
			} else {
				add_error("An evaluator must be selected to request an evaluation be completed for you.");
			}
		} else {
			add_error("A valid evaluation must be selected from the drop-down list to request an evaluation be completed for you.");
		}
	} else {
		add_error("An evaluation must be selected from the drop-down list to request an evaluation be completed for you.");
	}
	if (has_error()) {
		echo display_error();
	}
	if (isset($notifications_sent) && $notifications_sent) {
		add_success("Successfully requested that ".($notifications_sent > 1 ? $notifications_sent." evaluators" : get_account_data("wholename", $proxy_id))." fill out this evaluation [".$evaluation_title."] for you.");
		echo display_success();
	}
}

if ($evaluations && $view != "review") {
	?>
	<h1>My Evaluations and Assessments</h1>
	<?php
	if ($review_evaluations) {
		$sidebar_html  = "<ul class=\"menu\">\n";
		$sidebar_html .= "	<li class=\"link\"><a href=\"".ENTRADA_URL."/evaluations?view=review\">View Completed Evaluations Available for Review</a></li>\n";
		$sidebar_html .= "</ul>\n";

		new_sidebar_item("Evaluations Review", $sidebar_html, "view-review", "open", "1.9");
	}
	$evaluation_id = 0;
	echo "<div class=\"no-printing\">\n";
    echo "    <ul class=\"nav nav-tabs\">\n";
	echo "		<li".($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["view_type"] == "available" ? " class=\"active\"" : "")." style=\"width:25%;\"><a id=\"available\" onclick=\"loadTab(this.id)\">Display Available</a></li>\n";
	echo "		<li".($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["view_type"] == "overdue" ? " class=\"active\"" : "")." style=\"width:25%;\"><a id=\"overdue\" onclick=\"loadTab(this.id)\">Display Overdue</a></li>\n";
	echo "		<li".($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["view_type"] == "complete" ? " class=\"active\"" : "")." style=\"width:25%;\"><a id=\"complete\" onclick=\"loadTab(this.id)\">Display Completed</a></li>\n";
	echo "		<li".($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["view_type"] == "all" ? " class=\"active\"" : "")." style=\"width:25%;\"><a id=\"all\" onclick=\"loadTab(this.id)\">Display All</a></li>\n";
	echo "	</ul>\n";
	echo "</div>\n";
	echo "<br />";
	$HEAD[] = "<script type=\"text/javascript\">
	var eTable;
	jQuery(document).ready(function() {
		eTable = jQuery('#evaluations').dataTable(
			{    
				'sPaginationType': 'full_numbers',
				'aoColumns' : [ 
						null,
						null,
						null,
						{'sType': 'alt-string'},
						null,
						null,
                        { 'bVisible' : false }
					],
				'bInfo': false
			}
		);
		eTable.fnFilter('".($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["view_type"] == "all" ? "" : $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["view_type"])."', 6);
	});
	
	function loadTab (value) {
		if (!$(value).hasClassName('active')) {
			new Ajax.Request('".ENTRADA_URL."/evaluations', {
				method: 'get',
				parameters: {
								'view_type': value,
								'ajax': 1
							}
			});
			var filterval = (value == 'all' ? '' : value)
			eTable.fnFilter(filterval, 6);
			$$('li.active').each(function (e) {
				e.removeClassName('active');
			});
			$(value).parentNode.addClassName('active');
		}
	}
	</script>";
	?>
	<table id="evaluations" class="tableList" cellspacing="0" summary="List of Evaluations and Assessments to Attempt">
	<colgroup>
		<col class="modified" />
		<col class="general" />
		<col class="general" />
		<col class="date-small" />
		<col class="title" />
		<col class="date-smallest" />
		<col class="general" />
	</colgroup>
	<thead>
		<tr>
			<td class="modified">&nbsp;</td>
			<td class="general">Type</td>
			<td class="general">Target(s)</td>
			<td class="date-small">Close Date</td>
			<td class="title">Title</td>
			<td class="date-smallest">Submitted</td>
			<td class="general">Status</td>
		</tr>
	</thead>
	<tbody>
	<?php
	$request_evaluations = array();
	foreach ($evaluations as $evaluation) {
		if ($evaluation["click_url"]) {
			echo "<tr>\n";
			echo "	<td>&nbsp;</td>\n";
			echo "	<td><a href=\"".$evaluation["click_url"]."\">".(!empty($evaluation["target_title"]) ? $evaluation["target_title"] : "No Type Found")."</a></td>\n";
			echo "	<td><a href=\"".$evaluation["click_url"]."\">".(!empty($evaluation["evaluation_target_title"]) ? $evaluation["evaluation_target_title"] : "No Target")."</a></td>\n";
			echo "	<td><a href=\"".$evaluation["click_url"]."\" alt=\"".$evaluation["evaluation_finish"]."\">".date(DEFAULT_DATE_FORMAT, $evaluation["evaluation_finish"])."</a></td>\n";
			echo "	<td><a href=\"".$evaluation["click_url"]."\">".html_encode($evaluation["evaluation_title"])."</a></td>\n";
			echo "	<td><a href=\"".$evaluation["click_url"]."\">".($evaluation["completed_attempts"] ? ((int)$evaluation["completed_attempts"]) : "0")."/".($evaluation["max_submittable"] ? ((int)$evaluation["max_submittable"]) : "0")."</a></td>\n";
			echo "	<td>".($evaluation["max_submittable"] > $evaluation["completed_attempts"] && $evaluation["evaluation_finish"] < time() ? "overdue available" : ($evaluation["max_submittable"] > $evaluation["completed_attempts"] ? "available" : "complete"))."</td>";
			echo "</tr>\n";
		} else {
			echo "<tr>\n";
			echo "	<td class=\"content-small\">&nbsp;</td>\n";
			echo "	<td class=\"content-small\">".(!empty($evaluation["target_title"]) ? $evaluation["target_title"] : "No Type Found")."</td>\n";
			echo "	<td class=\"content-small\">".(!empty($evaluation["evaluation_target_title"]) ? $evaluation["evaluation_target_title"] : "No Target")."</td>\n";
			echo "	<td class=\"content-small\"><span alt=\"".$evaluation["evaluation_finish"]."\">".date(DEFAULT_DATE_FORMAT, $evaluation["evaluation_finish"])."</span></td>\n";
			echo "	<td class=\"content-small\">".html_encode($evaluation["evaluation_title"])."</td>\n";
			echo "	<td class=\"content-small\">".($evaluation["completed_attempts"] ? ((int)$evaluation["completed_attempts"]) : "0")."/".($evaluation["max_submittable"] ? ((int)$evaluation["max_submittable"]) : "0")."</td>\n";
			echo "	<td>".($evaluation["max_submittable"] > $evaluation["completed_attempts"] && $evaluation["evaluation_finish"] < time() ? "overdue available" : ($evaluation["max_submittable"] > $evaluation["completed_attempts"] ? "available" : "complete"))."</td>";
			echo "</tr>\n";
		}
	}
	?>
	</tbody>
	</table>
	<?php
} elseif ($review_evaluations && $view != "attempt") {
	$HEAD[] = "<script type=\"text/javascript\">
	var eTable;
	jQuery(document).ready(function() {
		eTable = jQuery('#evaluations').dataTable(
			{    
				'sPaginationType': 'full_numbers',
				'aoColumns' : [ 
						null,
						null,
						{'sType': 'alt-string'},
						null,
						null,
                        { 'bVisible' : false }
					],
				'bInfo': false
			}
		);
		eTable.fnFilter('".($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["view_type"] == "all" ? "" : $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["view_type"])."', 5);
	});
	
	function loadTab (value) {
		if (!$(value).hasClassName('active')) {
			new Ajax.Request('".ENTRADA_URL."/evaluations', {
				method: 'get',
				parameters: {
								'view_type': value,
								'ajax': 1
							}
			});
			var filterval = (value == 'all' ? '' : value)
			eTable.fnFilter(filterval, 5);
			$$('li.active').each(function (e) {
				e.removeClassName('active');
			});
			$(value).parentNode.addClassName('active');
		}
	}
	</script>";
	if ($ENTRADA_ACL->amIAllowed(new EvaluationResource(null, true), 'update')) {
		echo "<div class=\"no-printing\">\n";
		echo "    <ul class=\"nav nav-tabs\">\n";
		echo "		<li".($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["view_type"] == "available" ? " class=\"active\"" : "")." style=\"width:25%;\"><a id=\"available\" onclick=\"loadTab(this.id)\">Display My Evaluations and Assessments</a></li>\n";
		echo "		<li".($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["view_type"] == "all" ? " class=\"active\"" : "")." style=\"width:25%;\"><a id=\"all\" onclick=\"loadTab(this.id)\">Display All</a></li>\n";
		echo "	</ul>\n";
		echo "</div>\n";
		echo "<br />";
	}
	?>
	<h1>Evaluations and Assessments available for review</h1>
	<?php
	if ($evaluations) {
		$sidebar_html  = "<ul class=\"menu\">\n";
		$sidebar_html .= "	<li class=\"link\"><a href=\"".ENTRADA_URL."/evaluations?view=attempt\">View My Evaluations Available for Completion</a></li>\n";
		$sidebar_html .= "</ul>\n";

		new_sidebar_item("My Evaluations", $sidebar_html, "view-pending", "open", "1.9");
	}
	?>
	<table id="evaluations" class="tableList" cellspacing="0" summary="List of Evaluations and Assessments to Review">
	<colgroup>
		<col class="modified" />
		<col class="general" />
		<col class="date-small" />
		<col class="title" />
		<col class="date-smallest" />
		<col class="date-smallest" />
	</colgroup>
	<thead>
		<tr>
			<td class="modified">&nbsp;</td>
			<td class="general">Type</td>
			<td class="date-small">Close Date</td>
			<td class="title">Title</td>
			<td class="date-smallest">Submitted</td>
			<td class="date-smallest">Status</td>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach ($review_evaluations as $evaluation) {
		$url = ENTRADA_URL."/evaluations?section=review&id=".$evaluation["evaluation_id"];

		echo "<tr>\n";
		echo "	<td>&nbsp;</td>\n";
		echo "	<td><a href=\"".$url."\">".(!empty($evaluation["target_title"]) ? $evaluation["target_title"] : "No Type Found")."</a></td>\n";
		echo "	<td><a href=\"".$url."\" alt=\"".$evaluation["evaluation_finish"]."\">".date(DEFAULT_DATE_FORMAT, $evaluation["evaluation_finish"])."</a></td>\n";
		echo "	<td><a href=\"".$url."\">".html_encode($evaluation["evaluation_title"])."</a></td>\n";
		echo "	<td><a href=\"".$url."\">".($evaluation["completed_attempts"] ? ((int)$evaluation["completed_attempts"]) : "0")."</a></td>\n";
		echo "	<td><a href=\"".$url."\">".(isset($evaluation["admin"]) && $evaluation["admin"] ? "" : "available")."</a></td>\n";
		echo "</tr>\n";
	}
	?>
	</tbody>
	</table>
	<?php
} else {
	if (!isset($evaluations) || !$evaluations) {
		if ($review_evaluations) {
			$sidebar_html  = "<ul class=\"menu\">\n";
			$sidebar_html .= "	<li class=\"link\"><a href=\"".ENTRADA_URL."/evaluations?view=review\">View Completed Evaluations Available for Review</a></li>\n";
			$sidebar_html .= "</ul>\n";

			new_sidebar_item("Evaluations Review", $sidebar_html, "view-review", "open", "1.9");
		}
		?>
		<div class="display-generic">
			There are no evaluations or assessments <strong>assigned to you</strong> in the system at this time.
		</div>
		<?php
	}
}


$request_evaluations = array();
$target_evaluations = Evaluation::getTargetEvaluations();
if ($target_evaluations) {
	foreach ($target_evaluations as $target_evaluation) {
		if (isset($target_evaluation["allow_target_request"]) && $target_evaluation["allow_target_request"]) {
			$request_evaluations[] = $target_evaluation;
		}
	}
}
if (isset($request_evaluations) && count($request_evaluations)) {
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	$ONLOAD[] = "evaluator_list = new AutoCompleteList({ type: 'evaluator', url: '". ENTRADA_RELATIVE ."/api/personnel.api.php?type=evaluators&id=".(isset($RECORD_ID) && $RECORD_ID ? $RECORD_ID : 0)."', remove_image: '". ENTRADA_RELATIVE ."/images/action-delete.gif'})";
	$HEAD[] = "
	<script type=\"text/javascript\">jQuery(window).keydown(function(e) { if (e.keyCode == 123) debugger; });
		function loadAutocompleteList(evaluation_id) {
			evaluator_list.setUrl('". ENTRADA_RELATIVE ."/api/personnel.api.php?type=evaluators&id='+evaluation_id);
		}
	</script>";
	$sidebar_html  = "<form method=\"post\" action=\"".ENTRADA_RELATIVE."/evaluations?request=1\">\n";
	$sidebar_html .= "	<label class=\"form-nrequired\" for=\"evaluation_request_id\">Evaluation: </label>";
	$sidebar_html .= "	<select style=\"width: 150px; overflow: none;\" name=\"id\" onchange=\"loadAutocompleteList(this.options[this.selectedIndex].value)\">";
	$sidebar_html .= "		<option value=\"0\">-- Select an Evaluation --</option>";
	foreach ($request_evaluations as $request_evaluation) {
		$sidebar_html .= "		<option value=\"".$request_evaluation["evaluation_id"]."\"".(isset($RECORD_ID) && $RECORD_ID == $request_evaluation["evaluation_id"] ? " selecte=\"selected\"" : "").">".$request_evaluation["evaluation_title"]."</option>";
	}
	$sidebar_html .= "	</select>";
	$sidebar_html .= "	<br /><br /><label class=\"form-nrequired\" for=\"evaluator\">Evaluator: </label>";
	$sidebar_html .= "<input type=\"text\" id=\"evaluator_name\" name=\"fullname\" size=\"30\" autocomplete=\"off\" style=\"width: 140px; vertical-align: middle\" />";
	$sidebar_html .= "<div class=\"autocomplete\" id=\"evaluator_name_auto_complete\"></div>";
	$sidebar_html .= "<input type=\"hidden\" id=\"associated_evaluator\" name=\"associated_evaluator\" />";
	$sidebar_html .= "<input type=\"button\" class=\"button-sm\" id=\"add_associated_evaluator\" value=\"Add\" style=\"vertical-align: middle\" />";
	$sidebar_html .= "<ul id=\"evaluator_list\" class=\"menu\" style=\"margin-top: 15px\"></ul>\n";
	$sidebar_html .= "<input type=\"hidden\" id=\"evaluator_ref\" name=\"evaluator_ref\" value=\"\" />";
	$sidebar_html .= "<input type=\"hidden\" id=\"evaluator_id\" name=\"evaluator_id\" value=\"\" />";
	$sidebar_html .= "	<br /><br /><input type=\"submit\" value=\"Request Evaluation\" />";
	$sidebar_html .= "</form>";

	new_sidebar_item("Request an Evaluation", $sidebar_html, "request-evaluation", "open", "1.9");
}