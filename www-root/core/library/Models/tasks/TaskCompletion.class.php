<?php

require_once("Models/tasks/Task.class.php");

/**
 * 
 * 
 * @author Jonathan Fingland
 *
 */
class TaskCompletion {
	/**
	 * ID of the task we're working with
	 * @var int
	 */
	private $task_id;
	
	/**
	 * Proxy ID of the user receiving the task
	 * @var int
	 */
	private $recipient_id;
	
	/**
	 * Proxy ID of the user verifying completion
	 * @var int
	 */
	private $verifier_id;
	
	/**
	 * timestamp the verifier verified completion
	 * @var int
	 */
	private $verified_date;
	
	/**
	 * timestamp the recipient stated completion
	 * @var int
	 */
	private $completed_date;
	
	/**
	 * Proxy ID of the specific faculty associated with this task completion 
	 * @var int
	 */
	private $faculty_id;
	
	/**
	 * Comment provided on completion of this task
	 * @var string
	 */
	private $completion_comment;
	
	/**
	 * Comment provided on rejection of this task completion information
	 * @var string
	 */
	private $rejection_comment;
	
	/**
	 * timestamp of the rejection date
	 * @var int
	 */
	private $rejection_date;
	
	/**
	 * Returns the User obhect for the specific faculty associated with this task
	 * @return User
	 */
	public function getFaculty() {
		if ($this->faculty_id) {
			return User::get($this->faculty_id);
		}
	}
	
	/**
	 * Returns the comment entered by the task recipient when completing the task
	 * @return string
	 */
	public function getCompletionComment() {
		return $this->completion_comment;
	}
	
	/**
	 * Returns the comment entered by the task verifier when rejecting the claim of completion
	 * @return string
	 */
	public function getRejectionComment() {
		return $this->rejection_comment;
	}
	
	/**
	 * Returns the timestamp for the date of rejection
	 * @return number
	 */
	public function getRejectionDate() {
		return $this->rejection_date;
	}
	
	/**
	 * Returns true if the completion claim has been rejected
	 * @return boolean
	 */
	public function isRejected() {
		return $this->rejection_date > 0;
	}
	
	/**
	 * Returns the Task receipient (User) associated with this completion information
	 * @return User
	 */
	public function getRecipient() {
		return User::get($this->recipient_id);
	}
	
	/**
	 * Returns the User that verified completion of this task
	 * @return User
	 */
	public function getVerifier() {
		return User::get($this->verifier_id);
	}
	
	/**
	 * Returns true if the supplied User is a valid verifier for this task
	 * @param User $user
	 * 
	 * @return bool
	 */
	public function isVerifier(User $user) {
		return $user === $this->getVerifier();
	}
	
	/**
	 * Returns true if the task completion has been verified
	 * @return bool
	 */
	public function isVerified() {
		return $this->verified_date > 0;
	}
	
	/**
	 * Returns the timestamp for the date task completion was claimed 
	 * @return int
	 */
	public function getVerifiedDate() {
		return $this->verified_date;
	}
	
	/**
	 * Returns true if the task has been completed as claimed (irrespective of verification)
	 * @return bool
	 */
	public function isCompleted() {
		return $this->completed_date > 0;
	}
	
	/**
	 * Returns the timestamp for the date for which completionwas claimed 
	 * @return int
	 */
	public function getCompletedDate() {
		return $this->completed_date;
	}
	
	/**
	 * Returns the task associated with this completion information
	 * @return Task
	 */
	public function getTask() {
		return Task::get($this->task_id);
	}
	
	/**
	 * Updates the data in the database for this completion information using the supplied array as inputs 
	 * @param unknown_type $update_data
	 */
	public function update(array $update_data) {
		extract($update_data);
		global $db;
		
		//Insert/update to account for potentially missing entries
		$query = "	INSERT into `task_completion` (`task_id`, `recipient_id`, `verifier_id`, `verified_date`, `completed_date`, `faculty_id`, `completion_comment`, `rejection_comment`, `rejection_date`) value (?,?,?,?,?,?,?,?,?) 
					on duplicate key update `verifier_id`=?, `verified_date`=?, `completed_date`=?, `faculty_id`=?, `completion_comment`=?, `rejection_comment`=?, `rejection_date`=?";
		
		if(!$db->Execute($query,array($this->task_id, $this->recipient_id, 
				$verifier_id, $verified_date, $completed_date, $faculty_id, $completion_comment, $rejection_comment, $rejection_date, 
				$verifier_id, $verified_date, $completed_date, $faculty_id, $completion_comment, $rejection_comment, $rejection_date
				))) {
			add_error("Failed to update task completion information");
			application_log("error", "Unable to update a task_completion record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully updated task completion record");
		}
	}
	
	/**
	 * Returns the TaskCompletion for the provided task-recipient pair
	 * @param int $task_id
	 * @param int $recipient_id
	 * 
	 * @return TaskCompletion 
	 */
	public static function get($task_id, $recipient_id) {
		$cache = SimpleCache::getCache();
		$task_v = $cache->get("TaskCompletion",$task_id,$recipient_id);
		
		if (!$task_v) {
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
						WHERE b.`task_id` = ? AND a.`id` = ?";
			$result = $db->GetRow($query, array($task_id,$recipient_id));
			
			if ($result) {
				$task_v = self::fromArray($result);
			} 
		}
		return $task_v;
	}
	
	function __construct($task_id, $recipient_id, $verifier_id=null, $verified_date=null, $completed_date=null, $faculty_id=null, $completion_comment=null, $rejection_comment=null, $rejection_date=null) {
		$this->task_id = $task_id;
		$this->recipient_id = $recipient_id;
		$this->verifier_id = $verifier_id;
		$this->verified_date = $verified_date;
		$this->completed_date = $completed_date;
		$this->faculty_id = $faculty_id;
		$this->completion_comment = $completion_comment;
		$this->rejection_comment = $rejection_comment;
		$this->rejection_date = $rejection_date;

		$cache = SimpleCache::getCache();
		$cache->set($this,"TaskCompletion",$task_id);
	}
	
	/**
	 * Returns a TaskCompletion object built using the supplied array inputs  
	 * @param unknown_type $arr
	 * @return TaskCompletion
	 */
	public static function fromArray(array $arr) {
		return new self($arr['task_id'],$arr['recipient_id'],$arr['verifier_id'],$arr['verified_date'], $arr['completed_date'], $arr['faculty_id'], $arr['completion_comment'], $arr['rejection_comment'], $arr['rejection_date']);  
	}
}