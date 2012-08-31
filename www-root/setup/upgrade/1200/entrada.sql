ALTER TABLE `event_quizzes` ADD COLUMN `content_type` enum('event','community_page') NOT NULL DEFAULT 'event' AFTER `equiz_id`;
ALTER TABLE `event_quizzes` CHANGE `equiz_id` `aquiz_id` int(12) NOT NULL AUTO_INCREMENT;
ALTER TABLE `event_quizzes` CHANGE `event_id` `content_id` int(12) NOT NULL DEFAULT '0';
RENAME TABLE `event_quizzes` TO `attached_quizzes`;

ALTER TABLE  `attached_quizzes` ADD INDEX (`content_id`, `release_date`, `release_until`);

ALTER TABLE `event_quiz_progress` ADD COLUMN `content_type` enum('event','community_page') NOT NULL DEFAULT 'event' AFTER `equiz_id`;
ALTER TABLE `event_quiz_progress` CHANGE `equiz_id` `aquiz_id` int(12) unsigned NOT NULL;
ALTER TABLE `event_quiz_progress` CHANGE `event_id` `content_id` int(12) NOT NULL DEFAULT '0';
ALTER TABLE `event_quiz_progress` CHANGE `eqprogress_id` `qprogress_id` int(12) unsigned NOT NULL AUTO_INCREMENT;
RENAME TABLE `event_quiz_progress` TO `quiz_progress`;

ALTER TABLE `event_quiz_responses` ADD COLUMN `content_type` enum('event','community_page') NOT NULL DEFAULT 'event' AFTER `equiz_id`;
ALTER TABLE `event_quiz_responses` CHANGE `equiz_id` `aquiz_id` int(12) unsigned NOT NULL;
ALTER TABLE `event_quiz_responses` CHANGE `event_id` `content_id` int(12) NOT NULL DEFAULT '0';
ALTER TABLE `event_quiz_responses` CHANGE `eqresponse_id` `qpresponse_id` int(12) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `event_quiz_responses` CHANGE `eqprogress_id` `qprogress_id` int(12) unsigned NOT NULL;
RENAME TABLE `event_quiz_responses` TO `quiz_progress_responses`;

ALTER TABLE `quiz_questions` ADD COLUMN `question_active` int(1) NOT NULL DEFAULT '1' AFTER `question_order`;
ALTER TABLE `quiz_question_responses` ADD COLUMN `response_active` int(1) NOT NULL DEFAULT '1' AFTER `response_feedback`;

ALTER TABLE `student_awards_external` ADD COLUMN `comment` varchar(4096) default NULL;
ALTER TABLE `student_community_health_and_epidemiology` ADD COLUMN `comment` varchar(4096) default NULL;
ALTER TABLE `student_contributions` ADD COLUMN `comment` varchar(4096) default NULL;
ALTER TABLE `student_critical_enquiries` ADD COLUMN `comment` varchar(4096) default NULL;
ALTER TABLE `student_research` ADD COLUMN `comment` varchar(4096) default NULL;
ALTER TABLE `student_observerships` drop column `start`;
ALTER TABLE `student_observerships` drop column `end`;
ALTER TABLE `student_observerships` add column (
  `start` int(11) NOT NULL,
  `end` int(11) default NULL,
  `preceptor_firstname` varchar(256) default NULL,
  `preceptor_lastname` varchar(256) default NULL,
  `preceptor_proxy_id` int(12) unsigned default NULL);
  
ALTER TABLE `student_mspr` ADD COLUMN `carms_number` int(10) unsigned default NULL;
ALTER TABLE `student_mspr` CHANGE `generated` `generated` BIGINT(64) NULL DEFAULT NULL;
ALTER TABLE `student_mspr` CHANGE `closed` `closed` BIGINT(64) NULL DEFAULT NULL;

