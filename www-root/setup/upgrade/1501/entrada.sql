ALTER TABLE `events_lu_resources` ADD `organisation_id` INT(11)  NOT NULL  DEFAULT '1'  AFTER `resource_id`;
ALTER TABLE `events_lu_resources` ADD `active` TINYINT(1)  NOT NULL  DEFAULT '1'  AFTER `updated_by`;

UPDATE `settings` SET `value` = '1501' WHERE `shortname` = 'version_db';