CREATE TABLE IF NOT EXISTS `course_groups` (
  `cgroup_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `group_name` VARCHAR(30) NOT NULL,
  `active` int(1) DEFAULT NULL,
  PRIMARY KEY (`cgroup_id`),
  KEY `course_id` (`course_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `course_group_audience` (
  `cgaudience_id` int(11) NOT NULL AUTO_INCREMENT,
  `cgroup_id` int(11) NOT NULL,
  `proxy_id` int(11) NOT NULL,
  `entrada_only` INT(1) DEFAULT 0,
  `start_date` BIGINT(64) NOT NULL,
  `finish_date` BIGINT(64) NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY (`cgaudience_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1215' WHERE `shortname` = 'version_db';
UPDATE `settings` SET `value` = '1.3.0DEV' WHERE `shortname` = 'version_entrada';
