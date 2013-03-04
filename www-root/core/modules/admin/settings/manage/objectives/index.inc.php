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
 * This file displays the list of objectives pulled 
 * from the entrada.global_lu_objectives table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer:James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if((!defined("PARENT_INCLUDED")) || (!defined("IN_OBJECTIVES"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
		header("Location: ".ENTRADA_URL);
		exit;
} elseif (!$ENTRADA_ACL->amIAllowed('objective', 'update', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/scriptaculous/sortable_tree.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	
	echo "<h1>Manage Objectives</h1>";
	if($ENTRADA_ACL->amIAllowed('objective', 'create', false)) { 
		?>
		<div style="float: right">
			<ul class="page-action">
				<li><a href="<?php echo ENTRADA_URL; ?>/admin/settings/manage/objectives?section=add&amp;org=<?php echo $ORGANISATION_ID; ?>&amp;step=1" class="strong-green">Add New Objective</a></li>
			</ul>
		</div>
		<div style="clear: both"></div>
		<?php 
	}
	
	$query = "	SELECT a.* FROM `global_lu_objectives` AS a
				LEFT JOIN `objective_organisation` AS b
				ON a.`objective_id` = b.`objective_id`
				WHERE a.`objective_parent` = '0' 
				AND a.`objective_active` = '1' 
				AND b.`organisation_id` = ".$db->qstr($ORGANISATION_ID)."
				ORDER BY a.`objective_order` ASC";
	$result = $db->GetAll($query);
	
	if(isset($result) && count($result)>0){
		?>
		<form action="<?php echo ENTRADA_URL."/admin/settings/manage/objectives?".replace_query(array("section" => "delete", "step" => 1, "org" => $ORGANISATION_ID)); ?>" method="post">
			<table class="tableList" cellspacing="0" summary="List of Objectives">
				<thead>
					<tr>
						<td class="modified">&nbsp;</td>
						<td class="title">Objectives</td>
					</tr>
				</thead>
			</table>	
			<?php
			echo objectives_inlists_conf(0, 0, array('id'=>'pagelists'));
			?>
			<input type="submit" class="button" value="Delete Selected" />
		</form>
		<?php
	} else {
		$NOTICE++;
		$NOTICESTR[] = "There are currently no Objectives assigned to this Organisation";
		echo display_notice();
	}
}