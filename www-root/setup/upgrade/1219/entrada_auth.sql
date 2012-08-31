CREATE TABLE IF NOT EXISTS `user_organisations` (
	`id` int(12) NOT NULL AUTO_INCREMENT, 
	`organisation_id` int(3) NOT NULL, 
	`proxy_id` int(12) NOT NULL, 
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `user_organisations` (`organisation_id`, `proxy_id`)
    SELECT '1', a.`id`
    FROM `user_data` AS a
        JOIN `user_access` AS b
        ON b.`user_id` = a.`id`
    WHERE b.`app_id` = '1';

ALTER TABLE `organisations` ADD `template` VARCHAR(32) NOT NULL DEFAULT 'default' AFTER `organisation_desc`;
