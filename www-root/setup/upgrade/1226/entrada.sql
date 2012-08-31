ALTER TABLE `assessments` ADD `order` smallint(6) NOT NULL DEFAULT '0' AFTER `release_until`, ADD KEY `order` (`order`);

CREATE TABLE IF NOT EXISTS `assessment_objectives` (
  `aobjective_id` int(12) NOT NULL AUTO_INCREMENT,
  `assessment_id` int(12) NOT NULL DEFAULT '0',
  `objective_id` int(12) NOT NULL DEFAULT '0',
  `importance` int(11) DEFAULT NULL,
  `objective_details` text,
  `objective_type` enum('curricular_objective','clinical_presentation') NOT NULL DEFAULT 'curricular_objective',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`aobjective_id`),
  KEY `assessment_id` (`assessment_id`),
  KEY `objective_id` (`objective_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

UPDATE `settings` SET `value` = '1226' WHERE `shortname` = 'version_db';