CREATE TABLE IF NOT EXISTS `assignments` (
  `assignment_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `assignment_title` varchar(40) NOT NULL,
  `assignment_description` text NOT NULL,
  `assignment_active` int(11) NOT NULL,
  `required` int(1) NOT NULL,
  `due_date` bigint(64) NOT NULL DEFAULT '0',
  `assignment_uploads` int(11) NOT NULL DEFAULT '0',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`assignment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `assignment_files` (
  `afile_id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT '0',
  `proxy_id` int(11) NOT NULL,
  `file_type` varchar(24) NOT NULL DEFAULT 'submission',
  `file_title` varchar(40) NOT NULL,
  `file_description` text,
  `file_active` int(1) NOT NULL DEFAULT '1',
  `updated_date` int(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`afile_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `assignment_file_versions` (
  `afversion_id` int(11) NOT NULL AUTO_INCREMENT,
  `afile_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `proxy_id` int(11) NOT NULL,
  `file_mimetype` varchar(64) NOT NULL,
  `file_version` int(5) DEFAULT NULL,
  `file_filename` varchar(128) NOT NULL,
  `file_filesize` int(32) NOT NULL,
  `file_active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`afversion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `assignment_contacts` (
  `acontact_id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `proxy_id` int(11) NOT NULL,
  `contact_order` int(11) DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`acontact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `assignment_comments` (
  `acomment_id` int(12) NOT NULL AUTO_INCREMENT,
  `afile_id` int(12) NOT NULL DEFAULT '0',
  `assignment_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `comment_title` varchar(128) NOT NULL,
  `comment_description` text NOT NULL,
  `comment_active` int(1) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  `notify` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`acomment_id`),
  KEY `assignment_id` (`assignment_id`,`proxy_id`,`comment_active`,`updated_date`,`updated_by`),
  KEY `afile_id` (`afile_id`),
  KEY `release_date` (`release_date`),
  FULLTEXT KEY `comment_title` (`comment_title`,`comment_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1228' WHERE `shortname` = 'version_db';
UPDATE `settings` SET `value` = '1.3.0' WHERE `shortname` = 'version_entrada';