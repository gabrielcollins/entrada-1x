ALTER TABLE `groups` ADD COLUMN `group_type` enum('small_group','class') NOT NULL DEFAULT 'small_group' AFTER `group_name`;

UPDATE `settings` SET `value` = '1214' WHERE `shortname` = 'version_db';