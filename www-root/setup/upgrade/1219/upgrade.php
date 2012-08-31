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
 * Entrada upgrade helper.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Brandon Thorn <bt37@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 * 
*/

@set_time_limit(0);
@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../../../core",
    dirname(__FILE__) . "/../../../core/includes",
    dirname(__FILE__) . "/../../../core/library",
    get_include_path(),
)));

require_once("config/config.inc.php");
require_once "Zend/Loader/Autoloader.php";
$loader = Zend_Loader_Autoloader::getInstance();
require_once("config/settings.inc.php");
require_once("Entrada/adodb/adodb.inc.php");
require_once("functions.inc.php");
require_once("dbconnection.inc.php");

if((!isset($_SERVER["argv"])) || (@count($_SERVER["argv"]) < 1)) {
	echo "<html>\n";
	echo "<head>\n";
	echo "	<title>Processing Error</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	echo "This file should be run by command line only:";
	echo "<div style=\"font-family: monospace\">/usr/bin/php -f ".__FILE__."</div>";
	echo "</body>\n";
	echo "</html>\n";
	exit;
}

echo "\n\n";

$query = "INSERT INTO `topic_organisation` SELECT a.`topic_id`, b.`organisation_id` FROM `events_lu_topics` AS a JOIN `".AUTH_DATABASE."`.`organisations` AS b ON 1=1";

if ($db->Execute($query)) {
	echo "Successfully inserted values.";
} else {
	echo "Error while inserting values.";
}

echo "\n\n";