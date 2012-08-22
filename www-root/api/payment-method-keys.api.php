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

	if (!isset($_POST["ptype_id"]) || !$ptype_id = (int)$_POST["ptype_id"]) {
		$ptype_id = false;	
	}

	if ($ptype_id) {
		$query = "SELECT * FROM `payment_lu_type_keys` WHERE `ptype_id` = ".$db->qstr($ptype_id);
		if($results = $db->GetAll($query)) {
			foreach ($results as $key=>$value) {
			?>
			<tr>
				<td><label for="payment_key_<?php echo $value["ptkey_id"];?>" class="form-<?php echo $value['key_required']?'':'n';?>required"><?php echo ucwords($value["key_name"]);?>:</label></td>
				<td><input type="text" id="payment_key_<?php echo $value["ptkey_id"];?>" name="option_keys[<?php echo $value["ptkey_id"];?>]" value="<?php echo ((isset($PROCESSED["option_keys"][$value["ptkey_id"]])) ? html_encode($PROCESSED["option_keys"][$value["ptkey_id"]]) : ""); ?>" maxlength="60" style="width: 300px" /></td>
			</tr>				
			<?php
			}
		} else {
			echo htmlspecialchars(json_encode(array('error'=>'No keys found for payment type.')), ENT_NOQUOTES);		
		}
	} else {
		echo htmlspecialchars(json_encode(array('error'=>'Invalid payment tpe provided.')), ENT_NOQUOTES);		
	}
	exit;	
	
} else {
	application_log("error", "Payment Method Keys API accessed without valid session_id.");
	echo htmlspecialchars(json_encode(array('error'=>'Payment Method Keys API accessed without valid session_id.')), ENT_NOQUOTES);
	exit;
}