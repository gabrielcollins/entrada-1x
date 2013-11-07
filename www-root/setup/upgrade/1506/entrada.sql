ALTER TABLE `assessments_lu_meta_options` ADD COLUMN `type` varchar(255) DEFAULT NULL AFTER `active`;

INSERT INTO `assessments_lu_meta_options` (`id`, `title`, `type`) VALUES
(5, 'Track Late Submissions', 'reflection, project, paper'),
(6, 'Track Resubmissions', 'reflection, project, paper');

CREATE TABLE IF NOT EXISTS `assessment_option_values` (
  `aovalue_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `aoption_id` int(12) NOT NULL,
  `proxy_id` int(12) NOT NULL,
  `value` varchar(32) DEFAULT '',
  PRIMARY KEY (`aovalue_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1506' WHERE `shortname` = 'version_db';