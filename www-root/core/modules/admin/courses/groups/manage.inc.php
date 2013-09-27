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
 * @author Organisation: University of Calgary
 * @author Unit: Faculty of Medicine
 * @author Developer: Doug Hall<hall@ucalgary.ca>
 * @copyright Copyright 2011 University of Calgary. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_COURSE_GROUPS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed(new CourseResource($COURSE_ID, $ENTRADA_USER->getActiveOrganisation()), 'update',false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE.(isset($SUBMODULE) && $SUBMODULE ? "/".$SUBMODULE : "")."?id=".$COURSE_ID."\\'', 15000)";

	add_error("You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	// ERROR CHECKING
	$course_details = $db->GetRow("SELECT * FROM `courses` WHERE `course_id` = ".$db->qstr($COURSE_ID));
	switch ($STEP) {
		case "2" :
			if((isset($_POST["add_group_id"])) && ((int) trim($_POST["add_group_id"])) && strlen($_POST["group_members"])) {
				$PROCESSED["cgroup_id"] = (int) trim($_POST["add_group_id"]);
			} else {
				header("Location: ".ENTRADA_URL."/admin/".$MODULE.(isset($SUBMODULE) && $SUBMODULE ? "/".$SUBMODULE : "")."?id=".$COURSE_ID);
			}

			$proxy_ids = explode(',', $_POST["group_members"]);
			$PROCESSED["updated_date"]	= time();
			$PROCESSED["updated_by"] = $ENTRADA_USER->getID();


				$count = $added = 0;
			foreach($proxy_ids as $proxy_id) {
				if(($proxy_id = (int) trim($proxy_id))) {
					$count++;
					if (!$db->GetOne("SELECT `cgaudience_id` FROM `course_group_audience` WHERE `cgroup_id` = ".$db->qstr($PROCESSED["cgroup_id"])." AND `proxy_id` =".$db->qstr($proxy_id))) {
						$PROCESSED["proxy_id"]	= $proxy_id;
						$PROCESSED["active"] = 1;
						$added++;
						if (!$db->AutoExecute("`course_group_audience`", $PROCESSED, "INSERT")) {
							$ERROR++;
							$ERRORSTR[]	= "Failed to insert this member into the group. Please contact a system administrator if this problem persists.";
							application_log("error", "Error while inserting member into database. Database server said: ".$db->ErrorMsg());
						}

					}				
				}
			}
			
			if(!$count) {
				add_error("You must select a user(s) to add to this group. Please be sure that you select at least one user to add this event to from the interface.");
			}
			$STEP = 1;
		break;
		default :
			// No error checking for step 1.
		break;			
	}
	
	// PAGE DISPLAY
	switch ($STEP) {
		case "2" :			// Step 2
            add_success("You have successfully added this member"); 
			echo display_success($SUCCESSSTR);
		break;
	
		default :			// Step 1
			$group_ids = array();
			if ((!isset($PROCESSED["cgroup_id"]) || !(int)$PROCESSED["cgroup_id"]) && isset($_POST["cgroup_id"]) && (int)$_POST["cgroup_id"]) {
				$PROCESSED["cgroup_id"] = (int)$_POST["cgroup_id"];
			}
			if(isset($PROCESSED["cgroup_id"]) && (int)$PROCESSED["cgroup_id"]) {
				$GROUP_ID = $PROCESSED["cgroup_id"];
			} else {
				$GROUP_ID = 0;
			}
			if (isset($_GET["ids"])) {
				$_SESSION["gids"] = array(htmlentities($_GET["ids"]));
			} elseif (isset($_POST["checked"])) {
				$_SESSION["gids"] = $_POST["checked"];
			} elseif((isset($_POST["group_id"])) && ((int) trim($_POST["group_id"]))) {
				$GROUP_ID = (int) trim($_POST["group_id"]);
			} elseif((isset($_GET["gid"])) && ((int) trim($_GET["gid"]))) {
				$GROUP_ID = (int) trim($_GET["gid"]);
			}

			if ((!isset($_SESSION["gids"]) || !is_array($_SESSION["gids"])) || (!@count($_SESSION["gids"]))) {
				header("Location: ".ENTRADA_URL."/admin/courses/groups?id=".$COURSE_ID);
				exit;
			}
								
			$group_ids = $_SESSION["gids"];
			
			$query = "	SELECT * FROM `course_groups`
						WHERE `cgroup_id` IN (".implode(", ", $group_ids).")
						ORDER By `group_name`";
			$course_groups	= $db->GetAll($query);

			if (!$course_groups) {
				header("Location: ".ENTRADA_URL."/admin/".$MODULE.(isset($SUBMODULE) && $SUBMODULE ? "/".$SUBMODULE : "")."?id=".$COURSE_ID);
			}
			if (!$GROUP_ID) {
				$GROUP_ID = $course_groups[0]["cgroup_id"];
			}
			
			/**
			 * Add any existing associated reviewers from the evaluation_contacts table
			 * into the $PROCESSED["associated_reviewers"] array.
			 */
			$query = "SELECT * FROM `course_group_contacts` WHERE `cgroup_id` = ".$db->qstr($GROUP_ID)." ORDER BY `contact_order` ASC";
			$results = $db->GetAll($query);
			if ($results) {
				foreach($results as $contact_order => $result) {
					$PROCESSED["associated_tutors"][(int) $contact_order] = $result["proxy_id"];
				}
			}
			
			/**
			* Compiles the full list of faculty members.
			*/
		   $TUTOR_LIST = array();
		   $query = "	SELECT a.`id` AS `proxy_id`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, a.`organisation_id`
					   FROM `".AUTH_DATABASE."`.`user_data` AS a
					   LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
					   ON b.`user_id` = a.`id`
					   WHERE b.`app_id` = '".AUTH_APP_ID."'
					   AND (b.`group` = 'faculty' OR (b.`group` = 'resident' AND b.`role` = 'lecturer') OR b.`group` = 'staff' OR b.`group` = 'medtech')
					   ORDER BY a.`lastname` ASC, a.`firstname` ASC";
		   $results = $db->GetAll($query);
		   if ($results) {
			   foreach($results as $result) {
				   $TUTOR_LIST[$result["proxy_id"]] = array('proxy_id'=>$result["proxy_id"], 'fullname'=>$result["fullname"], 'organisation_id'=>$result['organisation_id']);
			   }
		   }

			$group_name = $db->GetOne("SELECT `group_name` FROM `course_groups` WHERE `cgroup_id` = ".$db->qstr($GROUP_ID));

			$emembers_query	= "	SELECT c.`cgaudience_id`, CONCAT_WS(' ', a.`firstname`, a.`lastname`) AS `fullname`, c.`active`,
								a.`username`, a.`organisation_id`, a.`username`, CONCAT_WS(':', b.`group`, b.`role`) AS `grouprole`
								FROM `".AUTH_DATABASE."`.`user_data` AS a
								LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
								ON a.`id` = b.`user_id`
								INNER JOIN `course_group_audience` AS c 
								ON a.`id` = c.`proxy_id`
								WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
								AND b.`account_active` = 'true'
								AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
								AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
								AND c.`cgroup_id` = ".$db->qstr($GROUP_ID)."
								GROUP BY a.`id`
								ORDER BY a.`lastname` ASC, a.`firstname` ASC";
			$ONLOAD[]	= "showgroup('".$group_name."',".$GROUP_ID.")";
			

			$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
			
			$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/courses/groups?section=manage&id=".$COURSE_ID, "title" => "Manage Course Groups");
			courses_subnavigation($course_details,"groups");
			?>
			<span class="content-heading">Manage Groups Edit</span>
				<div style="float: right">
	
						<a href="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE.(isset($SUBMODULE) && $SUBMODULE ? "/".$SUBMODULE : ""); ?>?section=add&id=<?php echo $COURSE_ID ?>" class="btn btn-primary">Add Group</a></li>
			</div>
			<h2 style="margin-top: 10px">Manage Groups</h2>
			<div>
				<div style="clear: both"></div> 
				<?php echo (($ERROR) ? display_error($ERRORSTR) : ""); ?>
				<table class="tableList" cellspacing="1" cellpadding="1">
					<colgroup>
						<col style="width: 6%" />
						<col style="width: 54%" />
						<col style="width: 25%" />
						<col style="width: 15%" />
					</colgroup>
					<thead >
						<td />
						<td>&nbsp; Group Name</td>
						<td>&nbsp; Members</td>
						<td />
					</thead>
				</table>
			</div>	
			<form action="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE.(isset($SUBMODULE) && $SUBMODULE ? "/".$SUBMODULE : ""); ?>?section=manage&id=<?php echo $COURSE_ID; ?>&step=1" method="post" id="addMembersForm">
				<input type="hidden" id="step" name="step" value="1" />
				<input type="hidden" id="cgroup_id" name="cgroup_id" value="" />
				<div >
					<table class="tableList" width="452px" cellspacing="0" cellpadding="1" summary="List of groups">
						<colgroup>
							<col style="width: 32px" />
							<col style="width: 270px" />
							<col style="width: 100px" />
							<col style="width: 50px" />
						</colgroup>
						<tbody>
						<?php
							foreach($course_groups as $result) {
								$members = $db->GetRow("SELECT COUNT(*) AS members, case when (MIN(`active`)=0) then 1 else 0 end as `inactive`
														FROM  `course_group_audience` WHERE `cgroup_id` = ".$db->qstr($result["cgroup_id"]));
								
								echo "<tr class=\"group".((!$result["active"]) ? " na" : (($members["inactive"]) ? " np" : ""))."\">";
								echo "	<td style=\"vertical-align: top\">&nbsp;<input type=\"radio\" name=\"groups\" value=\"".$result["cgroup_id"]."\" onclick=\"selectgroup(".$result["cgroup_id"].",'".$result["group_name"]."');\"".(($result["cgroup_id"] == $GROUP_ID) ?" checked=\"checked\"" : "")."/></td>\n";
								echo "	<td><a href=\"".ENTRADA_URL."/admin/courses/groups?section=manage&id=".$COURSE_ID."&gid=".$result["cgroup_id"]."\" >".html_encode($result["group_name"])."</a></td>";
								echo "	<td><a href=\"".ENTRADA_URL."/admin/courses/groups?section=manage&id=".$COURSE_ID."&gid=".$result["cgroup_id"]."\" >".$members["members"]."</a></td>";
								echo "	<td>
									<a href=\"".ENTRADA_URL."/admin/courses/groups?section=edit&action=rename&id=".$COURSE_ID."&gid=".$result["cgroup_id"]."\"><img src=\"".ENTRADA_URL."/images/action-edit.gif\" width=\"16\" height=\"16\" alt=\"Rename Group\" title=\"Rename Group\" border=\"0\" /></a>&nbsp;
									<a href=\"".ENTRADA_URL."/admin/courses/groups?section=edit&action=delete&id=".$COURSE_ID."&gid=".$result["cgroup_id"]."\"><img src=\"".ENTRADA_URL."/images/action-delete.gif\" width=\"16\" height=\"16\" alt=\"Delete/Activate Group\" title=\"Delete/Activate Group\" border=\"0\" /></a>
									</td>\n";
								echo "</tr>";
							}
						?>
						</tbody>
					</table>
				</div>
				<br />
			</form>
			<form action="<?php echo ENTRADA_URL; ?>/admin/courses/groups?section=edit&action=delmember&id=<?php echo $COURSE_ID; ?>&gid=<?php echo $GROUP_ID;?>" method="post">
				<h2 style="margin-top: 10px">View Members</h2>
				<div>
					<div style="clear: both"></div> 
					<?php echo (($ERROR) ? display_error($ERRORSTR) : ""); ?>
					<table class="tableList" cellspacing="1" cellpadding="1">
						<colgroup>
							<col style="width: 6%" />
							<col style="width: 54%" />
							<col style="width: 30%" />
							<col style="width: 10%" />
						</colgroup>
						<thead >
							<td />
							<td>&nbsp; Name</td>
							<td>&nbsp; Group & Role</td>
							<td />
						</thead>
					</table>
				</div>	
				<div>
					<table class="tableList" width="452px" cellspacing="0" cellpadding="1" summary="List of Members">
						<colgroup>
							<col style="width: 32px" />
							<col style="width: 250px" />
							<col style="width: 145px" />
							<col style="width: 25px" />
						</colgroup>
						<tbody>
						<?php
							$results = $db->GetAll($emembers_query);
							if ($results) {
								foreach($results as $result) {
									echo "<tr  class=\"event".(!$result["active"] ? " na" : "")."\">";
									echo "	<td class=\"modified\"><input type=\"checkbox\" class=\"delchk\" name=\"checked[]\" onclick=\"memberChecks()\" value=\"".$result["cgaudience_id"]."\" /></td>\n";
									echo "	<td><a href=\"".ENTRADA_URL."/people?profile=".$result["username"]."\" >".html_encode($result["fullname"])."</a></td>";
									echo "	<td><a href=\"".ENTRADA_URL."/people?profile=".$result["username"]."\" >".$result["grouprole"]."</a></td>";
									echo "	<td>
										<a href=\"".ENTRADA_URL."/admin/courses/groups?section=edit&action=delmember&id=".$COURSE_ID."&gid=".$GROUP_ID."&mids=".$result["cgaudience_id"]."\"><img src=\"".ENTRADA_URL."/images/action-delete.gif\" width=\"16\" height=\"16\" alt=\"Delete/Activate Member\" title=\"Delete/Activate Member\" border=\"0\" /></a>
										</td>\n";
									echo "</tr>";

								}
							}
						?>
						</tbody>
					</table>
				</div>
				<div id="delbutton" style="padding-top: 15px; text-align: right;">
					<input type="submit" class="btn btn-danger" value="Delete/Activate" style="vertical-align: middle" />
				</div>
				<input type="hidden" name="members" value="1" />
			</form>
			<form action="<?php echo ENTRADA_URL; ?>/admin/courses/groups?section=edit&action=managetutors&id=<?php echo $COURSE_ID; ?>&gid=<?php echo $GROUP_ID;?>" method="post">
				<h2 style="margin-top: 10px">Manage Tutors</h2>
				<table style="margin-top: 1px; width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Manage Tutors">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 20%" />
						<col style="width: 77%" />
					</colgroup>
					<tfoot>
						<tr>
							<td colspan="3" style="padding-top: 15px; text-align: right">
								<input type="submit" class="btn btn-primary" value="Update Tutors" style="vertical-align: middle" />
							</td>
						</tr>
					</tfoot>
					<tbody>
						<tr>
							<td></td>
							<td style="vertical-align: top;">
								<label for="evaluation_tutors" class="form-nrequired">Evaluation Tutors</label>
							</td>
							<td>
								<input type="text" id="tutor_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle" />
								<?php
								$ONLOAD[] = "tutor_list = new AutoCompleteList({ type: 'tutor', url: '". ENTRADA_RELATIVE ."/api/personnel.api.php?type=facultyorstaff', remove_image: '". ENTRADA_RELATIVE ."/images/action-delete.gif'})";
								?>
								<div class="autocomplete" id="tutor_name_auto_complete"></div>
								<input type="hidden" id="associated_tutor" name="associated_tutor" />
								<input type="button" class="btn" id="add_associated_tutor" value="Add" style="vertical-align: middle" />
								<span class="content-small">(<strong>Example:</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?>)</span>
								<ul id="tutor_list" class="menu" style="margin-top: 15px">
									<?php
									if (is_array($PROCESSED["associated_tutors"]) && count($PROCESSED["associated_tutors"])) {
										foreach ($PROCESSED["associated_tutors"] as $tutor) {
											if ((array_key_exists($tutor, $TUTOR_LIST)) && is_array($TUTOR_LIST[$tutor])) {
												?>
												<li class="user" id="tutor_<?php echo $TUTOR_LIST[$tutor]["proxy_id"]; ?>" style="cursor: move;margin-bottom:10px;width:350px;"><?php echo $TUTOR_LIST[$tutor]["fullname"]; ?> <img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="tutor_list.removeItem('<?php echo $TUTOR_LIST[$tutor]["proxy_id"]; ?>');" class="list-cancel-image" /></li>
												<?php
											}
										}
									}
									?>
								</ul>
								<input type="hidden" id="tutor_ref" name="tutor_ref" value="" />
								<input type="hidden" id="tutor_id" name="tutor_id" value="" />
							</td>
						</tr>
					</tbody>
				</table>
				<input type="hidden" name="step" value="2" />
			</form>
			<br />
			<div id="additions">
				<h2 style="margin-top: 10px">Add Members</h2>
				<form action="<?php echo ENTRADA_URL."/admin/".$MODULE.(isset($SUBMODULE) && $SUBMODULE ? "/".$SUBMODULE : "")."?".replace_query(array("section" => "manage", "type" => "add", "step" => 2)); ?>" method="post">
					<table style="margin-top: 1px; width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Add Member">
						<colgroup>
							<col style="width: 45%" />
							<col style="width: 10%" />
							<col style="width: 45%" />
						</colgroup>
						<tfoot>
							<tr>
								<td colspan="3" style="padding-top: 15px; text-align: right">
									<input type="submit" class="btn btn-primary" value="Proceed" style="vertical-align: middle" />
								</td>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td colspan="3" style="vertical-align: top">
									If you would like to add users that already exist in the system to this group yourself, you can do so by clicking the checkbox beside their name from the list below.
									Once you have reviewed the list at the bottom and are ready, click the <strong>Proceed</strong> button at the bottom to complete the process.
								</td>
							</tr>
							<tr>
								<td colspan="2" />
								<td>
									<div id="group_name_title"></div>
								</td>
							</tr>			
							<tr>
								<td colspan="2" style="vertical-align: top">
									<div class="member-add-type" id="existing-member-add-type">
									<?php
										$nmembers_results	= false;			
										$course_audience = $course_details["permission"] == "closed"?true:false;
										if ($course_audience) {
											$nmembers_query	= "	SELECT a.`id` AS `proxy_id`, CONCAT_WS(' ', a.`firstname`, a.`lastname`) AS `fullname`, a.`lastname`, a.`firstname`, a.`username`, a.`organisation_id`, b.`group`, b.`role`
																FROM `".AUTH_DATABASE."`.`user_data` AS a
																LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
																ON a.`id` = b.`user_id`
																JOIN `course_audience` AS c
																ON c.`course_id` = ".$db->qstr($COURSE_ID)."
																AND c.`audience_type` = 'proxy_id'
																AND a.`id` = c.`audience_value`
																JOIN `curriculum_periods` AS d
																ON c.`cperiod_id` = d.`cperiod_id`
																WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
																AND b.`account_active` = 'true'
																AND b.`group` = 'student'
																AND c.`audience_active` = 1
																AND d.`start_date` <= ".$db->qstr(time())."
																AND d.`finish_date` >= ".$db->qstr(time())."
																
																UNION
																
																SELECT a.`id` AS `proxy_id`, CONCAT_WS(' ', a.`firstname`, a.`lastname`) AS `fullname`, a.`lastname`, a.`firstname`, a.`username`, a.`organisation_id`, b.`group`, b.`role`
																FROM `".AUTH_DATABASE."`.`user_data` AS a
																JOIN `".AUTH_DATABASE."`.`user_access` AS b
																ON a.`id` = b.`user_id`
																JOIN `course_audience` AS c
																ON c.`course_id` = ".$db->qstr($COURSE_ID)."
																AND c.`audience_type` = 'group_id'
																JOIN `groups` AS d
																ON c.`audience_value` = d.`group_id`
																JOIN `group_members` AS e
																ON d.`group_id` = e.`group_id`
																AND e.`proxy_id` = a.`id`
																JOIN `curriculum_periods` AS f
																ON c.`cperiod_id` = f.`cperiod_id`
																WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
																AND b.`account_active` = 'true'
																AND b.`group` = 'student'
																AND c.`audience_active` = 1
																AND d.`group_active` = 1
																AND (d.`start_date` <= ".$db->qstr(time())." OR d.`start_date` = 0 OR d.`start_date` IS NULL)
																AND (d.`expire_date` >= ".$db->qstr(time())." OR d.`expire_date` = 0 OR d.`expire_date` IS NULL)
																
																GROUP BY a.`id`
																ORDER BY `lastname` ASC, `firstname` ASC";
										} else {
											$nmembers_query	= "	SELECT a.`id` AS `proxy_id`, CONCAT_WS(' ', a.`firstname`, a.`lastname`) AS `fullname`, a.`username`, a.`organisation_id`, b.`group`, b.`role`
																FROM `".AUTH_DATABASE."`.`user_data` AS a
																LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
																ON a.`id` = b.`user_id`
																WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
																AND b.`account_active` = 'true'
																AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
																AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
																GROUP BY a.`id`
																ORDER BY a.`lastname` ASC, a.`firstname` ASC";
										}
										
										//Fetch list of categories
										$query	= "SELECT `organisation_id`,`organisation_title` FROM `".AUTH_DATABASE."`.`organisations` ORDER BY `organisation_title` ASC";
										$organisation_results	= $db->GetAll($query);
										if($organisation_results) {
											$organisations = array();
											foreach($organisation_results as $result) {
												if($ENTRADA_ACL->amIAllowed('resourceorganisation'.$result["organisation_id"], 'create')) {
													$member_categories[$result["organisation_id"]] = array('text' => $result["organisation_title"], 'value' => 'organisation_'.$result["organisation_id"], 'category'=>true);
												}
											}
										}


											$current_member_list	= array();
										$query		= "SELECT `proxy_id` FROM `course_group_audience` WHERE `cgroup_id` = ".$db->qstr($GROUP_ID)." AND `active` = '1'";
										$results	= $db->GetAll($query);
										if($results) {
											foreach($results as $result) {
												if($proxy_id = (int) $result["proxy_id"]) {
													$current_member_list[] = $proxy_id;
												}
											}
										}

										$nmembers_results = $db->GetAll($nmembers_query);
										if($nmembers_results) {
											$members = $member_categories;


											foreach($nmembers_results as $member) {
				
												$organisation_id = $member['organisation_id'];
												$group = $member['group'];
												$role = $member['role'];
												if($group == "student" && !isset($members[$organisation_id]['options'][$group.$role])) {
													$members[$organisation_id]['options'][$group.$role] = array('text' => $group. ' > '.$role, 'value' => $organisation_id.'|'.$group.'|'.$role);
												} elseif ($group != "guest" && $group != "student" && !isset($members[$organisation_id]['options'][$group."all"])) {
													$members[$organisation_id]['options'][$group."all"] = array('text' => $group. ' > all', 'value' => $organisation_id.'|'.$group.'|all');
												}
											}


											foreach($members as $key => $member) {
												if(isset($member['options']) && is_array($member['options']) && !empty($member['options'])) {
													sort($members[$key]['options']);
												}
											}
											echo lp_multiple_select_inline('group_members', $members, array(
													'width'	=>'100%',
													'ajax'=>true,
													'selectboxname'=>'group and role',
													'default-option'=>'-- Select Group & Role --',
													'category_check_all'=>true));


										} else {
											echo "No One Available [1]";
										}
									?>
										<input class="multi-picklist" id="group_members" name="group_members" style="display: none;">
									</div>
								</td>
								<td style="vertical-align: top; padding-left: 20px;">
									<h3>Members to be Added on Submission</h3>
									<div id="group_members_list"></div>
								</td>
							</tr>
						</tbody>
					</table>
					<input type="hidden" id="add_group_id" name="add_group_id" value="" />
				</form>
			</div>
		<script type="text/javascript">


		var people = [[]];
		var ids = [[]];
		var disablestatus = 0;


		//Updates the People Being Added div with all the options
		function updatePeopleList(newoptions, index) {
			people[index] = newoptions;
				table = people.flatten().inject(new Element('table', {'class':'member-list'}), function(table, option, i) {
				if(i%2 == 0) {
					row = new Element('tr');
					table.appendChild(row);
				}
				row.appendChild(new Element('td').update(option));
				return table;
			});
			$('group_members_list').update(table);
			ids[index] = $F('group_members').split(',').compact();
			$('group_members').value = ids.flatten().join(',');
		}

		$('group_members_select_filter').observe('keypress', function(event){
		    if(event.keyCode == Event.KEY_RETURN) {
				Event.stop(event);
			}
		});

		//Reload the multiselect every time the category select box changes
		var multiselect;


		$('group_members_category_select').observe('change', function(event) {

			if ($('group_members_category_select').selectedIndex != 0) {
				$('group_members_scroll').update(new Element('div', {'style':'width: 100%; height: 100%; background: transparent url(<?php echo ENTRADA_URL;?>/images/loading.gif) no-repeat center'}));
	
				//Grab the new contents
				var updater = new Ajax.Updater('group_members_scroll', '<?php echo ENTRADA_URL."/admin/courses/groups?section=membersapi&id=".$COURSE_ID;?>',{
					method:'post',
					parameters: {
						'ogr':$F('group_members_category_select'),
						'group_id':'<?php echo $GROUP_ID;?>',
						'course_id':'<?php echo $COURSE_ID; ?>'
					},
					onSuccess: function(transport) {
						//onSuccess fires before the update actually takes place, so just set a flag for onComplete, which takes place after the update happens
						this.makemultiselect = true;
					},
					onFailure: function(transport){
						$('group_members_scroll').update(new Element('div', {'class':'display-error'}).update('There was a problem communicating with the server. An administrator has been notified, please try again later.'));
					},
					onComplete: function(transport) {
						//Only if successful (the flag set above), regenerate the multiselect based on the new options
						if(this.makemultiselect) {
							if(multiselect) {
								multiselect.destroy();
							}
							multiselect = new Control.SelectMultiple('group_members','group_members_options',{
								labelSeparator: '; ',
								checkboxSelector: 'table.select_multiple_table tr td.select_multiple_checkbox input[type=checkbox]',
								categoryCheckboxSelector: 'table.select_multiple_table tr td.select_multiple_checkbox_category input[type=checkbox]',
								nameSelector: 'table.select_multiple_table tr td.select_multiple_name label',
								overflowLength: 70,
								filter: 'group_members_select_filter',
								afterCheck: function(element) {
									var tr = $(element.parentNode.parentNode);
									tr.removeClassName('selected');
									if(element.checked) {
										tr.addClassName('selected');

									}
																	},
								updateDiv: function(options, isnew) {
									updatePeopleList(options, $('group_members_category_select').selectedIndex);

								}
							});
						}
					}
				});
			}
		});

		function selectgroup(group,name) {
			$('cgroup_id').value = group;
			$('addMembersForm').submit();
		}
		function showgroup(name,group) {					
			$('group_name_title').update(new Element('div',{'style':'font-size:14px; font-weight:600; color:#153E7E'}).update('Group: '+name));
			$('add_group_id').value = group;
		}
		function toggleDisabled(el) {
			try {
				el.disabled = !el.disabled;						
				}
			catch(E){				
			}
			if (el.childNodes && el.childNodes.length > 0) {
				for (var x = 0; x < el.childNodes.length; x++) {
					toggleDisabled(el.childNodes[x]);
				}
			}
		}
		function memberChecks() {
			if ($$('.delchk:checked').length&&!disablestatus) {
				disablestatus = 1;
				toggleDisabled($('additions'),true);
				$('delbutton').style.display = 'block';
				$('additions').fade({ duration: 0.3, to: 0.25 }); 
			} else if (!$$('.delchk:checked').length&&disablestatus) {
				disablestatus = 0;
				toggleDisabled($('additions'),false);
				$('delbutton').style.display = 'none';
				$('additions').fade({ duration: 0.3, to: 1.0 });
			}
		}
		</script>
		<br /><br />
		<?php
		break;	

	}
}
