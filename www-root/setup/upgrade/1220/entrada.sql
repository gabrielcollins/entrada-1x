CREATE TABLE IF NOT EXISTS `notice_audience` (
  `naudience_id` int(11) NOT NULL AUTO_INCREMENT,
  `notice_id` int(11) NOT NULL,
  `audience_type` varchar(20) NOT NULL,
  `audience_value` int(11) NOT NULL DEFAULT '0',
  `updated_by` int(11) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`naudience_id`),
  KEY `audience_id` (`notice_id`,`audience_type`,`audience_value`,`updated_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `notice_audience` (`audience_type`, `audience_value`, `notice_id`, `updated_by`)
	(
		SELECT 	SUBSTRING_INDEX( `target` , ':', 1 ) AS `audience_type`,
				SUBSTRING_INDEX( `target` , ':', -1 ) AS `audience_value`,
				`notice_id`,
				1
		FROM `notices`
	);

UPDATE `notice_audience` SET `audience_type` = CONCAT('all:', `audience_type`) WHERE `audience_type` != 'all' AND `audience_type` != 'cohort';
UPDATE `notice_audience` SET `audience_type` = 'cohorts' WHERE `audience_type` = 'cohort';
UPDATE `notice_audience` SET `audience_type` = 'all:users' WHERE `audience_type` = 'all';
UPDATE `notice_audience` SET `audience_type` = 'students' WHERE `audience_type` = 'all:proxy_id';

ALTER TABLE `notices` DROP COLUMN `target`;

UPDATE `settings` SET `value` = '1220' WHERE `shortname` = 'version_db';