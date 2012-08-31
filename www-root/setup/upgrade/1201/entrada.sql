CREATE TABLE IF NOT EXISTS `mtd_categories` (
  `id` int(11) NOT NULL ,
  `category_code` varchar(3) NOT NULL,
  `category_description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `mtd_facilities` (
  `id` int(11) NOT NULL ,
  `facility_code` int(3) NOT NULL,
  `facility_name` varchar(50) NOT NULL,
  `kingston` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `mtd_locale_duration` (
  `id` int(11) NOT NULL ,
  `location_id` int(3) NOT NULL,
  `percent_time` int(3) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `mtd_moh_program_codes` (
  `id` int(11) NOT NULL ,
  `program_code` varchar(3) NOT NULL,
  `program_description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `mtd_moh_service_codes` (
  `id` int(11) NOT NULL ,
  `service_code` varchar(3) NOT NULL,
  `service_description` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `mtd_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `service_id` int(3) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `creator_id` int(12) NOT NULL,
  `type_code` varchar(1) NOT NULL,
  `updated_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(12) NOT NULL,
  `category_id` int(3) DEFAULT NULL,
  `home_program_id` int(3) DEFAULT NULL,
  `home_school_id` int(3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mtd_schools` (
  `id` int(11) NOT NULL ,
  `school_code` varchar(3) NOT NULL,
  `school_description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `mtd_pgme_moh_programs` (
  `id` int(11) NOT NULL ,
  `pgme_program_name` varchar(100) NOT NULL,
  `moh_service_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `mtd_type` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `type_code` varchar(1) NOT NULL,
  `type_description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `mtd_type` (`id`, `type_code`, `type_description`) VALUES
(1, 'I', 'in-patient/emergency'),
(2, 'O', 'out-patient');

INSERT INTO `communities_modules` (`module_id`, `module_shortname`, `module_version`, `module_title`, `module_description`, `module_active`, `module_permissions`, `updated_date`, `updated_by`) VALUES
(8, 'mtdtracking', '1.0.0', 'MTD Tracking', 'The MTD Tracking module allows Program Assistants to enter the weekly schedule for each of their Residents.', 0, 'a:2:{s:4:"edit";i:1;s:5:"index";i:0;}', 1216256830, 5440);

ALTER TABLE `communities_modules` ADD COLUMN `module_visible` int(1) NOT NULL DEFAULT '1' AFTER `module_active`;
UPDATE `communities_modules` SET `module_visible` = '0' WHERE `module_shortname` = 'mtdtracking';

UPDATE `settings` SET `value` = '1201' WHERE `shortname` = 'version_db';