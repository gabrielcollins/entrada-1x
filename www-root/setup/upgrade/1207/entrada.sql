ALTER TABLE `events` ADD `parent_id` int(12) DEFAULT NULL AFTER `event_id`;
ALTER TABLE `events` ADD `event_children` int(12) DEFAULT NULL AFTER `parent_id`;
ALTER TABLE `events` ADD `include_parent_description` tinyint(1) NOT NULL DEFAULT 1 AFTER `event_description`;
ALTER TABLE `events` ADD `include_parent_message` tinyint(1) NOT NULL DEFAULT '1' AFTER `event_message`;
ALTER TABLE `event_audience` MODIFY COLUMN `audience_type` enum('proxy_id','grad_year','organisation_id','group_id','course_id') NOT NULL;

UPDATE `settings` SET `value` = '1207' WHERE `shortname` = 'version_db';
