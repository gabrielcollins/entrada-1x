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

require_once("Models/utility/SimpleCache.class.php");
require_once("Organisation.class.php");
require_once("Models/utility/Collection.class.php");

/**
 * Utility Class for getting a list of Users
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@quensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class Organisations extends Collection {
	
	/**
	 * Returns a Collection of Organisation objects 
	 * TODO add criteria to selection process 
	 * @param array
	 * @return Users
	 */
	static public function get() {
		global $db;
		$query = "SELECT * FROM `".AUTH_DATABASE."`.`organisations`";
		
		$results = $db->getAll($query);
		$organisations = array();
		if ($results) {
			foreach ($results as $result) {
				$organisation = new Organisation($result['organisation_id'],$result['organisation_title'],$result['organisation_address1'],$result['organisation_address2'],$result['organisation_city'],$result['organisation_province'],$result['organisation_country'],$result['organisation_postcode'],$result['organisation_telephone'],$result['organisation_fax'],$result['organisation_email'],$result['organisation_url'],$result['organisation_desc']);			
				$organisations[] = $organisation;
			}
		}
		return new self($organisations);
	}

}