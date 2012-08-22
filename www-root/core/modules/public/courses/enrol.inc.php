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
 * This file displays the list of objectives pulled 
 * from the entrada.global_lu_objectives table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/
if((!defined("PARENT_INCLUDED")) || (!defined("IN_COURSES"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
		header("Location: ".ENTRADA_URL);
		exit;
} elseif ($COURSE_ID) {

	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/".$MODULE, "title" => "View " . $module_title);

	/**
	 * Check for groups which have access to the administrative side of this module
	 * and add the appropriate toggle sidebar item.
	 */
	if ($ENTRADA_ACL->amIAllowed("coursecontent", "update", false)) {
		switch ($_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]) {
			case "admin" :
				$admin_wording	= "Administrator View";
				$admin_url		= ENTRADA_URL."/admin/".$MODULE.(($COURSE_ID) ? "?".replace_query(array("section" => "edit", "id" => $COURSE_ID)) : "");
			break;
			case "pcoordinator" :
				$admin_wording	= "Coordinator View";
				$admin_url		= ENTRADA_URL."/admin/".$MODULE.(($COURSE_ID) ? "?".replace_query(array("section" => "content", "id" => $COURSE_ID)) : "");
			break;
			case "director" :
				$admin_wording	= "Director View";
				$admin_url		= ENTRADA_URL."/admin/".$MODULE.(($COURSE_ID) ? "?".replace_query(array("section" => "content", "id" => $COURSE_ID)) : "");
			break;
			default :
				$admin_wording	= "";
				$admin_url		= "";
			break;
		}

		$sidebar_html  = "<ul class=\"menu\">\n";
		$sidebar_html .= "	<li class=\"on\"><a href=\"".ENTRADA_URL."/".$MODULE.(($COURSE_ID) ? "?".replace_query(array("id" => $COURSE_ID, "action" => false)) : "")."\">Student View</a></li>\n";
		if (($admin_wording) && ($admin_url)) {
			$sidebar_html .= "<li class=\"off\"><a href=\"".$admin_url."\">".html_encode($admin_wording)."</a></li>\n";
		}
		$sidebar_html .= "</ul>\n";
	
		new_sidebar_item("Display Style", $sidebar_html, "display-style", "open");
	}
	if(!$ORGANISATION_ID){
		$query = "SELECT `organisation_id` FROM `courses` WHERE `course_id` = ".$db->qstr($COURSE_ID);
		if($result = $db->GetOne($query)){
			$ORGANISATION_ID = $result;
			$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["organisation_id"] = $result;
		}
		else
			$ORGANISATION_ID	= $_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["organisation_id"];
	}
	
	$COURSE_LIST = array();

	$results = courses_fetch_courses(true, false);
	if ($results) {
		foreach ($results as $result) {
			$COURSE_LIST[$result["course_id"]] = html_encode($result["course_name"].(($result["course_code"]) ? ": ".$result["course_code"] : ""));
		}
	}


	$query = "	SELECT * FROM `courses` 
				WHERE `course_id` = ".$db->qstr($COURSE_ID)."
				AND `course_active` = '1'";
	$course_details	= ((USE_CACHE) ? $db->CacheGetRow(CACHE_TIMEOUT, $query) : $db->GetRow($query));
	if (!$course_details) {
		$ERROR++;
		$ERRORSTR[] = "The course identifier that was presented to this page currently does not exist in the system.";

		echo display_error();
	} else {
		$query = "	SELECT a.*, c.`payment_model` FROM `payment_catalog` a
					LEFT JOIN `payment_options` b 
					ON a.`poption_id` = b.`poption_id`
					LEFT JOIN `payment_lu_types` c 
					ON b.`ptype_id` = c.`ptype_id`
					WHERE `item_type` = 'course' 
					AND `item_value` = ".$db->qstr($COURSE_ID)." 
					AND `active` = 1";			
		$course_cost_details = $db->GetRow($query);			
		if (($ENTRADA_ACL->amIAllowed(new CourseResource($COURSE_ID, $ENTRADA_USER->getOrganisationId()), "read")) && ($course_details["allow_enroll"])) {
									
			$query = "	SELECT * FROM `course_audience` 
						WHERE `audience_type` = 'proxy_id'
						AND `audience_value` = ".$db->qstr($ENTRADA_USER->getId())."
						AND `audience_active` != '0'";
			if ($enrolment_record = $db->GetRow($query)) {
				echo display_notice("You are alredy enrolled or have a pending enromment in this course.");
			} else {
				//add_statistic($MODULE, "view", "course_id", $COURSE_ID);

				$BREADCRUMB[] = array("url" => ENTRADA_URL."/".$MODULE."?".replace_query(array("id" => $course_details["course_id"])), "title" => $course_details["course_name"].(($course_details["course_code"]) ? ": ".$course_details["course_code"] : ""));

				$OTHER_DIRECTORS = array();

				$sub_query = "SELECT `proxy_id` FROM `course_contacts` WHERE `course_contacts`.`course_id`=".$db->qstr($COURSE_ID)." AND `course_contacts`.`contact_type` = 'director' ORDER BY `contact_order` ASC";
				$sub_results = $db->GetAll($sub_query);
				if ($sub_results) {
					foreach ($sub_results as $sub_result) {
						$OTHER_DIRECTORS[] = $sub_result["proxy_id"];
					}
				}

				switch($STEP){
					case 2:
						if(isset($_POST["cperiod_id"]) && $c_id = (int)$_POST["cperiod_id"]){
							$PROCESSED["cperiod_id"] = $c_id;
						}else {
							add_error("The <strong>Period</strong> is a required field.");
						}	

						if (!$ERROR) {
							$PROCESSED["course_id"] = $COURSE_ID;
							$PROCESSED["audience_type"] = "proxy_id";
							$PROCESSED["audience_value"] = $ENTRADA_USER->getId();
							$PROCESSED["enroll_start"] = time();
							$PROCESSED["enroll_finish"] = 0;
							$PROCESSED["audience_active"] = $course_cost_details?2:1;

							if ($db->AutoExecute("course_audience",$PROCESSED,"INSERT")) {
								if (!$course_cost_details) {
									add_success("Successfully enrolled in ".$course_details['course_name'].".");
									onload_redirect(ENTRADA_URL."/courses?id=".$COURSE_ID);
								} else {
									if ($payment = PaymentFactory::getPaymentModel($course_cost_details["payment_model"])) {
										$payment->addLineItem(array("name"=>$course_details["course_name"]." Payment","value"=>$course_cost_details["item_cost"],"catalog_id"=>$course_cost_details["pcatalog_id"]));
										$payment->fetchCredentials($course_cost_details["poption_id"]);
										try {
											$payment->createPayment();
											$payment->printForm();
										} catch(Exception $e) {
											add_error($e->getMessage());
										}								
									} else {
										add_error("An error occurred while trying to access payment information for ".$course_details["course_name"].". Please try again later.");
									}
								}
							} else {
								add_error("Error occurred while enrolling in course. Please try again later.");
							}

						}					
						if ($ERROR) {
							$STEP = 1;
						}
						break;
					case 1:
					default:
						break;
				}

				switch($STEP){
					case 2:										

						if ($SUCCESS) {
							echo display_success();
						}

						if ($NOTICE) {
							echo display_notice();
						}

						break;						
					case 1:
					default:

					if ($ERROR) {
						echo display_error();
					}					
				?>
				<div class="no-printing" style="text-align: right">
					<form>
					<label for="course-quick-select" class="content-small"><?php echo $module_singular_name; ?> Quick Select:</label>
					<select id="course-quick-select" name="course-quick-select" style="width: 300px" onchange="window.location='<?php echo ENTRADA_URL; ?>/courses?id='+this.options[this.selectedIndex].value">
					<option value="">-- Select a <?php echo $module_singular_name; ?> --</option>
					<?php
					foreach ($COURSE_LIST as $key => $course_name) {
						echo "<option value=\"".$key."\"".(($key == $COURSE_ID) ? " selected=\"selected\"" : "").">".$course_name."</option>\n";
					}
					?>
					</select>
					</form>
				</div>
				<div>
					<div class="no-printing" style="float: right; margin-top: 8px">
						<a href="<?php echo ENTRADA_URL."/".$MODULE."?id=".$course_details["course_id"]; ?>"><img src="<?php echo ENTRADA_URL; ?>/images/page-link.gif" width="16" height="16" alt="Link to this page" title="Link to this page" border="0" style="margin-right: 3px; vertical-align: middle" /></a> <a href="<?php echo ENTRADA_URL."/".$MODULE."?id=".$course_details["course_id"]; ?>" style="font-size: 10px; margin-right: 8px">Link to this page</a>
						<a href="javascript:window.print()"><img src="<?php echo ENTRADA_URL; ?>/images/page-print.gif" width="16" height="16" alt="Print this page" title="Print this page" border="0" style="margin-right: 3px; vertical-align: middle" /></a> <a href="javascript: window.print()" style="font-size: 10px; margin-right: 8px">Print this page</a>
					</div>

					<h1><?php echo html_encode($course_details["course_name"].(($course_details["course_code"]) ? ": ".$course_details["course_code"] : "")); ?></h1>
				</div>
				<a name="course-enrol-section"></a>
				<h2 title="Course Enrol Section"><?php echo $module_singular_name; ?> Enrolment</h2>
				<div id="course-enrol-section">
					<form action="<?php echo ENTRADA_URL."/courses";?>?<?php echo replace_query(array("section"=>"enrol","step" => 2)); ?>" method="post">
						<table class="tableList">
							<tbody>
								<tr>
									<td style="width:20%;"><label for="cperiod_id">Period:</label></td>
									<td>
										<select name="cperiod_id" id="cperiod_id" style="width:100%;">
											<option value="0">--Select Period--</option>
											<?php 
											$query = "	SELECT * FROM `curriculum_periods` 
														WHERE `curriculum_type_id` = ".$db->qstr($course_details["curriculum_type_id"])."
														AND `active` = '1'";
											$periods = $db->GetAll($query);
											if ($periods) {
												foreach($periods as $period){
													?><option value="<?php echo $period["cperiod_id"];?>"><?php echo date("M d/y",$period["start_date"]).' - '.date("M d/y",$period["finish_date"]);?></option><?php
												}
											} 												
											?>
										</select>
									</td>
								</tr>
							</tbody>
						</table>					
						<?php 
						if ($course_cost_details) {							
						?>					
						<table class="tableList">
							<thead>
								<tr>
									<th style="text-align:right;">Course Name</th>
									<th style="text-align:right;width:25%">Course Cost</th>
									<th style="text-align:right;width:25%">Course Vacancies</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td style="text-align:right;"><?php echo $course_details["course_name"];?></td>
									<td style="text-align:right;width:25%"><?php echo '$'.$course_cost_details["item_cost"];?></td>
									<td style="text-align:right;width:25%"><?php echo $course_cost_details["quantity"] == '-1'?'Unlimited':$course_cost_details["quantity"];?></td>
								</tr>
							</tbody>
						</table>
						<br/>
						<?php
						} else {
							echo display_notice("This course has no costs associated with it. Click Enroll to continute enrolment into the course.");
						}
						?>
						<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/courses'" />
						<input type="submit" value="Enroll">
					</form>
				</div>
				<?php
				break;
				}
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "You do not have the permissions required to enrol in this course or this course doesn't allow self enrolment. If you believe that you have received this message in error, please contact a system administrator.";

			echo display_error();
		}
	}	
} else {
	echo display_error(array("You must provide a valid course identifier."));
}