INSERT INTO `acl_permissions` (`resource_type`, `resource_value`, `entity_type`, `entity_value`, `app_id`, `create`, `read`, `update`, `delete`, `assertion`)
VALUES
	('coursecontent', NULL, 'group:role', 'staff:admin', 1, NULL, 0, NULL, NULL, 'NotCourseOwner'),
	('coursecontent', NULL, 'group', 'faculty', 1, NULL, 0, NULL, NULL, 'NotCourseOwner'),
	('gradebook', NULL, 'group', 'faculty', 1, NULL, 1, 1, NULL, 'GradebookDropbox'),
	('assignment', NULL, 'group', 'faculty', 1, NULL, 1, 1, NULL, 'AssignmentContact'),
	('assessment', NULL, 'group', 'faculty', 1, NULL, NULL, 1, NULL, 'AssessmentContact'),
	('assignment', NULL, 'group:role', 'staff:admin', 1, NULL, 1, 1, NULL, 'AssignmentContact'),
	('assessment', NULL, 'group:role', 'staff:admin', 1, NULL, NULL, 1, NULL, 'AssessmentContact'),
	('gradebook', NULL, 'group:role', 'staff:admin', 1, NULL, 1, 1, NULL, 'GradebookDropbox'),
	('gradebook', NULL, 'group:role', 'staff:admin', 1, NULL, 1, NULL, NULL, NULL),
	('gradebook', NULL, 'group', 'faculty', 1, NULL, 1, NULL, NULL, NULL);

DELETE FROM `acl_permissions` WHERE `resource_type` IN ('task', 'taskverification', 'tasktab');
