INSERT INTO `acl_permissions` (`resource_type`, `resource_value`,`entity_type`, `entity_value`, `app_id`, `create`, `read`, `update`, `delete`, `assertion`)
VALUES
	('coursecontent', NULL, 'group:role', 'staff:admin', NULL, NULL, 0, NULL, NULL, 'NotCourseOwner'),
	('coursecontent', NULL, 'group', 'faculty', NULL, NULL, 0, NULL, NULL, 'NotCourseOwner'),
	('gradebook', NULL, 'group', 'faculty', NULL, NULL, 1, 1, NULL, 'GradebookDropbox'),
	('gradebook', NULL, 'group:role', 'staff:admin', NULL, NULL, 1, 1, NULL, 'GradebookDropbox'),
	('assignment', NULL, 'group', 'faculty', NULL, NULL, 1, 1, NULL, 'AssignmentContact'),
	('assessment', NULL, 'group', 'faculty', NULL, NULL, NULL, 1, NULL, 'AssessmentContact'),
	('assignment', NULL, 'group:role', 'staff:admin', NULL, NULL, 1, 1, NULL, 'AssignmentContact'),
	('assessment', NULL, 'group:role', 'staff:admin', NULL, NULL, NULL, 1, NULL, 'AssessmentContact');

UPDATE `acl_permissions` 
SET `read` = NULL, `update` = NULL 
WHERE `resource_type` = 'gradebook'
AND `resource_value` IS NULL
AND `entity_type` = 'group'
AND `entity_value` = 'faculty'
AND `app_id` IS NULL
AND `assertion` = 'GradebookOwner';

DELETE FROM `acl_permissions` WHERE `resource_type` IN ('task', 'taskverification', 'tasktab');

