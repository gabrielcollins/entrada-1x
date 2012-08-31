CREATE TABLE IF NOT EXISTS `eventtype_organisation`(
`eventtype_id` INT(12) NOT NULL, 
`organisation_id` INT(12) NOT NULL 
) ENGINE = MyISAM DEFAULT CHARSET=utf8;

INSERT INTO eventtype_organisation SELECT a.eventtype_id, b.organisation_id FROM events_lu_eventtypes AS a JOIN entrada_auth.organisations AS b ON 1=1;

CREATE TABLE IF NOT EXISTS `groups` (
  `group_id` int(12) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  `group_active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`group_id`),
  FULLTEXT KEY `group_title` (`group_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `group_members` (
  `gmember_id` int(12) NOT NULL AUTO_INCREMENT,
  `group_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `member_active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`gmember_id`),
  KEY `group_id` (`group_id`,`proxy_id`,`updated_date`,`updated_by`),
  KEY `member_active` (`member_active`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1203' WHERE `shortname` = 'version_db';