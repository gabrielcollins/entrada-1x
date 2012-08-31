<?php
/**
 * Task mdoel class for organizing and operating on task, and related, data 
 * 
 * @author Jonathan Fingland
 *
 */
class Task {
	//these are constants and not settings as they reflect database restrictions on the model.
	const TITLE_MAX_LENGTH = 255;
	const DURATION_MAX = 43200; //30 days in minutes. XXX: although the db limit is 0xFFFFFFFFFFFFFFFF max of 64-bit number, we use a more reasonable limit which is still likely to be excessive 
	
	
	/**
	 * @var int
	 */
	private $task_id;
		
	/**
	 * Task Title. limited to 255 characters
	 * @var Title
	 */
	private $title;
	
	/**
	 * Description of Task. Limited to 65535 characters
	 * @var unknown_type
	 */
	private $description;
	
	/**
	 * timestamp for when the task should appear to the audience. does not affect visibility for owner
	 * @var int
	 */
	private $release_start;
	
	/**
	 * timestamp for when the task should no longer appear to the audience. does not affect visibility for owner. Should never be earlier than the deadline
	 * @var int
	 */
	private $release_finish;
	
	/**
	 * timestamp for indicating when audience members *should* complete the task by. deadline does not impose constraints in the system; this is intended to be a real world deadline.
	 * @var deadline
	 */
	private $deadline;
	
	/**
	 * Suggested task length in minutes. e.g. 60 minutes. 
	 * @var int
	 */
	private $duration;
	
	/**
	 * timestamp indicating when the task was last updated
	 * @var int
	 */
	private $last_updated;
	
	/**
	 * proxy_id of the user that last updated this task.
	 * @var int
	 */
	private $last_updated_by;
	
	
	/**
	 * id of organisation to which this task belongs.
	 * @var int
	 */
	private $organisation_id;
	
	/**
	 * 
	 * @var string
	 */
	private $verification_type;
	
	/**
	 * 
	 * @var string
	 */
	private $faculty_selection_policy;
	
	/**
	 * 
	 * @var string
	 */
	private $completion_comment_policy;

	/**
	 * 
	 * @var string
	 */
	private $rejection_comment_policy;
	
	/**
	 * unlike the comment and faculty selection policy types, verification notification policies are not mutually exclusive. For this reason, a flag system is employed in which 0 is the absence of any verification, and beyond that the defines TASK_VERIFICATION_NOTIFICATION_* are used. 
	 * @var int
	 */
	private $verification_notification_policy;

	/**
	 * @return string
	 */
	function getVerificationType() {
		return $this->verification_type;
	}
	
	/**
	 * @return string
	 */
	function getFacultySelectionPolicy() {
		return $this->faculty_selection_policy;
	}
	
	/**
	 * @return string
	 */
	function getCompletionCommentPolicy() {
		return $this->completion_comment_policy;
	}
	
	/**
	 * @return string
	 */
	function getRejectionCommentPolicy() {
		return $this->rejection_comment_policy;
	}
	
	/**
	 * @return int
	 */
	function getVerificationNotificationPolicy() {
		return $this->verification_notification_policy;
	}
	
	/**
	 * Returns a User, Course, or Event depending on the owner type
	 * @return mixed
	 */
	function getOwners() {
		return TaskOwners::get($this->task_id);
	}
	
	/**
	 * Returns a list of Users designated as faculty associated with this task
	 * @return TaskAssociatedFaculty
	 */
	function getAssociatedFaculty() {
		return TaskAssociatedFaculty::get($this->task_id);
	}

	/**
	 * Returns the unique id of this task
	 * @return int
	 */
	function getID(){
		return $this->task_id;
	}
	
	/**
	 * Returns the title of the task.
	 * @return string
	 */
	function getTitle() {
		return $this->title;
	}
	
	/**
	 * Returns the description of the task.
	 * @return string
	 */
	function getDescription() {
		return $this->description;
	}
	
	/**
	 * Returns the Deadline set for this task as a timestamp.
	 * @return int
	 */
	function getDeadline() {
		return $this->deadline;
	}
	
	/**
	 * Returns the expected duration required for task completion in minutes
	 * @return int
	 */
	function getDuration() {
		return $this->duration;
	}
	
	/**
	 * Returns a timestamp of the time this information was last updated
	 * @return int
	 */
	function getLastUpdate() {
		return $this->last_updated;
	}
	
	/**
	 * Returns the User that last updated this information
	 * @return User
	 */
	function getLastUpdateUser() {
		return User::get($this->last_updated_by);
	}
	
	/**
	 * Returns the timestamp for when this Task should be made available to the audience. 0 or null if not set.
	 * @return int 
	 */
	function getReleaseStart() {
		return $this->release_start;
	}
	
