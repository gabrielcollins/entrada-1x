ALTER TABLE `courses` ADD COLUMN `online_course` INT NOT NULL DEFAULT 0 AFTER `sync_ldap`;
ALTER TABLE `courses` ADD COLUMN `allow_enroll` INT NOT NULL DEFAULT 0 AFTER `online_course`;
ALTER TABLE `events` ADD COLUMN `online_event` INT NOT NULL DEFAULT 0 AFTER `parent_id`;