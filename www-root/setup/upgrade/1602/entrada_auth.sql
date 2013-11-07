INSERT INTO `acl_permissions` (`resource_type`, `resource_value`, `entity_type`, `entity_value`, `app_id`, `create`, `read`, `update`, `delete`, `assertion`)
VALUES
	('eportfolio', NULL, 'group:role', 'medtech:admin', 1, 1, 1, 1, 1, 'EportfolioOwner'),
	('eportfolio', NULL, 'group', 'student', 1, NULL, 1, NULL, NULL, 'EportfolioOwner'),
	('eportfolio', NULL, 'group', 'resident', 1, NULL, 1, NULL, NULL, 'EportfolioOwner'),
	('eportfolio', NULL, 'group', 'alumni', 1, NULL, 1, NULL, NULL, 'EportfolioOwner'),
	('eportfolio', NULL, 'group', 'faculty', 1, NULL, 1, NULL, NULL, 'EportfolioOwner'),
	('eportfolio-review', NULL, 'group:role', 'medtech:admin', 1, 1, 1, 1, NULL, 'EportfolioArtifactReviewer'),
	('eportfolio-artifact-entry', NULL, 'group', 'student', 1, 1, 1, 1, 1, 'EportfolioArtifactEntryOwner'),
	('eportfolio-review', NULL, 'group', 'faculty', 1, 1, 1, 1, 1, NULL),
	('eportfolio-mentor-view', NULL, 'group', 'faculty', 1, 1, 1, 1, 1, NULL),
	('eportfolio-artifact-entry', NULL, 'group', 'student', 1, 1, 1, NULL, NULL, 'EportfolioArtifactSharePermitted'),
	('eportfolio-manage', NULL, 'group:role', 'medtech:admin', 1, 1, 1, NULL, NULL, NULL),
	('eportfolio-artifact-entry', NULL, 'group', 'faculty', 1, 1, 1, NULL, NULL, NULL),
	('eportfolio-review-interface', NULL, 'group', 'faculty', 1, 1, 1, 1, 1, NULL);
