<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Displayed the event calendar index, which will hopefully be a real calendar
 * sooner rather than later.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Don Zuiker <don.zuiker@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 *
 */
if ((!defined("COMMUNITY_INCLUDED")) || (!defined("IN_MTDTRACKING"))) {
	header("Location: " . COMMUNITY_URL);
	exit;
} elseif (!$COMMUNITY_LOAD) {
	exit;
} else {
	$community_title = $community_details["community_title"];

	//Get the MOH Service Name for this Queen's Program
	$query = "	SELECT *
				FROM  `mtd_pgme_moh_programs`
				WHERE `pgme_program_name` like " . $db->qstr(trim($community_title) . "%");
	$result = $db->GetRow($query);

	// Get the service code
	$query = "	SELECT *
					FROM  `mtd_moh_service_codes`
					WHERE `service_description` = " . $db->qstr($result["moh_program_description"]);

	$result = $db->GetRow($query);

	$mtd_service_id = 0;
	$mtd_service_code = "";
	$mtd_service_description = "";

	$mtd_service_id_arr = array();
	$mtd_service_code_arr = array();
	$mtd_service_description_arr = array();

	//add the other services that Internal Medicine does tracking for
	if ($result["service_description"] == "Internal Medicine") {
		array_push($mtd_service_id_arr, $result["id"], "1", "10", "22");
		array_push($mtd_service_code_arr, $result["service_code"], "ALL", "END", "ID");
		array_push($mtd_service_description_arr, $result["service_description"], "Allergy", "Endocrinology", "Infectious Diseases");
	} else {
		$mtd_service_id = $result["id"];
		$mtd_service_code = $result["service_code"];
		$mtd_service_description = $result["service_description"];
	}

	$query = "	SELECT *
				FROM `mtd_facilities`
				WHERE `kingston` = 1
				ORDER BY `facility_name` ASC";
	$mtd_locations_kingston = $db->GetAll($query);

	$query = "	SELECT *
				FROM `mtd_facilities`
				WHERE `kingston` = 0
				ORDER BY `facility_name` ASC";
	$mtd_locations_other = $db->GetAll($query);

	$query = "	SELECT *
				FROM `mtd_moh_program_codes`
				ORDER BY `program_description` ASC";
	$programs = $db->GetAll($query);

	$query = "	SELECT *
				FROM `mtd_schools`";
	$mtd_schools = $db->GetAll($query);

	$query = "	SELECT *
				FROM `mtd_categories`";
	$mtd_categories = $db->GetAll($query);

?>

	<script type="text/javascript" defer="defer">
		jQuery(document).ready(function() {
			var service_id = 0;
			if (jQuery("#mtdservice_select").val()) {
				service_id = jQuery("#mtdservice_select").val();
			}
			else {
				service_id = jQuery("#service_id").val();
			}
			schedule = jQuery("#mtd_schedule").flexigrid({
				url: '<?php echo COMMUNITY_URL . $COMMUNITY_URL . ":" . $PAGE_URL . "?section=api-mtd-load-schedule&service_id="?>' + service_id,
				dataType: 'json',
				method: 'POST',
				colModel : [
					{display: 'Location', name : 'facility_name', width : 200, sortable : true, align: 'left'},
					{display: 'Last', name : 'last_name', width : 100, sortable : true, align: 'left'},
					{display: 'First', name : 'first_name', width : 100, sortable : true, align: 'left'},
					{display: 'Start', name : 'start_date', width : 100, sortable : true, align: 'left'},
					{display: 'End', name : 'end_date', width : 100,  sortable : true, align: 'left'},
					{display: '%', name : 'percent_time', width : 25,  sortable : true, align: 'left'},
					{display: 'Type', name : 'type_description', width : 125,  sortable : true, align: 'left'}
				],
				searchitems : [
					{display: 'Location', name : 'facility_name'},
					{display: 'Last', name : 'last_name', isdefault: true},
					{display: 'First', name : 'first_name'}
				],
				sortname: "start_date",
				sortorder: "desc",
				resizable: false,
				usepager: true,
				collapseTable: false,
				showToggleBtn: false,
				title: 'Medical Training Days',
				useRp: true,
				rp: 20,
				showTableToggleBtn: true,
				width: 750,
				height: 400,
				nomsg: 'No Results',
				buttons : [
					{name: 'Delete Selected', bclass: 'delete', onpress : deleteRecord}
				]
			});

			jQuery("#add_MTD_form").submit(function(e) {
				//Cancel the default submit behaviour
				e.preventDefault();
				var form = jQuery(this);

				var url = form.attr( 'action' );
				// Send the data using post and put the results in a div
				jQuery.post( url, form.serialize() ,
				function( data ) {
					var content = jQuery( data ).find( '#responseMsg' );
					jQuery("#submitResponse").html( content );

					jQuery("#submitResponse").fadeIn();
					jQuery("#submitResponse").delay(5000).fadeOut();
				});
				window.setTimeout('schedule.flexReload()', 1000);
			});

			jQuery.datepicker.setDefaults({
				showOn: 'button',
				buttonImageOnly: true,
				buttonImage: "<?php echo ENTRADA_URL . '/images/jquery-calendar.gif'; ?>",
				buttonText: 'Calendar',
				dateFormat: 'yy-mm-dd',
				numberOfMonths: 2,
				showButtonPanel: true,
				closeOnSelect:false
			});

			//Add datepickers
			var s_date = jQuery( "#start_date" ).datepicker({
				onSelect: function( selectedDate ) {
					instance = jQuery( this ).data( "datepicker" ),
					date = jQuery.datepicker.parseDate(
					instance.settings.dateFormat ||
						jQuery.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
					var start_date = new Date(date);
					var default_end_date = start_date.addWeeks(4);
					jQuery("#end_date").datepicker( 'setDate', default_end_date )
					jQuery("#end_date").datepicker( "option", "minDate", date );
				}
			})

			var e_date = jQuery( "#end_date" ).datepicker();

			jQuery('#block_list').change(function(e) {
				var block = jQuery(this).val();
				var year = jQuery('#year').val();
				var url = '<?php echo COMMUNITY_URL . $COMMUNITY_URL . ":" . $PAGE_URL; ?>?section=api-mtd-get-block&block='+block+'&year='+year;
				jQuery.get(url, function(data) {
					jQuery('#block_choice').html(data);
				});
			});

			jQuery('#year').change(function(e) {
				var service_id = 0;
				if (jQuery("#mtdservice_select").val()) {
					service_id = jQuery("#mtdservice_select").val();
				}
				else {
					service_id = jQuery("#service_id").val();
				}
				var year = jQuery(this).val();
				var newURL = '<?php echo COMMUNITY_URL . $COMMUNITY_URL . ":" . $PAGE_URL . "?section=api-mtd-load-schedule&service_id=" ?>' + service_id + "&year=" + year;
				window.setTimeout("schedule.flexOptions({url: '" + newURL + "'}).flexReload()", 1000);
				//Redisplay the block dates for the newly selected year
				var block = jQuery('#block_list').val();
				if (block != null || block != "") {
					var url = '<?php echo COMMUNITY_URL . $COMMUNITY_URL . ":" . $PAGE_URL; ?>?section=api-mtd-get-block&block='+block+'&year='+year;
					jQuery.get(url, function(data) {
						jQuery('#block_choice').html(data);
					});
				}
			});

			jQuery('#mtdservice_select').change(function(e) {
				var service_id = jQuery(this).val();
				var year = jQuery('#year').val();
				var newURL = '<?php echo COMMUNITY_URL . $COMMUNITY_URL . ":" . $PAGE_URL . "?section=api-mtd-load-schedule&year="?>' + year + "&service_id=" + service_id;
				window.setTimeout("schedule.flexOptions({url: '" + newURL + "'}).flexReload()", 1000);
			});

			jQuery('#download_button').click(function(e) {
				var service_id = 0;
				if (jQuery("#mtdservice_select").val()) {
					service_id = jQuery("#mtdservice_select").val();
				}
				else {
					service_id = jQuery("#service_id").val();
				}
				var year = jQuery('#year').val();
				var url = '<?php echo COMMUNITY_URL . $COMMUNITY_URL . ":" . $PAGE_URL . "?section=export_schedule&service_id=" ?>' + service_id + "&year=" + year;
				window.location = url;
			});

			//Default the location to KGH
			jQuery("#mtdlocation option[value='59']").attr('selected', 'selected');
			createLocationDuration();

		});

		function validateRequired(formArray) {
			for (field in formArray) {
				if (field == null || field == "")
				{
					alert("First name must be filled out");
					return false;
				}
			}
		}

		function deleteRecord(com,grid) {
			if (com=='Delete Selected') {
				jQuery(function() {
					if(jQuery('.trSelected',grid).length>0) {
						// a workaround for a flaw in the demo system (http://dev.jqueryui.com/ticket/4375), ignore!
						jQuery("#delete-confirm").dialog("destroy");

						jQuery("#delete-confirm").dialog({
							resizable: false,
							height:180,
							modal: true,
							buttons: {
								'Delete': function() {
									var ids = "";
									jQuery('.trSelected', grid).each(function() {
										var id = jQuery(this).attr('id');
										id = id.substring(id.lastIndexOf('row')+3);
										if(ids == "") {
											ids = id;
										} else {
											ids = id+"|"+ids;
										}
									});
									jQuery.ajax
									({
										type: "POST",
										dataType: "json",
										url: '<?php echo COMMUNITY_URL . $COMMUNITY_URL . ":" . $PAGE_URL; ?>?section=api-mtd-delete&rid='+ids
									});

									window.setTimeout('schedule.flexReload()', 1000);
									jQuery(this).dialog('close');
								},
								Cancel: function() {
									jQuery(this).dialog('close');
								}
							}
						});
					} else {
						jQuery('#delete-valid').dialog({
							title: "No Items Selected for Delete",
							resizable: false,
							height:180,
							modal: true,
							buttons: {
								Ok: function() {
									jQuery(this).dialog('close');
								}
							}
						})
					}
				});
			}
		}

		function getResident(id) {
			jQuery("#resident_proxy_id").val(id);
			var url = jQuery("#find_resident_url").val();
			// Send the data using post and put the results in a div
			jQuery.get( url, {resident_id: id},
			function( data ) {
				if (jQuery( data ).find( '#resident_not_found' ).html().toString() == "") {
					jQuery("#resident_not_found").html("");
					jQuery("#school_description").html(jQuery( data ).find( '#school_description' ).html().toString());
					jQuery("#full_name").html(jQuery( data ).find( '#full_name' ).html().toString());
					jQuery("#program_description").html(jQuery( data ).find( '#program_description' ).html());
					jQuery("#category_description").html(jQuery( data ).find( '#category_description' ).html());
					//Remove program name from resident name
					var resident_name = jQuery("#resident_name").val().toString();
					jQuery("#resident_name").val(resident_name.strip());
				}
			});
		}

		function clearForm() {
			//Clear the resident profile section
			jQuery("#school_description").html("");
			jQuery("#program_description").html("");
			jQuery("#category_description").html("");
			jQuery("#full_name").html("");
			jQuery("#block_choice").html("");
			//Clear the form fields
			jQuery("#resident_name").val("");
			jQuery("#resident_proxy_id").val(0);
			jQuery("#start_date").val("");
			jQuery("#end_date").val("");
			jQuery('#type_code_i').attr('checked', true);
			jQuery('#mtdlocation option[value=""]').attr('selected', 'selected');
			//remove the locations
			jQuery('.location_duration').remove();
			jQuery("#block_list").val("");
		}

		var DELETE_IMAGE_URL = "<?php echo ENTRADA_URL . "/images/action-delete.gif"; ?>";
	</script>

	<style type="text/css">
		.mtd-form{
			background-color: #FFFFF2;
			border:1px black solid;
			margin:15px 0;
			padding:5px;
			width: 60%;
			float: left;
		}
		.mtd-resident-profile{
			background-color: #FFFFF2;
			border:1px black solid;
			margin:15px 0;
			padding:5px;
			width: 35%;
			float: right;
		}
		#resident_not_found {
			color: red;
		}

		.flexigrid tr.trSelected td.sorted,
		.flexigrid tr.trSelected td {
			background: #fad42e;
			border-right: 1px solid #d2e3ec;
			border-left: 1px solid #eef8ff;
			border-bottom: 1px solid #a8d8eb;
		}

	</style>

	<div id="delete-valid" style="display:none"><p><span class="ui-icon ui-icon-circle-check" style="float:left; margin:0 7px 50px 0;"></span>You must select at least one record in order to delete.</p></div>
	<div id="delete-confirm" style="display:none"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>All selected rows will be deleted. Are you sure?</p></div>

	<div id ="MTD_form_container" class="mtd-form">
		<form id="add_MTD_form" action="<?php echo COMMUNITY_URL . $COMMUNITY_URL . ":" . $PAGE_URL; ?>?section=add" method="post">
			<label for="resident_name">Resident Name:</label><br />
			<input id="resident_name" name="resident_name" type="text" class="required" size="50"/><em>*</em>
			<input id="resident_proxy_id" name="resident_proxy_id" type="hidden" />
			<div class="autocomplete" id="resident_name_auto_complete"></div>
			<script type="text/javascript">
				new Ajax.Autocompleter('resident_name', 'resident_name_auto_complete', '<?php echo COMMUNITY_URL . $COMMUNITY_URL . ":" . $PAGE_URL; ?>?section=api-resident-search', {frequency: 0.2, minChars: 2, afterUpdateElement: function (text, li) {getResident(li.id);}});
			</script>

			<input id="find_resident_url" type="hidden" value="<?php echo COMMUNITY_URL . $COMMUNITY_URL . ":" . $PAGE_URL; ?>?section=find_resident" />
			<div id="year_selector">
					<br />
					<strong>Pick a year:</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<select id="year" name="year">
						<option value="2011-2012">2011-2012</option>
						<option value="2010-2011">2010-2011</option>
						<option value="2009-2010">2009-2010</option>
					</select>
			</div>
			<br />
			<hr />
	<div id="period_selectors">
			<div id="blocks" style="float:left;">
				<p><br />
					<strong>Pick a block:</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<select id="block_list" name="block_list">
						<option value=""></option>
					<?php
					for ($i = 1; $i <= 13; $i++) {
						echo "<option value=\"" . $i . "\">" . "Block " . $i . "</option>";
					}
					?>
				</select>
			</p>
			<div id="block_choice" style="float:left;">
			</div>
			</div>

		<div id="calendars" style="float:right">
			<br />
			<p><strong>OR Pick a start and end date:</strong></p>
			<p>
				<label for="start_date">Start Date:</label>	<input id="start_date" name="start_date" type="text" />
				<br />
				<br />
				<label for="end_date">End Date:</label>	<input id="end_date" name="end_date" type="text" />
			</p>
		</div>
	</div>
	
				<br /><br /><br /><br /><br /><br /><br /><br /><br />
				<hr />

		<div id="type_location_duration_service_button" style="float:left">
			<p>
				<label>Type:</label>&nbsp;
				<input id="type_code_i" name="type_code" type="radio" value="I" CHECKED/> <label for="type_code_i" style="font-weight: normal">in-patient/emergency</label>&nbsp;
				<input id="type_code_o" name="type_code" type="radio" value="O"/> <label for="type_code_o" style="font-weight: normal">out-patient</label>&nbsp;
				<input id="type_code_r" name="type_code" type="radio" value="R"/> <label for="type_code_r" style="font-weight: normal">research elective</label>
			</p>
			<p>
				<label for="mtdlocation">Location:</label><br />
				<select id="mtdlocation" name="mtdlocation">
					<option value="">Choose a Location</option>
					<?php
					echo "<optgroup label=\"Kingston\">";
					foreach ($mtd_locations_kingston as $mtd_location) {
						echo "<option value=\"" . $mtd_location["id"] . "\">" . $mtd_location["facility_name"] . "</option>";
					}
					echo "</optgroup>";
					echo "<optgroup label=\"Other\">";
					foreach ($mtd_locations_other as $mtd_location) {
						echo "<option value=\"" . $mtd_location["id"] . "\">" . $mtd_location["facility_name"] . "</option>";
					}
					echo "</optgroup>";
					?>
				</select>
			</p>

			<ol id="duration_container" class="sortableList" style="display: none"></ol>
			<div id="total_duration" class="content-small" style="margin-bottom: 10px">Total percent time: 0 %.</div>
			<input type="hidden" id="mtdlocation_duration_order" name="mtdlocation_duration_order" value="" />
			<br />
			<?php if (!empty($mtd_service_id_arr)) { 
					  $i = 0;//loop counter ?>
			<label for="mtdservice_select">Service:</label>
			<select id="mtdservice_select" name="mtdservice_select">
			<?php	foreach ($mtd_service_id_arr as $mtd_service_id_el) {
						echo "<option value=\"" . $mtd_service_id_el . "\">" . $mtd_service_description_arr[$i] . "</option>";
						$i++;
				} ?>
			</select>
			<?php } else {
				    echo "<label>Service Code: </label>" . $mtd_service_code . " (" . $mtd_service_description . ")"; ?>
					<input type="hidden" id ="service_id" name="service_id" value="<?php echo $mtd_service_id ?>" />
			<?php } ?>
					<p>
						<input id="add_submit" type="submit" value="Add" style="margin-right:20px"/><a href="#" id="clearForm" onclick="clearForm();return false;">Clear Form</a>
					</p>
				</div>
			</form>
		</div>

		<div id ="mtd_resident_container" class="mtd-resident-profile">
			<div id="resident_not_found"></div>
			<table id="resident_data">
				<colgroup>
					<col style="font-weight: bold;" />
					<col class="resident_data" />
				</colgroup>
				<thead><tr><td colspan="2" style="font-weight:bold; font-size: 14px; border-bottom: 2px solid #999999;">Resident Profile</td><td></td></tr></thead>
				<tbody>
					<tr><td style="font-weight:bold;">Name: </td></tr>
					<tr><td><span id="full_name" class="resident_data" /></td></tr>
					<tr><td style="font-weight:bold;">School: </td></tr>
					<tr><td><span id="school_description" class="resident_data"/></td></tr>
					<tr><td style="font-weight:bold;">Program: </td></tr>
					<tr><td><span id="program_description" class="resident_data"/></td></tr>
					<tr><td style="font-weight:bold;">Category: </td></tr>
					<tr><td><span id="category_description" class="resident_data"/></td></tr>
				</tbody>
			</table>
		</div>

		<div id="submitResponse" style="display:none; float:right; width:35%"></div>

		<br class="clearboth">

		<div id="button_container" style="float: right;">
			<input type="button" id="download_button" name="download_button" value="Download Data" />
		</div>
		<br />
		<br class="clearboth">

		<div id="schedule_container" style="float: left;">
			<table id="mtd_schedule" class="" style="display:none"></table>
		</div>
<?php
				}