CREATE TABLE IF NOT EXISTS `organisation_lu_restricted_days` (
  `orday_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) NOT NULL,
  `date_type` enum('specific','computed','weekly','monthly') NOT NULL DEFAULT 'specific',
  `offset` tinyint(1) DEFAULT NULL,
  `day` tinyint(2) DEFAULT NULL,
  `month` tinyint(2) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `updated_date` int(12) NOT NULL,
  `updated_by` int(12) NOT NULL,
  `day_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`orday_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `event_lti_consumers` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL,
  `is_required` int(1) NOT NULL,
  `valid_from` bigint(64) NOT NULL,
  `valid_until` bigint(64) NOT NULL,
  `timeframe` varchar(64) NOT NULL,
  `launch_url` text NOT NULL,
  `lti_key` varchar(300) NOT NULL,
  `lti_secret` varchar(300) NOT NULL,
  `lti_params` text NOT NULL,
  `lti_title` varchar(300) NOT NULL,
  `lti_notes` text NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `course_lti_consumers` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(12) NOT NULL,
  `is_required` int(1) NOT NULL DEFAULT '0',
  `valid_from` bigint(64) NOT NULL,
  `valid_until` bigint(64) NOT NULL,
  `launch_url` text NOT NULL,
  `lti_key` varchar(300) NOT NULL,
  `lti_secret` varchar(300) NOT NULL,
  `lti_params` text NOT NULL,
  `lti_title` varchar(300) NOT NULL,
  `lti_notes` text NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1600' WHERE `shortname` = 'version_db';
UPDATE `settings` SET `value` = '1.6.0' WHERE `shortname` = 'version_entrada';
