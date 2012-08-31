<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Serves the categories list up in a select box.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
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

if ((isset($_SESSION["isAuthorized"])) && ((bool) $_SESSION["isAuthorized"])) {
	if ($ENTRADA_ACL->amIAllowed("dashboard", "update")) {
		$MODULE = "dashboard";
		
		switch($_GET["action"]) {
			case "save":
				$PREFERENCES = preferences_load($MODULE);
				$default_feeds = dashboard_fetch_feeds(true); //true fetches only the defaults and no personalized feeds
				
				
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["feed_break"] = $_POST["break"];
				
				//Massage the POST'd data into a easier understood format: [1 => [title, url], 2 => [title, url] ... ]
				$new_feeds = array();
				foreach($_POST as $attribute => $array) {
					if($attribute != "break" && $attribute != "_" ) {
						foreach($array as $index => $value) {
							$new_feeds[$index][$attribute] = $value;
						}
					}
				}
				
				//Find all the non removable urls from the default feeds
				$non_removable_feed_urls = array();
				foreach ($default_feeds as $key => $array) {
					if(isset($array["removable"]) && !$array["removable"]) {
						$non_removable_feed_urls[] = $array["url"];
					}
				}
				
				//Ensure all the non removable urls have their removable status preserved
				foreach($new_feeds as $key => &$array) {
					if(in_array($array["url"], $non_removable_feed_urls)) {
						$array["removable"] = false;
					} else {
						$array["removable"] = true;
					}
				}
				
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["feeds"] = $new_feeds;
				preferences_update($MODULE);
				break;
			case "reset":
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["feeds"] = null;
				$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["feed_break"] = -1;
				break;
			default:
				echo "Unrecognized Action!";
				break;
		}
	}
}
?>
