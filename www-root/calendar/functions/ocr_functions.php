<?php
/**
 * Online Course Resources [Pre-Clerkship]
 * @author Unit: Medical Education Technology Unit
 * @author Developer: Matt Simpson <simpson@post.queensu.ca>
 * @version 3.0
 * @copyright Copyright 2006 Queen's University, MEdTech Unit
 *
 * $Id: ocr_functions.php 1043 2010-02-12 21:19:55Z simpson $
*/

$showtabs		= "";
$lastupdated	= "";
$tab_list		= availableCalendarNames($username, $password, $ALL_CALENDARS_COMBINED);
$counter		= 0;
$total_tabs		= 0;

if(is_array($tab_list)) {
	@rsort($tab_list);
	if($total_tabs = @count($tab_list)) {
		$showtabs .= "<div id=\"screenTabs\">\n";
		$showtabs .= "	<div id=\"tabs\">\n";
		$showtabs .= "		<ul>\n";
		$showtabs .= "			<li class=\"first\"><a href=\"".ENTRADA_URL."\"><span>Home</span></a></li>\n";
		foreach($tab_list as $tab) {
			$counter++;
			$showtabs .= "		<li".(($tab == $cal) ? " id=\"current\"" : "")."".(($counter == $total_tabs) ? " class=\"last\"" : "")."><a href=\"{CURRENT_VIEW}.php?".replace_query(array("cal" => $tab))."\"><span>Class of ".$tab."</span></a></li>\n";
		}
		$showtabs .= "		</ul>\n";
		$showtabs .= "	</div>\n";
		$showtabs .= "</div>\n";
		
		if((int) $timestamp = filemtime($calendar_path."/".$cal.".ics")) {
			$lastupdated = "Last Updated: ".date(DEFAULT_DATE_FORMAT, $timestamp);
		}
	}
}

/**
 * Handy function that takes the QUERY_STRING and adds / modifies / removes elements from it
 * based on the $modify array that is provided.
 *
 * @param array $modify
 * @return string
 * @example echo "index.php?".replace_query(array("action" => "add", "step" => 2));
 */
function replace_query($modify = array(), $html_encode_output = false) {
	$process	= array();
	$tmp_string	= array();
	$new_query	= "";

	// Checks to make sure there is something to modify, else just returns the string.
	if(count($modify) > 0) {
		$original	= explode("&", $_SERVER["QUERY_STRING"]);
		if(count($original) > 0) {
			foreach($original as $value) {
				$pieces = explode("=", $value);
				// Gets rid of any unset variables for the URL.
				if(isset($pieces[0]) && isset($pieces[1])) {
					$process[$pieces[0]] = $pieces[1];
				}
			}
		}

		foreach($modify as $key => $value) {
			// If the variable already exists, replace it, else add it.
			if(array_key_exists($key, $process)) {
				if(($value === 0) || (($value) && ($value !=""))) {
					$process[$key] = $value;
				} else {
					unset($process[$key]);
				}
			} else {
				if(($value === 0) || (($value) && ($value !=""))) {
					$process[$key] = $value;
				}
			}
		}
		if(count($process) > 0) {
			foreach($process as $var => $value) {
				$tmp_string[] = $var."=".$value;
			}
			$new_query = implode("&", $tmp_string);
		} else {
			$new_query = "";
		}
	} else {
		$new_query = $_SERVER["QUERY_STRING"];
	}

	return (((bool) $html_encode_output) ? html_encode($new_query) : $new_query);
}

?>