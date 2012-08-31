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
 * Sync's LDAP server with class_list in groups table.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Brandon Thorn <bt37@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 * 
*/

@set_time_limit(0);
@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    dirname(__FILE__) . "/../core/includes",
    dirname(__FILE__) . "/../core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

application_log("cron", "-- Beginning of course-audience-sync --");

$ldap = NewADOConnection("ldap");
$ldap->SetFetchMode(ADODB_FETCH_ASSOC);
$ldap->debug = false;

/**
 * NOTE: This script is made for the Winter 2011-2012 term. To change the terms do the following:
 * 1 - Change the curriculum_type_id to the appropriate ID
 * 2 - Change the 'W' on line 132 to whatever term you're in (look at the suffix on some of the of the Course Codes)
 * 3 - Change the '_1_' on line 137 to the term code (number of the month the term starts in)
 */
$query = "	SELECT `course_code`,`course_id`,`curriculum_type_id`,`organisation_id` 
			FROM `courses` 
			WHERE `course_active` = '1'
			AND `sync_ldap` = '1'
			AND `curriculum_type_id` = '11'";
$results = $db->GetAll($query);
if ($results) {
	foreach ($results as $course) {
		$start_date = 0;
		$end_date = 0;
		$curriculum_period = 0;

		if ($course["curriculum_type_id"] != 0) {
			$now = time();
			$query = "SELECT `start_date`, `finish_date`,`cperiod_id` FROM `curriculum_periods` WHERE ".$db->qstr($now)." BETWEEN `start_date` AND `finish_date` AND `active` = 1 AND `curriculum_type_id` = ".$db->qstr($course["curriculum_type_id"]);
			if ($result = $db->GetRow($query)) {
				$start_date = $result["start_date"];
				$end_date = $result["finish_date"];
				$curriculum_period = $result["cperiod_id"];
			} else {
				$query = "SELECT * FROM `curriculum_periods` WHERE `active` = 1 AND `curriculum_type_id` = ".$db->qstr($course["curriculum_type_id"])."ORDER BY `start_date` ASC LIMIT 1";
				if ($result = $db->GetRow($query)) {
					$start_date = $result["start_date"];
					$end_date = $result["finish_date"];
					$curriculum_period = $result["cperiod_id"];
				}
			}
		}		

		$query = "	SELECT a.`id`, a.`number` 
					FROM `".AUTH_DATABASE."`.`user_data` AS a 
					JOIN `group_members` AS b	
					ON a.`id` = b.`proxy_id` 
					JOIN `groups` AS c 
					ON b.`group_id` = c.`group_id`
					WHERE c.`group_type` = 'course_list' 
					AND c.`group_value` = ".$db->qstr($course["course_id"])."
					AND b.`entrada_only` = 0
					AND b.`member_active` = 1";
		$audience = $db->GetAll($query);
		$course_audience = array();
		if ($audience) {
			foreach ($audience as $key=>$audience_member) {
				$course_audience["id"][$key] = $audience_member["id"];
				$course_audience["number"][$key] = $audience_member["number"];
			}
			
			unset($audience);
		} else {
			$course_audience = false;
		}
		
		
		$query = "	SELECT `community_id` FROM `community_courses` WHERE `course_id` = ".$db->qstr($course["course_id"]);
		$comm_id = $db->GetOne($query);
		if ($comm_id) {
			application_log("cron", "The community for the course ".$course["course_id"]." is ".$comm_id.".");
		
			$community_audience = array();
			
			$query = "SELECT `proxy_id` FROM `community_members` WHERE `community_id` = ".$db->qstr($comm_id)." AND `member_active` = 1 AND `member_acl` = 0";
			$audience = $db->GetAll($query);
			if ($audience) {
				foreach ($audience as $key=>$audience_member) {
					$community_audience["id"][$key] = $audience_member["proxy_id"];
				}
				unset($audience);
			} else {
				$community_audience = false;
			}
		} else {
			application_log("cron", "There is no community for the course ".$course["course_id"].".");
		}

		//create LDAP connection
		
		if ($ldap->Connect(LDAP_HOST, LDAP_SEARCH_DN,LDAP_SEARCH_DN_PASS, LDAP_GROUPS_BASE_DN)) {
			//get the course information, in particular the list of unique members
			/**
			 * Change from W depending on term
			 */
			$course_code = str_replace("W","",$course["course_code"]);
			$course_code_base = clean_input($course_code, "alpha")."_".clean_input($course_code, "numeric");
			/**
			 * Change _1_ to appropriate term ID (matches month number the term starts in)
			 */
			if (($results = $ldap->GetAll("cn=".$course_code_base."*_1_*"))) {
				$ldap->Close();
					echo "<strong>".$course["course_code"]."</strong><pre>".print_r($results, true)."</pre>\n<br/>";
				$uniUids = array();
				foreach ($results as $result) {
					//make new connection with the base set to people to get user information

					$query = "SELECT `group_id` FROM `groups` WHERE `group_type` = 'course_list' AND `group_value` = ".$db->qstr($course["course_id"]);
					$group_id = $db->GetOne($query);
					$now = time();

					if (!$group_id && count($result["uniqueMember"])) {

						$values = array();
						$values["group_name"] = $course["course_code"]." Class List";
						$values["group_type"] = "course_list";
						$values["group_value"] = (int) $course["course_id"];
						$values["start_date"] = $start_date;
						$values["expire_date"] = $end_date;
						$values["group_active"] = "1";
						$values["updated_date"] = time();
						$values["updated_by"] = "1";
						if ($db->AutoExecute("groups", $values, "INSERT") && ($group_id = $db->Insert_Id())) {
							$values = array();
							$values["group_id"] = $group_id;
							$values["organisation_id"] = $course["organisation_id"];
							$values["updated_date"] = time();
							$values["updated_by"] = "1";

							$db->AutoExecute("group_organisations", $values, "INSERT");
						}
					}

					if ($group_id) {
						$query = "	SELECT * FROM `course_audience` 
									WHERE `course_id` = ".$db->qstr($course["course_id"])." 
									AND `audience_type` = 'group_id' 
									AND `audience_value` = ".$db->qstr($group_id);
						if (!$db->GetAll($query)) {
							$values = array();
							$values["course_id"] = $course["course_id"];
							$values["audience_type"] = "group_id";
							$values["audience_value"] = $group_id;
							$values["enroll_finish"] = $end_date;
							$values["audience_active"] = "1";
							$values["cperiod_id"] = $curriculum_period;
							$db->AutoExecute("course_audience",$values,"INSERT");
						}

						if ($result["uniqueMember"] && is_array($result["uniqueMember"]) && count($result["uniqueMember"])){			
							//for each user in the unique member list get their queensuCaPkey
							foreach ($result["uniqueMember"] as $key=>$member) {
								$member_path = explode(",", $member);
								$uniUid = trim(str_ireplace("QueensuCaUniUid=", "", $member_path[0]));
								$uniUids[] = $uniUid;
							}
							
						} else {
							application_log("cron", "No members found for course ".$course["cource_code"].".");
						}
					} else {
						application_log("cron", "No group_id for course ".$course["course_code"].".");
					}
				}
				
				print_r($uniUids);
				echo "<br/>\n";
				
				foreach ($uniUids as $key=>$uniUid) {	
					if ($ldap->Connect(LDAP_HOST, LDAP_SEARCH_DN,LDAP_SEARCH_DN_PASS, LDAP_PEOPLE_BASE_DN)) {
						//there should always be a result, if not the LDAP server has a student enrolled with no LDAP entry
						if (($user_result = $ldap->GetRow("QueensuCaUniUid=".$uniUid."*"))) {
							
							$pKey = (int) str_replace("S", "", $user_result["queensuCaPKey"]);
							$query = "	SELECT `id` 
										FROM `".AUTH_DATABASE."`.`user_data` 
										WHERE `number` = ".$db->qstr($pKey);
							//if there is a record, the student is created inside Entrada, no result means there is no linked Entrada account
							if ($id = $db->GetOne($query)) {
								$query = "	SELECT * FROM `group_members` 
											WHERE `proxy_id` = ".$db->qstr($id)."
											AND `group_id` = ".$db->qstr($group_id);
	
								//if no result, insert into the course audience, otherwise remove from array
								if (!$user_result=$db->GetAll($query)) {
									//insert into audience
									$values = array();
									$values["group_id"] = $group_id;
									$values["proxy_id"] = $id;
									$values["start_date"] = $start_date;
									$values["expire_date"] = $end_date;
									$values["member_active"] = "1";
									$values["entrada_only"] = "0";
									$values["updated_date"] = time();
									$values["updated_by"] = "1";
									if ($db->AutoExecute("group_members",$values,"INSERT")) {												
										application_log("cron", $id." was successfully registered into course [".$course["course_code"]."].");
									} else {
										application_log("cron", "Error occurred while adding [".$id."] to the course.");
									}
								} elseif ($course_audience) {
									$key = array_search($pKey, $course_audience["number"]);
									if ($key !== false) {
										unset($course_audience["number"][$key]);
										unset($course_audience["id"][$key]);
										
										application_log("cron", $pKey." was already a course member in the system amd the key was unset.");
									}
									application_log("cron", $pKey." was already a course member in the system.");
								}
	
								if ($comm_id) {
									$query = "SELECT * FROM `community_members` WHERE `community_id` = ".$db->qstr($comm_id)." AND `proxy_id` = ".$db->qstr($id)." AND `member_active` = 1";
									if (!$row = $db->GetRow($query)) {
										$values = array();
										$values["community_id"] = $comm_id;
										$values["proxy_id"] = $id;
										$values["member_active"] = "1";
										$values["member_joined"] = time();
										$values["member_acl"] = "0";
										if ($db->AutoExecute("community_members",$values,"INSERT")) {
											application_log("cron", $pKey." was successfully registered into course website [".$course["course_code"]."].");
										}
									} else {
										$key = array_search($id,$community_audience["id"]);
										if ($key !== false) {
											unset($community_audience["id"][$key]);
											application_log("cron", $pKey." was already a member of the course website [".$course["course_code"]."].");
										}
									}
								}									
							} else {
								//application_log( 'Student found in course on LDAP server who is not registered in Entrada.';
							}
	
						} else {
							application_log("cron", "LDAP records out of date, inform LDAP admin.");
						}
						$ldap->Close();
					}
				}
				if (isset($course_audience) && $course_audience) {
					$end_stamp = time();
					foreach ($course_audience["id"] as $key=>$audience_member) {
						$values = array();
						$values["finish_date"] = $end_stamp;
						$values["member_active"] = 0;
						$values["updated_date"] = $end_stamp;
						if ($db->AutoExecute("group_members",$values,"UPDATE","`group_id` = ".$db->qstr($group_id)." AND `proxy_id` = ".$db->qstr($audience_member))) {
							application_log("success",$course_audience["number"][$key]." was successfully removed from  the group ".$group_id.".");
						} else {
							application_log("cron", "Error occurred while removing ".$pKey." from the group list ".$group_id.".");
						}
					}					
				}
				if ($comm_id) {
					if (isset($community_audience) && $community_audience) {
						$end_stamp = time();
						foreach ($community_audience["id"] as $key=>$audience_member) {
							$values = array();
							$values["member_active"] = 0;
							if ($db->AutoExecute("community_members",$values,"UPDATE","`community_id` = ".$db->qstr($comm_id)." AND `proxy_id` = ".$db->qstr($audience_member)." AND `member_active` = 1")) {
								application_log("success",$audience_member." was successfully removed from the  course community ".$comm_id.".");
							} else {
								application_log("cron", "Error occurred while removing ".$pKey." from the community ".$comm_id.".");
							}
						}					
					}
				}

			} else {
				application_log("cron", "No results from LDAP server for course ".$course["course_code"].". Check that course code is valid.");
			}
		} else {			
			application_log("cron", "Could not connect to get course information.");
			application_log("cron",$ldap->ErrorMsg());
		}
	}
} else {
	application_log("cron", "No courses found in system.");
}

application_log("cron", "-- End of course-audience-sync --");
