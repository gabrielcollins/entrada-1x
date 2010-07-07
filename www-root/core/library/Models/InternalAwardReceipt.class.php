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
 * This file contains all of the functions used within Entrada.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

require_once("User.class.php");
require_once("InternalAward.class.php");

/**
 * 
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@quensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class InternalAwardReceipt {
	private $award_receipt_id;
	private $award;
	private $user;
	private $year;
	
	function __construct(User $user, Award $award, $award_receipt_id, $year){
		$this->user = $user;
		$this->award = $award;
		$this->award_receipt_id = $award_receipt_id;
		$this->year = $year;
	}
	
	public function getID() {
		return $this->award_receipt_id;
	}
	
	public function getAwardYear() {
		return $this->year;
	}
	
	public function getUser() {
		return $this->user;
	}
	
	public function getAward() {
		return $this->award;
	}
	
	static public function create($award_id, $user_id, $year) {
		global $db,$SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR;
	
		$query = "INSERT INTO `student_awards_internal` (`user_id`,`award_id`, `year`) VALUES (".$db->qstr($user_id).", ".$db->qstr($award_id).", ".$db->qstr($year).")";
		if(!$db->Execute($query)) {
			$ERROR++;
			$ERRORSTR[] = "Failed to add award recipient to database. Please check your values and try again.";
			application_log("error", "Unable to insert a student_awards_internal record. Database said: ".$db->ErrorMsg());
		} else {
			$SUCCESS++;
			$SUCCESSSTR[] = "Successfully added Award Recipient.";
		}
	}
	
	/**
	 * 
	 * @param int $award_receipt_id
	 * @return AwardRecipient
	 */
	static public function get($award_receipt_id) {
		global $db;
		$query		= "SELECT a.id as `award_receipt_id`, b.id as user_id, award_id, c.title, c.award_terms, c.disabled, lastname, firstname, a.year 
				FROM `". DATABASE_NAME ."`.`student_awards_internal` a 
				left join `". DATABASE_NAME ."`.`student_awards_internal_types` c on c.id = a.award_id 
				right join `".AUTH_DATABASE."`.`user_data` b on a.`user_id` = b.`id` 
				WHERE a.id = ".$db->qstr($award_receipt_id);
		
		$result	= $db->GetRow($query);
			
		if ($result) {
			$user = new User($result['user_id'], null, $result['lastname'], $result['firstname']);
			$award = new InternalAward($result['award_id'], $result['title'], $result['award_terms'], $result['disabled']);
			return new InternalAwardReceipt( $user, $award, $result['award_receipt_id'], $result['year']);
		} else {
			$ERROR++;
			$ERRORSTR[] = "Failed to retreive award receipt from database.";
			application_log("error", "Unable to retrieve a student_awards_internal record. Database said: ".$db->ErrorMsg());
		}
			 
	} 
	
	public function delete() {
		global $db,$SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR;
	
		$query = "DELETE FROM `student_awards_internal` where `id`=".$db->qstr($this->award_receipt_id);
		if(!$db->Execute($query)) {
			$ERROR++;
			$ERRORSTR[] = "Failed to remove award receipt from database.";
			application_log("error", "Unable to delete a student_awards_internal record. Database said: ".$db->ErrorMsg());
		} else {
			$SUCCESS++;
			$SUCCESSSTR[] = "Successfully removed award receipt.";
		}
	}
}