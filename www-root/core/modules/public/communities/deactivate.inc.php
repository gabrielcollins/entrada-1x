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
 * This file is used to add events to the entrada.events table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2008 Queen's University. All Rights Reserved.
 *
*/

if((!defined("PARENT_INCLUDED")) || (!defined("IN_COMMUNITIES"))) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
}

if ($MAILING_LISTS["active"]) {
	require_once("Entrada/mail-list/mail-list.class.php");
}

$BREADCRUMB[]		= array("url" => ENTRADA_URL."/communities?".replace_query(array("section" => "modify")), "title" => "Modifying a Community");

$COMMUNITY_ID		= 0;

$url				= ENTRADA_URL."/communities";
$ONLOAD[]			= "setTimeout('window.location=\\'".$url."\\'', 5000)";

/**
 * Check for a community category to proceed (via POST) to help prevent against CSRF attacks.
 */
if((isset($_POST["community_id"])) && ((int) trim($_POST["community_id"]))) {
	$COMMUNITY_ID = (int) trim($_POST["community_id"]);
}

/**
 * Ensure that the selected community is editable by you.
 */
if($COMMUNITY_ID) {
	$query				= "SELECT * FROM `communities` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `community_active` = '1'";
	$community_details	= $db->GetRow($query);
	if($community_details) {
		if($ENTRADA_ACL->amIAllowed(new CommunityResource($COMMUNITY_ID), 'delete')) {
			if(@$db->AutoExecute("communities", array("community_active" => 0), "UPDATE", "`community_id` = ".$db->qstr($COMMUNITY_ID))) {

				if ($MAILING_LISTS["active"]) {
					$list = new MailingList($COMMUNITY_ID);
					$list->mode_change("inactive");
				}

				$SUCCESS++;
				$SUCCESSSTR[] = "<strong>You have just deactiviated ".html_encode($community_details["community_title"]).".</strong><br /><br />If there has been a mistake please contact the MEdTech unit directly for assistance.<br /><br />You will now be redirected back to the communities section; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";

				application_log("success", "Community ID [".$COMMUNITY_ID."] has been deactivated.");
			} else {
				$ERROR++;
				$ERRORSTR[] = "<strong>Unable to deactive ".html_encode($community_details["community_title"]).".</strong><br /><br />The MEdTech unit has been informed of this error, please try again later. You will now be redirected back to the communities section; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";

			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "You are not listed as an active administrator of this community, or the community has already been deactivated. If you are having trouble, please contact a community administrator or the MEdTech Unit directly.";

			application_log("error", "The proxy_id [".$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]." / ".$_SESSION["details"]["id"]."] is not an administrator of community_id [".$COMMUNITY_ID."] was not found or was already deactivated.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "The community identifier that was provided does not exist in our database or has already been deactivated.";

		application_log("error", "The provided community_id [".$COMMUNITY_ID."] was not found or was already deactivated.");
	}
} else {
	$ERROR++;
	$ERRORSTR[] = "You have not provided a valid community identifier to deactivate.";

	application_log("error", "There was no community_id provided to deactivate.");
}

if($SUCCESS) {
	echo display_success();
}

if($ERROR) {
	echo display_error();
}
?>
