<?php

require_once("Observership.class.php");
require_once("Models/utility/Collection.class.php");

class Observerships extends Collection {
	public static function get(User $user) {
		global $db;
		$user_id = $user->getID();
		$query = "SELECT * FROM `student_observerships` WHERE `student_id` = ? ORDER BY `start` ASC";
		$results = $db->getAll($query, array($user_id));
		$obss = array();
		if ($results) {
			foreach ($results as $result) {
				$obs = Observership::fromArray($result);
				$obss[] = $obs;
			}
		} 
		return new self($obss);
	}
}