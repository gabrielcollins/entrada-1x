<?php
/**
 * Online Course Resources [Pre-Clerkship]
 * @author Unit: Medical Education Technology Unit
 * @author Director: Dr. Benjamin Chen <bhc@post.queensu.ca>
 * @author Developer: Matt Simpson <simpson@post.queensu.ca>
 * @version 3.0
 * @copyright Copyright 2006 Queen's University, MEdTech Unit
 *
 * $Id: login.inc.php 1078 2010-03-26 17:09:35Z simpson $
*/

if(!defined("PARENT_INCLUDED")) exit;

/**
 * Focus on the username textbox when this module is loaded.
 */
$ONLOAD[] = "document.getElementById('username').focus()";
?>
<div id="login_section">
	<h1><?php echo APPLICATION_NAME; ?> Login</h1>
	Please enter your <?php echo APPLICATION_NAME; ?> username and password to log in.
	<?php if (defined('ALLOW_REGISTRATION') && ALLOW_REGISTRATION) { ?>
	<br/>Don't have a <?php echo APPLICATION_NAME;?> account? <a href="javascript:void(0)" id="register_link">Click Here</a> to register.
	<?php } ?>
	<blockquote>
		<?php
		if(($ACTION == "login") && ($ERROR)) {
			echo display_error();
		}

		/**
		 * If the user is trying to access a link and is not logged in, display a
		 * notice to inform the user that they need to log in first.
		 */
		if(($PROCEED_TO) && (stristr($PROCEED_TO, "link-course.php") || stristr($PROCEED_TO, "link-event.php"))) {
			echo display_notice(array("You must log in to access this link; once you have logged in you will be automatically redirected to the requested location."));
		}

		/**
		 * If the user is trying to access a file and is not logged in, display a
		 * notice to inform the user that they need to log in first.
		 */
		if(($PROCEED_TO) && (stristr($PROCEED_TO, "file-course.php") || stristr($PROCEED_TO, "file-event.php"))) {
			$ONLOAD[] = "setTimeout('window.location = \\'".ENTRADA_URL."\\'', 15000)";
			echo display_notice(array("You must log in to download the requested file; once you have logged in the download will start automatically."));
		}
		?>
		<form action="<?php echo ENTRADA_URL; ?>/<?php echo (($PROCEED_TO) ? "?url=".rawurlencode($PROCEED_TO) : ""); ?>" method="post">
			<input type="hidden" name="action" value="login" />
			<table style="width: 275px" cellspacing="1" cellpadding="1" border="0">
				<colgroup>
					<col style="width: 30%" />
					<col style="width: 70%" />
				</colgroup>
				<tfoot>
					<tr>
						<td colspan="2" style="text-align: right"><input type="submit" class="button" value="Login" /></td>
					</tr>
					<tr>
						<td colspan="2" style="padding-top: 15px">
							<?php if ((defined("PASSWORD_RESET_URL")) && (PASSWORD_RESET_URL != "")) : ?>
							<a href="<?php echo PASSWORD_RESET_URL; ?>" style="font-size: 10px">Forgot your password?</a> <span class="content-small">|</span>
							<?php endif; ?>
							<?php if (defined('ALLOW_REGISTRATION') && ALLOW_REGISTRATION && defined('REGISTRATION_CONFIRMATION_URL') && REGISTRATION_CONFIRMATION_URL) { 
								//link to resend email: need login page to be styled different to avoid wordwrap?>
							
							<?php } ?>
							<a href="<?php echo ENTRADA_URL; ?>/help" style="font-size: 10px">Need Help?</a>
						</td>
					</tr>
				</tfoot>
				<tbody>
					<tr>
						<td><label for="username" style="font-weight: bold">Username:</label></td>
						<td style="text-align: right"><input type="text" id="username" name="username" value="<?php echo ((isset($_REQUEST["username"])) ? html_encode(trim($_REQUEST["username"])) : ""); ?>" style="width: 150px" /></td>
					</tr>
					<tr>
						<td><label for="password" style="font-weight: bold">Password:</label></td>
						<td style="text-align: right"><input type="password" id="password" name="password" value="" style="width: 150px" /></td>
					</tr>
				</tbody>
			</table>
		</form>
	</blockquote>
