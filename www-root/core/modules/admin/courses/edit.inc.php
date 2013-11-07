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
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_COURSES"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("course", "update", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	if ($COURSE_ID) {
		$query = "	SELECT * FROM `courses`
					WHERE `course_id` = ".$db->qstr($COURSE_ID)."
					AND `course_active` = '1'";
		$course_details	= $db->GetRow($query);
		if ($course_details && $ENTRADA_ACL->amIAllowed(new CourseResource($course_details["course_id"], $course_details["organisation_id"]), "update")) {
			$HEAD[] = "<script type=\"text/javascript\">var SITE_URL = '".ENTRADA_URL."';</script>";
			$HEAD[] = "<script type=\"text/javascript\">var DELETE_IMAGE_URL = '".ENTRADA_URL."/images/minus-sign.png';</script>";
			$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/picklist.js\"></script>\n";
			$HEAD[]	= "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
			$HEAD[]	= "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/objectives.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
			$HEAD[]	= "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/objectives_course.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";

			$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("section" => "edit", "id" => $COURSE_ID, "step" => false)), "title" => "Editing " . $module_singular_name);

			/**
			* Fetch the Clinical Presentation details.
			*/
			$clinical_presentations_list	= array();
			$clinical_presentations			= array();

			$results = fetch_clinical_presentations();
			if ($results) {
				foreach ($results as $result) {
					$clinical_presentations_list[$result["objective_id"]] = $result["objective_name"];
				}
			} else {
				$clinical_presentations_list = false;
			}

			// if (isset($_POST["clinical_presentations_submit"]) && $_POST["clinical_presentations_submit"]) {
			if ((isset($_POST["clinical_presentations"])) && (is_array($_POST["clinical_presentations"])) && (count($_POST["clinical_presentations"]))) {
				foreach ($_POST["clinical_presentations"] as $objective_id) {
					if ($objective_id = clean_input($objective_id, array("trim", "int"))) {
							$query	= "SELECT a.`objective_id`
										FROM `global_lu_objectives` AS a
										JOIN `objective_organisation` AS b
										WHERE a.`objective_id` = ".$db->qstr($objective_id)."
										AND a.`objective_active` = '1'
										AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
										ORDER BY a.`objective_order` ASC";
						$result	= $db->GetRow($query);
						if ($result) {
							$clinical_presentations[$objective_id] = $clinical_presentations_list[$objective_id];
						}
					}
				}
			} else {
				$clinical_presentations = array();
			}
			// } else {
			// 	$query		= "SELECT `objective_id` FROM `course_objectives` WHERE `objective_type` = 'event' AND `course_id` = ".$db->qstr($COURSE_ID);
			// 	$results	= $db->GetAll($query);
			// 	if ($results) {
			// 		foreach ($results as $result) {
			// 			if (!empty($clinical_presentations_list[$result["objective_id"]])) {
			// 			$clinical_presentations[$result["objective_id"]] = $clinical_presentations_list[$result["objective_id"]];
			// 			}
			// 		}
			// 	}
			// }

			courses_subnavigation($course_details,"details");
			echo "<h1>Editing " . $module_singular_name  . "</h1>\n";

			$PROCESSED["permission"] = $course_details["permission"];
			$PROCESSED["sync_ldap"] = $course_details["sync_ldap"];
			$PROCESSED["sync_ldap_courses"] = $course_details["sync_ldap_courses"];

			// Error Checking
			switch($STEP) {
				case 2 :
					if ($ENTRADA_ACL->amIAllowed(new CourseResource($COURSE_ID, $ENTRADA_USER->getActiveOrganisation()), "update")) {
						$PROCESSED["organisation_id"] = $ENTRADA_USER->getActiveOrganisation();
					} else {
						add_error("You do not have permission to update a course for this organisation. This error has been logged and will be investigated.");
						application_log("error", "Proxy id [".$_SESSION['details']['proxy_id']."] tried to create a course within an organisation [".$ENTRADA_USER->getActiveOrganisation()."] they didn't have permissions on. ");
					}

					/**
					 * Non-required field "curriculum_type_id" / Curriculum Category
					 */
					if ((isset($_POST["curriculum_type_id"])) && ($curriculum_type_id = clean_input($_POST["curriculum_type_id"], array("int")))) {
						$PROCESSED["curriculum_type_id"] = $curriculum_type_id;
					} else {
						$PROCESSED["curriculum_type_id"] = 0;
					}

					/**
					 * Required field "course_name" / Course Name.
					 */
					if ((isset($_POST["course_name"])) && ($course_name = clean_input($_POST["course_name"], array("notags", "trim")))) {
						$PROCESSED["course_name"] = $course_name;
					} else {
						add_error("The <strong>" . $module_singular_name . " Name</strong> field is required.");
					}

					/**
					 * Non-required field "course_code" / Course Code.
					 */
					if ((isset($_POST["course_code"])) && ($course_code = clean_input($_POST["course_code"], array("notags", "trim")))) {
						$PROCESSED["course_code"] = $course_code;
					} else {
						add_error("The <strong>" . $module_singular_name . " Code</strong> field is required and must be provided.");
					}

					/**
					 * Check to see if notifications are enabled or not for events in this course.
					 */
					if ((isset($_POST["notifications"])) && (!clean_input($_POST["notifications"], "int"))) {
						$PROCESSED["notifications"] = 0;
					} else {
						$PROCESSED["notifications"] = 1;
					}

					/**
			 		 * Check to see if whether this course is open or closed.
					 */
					if ((isset($_POST["permission"])) && ($_POST["permission"] == "closed")) {
						$PROCESSED["permission"] = "closed";
					} else {
						$PROCESSED["permission"] = "open";
					}

					/**
					 * Check to see if this course audience should syncronize with LDAP or not.
					 */
					$PROCESSED["sync_ldap_courses"] = "";
					if ((isset($_POST["sync_ldap"])) && ($_POST["sync_ldap"] == "1")) {
						$PROCESSED["sync_ldap"] = 1;
					} else {
						$PROCESSED["sync_ldap"] = 0;
					}
					
					/*
					 * Process the ldap sync course list.
					 */
					$PROCESSED["sync_ldap_courses"] = "";
					$clean_ldap_course_codes = array();
					if (isset($_POST["sync_ldap_courses"]) && !empty($_POST["sync_ldap_courses"])) {
						$sync_ldap_courses = explode(",", $_POST["sync_ldap_courses"]);
						foreach ($sync_ldap_courses as $course_code) {
							if ($tmp_input = clean_input($course_code, array("trim", "striptags", "alphanumeric"))) {
								if (!in_array(strtoupper($tmp_input), $clean_ldap_course_codes)) {
									$clean_ldap_course_codes[] = strtoupper($tmp_input);
								}
							}
						}
						if (isset($clean_ldap_course_codes) && !empty($clean_ldap_course_codes)) {
							$PROCESSED["sync_ldap_courses"] = implode(", ", $clean_ldap_course_codes);
						}
					}
					
					if (empty($PROCESSED["sync_ldap_courses"]) && $PROCESSED["sync_ldap"] != 0) {
						add_error("The LDAP synchronization course list can not be empty.");
					}
					
					/**
					 * Non-required field "course_directors" / Course Directors (array of proxy ids).
					 * This is actually accomplished after the course is modified below.
					 */

					$posted_objectives = array();
					if ((isset($_POST["primary_objectives"])) && ($objectives = $_POST["primary_objectives"]) && (count($objectives))) {
						$PRIMARY_OBJECTIVES = array();
						foreach ($objectives as $objective_key => $objective) {
							$PRIMARY_OBJECTIVES[] = clean_input($objective, "int");
							$posted_objectives["primary"][] = clean_input($objective, "int");
						}
					}

					if ((isset($_POST["secondary_objectives"])) && ($objectives = $_POST["secondary_objectives"]) && (count($objectives))) {
						$SECONDARY_OBJECTIVES = array();
						foreach ($objectives as $objective_key => $objective) {
							$SECONDARY_OBJECTIVES[] = clean_input($objective, "int");
							$posted_objectives["secondary"][] = clean_input($objective, "int");
						}
					}

					if ((isset($_POST["tertiary_objectives"])) && ($objectives = $_POST["tertiary_objectives"]) && (count($objectives))) {
						$TERTIARY_OBJECTIVES = array();
						foreach ($objectives as $objective_key => $objective) {
							$TERTIARY_OBJECTIVES[] = clean_input($objective, "int");
							$posted_objectives["tertiary"][] = clean_input($objective, "int");
						}
					}


					/**
					 * Non-required field "pcoord_id" .
					 */
					if ((isset($_POST["pcoord_id"])) && ($pcoord_id = clean_input($_POST["pcoord_id"], "int"))) {
						$PROCESSED["pcoord_id"] = $pcoord_id;
					} else {
						$PROCESSED["pcoord_id"] = 0;
					}

					/**
					 * Non-required field "evalrep_id".
					 */
					if ((isset($_POST["evalrep_id"])) && ($evalrep_id = clean_input($_POST["evalrep_id"], "int"))) {
						$PROCESSED["evalrep_id"] = $evalrep_id;
					} else {
						$PROCESSED["evalrep_id"] = 0;
					}

					/**
					 * Non-required field "studrep_id" .
					 */
					if ((isset($_POST["studrep_id"])) && ($studrep_id = clean_input($_POST["studrep_id"], "int"))) {
						$PROCESSED["studrep_id"] = $studrep_id ;
					} else {
						$PROCESSED["studrep_id"] = 0;
					}

					if (isset($_POST["post_action"])) {
						switch($_POST["post_action"]) {
							case "content" :
								$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "content";
							break;
							case "new" :
								$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "new";
							break;
							case "index" :
							default :
								$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "index";
							break;
						}
					} else {
						$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "content";
					}
					
					$period_list = array();
					if (isset($_POST["periods"]) && is_array($_POST["periods"]) && $periods = $_POST["periods"]) {
						foreach ($periods as $key=>$unproced_period) {
							$period_id = (int)$unproced_period;

							$period_list[]=$period_id;
							$group_members = array();
							$individual_members = array();

							if (isset($_POST["group_audience_members"][$key]) && strlen($_POST["group_audience_members"][$key]) && $group_member_string = clean_input($_POST["group_audience_members"][$key],array("trim","notags"))) {
								$group_members = explode(",",$group_member_string);
								if ($group_members) {
									foreach ($group_members as $member) {
										$group_list[$period_id][] = $member["audience_value"];
										$PROCESSED["periods"][$period_id][]=array("audience_type"=>'group_id',"audience_value"=>$member,"cperiod_id"=>$period_id,"audience_active"=>1);

										//$query = "	INSERT INTO `course_audience` VALUES(NULL,".$db->qstr($COURSE_ID).",'group_id',".$db->qstr($member).",0,0,1)";
									}
								}
							}

							if (isset($_POST["individual_audience_members"][$key]) && strlen($_POST["individual_audience_members"][$key]) && $individual_member_string = clean_input($_POST["individual_audience_members"][$key],array("trim","notags"))) {
								$individual_members = explode(",",$individual_member_string);
								if ($individual_members) {
									foreach ($individual_members as $member) {
										$individual_list[$period_id][] = $member["audience_value"];
										$PROCESSED["periods"][$period_id][]=array("audience_type"=>'proxy_id',"audience_value"=>$member,"cperiod_id"=>$period_id,"audience_active"=>1);

										//$query = "	INSERT INTO `course_audience` VALUES(NULL,".$db->qstr($COURSE_ID).",'proxy_id',".$db->qstr($member).",0,0,1)";
									}
								}
							}
							if (!$group_members && !$individual_members) {																
								$query = "	SELECT *
											FROM `curriculum_periods`
											WHERE `cperiod_id` = " . $db->qstr($unproced_period);
								$curriculum_period = $db->getRow($query);
								if ($curriculum_period["curriculum_period_title"]) {
									add_error("The <strong>" . $curriculum_period["curriculum_period_title"] . "</strong> curriculum period requires an audience.");
								} else {
									$error_title =  date("F jS, Y",$curriculum_period["start_date"])." to ".date("F jS, Y",$curriculum_period["finish_date"]);
									add_error("The <strong>" . $error_title . "</strong> curriculum period requires an audience.");
								}
								$PROCESSED["periods"][$period_id][]=array("audience_type"=>'',"audience_value"=>0,"cperiod_id"=>$period_id,"audience_active"=>0);
							}
						}
					}

					if (isset($_POST["syllabus_id"]) && $tmp_input = clean_input($_POST["syllabus_id"], array("int"))) {
						$PROCESSED["syllabus"]["syllabus_id"] = $tmp_input;
					}
					
					if (isset($_POST["syllabus_enabled"]) && $tmp_input = clean_input($_POST["syllabus_enabled"], array("trim", "striptags"))) {
						$PROCESSED["syllabus"]["syllabus_enabled"] = $tmp_input == "enabled" ? 1 : 0;
					} else {
						$PROCESSED["syllabus"]["syllabus_enabled"] = 0;
					}
					
					if ($PROCESSED["syllabus"]["syllabus_enabled"] == 1) {
						if (isset($_POST["syllabus_template"]) && $tmp_input = clean_input($_POST["syllabus_template"], array("trim", "striptags"))) {
							$PROCESSED["syllabus"]["syllabus_template"] = $tmp_input;
						}
					}

					if (isset($_POST["course_report_ids"])) {						
						$PROCESSED["course_report_ids"] = array();
						foreach ($_POST["course_report_ids"] as $index => $tmp_input) {
							if ($course_report_id = clean_input($tmp_input, "int")) {								
								$PROCESSED["course_report_ids"][] = $course_report_id;
							}
						}
					}

					if (!has_error()) {
						$PROCESSED["updated_date"]	= time();
						$PROCESSED["updated_by"]	= $ENTRADA_USER->getID();

						if ($db->AutoExecute("courses", $PROCESSED, "UPDATE", "`course_id`=".$db->qstr($COURSE_ID))) {

							/**
							 * Update corresponding course website contacts if one exists for this course.
							 */
							$community_id = $db->GetOne("SELECT `community_id` FROM `community_courses` WHERE `course_id` = ".$db->qstr($COURSE_ID));
							if ($community_id) {
								$query = "SELECT `proxy_id` FROM `course_contacts` WHERE `course_id` = ".$db->qstr($COURSE_ID);
								$results = $db->GetAll($query);
								if ($results) {
									$delete_ids_string = false;
									foreach ($results as $result) {
										if ($delete_ids_string) {
											$delete_ids_string .= ", ".$db->qstr($result["proxy_id"]);
										} else {
											$delete_ids_string = $result["proxy_id"];
										}
									}
									$db->Execute("UPDATE `community_members` SET `member_acl` = '0' WHERE `proxy_id` IN (".$delete_ids_string.") AND `community_id` = ".$db->qstr($community_id));
								}
							}

							/**
							 * Insert Clinical Presentations.
							 */
							$query = "DELETE FROM `course_objectives` WHERE `objective_type` = 'event' AND `course_id` = ".$db->qstr($COURSE_ID);
							if ($db->Execute($query)) {
								if ((is_array($clinical_presentations)) && (count($clinical_presentations))) {
									foreach ($clinical_presentations as $objective_id => $presentation_name) {
										if (!$db->AutoExecute("course_objectives", array("course_id" => $COURSE_ID, "objective_id" => $objective_id, "objective_type" => "event", "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID()), "INSERT")) {
											add_error("There was an error when trying to insert a &quot;clinical presentation&quot; into the system. System administrators have been informed of this error; please try again later.");

											application_log("error", "Unable to insert a new clinical presentation to the database when adding a new event. Database said: ".$db->ErrorMsg());
										}
									}
								}
							}

							$query = "DELETE FROM `course_contacts` WHERE `course_id`=".$db->qstr($COURSE_ID)." AND `contact_type` != 'pcoordinator'";
							if ($db->Execute($query)) {
								if ((isset($_POST["associated_director"])) && ($associated_directors = explode(",", $_POST["associated_director"])) && (@is_array($associated_directors)) && (@count($associated_directors))) {
									$community_member = false;
									$order = 0;
									foreach($associated_directors as $proxy_id) {
										if ($proxy_id = clean_input($proxy_id, array("trim", "int"))) {
											if (!$db->AutoExecute("course_contacts", array("course_id" => $COURSE_ID, "proxy_id" => $proxy_id, "contact_type" => "director", "contact_order" => $order), "INSERT")) {
												add_error("There was an error when trying to insert a &quot;" . $module_singular_name . " Director&quot; into the system. The system administrator was informed of this error; please try again later.");

												application_log("error", "Unable to insert a new course_contact to the database when updating an event. Database said: ".$db->ErrorMsg());
											} else {
												$order++;

												$community_member = $db->GetOne("SELECT `proxy_id` FROM `community_members` WHERE `proxy_id` = ".$db->qstr($proxy_id)." AND `community_id` = ".$db->qstr($community_id));
												if (!$community_member) {
													if ($community_id && !$db->AutoExecute("community_members", array("community_id" => $community_id, "proxy_id" => $proxy_id, "member_active" => 1, "member_joined" => time(), "member_acl" => 1), "INSERT")) {
														add_error("There was an error when trying to insert a &quot;Curriculum Coordinator&quot; into the system. The system administrator was informed of this error; please try again later.");

														application_log("error", "Unable to insert a new community_member to the database when updating an event. Database said: ".$db->ErrorMsg());
													}
												} else {
													if ($community_id && !$db->AutoExecute("community_members", array("member_active" => 1, "member_acl" => 1), "UPDATE", "`community_id` = ".$db->qstr($community_id)." AND `proxy_id` = ".$db->qstr($proxy_id))) {
														add_error("There was an error when trying to insert a &quot;Curriculum Coordinator&quot; into the system. The system administrator was informed of this error; please try again later.");

														application_log("error", "Unable to insert a new community_member to the database when updating an event. Database said: ".$db->ErrorMsg());
													}
												}
											}
										}
									}
								}

								if ((isset($_POST["associated_coordinator"])) && ($associated_coordinators = explode(",", $_POST["associated_coordinator"])) && (@is_array($associated_coordinators)) && (@count($associated_coordinators))) {
									$community_member = false;
									foreach($associated_coordinators as $proxy_id) {
										if ($proxy_id = clean_input($proxy_id, array("trim", "int"))) {
											if (!$db->AutoExecute("course_contacts", array("course_id" => $COURSE_ID, "proxy_id" => $proxy_id, "contact_type" => "ccoordinator"), "INSERT")) {
												add_error("There was an error when trying to insert a &quot;Curriculum Coordinator&quot; into the system. The system administrator was informed of this error; please try again later.");

												application_log("error", "Unable to insert a new course_contact to the database when updating an event. Database said: ".$db->ErrorMsg());
											} else {
												$community_member = $db->GetOne("SELECT `proxy_id` FROM `community_members` WHERE `proxy_id` = ".$db->qstr($proxy_id)." AND `community_id` = ".$db->qstr($community_id));
												if (!$community_member) {
													if ($community_id && !$db->AutoExecute("community_members", array("community_id" => $community_id, "proxy_id" => $proxy_id, "member_active" => 1, "member_joined" => time(), "member_acl" => 1), "INSERT")) {
														add_error("There was an error when trying to insert a &quot;Curriculum Coordinator&quot; into the system. The system administrator was informed of this error; please try again later.");

														application_log("error", "Unable to insert a new community_member to the database when updating an event. Database said: ".$db->ErrorMsg());
													}
												} else {
													if ($community_id && !$db->AutoExecute("community_members", array("member_active" => 1, "member_acl" => 1), "UPDATE", "`community_id` = ".$db->qstr($community_id)." AND `proxy_id` = ".$db->qstr($proxy_id))) {
														add_error("There was an error when trying to insert a &quot;Curriculum Coordinator&quot; into the system. The system administrator was informed of this error; please try again later.");

														application_log("error", "Unable to insert a new community_member to the database when updating an event. Database said: ".$db->ErrorMsg());
													}
												}
											}
										}
									}
								}

								if (isset($PROCESSED["pcoord_id"]) && $PROCESSED["pcoord_id"]) {
									if ($community_id && !$db->AutoExecute("community_members", array("community_id" => $community_id, "proxy_id" => $PROCESSED["pcoord_id"], "member_active" => 1, "member_joined" => time(), "member_acl" => 1), "INSERT")) {
										add_error("There was an error when trying to insert a &quot;Curriculum Coordinator&quot; into the system. The system administrator was informed of this error; please try again later.");

										application_log("error", "Unable to insert a new community_member to the database when updating an event. Database said: ".$db->ErrorMsg());
									}
								}
							}

							$query = "SELECT * FROM `course_objectives` WHERE `objective_type` = 'course' AND `course_id` = ".$db->qstr($COURSE_ID);
							$course_objectives = $db->GetAll($query);
							$objective_details = array();
							if ($course_objectives && count($course_objectives)) {
								foreach ($course_objectives as $course_objective) {
									if ($course_objective["objective_details"]) {
										$objective_details[$course_objective["objective_id"]]["details"] = $course_objective["objective_details"];
										$objective_details[$course_objective["objective_id"]]["found"] = false;
									}
								}
							}

							$db->Execute("DELETE FROM `course_objectives` WHERE `objective_type` = 'course' AND `course_id` = ".$db->qstr($COURSE_ID));
							if (is_array($PRIMARY_OBJECTIVES) && count($PRIMARY_OBJECTIVES)) {
								foreach($PRIMARY_OBJECTIVES as $objective_id) {
									$db->Execute("INSERT INTO `course_objectives` SET `course_id` = ".$db->qstr($COURSE_ID).", `objective_id` = ".$db->qstr($objective_id).", `updated_date` = ".$db->qstr(time()).", `updated_by` = ".$db->qstr($ENTRADA_USER->getID()).", `importance` = '1', `objective_details` = ".(isset($objective_details[$objective_id]) && $objective_details[$objective_id] ? $db->qstr($objective_details[$objective_id]["details"]) : "NULL"));
									if (isset($objective_details[$objective_id]) && $objective_details[$objective_id]) {
										$objective_details[$objective_id]["found"] = true;
									}
								}
							}

							if (is_array($SECONDARY_OBJECTIVES) && count($SECONDARY_OBJECTIVES)) {
								foreach($SECONDARY_OBJECTIVES as $objective_id) {
									$db->Execute("INSERT INTO `course_objectives` SET `course_id` = ".$db->qstr($COURSE_ID).", `objective_id` = ".$db->qstr($objective_id).", `updated_date` = ".$db->qstr(time()).", `updated_by` = ".$db->qstr($ENTRADA_USER->getID()).", `importance` = '2', `objective_details` = ".(isset($objective_details[$objective_id]) && $objective_details[$objective_id] ? $db->qstr($objective_details[$objective_id]["details"]) : "NULL"));
									if (isset($objective_details[$objective_id]) && $objective_details[$objective_id]) {
										$objective_details[$objective_id]["found"] = true;
									}
								}
							}

							if (is_array($TERTIARY_OBJECTIVES) && count($TERTIARY_OBJECTIVES)) {
								foreach($TERTIARY_OBJECTIVES as $objective_id) {
									$db->Execute("INSERT INTO `course_objectives` SET `course_id` = ".$db->qstr($COURSE_ID).", `objective_id` = ".$db->qstr($objective_id).", `updated_date` = ".$db->qstr(time()).", `updated_by` = ".$db->qstr($ENTRADA_USER->getID()).", `importance` = '3', `objective_details` = ".(isset($objective_details[$objective_id]) && $objective_details[$objective_id] ? $db->qstr($objective_details[$objective_id]["details"]) : "NULL"));
									if (isset($objective_details[$objective_id]) && $objective_details[$objective_id]) {
										$objective_details[$objective_id]["found"] = true;
									}
								}
							}

							if (is_array($objective_details) && count($objective_details)) {
								foreach($objective_details as $objective_id => $objective) {
									if (!$objective["found"]) {
										$db->Execute("INSERT INTO `course_objectives` SET `course_id` = ".$db->qstr($COURSE_ID).", `objective_id` = ".$db->qstr($objective_id).", `updated_date` = ".$db->qstr(time()).", `updated_by` = ".$db->qstr($ENTRADA_USER->getID()).", `importance` = '1', `objective_details` = ".$db->qstr($objective["details"]));
									}
								}
							}

							if (isset($PROCESSED["course_report_ids"]) && count($PROCESSED["course_report_ids"]) > 0) {
								//remove existing course_reports for this course before adding the new set of course reports.
								$query = "DELETE FROM `course_reports` WHERE `course_id` = ".$db->qstr($COURSE_ID);
								if (!$db->Execute($query)) {
									add_error("An error occurred while editing course reports.  The system administrator was informed of this error; please try again later.");
									application_log("error", "Error inserting course reports for course id: " . $COURSE_ID);
								}
								if (!has_error()) {
									foreach ($PROCESSED["course_report_ids"] as $index => $course_report_id) {									
										$PROCESSED["course_report_id"] = $course_report_id;		
										$PROCESSED["course_id"] = $COURSE_ID;								

										if ($db->AutoExecute("course_reports", $PROCESSED, "INSERT")) {											
											add_statistic("Course Edit", "edit", "course_reports.course_report_id", $PROCESSED["course_report_id"], $ENTRADA_USER->getID());
										} else {
											add_error("An error occurred while editing course reports.  The system administrator was informed of this error; please try again later.");
											application_log("error", "Error inserting course reports for course id: " . $COURSE_ID);
										}								
									}
								}
							} else {
								//No course reports for this course.
								$query = "DELETE FROM `course_reports` WHERE `course_id` = ".$db->qstr($COURSE_ID);
								$db->Execute($query);
							}								

							$query = "	DELETE FROM `course_audience` WHERE `course_id` = ".$db->qstr($COURSE_ID).(isset($period_list)?" AND `cperiod_id` NOT IN (".implode(",",$period_list).")":"");
							$db->Execute($query);



							if ($PROCESSED["periods"]) {
								foreach ($PROCESSED["periods"] as $period_id=>$period) {
									$query = "	DELETE FROM `course_audience` WHERE `course_id` = ".$db->qstr($COURSE_ID)." AND `cperiod_id` = ".$db->qstr($period_id)." AND `audience_type` = 'group_id'".(isset($group_list[$period_id])?" AND `audience_value` NOT IN (".implode(",",$group_list[$period_id]).")":"");
									$db->Execute($query);

									$query = "	DELETE FROM `course_audience` WHERE `course_id` = ".$db->qstr($COURSE_ID)." AND `cperiod_id` = ".$db->qstr($period_id)." AND `audience_type` = 'proxy_id'".(isset($individual_list[$period_id])?" AND `audience_value` NOT IN (".implode(",",$individual_list[$period_id]).")":"");
									$db->Execute($query);

									foreach ($period as $key=>$audience) {
										$audience["course_id"] = $COURSE_ID;
										$query = "	SELECT * FROM	`course_audience`
													WHERE `course_id` = ".$db->qstr($COURSE_ID)."
													AND `cperiod_id` = ".$db->qstr($audience["cperiod_id"])."
													AND `audience_type` = ".$db->qstr($audience["audience_type"])."
													AND `audience_value` = ".$db->qstr($audience["audience_value"])."
													AND `audience_active` = 1";

										if (!$row=$db->GetRow($query)) {
											//if(!$db->AutoExecute("course_audience",$PROCESSED["periods"][$period_id][(count($PROCESSED["periods"][$period_id])-1)],"INSERT")) {
											if(!$db->AutoExecute("course_audience",$audience,"INSERT")) {
												add_error("An error occurred while adding the student with id ".$member." as an audience member.");
											}
										}
									}

								}
							} else {
								$query = "	DELETE FROM `course_audience` WHERE `course_id` = ".$db->qstr($COURSE_ID);
								$db->Execute($query);
							}

							if (isset($PROCESSED["syllabus"])) {
								if (isset($PROCESSED["syllabus"]["syllabus_id"])) {
									$mode = "UPDATE";
									$where = "`syllabus_id` = ".$db->qstr($PROCESSED["syllabus"]["syllabus_id"]);
									$syllabus_data["syllabus_id"] = $PROCESSED["syllabus"]["syllabus_id"];
								} else {
									$mode = "INSERT";
									$where = "1 = 1";
								}
								
								if (!empty($period_list)) {
									$query = "SELECT * FROM `curriculum_periods` WHERE `cperiod_id` IN (".implode(",", $period_list).") ORDER BY `start_date` DESC LIMIT 1";
									$period_data = $db->GetRow($query);
								} else {
									$period_data["start_date"] = mktime(0,0,0,1);
									$period_data["finish_date"] = mktime(0,0,0,12);
								}
								
								$syllabus_start = date("n", $period_data["start_date"]);
								$syllabus_finish = date("n", $period_data["finish_date"]);
								
								$syllabus_data["course_id"] = $COURSE_ID;
								$syllabus_data["template"] = $PROCESSED["syllabus"]["syllabus_template"];
								$syllabus_data["active"] = $PROCESSED["syllabus"]["syllabus_enabled"];
								$syllabus_data["syllabus_start"] = $syllabus_start;
								$syllabus_data["syllabus_finish"] = $syllabus_finish;
								
								if (!$db->AutoExecute("course_syllabi", $syllabus_data, $mode, $where)) {
									add_error("An error ocurred while attempting to update the course syllabus, an administrator has been informed, please try again later.");
									application_log("error", "Error on course syllabus ".$mode.", DB said: ".$db->ErrorMsg());
								}
								
							}
							
							if (!has_error()) {
								switch($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"]) {
									case "content" :
										$url = ENTRADA_URL."/admin/".$MODULE."?section=content&id=".$COURSE_ID;
										$msg = "You will now be redirected to the course content page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
									break;
									case "new" :
										$url = ENTRADA_URL."/admin/".$MODULE."?section=add";
										$msg = "You will now be redirected to add a new course; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
									break;
									case "index" :
									default :
										$url = ENTRADA_URL."/admin/".$MODULE."";
										$msg = "You will now be redirected to the course index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
									break;
								}

								$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";
								add_success("You have successfully edited <strong>".html_encode($PROCESSED["course_name"])."</strong> in the system.<br /><br />".$msg);

								application_log("success", $module_singular_name . " [".$COURSE_ID."] has been modified.");
							}
						} else {
							add_error("There was a problem updating this course in the system. The system administrator was informed of this error; please try again later.");

							application_log("error", "There was an error updating a course. Database said: ".$db->ErrorMsg());
						}
					}

					if (has_error()) {
						$STEP = 1;
					}
				break;
				case 1 :
				default :
					$PROCESSED = $course_details;
				break;
			}

			// Display Content
			switch($STEP) {
				case 2 :
					if ($SUCCESS) {
						echo display_success();
					}
					if ($NOTICE) {
						echo display_notice();
					}
					if ($ERROR) {
						echo display_error();
					}
				break;
				case 1 :
				default :
					$query = "	SELECT * 
								FROM `course_reports`
								WHERE `course_id` = ".$db->qstr($COURSE_ID);
					$results = $db->GetAll($query);

					if (!isset($PROCESSED["course_report_ids"])) {
						$PROCESSED["course_report_ids"] = array();
						if ($results) {						
							foreach ($results as $result) {
								$PROCESSED["course_report_ids"][] = $result["course_report_id"];
							}
						}
					}
					?>
					<script type="text/javascript">
					<?php
					$query = "	SELECT * FROM `groups` AS a
								JOIN `group_organisations` AS b
								ON a.`group_id`=b.`group_id`
								WHERE b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
								AND a.`group_active` = 1
                                ORDER BY a.`group_name` ASC";
					if ($groups = $db->GetAll($query)) {
						echo "var is_groups=true;";
						echo "var group_ids = new Array();";
						echo "var group_names = new Array();";
						foreach ($groups as $key=>$group){
							echo "group_ids[".$key."] = ".$group["group_id"].";";
							echo "group_names[".$key."] = '".$group["group_name"]."';";
						}
					} else {
						echo "var is_groups = false;\n";
					}
					?>
					</script>
					<?php

					$LASTUPDATED = $course_details["updated_date"];

					$course_directors = array();
					$curriculum_coordinators = array();
					$chosen_course_directors = array();

					$query = "SELECT a.* FROM `course_audience` AS a
								JOIN `curriculum_periods` AS b
								ON a.`cperiod_id` = b.`cperiod_id`
								WHERE a.`course_id` = ".$db->qstr($COURSE_ID)."
								AND a.`audience_active` = 1
								AND (a.`enroll_finish` = 0 OR a.`enroll_finish` > ".$db->qstr(time()).")
								AND b.`finish_date` >= ".$db->qstr(time());
					$course_audience = $db->GetAll($query);
					if ($course_audience) {
						$PROCESSED["cperiod_id"] = $course_audience[0]["cperiod_id"];
						foreach($course_audience as $audience_member){
							if ($audience_member["audience_type"] == "group_id") {
								$query = "SELECT `group_id`,`group_name` FROM `groups` WHERE `group_id` = ".$db->qstr($audience_member["audience_value"]);
								$result = $db->GetRow($query);
								if($result){
									$PROCESSED["groups"][] = array("id"=>$result["group_id"],"title"=>$result["group_name"]);
								}
							} else {
								$PROCESSED["associated_students"][] = $audience_member["audience_value"];
							}
						}
					}
					
					if (!isset($PROCESSED["periods"])) {
						$query = "	SELECT * FROM `course_audience` WHERE `course_id` = ".$db->qstr($COURSE_ID)." AND `audience_active` = 1";
						$audience = $db->GetAll($query);
						$PROCESSED["periods"] = array();

						if ($audience) {
							foreach ($audience as $member) {
								$PROCESSED["periods"][$member["cperiod_id"]][]=$member;
							}
						}						
					}

					$query	= "	SELECT `".AUTH_DATABASE."`.`user_data`.`id` AS `proxy_id`, CONCAT_WS(', ', `".AUTH_DATABASE."`.`user_data`.`lastname`, `".AUTH_DATABASE."`.`user_data`.`firstname`) AS `fullname`, `".AUTH_DATABASE."`.`organisations`.`organisation_id`
								FROM `".AUTH_DATABASE."`.`user_data`
								LEFT JOIN `".AUTH_DATABASE."`.`user_access`
								ON `".AUTH_DATABASE."`.`user_access`.`user_id` = `".AUTH_DATABASE."`.`user_data`.`id`
								LEFT JOIN `".AUTH_DATABASE."`.`organisations`
								ON `".AUTH_DATABASE."`.`user_access`.`organisation_id` = `".AUTH_DATABASE."`.`organisations`.`organisation_id`
								WHERE `".AUTH_DATABASE."`.`user_access`.`group` = 'faculty'
								AND (`".AUTH_DATABASE."`.`user_access`.`role` = 'director' OR `".AUTH_DATABASE."`.`user_access`.`role` = 'admin')
								AND `".AUTH_DATABASE."`.`user_access`.`app_id` = '".AUTH_APP_ID."'
								AND `".AUTH_DATABASE."`.`user_access`.`account_active` = 'true'
								AND `".AUTH_DATABASE."`.`user_access`.`organisation_id` = " . $ENTRADA_USER->getActiveOrganisation() . "
								ORDER BY `fullname` ASC";
					$results = ((USE_CACHE) ? $db->CacheGetAll(AUTH_CACHE_TIMEOUT, $query) : $db->GetAll($query));
					if ($results) {
						foreach($results as $result) {
							$course_directors[$result["proxy_id"]] = array('proxy_id'=>$result["proxy_id"], 'fullname'=>$result["fullname"], 'organisation_id'=>$result['organisation_id']);
						}
						$DIRECTOR_LIST = $course_directors;
					}

					$query = "	SELECT `".AUTH_DATABASE."`.`user_data`.`id` AS `proxy_id`, CONCAT_WS(', ', `".AUTH_DATABASE."`.`user_data`.`lastname`, `".AUTH_DATABASE."`.`user_data`.`firstname`) AS `fullname`, `".AUTH_DATABASE."`.`organisations`.`organisation_id`
								FROM `".AUTH_DATABASE."`.`user_data`
								LEFT JOIN `".AUTH_DATABASE."`.`user_access`
								ON `".AUTH_DATABASE."`.`user_access`.`user_id` = `".AUTH_DATABASE."`.`user_data`.`id`
								LEFT JOIN `".AUTH_DATABASE."`.`organisations`
								ON `".AUTH_DATABASE."`.`user_data`.`organisation_id` = `".AUTH_DATABASE."`.`organisations`.`organisation_id`
								WHERE `".AUTH_DATABASE."`.`user_access`.`group` = 'staff'
								AND `".AUTH_DATABASE."`.`user_access`.`role` = 'admin'
								AND `".AUTH_DATABASE."`.`user_access`.`app_id` = '".AUTH_APP_ID."'
								AND `".AUTH_DATABASE."`.`user_access`.`account_active` = 'true'
								ORDER BY `fullname` ASC";
					$results = ((USE_CACHE) ? $db->CacheGetAll(AUTH_CACHE_TIMEOUT, $query) : $db->GetAll($query));
					if ($results) {
						foreach($results as $result) {
							$curriculum_coordinators[$result["proxy_id"]] = array('proxy_id'=>$result["proxy_id"], 'fullname'=>$result["fullname"], 'organisation_id'=>$result['organisation_id']);
						}
						$COORDINATOR_LIST = $curriculum_coordinators;
					}

					/**
					 * Non-required field "associated_faculty" / Associated Faculty (array of proxy ids).
					 * This is actually accomplished after the event is inserted below.
					 */
					if ((isset($_POST["associated_director"]))) {
						$associated_director = explode(',', $_POST["associated_director"]);
						foreach($associated_director as $contact_order => $proxy_id) {
							if ($proxy_id = clean_input($proxy_id, array("trim", "int"))) {
								$chosen_course_directors[(int) $contact_order] = $proxy_id;
							}
						}
					} else {
						$query = "SELECT * FROM `course_contacts` WHERE `course_id` = ".$db->qstr($COURSE_ID)." AND `contact_type` = 'director' ORDER BY `contact_order` ASC";
						$results = $db->GetAll($query);
						if ($results) {
							foreach ($results as $result) {
								$chosen_course_directors[$result["contact_order"]] = $course_directors[$result["proxy_id"]]["proxy_id"];
							}
						}
					}

					if ((isset($_POST["associated_coordinator"]))) {
						$associated_coordinator = explode(',', $_POST["associated_coordinator"]);
						foreach($associated_coordinator as $contact_order => $proxy_id) {
							if ($proxy_id = clean_input($proxy_id, array("trim", "int"))) {
								$chosen_ccoordinators[] = $proxy_id;
							}
						}
					} else {
						$query = "SELECT * FROM `course_contacts` WHERE `course_id`=".$db->qstr($COURSE_ID)." AND `contact_type` = 'ccoordinator'";
						$results = $db->GetAll($query);
						if ($results) {
							foreach ($results as $result) {
								$chosen_ccoordinators[] = $result["proxy_id"];
							}
						}
					}

					// Compiles Program Coordinator list
					$programcoodinators = array();

					$query = "	SELECT `".AUTH_DATABASE."`.`user_data`.`id` AS `proxy_id`, CONCAT_WS(', ', `".AUTH_DATABASE."`.`user_data`.`lastname`, `".AUTH_DATABASE."`.`user_data`.`firstname`) AS `fullname`,`".AUTH_DATABASE."`.`user_data`.`id`, `".AUTH_DATABASE."`.`organisations`.`organisation_title`
								FROM `".AUTH_DATABASE."`.`user_data`
								LEFT JOIN `".AUTH_DATABASE."`.`user_access`
								ON `".AUTH_DATABASE."`.`user_access`.`user_id` = `".AUTH_DATABASE."`.`user_data`.`id`
								LEFT JOIN `".AUTH_DATABASE."`.`organisations`
								ON `".AUTH_DATABASE."`.`user_access`.`organisation_id` = `".AUTH_DATABASE."`.`organisations`.`organisation_id`
								WHERE `".AUTH_DATABASE."`.`user_access`.`role` = 'pcoordinator'
								AND `".AUTH_DATABASE."`.`user_access`.`app_id` = '".AUTH_APP_ID."'
								AND `".AUTH_DATABASE."`.`user_access`.`account_active` = 'true'
								AND `".AUTH_DATABASE."`.`user_access`.`organisation_id` = " . $ENTRADA_USER->getActiveOrganisation() . "
								ORDER BY `fullname` ASC";
					$results = ((USE_CACHE) ? $db->CacheGetAll(AUTH_CACHE_TIMEOUT, $query) : $db->GetAll($query));
					if ($results) {
						foreach ($results as $result) {
							$programcoodinators[$result["proxy_id"]] = $result["fullname"]. ' (' . $result['organisation_title'].')';
						}
					}

					// Compiles Evaluation Representative (evalrep_id)  list
					$evaluationreps = array();

					$query = "	SELECT `".AUTH_DATABASE."`.`user_data`.`id` AS `proxy_id`, CONCAT_WS(', ', `".AUTH_DATABASE."`.`user_data`.`lastname`, `".AUTH_DATABASE."`.`user_data`.`firstname`) AS `fullname`,`".AUTH_DATABASE."`.`user_data`.`id`, `".AUTH_DATABASE."`.`organisations`.`organisation_title`
								FROM `".AUTH_DATABASE."`.`user_data`
								LEFT JOIN `".AUTH_DATABASE."`.`user_access`
								ON `".AUTH_DATABASE."`.`user_access`.`user_id` = `".AUTH_DATABASE."`.`user_data`.`id`
								LEFT JOIN `".AUTH_DATABASE."`.`organisations`
								ON `".AUTH_DATABASE."`.`user_data`.`organisation_id` = `".AUTH_DATABASE."`.`organisations`.`organisation_id`
								WHERE `".AUTH_DATABASE."`.`user_access`.`group` = 'faculty'
								AND `".AUTH_DATABASE."`.`user_access`.`app_id` = '".AUTH_APP_ID."'
								AND `".AUTH_DATABASE."`.`user_access`.`account_active` = 'true'
								ORDER BY `fullname` ASC";
					$results = ((USE_CACHE) ? $db->CacheGetAll(AUTH_CACHE_TIMEOUT, $query) : $db->GetAll($query));
					if ($results) {
						foreach ($results as $result) {
							$evaluationreps[$result["proxy_id"]] = $result["fullname"] . ' (' . $result['organisation_title'].')';
						}
					}

					// Compiles Student Representative (evalrep_id)  list
					$studentreps = array();

					$query = "	SELECT `".AUTH_DATABASE."`.`user_data`.`id` AS `proxy_id`, CONCAT_WS(', ', `".AUTH_DATABASE."`.`user_data`.`lastname`, `".AUTH_DATABASE."`.`user_data`.`firstname`) AS `fullname`,`".AUTH_DATABASE."`.`user_data`.`id`, `".AUTH_DATABASE."`.`organisations`.`organisation_title`
								FROM `".AUTH_DATABASE."`.`user_data`
								LEFT JOIN `".AUTH_DATABASE."`.`user_access`
								ON `".AUTH_DATABASE."`.`user_access`.`user_id` = `".AUTH_DATABASE."`.`user_data`.`id`
								LEFT JOIN `".AUTH_DATABASE."`.`organisations`
								ON `".AUTH_DATABASE."`.`user_data`.`organisation_id` = `".AUTH_DATABASE."`.`organisations`.`organisation_id`
								WHERE `".AUTH_DATABASE."`.`user_access`.`group` = 'student'
								AND `".AUTH_DATABASE."`.`user_access`.`app_id` = '".AUTH_APP_ID."'
								AND `".AUTH_DATABASE."`.`user_access`.`account_active` = 'true'
								ORDER BY `fullname` ASC";
					$results = ((USE_CACHE) ? $db->CacheGetAll(AUTH_CACHE_TIMEOUT, $query) : $db->GetAll($query));
					if ($results) {
						foreach ($results as $result) {
							$studentreps[$result["proxy_id"]] = $result["fullname"] . ' (' . $result['organisation_title'].')';
						}
					}

					/**
					 * Compiles the list of students.
					 */
					$STUDENT_LIST = array();
					$query = "	SELECT a.`id` AS `proxy_id`, b.`role`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, a.`organisation_id`
								FROM `".AUTH_DATABASE."`.`user_data` AS a
								LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
								ON a.`id` = b.`user_id`
								WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
								AND b.`account_active` = 'true'
								AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
								AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
								AND b.`group` = 'student'
								AND b.`role` >= '".(date("Y") - ((date("m") < 7) ?  2 : 1))."'
								ORDER BY b.`role` ASC, a.`lastname` ASC, a.`firstname` ASC";
					$results = $db->GetAll($query);
					if ($results) {
						foreach($results as $result) {
							$STUDENT_LIST[$result["proxy_id"]] = array('proxy_id'=>$result["proxy_id"], 'fullname'=>$result["fullname"], 'organisation_id'=>$result['organisation_id']);
						}
					}

					if (has_error()) {
						echo display_error();
					}
					?>
					<form action="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?<?php echo replace_query(array("step" => 2)); ?>" method="post" id="editCourseForm" onsubmit="selIt()" class="form-horizontal">
					<h2 title="Course Details Section"><?php echo $module_singular_name; ?> Details</h2>
					<div id="course-details-section">

						<div class="control-group">
							<label for="curriculum_type_id" class="control-label form-nrequired">Curriculum Category</label>
							<div class="controls">
								<select id="curriculum_type_id" name="curriculum_type_id" style="width: 250px" onchange="loadCurriculumPeriods(this.options[this.selectedIndex].value)">
									<option value="0"<?php echo (((!isset($PROCESSED["curriculum_type_id"])) || (!(int) $PROCESSED["curriculum_type_id"])) ? " selected=\"selected\"" : ""); ?>>- Select Curriculum Category -</option>
									<?php
									$query = "	SELECT a.* FROM `curriculum_lu_types` AS a
												JOIN `curriculum_type_organisation` AS b
												ON a.`curriculum_type_id` = b.`curriculum_type_id`
												WHERE a.`curriculum_type_active` = 1
												AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
												ORDER BY `curriculum_type_order` ASC";
									$results = $db->GetAll($query);
									if ($results) {
										foreach($results as $result) {
											echo "<option value=\"".(int) $result["curriculum_type_id"]."\"".(((isset($PROCESSED["curriculum_type_id"])) && ($PROCESSED["curriculum_type_id"] == $result["curriculum_type_id"])) ? " selected=\"selected\"" : "").">".html_encode($result["curriculum_type_name"])."</option>\n";
										}
									}
									?>
								</select>
							</div>
						</div>

						<div class="control-group">
							<label for="course_name" class="form-nrequired control-label"><?php echo $module_singular_name; ?> Name</label>
							<div class="controls">
								<input type="text" id="course_name" name="course_name" value="<?php echo html_encode($PROCESSED["course_name"]); ?>" maxlength="85"/>
							</div>
						</div>

						<div class="control-group">
							<label for="course_code" class="form-nrequired control-label"><?php echo $module_singular_name; ?> Code</label>
							<div class="controls">
								<input type="text" id="course_code" name="course_code" value="<?php echo html_encode($PROCESSED["course_code"]); ?>" maxlength="16"/>
							</div>
						</div>

						<div class="control-group">
							<label class="form-nrequired control-label">Reminder Notifications</label>
							<div class="controls">
								<label for="notification_on" class="radio">
								  <input type="radio" name="notifications" id="notification_on" value="1"<?php echo (((!isset($PROCESSED["notifications"])) || ((isset($PROCESSED["notifications"])) && ($PROCESSED["notifications"]))) ? " checked=\"checked\"" : ""); ?> />
								   Send e-mail notifications to faculty for events under this <?php echo strtolower($module_singular_name); ?>.
								</label>
								<label for="notification_off" class="radio">
								  <input type="radio" name="notifications" id="notification_off" value="0"<?php echo (((isset($PROCESSED["notifications"])) && (!(int) $PROCESSED["notifications"])) ? " checked=\"checked\"" : ""); ?> />
								  <strong>Do not</strong> send e-mail notifications to faculty for events under this <?php echo strtolower($module_singular_name); ?>.
								</label>
							</div>
						</div>

						<div class="control-group">
							<label class="form-nrequired control-label"><?php echo $module_singular_name; ?> Permissions</label>
							<div class="controls">
								<label for="visibility_on" class="radio">
									<input type="radio" name="permission" id="visibility_on" value="open"<?php echo (((!isset($PROCESSED["permission"])) || ((isset($PROCESSED["permission"])) && ($PROCESSED["permission"] == "open"))) ? " checked=\"checked\"" : ""); ?> />
									This <?php echo strtolower($module_singular_name); ?> is <strong>open</strong> and visible to all logged in users.
								</label><br />
								<label for="visibility_off" class="radio">
									<input type="radio" name="permission" id="visibility_off" value="closed"<?php echo (((isset($PROCESSED["permission"])) && ($PROCESSED["permission"] == "closed")) ? " checked=\"checked\"" : ""); ?> />
									This <?php echo strtolower($module_singular_name); ?> is <strong>private</strong> and only visible to logged in users enrolled in the <?php echo strtolower($module_singular_name); ?>.
								</label>
							</div>
						</div>
						<script type="text/javascript">
							jQuery(function($) {
								$("input[name='sync_ldap']").on("change", function() {
									if ($(this).val() == "1") {
										$(".ldap-course-sync-list").slideDown("fast");
										if ($("textarea[name='sync_ldap_courses']").val().length <= 0) {
											$("textarea[name='sync_ldap_courses']").attr("value", $("#course_code").val());
										}
									} else {
										$(".ldap-course-sync-list").slideUp("fast");
									}
								});
							});
						</script>
						<div class="control-group">
							<label class="form-nrequired control-label">Audience Sync</label>
							<div class="controls">
								<label for="sync_off" class="radio">
									<input type="radio" name="sync_ldap" id="sync_off" value="0"<?php echo (((!isset($PROCESSED["sync_ldap"])) || (isset($PROCESSED["sync_ldap"])) && (!(int)$PROCESSED["sync_ldap"])) ? " checked=\"checked\"" : ""); ?> />The audience will be managed manually and <strong>should not</strong> be synced with the LDAP server.
								</label><br />
								<label for="sync_on" class="radio">
									<input type="radio" name="sync_ldap" id="sync_on" value="1"<?php echo ((((isset($PROCESSED["sync_ldap"])) && ($PROCESSED["sync_ldap"]))) ? " checked=\"checked\"" : ""); ?> /> This course <strong>should</strong> have its audience synced with the LDAP server.
								</label>
								<div class="<?php echo ((((isset($PROCESSED["sync_ldap"])) && ($PROCESSED["sync_ldap"]))) ? "" : "hide"); ?> ldap-course-sync-list">
									<div class="well well-small content-small"><strong>Note:</strong> Please enter a comma separated list of alphanumeric course codes you wish to sync in the text area below. Additional individuals and groups can be manually added in the <strong>Course Enrollment</strong> below. </div>
									<textarea name="sync_ldap_courses" class="span12"><?php echo (isset($PROCESSED["sync_ldap_courses"]) ? $PROCESSED["sync_ldap_courses"] : $PROCESSED["course_code"]); ?></textarea>
								</div>
							</div>
						</div>
					</div>
					<script type="text/javascript">
					var sortables = new Array();
					function updateOrder(type) {
						$('associated_'+type).value = Sortable.sequence(type+'_list');
					}

					function addItem(type) {
						if (($(type+'_id') != null) && ($(type+'_id').value != '') && ($(type+'_'+$(type+'_id').value) == null)) {
							var li = new Element('li', {'class':'community', 'id':type+'_'+$(type+'_id').value, 'style':'cursor: move;'}).update($(type+'_name').value);
							$(type+'_name').value = '';
							li.insert({bottom: '<img src=\"<?php echo ENTRADA_URL; ?>/images/action-delete.gif\" class=\"list-cancel-image\" onclick=\"removeItem(\''+$(type+'_id').value+'\', \''+type+'\')\" />'});
							$(type+'_id').value	= '';
							$(type+'_list').appendChild(li);
							sortables[type] = Sortable.destroy($(type+'_list'));
							Sortable.create(type+'_list', {onUpdate : function(){updateOrder(type);}});
							updateOrder(type);
						} else if ($(type+'_'+$(type+'_id').value) != null) {
							alert('Important: Each user may only be added once.');
							$(type+'_id').value = '';
							$(type+'_name').value = '';
							return false;
						} else if ($(type+'_name').value != '' && $(type+'_name').value != null) {
							alert('Important: When you see the correct name pop-up in the list as you type, make sure you select the name with your mouse, do not press the Enter button.');
							return false;
						} else {
							return false;
						}
					}

					function addItemNoError(type) {
						if (($(type+'_id') != null) && ($(type+'_id').value != '') && ($(type+'_'+$(type+'_id').value) == null)) {
							addItem(type);
						}
					}

					function copyItem(type) {
						if (($(type+'_name') != null) && ($(type+'_ref') != null)) {
							$(type+'_ref').value = $(type+'_name').value;
						}

						return true;
					}

					function checkItem(type) {
						if (($(type+'_name') != null) && ($(type+'_ref') != null) && ($(type+'_id') != null)) {
							if ($(type+'_name').value != $(type+'_ref').value) {
								$(type+'_id').value = '';
							}
						}

						return true;
					}

					function removeItem(id, type) {
						if ($(type+'_'+id)) {
							$(type+'_'+id).remove();
							Sortable.destroy($(type+'_list'));
							Sortable.create(type+'_list', {onUpdate : function (type) {updateOrder(type)}});
							updateOrder(type);
						}
					}

					function selectItem(id, type) {
						if ((id != null) && ($(type+'_id') != null)) {
							$(type+'_id').value = id;
						}
					}

					function loadCurriculumPeriods(ctype_id) {
						var updater = new Ajax.Updater('curriculum_type_periods', '<?php echo ENTRADA_URL."/api/curriculum_type_periods.api.php"; ?>',{
							method:'post',
							parameters: {
								'ctype_id': ctype_id
							},
							onFailure: function(transport){
								$('curriculum_type_periods').update(new Element('div', {'class':'display-error'}).update('No Periods were found for this Curriculum Category.'));
							}
						});
					}
					</script>

					<h2 title="Course Contacts Section"><?php echo $module_singular_name; ?> Contacts</h2>
					<div id="course-contacts-section">
						<div class="control-group">
							<label for="director_name" class="form-nrequired control-label"><?php echo $module_singular_name; ?> Directors</label>
							<div class="controls">
								<input type="text" id="director_name" name="fullname" size="30" autocomplete="off" onkeyup="checkItem('director')" onblur="addItemNoError('director')" />
								<div class="autocomplete" id="director_name_auto_complete"></div>
								<input type="hidden" id="associated_director" name="associated_director" />
								<input type="button" class="btn" onclick="addItem('director');" value="Add" style="vertical-align: middle" />
								<span class="content-small">(<strong>Example:</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?>)</span>
								<ul id="director_list" class="menu" style="margin-top: 15px">
									<?php
									if (is_array($chosen_course_directors) && count($chosen_course_directors)) {
										foreach ($chosen_course_directors as $director) {
											if ((array_key_exists($director, $DIRECTOR_LIST)) && is_array($DIRECTOR_LIST[$director])) {
												?>
													<li class="community" id="director_<?php echo $DIRECTOR_LIST[$director]["proxy_id"]; ?>" style="cursor: move;"><?php echo $DIRECTOR_LIST[$director]["fullname"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" class="list-cancel-image" onclick="removeItem('<?php echo $DIRECTOR_LIST[$director]["proxy_id"]; ?>', 'director');"/></li>
												<?php
											}
										}
									}
									?>
								</ul>
								<input type="hidden" id="director_ref" name="director_ref" value="" />
								<input type="hidden" id="director_id" name="director_id" value="" />
							</div>
						</div>

						<div class="control-group">
							<label for="coordinator_name" class="form-nrequired control-label">Curriculum Coordinators</label>
							<div class="controls">
								<input type="text" id="coordinator_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle" onkeyup="checkItem('coordinator')" onblur="addItemNoError('coordinator')" />
								<div class="autocomplete" id="coordinator_name_auto_complete"></div>
								<input type="hidden" id="associated_coordinator" name="associated_coordinator" />
								<input type="button" class="btn" onclick="addItem('coordinator');" value="Add" style="vertical-align: middle" />
								<span class="content-small">(<strong>Example:</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?>)</span>
								<ul id="coordinator_list" class="menu" style="margin-top: 15px">
									<?php
									if (is_array($chosen_ccoordinators) && count($chosen_ccoordinators)) {
										foreach ($chosen_ccoordinators as $coordinator) {
											if ((array_key_exists($coordinator, $COORDINATOR_LIST)) && is_array($COORDINATOR_LIST[$coordinator])) {
												?>
													<li class="community" id="coordinator_<?php echo $COORDINATOR_LIST[$coordinator]["proxy_id"]; ?>" style="cursor: move;"><?php echo $COORDINATOR_LIST[$coordinator]["fullname"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" class="list-cancel-image" onclick="removeItem('<?php echo $COORDINATOR_LIST[$coordinator]["proxy_id"]; ?>', 'coordinator');"/></li>
												<?php
											}
										}
									}
									?>
								</ul>
								<input type="hidden" id="coordinator_ref" name="coordinator_ref" value="" />
								<input type="hidden" id="coordinator_id" name="coordinator_id" value="" />
							</div>
						</div>

						<div class="control-group">
							<label for="programcoodinator_id" class="form-nrequired control-label">Program Coordinator</label>
							<div class="controls">
								<?php
								if ((is_array($programcoodinators)) && (count($programcoodinators))) {
									echo "<select id=\"pcoord_id\" name=\"pcoord_id\" style=\"width: 95%\">\n";
									echo "<option value=\"\"".((!isset($PROCESSED["pcoord_id"])) ? " selected=\"selected\"" : "").">-- To Be Announced --</option>\n";
									foreach($programcoodinators as $proxy_id => $fullname) {
										echo "<option value=\"".(int) $proxy_id."\"".(($PROCESSED["pcoord_id"] == $proxy_id) ? " selected=\"selected\"" : "").">".html_encode($fullname)."</option>\n";
									}
									echo "</select>\n";
								} else {
									echo "<input type=\"hidden\" id=\"pcoord_id\" name=\"pcoord_id\" value=\"0\" />\n";
									echo "Program Coordinator Information Not Available\n";
								}
								?>
								<div class="well well-small content-small space-above">
									<strong>Important:</strong> Program Coordinators will be able to add, edit or remove learning events in this <?php echo strtolower($module_singular_name); ?>.
								</div>
							</div>
						</div>
						<div class="control-group">
							<label for="evaluationrep_id" class="form-nrequired control-label">Evaluation Rep.</label>
							<div class="controls">
								<?php
								if ((is_array($evaluationreps)) && (count($evaluationreps))) {
									echo "<select id=\"evalrep_id\" name=\"evalrep_id\" style=\"width: 95%\">\n";
									echo "<option value=\"\"".((!isset($PROCESSED["evalrep_id"])) ? " selected=\"selected\"" : "").">-- To Be Announced --</option>\n";
									foreach($evaluationreps as $proxy_id => $fullname) {
										echo "<option value=\"".(int) $proxy_id."\"".(($PROCESSED["evalrep_id"] == $proxy_id) ? " selected=\"selected\"" : "").">".html_encode($fullname)."</option>\n";
									}
									echo "</select>\n";
								} else {
									echo "<input type=\"hidden\" id=\"evalrep_id\" name=\"evalrep_id\" value=\"0\" />\n";
									echo "Evaluation Representative Information Not Available\n";
								}
								?>
							</div>
						</div>

						<div class="control-group">
							<label for="studentrep_id" class="form-nrequired control-label">Student Rep.</label>
							<div class="controls">
								<?php
								if ((is_array($studentreps)) && (count($studentreps))) {
									echo "<select id=\"studrep_id\" name=\"studrep_id\" style=\"width: 95%\">\n";
									echo "<option value=\"\"".((!isset($PROCESSED["studrep_id"])) ? " selected=\"selected\"" : "").">-- To Be Announced --</option>\n";
									foreach($studentreps as $proxy_id => $fullname) {
										echo "<option value=\"".(int) $proxy_id."\"".(($PROCESSED["studrep_id"] == $proxy_id) ? " selected=\"selected\"" : "").">".html_encode($fullname)."</option>\n";
									}
									echo "</select>\n";
								} else {
									echo "<input type=\"hidden\" id=\"studrep_id\" name=\"studrep_id\" value=\"0\" />\n";
									echo "Student Representative Information Not Available\n";
								}
								?>
							</div>
						</div>
					</div>

					<script type="text/javascript">

						new Ajax.Autocompleter('director_name', 'director_name_auto_complete', '<?php echo ENTRADA_RELATIVE; ?>/api/personnel.api.php?type=director', {frequency: 0.2, minChars: 2, afterUpdateElement: function (text, li) {selectItem(li.id, 'director'); copyItem('director');}});
						new Ajax.Autocompleter('coordinator_name', 'coordinator_name_auto_complete', '<?php echo ENTRADA_RELATIVE; ?>/api/personnel.api.php?type=coordinator', {frequency: 0.2, minChars: 2, afterUpdateElement: function (text, li) {selectItem(li.id, 'coordinator'); copyItem('coordinator');}});

						$('director_name').observe('keypress', function(event){
						    if (event.keyCode == Event.KEY_RETURN) {
						        addItem('director');
						        Event.stop(event);
						    }
						});
						$('coordinator_name').observe('keypress', function(event){
						    if (event.keyCode == Event.KEY_RETURN) {
						        addItem('coordinator');
						        Event.stop(event);
						    }
						});
					</script>
					<?php
						$ONLOAD[] = "Sortable.create('director_list', {onUpdate : function() {updateOrder('director')}})";
						$ONLOAD[] = "$('associated_director').value = Sortable.sequence('director_list')";
						$ONLOAD[] = "Sortable.create('coordinator_list', {onUpdate : function() {updateOrder('coordinator')}})";
						$ONLOAD[] = "$('associated_coordinator').value = Sortable.sequence('coordinator_list')";


						require_once(ENTRADA_ABSOLUTE."/javascript/courses.js.php");
						$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
							$query = "	SELECT a.* FROM `global_lu_objectives` a
										JOIN `objective_audience` b
										ON a.`objective_id` = b.`objective_id`
										AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
										WHERE (
												(b.`audience_value` = 'all')
												OR
												(b.`audience_type` = 'course' AND b.`audience_value` = ".$db->qstr($COURSE_ID).")
											)
										AND a.`objective_parent` = '0'
										AND a.`objective_active` = '1'";
							$objectives = $db->GetAll($query);
							if ($objectives) {
								$objective_name = $translate->_("events_filter_controls");
								$hierarchical_name = $objective_name["co"]["global_lu_objectives_name"];
								?>
					<a name="course-objectives-section"></a>
					<h2 title="Course Objectives Section"><?php echo $module_singular_name; ?> Objectives</h2>
					<div id="course-objectives-section">
						<div class="objectives half left">
							<h3>Objective Sets</h3>
							<ul class="tl-objective-list" id="objective_list_0">
					<?php		foreach($objectives as $objective){ ?>
									<li class = "objective-container objective-set"
										id = "objective_<?php echo $objective["objective_id"]; ?>"
										data-list="<?php echo $objective["objective_name"] == $hierarchical_name?'hierarchical':'flat'; ?>"
										data-id="<?php echo $objective["objective_id"];?>">
										<?php $title = ($objective["objective_code"]?$objective["objective_code"].': '.$objective["objective_name"]:$objective["objective_name"]); ?>
										<div 	class="objective-title"
												id="objective_title_<?php echo $objective["objective_id"]; ?>"
												data-title="<?php echo $title;?>"
												data-id = "<?php echo $objective["objective_id"]; ?>"
												data-code = "<?php echo $objective["objective_code"]; ?>"
												data-name = "<?php echo $objective["objective_name"]; ?>"
												data-description = "<?php echo $objective["objective_description"]; ?>">
											<h4><?php echo $title; ?></h4>
										</div>
										<div class="objective-controls" id="objective_controls_<?php echo $objective["objective_id"];?>">
										</div>
										<div 	class="objective-children"
												id="children_<?php echo $objective["objective_id"]; ?>">
												<ul class="objective-list" id="objective_list_<?php echo $objective["objective_id"]; ?>">
												</ul>
										</div>
									</li>
					<?php 		} ?>
							</ul>
						</div>

						<div class="mapped_objectives right droppable" id="mapped_objectives" data-resource-type="course" data-resource-id="<?php echo $COURSE_ID;?>">
							<h3>Mapped Objectives</h3>
							<div class="clearfix">
								<ul class="page-action" style="float: right">
									<li class="last">
										<a href="javascript:void(0)" class="mapping-toggle strong-green" data-toggle="show" id="toggle_sets">Show Objective Sets</a>
									</li>
								</ul>
							</div>
							<p class="well well-small content-small">
								<strong>Helpful Tip:</strong> Click <strong>Show All Objectives</strong> to view the list of available objectives. Select an objective from the list on the left to map it to the course.
							</p>
								<?php   $query = "	SELECT a.*,b.`objective_type`, b.`importance`, c.`objective_active` AS `parent_active`
													FROM `global_lu_objectives` a
													JOIN `course_objectives` b
													ON a.`objective_id` = b.`objective_id`
													AND b.`course_id` = ".$db->qstr($COURSE_ID)."
												 	JOIN `global_lu_objectives` AS c
												 	ON a.`objective_parent` = c.`objective_id`
													WHERE a.`objective_active` = '1'
													AND c.`objective_active` = '1'
													GROUP BY a.`objective_id`
													ORDER BY b.`importance` ASC";
										$mapped_objectives = $db->GetAll($query);
										$primary = false;
										$secondary = false;
										$tertiary = false;
										$hierarchical_objectives = array();
										$flat_objectives = array();
										$objective_importance = array();
										if ($mapped_objectives) {
											foreach($mapped_objectives as $objective){
												//this should be using id from language file, not hardcoded to 1
												if($objective["objective_type"] == "course"){
													$hierarchical_objectives[] = $objective;
													$objective_importance[$objective["importance"]][] = $objective;
												}else{
													$flat_objectives[] = $objective;
												}
											}
										}
										?>
							<a name="curriculum-objective-list"></a>
							<h2 id="hierarchical-toggle" title="Curriculum Objective List" class="list-heading">Curriculum Objectives</h2>
							<div id="curriculum-objective-list">
								<ul class="objective-list mapped-list" id="mapped_hierarchical_objectives" data-importance="hierarchical">
										<?php
											if ($hierarchical_objectives) {
												foreach($hierarchical_objectives as $objective){
														$title = ($objective["objective_code"]?$objective["objective_code"].': '.$objective["objective_name"]:$objective["objective_name"]);
													?>
													<li class = "mapped-objective"
														id = "mapped_objective_<?php echo $objective["objective_id"]; ?>"
														data-title="<?php echo $title;?>"
														data-description="<?php echo $objective["objective_description"];?>">
														<strong><?php echo $title; ?></strong>
														<div class="objective-description">
															<?php
															$set = fetch_objective_set_for_objective_id($objective["objective_id"]);
															if ($set) {
																echo "From the Objective Set: <strong>".$set["objective_name"]."</strong><br/>";
															}
															?>
															<?php echo $objective["objective_description"];?>
														</div>
														<div class="objective-controls">
															<select 	class="importance mini input-small"
																		data-id="<?php echo $objective["objective_id"];?>"
																		data-value="<?php echo $objective["importance"];?>">
																<option value="1"<?php echo $objective["importance"] == 1?' selected="selected"':'';?>>Primary</option>
																<option value="2"<?php echo $objective["importance"] == 2?' selected="selected"':'';?>>Secondary</option>
																<option value="3"<?php echo $objective["importance"] == 3?' selected="selected"':'';?>>Tertiary</option>
															</select>
															<img 	src="<?php echo ENTRADA_URL;?>/images/action-delete.gif"
																	class="objective-remove list-cancel-image"
																	id="objective_remove_<?php echo $objective["objective_id"];?>"
																	data-id="<?php echo $objective["objective_id"];?>">
														</div>
													</li>

									<?php
												}
									 		} 	?>
								</ul>
							</div>
							<a name="other-objective-list"></a>
							<h2 id="flat-toggle" title="Other Objective List" class="collapsed list-heading">Other Objectives</h2>
							<div id="other-objective-list">
								<ul class="objective-list mapped-list" id="mapped_flat_objectives" data-importance="flat">
								<?php
									if ($flat_objectives) {
										foreach($flat_objectives as $objective){
												$title = ($objective["objective_code"]?$objective["objective_code"].': '.$objective["objective_name"]:$objective["objective_name"]);
											?>
									<li class = "mapped-objective"
										id = "mapped_objective_<?php echo $objective["objective_id"]; ?>"
										data-title="<?php echo $title;?>"
										data-description="<?php echo $objective["objective_description"];?>">
										<strong><?php echo $title; ?></strong>
										<div class="objective-description">
											<?php
											$set = fetch_objective_set_for_objective_id($objective["objective_id"]);
											if ($set) {
												echo "From the Objective Set: <strong>".$set["objective_name"]."</strong><br/>";
											}
											?>
											<?php echo $objective["objective_description"];?>
										</div>
										<div class="objective-controls">
											<img 	src="<?php echo ENTRADA_URL;?>/images/action-delete.gif"
													class="objective-remove list-cancel-image"
													id="objective_remove_<?php echo $objective["objective_id"];?>"
													data-id="<?php echo $objective["objective_id"];?>">
										</div>
									</li>

								<?php
										}
							 		} ?>
								</ul>
							</div>
							<select id="primary_objectives_select" name="primary_objectives[]" multiple="multiple" style="display:none;">
							<?php
								if (isset($objective_importance[1]) && $objective_importance[1]) {
									foreach($objective_importance[1] as $objective){
										if($objective["importance"] == 1) {
										?>
										<?php $title = ($objective["objective_code"]?$objective["objective_code"].': '.$objective["objective_name"]:$objective["objective_name"]); ?>
										<option value = "<?php echo $objective["objective_id"]; ?>" selected="selected"><?php echo $title; ?></option>
									<?php
										}
									}
								}
							?>
							</select>
							<select id="secondary_objectives_select" name="secondary_objectives[]" multiple="multiple" style="display:none;">
							<?php
								if (isset($objective_importance[2]) && $objective_importance[2]) {
									foreach($objective_importance[2] as $objective){
										if($objective["importance"] == 2) {
										?>
										<?php $title = ($objective["objective_code"]?$objective["objective_code"].': '.$objective["objective_name"]:$objective["objective_name"]); ?>
										<option value = "<?php echo $objective["objective_id"]; ?>" selected="selected"><?php echo $title; ?></option>
									<?php
										}
									}
								}
							?>
							</select>
							<select id="tertiary_objectives_select" name="tertiary_objectives[]" multiple="multiple" style="display:none;">
							<?php
								if (isset($objective_importance[3]) && $objective_importance[3]) {
									foreach($objective_importance[3] as $objective){
										if($objective["importance"] == 3) {
										?>
										<?php $title = ($objective["objective_code"]?$objective["objective_code"].': '.$objective["objective_name"]:$objective["objective_name"]); ?>
										<option value = "<?php echo $objective["objective_id"]; ?>" selected="selected"><?php echo $title; ?></option>
									<?php
										}
									}
								}
							?>
							</select>
							<select id="clinical_objectives_select" name="clinical_presentations[]" multiple="multiple" style="display:none;">
							<?php
								if ($flat_objectives) {
									foreach($flat_objectives as $objective){
										?>
										<?php $title = ($objective["objective_code"]?$objective["objective_code"].': '.$objective["objective_name"]:$objective["objective_name"]); ?>
										<option value = "<?php echo $objective["objective_id"]; ?>" selected="selected"><?php echo $title; ?></option>
									<?php
									}
								}
							?>
							</select>

						</div>
						<div style="clear:both;"></div>
					</div>
					<?php 	} 	?>
					<h2 title="Course Reports Section"><?php echo $module_singular_name; ?> Reports</h2>
					<div id="course-reports-section">
						<div class="control-group">
							<label for="course_report_ids" class="control-label form-nrequired">Report Types:</label>
							<div class="controls">
								<?php
                                $query = "	SELECT *
											FROM `course_report_organisations` a
											JOIN `course_lu_reports` b
											ON a.`course_report_id` = b.`course_report_id`
											WHERE a.`organisation_id` = " . $db->qstr($course_details["organisation_id"]);
                                $results = $db->GetAll($query);
                                if ($results) {
                                    ?>
                                    <select id="course_report_ids" name="course_report_ids[]" multiple data-placeholder="Choose reports..." class="chosen-select">
                                        <?php
                                        foreach($results as $result) {
											$selected = false;
											if (isset($PROCESSED["course_report_ids"]) && $PROCESSED["course_report_ids"] && in_array($result["course_report_id"], $PROCESSED["course_report_ids"])) {
												$selected = true;
											}
                                            echo build_option($result["course_report_id"], $result["course_report_title"], $selected);
                                        }
                                    ?>
                                    </select>
                                    <?php
                                } 
                                ?>                               
                                <?php
                                if (is_array($PROCESSED["course_reports"])) {
                                    foreach ($PROCESSED["course_reports"] as $course_report) {
                                        echo "<li id=\"type_".$course_report[0]."\" class=\"\">".$course_report[2]."
                                                <a href=\"#\" onclick=\"$(this).up().remove(); cleanupList(); return false;\" class=\"remove\"><img src=\"".ENTRADA_URL."/images/action-delete.gif\"></a>                                                
                                                </li>";
                                    }
                                }
                                ?>                               
							</div>
						</div>
					</div>

					<!-- Course Audience-->
					<h2 title="Course Enrolment Section"><?php echo $module_singular_name; ?> Enrolment</h2>
					<div id="course-enrolment-section">
						<table>
							<colgroup>
								<col style="width: 3%" />
								<col style="width: 22%" />
								<col style="width: 75%" />
							</colgroup>
							<tbody>
								<tr>
									<td>&nbsp;</td>
									<td><label for="period" class="form-nrequired">Enrolment Periods</label></td>
									<td>
										<div id="curriculum_type_periods">
											<?php
											if ($PROCESSED["curriculum_type_id"]) {
													$query = "SELECT * FROM `curriculum_periods` WHERE `curriculum_type_id` = ".$db->qstr($PROCESSED["curriculum_type_id"])." AND `active` = 1 AND `finish_date` >= ".$db->qstr(time());
													if ($periods = $db->GetAll($query)) {
														?>
														<select name="curriculum_period" id="period_select">
															<option value="0" selected="selected">-- Select a Period --</option>
															<?php
															foreach ($periods as $period) {
																echo "<option value=\"".$period["cperiod_id"]."\" ".((array_key_exists($period["cperiod_id"], $PROCESSED["periods"]))?" disabled=\"disabled\"":"").">". (($period["curriculum_period_title"]) ? $period["curriculum_period_title"] . " - " : "") . date("F jS, Y" ,$period["start_date"])." to ".date("F jS, Y" ,$period["finish_date"])."</option>";
															}
															?>
														</select>
														<?php
													} else {
														echo "<div class=\"display-notice\"><ul><li>No periods have been found for the selected <strong>Curriculum Category</strong>.</li></ul></div>";
													}
											} else {
												echo "<div class=\"display-notice\"><ul><li>No <strong>Curriculum Category</strong> has been selected.</li></ul></div>";
											}
											?>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
						 <div id="period_list" class="span12">                       
							<?php
							$peroid_start = 0;
							if (isset($PROCESSED["periods"])) {								
								?> 
							<h2>Active Periods</h2>
                        <?php
                            foreach ($PROCESSED["periods"] as $key=>$period) {
                                $query = "SELECT * FROM `curriculum_periods` WHERE `cperiod_id` = ".$db->qstr($key);
                                $period_data = $db->GetRow($query);

                                ?>
                                <div class="period_item" id="period_item_<?php echo $key;?>" style="margin-top:20px;">									                                    
									<div class="clearfix">
										<i class="icon-minus-sign remove_period" id="remove_period_<?php echo $key;?>"></i>&nbsp;<strong><?php echo (($period_data["curriculum_period_title"]) ? $period_data["curriculum_period_title"] . " - " : ""); ?></strong><span class="content-small"><?php echo date("F jS, Y",$period_data["start_date"])." to ".date("F jS, Y",$period_data["finish_date"]); ?></span><a href="javascript:void(0)" class="enrollment-toggle strong-green pull-right" id="add_audience_<?php echo $key;?>">Add Audience</a>											
									</div>	
									<div class="audience_selector span12 pull-left" id="audience_type_select_<?php echo $key;?>" style="display: none; margin-top: 20px;">
										<select class="audience_type_select" id="audience_type_select_<?php echo $key;?>" onchange="showSelect(<?php echo $key;?>,this.options[this.selectedIndex].value)">
											<option value="0">-- Select Audience Type --</option>
											<option value="cohort">Cohort</option>
											<option value="individual">Individual</option>
										</select>
										<select style="display:none;" class="type_select" id="cohort_select_<?php echo $key;?>" onchange="addAudience(<?php echo $key;?>,this.options[this.selectedIndex].text,'cohort',this.options[this.selectedIndex].value)"><option value="0">-- Add Cohort --</option>
											<?php											
											foreach ($period as $audience) {
												switch ($audience["audience_type"]){
													case 'group_id':														
														$group_ids[$key][] = $audience["audience_value"];																												
														break;
													
													case 'proxy_id':
														$proxy_ids[$key][] = $audience["audience_value"];	
														break;
												}
											}
											foreach ($groups as $group) {
												echo "<option value=\"".$group["group_id"]."\"".((in_array($group["group_id"],$group_ids[$key]))?" disabled=\"disabled\"":"").">".$group["group_name"]."</option>";
											}
											?>
										</select>										
										<input style="display:none;width:203px;vertical-align: middle;margin-left:10px;margin-right:10px;" type="text" name="fullname" class="type_select" id="student_<?php echo $key;?>_name" autocomplete="off"/><input style="display:none;" type="button" class="btn type_select individual_add_btn" id="add_associated_student_<?php echo $key;?>" value="Add" style="vertical-align: middle" />
										<div class="autocomplete" id="student_<?php echo $key;?>_name_auto_complete" style="margin-left:200px;"></div>
										<div style="display:none; margin-left: 240px;" id="student_example_<?php echo $key;?>">(Example: <?php echo $ENTRADA_USER->getFullname(true); ?>)</div>
										<input type="hidden" name="group_audience_members[]" id="group_audience_members_<?php echo $key;?>" value="<?php echo ($group_ids[$key] ? implode(',',$group_ids[$key]) : ""); ?>"/>
										<input type="hidden" name="individual_audience_members[]" id="associated_student_<?php echo $key;?>"/>
										<input type="hidden" name="student_id[]" id="student_<?php echo $key;?>_id"/>
										<input type="hidden" name="student_ref[]" id="student_<?php echo $key;?>_ref"/>
										<input type="hidden" name="periods[]" value="<?php echo $key;?>"/>
										<?php
										$ONLOAD[] = "new AutoCompleteList({ type: 'student_".$key."', url: '".ENTRADA_RELATIVE."/api/personnel.api.php?type=student', remove_image: '".ENTRADA_RELATIVE."/images/minus-sign.png'})";
										?>
									</div>
									
									<div class="audience_section span12 pull-left" id="audience_section_<?php echo $key;?>" style="display:block; margin-top: 20px; margin-bottom: 20px; border-bottom:1px solid #D3D3D3;">
                                    <div class="audience_list" id="audience_list_<?php echo $key;?>" style="margin-bottom:10px;">									
										<ul id="audience_list_container_<?php echo $key;?>" class="listContainer">
											<li><strong>Cohorts</strong>
												<ol id="audience_container_<?php echo $key;?>" class="sortableList">
													<?php
													foreach ($period as $audience) {
														switch ($audience["audience_type"]){
															case 'group_id':
																$query = "SELECT `group_name` AS `title` FROM `groups` WHERE `group_id`=".$db->qstr($audience["audience_value"]);
																$group_ids[$key][] = $audience["audience_value"];
																$audience["type"] = 'cohort';
																$audience["title"] = $db->GetOne($query);
																?>
																<li id="audience_<?php echo $audience["type"]."_".$audience["audience_value"];?>" class="audience_cohort"><?php echo $audience["title"];?><i class="icon-minus-sign remove_audience pull-right"></i></li>
																<?php
															break;
														}
													}
													?>
												</ol>
											</li>
										</ul>	
										<ul id="student_<?php echo $key;?>_list_container" class="listContainer">
											<li><strong>Students</strong>
												<ol id="student_<?php echo $key;?>_list" class="sortableList">
                                        <?php
                                        foreach ($period as $audience) {
                                            switch ($audience["audience_type"]) {
                                                case 'proxy_id':
                                                    $query = "SELECT CONCAT_WS(',',`lastname`,`firstname`) AS `title` FROM `".AUTH_DATABASE."`.`user_data` WHERE `id`=".$db->qstr($audience["audience_value"]);
                                                    $audience["type"]='individual';
                                                    $audience["title"] = $db->GetOne($query);
                                                ?>
																	<li id="student_<?php echo $key.'_'.$audience["audience_value"];?>" style="cursor: move; position: relative;" class="user"><?php echo $audience["title"];?><img src="<?php echo ENTRADA_URL; ?>/images/minus-sign.png" class="list-cancel-image remove_student" /></li>
                                                    <?php
                                                    break;
                                            }
                                        }
                                        ?>
												</ol>
											</li>
                                        </ul>
										<?php if (count($period) == 1 && $period[0]["audience_value"] == 0) { ?>
										<div id="no_audience_msg_<?php echo $key;?>" class="alert alert-block alert-info no_audience_msg" style="margin-top: 20px;">
											Please use the <strong>Add Audience</strong> link above to add an audience to this enrollment period.
										</div>
									<?php } ?>										
                                    </div>
									</div>
                                </div>
                                <?php
								}
							}
							?>
						</div>
					</div>
					<script type="text/javascript">
						jQuery(function($) {
							$("input[name=syllabus_enabled]").on("click", function() {
								if ($(this).val() == "enabled") {
									$("#syllabus-settings").show();
								} else {
									$("#syllabus-settings").hide();
								}
							});
							$('.chosen-select').chosen({no_results_text: 'No Reports Available.'});	
							
							$('div#period_list').on("click", 'a.enrollment-toggle', function(event) {								
								var section_id = $(this).attr("id").split("add_audience_")[1];	
								$('#audience_section_' + section_id).show();
								$('#no_audience_msg_' + section_id).remove();
								$('#audience_type_select_' + section_id).fadeToggle("slow", "swing",
									function() {
										if ($('#audience_type_select_' + section_id).is(':visible')) {
											$('#add_audience_' + section_id).html("Done");
										} else {
											$('#add_audience_' + section_id).html("Add Audience");
										}
									}
								);								
							});

							$('#curriculum_type_periods').on("change", '#period_select', function(event) {
								var option = $("option:selected", this);
								$.ajax({
									url: "<?php echo ENTRADA_URL . "/api/curriculum_periods.api.php" ;?>",
									data: "key=" + $(option).val(),
									type: "GET",
									dataType: "html",
									success: function(data) {
										$('#period_list').append(data);
										var period_id = $(option).val();																	

										$('#period_select option[value='+period_id+']').attr('disabled', 'disabled');
										$("#period_select").val('0');
										$('#period_list').show();
										eval("var student_"+period_id+"_list = new AutoCompleteList({ type: 'student_"+period_id+"', url: '<?php echo ENTRADA_RELATIVE;?>/api/personnel.api.php?type=student', remove_image: '"+DELETE_IMAGE_URL+"'})");
										eval("window.student_"+period_id+"_list = student_"+period_id+"_list");
									}
								});
							});
							
							$('div#period_list').on("click keyup", ".individual_add_btn", function(event) {							
								var period_id = $(this).attr("id").split("add_associated_student_")[1];
								var student_list_container = $('#student_' + period_id + "_list_container");		
								if (event.type == "keyup") {
									var code = event.keyCode || event.which;
									if(code == 13) { //Enter keycode
										//show student list if not visible									
										if (!$(student_list_container).is(':visible')) {
											$(student_list_container).show();
										}									
									}
								} else {
									if (!$(student_list_container).is(':visible')) {
										$(student_list_container).show();
									}
								}
							});
							
							$('div.audience_list .sortableList').each(function() {							
								if ($(this) !== 'undefined') {
									if ($(this).children().length > 0) {
										$(this).parent().parent().show();
									} else {
										$(this).parent().parent().hide();
									}
								}
							});

							$('div#period_list').on("click", 'img.list-cancel-image', function(e) {					
								$('div.audience_list .sortableList').each(function() {							
									if ($(this) !== 'undefined') {
										if ($(this).children().length > 0) {
											$(this).parent().parent().show();
										} else {
											$(this).parent().parent().hide();
										}
									}
								});
							});
							
						});
					</script>
					<h2 title="Course Syllabus Section">Course Syllabus</h2>
					<div id="course-syllabus-section">
						<?php
							$course_syllabus = Models_Syllabus::fetchRowByCourseID($COURSE_ID);
							$syllabi = glob($ENTRADA_TEMPLATE->absolute()."/syllabus/*.php");
						?>
						<input type="hidden" name="syllabus_id" value="<?php echo $course_syllabus->getID(); ?>" />
						<div class="control-group">
							<label class="control-label">Automatic Generation</label>
							<div class="controls">
								<label class="radio"><input type="radio" name="syllabus_enabled" value="enabled" <?php echo $course_syllabus->getActive() ? "checked=\"checked\"" : ""; ?> /> Enabled</label>
								<label class="radio"><input type="radio" name="syllabus_enabled" <?php echo !$course_syllabus->getActive() ? "checked=\"checked\"" : ""; ?> /> Disabled</label>
							</div>
						</div>
						<div id="syllabus-settings" style="<?php echo !$course_syllabus->getActive() ? "display:none;" : ""; ?>">
							<div class="control-group" id="syllabus-template">
								<label class="control-label">Template</label>
								<div class="controls">
									<select name="syllabus_template">
										<?php
										foreach ($syllabi as $syllabus) {
											$syllabus_template = trim(substr($syllabus, strrpos($syllabus, "/") + 1));
											$syllabus_template = substr($syllabus_template, 0, strlen($syllabus_template) - 4);
											if ($syllabus_template != "page-whitelist.inc") {
											?>
										<option value="<?php echo $syllabus_template; ?>" <?php echo $course_syllabus->getTemplate() == $syllabus_template ? "selected=\"selected\"" : ""; ?> ><?php echo $syllabus_template; ?></option>
											<?php
											}
										}
										?>
									</select>
								</div>
							</div>
						</div>
					</div>
					<script type="text/javascript">				

					function showSelect(period_id,type){
						jQuery('.type_select').each(function(){
							$(this).hide();
						});

						if (type=='cohort') {
							jQuery('#'+type+'_select_'+period_id).show();
							jQuery("#student_example_"+period_id).hide();
						}

						if (type=='individual') {
							jQuery('#student_'+period_id+'_name').show();
							jQuery('#add_associated_student_'+period_id).show();
							jQuery("#student_example_"+period_id).show();
						}
						jQuery('.audience_type_select').each(function(){
							jQuery(this).val('0');
						});
						jQuery("#audience_type_select_"+period_id).show();
						jQuery("#audience_type_select_"+period_id+" option[value='"+type+"']").attr('selected','selected');
					}

					function getGroupOptions(){
						var markup = '';
						if (is_groups) {
							for (i=0;i<group_ids.length;i++) {
								markup += '<option value="'+group_ids[i]+'">'+group_names[i]+'</option>';
							}
						}
						return markup;
					}

					function printGroupList(period_id) {
						var markup = '<ol id="group_'+period_id+'_container" class="sortableList" style="display: none;"></ol><input id="group_'+period_id+'order" name="group_order[]" value ="" style="display: none;"/>';
						return markup;
					}

					function printIndividualList(period_id) {
						var markup = '<input type="hidden" id="associated_student_'+period_id+'" name="associated_student[]" />'+
										'<ul id="student_'+period_id+'_list" class="menu" style="margin-top: 15px"></ul><input type="hidden" id="student_'+period_id+'_ref" name="student_ref[]" value="" /><input type="hidden" id="student_'+period_id+'_id" name="student_id[]" value="" />';
						return markup;
					}

					function addAudience(period_id,audience_value,type,select_value){
						if (type=='individual') {
							audience_value = jQuery('#individual_select_'+period_id).val();
						}
						li = new Element('li', {id: 'audience_'+type+'_'+select_value, 'class': 'audience_'+type});
						li.insert(audience_value + "&nbsp;");      
						li.insert(new Element('i', {style: 'cursor:pointer; float: right;','class': 'remove_audience icon-minus-sign'}));
						$('audience_container_'+period_id).insert(li);

						jQuery('#'+type+'_select_'+period_id).val('');
						jQuery('#audience_section_' + period_id).show();

						if (type=='cohort') {
							if (!jQuery('#audience_list_container' + period_id).is(':visible')) {
								jQuery('#audience_list_container_' + period_id).show();
							}						
							jQuery('#'+type+'_select_'+period_id).val('0');
							jQuery("#cohort_select_"+period_id+" option[value='"+select_value+"']").attr('disabled','disabled');
							var ids = jQuery('#group_audience_members_'+period_id).val().split(',');
							if (jQuery('#group_audience_members_'+period_id).val().length == 0) {
								idx = 0;
							} else {
								idx = ids.length;
							}
							ids[idx] = select_value;
							jQuery('#group_audience_members_'+period_id).val(ids.join(','));
						} else {
							if (!jQuery('#student_' + period_id + '_list_container').is(':visible')) {
								jQuery('#student_' + period_id + '_list_container').show();
							}
						}
					}

					function selectCourseAudienceOption(type) {
						$$('.course_audience').invoke('hide');
						$$('.'+type+'_audience').invoke('show');
					}


					jQuery('.remove_period').live('click',function(e){
						var id_info = e.target.id.split('_');
						var id = id_info[2];
						jQuery('#period_item_'+id).remove();
						jQuery("#period_select option[value='"+id+"']").removeAttr('disabled');
					});

					jQuery('div#period_list').on('click', '.remove_audience', function(e){	
						//check if the event target is <i> or <img>.  This depends upon
						//the element being added by the AutoComplete.js which uses the img tag still
						//or built on page load with the bootstrap icon.
						if (jQuery(e.target).is("i")) {						
							var period_info = $(e.target).up().up().id.split('_');			
							var id_info = $(e.target).up().id.split('_');				
						} else {						
						var period_info = $(e.target).up().up().up().id.split('_');
							var id_info = $(e.target).up().up().id.split('_');				
						}
						var period_id = period_info[2];
						var type = id_info[1];
						var id = id_info[2];
						jQuery('#audience_list_container_' + period_id).show();
						if (type==='cohort') {
							var members_array = jQuery('#group_audience_members_'+period_id).val().split(',');
							var idx = jQuery.inArray(id, members_array);
							if (idx != -1) {
								members_array.splice(idx,1);
							}
							jQuery('#group_audience_members_'+period_id).val(members_array.join(','));
							jQuery("#cohort_select_"+period_id+" option[value='"+id+"']").removeAttr('disabled');
						}
						if (jQuery(e.target).is("i")) {
							$(e.target).up().remove();
							if (jQuery('#audience_container_' + period_id).children().length == 0) {
								jQuery('#audience_list_container_' + period_id).hide();							
							}
						} else {
							$(e.target).up().up().remove();
							if ($(e.target).up().up().up().children.length == 0) {
								$(e.target).up().up().up().up().hide();
							}
						}
					});
					
					jQuery('div#period_list').on("click", '.remove_student', function(e) {					
						var temp = jQuery(this).parent().attr("id").split("_");
						var id = temp[2];						
						var type = "student_" + temp[1];						
						if ($(type+'_'+id)) {
							$(type+'_'+id).remove();
							Sortable.destroy($(type+'_list'));
							Sortable.create(type+'_list', {onUpdate : function (type) {updateOrder(type)}});
							updateOrder(type);
							if (jQuery('#' + type + '_container').children().length == 0) {
								jQuery('#' + type + '_list_container').hide();							
							}
						}
					});

					//$('student_list').observe('change', checkConditions);
					//$('group_order').observe('change', checkConditions);

					function checkConditions(){
							if($F('associated_student')){
								var students = $F('associated_student').split(',');
								if(students.length>0);{
									if($F('group_order').length >0){
										checkConflict();
									}
								}
							}

						}

						function checkConflict() {
							new Ajax.Request('<?php echo ENTRADA_URL;?>/api/course-audience-conflicts.api.php',
							{
								method:'post',
								parameters: $("editCourseForm").serialize(true),
								onSuccess: function(transport){
								var response = transport.responseText || null;
								if(response !==null){
									var g = new k.Growler();
									g.smoke(response,{life:7});
								}
								},
								onFailure: function(){ alert('Unable to check if a conflict exists.') }
							});
						}
					</script>
					
					<style type="text/css">
						.remove_audience, .remove_student, .remove_period {
							cursor: pointer;
						}
						ol.sortableList {
							margin-top: 0px;
						}
						.sortableList li {
							width: 175px;
							border-bottom: 0;
						}
						.listContainer {
							list-style-type:none;
						}
						.enrollment-toggle {
							display:inline;
							background:transparent url(../images/btn_add.gif) no-repeat center left;
							padding:0 14px 0 16px;
							color:#693;
							font-weight:700;
						}
						img.list-cancel-image {
							position: relative;
							right: 0;
							float: right;
							margin-right: 3px;
						}
					</style>

					<div style="padding-top: 25px">
						<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
						<tr>
							<td style="width: 25%; text-align: left">
								<input type="button" class="btn" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/courses'" />
							</td>
							<td style="width: 75%; text-align: right; vertical-align: middle">
								<span class="content-small">After saving:</span>
								<select id="post_action" name="post_action">
								<option value="content"<?php echo (((!isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"])) || ($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "content")) ? " selected=\"selected\"" : ""); ?>>Add content to course</option>
								<option value="new"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "new") ? " selected=\"selected\"" : ""); ?>>Add another course</option>
								<option value="index"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "index") ? " selected=\"selected\"" : ""); ?>>Return to course list</option>
								</select>
								<input type="submit" class="btn btn-primary" value="Save" />
							</td>
						</tr>
						</table>
					</div>
					</form>
					<?php
				break;
			}
		} else {
			add_error("In order to edit a course you must provide a valid course identifier. The provided ID does not exist in this system.");

			echo display_error();

			application_log("notice", "Failed to provide a valid course identifer when attempting to edit a course.");
		}
	} else {
		add_error("In order to edit a course you must provide the courses identifier.");

		echo display_error();

		application_log("notice", "Failed to provide course identifer when attempting to edit a course.");
	}
}
