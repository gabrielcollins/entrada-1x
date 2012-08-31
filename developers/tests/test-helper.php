<?php
/**
 * Entrada Tools
 * Tools: Test Helper
 * 
 * This is a script that you can use to move a standard set of test data (the fixtures)
 * in and out of a seperate set of test databases. To run the test suites you must have 
 * this test data in a configured test database.
 * 
 * Instructions:
 * 0. Configure the test databases in fixtures/test-config.php. The Entrada boot process will
 *    use this configuration for testing instead of your normal one.
 * 
 * 1. Run "./test-helper.php -r" to reload the databases from the included test fixture files
 * 
 * 2. Optional. If you make changes to the test suite and need to update the fixture files
 *    run "./test-helper.php -d" to dump out the files into the fixture folder. 
 *
 * @author Unit: Medical Education Technology Unit
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 * $Id: $
 */
// Let Entrada know it's being tested and point it to an alternate configuration for fixture data
define("ENTRADA_TESTING", true);
define("ENTRADA_TESTING_CONFIG", dirname(__FILE__) . "/fixtures/test-config.inc.php");
require_once(dirname(__FILE__) . "/../tools/bootstrap.php");
require_once(dirname(__FILE__) . "/../../www-root/setup/includes/Entrada_Setup.php");

define("DEFAULT_DUMP", false); // Don't dump the databases every time
define("DEFAULT_RELOAD", false); // Don't Reload the databases every time

// Allow dumping and reloading of test database
$opts = getopt("drh");

// Print help
if(isset($opts["h"])) {
	echo "Usage: php test-helper.php [-hrd]
	-d : dump the configured test databases into the fixture SQL files
	-r : reload the test databases from the fixture SQL files
	-h : print out this help.
";
	exit;
}
//Dump test databases
if(isset($opts["d"]) || DEFAULT_DUMP) {
	exit("Not implemented yet. \n");
}

//Reload test databases
if (false) { //(isset($opts["r"]) || DEFAULT_RELOAD) {
	$setup = new Entrada_Setup(array(
				"database_host" 		=> DATABASE_HOST,
				"database_username" 	=> DATABASE_USER,
				"database_password" 	=> DATABASE_PASS,
				"entrada_database" 		=> DATABASE_NAME,
				"auth_database" 		=> AUTH_DATABASE,
				"clerkship_database" 	=> CLERKSHIP_DATABASE,
				"admin_firstname" 		=> "John",
				"admin_lastname" 		=> "Doe",
				"admin_email" 			=> "joe@example.com",
				"admin_username" 		=> "admin",
				"admin_password_hash"	=> md5("apple123")));
				
	$setup->sql_dump_entrada 	= dirname(__FILE__)."/fixtures/entrada.sql";
	$setup->sql_dump_auth 	    = dirname(__FILE__)."/fixtures/entrada_auth.sql";
	$setup->sql_dump_clerkship 	= dirname(__FILE__)."/fixtures/entrada_clerkship.sql";
	
	if($setup->resetDatabases()) {
		echo "Test Databases reset. \n";
	} else {
		exit("Unable to reset test databases! Exiting.");
	}
}

function get_entrada_user($id) {
	global $db;
	$user = new EntradaUser("user".$id);
	$user->details = $db->GetRow("SELECT a.*, b.* FROM `".AUTH_DATABASE."`.`user_data` AS a LEFT JOIN `".AUTH_DATABASE."`.`user_access` as b ON b.`user_id` = a.`id` AND b.`app_id` = 1 WHERE a.`id` = $id");
	return $user;
}