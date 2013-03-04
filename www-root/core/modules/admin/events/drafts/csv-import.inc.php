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
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <ryan.warner@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVENTS"))) {
	exit;
} else if ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("event", "create", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");
	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	require_once(ENTRADA_ABSOLUTE."/core/library/Models/events/drafts/CsvImporter.class.php");

    echo "<h1>Draft Event Import</h1>";

	$draft_id = (isset($_GET["draft_id"]) ? (int) $_GET["draft_id"] : 0);
    if ($draft_id) {
    	$query = "  SELECT a.*
                    FROM `drafts` AS a
                    JOIN `draft_creators` AS b
                    ON b.`draft_id` = a.`draft_id`
                    AND b.`proxy_id` = ".$db->qstr($ENTRADA_USER->getActiveId())."
                    WHERE a.`draft_id` = ".$db->qstr($draft_id)."
                    AND `status` = 'open'";
        $result = $db->GetRow($query);
        if ($result) {
            if (isset($_FILES["csv_file"])) {
                switch ($_FILES["csv_file"]["error"]) {
                    case 1 :
                    case 2 :
                    case 3 :
                        add_error("The file that uploaded did not complete the upload process or was interupted. Please <a href=\"".ENTRADA_RELATIVE."/admin/events/drafts?section=edit&draft_id=".$draft_id."\">click here</a> and try your CSV again.");
                    break;
                    case 4 :
                        add_error("You did not select a file on your computer to upload. Please <a href=\"".ENTRADA_RELATIVE."/admin/events/drafts?section=edit&draft_id=".$draft_id."\">click here</a> and try your CSV import again.");
                    break;
                    case 6 :
                    case 7 :
                        add_error("Unable to store the new file on the server, please <a href=\"".ENTRADA_RELATIVE."/admin/events/drafts?section=edit&draft_id=".$draft_id."\">click here</a> and try again.");
                    break;
                    default :
                        continue;
                    break;
                }
            } else {
                add_error("To upload a file to this event you must select a file to upload from your computer.");
            }

            if (!has_error()) {
                $csv_importer = new CsvImporter($draft_id, $ENTRADA_USER->getActiveId());
                $csv_importer->importCsv($_FILES["csv_file"]);

                $csv_errors = $csv_importer->getErrors();
                if ($csv_errors) {
                    $err_msg  = "The following errors occured while attempting to import draft learning events. Please review the errors below and correct them in your file. Once correct, please try again.<br /><br />";
                    $err_msg .= "<pre>";
                    foreach ($csv_errors as $rowid => $error) {
                        foreach ($error as $msg) {
                            $err_msg .= "Row ".$rowid.": ".html_encode($msg)."\n";
                        }
                    }
                    $err_msg .= "</pre>";
                    $err_msg .= "<br /><br />";
                    $err_msg .= "Please <a href=\"".ENTRADA_RELATIVE."/admin/events/drafts?section=edit&draft_id=".$draft_id."\">click here</a> to return to the draft.";

                    add_error($err_msg);
                    echo display_error();
                } else {
                    $csv_success = $csv_importer->getSuccess();

                    add_success("Successfully imported <strong>".count($csv_success)."</strong> events from <strong>".html_encode($_FILES["csv_file"]["name"])."</strong><br /><br />You will now be redirected to the edit draft page; this will happen automatically in 5 seconds or <a href=\"".ENTRADA_URL."/admin/events/drafts?section=edit&draft_id=".$draft_id."\">click here</a> to continue.");
                    echo display_success();

                    $ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/events/drafts?section=edit&draft_id=".$draft_id."\\'', 5000)";

                    application_log("success", "Proxy_id [".$ENTRADA_USER->getActiveId()."] successfully imported ".count($csv_success)." events into draft_id [".$draft_id."].");
                }
            } else {
                echo display_error();
                $ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/events/drafts?section=edit&draft_id=".$draft_id."\\'', 5000)";
            }
        } else {
            add_error("We were unable to select the specified draft to import these events into. Please try again.");
            echo display_error();

            application_log("error", "Proxy_id [".$ENTRADA_USER->getActiveId()."] was unable to select the draft_id [".$draft_id."]. Database said: ".$db->ErrorMsg());
        }
	} else {
		add_error("There was no draft id provided to import any events into.");
		echo display_error();

        application_log("error", "Proxy_id [".$ENTRADA_USER->getActiveId()."] did not provide a draft_id.");
	}
}