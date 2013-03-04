ALTER TABLE `assessments` ADD `grade_threshold` float NOT NULL DEFAULT '0' AFTER `order`;
ALTER TABLE `assessment_grades` ADD `threshold_notified` int(1) NOT NULL DEFAULT '0' AFTER `value`;
UPDATE `settings` SET `value` = '1301' WHERE `shortname` = 'version_db';