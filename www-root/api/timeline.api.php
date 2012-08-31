<?php
/**
 * Online Course Resources [Pre-Clerkship]
 * @author Unit: Medical Education Technology Unit
 * @author Director: Dr. Benjamin Chen <bhc@post.queensu.ca>
 * @author Developer: Matt Simpson <simpson@post.queensu.ca>
 * @version 3.0
 * @copyright Copyright 2006 Queen's University, MEdTech Unit
 * 
 * $Id: timeline.api.php 1103 2010-04-05 15:20:37Z simpson $
*/

@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    dirname(__FILE__) . "/../core/includes",
    dirname(__FILE__) . "/../core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

if((isset($_SESSION["isAuthorized"])) && ((bool) $_SESSION["isAuthorized"])) {
	/**
	 * The query that is actually be searched for.
	 */
	if((isset($_GET["q"])) && (trim($_GET["q"]))) {
		$SEARCH_QUERY	= trim($_GET["q"]);
		
		if(strlen($SEARCH_QUERY) < 4) {
			$SEARCH_QUERY = str_pad($SEARCH_QUERY, 4, "*");
		}
	}
	
	/**
	 * The class of that will be outputted.
	 */
	if ((isset($_GET["c"])) && ($tmp_input = clean_input($_GET["c"], "alphanumeric"))) {
		$SEARCH_CLASS = $tmp_input;
	}
	
	/**
	 * Check if y variable is set for Academic year.
	 */
	if(isset($_GET["y"])) {
		$SEARCH_YEAR = (int) trim($_GET["y"]);
	
		$SEARCH_DURATION["start"]	= mktime(0, 0, 0, 9, 1, $SEARCH_YEAR);
		$SEARCH_DURATION["end"]		= strtotime("+1 year", $SEARCH_DURATION["start"]);
	}
	
	header("Content-Type: text/xml; charset=".DEFAULT_CHARSET);
	echo "<?xml version=\"1.0\" encoding=\"".DEFAULT_CHARSET."\" ?>\n";
	
	echo "<data>\n";
	if((isset($_SESSION["isAuthorized"])) && ($_SESSION["isAuthorized"]) && ($SEARCH_QUERY)) {
		$query = "	SELECT a.`event_id`, a.`event_title`, a.`event_goals`, a.`event_objectives`, a.`event_start`
					FROM `events` AS a
					LEFT JOIN `event_audience` AS b
					ON b.`event_id` = a.`event_id`
					WHERE b.`audience_type` = 'cohort'
					AND b.`audience_value` = ".$db->qstr($SEARCH_CLASS)."
					AND".(($SEARCH_YEAR) ? " (a.`event_start` BETWEEN ".$db->qstr($SEARCH_DURATION["start"])." AND ".$db->qstr($SEARCH_DURATION["end"]).") AND" : "")."
					MATCH (a.`event_title`, a.`event_description`, a.`event_goals`, a.`event_objectives`, a.`event_message`) AGAINST (".$db->qstr(str_replace(array("%", " AND ", " NOT "), array("%%", " +", " -"), $SEARCH_QUERY))." IN BOOLEAN MODE)
					ORDER BY a.`event_start` ASC, a.`event_title` ASC";
		$results = $db->GetAll($query);
		if($results) {
			foreach($results as $key => $result) {
				echo "\t<event start=\"".date("M j Y H:i:s \G\M\T", $result["event_start"])."\" title=\"".html_encode($result["event_title"])."\">\n";
				echo html_encode("<a href=\"".ENTRADA_URL."/events?id=".$result["event_id"]."\">".$result["event_title"]."</a>");
				echo "\t</event>\n";
			}
		}
	}
	echo "</data>\n";
} else {
	application_log("error", "Timeline API accessed without valid session_id.");	
}
?>