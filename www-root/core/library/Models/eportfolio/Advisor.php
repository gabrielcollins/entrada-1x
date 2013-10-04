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

	private $user,
			$related;
	
	public function __construct($proxy_id) {
		if (is_int($proxy_id)) {
			$this->user = Models_User::get($proxy_id);
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
	
	public static function fetchRow($pfolder_id, $active = 1) {
		global $db;
		
		$query = "SELECT * FROM `portfolio_folders` WHERE `pfolder_id` = ? AND `active` = ?";
		$result = $db->GetRow($query, array($pfolder_id, $active));
		if ($result) {
			$folder = new self($result);
			return $folder;
		} else {
			return false;
		}
	}
	
	public static function fetchAll($role = "faculty", $assigned = NULL, $active = 1) {
		global $db;
		
		$query = "SELECT a.`proxy_id`
					FROM `".AUTH_DATABASE."`.`user_data` AS a
					JOIN `".AUTH_DATABASE."`.`user_access` AS b
					ON a.`id` = b.`user_id`"
					.(!is_null($assigned) ? "JOIN `user_relations` AS c ON a.`id` = c.`from`" : "")."
					WHERE b.`group` = ".$db->qstr($role)."
					AND b.`organisation_id` = 1
					AND (b.`access_expires` = 0 || b.`access_expires` > UNIX_TIMESTAMP(NOW()))
					GROUP BY a.`id`";
		$results = $db->GetAll($query, array($active));
		if ($results) {
			$advisors = array();
			foreach ($results as $result) {
				$advisors[] = new self($result);
			}
			return $advisors;
		} else {
			return false;
		}
	}
	
	public function insert() {
		global $db;
		if ($db->AutoExecute("`portfolio_folders`", $this->toArray(), "INSERT")) {
			$this->pfolder_id = $db->Insert_ID();
			return true;
		} else {
			return false;
		}
	}
	
	public function update() {
		global $db;
		if ($db->AutoExecute("`portfolio_folders`", $this->toArray(), "UPDATE", "`pfolder_id` = ".$db->qstr($this->getID()))) {
			return true;
		} else {
			return false;
		}
	}
	
	public function delete() {
		global $db;
		
		$query = "DELETE FROM `portfolio_folders` WHERE `pfolder_id` = ?";
		$result = $db->Execute($query, array($this->getID()));
		
		return $result;
	}
	
}

?>
