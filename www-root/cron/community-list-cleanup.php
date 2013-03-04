<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Cron job responsible for checking google mail-list vs local database list and fixing any issues.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <ryan.warner@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 *
*/
@set_time_limit(0);
@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    dirname(__FILE__) . "/../core/includes",
    dirname(__FILE__) . "/../core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

if (isset($MAILING_LISTS) && is_array($MAILING_LISTS) && $MAILING_LISTS["active"]) {
	require_once("Entrada/mail-list/mail-list.class.php");

	if (!file_exists(COMMUNITY_MAIL_LIST_MEMBERS_LOCK)) {
		if (@file_put_contents(COMMUNITY_MAIL_LIST_MEMBERS_LOCK, "L_O_C_K")) {
			
			$query = "	SELECT a.*, GROUP_CONCAT(b.`email`, ';', b.`member_active`, ';', b.`list_administrator`) AS `members`
						FROM `community_mailing_lists` AS a
						LEFT JOIN `community_mailing_list_members` AS b
						ON a.`community_id` = b.`community_id`
						WHERE `list_type` != 'inactive'
						GROUP BY a.`community_id`
						ORDER BY a.`last_checked` ASC
						LIMIT 10";

			if ($lists = $db->GetAll($query)) {
				foreach ($lists as $list) {

					$google_list = new MailingList($list["community_id"]);
					$google_list->fetch_current_list();
					$google_list_members = array_merge($google_list->current_owners, $google_list->current_members);

					$local_list_members = (!empty($list["members"]) ? explode(',', $list["members"]) : array());

					if (!empty($local_list_members)) {
						foreach ($local_list_members as $member) {
							/*
							* $member[0] = email
							* $member[1] = member_active
							* $member[2] = list_administrator
							*/
							$member = explode(';', $member);
							if (in_array($member[0], $google_list_members)) {
								switch ($member[1]) {
									case "0" :
										$query = "UPDATE `community_mailing_list_members` SET `member_active` = '1' WHERE `community_id` = ".$db->qstr($list["community_id"])." AND `email` = ".$db->qstr($member);
										if (!$db->Execute($query)) {
											application_log("error",  "An error occured while attempting to update community_mailing_lists, DB said: ".$db->ErrorMsg());
										}
									break;
									case "-1" :
										if ($google_list->is_member($member) || $google_list->is_owner($member)) {
											$google_list->remove($member);
											$query = "DELETE FROM `community_mailing_list_members` WHERE `community_id` = ".$db->qstr($list["community_id"])." AND `email` = ".$db->qstr($member[0]);
											if (!$db->Execute($query)) {
												application_log("error", "An error occured while attempting to delete from community_mailing_lists, DB said: ".$db->ErrorMsg());
											}
										}
									break;
									case "1" :
									default:
									break;
								}
								switch ($member[2]) {
									case "0" :
									case "-1" :
										if ($google_list->is_owner($member[0])) {
											$google_list->remove($member);
											$google_list->add($member);
											$query = "UPDATE `community_mailing_list_members` SET `member_active` = '1' WHERE `community_id` = ".$db->qstr($list["community_id"])." AND `email` = ".$db->qstr($member);
											if (!$db->Execute($query)) {
												application_log("error", "An error occured while attempting to update community_mailing_list_members, DB said: ".$db->ErrorMsg());
											}
										}
									break;
									case "2" :
									case "1" :
										if (!$google_list->is_owner($member[0])) {
											$google_list->remove($member[0]);
											$google_list->add($member[0], '1');
										}
									break;
									default:
									break;
								}
							} else {
								echo "<span style=\"color:red;\">";
								switch ($member[1]) {
									case "0" :
										$query = "SELECT `id` FROM `".AUTH_DATABASE."`.`user_data` WHERE `email` = ".$db->qstr($member);
										if ($proxy_id = $db->GetOne($query)) {
											$query = "	UPDATE `community_mailing_list_members` SET `member_active` = '1' WHERE `community_id` = ".$db->qstr($list["community_id"])." AND `email` = ".$db->qstr($member);
											if (!$db->Execute($query)) {
												application_log("error", "An error occured while attempting to update community_mailing_list_members, DB said: ".$db->ErrorMsg());
											}
										}
									break;
									case "-1" :
										$query = "DELETE FROM `community_mailing_list_members` WHERE `community_id` = ".$db->qstr($list["community_id"])." AND `email` = ".$db->qstr($member[0]);
										if (!$db->Execute($query)) {
											application_log("error", "An error occured while attempting to delete from community_mailing_list, DB said: ".$db->ErrorMsg());
										}
									break;
									case "1" :
									default:
										if (!$google_list->is_member($member[0])) {
											$google_list->add($member[0]);
										}
									break;
								}
								switch ($member[2]) {
									case "2" :
									case "1" :
										if ($google_list->is_member($member[0])) {
											$google_list->remove($member[0]);
										}
										$google_list->add($member[0], '1');
									break;
									case "0" :
									case "-1" :
									default:
									break;
								}
							}
							$member_list[] = $member[0];
						}
					}

					if (!empty($google_list_members)) {

						foreach ($google_list_members as $member) {
							if (!in_array($member, $member_list)) {
								$member_active = '1';
								$list_administrator = '0';

								if ($google_list->is_owner($member)) {
									$list_administrator = '1';
								}

								$query = "SELECT `id` FROM `".AUTH_DATABASE."`.`user_data` WHERE `email` = ".$db->qstr($member);
								if ($proxy_id = $db->GetOne($query)) {
									$query = "	INSERT INTO `community_mailing_list_members` (`community_id`, `proxy_id`, `email`, `member_active`, `list_administrator`) 
												VALUES (".$db->qstr($list["community_id"]).", ".$db->qstr($proxy_id).", ".$db->qstr($member).", ".$db->qstr($member_active).", ".$db->qstr($list_administrator).")";
									if (!$db->Execute($query)) {
										application_log("error", "Error inserting [".$member."] into community_mailing_list_members, DB said: ".$db->ErrorMsg());
									}
								} else {
									application_log("error", "Error inserting [".$member."] into community_mailing_list_members, user proxy_id could not be found. DB said: ".$db->ErrorMsg());
								}

							}
						}
					}

					$query = "UPDATE `community_mailing_lists` SET `last_checked` = ".$db->qstr(time())." WHERE `cmlist_id` = ".$db->qstr($list["cmlist_id"]);

					if (!$db->Execute($query)) {
						application_log("error", "Unable to update community_mailing_lists, DB said: ".$db->ErrorMsg());
					}

				}
			} else {
				application_log("notice", "An issue occured when attempting mailing list cleanup. No active mailing lists found. DB said: ".$db->ErrorMsg());
			}	
			
			if (!@unlink(COMMUNITY_MAIL_LIST_MEMBERS_LOCK)) {
				application_log("error", "Unable to delete mail-list member lock file: ".COMMUNITY_MAIL_LIST_MEMBERS_LOCK);
			}
			
		} 
	}
}