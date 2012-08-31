#!/usr/bin/php
<?php
/**
 * Entrada Tools [ http://www.entrada-project.org ]
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
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
 */

require_once("bootstrap.php");

//Configurable viewization parameters: ---------------------------------------------

// Tables to become views
$COMMUNITIES_TABLES = array("communities", 
							"communities_categories", 
							"communities_modules", 
							"communities_most_active", 
							"community_announcements",
							"community_courses",
							"community_discussions",
							"community_discussion_topics",
							"community_events",
							"community_galleries",
							"community_gallery_comments",
							"community_gallery_photos",
							"community_history",
							"community_mailing_lists",
							"community_mailing_list_members",
							"community_members",
							"community_modules",
							"community_notifications",
							"community_notify_members",
							"community_pages",
							"community_page_options",
							"community_permissions",
							"community_polls",
							"community_polls_access",
							"community_polls_questions",
							"community_polls_responses",
							"community_polls_results",
							"community_shares",
							"community_share_comments",
							"community_share_files",
							"community_share_file_versions",
							"cron_community_notifications");
							
// Database to which views should point
$MASTER_DATABASE = "entrada";

// Slave database in which views will be created that point to the master databae
$SLAVE_DATABASES = array("test_entrada_entrada", "nursing_entrada", "meds_entrada");

// End of parameters ----------------------------------------------------------------

foreach($SLAVE_DATABASES as $db_name) {
	$db->Connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, $db_name);
	foreach($COMMUNITIES_TABLES as $table) {
		echo "Dropping and viewizing $table on $db_name \n";
		$db->Execute("DROP TABLE `$table`;");		
		$db->Execute("DROP VIEW `$table`;");
		$db->Execute("CREATE VIEW `$table` AS SELECT * FROM `$MASTER_DATABASE`.`$table`;");
	}
}