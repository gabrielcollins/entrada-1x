CREATE TABLE IF NOT EXISTS `logbook_entries` (
  `lentry_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) unsigned NOT NULL,
  `encounter_date` int(12) NOT NULL,
  `updated_date` bigint(64) unsigned NOT NULL DEFAULT '0',
  `patient_info` varchar(30) NOT NULL,
  `agerange_id` int(12) unsigned NOT NULL DEFAULT '0',
  `gender` varchar(1) NOT NULL DEFAULT '0',
  `course_id` int(12) unsigned NOT NULL DEFAULT '0',
  `llocation_id` int(12) unsigned NOT NULL DEFAULT '0',
  `lsite_id` int(11) NOT NULL,
  `comments` text,
  `reflection` text NOT NULL,
  `entry_active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`lentry_id`),
  KEY `proxy_id` (`proxy_id`,`entry_active`),
  KEY `proxy_id_2` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `logbook_entry_objectives` (
  `leobjective_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `lentry_id` int(12) unsigned NOT NULL DEFAULT '0',
  `objective_id` int(12) unsigned NOT NULL DEFAULT '0',
  `participation_level` int(12) NOT NULL DEFAULT '3',
  `updated_by` int(11) NOT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `objective_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`leobjective_id`),
  KEY `lentry_id` (`lentry_id`,`objective_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `logbook_lu_ageranges` (
  `agerange_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `agerange` varchar(8) DEFAULT NULL,
  `agerange_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`agerange_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `logbook_lu_ageranges` (`agerange_id`, `agerange`, `agerange_active`)
VALUES
	(1, '< 1', 1),
	(2, '1 - 4', 1),
	(3, '5 - 12', 1),
	(4, '13 - 19', 1),
	(5, '20 - 64', 1),
	(6, '65 - 74', 1),
	(7, '75+', 1);

CREATE TABLE IF NOT EXISTS `logbook_lu_locations` (
  `llocation_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `location` varchar(64) DEFAULT NULL,
  `location_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`llocation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `logbook_lu_locations` (`llocation_id`, `location`, `location_active`)
VALUES
	(1, 'Clinic', 1),
	(2, 'Ward', 1),
	(3, 'Emergency', 1),
	(4, 'ICU', 1),
	(5, 'Private Office', 1),
	(6, 'OR', 1),
	(7, 'NICU', 1),
	(8, 'Nursing Home', 1),
	(9, 'Community Site', 1),
	(10, 'Computer Interactive Case', 1),
	(11, 'Other (provide details in additional comments field)', 1);

CREATE TABLE IF NOT EXISTS `logbook_lu_sites` (
  `lsite_id` int(11) NOT NULL AUTO_INCREMENT,
  `site_name` varchar(64) NOT NULL,
  `site_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`lsite_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `logbook_lu_sites` (`lsite_id`, `site_name`, `site_active`)
VALUES
	(1, 'Brockville General Hospital', 1),
	(2, 'Brockville Pyschiatric Hospital', 1),
	(3, 'Hotel Dieu Hospital (Kingston)', 1),
	(4, 'Kingston General Hospital', 1),
	(5, 'Lakeridge Health', 1),
	(6, 'Markam Stouffville Hospital', 1),
	(7, 'Perth Family Health Team', 1),
	(8, 'Perth/Smiths Falls District Hospital', 1),
	(9, 'Peterborough Regional Health Centre', 1),
	(10, 'Providence Care Centre', 1),
	(11, 'Quinte Health Care', 1),
	(12, 'Weenebayko General Hospital', 1),
	(13, 'Other (provide details in additional comments field)', 1);

UPDATE `settings` SET `value` = '1509' WHERE `shortname` = 'version_db';