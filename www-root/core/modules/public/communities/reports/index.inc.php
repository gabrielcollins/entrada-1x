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
 * This file is used to add events to the entrada.events table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2008 Queen's University. All Rights Reserved.
 *
*/

if((!defined("PARENT_INCLUDED")) || (!defined("IN_COMMUNITIES"))) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('community', 'update', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	$query				= "	SELECT * FROM `communities`
							WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)."
							AND `community_active` = '1'";

	$community_details	= $db->GetRow($query);
	if($community_details) {
		$community_resource = new CommunityResource($COMMUNITY_ID);
		if($ENTRADA_ACL->amIAllowed($community_resource, 'update')) {
			echo "<h1>".html_encode($community_details["community_title"])."</h1>\n";

			// Error Checking
			switch($STEP) {
				case 3 :
				case 2 :
					$PROCESSED["community_members"]	= "";
					$PROCESSED["sub_communities"]	= 0;

					/**
					 * Required: Community Name / community_title
					 */
					if((isset($_POST["community_title"])) && ($community_title = clean_input($_POST["community_title"], array("notags", "trim")))) {
						$PROCESSED["community_title"] = substr($community_title, 0, 64);
					} else {
						$ERROR++;
						$ERRORSTR[] = "Please provide a title for your new community. Example: Medicine Club";
					}

					/**
					 * Not Required: Community Keywords / community_keywords
					 */
					if((isset($_POST["community_keywords"])) && ($community_keywords = clean_input($_POST["community_keywords"], array("notags", "trim")))) {
						$PROCESSED["community_keywords"] = substr($community_keywords, 0, 255);
					} else {
						$PROCESSED["community_keywords"] = "";
					}

					/**
					 * Not Required: Community Description / community_description
					 */
					if((isset($_POST["community_description"])) && ($community_description = clean_input($_POST["community_description"], array("notags", "trim")))) {
						$PROCESSED["community_description"] = $community_description;
					} else {
						$PROCESSED["community_description"] = "";
					}

					/**
					 * Not Required: Contact E-Mail / community_email
					if((isset($_POST["community_email"])) && ($community_email = clean_input($_POST["community_email"], array("trim", "lower")))) {
						if(valid_address($community_email)) {
							$PROCESSED["community_email"] = $community_email;
						} else {
							$ERROR++;
							$ERRORSTR[] = "The e-mail address you provided [".html_encode($community_email)."] is not a valid e-mail address.";
						}
					} else {
						$PROCESSED["community_email"] = "";
					}

					/**
					 * Not Required: External Website / community_website
					if((isset($_POST["community_website"])) && ($community_website = clean_input($_POST["community_website"], array("trim", "notags", "lower")))) {
						$PROCESSED["community_website"] = $community_website;
					} else {
						$PROCESSED["community_website"] = "";
					}
					*/

					/**
					 * Required: Access Permissions / community_protected
					 */
					if(isset($_POST["community_protected"])) {
						if($community_protected = clean_input($_POST["community_protected"], array("trim", "int")) === 0) {
							$PROCESSED["community_protected"] = 0;
						} else {
							$PROCESSED["community_protected"] = 1;
						}
					} else {
						$ERROR++;
						$ERRORSTR[] = "You must specify the Access Permissions for this new community.";
					}

					/**
					 * Not Required: Sub-Communities / sub_communities
					 */
					if(isset($_POST["sub_communities"])) {
						if($community_protected = clean_input($_POST["sub_communities"], array("trim", "int")) == 1) {
							$PROCESSED["sub_communities"] = 1;
						}
					}

					/**
					 * Required: Mailing List Mode
					 */
					if ($MAILING_LISTS["active"] && isset($_POST["community_list_mode"])) {
						if (($list_mode = clean_input($_POST["community_list_mode"], array("nows", "lower"))) && $list_mode != $mailing_list->type) {
							$PROCESSED["community_list_mode"] = $list_mode;
						}
					} elseif ($MAILING_LISTS["active"] && !array_key_exists("list_type", $community_details)) {
						$ERROR++;
						$ERRORSTR[] = "You must specify which mode the mailing list for this community is in.";
					}

					/**
					 * Required: Registration Options / community_registration
					 */
					if(isset($_POST["community_registration"])) {
						switch(clean_input($_POST["community_registration"], array("trim", "int"))) {
							case 0 :	// Open Community
								$PROCESSED["community_registration"]	= 0;
								break;
							case 2 :	// Group Registration
								$PROCESSED["community_registration"]	= 2;


								if((isset($_POST["community_registration_groups"])) && (is_array($_POST["community_registration_groups"])) && (count($_POST["community_registration_groups"]))) {
									$community_groups = array();

									foreach($_POST["community_registration_groups"] as $community_group) {
										if(($community_group = clean_input($community_group, "credentials")) && (array_key_exists($community_group, $GROUP_TARGETS))) {
											$community_groups[] = $community_group;
										}
									}

									if(count($community_groups)) {
										$PROCESSED["community_members"] = serialize($community_groups);
									} else {
										$ERROR++;
										$ERRORSTR[] = "You have selected Group Registration under Registration Options, but have not chosen any Groups that are able to register. Please select at least one Group to continue.";

										application_log("error", "User selected Group Registration option, did provide groups, none of which could be validated.");
									}
								} else {
									$ERROR++;
									$ERRORSTR[] = "You have selected Group Registration under Registration Options, but have not chosen any Groups that are able to register. Please select at least one Group to continue.";
								}
								break;
							case 3 :	// Community Registration
								$PROCESSED["community_registration"]	= 3;

								if((isset($_POST["community_registration_communities"])) && (is_array($_POST["community_registration_communities"])) && (count($_POST["community_registration_communities"]))) {

									$community_communities = array();

									foreach($_POST["community_registration_communities"] as $community_id) {
										if($community_id = (int) trim($community_id)) {
											$query	= "SELECT * FROM `communities` WHERE `community_id` = ".$db->qstr($community_id)." AND `community_active` = '1'";
											$result	= $db->GetRow($query);
											if($result) {
												$community_communities[] = $community_id;
											}
										}
									}

									if(count($community_communities)) {
										$PROCESSED["community_members"] = serialize($community_communities);
									} else {
										$ERROR++;
										$ERRORSTR[] = "You have selected Community Registration under Registration Options, but have not chosen any Communites which can register. Please select at least one existing Community to continue.";

										application_log("error", "User selected Community Registration, did provide community_ids, none of which existed.");
									}
								} else {
									$ERROR++;
									$ERRORSTR[] = "You have selected Community Registration under Registration Options, but have not chosen any Communites which can register. Please select at least one existing Community to continue.";
								}
								break;
							case 4 :	// Private Community
								$PROCESSED["community_registration"]	= 4;
								break;
							case 1 :	// Open Registration
							default :
								$PROCESSED["community_registration"]	= 1;
								break;
						}
					} else {
						$ERROR++;
						$ERRORSTR[] = "You must specify the Registration Options for this new community.";
					}

					/**
					 * Required: Available Modules / community_modules
					 */
					if((is_array($_POST["community_modules"])) && (count($_POST["community_modules"]))) {
						$community_modules_selected 	= array();
						$community_modules_current		= array();

						$community_modules_activate		= array();	// List of modules that need to be activated.
						$community_modules_deactivate	= array();	// List of modules that need to be deactivated.

						foreach($_POST["community_modules"] as $module_id) {
							if($module_id = (int) $module_id) {
								$query	= "SELECT * FROM `communities_modules` WHERE `module_id` = ".$db->qstr($module_id)." AND `module_active` = '1'";
								$result	= $db->GetRow($query);
								if($result) {
									$community_modules_selected[] = $module_id;
								}
							}
						}

						if(count($community_modules_selected)) {
							$query		= "SELECT * FROM `community_modules` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `module_active` = '1'";
							$results	= $db->GetAll($query);
							if($results) {
								foreach($results as $result) {
									$query = "SELECT * FROM `communities_modules` WHERE `module_id` = ".$db->qstr($result["module_id"]);
									$module = $db->GetRow($query);
									if ($module["module_shortname"] != "default" && $module["module_visible"] != 0) {
										$community_modules_current[] = (int) $result["module_id"];
									}
								}
							}

							/**
							 * Check for modules to activate / deactivate.
							 */
							if($community_modules_selected != $community_modules_current) {
								$community_modules_activate		= array_diff($community_modules_selected, $community_modules_current);
								$community_modules_deactivate	= array_diff($community_modules_current, $community_modules_selected);
							}
						} else {
							$ERROR++;
							$ERRORSTR[] = "You must enable at least one module in your new community, otherwise there will be nothing to do in this community.";
						}
					} else {
						$ERROR++;
						$ERRORSTR[] = "You must enable at least one module in your new community, otherwise there will be nothing to do in this community.";
					}

					if($ERROR) {
						$PROCESSED	= $_POST;
						$STEP		= 1;
					}
					break;
				case 1 :
				default :
					$PROCESSED						= $community_details;
					$PROCESSED["community_modules"]	= array();
					$COMMUNITY_PARENT				= $community_details["community_parent"];
					$community_groups				= array();
					$community_communities			= array();

					$query		= "SELECT `module_id` FROM `community_modules` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `module_active` = '1'";
					$results	= $db->GetAll($query);
					if($results) {
						foreach($results as $result) {
							$PROCESSED["community_modules"][] = (int) $result["module_id"];
						}
					}


					if(($community_details["community_registration"] == 2) && ($community_details["community_members"])) {
						$community_groups = @unserialize($community_details["community_members"]);
					}

					if(($community_details["community_registration"] == 3) && ($community_details["community_members"])) {
						$community_communities = @unserialize($community_details["community_members"]);
					}
					break;
			}

			// Display Content
			switch($STEP) {
				case 3 :
					$community_url	= ENTRADA_URL."/community".$community_details["community_url"];

					$PROCESSED["updated_date"]	= time();
					$PROCESSED["updated_by"]	= $_SESSION["details"]["id"];

					if($db->AutoExecute("communities", $PROCESSED, "UPDATE", "`community_id` = ".$db->qstr($COMMUNITY_ID))) {
						if ($MAILING_LISTS["active"] && array_key_exists("community_list_mode", $PROCESSED) && $PROCESSED["community_list_mode"] != $mailing_list->type) {
							$mailing_list->mode_change($PROCESSED["community_list_mode"]);
						}
						$SUCCESS++;
						$SUCCESSSTR[] = "<strong>You have successfully updated your community.</strong><br /><br />Please <a href=\"".$community_url."\">click here</a> to proceed to it or you will be automatically forwarded in 5 seconds.";

						communities_log_history($COMMUNITY_ID, 0, $_SESSION["details"]["id"], "community_history_edit_community", 1);

						if($community_details["community_title"] != $PROCESSED["community_title"]) {
							communities_log_history($COMMUNITY_ID, 0, 0, "community_history_rename_community", 1);
						}
					}

					/**
					 * Process modules that need to be activated.
					 */
					if((is_array($community_modules_activate)) && (count($community_modules_activate))) {
						foreach($community_modules_activate as $module_id) {
							if(communities_module_activate($COMMUNITY_ID, $module_id)) {
								$module_details = communities_module_details($module_id, array("module_shortname", "module_title"));

								communities_log_history($COMMUNITY_ID, 0, $module_id, "community_history_activate_module", 1);
							} else {
								application_log("error", "Failed to activate module id ".$module_id." during a community update.");
							}
						}
					}

					/**
					 * Process modules that need to be deactivated.
					 */
					if((is_array($community_modules_deactivate)) && (count($community_modules_deactivate))) {
						foreach($community_modules_deactivate as $module_id) {
							if(!communities_module_deactivate($COMMUNITY_ID, $module_id)) {
								application_log("error", "Failed to activate module id ".$module_id." during a community update.");
							}
						}
					}

					application_log("success", "Community ID ".$community_id." was successfully updated.");

					if($NOTICE) {
						echo display_notice();
					}
					if($SUCCESS) {
						echo display_success();
					}

					$ONLOAD[]		= "setTimeout('window.location=\\'".$community_url."\\'', 5000)";

					break;
				case 2 :
					?>
<div class="display-notice" style="line-height: 175%">
<strong>Please review</strong> the following changes that will be made to your community once you press the &quot;Save Changes&quot; button at the bottom of the screen. If you have made a mistake, please press the &quot;Cancel&quot; button, <strong>not</strong> your browsers back button.
</div>
<form action="<?php echo ENTRADA_URL."/".$MODULE."?".replace_query(array("action" => "modify", "step" => 3)); ?>" method="post">
						<?php
						if (is_array($community_modules_selected)) {
							foreach($community_modules_selected as $key => $value) {
								echo "<input type=\"hidden\" name=\"community_modules[]\" value=\"".html_encode($value)."\" />\n";
							}
						}

						if (is_array($community_groups)) {
							foreach($community_groups as $key => $value) {
								echo "<input type=\"hidden\" name=\"community_registration_groups[]\" value=\"".html_encode($value)."\" />\n";
							}
						}

						if (is_array($community_communities)) {
							foreach($community_communities as $key => $value) {
								echo "<input type=\"hidden\" name=\"community_registration_communities[]\" value=\"".html_encode($value)."\" />\n";
							}
						}

						if (is_array($PROCESSED)) {
							foreach($PROCESSED as $key => $value) {
								if(is_array($value)) {
									foreach($value as $skey => $svalue) {
										echo "<input type=\"hidden\" name=\"".html_encode($key."[".$skey."]")."\" value=\"".html_encode($svalue)."\" />\n";
									}
								} else {
									echo "<input type=\"hidden\" name=\"".html_encode($key)."\" value=\"".html_encode($value)."\" />\n";
								}
							}
						}
						?>
<table style="width: 100%" cellspacing="0" cellpadding="4" border="0" summary="Review of pending community updates.">
	<colgroup>
		<col style="width: 3%" />
		<col style="width: 20%" />
		<col style="width: 77%" />
	</colgroup>
	<tfoot>
		<tr>
			<td colspan="3" style="padding-top: 15px">
				<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td style="width: 25%; text-align: left">
							<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL."/".$MODULE."?".replace_query(array("action" => "modify", "step" => 1)); ?>'" />
						</td>
						<td style="width: 75%; text-align: right; vertical-align: middle">
							<input type="submit" class="button" value="Save Changes" />
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</tfoot>
	<tbody>
								<?php
								if($PROCESSED["community_title"] != $community_details["community_title"]) {
									?>
		<tr>
			<td><?php echo help_create_button("Community Name", "communities-community_title"); ?></td>
			<td><span class="form-required">Community Name</span></td>
			<td><?php echo html_encode($PROCESSED["community_title"]); ?></td>
		</tr>
								<?php
								}

								if($PROCESSED["community_keywords"] != $community_details["community_keywords"]) {
									?>
		<tr>
			<td><?php echo help_create_button("Community Keywords", "communities-community_keywords"); ?></td>
			<td><span class="form-nrequired">Community Keywords</span></td>
			<td><?php echo html_encode($PROCESSED["community_keywords"]); ?></td>
		</tr>
								<?php
								}

								if($PROCESSED["community_description"] != $community_details["community_description"]) {
									?>
		<tr>
			<td style="vertical-align: top"><?php echo help_create_button("Community Description", "communities-community_description"); ?></td>
			<td style="vertical-align: top"><span class="form-nrequired">Community Description</span></td>
			<td><?php echo nl2br(html_encode($PROCESSED["community_description"])); ?></td>
		</tr>
								<?php
								}
/*
								if($PROCESSED["community_email"] != $community_details["community_email"]) {
									?>
		<tr>
			<td><?php echo help_create_button("Contact E-Mail Address", "communities-community_email"); ?></td>
			<td><span class="form-nrequired">Contact E-Mail</span></td>
			<td><?php echo (($PROCESSED["community_email"]) ? "<a href=\"mailto:".html_encode($PROCESSED["community_email"])."\">".html_encode($PROCESSED["community_email"])."</a>" : ""); ?></td>
		</tr>
								<?php
								}

								if($PROCESSED["community_website"] != $community_details["community_website"]) {
									?>
		<tr>
			<td><?php echo help_create_button("External Website", "communities-community_website"); ?></td>
			<td><span class="form-nrequired">External Website</span></td>
			<td><?php echo (($PROCESSED["community_website"]) ? "<a href=\"".html_encode($PROCESSED["community_website"])."\" target=\"_blank\">".html_encode($PROCESSED["community_website"])."</a>" : ""); ?></td>
		</tr>
								<?php
								}
*/
								if((is_array($community_modules_activate)) && (count($community_modules_activate))) {
									?>
		<tr>
			<td></td>
			<td style="vertical-align: top"><span class="form-nrequired">Activated Modules</span></td>
			<td style="vertical-align: top">
							The following modules will be activated once the changes are confirmed.
				<ul style="list-style-image: url('<?php echo ENTRADA_URL; ?>/images/list-success.gif'); color: #333333; font-weight: bold">
												<?php
												foreach ($community_modules_activate as $module_id) {
													echo "<li>".communities_module_title($module_id)."</li>\n";
												}
												?>
				</ul>
			</td>
		</tr>
								<?php
								}

								if ((is_array($community_modules_deactivate)) && (count($community_modules_deactivate))) {
									?>
		<tr>
			<td></td>
			<td style="vertical-align: top"><span class="form-nrequired">Deactivated Modules</span></td>
			<td style="vertical-align: top">
							The following modules will be deactivated once the changes are confirmed.
				<ul style="list-style-image: url('<?php echo ENTRADA_URL; ?>/images/list-error.gif'); color: #333333; font-weight: bold">
												<?php
												foreach($community_modules_deactivate as $module_id) {
													echo "<li>".communities_module_title($module_id)."</li>\n";
												}
												?>
				</ul>
			</td>
		</tr>
								<?php
								}

								if ($PROCESSED["sub_communities"] != $community_details["sub_communities"]) {
									?>
		<tr>
			<td><?php echo help_create_button("Sub-Communities", "communities-sub_communities"); ?></td>
			<td><span class="form-nrequired">Sub-Communities</span></td>
			<td><?php echo (($PROCESSED["sub_communities"] == 1) ? " On" : " Off"); ?></td>
		</tr>
									<?php
									if (!(int) $PROCESSED["sub_communities"]) {
										$query	= "SELECT COUNT(*) AS `total_sub_communities` FROM `communities` WHERE `community_parent` = ".$db->qstr($COMMUNITY_ID)." AND `community_active` = '1'";
										$result	= $db->GetRow($query);
										if(($result) && ($total_sub_communities = (int) $result["total_sub_communities"])) {
											?>
		<tr>
			<td colspan="2">
			<td>
				<div class="display-notice" style="line-height: 175%">
					<strong>Please note</strong> that <?php echo (($total_sub_communities != 1) ? "there are ".$total_sub_communities." sub-communities / groups that exist" : "there is 1 sub-community / group that exists"); ?> under this community.<br /><br />If you proceed with turning off sub-community support, then <?php echo (($total_sub_communities != 1) ? "these communities" : "this community"); ?> will be deactivated and removed from the system completely.
				</div>
			</td>
		</tr>
										<?php
										}
									}
								}

								if ($MAILING_LISTS["active"] && isset($PROCESSED["community_list_mode"]) && $PROCESSED["community_list_mode"] != $mailing_list->type) {
									?>
		<tr>
			<td style="vertical-align: top"><?php echo help_create_button("Mailing List", "communities-community_mailing_list"); ?></td>
			<td style="vertical-align: top"><span class="form-nrequired">Mailing List</span></td>
			<td style="vertical-align: top"><?php
											if (($PROCESSED["community_list_mode"] == "announcements" || $PROCESSED["community_list_mode"] == "discussion") && ($mailing_list->type == "inactive")) {
												echo "<img src=\"" . ENTRADA_URL ."/images/list-success.gif\" />&nbsp Mailing List - will be activated.<br>";
												echo "<div style=\"margin-left: 20px;\"><img src=\"" . ENTRADA_URL ."/images/list-success.gif\" />&nbsp Members will be added within the next 30 minutes (Status can be viewed in the Manage Users section of the community).<br></div>";
											} elseif ($PROCESSED["community_list_mode"] == "inactive") {
												echo "<img src=\"" . ENTRADA_URL ."/images/list-success.gif\" />&nbsp Mailing List - will be deactivated.<br>";
											} else {
												echo "<img src=\"" . ENTRADA_URL ."/images/list-success.gif\" />&nbsp Mailing List - will be changed to '".$PROCESSED["community_list_mode"]."' mode.<br>";
											}
											?>
			</td>
		</tr>
								<?php
								}
								if ($PROCESSED["community_protected"] != $community_details["community_protected"]) {
									?>
		<tr>
			<td><?php echo help_create_button("Access Permissions", "communities-community_protected"); ?></td>
			<td><span class="form-nrequired">Access Permissions</span></td>
			<td><?php echo (($PROCESSED["community_protected"] == 1) ? "Protected" : "Public"); ?> Community</td>
		</tr>
								<?php
								}

								if (($PROCESSED["community_registration"] != $community_details["community_registration"]) || ($PROCESSED["community_members"] != $community_details["community_members"])) {
									?>
		<tr>
			<td style="vertical-align: top"><?php echo help_create_button("Registration Options", "communities-community_registration"); ?></td>
			<td style="vertical-align: top"><span class="form-nrequired">Registration Options</span></td>
			<td style="vertical-align: top">
											<?php
											switch($PROCESSED["community_registration"]) {
												case 0 :
													echo "Open Community";
													break;
												case 1 :
													echo "Open Registration";
													break;
												case 2 :
													echo "Group Registration";
													if($PROCESSED["community_members"] != $community_details["community_members"]) {
														echo "<ol>\n";
														foreach($community_groups as $community_group) {
															echo "<li>".html_encode($GROUP_TARGETS[$community_group])."</li>\n";
														}
														echo "</ol>\n";
													}
													break;
												case 3 :
													echo "Community Registration";
													if($PROCESSED["community_members"] != $community_details["community_members"]) {
														echo "<ol>\n";
														foreach($community_communities as $community_id) {
															echo "<li>".communities_title((int) $community_id)."</li>\n";
														}
														echo "</ol>\n";
													}

													break;
												case 4 :
													echo "Private Community";
													break;
												default :
													echo "Unknown Registration Pption";

													application_log("error", "An unknown community_registration option was encountered");
													break;
											}
											?>
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
				case 1 :
				default :
				$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/livepipe/progressbar.js?release=".APPLICATION_VERSION."\"></script>";
				$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/windows/window.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
				$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/config/xc2_default.js\"></script>\n";
				$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/script/xc2_inpage.js\"></script>\n";
				$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/script/xc2_timestamp.js\"></script>\n";
				$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
				/**
				 * Information used for the community statistics tab.
				 */
					$community_stats							= array();
					$community_stats["members_total"]			= 0;
					$community_stats["members_last_31_days"]	= 0;
					$community_stats["members_admins"]			= 0;
					$community_stats["history_last_31_days"]	= 0;
					$community_stats["total_sub_communities"]	= 0;

					$query	= "SELECT COUNT(*) AS `members_total` FROM `community_members` WHERE `community_id` = ".$db->qstr($community_details["community_id"])." AND `member_active` = '1'";
					$result	= $db->GetRow($query);
					if ($result) {
						$community_stats["members_total"] = $result["members_total"];
					}

					$query	= "SELECT COUNT(*) AS `members_last_31_days` FROM `community_members` WHERE `community_id` = ".$db->qstr($community_details["community_id"])." AND `member_active` = '1' AND `member_joined` >= ".$db->qstr(strtotime("-31 days"));
					$result	= $db->GetRow($query);
					if ($result) {
						$community_stats["members_last_31_days"] = $result["members_last_31_days"];
					}

					$query	= "SELECT COUNT(*) AS `members_admins` FROM `community_members` WHERE `community_id` = ".$db->qstr($community_details["community_id"])." AND `member_active` = '1' AND `member_acl` = '1'";
					$result	= $db->GetRow($query);
					if($result) {
						$community_stats["members_admins"] = $result["members_admins"];
					}

					$query	= "SELECT COUNT(*) AS `history_last_31_days` FROM `community_history` WHERE `community_id` = ".$db->qstr($community_details["community_id"])." AND `history_timestamp` >= ".$db->qstr(strtotime("-31 days"));
					$result	= $db->GetRow($query);
					if ($result) {
						$community_stats["history_last_31_days"] = $result["history_last_31_days"];
					}

					$query	= "SELECT COUNT(*) AS `total_sub_communities` FROM `communities` WHERE `community_parent` = ".$db->qstr($community_details["community_id"])." AND `community_active` = '1'";
					$result	= $db->GetRow($query);
					if ($result) {
						$community_stats["total_sub_communities"] = $result["total_sub_communities"];
					}

					/**
					 * Onload information for setting the registration options properly.
					 */
					if ((!isset($PROCESSED["community_registration"])) || (!(int) $PROCESSED["community_registration"])) {
						$ONLOAD[] = "selectRegistrationOption('0')";
					} else {
						$ONLOAD[] = "selectRegistrationOption('".(int) $PROCESSED["community_registration"]."')";
					}

					if ($COMMUNITY_PARENT) {
						$fetched = array();
						communities_fetch_parents($COMMUNITY_PARENT, $fetched);

						if((is_array($fetched)) && (count($fetched))) {
							$community_parents	= array_reverse($fetched);
						} else {
							$community_parents	= false;
						}
						unset($fetched);
					}

					if ($NOTICE) {
						echo display_notice();
					}
					if ($ERROR) {
						echo display_error();
					}
					?>

		<h2 style="margin-top: 0px">Community Reports</h2>
			
<?php

		tracking_process_filters($ACTION);

		tracking_output_filter_controls();
		/**
		* Output the calendar controls and pagination.
		*/
		//events_output_calendar_controls();
		
		
		if (isset($_GET["pg"]) && ($page = clean_input($_GET["pg"], array("int","trim")))) {
			$PROCESSED["page"] = $page;
		} else {
			$PROCESSED["page"] = 1;
		}
		
		

		list($statistics,$dates,$num_pages) = tracking_fetch_filtered_events($COMMUNITY_ID,$_SESSION[APPLICATION_IDENTIFIER]["tracking"]["filters"],true,$PROCESSED["page"]);
		
		if ($PROCESSED["page"] > $num_pages) {
			$PROCESSED["page"] = 1;
		}
		
		echo "<div style=\"float:right;\">";
		echo "<form id=\"pageSelector\" action=\"".ENTRADA_URL."/communities/reports\" method=\"get\">";
		echo "<input type=\"hidden\" name=\"community\" value=\"".$COMMUNITY_ID."\"/>";
		if ($PROCESSED["page"] != 1) {
			echo "<a href=\"".ENTRADA_URL."/communities/reports?community=".$COMMUNITY_ID."&pg=".($PROCESSED["page"]-1)."\"><img src =\"".ENTRADA_URL."/images/record-previous-on.gif\" width=\"11\" height=\"11\" alt=\"Previous Page\" title=\"Previous Page\" style=\"vertical-align:middle;\"/></a>";
		}
		echo "<select name=\"pg\" onchange=\"$('pageSelector').submit();\">";
		for ($i = 1;$i <= $num_pages; $i++) {
			if ($i == $PROCESSED["page"]) {
				echo "<option selected=\"selected\" value=\"".$i."\">Viewing Page ".$i."</option>";
			} else {
				echo "<option value=\"".$i."\">Jump to Page ".$i."</option>";
			}
		}
		echo "</select>";
		if ($PROCESSED["page"] != $num_pages) {
			echo "<a href=\"".ENTRADA_URL."/communities/reports?community=".$COMMUNITY_ID."&pg=".($PROCESSED["page"]+1)."\"><img src =\"".ENTRADA_URL."/images/record-next-on.gif\" width=\"11\" height=\"11\" alt=\"Next Page\" title=\"Next Page\" style=\"vertical-align:middle;\"/></a>";
		}
		echo "</form></div>";
		?>
		<br/><br/>

		
		<ul class="history" style="margin-left:-40px;">
			
		<?php
			if ($statistics) {
				foreach($statistics as $key=>$statistic){
					$module = explode(':',$statistic['module']);
					$action = explode('_',$statistic['action']); 
					$user_action = ((count($action)>1)?$action[1]:$action[0]);
					$user_action = ($user_action == "delete"?$user_action."d":$user_action."ed");
					$activity_message = "<a href=\"".ENTRADA_URL."/communities/reports?section=user&community=".$COMMUNITY_ID."&user=".$statistic["user_id"]."\">".$statistic['fullname']."</a> ";
					$activity_message .= $user_action." the <a href=\"".ENTRADA_URL."/communities/reports?section=type&community=".$COMMUNITY_ID."&type=".$module[2]."\">".ucwords($module[2])."</a>";
					$activity_message .= " titled <a href=\"".ENTRADA_URL."/communities/reports?section=page&community=".$COMMUNITY_ID."&page=".$statistic["action_field"]."-".$statistic["action_value"]."\">".(isset($statistic["page"])?$statistic["page"]:"-")."</a>";
					$activity_message .= " at ".date('D M j/y h:i a', $statistic['timestamp']);


					echo "<li".(!($key % 2) ? " style=\"background-color: #F4F4F4\"" : "").">".$activity_message."</li>";
				}
			}
			?>

		</ul>		


	<!--/TRACKING EDITS-->	


					<?php
					break;
			}
		} else {
			application_log("error", "User tried to modify a community, but they aren't an administrator of this community.");

			$ERROR++;
			$ERRORSTR[] = "You do not appear to be an administrator of the community that you are trying to modify.<br /><br />If you feel you are getting this message in error, please contact the MEdTech Unit (page feedback on left) and we will investigate. The MEdTech Unit has automatically been informed that this error has taken place.";

			echo display_error();
		}
	} else {
		application_log("error", "User tried to modify a community id [".$COMMUNITY_ID."] that does not exist or is not active in the system.");

		$ERROR++;
		$ERRORSTR[] = "The community you are trying to modify either does not exist in the system or has been deactived by an administrator.<br /><br />If you feel you are receiving this message in error, please contact the MEdTech Unit (page feedback on left) and we will investigate. The MEdTech Unit has automatically been informed that this error has taken place.";

		echo display_error();
	}

}


?>
