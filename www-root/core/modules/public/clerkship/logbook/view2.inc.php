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
 * Allows the student to view their logbook details.
 *
 * @author Organisation: University of Calgary
 * @author Unit: Undergraduate Medical Education
 * @author Developer: Doug Hall <hall@ucalgary.ca>
 * @copyright Copyright 2009 University of Calgary. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_CLERKSHIP"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('logbook', 'read')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {
    $HEAD[] = "<link href=\"".ENTRADA_URL."/css/tabpane.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
    $HEAD[] = "<style type=\"text/css\">.dynamic-tab-pane-control .tab-page { height:auto; }</style>\n";

    $HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/tabpane/tabpane.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";

    $BREADCRUMB[]	= array("url" => ENTRADA_URL."/public/clerkship/logbook?section=view", "title" => "View Patient Encounters");

    if(isset($_GET["core"])) {
	$rotation_id = clean_input($_GET["core"], "int");
    } else {
	$rotation_id = 0;
    }
    $clinical_rotation	 = clerkship_get_rotation($rotation_id);

    if (isset($_GET["id"]) && $_GET["id"]) {
    	$PROXY_ID = $_GET["id"];
	$STUDENT = false;
    } else {
    	$PROXY_ID = $ENTRADA_USER->getID();
	$STUDENT = true;
    }

    $query = "	SELECT `comments` FROM `".CLERKSHIP_DATABASE."`.`logbook_rotation_comments`
		WHERE `rotation_id` = ".$db->qstr($rotation_id)."
		AND `proxy_id` = ".$db->qstr($PROXY_ID);
    $comments = $db->GetOne($query);

    // Error Checking
    switch ($STEP) {
	case 2 :
	    if (isset($_POST["comments"]) && ($new_comments = clean_input($_POST["comments"], array("trim", "notags")))) {
		$PROCESSED["comments"] = $new_comments . "\n";
		if ($comments) {
		    if ($db->AutoExecute(CLERKSHIP_DATABASE.".logbook_rotation_comments", $PROCESSED, "UPDATE", "`proxy_id` = ".$db->qstr($PROXY_ID)." AND `rotation_id` = ".$db->qstr($rotation_id))) {
			$SUCCESS++;
		    }
		} else {
		    $PROCESSED["proxy_id"] = $PROXY_ID;
		    $PROCESSED["rotation_id"] = $rotation_id;
		    if ($db->AutoExecute(CLERKSHIP_DATABASE.".logbook_rotation_comments", $PROCESSED, "INSERT")) {
			$SUCCESS++;
		    }
		}
	    }
	    if ($SUCCESS) {
		$url = ENTRADA_URL."/clerkship/logbook?".replace_query(array("step" => ""));
		$SUCCESS++;
		$SUCCESSSTR[]  	= "You have successfully updated the <strong>Comments and Feeedback</strong><br /><br />Please <a href=\"".$url."\">click here</a> to proceed to the index page or you will be automatically forwarded in 3 seconds.";
		$ONLOAD[]	= "setTimeout('window.location=\\'".$url."\\'', 1000)";

	    }
	break;
	case 1 :
	default :
	    $PROCESSED["comments"] = $comments;
	    continue;
	break;
    }
