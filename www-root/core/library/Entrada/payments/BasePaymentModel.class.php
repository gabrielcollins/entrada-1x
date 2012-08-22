<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Entrada Base Payment Model Class
 *
 * Used as the blueprint for Payment models. Cannot be instantiated.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Brandon Thorn <bt37@qmed.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 */
abstract class BasePaymentModel{

	protected $payment_amount = 0;
	protected $payment_method = 'other';
	protected $proxy_id = 0;
	protected $transaction_hash = '';
	protected $payment_destination = '';
	protected $line_items = array();
	protected $payment_type = false;
	protected $coupon_id = 0;
	
	abstract function printForm();
	abstract function parseResponse($response,$trans_id);

	
	/**
	 * Creates the payment trasaction in the system. Returns either the transaction ID, or throws an exception if creation fails 
	 * @global type $db
	 * @global type $ENTRADA_USER
	 * @param type $payment_status : payment status, defaults to 'pending' if nothing is passed.
	 * @param type $processed_values : array of keys and values to map to model, if not present it assumes you've already loaded the model another way
	 * @return type 
	 */
	public function createPayment($payment_status = false,$processed_values = false){
		global $db,$ENTRADA_USER;
		if (!$this->line_items) {
			throw new Exception("No line items loaded.");
		}
		
		/**
		 * Loads model from processed array if it was passed
		 */
		if ($processed_values) {
			foreach($processed as $key=>$value){
				$this->$key = $value;
			}
		}
		
		do {
			$PROCESSED["transaction_hash"] = md5(uniqid(rand(), 1));			
		} while($db->GetRow("SELECT `ptransaction_id` FROM `payment_transactions` WHERE `transaction_hash` = ".$db->qstr($PROCESSED["transaction_hash"])));
		
		$this->transaction_hash = $PROCESSED["transaction_hash"];
		$PROCESSED_ITEM["updated_by"] = $PROCESSED["updated_by"] = $ENTRADA_USER->getId();
		$PROCESSED["proxy_id"] = $this->proxy_id?$this->proxy_id:$ENTRADA_USER->getId();
		$PROCESSED["transaction_amount"] = $this->payment_amount;
		$PROCESSED["coupon_id"] = $this->coupon_id;
		$PROCESSED["payment_method"] = $this->payment_method;
		$PROCESSED["payment_status"] = $payment_status?$payment_status:"pending";
		$PROCESSED_ITEM["updated_date"] = $PROCESSED["created_date"] = $PROCESSED["updated_date"] = time();
		
		if ($db->AutoExecute("payment_transactions",$PROCESSED,"INSERT")) {
			$PROCESSED_ITEM["ptransaction_id"] = $this->ptransaction_id =  $db->Insert_ID();	
			
			/**
			 * Adds line items to transaction. If any one line item fails, the whole transaction fails and is rolled back.
			 */
			foreach($this->line_items as $line_item){
				$query = "SELECT * FROM	`payment_catalog` WHERE `pcatalog_id` = ".$db->qstr($line_item["catalog_id"]);
				$catalog_item = $db->GetRow($query);
				
				if ($catalog_item) {
				
					if ($catalog_item["quantity"] == -1 || $catalog_item["quantity"] > 0) {
						$PROCESSED_ITEM["pcatalog_id"] = $line_item["catalog_id"];	
					
						if (!$db->AutoExecute("payment_transaction_items",$PROCESSED_ITEM,"INSERT")) {
							$this->deleteTransaction($this->ptransaction_id);
							throw new Exception("Error occurred adding line item: ".$line_item["name"]);
						} elseif ($catalog_item["quantity"] > 0){
							$db->AutoExecute("payment_catalog",array("quantity"=>($catalog_item["quantity"]-1)),"UPDATE","`pcatalog_id` = ".$db->qstr($line_item["catalog_id"]));
						}
				
					} else {
						$this->deleteTransaction($this->ptransaction_id);
						throw new Exception("Line item is no longer available: ".$line_item["name"]);	
					}
			
				} else {
					$this->deleteTransaction($this->ptransaction_id);
					throw new Exception("Invalid line item: ".$line_item["name"]);
				}
			}			
			return $this->ptransaction_id;
		} else throw new Exception("Error occured creating the transaction.");
	}	
	