</div>
<?php if (defined('ALLOW_REGISTRATION') && ALLOW_REGISTRATION) { 
$ONLOAD[] = "provStateFunction()";	
	?>
<div id="register_section">
	<h1><?php echo APPLICATION_NAME; ?> Registration</h1>
	Fill out the following form to create an account on <?php echo APPLICATION_NAME; ?>. Or <a href="javascript:void(0)" id="login_link">click here</a> to return to the login form.	
	<blockquote>
		<?php if ($ERROR) { 
				echo display_error();
			  } ?>
		<form action="<?php echo ENTRADA_URL; ?>/<?php echo (($PROCEED_TO) ? "?url=".rawurlencode($PROCEED_TO) : ""); ?>" method="post">
			<input type="hidden" name="action" value="register" />
			<table style="width: 275px" cellspacing="1" cellpadding="1" border="0">
				<colgroup>
					<col style="width: 30%" />
					<col style="width: 70%" />
				</colgroup>
				<tfoot>
					<tr>
						<td colspan="2" style="text-align: right"><input type="submit" class="button" value="Register" /></td>
					</tr>
					<tr>
						<td colspan="2" style="padding-top: 15px">
							<?php if ((defined("PASSWORD_RESET_URL")) && (PASSWORD_RESET_URL != "")) : ?>
							<a href="<?php echo PASSWORD_RESET_URL; ?>" style="font-size: 10px">Forgot your password?</a> <span class="content-small">|</span>
							<?php endif; ?>
							<a href="<?php echo ENTRADA_URL; ?>/help" style="font-size: 10px">Need Help?</a>
						</td>
					</tr>
				</tfoot>
				<tbody>
					<tr>
						<td><label for="firstname" style="font-weight: bold">First Name:</label></td>
						<td style="text-align: right"><input type="text" id="firstname" name="firstname" value="<?php echo ((isset($_REQUEST["username"])) ? html_encode(trim($_REQUEST["firstname"])) : ""); ?>" style="width: 150px" /></td>
					</tr>
					<tr>
						<td><label for="lastname" style="font-weight: bold">Last Name:</label></td>
						<td style="text-align: right"><input type="text" id="lastname" name="lastname" value="<?php echo ((isset($_REQUEST["username"])) ? html_encode(trim($_REQUEST["lastname"])) : ""); ?>" style="width: 150px" /></td>
					</tr>
					<tr class="no-show">
						<td><label for="lastname" style="font-weight: bold">Age:</label></td>
						<td style="text-align: right"><input type="text" id="age" name="age" value="<?php echo ((isset($_REQUEST["age"])) ? html_encode(trim($_REQUEST["age"])) : ""); ?>" style="width: 150px" tabindex="-1"/></td>
					</tr>
					<tr>
						<td><label for="email" style="font-weight: bold">Email:</label></td>
						<td style="text-align: right"><input type="text" id="email" name="email" value="<?php echo ((isset($_REQUEST["email"])) ? html_encode(trim($_REQUEST["email"])) : ""); ?>" style="width: 150px" /></td>
					</tr>
					<tr>
						<td><label for="country" style="font-weight: bold">Country:</label></td>
						<td style="text-align: right">
							<?php
								$countries = fetch_countries();
								if ((is_array($countries)) && (count($countries))) {
									echo "<select id=\"country_id\" name=\"country_id\" style=\"width: 156px\" onchange=\"provStateFunction();\">\n";
									echo "<option value=\"0\">-- Select Country --</option>\n";
									foreach ($countries as $country) {
										echo "<option value=\"".(int) $country["countries_id"]."\"".(((!isset($PROCESSED["country_id"]) && ($country["countries_id"] == DEFAULT_COUNTRY_ID)) || ($PROCESSED["country_id"] == $country["countries_id"])) ? " selected=\"selected\"" : "").">".html_encode($country["country"])."</option>\n";
									}
									echo "</select>\n";
								} else {
									echo "<input type=\"hidden\" id=\"countries_id\" name=\"countries_id\" value=\"0\" />\n";
									echo "Country information not currently available.\n";
								}
								?>							
						</td>
					</tr>
					<tr>
						<td><label for="prov_state_div" style="font-weight: bold">Province/State:</label></td>
						<td style="text-align: right">
							<div id="prov_state_div">Please select a <strong>Country</strong> from above first.</div>
						</td>
					</tr>
					<tr>
						<td><label for="username" style="font-weight: bold">Username:</label></td>
						<td style="text-align: right"><input type="text" id="register_username" name="username" value="<?php echo ((isset($_REQUEST["username"])) ? html_encode(trim($_REQUEST["username"])) : ""); ?>" style="width: 150px" /></td>
					</tr>
					<tr>
						<td><label for="password" style="font-weight: bold">Password:</label></td>
						<td style="text-align: right"><input type="password" id="register_password" name="password" value="" style="width: 150px" /></td>
					</tr>
					<tr>
						<td><label for="password" style="font-weight: bold">Confirm Password:</label></td>
						<td style="text-align: right"><input type="password" id="register_password_confirm" name="confirm" value="" style="width: 150px" /></td>
					</tr>
					<tr>
						<td><label for="organisations" style="font-weight: bold">Organisations:</label></td>
						<td>
							<?php
								$query = "	SELECT * FROM `".AUTH_DATABASE."`.`organisations`
											WHERE `organisation_active` = '1'";
								$organisations = $db->GetAll($query);
								if (false &&$organisations) {
									foreach($organisations as $organisation){
										echo '<div style="margin-left:15px;">';
										echo '<input type="checkbox" name="organisations[]" id="org_'.$organisation["organisation_id"].'" value = "'.$organisation["organisation_id"].'"/>';
										echo '<label for="org_'.$organisation["organisation_id"].'">'.$organisation["organisation_title"].'</label><br/>';
										echo '</div>';
									}
								} else {
									echo display_notice("No organisations available to register with.");
								}
								?>							
						</td>
					</tr>					
				</tbody>
			</table>
		</form>
	</blockquote>
</div>
<script type="text/javascript">	
	jQuery('#register_username').focusout(function(){
		findExistingUser('username',jQuery(this).val());		
	});

	var glob_type = null;
	function findExistingUser(type, value) {
		if (type && value) {
			var url = '<?php echo ENTRADA_RELATIVE; ?>/admin/<?php echo $MODULE; ?>?section=search&' + type + '=' + value;
			if (type == 'id') {
				type = 'number';
			}

			if ($(type + '-default')) {
				$(type + '-default').hide();
			}

			if ($(type + '-searching')) {
				$(type + '-searching').show();
			}

			glob_type = type;

			new Ajax.Request(url, {method: 'get', onComplete: getResponse});
		}
	}

	function getResponse(request) {
		if ($(glob_type + '-default')) {
			$(glob_type + '-default').show();
		}

		if ($(glob_type + '-searching')) {
			$(glob_type + '-searching').hide();
		}

		var data = request.responseJSON;

		if (data) {
			$('username').disable().setValue(data.username);
			$('firstname').disable().setValue(data.firstname);
			$('lastname').disable().setValue(data.lastname);
			$('email').disable().setValue(data.email);
			$('number').disable().setValue(data.number);
			$('password').disable().setValue('********');
			$('prefix').disable().setValue(data.prefix);
			$('email_alt').disable().setValue(data.email_alt);
			$('telephone').disable().setValue(data.telephone);
			$('fax').disable().setValue(data.fax);
			$('address').disable().setValue(data.address);
			$('city').disable().setValue(data.city);

			if ($('country')) {
				$('country').disable().setValue(data.country);
			} else if($('country_id')) {
				$('country_id').disable().setValue(data.country_id);

				provStateFunction(data.country_id, data.province_id);
			}

			$('postcode').disable().setValue(data.postcode);
			$('notes').disable().setValue(data.notes);

			$('send_notification_msg').hide();
			$('send_notification').checked = false;

			var notice = document.createElement('div');
			notice.id = 'display-notice';
			notice.addClassName('display-notice');
			notice.innerHTML = data.message;

			$('addUser').insert({'before' : notice});
		}
	}	
	function provStateFunction(country_id, province_id) {
		var url_country_id = '<?php echo ((!isset($PROCESSED["country_id"]) && defined("DEFAULT_COUNTRY_ID") && DEFAULT_COUNTRY_ID) ? DEFAULT_COUNTRY_ID : 0); ?>';
		var url_province_id = '<?php echo ((!isset($PROCESSED["province_id"]) && defined("DEFAULT_PROVINCE_ID") && DEFAULT_PROVINCE_ID) ? DEFAULT_PROVINCE_ID : 0); ?>';

		if (country_id != undefined) {
			url_country_id = country_id;
		} else if ($('country_id')) {
			url_country_id = $('country_id').getValue();
		}

		if (province_id != undefined) {
			url_province_id = province_id;
		} else if ($('province_id')) {
			url_province_id = $('province_id').getValue();
		}

		var url = '<?php echo webservice_url("province"); ?>?countries_id=' + url_country_id + '&prov_state=' + url_province_id;

		new Ajax.Updater($('prov_state_div'), url, {
			method:'get',
			onComplete: function (init_run) {
				if ($('prov_state').type == 'select-one') {
					$('prov_state_label').removeClassName('form-nrequired');
					$('prov_state_label').addClassName('form-required');
					if (!init_run) {
						$("prov_state").selectedIndex = 0;
					}
				} else {
					$('prov_state_label').removeClassName('form-required');
					$('prov_state_label').addClassName('form-nrequired');
					if (!init_run) {
						$("prov_state").clear();
					}
				}
				jQuery("#prov_state").attr("style","width:156px;");
			}
		});
	}	
	jQuery(document).ready(function(){
		jQuery('#register_section').hide();
		<?php if(isset($PROCESSED["register"]) && $PROCESSED["register"]){ ?>
			jQuery('#register_section').slideDown();
			jQuery('#login_section').slideUp();
		<?php } ?>
		jQuery('#register_link').click(function(){
			jQuery('#register_section').slideDown();
			jQuery('#login_section').slideUp();
		});
		jQuery('#login_link').click(function(){
			jQuery('#login_section').slideDown();
			jQuery('#register_section').slideUp();
		});
	});
</script>
<?php } ?>