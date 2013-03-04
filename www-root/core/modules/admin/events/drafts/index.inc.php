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
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <ryan.warner@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVENTS"))) {
	exit;
} else if ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
		header("Location: ".ENTRADA_URL);
		exit;
} else if (!$ENTRADA_ACL->amIAllowed('event', 'create', false)) {
	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/events/drafts", "title" => "My Draft Learning Event Schedules");
	$JQUERY[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/jquery/jquery.dataTables.min.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
	?>
	<style type="text/css">
		#draft-list_length {padding:5px 4px 0 0;}
		#draft-list_filter {-moz-border-radius:10px 10px 0px 0px; border: 1px solid #9D9D9D;border-bottom:none;background-color:#FAFAFA;font-size: 0.9em;padding:3px;}
		#draft-list_paginate a {margin:2px 5px;}
	</style>
	<script type="text/javascript">
		jQuery(function(){
			jQuery(".noLink").live("click", function(){
				return false;
			});

			jQuery('#draft-list').dataTable({
				"aaSorting": [[ 2, "asc" ]]
			});
		});
	</script>
	<h1>My Draft Learning Event Schedules</h1>
	<div style="float: right">
		<ul class="page-action">
			<li class="last"><a class="strong-green" href="<?php echo ENTRADA_URL; ?>/admin/events/drafts?section=create-draft">Create New Draft</a></li>
		</ul>
	</div>
	<div style="clear: both"></div>
	<?php
	// fetch the list of draft ids for the user
	$query = "	SELECT a.*
                FROM `drafts` AS a
                JOIN `draft_creators` AS b
                ON b.`draft_id` = a.`draft_id`
				WHERE b.`proxy_id` = ".$db->qstr($ENTRADA_USER->getActiveId())."
                AND a.`status` <> 'closed'";
	$drafts = $db->GetArray($query);
	if ($drafts) {
		add_notice("<p>You currently have <strong>".count($drafts)."</strong> draft".((count($drafts) > 1) ? "s" : "")." on the go.</p><p>When you are finished working on your draft event schedule you can click <strong>Publish Drafts</strong> to schedule them to be imported.</p>");
		echo display_notice();
		?>
			<form name="frmSelect" id="frmSelect" action="<?php echo ENTRADA_URL; ?>/admin/events/drafts?section=delete" method="post">
			<table class="tableList" id="draft-list" cellspacing="0" cellpadding="1" summary="List of Events">
				<colgroup>
					<col class="modified" />
					<col class="title" />
					<col class="date" />
					<col class="status" />
				</colgroup>
				<thead>
					<tr>
						<td class="modified">&nbsp;</td>
						<td class="title"><a href="#" class="noLink">Draft Name</a></td>
						<td class="date"><a href="#" class="noLink">Created</a></td>
						<td class="status"><a href="#" class="noLink">Status</a></td>
					</tr>
				</thead>
				<tbody>
				<?php

				$count_modified = 0;

				foreach ($drafts as $draft) {
					echo "<tr id=\"draft-".$draft["draft_id"]."\" rel=\"".$draft["draft_id"]."\" class=\"draft".((!$url) ? " np" : ((!$accessible) ? " na" : ""))."\">\n";
					echo "	<td class=\"modified\"><input type=\"checkbox\" name=\"checked[]\" value=\"".$draft["draft_id"]."\" /></td>\n";
					echo "	<td class=\"title\">".(($draft["status"] == "open") ? "<a href=\"".$url."?section=edit&draft_id=".$draft["draft_id"]."\" title=\"Draft Name\">" : "") .$draft["name"].(($draft["status"] == "open") ? "</a>" : "" )."</td>\n";
					echo "	<td class=\"date\">".(($draft["status"] == "open") ? "<a href=\"".$url."?section=edit&draft_id=".$draft["draft_id"]."\" title=\"Duration\">" : "").date("Y-m-d", $draft["created"]).(($draft["status"] == "open") ? "</a>" : "")."</td>\n";
					echo "	<td class=\"status\">".(($draft["status"] == "open") ? "<a href=\"".$url."?section=edit&draft_id=".$draft["draft_id"]."\" title=\"Draft Status\">" : "").$draft["status"].(($draft["status"] == "open") ? "</a>" : "")."</td>\n";
					echo "</tr>\n";
				}
				?>
				</tbody>
			</table>
			<?php if ($ENTRADA_ACL->amIAllowed("event", "delete", false) or $ENTRADA_ACL->amIAllowed("event", "create", false)) : ?>
			<table width="100%">
				<tfoot>
					<tr>
						<td></td>
						<td style="padding-top: 10px" colspan="2">
							<?php
							if ($ENTRADA_ACL->amIAllowed("event", "delete", false)) {
								?>
								<input type="submit" class="button" value="Delete Selected" />
								<?php
							} ?>
						</td>
						<td style="padding-top: 10px; text-align: right" colspan="1">
							<?php
							if ($ENTRADA_ACL->amIAllowed("event", "delete", false)) {
								?>
								<input type="submit" class="button" value="Reopen Drafts" onclick="jQuery('#frmSelect').attr('action', '<?php echo ENTRADA_URL; ?>/admin/events/drafts?section=status&action=reopen')" />
								<input type="submit" class="button" value="Publish Drafts" onclick="jQuery('#frmSelect').attr('action', '<?php echo ENTRADA_URL; ?>/admin/events/drafts?section=status&action=approve')" />
								<?php
							}
							?>
						</td>
					</tr>
				</tfoot>
			</table>
			<?php endif; ?>
			</form>

		<?php
	} else {
		?>
        <div class="display-generic">
            <h3>No draft schedules</h3>
            <p>You are not currently working on any draft schedules. You can create a new draft schedule or have an administrator add you to an existing one.</p>
        </div>
        <?php
	}
}