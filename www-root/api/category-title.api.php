<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Serves the categories list up in a select box.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    dirname(__FILE__) . "/../core/includes",
    dirname(__FILE__) . "/../core/library",
    get_include_path(),
)));

define("DEFAULT_ORGANIZATION_CATEGORY_ID", 49);

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

if (isset($_POST["cid"]) && $_SESSION["isAuthorized"]) {
	$category_id = clean_input($_POST["cid"], array("int"));
	if ($category_id) {
		$query = "	SELECT * FROM `".CLERKSHIP_DATABASE."`.`categories`
					WHERE `category_id` = ".$db->qstr($category_id);
		$category = $db->GetRow($query);
		if ($category) {
			$parent_id 						= $category["category_parent"];
			$category_selected_reverse[]	= $category["category_name"];
			while ($parent_id != DEFAULT_ORGANIZATION_CATEGORY_ID) {
				$query = "	SELECT * FROM `".CLERKSHIP_DATABASE."`.`categories`
							WHERE `category_id` = ".$db->qstr($parent_id);
				$parent_category = $db->GetRow($query);
				if ($parent_category["category_type"] == 32) {
					$category_selected_reverse[]	= $parent_category["category_name"];
				}
				$parent_id 						= $parent_category["category_parent"];
			}
			$category_selected = array_reverse($category_selected_reverse);
			for ($i = 0; $i <= count($category_selected)-1; $i++) {
				echo $category_selected[$i].($i != count($category_selected)-1 ? " > " : "");
			}
		}
	}
}

?>