<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Used to list any available folders under the specific page_id.
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

/**
 * Add the javascript for deleting forums.
 */
if (communities_module_access($COMMUNITY_ID, $MODULE_ID, "delete-folder")) {
	?>
	<script type="text/javascript">
		function folderDelete(id) {
			Dialog.confirm('Do you really wish to remove the '+ $('folder-' + id + '-title').innerHTML +' folder from this community?<br /><br />If you confirm this action, you will be deactivating the folder and all files within it.',
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
										window.location = '<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?section=delete-folder&id='+id;
										return true;
									}
				}
			);
		}
	</script>
	<?php
}
?>
<div id="module-header">
</div>

<div style="padding-top: 10px; clear: both">
	<?php
	if (communities_module_access($COMMUNITY_ID, $MODULE_ID, "add-folder")) {
		?>
		<div style="float: right">
			<ul class="page-action">
				<li><a href="<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?section=add-folder">Add Shared Folder</a></li>
			</ul>
		</div>
		<div style="clear: both"></div>
		<?php
	}
	
	$query		= "	SELECT a.*
					FROM `community_shares` AS a
					WHERE a.`community_id` = ".$db->qstr($COMMUNITY_ID)."
					AND a.`folder_active` = '1'
					".((!$LOGGED_IN) ? " AND a.`allow_public_read` = '1'" : (($COMMUNITY_MEMBER) ? ((!$COMMUNITY_ADMIN) ? " AND (a.`allow_member_read` = '1' OR a.`allow_member_upload` = '1')" : "") : " AND (a.`allow_troll_read` = '1' OR a.`allow_troll_upload` = '1')"))."
					".((!$COMMUNITY_ADMIN) ? " AND (a.`release_date` = '0' OR a.`release_date` <= ".$db->qstr(time()).") AND (a.`release_until` = '0' OR a.`release_until` > ".$db->qstr(time()).")" : "")."
					AND a.`cpage_id` = ".$db->qstr($PAGE_ID)."
					ORDER BY a.`folder_order` ASC, a.`folder_title` ASC";
	$results	= $db->GetAll($query);
	if ($results) {
		$total_folders	= @count($results);
		$column			= 0;
		?>
		<ul class="shares">
		<?php
			foreach($results as $progress => $result) {

				if ($LOGGED_IN) {
					$query = "SELECT * FROM `community_share_files` WHERE `proxy_id` = ".$db->qstr($ENTRADA_USER->getActiveId())." AND `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `cshare_id` = ".$db->qstr($result["cshare_id"])." AND `file_active` = 1 ORDER BY updated_date LIMIT 1";
					$file_uploaded = $db->GetRow($query);
				} else {
					$file_uploaded = false;
				}
				$accessible	= true;
				$files		= communities_shares_latest($result["cshare_id"]);

				if ((($result["release_date"]) && ($result["release_date"] > time())) || (($result["release_until"]) && ($result["release_until"] < time()))) {
					$accessible = false;
				}

				echo "<li class=\"folder-".(((int) $result["folder_icon"]) ? (int) $result["folder_icon"] : 1)."\">\n";
				echo "	<div".((!$accessible) ? " class=\"na\" style=\"padding: 4px\"" : "").">\n";
				echo "		".( (($COMMUNITY_MEMBER) ? ((!$COMMUNITY_ADMIN) ? $result["allow_member_upload"] == 1 && !$result["allow_member_read"] == 1 : false ) : $result["allow_troll_upload"] == 1 && !$result["allow_troll_read"] == 1 ) ? "<span id=\"folder-".(int) $result["cshare_id"]."-title\" style=\"font-weight: bold\">".html_encode($result["folder_title"])."</span>\n" : "<a id=\"folder-".(int) $result["cshare_id"]."-title\" href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-folder&amp;id=".$result["cshare_id"]."\" style=\"font-weight: bold\">".html_encode(limit_chars($result["folder_title"],58))."</a>\n");
				echo "		<span class=\"content-small\">(".$files["total_files"]." files)</span>";
				/** 
				 * "?section=add-file&amp;id=".$result["cshare_id"]
				 * "?section=edit-file&amp;id=".$file_uploaded["csfile_id"]
				 * ( $file_uploaded != false ? "" : "")
				*/
				echo 		( (($COMMUNITY_MEMBER) ? ((!$COMMUNITY_ADMIN) ? $result["allow_member_upload"] == 1 : true ) : $result["allow_troll_upload"] == 1 ) 
								? " (<a class=\"action\" href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL.
								($file_uploaded == true && !$COMMUNITY_ADMIN && !($COMMUNITY_MEMBER ? $result["allow_member_read"] : $result["allow_troll_read"])
									? "?section=add-revision&amp;id=".$file_uploaded["csfile_id"] 
									: "?section=add-file&amp;id=".$result["cshare_id"])."\">upload</a>)" 
								: "");
				echo		((communities_module_access($COMMUNITY_ID, $MODULE_ID, "edit-folder")) ? " (<a class=\"action\" href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=edit-folder&amp;id=".$result["cshare_id"]."\">edit</a>)" : "");
				echo 		((communities_module_access($COMMUNITY_ID, $MODULE_ID, "delete-folder")) ? " (<a class=\"action\" href=\"javascript:folderDelete('".$result["cshare_id"]."')\">delete</a>)" : "");
				echo "		<div class=\"content-small\">".(($result["folder_description"] != "") ? html_encode(limit_chars($result["folder_description"], 125)) : "")."</div>\n";
				echo "	</div>\n";
				echo "</li>\n";
			}
			?>
		</ul>
		<?php
	} else {
		$NOTICE++;
		$NOTICESTR[] = "There are currently no shared folders available in this community.<br /><br />".((communities_module_access($COMMUNITY_ID, $MODULE_ID, "add-folder")) ? "As a community adminstrator you can add shared folders by clicking <a href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=add-folder\">Add Shared Folder</a>." : "Please check back later.");

		echo display_notice();
	}
	?>
</div>