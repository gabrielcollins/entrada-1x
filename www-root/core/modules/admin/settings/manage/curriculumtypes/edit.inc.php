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
 * @author Organisation: Queen's University
 * @author Unit: MEdTech Unit
 * @author Developer: Brandon Thorn <brandon.thorn@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_CONFIGURATION"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("configuration", "create",false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {

	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/settings/manage/curriculumtypes?".replace_query(array("section" => "add"))."&amp;org=".$ORGANISATION_ID, "title" => "Edit");
	//$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/jquery/jquery.min.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";

	if ((isset($_GET["type_id"])) && ($type = clean_input($_GET["type_id"], array("notags", "trim")))) {
		$PROCESSED["curriculum_type_id"] = $type;
	}

	// Error Checking
	switch ($STEP) {
		case 2 :
			/**
			 * Required field "objective_name" / Objective Name
			 */
			if (isset($_POST["curriculum_type_name"]) && ($type_title = clean_input($_POST["curriculum_type_name"], array("notags", "trim")))) {
				$PROCESSED["curriculum_type_name"] = $type_title;
			} else {
				$ERROR++;
				$ERRORSTR[] = "The <strong>Curriculum Type Name</strong> is a required field.";
			}

			/**
			 * Non-required field "objective_description" / Objective Description
			 */
			if (isset($_POST["curriculum_type_description"]) && ($type_description = clean_input($_POST["curriculum_type_description"], array("notags", "trim")))) {
				$PROCESSED["curriculum_type_description"] = $type_description;
			} else {
				$PROCESSED["curriculum_type_description"] = "";
			}

			/**
			 * Required field "curriculum_level_id" / Curriculum Level
			 */
			if (isset($_POST["curriculum_level_id"]) && ($c_level_id = clean_input($_POST["curriculum_level_id"], array("notags", "trim", "int")))) {
				$PROCESSED["curriculum_level_id"] = $c_level_id;
			} else {
				$ERROR++;
				$ERRORSTR[] = "The <strong>Curriculum Level</strong> is a required field.";
			}

			if (!$ERROR) {
				$PROCESSED["updated_date"] = time();
				$PROCESSED["updated_by"] = $ENTRADA_USER->getID();
				$PROCESSED["curriculum_type_active"] = 1;
				$PROCESSED["curriculum_type_order"] = 1;

				if ($db->AutoExecute("curriculum_lu_types", $PROCESSED, "UPDATE","`curriculum_type_id`=".$db->qstr($PROCESSED["curriculum_type_id"]))) {

                    $active_cperiod_ids = array();

                    /**
                     * Optional field Period Start Date
                     */
                    if (isset($_POST["curriculum_start_date"]) && count($_POST["curriculum_start_date"])) {
                        foreach($_POST["curriculum_start_date"] as $key => $date){
                            $pieces = explode("-", $key);
                            $period_action = trim($pieces[0]);
                            $period_id = (int) trim($pieces[1]);

                            if (in_array($period_action, array("add", "edit")) && $period_id) {
                                $period_entry = array();
                                $period_entry["curriculum_type_id"] = $PROCESSED["curriculum_type_id"];
                                $period_entry["start_date"] = strtotime(clean_input($date, array("trim", "notags")));
                                $period_entry["finish_date"] = strtotime(clean_input($_POST["curriculum_finish_date"][$key], array("trim", "notags")));
								$period_entry["curriculum_period_title"] = clean_input($_POST["curriculum_period_title"][$key],array("trim","notags"));
                                $period_entry["active"] = 1;

                                switch ($period_action) {
                                    case "add" :
                                        if ($db->AutoExecute("curriculum_periods", $period_entry, "INSERT")) {
                                            $period_id = $db->Insert_Id();

                                            $active_cperiod_ids[] = $period_id;

                                            $key = "edit-".$period_id;
                                        } else {
                                            application_log("error", "Unable add new curriculum period to curriculum_type_id [".$PROCESSED["curriculum_type_id"]."]. Database said: ".$db->ErrorMsg());
                                        }
                                        break;
                                    case "edit" :
                                        $query = "SELECT * FROM `curriculum_periods` WHERE `cperiod_id` = ".$db->qstr($period_id)." AND `curriculum_type_id` = ".$db->qstr($PROCESSED["curriculum_type_id"]);
                                        $result = $db->GetRow($query);
                                        if ($result) {
                                            if ($db->AutoExecute("curriculum_periods", $period_entry, "UPDATE", "cperiod_id = " . $period_id)) {
                                                $active_cperiod_ids[] = $period_id;
                                            } else {
                                                application_log("error", "Unable add update curriculum period id [".$period_id."] in curriculum_type_id [".$PROCESSED["curriculum_type_id"]."]. Database said: ".$db->ErrorMsg());
                                            }
                                        }
                                        break;
                                    default :
                                        continue;
                                    break;
                                }

                                $PROCESSED["periods"][$key] = $period_entry;
                            }
                        }
                    }

                    $query = "UPDATE `curriculum_periods` SET active = '0' WHERE `curriculum_type_id` = ".$db->qstr($PROCESSED["curriculum_type_id"]).(!empty($active_cperiod_ids) ? " AND `cperiod_id` NOT IN (".implode(", ", $active_cperiod_ids).")" : "");
                    if (!$db->Execute($query)) {
                        application_log("error", "Unable to deactive old curriculum_periods. Database said: ".$db->ErrorMsg());
                    }

					$url = ENTRADA_URL . "/admin/settings/manage/curriculumtypes?org=".$ORGANISATION_ID;
					$SUCCESS++;
					$SUCCESSSTR[] = "You have successfully updated <strong>".html_encode($PROCESSED["curriculum_type_name"])."</strong> to the system.<br /><br />You will now be redirected to the Curriculum Types index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
					$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";
					application_log("success", "New Hot Topic [".$TOPIC_ID."] added to the system.");


				} else {
					$ERROR++;
					$ERRORSTR[] = "There was a problem updating this Curriculum Type into the system. The system administrator was informed of this error; please try again later.";

					application_log("error", "There was an error inserting a Curriculum Type. Database said: ".$db->ErrorMsg());
				}
			}

			if ($ERROR) {
				$STEP = 1;
			}
		break;
		case 1 :
		default :

			$query = "SELECT * FROM `curriculum_lu_types` WHERE `curriculum_type_id` = ".$db->qstr($PROCESSED["curriculum_type_id"]);
			$result = $db->GetRow($query);
			if($result){
				$PROCESSED["curriculum_type_name"] = $result["curriculum_type_name"];
				$PROCESSED["curriculum_type_description"] = $result["curriculum_type_description"];
				$PROCESSED["curriculum_level_id"] = $result["curriculum_level_id"];
			}


			$query = "SELECT * FROM `curriculum_periods` WHERE `curriculum_type_id` = ".$db->qstr($PROCESSED["curriculum_type_id"])." AND `active` = '1' ORDER BY `start_date` ASC";
			$result = $db->GetAll($query);

			if($result){
				foreach($result as $key=>$row){
					$PROCESSED["periods"]["edit-".$row["cperiod_id"]]["start_date"] = $row["start_date"];
					$PROCESSED["periods"]["edit-".$row["cperiod_id"]]["finish_date"] = $row["finish_date"];
					$PROCESSED["periods"]["edit-".$row["cperiod_id"]]["active"] = $row["active"];
					$PROCESSED["periods"]["edit-".$row["cperiod_id"]]["curriculum_period_title"] = $row["curriculum_period_title"];
				}
			}


		break;
	}

	// Display Content
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
		default:
			if ($ERROR) {
				echo display_error();
			}

			$ONLOAD[] = "selectObjective(".(isset($PROCESSED["objective_parent"]) && $PROCESSED["objective_parent"] ? $PROCESSED["objective_parent"] : "0").")";
			$ONLOAD[] = "selectOrder(".(isset($PROCESSED["objective_parent"]) && $PROCESSED["objective_parent"] ? $PROCESSED["objective_parent"] : "0").")";

			if (isset($_GET["org"]) && ($org_id = clean_input($_GET["org"], array("notags", "trim", "int")))) {
				$PROCESSED["org_id"] = $org_id;
			} else {
				$PROCESSED["org_id"] = 0;
			}
			?>
			<form action="<?php echo ENTRADA_URL."/admin/settings/manage/curriculumtypes"."?".replace_query(array("action" => "add", "step" => 2))."&org=".$ORGANISATION_ID; ?>" id ="curriculum_form" method="post">
			<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Adding Page">
			<colgroup>
				<col style="width: 20%" />
				<col style="width: 80%" />
			</colgroup>
			<thead>
				<tr>
					<td colspan="2"><h1>Edit Curriculum Type</h1></td>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td></td>
					<td style="padding-top: 15px;">
						<input type="button" class="btn btn-danger pull-left" id="delete_selected" value="Remove Selected" />
						<div class="pull-right">
							<input type="button" class="btn" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/settings/manage/curriculumtypes?org=<?php echo $ORGANISATION_ID;?>'" />
							<input type="submit" class="btn btn-primary" value="<?php echo $translate->_("global_button_save"); ?>" />
						</div>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td><label for="curriculum_type_name" class="form-required">Curriculum Type Name:</label></td>
					<td><input type="text" id="curriculum_type_name" name="curriculum_type_name" value="<?php echo ((isset($PROCESSED["curriculum_type_name"])) ? html_encode($PROCESSED["curriculum_type_name"]) : ""); ?>" maxlength="60" style="width: 300px" /></td>
				</tr>
				<tr>
					<td style="vertical-align: top;"><label for="curriculum_type_description" class="form-nrequired">Curriculum Type Description: </label></td>
					<td>
						<textarea id="curriculum_type_description" name="curriculum_type_description" style="width: 98%; height: 50px" rows="20" cols="70"><?php echo ((isset($PROCESSED["curriculum_type_description"])) ? html_encode($PROCESSED["curriculum_type_description"]) : ""); ?></textarea>
					</td>
				</tr>
				<tr>
					<td style="vertical-align: top;"><label for="curriculum_level" class="form-required">Curriculum Level: </label></td>
					<td>
						<select id="curriculum_level_id" name="curriculum_level_id" style="width: 250px">
						<?php
						$query = "	SELECT a.*
									FROM `" . DATABASE_NAME . "`.`curriculum_lu_levels`  AS a,
									`" . DATABASE_NAME . "`.`curriculum_level_organisation`  AS b
									WHERE a.`curriculum_level_id` = b.`curriculum_level_id`
									AND b.`org_id` = " . $PROCESSED["org_id"] . "
								    ORDER BY a.`curriculum_level` ASC";
						$results = $db->GetAll($query);

						if ($results) { ?>
							<option value="0">Choose a Curriculum Level</option>
						<?php
							foreach ($results as $result) {
						?>
							<option value="<?php echo $result["curriculum_level_id"]; ?>" <?php echo ($result["curriculum_level_id"] == $PROCESSED["curriculum_level_id"]) ? "selected=\"selected\"" : ""; ?>>
							<?php echo $result["curriculum_level"] ?>
							</option>
						<?php
							}
						} else { ?>
							<option value="0">No Curriculum Levels Exist for this Organisation</option>
						<?php }
						?>
					</select>
					</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td style="vertical-align: top;"><label for="curriculum_type_periods" class="form-nrequired">Curriculum Type Periods: </label></td>
					<td>
						<div style="float: right">
							<ul class="page-action">
								<li><a class="strong-green" id="add_period" style="cursor:pointer;">Add Curriculum Period</a></li>
							</ul>
						</div><br />
						<div id="curriculum_periods_table">
							<table class="tableList" cellspacing="0" cellpadding="1" border="0" summary="List of Curriculum Periods">
								<colgroup>
									<col class="modified" style="width: 5%" />
									<col class="start" style="width: 25%" />
									<col class="end" style="width: 25%" />
									<col style="width: 25%" />
									<col class="active" style="width: 20%" />
								</colgroup>
								<thead>
									<tr>
										<td class="modified">&nbsp;</td>
										<td class="start">Start Date</td>
										<td class="end">Finish Date</td>
										<td>Title</td>
										<td class="active">Active</td></tr>
									</tr>
								</thead>
								<tbody id="curriculum_periods">
									<?php
										if ($PROCESSED["periods"]) {
											foreach ($PROCESSED["periods"] as $currentIdx => $period){
                                                ?>
                                                <tr id="period_<?php echo $currentIdx;?>" class="curriculum_period">
                                                    <td class="modified">
														<input type="checkbox" class="remove_checkboxes" id="remove_<?php echo $currentIdx;?>" value="<?php echo $currentIdx;?>"/>
													</td>
                                                    <td class="date">
														<input type="text" name="curriculum_start_date[<?php echo $currentIdx;?>]" id="start_<?php echo $currentIdx;?>" class="start_date" disabled = "disabled" value="<?php echo date("Y-m-d",$period["start_date"]);?>" style="border:none;"/><img src="<?php echo ENTRADA_URL; ?>/images/cal-calendar.gif" alt="Select Start Date" class="calendar" id="start_calendar_<?php echo $currentIdx;?>" style="float:right;cursor:pointer;"/>
													</td>
                                                    <td class="date">
														<input type="text" name="curriculum_finish_date[<?php echo $currentIdx;?>]" id="finish_<?php echo $currentIdx;?>" class="end_date" disabled = "disabled" value="<?php echo date("Y-m-d",$period["finish_date"]);?>" style="border:none;"/><img src="<?php echo ENTRADA_URL; ?>/images/cal-calendar.gif" alt="Select End Date" class="calendar" id="finish_calendar_<?php echo $currentIdx;?>" style="float:right;cursor:pointer;"/>
													</td>
													<td>
														<input type="text" name="curriculum_period_title[<?php echo $currentIdx;?>]" id="curriculum_period_title_<?php echo $currentIdx;?>" value="<?php echo $period["curriculum_period_title"];?>" />
													</td>
													<td>
														<select id="curriculum_active_<?php echo $currentIdx;?>" name="curriculum_active[]">
															<option value="1" selected="selected">Active</option>
															<option value="0" <?php echo (($period["active"] == 0)?"selected=\"selected\"":"");?>>Inactive</option>
														</select>
													</td>
                                                </tr>
                                                <?php
											}
										}
									?>
								</tbody>
							</table>
						</div>
						<div id="no_period_msg">
						<?php
									add_notice("There are no active periods for this curriculum type.");
									echo display_notice();
						?>
						</div>
						<style type="text/css">
							[id^="curriculum_active"]	 {
								width: 100px; margin-bottom: 10px;
							}
							[id^="curriculum_period_title"] {
								width: 110px;
							}
							input[id^="start_"] {
								width: 80px;
							}
							input[id^="finish_"] {
								width: 80px;
							}
						</style>

						<script type="text/javascript">
						var rowTemplate = '<tr id="period_:id" class="curriculum_period">\n\
												<td class="modified"><input type="checkbox" class="remove_checkboxes" id="remove_:id" value=":id"/></td>\n\
												<td class="date"><input type="text" name="curriculum_start_date[:id]" id="start_:id" class="start_date" disabled = "disabled" value=":date" style="border:none;"/><img src="<?php echo ENTRADA_URL; ?>/images/cal-calendar.gif" alt="Select Start Date" class="calendar" id="start_calendar_:id" style="float:right;cursor:pointer;"/></td>\n\
												<td class="date"><input type="text" name="curriculum_finish_date[:id]" id="finish_:id" class="end_date" disabled = "disabled" value=":date" style="border:none;"/><img src="<?php echo ENTRADA_URL; ?>/images/cal-calendar.gif" alt="Select End Date" class="calendar" id="finish_calendar_:id" style="float:right;cursor:pointer;"/></td>\n\
												<td><input type="text" name="curriculum_period_title[:id]" id="curriculum_period_title_:id" /></td>\n\
												<td><select id="curriculum_active_:id" name="curriculum_active[:id]"><option value="1">Active</option><option value="0">Inactive</option></select></td>\n\
											</tr>';

						var currentIdx = 1;
						var numRows = 0;

						jQuery(function($){
							$(document).ready(function(){
								$('#delete_btn_row').hide();
								$(".calendar").live('click',function(e){
									var info = e.target.id.split("_");
									showCalendar('', document.getElementById(info[0]+'_'+info[2]), document.getElementById(info[0]+'_'+info[2]), '', 'Title', 0, 20, 1);
								});
								$('.curriculum_period').each(function(){
									currentIdx++;
									numRows++;
								});

								if(currentIdx>1){
									$('#delete_btn_row').show();
									$('#no_period_msg').hide();
								}

							});

							$('#add_period').click(function(){
								var today = new Date();
								var month = ((today.getMonth()+1)/10 > 1)?today.getMonth()+1:"0"+(today.getMonth()+1);
								var day = (today.getDate()/10 > 1)?today.getDate():"0"+(today.getDate());
								var date = today.getFullYear()+"-"+month+"-"+day;
								var formattedRow = rowTemplate.replace(/:id/g,'add-' + currentIdx).replace(/:date/g,date);
								$('#curriculum_periods').append(formattedRow);

								currentIdx++;
								numRows++;
								$('#delete_btn_row').show();
								$('#no_period_msg').hide();
							});


							$('#delete_selected').click(function(){
								$('.remove_checkboxes:checked').each(function(){
									var id = $(this).attr('value');
									$('#period_'+id).remove();
									numRows--;
									if(numRows == 0){
										$('#delete_btn_row').hide();
										$('#no_period_msg').show();
									}
								});
							});


							$('#curriculum_form').submit(function(){
								$('.start_date').each(function(){
									$(this).removeAttr('disabled');
								});

								$('.end_date').each(function(){
									$(this).removeAttr('disabled');
								});
							});

						});



						</script>

					</td>
				</tr>

			</tbody>
			</table>
			</form>
			<?php
		break;
	}

}
