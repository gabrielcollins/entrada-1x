ALTER TABLE `student_groups` RENAME TO `groups`;
ALTER TABLE `groups` CHANGE COLUMN `sgroup_id` `group_id` int(12) NOT NULL AUTO_INCREMENT;

ALTER TABLE `student_group_members` RENAME TO `group_members`;
ALTER TABLE `group_members` CHANGE COLUMN `sgmember_id` `gmember_id` int(12) NOT NULL AUTO_INCREMENT;
ALTER TABLE `group_members` CHANGE COLUMN `sgroup_id` `group_id` int(12) NOT NULL DEFAULT '0';

ALTER TABLE `student_group_organisations` RENAME TO `group_organisations`;
ALTER TABLE `group_organisations` CHANGE COLUMN `sgorganisation_id` `gorganisation_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `group_organisations` CHANGE COLUMN `sgroup_id` `group_id` int(11) NOT NULL;

UPDATE `settings` SET `value` = '1211' WHERE `shortname` = 'version_db';