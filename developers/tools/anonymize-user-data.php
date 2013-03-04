#!/usr/bin/php
<?php
/**
 * Entrada Tools [ http://www.entrada-project.org ]
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
 * Tools: Anonymize User Data
 *
 * Takes production data and performs the following actions:
 * - Replaces staff / student numbers with general information.
 * - Randomize first and last names.
 * - Replace e-mail addresses with a general one.
 * - Reset passwords to password.
 *
 * This script needs to be updated to handle users who do not have a record
 * in the user_access table. 
 *
 * @author Unit: Medical Education Technology Unit
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 */

set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__)."/includes");

set_time_limit(0);

if((!isset($_SERVER["argv"])) || (@count($_SERVER["argv"]) < 1)) {
	echo "<html>\n";
	echo "<head>\n";
	echo "	<title>Processing Error</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	echo "This file should be run by command line only.";
	echo "</body>\n";
	echo "</html>\n";
	exit;
}

require_once("classes/adodb/adodb.inc.php");
require_once("config.inc.php");
require_once("dbconnection.inc.php");
require_once("functions.inc.php");

echo "\nAnonymizing user data on " . DATABASE_HOST;

/**
 * Reset password to password
 * Empty department, email_alt, google_id, telephone, address, city, province, country, and office hours.
 */
$query = "UPDATE `".AUTH_DATABASE."`.`user_data` SET `password` = MD5('password'), `department` = NULL, `prefix` = '', `email_alt` = '', `google_id` = '', `telephone` = '', `address` = '', `city` = 'Kingston', `province` = '', `country` = '', `country_id` = 39, `province_id` = 9, `notifications` = 1, `office_hours` = ''";
$db->Execute($query);

$query = "	SELECT a.*, b.`group`
			FROM `".AUTH_DATABASE."`.`user_data` AS a
			JOIN `".AUTH_DATABASE."`.`user_access` AS b
			ON b.`user_id` = a.`id`
			WHERE b.`app_id` = '1'
			ORDER BY a.`id` ASC";

$results = $db->GetAll($query);
if ($results) {
	//Group type of Other added to handle all other groups than those listed.
	//The Group type is used as part of creating a unique username.
	$users = array("student" => 0, "faculty" => 0, "staff" => 0, "resident" => 0, "medtech" => 0, "other" => 0);
	$count = 0;
	foreach ($results as $key => $result) {
	
		if (array_key_exists($result["group"], $users)) {
			$username_prefix = $result["group"];
			$username_postfix = $users[$result["group"]]++;
		} else {
			$username_prefix = "other";
			$username_postfix = $users["other"]++;
		}
		
		$number = (2432154 + $key);
		$firstname = $db->GetOne("SELECT `firstname` FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` <> '".$result["id"]."' ORDER BY RAND() LIMIT 1");
		$lastname = $db->GetOne("SELECT `lastname` FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` <> '".$result["id"]."' ORDER BY RAND() LIMIT 1");
		$privacy = rand(0, 3);
		$gender = rand(1, 2);
		$username = $username_prefix.$username_postfix;
		
		$query = "	UPDATE `".AUTH_DATABASE."`.`user_data` SET
					`number` = '".(int) $number."',
					`username` = ".$db->qstr($username).",
					`firstname` = ".$db->qstr($firstname).",
					`lastname` = ".$db->qstr($lastname).",
					`email` = ".$db->qstr($username."@demo.entrada-project.org").",
					`telephone` = '613-533-6000',
					`privacy_level` = '".(int) $privacy."',
					`gender` = '".(int) $gender."'
					WHERE `id` = '".$result["id"]."'";

		$update = $db->Execute($query);
		if ($update) {
			$count++;
		}
		else {
			echo "\nError updating user id: " . $result["id"] . ". Database said: " . $db->ErrorMsg();
		}
	}
	echo "\nNumber of rows updated: " . $count . "\n";
}
