<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Used to view the details of / download the specified file within a folder.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Brandon Thorn <brandon.thorn@queensu.ca>
  * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 * 
*/
if ((!defined("IN_GRADEBOOK"))) {
	exit;
} 
if (!$RECORD_ID) {
	if (isset($_GET["id"]) && $tmp = clean_input($_GET["id"], "int")) {
		$RECORD_ID = $tmp;
	}
}

$query = "SELECT * FROM `assignment_contacts` WHERE `assignment_id` = ".$db->qstr($RECORD_ID)." AND `proxy_id` = ".$ENTRADA_USER->getProxyId();
if ($iscontact = $db->GetRow($query)) {
	$USER_ID = $tmp;
} else {
	$USER_ID = false;
}

if ($RECORD_ID) {
	if ($USER_ID) {	
		
		$query = "SELECT a.*, b.`course_code` FROM `assignments` AS a JOIN `courses` AS b ON a.`course_id` = b.`course_id` WHERE a.`assignment_id` = ".$db->qstr($RECORD_ID)." AND a.`assignment_active` = '1'";
		$assignment = $db->GetRow($query);
		if ($assignment) {

			/**
			 * Download the latest version.
			 */
			$query	= " SELECT a.*, CONCAT_WS('_',b.`firstname`,b.`lastname`) AS `username`, b.`number` FROM `assignment_file_versions` AS a
						JOIN `".AUTH_DATABASE."`.`user_data` AS b 
						ON a.`proxy_id` = b.`id` 
						WHERE `afversion_id` IN(
							SELECT MAX(`afversion_id`) FROM `assignment_file_versions` AS a
							JOIN `assignment_files` AS b
							ON a.`afile_id` = b.`afile_id`
							AND b.`file_type` = 'submission'
							WHERE a.`assignment_id` = ".$RECORD_ID." 
							GROUP BY a.`afile_id`
						)";
			$result	= $db->GetAll($query);
			$dir = FILE_STORAGE_PATH."/zips";
			if ( !file_exists($dir) ) {
			  mkdir ($dir, 0777);
			}
			$zip_file_name = $assignment["course_code"]."_".str_replace(' ', '_', $assignment["assignment_title"]).'.zip';
			$zipname = $dir."/".$zip_file_name;
			if ($result) {

				$zip = new ZipArchive();
				$res = $zip->open($zipname,ZIPARCHIVE::OVERWRITE);
				if($res !== true) {
					$ERROR++;
					$ERRORSTR[] = "<strong>Unable to create the file archive.</strong><br /><br />The archive of files was not created. Try again or contact ____________.";
				}else{
						foreach($result as $file){
							$zip->addFile(FILE_STORAGE_PATH."/A".$file["afversion_id"],$file["number"]."_".$file["username"]."_".$file["file_filename"]);
						}

						$file_version = array();
						$file_version["afversion_id"] = $result["afversion_id"];
						$file_version["file_mimetype"] = "application/zip";
						$file_version["file_filename"] = $zipname;
						$zip->close();
				}
			}

			if (($file_version) && (is_array($file_version))) {
				$download_file = $zipname;
				if ((@file_exists($download_file)) && (@is_readable($download_file))) {
						/**
					 * This must be done twice in order to close both of the open buffers.
					 */
					@ob_end_clean();
					@ob_end_clean();

					/**
					 * Determine method that the file should be accessed (downloaded or viewed)
					 * and send the proper headers to the client.
					 */
					switch($file_record["access_method"]) {
						case 1 :
							header("Pragma: public");
							header("Expires: 0");
							header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
							header("Content-Type: ".$file_version["file_mimetype"]);
							header("Content-Disposition: inline; filename=\"".$zip_file_name."\"");
							header("Content-Length: ".@filesize($download_file));
							header("Content-Transfer-Encoding: binary\n");
						break;
						case 0 :
						default :
							header("Pragma: public");
							header("Expires: 0");
							header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
							header("Content-Type: application/force-download");
							header("Content-Type: application/octet-stream");
							header("Content-Type: ".$file_version["file_mimetype"]);
							header("Content-Disposition: attachment; filename=\"".$zip_file_name."\"");
							header("Content-Length: ".@filesize($download_file));
							header("Content-Transfer-Encoding: binary\n");
						break;
					}
					add_statistic("assignment:".$RECORD_ID, "file_zip_download", "assignment_id", $RECORD_ID);
					echo @file_get_contents($download_file, FILE_BINARY);
					exit;
				}

			}else{
				echo 'error';
			}

			if ((!$ERROR) && (!$NOTICE)) {
				$ERROR++;
				$ERRORSTR[] = "<strong>Unable to download the selected file.</strong><br /><br />The file you have selected cannot be downloaded at this time, ".(($LOGGED_IN) ? "please try again later." : "Please log in to continue.");
			}

			if ($NOTICE) {
				echo display_notice();
			}
			if ($ERROR) {
				echo display_error();
			}
	
		}else{
				application_log("error", "The provided file id was invalid [".$RECORD_ID."] (View File).");
				//header("Location: ".ENTRADA_URL."/profile/gradebook/assignments?section=submit&id=".$RECORD_ID);
				echo 'Invalid id specified. No assignment found for that id.';
				exit;		
		}

	} else {
		echo 'You do not have authorization to view this resource';
	}
} else {
	application_log("error", "No assignment id was provided to view. (View File)");
	echo 'No id specified';
	
	exit;
}
?>