INSERT INTO `communities_modules` (`module_id`,`module_shortname`,`module_version`,`module_title`,`module_description`,`module_active`,`module_permissions`,`updated_date`,`updated_by`)
VALUES (7, 'quizzes', '1.0.0', 'Quizzes', 'This module allows communities to create their own quizzes for summative or formative evaluation.', 1, 'a:1:{s:5:\"index\";i:0;}', 1216256830, 3499);
	
INSERT INTO `community_modules` (`community_id`, `module_id`, `module_active`)
SELECT `community_id`, 7, 1 FROM `communities` WHERE `community_active` = 1;

CREATE TABLE IF NOT EXISTS `evaluations` (
  `evaluation_id` int(12) NOT NULL AUTO_INCREMENT,
  `eform_id` int(12) NOT NULL,
  `evaluation_title` varchar(128) NOT NULL,
  `evaluation_description` text NOT NULL,
  `evaluation_active` tinyint(1) NOT NULL,
  `evaluation_start` bigint(64) NOT NULL,
  `evaluation_finish` bigint(64) NOT NULL,
  `min_submittable` tinyint(1) NOT NULL DEFAULT '1',
  `max_submittable` tinyint(1) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL,
  `release_until` bigint(64) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` bigint(64) NOT NULL,
  PRIMARY KEY (`evaluation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluations_lu_questiontypes` (
  `questiontype_id` int(12) NOT NULL AUTO_INCREMENT,
  `questiontype_shortname` varchar(32) NOT NULL,
  `questiontype_title` varchar(64) NOT NULL,
  `questiontype_description` text NOT NULL,
  `questiontype_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`questiontype_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `evaluations_lu_questiontypes` (`questiontype_id`, `questiontype_shortname`, `questiontype_title`, `questiontype_description`, `questiontype_active`) VALUES
(1, 'matrix_single', 'Choice Matrix (single response)', 'The rating scale allows evaluators to rate each question based on the scale you provide (i.e. 1 = Not Demonstrated, 2 = Needs Improvement, 3 = Satisfactory, 4 = Above Average).', 1),
(2, 'descriptive_text', 'Descriptive Text', 'Allows you to add descriptive text information to your evaluation form. This could be instructions or other details relevant to the question or series of questions.', 1);

CREATE TABLE IF NOT EXISTS `evaluations_lu_targets` (
  `target_id` int(11) NOT NULL AUTO_INCREMENT,
  `target_shortname` varchar(32) NOT NULL,
  `target_title` varchar(64) NOT NULL,
  `target_description` text NOT NULL,
  `target_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`target_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `evaluations_lu_targets` (`target_id`, `target_shortname`, `target_title`, `target_description`, `target_active`) VALUES
(1, 'course', 'Course Evaluation', '', 1),
(2, 'teacher', 'Teacher Evaluation', '', 1),
(3, 'student', 'Student Assessment', '', 0),
(4, 'rotation_core', 'Clerkship Core Rotation Evaluation', '', 0),
(5, 'rotation_elective', 'Clerkship Elective Rotation Evaluation', '', 0),
(6, 'preceptor', 'Clerkship Preceptor Evaluation', '', 0),
(7, 'peer', 'Peer Assessment', '', 0),
(8, 'self', 'Self Assessment', '', 0);

CREATE TABLE IF NOT EXISTS `evaluation_evaluators` (
  `eevaluator_id` int(12) NOT NULL AUTO_INCREMENT,
  `evaluation_id` int(12) NOT NULL,
  `evaluator_type` enum('proxy_id','grad_year','organisation_id') NOT NULL,
  `evaluator_value` int(12) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`eevaluator_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_forms` (
  `eform_id` int(12) NOT NULL AUTO_INCREMENT,
  `target_id` int(12) NOT NULL,
  `form_parent` int(12) NOT NULL,
  `form_title` varchar(64) NOT NULL,
  `form_description` text NOT NULL,
  `form_active` tinyint(1) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`eform_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_form_questions` (
  `efquestion_id` int(12) NOT NULL AUTO_INCREMENT,
  `eform_id` int(121) NOT NULL,
  `questiontype_id` int(12) NOT NULL,
  `question_text` longtext NOT NULL,
  `question_order` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`efquestion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_form_responses` (
  `efresponse_id` int(12) NOT NULL AUTO_INCREMENT,
  `efquestion_id` int(12) NOT NULL,
  `response_text` longtext NOT NULL,
  `response_order` tinyint(3) NOT NULL DEFAULT '0',
  `response_is_html` tinyint(1) NOT NULL DEFAULT '0',
  `minimum_passing_level` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`efresponse_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_progress` (
  `eprogress_id` int(12) NOT NULL AUTO_INCREMENT,
  `evaluation_id` int(12) NOT NULL,
  `etarget_id` int(12) NOT NULL,
  `proxy_id` int(12) NOT NULL,
  `progress_value` enum('inprogress','complete','cancelled') NOT NULL DEFAULT 'inprogress',
  `updated_date` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`eprogress_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_responses` (
  `eresponse_id` int(12) NOT NULL AUTO_INCREMENT,
  `eprogress_id` int(12) NOT NULL,
  `eform_id` int(12) NOT NULL,
  `proxy_id` int(12) NOT NULL,
  `efquestion_id` int(12) NOT NULL,
  `efresponse_id` int(12) NOT NULL,
  `comments` text NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`eresponse_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_targets` (
  `etarget_id` int(12) NOT NULL AUTO_INCREMENT,
  `evaluation_id` int(12) NOT NULL,
  `target_id` int(11) NOT NULL,
  `target_value` int(12) NOT NULL,
  `target_active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`etarget_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `assessments` CHANGE `grad_year` `grad_year` varchar(35) NOT NULL DEFAULT '';

ALTER TABLE `community_pages` CHANGE `page_url` `page_url` varchar(329) NOT NULL;
ALTER TABLE `community_pages` ADD INDEX (`cpage_id`, `community_id`, `page_url`, `page_active`);
ALTER TABLE `community_pages` ADD INDEX (`community_id`, `parent_id`, `page_url`, `page_active`);
ALTER TABLE `community_pages` ADD INDEX (`page_order`);
ALTER TABLE `community_pages` ADD INDEX (`community_id`, `page_url`);
ALTER TABLE `community_pages` ADD INDEX (`community_id`, `page_type`);
ALTER TABLE `community_page_options` ADD INDEX (`cpage_id`);
ALTER TABLE `community_members` ADD INDEX (`community_id`, `proxy_id`, `member_active`);

CREATE TABLE IF NOT EXISTS `events` (
  `event_id` int(12) NOT NULL AUTO_INCREMENT,
  `recurring_id` int(12) DEFAULT '0',
  `region_id` int(12) DEFAULT '0',
  `course_id` int(12) NOT NULL DEFAULT '0',
  `event_phase` varchar(12) DEFAULT NULL,
  `event_title` varchar(255) NOT NULL,
  `event_description` text,
  `event_goals` text,
  `event_objectives` text,
  `event_message` text,
  `event_location` varchar(64) DEFAULT NULL,
  `event_start` bigint(64) NOT NULL,
  `event_finish` bigint(64) NOT NULL,
  `event_duration` int(64) NOT NULL,
  `release_date` bigint(64) NOT NULL,
  `release_until` bigint(64) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY  (`event_id`),
  KEY `course_id` (`course_id`),
  KEY `region_id` (`region_id`),
  KEY `recurring_id` (`recurring_id`),
  KEY `release_date` (`release_date`,`release_until`),
  KEY `event_start` (`event_start`,`event_duration`),
  KEY `event_start_2` (`event_start`,`event_finish`),
  KEY `event_phase` (`event_phase`),
  FULLTEXT KEY `event_title` (`event_title`,`event_description`,`event_goals`,`event_objectives`,`event_message`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `events_lu_topics` (
  `topic_id` int(12) NOT NULL AUTO_INCREMENT,
  `ed10_id` int(12) NULL,
  `ed11_id` int(12) NULL,
  `topic_name` varchar(60) NOT NULL,
  `topic_description` text,
  `topic_type` enum('ed10','ed11','other') NOT NULL DEFAULT 'other',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY  (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `events_lu_topics` (`ed10_id`, `topic_name`, `topic_description`, `topic_type`, `updated_date`, `updated_by`)
SELECT `ed10_id`, `topic_name`, `topic_description`, 'ed10', `updated_date`, `updated_by` FROM `events_lu_ed10` ORDER BY `ed10_id` ASC;

INSERT INTO `events_lu_topics` (`ed11_id`, `topic_name`, `topic_description`, `topic_type`, `updated_date`, `updated_by`)
SELECT `ed11_id`, `topic_name`, `topic_description`, 'ed11', `updated_date`, `updated_by` FROM `events_lu_ed11` ORDER BY `ed11_id` ASC;

CREATE TABLE IF NOT EXISTS `event_topics` (
  `etopic_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `topic_id` tinyint(1) DEFAULT '0',
  `topic_coverage`  enum('major','minor') NOT NULL,
  `topic_time` varchar(25) DEFAULT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY  (`etopic_id`),
  KEY `event_id` (`event_id`),
  KEY `topic_id` (`topic_id`),
  KEY `topic_coverage` (`topic_coverage`),
  KEY `topic_time` (`topic_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `event_topics` (`event_id`, `topic_id`, `topic_coverage`, `topic_time`, `updated_date`, `updated_by`)
SELECT a.`event_id`, b.`topic_id`, IF(a.`major_topic`, 'major', 'minor') AS `topic_coverage`, a.`minor_desc` AS `topic_time`, a.`updated_date`, a.`updated_by`
FROM `event_ed10` AS a
LEFT JOIN `events_lu_topics` AS b
ON b.`ed10_id` = a.`ed10_id`;

INSERT INTO `event_topics` (`event_id`, `topic_id`, `topic_coverage`, `topic_time`, `updated_date`, `updated_by`)
SELECT a.`event_id`, b.`topic_id`, IF(a.`major_topic`, 'major', 'minor') AS `topic_coverage`, a.`minor_desc` AS `topic_time`, a.`updated_date`, a.`updated_by`
FROM `event_ed11` AS a
LEFT JOIN `events_lu_topics` AS b
ON b.`ed11_id` = a.`ed11_id`;

ALTER TABLE `events_lu_topics` DROP `ed10_id`;
ALTER TABLE `events_lu_topics` DROP `ed11_id`;

RENAME TABLE `events_lu_ed10` TO `backup_events_lu_ed10`;
RENAME TABLE `events_lu_ed11` TO `backup_events_lu_ed11`;
RENAME TABLE `event_ed10` TO `backup_event_ed10`;
RENAME TABLE `event_ed11` TO `backup_event_ed11`;

INSERT INTO `ar_lu_on_call_locations` (`id`,`on_call_location`) VALUES ('', 'Other (specify)');

ALTER TABLE `assessments` ADD COLUMN `grade_weighting` int(11) NOT NULL default '0' AFTER `numeric_grade_points_total`;

CREATE TABLE `assessment_exceptions` (
  `aexception_id` int(12) NOT NULL auto_increment,
  `assessment_id` int(12) NOT NULL,
  `proxy_id` int(12) NOT NULL,
  `grade_weighting` int(11) NOT NULL default '0',
  PRIMARY KEY  (`aexception_id`),
  KEY `proxy_id` (`assessment_id`,`proxy_id`),
  KEY `assessment_id` (`assessment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `community_discussions` ADD KEY `page_id` (`cdiscussion_id`,`cpage_id`,`community_id`);
ALTER TABLE `community_discussions` ADD KEY `community_id2` (`community_id`,`forum_active`,`cpage_id`,`forum_order`,`forum_title`);

ALTER TABLE `community_discussion_topics` ADD KEY `community_id` (`cdtopic_id`,`community_id`);
ALTER TABLE `community_discussion_topics` ADD KEY `cdtopic_parent` (`cdtopic_parent`,`community_id`);
ALTER TABLE `community_discussion_topics` ADD KEY `user` (`cdiscussion_id`,`community_id`,`topic_active`,`cdtopic_parent`,`proxy_id`,`release_date`,`release_until`);
ALTER TABLE `community_discussion_topics` ADD KEY `admin` (`cdiscussion_id`,`community_id`,`topic_active`,`cdtopic_parent`);
ALTER TABLE `community_discussion_topics` ADD KEY `post` (`proxy_id`,`community_id`,`cdtopic_id`,`cdtopic_parent`,`topic_active`);
ALTER TABLE `community_discussion_topics` ADD KEY `release` (`proxy_id`,`community_id`,`cdtopic_parent`,`topic_active`,`release_date`);
ALTER TABLE `community_discussion_topics` ADD KEY `community` (`cdtopic_id`,`community_id`);

ALTER TABLE  `global_lu_objectives` ADD INDEX (`objective_code`);

ALTER TABLE `tasks`
 ADD COLUMN `verification_type` enum('faculty','other','none') NOT NULL default 'none',
 ADD COLUMN `faculty_selection_policy` enum('off','allow','require') NOT NULL default 'allow',
 ADD COLUMN `completion_comment_policy` enum('no_comments','require_comments','allow_comments') NOT NULL default 'allow_comments',
 ADD COLUMN `rejection_comment_policy` enum('no_comments','require_comments','allow_comments') NOT NULL default 'allow_comments',
 ADD COLUMN `verification_notification_policy` smallint(5) unsigned NOT NULL default '0';

UPDATE `tasks` SET
 `verification_type`='faculty'
 WHERE `require_verification`=1;

UPDATE `tasks` SET
 `verification_notification_policy`=1;

ALTER TABLE `tasks`
 DROP COLUMN `require_verification`;

CREATE TABLE IF NOT EXISTS `task_associated_faculty` (
  `task_id` int(12) unsigned NOT NULL,
  `faculty_id` int(12) unsigned NOT NULL,
  PRIMARY KEY  (`task_id`,`faculty_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `task_completion`
 ADD COLUMN `faculty_id` int(12) unsigned default NULL,
 ADD COLUMN `completion_comment` text,
 ADD COLUMN `rejection_comment` text,
 ADD COLUMN `rejection_date` bigint(64) default NULL;

CREATE TABLE IF NOT EXISTS `task_verifiers` (
  `task_id` int(12) unsigned NOT NULL,
  `verifier_id` int(12) unsigned NOT NULL,
  PRIMARY KEY  (`task_id`,`verifier_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `meta_types` (
  `meta_type_id` int(10) unsigned NOT NULL auto_increment,
  `label` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `parent_type_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`meta_type_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `meta_types` (`meta_type_id`, `label`, `description`, `parent_type_id`) VALUES
(1, 'N95 Mask Fit', 'Make, Model, and size definition of required N95 masks.', NULL),
(2, 'Police Record Check', 'Police Record Checks to verify background as clear of events which could prevent placement in hospitals or clinics.', NULL),
(3, 'Full', 'Full record check. Due to differences in how police departments handle reporting of background checks, vulnerable sector screening (VSS) is a separate type of record', 2),
(4, 'Vulnerable Sector Screening', 'Required for placement in hospitals or clinics. May be included in full police record checks or may be a separate document.', 2),
(5, 'Assertion', 'Yearly or bi-yearly assertion that prior police background checks remain valid.', 2),
(6, 'Immunization/Health Check', '', NULL),
(7, 'Hepatitis B', '', 6),
(8, 'Tuberculosis', '', 6),
(9, 'Measles', '', 6),
(10, 'Mumps', '', 6),
(11, 'Rubella', '', 6),
(12, 'Tetanus/Diptheria', '', 6),
(13, 'Polio', '', 6),
(14, 'Varicella', '', 6),
(15, 'Pertussis', '', 6),
(16, 'Influenza', 'Each student is required to obtain an annual influenza immunization. The Ontario government provides the influenza vaccine free to all citizens during the flu season. Students will be required to follow Public Health guidelines put forward for health care professionals. Thia immunization must be received by December 1st each academic year and documentation forwarded to the UGME office by the student', 6),
(17, 'Hepatitis C', '', 6),
(18, 'HIV', '', 6),
(19, 'Cardiac Life Support', '', NULL),
(20, 'Basic', '', 19),
(21, 'Advanced', '', 19);

CREATE TABLE IF NOT EXISTS `meta_type_relations` (
  `meta_data_relation_id` int(11) NOT NULL auto_increment,
  `meta_type_id` int(10) unsigned default NULL,
  `entity_type` varchar(63) NOT NULL,
  `entity_value` varchar(63) NOT NULL,
  PRIMARY KEY  (`meta_data_relation_id`),
  UNIQUE KEY `meta_type_id` (`meta_type_id`,`entity_type`,`entity_value`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `meta_type_relations` (`meta_data_relation_id`, `meta_type_id`, `entity_type`, `entity_value`) VALUES
(1, 1, 'organisation:group', '1:student'),
(2, 7, 'organisation:group', '1:student'),
(3, 3, 'organisation:group', '1:student'),
(4, 4, 'organisation:group', '1:student'),
(5, 5, 'organisation:group', '1:student'),
(6, 8, 'organisation:group', '1:student'),
(7, 9, 'organisation:group', '1:student'),
(8, 10, 'organisation:group', '1:student'),
(9, 11, 'organisation:group', '1:student'),
(10, 12, 'organisation:group', '1:student'),
(11, 13, 'organisation:group', '1:student'),
(12, 14, 'organisation:group', '1:student'),
(13, 15, 'organisation:group', '1:student'),
(14, 16, 'organisation:group', '1:student'),
(15, 17, 'organisation:group', '1:student'),
(16, 18, 'organisation:group', '1:student'),
(17, 20, 'organisation:group', '1:student'),
(18, 21, 'organisation:group', '1:student');

CREATE TABLE IF NOT EXISTS `meta_values` (
  `meta_value_id` int(10) unsigned NOT NULL auto_increment,
  `meta_type_id` int(10) unsigned NOT NULL,
  `proxy_id` int(10) unsigned NOT NULL,
  `data_value` varchar(255) NOT NULL,
  `value_notes` text NOT NULL,
  `effective_date` bigint(20) default NULL,
  `expiry_date` bigint(20) default NULL,
  PRIMARY KEY  (`meta_value_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `settings` (`shortname`, `value`) VALUES ('version_entrada', '1.2.0.1');
UPDATE `settings` SET `value` = '1200' WHERE `shortname` = 'version_db';

CREATE TABLE IF NOT EXISTS `ar_undergraduate_nonmedical_teaching` (
  `undergraduate_nonmedical_teaching_id` int(11) NOT NULL auto_increment,
  `course_number` varchar(25) NOT NULL DEFAULT '',
  `course_name` text NOT NULL,
  `assigned` char(3) NOT NULL DEFAULT '',
  `lec_enrollment` int(11) NOT NULL DEFAULT '0',
  `lec_hours` int(11) NOT NULL DEFAULT '0',
  `lab_enrollment` int(11) NOT NULL DEFAULT '0',
  `lab_hours` int(11) NOT NULL DEFAULT '0',
  `tut_enrollment` int(11) NOT NULL DEFAULT '0',
  `tut_hours` int(11) NOT NULL DEFAULT '0',
  `sem_enrollment` int(11) NOT NULL DEFAULT '0',
  `sem_hours` int(11) NOT NULL DEFAULT '0',
  `coord_enrollment` int(11) NOT NULL DEFAULT '0',
  `pbl_hours` int(11) NOT NULL DEFAULT '0',
  `comments` text,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY  (`undergraduate_nonmedical_teaching_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;