<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Fetches a list of all of the groups within an organisation.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Don Zuiker <zuikerd@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
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
	if (isset($_GET["organisation_id"]) && ($tmp_input = clean_input($_GET["organisation_id"], array("trim", "notags")))) {
		$organisation_id = $tmp_input;
	} else {
		$organisation_id = 0;
	}

	$accum = array();
	$query = "	SELECT g.id, g.group_name
				FROM `" . AUTH_DATABASE . "`.`system_groups` g,
					`" . AUTH_DATABASE . "`.organisations o, 
						`" . AUTH_DATABASE . "`.`system_group_organisation` gho
				WHERE o.`organisation_id` = gho.`organisation_id`
				AND gho.`groups_id` = g.`id`
				AND o.`organisation_id` = " . $organisation_id . "
				ORDER BY `group_name`";
	
	$groups_roles = $db->GetAll($query);
	if ($groups_roles) {		
		foreach ($groups_roles as $gr) {
			$accum[$gr["id"]] = ucfirst($gr["group_name"]);
		}
	}
	
	echo json_encode($accum);
}