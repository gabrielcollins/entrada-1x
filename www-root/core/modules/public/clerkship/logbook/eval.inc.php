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
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2009 Queen's University. All Rights Reserved.
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
    $BREADCRUMB[]	= array("url" => ENTRADA_URL."/public/clerkship/logbook?section=eval", "title" => "Rotation Evaluation Checklist");

    if(isset($_GET["id"])) {
	$event_id = clean_input($_GET["id"], "int");
	$query  = " SELECT a.* FROM `".CLERKSHIP_DATABASE."`.`global_lu_rotations` a
				INNER JOIN `".CLERKSHIP_DATABASE."`.`events` b
			    ON a.`rotation_id` = b.`rotation_id`
			    WHERE b.`event_id` = ".$db->qstr($event_id);
    } else {  // Select Overview / Elective if not a mandatory rotation
	$query  = " SELECT * FROM `".CLERKSHIP_DATABASE."`.`global_lu_rotations`
				WHERE a.`rotation_id` = ".$db->qstr(MAX_ROTATION);
    }

    $result = $db->GetRow($query);

    if ($result) {
	if ($ERROR) {
	    echo display_error();
	}
	?>
	<div class="content-heading">Evaluation Checklist for <?php echo $result["rotation_title"];?></div>
<!--  .... -->
	<br />
	<?php
	}
}
?>