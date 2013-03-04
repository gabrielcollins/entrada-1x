CREATE TABLE IF NOT EXISTS `reports_aamc_ci` (
  `raci_id` int(12) NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) NOT NULL,
  `report_title` varchar(255) NOT NULL,
  `report_date` bigint(64) NOT NULL DEFAULT '0',
  `report_start` bigint(64) NOT NULL DEFAULT '0',
  `report_finish` bigint(64) NOT NULL DEFAULT '0',
  `report_langauge` varchar(12) NOT NULL DEFAULT 'en-us',
  `report_description` text NOT NULL,
  `report_supporting_link` text NOT NULL,
  `report_active` tinyint(1) NOT NULL DEFAULT '1',
  `report_status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`raci_id`),
  KEY `report_date` (`report_date`),
  KEY `report_active` (`organisation_id`,`report_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1304' WHERE `shortname` = 'version_db';