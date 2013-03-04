CREATE TABLE IF NOT EXISTS `notification_users` (
  `nuser_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `proxy_id` int(11) NOT NULL,
  `content_type` varchar(32) NOT NULL DEFAULT '',
  `record_id` int(11) NOT NULL,
  `record_proxy_id` int(11) DEFAULT NULL,
  `notify_active` tinyint(1) NOT NULL DEFAULT '0',
  `digest_mode` tinyint(1) NOT NULL DEFAULT '0',
  `next_notification_date` int(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nuser_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nuser_id` int(11) NOT NULL,
  `notification_body` text NOT NULL,
  `proxy_id` int(11) NOT NULL,
  `digest` tinyint(1) NOT NULL DEFAULT '0',
  `sent` tinyint(1) NOT NULL DEFAULT '0',
  `sent_date` bigint(64) DEFAULT '0',
  PRIMARY KEY (`notification_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1309' WHERE `shortname` = 'version_db';

