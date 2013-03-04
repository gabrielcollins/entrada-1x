SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `ar_book_chapter_mono` (
  `book_chapter_mono_id` int(11) NOT NULL auto_increment,
  `title` text NOT NULL,
  `source` varchar(200) NOT NULL,
  `author_list` varchar(200) NOT NULL,
  `editor_list` varchar(200) DEFAULT NULL,
  `category` varchar(10) DEFAULT NULL,
  `epub_url` text,
  `status_date` varchar(8) DEFAULT NULL,
  `epub_date` varchar(8) NOT NULL,
  `volume` varchar(25) DEFAULT NULL,
  `edition` varchar(25) DEFAULT NULL,
  `pages` varchar(25) DEFAULT NULL,
  `role_id` int(3) NOT NULL,
  `type_id` int(3) NOT NULL,
  `status` varchar(25) NOT NULL,
  `group_id` int(3) DEFAULT NULL,
  `hospital_id` int(3) DEFAULT NULL,
  `pubmed_id` varchar(200) NOT NULL,
  `year_reported` int(4) NOT NULL,
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`book_chapter_mono_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_clinical_activity` (
  `clinical_activity_id` int(11) NOT NULL auto_increment,
  `site` varchar(150) NOT NULL DEFAULT '',
  `site_description` text,
  `description` text NOT NULL,
  `average_hours` int(11) DEFAULT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`clinical_activity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_clinical_education` (
  `clinical_education_id` int(11) NOT NULL auto_increment,
  `level` varchar(150) NOT NULL DEFAULT '',
  `level_description` varchar(255) DEFAULT NULL,
  `location` varchar(150) NOT NULL DEFAULT '',
  `location_description` varchar(255) DEFAULT NULL,
  `average_hours` int(11) NOT NULL DEFAULT '0',
  `research_percentage` int(1) DEFAULT '0',
  `description` text NOT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`clinical_education_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_clinical_innovation` (
  `clinical_innovation_id` int(11) NOT NULL auto_increment,
  `description` text NOT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`clinical_innovation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_clinics` (
  `clinics_id` int(11) NOT NULL auto_increment,
  `clinic` varchar(150) NOT NULL DEFAULT '',
  `patients` int(11) NOT NULL DEFAULT '0',
  `half_days` int(11) NOT NULL DEFAULT '0',
  `new_repeat` varchar(25) NOT NULL DEFAULT '',
  `weeks` int(2) NOT NULL DEFAULT '0',
  `average_clerks` int(11) NOT NULL DEFAULT '0',
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`clinics_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_conference_papers` (
  `conference_papers_id` int(11) NOT NULL auto_increment,
  `lectures_papers_list` text NOT NULL,
  `status` varchar(25) NOT NULL default '',
  `institution` text NOT NULL,
  `location` varchar(250) default NULL,
  `countries_id` int(12) default NULL,
  `city` varchar(100) default NULL,
  `prov_state` varchar(200) default NULL,
  `type` varchar(30) NOT NULL default '',
  `year_reported` int(4) NOT NULL default '0',
  `proxy_id` int(11) default NULL,
  `updated_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) default NULL,
  PRIMARY KEY (`conference_papers_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_consults` (
  `consults_id` int(11) NOT NULL auto_increment,
  `activity` varchar(250) NOT NULL DEFAULT '',
  `site` varchar(150) NOT NULL DEFAULT '',
  `site_description` text,
  `months` int(2) NOT NULL DEFAULT '0',
  `average_consults` int(11) NOT NULL DEFAULT '0',
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`consults_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_continuing_education` (
  `continuing_education_id` int(11) NOT NULL auto_increment,
  `unit` varchar(150) NOT NULL DEFAULT '',
  `location` varchar(150) NOT NULL DEFAULT '',
  `average_hours` int(11) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `start_month` int(2) NOT NULL DEFAULT '0',
  `start_year` int(4) NOT NULL DEFAULT '0',
  `end_month` int(2) NOT NULL DEFAULT '0',
  `end_year` int(4) NOT NULL DEFAULT '0',
  `total_hours` int(11) NOT NULL DEFAULT '0',
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`continuing_education_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_external_contributions` (
  `external_contributions_id` int(11) NOT NULL auto_increment,
  `organisation` varchar(255) NOT NULL default '',
  `city_country` text,
  `countries_id` int(12) default NULL,
  `city` varchar(100) default NULL,
  `prov_state` varchar(200) default NULL,
  `role` varchar(150) default NULL,
  `role_description` text,
  `description` text NOT NULL,
  `days_of_year` int(3) NOT NULL default '0',
  `year_reported` int(4) NOT NULL default '0',
  `proxy_id` int(11) default NULL,
  `updated_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) default NULL,
  PRIMARY KEY (`external_contributions_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_graduate_supervision` (
  `graduate_supervision_id` int(11) NOT NULL auto_increment,
  `student_name` varchar(150) NOT NULL DEFAULT '',
  `degree` varchar(25) NOT NULL DEFAULT '',
  `active` varchar(8) NOT NULL DEFAULT '',
  `supervision` varchar(7) NOT NULL DEFAULT '',
  `year_started` int(4) NOT NULL DEFAULT '0',
  `thesis_defended` char(3) NOT NULL DEFAULT '',
  `comments` text,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`graduate_supervision_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_graduate_teaching` (
  `graduate_teaching_id` int(11) NOT NULL auto_increment,
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
  PRIMARY KEY (`graduate_teaching_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_innovation` (
  `innovation_id` int(11) NOT NULL auto_increment,
  `course_number` varchar(25) DEFAULT NULL,
  `course_name` text,
  `type` varchar(150) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`innovation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_internal_contributions` (
  `internal_contributions_id` int(11) NOT NULL auto_increment,
  `activity_type` varchar(150) NOT NULL DEFAULT '',
  `activity_type_description` text,
  `role` varchar(150) NOT NULL DEFAULT '',
  `role_description` text,
  `description` text NOT NULL,
  `time_commitment` int(11) NOT NULL DEFAULT '0',
  `commitment_type` varchar(10) NOT NULL DEFAULT 'week',
  `start_month` int(2) NOT NULL DEFAULT '0',
  `start_year` int(4) DEFAULT NULL,
  `end_month` int(2) DEFAULT '0',
  `end_year` int(4) DEFAULT '0',
  `meetings_attended` int(3) DEFAULT NULL,
  `type` varchar(50) NOT NULL DEFAULT '',
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`internal_contributions_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_lu_activity_types` (
  `id` int(11) NOT NULL auto_increment,
  `activity_type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_activity_types` (`id`, `activity_type`) VALUES
(1, 'Lecture'),
(2, 'Seminar'),
(3, 'Workshop'),
(4, 'Other');

CREATE TABLE IF NOT EXISTS `ar_lu_clinical_locations` (
  `id` int(11) NOT NULL auto_increment,
  `clinical_location` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_clinical_locations` (`id`, `clinical_location`) VALUES
(1, 'Hospital A'),
(2, 'Hospital B'),
(3, 'Hospital C'),
(4, 'Other (specify)');

CREATE TABLE IF NOT EXISTS `ar_lu_conference_paper_types` (
  `id` int(11) NOT NULL auto_increment,
  `conference_paper_type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_conference_paper_types` (`id`, `conference_paper_type`) VALUES
(1, 'Invited Lecture'),
(2, 'Invited Conference Paper');

CREATE TABLE IF NOT EXISTS `ar_lu_consult_locations` (
  `id` int(11) NOT NULL auto_increment,
  `consult_location` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_consult_locations` (`id`, `consult_location`) VALUES
(1, 'Hospital A'),
(2, 'Hospital B'),
(3, 'Hospital C'),
(4, 'Other (specify)');

CREATE TABLE IF NOT EXISTS `ar_lu_contribution_roles` (
  `id` int(11) NOT NULL auto_increment,
  `contribution_role` varchar(50) NOT NULL,
  `visible` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_contribution_roles` (`id`, `contribution_role`) VALUES
(1, 'Advisor'),
(2, 'Chair'),
(3, 'Co-Chair'),
(4, 'Consultant'),
(5, 'Delegate'),
(6, 'Deputy Head'),
(7, 'Director'),
(8, 'Head'),
(9, 'Member'),
(10, 'Past President'),
(11, 'President'),
(12, 'Representative'),
(13, 'Secretary'),
(14, 'Vice Chair'),
(15, 'Vice President'),
(16, 'Other (specify)'),
(17, 'Site Leader on a Clinical Trial');

CREATE TABLE IF NOT EXISTS `ar_lu_contribution_types` (
  `id` int(11) NOT NULL auto_increment,
  `contribution_type` varchar(50) NOT NULL,
  `visible` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_contribution_types` (`id`, `contribution_type`) VALUES
(1, 'Accreditation Committee'),
(2, 'Committee (specify)'),
(3, 'Council (specify)'),
(4, 'Faculty Board'),
(5, 'Search Committee (specify)'),
(6, 'Senate'),
(7, 'Senate Committee (specify)'),
(8, 'Subcommittee (specify)'),
(9, 'Other (specify)');

CREATE TABLE IF NOT EXISTS `ar_lu_degree_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `degree_type` varchar(50) NOT NULL DEFAULT '',
  `visible` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_degree_types` (`id`, `degree_type`, `visible`) VALUES
(1, 'BA', 1),
(2, 'BSc', 1),
(3, 'BNSc', 1),
(4, 'MA', 1),
(5, 'MD', 1),
(6, 'M ED', 1),
(7, 'MES', 1),
(8, 'MSc', 1),
(9, 'MScOT', 1),
(10, 'MSc OT (Project)', 1),
(11, 'MScPT', 1),
(12, 'MSC PT (Project)', 1),
(13, 'PDF', 1),
(14, 'PhD', 1),
(15, 'Clinical Fellow', 1),
(16, 'Summer Research Student', 1),
(17, 'MPA Candidate', 1);

CREATE TABLE IF NOT EXISTS `ar_lu_education_locations` (
  `id` int(11) NOT NULL auto_increment,
  `education_location` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_education_locations` (`id`, `education_location`) VALUES
(1, 'Hospital A'),
(2, 'Hospital B'),
(3, 'Hospital C'),
(4, 'Other (specify)');

CREATE TABLE IF NOT EXISTS `ar_lu_focus_groups` (
  `group_id` int(11) NOT NULL auto_increment,
  `focus_group` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`group_id`),
  KEY `focus_group` (`focus_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_focus_groups` (`group_id`, `focus_group`) VALUES
(1, 'Cancer'),
(2, 'Neurosciences'),
(3, 'Cardiovascular, Circulatory and Respiratory'),
(4, 'Gastrointestinal'),
(5, 'Musculoskeletal\n'),
(6, 'Health Services Research'),
(15, 'Other'),
(7, 'Protein Function and Discovery'),
(8, 'Reproductive Sciences'),
(9, 'Genetics'),
(10, 'Nursing'),
(11, 'Primary Care Studies'),
(12, 'Emergency'),
(13, 'Critical Care'),
(14, 'Nephrology'),
(16, 'Educational Research'),
(17, 'Microbiology and Immunology'),
(18, 'Urology'),
(19, 'Psychiatry'),
(20, 'Anesthesiology'),
(22, 'Obstetrics and Gynecology'),
(23, 'Rehabilitation Therapy'),
(24, 'Occupational Therapy');

CREATE TABLE IF NOT EXISTS `ar_lu_hospital_location` (
  `hosp_id` int(11) NOT NULL DEFAULT '0',
  `hosp_desc` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`hosp_id`),
  KEY `hosp_desc` (`hosp_desc`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_hospital_location` (`hosp_id`, `hosp_desc`) VALUES
(1, 'Hospital A'),
(2, 'Hospital B'),
(3, 'Hospital C');

CREATE TABLE IF NOT EXISTS `ar_lu_innovation_types` (
  `id` int(11) NOT NULL auto_increment,
  `innovation_type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_innovation_types` (`id`, `innovation_type`) VALUES
(1, 'Course Design'),
(2, 'Curriculum Development'),
(3, 'Educational Materials Development'),
(4, 'Software Development'),
(5, 'Educational Planning and Policy Development'),
(6, 'Development of Innovative Teaching Methods');

CREATE TABLE IF NOT EXISTS `ar_lu_membership_roles` (
  `id` int(11) NOT NULL auto_increment,
  `membership_role` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_membership_roles` (`id`, `membership_role`) VALUES
(1, 'Examining Committee'),
(2, 'Comprehensive Exam Committee'),
(3, 'Mini Masters'),
(4, 'Supervisory Committee'),
(5, 'Other (specify)');

CREATE TABLE IF NOT EXISTS `ar_lu_on_call_locations` (
  `id` int(11) NOT NULL auto_increment,
  `on_call_location` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_on_call_locations` (`id`, `on_call_location`) VALUES
(1, 'Hospital A'),
(2, 'Hospital B'),
(3, 'Hospital C'),
(4, 'Other (specify)');

CREATE TABLE IF NOT EXISTS `ar_lu_other_locations` (
  `id` int(11) NOT NULL auto_increment,
  `other_location` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_other_locations` (`id`, `other_location`) VALUES
(1, 'Hospital A'),
(2, 'Hospital B'),
(3, 'Hospital C'),
(4, 'Other (specify)');

CREATE TABLE IF NOT EXISTS `ar_lu_patent_types` (
  `id` int(11) NOT NULL auto_increment,
  `patent_type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_patent_types` (`id`, `patent_type`) VALUES
(1, 'License Granted'),
(2, 'Non-Disclosure Agreement'),
(3, 'Patent Applied For'),
(4, 'Patent Obtained');

CREATE TABLE IF NOT EXISTS `ar_lu_pr_roles` (
  `role_id` int(11) NOT NULL default '0',
  `role_description` varchar(50) NOT NULL default '',
  PRIMARY KEY (`role_id`),
  KEY `role_description` (`role_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_pr_roles` (`role_id`, `role_description`) VALUES
(1, 'First Author'),
(2, 'Corresponding Author'),
(3, 'Contributing Author');

CREATE TABLE IF NOT EXISTS `ar_lu_prize_categories` (
  `id` int(11) NOT NULL auto_increment,
  `prize_category` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_prize_categories` (`id`, `prize_category`) VALUES
(1, 'Research'),
(2, 'Teaching'),
(3, 'Service');

CREATE TABLE IF NOT EXISTS `ar_lu_prize_types` (
  `id` int(11) NOT NULL auto_increment,
  `prize_type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_prize_types` (`id`, `prize_type`) VALUES
(1, 'Award'),
(2, 'Honour'),
(3, 'Prize');

CREATE TABLE IF NOT EXISTS `ar_lu_profile_roles` (
  `id` int(11) NOT NULL auto_increment,
  `profile_role` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_profile_roles` (`id`, `profile_role`) VALUES
(1, 'Researcher/Scholar'),
(2, 'Educator/Scholar'),
(3, 'Clinician/Scholar');

CREATE TABLE IF NOT EXISTS `ar_lu_publication_statuses` (
  `id` int(11) NOT NULL auto_increment,
  `publication_status` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_publication_statuses` (`id`, `publication_status`) VALUES
(1, 'Accepted'),
(2, 'In Press'),
(3, 'Presented'),
(4, 'Published'),
(5, 'Submitted');

CREATE TABLE IF NOT EXISTS `ar_lu_publication_type` (
  `type_id` int(11) NOT NULL DEFAULT '0',
  `type_description` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`type_id`),
  KEY `type_description` (`type_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_publication_type` (`type_id`, `type_description`) VALUES
(1, 'Peer-Reviewed Article'),
(2, 'Non-Peer-Reviewed Article'),
(3, 'Chapter'),
(4, 'Peer-Reviewed Abstract'),
(5, 'Non-Peer-Reviewed Abstract'),
(6, 'Complete Book'),
(7, 'Monograph'),
(8, 'Editorial'),
(9, 'Published Conference Proceeding'),
(10, 'Poster Presentations'),
(11, 'Technical Report');

CREATE TABLE IF NOT EXISTS `ar_lu_research_types` (
  `id` int(11) NOT NULL auto_increment,
  `research_type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_research_types` (`id`, `research_type`) VALUES
(1, 'Infrastructure'),
(2, 'Operating'),
(3, 'Salary'),
(4, 'Training');

CREATE TABLE IF NOT EXISTS `ar_lu_scholarly_types` (
  `id` int(11) NOT NULL auto_increment,
  `scholarly_type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_scholarly_types` (`id`, `scholarly_type`) VALUES
(1, 'Granting Body Referee'),
(2, 'Journal Editorship'),
(3, 'Journal Referee'),
(4, 'Other (specify)');

CREATE TABLE IF NOT EXISTS `ar_lu_self_education_types` (
  `id` int(11) NOT NULL auto_increment,
  `self_education_type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_self_education_types` (`id`, `self_education_type`) VALUES
(1, 'Clinical'),
(2, 'Research'),
(3, 'Teaching'),
(4, 'Service/Administrative'),
(5, 'Other');

CREATE TABLE IF NOT EXISTS `ar_lu_supervision_types` (
  `id` int(11) NOT NULL auto_increment,
  `supervision_type` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_supervision_types` (`id`, `supervision_type`) VALUES
(1, 'Joint'),
(2, 'Sole');

CREATE TABLE IF NOT EXISTS `ar_lu_trainee_levels` (
  `id` int(11) NOT NULL auto_increment,
  `trainee_level` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_trainee_levels` (`id`, `trainee_level`) VALUES
(1, 'Clerk(s)'),
(2, 'Clinical Fellow(s)'),
(3, 'International Med. Graduate'),
(4, 'PGY (specify)'),
(5, 'Other (specify)');

CREATE TABLE IF NOT EXISTS `ar_lu_undergraduate_supervision_courses` (
  `id` int(11) NOT NULL auto_increment,
  `undergarduate_supervision_course` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_undergraduate_supervision_courses` (`id`, `undergarduate_supervision_course`) VALUES
(1, 'ANAT-499'),
(2, 'BCHM-421'),
(3, 'BCHM-422'),
(4, 'MICR-499'),
(5, 'PATH-499'),
(6, 'PHAR-499'),
(7, 'PHGY-499'),
(8, 'NURS-490'),
(9, 'ANAT499'),
(10, 'BCHM421'),
(11, 'BCHM422'),
(12, 'MICR499'),
(13, 'PATH499'),
(14, 'PHAR499'),
(15, 'PHGY499'),
(16, 'NURS490'),
(17, 'ANAT 499'),
(18, 'BCHM 421'),
(19, 'BCHM 422'),
(20, 'MICR 499'),
(21, 'PATH 499'),
(22, 'PHAR 499'),
(23, 'PHGY 499'),
(24, 'NURS 490');

CREATE TABLE IF NOT EXISTS `ar_memberships` (
  `memberships_id` int(11) NOT NULL auto_increment,
  `student_name` varchar(150) NOT NULL DEFAULT '',
  `degree` varchar(25) NOT NULL DEFAULT '',
  `department` varchar(150) NOT NULL DEFAULT '',
  `university` varchar(255) NOT NULL DEFAULT '',
  `role` varchar(100) NOT NULL DEFAULT '',
  `role_description` text,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`memberships_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_non_peer_reviewed_papers` (
  `non_peer_reviewed_papers_id` int(11) NOT NULL auto_increment,
  `title` text NOT NULL,
  `source` varchar(200) NOT NULL,
  `author_list` varchar(200) NOT NULL,
  `category` varchar(10) DEFAULT NULL,
  `epub_url` text,
  `status_date` varchar(8) DEFAULT NULL,
  `epub_date` varchar(8) NOT NULL,
  `volume` varchar(25) DEFAULT NULL,
  `edition` varchar(25) DEFAULT NULL,
  `pages` varchar(25) DEFAULT NULL,
  `role_id` int(3) NOT NULL,
  `type_id` int(3) NOT NULL,
  `status` varchar(25) NOT NULL,
  `group_id` int(3) DEFAULT NULL,
  `hospital_id` int(3) DEFAULT NULL,
  `pubmed_id` varchar(200) NOT NULL,
  `year_reported` int(4) NOT NULL,
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`non_peer_reviewed_papers_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_on_call` (
  `on_call_id` int(11) NOT NULL auto_increment,
  `site` varchar(150) NOT NULL DEFAULT '',
  `site_description` text,
  `frequency` varchar(250) DEFAULT NULL,
  `special_features` text NOT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`on_call_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_other` (
  `other_id` int(11) NOT NULL auto_increment,
  `course_name` text NOT NULL,
  `type` varchar(150) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`other_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_other_activity` (
  `other_activity_id` int(11) NOT NULL auto_increment,
  `site` varchar(150) NOT NULL DEFAULT '',
  `site_description` text,
  `average_hours` int(11) DEFAULT NULL,
  `special_features` text NOT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`other_activity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_patent_activity` (
  `patent_activity_id` int(11) NOT NULL auto_increment,
  `patent_activity_type` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`patent_activity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_peer_reviewed_papers` (
  `peer_reviewed_papers_id` int(11) NOT NULL auto_increment,
  `title` text NOT NULL,
  `source` varchar(200) NOT NULL,
  `author_list` varchar(200) NOT NULL,
  `category` varchar(10) DEFAULT NULL,
  `epub_url` text,
  `status_date` varchar(8) DEFAULT NULL,
  `epub_date` varchar(8) NOT NULL,
  `volume` varchar(25) DEFAULT NULL,
  `edition` varchar(25) DEFAULT NULL,
  `pages` varchar(25) DEFAULT NULL,
  `role_id` int(3) NOT NULL,
  `type_id` int(3) NOT NULL,
  `status` varchar(25) NOT NULL,
  `group_id` int(3) DEFAULT NULL,
  `hospital_id` int(3) DEFAULT NULL,
  `pubmed_id` varchar(200) NOT NULL,
  `keywords` text,
  `year_reported` int(4) NOT NULL,
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`peer_reviewed_papers_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_poster_reports` (
  `poster_reports_id` int(11) NOT NULL auto_increment,
  `title` text NOT NULL,
  `source` varchar(200) NOT NULL,
  `author_list` varchar(200) NOT NULL,
  `editor_list` varchar(200) DEFAULT NULL,
  `epub_url` text,
  `status_date` varchar(8) DEFAULT NULL,
  `epub_date` varchar(8) NOT NULL,
  `volume` varchar(25) DEFAULT NULL,
  `edition` varchar(25) DEFAULT NULL,
  `pages` varchar(25) DEFAULT NULL,
  `role_id` int(3) NOT NULL,
  `type_id` int(3) NOT NULL,
  `status` varchar(25) NOT NULL,
  `group_id` int(3) DEFAULT NULL,
  `hospital_id` int(3) DEFAULT NULL,
  `pubmed_id` varchar(200) NOT NULL,
  `year_reported` int(4) NOT NULL,
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`poster_reports_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_prizes` (
  `prizes_id` int(11) NOT NULL auto_increment,
  `category` varchar(150) NOT NULL DEFAULT '',
  `prize_type` varchar(150) DEFAULT NULL,
  `description` text,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`prizes_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_procedures` (
  `procedures_id` int(11) NOT NULL auto_increment,
  `site` varchar(150) NOT NULL DEFAULT '',
  `site_description` text,
  `average_hours` int(11) DEFAULT NULL,
  `special_features` text NOT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`procedures_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_profile` (
  `profile_id` int(11) NOT NULL auto_increment,
  `education` float(5,2) NOT NULL DEFAULT '0.00',
  `research` float(5,2) NOT NULL DEFAULT '0.00',
  `clinical` float(5,2) NOT NULL DEFAULT '0.00',
  `combined` float(5,2) NOT NULL DEFAULT '0.00',
  `service` float(5,2) NOT NULL DEFAULT '0.00',
  `total` float(5,2) NOT NULL DEFAULT '0.00',
  `hospital_hours` int(11) NOT NULL DEFAULT '0',
  `on_call_hours` int(11) NOT NULL DEFAULT '0',
  `consistent` char(3) NOT NULL DEFAULT '',
  `consistent_comments` text,
  `career_goals` char(3) NOT NULL DEFAULT '',
  `career_comments` text,
  `roles` text NOT NULL,
  `roles_compatible` char(3) NOT NULL DEFAULT '',
  `roles_comments` text,
  `education_comments` text,
  `research_comments` text,
  `clinical_comments` text,
  `service_comments` text,
  `comments` text,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `department` text NOT NULL,
  `cross_department` text,
  `report_completed` char(3) DEFAULT NULL,
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`profile_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_research` (
  `research_id` int(11) NOT NULL auto_increment,
  `status` varchar(10) default NULL,
  `grant_title` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `location` varchar(25) default NULL,
  `multiinstitutional` varchar(3) default NULL,
  `agency` text,
  `role` varchar(50) NOT NULL,
  `principal_investigator` varchar(100) NOT NULL default '',
  `co_investigator_list` text,
  `amount_received` decimal(20,2) NOT NULL default '0.00',
  `start_month` int(2) NOT NULL default '0',
  `start_year` int(4) NOT NULL default '0',
  `end_month` int(2) default '0',
  `end_year` int(4) default '0',
  `year_reported` int(4) NOT NULL default '0',
  `funding_status` varchar(9) NOT NULL default '',
  `proxy_id` int(11) default NULL,
  `updated_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) default NULL,
  PRIMARY KEY (`research_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_scholarly_activity` (
  `scholarly_activity_id` int(11) NOT NULL auto_increment,
  `scholarly_activity_type` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `location` varchar(25) DEFAULT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`scholarly_activity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_self_education` (
  `self_education_id` int(11) NOT NULL auto_increment,
  `description` text NOT NULL,
  `activity_type` varchar(150) NOT NULL DEFAULT '',
  `institution` varchar(255) NOT NULL DEFAULT '',
  `start_month` int(2) NOT NULL DEFAULT '0',
  `start_year` int(4) NOT NULL DEFAULT '0',
  `end_month` int(2) NOT NULL DEFAULT '0',
  `end_year` int(4) NOT NULL DEFAULT '0',
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`self_education_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
  PRIMARY KEY (`undergraduate_nonmedical_teaching_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_undergraduate_supervision` (
  `undergraduate_supervision_id` int(11) NOT NULL auto_increment,
  `student_name` varchar(150) NOT NULL DEFAULT '',
  `degree` varchar(25) NOT NULL DEFAULT '',
  `course_number` varchar(25) DEFAULT NULL,
  `supervision` varchar(7) NOT NULL DEFAULT '',
  `comments` text,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`undergraduate_supervision_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_undergraduate_teaching` (
  `undergraduate_teaching_id` int(11) NOT NULL auto_increment,
  `course_number` varchar(25) NOT NULL default '',
  `course_name` text NOT NULL,
  `lecture_phase` varchar(6) default NULL,
  `assigned` char(3) NOT NULL default '',
  `lecture_hours` decimal(20,2) default '0.00',
  `lab_hours` decimal(20,2) default '0.00',
  `small_group_hours` decimal(20,2) default '0.00',
  `patient_contact_session_hours` decimal(20,2) default '0.00',
  `symposium_hours` decimal(20,2) default '0.00',
  `directed_independant_learning_hours` decimal(20,2) default '0.00',
  `review_feedback_session_hours` decimal(20,2) default '0.00',
  `examination_hours` decimal(20,2) default '0.00',
  `clerkship_seminar_hours` decimal(20,2) default '0.00',
  `other_hours` decimal(20,2) default '0.00',
  `coord_enrollment` int(11) default '0',
  `comments` text,
  `year_reported` int(4) NOT NULL default '0',
  `proxy_id` int(11) default NULL,
  `updated_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` varchar(7) NOT NULL default '',
  PRIMARY KEY (`undergraduate_teaching_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_ward_supervision` (
  `ward_supervision_id` int(11) NOT NULL auto_increment,
  `service` varchar(150) NOT NULL DEFAULT '',
  `average_patients` int(11) NOT NULL DEFAULT '0',
  `months` int(2) NOT NULL DEFAULT '0',
  `average_clerks` int(11) NOT NULL DEFAULT '0',
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`ward_supervision_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `assessments` (
  `assessment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(10) unsigned NOT NULL,
  `cohort` varchar(35) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `type` varchar(255) NOT NULL,
  `marking_scheme_id` int(10) unsigned NOT NULL,
  `numeric_grade_points_total` float unsigned DEFAULT NULL,
  `grade_weighting` float NOT NULL DEFAULT '0',
  `narrative` tinyint(1) NOT NULL DEFAULT '0',
  `required` tinyint(1) NOT NULL DEFAULT '1',
  `characteristic_id` int(4) NOT NULL,
  `show_learner` tinyint(1) NOT NULL DEFAULT '0',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `order` smallint(6) NOT NULL DEFAULT '0',
  `grade_threshold` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`assessment_id`),
  KEY `order` (`order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `assessment_options` (
  `aoption_id` int(12) NOT NULL AUTO_INCREMENT,
  `assessment_id` int(12) NOT NULL DEFAULT '0',
  `option_id` int(12) NOT NULL DEFAULT '0',
  `option_active` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`aoption_id`),
  KEY `assessment_id` (`assessment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `assessments_lu_meta` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) unsigned NOT NULL DEFAULT '0',
  `type` enum('rating','project','exam','paper','asessment','presentation','quiz','RAT','reflection') DEFAULT NULL,
  `title` varchar(60) NOT NULL,
  `description` text,
  `active` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `assessments_lu_meta` (`organisation_id`, `type`, `title`) VALUES
('1', '1', 'Faculty, resident or preceptor rating'),
('1', '2', 'Final project'),
('1', '3', 'Final written examination'),
('1', '3', 'Laboratory or practical examination (except OSCE/SP)'),
('1', '3', 'Midterm examination'),
('1', '3', 'NBME subject examination'),
('1', '3', 'Oral exam'),
('1', '3', 'OSCE/SP examination'),
('1', '4', 'Paper'),
('1', '5', 'Peer-assessment'),
('1', '6', 'Presentation'),
('1', '7', 'Quiz'),
('1', '8', 'RAT'),
('1', '9', 'Reflection'),
('1', '5', 'Self-assessment'),
('1', '5', 'Other assessments');

CREATE TABLE IF NOT EXISTS `assessments_lu_meta_options` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(60) NOT NULL,
  `active` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `assessments_lu_meta_options` (`title`) VALUES
('Essay questions'),
('Fill-in, short answer questions'),
('Multiple-choice, true/false, matching questions'),
('Problem-solving written exercises');

CREATE TABLE IF NOT EXISTS `assessment_exceptions` (
  `aexception_id` int(12) NOT NULL auto_increment,
  `assessment_id` int(12) NOT NULL,
  `proxy_id` int(12) NOT NULL,
  `grade_weighting` int(11) NOT NULL default '0',
  PRIMARY KEY (`aexception_id`),
  KEY `proxy_id` (`assessment_id`,`proxy_id`),
  KEY `assessment_id` (`assessment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `assessment_grades` (
  `grade_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `assessment_id` int(10) unsigned NOT NULL,
  `proxy_id` int(10) unsigned NOT NULL,
  `value` float NOT NULL,
  `threshold_notified` int(1) NOT NULL default '0',
  PRIMARY KEY (`grade_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `assessment_marking_schemes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `handler` varchar(255) NOT NULL DEFAULT 'Boolean',
  `description` text NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `assessment_marking_schemes` (`id`,`name`,`handler`,`description`,`enabled`) VALUES
(1, 'Pass/Fail', 'Boolean', 'Enter P for Pass, or F for Fail, in the student mark column.', 1),
(2, 'Percentage', 'Percentage', 'Enter a percentage in the student mark column.', 1),
(3, 'Numeric', 'Numeric', 'Enter a numeric total in the student mark column.', 1),
(4, 'Complete/Incomplete', 'IncompleteComplete', 'Enter C for Complete, or I for Incomplete, in the student mark column.', 1);

CREATE TABLE IF NOT EXISTS `assessment_objectives` (
  `aobjective_id` int(12) NOT NULL AUTO_INCREMENT,
  `assessment_id` int(12) NOT NULL DEFAULT '0',
  `objective_id` int(12) NOT NULL DEFAULT '0',
  `importance` int(11) DEFAULT NULL,
  `objective_details` text,
  `objective_type` enum('curricular_objective','clinical_presentation') NOT NULL DEFAULT 'curricular_objective',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`aobjective_id`),
  KEY `assessment_id` (`assessment_id`),
  KEY `objective_id` (`objective_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `assignments` (
  `assignment_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `assignment_title` varchar(40) NOT NULL,
  `assignment_description` text NOT NULL,
  `assignment_active` int(11) NOT NULL,
  `required` int(1) NOT NULL,
  `due_date` bigint(64) NOT NULL DEFAULT '0',
  `assignment_uploads` int(11) NOT NULL DEFAULT '0',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`assignment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `assignment_files` (
  `afile_id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT '0',
  `proxy_id` int(11) NOT NULL,
  `file_type` varchar(24) NOT NULL DEFAULT 'submission',
  `file_title` varchar(40) NOT NULL,
  `file_description` text,
  `file_active` int(1) NOT NULL DEFAULT '1',
  `updated_date` int(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`afile_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `assignment_file_versions` (
  `afversion_id` int(11) NOT NULL AUTO_INCREMENT,
  `afile_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `proxy_id` int(11) NOT NULL,
  `file_mimetype` varchar(64) NOT NULL,
  `file_version` int(5) DEFAULT NULL,
  `file_filename` varchar(128) NOT NULL,
  `file_filesize` int(32) NOT NULL,
  `file_active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`afversion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `assignment_contacts` (
  `acontact_id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `proxy_id` int(11) NOT NULL,
  `contact_order` int(11) DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`acontact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `assignment_comments` (
  `acomment_id` int(12) NOT NULL AUTO_INCREMENT,
  `afile_id` int(12) NOT NULL DEFAULT '0',
  `assignment_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `comment_title` varchar(128) NOT NULL,
  `comment_description` text NOT NULL,
  `comment_active` int(1) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  `notify` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`acomment_id`),
  KEY `assignment_id` (`assignment_id`,`proxy_id`,`comment_active`,`updated_date`,`updated_by`),
  KEY `afile_id` (`afile_id`),
  KEY `release_date` (`release_date`),
  FULLTEXT KEY `comment_title` (`comment_title`,`comment_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `communities` (
  `community_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_parent` int(12) NOT NULL DEFAULT '0',
  `category_id` int(12) NOT NULL DEFAULT '0',
  `community_url` text NOT NULL,
  `community_template` varchar(30) NOT NULL DEFAULT 'default',
  `community_theme` varchar(12) NOT NULL DEFAULT 'default',
  `community_shortname` varchar(32) NOT NULL,
  `community_title` varchar(64) NOT NULL,
  `community_description` text NOT NULL,
  `community_keywords` varchar(255) NOT NULL,
  `community_email` varchar(128) NOT NULL,
  `community_website` text NOT NULL,
  `community_protected` int(1) NOT NULL DEFAULT '1',
  `community_registration` int(1) NOT NULL DEFAULT '1',
  `community_members` text NOT NULL,
  `community_active` int(1) NOT NULL DEFAULT '1',
  `community_opened` bigint(64) NOT NULL DEFAULT '0',
  `community_notifications` int(1) NOT NULL DEFAULT '0',
  `sub_communities` int(1) NOT NULL DEFAULT '0',
  `storage_usage` int(32) NOT NULL DEFAULT '0',
  `storage_max` int(32) NOT NULL DEFAULT '104857600',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`community_id`),
  KEY `sub_communities` (`sub_communities`),
  KEY `community_parent` (`community_parent`,`category_id`,`community_protected`,`community_registration`,`community_opened`,`updated_date`,`updated_by`),
  KEY `community_shortname` (`community_shortname`),
  KEY `max_storage` (`storage_max`),
  KEY `storage_usage` (`storage_usage`),
  KEY `community_active` (`community_active`),
  FULLTEXT KEY `community_title` (`community_title`,`community_description`,`community_keywords`),
  FULLTEXT KEY `community_url` (`community_url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `communities_template_permissions` (
  `ctpermission_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `permission_type` enum('category_id','group') DEFAULT NULL,
  `permission_value` varchar(32) DEFAULT NULL,
  `template` varchar(32) NOT NULL,
  PRIMARY KEY (`ctpermission_id`),
  KEY `permission_index` (`permission_type`,`permission_value`,`template`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `communities_template_permissions` (`ctpermission_id`, `permission_type`, `permission_value`, `template`) VALUES
(1,'','','default'),
(2,'group','faculty,staff,medtech','course'),
(3,'category_id','5','course'),
(4,'group','faculty,staff,medtech','committee'),
(5,'category_id','12','committee'),
(6,'group','faculty,staff,medtech','learningmodule'),
(7,'group','faculty,staff,medtech','virtualpatient'),
(9,'category_id','','virtualpatient'),
(8,'category_id','','learningmodule');

CREATE TABLE IF NOT EXISTS `communities_categories` (
  `category_id` int(12) NOT NULL AUTO_INCREMENT,
  `category_parent` int(12) NOT NULL DEFAULT '0',
  `category_title` varchar(64) NOT NULL,
  `category_description` text NOT NULL,
  `category_keywords` varchar(255) NOT NULL,
  `category_visible` int(1) NOT NULL DEFAULT '1',
  `category_status` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`),
  KEY `category_parent` (`category_parent`,`category_keywords`),
  KEY `category_status` (`category_status`),
  FULLTEXT KEY `category_description` (`category_description`,`category_keywords`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `communities_categories` (`category_id`, `category_parent`, `category_title`, `category_description`, `category_keywords`, `category_visible`, `category_status`) VALUES
(1, 0, 'Official Communities', '', '', 1, 0),
(2, 0, 'Other Communities', '', '', 1, 0),
(4, 1, 'Administration', 'A container for official administrative units to reside.', '', 1, 0),
(5, 1, 'Courses, etc.', 'A container for official course groups and communities to reside.', '', 1, 0),
(7, 2, 'Health & Wellness', '', '', 1, 0),
(8, 2, 'Sports & Leisure', '', '', 1, 0),
(9, 2, 'Learning & Teaching', '', '', 1, 0),
(15, 2, 'Careers in Health Care', '', '', 1, 0),
(11, 2, 'Miscellaneous', '', '', 1, 0),
(12, 1, 'Committees', '', '', 1, 0),
(14, 2, 'Social Responsibility', '', '', 1, 0),
(16, 2, 'Cultures & Communities', '', '', 1, 0),
(17, 2, 'Business & Finance', '', '', 1, 0),
(18, 2, 'Arts & Entertainment', '', '', 1, 0);

CREATE TABLE IF NOT EXISTS `communities_modules` (
  `module_id` int(12) NOT NULL AUTO_INCREMENT,
  `module_shortname` varchar(32) NOT NULL,
  `module_version` varchar(8) NOT NULL DEFAULT '1.0.0',
  `module_title` varchar(64) NOT NULL,
  `module_description` text NOT NULL,
  `module_active` int(1) NOT NULL DEFAULT '1',
  `module_visible` int(1) NOT NULL DEFAULT '1',
  `module_permissions` text NOT NULL,
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`module_id`),
  KEY `module_shortname` (`module_shortname`),
  KEY `module_active` (`module_active`),
  FULLTEXT KEY `module_title` (`module_title`,`module_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `communities_modules` (`module_id`, `module_shortname`, `module_version`, `module_title`, `module_description`, `module_active`, `module_visible`, `module_permissions`, `updated_date`, `updated_by`) VALUES
(1, 'announcements', '1.0.0', 'Announcements', 'The Announcements module allows you to post Announcements to your community.', 1, 1, 'a:4:{s:3:"add";i:1;s:6:"delete";i:1;s:4:"edit";i:1;s:5:"index";i:0;}', 1173116408, 1),
(2, 'discussions', '1.0.0', 'Discussions', 'The Discussions module is a simple method you can use to host discussions.', 1, 1, 'a:10:{s:9:"add-forum";i:1;s:8:"add-post";i:0;s:12:"delete-forum";i:1;s:11:"delete-post";i:0;s:10:"edit-forum";i:1;s:9:"edit-post";i:0;s:5:"index";i:0;s:10:"reply-post";i:0;s:10:"view-forum";i:0;s:9:"view-post";i:0;}', 1173116408, 1),
(3, 'galleries', '1.0.0', 'Galleries', 'The Galleries module allows you to add photo galleries and images to your community.', 1, 1, 'a:13:{s:11:"add-comment";i:0;s:11:"add-gallery";i:1;s:9:"add-photo";i:0;s:10:"move-photo";i:0;s:14:"delete-comment";i:0;s:14:"delete-gallery";i:1;s:12:"delete-photo";i:0;s:12:"edit-comment";i:0;s:12:"edit-gallery";i:1;s:10:"edit-photo";i:0;s:5:"index";i:0;s:12:"view-gallery";i:0;s:10:"view-photo";i:0;}', 1173116408, 1),
(4, 'shares', '1.0.0', 'Document Sharing', 'The Document Sharing module gives you the ability to upload and share documents within your community.', 1, 1, 'a:15:{s:11:"add-comment";i:0;s:10:"add-folder";i:1;s:8:"add-file";i:0;s:9:"move-file";i:0;s:12:"add-revision";i:0;s:14:"delete-comment";i:0;s:13:"delete-folder";i:1;s:11:"delete-file";i:0;s:15:"delete-revision";i:0;s:12:"edit-comment";i:0;s:11:"edit-folder";i:1;s:9:"edit-file";i:0;s:5:"index";i:0;s:11:"view-folder";i:0;s:9:"view-file";i:0;}', 1173116408, 1),
(5, 'polls', '1.0.0', 'Polling', 'This module allows communities to create their own polls for everything from adhoc open community polling to individual community member votes.', 1, 1, 'a:10:{s:8:"add-poll";i:1;s:12:"add-question";i:1;s:13:"edit-question";i:1;s:15:"delete-question";i:1;s:11:"delete-poll";i:1;s:9:"edit-poll";i:1;s:9:"view-poll";i:0;s:9:"vote-poll";i:0;s:5:"index";i:0;s:8:"my-votes";i:0;}', 1216256830, 1408),
(6, 'events', '1.0.0', 'Events', 'The Events module allows you to post events to your community which will be accessible through iCalendar ics files or viewable in the community.', 1, 1, 'a:4:{s:3:"add";i:1;s:6:"delete";i:1;s:4:"edit";i:1;s:5:"index";i:0;}', 1225209600, 3499),
(7, 'quizzes', '1.0.0', 'Quizzes', 'This module allows communities to create their own quizzes for summative or formative evaluation.', 1, 1, 'a:4:{s:5:"index";i:0;s:7:"attempt";i:0;s:7:"results";i:0;s:13:"save-response";i:0;}', 1216256830, 3499),
(8, 'mtdtracking', '1.0.0', 'MTD Tracking', 'The MTD Tracking module allows Program Assistants to enter the weekly schedule for each of their Residents.', 0, 0, 'a:2:{s:4:\"edit\";i:1;s:5:\"index\";i:0;}', 1216256830, 5440);

CREATE TABLE IF NOT EXISTS `communities_most_active` (
  `cmactive_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL,
  `activity_order` int(2) NOT NULL,
  PRIMARY KEY (`cmactive_id`,`community_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_announcements` (
  `cannouncement_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `cpage_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `announcement_active` int(1) NOT NULL DEFAULT '1',
  `pending_moderation` int(1) NOT NULL DEFAULT '0',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `announcement_title` varchar(128) NOT NULL,
  `announcement_description` text NOT NULL,
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cannouncement_id`),
  KEY `community_id` (`community_id`,`proxy_id`,`release_date`,`release_until`,`updated_date`,`updated_by`),
  FULLTEXT KEY `announcement_title` (`announcement_title`,`announcement_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_courses` (
  `community_course_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL,
  `course_id` int(12) NOT NULL,
  PRIMARY KEY (`community_course_id`),
  KEY `community_id` (`community_id`,`course_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_discussions` (
  `cdiscussion_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `cpage_id` int(12) NOT NULL DEFAULT '0',
  `forum_title` varchar(64) NOT NULL DEFAULT '',
  `forum_description` text NOT NULL,
  `forum_order` int(6) NOT NULL DEFAULT '0',
  `forum_active` int(1) NOT NULL DEFAULT '1',
  `admin_notifications` int(1) NOT NULL DEFAULT '0',
  `allow_public_read` int(1) NOT NULL DEFAULT '0',
  `allow_public_post` int(1) NOT NULL DEFAULT '0',
  `allow_public_reply` int(1) NOT NULL DEFAULT '0',
  `allow_troll_read` int(1) NOT NULL DEFAULT '1',
  `allow_troll_post` int(1) NOT NULL DEFAULT '0',
  `allow_troll_reply` int(1) NOT NULL DEFAULT '0',
  `allow_member_read` int(1) NOT NULL DEFAULT '1',
  `allow_member_post` int(1) NOT NULL DEFAULT '1',
  `allow_member_reply` int(1) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cdiscussion_id`),
  KEY `community_id` (`community_id`,`forum_order`,`allow_member_post`,`allow_member_reply`),
  KEY `release_date` (`release_date`),
  KEY `release_until` (`release_until`),
  KEY `allow_member_read` (`allow_member_read`),
  KEY `allow_public_read` (`allow_public_read`),
  KEY `allow_troll_read` (`allow_troll_read`),
  KEY `allow_troll_post` (`allow_troll_post`),
  KEY `allow_troll_reply` (`allow_troll_reply`),
  KEY `allow_public_post` (`allow_public_post`),
  KEY `allow_public_reply` (`allow_public_reply`),
  KEY `forum_active` (`forum_active`),
  KEY `admin_notification` (`admin_notifications`),
  KEY `page_id` (`cdiscussion_id`,`cpage_id`,`community_id`),
  KEY `community_id2` (`community_id`,`forum_active`,`cpage_id`,`forum_order`,`forum_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_discussion_topics` (
  `cdtopic_id` int(12) NOT NULL AUTO_INCREMENT,
  `cdtopic_parent` int(12) NOT NULL DEFAULT '0',
  `cdiscussion_id` int(12) NOT NULL DEFAULT '0',
  `community_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `anonymous` int(1) NOT NULL DEFAULT '0',
  `topic_title` varchar(128) NOT NULL DEFAULT '',
  `topic_description` text NOT NULL,
  `topic_active` int(1) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  `notify` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cdtopic_id`),
  KEY `cdiscussion_parent` (`cdtopic_parent`,`community_id`,`proxy_id`,`updated_date`,`updated_by`),
  KEY `cdiscussion_id` (`cdiscussion_id`),
  KEY `topic_active` (`topic_active`),
  KEY `release_date` (`release_date`,`release_until`),
  KEY `community_id` (`cdtopic_id`,`community_id`),
  KEY `cdtopic_parent` (`cdtopic_parent`,`community_id`),
  KEY `user` (`cdiscussion_id`,`community_id`,`topic_active`,`cdtopic_parent`,`proxy_id`,`release_date`,`release_until`),
  KEY `admin` (`cdiscussion_id`,`community_id`,`topic_active`,`cdtopic_parent`),
  KEY `post` (`proxy_id`,`community_id`,`cdtopic_id`,`cdtopic_parent`,`topic_active`),
  KEY `release` (`proxy_id`,`community_id`,`cdtopic_parent`,`topic_active`,`release_date`),
  KEY `community` (`cdtopic_id`,`community_id`),
  FULLTEXT KEY `topic_title` (`topic_title`,`topic_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_events` (
  `cevent_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `cpage_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `event_active` int(1) NOT NULL DEFAULT '1',
  `pending_moderation` int(1) NOT NULL DEFAULT '0',
  `event_start` bigint(64) NOT NULL DEFAULT '0',
  `event_finish` bigint(64) NOT NULL DEFAULT '0',
  `event_location` varchar(128) NOT NULL,
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `event_title` varchar(128) NOT NULL,
  `event_description` text NOT NULL,
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cevent_id`),
  KEY `community_id` (`community_id`, `cpage_id`, `proxy_id`,`event_start`,`event_finish`,`release_date`,`release_until`,`updated_date`,`updated_by`),
  FULLTEXT KEY `event_title` (`event_title`,`event_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_galleries` (
  `cgallery_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `cpage_id` int(12) NOT NULL DEFAULT '0',
  `gallery_title` varchar(64) NOT NULL,
  `gallery_description` text NOT NULL,
  `gallery_cgphoto_id` int(12) NOT NULL DEFAULT '0',
  `gallery_order` int(6) NOT NULL DEFAULT '0',
  `gallery_active` int(1) NOT NULL DEFAULT '1',
  `admin_notifications` int(1) NOT NULL DEFAULT '0',
  `allow_public_read` int(1) NOT NULL DEFAULT '0',
  `allow_public_upload` int(1) NOT NULL DEFAULT '0',
  `allow_public_comment` int(1) NOT NULL DEFAULT '0',
  `allow_troll_read` int(1) NOT NULL DEFAULT '1',
  `allow_troll_upload` int(1) NOT NULL DEFAULT '0',
  `allow_troll_comment` int(1) NOT NULL DEFAULT '0',
  `allow_member_read` int(1) NOT NULL DEFAULT '1',
  `allow_member_upload` int(1) NOT NULL DEFAULT '1',
  `allow_member_comment` int(1) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cgallery_id`),
  KEY `community_id` (`community_id`,`gallery_order`,`allow_member_upload`,`allow_member_comment`),
  KEY `release_date` (`release_date`),
  KEY `release_until` (`release_until`),
  KEY `allow_member_read` (`allow_member_read`),
  KEY `allow_public_read` (`allow_public_read`),
  KEY `allow_troll_read` (`allow_troll_read`),
  KEY `allow_troll_upload` (`allow_troll_upload`),
  KEY `allow_troll_comments` (`allow_troll_comment`),
  KEY `allow_public_upload` (`allow_public_upload`),
  KEY `allow_public_comments` (`allow_public_comment`),
  KEY `gallery_active` (`gallery_active`),
  KEY `admin_notification` (`admin_notifications`),
  KEY `gallery_cgphoto_id` (`gallery_cgphoto_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_gallery_comments` (
  `cgcomment_id` int(12) NOT NULL AUTO_INCREMENT,
  `cgphoto_id` int(12) NOT NULL DEFAULT '0',
  `cgallery_id` int(12) NOT NULL DEFAULT '0',
  `community_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `comment_title` varchar(128) NOT NULL,
  `comment_description` text NOT NULL,
  `comment_active` int(1) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  `notify` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cgcomment_id`),
  KEY `cgallery_id` (`cgallery_id`,`community_id`,`proxy_id`,`comment_active`,`updated_date`,`updated_by`),
  KEY `cgphoto_id` (`cgphoto_id`),
  KEY `release_date` (`release_date`),
  FULLTEXT KEY `comment_title` (`comment_title`,`comment_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_gallery_photos` (
  `cgphoto_id` int(12) NOT NULL AUTO_INCREMENT,
  `cgallery_id` int(12) NOT NULL DEFAULT '0',
  `community_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `photo_mimetype` varchar(64) NOT NULL,
  `photo_filename` varchar(128) NOT NULL,
  `photo_filesize` int(32) NOT NULL DEFAULT '0',
  `photo_title` varchar(128) NOT NULL,
  `photo_description` text NOT NULL,
  `photo_active` int(1) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  `notify` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cgphoto_id`),
  KEY `cgallery_id` (`cgallery_id`,`community_id`,`proxy_id`,`photo_filesize`,`updated_date`,`updated_by`),
  KEY `photo_active` (`photo_active`),
  KEY `release_date` (`release_date`,`release_until`),
  FULLTEXT KEY `photo_title` (`photo_title`,`photo_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_history` (
  `chistory_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `cpage_id` int(12) DEFAULT '0',
  `record_id` int(12) NOT NULL DEFAULT '0',
  `record_parent` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `history_key` varchar(255) DEFAULT NULL,
  `history_message` text NOT NULL,
  `history_display` int(1) NOT NULL DEFAULT '0',
  `history_timestamp` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`chistory_id`),
  KEY `community_id` (`community_id`,`history_display`),
  KEY `history_timestamp` (`history_timestamp`),
  KEY `cpage_id` (`cpage_id`),
  KEY `record_id` (`record_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_mailing_lists` (
  `cmlist_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `list_name` varchar(64) NOT NULL,
  `list_type` enum('announcements','discussion','inactive') NOT NULL DEFAULT 'inactive',
  `last_checked` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmlist_id`),
  KEY `community_id` (`community_id`,`list_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_mailing_list_members` (
  `cmlmember_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL,
  `email` varchar(64) NOT NULL,
  `member_active` int(1) NOT NULL DEFAULT '0',
  `list_administrator` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmlmember_id`),
  UNIQUE KEY `member_id` (`community_id`,`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_members` (
  `cmember_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `member_active` int(1) NOT NULL DEFAULT '1',
  `member_joined` bigint(64) NOT NULL DEFAULT '0',
  `member_acl` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmember_id`),
  KEY `community_id` (`community_id`,`proxy_id`,`member_joined`,`member_acl`),
  KEY `member_active` (`member_active`),
  KEY `community_id_2` (`community_id`,`proxy_id`,`member_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_modules` (
  `cmodule_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `module_id` int(12) NOT NULL DEFAULT '0',
  `module_active` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmodule_id`),
  KEY `community_id` (`community_id`,`module_id`,`module_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_notifications` (
  `cnotification_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `release_time` bigint(64) NOT NULL DEFAULT '0',
  `community` varchar(128) NOT NULL,
  `type` varchar(64) NOT NULL,
  `subject` varchar(128) NOT NULL DEFAULT '',
  `record_id` int(12) NOT NULL DEFAULT '0',
  `author_id` int(12) NOT NULL DEFAULT '0',
  `body` text NOT NULL,
  `url` varchar(45) NOT NULL,
  PRIMARY KEY (`cnotification_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_notify_members` (
  `cnmember_id` int(12) NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `record_id` int(12) NOT NULL DEFAULT '0',
  `community_id` int(12) NOT NULL DEFAULT '0',
  `notify_type` varchar(32) NOT NULL DEFAULT 'announcement',
  `notify_active` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cnmember_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_pages` (
  `cpage_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `parent_id` int(12) NOT NULL DEFAULT '0',
  `page_order` int(3) NOT NULL DEFAULT '0',
  `page_type` varchar(16) NOT NULL DEFAULT 'default',
  `menu_title` varchar(48) NOT NULL,
  `page_title` text NOT NULL,
  `page_url` varchar(329) NOT NULL,
  `page_content` longtext NOT NULL,
  `page_active` int(1) NOT NULL DEFAULT '1',
  `page_visible` int(1) NOT NULL DEFAULT '1',
  `allow_member_view` int(1) NOT NULL DEFAULT '1',
  `allow_troll_view` int(1) NOT NULL DEFAULT '1',
  `allow_public_view` int(1) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cpage_id`),
  KEY `cpage_id` (`cpage_id`,`community_id`,`page_url`,`page_active`),
  KEY `community_id` (`community_id`,`parent_id`,`page_url`,`page_active`),
  KEY `page_order` (`page_order`),
  KEY `community_id_2` (`community_id`,`page_url`),
  KEY `community_id_3` (`community_id`,`page_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_page_options` (
  `cpoption_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL,
  `cpage_id` int(12) NOT NULL DEFAULT '0',
  `option_title` varchar(32) NOT NULL,
  `option_value` int(12) NOT NULL DEFAULT '1',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cpoption_id`,`community_id`,`cpage_id`),
  KEY `cpage_id` (`cpage_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_permissions` (
  `cpermission_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `module_id` int(12) NOT NULL DEFAULT '0',
  `action` varchar(64) NOT NULL,
  `level` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cpermission_id`),
  KEY `community_id` (`community_id`,`module_id`,`action`,`level`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_polls` (
  `cpolls_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `cpage_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `poll_title` varchar(64) NOT NULL,
  `poll_description` text NOT NULL,
  `poll_terminology` varchar(32) NOT NULL DEFAULT 'Poll',
  `poll_active` int(1) NOT NULL DEFAULT '1',
  `poll_order` int(6) NOT NULL DEFAULT '0',
  `poll_notifications` int(1) NOT NULL DEFAULT '0',
  `allow_multiple` int(1) NOT NULL DEFAULT '0',
  `number_of_votes` int(4) DEFAULT NULL,
  `allow_public_read` int(1) NOT NULL DEFAULT '0',
  `allow_public_vote` int(1) NOT NULL DEFAULT '0',
  `allow_public_results` int(1) NOT NULL DEFAULT '0',
  `allow_public_results_after` int(1) NOT NULL DEFAULT '0',
  `allow_troll_read` int(1) NOT NULL DEFAULT '0',
  `allow_troll_vote` int(1) NOT NULL DEFAULT '0',
  `allow_troll_results` int(1) NOT NULL DEFAULT '0',
  `allow_troll_results_after` int(1) NOT NULL DEFAULT '0',
  `allow_member_read` int(1) NOT NULL DEFAULT '1',
  `allow_member_vote` int(1) NOT NULL DEFAULT '1',
  `allow_member_results` int(1) NOT NULL DEFAULT '0',
  `allow_member_results_after` int(1) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cpolls_id`),
  KEY `community_id` (`community_id`),
  KEY `proxy_id` (`proxy_id`),
  KEY `poll_title` (`poll_title`),
  KEY `poll_notifications` (`poll_notifications`),
  KEY `release_date` (`release_date`),
  KEY `release_until` (`release_until`),
  KEY `allow_multiple` (`allow_multiple`),
  KEY `allow_member_read` (`allow_member_read`),
  KEY `allow_member_vote` (`allow_member_vote`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_polls_access` (
  `cpaccess_id` int(12) NOT NULL AUTO_INCREMENT,
  `cpolls_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cpaccess_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_polls_questions` (
  `cpquestion_id` int(12) NOT NULL AUTO_INCREMENT,
  `cpolls_id` int(12) NOT NULL DEFAULT '0',
  `community_id` int(12) NOT NULL DEFAULT '0',
  `cpage_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` int(12) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  `poll_question` text NOT NULL,
  `question_order` int(2) NOT NULL DEFAULT '0',
  `minimum_responses` int(2) NOT NULL DEFAULT '1',
  `maximum_responses` int(2) NOT NULL DEFAULT '1',
  `question_active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`cpquestion_id`),
  KEY `cpolls_id` (`cpolls_id`),
  KEY `community_id` (`community_id`),
  KEY `cpage_id` (`cpage_id`),
  FULLTEXT KEY `poll_question` (`poll_question`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_polls_responses` (
  `cpresponses_id` int(12) NOT NULL AUTO_INCREMENT,
  `cpquestion_id` int(12) NOT NULL,
  `cpolls_id` int(12) NOT NULL DEFAULT '0',
  `response` text NOT NULL,
  `response_index` int(5) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cpresponses_id`),
  KEY `cpolls_id` (`cpolls_id`),
  KEY `response_index` (`response_index`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_polls_results` (
  `cpresults_id` int(12) NOT NULL AUTO_INCREMENT,
  `cpresponses_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cpresults_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_shares` (
  `cshare_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `cpage_id` int(12) NOT NULL DEFAULT '0',
  `folder_title` varchar(64) NOT NULL,
  `folder_description` text NOT NULL,
  `folder_icon` int(3) NOT NULL DEFAULT '1',
  `folder_order` int(6) NOT NULL DEFAULT '0',
  `folder_active` int(1) NOT NULL DEFAULT '1',
  `admin_notifications` int(1) NOT NULL DEFAULT '0',
  `allow_public_read` int(1) NOT NULL DEFAULT '0',
  `allow_public_upload` int(1) NOT NULL DEFAULT '0',
  `allow_public_comment` int(1) NOT NULL DEFAULT '0',
  `allow_troll_read` int(1) NOT NULL DEFAULT '1',
  `allow_troll_upload` int(1) NOT NULL DEFAULT '0',
  `allow_troll_comment` int(1) NOT NULL DEFAULT '0',
  `allow_member_read` int(1) NOT NULL DEFAULT '1',
  `allow_member_upload` int(1) NOT NULL DEFAULT '1',
  `allow_member_comment` int(1) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cshare_id`),
  KEY `community_id` (`community_id`,`folder_order`,`allow_member_upload`,`allow_member_comment`),
  KEY `release_date` (`release_date`),
  KEY `release_until` (`release_until`),
  KEY `allow_member_read` (`allow_member_read`),
  KEY `allow_public_read` (`allow_public_read`),
  KEY `allow_troll_read` (`allow_troll_read`),
  KEY `allow_troll_upload` (`allow_troll_upload`),
  KEY `allow_troll_comments` (`allow_troll_comment`),
  KEY `allow_public_upload` (`allow_public_upload`),
  KEY `allow_public_comments` (`allow_public_comment`),
  KEY `folder_active` (`folder_active`),
  KEY `admin_notification` (`admin_notifications`),
  KEY `folder_icon` (`folder_icon`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_share_comments` (
  `cscomment_id` int(12) NOT NULL AUTO_INCREMENT,
  `csfile_id` int(12) NOT NULL DEFAULT '0',
  `cshare_id` int(12) NOT NULL DEFAULT '0',
  `community_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `comment_title` varchar(128) NOT NULL,
  `comment_description` text NOT NULL,
  `comment_active` int(1) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  `notify` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cscomment_id`),
  KEY `cshare_id` (`cshare_id`,`community_id`,`proxy_id`,`comment_active`,`updated_date`,`updated_by`),
  KEY `csfile_id` (`csfile_id`),
  KEY `release_date` (`release_date`),
  FULLTEXT KEY `comment_title` (`comment_title`,`comment_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_share_files` (
  `csfile_id` int(12) NOT NULL AUTO_INCREMENT,
  `cshare_id` int(12) NOT NULL DEFAULT '0',
  `community_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `file_title` varchar(128) NOT NULL,
  `file_description` text NOT NULL,
  `file_active` int(1) NOT NULL DEFAULT '1',
  `allow_member_revision` int(1) NOT NULL DEFAULT '0',
  `allow_troll_revision` int(1) NOT NULL DEFAULT '0',
  `access_method` int(1) NOT NULL DEFAULT '0',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  `notify` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`csfile_id`),
  KEY `cshare_id` (`cshare_id`,`community_id`,`proxy_id`,`updated_date`,`updated_by`),
  KEY `file_active` (`file_active`),
  KEY `release_date` (`release_date`,`release_until`),
  KEY `allow_member_edit` (`allow_member_revision`,`allow_troll_revision`),
  KEY `access_method` (`access_method`),
  FULLTEXT KEY `file_title` (`file_title`,`file_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_share_file_versions` (
  `csfversion_id` int(12) NOT NULL AUTO_INCREMENT,
  `csfile_id` int(12) NOT NULL DEFAULT '0',
  `cshare_id` int(12) NOT NULL DEFAULT '0',
  `community_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `file_version` int(5) NOT NULL DEFAULT '1',
  `file_mimetype` varchar(64) NOT NULL,
  `file_filename` varchar(128) NOT NULL,
  `file_filesize` int(32) NOT NULL DEFAULT '0',
  `file_active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`csfversion_id`),
  KEY `cshare_id` (`csfile_id`,`cshare_id`,`community_id`,`proxy_id`,`file_version`,`updated_date`,`updated_by`),
  KEY `file_active` (`file_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_templates` (
  `template_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `template_name` varchar(60) NOT NULL,
  `template_description` text,
  `organisation_id` int(12) unsigned DEFAULT NULL,
  `group` int(12) unsigned DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`template_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `community_templates` (`template_id`, `template_name`, `template_description`, `organisation_id`, `group`, `role`) VALUES
(1,'default','',NULL,NULL,NULL),
(2,'committee','',NULL,NULL,NULL),
(3,'virtualpatient','',NULL,NULL,NULL),
(4,'learningmodule','',NULL,NULL,NULL),
(5,'course','',NULL,NULL,NULL);

CREATE TABLE IF NOT EXISTS `courses` (
  `course_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) NOT NULL DEFAULT '0',
  `curriculum_type_id` int(12) unsigned NOT NULL DEFAULT '0',
  `parent_id` int(12) NOT NULL DEFAULT '0',
  `pcoord_id` int(12) unsigned NOT NULL DEFAULT '0',
  `evalrep_id` int(12) unsigned NOT NULL DEFAULT '0',
  `studrep_id` int(12) unsigned NOT NULL DEFAULT '0',
  `course_name` varchar(85) NOT NULL DEFAULT '',
  `course_code` varchar(16) NOT NULL DEFAULT '',
  `course_description` text,
  `course_objectives` text,
  `course_url` text,
  `course_message` text NOT NULL,
  `permission` ENUM('open','closed') NOT NULL DEFAULT 'closed',
  `sync_ldap` int(1) NOT NULL DEFAULT '0',
  `notifications` int(1) NOT NULL DEFAULT '1',
  `course_active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`course_id`),
  KEY `notifications` (`notifications`),
  KEY `pcoord_id` (`pcoord_id`),
  KEY `evalrep_id` (`evalrep_id`),
  KEY `studrep_id` (`studrep_id`),
  KEY `parent_id` (`parent_id`),
  KEY `curriculum_type_id` (`curriculum_type_id`),
  KEY `course_code` (`course_code`),
  KEY `course_active` (`course_active`),
  FULLTEXT KEY `course_description` (`course_description`),
  FULLTEXT KEY `course_objectives` (`course_objectives`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `course_audience` (
  `caudience_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `audience_type` enum('proxy_id','group_id') NOT NULL,
  `audience_value` int(11) NOT NULL,
  `cperiod_id` int(11) NOT NULL,
  `enroll_start` bigint(20) NOT NULL,
  `enroll_finish` bigint(20) NOT NULL,
  `audience_active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`caudience_id`),
  KEY `course_id` (`course_id`),
  KEY `audience_type` (`audience_type`),
  KEY `audience_value` (`audience_value`),
  KEY `audience_active` (`audience_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `course_contacts` (
  `contact_id` int(12) NOT NULL AUTO_INCREMENT,
  `course_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `contact_type` varchar(12) NOT NULL DEFAULT 'director',
  `contact_order` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`contact_id`),
  KEY `course_id` (`course_id`),
  KEY `proxy_id` (`proxy_id`),
  KEY `contact_type` (`contact_type`),
  KEY `contact_order` (`contact_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `course_files` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `course_id` int(12) NOT NULL DEFAULT '0',
  `required` int(1) NOT NULL DEFAULT '0',
  `timeframe` varchar(64) NOT NULL DEFAULT 'none',
  `file_category` varchar(32) NOT NULL DEFAULT 'other',
  `file_type` varchar(255) NOT NULL,
  `file_size` varchar(32) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_title` varchar(128) NOT NULL,
  `file_notes` longtext NOT NULL,
  `valid_from` bigint(64) NOT NULL DEFAULT '0',
  `valid_until` bigint(64) NOT NULL DEFAULT '0',
  `access_method` int(1) NOT NULL DEFAULT '0',
  `accesses` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  KEY `required` (`required`),
  KEY `valid_from` (`valid_from`),
  KEY `valid_until` (`valid_until`),
  KEY `access_method` (`access_method`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `course_groups` (
  `cgroup_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `group_name` VARCHAR(30) NOT NULL,
  `active` int(1) DEFAULT NULL,
  PRIMARY KEY (`cgroup_id`),
  KEY `course_id` (`course_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `course_group_audience` (
  `cgaudience_id` int(11) NOT NULL AUTO_INCREMENT,
  `cgroup_id` int(11) NOT NULL,
  `proxy_id` int(11) NOT NULL,
  `entrada_only` INT(1) DEFAULT 0,
  `start_date` BIGINT(64) NOT NULL,
  `finish_date` BIGINT(64) NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY (`cgaudience_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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


CREATE TABLE IF NOT EXISTS `course_links` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `course_id` int(12) NOT NULL DEFAULT '0',
  `required` int(1) NOT NULL DEFAULT '0',
  `proxify` int(1) NOT NULL DEFAULT '0',
  `timeframe` varchar(64) NOT NULL DEFAULT 'none',
  `link` text NOT NULL,
  `link_title` varchar(255) NOT NULL,
  `link_notes` text NOT NULL,
  `valid_from` bigint(64) NOT NULL DEFAULT '0',
  `valid_until` bigint(64) NOT NULL DEFAULT '0',
  `accesses` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  KEY `required` (`required`),
  KEY `valid_from` (`valid_from`),
  KEY `valid_until` (`valid_until`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `course_objectives` (
  `cobjective_id` int(12) NOT NULL AUTO_INCREMENT,
  `course_id` int(12) NOT NULL DEFAULT '0',
  `objective_id` int(12) NOT NULL DEFAULT '0',
  `importance` int(2) NOT NULL DEFAULT '1',
  `objective_type` enum('event','course') DEFAULT 'course',
  `objective_details` text,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`cobjective_id`),
  KEY `course_id` (`course_id`),
  KEY `objective_id` (`objective_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cron_community_notifications` (
  `ccnotification_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `cnotification_id` int(12) NOT NULL,
  `proxy_id` int(12) NOT NULL,
  PRIMARY KEY (`ccnotification_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `curriculum_lu_types` (
  `curriculum_type_id` int(12) unsigned NOT NULL auto_increment,
  `parent_id` int(12) unsigned NOT NULL default '0',
  `curriculum_type_name` varchar(60) NOT NULL,
  `curriculum_type_description` text,
  `curriculum_type_order` int(12) unsigned NOT NULL default '0',
  `curriculum_type_active` int(1) unsigned NOT NULL default '1',
  `curriculum_level_id` int(12) default NULL,
  `updated_date` bigint(64) unsigned NOT NULL,
  `updated_by` int(12) unsigned NOT NULL,
  PRIMARY KEY (`curriculum_type_id`),
  KEY `curriculum_type_order` (`curriculum_type_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `curriculum_lu_types` (`curriculum_type_id`, `parent_id`, `curriculum_type_name`, `curriculum_type_description`, `curriculum_type_order`, `curriculum_type_active`, `curriculum_level_id`, `updated_date`, `updated_by`) VALUES
(1, 0, 'Term 1', NULL, 0, 1, NULL, 1250538588, 1),
(2, 0, 'Term 2', NULL, 1, 1, NULL, 1250538588, 1),
(3, 0, 'Term 3', NULL, 2, 1, NULL, 1250538588, 1),
(4, 0, 'Term 4', NULL, 3, 1, NULL, 1250538588, 1),
(5, 0, 'Term 5', NULL, 4, 1, NULL, 1250538588, 1),
(6, 0, 'Term 6', NULL, 5, 1, NULL, 1250538588, 1),
(7, 0, 'Term 7', NULL, 6, 1, NULL, 1250538588, 1),
(8, 0, 'Term 8', NULL, 7, 1, NULL, 1250538588, 1);

CREATE TABLE IF NOT EXISTS `curriculum_periods`(
	`cperiod_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`curriculum_type_id` INT NOT NULL,
	`start_date` BIGINT(64) NOT NULL,
	`finish_date` BIGINT(64) NOT NULL,
	`active` INT(1) NOT NULL DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `curriculum_type_organisation` (
  `curriculum_type_id` int(11) NOT NULL,
  `organisation_id` int(11) NOT NULL,
  PRIMARY KEY (`curriculum_type_id`,`organisation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `curriculum_type_organisation` SELECT `curriculum_type_id`, 1 FROM `curriculum_lu_types`;

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
  `evaluation_mandatory` tinyint(1) NOT NULL DEFAULT '1',
  `allow_target_review` tinyint(1) NOT NULL DEFAULT '0',
  `allow_target_request` tinyint(1) NOT NULL DEFAULT '0',
  `allow_repeat_targets` tinyint(1) NOT NULL DEFAULT '0',
  `show_comments` tinyint(1) NOT NULL DEFAULT '0',
  `threshold_notifications_type` enum('reviewers','tutors','directors','pcoordinators','authors','disabled') NOT NULL DEFAULT 'disabled',
  `release_date` bigint(64) NOT NULL,
  `release_until` bigint(64) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` bigint(64) NOT NULL,
  PRIMARY KEY (`evaluation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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

CREATE TABLE `evaluations_lu_question_response_criteria` (
  `eqrcriteria_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `eqresponse_id` int(11) DEFAULT NULL,
  `criteria_text` text,
  PRIMARY KEY (`eqrcriteria_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluations_lu_questiontypes` (
  `questiontype_id` int(12) NOT NULL AUTO_INCREMENT,
  `questiontype_shortname` varchar(32) NOT NULL,
  `questiontype_title` varchar(64) NOT NULL,
  `questiontype_description` text NOT NULL,
  `questiontype_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`questiontype_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `evaluations_lu_questiontypes` (`questiontype_id`, `questiontype_shortname`, `questiontype_title`, `questiontype_description`, `questiontype_active`) VALUES
(1, 'matrix_single', 'Choice Matrix (single response)', 'The rating scale allows evaluators to rate each question based on the scale you provide (i.e. 1 = Not Demonstrated, 2 = Needs Improvement, 3 = Satisfactory, 4 = Above Average).', 1),
(2, 'descriptive_text', 'Descriptive Text', 'Allows you to add descriptive text information to your evaluation form. This could be instructions or other details relevant to the question or series of questions.', 1),
(3, 'rubric', 'Rubric', 'The rating scale allows evaluators to rate each question based on the scale you provide, while also providing a short description of the requirements to meet each level on the scale (i.e. Level 1 to 4 of \\\"Professionalism\\\" for an assignment are qualified with what traits the learner is expected to show to meet each level, and while the same scale is used for \\\"Collaborator\\\", the requirements at each level are defined differently).', 1),
(4, 'free_text', 'Free Text Comments', 'Allows the user to be asked for a simple free-text response. This can be used to get additional details about prior questions, or to simply ask for any comments from the evaluator regarding a specific topic.', 1);

CREATE TABLE `evaluations_lu_rubrics` (
  `erubric_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `rubric_title` varchar(32) DEFAULT NULL,
  `rubric_description` text,
  `efrubric_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`erubric_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluations_lu_targets` (
  `target_id` int(11) NOT NULL AUTO_INCREMENT,
  `target_shortname` varchar(32) NOT NULL,
  `target_title` varchar(64) NOT NULL,
  `target_description` text NOT NULL,
  `target_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`target_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `evaluations_lu_targets` (`target_id`, `target_shortname`, `target_title`, `target_description`, `target_active`) VALUES
(1, 'course', 'Course Evaluation', '', 1),
(2, 'teacher', 'Teacher Evaluation', '', 1),
(3, 'student', 'Student Assessment', '', 1),
(4, 'rotation_core', 'Clerkship Core Rotation Evaluation', '', 1),
(5, 'rotation_elective', 'Clerkship Elective Rotation Evaluation', '', 1),
(6, 'preceptor', 'Clerkship Preceptor Evaluation', '', 1),
(7, 'peer', 'Peer Assessment', '', 1),
(8, 'self', 'Self Assessment', '', 1),
(9, 'resident', 'Resident Evaluation', '', 1);

CREATE TABLE `evaluations_related_questions` (
  `erubric_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `related_equestion_id` int(11) unsigned NOT NULL,
  `equestion_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`erubric_id`)
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

CREATE TABLE IF NOT EXISTS `evaluation_evaluators` (
  `eevaluator_id` int(12) NOT NULL AUTO_INCREMENT,
  `evaluation_id` int(12) NOT NULL,
  `evaluator_type` enum('proxy_id','grad_year','cohort','organisation_id', 'cgroup_id') NOT NULL DEFAULT 'proxy_id',
  `evaluator_value` int(12) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`eevaluator_id`)
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
  `equestion_id` int(12) NOT NULL,
  `question_order` tinyint(3) NOT NULL DEFAULT '0',
  `allow_comments` tinyint(1) NOT NULL DEFAULT '1',
  `send_threshold_notifications` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`efquestion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_form_question_objectives` (
  `efqobjective_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `efquestion_id` int(12) NOT NULL,
  `objective_id` int(12) NOT NULL,
  `updated_date` bigint(64) DEFAULT NULL,
  `updated_by` int(12) DEFAULT NULL,
  PRIMARY KEY (`efqobjective_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_progress` (
  `eprogress_id` int(12) NOT NULL AUTO_INCREMENT,
  `evaluation_id` int(12) NOT NULL,
  `etarget_id` int(12) NOT NULL,
  `target_record_id` int(11) DEFAULT NULL,
  `proxy_id` int(12) NOT NULL,
  `progress_value` enum('inprogress','complete','cancelled') NOT NULL DEFAULT 'inprogress',
  `updated_date` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`eprogress_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_progress_clerkship_events` (
  `epcevent_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `eprogress_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `preceptor_proxy_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`epcevent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_responses` (
  `eresponse_id` int(12) NOT NULL AUTO_INCREMENT,
  `eprogress_id` int(12) NOT NULL,
  `eform_id` int(12) NOT NULL,
  `proxy_id` int(12) NOT NULL,
  `efquestion_id` int(12) NOT NULL,
  `eqresponse_id` int(12) NOT NULL,
  `comments` text,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`eresponse_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `evaluation_rubric_questions` (
  `efrquestion_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `erubric_id` int(11) DEFAULT NULL,
  `equestion_id` int(11) DEFAULT NULL,
  `question_order` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`efrquestion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_targets` (
  `etarget_id` int(12) NOT NULL AUTO_INCREMENT,
  `evaluation_id` int(12) NOT NULL,
  `target_id` int(11) NOT NULL,
  `target_value` int(12) NOT NULL,
  `target_type` varchar(24) NOT NULL DEFAULT 'course_id',
  `target_active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`etarget_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `events` (
  `event_id` int(12) NOT NULL AUTO_INCREMENT,
  `parent_id` int(12) DEFAULT NULL,
  `event_children` int(12) DEFAULT NULL,
  `recurring_id` int(12) DEFAULT '0',
  `region_id` int(12) DEFAULT '0',
  `course_id` int(12) NOT NULL DEFAULT '0',
  `event_phase` varchar(12) DEFAULT NULL,
  `event_title` varchar(255) NOT NULL,
  `event_description` text,
  `include_parent_description` tinyint(1) NOT NULL DEFAULT '1',
  `event_goals` text,
  `event_objectives` text,
  `event_message` text,
  `include_parent_message` tinyint(1) NOT NULL DEFAULT '1',
  `event_location` varchar(64) DEFAULT NULL,
  `event_start` bigint(64) NOT NULL,
  `event_finish` bigint(64) NOT NULL,
  `event_duration` int(64) NOT NULL,
  `release_date` bigint(64) NOT NULL,
  `release_until` bigint(64) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  `draft_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`event_id`),
  KEY `course_id` (`course_id`),
  KEY `region_id` (`region_id`),
  KEY `recurring_id` (`recurring_id`),
  KEY `release_date` (`release_date`,`release_until`),
  KEY `event_start` (`event_start`,`event_duration`),
  KEY `event_start_2` (`event_start`,`event_finish`),
  KEY `event_phase` (`event_phase`),
  FULLTEXT KEY `event_title` (`event_title`,`event_description`,`event_goals`,`event_objectives`,`event_message`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `event_attendance` (
  `eattendance_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `proxy_id` int(11) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`eattendance_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `events_lu_topics` (
  `topic_id` int(12) NOT NULL AUTO_INCREMENT,
  `topic_name` varchar(60) NOT NULL,
  `topic_description` text,
  `topic_type` enum('ed10','ed11','other') NOT NULL DEFAULT 'other',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `events_lu_topics` (`topic_id`, `topic_name`, `topic_description`, `topic_type`, `updated_date`, `updated_by`) VALUES
(1, 'Biostatistics', 'Biostatistics', 'ed10', 1215615910, 1),
(2, 'Communication Skills', 'Communication Skills', 'ed10', 1215615910, 1),
(3, 'Community Health', 'Community Health', 'ed10', 1215615910, 1),
(4, 'End-of-Life Care', 'End-of-Life Care', 'ed10', 1215615910, 1),
(5, 'Epidemiology', 'Epidemiology', 'ed10', 1215615910, 1),
(6, 'Evidence-Based Medicine', 'Evidence-Based Medicine', 'ed10', 1215615910, 1),
(7, 'Family Violence/Abuse', 'Family Violence/Abuse', 'ed10', 1215615910, 1),
(8, 'Medical Genetics', 'Medical Genetics', 'ed10', 1215615910, 1),
(9, 'Health Care Financing', 'Health Care Financing', 'ed10', 1215615910, 1),
(10, 'Health Care Systems', 'Health Care Systems', 'ed10', 1215615910, 1),
(11, 'Health Care Quality Review', 'Health Care Quality Review', 'ed10', 1215615910, 1),
(12, 'Home Health Care', 'Home Health Care', 'ed10', 1215615910, 1),
(13, 'Human Development/Life Cycle', 'Human Development/Life Cycle', 'ed10', 1215615910, 1),
(14, 'Human Sexuality', 'Human Sexuality', 'ed10', 1215615910, 1),
(15, 'Medical Ethics', 'Medical Ethics', 'ed10', 1215615910, 1),
(16, 'Medical Humanities', 'Medical Humanities', 'ed10', 1215615910, 1),
(17, 'Medical Informatics', 'Medical Informatics', 'ed10', 1215615910, 1),
(18, 'Medical Jurisprudence', 'Medical Jurisprudence', 'ed10', 1215615910, 1),
(19, 'Multicultural Medicine', 'Multicultural Medicine', 'ed10', 1215615910, 1),
(20, 'Nutrition', 'Nutrition', 'ed10', 1215615910, 1),
(21, 'Occupational Health/Medicine', 'Occupational Health/Medicine', 'ed10', 1215615910, 1),
(22, 'Pain Management', 'Pain Management', 'ed10', 1215615910, 1),
(23, 'Palliative Care', 'Palliative Care', 'ed10', 1215615910, 1),
(24, 'Patient Health Education', 'Patient Health Education', 'ed10', 1215615910, 1),
(25, 'Population-Based Medicine', 'Population-Based Medicine', 'ed10', 1215615910, 1),
(26, 'Practice Management', 'Practice Management', 'ed10', 1215615910, 1),
(27, 'Preventive Medicine', 'Preventive Medicine', 'ed10', 1215615910, 1),
(28, 'Rehabilitation/Care of the Disabled', 'Rehabilitation/Care of the Disabled', 'ed10', 1215615910, 1),
(29, 'Research Methods', 'Research Methods', 'ed10', 1215615910, 1),
(30, 'Substance Abuse', 'Substance Abuse', 'ed10', 1215615910, 1),
(31, 'Womens Health', 'Womens Health', 'ed10', 1215615910, 1),
(32, 'Anatomy', 'Anatomy', 'ed11', 1215615910, 1),
(33, 'Biochemistry', 'Biochemistry', 'ed11', 1215615910, 1),
(34, 'Genetics', 'Genetics', 'ed11', 1215615910, 1),
(35, 'Physiology', 'Physiology', 'ed11', 1215615910, 1),
(36, 'Microbiology and Immunology', 'Microbiology and Immunology', 'ed11', 1215615910, 1),
(37, 'Pathology', 'Pathology', 'ed11', 1215615910, 1),
(38, 'Pharmacology Therapeutics', 'Pharmacology Therapeutics', 'ed11', 1215615910, 1),
(39, 'Preventive Medicine', 'Preventive Medicine', 'ed11', 1215615910, 1);

CREATE TABLE IF NOT EXISTS `events_lu_eventtypes` (
  `eventtype_id` int(12) NOT NULL AUTO_INCREMENT,
  `eventtype_title` varchar(32) NOT NULL,
  `eventtype_description` text NOT NULL,
  `eventtype_active` int(1) NOT NULL DEFAULT '1',
  `eventtype_order` int(6) NOT NULL,
  `eventtype_default_enrollment` varchar(50) DEFAULT NULL,
  `eventtype_report_calculation` varchar(100) DEFAULT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`eventtype_id`),
  KEY `eventtype_order` (`eventtype_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `events_lu_eventtypes` (`eventtype_id`, `eventtype_title`, `eventtype_description`, `eventtype_active`, `eventtype_order`, `eventtype_default_enrollment`, `eventtype_report_calculation`, `updated_date`, `updated_by`) VALUES
(1, 'Lecture', 'Faculty member speaks to a whole group of students for the session. Ideally, the lecture is interactive, with brief student activities to apply learning within the talk or presentation. The focus, however, is on the faculty member speaking or presenting to a group of students.', 1, 0, NULL, NULL, 1250877835, 1),
(2, 'Lab', 'In this session, practical learning, activity and demonstration take place, usually with specialized equipment, materials or methods and related to a class, or unit of teaching.', 1, 1, NULL, NULL, 1250877835, 1),
(3, 'Small Group', 'In the session, students in small groups work on specific questions, problems, or tasks related to a topic or a case, using discussion and investigation. Faculty member facilitates. May occur in:\r\n<ul>\r\n<li><strong>Expanded Clinical Skills:</strong> demonstrations and practice of clinical approaches and assessments occur with students in small groups of 25 or fewer.</li>\r\n<li><strong>Team Based Learning Method:</strong> students are in pre-selected groups for the term to work on directed activities, often case-based. One-two faculty facilitate with all 100 students in small teams.</li>\r\n<li><strong>Peer Instruction:</strong> students work in partners on specific application activities throughout the session.</li>\r\n<li><strong>Seminars:</strong> Students are in small groups each with a faculty tutor or mentor to facilitate or coach each small group. Students are active in these groups, either sharing new information, working on tasks, cases, or problems. etc. This may include Problem Based Learning as a strategy where students research and explore aspects to solve issues raised by the case with faculty facilitating. Tutorials may also be incorporated here.</li>\r\n<li><strong>Clinical Skills:</strong> Students in the Clinical and Communication Skills courses work in small groups on specific tasks that allow application of clinical skills.</li>\r\n</ul>', 1, 2, NULL, NULL, 1219434863, 1),
(4, 'Patient Contact Session', 'The focus of the session is on the patient(s) who will be present to answer students'' and/or professor''s questions and/or to offer a narrative about their life with a condition, or as a member of a specific population. Medical Science Rounds are one example.', 1, 4, NULL, NULL, 1219434863, 1),
(5, 'Symposium / Student Presentation', 'For one or more hours, a variety of speakers, including students, present on topics to teach about current issues, research, etc.', 1, 6, NULL, NULL, 1219434863, 1),
(6, 'Directed Independent Learning', 'Students work independently (in groups or on their own) outside of class sessions on specific tasks to acquire knowledge, and develop enquiry and critical evaluation skills, with time allocated into the timetable. Directed Independent Student Learning may include learning through interactive online modules, online quizzes, working on larger independent projects (such as Community Based Projects or Critical Enquiry), or completing reflective, research or other types of papers and reports. While much student independent learning is done on the students own time, for homework, in this case, directed student time is built into the timetable as a specific session and linked directly to other learning in the course.', 1, 3, NULL, NULL, 1219434863, 1),
(7, 'Review / Feedback Session', 'In this session faculty help students to prepare for future learning and assessment through de-briefing about previous learning in a quiz or assignment, through reviewing a week or more of learning, or through reviewing at the end of a course to prepare for summative examination.', 1, 5, NULL, NULL, 1219434863, 1),
(8, 'Examination', 'Scheduled course examination time, including mid-term as well as final examinations. <strong>Please Note:</strong> These will be identified only by the Curricular Coordinators in the timetable.', 1, 7, NULL, NULL, 1219434863, 1),
(9, 'Clerkship Seminars', 'Case-based, small-group sessions emphasizing more advanced and integrative topics. Students draw upon their clerkship experience with patients and healthcare teams to participate and interact with the faculty whose role is to facilitate the discussion.', 1, 8, NULL, NULL, 1250878869, 1),
(10, 'Other', 'These are sessions that are not a part of the UGME curriculum but are recorded in MEdTech Central. Examples may be: Course Evaluation sessions, MD Management. NOTE: these will be identified only by the Curricular Coordinators in the timetable.', 1, 9, NULL, NULL, 1250878869, 1);

CREATE TABLE IF NOT EXISTS `events_lu_objectives` (
  `objective_id` int(12) NOT NULL AUTO_INCREMENT,
  `objective_name` varchar(60) NOT NULL,
  `objective_description` text,
  `objective_order` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`objective_id`),
  KEY `objective_order` (`objective_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `events_lu_resources` (
  `resource_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `resource` varchar(250) NOT NULL DEFAULT '',
  `description` text,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `events_recurring` (
  `recurring_id` int(12) NOT NULL AUTO_INCREMENT,
  `recurring_date` bigint(64) NOT NULL,
  `recurring_until` bigint(64) NOT NULL,
  `recurring_type` enum('daily','weekly','monthly','yearly') NOT NULL,
  `recurring_frequency` int(12) NOT NULL,
  `recurring_number` int(12) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`recurring_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `event_audience` (
  `eaudience_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `audience_type` enum('proxy_id','grad_year','cohort','organisation_id','group_id','course_id') NOT NULL,
  `audience_value` varchar(16) NOT NULL,
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`eaudience_id`),
  KEY `event_id` (`event_id`),
  KEY `target_value` (`audience_value`),
  KEY `target_type` (`audience_type`),
  KEY `event_id_2` (`event_id`,`audience_type`,`audience_value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `event_contacts` (
  `econtact_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `contact_role` ENUM('teacher','tutor','ta','auditor') NOT NULL,
  `contact_order` int(6) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`econtact_id`),
  UNIQUE KEY `event_id_2` (`event_id`,`proxy_id`),
  KEY `contact_order` (`contact_order`),
  KEY `event_id` (`event_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `event_discussions` (
  `ediscussion_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `parent_id` int(12) NOT NULL DEFAULT '0',
  `discussion_title` varchar(128) NOT NULL,
  `discussion_comment` text NOT NULL,
  `discussion_active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ediscussion_id`),
  KEY `event_id` (`event_id`),
  KEY `proxy_id` (`proxy_id`),
  KEY `parent_id` (`parent_id`),
  FULLTEXT KEY `discussion_title` (`discussion_title`,`discussion_comment`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `event_resources` (
  `event_resources_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fk_resource_id` int(11) NOT NULL,
  `fk_event_id` int(11) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`event_resources_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `event_topics` (
  `etopic_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `topic_id` tinyint(1) DEFAULT '0',
  `topic_coverage`  enum('major','minor') NOT NULL,
  `topic_time` varchar(25) DEFAULT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`etopic_id`),
  KEY `event_id` (`event_id`),
  KEY `topic_id` (`topic_id`),
  KEY `topic_coverage` (`topic_coverage`),
  KEY `topic_time` (`topic_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `event_eventtypes` (
  `eeventtype_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL,
  `eventtype_id` int(12) NOT NULL,
  `duration` int(12) NOT NULL,
  PRIMARY KEY (`eeventtype_id`),
  KEY `event_id` (`event_id`),
  KEY `eventtype_id` (`eventtype_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `event_files` (
  `efile_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `required` int(1) NOT NULL DEFAULT '0',
  `timeframe` varchar(64) NOT NULL DEFAULT 'none',
  `file_category` varchar(32) NOT NULL DEFAULT 'other',
  `file_type` varchar(255) NOT NULL,
  `file_size` varchar(32) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_title` varchar(128) NOT NULL,
  `file_notes` longtext NOT NULL,
  `access_method` int(1) NOT NULL DEFAULT '0',
  `accesses` int(12) NOT NULL DEFAULT '0',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`efile_id`),
  KEY `required` (`required`),
  KEY `access_method` (`access_method`),
  KEY `event_id` (`event_id`),
  KEY `release_date` (`release_date`,`release_until`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `event_history` (
  `ehistory_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `history_message` text NOT NULL,
  `history_display` int(1) NOT NULL DEFAULT '0',
  `history_timestamp` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ehistory_id`),
  KEY `history_timestamp` (`history_timestamp`),
  KEY `event_id` (`event_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `event_links` (
  `elink_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `required` int(1) NOT NULL DEFAULT '0',
  `timeframe` varchar(64) NOT NULL DEFAULT 'none',
  `proxify` int(1) NOT NULL DEFAULT '0',
  `link` text NOT NULL,
  `link_title` varchar(255) NOT NULL,
  `link_notes` text NOT NULL,
  `accesses` int(12) NOT NULL DEFAULT '0',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`elink_id`),
  KEY `lecture_id` (`event_id`),
  KEY `required` (`required`),
  KEY `release_date` (`release_date`,`release_until`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `event_objectives` (
  `eobjective_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `objective_id` int(12) NOT NULL DEFAULT '0',
  `objective_details` text,
  `objective_type` enum('event','course') NOT NULL DEFAULT 'event',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`eobjective_id`),
  KEY `event_id` (`event_id`),
  KEY `objective_id` (`objective_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `attached_quizzes` (
  `aquiz_id` int(12) NOT NULL AUTO_INCREMENT,
  `content_type` enum('event','community_page', 'assessment') NOT NULL DEFAULT 'event',
  `content_id` int(12) NOT NULL DEFAULT '0',
  `required` int(1) NOT NULL DEFAULT '0',
  `require_attendance` int(1) NOT NULL DEFAULT '0',
  `random_order` int(1) NOT NULL DEFAULT '0',
  `timeframe` varchar(64) NOT NULL,
  `quiz_id` int(12) NOT NULL DEFAULT '0',
  `quiz_title` varchar(128) NOT NULL,
  `quiz_notes` longtext NOT NULL,
  `quiztype_id` int(12) NOT NULL DEFAULT '0',
  `quiz_timeout` int(4) NOT NULL DEFAULT '0',
  `quiz_attempts` int(3) NOT NULL DEFAULT '0',
  `accesses` int(12) NOT NULL DEFAULT '0',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`aquiz_id`),
  KEY `content_id` (`content_id`),
  KEY `required` (`required`),
  KEY `timeframe` (`timeframe`),
  KEY `quiztype_id` (`quiztype_id`),
  KEY `quiz_id` (`quiz_id`),
  KEY `accesses` (`accesses`),
  KEY `release_date` (`release_date`,`release_until`),
  KEY `quiz_timeout` (`quiz_timeout`),
  KEY `quiz_attempts` (`quiz_attempts`),
  KEY `content_id_2` (`content_id`,`release_date`,`release_until`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `quiz_progress` (
  `qprogress_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `aquiz_id` int(12) unsigned NOT NULL,
  `content_type` enum('event','community_page') DEFAULT 'event',
  `content_id` int(12) unsigned NOT NULL,
  `quiz_id` int(12) unsigned NOT NULL,
  `proxy_id` int(12) unsigned NOT NULL,
  `progress_value` varchar(16) NOT NULL,
  `quiz_score` int(12) NOT NULL,
  `quiz_value` int(12) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) unsigned NOT NULL,
  PRIMARY KEY (`qprogress_id`),
  KEY `content_id` (`aquiz_id`,`content_id`,`proxy_id`),
  KEY `quiz_id` (`quiz_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `quiz_progress_responses` (
  `qpresponse_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `qprogress_id` int(12) unsigned NOT NULL,
  `aquiz_id` int(12) unsigned NOT NULL,
  `content_type` enum('event','community_page') NOT NULL DEFAULT 'event',
  `content_id` int(12) unsigned NOT NULL,
  `quiz_id` int(12) unsigned NOT NULL,
  `proxy_id` int(12) unsigned NOT NULL,
  `qquestion_id` int(12) unsigned NOT NULL,
  `qqresponse_id` int(12) unsigned NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) unsigned NOT NULL,
  PRIMARY KEY (`qpresponse_id`),
  KEY `qprogress_id` (`qprogress_id`,`aquiz_id`,`content_id`,`quiz_id`,`proxy_id`,`qquestion_id`,`qqresponse_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `event_related` (
  `erelated_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `related_type` enum('event_id') NOT NULL DEFAULT 'event_id',
  `related_value` varchar(16) NOT NULL,
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`erelated_id`),
  KEY `event_id` (`event_id`),
  KEY `related_type` (`related_type`),
  KEY `related_value` (`related_value`),
  KEY `event_id_2` (`event_id`,`related_type`,`related_value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `filetypes` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `ext` varchar(8) NOT NULL,
  `mime` varchar(64) NOT NULL,
  `english` varchar(64) NOT NULL,
  `image` blob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ext` (`ext`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `filetypes` (`id`, `ext`, `mime`, `english`, `image`) VALUES
(1, 'pdf', 'application/pdf', 'PDF Document', 0x47494638396110001000e60000660000c0c0c58e8e92828282eb060a666666e07e80d58e93dededfff6666bf4446d7c9ceff4b4eb4b4b4e77171a10406c7adb289898befeff0d5d5d6d80406eb9e9fd28c8ff4292bfe7679a3a3a7fda9a9990000ccccccc28386fec2c3e6e6e8cfa8adeeb0b09999998c0002f7f7f7fa7274a51819ff2125e9adafe28282edc1c2eca7a9707074e9d4d6ff7e80eaa7a8bcbcbfea9394e8c6c8c2c1c7a50e0ffdbcbee1e0e3e9e9eceb8787f40e11ababaccfcfd4e4060aff2b2effffff9c9ca1ff4f51ff9999e98d8eb6b6bad6d6dce67778ffccccebb5b59f0607ff8182dfc3c6eab5b6e9cccd86868bece6e7e3e0e2c4c4c5d79396ff7072ff7a7ca40608bdc5c5e6dee6ff292ca5a5ade78787ebc2c4e6d6deff0f12ff333300000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000021f9040514003e002c00000000100010000007d1803e823e0d038687880d833e4424123750433f933f11438b135a2d1f1105029d052c8a833b28595b2219053f03194d97a43732421308501f300b10338b1c1f372b1547472f450620508b3bbf37381231474c4a3bc9831c361f1221152a121f44d4bd0836374e0e373708131c018b01b53636291f36441c33300a26fb343454541b460058176048830457929c703105480902144648fad10041822e498260e08241c3051e1b74f820b1c307060609a40049d2c305831c48b09014e4a1668d2807721eb0d041c78445a43870d841b4d6a240003b),
(2, 'gif', 'image/gif', 'GIF Image', 0x47494638396110001000e600000e2345cbd6dcb87f659999998282829b705df0f0f0b1b8dc8098cfb9b9bf4f484c5d85b7c3b9ad4e71c11d325f94b7fc8c8b8b435789b5a48affffff423637666b7a909ab3dededecccccc7a9cf26f8edca48868706663b5b5b5b19c83aeaeaef7f7f7779ce863749caebee1c0c2c4291d207c7678c6c7d477829da8a8a8b5adad8aacf7e8e8e8afa28c334268a0a5b1bdbdbdc5c9e59999cc644e506073acd6d6d6c1bbb4cfbda799bbf6a5a5adb8bfc94c3e3cdbdde9c5c5c5bdbdc57d7d7dc5d1e3a7a1b400000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000021f90405140013002c00000000100010000007ad8013131d048586871d828235822c1f189082208c8a9403171d293f1720938a13943d293d1d10189d948b9d061d2c9dafa9a09d303c1d3006b806b135201735070a101f3d2c2c189f35ac0b15251c26272991953d230d2e053b15323109898a1824192b33022816001124309f904038144121340e2f1f09ec352d12010f191a22033e5c60878181870d3774209800e2d8a71e357a48ec61e343c34f8220d6a80109c687588a2e881c596320c640003b),
(3, 'jpg', 'image/jpeg', 'JPEG Image', 0x47494638396110001000d500000e2345ccccccaeaeae8c8c8c435789dedede7066636f8edcb8bfc9f7f7f7828282aebee1334268909ab3b19c839b705d8aacf763749cc1bcb41d325ff0f0f0c5d1e34f484c5d85b74236379999994e71c14c3e3ca48868779ce8c3b9adffffff666b7a291d20b5b5b5c6c7d49999ccd6d6d677829d8098cf7c7678c5c5c5b87f6599bbf6e7e7e7a0a5b1a3a3a3644e50afa28c6073acc5c9e594b7fccbd6dccfbda77a9cf2c0c1c5b1b8dcdbdde9bdbdbdb5b5bdb5adada7a1b4b5a48ac0c2c421f9040514001f002c00000000100010000006a3c0cf47a4281a8f22a1b044f9293f8968a2f4a46606898c2ee5ca4cabd981382508b0becad23435f8e928ba00859a8eea72a237654f5f260a2538160302292c2c01551422172021062823658969290b1a0c0f1b2024323a494a013f36102f2a260d0004373a4f0101152b183d1d31132d02a0420125303e3433360711190205ae011e0e1c35082750944a292529d529120209d1d225ddaf3a027d4f05e4e525c74f1f41003b),
(4, 'zip', 'application/x-zip-compressed', 'Zip File', 0x47494638396110001000c400001c1c1ce6e6e6ccccccb5b6b58d8d8d7b7b7b666666f7f7f73f3f3fd6d6d6adadadffffffefefef5d5d5dc5c5c5dddddd999999333333757575bdbdbd85858599999900000000000000000000000000000000000000000000000000000000000021f9040514000b002c0000000010001000000583e0b20853699e8e2826abe24c8fcacae2434c94f41cc7bc2e87470142904c78be058bc76c2613ccc4c4c188aa943cc6d111600aaed003c3711074198caf4a901510b8e8805a2440a713e6803eb5b6530e0d7a0f090357750c011405080f130a8f867a898b09109601575c7a8a080203010c570b9a89040d8ea1a202ab020611009fa221003b),
(5, 'exe', 'application/octet-stream', 'Windows Executable', 0x47494638396110001000b30000999999efefefdededeccccccb5b5b5fffffff7f7f7e6e6e6d5d5d5bdbdbd99999900000000000000000000000000000021f90405140005002c0000000010001000000453b09404aabd494aa1bbe69fa71906b879646a1660ea1a4827bc6ffcd169606f6fe007871d8bf4fb1d0e830ea2681420929a2533f8cc4481c7e3a0425821b24701a100f37cb34e42d91c760e08420d58403f8822003b),
(6, 'html', 'text/html', 'HTML Document', 0x47494638396110001000e60000052e49e6e6e6bbc3ad7d7d7d80bf35669933d6d6d6ffffff45834280a256cccccca2a2a2bdbdbdbdee744882578fe32eefefef21564e6fbb3a9999998dde2c9999991f4d617676768db756dededeb5b5b571a82e9ee438f7f7f797cf4039724a7ca08bc4c4c4184d54d3dbca87b16a29614484d6317bb834518845ababab5ea338abe74c5b886194bd5907385888ad5a225a5725576cb5c5ad99e333c2cbb588c739487f3991e12f7db83dc6cebe64aa3874ab317fbe35bfc8b25f9c3c82a55894e6318eb8588cad5a00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000021f904041400ff002c00000000100010000007a98007070c138586870c828219821d190a0a191d93068a078c10022d1e1e2d02101d958a9234381f252a37353da196191041083036003a0f1810a28b10043e310e22110f3c01ba9710271c24202c2e2627c5ae1d2f142b0d0d16122f012196061d233b14333328053906898a061010233f1b1b09e82129deedf8b9290bfcf701ffff180cb830c1980180011ed983a0c0d28183ff1e3158d0ca2144892952185b074941888f011c1e0804003b),
(7, 'mpg', 'video/mpeg', 'MPEG Movie', 0x47494638396110001000d50000737373c7d3dcaeb7c13e86c4e6e6e69999995792c5c1ccd4f7f7f74a99d899b9d0778ea7d8dadc7494b1298be0b5c2cbadadad8585854dadedbebebe85a7c0ffffff8c8c8c3292e3aec5d5d6d6d6a4a4a43d93d8ccccccbec6cdc3c3c34295dea7bfd24b9be472a0c22c94ed579bcee0e2e4469ddf8cadc9d1d4d8b6b6b6c5c9cd3e8bcf4593cba9bdcdd2d7dbdededeeaeaeb4a9cdeb5c5d64aadf7a9c6dcbdc9d36699ccd0d6db00000000000000000000000000000000000000000000000021f90405140015002c00000000100010000006a1c04a4513291a8f1aa19091617038150e24038165941506240228400a298be775555221dd8845c32e95851902624e9fbbb199179d202fc198782a08253617172426341e28782508061b201809210c4e7819280e357c2e120f4f781e1d232807142f262d29504a280c04262204010d1f2a2a13584f3018332c032b0b101a8c4a1c137c370a27021508c6c7710425254ed058424fd52f4c136f582fe2e24c2fd91541003b),
(8, 'pps', 'application/vnd.ms-powerpoint', 'Microsoft PowerPoint', 0x47494638396110001000d50000c35d0478afe084a4cc9fa1a5838484f4f3efbf8b5dde8a8ce8ebeed4c9c0f3b076e4e2e0e5a971ccccccdededed2d590ff7a04bfbfbfd5d5d5fca151adb2b5d5c0adb5bdbda2ddf5ff9933fffffff67908bb7f92f7d1a9e6e0c9fe860ef1bf92ddcfc3f5b67ddb95a4c6c4c1dce889f6f6f6cc8a52b9c3cfffcc99dccdbff2b27affb655edebeaff7c08e7e7e7a7aaaeffb66aebe3bfff8c10efefefb5b5b5ff8207c5bdbde1d3c5f9be7eecae76f7f7efd7cbc0a6e1fcc7c5c300000000000021f90405140019002c0000000010001000000699c08c70482c661c92a472996c3490c4128b55721493999236237115ac5a2d56db884446e888233c2ecd662781a05178cf4ac9f08c15e05d366e335e0d1281770f240722192c2e0b2c84522c331905311d25192e4a910808161414032fa1140404143309381f0a21af0c392a39060b8f1915181c13102dbebe1a000e2c42152b2830351ecbcb1a26944233292937d520d3293b0546dcddde4641003b),
(9, 'ppt', 'application/vnd.ms-powerpoint', 'Microsoft PowerPoint', 0x47494638396110001000e60000c35d048ebce294aac5adadad858585ebe9e6e4e1dfbaddf3be8a5cd0d4d8efaf77b1cdd6dfcebda1bddeff7a04eaad75ffffffdededefca151a2b5cbf2bf92a1d6f5ff9933d5c0adf67908b7d5ddf7f7f7c9f5ffcccccc8dacd6fe860ef9be7eb3c5cef7d1a9e6e6e6d6d8dfd7c8bba0bddf98bad2d6d6d6ced6deb5cedea4c2d491bfe4efefefdee0e3efb078ffb66ae9eaebcc8a52d2f1fcffcc99ffb655adbfd0ff7c08ff8c1094b5dd9cb4c9e4e3e1efb57bded6d6ff8207efb573dee6e6bddef7bfccd394add6bbc3cf9cbdd795c6e8e9ebefaac0d9ffffff00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000021f90405140048002c00000000100010000007aa804882838485481109898a091c8d8e1c11271093101a1197981a1a1027929a1a2c8b892c2c1a9d95229d20290b193241a41a89953a09233944262a0735b1899a11071b1d2b0102471330301a1c27a011451522d3053ad63a301c09cf1d0dde384322919dda1a462d22ca3022e81c0404032c241f140a2ef7f80f0806063048171642487060a360410c0022f8fb4763c68b1e1e2246c41083c520790c326624c19184454320438a2c1408003b),
(10, 'png', 'image/png', 'PNG Image', 0x47494638396110001000e600000e2345ccccccaeaeaea48868435789dedede6666666f8edcb9b9bf848484f7f7f78aacf7334268909ab39b705da2a2a263749cb8bfc91d325fefefefc5d1e38c8b8baebee14f484c5d85b7c3b9ad4236374e71c14c3e3c779ce8b5b5b5666b7aafa28cffffff291d20c6c7d49999ccd6d6d6999999b19c8377829de8e9e98098cf7c7678b87f65c0c2c499bbf6a0a5b1644e506073acc5c9e5c1bbb4bdbdbd70666394b7fccbd6dccfbda77a9cf2b1b8dcdbdde9b5adadc5c5c4a5a5adbdbdc5b5a48aa7a1b400000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000021f90405140021002c00000000100010000007ad8021211e098586871e828225210a8c253d131e3d8e8a218c2915010935022508958a250a99020908250f01a18b0a0a9229aeb28ca2ae343b1e3413bb13b4ad05253a1715023d292901962592181f22352b233e01c9a23d161b0c0e1c1f243208898a012d390b302c280d00042d3496d4142e1a411d31122f0208ef2520403736390e403021a0c0bb00194e0cc0114145a36a8a7a40ea4171860005102396d8488d86274b8a0a881c59c220c840003b),
(11, 'doc', 'application/msword', 'Microsoft Word', 0x47494638396110001000d500004476cadededeccccccafb8c784a6daeaecee92b8f5bdcbdfa5a5a56095eef6f4f2d1d8e0a2b2cfe2e5e9548ceaadc2e6d6d6d6ffffffcfd7e37a9fd98bafefc1c1c1adc5debccfeae8f1fd7698d4d6dee9ebeae7abc3efc2d0e4ebebe9dfe0e3b5b5b5e8eaec8fa5cd548dedd5dae1aaaaaabac9dacad7ebe6e5e2f0efefc5cedec2c2c293b0e3b3c7ebb3c7e4457ad29dbdf4e7e6e5e6dedef7efeff6f6f6d7d8dbc8d4e67b9cdec0d1ee598fee588ce8b5c5deaec8f4cfdbedc8d3e100000021f90405140011002c00000000100010000006aac048c42380188f479450b8593a9710a7875628a4141e4f2a458b2e3d9154af852be02e85ae73430b3d4e05dfc3a6517899b4c6a3b1d93c34282977111b0a31361d31312e24280583858a1f1d0b2e0b1f1b83313431241f311f07240128024e9b1f360b2828070b1001a64b81281f1224123e35462b4e1f0501c101bb20082515be05317d1b28100808204f2c061c1423d82f0c1f4f112c18303c09393a0003dd112e260413ed1922dd41003b),
(17, 'mov', 'video/mpeg', 'MPEG Movie', 0x47494638396110001000d50000666666c7d3dcaeb7c13e86c4e6e6e69999995792c5c1ccd4f7f7f74a99d899b9d0778ea77494b1d8dadc298be0a7a7a7b5c2cb828282c3c3c34daded85a7c08b8b8bffffff3292e3aec5d5d6d6d6bebebe3d93d8adadadccccccbec6cdeeeeee4295dea7bfd24b9be472a0c22c94ed579bcee0e2e4469ddf8cadc9d1d4d8b5b5b5c5c9cd3e8bcf4593cba9bdcdd2d7dbdededeeaeaeb4a9cdeb5c5d64aadf7a9c6dcbdc9d36699ccd0d6db00000000000000000000000000000000000000000021f90405140016002c00000000100010000006a3408be511291a8f0fa1b0618a3554a94f27d3e148949646a5d8a970228f422183cd00c4e24a2722ee940988b83c6e222b33303901de6cd885192b08263717172527351229652608061b211809220d0d6e7719290e367b2f13101d9880121e2429071430272e2aa316290d04272304010c202b2b1a58a23118342d032c0b0f0f8c4a1d1a7b380a28021608c8c9197b262697d25842a2d7307e1a7f5830e4e47e30db1641003b),
(12, 'xls', 'application/vnd.ms-excel', 'Microsoft Excel', 0x47494638396110001000d50000079f04c0f2bfd6d6d6b5b5b574d8714fc14d49b645f6f6f6a7a7a7eee9ee2dc52acccccce5e5e566cc66dedddeffffff9ad799a4c0a40abd048cc18b60d55cbababa4ace4771ba6fa8d4a7efefefe6dee610be0ac5c5c5afafaff4f4ff4bbf4853c24f66cc66efeff70bbb0606a60232c52c00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000021f9040514000f002c000000001000100000067ac0c783b110188f478650a85c3a850267e2e0701c32d7eb23ba641c8c592c96cbfc0aae99f4d8e9058bb3e4a1390c6767dce1bbddf838f8fd090b760b1c071e1e62447b461c480e1c76559202151d1d904b0e19099c091a020808034f0d140a0a12a924134d4e0d0104161bb300114f42101805051f1f0617b741003b),
(16, 'mp3', 'audio/x-mp3', 'MP3 Audio', 0x47494638396110001000d500004b914fd5cfc8a5b1b59999998c8c8ce1e1e3b1ccd07e7e7eefefef6db36b87b3889fb397ccccccdedddef7f7f7bdbdbdb4cdf3a8b8afebe4b383de738aa98b66996680a381bcbfd7a5a5a5c6ddb6ffffffb5b6b591ba959cc0a1d6d6d6b6c5b9c4c4c44fa752e6e6e67ab769b4dbcad1d2d8e9e1d6e7e8ecadadad8bb68b61a15c93af91b5b6bd8cb4919cb2a096c8929ec4a355a15a00000000000000000000000000000000000000000000000000000000000000000000000000000000000021f9040514001a002c00000000100010000006a3408de681291a8f0fa1d0c3f4141a1b28a2c1506a3c070c6aa0c5340884a4d213de0e508f8d8800b27a340e84071460201c8e92dbd1508d4c122f02777a630e2c2d212b0b13092508556327250e1f140c2e1505905625270d2711160f0e29222291427617102429770a22056d4a760d0619310909000d0d62aa27c20d1d301c150c0f28560ca7ce051e03d205cccf4f0c6a08561a20a74f0d1ecadadb0ce5e66927db41003b),
(13, 'swf', 'application/x-shockwave-flash', 'Macromedia Flash', 0x47494638396110001000d50000032f51d4d6deb5b8bc8c99a5808e9c6e8397eaedef557790c4cad09ca9b7f7f7f7dfe3e67a8b9b33536d949faed0d4d7999999c1c5c97676760b4568ffffffabababa2a2a2b5bdc4dedede1c44615b87a3aeb2b59999999aa5b1e4e7eb8a96a3bdbdbdd7dce2cbd1d9d6d6d659788f7d7d7dc4c4c410486c9ba6b2dfe0e6e6e6e6ccccccefefef8493a0b3bfcab5b5b5b8bcc0949ca596a1aed8dce3c4c8cb00000000000000000000000000000000000000000000000000000000000000000021f90405140014002c00000000100010000006a0400a0504291a8f20a110235460562b8c62ca5432151bd9e522dbb09c4a8a1446282042aa81006c65c52e8dcc6cfe618dc218162a6500905c33322a776d1d0614052709330e2a0f780a2d210a0b131a01040b34900f2d221e09070434230261232c2c9d0c0c04110f2615a7a9b4761b16b8a706bb062a2a2025121084420fbebe4fb20a2b6114c6c82b20160a8fcd0f0b0b4f201515c4610f502b26e32acd1441003b),
(14, 'txt', 'text/plain', 'Plain Text File', 0x47494638396110001000d50000513d32d8d1cdc5c5c5afaea86c7879dededef0f0f0694e468799bbafb6c0888173aaa8a5f7f7f99db4decccccc474837828282945147e6e6e6999999d0a183b2b2b2e2d7d08d423a575349989685ffffffbdbdbdd6d6d6504c2ea5b5d15e443bb4b0a07b7b7b5e39308f8578d4d7dcdae1e4474a424f4231d6d6ceadadade3d6ce00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000021f9040514001a002c0000000010001000000691408da6e010188f488650c3590a194a0dc36058720c28c9c620585c22a04255c82998cd8391e860428d99857219a3f89c3a8e3747e2e0ec1f190026046e56447d051e09080d0d247a7c051b657d1c026286667e1b95027986971c95a20e5ba0a205461b1b15157a1c1b4802132110154e1c01010ea5ab137c4e4c16142571bc0b4dc1572a121271021bc14206717ed6d241003b),
(15, 'rtf', 'text/richtext', 'Rich Text File', 0x47494638396110001000d50000513d32d8d1cdc5c5c5afaea86c7879dededef0f0f0694e468799bbafb6c0888173aaa8a5f7f7f99db4decccccc474837828282945147e6e6e6999999d0a183b2b2b2e2d7d08d423a575349989685ffffffbdbdbdd6d6d6504c2ea5b5d15e443bb4b0a07b7b7b5e39308f8578d4d7dcdae1e4474a424f4231d6d6ceadadade3d6ce00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000021f9040514001a002c0000000010001000000691408da6e010188f488650c3590a194a0dc36058720c28c9c620585c22a04255c82998cd8391e860428d99857219a3f89c3a8e3747e2e0ec1f190026046e56447d051e09080d0d247a7c051b657d1c026286667e1b95027986971c95a20e5ba0a205461b1b15157a1c1b4802132110154e1c01010ea5ab137c4e4c16142571bc0b4dc1572a121271021bc14206717ed6d241003b);

CREATE TABLE IF NOT EXISTS `draft_audience` (
  `daudience_id` int(12) NOT NULL AUTO_INCREMENT,
  `eaudience_id` int(12) NOT NULL,
  `devent_id` int(12) NOT NULL DEFAULT '0',
  `event_id` int(12) NOT NULL DEFAULT '0',
  `audience_type` enum('proxy_id','grad_year','cohort','organisation_id','group_id','course_id') NOT NULL,
  `audience_value` varchar(16) NOT NULL,
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`daudience_id`),
  KEY `eaudience_id` (`eaudience_id`),
  KEY `event_id` (`event_id`),
  KEY `target_value` (`audience_value`),
  KEY `target_type` (`audience_type`),
  KEY `event_id_2` (`event_id`,`audience_type`,`audience_value`),
  KEY `audience_type` (`audience_type`,`audience_value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `draft_contacts` (
  `dcontact_id` int(12) NOT NULL AUTO_INCREMENT,
  `econtact_id` int(12) DEFAULT NULL,
  `devent_id` int(12) NOT NULL DEFAULT '0',
  `event_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `contact_role` enum('teacher','tutor','ta','auditor') NOT NULL,
  `contact_order` int(6) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`dcontact_id`),
  KEY `econtact_id` (`econtact_id`),
  KEY `contact_order` (`contact_order`),
  KEY `event_id` (`event_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `draft_creators` (
  `create_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `draft_id` int(11) NOT NULL,
  `proxy_id` int(11) NOT NULL,
  PRIMARY KEY (`create_id`),
  KEY `DRAFT` (`draft_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `draft_events` (
  `devent_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) DEFAULT NULL,
  `draft_id` int(11) DEFAULT NULL,
  `parent_id` int(12) DEFAULT NULL,
  `event_children` int(12) DEFAULT NULL,
  `recurring_id` int(12) DEFAULT '0',
  `region_id` int(12) DEFAULT '0',
  `course_id` int(12) NOT NULL DEFAULT '0',
  `event_phase` varchar(12) DEFAULT NULL,
  `event_title` varchar(255) NOT NULL,
  `event_description` text,
  `include_parent_description` tinyint(1) NOT NULL DEFAULT '1',
  `event_goals` text,
  `event_objectives` text,
  `event_message` text,
  `include_parent_message` tinyint(1) NOT NULL DEFAULT '1',
  `event_location` varchar(64) DEFAULT NULL,
  `event_start` bigint(64) NOT NULL,
  `event_finish` bigint(64) NOT NULL,
  `event_duration` int(64) NOT NULL,
  `release_date` bigint(64) NOT NULL,
  `release_until` bigint(64) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`devent_id`),
  KEY `event_id` (`event_id`),
  KEY `course_id` (`course_id`),
  KEY `region_id` (`region_id`),
  KEY `recurring_id` (`recurring_id`),
  KEY `release_date` (`release_date`,`release_until`),
  KEY `event_start` (`event_start`,`event_duration`),
  KEY `event_start_2` (`event_start`,`event_finish`),
  KEY `event_phase` (`event_phase`),
  KEY `event_start_3` (`event_start`,`event_finish`,`release_date`,`release_until`),
  KEY `parent_id` (`parent_id`),
  FULLTEXT KEY `event_title` (`event_title`,`event_description`,`event_goals`,`event_objectives`,`event_message`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `draft_eventtypes` (
  `deventtype_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `eeventtype_id` int(12) DEFAULT NULL,
  `devent_id` int(12) NOT NULL,
  `event_id` int(12) DEFAULT NULL,
  `eventtype_id` int(12) NOT NULL,
  `duration` int(12) NOT NULL,
  `order` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`deventtype_id`),
  KEY `eeventtype_id` (`eeventtype_id`),
  KEY `event_id` (`devent_id`),
  KEY `eventtype_id` (`eventtype_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `drafts` (
  `draft_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `status` text,
  `name` text,
  `description` text,
  `created` int(11) DEFAULT NULL,
  `preserve_elements` binary(4) DEFAULT NULL,
  PRIMARY KEY (`draft_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `global_lu_countries` (
  `countries_id` int(6) NOT NULL AUTO_INCREMENT,
  `country` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`countries_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `global_lu_countries` (`countries_id`, `country`) VALUES
(1, 'Afghanistan'),
(2, 'Aland Islands'),
(3, 'Albania'),
(4, 'Algeria'),
(5, 'American Samoa'),
(6, 'Andorra'),
(7, 'Angola'),
(8, 'Anguilla'),
(9, 'Antarctica'),
(10, 'Antigua and Barbuda'),
(11, 'Argentina'),
(12, 'Armenia'),
(13, 'Aruba'),
(14, 'Australia'),
(15, 'Austria'),
(16, 'Azerbaijan'),
(17, 'Bahamas'),
(18, 'Bahrain'),
(19, 'Bangladesh'),
(20, 'Barbados'),
(21, 'Belarus'),
(22, 'Belgium'),
(23, 'Belize'),
(24, 'Benin'),
(25, 'Bermuda'),
(26, 'Bhutan'),
(27, 'Bolivia'),
(28, 'Bosnia and Herzegovina'),
(29, 'Botswana'),
(30, 'Bouvet Island'),
(31, 'Brazil'),
(32, 'British Indian Ocean territory'),
(33, 'Brunei Darussalam'),
(34, 'Bulgaria'),
(35, 'Burkina Faso'),
(36, 'Burundi'),
(37, 'Cambodia'),
(38, 'Cameroon'),
(39, 'Canada'),
(40, 'Cape Verde'),
(41, 'Cayman Islands'),
(42, 'Central African Republic'),
(43, 'Chad'),
(44, 'Chile'),
(45, 'China'),
(46, 'Christmas Island'),
(47, 'Cocos (Keeling) Islands'),
(48, 'Colombia'),
(49, 'Comoros'),
(50, 'Congo'),
(51, 'Congo'),
(52, 'Democratic Republic'),
(53, 'Cook Islands'),
(54, 'Costa Rica'),
(55, 'Cote D''Ivoire (Ivory Coast)'),
(56, 'Croatia (Hrvatska)'),
(57, 'Cuba'),
(58, 'Cyprus'),
(59, 'Czech Republic'),
(60, 'Denmark'),
(61, 'Djibouti'),
(62, 'Dominica'),
(63, 'Dominican Republic'),
(64, 'East Timor'),
(65, 'Ecuador'),
(66, 'Egypt'),
(67, 'El Salvador'),
(68, 'Equatorial Guinea'),
(69, 'Eritrea'),
(70, 'Estonia'),
(71, 'Ethiopia'),
(72, 'Falkland Islands'),
(73, 'Faroe Islands'),
(74, 'Fiji'),
(75, 'Finland'),
(76, 'France'),
(77, 'French Guiana'),
(78, 'French Polynesia'),
(79, 'French Southern Territories'),
(80, 'Gabon'),
(81, 'Gambia'),
(82, 'Georgia'),
(83, 'Germany'),
(84, 'Ghana'),
(85, 'Gibraltar'),
(86, 'Greece'),
(87, 'Greenland'),
(88, 'Grenada'),
(89, 'Guadeloupe'),
(90, 'Guam'),
(91, 'Guatemala'),
(92, 'Guinea'),
(93, 'Guinea-Bissau'),
(94, 'Guyana'),
(95, 'Haiti'),
(96, 'Heard and McDonald Islands'),
(97, 'Honduras'),
(98, 'Hong Kong'),
(99, 'Hungary'),
(100, 'Iceland'),
(101, 'India'),
(102, 'Indonesia'),
(103, 'Iran'),
(104, 'Iraq'),
(105, 'Ireland'),
(106, 'Israel'),
(107, 'Italy'),
(108, 'Jamaica'),
(109, 'Japan'),
(110, 'Jordan'),
(111, 'Kazakhstan'),
(112, 'Kenya'),
(113, 'Kiribati'),
(114, 'Korea (north)'),
(115, 'Korea (south)'),
(116, 'Kuwait'),
(117, 'Kyrgyzstan'),
(118, 'Lao People''s Democratic Republic'),
(119, 'Latvia'),
(120, 'Lebanon'),
(121, 'Lesotho'),
(122, 'Liberia'),
(123, 'Libyan Arab Jamahiriya'),
(124, 'Liechtenstein'),
(125, 'Lithuania'),
(126, 'Luxembourg'),
(127, 'Macao'),
(128, 'Macedonia'),
(129, 'Madagascar'),
(130, 'Malawi'),
(131, 'Malaysia'),
(132, 'Maldives'),
(133, 'Mali'),
(134, 'Malta'),
(135, 'Marshall Islands'),
(136, 'Martinique'),
(137, 'Mauritania'),
(138, 'Mauritius'),
(139, 'Mayotte'),
(140, 'Mexico'),
(141, 'Micronesia'),
(142, 'Moldova'),
(143, 'Monaco'),
(144, 'Mongolia'),
(145, 'Montserrat'),
(146, 'Morocco'),
(147, 'Mozambique'),
(148, 'Myanmar'),
(149, 'Namibia'),
(150, 'Nauru'),
(151, 'Nepal'),
(152, 'Netherlands'),
(153, 'Netherlands Antilles'),
(154, 'New Caledonia'),
(155, 'New Zealand'),
(156, 'Nicaragua'),
(157, 'Niger'),
(158, 'Nigeria'),
(159, 'Niue'),
(160, 'Norfolk Island'),
(161, 'Northern Mariana Islands'),
(162, 'Norway'),
(163, 'Oman'),
(164, 'Pakistan'),
(165, 'Palau'),
(166, 'Palestinian Territories'),
(167, 'Panama'),
(168, 'Papua New Guinea'),
(169, 'Paraguay'),
(170, 'Peru'),
(171, 'Philippines'),
(172, 'Pitcairn'),
(173, 'Poland'),
(174, 'Portugal'),
(175, 'Puerto Rico'),
(176, 'Qatar'),
(177, 'Reunion'),
(178, 'Romania'),
(179, 'Russian Federation'),
(180, 'Rwanda'),
(181, 'Saint Helena'),
(182, 'Saint Kitts and Nevis'),
(183, 'Saint Lucia'),
(184, 'Saint Pierre and Miquelon'),
(185, 'Saint Vincent and the Grenadines'),
(186, 'Samoa'),
(187, 'San Marino'),
(188, 'Sao Tome and Principe'),
(189, 'Saudi Arabia'),
(190, 'Senegal'),
(191, 'Serbia and Montenegro'),
(192, 'Seychelles'),
(193, 'Sierra Leone'),
(194, 'Singapore'),
(195, 'Slovakia'),
(196, 'Slovenia'),
(197, 'Solomon Islands'),
(198, 'Somalia'),
(199, 'South Africa'),
(200, 'South Georgia and the South Sandwich Islands'),
(201, 'Spain'),
(202, 'Sri Lanka'),
(203, 'Sudan'),
(204, 'Suriname'),
(205, 'Svalbard and Jan Mayen Islands'),
(206, 'Swaziland'),
(207, 'Sweden'),
(208, 'Switzerland'),
(209, 'Syria'),
(210, 'Taiwan'),
(211, 'Tajikistan'),
(212, 'Tanzania'),
(213, 'Thailand'),
(214, 'Togo'),
(215, 'Tokelau'),
(216, 'Tonga'),
(217, 'Trinidad and Tobago'),
(218, 'Tunisia'),
(219, 'Turkey'),
(220, 'Turkmenistan'),
(221, 'Turks and Caicos Islands'),
(222, 'Tuvalu'),
(223, 'Uganda'),
(224, 'Ukraine'),
(225, 'United Arab Emirates'),
(226, 'United Kingdom'),
(227, 'United States of America'),
(228, 'Uruguay'),
(229, 'Uzbekistan'),
(230, 'Vanuatu'),
(231, 'Vatican City'),
(232, 'Venezuela'),
(233, 'Vietnam'),
(234, 'Virgin Islands (British)'),
(235, 'Virgin Islands (US)'),
(236, 'Wallis and Futuna Islands'),
(237, 'Western Sahara'),
(238, 'Yemen'),
(239, 'Zaire'),
(240, 'Zambia'),
(241, 'Zimbabwe');

CREATE TABLE IF NOT EXISTS `global_lu_disciplines` (
  `discipline_id` int(11) NOT NULL AUTO_INCREMENT,
  `discipline` varchar(250) NOT NULL,
  PRIMARY KEY (`discipline_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `global_lu_disciplines` (`discipline_id`, `discipline`) VALUES
(1, 'Adolescent Medicine'),
(2, 'Anatomical Pathology'),
(3, 'Anesthesiology'),
(4, 'Cardiac Surgery'),
(5, 'Cardiology'),
(6, 'Child & Adolescent Psychiatry'),
(7, 'Clinical Immunology and Allergy'),
(8, 'Clinical Pharmacology'),
(9, 'Colorectal Surgery'),
(10, 'Community Medicine'),
(11, 'Critical Care Medicine'),
(12, 'Dermatology'),
(13, 'Developmental Pediatrics'),
(14, 'Diagnostic Radiology'),
(15, 'Emergency Medicine'),
(16, 'Endocrinology and Metabolism'),
(17, 'Family Medicine'),
(18, 'Forensic Pathology'),
(19, 'Forensic Psychiatry'),
(20, 'Gastroenterology'),
(21, 'General Pathology'),
(22, 'General Surgery'),
(23, 'General Surgical Oncology'),
(24, 'Geriatric Medicine'),
(25, 'Geriatric Psychiatry'),
(26, 'Gynecologic Oncology'),
(27, 'Gynecologic Reproductive Endocrinology and Infertility'),
(28, 'Hematological Pathology '),
(29, 'Hematology'),
(30, 'Infectious Disease'),
(31, 'Internal Medicine'),
(32, 'Maternal-Fetal Medicine'),
(33, 'Medical Biochemistry'),
(34, 'Medical Genetics'),
(35, 'Medical Microbiology'),
(36, 'Medical Oncology'),
(37, 'Neonatal-Perinatal Medicine'),
(38, 'Nephrology'),
(39, 'Neurology'),
(40, 'Neuropathology'),
(41, 'Neuroradiology'),
(42, 'Neurosurgery'),
(43, 'Nuclear Medicine'),
(44, 'Obstetrics & Gynecology'),
(45, 'Occupational Medicine'),
(46, 'Ophthalmology'),
(47, 'Orthopedic Surgery'),
(48, 'Otolaryngology-Head and Neck Surgery'),
(49, 'Palliative Medicine'),
(50, 'Pediatric Emergency Medicine'),
(51, 'Pediatric General Surgery'),
(52, 'Pediatric Hematology/Oncology'),
(53, 'Pediatric Radiology'),
(54, 'Pediatrics'),
(55, 'Physical Medicine and Rehabilitation'),
(56, 'Plastic Surgery'),
(57, 'Psychiatry'),
(58, 'Radiation Oncology'),
(59, 'Respirology'),
(60, 'Rheumatology'),
(61, 'Thoracic Surgery'),
(62, 'Transfusion Medicine'),
(63, 'Urology'),
(64, 'Vascular Surgery');

CREATE TABLE IF NOT EXISTS `global_lu_focus_groups` (
  `group_id` int(11) NOT NULL auto_increment,
  `focus_group` varchar(100) NOT NULL default '',
  PRIMARY KEY (`group_id`),
  KEY `focus_group` (`focus_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `global_lu_focus_groups` (`group_id`, `focus_group`) VALUES
(1, 'Cancer'),
(2, 'Neurosciences'),
(3, 'Cardiovascular, Circulatory and Respiratory'),
(4, 'Gastrointestinal'),
(5, 'Musculoskeletal\n'),
(6, 'Health Services Research'),
(15, 'Other'),
(7, 'Protein Function and Discovery'),
(8, 'Reproductive Sciences'),
(9, 'Genetics'),
(10, 'Nursing'),
(11, 'Primary Care Studies'),
(12, 'Emergency'),
(13, 'Critical Care'),
(14, 'Nephrology'),
(16, 'Educational Research'),
(17, 'Microbiology and Immunology'),
(18, 'Urology'),
(19, 'Psychiatry'),
(20, 'Anesthesiology'),
(22, 'Obstetrics and Gynecology'),
(23, 'Rehabilitation Therapy'),
(24, 'Occupational Therapy');

CREATE TABLE IF NOT EXISTS `global_lu_hospital_location` (
  `hosp_id` int(11) NOT NULL default '0',
  `hosp_desc` varchar(100) NOT NULL default '',
  PRIMARY KEY (`hosp_id`),
  KEY `hosp_desc` (`hosp_desc`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `global_lu_objectives` (
  `objective_id` int(12) NOT NULL AUTO_INCREMENT,
  `objective_code` varchar(24) DEFAULT NULL,
  `objective_name` varchar(60) NOT NULL,
  `objective_description` text,
  `objective_parent` int(12) NOT NULL DEFAULT '0',
  `objective_order` int(12) NOT NULL DEFAULT '0',
  `objective_active` int(12) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`objective_id`),
  KEY `objective_order` (`objective_order`),
  KEY `objective_code` (`objective_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `global_lu_objectives` (`objective_id`, `objective_name`, `objective_description`, `objective_code`, `objective_parent`, `objective_order`, `objective_active`, `updated_date`, `updated_by`) VALUES
(1, 'Curriculum Objectives', '', NULL, 0, 0, 1, 0, 0),
(2, 'Medical Expert', '', NULL, 1, 0, 1, 0, 0),
(3, 'Professionalism', '', NULL, 1, 0, 1, 0, 0),
(4, 'Scholar', '', NULL, 1, 0, 1, 0, 0),
(5, 'Communicator', '', NULL, 1, 0, 1, 0, 0),
(6, 'Collaborator', '', NULL, 1, 0, 1, 0, 0),
(7, 'Advocate', '', NULL, 1, 0, 1, 0, 0),
(8, 'Manager', '', NULL, 1, 0, 1, 0, 0),
(9, 'Application of Basic Sciences', 'The competent medical graduate articulates and uses the basic sciences to inform disease prevention, health promotion and the assessment and management of patients presenting with clinical illness.', NULL, 2, 0, 1, 0, 0),
(10, 'Clinical Assessment', 'Is able to perform a complete and appropriate clinical assessment of patients presenting with clinical illness', NULL, 2, 1, 1, 0, 0),
(11, 'Clinical Presentations', 'Is able to appropriately assess and provide initial management for patients presenting with clinical illness, as defined by the Medical Council of Canada Clinical Presentations', NULL, 2, 2, 1, 0, 0),
(12, 'Health Promotion', 'Apply knowledge of disease prevention and health promotion to the care of patients', NULL, 2, 3, 1, 0, 0),
(13, 'Professional Behaviour', 'Demonstrates appropriate professional behaviours to serve patients, the profession, and society', NULL, 3, 0, 1, 0, 0),
(14, 'Principles of Professionalism', 'Apply knowledge of legal and ethical principles to serve patients, the profession, and society', NULL, 3, 1, 1, 0, 0),
(15, 'Critical Appraisal', 'Critically evaluate medical information and its sources (the literature)', NULL, 4, 0, 1, 0, 0),
(16, 'Research', 'Contribute to the process of knowledge creation (research)', NULL, 4, 1, 1, 0, 0),
(17, 'Life Long Learning', 'Engages in life long learning', NULL, 4, 2, 1, 0, 0),
(18, 'Effective Communication', 'Effectively communicates with colleagues, other health professionals, patients, families and other caregivers', NULL, 5, 0, 1, 0, 0),
(19, 'Effective Collaboration', 'Effectively collaborate with colleagues and other health professionals', NULL, 6, 0, 1, 0, 0),
(20, 'Determinants of Health', 'Articulate and apply the determinants of health and disease, principles of health promotion and disease prevention', NULL, 7, 0, 1, 0, 0),
(21, 'Profession and Community', 'Effectively advocate for their patients, the profession, and community', NULL, 7, 1, 1, 0, 0),
(22, 'Practice Options', 'Describes a variety of practice options and settings within the practice of Medicine', NULL, 8, 0, 1, 0, 0),
(23, 'Balancing Health and Profession', 'Balances personal health and professional responsibilities', NULL, 8, 1, 1, 0, 0),
(24, 'ME1.1 Homeostasis & Dysregulation', 'Applies knowledge of molecular, biochemical, cellular, and systems-level mechanisms that maintain homeostasis, and of the dysregulation of these mechanisms, to the prevention, diagnosis, and management of disease.', NULL, 9, 0, 1, 0, 0),
(25, 'ME1.2 Physics and Chemistry', 'Apply major principles of physics and chemistry to explain normal biology, the pathobiology of significant diseases, and the mechanism of action of major technologies used in the prevention, diagnosis, and treatment of disease.', NULL, 9, 1, 1, 0, 0),
(26, 'ME1.3 Genetics', 'Use the principles of genetic transmission, molecular biology of the human genome, and population genetics to guide assessments and clinical decision making.', NULL, 9, 2, 1, 0, 0),
(27, 'ME1.4 Defense Mechanisms', 'Apply the principles of the cellular and molecular basis of immune and nonimmune host defense mechanisms in health and disease to determine the etiology of disease, identify preventive measures, and predict response to therapies.', NULL, 9, 3, 1, 0, 0),
(28, 'ME1.5 Pathological Processes', 'Apply the mechanisms of general and disease-specific pathological processes in health and disease to the prevention, diagnosis, management, and prognosis of critical human disorders.', NULL, 9, 4, 1, 0, 0),
(29, 'ME1.6 Microorganisms', 'Apply principles of the biology of microorganisms in normal physiology and disease to explain the etiology of disease, identify preventive measures, and predict response to therapies.', NULL, 9, 5, 1, 0, 0),
(30, 'ME1.7 Pharmacology', 'Apply the principles of pharmacology to evaluate options for safe, rational, and optimally beneficial drug therapy.', NULL, 9, 6, 1, 0, 0),
(31, 'ME1.8 Quantitative Reasoning', 'Apply quantitative knowledge and reasoning--including integration of data, modeling, computation, and analysis--and informatics tools to diagnostic and therapeutic clinical decision making.', NULL, 9, 7, 1, 0, 0),
(32, 'ME2.1 History and Physical', 'Conducts a comprehensive and appropriate history and physical examination ', NULL, 10, 0, 1, 0, 0),
(33, 'ME2.2 Procedural Skills', 'Demonstrate proficient and appropriate use of selected procedural skills, diagnostic and therapeutic', NULL, 10, 1, 1, 0, 0),
(34, 'ME3.x Clinical Presentations', '', NULL, 11, 0, 1, 0, 0),
(35, 'ME4.1 Health Promotion & Maintenance', '', NULL, 12, 0, 1, 0, 0),
(36, 'P1.1 Professional Behaviour', 'Practice appropriate professional behaviours, including honesty, integrity, commitment, dependability, compassion, respect, an understanding of the human condition, and altruism in the educational  and clinical settings', NULL, 13, 0, 1, 0, 0),
(37, 'P1.2 Patient-Centered Care', 'Learn how to deliver the highest quality patient-centered care, with commitment to patients'' well being.  ', NULL, 13, 1, 1, 0, 0),
(38, 'P1.3 Self-Awareness', 'Is self-aware, engages consultancy appropriately and maintains competence', NULL, 13, 2, 1, 0, 0),
(39, 'P2.1 Ethics', 'Analyze ethical issues encountered in practice (such as informed consent, confidentiality, truth telling, vulnerable populations, etc.)', NULL, 14, 0, 1, 0, 0),
(40, 'P2.2 Law and Regulation', 'Apply profession-led regulation to serve patients, the profession and society. ', NULL, 14, 1, 1, 0, 0),
(41, 'S1.1 Information Retrieval', 'Are able to retrieve medical information efficiently and effectively', NULL, 15, 0, 1, 0, 0),
(42, 'S1.2 Critical Evaluation', 'Critically evaluate the validity and applicability of medical procedures and therapeutic modalities to patient care', NULL, 15, 1, 1, 0, 0),
(43, 'S2.1 Research Methodology', 'Adopt rigorous research methodology and scientific inquiry procedures', NULL, 16, 0, 1, 0, 0),
(44, 'S2.2 Sharing Innovation', 'Prepares and disseminates new medical information', NULL, 16, 1, 1, 0, 0),
(45, 'S3.1 Learning Strategies', 'Implements effective personal learning experiences including the capacity to engage in reflective learning', NULL, 17, 0, 1, 0, 0),
(46, 'CM1.1 Therapeutic Relationships', 'Demonstrate skills and attitudes to foster rapport, trust and ethical therapeutic relationships with patients and families', NULL, 18, 0, 1, 0, 0),
(47, 'CM1.2 Eliciting Perspectives', 'Elicit and synthesize relevant information and perspectives of patients and families, colleagues and other professionals', NULL, 18, 1, 1, 0, 0),
(48, 'CM1.3 Conveying Information', 'Convey relevant information and explanations appropriately to patients and families, colleagues and other professionals, orally and in writing', NULL, 18, 2, 1, 0, 0),
(49, 'CM1.4 Finding Common Ground', 'Develop a common understanding on issues, problems, and plans with patients and families, colleagues and other professionals to develop a shared plan of care', NULL, 18, 3, 1, 0, 0),
(50, 'CL 1.1 Working In Teams', 'Participate effectively and appropriately as part of a multiprofessional healthcare team.', NULL, 19, 0, 1, 0, 0),
(51, 'CL1.2 Overcoming Conflict', 'Work with others effectively in order to prevent, negotiate, and resolve conflict.', NULL, 19, 1, 1, 0, 0),
(52, 'CL1.3 Including Patients and Families', 'Includes patients and families in prevention and management of illness', NULL, 19, 2, 1, 0, 0),
(53, 'CL1.4 Teaching and Learning', 'Teaches and learns from others consistently  ', NULL, 19, 3, 1, 0, 0),
(54, 'A1.1 Applying Determinants of Health', 'Apply knowledge of the determinants of health for populations to medical encounters and problems.', NULL, 20, 0, 1, 0, 0),
(55, 'A2.1 Community Resources', 'Identify and communicate about community resources to promote health, prevent disease, and manage illness in their patients and the communities they will serve.', NULL, 21, 0, 1, 0, 0),
(56, 'A2.2 Responsibility and Service', 'Integrate the principles of advocacy into their understanding of their professional responsibility to patients and the communities they will serve. ', NULL, 21, 1, 1, 0, 0),
(57, 'M1.1 Career Settings', 'Is aware of the variety of practice options and settings within the practice of Medicine, and makes informed personal choices regarding career direction', NULL, 22, 0, 1, 0, 0),
(58, 'M2.1 Work / Life Balance', 'Identifies and implement strategies that promote care of one''s self and one''s colleagues to maintain balance between personal and educational/ professional commitments', NULL, 23, 0, 1, 0, 0),
(59, 'ME1.1a', 'Apply knowledge of biological systems and their interactions to explain how the human body functions in health and disease. ', NULL, 24, 0, 1, 0, 0),
(60, 'ME1.1b', 'Use the principles of feedback control to explain how specific homeostatic and reproductive systems maintain the internal environment and identify (1) how perturbations in these systems may result in disease and (2) how homeostasis may be changed by disease.', NULL, 24, 1, 1, 0, 0),
(61, 'ME1.1c', 'Apply knowledge of the atomic and molecular characteristics of biological constituents to predict normal and pathological molecular function.', NULL, 24, 2, 1, 0, 0),
(62, 'ME1.1d', 'Explain how the regulation of major biochemical energy production pathways and the synthesis/degradation of macromolecules function to maintain health and identify major forms of dysregulation in disease.', NULL, 24, 3, 1, 0, 0),
(63, 'ME1.1e', 'Explain the major mechanisms of intra- and intercellular communication and their role in health and disease states.', NULL, 24, 4, 1, 0, 0),
(64, 'ME1.1f', 'Apply an understanding of the morphological and biochemical events that occur when somatic or germ cells divide, and the mechanisms that regulate cell division and cell death, to explain normal and abnormal growth and development.', NULL, 24, 5, 1, 0, 0),
(65, 'ME1.1g', 'Identify and describe the common and unique microscopic and three dimensional macroscopic structures of macromolecules, cells, tissues, organs, systems, and compartments that lead to their unique and integrated function from fertilization through senescence to explain how perturbations contribute to disease. ', NULL, 24, 6, 1, 0, 0),
(66, 'ME1.1h', 'Predict the consequences of structural variability and damage or loss of tissues and organs due to maldevelopment, trauma, disease, and aging.', NULL, 24, 7, 1, 0, 0),
(67, 'ME1.1i', 'Apply principles of information processing at the molecular, cellular, and integrated nervous system level and understanding of sensation, perception, decision making, action, and cognition to explain behavior in health and disease.', NULL, 24, 8, 1, 0, 0),
(68, 'ME1.2a', 'Apply the principles of physics and chemistry, such as mass flow, transport, electricity, biomechanics, and signal detection and processing, to the specialized functions of membranes, cells, tissues, organs, and the human organism, and recognize how perturbations contribute to disease.', NULL, 25, 0, 1, 0, 0),
(69, 'ME1.2b', 'Apply the principles of physics and chemistry to explain the risks, limitations, and appropriate use of diagnostic and therapeutic technologies.', NULL, 25, 1, 1, 0, 0),
(70, 'ME1.3a', 'Describe the functional elements in the human genome, their evolutionary origins, their interactions, and the consequences of genetic and epigenetic changes on adaptation and health.', NULL, 26, 0, 1, 0, 0),
(71, 'ME1.3b', 'Explain how variation at the gene level alters the chemical and physical properties of biological systems, and how this, in turn, influences health.', NULL, 26, 1, 1, 0, 0),
(72, 'ME1.3c', 'Describe the major forms and frequencies of genetic variation and their consequences on health in different human populations.', NULL, 26, 2, 1, 0, 0),
(73, 'ME1.3d', 'Apply knowledge of the genetics and the various patterns of genetic transmission within families in order to obtain and interpret family history and ancestry data, calculate risk of disease, and order genetic tests to guide therapeutic decision-making.', NULL, 26, 3, 1, 0, 0),
(74, 'ME1.3e', 'Use to guide clinical action plans, the interaction of genetic and environmental factors to produce phenotypes and provide the basis for individual variation in response to toxic, pharmacological, or other exposures.', NULL, 26, 4, 1, 0, 0),
(75, 'ME1.4a', 'Apply knowledge of the generation of immunological diversity and specificity to the diagnosis and treatment of disease.', NULL, 27, 0, 1, 0, 0),
(76, 'ME1.4b', 'Apply knowledge of the mechanisms for distinction between self and nonself (tolerance and immune surveillance) to the maintenance of health, autoimmunity, and transplant rejection.', NULL, 27, 1, 1, 0, 0),
(77, 'ME1.4c', 'Apply knowledge of the molecular basis for immune cell development to diagnose and treat immune deficiencies.', NULL, 27, 2, 1, 0, 0),
(78, 'ME1.4d', 'Apply knowledge of the mechanisms used to defend against intracellular or extracellular microbes to the development of immunological prevention or treatment.', NULL, 27, 3, 1, 0, 0),
(79, 'ME1.5a', 'Apply knowledge of cellular responses to injury, and the underlying etiology, biochemical and molecular alterations, to assess therapeutic interventions.', NULL, 28, 0, 1, 0, 0),
(80, 'ME1.5b', 'Apply knowledge of the vascular and leukocyte responses of inflammation and their cellular and soluble mediators to the causation, resolution, prevention, and targeted therapy of tissue injury.', NULL, 28, 1, 1, 0, 0),
(81, 'ME1.5c', 'Apply knowledge of the interplay of platelets, vascular endothelium, leukocytes, and coagulation factors in maintaining fluidity of blood, formation of thrombi, and causation of atherosclerosis to the prevention and diagnosis of thrombosis and atherosclerosis in various vascular beds, and the selection of therapeutic responses.', NULL, 28, 2, 1, 0, 0),
(82, 'ME1.5d', 'Apply knowledge of the molecular basis of neoplasia to an understanding of the biological behavior, morphologic appearance, classification, diagnosis, prognosis, and targeted therapy of specific neoplasms.', NULL, 28, 3, 1, 0, 0),
(83, 'ME1.6a', 'Apply the principles of host-pathogen and pathogen-population interactions and knowledge of pathogen structure, genomics, lifecycle, transmission, natural history, and pathogenesis to the prevention, diagnosis, and treatment of infectious disease.', NULL, 29, 0, 1, 0, 0),
(84, 'ME1.6b', 'Apply the principles of symbiosis (commensalisms, mutualism, and parasitism) to the maintenance of health and disease.', NULL, 29, 1, 1, 0, 0),
(85, 'ME1.6c', 'Apply the principles of epidemiology to maintaining and restoring the health of communities and individuals.', NULL, 29, 2, 1, 0, 0),
(86, 'ME1.7a', 'Apply knowledge of pathologic processes, pharmacokinetics, and pharmacodynamics to guide safe and effective treatments.', NULL, 30, 0, 1, 0, 0),
(87, 'ME1.7b', 'Select optimal drug therapy based on an understanding of pertinent research, relevant medical literature, regulatory processes, and pharmacoeconomics.', NULL, 30, 1, 1, 0, 0),
(88, 'ME1.7c', 'Apply knowledge of individual variability in the use and responsiveness to pharmacological agents to selecting and monitoring therapeutic regimens and identifying adverse responses.', NULL, 30, 2, 1, 0, 0),
(89, 'ME1.8a', 'Apply basic mathematical tools and concepts, including functions, graphs and modeling, measurement and scale, and quantitative reasoning, to an understanding of the specialized functions of membranes, cells, tissues, organs, and the human organism, in both health and disease.', NULL, 31, 0, 1, 0, 0),
(90, 'ME1.8b', 'Apply the principles and approaches of statistics, biostatistics, and epidemiology to the evaluation and interpretation of disease risk, etiology, and prognosis, and to the prevention, diagnosis, and management of disease.', NULL, 31, 1, 1, 0, 0),
(91, 'ME1.8c', 'Apply the basic principles of information systems, their design and architecture, implementation, use, and limitations, to information retrieval, clinical problem solving, and public health and policy.', NULL, 31, 2, 1, 0, 0),
(92, 'ME1.8d', 'Explain the importance, use, and limitations of biomedical and health informatics, including data quality, analysis, and visualization, and its application to diagnosis, therapeutics, and characterization of populations and subpopulations. ', NULL, 31, 3, 1, 0, 0),
(93, 'ME1.8e', 'Apply elements of the scientific process, such as inference, critical analysis of research design, and appreciation of the difference between association and causation, to interpret the findings, applications, and limitations of observational and experimental research in clinical decision making.', NULL, 31, 4, 1, 0, 0),
(94, 'ME2.1a', 'Effectively identify and explore issues to be addressed in a patient encounter, including the patient''s context and preferences.', NULL, 32, 0, 1, 0, 0),
(95, 'ME2.1b', 'For purposes of prevention and health promotion, diagnosis and/or management, elicit a history that is relevant, concise and accurate to context and preferences.', NULL, 32, 1, 1, 0, 0),
(96, 'ME2.1c', 'For the purposes of prevention and health promotion, diagnosis and/or management, perform a focused physical examination that is relevant and accurate.', NULL, 32, 2, 1, 0, 0),
(97, 'ME2.1d', 'Select basic, medically appropriate investigative methods in an ethical manner.', NULL, 32, 3, 1, 0, 0),
(98, 'ME2.1e', 'Demonstrate effective clinical problem solving and judgment to address selected common patient presentations, including interpreting available data and integrating information to generate differential diagnoses and management plans.', NULL, 32, 4, 1, 0, 0),
(99, 'ME2.2a', 'Demonstrate effective, appropriate and timely performance of selected diagnostic procedures.', NULL, 33, 0, 1, 0, 0),
(100, 'ME2.2b', 'Demonstrate effective, appropriate and timely performance of selected therapeutic procedures.', NULL, 33, 1, 1, 0, 0),
(101, 'ME3.xa', 'Identify and apply aspects of normal human structure and physiology relevant to the clinical presentation.', NULL, 34, 0, 1, 0, 0),
(102, 'ME3.xb', 'Identify pathologic or maladaptive processes that are active.', NULL, 34, 1, 1, 0, 0),
(103, 'ME3.xc', 'Develop a differential diagnosis for the clinical presentation.', NULL, 34, 2, 1, 0, 0),
(104, 'ME3.xd', 'Use history taking and physical examination relevant to the clinical presentation.', NULL, 34, 3, 1, 0, 0),
(105, 'ME3.xe', 'Use diagnostic tests or procedures appropriately to establish working diagnoses.', NULL, 34, 4, 1, 0, 0),
(106, 'ME3.xf', 'Provide appropriate initial management for the clinical presentation.', NULL, 34, 5, 1, 0, 0),
(107, 'ME3.xg', 'Provide evidence for diagnostic and therapeutic choices.', NULL, 34, 6, 1, 0, 0),
(108, 'ME4.1a', 'Demonstrate awareness and respect for the Determinants of Health in identifying the needs of a patient.', NULL, 35, 0, 1, 0, 0),
(109, 'ME4.1b', 'Discover opportunities for health promotion and disease prevention as well as resources for patient care.', NULL, 35, 1, 1, 0, 0),
(110, 'ME4.1c', 'Formulate preventive measures into their management strategies.', NULL, 35, 2, 1, 0, 0),
(111, 'ME4.1d', 'Communicate with the patient, the patient''s family and concerned others with regard to risk factors and their modification where appropriate.', NULL, 35, 3, 1, 0, 0),
(112, 'ME4.1e', 'Describe programs for the promotion of health including screening for, and the prevention of, illness.', NULL, 35, 4, 1, 0, 0),
(113, 'P1.1a', 'Defines the concepts of honesty, integrity, commitment, dependability, compassion, respect and altruism as applied to medical practice and correctly identifies examples of appropriate and inappropriate application.', NULL, 36, 0, 1, 0, 0),
(114, 'P1.1b', 'Applies these concepts in medical and professional encounters.', NULL, 36, 1, 1, 0, 0),
(115, 'P1.2a', 'Defines the concept of "standard of care".', NULL, 37, 0, 1, 0, 0),
(116, 'P1.2b', 'Applies diagnostic and therapeutic modalities in evidence based and patient centred contexts.', NULL, 37, 1, 1, 0, 0),
(117, 'P1.3a', 'Recognizes and acknowledges limits of personal competence.', NULL, 38, 0, 1, 0, 0),
(118, 'P1.3b', 'Is able to acquire specific knowledge appropriately to assist clinical management.', NULL, 38, 1, 1, 0, 0),
(119, 'P1.3c', 'Engages colleagues and other health professionals appropriately.', NULL, 38, 2, 1, 0, 0),
(120, 'P2.1a', 'Analyze ethical issues encountered in practice (such as informed consent, confidentiality, truth telling, vulnerable populations etc).', NULL, 39, 0, 1, 0, 0),
(121, 'P2.1b', 'Analyze legal issues encountered in practice (such as conflict of interest, patient rights and privacy, etc).', NULL, 39, 1, 1, 0, 0),
(122, 'P2.1c', 'Analyze the psycho-social, cultural and religious issues that could affect patient management.', NULL, 39, 2, 1, 0, 0),
(123, 'P2.1d', 'Define and implement principles of appropriate relationships with patients.', NULL, 39, 3, 1, 0, 0),
(124, 'P2.2a', 'Recognize the professional, legal and ethical codes and obligations required of current practice in a variety of settings, including hospitals, private practice and health care institutions, etc.', NULL, 40, 0, 1, 0, 0),
(125, 'P2.2b', 'Recognize and respond appropriately to unprofessional behaviour in colleagues.', NULL, 40, 1, 1, 0, 0),
(126, 'S1.1a', 'Use objective parameters to assess reliability of various sources of medical information.', NULL, 41, 0, 1, 0, 0),
(127, 'S1.1b', 'Are able to efficiently search sources of medical information in order to address specific clinical questions.', NULL, 41, 1, 1, 0, 0),
(128, 'S1.2a', 'Apply knowledge of research and statistical methodology to the review of medical information and make decisions for health care of patients and society through scientifically rigourous analysis of evidence.', NULL, 42, 0, 1, 0, 0),
(129, 'S1.2b', 'Apply to the review of medical literature the principles of research ethics, including disclosure, conflicts of interest, research on human subjects and industry relations.', NULL, 42, 1, 1, 0, 0),
(130, 'S1.2c', 'Identify the nature and requirements of organizations contributing to medical education.', NULL, 42, 2, 1, 0, 0),
(131, 'S1.2d', 'Balance scientific evidence with consideration of patient preferences and overall quality of life in therapeutic decision making.', NULL, 42, 3, 1, 0, 0),
(132, 'S2.1a', 'Formulates relevant research hypotheses.', NULL, 43, 0, 1, 0, 0),
(133, 'S2.1b', 'Develops rigorous methodologies.', NULL, 43, 1, 1, 0, 0),
(134, 'S2.1c', 'Develops appropriate collaborations in order to participate in research projects.', NULL, 43, 2, 1, 0, 0),
(135, 'S2.1d', 'Practice research ethics, including disclosure, conflicts of interest, research on human subjects and industry relations.', NULL, 43, 3, 1, 0, 0),
(136, 'S2.1e', 'Evaluates the outcomes of research by application of rigorous statistical analysis.', NULL, 43, 4, 1, 0, 0),
(137, 'S2.2a', 'Report to students and faculty upon new knowledge gained from research and enquiry, using a variety of methods.', NULL, 44, 0, 1, 0, 0),
(138, 'S3.1a', 'Develop lifelong learning strategies through integration of the principles of learning.', NULL, 45, 0, 1, 0, 0),
(139, 'S3.1b', 'Self-assess learning critically, in congruence with others'' assessment, and address prioritized learning issues.', NULL, 45, 1, 1, 0, 0),
(140, 'S3.1c', 'Ask effective learning questions and solve problems appropriately.', NULL, 45, 2, 1, 0, 0),
(141, 'S3.1d', 'Consult multiple sources of information.', NULL, 45, 3, 1, 0, 0),
(142, 'S3.1e', 'Employ a variety of learning methodologies.', NULL, 45, 4, 1, 0, 0),
(143, 'S3.1f', 'Learn with and enhance the learning of others through communities of practice.', NULL, 45, 5, 1, 0, 0),
(144, 'S3.1g', 'Employ information technology (informatics) in learning, including, in clerkship, access to patient record data and other technologies.', NULL, 45, 6, 1, 0, 0),
(145, 'CM1.1a', 'Apply the skills that develop positive therapeutic relationships with patients and their families, characterized by understanding, trust, respect, honesty and empathy.', NULL, 46, 0, 1, 0, 0),
(146, 'CM1.1b', 'Respect patient confidentiality, privacy and autonomy.', NULL, 46, 1, 1, 0, 0),
(147, 'CM1.1c', 'Listen effectively and be aware of and responsive to nonverbal cues.', NULL, 46, 2, 1, 0, 0),
(148, 'CM1.1d', 'Communicate effectively with individuals regardless of their social, cultural or ethnic backgrounds, or disabilities.', NULL, 46, 3, 1, 0, 0),
(149, 'CM1.1e', 'Effectively facilitate a structured clinical encounter.', NULL, 46, 4, 1, 0, 0),
(150, 'CM1.2a', 'Gather information about a disease, but also about a patient''s beliefs, concerns, expectations and illness experience.', NULL, 47, 0, 1, 0, 0),
(151, 'CM1.2b', 'Seek out and synthesize relevant information from other sources, such as a patient''s family, caregivers and other professionals.', NULL, 47, 1, 1, 0, 0),
(152, 'CM1.3a', 'Provide accurate information to a patient and family, colleagues and other professionals in a clear, non-judgmental, and understandable manner.', NULL, 48, 0, 1, 0, 0),
(153, 'CM1.3b', 'Maintain clear, accurate and appropriate records of clinical encounters and plans.', NULL, 48, 1, 1, 0, 0),
(154, 'CM1.3c', 'Effectively present verbal reports of clinical encounters and plans.', NULL, 48, 2, 1, 0, 0),
(155, 'CM1.4a', 'Effectively identify and explore problems to be addressed from a patient encounter, including the patient''s context, responses, concerns and preferences.', NULL, 49, 0, 1, 0, 0),
(156, 'CM1.4b', 'Respect diversity and difference, including but not limited to the impact of gender, religion and cultural beliefs on decision making.', NULL, 49, 1, 1, 0, 0),
(157, 'CM1.4c', 'Encourage discussion, questions and interaction in the encounter.', NULL, 49, 2, 1, 0, 0),
(158, 'CM1.4d', 'Engage patients, families and relevant health professionals in shared decision making to develop a plan of care.', NULL, 49, 3, 1, 0, 0),
(159, 'CM1.4e', 'Effectively address challenging communication issues such as obtaining informed consent, delivering bad news, and addressing anger, confusion and misunderstanding.', NULL, 49, 4, 1, 0, 0),
(160, 'CL1.1a', 'Clearly describe and demonstrate their roles and responsibilities under law and other provisions, to other professionals within a variety of health care settings.', NULL, 50, 0, 1, 0, 0),
(161, 'CL1.1b', 'Recognize and respect the diversity of roles and responsibilities of other health care professionals in a variety of settings, noting  how these roles interact with their own.', NULL, 50, 1, 1, 0, 0),
(162, 'CL1.1c', 'Work with others to assess, plan, provide and integrate care for individual patients.', NULL, 50, 2, 1, 0, 0),
(163, 'CL1.1d', 'Respect team ethics, including confidentiality, resource allocation and professionalism.', NULL, 50, 3, 1, 0, 0),
(164, 'CL1.1e', 'Where appropriate, demonstrate leadership in a healthcare team.', NULL, 50, 4, 1, 0, 0),
(165, 'CL1.2a', 'Demonstrate a respectful attitude towards other colleagues and members of an interprofessional team members in a variety of settings.', NULL, 51, 0, 1, 0, 0),
(166, 'CL1.2b', 'Respect differences, and work to overcome misunderstandings and limitations in others, that may contribute to conflict.', NULL, 51, 1, 1, 0, 0),
(167, 'CL1.2c', 'Recognize one''s own differences, and work to overcome one''s own misunderstandings and limitations that may contribute to interprofessional conflict.', NULL, 51, 2, 1, 0, 0),
(168, 'CL1.2d', 'Reflect on successful interprofessional team function.', NULL, 51, 3, 1, 0, 0),
(169, 'CL1.3a', 'Identify the roles of patients and their family in prevention and management of illness.', NULL, 52, 0, 1, 0, 0),
(170, 'CL1.3b', 'Learn how to inform and involve the patient and family in decision-making and management plans.', NULL, 52, 1, 1, 0, 0),
(171, 'CL1.4a', 'Improve teaching through advice from experts in medical education.', NULL, 53, 0, 1, 0, 0),
(172, 'CL1.4b', 'Accept supervision and feedback.', NULL, 53, 1, 1, 0, 0),
(173, 'CL1.4c', 'Seek learning from others.', NULL, 53, 2, 1, 0, 0),
(174, 'A1.1a', 'Explain factors that influence health, disease, disability and access to care including non-biologic factors (cultural, psychological, sociologic, familial, economic, environmental, legal, political, spiritual needs and beliefs).', NULL, 54, 0, 1, 0, 0),
(175, 'A1.1b', 'Describe barriers to access to care and resources.', NULL, 54, 1, 1, 0, 0),
(176, 'A1.1c', 'Discuss health issues for special populations, including vulnerable or marginalized populations.', NULL, 54, 2, 1, 0, 0),
(177, 'A1.1d', 'Identify principles of health policy and implications.', NULL, 54, 3, 1, 0, 0),
(178, 'A1.1e', 'Describe health programs and interventions at the population level.', NULL, 54, 4, 1, 0, 0),
(179, 'A2.1a', 'Identify the role of and method of access to services of community resources.', NULL, 55, 0, 1, 0, 0),
(180, 'A2.1b', 'Describe appropriate methods of communication about community resources to and on behalf of patients.', NULL, 55, 1, 1, 0, 0),
(181, 'A2.1c', 'Locate and analyze a variety of health communities and community health networks in the local Kingston area and beyond.', NULL, 55, 2, 1, 0, 0),
(182, 'A2.2a', 'Describe the role and examples of physicians and medical associations in advocating collectively for health and patient safety.', NULL, 56, 0, 1, 0, 0),
(183, 'A2.2b', 'Analyze the ethical and professional issues inherent in health advocacy, including possible conflict between roles of gatekeeper and manager.', NULL, 56, 1, 1, 0, 0),
(184, 'M1.1a', 'Outline strategies for effective practice in a variety of health care settings, including their structure, finance and operation.', NULL, 57, 0, 1, 0, 0),
(185, 'M1.1b', 'Outline the common law and statutory provisions which govern practice and collaboration within hospital and other settings.', NULL, 57, 1, 1, 0, 0),
(186, 'M1.1c', 'Recognizes one''s own personal preferences and strengths and uses this knowledge in career decisions.', NULL, 57, 2, 1, 0, 0),
(187, 'M1.1d', 'Identify career paths within health care settings.', NULL, 57, 3, 1, 0, 0),
(188, 'M2.1a', 'Identify and balance personal and educational priorities to foster future balance between personal health and a sustainable practice.', NULL, 58, 0, 1, 0, 0),
(189, 'M2.1b', 'Practice personal and professional awareness, insight and acceptance of feedback and peer review;  participate in peer review.', NULL, 58, 1, 1, 0, 0),
(190, 'M2.1c', 'Implement plans to overcome barriers to health personal and professional behavior.', NULL, 58, 2, 1, 0, 0),
(191, 'M2.1d', 'Recognize and respond to other educational/professional colleagues in need of support.', NULL, 58, 3, 1, 0, 0),
(200, 'Clinical Learning Objectives', NULL, NULL, 0, 0, 1, 0, 0),
(201, 'Pain, lower limb', NULL, NULL, 200, 113, 1, 1257353646, 3499),
(202, 'Pain, upper limb', NULL, NULL, 200, 112, 1, 1257353646, 3499),
(203, 'Fracture/disl''n', NULL, NULL, 200, 111, 1, 1257353646, 3499),
(204, 'Scrotal pain', NULL, NULL, 200, 101, 1, 1257353646, 3499),
(205, 'Blood in urine', NULL, NULL, 200, 100, 1, 1257353646, 3499),
(206, 'Urinary obstruction/hesitancy', NULL, NULL, 200, 99, 1, 1257353646, 3499),
(207, 'Nausea/vomiting', NULL, NULL, 200, 98, 1, 1257353646, 3499),
(208, 'Hernia', NULL, NULL, 200, 97, 1, 1257353646, 3499),
(209, 'Abdominal injuries', NULL, NULL, 200, 96, 1, 1257353646, 3499),
(210, 'Chest injuries', NULL, NULL, 200, 95, 1, 1257353646, 3499),
(211, 'Breast disorders', NULL, NULL, 200, 94, 1, 1257353646, 3499),
(212, 'Anorectal pain', NULL, NULL, 200, 93, 1, 1257353646, 3499),
(213, 'Blood, GI tract', NULL, NULL, 200, 92, 1, 1257353646, 3499),
(214, 'Abdominal distension', NULL, NULL, 200, 91, 1, 1257353646, 3499),
(215, 'Subs abuse/addic/wdraw', NULL, NULL, 200, 90, 1, 1257353646, 3499),
(216, 'Abdo pain - acute', NULL, NULL, 200, 89, 1, 1257353646, 3499),
(217, 'Psychosis/disord thoughts', NULL, NULL, 200, 88, 1, 1257353646, 3499),
(218, 'Personality disorders', NULL, NULL, 200, 87, 1, 1257353646, 3499),
(219, 'Panic/anxiety', NULL, NULL, 200, 86, 1, 1257353646, 3499),
(221, 'Mood disorders', NULL, NULL, 200, 84, 1, 1257353646, 3499),
(222, 'XR-Wrist/hand', NULL, NULL, 200, 83, 1, 1257353646, 3499),
(223, 'XR-Chest', NULL, NULL, 200, 82, 1, 1257353646, 3499),
(224, 'XR-Hip/pelvis', NULL, NULL, 200, 81, 1, 1257353646, 3499),
(225, 'XR-Ankle/foot', NULL, NULL, 200, 80, 1, 1257353646, 3499),
(226, 'Skin ulcers-tumors', NULL, NULL, 200, 79, 1, 1257353646, 3499),
(228, 'Skin wound', NULL, NULL, 200, 77, 1, 1257353646, 3499),
(233, 'Dyspnea, acute', NULL, NULL, 200, 72, 1, 1257353646, 3499),
(234, 'Infant/child nutrition', NULL, NULL, 200, 71, 1, 1257353646, 3499),
(235, 'Newborn assessment', NULL, NULL, 200, 70, 1, 1257353646, 3499),
(236, 'Rash,child', NULL, NULL, 200, 69, 1, 1257353646, 3499),
(237, 'Ped naus/vom/diarh', NULL, NULL, 200, 68, 1, 1257353646, 3499),
(238, 'Ped EM''s-acutely ill', NULL, NULL, 200, 67, 1, 1257353646, 3499),
(239, 'Ped dysp/resp dstres', NULL, NULL, 200, 66, 1, 1257353646, 3499),
(240, 'Ped constipation', NULL, NULL, 200, 65, 1, 1257353646, 3499),
(241, 'Fever in a child', NULL, NULL, 200, 64, 1, 1257353646, 3499),
(242, 'Ear pain', NULL, NULL, 200, 63, 1, 1257353646, 3499),
(257, 'Prolapse', NULL, NULL, 200, 48, 1, 1257353646, 3499),
(258, 'Vaginal bleeding, abn', NULL, NULL, 200, 47, 1, 1257353646, 3499),
(259, 'Postpartum, normal', NULL, NULL, 200, 46, 1, 1257353646, 3499),
(260, 'Labour, normal', NULL, NULL, 200, 45, 1, 1257353646, 3499),
(261, 'Labour, abnormal', NULL, NULL, 200, 44, 1, 1257353646, 3499),
(262, 'Infertility', NULL, NULL, 200, 43, 1, 1257353646, 3499),
(263, 'Incontinence-urine', NULL, NULL, 200, 42, 1, 1257353646, 3499),
(264, 'Hypertension, preg', NULL, NULL, 200, 41, 1, 1257353646, 3499),
(265, 'Dysmenorrhea', NULL, NULL, 200, 40, 1, 1257353646, 3499),
(266, 'Contraception', NULL, NULL, 200, 39, 1, 1257353646, 3499),
(267, 'Antepartum care', NULL, NULL, 200, 38, 1, 1257353646, 3499),
(268, 'Weakness', NULL, NULL, 200, 37, 1, 1257353646, 3499),
(269, 'Sodium-abn', NULL, NULL, 200, 36, 1, 1257353646, 3499),
(270, 'Renal failure', NULL, NULL, 200, 35, 1, 1257353646, 3499),
(271, 'Potassium-abn', NULL, NULL, 200, 34, 1, 1257353646, 3499),
(272, 'Murmur', NULL, NULL, 200, 33, 1, 1257353646, 3499),
(273, 'Joint pain, poly', NULL, NULL, 200, 32, 1, 1257353646, 3499),
(274, 'Impaired LOC (coma)', NULL, NULL, 200, 31, 1, 1257353646, 3499),
(275, 'Hypotension', NULL, NULL, 200, 30, 1, 1257353646, 3499),
(276, 'Hypertension', NULL, NULL, 200, 29, 1, 1257353646, 3499),
(277, 'H+ concentratn, abn', NULL, NULL, 200, 28, 1, 1257353646, 3499),
(278, 'Fever', NULL, NULL, 200, 27, 1, 1257353646, 3499),
(279, 'Edema', NULL, NULL, 200, 26, 1, 1257353646, 3499),
(280, 'Dyspnea-chronic', NULL, NULL, 200, 25, 1, 1257353646, 3499),
(281, 'Diabetes mellitus', NULL, NULL, 200, 24, 1, 1257353646, 3499),
(282, 'Dementia', NULL, NULL, 200, 23, 1, 1257353646, 3499),
(283, 'Delerium/confusion', NULL, NULL, 200, 22, 1, 1257353646, 3499),
(284, 'Cough', NULL, NULL, 200, 21, 1, 1257353646, 3499),
(286, 'Anemia', NULL, NULL, 200, 19, 1, 1257353646, 3499),
(287, 'Chest pain', NULL, NULL, 200, 18, 1, 1257353646, 3499),
(288, 'Abdo pain-chronic', NULL, NULL, 200, 17, 1, 1257353646, 3499),
(289, 'Wk-rel''td health iss', NULL, NULL, 200, 16, 1, 1257353646, 3499),
(290, 'Weight loss/gain', NULL, NULL, 200, 15, 1, 1257353646, 3499),
(291, 'URTI', NULL, NULL, 200, 14, 1, 1257353646, 3499),
(292, 'Sore throat', NULL, NULL, 200, 13, 1, 1257353646, 3499),
(293, 'Skin rash', NULL, NULL, 200, 12, 1, 1257353646, 3499),
(294, 'Pregnancy', NULL, NULL, 200, 11, 1, 1257353646, 3499),
(295, 'Periodic health exam', NULL, NULL, 200, 10, 1, 1257353646, 3499),
(296, 'Pain, spinal', NULL, NULL, 200, 9, 1, 1257353646, 3499),
(299, 'Headache', NULL, NULL, 200, 6, 1, 1257353646, 3499),
(300, 'Fatigue', NULL, NULL, 200, 5, 1, 1257353646, 3499),
(303, 'Dysuria/pyuria', NULL, NULL, 200, 2, 1, 1257353646, 3499),
(304, 'Fracture/dislocation', NULL, NULL, 200, 114, 1, 1261414735, 3499),
(305, 'Pain', NULL, NULL, 200, 115, 1, 1261414735, 3499),
(306, 'Preop Assess - anesthesiology', NULL, NULL, 200, 116, 1, 1261414735, 3499),
(307, 'Preop Assess - surgery', NULL, NULL, 200, 117, 1, 1261414735, 3499),
(308, 'Pain - spinal', NULL, NULL, 200, 118, 1, 1261414735, 3499),
(309, 'MCC Presentations', NULL, NULL, 0, 0, 1, 1265296358, 3499),
(310, 'Abdominal Distension', 'Abdominal distention is common and may indicate the presence of serious intra-abdominal or systemic disease.', '1-E', 309, 1, 1, 1271174177, 3499),
(311, 'Abdominal Mass', 'If hernias are excluded, most other abdominal masses represent a significant underlying disease that requires complete investigation.', '2-E', 309, 2, 1, 1271174177, 3499),
(312, 'Adrenal Mass', 'Adrenal masses are at times found incidentally after CT, MRI, or ultrasound examination done for unrelated reasons.  The incidence is about 3.5 % (almost 10 % of autopsies).', '2-1-E', 311, 1, 1, 1271174178, 3499),
(313, 'Hepatomegaly', 'True hepatomegaly (enlargement of the liver with a span greater than 14 cm in adult males and greater than 12 cm in adult females) is an uncommon clinical presentation, but is important to recognize in light of potentially serious causal conditions.', '2-2-E', 311, 2, 1, 1271174178, 3499),
(314, 'Hernia (abdominal Wall And Groin)', 'A hernia is defined as an abnormal protrusion of part of a viscus through its containing wall.  Hernias, in particular inguinal hernias, are very common, and thus, herniorrphaphy is a very common surgical intervention.', '2-4-E', 311, 3, 1, 1271174178, 3499),
(315, 'Splenomegaly', 'Splenomegaly, an enlarged spleen detected on physical examination by palpitation or percussion at Castell''s point, is relatively uncommon.  However, it is often associated with serious underlying pathology.', '2-3-E', 311, 4, 1, 1271174178, 3499),
(316, 'Abdominal Pain (children)', 'Abdominal pain is a common complaint in children.  While the symptoms may result from serious abdominal pathology, in a large proportion of cases, an identifiable organic cause is not found.  The causes are often age dependent.', '3-1-E', 309, 3, 1, 1271174178, 3499),
(317, 'Abdominal Pain, Acute ', 'Abdominal pain may result from intra-abdominal inflammation or disorders of the abdominal wall.  Pain may also be referred from sources outside the abdomen such as retroperitoneal processes as well as intra-thoracic processes.  Thorough clinical evaluation is the most important "test" in the diagnosis of abdominal pain.', '3-2-E', 309, 4, 1, 1271174178, 3499),
(318, 'Abdominal Pain, Anorectal', 'While almost all causes of anal pain are treatable, some can be destructive locally if left untreated.', '3-4-E', 309, 5, 1, 1271174178, 3499),
(319, 'Abdominal Pain, Chronic', 'Chronic and recurrent abdominal pain, including heartburn or dyspepsia is a common symptom (20 - 40 % of adults) with an extensive differential diagnosis and heterogeneous pathophysiology.  The history and physical examination frequently differentiate between functional and more serious underlying diseases.', '3-3-E', 309, 6, 1, 1271174178, 3499),
(320, 'Allergic Reactions/food Allergies Intolerance/atopy', 'Allergic reactions are considered together despite the fact that they exhibit a variety of clinical responses and are considered separately under the appropriate presentation.  The rationale for considering them together is that in some patients with a single response (e.g., atopic dermatitis), other atopic disorders such as asthma or allergic rhinitis may occur at other times.  Moreover, 50% of patients with atopic dermatitis report a family history of respiratory atopy. ', '4-E', 309, 7, 1, 1271174178, 3499),
(321, 'Attention Deficit/hyperactivity Disorder (adhd)/learning Dis', 'Family physicians at times are the initial caregivers to be confronted by developmental and behavioural problems of childhood and adolescence (5 - 10% of school-aged population).  Lengthy waiting lists for specialists together with the urgent plight of patients often force primary-care physicians to care for these children.', '5-E', 309, 8, 1, 1271174178, 3499),
(322, 'Blood From Gastrointestinal Tract', 'Both upper and lower gastrointestinal bleeding are common and may be life-threatening.  Upper intestinal bleeding usually presents with hematemesis (blood or coffee-ground material) and/or melena (black, tarry stools).  Lower intestinal bleeding usually manifests itself as hematochezia (bright red blood or dark red blood or clots per rectum).  Unfortunately, this difference is not consistent. Melena may be seen in patients with colorectal or small bowel bleeding, and hematochezia may be seen with massive upper gastrointestinal bleeding.  Occult bleeding from the gastrointestinal tract may also be identified by positive stool for occult blood or the presence of iron deficiency anemia.', '6-E', 309, 9, 1, 1271174178, 3499),
(323, 'Blood From Gastrointestinal Tract, Lower/hematochezia', 'Although lower gastrointestinal bleeding (blood originating distal to ligament of Treitz) or hematochezia is less common than upper (20% vs. 80%), it is associated with 10 -20% morbidity and mortality since it usually occurs in the elderly.  Early identification of colorectal cancer is important in preventing cancer-related morbidity and mortality (colorectal cancer is second only to lung cancer as a cause of cancer-related death). ', '6-2-E', 322, 1, 1, 1271174178, 3499),
(324, 'Blood From Gastrointestinal Tract, Upper/hematemesis', 'Although at times self-limited, upper GI bleeding always warrants careful and urgent evaluation, investigation, and treatment.  The urgency of treatment and the nature of resuscitation depend on the amount of blood loss, the likely cause of the bleeding, and the underlying health of the patient.', '6-1-E', 322, 2, 1, 1271174178, 3499),
(325, 'Blood In Sputum (hemoptysis/prevention Of Lung Cancer)', 'Expectoration of blood can range from blood streaking of sputum to massive hemoptysis (&gt;200 ml/d) that may be acutely life threatening.  Bleeding usually starts and stops unpredictably, but under certain circumstances may require immediate establishment of an airway and control of the bleeding.', '7-E', 309, 10, 1, 1271174178, 3499),
(326, 'Blood In Urine (hematuria)', 'Urinalysis is a screening procedure for insurance and routine examinations.  Persistent hematuria implies the presence of conditions ranging from benign to malignant.', '8-E', 309, 11, 1, 1271174178, 3499),
(327, 'Hypertension', 'Hypertension is a common condition that usually presents with a modest elevation in either systolic or diastolic blood pressure.  Under such circumstances, the diagnosis of hypertension is made only after three separate properly measured blood pressures.  Appropriate investigation and management of hypertension is expected to improve health outcomes.', '9-1-E', 309, 12, 1, 1271174178, 3499),
(328, 'Hypertension In Childhood', 'The prevalence of hypertension in children is&lt;1 %, but often results from identifiable causes (usually renal or vascular).  Consequently, vigorous investigation is warranted.', '9-1-1-E', 327, 1, 1, 1271174178, 3499),
(329, 'Hypertension In The Elderly', 'Elderly patients (&gt;65 years) have hypertension much more commonly than younger patients do, especially systolic hypertension.  The prevalence of hypertension among the elderly may reach 60 -80 %.', '9-1-2-E', 327, 2, 1, 1271174178, 3499),
(330, 'Malignant Hypertension', 'Malignant hypertension and hypertensive encephalopathies are two life-threatening syndromes caused by marked elevation in blood pressure.', '9-1-3-E', 327, 3, 1, 1271174178, 3499),
(331, 'Pregnancy Associated Hypertension', 'Ten to 20 % of pregnancies are associated with hypertension.  Chronic hypertension complicates&lt;5%, preeclampsia occurs in slightly&gt;6%, and gestational hypertension arises in 6% of pregnant women.  Preeclampsia is potentially serious, but can be managed by treatment of hypertension and ''cured'' by delivery of the fetus.', '9-1-4-E', 327, 4, 1, 1271174178, 3499),
(332, 'Hypotension/shock', 'All physicians must deal with life-threatening emergencies.  Regardless of underlying cause, certain general measures are usually indicated (investigations and therapeutic interventions) that can be life saving.', '9-2-E', 309, 13, 1, 1271174178, 3499),
(333, 'Anaphylaxis', 'Anaphylaxis causes about 50 fatalities per year, and occurs in 1/5000-hospital admissions in Canada.  Children most commonly are allergic to foods.', '9-2-1-E', 332, 1, 1, 1271174178, 3499),
(334, 'Breast Lump/screening', 'Complaints of breast lumps are common, and breast cancer is the most common cancer in women.  Thus, all breast complaints need to be pursued to resolution.  Screening women 50 - 69 years with annual mammography improves survival. ', '10-1-E', 309, 14, 1, 1271174178, 3499),
(335, 'Galactorrhea/discharge', 'Although noticeable breast secretions are normal in&gt;50 % of reproductive age women, spontaneous persistent galactorrhea may reflect underlying disease and requires investigation.', '10-2-E', 309, 15, 1, 1271174178, 3499),
(336, 'Gynecomastia', 'Although a definite etiology for gynecomastia is found in&lt;50% of patients, a careful drug history is important so that a treatable cause is detected.  The underlying feature is an increased estrogen to androgen ratio.', '10-3-E', 309, 16, 1, 1271174178, 3499),
(337, 'Burns', 'Burns are relatively common and range from minor cutaneous wounds to major life-threatening traumas.  An understanding of the patho-physiology and treatment of burns and the metabolic and wound healing response will enable physicians to effectively assess and treat these injuries.', '11-E', 309, 17, 1, 1271174178, 3499),
(338, 'Hypercalcemia', 'Hypercalcemia may be associated with an excess of calcium in both extracellular fluid and bone (e.g., increased intestinal absorption), or with a localised or generalised deficit of calcium in bone (e.g., increased bone resorption).  This differentiation by physicians is important for both diagnostic and management reasons.', '12-1-E', 309, 18, 1, 1271174178, 3499),
(339, 'Hyperphosphatemia', 'Acute severe hyperphosphatemia can be life threatening.', '12-4-E', 309, 19, 1, 1271174178, 3499),
(340, 'Hypocalcemia', 'Tetany, seizures, and papilledema may occur in patients who develop hypocalcemia acutely.', '12-2-E', 309, 20, 1, 1271174178, 3499),
(341, 'Hypophosphatemia/fanconi Syndrome', 'Of hospitalised patients, 10-15% develop hypophosphatemia, and a small proportion have sufficiently profound depletion to lead to complications (e.g., rhabdomyolysis).', '12-3-E', 309, 21, 1, 1271174178, 3499),
(342, 'Cardiac Arrest', 'All physicians are expected to attempt resuscitation of an individual with cardiac arrest. In the community, cardiac arrest most commonly is caused by ventricular fibrillation. However, heart rhythm at clinical presentation in many cases is unknown.  As a consequence, operational criteria for cardiac arrest do not rely on heart rhythm but focus on the presumed sudden pulse-less condition and the absence of evidence of a non-cardiac condition as the cause of the arrest.', '13-E', 309, 22, 1, 1271174178, 3499),
(343, 'Chest Discomfort/pain/angina Pectoris', 'Chest pain in the primary care setting, although potentially severe and disabling, is more commonly of benign etiology.  The correct diagnosis requires a cost-effective approach.  Although coronary heart disease primarily occurs in patients over the age of 40, younger men and women can be affected (it is estimated that advanced lesions are present in 20% of men and 8% of women aged 30 to 34).  Physicians must recognise the manifestations of coronary artery disease and assess coronary risk factors.  Modifications of risk factors should be recommended as necessary.', '14-E', 309, 23, 1, 1271174178, 3499),
(344, 'Bleeding Tendency/bruising', 'A bleeding tendency (excessive, delayed, or spontaneous bleeding) may signify serious underlying disease.  In children or infants, suspicion of a bleeding disorder may be a family history of susceptibility to bleeding.  An organised approach to this problem is essential.  Urgent management may be required.', '15-1-E', 309, 24, 1, 1271174178, 3499),
(345, 'Hypercoagulable State', 'Patients may present with venous thrombosis and on occasion with pulmonary embolism. A risk factor for thrombosis can now be identified in over 80% of such patients.', '15-2-E', 309, 25, 1, 1271174178, 3499),
(346, ' Adult Constipation', 'Constipation is common in Western society, but frequency depends on patient and physician''s definition of the problem.  One definition is straining, incomplete evacuation, sense of blockade, manual maneuvers, and hard stools at least 25% of the time along with&lt;3 stools/week for at least 12 weeks (need not be consecutive).  The prevalence of chronic constipation rises with age. In patients&gt;65 years, almost 1/3 complain of constipation.', '16-1-E', 309, 26, 1, 1271174178, 3499),
(347, 'Pediatric Constipation', 'Constipation is a common problem in children.  It is important to differentiate functional from organic causes in order to develop appropriate management plans.', '16-2-E', 309, 27, 1, 1271174178, 3499),
(348, 'Contraception', 'Ideally, the prevention of an unwanted pregnancy should be directed at education of patients, male and female, preferably before first sexual contact.  Counselling patients about which method to use, how, and when is a must for anyone involved in health care.', '17-E', 309, 28, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` (`objective_id`, `objective_name`, `objective_description`, `objective_code`, `objective_parent`, `objective_order`, `objective_active`, `updated_date`, `updated_by`) VALUES
(349, 'Cough', 'Chronic cough is the fifth most common symptom for which patients seek medical advice.  Assessment of chronic cough must be thorough.  Patients with benign causes for their cough (gastro-esophageal reflux, post-nasal drip, two of the commonest causes) can often be effectively and easily managed.  Patients with more serious causes for their cough (e.g., asthma, the other common cause of chronic cough) require full investigation and management is more complex.', '18-E', 309, 29, 1, 1271174178, 3499),
(350, 'Cyanosis/hypoxemia/hypoxia', 'Cyanosis is the physical sign indicative of excessive concentration of reduced hemoglobin in the blood, but at times is difficult to detect (it must be sought carefully, under proper lighting conditions).  Hypoxemia (low partial pressure of oxygen in blood), when detected, may be reversible with oxygen therapy after which the underlying cause requires diagnosis and management.', '19-E', 309, 30, 1, 1271174178, 3499),
(351, 'Cyanosis/hypoxemia/hypoxia In Children', 'Evaluation of the patient with cyanosis depends on the age of the child.  It is an ominous finding and differentiation between peripheral and central is essential in order to mount appropriate management.', '19-1-E', 350, 1, 1, 1271174178, 3499),
(352, 'Deformity/limp/pain In Lower Extremity, Child', '''Limp'' is a bumpy, rough, or strenuous way of walking, usually caused by weakness, pain, or deformity.  Although usually caused by benign conditions, at times it may be life or limb threatening. ', '20-E', 309, 31, 1, 1271174178, 3499),
(353, 'Development Disorder/developmental Delay', 'Providing that normal development and behavior is readily recognized, primary care physicians will at times be the first physicians in a position to assess development in an infant, and recognize abnormal delay and/or atypical development.  Developmental surveillance and direct developmental screening of children, especially those with predisposing risks, will then be an integral part of health care.', '21-E', 309, 32, 1, 1271174178, 3499),
(354, 'Acute Diarrhea', 'Diarrheal diseases are extremely common worldwide, and even in North America morbidity and mortality is significant.  One of the challenges for a physician faced with a patient with acute diarrhea is to know when to investigate and initiate treatment and when to simply wait for a self-limiting condition to run its course.', '22-1-E', 309, 33, 1, 1271174178, 3499),
(355, 'Chronic Diarrhea', 'Chronic diarrhea is a decrease in fecal consistency lasting for 4 or more weeks.  It affects about 5% of the population.', '22-2-E', 309, 34, 1, 1271174178, 3499),
(356, 'Pediatric Diarrhea', 'Diarrhea is defined as frequent, watery stools and is a common problem in infants and children.  In most cases, it is mild and self-limited, but the potential for hypovolemia (reduced effective arterial/extracellular volume) and dehydration (water loss in excess of solute) leading to electrolyte abnormalities is great.  These complications in turn may lead to significant morbidity or even mortality.', '22-3-E', 309, 35, 1, 1271174178, 3499),
(357, 'Diplopia', 'Diplopia is the major symptom associated with dysfunction of extra-ocular muscles or abnormalities of the motor nerves innervating these muscles.  Monocular diplopia is almost always indicative of relatively benign optical problems whereas binocular diplopia is due to ocular misalignment.  Once restrictive disease or myasthenia gravis is excluded, the major cause of binocular diplopia is a cranial nerve lesion.  Careful clinical assessment will enable diagnosis in most, and suggest appropriate investigation and management.', '23-E', 309, 36, 1, 1271174178, 3499),
(358, 'Dizziness/vertigo', '"Dizziness" is a common but imprecise complaint.  Physicians need to determine whether it refers to true vertigo, ''dizziness'', disequilibrium, or pre-syncope/ lightheadedness. ', '24-E', 309, 37, 1, 1271174178, 3499),
(359, 'Dying Patient/bereavement', 'Physicians are frequently faced with patients dying from incurable or untreatable diseases. In such circumstances, the important role of the physician is to alleviate any suffering by the patient and to provide comfort and compassion to both patient and family. ', '25-E', 309, 38, 1, 1271174178, 3499),
(360, 'Dysphagia/difficulty Swallowing', 'Dysphagia should be regarded as a danger signal that indicates the need to evaluate and define the cause of the swallowing difficulty and thereafter initiate or refer for treatment.', '26-E', 309, 39, 1, 1271174178, 3499),
(361, 'Dyspnea', 'Dyspnea is common and distresses millions of patients with pulmonary disease and myocardial dysfunction.  Assessment of the manner dyspnea is described by patients suggests that their description may provide insight into the underlying pathophysiology of the disease.', '27-E', 309, 40, 1, 1271174178, 3499),
(362, 'Acute Dyspnea (minutes To Hours)', 'Shortness of breath occurring over minutes to hours is caused by a relatively small number of conditions.  Attention to clinical information and consideration of these conditions can lead to an accurate diagnosis.  Diagnosis permits initiation of therapy that can limit associated morbidity and mortality.', '27-1-E', 361, 1, 1, 1271174178, 3499),
(363, 'Chronic Dyspnea (weeks To Months)', 'Since patients with acute dyspnea require more immediate evaluation and treatment, it is important to differentiate them from those with chronic dyspnea.  However, chronic dyspnea etiology may be harder to elucidate.  Usually patients have cardio-pulmonary disease, but symptoms may be out of proportion to the demonstrable impairment.', '27-2-E', 361, 2, 1, 1271174178, 3499),
(364, 'Pediatric Dyspnea/respiratory Distress', 'After fever, respiratory distress is one of the commonest pediatric emergency complaints.', '27-3-E', 361, 3, 1, 1271174178, 3499),
(365, 'Ear Pain', 'The cause of ear pain is often otologic, but it may be referred.  In febrile young children, who most frequently are affected by ear infections, if unable to describe the pain, a good otologic exam is crucial. (see also <a href="objectives.pl?lang=english&amp;loc=obj&amp;id=40-E" title="Presentation 40-E">Hearing Loss/Deafness)', '28-E', 309, 41, 1, 1271174178, 3499),
(366, ' Generalized Edema', 'Patients frequently complain of swelling.  On closer scrutiny, such swelling often represents expansion of the interstitial fluid volume.  At times the swelling may be caused by relatively benign conditions, but at times serious underlying diseases may be present.', '29-1-E', 309, 42, 1, 1271174178, 3499),
(367, ' Unilateral/local Edema', 'Over 90 % of cases of acute pulmonary embolism are due to emboli emanating from the proximal veins of the lower extremities.', '29-2-E', 309, 43, 1, 1271174178, 3499),
(368, 'Eye Redness', 'Red eye is a very common complaint.  Despite the rather lengthy list of causal conditions, three problems make up the vast majority of causes: conjunctivitis (most common), foreign body, and iritis.  Other types of injury are relatively less common, but important because excessive manipulation may cause further damage or even loss of vision.', '30-E', 309, 44, 1, 1271174178, 3499),
(369, 'Failure To Thrive, Elderly ', 'Failure to thrive for an elderly person means the loss of energy, vigor and/or weight often accompanied by a decline in the ability to function and at times associated with depression.', '31-1-E', 309, 45, 1, 1271174178, 3499),
(370, 'Failure To Thrive, Infant/child', 'Failure to thrive is a phrase that describes the occurrence of growth failure in either height or weight in childhood.  Since failure to thrive is attributed to children&lt;2 years whose weight is below the 5th percentile for age on more than one occasion, it is essential to differentiate normal from the abnormal growth patterns.', '31-2-E', 309, 46, 1, 1271174178, 3499),
(371, 'Falls', 'Falls are common (&gt;1/3 of people over 65 years; 80% among those with?4 risk factors) and 1 in 10 are associated with serious injury such as hip fracture, subdural hematoma, or head injury.  Many are preventable.  Interventions that prevent falls and their sequelae delay or reduce the frequency of nursing home admissions.', '32-E', 309, 47, 1, 1271174178, 3499),
(372, 'Fatigue ', 'In a primary care setting, 20-30% of patients will report significant fatigue (usually not associated with organic cause).  Fatigue&lt;1 month is ''recent'';&gt;6 months, it is ''chronic''.', '33-E', 309, 48, 1, 1271174178, 3499),
(373, 'Fractures/dislocations ', 'Fractures and dislocations are common problems at any age and are related to high-energy injuries (e.g., motor accidents, sport injuries) or, at the other end of the spectrum, simple injuries such as falls or non-accidental injuries.  They require initial management by primary care physicians with referral for difficult cases to specialists.', '34-E', 309, 49, 1, 1271174178, 3499),
(374, 'Gait Disturbances/ataxia ', 'Abnormalities of gait can result from disorders affecting several levels of the nervous system and the type of abnormality observed clinically often indicates the site affected.', '35-E', 309, 50, 1, 1271174178, 3499),
(375, 'Genetic Concerns', 'Genetics have increased our understanding of the origin of many diseases.  Parents with a family history of birth defects or a previously affected child need to know that they are at higher risk of having a baby with an anomaly.  Not infrequently, patients considering becoming parents seek medical advice because of concerns they might have.  Primary care physicians must provide counseling about risk factors such as maternal age, illness, drug use, exposure to infectious or environmental agents, etc. and if necessary referral if further evaluation is necessary.', '36-E', 309, 51, 1, 1271174178, 3499),
(376, 'Ambiguous Genitalia', 'Genetic males with 46, XY genotype but having impaired androgen sensitivity of varying severity may present with features that range from phenotypic females to ''normal'' males with only minor defects in masculinization or infertility.  Primary care physicians may be called upon to determine the nature of the problem.', '36-1-E', 375, 1, 1, 1271174178, 3499),
(377, 'Dysmorphic Features', 'Three out of 100 infants are born with a genetic disorder or congenital defect.  Many of these are associated with long-term disability, making early detection and identification vital.  Although early involvement of genetic specialists in the care of such children is prudent, primary care physicians are at times required to contribute immediate care, and subsequently assist with long term management of suctients.', '36-2-E', 375, 2, 1, 1271174178, 3499),
(378, 'Hyperglycemia/diabetes Mellitus', 'Diabetes mellitus is a very common disorder associated with a relative or absolute impairment of insulin secretion together with varying degrees of peripheral resistance to the action of insulin.  The morbidity and mortality associated with diabetic complications may be reduced by preventive measures.  Intensive glycemic control will reduce neonatal complications and reduce congenital malformations in pregnancy diabetes.', '37-1-E', 309, 52, 1, 1271174178, 3499),
(379, 'Hypoglycemia', 'Maintenance of the blood sugar within normal limits is essential for health.  In the short-term, hypoglycemia is much more dangerous than hyperglycemia.  Fortunately, it is an uncommon clinical problem outside of therapy for diabetes mellitus. ', '37-2-E', 309, 53, 1, 1271174178, 3499),
(380, 'Alopecia ', 'Although in themselves hair changes may be innocuous, they can be psychologically unbearable.  Frequently they may provide significant diagnostic hints of underlying disease.', '38-1-E', 309, 54, 1, 1271174178, 3499),
(381, 'Nail Complaints ', 'Nail disorders (toenails more than fingernails), especially ingrown, infected, and painful nails, are common conditions.  Local nail problems may be acute or chronic.  Relatively simple treatment can prevent or alleviate symptoms.  Although in themselves nail changes may be innocuous, they frequently provide significant diagnostic hints of underlying disease.', '38-2-E', 309, 55, 1, 1271174178, 3499),
(382, 'Headache', 'The differentiation of patients with headaches due to serious or life-threatening conditions from those with benign primary headache disorders (e.g., tension headaches or migraine) is an important diagnostic challenge.', '39-E', 309, 56, 1, 1271174178, 3499),
(383, 'Hearing Loss/deafness ', 'Many hearing loss causes are short-lived, treatable, and/or preventable.  In the elderly, more permanent sensorineural loss occurs.  In pediatrics, otitis media accounts for 25% of office visits.  Adults/older children have otitis less commonly, but may be affected by sequelae of otitis.', '40-E', 309, 57, 1, 1271174178, 3499),
(384, 'Hemiplegia/hemisensory Loss +/- Aphasia', 'Hemiplegia/hemisensory loss results from an upper motor neuron lesion above the mid-cervical spinal cord.  The concomitant finding of aphasia is diagnostic of a dominant cerebral hemisphere lesion.  Acute hemiplegia generally heralds the onset of serious medical conditions, usually of vascular origin, that at times are effectively treated by advanced medical and surgical techniques.</p>\r\n<p>If the sudden onset of focal neurologic symptoms and/or signs lasts&lt;24 hours, presumably it was caused by a transient decrease in blood supply rendering the brain ischemic but with blood flow restoration timely enough to avoid infarction.  This definition of transient ischemic attacks (TIA) is now recognized to be inadequate.  ', '41-E', 309, 58, 1, 1271174178, 3499),
(385, 'Anemia', 'The diagnosis in a patient with anemia can be complex.  An unfocused or unstructured investigation of anemia can be costly and inefficient.  Simple tests may provide important information.  Anemia may be the sole manifestation of serious medical disease.', '42-1-E', 309, 59, 1, 1271174178, 3499),
(386, 'Polycythemia/elevated Hemoglobin', 'The reason for evaluating patients with elevated hemoglobin levels (male 185 g/L, female 165 g/L) is to ascertain the presence or absence of polycythemia vera first, and subsequently to differentiate between the various causes of secondary erythrocytosis.', '42-2-E', 309, 60, 1, 1271174178, 3499),
(387, 'Hirsutism/virilization', 'Hirsutism, terminal body hair where unusual (face, chest, abdomen, back), is a common problem, particularly in dark-haired, darkly pigmented, white women.  However, if accompanied by virilization, then a full diagnostic evaluation is essential because it is androgen-dependent.  Hypertrichosis on the other hand is a rare condition usually caused by drugs or systemic illness.', '43-E', 309, 61, 1, 1271174178, 3499),
(388, 'Hoarseness/dysphonia/speech And Language Abnormalities', 'Patients with impairment in comprehension and/or use of the form, content, or function of language are said to have a language disorder.  Those who have correct word choice and syntax but have speech disorders may have an articulation disorder.  Almost any change in voice quality may be described as hoarseness.  However, if it lasts more than 2 weeks, especially in patients who use alcohol or tobacco, it needs to be evaluated.', '44-E', 309, 62, 1, 1271174178, 3499),
(389, 'Hydrogen Ion Concentration Abnormal, Serum', 'Major adverse consequences may occur with severe acidemia and alkalemia despite absence of specific symptoms.  The diagnosis depends on the clinical setting and laboratory studies.  It is crucial to distinguish acidemia due to metabolic causes from that due to respiratory causes; especially important is detecting the presence of both.  Management of the underlying causes and not simply of the change in [H+] is essential.', '45-E', 309, 63, 1, 1271174178, 3499),
(390, 'Infertility', 'Infertility, meaning the inability to conceive after one year of intercourse without contraception, affects about 15% of couples.  Both partners must be investigated; male-associated factors account for approximately half of infertility problems.  Although current emphasis is on treatment technologies, it is important to consider first the cause of the infertility and tailor the treatment accordingly.', '46-E', 309, 64, 1, 1271174178, 3499),
(391, 'Incontinence, Stool', 'Fecal incontinence varies from inadvertent soiling with liquid stool to the involuntary excretion of feces.  It is a demoralizing disability because it affects self-assurance and can lead to social isolation.  It is the second leading cause of nursing home placement.', '47-1-E', 309, 65, 1, 1271174178, 3499),
(392, 'Incontinence, Urine', 'Because there is increasing incidence of involuntary micturition with age, incontinence has increased in frequency in our ageing population.  Unfortunately, incontinence remains under treated despite its effect on quality of life and impact on physical and psychological morbidity.  Primary care physicians should diagnose the cause of incontinence in the majority of cases.', '47-2-E', 309, 66, 1, 1271174178, 3499),
(393, 'Incontinence, Urine, Pediatric (enuresis)', 'Enuresis is the involuntary passage of urine, and may be diurnal (daytime), nocturnal (nighttime), or both.  The majority of children have primary nocturnal enuresis (20% of five-year-olds).  Diurnal and secondary enuresis is much less common, but requires differentiating between underlying diseases and stress related conditions.', '47-3-E', 309, 67, 1, 1271174178, 3499),
(394, 'Impotence/erectile Dysfunction', 'Impotence is an issue that has a major impact on relationships.  There is a need to explore the impact with both partners, although many consider it a male problem.  Impotence is present when an erection of sufficient rigidity for sexual intercourse cannot be acquired or sustained&gt;75% of the time.', '48-E', 309, 68, 1, 1271174178, 3499),
(395, 'Jaundice ', 'Jaundice may represent hemolysis or hepatobiliary disease.  Although usually the evaluation of a patient is not urgent, in a few situations it is a medical emergency (e.g., massive hemolysis, ascending cholangitis, acute hepatic failure).', '49-E', 309, 69, 1, 1271174178, 3499),
(396, 'Neonatal Jaundice ', 'Jaundice, usually mild unconjugated bilirubinemia, affects nearly all newborns.  Up to 65% of full-term neonates have jaundice at 72 - 96 hours of age.  Although some causes are ominous, the majority are transient and without consequences.', '49-1-E', 395, 1, 1, 1271174178, 3499),
(397, 'Joint Pain, Mono-articular (acute, Chronic)', 'Any arthritis can initially present as one swollen painful joint.  Thus, the early exclusion of polyarticular joint disease may be challenging.  In addition, pain caused by a problem within the joint needs to be distinguished from pain arising from surrounding soft tissues.', '50-1-E', 309, 70, 1, 1271174178, 3499),
(398, 'Joint Pain, Poly-articular (acute, Chronic)', 'Polyarticular joint pain is common in medical practice, and causes vary from some that are self-limiting to others which are potentially disabling and life threatening.', '50-2-E', 309, 71, 1, 1271174178, 3499),
(399, 'Periarticular Pain/soft Tissue Rheumatic Disorders', 'Pain caused by a problem within the joint needs to be distinguished from pain arising from surrounding soft tissues.', '50-3-E', 309, 72, 1, 1271174178, 3499),
(400, 'Lipids Abnormal, Serum ', 'Hypercholesterolemia is a common and important modifiable risk factor for ischemic heart disease (IHD) and cerebro-vascular disease.  The relationship of elevated triglycerides to IHD is less clear (may be a modest independent predictor) but very high levels predispose to pancreatitis.  HDL cholesterol is inversely related to IHD risk.', '51-E', 309, 73, 1, 1271174178, 3499),
(401, 'Liver Function Tests Abnormal, Serum', 'Appropriate investigation can distinguish benign reversible liver disease requiring no treatment from potentially life-threatening conditions requiring immediate therapy.', '52-E', 309, 74, 1, 1271174178, 3499),
(402, 'Lump/mass, Musculoskeletal ', 'Lumps or masses are a common cause for consultation with a physician.  The majority will be of a benign dermatologic origin. Musculoskeletal lumps or masses are not common, but they represent an important cause of morbidity and mortality, especially among young people.', '53-E', 309, 75, 1, 1271174178, 3499),
(403, 'Lymphadenopathy', 'Countless potential causes may lead to lymphadenopathy.  Some of these are serious but treatable.  In a study of patients with lymphadenopathy, 84% were diagnosed with benign lymphadenopathy and the majority of these were due to a nonspecific (reactive) etiology.', '54-E', 309, 76, 1, 1271174178, 3499),
(404, 'Mediastinal Mass/hilar Adenopathy', 'The mediastinum contains many vital structures (heart, aorta, pulmonary hila, esophagus) that are affected directly or indirectly by mediastinal masses.  Evaluation of such masses is aided by envisaging the nature of the mass from its location in the mediastinum.</p>\r\n<p>', '54-1-E', 403, 1, 1, 1271174178, 3499),
(405, 'Magnesium Concentration Serum, Abnormal/hypomagnesemia ', 'Although hypomagnesemia occurs in only about 10% of hospitalized patients, the incidence rises to over 60% in severely ill patients.  It is frequently associated with hypokalemia and hypocalcemia.', '55-E', 309, 77, 1, 1271174178, 3499),
(406, 'Amenorrhea/oligomenorrhea', 'The average age of onset of menarche in North America is 11 to 13 years and menopause is approximately 50 years.  Between these ages, absence of menstruation is a cause for investigation and appropriate management.', '56-1-E', 309, 78, 1, 1271174178, 3499),
(407, 'Dysmenorrhea', 'Approximately 30 - 50% of post-pubescent women experience painful menstruation and 10% of women are incapacitated by pain 1 - 3 days per month.  It is the single greatest cause of lost working hours and school days among young women.', '56-2-E', 309, 79, 1, 1271174178, 3499),
(408, 'Pre-menstrual Syndrome (pms)', 'Pre-menstrual syndrome is a combination of physical, emotional, or behavioral symptoms that occur prior to the menstrual cycle and are absent during the rest of the cycle.  The symproms, on occasion, are severe enough to intefere significantly with work and/or home activities.', '56-3-E', 309, 80, 1, 1271174178, 3499),
(409, 'Menopause ', 'Women cease to have menstrual periods at about 50 years of age, although ovarian function declines earlier.  Changing population demographics means that the number of women who are menopausal will continue to grow, and many women will live 1/3 of their lives after ovarian function ceases.  Promotion of health maintenance in this group of women will enhance physical, emotional, and sexual quality of life.', '57-E', 309, 81, 1, 1271174178, 3499),
(410, 'Coma', 'Patients with altered level of consciousness account for 5% of hospital admissions.  Coma however is defined as a state of pathologic unconsciousness (unarousable).', '58-1-E', 309, 82, 1, 1271174178, 3499),
(411, 'Delirium/confusion ', 'An acute confusional state in patients with medical illness, especially among those who are older, is extremely common.  Between 10 - 15% of elderly patients admitted to hospital have delirium and up to a further 30% develop delirium while in hospital.  It represents a disturbance of consciousness with reduced ability to focus, sustain, or shift attention (DSM-IV).  This disturbance tends to develop over a short period of time (hours to days) and tends to fluctuate during the course of the day.  A clear understanding of the differential diagnosis enables rapid and appropriate management.', '58-2-E', 309, 83, 1, 1271174178, 3499),
(412, 'Dementia', 'Dementia is a problem physicians encounter frequently, and causes that are potentially treatable require identification.  Alzheimer disease is the most common form of dementia in the elderly (about 70%), and primary care physicians will need to diagnose and manage the early cognitive manifestations.', '58-3-E', 309, 84, 1, 1271174178, 3499),
(413, 'Mood Disorders ', 'Depression is one of the top five diagnoses made in the offices of primary care physicians.  Depressed mood occurs in some individuals as a normal reaction to grief, but in others it is considered abnormal because it interferes with the person''s daily function (e.g., self-care, relationships, work, self-support).  Thus, it is necessary for primary care clinicians to detect depression, initiate treatment, and refer to specialists for assistance when required.', '59-E', 309, 85, 1, 1271174178, 3499),
(414, 'Mouth Problems', 'Although many disease states can affect the mouth, the two most common ones are odontogenic infections (dental carries and periodontal infections) and oral carcinoma. Almost 15% of the population have significant periodontal disease despite its being preventable.  Such infections, apart from the discomfort inflicted, may result in serious complications.', '60-E', 309, 86, 1, 1271174178, 3499),
(415, 'Movement Disorders,involuntary/tic Disorders', 'Movement disorders are regarded as either excessive (hyperkinetic) or reduced (bradykinetic) activity.  Diagnosis depends primarily on careful observation of the clinical features. ', '61-E', 309, 87, 1, 1271174178, 3499),
(416, 'Diastolic Murmur', 'Although systolic murmurs are often "innocent" or physiological, diastolic murmurs are virtually always pathologic.', '62-1-E', 309, 88, 1, 1271174178, 3499),
(417, 'Heart Sounds, Pathological', 'Pathological heart sounds are clues to underlying heart disease.', '62-2-E', 309, 89, 1, 1271174178, 3499),
(418, 'Systolic Murmur', 'Ejection systolic murmurs are common, and frequently quite ''innocent'' (with absence of cardiac findings and normal splitting of the second sound).', '62-3-E', 309, 90, 1, 1271174178, 3499),
(419, 'Neck Mass/goiter/thyroid Disease ', 'The vast majority of neck lumps are benign (usually reactive lymph nodes or occasionally of congenital origin).  The lumps that should be of most concern to primary care physicians are the rare malignant neck lumps.  Among patients with thyroid nodules, children, patients with a family history or history for head and neck radiation, and adults&lt;30 years or&gt;60 years are at higher risk for thyroid cancer.', '63-E', 309, 91, 1, 1271174178, 3499),
(420, 'Newborn, Depressed', 'A call requesting assistance in the delivery of a newborn may be "routine" or because the neonate is depressed and requires resuscitation.  For any type of call, the physician needs to be prepared to manage potential problems.', '64-E', 309, 92, 1, 1271174178, 3499),
(421, 'Non-reassuring Fetal Status (fetal Distress)', 'Non-reassuring fetal status occurs in 5 - 10% of pregnancies.  (Fetal distress, a term also used, is imprecise and has a low positive predictive value.  The newer term should be used.)  Early detection and pro-active management can reduce serious consequences and prepare parents for eventualities.', '65-E', 309, 93, 1, 1271174178, 3499),
(422, 'Numbness/tingling/altered Sensation', 'Disordered sensation may be alarming and highly intrusive.  The physician requires a framework of knowledge in order to assess abnormal sensation, consider the likely site of origin, and recognise the implications.', '66-E', 309, 94, 1, 1271174178, 3499),
(423, 'Pain', 'Because pain is considered a signal of disease, it is the most common symptom that brings a patient to a physician.  Acute pain is a vital protective mechanism.  In contrast, chronic pain (&gt;6 weeks or lasting beyond the ordinary duration of time that an injury needs to heal) serves no physiologic role and is itself a disease state.  Pain is an unpleasant somatic sensation, but it is also an emotion.  Although control of pain/discomfort is a crucial endpoint of medical care, the degree of analgesia provided is often inadequate, and may lead to complications (e.g., depression, suicide).  Physicians should recognise the development and progression of pain, and develop strategies for its control.', '67-E', 309, 95, 1, 1271174178, 3499),
(424, ' Generalized Pain Disorders', 'Fibromyalgia, a common cause of chronic musculoskeletal pain and fatigue, has no known etiology and is not associated with tissue inflammation.  It affects muscles, tendons, and ligaments.  Along with a group of similar conditions, fibromyalgia is controversial because obvious sign and laboratory/radiological abnormalities are lacking.</p>\r\n<p>Polymyalgia rheumatica, a rheumatic condition frequently linked to giant cell (temporal) arteritis, is a relatively common disorder (prevalence of about 700/100,000 persons over 50 years of age).  Synovitis is considered to be the cause of the discomfort.', '67-1-2-1-E', 423, 1, 1, 1271174178, 3499),
(425, 'Local Pain, Hip/knee/ankle/foot', 'With the current interest in physical activity, the commonest cause of leg pain is muscular or ligamentous strain.  The knee, the most intricate joint in the body, has the greatest susceptibility to pain.', '67-1-2-3-E', 423, 2, 1, 1271174178, 3499),
(426, 'Local Pain, Shoulder/elbow/wrist/hand', 'After backache, upper extremity pain is the most common type of musculoskeletal pain.', '67-1-2-2-E', 423, 3, 1, 1271174178, 3499),
(427, 'Local Pain, Spinal Compression/osteoporosis', 'Spinal compression is one manifestation of osteoporosis, the prevalence of which increases with age.  As the proportion of our population in old age rises, osteoporosis becomes an important cause of painful fractures, deformity, loss of mobility and independence, and even death.  Although less common in men, the incidence of fractures increases exponentially with ageing, albeit 5 - 10 years later.  For unknown reasons, the mortality associated with fractures is higher in men than in women.', '67-1-2-4-E', 423, 4, 1, 1271174178, 3499),
(428, 'Local Pain, Spine/low Back Pain', 'Low back pain is one of the most common physical complaints and a major cause of lost work time.  Most frequently it is associated with vocations that involve lifting, twisting, bending, and reaching.  In individuals suffering from chronic back pain, 5% will have an underlying serious disease.', '67-1-2-6-E', 423, 5, 1, 1271174178, 3499),
(429, 'Local Pain, Spine/neck/thoracic', 'Approximately 10 % of the adult population have neck pain at any one time.  This prevalence is similar to low back pain, but few patients lose time from work and the development of neurologic deficits is&lt;1 %.', '67-1-2-5-E', 423, 6, 1, 1271174178, 3499),
(430, 'Central/peripheral Neuropathic Pain', 'Neuropathic pain is caused by dysfunction of the nervous system without tissue damage.  The pain tends to be chronic and causes great discomfort.', '67-2-2-E', 423, 7, 1, 1271174178, 3499),
(431, 'Sympathetic/complex Regional Pain Syndrome/reflex Sympatheti', 'Following an injury or vascular event (myocardial infarction, stroke), a disorder may develop that is characterized by regional pain and sensory changes (vasomotor instability, skin changes, and patchy bone demineralization).', '67-2-1-E', 423, 8, 1, 1271174178, 3499),
(432, 'Palpitations (abnormal Ecg-arrhythmia)', 'Palpitations are a common symptom.  Although the cause is often benign, occasionally it may indicate the presence of a serious underlying problem.', '68-E', 309, 96, 1, 1271174178, 3499),
(433, 'Panic And Anxiety ', 'Panic attacks/panic disorders are common problems in the primary care setting.  Although such patients may present with discrete episodes of intense fear, more commonly they complain of one or more physical symptoms.  A minority of such patients present to mental health settings, whereas 1/3 present to their family physician and another 1/3 to emergency departments.  Generalized anxiety disorder, characterized by excessive worry and anxiety that are difficult to control, tends to develop secondary to other psychiatric conditions.', '69-E', 309, 97, 1, 1271174178, 3499),
(434, 'Pap Smear Screening', 'Carcinoma of the cervix is a preventable disease.  Any female patient who visits a physician''s office should have current screening guidelines applied and if appropriate, a Pap smear should be recommended.', '70-E', 309, 98, 1, 1271174178, 3499),
(435, 'Pediatric Emergencies  - Acutely Ill Infant/child', 'Although pediatric emergencies such as the ones listed below are discussed with the appropriate condition, the care of the patient in the pediatric age group demands special skills', '71-E', 309, 99, 1, 1271174178, 3499),
(436, 'Crying/fussing Child', 'A young infant whose only symptom is crying/fussing challenges the primary care physician to distinguish between benign and organic causes.', '71-1-E', 435, 1, 1, 1271174178, 3499),
(437, 'Hypotonia/floppy Infant/child', 'Infants/children with decreased resistance to passive movement differ from those with weakness and hyporeflexia.  They require detailed, careful neurologic evaluation. Management programs, often life-long, are multidisciplinary and involve patients, family, and community.', '71-2-E', 435, 2, 1, 1271174178, 3499),
(438, 'Pelvic Mass', 'Pelvic masses are common and may be found in a woman of any age, although the possible etiologies differ among age groups.  There is a need to diagnose and investigate them since early detection may affect outcome.', '72-E', 309, 100, 1, 1271174178, 3499),
(439, 'Pelvic Pain', 'Acute pelvic pain is potentially life threatening.  Chronic pelvic pain is one of the most common problems in gynecology.  Women average 2 - 3 visits each year to physicians'' offices with chronic pelvic pain.  At present, only about one third of these women are given a specific diagnosis.  The absence of a clear diagnosis can frustrate both patients and clinicians.  Once the diagnosis is established, specific and usually successful treatment may be instituted.', '73-E', 309, 101, 1, 1271174178, 3499),
(440, 'Periodic Health Examination (phe) ', 'Periodically, patients visit physicians'' office not because they are unwell, but because they want a ''check-up''.  Such visits are referred to as health maintenance or the PHE. The PHE is an opportunity to relate to an asymptomatic patient for the purpose of case finding and screening for undetected disease and risky behaviour.  It is also an opportunity for health promotion and disease prevention.  The decision to include or exclude a medical condition in the PHE should be based on the burden of suffering caused by the condition, the quality of the screening, and effectiveness of the intervention.', '74-E', 309, 102, 1, 1271174178, 3499),
(441, 'Infant And Child Immunization ', 'Immunization has reduced or eradicated many infectious diseases and has improved overall world health.  Recommended immunization schedules are constantly updated as new vaccines become available.', '74-2-E', 440, 1, 1, 1271174178, 3499),
(442, 'Newborn Assessment/nutrition ', 'Primary care physicians play a vital role in identifying children at risk for developmental and other disorders that are threatening to life or long-term health before they become symptomatic.  In most cases, parents require direction and reassurance regarding the health status of their newborn infant.  With respect to development, parental concerns regarding the child''s language development, articulation, fine motor skills, and global development require careful assessment.', '74-1-E', 440, 2, 1, 1271174178, 3499),
(443, 'Pre-operative Medical Evaluation', 'Evaluation of patients prior to surgery is an important element of comprehensive medical care.  The objectives of such an evaluation include the detection of unrecognized disease that may increase the risk of surgery and how to minimize such risk.', '74-3-E', 440, 3, 1, 1271174178, 3499),
(444, 'Work-related Health Issues ', 'Physicians will encounter health hazards in their own work place, as well as in patients'' work place.  These hazards need to be recognised and addressed.  A patient''s reported environmental exposures may prompt interventions important in preventing future illnesses/injuries.  Equally important, physicians can not only play an important role in preventing occupational illness but also in promoting environmental health.', '74-4-E', 440, 4, 1, 1271174178, 3499),
(445, 'Personality Disorders ', 'Personality disorders are persistent and maladaptive patterns of behaviour exhibited over a wide variety of social, occupational, and relationship contexts and leading to distress and impairment.  They represent important risk factors for a variety of medical, interpersonal, and psychiatric difficulties.  For example, patients with personality difficulties may attempt suicide, or may be substance abusers.  As a group, they may alienate health care providers with angry outbursts, high-risk behaviours, signing out against medical advice, etc.', '75-E', 309, 103, 1, 1271174178, 3499),
(446, 'Pleural Effusion/pleural Abnormalities', NULL, '76-E', 309, 104, 1, 1271174178, 3499),
(447, 'Poisoning', 'Exposures to poisons or drug overdoses account for 5 - 10% of emergency department visits, and&gt;5 % of admissions to intensive care units.  More than 50 % of these patients are children less than 6 years of age.', '77-E', 309, 105, 1, 1271174178, 3499),
(448, 'Administration Of Effective Health Programs At The Populatio', 'Knowing the organization of the health care and public health systems in Canada as well as how to determine the most cost-effective interventions are becoming key elements of clinical practice. Physicians also must work well in multidisciplinary teams within the current system in order to achieve the maximum health benefit for all patients and residents. ', '78-4-E', 309, 106, 1, 1271174178, 3499),
(449, 'Assessing And Measuring Health Status At The Population Leve', 'Knowing the health status of the population allows for better planning and evaluation of health programs and tailoring interventions to meet patient/community needs. Physicians are also active participants in disease surveillance programs, encouraging them to address health needs in the population and not merely health demands.', '78-2-E', 309, 107, 1, 1271174178, 3499),
(450, 'Concepts Of Health And Its Determinants', 'Concepts of health, illness, disease and the socially defined sick role are fundamental to understanding the health of a community and to applying that knowledge to the patients that a physician serves. With advances in care, the aspirations of patients for good health have expanded and this has placed new demands on physicians to address issues that are not strictly biomedical in nature. These concepts are also important if the physician is to understand health and illness behaviour. ', '78-1-E', 309, 108, 1, 1271174178, 3499),
(451, 'Environment', 'Environmental issues are important in medical practice because exposures may be causally linked to a patient''s clinical presentation and the health of the exposed population. A physician is expected to work with regulatory agencies to help implement the necessary interventions to prevent future illness.  Physician involvement is important in the promotion of global environmental health.', '78-6-E', 309, 109, 1, 1271174178, 3499),
(452, 'Health Of Special Populations', 'Health equity is defined as each person in society having an equal opportunity for health. Each community is composed of diverse groups of individuals and sub-populations. Due to variations in factors such as physical location, culture, behaviours, age and gender structure, populations have different health risks and needs that must be addressed in order to achieve health equity.  Hence physicians need to be aware of the differing needs of population groups and must be able to adjust service provision to ensure culturally safe communications and care.', '78-7-E', 309, 110, 1, 1271174178, 3499),
(453, 'Interventions At The Population Level', 'Many interventions at the individual level must be supported by actions at the community level. Physicians will be expected to advocate for community wide interventions and to address issues that occur to many patients across their practice. ', '78-3-E', 309, 111, 1, 1271174178, 3499),
(454, 'Outbreak Management', 'Physicians are crucial participants in the control of outbreaks of disease. They must be able to diagnose cases, recognize outbreaks, report these to public health authorities and work with authorities to limit the spread of the outbreak. A common example includes physicians working in nursing homes and being asked to assist in the control of an outbreak of influenza or diarrhea.', '78-5-E', 309, 112, 1, 1271174178, 3499),
(455, 'Hyperkalemia ', 'Hyperkalemia may have serious consequences (especially cardiac) and may also be indicative of the presence of serious associated medical conditions.', '79-1-E', 309, 113, 1, 1271174178, 3499),
(456, 'Hypokalemia ', 'Hypokalemia, a common clinical problem, is most often discovered on routine analysis of serum electrolytes or ECG results.  Symptoms usually develop much later when depletion is quite severe.', '79-2-E', 309, 114, 1, 1271174178, 3499),
(457, 'Antepartum Care ', 'The purpose of antepartum care is to help achieve as good a maternal and infant outcome as possible.  This means that psychosocial issues as well as biological issues need to be addressed.', '80-1-E', 309, 115, 1, 1271174178, 3499),
(458, 'Intrapartum Care/postpartum Care ', 'Intrapartum and postpartum care means the care of the mother and fetus during labor and the six-week period following birth during which the reproductive tract returns to its normal nonpregnant state.  Of pregnant women, 85% will undergo spontaneous labor between 37 and 42 weeks of gestation.  Labor is the process by which products of conception are delivered from the uterus by progressive cervical effacement and dilatation in the presence of regular uterine contractions.', '80-2-E', 309, 116, 1, 1271174178, 3499),
(459, 'Obstetrical Complications ', 'Virtually any maternal medical or surgical condition can complicate the course of a pregnancy and/or be affected by the pregnancy.  In addition, conditions arising in pregnancy can have adverse effects on the mother and/or the fetus.  For example, babies born prematurely account for&gt;50% of perinatal morbidity and mortality; an estimated 5% of women will describe bleeding of some extent during pregnancy, and in some patients the bleeding will endanger the mother.', '80-3-E', 309, 117, 1, 1271174178, 3499),
(460, 'Pregnancy Loss', 'A miscarriage or abortion is a pregnancy that ends before the fetus can live outside the uterus.  The term also means the actual passage of the uterine contents.  It is very common in early pregnancy; up to 20% of pregnant women have a miscarriage before 20 weeks of pregnancy, 80% of these in the first 12 weeks.', '81-E', 309, 118, 1, 1271174178, 3499),
(461, 'Prematurity', 'The impact of premature birth is best summarized by the fact that&lt;10% of babies born prematurely in North America account for&gt;50% of all perinatal morbidity and mortality.  Yet outcomes, although guarded, can be rewarding given optimal circumstances.', '82-E', 309, 119, 1, 1271174178, 3499),
(462, 'Prolapse/pelvic Relaxation', 'Patients with pelvic relaxation present with a forward and downward drop of the pelvic organs (bladder, rectum).  In order to identify patients who would benefit from therapy, the physician should be familiar with the manifestations of pelvic relaxation (uterine prolapse, vaginal vault prolapse, cystocele, rectocele, and enterocele) and have an approach to management.', '83-E', 309, 120, 1, 1271174178, 3499),
(463, 'Proteinuria ', 'Urinalysis is a screening procedure used frequently for insurance and routine examinations.  Proteinuria is usually identified by positive dipstick on routine urinalysis. Persistent proteinuria often implies abnormal glomerular function.', '84-E', 309, 121, 1, 1271174178, 3499),
(464, 'Pruritus ', 'Itching is the most common symptom in dermatology.  In the absence of primary skin lesions, generalised pruritus can be indicative of an underlying systemic disorder.  Most patients with pruritus do not have a systemic disorder and the itching is due to a cutaneous disorder.', '85-E', 309, 122, 1, 1271174178, 3499),
(465, 'Psychotic Patient/disordered Thought', 'Psychosis is a general term for a major mental disorder characterized by derangement of personality and loss of contact with reality, often with false beliefs (delusions), disturbances in sensory perception (hallucinations), or thought disorders (illusions). Schizophrenia is both the most common (1% of world population) and the classic psychotic disorder.  There are other psychotic syndromes that do not meet the diagnostic criteria for schizophrenia, some of them caused by general medical conditions or induced by a substance (alcohol, hallucinogens, steroids).  In the evaluation of any psychotic patient in a primary care setting all of these possibilities need to be considered.', '86-E', 309, 123, 1, 1271174178, 3499),
(466, 'Pulse Abnormalities/diminished/absent/bruits', 'Arterial pulse characteristics should be assessed as an integral part of the physical examination.  Carotid, radial, femoral, posterior tibial, and dorsalis pedis pulses should be examined routinely on both sides, and differences, if any, in amplitude, contour, and upstroke should be ascertained.', '87-E', 309, 124, 1, 1271174178, 3499),
(467, 'Pupil Abnormalities ', 'Pupillary disorders of changing degree are in general of little clinical importance.  If only one pupil is fixed to light, it is suspicious of the effect of mydriatics.  However, pupillary disorders with neurological symptoms may be of significance.', '88-E', 309, 125, 1, 1271174178, 3499),
(468, 'Acute Renal Failure (anuria/oliguria/arf)', 'A sudden and rapid rise in serum creatinine is a common finding.  A competent physician is required to have an organised approach to this problem.', '89-1-E', 309, 126, 1, 1271174178, 3499),
(469, 'Chronic Renal Failure ', 'Although specialists in nephrology will care for patients with chronic renal failure, family physicians will need to identify patients at risk for chronic renal disease, will participate in treatment to slow the progression of chronic renal disease, and will care for other common medical problems that afflict these patients.  Physicians must realise that patients with chronic renal failure have unique risks and that common therapies may be harmful because kidneys are frequently the main routes for excretion of many drugs.', '89-2-E', 309, 127, 1, 1271174178, 3499),
(470, 'Scrotal Mass ', 'In children and adolescents, scrotal masses vary from incidental, requiring only reassurance, to acute pathologic events.  In adults, tumors of the testis are relatively uncommon (only 1 - 2 % of malignant tumors in men), but are considered of particular importance because they affect predominantly young men (25 - 34 years).  In addition, recent advances in management have resulted in dramatic improvement in survival rate.', '90-E', 309, 128, 1, 1271174178, 3499),
(471, 'Scrotal Pain ', 'In most scrotal disorders, there is swelling of the testis or its adnexae.  However, some conditions are not only associated with pain, but pain may precede the development of an obvious mass in the scrotum.', '91-E', 309, 129, 1, 1271174178, 3499),
(472, 'Seizures (epilepsy)', 'Seizures are an important differential diagnosis of syncope.  A seizure is a transient neurological dysfunction resulting from excessive/abnormal electrical discharges of cortical neurons.  They may represent epilepsy (a chronic condition characterized by recurrent seizures) but need to be differentiated from a variety of secondary causes.', '92-E', 309, 130, 1, 1271174178, 3499),
(473, 'Sexual Maturation, Abnormal ', 'Sexual development is important to adolescent perception of self-image and wellbeing. Many factors may disrupt the normal progression to sexual maturation.', '93-1-E', 309, 131, 1, 1271174178, 3499),
(474, 'Sexually Concerned Patient/gender Identity Disorder', 'The social appropriateness of sexuality is culturally determined.  The physician''s own sexual attitude needs to be recognised and taken into account in order to deal with the patient''s concern in a relevant manner.  The patient must be set at ease in order to make possible discussion of private and sensitive sexual issues.', '94-E', 309, 132, 1, 1271174178, 3499),
(475, 'Skin Ulcers/skin Tumors (benign And Malignant)', NULL, '95-E', 309, 133, 1, 1271174178, 3499),
(476, 'Skin Rash, Macules', NULL, '96-E', 309, 134, 1, 1271174178, 3499),
(477, 'Skin Rash, Papules', NULL, '97-E', 309, 135, 1, 1271174178, 3499),
(478, 'Childhood Communicable Diseases ', 'Communicable diseases are common in childhood and vary from mild inconveniences to life threatening disorders.  Physicians need to differentiate between these common conditions and initiate management.', '97-1-E', 477, 1, 1, 1271174178, 3499),
(479, 'Urticaria/angioedema/anaphylaxis', NULL, '97-2-E', 477, 2, 1, 1271174178, 3499),
(480, 'Sleep And Circadian Rhythm Disorders/sleep Apnea Syndrome/in', 'Insomnia is a symptom that affects 1/3 of the population at some time, and is a persistent problem in 10 % of the population.  Affected patients complain of difficulty in initiating and maintaining sleep, and this inability to obtain adequate quantity and quality of sleep results in impaired daytime functioning.', '98-E', 309, 136, 1, 1271174178, 3499),
(481, 'Hypernatremia ', 'Although not extremely common, hypernatremia is likely to be encountered with increasing frequency in our ageing population.  It is also encountered at the other extreme of life, the very young, for the same reason: an inability to respond to thirst by drinking water.', '99-1-E', 309, 137, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` (`objective_id`, `objective_name`, `objective_description`, `objective_code`, `objective_parent`, `objective_order`, `objective_active`, `updated_date`, `updated_by`) VALUES
(482, 'Hyponatremia ', 'Hyponatremia is detected in many asymptomatic patients because serum electrolytes are measured almost routinely.  In children with sodium depletion, the cause of the hyponatremia is usually iatrogenic.  The presence of hyponatremia may predict serious neurologic complications or be relatively benign.', '99-2-E', 309, 138, 1, 1271174178, 3499),
(483, 'Sore Throat (rhinorrhea) ', 'Rhinorrhea and sore throat occurring together indicate a viral upper respiratory tract infection such as the "common cold".  Sore throat may be due to a variety of bacterial and viral pathogens (as well as other causes in more unusual circumstances).  Infection is transmitted from person to person and arises from direct contact with infected saliva or nasal secretions.  Rhinorrhea alone is not infective and may be seasonal (hay fever or allergic rhinitis) or chronic (vaso-motor rhinitis).', '100-E', 309, 139, 1, 1271174178, 3499),
(484, 'Smell/taste Dysfunction ', 'In order to evaluate patients with smell or taste disorders, a multi-disciplinary approach is required.  This means that in addition to the roles specialists may have, the family physician must play an important role.', '100-1-E', 483, 1, 1, 1271174178, 3499),
(485, 'Stature Abnormal (tall Stature/short Stature)', 'To define any growth point, children should be measured accurately and each point (height, weight, and head circumference) plotted.  One of the more common causes of abnormal growth is mis-measurement or aberrant plotting.', '101-E', 309, 140, 1, 1271174178, 3499),
(486, 'Strabismus And/or Amblyopia ', 'Parental concern about children with a wandering eye, crossing eye, or poor vision in one eye makes it necessary for physicians to know how to manage such problems.', '102-E', 309, 141, 1, 1271174178, 3499),
(487, 'Substance Abuse/drug Addiction/withdrawal', 'Alcohol and nicotine abuse is such a common condition that virtually every clinician is confronted with their complications.  Moreover, 10 - 15% of outpatient visits as well as 25 - 40% of hospital admissions are related to substance abuse and its sequelae.', '103-E', 309, 142, 1, 1271174178, 3499),
(488, 'Sudden Infant Death Syndrome(sids)/acute Life Threatening Ev', 'SIDS and/or ALTE are a devastating event for parents, caregivers and health care workers alike.  It is imperative that the precursors, probable cause and parental concerns are extensively evaluated to prevent recurrence.', '104-E', 309, 143, 1, 1271174178, 3499),
(489, 'Suicidal Behavior', 'Psychiatric emergencies are common and serious problems.  Suicidal behaviour is one of several psychiatric emergencies which physicians must know how to assess and manage.', '105-E', 309, 144, 1, 1271174178, 3499),
(490, 'Syncope/pre-syncope/loss Of Consciousness  (fainting)', 'Syncopal episodes, an abrupt and transient loss of consciousness followed by a rapid and usually complete recovery, are common.  Physicians are required to distinguish syncope from seizures, and benign syncope from syncope caused by serious underlying illness.', '106-E', 309, 145, 1, 1271174178, 3499),
(491, 'Fever In A Child/fever In A Child Less Than Three Weeks', 'Fever in children is the most common symptom for which parents seek medical advice.  While most causes are self-limited viral infections (febrile illness of short duration) it is important to identify serious underlying disease and/or those other infections amenable to treatment.', '107-3-E', 309, 146, 1, 1271174178, 3499),
(492, 'Fever In The Immune Compromised Host/recurrent Fever', 'Patients with certain immuno-deficiencies are at high risk for infections.  The infective organism and site depend on the type and severity of immuno-suppression.  Some of these infections are life threatening.', '107-4-E', 309, 147, 1, 1271174178, 3499),
(493, 'Fever Of Unknown Origin ', 'Unlike acute fever (&lt;2 weeks), which is usually either viral (low-grade, moderate fever) or bacterial (high grade, chills, rigors) in origin, fever of unknown origin is an illness of three weeks or more without an established diagnosis despite appropriate investigation.', '107-2-E', 309, 148, 1, 1271174178, 3499),
(494, 'Hyperthermia ', 'Hyperthermia is an elevation in core body temperature due to failure of thermo-regulation (in contrast to fever, which is induced by cytokine activation).  It is a medical emergency and may be associated with severe complications and death.  The differential diagnosis is extensive (includes all causes of fever).', '107-1-E', 309, 149, 1, 1271174178, 3499),
(495, 'Hypothermia ', 'Hypothermia is the inability to maintain core body temperature.  Although far less common than is elevation in temperature, hypothermia (central temperature ? 35°C) is of considerable importance because it can represent a medical emergency.  Severe hypothermia is defined as a core temperature of &lt;28°C.', '107-5-E', 309, 150, 1, 1271174178, 3499),
(496, 'Tinnitus', 'Tinnitus is an awareness of sound near the head without an obvious external source.  It may involve one or both ears, be continuous or intermittent.  Although not usually related to serious medical problems, in some it may interfere with daily activities, affect quality of life, and in a very few be indicative of serious organic disease.', '108-E', 309, 151, 1, 1271174178, 3499),
(497, 'Trauma/accidents', 'Management of patients with traumatic injuries presents a variety of challenges.  They require evaluation in the emergency department for triage and prevention of further deterioration prior to transfer or discharge.  Early recognition and management of complications along with aggressive treatment of underlying medical conditions are necessary to minimise morbidity and mortality in this patient population.', '109-E', 309, 152, 1, 1271174178, 3499),
(498, 'Abdominal Injuries ', 'The major causes of blunt trauma are motor vehicles, auto-pedestrian injuries, and motorcycle/all terrain vehicle injuries.  In children, bicycle injuries, falls, and child abuse also contribute.  Assessment of a patient with an abdominal injury is difficult.  As a consequence, important injuries tend to be missed.  Rupture of a hollow viscus or bleeding from a solid organ may produce few clinical signs.', '109-1-E', 497, 1, 1, 1271174178, 3499),
(499, 'Bites, Animal/insects ', 'Since so many households include pets, animal bite wounds are common.  Dog and cat bites account for about 1% of emergency visits, the majority in children.  Some can be serious and lead to limb damage, and at times permanent disability.</p>\r\n<p>Insect bites in Canada most commonly cause a local inflammatory reaction that subsides within a few hours and is mostly a nuisance.  In contrast, mosquitoes can transmit infectious disease to more than 700 million people in other geographic areas of the world (e.g., malaria, yellow fever, dengue, encephalitis and filariasis among others), as well as in Canada.  Tick-borne illness is also common.  On the other hand, systemic reactions to insect bites are extremely rare compared with insect stings.  The most common insects associated with systemic allergic reactions were blackflies, deerflies, and horseflies.', '109-2-E', 497, 2, 1, 1271174178, 3499),
(500, 'Bone/joint Injury', 'Major fractures are at times associated with other injuries, and priorities must be set for each patient.  For example, hemodynamic stability takes precedence over fracture management, but an open fracture should be managed as soon as possible.  On the other hand, management of many soft tissue injuries is facilitated by initial stabilization of bone or joint injury. Unexplained fractures in children should alert physicians to the possibility of abuse.', '109-3-E', 497, 3, 1, 1271174178, 3499),
(501, 'Chest Injuries ', 'Injury to the chest may be blunt (e.g., motor vehicle accident resulting in steering wheel blow to sternum, falls, explosions, crush injuries) or penetrating (knife/bullet).  In either instance, emergency management becomes extremely important to the eventual outcome.', '109-4-E', 497, 4, 1, 1271174178, 3499),
(502, 'Drowning (near-drowning) ', 'Survival after suffocation by submersion in a liquid medium, including loss of consciousness, is defined as near drowning.  The incidence is uncertain, but likely it may occur several hundred times more frequently than drowning deaths (150,000/year worldwide).', '109-6-E', 497, 5, 1, 1271174178, 3499),
(503, 'Facial Injuries ', 'Facial injuries are potentially life threatening because of possible damage to the airway and central nervous system.', '109-8-E', 497, 6, 1, 1271174178, 3499),
(504, 'Hand/wrist Injuries ', 'Hand injuries are common problems presenting to emergency departments.  The ultimate function of the hand depends upon the quality of the initial care, the severity of the original injury and rehabilitation.', '109-9-E', 497, 7, 1, 1271174178, 3499),
(505, 'Head Trauma/brain Death/transplant Donations', 'Most head trauma is mild and not associated with brain injury or long-term sequelae. Improved outcome after head trauma depends upon preventing deterioration and secondary brain injury.  Serious intracranial injuries may remain undetected due to failure to obtain an indicated head CT.', '109-10-E', 497, 8, 1, 1271174178, 3499),
(506, 'Nerve Injury ', 'Peripheral nerve injuries often occur as part of more extensive injuries and tend to go unrecognized.  Evaluation of these injuries is based on an accurate knowledge of the anatomy and function of the nerve(s) involved.', '109-11-E', 497, 9, 1, 1271174178, 3499),
(507, 'Skin Wounds/regional Anaesthesia', 'Skin and subcutaneous wounds tend to be superficial and can be repaired under local anesthesia.  Animal bite wounds are common and require special consideration.  Since so many households include pets, dog and cat bites account for about 1% of emergency visits, the majority in children.  Some can be serious and lead to limb damage, and at times permanent disability.', '109-12-E', 497, 10, 1, 1271174178, 3499),
(508, 'Spinal Trauma', 'Most spinal cord injuries are a result of car accidents, falls, sports-related trauma, or assault with weapons.  The average age at the time of spinal injury is approximately 35 years, and men are four times more likely to be injured than are women.  The sequelae of such events are dire in terms of effect on patient, family, and community.  Initial immobilization and maintenance of ventilation are of critical importance.', '109-13-E', 497, 11, 1, 1271174178, 3499),
(509, 'Urinary Tract Injuries ', 'Urinary tract injuries are usually closed rather than penetrating, and may affect the kidneys and/or the collecting system.', '109-14-E', 497, 12, 1, 1271174178, 3499),
(510, 'Vascular Injury ', 'Vascular injuries are becoming more common.  Hemorrhage may be occult and require a high index of suspicion (e.g., fracture in an adjacent bone).', '109-15-E', 497, 13, 1, 1271174178, 3499),
(511, 'Dysuria And/or Pyuria ', 'Patients with urinary tract infections, especially the very young and very old, may present in an atypical manner.  Appropriate diagnosis and management may prevent significant morbidity.  Dysuria may mean discomfort/pain on micturition or difficulty with micturition.  Pain usually implies infection whereas difficulty is usually related to distal mechanical obstruction (e.g., prostatic).', '110-1-E', 309, 153, 1, 1271174178, 3499),
(512, 'Polyuria/polydipsia', 'Urinary frequency, a common complaint, can be confused with polyuria, a less common, but important complaint.  Diabetes mellitus is a common disorder with morbidity and mortality that can be reduced by preventive measures.  Intensive glycemic control during pregnancy will reduce neonatal complications.', '110-2-E', 309, 154, 1, 1271174178, 3499),
(513, 'Urinary Obstruction/hesitancy/prostatic Cancer', 'Urinary tract obstruction is a relatively common problem.  The obstruction may be complete or incomplete, and unilateral or bilateral.  Thus, the consequences of the obstruction depend on its nature.', '111-E', 309, 155, 1, 1271174178, 3499),
(514, 'Vaginal Bleeding, Excessive/irregular/abnormal', 'Vaginal bleeding is considered abnormal when it occurs at an unexpected time (before menarche or after menopause) or when it varies from the norm in amount or pattern (urinary tract and bowel should be excluded as a source).  Amount or pattern is considered outside normal when it is associated with iron deficiency anemia, it lasts&gt;7days, flow is&gt;80ml/clots, or interval is&lt;24 days.', '112-E', 309, 156, 1, 1271174178, 3499),
(515, 'Vaginal Discharge/vulvar Itch/std ', 'Vaginal discharge, with or without pruritus, is a common problem seen in the physician''s office.', '113-E', 309, 157, 1, 1271174178, 3499),
(516, 'Violence, Family', 'There are a number of major psychiatric emergencies and social problems which physicians must be prepared to assess and manage.  Domestic violence is one of them, since it has both direct and indirect effects on the health of populations.  Intentional controlling or violent behavior (physical, sexual, or emotional abuse, economic control, or social isolation of the victim) by a person who is/was in an intimate relationship with the victim is domestic violence.  The victim lives in a state of constant fear, terrified about when the next episode of abuse will occur.  Despite this, abuse frequently remains hidden and undiagnosed because patients often conceal that they are in abusive relationships.  It is important for clinicians to seek the diagnosis in certain groups of patients.', '114-E', 309, 158, 1, 1271174178, 3499),
(517, 'Adult Abuse/spouse Abuse ', 'The major problem in spouse abuse is wife abuse (some abuse of husbands has been reported).  It is the abuse of power in a relationship involving domination, coercion, intimidation, and the victimization of one person by another.  Ten percent of women in a relationship with a man have experienced abuse.  Of women presenting to a primary care clinic, almost 1/3 reported physical and verbal abuse.', '114-3-E', 516, 1, 1, 1271174178, 3499),
(518, 'Child Abuse, Physical/emotional/sexual/neglect/self-induced ', 'Child abuse is intentional harm to a child by the caregiver.  It is part of the spectrum of family dysfunction and leads to significant morbidity and mortality (recently sexual attacks on children by groups of other children have increased).  Abuse causes physical and emotional trauma, and may present as neglect.  The possibility of abuse must be in the mind of all those involved in the care of children who have suffered traumatic injury or have psychological or social disturbances (e.g., aggressive behavior, stress disorder, depressive disorder, substance abuse, etc.).', '114-1-E', 516, 2, 1, 1271174178, 3499),
(519, 'Elderly Abuse ', 'Abuse of the elderly may represent an act or omission that results in harm to the elderly person''s health or welfare.  Although the incidence and prevalence in Canada has been difficult to quantitate, in one study 4 % of surveyed seniors report that they experienced abuse.  There are three categories of abuse: domestic, institutional, and self-neglect.', '114-2-E', 516, 3, 1, 1271174178, 3499),
(520, 'Acute Visual Disturbance/loss', 'Loss of vision is a frightening symptom that demands prompt attention; most patients require an urgent ophthalmologic opinion.', '115-1-E', 309, 159, 1, 1271174178, 3499),
(521, 'Chronic Visual Disturbance/loss ', 'Loss of vision is a frightening symptom that demands prompt attention on the part of the physician.', '115-2-E', 309, 160, 1, 1271174178, 3499),
(522, 'Vomiting/nausea ', 'Nausea may occur alone or along with vomiting (powerful ejection of gastric contents), dyspepsia, and other GI complaints.  As a cause of absenteeism from school or workplace, it is second only to the common cold.  When prolonged or severe, vomiting may be associated with disturbances of volume, water and electrolyte metabolism that may require correction prior to other specific treatment.', '116-E', 309, 161, 1, 1271174178, 3499),
(523, 'Weakness/paralysis/paresis/loss Of Motion', 'Many patients who complain of weakness are not objectively weak when muscle strength is formally tested.  A careful history and physical examination will permit the distinction between functional disease and true muscle weakness.', '117-E', 309, 162, 1, 1271174178, 3499),
(524, 'Weight (low) At Birth/intrauterine Growth Restriction ', 'Intrauterine growth restriction (IUGR) is often a manifestation of congenital infections, poor maternal nutrition, or maternal illness.  In other instances, the infant may be large for the gestational age.  There may be long-term sequelae for both.  Low birth weight is the most important risk factor for infant mortality.  It is also a significant determinant of infant and childhood morbidity, particularly neuro-developmental problems and learning disabilities.', '118-3-E', 309, 163, 1, 1271174178, 3499),
(525, 'Weight Gain/obesity ', 'Obesity is a chronic disease that is increasing in prevalence. The percentage of the population with a body mass index of&gt;30 kg/m2 is approximately 15%.', '118-1-E', 309, 164, 1, 1271174178, 3499),
(526, 'Weight Loss/eating Disorders/anorexia ', 'Although voluntary weight loss may be of no concern in an obese patient, it could be a manifestation of psychiatric illness.  Involuntary clinically significant weight loss (&gt;5% baseline body weight or 5 kg) is nearly always a sign of serious medical or psychiatric illness and should be investigated.', '118-2-E', 309, 165, 1, 1271174178, 3499),
(527, 'Lower Respiratory Tract Disorders ', 'Individuals with episodes of wheezing, breathlessness, chest tightness, and cough usually have limitation of airflow.  Frequently this limitation is reversible with treatment.  Without treatment it may be lethal.', '119-1-E', 309, 166, 1, 1271174178, 3499),
(528, 'Upper Respiratory Tract Disorders ', 'Wheezing, a continuous musical sound&gt;1/4 seconds, is produced by vibration of the walls of airways narrowed almost to the point of closure.  It can originate from airways of any size, from large upper airways to intrathoracic small airways.  It can be either inspiratory or expiratory, unlike stridor (a noisy, crowing sound, usually inspiratory and resulting from disturbances in or adjacent to the larynx).', '119-2-E', 309, 167, 1, 1271174178, 3499),
(529, 'White Blood Cells, Abnormalities Of', 'Because abnormalities of white blood cells (WBCs) occur commonly in both asymptomatic as well as acutely ill patients, every physician will need to evaluate patients for this common problem.  Physicians also need to select medications to be prescribed mindful of the morbidity and mortality associated with drug-induced neutropenia and agranulocytosis.', '120-E', 309, 168, 1, 1271174178, 3499);

CREATE TABLE IF NOT EXISTS `global_lu_provinces` (
  `province_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL,
  `province` varchar(200) NOT NULL,
  `abbreviation` varchar(200) NOT NULL,
  PRIMARY KEY (`province_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `global_lu_provinces` (`province_id`, `country_id`, `province`, `abbreviation`) VALUES
(1, 39, 'Alberta', 'AB'),
(2, 39, 'British Columbia', 'BC'),
(3, 39, 'Manitoba', 'MB'),
(4, 39, 'New Brunswick', 'NB'),
(5, 39, 'Newfoundland and Labrador', 'NL'),
(6, 39, 'Northwest Territories', 'NT'),
(7, 39, 'Nova Scotia', 'NS'),
(8, 39, 'Nunavut', 'NU'),
(9, 39, 'Ontario', 'ON'),
(10, 39, 'Prince Edward Island', 'PE'),
(11, 39, 'Quebec', 'QC'),
(12, 39, 'Saskatchewan', 'SK'),
(13, 39, 'Yukon Territory', 'YT'),
(14, 227, 'Alabama', 'AL'),
(15, 227, 'Alaska', 'AK'),
(16, 227, 'Arizona', 'AZ'),
(17, 227, 'Arkansas', 'AR'),
(18, 227, 'California', 'CA'),
(19, 227, 'Colorado', 'CO'),
(20, 227, 'Connecticut', 'CT'),
(21, 227, 'Delaware', 'DE'),
(22, 227, 'Florida', 'FL'),
(23, 227, 'Georgia', 'GA'),
(24, 227, 'Hawaii', 'HI'),
(25, 227, 'Idaho', 'ID'),
(26, 227, 'Illinois', 'IL'),
(27, 227, 'Indiana', 'IN'),
(28, 227, 'Iowa', 'IA'),
(29, 227, 'Kansas', 'KS'),
(30, 227, 'Kentucky', 'KY'),
(31, 227, 'Louisiana', 'LA'),
(32, 227, 'Maine', 'ME'),
(33, 227, 'Maryland', 'MD'),
(34, 227, 'Massachusetts', 'MA'),
(35, 227, 'Michigan', 'MI'),
(36, 227, 'Minnesota', 'MN'),
(37, 227, 'Mississippi', 'MS'),
(38, 227, 'Missouri', 'MO'),
(39, 227, 'Montana', 'MT'),
(40, 227, 'Nebraska', 'NE'),
(41, 227, 'Nevada', 'NV'),
(42, 227, 'New Hampshire', 'NH'),
(43, 227, 'New Jersey', 'NJ'),
(44, 227, 'New Mexico', 'NM'),
(45, 227, 'New York', 'NY'),
(46, 227, 'North Carolina', 'NC'),
(47, 227, 'North Dakota', 'ND'),
(48, 227, 'Ohio', 'OH'),
(49, 227, 'Oklahoma', 'OK'),
(50, 227, 'Oregon', 'OR'),
(51, 227, 'Pennsylvania', 'PA'),
(52, 227, 'Rhode Island', 'RI'),
(53, 227, 'South Carolina', 'SC'),
(54, 227, 'South Dakota', 'SD'),
(55, 227, 'Tennessee', 'TN'),
(56, 227, 'Texas', 'TX'),
(57, 227, 'Utah', 'UT'),
(58, 227, 'Vermont', 'VT'),
(59, 227, 'Virginia', 'VA'),
(60, 227, 'Washington', 'WA'),
(61, 227, 'West Virginia', 'WV'),
(62, 227, 'Wisconsin', 'WI'),
(63, 227, 'Wyoming', 'WY');

CREATE TABLE IF NOT EXISTS `global_lu_publication_type` (
  `type_id` int(11) NOT NULL default '0',
  `type_description` varchar(50) NOT NULL default '',
  PRIMARY KEY (`type_id`),
  KEY `type_description` (`type_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `global_lu_publication_type` (`type_id`, `type_description`) VALUES
(1, 'Peer-Reviewed Article'),
(2, 'Non-Peer-Reviewed Article'),
(3, 'Chapter'),
(4, 'Peer-Reviewed Abstract'),
(5, 'Non-Peer-Reviewed Abstract'),
(6, 'Complete Book'),
(7, 'Monograph'),
(8, 'Editorial'),
(9, 'Published Conference Proceeding'),
(10, 'Poster Presentations'),
(11, 'Technical Report');

CREATE TABLE IF NOT EXISTS `global_lu_roles` (
  `role_id` int(11) NOT NULL default '0',
  `role_description` varchar(50) NOT NULL default '',
  PRIMARY KEY (`role_id`),
  KEY `role_description` (`role_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `global_lu_roles` (`role_id`, `role_description`) VALUES
(1, 'Lead Author'),
(2, 'Contributing Author'),
(3, 'Editor'),
(4, 'Co-Editor'),
(5, 'Senior Author'),
(6, 'Co-Lead');

CREATE TABLE IF NOT EXISTS `global_lu_schools` (
  `schools_id` int(11) NOT NULL AUTO_INCREMENT,
  `school_title` varchar(250) NOT NULL,
  PRIMARY KEY (`schools_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `global_lu_schools` (`schools_id`, `school_title`) VALUES
(1, 'University of Alberta'),
(2, 'University of British Columbia'),
(3, 'University of Calgary'),
(4, 'Dalhousie University'),
(5, 'Laval University'),
(6, 'University of Manitoba'),
(7, 'McGill University'),
(8, 'McMaster University'),
(9, 'Memorial University of Newfoundland'),
(10, 'Universite de Montreal'),
(11, 'Northern Ontario School of Medicine'),
(12, 'University of Ottawa'),
(13, 'Queen''s University'),
(14, 'University of Saskatchewan'),
(15, 'Universite de Sherbrooke'),
(16, 'University of Toronto'),
(17, 'University of Western Ontario');

CREATE TABLE IF NOT EXISTS `notices` (
  `notice_id` int(12) NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) DEFAULT NULL,
  `notice_summary` text NOT NULL,
  `notice_details` text NOT NULL,
  `display_from` bigint(64) NOT NULL DEFAULT '0',
  `display_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`notice_id`),
  KEY `display_from` (`display_from`),
  KEY `display_until` (`display_until`),
  KEY `organisation_id` (`organisation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `notice_audience` (
  `naudience_id` int(11) NOT NULL AUTO_INCREMENT,
  `notice_id` int(11) NOT NULL,
  `audience_type` varchar(20) NOT NULL,
  `audience_value` int(11) NOT NULL DEFAULT '0',
  `updated_by` int(11) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`naudience_id`),
  KEY `audience_id` (`notice_id`,`audience_type`,`audience_value`,`updated_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `objective_organisation` (
  `objective_id` int(12) NOT NULL,
  `organisation_id` int(12) NOT NULL,
  PRIMARY KEY (`objective_id`,`organisation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `objective_organisation` SELECT `objective_id`, 1 FROM `global_lu_objectives`;

CREATE TABLE IF NOT EXISTS `permissions` (
  `permission_id` int(12) NOT NULL AUTO_INCREMENT,
  `assigned_by` int(12) NOT NULL DEFAULT '0',
  `assigned_to` int(12) NOT NULL DEFAULT '0',
  `valid_from` bigint(64) NOT NULL DEFAULT '0',
  `valid_until` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`permission_id`),
  KEY `assigned_by` (`assigned_by`),
  KEY `assigned_to` (`assigned_to`),
  KEY `valid_from` (`valid_from`),
  KEY `valid_until` (`valid_until`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `poll_answers` (
  `answer_id` int(12) NOT NULL AUTO_INCREMENT,
  `poll_id` int(12) NOT NULL DEFAULT '0',
  `answer_text` varchar(255) NOT NULL,
  `answer_order` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`answer_id`),
  KEY `poll_id` (`poll_id`),
  KEY `answer_order` (`answer_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `poll_questions` (
  `poll_id` int(12) NOT NULL AUTO_INCREMENT,
  `poll_target_type` enum('group', 'grad_year', 'cohort') NOT NULL,
  `poll_target` varchar(32) NOT NULL DEFAULT 'all',
  `poll_question` text NOT NULL,
  `poll_from` bigint(64) NOT NULL DEFAULT '0',
  `poll_until` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`poll_id`),
  KEY `poll_target` (`poll_target`),
  KEY `poll_from` (`poll_from`),
  KEY `poll_until` (`poll_until`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `poll_results` (
  `result_id` int(12) NOT NULL AUTO_INCREMENT,
  `poll_id` int(12) NOT NULL DEFAULT '0',
  `answer_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `ip` varchar(64) NOT NULL,
  `timestamp` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`result_id`),
  KEY `poll_id` (`poll_id`),
  KEY `answer_id` (`answer_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `quizzes` (
  `quiz_id` int(12) NOT NULL AUTO_INCREMENT,
  `quiz_title` varchar(64) NOT NULL,
  `quiz_description` text NOT NULL,
  `quiz_active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`quiz_id`),
  KEY `quiz_active` (`quiz_active`),
  FULLTEXT KEY `quiz_title` (`quiz_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `quizzes_lu_questiontypes` (
  `questiontype_id` int(12) NOT NULL AUTO_INCREMENT,
  `questiontype_title` varchar(64) NOT NULL,
  `questiontype_description` text NOT NULL,
  `questiontype_active` int(1) NOT NULL DEFAULT '1',
  `questiontype_order` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`questiontype_id`),
  KEY `questiontype_active` (`questiontype_active`,`questiontype_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `quizzes_lu_questiontypes` (`questiontype_id`, `questiontype_title`, `questiontype_description`, `questiontype_active`, `questiontype_order`) VALUES
(1, 'Multiple Choice Question', '', 1, 0);

CREATE TABLE IF NOT EXISTS `quizzes_lu_quiztypes` (
  `quiztype_id` int(12) NOT NULL AUTO_INCREMENT,
  `quiztype_code` varchar(12) NOT NULL,
  `quiztype_title` varchar(64) NOT NULL,
  `quiztype_description` text NOT NULL,
  `quiztype_active` int(1) NOT NULL DEFAULT '1',
  `quiztype_order` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`quiztype_id`),
  KEY `quiztype_active` (`quiztype_active`,`quiztype_order`),
  KEY `quiztype_code` (`quiztype_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `quizzes_lu_quiztypes` (`quiztype_id`, `quiztype_code`, `quiztype_title`, `quiztype_description`, `quiztype_active`, `quiztype_order`) VALUES
(1, 'delayed', 'Delayed Quiz Results', 'This option restricts the learners ability to review the results of a quiz attempt (i.e. score, correct / incorrect responses, and question feedback) until after the time release period has expired.', 1, 0),
(2, 'immediate', 'Immediate Quiz Results', 'This option will allow the learner to review the results of a quiz attempt (i.e. score, correct / incorrect responses, and question feedback) immediately after they complete the quiz.', 1, 1);

CREATE TABLE IF NOT EXISTS `quiz_contacts` (
  `qcontact_id` int(12) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`qcontact_id`),
  KEY `quiz_id` (`quiz_id`,`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `quiz_questions` (
  `qquestion_id` int(12) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(12) NOT NULL DEFAULT '0',
  `questiontype_id` int(12) NOT NULL DEFAULT '1',
  `question_text` longtext NOT NULL,
  `question_points` int(6) NOT NULL DEFAULT '0',
  `question_order` int(6) NOT NULL DEFAULT '0',
  `question_active` int(1) NOT NULL DEFAULT '1',
  `randomize_responses` int(1) NOT NULL,
  PRIMARY KEY (`qquestion_id`),
  KEY `quiz_id` (`quiz_id`,`questiontype_id`,`question_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `quiz_question_responses` (
  `qqresponse_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `qquestion_id` int(12) unsigned NOT NULL,
  `response_text` longtext NOT NULL,
  `response_order` int(3) unsigned NOT NULL,
  `response_correct` enum('0','1') NOT NULL DEFAULT '0',
  `response_is_html` enum('0','1') NOT NULL,
  `response_feedback` text NOT NULL,
  `response_active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`qqresponse_id`),
  KEY `qquestion_id` (`qquestion_id`,`response_order`,`response_correct`),
  KEY `response_is_html` (`response_is_html`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `reports_aamc_ci` (
  `raci_id` int(12) NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) NOT NULL,
  `report_title` varchar(255) NOT NULL,
  `report_date` bigint(64) NOT NULL DEFAULT '0',
  `report_start` bigint(64) NOT NULL DEFAULT '0',
  `report_finish` bigint(64) NOT NULL DEFAULT '0',
  `report_langauge` varchar(12) NOT NULL DEFAULT 'en-us',
  `report_description` text NOT NULL,
  `report_supporting_link` text NOT NULL,
  `report_active` tinyint(1) NOT NULL DEFAULT '1',
  `report_status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`raci_id`),
  KEY `report_date` (`report_date`),
  KEY `report_active` (`organisation_id`,`report_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `settings` (
  `shortname` varchar(64) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`shortname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `settings` (`shortname`, `value`) VALUES
('version_db', '1400'),
('version_entrada', '1.4.0');

CREATE TABLE IF NOT EXISTS `statistics` (
  `statistic_id` int(12) NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `timestamp` bigint(64) NOT NULL DEFAULT '0',
  `module` varchar(64) NOT NULL DEFAULT 'undefined',
  `action` varchar(64) NOT NULL DEFAULT 'undefined',
  `action_field` varchar(64) DEFAULT NULL,
  `action_value` varchar(64) DEFAULT NULL,
  `prune_after` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`statistic_id`),
  KEY `proxy_id` (`proxy_id`,`timestamp`,`module`,`action`,`action_field`,`action_value`),
  KEY `proxy_id_2` (`proxy_id`),
  KEY `timestamp` (`timestamp`),
  KEY `module` (`module`,`action`,`action_field`,`action_value`),
  KEY `action` (`action`),
  KEY `action_field` (`action_field`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `statistics_archive` (
  `statistic_id` int(12) NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `timestamp` bigint(64) NOT NULL DEFAULT '0',
  `module` varchar(64) NOT NULL DEFAULT 'undefined',
  `action` varchar(64) NOT NULL DEFAULT 'undefined',
  `action_field` varchar(64) DEFAULT NULL,
  `action_value` varchar(64) DEFAULT NULL,
  `prune_after` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`statistic_id`),
  KEY `proxy_id` (`proxy_id`,`timestamp`,`module`,`action`,`action_field`,`action_value`),
  KEY `proxy_id_2` (`proxy_id`),
  KEY `timestamp` (`timestamp`),
  KEY `module` (`module`,`action`,`action_field`,`action_value`),
  KEY `action` (`action`),
  KEY `action_field` (`action_field`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_awards_external` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL default '',
  `year` year(4) NOT NULL,
  `awarding_body` varchar(4096) NOT NULL,
  `award_terms` mediumtext NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  `comment` varchar(4096) default NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_awards_internal` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `award_id` int(11) NOT NULL,
  `year` year(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_awards_internal_types` (
  `id` int(11) NOT NULL auto_increment,
  `award_terms` mediumtext NOT NULL,
  `title` varchar(200) NOT NULL default '',
  `disabled` tinyint(1) NOT NULL default '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `title_unique` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_clineval_comments` (
  `id` int(11) NOT NULL auto_increment,
  `source` varchar(4096) NOT NULL,
  `comment` mediumtext NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_community_health_and_epidemiology` (
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `organization` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `supervisor` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  `comment` varchar(500) default NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_contributions` (
  `id` int(11) NOT NULL auto_increment,
  `role` varchar(4096) NOT NULL,
  `org_event` varchar(256) NOT NULL default '',
  `date` varchar(256) NOT NULL default '',
  `status` tinyint(1) NOT NULL default '0',
  `user_id` int(11) NOT NULL,
  `start_month` int(11) default NULL,
  `start_year` int(11) default NULL,
  `end_month` int(11) default NULL,
  `end_year` int(11) default NULL,
  `comment` varchar(4096) default NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_critical_enquiries` (
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `organization` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `supervisor` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  `comment` varchar(4096) default NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_disciplinary_actions` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `action_details` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_formal_remediations` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `remediation_details` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_international_activities` (
  `id` int(11) NOT NULL auto_increment,
  `student_id` int(11) NOT NULL,
  `title` varchar(256) NOT NULL,
  `location` varchar(256) NOT NULL,
  `site` varchar(256) NOT NULL,
  `start` timestamp NOT NULL default '0000-00-00 00:00:00',
  `end` timestamp NULL default NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_leaves_of_absence` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `absence_details` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_mspr` (
  `user_id` int(11) default NULL,
  `last_update` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,
  `generated` bigint(64) default NULL,
  `closed` bigint(64) default NULL,
  `carms_number` int(10) unsigned default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_mspr_class` (
  `year` int(11) NOT NULL default '0',
  `closed` int(11) default NULL,
  PRIMARY KEY (`year`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_observerships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `title` varchar(256) NOT NULL,
  `location` varchar(256) NOT NULL,
  `site` varchar(256) NOT NULL,
  `start` int(11) NOT NULL,
  `end` int(11) DEFAULT NULL,
  `preceptor_prefix` varchar(4) DEFAULT NULL,
  `preceptor_firstname` varchar(256) DEFAULT NULL,
  `preceptor_lastname` varchar(256) DEFAULT NULL,
  `preceptor_proxy_id` int(12) unsigned DEFAULT NULL,
  `preceptor_email` varchar(255) DEFAULT NULL,
  `status` enum('UNCONFIRMED','CONFIRMED','REJECTED') NOT NULL DEFAULT 'UNCONFIRMED',
  `unique_id` varchar(64) DEFAULT NULL,
  `notice_sent` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_research` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `citation` varchar(4096) NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  `priority` tinyint(4) NOT NULL default '0',
  `comment` varchar(4096) default NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_studentships` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `title` varchar(4096) NOT NULL,
  `year` year(4) NOT NULL default '0000',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_student_run_electives` (
  `id` int(11) NOT NULL auto_increment,
  `group_name` varchar(255) NOT NULL,
  `university` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_month` tinyint(2) unsigned default NULL,
  `start_year` smallint(4) unsigned default NULL,
  `end_month` tinyint(2) unsigned default NULL,
  `end_year` smallint(4) unsigned default NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users_online` (
  `session_id` varchar(32) NOT NULL,
  `ip_address` varchar(32) NOT NULL,
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `username` varchar(32) NOT NULL,
  `firstname` varchar(35) NOT NULL,
  `lastname` varchar(35) NOT NULL,
  `timestamp` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`session_id`),
  KEY `ip_address` (`ip_address`),
  KEY `proxy_id` (`proxy_id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tasks` (
  `task_id` int(12) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `description` text,
  `release_start` bigint(64) default NULL,
  `release_finish` bigint(64) default NULL,
  `duration` bigint(64) default NULL,
  `updated_date` bigint(64) default NULL,
  `updated_by` int(12) unsigned default NULL,
  `deadline` bigint(64) default NULL,
  `organisation_id` int(12) unsigned NOT NULL,
  `verification_type` enum('faculty','other','none') NOT NULL default 'none',
  `faculty_selection_policy` enum('off','allow','require') NOT NULL default 'allow',
  `completion_comment_policy` enum('no_comments','require_comments','allow_comments') NOT NULL default 'allow_comments',
  `rejection_comment_policy` enum('no_comments','require_comments','allow_comments') NOT NULL default 'allow_comments',
  `verification_notification_policy` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY (`task_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `task_associated_faculty` (
  `task_id` int(12) unsigned NOT NULL,
  `faculty_id` int(12) unsigned NOT NULL,
  PRIMARY KEY (`task_id`,`faculty_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `task_completion` (
  `task_id` int(12) unsigned NOT NULL,
  `verifier_id` int(12) unsigned default NULL,
  `verified_date` bigint(64) default NULL,
  `recipient_id` int(12) unsigned NOT NULL,
  `completed_date` bigint(64) default NULL,
  `faculty_id` int(12) unsigned default NULL,
  `completion_comment` text,
  `rejection_comment` text,
  `rejection_date` bigint(64) default NULL,
  PRIMARY KEY (`task_id`,`recipient_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `task_owners` (
  `task_id` int(12) unsigned NOT NULL default '0',
  `owner_id` int(12) unsigned NOT NULL default '0',
  `owner_type` enum('course','event','user') NOT NULL default 'course',
  PRIMARY KEY (`task_id`,`owner_id`,`owner_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `task_recipients` (
  `task_id` int(12) unsigned NOT NULL,
  `recipient_type` enum('user','group','grad_year','cohort','organisation') NOT NULL,
  `recipient_id` int(12) unsigned NOT NULL,
  PRIMARY KEY (`task_id`,`recipient_type`,`recipient_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `task_verifiers` (
  `task_id` int(12) unsigned NOT NULL,
  `verifier_id` int(12) unsigned NOT NULL,
  PRIMARY KEY (`task_id`,`verifier_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `meta_types` (
  `meta_type_id` int(10) unsigned NOT NULL auto_increment,
  `label` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `parent_type_id` int(10) unsigned default NULL,
  PRIMARY KEY (`meta_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
  PRIMARY KEY (`meta_data_relation_id`),
  UNIQUE KEY `meta_type_id` (`meta_type_id`,`entity_type`,`entity_value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
  PRIMARY KEY (`meta_value_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mtd_categories` (
  `id` int(11) NOT NULL ,
  `category_code` varchar(3) NOT NULL,
  `category_description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `mtd_facilities` (
  `id` int(11) NOT NULL ,
  `facility_code` int(3) NOT NULL,
  `facility_name` varchar(50) NOT NULL,
  `kingston` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `mtd_locale_duration` (
  `id` int(11) NOT NULL ,
  `location_id` int(3) NOT NULL,
  `percent_time` int(3) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `mtd_moh_program_codes` (
  `id` int(11) NOT NULL ,
  `program_code` varchar(3) NOT NULL,
  `program_description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `mtd_moh_service_codes` (
  `id` int(11) NOT NULL ,
  `service_code` varchar(3) NOT NULL,
  `service_description` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `mtd_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `service_id` int(3) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `creator_id` int(12) NOT NULL,
  `type_code` varchar(1) NOT NULL,
  `updated_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(12) NOT NULL,
  `category_id` int(3) DEFAULT NULL,
  `home_program_id` int(3) DEFAULT NULL,
  `home_school_id` int(3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mtd_schools` (
  `id` int(11) NOT NULL ,
  `school_code` varchar(3) NOT NULL,
  `school_description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `mtd_pgme_moh_programs` (
  `id` int(11) NOT NULL ,
  `pgme_program_name` varchar(100) NOT NULL,
  `moh_service_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `mtd_type` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `type_code` varchar(1) NOT NULL,
  `type_description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `mtd_type` (`id`, `type_code`, `type_description`) VALUES
(1, 'I', 'in-patient/emergency'),
(2, 'O', 'out-patient');

CREATE TABLE IF NOT EXISTS `eventtype_organisation`(
`eventtype_id` INT(12) NOT NULL,
`organisation_id` INT(12) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `eventtype_organisation` SELECT `eventtype_id`, 1 FROM `events_lu_eventtypes`;

CREATE TABLE IF NOT EXISTS `groups` (
  `group_id` int(12) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  `group_type` enum('course_list','cohort') NOT NULL DEFAULT 'course_list',
  `group_value` int(12) DEFAULT NULL,
  `start_date` bigint(64) DEFAULT NULL,
  `expire_date` bigint(64) DEFAULT NULL,
  `group_active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`group_id`),
  FULLTEXT KEY `group_title` (`group_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `groups` (`group_id`, `group_name`, `group_type`, `group_active`, `updated_date`, `updated_by`)	VALUES
(1, CONCAT('Class of ', YEAR(CURRENT_DATE())), 'cohort', 1, UNIX_TIMESTAMP(), 1),
(2, CONCAT('Class of ', YEAR(CURRENT_DATE())+1), 'cohort', 1, UNIX_TIMESTAMP(), 1),
(3, CONCAT('Class of ', YEAR(CURRENT_DATE())+2), 'cohort', 1, UNIX_TIMESTAMP(), 1),
(4, CONCAT('Class of ', YEAR(CURRENT_DATE())+3), 'cohort', 1, UNIX_TIMESTAMP(), 1),
(5, CONCAT('Class of ', YEAR(CURRENT_DATE())+4), 'cohort', 1, UNIX_TIMESTAMP(), 1);

CREATE TABLE IF NOT EXISTS `group_members` (
  `gmember_id` int(12) NOT NULL AUTO_INCREMENT,
  `group_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `start_date` BIGINT(64) NOT NULL DEFAULT '0',
  `finish_date` BIGINT(64) NOT NULL DEFAULT '0',
  `member_active` int(1) NOT NULL DEFAULT '1',
  `entrada_only` INT(1) DEFAULT 0,
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`gmember_id`),
  KEY `group_id` (`group_id`,`proxy_id`,`updated_date`,`updated_by`),
  KEY `member_active` (`member_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `group_organisations` (
  `gorganisation_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `organisation_id` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`gorganisation_id`),
  KEY `group_id` (`group_id`,`organisation_id`,`updated_date`,`updated_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `group_organisations` (`gorganisation_id`, `group_id`, `organisation_id`, `updated_by`, `updated_date`) VALUES
(1, 1, 1, 1, UNIX_TIMESTAMP()),
(2, 2, 1, 1, UNIX_TIMESTAMP()),
(3, 3, 1, 1, UNIX_TIMESTAMP()),
(4, 4, 1, 1, UNIX_TIMESTAMP()),
(5, 5, 1, 1, UNIX_TIMESTAMP());

CREATE TABLE IF NOT EXISTS `pg_eval_response_rates` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `program_name` varchar(100) NOT NULL,
  `response_type` varchar(20) NOT NULL,
  `completed` int(10) NOT NULL,
  `distributed` int(10) NOT NULL,
  `percent_complete` int(3) NOT NULL,
   `gen_date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pg_one45_community` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `one45_name` varchar(50) NOT NULL,
  `community_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pg_blocks` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `block_name` varchar(8) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `year` varchar(9) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `pg_blocks` (`id`, `block_name`, `start_date`, `end_date`, `year`) VALUES
(1, '1', '2010-07-01', '2010-07-26', '2010-2011'),
(2, '2', '2010-07-27', '2010-08-23', '2010-2011'),
(3, '3', '2010-08-24', '2010-09-20', '2010-2011'),
(4, '4', '2010-09-21', '2010-10-18', '2010-2011'),
(5, '5', '2010-10-19', '2010-11-15', '2010-2011'),
(6, '6', '2010-11-16', '2010-12-13', '2010-2011'),
(7, '7', '2010-12-14', '2011-01-10', '2010-2011'),
(8, '8', '2011-01-11', '2011-02-07', '2010-2011'),
(9, '9', '2011-02-08', '2011-03-07', '2010-2011'),
(10, '10', '2011-03-08', '2011-04-04', '2010-2011'),
(11, '11', '2011-04-05', '2011-05-02', '2010-2011'),
(12, '12', '2011-05-03', '2011-05-30', '2010-2011'),
(13, '13', '2011-05-31', '2011-06-30', '2010-2011'),
(14, '1', '2011-07-01', '2011-08-01', '2011-2012'),
(15, '2', '2011-08-02', '2011-08-29', '2011-2012'),
(16, '3', '2011-08-30', '2011-09-26', '2011-2012'),
(17, '4', '2011-09-27', '2011-10-24', '2011-2012'),
(18, '5', '2011-10-25', '2011-11-21', '2011-2012'),
(19, '6', '2011-11-22', '2011-12-19', '2011-2012'),
(20, '7', '2012-12-20', '2012-01-16', '2011-2012'),
(21, '8', '2012-01-17', '2012-02-13', '2011-2012'),
(22, '9', '2012-02-14', '2012-03-12', '2011-2012'),
(23, '10', '2012-03-13', '2012-04-09', '2011-2012'),
(24, '11', '2012-04-10', '2012-05-07', '2011-2012'),
(25, '12', '2012-05-08', '2012-06-04', '2011-2012'),
(26, '13', '2012-06-05', '2012-06-30', '2011-2012');

CREATE TABLE IF NOT EXISTS `topic_organisation`(
  `topic_id` INT(12) NOT NULL,
  `organisation_id` INT(12) NOT NULL,
  PRIMARY KEY(`topic_id`,`organisation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `topic_organisation` SELECT `topic_id`, 1 FROM `events_lu_topics`;

CREATE TABLE IF NOT EXISTS `curriculum_lu_levels` (
  `curriculum_level_id` int(11) unsigned NOT NULL auto_increment,
  `curriculum_level` varchar(100) NOT NULL default '',
  PRIMARY KEY (`curriculum_level_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `curriculum_lu_levels` (`curriculum_level_id`, `curriculum_level`) VALUES
(1, 'Undergraduate'),
(2, 'Postgraduate');

CREATE TABLE IF NOT EXISTS `map_assessments_meta` (
  `map_assessments_meta_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fk_assessment_method_id` int(11) NOT NULL,
  `fk_assessments_meta_id` int(11) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`map_assessments_meta_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `map_event_resources` (
  `map_event_resources_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fk_medbiq_resource_id` int(11) DEFAULT NULL,
  `fk_resource_id` int(11) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`map_event_resources_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `map_events_eventtypes` (
  `map_events_eventtypes_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fk_instructional_method_id` int(11) NOT NULL,
  `fk_eventtype_id` int(11) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`map_events_eventtypes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `medbiq_assessment_methods` (
  `assessment_method_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `assessment_method` varchar(250) NOT NULL DEFAULT '',
  `assessment_method_description` text,
  `active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`assessment_method_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `medbiq_assessment_methods` (`assessment_method_id`, `assessment_method`, `assessment_method_description`, `active`, `updated_date`, `updated_by`) VALUES
(1,'Clinical Documentation Review','The review and assessment of clinical notes and logs kept by learners as part of practical training in the clinical setting (Bowen & Smith, 2010; Irby, 1995)',1,0,0),
(2,'Clinical Performance Rating/Checklist','A non-narrative assessment tool (checklist, Likert-type scale, other instrument) used to note completion or\rachievement of learning tasks (MacRae, Vu, Graham, Word-Sims, Colliver, & Robbs, 1995; Turnbull, Gray, & MacFadyen, 1998) also see ?Direct Observations or Performance Audits,? Institute for International Medical Education, 2002)',1,0,0),
(3,'Exam - Institutionally Developed, Clinical Performance','Practical performance-based examination developed internally to assess problem solving, clinical reasoning, decision making, and[/or] communication skills (LCME, 2011) (Includes observation of learner or small group by instructor)',1,0,0),
(4,'Exam - Institutionally Developed, Written/Computer-based','Examination utilizing various written question-and-answer formats (multiple-choice, short answer, essay, etc.) which may assess learners\' factual knowledge retention; application of knowledge, concepts, and principles; problem-solving acumen; and clinical reasoning (Cooke, Irby, & O?Brien, 2010b; LCME, 2011)',1,0,0),
(5,'Exam - Institutionally Developed, Oral','Verbal examination developed internally to assess problem solving, clinical reasoning, decision making, and[/or] communication skills (LCME, 2011)',1,0,0),
(6,'Exam - Licensure, Clinical Performance','Practical, performance-based examination developed by a professional licensing body to assess clinical skills such as problem solving, clinical reasoning, decision making, and communication, for licensure to practice in a given jurisdiction (e.g., USMLE for the United States); typically paired with a written/computer-based component (MCC, 2011a & 2011c; NBOME, 2010b; USMLE, n.d.); may also be used by schools to assess learners? achievement of certain curricular objectives',1,0,0),
(7,'Exam - Licensure, Written/Computer-based','Standardized written examination administered to assess learners\' factual knowledge retention; application of knowledge, concepts, and principles; problem-solving acumen; and clinical reasoning, for licensure to practice in a given jurisdiction (e.g., USMLE for the United States); typically paired with a clinical performance component (MCC, 2011a & 2011b; NBOME, 2010b; USMLE, n.d.); may also be used by schools or learners themselves to assess achievement of certain curricular objectives',1,0,0),
(8,'Exam - Nationally Normed/Standardized, Subject','Standardized written examination administered to assess learners? achievement of nationally established educational expectations for various levels of training and/or specialized subject area(s) (e.g., NBME Subject or ?Shelf? Exam) (NBME, 2011; NBOME, 2010a)',1,0,0),
(9,'Multisource Assessment','A formal assessment of performance by supervisors, peers, patients, and coworkers (Bowen & Smith, 2010; Institute for International Medical Education, 2002) (Also see Peer Assessment)',1,0,0),
(10,'Narrative Assessment','An instructor\'s or observer\'s written subjective assessment of a learner\'s work or performance (Mennin, McConnell, & Anderson, 1997); May Include: Comments within larger assessment; Observation of learner or small group by instructor',1,0,0),
(11,'Oral Patient Presentation','The presentation of clinical case (patient) findings, history and physical, differential diagnosis, treatment plan, etc., by a learner to an instructor or small group, and subsequent discussion with the instructor and/or small group for the purposes of learner demonstrating skills in clinical reasoning, problem-solving, etc.\r(Wiener, 1974)',1,0,0),
(12,'Participation','Sharing or taking part in an activity (Education Resources Information Center, 1966b)',1,0,0),
(13,'Peer Assessment','The concurrent or retrospective review by learners of the quality and efficiency of practices or services ordered or performed by fellow learners (based on MeSH Scope Note for \"Peer Review, Health Care,\" U.S. National Library of Medicine, 1992)',1,0,0),
(14,'Portfolio-Based Assessment','Review of a learner\'s achievement of agreed-upon academic objectives or completion of a negotiated set of learning activities, based on a learner portfolio (Institute for International Medical Education, 2002) (\"a systematic collection of a student\'s work samples, records of observation, test results, etc., over a period of time\"? Education Resources Information Center, 1994)',1,0,0),
(15,'Practical (Lab)','Learner engagement in hands-on or simulated exercises in which they collect or use data to test and/or verify hypotheses or to address questions about principles and/or phenomena (LCME, 2011)',1,0,0),
(16,'Research or Project Assessment','Assessment of activities and outcomes (e.g., posters, presentations, reports, etc.) of a project in which the learner participated or conducted research (Dyrbye, Davidson, & Cook, 2008)',1,0,0),
(17,'Self-Assessment','The process of evaluating one?s own deficiencies, achievements, behavior or professional performance and competencies (Institute for International Medical Education, 2002); Assessment completed by the learner to reflect and critically assess his/her own performance against a set of established criteria (Gordon, 1991) (NOTE: Does not refer to NBME Self-Assessment)',1,0,0),
(18,'Stimulated Recall','The use of various stimuli (e.g., written records, audio tapes, video tapes) to re-activate the experience of a learner during a learning activity or clinical encounter in order to reflect on task performance, reasoning, decision-making, interpersonal skills, personal thoughts and feelings, etc. (Barrows, 2000)',1,0,0);

CREATE TABLE IF NOT EXISTS `medbiq_instructional_methods` (
  `instructional_method_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `instructional_method` varchar(250) NOT NULL DEFAULT '',
  `instructional_method_description` text,
  `active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`instructional_method_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `medbiq_instructional_methods` (`instructional_method_id`, `instructional_method`, `instructional_method_description`, `active`, `updated_date`, `updated_by`) VALUES
(1,'Case-Based Instruction/Learning','The use of patient cases (actual or theoretical) to stimulate discussion, questioning, problem solving, and reasoning on issues pertaining to the basic sciences and clinical disciplines (Anderson, 2010)',1,0,0),
(2,'Clinical Experience - Ambulatory','Practical experience(s) in patient care and health-related services carried out in an ambulatory/outpatient\rsetting (LCME, 2011) where actual symptoms are studied and treatment is given (Education Resources Information Center, 1968 & 1981)',1,0,0),
(3,'Clinical Experience - Inpatient','Practical experience(s) in patient care and health-related services carried out in an inpatient setting (LCME, 2011) where actual symptoms are studied and treatment is given (Education Resources Information Center, 1968 & 1981)',1,0,0),
(4,'Concept Mapping','Technique [that] allows learners to organize and represent knowledge in an explicit interconnected network. Linkages between concepts are explored to make apparent connections that are not usually seen. Concept mapping also encourages the asking of questions about relationships between concepts that may not have been presented in traditional courses, standard texts, and teaching materials. It shifts the focus of learning away from rote acquisition of information to visualizing the underlying concepts that provide the cognitive\rframework of what the learner already knows, to facilitate the acquisition of new knowledge (Weiss & Levinson, 2000, citing Novak & Gowin, 1984)',1,0,0),
(5,'Conference','Departmentally-driven and/or content-specific presentations by clinical faculty/professionals, residents,\rand/or learners before a large group of other professionals and/or learners (e.g., Mortality and Morbidity, or \"M & M,\" Conference--Biddle & Oaster, 1990--and Interdisciplinary Conference--Feldman, 1999; also see Cooke, Irby, & O\'Brien, 2010b)',1,0,0),
(6,'Demonstration','A description, performance, or explanation of a process, illustrated by examples, observable action, specimens, etc. (Dictionary.com, n.d.)',1,0,0),
(7,'Discussion, Large Group (>13)','An exchange (oral or written) of opinions, observations, or ideas among a Large Group [more than 12\rparticipants], usually to analyze, clarify, or reach conclusions about issues, questions, or problems (Education Resources Information Center, 1980)',1,0,0),
(8,'Discussion, Small Group (<12)','An exchange (oral or written) of opinions, observations, or ideas among a Small Group [12 or fewer participants], usually to analyze, clarify, or reach conclusions about issues, questions, or problems (Education Resources Information Center, 1980)',1,0,0),
(9,'Games','Individual or group games that have cognitive, social, behavioral, and/or emotional, etc., dimensions which are related to educational objectives (Education Resources Information Center, 1966a)',1,0,0),
(10,'Independent Learning','Instructor-/ or mentor-guided learning activities to be performed by the learner outside of formal educational settings (classroom, lab, clinic) (Bowen & Smith, 2010); Dedicated time on learner schedules to prepare for specific learning activities, e.g., case discussions, TBL, PBL, clinical activities, research project(s)',1,0,0),
(11,'Journal Club','A forum in which participants discuss recent research papers from field literature in order to develop\rcritical reading skills (comprehension, analysis, and critique) (Cooke, Irby, & O\'Brien, 2010a; Mann & O\'Neill, 2010; Woods & Winkel, 1982)',1,0,0),
(12,'Laboratory','Hands-on or simulated exercises in which learners collect or use data to test and/or verify hypotheses or to address questions about principles and/or phenomena (LCME, 2011)',1,0,0),
(13,'Lecture','An instruction or verbal discourse by a speaker before a large group of learners (Institute for International Medical Education, 2002)',1,0,0),
(14,'Mentorship','The provision of guidance, direction and support by senior professionals to learners or more junior professionals (U.S. National Library of Medicine, 1987)',1,0,0),
(15,'Patient Presentation - Faculty','A presentation by faculty of patient findings, history and physical, differential diagnosis, treatment plan,\retc. (Wiener, 1974)',1,0,0),
(16,'Patient Presentation - Learner','A presentation by a learner or learners to faculty, resident(s), and/or other learners of patient findings, history and physical, differential diagnosis, treatment plan, etc. (Wiener, 1974)',1,0,0),
(17,'Peer Teaching','Learner-to-learner instruction for the mutual learning experience of both \"teacher\" and \"learner\"; may be \"peer-to-peer\" (same training level) or \"near-peer\" (higher-level learner teaching lower-level learner)\r(Soriano et al., 2010)',1,0,0),
(18,'Preceptorship','Practical experience in medical and health-related services wherein the professionally-trained learner works\runder the supervision of an established professional in the particular field (U. S. National Library of Medicine, 1974)',1,0,0),
(19,'Problem-Based Learning (PBL)','The use of carefully selected and designed patient cases that demand from the learner acquisition of critical\rknowledge, problem solving proficiency, self-directed learning strategies, and team participation skills as those needed in professional practice (Eshach & Bitterman, 2003; see also Major & Palmer, 2001; Cooke, Irby, & O\'Brien, 2010b;\rBarrows & Tamblyn, 1980)',1,0,0),
(20,'Reflection','Examination by the learner of his/her personal experiences of a learning event, including the cognitive, emotional, and affective aspects; the use of these past experiences in combination with objective information\rto inform present clinical decision-making and problem-solving (Mann, Gordon, & MacLeod, 2009; Mann & O\'Neill, 2010)',1,0,0),
(21,'Research','Short-term or sustained participation in research',1,0,0),
(22,'Role Play/Dramatization','The adopting or performing the role or activities of another individual',1,0,0),
(23,'Self-Directed Learning','Learners taking the initiative for their own learning: diagnosing needs, formulating goals, identifying resources, implementing appropriate activities, and evaluating outcomes (Garrison, 1997; Spencer & Jordan, 1999)',1,0,0),
(24,'Service Learning Activity','A structured learning experience that combines community service with preparation and reflection (LCME, 2011)',1,0,0),
(25,'Simulation','A method used to replace or amplify real patient encounters with scenarios designed to replicate real health care situations, using lifelike mannequins, physical models, standardized patients, or computers (Passiment,\rSacks, & Huang, 2011)',1,0,0),
(26,'Team-Based Learning (TBL)','A form of collaborative learning that follows a specific sequence of individual work, group work and immediate feedback; engages learners in learning activities within a small group that works independently in classes with high learner-faculty ratios (Anderson, 2010; Team-Based Learning Collaborative, n.d.; Thompson, Schneider, Haidet, Perkowski, & Richards, 2007)',1,0,0),
(27,'Team-Building','Workshops, sessions, and/or activities contributing to the development of teamwork skills, often as a foundation for group work in learning (PBL, TBL, etc.) and practice (interprofessional/-disciplinary, etc.)\r(Morrison, Goldfarb, & Lanken, 2010)',1,0,0),
(28,'Tutorial','Instruction provided to a learner or small group of learners by direct interaction with an instructor (Education\rResources Information Center, 1966c)',1,0,0),
(29,'Ward Rounds','An instructional session conducted in an actual clinical setting, using real patients or patient cases to demonstrate procedures or clinical skills, illustrate clinical reasoning and problem-solving, or stimulate discussion and analytical thinking among a group of learners (Bowen & Smith, 2010; Wiener, 1974)',1,0,0),
(30,'Workshop','A brief intensive educational program for a relatively small group of people that focuses especially on techniques and skills related to a specific topic (U. S. National Library of Medicine, 2011)',1,0,0);

CREATE TABLE IF NOT EXISTS `medbiq_resources` (
  `resource_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `resource` varchar(250) NOT NULL DEFAULT '',
  `resource_description` text,
  `active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `medbiq_resources` (`resource_id`, `resource`, `resource_description`, `active`, `updated_date`, `updated_by`) VALUES
(1,'Audience Response System','An electronic communication system that allows groups of people to vote on a topic or answer a question. Each person has a remote control (\"clicker\") with which selections can be made; Typically, the results are\rinstantly made available to the participants via a graph displayed on the projector. (Group on Information Resources, 2011; Stoddard & Piquette, 2010)',1,0,0),
(2,'Audio','Devices or applications used to acquire or transfer knowledge, attitudes, or skills through study, instruction, or experience using auditory delivery (see \"Electronic Learning,\" Education Resources Information Center, 2008b)',1,0,0),
(3,'Cadaver','A human body preserved post-mortem and \"used...to study anatomy, identify disease sites, determine causes of death, and provide tissue to repair a defect in a living human being\" (MedicineNet.com, 2004)',1,0,0),
(4,'Clinical Correlation','The application and elaboration of concepts introduced in lecture, reading assignments, independent study, and other learning activities to real patient or case scenarios in order to promote knowledge retrieval in similar clinical situations at a later time (Euliano, 2001)',1,0,0),
(5,'Distance Learning - Asynchronous','Education facilitated through communications media (often electronic), with little or no classroom or other face-to-face contact between learners and teachers, and which \"does not occur in real time or involve simultaneous interaction on the part of participants. It is intermittent and generally characterized by a significant time delay or interval between sending and receiving or responding to messages\" (Education Resources Information Center, 1983; 2008a)',1,0,0),
(6,'Distance Learning - Synchronous','Education facilitated through communications media (often electronic), with little or no classroom or other face-to-face contact between learners and teachers, \"in real time, characterized by concurrent exchanges between participants. Interaction is simultaneous without a meaningful time delay between sending a message and receiving or responding to it. Occurs in electronic (e.g., interactive videoconferencing) and non-electronic environments (e.g., telephone conversations)\" (Education Resources Information Center, 1983; 2008c)',1,0,0),
(7,'Educational Technology','Mobile or desktop technology (hardware or software) used for instruction/learning through audiovisual (A/V), multimedia, web-based, or online modalities (Group on Information Resources, 2011); Sometimes includes dedicated space (see Virtual/Computerized Lab)',1,0,0),
(8,'Electronic Health/Medical Record (EHR/EMR)','An individual patient\'s medical record in digital format...usually accessed on a computer, often over a network...[M]ay be made up of electronic medical records (EMRs) from many locations and/or sources. An Electronic Medical Record (EMR) may be an inpatient or outpatient medical record in digital format that may or may not be linked to or part of a larger EHR (Group on Information Resources, 2011)',1,0,0),
(9,'Film/Video','Devices or applications used to acquire or transfer knowledge, attitudes, or skills through study, instruction, or experience using visual recordings (see \"Electronic Learning,\" Education Resources Information Center, 2008b)',1,0,0),
(10,'Key Feature','An element specific to a clinical case or problem that demands the use of particular clinical skills in order to achieve the problem\'s successful resolution; Typically presented as written exam questions, as in the Canadian Qualifying Examination in Medicine (Page & Bordage, 1995; Page, Bordage, & Allen, 1995)',1,0,0),
(11,'Mannequin','A life-size model of the human body that mimics various anatomical functions to teach skills and procedures in health education; may be low-fidelity (having limited or no electronic inputs) or high-fidelity\r(connected to a computer that allows the robot to respond dynamically to user input) (Group on Information Resources, 2011; Passiment, Sacks, & Huang, 2011)',1,0,0),
(12,'Plastinated Specimens','Organic material preserved by replacing water and fat in tissue with silicone, resulting in \"anatomical specimens [that] are safer to use, more pleasant to use, and are much more durable and have a much longer shelf life\" (University of Michigan Plastination Lab, n.d.); See also: Wet Lab',1,0,0),
(13,'Printed Materials (or Digital Equivalent)','Reference materials produced or selected by faculty to augment course teaching and learning',1,0,0),
(14,'Real Patient','An actual clinical patient',1,0,0),
(15,'Searchable Electronic Database','A collection of information organized in such a way that a computer program can quickly select desired pieces of data (Webopedia, n.d.)',1,0,0),
(16,'Standardized/Simulated Patient (SP)','Individual trained to portray a patient with a specific condition in a realistic, standardized and repeatable way (where portrayal/presentation varies based only on learner performance) (ASPE, 2011)',1,0,0),
(17,'Task Trainer','A physical model that simulates a subset of physiologic function to include normal and abnormal anatomy (Passiment, Sacks, & Huang, 2011); Such models which provide just the key elements of the task or skill being learned (CISL, 2011)',1,0,0),
(18,'Virtual Patient','An interactive computer simulation of real-life clinical scenarios for the purpose of medical training, education, or assessment (Smothers, Azan, & Ellaway, 2010)',1,0,0),
(19,'Virtual/Computerized Laboratory','A practical learning environment in which technology- and computer-based simulations allow learners to engage in computer-assisted instruction while being able to ask and answer questions and also engage in discussion of content (Cooke, Irby, & O\'Brien, 2010a); also, to learn through experience by performing medical tasks, especially high-risk ones, in a safe environment (Uniformed Services University, 2011)',1,0,0),
(20,'Wet Laboratory','Facilities outfitted with specialized equipment* and bench space or adjustable, flexible desktop space for working with solutions or biological materials (\"C.1 Wet Laboratories,\" 2006; Stanford University School of Medicine, 2007;\rWBDG Staff, 2010) *Often includes sinks, chemical fume hoods, biosafety cabinets, and piped services such as deionized or RO water, lab cold and hot water, lab waste/vents, carbon dioxide, vacuum, compressed air, eyewash, safety showers, natural gas, telephone, LAN, and power (\"C.1 Wet Laboratories,\" 2006)',1,0,0);

CREATE TABLE IF NOT EXISTS `notification_users` (
  `nuser_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `proxy_id` int(11) NOT NULL,
  `content_type` varchar(32) NOT NULL DEFAULT '',
  `record_id` int(11) NOT NULL,
  `record_proxy_id` int(11) DEFAULT NULL,
  `notify_active` tinyint(1) NOT NULL DEFAULT '0',
  `digest_mode` tinyint(1) NOT NULL DEFAULT '0',
  `next_notification_date` int(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nuser_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nuser_id` int(11) NOT NULL,
  `notification_body` text NOT NULL,
  `proxy_id` int(11) NOT NULL,
  `digest` tinyint(1) NOT NULL DEFAULT '0',
  `sent` tinyint(1) NOT NULL DEFAULT '0',
  `sent_date` bigint(64) DEFAULT '0',
  PRIMARY KEY (`notification_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `curriculum_level_organisation` (
  `cl_org_id` INT(12) NOT NULL AUTO_INCREMENT,
  `org_id` INT(12) NOT NULL,
  `curriculum_level_id` INT(11) NOT NULL,
  PRIMARY KEY (`cl_org_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `curriculum_level_organisation` (`org_id`,`curriculum_level_id`)
VALUES
	(1, 1),
	(1, 2);
