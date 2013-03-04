<?php

/**
 * 
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Simple class for data-entry of observerships. XXX Replace when policy and plan in place for observserships going forward.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 */
class Observership implements Editable {
	private $id;
	private $student_id;
	private $title;
	private $site;
	private $start;
	private $end;
	private $location;
	private $preceptor_firstname;
	private $preceptor_lastname;
	private $preceptor_prefix;
	private $preceptor_proxy_id;
	private $preceptor_email;
	private $status;
	private $unique_id;
	private $notice_sent;
	
	function __construct($id, $student_id, $title, $site, $location, $preceptor_proxy_id, $preceptor_firstname, $preceptor_lastname, $start, $end, $preceptor_prefix, $preceptor_email, $status, $unique_id, $notice_sent) {
		$this->id = $id;
		$this->student_id = $student_id;
		$this->title = $title;
		$this->site = $site;
		$this->location = $location;
		$this->start = $start;
		$this->end = $end;
		$this->preceptor_firstname = $preceptor_firstname;
		$this->preceptor_lastname = $preceptor_lastname;
		$this->preceptor_prefix = $preceptor_prefix;
		$this->preceptor_proxy_id = $preceptor_proxy_id;
		$this->preceptor_email = $preceptor_email;
		$this->status = $status;
		$this->unique_id = $unique_id;
		$this->notice_sent = $notice_sent;
	}
	
	public static function fromArray(array $arr) {
		return new Observership($arr['id'], $arr['student_id'], $arr['title'], $arr['site'], $arr['location'], $arr['preceptor_proxy_id'], $arr['preceptor_firstname'], $arr['preceptor_lastname'], $arr['start'], $arr['end'], $arr['preceptor_prefix'], $arr['preceptor_email'], $arr['status'], $arr['unique_id'], $arr['notice_sent']);
	}
	
	public function getID() {
		return $this->id;
	}
	
	public function getStudentID() {
		return $this->student_id;	
	}
	
	public function getUser() {
		return User::get($this->student_id);
	}

	public function getSite() {
		return $this->site;
	}
	
	public function getLocation () {
		return $this->location;
	}
	
	public function getTitle(){
		return $this->title;
	}
	
	public function getPreceptorFirstname() {
		if ($this->preceptor_proxy_id) {
			$preceptor = $this->getPreceptor();
			if ($preceptor) {
				return $preceptor->getFirstname();
			}
		} else {
			return $this->preceptor_firstname;	
		}
	}
	
	public function getPreceptorLastname() {
		if ($this->preceptor_proxy_id) {
			$preceptor = $this->getPreceptor();
			if ($preceptor) {
				return $preceptor->getLastname();
			}
		} else {
			return $this->preceptor_lastname;
		}
	}
	
	public function getPreceptorPrefix() {
		if ($this->preceptor_proxy_id) {
			$preceptor = $this->getPreceptor();
			if ($preceptor) {
				return $preceptor->getPrefix();
			}
		} else {
			return $this->preceptor_prefix;
		}
	}
	
	public function getPreceptor() {
		if ($this->preceptor_proxy_id) {
			return User::get($this->preceptor_proxy_id);
		}
	}
	
	public function getPreceptorEmail() {
		if ($this->preceptor_email) {
			return $this->preceptor_email;
		} else {
			$preceptor = $this->getPreceptor();
			if ($preceptor) {
				return $preceptor->getEmail();
			}
		}
	}
	
	public function getDetails() {
		$preceptor = trim(($this->getPreceptorPrefix() ? $this->getPreceptorPrefix() . " " : "") . $this->getPreceptorFirstname() . " " . $this->getPreceptorLastname());
		
		$elements = array();
		$elements[] = $this->title;
		$elements[] = $this->site . ", " . $this->location;
		$elements[] = $preceptor;
		$details = implode("\n", $elements);
		return $details;
	}
	
	public function getStart() {
		return $this->start;
	}
	
