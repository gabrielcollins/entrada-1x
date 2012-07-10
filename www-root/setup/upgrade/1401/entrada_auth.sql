INSERT INTO `acl_permissions` (`resource_type`, `resource_value`, `entity_type`, `entity_value`, `app_id`, `create`, `read`, `update`, `delete`, `assertion`)
VALUES
	('course', NULL, 'group:role', 'online:learner', NULL, NULL, 1, NULL, NULL, 'OnlineCourse'),
	('event', NULL, 'group:role', 'online:learner', NULL, NULL, 1, NULL, NULL, 'OnlineEvent'),
	('profile', NULL, 'group:role', 'online:learner', NULL, NULL, 1, NULL, NULL, NULL),
	('dashboard', NULL, 'group:role', 'online:learner', NULL, NULL, 1, NULL, NULL, NULL);
