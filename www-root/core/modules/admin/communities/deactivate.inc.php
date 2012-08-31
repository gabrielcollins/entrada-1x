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
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Josh Dillon <jdillon@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/
if (!defined("IN_COMMUNITIES")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("communityadmin", "delete", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$GROUP."] and role [".$ROLE."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/communities?".replace_query(array("section" => "deactivate", "step" => false)), "title" => "Deactivate Communities");
	switch ($STEP) {
		case 2 :
			if ((is_array($_POST["checked"])) && (count($_POST["checked"]))) {
				foreach ($_POST["checked"] as $selected_community) {
					if ($selected_community = (int) $selected_community) {
						$query = " UPDATE `communities` SET `community_active` = 0
								   WHERE `community_id`= ". $db->qstr($selected_community);
						if (!$db->Execute($query)) {
							$ERROR++;
							$ERRORSTR[] = "Failed to deactivate selected Communities.";	
						} 
					}
				}
				$url = $url = ENTRADA_URL."/admin/communities";
				$msg = "Selected communities were successfully deactivated. You will now be redirected to the <strong>Manage Communities</strong> page, this will happen <strong>automatically</strong> in 5 seconds or <a href=\"" . $url . "\" style=\"font-weight: bold\">click here</a> to continue.";
				$SUCCESS++;
				$SUCCESSSTR[] = $msg;
				$ONLOAD[] = "setTimeout('window.location=\\'" . $url . "\\'', 5000)";
			} else {
				$ERROR++;
				$ERRORSTR[] = "You must select at least 1 community to deactivate by checking the checkbox to the left of the community name.";
			}
			if ($ERROR) {
				$STEP = 1;
			}
		break;
		case 1 :
		default :
			if ((is_array($_POST["checked"])) && (count($_POST["checked"]))) {
				$selected_communities = array();
				foreach ($_POST["checked"] as $deactivate_community_id) {
					if ($deactivate_community_id = (int) $deactivate_community_id) {
						$selected_communities[] = $deactivate_community_id;
					}
				}
			} else {
				$NOTICE++;
				$NOTICESTR[] = "<h3>No Communities Selected</h3><br />There are currently no communities selected to deactivate.";
				echo display_notice();
			}
		break;
	}
	
	switch ($STEP) {
		case 2 :
			if ($SUCCESS) {
				echo display_success();
			}
			if ($NOTICE) {
				echo display_notice();
			}
			if ($ERROR) {
				echo display_error();
			}
		break;
		case 1 :
		default :
			if (is_array($selected_communities)) { ?>
				<h1>Deactivate Communities</h1>
				<?php
				if ($ERROR) {
					echo display_error();
				}
				?>
				<div class="display-notice" style="margin-top: 15px; line-height: 175%">
					<strong>Please note</strong> that once you deactivate these communities all of the content (photos, calendar, etc) within the communities will no longer be accessible to you or any other members of the communities. Deactivating these communities will also deactivate any Sub-Communities / Groups that have been created under these communities.
				</div>
				<form action="<?php echo ENTRADA_URL; ?>/admin/communities?<?php echo replace_query(array("section" => "deactivate", "step" => 2)); ?>" method="post">
					<table class="tableList" cellspacing="0" cellpadding="1" summary="List of communities">
						<colgroup>
							<col class="modified" />
							<col class="title" />
							<col class="title" />
							<col class="date" />
						</colgroup>
						<thead>
							<tr>
								<td class="modified" style="width: 20px;">&nbsp;</td>
								<td class="title">Community Title</td>
								<td class="title">Category</td>
								<td class="date">Creation Date</td>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="4">&nbsp;</td>
							</tr>
							<tr>
								<td><input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/communities'" /></td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td style="padding-top: 10px; text-align: right;"><input type="submit" class="button" value="Deactivate Now" /></td>
							</tr>
						</tfoot>
						<tbody>
						<?php 
						foreach ($selected_communities as $selected_community) {
							$query = "SELECT a.`community_id`, a.`category_id`, a.`community_title`, a.`community_opened`, b.`category_id`, b.`category_title` 
									  FROM `communities` AS a
									  LEFT JOIN `communities_categories` AS b
									  ON a.`category_id` = b.`category_id`
									  WHERE a.`community_id` =". $db->qstr($selected_community)."
									  GROUP BY ". $db->qstr($sort_by);
							$results = $db->GetAll($query);
							if ($results) {
								foreach ($results as $result) {
									$url = ENTRADA_URL."/communities?section=modify&community=".$result["community_id"];
									echo "<tr id=\"community-".$result["community_id"]."\">\n";
									echo "	<td class=\"modified\"><input type=\"checkbox\" name=\"checked[]\" value=\"".$result["community_id"]."\"". ((in_array($result["community_id"], $selected_communities)) ? " checked=\"checked\"" : "") . "/></td>\n";
									echo "	<td class=\"title\"><a href=\"".$url."\">".html_encode($result["community_title"])."</a></td>\n";
									echo "	<td class=\"title\"><a href=\"".$url."\">".html_encode($result["category_title"])."</a></td>\n";
									echo "	<td class=\"date\"><a href=\"".$url."\">".date(DEFAULT_DATE_FORMAT, $result["community_opened"])."</a></td>\n";
									echo "</tr>\n";
								}
							}
						}
						?>
						</tbody>
					</table>
				</form>
			<?php
			} else {
				echo display_error();
			}
		break;
	}
}
?>