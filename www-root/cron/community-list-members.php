<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Cron job responsible for adding users to the google mail-list.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2009 Queen's University. All Rights Reserved.
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

	if ((@is_dir(CACHE_DIRECTORY)) && (@is_writable(CACHE_DIRECTORY))) {
		/**
		 * Lock present: application busy: quit
		 */

		if (!file_exists(COMMUNITY_MAIL_LIST_MEMBERS_LOCK)) {
			if (@file_put_contents(COMMUNITY_MAIL_LIST_MEMBERS_LOCK, "L_O_C_K")) {
				try {
					$limit = COMMUNITY_MAIL_LIST_MEMBERS_LIMIT;
					$community_id = 0;
					$query		=  "	SELECT a.* FROM `community_mailing_list_members` AS a
										JOIN `community_mailing_lists` AS b
										ON a.`community_id` = b.`community_id`
										WHERE a.`member_active` < 0
										AND b.`list_type` != 'inactive'
										ORDER BY a.`community_id` ASC";
					$members	= $db->GetAll($query);
					if ($members) {
						foreach ($members as $member) {
							if ($community_id != (int) $member["community_id"]) {
								$community_id = (int) $member["community_id"];

								$list = new MailingList($community_id);
							}

							echo "Delete: ".$member["email"]." -> ".$community_id."<br />";

							try {
							$list->remove_member($member["proxy_id"]);
							} catch (Exception $e) {
								echo $e->getCode()." - ".$e->getMessage();
								if ($e->getCode() == "502") {
									exit;
								}
								application_log("notice", "An issue was encountered while attempting to remove a user's email [".$member["email"]."] from a community [".$community_id."] mailing list. The server said: ".$e->getCode()." - ".$e->getMessage());
							}

							/**
							 * Email limit so exit
							 */
							if (!(--$limit)) {
								if (!@unlink(COMMUNITY_MAIL_LIST_MEMBERS_LOCK)) {
									application_log("error", "Unable to delete mail-list member lock file: ".COMMUNITY_MAIL_LIST_MEMBERS_LOCK);
								}
								exit;
							}
						}
					}

					$community_id = 0;
					$query		=  "	SELECT a.* FROM `community_mailing_list_members` AS a
										JOIN `community_mailing_lists` AS b
										ON a.`community_id` = b.`community_id`
										WHERE a.`member_active` = 0
										AND b.`list_type` != 'inactive'
										ORDER BY a.`community_id` ASC";
					$members	= $db->GetAll($query);
					if ($members) {
						foreach ($members as $member) {
							if ($community_id != (int) $member["community_id"]) {
								$community_id = (int) $member["community_id"];

								$list = new MailingList($community_id);
							}

							echo "Activate: ".$member["email"]." -> ".$community_id."<br/>";
							try {
							$list->activate_member($member["proxy_id"], ((int)$member["list_administrator"]));
							} catch (Exception $e) {
								echo $e->getMessage();
								if ($e->getCode() == "502") {
									exit;
								}
								application_log("notice", "An issue was encountered while attempting to add a user's email [".$member["email"]."] to a community [".$community_id."] mailing list. The server said: ".$e->getCode()." - ".$e->getMessage());
							}

							/**
							 * Email limit so exit
							 */
							if (!(--$limit)) {
								if (!@unlink(COMMUNITY_MAIL_LIST_MEMBERS_LOCK)) {
									application_log("error", "Unable to delete mail-list member lock file: ".COMMUNITY_MAIL_LIST_MEMBERS_LOCK);
								}
								exit;
							}
						}
					}

					$community_id = 0;
					$query		=  "	SELECT a.* FROM `community_mailing_list_members` AS a
										JOIN `community_mailing_lists` AS b
										ON a.`community_id` = b.`community_id`
										WHERE a.`list_administrator` = '2'
										AND b.`list_type` != 'inactive'
										ORDER BY a.`community_id` ASC";
					$members	= $db->GetAll($query);
					if ($members) {
						foreach ($members as $member) {
							if ($community_id != (int) $member["community_id"]) {
								$community_id = (int) $member["community_id"];

								$list = new MailingList($community_id);
							}

							echo "Promote: ".$member["email"]." -> ".$community_id."<br/>";
							try {
							$list->edit_member($member["proxy_id"], true);
							} catch (Exception $e) {
								echo $e->getMessage();
								if ($e->getCode() == "502") {
									exit;
								}
								application_log("notice", "An issue was encountered while attempting to promote a user's email [".$member["email"]."] to owner on a community [".$community_id."] mailing list. The server said: ".$e->getCode()." - ".$e->getMessage());
							}

							/**
							 * Email limit so exit
							 */
							if (!(--$limit)) {
								if (!@unlink(COMMUNITY_MAIL_LIST_MEMBERS_LOCK)) {
									application_log("error", "Unable to delete mail-list member lock file: ".COMMUNITY_MAIL_LIST_MEMBERS_LOCK);
								}
								exit;
							}
						}
					}

					$community_id = 0;
					$query		=  "	SELECT a.* FROM `community_mailing_list_members` AS a
										JOIN `community_mailing_lists` AS b
										ON a.`community_id` = b.`community_id`
										WHERE a.`list_administrator` = '-1'
										AND a.`member_active` = '1'
										AND b.`list_type` != 'inactive'
										ORDER BY a.`community_id` ASC";
					$members	= $db->GetAll($query);
					if ($members) {
						foreach ($members as $member) {
							if ($community_id != (int) $member["community_id"]) {
								$community_id = (int) $member["community_id"];

								$list = new MailingList($community_id);
							}

							echo "Demote: ".$member["email"]." -> ".$community_id."<br/>";

							try {
							$list->edit_member($member["proxy_id"], false);
							} catch (Exception $e) {
								echo $e->getMessage();
								if ($e->getCode() == "502") {
									exit;
								}
								application_log("notice", "An issue was encountered while attempting to demote a user's email [".$member["email"]."] to owner on a community [".$community_id."] mailing list. The server said: ".$e->getCode()." - ".$e->getMessage());
							}

							/**
							 * Email limit so exit
							 */
							if (!(--$limit)) {
								if (!@unlink(COMMUNITY_MAIL_LIST_MEMBERS_LOCK)) {
									application_log("error", "Unable to delete mail-list member lock file: ".COMMUNITY_MAIL_LIST_MEMBERS_LOCK);
								}
								exit;
							}
						}
					}

					if (!@unlink(COMMUNITY_MAIL_LIST_MEMBERS_LOCK)) {
						application_log("error", "Unable to delete mail-list member lock file: ".COMMUNITY_MAIL_LIST_MEMBERS_LOCK);
					}
				} catch (Exception $e) {
					@unlink(COMMUNITY_MAIL_LIST_MEMBERS_LOCK);
				}
			} else {
				application_log("error", "Unable to open mail-list member lock file: ".COMMUNITY_MAIL_LIST_MEMBERS_LOCK);
			}
		} else {
			/**
			 * Found old lock file get rid of it
			 */
			if (filemtime(COMMUNITY_MAIL_LIST_MEMBERS_LOCK) < time() - COMMUNITY_MAIL_LIST_MEMBERS_TIMEOUT ) {
				if (!@unlink(COMMUNITY_MAIL_LIST_MEMBERS_LOCK)) {
					application_log("error", "Unable to delete mail-list member lock file: ".COMMUNITY_MAIL_LIST_MEMBERS_LOCK);
				}
			}
		}
	} else {
		application_log("error", "The specified CACHE_DIRECTORY [".CACHE_DIRECTORY."] either does not exist or is not writable.");
	}
}
?>