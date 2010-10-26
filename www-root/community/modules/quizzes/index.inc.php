<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Used to list all available polls within this page of a community.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Andrew Dos-Santos <andrew.dos-santos@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
*/

if ((!defined("COMMUNITY_INCLUDED")) || (!defined("IN_QUIZZES"))) {
    exit;
} elseif (!$COMMUNITY_LOAD) {
	exit;
}
?>
<div id="module-header">
</div>
<script type="text/javascript">
	function openQuizWizard(eid, qid, action) {
		if (!action) {
			action = 'add';
		}

		if (!eid) {
			return;
		} else {
			var windowW = 485;
			var windowH = 585;

			var windowX = (screen.width / 2) - (windowW / 2);
			var windowY = (screen.height / 2) - (windowH / 2);

			quizWizard = window.open('<?php echo ENTRADA_URL; ?>/quiz-wizard.php?type=community_page&action=' + action + '&id=' + eid + ((qid) ? '&qid=' + qid : ''), 'quizWizard', 'width='+windowW+', height='+windowH+', scrollbars=no, resizable=yes');
			quizWizard.blur();
			window.focus();

			quizWizard.resizeTo(windowW, windowH);
			quizWizard.moveTo(windowX, windowY);

			quizWizard.focus();
		}
	}
</script>
<div style="padding-top: 10px; clear: both">
	<?php
	if ($COMMUNITY_ADMIN && $ENTRADA_ACL->amIAllowed($MODULES["quizzes"]["resource"], $MODULES["quizzes"]["permission"], false)) {
		?>
		<div style="float: right; margin-bottom: 5px">
			<ul class="page-action">
				<li><a href="<?php echo ENTRADA_URL; ?>/admin/quizzes?section=add">Create New Quiz</a></li>
				<li><a href="javascript: openQuizWizard('<?php echo $PAGE_ID; ?>', 0, 'add')">Attach Existing Quiz</a></li>
			</ul>
		</div>
		<div class="clear"></div>
		<br/><br/>
		<?php
	}
	/**
	 * This query will retrieve all of the quizzes associated with this evevnt.
	 */
	$query	= "	SELECT a.*, b.`quiztype_code`, b.`quiztype_title`
				FROM `attached_quizzes` AS a
				LEFT JOIN `quizzes_lu_quiztypes` AS b
				ON b.`quiztype_id` = a.`quiztype_id`
				WHERE a.`content_type` = 'community_page'
				AND a.`content_id` = ".$db->qstr($PAGE_ID)."
				GROUP BY a.`aquiz_id`
				ORDER BY a.`required` DESC, a.`quiz_title` ASC, a.`release_until` ASC";
	$quizzes = $db->GetAll($query);
	echo "	<div class=\"section-holder\">\n";
	echo "		<table class=\"tableList\" cellspacing=\"0\" summary=\"List of Attached Quizzes\">\n";
	echo "		<colgroup>\n";
	echo "			<col class=\"modified\" />\n";
	echo "			<col class=\"title\" />\n";
	echo "			<col class=\"date\" />\n";
	echo "		</colgroup>\n";
	echo "		<thead>\n";
	echo "			<tr>\n";
	echo "				<td class=\"modified\">&nbsp;</td>\n";
	echo "				<td class=\"title sortedASC\"><div class=\"noLink\">Quiz Title</div></td>\n";
	echo "				<td class=\"date\">Quiz Expires</td>\n";
	echo "			</tr>\n";
	echo "		</thead>\n";
	echo "		<tbody>\n";

	if ($quizzes) {
		foreach ($quizzes as $quiz_record) {
			$quiz_attempts		= 0;
			$total_questions	= quiz_count_questions($quiz_record["quiz_id"]);

			$query				= "	SELECT *
							FROM `quiz_progress`
							WHERE `aquiz_id` = ".$db->qstr($quiz_record["aquiz_id"])."
							AND `proxy_id` = ".$db->qstr($_SESSION["details"]["id"]);
			$progress_record	= $db->GetAll($query);
			if ($progress_record) {
				$quiz_attempts = count($progress_record);
			}

			$exceeded_attempts	= ((((int) $quiz_record["quiz_attempts"] === 0) || ($quiz_attempts < $quiz_record["quiz_attempts"])) ? false : true);

			if (((!(int) $quiz_record["release_date"]) || ($quiz_record["release_date"] <= time())) && ((!(int) $quiz_record["release_until"]) || ($quiz_record["release_until"] >= time())) && (!$exceeded_attempts)) {
				$allow_attempt = true;
			} else {
				$allow_attempt = false;
			}

			echo "	<tr id=\"quiz-".$quiz_record["aquiz_id"]."\">\n";
			echo "		<td class=\"modified\" style=\"vertical-align: top\">".(((int) $quiz_record["last_visited"]) ? (((int) $quiz_record["last_visited"] >= (int) $quiz_record["updated_date"]) ? "<img src=\"".ENTRADA_URL."/images/checkmark.gif\" width=\"20\" height=\"20\" alt=\"You have previously completed this quiz.\" title=\"You have previously completed this quiz.\" style=\"vertical-align: middle\" />" : "<img src=\"".ENTRADA_URL."/images/exclamation.gif\" width=\"20\" height=\"20\" alt=\"This attached quiz has been updated since you last completed it.\" title=\"This attached quiz has been updated since you last completed it.\" style=\"vertical-align: middle\" />") : "")."</td>\n";
			echo "		<td class=\"title\" style=\"vertical-align: top; white-space: normal; overflow: visible\">\n";
			if ($COMMUNITY_ADMIN && $_SESSION["details"]["group"] != "student") {
				echo "	<a href=\"".ENTRADA_URL."/admin/quizzes?section=results&amp;community=true&amp;id=".$quiz_record["aquiz_id"]."\"><img src=\"".ENTRADA_URL."/images/view-stats.gif\" width=\"16\" height=\"16\" alt=\"View results of ".html_encode($quiz_record["quiz_title"])."\" title=\"View results of ".html_encode($quiz_record["quiz_title"])."\" style=\"vertical-align: middle\" border=\"0\" /></a>\n";
			}
			if ($allow_attempt) {
				echo "		<a href=\"".ENTRADA_URL."/quizzes?section=attempt&amp;community=true&amp;id=".$quiz_record["aquiz_id"]."\" title=\"Take ".html_encode($quiz_record["quiz_title"])."\" style=\"font-weight: bold\">".html_encode($quiz_record["quiz_title"])."</a>";
			} else {
				echo "		<span style=\"color: #666666; font-weight: bold\">".html_encode($quiz_record["quiz_title"])."</span>";
			}

			echo "			<div class=\"content-small\" style=\"margin-top: 3px; margin-bottom: 5px\">\n";
			if (((int) $quiz_record["release_date"]) && ($quiz_record["release_date"] > time())) {
				echo "You will be able to attempt this quiz starting <strong>".date(DEFAULT_DATE_FORMAT, $quiz_record["release_date"])."</strong>.<br /><br />";
			} elseif (((int) $quiz_record["release_until"]) && ($quiz_record["release_until"] < time())) {
				echo "This quiz was only available until <strong>".date(DEFAULT_DATE_FORMAT, $quiz_record["release_until"])."</strong>. Please contact a teacher for assistance if required.<br /><br />";
			}

			echo quiz_generate_description($quiz_record["required"], $quiz_record["quiztype_code"], $quiz_record["quiz_timeout"], $total_questions, $quiz_record["quiz_attempts"], $quiz_record["timeframe"]);
			echo "			</div>\n";

			if ($progress_record) {
				echo "<strong>Your Attempts</strong>";
				echo "<ul class=\"menu\">";
				foreach ($progress_record as $entry) {
					$quiz_start_time	= $entry["updated_date"];
					$quiz_end_time		= (((int) $quiz_record["quiz_timeout"]) ? ($quiz_start_time + ($quiz_record["quiz_timeout"] * 60)) : 0);

					/**
					 * Checking for quizzes that are expired, but still in progress.
					 */
					if (($entry["progress_value"] == "inprogress") && ((((int) $quiz_record["release_until"]) && ($quiz_record["release_until"] < time())) || (($quiz_end_time) && (time() > ($quiz_end_time + 30))))) {
						$quiz_progress_array	= array (
							"progress_value" => "expired",
							"quiz_score" => "0",
							"quiz_value" => "0",
							"updated_date" => time(),
							"updated_by" => $_SESSION["details"]["id"]
						);
						if (!$db->AutoExecute("quiz_progress", $quiz_progress_array, "UPDATE", "qprogress_id = ".$db->qstr($entry["qprogress_id"]))) {
							application_log("error", "Unable to update the qprogress_id [".$qprogress_id."] to expired. Database said: ".$db->ErrorMsg());
						}
						$entry["progress_value"] = "expired";
					}

					switch ($entry["progress_value"]) {
						case "complete" :
							if (($quiz_record["quiztype_code"] != "delayed") || ($quiz_record["release_until"] <= time())) {
								$percentage = ((round(($entry["quiz_score"] / $entry["quiz_value"]), 2)) * 100);
								echo "<li class=\"".(($percentage >= 60) ? "correct" : "incorrect")."\">";
								echo	date(DEFAULT_DATE_FORMAT, $entry["updated_date"])." <strong>Score:</strong> ".$entry["quiz_score"]."/".$entry["quiz_value"]." (".$percentage."%)";
								echo "	( <a href=\"".ENTRADA_URL."/quizzes?section=results&amp;community=true&amp;id=".$entry["qprogress_id"]."\">review quiz</a> )";
								echo "</li>";
							} else {
								echo "<li>".date(DEFAULT_DATE_FORMAT, $entry["updated_date"])." <strong>Score:</strong> To Be Released ".date(DEFAULT_DATE_FORMAT, $quiz_record["release_until"])."</li>";
							}
						break;
						case "expired" :
							echo "<li class=\"incorrect\">".date(DEFAULT_DATE_FORMAT, $entry["updated_date"])." <strong>Expired Attempt</strong>: not completed.</li>";
						break;
						case "inprogress" :
							echo "<li>".date(DEFAULT_DATE_FORMAT, $entry["updated_date"])." <strong>Attempt In Progress</strong> ( <a href=\"".ENTRADA_URL."/quizzes?section=attempt&amp;id=".$quiz_record["aquiz_id"]."\">continue quiz</a> )</li>";
						break;
						default :
							continue;
						break;
					}
				}
				echo "</ul>";
			}

			echo "		</td>\n";
			echo "		<td class=\"date\" style=\"vertical-align: top\">".(((int) $quiz_record["release_until"]) ? date(DEFAULT_DATE_FORMAT, $quiz_record["release_until"]) : "No Expiration")."</td>\n";
			echo "	</tr>\n";
		}
	} else {
		echo "		<tr>\n";
		echo "			<td colspan=\"3\">\n";
		echo "				<div class=\"content-small\" style=\"margin-top: 3px; margin-bottom: 5px\">There are no online quizzes currently attached to this page.</div>\n";
		echo "			</td>\n";
		echo "		</tr>\n";
	}
	echo "		</tbody>\n";
	echo "		</table>\n";
	echo "	</div>\n";
	?>
</div>