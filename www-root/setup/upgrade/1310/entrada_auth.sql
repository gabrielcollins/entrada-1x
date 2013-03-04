ALTER TABLE `departments` ADD COLUMN updated_date BIGINT(64) unsigned NOT NULL;
ALTER TABLE `departments` ADD COLUMN updated_by INT(12) unsigned NOT NULL;
ALTER TABLE `departments` ADD COLUMN `country_id` INT(12) NOT NULL AFTER `department_country`;
ALTER TABLE `departments` ADD COLUMN `province_id` INT(12) NOT NULL AFTER `department_province`;