<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Serves the categories list up in a select box.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Andrew Dos-Santos <andrew.dos-santos@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    dirname(__FILE__) . "/../core/includes",
    dirname(__FILE__) . "/../core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

if ((isset($_SESSION["isAuthorized"])) && ((bool) $_SESSION["isAuthorized"])) {
	$countries_id = ((isset($_GET["countries_id"])) ? clean_input($_GET["countries_id"], "int") : 0);
	$tmp_input = ((isset($_GET["prov_state"])) ? clean_input(rawurldecode($_GET["prov_state"]), array("notags", "trim")) : "");

	$province_id = 0;
	$province ="";


	if (ctype_digit($tmp_input)) {
		$province_id = (int) $tmp_input;
	} else {
		$province = $tmp_input;
	}

	if ($countries_id) {
		$query		= "SELECT * FROM `global_lu_provinces` WHERE `country_id` = ".$db->qstr($countries_id)." ORDER BY `province` ASC";
		$results	= $db->GetAll($query);
		if ($results) {
			echo "<select id=\"prov_state\" name=\"prov_state\">\n";
			echo "<option value=\"0\"".((!$province_id) ? " selected=\"selected\"" : "").">-- Select Province / State --</option>\n";
			foreach($results as $result) {
				echo "<option value=\"".clean_input($result["province_id"], array("notags", "specialchars"))."\"".(($province_id == $result["province_id"]) ? " selected=\"selected\"" : ($province == clean_input($result["province"], array("notags", "specialchars")) ? " selected=\"selected\"" : "")).">".clean_input($result["province"], array("notags", "specialchars"))."</option>\n";
			}
			echo "</select>\n";
		} else {
			echo "<input type=\"text\" id=\"prov_state\" name=\"prov_state\" value=\"".clean_input($province, array("notags", "specialchars"))."\" maxlength=\"100\" />";
		}
		exit;
	}

	echo "<input type=\"hidden\" id=\"prov_state\" name=\"prov_state\" value=\"0\" />\n";
	echo "Please select a <strong>Country</strong> from above first.\n";
}
?>
