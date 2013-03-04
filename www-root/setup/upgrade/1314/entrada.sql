ALTER TABLE `attached_quizzes` ADD COLUMN `require_attendance` INT(1) NOT NULL DEFAULT '0' AFTER `required`;
ALTER TABLE `attached_quizzes` ADD COLUMN `random_order` INT(1) NOT NULL DEFAULT '0' AFTER `require_attendance`;
ALTER TABLE `community_discussion_topics` ADD COLUMN `anonymous` INT(1) NOT NULL DEFAULT '0' AFTER `proxy_id`;

UPDATE `settings` SET `value` = '1314' WHERE `shortname` = 'version_db';