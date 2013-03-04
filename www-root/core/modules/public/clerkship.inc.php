 <?php
/**
 * 
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
 * Module:	Clerkship
 * Area:	Public
 * @author Unit: Medical Education Technology Unit
 * @author Director: Dr. Benjamin Chen <bhc@post.queensu.ca>
 * @author Developer: Matt Simpson <simpson@post.queensu.ca>
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @version 3.0
 * @copyright Copyright 2007 Queen's University, MEdTech Unit
 *
 * $Id: clerkship.inc.php 391 2009-01-05 14:16:18Z ad29 $
*/

if(!defined("PARENT_INCLUDED")) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('clerkship', 'read')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	define("IN_CLERKSHIP", true);
 
	$EVENT_ID			= 0;
	$NOTIFICATION_ID	= 0;
	$STEP				= 1;
	$PROCESSED			= array();

	$MODULE				= "clerkship";
	$COMPONENT			= "";
	$SECTION			= "index";
	$ACTION				= "";

	$PREFERENCES		= preferences_load($MODULE);

	/**
	 * Gets the full component path to load based on $PATH_INFO (defined in index.php / admin.php).
	 */
	if ($tmp_input = parse_url($PATH_INFO, PHP_URL_PATH)) {
		$tmp_input = parse_url($PATH_INFO, PHP_URL_PATH);
		$component_path = preg_replace(array("%^/%", "%/$%"), "", $tmp_input);
	} else {
		$component_path = "";
	}

	/**
	 * This section is simply copied and pasted from the Clerkship system.
	 * Analysis will have to be done whether or not this should be here or
	 * included form.
	 */
	$FIELD_STATUS						= array();
	$FIELD_STATUS["published"]			= array("name" => "Published", "visible" => true);
	$FIELD_STATUS["draft"]				= array("name" => "Draft", "visible" => true);
	$FIELD_STATUS["approval"]			= array("name" => "Awaiting Approval", "visible" => false);
	$FIELD_STATUS["trash"]				= array("name" => "Trash", "visible" => false);
	$FIELD_STATUS["cancelled"]			= array("name" => "Cancelled", "visible" => false);

	$FIELD_FORMTYPE						= array();
	$FIELD_FORMTYPE["rotation"]			= array("name" => "Clinical Rotation", "visible" => true);
	$FIELD_FORMTYPE["teacher"]			= array("name" => "Clinical Teacher", "visible" => true);
	$FIELD_FORMTYPE["clerkship"]		= array("name" => "Clerkship", "visible" => true);

	$FIELD_QUESTIONSTYLE				= array();
	$FIELD_QUESTIONSTYLE["horizontal"]	= array("name" => "Horizontal", "visible" => true);
	$FIELD_QUESTIONSTYLE["vertical"]	= array("name" => "Vertical", "visible" => true);
	$FIELD_QUESTIONSTYLE["none"]		= array("name" => "None", "visible" => false);

	$FIELD_ANSWERS						= array();
	$FIELD_ANSWERS["strongly_disagree"]	= array("name" => "Strongly Disagree", "type"=> "radio", "value" => 1, "visible" => true);
	$FIELD_ANSWERS["disagree"]	  		= array("name" => "Disagree", "type"=> "radio", "value" => 2, "visible" => true);
	$FIELD_ANSWERS["neutral"]	  		= array("name" => "Neutral", "type"=> "radio", "value" => 3, "visible" => true);
	$FIELD_ANSWERS["agree"]	  			= array("name" => "Agree", "type"=> "radio", "value" => 4, "visible" => true);
	$FIELD_ANSWERS["strongly_agree"]	= array("name" => "Strongly Agree", "type"=> "radio", "value" => 5, "visible" => true);

	$FIELD_ANSWERTYPE					= array();
	$FIELD_ANSWERTYPE["radio"]			= array("name" => "Radio", "value" => "radio");
	$FIELD_ANSWERTYPE["comment"]		= array("name" => "Comment", "value" => "comment");

	$FIELD_TYPE							= array();
	$FIELD_TYPE["clinical"]				= array("name" => "Clerkship", "visible" => true);
	$FIELD_TYPE["academic"]				= array("name" => "Pre-Clerkship", "visible" => false);

	$FIELD_ACCESS						= array();
	$FIELD_ACCESS["public"]				= array("name" => "Public", "visible" => true);
	$FIELD_ACCESS["private"]			= array("name" => "Private", "visible" => false);
	$FIELD_ACCESS["shared"]				= array("name" => "Shared", "visible" => false);

	if(isset($_GET["section"])) {
		if(trim($_GET["section"]) != "") {
			$SECTION = clean_input($_GET["section"], "url");
		}
	}

	if(isset($_GET["action"])) {
		if(trim($_GET["action"]) != "") {
			$ACTION = clean_input($_GET["action"], "url");
		}
	}

	if((isset($_GET["step"])) && ((int) trim($_GET["step"]))) {
		$STEP = (int) trim($_GET["step"]);
	} elseif((isset($_POST["step"])) && ((int) trim($_POST["step"]))) {
		$STEP = (int) trim($_POST["step"]);
	}

	if((isset($_GET["id"])) && ((int) trim($_GET["id"]))) {
		$EVENT_ID = (int) trim($_GET["id"]);
	}

	if((isset($_GET["nid"])) && ((int) trim($_GET["nid"]))) {
		$NOTIFICATION_ID = (int) trim($_GET["nid"]);
	}

	if((isset($_GET["core"])) && ((int) trim($_GET["core"]))) {
		$rotation = (int) trim($_GET["core"]);
	} else {
	    $rotation = 0;
	}

	$BREADCRUMB[] = array("url" => ENTRADA_URL."/".$MODULE, "title" => "Clerkship");
	
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/clerkship.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/tabpane/tabpane.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";

	$HEAD[] = "<link href=\"".ENTRADA_URL."/css/tabpane.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
	$HEAD[] = "<link href=\"".ENTRADA_URL."/css/calendar.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";

	/**
	 * Add the schedule correction sidebar item.
	 */
	$sidebar_html  = "<a href=\"javascript:sendClerkship('".ENTRADA_URL."/agent-clerkship.php')\"><img src=\"".ENTRADA_URL."/images/icon-important.gif\" width=\"48\" height=\"48\" alt=\"Clerkship Schedule Correction\" title=\"Clerkship Schedule Correction\" border=\"0\"  border=\"0\" align=\"right\" hspace=\"3\" vspace=\"5\" /></a>\n";
	$sidebar_html .= "<strong>Spot a schedule problem?</strong> To report any issues with this clerkship schedule, please <a href=\"javascript:sendClerkship('".ENTRADA_URL."/agent-clerkship.php')\" style=\"font-size: 11px; font-weight: bold\">click here</a>.\n";

	new_sidebar_item("Schedule Corrections", $sidebar_html, "page-clerkship", "open");
	
	if ($ENTRADA_ACL->amIAllowed('clerkshipschedules', 'read')) {
		/**
		 * Add the student search sidebar item.
		 */
		$sidebar_html  = "<form action=\"".ENTRADA_URL."/clerkship?section=search\" method=\"post\" style=\"display: inline\">\n";
		$sidebar_html .= "<label for=\"name\" class=\"form-nrequired\">Student Search:</label><br />";
		$sidebar_html .= "<input type=\"text\" id=\"name\" name=\"name\" value=\"\" style=\"width: 95%\" /><br />\n";
		$sidebar_html .= "<input type=\"hidden\" name=\"action\" value=\"results\" />";
		$sidebar_html .= "<span style=\"float: left; padding-top: 7px;\"><a href=\"".ENTRADA_URL."/clerkship?section=search\" style=\"font-size: 11px\">Advanced Search</a></span>\n";
		$sidebar_html .= "<span style=\"float: right; padding-top: 4px;\"><input type=\"submit\" class=\"button-sm\" value=\"Search\" /></span>\n";
		$sidebar_html .= "</form>\n";
	
		new_sidebar_item("View Schedule", $sidebar_html, "search", "open");
	}
	
	if ($_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"] == "student") {
		/**
		 * Process local page actions.
		 */
		$elective_weeks		= clerkship_get_elective_weeks($ENTRADA_USER->getID());
		$remaining_weeks	= ((int) $CLERKSHIP_REQUIRED_WEEKS - (int) $elective_weeks["approved"]);

		$sidebar_html  = "<ul class=\"menu\">\n";
		$sidebar_html .= "	<li><a href=\"".ENTRADA_URL."/clerkship/electives?section=view&type=approval\"><strong>".$elective_weeks["approval"]."</strong> Pending Approval</a></li>\n";
		$sidebar_html .= "	<li class=\"checkmark\"><a href=\"".ENTRADA_URL."/clerkship/electives?section=view&type=published\"><strong>".$elective_weeks["approved"]."</strong> Weeks Approved</a></li>\n";
		$sidebar_html .= "	<li class=\"incorrect\"><a href=\"".ENTRADA_URL."/clerkship/electives?section=view&type=rejected\"><strong>".$elective_weeks["trash"]."</strong> Weeks Rejected</a></li>\n";
		$sidebar_html .= "	<br />";
		if((int)$elective_weeks["approval"] + (int)$elective_weeks["approved"] > 0) {
			$sidebar_html .= "	<li><a href=\"".ENTRADA_URL."/clerkship/electives?section=disciplines\">Discipline Breakdown</a></li>\n";
		}
		$sidebar_html .= "</ul>\n";

		$sidebar_html .= "<div style=\"margin-top: 10px\">\n";
		$sidebar_html .= "	You have ".$remaining_weeks." required elective week".(($remaining_weeks != 1) ? "s" : "")." remaining.\n";
		if ($remaining_weeks > 0) {
			$sidebar_html .= "	To submit electives for approval, <a href=\"".ENTRADA_URL."/clerkship/electives?section=add\" style=\"font-size: 11px; font-weight: bold\">click here</a>.";
		}
		$sidebar_html .= "</div>\n";

		new_sidebar_item("Elective Weeks", $sidebar_html, "page-clerkship", "open");

		/* Logbook Review setup */
		$query				= "	SELECT *
								FROM `".CLERKSHIP_DATABASE."`.`events` AS a
								LEFT JOIN `".CLERKSHIP_DATABASE."`.`event_contacts` AS b
								ON b.`event_id` = a.`event_id`
								LEFT JOIN `".CLERKSHIP_DATABASE."`.`regions` AS c
								ON c.`region_id` = a.`region_id`
								WHERE a.`event_finish` >= ".$db->qstr(strtotime("00:00:00", time()))."
								AND (a.`event_status` = 'published' OR a.`event_status` = 'approval')
								AND b.`econtact_type` = 'student'
								AND b.`etype_id` = ".$db->qstr($ENTRADA_USER->getActiveId())."
								ORDER BY a.`event_start` ASC";
		
		$clerkship_schedule	= $db->GetAll($query);
		$query						= "	SELECT *
										FROM `".CLERKSHIP_DATABASE."`.`events` AS a
										LEFT JOIN `".CLERKSHIP_DATABASE."`.`event_contacts` AS b
										ON b.`event_id` = a.`event_id`
										LEFT JOIN `".CLERKSHIP_DATABASE."`.`regions` AS c
										ON c.`region_id` = a.`region_id`
										WHERE a.`event_finish` <= ".$db->qstr(strtotime("00:00:00", time()))."
										AND (a.`event_status` = 'published' OR a.`event_status` = 'approval')
										AND b.`econtact_type` = 'student'
										AND b.`etype_id` = ".$db->qstr($ENTRADA_USER->getActiveId())."
										ORDER BY a.`event_start` ASC";
		$clerkship_past_schedule	= $db->GetAll($query);
		
		if ($clerkship_schedule[0]["event_start"] <= time() || isset($clerkship_past_schedule) && $clerkship_past_schedule) {
			$ROTATION_ID = $clerkship_schedule[0]["rotation_id"]; 
			$SHOW_LOGBOOK = ((((int)$_SESSION["details"]["role"]) <= date("Y", strtotime("+1 year"))) ? true : false);
		
			$clinical_rotation	 	= clerkship_get_rotation(($rotation ? $rotation : ($ROTATION_ID ? $ROTATION_ID : 0)));
			$rotation				= $clinical_rotation["id"];
			$clinical_encounters	= clerkship_get_rotation_overview($rotation);
			
			$objectives_required = 0;
		    $objectives_recorded = 0;
		    if ($rotation < 10) {
				$query = "	SELECT `objective_id`, MAX(`number_required`) AS `required`
							FROM `".CLERKSHIP_DATABASE."`.`logbook_mandatory_objectives`
							WHERE `rotation_id` = ".$db->qstr($ROTATION_ID)."
							GROUP BY `objective_id`";
				$required_objectives = $db->GetAll($query);
				if ($required_objectives) {
					foreach ($required_objectives as $required_objective) {
						$objectives_required += $required_objective["required"];
						$number_required[$required_objective["objective_id"]] = $required_objective["required"];
								$query = "SELECT COUNT(a.`objective_id`) AS `recorded`
										FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_objectives` AS a
										JOIN `".CLERKSHIP_DATABASE."`.`logbook_entries` AS b
										ON a.`lentry_id` = b.`lentry_id`
										AND b.`entry_active` = '1'
										AND b.`proxy_id` = ".$db->qstr($ENTRADA_USER->getID())."
										WHERE a.`objective_id` = ".$db->qstr($required_objective["objective_id"])."
										GROUP BY a.`objective_id`";
						$recorded = $db->GetOne($query);
						
						if ($recorded) {
							if ($required_objective["required"] > $recorded) {
								if ($objective_ids) {
									$objective_ids .= ",".$db->qstr($required_objective["objective_id"]);
								} else {
									$objective_ids = $db->qstr($required_objective["objective_id"]);
								}
								$number_required[$required_objective["objective_id"]] -= $recorded;
							}
							$objectives_recorded += ($recorded <= $required_objective["required"] ? $recorded : $required_objective["required"]);
						} else {
							if ($objective_ids) {
								$objective_ids .= ",".$db->qstr($required_objective["objective_id"]);
							} else {
								$objective_ids = $db->qstr($required_objective["objective_id"]);
							}
						}
					}
				}
		    }
			$remaining_weeks	 	= clerkship_get_rotation_schedule($rotation);
			$sidebar_html  			= "<center><a href=\"".ENTRADA_URL."/clerkship/logbook?section=select\"><strong>$clinical_rotation[title]</strong></a></center><br>";
			$sidebar_html 			.= "<ul class=\"menu\">\n";
			$sidebar_html 			.= "	<li class=\"incorrect\"><a href=\"".ENTRADA_URL."/clerkship/logbook?section=view&type=missing&core=$rotation\"><strong>".($objectives_required-$objectives_recorded)."</strong>  CPs Not Seen</a></li>\n";
			$sidebar_html 			.= "	<li class=\"checkmark\"><a href=\"".ENTRADA_URL."/clerkship/logbook?section=view&type=mandatories&core=$rotation\"><strong>".($objectives_recorded)."</strong>  CPs Seen</a></li>\n";
			$sidebar_html 			.= "	<li><a href=\"".ENTRADA_URL."/clerkship/logbook?section=view&type=procedures&core=$rotation\"><strong>".$clinical_encounters["procedures"]."</strong> Procedures</a></li>\n";
			$sidebar_html 			.= "</ul>\n";
			$sidebar_html .= "	<a href=\"".ENTRADA_URL."/clerkship/logbook?section=add&event=".$clerkship_schedule[0]["event_id"]."\">Log encounter</a>\n";
			if((int)$clinical_encounters["entries"] > 0) {
				$sidebar_html .= "	<br /><br /><a href=\"".ENTRADA_URL."/clerkship/logbook?sb=rotation&rotation=".$rotation."\">View ".($clinical_encounters["entries"]==1?"entry":"entries - $clinical_encounters[entries]")."</a>\n";
			}
			if ($rotation) {
				$sidebar_html .= "<div style=\"margin-top: 10px\">\n";
				$sidebar_html .= "	You have ".$remaining_weeks["yet"]." weeks remaining in this ".$remaining_weeks["total"]." week clerkship.<br /> To change rotations, <a href=\"".ENTRADA_URL."/clerkship/logbook?section=select\">click here</a>.";
				$sidebar_html .= "</div>\n";
				if ($SHOW_LOGBOOK) {
					new_sidebar_item("Logbook Entries", $sidebar_html, "page-clerkship", "open");
				}
			}
		}
	}

	if (($router) && ($router->initRoute())) {
		$PREFERENCES = preferences_load($MODULE);
		if((isset($_GET["step"])) && ((int) trim($_GET["step"]))) {
			$STEP = (int) trim($_GET["step"]);
		} elseif((isset($_POST["step"])) && ((int) trim($_POST["step"]))) {
			$STEP = (int) trim($_POST["step"]);
		} else {
			$STEP = 1;
		}
		
		
		$module_file = $router->getRoute();
		if ($module_file) {
			require_once($module_file);
		}
		
		/**
		 * Check if preferences need to be updated on the server at this point.
		 */
		preferences_update($MODULE, $PREFERENCES);
	}
}