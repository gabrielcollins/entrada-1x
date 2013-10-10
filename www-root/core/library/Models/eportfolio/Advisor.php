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
 * Models_Eportfolio_Folder
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <rw65@queensu.ca>
 * @copyright Copyright 2013 Queen's University. All Rights Reserved.
 */

class Models_Eportfolio_Advisor {

	private $padvisor_id,
			$proxy_id,
			$firstname,
			$lastname,
			$related,
			$active = 1;
	
	public function __construct($arr = NULL) {
		if (is_array($arr)) {
			$this->fromArray($arr);
		}
	}
	
	public function toArray() {
		$arr = false;
		$class_vars = get_class_vars(get_called_class());
		if (isset($class_vars)) {
			foreach ($class_vars as $class_var => $value) {
				$arr[$class_var] = $this->$class_var;
			}
		}
		return $arr;
	}
	
	public function fromArray($arr) {
		foreach ($arr as $class_var_name => $value) {
			$this->$class_var_name = $value;
		}
		return $this;
	}
	
	public static function fetchRow($proxy_id, $organisation_id = 1) {
		global $db;
		
		$query = "SELECT a.`padvisor_id`, b.`id` AS `proxy_id`, b.`firstname`, b.`lastname`
					FROM `portfolio-advisors` AS a
					JOIN `entrada-gh-auth`.`user_data` AS b
					ON a.`proxy_id` = b.`id`
					JOIN `entrada-gh-auth`.`user_access` AS c
					ON a.`proxy_id` = c.`user_id`
					AND c.`organisation_id` = ".$db->qstr($organisation_id)."
					WHERE a.`proxy_id` = ".$db->qstr($proxy_id)."
					GROUP BY a.`proxy_id`";
		$result = $db->GetRow($query);
		if ($result) {
			$query = "SELECT * FROM `".AUTH_DATABASE."`.`user_relations` WHERE `from` = ".$db->qstr($proxy_id);
			$related = $db->GetAll($query);
			if ($related) {
				$result["related"] = $related;
			}
			$advisor = new self($result);
			return $advisor;
		} else {
			return false;
		}
	}
	
	public static function fetchAll($organisation_id = 1) {
		global $db;
		
		$query = "SELECT a.`padvisor_id`, b.`id` AS `proxy_id`, b.`firstname`, b.`lastname`
					FROM `portfolio-advisors` AS a
					JOIN `entrada-gh-auth`.`user_data` AS b
					ON a.`proxy_id` = b.`id`
					JOIN `entrada-gh-auth`.`user_access` AS c
					ON a.`proxy_id` = c.`user_id`
					AND c.`organisation_id` = ".$db->qstr($organisation_id)."
					GROUP BY a.`proxy_id`";
		$results = $db->GetAll($query);
		if ($results) {
			$advisors = array();
			foreach ($results as $result) {
				$query = "SELECT * FROM `".AUTH_DATABASE."`.`user_relations` WHERE `from` = ".$db->qstr($result["proxy_id"]);
				$related = $db->GetAll($query);
				if ($related) {
					$result["related"] = $related;
				}
				$advisors[] = new self($result);
			}
			return $advisors;
		} else {
			return false;
		}
	}
	
	public function insert() {
		global $db;
		if ($db->AutoExecute("`portfolio-advisors`", $this->toArray(), "INSERT")) {
			$this->padvisor_id = $db->Insert_ID();
			return true;
		} else {
			return false;
		}
	}
	
	public function update() {
		global $db;
		if ($db->AutoExecute("`portfolio-advisors`", $this->toArray(), "UPDATE", "`padvisor_id` = ".$db->qstr($this->getID()))) {
			return true;
		} else {
			return false;
		}
	}
	
	public function delete() {
		global $db;
		
		$query = "DELETE FROM `portfolio-advisors` WHERE `padvisor_id` = ?";
		$result = $db->Execute($query, array($this->getID()));
		
		return $result;
	}
	
	public function getFirstName() {
		return $this->firstname;
	}
	
	public function getLastName() {
		return $this->lastname;
	}
	
	public function getID() {
		return $this->padvisor_id;
	}
	
	public function getProxyID() {
		return $this->proxy_id;
	}
	
	public function getRelated() {
		return $this->related;
	}
	
	public static function deleteRelation($advisor_id, $student_id) {
		global $db;
		$query = "DELETE FROM `".AUTH_DATABASE."`.`user_relations` WHERE `from` = ".$db->qstr($advisor_id)." AND `to` = ".$db->qstr($student_id);
		$result = $db->Execute($query);
		if ($result) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function addRelation($advisor_id, $student_id) {
		global $db;
		$query = "INSERT INTO `".AUTH_DATABASE."`.`user_relations` (`from`, `to`, `type`) VALUES (".$db->qstr($advisor_id).", ".$db->qstr($student_id).", '1')";
		$results = $db->Execute($query);
		if ($result) {
			return true;
		} else {
			return false;
		}
	}
	
}

?>
