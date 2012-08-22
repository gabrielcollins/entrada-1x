<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Entrada is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Entrada is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Entrada.  If not, see <http://www.gnu.org/licenses/>.
 *
 * This file is used to display events from the entrada.events table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Brandon Thorn <brandon.thorn@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 *
*/

if (!defined("PARENT_INCLUDED")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_RELATIVE);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("event", "read", false)) {
	add_error("Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {	
	if ($PAYMENT_ID) {
		
		if(isset($_GET["return"]) && isset($_GET["return_id"]) && $return = clean_input($_GET["return"],array("notags","trim")) && $return_id = (int)$_GET["return_id"]){
			switch($return){
				case 'item':
					$query = "	SELECT * FROM `payment_catalog` WHERE `pcatalog_id` = ".$db->qstr($return_id);		
					if ($item_details = $db->GetRow($query)) {					
						switch($item_details["item_type"]){
							case 'course':
								$query = "SELECT `course_name` FROM `courses` WHERE `course_id` = ".$db->qstr($item_details["item_value"]);
								$course_name = $db->GetOne($query); 
								$display_name = $course_name?$course_name:"Expired Course";	
								break;
							default:
								$display_name = "Item ".$return_id;
								break;
						}
						$BREADCRUMB[] = array("url" => ENTRADA_RELATIVE."/admin/payments/catalog", "title" => 'Catalog');				
						$BREADCRUMB[] = array("url" => ENTRADA_RELATIVE."/admin/payments/catalog?id=".$return_id, "title" => $display_name);				
					}
					break;
				default:
					break;
			}
		}
		
		$BREADCRUMB[] = array("url" => ENTRADA_RELATIVE."/admin/payments?id=".$PAYMENT_ID, "title" => "Transaction #".$PAYMENT_ID);	
		?>
		<h1>Transaction <?php echo "#".$PAYMENT_ID;?></h1>
		<?php
			if ($ENTRADA_ACL->amIAllowed("event", "create", false)) {
				?>
				<div style="float: right">
					<ul class="page-action">
						<li class="edit"><a href="<?php echo ENTRADA_URL; ?>/admin/payments?section=edit&id=<?php echo $PAYMENT_ID;?>" class="strong-green">Edit Transaction</a></li>
					</ul>
				</div>
				<div style="clear: both"></div>
				<?php
			}			

		$query = "	SELECT * FROM `payment_transactions` WHERE `ptransaction_id` = ".$db->qstr($PAYMENT_ID);
		if ($payment_details = $db->GetRow($query)) {
			?>
			<ul class="unstyled">
				<li><strong>Amount:</strong> <?php echo '$'.number_format($payment_details["transaction_amount"], 2, '.',',');?></li>
				<li><strong>Status:</strong> <?php echo ucwords($payment_details["payment_status"]);?></li>
				<li><strong>Payment Created:</strong> <?php echo date(DEFAULT_DATE_FORMAT,$payment_details["created_date"]);?></li>
				<?php if($payment_details["created_date"] != $payment_details["updated_date"]) { ?>
				<li><strong>Payment Updated:</strong> <?php echo date(DEFAULT_DATE_FORMAT,$payment_details["created_date"]);?></li>
				<?php } ?>
			</ul>
			<?php
			$query = "	SELECT * FROM `payment_transaction_items` a
						JOIN `payment_catalog` b
						ON a.`pcatalog_id` = b.`pcatalog_id`
						WHERE a.`ptransaction_id` = ".$db->qstr($PAYMENT_ID);
			if ($payment_items = $db->GetAll($query)) { ?>
				<table class="tableList">
					<colgroup>
						<col class="modified">				
						<col class="general">				
						<col class="general">				
					</colgroup>
					<thead>
						<tr>
							<td class="modified">&nbsp;</td>
							<td class="general">Item</td>
							<td class="general">Item Cost</td>
						</tr>
					</thead>				
					<tbody>
				<?php	
						$total = 0;
						foreach($payment_items as $payment_item){ 
							switch($payment_item["item_type"]){
								case 'course':
									$query = "SELECT `course_name` FROM `courses` WHERE `course_id` = ".$db->qstr($payment_item["item_value"]);
									if ($name = $db->GetOne($query)) {
										$display_name = "<strong>Course:</strong> ".$name;
									} else {
										$display_name = "Course";
									}
									break;
								default:
									$display_name = 'Default'.ucwords($payment_item["item_type"]);
									break;
							}
							?>
						<tr>
							<td>&nbsp;</td>
							<td><?php echo $display_name; ?></td>
							<td><?php echo '$'.number_format($payment_item["item_cost"], 2, '.',','); ?></td>
						</tr>
							
				<?php	
						$total += $payment_item["item_cost"];
						} ?>
						<tr>
							<td></td>
							<td><strong>Total Cost:</strong></td>
							<td><?php echo '$'.number_format($total, 2, '.',',');?></td>
						</tr>
					</tbody>
				</table>
			<?php
			} else {
				echo display_notice("No items where recorded on this transaction.");
			}
		} else {
			echo display_error("You do not have access to this transaction.");
		}
	} else {
		?>
		<ul class="nav nav-tabs">		
			<li class="active"><a href="<?php echo ENTRADA_URL;?>/admin/payments">Purchases</a></li>
			<li><a href="<?php echo ENTRADA_URL;?>/admin/payments/catalog">Catalog</a></li>
		</ul>
		<?php		
		if ($ENTRADA_ACL->amIAllowed("event", "create", false)) {
			?>
			<div style="float: right">
				<ul class="page-action">
					<li><a href="<?php echo ENTRADA_URL; ?>/admin/payments?section=add" class="strong-green">Add Offline Transaction</a></li>
				</ul>
			</div>
			<div style="clear: both"></div>
			<?php
		}	
		$query = "	SELECT * FROM `payment_transactions` ORDER BY `updated_date` DESC";
		if ($payments = $db->GetAll($query)) {
			if (isset($_GET["download"])) {
				$kind =  clean_input($_GET["download"],array("notags","trim"));
				switch ($kind){
					case "csv":
					default:
						ob_clean();
						$output = "Transaction ID, Payment Method, Payment Status, Create Date, Updated Date\n";
						foreach($payments as $payment) {
							$output .=	$payment["ptransaction_id"]
										.",".$payment["payment_method"].","
										.ucwords($payment["payment_status"]).","
										.date(DEFAULT_DATE_FORMAT,$payment["created_date"]).","
										.date(DEFAULT_DATE_FORMAT,$payment["updated_date"])."\n";
						}
						$file_title = "transaction-report-".time().".csv";
						header("Pragma: public");
						header("Expires: 0");
						header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
						header("Content-Type: text/csv");
						header("Content-Disposition: inline; filename=\"".$file_title."\"");
						header("Content-Length: ".@strlen($output));
						header("Content-Transfer-Encoding: binary\n");

						echo $output;											
						exit;
						break;
				}
				exit;						
			}
			?>
			<table class="tableList">
				<colgroup>
					<col class="modified">
					<col class="general">
					<col class="general">
					<col class="general">
					<col class="date">
					<col class="date">
					<col class="modified">
				</colgroup>			
				<thead>
					<tr>
						<td class="modified">&nbsp;</td>
						<td class="general">Payment ID</dh>
						<td class="general">Payment Method</td>
						<td class="general">Payment Status</td>
						<td class="date">Payment Date</td>
						<td class="date">Last Update</td>
						<td class="modified">&nbsp;</td>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td></td>
						<td style="padding-top: 10px" colspan="3"></td>					
						<td style="padding-top: 10px; text-align: right" colspan="2">
							<?php
							if ($ENTRADA_ACL->amIAllowed("event", "delete", false)) {
								?>
								<input type="button" value="Export Results" onclick="window.location='<?php echo ENTRADA_URL . "/admin/payments?download=csv"; ?>';" />
								<?php
							}
							?>
						</td>
					</tr>
				</tfoot>				
				<tbody>
					<?php
					foreach($payments as $payment) {
						?>
					<tr>
						<td></td>
						<td><a href="<?php echo ENTRADA_URL."/admin/payments?id=".$payment["ptransaction_id"];?>"><?php echo "#".$payment["ptransaction_id"];?></a></td>
						<td><a href="<?php echo ENTRADA_URL."/admin/payments?id=".$payment["ptransaction_id"];?>"><?php echo ucwords($payment["payment_method"]);?></a></td>
						<td><a href="<?php echo ENTRADA_URL."/admin/payments?id=".$payment["ptransaction_id"];?>"><?php echo ucwords($payment["payment_status"]);?></a></td>
						<td><a href="<?php echo ENTRADA_URL."/admin/payments?id=".$payment["ptransaction_id"];?>"><?php echo date(DEFAULT_DATE_FORMAT,$payment["created_date"]);?></a></td>
						<td><a href="<?php echo ENTRADA_URL."/admin/payments?id=".$payment["ptransaction_id"];?>"><?php echo date(DEFAULT_DATE_FORMAT,$payment["updated_date"]);?></a></td>
						<td><a href="<?php echo ENTRADA_URL."/admin/payments?section=edit&id=".$payment["ptransaction_id"];?>"><img src="<?php echo ENTRADA_URL;?>/images/action-edit.gif" alt="Edit Transaction" title="Edit Transaction"/></a></td>
					</tr>
						<?php
					}
					?>
				</tbody>
			</table>
			<?php		
		} else {
			echo display_notice("No Transactions Have Occured.");
		}
	}
	
}