	public function getEnd() {
		if ($this->end) {
			return $this->end;
		} else {
			return $this->start;
		}
	}
	
	public function getStartDate() {
		return array(
			"d" => date("j", $this->start),
			"m" => date("n", $this->start),
			"y" => date("Y", $this->start)
		);
	}
	
	public function getEndDate() {
		if (!$this->end) {
			return $this->getStartDate();
		} else {
			return array(
				"d" => date("j", $this->end),
				"m" => date("n", $this->end),
				"y" => date("Y", $this->end)
			);
		}
	}
	
	public function getPeriod() {
		return formatDateRange($this->getStartDate(), $this->getEndDate()); 
	}
	
	public function getStatus() {
		return $this->status;
	}
	
	public function getNoticeSent() {
		return $this->notice_sent;
	}
	
	public function getUniqueID() {
		return $this->unique_id;
	}
	
	public static function get($id) {
		global $db;
		$query		= "SELECT * FROM `student_observerships` WHERE `id` = ".$db->qstr($id);
		$result = $db->getRow($query);
		if ($result) {
			
			$obs = Observership::fromArray($result);
			return $obs;
		}
	}
	
	public static function getByUniqueID($unique_id) {
		global $db;
		$query		= "SELECT * FROM `student_observerships` WHERE `unique_id` = ".$db->qstr($unique_id);
		$result = $db->getRow($query);
		if ($result) {
			$obs = Observership::fromArray($result);
			return $obs;
		}
	}

	public static function create(array $input_arr) {
		extract($input_arr);
		global $db;
		$query = "insert into `student_observerships` (`student_id`, `title`,`site`,`location`,`preceptor_proxy_id`,`preceptor_firstname`, `preceptor_lastname`, `start`, `end`, `preceptor_prefix`, `preceptor_email`, `status`, `unique_id`, `notice_sent`) value (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		if(!$db->Execute($query, array($user_id, $title, $site, $location, $preceptor_proxy_id, $preceptor_firstname, $preceptor_lastname, $start, $end, $preceptor_prefix, $preceptor_email, 'CONFIRMED', hash("sha256", uniqid("obs-", true)), uniqid()))) {
			add_error("Failed to create new Observership.");
			application_log("error", "Unable to update a student_observerships record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully added new Observership.");
			$insert_id = $db->Insert_ID();
			return self::get($insert_id); 
		}
	}
	
	public function delete() {
		global $db;
		$query = "DELETE FROM `student_observerships` where `id`=".$db->qstr($this->id);
		if(!$db->Execute($query)) {
			add_error("Failed to remove Observership from database.");
			application_log("error", "Unable to delete a student_observerships record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully removed Observership.");
		}		
	}
	
	public function update(array $input_arr) {
		extract($input_arr);
		global $db;
		$query = "update `student_observerships` set `title`=?, `site`=?,`location`=?,`preceptor_proxy_id`=?,`preceptor_firstname`=?, `preceptor_lastname`=?, `start`=?, `end`=?, `preceptor_prefix`=?, `preceptor_email`=?, `status`=?, `notice_sent`=? where `id`=?";
		if(!$db->Execute($query, array($title, $site, $location, $preceptor_proxy_id, $preceptor_firstname, $preceptor_lastname, $start, $end, $preceptor_prefix, $preceptor_email, $status, $notice_sent, $this->id))) {
			add_error("Failed to update Observership.");
			application_log("error", "Unable to update a student_observerships record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully updated Observership.");
			$insert_id = $db->Insert_ID();
			return self::get($insert_id); 
		}
	}
	
	public function compare($obs, $compare_by='start') {
		switch($compare_by) {
			case 'start':
			case 'end':
				return $this->$compare_by == $obs->$compare_by ? 0 : ( $this->$compare_by > $obs->$compare_by ? 1 : -1 );
				break;
			case 'title':
				return strcasecmp($this->$compare_by, $obs->$compare_by);
		}
	}
}