
INSERT INTO `acl_permissions` (`resource_type`, `resource_value`, `entity_type`, `entity_value`, `app_id`, `create`, `read`, `update`, `delete`, `assertion`) VALUES
('evaluation', NULL, 'group', 'faculty', 1, 1, 1, 1, NULL, 'EvaluationReviewer'),
('evaluationform', NULL, 'group', 'faculty', 1, 1, 1, 1, NULL, 'EvaluationFormAuthor');