ALTER TABLE `events` ADD COLUMN `draft_id` int(12) DEFAULT NULL;

UPDATE `events` SET `draft_id` = NULL;

UPDATE `settings` SET `value` = '1318' WHERE `shortname` = 'version_db';