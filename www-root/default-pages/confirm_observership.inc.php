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
 * Observership confirmation page.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <ryan.warner@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 *
 */

if(!defined("PARENT_INCLUDED")) exit;

$unique_id = clean_input($_GET["unique_id"], "alphanumeric");

if ($unique_id) {
	
	echo "<h1>Observership Confirmation</h1>";

	require_once(ENTRADA_CORE."/library/Models/utility/Editable.interface.php");
	require_once(ENTRADA_CORE."/library/Models/mspr/Observership.class.php");

	$step		= (isset($_POST["step"]) ? (int) $_POST["step"] : '1');
	$action		= (isset($_POST["action"]) ? (strtolower($_POST["action"]) == "confirm") ? "confirm" : ((strtolower($_POST["action"]) == "reject") ? "reject" : "" ) : "");

	$obs = Observership::getByUniqueID($unique_id);

	if ($obs && $obs->getStatus() == "UNCONFIRMED") {

		switch ($step) {
			case 2 :
				// update the status to confirmed or rejected
				$obs->update(array(	"title" => $obs->getTitle(), 
									"site" => $obs->getSite(),
									"location" => $obs->getLocation(),
									"preceptor_proxy_id" => (($obs->getPreceptor()) ? $obs->getPreceptor()->getProxyId() : NULL), 
									"preceptor_firstname" => $obs->getPreceptorFirstname(), 
									"preceptor_lastname" => $obs->getPreceptorLastname(), 
									"start" => $obs->getStart(), 
									"end" => $obs->getEnd(), 
									"preceptor_prefix" => $obs->getPreceptorPrefix(), 
									"preceptor_email" => $obs->getPreceptorEmail(), 
									"status" => strtoupper($action."ed"), 
									"id" => $obs->getID()
								));
				echo display_success();
			break;
			default :
			continue;
		}

		switch ($step) {
			case 1 :
		?>

	<p><?php echo (($obs->getPreceptorPrefix() ? $obs->getPreceptorPrefix()." " : "").$obs->getPreceptorFirstname()." ".$obs->getPreceptorLastname()); ?>,</p>
	<p>The user <?php echo $obs->getUser()->getFullname(false); ?> has indicated you were the preceptor for the following observership:</p>
	<ul class="mspr-list ">
		
		<li class="entry">
			
			<span class="label">Title:</span>
			<span class="data"><?php echo $obs->getTitle(); ?></span>
			<span class="label">Site:</span>
			<span class="data"><?php echo $obs->getSite(); ?></span>
			<span class="label">Location:</span>
			<span class="data"><?php echo $obs->getLocation(); ?></span>
			<span class="label">Period:</span>
			<span class="data"><?php echo $obs->getPeriod(); ?></span>
		
		</li>
		
	</ul>
	
	<p>Please confirm or reject the observership with the buttons below.</p>
	
	<form action="<?php echo ENTRADA_URL."/confirm_observership?unique_id=".$unique_id; ?>" method="POST">
		<input type="hidden" value="<?php echo $step + 1; ?>" name="step" />
		<input type="submit" value="Confirm" name="action" />
		<input type="submit" value="Reject" name="action" />
	</form>

	<?php 
			break;
			default :
			continue;
		}

	} else {
		add_error("Observership not found.");
		echo display_error();
	}
}