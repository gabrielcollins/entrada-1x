CREATE TABLE `linked_objectives` (
  `linked_objective_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `objective_id` int(12) NOT NULL,
  `target_objective_id` int(12) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`linked_objective_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1507' WHERE `shortname` = 'version_db';