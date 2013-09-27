<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Entrada Resource Tree Builder
 *
 * Used to create an ACL tree of Zend_ACL resources for application of permissions after.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class Entrada_ACL extends ACL_Factory {
	var $acl;
	var $default_ptable;
	var $ptable;
	var $modules = array (
		"mom" => array (
			"awards",
			"community",
			"communityadmin",
			"configuration",
			"course" => array (
				"coursecontent",
				"event" => array (
					"eventcontent"
				)
			),
			"evaluation" => array (
				"evaluationform" => array (
					"evaluationformquestion"
				),
				"evaluationquestion"
			),
			"gradebook" => array (
				"assessment"
			),
			"assignment",
			"regionaled" => array (
				"apartments",
				"regions",
				"schedules"
			),
			"regionaled_tab",
			"dashboard",
			"clerkship" => array (
				"electives",
				"logbook",
				"lottery",
                "categories"
			),
			"term",
			"objective",
			"clerkshipschedules",
			"discussion",
			"photo",
			"firstlogin",
			"library",
			"people",
			"podcast",
			"profile" => array(
				"mspr"
			),
			"observerships",
			"search",
			"notice",
			"permission",
			"poll",
			"report",
			"reportindex",
			"quiz" => array (
				"quizquestion",
				"quizresult"
			),
			"user" => array (
				"incident",
				"metadata"
			),
			"assistant_support",
			"resourceorganisation",
			"evaluations" => array (
									"forms",
									"notifications",
									"reports"
									),
			"annualreport",
			"annualreportadmin",
			"anonymous-feedback",
			"mydepartment",
			"myowndepartment",
			"group",
            "encounter_tracking"
		)
	);
	/**
	 * Constructs the ACL upon instantiation of the class
	 *
	 * @param array $user,ils The user for which the ACL is being constructed details. $_SESSION["details"] is usually used
	 */
	function __construct($userdetails) {

		global $db;

		$this->default_ptable = "`".AUTH_DATABASE."`.`acl_permissions`";

		//Fetch all the different users this current user could masquerade as.
		$query = "	SELECT a.*, b.`id` AS `proxy_id`, CONCAT_WS(', ', b.`lastname`, b.`firstname`) AS `fullname`, b.`firstname`, b.`lastname`, b.`organisation_id`, c.`role`, c.`group`, c.`id` AS `access_id`
					FROM `permissions` AS a
					JOIN `".AUTH_DATABASE."`.`user_data` AS b
					ON b.`id` = a.`assigned_by`
					JOIN `".AUTH_DATABASE."`.`user_access` AS c
					ON c.`user_id` = b.`id` AND c.`app_id`=".$db->qstr(AUTH_APP_ID)."
					AND c.`account_active`='true'
					AND (c.`access_starts`='0' OR c.`access_starts`<=".$db->qstr(time()).")
					AND (c.`access_expires`='0' OR c.`access_expires`>=".$db->qstr(time()).")
					WHERE a.`assigned_to`=".$db->qstr($userdetails["id"])."
					AND a.`valid_from` <= ".$db->qstr(time())."
					AND a.`valid_until` >= ".$db->qstr(time())."
					ORDER BY `fullname` ASC";
		$results = $db->GetAll($query);
		if ($results) {
			foreach ($results as $result) {
				$permissions[$result["access_id"]] = array("id" => $result["proxy_id"], "access_id" => $result["access_id"], "permission_id" => $result["permission_id"], "group" => $result["group"], "role" => $result["role"], "organisation_id" => $result["organisation_id"], "starts" => $result["valid_from"], "expires" => $result["valid_until"], "fullname" => $result["fullname"], "firstname" => $result["firstname"], "lastname" => $result["lastname"]);
			}
		}

		// Add all user_access records to the $permissions tree by organisation.
		$query = "	SELECT b.`id` AS `proxy_id`, c.`id` as ua_id, e.`organisation_id`, e.`organisation_title`, CONCAT_WS(', ', b.`lastname`, b.`firstname`) AS `fullname`, b.`firstname`, b.`lastname`, c.`role`, c.`group`
					FROM `".AUTH_DATABASE."`.`user_data` AS b
					JOIN `".AUTH_DATABASE."`.`user_access` AS c
					ON c.`user_id` = b.`id`
					AND b.`id` = " . $userdetails["id"] . "
					AND c.`app_id`=".$db->qstr(AUTH_APP_ID)."
					AND c.`account_active`='true'
					AND (c.`access_starts`='0' OR c.`access_starts`<=".$db->qstr(time()).")
					AND (c.`access_expires`='0' OR c.`access_expires`>=".$db->qstr(time()).")
					JOIN `".AUTH_DATABASE."`.`organisations` AS e
					ON e.`organisation_id` = c.`organisation_id`
					ORDER BY c.`group` ASC";
		$results = $db->GetAll($query);
		if ($results) {
			foreach ($results as $result) {
				$permissions[$result["ua_id"]] = array("group" => $result["group"], "role" => $result["role"], "organisation_id" => $result["organisation_id"], "fullname" => $result["fullname"], "firstname" => $result["firstname"], "lastname" => $result["lastname"]);
			}
		}

		// Next, fetch all the role-resource permissions related to all these users.
		$this->rr_permissions = $this->_fetchPermissions($permissions);

		/**
		 * Next, "Clean" the permissions. This should create a permissions record for each active user_access record associated with
		 * the user id passed in from the acl_permissions table. This may need to change so the permissions table simply uses an
		 * access_id in the future for more granular permissions granting, but in the mean-time, this will ensure each access record for
		 * the user will be granted the same custom access from a user `entity_type` record.
		 */
		$clean_permissions = array();
		foreach ($this->rr_permissions as $permissions_record) {
			if ($permissions_record["entity_type"] != "user") {
				$clean_permissions[] = $permissions_record;
			} else {
				$query = "	SELECT `id`
							FROM `".AUTH_DATABASE."`.`user_access`
							WHERE `user_id` = ".$db->qstr($permissions_record["entity_value"])."
							AND `app_id` = ".$db->qstr(AUTH_APP_ID)."
							AND `account_active` = 'true'
							AND (`access_starts` = '0' OR `access_starts` <= ".$db->qstr(time()).")
							AND (`access_expires` = '0' OR `access_expires` >= ".$db->qstr(time()).")";
				$access_ids = $db->getAll($query);
				if ($access_ids) {
					foreach ($access_ids as $access_id) {
						$permissions_record["entity_value"] = $access_id["id"];
						$clean_permissions[] = $permissions_record;
					}
				}
			}
		}

		// This adds all the resources referenced by the permissions to the ACL.
		$acl = $this->_build($permissions, $clean_permissions);

		// Add generic roles
		foreach (array("organisation", "group", "role", "user") as $entity_type) {
			$acl->addRole(new Zend_Acl_Role($entity_type));
		}

		// Initialize variables for use throughout creation
		foreach ($permissions as $access_id => $permission_mask) {
			$cur_access_id = $access_id;
			$cur_role = $permission_mask["role"];
			$cur_group = $permission_mask["group"];
			$cur_organisation = (array_key_exists("organisation_title", $permission_mask) ? $permission_mask["organisation_title"] : NULL);
			$cur_organisation_id = $permission_mask["organisation_id"];

			if (!$acl->hasRole("organisation".$cur_organisation_id)) {
				$acl->addRole(new Zend_Acl_Role("organisation".$cur_organisation_id), "organisation");
			}

			if (!$acl->hasRole("group".$cur_group)) {
				$acl->addRole(new Zend_Acl_Role("group".$cur_group), array("group", "organisation".$cur_organisation_id));
			}

			if (!$acl->hasRole("role".$cur_role)) {
				$acl->addRole(new Zend_Acl_Role("role".$cur_role), array("role", "group".$cur_group));
			}

			$user_role = new Zend_Acl_Role("user".$cur_access_id);
			$acl->addRole($user_role, array("role".$cur_role, "user"));
		}

		//Instantiate ACL_Factory to facilitate application of rules
		$this->acl = new ACL_Factory($acl);

		//Create the final ACL
		$this->acl->create_acl($clean_permissions);
	}

	/**
	 * Asks the ACL if the $user is allowed to preform the $action on the $resource. Asserts by default.
	 *
	 * @param string|Zend_Acl_Role_Interface $user Either the string identifier or role object for the user being queried
	 * @param string|Zend_Acl_Resource_Interface $resource Either the string identifier or the resource object for the resource being queried
	 * @param string $action The action or priviledge being queried with.
	 * @param boolean $assert If false, any rules applying to this role resource pair but contingent on assertions will be counted, regardless of the assertion's outcome. Warning: the assertion applied must support this property.
	 * @return boolean
	 */
	function isAllowed($user, $resource, $action, $assert = true) {
		if ($resource instanceof Zend_Acl_Resource_Interface) {
			$resource->assert = $assert;
		} else {
		 	$resource = new EntradaAclResource($resource, $assert);
		}

		if (!($user instanceof Zend_Acl_Role_Interface)) {
			$user = new EntradaUser($user);
		}

		return $this->acl->isAllowed($user, $resource, $action);
	}

	/**
	 * Asks the ACL if the user role defined by the active proxy_id (the active permission mask) is allowed to preform the $action on the $resource. Asserts by default.
	 *
	 * @param string|Zend_Acl_Resource_Interface $resource
	 * @param <type> $action
	 * @param <type> $assert
	 * @return <type>
	 */
	function amIAllowed($resource, $action, $assert = true) {
		global $ENTRADA_USER;

		$user = new EntradaUser("user".$ENTRADA_USER->getAccessId());
		$current_details = $_SESSION["details"];
		$current_details["access_id"] = $ENTRADA_USER->getAccessId();
		$current_details["role"] = $ENTRADA_USER->getActiveRole();
		$current_details["group"] = $ENTRADA_USER->getActiveGroup();
		$current_details["organisation_id"] = $ENTRADA_USER->getActiveOrganisation();
		$user->details = $current_details;

		return $this->isAllowed($user, $resource, $action, $assert);
	}

	/**
	 * Asks the ACL if the currently logged in user role $ENTRADA_USER->getID() is allowed to preform the $action on the $resource. Asserts by default.
	 *
	 * @param string|Zend_Acl_Resource_Interface $resource
	 * @param <type> $action
	 * @param <type> $assert
	 * @return <type>
	 */
	function isLoggedInAllowed($resource, $action, $assert = true) {
		global $ENTRADA_USER;
		$user = new EntradaUser("user".$ENTRADA_USER->getAccessId());
		$user->details = $_SESSION["details"];
		return $this->isAllowed($user, $resource, $action, $assert);
	}

	/**
	 * Constructs and populates Zend_Acl_Interface
	 * with all the resources referenced by the role-resource permissions generated in _fetchPermissions().
	 *
	 * @param array $permission_masks An array of possible proxy ids
	 * @param array $rr_permissions Optional array of role-resource permissions as returned from the database. If not given, they will be fetched based on the supplied permission masks.
	 */
	function _build($permission_masks, $rr_permissions = null) {
		global $db;

		if (!isset($rr_permissions)) {
			$rr_permissions = $this->_fetchPermissions($permission_masks);
		}
		//First, add the base roles for each type of entity
		$acl = new Zend_Acl_Plus();

		$this->_parseResourceTree(null, $this->modules, $acl);

		foreach ($rr_permissions as $perm) {
			if (isset($perm["resource_type"]) && isset($perm["resource_value"]) && !$acl->has($perm["resource_type"].$perm["resource_value"])) {
				$acl->add(new Zend_Acl_Resource($perm["resource_type"].$perm["resource_value"]), $perm["resource_type"]);
			}
		}
		return $acl;
	}

	/**
	 * Takes a nested array of resources and parses them into the supplied ACL with inheritance intact. Operates only on the ACL supplied.
	 *
	 * @param string $parent The parent resource to be set for the resources given
	 * @param array $resources The optionally nested array of resources to be parsed into the ACL
	 * @param Zend_Acl $acl The acl object to be operated on
	 * @return boolean
	 */
	function _parseResourceTree($parent, $resources, &$acl) {
		if (!isset($resources)) {
			return false;
		}
		if (is_array($resources)) {
			foreach ($resources as $key => $value) {
				if (is_array($value)) {
					$acl->add(new Zend_Acl_Resource($key), $parent);
					$this->_parseResourceTree($key, $value, $acl);
				} else {
					$acl->add(new Zend_Acl_Resource($value), $parent);
				}
			}
		}
		return true;
	}

	/**
	 * 	Fetches all the relevant role-resource permissions (those pertinent to the possbile masks) from the default permissions table
	 *
	 * @param  array $permission_masks An array of possible proxy ids
	 * @return array
	 */
	function _fetchPermissions($permission_masks) {
		global $db;
		//Next, fetch all the role-resource permissions related to all these users.
		$table = $this->default_ptable;
		$query[] = "SELECT * FROM $table WHERE \n";
		$count = 0;
		foreach ($permission_masks as $access_id => $permission_mask) {
			// Initialize variables for use throughout creation
			$cur_access_id = $access_id;
			$cur_role = $permission_mask["role"];
			$cur_group = $permission_mask["group"];
			$cur_organisation_id = $permission_mask["organisation_id"];

			$access_query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access` WHERE `id` = ".$db->qstr($cur_access_id);
			$cur_proxy_id = $db->GetOne($access_query);

			$query[] = ($count && $count > 0 ? "OR " : "(")."($table.`entity_value` = '".$cur_proxy_id."' AND $table.`entity_type` = 'user') OR
								($table.`entity_value` = '".$cur_role."' AND $table.`entity_type` = 'role') OR
								($table.`entity_value` = '".$cur_group."' AND $table.`entity_type` = 'group') OR
								($table.`entity_value` = '".$cur_organisation_id."' AND $table.`entity_type` = 'organisation') OR
								($table.`entity_value` = '".$cur_group.":".$cur_role."' AND $table.`entity_type` = 'group:role') OR
								($table.`entity_value` = '".$cur_organisation_id.":".$cur_group."' AND $table.`entity_type` = 'organisation:group') OR
								($table.`entity_value` = '".$cur_organisation_id.":".$cur_group.":".$cur_role."' AND $table.`entity_type` = 'organisation:group:role') ";
			$count++;
		}

		$query[] = "OR ($table.`entity_value` IS NULL AND $table.`entity_type` IS NULL))\n";
		$query[] = "AND ($table.`app_id` IS NULL OR $table.`app_id` = '".AUTH_APP_ID."')\n";
		$query[] = "ORDER BY $table.`resource_value` ASC, $table.`entity_value` ASC;";

		$complete_query = "";
		foreach ($query as $part) {
			$complete_query .= $part;
		}

		return $db->GetAll($complete_query);
	}
}

class MultipleAssertion implements Zend_Acl_Assert_Interface {
	var $assertions = array();

	function MultipleAssertion($a_assertions) {
		$this->assertions = $a_assertions;
	}

	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		foreach ($this->assertions as $assertion) {
			$name = $assertion."Assertion";
			$assertion = new $name();
			if (!$assertion->assert($acl, $role, $resource, $privilege)) {
				return false;
			}
		}
		return true;
	}
}

/**
 * Course Owner Assertion
 *
 * Used to assert that the course referenced by the course resource is owned by the user referenced by the user role.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class CourseOwnerAssertion implements Zend_Acl_Assert_Interface {
	/**
	* Asserts that the role references the director, coordinator, or secondary director of the course resource
	*
	* @param Zend_Acl $acl The ACL object isself (the one calling the assertion)
	* @param Zend_Acl_Role_Interface $role The role being queried
	* @param Zend_Acl_Resource_Interface $resource The resource being queried
	* @param string $privilege The privilege being queried
	* @return boolean
	*/
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		global $db;
		//If asserting is off then return true right away
		if ((isset($resource->assert) && $resource->assert == false) || (isset($acl->_entrada_last_query) && isset($acl->_entrada_last_query->assert) && $acl->_entrada_last_query->assert == false)) {
			return true;
		}

		if (isset($resource->course_id)) {
			$course_id = $resource->course_id;
		} else if (isset($acl->_entrada_last_query->course_id)) {
			$course_id = $acl->_entrada_last_query->course_id;
		} else {
			//Parse out the user ID and course ID
			$resource_id = $resource->getResourceId();
			$resource_type = preg_replace('/[0-9]+/', "", $resource_id);

			if ($resource_type !== "course" && $resource_type !== "coursecontent") {
				//This only asserts for users on courses.
				return false;
			}

			$course_id = preg_replace('/[^0-9]+/', "", $resource_id);
		}

		$role_id = $role->getRoleId();
		$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

		$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access` WHERE `id` = ".$db->qstr($access_id);
		$user_id = $db->GetOne($query);
		if (!isset($user_id) || !$user_id) {
			$role_id = $acl->_entrada_last_query_role->getRoleId();
			$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

			$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access` WHERE `id` = ".$db->qstr($access_id);
			$user_id = $db->GetOne($query);
		}

		return $this->_checkCourseOwner($user_id, $course_id);
	}

	/**
	 * Checks if the $user_id is a director or program coordinator of a course.
	 *
	 * @param string|integer $user_id The proxy_id to be checked
	 * @param string|integer $course_id The course id to be checked
	 * @return boolean
	 */
	static function _checkCourseOwner($user_id, $course_id) {
		//Logic taken from the old permissions_check() function.
		global $db;

		$query	=  "SELECT a.`pcoord_id` AS `coordinator`, b.`proxy_id` AS `director_id`, d.`proxy_id` AS `admin_id`, e.`proxy_id` AS `pcoordinator`
					FROM `".DATABASE_NAME."`.`courses` AS a
					LEFT JOIN `".DATABASE_NAME."`.`course_contacts` AS b
					ON b.`course_id` = a.`course_id`
					AND b.`contact_type` = 'director'
					LEFT JOIN `".DATABASE_NAME."`.`community_courses` AS c
					ON c.`course_id` = a.`course_id`
					LEFT JOIN `".DATABASE_NAME."`.`community_members` AS d
					ON d.`community_id` = c.`community_id`
					AND d.`member_active` = '1'
					AND d.`member_acl` = '1'
					LEFT JOIN `".DATABASE_NAME."`.`course_contacts` AS e
					ON e.`course_id` = a.`course_id`
					AND e.`contact_type` = 'pcoordinator'
					WHERE a.`course_id` = ".$db->qstr($course_id)."
					AND (a.`pcoord_id` = ".$db->qstr($user_id)."
						OR b.`proxy_id` = ".$db->qstr($user_id)."
						OR d.`proxy_id` = ".$db->qstr($user_id)."
						OR e.`proxy_id` = ".$db->qstr($user_id)."
					)
					AND a.`course_active` = '1'
					LIMIT 0, 1";
		$result = $db->GetRow($query);
		if ($result) {
			foreach (array("director_id", "coordinator", "admin_id", "pcoordinator") as $owner) {
				if ($result[$owner] == $user_id) {
					return true;
				}
			}
		}

		return false;
	}
}

/**
 * Not Course Owner Assertion
 *
 * Used to assert that the course referenced by the course resource is not owned by the user referenced by the user role.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>, Don Zuiker <don.zuiker@queensu.ca>
 * @copyright Copyright 2010, 2013 Queen's University. All Rights Reserved.
 */
class NotCourseOwnerAssertion implements Zend_Acl_Assert_Interface {
	/**
	* Asserts that the role references the director, coordinator, or secondary director of the course resource
	*
	* @param Zend_Acl $acl The ACL object isself (the one calling the assertion)
	* @param Zend_Acl_Role_Interface $role The role being queried
	* @param Zend_Acl_Resource_Interface $resource The resource being queried
	* @param string $privilege The privilege being queried
	* @return boolean
	*/
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		global $db;
		//If asserting is off then return true right away
		if ((isset($resource->assert) && $resource->assert == false) || (isset($acl->_entrada_last_query) && isset($acl->_entrada_last_query->assert) && $acl->_entrada_last_query->assert == false)) {
			return false;
		}

		if (isset($resource->course_id)) {
			$course_id = $resource->course_id;
		} else if (isset($acl->_entrada_last_query->course_id)) {
			$course_id = $acl->_entrada_last_query->course_id;
		} else {
			//Parse out the user ID and course ID
			$resource_id = $resource->getResourceId();
			$resource_type = preg_replace('/[0-9]+/', "", $resource_id);

			if ($resource_type !== "course" && $resource_type !== "coursecontent") {
				//This only asserts for users on courses.
				return false;
			}

			$course_id = preg_replace('/[^0-9]+/', "", $resource_id);
		}

		$role_id = $role->getRoleId();
		$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

		$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access` WHERE `id` = ".$db->qstr($access_id);
		$user_id = $db->GetOne($query);
		if (!isset($user_id) || !$user_id) {
			$role_id = $acl->_entrada_last_query_role->getRoleId();
			$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

			$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access` WHERE `id` = ".$db->qstr($access_id);
			$user_id = $db->GetOne($query);
		}

		return $this->_checkCourseOwner($user_id, $course_id);
	}

	/**
	 * Checks if the $user_id is a director or program coordinator of a course.
	 *
	 * @param string|integer $user_id The proxy_id to be checked
	 * @param string|integer $course_id The course id to be checked
	 * @return boolean
	 */
	static function _checkCourseOwner($user_id, $course_id) {
		//Logic taken from the old permissions_check() function.
		global $db;

		$query	=  "SELECT a.`pcoord_id` AS `coordinator`, b.`proxy_id` AS `director_id`, d.`proxy_id` AS `admin_id`, e.`proxy_id` AS `pcoordinator`
					FROM `".DATABASE_NAME."`.`courses` AS a
					LEFT JOIN `".DATABASE_NAME."`.`course_contacts` AS b
					ON b.`course_id` = a.`course_id`
					AND b.`contact_type` = 'director'
					LEFT JOIN `".DATABASE_NAME."`.`community_courses` AS c
					ON c.`course_id` = a.`course_id`
					LEFT JOIN `".DATABASE_NAME."`.`community_members` AS d
					ON d.`community_id` = c.`community_id`
					AND d.`member_active` = '1'
					AND d.`member_acl` = '1'
					LEFT JOIN `".DATABASE_NAME."`.`course_contacts` AS e
					ON e.`course_id` = a.`course_id`
					AND e.`contact_type` = 'pcoordinator'
					WHERE a.`course_id` = ".$db->qstr($course_id)."
					AND (a.`pcoord_id` = ".$db->qstr($user_id)."
						OR b.`proxy_id` = ".$db->qstr($user_id)."
						OR d.`proxy_id` = ".$db->qstr($user_id)."
						OR e.`proxy_id` = ".$db->qstr($user_id)."
					)
					AND a.`course_active` = '1'
					LIMIT 0, 1";

		$result = $db->GetRow($query);
		if ($result) {
			foreach (array("director_id", "coordinator", "admin_id", "pcoordinator") as $owner) {
				if ($result[$owner] == $user_id) {
					return false;
				}
			}
		}

		return true;
	}
}

/**
 * Course Enrollment Assertion
 *
 * Used to assert that proxy_id is enrolled in a particular course based on their membership status
 * in the corresponding course website (community).
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class CourseEnrollmentAssertion implements Zend_Acl_Assert_Interface {

	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		global $db;
		//If asserting is off then return true right away
		if ((isset($resource->assert) && $resource->assert == false) || (isset($acl->_entrada_last_query) && isset($acl->_entrada_last_query->assert) && $acl->_entrada_last_query->assert == false)) {
			return false;
		}

		if (isset($resource->course_id)) {
			$course_id = $resource->course_id;
		} else if (isset($acl->_entrada_last_query->course_id)) {
			$course_id = $acl->_entrada_last_query->course_id;
		} else {
			// Parse out the user ID and course ID
			$resource_id = $resource->getResourceId();
			$resource_type = preg_replace('/[0-9]+/', "", $resource_id);

			if ($resource_type !== "course" && $resource_type !== "coursecontent") {
				// This only asserts for users on courses.
				return false;
			}

			$course_id = preg_replace("/[^0-9]+/", "", $resource_id);
		}

		$role_id = $role->getRoleId();
		$access_id = preg_replace('/[^0-9]+/', "", $role_id);

		$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
					WHERE `id` = ".$db->qstr($access_id);
		$user_id = $db->GetOne($query);

		if (!isset($user_id) || !$user_id) {
			$role_id = $acl->_entrada_last_query_role->getRoleId();
			$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

			$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
						WHERE `id` = ".$db->qstr($access_id);
			$user_id = $db->GetOne($query);
		}
		return !($this->_checkCourseEnrollment($user_id, $course_id));
	}

	/**
	 * Checks if the $user_id is an active member of the corresponding
	 * course website (community).
	 *
	 * @param string|integer $user_id The proxy_id to be checked
	 * @param string|integer $course_id The course id to be checked
	 * @return boolean
	 */
	static function _checkCourseEnrollment($user_id, $course_id) {
        global $db;

		$query = "	SELECT *
					FROM `courses`
					WHERE `course_id` = " . $db->qstr($course_id);
		$result = $db->GetRow($query);

		if ($result["permission"] == "open") {
			return true;
		} else {
			$query = "	SELECT *
						FROM `community_courses`
						WHERE `course_id` = " . $db->qstr($course_id);
			$result = $db->GetRow($query);
			if ($result) {
				$query = "	SELECT *
							FROM `community_members`
							WHERE `community_id` = " . $db->qstr($result["community_id"]) . "
							AND `proxy_id` = " . $db->qstr($user_id) . "
							AND `member_active` = 1";
				$result = $db->GetRow($query);
				if ($result) {
					return true;
				}
			}
			$query = "  SELECT *
						FROM `course_audience`
						WHERE `course_id` = " . $db->qstr($course_id);
			$results = $db->GetAll($query);
			if ($results) {
				foreach ($results as $result) {
					switch ($result["audience_type"]) {
						case "proxy_id":
							if ($result["audience_value"] == $user_id) {
								return true;
							}
							break;
						case "group_id":
							$query = "  SELECT a.*
										FROM `group_members` AS a
										JOIN `groups` AS b
										ON a.`group_id` = b.`group_id`
										WHERE a.`group_id` = ".$db->qstr($result["audience_value"]) . "
										AND a.`proxy_id` = " . $db->qstr($user_id) . "
										AND a.`member_active` = 1
										AND b.`group_active` = 1
										AND (
											(UNIX_TIMESTAMP() >= a.`start_date` OR a.`start_date` = 0) AND
											(UNIX_TIMESTAMP() <= a.`finish_date` OR a.`finish_date` = 0)
										)";
							$result = $db->GetRow($query);

							if ($result) {
								return true;
							}
							break;
						default:
							break;
					}
				}
			}
			return false;
        }
    }
}

class NotEventEnrollmentAssertion implements Zend_Acl_Assert_Interface {
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		global $db;
		//If asserting is off then return false right away
		if ((isset($resource->assert) && $resource->assert == false) || (isset($acl->_entrada_last_query) && isset($acl->_entrada_last_query->assert) && $acl->_entrada_last_query->assert == false)) {
			return false;
		}

		if (isset($resource->event_id)) {
			$event_id = $resource->event_id;
		} else if (isset($acl->_entrada_last_query->event_id)) {
			$event_id = $acl->_entrada_last_query->event_id;
		} else {
			// Parse out the user ID and course ID
			$resource_id = $resource->getResourceId();
			$resource_type = preg_replace('/[0-9]+/', "", $resource_id);

			if ($resource_type !== "event") {
				// This only asserts for users on events.
				return false;
			}

			$event_id = preg_replace("/[^0-9]+/", "", $resource_id);
		}

		if (isset($resource->course_id)) {
			$course_id = $resource->course_id;
		} else if (isset($acl->_entrada_last_query->course_id)) {
			$course_id = $acl->_entrada_last_query->course_id;
		} else {
			// Parse out the user ID and course ID
			$resource_id = $resource->getResourceId();
			$resource_type = preg_replace('/[0-9]+/', "", $resource_id);

			if ($resource_type !== "event") {
				// This only asserts for users on events.
				return false;
			}

			$course_id = preg_replace("/[^0-9]+/", "", $resource_id);
		}

		$role_id = $role->getRoleId();
		$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

		$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
					WHERE `id` = ".$db->qstr($access_id);
		$user_id = $db->GetOne($query);

		if (!isset($user_id) || !$user_id) {
			$role_id = $acl->_entrada_last_query_role->getRoleId();
			$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

			$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
						WHERE `id` = ".$db->qstr($access_id);
			$user_id = $db->GetOne($query);
		}
		return !($this->_checkEventEnrollment($user_id, $event_id, $course_id));
	}

	/**
	 * Checks if the $user_id is an active member of the corresponding
	 * event.
	 *
	 * @param string|integer $user_id The proxy_id to be checked
	 * @param string|integer $event_id The event id to be checked
	 * @param string|integer $course_id The course id to be checked
	 * @return boolean
	 */
	static function _checkEventEnrollment($user_id, $event_id, $course_id) {
        global $db;

            $query = "    SELECT *
                        FROM `courses`
                        WHERE `course_id` = ".$db->qstr($course_id);
            $result = $db->GetRow($query);

            if ($result["permission"] == "open") {
                //return false so that the course resource acl permission is tested.
                return true;
            }

        $query = "    SELECT *
                    FROM `event_audience`
                    WHERE `event_id` = " . $db->qstr($event_id);
        $results = $db->GetAll($query);

        if ($results) {
            foreach($results as $result) {
                switch($result["audience_type"]) {
                    case "proxy_id":
                        if ($result["audience_value"] == $user_id) {
                            return true;
                        }
                        break;
                    case "cohort":
                    case "group_id":
						$query = "  SELECT a.*
									FROM `group_members` AS a
									JOIN `groups` AS b
									ON a.`group_id` = b.`group_id`
									WHERE a.`group_id` = ".$db->qstr($result["audience_value"]) . "
									AND a.`proxy_id` = " . $db->qstr($user_id) . "
									AND a.`member_active` = 1
									AND b.`group_active` = 1
									AND (
										(UNIX_TIMESTAMP() >= a.`start_date` OR a.`start_date` = 0) AND
										(UNIX_TIMESTAMP() <= a.`finish_date` OR a.`finish_date` = 0)
									)";
                        $result = $db->GetRow($query);
                        if ($result) {
                            return true;
                        }
                        break;
                    case "cgroup_id":
                        $query = "    SELECT *
                                    FROM `course_group_audience` cga
                                    JOIN `curriculum_periods` cp
                                    ON cga.`cperiod_id` = cp.`cperiod_id`
                                    WHERE cga.`cgroup_id` = ".$db->qstr($result["audience_value"]) . "
                                    AND cga.`proxy_id` = " . $db->qstr($user_id) . "
                                    AND    cga.`active` = 1
                                    AND UNIX_TIMESTAMP() BETWEEN cp.`start_date` AND cp.`finish_date`";
                        $result = $db->GetRow($query);
                        if ($result) {
                            return true;
                        }
                        break;
                    case "course_id":
                        //hand off to course enrollment checking.
                        return true;
                    default:
                        break;
                }
            }
        }
        $query = "    SELECT *
                    FROM `course_contacts` AS cc
                    WHERE cc.`proxy_id` = " . $db->qstr($user_id) . "
                    AND cc.`course_id` = " . $db->qstr($course_id);

        $result = $db->GetRow($query);

        if ($result) {
            return true;
        }

        $query = "    SELECT *
                    FROM `event_contacts` AS ec
                    JOIN `events` as e
                    ON e.`event_id` = ec.`event_id`
                    WHERE ec.`proxy_id` = " . $db->qstr($user_id) . "
                    AND ec.`event_id` = " . $db->qstr($event_id) . "
                    AND e.`course_id` = " . $db->qstr($course_id);

        $result = $db->GetRow($query);

        if ($result) {
            return true;
        }

        return false;
    }
}

/**
 * Event Enrollment Assertion
 *
 * Used to assert that proxy_id is enrolled in a particular event based on their membership status
 * in the corresponding event audience.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Don Zuiker <don.zuiker@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 */
class EventEnrollmentAssertion implements Zend_Acl_Assert_Interface {

	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		global $db;
		//If asserting is off then return true right away
		if ((isset($resource->assert) && $resource->assert == false) || (isset($acl->_entrada_last_query) && isset($acl->_entrada_last_query->assert) && $acl->_entrada_last_query->assert == false)) {
			return true;
		}

		if (isset($resource->event_id)) {
			$event_id = $resource->event_id;
		} else if (isset($acl->_entrada_last_query->event_id)) {
			$event_id = $acl->_entrada_last_query->event_id;
		} else {
			// Parse out the user ID and course ID
			$resource_id = $resource->getResourceId();
			$resource_type = preg_replace('/[0-9]+/', "", $resource_id);

			if ($resource_type !== "event") {
				// This only asserts for users on events.
				return false;
			}

			$event_id = preg_replace("/[^0-9]+/", "", $resource_id);
		}

		if (isset($resource->course_id)) {
			$course_id = $resource->course_id;
		} else if (isset($acl->_entrada_last_query->course_id)) {
			$course_id = $acl->_entrada_last_query->course_id;
		} else {
			// Parse out the user ID and course ID
			$resource_id = $resource->getResourceId();
			$resource_type = preg_replace('/[0-9]+/', "", $resource_id);

			if ($resource_type !== "event") {
				// This only asserts for users on events.
				return false;
			}

			$course_id = preg_replace("/[^0-9]+/", "", $resource_id);
		}

		$role_id = $role->getRoleId();
		$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

		$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
					WHERE `id` = ".$db->qstr($access_id);
		$user_id = $db->GetOne($query);

		if (!isset($user_id) || !$user_id) {
			$role_id = $acl->_entrada_last_query_role->getRoleId();
			$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

			$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
						WHERE `id` = ".$db->qstr($access_id);
			$user_id = $db->GetOne($query);
		}
		return $this->_checkEventEnrollment($user_id, $event_id, $course_id);
	}

	/**
	 * Checks if the $user_id is an active member of the corresponding
	 * event.
	 *
	 * @param string|integer $user_id The proxy_id to be checked
	 * @param string|integer $event_id The event id to be checked
	 * @param string|integer $course_id The course id to be checked
	 * @return boolean
	 */
	static function _checkEventEnrollment($user_id, $event_id, $course_id) {
        global $db;

            $query = "    SELECT *
                        FROM `courses`
                        WHERE `course_id` = ".$db->qstr($course_id);
            $result = $db->GetRow($query);

            if ($result["permission"] == "open") {
                //return false so that the course resource acl permission is tested.
                return false;
            }

        $query = "    SELECT *
                    FROM `event_audience`
                    WHERE `event_id` = " . $db->qstr($event_id);
        $results = $db->GetAll($query);

        if ($results) {
            foreach($results as $result) {
                switch($result["audience_type"]) {
                    case "proxy_id":
                        if ($result["audience_value"] == $user_id) {
                            return true;
                        }
                        break;
                    case "group_id":
                    case "cohort":
						$query = "  SELECT a.*
									FROM `group_members` AS a
									JOIN `groups` AS b
									ON a.`group_id` = b.`group_id`
									WHERE a.`group_id` = ".$db->qstr($result["audience_value"]) . "
									AND a.`proxy_id` = " . $db->qstr($user_id) . "
									AND a.`member_active` = 1
									AND b.`group_active` = 1
									AND (
										(UNIX_TIMESTAMP() >= a.`start_date` OR a.`start_date` = 0) AND
										(UNIX_TIMESTAMP() <= a.`finish_date` OR a.`finish_date` = 0)
									)";
                        $result = $db->GetRow($query);
                        if ($result) {
                            return true;
                        }
                        break;
                    case "cgroup_id":
                        $query = "    SELECT *
                                    FROM `course_group_audience` cga
                                    JOIN `curriculum_periods` cp
                                    ON cga.`cperiod_id` = cp.`cperiod_id`
                                    WHERE cga.`cgroup_id` = ".$db->qstr($result["audience_value"]) . "
                                    AND cga.`proxy_id` = " . $db->qstr($user_id) . "
                                    AND    cga.`active` = 1
                                    AND UNIX_TIMESTAMP() BETWEEN cp.`start_date` AND cp.`finish_date`";
                        $result = $db->GetRow($query);
                        if ($result) {
                            return true;
                        }
                        break;
                    case "course_id":
                        //hand off to course enrollment checking.
                        return false;
                    default:
                        break;
                }
            }
        }
        $query = "    SELECT *
                    FROM `course_contacts` AS cc
                    WHERE cc.`proxy_id` = " . $db->qstr($user_id) . "
                    AND cc.`course_id` = " . $db->qstr($course_id);

        $result = $db->GetRow($query);

        if ($result) {
            return true;
        }

        $query = "    SELECT *
                    FROM `event_contacts` AS ec
                    JOIN `events` as e
                    ON e.`event_id` = ec.`event_id`
                    WHERE ec.`proxy_id` = " . $db->qstr($user_id) . "
                    AND ec.`event_id` = " . $db->qstr($event_id) . "
                    AND e.`course_id` = " . $db->qstr($course_id);

        $result = $db->GetRow($query);

        if ($result) {
            return true;
        }

        return false;
    }
}

class IsEvaluatedAssertion implements Zend_Acl_Assert_Interface {

/**
 * Asserts that the role references the director, coordinator, or secondary director of the course resource
 *
 * @param Zend_Acl $acl The ACL object isself (the one calling the assertion)
 * @param Zend_Acl_Role_Interface $role The role being queried
 * @param Zend_Acl_Resource_Interface $resource The resource being queried
 * @param string $privilege The privilege being queried
 * @return boolean
 */
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		global $db;

		//If asserting is off then return true right away
		if ((isset($resource->assert) && $resource->assert == false) || (isset($acl->_entrada_last_query) && isset($acl->_entrada_last_query->assert) && $acl->_entrada_last_query->assert == false)) {
			return true;
		}
		$role_id = $role->getRoleId();
		$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

		$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
					WHERE `id` = ".$db->qstr($access_id);
		$user_id = $db->GetOne($query);

		if (!isset($user_id) || !$user_id) {
			$role_id = $acl->_entrada_last_query_role->getRoleId();
			$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

			$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
						WHERE `id` = ".$db->qstr($access_id);
			$user_id = $db->GetOne($query);
		}

		$query = "SELECT * FROM `".CLERKSHIP_DATABASE."`.`eval_completed` WHERE `instructor_id` = ".$db->qstr($user_id);
		$evaluated = $db->GetRow($query);

		if ($evaluated) {
			return 	true;
		} else {
			return false;
		}
	}
}

/**
 * Gradebook Owner Assertion
 *
 * Used to assert that the course referenced by the course resource is owned by the user referenced by the user role.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class GradebookOwnerAssertion extends CourseOwnerAssertion {

/**
 * Asserts that the role references the director, coordinator, or secondary director of the course resource
 *
 * @param Zend_Acl $acl The ACL object isself (the one calling the assertion)
 * @param Zend_Acl_Role_Interface $role The role being queried
 * @param Zend_Acl_Resource_Interface $resource The resource being queried
 * @param string $privilege The privilege being queried
 * @return boolean
 */
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		global $db;
		//If asserting is off then return true right away
		if ((isset($resource->assert) && $resource->assert == false) || (isset($acl->_entrada_last_query) && isset($acl->_entrada_last_query->assert) && $acl->_entrada_last_query->assert == false)) {
			return true;
		}

		if (isset($resource->course_id)) {
			$course_id = $resource->course_id;
		} else if (isset($acl->_entrada_last_query->course_id)) {
			$course_id = $acl->_entrada_last_query->course_id;
		} else {
			//Parse out the user ID and course ID
			$resource_id = $resource->getResourceId();
			$resource_type = preg_replace('/[0-9]+/', "", $resource_id);

			if ($resource_type !== "gradebook" && $resource_type !== "assessment") {
				//This only asserts for users on gradebooks.
				return false;
			}

			$course_id = preg_replace('/[^0-9]+/', "", $resource_id);
		}

		$role_id = $role->getRoleId();
		$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

		$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
					WHERE `id` = ".$db->qstr($access_id);
		$user_id = $db->GetOne($query);

		if (!isset($user_id) || !$user_id) {
			$role_id = $acl->_entrada_last_query_role->getRoleId();
			$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

			$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
						WHERE `id` = ".$db->qstr($access_id);
			$user_id = $db->GetOne($query);
		}
		// Inherited from course owner assertion
		return $this->_checkCourseOwner($user_id, $course_id);
	}
}

/**
 * GradebookDropbox Assertion
 *
 * Assert true if access should be granted to a gradebook for a user that is a dropbox contact for an assignment.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>, Don Zuiker <zuikerd@qmed.ca>
 * @copyright Copyright 2010, 2013 Queen's University. All Rights Reserved.
 */
class GradebookDropboxAssertion implements Zend_Acl_Assert_Interface {
/**
 *
 * @param Zend_Acl $acl The ACL object isself (the one calling the assertion)
 * @param Zend_Acl_Role_Interface $role The role being queried
 * @param Zend_Acl_Resource_Interface $resource The resource being queried
 * @param string $privilege The privilege being queried
 * @return boolean
 */
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		global $db;

		//If asserting is off then return true right away
		if ((isset($resource->assert) && $resource->assert == false) || (isset($acl->_entrada_last_query) && isset($acl->_entrada_last_query->assert) && $acl->_entrada_last_query->assert == false)) {
			return true;
		}

		if (isset($resource->course_id)) {
			$course_id = $resource->course_id;
		} else if (isset($acl->_entrada_last_query->course_id)) {
			$course_id = $acl->_entrada_last_query->course_id;
		} else {
			//Parse out the user ID and course ID
			$resource_id = $resource->getResourceId();
			$resource_type = preg_replace('/[0-9]+/', "", $resource_id);

			if ($resource_type !== "gradebook" && $resource_type !== "assessment") {
				//This only asserts for users on gradebooks.
				return false;
			}

			$course_id = preg_replace('/[^0-9]+/', "", $resource_id);
		}

		$role_id = $role->getRoleId();
		$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

		$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
					WHERE `id` = ".$db->qstr($access_id);
		$user_id = $db->GetOne($query);

		if (!isset($user_id) || !$user_id) {
			$role_id = $acl->_entrada_last_query_role->getRoleId();
			$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

			$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
						WHERE `id` = ".$db->qstr($access_id);
			$user_id = $db->GetOne($query);
		}
		return $this->_checkGradebookDropbox($user_id, $course_id);
	}

	static function _checkGradebookDropbox($user_id, $course_id) {
		global $db;

		$query		= "	SELECT *
						FROM `assignment_contacts` a
						JOIN `assignments` b
						ON a.`assignment_id` = b.`assignment_id`
						WHERE a.`proxy_id` = " . $db->qstr($user_id) . "
						AND b.`assignment_active` = 1
						AND b.`course_id` = " . $db->qstr($course_id);
		$results	= $db->GetAll($query);

		if ($results) {
			return true;
		} else {
			return false;
		}
	}
}

/**
 * AssignmentContact Assertion
 *
 * Assert true if access should be granted to an assignment for a user that is listed as an Assignment Contact.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>, Don Zuiker <zuikerd@qmed.ca>
 * @copyright Copyright 2010, 2013 Queen's University. All Rights Reserved.
 */
class AssignmentContactAssertion implements Zend_Acl_Assert_Interface {
/**
 *
 * @param Zend_Acl $acl The ACL object isself (the one calling the assertion)
 * @param Zend_Acl_Role_Interface $role The role being queried
 * @param Zend_Acl_Resource_Interface $resource The resource being queried
 * @param string $privilege The privilege being queried
 * @return boolean
 */
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		global $db;

		//If asserting is off then return true right away
		if ((isset($resource->assert) && $resource->assert == false) || (isset($acl->_entrada_last_query) && isset($acl->_entrada_last_query->assert) && $acl->_entrada_last_query->assert == false)) {
			return true;
		}

		if (isset($resource->assignment_id)) {
			$assignment_id = $resource->assignment_id;
		} else if (isset($acl->_entrada_last_query->assignment_id)) {
			$assignment_id = $acl->_entrada_last_query->assignment_id;
		} else {
			//Parse out the user ID and course ID
			$resource_id = $resource->getResourceId();
			$resource_type = preg_replace('/[0-9]+/', "", $resource_id);

			if ($resource_type !== "assignment") {
				//This only asserts for users on gradebooks.
				return false;
			}

			$assignment_id = preg_replace('/[^0-9]+/', "", $resource_id);
		}

		$role_id = $role->getRoleId();
		$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

		$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
					WHERE `id` = ".$db->qstr($access_id);
		$user_id = $db->GetOne($query);

		if (!isset($user_id) || !$user_id) {
			$role_id = $acl->_entrada_last_query_role->getRoleId();
			$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

			$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
						WHERE `id` = ".$db->qstr($access_id);
			$user_id = $db->GetOne($query);
		}
		return $this->_checkAssignmentContacts($user_id, $assignment_id);
	}

	static function _checkAssignmentContacts($user_id, $assignment_id) {
		global $db;

		$query		= "	SELECT *
						FROM `assignment_contacts` a
						JOIN `assignments` b
						ON a.`assignment_id` = b.`assignment_id`
						WHERE a.`proxy_id` = " . $db->qstr($user_id) . "
						AND b.`assignment_active` = 1
						AND a.`assignment_id` = " . $db->qstr($assignment_id);
		$results	= $db->GetAll($query);
		if ($results) {
			return true;
		} else {
			return false;
		}
	}
}

/**
 * AssessmentContact Assertion
 *
 * Assert true if access should be granted to an assessment for a user that is an assignment contact of an assignment within the assessment.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>, Don Zuiker <zuikerd@qmed.ca>
 * @copyright Copyright 2010, 2013 Queen's University. All Rights Reserved.
 */
class AssessmentContactAssertion implements Zend_Acl_Assert_Interface {
/**
 *
 * @param Zend_Acl $acl The ACL object isself (the one calling the assertion)
 * @param Zend_Acl_Role_Interface $role The role being queried
 * @param Zend_Acl_Resource_Interface $resource The resource being queried
 * @param string $privilege The privilege being queried
 * @return boolean
 */
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		global $db;

		//If asserting is off then return true right away
		if ((isset($resource->assert) && $resource->assert == false) || (isset($acl->_entrada_last_query) && isset($acl->_entrada_last_query->assert) && $acl->_entrada_last_query->assert == false)) {
			return true;
		}

		if (isset($resource->assessment_id)) {
			$assessment_id = $resource->assessment_id;
		} else if (isset($acl->_entrada_last_query->assessment_id)) {
			$assessment_id = $acl->_entrada_last_query->assessment_id;
		} else {
			//Parse out the user ID and course ID
			$resource_id = $resource->getResourceId();
			$resource_type = preg_replace('/[0-9]+/', "", $resource_id);

			if ($resource_type !== "assessment") {
				//This only asserts for users on gradebooks.
				return false;
			}

			$assessment_id = preg_replace('/[^0-9]+/', "", $resource_id);
		}
		$role_id = $role->getRoleId();
		$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

		$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
					WHERE `id` = ".$db->qstr($access_id);
		$user_id = $db->GetOne($query);

		if (!isset($user_id) || !$user_id) {
			$role_id = $acl->_entrada_last_query_role->getRoleId();
			$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

			$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
						WHERE `id` = ".$db->qstr($access_id);
			$user_id = $db->GetOne($query);
		}
		return $this->_checkAssessmentContacts($user_id, $assessment_id);
	}

	static function _checkAssessmentContacts($user_id, $assessment_id) {
		global $db;

		$query		= "	SELECT *
						FROM `assignment_contacts` a
						JOIN `assignments` b
						ON a.`assignment_id` = b.`assignment_id`
						JOIN `assessments` c
						ON c.`assessment_id` = b.`assessment_id`
						WHERE a.`proxy_id` = " . $db->qstr($user_id) . "
						AND b.`assignment_active` = 1
						AND c.`assessment_id` = " . $db->qstr($assessment_id);
		$results	= $db->GetAll($query);
		if ($results) {
			return true;
		} else {
			return false;
		}
	}
}

/**
 * Event Owner Assertion
 *
 * Used to assert that the event referenced by the course resource is owned by the user referenced by the user role.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class EventOwnerAssertion implements Zend_Acl_Assert_Interface {
/**
 * Asserts that the role references the director, coordinator, or secondary director of the course resource
 *
 * @param Zend_Acl $acl The ACL object isself (the one calling the assertion)
 * @param Zend_Acl_Role_Interface $role The role being queried
 * @param Zend_Acl_Resource_Interface $resource The resource being queried
 * @param string $privilege The privilege being queried
 * @return boolean
 */
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		global $db;
		if ((isset($resource->assert) && $resource->assert == false) || (isset($acl->_entrada_last_query) && isset($acl->_entrada_last_query->assert) && $acl->_entrada_last_query->assert == false)) {
			return true;
		}

		if (isset($resource->event_id)) {
			$event_id = $resource->event_id;
		} else if (isset($acl->_entrada_last_query->event_id)) {
			$event_id = $acl->_entrada_last_query->event_id;
		} else {
			return false;

			$resource_id = $resource->getResourceId();
			$resource_type = preg_replace('/[0-9]+/', "", $resource_id);

			if ($resource_type !== "event" && $resource_type !== "eventcontent") {
			//This only asserts for events.
				return false;
			}

			$event_id = preg_replace('/[^0-9]+/', "", $resource_id);
		}

		$role_id = $role->getRoleId();
		$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

		$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
					WHERE `id` = ".$db->qstr($access_id);
		$user_id = $db->GetOne($query);

		if (!isset($user_id) || !$user_id) {
			$role_id = $acl->_entrada_last_query_role->getRoleId();
			$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

			$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
						WHERE `id` = ".$db->qstr($access_id);
			$user_id = $db->GetOne($query);
		}

		return $this->_checkEventOwner($user_id, $event_id);
	}

	/**
	 * Checks if the $user_id is either a lecturer teaching the event, or a director or program coordinator of the course the event belongs to.
	 *
	 * @param string|integer $user_id The proxy id to be checked
	 * @param string|integer $event_id The event id to be checked
	 * @return boolean
	 */
	static function _checkEventOwner($user_id, $event_id) {
		global $db;

		$query		= "	SELECT a.`event_id`, b.`proxy_id` AS `teacher`, c.`pcoord_id` AS `coordinator`, d.`proxy_id` AS `director_id`, e.`proxy_id` AS `pcoordinator`
						FROM `events` AS a
						LEFT JOIN `event_contacts` AS b
						ON b.`event_id` = a.`event_id`
						LEFT JOIN `courses` AS c
						ON c.`course_id` = a.`course_id`
						LEFT JOIN `course_contacts` AS d
						ON d.`course_id` = c.`course_id`
						AND d.`contact_type` = 'director'
						LEFT JOIN `course_contacts` AS e
						ON e.`course_id` = c.`course_id`
						AND e.`contact_type` = 'pcoordinator'
						WHERE a.`event_id` = ".$db->qstr($event_id)."
						AND c.`course_active` = '1'";
		$results	= $db->GetAll($query);
		if ($results) {
			foreach ($results as $result) {
				foreach (array("director_id", "coordinator", "teacher", "pcoordinator") as $owner) {
					if ($result[$owner] == $user_id) {
						return true;
					}
				}
			}
		}

		return false;
	}
}

/**
 * Is Student Assertion
 *
 * Used to assert that the user referenced is a student
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class IsStudentAssertion implements Zend_Acl_Assert_Interface {
/**
 * Asserts that the user group is student
 *
 * @param Zend_Acl $acl The ACL object isself (the one calling the assertion)
 * @param Zend_Acl_Role_Interface $role The role being queried
 * @param Zend_Acl_Resource_Interface $resource The resource being queried
 * @param string $privilege The privilege being queried
 * @return boolean
 */
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {

		if ((isset($resource->assert) && $resource->assert == false) || (isset($acl->_entrada_last_query) && isset($acl->_entrada_last_query->assert) && $acl->_entrada_last_query->assert == false)) {
			return true;
		}

		echo "Assertion required<br />";

		return ($acl && $acl->_entrada_last_query_role && $acl->_entrada_last_query_role->details && $acl->_entrada_last_query_role->details->group == "student");
	}
}

/**
 * Used to assert that the organisation this resource belongs to has the requested privlege for the asking role. Used to make blanket access rules for organisations's resources.
 * Extra: will also operate on courses and events who's organisation ID property has not been set.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class ResourceOrganisationAssertion implements Zend_Acl_Assert_Interface {
/**
 *
 * Asserts that the role has the requested privilege on the resource's organisation
 *
 * @param Zend_Acl $acl The ACL object isself (the one calling the assertion)
 * @param Zend_Acl_Role_Interface $role The role being queried
 * @param Zend_Acl_Resource_Interface $resource The resource being queried
 * @param string $privilege The privilege being queried
 * @return boolean
 */
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		//Return true right away if asserting is off.

		if (((isset($resource->assert) && $resource->assert == false) || (isset($acl->_entrada_last_query) && isset($acl->_entrada_last_query->assert) && $acl->_entrada_last_query->assert == false)) || (isset($acl->_entrada_last_query) && isset($acl->_entrada_last_query->assert) && $acl->_entrada_last_query->assert == false)) {
			return true;
		}

		//If the organisation_id has been supplied then go right ahead and check to see if this organisation has this privledge
		if (isset($resource->organisation_id) && $acl->has("resourceorganisation".$resource->organisation_id)) {
			return $acl->isAllowed($role, "resourceorganisation".$resource->organisation_id, $privilege);
		} else {
			//Otherwise, look at the object that the query was first made upon, which will have some information about it which hopefully can be used to figure out the organisation_id
			if (isset($acl->_entrada_last_query)) {
			//Use the organisation ID if provided
				if (isset($acl->_entrada_last_query->organisation_id)) {
					$organisation_id = $acl->_entrada_last_query->organisation_id;
				} else {
					global $db;
					//Use the course ID if nessecary
					if (isset($acl->_entrada_last_query->course_id) && ($acl->_entrada_last_query->course_id != 0)) {
						$query = "	SELECT `organisation_id` FROM `courses`
									WHERE `course_id` = ".$db->qstr($acl->_entrada_last_query->course_id)."
									AND `course_active` = '1'";
						$result = $db->GetRow($query);
						if ($result) {
							$organisation_id = $result["organisation_id"];
						}
					} elseif (isset($acl->_entrada_last_query->event_id) && ($acl->_entrada_last_query->event_id != 0)) {
						//Use the event ID if nessecary
						$query = "	SELECT a.`course_id`, b.`organisation_id` AS course_organisation_id, d.`audience_value` AS event_organisation_id
									FROM `events` AS a
									LEFT JOIN `courses` AS b
									ON b.`course_id` = a.`course_id`
									LEFT JOIN `event_audience` AS d
									ON d.`event_id` = ".$db->qstr($acl->_entrada_last_query->event_id)."
									AND d.`audience_type` = 'organisation_id'
									WHERE b.`course_active` = '1'
									ORDER BY b.`organisation_id`";
						$result = $db->GetRow($query);
						if ($result) {
							if (isset($result["course_organisation_id"])) {
								$organisation_id = $result["course_organisation_id"];
							} elseif (isset($result["event_organisation_id"])) {
								$organisation_id = $result["event_organisation_id"];
							}
						}
					}
				}

				if (isset($organisation_id) && $acl->has("resourceorganisation".$organisation_id)) {
					//Return this role's ability to preform this privilege on this organisation.
					return $acl->isAllowed($role, "resourceorganisation".$organisation_id, $privilege);
				}
			}
		}

		return false;
	}
}

/**
 * Community Assertion Class
 *
 * Asserts that a role is of a particular type for the community resource being queried.
 */
abstract class CommunityAssertion implements Zend_Acl_Assert_Interface {
	var $check_method;
	/**
	 *
	 *
	 * Asserts that the role has the requested privilege on the community
	 *
	 * @param Zend_Acl $acl The ACL object isself (the one calling the assertion)
	 * @param Zend_Acl_Role_Interface $role The role being queried
	 * @param Zend_Acl_Resource_Interface $resource The resource being queried
	 * @param string $privilege The privilege being queried
	 * @return boolean
	 */
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		global $db;
	//Return true right away if asserting is off.
		if ((isset($resource->assert) && $resource->assert == false) || (isset($acl->_entrada_last_query) && isset($acl->_entrada_last_query->assert) && $acl->_entrada_last_query->assert == false)) {
			return true;
		}

		if (isset($resource->community_id)) {
			$community_id = $resource->community_id;
		} else {
			if (isset($acl->_entrada_last_query->community_id)) {
				$community_id = $acl->_entrada_last_query->community_id;
			}
		}
		if (isset($community_id)) {
			$role_id = $role->getRoleId();
			$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

			$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
						WHERE `id` = ".$db->qstr($access_id);
			$user_id = $db->GetOne($query);

			if (!isset($user_id) || !$user_id) {
				$role_id = $acl->_entrada_last_query_role->getRoleId();
				$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

				$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
							WHERE `id` = ".$db->qstr($access_id);
				$user_id = $db->GetOne($query);
			}

			return $this->_checkCommunity($user_id, $community_id);
		}
		return false;
	}

	static abstract function _checkCommunity($user_id, $community_id);
}

/**
 * Community Owner Assertion Class
 *
 * Asserts that a role is an administrator for the community resource being queried.
 */
class CommunityOwnerAssertion extends CommunityAssertion {

	var $check_method = "_checkCommunityOwner";

	/**
	 *	Checks that a user can administer a community
	 *
	 * @param integer $user_id The user's proxy ID
	 * @param integer $community_id The community's ID
	 * @return boolean
	 */
	static function _checkCommunity ($user_id, $community_id) {
		global $db;
		$query	= "
				SELECT `proxy_id` FROM `community_members`
				WHERE `community_id` = ".$db->qstr($community_id)."
				AND `proxy_id` = ".$db->qstr($user_id)."
				AND `member_active` = '1'
				AND `member_acl` = '1'";
		$result	= $db->GetRow($query);
		if ($result) {
		//Query had a row
			return true;
		}
		return false;
	}
}

/**
 * Community Member Assertion Class
 *
 * Asserts that a role is an administrator for the community resource being queried.
 */
class CommunityMemberAssertion extends CommunityAssertion {
	var $check_method = "_checkCommunityMember";

	/**
	 *	Checks that a user can administer a community
	 *
	 * @param integer $user_id The user's proxy ID
	 * @param integer $community_id The community's ID
	 * @return boolean
	 */
	static function _checkCommunity($user_id, $community_id) {
		global $db, $ENTRADA_USER;
		$query	= "
				SELECT `proxy_id` FROM `community_members`
				WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)."
				AND `proxy_id` = ".$db->qstr($ENTRADA_USER->getActiveId());
		$result	= $db->GetRow($query);
		if ($result) {
		//Query had a row
			return true;
		}
		return false;
	}
}

/**
 * Not Guest assertion class
 *
 * Asserts that a role is not a guest
 */
class NotGuestAssertion implements Zend_Acl_Assert_Interface {
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		global $db;
		$role = $acl->_entrada_last_query_role;
		if (isset($role->details) && isset($role->details["group"])) {
			$GROUP = $role->details["group"];
		} else {
/**
 * @todo This needs to be fixed, or perhaps this would never even happen? The user_data table doesn't contain group or role fields, that's in user_access.
 */
			$role_id = $role->getRoleId();
			$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

			$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
						WHERE `id` = ".$db->qstr($access_id);
			$user_id = $db->GetOne($query);
			$query = "SELECT `group`, `role` FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($user_id);
			$result = $db->GetRow($query);
			if ($result) {
				$GROUP = $result["group"];
			} else {
			//Return false cause this person could be a guest.
				return false;
			}

		}
		if ($GROUP == "guest") {
			return false;
		}	 else {
			return true;
		}

	}

}

/**
 * Not Student assertion class
 *
 * Asserts that a role is not a student
 */
class NotStudentAssertion implements Zend_Acl_Assert_Interface {
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		global $db;
		$role = $acl->_entrada_last_query_role;
		if (isset($role->details) && isset($role->details["group"])) {
			$GROUP = $role->details["group"];
		} else {
/**
 * @todo This needs to be fixed, or perhaps this would never even happen? The user_data table doesn't contain group or role fields, that's in user_access.
 */
			$role_id = $role->getRoleId();
			$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

			$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
						WHERE `id` = ".$db->qstr($access_id);
			$user_id = $db->GetOne($query);
			$query = "SELECT `group`, `role` FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($user_id);
			$result = $db->GetRow($query);
			if ($result) {
				$GROUP = $result["group"];
			} else {
			//Return false cause this person could be a guest.
				return false;
			}

		}
		if ($GROUP == "student") {
			return false;
		}	 else {
			return true;
		}

	}

}

/**
 * Clerkship Assertion Class
 *
 * Asserts that a role's graduating year makes it eligble for clerkship
 */
class ClerkshipAssertion implements Zend_Acl_Assert_Interface {
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {

		if (!($role instanceof EntradaUser) || !isset($role->details) || !isset($role->details["grad_year"])) {
			if (isset($acl->_entrada_last_query_role)) {
				$role = $acl->_entrada_last_query_role;
				if (($role instanceof EntradaUser) || isset($role->details) || isset($role->details["grad_year"])) {
					$GRAD_YEAR = preg_replace("/[^0-9]+/i", "", $role->details["grad_year"]);
				}
			}
		} else {
			$GRAD_YEAR = preg_replace("/[^0-9]+/i", "", $role->details["grad_year"]);
		}

		if (!isset($GRAD_YEAR)) {
			return false;
		}

		if ((time() < $end_timestamp = mktime(0, 0, 0, 7, 13, intval($GRAD_YEAR))) && (time() >= strtotime("-26 months", $end_timestamp))) {
			return true;
		} else {
			return false;
		}
	}
}

/**
 * Clerkship Lottery Assertion Class
 *
 * Asserts that a role's graduating year makes it eligble for the clerkship lottery
 */
class ClerkshipLotteryAssertion implements Zend_Acl_Assert_Interface {
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {

		if (!($role instanceof EntradaUser) || !isset($role->details) || !isset($role->details["grad_year"])) {
			if (isset($acl->_entrada_last_query_role)) {
				$role = $acl->_entrada_last_query_role;
				if (($role instanceof EntradaUser) || isset($role->details) || isset($role->details["grad_year"])) {
					$GRAD_YEAR = $role->details["grad_year"];
				}
			}
		} else {
			$GRAD_YEAR = $role->details["grad_year"];
		}

		if (!isset($GRAD_YEAR)) {
			return false;
		}

		if ((date("Y",strtotime("+2 Years")) == $GRAD_YEAR) && ((time() >= CLERKSHIP_LOTTERY_START && time() <= CLERKSHIP_LOTTERY_FINISH) || time() >= CLERKSHIP_LOTTERY_RELEASE)) {
			return true;
		} else {
			return false;
		}
	}
}

/**
 * Clerkship Director Assertion Class
 *
 * Checks to see if the faculty:director's proxy_id is in the $AGENT_CONTACTS["agent-clerkship"]["director_ids"]
 * which therefore gives them access to the Manage Clerkship tab.
 */
class ClerkshipDirectorAssertion implements Zend_Acl_Assert_Interface {
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		global $AGENT_CONTACTS;

		if (!($role instanceof EntradaUser) || !isset($role->details) || !isset($role->details["id"])) {
			if (isset($acl->_entrada_last_query_role)) {
				$role = $acl->_entrada_last_query_role;
				if (($role instanceof EntradaUser) || isset($role->details) || isset($role->details["id"])) {
					$proxy_id = $role->details["id"];
				}
			}
		} else {
			$proxy_id = $role->details["id"];
		}

		if ((isset($proxy_id)) && ((int) $proxy_id)) {
			if ((isset($AGENT_CONTACTS)) && (is_array($AGENT_CONTACTS)) && (isset($AGENT_CONTACTS["agent-clerkship"]["director_ids"]))) {
				$director_ids = array();

				foreach ((array) $AGENT_CONTACTS["agent-clerkship"]["director_ids"] as $director_id) {
					if ((int) $director_id) {
						$director_ids[] = $director_id;
					}
				}

				if (count($director_ids)) {
					if (in_array($proxy_id, $director_ids)) {
						return true;
					}
				}
			}
		}

		return false;
	}
}

/**
 * Regional Education Has Accommodations Class
 *
 * Checks to see if the resident has regional accommodations assigned to them
 * by the regional education office.
 */
class HasAccommodationsAssertion implements Zend_Acl_Assert_Interface {
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		global $db;

		if (!($role instanceof EntradaUser) || !isset($role->details) || !isset($role->details["id"])) {
			if (isset($acl->_entrada_last_query_role)) {
				$role = $acl->_entrada_last_query_role;
				if (($role instanceof EntradaUser) || isset($role->details) || isset($role->details["id"])) {
					$proxy_id = $role->details["id"];
				}
			}
		} else {
			$proxy_id = $role->details["id"];
		}

		if ((isset($proxy_id)) && ((int) $proxy_id)) {
			$query = "SELECT COUNT(*) AS `total` FROM `".CLERKSHIP_DATABASE."`.`apartment_schedule` WHERE `proxy_id` = ".$db->qstr($proxy_id);
			$result = $db->GetRow($query);

			if ($result && ($result["total"] > 0)) {
				return true;
			}
		}

		return false;
	}
}

class QuizOwnerAssertion implements Zend_Acl_Assert_Interface {
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		global $db;

		//If asserting is off then return true right away
		if ((isset($resource->assert) && $resource->assert == false) || (isset($acl->_entrada_last_query) && isset($acl->_entrada_last_query->assert) && $acl->_entrada_last_query->assert == false)) {
			return true;
		}

		if (isset($resource->quiz_id)) {
			$quiz_id = $resource->quiz_id;
		} else if (isset($acl->_entrada_last_query->quiz_id)) {
			$quiz_id = $acl->_entrada_last_query->quiz_id;
		} else {
			//Parse out the user ID and course ID
			$resource_id = $resource->getResourceId();
			$resource_type = preg_replace('/[0-9]+/', "", $resource_id);

			if ($resource_type !== "quiz" || $resource_type !== "quizquestion" || $resource_type !== "quizresult") {
			//This only asserts for users on quizzes.
				return false;
			}

			$quiz_id = preg_replace('/[^0-9]+/', "", $resource_id);
		}

		$role_id = $role->getRoleId();
		$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

		$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
					WHERE `id` = ".$db->qstr($access_id);
		$user_id = $db->GetOne($query);

		if (!isset($user_id) || !$user_id) {
			$role_id = $acl->_entrada_last_query_role->getRoleId();
			$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

			$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
						WHERE `id` = ".$db->qstr($access_id);
			$user_id = $db->GetOne($query);
		}

		return $this->_checkQuizOwner($user_id, $quiz_id);
	}

	static function _checkQuizOwner($user_id, $quiz_id) {
		global $db;

		$query		= "	SELECT a.`proxy_id`
						FROM `quiz_contacts` AS a
						WHERE a.`quiz_id` = ".$db->qstr($quiz_id);
		$results	= $db->GetAll($query);
		if ($results) {
			foreach ($results as $result) {
				if ($result["proxy_id"] == $user_id) {
					return true;
				}
			}
		}

		return false;
	}
}



/**
 * Base class for smart Entrada resource objects. Used for dummy checks and non assertion checks.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class EntradaAclResource implements Zend_Acl_Resource_Interface {
/**
 * Wheather or not rules concering this resource need assert. True if so.
 * @var boolean
 */
	var $assert = true;

	/**
	 * Wheather or not this resource references as specific instance of it's resource type or the resource type. Used to drop down to blanket rules once is assured no rules concerning this instance have been defined
	 * @var boolean
	 */
	var $specific = true;

	/**
	 * The unique resource identifier of this object
	 * @var string
	 */
	var $resource_id = "";

	/**
	 * Creates a new untyped resource for easy checks. Should be overridden.
	 * @param string $id The resource ID to be returned by this when checked
	 * @param boolean $assert If assertions should be preformed or not.
	 */
	function __construct($id, $assert = true) {
		$this->resource_id = $id;
		$this->assert = $assert;
	}

	/**
	 * ACL method for keeping track. Required by Zend_Acl_Resource_Interface
	 * @return string
	 */
	public function getResourceId() {
		if ($this->specific) {
			return $this->resource_id;
		} else {
			return preg_replace('/[0-9]+/', "", $this->resource_id);
		}
	}
}

class UserResource extends EntradaAclResource {
/**
 * This user's organisation ID, used for ResourceOrganisationAssertion.
 * @see ResourceOrganisationAssertion()
 * @var integer
 */
	var $organisation_id;

	/**
	 * This user's proxy id.
	 * @var integer
	 */
	var $user_id;

	/**
	 * Constructs this user resource with the supplied values
	 * @param integer $user_id The proxy ID to represent
	 * @param integer $organisation_id The organisation ID this user belongs to
	 * @param boolean $assert Wheather or not to make an assertion
	 */
	function __construct($user_id, $organisation_id, $assert = null) {
		$this->user_id = $user_id;
		$this->organisation_id = $organisation_id;
		if (isset($assert)) {
			$this->assert = $assert;
		}
	}

	/**
	 * ACL method for keeping track. Required by Zend_Acl_Resource_Interface.
	 * Will return based on specifc property of this resource instance.
	 * @return string
	 */
	public function getResourceId() {
		return "user".($this->specific ? $this->user_id : "");
	}
}

/**
 * Smart course resource object for the EntradaACL.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class CourseResource extends EntradaAclResource {
/**
 * The course ID for this resource
 * @var integer
 */
	var $course_id;

	/**
	 * This course's organisation ID, used for ResourceOrganisationAssertion.
	 * @see ResourceOrganisationAssertion()
	 * @var integer
	 */
	var $organisation_id;

	/**
	 * Constructs this course resource with the supplied values
	 * @param integer $course_id The course ID to represent
	 * @param integer $organisation_id The organisation ID this course belongs to
	 * @param boolean $assert Wheather or not to make an assertion
	 */
	function __construct($course_id, $organisation_id, $assert = null) {
		$this->course_id = $course_id;
		$this->organisation_id = $organisation_id;
		if (isset($assert)) {
			$this->assert = $assert;
		}
	}

	/**
	 * ACL method for keeping track. Required by Zend_Acl_Resource_Interface.
	 * Will return based on specifc property of this resource instance.
	 * @return string
	 */
	public function getResourceId() {
		return "course".($this->specific ? $this->course_id : "");
	}
}
/**
 * Smart gradebook resource object for the EntradaACL.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class GradebookResource extends CourseResource {
	/**
	 * ACL method for keeping track. Required by Zend_Acl_Resource_Interface.
	 * Will return based on specifc property of this resource instance.
	 * @return string
	 */
	public function getResourceId() {
		return "gradebook".($this->specific ? $this->course_id : "");
	}
}

class AssessmentResource extends EntradaAclResource {
	var $assessment_id;

	function __construct($assessment_id, $assert = null) {
		$this->assessment_id = $assessment_id;

		if (isset($assert)) {
			$this->assert = $assert;
		}
	}

	public function getResourceId() {
		return "assessment".($this->specific ? $this->assessment_id : "");
	}
}

class AssignmentResource extends EntradaAclResource {
	var $assignment_id;

	function __construct($assignment_id, $assert = null) {
		$this->assignment_id = $assignment_id;

		if (isset($assert)) {
			$this->assert = $assert;
		}
	}

	public function getResourceId() {
		return "assignment".($this->specific ? $this->assignment_id : "");
	}
}

/**
 * Creates a photo resource.
 */
class PhotoResource extends EntradaAclResource {
	var $proxy_id;

	var $privacy_level;

	var $photo_type;

	function __construct($proxy_id, $privacy_level, $photo_type, $assert = null) {
		$this->proxy_id			= $proxy_id;
		$this->privacy_level	= $privacy_level;
		$this->photo_type		= $photo_type;

		if (isset($assert)) {
			$this->assert = $assert;
		}
	}

	public function getResourceId() {
		return "photo".($this->specific ? $this->proxy_id : "");
	}
}

class PhotoAssertion implements Zend_Acl_Assert_Interface {
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		if (!($resource instanceof PhotoResource)) {
			return false;
		}
		if (!isset($resource->proxy_id) && !isset($resource->privacy_level) && !isset($resource->photo_type)) {
			return false;
		}

		$role = $acl->_entrada_last_query_role;
		if (!isset($role->details["id"])) {
			return false;
		}
		if (($resource->proxy_id == $role->details["id"]) || ((($resource->photo_type == "official") && ((int) $resource->privacy_level >= 2)) || (($resource->photo_type == "upload") && ((int) $resource->privacy_level >= 2)))){
			return true;
		}

		return false;
	}
}

/**
 * Smart course resource object for the EntradaACL.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class NoticeResource extends EntradaAclResource {
	/**
	 * This notices targe organisation's ID, used for ResourceOrganisationAssertion.
	 * @see ResourceOrganisationAssertion()
	 * @var integer
	 */
	var $organisation_id;

	/**
	 * Constructs this course resource with the supplied values
	 * @param integer $course_id The course ID to represent
	 * @param integer $organisation_id The organisation ID this course belongs to
	 * @param boolean $assert Wheather or not to make an assertion
	 */
	function __construct($organisation_id, $assert = null) {
		$this->organisation_id = $organisation_id;
		if (isset($assert)) {
			$this->assert = $assert;
		}
	}

	/**
	 * ACL method for keeping track. Required by Zend_Acl_Resource_Interface.
	 * Will return based on specifc property of this resource instance.
	 * @return string
	 */
	public function getResourceId() {
		return "notice";
	}
}

/**
 * Configuration Resource
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class ConfigurationResource extends EntradaAclResource {
	var $organisation_id;

	function __construct($organisation_id, $assert = null) {
		$this->organisation_id = $organisation_id;
		if (isset($assert)) {
			$this->assert = $assert;
		}
	}

	public function getResourceId() {
		return "configuration";
	}
}

/**
 * Smart event resource object for the EntradaACL.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class EventResource extends EntradaAclResource {
	/**
	 * The event ID this resource represents
	 * @var integer
	 */
	var $event_id;

	/**
	 * The course ID for the course this event belongs to
	 * @var integer
	 */
	var $course_id;

	/**
	 * This event's parent course's organisation ID, used for ResourceOrganisationAssertion.
	 * @see ResourceOrganisationAssertion()
	 * @var integer
	 */
	var $organisation_id;

	/**
	 * Creates this event resource with the supplied information
	 * @param integer $event_id This event's ID
	 * @param integer $course_id This event's parent course's ID
	 * @param integer $organisation_id This event's parent course's organisation ID
	 * @param boolean $assert Wheather or not to use assertions when looking at rules
	 */
	function __construct($event_id, $course_id= null, $organisation_id = null, $assert = null) {
		$this->course_id = $course_id;
		$this->event_id = $event_id;
		$this->organisation_id = $organisation_id;
		if (isset($assert)) {
			$this->assert = $assert;
		}
	}

	/**
	 * ACL method for keeping track. Required by Zend_Acl_Resource_Interface.
	 * Will return based on specifc property of this resource instance.
	 * @return string
	 */
	public function getResourceId() {
		return "event".($this->specific ? $this->event_id : "");
	}
}

/**
 * Smart event resource object for the EntradaACL.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2008 Queen's University. All Rights Reserved.
 */
class ObjectiveResource extends EntradaAclResource {
	/**
	 * The objective ID this resource represents
	 * @var integer
	 */
	var $objective_id;

	/**
	 * The id of the top level parent of this objective
	 * @var integer
	 */
	var $objective_type;

	/**
	 * Creates this event resource with the supplied information
	 * @param integer $event_id This event's ID
	 * @param integer $course_id This event's parent course's ID
	 * @param integer $organisation_id This event's parent course's organisation ID
	 * @param boolean $assert Wheather or not to use assertions when looking at rules
	 */
	function __construct($objective_id, $objective_type= null, $assert = null) {
		$this->objective_id = $objective_id;
		$this->objective_type = $objective_type;
		if (isset($assert)) {
			$this->assert = $assert;
		}
	}

	/**
	 * ACL method for keeping track. Required by Zend_Acl_Resource_Interface.
	 * Will return based on specifc property of this resource instance.
	 * @return string
	 */
	public function getResourceId() {
		return "objective".($this->specific ? $this->objective_id : "");
	}
}

/**
 * Smart course content resource object for the EntradaACL.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class CourseContentResource extends CourseResource {
	/**
	 * ACL method for keeping track. Required by Zend_Acl_Resource_Interface.
	 * Will return based on specifc property of this resource instance.
	 * @return string
	 */
	public function getResourceId() {
		return "coursecontent".($this->specific ? $this->course_id : "");
	}
}

/**
 * Smart event content resource object for the EntradaACL.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class EventContentResource extends EventResource {
	/**
	 * ACL method for keeping track. Required by Zend_Acl_Resource_Interface.
	 * Will return based on specifc property of this resource instance.
	 * @return string
	 */
	public function getResourceId() {
		return "eventcontent".($this->specific ? $this->course_id : "");
	}
}

class QuizResource extends EntradaAclResource {
	var $quiz_id;

	function __construct($quiz_id, $assert = null) {
		$this->quiz_id = $quiz_id;
	}


	public function getResourceId() {
		return "quiz".($this->specific ? $this->quiz_id : "");
	}
}

class QuizResultResource extends QuizResource {
	public function getResourceId() {
		return "quizresult".($this->specific ? $this->quiz_id : "");
	}
}

class QuizQuestionResource extends QuizResource {
	var $quiz_question_id;

	function __construct($quiz_question_id, $quiz_id, $assert = null) {
		$this->quiz_question_id = $quiz_question_id;
		$this->quiz_id = $quiz_id;
	}

	public function getResourceId() {
		return "quizquestion".($this->specific ? $this->quiz_id : "");
	}
}

class EvaluationResource extends EntradaAclResource {
	var $evaluation_id;

	function __construct($evaluation_id, $assert = null) {
		$this->evaluation_id = $evaluation_id;
	}

	public function getResourceId() {
		return "evaluation".($this->specific ? $this->evaluation_id : "");
	}
}

class EvaluationReviewerAssertion implements Zend_Acl_Assert_Interface {
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		global $db;

		//If asserting is off then return true right away
		if ((isset($resource->assert) && $resource->assert == false) || (isset($acl->_entrada_last_query) && isset($acl->_entrada_last_query->assert) && $acl->_entrada_last_query->assert == false)) {
			return true;
		}

		if (isset($resource->evaluation_id)) {
			$evaluation_id = $resource->evaluation_id;
		} else if (isset($acl->_entrada_last_query->evaluation_id)) {
			$evaluation_id = $acl->_entrada_last_query->evaluation_id;
		} else {
			//Parse out the user ID and course ID
			$resource_id = $resource->getResourceId();
			$resource_type = preg_replace('/[0-9]+/', "", $resource_id);

			if ($resource_type !== "evaluation") {
			//This only asserts for users reviewing evaluations.
				return false;
			}

			$evaluation_id = preg_replace('/[^0-9]+/', "", $resource_id);
		}

		$role_id = $role->getRoleId();
		$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

		$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
					WHERE `id` = ".$db->qstr($access_id);
		$user_id = $db->GetOne($query);

		if (!isset($user_id) || !$user_id) {
			$role_id = $acl->_entrada_last_query_role->getRoleId();
			$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

			$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
						WHERE `id` = ".$db->qstr($access_id);
			$user_id = $db->GetOne($query);
		}

		$permissions = Models_Evaluation::getReviewPermissions($evaluation_id);
		if (count($permissions)) {
			return true;
		} else {
			return false;
		}
	}
}

class EvaluationFormAuthorAssertion implements Zend_Acl_Assert_Interface {
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		global $db;

		//If asserting is off then return true right away
		if ((isset($resource->assert) && $resource->assert == false) || (isset($acl->_entrada_last_query) && isset($acl->_entrada_last_query->assert) && $acl->_entrada_last_query->assert == false)) {
			return true;
		}

		if (isset($resource->eform_id)) {
			$eform_id = $resource->eform_id;
		} else if (isset($acl->_entrada_last_query->eform_id)) {
			$eform_id = $acl->_entrada_last_query->eform_id;
		} else {
			//Parse out the user ID and course ID
			$resource_id = $resource->getResourceId();
			$resource_type = preg_replace('/[0-9]+/', "", $resource_id);

			if ($resource_type !== "evaluationform") {
			//This only asserts for users authoring evaluation forms.
				return false;
			}

			$eform_id = preg_replace('/[^0-9]+/', "", $resource_id);
		}

		$role_id = $role->getRoleId();
		$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

		$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
					WHERE `id` = ".$db->qstr($access_id);
		$user_id = $db->GetOne($query);

		if (!isset($user_id) || !$user_id) {
			$role_id = $acl->_entrada_last_query_role->getRoleId();
			$access_id	= preg_replace('/[^0-9]+/', "", $role_id);

			$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access`
						WHERE `id` = ".$db->qstr($access_id);
			$user_id = $db->GetOne($query);
		}

		$permissions = Models_Evaluation::getFormAuthorPermissions($eform_id);
		if ($permissions) {
			return true;
		} else {
			return false;
		}
	}
}

class EvaluationResultResource extends EvaluationResource {
	public function getResourceId() {
		return "evaluationresult".($this->specific ? $this->evaluation_id : "");
	}
}

class EvaluationFormResource extends EvaluationResource {
	var $eform_id;

	function __construct($eform_id, $assert = null) {
		$this->eform_id = $eform_id;
	}
	public function getResourceId() {
		return "evaluationform".($this->specific ? $this->evaluation_id : "");
	}
}

class EvaluationQuestionResource extends EvaluationResource {
	var $equestion_id;

	function __construct($equestion_id, $assert = null) {
		$this->equestion_id = $equestion_id;
	}
	public function getResourceId() {
		return "evaluationquestion".($this->specific ? $this->equestion_id : "");
	}
}

class EvaluationFormQuestionResource extends EvaluationResource {
	var $evaluation_form_question_id;

	function __construct($evaluation_form_question_id, $evaluation_id, $assert = null) {
		$this->evaluation_form_question_id = $evaluation_form_question_id;
		$this->evaluation_id = $evaluation_id;
	}

	public function getResourceId() {
		return "evaluationformquestion".($this->specific ? $this->evaluation_id : "");
	}
}



class CommunityResource extends EntradaAclResource {
	/**
	 * This community's ID
	 * @var integer
	 */
	var $community_id;

	/**
	 * Constructs this community resource with the supplied values
	 * @param integer $community_id The ID of the community this resource is representing
	 * @param boolean $assert Wheather or not to make an assertion
	 */
	function __construct($community_id, $assert = null) {
		$this->community_id = $community_id;
		if (isset($assert)) {
			$this->assert = $assert;
		}
	}

	/**
	 * ACL method for keeping track. Required by Zend_Acl_Resource_Interface.
	 * Will return based on specifc property of this resource instance.
	 * @return string
	 */
	public function getResourceId() {
		return "community".($this->specific ? $this->community_id : "");
	}
}

class EntradaUser implements Zend_Acl_Role_Interface {
	var $userid;
	var $details;
	function EntradaUser($a_userid) {
		$this->userid = $a_userid;
	}
	function getRoleId() {
		return $this->userid;
	}
}

/**
 * Department Head Assertion Class
 *
 * Checks to see if the faculty department head's proxy_id is in the department_heads table
 * which therefore gives them access to the Department Reports section within My Reports.
 */
class DepartmentHeadAssertion implements Zend_Acl_Assert_Interface {
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		global $db, $ENTRADA_USER;

		// This was done so that the correct proxy_id was being used as $role->details["id"] was not using the "masked" id.
		// I'm sure there is a way to get this ID without using the SESSION but I needed to get this into production ASAP.
		// I will fix this as soon as I find out how to access the masked ID without going through the session.
		if (!(is_department_head($ENTRADA_USER->getActiveId()))) {
			return false;
		} else {
			return true;
		}

		return false;
	}
}
/**
 * Dean Assertion Class
 *
 * Checks to see if the if the user is a dean
 * which therefore gives them access to their performance appraisal within the profile section.
 */
class DeanAssertion implements Zend_Acl_Assert_Interface {
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		global $db, $ENTRADA_USER;

		// This was done so that the correct proxy_id was being used as $role->details["id"] was not using the "masked" id.
		// I'm sure there is a way to get this ID without using the SESSION but I needed to get this into production ASAP.
		// I will fix this as soon as I find out how to access the masked ID without going through the session.
		if (!(is_dean($ENTRADA_USER->getActiveId()))) {
			return false;
		} else {
			return true;
		}

		return false;
	}
}
/**
 * Logbook Assertion Class
 *
 * Checks to see if the user has access to a course with loggable objectives
 * associated with it.
 */
class LoggableFoundAssertion implements Zend_Acl_Assert_Interface {
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		$courses = Models_Logbook::getLoggingCourses();
		if ($courses && @count($courses)) {
			return true;
		} else {
			return false;
		}

		return false;
	}
}