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
 * This file is used to delete objectives from the entrada.global_lu_objectives table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer:James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if((!defined("PARENT_INCLUDED")) || (!defined("IN_OBJECTIVES"))) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->amIAllowed('objective', 'delete', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/settings/organisations/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$objective_ids	= array();
	
	$BREADCRUMB[]	= array("url" => "", "title" => "Delete Objectives");

	echo "<h1>Delete Objectives</h1>\n";

	// Error Checking
	switch($STEP) {
		case 2 :
		case 1 :
		default :
			if (isset($_POST["delete"]) && count($_POST["delete"]) && ($tmp_input = $_POST["delete"])) {
				$objective_ids_string = "";
				foreach ($tmp_input as $objective) {
					if ((int)$objective["objective_id"]) {
						$objective["objective_id"] = clean_input($objective["objective_id"], "int");
					}
					$query	= "SELECT a.* FROM `global_lu_objectives` AS a
								JOIN `objective_organisation` AS b
								ON a.`objective_id` = b.`objective_id`
								WHERE a.`objective_id` = ".$db->qstr($objective["objective_id"])."
								AND b.`organisation_id` = ".$db->qstr($ORGANISATION_ID)."
								AND a.`objective_active` = '1'";
					$result	= $db->GetRow($query);
					if ($result) {
						if (((int)$result["objective_active"]) == 0) {
							$ERROR++;
							$ERRORSTR[] = "The objective [".html_encode($result["objective_name"])."] you have tried to delete does not exist.";
						} else {
							$objectives[]	= array("objective_id" => $objective["objective_id"],
													"objective_children_target" => ($objective["move"] ? $objective["objective_parent"] : false),
													"objective_order" => $result["objective_order"],
													"objective_parent" => $result["objective_parent"]);
							$objective_ids_string .= ($objective_ids_string ? ",".$objective["objective_id"] : $objective["objective_id"]);
						}
					}
				}
			}
			if (!count($objectives)) {
				header("Location: ".ENTRADA_URL."/admin/settings/organisations/manage/objectives");
				exit;
			}
		break;
	}
	
	// Display Page
	switch($STEP) {
		case 2 :
			$success_count = 0;
			$moved_count = 0;
			$deleted_count = 0;
			foreach ($objectives as $objective) {
				if (objectives_delete_for_org($ORGANISATION_ID,$objective["objective_id"], $objective["objective_children_target"])) {
					$query				= "	SELECT `objective_id`, `objective_order` 
											FROM `global_lu_objectives` 
											WHERE `objective_parent` = ".$db->qstr($objective["objective_parent"])." 
											AND `objective_active` = '1'
											AND `objective_order` > ".$db->qstr($objective["objective_order"]);
					$moving_siblings	= $db->GetAll($query);
					if ($moving_siblings) {
						foreach($moving_siblings as $moving_sibling) {
							$query = "	UPDATE `global_lu_objectives` 
										SET `objective_order` = ".$db->qstr($moving_sibling["objective_order"] - 1)." 
										WHERE `objective_id` = ".$db->qstr($moving_sibling["objective_id"])."
										AND `objective_active` = '1'";
							$db->Execute($query);
						}
					}
					if ($objective["objective_children_target"] !== false) {
						$query				= "	SELECT `objective_id`, `objective_name` 
												FROM `global_lu_objectives` 
												WHERE `objective_parent` = ".$db->qstr($objective["objective_id"])."
												AND `objective_active` = '1'";
						$moving_children	= $db->GetAll($query);
						if ($moving_children) {
							$query = "	SELECT MAX(`objective_order`)
										FROM `global_lu_objectives`
										WHERE `objective_active` = '1'
										GROUP BY `objective_parent`
										HAVING `objective_parent` = ".$db->qstr($objective["objective_children_target"]);
							$count = $db->GetOne($query);
							if (!$count) {
								$count = 0;
							}
							$moved = true;
							foreach($moving_children as $moving_child) {
								$count++;
								$query = "	UPDATE `global_lu_objectives` 
											SET `objective_order` = ".$db->qstr($count).", 
											`objective_parent` = ".$db->qstr($objective["objective_children_target"])." 
											WHERE `objective_id` = ".$db->qstr($moving_child["objective_id"])."
											AND `objective_active` = '1'";
								if (!$db->Execute($query)) {
									$moved = false;
									$ERROR++;
									$ERRORSTR[] = "There was a problem trying to place the child Objective [".html_encode($moving_children["objective_name"])."] under another parent.";
									application_log("error", "There was an issue while trying to move an objective [".$moving_child["objective_id"]."] under a new parent. Database said: ".$db->ErrorMsg());
								} else {
									$moved_count++;
								}
							}
						}
					}
					$success_count++;
				} else {
					$ERROR++;
					$ERRORSTR[] = "A problem occurred while attempting to delete a selected Objective [".html_encode($moving_children["objective_name"])."].";
					application_log("error", "There was an issue while trying to move an objective [".$moving_child["objective_id"]."] under a new parent. Database said: ".$db->ErrorMsg());
				}
			}
			if ($ERROR) {
				echo display_error();
			}
			if ($NOTICE) {
				echo display_notice();
			}
			if ($success_count) {
				$url = ENTRADA_URL."/admin/settings/organisations/manage/objectives?org=".$ORGANISATION_ID;
				$SUCCESS++;
				$SUCCESSSTR[] = "You have successfully deactivated ".$success_count." objectives from the system.".($moved_count && $deleted_count ? " <br /><br />Additionally, ".$moved_count." of these objectives' children were placed under a new parent and ".$deleted_count." of the objectives' children were deactivated along with their parent objective." : ($moved_count && !$deleted_count ? " <br /><br />Additionally, ".$moved_count." of these objectives' children were placed under a new parent." : ($deleted_count ? " <br /><br />Additionally, ".$deleted_count." of these objectives' children were deactivated along with under a new parent." : "")))."<br /><br />You will now be redirected to the index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
				$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";
				
				application_log("success", "Objectives successfully deactivated in the system.");
			}
			if ($SUCCESS) {
				echo display_success();
			}
		break;
		case 1 :
		default :
			if ($ERROR) {
				echo display_error();
			} else {
				echo display_notice(array("Please review the following objective or objectives to ensure that you wish to permanently delete them."));
				$HEAD[]	= "	<script type=\"text/javascript\">
								function selectObjective(parent_id, objective_id, excluded_objectives) {
									new Ajax.Updater('selectParent'+objective_id+'Field', '".ENTRADA_URL."/api/objectives-list.api.php', {parameters: {'pid': parent_id, 'id': objective_id, 'excluded': excluded_objectives}});
									return;
								}
								function selectOrder(parent_id, objective_id) {
									return;
								}
							</script>";
				?>
				<form action="<?php echo ENTRADA_URL."/admin/settings/organisations/manage/objectives?".replace_query(array("action" => "delete", "step" => 2)); ?>" method="post">
				<table class="tableList" cellspacing="0" summary="List of objectives to be removed">
				<colgroup>
					<col class="modified" />
					<col class="title" />
				</colgroup>
				<thead>
					<tr>
						<td class="modified">&nbsp;</td>
						<td class="title">Objectives</td>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td>&nbsp;</td>
						<td style="padding-top: 10px">
							<input type="submit" class="button" value="Delete Selected" />
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php
				foreach ($objectives as $objective) {
					echo objectives_intable($ORGANISATION_ID, $objective["objective_id"], 0, $objective_ids_string);
				}
				?>
				</tbody>
				</table>
				</form>
				<?php
			}
		break;
	}
}
?>