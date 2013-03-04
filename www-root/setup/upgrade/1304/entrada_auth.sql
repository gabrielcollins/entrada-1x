ALTER TABLE `organisations` ADD `aamc_institution_id` VARCHAR(32) NULL DEFAULT NULL,
ADD `aamc_institution_name` VARCHAR(255) NULL DEFAULT NULL,
ADD `aamc_program_id` VARCHAR(32) NULL DEFAULT NULL,
ADD `aamc_program_name` VARCHAR(255) NULL DEFAULT NULL,
ADD `organisation_active` TINYINT(1) NOT NULL DEFAULT '1',
ADD INDEX (`organisation_active`);

