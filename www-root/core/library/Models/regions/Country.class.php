<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 *
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
*/

/**
 * Country provides country name and abbreviation (if available). based on data from database
 * @author Jonathan Fingland
 *
 */
class Country extends Region {
	
	/**
	 * Returns the Country corresponding to the provided country ID
	 * @param int $country_id
	 * @return Country
	 */
	public static function get($country_id) {
		global $db;
		$query = "SELECT * FROM `global_lu_countries` WHERE `countries_id` = ?";
		$result = $db->getRow($query, array($country_id));
		if ($result) {
			return new self($result['country'], $result['countries_id']);
		}		
	}
}
 
