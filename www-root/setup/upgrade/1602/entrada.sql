CREATE TABLE IF NOT EXISTS `portfolio_entries` (
  `pentry_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pfartifact_id` int(11) unsigned NOT NULL,
  `proxy_id` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  `submitted_date` bigint(64) unsigned NOT NULL DEFAULT '0',
  `reviewed_date` bigint(64) unsigned NOT NULL DEFAULT '0',
  `reviewed_by` int(10) unsigned NOT NULL,
  `flag` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `flagged_by` int(10) unsigned NOT NULL,
  `flagged_date` bigint(64) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  `_edata` text NOT NULL,
  `_class` varchar(200) NOT NULL,
  `order` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `type` enum('file','reflection','url') NOT NULL DEFAULT 'reflection',
  PRIMARY KEY (`pentry_id`),
  KEY `pfartifact_id` (`pfartifact_id`),
  KEY `order` (`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Used to record portfolio entries made by learners.';

CREATE TABLE IF NOT EXISTS `portfolio_artifact_permissions` (
  `permission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pentry_id` int(10) unsigned NOT NULL,
  `allow_to` int(10) unsigned NOT NULL COMMENT 'Who allowed to access',
  `proxy_id` int(10) unsigned NOT NULL COMMENT 'Who has created this permission',
  `view` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `comment` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `edit` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`permission_id`),
  KEY `portfolio_user_permissions_pentry_id` (`pentry_id`),
  CONSTRAINT `portfolio_user_permissions_pentry_id` FOREIGN KEY (`pentry_id`) REFERENCES `portfolio_entries` (`pentry_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `portfolio_entry_comments` (
  `pecomment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pentry_id` int(11) unsigned NOT NULL,
  `proxy_id` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  `comment` text NOT NULL,
  `submitted_date` bigint(64) unsigned NOT NULL,
  `flag` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  PRIMARY KEY (`pecomment_id`),
  KEY `pentry_id` (`pentry_id`),
  CONSTRAINT `portfolio_entry_comments_ibfk_1` FOREIGN KEY (`pentry_id`) REFERENCES `portfolio_entries` (`pentry_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Used to store comments on particular portfolio entries.';

CREATE TABLE IF NOT EXISTS `portfolios` (
  `portfolio_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(4) unsigned NOT NULL,
  `portfolio_name` varchar(100) NOT NULL,
  `start_date` bigint(64) unsigned NOT NULL,
  `finish_date` bigint(64) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  `organisation_id` int(11) NOT NULL,
  `allow_student_export` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`portfolio_id`),
  UNIQUE KEY `grad_year_unique` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The portfolio container for each class of learners.';

CREATE TABLE IF NOT EXISTS `portfolio_folders` (
  `pfolder_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `portfolio_id` int(11) unsigned NOT NULL,
  `title` varchar(32) NOT NULL,
  `description` text NOT NULL,
  `allow_learner_artifacts` tinyint(1) NOT NULL DEFAULT '0',
  `order` int(3) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  PRIMARY KEY (`pfolder_id`),
  KEY `portfolio_id` (`portfolio_id`),
  CONSTRAINT `portfolio_folders_ibfk_1` FOREIGN KEY (`portfolio_id`) REFERENCES `portfolios` (`portfolio_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The list of folders within each portfolio.';

CREATE TABLE IF NOT EXISTS `portfolios_lu_artifacts` (
  `artifact_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `handler_object` varchar(80) NOT NULL COMMENT 'PHP class which handles displays form to user.',
  `allow_learner_addable` tinyint(1) NOT NULL DEFAULT '0',
  `order` int(3) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  PRIMARY KEY (`artifact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Lookup table that stores all available types of artifacts.';

CREATE TABLE IF NOT EXISTS `portfolio_folder_artifacts` (
  `pfartifact_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pfolder_id` int(11) unsigned NOT NULL,
  `artifact_id` int(11) unsigned NOT NULL,
  `proxy_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `start_date` bigint(64) unsigned NOT NULL DEFAULT '0',
  `finish_date` bigint(64) unsigned NOT NULL DEFAULT '0',
  `allow_commenting` tinyint(1) NOT NULL DEFAULT '0',
  `order` int(3) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  `_edata` text,
  `handler_object` varchar(80) NOT NULL,
  PRIMARY KEY (`pfartifact_id`),
  KEY `pfolder_id` (`pfolder_id`),
  KEY `artifact_id` (`artifact_id`),
  CONSTRAINT `portfolio_folder_artifacts_ibfk_1` FOREIGN KEY (`pfolder_id`) REFERENCES `portfolio_folders` (`pfolder_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `portfolio_folder_artifacts_ibfk_2` FOREIGN KEY (`artifact_id`) REFERENCES `portfolios_lu_artifacts` (`artifact_id`) ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='List of artifacts within a particular portfolio folder.';

CREATE TABLE IF NOT EXISTS `portfolio_folder_artifact_reviewers` (
  `pfareviewer_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pfartifact_id` int(11) unsigned NOT NULL,
  `proxy_id` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  PRIMARY KEY (`pfareviewer_id`),
  KEY `pfartifact_id` (`pfartifact_id`),
  CONSTRAINT `portfolio_folder_artifact_reviewers_ibfk_1` FOREIGN KEY (`pfartifact_id`) REFERENCES `portfolio_folder_artifacts` (`pfartifact_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='List teachers responsible for reviewing an artifact.';

CREATE TABLE IF NOT EXISTS `portfolio-advisors` (
  `padvisor_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `proxy_id` int(11) NOT NULL,
  `portfolio_id` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`padvisor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1602' WHERE `shortname` = 'version_db';