<?php

require_once("MSPR.class.php");

$ORGANISATION_ID	= $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"];


class MSPRs extends Collection {
	
	/**
	 * @return MSPRs
	 */
	static public function getAll() {
		global $db, $ORGANISATION_ID;
		$query		= "select * from `student_mspr` a 
						left join `".AUTH_DATABASE."`.`user_data` b 
						on a.user_id = b.id
						where `organisation_id`=".$db->qstr($ORGANISATION_ID)."
						order by lastname, firstname";
		
		$results	= $db->GetAll($query);
		$msprs = array();
		if ($results) {
			foreach ($results as $result) {
				
				//unfortunate but cuts down on db requests by including this in the main query
				$user = new User($result['id'], $result['username'], $result['lastname'], $result['firstname'], $result['number'], $result['grad_year'], $result['entry_year']);
				
				$mspr = new MSPR( $result['id'], $result['last_update'], $result['closed'], $result['generated']);
				$msprs[] = $mspr;
			}
		}
		return new self($msprs);
	}
	
	/**
	 * 
	 * @param int $year
	 * @return MSPRs
	 */
	static public function getYear($year) {
		global $db, $ORGANISATION_ID;
		$query		= "select * from `student_mspr` a 
						left join `".AUTH_DATABASE."`.`user_data` b 
						on a.user_id = b.id 
						where `grad_year`=".$db->qstr($year)." 
						and `organisation_id`=".$db->qstr($ORGANISATION_ID)."
						order by lastname, firstname";
		$results	= $db->GetAll($query);
		$msprs = array();
		if ($results) {
			foreach ($results as $result) {
				
				$user = new User($result['id'], $result['username'], $result['lastname'], $result['firstname'], $result['number'], $result['grad_year'], $result['entry_year']);
				
				$mspr = new MSPR( $result['id'], $result['last_update'], $result['closed'], $result['generated']);
				$msprs[] = $mspr;
			}
		}
		return new self($msprs);
	}
	
	static public function hasCustomDeadlines_Year($year) {
		global $db, $ORGANISATION_ID;
		$query		= "select count(*) from `student_mspr` a 
						left join `".AUTH_DATABASE."`.`user_data` b 
						on a.user_id = b.id 
						where `grad_year`=".$db->qstr($year)."  
						and `organisation_id`=".$db->qstr($ORGANISATION_ID)."
						and a.closed is not null group by user_id";
		
		$result	= $db->GetOne($query);
		return $result > 0;
	}
	
	
	static public function clearCustomDeadlines_Year($year) {
		global $db, $ORGANISATION_ID,$ERROR,$ERRORSTR;
		$query = "update `student_mspr`,`".AUTH_DATABASE."`.`user_data` 
				 set `closed`=NULL
				 where `grad_year`=".$db->qstr($year) ." 
				 and `organisation_id`=".$db->qstr($ORGANISATION_ID)."
				 and user_id=id ";
		
		if(!$db->Execute($query)) {
			$ERROR++;
			$ERRORSTR[] = "Failed to update Submission Deadline.".$db->ErrorMsg();
			application_log("error", "Unable to update a student_mspr record. Database said: ".$db->ErrorMsg());
		}
	}
	
	static public function clearCustomDeadlinesEarlierThan_Year($year, $timestamp) {
		global $db, $ORGANISATION_ID,$ERROR,$ERRORSTR;
		$query = "update `student_mspr`,`".AUTH_DATABASE."`.`user_data` 
				 set `closed`=NULL
				 where `grad_year`=".$db->qstr($year) ." and user_id=id
				 and `organisation_id`=".$db->qstr($ORGANISATION_ID)."
				 and `closed` < ".$db->qstr($timestamp);
		
		if(!$db->Execute($query)) {
			$ERROR++;
			$ERRORSTR[] = "Failed to update Submission Deadline.".$db->ErrorMsg();
			application_log("error", "Unable to update a student_mspr record. Database said: ".$db->ErrorMsg());
		}
	}
}

class MSPRClassData {
	private $year;
	private $closed;
	
	public function __construct($year, $closed) {
		$this->year = $year;
		$this->closed = $closed;
	} 
	
	public function getClosedTimestamp() {
		return $this->closed;
	}
	
	public function getClassYear() {
		return $this->year;
	}
	

	/**
	 * Returns the meta-data for this class. At this point just the close date/time.
	 * @return MSPRClassData
	 */
	static public function get($year) {
		global $db;
		$query		= "select * from `student_mspr_class` 
						where `year`=".$db->qstr($year);
		
		$result = $db->getRow($query);
		if ($result) {
			return new self($result['year'],$result['closed']);
		}
	}
	
	public static function create($year, $closed = null) {
		global $db,$SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR;
		
		$query = "insert into `student_mspr_class` (`year`, `closed`) value (".$db->qstr($year).", ".$db->qstr($closed).")";
		if(!$db->Execute($query)) {
			$ERROR++;
			$ERRORSTR[] = "Failed to create new MSPR Class.";
			application_log("error", "Unable to update a student_mspr_class record. Database said: ".$db->ErrorMsg());
		} else {
			$SUCCESS++;
			$SUCCESSSTR[] = "Successfully created new MSPR Class.";
		}
	}
	
	public function setClosedTimestamp($timestamp) {
		global $db,$ERROR,$ERRORSTR;
		$query = "update `student_mspr_class` set
				 `closed`=".$db->qstr($timestamp)."
				 where `year`=".$db->qstr($this->year);
		
		if(!$db->Execute($query)) {
			$ERROR++;
			$ERRORSTR[] = "Failed to update Class Submission Deadline.".$db->ErrorMsg();
			application_log("error", "Unable to update a student_mspr_class record. Database said: ".$db->ErrorMsg());
		}
	}
}