ALTER TABLE `groups` MODIFY COLUMN `group_type` VARCHAR(20);
ALTER TABLE `groups` ADD COLUMN `group_value` INT(12) AFTER `group_type`;
ALTER TABLE `groups` ADD COLUMN `start_date` BIGINT(64) AFTER `group_value`;
ALTER TABLE `groups` ADD COLUMN `expire_date` BIGINT(64) AFTER `start_date`;

ALTER TABLE `group_members` ADD COLUMN `start_date` BIGINT(64) NOT NULL DEFAULT 0 AFTER `proxy_id`;
ALTER TABLE `group_members` ADD COLUMN `finish_date` BIGINT(64) NOT NULL DEFAULT 0 AFTER `start_date`;
ALTER TABLE `group_members` ADD COLUMN `entrada_only` INT(1) DEFAULT 0 AFTER `member_active`;

ALTER TABLE `courses` ADD COLUMN `sync_ldap` INT(1) NOT NULL DEFAULT 0 AFTER `permission`;

CREATE TABLE IF NOT EXISTS `curriculum_type_organisation` (
  `curriculum_type_id` int(11) NOT NULL,
  `organisation_id` int(11) NOT NULL,
  PRIMARY KEY (`curriculum_type_id`,`organisation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `curriculum_periods` (
  `cperiod_id` int(11) NOT NULL AUTO_INCREMENT,
  `curriculum_type_id` int(11) NOT NULL,
  `start_date` bigint(64) NOT NULL,
  `finish_date` bigint(64) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`cperiod_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1216' WHERE `shortname` = 'version_db';