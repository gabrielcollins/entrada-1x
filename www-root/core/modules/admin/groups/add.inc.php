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

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_GROUPS"))) {
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

	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";

	echo "<script language=\"text/javascript\">var DELETE_IMAGE_URL = '".ENTRADA_URL."/images/action-delete.gif';</script>";
	
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/groups?".replace_query(array("section" => "add")), "title" => "Adding Group");

	$group_type = "individual";
	$group_populate = "group_number";
	$group_active = "true";
	$number_of_groups ="";
	$populate = 0;
	$GROUP_IDS = array();

	echo "<h1>Add Group</h1>\n";
	
	// Error Checking
	switch($STEP) {
		case 2 :
			/**
			 * Get the active organisation_id and add it to the PROCESSED array.
			 */
			$PROCESSED["organisation_id"] = $ENTRADA_USER->getActiveOrganisation();
			
			/**
			 * Required field "group_name" / Group Name.
			 */
			if ((isset($_POST["group_name"])) && ($group_name = clean_input($_POST["group_name"], array("notags", "trim")))) {
				$PROCESSED["group_name"] = $group_name;
			} else {
				$ERROR++;
				$ERRORSTR[] = "The <strong>Group Name</strong> field is required.";
			}
			
			/**
			 * Required field "group_type" / Group Type.
			 */
			if ((isset($_POST["group_type"])) && ($group_type = clean_input($_POST["group_type"], array("trim"))) && in_array($group_type, array("course_list", "cohort"))) {
				$PROCESSED["group_type"] = $group_type;
			} else {
				$ERROR++;
				$ERRORSTR[] = "The <strong>Group Type</strong> field is required.";
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

			$proxy_ids = explode(',', $_POST["group_member_ids"]);
			
			foreach ($proxy_ids as &$proxy_id) {
				$proxy_id = (int) $proxy_id;
			}
			$PROCESSED["updated_date"]	= time();
			$PROCESSED["updated_by"] = $_SESSION["details"]["id"];
			
			if (!$ERROR) {
				$result = $db->GetRow("SELECT `group_id` FROM `groups` WHERE `group_name` = ".$db->qstr($PROCESSED["group_name"]));
				if ($result) {
					$ERROR++;
					$ERRORSTR[] = "The <strong>Group name</strong> already exits. The group was not created";
				} else {
					if (!$db->AutoExecute("groups", $PROCESSED, "INSERT")) {
						$ERROR++;
						$ERRORSTR[] = "There was an error while trying to add the <strong>Group</strong> ".$PROCESSED["group_name"].".<br /><br />The system administrator was informed of this error; please try again later.";
						application_log("error", "Unable to insert a new group ".$PROCESSED["group_name"].". Database said: ".$db->ErrorMsg());
					}
					$GROUP_ID = $db->Insert_Id();
					$PROCESSED["group_id"] = $GROUP_ID;
					if (!$db->AutoExecute("group_organisations", $PROCESSED, "INSERT")) {
						$ERROR++;
						$ERRORSTR[] = "There was an error while trying to add the <strong>Group</strong> ".$PROCESSED["group_name"].".<br /><br />The system administrator was informed of this error; please try again later.";
						application_log("error", "Unable to insert a new group organisation for group_id [".$GROUP_ID."[. Database said: ".$db->ErrorMsg());
					} else {
						$added = 0;
						foreach($proxy_ids as $proxy_id) {
							if(($proxy_id = (int) trim($proxy_id))) {
								$PROCESSED["proxy_id"]	= $proxy_id;
								$added++;
								if (!$db->AutoExecute("`group_members`", $PROCESSED, "INSERT")) {
									$ERROR++;
									$ERRORSTR[]	= "Failed to insert this member into the group. Please contact a system administrator if this problem persists.";
									application_log("error", "Error while inserting member into database. Database server said: ".$db->ErrorMsg());
								}
							}
						}
					}
				}


				switch($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"]) {
					case "new" :
							$url	= ENTRADA_URL."/admin/groups?section=add";
							$msg	= "You will now be redirected to add another group; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
						break;
						case "index" :
						default :
							$url	= ENTRADA_URL."/admin/groups";
							$msg	= "You will now be redirected to the group index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
						break;
					}

					$SUCCESS++;
					$SUCCESSSTR[] = "You have successfully added <strong>".html_encode($PROCESSED["event_title"])."</strong> to the system.<br /><br />".$msg;
					$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";

					application_log("success", "New event [".$EVENT_ID."] added to the system.");
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

			if ($ERROR) {
				echo display_error();
			}

			?>
			<form id="frmSubmit" action="<?php echo ENTRADA_URL; ?>/admin/groups?section=add&amp;step=2" method="post" id="addGroupForm">
				<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Adding Group">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 20%" />
						<col style="width: 77%" />
					</colgroup>
					<tfoot>
						<tr>
							<td colspan="3" style="padding-top: 25px">
								<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
									<tr>
										<td style="width: 25%; text-align: left">
											<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/groups'" />
										</td>
										<td style="width: 75%; text-align: right; vertical-align: middle">
											<span class="content-small">After saving:</span>
											<select id="post_action" name="post_action">
												<option value="new"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "new") ? " selected=\"selected\"" : ""); ?>>Add another group</option>
												<option value="index"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "index") ? " selected=\"selected\"" : ""); ?>>Return to group list</option>
											</select>
											<input type="submit" class="button" value="Proceed" />
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</tfoot>
					<tr>
						<td colspan="3"><h2>Group Details</h2></td>
					</tr>
					<tr class="prefixR">
						<td></td>
						<td><label for="group_name" class="form-required">Group Name</label></td>
						<td><input type="text" id="group_name" name="group_name" value="<?php echo html_encode($PROCESSED["group_name"]); ?>" maxlength="255" style="width: 45%" /></td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><label for="group_type" class="form-required">Group Type</label></td>
						<td>
							<select id="group_type" name="group_type" style="width: 250px">
							<option value="0">-- Select a group type --</option>
							<option value="course_list"<?php echo ($PROCESSED["group_type"] == "course_list" ? " selected=\"selected\"" : ""); ?>>Course list</option>
							<option value="cohort"<?php echo ($PROCESSED["group_type"] == "cohort" ? " selected=\"selected\"" : ""); ?>>Cohort</option>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="3">
							<br />
							<div id="additions">
								<h2 style="margin-top: 10px">Add Members</h2>
								<form action="<?php echo ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("section" => "edit", "type" => "add", "step" => 2)); ?>" method="post">
									<table style="margin-top: 1px; width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Add Member">
										<colgroup>
											<col style="width: 45%" />
											<col style="width: 10%" />
											<col style="width: 45%" />
										</colgroup>
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
														$query		= "SELECT `proxy_id` FROM `group_members` WHERE `group_id` = ".$db->qstr($GROUP_ID)." AND `member_active` = '1'";
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
				
															$added_ids = array();
															$added_people = array();
															$key_value = 1;
															foreach($members as $key => $member) {
																if(isset($member['options']) && is_array($member['options']) && !empty($member['options'])) {
																	sort($members[$key]['options']);
																	foreach ($members[$key]['options'] as $member_group) {
																		$tmp_array = explode("|", $member_group["value"]);
																		$organisation_id = $tmp_array[0];
																		$group = $tmp_array[1];
																		$role = $tmp_array[2];
																		if ($proxy_ids) {
																			$query = "SELECT a.`id`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) as `fullname` FROM `".AUTH_DATABASE."`.`user_data` AS a
																						JOIN `".AUTH_DATABASE."`.`user_access` AS b
																						ON a.`id` = b.`user_id`
																						WHERE a.`organisation_id` = ".$db->qstr($organisation_id)."
																						AND b.`group` = ".$db->qstr($group)."
																						".($group == "student" ? "AND b.`role` = ".$db->qstr($role) : "")."
																						AND b.`app_id` IN (".AUTH_APP_IDS_STRING.")
																						AND b.`account_active` = 'true'
																						AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
																						AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
																						AND a.`id` IN (".implode(",", $proxy_ids).")";
																			if ($member_ids = $db->GetAll($query)) {
																				$added_ids[$key_value] = array();
																				foreach ($member_ids as $member_id) {
																					$added_ids[$key_value][] = (int) $member_id["id"];
																					$added_people[$member_id["id"]] = $member_id["fullname"];
																				}
																			}
																			$key_value++;
																		}
																	}
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
														<input id="group_members_index" name="group_members_index" style="display: none;">
														<input id="group_member_ids" name="group_member_ids" value="<?php  echo (isset($proxy_ids) && $proxy_ids ? implode(",", $proxy_ids) : "") ?>" style="display: none;">
													</div>
												</td>
												<td style="vertical-align: top; padding-left: 20px;">
													<h3>Members to be Added on Submission</h3>
													<div id="group_members_list">
													<?php
														if ($added_people) {
															echo "<table class=\"member-list\">";
															echo "	<tr>";
															$tmp_count = 0;
															foreach ($added_people as $fullname) {
																if ($tmp_count%2 == 0 && $tmp_count) {
																	echo "	</tr>";
																	echo "	<tr>";
																}
																$tmp_count++;
																echo "<td>".$fullname."</td>";
															}
															echo "	</tr>";
															echo "</table>";
														}
													?>
													</div>
												</td>
											</tr>
										</tbody>
									</table>
									<input type="hidden" id="add_group_id" name="add_group_id" value="" />
								</form>
							</div>
						</td>
					</tr>
				</table>
			</form>
			<script type="text/javascript">
				<?php
				if (isset($added_ids) && $added_ids) {
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
				} else { 
					?>
					var people = [[]];
					var ids = [[]];
					<?php 
				} 
				?>
				var disablestatus = 0;
		
				//Updates the People Being Added div with all the options
				function updatePeopleList(newoptions, index) {
					if ($('group_members_index').value == index) {
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
						var member_ids = "";
						if ($F('group_members')) {
							ids[index] = $F('group_members').split(',').compact();
							$('group_member_ids').value = ids.flatten().join(',');
						}
					} else {
						$('group_members_index').value = index;
					}
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
						var updater = new Ajax.Updater('group_members_scroll', '<?php echo ENTRADA_URL."/admin/groups?section=membersapi";?>',{
							method:'post',
							parameters: {
								'ogr':$F('group_members_category_select'),
								'group_id':'0',
								'added_ids[]':ids[$('group_members_category_select').selectedIndex]
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
?>
