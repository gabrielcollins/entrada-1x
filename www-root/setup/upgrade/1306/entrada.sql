CREATE TABLE IF NOT EXISTS `event_history` (
  `ehistory_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `history_message` text NOT NULL,
  `history_display` int(1) NOT NULL DEFAULT '0',
  `history_timestamp` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ehistory_id`),
  KEY `history_timestamp` (`history_timestamp`),
  KEY `event_id` (`event_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

UPDATE `settings` SET `value` = '1306' WHERE `shortname` = 'version_db';
