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
 * @author Organisation: Queen's University
 * @author Unit: MEdTech Unit
 * @author Developer: Brandon Thorn <brandon.thorn@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_CONFIGURATION"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("configuration", "read", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	?>
	<div style="float: right">
		<ul class="page-action-edit">
			<li><a href="<?php echo ENTRADA_URL; ?>/admin/settings/organisations/manage?org=<?php echo$ORGANISATION_ID;?>&amp;section=edit" class="strong-green">Edit <?php echo $ORGANISATION["organisation_title"];?></a></li>
		</ul>
	</div>
	<?php
	echo "<h1>".$ORGANISATION["organisation_title"]."</h1>";

	if ($ORGANISATION["organisation_desc"]) {
		echo "<div class=\"event-description\">\n";
		echo $ORGANISATION["organisation_desc"];
		echo "</div>";
	}
	?>

	<h2>Organisation Details</h2>

	<table style="width: 100%" cellspacing="0" border="0" cellpadding="2" summary="View Organistion Form">
		<colgroup>
			<col style="width: 24%" />
			<col style="width: 76%" />
		</colgroup>
		<tbody>
			<tr>
				<td><label for="countries_id">Country</label></td>
				<td>
					<?php echo $ORGANISATION["organisation_country"];?>
				</td>
			</tr>
			<tr>
				<td><label for="province_id">Province</label></td>
				<td>
					<?php echo $ORGANISATION["organisation_province"];?>
				</td>
			</tr>
			<tr>
				<td><label for="city_id">City</label></td>
				<td>
					<?php echo $ORGANISATION["organisation_city"];?>
				</td>
			</tr>
			<tr>
				<td><label for="postal_id">Postal Code</label></td>
				<td>
					<?php echo $ORGANISATION["organisation_postcode"];?>
				</td>
			</tr>
			<tr>
				<td><label for="address1_id">Address 1</label></td>
				<td>
					<?php echo $ORGANISATION["organisation_address1"];?>
				</td>
			</tr>
			<tr>
				<td><label for="address2_id">Address 2</label></td>
				<td>
					<?php echo $ORGANISATION["organisation_address2"];?>
				</td>
			</tr>
			<tr>
				<td><label for="telephone_id">Telephone</label></td>
				<td>
					<?php echo $ORGANISATION["organisation_telephone"];?>
				</td>
			</tr>
			<tr>
				<td><label for="fax_id">Fax</label></td>
				<td>
					<?php echo $ORGANISATION["organisation_fax"];?>
				</td>
			</tr>
			<tr>
				<td><label for="email_id">E-Mail Address</label></td>
				<td>
					<?php echo $ORGANISATION["organisation_email"];?>
				</td>
			</tr>
			<tr>
				<td><label for="url_id">Website</label></td>
				<td>
					<?php echo "<a href=\"".$ORGANISATION["organisation_url"]."\">".$ORGANISATION["organisation_url"]."</a>";?>
				</td>
			</tr>

			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>

			<tr>
				<td><label for="template">Interface Template</label></td>
				<td>
					<?php echo $ORGANISATION["template"];?>
				</td>
			</tr>

			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>

			<?php if (isset($ORGANISATION["aamc_institution_id"]) && $ORGANISATION["aamc_institution_id"]) : ?>

			<tr>
				<td>AAMC Institution ID</td>
				<td>
					<?php echo $ORGANISATION["aamc_institution_id"]; ?>
				</td>
			</tr>
			<tr>
				<td>AAMC Institution Name</td>
				<td>
					<?php echo $ORGANISATION["aamc_institution_name"]; ?>
				</td>
			</tr>

			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>

			<tr>
				<td>AAMC Program ID</td>
				<td>
					<?php echo $ORGANISATION["aamc_program_id"]; ?>
				</td>
			</tr>
			<tr>
				<td>AAMC Program Name</td>
				<td>
					<?php echo $ORGANISATION["aamc_program_name"]; ?>
				</td>
			</tr>

			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<?php endif; ?>
		</tbody>
	</table>
	<?php
}