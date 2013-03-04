<?php
ob_start();
function getmicrotime() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

if($_POST) {
//	header ("Content-type: text/xml");
	require_once(dirname(__FILE__)."/classes/authentication.class.php");
	$auth = new AuthSystem();
	$auth->setAppAuthentication($_POST["app_id"], $_POST["script_id"], $_POST["script_pass"]);
	$auth->setUserAuthentication($_POST["username"], $_POST["password"]);
	$result = $auth->Authenticate($_POST["requested_info"]);
	
	if($result["STATUS"] == "success") {
		$auth->updateLastLogin();
		$auth->updateData($_POST["update_fields"]);
	}
	echo "<pre>";
	print_r($result);
	echo "</pre>";

} else {
	?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
		<title>Testing Authentication Class</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</head>
	
	<body>
	<form action="index.php" method="post">
	Application ID: <input type="text" name="app_id" value="1" /><br />
	Script ID: <input type="text" name="script_id" value="000072638001" /><br />
	Script Pass: (md5) <input type="text" name="script_pass" value="MEdTech04" /><br />
	Username: <input type="text" name="username" value="simpson" /><br />
	Password: (md5) <input type="text" name="password" value="password" />
	<br /><br />
	<input type="checkbox" name="requested_info[]" value="id" /> - ID<br />
	<input type="checkbox" name="requested_info[]" value="number" /> - Queens Number<br />
	<input type="checkbox" name="requested_info[]" value="prefix" /> - Name Prefix<br />
	<input type="checkbox" name="requested_info[]" value="firstname" /> - Firstname<br />
	<input type="checkbox" name="requested_info[]" value="lastname" /> - Lastname<br />
	<input type="checkbox" name="requested_info[]" value="email" /> - E-mail address<br />
	<input type="checkbox" name="requested_info[]" value="email_alt" /> - Alternative E-mail address<br />
	<input type="checkbox" name="requested_info[]" value="telephone" /> - Telephone Number<br />
	<input type="checkbox" name="requested_info[]" value="fax" /> - Fax Number<br />
	<input type="checkbox" name="requested_info[]" value="address" /> - Address<br />
	<input type="checkbox" name="requested_info[]" value="city" /> - City<br />
	<input type="checkbox" name="requested_info[]" value="province" /> - Province<br />
	<input type="checkbox" name="requested_info[]" value="postcode" /> - Postal Code<br />
	<input type="checkbox" name="requested_info[]" value="country" /> - Country<br />
	<input type="checkbox" name="requested_info[]" value="access_starts" /> - Access Starts<br />
	<input type="checkbox" name="requested_info[]" value="access_expires" /> - Access Expires<br />
	<input type="checkbox" name="requested_info[]" value="last_login" /> - Last Login<br />
	<input type="checkbox" name="requested_info[]" value="last_ip" /> - Last IP Logged in with<br />
	<input type="checkbox" name="requested_info[]" value="role" /> - Role<br />
	<input type="checkbox" name="requested_info[]" value="group" /> - Group<br />
	<input type="checkbox" name="requested_info[]" value="private-space" /> - Private (space)<br />
	<input type="checkbox" name="requested_info[]" value="private-spat" /> - Private (spat)<br />
	<input type="checkbox" name="requested_info[]" value="private-apple" /> - Private (apple)<br />
	<input type="checkbox" name="requested_info[]" value="private-table" /> - Private (table)<br />
	<br />
	<br />
	<b>New Role:</b>&nbsp;
	<select name="update_fields[role]" />
	<option value="admin">Admin</option>
	<option value="lecturer">Lecturer</option>
	<option value="student">Student</option>
	</select>
	<br />
	<b>New Group:</b>&nbsp;
	<select name="update_fields[group]" />
	<option value="medtech">MEdTech</option>
	<option value="lecturer">Lecturer</option>
	<option value="student">Student</option>
	</select>

	
	<input type="submit" value="Submit" />
	</form>
	
	<br /><br />
	<?
	phpinfo();
	?>
	
	</body>
	</html>
	<?
}
ob_end_flush();
?>