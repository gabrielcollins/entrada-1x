<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Used to allow users to add comments to a particular file that is being shared
 * within a folder.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Brandon Thorn <brandon.thorn@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 * 
*/

if (!defined("IN_PUBLIC_ASSIGNMENTS")) {
	exit;
}

echo "<h1>Add File Comment</h1>\n";

if(isset($_GET["fid"]) && $tmp = clean_input($_GET["fid"],"int")){
	$FILE_ID = $tmp;
}else{
	$FILE_ID = false;
}

if ($RECORD_ID && $FILE_ID) {
	
	$query			= "
					SELECT a.*
					FROM `assignment_files` AS a
					JOIN `assignments` AS b
					ON a.`assignment_id` = b.`assignment_id`
					AND a.`afile_id` = ".$db->qstr($FILE_ID)."
					AND b.`assignment_id` = ".$db->qstr($RECORD_ID)."
					AND a.`file_active` = '1'";
	$file_record	= $db->GetRow($query);
	if ($file_record) {
		if ((int) $file_record["file_active"]) {
			$allowed = false;
			$query = "SELECT * FROM `assignment_files` WHERE `afile_id` = ".$db->qstr($FILE_ID)." AND `proxy_id` = ".$db->qstr($ENTRADA_USER->getProxyId());
			$owner = $db->GetRow($query);
			if ($owner) {
				$allowed = true;
			} else{
				$query = "SELECT a.* FROM `assignment_files` AS a JOIN `assignment_contacts` AS b ON a.`assignment_id` = b.`assignment_id` WHERE a.`afile_id` = ".$db->qstr($FILE_ID)." AND b.`proxy_id` = ".$db->qstr($ENTRADA_USER->getProxyId());
				$assignment_contact = $db->GetRow($query);				
				if($assignment_contact){
					$allowed = true;
				}
			}
			if ($allowed){//shares_module_access($file_record["cshare_id"], "add-comment")) {
				$BREADCRUMB[] = array("url" => ENTRADA_URL."/profile/gradebook/assignments?section=view&id=".$RECORD_ID.(isset($assignment_contact)&&$assignment_contact?"&pid=".$file_record["proxy_id"]:""), "title" => limit_chars($file_record["file_title"], 32));
				$BREADCRUMB[] = array("url" => ENTRADA_URL."/profile/gradebook/assignments?section=add-comment&id=".$RECORD_ID."&fid=".$FILE_ID, "title" => "Add File Comment");

				load_rte();

				// Error Checking
				switch($STEP) {
					case 2 :
						/**
						 * Required field "title" / Comment Title.
						 */
						if ((isset($_POST["comment_title"])) && ($title = clean_input($_POST["comment_title"], array("notags", "trim")))) {
							$PROCESSED["comment_title"] = $title;
						} else {
							$PROCESSED["comment_title"] = "";
						}

						/**
						 * Non-Required field "description" / Comment Body
						 *
						 */
						if ((isset($_POST["comment_description"])) && ($description = clean_input($_POST["comment_description"], array("trim", "allowedtags")))) {
							$PROCESSED["comment_description"] = $description;
						} else {
							$ERRORSTR[] = "The <strong>Comment Body</strong> field is required, this is the comment you're making.";
						}

						/**
						 * Email Notificaions.
						 */
						if(isset($_POST["member_notify"])) {
							$PROCESSED["notify"] = $_POST["member_notify"];
						} else {
							$PROCESSED["notify"] = 0;
						}

						if (!$ERROR) {
							$PROCESSED["afile_id"]			= $FILE_ID;
							$PROCESSED["assignment_id"]		= $RECORD_ID;
							$PROCESSED["proxy_id"]			= $ENTRADA_USER->getProxyId();
							$PROCESSED["comment_active"]	= 1;
							$PROCESSED["release_date"]		= time();
							$PROCESSED["updated_date"]		= time();
							$PROCESSED["updated_by"]		= $ENTRADA_USER->getProxyId();

							if ($db->AutoExecute("assignment_comments", $PROCESSED, "INSERT")) {
								if ($COMMENT_ID = $db->Insert_Id()) {
									$url			= ENTRADA_URL."/profile/gradebook/assignments?section=view&id=".$RECORD_ID.(isset($assignment_contact)&&$assignment_contact?"&pid=".$file_record["proxy_id"]:"")."#comment-".$COMMENT_ID;
									$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";

									$SUCCESS++;
									$SUCCESSSTR[]	= "You have successfully added a new file comment.<br /><br />You will now be redirected back to this file; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";

									add_statistic("assignment:".$RECORD_ID, "comment_add", "acomment_id", $COMMENT_ID);
								}
							}

							if (!$SUCCESS) {
								$ERROR++;
								$ERRORSTR[] = "There was a problem adding this file comment into the system. The MEdTech Unit was informed of this error; please try again later.";

								application_log("error", "There was an error inserting a file comment. Database said: ".$db->ErrorMsg());
							}
						}

						if ($ERROR) {
							$STEP = 1;
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
						}
					break;
					case 1 :
					default :
					if ($ERROR) {
						echo display_error();
					}
					if ($NOTICE) {
						echo display_notice();
					}
					?>
					<form action="<?php echo ENTRADA_URL."/profile/gradebook/assignments?section=add-comment&amp;id=".$RECORD_ID."&amp;fid=".$FILE_ID; ?>&amp;step=2" method="post">
					<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Add File Comment">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 20%" />
						<col style="width: 77%" />
					</colgroup>
					<tfoot>
						<tr>
							<td colspan="3" style="padding-top: 15px; text-align: right">
								<input type="submit" class="button" value="Save" />
							</td>
						</tr>
					</tfoot>
					<tbody>
						<tr>
							<td colspan="3"><h2>File Comment Details</h2></td>
						</tr>
						<tr>
							<td colspan="2"><label for="comment_title" class="form-nrequired">Comment Title</label></td>
							<td style="text-align: right"><input type="text" id="comment_title" name="comment_title" value="<?php echo ((isset($PROCESSED["comment_title"])) ? html_encode($PROCESSED["comment_title"]) : ""); ?>" maxlength="128" style="width: 95%" /></td>
						</tr>
						<tr>
							<td colspan="3"><label for="comment_description" class="form-required">Comment Body</label></td>
						</tr>
						<tr>
							<td colspan="3">
								<textarea id="comment_description" name="comment_description" style="width: 100%; height: 200px" cols="68" rows="12"><?php echo ((isset($PROCESSED["comment_description"])) ? html_encode($PROCESSED["comment_description"]) : ""); ?></textarea>
							</td>
						</tr>
					</tbody>
					</table>
					</form>
					<?php
					break;
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "You are not authorized to add a comment to this file.";
				if ($ERROR) {
					echo display_error();
				}
				if ($NOTICE) {
					echo display_notice();
				}
			}
		} else {
			$NOTICE++;
			$NOTICESTR[] = "The file that you are trying to comment on was deactivated <strong>".date(DEFAULT_DATE_FORMAT, $file_record["updated_date"])."</strong> by <strong>".html_encode(get_account_data("firstlast", $file_record["updated_by"]))."</strong>.<br /><br />If there has been a mistake or you have questions relating to this issue please contact the MEdTech Unit directly.";

			echo display_notice();

			application_log("error", "The file record id [".$RECORD_ID."] is deactivated; however, ".$_SESSION["details"]["firstname"]." ".$_SESSION["details"]["lastname"]." [".$_SESSION["details"]["id"]."] has tried to comment on it.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "The file id that you have provided does not exist in the system. Please provide a valid record id to proceed.";

		echo display_error();

		application_log("error", "The provided file id was invalid [".$RECORD_ID."] (Add Comment).");
	}
} else {
	$ERROR++;
	$ERRORSTR[] = "Please provide a valid file id to proceed.";

	echo display_error();

	application_log("error", "No file id was provided to comment on. (Add Comment)");
}
?>
