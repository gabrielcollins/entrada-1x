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
 * This file is used to add groups.
 *
 * @author Organisation: University of Calgary
 * @author Unit: School of Medicine
 * @author Developer: Doug Hall <hall@ucalgary.ca>
 * @copyright Copyright 2011 University of Calgary. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_COURSE_GROUPS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('group', 'create', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	ini_set('auto_detect_line_endings',true);

	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/eventtypes_list.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";

	echo "<script language=\"text/javascript\">var DELETE_IMAGE_URL = '".ENTRADA_URL."/images/action-delete.gif';</script>";
	
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/course/groups?".replace_query(array("section" => "add")), "title" => "Adding Group");

	$group_type = "individual";
	$group_populate = "group_number";
	$number_of_groups = "";
	$populate = 0;
	$GROUP_IDS = array();

	echo "<h1>Add Group</h1>\n";
	
	// Error Checking
	switch($STEP) {
		case 2 :
			/*
			 *  CSV file format "group_name, first_name, last_name, status, entrada_id"
			 */
			$PROCESSED["updated_date"]	= time();
			$PROCESSED["updated_by"]	= $_SESSION["details"]["id"];
			
			/**
			 * Required field "prefix" / Group Name.
			 */
			if ((isset($_POST["prefix"])) && ($group_prefix = clean_input($_POST["prefix"], array("notags", "trim")))) {
				$PROCESSED["group_name"] = $group_prefix;
			} else {
				$ERROR++;
				$ERRORSTR[] = "The <strong>Group Prefix</strong> field is required.";
			}

			/**
			 * Non-required field "associated_faculty" / Associated Faculty (array of proxy ids).
			 * This is actually accomplished after the event is inserted below.
			 */
			if (isset($_POST["group_type"])) {
				$group_type = clean_input($_POST["group_type"], array("page_url"));

				switch($group_type) {
					case "individual" :
						if (!((isset($_POST["group_number"])) && ($number_of_groups = clean_input($_POST["group_number"], array("trim", "int"))))) {
							$ERROR++;
							$ERRORSTR[] = "A <strong>Number of Groups</strong> value is required.";
						}
					break;
					case "populated" :
						if (!((isset($_POST["number"])) && ($number_of_groups = clean_input($_POST["number"], array("trim", "int"))))) {
							$number_of_groups = 0;
						}
						if (!((isset($_POST["size"])) && ($size_of_groups = clean_input($_POST["size"], array("trim", "int"))))) {
							$size_of_groups = 0;
						}

						if (isset($_POST["group_populate"])) {
							$group_populate = clean_input($_POST["group_populate"], array("page_url"));
							switch($group_populate) {
								case "group_number" :
									if (!$number_of_groups) {
										$ERROR++;
										$ERRORSTR[] = "A value for <strong>Number of Groups</strong> is required.";
									}
								break;
								case "group_size" :
									if (!$size_of_groups) {
										$ERROR++;
										$ERRORSTR[] = "A value for <strong>Group size</strong> is required.";
									}
								break;
								default:
									$ERROR++;
									$ERRORSTR[] = "Unable to proceed because the <strong>Groups</strong> style is unrecognized.";
								break;
							}
						} else {
							$ERROR++;
							$ERRORSTR[] = "Unable to proceed because the <strong>Groups</strong> style is unrecognized.";
						}
						$populate = 1;
					break;
					default :
						$ERROR++;
						$ERRORSTR[] = "Unable to proceed because the <strong>Grouping</strong> type is unrecognized.";

						application_log("error", "Unrecognized group_type [".$_POST["group_type"]."] encountered.");
					break;
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "Unable to proceed because the <strong>Grouping</strong> type is unrecognized.";

				application_log("error", "The group_type field has not been set.");
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

			if (!$ERROR) {
				
				$query = "SELECT * FROM `courses` WHERE `course_id` = ".$db->qstr($COURSE_ID);
				if ($course = $db->GetRow($query)) {
					if ($course["permission"] == "closed") {
						$course_audience = true;
					} else {
						$course_audience = false;
					}
				}
				
				if (!$course_audience) {
					$query = "SELECT a.* FROM `course_audience` AS a 
								JOIN `curriculum_periods` AS b
								ON a.`cperiod_id` = b.`cperiod_id`
								WHERE a.`course_id` = ".$db->qstr($COURSE_ID)." 
								AND a.`audience_active` = 1
								AND b.`start_date` <= ".$db->qstr(time())."
								AND b.`finish_date` >= ".$db->qstr(time());
					$course_audience_record = $db->GetRow($query);
					if ($course_audience_record) {
						$course_audience = true;
					}
					
				}
				
				$PROCESSED["course_id"] = $COURSE_ID;
				$PROCESSED["active"] = 1;

				if ($number_of_groups == 1) {
					$result = $db->GetRow("SELECT `cgroup_id` FROM `course_groups` WHERE `group_name` = ".$db->qstr($PROCESSED["group_name"]));
					if ($result) {
						$ERROR++;
						$ERRORSTR[] = "The <strong>Group name</strong> already exists. The group was not created";
					} else {
						if (!$db->AutoExecute("course_groups", $PROCESSED, "INSERT")) {
							$ERROR++;
							$ERRORSTR[] = "There was an error while trying to add the <strong>Group</strong> ".$PROCESSED["group_name"].".<br /><br />The system administrator was informed of this error; please try again later.";
							application_log("error", "Unable to insert a new group ".$PROCESSED["group_name"].". Database said: ".$db->ErrorMsg());
						}
						$GROUP_IDS[] = $db->Insert_Id();
					}

				} else {
					$prefix = $PROCESSED["group_name"].' ';

					if ($group_populate == "group_size") {
						if ($course_audience) {
							$query	= "	SELECT COUNT(a.`id`)
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										JOIN `".AUTH_DATABASE."`.`user_access` AS b
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
										AND d.`finish_date` >= ".$db->qstr(time());
							$students = $db->GetOne($query);
							$query	= "	SELECT COUNT(a.`id`)
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
										AND f.`start_date` <= ".$db->qstr(time())."
										AND f.`finish_date` >= ".$db->qstr(time())."
										AND d.`group_active` = 1
										AND e.`member_active` = 1
										AND ((d.`start_date` <= ".$db->qstr(time())." OR d.`start_date` = 0)
										AND (d.`expire_date` >= ".$db->qstr(time())." OR d.`expire_date` = 0 OR d.`expire_date` IS NULL))";
							$students += $db->GetOne($query);
						} else {
							$query	= "	SELECT a.`id`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON a.`id` = b.`user_id`
										WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
										AND b.`group` = 'student'										
										AND b.`account_active` = 'true'
										AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
										AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
										GROUP BY a.`id`
										ORDER BY a.`lastname` ASC, a.`firstname` ASC";
							$students = count($db->GetAll($query));
						}
						$number_of_groups = ceil($students / $size_of_groups) ;
					}
					$dfmt = "%0".strlen((string) $number_of_groups)."d";

					$result = false;
					for ($i=1;$i<=$number_of_groups&&!$result;$i++){
						$result = $db->GetRow("SELECT `cgroup_id` FROM `course_groups` WHERE `group_name` = ".$db->qstr($prefix.sprintf($dfmt,$i))." AND `course_id` = ".$db->qstr($COURSE_ID));	
					}
					if ($result) {
						$ERROR++;
						$ERRORSTR[] = "A <strong>Group name</strong> already exists. The groups were not created";
					} else {
						for ($i=1;$i<=$number_of_groups;$i++) {
							$PROCESSED["group_name"] = $prefix.sprintf($dfmt,$i);
							$PROCESSED["active"] = 1;
							if (!$db->AutoExecute("course_groups", $PROCESSED, "INSERT")) {
								$ERROR++;
								$ERRORSTR[] = "There was an error while trying to add the <strong>Group</strong> ".$PROCESSED["group_name"].".<br /><br />The system administrator was informed of this error; please try again later.";
								application_log("error", "Unable to insert a new group ".$PROCESSED["group_name"].". Database said: ".$db->ErrorMsg());
								break;
							}
							$GROUP_IDS[] = $db->Insert_Id();
						}
					}
				}	
				if ($populate) {
					unset($PROCESSED["group_name"]);
					$PROCESSED["active"] = 1;
					if ($course_audience) {
						$query	= "	SELECT a.`id`
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
									
									SELECT a.`id`
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
									AND f.`start_date` <= ".$db->qstr(time())."
									AND f.`finish_date` >= ".$db->qstr(time())."
									AND d.`group_active` = 1
									AND e.`member_active` = 1
									AND (d.`start_date` <= ".$db->qstr(time())." OR d.`start_date` = 0)
									AND (d.`expire_date` >= ".$db->qstr(time())." OR d.`start_date` = 0)
									
									ORDER By RAND()";
					} else {
						$query	= "	SELECT a.`id`
									FROM `".AUTH_DATABASE."`.`user_data` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
									ON a.`id` = b.`user_id`
									WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
									AND b.`group` = 'student'
									AND b.`account_active` = 'true'
									AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
									AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
									GROUP BY a.`id`
									ORDER BY a.`lastname` ASC, a.`firstname` ASC";
					}
					$results = $db->GetAll($query);

					$i = 0;
					foreach($results as $result) {
						$PROCESSED["proxy_id"] =  $result["id"];
						
						$PROCESSED["cgroup_id"] =  $GROUP_IDS[$i];
						$i++;
						if (!$db->AutoExecute("course_group_audience", $PROCESSED, "INSERT")) {
							$ERROR++;
							$ERRORSTR[] = "There was an error while trying to add an audience member to the database.<br /><br />The system administrator was informed of this error; please try again later.";
							application_log("error", "Unable to insert a new group member ".$PROCESSED["proxy_id"].". Database said: ".$db->ErrorMsg());
							break;
						}
						if ($i==$number_of_groups) {
							$i = 0;
						}
					}
				}
				if (!$ERROR) {
					switch($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"]) {
						case "content" :
							$url	= ENTRADA_URL."/admin/courses/groups?section=edit&id=".$COURSE_ID."&ids=".implode(",", $GROUP_IDS);
							$msg	= "You will now be redirected to the event content page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
						break;
						case "new" :
							$url	= ENTRADA_URL."/admin/courses/groups?section=add&id=".$COURSE_ID;
							$msg	= "You will now be redirected to add more group(s); this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
						break;
						case "index" :
						default :
							$url	= ENTRADA_URL."/admin/courses/groups?id=".$COURSE_ID;
							$msg	= "You will now be redirected to the group index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
						break;
					}
	
					$SUCCESS++;
					$SUCCESSSTR[] = "You have successfully added <strong>".$number_of_groups." course groups</strong> to the system.<br /><br />".$msg;
					$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";
	
					application_log("success", "New course groups added for course [".$COURSE_ID."] added to the system.");
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "There was a problem inserting group into the system. The system administrator was informed of this error; please try again later.";

				application_log("error", "There was an error inserting a group. Database said: ".$db->ErrorMsg());
			}

			if ($ERROR) {
				$STEP = 1;
			}
		break;
		case 1 :
		default :
			continue;
		break;
	}

	// Display Content
	switch($STEP) {
		case 2 :
			display_status_messages();
		break;
		case 1 :
		default :
			$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
			$ONLOAD[] = "selectgroupOption('".$group_type."',0)";

			if ($ERROR) {
				echo display_error();
			}

			?>
			<form id="frmSubmit" action="<?php echo ENTRADA_URL; ?>/admin/courses/groups?<?php echo replace_query(array("step" => 2)); ?>" method="post" id="addGroupForm">
				<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Adding Group">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 20%" />
						<col style="width: 77%" />
					</colgroup>
					<tr>
						<td colspan="3"><h2>Group Details</h2></td>
					</tr>
					<tr class="prefixR">
						<td></td>
						<td><label for="prefix" class="form-required">Group Prefix</label></td>
						<td><input type="text" id="prefix" name="prefix" value="<?php echo (isset($PROCESSED["group_name"]) && $PROCESSED["group_name"] ? html_encode($PROCESSED["group_name"]) : ""); ?>" maxlength="255" style="width: 45%" /></td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
						<td>
							<div class="content-small">This will be used as the first portion of the default name for the groups created, with the second portion being which number the group is (eg. For the prefix "small group" the first 3 groups created would be named: small group 1, small group 2 and small group 3).</div>
						</td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td style="vertical-align: top"><input type="radio" name="group_type" id="group_type_individual" value="individual" onclick="selectgroupOption('individual',0)" style="vertical-align: middle"<?php echo (($group_type == "individual") ? " checked=\"checked\"" : ""); ?> /></td>
						<td colspan="2" style="padding-bottom: 15px">
							<label for="group_type_individual" class="radio-group-title">Empty Groups</label>
							<div class="content-small">These groups will be created without any members, then you may add whichever members you require on the next screen.</div>
						</td>
					</tr>
					<tr class="group_members individual_members">
						<td></td>
						<td style="vertical-align: top; text-align: left"><label for="number_of_groups" class="form-required">Number of Groups</label></td>
						<td><input type="text" id="empty_group_number" name="group_number" value="<?php echo html_encode($number_of_groups); ?>" maxlength="10" style="width: 15%" />
							<span class="content-small">   For a single group the Group Prefix is the Group name.</span>
						</td>
					</tr>
					<tr>
						<td style="vertical-align: top"><input type="radio" name="group_type" id="group_type_populated" value="populated" onclick="selectgroupOption('populated',0)" style="vertical-align: middle"<?php echo (($group_type == "populated") ? " checked=\"checked\"" : ""); ?> /></td>
						<td colspan="2" style="padding-bottom: 15px">
							<label for="group_type_populated" class="radio-group-title">Prepopulated Groups</label>
							<div class="content-small">These groups will be prepopulated with a number of users based on either the number of groups, or the explicit maximum group size.</div>
						</td>
					</tr>
					<tr class="group_members populated_members">
						<td></td>
						<td><label class="form-required">Populate based on:</label></td>
						<td>
							<div style="display: inline-block; width: 180px;">
								<input type="radio" onchange="toggleGroupTextbox()" name="group_populate" id="group_populate_group_number" value="group_number" <?php echo (!isset($group_populate) || ($group_populate == "group_number") ? " checked=\"checked\"" : ""); ?> />
								<label for="group_number" class="form-nrequired">Number of Groups</label>
							</div>
							<input type="text" id="group_number" name="number" value="<?php echo html_encode($number_of_groups); ?>"  style="width: 50px<?php echo (!isset($group_populate) || ($group_populate == "group_number") ? "" : "; display: none;"); ?>" />
						</td>
					<tr class="group_members populated_members">
						<td colspan="2">&nbsp;</td>
						<td>
							<div style="display: inline-block; width: 180px;">
								<input type="radio" onchange="toggleGroupTextbox()" name="group_populate" id="group_populate_group_size" value="group_size" <?php echo (($group_populate == "group_size") ? " checked=\"checked\"" : ""); ?> />
								<label for="group_size" class="form-nrequired">Group Size</label>
							</div>
							<input type="text" id="group_size" name="size" value="<?php echo html_encode($number_of_groups); ?>"  style="width: 50px<?php echo (($group_populate == "group_size") ? "" : "; display: none;"); ?>" />
						</td>
					</tr>
					<tr>
						<td colspan="3" style="padding-top: 25px">
							<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
								<tr>
									<td style="width: 25%; text-align: left">
										<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/courses/groups?id=<?php echo $COURSE_ID; ?>'" />
									</td>
									<td style="width: 75%; text-align: right; vertical-align: middle">
										<span class="content-small">After saving:</span>
										<select id="post_action" name="post_action">
											<option value="content"<?php echo (((!isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"])) || ($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "content")) ? " selected=\"selected\"" : ""); ?>>Add members to group(s)</option>
											<option value="new"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "new") ? " selected=\"selected\"" : ""); ?>>Add additional group(s)</option>
											<option value="index"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "index") ? " selected=\"selected\"" : ""); ?>>Return to group list</option>
										</select>
										<input type="submit" class="button" value="Proceed" />
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</form>
			<script type="text/javascript">
				function selectgroupOption(type,files) {
					$$('.group_members').invoke('hide');
					$$('.'+type+'_members').invoke('show');
					if (files) {
						$$('.prefixR').invoke('hide');
						$('file').style.display = 'block';
						$('file').disabled = false;
						$('frmSubmit').enctype="multipart/form-data";
					} else {
						$$('.prefixR').invoke('show');
						$('frmSubmit').enctype="application/x-www-form-urlencoded";
						$('file').style.display = 'none';
						$('file').disabled = true;
					}
				}
				
				function toggleGroupTextbox () {
					if ($('group_populate_group_size').checked) {
						$('group_size').show();
						$('group_number').hide();
					} else {
						$('group_size').hide();
						$('group_number').show();
					}
				}
			</script>
			<br /><br />
			<?php
		break;
	}
}
?>
