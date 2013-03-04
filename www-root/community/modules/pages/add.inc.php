<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Gives community administrators the ability to add a page to their community.
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
	$PAGE_TYPE = "default";
	$PAGE_TYPES	= array();
	$STEP = 1;

	$query				= "	SELECT `module_shortname`, `module_title`
							FROM `communities_modules`
							WHERE `module_id` IN (
								SELECT `module_id`
								FROM `community_modules`
								WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)."
								AND `module_active` = '1'
							)
							ORDER BY `module_title` ASC";
	$module_pagetypes	= $db->GetAll($query);

	$PAGE_TYPES[]		= array("module_shortname" => "default", "module_title" => "Default Content");
	
	foreach ($module_pagetypes as $module_pagetype) {
		$PAGE_TYPES[]	= array("module_shortname" => $module_pagetype["module_shortname"], "module_title" => $module_pagetype["module_title"]);
	}
	
	$PAGE_TYPES[]	= array("module_shortname" => "url", "module_title" => "External URL");
					
	foreach ($PAGE_TYPES as $PAGE) {
		if (isset($_GET["type"])) {
			if ((is_array($PAGE)) && (array_search(trim($_GET["type"]), $PAGE))) {
				$PAGE_TYPE = trim($_GET["type"]);
				break;
			}
		}
	}

	if ((isset($_GET["step"])) && ($tmp_input = clean_input($_GET["step"], array("int")))) {
		$STEP = $tmp_input;
	}
	
	if ($COMMUNITY_ID) {
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
			if ($result) {
				load_rte((($PAGE_TYPE == "default") ? "communityadvanced" : "communitybasic"));
				
				$BREADCRUMB[]	= array("url" => "", "title" => "Add Page");
				
				if (!isset($PAGE_TYPE)) {
					$PAGE_TYPE = "default";
				}
			
				// Error Checking
				switch($STEP) {
					case 2 :
						/**
						 * Required field "page_type" / Page Type.
						 */
						foreach ($PAGE_TYPES as $PAGE) {
							if ((isset($_POST["page_type"])) && (is_array($PAGE)) && (array_search(trim($_POST["page_type"]), $PAGE))) {
								$PROCESSED["page_type"] = trim($_POST["page_type"]);
								break;
							}
						}
						
						if (!array_key_exists("page_type", $PROCESSED)){
							$ERROR++;
							$ERRORSTR[] = "The <strong>Page Type</strong> field is required and is either empty or an invalid value.";
						}

						/**
						 * Required field "parent_id" / Page Parent.
						 */
						if (isset($_POST["parent_id"])) {
							if ($parent_id = clean_input($_POST["parent_id"], array("trim", "int"))) {
								$PROCESSED["parent_id"] = $parent_id;
							} else {
								$PROCESSED["parent_id"] = 0;
							}
						} else {
							$ERROR++;
							$ERRORSTR[] = "The <strong>Page Parent</strong> field is required.";
						}

						/**
						 * Required field "menu_title" / Menu Title.
						 */
						if ((isset($_POST["menu_title"])) && ($menu_title = clean_input($_POST["menu_title"], array("trim", "notags")))) {
							$PROCESSED["menu_title"] = $menu_title;
							
							if ($page_url = clean_input($PROCESSED["menu_title"], array("lower", "underscores", "page_url"))) {
								if ($PROCESSED["parent_id"] != 0) {
									$query = "SELECT `page_url` FROM `community_pages` WHERE `cpage_id` = ".$db->qstr($PROCESSED["parent_id"]);
									if ($parent_url = $db->GetOne($query)){
										$page_url = $parent_url . "/" . $page_url;
									}
								}
								
								if (in_array($page_url, $COMMUNITY_RESERVED_PAGES)) {
									$ERROR++;
									$ERRORSTR[] = "A similar <strong>Menu Title</strong> already exists in this community; menu titles must be unique.";
								} else {
									$query	= "SELECT * FROM `community_pages` WHERE `page_url` = ".$db->qstr($page_url)." AND `community_id` = ".$db->qstr($COMMUNITY_ID);
									$result	= $db->GetRow($query);
									if ($result) {
										$ERROR++;
										$ERRORSTR[] = "A similar <strong>Menu Title</strong> already exists in this community; menu titles must be unique.";
									} else {
										$PROCESSED["page_url"] = $page_url;
									}
								}
							} else {
								$ERROR++;
								$ERRORSTR[] = "The <strong>Menu Title</strong> is not valid, must contain at least one alphanumeric character.";
							}
						} else {
							$ERROR++;
							$ERRORSTR[] = "The <strong>Menu Title</strong> field is required.";
						}
						
						/**
						 * Non-required fields view page access for members, non-members and public
						 */
						$PROCESSED["allow_admin_view"] = 1;
						
						if ((isset($_POST["allow_member_view"])) && ((int) $_POST["allow_member_view"])) {
							$PROCESSED["allow_member_view"] = 1;
						} else {
							$PROCESSED["allow_member_view"] = 0;
						}
						if ((isset($_POST["allow_troll_view"])) && ((int) $_POST["allow_troll_view"])) {
							$PROCESSED["allow_troll_view"] = 1;
						} else {
							$PROCESSED["allow_troll_view"] = 0;
						}
						if ((isset($_POST["allow_public_view"])) && ((int) $_POST["allow_public_view"])) {
							$PROCESSED["allow_public_view"] = 1;
						} else {
							$PROCESSED["allow_public_view"] = 0;
						}

						/**
						 * Non-required field "page_title" / Page Title.
						 */
						if ((isset($_POST["page_title"])) && ($page_title = clean_input($_POST["page_title"], array("trim", "notags")))) {
							$PROCESSED["page_title"] = $page_title;
						} else {
							$PROCESSED["page_title"] = "";
						}

						/**
						 * If the page type is an external URL the data will come in from a different field than if it is
						 * another type of page that actually holds content.
						 */
						if ($PAGE_TYPE == "url") {
							/**
							 * Required "page_url" / Page URL.
							 */
							if ((isset($_POST["page_content"])) && ($page_content = clean_input($_POST["page_content"], array("trim", "notags")))) {
								if (preg_match("/[\w]{3,5}[\:]{1}[\/]{2}/", $page_content) == 1) {
									$PROCESSED["page_content"] = $page_content;
								} else {
									$PROCESSED["page_content"] = "http://" . $page_content;
								}
							} else {
								$ERROR++;
								$ERRORSTR[] = "The <strong>External URL</strong> field is required, please enter a valid website address.";
							}
						} else {
							/**
							 * Non-Required "page_content" / Page Contents.
							 */
							if (isset($_POST["page_content"])) {
								$PROCESSED["page_content"] = clean_input($_POST["page_content"], array("trim", "allowedtags"));
							} else {
								$PROCESSED["page_content"] = "";

								$NOTICE++;
								$NOTICESTR[] = "The <strong>Page Content</strong> field is empty, which means that nothing will show up on this page.";
							}
						}
						
						if ($_POST["page_visibile"] == '0') {
							$PROCESSED["page_visible"] = 0;
						} else {
							$PROCESSED["page_visible"] = 1;
						}
						
						if (!$ERROR) {
							/**
							 * Colculation of page_order to place at the end of the list under whichever parent it has.
							 */
							$query	= "	SELECT (MAX(`page_order`) + 1) AS `new_order` 
										FROM `community_pages` 
										WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)."
										AND `parent_id` = ".$db->qstr($PROCESSED["parent_id"])."
										AND `page_url` != ''";
							$result	= $db->GetRow($query);
							if ($result) {
								$PROCESSED["page_order"] = (int) $result["new_order"];
							} else {
								$PROCESSED["page_order"] = 0;
							}
							if ($PAGE_TYPE ==  "announcements" || $PAGE_TYPE == "events") {
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
							 * page_type
							 * page_order
							 * page_url
							 * menu_title
							 * page_title
							 * meta_keywords
							 * meta_description
							 * parent_id
							 * page_content
							 * page_tags
							 * visible
							 * updated
							 */
							$PROCESSED["updated_by"]	= $ENTRADA_USER->getID();
							$PROCESSED["updated_date"]	= time();
							$PROCESSED["community_id"]	= $COMMUNITY_ID;
							
							if (($db->AutoExecute("community_pages", $PROCESSED, "INSERT")) && ($PAGE_ID = $db->Insert_Id())) {
								communities_log_history($COMMUNITY_ID, $PAGE_ID, 0, "community_history_add_page", 1);
								if ($PAGE_TYPE == "announcements" || $PAGE_TYPE == "events") {
									if ($db->Execute("INSERT INTO `community_page_options` SET `community_id` = ".$db->qstr($COMMUNITY_ID).", `cpage_id` = ".$db->qstr($PAGE_ID).", `option_title` = 'moderate_posts', `option_value` = ".$db->qstr($page_options["moderate_posts"]["option_value"]))) {
										if ($db->Execute("INSERT INTO `community_page_options` SET `community_id` = ".$db->qstr($COMMUNITY_ID).", `cpage_id` = ".$db->qstr($PAGE_ID).", `option_title` = 'allow_member_posts', `option_value` = ".$db->qstr($page_options["allow_member_posts"]["option_value"]))) {
											if ($db->Execute("INSERT INTO `community_page_options` SET `community_id` = ".$db->qstr($COMMUNITY_ID).", `cpage_id` = ".$db->qstr($PAGE_ID).", `option_title` = 'allow_troll_posts', `option_value` = ".$db->qstr($page_options["allow_troll_posts"]["option_value"]))) {
												if (!$ERROR) {
													$url = ENTRADA_URL."/community".$community_details["community_url"].":pages";
				
													$SUCCESS++;
													$SUCCESSSTR[]	= "You have successfully added the <strong>".html_encode($PROCESSED["menu_title"])."</strong> page to your community.<br /><br />You will now be redirected to the page management index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
					
													$HEAD[]			= "<script type=\"text/javascript\"> setTimeout('window.location=\\'".$url."\\'', 5000); </script>";
					
													application_log("success", "Page [".$PAGE_ID."] was just created in community_id [".$COMMUNITY_ID."].");
												}
											} else {
												$ERROR++;
												application_log("error", "There was an error inserting this page option. Database said: ".$db->ErrorMsg());
											}
										} else {
											$ERROR++;
											application_log("error", "There was an error inserting this page option. Database said: ".$db->ErrorMsg());
										}
									} else {
										$ERROR++;
										application_log("error", "There was an error inserting this page option. Database said: ".$db->ErrorMsg());
									}
								} elseif ($PAGE_TYPE == "url") { 
									if ($db->Execute("INSERT INTO `community_page_options` SET `community_id` = ".$db->qstr($COMMUNITY_ID).", `cpage_id` = ".$db->qstr($PAGE_ID).", `option_title` = 'new_window', `option_value` = ".$db->qstr($page_options["new_window"]["option_value"]))) {
										if (!$ERROR) {
											$url = ENTRADA_URL."/community".$community_details["community_url"].":pages";
		
											$SUCCESS++;
											$SUCCESSSTR[]	= "You have successfully added the <strong>".html_encode($PROCESSED["menu_title"])."</strong> page to your community.<br /><br />You will now be redirected to the page management index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
			
											$HEAD[]			= "<script type=\"text/javascript\"> setTimeout('window.location=\\'".$url."\\'', 5000); </script>";
			
											application_log("success", "Page [".$PAGE_ID."] was just created in community_id [".$COMMUNITY_ID."].");
										}
									} else {
										$ERROR++;
										application_log("error", "There was an error inserting this page option. Database said: ".$db->ErrorMsg());
									}
								} else {
									$url = ENTRADA_URL."/community".$community_details["community_url"].":pages";

									$SUCCESS++;
									$SUCCESSSTR[]	= "You have successfully added the <strong>".html_encode($PROCESSED["menu_title"])."</strong> page to your community.<br /><br />You will now be redirected to the page management index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
	
									$HEAD[]			= "<script type=\"text/javascript\"> setTimeout('window.location=\\'".$url."\\'', 5000); </script>";
	
									application_log("success", "Page [".$PAGE_ID."] was just created in community_id [".$COMMUNITY_ID."].");
								}
							} else {
								$ERROR++;
								application_log("error", "There was an error creating a new community page in community_id [".$COMMUNITY_ID."]. Database said: ".$db->ErrorMsg());
							}
							
							if ($ERROR) {
								$ERRORSTR[] = "There was a problem creating this new page in your community. Please contact the application administrator and inform them of this error.";
							}
						}
	
						if ($ERROR) {
							$STEP = 1;
						}
					break;
					case 1 :
					default :
						$PROCESSED = $result;
	
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
					case 1 :
					default :
						if ($NOTICE) {
							echo display_notice();
						}
						
						if ($ERROR) {
							echo display_error();
						}
						?>
						<form action="<?php echo ENTRADA_URL."/community".$community_details["community_url"].":pages"."?".replace_query(array("action" => "add", "step" => 2)); ?>" method="post" enctype="multipart/form-data">
						<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Adding Page">
						<colgroup>
							<col style="width: 30%" />
							<col style="width: 70%" />
						</colgroup>
						<thead>
							<tr>
								<td colspan="2"><h1>Add Community Page</h1></td>
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
									if (is_array($PAGE_TYPES)) {
										echo "<select id=\"page_type\" name=\"page_type\" style=\"width: 204px\" onchange=\"window.location = '".COMMUNITY_URL.$COMMUNITY_URL.":pages?section=add&page=".$PAGE_ID."&type='+this.options[this.selectedIndex].value\">\n";
										foreach ($PAGE_TYPES as $page_type_info) {
											echo "<option value=\"".html_encode($page_type_info["module_shortname"])."\"".((isset($PAGE_TYPE) && $PAGE_TYPE == $page_type_info["module_shortname"]) || ((isset($PROCESSED["page_type"])) && !isset($PAGE_TYPE) && ($PROCESSED["page_type"] == $page_type_info["module_shortname"])) ? " selected=\"selected\"" : "").">".html_encode($page_type_info["module_title"])."</option>\n";
										}
										echo "</select>";
										if (isset($PAGE_TYPES[$PROCESSED["page_type"]]["description"])) {
											echo "<div class=\"content-small\" style=\"margin-top: 5px\">\n";
											echo "<strong>Page Type Description:</strong><br />".html_encode($PAGE_TYPES[$PROCESSED["page_type"]]["description"]);
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
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<tr>
								<td><label for="parent_id" class="form-required">Page Parent:</label></td>
								<td>
									<select id="parent_id" name="parent_id" style="width: 304px">
									<?php
									echo "<option value=\"0\" selected=\"selected\">-- No Parent Page --</option>\n";
									
									$current_selected	= array(((isset($PROCESSED["parent_id"])) ? $PROCESSED["parent_id"] : 0));
									$exclude			= 0;
									
									echo communities_pages_inselect(0, $current_selected, 0, $exclude, $COMMUNITY_ID);
									?>
									</select>
								</td>
							</tr>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<tr>
								<td><label for="menu_title" class="form-required">Menu Title:</label></td>
								<td><input type="text" id="menu_title" name="menu_title" value="<?php echo ((isset($PROCESSED["menu_title"])) ? html_encode($PROCESSED["menu_title"]) : ""); ?>" maxlength="32" style="width: 300px" onblur="fieldCopy('menu_title', 'page_title', 1)" /></td>
							</tr>
							<tr>
								<td><label for="page_title" class="form-nrequired">Page Title:</label></td>
								<td><input type="text" id="page_title" name="page_title" value="<?php echo ((isset($PROCESSED["page_title"])) ? html_encode($PROCESSED["page_title"]) : ""); ?>" maxlength="100" style="width: 300px" /></td>
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
										<input type="textbox" id="page_content" name="page_content" style="width: 99%;" value="<?php echo ((isset($PROCESSED["page_content"])) ? html_encode($PROCESSED["page_content"]) : ""); ?>" />
									</td>
								</tr>
								<?php
							} else {
								?>
								<tr>
									<td colspan="2"><label for="page_content" class="form-nrequired"><?php if ($PAGE_TYPE != "default"){ echo "Top of "; } ?>Page Content:</label></td>
								</tr>
								<tr>
									<td colspan="2">
										<textarea id="page_content" name="page_content" style="width: 98%; height: <?php echo (($PAGE_TYPE == "default") ? "400" : "200"); ?>px" rows="20" cols="70"><?php echo ((isset($PROCESSED["page_content"])) ? html_encode($PROCESSED["page_content"]) : ""); ?></textarea>
									</td>
								</tr>
								<?php
							}
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
										if($('allow_member_posts').checked || $('allow_troll_posts').checked) {
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
								<tbody id="moderate-posts-body"<?php (((isset($page_options["allow_troll_posts"]["option_value"])) && (((int)$page_options["allow_troll_posts"]["option_value"]) == 1)) || ((isset($page_options["allow_member_posts"]["option_value"])) && (((int)$page_options["allow_member_posts"]["option_value"]) == 1)) ? "" : " style=\"display: none;\""); ?>>
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
							
							?>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="2"><h2>Page Options</h2></td>
							</tr>
							<?php
							
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
							?>
							<tr>
								<td><label for="page_visibile" class="form-nrequired">Page Visibility:</label></td>
								<td>
									<select id="page_visibile" name="page_visibile">
										<option value="1"<?php echo (!isset($PROCESSED["page_visible"]) || ((int) $PROCESSED["page_visible"] == 1) ? " selected=\"selected\"" : ""); ?>>Show this page on menu</option>
										<option value="0"<?php echo (isset($PROCESSED["page_visible"]) && ((int) $PROCESSED["page_visible"] == 0) ? " selected=\"selected\"" : ""); ?>>Hide this page from menu</option>
									</select>
								</td>
							</tr>
						</tbody>
						</table>
						</form>
						<?php
					break;
				}
			}
		}
	}
}
?>