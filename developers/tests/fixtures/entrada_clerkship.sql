SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
CREATE DATABASE `test_entrada_clerkship` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `test_entrada_clerkship`;

CREATE TABLE `apartment_accounts` (
  `aaccount_id` int(12) NOT NULL AUTO_INCREMENT,
  `apartment_id` int(12) NOT NULL DEFAULT '0',
  `aaccount_company` varchar(128) NOT NULL DEFAULT '',
  `aaccount_custnumber` varchar(128) NOT NULL DEFAULT '',
  `aaccount_details` text NOT NULL,
  `updated_last` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  `account_status` varchar(12) NOT NULL DEFAULT '',
  PRIMARY KEY (`aaccount_id`),
  KEY `apartment_id` (`apartment_id`),
  KEY `aaccount_company` (`aaccount_company`),
  KEY `aaccount_custnumber` (`aaccount_custnumber`),
  KEY `account_status` (`account_status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE `apartment_photos` (
  `aphoto_id` int(12) NOT NULL AUTO_INCREMENT,
  `apartment_id` int(12) NOT NULL DEFAULT '0',
  `aphoto_name` varchar(64) NOT NULL DEFAULT '',
  `aphoto_type` varchar(32) NOT NULL DEFAULT '',
  `aphoto_size` int(32) NOT NULL DEFAULT '0',
  `aphoto_desc` text NOT NULL,
  PRIMARY KEY (`aphoto_id`),
  KEY `apartment_id` (`apartment_id`),
  KEY `aphoto_name` (`aphoto_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE `apartment_schedule` (
  `aschedule_id` int(12) NOT NULL AUTO_INCREMENT,
  `apartment_id` int(12) NOT NULL DEFAULT '0',
  `event_id` int(12) NOT NULL DEFAULT '0',
  `econtact_id` int(12) NOT NULL DEFAULT '0',
  `econtact_notes` text NOT NULL,
  `inhabiting_start` bigint(64) NOT NULL DEFAULT '0',
  `inhabiting_finish` bigint(64) NOT NULL DEFAULT '0',
  `updated_last` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  `aschedule_status` varchar(12) NOT NULL DEFAULT '',
  PRIMARY KEY (`aschedule_id`),
  KEY `apartment_id` (`apartment_id`),
  KEY `event_id` (`event_id`),
  KEY `econtact_id` (`econtact_id`),
  KEY `inhabiting_start` (`inhabiting_start`),
  KEY `inhabiting_finish` (`inhabiting_finish`),
  KEY `aschedule_status` (`aschedule_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `apartments` (
  `apartment_id` int(12) NOT NULL AUTO_INCREMENT,
  `region_id` int(12) NOT NULL DEFAULT '0',
  `apartment_title` varchar(86) NOT NULL DEFAULT '',
  `apartment_number` varchar(12) NOT NULL DEFAULT '',
  `apartment_address` varchar(86) NOT NULL DEFAULT '',
  `apartment_city` varchar(48) NOT NULL DEFAULT '',
  `apartment_province` varchar(24) NOT NULL DEFAULT '',
  `apartment_postcode` varchar(12) NOT NULL DEFAULT '',
  `apartment_country` varchar(48) NOT NULL DEFAULT '',
  `apartment_phone` varchar(24) NOT NULL DEFAULT '',
  `apartment_email` varchar(128) NOT NULL DEFAULT '',
  `max_occupants` int(8) NOT NULL DEFAULT '0',
  `apartment_longitude` varchar(24) NOT NULL DEFAULT '',
  `apartment_latitude` varchar(24) NOT NULL DEFAULT '',
  `available_start` bigint(64) NOT NULL DEFAULT '0',
  `available_finish` bigint(64) NOT NULL DEFAULT '0',
  `updated_last` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  `apartment_status` varchar(12) NOT NULL DEFAULT '',
  PRIMARY KEY (`apartment_id`),
  KEY `region_id` (`region_id`),
  KEY `apartment_title` (`apartment_title`),
  KEY `apartment_address` (`apartment_address`),
  KEY `apartment_city` (`apartment_city`),
  KEY `apartment_province` (`apartment_province`),
  KEY `apartment_country` (`apartment_country`),
  KEY `max_occupants` (`max_occupants`),
  KEY `apartment_longitude` (`apartment_longitude`),
  KEY `apartment_latitude` (`apartment_latitude`),
  KEY `available_start` (`available_start`),
  KEY `available_finish` (`available_finish`),
  KEY `apartment_status` (`apartment_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `categories` (
  `category_id` int(12) NOT NULL AUTO_INCREMENT,
  `category_parent` int(12) NOT NULL DEFAULT '0',
  `category_code` varchar(12) DEFAULT NULL,
  `category_type` int(12) NOT NULL DEFAULT '0',
  `category_name` varchar(128) NOT NULL DEFAULT '',
  `category_desc` text,
  `category_min` int(12) DEFAULT NULL,
  `category_max` int(12) DEFAULT NULL,
  `category_buffer` int(12) DEFAULT NULL,
  `category_start` bigint(64) NOT NULL DEFAULT '0',
  `category_finish` bigint(64) NOT NULL DEFAULT '0',
  `subcategory_strict` int(1) NOT NULL DEFAULT '0',
  `category_expiry` bigint(64) NOT NULL DEFAULT '0',
  `category_status` varchar(12) NOT NULL DEFAULT 'published',
  `category_order` int(3) NOT NULL DEFAULT '0',
  `rotation_id` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`),
  KEY `category_parent` (`category_parent`),
  KEY `category_code` (`category_code`),
  KEY `category_type` (`category_type`),
  KEY `category_name` (`category_name`),
  KEY `category_min` (`category_min`),
  KEY `category_max` (`category_max`),
  KEY `category_start` (`category_start`),
  KEY `category_finish` (`category_finish`),
  KEY `subcategory_strict` (`subcategory_strict`),
  KEY `category_expiry` (`category_expiry`),
  KEY `category_status` (`category_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `category_departments` (
  `cdep_id` int(12) NOT NULL AUTO_INCREMENT,
  `category_id` int(12) NOT NULL DEFAULT '0',
  `department_id` int(12) NOT NULL DEFAULT '0',
  `contact_id` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cdep_id`),
  KEY `category_id` (`category_id`),
  KEY `department_id` (`department_id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `category_type` (
  `ctype_id` int(12) NOT NULL AUTO_INCREMENT,
  `ctype_parent` int(12) NOT NULL DEFAULT '0',
  `ctype_name` varchar(128) NOT NULL DEFAULT '',
  `ctype_desc` text NOT NULL,
  `require_min` int(11) NOT NULL DEFAULT '0',
  `require_max` int(11) NOT NULL DEFAULT '0',
  `require_buffer` int(11) NOT NULL DEFAULT '0',
  `require_start` int(11) NOT NULL DEFAULT '0',
  `require_finish` int(11) NOT NULL DEFAULT '0',
  `require_expiry` int(11) NOT NULL DEFAULT '0',
  `ctype_filterable` int(11) NOT NULL DEFAULT '0',
  `ctype_order` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ctype_id`),
  KEY `ctype_parent` (`ctype_parent`),
  KEY `require_start` (`require_start`),
  KEY `require_finish` (`require_finish`),
  KEY `require_expiry` (`require_expiry`),
  KEY `ctype_filterable` (`ctype_filterable`),
  KEY `ctype_order` (`ctype_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=33 ;

INSERT INTO `category_type` VALUES(1, 30, 'Institution', '', 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `category_type` VALUES(2, 30, 'Faculty', '', 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `category_type` VALUES(12, 30, 'School', '', 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `category_type` VALUES(13, 30, 'Graduating Year', '', 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `category_type` VALUES(14, 30, 'Phase', '', 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `category_type` VALUES(15, 30, 'Unit', '', 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `category_type` VALUES(16, 30, 'Block', '', 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `category_type` VALUES(17, 30, 'Stream', '', 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `category_type` VALUES(19, 30, 'Selective', '', 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `category_type` VALUES(20, 30, 'Course Grouping', '', 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `category_type` VALUES(21, 30, 'Course', '', 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `category_type` VALUES(22, 30, 'Date Period', '', 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `category_type` VALUES(23, 0, 'Downtime', '', 0, 0, 0, 0, 0, 0, 0, 1);
INSERT INTO `category_type` VALUES(24, 23, 'Holiday Period', '', 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `category_type` VALUES(25, 23, 'Vacation Period', '', 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `category_type` VALUES(26, 23, 'Sick Leave', '', 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `category_type` VALUES(27, 23, 'Maternity Leave', '', 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `category_type` VALUES(28, 23, 'Personal Leave', '', 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `category_type` VALUES(29, 23, 'Leave Of Absense', '', 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `category_type` VALUES(30, 0, 'Default Types', '', 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `category_type` VALUES(31, 30, 'Elective', '', 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `category_type` VALUES(32, 30, 'Rotation', '', 0, 0, 0, 0, 0, 0, 0, 0);

CREATE TABLE `electives` (
  `electives_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL,
  `geo_location` varchar(15) NOT NULL DEFAULT 'National',
  `department_id` int(12) NOT NULL,
  `discipline_id` int(11) NOT NULL,
  `sub_discipline` varchar(100) DEFAULT NULL,
  `schools_id` int(11) NOT NULL,
  `other_medical_school` varchar(150) DEFAULT NULL,
  `objective` text NOT NULL,
  `preceptor_first_name` varchar(50) DEFAULT NULL,
  `preceptor_last_name` varchar(50) NOT NULL,
  `address` varchar(250) NOT NULL,
  `countries_id` int(12) NOT NULL,
  `city` varchar(100) NOT NULL,
  `prov_state` varchar(200) NOT NULL,
  `region_id` int(12) NOT NULL DEFAULT '0',
  `postal_zip_code` varchar(20) DEFAULT NULL,
  `fax` varchar(25) DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`electives_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `eval_answers` (
  `answer_id` int(12) NOT NULL AUTO_INCREMENT,
  `question_id` int(12) NOT NULL DEFAULT '0',
  `answer_type` varchar(50) NOT NULL DEFAULT '',
  `answer_label` varchar(50) NOT NULL DEFAULT '',
  `answer_value` varchar(50) NOT NULL DEFAULT '',
  `answer_lastmod` bigint(64) DEFAULT NULL,
  PRIMARY KEY (`answer_id`),
  KEY `question_id` (`question_id`),
  KEY `answer_value` (`answer_value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `eval_approved` (
  `approved_id` int(12) NOT NULL AUTO_INCREMENT,
  `notification_id` int(12) NOT NULL DEFAULT '0',
  `completed_id` int(12) NOT NULL DEFAULT '0',
  `modified_last` bigint(64) NOT NULL DEFAULT '0',
  `modified_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`approved_id`),
  KEY `notification_id` (`notification_id`),
  KEY `completed_id` (`completed_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `eval_completed` (
  `completed_id` int(12) NOT NULL AUTO_INCREMENT,
  `notification_id` int(12) NOT NULL DEFAULT '0',
  `instructor_id` varchar(24) NOT NULL DEFAULT '0',
  `completed_status` varchar(12) NOT NULL DEFAULT 'pending',
  `completed_lastmod` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`completed_id`),
  KEY `notification_id` (`notification_id`),
  KEY `instructor_id` (`instructor_id`),
  KEY `completed_status` (`completed_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `eval_forms` (
  `form_id` int(12) NOT NULL AUTO_INCREMENT,
  `form_type` varchar(12) NOT NULL DEFAULT '',
  `nmessage_id` int(12) NOT NULL DEFAULT '0',
  `form_title` varchar(128) NOT NULL DEFAULT '',
  `form_author` int(12) NOT NULL DEFAULT '0',
  `form_desc` text NOT NULL,
  `form_status` varchar(12) NOT NULL DEFAULT 'published',
  `form_lastmod` bigint(64) DEFAULT NULL,
  PRIMARY KEY (`form_id`),
  KEY `form_type` (`form_type`),
  KEY `nmessage_id` (`nmessage_id`),
  KEY `form_status` (`form_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `eval_questions` (
  `question_id` int(12) NOT NULL AUTO_INCREMENT,
  `form_id` int(12) NOT NULL DEFAULT '0',
  `question_text` text NOT NULL,
  `question_style` varchar(50) NOT NULL DEFAULT '',
  `question_required` varchar(50) NOT NULL DEFAULT '',
  `question_lastmod` bigint(64) DEFAULT NULL,
  PRIMARY KEY (`question_id`),
  KEY `form_id` (`form_id`),
  KEY `question_required` (`question_required`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `eval_results` (
  `result_id` int(12) NOT NULL AUTO_INCREMENT,
  `completed_id` int(12) NOT NULL DEFAULT '0',
  `answer_id` int(12) NOT NULL DEFAULT '0',
  `result_value` text NOT NULL,
  `result_lastmod` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`result_id`),
  KEY `completed_id` (`completed_id`),
  KEY `answer_id` (`answer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `evaluations` (
  `item_id` int(12) NOT NULL AUTO_INCREMENT,
  `form_id` int(12) NOT NULL DEFAULT '0',
  `category_id` int(12) NOT NULL DEFAULT '0',
  `category_recurse` int(2) NOT NULL DEFAULT '1',
  `item_title` varchar(128) NOT NULL DEFAULT '',
  `item_maxinstances` int(4) NOT NULL DEFAULT '1',
  `item_start` int(12) NOT NULL DEFAULT '1',
  `item_end` int(12) NOT NULL DEFAULT '30',
  `item_status` varchar(12) NOT NULL DEFAULT 'published',
  `modified_last` bigint(64) NOT NULL DEFAULT '0',
  `modified_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`item_id`),
  KEY `form_id` (`form_id`),
  KEY `category_id` (`category_id`),
  KEY `item_status` (`item_status`),
  KEY `item_end` (`item_end`),
  KEY `item_start` (`item_start`),
  KEY `item_maxinstances` (`item_maxinstances`),
  KEY `category_recurse` (`category_recurse`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `event_contacts` (
  `econtact_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `econtact_type` varchar(12) NOT NULL DEFAULT 'student',
  `etype_id` int(12) NOT NULL DEFAULT '0',
  `econtact_parent` int(12) NOT NULL DEFAULT '0',
  `econtact_desc` text,
  `econtact_start` bigint(64) NOT NULL DEFAULT '0',
  `econtact_finish` bigint(64) NOT NULL DEFAULT '0',
  `econtact_status` varchar(12) NOT NULL DEFAULT 'published',
  `econtact_order` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`econtact_id`),
  KEY `event_id` (`event_id`),
  KEY `econtact_type` (`econtact_type`),
  KEY `etype_id` (`etype_id`),
  KEY `econtact_parent` (`econtact_parent`),
  KEY `econtact_order` (`econtact_order`),
  KEY `econtact_status` (`econtact_status`),
  KEY `econtact_finish` (`econtact_finish`),
  KEY `econtact_start` (`econtact_start`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `event_locations` (
  `elocation_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `location_id` int(12) NOT NULL DEFAULT '0',
  `elocation_start` bigint(64) NOT NULL DEFAULT '0',
  `elocation_finish` bigint(64) NOT NULL DEFAULT '0',
  `elocation_status` varchar(12) NOT NULL DEFAULT 'published',
  `elocation_order` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`elocation_id`),
  KEY `event_id` (`event_id`),
  KEY `location_id` (`location_id`),
  KEY `elocation_start` (`elocation_start`),
  KEY `elocation_finish` (`elocation_finish`),
  KEY `elocation_status` (`elocation_status`),
  KEY `elocation_order` (`elocation_order`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE `events` (
  `event_id` int(12) NOT NULL AUTO_INCREMENT,
  `category_id` int(12) NOT NULL DEFAULT '0',
  `rotation_id` int(12) NOT NULL DEFAULT '0',
  `region_id` int(12) NOT NULL DEFAULT '0',
  `event_title` varchar(255) NOT NULL DEFAULT '',
  `event_desc` text,
  `event_start` bigint(64) NOT NULL DEFAULT '0',
  `event_finish` bigint(64) NOT NULL DEFAULT '0',
  `event_expiry` bigint(64) NOT NULL DEFAULT '0',
  `accessible_start` bigint(64) NOT NULL DEFAULT '0',
  `accessible_finish` bigint(64) NOT NULL DEFAULT '0',
  `modified_last` bigint(64) NOT NULL DEFAULT '0',
  `modified_by` int(12) NOT NULL DEFAULT '0',
  `event_type` varchar(12) NOT NULL DEFAULT 'academic',
  `event_access` varchar(12) NOT NULL DEFAULT 'public',
  `event_status` varchar(12) NOT NULL DEFAULT 'published',
  PRIMARY KEY (`event_id`),
  KEY `category_id` (`category_id`),
  KEY `region_id` (`region_id`),
  KEY `event_type` (`event_type`),
  KEY `event_access` (`event_access`),
  KEY `event_status` (`event_status`),
  KEY `accessible_finish` (`accessible_finish`),
  KEY `accessible_start` (`accessible_start`),
  KEY `event_expiry` (`event_expiry`),
  KEY `event_finish` (`event_finish`),
  KEY `event_start` (`event_start`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `global_lu_rotations` (
  `rotation_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `rotation_title` varchar(24) DEFAULT NULL,
  `percent_required` int(3) NOT NULL,
  `percent_period_complete` int(3) NOT NULL,
  `course_id` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rotation_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

INSERT INTO `global_lu_rotations` VALUES(1, 'Family Medicine', 50, 50, 0);
INSERT INTO `global_lu_rotations` VALUES(2, 'Medicine', 50, 50, 0);
INSERT INTO `global_lu_rotations` VALUES(3, 'Obstetrics & Gynecology', 50, 50, 0);
INSERT INTO `global_lu_rotations` VALUES(4, 'Pediatrics', 50, 50, 0);
INSERT INTO `global_lu_rotations` VALUES(5, 'Perioperative', 50, 50, 0);
INSERT INTO `global_lu_rotations` VALUES(6, 'Psychiatry', 50, 50, 0);
INSERT INTO `global_lu_rotations` VALUES(7, 'Surgery-Urology', 50, 50, 0);
INSERT INTO `global_lu_rotations` VALUES(8, 'Surgery-Orthopedic', 50, 50, 0);
INSERT INTO `global_lu_rotations` VALUES(9, 'Integrated', 50, 50, 0);

CREATE TABLE `logbook_entries` (
  `lentry_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) unsigned NOT NULL,
  `encounter_date` int(12) NOT NULL,
  `updated_date` bigint(64) unsigned NOT NULL DEFAULT '0',
  `patient_info` varchar(30) NOT NULL,
  `agerange_id` int(12) unsigned NOT NULL DEFAULT '0',
  `gender` varchar(1) NOT NULL DEFAULT '0',
  `rotation_id` int(12) unsigned NOT NULL DEFAULT '0',
  `llocation_id` int(12) unsigned NOT NULL DEFAULT '0',
  `lsite_id` int(11) NOT NULL,
  `comments` text,
  `reflection` text NOT NULL,
  `participation_level` int(2) NOT NULL DEFAULT '2',
  `entry_active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`lentry_id`),
  KEY `proxy_id` (`proxy_id`,`updated_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `logbook_entry_checklist` (
  `lechecklist_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) unsigned NOT NULL,
  `rotation_id` int(12) unsigned NOT NULL,
  `checklist` bigint(64) unsigned NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  PRIMARY KEY (`lechecklist_id`),
  UNIQUE KEY `proxy_id` (`proxy_id`,`rotation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `logbook_entry_evaluations` (
  `leevaluation_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `levaluation_id` int(12) unsigned NOT NULL,
  `item_status` int(1) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL,
  PRIMARY KEY (`leevaluation_id`),
  UNIQUE KEY `levaluation_id` (`levaluation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE `logbook_entry_objectives` (
  `leobjective_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `lentry_id` int(12) unsigned NOT NULL DEFAULT '0',
  `objective_id` int(12) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`leobjective_id`,`lentry_id`,`objective_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `logbook_entry_procedures` (
  `leprocedure_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `lentry_id` int(12) unsigned NOT NULL DEFAULT '0',
  `lprocedure_id` int(12) unsigned NOT NULL DEFAULT '0',
  `level` smallint(6) NOT NULL COMMENT 'Level of involvement',
  PRIMARY KEY (`leprocedure_id`,`lentry_id`,`lprocedure_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `logbook_lu_agerange` (
  `agerange_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `rotation_id` int(12) unsigned NOT NULL DEFAULT '0',
  `age` varchar(8) DEFAULT NULL,
  PRIMARY KEY (`agerange_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;

INSERT INTO `logbook_lu_agerange` VALUES(1, 0, '  < 1');
INSERT INTO `logbook_lu_agerange` VALUES(2, 0, ' 1 - 4');
INSERT INTO `logbook_lu_agerange` VALUES(3, 0, ' 5 - 14');
INSERT INTO `logbook_lu_agerange` VALUES(4, 0, '15 - 24');
INSERT INTO `logbook_lu_agerange` VALUES(5, 0, '25 - 34');
INSERT INTO `logbook_lu_agerange` VALUES(6, 0, '35 - 44');
INSERT INTO `logbook_lu_agerange` VALUES(7, 0, '45 - 54');
INSERT INTO `logbook_lu_agerange` VALUES(8, 0, '55 - 64');
INSERT INTO `logbook_lu_agerange` VALUES(9, 0, '65 - 74');
INSERT INTO `logbook_lu_agerange` VALUES(10, 0, '  75+');
INSERT INTO `logbook_lu_agerange` VALUES(11, 5, '  < 1m');
INSERT INTO `logbook_lu_agerange` VALUES(12, 5, '  < 1w');
INSERT INTO `logbook_lu_agerange` VALUES(13, 5, '  < 6m');
INSERT INTO `logbook_lu_agerange` VALUES(14, 5, '  < 12m');
INSERT INTO `logbook_lu_agerange` VALUES(15, 5, '  < 60m');
INSERT INTO `logbook_lu_agerange` VALUES(16, 5, '  5-12');
INSERT INTO `logbook_lu_agerange` VALUES(17, 5, '13 - 19');
INSERT INTO `logbook_lu_agerange` VALUES(18, 5, '20 - 64');
INSERT INTO `logbook_lu_agerange` VALUES(19, 6, ' 5 - 11');
INSERT INTO `logbook_lu_agerange` VALUES(20, 6, '12 - 17');
INSERT INTO `logbook_lu_agerange` VALUES(21, 6, '18 - 34');

CREATE TABLE `logbook_lu_checklist` (
  `lchecklist_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `rotation_id` int(12) unsigned NOT NULL,
  `line` int(11) DEFAULT NULL,
  `type` int(11) NOT NULL,
  `indent` int(11) DEFAULT NULL,
  `item` varchar(255) NOT NULL,
  PRIMARY KEY (`lchecklist_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=64 ;

INSERT INTO `logbook_lu_checklist` VALUES(1, 2, 1, 1, 0, 'Checklist for Family Medicine:');
INSERT INTO `logbook_lu_checklist` VALUES(2, 2, 2, 2, 2, 'Learning Plan (see core doc, p. 26)');
INSERT INTO `logbook_lu_checklist` VALUES(3, 2, 3, 1, 1, 'Midpoint Review (to be completed by 3rd Friday of rotation)');
INSERT INTO `logbook_lu_checklist` VALUES(4, 2, 5, 2, 2, 'Review Logbook with Preceptor');
INSERT INTO `logbook_lu_checklist` VALUES(5, 2, 6, 2, 2, 'Review Learning Plan');
INSERT INTO `logbook_lu_checklist` VALUES(6, 2, 7, 2, 2, 'Formative MCQ Exam (on 3rd Friday of rotation)');
INSERT INTO `logbook_lu_checklist` VALUES(7, 2, 8, 1, 1, 'In the final (6th) week:');
INSERT INTO `logbook_lu_checklist` VALUES(8, 2, 9, 2, 2, 'Show completed logbook');
INSERT INTO `logbook_lu_checklist` VALUES(9, 2, 10, 2, 2, 'Present Project  to clinic (by 6th Friday; see core doc, p. 28)');
INSERT INTO `logbook_lu_checklist` VALUES(10, 2, 11, 2, 2, 'Present Project to peers/examiners (by 6th Fri; core doc, p.28)');
INSERT INTO `logbook_lu_checklist` VALUES(11, 2, 12, 2, 2, 'Have completed 4 mini-CEX (see core doc, p. 28)');
INSERT INTO `logbook_lu_checklist` VALUES(12, 2, 13, 2, 2, 'Final ITER (due on 6th Friday of rotation)');
INSERT INTO `logbook_lu_checklist` VALUES(13, 2, 14, 2, 2, 'Summative MCQ Exam (on 6th Friday of rotation)');
INSERT INTO `logbook_lu_checklist` VALUES(14, 1, 1, 1, 0, 'Checklist for Emergency Medicine:');
INSERT INTO `logbook_lu_checklist` VALUES(15, 1, 2, 2, 2, 'Daily Shift Reports');
INSERT INTO `logbook_lu_checklist` VALUES(16, 1, 3, 1, 1, 'Midpoint Review ');
INSERT INTO `logbook_lu_checklist` VALUES(17, 1, 4, 2, 2, 'Review Logbook with Preceptor');
INSERT INTO `logbook_lu_checklist` VALUES(18, 1, 5, 1, 1, 'In the final week:');
INSERT INTO `logbook_lu_checklist` VALUES(19, 1, 6, 2, 2, 'Show completed logbook');
INSERT INTO `logbook_lu_checklist` VALUES(20, 1, 7, 2, 2, 'Final ITER ');
INSERT INTO `logbook_lu_checklist` VALUES(21, 1, 8, 2, 2, 'Summative MCQ Exam ');
INSERT INTO `logbook_lu_checklist` VALUES(22, 3, 1, 1, 0, 'Checklist for Internal Medicine:');
INSERT INTO `logbook_lu_checklist` VALUES(23, 3, 2, 1, 1, 'Midpoint Review (to be completed by 6th Friday of rotation)');
INSERT INTO `logbook_lu_checklist` VALUES(24, 3, 3, 2, 2, 'Review Logbook with Preceptor');
INSERT INTO `logbook_lu_checklist` VALUES(25, 3, 4, 2, 2, 'Formative MCQ Exam (on 6th Friday of rotation)');
INSERT INTO `logbook_lu_checklist` VALUES(26, 3, 5, 2, 2, 'Formative Mid-term OSCE');
INSERT INTO `logbook_lu_checklist` VALUES(27, 3, 6, 1, 1, 'In the final (12th) week:');
INSERT INTO `logbook_lu_checklist` VALUES(28, 3, 7, 2, 2, 'Show completed logbook');
INSERT INTO `logbook_lu_checklist` VALUES(29, 3, 8, 2, 2, 'Final ITER (due on 12th Friday of rotation)');
INSERT INTO `logbook_lu_checklist` VALUES(30, 3, 9, 2, 2, 'Summative MCQ Exam');
INSERT INTO `logbook_lu_checklist` VALUES(31, 4, 1, 1, 0, 'Checklist for Obstetrics & Gynecology:');
INSERT INTO `logbook_lu_checklist` VALUES(32, 4, 2, 1, 1, 'Midpoint Review (to be completed by 2nd week of rotation)');
INSERT INTO `logbook_lu_checklist` VALUES(33, 4, 3, 2, 2, 'Review Logbook with Preceptor');
INSERT INTO `logbook_lu_checklist` VALUES(34, 4, 4, 2, 2, 'Formative MCQ Exam (on 2nd week of rotation)');
INSERT INTO `logbook_lu_checklist` VALUES(35, 4, 5, 1, 1, 'In the final (4th) week:');
INSERT INTO `logbook_lu_checklist` VALUES(36, 4, 6, 2, 2, 'Show completed logbook');
INSERT INTO `logbook_lu_checklist` VALUES(37, 4, 7, 2, 2, 'Final ITER (due on 4th Friday of rotation)');
INSERT INTO `logbook_lu_checklist` VALUES(38, 4, 8, 2, 2, 'Summative MCQ Exam');
INSERT INTO `logbook_lu_checklist` VALUES(39, 5, 1, 1, 0, 'Checklist for Pediatrics:');
INSERT INTO `logbook_lu_checklist` VALUES(40, 5, 2, 1, 1, 'Midpoint Review (to be completed by mid rotation)');
INSERT INTO `logbook_lu_checklist` VALUES(41, 5, 3, 2, 2, 'Review Logbook with Preceptor');
INSERT INTO `logbook_lu_checklist` VALUES(42, 5, 4, 2, 2, 'Formative OSCE (mid-point of rotation)');
INSERT INTO `logbook_lu_checklist` VALUES(43, 5, 5, 1, 1, 'In the final week:');
INSERT INTO `logbook_lu_checklist` VALUES(44, 5, 6, 2, 2, 'Show completed logbook');
INSERT INTO `logbook_lu_checklist` VALUES(45, 5, 7, 2, 2, 'Final ITER');
INSERT INTO `logbook_lu_checklist` VALUES(46, 5, 8, 2, 2, 'Summative MCQ Exam');
INSERT INTO `logbook_lu_checklist` VALUES(47, 6, 1, 1, 0, 'Checklist for Psychiatry:');
INSERT INTO `logbook_lu_checklist` VALUES(48, 6, 2, 1, 1, 'Midpoint Review (to be completed by mid rotation)');
INSERT INTO `logbook_lu_checklist` VALUES(49, 6, 3, 2, 2, 'Review Logbook with Preceptor');
INSERT INTO `logbook_lu_checklist` VALUES(50, 6, 4, 2, 2, 'Formative VOSCE (mid-point of rotation)');
INSERT INTO `logbook_lu_checklist` VALUES(51, 6, 5, 1, 1, 'In the final week:');
INSERT INTO `logbook_lu_checklist` VALUES(52, 6, 6, 2, 2, 'Show completed logbook');
INSERT INTO `logbook_lu_checklist` VALUES(53, 6, 7, 2, 2, 'Final ITER');
INSERT INTO `logbook_lu_checklist` VALUES(54, 6, 8, 2, 2, 'Summative MCQ Exam');
INSERT INTO `logbook_lu_checklist` VALUES(55, 6, 9, 2, 2, 'Evaluation of Psychiatric Interviewing Skills');
INSERT INTO `logbook_lu_checklist` VALUES(56, 7, 1, 1, 0, 'Checklist for Surgery / Anesthesia:');
INSERT INTO `logbook_lu_checklist` VALUES(57, 7, 2, 1, 1, 'Midpoint Review (to be completed by mid rotation)');
INSERT INTO `logbook_lu_checklist` VALUES(58, 7, 3, 2, 2, 'Review Logbook with Preceptor');
INSERT INTO `logbook_lu_checklist` VALUES(59, 7, 4, 2, 2, 'Formative MCQ (mid-point of rotation)');
INSERT INTO `logbook_lu_checklist` VALUES(60, 7, 5, 1, 1, 'In the final week:');
INSERT INTO `logbook_lu_checklist` VALUES(61, 7, 6, 2, 2, 'Show completed logbook');
INSERT INTO `logbook_lu_checklist` VALUES(62, 7, 7, 2, 2, 'Final ITER');
INSERT INTO `logbook_lu_checklist` VALUES(63, 7, 8, 2, 2, 'Summative MCQ Exam');

CREATE TABLE `logbook_lu_evaluations` (
  `levaluation_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `rotation_id` int(12) unsigned NOT NULL,
  `line` int(11) DEFAULT NULL,
  `type` int(11) NOT NULL,
  `indent` int(11) DEFAULT NULL,
  `item` varchar(255) NOT NULL,
  PRIMARY KEY (`levaluation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `logbook_lu_locations` (
  `llocation_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `location` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`llocation_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

INSERT INTO `logbook_lu_locations` VALUES(1, 'Office / Clinic');
INSERT INTO `logbook_lu_locations` VALUES(2, 'Hospital Ward');
INSERT INTO `logbook_lu_locations` VALUES(3, 'Emergency');
INSERT INTO `logbook_lu_locations` VALUES(4, 'OR');
INSERT INTO `logbook_lu_locations` VALUES(5, 'OSCE');
INSERT INTO `logbook_lu_locations` VALUES(6, 'Bedside Teaching Rounds');
INSERT INTO `logbook_lu_locations` VALUES(7, 'Case Base Teaching Rounds');
INSERT INTO `logbook_lu_locations` VALUES(8, 'Patients Home');
INSERT INTO `logbook_lu_locations` VALUES(9, 'Nursing Home');
INSERT INTO `logbook_lu_locations` VALUES(10, 'Community Site');
INSERT INTO `logbook_lu_locations` VALUES(11, 'Computer Interactive Case');
INSERT INTO `logbook_lu_locations` VALUES(12, 'Day Surgery');
INSERT INTO `logbook_lu_locations` VALUES(13, 'Mega code');
INSERT INTO `logbook_lu_locations` VALUES(14, 'Seminar Blocks');
INSERT INTO `logbook_lu_locations` VALUES(15, 'HPS');
INSERT INTO `logbook_lu_locations` VALUES(16, 'Nursery');

CREATE TABLE `logbook_lu_procedures` (
  `lprocedure_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `procedure` varchar(60) NOT NULL,
  PRIMARY KEY (`lprocedure_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=26 ;

INSERT INTO `logbook_lu_procedures` VALUES(1, 'ABG');
INSERT INTO `logbook_lu_procedures` VALUES(2, 'Dictation-discharge');
INSERT INTO `logbook_lu_procedures` VALUES(3, 'Dictation-letter');
INSERT INTO `logbook_lu_procedures` VALUES(4, 'Cervical exam/labour');
INSERT INTO `logbook_lu_procedures` VALUES(5, 'Delivery, norm vaginal');
INSERT INTO `logbook_lu_procedures` VALUES(6, 'Delivery, placenta');
INSERT INTO `logbook_lu_procedures` VALUES(7, 'PAP smear');
INSERT INTO `logbook_lu_procedures` VALUES(8, 'Pelvic exam');
INSERT INTO `logbook_lu_procedures` VALUES(9, 'Perineal repair');
INSERT INTO `logbook_lu_procedures` VALUES(10, 'Pessary insert/remove');
INSERT INTO `logbook_lu_procedures` VALUES(11, 'Growth curve');
INSERT INTO `logbook_lu_procedures` VALUES(12, 'Infant/child immun');
INSERT INTO `logbook_lu_procedures` VALUES(13, 'Otoscopy, child');
INSERT INTO `logbook_lu_procedures` VALUES(14, 'Cast/splint');
INSERT INTO `logbook_lu_procedures` VALUES(15, 'ETT intubation');
INSERT INTO `logbook_lu_procedures` VALUES(16, 'Facemask ventilation');
INSERT INTO `logbook_lu_procedures` VALUES(17, 'IV catheter');
INSERT INTO `logbook_lu_procedures` VALUES(18, 'IV setup');
INSERT INTO `logbook_lu_procedures` VALUES(19, 'OR monitors');
INSERT INTO `logbook_lu_procedures` VALUES(20, 'PCA setup');
INSERT INTO `logbook_lu_procedures` VALUES(21, 'Slit lamp exam');
INSERT INTO `logbook_lu_procedures` VALUES(22, 'Suturing');
INSERT INTO `logbook_lu_procedures` VALUES(23, 'Venipuncture');
INSERT INTO `logbook_lu_procedures` VALUES(24, 'NG tube');
INSERT INTO `logbook_lu_procedures` VALUES(25, 'Surgical technique/OR assist');

CREATE TABLE `logbook_lu_sites` (
  `lsite_id` int(11) NOT NULL AUTO_INCREMENT,
  `site_type` int(11) DEFAULT NULL,
  `site_name` varchar(64) NOT NULL,
  PRIMARY KEY (`lsite_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

INSERT INTO `logbook_lu_sites` VALUES(1, 1, 'Brockville General Hospital');
INSERT INTO `logbook_lu_sites` VALUES(2, 1, 'Brockville Pyschiatric Hospital');
INSERT INTO `logbook_lu_sites` VALUES(3, 1, 'CHEO');
INSERT INTO `logbook_lu_sites` VALUES(4, 1, 'Hotel Dieu Hospital');
INSERT INTO `logbook_lu_sites` VALUES(5, 1, 'Kingston General Hospital');
INSERT INTO `logbook_lu_sites` VALUES(6, 1, 'Lakeridge Health');
INSERT INTO `logbook_lu_sites` VALUES(7, 1, 'Markam Stouffville Hospital');
INSERT INTO `logbook_lu_sites` VALUES(8, 1, 'Ongwanada');
INSERT INTO `logbook_lu_sites` VALUES(9, 1, 'Peterborough Regional Health Centre');
INSERT INTO `logbook_lu_sites` VALUES(10, 1, 'Providence Continuing Care Centre');
INSERT INTO `logbook_lu_sites` VALUES(11, 1, 'Queensway Carleton Hospital');
INSERT INTO `logbook_lu_sites` VALUES(12, 1, 'Quinte Health Care');
INSERT INTO `logbook_lu_sites` VALUES(13, 1, 'Weenebayko General Hospital');
INSERT INTO `logbook_lu_sites` VALUES(14, 1, 'Queen''s University');

CREATE TABLE `logbook_mandatory_objectives` (
  `lmobjective_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `rotation_id` int(12) unsigned DEFAULT NULL,
  `objective_id` int(12) unsigned DEFAULT NULL,
  `number_required` int(2) NOT NULL DEFAULT '1',
  PRIMARY KEY (`lmobjective_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=114 ;

INSERT INTO `logbook_mandatory_objectives` VALUES(1, 8, 201, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(2, 8, 202, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(3, 8, 203, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(4, 8, 207, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(5, 8, 208, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(6, 8, 209, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(7, 8, 210, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(8, 8, 211, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(9, 8, 212, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(10, 8, 213, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(11, 8, 214, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(12, 6, 215, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(13, 8, 216, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(14, 7, 204, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(15, 7, 205, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(16, 7, 206, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(17, 7, 207, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(18, 7, 208, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(19, 7, 209, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(20, 7, 210, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(21, 7, 211, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(22, 7, 212, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(23, 7, 213, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(24, 7, 214, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(25, 7, 216, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(26, 6, 217, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(27, 6, 218, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(28, 6, 219, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(29, 5, 220, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(30, 6, 221, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(31, 5, 222, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(32, 5, 223, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(33, 5, 224, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(34, 5, 225, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(35, 5, 226, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(36, 5, 227, 2);
INSERT INTO `logbook_mandatory_objectives` VALUES(37, 5, 228, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(38, 5, 229, 2);
INSERT INTO `logbook_mandatory_objectives` VALUES(39, 5, 230, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(40, 5, 201, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(41, 5, 202, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(42, 5, 233, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(43, 4, 234, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(44, 4, 235, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(45, 4, 236, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(46, 4, 237, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(47, 4, 238, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(48, 4, 239, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(49, 4, 240, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(50, 4, 241, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(51, 4, 242, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(52, 4, 243, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(53, 4, 244, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(54, 4, 245, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(55, 4, 246, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(56, 4, 247, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(57, 4, 248, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(58, 4, 249, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(59, 4, 250, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(60, 4, 251, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(61, 4, 252, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(62, 4, 253, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(63, 4, 254, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(64, 4, 255, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(65, 4, 256, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(66, 3, 257, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(67, 3, 258, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(68, 3, 259, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(69, 3, 260, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(70, 3, 261, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(71, 3, 262, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(72, 3, 263, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(73, 3, 264, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(74, 3, 265, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(75, 3, 266, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(76, 3, 267, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(77, 2, 268, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(78, 2, 269, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(79, 2, 270, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(80, 2, 271, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(81, 2, 272, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(82, 2, 273, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(83, 2, 274, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(84, 2, 275, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(85, 2, 276, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(86, 2, 277, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(87, 2, 278, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(88, 2, 279, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(89, 2, 280, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(90, 2, 281, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(91, 2, 282, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(92, 2, 283, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(93, 2, 284, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(94, 2, 285, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(95, 2, 286, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(96, 2, 287, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(97, 2, 288, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(98, 1, 289, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(99, 1, 290, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(100, 1, 291, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(101, 1, 292, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(102, 1, 293, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(103, 1, 294, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(104, 1, 295, 3);
INSERT INTO `logbook_mandatory_objectives` VALUES(105, 1, 296, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(106, 1, 221, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(107, 1, 276, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(108, 1, 299, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(109, 1, 300, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(110, 1, 242, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(111, 1, 281, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(112, 1, 303, 1);
INSERT INTO `logbook_mandatory_objectives` VALUES(113, 1, 284, 1);

CREATE TABLE `logbook_notification_history` (
  `lnhistory_id` int(12) NOT NULL AUTO_INCREMENT,
  `clerk_id` int(12) NOT NULL,
  `proxy_id` int(12) NOT NULL,
  `rotation_id` int(12) NOT NULL,
  `notified_date` int(12) NOT NULL,
  PRIMARY KEY (`lnhistory_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `logbook_overdue` (
  `lologging_id` int(12) NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) NOT NULL,
  `rotation_id` int(12) NOT NULL,
  `event_id` int(12) NOT NULL,
  `logged_required` int(12) NOT NULL,
  `logged_completed` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`lologging_id`),
  UNIQUE KEY `lologging_id` (`lologging_id`,`proxy_id`,`event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `logbook_preferred_procedures` (
  `lpprocedure_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `rotation_id` int(12) unsigned NOT NULL,
  `order` smallint(6) NOT NULL,
  `lprocedure_id` int(12) unsigned NOT NULL,
  `number_required` int(2) NOT NULL DEFAULT '1',
  PRIMARY KEY (`lpprocedure_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=30 ;

INSERT INTO `logbook_preferred_procedures` VALUES(1, 2, 0, 1, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(2, 2, 0, 2, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(3, 2, 0, 3, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(4, 3, 0, 4, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(5, 3, 0, 5, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(6, 3, 0, 6, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(7, 3, 0, 7, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(8, 3, 0, 8, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(9, 3, 0, 9, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(10, 3, 0, 10, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(11, 4, 0, 11, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(12, 4, 0, 12, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(13, 4, 0, 13, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(14, 5, 0, 14, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(15, 5, 0, 15, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(16, 5, 0, 16, 3);
INSERT INTO `logbook_preferred_procedures` VALUES(17, 5, 0, 17, 3);
INSERT INTO `logbook_preferred_procedures` VALUES(18, 5, 0, 18, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(19, 5, 0, 19, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(20, 5, 0, 20, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(21, 5, 0, 21, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(22, 5, 0, 22, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(23, 5, 0, 23, 2);
INSERT INTO `logbook_preferred_procedures` VALUES(24, 7, 0, 24, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(25, 7, 0, 25, 4);
INSERT INTO `logbook_preferred_procedures` VALUES(26, 7, 0, 22, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(27, 8, 0, 24, 1);
INSERT INTO `logbook_preferred_procedures` VALUES(28, 8, 0, 25, 4);
INSERT INTO `logbook_preferred_procedures` VALUES(29, 8, 0, 22, 1);

CREATE TABLE `logbook_rotation_comments` (
  `lrcomment_id` int(12) NOT NULL AUTO_INCREMENT,
  `clerk_id` int(12) NOT NULL,
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `rotation_id` int(12) NOT NULL DEFAULT '0',
  `comments` text NOT NULL,
  `updated_date` int(12) NOT NULL,
  `comment_active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`lrcomment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `logbook_rotation_notifications` (
  `lrnotification_id` int(12) NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) NOT NULL,
  `rotation_id` int(12) NOT NULL,
  `notified` int(1) NOT NULL DEFAULT '0',
  `updated_date` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`lrnotification_id`),
  UNIQUE KEY `proxy_id` (`proxy_id`,`rotation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE `logbook_rotation_sites` (
  `lrsite_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `site_description` varchar(255) DEFAULT NULL,
  `rotation_id` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`lrsite_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE `logbook_virtual_patient_objectives` (
  `lvpobjective_id` int(12) unsigned NOT NULL DEFAULT '0',
  `objective_id` int(12) unsigned DEFAULT NULL,
  `lvpatient_id` int(12) unsigned DEFAULT NULL,
  PRIMARY KEY (`lvpobjective_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE `logbook_virtual_patients` (
  `lvpatient_id` int(12) unsigned NOT NULL DEFAULT '0',
  `title` varchar(30) DEFAULT NULL,
  `url` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`lvpatient_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE `lottery_clerk_streams` (
  `lcstream_id` int(12) NOT NULL AUTO_INCREMENT,
  `lottery_clerk_id` int(12) NOT NULL DEFAULT '0',
  `category_id` int(12) NOT NULL DEFAULT '0',
  `rationale` text,
  `stream_order` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`lcstream_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE `lottery_clerks` (
  `lottery_clerk_id` int(12) NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `discipline_1` int(12) NOT NULL DEFAULT '0',
  `discipline_2` int(12) NOT NULL DEFAULT '0',
  `discipline_3` int(12) NOT NULL DEFAULT '0',
  `chosen_stream` int(12) NOT NULL DEFAULT '0',
  `chosen_rationale` text,
  `chosen_order` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`lottery_clerk_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE `notification_log` (
  `nlog_id` int(12) NOT NULL AUTO_INCREMENT,
  `notification_id` int(12) NOT NULL DEFAULT '0',
  `user_id` int(12) NOT NULL DEFAULT '0',
  `nlog_timestamp` bigint(64) NOT NULL DEFAULT '0',
  `nlog_address` varchar(128) NOT NULL DEFAULT '',
  `nlog_message` text NOT NULL,
  `prune_after` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nlog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `notification_messages` (
  `nmessage_id` int(12) NOT NULL AUTO_INCREMENT,
  `form_type` varchar(12) NOT NULL DEFAULT 'rotation',
  `nmessage_title` varchar(128) NOT NULL DEFAULT '',
  `nmessage_version` int(4) NOT NULL DEFAULT '0',
  `nmessage_from_email` varchar(128) NOT NULL DEFAULT 'eval@meds.queensu.ca',
  `nmessage_from_name` varchar(64) NOT NULL DEFAULT 'Evaluation System',
  `nmessage_reply_email` varchar(128) NOT NULL DEFAULT 'eval@meds.queensu.ca',
  `nmessage_reply_name` varchar(64) NOT NULL DEFAULT 'Evaluation System',
  `nmessage_subject` varchar(255) NOT NULL DEFAULT '',
  `nmessage_body` text NOT NULL,
  `modified_last` bigint(64) NOT NULL DEFAULT '0',
  `modified_by` int(12) NOT NULL DEFAULT '0',
  `nmessage_status` varchar(12) NOT NULL DEFAULT 'published',
  PRIMARY KEY (`nmessage_id`),
  KEY `form_type` (`form_type`),
  KEY `nmessage_version` (`nmessage_version`),
  KEY `nmessage_status` (`nmessage_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `notification_monitor` (
  `nmonitor_id` int(12) NOT NULL AUTO_INCREMENT,
  `item_id` int(12) NOT NULL DEFAULT '0',
  `form_id` int(12) NOT NULL DEFAULT '0',
  `category_id` int(12) NOT NULL DEFAULT '0',
  `category_recurse` int(2) NOT NULL DEFAULT '1',
  `item_title` varchar(128) NOT NULL DEFAULT '',
  `item_maxinstances` int(12) NOT NULL DEFAULT '1',
  `item_start` int(12) NOT NULL DEFAULT '1',
  `item_end` int(12) NOT NULL DEFAULT '30',
  `item_status` varchar(12) NOT NULL DEFAULT 'published',
  `modified_last` bigint(64) NOT NULL DEFAULT '0',
  `modified_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nmonitor_id`),
  KEY `item_id` (`item_id`),
  KEY `form_id` (`form_id`),
  KEY `category_id` (`category_id`),
  KEY `category_recurse` (`category_recurse`),
  KEY `item_start` (`item_start`),
  KEY `item_end` (`item_end`),
  KEY `item_status` (`item_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `notifications` (
  `notification_id` int(12) NOT NULL AUTO_INCREMENT,
  `user_id` int(12) NOT NULL DEFAULT '0',
  `event_id` int(12) NOT NULL DEFAULT '0',
  `category_id` int(12) NOT NULL DEFAULT '0',
  `item_id` int(12) NOT NULL DEFAULT '0',
  `item_maxinstances` int(4) NOT NULL DEFAULT '1',
  `notification_status` varchar(16) NOT NULL DEFAULT 'initiated',
  `notified_last` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  KEY `event_id` (`event_id`),
  KEY `category_id` (`category_id`),
  KEY `item_id` (`item_id`),
  KEY `item_maxinstances` (`item_maxinstances`),
  KEY `notification_status` (`notification_status`),
  KEY `notified_last` (`notified_last`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `other_teachers` (
  `oteacher_id` int(12) NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `firstname` varchar(50) NOT NULL DEFAULT '',
  `lastname` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(150) NOT NULL DEFAULT '',
  PRIMARY KEY (`oteacher_id`),
  KEY `proxy_id` (`proxy_id`),
  KEY `firstname` (`firstname`),
  KEY `lastname` (`lastname`),
  KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `regions` (
  `region_id` int(12) NOT NULL AUTO_INCREMENT,
  `region_name` varchar(64) NOT NULL DEFAULT '',
  `manage_apartments` int(1) NOT NULL DEFAULT '0',
  `is_core` int(1) NOT NULL DEFAULT '0',
  `countries_id` int(12) DEFAULT NULL,
  `prov_state` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`region_id`),
  KEY `region_name` (`region_name`),
  KEY `manage_apartments` (`manage_apartments`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

