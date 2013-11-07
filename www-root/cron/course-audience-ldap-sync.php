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
 * @author Developer: Ryan Warner <ryan.warner@queensu.ca>
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

//easy way to run tests that don't alter data, just switch to false
$UPDATE = true;
//easy way to switch between testing and production, command doesn't use application log, cron does
$mode = 'cron'; //command,cron
$YEAR = date('Y',time());
//added black list to exclude any organisations that aren't being synced
//some meds courses seem to have ldap enabled, will need to disable it
$org_blacklist = array();

if($mode == "cron"){
	application_log("cron", "-- Beginning of course-audience-sync --");
}

$ldap = NewADOConnection("ldap");
$ldap->SetFetchMode(ADODB_FETCH_ASSOC);
$ldap->debug = false;

function fetchCustomInfo($date,$org_id){	
	switch($org_id){
		case 1:
			$app = 1;
			break;
	}
	$m = date('n',$date);
	switch(true){
		case $m < 4:
			return array("W","_1_",$app);
			break;
		case $m < 9:
			if($org_id == 5 && $m < 6){
				return array("Sp","_5_",$app);
			}
			return array("S","_5_",$app);
			break;
		case $m < 12:
			return array("F","_9_",$app);
			break;
	}
}

function progressMessage($msg,$mode = "cron",$display_cron = true) {
	if ($mode == "command") {
		echo $msg."\n";
	} elseif ($display_cron) {
		application_log($mode, $msg);
	}
}
//the -1209600 is to start loading course lists two weeks in advance
$query = "	SELECT a.`course_code`,a.`course_id`,a.`curriculum_type_id`,a.`organisation_id`, c.`start_date`,c.`cperiod_id`, a.`sync_ldap_courses`
			FROM `courses` a
			JOIN curriculum_lu_types b
			ON a.`curriculum_type_id` = b.`curriculum_type_id`
			JOIN `curriculum_periods` c
			ON b.`curriculum_type_id` = c.`curriculum_type_id`
			AND c.`active` = '1'
			AND UNIX_TIMESTAMP() BETWEEN (c.`start_date` - 1209600) AND c.`finish_date`
			WHERE a.`course_active` = '1'
			AND a.`sync_ldap` = '1'
			AND a.`organisation_id` NOT IN(".implode(",",$org_blacklist).")
			GROUP BY a.`course_id`";
