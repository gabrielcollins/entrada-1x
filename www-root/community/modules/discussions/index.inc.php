<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Used to list the available discussion forums within this particular page
 * in a community.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
*/

if ((!defined("COMMUNITY_INCLUDED")) || (!defined("IN_DISCUSSIONS"))) {
	exit;
} elseif (!$COMMUNITY_LOAD) {
	exit;
}

/**
 * Add the javascript for deleting forums.
 */
if (communities_module_access($COMMUNITY_ID, $MODULE_ID, "delete-forum")) {
	?>
	<script type="text/javascript">
		function discussionDelete(id) {
			Dialog.confirm('Do you really wish to remove the '+ $('forum-' + id + '-title').innerHTML +' forum from this community?<br /><br />If you confirm this action, you will be deactivating this forum and all posts within it.',
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
										window.location = '<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?section=delete-forum&id='+id;
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
	<a href="<?php echo COMMUNITY_URL."/feeds".$COMMUNITY_URL.":".$PAGE_URL."/rss"; ?>" class="feeds rss">Subscribe to RSS</a>
</div>

<div style="padding-top: 10px; clear: both">
	<?php
	if (communities_module_access($COMMUNITY_ID, $MODULE_ID, "add-forum")) {
		?>
		<div style="float: right">
			<ul class="page-action">
				<li><a href="<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?section=add-forum">Add Discussion Forum</a></li>
			</ul>
		</div>
		<div style="clear: both"></div>
		<?php
	}

	$query		= "	SELECT a.*
					FROM `community_discussions` AS a
					WHERE a.`community_id` = ".$db->qstr($COMMUNITY_ID)."
					AND a.`forum_active` = '1'
					".((!$LOGGED_IN) ? " AND a.`allow_public_read` = '1'" : (($COMMUNITY_MEMBER) ? ((!$COMMUNITY_ADMIN) ? " AND a.`allow_member_read` = '1'" : "") : " AND a.`allow_troll_read` = '1'"))."
					".((!$COMMUNITY_ADMIN) ? " AND (a.`release_date` = '0' OR a.`release_date` <= ".$db->qstr(time()).") AND (a.`release_until` = '0' OR a.`release_until` > ".$db->qstr(time()).")" : "")."
					AND a.`cpage_id` = ".$db->qstr($PAGE_ID)."
					ORDER BY a.`forum_order` ASC, a.`forum_title` ASC";
	$results	= $db->GetAll($query);
	if ($results) {
		?>
		<table class="discussions" style="width: 100%" cellspacing="0" cellpadding="0" border="0">
		<colgroup>
			<col style="width: 50%" />
			<col style="width: 10%" />
			<col style="width: 10%" />
			<col style="width: 30%" />
		</colgroup>
		<thead>
			<tr>
				<td>Forum Title</td>
				<td style="border-left: none">Posts</td>
				<td style="border-left: none">Replies</td>
				<td style="border-left: none">Latest Post</td>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach($results as $result) {
				$accessible	= true;
				$topics		= communities_discussions_latest($result["cdiscussion_id"]);

				if ((($result["release_date"]) && ($result["release_date"] > time())) || (($result["release_until"]) && ($result["release_until"] < time()))) {
					$accessible = false;
				}

				echo "<tr".((!$accessible) ? " class=\"na\"" : "").">\n";
				echo "	<td>\n";
				echo "		<a id=\"forum-".(int) $result["cdiscussion_id"]."-title\" href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-forum&amp;id=".$result["cdiscussion_id"]."\" style=\"font-weight: bold\">".html_encode($result["forum_title"])."</a>\n";
				echo		((communities_module_access($COMMUNITY_ID, $MODULE_ID, "edit-forum")) ? " (<a class=\"action\" href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=edit-forum&amp;id=".$result["cdiscussion_id"]."\">edit</a>)" : "");
				echo 		((communities_module_access($COMMUNITY_ID, $MODULE_ID, "delete-forum")) ? " (<a class=\"action\" href=\"javascript:discussionDelete('".$result["cdiscussion_id"]."')\">delete</a>)" : "");
				echo "		<div class=\"content-small\">".html_encode(limit_chars($result["forum_description"], 125))."</div>\n";
				echo "	</td>\n";
				echo "	<td class=\"center\">".$topics["posts"]."</td>\n";
				echo "	<td class=\"center\">".$topics["replies"]."</td>\n";
				echo "	<td class=\"small\">\n";
				if ((int) $topics["posts"]) {
					if(defined('COMMUNITY_DISCUSSIONS_ANON') && COMMUNITY_DISCUSSIONS_ANON && !$COMMUNITY_ADMIN && isset($topics["anonymous"]) && $topics["anonymous"]){
						$display = "Anonymous";
					} else {
						$display = '<a href="'.ENTRADA_URL.'/people?profile='.html_encode($topics["username"]).'" style="font-size: 10px">'.html_encode($topics["fullname"]).'</a>';						
					}										
					echo "	<strong>Time:</strong> ".date("M d Y, g:ia", $topics["updated_date"])."<br />\n";
					echo "	<strong>Topic:</strong> <a href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-post&amp;id=".$topics["cdtopic_id"]."\">".limit_chars(html_encode($topics["topic_title"]), 25, true)."</a><br />\n";
					echo "	<strong>By:</strong>".$display."\n";
				} else {
					echo "	No topics in this forum.\n";
				}
				echo "	</td>\n";
				echo "</tr>\n";
			}
			?>
		</tbody>
		</table>
		<?php
	} else {
		$NOTICE++;
		$NOTICESTR[] = "There are currently no forums available in this community.<br /><br />".((communities_module_access($COMMUNITY_ID, $MODULE_ID, "add-forum")) ? "As a community adminstrator you can add forums by clicking <a href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=add-forum\">Add Discussion Forum</a>." : "Please check back later.");

		echo display_notice();
	}
	?>
</div>