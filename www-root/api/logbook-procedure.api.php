<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Outputs a table row with the appropriate clerkship procedure's data.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2009 Queen's University. All Rights Reserved.
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

if (isset($_POST["id"]) && $_SESSION["isAuthorized"]) {
	$procedure_id = clean_input($_POST["id"], array("int"));
	if (isset($_POST["level"]) && ((int)$_POST["level"])) {
		$level = (int)$_POST["level"];
	} else {
		$level = 0;
	}
	if ($procedure_id) {
		$query = "SELECT * FROM `".CLERKSHIP_DATABASE."`.`logbook_lu_procedures` WHERE `lprocedure_id` = ".$db->qstr($procedure_id);
		$procedure = $db->GetRow($query);
		if ($procedure) {
			?>
			<tr id="procedure_<?php echo $procedure_id; ?>_row">
				<td><input type="checkbox" class="procedure_delete" value="<?php echo $procedure_id; ?>" /></td>
				<td class="left"><label for="delete_procedure_<?php echo $procedure_id; ?>"><?php echo $procedure["procedure"]?></label></td>
				<td style="text-align: right">
					<input type="hidden" name="procedures[<?php echo $procedure_id; ?>]" value="<?php echo $procedure_id; ?>" />
					<select name="proc_participation_level[<?php echo $procedure_id; ?>]" id="proc_<?php echo $procedure_id; ?>_participation_level" style="width: 150px">
						<option value="1" <?php echo ($level == 1 ? "selected=\"selected\"" : ""); ?>>Performed with help</option>
						<option value="2" <?php echo ($level == 2 ? "selected=\"selected\"" : ""); ?>>Performed independently</option>
					</select>
				</td>
			</tr>
			<?php
		}
	}
}

?>