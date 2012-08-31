
-- Table: apartments

ALTER TABLE `apartments` ADD `countries_id` INT( 12 ) NOT NULL DEFAULT '0' AFTER `apartment_id`,
ADD `province_id` INT(12) NOT NULL DEFAULT '0' AFTER `countries_id`;

ALTER TABLE `apartments` MODIFY COLUMN `apartment_province` varchar(24) NOT NULL DEFAULT '' AFTER `province_id`;

ALTER TABLE `apartments` DROP `apartment_country`;
ALTER TABLE `apartments` DROP `apartment_city`;

ALTER TABLE `apartments` ADD `apartment_information` TEXT NOT NULL AFTER `apartment_email`,
ADD `super_firstname` VARCHAR( 32 ) NOT NULL AFTER `apartment_information`,
ADD `super_lastname` VARCHAR( 32 ) NOT NULL AFTER `super_firstname`,
ADD `super_phone` VARCHAR( 32 ) NOT NULL AFTER `super_lastname`,
ADD `super_email` VARCHAR( 128 ) NOT NULL AFTER `super_phone`;

ALTER TABLE `apartments` DROP `apartment_status`;

ALTER TABLE `apartments` ADD INDEX (`countries_id`);
ALTER TABLE `apartments` ADD INDEX (`province_id`);

-- Table: apartment_schedule

ALTER TABLE `apartment_schedule` CHANGE `econtact_id` `proxy_id` INT(12) NOT NULL DEFAULT '0';
ALTER TABLE `apartment_schedule` DROP `econtact_notes`;

ALTER TABLE `apartment_schedule` ADD `occupant_title` VARCHAR( 64 ) NOT NULL AFTER `proxy_id`,
ADD `occupant_type` VARCHAR( 16 ) NOT NULL DEFAULT 'undergrad' AFTER `occupant_title`,
ADD `confirmed` INT( 1 ) NOT NULL DEFAULT '1' AFTER `occupant_type`,
ADD `cost_recovery` INT( 1 ) NOT NULL DEFAULT '0' AFTER `confirmed`,
ADD `notes` TEXT NOT NULL AFTER `cost_recovery`;

ALTER TABLE `apartment_schedule` ADD INDEX ( `occupant_type` );
ALTER TABLE `apartment_schedule` ADD INDEX ( `confirmed` );
ALTER TABLE `apartment_schedule` ADD INDEX ( `cost_recovery` );

-- Table: electives

ALTER TABLE `electives` ADD INDEX ( `region_id` );
ALTER TABLE `electives` ADD INDEX ( `event_id` );
ALTER TABLE `electives` ADD INDEX ( `department_id` );
ALTER TABLE `electives` ADD INDEX ( `discipline_id` );
ALTER TABLE `electives` ADD INDEX ( `schools_id` );
ALTER TABLE `electives` ADD INDEX ( `countries_id` );

-- Table: evaluations

ALTER TABLE `evaluations` ADD `active` INT( 1 ) NOT NULL DEFAULT '0' AFTER `form_id`,
ADD INDEX ( `active` );

-- Table: events

ALTER TABLE `events` ADD `requires_apartment` INT( 1 ) NOT NULL DEFAULT '0' AFTER `event_status`,
ADD INDEX ( `requires_apartment` );

ALTER TABLE `events` ADD INDEX ( `rotation_id` );

-- Table: regions

ALTER TABLE `regions` ADD `province_id` INT( 12 ) NOT NULL DEFAULT '0' AFTER `region_name`,
ADD INDEX ( `province_id` );

ALTER TABLE `regions` MODIFY COLUMN `countries_id` int(12) DEFAULT NULL AFTER `province_id`;
ALTER TABLE `regions` MODIFY COLUMN `prov_state` varchar(200) DEFAULT NULL AFTER `countries_id`;

ALTER TABLE `regions` CHANGE `countries_id` `countries_id` INT( 12 ) NOT NULL DEFAULT '0';

ALTER TABLE `regions` ADD `region_active` INT( 1 ) NOT NULL DEFAULT '1' AFTER `is_core`,
ADD `updated_date` BIGINT( 64 ) NOT NULL DEFAULT '0' AFTER `region_active`,
ADD `updated_by` INT( 12 ) NOT NULL DEFAULT '0' AFTER `updated_date`;

ALTER TABLE `regions` ADD INDEX ( `countries_id` );
ALTER TABLE `regions` ADD INDEX ( `is_core` );
