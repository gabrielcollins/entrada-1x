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
 * Primary controller file for the Objectives module.
 * /objectives
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if (!defined("PARENT_INCLUDED") || !defined("IN_COURSES")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} else {
	define("IN_OBJECTIVES",	true);

	if (($router) && ($router->initRoute())) {
		$BREADCRUMB[] = array("url" => ENTRADA_RELATIVE."/courses/objectives", "title" => "Curriculum Objectives");

		$PREFERENCES = preferences_load($MODULE);
		
		if((isset($_GET["id"])) && ((int) trim($_GET["id"]))) {
			$COMPETENCY_ID = (int) trim($_GET["id"]);
		}
	
		if((isset($_GET["oid"])) && ((int) trim($_GET["oid"]))) {
			$OBJECTIVE_ID = (int) trim($_GET["oid"]);
		}
	
		if((isset($_GET["cid"])) && ((int) trim($_GET["cid"]))) {
			$COURSE_ID = (int) trim($_GET["cid"]);
		}
	
		if((isset($_GET["api"])) && (trim($_GET["api"]))) {
			$API = ($_GET["api"] == "true" ? true : false);
		}
		
		$module_file = $router->getRoute();
		if ($module_file) {
			require_once($module_file);
		}
		
		/**
		 * Check if preferences need to be updated on the server at this point.
		 */
		preferences_update($MODULE, $PREFERENCES);
	} else {
		$url = ENTRADA_URL;
		application_log("error", "The Entrada_Router failed to load a request. The user was redirected to [".$url."].");

		header("Location: ".$url);
		exit;
	}
}