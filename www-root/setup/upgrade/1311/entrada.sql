CREATE TABLE IF NOT EXISTS `curriculum_level_organisation` (
  `cl_org_id` INT(12) NOT NULL AUTO_INCREMENT,
  `org_id` INT(12) NOT NULL,
  `curriculum_level_id` INT(11) NOT NULL,
  PRIMARY KEY (`cl_org_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `curriculum_level_organisation` (`org_id`,`curriculum_level_id`)
VALUES
	(1, 1),
	(1, 2);

UPDATE `settings` SET `value` = '1311' WHERE `shortname` = 'version_db';