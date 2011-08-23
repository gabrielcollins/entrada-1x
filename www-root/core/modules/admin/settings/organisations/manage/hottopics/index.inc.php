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
 * @author Unit: MEdTech Unit
 * @author Developer: Brandon Thorn <brandon.thorn@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_CONFIGURATION"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("configuration", "read",false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {
?>
	<h1>Manage topics</h1>
	<div style="float: right">
		<ul class="page-action">
			<li><a href="<?php echo ENTRADA_URL; ?>/admin/settings/organisations/manage/hottopics?section=add&amp;org=<?php echo $ORGANISATION_ID;?>" class="strong-green">Add New Hot Topic</a></li>
		</ul>
	</div>
	<?php

	/*
	 * To change this template, choose Tools | Templates
	 * and open the template in the editor.
	 */

	$query = "	SELECT a.* FROM `events_lu_topics` AS a 
				LEFT JOIN `topic_organisation` AS b
				ON a.`topic_id` = b.`topic_id` 
				WHERE b.`organisation_id` = ".$db->qstr($ORGANISATION_ID)." 
				ORDER BY a.`topic_name` ASC";




	$results = $db->GetAll($query);

	if($results){
	?>
	<form action ="<?php echo ENTRADA_URL;?>/admin/settings/organisations/manage/hottopics?section=delete&amp;org=<?php echo $ORGANISATION_ID;?>" method="post">
	<table class="tableList" cellspacing="0" cellpadding="1" border="0" summary="List of Organisations">
		<colgroup>
			<col class="modified" />
			<col class="title" />
		</colgroup>
		<thead>
			<tr>
				<td class="modified">&nbsp;</td>
				<td class="title">Hot Topic</td>
			</tr>
		</thead>
		<tbody>
			<?php
				foreach($results as $result){
					echo "<tr><td><input type=\"checkbox\" name = \"remove_ids[]\" value=\"".$result["topic_id"]."\"/></td>";
					echo"<td><a href=\"".ENTRADA_URL."/admin/settings/organisations/manage/hottopics?section=edit&amp;org=".$ORGANISATION_ID."&amp;topic_id=".$result["topic_id"]."\">".$result["topic_name"]."</a></td></tr>";
				}
			?>
		</tbody>
	</table>
	<br/>
	<input type="submit" class="button" value="Delete Selected" />
	</form>
	<?php

	}
	else{
		$NOTICE++;
		$NOTICESTR[] = "There are currently no Hot Topics assigned to this Organisation";
		echo "<br/>".display_notice();

	}


}
