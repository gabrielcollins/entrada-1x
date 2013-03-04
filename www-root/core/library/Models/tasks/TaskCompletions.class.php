<?php

require_once("Models/utility/Collection.class.php");
require_once("TaskCompletion.class.php");
require_once("Models/users/User.class.php");

/**
 * Collection of TaskCompletions and methods for retrieving completion data from the database
 * @author Jonathan Fingland
 *
 */
class TaskCompletions extends Collection {
	
	/**
	 * Returns a collection of TaskCompletion objects. One for each user requiring verification. 
	 * 
	 * @param int $task_id
	 * @param array $options Sorting and limiting result options
	 * @return TaskCompletions
	 */
	public static function getByTask($task_id, $options=null) {
		
		if (isset($options['order_by'])) {
			if (is_array($options['order_by'])) {
				foreach ($options['order_by'] as $orders) {
					$order[] = "`".$orders[0]."` ". (isset($orders[1]) ? $orders[1] : "asc"); 
				}	
			}
			$order_by = " ORDER BY ".implode(",",$order);
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
		
		global $db;
		$query = "	SELECT a.*, b.`task_id`, c.`verifier_id`, c.`verified_date`, a.`id` as `recipient_id`, c.`completed_date`, c.`faculty_id`, c.`completion_comment`, c.`rejection_comment`, c.`rejection_date`
					FROM `".AUTH_DATABASE."`.`user_data` AS a
					LEFT JOIN `group_members` AS gm
					ON a.`id` = gm.`proxy_id`
					LEFT JOIN `groups` AS g
					ON gm.`group_id` = g.`group_id`
					AND g.`group_type` = 'cohort'
					INNER JOIN `task_recipients` AS b 
					ON 
					(
						b.`recipient_type` = 'cohort' 
						AND g.`group_id` = b.`recipient_id`
					) 
					OR 
					(
						b.`recipient_type` = 'user' 
						AND a.`id` = b.`recipient_id`
					) 
					OR 
					(
						b.`recipient_type` = 'organisation' 
						AND b.`recipient_id` = a.`organisation_id`
					) 
					LEFT JOIN `task_completion` AS c 
					ON c.`task_id` = b.`task_id` 
					AND c.`recipient_id` = a.`id`
					WHERE b.`task_id` = ?";
		
		$results = $db->SelectLimit($query,$limit, $offset, array($task_id));
		
		$completions = array();
		if ($results) {
			foreach ($results as $result) {
				$completion = TaskCompletion::fromArray($result);
				$recipient = User::get($result["id"]); //and throw away. will be retrieved from cache when needed
				$completions[] = $completion;
			}
		}
		return new self($completions);
	}
	
	/**
	 * Returns a collection of TaskCompletion objects. One for each task for which the provided user is a verifier 
	 * 
	 * @param int $task_id
	 * @param array $options Sorting and limiting result options
	 * @return TaskCompletions
	 */
	public static function getByVerifier($proxy_id, $options=null) {
		
		if (isset($options['order_by'])) {
			if (is_array($options['order_by'])) {
				foreach ($options['order_by'] as $orders) {
					$order[] = "`".$orders[0]."` ". (isset($orders[1]) ? $orders[1] : "asc"); 
				}	
			}
			$order_by = " ORDER BY ".implode(",",$order);
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
		global $db;
		
		$query = "	SELECT a.*, b.*,c.* from `task_completion` a
					left join `tasks` b on b.`task_id`=a.`task_id`
					inner join `".AUTH_DATABASE."`.`user_data` c on a.`recipient_id`=c.`id` 
					where a.`verifier_id`=?".$where.(isset($order_by) && $order_by ? $order_by : "");
		
		$results = $db->SelectLimit($query,$limit, $offset, array($proxy_id));
		$completions = array();
		if ($results) {
			foreach ($results as $result) {
				$task = Task::fromArray($result);//for cache
				$user = User::get($result["id"]);//for cache
				$completion = TaskCompletion::fromArray($result);
				$completions[] = $completion;
			}
		}
		return new self($completions);
	}
	
	/**
	 * Returns true if the provided user (ID) is a verifier for the provided task (ID) 
	 * @param int $proxy_id
	 * @param int $task_id
	 * @return boolean
	 */
	public static function isVerifier($proxy_id, $task_id) {
		global $db;

		$query = "SELECT `verifier_id` from `task_completion` where `verifier_id`=? and `task_id`=?";
		
		$result = $db->getOne($query, array($proxy_id,$task_id));
		
		return (!!$result);
	}
	
	/**
	 * Returns a collection of Users that are verifiers of the specified task
	 * @param int $task_id
	 * @return TaskVerifiers
	 */
	public static function getVerifiers($task_id) {
		global $db;

		$query = "SELECT  b.* from `task_completions` a left join `".AUTH_DATABASE."`.`user_data` b on a.`verifier_id`=b.`id` where a.`task_id`=?";
		
		$result = $db->getAll($query, array($task_id));
		$verifiers = array();
		if ($results) {
			foreach ($results as $result) {
				$verifier = User::get($result["id"]);
				if ($verifier) {
					$verifiers[] = $verifier;
				}
			}
		}
		return new TaskVerifiers($verifiers);
	}
	
	/**
	 * Returns a collection of TaskCompletion objects. One for each task for which the provided user is a recipient
	 * @param User $recipient
	 * @param unknown_type $options
	 * @return TaskCompletions
	 */
	public static function getByRecipient(User $recipient, $options=null) {
		if (isset($options['order_by'])) {
			if (is_array($options['order_by'])) {
				foreach ($options['order_by'] as $orders) {
					if ($orders[0] == "deadline") {
						$orders[0] = "COALESCE(`deadline`,9223372036854775807)";
					} else {
						$orders[0] = "`".$orders[0]."`";
					}
					$order[] = $orders[0]." ". (isset($orders[1]) ? $orders[1] : "asc"); 
				}	
			}
			$order_by = " ORDER BY ".implode(",",$order);
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
			$where = ' and ' . $options['where'] . " ";
		}		
		
		global $db;
		
		$proxy_id = $recipient->getID();
		
		$query = "	SELECT a.id, d.*, c.`verifier_id`, c.`verified_date`, a.`id` as `recipient_id`, c.`completed_date`, c.`faculty_id`, c.`completion_comment`, c.`rejection_comment`, c.`rejection_date`
					FROM `".AUTH_DATABASE."`.`user_data` AS a
					LEFT JOIN `group_members` AS gm
					ON a.`id` = gm.`proxy_id`
					LEFT JOIN `groups` AS g
					ON gm.`group_id` = g.`group_id`
					AND g.`group_type` = 'cohort'
					INNER JOIN `task_recipients` AS b 
					ON 
					(
						b.`recipient_type` = 'cohort' 
						AND g.`group_id` = b.`recipient_id`
					) 
					OR 
					(
						b.`recipient_type` = 'user' 
						AND a.`id` = b.`recipient_id`
					) 
					OR 
					(
						b.`recipient_type` = 'organisation' 
						AND b.`recipient_id` = a.`organisation_id`
					) 
					LEFT JOIN `task_completion` AS c 
					ON c.`task_id` = b.`task_id` 
					AND c.`recipient_id` = a.`id`
					LEFT JOIN `tasks` AS d 
					ON b.`task_id` = d.`task_id`
					where a.id = ?";

		$results = $db->SelectLimit($query.$where.(isset($order_by) && $order_by ? $order_by : ""), $limit, $offset, array($proxy_id));
		
		$completions = array();
		if ($results) {
			foreach ($results as $result) {
				$completion = TaskCompletion::fromArray($result);
				$task = Task::fromArray($result);
				$completions[] = $completion;
			}
		}
		return new self($completions);
		
	}
}