<?php
/**
 * Online Course Resources [Pre-Clerkship]
 * @author Unit: Medical Education Technology Unit
 * @author Director: Dr. Benjamin Chen <bhc@post.queensu.ca>
 * @author Developer: Matt Simpson <simpson@post.queensu.ca>
 * @version 3.0
 * @copyright Copyright 2006 Queen's University, MEdTech Unit
 * 
 * Cron needs run the following command at any interval of your choice in order to update the syllabi.
 * I would suggest running it every 6 hours, because it's sort of extensive on the server.
 * /home/ccs/qlib/apps/php/bin/php -f /export/home/hippocrates/courses/www/cron/syllabus_exec.php
 *
 * $Id: syllabus_exec.php 1103 2010-04-05 15:20:37Z simpson $
*/
ini_set("display_errros",1);
@set_time_limit(0);
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

$classes = array (
	// Class of 2014
	10 => array ( // from entrada.groups table.
		"grad_year" => "2013",
		"terms" => array (
			1 => array ( // from entrada.curriculum_lu_types table.
				"title" => "Term 1",
				"start_date" => strtotime("Sept 1st, 2009"),
				"end_date" => strtotime("Dec 31st, 2009"),
			),
			2 => array (
				"title" => "Term 2",
				"start_date" => strtotime("Jan 1st, 2010"),
				"end_date" => strtotime("May 31st, 2010"),
			),
			3 => array (
				"title" => "Term 3",
				"start_date" => strtotime("Sept 1st, 2010"),
				"end_date" => strtotime("Dec 31st, 2010"),
			),
			4 => array (
				"title" => "Term 4",
				"start_date" => strtotime("Jan 1st, 2011"),
				"end_date" => strtotime("May 31st, 2011"),
			),
			5 => array ( // from entrada.curriculum_lu_types table.
				"title" => "Term 1",
				"start_date" => strtotime("Sept 1st, 2011"),
				"end_date" => strtotime("Dec 31st, 2011"),
			)
		)
	),
    // Class of 2014
	11 => array ( // from entrada.groups table.
		"grad_year" => "2014",
		"terms" => array (
			1 => array ( // from entrada.curriculum_lu_types table.
				"title" => "Term 1",
				"start_date" => strtotime("Sept 1st, 2010"),
				"end_date" => strtotime("Dec 31st, 2010"),
			),
			2 => array (
				"title" => "Term 2",
				"start_date" => strtotime("Jan 1st, 2011"),
				"end_date" => strtotime("May 31st, 2011"),
			),
			3 => array (
				"title" => "Term 3",
				"start_date" => strtotime("Sept 1st, 2011"),
				"end_date" => strtotime("Dec 31st, 2011"),
			),
			4 => array (
				"title" => "Term 4",
				"start_date" => strtotime("Jan 1st, 2012"),
				"end_date" => strtotime("May 31st, 2012"),
			),
			5 => array ( // from entrada.curriculum_lu_types table.
				"title" => "Term 1",
				"start_date" => strtotime("Sept 1st, 2012"),
				"end_date" => strtotime("Dec 31st, 2012"),
			),
		)
	),
	// Class of 2015
    12 => array ( // from entrada.groups table.
		"grad_year" => "2015",
		"terms" => array (
			1 => array ( // from entrada.curriculum_lu_types table.
				"title" => "Term 1",
				"start_date" => strtotime("Sept 1st, 2011"),
				"end_date" => strtotime("Dec 31st, 2011"),
			),
			2 => array (
				"title" => "Term 2",
				"start_date" => strtotime("Jan 1st, 2012"),
				"end_date" => strtotime("May 31st, 2012"),
			),
			3 => array (
				"title" => "Term 3",
				"start_date" => strtotime("Sept 1st, 2012"),
				"end_date" => strtotime("Dec 31st, 2012"),
			),
			4 => array (
				"title" => "Term 4",
				"start_date" => strtotime("Jan 1st, 2013"),
				"end_date" => strtotime("May 31st, 2013"),
			),
			5 => array (
				"title" => "Term 4",
				"start_date" => strtotime("Sept 1st, 2013"),
				"end_date" => strtotime("Dec 31st, 2013"),
			)
		)
	)
);

if((is_array($APPLICATION_PATH)) && (isset($APPLICATION_PATH["htmldoc"])) && (@is_executable($APPLICATION_PATH["htmldoc"]))) {
	$output_file	= ENTRADA_ABSOLUTE."/syllabi/class-of-%GRADYEAR%_term-%TERM%_%COURSECODE%.pdf";
	$exec_command	= $APPLICATION_PATH["htmldoc"]." \
	--format pdf14 \
	--charset ".DEFAULT_CHARSET." \
	--size Letter \
	--pagemode document \
	--portrait \
	--no-duplex \
	--overflow \
	--top 1cm \
	--bottom 1cm \
	--left 2cm \
	--right 2cm \
	--header \
	--footer \
	--embedfonts \
	--bodyfont Helvetica \
	--headfootsize 10 \
	--headfootfont Courier \
	--firstpage p1 \
	--titleimage ".ENTRADA_URL."/images/syllabus_logo.gif \
	--quiet \
	--book '".ENTRADA_URL."/cron/syllabus_gen.php?%PARAMS%' \
	--outfile ".$output_file;
	
	$current_date = strtotime(date("M jS, Y"));
	
	foreach ($classes as $cohort_id => $cohort) {

		foreach ($cohort["terms"] as $term_id => $term ) {
			
			if($current_date >= $term["start_date"] && $current_date <= $term["end_date"]) {
				// fetch the active courses
				$query = "	SELECT a.`course_id`, a.`course_code`
							FROM `courses` AS a 
							JOIN `curriculum_lu_types` AS b 
							ON a.`curriculum_type_id` = b.`curriculum_type_id` 
							WHERE a.`curriculum_type_id` = ".$db->qstr($term_id)."
							AND a.`course_active` = 1";
				$courses = $db->GetAssoc($query);

				foreach ($courses as $course_id => $course_code) {
					$params = "cohort=".$cohort_id."&term=".$term_id."&course_id=".$course_id."&start_date=".$term["start_date"]."&end_date=".$term["end_date"]."&course_code=".$course_code;
					$filename	= str_replace(array("%GRADYEAR%", "%TERM%", "%COURSECODE%"), array($cohort["grad_year"], $term_id, strtolower($course_code)), $output_file);
					$command	= str_replace("%PARAMS%", $params, str_replace(array("%GRADYEAR%", "%TERM%", "%COURSECODE%"), array($cohort["grad_year"], $term_id, strtolower($course_code)), $exec_command));
					application_log("success", "Generated: ".$filename);
					@exec($command);
					@chmod($filename, 0644);
				}
			}
		}
	}
	
} else {
	application_log("error", "Unable to locate the executable HTMLDoc application that is required to generate the syllabus'");
}
?>