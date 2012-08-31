ALTER TABLE `assessments` CHANGE `grade_weighting` `grade_weighting` float NOT NULL DEFAULT '0';


UPDATE `settings` SET `value` = '1225' WHERE `shortname` = 'version_db';