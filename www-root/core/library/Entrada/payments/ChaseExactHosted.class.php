<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Entrada Chase Payment Implementation
 *
 * Implementation of the Chase Payment Model.
 *
 * @todo: genericize form to not be limited to courses
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Brandon Thorn <bt37@qmed.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 */
class ChaseExactHosted extends BasePaymentModel{
	
	function __construct($options = false) {
	  $this->fp_sequence = (rand(1000, 100000) + time());;
	  $this->fp_timestamp = time();
	  $this->payment_type = 'chase';
	  $this->demo_mode = true;
	}
	
	function parseResponse($post,$trans_id = false){
		global $db;
		$trans_id = (int)(isset($post["x_invoice_num"])?$post["x_invoice_num"]:false);
		if (isset($post["x_response_code"]) && $trans_id) {
			try{
				if(!isset($post["x_reference_3"]) || !$hash = clean_input($post["x_reference_3"],array("trim","notags"))){
					throw new Exception("Missing or invalid transaction hash provided with response.");
				}				
				$this->loadTransaction($trans_id, $hash);
				switch ($post["x_response_code"]) {
					case "1" :
						$this->updatePaymentStatus("approved",$trans_id,$post["x_trans_id"]);
						return $db->GetRow("SELECT * FROM `payment_transactions` WHERE `ptransaction_id` = ".$db->qstr($trans_id));
					break;
					case "2" :
						$this->updatePaymentStatus("declined",$trans_id);
						throw new Exception("Chase trasaction_id [".$post["x_trans_id"]."] was declined.");
					break;
					case "3" :
						$this->updatePaymentStatus("error",$trans_id);
						throw new Exception("Chase trasaction_id [".$post["x_trans_id"]."] had errors.");
					break;
					default :
						$this->updatePaymentStatus("error",$trans_id);
						throw new Exception("An unknown response code was returned by Chase [".$post["x_response_code"]."] for transaction [".$this->ptransaction_id."].");
					break;
				}		
			} catch (Exception $e){
				throw new Exception($e->getMessage());
			}
		} else throw new Exception("Missing expected values in response.");
		return false;
	}	
	
	function printForm(){
			  $hmac_data = $this->login.'^'.$this->fp_sequence.'^'.$this->fp_timestamp.'^'.$this->payment_amount.'^';
			  $this->fp_hash =  hash_hmac("MD5", $hmac_data,$this->key);;		
		?>	
			<h1>Payment Confirmation</h1>
			<form id="checkout-form" action="<?php echo $this->demo_mode?'https://rpm-demo.e-xact.com/payment':'https://checkout.e-xact.com/payment';?>" method="post"> 
			  <input name="x_login" value="<?php echo $this->login;?>" type="hidden"> 
			  <input name="x_amount" value="<?php echo $this->payment_amount;?>" type="hidden"> 
			  <input name="x_invoice_num" value="<?php echo $this->ptransaction_id;?>" type="hidden"> 
			  <input name="x_fp_sequence" value="<?php echo $this->fp_sequence;?>" type="hidden"> 
			  <input name="x_fp_timestamp" value="<?php echo $this->fp_timestamp;?>" type="hidden"> 
			  <input name="x_fp_hash" value="<?php echo $this->fp_hash;?>" type="hidden"> 
			  <input name="x_show_form" value="PAYMENT_FORM" type="hidden"> 
			  <input name="x_reference_3" value="<?php echo $this->transaction_hash;?>" type="hidden"> 
			  <?php
			  if($this->line_items){
				  foreach ($this->line_items as $line_item){
					echo '<input name="x_line_item" value="'.$line_item['name'].'<|>'.$line_item['name'].' Payment<|>Payment of fees for '.$line_item['name'].'<|>1<|>'.$line_item['value'].'<|>NO<|>" type="hidden">';
				  }
			  } 
			  echo display_notice("Please click Checkout below to continue to the payment processor. Your enrolment in the course will only become active after payment is recieved. If you'd like to cancel the enrolment process click Cancel and your transaction will be cancelled.");
			  ?>
			  
				<table class="tableList">
					<thead>
						<tr>
							<th style="text-align:right;">Course Name</th>
							<th style="text-align:right;width:25%">Course Cost</th>
						</tr>
					</thead>
					<tbody>
					  <?php
					  if($this->line_items){
						  foreach ($this->line_items as $line_item){
							echo '<tr><td style="text-align:right;">'.$line_item['name'].'</td><td style="text-align:right;">'.$line_item['value'].'</td></tr>';
						  }
					  } ?>						
					</tbody>
					<tfoot>
						<tr>
							<td colspan="2" style="text-align:right;">
								<input type="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL."/payments?action=cancel&id=".$this->ptransaction_id;?>';"/>
								<input type="submit" value="Checkout"/>
							</td>
						</tr>
					</tfoot>
				</table>			  			  
			</form>
<?php
	}
	
}

?>
