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
 * Generates some SQL to create random users for Entrada testing data.
 *
 * @author Unit: Medical Education Technology Unit
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 */

$user_data		= array();
$user_access	= array();

$firstnames		= array("Steve", "Anne", "Robert", "Andrew", "Matt", "Amy", "James", "Larry", "Marta", "Suzanne", "Taylor", "Pat", "Doug", "Walter", "Judith", "Renee", "Trent", "Victor", "Dominic", "Dominique", "Cynthia", "Clair", "Howard", "Patrick", "Jenny", "Jennifer", "Susie");
$lastnames		= array("Robertson", "Sampson", "Totannia", "Summers", "Turcot", "Blueson", "Karington", "Andrews", "Jamieson", "Walters", "Douglas", "Patterson", "Eldridge", "Meadows", "Cyr", "Ostapha", "Foriendie", "Wauters");

$group			= "student";
$role			= "2011";

foreach	(range(5, 55) as $proxy_id) {
	$user_data[]	= "(".$proxy_id.", 0, '".$group.$proxy_id."', MD5('apple123'), 1, NULL, '', '".$firstnames[array_rand($firstnames)]."', '".$lastnames[array_rand($lastnames)]."', '".$group.$proxy_id."@demo.entrada-project.org', '', NULL, '', '', '', '', '', '', '', '', NULL, 0, 0)";
	$user_access[]	= "(NULL, ".$proxy_id.", 1, 'true', ".time().", 0, 0, '', NULL, NULL, '".$role."', '".$group."', '', '')";
}

echo "INSERT INTO `user_data` (`id`, `number`, `username`, `password`, `organisation_id`, `department`, `prefix`, `firstname`, `lastname`, `email`, `email_alt`, `google_id`, `telephone`, `fax`, `address`, `city`, `province`, `postcode`, `country`, `notes`, `office_hours`, `privacy_level`, `notifications`) VALUES\n";
echo implode(",\n", $user_data).";\n\n";

echo "INSERT INTO `user_access` (`id`, `user_id`, `app_id`, `account_active`, `access_starts`, `access_expires`, `last_login`, `last_ip`, `login_attempts`, `locked_out_until`, `role`, `group`, `extras`, `notes`) VALUES\n";
echo implode(",\n", $user_access).";\n\n";
?>