<?php
/**
 * Online Course Resources [Pre-Clerkship]
 * @author Unit: Medical Education Technology Unit
 * @author Director: Dr. Benjamin Chen <bhc@post.queensu.ca>
 * @author Developer: Matt Simpson <simpson@post.queensu.ca>
 * @version 3.0
 * @copyright Copyright 2006 Queen's University, MEdTech Unit
 *
 * $Id: personnel.api.php 1140 2010-04-27 18:59:15Z simpson $
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

if ((isset($_SESSION["isAuthorized"])) && ((bool) $_SESSION["isAuthorized"])) {
	
	$COMMENT_TYPE = clean_input($_POST["comment_type"],array("trim","notags"));	
	$PROCESSED["comment_description"] = clean_input($_POST["comment_description"],array("trim","notags"));
	$PROCESSED["comment_title"] = clean_input($_POST["comment_title"],array("trim","notags"));
	$PROCESSED["proxy_id"] = $ENTRADA_USER->getProxyId();
	$PROCESSED["comment_active"] = 1;
	$PROCESSED["release_date"] = time();
	$PROCESSED["updated_date"] = time();
	$PROCESSED["notify"] = 0;
	$PROCESSED["updated_by"] = $ENTRADA_USER->getProxyId();
	
	switch($COMMENT_TYPE){
		case "assignment":
			if(!isset($_POST["uid"]) || !isset($_POST["assignment_id"]) || !isset($_POST["comment_description"])){
				application_log("error", "Invalid parameters passed.");
				echo htmlspecialchars(json_encode(array('error'=>'Invalid parameters passed.')), ENT_NOQUOTES);
				exit;
			}

			$USER_ID = (int)$_POST["uid"];
			$PROCESSED["assignment_id"] = (int)$_POST["assignment_id"];


			$query = "SELECT * FROM `assignment_contacts` WHERE `assignment_id` = ".$db->qstr($PROCESSED["assignment_id"])." AND `proxy_id` = ".$db->qstr($ENTRADA_USER->getProxyId());
			$result = $db->GetRow($query);
			if (!$result) {
				application_log("error", "You are not authorized to comment on this submission.");
				echo htmlspecialchars(json_encode(array('error'=>'You are not authorized to comment on this submission.')), ENT_NOQUOTES);
				exit;		
			}

			$query = "SELECT `afile_id` FROM `assignment_files` WHERE `assignment_id` = ".$db->qstr($PROCESSED["assignment_id"])." AND `proxy_id` = ".$db->qstr($USER_ID);
			$PROCESSED["afile_id"] = $db->GetOne($query);

			if(!$PROCESSED["afile_id"]){
				application_log("error", "Error finding file id for submission.");
				echo htmlspecialchars(json_encode(array('error'=>'Error finding file id for submission.')), ENT_NOQUOTES);
				exit;				
			}
			$COMMENT_TABLE = "assignment_comments";
				break;
		default:
			$COMMENT_TABLE = false;
				break;
	}	
	
	if($COMMENT_TABLE){
	
		if (!$db->AutoExecute($COMMENT_TABLE,$PROCESSED,"INSERT")) {
			application_log("error", "Error occurred while submitting comment.");
			echo htmlspecialchars(json_encode(array('error'=>'Error occurred while submitting comment.')), ENT_NOQUOTES);
			exit;						
		} else {
			$COMMENT_ID = $db->Insert_Id();
			$query = "SELECT `username` AS `commenter_username`, CONCAT_WS(' ',`firstname`,`lastname`) AS `commenter_fullname` FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($PROCESSED["proxy_id"]);
			$result = $db->GetRow($query);
			if ($result) {
			?>
				<li>
				<table style="width:100%;" class="discussions posts">
					<tr>
						<td style="border-bottom: none; border-right: none"><span class="content-small">By:</span> <a href="<?php echo ENTRADA_URL."/people?profile=".html_encode($result["commenter_username"]); ?>" style="font-size: 10px"><?php echo html_encode($result["commenter_fullname"]); ?></a></td>
						<td style="border-bottom: none">
							<div style="float: left">
								<span class="content-small"><strong>Commented:</strong> <?php echo date(DEFAULT_DATE_FORMAT, $PROCESSED["updated_date"]); ?></span>
							</div>
							<div style="float: right">
							<?php
							echo (($PROCESSED["proxy_id"] == $ENTRADA_USER->getProxyId()) ? " (<a class=\"action\" href=\"".ENTRADA_URL."/profile/gradebook/assignments?section=edit-comment&amp;id=".$PROCESSED["assignment_id"]."&amp;cid=".$COMMENT_ID."\">edit</a>)" : "");
							echo (($PROCESSED["proxy_id"] == $ENTRADA_USER->getProxyId()) ? " (<a class= \"action delete\" id=\"delete_".$COMMENT_ID."\" href=\"#delete_".$COMMENT_ID."\">delete</a>)":"");
							?>
							</div>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="content" style="border-bottom: 3px solid #EBEBEB;">
						<a name="comment-<?php echo (int) $result["cscomment_id"]; ?>"></a>
						<?php
							echo ((trim($PROCESSED["comment_title"])) ? "<div style=\"font-weight: bold\">".html_encode(trim($PROCESSED["comment_title"]))."</div>" : "");
							echo $PROCESSED["comment_description"];

						?>
						</td>
					</tr>
				</table>
				</li>
			<?php
			} else {
				echo htmlspecialchars(json_encode(array('success'=>'Comment created but unable to load new comment. Please refresh the page to see new comment.')), ENT_NOQUOTES);
			}
			exit;
		}
	
	}else{
		application_log("error", "Invalid comment type provided.");
		echo htmlspecialchars(json_encode(array('error'=>'Invalid comment tpe provided.')), ENT_NOQUOTES);
		exit;
	}
	
} else {
	application_log("error", "Assignment comment API accessed without valid session_id.");
	echo htmlspecialchars(json_encode(array('error'=>'Assignment comment API accessed without valid session_id.')), ENT_NOQUOTES);
	exit;
}