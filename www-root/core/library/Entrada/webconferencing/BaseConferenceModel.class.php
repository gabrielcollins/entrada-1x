<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Entrada Base Conference Model Class
 *
 * Used as the blueprint for Conference models. Cannot be instantiated.
 * Note: not really a true model. Contains form and requires use of globals for database and $PROCESSED (could probably do without $PROCESSED eventually).
 * 
 * Implementation classes must implement the following functions:
 * 
 * printForm(): Prints the software specific form to gather any software specific keys and values. Used in the conference-wizard.api.php file, try to follow same standards with form.
 * NOTE: Only the fields need to be echoed, the rest of the form including buttons and action are set in the conference-wizard.api.php file
 * processForm($post): Used to process the form. Gets called whether $third_form is true or not. Can set values specific to software here even if you don't need a form displayed.
 * parseResponse($result): Parses the response from API calls. 
 * createConferenceInstance($conference_id): Used to create the conference on the software's server. If not done as a second step, override createConference($PROCESSED) and have it call createConferenceInstance($conference_id) at the same time.
 * NOTE: createConferenceInstance() should also updated the web_conferences table and set conference_started to 1 when the conference gets created.
 * buildURL($action): not mandatory to implement this but unless the software_url in the conference_lu_software table is the URL used for all actions its going to need Implementation specific business logic
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Brandon Thorn <bt37@qmed.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 */
abstract class BaseConferenceModel{

	protected $csoftware_id = 0;
	protected $conference_title = '';
	protected $conference_description = '';
	protected $conference_duration = 360;
	protected $conference_start = 0;
	protected $release_date = 0;
	protected $release_until = 0;	
	protected $updated_date = 0;	
	protected $updated_by = 0;	
	protected $software_url = '';
	protected $implementation_keys = array();
	public $third_form = true;
	
	public function __construct($options=false) {
		set_exception_handler(array($this,'handleException'));
	}
	/**
	 *  If $third_form is true should display the form with any custom fields required by implementation.
	 */
	abstract function printForm();
	/**
	 * Should set global $PROCESSED values based on array passed (likely $_POST). Returns true if successful, false or throws Exception if invalid.
	 * Throes exception on error
	 * @global type $PROCESSED
	 * @param type $post
	 * @return type 
	 */
	abstract function processForm($post);
	/**
	 * Should accept response and an optional conference_id and return true or throw Exception if it fails.
	 * Throws exception on error
	 */
	abstract function parseResponse($response,$conference_id);
	/**
	 * Accepts an optional $conference_id to load the conference if not already loaded, then creates the Conference on the Software
	 * Throws Exception on error
	 */
	abstract function createConferenceInstance($conference_id);
	/**
	 * Accepts an optional $conference_id to load the conference if not already loaded, then checks if the conference is live
	 * Throws Exception on error
	 */
	abstract function isLive($conference_id);
	
	/**
	 * Builds the URL for the specified software and $action (ex: admin,attendee,live,end,create,update,etc)
	 */
	public function buildURL($action){
		if (!isset($this->software_url)){
			if(!isset($this->wconference_id) || !$this->wconference_id){
				throw new Exception("No URL loaded for conference software.");
			}
			$query = "	SELECT a.`software_url` FROM
						`conference_lu_software` a
						JOIN `web_conferences` b
						ON a.`csoftware_id` = b.`csoftware_id`
						WHERE b.`wconference_id` = ".$db->qstr($this->wconference_id);
			if(!$this->software_url = $db->GetOne($query)){
				throw new Exception("Unable to fetch URL for conference software. Please try again later.");
			}
		}
		return $this->software_url;
	}
	/**
	 * Creates the web conference in the system. Extended classes can choose to create the conference on the software if possible/required.
	 * Returns either the wconference_id, or throws an exception if creation fails 
	 * @global type $db
	 * @global type $ENTRADA_USER
	 * @param type $payment_status
	 * @return type 
	 */
	public function createConference($PROCESSED = false){
		global $db,$ENTRADA_USER;
		
		if (!$PROCESSED) {
			$PROCESSED = array();
			foreach($this as $variable=>$value){
				$PROCESSED[$variable] = $value;
			}
		}
		
		$PROCESSED["updated_by"] = $ENTRADA_USER->getId();	
		$PROCESSED["updated_date"] = time();
		
		if ($db->AutoExecute("web_conferences",$PROCESSED,"INSERT")) {
			$PROCESSED["wconference_id"] = $this->wconference_id =  $db->Insert_ID();	
			if($keys = $this->fetchKeysForSoftware($PROCESSED["csoftware_id"])){
				foreach($keys as $key=>$value){
					if(isset($PROCESSED[$value])){
						$key_value = array("wconference_id"=>$PROCESSED["wconference_id"],"cskey_id"=>$key,"key_value"=>$PROCESSED[$value]);
						if (!$db->AutoExecute("web_conference_key_values",$key_value,"INSERT")) {
							$this->deleteConference($PROCESSED["wconference_id"]);
							throw new Exception("Error occurred while adding web conference. Please try again.");
						}
					}
				}
			}
			foreach($PROCESSED as $variable=>$value){
				$this->$variable = $value;
			}
			return $this->wconference_id;
		} else throw new Exception("Error occured creating the transaction.");
	}	
	
