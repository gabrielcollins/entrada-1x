CREATE TABLE IF NOT EXISTS `evaluation_form_question_objectives` (
  `efqobjective_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `efquestion_id` int(12) NOT NULL,
  `objective_id` int(12) NOT NULL,
  `updated_date` bigint(64) DEFAULT NULL,
  `updated_by` int(12) DEFAULT NULL,
  PRIMARY KEY (`efqobjective_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1317' WHERE `shortname` = 'version_db';