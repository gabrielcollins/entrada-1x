ALTER TABLE `community_mailing_lists` ADD COLUMN `last_checked` int(11) NOT NULL DEFAULT '0';

UPDATE `community_mailing_lists` SET `last_checked` = '0';

UPDATE `settings` SET `value` = '1316' WHERE `shortname` = 'version_db';