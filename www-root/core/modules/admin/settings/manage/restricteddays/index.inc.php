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
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2013 Queen's University. All Rights Reserved.
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
	<h1>Manage Restricted Days</h1>

	<div class="clearfix space-below">
		<div class="pull-right">
			<a href="<?php echo ENTRADA_URL; ?>/admin/settings/manage/restricteddays?section=add&amp;org=<?php echo $ORGANISATION_ID;?>" class="btn btn-primary">Add New Restricted Days</a>
		</div>
	</div>
	<?php

	$restricted_days = Models_RestrictedDays::fetchAll($ORGANISATION_ID);

	if($restricted_days){
	?>
	<form action ="<?php echo ENTRADA_URL;?>/admin/settings/manage/restricteddays?section=delete&amp;org=<?php echo $ORGANISATION_ID;?>" method="post">
	<table class="tableList" cellspacing="0" cellpadding="1" border="0" summary="List of Restricted Days">
		<colgroup>
			<col class="modified" />
			<col class="title" />
		</colgroup>
		<thead>
			<tr>
				<td class="modified">&nbsp;</td>
				<td class="title">Restricted Day</td>
			</tr>
		</thead>
		<tbody>
			<?php
				foreach ($restricted_days as $restricted_day) {
					echo "<tr><td><input type=\"checkbox\" name = \"remove_ids[]\" value=\"".$restricted_day->getID()."\"/></td>";
					echo"<td><a href=\"".ENTRADA_URL."/admin/settings/manage/restricteddays?section=edit&amp;org=".$ORGANISATION_ID."&amp;day_id=".$restricted_day->getID()."\">".$restricted_day->getName()."</a></td></tr>";
				}
			?>
		</tbody>
	</table>
	<br />
	<input type="submit" class="btn btn-danger" value="Delete Selected" />
	</form>
	<?php

	} else {
		add_notice("There are currently no Restricted Days assigned to this Organisation");
		echo display_notice();
	}


}

