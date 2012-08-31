INSERT INTO `acl_permissions` (`resource_type`, `resource_value`, `entity_type`, `entity_value`, `app_id`, `create`, `read`, `update`, `delete`, `assertion`)
VALUES
	('evaluation', NULL, 'group', 'faculty', 1, 0, 1, 0, 0, "IsEvaluated");