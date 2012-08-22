<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Entrada Conference Factory
 *
 * Used as a simple way to get requested Conference Models and Forms.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Brandon Thorn <bt37@qmed.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 */

class ConferenceFactory{
	private static $conference_models;
	
	/**
	 * Accepts the conference software type in lower case (bigbluebutton,citrix,etc) and an optional array of options and returns an instance 
	 * of the model if it finds a match based on the name, or false if no match was found.
	 * @todo the list of models could probably be Cached, no point in hitting the filesystem over and over
	 * @param type $type
	 * @param type $options
	 * @return BaseConferenceModel
	 */
	static function getConferenceModel($type,$options = false){
		$cur_dir = dirname(__FILE__);
        if (!self::$conference_models) {
            $models = array();			
            if ($handle = opendir($cur_dir)) {
                while (false !== ($file = readdir($handle))) {
					$split = explode('.',$file);
					if ($split[0] != 'ConferenceFactory' && $split[0] != 'BaseConferenceModel') {
						$models[strtolower($split[0])] = array('file'=>$file,'class'=>$split[0]);
					}                        
                }
                closedir($handle);
            }
            self::$conference_models = $models;
        }		
		
        if (array_key_exists($type, self::$conference_models) && self::$conference_models[$type]['file'] && self::$conference_models[$type]['class']) {
            require_once $cur_dir.DIRECTORY_SEPARATOR.'BaseConferenceModel.class.php';
            require_once $cur_dir.DIRECTORY_SEPARATOR.self::$conference_models[$type]['file'];
			if (!$options) {
				/** @var $model BaseConferenceModel */
				$model =  new self::$conference_models[$type]['class'];
				return $model;
			} else {
				$r = new ReflectionClass(self::$conference_models[$type]['class']);
				/** @var $model BaseConferenceModel */
				$model =  $r->newInstanceArgs(array($options));
				return $model;
			}
        }

        return false;		
	}
	
	/**
	 * Accessor function to return a loaded Conference using $conference_id parameter.
	 * Can also be done by $c = ConferenceFactory::getConferenceModel($type); $c->loadConference($conference_id); but in cases when the type isn't known its easiest to use this function.
	 * @global type $db
	 * @param type $conference_id
	 * @return type 
	 */
	public static function getConferenceById($conference_id){
		global $db;
		$query = "	SELECT `software_model` FROM `conference_lu_software` a
					JOIN `web_conferences` b
					ON a.`csoftware_id` = b.`csoftware_id`
					WHERE b.`wconference_id` = ".$db->qstr($conference_id);
		if ($software_model = $db->GetOne($query)) {
			if($model_inst = self::getConferenceModel($software_model)) {
				$model_inst->loadConference($conference_id);
				$model_inst->software_model = $software_model;
				return $model_inst;
			} else return false;
		} else return false;
	}
	
}
?>
