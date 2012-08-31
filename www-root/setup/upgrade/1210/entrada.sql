ALTER TABLE `groups` RENAME TO `student_groups`;
ALTER TABLE `student_groups` CHANGE COLUMN `group_id` `sgroup_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `student_groups` DROP COLUMN `parent_id`;

ALTER TABLE `group_members` RENAME TO `student_group_members`;
ALTER TABLE `student_group_members` CHANGE COLUMN `gmember_id` `sgmember_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `student_group_members` CHANGE COLUMN `group_id` `sgroup_id` int(11) NOT NULL;

CREATE TABLE IF NOT EXISTS `student_group_organisations` (
  `sgorganisation_id` int(11) NOT NULL AUTO_INCREMENT,
  `sgroup_id` int(11) NOT NULL,
  `organisation_id` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`sgorganisation_id`),
  KEY `group_id` (`sgroup_id`,`organisation_id`,`updated_date`,`updated_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `small_groups`;
DROP TABLE IF EXISTS `small_group_members`;
DROP TABLE IF EXISTS `small_group_categories`;

UPDATE `settings` SET `value` = '1210' WHERE `shortname` = 'version_db';