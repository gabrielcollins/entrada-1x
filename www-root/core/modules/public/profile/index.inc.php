<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * This file gives Entrada users the ability to update their user profile.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/
if (!defined("IN_PROFILE")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->isLoggedInAllowed('profile', 'read')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {

	$PAGE_META["title"]			= "My Profile";
	$PAGE_META["description"]	= "";
	$PAGE_META["keywords"]		= "";

	$PROXY_ID					= $_SESSION["details"]["id"];
	$VALID_MIME_TYPES			= array("image/pjpeg" => "jpg", "image/jpeg" => "jpg", "image/jpg" => "jpg", "image/gif" => "gif", "image/png" => "png");
	$VALID_MAX_FILESIZE			= 2097512; // 2MB
	$VALID_MAX_DIMENSIONS		= array("photo-width" => 216, "photo-height" => 300, "thumb-width" => 75, "thumb-height" => 104);
	$RENDER						= false;

	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/profile", "title" => "Personal Information");

	$PROCESSED		= array();

	if ((is_array($_SESSION["permissions"])) && ($total_permissions = count($_SESSION["permissions"]) > 1)) {
		$sidebar_html  = "The following individual".((($total_permissions - 1) != 1) ? "s have" : " has")." given you access to their ".APPLICATION_NAME." permission levels:";
		$sidebar_html .= "<ul class=\"menu\">\n";
		foreach ($_SESSION["permissions"] as $proxy_id => $result) {
			if ($proxy_id != $_SESSION["details"]["id"]) {
				$sidebar_html .= "<li class=\"checkmark\"><strong>".html_encode($result["fullname"])."</strong><br /><span class=\"content-small\">Exp: ".(($result["expires"]) ? date("D M d/y", $result["expires"]) : "Unknown")."</span></li>\n";
			}
		}
		$sidebar_html .= "</ul>\n";

		new_sidebar_item("Delegated Permissions", $sidebar_html, "delegated-permissions", "open");
	}

	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/tabpane/tabpane.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
	$HEAD[] = "<link href=\"".ENTRADA_URL."/css/tabpane.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
	$HEAD[] = "<style type=\"text/css\"> .dynamic-tab-pane-control .tab-page {height:auto;}</style>\n";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/profile.js\"></script>";

	if (file_exists(STORAGE_USER_PHOTOS."/".$PROXY_ID."-upload")) {
		$size_upload = getimagesize(STORAGE_USER_PHOTOS."/".$PROXY_ID."-upload");
	}
	if (file_exists(STORAGE_USER_PHOTOS."/".$PROXY_ID."-official")) {
		$size_official = getimagesize(STORAGE_USER_PHOTOS."/".$PROXY_ID."-official");
	}
	
	if ($ERROR) {
		fade_element("out", "display-error-box");
		echo display_error();
	}

	if ($SUCCESS) {
		fade_element("out", "display-success-box");
		echo display_success();
	}

	if ($NOTICE) {
		fade_element("out", "display-notice-box");
		echo display_notice();
	}
	
	$ONLOAD[] = "provStateFunction(\$F($('profile-update')['country_id']))";
	
	$query	= "SELECT * FROM `".AUTH_DATABASE."`.`user_data` WHERE `".AUTH_DATABASE."`.`user_data`.`id`=".$db->qstr($_SESSION["details"]["id"]);
	$result	= $db->GetRow($query);
	if ($result) {
		?>
		<script type="text/javascript">
		function provStateFunction(country_id) {
			var url='<?php echo webservice_url("province"); ?>';
			<?php
				if ($PROCESSED["province"] || $PROCESSED["province_id"]) {
					$source_arr = $PROCESSED;
				} else {
					$source_arr = $result;
				}
				$province = $source_arr["province"];
				$province_id = $source_arr["province_id"];
				$prov_state = ($province) ? $province : $province_id;
			?>

			url = url + '?countries_id=' + country_id + '&prov_state=<?php echo $prov_state; ?>';
			new Ajax.Updater($('prov_state_div'), url,
				{
					method:'get',
					onComplete: function (init_run) {

						if ($('prov_state').type == 'select-one') {
							$('prov_state_label').removeClassName('form-nrequired');
							$('prov_state_label').addClassName('form-required');
							if (!init_run)
								$("prov_state").selectedIndex = 0;


						} else {

							$('prov_state_label').removeClassName('form-required');
							$('prov_state_label').addClassName('form-nrequired');
							if (!init_run)
								$("prov_state").clear();


						}
					}.curry(!provStateFunction.initialzed)
				});
			provStateFunction.initialzed = true;

		}
		provStateFunction.initialzed = false;

		</script>

		<h1 style="margin-top: 0px">Personal Information</h1>
		This section allows you to update your <?php echo APPLICATION_NAME; ?> user profile information. Please note that this information does not necessarily reflect any information stored at the main University. <span style="background-color: #FFFFCC; padding-left: 5px; padding-right: 5px">This is not your official university contact information.</span>
		<br /><br />

		<form name="profile-update" id="profile-update" action="<?php echo ENTRADA_URL; ?>/profile" method="post" enctype="multipart/form-data" accept="<?php echo ((@is_array($VALID_MIME_TYPES)) ? implode(",", array_keys($VALID_MIME_TYPES)) : ""); ?>">
			<input type="hidden" name="action" value="profile-update" />
			<table style="width: 100%" cellspacing="1" cellpadding="1" border="0" summary="My <?php echo APPLICATION_NAME;?> Profile Information">
				<colgroup>
					<col style="width: 25%" />
					<col style="width: 75%" />
				</colgroup>
				<tfoot>
					<tr>
						<td colspan="2" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
							<input type="submit" class="button" value="Update Profile" />
						</td>
					</tr>
				</tfoot>
				<tbody>
					<tr>
						<td><strong>Last Login:</strong></td>
						<td><?php echo ((!$_SESSION["details"]["lastlogin"]) ? "Your first login" : date(DEFAULT_DATE_FORMAT, $_SESSION["details"]["lastlogin"])); ?></td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td><strong>Username:</strong></td>
						<td><?php echo html_encode($_SESSION["details"]["username"]); ?></td>
					</tr>
					<tr>
						<td><strong>Password:</strong></td>
						<td><a href="<?php echo PASSWORD_CHANGE_URL; ?>">Click here to change password</a></td>
					</tr>
					<tr>
						<td><strong>Account Type:</strong></td>
						<td><?php echo ucwords($_SESSION["details"]["group"])." &rarr; ".ucwords($_SESSION["details"]["role"]); ?></td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td><strong>Organisation:</strong></td>
						<td>
							<?php
							$query		= "SELECT `organisation_title` FROM `".AUTH_DATABASE."`.`organisations` WHERE `organisation_id` = ".$_SESSION['details']['organisation_id'];
							$oresult	= $db->GetRow($query);
							if($oresult) {
								echo $oresult['organisation_title'];
							}
							?>
						</td>
					</tr>
					<?php if (isset($_SESSION["details"]["grad_year"])) : ?>
					<tr>
						<td><strong>Graduating Year:</strong></td>
						<td>Class of <?php echo html_encode($_SESSION["details"]["grad_year"]); ?></td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<?php endif; ?>
					<tr>
						<td><label for="prefix"><strong>Full Name:</strong></label></td>
						<td>
							<select id="prefix" name="prefix" style="width: 55px; vertical-align: middle; margin-right: 5px">
								<option value=""<?php echo ((!$result["prefix"]) ? " selected=\"selected\"" : ""); ?>></option>
								<?php
								if ((@is_array($PROFILE_NAME_PREFIX)) && (@count($PROFILE_NAME_PREFIX))) {
									foreach ($PROFILE_NAME_PREFIX as $key => $prefix) {
										echo "<option value=\"".html_encode($prefix)."\"".(($result["prefix"] == $prefix) ? " selected=\"selected\"" : "").">".html_encode($prefix)."</option>\n";
									}
								}
								?>
							</select>
							<?php echo html_encode($result["firstname"]." ".$result["lastname"]); ?>
						</td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<?php if($_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"] == "faculty") { ?>
					<tr>
						<td><label for="email" class="form-required"><strong>Primary E-Mail:</strong></label></td>
						<td>	
							<input type="text" id="email" name="email" value="<?php echo html_encode($result["email"]); ?>" style="width: 250px; vertical-align: middle" maxlength="128" />
						</td>
					</tr>
					<?php } else { ?>
					<tr>
						<td><strong>Primary E-Mail:</strong></td>
						<td><a href="mailto:<?php echo html_encode($result["email"]); ?>"><?php echo html_encode($result["email"]); ?></a></td>
					</tr>
					<?php } ?>
					<tr>
						<td><label for="email_alt"><strong>Secondary E-Mail:</strong></label></td>
						<td>
							<input type="text" id="email_alt" name="email_alt" value="<?php echo html_encode($result["email_alt"]); ?>" style="width: 250px; vertical-align: middle" maxlength="128" />
						</td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<?php
					if (((bool) $GOOGLE_APPS["active"]) && $result["google_id"]) {
						?>
						<tr>
							<td style="vertical-align: top"><label for="email_alt" style="font-weight: bold">Google Account:</label></td>
							<td style="vertical-align: top">
								<div id="google-account-details">
									<?php
									if (($result["google_id"] == "") || ($result["google_id"] == "opt-out") || ($result["google_id"] == "opt-in") || ($_SESSION["details"]["google_id"] == "opt-in")) {
										?>
										Your <?php echo $GOOGLE_APPS["domain"]; ?> account is <strong>not active</strong>. ( <a href="javascript: create_google_account()" class="action">create my account</a> )
										<script type="text/javascript">
										function create_google_account() {
											$('google-account-details').update('<img src="<?php echo ENTRADA_RELATIVE; ?>/images/indicator.gif\" width=\"16\" height=\"16\" alt=\"Please wait\" border=\"0\" style=\"margin-right: 2px; vertical-align: middle\" /> <span class=\"content-small\">Please wait while your account is created ...</span>');
											new Ajax.Updater('google-account-details', '<?php echo ENTRADA_URL; ?>/profile', { method: 'post', parameters: { 'action' : 'google-update', 'google_account' : 1, 'ajax' : 1 }});
										}
										</script>
										<?php
									} else {
										$google_address = html_encode($result["google_id"]."@".$GOOGLE_APPS["domain"]);
										?>
										<a href="mailto:"<?php echo $google_address; ?>"><?php echo $google_address; ?></a> ( <a href="#reset-google-password-box" id="reset-google-password" class="action">reset my <strong><?php echo $GOOGLE_APPS["domain"]; ?></strong> password</a> | <a href="http://webmail.<?php echo $GOOGLE_APPS["domain"]; ?>" class="action" target="_blank">visit <?php echo html_encode($GOOGLE_APPS["domain"]); ?> webmail</a> )
										<?php
									}
									?>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
						<?php
					}
					?>
					<tr>
						<td><label for="telephone"><strong>Telephone Number:</strong></label></td>
						<td>
							<input type="text" id="telephone" name="telephone" value="<?php echo html_encode($result["telephone"]); ?>" style="width: 250px; vertical-align: middle" maxlength="25" />
							<span class="content-small">(<strong>Example:</strong> 613-533-6000 x74918)</span>
						</td>
					</tr>
					<tr>
						<td><label for="fax"><strong>Fax Number:</strong></label></td>
						<td>
							<input type="text" id="fax" name="fax" value="<?php echo html_encode($result["fax"]); ?>" style="width: 250px; vertical-align: middle" maxlength="25" />
							<span class="content-small">(<strong>Example:</strong> 613-533-3204)</span>
						</td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>

					<tr>
						<td><label for="country_id" class="form-required"><strong>Country</strong></label></td>
						<td>
							<?php
							$countries = fetch_countries();
							if ((is_array($countries)) && (count($countries))) {

								$country_id = ($PROCESSED["country_id"])?$PROCESSED["country_id"]:$result["country_id"];

								echo "<select id=\"country_id\" name=\"country_id\" style=\"width: 256px\" onchange=\"provStateFunction(this.value);\">\n";
								echo "<option value=\"0\"".((!country_id) ? " selected=\"selected\"" : "").">-- Select Country --</option>\n";
								foreach ($countries as $country) {
									echo "<option value=\"".(int) $country["countries_id"]."\"".(($country_id == $country["countries_id"]) ? " selected=\"selected\"" : "").">".html_encode($country["country"])."</option>\n";
								}
								echo "</select>\n";
							} else {
								echo "<input type=\"hidden\" id=\"country_id\" name=\"country_id\" value=\"0\" />\n";
								echo "Country information not currently available.\n";
							}
							?>
						</td>
					</tr>
					<tr>
						<td><label id="prov_state_label" for="prov_state_div" class="form-nrequired"><strong>Province / State</strong></label></td>
						<td>
							<div id="prov_state_div">Please select a <strong>Country</strong> from above first.</div>
						</td>
					</tr>
					<tr>
						<td><label for="city"><strong>City:</strong></label></td>
						<td>
							<input type="text" id="city" name="city" value="<?php echo html_encode($result["city"]); ?>" style="width: 250px; vertical-align: middle" maxlength="35" />
						</td>
					</tr>
					<tr>
						<td><label for="address"><strong>Address:</strong></label></td>
						<td>
							<input type="text" id="address" name="address" value="<?php echo html_encode($result["address"]); ?>" style="width: 250px; vertical-align: middle" maxlength="255" />
						</td>
					</tr>
					<tr>
						<td><label for="postcode"><strong>Postal Code:</strong></label></td>
						<td>
							<input type="text" id="postcode" name="postcode" value="<?php echo html_encode($result["postcode"]); ?>" style="width: 250px; vertical-align: middle" maxlength="35" />
							<span class="content-small">(<strong>Example:</strong> K7L 3N6)</span>
						</td>
					</tr>
					<?php
					if ($_SESSION["details"]["group"] != "student") {
						$ONLOAD[] = "setMaxLength()";
						?>
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
						<tr>
							<td style="vertical-align: top"><label for="hours"><strong>Office Hours:</strong></label></td>
							<td>
								<textarea id="office_hours" name="office_hours" style="width: 254px; height: 40px;" maxlength="100"><?php echo html_encode($result["office_hours"]); ?></textarea>
							</td>
						</tr>
						<?php
					}
					?>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2"><h2 style="margin-top: 0px;">Profile Photo</h2></td>
					</tr>
					<tr>
						<td style="vertical-align: top; text-align: center">
							<table>
								<tr>
									<td>
										<div style="position: relative; width: 74px; height: 103px;">
											<img src="<?php echo webservice_url("photo", array($_SESSION["details"]["id"], "official"))."/".time(); ?>" width="72" height="100" class="cursor" id="profile_pic_<?php echo $result["id"] ?>" name="profile_pic" style="border: 1px #666666 solid; position: relative;"/>
										</div>
									</td>
									<td>
										<div style="position: relative; width: 74px; height: 103px;">
											<img src="<?php echo webservice_url("photo", array($_SESSION["details"]["id"], "upload"))."/".time(); ?>" width="72" height="100" class="cursor" id="alt_profile_pic_<?php echo $result["id"]; ?>" name="profile_pic" style="border: 1px #666666 solid; position: relative;"/>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<span class="content-small">Official</span>
									</td>
									<td>
										<span class="content-small">Uploaded</span>
									</td>
								</tr>
							</table>
						</td>
						<td style="vertical-align: top">
							<label for="photo_file" class="form-nrequired" style="margin-right: 5px">Upload New Photo:</label>
							<input type="file" id="photo_file" name="photo_file" />

							<div class="content-small" style="margin-top: 10px; width: 435px">
								<strong>Notice:</strong> You may upload JPEG, GIF or PNG images under <?php echo readable_size($VALID_MAX_FILESIZE); ?> only and any image larger than <?php echo $VALID_MAX_DIMENSIONS["photo-width"]."px by ".$VALID_MAX_DIMENSIONS["photo-height"]; ?>px (width by height) will be automatically resized.
							</div>

							<?php
							$query = "SELECT * FROM `".AUTH_DATABASE."`.`user_photos` WHERE `proxy_id` = ".$db->qstr($result["id"])." AND `photo_active` = '1'";
							$uploaded_photo = $db->GetRow($query);
							if ($uploaded_photo) {
								?>
								<div style="margin-top: 20px">
									<input type="checkbox" id="deactivate_photo" name="deactivate_photo" value="1" style="vertical-align: middle" />
									<label for="deactivate_photo" class="form-nrequired" style="vertical-align: middle">Deactivate your uploaded photo.</label>
								</div>
								<?php
							}
							?>
						</td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
				</tbody>
			</table>
		</form>
		
		<?php
		if (((bool) $GOOGLE_APPS["active"]) && $result["google_id"] && !in_array($result["google_id"], array("opt-out", "opt-in"))) {
			?>
			<div id="reset-google-password-box" class="modal-confirmation">
				<h1>Reset <strong><?php echo ucwords($GOOGLE_APPS["domain"]); ?></strong> Password</h1>
				<div id="reset-google-password-form">
					<div id="reset-google-password-form-status">To reset your <?php echo ucwords($GOOGLE_APPS["domain"]); ?> account password at Google, please enter your new password below and click the <strong>Submit</strong> button.</div>
					<form action="#" method="post">
						<table style="width: 100%; margin-top: 15px" cellspacing="2" cellpadding="0">
							<colgroup>
								<col style="width: 35%" />
								<col style="width: 65%" />
							</colgroup>
							<tbody>
								<tr>
									<td><label for="google_password_1" class="form-required">New Password</label></td>
									<td><input type="password" id="google_password_1" name="password1" value="" style="width: 175px" maxlength="24" /></td>
								</tr>
								<tr>
									<td><label for="google_password_2" class="form-required">Re-Enter Password</label></td>
									<td><input type="password" id="google_password_2" name="password2" value="" style="width: 175px" maxlength="24" /></td>
								</tr>
							</tbody>
						</table>
					</form>
				</div>
				<div id="reset-google-password-waiting" class="display-generic" style="display: none">
					<img src="<?php echo ENTRADA_RELATIVE; ?>/images/indicator.gif" width="16" height="16" alt="Please wait" border="0" style="margin-right: 2px; vertical-align: middle" /> <span class="content-small">Please wait while your password is being changed ...</span>
				</div>
				<div id="reset-google-password-success" class="display-success" style="display: none">
					We have successfully reset your <?php echo $GOOGLE_APPS["domain"]; ?> account password at Google.<br /><br />If you would like to log into your webmail account, please do so via <a href="http://webmail.qmed.ca" target="_blank">http://webmail.qmed.ca</a>.
				</div>
				<div class="footer">
					<button id="reset-google-password-close" style="float: left; margin: 8px 0px 4px 10px">Close</button>
					<button id="reset-google-password-submit" style="float: right; margin: 8px 10px 4px 0px">Submit</button>
				</div>
			</div>
			<script type="text/javascript" defer="defer">
			Event.observe(window, 'load', function() {
				new Control.Modal('reset-google-password', {
					overlayOpacity:	0.75,
					closeOnClick: 'overlay',
					className: 'modal-confirmation',
					fade: true,
					fadeDuration: 0.30
				});
			});
			$('reset-google-password-close').observe('click', function() {
				$('reset-google-password-submit', 'reset-google-password-form').invoke('show');
				$('reset-google-password-success', 'reset-google-password-waiting').invoke('hide');
	
				$('google_password_1').setValue('');
				$('google_password_2').setValue('');
				Control.Modal.close();
			});
	
			$('reset-google-password-submit').observe('click', function() {
				$('reset-google-password-submit', 'reset-google-password-form').invoke('hide');
				$('reset-google-password-waiting').show();
	
				if ($('google_password_1') && $('google_password_2')) {
					var new_password = $F('google_password_1');
					var test_password = $F('google_password_2');
	
					if (new_password && test_password) {
						if (new_password == test_password) {
							new Ajax.Request('<?php echo ENTRADA_URL; ?>/profile', {
								method: 'post',
								parameters: {
									'action' : 'google-password-reset',
									'password' : new_password,
									'ajax' : 1
								},
								onSuccess: function(response) {
									$('reset-google-password-form-status').update('');
									$('reset-google-password-waiting').hide();
									$('reset-google-password-success').show();
								},
								onFailure: function(response) {
									$('reset-google-password-form-status').update('<div class="display-error">We were unable to reset your password at this time, please try again later. If this error persists please contact the system administrator and inform them of the error.</div>');
									$('reset-google-password-waiting').hide();
									$('reset-google-password-submit', 'reset-google-password-form').invoke('show');
								}
							});
						} else {
							$('reset-google-password-form-status').update('<div class="display-error">Your passwords did not match, please try again.</div>');
							$('reset-google-password-waiting').hide();
							$('reset-google-password-submit', 'reset-google-password-form').invoke('show');
						}
					} else {
						$('reset-google-password-form-status').update('<div class="display-error" style="margin: 0">Please make sure you enter your new password, then re-enter it again in the space provided.</div>');
						$('reset-google-password-waiting').hide();
						$('reset-google-password-submit', 'reset-google-password-form').invoke('show');
					}
				} else {
					$('reset-google-password-form-status').update('<div class="display-error" style="margin: 0">Please make sure you enter your new password, then re-enter it again in the space provided.</div>');
					$('reset-google-password-waiting').hide();
					$('reset-google-password-submit', 'reset-google-password-form').invoke('show');
				}
			});
			</script>		
			<?php
		}
	} else {
		$NOTICE++;
		$NOTICESTR[]	= "Unfortunately your ".APPLICATION_NAME." profile is not accessible at this time, please try again later.";

		echo display_notice();

		application_log("error", "A user profile was not available in the database? Database said: ".$db->ErrorMsg());
	}
}