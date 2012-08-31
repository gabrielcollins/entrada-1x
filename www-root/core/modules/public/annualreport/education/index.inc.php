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
 * @author Unit: School of Medicine
 * @author Developer: Andrew Dos-Santos <andrew.dos-santos@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_ANNUAL_REPORT"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('annualreport', 'read', false)) {
	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	
	if(!isset($_SESSION["education_expand_grid"])) {
		$_SESSION["education_expand_grid"] = "undergraduate_medical_teaching_grid";
	}
	?>
	<h1>Section I - Education</h1>
	<table id="flex1" style="display:none"></table>
	<table id="flex2" style="display:none"></table>
	<table id="flex3" style="display:none"></table>
	<table id="flex4" style="display:none"></table>
	<table id="flex5" style="display:none"></table>
	<table id="flex6" style="display:none"></table>
	<table id="flex7" style="display:none"></table>
	<table id="flex8" style="display:none"></table>
	<table id="flex9" style="display:none"></table>
	<table id="flex10" style="display:none"></table>
	
	<?php
	// @TODO
	// Had to hardcode 1230785940 as the event start time due to the change in the event types from 2008 to 2009.
	// Need to get this done before April 1st so this stays for now until I can revisit it.
	$query	= "SELECT a.`event_id`, a.`event_title`, a.`course_id`, a.`event_duration`, 
	a.`event_start`, c.`course_name`, c.`course_code`, a.`event_phase`, d.`audience_type`, d.`audience_value`, e.`eventtype_id`, e.`duration`
	FROM `events` AS a
	LEFT JOIN `event_contacts` AS b
	ON b.`event_id` = a.`event_id`
	AND b.`proxy_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])."
	LEFT JOIN `courses` AS c
	ON a.`course_id` = c.`course_id`
	LEFT JOIN `event_audience` AS d
	ON a.`event_id` = d.`event_id`
	LEFT JOIN `event_eventtypes` AS e
	ON a.`event_id` = e.`event_id`
	WHERE b.`proxy_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])."
	AND event_start > 1230785940
	ORDER BY `event_start` DESC, `course_name` DESC, `course_code` DESC";

	$results	= $db->GetAll($query);
	
	$proxyID 	= $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"];
	
	$modified				= 0;
	$undergraduateArray 	= array();
	$previousEventID		= '';
	$previousYear			= '';
	$previousCourse 		= '';
	$previousPhase 			= '';
	$previousCourseNumber	= '';
	
	// Get the default enrollments to be used to determine how many learners were enrolled in an event
	$defaultEnrollments = getDefaultEnrollment();
	
	foreach($results as $result) {
		$currentYear 		= trim((date("Y", $result["event_start"])));
		$currentCourse 		= trim($result['course_name']);
		$currentCourseNum 	= trim($result['course_code']);
		$audienceType 		= trim($result['audience_type']);
		$audienceValue 		= trim($result['audience_value']);
		$courseID			= trim($result['course_id']);
		$eventID			= trim($result['event_id']);
		$phase              = trim($result['event_phase']);
		$eventtype_id       = trim($result['eventtype_id']);
		
		// The following logic compiles data on a course level based on the learning events within the system.
		// Student counts are included in this logic as it used to be used in the system and maybe required by your school so we have left it here
		// However, our school relies on displaying "defaul enrollment" numbers from the events_lu_eventtypes table.
		if(!key_exists($eventtype_id, $defaultEnrollments)) {
			if($audienceValue != "") {
				switch($audienceType) {
					case "proxy_id":
						if(!isset($undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['students'])) {
							$undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['students'] = 1;
						} else {
							$undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['students']++;
						}
						if($eventID != $previousEventID) {
							$undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['course_number'] = $currentCourseNum;
							$undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['hours'] = $undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['hours'] + ($result['duration'] / 60);
							$undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['phase'] = $phase;
						} else if(!isset($undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['phase'])) {
							$undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['course_number'] = $currentCourseNum;
							$undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['phase'] = $phase;
							$undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['hours'] = $result['duration'] / 60;
						}
						break;
					case "grad_year":
					default:
						if($eventID != $previousEventID) {
							$studQuery = "SELECT COUNT(*) AS `num_studs`
							FROM `".AUTH_DATABASE."`.`user_access` 
							WHERE `app_id` = '".AUTH_APP_ID."' 
							AND `role` = ".$db->qstr($audienceValue);	
							
							$studResult	= $db->GetRow($studQuery);
							$undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['students'] = $studResult['num_studs'];
							$undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['hours'] = $undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['hours'] + ($result['duration'] / 60);
							$undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['course_number'] = $currentCourseNum;
							$undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['phase'] = $phase;
						} else {
							$undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['course_number'] = $currentCourseNum;
							$undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['phase'] = $phase;
							$undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['hours'] = $undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['hours'] + ($result['duration'] / 60);
						}
						break;
				}
			}
		} else {
			// There is a default enrollment for this event type so use that to determine the number of learners enrolled
			if($eventID != $previousEventID && $previousEventID != "") {
				$undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['hours'] = $undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['hours'] + ($result['duration'] / 60);
				$undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['course_number'] = $currentCourseNum;
				$undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['phase'] = $phase;
				$undergraduateArray[$currentYear][$currentCourse][$currentCourseNum][$phase][$eventtype_id]['students'] = $defaultEnrollments[$eventtype_id]["default_enrollment"];
			}
		}
		$previousEventID = $eventID;
	}
	
	foreach($undergraduateArray as $year => $value) {
		foreach($value as $course => $nextValue) {
			foreach($nextValue as $coursenum => $nextNextValue) {
				$PROCESSED["lecture_enrollment"] 						= 0;
				$PROCESSED["lecture_hours"]								= 0;
				$PROCESSED["lab_hours"]									= 0;
				$PROCESSED["small_group_hours"]							= 0;
				$PROCESSED["patient_contact_session_hours"] 			= 0;
				$PROCESSED["symposium_hours"] 							= 0;
				$PROCESSED["directed_independant_learning_hours"] 		= 0;
				$PROCESSED["review_feedback_session_hours"] 			= 0;
				$PROCESSED["examination_hours"] 						= 0;
				$PROCESSED["clerkship_seminar_hours"] 					= 0;
				$PROCESSED["other_hours"] 								= 0;
				$PROCESSED["coord_enrollment"] 							= 0;
				unset($PROCESSED["comments"]);

				foreach($nextNextValue as $phaseValue => $eventType) {
					foreach($eventType as $eventTypeValue => $amounts) {
						switch ($eventTypeValue) {
							case 1:
								$PROCESSED["lecture_hours"] 							= $amounts["hours"];
							break;
							case 6:
								$PROCESSED["lab_hours"] 								= $amounts["hours"];
							break;
							case 8:
								$PROCESSED["small_group_hours"] 						= $amounts["hours"];
							break;
							case 13:
								$PROCESSED["symposium_hours"] 							= $amounts["hours"];
							case 15:
								$PROCESSED["directed_independant_learning_hours"] 		= $amounts["hours"];
							break;	
							case 18:
								$PROCESSED["review_feedback_session_hours"] 			= $amounts["hours"];
							break;
							case 20:
								$PROCESSED["examination_hours"] 						= $amounts["hours"];
							break;
							case 23:
								$PROCESSED["clerkship_seminar_hours"] 					= $amounts["hours"];
							break;
							case 11:
								$PROCESSED["patient_contact_session_hours"] 			= $amounts["hours"];
							break;
							case 24:
								$PROCESSED["other_hours"] 								= $amounts["hours"];
							break;
							default:
								$PROCESSED["lecture_hours"] 							= $amounts["hours"];
							break;
						}
						$phase = $amounts["phase"];
					}
				
					$PROCESSED["course_number"] 	= $coursenum;
					$PROCESSED["year_reported"] 	= $year;
					$PROCESSED["proxy_id"] 			= $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"];
					$PROCESSED["course_name"]		= $course;
					$PROCESSED["lecture_phase"] 	= $phase;
					$PROCESSED["assigned"] 			= 'Yes';
					
					// Write to the database here
					$checkQuery	= "SELECT * 
					FROM `ar_undergraduate_teaching` 
					WHERE `year_reported` = ".$db->qstr($year)."
					AND `course_number` = ".$db->qstr($coursenum)."
					AND `course_name` = ".$db->qstr($course)."
					AND `lecture_phase` = ".$db->qstr($phase)."
					AND `proxy_id` = '".$PROCESSED["proxy_id"]."'";
					
					if(!$checkResult = $db->GetRow($checkQuery)) {
						$PROCESSED["comments"] 			= '';
						$PROCESSED["updated_date"] 		= time();
						$PROCESSED["updated_by"] 		= "9999999";
						
						if($db->AutoExecute("ar_undergraduate_teaching", $PROCESSED, "INSERT")) {
							$EVENT_ID = $db->Insert_Id();
							application_log("success", "Undergraduate Teaching [".$EVENT_ID."] added to the system.");
						} else {
							$ERROR++;
							$ERRORSTR[] = "There was a problem inserting this record into the system. The MEdIT Unit was informed of this error; please try again later.";
			
							application_log("error", "There was an error inserting an undergraduate teaching record. Database said: ".$db->ErrorMsg());
						}
					} else {
						// Ensure all editable fields are not over written by null or 0
						$PROCESSED["assigned"] 			= $checkResult["assigned"];
						$PROCESSED["coord_enrollment"] 	= $checkResult["coord_enrollment"];
						$PROCESSED["comments"] 			= $checkResult["comments"];
						
						$UNDERGRADUATE_TEACHING_ID = $checkResult['undergraduate_teaching_id'];
						
						$db->AutoExecute(DATABASE_NAME.".ar_undergraduate_teaching", $PROCESSED, "UPDATE", "`undergraduate_teaching_id`=".$db->qstr($UNDERGRADUATE_TEACHING_ID));
					}
				}
			}
		}
	}
	$fields = "ar_undergraduate_teaching,undergraduate_teaching_id,course_number,course_name,lecture_phase,year_reported";
	?>
	<script type="text/javascript" defer="defer">
	jQuery(document).ready(function() {
		jQuerydialog = jQuery('<div></div>')
			.html('<span class="ui-icon ui-icon-circle-check" style="float:left; margin:0 7px 50px 0;"></span>You must select at least one record in order to delete.')
			.dialog({
				autoOpen: false,
				title: 'Please Select a Record',
				buttons: {
					Ok: function() {
						jQuery(this).dialog('close');
					}
				}
			});
		
		jQueryError = jQuery('<div></div>')
		.html('<span class="ui-icon ui-icon-locked" style="float:left; margin:0 7px 50px 0;"></span>Error: You cannot delete records from previous years. Contact support if you need one deleted.')
		.dialog({
			autoOpen: false,
			title: 'Error',
			buttons: {
				Cancel: function() {
					jQuery(this).dialog('close');
				},
				'Contact Support': function() {
					sendFeedback('<?php echo ENTRADA_URL;?>/agent-feedback.php?enc=<?php echo feedback_enc()?>');
					jQuery(this).dialog('close');
				}
			}
		});
		
		undergraduate_medical_teaching_grid = jQuery("#flex1").flexigrid
		(
			{
			url: '<?php echo ENTRADA_URL; ?>/api/ar_loadgrid.api.php?id=<?php echo $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]; ?>&t=<?php echo $fields; ?>',
			dataType: 'json',
			method: 'POST',
			colModel : [
				{display: 'Course Code', name : 'course_number', width : 100, sortable : true, align: 'left'},
				{display: 'Course', name : 'course_name', width : 405, sortable : true, align: 'left'},
				{display: 'Phase', name : 'lecture_phase', width : 59, sortable : true, align: 'left'},
				{display: 'Year', name : 'year_reported', width : 50, sortable : true, align: 'left'},
				{display: 'Edit / View', name : 'ctled', width : 50,  sortable : false, align: 'center', process:editUndergradMedicalTeaching}
				],
			searchitems : [
				{display: 'Course Code', name : 'course_number'},
				{display: 'Course', name : 'course_name'},
				{display: 'Phase', name : 'lecture_phase'},
				{display: 'Year', name : 'year_reported', isdefault: true}
				],
			sortname: "year_reported",
			sortorder: "desc",
			resizable: false, 
			disableSelect: true, 
			usepager: true,
			showToggleBtn: false,
			collapseTable: <?php echo ($_SESSION["education_expand_grid"] == "undergraduate_medical_teaching_grid" ? "false" : "true"); ?>,
			title: 'A. Undergrad Teaching - MEdTech Central',
			useRp: true,
			rp: 15,
			showTableToggleBtn: true,
			width: 732,
			height: 200,
			nomsg: 'No Results', 
			buttons : [
	            {name: 'Report Missing', bclass: 'report_missing', onpress : reportMissingUndergradMedicalTeaching}
	            ]
			}
		);
			
		function reportMissingUndergradMedicalTeaching(com,grid) {
	        if (com=='Report Missing') {
	        	sendFeedback('<?php echo ENTRADA_URL; ?>/agent-undergrad-teaching.php?enc=<?php echo feedback_enc(); ?>')
	        }            
	    }
	     
	    function editUndergradMedicalTeaching(celDiv,id) {
	    	jQuery(celDiv).html("<a href='<?php echo ENTRADA_URL; ?>/annualreport/education?section=edit_undergraduate&amp;rid="+id+"' style=\"cursor: pointer; cursor: hand\" text-decoration: none><img src=\"<?php echo ENTRADA_RELATIVE; ?>/images/action-edit.gif\" style=\"border: none\"/></a>");
	    }
		<?php $fields = "ar_undergraduate_nonmedical_teaching,undergraduate_nonmedical_teaching_id,course_number,course_name,assigned,year_reported"; ?>
		undergraduate_nonmedical_grid = jQuery("#flex2").flexigrid
		(
			{
			url: '<?php echo ENTRADA_URL; ?>/api/ar_loadgrid.api.php?id=<?php echo $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]; ?>&t=<?php echo $fields; ?>',
			dataType: 'json',
			method: 'POST',
			colModel : [
				{display: 'Course Code', name : 'course_number', width : 100, sortable : true, align: 'left'},
				{display: 'Course', name : 'course_name', width : 405, sortable : true, align: 'left'},
				{display: 'Assigned', name : 'assigned', width : 59, sortable : true, align: 'left'},
				{display: 'Year', name : 'year_reported', width : 50, sortable : true, align: 'left'},
				{display: 'Edit', name : 'ctled', width : 25,  sortable : false, align: 'center', process:edRow}
				],
			searchitems : [
				{display: 'Assigned', name : 'assigned'},
				{display: 'Course Code', name : 'course_number'},
				{display: 'Course', name : 'course_name'},
				{display: 'Year', name : 'year_reported', isdefault: true}
				],
			sortname: "year_reported",
			sortorder: "desc",
			resizable: false, 
			usepager: true,
			showToggleBtn: false,
			collapseTable: <?php echo ($_SESSION["education_expand_grid"] == "undergraduate_nonmedical_grid" ? "false" : "true"); ?>,
			title: 'B. Undergraduate Teaching - Other',
			useRp: true,
			rp: 15,
			showTableToggleBtn: true,
			width: 732,
			height: 200,
			nomsg: 'No Results', 
			buttons : [
	            {name: 'Add', bclass: 'add', onpress : addRecord},
	            {separator: true}, 
	            {name: 'Delete Selected', bclass: 'delete', onpress : deleteRecord}
	            ]
			}
		);
			
		function addRecord(com,grid) {
	        if (com=='Add') {
	             window.location='<?php echo ENTRADA_URL; ?>/annualreport/education?section=add_undergraduate_nonmedical';
	        }            
	    }
	     
	    function edRow(celDiv,id) {
	    	jQuery(celDiv).html("<a href='<?php echo ENTRADA_URL; ?>/annualreport/education?section=edit_undergraduate_nonmedical&amp;rid="+id+"' style=\"cursor: pointer; cursor: hand\" text-decoration: none><img src=\"<?php echo ENTRADA_RELATIVE; ?>/images/action-edit.gif\" style=\"border: none\"/></a>");
	    }
	    
	    function deleteRecord(com,grid) {
		    if (com=='Delete Selected') {
		    	jQuery(function() {
		    		var error = "false";
					if(jQuery('.trSelected',grid).length>0) {
			    		// a workaround for a flaw in the demo system (http://dev.jqueryui.com/ticket/4375), ignore!
						jQuery("#dialog-confirm").dialog("destroy");
						jQuery('.trSelected', grid).each(function() {  
	               			var reportYear = jQuery(this).find('div')[3].textContent;
							if(reportYear < <?php echo $AR_CUR_YEAR;?>) {
		               			// Do not allow the deletion of years that are prior to the current reporting year.
		               			error = "true";
							}
						});
						
						if(error == "false") {
							// allow deletion
							jQuery("#dialog-confirm").dialog({
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
						               url: '<?php echo ENTRADA_URL; ?>/api/ar_delete.api.php?id=<?php echo $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]; ?>&t=<?php echo $fields; ?>&rid='+ids
						             });
							       	
							       	window.setTimeout('undergraduate_nonmedical_grid.flexReload()', 1000);
									jQuery(this).dialog('close');
								},
								Cancel: function() {
									jQuery(this).dialog('close');
								}
							}
							});
						} else {
							jQueryError.dialog('open');
						}
			    	} else {
				    	jQuerydialog.dialog('open');
			    	}
				});
		    }
		}
		
		<?php $fields = "ar_graduate_teaching,graduate_teaching_id,course_number,course_name,assigned,year_reported"; ?>
		graduate_grid = jQuery("#flex3").flexigrid
		(
			{
			url: '<?php echo ENTRADA_URL; ?>/api/ar_loadgrid.api.php?id=<?php echo $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]; ?>&t=<?php echo $fields; ?>',
			dataType: 'json',
			method: 'POST',
			colModel : [
				{display: 'Course Code', name : 'course_number', width : 100, sortable : true, align: 'left'},
				{display: 'Course', name : 'course_name', width : 405, sortable : true, align: 'left'},
				{display: 'Assigned', name : 'assigned', width : 59, sortable : true, align: 'left'},
				{display: 'Year', name : 'year_reported', width : 50, sortable : true, align: 'left'},
				{display: 'Edit', name : 'ctled', width : 25,  sortable : false, align: 'center', process:editGraduate}
				],
			searchitems : [
				{display: 'Course Code', name : 'course_number'},
				{display: 'Course', name : 'course_name'},
				{display: 'Assigned', name : 'assigned'},
				{display: 'Year', name : 'year_reported', isdefault: true}
				],
			sortname: "year_reported",
			sortorder: "desc",
			resizable: false, 
			usepager: true,
			showToggleBtn: false,
			collapseTable: <?php echo ($_SESSION["education_expand_grid"] == "graduate_grid" ? "false" : "true"); ?>,
			title: 'C. Graduate Teaching',
			useRp: true,
			rp: 15,
			showTableToggleBtn: true,
			width: 732,
			height: 200,
			nomsg: 'No Results', 
			buttons : [
	            {name: 'Add', bclass: 'add', onpress : addGraduate},
	            {separator: true}, 
	            {name: 'Delete Selected', bclass: 'delete', onpress : deleteGraduate}
	            ]
			}
		);
			
		function addGraduate(com,grid) {
	        if (com=='Add') {
	             window.location='<?php echo ENTRADA_URL; ?>/annualreport/education?section=add_graduate';
	        }            
	    }
	     
	    function editGraduate(celDiv,id) {
	    	jQuery(celDiv).html("<a href='<?php echo ENTRADA_URL; ?>/annualreport/education?section=edit_graduate&amp;rid="+id+"' style=\"cursor: pointer; cursor: hand\" text-decoration: none><img src=\"<?php echo ENTRADA_RELATIVE; ?>/images/action-edit.gif\" style=\"border: none\"/></a>");
	    }
	    
	    function deleteGraduate(com,grid) {
		    if (com=='Delete Selected') {
		    	jQuery(function() {
		    		var error = "false";
					if(jQuery('.trSelected',grid).length>0) {
			    		// a workaround for a flaw in the demo system (http://dev.jqueryui.com/ticket/4375), ignore!
						jQuery("#dialog-confirm").dialog("destroy");
						jQuery('.trSelected', grid).each(function() {  
	               			var reportYear = jQuery(this).find('div')[3].textContent;
							if(reportYear < <?php echo $AR_CUR_YEAR;?>) {
		               			// Do not allow the deletion of years that are prior to the current reporting year.
		               			error = "true";
							}
						});
						
						if(error == "false") {
							// allow deletion
							jQuery("#dialog-confirm").dialog({
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
						               url: '<?php echo ENTRADA_URL; ?>/api/ar_delete.api.php?id=<?php echo $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]; ?>&t=<?php echo $fields; ?>&rid='+ids
						             });
							       	
							       	window.setTimeout('graduate_grid.flexReload()', 1000);
									jQuery(this).dialog('close');
								},
								Cancel: function() {
									jQuery(this).dialog('close');
								}
							}
							});
						} else {
							jQueryError.dialog('open');
						}
			    	} else {
				    	jQuerydialog.dialog('open');
			    	}
				});
		    }
		}
		
		<?php $fields = "ar_undergraduate_supervision,undergraduate_supervision_id,course_number,student_name,degree,year_reported"; ?>
		undergraduate_supervision_grid = jQuery("#flex4").flexigrid
		(
			{
			url: '<?php echo ENTRADA_URL; ?>/api/ar_loadgrid.api.php?id=<?php echo $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]; ?>&t=<?php echo $fields; ?>',
			dataType: 'json',
			method: 'POST',
			colModel : [
				{display: 'Course Code', name : 'course_number', width : 100, sortable : true, align: 'left'},
				{display: 'Student', name : 'student_name', width : 405, sortable : true, align: 'left'},
				{display: 'Degree', name : 'degree', width : 59, sortable : true, align: 'left'},
				{display: 'Year', name : 'year_reported', width : 50, sortable : true, align: 'left'},
				{display: 'Edit', name : 'ctled', width : 25,  sortable : false, align: 'center', process:editUndergraduateSupervision}
				],
			searchitems : [
				{display: 'Course Code', name : 'course_number'},
				{display: 'Student', name : 'student_name'},
				{display: 'Degree', name : 'degree'},
				{display: 'Year', name : 'year_reported', isdefault: true}
				],
			sortname: "year_reported",
			sortorder: "desc",
			resizable: false, 
			usepager: true,
			showToggleBtn: false,
			collapseTable: <?php echo ($_SESSION["education_expand_grid"] == "undergraduate_supervision_grid" ? "false" : "true"); ?>,
			title: 'D. Undergraduate Supervision',
			useRp: true,
			rp: 15,
			showTableToggleBtn: true,
			width: 732,
			height: 200,
			nomsg: 'No Results', 
			buttons : [
	            {name: 'Add', bclass: 'add', onpress : addUndergraduateSupervision},
	            {separator: true}, 
	            {name: 'Delete Selected', bclass: 'delete', onpress : deleteUndergraduateSupervision}
	            ]
			}
		);
			
		function addUndergraduateSupervision(com,grid) {
	        if (com=='Add') {
	             window.location='<?php echo ENTRADA_URL; ?>/annualreport/education?section=add_undergrad_sup';
	        }            
	    }
	     
	    function editUndergraduateSupervision(celDiv,id) {
	    	jQuery(celDiv).html("<a href='<?php echo ENTRADA_URL; ?>/annualreport/education?section=edit_undergrad_sup&amp;rid="+id+"' style=\"cursor: pointer; cursor: hand\" text-decoration: none><img src=\"<?php echo ENTRADA_RELATIVE; ?>/images/action-edit.gif\" style=\"border: none\"/></a>");
	    }
	    
	    function deleteUndergraduateSupervision(com,grid) {
		    if (com=='Delete Selected') {
		    	jQuery(function() {
		    		var error = "false";
					if(jQuery('.trSelected',grid).length>0) {
			    		// a workaround for a flaw in the demo system (http://dev.jqueryui.com/ticket/4375), ignore!
						jQuery("#dialog-confirm").dialog("destroy");
						jQuery('.trSelected', grid).each(function() {  
	               			var reportYear = jQuery(this).find('div')[3].textContent;
							if(reportYear < <?php echo $AR_CUR_YEAR;?>) {
		               			// Do not allow the deletion of years that are prior to the current reporting year.
		               			error = "true";
							}
						});
						
						if(error == "false") {
							// allow deletion
							jQuery("#dialog-confirm").dialog({
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
						               url: '<?php echo ENTRADA_URL; ?>/api/ar_delete.api.php?id=<?php echo $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]; ?>&t=<?php echo $fields; ?>&rid='+ids
						             });
							       	
							       	window.setTimeout('undergraduate_supervision_grid.flexReload()', 1000);
									jQuery(this).dialog('close');
								},
								Cancel: function() {
									jQuery(this).dialog('close');
								}
							}
							});
						} else {
							jQueryError.dialog('open');
						}
			    	} else {
				    	jQuerydialog.dialog('open');
			    	}
				});
		    }
		}
		
		<?php 	$fields = "ar_graduate_supervision,graduate_supervision_id,supervision,student_name,degree,year_reported"; ?>
		graduate_supervision_grid = jQuery("#flex5").flexigrid
		(
			{
			url: '<?php echo ENTRADA_URL; ?>/api/ar_loadgrid.api.php?id=<?php echo $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]; ?>&t=<?php echo $fields; ?>',
			dataType: 'json',
			method: 'POST',
			colModel : [
				{display: 'Supervision', name : 'supervision', width : 100, sortable : true, align: 'left'},
				{display: 'Student', name : 'student_name', width : 405, sortable : true, align: 'left'},
				{display: 'Degree', name : 'degree', width : 59, sortable : true, align: 'left'},
				{display: 'Year', name : 'year_reported', width : 50, sortable : true, align: 'left'},
				{display: 'Edit', name : 'ctled', width : 25,  sortable : false, align: 'center', process:editGraduateSupervision}
				],
			searchitems : [
				{display: 'Supervision', name : 'supervision'},
				{display: 'Student', name : 'student_name'},
				{display: 'Degree', name : 'degree'},
				{display: 'Year', name : 'year_reported', isdefault: true}
				],
			sortname: "year_reported",
			sortorder: "desc",
			resizable: false, 
			usepager: true,
			showToggleBtn: false,
			collapseTable: <?php echo ($_SESSION["education_expand_grid"] == "graduate_supervision_grid" ? "false" : "true"); ?>,
			title: 'E. Graduate Supervision',
			useRp: true,
			rp: 15,
			showTableToggleBtn: true,
			width: 732,
			height: 200,
			nomsg: 'No Results', 
			buttons : [
	            {name: 'Add', bclass: 'add', onpress : addGraduateSupervision},
	            {separator: true}, 
	            {name: 'Delete Selected', bclass: 'delete', onpress : deleteGraduateSupervision}
	            ]
			}
		);
			
		function addGraduateSupervision(com,grid) {
	        if (com=='Add') {
	             window.location='<?php echo ENTRADA_URL; ?>/annualreport/education?section=add_grad_sup';
	        }            
	    }
	     
	    function editGraduateSupervision(celDiv,id) {
	    	jQuery(celDiv).html("<a href='<?php echo ENTRADA_URL; ?>/annualreport/education?section=edit_grad_sup&amp;rid="+id+"' style=\"cursor: pointer; cursor: hand\" text-decoration: none><img src=\"<?php echo ENTRADA_RELATIVE; ?>/images/action-edit.gif\" style=\"border: none\"/></a>");
	    }
	    
	    function deleteGraduateSupervision(com,grid) {
		    if (com=='Delete Selected') {
		    	jQuery(function() {
		    		var error = "false";
					if(jQuery('.trSelected',grid).length>0) {
			    		// a workaround for a flaw in the demo system (http://dev.jqueryui.com/ticket/4375), ignore!
						jQuery("#dialog-confirm").dialog("destroy");
						jQuery('.trSelected', grid).each(function() {  
	               			var reportYear = jQuery(this).find('div')[3].textContent;
							if(reportYear < <?php echo $AR_CUR_YEAR;?>) {
		               			// Do not allow the deletion of years that are prior to the current reporting year.
		               			error = "true";
							}
						});
						
						if(error == "false") {
							// allow deletion
							jQuery("#dialog-confirm").dialog({
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
						               url: '<?php echo ENTRADA_URL; ?>/api/ar_delete.api.php?id=<?php echo $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]; ?>&t=<?php echo $fields; ?>&rid='+ids
						             });
							       	
							       	window.setTimeout('graduate_supervision_grid.flexReload()', 1000);
									jQuery(this).dialog('close');
								},
								Cancel: function() {
									jQuery(this).dialog('close');
								}
							}
							});
						} else {
							jQueryError.dialog('open');
						}
			    	} else {
				    	jQuerydialog.dialog('open');
			    	}
				});
		    }
		}
		
		<?php $fields = "ar_memberships,memberships_id,student_name,department,degree,year_reported"; ?>
		memberships_grid = jQuery("#flex6").flexigrid
		(
			{
			url: '<?php echo ENTRADA_URL; ?>/api/ar_loadgrid.api.php?id=<?php echo $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]; ?>&t=<?php echo $fields; ?>',
			dataType: 'json',
			method: 'POST',
			colModel : [
				{display: 'Student', name : 'student_name', width : 205, sortable : true, align: 'left'},
				{display: 'Department', name : 'department', width : 300, sortable : true, align: 'left'},
				{display: 'Degree', name : 'degree', width : 59, sortable : true, align: 'left'},
				{display: 'Year', name : 'year_reported', width : 50, sortable : true, align: 'left'},
				{display: 'Edit', name : 'ctled', width : 25,  sortable : false, align: 'center', process:editMemberships}
				],
			searchitems : [
				{display: 'Student', name : 'student_name'},
				{display: 'Department', name : 'department'},
				{display: 'Degree', name : 'degree'},
				{display: 'Year', name : 'year_reported', isdefault: true}
				],
			sortname: "year_reported",
			sortorder: "desc",
			resizable: false, 
			usepager: true,
			showToggleBtn: false,
			collapseTable: <?php echo ($_SESSION["education_expand_grid"] == "memberships_grid" ? "false" : "true"); ?>,
			title: 'F. Memberships',
			useRp: true,
			rp: 15,
			showTableToggleBtn: true,
			width: 732,
			height: 200,
			nomsg: 'No Results', 
			buttons : [
	            {name: 'Add', bclass: 'add', onpress : addMemberships},
	            {separator: true}, 
	            {name: 'Delete Selected', bclass: 'delete', onpress : deleteMemberships}
	            ]
			}
		);
			
		function addMemberships(com,grid) {
	        if (com=='Add') {
	             window.location='<?php echo ENTRADA_URL; ?>/annualreport/education?section=add_membership';
	        }            
	    }
	     
	    function editMemberships(celDiv,id) {
	    	jQuery(celDiv).html("<a href='<?php echo ENTRADA_URL; ?>/annualreport/education?section=edit_membership&amp;rid="+id+"' style=\"cursor: pointer; cursor: hand\" text-decoration: none><img src=\"<?php echo ENTRADA_RELATIVE; ?>/images/action-edit.gif\" style=\"border: none\"/></a>");
	    }
	    
	    function deleteMemberships(com,grid) {
		    if (com=='Delete Selected') {
		    	jQuery(function() {
		    		var error = "false";
					if(jQuery('.trSelected',grid).length>0) {
			    		// a workaround for a flaw in the demo system (http://dev.jqueryui.com/ticket/4375), ignore!
						jQuery("#dialog-confirm").dialog("destroy");
						jQuery('.trSelected', grid).each(function() {  
	               			var reportYear = jQuery(this).find('div')[3].textContent;
							if(reportYear < <?php echo $AR_CUR_YEAR;?>) {
		               			// Do not allow the deletion of years that are prior to the current reporting year.
		               			error = "true";
							}
						});
						
						if(error == "false") {
							// allow deletion
							jQuery("#dialog-confirm").dialog({
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
						               url: '<?php echo ENTRADA_URL; ?>/api/ar_delete.api.php?id=<?php echo $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]; ?>&t=<?php echo $fields; ?>&rid='+ids
						             });
							       	
							       	window.setTimeout('memberships_grid.flexReload()', 1000);
									jQuery(this).dialog('close');
								},
								Cancel: function() {
									jQuery(this).dialog('close');
								}
							}
							});
						} else {
							jQueryError.dialog('open');
						}
			    	} else {
				    	jQuerydialog.dialog('open');
			    	}
				});
		    }
		}
		
		<?php 
		if($_SESSION["details"]["clinical_member"]) {
			$fields = "ar_clinical_education,clinical_education_id,level,description,location,year_reported";?>
			clinical_education_grid = jQuery("#flex7").flexigrid
			(
				{
				url: '<?php echo ENTRADA_URL; ?>/api/ar_loadgrid.api.php?id=<?php echo $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]; ?>&t=<?php echo $fields; ?>',
				dataType: 'json',
				method: 'POST',
				colModel : [
					{display: 'Level', name : 'level', width : 100, sortable : true, align: 'left'},
					{display: 'Description', name : 'description', width : 255, sortable : true, align: 'left'},
					{display: 'Location', name : 'location', width : 209, sortable : true, align: 'left'},
					{display: 'Year', name : 'year_reported', width : 50, sortable : true, align: 'left'},
					{display: 'Edit', name : 'ctled', width : 25,  sortable : false, align: 'center', process:editClinicalEducation}
					],
				searchitems : [
					{display: 'Level', name : 'level'},
					{display: 'Description', name : 'description'},
					{display: 'Location', name : 'location'},
					{display: 'Year', name : 'year_reported', isdefault: true}
					],
				sortname: "year_reported",
				sortorder: "desc",
				resizable: false, 
				usepager: true,
				showToggleBtn: false,
				collapseTable: <?php echo ($_SESSION["education_expand_grid"] == "clinical_education_grid" ? "false" : "true"); ?>,
				title: 'G. Education of Clinical Trainees Including Clinical Clerks',
				useRp: true,
				rp: 15,
				showTableToggleBtn: true,
				width: 732,
				height: 200,
				nomsg: 'No Results', 
				buttons : [
	                {name: 'Add', bclass: 'add', onpress : addClinicalEducation},
	                {separator: true}, 
	                {name: 'Delete Selected', bclass: 'delete', onpress : deleteClinicalEducation}
	                ]
				}
			);
				
			function addClinicalEducation(com,grid) {
	            if (com=='Add') {
	                 window.location='<?php echo ENTRADA_URL; ?>/annualreport/education?section=add_clinical';
	            }            
	        }
	         
	        function editClinicalEducation(celDiv,id) {
	        	jQuery(celDiv).html("<a href='<?php echo ENTRADA_URL; ?>/annualreport/education?section=edit_clinical&amp;rid="+id+"' style=\"cursor: pointer; cursor: hand\" text-decoration: none><img src=\"<?php echo ENTRADA_RELATIVE; ?>/images/action-edit.gif\" style=\"border: none\"/></a>");
		    }
		    
		    function deleteClinicalEducation(com,grid) {
			    if (com=='Delete Selected') {
			    	jQuery(function() {
			    		var error = "false";
						if(jQuery('.trSelected',grid).length>0) {
				    		// a workaround for a flaw in the demo system (http://dev.jqueryui.com/ticket/4375), ignore!
							jQuery("#dialog-confirm").dialog("destroy");
							jQuery('.trSelected', grid).each(function() {  
		               			var reportYear = jQuery(this).find('div')[3].textContent;
								if(reportYear < <?php echo $AR_CUR_YEAR;?>) {
			               			// Do not allow the deletion of years that are prior to the current reporting year.
			               			error = "true";
								}
							});
							
							if(error == "false") {
								// allow deletion
								jQuery("#dialog-confirm").dialog({
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
							               url: '<?php echo ENTRADA_URL; ?>/api/ar_delete.api.php?id=<?php echo $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]; ?>&t=<?php echo $fields; ?>&rid='+ids
							             });
								       	
								       	window.setTimeout('clinical_education_grid.flexReload()', 1000);
										jQuery(this).dialog('close');
									},
									Cancel: function() {
										jQuery(this).dialog('close');
									}
								}
								});
							} else {
								jQueryError.dialog('open');
							}
				    	} else {
					    	jQuerydialog.dialog('open');
				    	}
					});
			    }
			}
			
		<?php 
		}
		$fields = "ar_continuing_education,continuing_education_id,unit,description,location,year_reported"; ?>
		continuing_education_grid = jQuery("#flex8").flexigrid
		(
			{
			url: '<?php echo ENTRADA_URL; ?>/api/ar_loadgrid.api.php?id=<?php echo $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]; ?>&t=<?php echo $fields; ?>',
			dataType: 'json',
			method: 'POST',
			colModel : [
				{display: 'Unit', name : 'unit', width : 100, sortable : true, align: 'left'},
				{display: 'Description', name : 'description', width : 255, sortable : true, align: 'left'},
				{display: 'Location', name : 'location', width : 209, sortable : true, align: 'left'},
				{display: 'Year', name : 'year_reported', width : 50, sortable : true, align: 'left'},
				{display: 'Edit', name : 'ctled', width : 25,  sortable : false, align: 'center', process:editContinuingEducation}
				],
			searchitems : [
				{display: 'Unit', name : 'unit'},
				{display: 'Description', name : 'description'},
				{display: 'Location', name : 'location'},
				{display: 'Year', name : 'year_reported', isdefault: true}
				],
			sortname: "year_reported",
			sortorder: "desc",
			resizable: false, 
			usepager: true,
			showToggleBtn: false,
			collapseTable: <?php echo ($_SESSION["education_expand_grid"] == "continuing_education_grid" ? "false" : "true"); ?>,
			title: 'H. Continuing Education Under The Aegis of Queen\'s',
			useRp: true,
			rp: 15,
			showTableToggleBtn: true,
			width: 732,
			height: 200,
			nomsg: 'No Results', 
			buttons : [
	            {name: 'Add', bclass: 'add', onpress : addContinuingEducation},
	            {separator: true}, 
	            {name: 'Delete Selected', bclass: 'delete', onpress : deleteContinuingEducation}
	            ]
			}
		);
			
		function addContinuingEducation(com,grid) {
	        if (com=='Add') {
	             window.location='<?php echo ENTRADA_URL; ?>/annualreport/education?section=add_continuing';
	        }            
	    }
	     
	    function editContinuingEducation(celDiv,id) {
	    	jQuery(celDiv).html("<a href='<?php echo ENTRADA_URL; ?>/annualreport/education?section=edit_continuing&amp;rid="+id+"' style=\"cursor: pointer; cursor: hand\" text-decoration: none><img src=\"<?php echo ENTRADA_RELATIVE; ?>/images/action-edit.gif\" style=\"border: none\"/></a>");
	    }
	    
	    function deleteContinuingEducation(com,grid) {
		    if (com=='Delete Selected') {
		    	jQuery(function() {
		    		var error = "false";
					if(jQuery('.trSelected',grid).length>0) {
			    		// a workaround for a flaw in the demo system (http://dev.jqueryui.com/ticket/4375), ignore!
						jQuery("#dialog-confirm").dialog("destroy");
						jQuery('.trSelected', grid).each(function() {  
	               			var reportYear = jQuery(this).find('div')[3].textContent;
							if(reportYear < <?php echo $AR_CUR_YEAR;?>) {
		               			// Do not allow the deletion of years that are prior to the current reporting year.
		               			error = "true";
							}
						});
						
						if(error == "false") {
							// allow deletion
							jQuery("#dialog-confirm").dialog({
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
						               url: '<?php echo ENTRADA_URL; ?>/api/ar_delete.api.php?id=<?php echo $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]; ?>&t=<?php echo $fields; ?>&rid='+ids
						             });
							       	
							       	window.setTimeout('continuing_education_grid.flexReload()', 1000);
									jQuery(this).dialog('close');
								},
								Cancel: function() {
									jQuery(this).dialog('close');
								}
							}
							});
						} else {
							jQueryError.dialog('open');
						}
			    	} else {
				    	jQuerydialog.dialog('open');
			    	}
				});
		    }
		}
		
		<?php $fields = "ar_innovation,innovation_id,course_number,course_name,type,year_reported"; ?>
		innovation_grid = jQuery("#flex9").flexigrid
		(
			{
			url: '<?php echo ENTRADA_URL; ?>/api/ar_loadgrid.api.php?id=<?php echo $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]; ?>&t=<?php echo $fields; ?>',
			dataType: 'json',
			method: 'POST',
			colModel : [
				{display: 'Course Code', name : 'course_number', width : 100, sortable : true, align: 'left'},
				{display: 'Course', name : 'course_name', width : 320, sortable : true, align: 'left'},
				{display: 'Type', name : 'type', width : 144, sortable : true, align: 'left'},
				{display: 'Year', name : 'year_reported', width : 50, sortable : true, align: 'left'},
				{display: 'Edit', name : 'ctled', width : 25,  sortable : false, align: 'center', process:editInnovation}
				],
			searchitems : [
				{display: 'Course Code', name : 'course_number'},
				{display: 'Course', name : 'course_name'},
				{display: 'Type', name : 'type'},
				{display: 'Year', name : 'year_reported', isdefault: true}
				],
			sortname: "year_reported",
			sortorder: "desc",
			resizable: false, 
			usepager: true,
			showToggleBtn: false,
			collapseTable: <?php echo ($_SESSION["education_expand_grid"] == "innovation_grid" ? "false" : "true"); ?>,
			title: '<?php echo (!$_SESSION["details"]["clinical_member"] ? "I. " : "G. "); ?>Innovation in Education',
			useRp: true,
			rp: 15,
			showTableToggleBtn: true,
			width: 732,
			height: 200,
			nomsg: 'No Results', 
			buttons : [
	            {name: 'Add', bclass: 'add', onpress : addInnovation},
	            {separator: true}, 
	            {name: 'Delete Selected', bclass: 'delete', onpress : deleteInnovation}
	            ]
			}
		);
			
		function addInnovation(com,grid) {
	        if (com=='Add') {
	             window.location='<?php echo ENTRADA_URL; ?>/annualreport/education?section=add_innovation';
	        }            
	    }
	     
	    function editInnovation(celDiv,id) {
	    	jQuery(celDiv).html("<a href='<?php echo ENTRADA_URL; ?>/annualreport/education?section=edit_innovation&amp;rid="+id+"' style=\"cursor: pointer; cursor: hand\" text-decoration: none><img src=\"<?php echo ENTRADA_RELATIVE; ?>/images/action-edit.gif\" style=\"border: none\"/></a>");
	    }
	    
	    function deleteInnovation(com,grid) {
		    if (com=='Delete Selected') {
		    	jQuery(function() {
		    		var error = "false";
					if(jQuery('.trSelected',grid).length>0) {
			    		// a workaround for a flaw in the demo system (http://dev.jqueryui.com/ticket/4375), ignore!
						jQuery("#dialog-confirm").dialog("destroy");
						jQuery('.trSelected', grid).each(function() {  
	               			var reportYear = jQuery(this).find('div')[3].textContent;
							if(reportYear < <?php echo $AR_CUR_YEAR;?>) {
		               			// Do not allow the deletion of years that are prior to the current reporting year.
		               			error = "true";
							}
						});
						
						if(error == "false") {
							// allow deletion
							jQuery("#dialog-confirm").dialog({
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
						               url: '<?php echo ENTRADA_URL; ?>/api/ar_delete.api.php?id=<?php echo $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]; ?>&t=<?php echo $fields; ?>&rid='+ids
						             });
							       	
							       	window.setTimeout('innovation_grid.flexReload()', 1000);
									jQuery(this).dialog('close');
								},
								Cancel: function() {
									jQuery(this).dialog('close');
								}
							}
							});
						} else {
							jQueryError.dialog('open');
						}
			    	} else {
				    	jQuerydialog.dialog('open');
			    	}
				});
		    }
		}
		
		<?php $fields = "ar_other,other_id,description,course_name,type,year_reported"; ?>
		other_grid = jQuery("#flex10").flexigrid
		(
			{
			url: '<?php echo ENTRADA_URL; ?>/api/ar_loadgrid.api.php?id=<?php echo $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]; ?>&t=<?php echo $fields; ?>',
			dataType: 'json',
			method: 'POST',
			colModel : [
				{display: 'Description', name : 'description', width : 214, sortable : true, align: 'left'},
				{display: 'Course', name : 'course_name', width : 270, sortable : true, align: 'left'},
				{display: 'Type', name : 'type', width : 80, sortable : true, align: 'left'},
				{display: 'Year', name : 'year_reported', width : 50, sortable : true, align: 'left'},
				{display: 'Edit', name : 'ctled', width : 25,  sortable : false, align: 'center', process:editOther}
				],
			searchitems : [
				{display: 'Description', name : 'description'},
				{display: 'Course', name : 'course_name'},
				{display: 'Type', name : 'type'},
				{display: 'Year', name : 'year_reported', isdefault: true}
				],
			sortname: "year_reported",
			sortorder: "desc",
			resizable: false, 
			usepager: true,
			showToggleBtn: false,
			collapseTable: <?php echo ($_SESSION["education_expand_grid"] == "other_grid" ? "false" : "true"); ?>,
			title: '<?php echo (!$_SESSION["details"]["clinical_member"] ? "J. " : "H. "); ?>Other Education',
			useRp: true,
			rp: 15,
			showTableToggleBtn: true,
			width: 732,
			height: 200,
			nomsg: 'No Results', 
			buttons : [
	            {name: 'Add', bclass: 'add', onpress : addOther},
	            {separator: true}, 
	            {name: 'Delete Selected', bclass: 'delete', onpress : deleteOther}
	            ]
			}
		);
			
		function addOther(com,grid) {
	        if (com=='Add') {
	             window.location='<?php echo ENTRADA_URL; ?>/annualreport/education?section=add_other';
	        }            
	    }
	     
	    function editOther(celDiv,id) {
	    	jQuery(celDiv).html("<a href='<?php echo ENTRADA_URL; ?>/annualreport/education?section=edit_other&amp;rid="+id+"' style=\"cursor: pointer; cursor: hand\" text-decoration: none><img src=\"<?php echo ENTRADA_RELATIVE; ?>/images/action-edit.gif\" style=\"border: none\"/></a>");
	    }
	    
	    function deleteOther(com,grid) {
		    if (com=='Delete Selected') {
		    	jQuery(function() {
		    		var error = "false";
					if(jQuery('.trSelected',grid).length>0) {
			    		// a workaround for a flaw in the demo system (http://dev.jqueryui.com/ticket/4375), ignore!
						jQuery("#dialog-confirm").dialog("destroy");
						jQuery('.trSelected', grid).each(function() {  
	               			var reportYear = jQuery(this).find('div')[3].textContent;
							if(reportYear < <?php echo $AR_CUR_YEAR;?>) {
		               			// Do not allow the deletion of years that are prior to the current reporting year.
		               			error = "true";
							}
						});
						
						if(error == "false") {
							// allow deletion
							jQuery("#dialog-confirm").dialog({
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
						               url: '<?php echo ENTRADA_URL; ?>/api/ar_delete.api.php?id=<?php echo $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]; ?>&t=<?php echo $fields; ?>&rid='+ids
						             });
							       	
							       	window.setTimeout('other_grid.flexReload()', 1000);
									jQuery(this).dialog('close');
								},
								Cancel: function() {
									jQuery(this).dialog('close');
								}
							}
							});
						} else {
							jQueryError.dialog('open');
						}
			    	} else {
				    	jQuerydialog.dialog('open');
			    	}
				});
		    }
		}
	});
	</script>
	<div id="dialog-confirm" title="Delete?" style="display: none">
		<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>These items will be permanently deleted and cannot be recovered. Are you sure?</p>
	</div>

	<?php
}