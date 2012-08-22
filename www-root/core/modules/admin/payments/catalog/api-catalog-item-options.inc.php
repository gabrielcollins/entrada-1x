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
 * This API file returns an HTML table of the possible audience information
 * based on the selected course.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 */
if ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("event", "create", false)) {
	add_error("You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$GROUP."] and role [".$ROLE."] do not have access to this module [".$MODULE."]");
} else {
	if (isset($_POST["ajax"]) && ($_POST["ajax"] == 1)) {
		$use_ajax = true;
	} else {
		$use_ajax = false;
	}
	if (isset($_POST["ignore_prev"]) && ($_POST["ignore_prev"] == 1)) {
		$ignore_prev = true;
	} elseif(!isset($ignore_prev)) {
		$ignore_prev = false;
	}
	if (!isset($PROCESSED["item_value"]) || !$PROCESSED["item_value"]) {
		$PROCESSED["item_value"] = 0;
	}

	if ($use_ajax) {
		/**
		 * Clears all open buffers so we can return a plain response for the Javascript.
		 */
		ob_clear_open_buffers();

		$PROCESSED = array();
		$PROCESSED["item_type"] = 0;
		$EVENT_ID = 0;

		if (isset($_POST["item_type"]) && ($tmp_input = clean_input($_POST["item_type"], array("notags","trim")))) {
			$PROCESSED["item_type"] = $tmp_input;
		}
	}

	if ($PROCESSED["item_type"]) {
		switch($PROCESSED["item_type"]){
			case 'course':
				$query = "	SELECT * FROM `payment_catalog` 
							WHERE `item_type` = 'course' 
							AND `active` = '1'";
				$existing_course_items = $db->GetAll($query);
				$results = courses_fetch_courses(true, false);
				if ($results) {
					foreach ($results as $result) {
						if ($ignore_prev || !in_results($existing_course_items,'item_value',$result["course_id"])) {
							$COURSE_LIST[$result["course_id"]] = html_encode($result["course_name"].(($result["course_code"]) ? ": ".$result["course_code"] : ""));
						}
					}
				}
				if ($COURSE_LIST) {
				?>
				<td></td>
				<td><label for="item_value" class="form-required">Course</label></td>
				<td>
					<select name="item_value">
						<option value="0">-- Select a Course --</option>
						<?php
						foreach ($COURSE_LIST as $key => $course_name) {
							echo "<option value=\"".$key."\"".(($key == $PROCESSED["item_value"]) ? " selected=\"selected\"" : "").">".$course_name."</option>\n";
						}
						?>
					</select>				
				</td>
				<?php
				} else {
					?><td>&nbsp;</td><td><label for="item_value" class="form-required">Course</label></td><td><?php echo display_notice("No courses available to be added. You may already have created catalog items out of all the courses you have access to.");?></td><?php
				}
				break;
			default:
				break;
		}
	}
	

	/**
	 * If we are return this via Javascript,
	 * exit now so we don't get the entire page.
	 */
	if ($use_ajax) {
		exit;
	}
}