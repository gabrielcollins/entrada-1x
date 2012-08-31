<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Entrada is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Entrada is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Entrada.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

require_once("Task.class.php");
require_once("Models/utility/Collection.class.php");

/**
 * Utility Class for getting a list of Courses
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@quensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class Tasks extends Collection {
	
	/**
	 * Returns a Collection of Task objects
	 * @param array
	 * @return Tasks
	 */
	static public function getAll($options = null) {
		global $db;
		$ORGANISATION_ID	= $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"];
		
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
		
		$tasks = array();
		$rs = $db->selectLimit("SELECT * from `tasks` where `organisation_id`=? ".$order_by, $limit, $offset, array($ORGANISATION_ID));
		if ($rs) {
			$results = $rs->getIterator();	
			foreach ($results as $result) {
				$task = Task::fromArray($result);
				//$task = new Task($result['task_id'], $result['last_updated_date'], $result['last_updated_by'], $result['title'], $result['deadline'], $result['duration'], $result['description'],$result['release_start'], $result['release_finish'], $result['organisation_id']);
				$tasks[] = $task;
			}
		}
		return new self($tasks);
	}
	
	/**
	 * Returns a Collection of Tasks.. tries to guess which type you want. Recommended to go with getBy____ functions
	 * @param int $award_id
	 * @return Tasks
	 */
	static public function get($obj = null, $options = null) {
		if ($obj instanceof User) {
			$tasks = self::getByOwner($obj, $options);
		} elseif ($obj instanceof Course) {
			$tasks = self::getByCourse($obj, $options);
		} elseif ($obj instanceof Event) {
			$tasks = self::getByEvent($obj, $options);
		} else {
			$tasks = self::getAll($options);
		}
		return $tasks;
	}
	
	/**
	 * Returns a collection of Task objects for which the provided user is a recipient 
	 * @param User $user
	 * @param array $options Options array for limiting and sorting.
	 * @return Tasks
	 */
	static public function getByRecipient(User $user, $options=null) {
		$org = $user->getOrganisation();
		
		$user_id = $user->getID();
		$org_id = $org->getID();
		$cohort = $user->getCohort();
		
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
			$where = ' AND ' . $options['where'];
		}		
		
		$tasks = array();
		$query = "	SELECT a.* from `tasks` a 
					left join `task_recipients` b on a.`task_id`=b.`task_id` 
					where (b.`recipient_type`=? AND b.`recipient_id`=?) 
					OR (b.`recipient_type`=? AND b.`recipient_id`=?) 
					OR (b.`recipient_type`=? AND b.`recipient_id`=?)";
		$rs = $db->selectLimit($query.$where.$order_by, $limit, $offset, array(TASK_RECIPIENT_USER,$user_id,TASK_RECIPIENT_CLASS,$cohort,TASK_RECIPIENT_ORGANISATION,$org_id));
		if ($rs) {
			$results = $rs->getIterator();	
			foreach ($results as $result) {
				$task = Task::fromArray($result);
				$tasks[] = $task;
			}
		}
		return new self($tasks);
		 
	}
	
	/**
	 * Returns a collection of Task objects for which the provided user is an owner (user) 
	 * @param User $user
	 * @param array $options
	 * @return Tasks
	 */
	static public function getByOwner(User $user, $options=null) {
		return self::getByOwnerType(TASK_OWNER_USER,$user->getID(), $options);
	}
	
	/**
	 * Returns a collection of Task objects for which the provided course is associated
	 * @param Course $course
	 * @param array $options
	 * @return Tasks
	 */
	static public function getByCourse(Course $course, $options=null) {
		return self::getByOwnerType(TASK_OWNER_COURSE, $course->getID(), $options);
	}
	
	/**
	 * Returns a collection of Task objects for which the provided Event is associated
	 * @param Event $event
	 * @param array $options
	 * @return Tasks
	 */
	static public function getByEvent(Event $event, $options=null) {
		return self::getByOwnerType(TASK_OWNER_EVENT,$event->getID(), $options);
	}
	
	/**
	 * Returns a collection of Task objects for which the provided owner_type and corresponding owner_id are owners/associated
	 * @param string $owner_type
	 * @param int $owner_id
	 * @param array $options
	 * @return Tasks
	 */
	static private function getByOwnerType($owner_type, $owner_id, $options=null) {
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
		
		$tasks = array();
		$rs = $db->selectLimit("SELECT a.* from `tasks` a left join `task_owners` b on a.`task_id`=b.`task_id` where b.`owner_type`=? AND b.`owner_id`=? ".$order_by, $limit, $offset, array($owner_type,$owner_id));
		if ($rs) {
			$results = $rs->getIterator();	
			foreach ($results as $result) {
				$task = Task::fromArray($result);
				$tasks[] = $task;
			}
		}
		return new self($tasks);
		
	}
}
