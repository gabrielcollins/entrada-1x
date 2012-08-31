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
 * Allows the student to review resources they may not see on clerkship.
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

    application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {
    $BREADCRUMB[]	= array("url" => ENTRADA_URL."/public/clerkship/logbook?section=resource", "title" => "Objectives Resources");


    if(isset($_GET["id"])) {
	$objective_id = clean_input($_GET["id"], "int");
    } else {
	$objective_id = 0;
    }

    $query  = " SELECT a.`objective_id`, a.`objective_name`
		FROM `".DATABASE_NAME."`.`global_lu_objectives` AS a
		JOIN `objective_organisation` AS b
		ON a.`objective_id` = b.`objective_id`
		AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
		WHERE a.`objective_id` = ".$db->qstr($objective_id)."
		UNION
		SELECT a.`objective_id`, a.`objective_name`
		FROM `".DATABASE_NAME."`.`global_lu_objectives` as a
		JOIN `".DATABASE_NAME."`.`global_lu_objectives` as b
		ON a.`objective_id` = b.`objective_parent`
		JOIN `objective_organisation` AS c
		ON c.`objective_id` = b.`objective_id`
		AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
		WHERE a.`objective_id` > 200
		AND b.`objective_id` = ".$db->qstr($objective_id);
    $results = $db->GetAll($query);

    $secondary = 0;
    foreach ($results as $result) {
	if (!$secondary) {
	    ?>
	    <div class="content-heading">Resources for <?php echo $result["objective_name"]; ?></div>
	    <div style="float: right; margin-bottom: 5px">
		<div id="module-content">
		    <ul class="page-action">
			<li>
			    <a href="<?php echo ENTRADA_URL."/clerkship/logbook?section=add";?>" class="strong-green">Log Encounter</a>
			</li>
		    </ul>
		</div>
	    </div>
	    <div style="clear: both"></div>
	    <?php
	}
	else
	    echo "<div class=\"content-steps\" style=\"padding:0px 50px\">(".$results[1]["objective_name"].")</div>";
	$query = "  SELECT `resource_name`, `resource_text`, `resource_url`
		    FROM `".CLERKSHIP_DATABASE."`.`logbook_lu_resources`
		    WHERE `objective_id` = ".$db->qstr($result["objective_id"]);
	$resources = $db->GetAll($query);
	$secondary++;
	foreach ($resources as $resource) {
	    echo "$resource[resource_name] $resource[resource_texy] $resource[resource_url]\n";
	}
   }

}
?>