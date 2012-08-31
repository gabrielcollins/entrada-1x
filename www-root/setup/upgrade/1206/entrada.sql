CREATE TABLE IF NOT EXISTS `objective_organisation` (
  `objective_id` INT(12) NOT NULL, 
  `organisation_id` INT(12) NOT NULL, 
  PRIMARY KEY(`objective_id`,`organisation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `topic_organisation`(
  `topic_id` INT(12) NOT NULL, 
  `organisation_id` INT(12) NOT NULL,
  PRIMARY KEY(`topic_id`,`organisation_id`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `objective_organisation` SELECT a.`objective_id`, b.`organisation_id` FROM `global_lu_objectives` AS a JOIN `entrada_auth`.`organisations` AS b ON 1 = 1;

ALTER TABLE `event_contacts` ADD `contact_role` ENUM('teacher','tutor','ta','auditor') NOT NULL AFTER `proxy_id`;

UPDATE `settings` SET `value` = '1206' WHERE `shortname` = 'version_db';