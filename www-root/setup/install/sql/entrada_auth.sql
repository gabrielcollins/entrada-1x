SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `acl_permissions` (
  `permission_id` int(12) NOT NULL AUTO_INCREMENT,
  `resource_type` varchar(64) DEFAULT NULL,
  `resource_value` int(12) DEFAULT NULL,
  `entity_type` varchar(64) DEFAULT NULL,
  `entity_value` varchar(64) DEFAULT NULL,
  `app_id` int(12) NULL DEFAULT NULL,
  `create` tinyint(1) DEFAULT NULL,
  `read` tinyint(1) DEFAULT NULL,
  `update` tinyint(1) DEFAULT NULL,
  `delete` tinyint(1) DEFAULT NULL,
  `assertion` varchar(50) DEFAULT NULL,
  PRIMARY KEY  (`permission_id`),
  KEY `entity_type` (`entity_type`,`entity_value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `acl_permissions` (`resource_type`, `resource_value`, `entity_type`, `entity_value`, `app_id`, `create`, `read`, `update`, `delete`, `assertion`) VALUES
('community', NULL, NULL, NULL, 1, 1, 1, NULL, NULL, 'NotGuest'),
('course', NULL, 'group', 'student', 1, NULL, 0, NULL, NULL, 'CourseEnrollment'),
('course', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'ResourceOrganisation&NotGuest'),
('dashboard', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest'),
('discussion', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest'),
('library', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest'),
('people', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest'),
('podcast', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest'),
('profile', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest'),
('search', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest'),
('event', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'ResourceOrganisation&NotGuest'),
('resourceorganisation', 1, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest'),
('coursecontent', NULL, 'role', 'pcoordinator', 1, NULL, NULL, 1, NULL, 'CourseOwner'),
('evaluation', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL),
('evaluationform', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL),
('evaluationformquestion', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL),
('event', NULL, 'role', 'pcoordinator', 1, 1, NULL, NULL, NULL, 'CourseOwner'),
('event', NULL, 'role', 'pcoordinator', 1, NULL, NULL, 1, 1, 'EventOwner'),
('event', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'EventEnrollment&NotGuest'),
('event', NULL, 'group', 'student', 1, NULL, 0, NULL, NULL, 'NotEventEnrollment'),
('eventcontent', NULL, 'role', 'pcoordinator', 1, NULL, NULL, 1, NULL, 'EventOwner'),
('coursecontent', NULL, 'role', 'director', 1, NULL, NULL, 1, NULL, 'CourseOwner'),
('coursecontent', NULL, 'role', 'lecturer', 1, NULL, NULL, 1, NULL, 'CourseOwner'),
('eventcontent', NULL, 'role', 'lecturer', 1, NULL, NULL, 1, NULL, 'EventOwner'),
('eventcontent', NULL, 'role', 'director', 1, NULL, NULL, 1, NULL, 'EventOwner'),
('eventcontent', NULL, 'group:role', 'faculty:admin', 1, NULL, NULL, 1, NULL, 'EventOwner'),
(NULL, NULL, 'group:role', 'medtech:admin', 1, 1, 1, 1, 1, NULL),
('notice', NULL, 'group:role', 'faculty:director', 1, 1, NULL, 1, 1, 'ResourceOrganisation'),
('notice', NULL, 'group:role', 'faculty:admin', 1, 1, NULL, 1, 1, 'ResourceOrganisation'),
('notice', NULL, 'group:role', 'staff:admin', 1, 1, NULL, 1, 1, 'ResourceOrganisation'),
('notice', NULL, 'group:role', 'staff:pcoordinator', 1, 1, NULL, 1, 1, 'ResourceOrganisation'),
('resourceorganisation', 1, 'organisation:group:role', '1:faculty:director', 1, 1, NULL, NULL, NULL, NULL),
('resourceorganisation', 1, 'organisation:group:role', '1:faculty:admin', 1, 1, NULL, NULL, NULL, NULL),
('resourceorganisation', 1, 'organisation:group:role', '1:staff:admin', 1, 1, NULL, NULL, NULL, NULL),
('resourceorganisation', 1, 'organisation:group:role', '1:staff:pcoordinator', 1, 1, NULL, NULL, NULL, NULL),
('resourceorganisation', NULL, NULL, NULL, 1, NULL, NULL, 0, NULL, NULL),
('poll', NULL, 'role', 'admin', 1, 1, NULL, 1, 1, NULL),
('poll', NULL, 'role', 'pcoordinator', 1, 1, NULL, 1, 1, NULL),
('quiz', NULL, 'group:role', 'faculty:director', 1, NULL, NULL, 1, 1, 'QuizOwner'),
('firstlogin', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, NULL),
('community', NULL, NULL, NULL, 1, NULL, NULL, 1, 1, 'CommunityOwner'),
('quiz', NULL, 'group:role', 'faculty:admin', 1, NULL, NULL, 1, 1, 'QuizOwner'),
('quiz', NULL, 'group:role', 'faculty:lecturer', 1, NULL, NULL, 1, 1, 'QuizOwner'),
('quiz', NULL, 'group:role', 'resident:lecturer', 1, NULL, NULL, 1, 1, 'QuizOwner'),
('quiz', NULL, 'group:role', 'staff:admin', 1, NULL, NULL, 1, 1, 'QuizOwner'),
('quiz', NULL, 'group:role', 'staff:pcoordinator', 1, NULL, NULL, 1, 1, 'QuizOwner'),
(NULL, NULL, 'group:role', 'guest:communityinvite', 1, 0, 0, 0, 0, NULL),
('clerkship', NULL, 'group', 'student', 1, NULL, 1, NULL, NULL, 'Clerkship'),
('clerkship', NULL, 'group', 'staff', 1, NULL, 1, NULL, NULL, NULL),
('clerkship', NULL, 'group', 'faculty', 1, NULL, 1, NULL, NULL, NULL),
(NULL, NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, 'ResourceOrganisation'),
('resourceorganisation', 1, 'organisation:group:role', '1:staff:admin', 1, 1, 1, 1, 1, NULL),
('clerkship', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL),
('clerkship', NULL, 'group:role', 'faculty:clerkship', 1, 1, 1, 1, 1, NULL),
('quiz', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest'),
('quiz', NULL, 'group', 'faculty', 1, 1, NULL, NULL, NULL, NULL),
('quiz', NULL, 'group', 'staff', 1, 1, NULL, NULL, NULL, NULL),
('quiz', NULL, 'group:role', 'resident:lecturer', 1, 1, NULL, NULL, NULL, NULL),
('photo', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'Photo'),
('photo', NULL, 'group', 'faculty', 1, NULL, 1, NULL, NULL, NULL),
('photo', NULL, 'group', 'staff', 1, NULL, 1, NULL, NULL, NULL),
('clerkshipschedules', NULL, 'group', 'faculty', 1, NULL, 1, NULL, NULL, NULL),
('clerkshipschedules', NULL, 'group', 'staff', 1, NULL, 1, NULL, NULL, NULL),
('reportindex', NULL, 'organisation:group:role', '1:staff:admin', 1, NULL, 1, NULL, NULL, NULL),
('report', NULL, 'organisation:group:role', '1:staff:admin', 1, NULL, 1, NULL, NULL, NULL),
('assistant_support', NULL, 'group:role', 'faculty:director', 1, 1, 1, 1, 1, NULL),
('assistant_support', NULL, 'group:role', 'faculty:clerkship', 1, 1, 1, 1, 1, NULL),
('assistant_support', NULL, 'group:role', 'faculty:admin', 1, 1, 1, 1, 1, NULL),
('assistant_support', NULL, 'group:role', 'faculty:lecturer', 1, 1, 1, 1, 1, NULL),
('assistant_support', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL),
('assistant_support', NULL, 'group:role', 'staff:pcoordinator', 1, 1, 1, 1, 1, NULL),
('lottery', NULL, 'group', 'student', 1, NULL, 1, NULL, NULL, 'ClerkshipLottery'),
('lottery', NULL, 'group:role', 'staff:admin', 1, NULL, 1, NULL, NULL, NULL),
('lottery', NULL, 'group:role', 'faculty:director', 1, NULL, 1, NULL, NULL, NULL),
('logbook', NULL, 'group:role', 'staff:pcoordinator', 1, NULL, 1, 1, NULL, NULL),
('annualreport', NULL, 'group', 'faculty', 1, 1, 1, 1, 1, NULL),
('gradebook', NULL, 'role', 'pcoordinator', 1, NULL, 1, NULL, NULL, 'GradebookOwner'),
('gradebook', NULL, 'group:role', 'faculty:admin', 1, NULL, 1, NULL, NULL, 'GradebookOwner'),
('gradebook', NULL, 'group:role', 'faculty:director', 1, NULL, 1, NULL, NULL, 'GradebookOwner'),
('dashboard', NULL, NULL, NULL, 1, NULL, NULL, 1, NULL, 'NotGuest'),
('regionaled', NULL, 'group', 'resident', 1, NULL, 1, NULL, NULL, 'HasAccommodations'),
('regionaled', NULL, 'group', 'student', 1, NULL, 1, NULL, NULL, 'HasAccommodations'),
('regionaled_tab', NULL, 'group', 'resident', 1, NULL, 1, NULL, NULL, 'HasAccommodations'),
('awards', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL),
('mspr', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL),
('mspr', NULL, 'group', 'student', 1, NULL, 1, 1, NULL, NULL),
('user', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL),
('incident', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL),
('task', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, 'ResourceOrganisation'),
('task', NULL, 'group:role', 'faculty:director', 1, NULL, 1, 1, 1, 'TaskOwner'),
('task', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'TaskRecipient'),
('task', NULL, 'role', 'pcoordinator', 1, NULL, 1, 1, 1, 'TaskOwner'),
('task', NULL, 'group:role', 'faculty:director', 1, 1, NULL, NULL, NULL, 'CourseOwner'),
('task', NULL, 'role', 'pcoordinator', 1, 1, NULL, NULL, NULL,'CourseOwner'),
('taskverification', NULL, NULL, NULL, 1, NULL, NULL, 1, NULL, 'TaskVerifier'),
('task', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'TaskVerifier'),
('tasktab', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'ShowTaskTab'),
('mydepartment', NULL, 'group', 'faculty', 1, 1, 1, 1, 1, 'DepartmentHead'),
('myowndepartment', NULL, 'user', 1, 1, 1, 1, 1, 1, NULL),
('annualreportadmin', NULL, 'group:role', 'medtech:admin', 1, 1, 1, 1, 1, NULL),
('gradebook', NULL, 'group', 'student', 1, NULL, 1, NULL, NULL, NULL),
('metadata', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL),
('evaluation', NULL, 'group', 'faculty', 1, NULL, 1, NULL, NULL, 'IsEvaluated'),
('evaluation', NULL, 'group', 'faculty', 1, NULL, 1, NULL, NULL, 'EvaluationReviewer'),
('evaluationform', NULL, 'group', 'faculty', 1, 1, 1, 1, NULL, 'EvaluationFormAuthor'),
('evaluationquestion', NULL, 'group', 'faculty', 1, 1, 1, 1, NULL, NULL),
('evaluationquestion', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL);

CREATE TABLE IF NOT EXISTS `departments` (
  `department_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) unsigned NOT NULL DEFAULT '1',
  `entity_id` int(12) unsigned NOT NULL DEFAULT '0',
  `parent_id` int(12) NOT NULL DEFAULT '0',
  `department_title` varchar(128) NOT NULL DEFAULT '',
  `department_address1` varchar(128) NOT NULL DEFAULT '',
  `department_address2` varchar(128) NOT NULL DEFAULT '',
  `department_city` varchar(64) NOT NULL DEFAULT 'Kingston',
  `department_province` varchar(64) NOT NULL DEFAULT 'ON',
  `province_id` int(12) NOT NULL DEFAULT '9',
  `department_country` varchar(64) NOT NULL DEFAULT 'CA',
  `country_id` int(12) NOT NULL DEFAULT '39',
  `department_postcode` varchar(16) NOT NULL DEFAULT '',
  `department_telephone` varchar(32) NOT NULL DEFAULT '',
  `department_fax` varchar(32) NOT NULL DEFAULT '',
  `department_email` varchar(128) NOT NULL DEFAULT '',
  `department_url` text NOT NULL,
  `department_desc` text,
  `department_active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) unsigned NOT NULL,
  `updated_by` int(12) unsigned NOT NULL,
  PRIMARY KEY  (`department_id`),
  UNIQUE KEY `organisation_id` (`organisation_id`,`entity_id`,`department_title`),
  KEY `department_active` (`department_active`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `departments` (`department_id`, `organisation_id`, `entity_id`, `department_title`, `department_address1`, `department_address2`, `department_city`, `department_province`, `department_country`, `department_postcode`, `department_telephone`, `department_fax`, `department_email`, `department_url`, `department_desc`) VALUES
(1, 1, 5, 'Medical IT', '', '', 'Kingston', 'ON', 'CA', '', '', '', '', '', NULL);

CREATE TABLE IF NOT EXISTS `department_heads` (
  `department_heads_id` int(11) NOT NULL auto_increment,
  `department_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY  (`department_heads_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `entity_type` (
  `entity_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `entity_title` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY  (`entity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `entity_type` (`entity_id`, `entity_title`) VALUES
(1, 'Faculty'),
(2, 'School'),
(3, 'Department'),
(4, 'Division'),
(5, 'Unit');

CREATE TABLE IF NOT EXISTS `locations` (
  `location_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) unsigned NOT NULL DEFAULT '1',
  `department_id` int(12) unsigned NOT NULL DEFAULT '0',
  `location_title` varchar(128) NOT NULL DEFAULT '',
  `location_address1` varchar(128) NOT NULL DEFAULT '',
  `location_address2` varchar(128) NOT NULL DEFAULT '',
  `location_city` varchar(64) NOT NULL DEFAULT 'Kingston',
  `location_province` char(2) NOT NULL DEFAULT 'ON',
  `location_country` char(2) NOT NULL DEFAULT 'CA',
  `location_postcode` varchar(7) NOT NULL DEFAULT '',
  `location_telephone` varchar(32) NOT NULL DEFAULT '',
  `location_fax` varchar(32) NOT NULL DEFAULT '',
  `location_email` varchar(128) NOT NULL DEFAULT '',
  `location_url` text NOT NULL,
  `location_longitude` varchar(12) DEFAULT NULL,
  `location_latitude` varchar(12) DEFAULT NULL,
  `location_desc` text,
  PRIMARY KEY  (`location_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `location_ipranges` (
  `iprange_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `location_id` int(12) unsigned NOT NULL DEFAULT '0',
  `block_start` varchar(32) NOT NULL DEFAULT '0',
  `block_end` varchar(32) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`iprange_id`),
  KEY `location_id` (`location_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `organisations` (
  `organisation_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_title` varchar(128) NOT NULL DEFAULT '',
  `organisation_address1` varchar(128) NOT NULL DEFAULT '',
  `organisation_address2` varchar(128) NOT NULL DEFAULT '',
  `organisation_city` varchar(64) NOT NULL DEFAULT 'Kingston',
  `organisation_province` varchar(64) NOT NULL DEFAULT 'ON',
  `organisation_country` varchar(64) NOT NULL DEFAULT 'CA',
  `organisation_postcode` varchar(16) NOT NULL DEFAULT '',
  `organisation_telephone` varchar(32) NOT NULL DEFAULT '',
  `organisation_fax` varchar(32) NOT NULL DEFAULT '',
  `organisation_email` varchar(128) NOT NULL DEFAULT '',
  `organisation_url` text NOT NULL,
  `organisation_desc` text,
  `template` varchar(32) NOT NULL DEFAULT 'default',
  `aamc_institution_id` varchar(32) DEFAULT NULL,
  `aamc_institution_name` varchar(255) DEFAULT NULL,
  `aamc_program_id` varchar(32) DEFAULT NULL,
  `aamc_program_name` varchar(255) DEFAULT NULL,
  `organisation_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`organisation_id`),
  KEY `organisation_active` (`organisation_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `organisations` (`organisation_id`, `organisation_title`, `organisation_address1`, `organisation_address2`, `organisation_city`, `organisation_province`, `organisation_country`, `organisation_postcode`, `organisation_telephone`, `organisation_fax`, `organisation_email`, `organisation_url`, `organisation_desc`, `organisation_active`) VALUES
(1, 'Your University', 'University Avenue', '', 'Kingston', 'ON', 'CA', 'K7L3N6', '613-533-2000', '', '', 'http://www.yourschool.ca', NULL, 1);

CREATE TABLE IF NOT EXISTS `password_reset` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(24) NOT NULL DEFAULT '',
  `date` bigint(64) NOT NULL DEFAULT '0',
  `user_id` int(12) NOT NULL DEFAULT '0',
  `hash` varchar(64) NOT NULL DEFAULT '',
  `complete` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `registered_apps` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `script_id` varchar(32) NOT NULL DEFAULT '0',
  `script_password` varchar(32) NOT NULL DEFAULT '',
  `server_ip` varchar(75) NOT NULL DEFAULT '',
  `server_url` text NOT NULL,
  `employee_rep` int(12) unsigned NOT NULL DEFAULT '0',
  `notes` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `script_id` (`script_id`),
  KEY `script_password` (`script_password`),
  KEY `server_ip` (`server_ip`),
  KEY `employee_rep` (`employee_rep`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `registered_apps` (`id`, `script_id`, `script_password`, `server_ip`, `server_url`, `employee_rep`, `notes`) VALUES
(1, '%AUTH_USERNAME%', MD5('%AUTH_PASSWORD%'), '%', '%', 1, 'Entrada');

CREATE TABLE IF NOT EXISTS `sessions` (
  `sesskey` varchar(64) NOT NULL DEFAULT '',
  `expiry` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expireref` varchar(250) DEFAULT '',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sessdata` longtext,
  PRIMARY KEY  (`sesskey`),
  KEY `sess2_expiry` (`expiry`),
  KEY `sess2_expireref` (`expireref`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `statistics` (
  `statistic_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) unsigned NOT NULL DEFAULT '0',
  `app_id` int(12) unsigned NOT NULL DEFAULT '0',
  `role` varchar(32) NOT NULL DEFAULT '',
  `group` varchar(32) NOT NULL DEFAULT '',
  `timestamp` bigint(64) NOT NULL DEFAULT '0',
  `prune_after` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`statistic_id`),
  KEY `proxy_id` (`proxy_id`,`app_id`,`role`,`group`,`timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user_access` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(12) unsigned NOT NULL DEFAULT '0',
  `app_id` int(12) unsigned NOT NULL DEFAULT '0',
  `organisation_id` INT(12) unsigned NOT NULL DEFAULT '0',
  `account_active` enum('true','false') NOT NULL DEFAULT 'true',
  `access_starts` bigint(64) NOT NULL DEFAULT '0',
  `access_expires` bigint(64) NOT NULL DEFAULT '0',
  `last_login` bigint(64) NOT NULL DEFAULT '0',
  `last_ip` varchar(75) NOT NULL DEFAULT '',
  `login_attempts` int(11) DEFAULT NULL,
  `locked_out_until` bigint(64) DEFAULT NULL,
  `role` varchar(35) NOT NULL DEFAULT '',
  `group` varchar(35) NOT NULL DEFAULT '',
  `extras` longtext NOT NULL,
  `private_hash` varchar(32) DEFAULT NULL,
  `notes` text NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `private_hash` (`private_hash`),
  KEY `user_id` (`user_id`),
  KEY `app_id` (`app_id`),
  KEY `account_active` (`account_active`),
  KEY `access_starts` (`access_starts`),
  KEY `access_expires` (`access_expires`),
  KEY `role` (`role`),
  KEY `group` (`group`),
  KEY `user_app_id` (`user_id`,`app_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `user_access` (`id`, `user_id`, `app_id`, `organisation_id`, `account_active`, `access_starts`, `access_expires`, `last_login`, `last_ip`, `login_attempts`, `locked_out_until`, `role`, `group`, `extras`, `private_hash`, `notes`) VALUES
(1, 1, 1, 1, 'true', 1216149930, 0, 0, '', NULL, NULL, 'admin', 'medtech', 'YToxOntzOjE2OiJhbGxvd19wb2RjYXN0aW5nIjtzOjM6ImFsbCI7fQ==', MD5(CONCAT(rand(), CURRENT_TIMESTAMP)), '');

CREATE TABLE IF NOT EXISTS `user_data` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `number` int(12) unsigned NOT NULL DEFAULT '0',
  `username` varchar(25) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `organisation_id` int(12) NOT NULL DEFAULT '1',
  `department` varchar(255) DEFAULT NULL,
  `prefix` varchar(10) NOT NULL DEFAULT '',
  `firstname` varchar(35) NOT NULL DEFAULT '',
  `lastname` varchar(35) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `email_alt` varchar(255) NOT NULL DEFAULT '',
  `email_updated` bigint(64) DEFAULT NULL,
  `google_id` varchar(128) DEFAULT NULL,
  `telephone` varchar(25) NOT NULL DEFAULT '',
  `fax` varchar(25) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `city` varchar(35) NOT NULL DEFAULT '',
  `province` varchar(35) NOT NULL DEFAULT '',
  `postcode` varchar(12) NOT NULL DEFAULT '',
  `country` varchar(35) NOT NULL DEFAULT '',
  `country_id` int(12) DEFAULT NULL,
  `province_id` int(12) DEFAULT NULL,
  `notes` text NOT NULL,
  `office_hours` text,
  `privacy_level` int(1) DEFAULT '0',
  `notifications` int(1) NOT NULL DEFAULT '0',
  `entry_year` int(11) DEFAULT NULL,
  `grad_year` int(11) DEFAULT NULL,
  `gender` int(1) NOT NULL DEFAULT '0',
  `clinical` int(1) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `number` (`number`),
  KEY `password` (`password`),
  KEY `firstname` (`firstname`),
  KEY `lastname` (`lastname`),
  KEY `privacy_level` (`privacy_level`),
  KEY `google_id` (`google_id`),
  KEY `clinical` (`clinical`),
  KEY `organisation_id` (`organisation_id`),
  KEY `gender` (`gender`),
  KEY `country_id` (`country_id`),
  KEY `province_id` (`province_id`),
  FULLTEXT KEY `firstname_2` (`firstname`,`lastname`,`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `user_data` (`id`, `number`, `username`, `password`, `organisation_id`, `department`, `prefix`, `firstname`, `lastname`, `email`, `email_alt`, `google_id`, `telephone`, `fax`, `address`, `city`, `province`, `postcode`, `country`, `notes`, `office_hours`, `privacy_level`, `notifications`, `clinical`) VALUES
(1, 0, '%ADMIN_USERNAME%', '%ADMIN_PASSWORD_HASH%', 1, NULL, '', '%ADMIN_FIRSTNAME%', '%ADMIN_LASTNAME%', '%ADMIN_EMAIL%', '', NULL, '', '', '', '', '', '', '', 'System Administrator', NULL, 0, 0, 1);

CREATE TABLE IF NOT EXISTS `user_departments` (
  `udep_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(12) unsigned NOT NULL DEFAULT '0',
  `dep_id` int(12) unsigned NOT NULL DEFAULT '0',
  `dep_title` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY  (`udep_id`),
  KEY `user_id` (`user_id`),
  KEY `dep_id` (`dep_id`),
  KEY `dep_title` (`dep_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `user_departments` (`udep_id`, `user_id`, `dep_id`, `dep_title`) VALUES
(1, 1, 1, 'System Administrator');

CREATE TABLE IF NOT EXISTS `user_incidents` (
  `incident_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) unsigned NOT NULL DEFAULT '0',
  `incident_title` text NOT NULL,
  `incident_description` text,
  `incident_severity` tinyint(1) NOT NULL DEFAULT '1',
  `incident_status` tinyint(1) NOT NULL DEFAULT '1',
  `incident_author_id` int(12) NOT NULL DEFAULT '0',
  `incident_date` bigint(64) NOT NULL DEFAULT '0',
  `follow_up_date` bigint(64) DEFAULT NULL,
  PRIMARY KEY  (`incident_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user_photos` (
  `photo_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) unsigned NOT NULL DEFAULT '0',
  `photo_mimetype` varchar(64) NOT NULL,
  `photo_filesize` int(32) NOT NULL DEFAULT '0',
  `photo_active` int(1) NOT NULL DEFAULT '1',
  `photo_type` int(1) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`photo_id`),
  KEY `photo_active` (`photo_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user_preferences` (
  `preference_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` int(12) unsigned NOT NULL DEFAULT '0',
  `proxy_id` int(12) unsigned NOT NULL DEFAULT '0',
  `module` varchar(32) NOT NULL DEFAULT '',
  `preferences` text NOT NULL,
  `updated` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`preference_id`),
  KEY `app_id` (`app_id`,`proxy_id`,`module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user_data_resident` (
  `proxy_id` int(12) NOT NULL,
  `cmpa_no` int(11) NOT NULL,
  `cpso_no` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `student_no` int(11) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `category_id` int(11) NOT NULL,
  `assess_prog_img` varchar(1) NOT NULL,
  `assess_prog_non_img` varchar(1) NOT NULL,
  PRIMARY KEY (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `system_groups` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `group_name` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `system_groups` (`group_name`) VALUES
('student'),
('alumni'),
('faculty'),
('resident'),
('staff'),
('medtech'),
('guest');

CREATE TABLE IF NOT EXISTS `system_roles` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `role_name` VARCHAR(45) NOT NULL ,
  `groups_id` INT NOT NULL ,
  PRIMARY KEY (`id`, `groups_id`) )
ENGINE = MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `system_roles` (`role_name`, `groups_id`) VALUES
((YEAR(CURRENT_DATE())+4),1),
((YEAR(CURRENT_DATE())+3),1),
((YEAR(CURRENT_DATE())+2),1),
((YEAR(CURRENT_DATE())+1),1),
((YEAR(CURRENT_DATE())),1),
((YEAR(CURRENT_DATE())-1),1),
((YEAR(CURRENT_DATE())-2),1),
((YEAR(CURRENT_DATE())-3),1),
((YEAR(CURRENT_DATE())-4),1),
((YEAR(CURRENT_DATE())+4),2),
((YEAR(CURRENT_DATE())+3),2),
((YEAR(CURRENT_DATE())+2),2),
((YEAR(CURRENT_DATE())+1),2),
((YEAR(CURRENT_DATE())),2),
((YEAR(CURRENT_DATE())-1),2),
((YEAR(CURRENT_DATE())-2),2),
((YEAR(CURRENT_DATE())-3),2),
((YEAR(CURRENT_DATE())-4),2),
('faculty',3),
('lecturer',3),
('director',3),
('admin',3),
('resident',4),
('lecturer',4),
('staff',5),
('pcoordinator',5),
('admin',5),
('staff',6),
('admin',6),
('communityinvite',7);

CREATE  TABLE IF NOT EXISTS `system_group_organisation` (
  `groups_id` INT NOT NULL ,
  `organisation_id` INT(12) UNSIGNED NOT NULL ,
  PRIMARY KEY (`groups_id`, `organisation_id`) )
ENGINE = MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `system_group_organisation` (`organisation_id`, `groups_id`)
VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7);