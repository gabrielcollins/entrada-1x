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
 * Import One45 Response Rates into MedTech Central.
 *
 * This is a script that imports One45 Response Rates obtained obtained from the
 * One45 report function and saved as a CSV.
 *
 * Instructions:
 * 0. Backup the following database tables pg_eval_response_rates
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
			$program_response_rates = array();
			while (($row = fgetcsv($handle)) !== false) {
				$row_count++;
				echo "\n" . $row_count;
				//To Do: get the 4 dates from the file.
//				if ($row_count == 2) {
//					$end = strpos("to");
//					$from = substr($row[0], 16, 11);
//					$to = substr($row[0], 31, 12);
//					echo "\n" . $to;
//					exit;
//				}
//				if ($row_count == 3) {
//
//				}
//				if ($row_count == 4) {
//
//				}
				//We do not want the first six rows to be imported because that is the pre-amble.
				if ($row_count > 6) {
					$program_name = clean_input($row[0], array("trim"));
					//if we've hit the end of the program list, we must be at the Totals section and
					//we must account for an extra blank line.
					if ($program_name == "" || $program_name == null) {
						echo "\n******Entering Totals secton.";
						if (($row = fgetcsv($handle)) !== false) {
							$program_name = clean_input($row[0], array("trim"));
						}
					}

					echo "\nProgram: " . $program_name . "\n";
					print_r($row);
					//get the response rate results
					for ($i = 0; $i < 6; $i++) {
						if (($program_row = fgetcsv($handle)) !== false) {
							$row_count++;
							echo "\n" . $row_count;
							$response_type = $program_row[0];
							if (strcasecmp("Faculty", $response_type) == 0 || strcasecmp("Resident", $response_type) == 0) {
								$program_response_rates["program_name"] = $program_name;
								$program_response_rates["response_type"] = $response_type;
								$program_response_rates["completed"] = $program_row[1];
								$program_response_rates["distributed"] = $program_row[2];
								$program_response_rates["percent_complete"] = $program_row[3];
								$program_response_rates["gen_date"] = date("Y-m-d");

								if ($db->AutoExecute(DATABASE_NAME . ".pg_eval_response_rates", $program_response_rates, "INSERT")) {
									output_success("ROW: " . $row_count . " - Insert succeeded");
								} else {
									output_error("ROW: " . $row_count . " - Insert failed.  DB said: " . $db->ErrorMsg());
								}
							}

						}
					}
				}
			}
			fclose($handle);
			echo "Finished import\n";
		} else {
			output_error("Unable to open the provided CSV file [" . $CSV_FILE . "].");
		}
		break;
	case "-usage" :
	default :
		echo "\nUsage: import-community-guests.php [options] /path/to/import-file.csv";
		echo "\n   -usage               Brings up this help screen.";
		echo "\n   -import              Proceeds with import to database and sends e-mail.";
		break;
}
echo "\n\n";
?>