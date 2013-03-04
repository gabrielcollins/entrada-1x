<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * This is the template for the default English language file for Entrada.
 * 
 * @author Organisation: University of Calgary
 * @author Unit: Faculty of Veterinary Medicine
 * @author Developer: Szemir Khangyi <skhangyi@ucalgary.ca>
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2008 University of Calgary. All Rights Reserved.
 * 
*/
global $AGENT_CONTACTS;
return array (
	/**
	 * Core Navigation
	 */
	"navigation_tabs" => array (
		"public" => array (
			"dashboard" => array ("title" => "Dashboard"),
			"communities" => array ("title" => "Communities"),
			"courses" => array ("title" => "Courses"),
			"events" => array ("title" => "Learning Events"),
			"clerkship" => array ("title" => "Clerkship", "resource" => "clerkship", "permission" => "read"),
			"search" => array ("title" => "Curriculum Search"),
//			"curriculum" => array (
//				"title" => "Curriculum",
//				"children" => array (
//					"curriculum/overview" => array (
//						"title" => "Overview"
//					),
//					"curriculum/search" => array (
//						"title" => "Search"
//					),
//					"curriculum/objectives" => array (
//						"title" => "Objective Map"
//					)
//				)
//			),
			"people" => array ("title" => "People Search"),
			"evaluations" => array ("title" => "My Evaluations"),
			"tasks" => array ("title" => "My Tasks", "resource" => "tasktab", "permission" => "read"),
			"annualreport" => array ("title" => "My Annual Report", "resource" => "annualreport", "permission" => "read"),
			"profile" => array ("title" => "My Profile"),
			"library" => array ("title" => "Library", "target" => "_blank"),
			"help" => array ("title" => "Help")
		),
/*		@todo This is not currently used, unfortunately this exists in core/includes/settings.php in the $MODULES array.
		We need to fix this in order to allow it to work from here. The use of the $MODULES array is silly,
		and I believe this is something that should be handled by the ACL or is just plain unnecessary.

		"admin" => array (
			"awards" => array ("title" => "Manage Awards", "resource" => "awards", "permission" => "update"),
			"clerkship" => array ("title" => "Manage Clerkship", "resource" => "clerkship", "permission" => "update"),
			"courses" => array ("title" => "Manage Courses", "resource"=> "coursecontent", "permission" => "update"),
			"evaluations" => array ("title" => "Manage Evaluations", "resource" => "evaluation", "permission" => "update"),
			"communities" => array ("title" => "Manage Communities", "resource" => "community", "permission" => "update"),
			"groups" => array ("title" => "Manage Groups", "resource" => "group", "permission" => "update"),
			"events" => array ("title" => "Manage Events", "resource" => "eventcontent", "permission" => "update"),
			"gradebook" => array ("title" => "Manage Gradebook", "resource" => "gradebook", "permission" => "update"),
			"tasks" => array ("title" => "Manage Tasks", "resource" => "task", "permission" => "create"), 
			"notices" => array ("title" => "Manage Notices", "resource" => "notice", "permission" => "update"),
			"configuration" => array ("title" => "Manage Configuration", "resource" => "configuration", "permission" => "update"),
			"objectives" => array ("title" => "Manage Objectives", "resource" => "objective", "permission" => "update"),
			"observerships" => array ("title" => "Manage Observerships", "resource" => "observerships", "permission" => "update"),
			"polls" => array ("title" => "Manage Polls", "resource" => "poll", "permission" => "update"),
			"quizzes" => array ("title" => "Manage Quizzes", "resource" => "quiz", "permission" => "update"),
			"users" => array ("title" => "Manage Users", "resource" => "user", "permission" => "update"),
			"regionaled" => array ("title" => "Regional Education", "resource" => "regionaled", "permission" => "update"),
			"reports" => array ("title" => "System Reports", "resource" => "reportindex", "permission" => "read"),
			"annualreport" => array ("title" => "Annual Reports", "resource" => "annualreportadmin", "permission" => "read")			
		)
*/
	),
	
	"events_filter_controls" => array (
		"teacher" => array (
			"label" => "Teacher Filters"
		),
		"student" => array (
			"label" => "Student Filters"
		),
		"group" => array (
			"label" => "Cohort Filters"
		),
		"course" => array (
			"label" => "Course Filters"
		),
		"term" => array (
			"label" => "Term Filters"
		),
		"eventtype" => array (
			"label" => "Learning Event Type Filters"
		),
		"cp" => array (
			"label" => "Clinical Presentation Filters",
			"global_lu_objectives_name" => "MCC Presentations"
		),
		"co" => array (
			"label" => "Curriculum Objective Filters",
			"global_lu_objectives_name" => "Curriculum Objectives"
		),
		"topic" => array (
			"label" => "Hot Topic Filters"
		),
		"department" => array (
			"label" => "Department Filters"
		),
	),
	
	/**
	 * Global terminology used across different Entrada modules.
	 */
    "global_button_save" => "Save",
    "global_button_cancel" => "Cancel",
    "global_button_proceed" => "Proceed",

	"global_feedback_widget" => array(
		"global" => array(
			"system"		=> array(
				"link-text" => APPLICATION_NAME." Feedback",
				"link-desc" => "Please share any feedback you may have about this page.",
				"form"		=> array(
					"title" => "Feedback about ".APPLICATION_NAME,
					"description" => "This form is provided so you can efficiently provide our developers with important feedback regarding this application. Whether you are reporting a bug, feature request or just general feedback, all messages are important to us and appreciated.<br /><br />
									<span class=\"content-small\">Please note: If you are submitting a bug or problem, please try to be specific as to the issue. If possible also let us know how to recreate the problem.</span>",
					"anon"	=> false,
					"recipients" => array(
						$AGENT_CONTACTS["administrator"]["email"] => $AGENT_CONTACTS["administrator"]["name"]
					)
				)
			)
		)
	),
	
	/**
	 * Public Dashboard Module
	 * modules/public/dashboard
	 */
    "public_dashboard_feeds" => array (
		"global" => array (
			array ("title" => "Entrada Announcement Feed", "url" => "http://www.entrada-project.org/news/feed", "removable" => false),
			array ("title" => "Zend DevZone", "url" => "http://feeds.feedburner.com/PHPDevZone", "removable" => true),
			array ("title" => "Insider Medicine", "url" => "http://insidermedicine.ca/xml/Patient/insidermedicine_English.xml", "removable" => true),
			array ("title" => "Google News Top Stories", "url" => "http://news.google.com/news?pz=1&cf=all&ned=ca&hl=en&topic=h&num=3&output=rss", "removable" => true)
		),
		"medtech" => array (
			// array ("title" => "Admin Feed Example", "url" => "http://www.yourschool.ca/admin.rss", "removable" => false)
		),
		"student" => array (
			// array ("title" => "Student Feed Example", "url" => "http://www.yourschool.ca/student.rss", "removable" => false)
		),
		"alumni" => array (
			// array ("title" => "Student Feed Example", "url" => "http://www.yourschool.ca/student.rss", "removable" => false)
		),
		"faculty" => array (
			// array ("title" => "Faculty Feed Example", "url" => "http://www.yourschool.ca/faculty.rss", "removable" => false)
		),
		"resident" => array (
			// array ("title" => "Resident Feed Example", "url" => "http://www.yourschool.ca/resident.rss", "removable" => false)
		),
		"staff" => array (
			// array ("title" => "Staff Feed Example", "url" => "http://www.yourschool.ca/staff.rss", "removable" => false)
		)
	),
    "public_dashboard_links" => array (
		"global" => array (
			array ("title" => "Entrada Project", "url" => "http://www.entrada-project.org", "target" => "_blank"),
			array ("title" => "Public Calendar", "url" => ENTRADA_URL."/calendar", "target" => "_blank"),
			array ("title" => "School Library", "url" => ENTRADA_URL."/library", "target" => "_blank"),
			array ("title" => "HealthLibrary.ca", "url" => "http://www.healthlibrary.ca", "target" => "_blank")
		),
		"medtech" => array (
			// array ("title" => "Additional Admin Link", "url" => "http://admin.yourschool.ca")
		),
		"student" => array (
			// array ("title" => "Additional Student Link", "url" => "http://student.yourschool.ca")
		),
		"alumni" => array (
			// array ("title" => "Additional Alumni Link", "url" => "http://alumni.yourschool.ca")
		),
		"faculty" => array (
			// array ("title" => "Additional Faculty Link", "url" => "http://faculty.yourschool.ca")
		),
		"resident" => array (
			// array ("title" => "Additional Resident Link", "url" => "http://resident.yourschool.ca")
		),
		"staff" => array (
			// array ("title" => "Additional Staff Link", "url" => "http://staff.yourschool.ca")
		)
	),
    "public_dashboard_title_medtech" => "MEdTech Dashboard",
    "public_dashboard_title_student" => "Student Dashboard",
    "public_dashboard_title_alumni" => "Alumni Dashboard",
    "public_dashboard_title_faculty" => "Faculty Dashboard",
    "public_dashboard_title_resident" => "Resident Dashboard",
    "public_dashboard_title_staff" => "Staff Dashboard",
    "public_dashboard_block_weather" => "Weather Forecast",
    "public_dashboard_block_community" => "My Communities",

	/**
	 * Public Communities Module
	 * modules/public/communities
	 */
    "public_communities_heading_line" => "Creating a <strong>new community</strong> in the <strong>Entrada<br />Community System</strong> gives you a <strong>place to connect</strong> on-line.",
    "public_communities_title" => "Entrada Communities",
    "breadcrumb_communities_title"=> "Entrada Communities",
    
	/**
	 * Community System History Strings
	 * community
	 */
    "community_history_add_announcement" => "A new announcement (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been added.",
    "community_history_edit_announcement" => "Announcement (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been updated.",
    "community_history_add_forum" => "A new discussion forum (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been added.",
    "community_history_add_post" => "A new discussion post (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-post&id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been added.",
    "community_history_edit_forum" => "Discussion forum (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been updated.",
    "community_history_edit_post" => "Discussion post (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-post&id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been updated.",
    "community_history_edit_reply" => "Discussion post #%RECORD_ID% of (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-post&id=%PARENT_ID%#post-%RECORD_ID%\">%RECORD_TITLE%</a>) has been updated.",
    "community_history_add_reply" => "Discussion post (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-post&id=%PARENT_ID%#post-%RECORD_ID%\">%RECORD_TITLE%</a>) was replied to.",
    "community_history_add_event" => "A new event (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been added.",
    "community_history_edit_event" => "Event (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been updated.",
	"community_history_add_event" => "A new event (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been added.",
    "community_history_edit_event" => "Event (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been updated.",
	"community_history_add_learning_event" => "A new learning event (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been added.",
    "community_history_edit_learning_event" => "Learning Event (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been updated.",
    "community_history_add_photo_comment" => "New comment on (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-photo&id=%PARENT_ID%\">%RECORD_TITLE%</a>) photo.",
    "community_history_add_gallery" => "A new photo gallery (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been added.",
    "community_history_add_photo" => "A new photo (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-photo&id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been added.",
    "community_history_edit_photo_comment" => "Comment on (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-photo&id=%PARENT_ID%\">%RECORD_TITLE%</a>) updated.",
    "community_history_edit_gallery" => "Photo gallery (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-photo&id=%RECORD_ID%\">%RECORD_TITLE%</a>) updated.",
    "community_history_edit_photo" => "Photo (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-photo&id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been updated.",
    "community_history_add_page" => "A new page (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%\">%RECORD_TITLE%</a>) has been created.",
    "community_history_edit_page" => "Page (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%\">%RECORD_TITLE%</a>) has been updated.",
    "community_history_edit_home_page" => "<a href=\"%SITE_COMMUNITY_URL%\">Home page</a> has been updated.",
    "community_history_add_poll" => "A new poll (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been added.",
    "community_history_edit_poll" => "Poll (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been updated.",
    "community_history_add_file_comment" => "New comment on (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-file&id=%PARENT_ID%\">%RECORD_TITLE%</a>) file.",
    "community_history_add_file" => "A new file (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-file&id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been uploaded.",
    "community_history_add_share" => "A new shared folder (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been added.",
    "community_history_add_file_revision" => "A new revision of (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-file&id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been uploaded.",
    "community_history_edit_file_comment" => "Comment on (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-file&id=%PARENT_ID%\">%RECORD_TITLE%</a>) updated.",
    "community_history_edit_file" => "File (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-file&id=%RECORD_ID%\">%RECORD_TITLE%</a>) had been updated.",
    "community_history_edit_share" => "Shared folder (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been updated.",
    "community_history_create_moderated_community" => "Community (<a href=\"%SITE_COMMUNITY_URL%\">%RECORD_TITLE%</a>) has been created, but is waiting for administrator approval.",
    "community_history_create_active_community" => "Community (<a href=\"%SITE_COMMUNITY_URL%\">%RECORD_TITLE%</a>) has been created, and is now active.",
    "community_history_add_member" => "A new member (<a href=\"%SYS_PROFILE_URL%?id=%PROXY_ID%\">%RECORD_TITLE%</a>) has joined this community.",
    "community_history_add_members" => "%RECORD_ID% new member(s) added to the community.",
    "community_history_edit_community" => "The community profile was updated by <a href=\"%SYS_PROFILE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>.",
    "community_history_rename_community" => "Community is now known as <a href=\"%SITE_COMMUNITY_URL%\">%RECORD_TITLE%</a>",
    "community_history_activate_module" => "The <a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%\">%RECORD_TITLE%</a> module was activated for this community.",
    "community_history_move_file" => "The <a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-file&id=%RECORD_ID%\">%RECORD_TITLE%</a> file was moved to a different <a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-folder&id=%PARENT_ID%\">folder</a>.",
	"community_history_move_photo" => "The <a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-photo&id=%RECORD_ID%\">%RECORD_TITLE%</a> photo was moved to a different <a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-gallery&id=%PARENT_ID%\">gallery</a>.",
	
	/**
	 * mspr messages
	 */
	"mspr_no_entity" => "No Entity ID provided.",
	"mspr_invalid_entity" => "Item not found or invalid identifier provided",
	"mspr_no_action" => "No action requested.",
	"mspr_invalid_action" => "Invalid action requested for this item",
	"mspr_no_section" => "No MSPR section specified",
	"mspr_invalid_section" => "Invalid MSPR section specified",
	"mspr_no_comment" => "A comment is required and none was provided",
	"mspr_no_reject_reason" => "A reason for the rejection is required and none was provided",
	"mspr_invalid_user_info" => "Invalid user information provided",
	"mspr_no_details" => "Details are required and none were provided",
	"mspr_insufficient_info" => "Insufficient information provided.",
	"mspr_email_failed" => "Failed to send rejection email.",
	"mspr_observership_preceptor_required" => "A faculty preceptor must be selected or a non-faculty preceptor name entered.",
	"mspr_observership_invalid_dates" => "A valid start date is required.",
	"mspr_too_many_critical_enquiry" => "Cannot have more than one Critical Enquiry on MSPR. Please edit the existing project or remove it before adding a new one.",
	"mspr_too_many_community_based_project" => "Cannot have more than one Community-Based Project on MSPR. Please edit the existing project or remove it before adding a new one.",
	
	
	/*****************
	 * Tasks Module  *
	 *****************/
	
	/** Heading labels **/
	"task_heading_create" => "Create Task",
	"task_heading_edit" => "Edit Task",
	"task_heading_recipients" => "Task Recipients",
	"task_heading_completion_options" => "Task Completion Options",
	"task_heading_verification_options" => "Task Verification Options",
	"task_heading_time_release_options" => "Time Release Options",
	"task_heading_description" => "Task Description",
	
	/** field labels **/
	"task_field_title" => "Task Title",
	"task_field_deadline" => "Deadline",
	"task_field_time_required" => "Estimated Time Required",
	"task_field_course" => "Course",
	"task_field_associated_faculty" => "Associated Faculty", 
	"task_field_description" => "Task Description",
	"task_field_recipients_class" => "Entire Class Task",
	"task_field_cohort" => "Cohort",
	"task_field_recipients_students" => "Individual Student Task",
	"task_field_associated_students" => "Associated Students",
	"task_field_recipients_organisation" => "Entire Organisation Task",
	"task_field_organisation" => "Organisation",
	"task_field_completion_comments" => "Completion Comments",
	"task_field_rejection_comments" => "Rejection Comments",
	"task_field_faculty_selection" => "Faculty Selection",
	"task_field_verification_none" => "No Verification",
	"task_field_verification_faculty" => "Selected Faculty Verification",
	"task_field_verification_other" => "Other Specified Individual Verification",
	"task_field_verification_other_names" => "Designated Verifier",
	"task_field_notification_types" => "Notification Types", 
	"task_field_verification_notification_dashboard" => "Dashboard Notification [disabled]",
	"task_field_verification_notificaiton_email" => "Email Notification",
	"task_field_after_saving_options" => "After Saving:",
	 

	/** button labels **/
	"task_button_add" => "Add",
	"task_button_save" => "Save",
	"task_button_cancel" => "Cancel",

	/** instructions **/
	"task_instructions_recipients_class" => "This task is intended for an entire class",
	"task_instructions_recipients_students" => "This task is intended for a specific student or students",
	"task_instructions_recipients_organisation" => "This task is intended for every member of an organisation",
	"task_instructions_faculty_name" => "(<strong>Example:</strong> %MY_FULLNAME%)",	
	"task_instructions_associated_students" => "(<strong>Example:</strong> %MY_FULLNAME%)",
	"task_instructions_verification_other_names" => "(<strong>Example:</strong> %MY_FULLNAME%)",
	"task_instructions_verification_none" => "No external verification required. Task recipients assertion of completion functions as self-verification.",
	"task_instructions_verification_other" => "The individual specified will receive all verification requests (if applicable) and will be granted verification authority where they might not otherwise have it.",
	"task_instructions_verification_faculty" => "The selected associated faculty will receive verification requests (if applicable)",	

	/** option labels **/
	"task_option_complete_allow_comments" => "Allow comments",
	"task_option_complete_no_comments" => "Disable comments",
	"task_option_complete_require_comments" => "Require comments",
	"task_option_course_none" => "None",
	"task_option_faculty_selection_off" => "Off",
	"task_option_faculty_selection_allow" => "Allowed",
	"task_option_faculty_selection_require" => "Required",
	
	/** misc labels **/
	"task_misc_minutes" => "minutes",
	
	/** errors **/
	"task_title_too_short" => "The <strong>Task Title</strong> field is required.",
	"task_course_invalid" => "The <strong>Course</strong> you selected does not exist.",
	"task_course_permission_fail" => "You do not have permission to add a task for the course you selected. <br />Please re-select the course you would like to associate with this task.",
	"task_recipient_type_invalid" => "Unable to proceed because the <strong>Task Recipients</strong> type is unrecognized.",
	"task_verification_type_invalid" => "Unable to proceed because the <strong>Task Verification</strong> type is unrecognized.",
	"task_time_required_invalid" => "Invalid <strong>Time Required</strong> entered. Time Required must be empty or a non-negative number of minutes.",
	"task_time_required_too_long" => "Invalid <strong>Time Required</strong> entered. Time Required cannot be greater than %MAX_TIME_REQUIRED% minutes.",
	"task_recipient_individual_empty" => "You have chosen <strong>Individual Task</strong> as <strong>Task Recipients</strong> type, but have not selected any individuals.",
	"task_recipient_cohort_missing" => "You have chosen <strong>Entire Clss Task</strong> as <strong Task Recipients</strong> type, but have not selected a valid <strong>Graduating Year</strong>.",
	"task_no_faculty_and_faculty_verification" => "You have chosen <strong>Selected Faculty Verification</strong>, but have not designated any faculty in <strong>Associated Faculty</strong>.",
	"task_organisation_permission_fail" => "You do not have permission to add a task for the selected organisation, please select a different one.",
	"task_organisation_invalid" => "The <strong>Organisation</strong> you selected does not exist.",
	"task_verification_no_verifier" => "You have chosen <strong>Other Specified Individual Verifiction</strong>, but have not selected an individual as <strong>Designated Verifier</strong>.",
	"task_completion_comment_policy_invalid" => "Invalid completion comment policy provided. Please select one of the options from the list.",
	"task_rejection_comment_policy_invalid" => "Invalid rejection comment policy provided. Please select one of the options from the list.", 
	
	/** notices **/
	"task_title_too_long" => "The <strong>Task Title</strong> field has a maximum length of %MAX_LENGTH% characters. The title was truncated to accomodate this.", //note, the field has the same restriction, so the user is unlikely to receive this message

	/** courses **/
	"course" => "Course",
	"courses" => "Courses",
	"evaluation_filtered_words" => "Dr. Doctor; Firstname Lastname"
	
	);
?>