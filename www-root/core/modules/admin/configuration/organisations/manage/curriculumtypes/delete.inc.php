
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
} elseif (!$ENTRADA_ACL->amIAllowed("configuration", "delete",false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {
?>
<h1>Delete Event Types</h1>
<?php
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/configuration/organisations/manage/curriculumtypes?section=delete&amp;org=".$ORGANISATION['organisation_id'], "title" => "Delete Curriculum Types");

	if (isset($_POST["remove_ids"]) && is_array($_POST["remove_ids"]) && !empty($_POST["remove_ids"])) {
		foreach ($_POST["remove_ids"] as $id) {

			$query = "SELECT COUNT(*) FROM `curriculum_type_organisation` WHERE `curriculum_type_id` = ".$db->qstr($id);

			$num_uses = $db->GetOne($query);

			$query = "DELETE FROM `curriculum_type_organisation` WHERE `curriculum_type_id` = ".$db->qstr($id);
			if ($num_uses > 1) {
				$query .= " AND	`organisation_id` = ".$db->qstr($ORGANISATION_ID);
			}
			if ($db->Execute($query)) {
				$SUCCESS++;
				$SUCCESSSTR[] = "Successfully removed Curriculum Type [".$id."] from your organisation.<br/>";
			}
			if ($num_uses == 1) {
				$query = "UPDATE `curriculum_lu_types` SET	`curriculum_type_active`=0 WHERE `curriculum_type_id` = ".$db->qstr($id);
				if($db->Execute($query)){
					$SUCCESS++;
					$SUCCESSSTR[] = "Successfully removed Curriculum Type [".$id."] from your the system.<br/>You will now be redirected to the Curriculum Type index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/configuration/organisations/manage/curriculumtypes/?org=".$ORGANISATION_ID."\" style=\"font-weight: bold\">click here</a> to continue.";
				}
				else{
					$ERROR++;
					$ERRORSTR[] = "An error occurred while removing the Curriculum Type [".$id."] from the system. The system administrator has been notified.You will now be redirected to the Event Type index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/configuration/organisations/manage/curriculumtypes/?org=".$ORGANISATION_ID."\" style=\"font-weight: bold\">click here</a> to continue.";
					application_log("error", "An error occurred while removing the Curriculum Type [".$id."] from the system. ");
				}
			}
		}


		if ($SUCCESS) {
			echo display_success();
		}
		if ($NOTICE) {
			echo display_notice();
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "No Curriculum Types were selected to be deleted. You will now be redirected to the Curriculum Type index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/configuration/organisations/manage/curriculumtypes/?org=".$ORGANISATION_ID."\" style=\"font-weight: bold\">click here</a> to continue.";

		echo display_error();
	}
		$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/configuration/organisations/manage/curriculumtypes/?org=".$ORGANISATION_ID."\\'', 5000)";
}