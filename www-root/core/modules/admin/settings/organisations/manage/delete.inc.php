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
} else{
	if((isset($_POST["remove_ids"])) && (count($_POST["remove_ids"])>0)){

		foreach($_POST["remove_ids"] as $id){
			if($ENTRADA_ACL->amIAllowed(new ConfigurationResource($id),"delete")){
			$query = "DELETE FROM `".AUTH_DATABASE."`.`organisations` WHERE `organisation_id` = ".$db->qstr($id);
				if ($db->Execute($query) && ($db->Affected_Rows() == 1)) {
					$query = "DELETE FROM `eventtype_organisation` WHERE `organisation_id` = ".$db->qstr($id);
					if ($db->Execute($query)) {
						$query = "DELETE FROM `objective_organisation` WHERE `organisation_id` = ".$db->qstr($id);
						if ($db->Execute($query)) {
							$SUCCESS++;
							$SUCCESSSTR[] = "You have successfully removed organisation <strong>".$id."</strong> from the system.<br /><br />You will now be redirected to the organisations index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/settings/organisations\" style=\"font-weight: bold\">click here</a> to continue.";
							$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/settings/organisations/\\'', 5000)";
							application_log("success", "Removed information for the organisation [".$id."].");
						} else {
						$ERROR++;
						$ERRORSTR[]	= "We were unable to remove organisation ".$id." from the system at this time.<br /><br />The system administrator has been notified of this issue, please try again later.";
						application_log("error", "Failed to remove organisation from the database. Database said: ".$db->ErrorMsg());
						}
					} else {
					$ERROR++;
					$ERRORSTR[]	= "We were unable to remove organisation ".$id." from the system at this time.<br /><br />The system administrator has been notified of this issue, please try again later.";
					application_log("error", "Failed to remove organisation from the database. Database said: ".$db->ErrorMsg());
					}
				} else {
					$ERROR++;
					$ERRORSTR[]	= "We were unable to remove organisation ".$id." from the system at this time.<br /><br />The system administrator has been notified of this issue, please try again later.";
					application_log("error", "Failed to remove organisation from the database. Database said: ".$db->ErrorMsg());
				}
			}else{
				add_notice("You don't appear to have access to change organisation[".$id."]. If you feel you are seeing this in error, please contact your system administrator.");
				echo display_notice();
			}
		}
	}else{
		$ERROR++;
		$ERRORSTR[] = "No organisations were selected to be deleted. You will be redirected back to the organisations index <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/settings/organisations\" style=\"font-weight: bold\">click here</a> to continue. ";
		$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/settings/organisations/\\'', 5000)";	
	}



	if ($ERROR) {
		echo display_error();
	}
	if ($NOTICE) {
		echo display_notices();
	}
	if ($SUCCESS) {
		echo display_success();
	}

}

?>
