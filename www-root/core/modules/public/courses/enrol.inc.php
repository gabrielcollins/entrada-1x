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
} else {

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

	/**
	 * If we were going into the $COURSE_ID
	 */
	if ($COURSE_ID) {
		$query = "	SELECT b.`community_url` FROM `community_courses` AS a
					JOIN `communities` AS b
					ON a.`community_id` = b.`community_id`
					WHERE a.`course_id` = ".$db->qstr($COURSE_ID);
		$course_community = $db->GetOne($query);
		if ($course_community) {
			header("Location: ".ENTRADA_URL."/community".$course_community);
			exit;
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
			if (($ENTRADA_ACL->amIAllowed(new CourseResource($COURSE_ID, $ENTRADA_USER->getOrganisationId()), "read")) && ($course_details["allow_enroll"])) {
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

				// Meta information for this page.
				$PAGE_META["title"]			= $course_details["course_name"].(($course_details["course_code"]) ? ": ".$course_details["course_code"] : "")." - ".APPLICATION_NAME;
				$PAGE_META["description"]	= trim(str_replace(array("\t", "\n", "\r"), " ", html_encode(strip_tags($course_details["course_description"]))));
				$PAGE_META["keywords"]		= "";

				$course_details_section			= true;
				$course_description_section		= false;
				$course_objectives_section		= false;
				$course_assessment_section		= false;
				$course_textbook_section		= false;
				$course_message_section			= false;
				$course_resources_section		= true;
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
					<form action="<?php echo ENTRADA_URL.$MODULE; ?>?<?php echo replace_query(array("step" => 2)); ?>" method="post">
						<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/courses'" />
						<input type="submit" value="Enroll">
					</form>
				</div>
				<?php
			} else {
				$ERROR++;
				$ERRORSTR[] = "You do not have the permissions required to enrol in this course. If you believe that you have received this message in error, please contact a system administrator.";

				echo display_error();
			}
		}
	} else {
		echo display_error(array("You must provide a valid course identifier."));
	}
}