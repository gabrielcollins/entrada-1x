<?php

class Models_Objective {
	
	private	$objective_id,
			$objective_code,
			$objective_name,
			$objective_description,
			$objective_parent,
			$objective_order,
			$objective_loggable,
			$objective_active,
			$updated_date,
			$updated_by;
			
	public function __construct(	$objective_id = NULL,
                                    $objective_code = NULL,
                                    $objective_name = NULL,
                                    $objective_description = NULL,
                                    $objective_parent = NULL,
                                    $objective_order = NULL,
                                    $objective_loggable = NULL,
                                    $objective_active = 1,
                                    $updated_date = NULL,
                                    $updated_by = NULL) {
		
		$this->objective_id = $objective_id;
		$this->objective_code = $objective_code;
		$this->objective_name = $objective_name;
		$this->objective_description = $objective_description;
		$this->objective_parent = $objective_parent;
		$this->objective_order = $objective_order;
		$this->objective_loggable = $objective_loggable;
		$this->objective_active = $objective_active;
		$this->updated_date = $updated_date;
		$this->updated_by = $updated_by;
		
	}
	
	public function getID() {
		return $this->objective_id;
	}
	
	public function getCode() {
		return $this->objective_code;
	}
	
	public function getName() {
		return $this->objective_name;
	}
	
	public function getParent() {
		return $this->objective_parent;
	}
	
	public function getOrder() {
		return $this->objective_order;
	}
	
	public function getDateUpdated() {
		return $this->updated_date;
	}
	
	public function getUpdatedBy() {
		return $this->updated_by;
	}
	
	public function getLoggable() {
		return $this->objective_loggable;
	}
	
	public function getActive() {
		return $this->objective_active;
	}
	
	/**
	 * Returns objects values in an array.
	 * @return Array
	 */
	public function toArray() {
		$arr = false;
		$class_vars = get_class_vars("Models_Objective");
		if (isset($class_vars)) {
			foreach ($class_vars as $class_var => $value) {
				$arr[$class_var] = $this->$class_var;
			}
		}
		return $arr;
	}
	
	/**
	 * Uses key-value pair to set object values
	 * @return Organisation
	 */
	public function fromArray($arr) {
		foreach ($arr as $class_var_name => $value) {
			$this->$class_var_name = $value;
		}
		return $this;
	}
	
    public static function fetchByOrganisation($organisation_id, $active = 1) {
		global $db;
		
		$objectives = false;
		
		$query = "SELECT a.* FROM `global_lu_objectives` AS a
                    JOIN `objective_organisations` AS b
                    ON a.`objective_id` = b.`objective_id`
                    WHERE b.`organisation_id` = ? 
                    AND a.`objective_active` = ?";
		$results = $db->GetAll($query, array($organisation_id, $active));
		if ($results) {
			foreach ($results as $result) {
				$objectives[] = new self($result["objective_id"], $result["objective_name"], $result["objective_description"], $result["objective_parent"], $result["objective_order"], $result["objective_loggable"], $result["updated_date"], $result["updated_by"], $result["objective_active"]);
			}
		}
		
        return $objectives;
    }
    public static function fetchAll($parent_id = NULL, $active = 1) {
		global $db;
		
		$objectives = false;
		
		$query = "SELECT * FROM `global_lu_objectives` WHERE `objective_active` = ?".(isset($parent_id) && ($parent_id || $parent_id === 0) ? " AND `objective_parent` = ?" : "");
		$results = $db->GetAll($query, array($active, $parent_id));
		if ($results) {
			foreach ($results as $result) {
				$objective = new self();
				$objectives[$result["objective_id"]] = $objective->fromArray($result);
			}
		}
		
        return $objectives;
    }
    
    public static function fetchObjectiveSet($objective_id) {
        global $db;

        $parent_id = (int)$objective_id;

        if (!$parent_id) {
            return false;
        }

        $level = 0;

        do{
            $level++;
            $parent = self::fetchRow($parent_id);
            $parent_id = (int) $parent->getParent();
        } while($parent_id && $level < 10);

        if ($level == 10) {
            return false;
        }

        return $parent;
    }
    
    public static function descendantInArray($objective_id, $objective_ids_array, $first_level = false) {
        global $db;
        if (!$first_level && in_array($objective_id, $objective_ids_array)) {
            return true;
        }
        $found = false;
        $children = self::fetchAll($objective_id);
        if (!$children || !@count($children)) {
            return false;
        } else {
            foreach ($children as $child) {
                if (self::descendantInArray($child->getID(), $objective_ids_array)) {
                    $found = true;
                    break;
                }
            }
        }
        return $found;
    }

    public static function fetchRow($objective_id = 0, $active = 1) {
        global $db;

		$return = false;
		
		if ($objective_id != 0) {
			$query = "SELECT * FROM `global_lu_objectives` WHERE `objective_id` = ? AND `objective_active` = ?";
			$result = $db->GetRow($query, array($objective_id, $active));
			if ($result) {
				$objective = new self();
				$return = $objective->fromArray($result);
			}
		}
		
        return $return;
    }

    public function insert() {
		global $db;
		
		if ($db->AutoExecute("`global_lu_objectives`", $this->toArray(), "INSERT")) {
			$this->objective_id = $db->Insert_ID();
			return true;
		} else {
			return false;
		}
    }

	public function update() {
		global $db;
		
		if ($db->AutoExecute("`global_lu_objectives`", $this->toArray(), "UPDATE", "`objective_id` = ".$db->qstr($this->getID()))) {
			return true;
		} else {
			return false;
		}
		
    }

    public function delete() {
        $this->objective_active = false;
		return $this->update();
    }
    
    public static function getChildIDs($objective_id) {
        global $db;
        
        $objective_ids = array();
        $query = "SELECT `objective_id` FROM `global_lu_objectives` WHERE `objective_parent` = ".$db->qstr($objective_id);
        $child_ids = $db->GetAll($query);
        if ($child_ids) {
            foreach ($child_ids as $child_id) {
                $objective_ids[] = $child_id["objective_id"];
                $grandchild_ids = Models_Objective::getChildIDs($child_id["objective_id"]);
                if ($grandchild_ids) {
                    foreach ($grandchild_ids as $grandchild_id) {
                        $objective_ids[] = $grandchild_id;
                    }
                }
            }
        }
        return $objective_ids;
    }
	
}