<?php

/**
 * Collection of TaskRecipient objects. tracks only recipients of a single task.
 * @author Jonathan Fingland
 *
 */
class TaskRecipients extends Collection {
	
	/**
	 * Internal ID of the related Task
	 * @var int
	 */
	private $task_id;
	
	/**
	 * Returns Colleciton of Task Recipients including User, Cohort, or Organisation objects
	 * @param int $task_id
	 * @return TaskRecipients
	 */
	public static function get($task_id) {
		global $db;
		
		$query = "SELECT * from `task_recipients` where `task_id`=".$db->qstr($task_id);
		
		$results = $db->getAll($query);
		$recipients = array();
		if ($results) {
			foreach ($results as $result) {
				$rtype = $result['recipient_type'];
				$rid = $result['recipient_id'];
				switch($rtype) {
					case TASK_RECIPIENT_USER:
						$recipient = User::get($rid);
						break;
					case TASK_RECIPIENT_CLASS:
						$recipient = Cohort::get($rid /* cohort */);
						break;
					case TASK_RECIPIENT_ORGANISATION:
						$recipient = Organisation::get($rid);
						break;
					default:
						$recipient = null; // not a valid recipient type. ensures we don't use the same recipient as the last run through the loop.
				}
				if ($recipient) {
					$recipients[] = $recipient;
				}
			}
		}
		return new self($recipients, $task_id);
	}
	
	function __construct($recipients, $task_id) {
		parent::__construct($recipients);
		$this->task_id = $task_id;
	}
	
	/**
	 * Adds Receipients in the array to the task
	 * @param int task_id
	 * @param array $recipients
	 */
	public static function add($task_id, $recipients) {
		global $db;
		$query = "insert ignore into `task_recipients` (`task_id`,`recipient_id`, `recipient_type` ) values ";
		$q_task_id = $db->qstr($task_id);
		$records = array();
		foreach($recipients as $recipient) {
			if ($recipient instanceof User) {
				$recipient_type = TASK_RECIPIENT_USER;
				$recipient_id = $recipient->getID();
			} elseif ($recipient instanceof Cohort) {
				$recipient_type = TASK_RECIPIENT_CLASS;
				$recipient_id = $recipient->getCohort();
			} elseif ($recipient instanceof Organisation) {
				$recipient_type = TASK_RECIPIENT_ORGANISATION;
				$recipient_id = $recipient->getID();
			} elseif (is_array($recipient)) {
				//manually passing type and id
				$recipient_type = $recipient['type'];
				$recipient_id = $recipient['id'];
			} else {
				continue;
			}
			$records[] = "(".$q_task_id.",".$db->qstr($recipient_id)."," . $db->qstr($recipient_type) . ")";
		}
		$query .= implode(",",$records);
		if(!$db->Execute($query)) {
			add_error("Failed to add recipients to task");
			application_log("error", "Unable to create task_recipients records. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully added recipients to task.");
		}
	}
	
	/**
	 * Removes provided TaskRecipients from recipient list. If the $recipients array is null, it will remove all recipients (an empty array will cause an error)   
	 * @param int $task_id
	 * @param array $recipients
	 */
	public static function remove($task_id, $recipients=null) {
		global $db;
		
		if (is_null($recipients)) {
			$query = "delete from `task_recipients` where `task_id`=?";
		} else {
			$query = "delete from `task_recipients` where `task_id`=? and (";
			$q_task_id = $db->qstr($task_id);
			$records = array();
			foreach($recipients as $recipient) {
				if ($recipient instanceof User) {
					$recipient_type = TASK_RECIPIENT_USER;
					$recipient_id = $recipient->getID();
				} elseif ($recipient instanceof Cohort) {
					$recipient_type = TASK_RECIPIENT_CLASS;
					$recipient_id = $recipient->getCohort();
				} elseif ($recipient instanceof Organisation) {
					$recipient_type = TASK_RECIPIENT_ORGANISATION;
					$recipient_id = $recipient->getID();
				} elseif (is_array($recipient)) {
					//manually passing type and id
					$recipient_type = $recipient['type'];
					$recipient_id = $recipient['id'];
				} else {
					continue;
				}
				$records[] = "(`recipient_id`=".$db->qstr($recipient_id)." AND `recipient_type`=" . $db->qstr($recipient_type) . ")";
			}
			$query .= implode(" OR ",$records) . ")";
		}
		if(!$db->Execute($query, array($task_id))) {
			add_error("Failed to remove recipients from task");
			application_log("error", "Unable to remove from task_recipients records. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully removed recipients from task.");
		}		
	}
}