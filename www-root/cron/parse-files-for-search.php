<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Batch process for parsing the content of all event files and loading that 
 * into the database so that it can be made to be searchable.
 *
 * @author Scott Steil <sasteil@ucalgary.ca>
 *
 */

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
require_once('Entrada/FileToText.php');

$sql = "SELECT *
		FROM `event_files`
		WHERE `file_contents` IS NULL
		   OR `file_contents` = ''";
$results = $db->GetAll($sql);
$num_results = count($results);

if ($num_results > 0)
{
	$num_updated = 0;
	
	foreach($results as $result)
	{
		$path = FILE_STORAGE_PATH.'/'.$result['efile_id'];
		$pathinfo = pathinfo($result['file_name']);
		$ext = $pathinfo['extension'];
		
 		$result['file_contents'] = FileToText::decode($path, $ext);

		if (!$db->AutoExecute("event_files", $result, "UPDATE", "efile_id = ".$result['efile_id'])) 
		{
			application_log("error", "Unable to update file id [".$result['efile_id']."]. Database said: ".$db->ErrorMsg());
		}
		else
		{
			$num_updated++;
		}
	}
	
	echo "$num_updated of $num_results files were parsed and updated.";
}
else
{
	echo "No files to update.";
}