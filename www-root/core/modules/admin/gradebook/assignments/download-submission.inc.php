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
if ($RECORD_ID) {
	$query = "	SELECT a.*,b.`organisation_id` 
				FROM `assignments` a
				JOIN `courses` b
				ON a.`course_id` = b.`course_id` 
				WHERE a.`assignment_id` = ".$db->qstr($RECORD_ID)."
				AND a.`assignment_active` = '1'";
	$assignment = $db->GetRow($query);	
	if (isset($_GET["sid"]) && $tmp = clean_input($_GET["sid"], "int")) {
		/** @todo this needs to make sure the user is a teacher for the course if this way is used, otherwise students could add another student's proxy*/
		$query = "SELECT * FROM `assignment_contacts` WHERE `assignment_id` = ".$db->qstr($RECORD_ID)." AND `proxy_id` = ".$db->qstr($ENTRADA_USER->getID());
		if ($iscontact = $db->GetRow($query)) {
			$USER_ID = $tmp;
		} elseif ($assignment && $ENTRADA_ACL->amIAllowed(new CourseResource($assignment["course_id"], $assignment["organisation_id"]), "update")) {
			$iscontact = true;
			$USER_ID = $tmp;
		} else {
			$USER_ID = false;
		}

	} else {
		$USER_ID = false;
	}	
	if ($USER_ID) {			
		if($assignment){
			$query			= "
							SELECT a.*, b.`course_id`, b.`assignment_title`
							FROM `assignment_files` AS a
							JOIN `assignments` AS b 
							ON a.`assignment_id` = b.`assignment_id`
							JOIN `".AUTH_DATABASE."`.`user_data` AS c
							ON a.`proxy_id` = c.`id`
							WHERE `file_active` = '1'
							AND a.`assignment_id` = ".$db->qstr($RECORD_ID)."
							AND a.`proxy_id` = ".$db->qstr($USER_ID);
			$file_record	= $db->GetRow($query);
			if ($file_record) {
				$FILE_ID = $file_record["afile_id"];
		
					/**
					 * Download the latest version.
					 */
					$query	= "
							SELECT *
							FROM `assignment_file_versions`
							WHERE `assignment_id` = ".$db->qstr($RECORD_ID)."
							AND `proxy_id` = ".$db->qstr($USER_ID)."
							AND `file_active` = '1'
							ORDER BY `file_version` DESC
							LIMIT 0, 1";
					$result	= $db->GetRow($query);
					if ($result) {
						$file_version = array();
						$file_version["afversion_id"] = $result["afversion_id"];
						$file_version["file_mimetype"] = $result["file_mimetype"];
						$file_version["file_filename"] = $result["file_filename"];
						$file_version["file_filesize"] = (int) $result["file_filesize"];
					}

					if (($file_version) && (is_array($file_version))) {
						if ((@file_exists($download_file = FILE_STORAGE_PATH."/A".$file_version["afversion_id"])) && (@is_readable($download_file))) {
							
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
									header("Content-Disposition: inline; filename=\"".$file_version["file_filename"]."\"");
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
									header("Content-Disposition: attachment; filename=\"".$file_version["file_filename"]."\"");
									header("Content-Length: ".@filesize($download_file));
									header("Content-Transfer-Encoding: binary\n");
								break;
							}
							add_statistic("community:".$COMMUNITY_ID.":shares", "file_download", "csfile_id", $RECORD_ID);
							echo @file_get_contents($download_file, FILE_BINARY);
							exit;
						}
					}

					
					if ((!$ERROR) || (!$NOTICE)) {
						$ERROR++;
						$ERRORSTR[] = "<strong>Unable to download the selected file.</strong><br /><br />The file you have selected cannot be downloaded at this time, please try again later.";
					}

					if ($NOTICE) {
						echo display_notice();
					}
					if ($ERROR) {
						echo display_error();
					}
			} else {
				header("Location: ".ENTRADA_URL."/profile/gradebook/assignments?section=submit&id=".$RECORD_ID);
				//echo 'Invalid id specified. Redirect to submit page.';
				exit;
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
