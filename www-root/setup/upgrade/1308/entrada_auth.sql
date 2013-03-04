ALTER TABLE `user_access` ADD COLUMN `organisation_id` INT(12) unsigned NOT NULL DEFAULT '0' AFTER `app_id`;

CREATE TABLE IF NOT EXISTS `system_groups` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `group_name` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `system_groups` (`group_name`) VALUES
('student'),
('alumni'),
('faculty'),
('resident'),
('staff'),
('medtech'),
('guest');

CREATE TABLE IF NOT EXISTS `system_roles` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `role_name` VARCHAR(45) NOT NULL ,
  `groups_id` INT NOT NULL ,
  PRIMARY KEY (`id`, `groups_id`) )
ENGINE = MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `system_roles` (`role_name`, `groups_id`) VALUES
(2016,1),
(2015,1),
(2014,1),
(2013,1),
(2012,1),
(2011,1),
(2010,1),
(2009,1),
(2008,1),
(2007,1),
(2006,1),
(2005,1),
(2004,1),
(2015,2),
(2014,2),
(2013,2),
(2012,2),
(2011,2),
(2010,2),
(2009,2),
(2008,2),
(2007,2),
(2006,2),
(2005,2),
(2004,2),
(2003,2),
(2002,2),
(2001,2),
(2000,2),
(1999,2),
(1998,2),
(1997,2),
('faculty',3),
('lecturer',3),
('director',3),
('admin',3),
('resident',4),
('lecturer',4),
('staff',5),
('pcoordinator',5),
('admin',5),
('staff',6),
('admin',6),
('communityinvite',7);

CREATE TABLE IF NOT EXISTS `system_group_organisation` (
  `groups_id` INT NOT NULL ,
  `organisation_id` INT(12) UNSIGNED NOT NULL ,
  PRIMARY KEY (`groups_id`, `organisation_id`) )
ENGINE = MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `system_group_organisation` (`organisation_id`, `groups_id`)
VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7);