	public function loadTransaction($transaction_id, $hash = false){
		global $db;
		$query = "	SELECT * FROM `payment_transactions`
					WHERE `ptransaction_id` = ".$db->qstr($transaction_id)
					.($hash?" AND `transaction_hash` = ".$db->qstr($hash):"");
		if ($result = $db->GetRow($query)) {
			foreach ($result as $key=>$value) {
				$this->$key = $value;
			}
			$this->transaction_items = $this->fetchTransactionItems($this->ptransaction_id);
		} else throw new Exception("No transaction with matching ID".($hash?" and hash":"").".");
	}
	
	public function deleteTransaction($id = false){
		global $db;
		$id = $id?$id:$this->ptransaction_id;
		$query = "DELETE FROM `payment_transactions` WHERE `ptransaction_id` = ".$db->qstr($id);
		$db->Execute($query);		
		/**
		 * Rolls back quanity of each item because transaction failed and then removes them all. 
		 */
		$this->rollbackItemQuanities($id);
		
		$query = "DELETE FROM `payment_transaction_items` WHERE `ptransaction_id` = ".$db->qstr($id);
		$db->Execute($query);	
	}
	
	/**
	 * Update the status of a transaction. Can pass transaction ID or use one that's already loaded in the model.
	 * If transaction status is not approved, rolls back quantities in the catalog (unless its quantity is unlimited).
	 * @global type $db
	 * @param type $new_status - new payment status
	 * @param type $transaction_id - transaction id, defaults to one already loaded in the model
	 * @param type $identifier - Authorization number or some other unique ID as it was returned from the processor
	 * @return type 
	 */
	public function updatePaymentStatus($new_status, $transaction_id = false, $identifier = false){		
		global $db;
		$id = $transaction_id?$transaction_id:$this->ptransaction_id;
		$PROCESSED["payment_status"] = $new_status;
		$PROCESSED["updated_date"] = time();
		if ($identifier) {
			$PROCESSED["transaction_identifier"] = $identifier;
		}
		if ($db->AutoExecute("payment_transactions",$PROCESSED,"UPDATE","`ptransaction_id` = ".$db->qstr($id))) {
			if ($new_status != 'approved') {
				$this->rollbackItemQuanities($id);
			} else {
				$this->confirmTransactionItems($id);
			}
			return true;
		} else return false;		
	}
	
	/**
	 * Rolls back the quantities for any items attached to a transaction id. 
	 * @param type $transaction_id 
	 */
	public function rollbackItemQuanities($transaction_id = false){
		global $db;
		$id = $transaction_id?$transaction_id:$this->ptransaction_id;
		$query = "SELECT * FROM	`payment_transaction_items` WHERE `ptransaction_id` = ".$db->qstr($id);
		if ($items = $db->GetAll($query)) {
			foreach($items as $item){
				$query = "	SELECT * FROM `payment_catalog` 
							WHERE `pcatalog_id` = ".$db->qstr($item["pcatalog_id"]);
				if($catalog_item = $db->GetRow($query)) {
					if($catalog_item["quantity"] != -1){
						$db->AutoExecute("payment_catalog",array("quantity"=>($catalog_item["quantity"]+1)),"UPDATE","`pcatalog_id` = ".$db->qstr($catalog_item["pcatalog_id"]));
					}
					switch($catalog_item["item_type"]){
						case 'course':
							$query = "	DELETE FROM `course_audience` 
										WHERE `course_id` = ".$db->qstr($catalog_item["item_value"])." 
										AND `audience_value` = ".$db->qstr($this->proxy_id)." 
										AND `audience_active` = '2'";
							$db->Execute($query);
							break;
						default:
							break;
					}
				}						
			}
		}
	}
	
