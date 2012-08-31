<?php

/**
 * Class providing a model of an Organisation and its related data
 * @author Jonathan Fingland
 *
 */
class Organisation {
	private	$organisation_id,
			$organisation_title,
			$organisation_address1,
			$organisation_address2,
			$organisation_city,
			$organisation_province,
			$organisation_country,
			$organisation_postcode,
			$organisation_telephone,
			$organisation_fax,
			$organisation_email,
			$organisation_url,
			$organisation_desc;
	
	function __construct(	$organisation_id,
							$organisation_title,
							$organisation_address1,
							$organisation_address2,
							$organisation_city,
							$organisation_province,
							$organisation_country,
							$organisation_postcode,
							$organisation_telephone,
							$organisation_fax,
							$organisation_email,
							$organisation_url,
							$organisation_desc) {

		$this->organisation_id = $organisation_id;
		$this->organisation_title = $organisation_title;
		$this->organisation_address1 = $organisation_address1;
		$this->organisation_address2 = $organisation_address2;
		$this->organisation_city = $organisation_city;
		$this->organisation_province = $organisation_province;
		$this->organisation_country = $organisation_country;
		$this->organisation_postcode = $organisation_postcode;
		$this->organisation_telephone = $organisation_telephone;
		$this->organisation_fax = $organisation_fax;
		$this->organisation_email = $organisation_email;
		$this->organisation_url = $organisation_url;
		$this->organisation_desc = $organisation_desc;
		
		//be sure to cache this whenever created.
		$cache = SimpleCache::getCache();
		$cache->set($this,"Organisation",$this->organisation_id);
		
	}
	
	/**
	 * returns the internal ID of this organisation
	 * @return int
	 */
	function getID() {
		return $this->organisation_id;
	}
	
	/**
	 * Returns the title of the orgnisation
	 * @return string
	 */
	function getTitle() {
		return $this->organisation_title;
	}
	
	//XXX should address info be formatted differently or remain atomic by address lines?
	/**
	 * Returns the first address line of the organisation
	 * @return string
	 */ 
	function getAddress1() {
		return $this->organisation_address1;
	}
	
	/**
	 * Returns the second address line of the organisation
	 * @return string
	 */
	function getAddress2() {
		return $this->organisation_address2;
	}
	
	/**
	 * Returns the city portion of the Organisation's address
	 * @return string
	 */
	function getCity() {
		return $this->organisation_city;
	}
	
	/**
	 * Returns the state/province portion of the Organisation's address
	 * @return string
	 */
	function getProvince() {
		return $this->organisation_province;
	}
	
	/**
	 * Returns the country portion of the Organisation's address
	 * @return string
	 */
	function getCountry() {
		return $this->organisation_country;
	}
	
	/**
	 * Returns the postal code/zip code portion of the Organisation's address
	 * @return string
	 */
	function getPostCode() {
		return $this->organisation_postcode;
	}
	
	/**
	 * Returns the telephone number for the organisation
	 * @return string
	 */
	function getTelephone() {
		return $this->organisation_telephone;
	}
	
	/**
	 * Returns the fax number for the organistion
	 * @return string
	 */
	function getFax() {
		return $this->organisation_fax;
	}
	
	/**
	 * Returns the email address for the organisation
	 * @return string
	 */
	function getEmail() {
		return $this->organisation_email;
	}
	
	/**
	 * Returns the web page URL for the organisation 
	 * @return string
	 */
	function getURL() {
		return $this->organisation_url;
	}
	
	/**
	 * Returns the description of the organistion
	 * @return string
	 */
	function getDescription() {
		return $this->organisation_desc;
	}
	
	/**
	 * Returns the Organisation corresponding to the supplied ID
	 * @param int $organisation_id
	 * @return Organisation
	 */
	static function get($organisation_id) {
		$cache = SimpleCache::getCache();
		$organisation = $cache->get("Organisation",$organisation_id);
		if (!$organisation) {
			global $db;
			$query = "SELECT * FROM `".AUTH_DATABASE."`.`organisations` WHERE `organisation_id` = ".$db->qstr($organisation_id);
			$result = $db->getRow($query);
			if ($result) {
				$organisation = new Organisation($result['organisation_id'],$result['organisation_title'],$result['organisation_address1'],$result['organisation_address2'],$result['organisation_city'],$result['organisation_province'],$result['organisation_country'],$result['organisation_postcode'],$result['organisation_telephone'],$result['organisation_fax'],$result['organisation_email'],$result['organisation_url'],$result['organisation_desc']);			
			}		
		} 
		return $organisation;
		
	}
}