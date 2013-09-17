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
 * ePortfolio public index
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <ryan.warner@queensu.ca>
 * @author Developer: Josh Dillon <josh.dillon@queensu.ca>
 * @copyright Copyright 2013 Queen's University. All Rights Reserved.
 *
 */

if (!defined("PARENT_INCLUDED")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("eportfolio", "read")) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/eportfolio.js\"></script>";
	$HEAD[] = "<script type=\"text/javascript\">var ENTRADA_URL = '".ENTRADA_URL."';</script>";
	?>
	<h1>Entrada ePortfolio</h1>
	<?php
	
	$eportfolio = Models_Eportfolio::fetchRowByGroupID($ENTRADA_USER->getCohort());
	
	echo "<h2>".$eportfolio->getPortfolioName()."</h2>";
	
	$folders = $eportfolio->getFolders();
	if ($folders) {
		echo "<ul>";
		foreach ($folders as $folder) {
			echo "<li>";
			echo "<h3>".$folder->getTitle()."<a href=\"#\" data-pfolder-id=\"".$folder->getID()."\" class=\"add-artifact\"><i class=\"icon-plus-sign\"></i></a></h3>";
			$artifacts = $folder->getArtifacts($ENTRADA_USER->getID());
			if ($artifacts) {
				echo "<ul>";
				foreach ($artifacts as $artifact) {
					echo "<li>";
					echo "<h4>".$artifact->getTitle()."<a href=\"#\" data-pfartifact-id=\"".$artifact->getID()."\" class=\"add-entry\"><i class=\"icon-plus\"></i></a></h4>";
					$entries = $artifact->getEntries($ENTRADA_USER->getID());
					if ($entries) {
						echo "<ul>";
						foreach ($entries as $entry) {
							echo "<li>";
							$edata = $entry->getEdataDecoded(); 
							if (isset($edata["filename"]) && !empty($edata["filename"])) {
								echo "<a href=\"#\">".$edata["filename"]."</a>";
							} else if (isset($edata["description"]) && !empty($edata["description"])) {
								echo $edata["description"];
							}
							echo "</li>";
						}
						echo "</ul>";
					}
					echo "</li>";
				}
				echo "</ul>";
			}
			echo "</li>";
		}
		echo "</ul>";
	}
	
}