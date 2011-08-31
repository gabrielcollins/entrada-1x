INSERT INTO `user_organisations` (`organisation_id`, `proxy_id`)
    SELECT '1', a.`id`
    FROM `user_data` AS a
        JOIN `user_access` AS b
        ON b.`user_id` = a.`id`
    WHERE b.`app_id` = '1';

ALTER TABLE `organisations` ADD `template` VARCHAR(32) NOT NULL DEFAULT 'default' AFTER `organisation_desc`;

UPDATE `settings` SET `value` = '1219' WHERE `shortname` = 'version_db';