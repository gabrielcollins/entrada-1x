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

require_once("Models/organisations/Organisation.class.php");
require_once("Models/users/Cohort.class.php");

/**
 * User class with basic information and access to user related info
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@quensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class User {
	private $id,
			$active_id,
			$username,
			$firstname,
			$lastname,
			$number,
			$grad_year,
			$cohort,
			$entry_year,
			$password,
			$organisation_id,
			$department,
			$prefix,
			$email,
			$email_alt,
			$google_id,
			$telephone,
			$fax,
			$address,
			$city,
			$province,
			$postcode,
			$country,
			$country_id,
			$province_id,
			$cached_country,
			$cached_province,
			$notes,
			$privacy_level,
			$notifications,
			$office_hours,
			$clinical,
			$active_organisation,
			$all_organisations,
			$organisation_group_role,
			$default_access_id,
			$access_id,
			$group,
			$role,
			$active_group,
			$active_role;


	/**
	 * lookup array for formatting user information
	 * <code>
	 * $format_keys = array(
	 *								"f" => "firstname",
	 *								"l" => "lastname",
	 *								"p" => "prefix"
	 *								);
	 *
	 * //Usage:
	 * if ($user->getPrefix()) {
	 *   echo $user->getName("%p. %f %l"); //i.e. Dr. John Smith
	 * } else {
	 *   echo $user->getName("%f %l"); //i.e. John Smith
	 * }
	 * </code>
	 * @var array
	 */
	private static $format_keys = array(
									"f" => "firstname",
									"l" => "lastname",
									"p" => "prefix"
									);

	function __construct() {}

	/**
	 * Returns the id of the user
	 * @return int
	 */
	public function getProxyId() {
		return $this->id;
	}

	/**
	 * Returns the id of the user
	 * @return int
	 */
	public function getID() {
		return $this->id;
	}

	/**
	 * Returns the active proxy_id of the user
	 * @return int
	 */
	public function getActiveId() {
		if ($this->active_id) {
			return $this->active_id;
		} elseif ($this->access_id) {
			setActiveId($this->access_id);
			return $this->active_id;
		} else {
			return $this->id;
		}
	}

	/**
	 * Sets the active proxy_id of the user
	 * @return int
	 */
	public function setActiveId($value) {
		global $db;

		$query = "SELECT `user_id` FROM `".AUTH_DATABASE."`.`user_access` WHERE `id` = ".$db->qstr($value);
		$active_id = $db->GetOne($query);
		if ($active_id) {
			$this->active_id = $active_id;
		}
	}

	/**
	 * Returns the username of the user
	 * @return string
	 */
	function getUsername() {
		return $this->username;
	}

	/**
	 * Returns the graduating year of the user, if available
	 * @return int
	 */
	function getGradYear() {
		return $this->grad_year;
	}

	/**
	 * Returns the cohort of the user, if available
	 * @return int
	 */
	function getCohort() {
		return $this->cohort;
	}

	/**
	 * Sets the cohort of the user, if available
	 * @param int $value : The cohort with which the given user is associated.
	 */
	public function setCohort($value) {
		$this->cohort = $value;
	}

	/**
	 * Returns the entire class of the same cohort
	 * @return Cohort
	 */
	function getFullCohort() {
		if ($this->cohort) {
			return Cohort::get($this->cohort);
		}
	}

	/**
	 * Returns the year a student enetered med school, if available
	 * @return int
	 */
	function getEntryYear() {
		return $this->entry_year;
	}

	/**
	 * Returns the first name of the user
	 * @return string
	 */
	function getFirstname(){
		return $this->firstname;
	}

	/**
	 * Returns the last name of the user
	 * @return string
	 */
	function getLastname() {
		return $this->lastname;
	}

	/**
	 * Returns the Last and First names formatted as "lastname, firstname"
	 * @return string
	 */
	function getFullname($reverse = true) {
		if ($reverse) {
			return $this->getName("%l, %f");
		} else {
			return $this->getName("%f %l");
		}
	}

	/**
	 * Returns the user's name formatted according to the format string supplied. Default format is "%f %l" (firstname, lastname)
	 * <code>
	 * if ($user->getPrefix()) {
	 *   echo $user->getName("%p. %f %l"); //i.e. Dr. John Smith
	 * } else {
	 *   echo $user->getName("%f %l"); //i.e. John Smith
	 * }
	 * </code>
	 * @see User::$format_keys
	 * @param string $format
	 * @return string
	 */
	function getName($format = "%f %l") {
		foreach(self::$format_keys as $key => $var) {
			$pattern = "/([^%])%".$key."|^%".$key."|(%%)%".$key."/";
			$format = preg_replace($pattern, "$1$2".$this->{$var}, $format);
		}
		$format = preg_replace("/%%/", "%", $format);
		return $format;
	}

	/**
	 * Returns the User's specified prefix, if any. e.g. Mr, Mrs, Dr,...
	 * @return string
	 */
	function getPrefix() {
		return $this->prefix;
	}

	/**
	 * Returns the user's email address, if available
	 * @return string
	 */
	function getEmail() {
		return $this->email;
	}

	/**
	 * Returns the user's alternate email address, if available
	 * @return string
	 */
	function getEmailAlternate() {
		return $this->email_alt;
	}

	/**
	 * Returns the real world student number/employee number
	 * @return int
	 */
	function getNumber() {
		return $this->number;
	}

	/**
	 * Not supported yet.
	 *
	 * @return Organisation
	 */
