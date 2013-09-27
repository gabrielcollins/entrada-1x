ALTER TABLE `global_lu_objectives` ADD COLUMN `objective_loggable` tinyint(1) NOT NULL DEFAULT '0' AFTER `objective_order`;

CREATE TABLE IF NOT EXISTS `course_reports` (
  `creport_id` int(12) NOT NULL AUTO_INCREMENT,
  `course_id` int(12) NOT NULL DEFAULT '0',
  `course_report_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`creport_id`),
  KEY `course_id` (`course_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `course_lu_reports` (
  `course_report_id` int(12) NOT NULL AUTO_INCREMENT,
  `course_report_title` varchar(250) NOT NULL DEFAULT '',
  `section` varchar(250) NOT NULL DEFAULT '',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`course_report_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `course_lu_reports` (`course_report_id`, `course_report_title`, `section`, `updated_date`, `updated_by`)
VALUES
	(1, 'Report Card', 'report-card', UNIX_TIMESTAMP(), 1),
	(2, 'My Teachers', 'my-teachers', UNIX_TIMESTAMP(), 1);

CREATE TABLE IF NOT EXISTS `course_report_organisations` (
  `crorganisation_id` int(12) NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) NOT NULL DEFAULT '0',
  `course_report_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`crorganisation_id`),
  KEY `organisation_id` (`organisation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `course_report_organisations` (`organisation_id`, `course_report_id`, `updated_date`, `updated_by`)
VALUES
	(1, 1, UNIX_TIMESTAMP(), 1),
	(1, 2, UNIX_TIMESTAMP(), 1);

UPDATE `settings` SET `value` = '1508' WHERE `shortname` = 'version_db';