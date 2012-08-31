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
 * @author Developer: James Ellis <james.ellis@queensu.ca>
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

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/public/clerkship/logbook?section=view", "title" => "View Patient Encounters");
	
    if(isset($_GET["core"])) {
		$rotation_id = clean_input($_GET["core"], "int");
    } else {
		$rotation_id = 0;
    }
    $clinical_rotation	 = clerkship_get_rotation($rotation_id);
    
    if (isset($_GET["id"]) && $_GET["id"]) {
    	$PROXY_ID = $_GET["id"];
    	$student = false;
    } else {
    	$PROXY_ID = $_SESSION["details"]["id"];
    	$student = true;
    }
    
    if (!$student) {
    	$accessible_rotation_ids = clerkship_rotations_access();
    }
	if (($student && $_SESSION["details"]["group"] == "student") || array_search($rotation_id, $accessible_rotation_ids) !== false) {
		// Error Checking
		switch ($STEP) {
			case 2 :
				if (isset($_POST["discussion_comment"]) && ($new_comments = clean_input($_POST["discussion_comment"], array("trim", "notags")))) {
					$PROCESSED["comments"] = $new_comments;
					$PROCESSED["clerk_id"] = $PROXY_ID;
					$PROCESSED["proxy_id"] = $_SESSION["details"]["id"];
					$PROCESSED["rotation_id"] = $rotation_id;
					$PROCESSED["updated_date"] = time();
					
					if ($db->AutoExecute(CLERKSHIP_DATABASE.".logbook_rotation_comments", $PROCESSED, "INSERT")) {
						$SUCCESS++;
						$SUCCESSSTR[] = "You have succesfully added a comment to this rotation".($student ? "" : " for ".get_account_data("firstlast", $PROXY_ID)).".";
					} else {
						$ERROR++;
						$ERRORSTR[] = "There was an issue while attempting to add your comment to the system. <br /><br />If you if this error persists, please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";
						application_log("error", "There was an error adding a clerkship rotation comment entry. Database said: ".$db->ErrorMsg());
					}
				}
				$STEP = 1;
			break;
			case 1 :
			default :
				continue;
			break;
		}
		
		// Display Content
		switch ($STEP) {
			case 2 :
			break;
			case 1 :
			default :
			    $clinical_rotation	 = clerkship_get_rotation($rotation_id);
			    $fullname = $db->GetOne("SELECT CONCAT_WS(' ', `firstname`, `lastname`) FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($PROXY_ID));
			    echo "<h1>".$clinical_rotation["title"]." Rotation Patient Encounters Report</h1>\n";
			    echo "<h2 style=\"border: none;\">For: ".$fullname."</h2>";
			
				if ($SUCCESS) {
					echo display_success();
				}
						
				if ($NOTICE) {
					echo display_notice();
				}
						
				if ($ERROR) {
					echo display_error();
				}
				// Collect objectives seen within the rotation: 1 indicates mandatories, 2 indicates non mandatories
				
				$query = "	SELECT COUNT(a.`objective_id`) AS `count`, a.`objective_id`, e.`number_required` AS `required`, f.`objective_name` AS `objective`
							FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_objectives` AS a
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`logbook_entries` AS b
							ON b.`lentry_id` = a.`lentry_id`
							AND b.`entry_active` = 1
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`events` AS c
							ON b.`rotation_id` = c.`event_id`
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`logbook_mandatory_objectives` AS e
							ON e.`rotation_id` = c.`rotation_id`
							AND e.`objective_id` = a.`objective_id`
							LEFT JOIN `global_lu_objectives` AS f
							ON f.`objective_id` = a.`objective_id`
							JOIN `objective_organisation` AS g
							ON f.`objective_id` = g.`objective_id`
							AND g.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
							WHERE c.`rotation_id` = ".$db->qstr($rotation_id)."
							AND b.`proxy_id` = ".$PROXY_ID."
							AND f.`objective_active` = '1'
							GROUP BY a.`objective_id`";
				$results = $db->GetAll($query);
			    if ($results) {
					?>
					<br />
					<table class="tableList" cellspacing="0" summary="Clinical Presentations Encountered in <?php echo $clinical_rotation["title"]; ?>">
					    <colgroup>
							<col style="width:3%" />
							<col style="width:67%;"/>
							<col style="width:15%;"  />
							<col style="width:15%;" />
					    </colgroup>
					    <thead>
						<tr>
						    <td colspan="2">Clinical Presentations Encountered in <?php echo $clinical_rotation["title"]; ?></td>
						    <td style="border-left: none;">Logged</td>
						    <td style="border-left: none;">Required</td>
						</tr>
					    </thead>
					    <tbody>
					    <?php
						foreach ($results as $result) {
							echo "<tr>";
							echo "<td>&nbsp;</td>";
						    echo "<td>".$result["objective"]."</td>";
							echo "<td>".$result["count"]."</td>";
						    echo "<td>".$result["required"]."</td>";
						    echo "</tr>";
						}
					    ?>
					    </tbody>
					</table>
				    <br />
				    <?php
			    }
				$query = "	SELECT COUNT(a.`objective_id`) AS `count`, a.`objective_id`, e.`objective_name` AS `objective`, f.`number_required` as `required`
							FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_objectives` AS a
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`logbook_entries` AS b
							ON b.`lentry_id` = a.`lentry_id`
							AND b.`entry_active` = 1
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`events` AS c
							ON b.`rotation_id` = c.`event_id`
							LEFT JOIN `global_lu_objectives` AS e
							ON e.`objective_id` = a.`objective_id`
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`logbook_mandatory_objectives` AS f
							ON e.`objective_id` = f.`objective_id`
							AND f.`rotation_id` = ".$db->qstr($rotation_id)."
							JOIN `objective_organisation` AS g
							ON e.`objective_id` = g.`objective_id`
							AND g.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
							WHERE b.`proxy_id` = ".$PROXY_ID."
							AND c.`rotation_id` != ".$db->qstr($rotation_id)."
							AND e.`objective_active` = '1'
							AND a.`objective_id` IN (
								SELECT `objective_id` FROM `".CLERKSHIP_DATABASE."`.`logbook_mandatory_objectives` WHERE `rotation_id` = ".$db->qstr($rotation_id)."
							)
							GROUP BY a.`objective_id`";
				$results = $db->GetAll($query);
			    if ($results) {
					?>
					<br />
					<table class="tableList" cellspacing="0" summary="Clinical Presentations for <?php echo $clinical_rotation["title"]; ?> encountered in other rotations">
					    <colgroup>
							<col style="width:3%" />
							<col style="width:67%;"/>
							<col style="width:15%;"  />
							<col style="width:15%;" />
					    </colgroup>
					    <thead>
						<tr>
						    <td colspan="2">Clinical Presentations for <?php echo $clinical_rotation["title"]; ?> encountered in other rotations</td>
						    <td style="border-left: none;">Logged</td>
						    <td style="border-left: none;">Required</td>
						</tr>
					    </thead>
					    <tbody>
					    <?php
						foreach ($results as $result) {
							echo "<tr>";
							echo "<td>&nbsp;</td>";
						    echo "<td>".$result["objective"]."</td>";
							echo "<td>".$result["count"]."</td>";
							echo "<td>".$result["required"]."</td>";
						    echo "</tr>";
						}
					    ?>
					    </tbody>
					</table>
				    <br />
				    <?php
			    }
				$query = "	SELECT COUNT(a.`lprocedure_id`) AS `count`, a.`lprocedure_id`, e.`number_required` AS `required`, f.`procedure`, a.`level`
							FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_procedures` AS a
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`logbook_entries` AS b
							ON b.`lentry_id` = a.`lentry_id`
							AND b.`entry_active` = 1
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`events` AS c
							ON b.`rotation_id` = c.`event_id`
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`logbook_preferred_procedures` AS e
							ON e.`rotation_id` = c.`rotation_id`
							AND e.`lprocedure_id` = a.`lprocedure_id`
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`logbook_lu_procedures` AS f
							ON f.`lprocedure_id` = a.`lprocedure_id`
							WHERE c.`rotation_id` = ".$db->qstr($rotation_id)."
							AND b.`proxy_id` = ".$PROXY_ID."
							GROUP BY a.`lprocedure_id`";
			    $results = $db->GetAll($query);
			
			    if ($results) {
				//     <div class="content-heading">Procedures List</div>
					?>
					<br />
					<table class="tableList" cellspacing="0" summary="Procedures List">
					    <colgroup>
							<col style="width:3%" />
							<col style="width:67%;"/>
							<col style="width:15%;"  />
							<col style="width:15%;" />
					    </colgroup>
					    <thead>
						<tr>
						    <td colspan="2">Tasks Completed in <?php echo $clinical_rotation["title"]?></td>
						    <td style="border-left: none;">Logged</td>
						    <td style="border-left: none;">Required</td>
						</tr>
					    </thead>
					    <tbody>
					    <?php
						foreach ($results as $result) {
						    echo "<tr>";
						    echo "<td>&nbsp;</td>";
						    echo "<td>".$result["procedure"]."</td>";
						    echo "<td>".$result["count"]."</td>";
						    echo "<td>".$result["required"]."</td>";
						    echo "</tr>";
						}
					    ?>
					    </tbody>
					</table>
				    <br />
				    <?php
			    }
				$query = "	SELECT COUNT(a.`lprocedure_id`) AS `count`, a.`lprocedure_id`, e.`procedure`, f.`number_required`
							FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_procedures` AS a
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`logbook_entries` AS b
							ON b.`lentry_id` = a.`lentry_id`
							AND b.`entry_active` = 1
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`events` AS c
							ON b.`rotation_id` = c.`event_id`
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`logbook_lu_procedures` AS e
							ON e.`lprocedure_id` = a.`lprocedure_id`
							LEFT JOIN `".CLERKSHIP_DATABASE."`.`logbook_preferred_procedures` AS f
							ON e.`lprocedure_id` = f.`lprocedure_id`
							AND c.`rotation_id` = f.`rotation_id`
							WHERE b.`proxy_id` = ".$PROXY_ID."
							AND c.`rotation_id` != ".$db->qstr($rotation_id)."
							AND a.`lprocedure_id` IN 
							(
								SELECT `lprocedure_id` FROM `".CLERKSHIP_DATABASE."`.`logbook_preferred_procedures` WHERE `rotation_id` = ".$db->qstr($rotation_id)."
							)
							GROUP BY a.`lprocedure_id`";
			    $results = $db->GetAll($query);
			
			    if ($results) {
				//     <div class="content-heading">Procedures List</div>
					?>
					<br />
					<table class="tableList" cellspacing="0" summary="Procedures List">
					    <colgroup>
							<col style="width:3%" />
							<col style="width:67%;"/>
							<col style="width:15%;"  />
							<col style="width:15%;" />
					    </colgroup>
					    <thead>
						<tr>
						    <td colspan="2">Tasks Completed for <?php echo $clinical_rotation["title"]?> in other rotations</td>
						    <td style="border-left: none;">Logged</td>
						    <td style="border-left: none;">Required</td>
						</tr>
					    </thead>
					    <tbody>
					    <?php
						foreach ($results as $result) {
						    echo "<tr>";
						    echo "<td>&nbsp;</td>";
						    echo "<td>".$result["procedure"]."</td>";
						    echo "<td>".$result["count"]."</td>";
						    echo "<td>".$result["number_required"]."</td>";
						    echo "</tr>";
						}
					    ?>
					    </tbody>
					</table>
				    <br />
				    <?php
			    }
			    $procedures_required = 0;
			    $objectives_required = 0;
			    $objectives_recorded = 0;
			    $procedures_recorded = 0;
			    
				$query = "	SELECT `objective_id`, MAX(`number_required`) AS `required`
							FROM `".CLERKSHIP_DATABASE."`.`logbook_mandatory_objectives`
							WHERE `rotation_id` = ".$db->qstr($rotation_id)."
							GROUP BY `objective_id`";
				$required_objectives = $db->GetAll($query);
				if ($required_objectives) {
					foreach ($required_objectives as $required_objective) {
						$objectives_required += $required_objective["required"];
						$number_required[$required_objective["objective_id"]] = $required_objective["required"];
						$query = "SELECT COUNT(`objective_id`) AS `recorded`
								FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_objectives`
								WHERE `lentry_id` IN
								(
									SELECT `lentry_id` FROM `".CLERKSHIP_DATABASE."`.`logbook_entries`
									WHERE `entry_active` = '1' 
									AND `proxy_id` = ".$db->qstr($PROXY_ID)."
								)
								AND `objective_id` = ".$db->qstr($required_objective["objective_id"])."
								GROUP BY `objective_id`";
						$recorded = $db->GetOne($query);
						
						if ($recorded) {
							if ($required_objective["required"] > $recorded) {
								if ($objective_ids) {
									$objective_ids .= ",".$db->qstr($required_objective["objective_id"]);
								} else {
									$objective_ids = $db->qstr($required_objective["objective_id"]);
								}
								$number_required[$required_objective["objective_id"]] -= $recorded;
							}
							$objectives_recorded += ($recorded <= $required_objective["required"] ? $recorded : $required_objective["required"]);
						} else {
							if ($objective_ids) {
								$objective_ids .= ",".$db->qstr($required_objective["objective_id"]);
							} else {
								$objective_ids = $db->qstr($required_objective["objective_id"]);
							}
						}
					}
				}
				if (isset($objective_ids) && count(explode(",", $objective_ids))) {
				    $query  = "SELECT a.* FROM `".DATABASE_NAME."`.`global_lu_objectives` AS a
								JOIN `objective_organisation` AS b
								ON a.`objective_id` = b.`objective_id`
								AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
								WHERE a.`objective_id` IN	(".$objective_ids.")
								AND a.`objective_active` = '1'
								ORDER BY a.`objective_name`";
				    $results = $db->GetAll($query);
				} else {
					$results = false;
				}
			
			    if ($results) {
					?>
					<br />
					<table class="tableList" cellspacing="0" summary="Missing Objectives">
					    <colgroup>
						<col style="width: 3%;"/>
						<col style="width: 82%;"/>
						<col style="width: 15%;"/>
						<col/>
					    </colgroup>
					    <thead>
						<tr>
						    <td colspan="2">Missing Clinical Presentations</td>
						    <td style="border-left: none;">Number Missing</td>
						</tr>
					    </thead>
					    <tbody>
					<?php
						foreach ($results as $result) {
						    $click_url	= ENTRADA_URL."/clerkship?core=".$rotation_id;
						    echo "<tr>";
						    echo "<td>&nbsp;</td>";
						    echo "<td>".($result["objective_name"] ? limit_chars(html_decode($result["objective_name"]), 55, true, false) : "&nbsp;")."</td>";
						    echo "<td>".$number_required[$result["objective_id"]]."</td>";
						    echo "</tr>";
						}
					    ?>
						</tbody>
					</table>
				    <br />
				    <?php
			    }
				$query = "	SELECT `lprocedure_id`, MAX(`number_required`) AS `required`
							FROM `".CLERKSHIP_DATABASE."`.`logbook_preferred_procedures`
							WHERE `rotation_id` = ".$db->qstr($rotation_id)."
							GROUP BY `lprocedure_id`";
				$required_procedures = $db->GetAll($query);
				if ($required_procedures) {
					foreach ($required_procedures as $required_procedure) {
						$procedures_required += $required_procedure["required"];
						$number_required[$required_procedure["lprocedure_id"]] = $required_procedure["required"];
						$query = "SELECT COUNT(`lprocedure_id`) AS `recorded`
								FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_procedures`
								WHERE `lentry_id` IN
								(
									SELECT `lentry_id` FROM `".CLERKSHIP_DATABASE."`.`logbook_entries`
									WHERE `entry_active` = '1' 
									AND `proxy_id` = ".$db->qstr($PROXY_ID)."
								)
								AND `lprocedure_id` = ".$db->qstr($required_procedure["lprocedure_id"])."
								GROUP BY `lprocedure_id`";
						$recorded = $db->GetOne($query);
						
						if ($recorded) {
							if ($required_procedure["required"] > $recorded) {
								if ($procedure_ids) {
									$procedure_ids .= ",".$db->qstr($required_procedure["lprocedure_id"]);
								} else {
									$procedure_ids = $db->qstr($required_procedure["lprocedure_id"]);
								}
								$number_required[$required_procedure["lprocedure_id"]] -= $recorded;
							}
							$procedures_recorded += ($recorded <= $required_procedure["required"] ? $recorded : $required_procedure["required"]);
						} else {
							if (isset($procedure_ids) && $procedure_ids) {
								$procedure_ids .= ",".$db->qstr($required_procedure["lprocedure_id"]);
							} else {
								$procedure_ids = $db->qstr($required_procedure["lprocedure_id"]);
							}
						}
					}
				}
			    $query  = "	SELECT * FROM `".CLERKSHIP_DATABASE."`.`logbook_lu_procedures`
							WHERE `lprocedure_id` IN
							(".$procedure_ids.")
							ORDER BY `procedure`";
			    $results = $db->GetAll($query);

			    if ($results) {
					?>
					<br />
					<table class="tableList" cellspacing="0" summary="Missing procedures">
					    <colgroup>
						<col style="width: 3%;"/>
						<col style="width: 82%;"/>
						<col style="width: 15%;"/>
					    </colgroup>
					    <thead>
						<tr>
						    <td colspan="2">Missing Clinical Tasks</td>
						    <td style="border-left: none;">Number Missing</td>
						</tr>
					    </thead>
					    <tbody>
					<?php
						foreach ($results as $result) {
						    $click_url	= ENTRADA_URL."/clerkship?core=".$rotation_id;
						    echo "<tr>";
						    echo "<td>&nbsp;</td>";
						    echo "<td class=\"phase\">".limit_chars(html_decode($result["procedure"]), 55, true, false)."</td>";
						    echo "<td>".$number_required[$result["lprocedure_id"]]."</td>";
						    echo "</tr>";
						}
					    ?>
						</tbody>
					</table>
				    <br />
				    <?php
			    }
			    // Patient follow ups
			    $query  = "	SELECT `count`, `patient_info` FROM
					(SELECT count(`patient_info`) as `count`, `patient_info`
					FROM `".CLERKSHIP_DATABASE."`.`logbook_entries`
					WHERE `proxy_id` = ".$db->qstr($PROXY_ID)." AND `rotation_id` IN
					    (Select a.`event_id` FROM `".CLERKSHIP_DATABASE."`.`events` a
					    where a.`rotation_id` = ".$db->qstr($rotation_id).")
					    GROUP BY `patient_info`) t1
					WHERE `count` = 1 ORDER BY `count` desc, `patient_info`";
			    $results = $db->GetAll($query);
			
			    if ($results) {
					if ($ERROR) {
					    echo display_error();
					}
			//     <div class="content-heading">Procedures List</div>
				?>
				<br />
				<table class="tableList" cellspacing="0" summary="Patient Follow-ups">
				    <colgroup>
					<col class="modified" style="width:30px" />
					<col class="date" />
				    </colgroup>
				    <thead>
					<tr>
					    <td colspan="2">Follow-up Patient</td>
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
			    <br />
			    <?php
			    }
				?>
				<h3>Summary</h3>
				<div style="width: 80%;">
					<?php if (isset($_GET["id"]) && $_GET["id"]) { ?>
						<?php echo $fullname; ?> has logged <?php echo $objectives_recorded; ?> of the <?php echo $objectives_required; ?> required <strong>Clinical Presentations</strong> and <?php echo $procedures_recorded; ?> of the <?php echo $procedures_required; ?> required <strong>Clinical Tasks</strong> for this rotation. 
					<?php } else { ?>
						You have logged <?php echo $objectives_recorded; ?> of the <?php echo $objectives_required; ?> required <strong>Clinical Presentations</strong> and <?php echo $procedures_recorded; ?> of the <?php echo $procedures_required; ?> required <strong>Clinical Tasks</strong> for this rotation. 
					<?php } ?>
				</div>
				<br/>
				<?php
				echo "<h2 title=\"Rotation Comments Section\">Discussions &amp; Comments</h2>\n";
				echo "<div id=\"rotation-comments-section\">\n";
	
				$query = "	SELECT * FROM `".CLERKSHIP_DATABASE."`.`logbook_rotation_comments`
							WHERE `clerk_id` = ".$db->qstr($PROXY_ID)."
							AND `comments` <> ''
							AND `comment_active` = '1'
							AND `rotation_id` = ".$db->qstr($rotation_id)."
							ORDER BY `lrcomment_id` ASC";
				
				$ROTATION_DISCUSSION = $db->GetAll($query);
				
				$editable	= false;
				$edit_ajax	= array();
				if($ROTATION_DISCUSSION) {
					$i = 0;
					foreach($ROTATION_DISCUSSION as $result) {
						$poster_name = get_account_data("firstlast", $result["proxy_id"]);
	
						echo "<div class=\"discussion\"".(($i % 2) ? " style=\"background-color: #F3F3F3\"" : "").">\n";
						echo "	<div class=\"content-small\"><strong>".get_account_data("firstlast", $result["proxy_id"])."</strong>, ".date(DEFAULT_DATE_FORMAT, $result["updated_date"])."</div>\n";
						echo "	<div class=\"discussion-comment\" id=\"discussion_comment_".$result["lrcomment_id"]."\">".html_encode($result["comments"])."</div>\n";
						echo "</div>\n";
	
						$i++;
					}
				} else {
					echo "<div class=\"content-small\">There are no comments or discussions on this event. <strong>Start a conversation</strong>, leave your comment below.</div>\n";
				}
				echo "	<br /><br />";
				echo "	<div class=\"no-printing\">\n";
				echo "		<form action=\"".ENTRADA_URL."/clerkship/logbook?".replace_query(array("step" => 2))."\" method=\"post\">\n";
				echo "			<label for=\"discussion_comment\" class=\"content-subheading\">Leave a Comment</label>\n";
				echo "			<div class=\"content-small\">Posting comment as <strong>".$_SESSION["details"]["firstname"]." ".$_SESSION["details"]["lastname"]."</strong></div>\n";
				echo "			<textarea id=\"discussion_comment\" name=\"discussion_comment\" cols=\"85\" rows=\"10\" style=\"width: 100%; height: 135px\"></textarea>\n";
				echo "			<div style=\"text-align: right; padding-top: 8px\"><input type=\"submit\" class=\"button\" value=\"Submit\" /></div>\n";
				echo "		</form>\n";
				echo "	</div>\n";
				echo "</div>\n";
			break;
		}
	} else {
		$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";
	
		$ERROR++;
		$ERRORSTR[]	= "Your account does not have the permissions required to view clerk information for this rotation.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";
	
		echo display_error();
	
		application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this rotation [".$rotation_id."] in this module [".$MODULE."]");
	}
}
?>