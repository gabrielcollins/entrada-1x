ALTER TABLE `courses` ADD COLUMN `online_course` INT NOT NULL DEFAULT 0 AFTER `sync_ldap`;
ALTER TABLE `courses` ADD COLUMN `allow_enroll` INT NOT NULL DEFAULT 0 AFTER `online_course`;
ALTER TABLE `events` ADD COLUMN `online_event` INT NOT NULL DEFAULT 0 AFTER `parent_id`;

CREATE TABLE IF NOT EXISTS `payment_options`(
	`poption_id` INT NOT NULL AUTO_INCREMENT,
	`organisation_id` INT NOT NULL,
	`payment_name` VARCHAR(255) NOT NULL,
	`ptype_id` INT NOT NULL,
	`updated_date` bigint(64) NOT NULL,
	`updated_by` int(11) NOT NULL,
	PRIMARY KEY (`poption_id`)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS `payment_option_keys`(
	`pokey_id` INT NOT NULL AUTO_INCREMENT,
	`poption_id` INT NOT NULL,
	`ptkey_id` INT NOT NULL,
	`key_value` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`pokey_id`)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS `payment_lu_types`(
	`ptype_id` INT NOT NULL AUTO_INCREMENT,
	`payment_name` VARCHAR(255) NOT NULL,
	`payment_model` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`ptype_id`)	
) ENGINE=MyISAM DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS `payment_lu_type_keys`(
	`ptkey_id` INT NOT NULL AUTO_INCREMENT,
	`ptype_id` INT NOT NULL,	
	`key_name` VARCHAR(255) NOT NULL,
	`key_required` INT NOT NULL DEFAULT 0,
	PRIMARY KEY (`ptkey_id`)	
) ENGINE=MyISAM DEFAULT CHARSET=UTF8;

CREATE TABLE `payment_catalog` (
  `pcatalog_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_type` varchar(64) NOT NULL,
  `item_value` varchar(64) NOT NULL,
  `item_cost` float DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT '-1',
  `poption_id` int(11) NOT NULL,
  `active` int(11) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`pcatalog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8;

INSERT INTO `payment_lu_types` (`ptype_id`, `payment_name`, `payment_model`)
VALUES
	(1, 'Chase Exact Hosted', 'chaseexacthosted');

INSERT INTO `payment_lu_type_keys` (`ptkey_id`, `ptype_id`, `key_name`, `key_required`)
VALUES
	(1, 1, 'login', 1),
	(2, 1, 'key', 1);

CREATE TABLE `payment_transactions` (
  `ptransaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `proxy_id` int(11) NOT NULL,
  `transaction_hash` varchar(255) NOT NULL,
  `transaction_identifier` varchar(64) DEFAULT NULL,
  `transaction_amount` float DEFAULT NULL,
  `coupon_id` int(11) NOT NULL DEFAULT '0',
  `payment_method` varchar(64) NOT NULL,
  `payment_status` varchar(64) NOT NULL,
  `created_date` bigint(64) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`ptransaction_id`)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8;


CREATE TABLE `payment_transaction_items` (
  `ptitem_id` int(11) NOT NULL AUTO_INCREMENT,
  `ptransaction_id` int(11) NOT NULL,
  `pcatalog_id` int(11) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`ptitem_id`)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8;

CREATE TABLE `conference_lu_software` (
  `csoftware_id` int(11) NOT NULL AUTO_INCREMENT,
  `software_name` varchar(255) NOT NULL,
  `software_model` varchar(255) NOT NULL,
  `software_url` varchar(255),
  PRIMARY KEY (`csoftware_id`)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8;

CREATE TABLE `conference_lu_software_keys` (
  `cskey_id` int(11) NOT NULL AUTO_INCREMENT,
  `csoftware_id` int(11) NOT NULL,
  `key_name` varchar(255) NOT NULL,
  `key_required` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cskey_id`)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8;

CREATE TABLE `conference_lu_software_meta` (
  `csmeta_id` int(11) NOT NULL AUTO_INCREMENT,
  `csoftware_id` int(11) NOT NULL,
  `meta_name` varchar(255) NOT NULL,
  `meta_value` varchar(255) NOT NULL,
  PRIMARY KEY (`csmeta_id`)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8;

CREATE TABLE `web_conferences` (
  `wconference_id` int(11) NOT NULL AUTO_INCREMENT,
  `csoftware_id` int(11) NOT NULL,  
  `conference_title` varchar(255) NOT NULL,
  `conference_description` TEXT NOT NULL DEFAULT '',
  `attached_type` VARCHAR(255) NOT NULL,
  `attached_id` INT(11) NOT NULL,
  `conference_duration` INT(11) NOT NULL DEFAULT 360,
  `conference_start` BIGINT(64) NOT NULL,
  `release_date` BIGINT(64) NOT NULL DEFAULT 0,
  `release_until` BIGINT(64) NOT NULL DEFAULT 0,
  `updated_date` BIGINT(64) NOT NULL,
  `updated_by` INT(11) NOT NULL,
  PRIMARY KEY (`wconference_id`)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8;

CREATE TABLE `web_conference_key_values` (
  `wckvalue_id` int(11) NOT NULL AUTO_INCREMENT,
  `wconference_id` int(11) NOT NULL,
  `cskey_id` int(11) NOT NULL,
  `key_value` varchar(255) NOT NULL,
  PRIMARY KEY (`wckvalue_id`)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8;

INSERT INTO `conference_lu_software` (`csoftware_id`, `software_name`, `software_model`,`software_url`)
VALUES
	(1, 'Big Blue Button', 'bigbluebutton',NULL);

INSERT INTO `conference_lu_software_keys` (`cskey_id`, `csoftware_id`, `key_name`, `key_required`)
VALUES
	(1, 1, 'admin_pass', 1),
	(2, 1, 'attendee_pass', 1);

UPDATE `settings` SET `version_db` = '1401';