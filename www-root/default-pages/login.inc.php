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
<h1><?php echo APPLICATION_NAME; ?> Login</h1>
Please enter your <?php echo APPLICATION_NAME; ?> username and password to log in.
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