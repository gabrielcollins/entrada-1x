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
 * @author Unit: School of Medicine
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 */

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_GRADEBOOK"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("gradebook", "update", false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	if ($COURSE_ID) {
		/**
		 * Handles the AJAX re-ordering of assessments.
		 */
		if (isset($_POST["mode"]) && ($_POST["mode"] == "ajax") && isset($_POST["order"]) && is_array($_POST["order"]) && !empty($_POST["order"])) {
			ob_clear_open_buffers();

			foreach ($_POST["order"] as $assessment_id => $order) {
				$order = (int) $order[0];

				$query = "UPDATE `assessments` SET `order` = ".$db->qstr($order)." WHERE `course_id` = ".$db->qstr($COURSE_ID)." AND `assessment_id` = ".$db->qstr((int) $assessment_id);
				if($db->Execute($query)) {
					$error = false;
					application_log("success", "Updated gradebook assessment [".$assessment_id."] to order [".$order."].");
				} else {
					$error = true;
					application_log("error", "Failed to update assessment [".$assessment_id."] to order [".$order."]. Database said: ".$db->ErrorMsg());
				}
			}

			echo ($error == false ? 1 : 0);

			exit;
		}

		$query = "	SELECT * FROM `courses`
					WHERE `course_id` = ".$db->qstr($COURSE_ID)."
					AND `course_active` = '1'";
		$course_details	= $db->GetRow($query);
		if ($course_details && $ENTRADA_ACL->amIAllowed(new GradebookResource($course_details["course_id"], $course_details["organisation_id"]), "read")) {
			$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/gradebook?".replace_query(array("section" => "view", "id" => $COURSE_ID, "step" => false)), "title" => "Assessments");

			/**
			 * Update requested column to sort by.
			 * Valid: director, name
			 */
			if (isset($_GET["sb"])) {
				if (@in_array(trim($_GET["sb"]), array("name", "type", "scheme"))) {
					$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= trim($_GET["sb"]);
				} else {
					$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = "name";
				}

				$_SERVER["QUERY_STRING"]	= replace_query(array("sb" => false));
			} else {
				if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"])) {
					$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = "name";
				}
			}

			/**
			 * Update requested order to sort by.
			 * Valid: asc, desc
			 */
			if (isset($_GET["so"])) {
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"] = ((strtolower($_GET["so"]) == "desc") ? "desc" : "asc");

				$_SERVER["QUERY_STRING"] = replace_query(array("so" => false));
			} else {
				if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"])) {
					$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"] = "asc";
				}
			}

			/**
			 * Update requsted number of rows per page.
			 * Valid: any integer really.
			 */
			if ((isset($_GET["pp"])) && ((int) trim($_GET["pp"]))) {
				$integer = (int) trim($_GET["pp"]);

				if (($integer > 0) && ($integer <= 250)) {
					$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] = $integer;
				}

				$_SERVER["QUERY_STRING"] = replace_query(array("pp" => false));
			} else {
				if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"])) {
					$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] = DEFAULT_ROWS_PER_PAGE;
				}
			}

			/**
			 * Check if preferences need to be updated on the server at this point.
			 */
			preferences_update($MODULE, $PREFERENCES);

			/**
			 * Provide the queries with the columns to order by.
			 */
			switch($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) {
				case "name" :
					$sort_by = "`assessments`.`name` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]).", `assessments`.`cohort` ASC";
				break;
				case "type" :
					$sort_by = "`assessments`.`type` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
				break;
				case "scheme" :
					$sort_by = "`assessment_marking_schemes`.`name` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
				break;
				default :
					$sort_by = "`assessments`.`order` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
				break;
			}			

			/**
			 * Check if cohort variable is set, otherwise a default is used.
			 */
			if (isset($_GET["cohort"]) && ((int)$_GET["cohort"])) {
				$selected_cohort = (int) $_GET["cohort"];
			} elseif ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["cohort"]) {
                $selected_cohort = $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["cohort"];
            }
			
			if (isset($_GET["course_list"]) && ((int)$_GET["course_list"])) {
				$selected_classlist = (int) $_GET["course_list"];
			} elseif ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["course_list"]) {
                $selected_classlist = $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["course_list"];
            }
			if ($selected_cohort) {
				$query	= "	SELECT COUNT(*) AS `total_rows` 
							FROM `assessments` a
							JOIN `groups` AS b
							ON a.`cohort` = b.`group_id`
							JOIN `group_organisations` AS c
							ON b.`group_id` = c.`group_id`
							AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
							AND b.`group_active` = 1
							AND b.`group_id` = " . $db->qstr($selected_cohort) . "
							WHERE a.`course_id` = ".$db->qstr($COURSE_ID);
				$result	= $db->GetRow($query);
			} 
			if ($result) {
				$total_rows	= $result["total_rows"];

				if ($total_rows <= $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) {
					$total_pages = 1;
				} elseif (($total_rows % $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == 0) {
					$total_pages = (int) ($total_rows / $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
				} else {
					$total_pages = (int) ($total_rows / $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) + 1;
				}
			} else {
				$total_rows = 0;
				$total_pages = 1;
			}

			/**
			 * Check if pv variable is set and see if it's a valid page, other wise page 1 it is.
			 */
			if (isset($_GET["pv"])) {
				$page_current = (int) trim($_GET["pv"]);

				if (($page_current < 1) || ($page_current > $total_pages)) {
					$page_current = 1;
				}
			} else {
				$page_current = 1;
			}

			if ($total_pages > 1) {
				$pagination = new Pagination($page_current, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"], $total_rows, ENTRADA_URL."/admin/".$MODULE, replace_query());
			}

			$limit_parameter = (int) (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] * $page_current) - $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);

            courses_subnavigation($course_details,"gradebook");
			$curriculum_path = curriculum_hierarchy($COURSE_ID);
			if ((is_array($curriculum_path)) && (count($curriculum_path))) {
                echo "  <div class=\"row-fluid\">";
                echo "      <div class=\"span12\">";
				echo "          <h1>" . implode(": ", $curriculum_path) . " Gradebook </h1>";
                echo "      </div>";
                echo "  </div>";
			}

            echo "  <br />\n";

            echo "<div class=\"row-fluid\">\n";
			
			$query = "	SELECT * 
						FROM `groups` 
						WHERE `group_type` = 'course_list' 
						AND `group_value` = ".$db->qstr($COURSE_ID)." 
						AND `group_active` = '1'
						ORDER BY `group_name`";
			$course_lists = $db->GetAll($query);			
			if ($course_lists) { 		
				$cohorts = $course_lists;
				if (count($course_lists) == 1) {										
					$output_cohort = $course_lists[0];
					?>
					<h2 class="pull-left"><?php echo $course_list["group_name"];?></h2>				
		<?php
				} else {
					$output_cohort = false;
					$classlist_found = false;
					foreach ($course_lists as $key => $course_list) {
						if (!$classlist_found) {
							$output_cohort = $course_list;
							if (isset($selected_classlist) && $selected_classlist && $selected_classlist == $course_list["group_id"]) {
								$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["course_list"] = $selected_classlist;
								$classlist_found = true;
							}
							if ($key == (count($course_lists) - 1) && !$classlist_found) {
								$selected_classlist = $course_list["group_id"];
							}
						}
					} 
		?>
					<div class="span12 clearfix">
						<h2 class="pull-left"><?php echo $output_cohort["group_name"];?></h2>
						<form class="pull-right form-horizontal" style="margin-bottom:0;">
							<div class="control-group">
								<label for="course_list-quick-select" class="control-label content-small">
									Target Audience:
								</label>
								<div class="controls">
									<select id="course_list-quick-select" name="course_list-quick-select" onchange="window.location='<?php echo ENTRADA_URL;?>/admin/gradebook?section=view&id=<?php echo $COURSE_ID;?>&course_list='+this.options[this.selectedIndex].value">
										<?php
										foreach ($course_lists as $key => $course_list) { ?>
											<option value="<?php echo $course_list["group_id"];?>" <?php echo (($course_list["group_id"] == $selected_classlist) ? "selected=\"selected\"" : "");?>>
												<?php echo $course_list["group_name"];?>
											</option>
										<?php
										} ?>
									</select>
								</div>
							</div>
						</form>
					</div>
		<?php
				}
			} else {
				$query =  "SELECT a.`course_id`, b.`group_name`, b.`group_id` 
							FROM `assessments` AS a
							JOIN `groups` AS b
							ON a.`cohort` = b.`group_id`
							JOIN `group_organisations` AS c
							ON b.`group_id` = c.`group_id`
							WHERE a.`course_id` =". $db->qstr($COURSE_ID)."
							AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
							GROUP BY b.`group_id`
							ORDER BY b.`group_name`";
				$cohorts = $db->GetAll($query);
				
                $output_cohort = false;
                $cohort_found = false;
                foreach ($cohorts as $key => $cohort) {
                    if (!$cohort_found) {
                        $output_cohort = $cohort;
                        if (isset($selected_cohort) && $selected_cohort && $selected_cohort == $cohort["group_id"]) {
                            $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["cohort"] = $selected_cohort;
                            $cohort_found = true;
                        }
                        if ($key == (count($cohorts) - 1) && !$cohort_found) {
                            $selected_cohort = $cohort["group_id"];
                        }
                    }
                }
                ?>
                <div class="span12 clearfix">
					<h2 class="pull-left"><?php echo $output_cohort["group_name"];?></h2>
            		<form class="pull-right form-horizontal" style="margin-bottom:0;">
						<div class="control-group">
                			<label for="cohort-quick-select" class="control-label content-small">
                				Target Audience:
                			</label>
                			<div class="controls">
								<select id="cohort-quick-select" name="cohort-quick-select" onchange="window.location='<?php echo ENTRADA_URL;?>/admin/gradebook?section=view&id=<?php echo $COURSE_ID;?>&cohort='+this.options[this.selectedIndex].value">
                                    <?php
                                    foreach ($cohorts as $key => $cohort) { ?>
                                        <option value="<?php echo $cohort["group_id"];?>" <?php echo (($cohort["group_id"] == $selected_cohort) ? "selected=\"selected\"" : "");?>>
                                            <?php echo $cohort["group_name"];?>
                                        </option>
                                    <?php
                                    } ?>
                				</select>
                			</div>
                		</div>
                	</form>
                </div>
                <?php
            }
            echo "  </div>\n";
            if ($ENTRADA_ACL->amIAllowed("gradebook", "create", false) && $ENTRADA_ACL->amIAllowed(new CourseContentResource($COURSE_ID, $course_details["organisation_id"]), "update")) { 
                ?>
                <div class="pull-right">
                    <a id="gradebook_assessment_add" href="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE . "/assessments/?" . replace_query(array("section" => "add", "step" => false)); ?>" class="btn btn-primary">Add New Assessment</a>
                </div>
                <div style="clear: both"></div>
                <?php
            }
			if($cohorts) {
				if ($total_pages > 1) {
					echo "<div id=\"pagination-links\">\n";
					echo "	Pages: ".$pagination->GetPageLinks();
					echo "</div>\n";
				}
				if ($ENTRADA_ACL->amIAllowed("gradebook", "delete", false)) {
					echo "<form action=\"".ENTRADA_URL . "/admin/gradebook/assessments?".replace_query(array("section" => "delete", "step"=>1))."\" method=\"post\">";
				}
				?>
                <script type="text/javascript">
                    jQuery(document).ready(function(){
                        jQuery('.edit_grade').live('click',function(e){
                            var id = e.target.id.substring(5);
                            jQuery('#'+id).trigger('click');
                        });
                        
						jQuery('#export-grades').live('click',function(){
	                        var ids = [];
	                        jQuery('#assessment_list .modified input:checked').each(function() {
	                            ids.push(jQuery(this).val());
	                        });
	                        if(ids.length > 0) {
	                            window.location = '<?php echo ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("section" => "io", "download" => "csv", "assessment_ids" => false)); ?>&assessment_ids='+ids.join(',');
	                        } else {
	                            var cohort = jQuery('#cohort-quick-select').val();
	                            window.location = '<?php echo ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("section" => "io", "download" => "csv", "assessment_ids" => false)); ?>&cohort='+cohort;
	                        }
	                        return false;
	                    });                          

                        var reordering = false;
                        var orderChanged = false;

                        jQuery('#reorder').click(function(){
                            jQuery('.ordermsg').remove();
                            if (reordering == false) {
                                jQuery('#saveorder').show();
                                jQuery('#delete, #export').hide();

                                jQuery('#assessment_list tbody tr td.modified .delete').hide();
                                jQuery('#assessment_list tbody tr td.modified').append('<span class="handle"></span>');
                                jQuery('#assessment_list tbody').sortable({
                                    items: '.assessment',
                                    containment: 'parent',
                                    handle: '.handle',
                                    change: function(event,ui){
                                        orderChanged = true;
                                    }
                                });
                                reordering = true;
                                jQuery('#reorder').attr('value', 'Cancel Reorder');
                                jQuery('.display-success, .display-error').fadeOut(500,function(){
                                    $(this).remove();
                                });
                            } else {
                                jQuery('#saveorder').hide();
                                jQuery('#assessment_list tbody tr td.modified .handle').remove();
                                jQuery('#assessment_list tbody tr td.modified .delete').show();
                                jQuery('#reorder').attr('value', 'Reorder');
                                reordering = false;
                                jQuery('#delete, #export').show();
                                if (orderChanged == true) {
                                    // if you try to cancel the sortable and the order hasn't changed javascript breaks.
                                    jQuery('#assessment_list tbody').sortable('cancel').sortable('destroy');
                                } else {
                                    jQuery('#assessment_list tbody').sortable('destroy');
                                }
                            }
                            return false;
                        });

                        jQuery('#saveorder').click(function(){
                            jQuery('.ordermsg').remove();

                            // assign order to assessment
                            jQuery('#assessment_list tbody tr td.modified .order').each(function(){
                                jQuery(this).attr('value',jQuery(this).parent().parent().index()-1);
                            });

                            // serialize the form data to pass to the ajax updater
                            var formData = jQuery('#assessment_list').parent().serialize();

                            var ajaxParams = 'mode=ajax&'+formData;
                            var ajaxURL = '<?php echo ENTRADA_RELATIVE; ?>/admin/gradebook?section=view&id=<?php echo $COURSE_ID; ?>';

                            jQuery.ajax({
                                data: ajaxParams,
                                url: ajaxURL,
                                type: 'POST',
                                success: function(data) {
                                    if (data == 1) {
                                        jQuery('#assessment_list').parent().append('<div class=\'display-success\'><ul><li>These assessment order have been reordered.</li></ul></div>');
                                    } else {
                                        jQuery('#assessment_list').parent().append('<div class=\'display-error\'><ul><li>An error occurred while reordering these assessments.</li></ul></div>');
                                    }
                                }
                            });

                            reordering = false;

                            jQuery(this).hide();
                            jQuery('#assessment_list tbody tr td .handle').remove();
                            jQuery('#assessment_list tbody tr td.modified .delete').show();
                            jQuery('#reorder').attr('value', 'Reorder');
                            jQuery('#assessment_list tbody').sortable('destroy');
                            jQuery('#delete, #export').show();
                        });
                    });                   
                </script>
                <br />
				<table class="tableList" cellspacing="0" summary="List of Assessments" id="assessment_list">
					<tfoot>
						<tr>
							<td style="padding-top: 10px; border-bottom:0;" colspan="2">
								<input type="submit" class="btn btn-danger" id="delete" value="Delete Selected" />								
								<input type="button" class="btn" id="reorder" value="Reorder" />
								<input type="button" class="btn btn-primary" id="saveorder" value="Save Order" />
							</td>
							<td style="padding-top: 10px; border-bottom: 0; text-align:right;" colspan="2">
								<input type="button" id="fullscreen-edit" class="btn" data-href="<?php echo ENTRADA_URL . "/admin/gradebook?" . replace_query(array("section" => "api-edit")); ?>" value="Grade Spreadsheet" />
								<input type="button" id="export-grades" class="btn" value="Export Grades"/>
							</td>
						</tr>
						<tr>
							<td style="border-bottom:0;"></td>
						</tr>
					</tfoot>
					<tbody>
					<?php
					if ($cohorts) {
                        if ($output_cohort) {
							echo "<tr>";
							echo "<td style=\"width: 20px;\"></td>";
							echo "<td style=\"width: 300px;\"><h3 style=\"border-bottom: 0;\">Assessment</h3></td>";
							echo "<td><h3 style=\"border-bottom: 0;\">Grade Weighting</h3></td>";
							echo "<td><h3 style=\"border-bottom: 0;\">Assignment</h3></td>";
							echo "</tr>";

							$query = "	SELECT `course_id`, `assessment_id`, `name`, `grade_weighting`, `order`
										FROM `assessments`
										WHERE `cohort` = " . $db->qstr($output_cohort["group_id"])."
										AND `course_id` = ". $db->qstr($COURSE_ID)."
										ORDER BY `order` ASC";

							$results = $db->GetAll($query);
							if ($results) {
								$total_grade_weight = 0;
								$count = 0;
								foreach ($results as $result) {
									if ($ENTRADA_ACL->amIAllowed(new AssessmentResource($course_details["course_id"], $course_details["organisation_id"], $result["assessment_id"]), "update")) {
										//Display this row if the user is a Dropbox Contact for an assignment associated with this assessment or if they are the Course Owner.
										$query =  "	SELECT a.`course_id`, a.`assignment_id`, a.`assignment_title` 
													FROM `assignments` a
													JOIN `assignment_contacts`	b
													ON a.`assignment_id` = b.`assignment_id`
													WHERE a.`assessment_id` = " . $db->qstr($result["assessment_id"]) . "
													AND b.`proxy_id` = " . $db->qstr($ENTRADA_USER->getActiveId()) . "
													AND a.`assignment_active` = 1";
										$assignment_contact = $db->GetRow($query);	
										if ($assignment_contact || $ENTRADA_ACL->amIAllowed(new CourseContentResource($course_details["course_id"], $course_details["organisation_id"]), "update")) {
											$count++;
											$total_grade_weight += $result["grade_weighting"];

											$url = ENTRADA_URL."/admin/gradebook/assessments?section=grade&amp;id=".$COURSE_ID."&amp;assessment_id=".$result["assessment_id"];
											echo "<tr id=\"assessment-".$result["assessment_id"]."\" class=\"assessment\">";
											if ($ENTRADA_ACL->amIAllowed("gradebook", "delete", false)) {
												echo "	<td class=\"modified\"><input type=\"hidden\" name=\"order[".$result['assessment_id']."][]\" value=\"".$result["order"]."\" class=\"order\" /><input class=\"delete\" type=\"checkbox\" name=\"delete[]\" value=\"".$result["assessment_id"]."\" /></td>\n";
											} else {
												echo "	<td class=\"modified\" width=\"20\"><input type=\"hidden\" name=\"order[".$result["assessment_id"]."][]\" value=\"sortorder\" class=\"order\" /><img src=\"".ENTRADA_URL."/images/pixel.gif\" width=\"19\" height=\"19\" alt=\"\" title=\"\" /></td>";
											}
											if ($ENTRADA_ACL->amIAllowed(new CourseContentResource($course_details["course_id"], $course_details["organisation_id"]), "update")) {
												echo "<td><a href=\"$url\">".$result["name"]."</a></td>";
												echo "<td><a href=\"$url\">".$result["grade_weighting"]. "%</a></td>";
											} else {
												echo "<td>".$result["name"]."</td>";
												echo "<td>".$result["grade_weighting"]. "%</td>";
											}
											
											$query =  "	SELECT a.`course_id`, a.`assignment_id`, a.`assignment_title` 
														FROM `assignments` a
														WHERE a.`assessment_id` = ".$db->qstr($result["assessment_id"])."
														AND a.`assignment_active` = 1";
											$assignment = $db->GetRow($query);	
											
											if ($assignment && $ENTRADA_ACL->amIAllowed(new AssignmentResource($course_details["course_id"], $course_details["organisation_id"], $assignment["assignment_id"]), "update")) {
												$url = ENTRADA_URL."/admin/gradebook/assignments?section=grade&amp;id=".$COURSE_ID."&amp;assignment_id=".$assignment["assignment_id"];
												echo "<td id=\"assignment-".$assignment["assignment_id"]."\">";
												echo "<a href=\"".ENTRADA_URL."/admin/gradebook/assignments?section=download-submissions&assignment_id=".$assignment["assignment_id"]."&id=" . $COURSE_ID . "\"><i class=\"icon-download-alt\"></i></a>";
												if ($ENTRADA_ACL->amIAllowed(new CourseContentResource($course_details["course_id"], $course_details["organisation_id"]), "update")) {
													echo "&nbsp;<a href=\"".ENTRADA_URL."/admin/gradebook/assignments?section=delete&id=".$COURSE_ID."&delete=".$assignment["assignment_id"]."\"><i class=\"icon-minus-sign\"></i></a>";
												}
												echo "&nbsp;<a href=\"".$url."\">".$assignment["assignment_title"]."</a>";																						
												echo "</td>";
											} else {
												echo "<td>\n";
												if ($ENTRADA_ACL->amIAllowed(new CourseContentResource($course_details["course_id"], $course_details["organisation_id"]), "update")) {
													echo "<a href=\"".ENTRADA_URL."/admin/gradebook/assignments?section=add&id=".$COURSE_ID."&assessment_id=".$result["assessment_id"]."\"><i class=\"icon-plus-sign\"></i> Add New Assignment</a>";
												} else {
													echo "Not a Dropbox Contact";
												}
												echo "</td>\n";
											}
											echo "</tr>";											
										}
									}
								}
								if ($count == 0) {
									?>
									<tr>
										<td colspan="4">
											There are currently no assessments entered for this course. <br />You can create new ones by clicking the <strong>Add New Assessment</strong> link above.
										</td>
									</tr>
									<?php
								}
								echo "<tr>";
								echo "	<td style=\"border-bottom: 0\" colspan=\"2\">&nbsp;</td>";
								echo "	<td style=\"".(($total_grade_weight < "100") ? "color: #ff2431; " : "")."border-bottom: 0\">". $total_grade_weight."%</td>";
								echo "</tr>";
							} else {
								?>
								<tr>
									<td colspan="4">
										There are currently no assessments entered for this course. <br />You can create new ones by clicking the <strong>Add New Assessment</strong> link above.
									</td>
								</tr>
								<?php
							}
						} else {
							?>
							<tr>
								<td colspan="4">
									There are currently no assessments entered for this course. <br />You can create new ones by clicking the <strong>Add New Assessment</strong> link above.
								</td>
							</tr>
							<?php
						}
					}
					?>
					</tbody>
				</table>
				<div class="gradebook_edit" style="display: none;"></div>
				<?php
				if ($ENTRADA_ACL->amIAllowed("gradebook", "delete", false)) {
					echo "</form>";
				}
			} else {
				// No assessments in this course.
				?>
				<div class="display-generic">
					<h3>No Assessments for <?php echo $course_details["course_name"]; ?></h3>
					There are currently no assessments entered for this course. You can create new ones by clicking the <strong>Add New Assessment</strong> link above.
				</div>
				<?php
			}
		} else {
			$url = ENTRADA_URL."/admin/gradebook";
			$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";
			
			$ERROR++;
			$ERRORSTR[] = "You do not have permission to view this Gradebook.<br /><br />You will now be redirected to the <strong>Gradebook index</strong> page.  This will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";

			echo display_error();

			application_log("notice", "Failed to provide a valid course identifer when attempting to view a gradebook");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to edit a course you must provide the courses identifier.";

		echo display_error();

		application_log("notice", "Failed to provide course identifer when attempting to view a gradebook");
	}
}
?>
