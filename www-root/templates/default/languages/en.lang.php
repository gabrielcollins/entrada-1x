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
	 * Navigation
	 */
	"navigation_tabs" => array (
		"public" => array (
			"dashboard" => array ("title" => "Dashboard"),
			"communities" => array ("title" => "Communities"),
			"curriculum/search" => array (
				"title" => "Curriculum",
				"children" => array (
					"curriculum/search" => array (
						"title" => "Curriculum Search"
					),
					"curriculum/explorer" => array (
						"title" => "Curriculum Explorer"
					)
				)
			),
			"courses" => array ("title" => "Courses"),
			"events" => array ("title" => "Learning Events"),
			"logbook" => array ("title" => "Logbook", "resource" => "encounter_tracking", "permission" => "read"),
			"clerkship" => array ("title" => "Clerkship", "resource" => "clerkship", "permission" => "read"),
			"people" => array ("title" => "People Search"),
			"annualreport" => array ("title" => "My Annual Report", "resource" => "annualreport", "permission" => "read")
		),
		"admin" => array (
			"observerships" => array ("title" => "Manage Observerships")
		)
	),

	/**
	 * Global terminology used across different Entrada modules.
	 */
    "Organisation" => "Organisation",
    "Organisations" => "Organisations",
    "My Organisations" => "My Organisations",
    "Give Feedback!" => "Give Feedback!",
    "Quick Polls" => "Quick Polls",
	"Message Center" => "Message Center",
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
     * Events Module
     */
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
	 * Dashboard Module
	 */
    "public_dashboard_feeds" => array (
		"global" => array (
			array ("title" => "Entrada Project", "url" => "http://www.entrada-project.org/feed/", "removable" => false),
			array ("title" => "Zend Developer Zone", "url" => "http://feeds.feedburner.com/PHPDevZone", "removable" => true),
			array ("title" => "Insider Medicine", "url" => "http://insidermedicine.ca/xml/Patient/insidermedicine_English.xml", "removable" => true),
			array ("title" => "Google News Top Stories", "url" => "https://news.google.ca/news/feeds?pz=1&cf=all&ned=ca&hl=en&output=rss", "removable" => true)
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
			array ("title" => "School Library", "url" => ENTRADA_URL."/library", "target" => "_blank"),
			array ("title" => "Insider Medicine", "url" => "http://insidermedicine.ca", "target" => "_blank"),
			array ("title" => "Zend Developer Zone", "url" => "http://devzone.zend.com", "target" => "_blank"),
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
	 * Communities Module
	 */
    "public_communities_heading_line" => "Creating a <strong>new community</strong> in the <strong>Entrada Community System</strong> gives you a <strong>place to connect</strong> on-line.",
    "public_communities_title" => "Entrada Communities",
    "breadcrumb_communities_title"=> "Entrada Communities",
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
	 * MSPR Module
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

	/**
     * Courses Module
     */
	"course" => "Course",
	"courses" => "Courses",
	"evaluation_filtered_words" => "Dr. Doctor; Firstname Lastname",

	/*
	 * Curriculum Explorer
	 */
	"curriculum_explorer" => array(
		"badge-success" => "0.3",
		"badge-warning" => "0.1",
		"badge-important" => "0.05"
	)
);