	/**
	 * Returns the timestamp for when this Task should no longer be available to the audience. 0 or null if not set.
	 * @return int 
	 */
	function getReleaseFinish() {
		return $this->release_finish;
	}
	
	/**
	 * @return Organisation
	 */
	function getOrganisation() {
		return Organisation::get($this->organisation_id);
	}
	
	/**
	 * @return Course
	 */
	function getCourse() {
		return TaskOwners::getCourse($this->task_id);
	} 
	
	/**
	 * 
	 * @param $obj
	 * 
	 * @return bool
	 */
	function addOwner($obj) {
		return TaskOwners::add($this,$obj);
	}
	
	/**
	 * 
	 * @param $obj
	 * 
	 * @return bool
	 */
	function addVerifier($obj) {
		return TaskVerifiers::add($this,$obj);
	}
	
	function getVerifiers() {
		$verification_type = $this->getVerificationType();
		switch($verification_type) {
			case TASK_VERIFICATION_NONE:
				return;
			case TASK_VERIFICATION_FACULTY:
				return TaskCompletions::getVerifiers($this->getID());
			case TASK_VERIFICATION_OTHER:
				return TaskVerifiers::get($this->getID());
		}
	}
	
	/**
	 * 
	 * @param User $user
	 * 
	 * @return bool
	 */
	function isOwner(User $user) {
		
		$task_owners = $this->getOwners();
		if ($task_owners->count() == 0) {
			//no owners? orphan?
			application_log("error", "A task was found to have no owners associated with it. Task ID: ".$task_id);
			return false;
		}
		
		foreach($task_owners as $task_owner) {
			if (($task_owner instanceof User) && ($task_owner === $user)  ) {
				return true;
			} else if(($task_owner instanceof Course) && ($task_owner->isOwner($user))) {
				return true;
			} else if(($task_owner instanceof Event) && ($task_owner->isOwner($user))) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 
	 * @param User $user
	 * 
	 * @return bool
	 */
	function isRecipient(User $user) {
		$task_recipients = TaskRecipients::get($this->task_id);
		
		if ($task_recipients->count() == 0) {
			application_log("error", "A task was found to have no recipients associated with it. Task ID: ".$task_id);
			return false;
		}
		foreach($task_recipients as $task_recipient) {
			if (($task_recipient instanceof User) && ($task_recipient === $user)  ) {
				return true;
			} else if(($task_recipient instanceof Cohort) && ($user->getFullCohort() == $task_recipient)) {
				return true;
			} else if(($task_recipient instanceof Organisation) && ($task_recipient == $user->getOrganisation())) {
				return true;
			}
		}
	}
	
	/**
	 * Returns true if the supplied user is a verifier for any of the recipients however
	 * if the $recipient parameter is supplied, it will check if they are a verifier for *that* recipient.
	 * @param $user
	 * @param $recipient
	 * @return bool
	 */
	function isVerifier(User $user, User $recipient = null) {
		$verification_type = $this->getVerificationType();
		switch($verification_type) {
			case TASK_VERIFICATION_NONE:
				return false;
			case TASK_VERIFICATION_FACULTY:
				if ($recipient) {
					$tc = TaskCompletion::get($this->getID(), $recipient->getID());
					return $tc->isVerifier($user);
				} else {
					return TaskCompletions::isVerifier($user->getID(), $this->task_id);
				}
			case TASK_VERIFICATION_OTHER:
				$user_id = $user->getID(); 
				$verifiers = TaskVerifiers::get($this->getID());
				return $verifiers->contains(User::get($user_id));
		}
	}
	
	/**
	 * @return bool
	 */
	function isFacultySelectionRequired() {
		return $this->require_faculty_selection;
	}
		
	/**
	 * Allows sorting by 'deadline', 'course', and 'title'
	 * @param Task $task
	 * @param string $sort_by
	 */
	function compare(Task $task, $compare_by='title') {
		switch($compare_by) {
			case 'deadline':
				return $this->deadline == $task->deadline ? 0 : ( $this->deadline > $task->deadline ? 1 : -1 );
				break;
			case 'title':
				return strcasecmp($this->title, $task->title);
				break;
			case 'course':
				$thiscourse = $this->getCourse();
				$thatcourse = $task->getCourse();
				$thistitle = ($thiscourse) ? $thiscourse->getTitle() : "";
				$thattitle = ($thatcourse) ? $thatcourse->getTitle() : "";
				return strcasecmp($thiscourse->getTitle(),$thatcourse->getTitle());
				break;
		}
	} 
	
	/**
	 * Creates a new task. Returns new task_id
	 * 
	 * @param int $creator_id
	 * @param string $title
	 * @param int $deadline
	 * @param int $duration
	 * @param string $description
	 * @param int $release_start
	 * @param int $release_finish
	 * @param int $organisation_id
	 * @param bool $require_verification
	 * 
	 * @return int
	 */
	static function create(array $inputs) {
		extract($inputs);
		global $db;
		$query = "insert into `tasks` (`updated_by`, `updated_date`, `title`, `deadline`,`duration`,`description`, `release_start`, `release_finish`, `organisation_id`, `verification_type`, `faculty_selection_policy`, `completion_comment_policy`, `rejection_comment_policy`, `verification_notification_policy`) 
				value (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		if(!$db->Execute($query, array($creator_id, time(), $title, $deadline, $duration, $description, $release_start, $release_finish, $organisation_id, $verification_type, $faculty_selection_policy, $completion_comment_policy, $rejection_comment_policy, $verification_notification_policy))) {
			add_error("Failed to create Task.");
			application_log("error", "Unable to update a tasks record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully created task.");
			return ($db->Insert_ID('tasks','task_id'));
		}
	} 
	
	/**
	 * 
	 * @param array $inputs
	 */
	function update(array $inputs) {
		extract($inputs);
		global $db;
		$query = "UPDATE `tasks` set `updated_by`=?, `updated_date`=?, `title`=?, `deadline`=?, `duration`=?, `description`=?, `release_start`=?, `release_finish`=?, `organisation_id`=?, `verification_type`=?, `faculty_selection_policy`=?, `completion_comment_policy`=?, `rejection_comment_policy`=?, `verification_notification_policy`=? where `task_id`=?";
		
		if(!$db->Execute($query, array($updater_id, time(), $title, $deadline, $duration, $description, $release_start, $release_finish, $organisation_id, $verification_type, $faculty_selection_policy, $completion_comment_policy, $rejection_comment_policy, $verification_notification_policy, $this->task_id))) {
			add_error("Failed to update Task");
			application_log("error", "Unable to update a tasks record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully updated task.");
		}
	}
	
	/**
	 * Deletes the task record AND associated audience, owners, and verification
	 */
	function delete() {
		global $db;
		$query = "	DELETE a,b,c,d
					FROM `tasks` a 
					left join `task_owners` b on a.`task_id`=b.`task_id` 
					left join `task_completion` c on a.`task_id`=c.`task_id`
					left join `task_recipients` d on a.`task_id`=d.`task_id`
					left join `task_verifiers` e on a.`task_id`=e.`task_id`
					where a.`task_id` = ?";
		if(!$db->Execute($query, array($this->task_id))) {
			add_error("Failed to remove task from database.");
			application_log("error", "Unable to delete a tasks record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully removed task.");
		}		
	}
	
	/**
	 * 
	 * @param int $task_id
	 * @return Task
	 */
	static function get($task_id) {
		$cache = SimpleCache::getCache();
		$task = $cache->get("Task",$task_id);
		if (!$task) {
			global $db;
			$query = "SELECT * FROM `tasks` WHERE `task_id` = ".$db->qstr($task_id);
			$result = $db->getRow($query);
			if ($result) {
				$task = self::fromArray($result);		
			}		
		} 
		return $task;
	}
	
	/**
	 * 
	 * @param int $task_id
	 * @param int $last_updated_date
	 * @param int $last_updated_by
	 * @param string $title
	 * @param int $deadline
	 * @param int $duration
	 * @param string $description
	 * @param int $release_start
	 * @param int $release_finish
	 * @param int $organisation_id
	 * @param bool $require_verification
	 */
	function __construct($task_id, $last_updated_date, $last_updated_by, $title, $deadline, $duration = 0, $description = "", $release_start = null, $release_finish = null, $organisation_id=null, $verification_type=null, $verification_notification_policy=null, $faculty_selection_policy=null,$completion_comment_policy=null, $rejection_comment_policy=null ) {
		$this->task_id = $task_id;
		$this->last_updated = $last_updated_date;
		$this->last_updated_by = $last_updated_by;
		$this->title = $title;
		$this->deadline = $deadline;
		$this->duration = $duration;
		$this->description = $description;
		$this->release_start = $release_start;
		$this->release_finish = $release_finish;
		$this->organisation_id = $organisation_id;
		$this->verification_type = $verification_type;
		$this->completion_comment_policy = $completion_comment_policy;
		$this->rejection_comment_policy = $rejection_comment_policy;
		$this->faculty_selection_policy = $faculty_selection_policy;
		$this->verification_notification_policy = $verification_notification_policy;
		
		$cache = SimpleCache::getCache();
		$cache->set($this,"Task", $task_id);
	}
	
	static public function fromArray($arr) {
		return new Task($arr['task_id'], $arr['updated_date'], $arr['updated_by'], $arr['title'], $arr['deadline'], $arr['duration'], $arr['description'],$arr['release_start'], $arr['release_finish'], $arr['organisation_id'], $arr['verification_type'], $arr['verification_notification_policy'], $arr['faculty_selection_policy'],  $arr['completion_comment_policy'],  $arr['rejection_comment_policy']);
	}
}