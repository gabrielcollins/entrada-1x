CREATE TABLE IF NOT EXISTS `draft_audience` (
  `daudience_id` int(12) NOT NULL AUTO_INCREMENT,
  `eaudience_id` int(12) NOT NULL,
  `devent_id` int(12) NOT NULL DEFAULT '0',
  `event_id` int(12) NOT NULL DEFAULT '0',
  `audience_type` enum('proxy_id','grad_year','cohort','organisation_id','group_id','course_id') NOT NULL,
  `audience_value` varchar(16) NOT NULL,
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`daudience_id`),
  KEY `eaudience_id` (`eaudience_id`),
  KEY `event_id` (`event_id`),
  KEY `target_value` (`audience_value`),
  KEY `target_type` (`audience_type`),
  KEY `event_id_2` (`event_id`,`audience_type`,`audience_value`),
  KEY `audience_type` (`audience_type`,`audience_value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `draft_contacts` (
  `dcontact_id` int(12) NOT NULL AUTO_INCREMENT,
  `econtact_id` int(12) DEFAULT NULL,
  `devent_id` int(12) NOT NULL DEFAULT '0',
  `event_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `contact_role` enum('teacher','tutor','ta','auditor') NOT NULL,
  `contact_order` int(6) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`dcontact_id`),
  KEY `econtact_id` (`econtact_id`),
  KEY `contact_order` (`contact_order`),
  KEY `event_id` (`event_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `draft_creators` (
  `create_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `draft_id` int(11) NOT NULL,
  `proxy_id` int(11) NOT NULL,
  PRIMARY KEY (`create_id`),
  KEY `DRAFT` (`draft_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `draft_events` (
  `devent_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) DEFAULT NULL,
  `draft_id` int(11) DEFAULT NULL,
  `parent_id` int(12) DEFAULT NULL,
  `event_children` int(12) DEFAULT NULL,
  `recurring_id` int(12) DEFAULT '0',
  `region_id` int(12) DEFAULT '0',
  `course_id` int(12) NOT NULL DEFAULT '0',
  `event_phase` varchar(12) DEFAULT NULL,
  `event_title` varchar(255) NOT NULL,
  `event_description` text,
  `include_parent_description` tinyint(1) NOT NULL DEFAULT '1',
  `event_goals` text,
  `event_objectives` text,
  `event_message` text,
  `include_parent_message` tinyint(1) NOT NULL DEFAULT '1',
  `event_location` varchar(64) DEFAULT NULL,
  `event_start` bigint(64) NOT NULL,
  `event_finish` bigint(64) NOT NULL,
  `event_duration` int(64) NOT NULL,
  `release_date` bigint(64) NOT NULL,
  `release_until` bigint(64) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`devent_id`),
  KEY `event_id` (`event_id`),
  KEY `course_id` (`course_id`),
  KEY `region_id` (`region_id`),
  KEY `recurring_id` (`recurring_id`),
  KEY `release_date` (`release_date`,`release_until`),
  KEY `event_start` (`event_start`,`event_duration`),
  KEY `event_start_2` (`event_start`,`event_finish`),
  KEY `event_phase` (`event_phase`),
  KEY `event_start_3` (`event_start`,`event_finish`,`release_date`,`release_until`),
  KEY `parent_id` (`parent_id`),
  FULLTEXT KEY `event_title` (`event_title`,`event_description`,`event_goals`,`event_objectives`,`event_message`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `draft_eventtypes` (
  `deventtype_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `eeventtype_id` int(12) DEFAULT NULL,
  `devent_id` int(12) NOT NULL,
  `event_id` int(12) DEFAULT NULL,
  `eventtype_id` int(12) NOT NULL,
  `duration` int(12) NOT NULL,
  `order` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`deventtype_id`),
  KEY `eeventtype_id` (`eeventtype_id`),
  KEY `event_id` (`devent_id`),
  KEY `eventtype_id` (`eventtype_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `drafts` (
  `draft_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `status` text,
  `name` text,
  `description` text,
  `created` int(11) DEFAULT NULL,
  `preserve_elements` binary(4) DEFAULT NULL,
  PRIMARY KEY (`draft_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1307' WHERE `shortname` = 'version_db';