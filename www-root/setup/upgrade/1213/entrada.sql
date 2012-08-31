ALTER TABLE `assessments` ADD COLUMN `narrative` TINYINT(1) NOT NULL DEFAULT '0' AFTER `grade_weighting`;
ALTER TABLE `assessments` ADD COLUMN `required` TINYINT(1) NOT NULL DEFAULT '1' AFTER `narrative`;
ALTER TABLE `assessments` ADD COLUMN `characteristic_id` int(4) NOT NULL AFTER `required`;

ALTER TABLE `assessment_marking_schemes` ADD COLUMN `description` text NOT NULL AFTER `handler`;
UPDATE `assessment_marking_schemes` SET `description` = 'Enter P for Pass, or F for Fail, in the student mark column.' WHERE `id` = '1';
UPDATE `assessment_marking_schemes` SET `description` = 'Enter a percentage in the student mark column.' WHERE `id` = '2';
UPDATE `assessment_marking_schemes` SET `description` = 'Enter a numeric total in the student mark column.' WHERE `id` = '3';
UPDATE `assessment_marking_schemes` SET `description` = 'Enter C for Complete, or I for Incomplete, in the student mark column.' WHERE `id` = '4';

CREATE TABLE IF NOT EXISTS `assessment_options` (
  `aoption_id` int(12) NOT NULL AUTO_INCREMENT,
  `assessment_id` int(12) NOT NULL DEFAULT '0',
  `option_id` int(12) NOT NULL DEFAULT '0',
  `option_active` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`aoption_id`),
  KEY `assessment_id` (`assessment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `assessments_lu_meta` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) unsigned NOT NULL DEFAULT '0',
  `type` enum('rating','project','exam','paper','asessment','presentation','quiz','RAT','reflection') DEFAULT NULL,
  `title` varchar(60) NOT NULL,
  `description` text,
  `active` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `assessments_lu_meta` (`organisation_id`, `type`, `title`) VALUES 
('1', '1', 'Faculty, resident or preceptor rating'),
('1', '2', 'Final project'),
('1', '3', 'Final written examination'),
('1', '3', 'Laboratory or practical examination (except OSCE/SP)'),
('1', '3', 'Midterm examination'),
('1', '3', 'NBME subject examination'),
('1', '3', 'Oral exam'),
('1', '3', 'OSCE/SP examination'),
('1', '4', 'Paper'),
('1', '5', 'Peer-assessment'),
('1', '6', 'Presentation'),
('1', '7', 'Quiz'),
('1', '8', 'RAT'),
('1', '9', 'Reflection'),
('1', '5', 'Self-assessment'),
('1', '5', 'Other assessments');

CREATE TABLE IF NOT EXISTS `assessments_lu_meta_options` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(60) NOT NULL,
  `active` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `assessments_lu_meta_options` (`title`) VALUES
('Essay questions'),
('Fill-in, short answer questions'),
('Multiple-choice, true/false, matching questions'),
('Problem-solving written exercises');

UPDATE `settings` SET `value` = '1213' WHERE `shortname` = 'version_db';