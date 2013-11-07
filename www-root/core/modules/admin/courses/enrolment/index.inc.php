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
 * @author Developer: Brandon Thorn <brandon.thorn@queensu.ca>
 * @copyright Copyright 2012 University of Calgary. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_COURSE_ENROLMENT"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('course', 'update',false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	// ERROR CHECKING
	switch ($STEP) {
		case "2" :
			$GROUP_ID		= 0;

			if ((isset($_GET["group_id"])) && ((int) $_GET["group_id"])) {
				$GROUP_ID	= (int) $_GET["group_id"];
			}
			if ((isset($_GET["org_id"])) && ((int) $_GET["org_id"])) {
				$ORGANISATION_ID	= (int) $_GET["org_id"];
			}

			if($GROUP_ID && $COURSE_ID){
				$proxy_ids = explode(',', $_POST["group_members_".$GROUP_ID]);
				foreach ($proxy_ids as $proxy_id) {
					$added_proxy_ids[] = (int) $proxy_id;
				}
				if (isset($added_proxy_ids) && !empty($added_proxy_ids)) {
					$PROCESSED["updated_date"]	= time();
					$PROCESSED["updated_by"] = $ENTRADA_USER->getID();
					$PROCESSED["group_id"] = $GROUP_ID;
					$PROCESSED["start_date"] = time();
					$PROCESSED["finish_date"] = 0;
					$PROCESSED["member_active"] = 1;
					$PROCESSED["entrada_only"] = 1;
					$count = $added = 0;
					foreach($proxy_ids as $proxy_id) {
						if(($proxy_id = (int) trim($proxy_id))) {
							$count++;
							if (!$db->GetOne("SELECT `gmember_id` FROM `group_members` WHERE `group_id` = ".$db->qstr($PROCESSED["group_id"])." AND `proxy_id` =".$db->qstr($proxy_id))) {
								$PROCESSED["proxy_id"]	= $proxy_id;
								$added++;
								if (!$db->AutoExecute("group_members", $PROCESSED, "INSERT")) {
									$ERROR++;
									$ERRORSTR[]	= "Failed to insert this member into the group. Please contact a system administrator if this problem persists.";
									application_log("error", "Error while inserting member into database. Database server said: ".$db->ErrorMsg());
								}
							}
						}
					}

					if(!$count) {
						$ERROR++;
						$ERRORSTR[] = "You must select a user(s) to add to this group. Please be sure that you select at least one user to add this event to from the interface.";
					}

					$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/courses/enrolment?id=".$COURSE_ID."\\'', 15000)";
				} else{
					add_error("You must select users to add.");
					$STEP = 1;
				}
			} elseif($COURSE_ID) {
				$proxy_ids = explode(',', $_POST["group_members_0"]);
				foreach ($proxy_ids as $proxy_id) {
					$added_proxy_ids[] = (int) $proxy_id;
				}
				if (isset($added_proxy_ids) && !empty($added_proxy_ids)) {
					$query = "	SELECT a.`cperiod_id`
								FROM `curriculum_periods` a
								JOIN `courses` b
								ON a.`curriculum_type_id` = b.`curriculum_type_id`
								AND b.`course_id` = ".$db->qstr($COURSE_ID)."
								WHERE a.`start_date` < ".$db->qstr(time())."
								AND a.`finish_date` > ".$db->qstr(time())."
								AND a.`active` = '1'";
					if ($cperiod_id = $db->GetOne($query)) {
						$PROCESSED["cperiod_id"] = (int)$cperiod_id;
					} else {
						$PROCESSED["cperiod_id"] = 0;
					}
					$PROCESSED["audience_type"]	= 'proxy_id';
					$PROCESSED["course_id"]	= $COURSE_ID;
					$PROCESSED["audience_active"] = 1;
					$PROCESSED["enroll_start"]	= time();
					$PROCESSED["enroll_finish"]	= 0;
					$count = $added = 0;
					foreach ($proxy_ids as $proxy_id) {
						if (($proxy_id = (int) trim($proxy_id))) {
							$count++;
							if (!$db->GetOne("SELECT `caudience_id` FROM `course_audience` WHERE `audience_type` = 'proxy_id' AND `course_id` = ".$db->qstr($COURSE_ID)." AND `audience_value` =".$db->qstr($proxy_id))) {
								$PROCESSED["audience_value"]	= $proxy_id;
								$added++;
								if (!$db->AutoExecute("course_audience", $PROCESSED, "INSERT")) {
									echo 'here';
									add_error("Failed to insert this member into the group. Please contact a system administrator if this problem persists.");
									application_log("error", "Error while inserting member into database. Database server said: ".$db->ErrorMsg());
								}
							}
						}
					}
					$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/courses/enrolment?id=".$COURSE_ID."\\'', 5000)";
				} else{
					add_error("You must select users to add.");
					$STEP = 1;
				}
			} else {
				add_error("Invalid course identifier provided.");
				$STEP = 1;
			}

		break;
		default :

		break;
	}

	// PAGE DISPLAY
	switch ($STEP) {
		case "2" :			// Step 2
            add_success("You have successfully updated the course enrolment. You will be returned to the Course Enrolment index in 5 seconds. Or <a href=\"".ENTRADA_URL."/admin/courses/enrolment?id=".$COURSE_ID."\">click here</a> to go there now.");
			echo display_success($SUCCESSSTR);
		break;

		default :			// Step 1

			if ($ERROR) {
				echo display_error();
			}

			if ($NOTICE) {
				echo display_notice();
			}

			if ($SUCCESS) {
				echo display_success();
			}

			$group_ids = array();

			$ONLOAD[]	= "showgroup('".$group_name."',".$GROUP_ID.")";

			$query = "	SELECT * FROM `courses`
						WHERE `course_id` = ".$db->qstr($COURSE_ID)."
						AND `course_active` = '1'";
			$course_details	= $db->GetRow($query);

			if (isset($_GET["cperiod_id"]) && $temp = clean_input($_GET["cperiod_id"], array("trim", "int"))) {
				$cperiod = $temp;				
			} else {
				$query = "	SELECT `cperiod_id`
							FROM `curriculum_periods`
							WHERE `curriculum_type_id` = ".$db->qstr($course_details["curriculum_type_id"])."
							AND `start_date` < ".$db->qstr(time())."
							AND `finish_date` > ".$db->qstr(time())."
							AND `active` = 1
							LIMIT 1";
				$cperiod = $db->GetOne($query);
				if (!$cperiod) {
					$cperiod = 0;
				}
			}

			$query = "	SELECT * FROM `course_audience`
						WHERE `course_id` = ".$db->qstr($COURSE_ID)."
						AND `cperiod_id` = ".$db->qstr($cperiod);
			$audience = $db->GetAll($query);
			$singles = array();
			$groups = array();
			if ($audience) {
				foreach ($audience as $member) {
					if ($member["audience_type"] == "proxy_id") {
						$singles[] = (int)$member["audience_value"];
					} elseif($member["audience_type"] == "group_id") {
						$groups[] = $member["audience_value"];
					}
				}
			}
			if ($singles && !empty ($singles)) {
				//$query = "SELECT * FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` IN ".$db->qstr(implode(",",$singles));
				$members_query	= "	SELECT a.`id` AS `user_id`, a.`number`, CONCAT_WS(' ', a.`firstname`, a.`lastname`) AS `fullname`,
										a.`username`, a.`organisation_id`,  CONCAT_WS(':', b.`group`, b.`role`) AS `grouprole`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON a.`id` = b.`user_id`
										WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
										AND b.`account_active` = 'true'
										AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
										AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
										AND a.`id` IN (".implode(",",$singles).")
										GROUP BY a.`id`
										ORDER BY a.`lastname` ASC, a.`firstname` ASC";
				$single_members = $db->GetAll($members_query);
			}
			if ($groups && !empty ($groups)) {
				foreach	($groups as $group) {
					$query = "	SELECT * FROM `groups` WHERE `group_id` = ".$db->qstr($group);
					$group_names[$group] = $db->GetRow($query);

					$emembers_query	= "	SELECT a.`id` AS `proxy_id`, a.`number`, c.`gmember_id`, CONCAT_WS(' ', a.`firstname`, a.`lastname`) AS `fullname`, c.`member_active`,
										a.`username`, a.`organisation_id`, a.`username`, CONCAT_WS(':', b.`group`, b.`role`) AS `grouprole`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON a.`id` = b.`user_id`
										INNER JOIN `group_members` c ON a.`id` = c.`proxy_id`
										WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
										AND b.`account_active` = 'true'
										AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
										AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
										AND c.`group_id` = ".$db->qstr($group)."
										AND c.`member_active` = 1
										GROUP BY a.`id`
										ORDER BY a.`lastname` ASC, a.`firstname` ASC";
					$group_members[$group] = $db->GetAll($emembers_query);
				}
			}

			if (isset($_GET["download"]) && $type = clean_input($_GET["download"])) {
				switch($type){
					case "csv":
						ob_clean();
						$output = '';
						$num_members = 0;
						if($group_members){
							foreach($group_names as $key=>$group){
								foreach($group_members[$key] as $member){
									$num_members++;
									$output .= $group["group_name"].",".$member["fullname"].",".$member["number"]."\n";
								}

							}
						}
						if($single_members){
							foreach($single_members as $key=>$member){
								$num_members++;
								$output .= "Individual User,".$member["fullname"].",".$member["number"]."\n";
							}
						}
						$output .= "\n\n";
						$output .= "Total Number of Users,,".$num_members."\n";
						header("Pragma: public");
						header("Expires: 0");
						header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
						header("Content-Type: text/csv");
						header("Content-Disposition: inline; filename=\"ClassList.csv\"");
						header("Content-Length: ".@strlen($output));
						header("Content-Transfer-Encoding: binary\n");

						echo $output;
						exit;
						break;
					default:
						break;
				}
			}

			courses_subnavigation($course_details,"enrolment");
			?>
			<h1>Manage Course Enrolment</h1>
			<?php
			if (!$group_names && !$single_members) {
				add_notice('There is currently no student enrolment for this course at this time.');
				echo display_notice();
			} ?>			
			<div class="span12 clearfix">
				<form class="pull-right form-horizontal" style="margin-bottom:0;">
					<div class="control-group">
						<label for="cperiod_select" class="control-label content-small">
							Enrolment Period:
						</label>
						<div class="controls">
							<select id="cperiod_select" name="cperiod_select" onchange="window.location='<?php echo ENTRADA_URL;?>/admin/courses/enrolment?id=<?php echo $COURSE_ID;?>&cperiod_id='+this.options[this.selectedIndex].value">
								<option value="0">-- Select a Period --</option>
								<?php							
								$query = "	SELECT * 
											FROM `curriculum_periods` a
											JOIN `course_audience` b
											ON a.`cperiod_id` = b.`cperiod_id`
											WHERE a.`curriculum_type_id` = ".$db->qstr($course_details["curriculum_type_id"])." 
											AND a.`active` = 1
											AND b.`course_id` = " . $db->qstr($course_details["course_id"]) . "
											GROUP BY a.`cperiod_id`";	
								$periods = $db->GetAll($query);									
								if ($periods) {
									foreach ($periods as $period) { ?>
										<option value="<?php echo $period["cperiod_id"];?>" <?php echo (($period["cperiod_id"] == $cperiod) ? "selected=\"selected\"" : "");?>>
											<?php echo (($period["curriculum_period_title"]) ? $period["curriculum_period_title"] . " - " : "") . date("F jS, Y" ,$period["start_date"])." to ".date("F jS, Y" ,$period["finish_date"]); ?>
										</option>
								<?php
									}
								} ?>
							</select>
						</div>
					</div>
				</form>
			</div>
			<?php
			if ($group_names) {
				foreach ($group_names as $key=>$group) {
					if ($group["group_type"] == "course_list") { ?>
					<div style="float: right; margin-bottom: 5px">
						<ul class="page-action">
							<li class="last"><a id="toggle_add_button_<?php echo $group["group_id"];?>" href="javascript: toggleAddUsers(<?php echo $group["group_id"];?>)" data-group-name="<?php echo $group["group_name"];?>">Add Users to <?php echo $group["group_name"]; ?></a></li>
						</ul>
					</div>
					<?php } ?>
					<h2>Members in the Group '<?php echo $group["group_name"]; ?>'</h2>
					<form id="delete_form_<?php echo $group["group_id"];?>" action="<?php echo ENTRADA_URL; ?>/admin/courses/enrolment?section=manage&amp;step=2&amp;id=<?php echo $COURSE_ID;?>&amp;group_id=<?php echo $group["group_id"];?>" method="post">
						<table class="tableList" cellspacing="1" cellpadding="1">
							<colgroup>
								<col class="modified" />
								<col class="title" />
								<col class="general" />
							</colgroup>
							<thead>
								<tr>
									<td class="modified"></td>
									<td class="title">Full Name</td>
									<td class="general">Group &amp; Role</td>
								</tr>
							</thead>
							<tbody>
							<?php
                            if ($group_members[$key]) {
                                foreach ($group_members[$key] as $result) {
                                    echo "<tr class=\"event".(!$result["member_active"] ? " na" : "")."\">";
                                    echo "	<td class=\"modified\">".($group["group_type"]=="course_list"?"<input type=\"checkbox\" class=\"delchk delchk_".$group["group_id"]."\" name=\"checked[]\" onclick=\"memberChecks(".$group["group_id"].")\" value=\"".$result["proxy_id"]."\" />":"&nbsp;")."</td>\n";
                                    echo "	<td class=\"title\"><a href=\"".ENTRADA_URL."/people?profile=".$result["username"]."\" >".html_encode($result["fullname"])."</a></td>";
                                    echo "	<td class=\"general\"><a href=\"".ENTRADA_URL."/people?profile=".$result["username"]."\" >".$result["grouprole"]."</a></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr>";
                                echo "  <td colspan=\"3\">";
                                    clear_notice();
                                    add_notice("There are no students in the group '".$group["group_name"]."' at this time.");
                                    echo display_notice();
                                echo "  </td>";
                                echo "</tr>";
                            }
							?>
							</tbody>
						</table>

						<div id="delbutton_<?php echo $group["group_id"];?>" style="padding-top: 15px; text-align: right; display:none">
							<input type="button" class="btn btn-danger" id="delete_<?php echo $group["group_id"];?>" value="Delete/Activate" style="vertical-align: middle" />
						</div>

						<input type="hidden" name="members" value="1" />
					</form>
				<?php
					if ($group["group_type"] == "cohort") {
                        ?>
                        <div class="display-generic">
                            <strong>Please Note:</strong> Because this student list is used by multiple courses editing it is reserved for administrators.
                        </div>
						<?php
					} else {
						?>
                        <div id="additions_<?php echo $group["group_id"];?>" style="display:none;">
                            <h2 style="margin-top: 10px">Add Members to the group '<?php echo $group["group_name"]; ?>'</h2>
                            <form action="<?php echo ENTRADA_URL;?>/admin/courses/enrolment?step=2&amp;id=<?php echo $COURSE_ID;?>&amp;org_id=<?php echo $ORGANISATION_ID;?>&amp;group_id=<?php echo $group["group_id"]; ?>" method="post">
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


                                                    if ($course_details["permission"] == "closed") {
                                                        $course_audience = true;
                                                    } else {
                                                        $course_audience = false;
                                                    }

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
                                                                            GROUP BY a.`id`
                                                                            ORDER BY a.`lastname` ASC, a.`firstname` ASC

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
                                                                            AND (d.`start_date` <= ".$db->qstr(time())." OR d.`start_date` = 0)
                                                                            AND (d.`expire_date` >= ".$db->qstr(time())." OR d.`expire_date` = 0)

                                                                            GROUP BY a.`id`
                                                                            ORDER BY a.`lastname` ASC, a.`firstname` ASC";
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
                                                            $user_group = $member['group'];
                                                            $user_role = $member['role'];

                                                            if($user_group == "student" && !isset($members[$organisation_id]['options'][$user_group.$user_role])) {
                                                                $members[$organisation_id]['options'][$user_group.$user_role] = array('text' => $user_group. ' > '.$user_role, 'value' => $organisation_id.'|'.$user_group.'|'.$user_role);
                                                            } elseif ($user_group != "guest" && $group != "student" && !isset($members[$organisation_id]['options'][$user_group."all"])) {
                                                                $members[$organisation_id]['options'][$user_group."all"] = array('text' => $user_group. ' > all', 'value' => $organisation_id.'|'.$user_group.'|all');
                                                            }
                                                        }

                                                        foreach($members as $key => $member) {
                                                            if(isset($member['options']) && is_array($member['options']) && !empty($member['options'])) {
                                                                sort($members[$key]['options']);
                                                            }
                                                        }
                                                        echo lp_multiple_select_inline('group_members_'.$group["group_id"], $members, array(
                                                                'width'	=>'100%',
                                                                'ajax'=>true,
                                                                'selectboxname'=>'group and role',
                                                                'default-option'=>'-- Select Group & Role --',
                                                                'category_check_all'=>true));

                                                    } else {
                                                        echo "No One Available [1]";
                                                    }
                                                ?>
                                                    <input class="multi-picklist" id="group_members_<?php echo $group["group_id"];?>" name="group_members_<?php echo $group["group_id"];?>" style="display: none;">
                                                </div>
                                            </td>
                                            <td style="vertical-align: top; padding-left: 20px;">
                                                <h3>Members to be Added on Submission</h3>
                                                <div id="group_members_<?php echo $group["group_id"];?>_list"></div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <input type="hidden" id="add_group_id" name="add_group_id" value="" />
                            </form>
                        </div>
						<?php
					}
				}
			}

			if ($single_members) {
                ?>
				<div style="float: right; margin-bottom: 5px">
					<ul class="page-action">
						<li class="last"><a id="toggle_add_button_0" href="javascript: toggleAddUsers(0)" data-group-name="<?php echo $course_details["course_name"];?>">Add Individual Users to <?php echo $course["course_name"]; ?></a></li>
					</ul>
				</div>
				<h2>Individual Members</h2>
				<form id="delete_form_0"action="<?php echo ENTRADA_URL; ?>/admin/courses/enrolment?section=manage&amp;step=2&amp;id=<?php echo $COURSE_ID;?>&amp;org_id=<?php echo $ORGANISATION_ID;?>&amp;group_id=0" method="post">
					<table class="tableList" cellspacing="1" cellpadding="1">
						<colgroup>
							<col style="width: 6%" />
							<col style="width: 54%" />
							<col style="width: 40%" />
						</colgroup>
						<thead>
							<tr>
								<td></td>
								<td>Full Name</td>
								<td>Group &amp; Role</td>
							</tr>
						</thead>
						<tbody>
						<?php

							foreach($single_members as $result) {
								echo "<tr class=\"event\">";
								echo "	<td><input type=\"checkbox\" class=\"delchk delchk_0\" name=\"checked[]\" onclick=\"memberChecks(0)\" value=\"".$result["user_id"]."\"/></td>\n";
								echo "	<td><a href=\"".ENTRADA_URL."/people?profile=".$result["username"]."\" >".html_encode($result["fullname"])."</a></td>";
								echo "	<td><a href=\"".ENTRADA_URL."/people?profile=".$result["username"]."\" >".$result["grouprole"]."</a></td>";
								echo "</tr>";
							}
						?>
						</tbody>
					</table>

					<div id="delbutton_0" style="padding-top: 15px; text-align: right; display:none">
						<input type="button" class="btn btn-danger" id="delete_0" value="Delete/Activate" style="vertical-align: middle" />
					</div>

					<input type="hidden" name="members" value="1" />
				</form>
				<div id="additions_0" style="display:none;">
							<h2 style="margin-top: 10px">Add Individual Members to the course '<?php echo $course_details["course_name"]; ?>'</h2>
							<form action="<?php echo ENTRADA_URL; ?>/admin/courses/enrolment?step=2&amp;id=<?php echo $COURSE_ID;?>&amp;org_id=<?php echo $ORGANISATION_ID;?>&amp;group_id=0" method="post">
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

													if ($course_details["permission"] == "closed") {
														$course_audience = true;
													} else {
														$course_audience = false;
													}

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
																			AND (d.`start_date` <= ".$db->qstr(time())." OR d.`start_date` = 0)
																			AND (d.`expire_date` >= ".$db->qstr(time())." OR d.`expire_date` = 0)

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
														echo lp_multiple_select_inline('group_members_0', $members, array(
																'width'	=>'100%',
																'ajax'=>true,
																'selectboxname'=>'group and role',
																'default-option'=>'-- Select Group & Role --',
																'category_check_all'=>true));

													} else {
														echo "No One Available [1]";
													}
												?>
													<input class="multi-picklist" id="group_members_0" name="group_members_0" style="display: none;">
												</div>
											</td>
											<td style="vertical-align: top; padding-left: 20px;">
												<h3>Members to be Added on Submission</h3>
												<div id="group_members_0_list"></div>
											</td>
										</tr>
									</tbody>
								</table>
								<input type="hidden" id="add_group_id" name="add_group_id" value="" />
							</form>
						</div>
				<br/>
				<a href="<?php echo ENTRADA_URL;?>/admin/courses/enrolment?id=<?php echo $COURSE_ID;?>&amp;download=csv"><img src="<?php echo ENTRADA_URL;?>/templates/default/images/btn_save.gif" title="Download File" alt="Download File" width="15" style="vertical-align:text-bottom;">Download CSV</a>

			<?php } ?>
			<div id="dialog-confirm" title="Delete?" style="display: none">
				<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Any users removed will be permanently removed. Are you sure you want to continue?</p>
			</div>
			<script type="text/javascript">
				jQuery(document).ready(function(){
					jQuery('.delete').click(function(){
						id = jQuery(this).attr('id').substring(7);
						jQuery("#dialog-confirm").dialog({
							resizable: false,
							height:180,
							modal: true,
							buttons: {
								'Delete': function() {
									jQuery('#delete_form_'+id).submit();
									return true;
								},
								Cancel: function() {
									jQuery(this).dialog('close');
								}
							}
							});
					});
				});
				function toggleAddUsers(id){
					if(jQuery('#additions_'+id).is(":visible")){
						jQuery('#additions_'+id).hide();
						var group = jQuery('#toggle_add_button_'+id).attr('data-group-name');
						if (id==0) {
							jQuery('#toggle_add_button_'+id).text('Add Individual Users to '+group);
						} else {
							jQuery('#toggle_add_button_'+id).text('Add Users to '+group);
						}
					}else{
						jQuery('#additions_'+id).show();
						var group = jQuery('#toggle_add_button_'+id).attr('data-group-name');
						if (id == 0) {
							jQuery('#toggle_add_button_'+id).text('Hide Add Individual Users to '+group);
						} else {
							jQuery('#toggle_add_button_'+id).text('Hide Add Users for '+group);
						}
					}
				}

				var people = [[]];
				var ids = [[]];
				var disablestatus = 0;
				var currentgroup = 0;

				//Updates the People Being Added div with all the options
				function updatePeopleList(newoptions, index, id) {
					if(currentgroup !== id){
						people = [[]];
						ids = [[]];
						table = people.flatten().inject(new Element('table', {'class':'member-list'}), function(table, option, i) {
							if(i%2 == 0) {
								row = new Element('tr');
								table.appendChild(row);
							}
							row.appendChild(new Element('td').update(option));
							return table;
						});
						$('group_members_'+currentgroup+'_category_select').selectedIndex = 0;
						$('group_members_'+currentgroup+'_scroll').update('');
						$('group_members_'+currentgroup+'_list').update(table);
						currentgroup = id;
					}
					people[index] = newoptions;
					table = people.flatten().inject(new Element('table', {'class':'member-list'}), function(table, option, i) {
						if(i%2 == 0) {
							row = new Element('tr');
							table.appendChild(row);
						}
						row.appendChild(new Element('td').update(option));
						return table;
					});
					$('group_members_'+id+'_list').update(table);
					ids[index] = $F('group_members_'+id).split(',').compact();
				}

