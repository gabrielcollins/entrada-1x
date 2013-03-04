ALTER TABLE `evaluation_form_questions` ADD COLUMN `equestion_id` int(12) NOT NULL AFTER `eform_id`;

CREATE TABLE `evaluations_lu_questions` (
  `equestion_id` int(12) NOT NULL AUTO_INCREMENT,
  `efquestion_id` int(12) NOT NULL DEFAULT '0',
  `question_parent_id` int(12) NOT NULL DEFAULT '0',
  `questiontype_id` int(12) NOT NULL,
  `question_text` longtext NOT NULL,
  `allow_comments` tinyint(1) NOT NULL DEFAULT '1',
  `question_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`equestion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `evaluations_lu_questions` (`efquestion_id`, `questiontype_id`, `question_text`, `allow_comments`) (SELECT `efquestion_id`, `questiontype_id`, `question_text`, `allow_comments` FROM `evaluation_form_questions`);

UPDATE `evaluation_form_questions` AS a SET a.`equestion_id` = (SELECT b.`equestion_id` FROM `evaluations_lu_questions` AS b WHERE b.`efquestion_id` = a.`efquestion_id`);

ALTER TABLE `evaluations_lu_questions` DROP COLUMN `efquestion_id`;
ALTER TABLE `evaluation_form_questions` DROP COLUMN `question_text`;
ALTER TABLE `evaluation_form_questions` DROP COLUMN `questiontype_id`;

CREATE TABLE `evaluations_lu_rubrics` (
  `erubric_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `rubric_title` varchar(32) DEFAULT NULL,
  `rubric_description` text,
  `efrubric_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`erubric_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `evaluations_lu_rubrics` 
(`rubric_title`, `rubric_description`, `efrubric_id`) (
	SELECT `rubric_title`, `rubric_description`, `efrubric_id` 
	FROM `evaluation_form_rubrics`
);

CREATE TABLE `evaluation_rubric_questions` (
  `efrquestion_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `erubric_id` int(11) DEFAULT NULL,
  `equestion_id` int(11) DEFAULT NULL,
  `question_order` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`efrquestion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `evaluation_rubric_questions` 
(`erubric_id`, `equestion_id`, `question_order`) (
	SELECT b.`erubric_id`, c.`equestion_id`, c.`question_order` 
	FROM `evaluation_form_rubric_questions` AS a
	JOIN `evaluations_lu_rubrics` AS b
	ON a.`efrubric_id` = b.`efrubric_id`
	JOIN `evaluation_form_questions` AS c
	ON a.`efquestion_id` = c.`efquestion_id`
);

ALTER TABLE `evaluations_lu_rubrics` DROP COLUMN `efrubric_id`;

CREATE TABLE `evaluations_lu_question_responses` (
  `eqresponse_id` int(12) NOT NULL AUTO_INCREMENT,
  `efresponse_id` int(12) NOT NULL,
  `equestion_id` int(12) NOT NULL,
  `response_text` longtext NOT NULL,
  `response_order` tinyint(3) NOT NULL DEFAULT '0',
  `response_is_html` tinyint(1) NOT NULL DEFAULT '0',
  `minimum_passing_level` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`eqresponse_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `evaluations_lu_question_responses`
(`efresponse_id`, `equestion_id`, `response_text`, `response_order`, `response_is_html`, `minimum_passing_level`) (
	SELECT a.`efresponse_id`, b.`equestion_id`, a.`response_text`, a.`response_order`, a.`response_is_html`, a.`minimum_passing_level` 
	FROM `evaluation_form_responses` AS a
	JOIN `evaluation_form_questions` AS b
	ON a.`efquestion_id` = b.`efquestion_id`
);

CREATE TABLE `evaluations_lu_question_response_criteria` (
  `eqrcriteria_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `eqresponse_id` int(11) DEFAULT NULL,
  `criteria_text` text,
  PRIMARY KEY (`eqrcriteria_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `evaluations_lu_question_response_criteria` 
(`eqresponse_id`, `criteria_text`) (
	SELECT b.`eqresponse_id`, a.`criteria_text` 
	FROM `evaluation_form_response_criteria` AS a
	JOIN `evaluations_lu_question_responses` AS b
	ON a.`efresponse_id` = b.`efresponse_id`
);

ALTER TABLE `evaluation_responses` ADD COLUMN `eqresponse_id` int(12) NOT NULL AFTER `efresponse_id`;

UPDATE `evaluation_responses` AS a SET a.`eqresponse_id` = (SELECT b.`eqresponse_id` FROM `evaluations_lu_question_responses` AS b WHERE b.`efresponse_id` = a.`efresponse_id`);

ALTER TABLE `evaluations` ADD COLUMN `allow_target_request` tinyint(1) NOT NULL DEFAULT '0' AFTER `allow_target_review`;
ALTER TABLE `evaluations` ADD COLUMN `allow_repeat_targets` tinyint(1) NOT NULL DEFAULT '0' AFTER `allow_target_request`;
ALTER TABLE `evaluations_lu_question_responses` DROP COLUMN `efresponse_id`;
ALTER TABLE `evaluation_responses` DROP COLUMN `efresponse_id`;

CREATE TABLE `evaluations_related_questions` (
  `erubric_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `related_equestion_id` int(11) unsigned NOT NULL,
  `equestion_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`erubric_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `evaluation_form_response_criteria`;
DROP TABLE IF EXISTS `evaluation_form_responses`;
DROP TABLE IF EXISTS `evaluation_form_rubric_questions`;
DROP TABLE IF EXISTS `evaluation_form_rubrics`;

UPDATE `settings` SET `value` = '1325' WHERE `shortname` = 'version_db';