ALTER TABLE `evaluations` ADD COLUMN `organisation_id` int(12) unsigned NOT NULL DEFAULT '0' AFTER `eform_id`;
ALTER TABLE `evaluation_forms` ADD COLUMN `organisation_id` int(12) unsigned NOT NULL DEFAULT '0' AFTER `eform_id`;
ALTER TABLE `evaluations_lu_questions` ADD COLUMN `organisation_id` int(12) unsigned NOT NULL DEFAULT '0' AFTER `equestion_id`;

UPDATE `evaluations` AS a SET a.`organisation_id` = (SELECT `organisation_id` FROM `entrada_auth`.`user_data` WHERE `id` = a.`updated_by`);

UPDATE `evaluation_forms` AS a SET a.`organisation_id` = (SELECT `organisation_id` FROM `entrada_auth`.`user_data` WHERE `id` = a.`updated_by`);

UPDATE `evaluations_lu_questions` AS a 
JOIN `evaluation_form_questions` AS b
ON a.`equestion_id` = b.`equestion_id`
JOIN `evaluation_forms` AS c
ON b.`eform_id` = c.`eform_id`
SET a.`organisation_id` = (SELECT `organisation_id` FROM `entrada_auth`.`user_data` WHERE `id` = c.`updated_by`);

ALTER TABLE `evaluations`
CHANGE `min_submittable` `min_submittable` tinyint(3) NOT NULL DEFAULT '1';

ALTER TABLE `evaluations`
CHANGE `max_submittable` `max_submittable` tinyint(3) NOT NULL DEFAULT '1';

INSERT INTO `evaluations_lu_questiontypes` (`questiontype_id`, `questiontype_shortname`, `questiontype_title`, `questiontype_description`, `questiontype_active`)
VALUES
	('selectbox', 'Drop Down (single response)', 'The dropdown allows evaluators to answer each question by choosing one of up to 100 options which have been provided to populate a select box.', 1),
	('vertical_matrix', 'Vertical Choice Matrix (single response)', 'The rating scale allows evaluators to rate each question based on the scale you provide (i.e. 1 = Not Demonstrated, 2 = Needs Improvement, 3 = Satisfactory, 4 = Above Average).', 1);
	
UPDATE `evaluations_lu_questiontypes` SET `questiontype_title` = 'Horizontal Choice Matrix (single response)' WHERE `questiontype_shortname` = 'question_matrix';

UPDATE `settings` SET `value` = '1603' WHERE `shortname` = 'version_db';