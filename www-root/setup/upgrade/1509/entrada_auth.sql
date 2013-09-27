INSERT INTO `acl_permissions` (`resource_type`, `resource_value`, `entity_type`, `entity_value`, `app_id`, `create`, `read`, `update`, `delete`, `assertion`)
VALUES
	('encounter_tracking', NULL, 'group', 'student', NULL, NULL, 1, NULL, NULL, 'LoggableFound'),
	('encounter_tracking', NULL, 'role', 'admin', NULL, NULL, 0, NULL, NULL, NULL);
