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
	
	if ($CATALOG_ID) {
		$query = "	SELECT a.*,b.`payment_name` FROM `payment_catalog` a
					LEFT JOIN `payment_options` b
					ON a.`poption_id` = b.`poption_id`
					WHERE a.`pcatalog_id` = ".$db->qstr($CATALOG_ID);
		
		if ($item_details = $db->GetRow($query)) {					
			switch($item_details["item_type"]){
				case 'course':
					$query = "SELECT `course_name` FROM `courses` WHERE `course_id` = ".$db->qstr($item_details["item_value"]);
					$course_name = $db->GetOne($query); 
					$display_name = $course_name?$course_name:"Expired Course";	
					break;
				default:
					$display_name = "Item ".$CATALOG_ID;
					break;
			}

			$query = "	SELECT a.* FROM
						`payment_transactions` a
						JOIN `payment_transaction_items` b
						ON a.`ptransaction_id` = b.`ptransaction_id`
						WHERE b.`pcatalog_id` = ".$db->qstr($CATALOG_ID)."
						ORDER BY a.`updated_date` DESC";
			$payments = $db->GetAll($query);
			
			if (isset($_GET["download"])) {
				$kind =  clean_input($_GET["download"],array("notags","trim"));
				switch ($kind){
					case "csv":
					default:
						ob_clean();
						$output = "";
						$output .= "Status:,".($item_details["active"]?"Active":"Deactived")."\n";
						$output .= "Item Type:,".ucwords($item_details["item_type"])."\n";
						$output .= "Item Cost:,".ucwords($item_details["item_cost"])."\n";
						$output .= "Payment Option:,".($item_details["payment_name"]?$item_details["payment_name"]:'No Account Attached')."\n";
						$output .= "Quantity:,".($item_details["quantity"] == -1?'Unlimited':$item_details["quantity"])."\n\n";

						if ($payments) {
						$output .= "Transaction ID, Payment Method, Payment Status, Create Date, Updated Date\n";
						foreach($payments as $payment) {
							$output .=	"#".$payment["ptransaction_id"]
										.",".$payment["payment_method"].","
										.ucwords($payment["payment_status"]).","
										.date(DEFAULT_DATE_FORMAT,$payment["created_date"]).","
										.date(DEFAULT_DATE_FORMAT,$payment["updated_date"])."\n";
						}							
						} else {
							$ouput .= "No transactions for item.";
						}
						$file_title = "inventory-item-".$CATALOG_ID."-report-".time().".csv";
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
			
			$BREADCRUMB[] = array("url" => ENTRADA_RELATIVE."/admin/payments/catalog?id=".$PAYMENT_ID, "title" => $display_name);							
		?>
		<h1><?php echo $display_name;?></h1>
		<?php
			if ($ENTRADA_ACL->amIAllowed("event", "create", false)) {
				?>
				<div style="float: right">
					<ul class="page-action">
						<li class="edit"><a href="<?php echo ENTRADA_URL; ?>/admin/payments/catalog?section=edit&id=<?php echo $CATALOG_ID;?>" class="strong-green">Edit Item</a></li>
					</ul>
				</div>
				<div style="clear: both"></div>
				<?php
			}			
		?>
			<ul class="unstyled">
				<li><strong>Status:</strong> <?php echo $item_details["active"]?"Active":"Deactived";?></li>
				<li><strong>Item Type:</strong> <?php echo ucwords($item_details["item_type"]);?></li>
				<li><strong>Item Cost:</strong> <?php echo '$'.$item_details["item_cost"];?></li>
				<li><strong>Payment Option:</strong> <?php echo $item_details["payment_name"]?$item_details["payment_name"]:'No Account Attached';?></li>
				<li><strong>Quantity Remaining:</strong> <?php echo $item_details["quantity"] == -1?'Unlimited':$item_details["quantity"];?></li>
			</ul>		
		<h2>Item Transactions</h2>
		<?php			
			if ($payments) {
				?>
				<table class="tableList">
					<colgroup>
						<col class="modified">
						<col class="general">
						<col class="general">
						<col class="general">
						<col class="date">
						<col class="date">
					</colgroup>			
					<thead>
						<tr>
							<td class="modified">&nbsp;</td>
							<td class="general">Payment ID</dh>
							<td class="general">Payment Method</td>
							<td class="general">Payment Status</td>
							<td class="date">Payment Date</td>
							<td class="date">Last Update</td>
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
								<input type="button" value="Export Results" onclick="window.location='<?php echo ENTRADA_URL . "/admin/payments/catalog?id=".$CATALOG_ID."&download=csv"; ?>';" />
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
							<td><a href="<?php echo ENTRADA_URL."/admin/payments?id=".$payment["ptransaction_id"];?>&return=item&return_id=<?php echo $CATALOG_ID;?>"><?php echo "#".$payment["ptransaction_id"];?></a></td>
							<td><a href="<?php echo ENTRADA_URL."/admin/payments?id=".$payment["ptransaction_id"];?>&return=item&return_id=<?php echo $CATALOG_ID;?>"><?php echo ucwords($payment["payment_method"]);?></a></td>
							<td><a href="<?php echo ENTRADA_URL."/admin/payments?id=".$payment["ptransaction_id"];?>&return=item&return_id=<?php echo $CATALOG_ID;?>"><?php echo ucwords($payment["payment_status"]);?></a></td>
							<td><a href="<?php echo ENTRADA_URL."/admin/payments?id=".$payment["ptransaction_id"];?>&return=item&return_id=<?php echo $CATALOG_ID;?>"><?php echo date(DEFAULT_DATE_FORMAT,$payment["created_date"]);?></a></td>
							<td><a href="<?php echo ENTRADA_URL."/admin/payments?id=".$payment["ptransaction_id"];?>&return=item&return_id=<?php echo $CATALOG_ID;?>"><?php echo date(DEFAULT_DATE_FORMAT,$payment["updated_date"]);?></a></td>
						</tr>
							<?php
						}
						?>
					</tbody>
				</table>
				<?php		
			} else {
				echo display_notice("No transactions have occurred which included this item.");
			}		
		
		} else {
			echo display_error("Invalid ID Provided.");
		}
	} else {
		?>
		<ul class="nav nav-tabs">		
			<li><a href="<?php echo ENTRADA_URL;?>/admin/payments">Purchases</a></li>
			<li class="active"><a href="<?php echo ENTRADA_URL;?>/admin/payments/catalog">Catalog</a></li>
		</ul>
		<?php		
		if ($ENTRADA_ACL->amIAllowed("event", "create", false)) {
			?>
			<div style="float: right">
				<ul class="page-action">
					<li><a href="<?php echo ENTRADA_URL; ?>/admin/payments/catalog?section=add" class="strong-green">Add New Item</a></li>
				</ul>
			</div>
			<div style="clear: both"></div>
			<?php
		}	
		
		$query = "	SELECT * FROM `payment_catalog` WHERE `organisation_id` = ".$db->qstr($ENTRADA_USER->getOrganisationId());
		if ($items = $db->GetAll($query)) {
			if (isset($_GET["download"])) {
				$kind =  clean_input($_GET["download"],array("notags","trim"));
				switch ($kind){
					case "csv":
					default:
						ob_clean();
						$output = "Catalog ID, Item Type, Item Value, Cost, Quantity, Payment Option\n";
						foreach($items as $item) {							
							$output .=	$item["pcatalog_id"].","
										.ucwords($item["item_type"]).","
										.$item["item_value"].","
										.$item["item_cost"].","
										.($item["quantity"] == -1?'Unlimited':$item["quantity"]).","										
										.$item["poption_id"]."\n";
						}
						$file_title = "inventory-report-".time().".csv";
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
		<form action="<?php echo ENTRADA_URL;?>/admin/payments/catalog?section=delete" method="POST">
			<table class="tableList">
				<colgroup>
					<col class="modified">
					<col class="general">
					<col class="general">
					<col class="general">
					<col class="general">
					<col class="general">
					<col class="modified">
				</colgroup>			
				<thead>
					<tr>
						<td class="modified">&nbsp;</td>
						<td class="general">Item Type</dh>
						<td class="general">Item Value</td>
						<td class="general">Item Cost</td>
						<td class="general">Quantity</td>
						<td class="general">Payment Option</td>
						<td class="modified">&nbsp;</td>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td></td>
						<td style="padding-top: 10px" colspan="3">
							<?php
							if ($ENTRADA_ACL->amIAllowed("event", "delete", false)) {
								?>
								<input type="submit" class="button" value="Delete Selected" />
								<?php
							}
							?>
						</td>
						<td style="padding-top: 10px; text-align: right" colspan="2">
							<?php
							if ($ENTRADA_ACL->amIAllowed("event", "delete", false)) {
								?>
								<input type="button" value="Export Results" onclick="window.location='<?php echo ENTRADA_URL . "/admin/payments/catalog?download=csv"; ?>';" />
								<?php
							}
							?>
						</td>
					</tr>
				</tfoot>				
				<tbody>
					<?php
					foreach($items as $item) {
						?>
					<tr>
						<td><input type="checkbox" name="remove_ids[]" value="<?php echo $item["pcatalog_id"];?>"/></td>
						<td><a href="<?php echo ENTRADA_URL."/admin/payments/catalog?id=".$item["pcatalog_id"];?>"><?php echo ucwords($item["item_type"]);?></a></td>
						<td><a href="<?php echo ENTRADA_URL."/admin/payments/catalog?id=".$item["pcatalog_id"];?>"><?php echo $item["item_value"];?></a></td>
						<td><a href="<?php echo ENTRADA_URL."/admin/payments/catalog?id=".$item["pcatalog_id"];?>"><?php echo $item["item_cost"];?></a></td>
						<td><a href="<?php echo ENTRADA_URL."/admin/payments/catalog?id=".$item["pcatalog_id"];?>"><?php echo $item["quantity"] == -1?'Unlimited':$item["quantity"];?></a></td>
						<td><a href="<?php echo ENTRADA_URL."/admin/payments/catalog?id=".$item["pcatalog_id"];?>"><?php echo $item["poption_id"];?></a></td>
						<td><a href="<?php echo ENTRADA_URL."/admin/payments/catalog?section=edit&id=".$item["pcatalog_id"];?>"><img src="<?php echo ENTRADA_URL;?>/images/action-edit.gif" alt="Edit Item" title="Edit Item"/></a></td>
					</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</form>
			<?php		
		} else {
			echo display_notice("No items have been added to the catalog.");
		}
	}
	
}