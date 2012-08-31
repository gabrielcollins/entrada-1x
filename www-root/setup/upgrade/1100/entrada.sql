CREATE TABLE IF NOT EXISTS `ar_book_chapter_mono` (
  `book_chapter_mono_id` int(11) NOT NULL auto_increment,
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
  PRIMARY KEY  (`book_chapter_mono_id`)
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
  PRIMARY KEY  (`clinical_activity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_clinical_education` (
  `clinical_education_id` int(11) NOT NULL auto_increment,
  `level` varchar(150) NOT NULL DEFAULT '',
  `level_description` varchar(255) DEFAULT NULL,
  `location` varchar(150) NOT NULL DEFAULT '',
  `location_description` varchar(255) DEFAULT NULL,
  `average_hours` int(11) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY  (`clinical_education_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_clinical_innovation` (
  `clinical_innovation_id` int(11) NOT NULL auto_increment,
  `description` text NOT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY  (`clinical_innovation_id`)
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
  PRIMARY KEY  (`clinics_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_conference_papers` (
  `conference_papers_id` int(11) NOT NULL auto_increment,
  `lectures_papers_list` text NOT NULL,
  `status` varchar(25) NOT NULL DEFAULT '',
  `institution` text NOT NULL,
  `location` varchar(250) NOT NULL DEFAULT '',
  `type` varchar(30) NOT NULL DEFAULT '',
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY  (`conference_papers_id`)
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
  PRIMARY KEY  (`consults_id`)
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
  PRIMARY KEY  (`continuing_education_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_external_contributions` (
  `external_contributions_id` int(11) NOT NULL auto_increment,
  `organisation` varchar(255) NOT NULL DEFAULT '',
  `city_country` text NOT NULL,
  `description` text NOT NULL,
  `days_of_year` int(3) NOT NULL DEFAULT '0',
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY  (`external_contributions_id`)
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
  PRIMARY KEY  (`graduate_supervision_id`)
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
  PRIMARY KEY  (`graduate_teaching_id`)
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
  PRIMARY KEY  (`innovation_id`)
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
  PRIMARY KEY  (`internal_contributions_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_lu_activity_types` (
  `id` int(11) NOT NULL auto_increment,
  `activity_type` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_activity_types` (`id`, `activity_type`) VALUES
(1, 'Lecture'),
(2, 'Seminar'),
(3, 'Workshop'),
(4, 'Other');

CREATE TABLE IF NOT EXISTS `ar_lu_clinical_locations` (
  `id` int(11) NOT NULL auto_increment,
  `clinical_location` varchar(50) DEFAULT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_clinical_locations` (`id`, `clinical_location`) VALUES
(1, 'Hospital A'),
(2, 'Hospital B'),
(3, 'Hospital C'),
(4, 'Other (specify)');

CREATE TABLE IF NOT EXISTS `ar_lu_conference_paper_types` (
  `id` int(11) NOT NULL auto_increment,
  `conference_paper_type` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_conference_paper_types` (`id`, `conference_paper_type`) VALUES
(1, 'Invited Lecture'),
(2, 'Invited Conference Paper');

CREATE TABLE IF NOT EXISTS `ar_lu_consult_locations` (
  `id` int(11) NOT NULL auto_increment,
  `consult_location` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_consult_locations` (`id`, `consult_location`) VALUES
(1, 'Hospital A'),
(2, 'Hospital B'),
(3, 'Hospital C'),
(4, 'Other (specify)');

CREATE TABLE IF NOT EXISTS `ar_lu_contribution_roles` (
  `id` int(11) NOT NULL auto_increment,
  `contribution_role` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
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
(16, 'Other (specify)');

CREATE TABLE IF NOT EXISTS `ar_lu_contribution_types` (
  `id` int(11) NOT NULL auto_increment,
  `contribution_type` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
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
  `id` int(11) NOT NULL auto_increment,
  `degree_type` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_degree_types` (`id`, `degree_type`) VALUES
(1, 'BA'),
(2, 'BSc'),
(3, 'BNSc'),
(4, 'MA'),
(5, 'MD'),
(6, 'M ED'),
(7, 'MES'),
(8, 'MSc'),
(9, 'MScOT'),
(10, 'MSc OT (Project)'),
(11, 'MScPT'),
(12, 'MSC PT (Project)'),
(13, 'PDF'),
(14, 'PhD'),
(15, 'Clinical Fellow'),
(16, 'Summer Research Student'),
(17, 'MPA Candidate');

CREATE TABLE IF NOT EXISTS `ar_lu_education_locations` (
  `id` int(11) NOT NULL auto_increment,
  `education_location` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_education_locations` (`id`, `education_location`) VALUES
(1, 'Hospital A'),
(2, 'Hospital B'),
(3, 'Hospital C'),
(4, 'Other (specify)');

CREATE TABLE IF NOT EXISTS `ar_lu_focus_groups` (
  `group_id` int(11) NOT NULL auto_increment,
  `focus_group` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY  (`group_id`),
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
  PRIMARY KEY  (`hosp_id`),
  KEY `hosp_desc` (`hosp_desc`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_hospital_location` (`hosp_id`, `hosp_desc`) VALUES
(1, 'Hospital A'),
(2, 'Hospital B'),
(3, 'Hospital C');

CREATE TABLE IF NOT EXISTS `ar_lu_innovation_types` (
  `id` int(11) NOT NULL auto_increment,
  `innovation_type` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
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
  PRIMARY KEY  (`id`)
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
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_on_call_locations` (`id`, `on_call_location`) VALUES
(1, 'Hospital A'),
(2, 'Hospital B'),
(3, 'Hospital C');

CREATE TABLE IF NOT EXISTS `ar_lu_other_locations` (
  `id` int(11) NOT NULL auto_increment,
  `other_location` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_other_locations` (`id`, `other_location`) VALUES
(1, 'Hospital A'),
(2, 'Hospital B'),
(3, 'Hospital C'),
(4, 'Other (specify)');

CREATE TABLE IF NOT EXISTS `ar_lu_patent_types` (
  `id` int(11) NOT NULL auto_increment,
  `patent_type` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_patent_types` (`id`, `patent_type`) VALUES
(1, 'License Granted'),
(2, 'Non-Disclosure Agreement'),
(3, 'Patent Applied For'),
(4, 'Patent Obtained');

CREATE TABLE IF NOT EXISTS `ar_lu_prize_categories` (
  `id` int(11) NOT NULL auto_increment,
  `prize_category` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_prize_categories` (`id`, `prize_category`) VALUES
(1, 'Research'),
(2, 'Teaching'),
(3, 'Service');

CREATE TABLE IF NOT EXISTS `ar_lu_prize_types` (
  `id` int(11) NOT NULL auto_increment,
  `prize_type` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_prize_types` (`id`, `prize_type`) VALUES
(1, 'Award'),
(2, 'Honour'),
(3, 'Prize');

CREATE TABLE IF NOT EXISTS `ar_lu_profile_roles` (
  `id` int(11) NOT NULL auto_increment,
  `profile_role` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_profile_roles` (`id`, `profile_role`) VALUES
(1, 'Researcher/Scholar'),
(2, 'Educator/Scholar'),
(3, 'Clinician/Scholar');

CREATE TABLE IF NOT EXISTS `ar_lu_publication_statuses` (
  `id` int(11) NOT NULL auto_increment,
  `publication_status` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
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
  PRIMARY KEY  (`type_id`),
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
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_research_types` (`id`, `research_type`) VALUES
(1, 'Infrastructure'),
(2, 'Operating'),
(3, 'Salary'),
(4, 'Training');

CREATE TABLE IF NOT EXISTS `ar_lu_scholarly_types` (
  `id` int(11) NOT NULL auto_increment,
  `scholarly_type` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_scholarly_types` (`id`, `scholarly_type`) VALUES
(1, 'Granting Body Referee'),
(2, 'Journal Editorship'),
(3, 'Journal Referee'),
(4, 'Other (specify)');

CREATE TABLE IF NOT EXISTS `ar_lu_self_education_types` (
  `id` int(11) NOT NULL auto_increment,
  `self_education_type` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
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
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ar_lu_supervision_types` (`id`, `supervision_type`) VALUES
(1, 'Joint'),
(2, 'Sole');

CREATE TABLE IF NOT EXISTS `ar_lu_trainee_levels` (
  `id` int(11) NOT NULL auto_increment,
  `trainee_level` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
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
  PRIMARY KEY  (`id`)
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
  PRIMARY KEY  (`memberships_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_non_peer_reviewed_papers` (
  `non_peer_reviewed_papers_id` int(11) NOT NULL auto_increment,
  `title` text NOT NULL,
  `source` varchar(200) NOT NULL,
  `author_list` varchar(200) NOT NULL,
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
  PRIMARY KEY  (`non_peer_reviewed_papers_id`)
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
  PRIMARY KEY  (`on_call_id`)
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
  PRIMARY KEY  (`other_id`)
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
  PRIMARY KEY  (`other_activity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_patent_activity` (
  `patent_activity_id` int(11) NOT NULL auto_increment,
  `patent_activity_type` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY  (`patent_activity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_peer_reviewed_papers` (
  `peer_reviewed_papers_id` int(11) NOT NULL auto_increment,
  `title` text NOT NULL,
  `source` varchar(200) NOT NULL,
  `author_list` varchar(200) NOT NULL,
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
  PRIMARY KEY  (`peer_reviewed_papers_id`)
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
  PRIMARY KEY  (`poster_reports_id`)
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
  PRIMARY KEY  (`prizes_id`)
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
  PRIMARY KEY  (`procedures_id`)
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
  PRIMARY KEY  (`profile_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_research` (
  `research_id` int(11) NOT NULL auto_increment,
  `grant_title` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `agency` text,
  `role` varchar(50) NOT NULL,
  `principal_investigator` varchar(100) NOT NULL DEFAULT '',
  `co_investigator_list` text,
  `amount_received` decimal(20,2) NOT NULL DEFAULT '0.00',
  `start_month` int(2) NOT NULL DEFAULT '0',
  `start_year` int(4) NOT NULL DEFAULT '0',
  `end_month` int(2) DEFAULT '0',
  `end_year` int(4) DEFAULT '0',
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `funding_status` varchar(9) NOT NULL DEFAULT '',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY  (`research_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_scholarly_activity` (
  `scholarly_activity_id` int(11) NOT NULL auto_increment,
  `scholarly_activity_type` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY  (`scholarly_activity_id`)
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
  PRIMARY KEY  (`self_education_id`)
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
  PRIMARY KEY  (`undergraduate_teaching_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

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
  PRIMARY KEY  (`undergraduate_supervision_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ar_undergraduate_teaching` (
  `undergraduate_teaching_id` int(11) NOT NULL auto_increment,
  `course_number` varchar(25) NOT NULL DEFAULT '',
  `course_name` text NOT NULL,
  `lecture_phase` varchar(6) DEFAULT NULL,
  `assigned` char(3) NOT NULL DEFAULT '',
  `lec_enrollment` int(11) DEFAULT '0',
  `lec_hours` decimal(20,2) DEFAULT '0.00',
  `lab_enrollment` int(11) DEFAULT '0',
  `lab_hours` decimal(20,2) DEFAULT '0.00',
  `tut_enrollment` int(11) DEFAULT '0',
  `tut_hours` decimal(20,2) DEFAULT '0.00',
  `sem_enrollment` int(11) DEFAULT '0',
  `sem_hours` decimal(20,2) DEFAULT '0.00',
  `coord_enrollment` int(11) DEFAULT '0',
  `pbl_hours` decimal(20,2) DEFAULT '0.00',
  `comments` text,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY  (`undergraduate_teaching_id`)
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
  PRIMARY KEY  (`ward_supervision_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `assessments` (
  `assessment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(10) unsigned NOT NULL,
  `grad_year` int(4) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `type` varchar(255) NOT NULL,
  `marking_scheme_id` int(10) unsigned NOT NULL,
  `numeric_grade_points_total` float unsigned DEFAULT NULL,
  PRIMARY KEY (`assessment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `assessment_grades` (
  `grade_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `assessment_id` int(10) unsigned NOT NULL,
  `proxy_id` int(10) unsigned NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY (`grade_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `assessment_marking_schemes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `handler` varchar(255) NOT NULL DEFAULT 'Boolean',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `assessment_marking_schemes` (`id`,`name`,`handler`,`enabled`) VALUES
(1, 'Pass/Fail', 'Boolean', 1),
(2, 'Percentage', 'Percentage', 1),
(3, 'Numeric', 'Numeric', 1),
(4, 'Complete/Incomplete', 'IncompleteComplete', 1);

ALTER TABLE `courses` CHANGE `course_name` `course_name` VARCHAR(85) NOT NULL DEFAULT '';

ALTER TABLE `course_objectives` ADD COLUMN `objective_type` enum('event','course') DEFAULT 'course' AFTER `importance`;

INSERT INTO `course_objectives` (`course_id`, `objective_id`, `importance`, `objective_type`) (SELECT b.`course_id`, a.`objective_id`, 1, 'event' FROM `event_objectives` AS a JOIN `events` AS b ON a.`event_id` = b.`event_id` WHERE a.`objective_type` = 'event' GROUP BY a.`objective_id`, b.`course_id`);

CREATE TABLE IF NOT EXISTS `global_lu_focus_groups` (
  `group_id` int(11) NOT NULL auto_increment,
  `focus_group` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`group_id`),
  KEY `focus_group` (`focus_group`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

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
  PRIMARY KEY  (`hosp_id`),
  KEY `hosp_desc` (`hosp_desc`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `global_lu_publication_type` (
  `type_id` int(11) NOT NULL default '0',
  `type_description` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`type_id`),
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
  PRIMARY KEY  (`role_id`),
  KEY `role_description` (`role_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `global_lu_roles` (`role_id`, `role_description`) VALUES
(1, 'Lead Author'),
(2, 'Contributing Author'),
(3, 'Editor'),
(4, 'Co-Editor'),
(5, 'Senior Author'),
(6, 'Co-Lead');

ALTER TABLE `notices` ADD INDEX (`organisation_id`);

CREATE TABLE IF NOT EXISTS `settings` (
  `shortname` varchar(64) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`shortname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `settings` (`shortname`, `value`) VALUES ('version_db', '1.1.0');

CREATE TABLE IF NOT EXISTS `statistics_archive` (
  `statistic_id` int(12) NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `timestamp` bigint(64) NOT NULL DEFAULT '0',
  `module` varchar(64) NOT NULL DEFAULT 'undefined',
  `action` varchar(64) NOT NULL DEFAULT 'undefined',
  `action_field` varchar(64) DEFAULT NULL,
  `action_value` varchar(64) DEFAULT NULL,
  `prune_after` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`statistic_id`),
  KEY `proxy_id` (`proxy_id`,`timestamp`,`module`,`action`,`action_field`,`action_value`),
  KEY `proxy_id_2` (`proxy_id`),
  KEY `timestamp` (`timestamp`),
  KEY `module` (`module`,`action`,`action_field`,`action_value`),
  KEY `action` (`action`),
  KEY `action_field` (`action_field`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_awards_external` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(4096) NOT NULL,
  `year` year(4) NOT NULL,
  `awarding_body` varchar(4096) NOT NULL,
  `award_terms` mediumtext NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_awards_internal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `award_id` int(11) NOT NULL,
  `year` year(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_awards_internal_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `award_terms` mediumtext NOT NULL,
  `title` varchar(200) NOT NULL DEFAULT '',
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `title_unique` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_clineval_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_contributions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(4096) NOT NULL,
  `org_event` varchar(256) NOT NULL DEFAULT '',
  `date` varchar(256) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL,
  `start_month` int(11) DEFAULT NULL,
  `start_year` int(11) DEFAULT NULL,
  `end_month` int(11) DEFAULT NULL,
  `end_year` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_critical_enquiries` (
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `organization` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `supervisor` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_disciplinary_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action_details` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_formal_remediations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `remediation_details` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_international_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `title` varchar(256) NOT NULL,
  `location` varchar(256) NOT NULL,
  `site` varchar(256) NOT NULL,
  `start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_leaves_of_absence` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `absence_details` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_mspr` (
  `user_id` int(11) DEFAULT NULL,
  `last_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `generated` int(11) DEFAULT NULL,
  `closed` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_mspr_class` (
  `year` int(11) NOT NULL DEFAULT '0',
  `closed` int(11) DEFAULT NULL,
  PRIMARY KEY (`year`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_observerships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `title` varchar(256) NOT NULL,
  `location` varchar(256) NOT NULL,
  `site` varchar(256) NOT NULL,
  `start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_research` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `citation` varchar(4096) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `priority` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_studentships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(4096) NOT NULL,
  `year` year(4) NOT NULL DEFAULT '0000',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_student_run_electives` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  `university` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_month` tinyint(2) unsigned DEFAULT NULL,
  `start_year` smallint(4) unsigned DEFAULT NULL,
  `end_month` tinyint(2) unsigned DEFAULT NULL,
  `end_year` smallint(4) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Tasks Module tables

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
  `require_verification` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`task_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=34 ;

CREATE TABLE IF NOT EXISTS `task_completion` (
  `task_id` int(12) unsigned NOT NULL,
  `verifier_id` int(12) unsigned default NULL,
  `verified_date` bigint(64) default NULL,
  `recipient_id` int(12) unsigned NOT NULL,
  `completed_date` bigint(64) default NULL,
  PRIMARY KEY  (`task_id`,`recipient_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `task_owners` (
  `task_id` int(12) unsigned NOT NULL default '0',
  `owner_id` int(12) unsigned NOT NULL default '0',
  `owner_type` enum('course','event','user') NOT NULL default 'course',
  PRIMARY KEY  (`task_id`,`owner_id`,`owner_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `task_recipients` (
  `task_id` int(12) unsigned NOT NULL,
  `recipient_type` enum('user','group','grad_year','organisation') NOT NULL,
  `recipient_id` int(12) unsigned NOT NULL,
  PRIMARY KEY  (`task_id`,`recipient_type`,`recipient_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Events modifications

ALTER TABLE `events`
DROP COLUMN `eventtype_id`;
