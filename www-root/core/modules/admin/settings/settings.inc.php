<?php

$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/settings", "title" => "Manage Entrada Settings");

?>
<h1>Manage Entrada Settings</h1>

<table>
	<tr>
		<td colspan="2"><h2>Database Settings</h2></td>
	</tr>
	<tr>
		<td>Database Host: </td><td><input type="text" name ="db_host" value ="<?php echo DATABASE_HOST;?>"/></td>
	</tr>
	<tr>
		<td>Database User: </td><td><input type="password" name ="db_user" value ="<?php echo DATABASE_USER;?>"/></td>
	</tr>
	<tr>
		<td>Database Password: </td><td><input type="text" name ="db_pass" value ="<?php echo DATABASE_PASS;?>"/></td>
	</tr>
	<tr>
		<td>Entrada Database: </td><td><input type="text" name ="db_entrada" value ="<?php echo DATABASE_NAME;?>"/></td>
	</tr>
	<tr>
		<td>Auth Database: </td><td><input type="text" name ="db_auth" value ="<?php echo AUTH_DATABASE;?>"/></td>
	</tr>
	<tr>
		<td>Clerkship Database: </td><td><input type="text" name ="db_clerkship" value ="<?php echo CLERKSHIP_DATABASE;?>"/></td>
	</tr>	
	<tr>
		<td colspan ="2"><input type="button" value ="Update Settings" style="float:right;"/></td>
	</tr>
	<tr>
		<td colspan="2"><h2>Path Settings</h2></td>
	</tr>
	<tr>
		<td>Absolute Path: </td><td><input type="text" name ="db_host" value ="<?php echo ENTRADA_ABSOLUTE;?>" style="width:350px;"/></td>
	</tr>
	<tr>
		<td>Relative Path: </td><td><input type="text" name ="db_user" value ="<?php echo ENTRADA_RELATIVE;?>" style="width:350px;"/></td>
	</tr>
	<tr>
		<td>URL: </td><td><input type="text" name ="db_pass" value ="<?php echo ENTRADA_URL;?>" style="width:350px;"/></td>
	</tr>
	<tr>
		<td colspan ="2"><input type="button" value ="Update Settings" style="float:right;"/></td>
	</tr>
</table>