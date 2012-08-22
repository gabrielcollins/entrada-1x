<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Entrada BigBlueButton Conference Implementation
 *
 * Implementation of the BigBlueButton Conference Model.
 * BBB provides a helper class of their own so this class mostly just maps genericized function calls to the appropriate helper class function.
 * BBB helper class code below BigBlueButton class code
 * @todo: in your installation you must add a meta record for BBB with the name salt and the value of the salt from your installation
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Brandon Thorn <bt37@qmed.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 */

class BigBlueButton extends BaseConferenceModel{
	
	public function __construct($options = false) {
		/**
		 * apparently parent::__construct($options) isn't working, will need to be fixed but for now copied the only line of code from that function to this one.
		 */
		set_exception_handler(array($this,'handleException'));
		$this->updated_date = time();
		$this->third_form = false;
	}
	
	public function createConferenceInstance($conference_id = false){
		global $db;
		/**
		 * If Conference isn't loaded yet, it can be loaded via the $conference_id variable
		 */
		if ($conference_id) {
			$this->loadConference($conference_id);
		}
		
		if (!$params = $this->buildURL("create")) {
			throw new Exception("Unable to create new BigBlueButton conference");
		}
		
		try {
			// Create the meeting and get back a response:
			$bbb = new BigBlueButtonHelper($this->salt,$this->software_url);
			$result = $bbb->createMeetingWithXmlResponseArray($params);
			if ($this->parseResponse($result,"create")) {
				$db->AutoExecute("web_conferences",array("conference_started"=>1),"UPDATE","`wconference_id` = ".$db->qstr($this->wconference_id));
				return true;
			} else {
				throw new Exception("Unexpected response from server while creating conference.");
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}
	
	public function isLive($conference_id = false){
		if ($conference_id) {
			$this->loadConference($conference_id);
		}
		
		try {
			// Create the meeting and get back a response:
			$bbb = new BigBlueButtonHelper($this->salt,$this->software_url);
			if ($result = $bbb->isMeetingRunningWithXmlResponseArray($this->wconference_id)) {
				return $this->parseResponse($result,"live");			
			} else {
				throw new Exception("Unexpected response from server while creating conference.");
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
				
	}
	
	public function parseResponse($result,$action = false){
		// If it's all good, then we've interfaced with our BBB php api OK:
		if ($result == null) {
			// If we get a null response, then we're not getting any XML back from BBB.
			application_log("cron","No result from conferencing server.");
			throw new Exception("Unable to contact server to create conference.");
		} else { 
			if ($result['returncode'] == 'SUCCESS') {
				if($action){
					switch($action){
						case 'create':
							break;
						case 'live':
							return $result["running"] == "true"?true:false;
							break;
					}
				}
				return true;
			} else {
				application_log("cron","Didn't create conference. Value of result: ".print_r($result,true));
				throw new Exception("An error occurred while creating the conference.");
			}
		}
		return false;
	}			
	
	public function buildURL($action){
		global $ENTRADA_USER;
		if (!parent::buildURL($action)){
			throw new Exception("Unable to load URL. Please try again later.");
		}
		
		switch($this->attached_type){
			case 'event':
				$attached_path = '/events?id='.$this->attached_id;
				break;
			default:
				$attached_path = '/'.$this->attached_type;
				break;
		}	
		switch($action){
			case 'admin':
				$joinParams = array(
						'meetingId' => $this->wconference_id,
						'username' => $ENTRADA_USER->getFullname(),
						'password' => $this->admin_pass,
					);
				$bbb = new BigBlueButtonHelper($this->salt,$this->software_url);
				$this->action_url =  $bbb->getJoinMeetingURL($joinParams);
				break;
			case 'attendee':
				$joinParams = array(
						'meetingId' => $this->wconference_id,
						'username' => $ENTRADA_USER->getFullname(),
						'password' => $this->attendee_pass,
					);
				$bbb = new BigBlueButtonHelper($this->salt,$this->software_url);
				$this->action_url =  $bbb->getJoinMeetingURL($joinParams);
				break;
			case 'live':
				$this->action_url =  $this->software_url.'/isMeetingRunning?meetingID='.$this->wconference_id;
				break;
			case 'end':
				$this->action_url =  $this->software_url.'/end?meetingID='.$this->wconference_id.'&password='.$this->admin_pass;
				break;
			case 'create':
				/** Conferences won't be created at exactly the start time (they'll be created a little bit before, but in case of an issue this will also work if created after), so this makes sure the duration accounts for the difference.**/				
				$duration = $this->conference_start>time()?(($this->conference_start - time()) + $this->conference_duration):$this->conference_duration;
				$this->action_url = array(
					'meetingId' => $this->wconference_id, 					// REQUIRED
					'meetingName' => $this->conference_title, 	// REQUIRED
					'attendeePw' => $this->attendee_pass, 					// Match this value in getJoinMeetingURL() to join as attendee.
					'moderatorPw' => $this->admin_pass, 					// Match this value in getJoinMeetingURL() to join as moderator.
					'welcomeMsg' => $this->conference_description, 					// ''= use default. Change to customize.
					'logoutUrl' => ENTRADA_URL.$attached_path, 						// Default in bigbluebutton.properties. Optional.
					'duration' => $duration, 						// Default = 0 which means no set duration in minutes. [number]
				);
				//$this->action_url = $this->software_url.'/create?name='.$this->conference_title.'&meetingID='.$this->wconference_id
				//					.'&attendeePW='.$this->attendee_pass.'&moderatorPW='.$this->admin_pass
				//					.'&welcome='.$this->conference_description.'&logoutURL='.ENTRADA_URL.$attached_path
				//					.'&duration='.$this->conference_duration;
				break;
			case 'update':
			default:
				return false;
				break;
		}
		return $this->action_url;
	}
	
	function processForm($post){
		global $PROCESSED;
		if (isset($post["admin_pass"]) && $tmp_input = clean_input($post["admin_pass"],array("trim","notags"))) {
			$PROCESSED["admin_pass"] = $tmp_input;
			$this->admin_pass = $tmp_input;
		} else {
			$PROCESSED["admin_pass"] = md5("admin".microtime());
			$this->admin_pass = $PROCESSED["admin_pass"];
		}
		if (isset($post["attendee_pass"]) && $tmp_input = clean_input($post["attendee_pass"],array("trim","notags"))) {
			$PROCESSED["attendee_pass"] = $tmp_input;
			$this->attendee_pass = $tmp_input;
		} else {
			$PROCESSED["attendee_pass"] = md5("attendee".microtime());
			$this->attendee_pass = $PROCESSED["attendee_pass"];
		}
		application_log("error", "I've been here. Everything should be set.");
		return true;
	}
	
	function printForm(){
		global $PROCESSED;
		if ($this->third_form) {
		?>	
		<div class="wizard-question">
			<div class="response-area">
				<label for="admin_pass" class="form-required">Administrator Password</label><br />
				<input type="text" id="admin_pass" name="admin_pass" value="<?php echo ((isset($PROCESSED["admin_pass"])) ? html_encode($PROCESSED["admin_pass"]) : ""); ?>" maxlength="128" style="width: 96%;" />
			</div>
		</div>
		<div class="wizard-question">
			<div class="response-area">
				<label for="attendee_pass" class="form-required">Attendee Password</label><br />
				<input type="text" id="attendee_pass" name="attendee_pass" value="<?php echo ((isset($PROCESSED["attendee_pass"])) ? html_encode($PROCESSED["attendee_pass"]) : ""); ?>" maxlength="128" style="width: 96%;" />
			</div>
		</div>
<?php
		} else {
			$PROCESSED["admin_pass"] = md5("admin".microtime());
			$PROCESSED["attendee_pass"] = md5("attendee".microtime());
		}
	}
	
}

/*
Copyright 2010 Blindside Networks

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Versions:
   1.0  --  Initial version written by DJP
                   (email: djp [a t ]  architectes DOT .org)
   1.1  --  Updated by Omar Shammas and Sebastian Schneider
                    (email : omar DOT shammas [a t ] g m ail DOT com)
                    (email : seb DOT sschneider [ a t ] g m ail DOT com)
   1.2  --  Updated by Omar Shammas
                    (email : omar DOT shammas [a t ] g m ail DOT com)
   1.3  --  Refactored by Peter Mentzer
 					(email : peter@petermentzerdesign.com)
					- This update will BREAK your external existing code if
					  you've used the previous versions <= 1.2 already so:
						-- update your external code to use new method names if needed
						-- update your external code to pass new parameters to methods
					- Working example of joinIfRunning.php now included
					- Added support for BBB 0.8b recordings
					- Now using Zend coding, naming and style conventions
					- Refactored methods to accept standardized parameters & match BBB API structure
					    -- See included samples for usage examples
*/

/* _______________________________________________________________________*/


class BigBlueButtonHelper {

	private $_securitySalt;				
	private $_bbbServerBaseUrl;			

	/* ___________ General Methods for the BigBlueButton Class __________ */

	function __construct($salt = false,$url = false) {
	/* 
	Establish just our basic elements in the constructor: 
	*/
		// BASE CONFIGS - set these for your BBB server in config.php and they will
		// simply flow in here via the constants:		
		$this->_securitySalt 		= $salt?$salt:CONFIG_SECURITY_SALT;
		$this->_bbbServerBaseUrl 	= $url?$url:CONFIG_SERVER_BASE_URL;		
		set_exception_handler(array($this,'handleException'));
	}

	private function _processXmlResponse($url){
	/* 
	A private utility method used by other public methods to process XML responses.
	*/
		if (extension_loaded('curl')) {
			$ch = curl_init() or die ( curl_error() );
			$timeout = 10;
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);	
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$data = curl_exec( $ch );
			curl_close( $ch );
			if($data)
				return (new SimpleXMLElement($data));
			else
				return false;
		}
		return (simplexml_load_file($url));	
	}

	private function _requiredParam($param) {
		/* Process required params and throw errors if we don't get values */
		if ((isset($param)) && ($param != '')) {
			return $param;
		}
		elseif (!isset($param)) {
			throw new Exception('Missing parameter.');
		}
		else {
			throw new Exception(''.$param.' is required.');
		}
	}

	private function _optionalParam($param) {
		/* Pass most optional params through as set value, or set to '' */
		/* Don't know if we'll use this one, but let's build it in case. */ 
		if ((isset($param)) && ($param != '')) {
			return $param;
		}
		else {
			$param = '';
			return $param;
		}
	}

	/* __________________ BBB ADMINISTRATION METHODS _________________ */
	/* The methods in the following section support the following categories of the BBB API:
	-- create
	-- join
	-- end
	*/

	public function getCreateMeetingUrl($creationParams) {
		/* 
		USAGE: 
		(see $creationParams array in createMeetingArray method.)
		*/
		$this->_meetingId = $this->_requiredParam($creationParams['meetingId']);
		$this->_meetingName = $this->_requiredParam($creationParams['meetingName']);		
		// Set up the basic creation URL:
		$creationUrl = $this->_bbbServerBaseUrl."api/create?";
		// Add params:
		$params = 
		'name='.urlencode($this->_meetingName).
		'&meetingID='.urlencode($this->_meetingId).
		'&attendeePW='.urlencode($creationParams['attendeePw']).
		'&moderatorPW='.urlencode($creationParams['moderatorPw']).
		'&dialNumber='.urlencode($creationParams['dialNumber']).
		'&voiceBridge='.urlencode($creationParams['voiceBridge']).
		'&webVoice='.urlencode($creationParams['webVoice']).
		'&logoutURL='.urlencode($creationParams['logoutUrl']).
		'&maxParticipants='.urlencode($creationParams['maxParticipants']).
		'&record='.urlencode($creationParams['record']).
		'&duration='.urlencode($creationParams['duration']);
		//'&meta_category='.urlencode($creationParams['meta_category']);				
		$welcomeMessage = $creationParams['welcomeMsg'];
		if(trim($welcomeMessage)) 
			$params .= '&welcome='.urlencode($welcomeMessage);
		// Return the complete URL:
		return ( $creationUrl.$params.'&checksum='.sha1("create".$params.$this->_securitySalt) );
	}

	public function createMeetingWithXmlResponseArray($creationParams) {
		/*
		USAGE: 
		$creationParams = array(
			'name' => 'Meeting Name',	-- A name for the meeting (or username)
			'meetingId' => '1234',		-- A unique id for the meeting
			'attendeePw' => 'ap',  		-- Set to 'ap' and use 'ap' to join = no user pass required.
			'moderatorPw' => 'mp', 		-- Set to 'mp' and use 'mp' to join = no user pass required.
			'welcomeMsg' => '', 		-- ''= use default. Change to customize.
			'dialNumber' => '', 		-- The main number to call into. Optional.
			'voiceBridge' => '', 		-- PIN to join voice. Optional.
			'webVoice' => '', 			-- Alphanumeric to join voice. Optional.
			'logoutUrl' => '', 			-- Default in bigbluebutton.properties. Optional.
			'maxParticipants' => '-1', 	-- Optional. -1 = unlimitted. Not supported in BBB. [number]
			'record' => 'false', 		-- New. 'true' will tell BBB to record the meeting.
			'duration' => '0', 			-- Default = 0 which means no set duration in minutes. [number]
			'meta_category' => '', 		-- Use to pass additional info to BBB server. See API docs to enable.
		);
		*/
		$xml = $this->_processXmlResponse($this->getCreateMeetingURL($creationParams));

		if($xml) {
			if($xml->meetingID) 
				return array(
					'returncode' => $xml->returncode, 
					'message' => $xml->message, 
					'messageKey' => $xml->messageKey, 
					'meetingId' => $xml->meetingID, 
					'attendeePw' => $xml->attendeePW, 
					'moderatorPw' => $xml->moderatorPW, 
					'hasBeenForciblyEnded' => $xml->hasBeenForciblyEnded,
					'createTime' => $xml->createTime
					);
			else 
				return array(
					'returncode' => $xml->returncode, 
					'message' => $xml->message, 
					'messageKey' => $xml->messageKey 
					);
		}
		else {
			return null;
		}
	}

	public function getJoinMeetingURL($joinParams) {
		/*
		NOTE: At this point, we don't use a corresponding joinMeetingWithXmlResponse here because the API 
		doesn't respond on success, but you can still code that method if you need it. Or, you can take the URL
		that's returned from this method and simply send your users off to that URL in your code.
		USAGE: 
		$joinParams = array(
			'meetingId' => '1234',		-- REQUIRED - A unique id for the meeting
			'username' => 'Jane Doe',	-- REQUIRED - The name that will display for the user in the meeting
			'password' => 'ap',			-- REQUIRED - The attendee or moderator password, depending on what's passed here
			'createTime' => '',			-- OPTIONAL - string. Leave blank ('') unless you set this correctly.
			'userID' => '',				-- OPTIONAL - string
			'webVoiceConf' => ''		-- OPTIONAL - string
		);
		*/
		$this->_meetingId = $this->_requiredParam($joinParams['meetingId']);
		$this->_username = $this->_requiredParam($joinParams['username']);
		$this->_password = $this->_requiredParam($joinParams['password']);		
		// Establish the basic join URL:
		$joinUrl = $this->_bbbServerBaseUrl."api/join?";
		// Add parameters to the URL:
		$params = 
		'meetingID='.urlencode($this->_meetingId).
		'&fullName='.urlencode($this->_username).
		'&password='.urlencode($this->_password).
		'&userID='.urlencode($joinParams['userId']).
		'&webVoiceConf='.urlencode($joinParams['webVoiceConf']);		
		// Only use createTime if we really want to use it. If it's '', then don't pass it:
		if (((isset($joinParams['createTime'])) && ($joinParams['createTime'] != ''))) {
			$params .= '&createTime='.urlencode($joinParams['createTime']);
		}
		// Return the URL:
		return ($joinUrl.$params.'&checksum='.sha1("join".$params.$this->_securitySalt));
	}

	public function getEndMeetingURL($endParams) {
		/* USAGE: 
		$endParams = array (
			'meetingId' => '1234',		-- REQUIRED - The unique id for the meeting
			'password' => 'mp'			-- REQUIRED - The moderator password for the meeting
		);
		*/
		$this->_meetingId = $this->_requiredParam($endParams['meetingId']);
		$this->_password = $this->_requiredParam($endParams['password']);		
		$endUrl = $this->_bbbServerBaseUrl."api/end?";
		$params = 
		'meetingID='.urlencode($this->_meetingId).
		'&password='.urlencode($this->_password);
		return ($endUrl.$params.'&checksum='.sha1("end".$params.$this->_securitySalt));
	}

	public function endMeetingWithXmlResponseArray($endParams) {
		/* USAGE: 
		$endParams = array (
			'meetingId' => '1234',		-- REQUIRED - The unique id for the meeting
			'password' => 'mp'			-- REQUIRED - The moderator password for the meeting
		);
		*/
		$xml = $this->_processXmlResponse($this->getEndMeetingURL($endParams));
		if($xml) {
			return array(
				'returncode' => $xml->returncode, 
				'message' => $xml->message, 
				'messageKey' => $xml->messageKey
				);
		}
		else {
			return null;
		}

	}

	/* __________________ BBB MONITORING METHODS _________________ */
	/* The methods in the following section support the following categories of the BBB API:
	-- isMeetingRunning
	-- getMeetings
	-- getMeetingInfo
	*/

	public function getIsMeetingRunningUrl($meetingId) {
		/* USAGE: 
		$meetingId = '1234'		-- REQUIRED - The unique id for the meeting
		*/
		$this->_meetingId = $this->_requiredParam($meetingId);	
		$runningUrl = $this->_bbbServerBaseUrl."api/isMeetingRunning?";
		$params = 
		'meetingID='.urlencode($this->_meetingId);
		return ($runningUrl.$params.'&checksum='.sha1("isMeetingRunning".$params.$this->_securitySalt));
	}

	public function isMeetingRunningWithXmlResponseArray($meetingId) {
		/* USAGE: 
		$meetingId = '1234'		-- REQUIRED - The unique id for the meeting
		*/
		$xml = $this->_processXmlResponse($this->getIsMeetingRunningUrl($meetingId));
		if($xml) {
			return array(
				'returncode' => $xml->returncode, 
				'running' => $xml->running 	// -- Returns true/false.
				);
		}
		else {
			return null;
		}

	}

	public function getGetMeetingsUrl() {
		/* Simply formulate the getMeetings URL 
		We do this in a separate function so we have the option to just get this 
		URL and print it if we want for some reason.
		*/
		$getMeetingsUrl = $this->_bbbServerBaseUrl."api/getMeetings?checksum=".sha1("getMeetings".$this->_securitySalt);
		return $getMeetingsUrl;
	}

	public function getMeetingsWithXmlResponseArray() {
		/* USAGE: 
		We don't need to pass any parameters with this one, so we just send the query URL off to BBB
		and then handle the results that we get in the XML response.
		*/
		$xml = $this->_processXmlResponse($this->getGetMeetingsUrl());
		if($xml) {
			// If we don't get a success code, stop processing and return just the returncode:
			if ($xml->returncode != 'SUCCESS') {
				$result = array(
					'returncode' => $xml->returncode
				);
				return $result;
			}	
			elseif ($xml->messageKey == 'noMeetings') {
				/* No meetings on server, so return just this info: */	
				$result = array(
					'returncode' => $xml->returncode,
					'messageKey' => $xml->messageKey,
					'message' => $xml->message
				);					
				return $result;
			}
			else {
				// In this case, we have success and meetings. First return general response:
				$result = array(
					'returncode' => $xml->returncode,
					'messageKey' => $xml->messageKey,
					'message' => $xml->message
				);
				// Then interate through meeting results and return them as part of the array:
				foreach ($xml->meetings->meeting as $m) {
					$result[] = array( 
						'meetingId' => $m->meetingID, 
						'meetingName' => $m->meetingName, 
						'createTime' => $m->createTime, 
						'attendeePw' => $m->attendeePW, 
						'moderatorPw' => $m->moderatorPW, 
						'hasBeenForciblyEnded' => $m->hasBeenForciblyEnded,
						'running' => $m->running
						);
					}								
				return $result;				
			}
		}
		else {
			return null;
		}

	}

	public function getMeetingInfoUrl($infoParams) {
		/* USAGE:
		$infoParams = array(
			'meetingId' => '1234',		-- REQUIRED - The unique id for the meeting
			'password' => 'mp'			-- REQUIRED - The moderator password for the meeting
		);
		*/
		$this->_meetingId = $this->_requiredParam($infoParams['meetingId']);
		$this->_password = $this->_requiredParam($infoParams['password']);	
		$infoUrl = $this->_bbbServerBaseUrl."api/getMeetingInfo?";
		$params = 
		'meetingID='.urlencode($this->_meetingId).
		'&password='.urlencode($this->_password);
		return ($infoUrl.$params.'&checksum='.sha1("getMeetingInfo".$params.$this->_securitySalt));		
	}

	public function getMeetingInfoWithXmlResponseArray($infoParams) {
		/* USAGE:
		$infoParams = array(
			'meetingId' => '1234',		-- REQUIRED - The unique id for the meeting
			'password' => 'mp'			-- REQUIRED - The moderator password for the meeting
		);
		*/
		$xml = $this->_processXmlResponse($this->getMeetingInfoUrl($infoParams));
		if($xml) {
			// If we don't get a success code or messageKey, find out why:
			if (($xml->returncode != 'SUCCESS') || ($xml->messageKey == null)) {
				$result = array(
					'returncode' => $xml->returncode,
					'messageKey' => $xml->messageKey,
					'message' => $xml->message
				);
				return $result;
			}	
			else {
				// In this case, we have success and meeting info:
				$result = array(
					'returncode' => $xml->returncode,
					'meetingName' => $xml->meetingName,
					'meetingId' => $xml->meetingID,
					'createTime' => $xml->createTime,
					'voiceBridge' => $xml->voiceBridge,
					'attendeePw' => $xml->attendeePW,
					'moderatorPw' => $xml->moderatorPW,
					'running' => $xml->running,
					'recording' => $xml->recording,
					'hasBeenForciblyEnded' => $xml->hasBeenForciblyEnded,
					'startTime' => $xml->startTime,
					'endTime' => $xml->endTime,
					'participantCount' => $xml->participantCount,
					'maxUsers' => $xml->maxUsers,
					'moderatorCount' => $xml->moderatorCount,					
				);
				// Then interate through attendee results and return them as part of the array:
				foreach ($xml->attendees->attendee as $a) {
					$result[] = array( 
						'userId' => $a->userID, 
						'fullName' => $a->fullName, 
						'role' => $a->role
						);
					}						
				return $result;				
			}
		}
		else {
			return null;
		}

	}

	/* __________________ BBB RECORDING METHODS _________________ */
	/* The methods in the following section support the following categories of the BBB API:
	-- getRecordings
	-- publishRecordings
	-- deleteRecordings
	*/

	public function getRecordingsUrl($recordingParams) {
		/* USAGE:
		$recordingParams = array(
			'meetingId' => '1234',		-- OPTIONAL - comma separate if multiple ids
		);
		*/
		$recordingsUrl = $this->_bbbServerBaseUrl."api/getRecordings?";
		$params = 
		'meetingID='.urlencode($recordingParams['meetingId']);
		return ($recordingsUrl.$params.'&checksum='.sha1("getRecordings".$params.$this->_securitySalt));

	}

	public function getRecordingsWithXmlResponseArray($recordingParams) {
		/* USAGE:
		$recordingParams = array(
			'meetingId' => '1234',		-- OPTIONAL - comma separate if multiple ids
		);
		NOTE: 'duration' DOES work when creating a meeting, so if you set duration
		when creating a meeting, it will kick users out after the duration. Should 
		probably be required in user code when 'recording' is set to true.
		*/
		$xml = $this->_processXmlResponse($this->getRecordingsUrl($recordingParams));
		if($xml) {
			// If we don't get a success code or messageKey, find out why:
			if (($xml->returncode != 'SUCCESS') || ($xml->messageKey == null)) {
				$result = array(
					'returncode' => $xml->returncode,
					'messageKey' => $xml->messageKey,
					'message' => $xml->message
				);
				return $result;
			}	
			else {
				// In this case, we have success and recording info:
				$result = array(
					'returncode' => $xml->returncode,
					'messageKey' => $xml->messageKey,
					'message' => $xml->message					
				);

				foreach ($xml->recordings->recording as $r) {
					$result[] = array( 
						'recordId' => $r->recordID, 
						'meetingId' => $r->meetingID, 
						'name' => $r->name,
						'published' => $r->published,
						'startTime' => $r->startTime,
						'endTime' => $r->endTime,
						'playbackFormatType' => $r->playback->format->type,
						'playbackFormatUrl' => $r->playback->format->url,
						'playbackFormatLength' => $r->playback->format->length,
						'metadataTitle' => $r->metadata->title,
						'metadataSubject' => $r->metadata->subject,
						'metadataDescription' => $r->metadata->description,
						'metadataCreator' => $r->metadata->creator,
						'metadataContributor' => $r->metadata->contributor,
						'metadataLanguage' => $r->metadata->language,
						// Add more here as needed for your app depending on your
						// use of metadata when creating recordings.
						);
					}						
				return $result;				
			}
		}
		else {
			return null;
		}
	}

	public function getPublishRecordingsUrl($recordingParams) {
		/* USAGE:
		$recordingParams = array(
			'recordId' => '1234',		-- REQUIRED - comma separate if multiple ids
			'publish' => 'true',		-- REQUIRED - boolean: true/false
		);
		*/
		$recordingsUrl = $this->_bbbServerBaseUrl."api/publishRecordings?";
		$params = 
		'recordID='.urlencode($recordingParams['recordId']).
		'&publish='.urlencode($recordingParams['publish']);
		return ($recordingsUrl.$params.'&checksum='.sha1("publishRecordings".$params.$this->_securitySalt));

	}

	public function publishRecordingsWithXmlResponseArray($recordingParams) {
		/* USAGE:
		$recordingParams = array(
			'recordId' => '1234',		-- REQUIRED - comma separate if multiple ids
			'publish' => 'true',		-- REQUIRED - boolean: true/false
		);
		*/
		$xml = $this->_processXmlResponse($this->getPublishRecordingsUrl($recordingParams));
		if($xml) {
			return array(
				'returncode' => $xml->returncode, 
				'published' => $xml->published 	// -- Returns true/false.
				);
		}
		else {
			return null;
		}


	}

	public function getDeleteRecordingsUrl($recordingParams) {
		/* USAGE:
		$recordingParams = array(
			'recordId' => '1234',		-- REQUIRED - comma separate if multiple ids
		);
		*/
		$recordingsUrl = $this->_bbbServerBaseUrl."api/deleteRecordings?";
		$params = 
		'recordID='.urlencode($recordingParams['recordId']);
		return ($recordingsUrl.$params.'&checksum='.sha1("deleteRecordings".$params.$this->_securitySalt));
	}

	public function deleteRecordingsWithXmlResponseArray($recordingParams) {
		/* USAGE:
		$recordingParams = array(
			'recordId' => '1234',		-- REQUIRED - comma separate if multiple ids
		);
		*/

		$xml = $this->_processXmlResponse($this->getDeleteRecordingsUrl($recordingParams));
		if($xml) {
			return array(
				'returncode' => $xml->returncode, 
				'deleted' => $xml->deleted 	// -- Returns true/false.
				);
		}
		else {
			return null;
		}

	}

	public function handleException($e){
		throw new Exception($e->getMessage());
	}

} // END OF BIGBLUEBUTTONHELPER CLASS

