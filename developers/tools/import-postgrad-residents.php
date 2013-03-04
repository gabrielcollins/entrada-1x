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
 * Import of resident data.
 *
 * This is a script that imports resident data into the Medtech Auth database
 * into the user_data_resident table.  It first checks to see if the resident
 * already has an entry in the user_data table and is allowed to access
 * Medtech Central and is a member of the resident group.
 *
 * Instructions:
 * 0. Backup the databases *always* before importing new users.
 *
 * @author Unit: Medical Education Technology Unit
 * @author Developer: Don Zuiker <don.zuiker@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 *
 */
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . "/includes");

@ini_set("auto_detect_line_endings", 1);
@ini_set("magic_quotes_runtime", 0);
set_time_limit(0);

if ((!isset($_SERVER["argv"])) || (@count($_SERVER["argv"]) < 1)) {
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

$ACTION = ((isset($_SERVER["argv"][1])) ? trim($_SERVER["argv"][1]) : "-usage");
$CSV_FILE = (((isset($_SERVER["argv"][2])) && (trim($_SERVER["argv"][2]) != "")) ? trim($_SERVER["argv"][2]) : false);

switch ($ACTION) {
	case "-import" :
		$handle = fopen($CSV_FILE, "r");
		if ($handle) {
			$row_count = 0;

			while (($row = fgetcsv($handle)) !== false) {
				$row_count++;

				/**
				 * We do not want the first row to be imported because it should
				 * be the CSV heading titles.
				 */
				/**
				 * Resident dump file headers: 
				 * First_Name Last_Name CMPA_NO CPSO_No Student_No Program_Code Year Level	school_id assess_prog_img assess_prog_non_img
				 */
				if ($row_count > 1) {
					$resident = array();
					$resident["first_name"] = clean_input($row[0], array("trim"));
					$resident["last_name"] = clean_input($row[1], array("trim"));
					$resident["cmpa_no"] = clean_input($row[2], array("trim")); //can be a non-int like "DND"
					$resident["cpso_no"] = clean_input($row[3], array("trim", "int"));
					$resident["student_no"] = clean_input($row[4], array("trim", "int"));
					$temp_resident["program_code"] = clean_input($row[5], array("trim"));
					$temp_resident["category_code"] = clean_input($row[6], array("trim"));
					$resident["school_id"] = clean_input($row[7], array("trim", "int"));
					$resident["assess_prog_img"] = clean_input($row[8], array("trim"));
					$resident["assess_prog_non_img"] = clean_input($row[9], array("trim"));

					//Convert the CAPER Medical School Codes to MOH school codes.
					switch($resident["school_id"]) {
						case 42:
							$resident["school_id"] = 4;
							break;
						case 45:
							$resident["school_id"] = 6;
							break;
						case 41:
							$resident["school_id"] = 3;
							break;
						case 46:
							$resident["school_id"] = 2;
							break;
						case 43:
							$resident["school_id"] = 5;
							break;
						case 44:
							$resident["school_id"] = 1;
							break;
						default:
							$resident["school_id"] = 7;
					}
					
					if (!$resident["first_name"]) {
						output_error("No first name found for " . $resident["first_name"] . " " . $resident["last_name"] . ", student number: " . $resident["student_no"]);
					}
					if (!$resident["last_name"]) {
						output_error("No last name found for " . $resident["first_name"] . " " . $resident["last_name"] . ", student number: " . $resident["student_no"]);
					}
					if (!$resident["cpso_no"]) {
						output_error("No cpso no found for " . $resident["first_name"] . " " . $resident["last_name"] . ", student number: " . $resident["student_no"]);
					}
					if ($resident["cpso_no"] != "DND" && !$resident["cmpa_no"]) {
						output_error("No cmpa no found for " . $resident["first_name"] . " " . $resident["last_name"] . ", student number: " . $resident["student_no"]);
					}
					if (!$resident["school_id"]) {
						output_error("No school code found for " . $resident["first_name"] . " " . $resident["last_name"] . ", student number: " . $resident["student_no"]);
					}

					//Get the program_id and category_id from the respective codes.
					$query = "SELECT * FROM `" . DATABASE_NAME . "`.`mtd_moh_program_codes` WHERE `program_code` = " . $db->qstr($temp_resident["program_code"]);
					$result = $db->GetRow($query);
					if ($result) {
						$resident["program_id"] = $result["id"];
					}
					else {
						output_error("No program code found for " . $resident["first_name"] . " " . $resident["last_name"] . ", student number: " . $resident["student_no"]);
						$query = "SELECT id FROM `" . DATABASE_NAME . "`.`mtd_moh_program_codes` WHERE `program_code` = UKN";
						$id = $db->GetOne($query);
						$resident["program_id"] = $id;
					}

					$query = "SELECT * FROM `" . DATABASE_NAME . "`.`mtd_categories` WHERE `category_code` = " . $db->qstr($temp_resident["category_code"]);
					$result = $db->GetRow($query);
					if ($result) {
						$resident["category_id"] = $result["id"];
					}
					else {
						output_error("No category code found for " . $resident["first_name"] . " " . $resident["last_name"] . ", student number: " . $resident["student_no"]);
						$query = "SELECT id FROM `" . DATABASE_NAME . "`.`mtd_categories` WHERE `category_code` = UKN";
						$id = $db->GetOne($query);
						$resident["category_id"] = $id;
					}

					$query = "SELECT a.`id` as 'proxy_id', b.`group` as 'group' FROM `" . AUTH_DATABASE . "`.`user_data` a, `" . AUTH_DATABASE . "`.`user_access` b
						      WHERE a.`number` = " . $db->qstr($resident["student_no"]) .
							" AND b.`app_id` = 1
							  AND a.id = b.user_id";
					
					$result = $db->GetRow($query);

					if ($result) {
						$resident["proxy_id"] = $result["proxy_id"];

						//update Queen's Medical School Alumni to resident
						if ($result["group"] != "resident") {
							$record["group"] = "resident";
							$record["role"] = "resident";

							if (!$db->AutoExecute(AUTH_DATABASE . ".user_access", $record, 'UPDATE', 'user_id = ' . $resident["proxy_id"] . ' AND app_id = 1')) {
								output_error("Could not update student to resident group and role.  Student number: " . $resident["student_no"]);
							} else {
								output_notice("Updated the group and role of student no. " . $resident["student_no"] . " to resident.");
							}
						}

						if ($db->AutoExecute(AUTH_DATABASE . ".user_data_resident", $resident, "INSERT")) {
							output_success("ROW: " .$row_count . " - Insert of resident proxy_id[" . $resident["proxy_id"] .  "] succeeded");
						}
						else {
							output_error("ROW: " .$row_count . " - Insert of resident proxy_id[" . $resident["proxy_id"] .  "] failed.  DB said: " . $db->ErrorMsg());
						}
					}
					else {
						echo "\nResident not found. Student No.: " . $resident["student_no"];
					}					
				}
			}
			fclose($handle);
			echo "\nFinished import\n";
		} else {
			output_error("Unable to open the provided CSV file [" . $CSV_FILE . "].");
		}
		break;
	case "-usage" :
	default :
		echo "\nUsage: import-community-guests.php [options] /path/to/import-file.csv";
		echo "\n   -usage               Brings up this help screen.";
		echo "\n   -import              Proceeds with import to database.";
		break;
}
echo "\n\n";
?>