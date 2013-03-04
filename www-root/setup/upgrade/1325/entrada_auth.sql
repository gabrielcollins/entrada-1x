INSERT INTO `acl_permissions` (`resource_type`, `resource_value`, `entity_type`, `entity_value`, `app_id`, `create`, `read`, `update`, `delete`, `assertion`)
VALUES
	('evaluationquestion', NULL, 'group', 'faculty', 1, 1, 1, 1, NULL, NULL),
	('evaluationquestion', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL);