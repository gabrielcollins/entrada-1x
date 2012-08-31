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
 * Outputs the requested event file id to the users web browser.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
*/

@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/core",
    dirname(__FILE__) . "/core/includes",
    dirname(__FILE__) . "/core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

if ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL.((isset($_SERVER["REQUEST_URI"])) ? "?url=".rawurlencode(clean_input($_SERVER["REQUEST_URI"], array("nows", "url"))) : ""));
	exit;
} else {
	require_once("Entrada/rss.class.php");
	if (!isset($_GET["url"])) {
		header("HTTP/1.0 418 I'm A Teapot", false, 418);
	} else {
		header("Content-type: application/json");
		
		$rss_reader = new rssFeed();
		
		$results = $rss_reader->fetch(html_entity_decode($_GET["url"]));
		$feed = array("channel_title" => $results["title"], "url" => $_GET["url"], "items" => array());
		
		foreach ($results["items"] as $key => $fetched_item) {
			$item = array("title" => $fetched_item["title"], "description" => $fetched_item["description"], "link" => $fetched_item["link"]);
			$feed["items"][] = $item;
		}
		
		echo json_encode($feed);
	}
}