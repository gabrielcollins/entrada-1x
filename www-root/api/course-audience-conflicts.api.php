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
	if (!isset($_POST)) {
		die();
	}
	
	if (!isset($_POST["associated_student"]) || !strlen($_POST["associated_student"])) {
		die();
	}
	
	if (!isset($_POST["group_order"]) || !strlen($_POST["group_order"])) {
		die();
	}
	

	$students = explode(",",  clean_input($_POST["associated_student"],array("notags", "trim")));
	$groups = explode(",",clean_input($_POST["group_order"],array("notags", "trim")));
	
	foreach ($students as $student) {
		foreach($groups as $group){
			$query = "	SELECT CONCAT_WS(' ',a.`firstname`,a.`lastname`) AS `fullname`, c.`group_name` 
						FROM `".AUTH_DATABASE."`.`user_data` AS a 
						JOIN `group_members` AS b ON a.`id` = b.`proxy_id` 
						JOIN `groups` AS c ON b.`group_id` = c.`group_id` 
						WHERE b.`group_id` = ".$db->qstr($group)." 
						AND b.`proxy_id` = ".$db->qstr($student);
			$result = $db->GetRow($query);
			if($result){
				$conflicts[] = $result["fullname"]." is already associated with the course via the group '".$result["group_name"]."'.";
			}
		}
	}
	
	

	if ($conflicts) {
		foreach ($conflicts as $conflict) {
			echo $conflict."<br/>";
		}
	}

}

?>
