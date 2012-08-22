CREATE TABLE `registration_confirmation` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(24) NOT NULL DEFAULT '',
  `date` bigint(64) NOT NULL DEFAULT '0',
  `user_id` int(12) NOT NULL DEFAULT '0',
  `hash` varchar(64) NOT NULL DEFAULT '',
  `complete` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `acl_permissions` (`resource_type`, `resource_value`, `entity_type`, `entity_value`, `app_id`, `create`, `read`, `update`, `delete`, `assertion`)
VALUES
	('course', NULL, 'group:role', 'online:learner', NULL, NULL, 1, NULL, NULL, 'OnlineCourse'),
	('event', NULL, 'group:role', 'online:learner', NULL, NULL, 1, NULL, NULL, 'OnlineEvent'),
	('profile', NULL, 'group:role', 'online:learner', NULL, NULL, 1, NULL, NULL, NULL),
	('dashboard', NULL, 'group:role', 'online:learner', NULL, NULL, 1, NULL, NULL, NULL);