//				$('group_members_select_filter').observe('keypress', function(event){
//					if(event.keyCode == Event.KEY_RETURN) {
//						Event.stop(event);
//					}
//				});

				//Reload the multiselect every time the category select box changes
				var multiselect;

				jQuery('.select_multiple_submit > select').live('change', function(event) {
					var select_data = event.target.id.split('_');
					var id = select_data[2];
					if ($('group_members_'+id+'_category_select').selectedIndex != 0) {
						$('group_members_'+id+'_scroll').update(new Element('div', {'style':'width: 100%; height: 100%; background: transparent url(<?php echo ENTRADA_URL;?>/images/loading.gif) no-repeat center'}));

						//Grab the new contents
						var updater = new Ajax.Updater('group_members_'+id+'_scroll', '<?php echo ENTRADA_URL."/admin/courses/enrolment?section=groupmembersapi&id=".$COURSE_ID;?>',{
							method:'post',
							parameters: {
								'ogr':$F('group_members_'+id+'_category_select'),
								'group_id':id,
								'course_id':'<?php echo $COURSE_ID; ?>'
							},
							onSuccess: function(transport) {
								//onSuccess fires before the update actually takes place, so just set a flag for onComplete, which takes place after the update happens
								this.makemultiselect = true;
							},
							onFailure: function(transport){
								$('group_members_'+id+'_scroll').update(new Element('div', {'class':'display-error'}).update('There was a problem communicating with the server. An administrator has been notified, please try again later.'));
							},
							onComplete: function(transport) {
								//Only if successful (the flag set above), regenerate the multiselect based on the new options
								if(this.makemultiselect) {
									if(multiselect) {
										multiselect.destroy();
									}
									multiselect = new Control.SelectMultiple('group_members_'+id,'group_members_'+id+'_options',{
										labelSeparator: '; ',
										checkboxSelector: 'table.select_multiple_table tr td.select_multiple_checkbox input[type=checkbox]',
										categoryCheckboxSelector: 'table.select_multiple_table tr td.select_multiple_checkbox_category input[type=checkbox]',
										nameSelector: 'table.select_multiple_table tr td.select_multiple_name label',
										overflowLength: 70,
										filter: 'group_members_'+id+'_select_filter',
										afterCheck: function(element) {
											var tr = $(element.parentNode.parentNode);
											tr.removeClassName('selected');
											if(element.checked) {
												tr.addClassName('selected');
											}
										},
										updateDiv: function(options, isnew) {
											updatePeopleList(options, $('group_members_'+id+'_category_select').selectedIndex, id);
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
				function memberChecks(id) {
					if ($$('.delchk_'+id+':checked').length&&!disablestatus[id]) {
						disablestatus[id] = 1;
						toggleDisabled($('additions_'+id),true);
						$('delbutton_'+id).style.display = 'block';
						$('additions_'+id).fade({ duration: 0.3, to: 0.25 });
					} else if (!$$('.delchk_'+id+':checked').length&&disablestatus[id]) {
						disablestatus[id] = 0;
						toggleDisabled($('additions_'+id),false);
						$('delbutton_'+id).style.display = 'none';
						$('additions_'+id).fade({ duration: 0.3, to: 1.0 });
					}
				}


		<?php
		if (false && isset($added_ids) && $added_ids) {
			?>
			var ids = [];
			var people = [];
			<?php
			foreach ($added_ids as $key => $added_ids_array) {
				if ($added_ids_array) {
					?>
					ids[<?php echo $key; ?>] = [<?php echo implode(",", $added_ids_array); ?>];
					people[<?php echo $key; ?>] = [];
					<?php
					foreach ($added_ids_array as $id) {
						?>
						people[<?php echo $key; ?>].push('<?php echo $added_people[$id]; ?>');
						<?php
					}
				}
			}
		} ?>
		var disablestatus = [];

		</script>
		<br /><br />
		<?php
		break;
	}
}
