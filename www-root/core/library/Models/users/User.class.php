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
			$all_organisations;

	
	private $group, $role;

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
		return $this->getProxyId();
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
	function getOrganisationID() {
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
		else if ($_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]) {
			return $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"];
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
	 * 
	 * @param int proxy_id
	 * @return User
	 */
	public static function get($proxy_id) {
		$user = new User();
		global $db;
		$query = "SELECT a.*, b.`group`, b.`role`
					  FROM `" . AUTH_DATABASE . "`.`user_data` a
					  LEFT JOIN `" . AUTH_DATABASE . "`.`user_access` b
						  on a.`id`=b.`user_id` and b.`app_id`=?
					  WHERE a.`id` = ?";
		$result = $db->getRow($query, array(AUTH_APP_ID, $proxy_id));

		if ($result) {
			$user = self::fromArray($result, $user);
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

		//get all of the users orgs
		$query = "SELECT b.`organisation_id`, b.`organisation_title`
					  FROM `" . AUTH_DATABASE . "`.`user_organisations` a
					  JOIN `" . AUTH_DATABASE . "`.`organisations` b
						  on a.`organisation_id` = b.`organisation_id`
					  WHERE a.`proxy_id` = ?";
		$results = $db->getAll($query, array($proxy_id));

		//every user should have at least one org.
		if ($results) {
			foreach ($results as $result) {
				$organisation_list[$result["organisation_id"]] = html_encode($result["organisation_title"]);
			}
			$user->setAllOrganisations($organisation_list);
		}
		return $user;
	}
	
	/**
	 * Returns a User object created using the array inputs supplied
	 * @param array $arr
	 * @return User
	 */
	public static function fromArray(array $arr, User $user) {
		$user->id = $arr['id'];
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

}