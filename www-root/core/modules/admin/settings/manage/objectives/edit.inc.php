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
 * This file is used to edit objectives in the entrada.global_lu_objectives table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @author Developer: Ryan Warner <ryan.warner@queensu.ca>
 * @copyright Copyright 2013 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_OBJECTIVES"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('objective', 'update', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/settings/manage/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	
	if (isset($_GET["id"]) && ($id = clean_input($_GET["id"], array("notags", "trim")))) {
		$OBJECTIVE_ID = $id;
	}
	
	if (isset($_GET["mode"]) && $_GET["mode"] == "ajax") {
		$MODE = "ajax";
	}
	
	if ($OBJECTIVE_ID) {
		
		$query = "	SELECT a.*, GROUP_CONCAT(c.`audience_value`) AS `audience` FROM `global_lu_objectives` AS a
					JOIN `objective_organisation` AS b
					ON a.`objective_id` = b.`objective_id`
					LEFT JOIN `objective_audience` AS c
					ON a.`objective_id` = c.`objective_id`
					AND b.`organisation_id` = c.`organisation_id`
					WHERE a.`objective_id` = ".$db->qstr($OBJECTIVE_ID)."
					AND b.`organisation_id` = ".$db->qstr($ORGANISATION_ID)."
					AND a.`objective_active` = '1'";					
		$objective_details	= $db->GetRow($query);
		if ($MODE == "ajax") {
			ob_clear_open_buffers();
			$time = time();
			
			$method = clean_input($_POST["method"], array("trim", "striptags"));

			switch ($method) {
				case "link-objective" :
					
					$PROCESSED["objective_id"] = $OBJECTIVE_ID;
						
					if ($_GET["target_objective_id"] && $tmp_input = clean_input($_GET["target_objective_id"], "int")) {
						$PROCESSED["target_objective_id"] = $tmp_input;
					} else {
						add_error("Invalid target objective ID provided.");
					}
					
					if (!$ERROR) {
						if ($_POST["action"] == "link") {
							if ($db->AutoExecute("linked_objectives", $PROCESSED, "INSERT")) {
								$query = "SELECT `objective_id` AS `target_objective_id`, `objective_name`, `objective_description` FROM `global_lu_objectives` WHERE `objective_id` = ".$db->qstr($PROCESSED["target_objective_id"]);
								$result = $db->GetRow($query);
								if ($result) {
									$parent = fetch_objective_parents($PROCESSED["target_objective_id"]);
									$result["action"] = "link";
									$result["parent_objective"] = $parent["parent"]["objective_name"];
									echo json_encode(array("status" => "success", "data" => $result));
								}
							}
						} else {
							$query = "DELETE FROM `linked_objectives` WHERE `objective_id` = " . $db->qstr($PROCESSED["objective_id"]) . " AND `target_objective_id` = ".$db->qstr($PROCESSED["target_objective_id"]);
							if ($db->Execute($query)) {
								echo json_encode(array("status" => "success", "data" => array("action" => "unlink", "target_objective_id" => $PROCESSED["target_objective_id"])));
							}
						}
					}
										
				break;
				case "fetch-linked-objectives" :
					
					echo "<h1>".$objective_details["objective_name"]."</h1>";
					echo (!empty($objective_details["objective_description"]) ? "<p>".$objective_details["objective_description"]."</p>" : "");
					if (isset($_POST["objective_set_id"]) && $tmp_input = clean_input($_POST["objective_set_id"], "int")) {
						$PROCESSED["objective_set_id"] = $tmp_input;
					}
					
					$query = "	SELECT a.`objective_id`, a.`objective_description`, a.`objective_name`, b.`linked_objective_id`
								FROM `global_lu_objectives` AS a
								JOIN `linked_objectives` AS b
								ON b.`target_objective_id` = a.`objective_id`
								WHERE b.`objective_id` = ".$db->qstr($OBJECTIVE_ID)."
								AND b.`active` = '1'";
					$linked_objectives = $db->GetAll($query);
					
					echo "<h2>Currently Linked Objectives</h2>\n";
					echo "<ul id=\"currently-linked-objectives\">";
					if ($linked_objectives) {
						foreach ($linked_objectives as $objective) {
							$parent = fetch_objective_parents($objective["objective_id"]);
							echo "<li data-id=\"" . $objective["objective_id"] . "\"><strong>".$objective["objective_name"]."</strong><a href=\"#\" class=\"unlink\"><i class=\"icon-trash\"></i></a>".($parent["parent"]["objective_name"] ? "<br /><small class=\"content-small\">From ".$parent["parent"]["objective_name"]."</small>" : "")."".(!empty($objective["objective_description"]) ? "<br />".$objective["objective_description"] : "")."</li>";
						}
					} else {
						echo "<li class=\"no-objectives\">This objective is not currently linked to any other objectives.</li>";
					}
					echo "</ul>";

					$query = "	SELECT a.* FROM `global_lu_objectives` a
								JOIN `objective_audience` b
								ON a.`objective_id` = b.`objective_id`
								AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
								WHERE a.`objective_parent` = '0'
								AND a.`objective_active` = '1'
								AND a.`objective_id` != ".$db->qstr($PROCESSED["objective_set_id"])."
								GROUP BY a.`objective_id`";
					$objectives = $db->GetAll($query);
					if ($objectives) {
						$objective_name = $translate->_("events_filter_controls");
						$hierarchical_name = $objective_name["co"]["global_lu_objectives_name"];
						?>
							<h2>Objectives Available to Link</h2>
							<ul id="linked-objective-list" class="objective-list">
								<?php foreach ($objectives as $objective) { ?>
								<li>
									<a href="#" class="objective" data-id="<?php echo $objective["objective_id"];?>">
									<?php $title = ($objective["objective_code"] ? $objective["objective_code"] . ': ' . $objective["objective_name"] : $objective["objective_name"]);
										  echo $title; ?>
									</a><i class="icon-chevron-down"></i>
									<div class="children"></div>
								</li>
								<?php } ?>
							</ul>
						<?php
					}
				break;
				default:
				
					if ($objective_details["objective_parent"] != 0) {

						switch ($STEP) {
							case "2" :
								/**
								* Required field "objective_name" / Objective Name
								*/
								if (isset($_POST["objective_name"]) && ($objective_name = clean_input($_POST["objective_name"], array("notags", "trim")))) {
									$PROCESSED["objective_name"] = $objective_name;
								} else {
									$ERROR++;
									$ERRORSTR[] = "The <strong>Objective Name</strong> is a required field.";
								}

								/**
								* Non-required field "objective_code" / Objective Code
								*/
								if (isset($_POST["objective_code"]) && ($objective_code = clean_input($_POST["objective_code"], array("notags", "trim")))) {
									$PROCESSED["objective_code"] = $objective_code;
								} else {
									$PROCESSED["objective_code"] = "";
								}

								/**
								* Non-required field "objective_parent" / Objective Parent
								*/
								if (isset($_POST["objective_id"]) && ($objective_parent = clean_input($_POST["objective_id"], array("int")))) {
									$PROCESSED["objective_parent"] = $objective_parent;
								} else {
									$PROCESSED["objective_parent"] = 0;
								}

								/**
								* Non-required field "objective_description" / Objective Description
								*/
								if (isset($_POST["objective_description"]) && ($objective_description = clean_input($_POST["objective_description"], array("notags", "trim")))) {
									$PROCESSED["objective_description"] = $objective_description;
								} else {
									$PROCESSED["objective_description"] = "";
								}
				
                                /**
                                * Non-required field "objective_loggable" / Objective Loggable
                                */
                                if (isset($_POST["objective_loggable"]) && $_POST["objective_loggable"]) {
                                    $PROCESSED["objective_loggable"] = 1;
                                } else {
                                    $PROCESSED["objective_loggable"] = 0;
                                }

								/**
								* Required field "objective_order" / Objective Order
								*/
								if (isset($_POST["objective_order"]) && ($objective_order = clean_input($_POST["objective_order"], array("int"))) && $objective_order != "-1") {
									$PROCESSED["objective_order"] = clean_input($_POST["objective_order"], array("int")) - 1;
								} else if($objective_order == "-1") {
									$PROCESSED["objective_order"] = $objective_details["objective_order"];
								} else {
									$PROCESSED["objective_order"] = 0;
								}

								if (!$ERROR) {
									if ($objective_details["objective_order"] != $PROCESSED["objective_order"]) {
										$query = "SELECT a.`objective_id` FROM `global_lu_objectives` AS a
													LEFT JOIN `objective_organisation` AS b
													ON a.`objective_id` = b.`objective_id`
													WHERE a.`objective_parent` = ".$db->qstr($PROCESSED["objective_parent"])."
													AND (b.`organisation_id` = ".$db->qstr($ORGANISATION_ID)." OR b.`organisation_id` IS NULL)
													AND a.`objective_id` != ".$db->qstr($OBJECTIVE_ID)./*"
													AND a.`objective_order` >= ".$db->qstr($PROCESSED["objective_order"]).*/"
													AND a.`objective_active` = '1'
													ORDER BY a.`objective_order` ASC";
										$objectives = $db->GetAll($query);
										if ($objectives) {
											$count = 0;
											foreach ($objectives as $objective) {
												if($count === $PROCESSED["objective_order"]) {
													$count++;
												}
												if (!$db->AutoExecute("global_lu_objectives", array("objective_order" => $count), "UPDATE", "`objective_id` = ".$db->qstr($objective["objective_id"]))) {
													$ERROR++;
													$ERRORSTR[] = "There was a problem updating this objective in the system. The system administrator was informed of this error; please try again later.";

													application_log("error", "There was an error updating an objective. Database said: ".$db->ErrorMsg());
												}
												$count++;
											}
										}
									}
								}

								if (!$ERROR) {

									$PROCESSED["updated_date"] = time();
									$PROCESSED["updated_by"] = $ENTRADA_USER->getID();

									if (!$db->AutoExecute("global_lu_objectives", $PROCESSED, "UPDATE", "`objective_id` = ".$db->qstr($OBJECTIVE_ID))) {

										echo json_encode(array("status" => "error", "msg" => "There was a problem updating this objective in the system. The system administrator was informed of this error; please try again later."));

										application_log("error", "There was an error updating an objective. Database said: ".$db->ErrorMsg());
									} else {
										$PROCESSED["objective_id"] = $OBJECTIVE_ID;
										echo json_encode(array("status" => "success", "updates" => $PROCESSED));
									}

								} else {
									echo json_encode(array("status" => "error", "msg" => "Name is required"));
								}
							break;
							case "1" :
							default :
								?>
								<script type="text/javascript">
								function selectObjective(parent_id, objective_id) {
									new Ajax.Updater('m_selectObjectiveField_<?php echo $time; ?>', '<?php echo ENTRADA_URL; ?>/api/objectives-list.api.php', {parameters: {'pid': parent_id, 'id': objective_id, 'organisation_id': <?php echo $ORGANISATION_ID; ?>}});
									return;
								}
								function selectOrder(objective_id, parent_id) {
									new Ajax.Updater('m_selectOrderField_<?php echo $time; ?>', '<?php echo ENTRADA_URL; ?>/api/objectives-list.api.php', {parameters: {'id': objective_id, 'type': 'order', 'pid': parent_id, 'organisation_id': <?php echo $ORGANISATION_ID; ?>}});
									return;
								}
								jQuery(function(){
									selectObjective(<?php echo (isset($objective_details["objective_parent"]) && $objective_details["objective_parent"] ? $objective_details["objective_parent"] : "0"); ?>, <?php echo $OBJECTIVE_ID; ?>);
									selectOrder(<?php echo $OBJECTIVE_ID; ?>, <?php echo (isset($objective_details["objective_parent"]) && $objective_details["objective_parent"] ? $objective_details["objective_parent"] : "0"); ?>);
								});
								</script>

								<form id="objective-form" action="<?php echo ENTRADA_URL."/admin/settings/manage/objectives"."?".replace_query(array("action" => "edit", "step" => 2, "mode" => "ajax")); ?>" method="post" style="margin-bottom:0px!important;">
									<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Adding Page">
									<colgroup>
										<col style="width: 30%" />
										<col style="width: 70%" />
									</colgroup>
									<thead>
										<tr>
											<td colspan="2"><h2>Objective <?php echo ($objective_details["objective_parent"] == 0) ? "Set " : ""; ?>Details</h2></td>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td><label for="objective_code" class="form-nrequired">Objective Code:</label></td>
											<td><input type="text" id="objective_code" name="objective_code" value="<?php echo ((isset($objective_details["objective_code"])) ? html_encode($objective_details["objective_code"]) : ""); ?>" maxlength="100" style="width: 300px" /></td>
										</tr>
										<tr>
											<td><label for="objective_name" class="form-required">Objective Name:</label></td>
											<td><input type="text" id="objective_name" name="objective_name" value="<?php echo ((isset($objective_details["objective_name"])) ? html_encode($objective_details["objective_name"]) : ""); ?>" maxlength="60" style="width: 300px" /></td>
										</tr>
										<tr>
											<td colspan="2">&nbsp;</td>
										</tr>
										<tr>
											<td style="vertical-align: top"><label for="objective_description" class="form-nrequired">Objective Description: </label></td>
											<td>
												<textarea id="objective_description" name="objective_description" style="width: 98%; height: 200px" rows="20" cols="70"><?php echo ((isset($objective_details["objective_description"])) ? html_encode($objective_details["objective_description"]) : ""); ?></textarea>
											</td>
										</tr>
										<tr>
											<td colspan="2">&nbsp;</td>
										</tr>
										<tr>
											<td style="vertical-align: top; padding-top: 15px"><label for="objective_loggable" class="form-nrequired">Objective Loggable:</label></td>
											<td style="vertical-align: top"><input type="checkbox" id="objective_loggable" name="objective_loggable" value="1"<?php echo (isset($objective_details["objective_loggable"]) && $objective_details["objective_loggable"] ? " checked=\"checked\"" : ""); ?> /></td>
										</tr>
										<tr>
											<td colspan="2">&nbsp;</td>
										</tr>
										<tr>
											<td style="vertical-align: top; padding-top: 15px"><label for="objective_id" class="form-required">Objective Parent:</label></td>
											<td style="vertical-align: top"><div id="m_selectObjectiveField_<?php echo $time; ?>"></div></td>
										</tr>
										<tr>
											<td colspan="2">&nbsp;</td>
										</tr>
										<tr>
											<td style="vertical-align: top"><label for="objective_id" class="form-required">Objective Order:</label></td>
											<td style="vertical-align: top"><div id="m_selectOrderField_<?php echo $time; ?>"></div></td>
										</tr>
										<tr>
											<td colspan="2"><div class="alert alert-block alert-error hide" id="objective_error" style="margin-top:10px!important;margin-bottom:0px!important;"></div></td>
										</tr>								
									</tbody>
									</table>
								</form>
								<?php
							break;
						}
					}
				break;
			}
			
			exit;
		} else {
			/**
			* Fetch all courses into an array that will be used.
			*/
			$query = "SELECT * FROM `courses` WHERE `organisation_id` = ".$ENTRADA_USER->getActiveOrganisation()." ORDER BY `course_code` ASC";
			$courses = $db->GetAll($query);
			if ($courses) {
				foreach ($courses as $course) {
					$course_list[$course["course_id"]] = array("code" => $course["course_code"], "name" => $course["course_name"]);
				}
			}

			if ($objective_details) {
				$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/settings/manage/objectives?".replace_query(array("section" => "edit")), "title" => "Editing Objective Set");

				// Error Checking
				switch ($STEP) {
					case 2:
						/**
						* Required field "objective_name" / Objective Name
						*/
						if (isset($_POST["objective_name"]) && ($objective_name = clean_input($_POST["objective_name"], array("notags", "trim")))) {
							$PROCESSED["objective_name"] = $objective_name;
						} else {
							$ERROR++;
							$ERRORSTR[] = "The <strong>Objective Name</strong> is a required field.";
						}

						/**
						* Non-required field "objective_code" / Objective Code
						*/
						if (isset($_POST["objective_code"]) && ($objective_code = clean_input($_POST["objective_code"], array("notags", "trim")))) {
							$PROCESSED["objective_code"] = $objective_code;
						} else {
							$PROCESSED["objective_code"] = "";
						}

						/**
						* Non-required field "objective_parent" / Objective Parent
						*/
						if (isset($_POST["objective_id"]) && ($objective_parent = clean_input($_POST["objective_id"], array("int")))) {
							$PROCESSED["objective_parent"] = $objective_parent;
						} else {
							$PROCESSED["objective_parent"] = 0;
						}

						/**
						* Required field "objective_order" / Objective Order
						*/
						if (isset($_POST["objective_order"]) && ($objective_order = clean_input($_POST["objective_order"], array("int"))) && $objective_order != "-1") {
							$PROCESSED["objective_order"] = clean_input($_POST["objective_order"], array("int")) - 1;
						} else if($objective_order == "-1") {
							$PROCESSED["objective_order"] = $objective_details["objective_order"];
						} else {
							$PROCESSED["objective_order"] = 0;
						}

						/**
						* Non-required field "objective_description" / Objective Description
						*/
						if (isset($_POST["objective_description"]) && ($objective_description = clean_input($_POST["objective_description"], array("notags", "trim")))) {
							$PROCESSED["objective_description"] = $objective_description;
						} else {
							$PROCESSED["objective_description"] = "";
						}

						if ($objective_details["objective_parent"] == 0 || $PROCESSED["objective_parent"] == 0) {
							/*
							* Non-required field "objective_audience"
							*/
							if (isset($_POST["objective_audience"]) && $tmp_input = clean_input($_POST["objective_audience"], array("notags", "trim")) && ($tmp_input == "all" || $tmp_input == "none" || "selected")) {
								$PROCESSED["objective_audience"] = clean_input($_POST["objective_audience"], array("notags", "trim"));
							} else {
								$PROCESSED["objective_audience"] = "all";
							}

							/*
							* Non-required field "course_ids"
							*/
							if (isset($_POST["course_ids"]) && isset($PROCESSED["objective_audience"]) == "selected") {
								foreach ($_POST["course_ids"] as $course_id) {
									if (array_key_exists($course_id, $course_list)) {
										$PROCESSED["course_ids"][] = clean_input($course_id, "numeric") ;
									}
								}
								if (empty($PROCESSED["course_ids"])) {
									$PROCESSED["objective_audience"] = "none";
								}
							}
						}

//						if (!$ERROR) {
//							if ($objective_details["objective_order"] != $PROCESSED["objective_order"]) {
//								$query = "SELECT a.`objective_id` FROM `global_lu_objectives` AS a
//											JOIN `objective_organisation` AS b
//											ON a.`objective_id` = b.`objective_id`
//											WHERE a.`objective_parent` = ".$db->qstr($PROCESSED["objective_parent"])."
//											AND b.`organisation_id` = ".$db->qstr($ORGANISATION_ID)."
//											AND a.`objective_id` != ".$db->qstr($OBJECTIVE_ID)."
//											AND a.`objective_order` >= ".$db->qstr($PROCESSED["objective_order"])."
//											AND a.`objective_active` = '1'
//											ORDER BY a.`objective_order` ASC";
//								$objectives = $db->GetAll($query);
//								if ($objectives) {
//									$count = $PROCESSED["objective_order"];
//									foreach ($objectives as $objective) {
//										$count++;
//										if (!$db->AutoExecute("global_lu_objectives", array("objective_order" => $count), "UPDATE", "`objective_id` = ".$db->qstr($objective["objective_id"]))) {
//											$ERROR++;
//											$ERRORSTR[] = "There was a problem updating this objective in the system. The system administrator was informed of this error; please try again later.";
//
//											application_log("error", "There was an error updating an objective. Database said: ".$db->ErrorMsg());
//										}
//									}
//								}
//							}
//						}
						
						if (!$ERROR) {
							if ($objective_details["objective_order"] != $PROCESSED["objective_order"]) {
								$query = "SELECT a.`objective_id` FROM `global_lu_objectives` AS a
											LEFT JOIN `objective_organisation` AS b
											ON a.`objective_id` = b.`objective_id`
											WHERE a.`objective_parent` = ".$db->qstr($PROCESSED["objective_parent"])."
											AND (b.`organisation_id` = ".$db->qstr($ORGANISATION_ID)." OR b.`organisation_id` IS NULL)
											AND a.`objective_id` != ".$db->qstr($OBJECTIVE_ID)./*"
											AND a.`objective_order` >= ".$db->qstr($PROCESSED["objective_order"]).*/"
											AND a.`objective_active` = '1'
											ORDER BY a.`objective_order` ASC";
								$objectives = $db->GetAll($query);
								if ($objectives) {
									$count = 0;
									foreach ($objectives as $objective) {
										if($count === $PROCESSED["objective_order"]) {
											$count++;
										}
										if (!$db->AutoExecute("global_lu_objectives", array("objective_order" => $count), "UPDATE", "`objective_id` = ".$db->qstr($objective["objective_id"]))) {
											$ERROR++;
											$ERRORSTR[] = "There was a problem updating this objective in the system. The system administrator was informed of this error; please try again later.";

											application_log("error", "There was an error updating an objective. Database said: ".$db->ErrorMsg());
										}
										$count++;
									}
								}
							}
						}

						if (!$ERROR) {
							$PROCESSED["updated_date"] = time();
							$PROCESSED["updated_by"] = $ENTRADA_USER->getID();

							if ($db->AutoExecute("global_lu_objectives", $PROCESSED, "UPDATE", "`objective_id` = ".$db->qstr($OBJECTIVE_ID))) {

								$query = "DELETE FROM `objective_audience` WHERE `objective_id` = ".$db->qstr($OBJECTIVE_ID);
								if ($db->Execute($query)) {
									if ($objective_details["objective_parent"] == 0 || $PROCESSED["objective_parent"] == 0) {
										if ($PROCESSED["objective_audience"] == "all" || $PROCESSED["objective_audience"] == "none") {
											$query = "	INSERT INTO `objective_audience` (`objective_id`, `organisation_id`, `audience_type`, `audience_value`, `updated_date`, `updated_by`)
														VALUES(".$db->qstr($OBJECTIVE_ID).", ".$db->qstr($ORGANISATION_ID).", ".$db->qstr("course").", ".$db->qstr($PROCESSED["objective_audience"]).", ".$db->qstr(time()).", ".$db->qstr($ENTRADA_USER->getID()).")";
											if (!$db->Execute($query)) {
												add_error("An error occurred while updating objective audience.");
											}
										} else if ($PROCESSED["objective_audience"] == "selected" && is_array($PROCESSED["course_ids"]) && !empty($PROCESSED["course_ids"])) {
											foreach ($PROCESSED["course_ids"] as $course => $course_id) {
												$query = "	INSERT INTO `objective_audience` (`objective_id`, `organisation_id`, `audience_type`, `audience_value`, `updated_date`, `updated_by`)
															VALUES(".$db->qstr($OBJECTIVE_ID).", ".$db->qstr($ORGANISATION_ID).", ".$db->qstr("course").", ".$db->qstr($course_id).", ".$db->qstr(time()).", ".$db->qstr($ENTRADA_USER->getID()).")";
												if (!$db->Execute($query)) {
													add_error("An error occurred while updating objective audience.");
												}
											}
										} else {
											$query = "	INSERT INTO `objective_audience` (`objective_id`, `organisation_id`, `audience_type`, `audience_value`, `updated_date`, `updated_by`)
														VALUES(".$db->qstr($OBJECTIVE_ID).", ".$db->qstr($ORGANISATION_ID).", ".$db->qstr("course").", ".$db->qstr("none").", ".$db->qstr(time()).", ".$db->qstr($ENTRADA_USER->getID()).")";
											if (!$db->Execute($query)) {
												add_error("An error occurred while updating objective audience.");
											}
										}
									}
								} 

								if (!$ERROR) {
									$url = ENTRADA_URL . "/admin/settings/manage/objectives?org=".$ORGANISATION_ID;

									$SUCCESS++;
									$SUCCESSSTR[] = "You have successfully updated <strong>".html_encode($PROCESSED["objective_name"])."</strong> in the system.<br /><br />You will now be redirected to the objectives index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";

									$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";

									application_log("success", "Objective [".$OBJECTIVE_ID."] updated in the system.");		
								}
							} else {
								$ERROR++;
								$ERRORSTR[] = "There was a problem updating this objective in the system. The system administrator was informed of this error; please try again later.";

								application_log("error", "There was an error updating an objective. Database said: ".$db->ErrorMsg());
							}
						}

						if ($ERROR) {
							$STEP = 1;
						}
					break;
					case 1:
					default:
						$PROCESSED = $objective_details;
					break;
				}

				//Display Content
				switch ($STEP) {
					case 2:
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
					case 1:
						if ($ERROR) {
							echo display_error();
						}
						if ($objective_details["audience"] != "all" && $objective_details["audience"] != "none" && !empty($objective_details["audience"])) {
							$objetive_audience_courses = explode(",", $objective_details["audience"]);
							if (is_array($objetive_audience_courses)) {
								foreach ($objetive_audience_courses as $course_id) {
									$PROCESSED["course_ids"][] = clean_input($course_id, "numeric");
								}
							} else {
								$PROCESSED["course_ids"][] = clean_input($course_id, "numeric"); 
							}
							$PROCESSED["objective_audience"] = "selected";
						} else {
							$PROCESSED["objective_audience"] = "all";
						}
						$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
						$HEAD[]	= "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/picklist.js\"></script>\n";
						$ONLOAD[]	= "$('courses_list').style.display = 'none'";

						$HEAD[]	= "<script type=\"text/javascript\">
									function selectObjective(parent_id, objective_id) {
										new Ajax.Updater('selectObjectiveField', '".ENTRADA_URL."/api/objectives-list.api.php', {parameters: {'pid': parent_id, 'id': objective_id, 'organisation_id': ".$ORGANISATION_ID."}});
										return;
									}
									function selectOrder(objective_id, parent_id) {
										new Ajax.Updater('selectOrderField', '".ENTRADA_URL."/api/objectives-list.api.php', {parameters: {'id': objective_id, 'type': 'order', 'pid': parent_id, 'organisation_id': ".$ORGANISATION_ID."}});
										return;
									}
									</script>";
						$ONLOAD[] = "selectObjective(".(isset($PROCESSED["objective_parent"]) && $PROCESSED["objective_parent"] ? $PROCESSED["objective_parent"] : "0").", ".$OBJECTIVE_ID.")";
						$ONLOAD[] = "selectOrder(".$OBJECTIVE_ID.", ".(isset($PROCESSED["objective_parent"]) && $PROCESSED["objective_parent"] ? $PROCESSED["objective_parent"] : "0").")";
						?>
						<script type="text/javascript">
							jQuery(function(){
								jQuery("#objective-form").submit(function(){
									jQuery("#PickList").each(function(){
										jQuery("#PickList option").attr("selected", "selected");	
									});
								});
								jQuery("input[name=objective_audience]").click(function(){
									if (jQuery(this).val() == "selected" && !jQuery("#course-selector").is(":visible")) {
										jQuery("#course-selector").show();
									} else if (jQuery("#course-selector").is(":visible")) {
										jQuery("#course-selector").hide();
									}
								});
							});
						</script>
						<h1>Edit Objective Set</h1>
						<form id="objective-form" action="<?php echo ENTRADA_URL."/admin/settings/manage/objectives"."?".replace_query(array("action" => "add", "step" => 2)); ?>" method="post">
						<h2 class="collapsable" title="Objective Set Details Section">Objective <?php echo ($objective_details["objective_parent"] == 0) ? "Set " : ""; ?>Details</h2>
						<div id="objective-set-details-section">
						<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Adding Page">
						<colgroup>
							<col style="width: 30%" />
							<col style="width: 70%" />
						</colgroup>
						<tfoot>
							<tr>
								<td colspan="2" style="padding-top: 15px; text-align: right">
									<input type="button" class="btn" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/settings/manage/objectives?org=<?php echo $ORGANISATION_ID;?>'" />
									<input type="submit" class="btn btn-primary" value="<?php echo $translate->_("global_button_save"); ?>" />                           
								</td>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td><label for="objective_code" class="form-nrequired">Objective Set Code:</label></td>
								<td><input type="text" id="objective_code" name="objective_code" value="<?php echo ((isset($PROCESSED["objective_code"])) ? html_encode($PROCESSED["objective_code"]) : ""); ?>" maxlength="100" style="width: 300px" /></td>
							</tr>
							<tr>
								<td><label for="objective_name" class="form-required">Objective Set Name:</label></td>
								<td><input type="text" id="objective_name" name="objective_name" value="<?php echo ((isset($PROCESSED["objective_name"])) ? html_encode($PROCESSED["objective_name"]) : ""); ?>" maxlength="60" style="width: 300px" /></td>
							</tr>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<tr>
								<td style="vertical-align: top"><label for="objective_description" class="form-nrequired">Objective Set Description: </label></td>
								<td>
									<textarea id="objective_description" name="objective_description" style="width: 98%; height: 200px" rows="20" cols="70"><?php echo ((isset($PROCESSED["objective_description"])) ? html_encode($PROCESSED["objective_description"]) : ""); ?></textarea>
								</td>
							</tr>
							<?php if ($objective_details["objective_parent"] == 0) { ?>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<tr>
								<td style="vertical-align: top"><label for="objective_audience" class="form-required">Objective Set Audience:</label></td>
								<td><input type="radio" name="objective_audience" value="all" <?php echo ($PROCESSED["objective_audience"] == "all" || $objective_details["audience"] == "all") ? "checked=\"checked\"" : ""; ?> /> All Courses<br />
									<input type="radio" name="objective_audience" value="none" <?php echo ($PROCESSED["objective_audience"] == "none" || $objective_details["audience"] == "none") ? "checked=\"checked\"" : ""; ?> /> No Courses<br />
									<input type="radio" name="objective_audience" value="selected" <?php echo ($PROCESSED["objective_audience"] == "selected" || $objective_details["audience"] == "selected") ? "checked=\"checked\"" : ""; ?> /> Selected Courses<br />
								</td>
							</tr>
							<tr id="course-selector" style="<?php echo ($PROCESSED["objective_audience"] == "selected" || $objective_details["audience"] == "selected") ? "" : "display:none;"; ?>">
								<td></td>
								<td>
									<?php
									echo "<h2>Selected Courses</h2>\n";
									echo "<select class=\"multi-picklist\" id=\"PickList\" name=\"course_ids[]\" multiple=\"multiple\" size=\"5\" style=\"width: 100%; margin-bottom: 5px\">\n";
											if ((is_array($PROCESSED["course_ids"])) && (count($PROCESSED["course_ids"]))) {
												foreach ($PROCESSED["course_ids"] as $key => $course_id) {
													echo "<option value=\"".(int) $course_id."\">".html_encode($course_list[$course_id]["code"] . " - " . $course_list[$course_id]["name"])."</option>\n";
												}
											}
									echo "</select>\n";
									echo "<div style=\"float: left; display: inline\">\n";
									echo "	<input type=\"button\" id=\"courses_list_state_btn\" class=\"btn\" value=\"Show List\" onclick=\"toggle_list('courses_list')\" />\n";
									echo "</div>\n";
									echo "<div style=\"float: right; display: inline\">\n";
									echo "	<input type=\"button\" id=\"courses_list_remove_btn\" class=\"btn btn-danger\" onclick=\"delIt()\" value=\"Remove\" />\n";
									echo "	<input type=\"button\" id=\"courses_list_add_btn\" class=\"btn btn-primary\" onclick=\"addIt()\" style=\"display: none\" value=\"Add\" />\n";
									echo "</div>\n";
									echo "<div id=\"courses_list\" style=\"clear: both; padding-top: 3px; display: none\">\n";
									echo "	<h2>Available Courses</h2>\n";
									echo "	<select class=\"multi-picklist\" id=\"SelectList\" name=\"other_courses_list\" multiple=\"multiple\" size=\"15\" style=\"width: 100%\">\n";
											if ((is_array($course_list)) && (count($course_list))) {
												foreach ($course_list as $course_id => $course) {
													if (!is_array($PROCESSED["course_ids"])) {
														$PROCESSED["course_ids"] = array();
													}
													if (!in_array($course_id, $PROCESSED["course_ids"])) {
														echo "<option value=\"".(int) $course_id."\">".html_encode($course_list[$course_id]["code"] . " - " . $course_list[$course_id]["name"])."</option>\n";
													}
												}
											}
									echo "	</select>\n";
									echo "	</div>\n";
									echo "	<script type=\"text/javascript\">\n";
									echo "	\$('PickList').observe('keypress', function(event) {\n";
									echo "		if (event.keyCode == Event.KEY_DELETE) {\n";
									echo "			delIt();\n";
									echo "		}\n";
									echo "	});\n";
									echo "	\$('SelectList').observe('keypress', function(event) {\n";
									echo "	    if (event.keyCode == Event.KEY_RETURN) {\n";
									echo "			addIt();\n";
									echo "		}\n";
									echo "	});\n";
									echo "	</script>\n";
									?>
								</td>
							</tr>
							<?php } ?>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<tr>
								<td style="vertical-align: top"><label for="objective_id" class="form-required">Objective Set Order:</label></td>
								<td style="vertical-align: top"><div id="selectOrderField"></div></td>
							</tr>
						</tbody>
						</table>
						</div>
						</form>
						<br />
						<script type="text/javascript">
							var SITE_URL = "<?php echo ENTRADA_URL;?>";
							var EDITABLE = true;
							var org_id = "<?php echo $ORGANISATION_ID; ?>";
							var objective_set_id = "<?php echo $OBJECTIVE_ID; ?>";
						</script>
						<?php $HEAD[]	= "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/objectives.js?release=".html_encode(APPLICATION_VERSION)."\"></script>"; ?>

						<div>
							<style>
								#objective-link-modal {
									max-height:500px;
									overflow-x:hidden;
									overflow-y:scroll;
								}
								#objective-link-modal .objective-list > li {
									background-image:none;
								}
								.objective-title{
									cursor:pointer;
								}
								.objective-list{
									padding-left:5px;
								}
								#mapped_objectives,#objective_list_0{
									margin-left:0px;
									padding-left: 0px;
								}
								.objectives{
									width:48%;
									float:left;
								}
								.mapped_objectives{
									float:right;
									height:100%;
									width:100%;
								}
								.remove{
									display:block;
									cursor:pointer;
									float:right;
								}
								.draggable{
									cursor:pointer;
								}
								.droppable.hover{
									background-color:#ddd;
								}
								.objective-title{
									font-weight:bold;
								}
								.objective-children{
									margin-top:5px;
								}
								.objective-container{
									position:relative;
									padding-right:0px!important;
									margin-right:0px!important;
								}
								.objective-controls{
									position:absolute;
									top:5px;
									right:0px;
								}
								li.display-notice{
									border:1px #FC0 solid!important;
									padding-top:10px!important;
									text-align:center;
								}
								.hide{
									display:none;
								}
								.objective-controls i {
									display:block;
									width:16px;
									height:16px;
									cursor:pointer;
									float:left;
								}
								.objective-controls .objective-add-control {
									/*background-image:url("<?php echo ENTRADA_URL; ?>/images/add.png");*/
								}
								.objective-controls .objective-edit-control {
									/*background-image:url("<?php echo ENTRADA_URL; ?>/images/edit_list.png");*/								
								}
								.objective-controls .objective-delete-control {
									/*background-image:url("<?php echo ENTRADA_URL; ?>/images/action-delete.gif");*/								
								}
							</style>

							<script type="text/javascript">
								var mapped = [];
								jQuery(document).ready(function($){
									jQuery('.objectives').hide();
									jQuery('.draggable').draggable({
										revert:true
									});
									jQuery('.droppable').droppable({
										drop: function(event,ui){										
											var id = jQuery(ui.draggable[0]).attr('data-id');
											var ismapped = jQuery.inArray(id,mapped);
											if(ismapped == -1){
												var title = jQuery('#objective_title_'+id).attr('data-title');										
												mapObjective(id,title);
											}
											jQuery(this).removeClass('hover');											
										},
										over:function(event,ui){
											jQuery(this).addClass('hover');
										},
										out: function(event,ui){
											jQuery(this).removeClass('hover');	
										}
									});

									jQuery('.remove').live('click',function(){
										var id = jQuery(this).attr('data-id');
										var key = jQuery.inArray(id,mapped);
										if(key != -1){
											mapped.splice(key,1);
										}
										jQuery('#check_objective_'+id).attr('checked','');

										jQuery('#mapped_objective_'+id).remove();																		
										jQuery("#mapped_objectives_select option[value='"+id+"']").remove();
										if(jQuery('#mapped_objectives').children('li').length == 0){
											var warning = jQuery(document.createElement('li'))
															.attr('class','display-notice')
															.html('No <strong>objectives</strong> have been mapped to this course.');
											jQuery('#mapped_objectives').append(warning);				
										}									
									});

									jQuery('.checked-objective').live('change',function(){
										var id = jQuery(this).val();
										var title = jQuery('#objective_title_'+id).attr('data-title');
										if (jQuery(this).is(':checked')) {
											mapObjective(id,title);
										} else {
											jQuery('#objective_remove_'+id).trigger('click');
										}
									});

									jQuery('.mapping-toggle').click(function(){
										var state = $(this).attr('data-toggle');
										if(state == "show"){
											$(this).attr('data-toggle','hide');
											$(this).html('Hide All Objectives');
											jQuery('.mapped_objectives').animate({width:'45%'},400,'swing',function(){
												//jQuery('.objectives').animate({display:'block'},400,'swing');											
												jQuery('.objectives').css({width:'48%'});
												jQuery('.objectives').show(400);
											});										
										}else{
											$(this).attr('data-toggle','show');
											$(this).html('Show All Objectives');
											jQuery('.objectives').animate({width:'0%'},400,'swing',function(){
												jQuery('.objectives').hide();
												jQuery('.mapped_objectives').animate({width:'100%'},400,'swing');
											});																				
										}
									});

								});

								function mapObjective(id,title){

									var li = jQuery(document.createElement('li'))
													.attr('class','mapped-objective')
													.attr('id','mapped_objective_'+id)
													.html(title);
									var rm = jQuery(document.createElement('a'))
													.attr('data-id',id)
													.attr('class','remove')
													.attr('id','objective_remove_'+id)
													.html('x');			
									jQuery(li).append(rm);											
									var option = jQuery(document.createElement('option'))
													.val(id)
													.attr('selected','selected')
													.html(title);														
									jQuery('#mapped_objectives').append(li);
									jQuery('#mapped_objectives .display-notice').remove();
									jQuery('#mapped_objectives_select').append(option);
									jQuery('#check_objective_'+id).attr('checked','checked');
									mapped.push(id);								
								}
							</script>
							<h2 class="collapsable" title="Child Objectives Section">Child Objectives</h2>
							<div id="child-objectives-section">
								<div class="pull-right space-below">
									<a href="#" class="btn btn-success objective-add-control" data-id="<?php echo $OBJECTIVE_ID; ?>"><i class="icon-plus-sign icon-white"></i> Add New Objective</a>
								</div>
								<div style="clear: both"></div>
								<div data-description="" data-id="<?php echo $OBJECTIVE_ID; ?>" data-title="" id="objective_title_<?php echo $OBJECTIVE_ID; ?>" class="objective-title" style="display:none;"></div>
								<div class="half left" id="children_<?php echo $OBJECTIVE_ID; ?>">
										<ul class="objective-list" id="objective_list_<?php echo $OBJECTIVE_ID; ?>">
								<?php
									$query = "SELECT a.* FROM `global_lu_objectives` a
												LEFT JOIN `objective_organisation` b
												ON a.`objective_id` = b.`objective_id`
												AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
												WHERE a.`objective_parent` = ".$db->qstr($OBJECTIVE_ID)."
												AND a.`objective_active` = '1'
												ORDER BY a.`objective_order`";
									$objectives = $db->GetAll($query);
									if($objectives){ ?>
									
								<?php		foreach($objectives as $objective){ ?>
												<li class = "objective-container"
													id = "objective_<?php echo $objective["objective_id"]; ?>">
													<?php $title = ($objective["objective_code"]?$objective["objective_code"].': '.$objective["objective_name"]:$objective["objective_name"]); ?>
													<div 	class="objective-title" 
															id="objective_title_<?php echo $objective["objective_id"]; ?>" 
															data-title="<?php echo $title;?>"
															data-id = "<?php echo $objective["objective_id"]; ?>"
															data-code = "<?php echo $objective["objective_code"]; ?>"
															data-name = "<?php echo $objective["objective_name"]; ?>"
															data-description = "<?php echo $objective["objective_description"]; ?>">
														<?php echo $title; ?>
													</div>
													<div class="objective-controls">
														<i class="objective-edit-control icon-edit" data-id="<?php echo $objective["objective_id"]; ?>"></i>
														<i class="objective-add-control icon-plus-sign" data-id="<?php echo $objective["objective_id"]; ?>"></i>
														<i class="objective-delete-control icon-minus-sign" data-id="<?php echo $objective["objective_id"]; ?>"></i>
														<i class="objective-link-control icon-link" data-id="<?php echo $objective["objective_id"]; ?>"></i>
													</div>
													<div 	class="objective-children"
															id="children_<?php echo $objective["objective_id"]; ?>">
															<ul class="objective-list" id="objective_list_<?php echo $objective["objective_id"]; ?>">
															</ul>
													</div>
												</li>
								<?php 		} ?>
										
							<?php } ?>
												</ul>
									</div>
								<div style="clear:both;"></div>
							</div>
						</div>
						<div id="objective-link-modal" class="hide"></div>
						<?php
					default:
					break;
				}
			} else {
				$url = ENTRADA_URL."/admin/settings/manage/objectives?org=" . $ORGANISATION_ID;
				$ONLOAD[]	= "setTimeout('window.location=\\'". $url . "\\'', 5000)";

				$ERROR++;
				$ERRORSTR[] = "	In order to update an objective a valid objective identifier must be supplied. The provided ID does not exist in the system.  You will be redirected to the System Settings page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";

				echo display_error();

				application_log("notice", "Failed to provide objective identifer when attempting to edit an objective.");
			}
		}
	} else {
		$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/settings/manage/".$MODULE."\\'', 15000)";

		$ERROR++;
		$ERRORSTR[] = "In order to update an objective a valid objective identifier must be supplied.";

		echo display_error();

		application_log("notice", "Failed to provide objective identifer when attempting to edit an objective.");
	}
}
?>