//	function getOrganisation() {
//		return Organisation::get($this->organisation_id);
//	}

	/**
	 * Returns the ID of the organisation to which the user belongs
	 * @return int
	 */
	function getOrganisationId() {
		return $this->organisation_id;
	}

	/**
	 * Returns a collection of photos belonging to the user.
	 * @return UserPhotos
	 */
	function getPhotos() {
		return UserPhotos::get($this->getID());
	}

	/**
	 * Returns the currently active organisation.
	 * If not set then it returns the default org for this user found
	 * in the user_data table.
	 *
	 * @return int
	 */
	function getActiveOrganisation() {
		if ($this->active_organisation) {
			return $this->active_organisation;
		}
		else if ($_SESSION["permissions"][$this->getAccessId()]["organisation_id"]) {
			return $_SESSION["permissions"][$this->getAccessId()]["organisation_id"];
		}
		else {
			return $this->organisation_id;
		}
	}

	/**
	 * Sets the active organisation.
	 *
	 * @param <String> $value - the active, i.e., current org
	 */
	public function setActiveOrganisation($value){
		$this->active_organisation = $value;
	}

	/**
	 * Returns an array of all the organisation that this user
	 * belongs to.
	 *
	 * @return array
	 */
	function getAllOrganisations() {
		return $this->all_organisations;
	}

	/**
	 * Sets the array of all orgs this user belongs to.
	 *
	 * @param <array> $value
	 */
	function setAllOrganisations($value) {
		$this->all_organisations = $value;
	}

	/**
	 * Returns the int/boolean of the user's "clinical" status.
	 *
	 * @return int
	 */
	function getClinical() {
		return $this->clinical;
	}

	/**
	 * Sets the int/boolean for the user's "clinical" status.
	 *
	 * @param int $value
	 */
	function setClinical($value) {
		$this->clinical = $value;
	}


	function setOrganisationGroupRole($value) {
		$this->organisation_group_role = $value;
	}


	function getOrganisationGroupRole() {
		return $this->organisation_group_role;
	}

	function setAccessId($value) {
		global $db;
		if ((!isset($value) || !$value) && isset($this->default_access_id) && $this->default_access_id) {
			$value = $this->default_access_id;
		} elseif ((!isset($value) || !$value) && (!isset($this->default_access_id) || !$this->default_access_id)) {
			$query = "SELECT `id` FROM `".AUTH_DATABASE."`.`user_access`
						WHERE `user_id` = ".$db->qstr($this->getID())."
						AND `app_id` = ".$db->qstr(AUTH_APP_ID)."
						AND `account_active` = 'true'
						AND (`access_starts` = '0' OR `access_starts` <= ".$db->qstr(time()).")
						AND (`access_expires` = '0' OR `access_expires` >= ".$db->qstr(time()).")
						AND `organisation_id` = ".$db->qstr(($this->getActiveOrganisation() ? $this->getActiveOrganisation() : $this->getOrganisationID()));
			$value = $db->getOne();
			$this->default_access_id = $value;
		}
		$this->access_id = $value;
		$this->setActiveId($value);

		// Get all of the users orgs
		$query = "SELECT b.`organisation_id`, b.`organisation_title`
					  FROM `" . AUTH_DATABASE . "`.`user_access` a
					  JOIN `" . AUTH_DATABASE . "`.`organisations` b
					  ON a.`organisation_id` = b.`organisation_id`
					  WHERE a.`user_id` = ?
					  AND a.`app_id` = ?";
		$results = $db->getAll($query, array($this->getActiveId(), AUTH_APP_ID));

		// Every user should have at least one org.
		if ($results) {
			$organisation_list = array();
			foreach ($results as $result) {
				$organisation_list[$result["organisation_id"]] = html_encode($result["organisation_title"]);
			}
			$this->setAllOrganisations($organisation_list);
		}

		// Get all of the users groups and roles for each organisation
		$query = "SELECT b.`organisation_id`, b.`organisation_title`, a.`group`, a.`role`, a.`id`, c.`organisation_id` AS `default_organisation_id`
					FROM `" . AUTH_DATABASE . "`.`user_access` a
					JOIN `" . AUTH_DATABASE . "`.`organisations` b
					ON a.`organisation_id` = b.`organisation_id`
					JOIN `" . AUTH_DATABASE . "`.`user_data` c
					ON a.`user_id` = c.`id`
					WHERE a.`user_id` = ?
					AND a.`account_active` = 'true'
					AND (a.`access_starts` = '0' OR a.`access_starts` < ?)
					AND (a.`access_expires` = '0' OR a.`access_expires` >= ?)
					AND a.`app_id` = ?
					ORDER BY a.`id` ASC";

		$results = $db->getAll($query, array($this->getActiveId(), time(), time(), AUTH_APP_ID));
		if ($results) {
			$org_group_role = array();
			foreach ($results as $result) {
				$org_group_role[$result["organisation_id"]][] = array("group" => html_encode($result["group"]), "role" => html_encode($result["role"]), "access_id" => $result["id"]);
			}
			$this->setOrganisationGroupRole($org_group_role);
		}
	}

	function getAccessId() {
		return $this->access_id;
	}

	function getDefaultAccessId() {
		return $this->default_access_id;
	}

	function setDefaultAccessId($value) {
		$this->default_access_id = $value;
	}

	/**
	 *
	 * @param int proxy_id
	 * @return User
	 */
	public static function get($proxy_id) {
		global $db, $ENTRADA_CACHE;

		if (!isset($ENTRADA_CACHE) || !($user = $ENTRADA_CACHE->load("user_".AUTH_APP_ID."_".$proxy_id))) {
			$user = new User();
			$query = "	SELECT a.*, b.`group`, b.`role`
						FROM `" . AUTH_DATABASE . "`.`user_data` a
						LEFT JOIN `" . AUTH_DATABASE . "`.`user_access` b
						ON a.`id`=b.`user_id` and b.`app_id` = ?
						WHERE a.`id` = ?";
			$result = $db->GetRow($query, array(AUTH_APP_ID, $proxy_id));
			if ($result) {
				$user = self::fromArray($result, $user);
			}

			if (isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["access_id"]) && $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["access_id"]) {
				$query = "SELECT `id` FROM `".AUTH_DATABASE."`.`user_access`
							WHERE `id` = ?
							AND `account_active` = 'true'
							AND (`access_starts` = '0' OR `access_starts` < ?)
							AND (`access_expires` = '0' OR `access_expires` >= ?)
							AND `app_id` = ?
							AND `user_id` = ?";
				$available = $db->getRow($query, array($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["access_id"], time(), time(), AUTH_APP_ID, $user->getID()));
				if ($available) {
					$user->setAccessId($available["id"]);
				} else {
					$query = "SELECT b.`id` FROM `permissions` AS a
								JOIN `".AUTH_DATABASE."`.`user_access` AS b
								ON a.`assigned_by` = b.`user_id`
								WHERE b.`id` = ?
								AND b.`account_active` = 'true'
								AND (b.`access_starts` = '0' OR b.`access_starts` < ?)
								AND (b.`access_expires` = '0' OR b.`access_expires` >= ?)
								AND b.`app_id` = ?
								AND a.`assigned_to` = ?
								AND a.`valid_from` <= ?
								AND a.`valid_until` >= ?";
					$mask_available = $db->getRow($query, array($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["access_id"], time(), time(), AUTH_APP_ID, $user->getID(), time(), time()));
					if ($mask_available) {
						$user->setAccessId($mask_available["id"]);
					}
				}
			} else {
				$query = "	SELECT a.`group`, a.`role`, a.`id`
								FROM `" . AUTH_DATABASE . "`.`user_access` a
								WHERE a.`user_id` = " . $db->qstr($user->getID()) . "
								AND a.`organisation_id` = " . $db->qstr($user->getActiveOrganisation()) . "
								AND a.`app_id` = " . $db->qstr(AUTH_APP_ID) . "
								ORDER BY a.`id` ASC";
				$result = $db->getRow($query);
				if ($result) {
					$user->setAccessId($result["id"]);
					$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["access_id"] = $user->getAccessId();
				}
			}

			$query = "SELECT a.`group_id` FROM `groups` AS a
						JOIN `group_members` AS b
						ON a.`group_id` = b.`group_id`
						WHERE a.`group_type` = 'cohort'
						AND b.`proxy_id` = ?";
			$result = $db->getOne($query, array($proxy_id));
			if ($result) {
				$user->setCohort($result);
			}

			//get all of the users groups and roles for each organisation
			$query = "SELECT b.`organisation_id`, b.`organisation_title`, a.`group`, a.`role`, a.`id`
						FROM `" . AUTH_DATABASE . "`.`user_access` a
						JOIN `" . AUTH_DATABASE . "`.`organisations` b
						ON a.`organisation_id` = b.`organisation_id`
						WHERE a.`user_id` = ?
						AND a.`account_active` = 'true'
						AND (a.`access_starts` = '0' OR a.`access_starts` < ?)
						AND (a.`access_expires` = '0' OR a.`access_expires` >= ?)
						AND a.`app_id` = ?
						ORDER BY a.`id` ASC";

			$results = $db->getAll($query, array($proxy_id, time(), time(), AUTH_APP_ID));

			//every user should have at least one org.
			if ($results) {
				$organisation_list = array();
				$org_group_role = array();
				
				foreach ($results as $result) {
					$organisation_list[$result["organisation_id"]] = html_encode($result["organisation_title"]);
				}
				
				$user->setAllOrganisations($organisation_list);
				
				foreach ($results as $result) {
					if ((!isset($user->default_access_id) || !$user->default_access_id)) {
						if (!isset($_SESSION["permissions"][$user->getAccessId()]["organisation_id"]) || !$_SESSION["permissions"][$user->getAccessId()]["organisation_id"]) {
							$_SESSION["permissions"][$user->getAccessId()]["organisation_id"] = $result["organisation_id"];
							$user->setActiveOrganisation($result["organisation_id"]);
						}
						$user->setDefaultAccessId($result["id"]);
					}
    				$org_group_role[$result["organisation_id"]][] = array("group" => html_encode($result["group"]), "role" => html_encode($result["role"]), "access_id" => $result["id"]);
				}
				if ((!isset($_SESSION["permissions"][$user->getAccessId()]["organisation_id"]) || !$_SESSION["permissions"][$user->getAccessId()]["organisation_id"]) && isset($results[0]["organisation_id"]) && $results[0]["organisation_id"]) {
					$_SESSION["permissions"][$user->getAccessId()]["organisation_id"] = $results[0]["organisation_id"];
					$user->setActiveOrganisation($results[0]["organisation_id"]);
				}

				$user->setOrganisationGroupRole($org_group_role);
			}

			$ENTRADA_CACHE->save($user, "user_".AUTH_APP_ID."_".$proxy_id, array("auth"), 300);
			$ENTRADA_CACHE->save(md5(serialize($results)), "access_hash_" . $proxy_id, array("access_hash"), 300);
		}
		return $user;
	}
	
	public static function getAccessHash() {
		global $db, $ENTRADA_USER;
		//get all of the users groups and roles for each organisation
		$query = "		SELECT b.`organisation_id`, b.`organisation_title`, a.`group`, a.`role`, a.`id`
						FROM `" . AUTH_DATABASE . "`.`user_access` a
						JOIN `" . AUTH_DATABASE . "`.`organisations` b
						ON a.`organisation_id` = b.`organisation_id`
						WHERE a.`user_id` = ?
						AND a.`account_active` = 'true'
						AND (a.`access_starts` = '0' OR a.`access_starts` < ?)
						AND (a.`access_expires` = '0' OR a.`access_expires` >= ?)
						AND a.`app_id` = ?
						ORDER BY a.`id` ASC";

		$results = $db->getAll($query, array($ENTRADA_USER->getID(), time(), time(), AUTH_APP_ID));
		return md5(serialize($results));
	}

	/**
	 * Returns a User object created using the array inputs supplied
	 * @param array $arr
	 * @return User
	 */
	public static function fromArray(array $arr, User $user) {
		$user->id = $arr['id'];
		$user->active_id = $arr['id'];
		$user->username = $arr['username'];
		$user->firstname = $arr['firstname'];
		$user->lastname = $arr['lastname'];
		$user->number = $arr['number'];
		$user->grad_year = $arr['grad_year'];
		$user->entry_year = $arr['entry_year'];
		$user->password = $arr['password'];
		$user->organisation_id = $arr['organisation_id'];
		$user->department = $arr['department'];
		$user->prefix = $arr['prefix'];
		$user->email = $arr['email'];
		$user->email_alt = $arr['email_alt'];
		$user->google_id = $arr['google_id'];
		$user->telephone = $arr['telephone'];
		$user->fax = $arr['fax'];
		$user->address = $arr['address'];
		$user->city = $arr['city'];
		$user->province = $arr['province'];
		$user->postcode = $arr['postcode'];
		$user->country = $arr['country'];
		$user->country_id = $arr['country_id'];
		$user->province_id = $arr['province_id'];
		$user->notes = $arr['notes'];
		$user->privacy_level = $arr['privacy_level'];
		$user->notifications = $arr['notifications'];
		$user->office_hours = $arr['office_hours'];
		$user->clinical = $arr['clinical'];
		$user->group = $arr['group'];
		$user->role = $arr['role'];

		return $user;
	}

	/**
	 * @return Departments
	 */
	public function getDepartments() {
		return Departments::getByUser($this->user_id);
	}

		/**
	 * Returns the active group for the active organisation.
	 *
	 * @return array
	 */
	public function getActiveGroup() {
		if ($_SESSION["permissions"][$this->getAccessId()]["group"]) {
			return $_SESSION["permissions"][$this->getAccessId()]["group"];
		}
		else {
			return $this->group;
		}
	}

	/**
	 * Sets the active group.
	 *
	 * @param type $string
	 */
	public function setActiveGroup($group) {
		$this->active_group = $group;
	}

	/**
	 * Returns the active role for the active organisation.
	 *
	 * @return array
	 */
	public function getActiveRole() {
		if ($_SESSION["permissions"][$this->getAccessId()]["role"]) {
			return $_SESSION["permissions"][$this->getAccessId()]["role"];
		}
		else {
			return $this->role;
		}
	}

	/**
	 * Sets the active role.
	 *
	 * @param type string
	 */
	public function setActiveRole($role) {
		$this->active_role = $role;
	}

	/**
	 * Returns the access group to which this user belongs e.g. student, faculty, ...
	 * @return string
	 */
	public function getGroup() {
		if (is_null($this->group) && !$this->getAccess()) {
			return;
		}
		return $this->group;
	}

	/**
	 * Returns the access role to which the user belongs
	 * @return string
	 */
	public function getRole() {
		if (is_null($this->role) && !$this->getAccess()) {
			return;
		}
		return $this->role;
	}

	/**
	 * Sets the active role.
	 *
	 * @param type string
	 */
	public function setRole($role) {
		$this->role = $role;
	}

	/**
	 * Internal function for getting access information for a user
	 * @return bool
	 */
	private function getAccess() {
		global $db;
		$query = "	SELECT *
					FROM `".AUTH_DATABASE."`.`user_access`
					WHERE `user_id` = ?
					AND `account_active` = 'true'
					AND (`access_starts` = '0' OR `access_starts` < ?)
					AND (`access_expires` = '0' OR `access_expires` >= ?)
					AND `app_id` = ?";
		$result = $db->getRow($query, array($this->getID(), time(), time(), AUTH_APP_ID));
		if ($result) {
			$this->group = $result["group"];
			$this->role = $result["role"];

			return true;
		}
	}

	/**
	 * Returns the user-specified (numeric) privacy level
	 * @return int
	 */
	public function getPrivacyLevel(){
		return $this->privacy_level;
	}

	/**
	 * @return string
	 */
	public function getAlternateEmail() {
		return $this->email_alt;
	}

	/**
	 * @return string
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * NOTE: also used for zip codes and the like
	 * @return string
	 */
	public function getPostalCode() {
		return $this->postcode;
	}

	/**
	 * @return Region
	 */
	public function getProvince() {
		if (is_null($this->cached_province)) {
			if ($this->province_id && $this->country_id) {
				if ($prov) {
					$c_id = $prov->getParentID();
					if ($c_id == $this->country_id) {
						$this->cached_province = $prov;
					}
				} else {
					$this->cached_province = new Region($this->province);
				}
			} else {
				$this->cached_province = new Region($this->province);
			}
		}
		return $this->cached_province;
	}

	/**
	 * Returns a Country object for the user's specified country. Legacy Support: Note that some users may not have country_id specified and rely on older country names. In those cases a new object is returned and can be operated in the same manner as newer country data
	 * @return Country
	 */
	public function getCountry() {
		if (is_null($this->cached_country)) {
			if ($this->country_id && ($country = Country::get($this->country_id))) {
				$this->cached_country = $country;
			} else {
				$this->cached_country = new Country($this->country);
			}
		}
		return $this->cached_country;
	}

	/**
	 * Returns the street address portion of a user's provided address. For excample: 123 Fourth Street
	 * @return string
	 */
	public function getAddress() {
		return $this->address;
	}

	/**
	 * @return string
	 */
	public function getTelephone() {
		return $this->telephone;
	}

	/**
	 * @return string
	 */
	public function getFax() {
		return $this->fax;
	}

	/**
	 * @return string
	 */
	public function getOfficeHours() {
		return $this->office_hours;
	}

	/**
	 * @return Users
	 */
	public function getAssistants() {
		global $db;

		$query		= "	SELECT b.*, a.*
								FROM `permissions` AS a
								LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
								ON b.`id` = a.`assigned_to`
								WHERE a.`assigned_by`=?
								AND (a.`valid_from` = '0' OR a.`valid_from` <= ?) AND (a.`valid_until` = '0' OR a.`valid_until` > ?)
								ORDER BY `valid_until` ASC";

		$time = time();
		$results = $db->getAll($query, array($this->getID(), $time, $time));
		$users = array();
		if ($results) {
			foreach ($results as $result) {
				$user =  Assistant::fromArray($result);
				$users[] = $user;
			}
		}
		return new Users($users);
	}

	/**
	 * Creates a user account and updates object, returns true or false.
	 * $user_data requires: "username", "firstname", "lastname", "email", "password", "organisation_id"
	 * $user_access requires: "group", "role", "app_id"
	 *
	 * @param array $user_data User data array, keys match table fields. Ex: array("id" => "1", "username" => "foo").
	 * @param array $user_access User access array, keys match table fields. Ex: array("group" => "admin").
	 * @return boolean
	 */
	public function createUser(array $user_data, array $user_access) {
		global $db;

		$required_user_data		= array("username", "firstname", "lastname", "email", "password", "organisation_id");
		$required_user_access	= array("group", "role", "app_id");

		foreach ($required_user_data as $data) {
			if (!array_key_exists($data, $user_data)) {
				$error = true;
			}
		}

		foreach ($required_user_access as $data) {
			if (!array_key_exists($data, $user_access)) {
				$error = true;
			}
		}

		if (!$error) {

			foreach ($user_data as $fieldname => $data) {
				$processed["user_data"][$fieldname] = clean_input($data, array("trim", "striptags"));
			}

			foreach ($user_access as $fieldname => $data) {
				$processed["user_access"][$fieldname] = clean_input($data, array("trim", "striptags"));
			}

			if ($db->AutoExecute("`".AUTH_DATABASE."`.`user_data`", $processed["user_data"], "INSERT")) {

				$processed["user_data"]["id"]			= $db->Insert_ID();
				$processed["user_access"]["user_id"]	= $processed["user_data"]["id"];

				if (!isset($processed["user_access"]["organisation_id"])) { $processed["user_access"]["organisation_id"] = $processed["user_data"]["organisation_id"]; }
				if (!isset($processed["user_access"]["access_starts"])) { $processed["user_access"]["access_starts"] = time(); }
				if (!isset($processed["user_access"]["account_active"])) { $processed["user_access"]["account_active"] = "true"; }
				if (!isset($processed["user_access"]["private_hash"])) { $processed["user_access"]["private_hash"]	= generate_hash(); }

				if (!$db->AutoExecute("`".AUTH_DATABASE."`.`user_access`", $processed["user_access"], "INSERT")) {
					application_log("error", "Failed to add user, DB said: ".$db->ErrorMsg());
					$return = false;
				} else {

					$params = get_class_vars(__CLASS__);

					foreach ($params as $param_name => $param) {
						$this->$param_name = (isset($processed["user_data"][$param_name]) ? $processed["user_data"][$param_name] : (isset($processed["user_access"][$param_name]) ? $processed["user_access"][$param_name] : $param));
					}

					$return = true;
				}

			} else {
				application_log("error", "Failed to add user, DB said: ".$db->ErrorMsg());
				$return = false;
			}

		} else {
			$return = false;
		}

		return $return;
	}

	/**
	 * Updates a user account and updates object, returns true or false.
	 * @param $user_data User data array, keys match table fields. Ex: array("id" => "1", "username" => "foo")
	 * @param $user_access User access array, keys match table fields. Assumes user_id from $user_data["id"]. Ex: array("group" => "admin").
	 * @return boolean
	 */
	public function updateUser(array $user_data, array $user_access = array()) {
		global $db;

		if (!isset($user_data["id"]) || empty($user_data["id"])) {
			$processed["user_data"]["id"] = $this->getID();
		}

		foreach ($user_data as $fieldname => $data) {
			$processed["user_data"][$fieldname] = clean_input($data, array("trim", "striptags"));
		}

		if (!empty($user_access)) {
			foreach ($user_access as $fieldname => $data) {
				$processed["user_access"][$fieldname] = clean_input($data, array("trim", "striptags"));
			}
		}

		if ($db->AutoExecute("`".AUTH_DATABASE."`.`user_data`", $processed["user_data"], "UPDATE", "id = ".$db->qstr($processed["user_data"]["id"]))) {

			if (!empty($processed["user_access"])) {
				if (!$db->AutoExecute("`".AUTH_DATABASE."`.`user_access`", $processed["user_access"], "UPDATE", "user_id = ".$db->qstr($processed["user_data"]["id"]))) {
					application_log("error", "Failed to update user [".$processed["user_data"]["id"]."], DB said: ".$db->ErrorMsg());
					$return = false;
				}
			}

			$params = get_class_vars(__CLASS__);

			foreach ($params as $param_name => $param) {
				$this->$param_name = (isset($processed["user_data"][$param_name]) ? $processed["user_data"][$param_name] : (isset($processed["user_access"][$param_name]) ? $processed["user_access"][$param_name] : $param));
			}

		} else {
			application_log("error", "Failed to update user [".$processed["user_data"]["id"]."], DB said: ".$db->ErrorMsg());
			$return = false;
		}


		return $return;
	}

	/**
	 * Deactivates a user account and returns true or false.
	 * @param int $id The userid to activate. Uses objects ID if empty.
	 * @return boolean
	 */
	public function deactivateUser($id = "") {
		global $db;

		if (!empty($id)) {
			$proxy_id = (int) $id;
		} else {
			$proxy_id = $this->getID();
		}

		if ($proxy_id) {
			$processed["account_active"] = "false";
			if (!$db->AutoExecute("`".AUTH_DATABASE."`.`user_access`", $processed, "UPDATE", "user_id = ".$db->qstr($proxy_id))) {
				application_log("error", "Failed to set account_active to false for user [".$processed["user_data"]["id"]."], DB said: ".$db->ErrorMsg());
				$return = false;
			} else {
				$return = true;
			}
		} else {
			application_log("error", "Unable to deactivate user account, no proxy_id.");
			$return = false;
		}

		return $return;

	}

}