<?php
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

	if ((isset($_POST["ctype_id"])) && $type_id = (int)$_POST["ctype_id"]) {
		$query = "SELECT * FROM `curriculum_periods` WHERE `curriculum_type_id` = ".$db->qstr($type_id);
		$periods = $db->GetAll($query);
		if ($periods) {
			echo "<select name=\"curriculum_period\" id = \"period_select\">";
			echo "<option value=\"0\">-- Select a Period --</option>";
			foreach ($periods as $period) {
				echo "<option value = \"".$period["cperiod_id"]."\">".(($period["curriculum_period_title"]) ? $period["curriculum_period_title"] . " - " : "").date("F jS, Y",$period["start_date"])." to ".date("F jS, Y",$period["finish_date"])."</option>";
			}
			echo "</select>";
		} else {
			add_notice("No <strong>Curriculum Periods</strong> assigned to the selected <strong>Curriculum Category</strong>.");
			echo display_notice();			
		}
		
		
	} else {
		add_notice("No <strong>Curriculum Category</strong> has been selected.");
		echo display_notice();
	}
}
?>
