
<?php 

function award_details_edit($award) {
	
	ob_start();
?>
<form class="edit_award_form" action="<?php echo ENTRADA_URL; ?>/admin/awards?section=award_details&id=<?php echo $award->getID(); ?>" method="post" >
	<input type="hidden" name="action" value="edit_award_details"></input>
	<input type="hidden" name="award_id" value="<?php echo $award->getID(); ?>"></input>
	<table class="award_details" style="width: 100%">
		<colgroup>
			<col width="3%"></col>
			<col width="25%"></col>
			<col width="72%"></col>
		</colgroup>
		<tfoot>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<tr>
				<td>
					<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/awards'" />
				</td>
				<td colspan="2" style="padding-top: 5px; text-align: right">
					<input type="submit" class="button" value="Submit Changes" />
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php 
		$disabled = $award->isDisabled();
		?>
			<tr>
				<td>&nbsp;</td>
				<td>
					<label for="award_title" class="form-required">Title:</label>
				</td>
				<td>
					<input id="award_title" name="award_title" type="text" maxlength="4096" style="width: 250px; vertical-align: middle;" value="<?php echo clean_input($award->getTitle(), array("notags", "specialchars")) ?>"></input>	
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td style="vertical-align:top;">
					<label for="award_terms" class="form-required">Terms of Award:</label>
				</td>
				<td>
					<textarea id="award_terms" name="award_terms" style="width: 100%; height: 100px;" cols="65" rows="20"><?php echo clean_input($award->getTerms(), array("notags", "specialchars")) ?></textarea>	
				</td>
			</tr>
			<tr>
			<td>&nbsp;</td>
			<td style="vertical-align:top;">
				<label for="award_disabled" class="form-nrequired">Disabled:</label>
			</td>
			<td><input type="radio" name="award_disabled" id="award_disabled_0" value="0"<?php if(!$disabled) echo " checked=\"checked\"";?>></input><label for="award_disabled_0">No</label><br />
			<input type="radio" name="award_disabled" id="award_disabled_1" value="1"<?php if($disabled) echo " checked=\"checked\"";?>></input><label for="award_disabled_1">Yes</label></td>
			</tr>
		</tbody>
	</table>
</form>
<?php
	return ob_get_clean();
}

function award_recipients_list(InternalAward $award) {
		$receipts = $award->getRecipients();
		?>
		<table class="award_history tableList" cellspacing="0">
			<colgroup>
				<col width="75%"></col>
				<col width="15%"></col>
				<col width="10%"></col>
			</colgroup>
			<thead>
				<tr>
					<td class="general" style="border-left: 1px solid #999;">
						Full Name
					</td>
					<td class="sortedDESC">
						Year Awarded
					</td>
					<td class="general">&nbsp;</td>
				</tr>
				</thead>
		<?php 
		if ($receipts) {
			foreach ($receipts as $receipt) {
				$user = $receipt->getUser();
				//$award = $recipient->getAward();
				?>
				<tr>
					<td class="general">
						<a href="<?php echo ENTRADA_URL; ?>/admin/users/manage?id=<?php echo $user->getID();?>"><?php echo clean_input($user->getFullname(), array("notags", "specialchars")) ?></a>
					</td>
					<td class="general">
						<?php echo clean_input($receipt->getAwardYear(), array("notags", "specialchars")) ?>
					</td>
					<td><form class="remove_award_recipient_form" action="<?php echo ENTRADA_URL; ?>/admin/awards?section=award_details&id=<?php echo $award->getID(); ?>" method="post" >
							<input type="hidden" name="internal_award_id" value="<?php echo clean_input($receipt->getID(), array("notags", "specialchars")); ?>"></input>
							<input type="hidden" name="action" value="remove_award_recipient"></input>
							<input type="hidden" name="award_id" value="<?php echo $award->getID(); ?>"></input>
							
							<input type="image" src="<?php echo ENTRADA_URL ?>/images/action-delete.gif"></input> 
						</form>
						</td>
				</tr>
				<?php 
			}
		}
		?>
		</table>
		<?php
}

