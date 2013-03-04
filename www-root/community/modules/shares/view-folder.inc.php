<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Used to list the files that exist within the specified folder.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
*/

if ((!defined("COMMUNITY_INCLUDED")) || (!defined("IN_SHARES"))) {
	exit;
} elseif (!$COMMUNITY_LOAD) {
	exit;
}

if ($RECORD_ID) {
	$query			= "SELECT * FROM `community_shares` WHERE `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `cshare_id` = ".$db->qstr($RECORD_ID);
	$folder_record	= $db->GetRow($query);
	if ($folder_record) {
		if (shares_module_access($RECORD_ID, "view-folder")) {
			$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-folder&id=".$RECORD_ID, "title" => $folder_record["folder_title"]);

			/**
			 * Update requested sort column.
			 * Valid: date, title
			 */
			if (isset($_GET["sb"])) {
				if (@in_array(trim($_GET["sb"]), array("date", "title", "owner"))) {
					$_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["sb"] = trim($_GET["sb"]);
				}

				$_SERVER["QUERY_STRING"]	= replace_query(array("sb" => false));
			} else {
				if (!isset($_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["sb"])) {
					$_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["sb"] = "date";
				}
			}

			/**
			 * Update requested order to sort by.
			 * Valid: asc, desc
			 */
			if (isset($_GET["so"])) {
				$_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["so"] = ((strtolower($_GET["so"]) == "desc") ? "desc" : "asc");

				$_SERVER["QUERY_STRING"]	= replace_query(array("so" => false));
			} else {
				if (!isset($_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["so"])) {
					$_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["so"] = "desc";
				}
			}

			/**
			 * Update requsted number of rows per page.
			 * Valid: any integer really.
			 */
			if ((isset($_GET["pp"])) && ((int) trim($_GET["pp"]))) {
				$integer = (int) trim($_GET["pp"]);

				if (($integer > 0) && ($integer <= 250)) {
					$_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["pp"] = $integer;
				}

				$_SERVER["QUERY_STRING"] = replace_query(array("pp" => false));
			} else {
				if (!isset($_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["pp"])) {
					$_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["pp"] = 15;
				}
			}

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

			/**
			 * Get the total number of results using the generated queries above and calculate the total number
			 * of pages that are available based on the results per page preferences.
			 */
			$query	= "
					SELECT COUNT(*) AS `total_rows`
					FROM `community_share_files` AS a
					LEFT JOIN `community_shares` AS c
					ON a.`cshare_id` = c.`cshare_id`
					WHERE a.`cshare_id` = ".$db->qstr($RECORD_ID)."
					AND a.`community_id` = ".$db->qstr($COMMUNITY_ID)."
					AND c.`cpage_id` = ".$db->qstr($PAGE_ID)."
					AND a.`file_active` = '1'
					".((!$LOGGED_IN) ? " AND c.`allow_public_read` = '1'" : (($COMMUNITY_MEMBER) ? ((!$COMMUNITY_ADMIN) ? " AND c.`allow_member_read` = '1'" : "") : " AND c.`allow_troll_read` = '1'"))."
					".((!$COMMUNITY_ADMIN) ? ($LOGGED_IN ? " AND ((a.`proxy_id` = ".$db->qstr($ENTRADA_USER->getActiveId()).") OR " : " AND (")."(a.`release_date` = '0' OR a.`release_date` <= ".$db->qstr(time()).") AND (a.`release_until` = '0' OR a.`release_until` > ".$db->qstr(time())."))" : "");
			$result	= $db->GetRow($query);
			if ($result) {
				$TOTAL_ROWS	= $result["total_rows"];

				if ($TOTAL_ROWS <= $_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["pp"]) {
					$TOTAL_PAGES = 1;
				} elseif (($TOTAL_ROWS % $_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["pp"]) == 0) {
					$TOTAL_PAGES = (int) ($TOTAL_ROWS / $_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["pp"]);
				} else {
					$TOTAL_PAGES = (int) ($TOTAL_ROWS / $_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["pp"]) + 1;
				}

				if ($TOTAL_PAGES > 1) {
					$pagination = new Pagination($PAGE_CURRENT, $_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["pp"], $TOTAL_ROWS, COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL, replace_query());
				}
			} else {
				$TOTAL_ROWS		= 0;
				$TOTAL_PAGES	= 1;
			}

			/**
			 * Check if pv variable is set and see if it's a valid page, other wise page 1 it is.
			 */
			if (isset($_GET["pv"])) {
				$PAGE_CURRENT = (int) trim($_GET["pv"]);

				if (($PAGE_CURRENT < 1) || ($PAGE_CURRENT > $TOTAL_PAGES)) {
					$PAGE_CURRENT = 1;
				}
			} else {
				$PAGE_CURRENT = 1;
			}

			$PAGE_PREVIOUS	= (($PAGE_CURRENT > 1) ? ($PAGE_CURRENT - 1) : false);
			$PAGE_NEXT		= (($PAGE_CURRENT < $TOTAL_PAGES) ? ($PAGE_CURRENT + 1) : false);

			/**
			 * Provides the first parameter of MySQLs LIMIT statement by calculating which row to start results from.
			 */
			$limit_parameter = (int) (($_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["pp"] * $PAGE_CURRENT) - $_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["pp"]);

			$community_shares_select = community_shares_in_select($folder_record["cshare_id"]);
			?>
			<script type="text/javascript">
				function fileDelete(id) {
					Dialog.confirm('Do you really wish to remove the '+ $('file-' + id + '-title').innerHTML +' file?<br /><br />If you confirm this action, you will be deactivating this file and any comments.',
						{
							id:				'requestDialog',
							width:			350,
							height:			125,
							title:			'Delete Confirmation',
							className:		'medtech',
							okLabel:		'Yes',
							cancelLabel:	'No',
							closable:		'true',
							buttonClass:	'button small',
							ok:				function(win) {
												window.location = '<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?section=delete-file&id='+id;
												return true;
											}
						}
					);
				}

				<?php if ($community_shares_select != "") : ?>
				function fileMove(id) {
					Dialog.confirm('Do you really wish to move the '+ $('file-' + id + '-title').innerHTML +' file?<br /><br />If you confirm this action, you will be moving the file and all comments to the selected folder.<br /><br /><?php echo $community_shares_select; ?>',
						{
							id:				'requestDialog',
							width:			350,
							height:			165,
							title:			'Move File',
							className:		'medtech',
							okLabel:		'Yes',
							cancelLabel:	'No',
							closable:		'true',
							buttonClass:	'button small',
							ok:				function(win) {
												window.location = '<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?section=move-file&id='+id+'&share_id='+$F('share_id');
												return true;
											}
						}
					);
				}
				<?php endif; ?>
			</script>
			<h1><?php echo html_encode($folder_record["folder_title"]); ?></h1>
			<div style="margin-bottom: 15px">
				<?php echo nl2br(html_encode($folder_record["folder_description"])); ?>
			</div>
			<div id="module-header">
				<?php
				if ($TOTAL_PAGES > 1) {
					echo "<div id=\"pagination-links\">\n";
					echo "Pages: ".$pagination->GetPageLinks();
					echo "</div>\n";
				}
				?>
			</div>
			<div style="padding-top: 10px; clear: both">
				<?php if (COMMUNITY_NOTIFICATIONS_ACTIVE && $LOGGED_IN && $_SESSION["details"]["notifications"]) { ?>
					<div id="notifications-toggle" style="position: absolute; padding-top: 14px;"></div>
					<script type="text/javascript">
					function promptNotifications(enabled) {
						Dialog.confirm('Do you really wish to '+ (enabled == 1 ? "stop" : "begin") +' receiving notifications for new files on this page?',
							{
								id:				'requestDialog',
								width:			350,
								height:			75,
								title:			'Notification Confirmation',
								className:		'medtech',
								okLabel:		'Yes',
								cancelLabel:	'No',
								closable:		'true',
								buttonClass:	'button small',
								destroyOnClose:	true,
								ok:				function(win) {
													new Window(	{
																	id:				'resultDialog',
																	width:			350,
																	height:			75,
																	title:			'Notification Result',
																	className:		'medtech',
																	okLabel:		'close',
																	buttonClass:	'button small',
																	resizable:		false,
																	draggable:		false,
																	minimizable:	false,
																	maximizable:	false,
																	recenterAuto:	true,
																	destroyOnClose:	true,
																	url:			'<?php echo ENTRADA_URL."/api/notifications.api.php?community_id=".$COMMUNITY_ID."&id=".$RECORD_ID; ?>&type=file&action=edit&active='+(enabled == 1 ? '0' : '1'),
																	onClose:			function () {
																						new Ajax.Updater('notifications-toggle', '<?php echo ENTRADA_URL."/api/notifications.api.php?community_id=".$COMMUNITY_ID."&id=".$RECORD_ID; ?>&type=file&action=view');
																					}
																}
													).showCenter();
													return true;
												}
							}
						);
					}
					
					</script>
					<?php
					$ONLOAD[] = "new Ajax.Updater('notifications-toggle', '".ENTRADA_URL."/api/notifications.api.php?community_id=".$COMMUNITY_ID."&id=".$RECORD_ID."&type=file&action=view')";
				}
				if (shares_module_access($RECORD_ID, "add-file")) {
					?>
					<div style="float: right; padding-top: 10px;">
						<ul class="page-action">
							<li><a href="<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?section=add-file&id=<?php echo $RECORD_ID; ?>">Upload File</a></li>
						</ul>
					</div>
					<div style="clear: both"></div>
					<?php
				}
				
				$query		= "	SELECT a.*, CONCAT_WS(' ', b.`firstname`, b.`lastname`) AS `owner`, b.`username` AS `owner_username`
								FROM `community_share_files` AS a
								LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
								ON a.`proxy_id` = b.`id`
								LEFT JOIN `community_shares` AS c
								ON a.`cshare_id` = c.`cshare_id`
								WHERE a.`cshare_id` = ".$db->qstr($RECORD_ID)."
								AND a.`community_id` = ".$db->qstr($COMMUNITY_ID)."
								AND a.`file_active` = '1'
								".((!$LOGGED_IN) ? " AND c.`allow_public_read` = '1'" : (($COMMUNITY_MEMBER) ? ((!$COMMUNITY_ADMIN) ? " AND c.`allow_member_read` = '1'" : "") : " AND c.`allow_troll_read` = '1'"))."
								".((!$COMMUNITY_ADMIN) ? ($LOGGED_IN ? " AND ((a.`proxy_id` = ".$db->qstr($ENTRADA_USER->getActiveId()).") OR " : " AND (")."(a.`release_date` = '0' OR a.`release_date` <= ".$db->qstr(time()).") AND (a.`release_until` = '0' OR a.`release_until` > ".$db->qstr(time())."))" : "")."
								ORDER BY %s
								LIMIT %s, %s";
				$query		= sprintf($query, $SORT_BY, $limit_parameter, $_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["pp"]);
				$results	= $db->GetAll($query);
				if ($results) {
					?>
					<table class="discussions forums" style="width: 100%" cellspacing="0" cellpadding="0" border="0">
					<colgroup>
						<col style="width: 4%" />
						<col style="width: 55%" />
						<col style="width: 20%" />
						<col style="width: 21%" />
					</colgroup>
					<thead>
						<tr>
							<td>&nbsp;</td>
							<td<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["sb"] == "title") ? " class=\"sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["so"])."\"" : ""); ?> style="border-left: none"><?php echo communities_order_link("title", "File Title"); ?></td>
							<td<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["sb"] == "owner") ? " class=\"sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["so"])."\"" : ""); ?> style="border-left: none"><?php echo communities_order_link("owner", "Owner"); ?></td>
							<td<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["sb"] == "date") ? " class=\"sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["cid_".$COMMUNITY_ID][$PAGE_URL]["so"])."\"" : ""); ?> style="border-left: none"><?php echo communities_order_link("date", "Last Updated"); ?></td>
						</tr>
					</thead>
					<tbody>
					<?php
					foreach($results as $key => $result) {
						$accessible	= true;
						$parts		= pathinfo($result["file_title"]);
						$ext		= $parts["extension"];

						if ((($result["release_date"]) && ($result["release_date"] > time())) || (($result["release_until"]) && ($result["release_until"] < time()))) {
							$accessible = false;
						}

						echo "<tr".((!$accessible) ? " class=\"na\"" : "").">\n";
						echo "  <td style=\"vertical-align: top\">";
						echo "		<a href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-file&id=".$result["csfile_id"]."&download=latest\"><img src=\"".ENTRADA_URL."/community/templates/default/images/btn_save.gif\" alt=\"Download File\" title=\"Download File\"width=\"15\" border=\"0\" /></a>";
						echo "	</td>";
						echo "	<td style=\"vertical-align: top\">\n";
						echo "		<img src=\"".ENTRADA_URL."/serve-icon.php?ext=".$ext."\" width=\"16\" height=\"16\" alt=\"".strtoupper($ext)." Document\" title=\"".strtoupper($ext)." Document\" style=\"vertical-align: middle; margin-right: 4px\" /> <a id=\"file-".(int) $result["csfile_id"]."-title\" href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-file&amp;id=".$result["csfile_id"]."\" style=\"font-weight: bold; vertical-align: middle\">".limit_chars(html_encode($result["file_title"]), 50, true)."</a>\n";
						echo "		<div class=\"content-small\" style=\"padding-left: 23px\">";
						echo 		((shares_file_module_access($result["csfile_id"], "edit-file")) ? " <span style=\"vertical-align: middle\">(<a class=\"action\" href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=edit-file&amp;id=".$result["csfile_id"]."\">edit</a>)</span>" : "");
						echo 		((shares_file_module_access($result["csfile_id"], "delete-file")) ? " <span style=\"vertical-align: middle\">(<a class=\"action\" href=\"javascript:fileDelete('".$result["csfile_id"]."')\">delete</a>)</span>" : "");
						if ($community_shares_select != "") {
							echo 	((shares_file_module_access($result["csfile_id"], "move-file")) ? " <span style=\"vertical-align: middle\">(<a class=\"action\" href=\"javascript:fileMove('".$result["csfile_id"]."')\">move</a>)</span>" : "");
						}
						echo "		<span style=\"vertical-align: middle\">".html_encode(limit_chars($result["file_description"], 125))."</span>";
						echo "		</div>\n";
						echo "	</td>\n";
						echo "	<td style=\"font-size: 10px; white-space: nowrap; overflow: hidden\"><a href=\"".ENTRADA_URL."/people?profile=".html_encode($result["owner_username"])."\" style=\"font-size: 10px\">".html_encode($result["owner"])."</a></td>\n";
						echo "	<td style=\"font-size: 10px; white-space: nowrap; overflow: hidden\">".date(DEFAULT_DATE_FORMAT, $result["updated_date"])."</td>\n";
						echo "</tr>\n";
					}
					?>
					</tbody>
					</table>
					<?php
				} else {
					$NOTICE++;
					$NOTICESTR[] = "<strong>No files in this shared folder.</strong><br /><br />".((shares_module_access($RECORD_ID, "add-file")) ? "If you would like to upload a new file, <a href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=add-file&id=".$RECORD_ID."\">click here</a>." : "Please check back later.");

					echo display_notice();
				}
				?>
			</div>
			<?php
			if ($LOGGED_IN) {
				add_statistic("community:".$COMMUNITY_ID.":shares", "folder_view", "cshare_id", $RECORD_ID);
			}
		} else {
			if ($ERROR) {
				echo display_error();
			}
			if ($NOTICE) {
				echo display_notice();
			}
		}
	} else {
		application_log("error", "The provided shared folder id was invalid [".$RECORD_ID."] (View Folder).");

		header("Location: ".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL);
		exit;
	}
} else {
	application_log("error", "No shared folder id was provided to view. (View Folder)");

	header("Location: ".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL);
	exit;
}
?>