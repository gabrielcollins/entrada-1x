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

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	?>
	<h1>Manage Payment Methods</h1>
	<div style="float: right">
		<ul class="page-action">
			<li><a href="<?php echo ENTRADA_URL; ?>/admin/settings/organisations/manage/payments?section=add&amp;org=<?php echo $ORGANISATION_ID;?>" class="strong-green">Add New Payment Method</a></li>
		</ul>
	</div><br />
	<?php

	/*
	 * To change this template, choose Tools | Templates
	 * and open the template in the editor.
	 */

	$query = "	SELECT a.*, b.`payment_name` AS `paytype_name` FROM `payment_options` AS a 
				JOIN `payment_lu_types` AS b
				ON a.`ptype_id` = b.`ptype_id` 
				WHERE a.`organisation_id` = ".$db->qstr($ORGANISATION_ID)." 
				AND a.`payment_active` = 1 
				ORDER BY a.`payment_name` ASC";
	
	$results = $db->GetAll($query);

	if ($results) {
	?>
	<form action ="<?php echo ENTRADA_URL;?>/admin/settings/organisations/manage/payments?section=delete&amp;org=<?php echo $ORGANISATION_ID;?>" method="post">
	<table class="tableList" cellspacing="0" cellpadding="1" border="0" summary="List of Organisations">
		<colgroup>
			<col class="modified" />
			<col class="title" />
			<col class="title" />
		</colgroup>
		<thead>
			<tr>
				<td class="modified">&nbsp;</td>
				<td class="title">Payment Account Name</td>
				<td class="title">Payment Service</td>
			</tr>
		</thead>
		<tbody>
			<?php
				foreach($results as $result){
					echo "<tr><td><input type=\"checkbox\" name = \"remove_ids[]\" value=\"".$result["poption_id"]."\"/></td>";
					echo"<td><a href=\"".ENTRADA_URL."/admin/settings/organisations/manage/payments?section=edit&amp;org=".$ORGANISATION_ID."&amp;type_id=".$result["poption_id"]."\">".$result["payment_name"]."</a></td>";
					echo"<td><a href=\"".ENTRADA_URL."/admin/settings/organisations/manage/payments?section=edit&amp;org=".$ORGANISATION_ID."&amp;type_id=".$result["poption_id"]."\">".$result["paytype_name"]."</a></td></tr>";
				}
			?>
		</tbody>
	</table>
	<br />
	<input type="submit" class="button" value="Delete Selected" />
	</form>
	<?php

	}
	else{
		$NOTICE++;
		$NOTICESTR[] = "There are currently no Payment Methods assigned to this Organisation";
		echo "<br />".display_notice();

	}

}

