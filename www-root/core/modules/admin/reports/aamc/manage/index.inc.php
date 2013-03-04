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
 * @author Developer: Matt Simpson <simpson@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_AAMC_CI"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("report", "read", false)) {
	$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
$xml_preview = <<<XMLEND
<?xml version="1.0" encoding="UTF-8"?>
<CurriculumInventory
xsi:schemaLocation="http://ns.medbiq.org/curriculuminventory/v1/
curriculuminventory.xsd"
xmlns="http://ns.medbiq.org/curriculuminventory/v1/"
xmlns:lom="http://ltsc.ieee.org/xsd/LOM"
xmlns:a="http://ns.medbiq.org/address/v1/"
xmlns:cf="http://ns.medbiq.org/competencyframework/v1/"
xmlns:co="http://ns.medbiq.org/competencyobject/v1/"
xmlns:hx="http://ns.medbiq.org/lom/extend/v1/"
xmlns:m="http://ns.medbiq.org/member/v1/"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<ReportID domain="idd:nosuch.edu:cip">12345</ReportID>
<Institution>
<m:InstitutionName>NoSuch University School of
Medicine</m:InstitutionName>
<m:InstitutionID
domain="idd:aamc.org:institution">987</m:InstitutionID>
<m:Address>
<a:StreetAddressLine>720 Main Street</a:StreetAddressLine>
<a:City>Baltimore</a:City>
<a:StateOrProvince>MD</a:StateOrProvince>
<a:PostalCode>21205</a:PostalCode>
<a:Country>
<a:CountryCode>US</a:CountryCode>
</a:Country>
</m:Address>
</Institution>
<Program>
<ProgramName>M.D.</ProgramName>
<ProgramID domain="idd:aamc.org:program">5678</ProgramID>
</Program>
<Title>NoSuch School of Medicine Curriculum 2010-2011</Title>
<ReportDate>2011-07-01</ReportDate>
<ReportingStartDate>2010-07-01</ReportingStartDate>
<ReportingEndDate>2011-06-30</ReportingEndDate>
<Language>en-us</Language>
<Description>The NoSuch curriculum reframes the context of health and
illness to encourage students to explore a larger, integrated
system.</Description>
<SupportingLink>http://www.nosuchmedicine.org/crc/</SupportingLink>
XMLEND;


	echo "<h1>".html_encode($REPORT["report_title"])."</h1>";

	if ($REPORT["report_description"]) {
		echo "<div class=\"event-description\">\n";
		echo $REPORT["report_description"];
		echo "</div>";
	}
	?>

	<h2>XML Preview</h2>
	<div style="width: 100%; height:300px; border: 1px #666 solid; padding: 5px; font-family: monospace; overflow: auto">
		<?php echo nl2br(htmlspecialchars($xml_preview)); ?>
	</div>

	<div style="text-align: right; margin-top: 5px;">
		<button>Send XML</button>
	</div>
	<?php
}
