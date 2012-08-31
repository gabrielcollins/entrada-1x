ALTER TABLE `ar_lu_degree_types` ADD `visible` INT( 1 ) NOT NULL DEFAULT '1';

CREATE TABLE IF NOT EXISTS `ar_lu_pr_roles` (
  `role_id` int(11) NOT NULL default '0',
  `role_description` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`role_id`),
  KEY `role_description` (`role_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_pr_roles` (`role_id`, `role_description`) VALUES
(1, 'First Author'),
(2, 'Corresponding Author'),
(3, 'Contributing Author');

UPDATE `settings` SET `value` = '1223' WHERE `shortname` = 'version_db';