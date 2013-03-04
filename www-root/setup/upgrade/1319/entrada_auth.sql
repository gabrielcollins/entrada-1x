INSERT INTO `acl_permissions` (`resource_type`,`resource_value`,`entity_type`,`entity_value`,`app_id`,`create`,`read`,`update`,`delete`,`assertion`)
VALUES
	('eventclosed', NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'EventEnrollment&NotGuest');