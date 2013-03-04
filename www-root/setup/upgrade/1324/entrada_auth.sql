INSERT INTO `acl_permissions` (`resource_type`, `resource_value`, `entity_type`, `entity_value`, `app_id`, `create`, `read`, `update`, `delete`, `assertion`)
VALUES
	('event', NULL, 'group', 'student', NULL, NULL, 0, NULL, NULL, 'NotEventEnrollment');