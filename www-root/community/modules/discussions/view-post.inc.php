<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Used to view a particular discussion forum as well as any additional replies
 * that were received for this post.
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

if ($RECORD_ID) {	
	$query			= "
					SELECT a.*, b.`forum_title`, CONCAT_WS(' ', c.`firstname`, c.`lastname`) AS `poster_fullname`, c.`username` AS `poster_username`
					FROM `community_discussion_topics` AS a
					LEFT JOIN `community_discussions` AS b
					ON a.`cdiscussion_id` = b.`cdiscussion_id`
					LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS c
					ON a.`proxy_id` = c.`id`
					WHERE a.`proxy_id` = c.`id`
					AND a.`community_id` = ".$db->qstr($COMMUNITY_ID)."
					AND b.`cpage_id` = ".$db->qstr($PAGE_ID)." 
					AND a.`cdtopic_id` = ".$db->qstr($RECORD_ID)."
					AND a.`cdtopic_parent` = '0'
					AND a.`topic_active` = '1'
					AND b.`forum_active` = '1'";
	$topic_record	= $db->GetRow($query);
	if ($topic_record) {
		if (discussion_topic_module_access($RECORD_ID, "view-post")) {
			$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-forum&id=".$topic_record["cdiscussion_id"], "title" => limit_chars($topic_record["forum_title"], 32));
			$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-post&id=".$RECORD_ID, "title" => limit_chars($topic_record["topic_title"], 32));

			$POST_REPLY = discussion_topic_module_access($RECORD_ID, "reply-post");
			?>
			<script type="text/javascript">
			function postDelete(id, type) {
				if (type && type == 'post') {
					var message = 'Do you really wish to deactivate the '+ $('post-<?php echo $RECORD_ID; ?>-title').innerHTML +' discussion post?<br /><br />If you confirm this action, you will be deactivating this discussion post and any replies.';
				} else {
					var message = 'Do you really wish to deactivate this reply to '+ $('post-<?php echo $RECORD_ID; ?>-title').innerHTML +'?';
				}

				Dialog.confirm(message,
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
											window.location = '<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?section=delete-post&id='+id;
											return true;
										}
					}
				);
			}
			</script>
			<?php
			/**
			 * If there is time release properties, display them to the browsing users.
			 */
			if (($release_date = (int) $topic_record["release_date"]) && ($release_date > time())) {
				$NOTICE++;
				$NOTICESTR[] = "This discussion post will not be accessible to others until <strong>".date(DEFAULT_DATE_FORMAT, $release_date)."</strong>.";
			} elseif ($release_until = (int) $topic_record["release_until"]) {
				if ($release_until > time()) {
					$NOTICE++;
					$NOTICESTR[] = "This discussion post will be accessible until <strong>".date(DEFAULT_DATE_FORMAT, $release_until)."</strong>.";
				} else {
					/**
					 * Only administrators or people who wrote the post will get this.
					 */
					$NOTICE++;
					$NOTICESTR[] = "This discussion post was only accessible until <strong>".date(DEFAULT_DATE_FORMAT, $release_until)."</strong> by others.";
				}
			}

			if ($NOTICE) {
				echo display_notice();
			}
			?>
			<a name="top"></a>
			<div id="post-<?php echo $RECORD_ID; ?>" style="padding-top: 10px; clear: both">
				<table class="discussions posts" style="width: 100%" cellspacing="0" cellpadding="0" border="0">
				<colgroup>
					<col style="width: 30%" />
					<col style="width: 70%" />
				</colgroup>
				<thead>
					<tr>
						<td colspan="2"><div id="post-<?php echo $RECORD_ID; ?>-title"><?php echo html_encode($topic_record["topic_title"]); ?></div></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="border-bottom: none; border-right: none"><span class="content-small">By:</span> <?php if(defined('COMMUNITY_DISCUSSIONS_ANON') && COMMUNITY_DISCUSSIONS_ANON && isset($topic_record["anonymous"]) && $topic_record["anonymous"] && !$COMMUNITY_ADMIN){?><span style="font-size: 10px">Anonymous</span><?php } else {?><a href="<?php echo ENTRADA_URL."/people?profile=".html_encode($topic_record["poster_username"]); ?>" style="font-size: 10px"><?php echo html_encode($topic_record["poster_fullname"]); ?></a><?php } ?></td>
						<td style="border-bottom: none">
							<div style="float: left">
								<span class="content-small"><strong>Posted:</strong> <?php echo date(DEFAULT_DATE_FORMAT, $topic_record["updated_date"]); ?></span>
							</div>
							<div style="float: right">
							<?php
							echo ((discussion_topic_module_access($RECORD_ID, "edit-post")) ? " (<a class=\"action\" href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=edit-post&amp;id=".$RECORD_ID."\">edit</a>)" : "");
							echo ((discussion_topic_module_access($RECORD_ID, "delete-post")) ? " (<a class=\"action\" href=\"javascript:postDelete('".$RECORD_ID."', 'post')\">delete</a>)" : "");
							?>
							</div>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="content">
							<?php echo $topic_record["topic_description"]; ?>
						</td>
					</tr>
				</tbody>
				</table>
				<?php
				$query		= "
							SELECT a.*, CONCAT_WS(' ', b.`firstname`, b.`lastname`) AS `poster_fullname`, b.`username` AS `poster_username`
							FROM `community_discussion_topics` AS a
							LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
							ON a.`proxy_id` = b.`id`
							WHERE a.`proxy_id` = b.`id`
							AND a.`community_id` = ".$db->qstr($COMMUNITY_ID)."
							AND a.`cdtopic_parent` = ".$db->qstr($RECORD_ID)."
							AND a.`topic_active` = '1'
							ORDER BY a.`release_date` ASC";
				$results	= $db->GetAll($query);
				$replies	= 0;
				if ($results) {
					if ($POST_REPLY) {
						?>
						<ul class="page-action">
							<li><a href="<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?section=reply-post&id=<?php echo $RECORD_ID; ?>">Reply To Post</a></li>
						</ul>
						<?php
					}
					?>
					<h2 style="margin-bottom: 0px"><?php echo html_encode($topic_record["topic_title"]); ?> Replies</h2>
					<table class="discussions posts" style="width: 100%" cellspacing="0" cellpadding="0" border="0">
					<colgroup>
						<col style="width: 30%" />
						<col style="width: 70%" />
					</colgroup>
					<tbody>
					<?php
					foreach($results as $result) {
						$replies++;
						?>
						<tr>
							<td style="border-bottom: none; border-right: none"><span class="content-small">By:</span> <?php if(defined('COMMUNITY_DISCUSSIONS_ANON') && COMMUNITY_DISCUSSIONS_ANON && isset($result["anonymous"]) && $result["anonymous"] && !$COMMUNITY_ADMIN){?><span style="font-size: 10px">Anonymous</span><?php } else {?><a href="<?php echo ENTRADA_URL."/people?profile=".html_encode($result["poster_username"]); ?>" style="font-size: 10px"><?php echo html_encode($result["poster_fullname"]); ?></a><?php } ?></td>
							<td style="border-bottom: none">
								<div style="float: left">
									<span class="content-small"><strong>Replied:</strong> <?php echo date(DEFAULT_DATE_FORMAT, $result["updated_date"]); ?></span>
								</div>
								<div style="float: right">
								<?php
								echo ((discussion_topic_module_access($result["cdtopic_id"], "edit-post")) ? " (<a class=\"action\" href=\"".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=edit-post&amp;id=".$result["cdtopic_id"]."\">edit</a>)" : "");
								echo ((discussion_topic_module_access($result["cdtopic_id"], "delete-post")) ? " (<a class=\"action\" href=\"javascript:postDelete('".$result["cdtopic_id"]."', 'reply')\">delete</a>)" : "");
								?>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="2" class="content">
							<a name="post-<?php echo (int) $result["cdtopic_id"]; ?>"></a>
							<?php
								echo $result["topic_description"];

								if ($result["release_date"] != $result["updated_date"]) {
									echo "<div class=\"content-small\" style=\"margin-top: 15px\">\n";
									echo "	<strong>Last updated:</strong> ".date(DEFAULT_DATE_FORMAT, $result["updated_date"])." by ".(($result["proxy_id"] == $result["updated_by"]) ? html_encode($result["poster_fullname"]) : html_encode(get_account_data("firstlast", $result["updated_by"]))).".";
									echo "</div>\n";
								}
							?>
							</td>
						</tr>
						<?php
					}
					?>
					</tbody>
					</table>
					<?php
				}
				if ($POST_REPLY) {
					?>
					<ul class="page-action">
						<li><a href="<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?section=reply-post&id=<?php echo $RECORD_ID; ?>">Reply To Post</a></li>
						<li class="top"><a href="#top">Top Of Page</a></li>
					</ul>
					<?php
				}
				?>
			</div>
			<?php
			if ($LOGGED_IN) {
				add_statistic("community:".$COMMUNITY_ID.":discussions", "post_view", "cdtopic_id", $RECORD_ID);
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
		application_log("error", "The provided discussion post id was invalid [".$RECORD_ID."] (View Post).");

		header("Location: ".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL);
		exit;
	}
} else {
	application_log("error", "No discussion post id was provided to view. (View Post)");

	header("Location: ".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL);
	exit;
}
?>