function awards_list($awards = array()) {
	if (is_array($awards) && !empty($awards)) {
		?>
		<table class="manage_awards tableList" cellspacing="0" summary="List of Awards">
		<colgroup>
			<col class="title" width="45%" />
			<col class="award_terms" width="50%" />
			<col class="controls" width="5%" />
		</colgroup>
		<thead>
			<tr>
				<td class="title sortedASC borderl" style="font-size: 12px"><div class="noLink">Title</div></td>
				<td class="award_terms" style="font-size: 12px">Terms of Award</td>
				<td class="controls">&nbsp;</td>
			</tr>
		</thead>
		<tbody>
		<?php 
		foreach($awards as $award) {
			?>
			<tr<?php if ($award->isDisabled()) echo " class=\"disabled\""; ?>>
				<td class="title"><a href="<?php echo ENTRADA_URL; ?>/admin/awards?section=award_details&id=<?php echo $award->getID(); ?>">
					<?php echo clean_input($award->getTitle(), array("notags", "specialchars")) ?></a>	
				</td>
				<td class="award_terms">
					<?php
					$award_terms = clean_input($award->getTerms(), array("notags", "specialchars"));
					if (strlen($award_terms) > 152) {
						$award_terms = preg_replace("/([\s\S]{150,}?[\.\s,])[\s\S]*/", "$1...", $award_terms );
					}
					echo $award_terms; ?>	
				</td>
				<td class="controls">
					<form class="remove_award_form" action="<?php echo ENTRADA_URL; ?>/admin/awards?id=<?php echo $award_id; ?>" method="post" >
						<input type="hidden" name="award_id" value="<?php echo clean_input($award->getID(), array("notags", "specialchars")); ?>"></input>
						<input type="hidden" name="action" value="remove_award"></input>
						
						<input type="image" src="<?php echo ENTRADA_URL ?>/images/action-delete.gif"></input> 
					</form>
				</td>
			</tr>
			<?php 
		}
		?>
		</tbody>
		</table>
		<?php
	} else {
		echo display_notice(array("There are no awards in the system at this time, please click <strong>Add Award</strong> to begin."));
	}
}


/**
 * Deletes the specificed award-user pair record.
 * @param $comment_id
 */
function edit_award_details($award,$title=null,$terms=null,$disabled=null) {
	
	if ($award->isDisabled() && !$disabled) {
		$award->enable();
	} elseif (!($award->isDisabled()) && $disabled) {
		$award->disable();
	} else {
		$award->update($title,$terms);
	}
	
}

/**
 * Processes the various sections of the MSPR module
 */
function process_manage_award_details() {
	
	if (isset($_POST['action'])) {
		$action = $_POST['action'];
		switch($action) {
			
			case "add_award_recipient": 
				$award_id = (isset($_POST['award_id']) ? $_POST['award_id'] : 0);
				$user_id = (isset($_POST['internal_award_user_id']) ? $_POST['internal_award_user_id'] : 0);
				if ($user_id && $award_id) {
					$year = $_POST['internal_award_year'];
					$info = array("award_id" => $award_id,
								   "user_id" => $user_id,
								   "year" => $year);
					InternalAwardReceipt::create($info);
				}
			break;
		
			case "remove_award_recipient":
				$id = (isset($_POST['internal_award_id']) ? $_POST['internal_award_id'] : 0);
				if ($id) {
					$recipient = InternalAwardReceipt::get($id);
					if ($recipient) {
						$recipient->delete();
					}
				}
			break;
			
			case "edit_award_details":
				$award_id = (isset($_POST['award_id']) ? $_POST['award_id'] : 0);
				$disabled = (bool)($_POST['award_disabled']);
 
				$title = clean_input($_POST['award_title'], array("notags","specialchars"));
				$terms = clean_input($_POST['award_terms'], array("notags","specialchars", "nl2br"));
				if (!$title || !$terms) {
					add_error("Insufficient information please check the fields and try again");
				} else {
					if ($award_id) {
						$award = InternalAward::get($award_id);
						if ($award) {
							edit_award_details($award, $title, $terms, $disabled);
						}
					} else {
						add_error("Award not found");
					}
				}
			break;
			
			case "new_award":
				$title = clean_input($_POST['award_title'], array("notags","specialchars"));
				$terms = clean_input($_POST['award_terms'], array("notags","specialchars", "nl2br"));
				if (!$title || !$terms) {
					add_error("Insufficient information please check the fields and try again");
				} else {
					InternalAward::create($title,$terms);
				}
			break;
			
			case "remove_award":
				$award_id = (isset($_POST['award_id']) ? $_POST['award_id'] : 0);
				if ($award_id) {
					$award = InternalAward::get($award_id);
					$award->delete();		
				}
			break;
				
		}
	}

}


function process_awards_admin() {
	if (isset($_POST['action'])) {
			$action = $_POST['action'];
						
			switch($action) {
				
				case "add_award_recipient": 
				case "remove_award_recipient":
				case "edit_award_details":
					$award_id = (isset($_POST['award_id']) ? $_POST['award_id'] : 0);
					if ($award_id) {
						$award = InternalAward::get($award_id);		
						process_manage_award_details();		
						display_status_messages();
						
						echo award_recipients_list($award);
					}
				break;
				
				case "remove_award":
					$award_id = (isset($_POST['award_id']) ? $_POST['award_id'] : 0);
					if (! $award_id) {
						break;
					}		
				case "new_award":
					process_manage_award_details();		
					display_status_messages();
					$awards = InternalAwards::get(true);
					if ($awards) {
						echo awards_list($awards); 
					}
				break;
					
			}
		}	
}