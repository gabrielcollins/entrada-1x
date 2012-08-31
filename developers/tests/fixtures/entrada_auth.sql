SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
CREATE DATABASE `test_entrada_auth` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `test_entrada_auth`;

CREATE TABLE `acl_permissions` (
  `permission_id` int(12) NOT NULL AUTO_INCREMENT,
  `resource_type` varchar(64) DEFAULT NULL,
  `resource_value` int(12) DEFAULT NULL,
  `entity_type` varchar(64) DEFAULT NULL,
  `entity_value` varchar(64) DEFAULT NULL,
  `app_id` int(12) NOT NULL,
  `create` tinyint(1) DEFAULT NULL,
  `read` tinyint(1) DEFAULT NULL,
  `update` tinyint(1) DEFAULT NULL,
  `delete` tinyint(1) DEFAULT NULL,
  `assertion` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`permission_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=270 ;

INSERT INTO `acl_permissions` VALUES(116, 'community', NULL, NULL, NULL, 1, 1, 1, NULL, NULL, 'NotGuest');
INSERT INTO `acl_permissions` VALUES(117, 'course', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'ResourceOrganisation&NotGuest');
INSERT INTO `acl_permissions` VALUES(118, 'dashboard', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest');
INSERT INTO `acl_permissions` VALUES(119, 'discussion', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest');
INSERT INTO `acl_permissions` VALUES(120, 'library', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest');
INSERT INTO `acl_permissions` VALUES(121, 'people', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest');
INSERT INTO `acl_permissions` VALUES(122, 'podcast', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest');
INSERT INTO `acl_permissions` VALUES(123, 'profile', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest');
INSERT INTO `acl_permissions` VALUES(124, 'search', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest');
INSERT INTO `acl_permissions` VALUES(125, 'event', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'ResourceOrganisation&NotGuest');
INSERT INTO `acl_permissions` VALUES(126, 'resourceorganisation', 1, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest');
INSERT INTO `acl_permissions` VALUES(127, 'coursecontent', NULL, 'role', 'pcoordinator', 1, NULL, NULL, 1, NULL, 'CourseOwner');
INSERT INTO `acl_permissions` VALUES(128, 'event', NULL, 'role', 'pcoordinator', 1, 1, NULL, NULL, NULL, 'CourseOwner');
INSERT INTO `acl_permissions` VALUES(129, 'event', NULL, 'role', 'pcoordinator', 1, NULL, NULL, 1, 1, 'EventOwner');
INSERT INTO `acl_permissions` VALUES(130, 'eventcontent', NULL, 'role', 'pcoordinator', 1, NULL, NULL, 1, NULL, 'EventOwner');
INSERT INTO `acl_permissions` VALUES(131, 'coursecontent', NULL, 'role', 'director', 1, NULL, NULL, 1, NULL, 'CourseOwner');
INSERT INTO `acl_permissions` VALUES(132, 'coursecontent', NULL, 'role', 'lecturer', 1, NULL, NULL, 1, NULL, 'CourseOwner');
INSERT INTO `acl_permissions` VALUES(133, 'eventcontent', NULL, 'role', 'lecturer', 1, NULL, NULL, 1, NULL, 'EventOwner');
INSERT INTO `acl_permissions` VALUES(134, 'eventcontent', NULL, 'role', 'director', 1, NULL, NULL, 1, NULL, 'EventOwner');
INSERT INTO `acl_permissions` VALUES(135, 'eventcontent', NULL, 'group:role', 'faculty:admin', 1, NULL, NULL, 1, NULL, 'EventOwner');
INSERT INTO `acl_permissions` VALUES(136, NULL, NULL, 'group:role', 'medtech:admin', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(137, 'notice', NULL, 'group:role', 'faculty:director', 1, 1, NULL, 1, 1, 'ResourceOrganisation');
INSERT INTO `acl_permissions` VALUES(138, 'notice', NULL, 'group:role', 'faculty:admin', 1, 1, NULL, 1, 1, 'ResourceOrganisation');
INSERT INTO `acl_permissions` VALUES(139, 'notice', NULL, 'group:role', 'staff:admin', 1, 1, NULL, 1, 1, 'ResourceOrganisation');
INSERT INTO `acl_permissions` VALUES(140, 'notice', NULL, 'group:role', 'staff:pcoordinator', 1, 1, NULL, 1, 1, 'ResourceOrganisation');
INSERT INTO `acl_permissions` VALUES(141, 'notice', NULL, 'group:role', 'staff:admin', 1, 1, NULL, 1, 1, 'ResourceOrganisation');
INSERT INTO `acl_permissions` VALUES(142, 'notice', NULL, 'group:role', 'staff:pcoordinator', 1, 1, NULL, 1, 1, 'ResourceOrganisation');
INSERT INTO `acl_permissions` VALUES(143, 'resourceorganisation', 1, 'organisation:group:role', '1:faculty:director', 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(144, 'resourceorganisation', 1, 'organisation:group:role', '1:faculty:admin', 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(145, 'resourceorganisation', 1, 'organisation:group:role', '1:staff:admin', 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(146, 'resourceorganisation', 1, 'organisation:group:role', '1:staff:pcoordinator', 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(147, 'poll', NULL, 'role', 'admin', 1, 1, NULL, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(148, 'poll', NULL, 'role', 'pcoordinator', 1, 1, NULL, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(149, 'quiz', NULL, 'group:role', 'faculty:director', 1, NULL, NULL, 1, 1, 'QuizOwner');
INSERT INTO `acl_permissions` VALUES(150, 'firstlogin', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(151, 'community', NULL, NULL, NULL, 1, NULL, NULL, 1, 1, 'CommunityOwner');
INSERT INTO `acl_permissions` VALUES(152, 'quiz', NULL, 'group:role', 'faculty:admin', 1, NULL, NULL, 1, 1, 'QuizOwner');
INSERT INTO `acl_permissions` VALUES(153, 'quiz', NULL, 'group:role', 'faculty:lecturer', 1, NULL, NULL, 1, 1, 'QuizOwner');
INSERT INTO `acl_permissions` VALUES(154, 'quiz', NULL, 'group:role', 'resident:lecturer', 1, NULL, NULL, 1, 1, 'QuizOwner');
INSERT INTO `acl_permissions` VALUES(155, 'quiz', NULL, 'group:role', 'staff:admin', 1, NULL, NULL, 1, 1, 'QuizOwner');
INSERT INTO `acl_permissions` VALUES(156, 'quiz', NULL, 'group:role', 'staff:pcoordinator', 1, NULL, NULL, 1, 1, 'QuizOwner');
INSERT INTO `acl_permissions` VALUES(157, NULL, NULL, 'group:role', 'guest:communityinvite', 1, 0, 0, 0, 0, NULL);
INSERT INTO `acl_permissions` VALUES(158, 'clerkship', NULL, 'group', 'student', 1, NULL, 1, NULL, NULL, 'Clerkship');
INSERT INTO `acl_permissions` VALUES(159, 'clerkship', NULL, 'group', 'staff', 1, NULL, 1, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(160, 'clerkship', NULL, 'group', 'faculty', 1, NULL, 1, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(161, NULL, NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, 'ResourceOrganisation');
INSERT INTO `acl_permissions` VALUES(162, 'resourceorganisation', 1, 'organisation:group:role', '1:staff:admin', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(163, 'clerkship', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(164, 'clerkship', NULL, 'group:role', 'faculty:clerkship', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(165, 'quiz', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest');
INSERT INTO `acl_permissions` VALUES(166, 'quiz', NULL, 'group', 'faculty', 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(167, 'quiz', NULL, 'group', 'staff', 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(168, 'quiz', NULL, 'group:role', 'resident:lecturer', 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(169, 'photo', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'Photo');
INSERT INTO `acl_permissions` VALUES(170, 'photo', NULL, 'group', 'faculty', 1, NULL, 1, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(171, 'photo', NULL, 'group', 'staff', 1, NULL, 1, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(172, 'clerkshipschedules', NULL, 'group', 'faculty', 1, NULL, 1, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(173, 'clerkshipschedules', NULL, 'group', 'staff', 1, NULL, 1, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(174, 'reportindex', NULL, 'organisation:group:role', '1:staff:admin', 1, NULL, 1, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(175, 'report', NULL, 'organisation:group:role', '1:staff:admin', 1, NULL, 1, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(176, 'assistant_support', NULL, 'group:role', 'faculty:director', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(177, 'assistant_support', NULL, 'group:role', 'faculty:clerkship', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(178, 'assistant_support', NULL, 'group:role', 'faculty:admin', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(179, 'assistant_support', NULL, 'group:role', 'faculty:lecturer', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(180, 'assistant_support', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(181, 'assistant_support', NULL, 'group:role', 'staff:pcoordinator', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(182, 'lottery', NULL, 'group', 'student', 1, NULL, 1, NULL, NULL, 'ClerkshipLottery');
INSERT INTO `acl_permissions` VALUES(183, 'lottery', NULL, 'group:role', 'staff:admin', 1, NULL, 1, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(184, 'lottery', NULL, 'group:role', 'faculty:director', 1, NULL, 1, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(185, 'logbook', NULL, 'group:role', 'staff:pcoordinator', 1, NULL, 1, 1, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(186, 'objective', NULL, 'group', 'staff', 1, NULL, NULL, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(187, 'objective', NULL, 'group', 'faculty', 1, NULL, NULL, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(188, 'objectivecontent', NULL, 'group', 'staff', 1, NULL, NULL, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(189, 'objectivecontent', NULL, 'group', 'faculty', 1, NULL, NULL, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(190, 'objective', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(191, 'objective', NULL, 'group:role', 'medtech:admin', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(192, 'annualreport', NULL, 'group', 'faculty', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(193, 'community', NULL, NULL, NULL, 1, 1, 1, NULL, NULL, 'NotGuest');
INSERT INTO `acl_permissions` VALUES(194, 'course', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'ResourceOrganisation&NotGuest');
INSERT INTO `acl_permissions` VALUES(195, 'dashboard', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest');
INSERT INTO `acl_permissions` VALUES(196, 'discussion', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest');
INSERT INTO `acl_permissions` VALUES(197, 'library', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest');
INSERT INTO `acl_permissions` VALUES(198, 'people', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest');
INSERT INTO `acl_permissions` VALUES(199, 'podcast', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest');
INSERT INTO `acl_permissions` VALUES(200, 'profile', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest');
INSERT INTO `acl_permissions` VALUES(201, 'search', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest');
INSERT INTO `acl_permissions` VALUES(202, 'event', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'ResourceOrganisation&NotGuest');
INSERT INTO `acl_permissions` VALUES(203, 'resourceorganisation', 1, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest');
INSERT INTO `acl_permissions` VALUES(204, 'coursecontent', NULL, 'role', 'pcoordinator', 1, NULL, NULL, 1, NULL, 'CourseOwner');
INSERT INTO `acl_permissions` VALUES(205, 'event', NULL, 'role', 'pcoordinator', 1, 1, NULL, NULL, NULL, 'CourseOwner');
INSERT INTO `acl_permissions` VALUES(206, 'event', NULL, 'role', 'pcoordinator', 1, NULL, NULL, 1, 1, 'EventOwner');
INSERT INTO `acl_permissions` VALUES(207, 'eventcontent', NULL, 'role', 'pcoordinator', 1, NULL, NULL, 1, NULL, 'EventOwner');
INSERT INTO `acl_permissions` VALUES(208, 'coursecontent', NULL, 'role', 'director', 1, NULL, NULL, 1, NULL, 'CourseOwner');
INSERT INTO `acl_permissions` VALUES(209, 'coursecontent', NULL, 'role', 'lecturer', 1, NULL, NULL, 1, NULL, 'CourseOwner');
INSERT INTO `acl_permissions` VALUES(210, 'eventcontent', NULL, 'role', 'lecturer', 1, NULL, NULL, 1, NULL, 'EventOwner');
INSERT INTO `acl_permissions` VALUES(211, 'eventcontent', NULL, 'role', 'director', 1, NULL, NULL, 1, NULL, 'EventOwner');
INSERT INTO `acl_permissions` VALUES(212, 'eventcontent', NULL, 'group:role', 'faculty:admin', 1, NULL, NULL, 1, NULL, 'EventOwner');
INSERT INTO `acl_permissions` VALUES(213, NULL, NULL, 'group:role', 'medtech:admin', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(214, 'notice', NULL, 'group:role', 'faculty:director', 1, 1, NULL, 1, 1, 'ResourceOrganisation');
INSERT INTO `acl_permissions` VALUES(215, 'notice', NULL, 'group:role', 'faculty:admin', 1, 1, NULL, 1, 1, 'ResourceOrganisation');
INSERT INTO `acl_permissions` VALUES(216, 'notice', NULL, 'group:role', 'staff:admin', 1, 1, NULL, 1, 1, 'ResourceOrganisation');
INSERT INTO `acl_permissions` VALUES(217, 'notice', NULL, 'group:role', 'staff:pcoordinator', 1, 1, NULL, 1, 1, 'ResourceOrganisation');
INSERT INTO `acl_permissions` VALUES(218, 'notice', NULL, 'group:role', 'staff:admin', 1, 1, NULL, 1, 1, 'ResourceOrganisation');
INSERT INTO `acl_permissions` VALUES(219, 'notice', NULL, 'group:role', 'staff:pcoordinator', 1, 1, NULL, 1, 1, 'ResourceOrganisation');
INSERT INTO `acl_permissions` VALUES(220, 'resourceorganisation', 1, 'organisation:group:role', '1:faculty:director', 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(221, 'resourceorganisation', 1, 'organisation:group:role', '1:faculty:admin', 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(222, 'resourceorganisation', 1, 'organisation:group:role', '1:staff:admin', 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(223, 'resourceorganisation', 1, 'organisation:group:role', '1:staff:pcoordinator', 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(224, 'poll', NULL, 'role', 'admin', 1, 1, NULL, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(225, 'poll', NULL, 'role', 'pcoordinator', 1, 1, NULL, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(226, 'quiz', NULL, 'group:role', 'faculty:director', 1, NULL, NULL, 1, 1, 'QuizOwner');
INSERT INTO `acl_permissions` VALUES(227, 'firstlogin', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(228, 'community', NULL, NULL, NULL, 1, NULL, NULL, 1, 1, 'CommunityOwner');
INSERT INTO `acl_permissions` VALUES(229, 'quiz', NULL, 'group:role', 'faculty:admin', 1, NULL, NULL, 1, 1, 'QuizOwner');
INSERT INTO `acl_permissions` VALUES(230, 'quiz', NULL, 'group:role', 'faculty:lecturer', 1, NULL, NULL, 1, 1, 'QuizOwner');
INSERT INTO `acl_permissions` VALUES(231, 'quiz', NULL, 'group:role', 'resident:lecturer', 1, NULL, NULL, 1, 1, 'QuizOwner');
INSERT INTO `acl_permissions` VALUES(232, 'quiz', NULL, 'group:role', 'staff:admin', 1, NULL, NULL, 1, 1, 'QuizOwner');
INSERT INTO `acl_permissions` VALUES(233, 'quiz', NULL, 'group:role', 'staff:pcoordinator', 1, NULL, NULL, 1, 1, 'QuizOwner');
INSERT INTO `acl_permissions` VALUES(234, NULL, NULL, 'group:role', 'guest:communityinvite', 1, 0, 0, 0, 0, NULL);
INSERT INTO `acl_permissions` VALUES(235, 'clerkship', NULL, 'group', 'student', 1, NULL, 1, NULL, NULL, 'Clerkship');
INSERT INTO `acl_permissions` VALUES(236, 'clerkship', NULL, 'group', 'staff', 1, NULL, 1, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(237, 'clerkship', NULL, 'group', 'faculty', 1, NULL, 1, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(238, NULL, NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, 'ResourceOrganisation');
INSERT INTO `acl_permissions` VALUES(239, 'resourceorganisation', 1, 'organisation:group:role', '1:staff:admin', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(240, 'clerkship', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(241, 'clerkship', NULL, 'group:role', 'faculty:clerkship', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(242, 'quiz', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest');
INSERT INTO `acl_permissions` VALUES(243, 'quiz', NULL, 'group', 'faculty', 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(244, 'quiz', NULL, 'group', 'staff', 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(245, 'quiz', NULL, 'group:role', 'resident:lecturer', 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(246, 'photo', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'Photo');
INSERT INTO `acl_permissions` VALUES(247, 'photo', NULL, 'group', 'faculty', 1, NULL, 1, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(248, 'photo', NULL, 'group', 'staff', 1, NULL, 1, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(249, 'clerkshipschedules', NULL, 'group', 'faculty', 1, NULL, 1, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(250, 'clerkshipschedules', NULL, 'group', 'staff', 1, NULL, 1, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(251, 'reportindex', NULL, 'organisation:group:role', '1:staff:admin', 1, NULL, 1, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(252, 'report', NULL, 'organisation:group:role', '1:staff:admin', 1, NULL, 1, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(253, 'assistant_support', NULL, 'group:role', 'faculty:director', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(254, 'assistant_support', NULL, 'group:role', 'faculty:clerkship', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(255, 'assistant_support', NULL, 'group:role', 'faculty:admin', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(256, 'assistant_support', NULL, 'group:role', 'faculty:lecturer', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(257, 'assistant_support', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(258, 'assistant_support', NULL, 'group:role', 'staff:pcoordinator', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(259, 'lottery', NULL, 'group', 'student', 1, NULL, 1, NULL, NULL, 'ClerkshipLottery');
INSERT INTO `acl_permissions` VALUES(260, 'lottery', NULL, 'group:role', 'staff:admin', 1, NULL, 1, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(261, 'lottery', NULL, 'group:role', 'faculty:director', 1, NULL, 1, NULL, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(262, 'logbook', NULL, 'group:role', 'staff:pcoordinator', 1, NULL, 1, 1, NULL, NULL);
INSERT INTO `acl_permissions` VALUES(263, 'objective', NULL, 'group', 'staff', 1, NULL, NULL, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(264, 'objective', NULL, 'group', 'faculty', 1, NULL, NULL, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(265, 'objectivecontent', NULL, 'group', 'staff', 1, NULL, NULL, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(266, 'objectivecontent', NULL, 'group', 'faculty', 1, NULL, NULL, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(267, 'objective', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(268, 'objective', NULL, 'group:role', 'medtech:admin', 1, 1, 1, 1, 1, NULL);
INSERT INTO `acl_permissions` VALUES(269, 'annualreport', NULL, 'group', 'faculty', 1, 1, 1, 1, 1, NULL);

CREATE TABLE `departments` (
  `department_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) unsigned NOT NULL DEFAULT '1',
  `entity_id` int(12) unsigned NOT NULL DEFAULT '0',
  `department_title` varchar(128) NOT NULL DEFAULT '',
  `department_address1` varchar(128) NOT NULL DEFAULT '',
  `department_address2` varchar(128) NOT NULL DEFAULT '',
  `department_city` varchar(64) NOT NULL DEFAULT 'Kingston',
  `department_province` varchar(64) NOT NULL DEFAULT 'ON',
  `department_country` varchar(64) NOT NULL DEFAULT 'CA',
  `department_postcode` varchar(16) NOT NULL DEFAULT '',
  `department_telephone` varchar(32) NOT NULL DEFAULT '',
  `department_fax` varchar(32) NOT NULL DEFAULT '',
  `department_email` varchar(128) NOT NULL DEFAULT '',
  `department_url` text NOT NULL,
  `department_desc` text,
  PRIMARY KEY (`department_id`),
  UNIQUE KEY `organisation_id` (`organisation_id`,`entity_id`,`department_title`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `departments` VALUES(1, 1, 5, 'Medical IT', '', '', 'Kingston', 'ON', 'CA', '', '', '', '', '', NULL);

CREATE TABLE `entity_type` (
  `entity_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `entity_title` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`entity_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Used to define entities (departments, schools, etc)' AUTO_INCREMENT=6 ;

INSERT INTO `entity_type` VALUES(1, 'Faculty');
INSERT INTO `entity_type` VALUES(2, 'School');
INSERT INTO `entity_type` VALUES(3, 'Department');
INSERT INTO `entity_type` VALUES(4, 'Division');
INSERT INTO `entity_type` VALUES(5, 'Unit');

CREATE TABLE `location_ipranges` (
  `iprange_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `location_id` int(12) unsigned NOT NULL DEFAULT '0',
  `block_start` varchar(32) NOT NULL DEFAULT '0',
  `block_end` varchar(32) NOT NULL DEFAULT '0',
  PRIMARY KEY (`iprange_id`),
  KEY `location_id` (`location_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `locations` (
  `location_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) unsigned NOT NULL DEFAULT '1',
  `department_id` int(12) unsigned NOT NULL DEFAULT '0',
  `location_title` varchar(128) NOT NULL DEFAULT '',
  `location_address1` varchar(128) NOT NULL DEFAULT '',
  `location_address2` varchar(128) NOT NULL DEFAULT '',
  `location_city` varchar(64) NOT NULL DEFAULT 'Kingston',
  `location_province` char(2) NOT NULL DEFAULT 'ON',
  `location_country` char(2) NOT NULL DEFAULT 'CA',
  `location_postcode` varchar(7) NOT NULL DEFAULT '',
  `location_telephone` varchar(32) NOT NULL DEFAULT '',
  `location_fax` varchar(32) NOT NULL DEFAULT '',
  `location_email` varchar(128) NOT NULL DEFAULT '',
  `location_url` text NOT NULL,
  `location_longitude` varchar(12) DEFAULT NULL,
  `location_latitude` varchar(12) DEFAULT NULL,
  `location_desc` text,
  PRIMARY KEY (`location_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `organisations` (
  `organisation_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_title` varchar(128) NOT NULL DEFAULT '',
  `organisation_address1` varchar(128) NOT NULL DEFAULT '',
  `organisation_address2` varchar(128) NOT NULL DEFAULT '',
  `organisation_city` varchar(64) NOT NULL DEFAULT 'Kingston',
  `organisation_province` varchar(64) NOT NULL DEFAULT 'ON',
  `organisation_country` varchar(64) NOT NULL DEFAULT 'CA',
  `organisation_postcode` varchar(16) NOT NULL DEFAULT '',
  `organisation_telephone` varchar(32) NOT NULL DEFAULT '',
  `organisation_fax` varchar(32) NOT NULL DEFAULT '',
  `organisation_email` varchar(128) NOT NULL DEFAULT '',
  `organisation_url` text NOT NULL,
  `organisation_desc` text,
  PRIMARY KEY (`organisation_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `organisations` VALUES(1, 'Your University', 'University Avenue', '', 'Kingston', 'ON', 'CA', 'K7L3N6', '613-533-2000', '', '', 'http://www.yourschool.ca', NULL);

CREATE TABLE `password_reset` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(24) NOT NULL DEFAULT '',
  `date` bigint(64) NOT NULL DEFAULT '0',
  `user_id` int(12) NOT NULL DEFAULT '0',
  `hash` varchar(64) NOT NULL DEFAULT '',
  `complete` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

INSERT INTO `password_reset` VALUES(1, '::1', 1272910565, 2, '6920ba115c39ff3f1783da912a947669', 0);
INSERT INTO `password_reset` VALUES(2, '::1', 1272910633, 3, 'f0759d5bcc226e671171a4c684630787', 0);
INSERT INTO `password_reset` VALUES(3, '::1', 1272910683, 4, '860bea2e6c508a29b59750f60016db6b', 0);
INSERT INTO `password_reset` VALUES(4, '::1', 1272910783, 5, '701a2251bb2d93f8accbd46c7ef98034', 0);
INSERT INTO `password_reset` VALUES(5, '::1', 1272910966, 6, 'e08d56be0cf4530173c01bde2f025439', 0);
INSERT INTO `password_reset` VALUES(6, '::1', 1272911022, 7, '150cb46b7b879235453ae0f66a866264', 0);
INSERT INTO `password_reset` VALUES(7, '::1', 1272911124, 8, '7854cbc5706cf698e7ad9b562d55e970', 0);
INSERT INTO `password_reset` VALUES(8, '::1', 1272911197, 9, '17e1ce4fcfd7222d73a856837c93f898', 0);
INSERT INTO `password_reset` VALUES(9, '::1', 1272911257, 10, '698abf22a00f003f3e516af8376e50eb', 0);
INSERT INTO `password_reset` VALUES(10, '::1', 1272911325, 11, '8fd09479d38c6d748a32d6858a006c00', 0);
INSERT INTO `password_reset` VALUES(11, '::1', 1272911419, 12, '7f8a96804b99646be99c644cfcd07df6', 0);
INSERT INTO `password_reset` VALUES(12, '::1', 1272911460, 13, 'ac6abe37bba9d09039733bfb43bd7579', 0);
INSERT INTO `password_reset` VALUES(13, '::1', 1272911494, 14, '927b978c5f1e79a9b748dfe9598a5177', 0);
INSERT INTO `password_reset` VALUES(14, '::1', 1272911540, 15, 'c307208d54eb181ca5e97c2603cee422', 0);
INSERT INTO `password_reset` VALUES(15, '::1', 1272911635, 16, 'c76e37a4144b5d3fbe194aa388d973d9', 0);
INSERT INTO `password_reset` VALUES(16, '::1', 1272911700, 17, '9b8fb53b6f7876fb35a7159ef282de54', 0);

CREATE TABLE `registered_apps` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `script_id` varchar(25) NOT NULL DEFAULT '0',
  `script_password` varchar(255) NOT NULL DEFAULT '',
  `server_ip` varchar(75) NOT NULL DEFAULT '',
  `server_url` text NOT NULL,
  `employee_rep` int(12) unsigned NOT NULL DEFAULT '0',
  `notes` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `script_id` (`script_id`),
  KEY `script_password` (`script_password`),
  KEY `server_ip` (`server_ip`),
  KEY `employee_rep` (`employee_rep`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `registered_apps` VALUES(1, '30000001', '75a593a34aa5ba8e5e5788b7c899802e', '%', '%', 1, 'Entrada');

CREATE TABLE `sessions` (
  `sesskey` varchar(64) NOT NULL DEFAULT '',
  `expiry` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expireref` varchar(250) DEFAULT '',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sessdata` longtext,
  PRIMARY KEY (`sesskey`),
  KEY `sess2_expiry` (`expiry`),
  KEY `sess2_expireref` (`expireref`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `statistics` (
  `statistic_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) unsigned NOT NULL DEFAULT '0',
  `app_id` int(12) unsigned NOT NULL DEFAULT '0',
  `role` varchar(32) NOT NULL DEFAULT '',
  `group` varchar(32) NOT NULL DEFAULT '',
  `timestamp` bigint(64) NOT NULL DEFAULT '0',
  `prune_after` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`statistic_id`),
  KEY `proxy_id` (`proxy_id`,`app_id`,`role`,`group`,`timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `user_access` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(12) unsigned NOT NULL DEFAULT '0',
  `app_id` int(12) unsigned NOT NULL DEFAULT '0',
  `account_active` enum('true','false') NOT NULL DEFAULT 'true',
  `access_starts` bigint(64) NOT NULL DEFAULT '0',
  `access_expires` bigint(64) NOT NULL DEFAULT '0',
  `last_login` bigint(64) NOT NULL DEFAULT '0',
  `last_ip` varchar(75) NOT NULL DEFAULT '',
  `login_attempts` int(11) DEFAULT NULL,
  `locked_out_until` bigint(64) DEFAULT NULL,
  `role` varchar(35) NOT NULL DEFAULT '',
  `group` varchar(35) NOT NULL DEFAULT '',
  `extras` longtext NOT NULL,
  `notes` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `app_id` (`app_id`),
  KEY `account_active` (`account_active`),
  KEY `access_starts` (`access_starts`),
  KEY `access_expires` (`access_expires`),
  KEY `role` (`role`),
  KEY `group` (`group`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;

INSERT INTO `user_access` VALUES(1, 1, 1, 'true', 1216149930, 0, 1272910492, '0', NULL, NULL, 'admin', 'medtech', 'YToxOntzOjE2OiJhbGxvd19wb2RjYXN0aW5nIjtzOjM6ImFsbCI7fQ==', '');
INSERT INTO `user_access` VALUES(2, 2, 1, 'true', 1272910500, 0, 0, '', NULL, NULL, 'admin', 'staff', '', '');
INSERT INTO `user_access` VALUES(3, 3, 1, 'true', 1272910560, 0, 0, '', NULL, NULL, 'admin', 'staff', '', '');
INSERT INTO `user_access` VALUES(4, 4, 1, 'true', 1272910620, 0, 0, '', NULL, NULL, 'pcoordinator', 'staff', '', '');
INSERT INTO `user_access` VALUES(5, 5, 1, 'true', 1272910740, 0, 0, '', NULL, NULL, 'pcoordinator', 'staff', '', '');
INSERT INTO `user_access` VALUES(6, 6, 1, 'true', 1272910920, 0, 0, '', NULL, NULL, 'faculty', 'faculty', '', '');
INSERT INTO `user_access` VALUES(7, 7, 1, 'true', 1272910920, 0, 0, '', NULL, NULL, 'faculty', 'faculty', '', '');
INSERT INTO `user_access` VALUES(8, 8, 1, 'true', 1272911040, 0, 0, '', NULL, NULL, 'lecturer', 'faculty', '', '');
INSERT INTO `user_access` VALUES(9, 9, 1, 'true', 1272911100, 0, 0, '', NULL, NULL, 'lecturer', 'faculty', '', '');
INSERT INTO `user_access` VALUES(10, 10, 1, 'true', 1272911160, 0, 0, '', NULL, NULL, 'director', 'faculty', '', '');
INSERT INTO `user_access` VALUES(11, 11, 1, 'true', 1272911280, 0, 0, '', NULL, NULL, 'director', 'faculty', '', '');
INSERT INTO `user_access` VALUES(12, 12, 1, 'true', 1272911340, 0, 0, '', NULL, NULL, '2013', 'student', '', '');
INSERT INTO `user_access` VALUES(13, 13, 1, 'true', 1272911400, 0, 0, '', NULL, NULL, '2013', 'student', '', '');
INSERT INTO `user_access` VALUES(14, 14, 1, 'true', 1272911460, 0, 0, '', NULL, NULL, '2009', 'student', '', '');
INSERT INTO `user_access` VALUES(15, 15, 1, 'true', 1272911460, 0, 0, '', NULL, NULL, '2009', 'student', '', '');
INSERT INTO `user_access` VALUES(16, 16, 1, 'true', 1272911580, 0, 0, '', NULL, NULL, 'staff', 'medtech', '', '');
INSERT INTO `user_access` VALUES(17, 17, 1, 'true', 1272911640, 0, 0, '', NULL, NULL, 'admin', 'medtech', '', '');

CREATE TABLE `user_data` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `number` int(12) unsigned NOT NULL DEFAULT '0',
  `username` varchar(25) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `organisation_id` int(12) NOT NULL DEFAULT '1',
  `department` varchar(255) DEFAULT NULL,
  `prefix` varchar(10) NOT NULL DEFAULT '',
  `firstname` varchar(35) NOT NULL DEFAULT '',
  `lastname` varchar(35) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `email_alt` varchar(255) NOT NULL DEFAULT '',
  `google_id` varchar(128) DEFAULT NULL,
  `telephone` varchar(25) NOT NULL DEFAULT '',
  `fax` varchar(25) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `city` varchar(35) NOT NULL DEFAULT '',
  `province` varchar(35) NOT NULL DEFAULT '',
  `postcode` varchar(12) NOT NULL DEFAULT '',
  `country` varchar(35) NOT NULL DEFAULT '',
  `notes` text NOT NULL,
  `office_hours` text,
  `privacy_level` int(1) DEFAULT '0',
  `notifications` int(1) NOT NULL DEFAULT '0',
  `clinical` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `number` (`number`),
  KEY `password` (`password`),
  KEY `firstname` (`firstname`),
  KEY `lastname` (`lastname`),
  KEY `privacy_level` (`privacy_level`),
  KEY `google_id` (`google_id`),
  KEY `clinical` (`clinical`),
  FULLTEXT KEY `firstname_2` (`firstname`,`lastname`,`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;

INSERT INTO `user_data` VALUES(1, 4857241, 'admin', '75a593a34aa5ba8e5e5788b7c899802e', 1, NULL, '', 'John', 'Doe', 'joe@example.com', '', NULL, '', '', '', '', '', '', '', 'System Administrator', NULL, 0, 0, 1);
INSERT INTO `user_data` VALUES(2, 0, 'staff1', '70c03b990a3b4d605d16c4b277675a53', 1, NULL, '', 'Staffer', 'Example', 'staff@example.com', '', NULL, '', '', '', 'Kingston', '', 'K7L 3N6', '', '', '', 0, 0, 1);
INSERT INTO `user_data` VALUES(3, 0, 'staff2', '2d53bb3a4799fb91e206b69fc8fd1751', 1, NULL, '', 'Staff2', 'Example', 'staff2@example.com', '', NULL, '', '', '', 'Kingston', '', 'K7L 3N6', '', '', '', 0, 0, 1);
INSERT INTO `user_data` VALUES(4, 0, 'pcoord1', '30eab833e8f6b19f806a0030fa8e9462', 1, NULL, '', 'Pcoord', 'Example', 'pcoord1@example.com', '', NULL, '', '', '', 'Kingston', '', 'K7L 3N6', '', '', '', 0, 0, 1);
INSERT INTO `user_data` VALUES(5, 0, 'pcoord2', 'c68ca46b2cb70fd02918f9a47b9b1c5f', 1, NULL, '', 'Pcoord2', 'Example', 'pcoord2@example.com', '', NULL, '', '', '', 'Kingston', '', 'K7L 3N6', '', '', '', 0, 0, 1);
INSERT INTO `user_data` VALUES(6, 0, 'faculty1', 'b5212cfe887b48b7836e527e097ff9e9', 1, NULL, '', 'Faculty1', 'Example', 'faculty1@example.com', '', NULL, '', '', '', 'Kingston', '', 'K7L 3N6', '', '', '', 0, 0, 1);
INSERT INTO `user_data` VALUES(7, 0, 'faculty2', '6e8bb12d1d2cd08b2ac6017623f2af10', 1, NULL, '', 'Faculty', 'Example', 'faculty2@example.com', '', NULL, '', '', '', 'Kingston', '', 'K7L 3N6', '', '', '', 0, 0, 1);
INSERT INTO `user_data` VALUES(8, 0, 'lecturer1', 'b20972b7161d7947f4aa68b0655dd32d', 1, NULL, '', 'Lecturer', 'Example', 'lecturer1@example.com', '', NULL, '', '', '', 'Kingston', '', 'K7L 3N6', '', '', '', 0, 0, 1);
INSERT INTO `user_data` VALUES(9, 0, 'lecturer2', 'f38f9bff67c3e91e6afd7ecfb50eee35', 1, NULL, '', 'Lecturer2', 'Example', 'lecturer2@example.com', '', NULL, '', '', '', 'Kingston', '', 'K7L 3N6', '', '', '', 0, 0, 1);
INSERT INTO `user_data` VALUES(10, 0, 'director1', 'da18c5f84dbfea958df1f1d5e45d1c67', 1, NULL, '', 'Director1', 'Example', 'director1@example.com', '', NULL, '', '', '', 'Kingston', '', 'K7L 3N6', '', '', '', 0, 0, 1);
INSERT INTO `user_data` VALUES(11, 0, 'director2', 'b1571bb0a5551dbf6cbf9b893f22023e', 1, NULL, '', 'Director2', 'Example', 'director2@example.com', '', NULL, '', '', '', 'Kingston', '', 'K7L 3N6', '', '', '', 0, 0, 1);
INSERT INTO `user_data` VALUES(12, 0, 'student2013_1', 'c67d7a9e6a2bec877685a884a431c9d7', 1, NULL, '', 'Student1', 'Example', 'student1@example.com', '', NULL, '', '', '', 'Kingston', '', 'K7L 3N6', '', '', '', 0, 0, 1);
INSERT INTO `user_data` VALUES(13, 0, 'student2013_2', 'b1c4e2549066b5d045bfc6cf1c2cde0b', 1, NULL, '', 'Student2', 'Example', 'student2@example.com', '', NULL, '', '', '', 'Kingston', '', 'K7L 3N6', '', '', '', 0, 0, 1);
INSERT INTO `user_data` VALUES(14, 0, 'student2009_1', '4bb010c630978e4708f071b9bf096d84', 1, NULL, '', 'Student3', 'Example', 'student3@example.com', '', NULL, '', '', '', 'Kingston', '', 'K7L 3N6', '', '', '', 0, 0, 1);
INSERT INTO `user_data` VALUES(15, 0, 'student2009_2', 'bab6f1526c2c1b7e1c8dea815ad87f21', 1, NULL, '', 'Student4', 'Example', 'student4@example.com', '', NULL, '', '', '', 'Kingston', '', 'K7L 3N6', '', '', '', 0, 0, 1);
INSERT INTO `user_data` VALUES(16, 0, 'medtechstaff1', '6bc9c73ab663df5ef59c7a7c5d0f491c', 1, NULL, '', 'Medtech Staff 1', 'Example', 'staff3@example.com', '', NULL, '', '', '', 'Kingston', '', 'K7L 3N6', '', '', '', 0, 0, 1);
INSERT INTO `user_data` VALUES(17, 0, 'medtechadmin1', '84d32e69dc15d483ccd45e607b15f64a', 1, NULL, '', 'Medtech Admin 1', 'Example', 'exampleadmin@example.com', '', NULL, '', '', '', 'Kingston', '', 'K7L 3N6', '', '', '', 0, 0, 1);

CREATE TABLE `user_departments` (
  `udep_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(12) unsigned NOT NULL DEFAULT '0',
  `dep_id` int(12) unsigned NOT NULL DEFAULT '0',
  `dep_title` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`udep_id`),
  KEY `user_id` (`user_id`),
  KEY `dep_id` (`dep_id`),
  KEY `dep_title` (`dep_title`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1253 ;

INSERT INTO `user_departments` VALUES(1, 1, 1, 'System Administrator');

CREATE TABLE `user_incidents` (
  `incident_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) unsigned NOT NULL DEFAULT '0',
  `incident_title` text NOT NULL,
  `incident_description` text,
  `incident_severity` tinyint(1) NOT NULL DEFAULT '1',
  `incident_status` tinyint(1) NOT NULL DEFAULT '1',
  `incident_author_id` int(12) NOT NULL DEFAULT '0',
  `incident_date` bigint(64) NOT NULL DEFAULT '0',
  `follow_up_date` bigint(64) DEFAULT NULL,
  PRIMARY KEY (`incident_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `user_photos` (
  `photo_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) unsigned NOT NULL DEFAULT '0',
  `photo_mimetype` varchar(64) NOT NULL,
  `photo_filesize` int(32) NOT NULL DEFAULT '0',
  `photo_active` int(1) NOT NULL DEFAULT '1',
  `photo_type` int(1) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`photo_id`),
  KEY `photo_active` (`photo_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `user_preferences` (
  `preference_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` int(12) unsigned NOT NULL DEFAULT '0',
  `proxy_id` int(12) unsigned NOT NULL DEFAULT '0',
  `module` varchar(32) NOT NULL DEFAULT '',
  `preferences` text NOT NULL,
  `updated` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`preference_id`),
  KEY `app_id` (`app_id`,`proxy_id`,`module`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

INSERT INTO `user_preferences` VALUES(1, 1, 1, 'firstlogin', 'N;', 1272910492);
INSERT INTO `user_preferences` VALUES(2, 1, 1, 'users', 'N;', 1272911706);
INSERT INTO `user_preferences` VALUES(3, 1, 1, 'events', 'a:6:{s:5:"dtype";s:4:"week";s:2:"sb";s:4:"date";s:2:"so";s:3:"asc";s:2:"pp";i:25;s:19:"filter_defaults_set";b:1;s:7:"filters";a:1:{s:12:"organisation";a:1:{i:0;s:1:"1";}}}', 1272911708);
INSERT INTO `user_preferences` VALUES(4, 1, 1, 'courses', 'a:4:{s:2:"sb";s:4:"name";s:2:"so";s:3:"asc";s:2:"pp";i:25;s:15:"organisation_id";s:1:"1";}', 1272911712);
INSERT INTO `user_preferences` VALUES(5, 1, 1, 'communities', 'N;', 1272912219);
INSERT INTO `user_preferences` VALUES(6, 1, 1, 'dashboard', 'N;', 1272912220);