	public function confirmTransactionItems($transaction_id = false){
		global $db;
		$id = $transaction_id?$transaction_id:$this->ptransaction_id;
		$query = "	SELECT a.`proxy_id`,b.*,c.* FROM	
					`payment_transactions` a
					JOIN `payment_transaction_items` b
					ON a.`ptransaction_id` = b.`ptransaction_id`
					JOIN `payment_catalog` c
					ON b.`pcatalog_id` = c.`pcatalog_id`
					WHERE b.`ptransaction_id` = ".$db->qstr($id);
		if ($items = $db->GetAll($query)) {
			foreach($items as $item){
				switch($item["item_type"]){
					case 'course':
						$where = "`course_id` = ".$db->qstr($item["item_value"])." AND `audience_value` = ".$db->qstr($item["proxy_id"])." AND `audience_active` = '2'";
						$db->AutoExecute("course_audience",array("audience_active"=>1),"UPDATE",$where);
						break;
					default:
						break;
				}

			}
		}		
	}
	
	/**
	 * Returns an array of catalog items that are associated with transaction. Presumably to handle business logic for each after a transaction is confirmed.
	 * @param type $transaction_id 
	 */
	public function fetchTransactionItems($transaction_id = false){
		global $db;
		$id = $transaction_id?$transaction_id:$this->ptransaction_id;
		$query = "	SELECT b.* FROM	`payment_transaction_items` a
					JOIN `payment_catalog` b
					ON a.`pcatalog_id` = b.`pcatalog_id`
					WHERE a.`ptransaction_id` = ".$db->qstr($id);
		return $db->GetAll($query);
	}
	
	/**
	 * Applies the coupon to the transaction
	 * @todo Figure out business logic for coupon. Likely best to break it out into its own class but maybe not. 
	 * @param type $coupond_id 
	 */
	public function applyCoupon($coupond_id){
		$this->coupon_id = (int)$coupon_id;
	}
	
	/**
	 * Fetches credentials for payment_option, 
	 * @global type $db
	 * @param type $payment_option 
	 */
	public function fetchCredentials($payment_option){
		global $db;
		$this->payment_option_id = $payment_option;		
		$query = "	SELECT b.`key_name`, a.`key_value`
					FROM `payment_option_keys` a 
					JOIN `payment_lu_type_keys` b
					ON a.`ptkey_id` = b.`ptkey_id`
					WHERE a.`poption_id` = ".$db->qstr($payment_option);
		if ($results = $db->GetAll($query)) {
			foreach($results as $result){
				$this->$result["key_name"] = $result["key_value"];
			}
		} else return false;
		
	}
	
	public function addLineItem($line_item){
		if (array_key_exists('name', $line_item) && array_key_exists('value',$line_item) && array_key_exists('catalog_id',$line_item)) {
			$this->line_items[] = $line_item;
			$this->payment_amount += $line_item['value'];
		}
	}
	
	/**
	 * Sets/returns the value for the payment amount.
	 * @param type $amount
	 * @return type 
	 */
	public function paymentAmount($amount = false){
		if ($amount) {
			$this->payment_amount = $amount;
		}
		return $this->payment_amount;
	}
	
	/**
	 * Sets/returns the value for the payment amount.
	 * @param type $amount
	 * @return type 
	 */
	public function paymentMethod($method = false){
		if ($method) {
			$this->payment_method = $method;
		}
		return $this->payment_method;
	}
	
	/**
	 * Sets/returns the value for the payment destination
	 * @param type $dest
	 * @return type 
	 */
	public function paymentDest($dest = false){
		if ($dest) {
			$this->payment_destination = $dest;
		}
		return $this->payment_destination;
	}
	
	/**
	 * Sets/returns the value for the payment destination
	 * @param type $dest
	 * @return type 
	 */
	public function proxyId($id = false){
		if ($id) {
			$this->proxy_id = $id;
		}
		return $this->proxy_id;
	}
	
}

?>
