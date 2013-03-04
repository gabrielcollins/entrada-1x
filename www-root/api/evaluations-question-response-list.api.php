<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Serves the categories list up in a select box.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
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
require_once("Models/evaluation/Evaluation.class.php");

if (isset($_POST["response_text"]) && $_POST["response_text"]) {
	$question_data = json_decode($_POST["response_text"], true);
}

if (isset($_GET["responses"]) && $_GET["responses"]) {
	$responses = (int) $_GET["responses"];
}

$question_data["responses_count"] = $responses;

echo Evaluation::getQuestionResponseList($question_data);

?>
