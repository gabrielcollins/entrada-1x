<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Entrada Payment Factory
 *
 * Used as a simple way to get requested Payment Models and Forms.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Brandon Thorn <bt37@qmed.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 */

class PaymentFactory{
	private static $payment_models;
	
	/**
	 * Accepts the payment type in lower case (chase,paypal,etc) and an array of options and returns an instance 
	 * of the model if it finds a match based on the name, or false if no match was found.
	 * @param type $type
	 * @param type $options
	 * @return  
	 */
	static function getPaymentModel($type,$options = false){
		$cur_dir = dirname(__FILE__);
        if (!self::$models) {
            $models = array();			
            if ($handle = opendir($cur_dir)) {
                while (false !== ($file = readdir($handle))) {
					$split = explode('.',$file);
					if ($split[0] != 'PaymentFactory' && $split[0] != 'BasePaymentModel') {
						$models[strtolower($split[0])] = array('file'=>$file,'class'=>$split[0]);
					}                        
                }
                closedir($handle);
            }
            self::$payment_models = $models;
        }		
		
        if (array_key_exists($type, self::$payment_models)) {
            require_once $cur_dir.DIRECTORY_SEPARATOR.'BasePaymentModel.class.php';
            require_once $cur_dir.DIRECTORY_SEPARATOR.self::$payment_models[$type]['file'];
			if (!$options) {
				return new self::$payment_models[$type]['class'];
			} else {
				$r = new ReflectionClass(self::$payment_models[$type]['class']);
				return $r->newInstanceArgs(array($options));
			}
        }

        return false;		
	}	
	
}
?>
