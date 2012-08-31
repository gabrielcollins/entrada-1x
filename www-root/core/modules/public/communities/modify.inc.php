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
 * but WITHOUT ANY WARRANTY; without even the implied warranty ofF
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

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_COMMUNITIES"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('community', 'update', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {

	if ($MAILING_LISTS["active"]) {
		require_once("Entrada/mail-list/mail-list.class.php");
	}

	$COMMUNITY_ID		= 0;

	/**
	 * Check for a community category to proceed.
	 */
	if ((isset($_GET["community"])) && ((int) trim($_GET["community"]))) {
		$COMMUNITY_ID	= (int) trim($_GET["community"]);
	} elseif ((isset($_POST["community_id"])) && ((int) trim($_POST["community_id"]))) {
		$COMMUNITY_ID	= (int) trim($_POST["community_id"]);
	}

	/**
	 * Ensure that the selected community is editable by you.
	 */
	if ($COMMUNITY_ID) {
		if ($MAILING_LISTS["active"]) {
			$mailing_list = new MailingList($COMMUNITY_ID);
			$list_mode = $mailing_list->type;

			$query = "  SELECT a.*, b.`list_type`
						FROM `communities` AS a
						JOIN `community_mailing_lists` AS b
						ON a.`community_id` = b.`community_id`
						WHERE a.`community_id` = ".$db->qstr($COMMUNITY_ID)."
						AND a.`community_active` = '1'";
		} else {
			$query = "  SELECT *
						FROM `communities`
						WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)."
						AND `community_active` = '1'";
		}
		$community_details	= $db->GetRow($query);
		if ($community_details) {
			$BREADCRUMB[]		= array("url" => ENTRADA_URL."/community".$community_details["community_url"], "title" => limit_chars($community_details["community_title"], 50));
			$BREADCRUMB[]		= array("url" => ENTRADA_URL."/communities?".replace_query(array("section" => "modify")), "title" => "Manage Community");
			$community_resource = new CommunityResource($COMMUNITY_ID);
			if ($ENTRADA_ACL->amIAllowed($community_resource, 'update')) {
				$CATEGORY_ID = $community_details["category_id"];
				echo "<h1>".html_encode($community_details["community_title"])."</h1>\n";

				// Error Checking
				switch ($STEP) {
					case 3 :
					case 2 :
						$PROCESSED["community_members"]	= "";
						$PROCESSED["sub_communities"]	= 0;

						/**
						 * Required: Community Name / community_title
						 */
						if ((isset($_POST["community_title"])) && ($community_title = clean_input($_POST["community_title"], array("notags", "trim")))) {
							$PROCESSED["community_title"] = substr($community_title, 0, 64);
						} else {
							$ERROR++;
							$ERRORSTR[] = "Please provide a title for your new community. Example: Medicine Club";
						}

						/**
						 * Not Required: Community Keywords / community_keywords
						 */
						if ((isset($_POST["community_keywords"])) && ($community_keywords = clean_input($_POST["community_keywords"], array("notags", "trim")))) {
							$PROCESSED["community_keywords"] = substr($community_keywords, 0, 255);
						} else {
							$PROCESSED["community_keywords"] = "";
						}

						/**
						 * Not Required: Community Description / community_description
						 */
						if ((isset($_POST["community_description"])) && ($community_description = clean_input($_POST["community_description"], array("notags", "trim")))) {
							$PROCESSED["community_description"] = $community_description;
						} else {
							$PROCESSED["community_description"] = "";
						}
						
						/**
						 * Not Required: Contact E-Mail / community_email
						if ((isset($_POST["community_email"])) && ($community_email = clean_input($_POST["community_email"], array("trim", "lower")))) {
							if (valid_address($community_email)) {
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
						if ((isset($_POST["community_website"])) && ($community_website = clean_input($_POST["community_website"], array("trim", "notags", "lower")))) {
							$PROCESSED["community_website"] = $community_website;
						} else {
							$PROCESSED["community_website"] = "";
						}
						*/
						
						/**
						 * Required: Access Permissions / community_protected
						 */
						if (isset($_POST["community_protected"])) {
							if ($community_protected = clean_input($_POST["community_protected"], array("trim", "int")) === 0) {
								$PROCESSED["community_protected"] = 0;
							} else {
								$PROCESSED["community_protected"] = 1;
							}
						} else {
							$ERROR++;
							$ERRORSTR[] = "You must specify the Access Permissions for this new community.";
						}
						
						if (isset($_POST["template_selection"])) {
							if ($template_selection = clean_input($_POST["template_selection"], array("trim", "int"))) {
								$query = "SELECT * FROM `community_templates` WHERE `template_id` = ". $db->qstr($template_selection);
								$results = $db->GetRow($query);
								if ($results) {
									$PROCESSED["community_template"] = $results["template_name"];
									$db->AutoExecute("communities", $PROCESSED, "UPDATE", "`community_id` = " . $db->qstr($COMMUNITY_ID));
								}
							}		
						}
						
						/**
						 * Not Required: Sub-Communities / sub_communities
						 */
						if (isset($_POST["sub_communities"])) {
							if ($community_protected = clean_input($_POST["sub_communities"], array("trim", "int")) == 1) {
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
						if (isset($_POST["community_registration"])) {
							switch (clean_input($_POST["community_registration"], array("trim", "int"))) {
								case 0 :	// Open Community
									$PROCESSED["community_registration"]	= 0;
									break;
								case 2 :	// Group Registration
									$PROCESSED["community_registration"]	= 2;


									if ((isset($_POST["community_registration_groups"])) && (is_array($_POST["community_registration_groups"])) && (count($_POST["community_registration_groups"]))) {
										$community_groups = array();

										foreach ($_POST["community_registration_groups"] as $community_group) {
											if (($community_group = clean_input($community_group, "credentials")) && (array_key_exists($community_group, $GROUP_TARGETS))) {
												$community_groups[] = $community_group;
											}
										}

										if (count($community_groups)) {
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

									if ((isset($_POST["community_registration_communities"])) && (is_array($_POST["community_registration_communities"])) && (count($_POST["community_registration_communities"]))) {

										$community_communities = array();

										foreach ($_POST["community_registration_communities"] as $community_id) {
											if ($community_id = (int) trim($community_id)) {
												$query = "  SELECT * FROM `communities` WHERE `community_id` = ".$db->qstr($community_id)." AND `community_active` = '1'";
												$result	= $db->GetRow($query);
												if ($result) {
													$community_communities[] = $community_id;
												}
											}
										}

										if (count($community_communities)) {
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
						if ((is_array($_POST["community_modules"])) && (count($_POST["community_modules"]))) {
							$community_modules_selected 	= array();
							$community_modules_current		= array();

							$community_modules_activate		= array();	// List of modules that need to be activated.
							$community_modules_deactivate	= array();	// List of modules that need to be deactivated.

							foreach ($_POST["community_modules"] as $module_id) {
								if ($module_id = (int) $module_id) {
									$query	= "SELECT * FROM `communities_modules` WHERE `module_id` = ".$db->qstr($module_id)." AND `module_active` = '1'";
									$result	= $db->GetRow($query);
									if ($result) {
										$community_modules_selected[] = $module_id;
									}
								}
							}

							if (count($community_modules_selected)) {
								$query = "  SELECT * FROM `community_modules` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `module_active` = '1'";
								$results	= $db->GetAll($query);
								if ($results) {
									foreach ($results as $result) {
										$query = "  SELECT * FROM `communities_modules` WHERE `module_id` = ".$db->qstr($result["module_id"]);
										$module = $db->GetRow($query);
										if ($module["module_shortname"] != "default" && $module["module_visible"] != 0) {
											$community_modules_current[] = (int) $result["module_id"];
										}
									}
								}

								/**
								 * Check for modules to activate / deactivate.
								 */
								if ($community_modules_selected != $community_modules_current) {
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

						if ($ERROR) {
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

						$query = "SELECT `module_id` FROM `community_modules` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `module_active` = '1'";
						$results = $db->GetAll($query);
						if ($results) {
							foreach ($results as $result) {
								$PROCESSED["community_modules"][] = (int) $result["module_id"];
							}
						}

						$community_template = $PROCESSED["community_template"];
						$query = "SELECT * FROM `community_templates` WHERE `template_name` =".$db->qstr($community_template);
						$results = $db->GetRow($query);
						if ($results) {
							$template_selection = $results["template_id"];
						}

						if (($community_details["community_registration"] == 2) && ($community_details["community_members"])) {
							$community_groups = @unserialize($community_details["community_members"]);
						}

						if (($community_details["community_registration"] == 3) && ($community_details["community_members"])) {
							$community_communities = @unserialize($community_details["community_members"]);
						}
					break;
				}

				// Display Content
				switch ($STEP) {
					case 3 :
						$community_url	= ENTRADA_URL."/community".$community_details["community_url"];

						$PROCESSED["updated_date"]	= time();
						$PROCESSED["updated_by"]	= $_SESSION["details"]["id"];

						if ($db->AutoExecute("communities", $PROCESSED, "UPDATE", "`community_id` = ".$db->qstr($COMMUNITY_ID))) {
							if ($MAILING_LISTS["active"] && array_key_exists("community_list_mode", $PROCESSED) && $PROCESSED["community_list_mode"] != $mailing_list->type) {
								$mailing_list->mode_change($PROCESSED["community_list_mode"]);
							}
							$SUCCESS++;
							$SUCCESSSTR[] = "<strong>You have successfully updated your community.</strong><br /><br />Please <a href=\"".$community_url."\">click here</a> to proceed to it or you will be automatically forwarded in 5 seconds.";

							communities_log_history($COMMUNITY_ID, 0, $_SESSION["details"]["id"], "community_history_edit_community", 1);

							if ($community_details["community_title"] != $PROCESSED["community_title"]) {
								communities_log_history($COMMUNITY_ID, 0, 0, "community_history_rename_community", 1);
							}
						}

						/**
						 * Process modules that need to be activated.
						 */
						if ((is_array($community_modules_activate)) && (count($community_modules_activate))) {
							foreach ($community_modules_activate as $module_id) {
								if (communities_module_activate($COMMUNITY_ID, $module_id)) {
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
						if ((is_array($community_modules_deactivate)) && (count($community_modules_deactivate))) {
							foreach ($community_modules_deactivate as $module_id) {
								if (!communities_module_deactivate($COMMUNITY_ID, $module_id)) {
									application_log("error", "Failed to activate module id ".$module_id." during a community update.");
								}
							}
						}

						application_log("success", "Community ID ".$community_id." was successfully updated.");

						if ($NOTICE) {
							echo display_notice();
						}
						if ($SUCCESS) {
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
								foreach ($community_modules_selected as $key => $value) {
									echo "<input type=\"hidden\" name=\"community_modules[]\" value=\"".html_encode($value)."\" />\n";
								}
							}

							if (is_array($community_groups)) {
								foreach ($community_groups as $key => $value) {
									echo "<input type=\"hidden\" name=\"community_registration_groups[]\" value=\"".html_encode($value)."\" />\n";
								}
							}

							if (is_array($community_communities)) {
								foreach ($community_communities as $key => $value) {
									echo "<input type=\"hidden\" name=\"community_registration_communities[]\" value=\"".html_encode($value)."\" />\n";
								}
							}

							if (is_array($PROCESSED)) {
								foreach ($PROCESSED as $key => $value) {
									if (is_array($value)) {
										foreach ($value as $skey => $svalue) {
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
								if ($PROCESSED["community_title"] != $community_details["community_title"]) {
									?>
									<tr>
										<td><?php echo help_create_button("Community Name", "communities-community_title"); ?></td>
										<td><span class="form-required">Community Name</span></td>
										<td><?php echo html_encode($PROCESSED["community_title"]); ?></td>
									</tr>
								<?php
								}
								if ($PROCESSED["community_keywords"] != $community_details["community_keywords"]) {
									?>
									<tr>
										<td><?php echo help_create_button("Community Keywords", "communities-community_keywords"); ?></td>
										<td><span class="form-nrequired">Community Keywords</span></td>
										<td><?php echo html_encode($PROCESSED["community_keywords"]); ?></td>
									</tr>
								<?php
								}
								if ($PROCESSED["community_description"] != $community_details["community_description"]) {
									?>
									<tr>
										<td style="vertical-align: top"><?php echo help_create_button("Community Description", "communities-community_description"); ?></td>
										<td style="vertical-align: top"><span class="form-nrequired">Community Description</span></td>
										<td><?php echo nl2br(html_encode($PROCESSED["community_description"])); ?></td>
									</tr>
								<?php
								}
								/*
								if ($PROCESSED["community_email"] != $community_details["community_email"]) {
								?>
									<tr>
										<td><?php echo help_create_button("Contact E-Mail Address", "communities-community_email"); ?></td>
										<td><span class="form-nrequired">Contact E-Mail</span></td>
										<td><?php echo (($PROCESSED["community_email"]) ? "<a href=\"mailto:".html_encode($PROCESSED["community_email"])."\">".html_encode($PROCESSED["community_email"])."</a>" : ""); ?></td>
									</tr>
								<?php
								}
								if ($PROCESSED["community_website"] != $community_details["community_website"]) {
								?>
									<tr>
										<td><?php echo help_create_button("External Website", "communities-community_website"); ?></td>
										<td><span class="form-nrequired">External Website</span></td>
										<td><?php echo (($PROCESSED["community_website"]) ? "<a href=\"".html_encode($PROCESSED["community_website"])."\" target=\"_blank\">".html_encode($PROCESSED["community_website"])."</a>" : ""); ?></td>
									</tr>
								<?php
								}
								*/
								if ((is_array($community_modules_activate)) && (count($community_modules_activate))) {
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
											foreach ($community_modules_deactivate as $module_id) {
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
										if (($result) && ($total_sub_communities = (int) $result["total_sub_communities"])) {
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
										<td style="vertical-align: top">
										<?php
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

								if ($PROCESSED["community_template"] != $community_details["community_template"]) {
										?>
									<tr>
										<td><?php echo help_create_button("Community Template", "community_template"); ?></td>
										<td><span class="form-nrequired">Community Template</span></td>
										<td><?php echo ucfirst($PROCESSED["community_template"]); ?> Template</td>
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
										switch ($PROCESSED["community_registration"]) {
											case 0 :
												echo "Open Community";
												break;
											case 1 :
												echo "Open Registration";
												break;
											case 2 :
												echo "Group Registration";
												if ($PROCESSED["community_members"] != $community_details["community_members"]) {
													echo "<ol>\n";
													foreach ($community_groups as $community_group) {
														echo "<li>".html_encode($GROUP_TARGETS[$community_group])."</li>\n";
													}
													echo "</ol>\n";
												}
												break;
											case 3 :
												echo "Community Registration";
												if ($PROCESSED["community_members"] != $community_details["community_members"]) {
													echo "<ol>\n";
													foreach ($community_communities as $community_id) {
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

						/**
						 * Information used for the community statistics tab.
						 */
						$community_stats							= array();
						$community_stats["members_total"]			= 0;
						$community_stats["members_last_31_days"]	= 0;
						$community_stats["members_admins"]			= 0;
						$community_stats["history_last_31_days"]	= 0;
						$community_stats["total_sub_communities"]	= 0;

						$query = "SELECT COUNT(*) AS `members_total` FROM `community_members` WHERE `community_id` = ".$db->qstr($community_details["community_id"])." AND `member_active` = '1'";
						$result	= $db->GetRow($query);
						if ($result) {
							$community_stats["members_total"] = $result["members_total"];
						}

						$query = "SELECT COUNT(*) AS `members_last_31_days` FROM `community_members` WHERE `community_id` = ".$db->qstr($community_details["community_id"])." AND `member_active` = '1' AND `member_joined` >= ".$db->qstr(strtotime("-31 days"));
						$result	= $db->GetRow($query);
						if ($result) {
							$community_stats["members_last_31_days"] = $result["members_last_31_days"];
						}

						$query = "SELECT COUNT(*) AS `members_admins` FROM `community_members` WHERE `community_id` = ".$db->qstr($community_details["community_id"])." AND `member_active` = '1' AND `member_acl` = '1'";
						$result = $db->GetRow($query);
						if ($result) {
							$community_stats["members_admins"] = $result["members_admins"];
						}

						$query = "SELECT COUNT(*) AS `history_last_31_days` FROM `community_history` WHERE `community_id` = ".$db->qstr($community_details["community_id"])." AND `history_timestamp` >= ".$db->qstr(strtotime("-31 days"));
						$result = $db->GetRow($query);
						if ($result) {
							$community_stats["history_last_31_days"] = $result["history_last_31_days"];
						}

						$query = "SELECT COUNT(*) AS `total_sub_communities` FROM `communities` WHERE `community_parent` = ".$db->qstr($community_details["community_id"])." AND `community_active` = '1'";
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

							if ((is_array($fetched)) && (count($fetched))) {
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
						<form action="<?php echo ENTRADA_URL."/".$MODULE."?".replace_query(array("action" => "modify", "step" => 2)); ?>" method="post">
							<div class="tab-pane" id="community-modify-tabs">
								<div class="tab-page">
									<h2 class="tab">Statistics</h2>
									<h2 style="margin-top: 0px">Community Statistics</h2>
									<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Basic Community Statistics">
										<colgroup>
											<col style="width: 3%" />
											<col style="width: 30%" />
											<col style="width: 67%" />
										</colgroup>
										<?php
										if ((int) $community_details["storage_max"]) {
											$percent = ceil((($community_details["storage_usage"] / $community_details["storage_max"]) * 100));

											/**
											 * Interface display options.
											 */
											if ($percent >= 100) {
												$usage	= " over";
											} elseif ($percent >= 85) {
												$usage	= " warning";
											} else {
												$usage	= "";
											}
											?>
											<tr>
												<td><?php echo help_create_button("Community Quota Usage", "community-quota_usage"); ?></td>
												<td><span class="form-nrequired">Community Quota Usage</span></td>
												<td>
													<div id="community-usage" class="usage-container<?php echo $usage; ?>"></div>
													<script type="text/javascript">
														new Control.ProgressBar('community-usage').setProgress('<?php echo (($percent > 100) ? 100 : $percent); ?>');
													</script>
												</td>
											</tr>
											<tr>
												<td colspan="2">&nbsp;</td>
												<td><span class="content-small"><strong>Usage:</strong> <?php echo $percent; ?>% or <?php echo readable_size($community_details["storage_usage"])." of ".readable_size($community_details["storage_max"]); ?></span></td>
											</tr>
											<tr>
												<td colspan="3">&nbsp;</td>
											</tr>
											<?php
										}
										?>
										<tr>
											<td><?php echo help_create_button("Community Administrators", "community-community_admistrators"); ?></td>
											<td><span class="form-nrequired">Community Administrators</span></td>
											<td><?php echo $community_stats["members_admins"]." member".(($community_stats["members_admins"] != 1) ? "s" : ""); ?></td>
										</tr>
										<tr>
											<td><?php echo help_create_button("Total Members", "community-total_members"); ?></td>
											<td><span class="form-nrequired">Total Members</span></td>
											<td><?php echo $community_stats["members_total"]." member".(($community_stats["members_total"] != 1) ? "s" : ""); ?></td>
										</tr>
										<tr>
											<td><?php echo help_create_button("New Members", "community-new_members"); ?></td>
											<td><span class="form-nrequired">New Members <small>(previous 31 days)</small></span></td>
											<td><?php echo $community_stats["members_last_31_days"]." member".(($community_stats["members_last_31_days"] != 1) ? "s" : ""); ?></td>
										</tr>
										<tr>
											<td colspan="3">&nbsp;</td>
										</tr>
										<tr>
											<td><?php echo help_create_button("Updates Last Month", "community-total_updates"); ?></td>
											<td><span class="form-nrequired">Activity Points: <small>(previous 31 days)</small></span></td>
											<td><?php echo $community_stats["history_last_31_days"]." update".(($community_stats["history_last_31_days"] != 1) ? "s" : ""); ?></td>
										</tr>
										<tr>
											<td colspan="3">&nbsp;</td>
										</tr>
										<!--
										<tr>
											<td><?php echo help_create_button("Total Sub-Communities / Groups", "community-total_sub_communities"); ?></td>
											<td><span class="form-nrequired">Total Sub-Communities / Groups</span></td>
											<td><?php echo $community_stats["total_sub_communities"]." ".(($community_stats["total_sub_communities"] != 1) ? "sub-communities / groups" : "sub-community / group"); ?></td>
										</tr>
										<tr>
											<td colspan="3">&nbsp;</td>
										</tr>
										-->
									</table>
								</div>
								<div class="tab-page">
									<script type="text/javascript">
										jQuery(function() {
											jQuery( ".large-view-1" ).click(function() {
												jQuery(".default-large").dialog({ 
													 width: 792 , 
													 height: 720,
													 position: 'center',
													 draggable: false,
													 resizable: false,
													 modal : true, 
													 show: 'fade',
													 hide: 'fade',
													 title: 'Default Template',
													 buttons: {
													'Select': function() {
													   jQuery('#template_option_1').attr('checked', 'checked');
													   jQuery(this).dialog('close');
													},
													'Close': function() {
													   jQuery(this).dialog('close');
													}
												  }
												});
											});
											jQuery( ".large-view-2" ).click(function() {
												jQuery(".committee-large").dialog({
													 width: 792 , 
													 height: 720,
													 position: 'center',
													 draggable: false,
													 resizable: false,
													 modal : true,
													 show: 'fade',
													 hide: 'fade',
													 title: 'Committee Template',
													 buttons: {
													'Select': function() {
													   jQuery('#template_option_2').attr('checked', 'checked');
													   jQuery(this).dialog('close');
													},
													'Close': function() {
													   jQuery(this).dialog('close');
													}
												  }
												});
											});
											jQuery( ".large-view-3" ).click(function() {
												jQuery(".vp-large").dialog({
													 width: 792 , 
													 height: 720,
													 position: 'center',
													 draggable: false,
													 resizable: false,
													 modal : true,
													 show: 'fade',
													 hide: 'fade',
													 title: 'Virtual Patient Template',
													 buttons: {
													'Select': function() {
													   jQuery('#template_option_3').attr('checked', 'checked');
													   jQuery(this).dialog('close');
													},
													'Close': function() {
													   jQuery(this).dialog('close');
													}
												  }
												});
											});
											jQuery( ".large-view-4" ).click(function() {
												jQuery(".learningModule-large").dialog({
													 width: 792, 
													 height: 720,
													 position: 'center',
													 draggable: false,
													 resizable: false,
													 modal : true,
													 show: 'fade',
													 hide: 'fade',
													 title: 'Learning Module Template',
													 buttons: {
													'Select': function() {
													   jQuery('#template_option_4').attr('checked', 'checked');
													   jQuery(this).dialog('close');
													},
													'Close': function() {
													   jQuery(this).dialog('close');
													}
												  }
												});
											});
											jQuery( ".large-view-5" ).click(function() {
												jQuery(".course-large").dialog({
													 width: 792, 
													 height: 720,
													 position: 'center',
													 draggable: false,
													 resizable: false,
													 modal : true,
													 show: 'fade',
													 hide: 'fade',
													 title: 'Course Template',
													 buttons: {
													'Select': function() {
													   jQuery('#template_option_5').attr('checked', 'checked');
													   jQuery(this).dialog('close');
													},
													'Close': function() {
													   jQuery(this).dialog('close');
													}
												  }
												});
											});
										});
										
									</script>
									<h2 class="tab">Details</h2>
									<h2 style="margin-top: 0px">Community Details</h2>
									<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Modifying Community Details">
										<colgroup>
											<col style="width: 3%" />
											<col style="width: 20%" />
											<col style="width: 77%" />
										</colgroup>
										<tbody>
											<tr>
												<td>&nbsp;</td>
												<td><span class="form-nrequired">Community Shortname</span></td>
												<td><?php echo html_encode($community_details["community_shortname"]); ?></td>
											</tr>
											<tr>
												<td>&nbsp;</td>
												<td><span class="form-nrequired">Community URL</span></td>
												<td><a href="<?php echo html_encode(ENTRADA_URL."/community".$community_details["community_url"]); ?>" style="font-size: 10px" target="_blank"><?php echo html_encode(ENTRADA_URL."/community".$community_details["community_url"]); ?></a></td>
											</tr>
											<tr>
												<td colspan="3">&nbsp;</td>
											</tr>
											<?php
											if ((is_array($community_parents)) && ($total_parents = count($community_parents))) {
												?>
												<tr>
													<td></td>
													<td><span class="form-nrequired">Community Path</span></td>
													<td>
													<?php
													$i = 0;
													foreach ($community_parents as $result) {
														$i++;
														echo html_encode($result["community_title"])." / ";
													}
													echo "<em>".html_encode($PROCESSED["community_title"])."</em>";
													?>
													</td>
												</tr>
												<?php
												}
												?>
											<tr>
												<td><?php echo help_create_button("Community Name", ""); ?></td>
												<td><label for="community_title" class="form-required">Community Name</label></td>
												<td>
													<input type="text" id="community_title" name="community_title" value="<?php echo html_encode($PROCESSED["community_title"]); ?>" maxlength="64" style="width: 250px" />
													<span class="content-small">(<strong>Example:</strong> Medicine Club)</span>
												</td>
											</tr>
											<tr>
												<td><?php echo help_create_button("Community Keywords", ""); ?></td>
												<td><label for="community_keywords" class="form-nrequired">Community Keywords</label></td>
												<td>
													<input type="text" id="community_keywords" name="community_keywords" value="<?php echo html_encode($PROCESSED["community_keywords"]); ?>" maxlength="255" style="width: 500px" />
												</td>
											</tr>
											<tr>
												<td style="vertical-align: top"><?php echo help_create_button("Community Description", ""); ?></td>
												<td style="vertical-align: top"><label for="community_description" class="form-nrequired">Community Description</label></td>
												<td><textarea id="community_description" name="community_description" style="width: 500px; height: 75px"><?php echo html_encode($PROCESSED["community_description"]); ?></textarea></td>
											</tr>
											<tr>
												<td style="padding-top:6px; vertical-align: top"><?php echo help_create_button("Community Template", ""); ?></td>
												<td style="padding-top:6px; vertical-align: top"><label for="community_template" class="form-nrequired">Community Template</label></td>
												<td style="vertical-align: top">
												<div>
												<?php
													$admin_query = "SELECT `cmember_id`, `community_id`, `proxy_id`, `member_active`, `member_acl` FROM `community_members` WHERE `community_id` = ".$db->qstr($community_details["community_id"])." AND `member_active` = '1' AND `member_acl` = '1' ORDER BY `cmember_id`";
													$admin = $db->GetRow($admin_query);
													if ($admin) {
														$query = "SELECT `user_id`, `group` FROM `".AUTH_DATABASE."`.`user_access` WHERE `user_id`= ".$db->qstr($admin["proxy_id"]);
														$admin_group = $db->GetRow($query);
														if ($admin_group) {
															$creator_group = $admin_group["group"];
														}
													}
													$query = "SELECT * FROM `community_templates`"; 
													$results = $db->GetAll($query);
													if ($results) {
													?>
														<ul class="community-themes">
														<?php
														$default_templates = array();
														$groups = array();
														$category = array();
														$default_categories = array();
														$default_groups = array();
														
														foreach($results as $community_template) {
															$permissions_query = "SELECT * FROM `communities_template_permissions` WHERE `template`=". $db->qstr($community_template["template_name"]);
															$template_permissions = $db->GetAll($permissions_query);
															if ($template_permissions) {
																foreach ($template_permissions as $template_permission) {
																	if ($template_permission["permission_type"] == "group") {
																		$groups = explode(",",$template_permission["permission_value"]);
																	}
																	if (($template_permission["permission_type"] == null && $template_permission["permission_value"] == null)) {
																		$default_templates[] = $template_permission["template"];
																	}
																	if (($template_permission["permission_type"] == "category_id" && $template_permission["permission_value"] != null)) {
																		$category = explode(",",$template_permission["permission_value"]);
																	}
																	if (($template_permission["permission_type"] == "category_id" && $template_permission["permission_value"] == null)) {
																		$category_permissions_query = " SELECT * FROM `communities_template_permissions` 
																										WHERE `permission_type`= 'group' 
																										AND `template`=". $db->qstr($template_permission["template"]);
																		$category_permissions = $db->GetAll($category_permissions_query);
																		if($category_permissions) {
																			foreach ($category_permissions as $category_permission) {
																				$default_categories = explode(",", $category_permission["permission_value"]);
																				if (in_array($creator_group, $default_categories)) {
																					$default_categories[] = $category_permission["template"];
																				}

																			}
																		}
																	}
																	?>
																	<?php
																}
																if ((in_array($creator_group, $groups) && in_array($CATEGORY_ID, $category)) || (in_array($community_template["template_name"], $default_templates)) || (in_array($community_template["template_name"], $default_categories))) {
																?>
																<li id="<?php echo $community_template["template_name"]."-template"; ?>">
																	<div class="template-rdo">
																		<input type="radio" id="<?php echo "template_option_".$community_template["template_id"] ?>" name="template_selection" value="<?php echo $community_template["template_id"]; ?>"<?php echo ((($template_selection == 0) && ($community_template["template_id"] == 1) || ($template_selection == $community_template["template_id"])) ? " checked=\"checked\"" : ""); ?> />
																	</div>
																	<div class="large-view">
																		<a href="#" class="<?php echo "large-view-".$community_template["template_id"]; ?>"><img src="<?php echo ENTRADA_URL. "/images/icon-magnify.gif"  ?>" /></a>
																	</div>
																	<label for="<?php echo "template_option_".$community_template["template_id"]; ?>"><?php echo ucfirst($community_template["template_name"]. " Template"); ?></label>
																</li>
																<?php
																}
															} 
														}
														?>
														</ul>
														<div class="default-large" style="display:none;">
															<img src="<?php echo ENTRADA_URL."/images/template-default-large.gif" ?>" alt="Default Template Screen shot" />
														</div>
														<div class="committee-large" style="display:none;">
															<img src="<?php echo ENTRADA_URL."/images/template-meeting-large.gif" ?>" alt="Committee Template Screen shot" />
														</div> 
														<div class="vp-large" style="display:none;">
															<img src="<?php echo ENTRADA_URL."/images/template-vp-large.gif" ?>" alt="Virtual Patient Template Screen shot" />
														</div> 
														<div class="learningModule-large" style="display:none;">
															<img src="<?php echo ENTRADA_URL."/images/template-education-large.gif" ?>" alt="Learning Template Screen shot" />
														</div>
														<div class="course-large" style="display:none;">
															<img src="<?php echo ENTRADA_URL."/images/template-course-large.gif" ?>" alt="Course Template Screen shot" />
														</div> 
													<?php
													}
													?>					
													</div>
												</td>
											</tr>
											<tr>
												<td colspan="3">&nbsp;</td>
											</tr>
											<?php /*
											<tr>
												<td><?php echo help_create_button("Contact E-Mail Address", ""); ?></td>
												<td><label for="community_email" class="form-nrequired">Contact E-Mail</label></td>
												<td><input type="text" id="community_email" name="community_email" value="<?php echo html_encode($PROCESSED["community_email"]); ?>" maxlength="128" style="width: 250px" /></td>
											</tr>
											<tr>
												<td><?php echo help_create_button("External Website", ""); ?></td>
												<td><label for="community_website" class="form-nrequired">External Website</label></td>
												<td><input type="text" id="community_website" name="community_website" value="<?php echo html_encode($PROCESSED["community_website"]); ?>" maxlength="1055" style="width: 250px" /></td>
											</tr>
											*/ ?>
										</tbody>
									</table>
								</div>
								<div class="tab-page">
									<h2 class="tab">Modules</h2>
									<h2 style="margin-top: 0px">Community Modules</h2>
									<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Modifying Community Modules">
										<colgroup>
											<col style="width: 3%" />
											<col style="width: 20%" />
											<col style="width: 77%" />
										</colgroup>
										<tr>
											<td style="vertical-align: top"><?php echo help_create_button("Available Modules", ""); ?></td>
											<td style="vertical-align: top"><span class="form-required">Available Modules</span></td>
											<td style="padding-bottom: 15px">
											<?php
											$query		= "SELECT * FROM `communities_modules` WHERE `module_active` = '1' AND `module_visible` = '1' ORDER BY `module_title` ASC";
											$results	= $db->GetAll($query);
											if ($results) {
												?>
												<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Setting Available Modules">
													<colgroup>
														<col style="width: 3%" />
														<col style="width: 97%" />
													</colgroup>
													<tbody>
													<?php
													foreach ($results as $result) {
														if ($result["module_shortname"] != "default") {
														?>
														<tr>
															<td style="padding-bottom: 5px; vertical-align: top"><input type="checkbox" name="community_modules[]" id="community_modules_<?php echo $result["module_id"]; ?>" value="<?php echo $result["module_id"]; ?>" style="vertical-align: middle"<?php echo (((!isset($PROCESSED["community_modules"])) || ((isset($PROCESSED["community_modules"])) && (is_array($PROCESSED["community_modules"])) && (in_array($result["module_id"], $PROCESSED["community_modules"])))) ? " checked=\"checked\"" : ""); ?> /></td>
															<td style="padding-bottom: 5px; vertical-align: top">
																<label for="community_modules_<?php echo $result["module_id"]; ?>" class="normal-green"><?php echo html_encode($result["module_title"]); ?></label>
																<div class="content-small"><?php echo html_encode($result["module_description"]); ?></div>
															</td>
														</tr>
														<?php
														}
													}
													?>
													</tbody>
												</table>
												<?php
											} else {
												$ERROR++;
												$ERRORSTR[] = "There has been an error obtaining the required Community Modules from the database. The MEdTech Unit has been informed of the issue, please try again later.";

												echo display_error();

												application_log("error", "Unable to fetch Community Modules from database. Database said: ".$db->ErrorMsg());
											}
											?>
											</td>
										</tr>
									</table>
								</div>
								<div class="tab-page">
									<h2 class="tab">Permissions</h2>
									<h2 style="margin-top: 0px">Community Permissions</h2>
									<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Modifying Community Permissions">
										<colgroup>
											<col style="width: 3%" />
											<col style="width: 20%" />
											<col style="width: 77%" />
										</colgroup>
										<!--
										<tr>
											<td><?php echo help_create_button("Sub-Communities", "communities-sub_communities"); ?></td>
											<td><span class="form-nrequired">Sub-Communities</span></td>
											<td><input type="checkbox" id="sub_communities" name="sub_communities" value="1"<?php echo (($PROCESSED["sub_communities"] == 1) ? " checked=\"checked\"" : ""); ?> style="vertical-align: middle" /> <label for="sub_communities" class="form-nrequired" style="vertical-align: middle">Allow members to create sub-communities / groups under this community.</label></td>
										</tr>
										<tr>
											<td colspan="3">&nbsp;</td>
										</tr>
										-->
										<tr>
											<td style="vertical-align: top"><?php echo help_create_button("Access Permissions", ""); ?></td>
											<td style="vertical-align: top"><span class="form-nrequired">Access Permissions</span></td>
											<td style="padding-bottom: 15px">
												<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Setting Access Permissions">
													<colgroup>
														<col style="width: 3%" />
														<col style="width: 97%" />
													</colgroup>
													<tbody>
														<tr>
															<td style="vertical-align: top"><input type="radio" name="community_protected" id="community_protected_1" value="1" style="vertical-align: middle"<?php echo (((!isset($PROCESSED["community_protected"])) || ($PROCESSED["community_protected"] == 1)) ? " checked=\"checked\"" : ""); ?> /></td>
															<td>
																<label for="community_protected_1" class="normal-green">Protected Community</label>
																<div class="content-small">Only authenticated users can access this community after they log in.</div>
															</td>
														</tr>
														<tr>
															<td style="vertical-align: top"><input type="radio" name="community_protected" id="community_protected_0" value="0" style="vertical-align: middle"<?php echo (((isset($PROCESSED["community_protected"])) && ($PROCESSED["community_protected"] == 0)) ? " checked=\"checked\"" : ""); ?> /></td>
															<td>
																<label for="community_protected_0" class="normal-green">Public Community</label>
																<div class="content-small">Anyone in the world can have read-only access to this community without logging in.</div>
															</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
										<tr>
											<td style="vertical-align: top"><?php echo help_create_button("Registration Options", ""); ?></td>
											<td style="vertical-align: top"><span class="form-nrequired">Registration Options</span></td>
											<td style="padding-bottom: 15px">
												<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Setting Registration Options">
													<colgroup>
														<col style="width: 3%" />
														<col style="width: 97%" />
													</colgroup>
													<tbody>
														<tr>
															<td style="vertical-align: top"><input type="radio" name="community_registration" id="community_registration_0" value="0" onclick="selectRegistrationOption('0')" style="vertical-align: middle"<?php echo (((!isset($PROCESSED["community_registration"])) || ((isset($PROCESSED["community_registration"])) && ($PROCESSED["community_registration"] == 0))) ? " checked=\"checked\"" : ""); ?> /></td>
															<td>
																<label for="community_registration_0" class="normal-green">Open Community</label>
																<div class="content-small">Any authenticated user can access this community without registering in it.</div>
															</td>
														</tr>
														<tr>
															<td style="vertical-align: top"><input type="radio" name="community_registration" id="community_registration_1" value="1" onclick="selectRegistrationOption('1')" style="vertical-align: middle"<?php echo (((isset($PROCESSED["community_registration"])) && ($PROCESSED["community_registration"] == 1)) ? " checked=\"checked\"" : ""); ?> /></td>
															<td>
																<label for="community_registration_1" class="normal-green">Open Registration</label>
																<div class="content-small">Any authenticated user can and must register to be part of this community.</div>
															</td>
														</tr>
														<tr>
															<td style="vertical-align: top"><input type="radio" name="community_registration" id="community_registration_2" value="2" onclick="selectRegistrationOption('2')" style="vertical-align: middle"<?php echo (((isset($PROCESSED["community_registration"])) && ($PROCESSED["community_registration"] == 2)) ? " checked=\"checked\"" : ""); ?> /></td>
															<td>
																<label for="community_registration_2" class="normal-green">Group Registration</label>
																<div class="content-small">Only members of the selected Groups can register to be part of this community.</div>
																<div id="community_registration_show_groups" style="display: none; padding-left: 25px">
																<?php
																if ((is_array($GROUP_TARGETS)) && ($total_sresults = count($GROUP_TARGETS))) {
																	$count			= 0;
																	$column			= 0;
																	$max_columns	= 2;

																	echo "<table style=\"width: 100%\" cellspacing=\"0\" cellpadding=\"1\" border=\"0\" summary=\"Available Groups\">\n";
																	echo "<colgroup>\n";
																	echo "	<col style=\"width: 50%\" />\n";
																	echo "	<col style=\"width: 50%\" />\n";
																	echo "</colgroup>\n";
																	echo "<tbody>\n";
																	echo "	<tr>\n";
																	foreach ($GROUP_TARGETS as $group => $result) {
																		$count++;
																		$column++;

																		echo "	<td>\n";
																		echo "		<input type=\"checkbox\" id=\"community_registration_groups_".$group."\" name=\"community_registration_groups[]\" value=\"".$group."\" style=\"vertical-align: middle\"".(((isset($community_groups)) && (is_array($community_groups)) && (in_array($group, $community_groups))) ? " checked=\"checked\"" : "")." /> <label for=\"community_registration_groups_".$group."\" class=\"content-small\">".html_encode($result)."</label>\n";
																		echo "	</td>\n";
																		if (($count == $total_sresults) && ($column < $max_columns)) {
																			for ($i = 0; $i < ($max_columns - $column); $i++) {
																				echo "	<td>&nbsp;</td>\n";
																			}
																		}

																		if (($count == $total_sresults) || ($column == $max_columns)) {
																			$column = 0;
																			echo "	</tr>\n";

																			if ($count < $total_sresults) {
																				echo "	<tr>\n";
																			}
																		}
																	}
																	echo "</tbody>\n";
																	echo "</table>\n";
																}
																?>
																</div>
															</td>
														</tr>
														<tr>
															<td style="vertical-align: top"><input type="radio" name="community_registration" id="community_registration_3" value="3" onclick="selectRegistrationOption('3')" style="vertical-align: middle"<?php echo (((isset($PROCESSED["community_registration"])) && ($PROCESSED["community_registration"] == 3)) ? " checked=\"checked\"" : ""); ?> /></td>
															<td>
																<label for="community_registration_3" class="normal-green">Community Registration</label>
																<div class="content-small">Only members of the selected Communities can register to be part of this community.</div>
																<div id="community_registration_show_communities" style="display: none; padding: 5px 5px 0px 5px">
																	<select id="community_registration_communities" name="community_registration_communities[]" multiple="multiple" size="10" style="width: 85%; height: 150px">
																	<?php
																	$COMMUNITIES_FETCH_CHILDREN = ((isset($community_communities)) ? $community_communities : array());
																	echo communities_fetch_children(0, false, 0, false, "select");
																	?>
																	</select>
																</div>
															</td>
														</tr>
														<tr>
															<td style="vertical-align: top"><input type="radio" name="community_registration" id="community_registration_4" value="4" onclick="selectRegistrationOption('4')" style="vertical-align: middle"<?php echo (((isset($PROCESSED["community_registration"])) && ($PROCESSED["community_registration"] == 4)) ? " checked=\"checked\"" : ""); ?> /></td>
															<td>
																<label for="community_registration_4" class="normal-green">Private Community</label>
																<div class="content-small">People cannot register, members are invited only by community administrators.</div>
															</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
									</table>
								</div>
								<?php
								if ($MAILING_LISTS["active"]) {
									?>
									<div class="tab-page">
										<h2 class="tab">Mailing List</h2>
										<h2 style="margin-top: 0px">Community Mailing List</h2>
										<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Modifying Community Mailing List">
											<colgroup>
												<col style="width: 3%" />
												<col style="width: 20%" />
												<col style="width: 77%" />
											</colgroup>
											<tr>
												<td style="vertical-align: top"><?php echo help_create_button("Mailing List Mode", ""); ?></td>
												<td style="vertical-align: top"><span class="form-nrequired">Mailing List Mode</span></td>
												<td style="padding-bottom: 15px">
													<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Setting Mailing List Mode">
														<colgroup>
															<col style="width: 3%" />
															<col style="width: 97%" />
														</colgroup>
														<tbody>
															<tr>
																<td style="padding-bottom: 5px; vertical-align: top"><input type="radio" name="community_list_mode" id="community_list_announcement"<?php echo ($list_mode == "announcements" ? "\" checked=\"checked\"" : "\""); ?> style="vertical-align: middle" value="announcements" /></td>
																<td style="padding-bottom: 5px; vertical-align: top">
																	<label for="community_list_announcement" class="normal-green">Announcement Mode</label>
																	<div class="content-small">Allow administrators of this community to send out email announcements to all the members of the community through the mailing list at <a style="font-size: 11px;" href=<?php echo "\"mailto:" .$mailing_list->list_name . "@" . $GOOGLE_APPS["domain"]. "\">" . $mailing_list->list_name . "@" . $GOOGLE_APPS["domain"]; ?></a>.</div>
																</td>
															</tr>
															<tr>
																<td style="padding-bottom: 5px; vertical-align: top"><input type="radio" name="community_list_mode" id="community_list_discussion"<?php echo ($list_mode == "discussion" ? "\" checked=\"checked\"" : "\""); ?> style="vertical-align: middle" value="discussion" /></td>
																<td style="padding-bottom: 5px; vertical-align: top">
																	<label for="community_list_discussion" class="normal-green">Discussion Mode</label>
																	<div class="content-small">Allow all members of this community to send out email to the community through the mailing list at <a style="font-size: 11px;" href=<?php echo "\"mailto:" .$mailing_list->list_name . "@" . $GOOGLE_APPS["domain"]. "\">" . $mailing_list->list_name . "@" . $GOOGLE_APPS["domain"]; ?></a>.</div></td>
															</tr>
															<tr>
																<td style="padding-bottom: 5px; vertical-align: top"><input type="radio" name="community_list_mode" id="community_list_deactivate"<?php echo ($list_mode == "inactive" ? "\" checked=\"checked\"" : "\""); ?> style="vertical-align: middle" value="inactive" /></td>
																<td>
																	<label for="community_list_deactivates" class="normal-green">Deactivate List</label>
																	<div class="content-small">Disable the mailing list for this community so members cannot be contacted through the list.</div>
																</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
										</table>
									</div>
									<?php
								}
									?>
								<div class="tab-page">
									<h2 class="tab">Deactivate</h2>
									<h2 style="margin-top: 0px">Deactivate Community</h2>

									<script type="text/javascript">
										function validateDeactivate() {
											if ((document.getElementById('confirmed-deactivation')) && (document.getElementById('confirmed-deactivation').checked)) {
												if (document.getElementById('deactivate_community_form')) {
													document.getElementById('deactivate_community_form').submit();
												}
											} else {
												alert('Before being able to deactivate this community, you must check the box that asks if you understand what will happen once you deactivate the community.');
											}
											return;
										}
									</script>

									If you no longer wish to maintain this community or it is no longer being used, you can deactivate the community using the button below. If you have any questions before deactivating, please use the Page Feedback icon to the left of the page to contact the MEdTech Unit.
									<div class="display-notice" style="margin-top: 15px; line-height: 175%">
										<strong>Please note</strong> that once you deactivate this community all of the content (photos, calendar, etc) within the community will no longer be accessible to you or any other members of the community. Deactivating this community will also deactivate any Sub-Communities / Groups that have been created under this community.
									</div>

									<input type="checkbox" id="confirmed-deactivation" value="" style="vertical-align: middle" /> <label for="confirmed-deactivation" class="form-required">I understand that deactivating this community will render everything inside it inaccessible.</label>
									<div style="margin-top: 15px; margin-bottom: 50px; padding-left: 25px">
										<input type="button" class="button" onclick="validateDeactivate()" value="Deactivate Now" />
									</div>
								</div>
							</div>
							<script type="text/javascript">setupAllTabs(true);
							</script>
							<?php
							if (isset($_GET["deactivate"])) {
								?>
								<script type="text/javascript">tabPaneObj.setSelectedIndex(4);</script>
								<?php
							}
							?>
							<div style="margin-top: 10px">
								<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
									<tr>
										<td style="width: 25%; text-align: left">
											<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo html_encode(ENTRADA_URL."/community".$community_details["community_url"]); ?>'" />
										</td>
										<td style="width: 75%; text-align: right; vertical-align: middle">
											<input type="submit" class="button" value="Save Changes" />
										</td>
									</tr>
								</table>
							</div>
						</form>
						<form id="deactivate_community_form" action="<?php echo ENTRADA_URL."/".$MODULE; ?>?section=deactivate" method="post">
							<input type="hidden" id="deactivate_community_id" name="community_id" value="<?php echo (int) $community_details["community_id"]; ?>" />
						</form>
						<br /><br />
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
	} else {
		application_log("error", "User tried to modify a community without providing a community_id.");

		header("Location: ".ENTRADA_URL."/communities");
		exit;
	}
}