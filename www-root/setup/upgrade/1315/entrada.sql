CREATE TABLE IF NOT EXISTS `course_group_contacts` (
  `cgcontact_id` int(12) NOT NULL AUTO_INCREMENT,
  `cgroup_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `contact_order` int(6) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cgcontact_id`),
  UNIQUE KEY `event_id_2` (`cgroup_id`,`proxy_id`),
  KEY `contact_order` (`contact_order`),
  KEY `event_id` (`cgroup_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_contacts` (
  `econtact_id` int(12) NOT NULL AUTO_INCREMENT,
  `evaluation_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `contact_role` enum('reviewer','tutor','author') NOT NULL DEFAULT 'reviewer',
  `contact_order` int(6) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`econtact_id`),
  UNIQUE KEY `event_id_2` (`evaluation_id`,`proxy_id`),
  KEY `contact_order` (`contact_order`),
  KEY `event_id` (`evaluation_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_evaluator_exclusions` (
  `eeexclusion_id` int(12) NOT NULL AUTO_INCREMENT,
  `evaluation_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`eeexclusion_id`),
  UNIQUE KEY `event_id_2` (`evaluation_id`,`proxy_id`),
  KEY `event_id` (`evaluation_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_form_contacts` (
  `econtact_id` int(12) NOT NULL AUTO_INCREMENT,
  `eform_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `contact_role` enum('reviewer','author') NOT NULL DEFAULT 'author',
  `contact_order` int(6) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`econtact_id`),
  UNIQUE KEY `event_id_2` (`eform_id`,`proxy_id`),
  KEY `contact_order` (`contact_order`),
  KEY `event_id` (`eform_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_form_response_criteria` (
  `efrcriteria_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `efresponse_id` int(11) DEFAULT NULL,
  `criteria_text` text,
  PRIMARY KEY (`efrcriteria_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_form_rubric_questions` (
  `efrquestion_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `efrubric_id` int(11) DEFAULT NULL,
  `efquestion_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`efrquestion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_form_rubrics` (
  `efrubric_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `eform_id` int(11) NOT NULL,
  `rubric_title` varchar(32) DEFAULT NULL,
  `rubric_description` text,
  PRIMARY KEY (`efrubric_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_progress_clerkship_events` (
  `epcevent_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `eprogress_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `preceptor_proxy_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`epcevent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `evaluations` ADD COLUMN `evaluation_mandatory` tinyint(1) NOT NULL DEFAULT '1' AFTER `max_submittable`;
ALTER TABLE `evaluations` ADD COLUMN `allow_target_review` tinyint(1) NOT NULL DEFAULT '0' AFTER `evaluation_mandatory`;
ALTER TABLE `evaluations` ADD COLUMN `show_comments` tinyint(1) NOT NULL DEFAULT '0' AFTER `allow_target_review`;
ALTER TABLE `evaluations` ADD COLUMN `threshold_notifications_type` enum('reviewers','tutors','directors','pcoordinators','authors','disabled') NOT NULL DEFAULT 'disabled' AFTER `show_comments`;

ALTER TABLE `evaluation_form_questions` ADD COLUMN `allow_comments` tinyint(1) NOT NULL DEFAULT '1' AFTER `question_order`;
ALTER TABLE `evaluation_form_questions` ADD COLUMN `send_threshold_notifications` tinyint(1) NOT NULL DEFAULT '0' AFTER `allow_comments`;

ALTER TABLE `evaluation_progress` ADD COLUMN `target_record_id` int(11) DEFAULT NULL AFTER `etarget_id`;

ALTER TABLE `evaluation_targets` ADD COLUMN `target_type` varchar(24) NOT NULL DEFAULT 'course_id' AFTER `target_value`;

ALTER TABLE `evaluation_evaluators` MODIFY `evaluator_type` enum('proxy_id','grad_year','cohort','organisation_id', 'cgroup_id') NOT NULL DEFAULT 'proxy_id';

INSERT INTO `evaluations_lu_questiontypes` (`questiontype_id`, `questiontype_shortname`, `questiontype_title`, `questiontype_description`, `questiontype_active`) VALUES
(3, 'rubric', 'Rubric', 'The rating scale allows evaluators to rate each question based on the scale you provide, while also providing a short description of the requirements to meet each level on the scale (i.e. Level 1 to 4 of \\\"Professionalism\\\" for an assignment are qualified with what traits the learner is expected to show to meet each level, and while the same scale is used for \\\"Collaborator\\\", the requirements at each level are defined differently).', 1),
(4, 'free_text', 'Free Text Comments', 'Allows the user to be asked for a simple free-text response. This can be used to get additional details about prior questions, or to simply ask for any comments from the evaluator regarding a specific topic.', 1);

UPDATE `evaluations_lu_targets` SET `target_active` = 1 WHERE `target_shortname` IN ('student', 'rotation_core', 'rotation_elective', 'preceptor', 'peer', 'self');

UPDATE `settings` SET `value` = '1315' WHERE `shortname` = 'version_db';