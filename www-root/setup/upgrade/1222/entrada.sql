ALTER TABLE `ar_research` ADD `status` VARCHAR( 10 ) NULL DEFAULT NULL AFTER `research_id`;

ALTER TABLE `ar_research` ADD `location` VARCHAR( 25 ) NULL DEFAULT NULL AFTER `type`;

ALTER TABLE `ar_research` ADD `multiinstitutional` VARCHAR( 3 ) NULL DEFAULT NULL AFTER `location`;

ALTER TABLE `ar_conference_papers` CHANGE `location` `location` VARCHAR( 250 ) NULL DEFAULT NULL; 

ALTER TABLE `ar_conference_papers` ADD `countries_id` INT( 12 ) NULL DEFAULT NULL AFTER `location` ,
ADD `city` VARCHAR( 100 ) NULL DEFAULT NULL AFTER `countries_id` ,
ADD `prov_state` VARCHAR( 200 ) NULL DEFAULT NULL AFTER `city`;

INSERT INTO `ar_lu_contribution_roles` (`id`, `contribution_role`) VALUES (NULL , 'Site Leader on a Clinical Trial');

ALTER TABLE `ar_external_contributions` ADD `role` VARCHAR( 150 ) NULL DEFAULT NULL AFTER `city_country` ,
ADD `role_description` TEXT NULL DEFAULT NULL AFTER `role`;

ALTER TABLE `ar_external_contributions` ADD `countries_id` INT( 12 ) NULL DEFAULT NULL AFTER `city_country` ,
ADD `city` VARCHAR( 100 ) NULL DEFAULT NULL AFTER `countries_id` ,
ADD `prov_state` VARCHAR( 200 ) NULL DEFAULT NULL AFTER `city`;

ALTER TABLE `ar_external_contributions` CHANGE `city_country` `city_country` TEXT NULL DEFAULT NULL;

UPDATE `settings` SET `value` = '1222' WHERE `shortname` = 'version_db';