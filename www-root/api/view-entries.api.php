<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Display Clerkship logbook entries in various order.
 *
 * @author Organisation: University of Calgary
 * @author Unit: Undergraduate Medical Education
 * @author Developer: Doug Hall <hall@ucalgary.ca>
 * @copyright Copyright 2009 University of Calgary. All Rights Reserved.
 *
 * $Id: view-entries.api.php 1 2009-11-20 19:36:06Z hall $
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

if((isset($_SESSION["isAuthorized"])) && ((bool) $_SESSION["isAuthorized"])) {

    $rotation_id = ((isset($_POST["rot"])) ? (int) trim($_POST["rot"]) : 0);
    $proxy_id = ((isset($_POST["proxy"])) ? (int) trim($_POST["proxy"]) : 0);
    $reverse = ((isset($_POST["rev"])) ? (int) trim($_POST["rev"]) : 0);
    $view = ((isset($_POST["view"])) ? (int) trim($_POST["view"]) : 0);

// View entries
    if (!$view) return ;

    $query  = " SELECT  a.`lentry_id`, a.`patient_info`, from_unixtime(CASE WHEN a.`updated_date` > 2000000000 THEN a.`updated_date` - 2082823200 ELSE a.`updated_date` END,'%b%e %Hh') `date`,  a.`agerange_id`,
		CASE WHEN a.`gender`='m' THEN 'M' WHEN a.`gender`='f' THEN 'F' WHEN a.`gender`=0 THEN '' ELSE a.`gender` END as gender, a.`participation_level`,
		a.`rotation_id`,  b.`location`, a.`comments`
		FROM `".CLERKSHIP_DATABASE."`.`logbook_entries` a,
		`".CLERKSHIP_DATABASE."`.`logbook_lu_locations` b
		WHERE `proxy_id` = ".$db->qstr($proxy_id)." AND a.`entry_active` = 1 AND a.`rotation_id`
		IN (Select a.`event_id` FROM `".CLERKSHIP_DATABASE."`.`events` a
		INNER JOIN `".CLERKSHIP_DATABASE."`.`categories` b On b.`category_id` = a.`category_id` where b.`rotation_id` = ".$db->qstr($rotation_id).")
		AND a.`llocation_id` = b.`llocation_id`  Order by ";
    switch ($view) {
	case 1 :    // Select by patient info order
	    $query .= "a.`patient_info` ".($reverse?"DESC":"ASC").", a.`updated_date`";
	    break;
	case 2 :  // Select by date order
	    $query  .= "a.`updated_date` ".($reverse?"DESC":"ASC").", a.`patient_info`";
	    break;
	default :
	    return "";
	    break;
    }
    $entries = $db->GetAll($query);

    if ($entries) {
	if ($ERROR) {
	    echo display_error();
	}
//     <div class="content-heading">Entries View</div>
	?>
	    <table class="tableList" cellspacing="0" summary="Entries View Body">
		<colgroup>
		    <col style="width: 20%"/>
		    <col style="width: 9%"/>
		    <col style="width: 11%"/>
		    <col style="width: 18%"/>
		    <col style="width: 6%"/>
		    <col style="width: 36%"/>
		</colgroup>
		<?php
		    $toggle = 0; $participation = 0;
		foreach ($entries as $entry) {

		    $participation |=  $part = $entry["participation_level"] > 1; // Display 'participation level' indicator
		    
		    $query = "	SELECT b.`objective_name` obj FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_objectives` AS a
				INNER JOIN `".DATABASE_NAME."`.`global_lu_objectives` AS b
				ON a.`objective_id` = b.`objective_id`
				INNER JOIN `".DATABASE_NAME."`.`objective_organisation` AS c
				ON b.`objective_id` = c.`objective_id
				WHERE a.`lentry_id` = ".$db->qstr($entry["lentry_id"])."
				AND b.`objective_active` = '1'
				AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
				ORDER by a.`leobjective_id`";
		    $objectives = $db->GetAll($query);

		    $query = "	SELECT b.`procedure`, CASE a.`level` WHEN 3 THEN 'done' WHEN 2 THEN 'assisted' ELSE 'observed' END level FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_procedures` a
				INNER JOIN `".CLERKSHIP_DATABASE."`.`logbook_lu_procedures` b
				ON a.`lprocedure_id` = b.`lprocedure_id`
				WHERE a.`lentry_id` = ".$db->qstr($entry["lentry_id"])." Order by a.`level` DESC";
		    $procedures = $db->GetAll($query);
		    if ($toggle++) {
			$rowColour = 'ffffff';
			$toggle = 0;
		    } else {
			$rowColour = 'ffffee';
		    }
		    echo "<tr bgcolor=\"#$rowColour\" class=\"details\"><td>$entry[patient_info]".($part?"&nbsp;<i>(p)</i></span>":"")."</td>";
		    echo "<td>$entry[date]</td><td>$entry[gender] ".clerkship_get_agerange ($entry["agerange_id"], $rotation_id)."</td>";
		    echo "<td colspan=\"2\">$entry[location]</td><td>$entry[comments]</td></tr>";
		    if ($objectives) {
			echo "<tr bgcolor=\"#$rowColour\" class=\"details\"><td colspan=2>&nbsp;</td><td colspan=4>";
			foreach ($objectives as $objective) {
			    echo "&nbsp;$objective[obj]<br>";
			}
			echo "</td></tr>";

		    }
		    if ($procedures) {
			echo "<tr bgcolor=\"#$rowColour\" class=\"details\"><td colspan=2>&nbsp;</td><td colspan=2><i>";
			foreach ($procedures as $procedure) {
			    echo "$procedure[procedure]<br>";
			}
			echo "</i></td><td colspan=2><i>";
			foreach ($procedures as $procedure) {
			    echo "$procedure[level]<br>";
			}
			echo "</i></td></tr>";
		    }
		}
	    ?>
	    </table>
    <?php
	if ($participation) {
	    echo "(p) indicates participated.";
	}
    }
	
} else {
	application_log("error", "Personnel API accessed without valid session_id.");
}
?>
