<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Outputs a table row with the appropriate clerkship objective's data.
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
	$objective_id = clean_input($_POST["id"], array("int"));
	if ($objective_id) {
		$query = "SELECT a.* FROM `global_lu_objectives` AS a
					JOIN `objective_organisation` AS b
					ON a.`objective_id` = b.`objective_id`
					WHERE a.`objective_active` = '1' 
					AND a.`objective_id` = ".$db->qstr($objective_id)." 
					AND 
					(
						a.`objective_parent` = '200' 
						OR a.`objective_parent` IN 
						(
							SELECT `objective_id` FROM `global_lu_objectives` 
							WHERE `objective_active` = '1' 
							AND `objective_parent` = '200'
						)
					)
					AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation());
		$objective = $db->GetRow($query);
		if ($objective) {
			?>
			<tr id="objective_<?php echo $objective_id; ?>_row">
				<td><input type="checkbox" class="objective_delete" value="<?php echo $objective_id; ?>" /></td>
				<td>
					<label for="delete_objective_<?php echo $objective_id; ?>"><?php echo $objective["objective_name"]?></label>
					<input type="hidden" name="objectives[<?php echo $objective_id; ?>]" value="<?php echo $objective_id; ?>" />
				</td>
			</tr>
			<?php
		}
	}
}
?>