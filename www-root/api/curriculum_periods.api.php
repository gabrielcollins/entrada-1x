<?php
@set_include_path(implode(PATH_SEPARATOR, array(
					dirname(__FILE__) . "/../core",
					dirname(__FILE__) . "/../core/includes",
					dirname(__FILE__) . "/../core/library",
					get_include_path(),
				)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

if ((isset($_SESSION["isAuthorized"])) && ((bool) $_SESSION["isAuthorized"]) && $ENTRADA_ACL->amIAllowed(new CourseResource(null, $ENTRADA_USER->getActiveOrganisation()), "create")) {	
	if (isset($_GET["key"]) && $tmp_input = clean_input($_GET["key"], array("int"))) {
		$key = $tmp_input;
	} else {
		$key = 0;
	}
	
	if ($key) {
		$query = "SELECT * FROM `curriculum_periods` WHERE `cperiod_id` = ".$db->qstr($key);
		$period_data = $db->GetRow($query);
		
		$query = "	SELECT * FROM `groups` AS a
					JOIN `group_organisations` AS b
					ON a.`group_id`=b.`group_id`
					WHERE b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
					AND a.`group_active` = 1";
		$groups = $db->GetAll($query);		
?>
	 <div class="period_item" id="period_item_<?php echo $key;?>" style="margin-top:20px;">									
		<div class="clearfix">
			<i class="icon-minus-sign remove_period" id="remove_period_<?php echo $key;?>"></i>&nbsp;<strong><?php echo (($period_data["curriculum_period_title"]) ? $period_data["curriculum_period_title"] . " - " : ""); ?></strong><span class=\"content-small\"><?php echo date("F jS, Y",$period_data["start_date"])." to ".date("F jS, Y",$period_data["finish_date"]); ?></span><a href="javascript:void(0)" class="enrollment-toggle strong-green pull-right" id="add_audience_<?php echo $key;?>">Add Audience</a>											
		</div>	
		<div class="audience_selector span12 pull-left" id="audience_type_select_<?php echo $key;?>" style="display: none; margin-top: 20px;">
			<select class="audience_type_select" id="audience_type_select_<?php echo $key;?>" onchange="showSelect(<?php echo $key;?>,this.options[this.selectedIndex].value)">
				<option value="0">-- Select Audience Type --</option>
				<option value="cohort">Cohort</option>
				<option value="individual">Individual</option>
			</select>
			<select style="display:none;" class="type_select" id="cohort_select_<?php echo $key;?>" onchange="addAudience(<?php echo $key;?>,this.options[this.selectedIndex].text,'cohort',this.options[this.selectedIndex].value)"><option value="0">-- Add Cohort --</option>
				<?php				
				foreach ($groups as $group) {
					echo "<option value=\"".$group["group_id"]."\">".$group["group_name"]."</option>";
				}
				?>
			</select>										
			<input style="display:none;width:203px;vertical-align: middle;margin-left:10px;margin-right:10px;" type="text" name="fullname" class="type_select" id="student_<?php echo $key;?>_name" autocomplete="off"/>
			<input style="display:none;" type="button" class="btn type_select individual_add_btn" id="add_associated_student_<?php echo $key;?>" value="Add" style="vertical-align: middle" />
			<div class="autocomplete" id="student_<?php echo $key;?>_name_auto_complete" style="margin-left:200px;"></div>
			<div style="display:none; margin-left: 240px;" id="student_example_<?php echo $key;?>">(Example: <?php echo $ENTRADA_USER->getFullname(true); ?>)</div>
			<input type="hidden" name="group_audience_members[]" id="group_audience_members_<?php echo $key;?>" />
			<input type="hidden" name="individual_audience_members[]" id="associated_student_<?php echo $key;?>"/>
			<input type="hidden" name="student_id[]" id="student_<?php echo $key;?>_id"/>
			<input type="hidden" name="student_ref[]" id="student_<?php echo $key;?>_ref"/>
			<input type="hidden" name="periods[]" value="<?php echo $key;?>"/>
		</div>
		<div id="no_audience_msg_<?php echo $key;?>" class="alert alert-block alert-info no_audience_msg" style="margin-top: 20px;">
			Please use the <strong>Add Audience</strong> link above to add an audience to this enrollment period.
		</div>
		<div class="audience_section span12 pull-left" id="audience_section_<?php echo $key;?>" style="display:none; margin-top: 20px; margin-bottom: 20px; border-bottom:1px solid #D3D3D3;">
			<div class="audience_list" id="audience_list_<?php echo $key;?>">																						
				<ul id="audience_list_container_<?php echo $key;?>" class="listContainer" style="display: none;">					
					<li><strong>Cohorts</strong>
						<ol id="audience_container_<?php echo $key;?>" class="sortableList">
						
						</ol>
					</li>
				</ul>					
				<ul id="student_<?php echo $key;?>_list_container" class="listContainer" style="display: none;">
					<li><strong>Students</strong>
						<ol id="student_<?php echo $key;?>_list" class="sortableList">
							
						</ol>
					</li>
				</ul>
			</div>
		</div>
	</div>	
<?php
	}
} else {
	//no curriculium period provided
}
?>
