<?php


if(isset($_POST["org_id"])){
	$ORGANISATION_ID = $_POST["org_id"];
	
	if(isset($_POST["posted_objectives"])){
		$posted_objectives = $POST["posted_objectives"];
	}
	else{
		$posted_objectives = null;
	}
	
	list($course_objectives,$top_level_id) = courses_fetch_objectives($ORGANISATION_ID,array(0),-1,0, false, $posted_objectives);

	if (is_array($course_objectives["primary_ids"])) {
		foreach ($course_objectives["primary_ids"] as $objective_id) {
			echo "<input type=\"hidden\" class=\"primary_objectives\" id=\"primary_objective_".$objective_id."\" name=\"primary_objectives[]\" value=\"".$objective_id."\" />\n";
		}
	}
	if (is_array($course_objectives["secondary_ids"])) {
		foreach ($course_objectives["secondary_ids"] as $objective_id) {
			echo "<input type=\"hidden\" class=\"secondary_objectives\" id=\"secondary_objective_".$objective_id."\" name=\"secondary_objectives[]\" value=\"".$objective_id."\" />\n";
		}
	}
	if (is_array($course_objectives["tertiary_ids"])) {
		foreach ($course_objectives["tertiary_ids"] as $objective_id) {
			echo "<input type=\"hidden\" class=\"tertiary_objectives\" id=\"tertiary_objective_".$objective_id."\" name=\"tertiary_objectives[]\" value=\"".$objective_id."\" />\n";
		}
	}


	if(!count($course_objectives["objectives"])){
		$NOTICE = 1;
		$NOTICESTR = null;
		$NOTICESTR[] = "No Curriculum Objectives were found for this organisation.";
		echo display_notice();
			
	}
	else{
	?>							
	<select id="objective_select" onchange="showMultiSelect()">
	<option value="">- Select Competency -</option>
	<?php
		$objective_select = "";
		foreach ($course_objectives["objectives"] as $parent_id => $parent) {
			if ($parent["parent"] == $top_level_id) {
			echo "<optgroup label=\"".$parent["name"]."\">";
			foreach($course_objectives["objectives"] as $objective_id => $objective) {
				if ($objective["parent"] == $parent_id) {
					echo "<option value=\"id_".$objective_id."\">".$objective["name"]."</option>";
					foreach($course_objectives["objectives"] as $child_id => $child) {
						if ($child["parent"] == $objective_id) {
							if (array_search($child_id, $course_objectives["used_ids"]) !== false) {
								$checked = "checked=\"checked\"";
							} else {
								$checked = "";
							}
							$selectable_objectives[$child_id] = array("text" => $child["name"], "value" => $child_id, "checked" => $checked, "category" => true);
							foreach($course_objectives["objectives"] as $grandkid_id => $grandkid) {
								if ($grandkid["parent"] == $child_id) {
									if (array_search($grandkid_id, $course_objectives["used_ids"]) !== false) {
										$checked = "checked=\"checked\"";
									} else {
										$checked = "";
									}
									if ($grandkid["parent"] == $child_id) {
										$selectable_objectives[$grandkid_id] = array("text" => "<strong>".$grandkid["name"]."</strong><br />".$grandkid["description"], "value" => $grandkid_id, "checked" => $checked);
									}
								}
							}
						}
					}
					$objective_select .= course_objectives_multiple_select_options_checked("id_".$objective_id, $selectable_objectives, array("title" => "Please select program or curriculum objectives", "cancel" => true, "cancel_text" => "Close", "submit" => false, "width" => "550px"));
				}
				unset($selectable_objectives);
			}
			echo "\n</optgroup>";
		}
	}
	?>
	</select>
<?php
	}

}

?>
