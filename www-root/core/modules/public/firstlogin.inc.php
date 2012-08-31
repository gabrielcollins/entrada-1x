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
 * $Id: firstlogin.inc.php 1171 2010-05-01 14:39:27Z ad29 $
 */

if(!defined("PARENT_INCLUDED")) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->amIAllowed('firstlogin', 'read')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	?>
	<form action="<?php echo ENTRADA_URL; ?>/profile" method="post">
	<?php
	echo "<input type=\"hidden\" name=\"action\" value=\"".((!(int) $_SESSION["details"]["privacy_level"]) ? (($_SESSION["details"]["google_id"] == "opt-in") ? "privacy-google-update" : "privacy-update") : (($_SESSION["details"]["google_id"] == "opt-in") ? "google-update" : ""))."\" />\n";
	echo (($PROCEED_TO) ? "<input type=\"hidden\" name=\"redirect\" value=\"".rawurlencode($PROCEED_TO)."\" />\n" : "");
	?>
	<h1>Welcome To <?php echo APPLICATION_NAME; ?>!</h1>
	This is your first time logging in and we need to collect a bit of information to fully provision your account.
	<table style="width: 100%" cellspacing="4" cellpadding="2" border="0">
	<colgroup>
		<col style="width: 3%" />
		<col style="width: 97%" />
	</colgroup>
	<tfoot>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
				<input type="submit" class="button" value="Proceed" />
			</td>
		</tr>
	</tfoot>
	<tbody>
	<?php
	/**
	 * Google Hosted Apps Account
	 */
	if($_SESSION["details"]["google_id"] == "opt-in") {
		?>
		<tr>
			<td colspan="2">
				<h2>Create Your <strong><?php echo $GOOGLE_APPS["domain"]; ?></strong> Google Account</h2>
				<div class="display-generic">
					Would you like to create a <strong><?php echo $GOOGLE_APPS["domain"]; ?></strong> account, powered by Google? This exciting new ability gives you your own personal <?php echo $GOOGLE_APPS["quota"]; ?> e-mail address @<?php echo $GOOGLE_APPS["domain"]; ?> that you can keep <em>indefinitely</em>! In addition to e-mail, you also have access to your own personal calendar space, and a powerful suite of online document tools.
				</div>
			</td>
		</tr>
		<tr>
			<td style="vertical-align: top"><input type="radio" id="google_account_1" name="google_account" value="1" checked="checked" /></td>
			<td style="vertical-align: top">
				<label for="google_account_1"><strong>Yes Please!</strong>: create my <?php echo $GOOGLE_APPS["domain"]; ?> account</strong>.</label><br />
				<span class="content-small">Your account will be automatically created, and activation information will be sent to <strong><?php echo $_SESSION["details"]["email"]; ?></strong>.</span>
			</td>
		</tr>
		<tr>
			<td style="vertical-align: top"><input type="radio" id="google_account_0" name="google_account" value="0" /></td>
			<td style="vertical-align: top">
				<label for="google_account_0"><strong>No Thank-you</strong>: please do not create me an account at this time.</label><br />
				<span class="content-small">If you decide you would like one in the future, simply contact the system administrator.</span>
			</td>
		</tr>
	<?php
	}

	/**
	 * Privacy Level Settings
	 */
	if(!(int) $_SESSION["details"]["privacy_level"]) {
		?>
		<tr>
			<td colspan="2">
				<table style="width: 100%" cellspacing="1" cellpadding="1" border="0" summary="My <?php echo APPLICATION_NAME;?> Profile Privacy">
					<colgroup>
						<col style="width: 25%" />
						<col style="width: 75%" />
					</colgroup>
					<tbody>
						<tr>
							<td colspan="2">
								<h2>Privacy Level Setting</h2>
								<div class="display-generic">
									<?php echo APPLICATION_NAME; ?> contains a <strong>People Search</strong> tab, which acts a directory of people associated with your institution. You can lookup people using a simple name search or by browsing through groups. Please tell us how much information you wish to reveal about yourself when other students, faculty, or staff associated with the school use People Search (i.e. after they log in).
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<table style="width: 100%" cellspacing="4" cellpadding="2" border="0">
								<colgroup>
									<col style="width: 3%" />
									<col style="width: 97%" />
								</colgroup>
								<tbody>
									<tr>
										<td style="vertical-align: top"><input type="radio" id="privacy_level_3" name="privacy_level" value="3" /></td>
										<td style="vertical-align: top">
											<label for="privacy_level_3"><strong>Complete Profile</strong>: show the information I choose to provide.</label><br />
											<span class="content-small">This means that normal logged in users will be able to view any information you provide in the <strong>My Profile</strong> section. You can provide as much or as little information as you would like; however, whatever you provide will be displayed.</span>
										</td>
									</tr>
									<tr>
										<td style="vertical-align: top"><input type="radio" id="privacy_level_2" name="privacy_level" value="2" checked="checked" /></td>
										<td style="vertical-align: top">
											<label for="privacy_level_2"><strong>Typical Profile</strong>: show basic information about me.</label><br />
											<span class="content-small">This means that normal logged in users will only be able to view your name, email address, role, official photo and uploaded photo if you have added one, regardless of how much information you provide in the <strong>My Profile</strong> section.</span>
										</td>
									</tr>
									<tr>
										<td style="vertical-align: top"><input type="radio" id="privacy_level_1" name="privacy_level" value="1" /></td>
										<td style="vertical-align: top">
											<label for="privacy_level_1"><strong>Minimal Profile</strong>: show minimal information about me.</label><br />
											<span class="content-small">This means that normal logged in users will only be able to view your name and role. In other words, people will not be able to get your e-mail address or other contact information.</span>
										</td>
									</tr>
								</tbody>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
		<?php
	}
	?>
	</tbody>
	</table>
	</form>
	<?php
}