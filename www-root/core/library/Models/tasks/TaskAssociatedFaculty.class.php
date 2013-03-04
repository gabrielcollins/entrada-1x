<?php
/**
 * Collection class for managing faculty associated with a given task 
 * 
 * @author Jonathan Fingland
 *
 */
class TaskAssociatedFaculty extends Collection {
	
	/**
	 * Internal ID of the associated task
	 * @var int
	 */
	private $task_id;
	
	/**
	 * Returns a Collection of Users deisgnated as associated faculty for the provided task ID
	 * @return TaskAssociatedFaculty
	 */
	public static function get($task_id) {
		global $db;
		
		$query = "SELECT b.* from `task_associated_faculty` a left join `".AUTH_DATABASE."`.`user_data` b on a.`faculty_id`=b.`id` where a.`task_id`=".$db->qstr($task_id);
		
		$results = $db->getAll($query);
		$faculty = array();
		if ($results) {
			foreach ($results as $result) {
				$faculty_member = User::get($result["id"]);
				if ($faculty_member) {
					$faculty[] = $faculty_member;
				}
			}
		}
		return new self($faculty, $task_id);
	}
	
	function __construct($faculty, $task_id) {
		parent::__construct($faculty);
		$this->task_id = $task_id;
	}
	
	
	/**
	 * Adds the provided faculty member(s) to the list of faculty associated with the provided task ID 
	 * @param int $task_id
	 * @param array|int|User $faculty_members
	 */
	public static function add($task_id, $faculty_members) {
		global $db;
		$query = "insert ignore into `task_associated_faculty` (`task_id`,`faculty_id`) values ";
		$q_task_id = $db->qstr($task_id);
		if (!is_array($faculty_members)) {
			$faculty_members = array($faculty_members);
		}
		$records = array();
		foreach($faculty_members as $faculty) {
			if ($faculty instanceof User) {
				$faculty_id = $faculty->getID();
			} elseif (is_numeric($faculty)) {
				$faculty_id = (int) $faculty;
			} else {
				continue;
			}
			$records[] = "(".$q_task_id.",".$db->qstr($faculty_id). ")";
		}
		$query .= implode(",",$records);
		if(false === $db->Execute($query)) {
			add_error("Failed to add faculty to task. Database said: ".$db->ErrorMsg());
			application_log("error", "Unable to create task_associated_faculty records. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully added faculty to task.");
		}
	} 
	
	/**
	 * Removes the provided faculty member(s) from the list of faculty associated with the provided task ID   
	 * @param unknown_type $task_id
	 * @param array|int|User $faculty_members
	 */
	public static function remove($task_id, $faculty_members=null) {
		global $db;
		$q_task_id = $db->qstr($task_id);
		if (is_null($faculty_members)) {
			$query = "delete from `task_associated_faculty` where `task_id`=?";
		} else {
			$query = "delete from `task_associated_faculty` where `task_id`=? and `faculty_id` IN (";
			if( !is_array($faculty_members)) {
				$faculty_members = array($faculty_members);
			} 
			$records = array();
			foreach($faculty_members as $faculty) {
				if ($faculty instanceof User) {
					$faculty_id = $faculty->getID();
				} elseif (is_int($faculty)) {
					$faculty_id = $faculty;
				} else {
					continue;
				}
				$records[] = $db->qstr($faculty_id);
			}
			$query .= implode(",",$records) . ")";
		}
		if(!$db->Execute($query, array($task_id))) {
			add_error("Failed to remove faculty from task");
			application_log("error", "Unable to remove from task_associated_faculty records. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully removed faculty from task.");
		}		
	}
}