	/**
	 * Updates the web conference in the system. Extended classes can choose to create the conference on the software if possible/required.
	 * Returns either the wconference_id, or throws an exception if creation fails 
	 * @global type $db
	 * @global type $ENTRADA_USER
	 * @param type $payment_status
	 * @return type 
	 */
	public function updateConference($PROCESSED = false,$conference_id = false){
		global $db,$ENTRADA_USER;
		
		if (!$PROCESSED) {
			$PROCESSED = array();
			foreach($this as $variable=>$value){
				$PROCESSED[$variable] = $value;
			}
		}
		
		$PROCESSED["wconference_id"] = $conference_id?$conference_id:$this->wconference_id;
		$PROCESSED["updated_by"] = $ENTRADA_USER->getId();	
		$PROCESSED["updated_date"] = time();
		$query = "SELECT * FROM `web_conferences` WHERE `wconference_id` = ".$db->qstr($PROCESSED["wconference_id"])." AND `conference_started` = '1'";
		if ($db->GetRow($query)) {
			throw new Exception("This conference has already been created on the web conferencing server and can no longer be edited.");
		} else {
			if ($db->AutoExecute("web_conferences",$PROCESSED,"UPDATE","`wconference_id` = ".$db->qstr($PROCESSED["wconference_id"]))) {			
				$query = "DELETE FROM `web_conference_key_values` WHERE `wconference_id` = ".$db->qstr($PROCESSED["wconference_id"]);
				$db->Execute($query);
				if($keys = $this->fetchKeysForSoftware($PROCESSED["csoftware_id"])){				
					foreach($keys as $key=>$value){
						if(isset($PROCESSED[$value])){
							$key_value = array("wconference_id"=>$PROCESSED["wconference_id"],"cskey_id"=>$key,"key_value"=>$PROCESSED[$value]);
							if (!$db->AutoExecute("web_conference_key_values",$key_value,"INSERT")) {
								throw new Exception("Error occurred while updating web conference. Please try again.");
							}
						}
					}
				}
				foreach($PROCESSED as $variable=>$value){
					$this->$variable = $value;
				}			
				return true;
			} else throw new Exception("Error occured creating the transaction.");
		}
	}	
	
	/**
	 * Deletes web conference and existing key values from database. Can pass in id or have it use the Class's wconference_id variable.
	 * @global type $db
	 * @param type $id 
	 */
	public function deleteConference($conference_id = false){
		global $db;
		$id = $conference_id?$conference_id:$this->wconference_id;
		$query = "SELECT * FROM `web_conferences` WHERE `wconference_id` = ".$db->qstr($id)." AND `conference_started` = '1'";
		if ($db->GetRow($query)) {
			throw new Exception("This conference has already been created on the web conferencing server and can no longer be deleted.");
		} else {
			$query = "DELETE FROM `web_conferences` WHERE `wconference_id` = ".$db->qstr($id);
			$db->Execute($query);			
			$query = "DELETE FROM `web_conference_key_values` WHERE `wconference_id` = ".$db->qstr($id);
			$db->Execute($query);	
		}
	}
	
	public function loadConference($conference_id = false) {
		global $db,$PROCESSED;
		$id = $conference_id?$conference_id:$this->wconference_id;
		$query = "	SELECT a.*,b.* FROM `web_conferences` a
					JOIN `conference_lu_software` b
					ON a.`csoftware_id` = b.`csoftware_id`
					WHERE a.`wconference_id` = ".$db->qstr($id);
		if($conference_details = $db->GetRow($query)){
			foreach($conference_details as $key=>$value) {
				$this->$key = $value;
				$PROCESSED[$key] = $value;
			}
			if($this->csoftware_id){
				$query = "	SELECT * FROM `conference_lu_software_meta`
							WHERE `csoftware_id` = ".$db->qstr($this->csoftware_id);
				if ($results = $db->GetAll($query)) {
					foreach($results as $meta) {
						$this->$meta["meta_name"] = $meta["meta_value"];
						$PROCESSED[$meta["meta_name"]] = $meta["meta_value"];;
					}				
				}
			}
			$this->fetchValuesForConference($conference_id);
			return true;
		} else return false;
	}
	
	/**
	 * Fetches value for web conference by id, 
	 * @global type $db
	 * @param type $conference_id 
	 */
	public function fetchKeysForSoftware($csoftware_id = false){
		global $db;
		$id = $csoftware_id?$csoftware_id:$this->csoftware_id;
		$query = "	SELECT *
					FROM `conference_lu_software_keys`
					WHERE `csoftware_id` = ".$db->qstr($id);
		if ($results = $db->GetAll($query)) {
			foreach($results as $key=>$result){
				$this->implementation_keys[$result["cskey_id"]] = $result["key_name"];
			}
			return $this->implementation_keys;
		} else return false;
		
	}
	
	/**
	 * Fetches value for web conference by id. Also loads $PROCESSED array
	 * @global type $db
	 * @param type $conference_id 
	 */
	public function fetchValuesForConference($conference_id){
		global $db,$PROCESSED;
		$this->wconference_id = $conference_id;		
		$query = "	SELECT a.`key_name`, b.`key_value`
					FROM `conference_lu_software_keys` a 
					JOIN `web_conference_key_values` b
					ON a.`cskey_id` = b.`cskey_id`
					WHERE b.`wconference_id` = ".$db->qstr($conference_id);
		if ($results = $db->GetAll($query)) {
			foreach($results as $result){
				$this->$result["key_name"] = $result["key_value"];
				$PROCESSED[$result["key_name"]] = $result["key_value"];
			}
			return true;
		} else return false;
		
	}

	protected function handleException(Exception $e){
		throw new Exception($e->getMessage);
	}
	
}

?>