// Display Content
    switch ($STEP) {
	case 2 :
	    if ($SUCCESS) {
		echo display_success();
	    }

	    if ($NOTICE) {
		echo display_notice();
	    }

	    if ($ERROR) {
		echo display_error();
	    }
	break;
	case 1 :
	default :
	$ONLOAD[]	= "setView(2)";

	if ($STUDENT) {
	?>
	    <div class="content-heading"><?php echo $clinical_rotation["title"];?></div>
	    <div style="float: right; margin-bottom: 0px">
		<ul class="page-action">
		    <li>
			<a href="<?php echo ENTRADA_URL."/clerkship/logbook?section=add";?>" class="strong-green">Log Patient Encounter</a>
		    </li>
		</ul>
	    </div>
	    <div style="clear: both"></div>
	<?php
	} else {  // Admin / Preceptor
	    $fullname = $db->GetOne("SELECT CONCAT_WS(' ', `firstname`, `lastname`) FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($PROXY_ID));
	?>
	    <div class="content-heading"><?php echo "$clinical_rotation[title]  - $fullname";?></div><br>
	<?php
	}
	?>

	    <div class="tab-pane" id="view-entry-tabs">
		<div class="tab-page" id="report">
		    <h2 class="tab">Report</h2>
		    <div class="content-heading"> Logbook Report</div>
		<?php
	// Collect mandatory objectives not seen within the rotation
		    $query  = "	SELECT  a.`objective_id`, b.`objective_name` 
				FROM `".CLERKSHIP_DATABASE."`.`logbook_mandatory_objectives` a
				INNER JOIN `".DATABASE_NAME."`.`global_lu_objectives` b 
				ON a.`objective_id` = b.`objective_id`
				WHERE a.`rotation_id` = ".$db->qstr($rotation_id)." 
				AND a.`objective_id` NOT IN
				(   
					SELECT distinct c.`objective_id` 
					FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_objectives` c, `".CLERKSHIP_DATABASE."`.`logbook_entries` d
				    WHERE d.`proxy_id` = ".$db->qstr($PROXY_ID)." 
					AND d.`entry_active` = 1 
					AND c.`lentry_id` = d.`lentry_id`
				)
				ORDER BY b.`objective_name`";
		    $results = $db->GetAll($query);

		    if ($results) {
			if ($ERROR) {
			    echo display_error();
			}
//	    <div class="content-heading">Missing Clinical Presentations [Objectives]</div>
		?>
		    <br />
		    <table class="tableList" cellspacing="0" summary="Missing Objectives">
			<colgroup>
			    <col class="phase" />
			</colgroup>
			<thead>
			    <tr>
				<td style="color:#F00">Missing Clinical Presentation</td>
			    </tr>
			</thead>
			<tbody>
			<?php
			    $objectives_not_recorded = 0;
			    foreach ($results as $result) {
				$click_url = ENTRADA_URL."/clerkship/logbook?core=$rotation_id&section=resource&id=$result[objective_id]";
				echo "<tr><td class=\"phase\"><a href=\"".$click_url."\" style=\"font-size: 11px\">".limit_chars(html_decode($result["objective_name"]), 55, true, false)."</a></td></tr>";
				$objectives_not_recorded++;
			    }
			?>
			</tbody>
		    </table>
		    <br />
		    <?php
		    }

	// Collect mandatory objectives seen within and without the rotation: 1 indicates within rotation, 2 indicates in other rotations
		    $query = "	SELECT a.`count`, b.`objective_name` objective, a.`ind`
				FROM (SELECT  count(c.`objective_id`) count, c.`objective_id` obj , 1 ind
				    FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_objectives` c,
				    `".CLERKSHIP_DATABASE."`.`logbook_entries` d
				    WHERE d.`proxy_id` = ".$db->qstr($PROXY_ID)." AND d.`entry_active` = 1 AND c.`lentry_id` = d.`lentry_id`
				    AND d.`rotation_id` IN (Select e.`event_id` FROM `".CLERKSHIP_DATABASE."`.`events` as e
					Inner Join `".CLERKSHIP_DATABASE."`.`categories` f On f.`category_id` = e.`category_id`
					where f.`rotation_id` = ".$db->qstr($rotation_id).")
				    AND c.`objective_id` IN (SELECT `objective_id` FROM `".CLERKSHIP_DATABASE."`.`logbook_mandatory_objectives`
					WHERE `rotation_id` = ".$db->qstr($rotation_id).") group by c.`objective_id`
				    UNION
				    SELECT  count(c.`objective_id`) count, c.`objective_id` obj, 2 ind
				    FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_objectives` c,
				    `".CLERKSHIP_DATABASE."`.`logbook_entries` d WHERE d.`proxy_id` = ".$db->qstr($PROXY_ID)." AND d.`entry_active` = 1 AND c.`lentry_id` = d.`lentry_id`
				    AND c.`objective_id` IN (SELECT `objective_id` FROM `".CLERKSHIP_DATABASE."`.`logbook_mandatory_objectives`
					WHERE `rotation_id` = ".$db->qstr($rotation_id).") AND c.`objective_id` NOT IN (SELECT distinct e.`objective_id`
					FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_objectives` e, `".CLERKSHIP_DATABASE."`.`logbook_entries` f
					    WHERE f.`proxy_id` = ".$db->qstr($PROXY_ID)." AND f.`entry_active` = 1 AND e.`lentry_id` = f.`lentry_id` AND f.`rotation_id` IN (Select g.`event_id`
					    FROM `".CLERKSHIP_DATABASE."`.`events` as g Inner Join `".CLERKSHIP_DATABASE."`.`categories` h
					    On h.`category_id` = g.`category_id` where h.`rotation_id` = ".$db->qstr($rotation_id).")) group by c.`objective_id`)as a
				Inner Join `".DATABASE_NAME."`.`global_lu_objectives` b
				ON a.`obj` = b.`objective_id` Order by count Desc, objective";
		    $results = $db->CacheGetAll(CACHE_TIMEOUT,$query);

		    if ($results) {
			if ($ERROR) {
			    echo display_error();
			}
//     <div class="content-heading">Mandatory Clinical Presentations Encountered</div>
			?>
			<br />
			<table class="tableList" cellspacing="0" summary="Mandatory Clinical Presentations Encountered">
			    <colgroup>
				<col class="modified" style="width:30px" />
				<col class="date" />
			    </colgroup>
			    <thead>
				<tr>
				    <td colspan="2" style="color:#080">Mandatory Clinical Presentations Encountered</td>
				</tr>
			    </thead>
			    <tbody>
			    <?php
				$other = false;
				foreach ($results as $result) {
				    if ($result["ind"]==2) {
					echo "<tr style=\"background-color:#FFB\"><td>$result[count]</td>";
					$other = true;
				    } else {
					echo "<tr><td>$result[count]</td>";
				    }
				    echo "<td>$result[objective]</td></tr>";
				}
			    ?>
			    </tbody>
			</table>
			<br />
		    <?php
			if ($other) {
			    echo "	<div style=\"background-color:#FFC; color:#666; -moz-border-radius:5px;
				    -webkit-border-radius:5px; #FC0 solid; margin:5px 20px; padding:5px; width:590px\">";
			    echo "Yellow indicates mandatory objectives seen in other rotations but not in $clinical_rotation[title].";
			    echo "</div>\n";
			}
		    }

	// Collect objectives seen within the rotation: 1 indicates mandatories, 2 indicates non mandatories
		    $query = "	SELECT a.`count`, b.`objective_name` objective, a.`ind` FROM (
				    SELECT  count(c.`objective_id`) count, c.`objective_id` obj , 1 ind
				    FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_objectives` c,
				    `".CLERKSHIP_DATABASE."`.`logbook_entries` d
				    WHERE d.`proxy_id` = ".$db->qstr($PROXY_ID)." AND d.`entry_active` = 1 AND c.`lentry_id` = d.`lentry_id`
				    AND d.`rotation_id` IN (Select e.`event_id` FROM `".CLERKSHIP_DATABASE."`.`events` as e
					Inner Join `".CLERKSHIP_DATABASE."`.`categories` f On f.`category_id` = e.`category_id`
					where f.`rotation_id` = ".$db->qstr($rotation_id).")
				    AND c.`objective_id` IN (SELECT `objective_id` FROM `".CLERKSHIP_DATABASE."`.`logbook_mandatory_objectives`
					WHERE `rotation_id` = ".$db->qstr($rotation_id).") group by c.`objective_id`
				    UNION
				    SELECT  count(c.`objective_id`) count, c.`objective_id` obj , 2 ind
				    FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_objectives` c,
				    `".CLERKSHIP_DATABASE."`.`logbook_entries` d
				    WHERE d.`proxy_id` = ".$db->qstr($PROXY_ID)." AND d.`entry_active` = 1 AND c.`lentry_id` = d.`lentry_id`
				    AND d.`rotation_id` IN (Select e.`event_id` FROM `".CLERKSHIP_DATABASE."`.`events` as e
					Inner Join `".CLERKSHIP_DATABASE."`.`categories` f On f.`category_id` = e.`category_id`
					where f.`rotation_id` = ".$db->qstr($rotation_id).")
				    AND c.`objective_id` NOT IN (SELECT `objective_id` FROM `".CLERKSHIP_DATABASE."`.`logbook_mandatory_objectives`
					WHERE `rotation_id` = ".$db->qstr($rotation_id).") group by c.`objective_id`) as a
				Inner Join `".DATABASE_NAME."`.`global_lu_objectives` b
				ON a.`obj` = b.`objective_id` Order by count Desc, objective";
		    $results = $db->GetAll($query);

		    if ($results) {
			if ($ERROR) {
			    echo display_error();
			}
//     <div class="content-heading">Mandatory Clinical Presentations Encountered</div>
		    ?>
		    <br />
		    <table class="tableList" cellspacing="0" summary="Clinical Presentations Encountered in $clinical_rotation[title]">
			<colgroup>
			    <col class="modified" style="width:30px" />
			    <col class="date" />
			</colgroup>
			<thead>
			    <tr>
				<td colspan="2" style="color:#444">Clinical Presentations Encountered in <?php echo $clinical_rotation["title"]?></td>
			    </tr>
			</thead>
			<tbody>
			<?php
			    $nonmandatories = false;
			    $objectives_recorded = 0;
			    foreach ($results as $result) {
				if ($result["ind"]==2) {
				    echo "<tr style=\"background-color:#FFB\"><td>$result[count]</td>";
				    $nonmandatories = true;
				} else {
				    echo "<tr><td>$result[count]</td>";
				}
				echo "<td>$result[objective]</td></tr>";
				$objectives_recorded++;
			    }
			?>
			</tbody>
		    </table>
		    <br />
		    <?php
			if ($nonmandatories) {
			    echo "	<div style=\"background-color:#FFC; color:#666; -moz-border-radius:5px;
				-webkit-border-radius:5px; #FC0 solid; margin:5px 20px; padding:5px; width:300px\">";
			    echo "Yellow indicates non mandatory objectives.";
			    echo "</div>\n";
			}
		    }

	// Collect procedures for the rotation
		    $query  = " SELECT count(c.`procedure`) count, c.`procedure`,
				(CASE a.`level` WHEN 3 THEN 'Performed Independently' WHEN 2 THEN 'Assisted' ELSE 'Observed' END) part
				FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_procedures` a,
				`".CLERKSHIP_DATABASE."`.`logbook_entries` b,
				`".CLERKSHIP_DATABASE."`.`logbook_lu_procedures` c
				WHERE b.`proxy_id` = ".$db->qstr($PROXY_ID)." AND b.`entry_active` = 1 AND a.`lentry_id` = b.`lentry_id` AND b.`rotation_id` IN
				(   Select c.`event_id` FROM `".CLERKSHIP_DATABASE."`.`events` c
				    Inner Join `".CLERKSHIP_DATABASE."`.`categories` d On d.`category_id` = c.`category_id` where d.`rotation_id` = ".$db->qstr($rotation_id).")
				AND c.`lprocedure_id` = a.`lprocedure_id`
				GROUP BY c.`procedure` ORDER BY c.`procedure`, count desc, part desc";
		    $results = $db->GetAll($query);

		    if ($results) {
			if ($ERROR) {
			    echo display_error();
			}

			?>
			<br />
			<table class="tableList" cellspacing="0" summary="Procedures List">
			    <colgroup>
				<col class="modified" style="width:30px" />
				<col class="date" />
				<col class="date" />
			    </colgroup>
			    <thead>
				<tr>
				    <td colspan="2" style="color:#008">Procedures</td>
				    <td style="color:#008">level</td>
				</tr>
			    </thead>
			    <tbody>
			    <?php
				foreach ($results as $result) {
				    echo "<tr><td>$result[count]</td>";
				    echo "<td>$result[procedure]</td><td>$result[part]</td></tr>";
				}
			    ?>
			    </tbody>
			</table>
			<br />
		<?php
		    }

    // Patient follow ups
		    $query  = " SELECT `count`, `patient_info` FROM
				(   SELECT count(`patient_info`) as `count`, `patient_info`
				    FROM `".CLERKSHIP_DATABASE."`.`logbook_entries`
				    WHERE `proxy_id` = ".$db->qstr($PROXY_ID)." AND `entry_active` = 1 AND `rotation_id` IN
				    (	Select a.`event_id` FROM `".CLERKSHIP_DATABASE."`.`events` a
					INNER JOIN `".CLERKSHIP_DATABASE."`.`categories` b On b.`category_id` = a.`category_id` where b.`rotation_id` = ".$db->qstr($rotation_id).")
				GROUP BY `patient_info`) t1
				WHERE `count` > 1 ORDER BY `count` DESC, `patient_info`";  // Count should be greater than 1 or 2
		    $results = $db->GetAll($query);

		    if ($results) {
			if ($ERROR) {
			    echo display_error();
			}
		?>
			<br />
			<table class="tableList" cellspacing="0" summary="Patient Follow-ups">
			    <colgroup>
				<col class="modified" style="width:30px" />
				<col class="date" />
			    </colgroup>
			    <thead>
				<tr>
				    <td colspan="2" style="color:#088">Follow-up Patient</td>
				</tr>
			    </thead>
			    <tbody>
			<?php
				foreach ($results as $result) {
				    echo "<tr><td>$result[count]</td>";
				    echo "<td>$result[patient_info]</td></tr>";
				}
			?>
			    </tbody>
			</table>
			<br />&nbsp;<br />

		    <?php
		    }

    // View entries
		    ?>
	    </div>
	    <div class="tab-page">
		<h2 class="tab">View Entries</h2>
		<div class="content-heading"> Logged Entries</div>

    <script type="text/javascript">
    var view = 0;
    var rev = 0;
  
    function setView(v){
	if (v == 1)
	    if (view == 1)  // Toggle Patient
		rev = rev ? 0 : 1;
	    else {	    // Reset to Patient order
		view = 1;
	    }
	else if (view != 2) {  //Reset to Date order
	    view = 2;
	} else
	    rev = rev ? 0 : 1; // Toggle Date
	new Ajax.Updater('ajax-view-body','<?php echo ENTRADA_URL."/api/view-entries.api.php" ?>', {
	    parameters: { 'proxy' : <?php echo $PROXY_ID; ?> , 'rot' : '<?php echo $rotation_id; ?>', 'view' : view, 'rev' : rev }
	});
    }
    </script>
    <?php
    // Count of entries entered in this rotation
	$query  = " SELECT COUNT(*) FROM `".CLERKSHIP_DATABASE."`.`logbook_entries` WHERE `proxy_id` = ".$db->qstr($PROXY_ID)." AND `entry_active` = 1 AND
		    `rotation_id` IN (Select e.`event_id` FROM `".CLERKSHIP_DATABASE."`.`events` as e
		    Inner Join `".CLERKSHIP_DATABASE."`.`categories` c On c.`category_id` = e.`category_id` where c.`rotation_id` = ".$db->qstr($rotation_id).")";
	$entryCount = $db->GetOne($query);

	if ($entryCount) {
	    if ($ERROR) {
		echo display_error();
	    }
//     <div class="content-heading">Entries View</div>
	?>
	    <br />
	    <table class="tableList" cellspacing="0" summary="Entries View">
		<colgroup>
		    <col style="width: 20%"/>
		    <col style="width: 9%"/>
		    <col style="width: 11%"/>
		    <col style="width: 24%"/>
		    <col style="width: 36%"/>
		</colgroup>
<!--		    <td colspan="5" class="sortedASC" style="color:#06F"><?php echo "Logged ". ($entryCount > 1?" Entries  ($entryCount)":" Entry"); ?></td>
-->		<thead>
		    <tr>
			<td><a href="javascript:setView(1)">Patient<?php echo ($entryCount > 1?"s ($entryCount)":""); ?></a></td>
			<td><a href="javascript:setView(2)">Date</a></td>
			<td>Gender/Age</td>
			<td>Location</td>
			<td>Comments</td>
		    </tr>
		</thead>
	    </table>
	    <div id="ajax-view-body"></div>
    <?php
	}
    ?>
	</div>
	<div class="tab-page">
	    <h2 class="tab">Summary</h2>
		<div class="content-heading">Logbook Summary / Feedback</div>
		<br />

		<div style="width: 80%;">
		<?php if (!$STUDENT) { ?>
		    <?php echo $fullname; ?> has logged <?php echo $objectives_recorded; ?> of the <?php echo $objectives_recorded + $objectives_not_recorded; ?> required <strong>Clinical Presentations</strong> and <?php echo $procedures_recorded; ?> of the <?php echo $procedures_required; ?> required <strong>Clinical Tasks</strong> for this rotation.
		<?php } else { ?>
		    You have logged <?php echo $objectives_recorded; ?> of the <?php echo $objectives_recorded + $objectives_not_recorded; /*echo $objectives_required;*/ ?> required <strong>Clinical Presentations</strong> and <?php echo $procedures_recorded; ?> of the <?php echo $procedures_required; ?> required <strong>Clinical Tasks</strong> for this rotation.
		<?php } ?>
		</div>
		<br />

		<div class="no-printing">
		    <h3>Comments/Notes:</h3>
		    <form method="POST" action="<?php echo ENTRADA_URL."/clerkship/logbook?".replace_query(array("step" => 2)); ?>" >
			<div class="content-small">If you are unable to complete all requirements for this rotation, or have any other issues or concerns, please write a note here for the Preceptor as to how you plan or suggest to resolve the issue.</div>
			<textarea class="expandable" id="comments" name="comments"><?php echo html_encode($PROCESSED["comments"]); ?></textarea>
			<div style="padding-top: 10px;">
			    <input type="submit" value="Submit Comments" />
			</div>
		    </form>
		</div>
		<div <?php echo (isset($PROCESSED["comments"]) && $PROCESSED["comments"] ? "class=\"print-only\"" : "style=\"display: none;\""); ?>>
		    <br />
		    <h2>Comments/Notes:</h2>
		    <div>
			<?php echo html_encode($PROCESSED["comments"]); ?>
		    </div>
		</div>
		    <br />&nbsp;<br />
	</div>
    <?php
	if (!$STUDENT && $rotation_id) {
	    ?>
	    <div class="tab-page">
		<h2 class="tab">Schedule</h2>
		<div class="content-heading">Schedule</div>
		<br />
		<div style="width: 95%;">
		<?php
		    $query = "	SELECT b.`event_start`, b.`event_finish`, b.`event_title`, d.`region_name`, d.`region_city`, d.`region_prov`, c.`category_type`
				FROM `".CLERKSHIP_DATABASE."`.`event_contacts` a
				INNER JOIN `".CLERKSHIP_DATABASE."`.`events` b ON a.`event_id` = b.`event_id`
				INNER JOIN `".CLERKSHIP_DATABASE."`.`categories` c On b.`category_id` = c.`category_id`
				INNER JOIN `".CLERKSHIP_DATABASE."`.`regions_uc` d On b.`region_id` = d.`region_id`
				WHERE a.`etype_id` = ".$db->qstr($_GET["id"])." and c.`rotation_id` = ".$db->qstr($rotation_id)." 
				ORDER BY b.`event_start`";
		    $results = $db->GetAll($query);
		    if ($results) {
			?>
			<table class="tableList" cellspacing="0" summary="Missing Objectives">
			    <colgroup>
				<col style="width: 12%" />
				<col style="width: 12%" />
				<col style="width: 34%" />
				<col style="width: 42%" />
			    </colgroup>
			    <thead>
				<tr>
				    <td>Start</td>
				    <td>Finish</td>
				    <td>Unit</td>
				    <td>Location</td>
				</tr>
			    </thead>
			    <tbody>
			    <?php
				$elective = false;
				foreach ($results as $result) {
				    echo "<tr class=\"details\">";
				    if ($result["category_type"]==31) {
					echo    "<td>".date("M d/y", $result["event_start"])." *</td>";
					$elective = true;
				    } else {
					echo    "<td>".date("M d/y", $result["event_start"])."</td>";
				    }
				    echo    "<td>".date("M d/y", $result["event_finish"])."</td>";
				    echo    "<td>$result[event_title]</td>";
				    echo    "<td>$result[region_name]</td></tr>";
				}
				if ($elective) {
				    echo "</tbody><tbody><tr class=\"details\"><td>&nbsp;</td><td colspan=3>* indicates an elective.</td></tr>";
				}
			    ?>
			    </tbody>
			</table>
	    <?php  } ?>
	    </div>
	<?php } ?>
    </div>
    <?php if ($STUDENT) { ?>
	<br>
	<div style="float: right; margin-bottom: 0px">
	    <img width="15px" height="15px" src="<?php echo ENTRADA_URL; ?>/images/icon-lecture-notes-on.gif" style="padding-right: 5px; vertical-align: bottom;" />
	    <strong><a style="font-size: 11px;" href="<?php echo ENTRADA_URL."/clerkship/logbook?section=checklist&core=$rotation_id\""; ?>>Rotation Checklist</a></strong>
	</div>
    <?php
	}
    break;
    }
}
?>
