ALTER TABLE `attached_quizzes` MODIFY `content_type` enum('event','community_page', 'assessment') NOT NULL DEFAULT 'event' AFTER `aquiz_id`;

UPDATE `settings` SET `value` = '1400' WHERE `shortname` = 'version_db';
UPDATE `settings` SET `value` = '1.4.0' WHERE `shortname` = 'version_entrada';