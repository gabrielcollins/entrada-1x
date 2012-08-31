ALTER TABLE `course_audience` ADD COLUMN `cperiod_id` int(11) NOT NULL AFTER `audience_value`;

ALTER TABLE `groups` MODIFY `group_type` enum('course_list','cohort') NOT NULL DEFAULT 'course_list';

ALTER TABLE `poll_questions` ADD COLUMN `poll_target_type` enum('group', 'grad_year', 'cohort') NOT NULL AFTER `poll_id`;
ALTER TABLE `event_audience` MODIFY `audience_type` enum('proxy_id','grad_year','cohort','organisation_id','group_id','course_id') NOT NULL;
ALTER TABLE `task_recipients` MODIFY `recipient_type` enum('user','group','grad_year','cohort','organisation') NOT NULL;
ALTER TABLE `evaluation_evaluators` MODIFY `evaluator_type` enum('proxy_id','grad_year','cohort','organisation_id') NOT NULL;
ALTER TABLE `assessments` CHANGE `grad_year` `cohort` varchar(35) NOT NULL;

UPDATE `settings` SET `value` = '1218' WHERE `shortname` = 'version_db';