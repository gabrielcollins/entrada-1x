<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Gives community administrators the ability to edit a page in their community.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
*/

if ((!defined("COMMUNITY_INCLUDED")) || (!defined("IN_PAGES")) || !COMMUNITY_INCLUDED || !IN_PAGES) {
	exit;
} elseif (!$COMMUNITY_LOAD) {
	exit;
}

if (($LOGGED_IN) && (!$COMMUNITY_MEMBER)) {
	$NOTICE++;
	$NOTICESTR[] = "You are not currently a member of this community, <a href=\"".ENTRADA_URL."/communities?section=join&community=".$COMMUNITY_ID."&step=2\" style=\"font-weight: bold\">want to join?</a>";

	echo display_notice();
} else {
	$PAGE_TYPES		= array();
	$STEP			= 1;
	$PAGE_ID		= 0;
	$page_options 	= array();
	$home_page		= false;
	
	if ((isset($_GET["step"])) && ($tmp_input = clean_input($_GET["step"], array("int")))) {
		$STEP = $tmp_input;
	}
	
	if ((isset($_GET["page"])) && ($tmp_input = clean_input($_GET["page"], array("int")))) {
		$PAGE_ID 		= $tmp_input;
		$query			= "SELECT `page_type`, `page_url` FROM `community_pages` WHERE `cpage_id` = ".$db->qstr($PAGE_ID);
		$page_record	= $db->GetRow($query);
		$page_type		= $page_record["page_type"];
		
		$home_page		= (((isset($page_record["page_url"])) && ($page_record["page_url"] != "")) ? false : true);
	} elseif ($_GET["page"] == "home") {
		$query			= "SELECT `cpage_id`,`page_type` FROM `community_pages` WHERE `page_url` = '' AND `community_id` = ".$db->qstr($COMMUNITY_ID);
		$page_record 	= $db->GetRow($query);
		$PAGE_ID 		= $page_record["cpage_id"];
		$page_type		= $page_record["page_type"];
		
		$home_page		= true;
	}
	
	$query				= "	SELECT `module_shortname`, `module_title`
							FROM `communities_modules`
							WHERE `module_id` IN (
								SELECT `module_id`
								FROM `community_modules`
								WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)."
								AND `module_active` = '1'
								ORDER BY `module_title` ASC
							)";
	$module_pagetypes	= $db->GetAll($query);
	
	$PAGE_TYPES[]		= array("module_shortname" => "default", "module_title" => "Default Content");	
	
	foreach ($module_pagetypes as $module_pagetype) {
		$PAGE_TYPES[]	= array("module_shortname" => $module_pagetype["module_shortname"], "module_title" => $module_pagetype["module_title"]);
	}
	
	if (!$home_page) {
		$PAGE_TYPES[]	= array("module_shortname" => "url", "module_title" => "External URL");
	}
	
	foreach ($PAGE_TYPES as $PAGE) {
		if (isset($_GET["type"])) {
			if ((is_array($PAGE)) && (array_search(trim($_GET["type"]), $PAGE))) {
				$PAGE_TYPE = trim($_GET["type"]);
				break;
			}
		}
	}
	
	if (!isset($PAGE_TYPE) || !$PAGE_TYPE) {
		$PAGE_TYPE= $page_type;
	}
	
	if ($home_page) {
		$query		= "SELECT * FROM `community_page_options` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID);//." AND `cpage_id` = '0'";
		$results	= $db->GetAll($query);
		if ($results) {
			foreach ($results as $result) {
				switch ($result["option_title"]) {
					case "show_announcements" :
						$page_options["show_announcements"] = $result;
					break;
					case "show_events" :
						$page_options["show_events"] = $result;
					break;
					case "show_history" :
						$page_options["show_history"] = $result;
					break;
					default :
						continue;
					break;
				}
			}
		}
	} else {
		if ($PAGE_TYPE == "announcements" || $PAGE_TYPE == "events") {
			$query		= "SELECT * FROM `community_page_options` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `cpage_id` = ".$db->qstr($PAGE_ID);
			$results	= $db->GetAll($query);
			if ($results) {
				foreach ($results as $result) {
							$page_options[$result["option_title"]] = $result;
				}
			}
			if (!key_exists('allow_member_posts', $page_options)) {
			$db->Execute("INSERT INTO `community_page_options` SET `community_id` = ".$db->qstr($COMMUNITY_ID).", `cpage_id` = ".$db->qstr($PAGE_ID).", `option_title` = 'allow_member_posts', `option_value` = '0'");
			$page_options["allow_member_posts"] = Array ('cpoption_id' 	=> $db->insert_id(),
														 'community_id' => $COMMUNITY_ID,
														 'cpage_id' 	=> $PAGE_ID,
														 'option_title' => "allow_member_posts",
														 'option_value' => 0,
														 'proxy_id'		=> 0,
														 'updated_date' => 0
														);
			}
			
			if (!key_exists('allow_troll_posts', $page_options)) {
				$db->Execute("INSERT INTO `community_page_options` SET `community_id` = ".$db->qstr($COMMUNITY_ID).", `cpage_id` = ".$db->qstr($PAGE_ID).", `option_title` = 'allow_troll_posts', `option_value` = '0'");
				$page_options["allow_troll_posts"] = Array ('cpoption_id' 	=> $db->insert_id(),
															 'community_id' => $COMMUNITY_ID,
															 'cpage_id' 	=> $PAGE_ID,
															 'option_title' => "allow_troll_posts",
															 'option_value' => 0,
															 'proxy_id'		=> 0,
															 'updated_date' => 0
															);
			}
			
			if (!key_exists('moderate_posts', $page_options)) {
				$db->Execute("INSERT INTO `community_page_options` SET `community_id` = ".$db->qstr($COMMUNITY_ID).", `cpage_id` = ".$db->qstr($PAGE_ID).", `option_title` = 'moderate_posts', `option_value` = '0'");
				$page_options["moderate_posts"] = Array ('cpoption_id' 	=> $db->insert_id(),
															 'community_id' => $COMMUNITY_ID,
															 'cpage_id' 	=> $PAGE_ID,
															 'option_title' => "moderate_posts",
															 'option_value' => 0,
															 'proxy_id'		=> 0,
															 'updated_date' => 0
															);
			}
		} elseif ($PAGE_TYPE == "url") {
			$query		= "SELECT * FROM `community_page_options` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `cpage_id` = ".$db->qstr($PAGE_ID);
			$results	= $db->GetAll($query);
			if ($results) {
				foreach ($results as $result) {
							$page_options[$result["option_title"]] = $result;
				}
			}
			if (!key_exists('new_window', $page_options)) {
				$db->Execute("INSERT INTO `community_page_options` SET `community_id` = ".$db->qstr($COMMUNITY_ID).", `cpage_id` = ".$db->qstr($PAGE_ID).", `option_title` = 'new_window', `option_value` = '0'");
				$page_options["moderate_posts"] = Array ('cpoption_id' 	=> $db->insert_id(),
															 'community_id' => $COMMUNITY_ID,
															 'cpage_id' 	=> $PAGE_ID,
															 'option_title' => "new_window",
															 'option_value' => 0,
															 'proxy_id'		=> 0,
															 'updated_date' => 0
															);
			}
		}
	}
	
	
	if (($COMMUNITY_ID) && ($PAGE_ID)) {
		$query				= "SELECT * FROM `communities` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `community_active` = '1'";
		$community_details	= $db->GetRow($query);
		if ($community_details) {
			$BREADCRUMB[]	= array("url" => ENTRADA_URL."/community".$community_details["community_url"], "title" => limit_chars($community_details["community_title"], 50));
			$BREADCRUMB[]	= array("url" => ENTRADA_URL."/community".$community_details["community_url"].":pages", "title" => "Manage Pages");
			
			$query	= "	SELECT * FROM `community_members`
						WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)."
						AND `proxy_id` = ".$db->qstr($ENTRADA_USER->getActiveId())."
						AND `member_active` = '1'
						AND `member_acl` = '1'";
			$result	= $db->GetRow($query);
			if ($result && ($PAGE_ID != "home") && (isset($page_type) && $page_type != "home")) {
				$query			= "SELECT * FROM `community_pages` WHERE `cpage_id` = ".$db->qstr($PAGE_ID)." AND `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `page_active` = '1'";
				$page_details	= $db->GetRow($query);
				if ($page_details) {
					load_rte(($PAGE_TYPE == "default" || $PAGE_TYPE == "course") ? "communityadvanced" : "communitybasic");
					
					$BREADCRUMB[]	= array("url" => "", "title" => "Edit Page");
				
					if (!isset($PAGE_TYPE) || $page_details["page_type"] == "course") {
						$PAGE_TYPE = $page_details["page_type"];
						$results = $db->GetAll("SELECT `course_id` FROM `community_courses` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID));
				        $course_ids = array();
				        $course_ids_string = "";
				        foreach($results as $course_id) {
				        	$course_ids[] = $course_id["course_id"];
				        	if ($course_ids_string) {
				        		$course_ids_string .= ",".$course_id["course_id"];
				        	} else {
				        		$course_ids_string .= $course_id["course_id"];
				        	}
				        }
						
						$query = "	SELECT `organisation_id` FROM `courses` WHERE `course_id` = ".$db->qstr($results[0]["course_id"]);
						$org_id = $db->GetOne($query);
						list($course_objectives,$top_level_id) = courses_fetch_objectives($org_id,$course_ids, -1,1, false, false, 0, true);
					}
					
									
					// Error Checking
					switch($STEP) {
						case 2 :
							/**
							 * The "course" page type is meant to have more static unchangeable poperties than
							 * a normal page, so the page_type, menu_title, permissions, and page_title
							 * will not be set when the page type is currently set to "course"
							 */
							if ($PAGE_TYPE != "course") {
								/**
								 * Required field "page_type" / Page Type (Unchangeable for course content pages).
								 */
								foreach ($PAGE_TYPES as $PAGE) {
									if ((isset($_POST["page_type"])) && (is_array($PAGE)) && (array_search(trim($_POST["page_type"]), $PAGE))) {
										$PROCESSED["page_type"] = trim($_POST["page_type"]);
										break;
									}
								}
								if (!array_key_exists("page_type", $PROCESSED)) {
									$ERROR++;
									$ERRORSTR[] = "The <strong>Page Type</strong> field is required and is either empty or an invalid value.";
								}

	
								/**
								 * Required field "parent_id" / Page Parent.
								 */
								if (isset($_POST["parent_id"]) && !$home_page) {
									if ($parent_id = clean_input($_POST["parent_id"], array("trim", "int"))) {
										$PROCESSED["parent_id"] = $parent_id;
									} else {
										$PROCESSED["parent_id"] = 0;
									}
								} else {
									$PROCESSED["parent_id"] = 0;
								}
		
								/**
								 * Required field "menu_title" / Menu Title.
								 *  note: page_url is not changed for home page
								 */
								if ((isset($_POST["menu_title"])) && ($menu_title = clean_input($_POST["menu_title"], array("trim", "notags")))) {
									$PROCESSED["menu_title"] = $menu_title;
									
									if (($PROCESSED["menu_title"] != $page_details["menu_title"]) && !$home_page) {
										$page_url = clean_input($PROCESSED["menu_title"], array("lower","underscores","page_url"));
										if ($page_details["parent_id"] != 0) {
											$query = "SELECT `page_url` FROM `community_pages` WHERE `cpage_id` = ".$db->qstr($page_details["parent_id"]);
											if ($parent_url = $db->GetOne($query)) {
												$page_url = $parent_url . "/" . $page_url;
											}
										}
										if (in_array($page_url, $COMMUNITY_RESERVED_PAGES)) {
											$ERROR++;
											$ERRORSTR[] = "The <strong>Menu Title</strong> you have chosen is reserved. Please enter a new menu title.";
										} else {
											$query	= "SELECT * FROM `community_pages` WHERE `page_url` = ".$db->qstr($page_url)." AND `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `cpage_id` != ".$db->qstr($PAGE_ID);
											$result	= $db->GetRow($query);
											if ($result) {
												$ERROR++;
												$ERRORSTR[] = "A similar <strong>Menu Title</strong> already exists in this community; menu titles must be unique.";
											} else {
												$PROCESSED["page_url"] = $page_url;
											}
										}									
									}
								} else {
									$ERROR++;
									$ERRORSTR[] = "The <strong>Menu Title</strong> field is required.";
								}
								
								/**
								 * Non-required fields view page access for members, non-members and public
								 *  - cannot be changed for home page
								 */
								if (!$home_page) {
									if ((isset($_POST["allow_member_view"])) && ((int) $_POST["allow_member_view"])) {
										$PROCESSED["allow_member_view"] = 1;
									} else {
										$PROCESSED["allow_member_view"] = 0;
									}
									if (!(int) $community_details["community_registration"]) {
										if ((isset($_POST["allow_troll_view"])) && ((int) $_POST["allow_troll_view"])) {
											$PROCESSED["allow_troll_view"] = 1;
										} else {
											$PROCESSED["allow_troll_view"] = 0;
										}
									}
									if (!(int) $community_details["community_protected"]) {
										if ((isset($_POST["allow_public_view"])) && ((int) $_POST["allow_public_view"])) {
											$PROCESSED["allow_public_view"] = 1;
										} else {
											$PROCESSED["allow_public_view"] = 0;
										}
									}
								}
		
								/**
								 * Non-required field "page_title" / Page Title.
								 */
								if ((isset($_POST["page_title"])) && ($page_title = clean_input($_POST["page_title"], array("trim", "notags")))) {
									$PROCESSED["page_title"] = $page_title;
								} else {
									$PROCESSED["page_title"] = "";
								}
							}
	
							/**
							 * If the page type is an external URL the data will come in from a different field than if it is
							 * another type of page that actually holds content.
							 */
							if ($PAGE_TYPE == "url") {
								/**
								 * Required "page_url" / Page URL.
								 */
								if ((isset($_POST["page_content"])) && ($page_content = clean_input($_POST["page_content"], array("nows", "notags")))) {
									if (preg_match("/[\w]{3,5}[\:]{1}[\/]{2}/", $page_content) == 1) {
										$PROCESSED["page_content"] = $page_content;
									} else {
										$PROCESSED["page_content"] = "http://" . $page_content;
									}
								} else {
									$ERROR++;
									$ERRORSTR[] = "The <strong>External URL</strong> field is required, please enter a valid website address.";
								}
							} elseif ($PAGE_TYPE == "default") {
								/**
								 * Non-Required "page_content" / Page Contents.
								 */
								if (isset($_POST["page_content"]) && (trim($_POST["page_content"]))) {
									$PROCESSED["page_content"]	= clean_input($_POST["page_content"], array("trim", "allowedtags"));
								} else {
									$PROCESSED["page_content"]	= "";
									
									$NOTICE++;
									$NOTICESTR[] = "The <strong>Page Content</strong> field is empty, which means that nothing will show up on this page.";
								}
							} else {
								/**
								 * Non-Required "page_content" / Page Contents.
								 */
								if (isset($_POST["page_content"])) {
									$PROCESSED["page_content"] = clean_input($_POST["page_content"], array("trim", "allowedtags"));
								} else {
									$PROCESSED["page_content"] = "";	
								}
							}
							if (!$home_page && $PAGE_TYPE != "course") {
								if ($_POST["page_visibile"] == '0') {
									$PROCESSED["page_visible"] = 0;
								} else {
									$PROCESSED["page_visible"] = 1;
								}
							}

							if (!$ERROR) {
								if ($PAGE_TYPE != "course" && !$home_page) {
									/**
									 * Non-required "page_order" / Page Position.
									 * This special field will change the order which this page will appear under the parent.
									 * Don't get confused though, because page_order isn't the actual number, you've still got
									 * to do some math ;)
									 * 
									 * note: this is never changed for the home page, it should always be 0.
									 */
									if ((isset($_POST["page_order"])) && ($_POST["page_order"] != "no") && !$home_page) {
										$page_order = clean_input($_POST["page_order"], array("trim", "int"));
										if ($page_order == 0) {
											$first_available = $db->GetOne("SELECT MAX(`page_order`) FROM `community_pages` WHERE `page_type` = 'course' AND `community_id` = ".$db->qstr($COMMUNITY_ID));
											if ($first_available) {
												$page_order = (int)$first_available + 1;
											}
										}
			
										if ($PROCESSED["parent_id"] == $page_details["parent_id"]) {
											
											$PROCESSED["page_order"] = $page_order;
	
											/**
											 * Go through this process the first time to put each page in the proper order.
											 */
											$query		= "SELECT `cpage_id`, `page_order` FROM `community_pages` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `parent_id` = ".$db->qstr($PROCESSED["parent_id"])." AND `page_order` >= ".$PROCESSED["page_order"]." AND `page_url` != '' ORDER BY `page_order` ASC";
											$results	= $db->GetAll($query);
											if ($results) {
												foreach ($results as $result) {
													$query = "UPDATE `community_pages` SET `page_order` = ".$db->qstr(($result["page_order"] + 1))." WHERE `cpage_id` = ".$db->qstr($result["cpage_id"]);
													if (!$db->Execute($query)) {
														application_log("error", "Unable to update the page order of page_id ".$result["page_id"]);
													}
												}
											}
										} else {
											$NOTICE++;
											$NOTICESTR[] = "You cannot update the <strong>Page Position</strong> of this page if you have also changed the <strong>Page Parent</strong>. The new page position was disregarded.";								
										}
									}
		
									if ($PROCESSED["parent_id"] != $page_details["parent_id"] && !$home_page) {
										$query	= "SELECT COUNT(*) AS `new_order` FROM `community_pages` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `parent_id` = ".$db->qstr($PROCESSED["parent_id"])." AND `page_url` != ''";
										$result	= $db->GetRow($query);
										if ($result) {
											$PROCESSED["page_order"] = (int) $result["new_order"];
										} else {
											$PROCESSED["page_order"] = 0;
										}
										
										$query			= "SELECT `cpage_id`, `page_order` FROM `community_pages` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `parent_id` = ".$db->qstr($page_details["parent_id"])." AND `page_order` > ".$db->qstr($page_details["page_order"])." AND `page_url` != ''";
										$moving_pages	= $db->GetAll($query);
										if ($moving_pages) {
											foreach ($moving_pages as $moving_page) {
												$query = "UPDATE `community_pages` SET `page_order` = ".$db->qstr($moving_page["page_order"] - 1)." WHERE `cpage_id` = ".$db->qstr($moving_page["cpage_id"]);
												$db->Execute($query);
											}
										}
									}
								}
								if ($home_page) {
									/**
									 * Non-required fields for various page options of what to display on default home pages
									 */
									if ((isset($_POST["show_announcements"])) && ((int) $_POST["show_announcements"])) {
										$page_options["show_announcements"]["option_value"] = 1;
									} else {
										$page_options["show_announcements"]["option_value"] = 0;
									}
									if ((isset($_POST["show_events"])) && ((int) $_POST["show_events"])) {
										$page_options["show_events"]["option_value"] = 1;
									} else {
										$page_options["show_events"]["option_value"] = 0;
									}	
									if ((isset($_POST["show_history"])) && ((int) $_POST["show_history"])) {
										$page_options["show_history"]["option_value"] = 1;
									} else {
										$page_options["show_history"]["option_value"] = 0;
									}	
								} elseif ($PAGE_TYPE ==  "announcements" || $PAGE_TYPE == "events") {
									/**
									 * Non-required fields for various page options of what to display on default home pages
									 */
									if ((isset($_POST["allow_member_posts"])) && ((int) $_POST["allow_member_posts"])) {
										$page_options["allow_member_posts"]["option_value"] = 1;
									} else {
										$page_options["allow_member_posts"]["option_value"] = 0;
									}
									if ((isset($_POST["allow_troll_posts"])) && ((int) $_POST["allow_troll_posts"])) {
										$page_options["allow_troll_posts"]["option_value"] = 1;
									} else {
										$page_options["allow_troll_posts"]["option_value"] = 0;
									}	
									if ((isset($_POST["moderate_posts"])) && ((int) $_POST["moderate_posts"])) {
										$page_options["moderate_posts"]["option_value"] = 1;
									} else {
										$page_options["moderate_posts"]["option_value"] = 0;
									}	
								} elseif ($PAGE_TYPE ==  "url") {
									/**
									 * Non-required fields for various page options of what to display on default home pages
									 */
									if ((isset($_POST["new_window"])) && ((int) $_POST["new_window"])) {
										$page_options["new_window"]["option_value"] = 1;
									} else {
										$page_options["new_window"]["option_value"] = 0;
									}	
								}
		
								/**
								 * page_id
								 * community_id
								 * page_type
								 * page_order
								 * page_url
								 * menu_title
								 * page_title
								 * page_content
								 * page_visible
								 * allow_member_view
								 * allow_troll_view
								 * allow_public_view
								 * updated_by
								 * updated_date
								 */
								$PROCESSED["updated_date"]	= time();
								$PROCESSED["updated_by"]	= $ENTRADA_USER->getID();
								
								if ($db->AutoExecute("community_pages", $PROCESSED, "UPDATE", "cpage_id = ".$PAGE_ID)) {
									if ($home_page) {
										if ($PAGE_TYPE == "default" || $PAGE_TYPE == "course") {
											if ($db->Execute("UPDATE `community_page_options` SET `option_value` = ".$db->qstr($page_options["show_announcements"]["option_value"])." WHERE `cpoption_id` = ".$db->qstr($page_options["show_announcements"]["cpoption_id"]))) {
												if ($db->Execute("UPDATE `community_page_options` SET `option_value` = ".$db->qstr($page_options["show_events"]["option_value"])." WHERE `cpoption_id` = ".$db->qstr($page_options["show_events"]["cpoption_id"]))) {
													if ($db->Execute("UPDATE `community_page_options` SET `option_value` = ".$db->qstr($page_options["show_history"]["option_value"])." WHERE `cpoption_id` = ".$db->qstr($page_options["show_history"]["cpoption_id"]))) {
														if (!$ERROR) {
															communities_log_history($COMMUNITY_ID, 0, 0, "community_history_edit_home_page", 1);
															$url = ENTRADA_URL."/community".$community_details["community_url"].":pages";
							
															$SUCCESS++;
															$SUCCESSSTR[]	= "You have successfully updated the home page of the community.<br /><br />You will now be redirected to the page management index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
							
															$HEAD[]			= "<script type=\"text/javascript\"> setTimeout('window.location=\\'".$url."\\'', 5000); </script>";
							
															application_log("success", "Home Page [".$PAGE_ID."] updated in the system.");
														}
													} else {
														$ERROR++;
														$ERRORSTR[] = "There was a problem updating the 'show history' option for the home page of the community. Please contact the application administrator and inform them of this error.";
							
														application_log("error", "There was an error updating this page option. Database said: ".$db->ErrorMsg());
													}
												} else {
													$ERROR++;
													$ERRORSTR[] = "There was a problem updating the 'show events' option for the home page of the community. Please contact the application administrator and inform them of this error.";
						
													application_log("error", "There was an error updating this page option. Database said: ".$db->ErrorMsg());
												}
											} else {
												$ERROR++;
												$ERRORSTR[] = "There was a problem updating the 'show announcements' option for the home page of the community. Please contact the application administrator and inform them of this error.";
					
												application_log("error", "There was an error updating this page option. Database said: ".$db->ErrorMsg());
											}
										} elseif ($PAGE_TYPE == "announcements" || $PAGE_TYPE == "events") { 
											if ($db->Execute("UPDATE `community_page_options` SET `option_value` = ".$db->qstr($page_options["moderate_posts"]["option_value"])." WHERE `cpoption_id` = ".$db->qstr($page_options["moderate_posts"]["cpoption_id"]))) {
												if ($db->Execute("UPDATE `community_page_options` SET `option_value` = ".$db->qstr($page_options["allow_member_posts"]["option_value"])." WHERE `cpoption_id` = ".$db->qstr($page_options["allow_member_posts"]["cpoption_id"]))) {
													if ($db->Execute("UPDATE `community_page_options` SET `option_value` = ".$db->qstr($page_options["allow_troll_posts"]["option_value"])." WHERE `cpoption_id` = ".$db->qstr($page_options["allow_troll_posts"]["cpoption_id"]))) {
														if (!$ERROR) {
															
															$url = ENTRADA_URL."/community".$community_details["community_url"].":pages";
							
															$SUCCESS++;
															$SUCCESSSTR[]	= "You have successfully updated the home page of the community.<br /><br />You will now be redirected to the page management index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
							
															$HEAD[]			= "<script type=\"text/javascript\"> setTimeout('window.location=\\'".$url."\\'', 5000); </script>";
							
															application_log("success", "Home Page [".$PAGE_ID."] updated in the system.");
														}
													} else {
														$ERROR++;
														$ERRORSTR[] = "There was a problem updating the 'moderate posts' option for the home page of the community. Please contact the application administrator and inform them of this error.";
							
														application_log("error", "There was an error updating this page option. Database said: ".$db->ErrorMsg());
													}
												} else {
													$ERROR++;
													$ERRORSTR[] = "There was a problem updating the 'allow member posts' option for the home page of the community. Please contact the application administrator and inform them of this error.";
						
													application_log("error", "There was an error updating this page option. Database said: ".$db->ErrorMsg());
												}
											} else {
												$ERROR++;
												$ERRORSTR[] = "There was a problem updating the 'allow non-member posts' option for the home page of the community. Please contact the application administrator and inform them of this error.";
					
												application_log("error", "There was an error updating this page option. Database said: ".$db->ErrorMsg());
											}
										} else {													
											if (!$ERROR) {
												$url = ENTRADA_URL."/community".$community_details["community_url"].":pages";
				
												$SUCCESS++;
												$SUCCESSSTR[]	= "You have successfully updated the home page of the community.<br /><br />You will now be redirected to the page management index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
				
												$HEAD[]			= "<script type=\"text/javascript\"> setTimeout('window.location=\\'".$url."\\'', 5000); </script>";
				
												application_log("success", "Home Page [".$PAGE_ID."] updated in the system.");
											}
										}
									} else {
										communities_log_history($COMMUNITY_ID, $PAGE_ID, 0, "community_history_edit_page", 1);
										if ($PROCESSED["menu_title"] != $page_details["menu_title"]) {
											communities_set_children_urls($PAGE_ID, $PROCESSED["page_url"]);
										}
										if ((isset($PROCESSED["page_order"])) && ($PROCESSED["page_order"] != $page_details["page_order"])) {
											/**
											 * Go through this process the second time to ensure each page is in the correct order.
											 */
											$query		= "SELECT `cpage_id`, `page_order` FROM `community_pages` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `parent_id` = ".$db->qstr($PROCESSED["parent_id"])." AND `page_url` != '' ORDER BY `page_order` ASC";
											$results	= $db->GetAll($query);
											if ($results) {
												foreach ($results as $key => $result) {
													$order = $key;
													if ((int) $order != (int) $result["page_order"]) {
														$query = "UPDATE `community_pages` SET `page_order` = ".$db->qstr($order)." WHERE `cpage_id` = ".$db->qstr($result["cpage_id"]);
														if (!$db->Execute($query)) {
															application_log("error", "Unable to update the page order of page_id ".$result["page_id"]);
														}
													}
												}
											}
										}
										if ($PAGE_TYPE == "announcements" || $PAGE_TYPE == "events") {
											if ($db->Execute("UPDATE `community_page_options` SET `option_value` = ".$db->qstr($page_options["moderate_posts"]["option_value"])." WHERE `cpoption_id` = ".$db->qstr($page_options["moderate_posts"]["cpoption_id"]))) {
												if ($db->Execute("UPDATE `community_page_options` SET `option_value` = ".$db->qstr($page_options["allow_member_posts"]["option_value"])." WHERE `cpoption_id` = ".$db->qstr($page_options["allow_member_posts"]["cpoption_id"]))) {
													if ($db->Execute("UPDATE `community_page_options` SET `option_value` = ".$db->qstr($page_options["allow_troll_posts"]["option_value"])." WHERE `cpoption_id` = ".$db->qstr($page_options["allow_troll_posts"]["cpoption_id"]))) {
														if (!$ERROR) {
															$url = ENTRADA_URL."/community".$community_details["community_url"].":pages";
							
															$SUCCESS++;
															$SUCCESSSTR[]	= "You have successfully updated this page of the community.<br /><br />You will now be redirected to the page management index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
							
															$HEAD[]			= "<script type=\"text/javascript\"> setTimeout('window.location=\\'".$url."\\'', 5000); </script>";
							
															application_log("success", "Page [".$PAGE_ID."] updated in the system.");
														}
													} else {
														$ERROR++;
														$ERRORSTR[] = "There was a problem updating the 'moderate posts' option for this page of the community. Please contact the application administrator and inform them of this error.";
							
														application_log("error", "There was an error updating this page option. Database said: ".$db->ErrorMsg());
													}
												} else {
													$ERROR++;
													$ERRORSTR[] = "There was a problem updating the 'allow member posts' option this page of the community. Please contact the application administrator and inform them of this error.";
						
													application_log("error", "There was an error updating this page option. Database said: ".$db->ErrorMsg());
												}
											} else {
												$ERROR++;
												$ERRORSTR[] = "There was a problem updating the 'allow non-member posts' option for this page of the community. Please contact the application administrator and inform them of this error.";
					
												application_log("error", "There was an error updating this page option. Database said: ".$db->ErrorMsg());
											}
										} elseif ($PAGE_TYPE == "course" && $page_details["page_url"] == "objectives") {
											foreach ($course_ids as $course_id) {
												if (isset($_POST["course_objectives"]) && ($objectives = $_POST["course_objectives"]) && (is_array($objectives))) {
													foreach ($objectives as $objective => $status) {
														if ($objective) {
															if (isset($_POST["objective_text"][$objective]) && $_POST["objective_text"][$objective]) {
																$objective_text = $_POST["objective_text"][$objective];
															} else {
																$objective_text = false;
															}
															$PROCESSED_OBJECTIVES[$objective] = $objective_text;
														}
													}
												}
												if (is_array($PROCESSED_OBJECTIVES)) {
													foreach ($PROCESSED_OBJECTIVES as $objective_id => $objective) {
														$objective_found = $db->GetOne("SELECT `objective_id` FROM `course_objectives` WHERE `objective_id` = ".$db->qstr($objective_id)." AND `course_id` = ".$db->qstr($course_id));
														if ($objective_found) {
															$db->AutoExecute("course_objectives", array("objective_details" => $objective, "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID()), "UPDATE", "`objective_id` = ".$db->qstr($objective_id)." AND `course_id` = ".$db->qstr($course_id));
														} else {
															$db->AutoExecute("course_objectives", array("course_id" => $course_id, "objective_id" => $objective_id, "objective_details" => $objective, "importance" => 0, "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID()), "INSERT");
														}
													}
													foreach ($course_objectives["used_ids"] as $objective_id) {
														if (!array_key_exists($objective_id, $PROCESSED_OBJECTIVES)) {
															$db->AutoExecute("course_objectives", array("objective_details" => "", "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID()), "UPDATE", "`objective_id` = ".$db->qstr($objective_id)." AND `course_id` = ".$db->qstr($course_id));
														}
													}
												}
											}
											if (!$ERROR) {
												$url = ENTRADA_URL."/community".$community_details["community_url"].":pages";
				
												$SUCCESS++;
												$SUCCESSSTR[]	= "You have successfully updated this page of the community.<br /><br />You will now be redirected to the page management index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
				
												$HEAD[]			= "<script type=\"text/javascript\"> setTimeout('window.location=\\'".$url."\\'', 5000); </script>";
				
												application_log("success", "Page [".$PAGE_ID."] updated in the system.");
											}
										} elseif ($PAGE_TYPE == "url") { 
											if ($db->Execute("UPDATE `community_page_options` SET `option_value` = ".$db->qstr($page_options["new_window"]["option_value"])." WHERE `cpoption_id` = ".$db->qstr($page_options["new_window"]["cpoption_id"]))) {
												if (!$ERROR) {
													$url = ENTRADA_URL."/community".$community_details["community_url"].":pages";
					
													$SUCCESS++;
													$SUCCESSSTR[]	= "You have successfully updated this page of the community.<br /><br />You will now be redirected to the page management index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
					
													$HEAD[]			= "<script type=\"text/javascript\"> setTimeout('window.location=\\'".$url."\\'', 5000); </script>";
					
													application_log("success", "Page [".$PAGE_ID."] updated in the system.");
												}
											} else {
												$ERROR++;
												$ERRORSTR[] = "There was a problem updating the 'open in new window' option for the current page in the community. The application administrator has been informed them of this error.";
					
												application_log("error", "There was an error updating this page option. Database said: ".$db->ErrorMsg());
											}
										} elseif (!$ERROR) {
											$url = ENTRADA_URL."/community".$community_details["community_url"].":pages";
			
											$SUCCESS++;
											$SUCCESSSTR[]	= "You have successfully updated this page of the community.<br /><br />You will now be redirected to the page management index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
			
											$HEAD[]			= "<script type=\"text/javascript\"> setTimeout('window.location=\\'".$url."\\'', 5000); </script>";
			
											application_log("success", "Page [".$PAGE_ID."] updated in the system.");
										}
									}
								} else {
									$ERROR++;
									$ERRORSTR[] = "There was a problem updating this page of the community. The application administrator has been informed them of this error.";
		
									application_log("error", "There was an error updating this page. Database said: ".$db->ErrorMsg());
								}
							}
		
							if ($ERROR) {
								$STEP = 1;
							}
						break;
						case 1 :
						default :
							$PROCESSED = $page_details;
		
							if ((isset($PAGE_TYPE)) && ($PAGE_TYPE != "")) {
								$PROCESSED["page_type"] = $PAGE_TYPE;
							}
						break;
					}
				
					//Display Page
					switch($STEP) {
						case 2 :
							if ($NOTICE) {
								echo display_notice();
							}
							
							if ($SUCCESS) {
								echo display_success();
							}
							
							if ($ERROR) {
								echo display_error();
							}
						break;
						case 1:
						default:
							if ($NOTICE) {
								echo display_notice();
							}

							if ($ERROR) {
								echo display_error();
							}
							
							if ($page_type == "course" && $page_details["page_url"] == "objectives") {
								require_once("../javascript/courses.js.php");
							}
							?>
							<script type="text/javascript">
							var text = new Array();
							
							function objectiveClick(element, id, default_text) {
								if (element.checked) {
									var textarea = document.createElement('textarea');
									textarea.name = 'objective_text['+id+']';
									textarea.id = 'objective_text_'+id;
									if (text[id] != null) {
										textarea.innerHTML = text[id];
									} else {
										textarea.innerHTML = default_text;
									}
									textarea.className = "expandable objective";
									$('objective_'+id+'_append').insert({after: textarea});
									setTimeout('new ExpandableTextarea($("objective_text_'+id+'"));', 100);
								} else {
									if ($('objective_text_'+id)) {
										text[id] = $('objective_text_'+id).value;
										$('objective_text_'+id).remove();
									}
								}
							}
							</script>
							<form action="<?php echo ENTRADA_URL."/community".$community_details["community_url"].":pages?".replace_query(array("action" => "edit", "step" => 2)); ?>" method="post" enctype="multipart/form-data" onsubmit="selIt()">
							<table style="width: 95%;" cellspacing="0" cellpadding="2" border="0" summary="Editing Page">
							<colgroup>
								<col style="width: 30%" />
								<col style="width: 70%" />
							</colgroup>
							<thead>
							<thead>
								<tr>
									<td colspan="2"><h1>Edit Community Page</h1></td>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<td colspan="2" style="padding-top: 15px; text-align: right">
                                        <input type="submit" class="button" value="<?php echo $translate->_("global_button_save"); ?>" />                              
									</td>
								</tr>
							</tfoot>
							<tbody>
								<tr>
									<td style="vertical-align: top"><label for="page_type" class="form-required">Page Type:</label></td>
									<td style="vertical-align: top">
										<?php
										if ((is_array($PAGE_TYPES)) && (count($PAGE_TYPES))) {
											echo "<select id=\"page_type\" name=\"page_type\" style=\"width: 204px\" onchange=\"window.location = '".COMMUNITY_URL.$COMMUNITY_URL.":pages?section=edit&page=".($home_page ? "home" : $PAGE_ID)."&type='+this.options[this.selectedIndex].value\" ".($PAGE_TYPE == "course" ? "disabled=\"disabled\" " : "").">\n";
											foreach ($PAGE_TYPES as $page_type_info) {
												echo "<option value=\"".html_encode($page_type_info["module_shortname"])."\"".(((isset($PAGE_TYPE)) && ($PAGE_TYPE == $page_type_info["module_shortname"])) || ((isset($page_details["page_type"])) && !isset($PAGE_TYPE) && ($page_details["page_type"] == $page_type_info["module_shortname"])) ? " selected=\"selected\"" : "").">".html_encode($page_type_info["module_title"])."</option>\n";
											}
											if ($PAGE_TYPE == "course") {
												echo "<option value=\"course\" selected=\"selected\">Course Content Page</option>\n";
											}
											echo "</select>";
											if (isset($PAGE_TYPES[$page_details["page_type"]]["description"])) {
												echo "<div class=\"content-small\" style=\"margin-top: 5px\">\n";
												echo "<strong>Page Type Description:</strong><br />".html_encode($PAGE_TYPES[$page_details["page_type"]]["description"]);
												echo "</div>\n";
											}
										} else {
											echo "<input type=\"hidden\" name=\"page_type\" value=\"default\" />\n";
											echo "<strong>Default Content Page</strong>\n";
						
											application_log("error", "No available page types during content page add or edit.");
										}
										?>
									</td>
								</tr>
								<?php 
								if (!$home_page && $PAGE_TYPE != "course") {
										?>
									<tr>
										<td colspan="2">&nbsp;</td>
									</tr>
									<tr>
										<td><label for="parent_id" class="form-required">Page Parent:</label></td>
										<td>
											<select id="parent_id" name="parent_id" onchange="if (this.value == <?php echo $PROCESSED["parent_id"]?>) { $('page_order').disabled = false; } else { $('page_order').disabled = true; } " style="width: 304px">
											<?php
											echo "<option value=\"0\" selected=\"selected\">-- No Parent Page --</option>\n";
											
											$current_selected	= array($page_details["parent_id"]);
											$exclude			= array($PAGE_ID);
											
											echo communities_pages_inselect(0, $current_selected, 0, $exclude, $COMMUNITY_ID);
											?>
											</select>
										</td>
									</tr>
									<?php
								}
								?>
								<tr>
									<td colspan="2">&nbsp;</td>
								</tr>
								<tr>
									<td><label for="menu_title" class="form-required">Menu Title:</label></td>
									<td><input type="text" id="menu_title" name="menu_title" value="<?php echo ((isset($PROCESSED["menu_title"])) ? html_encode($PROCESSED["menu_title"]) : ((isset($page_details["menu_title"])) ? html_encode($page_details["menu_title"]) : "")); ?>" maxlength="32" style="width: 300px" onblur="fieldCopy('menu_title', 'page_title', 1)"<?php echo ($PAGE_TYPE == "course" ? " disabled=\"disabled\"" : ""); ?> /></td>
								</tr>
								<tr>
									<td><label for="page_title" class="form-nrequired">Page Title:</label></td>
									<td><input type="text" id="page_title" name="page_title" value="<?php echo ((isset($PROCESSED["page_title"])) ? html_encode($PROCESSED["page_title"]) : ""); ?>" maxlength="100" style="width: 300px"<?php echo ($PAGE_TYPE == "course" ? " disabled=\"disabled\"" : ""); ?> /></td>
								</tr>
								<tr>
									<td colspan="2">&nbsp;</td>
								</tr>
								<?php
								if ($PAGE_TYPE == "url") {
									?>
									<tr>
										<td><label for="page_content" class="form-required">External URL:</label></td>
										<td>
											<input type="textbox" id="page_content" name="page_content" style="width: 99%" value="<?php echo (((isset($PROCESSED["page_content"])) && ($page_details["page_type"] == "url")) ? html_encode($PROCESSED["page_content"]) : ""); ?>">
										</td>
									</tr>
									<?php
								} else {
									?>
									<tr>
										<td colspan="2"><label for="page_content" class="form-nrequired"><?php (($PAGE_TYPE != "default") ? "Top of " : ""); ?>Page Content:</label></td>
									</tr>
									<tr>
										<td colspan="2">
											<textarea id="page_content" name="page_content" style="margin-right: 10px;width: 95%; height: <?php echo (($PAGE_TYPE == "default") ? "400" : "200"); ?>px" rows="20" cols="70"><?php echo ((isset($PROCESSED["page_content"])) ? html_encode($PROCESSED["page_content"]) : ""); ?></textarea>
										</td>
									</tr>
									<?php
								} 
								if (!$home_page && $PAGE_TYPE != "course") {
									if ($PAGE_TYPE == "events" || $PAGE_TYPE == "announcements") {
										?>
										<tr>
											<td colspan="2">&nbsp;</td>
										</tr>
										<tr>
											<td colspan="2"><h2>Page Permissions</h2></td>
										</tr>
										<tr>
											<td colspan="2">
												<table class="permissions" style="width: 100%" cellspacing="0" cellpadding="0" border="0">
												<colgroup>
													<col style="width: 40%" />
													<col style="width: 20%" />
													<col style="width: 25%" />
													<col style="width: 15%" />
												</colgroup>
												<thead>
													<tr>
														<td>Group</td>
														<td style="border-left: none">View Page</td>
														<td style="border-left: none">Post <?php echo ucfirst($PAGE_TYPE); ?></td>
														<td style="border-left: none">&nbsp;</td>
													</tr>
												</thead>
												<tbody>
													<tr>
														<td class="left"><strong>Community Administrators</strong></td>
														<td class="on"><input type="checkbox" id="allow_admin_view" name="allow_admin_view" value="1" checked="checked" onclick="this.checked = true" /></td>
														<td><input type="checkbox" checked="checked" disabled="disabled"/></td>
														<td>&nbsp;</td>
													</tr>
													<tr>
														<td class="left"><strong>Community Members</strong></td>
														<td class="on"><input type="checkbox" id="allow_member_view" name="allow_member_view" value="1"<?php echo (((!isset($PROCESSED["allow_member_view"])) || ((isset($PROCESSED["allow_member_view"])) && ($PROCESSED["allow_member_view"] == 1))) ? " checked=\"checked\"" : ""); ?> /></td>
														<td><input onclick="show_hide_moderation()" type="checkbox" id="allow_member_posts" name="allow_member_posts" value="1"<?php echo (((isset($page_options["allow_member_posts"]["option_value"])) && (((int)$page_options["allow_member_posts"]["option_value"]) == 1)) ? " checked=\"checked\"" : ""); ?> /></td>
														<td>&nbsp;</td>
													</tr>
													<?php if (!(int) $community_details["community_registration"]) : ?>
													<tr>
														<td class="left"><strong>Browsing Non-Members</strong></td>
														<td class="on"><input type="checkbox" id="allow_troll_view" name="allow_troll_view" value="1"<?php echo (((!isset($PROCESSED["allow_troll_view"])) || ((isset($PROCESSED["allow_troll_view"])) && ($PROCESSED["allow_troll_view"] == 1))) ? " checked=\"checked\"" : ""); ?> /></td>
														<td><input onclick="show_hide_moderation()" type="checkbox" id="allow_troll_posts" name="allow_troll_posts" value="1"<?php echo (((isset($page_options["allow_troll_posts"]["option_value"])) && (((int)$page_options["allow_troll_posts"]["option_value"]) == 1)) ? " checked=\"checked\"" : ""); ?> /></td>
														<td>&nbsp;</td>
													</tr>
													<?php endif; ?>
													<?php if (!(int) $community_details["community_protected"]) :  ?>
													<tr>
														<td class="left"><strong>Non-Authenticated / Public Users</strong></td>
														<td class="on"><input type="checkbox" id="allow_public_view" name="allow_public_view" value="1"<?php echo (((isset($PROCESSED["allow_public_view"])) && ($PROCESSED["allow_public_view"] == 1)) ? " checked=\"checked\"" : ""); ?> /></td>
														<td><input type="checkbox" disabled="disabled"/></td>
														<td>&nbsp;</td>
													</tr>
													<?php endif; ?>
												</tbody>
												</table>
											</td>
										</tr>
										</tbody>
										<script type="text/javascript">
											function show_hide_moderation() {
												if($('allow_member_posts').checked || ($('allow_troll_posts') && $('allow_troll_posts').checked)) {
													if (!$('moderate-posts-body').visible()) {
														Effect.BlindDown($('moderate-posts-body'), { duration: 0.3 }); 
													}
												} else { 
													if ($('moderate-posts-body').visible()) {
														Effect.BlindUp($('moderate-posts-body'), { duration: 0.3 }); 
													}
												}
											}
										</script>
										<tbody id="moderate-posts-body" <?php echo ((((isset($page_options["allow_troll_posts"]["option_value"])) && (((int)$page_options["allow_troll_posts"]["option_value"]) == 1)) && (!(int) $community_details["community_protected"])) || ((isset($page_options["allow_member_posts"]["option_value"])) && (((int)$page_options["allow_member_posts"]["option_value"]) == 1)) ? "" : " style=\"display: none;\""); ?>>
											<tr>
												<td colspan="2">
													&nbsp;
												</td>
											</tr>
											<tr>
												<td>
													<label for="moderate_posts" class="form-nrequired">
														Moderation
													</label>
												</td>
												<td>
													<input type="checkbox" id="moderate_posts" name="moderate_posts" value="1"<?php echo (((isset($page_options["moderate_posts"]["option_value"])) && (((int)$page_options["moderate_posts"]["option_value"]) == 1)) ? " checked=\"checked\"" : ""); ?> />
													<span class="content-small">Require non-administrator <?php echo $PAGE_TYPE; ?> to be moderated</span>
												</td>
											</tr>
										</tbody>
										<tbody>
										<?php
									} else {
										?>
										<tr>
											<td colspan="2">&nbsp;</td>
										</tr>
										<tr>
											<td colspan="2"><h2>Page Permissions</h2></td>
										</tr>
										<tr>
											<td colspan="2">
												<table class="permissions" style="width: 100%" cellspacing="0" cellpadding="0" border="0">
												<colgroup>
													<col style="width: 40%" />
													<col style="width: 20%" />
													<col style="width: 40%" />
												</colgroup>
												<thead>
													<tr>
														<td>Group</td>
														<td style="border-left: none">View Page</td>
														<td style="border-left: none">&nbsp;</td>
													</tr>
												</thead>
												<tbody>
													<tr>
														<td class="left"><strong>Community Administrators</strong></td>
														<td class="on"><input type="checkbox" id="allow_admin_view" name="allow_admin_view" value="1" checked="checked" onclick="this.checked = true" /></td>
														<td>&nbsp;</td>
													</tr>
													<tr>
														<td class="left"><strong>Community Members</strong></td>
														<td class="on"><input type="checkbox" id="allow_member_view" name="allow_member_view" value="1"<?php echo (((!isset($PROCESSED["allow_member_view"])) || ((isset($PROCESSED["allow_member_view"])) && ($PROCESSED["allow_member_view"] == 1))) ? " checked=\"checked\"" : ""); ?> /></td>
														<td>&nbsp;</td>
													</tr>
													<?php if (!(int) $community_details["community_registration"]) : ?>
													<tr>
														<td class="left"><strong>Browsing Non-Members</strong></td>
														<td class="on"><input type="checkbox" id="allow_troll_view" name="allow_troll_view" value="1"<?php echo (((!isset($PROCESSED["allow_troll_view"])) || ((isset($PROCESSED["allow_troll_view"])) && ($PROCESSED["allow_troll_view"] == 1))) ? " checked=\"checked\"" : ""); ?> /></td>
														<td>&nbsp;</td>
													</tr>
													<?php endif; ?>
													<?php if (!(int) $community_details["community_protected"]) :  ?>
													<tr>
														<td class="left"><strong>Non-Authenticated / Public Users</strong></td>
														<td class="on"><input type="checkbox" id="allow_public_view" name="allow_public_view" value="1"<?php echo (((isset($PROCESSED["allow_public_view"])) && ($PROCESSED["allow_public_view"] == 1)) ? " checked=\"checked\"" : ""); ?> /></td>
														<td>&nbsp;</td>
													</tr>
													<?php endif; ?>
												</tbody>
												</table>
											</td>
										</tr>
										<?php
									}
								} elseif ($PAGE_TYPE == "course" && strpos($page_record["page_url"], "objectives") !== false) {
									if ((is_array($course_objectives["primary_ids"]) && count($course_objectives["primary_ids"])) || (is_array($course_objectives["secondary_ids"]) && count($course_objectives["secondary_ids"])) || (is_array($course_objectives["tertiary_ids"]) && count($course_objectives["tertiary_ids"]))) {
										?>
										<tr>
											<td colspan="2">
												<br />
												<h1 style="font-size: 17px;" title="Course Objectives Section">Curriculum Objectives</h1>
												<form action="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?<?php echo replace_query(); ?>" method="post">
												<input type="hidden" name="type" value="objectives" />
												<input type="hidden" id="objectives_head" name="course_objectives" value="" />
												<?php
												if (is_array($course_objectives["primary_ids"])) {
													foreach ($course_objectives["primary_ids"] as $objective_id) {
														echo "<input type=\"hidden\" class=\"primary_objectives\" id=\"primary_objective_".$objective_id."\" name=\"primary_objectives[]\" value=\"".$objective_id."\" />\n";
													}
												}
												if (is_array($course_objectives["secondary_ids"])) {
													foreach ($course_objectives["secondary_ids"] as $objective_id) {
														echo "<input type=\"hidden\" class=\"secondary_objectives\" id=\"secondary_objective_".$objective_id."\" name=\"secondary_objectives[]\" value=\"".$objective_id."\" />\n";
													}
												}
												if (is_array($course_objectives["tertiary_ids"])) {
													foreach ($course_objectives["tertiary_ids"] as $objective_id) {
														echo "<input type=\"hidden\" class=\"tertiary_objectives\" id=\"tertiary_objective_".$objective_id."\" name=\"tertiary_objectives[]\" value=\"".$objective_id."\" />\n";
													}
												}
												?>
												<table style="width: 100%" cellspacing="0" cellpadding="2" border="0">
												<colgroup>
													<col width="22%" />
													<col width="78%" />
												</colgroup>
												<tbody>
													<tr>
														<td colspan="2">
															<div id="objectives_list">
															<?php echo event_objectives_in_list($course_objectives, $top_level_id,$top_level_id, true); ?>
															</div>
														</td>
													</tr>
												</tbody>
												</table>
												</form>
												<?php
												if ((@is_array($edit_ajax)) && (@count($edit_ajax))) {
													echo "<script type=\"text/javascript\">\n";
													foreach ($edit_ajax as $objective_id) {
														echo "var editor_".$objective_id." = new Ajax.InPlaceEditor('objective_description_".$objective_id."', '".ENTRADA_RELATIVE."/api/objective-details.api.php', { rows: 7, cols: 62, okText: \"Save Changes\", cancelText: \"Cancel Changes\", externalControl: \"edit_mode_".$objective_id."\", submitOnBlur: \"true\", callback: function(form, value) { return 'id=".$objective_id."&cids=".$course_ids_string."&objective_details='+escape(value) } });\n";
													}
													echo "</script>\n";
												}
												?>
											</td>
										</tr>
										<?php
									}
								} elseif ($PAGE_TYPE == "course" && strpos($page_record["page_url"], "mcc_presentations") !== false) {
									$organisation_id = $db->GetOne("SELECT `organisation_id` FROM `courses` WHERE `course_id` = ".$db->qstr($course_ids[0]));
									?>
									<tr>
										<td colspan="2">
											<?php
											echo "<h3>Clinical Presentations</h3>";
											$query = "	SELECT b.*
														FROM `course_objectives` AS a
														JOIN `global_lu_objectives` AS b
														ON a.`objective_id` = b.`objective_id`
														JOIN `objective_organisation` AS c
														ON b.`objective_id` = c.`objective_id`
														WHERE a.`objective_type` = 'event'
														AND b.`objective_active` = '1'
														AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
														AND a.`course_id` IN (".$course_ids_string.")
														GROUP BY b.`objective_id`
														ORDER BY b.`objective_order`";
											$results = $db->GetAll($query);
											if ($results) {
												
												if($ENTRADA_ACL->amIAllowed(new CourseResource($course_ids[0], $organisation_id), "update")) {
													echo "<div class=\"no-printing\">\n";
													echo "	<div style=\"float: right\">\n";
													echo "<a href=\"".ENTRADA_URL."/admin/courses?section=edit&id=".$course_ids[0]."\"><img src=\"".ENTRADA_URL."/images/event-details.gif\" width=\"16\" height=\"16\" alt=\"Edit course details\" title=\"Edit course details\" border=\"0\" style=\"vertical-align: middle; margin-bottom: 2px;\" /></a> <a href=\"".ENTRADA_URL."/admin/courses?section=edit&id=".$course_ids[0]."\" style=\"font-size: 10px; margin-right: 8px\">Edit course details</a>\n";
													echo "	</div>\n";
													echo "</div>\n";
													echo "<br />";
												}
												echo "<ul class=\"objectives\">\n";
												foreach ($results as $result) {
													if ($result["objective_name"]) {
														echo "	<li>".$result["objective_name"]."</li>\n";
													}
												}
												echo "</ul>\n";
											} else {
												if($ENTRADA_ACL->amIAllowed(new CourseResource($course_ids[0], $organisation_id), "update")) {
													echo "<div class=\"display-notice\">While medical presentations may be used to illustrate concepts in this course, there are no specific presentations from the Medical Council of Canada that have been selected. If you would like to add a clinical presentation to this course, please click <a href=\"".ENTRADA_URL."/admin/courses?section=edit&id=".$course_ids[0]."\">here</a>. </div>\n";
												} else {
													echo "<div class=\"display-notice\">While medical presentations may be used to illustrate concepts in this course, there are no specific presentations from the Medical Council of Canada that have been selected. </div>";
												}
											}
											?>
										</td>
									</tr>
									<?php
								}
								?>
								<tr>
									<td colspan="2">&nbsp;</td>
								</tr>
								<?php
								if (($PAGE_TYPE == "url") || (!$home_page && $PAGE_TYPE != "course") || ($home_page && $PAGE_TYPE == "default")) {	
									?>
									<tr>
										<td colspan="2"><h2>Page Options</h2></td>
									</tr>
									<?php
								}
								if ($PAGE_TYPE == "url") {
								?>
									<tr>
										<td colspan="2" style="padding-top: 1em;">
											<table class="page-options" style="width: 95%; padding-bottom: 20px;" cellspacing="0" cellpadding="0" border="0">
											<colgroup>
												<col style="width: 70%" />
												<col style="width: 20%" />
												<col style="width: 10%" />
											</colgroup>
											<thead>
												<tr>
													<td>Additional Options</td>
													<td style="border-left: none">Status</td>
													<td style="border-left: none">&nbsp;</td>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td class="left"><strong>Open page in new window. </strong></td>
													<td class="on"><input type="checkbox" id="new_window" name="new_window" value="1"<?php echo (((isset($page_options["new_window"]["option_value"])) && (((int)$page_options["new_window"]["option_value"]) == 1)) ? " checked=\"checked\"" : ""); ?> /></td>
													<td>&nbsp;</td>
												</tr>
											</tbody>
											</table>
										</td>
									</tr>			
								<?php
								}
								if (!$home_page && $PAGE_TYPE != "course") {
									$query		= "SELECT `cpage_id`, `page_order`, `menu_title` FROM `community_pages` WHERE `cpage_id` <> ".$db->qstr($PAGE_ID)." AND `parent_id` = ".$db->qstr($PROCESSED["parent_id"])." AND `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `page_url` != '' AND `page_type` != 'course' ORDER BY `page_order` ASC";
									$results	= $db->GetAll($query);
									if ($results) {
										?>
										<tr>
											<td><label for="page_order" class="form-nrequired">Page Position:</label></td>
											<td>
												<select id="page_order" name="page_order" style="width: 304px">
													<option value="no">Do Not Move Page</option>
													<?php
													if ((int) $PROCESSED["parent_id"]) {
														?>
														<optgroup label="&rarr; /<?php echo html_encode($PROCESSED["menu_title"]); ?>">
														<?php
													} else {
														?>
														<optgroup label="/">
														<?php
													}
													?>
													<option value="0">Appear First</option>
													<?php
													foreach ($results as $result) {
														echo "<option value=\"".(((int) $result["page_order"]) + 1)."\">After &quot;".html_encode($result["menu_title"])."&quot;</option>\n";
													}
													?>
													</optgroup>
												</select>
											</td>
										</tr>
										<?php
									}							
									?>	
									<tr>
										<td><label for="page_visibile" class="form-nrequired">Page Visibility:</label></td>
										<td>
											<select id="page_visibile" name="page_visibile">
												<option value="1"<?php echo (((int)$PROCESSED["page_visible"]) == 1 ? " selected=\"true\"" : ""); ?>>Show this page on menu</option>
												<option value="0"<?php echo (((int)$PROCESSED["page_visible"]) == 0 ? " selected=\"true\"" : ""); ?>>Hide this page from menu</option>
											</select>
										</td>
									</tr>
									<?php
								} elseif ($home_page) {
								?>
									<tr>
										<td colspan="2" style="padding-top: 1em;">
											<table class="page-options" style="width: 95%" cellspacing="0" cellpadding="0" border="0">
											<colgroup>
												<col style="width: 40%" />
												<col style="width: 30%" />
												<col style="width: 30%" />
											</colgroup>
											<thead>
												<tr>
													<td>Option</td>
													<td style="border-left: none">Additional Options</td>
													<td style="border-left: none">&nbsp;</td>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td class="left"><strong>Show New Announcements</strong></td>
													<td class="on"><input type="checkbox" id="show_announcements" name="show_announcements" value="1"<?php echo (((isset($page_options["show_announcements"]["option_value"])) && (((int)$page_options["show_announcements"]["option_value"]) == 1)) ? " checked=\"checked\"" : ""); ?> /></td>
													<td>&nbsp;</td>
												</tr>
												<tr>
													<td class="left"><strong>Show Upcoming Events</strong></td>
													<td class="on"><input type="checkbox" id="show_events" name="show_events" value="1"<?php echo (((isset($page_options["show_events"]["option_value"])) && (((int)$page_options["show_events"]["option_value"]) == 1)) ? " checked=\"checked\"" : ""); ?> /></td>
													<td>&nbsp;</td>
												</tr>												<tr>
													<td class="left"><strong>Show Community History</strong></td>
													<td class="on"><input type="checkbox" id="show_history" name="show_history" value="1"<?php echo (((isset($page_options["show_history"]["option_value"])) && (((int)$page_options["show_history"]["option_value"]) == 1)) ? " checked=\"checked\"" : ""); ?> /></td>
													<td>&nbsp;</td>
												</tr>
											</tbody>
											</table>
										</td>
									</tr>
								<?php
								}
								?>
							</tbody>
							</table>
							</form>
							<?php
						break;
					}
				} else {
					$url		= COMMUNITY_URL.$COMMUNITY_URL.":pages";
					$ONLOAD[]	= "setTimeout('window.location=\\'".$url."\\'', 5000)";
				
					$ERROR++;
					$ERRORSTR[]	= "The page you have requested does not currently exist within this community.<br /><br />You will now be redirected to the page management index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
					
					echo display_error();
				}
			} else {
				application_log("error", "Someone attempted to access this page who was not a community administrator. (Edit Page)");
				header("Location: ".COMMUNITY_URL.$COMMUNITY_URL.":pages");
				exit;
			}
		} else {
			application_log("error", "The provided pages page_id does not exist in the system. (Edit Page)");
			header("Location: ".COMMUNITY_URL.$COMMUNITY_URL.":pages");
			exit;
		}
	} else {
		application_log("error", "No pages page id was provided to edit. (Edit Page)");
		header("Location: ".COMMUNITY_URL.$COMMUNITY_URL.":pages");
		exit;
	}
}					
?>