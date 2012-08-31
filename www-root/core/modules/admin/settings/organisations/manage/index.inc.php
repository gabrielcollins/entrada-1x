

<div style="float: right">
	<ul class="page-action-edit">
		<li><a href="<?php echo ENTRADA_URL; ?>/admin/settings/organisations/manage?org=<?php echo$ORGANISATION_ID;?>&amp;section=edit" class="strong-green">Edit <?php echo $ORGANISATION["organisation_title"];?></a></li>
	</ul>
</div>
<?php

echo "<h1>".$ORGANISATION["organisation_title"]."</h1>";

?>


<table style="width: 100%" cellspacing="0" border="0" cellpadding="2" summary="View Organistion Form">
		<colgroup>
			<col style="width: 24%" />
			<col style="width: 76%" />
		</colgroup>
		<tbody>
			<tr>
				<td colspan="2"><h2>Organisation Information</h2></td>
			</tr>
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
				<td><label for="description_id">Description</label></td>
				<td>
					<?php echo $ORGANISATION["organisation_desc"];?>
				</td>
			</tr>
			<tr>
				<td><label for="template">Template</label></td>
				<td>
					<?php echo $ORGANISATION["template"];?>
				</td>
			</tr>

		</tbody>
</table>