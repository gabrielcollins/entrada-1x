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

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_GROUPS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('group', 'update')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	// ERROR CHECKING

	switch ($STEP) {
		case "2" :
			if((isset($_POST["add_group_id"])) && ((int) trim($_POST["add_group_id"])) && strlen($_POST["group_members"])) {
				$PROCESSED["group_id"] = (int) trim($_POST["add_group_id"]);
			} else {
				header("Location: ".ENTRADA_URL."/admin/".$MODULE);
			}

			$proxy_ids = explode(',', $_POST["group_member_ids"]);
			foreach ($proxy_ids as $proxy_id) {
				$added_proxy_ids[] = (int) $proxy_id;
			}

            $PROCESSED["entrada_only"] = 1;
			$PROCESSED["updated_date"] = time();
			$PROCESSED["updated_by"] = $ENTRADA_USER->getID();

			$count = $added = 0;
			foreach($proxy_ids as $proxy_id) {
				if(($proxy_id = (int) trim($proxy_id))) {
					$count++;
					if (!$db->GetOne("SELECT `gmember_id` FROM `group_members` WHERE `group_id` = ".$db->qstr($PROCESSED["group_id"])." AND `proxy_id` =".$db->qstr($proxy_id))) {
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

			if(!$count) {
				$ERROR++;
				$ERRORSTR[] = "You must select a user(s) to add to this group. Please be sure that you select at least one user to add this event to from the interface.";
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
            $SUCCESS++;
            $SUCCESSSTR[] = "You have successfully added this member";
			echo display_success($SUCCESSSTR);
		break;

		default :			// Step 1
			$group_ids = array();


/**
 * @todo What the heck is this? Will who ever did this, please fix this. It's crap.
 *
 */
			if(isset($PROCESSED["group_id"]) && (int)$PROCESSED["group_id"]) {
				$GROUP_ID = $PROCESSED["group_id"];
			} else {
				$GROUP_ID = 0;
			}
			if (isset($_GET["ids"])) {
				$_SESSION["ids"] = array(htmlentities($_GET["ids"]));
			} elseif (isset($_POST["checked"])) {
				$_SESSION["ids"] = $_POST["checked"];
			} elseif((isset($_POST["group_id"])) && ((int) trim($_POST["group_id"]))) {
				$GROUP_ID = (int) trim($_POST["group_id"]);
			} elseif((isset($_GET["id"])) && ((int) trim($_GET["id"]))) {
				$GROUP_ID = (int) trim($_GET["id"]);
			}

			if ((!isset($_SESSION["ids"]) || !is_array($_SESSION["ids"])) || (!@count($_SESSION["ids"]))) {
				header("Location: ".ENTRADA_URL."/admin/groups");
				exit;
			}

			$group_ids = $_SESSION["ids"];

			$query = "	SELECT * FROM `groups`
						WHERE `group_id` IN (".implode(", ", $group_ids).")
						ORDER By `group_name`";
			$results	= $db->GetAll($query);

			if (!$results) {
				header("Location: ".ENTRADA_URL."/admin/".$MODULE);
			}
			if (!$GROUP_ID) {
				$GROUP_ID = $results[0]["group_id"]; // $group_ids[0];
			}


			$query = "	SELECT a.*, b.`organisation_id` FROM `groups` AS a
						LEFT JOIN `group_organisations` AS b
						ON a.`group_id` = b.`group_id`
						WHERE a.`group_id` = ".$db->qstr($GROUP_ID);
			if ($group = $db->GetRow($query)) {
				$group_name = $group["group_name"];
				$PROCESSED = $group;
			}

			$emembers_query	= "	SELECT c.`gmember_id`, CONCAT_WS(' ', a.`firstname`, a.`lastname`) AS `fullname`, c.`member_active`,
								a.`username`, a.`organisation_id`, a.`username`, CONCAT_WS(':', b.`group`, b.`role`) AS `grouprole`
								FROM `".AUTH_DATABASE."`.`user_data` AS a
								LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
								ON a.`id` = b.`user_id`
								INNER JOIN `group_members` c ON a.`id` = c.`proxy_id`
								WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
								AND b.`account_active` = 'true'
								AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
								AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
								AND c.`group_id` = ".$db->qstr($GROUP_ID)."
								GROUP BY a.`id`
								ORDER BY a.`lastname` ASC, a.`firstname` ASC";
			$ONLOAD[]	= "showgroup('".$group_name."',".$GROUP_ID.")";

			$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/groups?section=edit", "title" => "Edit");

			?>
			<h1>Editing Groups</h1>

			<h2>Selected Groups</h2>
			<form action="<?php echo ENTRADA_URL; ?>/admin/<?php echo "$MODULE"; ?>?section=edit&step=1" method="post" id="addMembersForm">
				<input type="hidden" id="step" name="step" value="1" />
				<input type="hidden" id="group_id" name="group_id" value="" />

				<?php echo (($ERROR) ? display_error($ERRORSTR) : ""); ?>
				<table class="tableList" cellspacing="1" cellpadding="1">
					<colgroup>
						<col style="width: 6%" />
						<col style="width: 54%" />
						<col style="width: 30%" />
						<col style="width: 10%" />
					</colgroup>
					<thead>
						<tr>
							<td></td>
							<td>Group Name</td>
							<td>Members</td>
							<td></td>
						</tr>
					</thead>
					<tbody>
					<?php
						foreach($results as $result) {
							$members = $db->GetRow("SELECT COUNT(*) AS members, case when (MIN(`member_active`)=0) then 1 else 0 end as `inactive`
													FROM  `group_members` WHERE `group_id` = ".$db->qstr($result["group_id"]));

								echo "<tr class=\"group".((!$result["group_active"]) ? " na" : (($members["inactive"]) ? " np" : ""))."\">";
								echo "	<td><input type=\"radio\" name=\"groups\" value=\"".$result["group_id"]."\" onclick=\"selectgroup(".$result["group_id"].",'".$result["group_name"]."');\"".(($result["group_id"] == $GROUP_ID) ?" checked=\"checked\"" : "")."/></td>\n";
								echo "	<td><a href=\"".ENTRADA_URL."/admin/groups?section=edit&id=".$result["group_id"]."\" >".html_encode($result["group_name"])."</a></td>";
								echo "	<td><a href=\"".ENTRADA_URL."/admin/groups?section=edit&id=".$result["group_id"]."\" >".$members["members"]."</a></td>";
								echo "	<td>
											<a href=\"".ENTRADA_URL."/admin/groups?section=manage&gids=".$result["group_id"]."\"><img src=\"".ENTRADA_URL."/images/action-edit.gif\" width=\"16\" height=\"16\" alt=\"Rename Group\" title=\"Rename Group\" border=\"0\" /></a>&nbsp;
											<a href=\"".ENTRADA_URL."/admin/groups?section=manage&ids=".$result["group_id"]."\"><img src=\"".ENTRADA_URL."/images/action-delete.gif\" width=\"16\" height=\"16\" alt=\"Delete/Activate Group\" title=\"Delete/Activate Group\" border=\"0\" /></a>
										</td>\n";
								echo "</tr>";
						}
					?>
					</tbody>
				</table>
			</form>
			<?php

//			$orgs = array_keys($ENTRADA_USER->getAllOrganisations());
//			$query = "SELECT * FROM `group_organisations` WHERE `group_id` =".$db->qstr($GROUP_ID)." AND `organisation_id` IN (".implode(",",$orgs).")";
//			print_r($ENTRADA_USER->getAllOrganisations());
//			if ($result = $db->GetAll($query)) {
			?>
			<h2>View Selected Group Members</h2>
			<form action="<?php echo ENTRADA_URL; ?>/admin/groups?section=manage" method="post">
				<table class="tableList" cellspacing="1" cellpadding="1">
					<colgroup>
						<col style="width: 6%" />
						<col style="width: 54%" />
						<col style="width: 30%" />
						<col style="width: 10%" />
					</colgroup>
					<thead>
						<tr>
							<td></td>
							<td>Full Name</td>
							<td>Group &amp; Role</td>
							<td></td>
						</tr>
					</thead>
					<tbody>
					<?php
						$results = $db->GetAll($emembers_query);
						if ($results) {
							foreach($results as $result) {
								echo "<tr class=\"event".(!$result["member_active"] ? " na" : "")."\">";
								echo "	<td><input type=\"checkbox\" class=\"delchk\" name=\"checked[]\" onclick=\"memberChecks()\" value=\"".$result["gmember_id"]."\" /></td>\n";
								echo "	<td><a href=\"".ENTRADA_URL."/people?profile=".$result["username"]."\" >".html_encode($result["fullname"])."</a></td>";
								echo "	<td><a href=\"".ENTRADA_URL."/people?profile=".$result["username"]."\" >".$result["grouprole"]."</a></td>";
								echo "	<td>
											<a href=\"".ENTRADA_URL."/admin/groups?section=manage&mids=".$result["gmember_id"]."\"><img src=\"".ENTRADA_URL."/images/action-delete.gif\" width=\"16\" height=\"16\" alt=\"Delete/Activate Member\" title=\"Delete/Activate Member\" border=\"0\" /></a>
										</td>\n";
								echo "</tr>";
							}
						}
					?>
					</tbody>
				</table>

				<div id="delbutton" style="padding-top: 15px; text-align: right; display:none">
					<input type="submit" class="button" value="Delete/Activate" style="vertical-align: middle" />
				</div>

				<input type="hidden" name="members" value="1" />
			</form>
			<br />
			<form action="<?php echo ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("section" => "edit", "type" => "add", "step" => 2)); ?>" method="post">
				<div id="additions">
					<h2 style="margin-top: 10px">Add Members</h2>
					<table style="margin-top: 1px; width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Add Member">
						<colgroup>
							<col style="width: 45%" />
							<col style="width: 10%" />
							<col style="width: 45%" />
						</colgroup>
						<tfoot>
							<tr>
								<td colspan="3" style="padding-top: 15px; text-align: right">
									<input type="submit" class="button" value="Proceed" style="vertical-align: middle" />
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
														if ($added_proxy_ids) {
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
																		AND a.`id` IN (".implode(",", $added_proxy_ids).")";
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
										<input id="group_member_ids" name="group_member_ids" value="<?php  echo (isset($added_proxy_ids) && $added_proxy_ids ? implode(",", $added_proxy_ids) : "") ?>" style="display: none;">
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

		<?php
//		} else {
//			add_error("You do not have access to change edit this group.");
//			echo display_error();
//		}
		?>
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
						'group_id':'<?php echo $GROUP_ID;?>',
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

		function selectgroup(group,name) {
			$('group_id').value = group;
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