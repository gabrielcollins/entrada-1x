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
 * Models_Eportfolio
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <rw65@queensu.ca>
 * @copyright Copyright 2013 Queen's University. All Rights Reserved.
 */

class Models_Eportfolio {

	private $portfolio_id,
			$group_id,
			$portfolio_name,
			$start_date,
			$finish_date,
			$active = 1,
			$updated_date,
			$updated_by,
			$organisation_id,
			$allow_student_export = 1;
	
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
	
	public static function fetchRow($portfolio_id, $active = 1) {
		global $db;
		
		$query = "SELECT * FROM `portfolios` WHERE `portfolio_id` = ? AND `active` = ?";
		$result = $db->GetRow($query, array($portfolio_id, $active));
		if ($result) {
			$portfolio = new self($result);
			return $portfolio;
		} else {
			return false;
		}
	}
	
	public static function fetchRowByGroupID($group_id, $active = 1) {
		global $db;
		
		$query = "SELECT * FROM `portfolios` WHERE `group_id` = ? AND `active` = ?";
		$result = $db->GetRow($query, array($group_id, $active));
		if ($result) {
			$portfolio = new self($result);
			return $portfolio;
		} else {
			return false;
		}
	}
	
	public static function fetchAll($active = 1) {
		global $db;
		
		$query = "SELECT * FROM `portfolios` WHERE `active` = ?";
		$results = $db->GetAll($query, array($active));
		if ($results) {
			$portfolios = array();
			foreach ($results as $result) {
				$portfolios[] = new self($result);
			}
			return $portfolios;
		} else {
			return false;
		}
	}
	
	public function insert() {
		global $db;
		if ($db->AutoExecute("`portfolios`", $this->toArray(), "INSERT")) {
			$this->portfolio_id = $db->Insert_ID();
			return true;
		} else {
			return false;
		}
	}
	
	public function update() {
		global $db;
		if ($db->AutoExecute("`portfolios`", $this->toArray(), "UPDATE", "`portfolio_id` = ".$db->qstr($this->getID()))) {
			return true;
		} else {
			return false;
		}
	}
	
	public function delete() {
		global $db;
		
		$query = "DELETE FROM `portfolios` WHERE `portfolio_id` = ?";
		$result = $db->Execute($query, array($this->getID()));
		
		return $result;
	}

	public function getID() {
		return $this->portfolio_id;
	}
	
	public function getGroupID() {
		return $this->group_id;
	}
	
	public function getPortfolioName() {
		return $this->portfolio_name;
	}
	
	public function getStartDate() {
		return $this->start_date;
	}
	
	public function getFinishDate() {
		return $this->finish_date;
	}
	
	public function getActive() {
		return $this->active;
	}
	
	public function getUpdatedDate() {
		return $this->updated_date;
	}
	
	public function getUpdatedBy() {
		$user = User::get($this->updated_by);
		return $user;
	}
	
	public function getOrganisationID() {
		return $this->organisation_id;
	}
	
	public function getAllowStudentExport() {
		return $this->allow_student_export;
	}
	
	public function getFolders() {
		$folders = Models_Eportfolio_Folder::fetchAll($this->portfolio_id);
		return $folders;
	}
	
}

?>
