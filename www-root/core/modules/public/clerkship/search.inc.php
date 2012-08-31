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
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2009 Queen's University. All Rights Reserved.
 *
*/
$DISPLAY	= false;

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_CLERKSHIP"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('clerkshipschedules', 'read')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	// Setup internal variables.
	$DISPLAY		= true;
}

if ($DISPLAY) {
	if (($_GET["gradyear"]) || ($_GET["gradyear"] === "0")) {
		$GRADYEAR = trim($_GET["gradyear"]);
		@app_setcookie("search[gradyear]", trim($_GET["gradyear"]));
	} elseif (($_POST["gradyear"]) || ($_POST["gradyear"] === "0")) {
		$GRADYEAR	= trim($_POST["gradyear"]);
		@app_setcookie("search[gradyear]", trim($_POST["gradyear"]));
	} elseif (isset($_COOKIE["search"]["gradyear"])) {
		$GRADYEAR = $_COOKIE["search"]["gradyear"];
	} else {
		$GRADYEAR = 0;	
	}
	
	$GRADYEAR = clean_input($GRADYEAR, "credentials");
	
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/clerkship?".replace_query(array("section" => "search")), "title" => "Student Search");
	
	switch ($_POST["action"]) {
		case "results" :
			?>
			<div class="content-heading">Student Search Results</div>
			<br />
			<?php
			if (trim($_GET["year"]) != "" || trim($_POST["year"]) != "") {
				if (trim($_POST["year"]) != "") {
					$query_year = trim($_POST["year"]);
				} else {
					$query_year = trim($_GET["year"]);
				}
				
				$query	= "SELECT `".AUTH_DATABASE."`.`user_data`.`id` AS `proxy_id`, CONCAT_WS(', ', `".AUTH_DATABASE."`.`user_data`.`lastname`, `".AUTH_DATABASE."`.`user_data`.`firstname`) AS `fullname` 
				FROM `".AUTH_DATABASE."`.`user_data` 
				LEFT JOIN `".AUTH_DATABASE."`.`user_access` ON `".AUTH_DATABASE."`.`user_access`.`user_id`=`".AUTH_DATABASE."`.`user_data`.`id` 
				WHERE `".AUTH_DATABASE."`.`user_access`.`app_id`='".AUTH_APP_ID."' 
				AND `role`=".$db->qstr(trim($query_year), get_magic_quotes_gpc())." 
				AND `group`='student' 
				ORDER BY `".AUTH_DATABASE."`.`user_data`.`lastname`, `".AUTH_DATABASE."`.`user_data`.`firstname` ASC";
				
				$results	= $db->GetAll($query);
				
				if ($results) {
					$counter	= 0;
					$total	= count($results);
					$split	= (round($total / 2) + 1);
					
					echo "There are a total of <b>".$total."</b> student".(($total != "1") ? "s" : "")." in the class of <b>".checkslashes(trim($query_year))."</b>. Please choose a student you wish to work with by clicking on their name, or if you wish to add an event to multiple students simply check the checkbox beside their name and click the &quot;Add Mass Event&quot; button.";

					echo "<form action=\"".ENTRADA_URL."/clerkship?section=add_elective\" method=\"post\">\n";
					echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
					echo "<tr>\n";
					echo "	<td style=\"vertical-align: top\">\n";
					echo "		<ol start=\"1\">\n";
					foreach ($results as $result) {
						
						$elective_weeks = clerkship_get_elective_weeks($result["proxy_id"]);
						$remaining_weeks = (int)$CLERKSHIP_REQUIRED_WEEKS - (int)$elective_weeks["approved"];
						
						switch (htmlentities($_POST["qualifier"])) {
							case "*":
							default:
								$show 			= true;
								$noResults		= "No Results";
								break;
						}
						
						if ($show) {
							$counter++;
							if ($counter == $split) {
								echo "		</ol>\n";
								echo "	</td>\n";
								echo "	<td style=\"vertical-align: top\">\n";
								echo "		<ol start=\"".$split."\">\n";
							}
							echo "	<li><input type=\"checkbox\" name=\"ids[]\" value=\"".$result["proxy_id"]."\" />&nbsp;<a href=\"".ENTRADA_URL."/clerkship?section=view&ids=".$result["proxy_id"]."\" style=\"font-weight: bold\">".$result["fullname"]."</a>".$weeksOutput."</li>\n";
						}
					}
					
					if ($counter == 0) {
						echo "	<li>".$noResults."</li>\n";
					}
					echo "		</ol>\n";
					echo "	</td>\n";
					echo "</tr>\n";
					echo "<tr>\n";
					echo "	<td colspan=\"3\" style=\"border-top: 1px #333333 dotted; padding-top: 5px\">\n";
					echo "		<ul type=\"none\">\n";
					echo "		<li><input type=\"checkbox\" name=\"selectall\" value=\"1\" onClick=\"selection(this.form['ids[]'])\" />&nbsp;";
					echo "		<input type=\"submit\" value=\"Add Mass Event\" class=\"button\" style=\"background-image: url('".ENTRADA_URL."/images/btn_bg.gif')\" /></li>\n";
					echo "		</ul>\n";
					echo "	</td>\n";
					echo "</tr>\n";
					echo "</table>\n";
					echo "</form>\n";
				} else {
					$ERROR++;
					$ERRORSTR[] = "Unable to find students in the database with a graduating year of <b>".trim($query_year)."</b>. It's possible that these students are not yet added to this system, so please check the User Management module.";

					echo "<br />";
					echo display_error($ERRORSTR);
				}
			} elseif (trim($_GET["name"]) != "" || trim($_POST["name"]) != "") {
				if (trim($_POST["name"]) != "") {
					$query_name = trim($_POST["name"]);
				} else {
					$query_name = trim($_GET["name"]);
				}
				$query	= "SELECT `".AUTH_DATABASE."`.`user_data`.`id` AS `proxy_id`, CONCAT_WS(', ', `".AUTH_DATABASE."`.`user_data`.`lastname`, `".AUTH_DATABASE."`.`user_data`.`firstname`) AS `fullname`, `".AUTH_DATABASE."`.`user_access`.`role` AS `gradyear` FROM `".AUTH_DATABASE."`.`user_data` LEFT JOIN `".AUTH_DATABASE."`.`user_access` ON `".AUTH_DATABASE."`.`user_access`.`user_id`=`".AUTH_DATABASE."`.`user_data`.`id` WHERE `".AUTH_DATABASE."`.`user_access`.`app_id`='".AUTH_APP_ID."' AND CONCAT(`".AUTH_DATABASE."`.`user_data`.`firstname`, `".AUTH_DATABASE."`.`user_data`.`lastname`) LIKE '%".checkslashes(trim($query_name))."%' AND `group`='student' ORDER BY `".AUTH_DATABASE."`.`user_data`.`lastname`, `".AUTH_DATABASE."`.`user_data`.`firstname` ASC";
				$results	= $db->GetAll($query);
				if ($results) {
					$counter	= 0;
					$total	= count($results);
					$split	= (round($total / 2) + 1);
					
					echo "There are a total of <b>".$total."</b> student".(($total != "1") ? "s" : "")." that match the search term of <b>".checkslashes(trim($query_name), "display")."</b>. Please choose a student you wish to work with by clicking on their name, or if you wish to add an event to multiple students simply check the checkbox beside their name and click the &quot;Add Mass Event&quot; button.";

					echo "<form action=\"".ENTRADA_URL."/clerkship?section=add_elective\" method=\"post\">\n";
					echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
					echo "<tr>\n";
					echo "	<td style=\"vertical-align: top\">\n";
					echo "		<ol start=\"1\">\n";
					foreach ($results as $result) {
						$counter++;
						if ($counter == $split) {
							echo "		</ol>\n";
							echo "	</td>\n";
							echo "	<td style=\"vertical-align: top\">\n";
							echo "		<ol start=\"".$split."\">\n";
						}
						echo "	<li><input type=\"checkbox\" name=\"ids[]\" value=\"".$result["proxy_id"]."\" />&nbsp;<a href=\"".ENTRADA_URL."/clerkship?section=view&ids=".$result["proxy_id"]."\" style=\"font-weight: bold\">".$result["fullname"]."</a> <span class=\"content-small\">(Class of ".$result["gradyear"].")</span></li>\n";
					}
					echo "		</ol>\n";
					echo "	</td>\n";
					echo "</tr>\n";
					echo "<tr>\n";
					echo "	<td colspan=\"3\" style=\"border-top: 1px #333333 dotted; padding-top: 5px\">\n";
					echo "		<ul type=\"none\">\n";
					echo "		<li><input type=\"checkbox\" name=\"selectall\" value=\"1\" onClick=\"selection(this.form['ids[]'])\" />&nbsp;";
					echo "		<input type=\"submit\" value=\"Add Mass Event\" class=\"button\" style=\"background-image: url('".ENTRADA_URL."/images/btn_bg.gif')\" /></li>\n";
					echo "		</ul>\n";
					echo "	</td>\n";
					echo "</tr>\n";
					echo "</table>\n";
					echo "</form>\n";
				} else {
					$ERROR++;
					$ERRORSTR[] = "Unable to find any students in the database matching <b>".checkslashes(trim($query_name), "display")."</b>. It's possible that the student you're looking for is not yet added to this system, so please check the User Management module.";

					echo "<br />";
					echo display_error($ERRORSTR);
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "You must search either by graduating year or by students name at this time, please try again.";
				
				echo "<br />";
				echo display_error($ERRORSTR);
			}
		break;
		default :
			?>			
			<div class="content-heading">Student Search</div>
			<br />
			<form "<?php echo ENTRADA_URL; ?>/clerkship?section=search" method="post">
			<input type="hidden" name="action" value="results" />
			<table cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td colspan="3"><span class="content-subheading">Graduating Year</span></td>
			</tr>
			<tr>
				<td>Select the graduating year you wish to view students in:</td>
				<td style="padding-left: 10px">
					<select name="year" style="width: 205px">
					<option value="">-- Select Graduating Year --</option>
					<?php
					if (isset($SYSTEM_GROUPS["student"]) && !empty($SYSTEM_GROUPS["student"])) {
						foreach ($SYSTEM_GROUPS["student"] as $class) {
							echo "<option value=\"".$class."\">Class of ".html_encode($class)."</option>\n";
						}
					}
					?>
					</select>
				</td>
				<td style="padding-left: 10px"><input type="submit" value="Proceed" /></td>
			</tr>
			<tr>
				<td colspan="3">
					<br />
					<b>- OR -</b>
					<br /><br />
				</td>
			</tr>
			<tr>
				<td colspan="3"><span class="content-subheading">Student Finder</span></td>
			</tr>
			<tr>
				<td>Enter the first or lastname of the student:</td>			
				<td style="padding-left: 10px">
					<input type="text" name="name" value="" style="width: 200px" />
				</td>
				<td style="padding-left: 10px"><input type="submit" value="Search" /></td>
			</tr>
			</table>
			</form>
			<br /><br />
			<?php
		break;
	}
} else {
	// Display the errors.
	echo display_error($ERRORSTR);
}