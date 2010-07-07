<?php


require_once("DisciplinaryAction.class.php");

class DisciplinaryActions {
	public static function get(User $user) {
		global $db;
		$user_id = $user->getID();
		$query		= "SELECT * FROM `student_disciplinary_actions` WHERE `user_id` = ".$db->qstr($user_id);
		$results = $db->getAll($query);
		if ($results) {
			$das = array();
			foreach ($results as $result) {
				$da =  new DisciplinaryAction($user, $result['id'], $result['action_details']);
				$das[] = $da;
			}
			return $das;
		}
	}
}