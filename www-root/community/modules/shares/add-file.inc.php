<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Used to upload files to a specific folder of a community.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
*/

if ((!defined("COMMUNITY_INCLUDED")) || (!defined("IN_SHARES"))) {
	exit;
} elseif (!$COMMUNITY_LOAD) {
	exit;
}

$HEAD[] = "<link href=\"".ENTRADA_URL."/javascript/calendar/css/xc2_default.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />";
$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/config/xc2_default.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/script/xc2_inpage.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
$HEAD[] = "<script type=\"text/javascript\" src=\"".COMMUNITY_URL."/javascript/shares.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";

echo "<h1>Upload File</h1>\n";

if ($RECORD_ID) {
	$query			= "SELECT * FROM `community_shares` WHERE `cshare_id` = ".$db->qstr($RECORD_ID)." AND `cpage_id` = ".$db->qstr($PAGE_ID)." AND `community_id` = ".$db->qstr($COMMUNITY_ID);
	$folder_record	= $db->GetRow($query);
	if ($folder_record) {
		
		$query = "SELECT COUNT(*) FROM `community_share_files` WHERE `cshare_id` = ".$db->qstr($RECORD_ID)." AND `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `proxy_id` = ".$db->qstr($ENTRADA_USER->getActiveId())." AND `file_active` = 1";
		
		
		if ( !$db->GetOne($query) || ($COMMUNITY_MEMBER && $folder_record["allow_member_read"]) || (!$COMMUNITY_MEMBER && $folder_record["allow_troll_read"]) || $COMMUNITY_ADMIN){
			if (shares_module_access($RECORD_ID, "add-file")) {
				$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-folder&id=".$folder_record["cshare_id"], "title" => limit_chars($folder_record["folder_title"], 32));
				$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=add-file&id=".$RECORD_ID, "title" => "Upload File");
	
				$file_uploads = array();
				// Error Checking
				switch($STEP) {
					case 2 :
						//var_dump($_FILES["uploaded_file"]);
						if (isset($_FILES["uploaded_file"]) && is_array($_FILES["uploaded_file"])) {
							foreach($_FILES["uploaded_file"]["name"] as $tmp_file_id=>$file_name){

							switch($_FILES["uploaded_file"]["error"][$tmp_file_id]) {
								case 0 :
									if (($file_filesize = (int) trim($_FILES["uploaded_file"]["size"][$tmp_file_id])) <= $VALID_MAX_FILESIZE) {
										$PROCESSED["file_version"]		= 1;
										$PROCESSED["file_mimetype"]		= strtolower(trim($_FILES["uploaded_file"]["type"][$tmp_file_id]));
										$PROCESSED["file_filesize"]		= $file_filesize;
										$PROCESSED["file_filename"]		= useable_filename(trim($file_name));
	
										if ((!defined("COMMUNITY_STORAGE_DOCUMENTS")) || (!@is_dir(COMMUNITY_STORAGE_DOCUMENTS)) || (!@is_writable(COMMUNITY_STORAGE_DOCUMENTS))) {
											$ERROR++;
											$ERRORSTR[] = "There is a problem with the document storage directory on the server; the MEdTech Unit has been informed of this error, please try again later.";
	
											application_log("error", "The community document storage path [".COMMUNITY_STORAGE_DOCUMENTS."] does not exist or is not writable.");
										}
									}
								break;
								case 1 :
								case 2 :
									$ERROR++;
									$ERRORSTR[] = "The file that was uploaded is larger than ".readable_size($VALID_MAX_FILESIZE).". Please make the file smaller and try again.";
								break;
								case 3 :
									$ERROR++;
									$ERRORSTR[]	= "The file that was uploaded did not complete the upload process or was interrupted; please try again.";
								break;
								case 4 :
									$ERROR++;
									$ERRORSTR[]	= "You did not select a file from your computer to upload. Please select a local file and try again. The file's id was ".$tmp_file_id;
								break;
								case 6 :
								case 7 :
									$ERROR++;
									$ERRORSTR[]	= "Unable to store the new file on the server; the MEdTech Unit has been informed of this error, please try again later.";
	
									application_log("error", "Community file upload error: ".(($_FILES["filename"]["error"] == 6) ? "Missing a temporary folder." : "Failed to write file to disk."));
								break;
								default :
									application_log("error", "Unrecognized file upload error number [".$_FILES["filename"]["error"]."].");
								break;
							}
							
						/**
						 * Required field "title" / File Title.
						 */
						if ((isset($_POST["file_title"][$tmp_file_id])) && ($title = clean_input($_POST["file_title"][$tmp_file_id], array("notags", "trim")))) {
							$PROCESSED["file_title"] = $title;
							$file_uploads[$tmp_file_id]["file_title"] = $title;
						} elseif ((isset($PROCESSED["file_filename"])) && ($PROCESSED["file_filename"])) {
							$PROCESSED["file_title"] = $PROCESSED["file_filename"];
							$file_uploads[$tmp_file_id]["file_title"] = $PROCESSED["file_filename"];
						} else {
							$ERROR++;
							$ERRORSTR[] = "The <strong>File Title</strong> field is required.";
						}

						/**
						 * Non-Required field "description" / File Description.
						 *
						 */
						if ((isset($_POST["file_description"][$tmp_file_id])) && $description = clean_input($_POST["file_description"][$tmp_file_id], array("notags", "trim"))) {
							$PROCESSED["file_description"] = $description;
							$file_uploads[$tmp_file_id]["file_description"] = $description;
						} else {
							$PROCESSED["file_description"] = "";
							$file_uploads[$tmp_file_id]["file_description"] = "";
						}
	
						/**
						 * Non-Required field "access_method" / View Method.
						 */
						if ((isset($_POST["access_method"][$tmp_file_id])) && clean_input($_POST["access_method"][$tmp_file_id], array("int")) == 1) {
							$PROCESSED["access_method"] = 1;
							$file_uploads[$tmp_file_id]["access_method"] = 1;
						} else {
							$PROCESSED["access_method"] = 0;
							$file_uploads[$tmp_file_id]["access_method"] = 0;
						}
	
					
						/**
						 * Permission checking for member access.
						 */
						if ((isset($_POST["allow_member_revision"])) && (clean_input($_POST["allow_member_revision"], array("int")) == 1)) {
							$PROCESSED["allow_member_revision"]	= 1;
						} else {
							$PROCESSED["allow_member_revision"]	= 0;
						}
	
						/**
						 * Permission checking for troll access.
						 * This can only be done if the community_registration is set to "Open Community"
						 */
						if (!(int) $community_details["community_registration"]) {
							if ((isset($_POST["allow_troll_revision"])) && (clean_input($_POST["allow_troll_revision"], array("int")) == 1)) {
								$PROCESSED["allow_troll_revision"]	= 1;
							} else {
								$PROCESSED["allow_troll_revision"]	= 0;
							}
						} else {
							$PROCESSED["allow_troll_revision"]		= 0;
						}
	
						/**
						 * Required field "release_from" / Release Start (validated through validate_calendars function).
						 * Non-required field "release_until" / Release Finish (validated through validate_calendars function).
						 */
						if (($LOGGED_IN && $folder_record["allow_troll_read"]) || ($LOGGED_IN && $COMMUNITY_MEMBER && $folder_record["allow_member_read"]) || $COMMUNITY_ADMIN){
							$release_dates = validate_calendars("release", true, false);
							if ((isset($release_dates["start"])) && ((int) $release_dates["start"])) {
								$PROCESSED["release_date"]	= (int) $release_dates["start"];
							} else {
								$ERROR++;
								$ERRORSTR[] = "The <strong>Release Start</strong> field is required.";
							}
							if ((isset($release_dates["finish"])) && ((int) $release_dates["finish"])) {
								$PROCESSED["release_until"]	= (int) $release_dates["finish"];
							} else {
								$PROCESSED["release_until"]	= 0;
							}
						}else{
							$PROCESSED["release_date"] = time();
						}

						if (!$ERROR) {
							$PROCESSED["cshare_id"]		= $RECORD_ID;
							$PROCESSED["community_id"]	= $COMMUNITY_ID;
							$PROCESSED["proxy_id"]		= $ENTRADA_USER->getActiveId();
							$PROCESSED["file_active"]	= 1;
							$PROCESSED["updated_date"]	= time();
							$PROCESSED["updated_by"]	= $ENTRADA_USER->getID();
							

							unset($PROCESSED["csfile_id"]);
							if ($db->AutoExecute("community_share_files", $PROCESSED, "INSERT")) {
								if ($FILE_ID = $db->Insert_Id()) {
									$PROCESSED["csfile_id"]	= $FILE_ID;
									if ($db->AutoExecute("community_share_file_versions", $PROCESSED, "INSERT")) {
										if ($VERSION_ID = $db->Insert_Id()) {
											if (communities_shares_process_file($_FILES["uploaded_file"]["tmp_name"][$tmp_file_id], $VERSION_ID)) {
												if ($LOGGED_IN) {
													if ($COMMUNITY_MEMBER) {
														if (($COMMUNITY_ADMIN) || ($folder_record["allow_member_read"] == 1)) {
															$url = COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-folder&id=".$RECORD_ID;
														} elseif ($folder_record["allow_member_upload"] == 1){
															$url = COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL;
														}
													} else {
														if ($folder_record["allow_troll_read"] == 1) {
															$url = COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-folder&id=".$RECORD_ID;
														} elseif ($folder_record["allow_troll_upload"] == 1) {
															$url = COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL;
														}
													}
												}
												$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";
	
												$SUCCESS++;
												$SUCCESSSTR[]	= "You have successfully uploaded ".html_encode($PROCESSED["file_filename"])." (version 1).<br /><br />You will now be redirected to this files page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
												add_statistic("community:".$COMMUNITY_ID.":shares", "file_add", "csfile_id", $VERSION_ID);
												communities_log_history($COMMUNITY_ID, $PAGE_ID, $FILE_ID, "community_history_add_file", 1, $RECORD_ID);
											}
										}
									}
								}
							}
	
							if (!$SUCCESS) {
								/**
								 * Because there was no success, check if the file_id was set... if it
								 * was we need to delete the database record :( In the future this will
								 * be handled with transactions like it's supposed to be.
								 */
								if ($FILE_ID) {
									$query	= "DELETE FROM `community_share_files` WHERE `csfile_id` = ".$db->qstr($FILE_ID)." AND `cshare_id` = ".$db->qstr($RECORD_ID)." AND `community_id` = ".$db->qstr($COMMUNITY_ID)." LIMIT 1";
									@$db->Execute($query);
	
									/**
									 * Also delete the file version, again, hello transactions.
									 */
									if ($VERSION_ID) {
										$query	= "DELETE FROM `community_share_file_versions` WHERE `csfversion_id` = ".$db->qstr($VERSION_ID)." AND `csfile_id` = ".$db->qstr($FILE_ID)." AND `cshare_id` = ".$db->qstr($RECORD_ID)." AND `community_id` = ".$db->qstr($COMMUNITY_ID)." LIMIT 1";
										@$db->Execute($query);
									}
								}
	
	
								$ERROR++;
								$ERRORSTR[]	= "Unable to store the new file on the server; the MEdTech Unit has been informed of this error, please try again later.";
	
								application_log("error", "Failed to move the uploaded Community file to the storage directory [".COMMUNITY_STORAGE_DOCUMENTS."/".$VERSION_ID."].");
							}
						}
	
							if ($ERROR) {
								$STEP = 1;
							}
						
							}
						} else {
							$ERROR++;
							$ERRORSTR[]	 = "To upload a file to this folder you must select a local file from your computer.";
						}
					break;
					case 1 :
					default :
						continue;
						break;
				}
	
				// Page Display
				switch($STEP) {
					case 2 :
						if ($NOTICE) {
							echo display_notice();
						}
						if ($SUCCESS) {
							echo display_success();
							if (COMMUNITY_NOTIFICATIONS_ACTIVE) {
								community_notify($COMMUNITY_ID, $FILE_ID, "file", COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-file&id=".$FILE_ID, $RECORD_ID, $PROCESSES["release_date"]);
							}
						}
					break;
					case 1 :
					default :					
						if(count($file_uploads)<1){
							$file_uploads[] = array();
						}
						if ($ERROR) {
							echo display_error();
							$NOTICE++;
							$NOTICESTR[] = "There was an error while trying to upload your file(s). You will need to reselect the file(s) you wish to upload.";
						}
						if ($NOTICE) {
							echo display_notice();
						}
						?>
						
						
						<script>
						var is_admin = <?php if (($LOGGED_IN && $folder_record["allow_troll_read"]) || ($LOGGED_IN && $COMMUNITY_MEMBER && $folder_record["allow_member_read"]) || $COMMUNITY_ADMIN) echo 'true'; else echo 'false';?>;
						var addFileHTML =	'	<div id="file_#{file_id}" class="file-upload">'+
											'		<table>'+
											'			<tr>'+
											'				<td colspan="3"><h2>File #{file_number} Details</h2></td>'+
											'			</tr>'+
											'			<tr>'+
											'				<td colspan="3"><div style="text-align: right">(<a class="action" href="#" onclick="$(\'file_#{file_id}\').remove();">remove</a>)</div></td>'+
											'			</tr>'+
											'			<tr>'+
											'				<td colspan="2" style="vertical-align: top"><label for="uploaded_file" class="form-required">Select Local File</label></td>'+
											'				<td style="vertical-align: top">'+
											'					<input type="file" id="uploaded_file_#{file_id}" name="uploaded_file[#{file_id}]" onchange="fetchFilename(#{file_id})" />'+
											'					<div class="content-small" style="margin-top: 5px">'+
											'						<strong>Notice:</strong> You may upload files under <?php echo readable_size($VALID_MAX_FILESIZE); ?>.'+
											'					</div>'+
											'				</td>'+
											'			</tr>'+
											'			<tr>' +
											'				<td colspan="3">&nbsp;</td>'+
											'			</tr>'+
											'			<tr>'+
											'				<td colspan="2"><label for="file_title" class="form-required">File Title</label></td>'+
											'				<td><input type="text" id="file_#{file_id}_title" name="file_title[#{file_id}]" value="<?php echo ((isset($PROCESSED["file_title"])) ? html_encode($PROCESSED["file_title"]) : ""); ?>" maxlength="64" style="width: 95%" /></td>'+
											'			</tr>'+
											'			<tr>'+
											'				<td colspan="2" style="vertical-align: top"><label for="file_description" class="form-nrequired">File Description</label></td>'+
											'				<td style="vertical-align: top">'+
											'					<textarea id="file_#{file_id}_description" name="file_description[#{file_id}]" style="width: 95%; height: 60px" cols="50" rows="5"><?php echo ((isset($PROCESSED["file_description"])) ? html_encode($PROCESSED["file_description"]) : ""); ?></textarea>'+
											'				</td>'+
											'			</tr>'+
											'			<tr>'+
											'				<td colspan="3">&nbsp;</td>'+
											'			</tr>';
											
						if(is_admin){
						addFileHTML +=      '			<tr>'+
											'				<td colspan="2" style="vertical-align: top"><label for="access_method" class="form-nrequired">Access Method</label></td>'+
											'				<td>'+
											'					<input type="radio" id="access_method_0_#{file_id}" name="access_method[#{file_id}]" value="0" style="vertical-align: middle"<?php echo (((!isset($PROCESSED["access_method"])) || ((isset($PROCESSED["access_method"])) && (!(int) $PROCESSED["access_method"]))) ? " checked=\"checked\"" : ""); ?> /> <label for="access_method_0" style="vertical-align: middle" class="content-small">Download this file to their computer first, then open it.</label><br />'+
											'					<input type="radio" id="access_method_1_#{file_id}" name="access_method[#{file_id}]" value="1" style="vertical-align: middle"<?php echo (((isset($PROCESSED["access_method"])) && ((int) $PROCESSED["access_method"])) ? " checked=\"checked\"" : ""); ?> /> <label for="access_method_1" style="vertical-align: middle" class="content-small">Attempt to view it directly in the web-browser.</label><br />'+
											'				</td>'+
											'			</tr>';
						}
						
						addFileHTML +=		'		</table>'+
											'	</div>';
						</script>
						<div style="float: right">
							<ul class="page-action">
								<li><a style="cursor: pointer" onclick="addFile(addFileHTML)">Add Another File</a></li>
							</ul>
						</div>
						
						<form id="upload-file-form" action="<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?section=add-file&amp;id=<?php echo $RECORD_ID; ?>&amp;step=2" method="post" enctype="multipart/form-data">
						<input type="hidden" name="MAX_UPLOAD_FILESIZE" value="<?php echo $VALID_MAX_FILESIZE; ?>" />
						<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Upload File">
						<colgroup>
							<col style="width: 3%" />
							<col style="width: 20%" />
							<col style="width: 77%" />
						</colgroup>
						<tfoot>
							<tr>
								<td colspan="3" style="padding-top: 15px; text-align: right">
									<div id="display-upload-button">
										<input type="button" class="button" value="Upload File(s)" onclick ="uploadFile()" />
									</div>
								</td>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td colspan="3">
									<div id="file_list">
										<?php foreach($file_uploads as $tmp_file_id=>$file_upload){
											if($file_uplaod["success"]){
												
											}
											else{
											?>	
												<div id="file_<?php echo $tmp_file_id;?>" class="file-upload">
												<table>
													<tr>
														<td colspan="3"><h2>File <?php echo $tmp_file_id+1;?> Details</h2></td>
													</tr>
													<tr>
														<td colspan="2" style="vertical-align: top"><label for="uploaded_file" class="form-required">Select Local File</label></td>
														<td style="vertical-align: top">
															<input type="file" id="uploaded_file_<?php echo $tmp_file_id;?>" name="uploaded_file[<?php echo $tmp_file_id;?>]" onchange="fetchFilename(<?php echo $tmp_file_id;?>)" />
															<div class="content-small" style="margin-top: 5px">
																<strong>Notice:</strong> You may upload files under <?php echo readable_size($VALID_MAX_FILESIZE); ?>.
															</div>
														</td>
													</tr>
													<tr>
														<td colspan="3">&nbsp;</td>
													</tr>
													<tr>
														<td colspan="2"><label for="file_title" class="form-required">File Title</label></td>
														<td><input type="text" id="file_<?php echo $tmp_file_id;?>_title" name="file_title[<?php echo $tmp_file_id;?>]" value="<?php echo ((isset($file_upload["file_title"])) ? html_encode($file_upload["file_title"]) : ""); ?>" maxlength="64" style="width: 95%" /></td>
													</tr>
													<tr>
														<td colspan="2" style="vertical-align: top"><label for="file_description" class="form-nrequired">File Description</label></td>
														<td style="vertical-align: top">
															<textarea id="file_<?php echo $tmp_file_id;?>_description" name="file_description[<?php echo $tmp_file_id;?>]" style="width: 95%; height: 60px;max-width: 300px;min-width: 300px;" cols="50" rows="5"><?php echo ((isset($file_upload["file_description"])) ? html_encode($file_upload["file_description"]) : ""); ?></textarea>
														</td>
													</tr>
													<tr>
														<td colspan="3">&nbsp;</td>
													</tr>
													<script>
													if(is_admin){
														document.write(     '			<tr>'+
																			'				<td colspan="2" style="vertical-align: top"><label for="access_method" class="form-nrequired">Access Method</label></td>'+
																			'				<td>'+
																			'					<input type="radio" id="access_method_0_<?php echo $tmp_file_id;?>" name="access_method[<?php echo $tmp_file_id;?>]" value="0" style="vertical-align: middle" checked/> <label for="access_method_0" style="vertical-align: middle" class="content-small">Download this file to their computer first, then open it.</label><br />'+
																			'					<input type="radio" id="access_method_1_<?php echo $tmp_file_id;?>" name="access_method[<?php echo $tmp_file_id;?>]" value="1" style="vertical-align: middle"<?php echo (((isset($file_upload["access_method"])) && ((int) $file_upload["access_method"]) == 1) ? " checked" : ""); ?> /> <label for="access_method_1" style="vertical-align: middle" class="content-small">Attempt to view it directly in the web-browser.</label><br />'+
																			'				</td>'+
																			'			</tr>');
													}
													</script>
												</table>
												</div>
											
									<?php	}
										}
										?>
									</div>							
								</td>
							</tr>
							<tr>
								<td colspan="3"><h2>Batch File Permissions*</h2></td>
							</tr>
							<tr>
								<td><input type="checkbox" id="allow_member_revision" name="allow_member_revision[]" value="1"<?php echo (((isset($PROCESSED["allow_member_revision"])) && ($PROCESSED["allow_member_revision"] == 1)) ? " checked=\"checked\"" : ""); ?> /></td>
								<td colspan="2"><label for="allow_member_revision" class="form-nrequired">Allow <strong>other community members</strong> to upload newer versions of this file.</label></td>
							</tr>
							<?php if (!(int) $community_details["community_registration"]) :  ?>
							<tr>
								<td><input type="checkbox" id="allow_troll_revision" name="allow_troll_revision[]" value="1"<?php echo (((isset($PROCESSED["allow_troll_revision"])) && ($PROCESSED["allow_troll_revision"] == 1)) ? " checked=\"checked\"" : ""); ?> /></td>
								<td colspan="2"><label for="allow_troll_revision" class="form-nrequired">Allow <strong>non-community members</strong> to upload newer versions of this file.</label></td>
							</tr>
							<?php endif; ?>
							<tr>
								<td colspan="3"><h2>Batch Time Release Options*</h2></td>
							</tr>
							<?php
							echo generate_calendars("release", "", true, true, ((isset($PROCESSED["release_date"])) ? $PROCESSED["release_date"] : time()), true, false, ((isset($PROCESSED["release_until"])) ? $PROCESSED["release_until"] : 0));

						?>
							<tr>
								<td colspan="3" style="font-style:italic;font-size:12px;">* these settings will be applied to all files chosen to be uploaded</td>
							</tr>
					</tbody>
					</table>
					</form>
						<div id="display-upload-status" style="display: none">
							<div style="text-align: left; background-color: #EEEEEE; border: 1px #666666 solid; padding: 10px">
								<div style="color: #003366; font-size: 18px; font-weight: bold">
									<img src="<?php echo ENTRADA_URL; ?>/images/loading.gif" width="32" height="32" alt="File Uploading" title="Please wait while this file is being uploaded." style="vertical-align: middle" /> Please Wait: this file is being uploaded.
								</div>
								<br /><br />
								This can take time depending on your connection speed and the filesize.
							</div>
						</div>
						<?php
					break;
				}
			} else {
				if ($ERROR) {
					echo display_error();
				}
				if ($NOTICE) {
					echo display_notice();
				}
			}
		}else{
			
			$ERROR++;
			$ERRORSTR[] = "Your access level only allows you to upload one file and revisions of it. Any additional files can be uploaded as a new revision of that file without overwriting the current file.";
			
			if ($ERROR) {
				echo display_error();
			}
			if ($NOTICE) {
				echo display_notice();
			}
		}
	} else {
		application_log("error", "The provided folder id was invalid [".$RECORD_ID."] (Upload File).");

		header("Location: ".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL);
		exit;
	}
} else {
	application_log("error", "No folder id was provided to upload into. (Upload File)");

	header("Location: ".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL);
	exit;
}
?>
