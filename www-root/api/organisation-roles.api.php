<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Fetches a list of all of the roles within an organisation.
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
	
	if (isset($_GET["group_id"]) && ($tmp_input = clean_input($_GET["group_id"], array("trim", "notags")))) {
		$group_id = $tmp_input;
	}

	$accum = array();
	$query = "	SELECT r.id, role_name
				FROM `".AUTH_DATABASE."`.`system_groups` g, `".AUTH_DATABASE."`.`system_roles` r,
					`".AUTH_DATABASE."`.organisations o, `".AUTH_DATABASE."`.`system_group_organisation` gho
				WHERE g.id = r.groups_id
				AND o.`organisation_id` = gho.`organisation_id`
				AND gho.`groups_id` = g.`id`
				AND o.`organisation_id` = " . $organisation_id . "
				AND g.`id` = " . $group_id . "
				ORDER BY `group_name`, `role_name`";
	$groups_roles = $db->GetAll($query);
	if ($groups_roles) {		
		foreach ($groups_roles as $gr) {
			$accum[$gr["id"]] = ucfirst($gr["role_name"]);
		}
	}

	echo json_encode($accum);	
}