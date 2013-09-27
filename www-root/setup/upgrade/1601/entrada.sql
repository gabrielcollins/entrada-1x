ALTER TABLE `curriculum_periods` ADD COLUMN `curriculum_period_title` VARCHAR(200) NOT NULL DEFAULT '' AFTER `curriculum_type_id`;

UPDATE `settings` SET `value` = '1601' WHERE `shortname` = 'version_db';
UPDATE `settings` SET `value` = '1.6.1' WHERE `shortname` = 'version_entrada';