$results = $db->GetAll($query);
if (!$results) {	
	progressMessage("There are no courses to sync.",$mode);
	exit;
}
foreach ($results as $course) {
	
	$query = "SELECT `group_id` FROM `groups` WHERE `group_type` = 'course_list' AND `group_value` = ".$db->qstr($course["course_id"])." AND `group_name` LIKE '%{$YEAR}' ORDER BY `group_id` DESC";
	$group_id = $db->GetOne($query);

	/**
	* This calls function above to implement organization specific logic
	* @todo move all this to the database along with other settings
	*/
	list($SUFFIX,$LDAP_CODE,$APP_ID) = fetchCustomInfo($course["start_date"],$course["organisation_id"]);
	progressMessage("Course Code: {$course["course_code"]} Suffix: {$SUFFIX} LDAP: {$LDAP_CODE}\n",$mode,false);
	$start_date = 0;
	$end_date = 0;
	$curriculum_period = 0;

	/**
	* Fetch curriculum period to make sure the correct course audience group is being worked with
	*/
	if ($course["curriculum_type_id"] != 0) {
		$now = time();
		$query = "SELECT `start_date`, `finish_date`,`cperiod_id` FROM `curriculum_periods` WHERE ".$db->qstr($now)." BETWEEN `start_date` AND `finish_date` AND `active` = '1' AND `curriculum_type_id` = ".$db->qstr($course["curriculum_type_id"]);
		$result = $db->GetRow($query);
		if ($result) {
			$start_date			= $result["start_date"];
			$end_date			= $result["finish_date"];
			$curriculum_period	= $result["cperiod_id"];
		} else {
			$query = "SELECT * FROM `curriculum_periods` WHERE `active` = '1' AND `curriculum_type_id` = ".$db->qstr($course["curriculum_type_id"])."ORDER BY `start_date` ASC LIMIT 1";
			$result = $db->GetRow($query);
			if ($result) {
				$start_date			= $result["start_date"];
				$end_date			= $result["finish_date"];
				$curriculum_period	= $result["cperiod_id"];
			}
		}
	}		


	/**
	* Fetch current audience that's attached to the course
	*/
	$query = "	SELECT a.`id`, a.`number`, b.`member_active`
				FROM `".AUTH_DATABASE."`.`user_data` AS a 
				JOIN `group_members` AS b	
				ON a.`id` = b.`proxy_id` 
				JOIN `groups` AS c 
				ON b.`group_id` = c.`group_id`
				WHERE c.`group_type` = 'course_list' 
				AND c.`group_value` = ".$db->qstr($course["course_id"])."
				AND b.`entrada_only` = 0
				AND c.`group_name` LIKE '%{$YEAR}'";
	$audience = $db->GetAll($query);
	$course_audience = array();
	if ($audience) {
		foreach ($audience as $key=>$audience_member) {
			$course_audience["id"][$key]		= $audience_member["id"];
			$course_audience["number"][$key]	= $audience_member["number"];
			if ($audience_member["member_active"] == 1) {
				$course_audience["active"][$key]	= $audience_member["number"];
			} else {
				$course_audience["inactive"][$key]	= $audience_member["number"];
			}
		}
		
		unset($audience);
	} else {
		$course_audience = false;
	}

	$community_audience = array();	
	$query = "	SELECT `community_id` FROM `community_courses` WHERE `course_id` = ".$db->qstr($course["course_id"]);
	$comm_id = $db->GetOne($query);
	if ($comm_id) {
		$msg = "The community for the course_id ".$course["course_id"]." is ".$comm_id.".";
		progressMessage($msg,$mode);
	} else {
		$msg = "There is no community for the course ".$course["course_id"].".";
		progressMessage($msg,$mode);
	}

	$course_codes = array();
	
	if (!empty($course["sync_ldap_courses"]) && !is_null($course["sync_ldap_courses"])) {
		$c_codes = explode(",", $course["sync_ldap_courses"]);
		foreach ($c_codes as $course_code) {
			$tmp_input = clean_input($course_code, array("trim", "alphanumeric"));
			if (!empty($tmp_input)) {
				$course_codes[] = strtoupper($tmp_input);
			}
		}
		if (empty($course_codes)) {
			$course_codes = $course["course_code"];
		}
	} else {
		$course_codes[] = $course["course_code"];
	}
	
	if (!empty($course_codes)) {
		foreach ($course_codes as $code) {
			//create LDAP connection
			if ($ldap->Connect(LDAP_HOST, LDAP_SEARCH_DN, LDAP_SEARCH_DN_PASS, LDAP_GROUPS_BASE_DN)) {	
				//get the course information, in particular the list of unique members

				$course_code = str_replace($SUFFIX,"",$code);
				$course_code_base = clean_input($course_code, "alpha")."_".clean_input($course_code, "numeric");
				$search_query = "cn=".$course_code_base."*{$LDAP_CODE}*";

				progressMessage("Querying LDAP server with query: {$search_query}\n",$mode);

				/**
				* Fetch course from LDAP server
				*/
				$results = $ldap->GetAll($search_query);
				if ($results) {
					$ldap->Close();				
					
					/**
					* Find out if group exists for this course for this year and make the group if it doesn't yet exist
					* @todo find a better way for this that doesn't force the "Class List YYYY" naming convention, should be in settings along with
					* changes mentioned above
					*/
					if (!$group_id) {
						progressMessage("No group exists will be creating one now.\n",$mode,false);
						if ($UPDATE) {
							progressMessage("Deactivating old groups for course.\n",$mode,false);
							$db->AutoExecute("groups", array("group_active"=>0), "UPDATE","`group_type` = 'course_list' AND `group_value` = ".$db->qstr($course["course_id"])." AND `group_name` NOT LIKE '%{$YEAR}'");
						}
						$values = 	array(
										"group_name"	=> $course["course_code"]." Class List {$YEAR}",
										"group_type"	=> "course_list",
										"group_value"	=> (int) $course["course_id"],
										"start_date"	=> $start_date,
										"expire_date"	=> $end_date,
										"group_active"	=> "1",
										"updated_date"	=> time(),
										"updated_by"	=> "1"
									);
						if ($UPDATE) {
							if ($db->AutoExecute("groups", $values, "INSERT") && ($group_id = $db->Insert_Id())) {
								$values						= array();
								$values["group_id"]			= $group_id;
								$values["organisation_id"]	= $course["organisation_id"];
								$values["updated_date"]		= time();
								$values["updated_by"]		= "1";

								$db->AutoExecute("group_organisations", $values, "INSERT");
							}
						}else{
							$group_id = true;
						}
					}
					
					$uniUids = array();
					progressMessage("Response recieved",$mode,false);
					foreach ($results as $result) {
						progressMessage((isset($result["uniqueMember"])?count($result["uniqueMember"]):0)." members found for {$course["course_code"]}\n",$mode);
						//make new connection with the base set to people to get user information

						$now = time();

						if ($group_id) {
							if ($UPDATE) {
								$query = "	SELECT * FROM `course_audience` 
											WHERE `course_id` = ".$db->qstr($course["course_id"])." 
											AND `audience_type` = 'group_id' 
											AND `audience_value` = ".$db->qstr($group_id);
								/**
								* If no course audience record exists for this course and group combination, add group as audience
								*/
								if (!$db->GetAll($query)) {
									$values = 	array(
													"course_id" => $course["course_id"],
													"audience_type" => "group_id",
													"audience_value" => $group_id,
													"enroll_finish" => $end_date,
													"audience_active" => "1",
													"cperiod_id" => $course["cperiod_id"]
												);
									if ($UPDATE) {
										$db->AutoExecute("course_audience",$values,"INSERT");
									}
								}
							}

							/**
							* Build array of relavent information for each student to be used below (just QueensuCaUniUid from here)
							*/
							if ($result["uniqueMember"] && is_array($result["uniqueMember"]) && count($result["uniqueMember"])){			
								//for each user in the unique member list get their queensuCaPkey
								progressMessage("Looping through users to add them to list of users.\n",$mode,false);
								foreach ($result["uniqueMember"] as $key=>$member) {
									$member_path = explode(",", $member);
									$uniUid = trim(str_ireplace("QueensuCaUniUid=", "", $member_path[0]));
									$uniUids[] = $uniUid;
								}

							} else {
								progressMessage("No members found for course ".$course["cource_code"].".",$mode);
							}
						} else {
							progressMessage("No group_id for course ".$course["course_code"].".",$mode);
						}
					}

					progressMessage(count($uniUids)?"There are users to be enrolled for course\n":"List of users is empty\n",$mode,false);
					$num_to_add = 0;
					progressMessage("Students:\n\n",$mode,false);

					/**
					* Loop through each student returned from the uniqueMember query done against the course. This is the enrolment from LDAP.
					*/
					foreach ($uniUids as $key=>$uniUid) {	
						if ($ldap->Connect(LDAP_HOST, LDAP_SEARCH_DN,LDAP_SEARCH_DN_PASS, LDAP_PEOPLE_BASE_DN)) {
							/**
							* There should always be a result here, if not the LDAP server has a student enrolled with no LDAP entry
							*/ 					
							if (($user_result = $ldap->GetRow("QueensuCaUniUid=".$uniUid))) {

								$pKey = (int) str_replace("S", "", $user_result["queensuCaPKey"]);
								if ($pKey != 0 || !empty($pKey)) {
									$query = "	SELECT `id` 
												FROM `".AUTH_DATABASE."`.`user_data` 
												WHERE `number` = ".$db->qstr($pKey);
									/**
									* If there is a record, the student is created inside Entrada, no result means there is no linked Entrada account
									* Create Entrada account for users missing an account
									*/
									$id = $db->GetOne($query);
									if (!$id) {	
										/**
										* Two conventions exist for names:
										* New: sn contains user lastname and givenName contains the user's last name
										* Old: old full name stored in cn field, needs to be split
										* @todo + @info: the old convention causes issues for users with given names that contain spaces by assuming the first name
										* is just the section, and the last name is all other sections. This needs to be addressed if there's a real solution. 
										* Most students should have had their LDAP accounts updated to the new convention so this shouldn't be an issue much longer
										* Example: Bobby Jo Smith will result in Firstname: Bobby, Lastname: Jo Smith
										*/					
										if(isset($user_result["sn"]) && isset($user_result["givenName"]) && $user_result["sn"] && $user_result["givenName"]){
											$names[0] = $user_result["givenName"];
											$names[1] = $user_result["sn"];
										}else{
											$names = explode(" ",$user_result["cn"]);	
										}							
										$GRAD = $YEAR+4;
										$student = array(	"number"			=> $pKey,
															"username"			=> strtolower($uniUid),
															"password"			=> md5(generate_password(8)),
															"organisation_id"	=> $course["organisation_id"],
															"firstname"			=> trim($names[0]),
															"lastname"			=> trim($names[1]),
															"prefix"			=> "",
															"email"				=> isset($user_result["mail"])?$user_result["mail"]:strtolower($uniUid)."@queensu.ca",
															"email_alt"			=> "",
															"email_updated"		=> time(),
															"telephone"			=> "",
															"fax"				=> "",
															"address"			=> "",
															"city"				=> "Kingston",
															"postcode"			=> "K7L 3N6",
															"country"			=> "",
															"country_id"		=> "39",
															"province"			=> "",
															"province_id"		=> "9",
															"notes"				=> "",
															"privacy_level"		=> "0",
															"notifications"		=> "0",
															"entry_year"		=> $YEAR,
															"grad_year"			=> $GRAD,
															"gender"			=> "0",
															"clinical"			=> "0",
															"updated_date"		=> time(),
															"updated_by"		=> "1"
													);
										progressMessage("Student number: {$student["number"]} Student Firstname: {$student["firstname"]} Student Lastname: {$student["lastname"]}\n",$mode,false);
										if ($UPDATE) {
											$response = $db->AutoExecute("`".AUTH_DATABASE."`.`user_data`",$student,"INSERT");
											$id = $db->Insert_Id();
											/**
											* Create data record, followed by access record for new user and default to student permissions
											*/ 

											if($response && is_int($id)){
												$access = array(	"user_id"			=> $id,
																	"app_id"			=> $APP_ID,//insert app id of dbms here
																	"organisation_id"	=> $course["organisation_id"],
																	"account_active"	=> "true",
																	"access_starts"		=> time(),
																	"access_expires"	=> "0",
																	"last_login"		=> "0",
																	"last_ip"			=> "",
																	"role"				=> $GRAD,
																	"group"				=> "student",
																	"extras"			=> "",
																	"private_hash"		=> generate_hash(32),
																	"notes"				=> ""
															);
												if ($UPDATE) {
													$db->AutoExecute("`".AUTH_DATABASE."`.`user_access`",$access,"INSERT");		
												}
												progressMessage($uniUid." was successfully created.\n",$mode,false);
												$num_to_add++;
											} else {
												$id = false;
											}
										}else{
											$id = true;
										}							
										//application_log( 'Student found in course on LDAP server who is not registered in Entrada.';
									}


									if ($id) {
										//They have an account, but make sure they have an account with the application the course is part of as well, if not make one
										$query = " 	SELECT * FROM `".AUTH_DATABASE."`.`user_access`
													WHERE `user_id` = ".$db->qstr($id)." AND `organisation_id` = ".$db->qstr($course["organisation_id"]);

										$access_rec = $db->GetRow($query);

										/**
										* If user has no access record for the course's organisation, create one and default to student permissions
										*/
										if (!$access_rec) {
											if($id != 1){
												progressMessage("User ID {$id} has an account, but needs access added for organisation {$course["organisation_id"]}\n",$mode,false);
											}
											if ($UPDATE) {
												$GRAD = $YEAR+4;
												$access = array(	"user_id"			=> $id,
																	"app_id"			=> $APP_ID,//insert app id of dbms here
																	"organisation_id"	=> $course["organisation_id"],
																	"account_active"	=> "true",
																	"access_starts"		=> time(),
																	"access_expires"	=> "0",
																	"last_login"		=> "0",
																	"last_ip"			=> "",
																	"role"				=> $GRAD,
																	"group"				=> "student",
																	"extras"			=> "",
																	"private_hash"		=> generate_hash(32),
																	"notes"				=> ""
													);
												$db->AutoExecute("`".AUTH_DATABASE."`.`user_access`",$access,"INSERT");		
											}
										}

										if (!$UPDATE) {
											progressMessage("Skipped adding user {$pKey} to {$course["course_code"]} due to test mode.\n",$mode,false);
											continue;
										}

										$query = "	SELECT * FROM `group_members` 
												WHERE `proxy_id` = ".$db->qstr($id)."
												AND `group_id` = ".$db->qstr($group_id);
										$user_result = $db->GetAll($query);
										/**
										* If no result, insert into the course audience, otherwise remove from array so they won't be removed later
										*/
										if (!$user_result) {
											//insert into audience
											$values = 	array(
															"group_id"		=> $group_id,
															"proxy_id"		=> $id,
															"start_date"	=> $start_date,
															"expire_date"	=> $end_date,
															"member_active" => "1",
															"entrada_only"	=> "0",
															"updated_date"	=> time(),
															"updated_by"	=> "1"
														);
											if ($UPDATE) {
												if ($db->AutoExecute("group_members",$values,"INSERT")) {												
													progressMessage($id." was successfully registered into course [".$course["course_code"]."].", $mode);
												} else {
													progressMessage("Error occurred while adding [".$id."] to the course.",$mode);
												}
											}
										} elseif ($course_audience && isset($course_audience["number"]) && is_array($course_audience["number"])) {
											if (in_array($pKey, $course_audience["inactive"])) {
												$query = "UPDATE `group_members` SET `finish_date` = '0', `member_active` = '1', `updated_date` = ".$db->qstr(time()).", `updated_by` = '1' WHERE `group_id` = ".$db->qstr($group_id)." AND `proxy_id` = ".$db->qstr($id);
												if ($db->Execute($query)) {
													$key = array_search($pKey, $course_audience["number"]);
													unset($course_audience["number"][$key]);
													unset($course_audience["id"][$key]);
												}
											} else {
												$key = array_search($pKey, $course_audience["number"]);
												if ($key !== false) {
													unset($course_audience["number"][$key]);
													unset($course_audience["id"][$key]);

													//progressMessage($pKey." was already a course member in the system and the key was unset.",$mode);
												} else {
													//progressMessage($pKey." was already a course member in the system.",$mode);
												}
											}
										}

									}else{
										progressMessage("The user wasn't created properly.\n",$mode,false);
									}
								} else {
									application_log("error", "LDAP user data error [".serialize($user_result)."]");
								}
							} else {
								progressMessage("LDAP records out of date, inform LDAP admin.",$mode);
							}
							$ldap->Close();
						}
					}
					
				} else {
					$msg = "No results from LDAP server for course ".$course["course_code"]." using query ".$search_query.". Check that course code is valid.";
					progressMessage($msg."\n",$mode);
				}
			} else {			
				$msg = "Could not connect to get course information.";
				progressMessage($msg."\n",$mode,false);
				if($mode == "cron"){
					application_log("cron",$ldap->ErrorMsg());			
				}
			}	
		}
		
		/**
		* If the course audience array has values, it means somone used to be enrolled and no longer is
		* Foreach member
		* not in the group
		* @todo change course websites to default to course_audience for permissions and have it fall back to community_members
		*/
		progressMessage("\n\nNum to add:".($num_to_add/2)."\n",$mode,false);
		if (isset($course_audience) && $course_audience) {
			$end_stamp = time();
			$expired_audience = array();

			//loop through and cast each just to be sure
			foreach ($course_audience["id"] as $key=>$audience_member) {
				$expired_audience[] = (int) $audience_member;
			}

			if ($UPDATE && !empty($expired_audience)) {
				$values						= array();
				$values["finish_date"]		= $end_stamp;
				$values["member_active"]	= 0;
				$values["updated_date"]		= $end_stamp;
				$expired_audience_string	= implode(",",$expired_audience);		
				$where = "`group_id` = ".$db->qstr($group_id)." AND `entrada_only` = '0' AND `proxy_id` IN(".$expired_audience_string.")";
				if ($db->AutoExecute("group_members",$values,"UPDATE",$where)) {
					$mode = $mode == "cron" ? "success" : $mode;
					progressMessage($course_audience["number"][$key]." was successfully removed from  the group ".$group_id.".",$mode);
				} else {
					progressMessage("Error occurred while removing ".$pKey." from the group list ".$group_id.".", $mode);
				}
			}

		}
		/**
		* If Community and Group (There should never not be a group at this stage), fetch all
		* members of the group, and add them to the community, then disable any non-admins who are
		* not in the group
		* @todo change course websites to default to course_audience for permissions and have it fall back to community_members
		*/
		if ($comm_id && $group_id) {
			$query = "	SELECT * FROM `group_members` 
						WHERE `group_id` = ".$db->qstr($group_id)." 
						AND `member_active` = '1' 
						AND UNIX_TIMESTAMP() > (`start_date` - 1209600)
						AND (
							`finish_date` = 0 
							OR UNIX_TIMESTAMP() < `finish_date`
						)
						GROUP BY `proxy_id` ";
			$members = $db->GetAll($query);
			$current_list = array();
			$group_count = count($members);
			$iteration_count = 0;
			foreach($members as $member){
				$iteration_count++;
				$current_list[] = (int)$member["proxy_id"];
				$query = "SELECT * FROM `community_members` WHERE `community_id` = ".$db->qstr($comm_id)." AND `proxy_id` = ".$db->qstr($member["proxy_id"])." AND `member_active` = 1";
				if (!$row = $db->GetRow($query)) {
					$values = 	array(
									"community_id" => $comm_id,
									"proxy_id" => (int)$member["proxy_id"],
									"member_active" => "1",
									"member_joined" => time(),
									"member_acl" => "0"
								);
					if ($UPDATE && $db->AutoExecute("community_members",$values,"INSERT")) {
						progressMessage("Proxy ".$member["proxy_id"]." was successfully registered into course community  for [".$course["course_code"]."].",$mode);
					} else {
						progressMessage("Problem with insert for proxy ".$member["proxy_id"]." when adding them to course community for [".$course["course_code"]."].",$mode);
					}
				}
			}

			$query = "	SELECT `proxy_id` 
						FROM `community_members`, ". AUTH_DATABASE .".`user_access` 
						WHERE `community_id` = ".$db->qstr($comm_id)."
						AND `member_active` = '1' 
						AND `proxy_id` = `user_id`
						AND `organisation_id` =". $db->qstr($course["organisation_id"]) ."
						AND (`group` = 'medtech' OR `group` = 'faculty' OR `group` = 'staff')";
			$results = $db->GetAll($query);
			
			foreach ($results as $community_faculty) {
				$current_list[] =  (int) $community_faculty["proxy_id"];
			}
			$current_list_string = implode(",", $current_list);
			$data = array('member_active'=>0);
			$where = "`community_id` = ".$db->qstr($comm_id)." AND `member_active` = '1' AND `member_acl` = '0' AND `proxy_id` NOT IN(".$current_list_string.")";
			if ($UPDATE && $db->AutoExecute("community_members",$data,"UPDATE",$where)) {
				progressMessage("Successfully removed all non-active members from the course website for [".$course["course_code"]."].",$mode);
			}				

			$query = "  SELECT COUNT(*) FROM `community_members`
						WHERE `community_id` = ".$db->qstr($comm_id)."
						AND `member_acl` = '0'
						AND `member_active` = '1'";
			$community_count = $db->GetOne($query);
			$warning = $community_count != $group_count?true:false;
			progressMessage("Course: [".$course["course_code"]."] Enrollment Iterations: [".$iteration_count."] Group: [".$group_id."] Count: [".$group_count."] Community [".$comm_id."] Count: [".$community_count."].".($warning?"[WARNING] MISMATCHED USER COUNT":"")."\n",$mode);
		}
		
	} else {
		application_log("cron", "No course codes associated with course [".$course["course_code"]."].");
	}
	progressMessage("\n\n\n\n",$mode);
}

if($mode == "cron"){
	application_log("cron", "-- End of course-audience-sync --");
}