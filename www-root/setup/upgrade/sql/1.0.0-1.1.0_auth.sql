INSERT INTO `acl_permissions` (`resource_type`, `resource_value`, `entity_type`, `entity_value`, `app_id`, `create`, `read`, `update`, `delete`, `assertion`) VALUES('gradebook', NULL, 'role', 'pcoordinator', 1, NULL, 1, NULL, NULL, 'GradebookOwner');
INSERT INTO `acl_permissions` (`resource_type`, `resource_value`, `entity_type`, `entity_value`, `app_id`, `create`, `read`, `update`, `delete`, `assertion`) VALUES('gradebook', NULL, 'group:role', 'faculty:admin', 1, NULL, 1, NULL, NULL, 'GradebookOwner');
INSERT INTO `acl_permissions` (`resource_type`, `resource_value`, `entity_type`, `entity_value`, `app_id`, `create`, `read`, `update`, `delete`, `assertion`) VALUES('gradebook', NULL, 'group:role', 'faculty:director', 1, NULL, 1, NULL, NULL, 'GradebookOwner');