ALTER TABLE `curriculum_lu_types` ADD `curriculum_level_id` INT( 12 ) NULL DEFAULT NULL AFTER `curriculum_type_active`;

CREATE TABLE IF NOT EXISTS `curriculum_lu_levels` (
  `curriculum_level_id` int(11) unsigned NOT NULL auto_increment,
  `curriculum_level` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`curriculum_level_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

INSERT INTO `curriculum_lu_levels` (`curriculum_level_id`, `curriculum_level`) VALUES
(1, 'Undergraduate'),
(2, 'Postgraduate');

UPDATE `settings` SET `value` = '1224' WHERE `shortname` = 'version_db';