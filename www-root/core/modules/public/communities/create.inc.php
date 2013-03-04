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
} elseif (!$ENTRADA_ACL->amIAllowed('community', 'create')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {

	if ($MAILING_LISTS["active"]) {
		require_once("Entrada/mail-list/mail-list.class.php");
	}

	$BREADCRUMB[]		= array("url" => ENTRADA_URL."/communities?".replace_query(array("section" => "create")), "title" => "Creating a Community");
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/windows/window.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	$CATEGORY_ID		= 0;
	$COMMUNITY_PARENT	= 0;

	/**
	 * Check for a community category to proceed.
	 */
	if((isset($_GET["category"])) && ((int) trim($_GET["category"]))) {
		$CATEGORY_ID	= (int) trim($_GET["category"]);
	} elseif((isset($_POST["category_id"])) && ((int) trim($_POST["category_id"]))) {
		$CATEGORY_ID	= (int) trim($_POST["category_id"]);
	}

	/**
	 * Ensure the selected category is feasible or send them to the first step.
	 */
	if($CATEGORY_ID) {
		$query	= "	SELECT *
				FROM `communities_categories`
				WHERE `category_id` = ".$db->qstr($CATEGORY_ID)."
				AND `category_visible` = '1'";
		$result	= $db->GetRow($query);
		if($result) {
			$query		= "
					SELECT COUNT(*) AS `total_categories`
					FROM `communities_categories`
					WHERE `category_parent` = ".$db->qstr($CATEGORY_ID)."
					AND `category_visible` = '1'";
			$sresult	= $db->GetRow($query);
			if(($sresult) && ((int) $sresult["total_categories"])) {
				$ERROR++;
				$ERRORSTR[] = "The community category that you have chosen can not accept new communities because it has categories underneath it. Please choose a new child category to place your new community under.";
			} else {
				if($result["category_status"] == 1) {
					$NOTICE++;
					$NOTICESTR[] = "You have chosen a community category which requires administrator approval before your community will be accessible. An administrator will be notified once you have finished creating this community and they will review your request as soon as possible. Please continue creating this community.";
				}

				if($STEP < 2) {
					$STEP = 2;
				}
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "The category that you have selected no longer exists in the system. Please choose a new category.";
		}
	} else {
		$NOTICE++;
		$NOTICESTR[]	= "Please begin by selecting a community category to place your new community under.";
		$STEP			= 1;
	}

	/**
	 * Check for a selected community parent for this category.
	 */
	if((isset($_GET["parent"])) && ((int) trim($_GET["parent"]))) {
		$COMMUNITY_PARENT	= (int) trim($_GET["parent"]);
	} elseif((isset($_POST["community_parent"])) && ((int) trim($_POST["community_parent"]))) {
		$COMMUNITY_PARENT	= (int) trim($_POST["community_parent"]);
	}

	/**
	 * If there is a selected community parent, make sure they have permissions to do this.
	 */
	if($COMMUNITY_PARENT) {
		$query	= "SELECT * FROM `communities` WHERE `community_id` = ".$db->qstr($COMMUNITY_PARENT);
		$result	= $db->GetRow($query);
		if($result) {
			if(!$result["sub_communities"]) {
				$ERROR++;
				$ERRORSTR[] = "The parent community that you have chosen does not allow sub-communities to be created under it.<br /><br />If you would like to create a community here, please contact a community administrator who will need to update the community profile to allow sub-communities.";
				$COMMUNITY_PARENT = 0;
			} elseif(!$result["community_active"]) {
				$ERROR++;
				$ERRORSTR[] = "The parent community that you have chosen is not currently activated; therefore a sub-communit cannot be created at this time.<br /><br />If you would like to create a community here, please contact a community administrator who will need to re-activate the community in their community profile.";
				$COMMUNITY_PARENT = 0;
			} elseif($result["community_members"] != "") {
				if((is_array($community_members = @unserialize($result["community_members"]))) && (isset($_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]))) {
					if(!in_array($_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"], $community_members)) {
						$ERROR++;
						$ERRORSTR[] = "The parent community that you have chosen only allows certain MEdTech groups (".html_encode(implode(", ", $community_members)).") to become members, and your group is ".html_encode($_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]).".<br /><br />If you would like to create a community here, please contact a community administrator who will need to adjust the groups requirements option in their community profile.";
						$COMMUNITY_PARENT = 0;
					}
				}
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "The parent community that you have provided does not exist.";
			$COMMUNITY_PARENT = 0;
		}
	}

	if($ERROR) {
		$STEP = 1;
	}

	echo "<h1>Creating a Community</h1>\n";

	// Error Checking
	switch($STEP) {
		case 3 :
			$PROCESSED["community_parent"]	= $COMMUNITY_PARENT;
			$PROCESSED["category_id"]		= $CATEGORY_ID;
			$PROCESSED["community_active"]	= 1;
			$PROCESSED["community_members"]	= "";

			$query	= "SELECT `category_status` FROM `communities_categories` WHERE `category_id` = ".$db->qstr($PROCESSED["category_id"])." AND `category_visible` = '1'";
			$result	= $db->GetRow($query);
			if($result) {
				if($result["category_status"] == 1) {
					$PROCESSED["community_active"] = 0;
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "The category that you have selected no longer exists in the system. Please choose a new category.";
			}


			if((isset($_POST["community_title"])) && ($community_title = clean_input($_POST["community_title"], array("notags", "trim")))) {
				$PROCESSED["community_title"] = substr($community_title, 0, 64);
			} else {
				$ERROR++;
				$ERRORSTR[] = "Please provide a title for your new community. Example: Medicine Club";
			}

			if((isset($_POST["community_keywords"])) && ($community_keywords = clean_input($_POST["community_keywords"], array("notags", "trim")))) {
				$PROCESSED["community_keywords"] = substr($community_keywords, 0, 255);
			} else {
				$PROCESSED["community_keywords"] = "";
			}

			if((isset($_POST["community_description"])) && ($community_description = clean_input($_POST["community_description"], array("notags", "trim")))) {
				$PROCESSED["community_description"] = $community_description;
			} else {
				$PROCESSED["community_description"] = "";
			}

			if((isset($_POST["community_shortname"])) && ($community_shortname = clean_input($_POST["community_shortname"], array("notags", "lower", "trim")))) {
			/**
			 * Ensure that this community name is less than 32 characters in length.
			 */
				$community_shortname = substr($community_shortname, 0, 32);

				$query	= "SELECT `community_id`, `community_url`, `community_shortname`, `community_title` FROM `communities` WHERE `community_shortname` = ".$db->qstr($community_shortname)." AND `community_parent` = ".$db->qstr($COMMUNITY_PARENT)." LIMIT 1";
				$result	= $db->GetRow($query);
				if($result) {
					$ERROR++;
					$ERRORSTR[] = "The Community Shortname <em>(".html_encode($community_shortname).")</em> that you have chosen is already in use by another community in the system.<br /><br />Please choose and enter a new shortname to use for your community.";
				} else {
					if($parent_details = communities_fetch_parent($COMMUNITY_PARENT)) {
						$PROCESSED["community_url"] = $parent_details["community_url"]."/".$community_shortname;
					} else {
						$PROCESSED["community_url"] = "/".$community_shortname;
					}

					$PROCESSED["community_shortname"] = $community_shortname;
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "You must provide a shortname for your new community to use. Example: medicine_club";
			}

			/**
			 * Required: Mailing List Mode
			 */
			if (($MAILING_LISTS["active"]) && isset($_POST["community_list_mode"])) {
				if (($list_mode = clean_input($_POST["community_list_mode"], array("nows", "lower"))) && $list_mode != $mail_list->type) {
					$PROCESSED["community_list_mode"] = $list_mode;
				}
			} elseif ($MAILING_LISTS["active"]) {
				$ERROR++;
				$ERRORSTR[] = "You must specify which mode the mailing list for this community is in.";
			}
/*
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

			if((isset($_POST["community_website"])) && ($community_website = clean_input($_POST["community_website"], array("trim", "notags", "lower")))) {
				$PROCESSED["community_website"] = $community_website;
			} else {
				$PROCESSED["community_website"] = "";
			}
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
			
			if (isset($_POST["template_selection"])) {
				if ($template_selection = clean_input($_POST["template_selection"], array("trim", "int"))) {
					$template_query = "SELECT * FROM `community_templates` WHERE `template_id` = ". $db->qstr($template_selection);
					$community_template = $db->GetRow($template_query);
					if ($community_template) {
						$PROCESSED["community_template"] = $community_template["template_name"];
					}
				}
			}
			
			if(isset($_POST["community_registration"])) {
				switch(clean_input($_POST["community_registration"], array("trim", "int"))) {
					case 0 :
						$PROCESSED["community_registration"]	= 0;
						break;
					case 2 :
						$PROCESSED["community_registration"]	= 2;

						// Group Registration
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
					case 3 :
						$PROCESSED["community_registration"]	= 3;

						// Community Registration
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
					case 4 :
						$PROCESSED["community_registration"]	= 4;
						break;
					case 1 :
					default :
						$PROCESSED["community_registration"]	= 1;
						break;
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "You must specify the Registration Options for this new community.";
			}

			if((is_array($_POST["community_modules"])) && (count($_POST["community_modules"]))) {
				$community_modules = array();
				foreach($_POST["community_modules"] as $module_id) {
					if($module_id = (int) $module_id) {
						$query	= "SELECT * FROM `communities_modules` WHERE `module_active` = '1' AND `module_id` = ".$db->qstr($module_id);
						$result	= $db->GetRow($query);
						if($result) {
							$community_modules[] = $module_id;
						}
					}
				}

				if(!count($community_modules)) {
					$ERROR++;
					$ERRORSTR[] = "You must enable at least one module in your new community, otherwise there will be nothing to do once the community has been created.";
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "You must enable at least one module in your new community, otherwise there will be nothing to do once the community has been created.";
			}

			if(!$ERROR) {
				$PROCESSED["community_opened"]	= time();
				$PROCESSED["updated_date"]		= time();
				$PROCESSED["updated_by"]		= $ENTRADA_USER->getID();

				if(($db->AutoExecute("communities", $PROCESSED, "INSERT")) && ($community_id = $db->Insert_Id())) {
					if($db->AutoExecute("community_members", array("community_id" => $community_id, "proxy_id" => $ENTRADA_USER->getActiveId(), "member_active" => 1, "member_joined" => time(), "member_acl" => 1), "INSERT")) {

						foreach($community_modules as $module_id) {
							if(!communities_module_activate($community_id, $module_id)) {
								$NOTICE++;
								$NOTICESTR[] = "We were unable to activate module ".(int) $module_id." when creating your community.<br /><br />Your community will still function without this module. The MEdTech Unit has been informed of this problem and will resolve it shortly.";

								application_log("error", "Unable to active module ".(int) $module_id." for new community id ".(int) $community_id.". Database said: ".$db->ErrorMsg());
							}
						}

						$query = "INSERT INTO `community_pages` (`community_id`, `parent_id`, `page_order`, `page_type`, `menu_title`, `page_title`, `page_url`, `page_content`, `allow_member_view`, `allow_troll_view`, `allow_public_view`, `updated_date`, `updated_by`) VALUES
								(".$db->qstr($community_id).", '0', '0', 'default', 'Home', 'Home', '', '', '1', '1', '1', ".$db->qstr(time()).", ".$db->qstr($ENTRADA_USER->getActiveId()).")";
						if (!$db->Execute($query)) {
							$ERROR++;
							$ERRORSTR[] = "Your community was successfully created; however, a home page could not be creared for the community.<br /><br />The system administrator has been informed of this problem, and they will resolve it shortly.";
							application_log("error", "Could not create a home page in community [".$community_id."]. Database said: ".$db->ErrorMsg());
						} else {
							$query = "INSERT INTO `community_page_options` (`community_id`, `option_title`) VALUES
									(".$db->qstr($community_id).", 'show_announcements')";
							if (!$db->Execute($query)) {
								$ERROR++;
								$ERRORSTR[] = "Your community was successfully created; however, the 'show announcements' option could not be set for the community home page.<br /><br />The system administrator has been informed of this problem, and they will resolve it shortly.";
								application_log("error", "Could not add 'show_announcement` option for community [".$community_id."]. Database said: ".$db->ErrorMsg());
							}
							$query = "INSERT INTO `community_page_options` (`community_id`, `option_title`) VALUES
									(".$db->qstr($community_id).", 'show_events')";
							if (!$db->Execute($query)) {
								$ERROR++;
								$ERRORSTR[] = "Your community was successfully created; however, the 'show events' option could not be set for the community home page.<br /><br />The system administrator has been informed of this problem, and they will resolve it shortly.";
								application_log("error", "Could not add 'show_event` option for community [".$community_id."]. Database said: ".$db->ErrorMsg());
							}
							$query = "INSERT INTO `community_page_options` (`community_id`, `option_title`) VALUES
									(".$db->qstr($community_id).", 'show_history')";
							if (!$db->Execute($query)) {
								$ERROR++;
								$ERRORSTR[] = "Your community was successfully created; however, the 'show history' option could not be set for the community home page.<br /><br />The system administrator has been informed of this problem, and they will resolve it shortly.";
								application_log("error", "Could not add 'show_history` option for community [".$community_id."]. Database said: ".$db->ErrorMsg());
							}
						}

					if(!$PROCESSED["community_active"]) {
						if ($MAILING_LISTS["active"]) {
							$mail_list = new MailingList($community_id, $PROCESSED["community_list_mode"]);
						}
						$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/communities\\'', 10000)";

							if(communities_approval_notice($community_id)) {
								$SUCCESS++;
								$SUCCESSSTR[] = "You have successfully created your new community; however, it must be approved by an administrator before you can actually access the site.<br /><br />An e-mail has been sent to us and we will review and approve your Commmunity shortly. You will receive an e-mail notification once the Community has been activated.";

								communities_log_history($community_id, 0, $community_id, "community_history_create_moderated_community", 1);
							} else {
								$ERROR++;
								$ERRORSTR[] = "Your new community has been successfully created; however, this community requires an administrator's approval before it is activated and there was an error when trying to send an administrator this notification.<br /><br />The MEdTech Unit has been informed of this error and will contact you shortly.";

								application_log("error", "Community ID ".$community_id." was successfully created, but an admin approval notification could not be sent.");
							}

						} else {
							if ($MAILING_LISTS["active"]) {
								$mail_list = new MailingList($community_id, $PROCESSED["community_list_mode"]);
							}
							$community_url	= ENTRADA_URL."/community".$PROCESSED["community_url"];

							$ONLOAD[]		= "setTimeout('window.location=\\'".$community_url."\\'', 5000)";

							$SUCCESS++;
							$SUCCESSSTR[] = "<strong>You have successfully created your new community!</strong><br /><br />Please <a href=\"".$community_url."\">click here</a> to proceed to it or you will be automatically forwarded in 5 seconds.";

							communities_log_history($community_id, 0, $community_id, "community_history_create_active_community", 1);
						}
					} else {
						$ERROR++;
						$ERRORSTR[] = "Your community was successfully created; however, administrative permissions for your account could not be set to the new community.<br /><br />The system administrator has been informed of this problem, and they will resolve it shortly.";

						application_log("error", "Community was created, but admin permissions for proxy id ".$ENTRADA_USER->getActiveId()." could not be set. Database said: ".$db->ErrorMsg());
					}
				} else {
					$ERROR++;
					$ERRORSTR[] = "We encountered a problem while creating your new community.<br /><br />The system administrator has been informed of this problem, please try again later.";

					application_log("error", "Failed to create new community. Database said: ".$db->ErrorMsg());
				}
			}

			if($ERROR) {
				$PROCESSED	= $_POST;
				$STEP		= 2;
			} else {
				application_log("success", "Community ID ".$community_id." was successfully created.");
			}
			break;
		case 2 :
		/**
		 * This error checking is actually done above because it's a requirement for any page (including 3).
		 */
			$template_selection = 0;
			continue;
		break;
		case 1 :
		default :
			continue;
		break;
	}

	// Display Content
	switch($STEP) {
		case 3 :
			if($SUCCESS) {
				echo display_success();
			}
			break;
		case 2 :
			$ONLOAD[] = "validateShortname('".html_encode($PROCESSED["community_shortname"])."')";

			if((!isset($PROCESSED["community_registration"])) || (!(int) $PROCESSED["community_registration"])) {
				$ONLOAD[] = "selectRegistrationOption('0')";
			} else {
				$ONLOAD[] = "selectRegistrationOption('".(int) $PROCESSED["community_registration"]."')";
			}

			if($COMMUNITY_PARENT) {
				$fetched	= array();
				communities_fetch_parents($COMMUNITY_PARENT, $fetched);

				if((is_array($fetched)) && (@count($fetched))) {
					$community_parents	= array_reverse($fetched);
				} else {
					$community_parents	= false;
				}
				unset($fetched);
			}
			?>
<h2>Step 2: Community Details</h2>
			<?php
			if($ERROR) {
				echo display_error();
			}
			if($NOTICE) {
				echo display_notice();
			}
			?>
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
<form action="<?php echo ENTRADA_URL."/".$MODULE."?".replace_query(array("action" => "create", "step" => 3)); ?>" method="post">
	<input type="hidden" name="category_id" value="<?php echo html_encode($CATEGORY_ID); ?>" />
	<input type="hidden" name="community_parent" value="<?php echo html_encode($COMMUNITY_PARENT); ?>" />
	<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Creating a Community">
		<colgroup>
			<col style="width: 3%" />
			<col style="width: 20%" />
			<col style="width: 77%" />
		</colgroup>
		<tr>
			<td colspan="3">
				<h2>Community Details</h2>
			</td>
		</tr>
					<?php
					if(is_array($community_parents)) {
						?>
		<tr>
			<td></td>
			<td><span class="form-nrequired">Community Path</span></td>
			<td>
								<?php
								foreach($community_parents as $result) {
									echo html_encode($result["community_title"])." / ";
								}
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
				<input type="text" id="community_title" name="community_title" value="<?php echo html_encode($PROCESSED["community_title"]); ?>" maxlength="64" style="width: 250px" onkeyup="validateShortname(this.value)" />
				<span class="content-small">(<strong>Example:</strong> Medicine Club)</span>
			</td>
		</tr>
		<tr>
			<td><?php echo help_create_button("Community Keywords", ""); ?></td>
			<td><label for="community_keywords" class="form-nrequired">Community Keywords</label></td>
			<td>
				<input type="text" id="community_keywords" name="community_keywords" value="<?php echo html_encode($PROCESSED["community_keywords"]); ?>" maxlength="255" style="width: 90%" />
			</td>
		</tr>
		<tr>
			<td style="vertical-align: top"><?php echo help_create_button("Community Description", ""); ?></td>
			<td style="vertical-align: top"><label for="community_description" class="form-nrequired">Community Description</label></td>
			<td><textarea id="community_description" name="community_description" style="width: 90%; height: 75px"><?php echo html_encode($PROCESSED["community_description"]); ?></textarea></td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td><?php echo help_create_button("Community Shortname", ""); ?></td>
			<td><label for="community_shortname" class="form-required">Community Shortname</label></td>
			<td>
				<input type="text" id="community_shortname" name="community_shortname" value="" maxlength="20" style="width: 150px" onkeyup="validateShortname(this.value)" />
				<span class="content-small">(<strong>Example:</strong> medicine_club)</span>
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
			<td class="content-small" style="padding-bottom: 15px">
				<strong>Help Note:</strong> The community shortname is a name used to uniquely identify your new community in the URL; a username for the community of sorts. It must lower-case, less than 20 characters, and can only contain letters, numbers, underscore or period.
				<br /><br />
							<?php
							echo ENTRADA_URL."/community/";
							if(is_array($community_parents)) {
								foreach($community_parents as $result) {
									echo html_encode($result["community_shortname"])."/";
								}
							}
							?><span id="displayed_shortname"></span>
			</td>
		</tr>
		<tr>
			<td style="padding-top:6px;"><?php echo help_create_button("Community Template", "community_template"); ?></td>
			<td style="padding-top:6px;"><label for="community_template" class="form-nrequired">Community Template</label></td>
			<td>
				<div>
					<?php
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
													if (in_array($GROUP, $default_categories)) {
														$default_categories[] = $category_permission["template"];
													}
													
												}
											}
										}
										?>
										<?php
									}
									if ((in_array($GROUP, $groups) && in_array($CATEGORY_ID, $category)) || (in_array($community_template["template_name"], $default_templates)) || (in_array($community_template["template_name"], $default_categories))) {
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
		<?php /* <tr>
			<td><?php echo help_create_button("Contact E-Mail Address", ""); ?></td>
			<td><label for="community_email" class="form-nrequired">Contact E-Mail</label></td>
			<td><input type="text" id="community_email" name="community_email" value="<?php echo html_encode($PROCESSED["community_email"]); ?>" maxlength="128" style="width: 250px" /></td>
		</tr>
		<tr>
			<td><?php echo help_create_button("External Website", ""); ?></td>
			<td><label for="community_website" class="form-nrequired">External Website</label></td>
			<td><input type="text" id="community_website" name="community_website" value="<?php echo html_encode($PROCESSED["community_website"]); ?>" maxlength="1055" style="width: 250px" /></td>
		</tr> */ ?>
		<tr>
			<td colspan="3" style="padding-top: 20px">
				<h2>Community Modules</h2>
			</td>
		</tr>
		<tr>
			<td style="vertical-align: top"><?php echo help_create_button("Available Modules", ""); ?></td>
			<td style="vertical-align: top"><span class="form-required">Available Modules</span></td>
			<td>
							<?php
							$query	= "SELECT * FROM `communities_modules` WHERE `module_active` = '1' AND `module_visible` = '1' ORDER BY `module_title` ASC";
							$results	= $db->GetAll($query);
							if($results) {
								?>
				<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Setting Available Modules">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 97%" />
					</colgroup>
					<tbody>
										<?php
										foreach($results as $result) {
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
		<tr>
			<td colspan="3" style="padding-top: 10px">
				<h2>Community Permissions</h2>
			</td>
		</tr>
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
							<td style="vertical-align: top"><input type="radio" name="community_registration" id="community_registration_0" value="0" onclick="selectRegistrationOption('0')" style="vertical-align: middle"<?php echo (((!isset($PROCESSED["community_registration"])) || (isset($PROCESSED["community_registration"])) && ((int) $PROCESSED["community_registration"] === 0)) ? " checked=\"checked\"" : ""); ?> /></td>
							<td>
								<label for="community_registration_0" class="normal-green">Open Community</label>
								<div class="content-small">Any authenticated user can access this community without registering.</div>
							</td>
						</tr>
						<tr>
							<td style="vertical-align: top"><input type="radio" name="community_registration" id="community_registration_1" value="1" onclick="selectRegistrationOption('1')" style="vertical-align: middle"<?php echo ((((isset($PROCESSED["community_registration"])) && ($PROCESSED["community_registration"] == 1))) ? " checked=\"checked\"" : ""); ?> /></td>
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
												if((is_array($GROUP_TARGETS)) && ($total_sresults = count($GROUP_TARGETS))) {
													$count = 0;
													$column = 0;
													$max_columns = 2;

													echo "<table style=\"width: 100%\" cellspacing=\"0\" cellpadding=\"1\" border=\"0\" summary=\"Available Groups\">\n";
													echo "<colgroup>\n";
													echo "	<col style=\"width: 50%\" />\n";
													echo "	<col style=\"width: 50%\" />\n";
													echo "</colgroup>\n";
													echo "<tbody>\n";
													echo "	<tr>\n";
													foreach($GROUP_TARGETS as $group => $result) {
														$count++;
														$column++;

														echo "	<td>\n";
														echo "		<input type=\"checkbox\" id=\"community_registration_groups_".$group."\" name=\"community_registration_groups[]\" value=\"".$group."\" style=\"vertical-align: middle\"".(((isset($community_groups)) && (is_array($community_groups)) && (in_array($group, $community_groups))) ? " checked=\"checked\"" : "")." /> <label for=\"community_registration_groups_".$group."\" class=\"content-small\">".html_encode($result)."</label>\n";
														echo "	</td>\n";
														if(($count == $total_sresults) && ($column < $max_columns)) {
															for($i = 0; $i < ($max_columns - $column); $i++) {
																echo "	<td>&nbsp;</td>\n";
															}
														}

														if(($count == $total_sresults) || ($column == $max_columns)) {
															$column = 0;
															echo "	</tr>\n";

															if($count < $total_sresults) {
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
					<?php
					if ($MAILING_LISTS["active"]) {
						?>
		<tr><td colspan="3"><h2 style="margin-top: 0px">Community Mailing List</h2></td></tr>
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
							<td style="padding-bottom: 5px; vertical-align: top"><input type="radio" name="community_list_mode" id="community_list_announcement" style="vertical-align: middle" value="announcements" <?php echo (((!isset($PROCESSED["community_list_mode"]) && $mail_list->type == "announcements") || (isset($PROCESSED["community_list_mode"])) && ($PROCESSED["community_list_mode"] == "announcements")) ? " checked=\"checked\"" : ""); ?>/></td>
							<td style="padding-bottom: 5px; vertical-align: top">
								<label for="community_list_announcement" class="normal-green">Announcement Mode</label>
								<div class="content-small">Allow administrators of this community to send out email announcements to all the members of the community through the mailing list.</div>
							</td>
						</tr>
						<tr>
							<td style="padding-bottom: 5px; vertical-align: top"><input type="radio" name="community_list_mode" id="community_list_discussion" style="vertical-align: middle" value="discussion" <?php echo (((!isset($PROCESSED["community_list_mode"]) && $mail_list->type == "discussion") || (isset($PROCESSED["community_list_mode"])) && ($PROCESSED["community_list_mode"] == "discussion")) ? " checked=\"checked\"" : ""); ?>/></td>
							<td style="padding-bottom: 5px; vertical-align: top">
								<label for="community_list_discussion" class="normal-green">Discussion Mode</label>
								<div class="content-small">Allow all members of this community to send out email to the community through the mailing list.</div></td>
						</tr>
						<tr>
							<td style="padding-bottom: 5px; vertical-align: top"><input type="radio" name="community_list_mode" id="community_list_deactivate" style="vertical-align: middle" value="inactive" <?php echo (((!isset($PROCESSED["community_list_mode"]) || $mail_list->type == "inactive") || (isset($PROCESSED["community_list_mode"])) && ($PROCESSED["community_list_mode"] == "inactive")) ? " checked=\"checked\"" : ""); ?>/></td>
							<td>
								<label for="community_list_deactivates" class="normal-green">Deactivate List</label>
								<div class="content-small">Disable the mailing list for this community so members cannot be contacted through the list.</div>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
					<?php
					}
					?>
		<tr>
			<td colspan="3" style="padding-top: 25px">
				<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td style="width: 25%; text-align: left">
							<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL."/".$MODULE; ?>'" />
						</td>
						<td style="width: 75%; text-align: right; vertical-align: middle">
							<input type="submit" class="button" value="Create" />
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>
<br /><br />
			<?php
			break;
		case 1 :
		default :
			?>
<h2>Step 1: Choosing Your Category</h2>
			<?php
			if($ERROR) {
				echo display_error();
			}
			if($NOTICE) {
				echo display_notice();
			}

			$query	= "
				SELECT *
				FROM `communities_categories`
				WHERE `category_parent` = '0'
					AND `category_visible` = '1'
				ORDER BY `category_title` ASC";
			$results	= $db->GetAll($query);
			if($results) {
				?>
<table style="width: 100%" cellspacing="0" cellpadding="2" border="0">
	<colgroup>
		<col style="width: 50%" />
		<col style="width: 50%" />
	</colgroup>
	<tbody>
						<?php
						foreach($results as $result) {
							echo "<tr>\n";
							echo "	<td colspan=\"2\"><div class=\"strong-green\"><img src=\"".ENTRADA_URL."/images/btn_attention.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" /> ".html_encode($result["category_title"])."</div></td>\n";
							echo "</tr>\n";
							$query	= "
						SELECT *
						FROM `communities_categories`
						WHERE `category_parent` = ".$db->qstr($result["category_id"])."
							AND `category_visible` = '1'
						ORDER BY `category_title` ASC";
							$sresults	= $db->GetAlL($query);
							if($sresults) {
								$total_sresults	= @count($sresults);
								$count			= 0;
								$column			= 0;
								$max_columns		= 2;
								foreach($sresults as $sresult) {
									$count++;
									$column++;
									$communities = communities_count($sresult["category_id"]);
									echo "\t<td style=\"padding: 2px 2px 2px 19px\">";
									echo "	<div style=\"position: relative; vertical-align: middle; border-bottom: 1px #CCCCCC dotted\">\n";
									echo "		<img src=\"".ENTRADA_URL."/images/btn_folder_go.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"margin-right: 2px\" />";
									echo "		<a href=\"".ENTRADA_URL."/communities?".replace_query(array("section" => "create", "step" => 2, "category" => $sresult["category_id"], "parent" => (($COMMUNITY_PARENT) ? $COMMUNITY_PARENT : false)))."\" style=\"font-size: 13px; color: #006699\">".html_encode($sresult["category_title"])."</a>";
									if($communities) {
										echo "<span style=\"position: absolute; right: 0px; display: inline; vertical-align: middle\" class=\"content-small\">(".$communities." communit".(($communities != 1) ? "ies" : "y").")</span>";
									}
									echo "	</div>\n";
									echo "</td>\n";

									if(($count == $total_sresults) && ($column < $max_columns)) {
										for($i = 0; $i < ($max_columns - $column); $i++) {
											echo "<td>&nbsp;</td>\n";
										}
									}

									if(($count == $total_sresults) || ($column == $max_columns)) {
										$column = 0;
										echo "</tr>\n";

										if($count < $total_sresults) {
											echo "<tr>\n";
										}
									}
								}
								echo "<tr>\n";
								echo "	<td colspan=\"2\">&nbsp;</td>\n";
								echo "</tr>\n";
							}
						}
						?>
	</tbody>
</table>
			<?php
			} else {
				$ERROR++;
				$ERRORSTR[] = "There does no seem to be any Community Categories in the database right now.<br /><br />The MEdTech Unit has been notified of this problem, please try again later. We apologize for any inconvenience this has caused.";

				echo display_error();

				application_log("error", "No community categories in the database. Database said: ".$db->ErrorMsg());
			}
			break;
	}
}
?>
