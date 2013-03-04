<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Controller file for the document sharing module.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
*/

if (!defined("COMMUNITY_INCLUDED")) {
	exit;
} elseif (!$COMMUNITY_LOAD) {
	exit;
}

define("IN_SHARES", true);

communities_build_parent_breadcrumbs();
$BREADCRUMB[]			= array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL, "title" => $MENU_TITLE);
$VALID_MIME_TYPES		= array();
$VALID_MAX_FILESIZE		= 31457280; // 30MB
$DOWNLOAD				= false;

/**
 * If the download variable exists in the URL on view-file and it's a valid integer
 * it will download the specified version of the file (i.e. ...action=view-file&id=123&download=6
 */
if (isset($_GET["download"])) {
	if ($tmp_download = clean_input($_GET["download"], "alphanumeric")) {
		if ((int) $tmp_download) {
			$DOWNLOAD = (int) $tmp_download;
		} elseif ($tmp_download == "latest") {
			$DOWNLOAD = "latest";
		}
	}
}

/**
 * This function handles granular permissions levels (where as communities_module_access handles higer level permissions)
 * for the actual shares.
 *
 * @param int $cshare_id
 * @param string $section
 * @return bool
 */
function shares_module_access($cshare_id = 0, $section = "") {
	global $db, $COMMUNITY_ID, $LOGGED_IN, $COMMUNITY_MEMBER, $COMMUNITY_ADMIN, $NOTICE, $NOTICESTR, $ERROR, $ERRORSTR, $PAGE_ID;

	$allow_to_load = false;

	if (((bool) $LOGGED_IN) && ((bool) $COMMUNITY_MEMBER) && ((bool) $COMMUNITY_ADMIN)) {
		$allow_to_load = true;
	} else {
		if ($cshare_id = (int) $cshare_id) {
			$query	= "SELECT * FROM `community_shares` WHERE `cshare_id` = ".$db->qstr($cshare_id)." AND `cpage_id` = ".$db->qstr($PAGE_ID)." AND `community_id` = ".$db->qstr($COMMUNITY_ID);
			$result	= $db->CacheGetRow(CACHE_TIMEOUT, $query);
			if ($result) {
				switch($section) {
					case "add-folder" :
					case "delete-folder" :
					case "edit-folder" :
						/**
						 * This is false, because this should have been covered by the statement
						 * above as creating new galleries is an administrative only function.
						 */
						$allow_to_load = false;
					break;
					case "add-file" :
					case "delete-file" :
					case "edit-file" :
						if ($LOGGED_IN) {
							if ($COMMUNITY_MEMBER) {
								if ((int) $result["allow_member_upload"]) {
									$allow_to_load = true;
								}
							} elseif ((int) $result["allow_troll_upload"]) {
								$allow_to_load = true;
							}
						} else {
							$allow_to_load = false;
						}
					break;
					case "add-comment" :
					case "edit-comment" :
					case "delete-comment" :
						if ($LOGGED_IN) {
							if ($COMMUNITY_MEMBER) {
								if ((int) $result["allow_member_comment"]) {
									$allow_to_load = true;
								}
							} elseif ((int) $result["allow_troll_comment"]) {
								$allow_to_load = true;
							}
						} else {
							$allow_to_load = false;
						}
					break;
					case "view-folder" :
					case "view-file" :
						if ($LOGGED_IN) {
							if ($COMMUNITY_MEMBER) {
								if ((int) $result["allow_member_read"]) {
									$allow_to_load = true;
								}
							} elseif ((int) $result["allow_troll_read"]) {
								$allow_to_load = true;
							}
						} elseif ((int) $result["allow_public_read"]) {
							$allow_to_load = true;
						}
					break;
					case "add-revision" :
					case "delete-revision" :
						/**
						 * This must allow to load at this point; however, the real check
						 * occurs on the per-file level in shares_file_module_access() because
						 * the permissions are set on a per-file basis.
						 */
						$allow_to_load = true;
					break;
					case "index" :
						$allow_to_load = true;
					break;
					default :
						continue;
					break;
				}
			}
		}

		if ($allow_to_load) {
			if ((int) $result["folder_active"]) {
				if ((!$release_date = (int) $result["release_date"]) || ($release_date <= time())) {
					if ((!$release_until = (int) $result["release_until"]) || ($release_until > time())) {
						/**
						 * You're good to go, no further checks at this time.
						 * If you need to add more checks, this is there they would go.
						 */
					} else {
						$NOTICE++;
						$NOTICESTR[]	= "This shared folder was only accessible until <strong>".date(DEFAULT_DATE_FORMAT, $release_until)."</strong>.<br /><br />Please contact your community administrators for further assistance.";

						$allow_to_load	= false;
					}
				} else {
					$NOTICE++;
					$NOTICESTR[]	= "This shared folder will not be accessible until <strong>".date(DEFAULT_DATE_FORMAT, $release_date)."</strong>.<br /><br />Please check back at this time, thank-you.";

					$allow_to_load	= false;
				}
			} else {
				$NOTICE++;
				$NOTICESTR[]	= "This shared folder was deactivated <strong>".date(DEFAULT_DATE_FORMAT, $result["updated_date"])."</strong> by <strong>".html_encode(get_account_data("firstlast", $result["updated_by"]))."</strong>.<br /><br />If there has been a mistake or you have questions relating to this issue please contact the MEdTech Unit directly.";

				$allow_to_load	= false;
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "You do not have access to this shared folder.<br /><br />".(($LOGGED_IN) ? "If you believe there has been a mistake, please contact a community administrator for assistance." : "You are not currently authenticated, please log in by clicking the login link to the right.");
		}
	}

	return $allow_to_load;
}

/**
 * This function handles granular permissions levels (where as communities_module_access handles higer level permissions)
 * for the actual files within the share.
 *
 * @param int $csfile_id
 * @param string $section
 * @return bool
 */
function shares_file_module_access($csfile_id = 0, $section = "") {
	global $db, $COMMUNITY_ID, $LOGGED_IN, $COMMUNITY_MEMBER, $COMMUNITY_ADMIN, $NOTICE, $NOTICESTR, $ERROR, $ERRORSTR, $ENTRADA_USER;

	$allow_to_load = false;

	if (((bool) $LOGGED_IN) && ((bool) $COMMUNITY_MEMBER) && ((bool) $COMMUNITY_ADMIN)) {
		$allow_to_load = true;
	} else {
		if ($csfile_id = (int) $csfile_id) {
			$query	= "SELECT * FROM `community_share_files` WHERE `csfile_id` = ".$db->qstr($csfile_id)." AND `community_id` = ".$db->qstr($COMMUNITY_ID);
			$result	= $db->CacheGetRow(CACHE_TIMEOUT, $query);
			if ($result) {
				if ($allow_to_load = shares_module_access($result["cshare_id"], $section)) {
					switch($section) {
						case "delete-file" :
						case "edit-file" :
							if ($ENTRADA_USER->getActiveId() != (int) $result["proxy_id"]) {
								$allow_to_load = false;
							}
						break;
						case "add-revision" :
							if ($LOGGED_IN) {
								if ($result["proxy_id"] != $ENTRADA_USER->getActiveId()){
									if ($COMMUNITY_MEMBER) {
										if (!(int) $result["allow_member_revision"]) {
											$allow_to_load = false;	
										}
									} elseif (!(int) $result["allow_troll_revision"]) {
										$allow_to_load = false;
									}
								}else{
									
									$query = "SELECT `allow_troll_upload`, `allow_member_upload` FROM `community_shares` WHERE `cshare_id` = ".$db->qstr($result["cshare_id"])." AND `community_id` = ".$db->qstr($COMMUNITY_ID);
									$share_permission = $db->GetRow($query);
									
									if ($COMMUNITY_MEMBER) {
										if (!(int) $share_permission["allow_member_upload"]) {
											$allow_to_load = false;	
										}
									} elseif (!(int) $share_permission["allow_troll_upload"]) {
										$allow_to_load = false;
									}
								}
							} else {
								$allow_to_load = false;
							}
						break;
						default :
							continue;
						break;
					}
				}
			}
		}
		if ($allow_to_load) {
			if ((int) $result["file_active"]) {
				/**
				 * Don't worry about checking the release dates if the person viewing
				 * the photo is the photo uploader.
				 */
				if (!$LOGGED_IN || $ENTRADA_USER->getActiveId() != (int) $result["proxy_id"]) {
					if ((!$release_date = (int) $result["release_date"]) || ($release_date <= time())) {
						if ((!$release_until = (int) $result["release_until"]) || ($release_until > time())) {
							/**
							 * You're good to go, no further checks at this time.
							 * If you need to add more checks, this is there they would go.
							 */
						} else {
							$NOTICE++;
							$NOTICESTR[]	= "This file was only accessible until <strong>".date(DEFAULT_DATE_FORMAT, $release_until)."</strong>.<br /><br />Please contact your community administrators for further assistance.";

							$allow_to_load	= false;
						}
					} else {
						$NOTICE++;
						$NOTICESTR[]	= "This file will not be accessible until <strong>".date(DEFAULT_DATE_FORMAT, $release_date)."</strong>.<br /><br />Please check back at this time, thank-you.";

						$allow_to_load	= false;
					}
				}
			} else {
				$NOTICE++;
				$NOTICESTR[]	= "This file was deactivated <strong>".date(DEFAULT_DATE_FORMAT, $result["updated_date"])."</strong> by <strong>".html_encode(get_account_data("firstlast", $result["updated_by"]))."</strong>.<br /><br />If there has been a mistake or you have questions relating to this issue please contact the MEdTech Unit directly.";

				$allow_to_load	= false;
			}
		} else {
			if (!$ERROR) {
				$ERROR++;
				$ERRORSTR[] = "You do not have access to this file.<br /><br />If you believe there has been a mistake, please contact a community administrator for assistance.";
			}
		}
	}
	
	return $allow_to_load;
}

/**
 * This function handles granular permissions levels (where as communities_module_access handles higer level permissions)
 * for the actual gallery comments.
 *
 * @param int $cgallery_id
 * @param string $section
 * @return bool
 */
function shares_comment_module_access($cscomment_id = 0, $section = "") {
	global $db, $COMMUNITY_ID, $LOGGED_IN, $COMMUNITY_MEMBER, $COMMUNITY_ADMIN, $NOTICE, $NOTICESTR, $ERROR, $ERRORSTR, $ENTRADA_USER;

	$allow_to_load = false;

	if (((bool) $LOGGED_IN) && ((bool) $COMMUNITY_MEMBER) && ((bool) $COMMUNITY_ADMIN)) {
		$allow_to_load = true;
	} else {
		if ($cscomment_id = (int) $cscomment_id) {

			$query	= "SELECT * FROM `community_share_comments` WHERE `cscomment_id` = ".$db->qstr($cscomment_id)." AND `community_id` = ".$db->qstr($COMMUNITY_ID);
			$result	= $db->CacheGetRow(CACHE_TIMEOUT, $query);
			if ($result) {
				if ($allow_to_load = shares_module_access($result["cshare_id"], $section)) {
					switch($section) {
						case "delete-comment" :
						case "edit-comment" :
							if ($ENTRADA_USER->getActiveId() != (int) $result["proxy_id"]) {
								$allow_to_load = false;
							}
						break;
						default :
							continue;
						break;
					}
				}
			}
		}
		if ($allow_to_load) {
			if ((int) $result["comment_active"]) {
				/**
				 * You're good to go, no further checks at this time.
				 * If you need to add more checks, this is there they would go.
				 */
			} else {
				$NOTICE++;
				$NOTICESTR[]	= "This comment was deactivated <strong>".date(DEFAULT_DATE_FORMAT, $result["updated_date"])."</strong> by <strong>".html_encode(get_account_data("firstlast", $result["updated_by"]))."</strong>.<br /><br />If there has been a mistake or you have questions relating to this issue please contact the MEdTech Unit directly.";

				$allow_to_load	= false;
			}
		} else {
			if (!$ERROR) {
				$ERROR++;
				$ERRORSTR[] = "You do not have access to this comment.<br /><br />If you believe there has been a mistake, please contact a community administrator for assistance.";
			}
		}
	}

	return $allow_to_load;
}

/**
 * This function handles granular permissions levels (where as communities_module_access handles higer level permissions)
 * for the actual file version.
 *
 * @param int $csfversion_id
 * @param string $section
 * @return bool
 */
function shares_file_version_module_access($csfversion_id = 0, $section = "") {
	global $db, $COMMUNITY_ID, $LOGGED_IN, $COMMUNITY_MEMBER, $COMMUNITY_ADMIN, $NOTICE, $NOTICESTR, $ERROR, $ERRORSTR, $ENTRADA_USER;

	$allow_to_load = false;

	if (((bool) $LOGGED_IN) && ((bool) $COMMUNITY_MEMBER) && ((bool) $COMMUNITY_ADMIN)) {
		$allow_to_load = true;
	} else {
		if ($csfversion_id = (int) $csfversion_id) {

			$query	= "SELECT * FROM `community_share_file_versions` WHERE `csfversion_id` = ".$db->qstr($csfversion_id)." AND `community_id` = ".$db->qstr($COMMUNITY_ID);
			$result	= $db->CacheGetRow(CACHE_TIMEOUT, $query);
			if ($result) {
				if ($allow_to_load = shares_module_access($result["cshare_id"], $section)) {
					switch($section) {
						case "delete-revision" :
							if ($ENTRADA_USER->getActiveId() != (int) $result["proxy_id"]) {
								$allow_to_load = false;
							}
						break;
						default :
							continue;
						break;
					}
				}
			}
		}
		if ($allow_to_load) {
			if ((int) $result["file_active"]) {
				/**
				 * You're good to go, no further checks at this time.
				 * If you need to add more checks, this is there they would go.
				 */
			} else {
				$NOTICE++;
				$NOTICESTR[]	= "This file revision was deactivated <strong>".date(DEFAULT_DATE_FORMAT, $result["updated_date"])."</strong> by <strong>".html_encode(get_account_data("firstlast", $result["updated_by"]))."</strong>.<br /><br />If there has been a mistake or you have questions relating to this issue please contact the MEdTech Unit directly.";

				$allow_to_load	= false;
			}
		} else {
			if (!$ERROR) {
				$ERROR++;
				$ERRORSTR[] = "You do not have access to this file revision.<br /><br />If you believe there has been a mistake, please contact a community administrator for assistance.";
			}
		}
	}

	return $allow_to_load;
}

function shares_file_navigation($cshare_id = 0, $csfile_id = 0) {
	global $db, $COMMUNITY_ID, $PAGE_URL, $LOGGED_IN, $COMMUNITY_MEMBER, $COMMUNITY_ADMIN, $ENTRADA_USER;

	$output = false;
	if (($cshare_id = (int) $cshare_id) && ($csfile_id = (int) $csfile_id)) {
		/**
		 * Provide the queries with the columns to order by.
		 */
		switch($_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["sb"]) {
			case "title" :
				$SORT_BY	= "a.`file_title` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["so"]).", a.`updated_date` DESC";
			break;
			case "owner" :
				$SORT_BY	= "CONCAT_WS(', ', b.`lastname`, b.`firstname`) ".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["so"]).", a.`updated_date` DESC";
			break;
			case "date" :
			default :
				$SORT_BY	= "a.`updated_date` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["so"]);
			break;
		}
		$query		= "
					SELECT a.`csfile_id`
					FROM `community_share_files` AS a
					LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
					ON a.`proxy_id` = b.`id`
					LEFT JOIN `community_shares` AS c
					ON a.`cshare_id` = c.`cshare_id`
					WHERE a.`cshare_id` = ".$db->qstr($cshare_id)."
					AND a.`community_id` = ".$db->qstr($COMMUNITY_ID)."
					AND a.`file_active` = '1'
					".((!$LOGGED_IN) ? " AND c.`allow_public_read` = '1'" : (($COMMUNITY_MEMBER) ? ((!$COMMUNITY_ADMIN) ? " AND c.`allow_member_read` = '1'" : "") : " AND c.`allow_troll_read` = '1'"))."
					".((!$COMMUNITY_ADMIN) ? ($LOGGED_IN ? " AND ((a.`proxy_id` = ".$db->qstr($ENTRADA_USER->getActiveId()).") OR " : " AND (")."(a.`release_date` = '0' OR a.`release_date` <= ".$db->qstr(time()).") AND (a.`release_until` = '0' OR a.`release_until` > ".$db->qstr(time())."))" : "")."
					ORDER BY ".$SORT_BY;
		$results	= $db->GetAll($query);
		if ($results) {
			$output = array("back" => false, "next" => false);

			if ($csfile_id_search = dimensional_array_search($csfile_id, $results)) {
				$back_key = ($csfile_id_search[0] - 1);
				$next_key = ($csfile_id_search[0] + 1);
				if ((isset($results[$back_key]["csfile_id"])) && ($csfile_id_back = (int) $results[$back_key]["csfile_id"])) {
					$output["back"] = $csfile_id_back;
				}
				if ((isset($results[$next_key]["csfile_id"])) && ($csfile_id_next = (int) $results[$next_key]["csfile_id"])) {
					$output["next"] = $csfile_id_next;
				}
			}
		}
	}

	return $output;
}

if (communities_module_access($COMMUNITY_ID, $MODULE_ID, $SECTION)) {
	if ((@file_exists($section_to_load = COMMUNITY_ABSOLUTE.DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR.$COMMUNITY_MODULE.DIRECTORY_SEPARATOR.$SECTION.".inc.php")) && (@is_readable($section_to_load))) {
		require_once($section_to_load);
	} else {
		$ONLOAD[]	= "setTimeout('window.location=\\'".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."\\'', 5000)";

		$ERROR++;
		$ERRORSTR[] = "The action you are looking for does not exist for this module.";

		echo display_error();

		application_log("error", "Communities system tried to load ".$section_to_load." which does not exist or is not readable by PHP.");
	}
} else {
	$ONLOAD[]	= "setTimeout('window.location=\\'".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."\\'', 5000)";

	$ERROR++;
	$ERRORSTR[] = "You do not have access to this section of this module. Please contact a community administrator for assistance.";

	echo display_error();
}
?>