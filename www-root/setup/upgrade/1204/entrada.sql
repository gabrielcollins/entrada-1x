
CREATE TABLE IF NOT EXISTS `pg_eval_response_rates` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `program_name` varchar(100) NOT NULL,
  `response_type` varchar(20) NOT NULL,
  `completed` int(10) NOT NULL,
  `distributed` int(10) NOT NULL,
  `percent_complete` int(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pg_one45_community` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `one45_name` varchar(50) NOT NULL,
  `community_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1204' WHERE `shortname` = 'version_db';