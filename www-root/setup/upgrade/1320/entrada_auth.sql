DELETE FROM `acl_permissions` WHERE `resource_type` = 'eventclosed';
DELETE FROM `acl_permissions` WHERE `resource_type` = 'course' AND `read` = 1 AND `app_id` = 1 AND `assertion` = 'ResourceOrganisation&NotGuest';

INSERT INTO `acl_permissions` (`resource_type`, `resource_value`, `entity_type`, `entity_value`, `app_id`, `create`, `read`, `update`, `delete`, `assertion`)
VALUES
	('course', NULL, 'group', 'student', NULL, NULL, 0, NULL, NULL, 'CourseEnrollment'),
	('course', NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'ResourceOrganisation&NotGuest'),
	('event', NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'EventEnrollment&NotGuest');