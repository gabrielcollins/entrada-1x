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
 * Models_Eportfolio_Folder_Artifact
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <rw65@queensu.ca>
 * @copyright Copyright 2013 Queen's University. All Rights Reserved.
 */
class Models_Eportfolio_Folder_Artifact {
	private $pfartifact_id,
			$pfolder_id,
			$artifact_id,
			$proxy_id,
			$title,
			$description,
			$start_date,
			$finish_date,
			$allow_commenting = 0,
			$order,
			$active = 1,
			$updated_date,
			$updated_by,
			$_edata;
	
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
	
	
	public static function fetchRow($pfartifact_id, $active = 1) {
		global $db;
		
		$query = "SELECT * FROM `portfolio_folder_artifacts` WHERE `pfartifact_id` = ? AND `active` = ?";
		$result = $db->GetRow($query, array($pfartifact_id, $active));
		if ($result) {
			$folder = new self($result);
			return $folder;
		} else {
			return false;
		}
	}
	
	public static function fetchAll($pfolder_id = NULL, $active = 1) {
		global $db;
		
		$query = "SELECT * FROM `portfolio_folder_artifacts` WHERE ".(!is_null($pfolder_id) ? "`pfolder_id` = " . $db->qstr($pfolder_id) . " AND " : "")." `active` = ?";
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
		if ($db->AutoExecute("`portfolio_folder_artifacts`", $this->toArray(), "INSERT")) {
			$this->pfartifact_id = $db->Insert_ID();
			return true;
		} else {
			return false;
		}
	}
	
	public function update() {
		global $db;
		if ($db->AutoExecute("`portfolio_folder_artifacts`", $this->toArray(), "UPDATE", "`pfartifact_id` = ".$db->qstr($this->getID()))) {
			return true;
		} else {
			return false;
		}
	}
	
	public function delete() {
		global $db;
		
		$query = "DELETE FROM `portfolio_folder_artifacts` WHERE `pfartifact_id` = ?";
		$result = $db->Execute($query, array($this->getID()));
		
		return $result;
	}	

	public function getID() {
		return $this->pfartifact_id;
	}
	
	public function getPfolderID() {
		return $this->pfolder_id;
	}
	
	public function getPfolder() {
		$pfolder = Models_Eportfolio_Folder::fetchRow($this->pfolder_id);
		return $pfolder;
	}
	
	public function getArtifactID() {
		return $this->artifact_id;
	}
	
	public function getArtifact() {
		// @todo: return instance of artifact model
		return false;
	}
	
	public function getProxyID() {
		return $this->proxy_id;
	}
	
	public function getTitle() {
		return $this->title;
	}
	
	public function getDescription() {
		return $this->description;
	}
	
	public function getStartDate() {
		return $this->start_date;
	}
	
	public function getFinishDate() {
		return $this->finish_date;
	}
	
	public function getAllowCommenting() {
		return $this->allow_commenting;
	}
	
	public function getOrder() {
		return $this->order;
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
	
	public function getEdata() {
		return $this->_edata;
	}
	
	public function getEdataDecoded() {
		return unserialize($this->_edata);
	}
	
	public function getReviewers() {
		$reviewers = Models_Eportfolio_Folder_Artifact_Reviewer::fetchAll($this->pfartifact_id);
		return $reviewers;
	}
	
}

?>
