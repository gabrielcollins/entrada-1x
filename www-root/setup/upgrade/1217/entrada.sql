ALTER TABLE `assessments` ADD COLUMN `show_learner` tinyint(1) NOT NULL DEFAULT '0' AFTER `characteristic_id`;
ALTER TABLE `assessments` ADD COLUMN `release_date` bigint(64) NOT NULL DEFAULT '0' AFTER `show_learner`;
ALTER TABLE `assessments` ADD COLUMN `release_until` bigint(64) NOT NULL DEFAULT '0' AFTER `release_date`;

UPDATE `settings` SET `value` = '1217' WHERE `shortname` = 'version_db';