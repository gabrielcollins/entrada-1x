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
 * This file looks a bit different because it is called only by AJAX requests
 * and returns the members relevant to the requested group, role, and community.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 * @version $Id: add.inc.php 317 2009-01-19 19:26:35Z simpson $
 *
 */

if((!defined("PARENT_INCLUDED")) || (!defined("IN_COMMUNITIES"))) {
/**
 * @exception 0: Unable to start processing request.
 */
	echo "Authentication error!";
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
/**
 * @exception 0: Unable to start processing request.
 */
	echo "Authentication error!";
	exit;
}

ob_clear_open_buffers();
$COMMUNITY_ID		= 0;

if((isset($_GET["community"])) && ((int) trim($_GET["community"]))) {
	$COMMUNITY_ID	= (int) trim($_GET["community"]);
} elseif((isset($_POST["community_id"])) && ((int) trim($_POST["community_id"]))) {
	$COMMUNITY_ID	= (int) trim($_POST["community_id"]);
}

if((isset($_GET["action"])) && ($tmp_action_type = clean_input(trim($_GET["action"]), "alphanumeric"))) {
	$ACTION	= $tmp_action_type;
} elseif((isset($_POST["action"])) && ($tmp_action_type = clean_input(trim($_POST["action"]), "alphanumeric"))) {
	$ACTION	= $tmp_action_type;
}

if ((isset($_GET["type"])) && ($tmp_type = clean_input(trim($_GET["type"]), "alphanumeric"))) {
	$TYPE	= $tmp_type;
} elseif((isset($_POST["type"])) && ($tmp_action_type = clean_input(trim($_POST["type"]), "alphanumeric"))) {
	$TYPE	= $tmp_type;
} else {
	$TYPE	= "default";
}

if ((isset($_GET["poll_id"])) && ($tmp_poll = clean_input(trim($_GET["poll_id"]), "int"))) {
	$poll_id	= $tmp_poll;
} elseif((isset($_POST["poll_id"])) && ($tmp_poll = clean_input(trim($_POST["poll_id"]), "int"))) {
	$poll_id	= $tmp_poll;
} else {
	$poll_id	= 0;
}

unset($tmp_action_type);
unset($tmp_type);
unset($tmp_poll);
if(isset($COMMUNITY_ID) && isset($ACTION)) {
	ob_clear_open_buffers();

	switch($ACTION) {
		case "memberlist":
			//Figure out the organisation, group, and role requested
			if(isset($_POST["ogr"])) {
				$pieces = explode('|', $_POST["ogr"]);
				if(isset($pieces[0])) {
					$ORGANISATION_ID = clean_input($pieces[0], array("trim", "int"));
				}
				if(isset($pieces[1])) {
					$GROUP = clean_input($pieces[1], array("trim", "alphanumeric"));
				}
				if(isset($pieces[2])) {
					$ROLE = clean_input($pieces[2], array("trim", "alphanumeric"));
				}
			}
			
			if(isset($ORGANISATION_ID) && isset($GROUP) && isset($ROLE)) {
				$query				= "SELECT * FROM `communities` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `community_active` = '1'";
				$community_details	= $db->GetRow($query);
				if($community_details) {
					if($ENTRADA_ACL->amIAllowed(new CommunityResource($COMMUNITY_ID), 'update')) {
						//Community exists and is editable by the current users
						$nmembers_results		= false;
						$nmembers_query	= "	SELECT a.`id` AS `proxy_id`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, a.`username`, a.`organisation_id`, b.`group`, b.`role`
											FROM `".AUTH_DATABASE."`.`user_data` AS a
											LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
											ON a.`id` = b.`user_id`
											WHERE
											a.`organisation_id` = ".$db->qstr($ORGANISATION_ID)."
											AND b.`group` = ".$db->qstr($GROUP)."
											".($ROLE != 'all' ? "AND b.`role` = ".$db->qstr($ROLE) : "")."
											AND b.`app_id` IN (".AUTH_APP_IDS_STRING.")
											AND b.`account_active` = 'true'
											AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
											AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
											GROUP BY a.`id`
											ORDER BY a.`lastname` ASC, a.`firstname` ASC";

						//Fetch list of current members
						$current_member_list	= array();
						$query		= "SELECT `proxy_id` FROM `community_members` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `member_active` = '1'";
						$results	= $db->GetAll($query);
						if($results) {
							foreach($results as $result) {
								if($proxy_id = (int) $result["proxy_id"]) {
									$current_member_list[] = $proxy_id;
								}
							}
						}
						
						if ($TYPE == "polls" && $poll_id) {
							$query = "	SELECT `proxy_id` FROM `community_polls_access` 
												WHERE `cpolls_id` = ".$db->qstr($poll_id);
							$results	= $db->GetAll($query);
							if ($results) {
								foreach($results as $result) {
									if($proxy_id = (int) $result["proxy_id"]) {
										$poll_member_list[] = $proxy_id;
									}
								}
							}
						}

						//
						if($nmembers_query != "") {
							$nmembers_results = $db->GetAll($nmembers_query);
							if($nmembers_results) {
								$members = array(array('text' => "$GROUP > $ROLE", 'value'=>$GROUP.$ROLE, 'options'=>array(), 'disabled'=>false, 'category'=>'true'));
								foreach($nmembers_results as $member) {
									if(in_array($member['proxy_id'], $current_member_list)) {
										$registered = true;
									} else {
										$registered = false;
									}
									if ($poll_member_list && is_array($poll_member_list) && count($poll_member_list)) {
										if(in_array($member['proxy_id'], $poll_member_list)) {
											$in_poll = true;
										} else {
											$in_poll = false;
										}
									}
									if ($TYPE == "polls") {
										if ($registered) {
											$members[0]['options'][] = array('text' => $member['fullname'], 'value' => $member['proxy_id'], 'disabled' => false, 'checked' => ($in_poll ? "checked=\"checked\"" : ""));
										}
									} else {
										$members[0]['options'][] = array('text' => $member['fullname'].($registered ? ' (already a member)' : ''), 'value' => $member['proxy_id'], 'disabled' => $registered);
									}
								}
								
								foreach($members[0]['options'] as $key => $member) {
									if(isset($member['options']) && is_array($member['options']) && !empty($member['options'])) {
										//Alphabetize members
										sort($members[0]['options'][$key]['options']);
									}
								}

								echo '<table cellspacing="0" cellpadding="0" class="select_multiple_table" width="100%">';
								echo lp_multiple_select_table($members, 0, 0, true);
								echo '</table>';
							} else {
								echo "No One Available [1]";
							}
						} else {
							echo "No One Available [2]";
						}
					} else {
						echo "Permissions error!";
					}
				} else {
					echo "Malformed request!";
				}
			}
			break;
	}
}
exit();