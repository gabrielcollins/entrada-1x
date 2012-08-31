<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 *
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
*/

/**
 * Instances are a Collection of TaskVerifier objects associated with a single associated task  
 * 
 * @author Jonathan Fingland
 *
 */
class TaskVerifiers extends Collection {
	
	/**
	 * Interal ID for Task
	 * @var int
	 */
	private $task_id;
	
	/**
	 * Returns a TaskVerifiers Collection of TaskVerifier objects
	 * @param $task_id
	 * @return TaskVerifiers
	 */
	public static function get($task_id) {
		global $db;
		
		$query = "SELECT b.* from `task_verifiers` a left join `".AUTH_DATABASE."`.`user_data` b on a.`verifier_id`=b.`id` where a.`task_id`=".$db->qstr($task_id);
		
		$results = $db->getAll($query);
		$verifiers = array();
		if ($results) {
			foreach ($results as $result) {
				$verifier = User::get($result["id"]);
				if ($verifier) {
					$verifiers[] = $verifier;
				}
			}
		}
		return new self($verifiers, $task_id);
	}
	
	function __construct($verifiers, $task_id) {
		parent::__construct($verifiers);
		$this->task_id = $task_id;
	}
	
	
	/**
	 * Adds the supplied user(s) to the list of verifiers for the specified task   
	 * @param int $task_id
	 * @param array|int|User $verifiers
	 */
	public static function add($task_id, $verifiers) {
		global $db;
		$query = "insert ignore into `task_verifiers` (`task_id`,`verifier_id`) values ";
		$q_task_id = $db->qstr($task_id);
		if (!is_array($verifiers)) {
			$verifiers = array($verifiers);
		}
		$records = array();
		foreach($verifiers as $verifier) {
			if ($verifier instanceof User) {
				$verifier_id = $verifier->getID();
			} elseif (is_numeric($verifier)) {
				$verifier_id = (int) $verifier;
			} else {
				continue;
			}
			$records[] = "(".$q_task_id.",".$db->qstr($verifier_id). ")";
		}
		$query .= implode(",",$records);
		if(false === $db->Execute($query)) {
			add_error("Failed to add verifier to task. Database said: ".$db->ErrorMsg());
			application_log("error", "Unable to create task_verifiersy records. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully added verifier to task.");
		}
	} 
	
	/**
	 * Removes the supplied user(s) from the list of verifiers for the specified task
	 * @param int $task_id
	 * @param array|int|User $verifiers
	 */
	public static function remove($task_id, $verifiers=null) {
		global $db;
		$q_task_id = $db->qstr($task_id);
		if (is_null($verifiers)) {
			$query = "delete from `task_verifiers` where `task_id`=?";
		} else {
			$query = "delete from `task_verifiers` where `task_id`=? and `verifier_id` IN (";
			if( !is_array($verifiers)) {
				$verifiers = array($verifiers);
			} 
			$records = array();
			foreach($verifiers as $verifier) {
				if ($verifier instanceof User) {
					$verifier_id = $verifier->getID();
				} elseif (is_int($verifier)) {
					$verifier_id = $verifier;
				} else {
					continue;
				}
				$records[] = $db->qstr($verifier_id);
			}
			$query .= implode(",",$records) . ")";
		}
		if(!$db->Execute($query, array($task_id))) {
			add_error("Failed to remove verifier from task");
			application_log("error", "Unable to remove from task_verifiers records. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully removed verifiers from task.");
		}		
	}
	
	/**
	 * Returns Tasks collection of Task objects for which the supplied id is the proxy id of the verifier
	 * @param unknown_type $verifier_id
	 * @param unknown_type $options
	 * @return Tasks
	 */
	public static function getTasksByVerifier($verifier_id, $options=array()) {
		global $db;
		
		if (isset($options['dir'])){
			$direction = $options['dir'];
		}
		if (isset($options['order_by'])) {
			if ($options['order_by'] == "deadline") {
				$options['order_by'] = "COALESCE(`deadline`,9223372036854775807)";
			} else {
				$options['order_by'] = '`'.$options['order_by'].'`';
			}
			$order_by = " ORDER BY ".$options['order_by']." ".$direction;
		}
		if (isset($options['limit'])) {
			$limit = $options['limit'];
		} else {
			$limit = -1;
		}
		if (isset($options['offset'])) {
			$offset = $options['offset'];
		} else {
			$offset = -1;
		}
		if (isset($options['where'])) {
			$where = " AND " . $options['where'];
		}
		
		$tasks = array();
		$rs = $db->selectLimit("SELECT a.* from `task_verifiers` b left join `tasks` a on a.`task_id`=b.`task_id` where `verifier_id`=? ".(isset($where) && $where ? $where : "").(isset($order_by) && $order_by ? $order_by : ""), $limit, $offset, array($verifier_id));
		if ($rs) {
			$results = $rs->getIterator();	
			foreach ($results as $result) {
				$task = Task::fromArray($result);
				//$task = new Task($result['task_id'], $result['last_updated_date'], $result['last_updated_by'], $result['title'], $result['deadline'], $result['duration'], $result['description'],$result['release_start'], $result['release_finish'], $result['organisation_id']);
				$tasks[] = $task;
			}
		}
		return new Tasks($tasks);
	}
	
	/**
	 * Returns true if the supplied proxy id is a designated verifier for the specified task
	 * <code>
	 * $proxy_id = 1234;
	 * $task_id = 4321;
	 * if (TaskVerifiers::isVerifier($proxy_id, $task_id)) {
	 *   //do stuff based on condition
	 * }
	 * </code> 
	 * @param int $proxy_id 
	 * @param int $task_id
	 * @return boolean
	 */
	public static function isVerifier($proxy_id, $task_id) {
		global $db;

		$query = "SELECT `verifier_id` from `task_verifiers` where `verifier_id`=? and `task_id`=?";
		
		$result = $db->getOne($query, array($proxy_id,$task_id));
		
		return (!!$result);
	}
} 
