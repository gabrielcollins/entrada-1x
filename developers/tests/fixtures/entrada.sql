SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
CREATE DATABASE `test_entrada_entrada` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `test_entrada_entrada`;

CREATE TABLE `communities` (
  `community_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_parent` int(12) NOT NULL DEFAULT '0',
  `category_id` int(12) NOT NULL DEFAULT '0',
  `community_url` text NOT NULL,
  `community_template` varchar(12) NOT NULL DEFAULT 'default',
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `communities_categories` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

INSERT INTO `communities_categories` VALUES(1, 0, 'Official Communities', '', '', 1, 0);
INSERT INTO `communities_categories` VALUES(2, 0, 'Other Communities', '', '', 1, 0);
INSERT INTO `communities_categories` VALUES(4, 1, 'Administration', 'A container for official administrative units to reside.', '', 1, 0);
INSERT INTO `communities_categories` VALUES(5, 1, 'Courses, etc.', 'A container for official course groups and communities to reside.', '', 1, 0);
INSERT INTO `communities_categories` VALUES(7, 2, 'Health & Wellness', '', '', 1, 0);
INSERT INTO `communities_categories` VALUES(8, 2, 'Sports & Leisure', '', '', 1, 0);
INSERT INTO `communities_categories` VALUES(9, 2, 'Learning & Teaching', '', '', 1, 0);
INSERT INTO `communities_categories` VALUES(15, 2, 'Careers in Health Care', '', '', 1, 0);
INSERT INTO `communities_categories` VALUES(11, 2, 'Miscellaneous', '', '', 1, 0);
INSERT INTO `communities_categories` VALUES(12, 1, 'Committees', '', '', 1, 0);
INSERT INTO `communities_categories` VALUES(14, 2, 'Social Responsibility', '', '', 1, 0);
INSERT INTO `communities_categories` VALUES(16, 2, 'Cultures & Communities', '', '', 1, 0);
INSERT INTO `communities_categories` VALUES(17, 2, 'Business & Finance', '', '', 1, 0);
INSERT INTO `communities_categories` VALUES(18, 2, 'Arts & Entertainment', '', '', 1, 0);

CREATE TABLE `communities_modules` (
  `module_id` int(12) NOT NULL AUTO_INCREMENT,
  `module_shortname` varchar(32) NOT NULL,
  `module_version` varchar(8) NOT NULL DEFAULT '1.0.0',
  `module_title` varchar(64) NOT NULL,
  `module_description` text NOT NULL,
  `module_active` int(1) NOT NULL DEFAULT '1',
  `module_permissions` text NOT NULL,
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`module_id`),
  KEY `module_shortname` (`module_shortname`),
  KEY `module_active` (`module_active`),
  FULLTEXT KEY `module_title` (`module_title`,`module_description`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

INSERT INTO `communities_modules` VALUES(1, 'announcements', '1.0.0', 'Announcements', 'The Announcements module allows you to post Announcements to your community.', 1, 'a:4:{s:3:"add";i:1;s:6:"delete";i:1;s:4:"edit";i:1;s:5:"index";i:0;}', 1173116408, 1);
INSERT INTO `communities_modules` VALUES(2, 'discussions', '1.0.0', 'Discussions', 'The Discussions module is a simple method you can use to host discussions.', 1, 'a:10:{s:9:"add-forum";i:1;s:8:"add-post";i:0;s:12:"delete-forum";i:1;s:11:"delete-post";i:0;s:10:"edit-forum";i:1;s:9:"edit-post";i:0;s:5:"index";i:0;s:10:"reply-post";i:0;s:10:"view-forum";i:0;s:9:"view-post";i:0;}', 1173116408, 1);
INSERT INTO `communities_modules` VALUES(3, 'galleries', '1.0.0', 'Galleries', 'The Galleries module allows you to add photo galleries and images to your community.', 1, 'a:13:{s:11:"add-comment";i:0;s:11:"add-gallery";i:1;s:9:"add-photo";i:0;s:10:"move-photo";i:0;s:14:"delete-comment";i:0;s:14:"delete-gallery";i:1;s:12:"delete-photo";i:0;s:12:"edit-comment";i:0;s:12:"edit-gallery";i:1;s:10:"edit-photo";i:0;s:5:"index";i:0;s:12:"view-gallery";i:0;s:10:"view-photo";i:0;}', 1173116408, 1);
INSERT INTO `communities_modules` VALUES(4, 'shares', '1.0.0', 'Document Sharing', 'The Document Sharing module gives you the ability to upload and share documents within your community.', 1, 'a:15:{s:11:"add-comment";i:0;s:10:"add-folder";i:1;s:8:"add-file";i:0;s:9:"move-file";i:0;s:12:"add-revision";i:0;s:14:"delete-comment";i:0;s:13:"delete-folder";i:1;s:11:"delete-file";i:0;s:15:"delete-revision";i:0;s:12:"edit-comment";i:0;s:11:"edit-folder";i:1;s:9:"edit-file";i:0;s:5:"index";i:0;s:11:"view-folder";i:0;s:9:"view-file";i:0;}', 1173116408, 1);
INSERT INTO `communities_modules` VALUES(5, 'polls', '1.0.0', 'Polling', 'This module allows communities to create their own polls for everything from adhoc open community polling to individual community member votes.', 1, 'a:10:{s:8:"add-poll";i:1;s:12:"add-question";i:1;s:13:"edit-question";i:1;s:15:"delete-question";i:1;s:11:"delete-poll";i:1;s:9:"edit-poll";i:1;s:9:"view-poll";i:0;s:9:"vote-poll";i:0;s:5:"index";i:0;s:8:"my-votes";i:0;}', 1216256830, 1408);
INSERT INTO `communities_modules` VALUES(6, 'events', '1.0.0', 'Events', 'The Events module allows you to post events to your community which will be accessible through iCalendar ics files or viewable in the community.', 1, 'a:4:{s:3:"add";i:1;s:6:"delete";i:1;s:4:"edit";i:1;s:5:"index";i:0;}', 1225209600, 3499);

CREATE TABLE `communities_most_active` (
  `cmactive_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL,
  `activity_order` int(2) NOT NULL,
  PRIMARY KEY (`cmactive_id`,`community_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_announcements` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_courses` (
  `community_course_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL,
  `course_id` int(12) NOT NULL,
  PRIMARY KEY (`community_course_id`),
  KEY `community_id` (`community_id`,`course_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE `community_discussion_topics` (
  `cdtopic_id` int(12) NOT NULL AUTO_INCREMENT,
  `cdtopic_parent` int(12) NOT NULL DEFAULT '0',
  `cdiscussion_id` int(12) NOT NULL DEFAULT '0',
  `community_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `topic_title` varchar(128) NOT NULL,
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
  FULLTEXT KEY `discussion_title` (`topic_title`,`topic_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_discussions` (
  `cdiscussion_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `cpage_id` int(12) NOT NULL DEFAULT '0',
  `forum_title` varchar(64) NOT NULL,
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
  KEY `admin_notification` (`admin_notifications`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_events` (
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
  KEY `community_id` (`community_id`,`cpage_id`,`proxy_id`,`event_start`,`event_finish`,`release_date`,`release_until`,`updated_date`,`updated_by`),
  FULLTEXT KEY `event_title` (`event_title`,`event_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_galleries` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_gallery_comments` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_gallery_photos` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_history` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_mailing_list_members` (
  `cmlmember_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL,
  `email` varchar(64) NOT NULL,
  `member_active` int(1) NOT NULL DEFAULT '0',
  `list_administrator` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmlmember_id`),
  UNIQUE KEY `member_id` (`community_id`,`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_mailing_lists` (
  `cmlist_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `list_name` varchar(64) NOT NULL,
  `list_type` enum('announcements','discussion','inactive') NOT NULL DEFAULT 'inactive',
  PRIMARY KEY (`cmlist_id`),
  KEY `community_id` (`community_id`,`list_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_members` (
  `cmember_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `member_active` int(1) NOT NULL DEFAULT '1',
  `member_joined` bigint(64) NOT NULL DEFAULT '0',
  `member_acl` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmember_id`),
  KEY `community_id` (`community_id`,`proxy_id`,`member_joined`,`member_acl`),
  KEY `member_active` (`member_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_modules` (
  `cmodule_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `module_id` int(12) NOT NULL DEFAULT '0',
  `module_active` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmodule_id`),
  KEY `community_id` (`community_id`,`module_id`,`module_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_notifications` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_notify_members` (
  `cnmember_id` int(12) NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `record_id` int(12) NOT NULL DEFAULT '0',
  `community_id` int(12) NOT NULL DEFAULT '0',
  `notify_type` varchar(32) NOT NULL DEFAULT 'announcement',
  `notify_active` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cnmember_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_page_options` (
  `cpoption_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL,
  `cpage_id` int(12) NOT NULL DEFAULT '0',
  `option_title` varchar(32) NOT NULL,
  `option_value` int(12) NOT NULL DEFAULT '1',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cpoption_id`,`community_id`,`cpage_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_pages` (
  `cpage_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `parent_id` int(12) NOT NULL DEFAULT '0',
  `page_order` int(3) NOT NULL DEFAULT '0',
  `page_type` varchar(16) NOT NULL DEFAULT 'default',
  `menu_title` varchar(48) NOT NULL,
  `page_title` text NOT NULL,
  `page_url` text NOT NULL,
  `page_content` longtext NOT NULL,
  `page_active` int(1) NOT NULL DEFAULT '1',
  `page_visible` int(1) NOT NULL DEFAULT '1',
  `allow_member_view` int(1) NOT NULL DEFAULT '1',
  `allow_troll_view` int(1) NOT NULL DEFAULT '1',
  `allow_public_view` int(1) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cpage_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_permissions` (
  `cpermission_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `module_id` int(12) NOT NULL DEFAULT '0',
  `action` varchar(64) NOT NULL,
  `level` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cpermission_id`),
  KEY `community_id` (`community_id`,`module_id`,`action`,`level`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_polls` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_polls_access` (
  `cpaccess_id` int(12) NOT NULL AUTO_INCREMENT,
  `cpolls_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cpaccess_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_polls_questions` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_polls_responses` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_polls_results` (
  `cpresults_id` int(12) NOT NULL AUTO_INCREMENT,
  `cpresponses_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cpresults_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_share_comments` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_share_file_versions` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_share_files` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `community_shares` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `course_contacts` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

INSERT INTO `course_contacts` VALUES(1, 1, 10, 'director', 0);
INSERT INTO `course_contacts` VALUES(2, 1, 2, 'ccoordinator', 0);

CREATE TABLE `course_files` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `course_links` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `course_objectives` (
  `cobjective_id` int(12) NOT NULL AUTO_INCREMENT,
  `course_id` int(12) NOT NULL DEFAULT '0',
  `objective_id` int(12) NOT NULL DEFAULT '0',
  `importance` int(2) NOT NULL DEFAULT '1',
  `objective_details` text,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`cobjective_id`),
  KEY `course_id` (`course_id`),
  KEY `objective_id` (`objective_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `courses` (
  `course_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) NOT NULL DEFAULT '0',
  `curriculum_type_id` int(12) unsigned NOT NULL DEFAULT '0',
  `parent_id` int(12) NOT NULL DEFAULT '0',
  `pcoord_id` int(12) unsigned NOT NULL DEFAULT '0',
  `evalrep_id` int(12) unsigned NOT NULL DEFAULT '0',
  `studrep_id` int(12) unsigned NOT NULL DEFAULT '0',
  `course_name` varchar(64) NOT NULL DEFAULT '',
  `course_code` varchar(16) NOT NULL DEFAULT '',
  `course_description` text,
  `course_objectives` text,
  `course_url` text,
  `course_message` text NOT NULL,
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `courses` VALUES(1, 1, 1, 0, 4, 0, 0, 'Example Course 1', 'AAA123', NULL, NULL, NULL, '', 0, 1);

CREATE TABLE `cron_community_notifications` (
  `ccnotification_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `cnotification_id` int(12) NOT NULL,
  `proxy_id` int(12) NOT NULL,
  PRIMARY KEY (`ccnotification_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `curriculum_lu_types` (
  `curriculum_type_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(12) unsigned NOT NULL DEFAULT '0',
  `curriculum_type_name` varchar(60) NOT NULL,
  `curriculum_type_description` text,
  `curriculum_type_order` int(12) unsigned NOT NULL DEFAULT '0',
  `curriculum_type_active` int(1) unsigned NOT NULL DEFAULT '1',
  `updated_date` bigint(64) unsigned NOT NULL,
  `updated_by` int(12) unsigned NOT NULL,
  PRIMARY KEY (`curriculum_type_id`),
  KEY `curriculum_type_order` (`curriculum_type_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

INSERT INTO `curriculum_lu_types` VALUES(1, 0, 'Term 1', NULL, 0, 1, 1250538588, 1);
INSERT INTO `curriculum_lu_types` VALUES(2, 0, 'Term 2', NULL, 1, 1, 1250538588, 1);
INSERT INTO `curriculum_lu_types` VALUES(3, 0, 'Term 3', NULL, 2, 1, 1250538588, 1);
INSERT INTO `curriculum_lu_types` VALUES(4, 0, 'Term 4', NULL, 3, 1, 1250538588, 1);
INSERT INTO `curriculum_lu_types` VALUES(5, 0, 'Term 5', NULL, 4, 1, 1250538588, 1);
INSERT INTO `curriculum_lu_types` VALUES(6, 0, 'Term 6', NULL, 5, 1, 1250538588, 1);
INSERT INTO `curriculum_lu_types` VALUES(7, 0, 'Term 7', NULL, 6, 1, 1250538588, 1);
INSERT INTO `curriculum_lu_types` VALUES(8, 0, 'Term 8', NULL, 7, 1, 1250538588, 1);

CREATE TABLE `event_audience` (
  `eaudience_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `audience_type` enum('proxy_id','grad_year','organisation_id') NOT NULL,
  `audience_value` varchar(16) NOT NULL,
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`eaudience_id`),
  KEY `event_id` (`event_id`),
  KEY `target_value` (`audience_value`),
  KEY `target_type` (`audience_type`),
  KEY `event_id_2` (`event_id`,`audience_type`,`audience_value`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

INSERT INTO `event_audience` VALUES(1, 1, 'grad_year', '2013', 1272912099, 1);
INSERT INTO `event_audience` VALUES(2, 2, 'proxy_id', '12', 1272912187, 1);

CREATE TABLE `event_contacts` (
  `econtact_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `contact_order` int(6) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`econtact_id`),
  UNIQUE KEY `event_id_2` (`event_id`,`proxy_id`),
  KEY `contact_order` (`contact_order`),
  KEY `event_id` (`event_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

INSERT INTO `event_contacts` VALUES(1, 1, 7, 0, 1272912099, 1);
INSERT INTO `event_contacts` VALUES(2, 2, 7, 0, 1272912187, 1);

CREATE TABLE `event_discussions` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `event_ed10` (
  `eed10_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `ed10_id` int(12) NOT NULL DEFAULT '0',
  `major_topic` tinyint(1) DEFAULT '0',
  `minor_topic` tinyint(1) DEFAULT '0',
  `minor_desc` varchar(32) DEFAULT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`eed10_id`),
  KEY `event_id` (`event_id`),
  KEY `ed10_id` (`ed10_id`),
  KEY `major_topic` (`major_topic`),
  KEY `minor_topic` (`minor_topic`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `event_ed11` (
  `eed11_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `ed11_id` int(12) NOT NULL DEFAULT '0',
  `major_topic` tinyint(1) DEFAULT '0',
  `minor_topic` tinyint(1) DEFAULT '0',
  `minor_desc` varchar(25) DEFAULT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`eed11_id`),
  KEY `event_id` (`event_id`),
  KEY `ed11_id` (`ed11_id`),
  KEY `major_topic` (`major_topic`),
  KEY `minor_topic` (`minor_topic`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `event_eventtypes` (
  `eeventtype_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL,
  `eventtype_id` int(12) NOT NULL,
  `duration` int(12) NOT NULL,
  PRIMARY KEY (`eeventtype_id`),
  KEY `event_id` (`event_id`),
  KEY `eventtype_id` (`eventtype_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

INSERT INTO `event_eventtypes` VALUES(1, 1, 6, 10);
INSERT INTO `event_eventtypes` VALUES(2, 1, 4, 20);
INSERT INTO `event_eventtypes` VALUES(3, 2, 2, 100);

CREATE TABLE `event_files` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `event_links` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `event_objectives` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `event_quiz_progress` (
  `eqprogress_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `equiz_id` int(12) unsigned NOT NULL,
  `event_id` int(12) unsigned NOT NULL,
  `quiz_id` int(12) unsigned NOT NULL,
  `proxy_id` int(12) unsigned NOT NULL,
  `progress_value` varchar(16) NOT NULL,
  `quiz_score` int(12) NOT NULL,
  `quiz_value` int(12) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) unsigned NOT NULL,
  PRIMARY KEY (`eqprogress_id`),
  KEY `event_id` (`equiz_id`,`event_id`,`proxy_id`),
  KEY `quiz_id` (`quiz_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE `event_quiz_responses` (
  `eqresponse_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `eqprogress_id` int(12) unsigned NOT NULL,
  `equiz_id` int(12) unsigned NOT NULL,
  `event_id` int(12) unsigned NOT NULL,
  `quiz_id` int(12) unsigned NOT NULL,
  `proxy_id` int(12) unsigned NOT NULL,
  `qquestion_id` int(12) unsigned NOT NULL,
  `qqresponse_id` int(12) unsigned NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) unsigned NOT NULL,
  PRIMARY KEY (`eqresponse_id`),
  KEY `eqprogress_id` (`eqprogress_id`,`equiz_id`,`event_id`,`quiz_id`,`proxy_id`,`qquestion_id`,`qqresponse_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE `event_quizzes` (
  `equiz_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `required` int(1) NOT NULL DEFAULT '0',
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
  PRIMARY KEY (`equiz_id`),
  KEY `event_id` (`event_id`),
  KEY `required` (`required`),
  KEY `timeframe` (`timeframe`),
  KEY `quiztype_id` (`quiztype_id`),
  KEY `quiz_id` (`quiz_id`),
  KEY `accesses` (`accesses`),
  KEY `release_date` (`release_date`,`release_until`),
  KEY `quiz_timeout` (`quiz_timeout`),
  KEY `quiz_attempts` (`quiz_attempts`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE `event_related` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `events` (
  `event_id` int(12) NOT NULL AUTO_INCREMENT,
  `recurring_id` int(12) DEFAULT '0',
  `eventtype_id` int(12) DEFAULT '0',
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
  PRIMARY KEY (`event_id`),
  KEY `course_id` (`course_id`),
  KEY `region_id` (`region_id`),
  KEY `eventtype_id` (`eventtype_id`),
  KEY `recurring_id` (`recurring_id`),
  KEY `release_date` (`release_date`,`release_until`),
  KEY `event_start` (`event_start`,`event_duration`),
  KEY `event_start_2` (`event_start`,`event_finish`),
  KEY `event_phase` (`event_phase`),
  FULLTEXT KEY `event_title` (`event_title`,`event_description`,`event_goals`,`event_objectives`,`event_message`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

INSERT INTO `events` VALUES(1, 0, 6, 0, 1, '1', 'Example Event', NULL, NULL, NULL, NULL, '', 1272945600, 1272947400, 30, 0, 0, 1272912099, 1);
INSERT INTO `events` VALUES(2, 0, 2, 0, 1, '1', 'Another Event', NULL, NULL, NULL, NULL, '', 1273118400, 1273124400, 100, 0, 0, 1272912187, 1);

CREATE TABLE `events_lu_ed10` (
  `ed10_id` int(12) NOT NULL AUTO_INCREMENT,
  `topic_name` varchar(60) NOT NULL,
  `topic_description` text,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`ed10_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=32 ;

INSERT INTO `events_lu_ed10` VALUES(1, 'Biostatistics', 'Biostatistics', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(2, 'Communication Skills', 'Communication Skills', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(3, 'Community Health', 'Community Health', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(4, 'End-of-Life Care', 'End-of-Life Care', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(5, 'Epidemiology', 'Epidemiology', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(6, 'Evidence-Based Medicine', 'Evidence-Based Medicine', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(7, 'Family Violence/Abuse', 'Family Violence/Abuse', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(8, 'Medical Genetics', 'Medical Genetics', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(9, 'Health Care Financing', 'Health Care Financing', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(10, 'Health Care Systems', 'Health Care Systems', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(11, 'Health Care Quality Review', 'Health Care Quality Review', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(12, 'Home Health Care', 'Home Health Care', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(13, 'Human Development/Life Cycle', 'Human Development/Life Cycle', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(14, 'Human Sexuality', 'Human Sexuality', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(15, 'Medical Ethics', 'Medical Ethics', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(16, 'Medical Humanities', 'Medical Humanities', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(17, 'Medical Informatics', 'Medical Informatics', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(18, 'Medical Jurisprudence', 'Medical Jurisprudence', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(19, 'Multicultural Medicine', 'Multicultural Medicine', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(20, 'Nutrition', 'Nutrition', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(21, 'Occupational Health/Medicine', 'Occupational Health/Medicine', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(22, 'Pain Management', 'Pain Management', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(23, 'Palliative Care', 'Palliative Care', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(24, 'Patient Health Education', 'Patient Health Education', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(25, 'Population-Based Medicine', 'Population-Based Medicine', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(26, 'Practice Management', 'Practice Management', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(27, 'Preventive Medicine', 'Preventive Medicine', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(28, 'Rehabilitation/Care of the Disabled', 'Rehabilitation/Care of the Disabled', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(29, 'Research Methods', 'Research Methods', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(30, 'Substance Abuse', 'Substance Abuse', 1215615910, 1);
INSERT INTO `events_lu_ed10` VALUES(31, 'Womens Health', 'Womens Health', 1215615910, 1);

CREATE TABLE `events_lu_ed11` (
  `ed11_id` int(12) NOT NULL AUTO_INCREMENT,
  `topic_name` varchar(60) NOT NULL,
  `topic_description` text,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`ed11_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

INSERT INTO `events_lu_ed11` VALUES(1, 'Anatomy', 'Anatomy', 1215615910, 1);
INSERT INTO `events_lu_ed11` VALUES(2, 'Biochemistry', 'Biochemistry', 1215615910, 1);
INSERT INTO `events_lu_ed11` VALUES(3, 'Genetics', 'Genetics', 1215615910, 1);
INSERT INTO `events_lu_ed11` VALUES(4, 'Physiology', 'Physiology', 1215615910, 1);
INSERT INTO `events_lu_ed11` VALUES(5, 'Microbiology and Immunology', 'Microbiology and Immunology', 1215615910, 1);
INSERT INTO `events_lu_ed11` VALUES(6, 'Pathology', 'Pathology', 1215615910, 1);
INSERT INTO `events_lu_ed11` VALUES(7, 'Pharmacology Therapeutics', 'Pharmacology Therapeutics', 1215615910, 1);
INSERT INTO `events_lu_ed11` VALUES(8, 'Preventive Medicine', 'Preventive Medicine', 1215615910, 1);

CREATE TABLE `events_lu_eventtypes` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

INSERT INTO `events_lu_eventtypes` VALUES(1, 'Lecture', 'Faculty member speaks to a whole group of students for the session. Ideally, the lecture is interactive, with brief student activities to apply learning within the talk or presentation. The focus, however, is on the faculty member speaking or presenting to a group of students.', 1, 0, NULL, NULL, 1250877835, 1);
INSERT INTO `events_lu_eventtypes` VALUES(2, 'Lab', 'In this session, practical learning, activity and demonstration take place, usually with specialized equipment, materials or methods and related to a class, or unit of teaching.', 1, 1, NULL, NULL, 1250877835, 1);
INSERT INTO `events_lu_eventtypes` VALUES(3, 'Small Group', 'In the session, students in small groups work on specific questions, problems, or tasks related to a topic or a case, using discussion and investigation. Faculty member facilitates. May occur in:\r\n<ul>\r\n<li><strong>Expanded Clinical Skills:</strong> demonstrations and practice of clinical approaches and assessments occur with students in small groups of 25 or fewer.</li>\r\n<li><strong>Team Based Learning Method:</strong> students are in pre-selected groups for the term to work on directed activities, often case-based. One-two faculty facilitate with all 100 students in small teams.</li>\r\n<li><strong>Peer Instruction:</strong> students work in partners on specific application activities throughout the session.</li>\r\n<li><strong>Seminars:</strong> Students are in small groups each with a faculty tutor or mentor to facilitate or coach each small group. Students are active in these groups, either sharing new information, working on tasks, cases, or problems. etc. This may include Problem Based Learning as a strategy where students research and explore aspects to solve issues raised by the case with faculty facilitating. Tutorials may also be incorporated here.</li>\r\n<li><strong>Clinical Skills:</strong> Students in the Clinical and Communication Skills courses work in small groups on specific tasks that allow application of clinical skills.</li>\r\n</ul>', 1, 2, NULL, NULL, 1219434863, 1);
INSERT INTO `events_lu_eventtypes` VALUES(4, 'Patient Contact Session', 'The focus of the session is on the patient(s) who will be present to answer students'' and/or professor''s questions and/or to offer a narrative about their life with a condition, or as a member of a specific population. Medical Science Rounds are one example.', 1, 4, NULL, NULL, 1219434863, 1);
INSERT INTO `events_lu_eventtypes` VALUES(5, 'Symposium / Student Presentation', 'For one or more hours, a variety of speakers, including students, present on topics to teach about current issues, research, etc.', 1, 6, NULL, NULL, 1219434863, 1);
INSERT INTO `events_lu_eventtypes` VALUES(6, 'Directed Independent Learning', 'Students work independently (in groups or on their own) outside of class sessions on specific tasks to acquire knowledge, and develop enquiry and critical evaluation skills, with time allocated into the timetable. Directed Independent Student Learning may include learning through interactive online modules, online quizzes, working on larger independent projects (such as Community Based Projects or Critical Enquiry), or completing reflective, research or other types of papers and reports. While much student independent learning is done on the studentsÕ own time, for homework, in this case, directed student time is built into the timetable as a specific session and linked directly to other learning in the course.', 1, 3, NULL, NULL, 1219434863, 1);
INSERT INTO `events_lu_eventtypes` VALUES(7, 'Review / Feedback Session', 'In this session faculty help students to prepare for future learning and assessment through de-briefing about previous learning in a quiz or assignment, through reviewing a week or more of learning, or through reviewing at the end of a course to prepare for summative examination.', 1, 5, NULL, NULL, 1219434863, 1);
INSERT INTO `events_lu_eventtypes` VALUES(8, 'Examination', 'Scheduled course examination time, including mid-term as well as final examinations. <strong>Please Note:</strong> These will be identified only by the Curricular Coordinators in the timetable.', 1, 7, NULL, NULL, 1219434863, 1);
INSERT INTO `events_lu_eventtypes` VALUES(9, 'Clerkship Seminars', 'Case-based, small-group sessions emphasizing more advanced and integrative topics. Students draw upon their clerkship experience with patients and healthcare teams to participate and interact with the faculty whose role is to facilitate the discussion.', 1, 8, NULL, NULL, 1250878869, 1);
INSERT INTO `events_lu_eventtypes` VALUES(10, 'Other', 'These are sessions that are not a part of the UGME curriculum but are recorded in MEdTech Central. Examples may be: Course Evaluation sessions, MD Management. NOTE: these will be identified only by the Curricular Coordinators in the timetable.', 1, 9, NULL, NULL, 1250878869, 1);

CREATE TABLE `events_lu_objectives` (
  `objective_id` int(12) NOT NULL AUTO_INCREMENT,
  `objective_name` varchar(60) NOT NULL,
  `objective_description` text,
  `objective_order` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`objective_id`),
  KEY `objective_order` (`objective_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `events_recurring` (
  `recurring_id` int(12) NOT NULL AUTO_INCREMENT,
  `recurring_date` bigint(64) NOT NULL,
  `recurring_until` bigint(64) NOT NULL,
  `recurring_type` enum('daily','weekly','monthly','yearly') NOT NULL,
  `recurring_frequency` int(12) NOT NULL,
  `recurring_number` int(12) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`recurring_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `filetypes` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `ext` varchar(8) NOT NULL,
  `mime` varchar(64) NOT NULL,
  `english` varchar(64) NOT NULL,
  `image` blob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ext` (`ext`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;

INSERT INTO `filetypes` VALUES(1, 'pdf', 'application/pdf', 'PDF Document', 0x47494638396110001000e60000660000c0c0c58e8e92828282eb060a666666e07e80d58e93dededfff6666bf4446d7c9ceff4b4eb4b4b4e77171a10406c7adb289898befeff0d5d5d6d80406eb9e9fd28c8ff4292bfe7679a3a3a7fda9a9990000ccccccc28386fec2c3e6e6e8cfa8adeeb0b09999998c0002f7f7f7fa7274a51819ff2125e9adafe28282edc1c2eca7a9707074e9d4d6ff7e80eaa7a8bcbcbfea9394e8c6c8c2c1c7a50e0ffdbcbee1e0e3e9e9eceb8787f40e11ababaccfcfd4e4060aff2b2effffff9c9ca1ff4f51ff9999e98d8eb6b6bad6d6dce67778ffccccebb5b59f0607ff8182dfc3c6eab5b6e9cccd86868bece6e7e3e0e2c4c4c5d79396ff7072ff7a7ca40608bdc5c5e6dee6ff292ca5a5ade78787ebc2c4e6d6deff0f12ff333300000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000021f9040514003e002c00000000100010000007d1803e823e0d038687880d833e4424123750433f933f11438b135a2d1f1105029d052c8a833b28595b2219053f03194d97a43732421308501f300b10338b1c1f372b1547472f450620508b3bbf37381231474c4a3bc9831c361f1221152a121f44d4bd0836374e0e373708131c018b01b53636291f36441c33300a26fb343454541b460058176048830457929c703105480902144648fad10041822e498260e08241c3051e1b74f820b1c307060609a40049d2c305831c48b09014e4a1668d2807721eb0d041c78445a43870d841b4d6a240003b);
INSERT INTO `filetypes` VALUES(2, 'gif', 'image/gif', 'GIF Image', 0x47494638396110001000e600000e2345cbd6dcb87f659999998282829b705df0f0f0b1b8dc8098cfb9b9bf4f484c5d85b7c3b9ad4e71c11d325f94b7fc8c8b8b435789b5a48affffff423637666b7a909ab3dededecccccc7a9cf26f8edca48868706663b5b5b5b19c83aeaeaef7f7f7779ce863749caebee1c0c2c4291d207c7678c6c7d477829da8a8a8b5adad8aacf7e8e8e8afa28c334268a0a5b1bdbdbdc5c9e59999cc644e506073acd6d6d6c1bbb4cfbda799bbf6a5a5adb8bfc94c3e3cdbdde9c5c5c5bdbdc57d7d7dc5d1e3a7a1b400000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000021f90405140013002c00000000100010000007ad8013131d048586871d828235822c1f189082208c8a9403171d293f1720938a13943d293d1d10189d948b9d061d2c9dafa9a09d303c1d3006b806b135201735070a101f3d2c2c189f35ac0b15251c26272991953d230d2e053b15323109898a1824192b33022816001124309f904038144121340e2f1f09ec352d12010f191a22033e5c60878181870d3774209800e2d8a71e357a48ec61e343c34f8220d6a80109c687588a2e881c596320c640003b);
INSERT INTO `filetypes` VALUES(3, 'jpg', 'image/jpeg', 'JPEG Image', 0x47494638396110001000d500000e2345ccccccaeaeae8c8c8c435789dedede7066636f8edcb8bfc9f7f7f7828282aebee1334268909ab3b19c839b705d8aacf763749cc1bcb41d325ff0f0f0c5d1e34f484c5d85b74236379999994e71c14c3e3ca48868779ce8c3b9adffffff666b7a291d20b5b5b5c6c7d49999ccd6d6d677829d8098cf7c7678c5c5c5b87f6599bbf6e7e7e7a0a5b1a3a3a3644e50afa28c6073acc5c9e594b7fccbd6dccfbda77a9cf2c0c1c5b1b8dcdbdde9bdbdbdb5b5bdb5adada7a1b4b5a48ac0c2c421f9040514001f002c00000000100010000006a3c0cf47a4281a8f22a1b044f9293f8968a2f4a46606898c2ee5ca4cabd981382508b0becad23435f8e928ba00859a8eea72a237654f5f260a2538160302292c2c01551422172021062823658969290b1a0c0f1b2024323a494a013f36102f2a260d0004373a4f0101152b183d1d31132d02a0420125303e3433360711190205ae011e0e1c35082750944a292529d529120209d1d225ddaf3a027d4f05e4e525c74f1f41003b);
INSERT INTO `filetypes` VALUES(4, 'zip', 'application/x-zip-compressed', 'Zip File', 0x47494638396110001000c400001c1c1ce6e6e6ccccccb5b6b58d8d8d7b7b7b666666f7f7f73f3f3fd6d6d6adadadffffffefefef5d5d5dc5c5c5dddddd999999333333757575bdbdbd85858599999900000000000000000000000000000000000000000000000000000000000021f9040514000b002c0000000010001000000583e0b20853699e8e2826abe24c8fcacae2434c94f41cc7bc2e87470142904c78be058bc76c2613ccc4c4c188aa943cc6d111600aaed003c3711074198caf4a901510b8e8805a2440a713e6803eb5b6530e0d7a0f090357750c011405080f130a8f867a898b09109601575c7a8a080203010c570b9a89040d8ea1a202ab020611009fa221003b);
INSERT INTO `filetypes` VALUES(5, 'exe', 'application/octet-stream', 'Windows Executable', 0x47494638396110001000b30000999999efefefdededeccccccb5b5b5fffffff7f7f7e6e6e6d5d5d5bdbdbd99999900000000000000000000000000000021f90405140005002c0000000010001000000453b09404aabd494aa1bbe69fa71906b879646a1660ea1a4827bc6ffcd169606f6fe007871d8bf4fb1d0e830ea2681420929a2533f8cc4481c7e3a0425821b24701a100f37cb34e42d91c760e08420d58403f8822003b);
INSERT INTO `filetypes` VALUES(6, 'html', 'text/html', 'HTML Document', 0x47494638396110001000e60000052e49e6e6e6bbc3ad7d7d7d80bf35669933d6d6d6ffffff45834280a256cccccca2a2a2bdbdbdbdee744882578fe32eefefef21564e6fbb3a9999998dde2c9999991f4d617676768db756dededeb5b5b571a82e9ee438f7f7f797cf4039724a7ca08bc4c4c4184d54d3dbca87b16a29614484d6317bb834518845ababab5ea338abe74c5b886194bd5907385888ad5a225a5725576cb5c5ad99e333c2cbb588c739487f3991e12f7db83dc6cebe64aa3874ab317fbe35bfc8b25f9c3c82a55894e6318eb8588cad5a00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000021f904041400ff002c00000000100010000007a98007070c138586870c828219821d190a0a191d93068a078c10022d1e1e2d02101d958a9234381f252a37353da196191041083036003a0f1810a28b10043e310e22110f3c01ba9710271c24202c2e2627c5ae1d2f142b0d0d16122f012196061d233b14333328053906898a061010233f1b1b09e82129deedf8b9290bfcf701ffff180cb830c1980180011ed983a0c0d28183ff1e3158d0ca2144892952185b074941888f011c1e0804003b);
INSERT INTO `filetypes` VALUES(7, 'mpg', 'video/mpeg', 'MPEG Movie', 0x47494638396110001000d50000737373c7d3dcaeb7c13e86c4e6e6e69999995792c5c1ccd4f7f7f74a99d899b9d0778ea7d8dadc7494b1298be0b5c2cbadadad8585854dadedbebebe85a7c0ffffff8c8c8c3292e3aec5d5d6d6d6a4a4a43d93d8ccccccbec6cdc3c3c34295dea7bfd24b9be472a0c22c94ed579bcee0e2e4469ddf8cadc9d1d4d8b6b6b6c5c9cd3e8bcf4593cba9bdcdd2d7dbdededeeaeaeb4a9cdeb5c5d64aadf7a9c6dcbdc9d36699ccd0d6db00000000000000000000000000000000000000000000000021f90405140015002c00000000100010000006a1c04a4513291a8f1aa19091617038150e24038165941506240228400a298be775555221dd8845c32e95851902624e9fbbb199179d202fc198782a08253617172426341e28782508061b201809210c4e7819280e357c2e120f4f781e1d232807142f262d29504a280c04262204010d1f2a2a13584f3018332c032b0b101a8c4a1c137c370a27021508c6c7710425254ed058424fd52f4c136f582fe2e24c2fd91541003b);
INSERT INTO `filetypes` VALUES(8, 'pps', 'application/vnd.ms-powerpoint', 'Microsoft PowerPoint', 0x47494638396110001000d50000c35d0478afe084a4cc9fa1a5838484f4f3efbf8b5dde8a8ce8ebeed4c9c0f3b076e4e2e0e5a971ccccccdededed2d590ff7a04bfbfbfd5d5d5fca151adb2b5d5c0adb5bdbda2ddf5ff9933fffffff67908bb7f92f7d1a9e6e0c9fe860ef1bf92ddcfc3f5b67ddb95a4c6c4c1dce889f6f6f6cc8a52b9c3cfffcc99dccdbff2b27affb655edebeaff7c08e7e7e7a7aaaeffb66aebe3bfff8c10efefefb5b5b5ff8207c5bdbde1d3c5f9be7eecae76f7f7efd7cbc0a6e1fcc7c5c300000000000021f90405140019002c0000000010001000000699c08c70482c661c92a472996c3490c4128b55721493999236237115ac5a2d56db884446e888233c2ecd662781a05178cf4ac9f08c15e05d366e335e0d1281770f240722192c2e0b2c84522c331905311d25192e4a910808161414032fa1140404143309381f0a21af0c392a39060b8f1915181c13102dbebe1a000e2c42152b2830351ecbcb1a26944233292937d520d3293b0546dcddde4641003b);
INSERT INTO `filetypes` VALUES(9, 'ppt', 'application/vnd.ms-powerpoint', 'Microsoft PowerPoint', 0x47494638396110001000e60000c35d048ebce294aac5adadad858585ebe9e6e4e1dfbaddf3be8a5cd0d4d8efaf77b1cdd6dfcebda1bddeff7a04eaad75ffffffdededefca151a2b5cbf2bf92a1d6f5ff9933d5c0adf67908b7d5ddf7f7f7c9f5ffcccccc8dacd6fe860ef9be7eb3c5cef7d1a9e6e6e6d6d8dfd7c8bba0bddf98bad2d6d6d6ced6deb5cedea4c2d491bfe4efefefdee0e3efb078ffb66ae9eaebcc8a52d2f1fcffcc99ffb655adbfd0ff7c08ff8c1094b5dd9cb4c9e4e3e1efb57bded6d6ff8207efb573dee6e6bddef7bfccd394add6bbc3cf9cbdd795c6e8e9ebefaac0d9ffffff00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000021f90405140048002c00000000100010000007aa804882838485481109898a091c8d8e1c11271093101a1197981a1a1027929a1a2c8b892c2c1a9d95229d20290b193241a41a89953a09233944262a0735b1899a11071b1d2b0102471330301a1c27a011451522d3053ad63a301c09cf1d0dde384322919dda1a462d22ca3022e81c0404032c241f140a2ef7f80f0806063048171642487060a360410c0022f8fb4763c68b1e1e2246c41083c520790c326624c19184454320438a2c1408003b);
INSERT INTO `filetypes` VALUES(10, 'png', 'image/png', 'PNG Image', 0x47494638396110001000e600000e2345ccccccaeaeaea48868435789dedede6666666f8edcb9b9bf848484f7f7f78aacf7334268909ab39b705da2a2a263749cb8bfc91d325fefefefc5d1e38c8b8baebee14f484c5d85b7c3b9ad4236374e71c14c3e3c779ce8b5b5b5666b7aafa28cffffff291d20c6c7d49999ccd6d6d6999999b19c8377829de8e9e98098cf7c7678b87f65c0c2c499bbf6a0a5b1644e506073acc5c9e5c1bbb4bdbdbd70666394b7fccbd6dccfbda77a9cf2b1b8dcdbdde9b5adadc5c5c4a5a5adbdbdc5b5a48aa7a1b400000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000021f90405140021002c00000000100010000007ad8021211e098586871e828225210a8c253d131e3d8e8a218c2915010935022508958a250a99020908250f01a18b0a0a9229aeb28ca2ae343b1e3413bb13b4ad05253a1715023d292901962592181f22352b233e01c9a23d161b0c0e1c1f243208898a012d390b302c280d00042d3496d4142e1a411d31122f0208ef2520403736390e403021a0c0bb00194e0cc0114145a36a8a7a40ea4171860005102396d8488d86274b8a0a881c59c220c840003b);
INSERT INTO `filetypes` VALUES(11, 'doc', 'application/msword', 'Microsoft Word', 0x47494638396110001000d500004476cadededeccccccafb8c784a6daeaecee92b8f5bdcbdfa5a5a56095eef6f4f2d1d8e0a2b2cfe2e5e9548ceaadc2e6d6d6d6ffffffcfd7e37a9fd98bafefc1c1c1adc5debccfeae8f1fd7698d4d6dee9ebeae7abc3efc2d0e4ebebe9dfe0e3b5b5b5e8eaec8fa5cd548dedd5dae1aaaaaabac9dacad7ebe6e5e2f0efefc5cedec2c2c293b0e3b3c7ebb3c7e4457ad29dbdf4e7e6e5e6dedef7efeff6f6f6d7d8dbc8d4e67b9cdec0d1ee598fee588ce8b5c5deaec8f4cfdbedc8d3e100000021f90405140011002c00000000100010000006aac048c42380188f479450b8593a9710a7875628a4141e4f2a458b2e3d9154af852be02e85ae73430b3d4e05dfc3a6517899b4c6a3b1d93c34282977111b0a31361d31312e24280583858a1f1d0b2e0b1f1b83313431241f311f07240128024e9b1f360b2828070b1001a64b81281f1224123e35462b4e1f0501c101bb20082515be05317d1b28100808204f2c061c1423d82f0c1f4f112c18303c09393a0003dd112e260413ed1922dd41003b);
INSERT INTO `filetypes` VALUES(17, 'mov', 'video/mpeg', 'MPEG Movie', 0x47494638396110001000d50000666666c7d3dcaeb7c13e86c4e6e6e69999995792c5c1ccd4f7f7f74a99d899b9d0778ea77494b1d8dadc298be0a7a7a7b5c2cb828282c3c3c34daded85a7c08b8b8bffffff3292e3aec5d5d6d6d6bebebe3d93d8adadadccccccbec6cdeeeeee4295dea7bfd24b9be472a0c22c94ed579bcee0e2e4469ddf8cadc9d1d4d8b5b5b5c5c9cd3e8bcf4593cba9bdcdd2d7dbdededeeaeaeb4a9cdeb5c5d64aadf7a9c6dcbdc9d36699ccd0d6db00000000000000000000000000000000000000000021f90405140016002c00000000100010000006a3408be511291a8f0fa1b0618a3554a94f27d3e148949646a5d8a970228f422183cd00c4e24a2722ee940988b83c6e222b33303901de6cd885192b08263717172527351229652608061b211809220d0d6e7719290e367b2f13101d9880121e2429071430272e2aa316290d04272304010c202b2b1a58a23118342d032c0b0f0f8c4a1d1a7b380a28021608c8c9197b262697d25842a2d7307e1a7f5830e4e47e30db1641003b);
INSERT INTO `filetypes` VALUES(12, 'xls', 'application/vnd.ms-excel', 'Microsoft Excel', 0x47494638396110001000d50000079f04c0f2bfd6d6d6b5b5b574d8714fc14d49b645f6f6f6a7a7a7eee9ee2dc52acccccce5e5e566cc66dedddeffffff9ad799a4c0a40abd048cc18b60d55cbababa4ace4771ba6fa8d4a7efefefe6dee610be0ac5c5c5afafaff4f4ff4bbf4853c24f66cc66efeff70bbb0606a60232c52c00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000021f9040514000f002c000000001000100000067ac0c783b110188f478650a85c3a850267e2e0701c32d7eb23ba641c8c592c96cbfc0aae99f4d8e9058bb3e4a1390c6767dce1bbddf838f8fd090b760b1c071e1e62447b461c480e1c76559202151d1d904b0e19099c091a020808034f0d140a0a12a924134d4e0d0104161bb300114f42101805051f1f0617b741003b);
INSERT INTO `filetypes` VALUES(16, 'mp3', 'audio/x-mp3', 'MP3 Audio', 0x47494638396110001000d500004b914fd5cfc8a5b1b59999998c8c8ce1e1e3b1ccd07e7e7eefefef6db36b87b3889fb397ccccccdedddef7f7f7bdbdbdb4cdf3a8b8afebe4b383de738aa98b66996680a381bcbfd7a5a5a5c6ddb6ffffffb5b6b591ba959cc0a1d6d6d6b6c5b9c4c4c44fa752e6e6e67ab769b4dbcad1d2d8e9e1d6e7e8ecadadad8bb68b61a15c93af91b5b6bd8cb4919cb2a096c8929ec4a355a15a00000000000000000000000000000000000000000000000000000000000000000000000000000000000021f9040514001a002c00000000100010000006a3408de681291a8f0fa1d0c3f4141a1b28a2c1506a3c070c6aa0c5340884a4d213de0e508f8d8800b27a340e84071460201c8e92dbd1508d4c122f02777a630e2c2d212b0b13092508556327250e1f140c2e1505905625270d2711160f0e29222291427617102429770a22056d4a760d0619310909000d0d62aa27c20d1d301c150c0f28560ca7ce051e03d205cccf4f0c6a08561a20a74f0d1ecadadb0ce5e66927db41003b);
INSERT INTO `filetypes` VALUES(13, 'swf', 'application/x-shockwave-flash', 'Macromedia Flash', 0x47494638396110001000d50000032f51d4d6deb5b8bc8c99a5808e9c6e8397eaedef557790c4cad09ca9b7f7f7f7dfe3e67a8b9b33536d949faed0d4d7999999c1c5c97676760b4568ffffffabababa2a2a2b5bdc4dedede1c44615b87a3aeb2b59999999aa5b1e4e7eb8a96a3bdbdbdd7dce2cbd1d9d6d6d659788f7d7d7dc4c4c410486c9ba6b2dfe0e6e6e6e6ccccccefefef8493a0b3bfcab5b5b5b8bcc0949ca596a1aed8dce3c4c8cb00000000000000000000000000000000000000000000000000000000000000000021f90405140014002c00000000100010000006a0400a0504291a8f20a110235460562b8c62ca5432151bd9e522dbb09c4a8a1446282042aa81006c65c52e8dcc6cfe618dc218162a6500905c33322a776d1d0614052709330e2a0f780a2d210a0b131a01040b34900f2d221e09070434230261232c2c9d0c0c04110f2615a7a9b4761b16b8a706bb062a2a2025121084420fbebe4fb20a2b6114c6c82b20160a8fcd0f0b0b4f201515c4610f502b26e32acd1441003b);
INSERT INTO `filetypes` VALUES(14, 'txt', 'text/plain', 'Plain Text File', 0x47494638396110001000d50000513d32d8d1cdc5c5c5afaea86c7879dededef0f0f0694e468799bbafb6c0888173aaa8a5f7f7f99db4decccccc474837828282945147e6e6e6999999d0a183b2b2b2e2d7d08d423a575349989685ffffffbdbdbdd6d6d6504c2ea5b5d15e443bb4b0a07b7b7b5e39308f8578d4d7dcdae1e4474a424f4231d6d6ceadadade3d6ce00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000021f9040514001a002c0000000010001000000691408da6e010188f488650c3590a194a0dc36058720c28c9c620585c22a04255c82998cd8391e860428d99857219a3f89c3a8e3747e2e0ec1f190026046e56447d051e09080d0d247a7c051b657d1c026286667e1b95027986971c95a20e5ba0a205461b1b15157a1c1b4802132110154e1c01010ea5ab137c4e4c16142571bc0b4dc1572a121271021bc14206717ed6d241003b);
INSERT INTO `filetypes` VALUES(15, 'rtf', 'text/richtext', 'Rich Text File', 0x47494638396110001000d50000513d32d8d1cdc5c5c5afaea86c7879dededef0f0f0694e468799bbafb6c0888173aaa8a5f7f7f99db4decccccc474837828282945147e6e6e6999999d0a183b2b2b2e2d7d08d423a575349989685ffffffbdbdbdd6d6d6504c2ea5b5d15e443bb4b0a07b7b7b5e39308f8578d4d7dcdae1e4474a424f4231d6d6ceadadade3d6ce00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000021f9040514001a002c0000000010001000000691408da6e010188f488650c3590a194a0dc36058720c28c9c620585c22a04255c82998cd8391e860428d99857219a3f89c3a8e3747e2e0ec1f190026046e56447d051e09080d0d247a7c051b657d1c026286667e1b95027986971c95a20e5ba0a205461b1b15157a1c1b4802132110154e1c01010ea5ab137c4e4c16142571bc0b4dc1572a121271021bc14206717ed6d241003b);

CREATE TABLE `global_lu_countries` (
  `countries_id` int(6) NOT NULL AUTO_INCREMENT,
  `country` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`countries_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=242 ;

INSERT INTO `global_lu_countries` VALUES(1, 'Afghanistan');
INSERT INTO `global_lu_countries` VALUES(2, 'land Islands');
INSERT INTO `global_lu_countries` VALUES(3, 'Albania');
INSERT INTO `global_lu_countries` VALUES(4, 'Algeria');
INSERT INTO `global_lu_countries` VALUES(5, 'American Samoa');
INSERT INTO `global_lu_countries` VALUES(6, 'Andorra');
INSERT INTO `global_lu_countries` VALUES(7, 'Angola');
INSERT INTO `global_lu_countries` VALUES(8, 'Anguilla');
INSERT INTO `global_lu_countries` VALUES(9, 'Antarctica');
INSERT INTO `global_lu_countries` VALUES(10, 'Antigua and Barbuda');
INSERT INTO `global_lu_countries` VALUES(11, 'Argentina');
INSERT INTO `global_lu_countries` VALUES(12, 'Armenia');
INSERT INTO `global_lu_countries` VALUES(13, 'Aruba');
INSERT INTO `global_lu_countries` VALUES(14, 'Australia');
INSERT INTO `global_lu_countries` VALUES(15, 'Austria');
INSERT INTO `global_lu_countries` VALUES(16, 'Azerbaijan');
INSERT INTO `global_lu_countries` VALUES(17, 'Bahamas');
INSERT INTO `global_lu_countries` VALUES(18, 'Bahrain');
INSERT INTO `global_lu_countries` VALUES(19, 'Bangladesh');
INSERT INTO `global_lu_countries` VALUES(20, 'Barbados');
INSERT INTO `global_lu_countries` VALUES(21, 'Belarus');
INSERT INTO `global_lu_countries` VALUES(22, 'Belgium');
INSERT INTO `global_lu_countries` VALUES(23, 'Belize');
INSERT INTO `global_lu_countries` VALUES(24, 'Benin');
INSERT INTO `global_lu_countries` VALUES(25, 'Bermuda');
INSERT INTO `global_lu_countries` VALUES(26, 'Bhutan');
INSERT INTO `global_lu_countries` VALUES(27, 'Bolivia');
INSERT INTO `global_lu_countries` VALUES(28, 'Bosnia and Herzegovina');
INSERT INTO `global_lu_countries` VALUES(29, 'Botswana');
INSERT INTO `global_lu_countries` VALUES(30, 'Bouvet Island');
INSERT INTO `global_lu_countries` VALUES(31, 'Brazil');
INSERT INTO `global_lu_countries` VALUES(32, 'British Indian Ocean territory');
INSERT INTO `global_lu_countries` VALUES(33, 'Brunei Darussalam');
INSERT INTO `global_lu_countries` VALUES(34, 'Bulgaria');
INSERT INTO `global_lu_countries` VALUES(35, 'Burkina Faso');
INSERT INTO `global_lu_countries` VALUES(36, 'Burundi');
INSERT INTO `global_lu_countries` VALUES(37, 'Cambodia');
INSERT INTO `global_lu_countries` VALUES(38, 'Cameroon');
INSERT INTO `global_lu_countries` VALUES(39, 'Canada');
INSERT INTO `global_lu_countries` VALUES(40, 'Cape Verde');
INSERT INTO `global_lu_countries` VALUES(41, 'Cayman Islands');
INSERT INTO `global_lu_countries` VALUES(42, 'Central African Republic');
INSERT INTO `global_lu_countries` VALUES(43, 'Chad');
INSERT INTO `global_lu_countries` VALUES(44, 'Chile');
INSERT INTO `global_lu_countries` VALUES(45, 'China');
INSERT INTO `global_lu_countries` VALUES(46, 'Christmas Island');
INSERT INTO `global_lu_countries` VALUES(47, 'Cocos (Keeling) Islands');
INSERT INTO `global_lu_countries` VALUES(48, 'Colombia');
INSERT INTO `global_lu_countries` VALUES(49, 'Comoros');
INSERT INTO `global_lu_countries` VALUES(50, 'Congo');
INSERT INTO `global_lu_countries` VALUES(51, 'Congo');
INSERT INTO `global_lu_countries` VALUES(52, 'Democratic Republic');
INSERT INTO `global_lu_countries` VALUES(53, 'Cook Islands');
INSERT INTO `global_lu_countries` VALUES(54, 'Costa Rica');
INSERT INTO `global_lu_countries` VALUES(55, 'C™te d''Ivoire (Ivory Coast)');
INSERT INTO `global_lu_countries` VALUES(56, 'Croatia (Hrvatska)');
INSERT INTO `global_lu_countries` VALUES(57, 'Cuba');
INSERT INTO `global_lu_countries` VALUES(58, 'Cyprus');
INSERT INTO `global_lu_countries` VALUES(59, 'Czech Republic');
INSERT INTO `global_lu_countries` VALUES(60, 'Denmark');
INSERT INTO `global_lu_countries` VALUES(61, 'Djibouti');
INSERT INTO `global_lu_countries` VALUES(62, 'Dominica');
INSERT INTO `global_lu_countries` VALUES(63, 'Dominican Republic');
INSERT INTO `global_lu_countries` VALUES(64, 'East Timor');
INSERT INTO `global_lu_countries` VALUES(65, 'Ecuador');
INSERT INTO `global_lu_countries` VALUES(66, 'Egypt');
INSERT INTO `global_lu_countries` VALUES(67, 'El Salvador');
INSERT INTO `global_lu_countries` VALUES(68, 'Equatorial Guinea');
INSERT INTO `global_lu_countries` VALUES(69, 'Eritrea');
INSERT INTO `global_lu_countries` VALUES(70, 'Estonia');
INSERT INTO `global_lu_countries` VALUES(71, 'Ethiopia');
INSERT INTO `global_lu_countries` VALUES(72, 'Falkland Islands');
INSERT INTO `global_lu_countries` VALUES(73, 'Faroe Islands');
INSERT INTO `global_lu_countries` VALUES(74, 'Fiji');
INSERT INTO `global_lu_countries` VALUES(75, 'Finland');
INSERT INTO `global_lu_countries` VALUES(76, 'France');
INSERT INTO `global_lu_countries` VALUES(77, 'French Guiana');
INSERT INTO `global_lu_countries` VALUES(78, 'French Polynesia');
INSERT INTO `global_lu_countries` VALUES(79, 'French Southern Territories');
INSERT INTO `global_lu_countries` VALUES(80, 'Gabon');
INSERT INTO `global_lu_countries` VALUES(81, 'Gambia');
INSERT INTO `global_lu_countries` VALUES(82, 'Georgia');
INSERT INTO `global_lu_countries` VALUES(83, 'Germany');
INSERT INTO `global_lu_countries` VALUES(84, 'Ghana');
INSERT INTO `global_lu_countries` VALUES(85, 'Gibraltar');
INSERT INTO `global_lu_countries` VALUES(86, 'Greece');
INSERT INTO `global_lu_countries` VALUES(87, 'Greenland');
INSERT INTO `global_lu_countries` VALUES(88, 'Grenada');
INSERT INTO `global_lu_countries` VALUES(89, 'Guadeloupe');
INSERT INTO `global_lu_countries` VALUES(90, 'Guam');
INSERT INTO `global_lu_countries` VALUES(91, 'Guatemala');
INSERT INTO `global_lu_countries` VALUES(92, 'Guinea');
INSERT INTO `global_lu_countries` VALUES(93, 'Guinea-Bissau');
INSERT INTO `global_lu_countries` VALUES(94, 'Guyana');
INSERT INTO `global_lu_countries` VALUES(95, 'Haiti');
INSERT INTO `global_lu_countries` VALUES(96, 'Heard and McDonald Islands');
INSERT INTO `global_lu_countries` VALUES(97, 'Honduras');
INSERT INTO `global_lu_countries` VALUES(98, 'Hong Kong');
INSERT INTO `global_lu_countries` VALUES(99, 'Hungary');
INSERT INTO `global_lu_countries` VALUES(100, 'Iceland');
INSERT INTO `global_lu_countries` VALUES(101, 'India');
INSERT INTO `global_lu_countries` VALUES(102, 'Indonesia');
INSERT INTO `global_lu_countries` VALUES(103, 'Iran');
INSERT INTO `global_lu_countries` VALUES(104, 'Iraq');
INSERT INTO `global_lu_countries` VALUES(105, 'Ireland');
INSERT INTO `global_lu_countries` VALUES(106, 'Israel');
INSERT INTO `global_lu_countries` VALUES(107, 'Italy');
INSERT INTO `global_lu_countries` VALUES(108, 'Jamaica');
INSERT INTO `global_lu_countries` VALUES(109, 'Japan');
INSERT INTO `global_lu_countries` VALUES(110, 'Jordan');
INSERT INTO `global_lu_countries` VALUES(111, 'Kazakhstan');
INSERT INTO `global_lu_countries` VALUES(112, 'Kenya');
INSERT INTO `global_lu_countries` VALUES(113, 'Kiribati');
INSERT INTO `global_lu_countries` VALUES(114, 'Korea (north)');
INSERT INTO `global_lu_countries` VALUES(115, 'Korea (south)');
INSERT INTO `global_lu_countries` VALUES(116, 'Kuwait');
INSERT INTO `global_lu_countries` VALUES(117, 'Kyrgyzstan');
INSERT INTO `global_lu_countries` VALUES(118, 'Lao People''s Democratic Republic');
INSERT INTO `global_lu_countries` VALUES(119, 'Latvia');
INSERT INTO `global_lu_countries` VALUES(120, 'Lebanon');
INSERT INTO `global_lu_countries` VALUES(121, 'Lesotho');
INSERT INTO `global_lu_countries` VALUES(122, 'Liberia');
INSERT INTO `global_lu_countries` VALUES(123, 'Libyan Arab Jamahiriya');
INSERT INTO `global_lu_countries` VALUES(124, 'Liechtenstein');
INSERT INTO `global_lu_countries` VALUES(125, 'Lithuania');
INSERT INTO `global_lu_countries` VALUES(126, 'Luxembourg');
INSERT INTO `global_lu_countries` VALUES(127, 'Macao');
INSERT INTO `global_lu_countries` VALUES(128, 'Macedonia');
INSERT INTO `global_lu_countries` VALUES(129, 'Madagascar');
INSERT INTO `global_lu_countries` VALUES(130, 'Malawi');
INSERT INTO `global_lu_countries` VALUES(131, 'Malaysia');
INSERT INTO `global_lu_countries` VALUES(132, 'Maldives');
INSERT INTO `global_lu_countries` VALUES(133, 'Mali');
INSERT INTO `global_lu_countries` VALUES(134, 'Malta');
INSERT INTO `global_lu_countries` VALUES(135, 'Marshall Islands');
INSERT INTO `global_lu_countries` VALUES(136, 'Martinique');
INSERT INTO `global_lu_countries` VALUES(137, 'Mauritania');
INSERT INTO `global_lu_countries` VALUES(138, 'Mauritius');
INSERT INTO `global_lu_countries` VALUES(139, 'Mayotte');
INSERT INTO `global_lu_countries` VALUES(140, 'Mexico');
INSERT INTO `global_lu_countries` VALUES(141, 'Micronesia');
INSERT INTO `global_lu_countries` VALUES(142, 'Moldova');
INSERT INTO `global_lu_countries` VALUES(143, 'Monaco');
INSERT INTO `global_lu_countries` VALUES(144, 'Mongolia');
INSERT INTO `global_lu_countries` VALUES(145, 'Montserrat');
INSERT INTO `global_lu_countries` VALUES(146, 'Morocco');
INSERT INTO `global_lu_countries` VALUES(147, 'Mozambique');
INSERT INTO `global_lu_countries` VALUES(148, 'Myanmar');
INSERT INTO `global_lu_countries` VALUES(149, 'Namibia');
INSERT INTO `global_lu_countries` VALUES(150, 'Nauru');
INSERT INTO `global_lu_countries` VALUES(151, 'Nepal');
INSERT INTO `global_lu_countries` VALUES(152, 'Netherlands');
INSERT INTO `global_lu_countries` VALUES(153, 'Netherlands Antilles');
INSERT INTO `global_lu_countries` VALUES(154, 'New Caledonia');
INSERT INTO `global_lu_countries` VALUES(155, 'New Zealand');
INSERT INTO `global_lu_countries` VALUES(156, 'Nicaragua');
INSERT INTO `global_lu_countries` VALUES(157, 'Niger');
INSERT INTO `global_lu_countries` VALUES(158, 'Nigeria');
INSERT INTO `global_lu_countries` VALUES(159, 'Niue');
INSERT INTO `global_lu_countries` VALUES(160, 'Norfolk Island');
INSERT INTO `global_lu_countries` VALUES(161, 'Northern Mariana Islands');
INSERT INTO `global_lu_countries` VALUES(162, 'Norway');
INSERT INTO `global_lu_countries` VALUES(163, 'Oman');
INSERT INTO `global_lu_countries` VALUES(164, 'Pakistan');
INSERT INTO `global_lu_countries` VALUES(165, 'Palau');
INSERT INTO `global_lu_countries` VALUES(166, 'Palestinian Territories');
INSERT INTO `global_lu_countries` VALUES(167, 'Panama');
INSERT INTO `global_lu_countries` VALUES(168, 'Papua New Guinea');
INSERT INTO `global_lu_countries` VALUES(169, 'Paraguay');
INSERT INTO `global_lu_countries` VALUES(170, 'Peru');
INSERT INTO `global_lu_countries` VALUES(171, 'Philippines');
INSERT INTO `global_lu_countries` VALUES(172, 'Pitcairn');
INSERT INTO `global_lu_countries` VALUES(173, 'Poland');
INSERT INTO `global_lu_countries` VALUES(174, 'Portugal');
INSERT INTO `global_lu_countries` VALUES(175, 'Puerto Rico');
INSERT INTO `global_lu_countries` VALUES(176, 'Qatar');
INSERT INTO `global_lu_countries` VALUES(177, 'RÃ©union');
INSERT INTO `global_lu_countries` VALUES(178, 'Romania');
INSERT INTO `global_lu_countries` VALUES(179, 'Russian Federation');
INSERT INTO `global_lu_countries` VALUES(180, 'Rwanda');
INSERT INTO `global_lu_countries` VALUES(181, 'Saint Helena');
INSERT INTO `global_lu_countries` VALUES(182, 'Saint Kitts and Nevis');
INSERT INTO `global_lu_countries` VALUES(183, 'Saint Lucia');
INSERT INTO `global_lu_countries` VALUES(184, 'Saint Pierre and Miquelon');
INSERT INTO `global_lu_countries` VALUES(185, 'Saint Vincent and the Grenadines');
INSERT INTO `global_lu_countries` VALUES(186, 'Samoa');
INSERT INTO `global_lu_countries` VALUES(187, 'San Marino');
INSERT INTO `global_lu_countries` VALUES(188, 'Sao Tome and Principe');
INSERT INTO `global_lu_countries` VALUES(189, 'Saudi Arabia');
INSERT INTO `global_lu_countries` VALUES(190, 'Senegal');
INSERT INTO `global_lu_countries` VALUES(191, 'Serbia and Montenegro');
INSERT INTO `global_lu_countries` VALUES(192, 'Seychelles');
INSERT INTO `global_lu_countries` VALUES(193, 'Sierra Leone');
INSERT INTO `global_lu_countries` VALUES(194, 'Singapore');
INSERT INTO `global_lu_countries` VALUES(195, 'Slovakia');
INSERT INTO `global_lu_countries` VALUES(196, 'Slovenia');
INSERT INTO `global_lu_countries` VALUES(197, 'Solomon Islands');
INSERT INTO `global_lu_countries` VALUES(198, 'Somalia');
INSERT INTO `global_lu_countries` VALUES(199, 'South Africa');
INSERT INTO `global_lu_countries` VALUES(200, 'South Georgia and the South Sandwich Islands');
INSERT INTO `global_lu_countries` VALUES(201, 'Spain');
INSERT INTO `global_lu_countries` VALUES(202, 'Sri Lanka');
INSERT INTO `global_lu_countries` VALUES(203, 'Sudan');
INSERT INTO `global_lu_countries` VALUES(204, 'Suriname');
INSERT INTO `global_lu_countries` VALUES(205, 'Svalbard and Jan Mayen Islands');
INSERT INTO `global_lu_countries` VALUES(206, 'Swaziland');
INSERT INTO `global_lu_countries` VALUES(207, 'Sweden');
INSERT INTO `global_lu_countries` VALUES(208, 'Switzerland');
INSERT INTO `global_lu_countries` VALUES(209, 'Syria');
INSERT INTO `global_lu_countries` VALUES(210, 'Taiwan');
INSERT INTO `global_lu_countries` VALUES(211, 'Tajikistan');
INSERT INTO `global_lu_countries` VALUES(212, 'Tanzania');
INSERT INTO `global_lu_countries` VALUES(213, 'Thailand');
INSERT INTO `global_lu_countries` VALUES(214, 'Togo');
INSERT INTO `global_lu_countries` VALUES(215, 'Tokelau');
INSERT INTO `global_lu_countries` VALUES(216, 'Tonga');
INSERT INTO `global_lu_countries` VALUES(217, 'Trinidad and Tobago');
INSERT INTO `global_lu_countries` VALUES(218, 'Tunisia');
INSERT INTO `global_lu_countries` VALUES(219, 'Turkey');
INSERT INTO `global_lu_countries` VALUES(220, 'Turkmenistan');
INSERT INTO `global_lu_countries` VALUES(221, 'Turks and Caicos Islands');
INSERT INTO `global_lu_countries` VALUES(222, 'Tuvalu');
INSERT INTO `global_lu_countries` VALUES(223, 'Uganda');
INSERT INTO `global_lu_countries` VALUES(224, 'Ukraine');
INSERT INTO `global_lu_countries` VALUES(225, 'United Arab Emirates');
INSERT INTO `global_lu_countries` VALUES(226, 'United Kingdom');
INSERT INTO `global_lu_countries` VALUES(227, 'United States of America');
INSERT INTO `global_lu_countries` VALUES(228, 'Uruguay');
INSERT INTO `global_lu_countries` VALUES(229, 'Uzbekistan');
INSERT INTO `global_lu_countries` VALUES(230, 'Vanuatu');
INSERT INTO `global_lu_countries` VALUES(231, 'Vatican City');
INSERT INTO `global_lu_countries` VALUES(232, 'Venezuela');
INSERT INTO `global_lu_countries` VALUES(233, 'Vietnam');
INSERT INTO `global_lu_countries` VALUES(234, 'Virgin Islands (British)');
INSERT INTO `global_lu_countries` VALUES(235, 'Virgin Islands (US)');
INSERT INTO `global_lu_countries` VALUES(236, 'Wallis and Futuna Islands');
INSERT INTO `global_lu_countries` VALUES(237, 'Western Sahara');
INSERT INTO `global_lu_countries` VALUES(238, 'Yemen');
INSERT INTO `global_lu_countries` VALUES(239, 'Zaire');
INSERT INTO `global_lu_countries` VALUES(240, 'Zambia');
INSERT INTO `global_lu_countries` VALUES(241, 'Zimbabwe');

CREATE TABLE `global_lu_disciplines` (
  `discipline_id` int(11) NOT NULL AUTO_INCREMENT,
  `discipline` varchar(250) NOT NULL,
  PRIMARY KEY (`discipline_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=65 ;

INSERT INTO `global_lu_disciplines` VALUES(1, 'Adolescent Medicine');
INSERT INTO `global_lu_disciplines` VALUES(2, 'Anatomical Pathology');
INSERT INTO `global_lu_disciplines` VALUES(3, 'Anesthesiology');
INSERT INTO `global_lu_disciplines` VALUES(4, 'Cardiac Surgery');
INSERT INTO `global_lu_disciplines` VALUES(5, 'Cardiology');
INSERT INTO `global_lu_disciplines` VALUES(6, 'Child & Adolescent Psychiatry');
INSERT INTO `global_lu_disciplines` VALUES(7, 'Clinical Immunology and Allergy');
INSERT INTO `global_lu_disciplines` VALUES(8, 'Clinical Pharmacology');
INSERT INTO `global_lu_disciplines` VALUES(9, 'Colorectal Surgery');
INSERT INTO `global_lu_disciplines` VALUES(10, 'Community Medicine');
INSERT INTO `global_lu_disciplines` VALUES(11, 'Critical Care Medicine');
INSERT INTO `global_lu_disciplines` VALUES(12, 'Dermatology');
INSERT INTO `global_lu_disciplines` VALUES(13, 'Developmental Pediatrics');
INSERT INTO `global_lu_disciplines` VALUES(14, 'Diagnostic Radiology');
INSERT INTO `global_lu_disciplines` VALUES(15, 'Emergency Medicine');
INSERT INTO `global_lu_disciplines` VALUES(16, 'Endocrinology and Metabolism');
INSERT INTO `global_lu_disciplines` VALUES(17, 'Family Medicine');
INSERT INTO `global_lu_disciplines` VALUES(18, 'Forensic Pathology');
INSERT INTO `global_lu_disciplines` VALUES(19, 'Forensic Psychiatry');
INSERT INTO `global_lu_disciplines` VALUES(20, 'Gastroenterology');
INSERT INTO `global_lu_disciplines` VALUES(21, 'General Pathology');
INSERT INTO `global_lu_disciplines` VALUES(22, 'General Surgery');
INSERT INTO `global_lu_disciplines` VALUES(23, 'General Surgical Oncology');
INSERT INTO `global_lu_disciplines` VALUES(24, 'Geriatric Medicine');
INSERT INTO `global_lu_disciplines` VALUES(25, 'Geriatric Psychiatry');
INSERT INTO `global_lu_disciplines` VALUES(26, 'Gynecologic Oncology');
INSERT INTO `global_lu_disciplines` VALUES(27, 'Gynecologic Reproductive Endocrinology and Infertility');
INSERT INTO `global_lu_disciplines` VALUES(28, 'Hematological Pathology ');
INSERT INTO `global_lu_disciplines` VALUES(29, 'Hematology');
INSERT INTO `global_lu_disciplines` VALUES(30, 'Infectious Disease');
INSERT INTO `global_lu_disciplines` VALUES(31, 'Internal Medicine');
INSERT INTO `global_lu_disciplines` VALUES(32, 'Maternal-Fetal Medicine');
INSERT INTO `global_lu_disciplines` VALUES(33, 'Medical Biochemistry');
INSERT INTO `global_lu_disciplines` VALUES(34, 'Medical Genetics');
INSERT INTO `global_lu_disciplines` VALUES(35, 'Medical Microbiology');
INSERT INTO `global_lu_disciplines` VALUES(36, 'Medical Oncology');
INSERT INTO `global_lu_disciplines` VALUES(37, 'Neonatal-Perinatal Medicine');
INSERT INTO `global_lu_disciplines` VALUES(38, 'Nephrology');
INSERT INTO `global_lu_disciplines` VALUES(39, 'Neurology');
INSERT INTO `global_lu_disciplines` VALUES(40, 'Neuropathology');
INSERT INTO `global_lu_disciplines` VALUES(41, 'Neuroradiology');
INSERT INTO `global_lu_disciplines` VALUES(42, 'Neurosurgery');
INSERT INTO `global_lu_disciplines` VALUES(43, 'Nuclear Medicine');
INSERT INTO `global_lu_disciplines` VALUES(44, 'Obstetrics & Gynecology');
INSERT INTO `global_lu_disciplines` VALUES(45, 'Occupational Medicine');
INSERT INTO `global_lu_disciplines` VALUES(46, 'Ophthalmology');
INSERT INTO `global_lu_disciplines` VALUES(47, 'Orthopedic Surgery');
INSERT INTO `global_lu_disciplines` VALUES(48, 'Otolaryngology-Head and Neck Surgery');
INSERT INTO `global_lu_disciplines` VALUES(49, 'Palliative Medicine');
INSERT INTO `global_lu_disciplines` VALUES(50, 'Pediatric Emergency Medicine');
INSERT INTO `global_lu_disciplines` VALUES(51, 'Pediatric General Surgery');
INSERT INTO `global_lu_disciplines` VALUES(52, 'Pediatric Hematology/Oncology');
INSERT INTO `global_lu_disciplines` VALUES(53, 'Pediatric Radiology');
INSERT INTO `global_lu_disciplines` VALUES(54, 'Pediatrics');
INSERT INTO `global_lu_disciplines` VALUES(55, 'Physical Medicine and Rehabilitation');
INSERT INTO `global_lu_disciplines` VALUES(56, 'Plastic Surgery');
INSERT INTO `global_lu_disciplines` VALUES(57, 'Psychiatry');
INSERT INTO `global_lu_disciplines` VALUES(58, 'Radiation Oncology');
INSERT INTO `global_lu_disciplines` VALUES(59, 'Respirology');
INSERT INTO `global_lu_disciplines` VALUES(60, 'Rheumatology');
INSERT INTO `global_lu_disciplines` VALUES(61, 'Thoracic Surgery');
INSERT INTO `global_lu_disciplines` VALUES(62, 'Transfusion Medicine');
INSERT INTO `global_lu_disciplines` VALUES(63, 'Urology');
INSERT INTO `global_lu_disciplines` VALUES(64, 'Vascular Surgery');

CREATE TABLE `global_lu_objectives` (
  `objective_id` int(12) NOT NULL AUTO_INCREMENT,
  `objective_name` varchar(60) NOT NULL,
  `objective_description` text,
  `objective_code` varchar(24) DEFAULT NULL,
  `objective_parent` int(12) NOT NULL DEFAULT '0',
  `objective_order` int(12) NOT NULL DEFAULT '0',
  `objective_active` int(12) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`objective_id`),
  KEY `objective_order` (`objective_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=530 ;

INSERT INTO `global_lu_objectives` VALUES(1, 'Queen''s Objectives', '', NULL, 0, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(2, 'Medical Expert', '', NULL, 1, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(3, 'Professionalism', '', NULL, 1, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(4, 'Scholar', '', NULL, 1, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(5, 'Communicator', '', NULL, 1, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(6, 'Collaborator', '', NULL, 1, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(7, 'Advocate', '', NULL, 1, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(8, 'Manager', '', NULL, 1, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(9, 'Application of Basic Sciences', 'The competent medical graduate articulates and uses the basic sciences to inform disease prevention, health promotion and the assessment and management of patients presenting with clinical illness.', NULL, 2, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(10, 'Clinical Assessment', 'Is able to perform a complete and appropriate clinical assessment of patients presenting with clinical illness', NULL, 2, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(11, 'Clinical Presentations', 'Is able to appropriately assess and provide initial management for patients presenting with clinical illness, as defined by the Medical Council of Canada Clinical Presentations', NULL, 2, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(12, 'Health Promotion', 'Apply knowledge of disease prevention and health promotion to the care of patients', NULL, 2, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(13, 'Professional Behaviour', 'Demonstrates appropriate professional behaviours to serve patients, the profession, and society', NULL, 3, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(14, 'Principles of Professionalism', 'Apply knowledge of legal and ethical principles to serve patients, the profession, and society', NULL, 3, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(15, 'Critical Appraisal', 'Critically evaluate medical information and its sources (the literature)', NULL, 4, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(16, 'Research', 'Contribute to the process of knowledge creation (research)', NULL, 4, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(17, 'Life Long Learning', 'Engages in life long learning', NULL, 4, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(18, 'Effective Communication', 'Effectively communicates with colleagues, other health professionals, patients, families and other caregivers', NULL, 5, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(19, 'Effective Collaboration', 'Effectively collaborate with colleagues and other health professionals', NULL, 6, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(20, 'Determinants of Health', 'Articulate and apply the determinants of health and disease, principles of health promotion and disease prevention', NULL, 7, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(21, 'Profession and Community', 'Effectively advocate for their patients, the profession, and community', NULL, 7, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(22, 'Practice Options', 'Describes a variety of practice options and settings within the practice of Medicine', NULL, 8, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(23, 'Balancing Health and Profession', 'Balances personal health and professional responsibilities', NULL, 8, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(24, 'ME1.1 Homeostasis & Dysregulation', 'Applies knowledge of molecular, biochemical, cellular, and systems-level mechanisms that maintain homeostasis, and of the dysregulation of these mechanisms, to the prevention, diagnosis, and management of disease.', NULL, 9, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(25, 'ME1.2 Physics and Chemistry', 'Apply major principles of physics and chemistry to explain normal biology, the pathobiology of significant diseases, and the mechanism of action of major technologies used in the prevention, diagnosis, and treatment of disease.', NULL, 9, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(26, 'ME1.3 Genetics', 'Use the principles of genetic transmission, molecular biology of the human genome, and population genetics to guide assessments and clinical decision making.', NULL, 9, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(27, 'ME1.4 Defense Mechanisms', 'Apply the principles of the cellular and molecular basis of immune and nonimmune host defense mechanisms in health and disease to determine the etiology of disease, identify preventive measures, and predict response to therapies.', NULL, 9, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(28, 'ME1.5 Pathological Processes', 'Apply the mechanisms of general and disease-specific pathological processes in health and disease to the prevention, diagnosis, management, and prognosis of critical human disorders.', NULL, 9, 4, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(29, 'ME1.6 Microorganisms', 'Apply principles of the biology of microorganisms in normal physiology and disease to explain the etiology of disease, identify preventive measures, and predict response to therapies.', NULL, 9, 5, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(30, 'ME1.7 Pharmacology', 'Apply the principles of pharmacology to evaluate options for safe, rational, and optimally beneficial drug therapy.', NULL, 9, 6, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(32, 'ME2.1 History and Physical', 'Conducts a comprehensive and appropriate history and physical examination ', NULL, 10, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(33, 'ME2.2 Procedural Skills', 'Demonstrate proficient and appropriate use of selected procedural skills, diagnostic and therapeutic', NULL, 10, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(34, 'ME3.x Clinical Presentations', '', NULL, 11, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(35, 'ME4.1 Health Promotion & Maintenance', '', NULL, 12, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(36, 'P1.1 Professional Behaviour', 'Practice appropriate professional behaviours, including honesty, integrity, commitment, dependability, compassion, respect, an understanding of the human condition, and altruism in the educational  and clinical settings', NULL, 13, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(37, 'P1.2 Patient-Centered Care', 'Learn how to deliver the highest quality patient-centered care, with commitment to patients'' well being.  ', NULL, 13, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(38, 'P1.3 Self-Awareness', 'Is self-aware, engages consultancy appropriately and maintains competence', NULL, 13, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(39, 'P2.1 Ethics', 'Analyze ethical issues encountered in practice (such as informed consent, confidentiality, truth telling, vulnerable populations, etc.)', NULL, 14, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(40, 'P2.2 Law and Regulation', 'Apply profession-led regulation to serve patients, the profession and society. ', NULL, 14, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(41, 'S1.1 Information Retrieval', 'Are able to retrieve medical information efficiently and effectively', NULL, 15, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(42, 'S1.2 Critical Evaluation', 'Critically evaluate the validity and applicability of medical procedures and therapeutic modalities to patient care', NULL, 15, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(43, 'S2.1 Research Methodology', 'Adopt rigorous research methodology and scientific inquiry procedures', NULL, 16, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(44, 'S2.2 Sharing Innovation', 'Prepares and disseminates new medical information', NULL, 16, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(45, 'S3.1 Learning Strategies', 'Implements effective personal learning experiences including the capacity to engage in reflective learning', NULL, 17, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(46, 'CM1.1 Therapeutic Relationships', 'Demonstrate skills and attitudes to foster rapport, trust and ethical therapeutic relationships with patients and families', NULL, 18, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(47, 'CM1.2 Eliciting Perspectives', 'Elicit and synthesize relevant information and perspectives of patients and families, colleagues and other professionals', NULL, 18, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(48, 'CM1.3 Conveying Information', 'Convey relevant information and explanations appropriately to patients and families, colleagues and other professionals, orally and in writing', NULL, 18, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(49, 'CM1.4 Finding Common Ground', 'Develop a common understanding on issues, problems, and plans with patients and families, colleagues and other professionals to develop a shared plan of care', NULL, 18, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(50, 'CL 1.1 Working In Teams', 'Participate effectively and appropriately as part of a multiprofessional healthcare team.', NULL, 19, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(51, 'CL1.2 Overcoming Conflict', 'Work with others effectively in order to prevent, negotiate, and resolve conflict.', NULL, 19, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(52, 'CL1.3 Including Patients and Families', 'Includes patients and families in prevention and management of illness', NULL, 19, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(53, 'CL1.4 Teaching and Learning', 'Teaches and learns from others consistently  ', NULL, 19, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(54, 'A1.1 Applying Determinants of Health', 'Apply knowledge of the determinants of health for populations to medical encounters and problems.', NULL, 20, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(55, 'A2.1 Community Resources', 'Identify and communicate about community resources to promote health, prevent disease, and manage illness in their patients and the communities they will serve.', NULL, 21, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(56, 'A2.2 Responsibility and Service', 'Integrate the principles of advocacy into their understanding of their professional responsibility to patients and the communities they will serve. ', NULL, 21, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(57, 'M1.1 Career Settings', 'Is aware of the variety of practice options and settings within the practice of Medicine, and makes informed personal choices regarding career direction', NULL, 22, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(58, 'M2.1 Work / Life Balance', 'Identifies and implement strategies that promote care of one''s self and one''s colleagues to maintain balance between personal and educational/ professional commitments', NULL, 23, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(59, 'ME1.1a', 'Apply knowledge of biological systems and their interactions to explain how the human body functions in health and disease. ', NULL, 24, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(60, 'ME1.1b', 'Use the principles of feedback control to explain how specific homeostatic and reproductive systems maintain the internal environment and identify (1) how perturbations in these systems may result in disease and (2) how homeostasis may be changed by disease.', NULL, 24, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(61, 'ME1.1c', 'Apply knowledge of the atomic and molecular characteristics of biological constituents to predict normal and pathological molecular function.', NULL, 24, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(62, 'ME1.1d', 'Explain how the regulation of major biochemical energy production pathways and the synthesis/degradation of macromolecules function to maintain health and identify major forms of dysregulation in disease.', NULL, 24, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(63, 'ME1.1e', 'Explain the major mechanisms of intra- and intercellular communication and their role in health and disease states.', NULL, 24, 4, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(64, 'ME1.1f', 'Apply an understanding of the morphological and biochemical events that occur when somatic or germ cells divide, and the mechanisms that regulate cell division and cell death, to explain normal and abnormal growth and development.', NULL, 24, 5, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(65, 'ME1.1g', 'Identify and describe the common and unique microscopic and three dimensional macroscopic structures of macromolecules, cells, tissues, organs, systems, and compartments that lead to their unique and integrated function from fertilization through senescence to explain how perturbations contribute to disease. ', NULL, 24, 6, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(66, 'ME1.1h', 'Predict the consequences of structural variability and damage or loss of tissues and organs due to maldevelopment, trauma, disease, and aging.', NULL, 24, 7, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(67, 'ME1.1i', 'Apply principles of information processing at the molecular, cellular, and integrated nervous system level and understanding of sensation, perception, decision making, action, and cognition to explain behavior in health and disease.', NULL, 24, 8, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(68, 'ME1.2a', 'Apply the principles of physics and chemistry, such as mass flow, transport, electricity, biomechanics, and signal detection and processing, to the specialized functions of membranes, cells, tissues, organs, and the human organism, and recognize how perturbations contribute to disease.', NULL, 25, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(69, 'ME1.2b', 'Apply the principles of physics and chemistry to explain the risks, limitations, and appropriate use of diagnostic and therapeutic technologies.', NULL, 25, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(70, 'ME1.3a', 'Describe the functional elements in the human genome, their evolutionary origins, their interactions, and the consequences of genetic and epigenetic changes on adaptation and health.', NULL, 26, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(71, 'ME1.3b', 'Explain how variation at the gene level alters the chemical and physical properties of biological systems, and how this, in turn, influences health.', NULL, 26, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(72, 'ME1.3c', 'Describe the major forms and frequencies of genetic variation and their consequences on health in different human populations.', NULL, 26, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(73, 'ME1.3d', 'Apply knowledge of the genetics and the various patterns of genetic transmission within families in order to obtain and interpret family history and ancestry data, calculate risk of disease, and order genetic tests to guide therapeutic decision-making.', NULL, 26, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(74, 'ME1.3e', 'Use to guide clinical action plans, the interaction of genetic and environmental factors to produce phenotypes and provide the basis for individual variation in response to toxic, pharmacological, or other exposures.', NULL, 26, 4, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(75, 'ME1.4a', 'Apply knowledge of the generation of immunological diversity and specificity to the diagnosis and treatment of disease.', NULL, 27, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(76, 'ME1.4b', 'Apply knowledge of the mechanisms for distinction between self and nonself (tolerance and immune surveillance) to the maintenance of health, autoimmunity, and transplant rejection.', NULL, 27, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(77, 'ME1.4c', 'Apply knowledge of the molecular basis for immune cell development to diagnose and treat immune deficiencies.', NULL, 27, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(78, 'ME1.4d', 'Apply knowledge of the mechanisms used to defend against intracellular or extracellular microbes to the development of immunological prevention or treatment.', NULL, 27, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(79, 'ME1.5a', 'Apply knowledge of cellular responses to injury, and the underlying etiology, biochemical and molecular alterations, to assess therapeutic interventions.', NULL, 28, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(80, 'ME1.5b', 'Apply knowledge of the vascular and leukocyte responses of inflammation and their cellular and soluble mediators to the causation, resolution, prevention, and targeted therapy of tissue injury.', NULL, 28, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(81, 'ME1.5c', 'Apply knowledge of the interplay of platelets, vascular endothelium, leukocytes, and coagulation factors in maintaining fluidity of blood, formation of thrombi, and causation of atherosclerosis to the prevention and diagnosis of thrombosis and atherosclerosis in various vascular beds, and the selection of therapeutic responses.', NULL, 28, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(82, 'ME1.5d', 'Apply knowledge of the molecular basis of neoplasia to an understanding of the biological behavior, morphologic appearance, classification, diagnosis, prognosis, and targeted therapy of specific neoplasms.', NULL, 28, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(83, 'ME1.6a', 'Apply the principles of host-pathogen and pathogen-population interactions and knowledge of pathogen structure, genomics, lifecycle, transmission, natural history, and pathogenesis to the prevention, diagnosis, and treatment of infectious disease.', NULL, 29, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(84, 'ME1.6b', 'Apply the principles of symbiosis (commensalisms, mutualism, and parasitism) to the maintenance of health and disease.', NULL, 29, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(85, 'ME1.6c', 'Apply the principles of epidemiology to maintaining and restoring the health of communities and individuals.', NULL, 29, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(86, 'ME1.7a', 'Apply knowledge of pathologic processes, pharmacokinetics, and pharmacodynamics to guide safe and effective treatments.', NULL, 30, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(87, 'ME1.7b', 'Select optimal drug therapy based on an understanding of pertinent research, relevant medical literature, regulatory processes, and pharmacoeconomics.', NULL, 30, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(88, 'ME1.7c', 'Apply knowledge of individual variability in the use and responsiveness to pharmacological agents to selecting and monitoring therapeutic regimens and identifying adverse responses.', NULL, 30, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(89, 'ME1.8a', 'Apply basic mathematical tools and concepts, including functions, graphs and modeling, measurement and scale, and quantitative reasoning, to an understanding of the specialized functions of membranes, cells, tissues, organs, and the human organism, in both health and disease.', NULL, 31, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(90, 'ME1.8b', 'Apply the principles and approaches of statistics, biostatistics, and epidemiology to the evaluation and interpretation of disease risk, etiology, and prognosis, and to the prevention, diagnosis, and management of disease.', NULL, 31, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(91, 'ME1.8c', 'Apply the basic principles of information systems, their design and architecture, implementation, use, and limitations, to information retrieval, clinical problem solving, and public health and policy.', NULL, 31, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(92, 'ME1.8d', 'Explain the importance, use, and limitations of biomedical and health informatics, including data quality, analysis, and visualization, and its application to diagnosis, therapeutics, and characterization of populations and subpopulations. ', NULL, 31, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(93, 'ME1.8e', 'Apply elements of the scientific process, such as inference, critical analysis of research design, and appreciation of the difference between association and causation, to interpret the findings, applications, and limitations of observational and experimental research in clinical decision making.', NULL, 31, 4, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(94, 'ME2.1a', 'Effectively identify and explore issues to be addressed in a patient encounter, including the patient''s context and preferences.', NULL, 32, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(95, 'ME2.1b', 'For purposes of prevention and health promotion, diagnosis and/or management, elicit a history that is relevant, concise and accurate to context and preferences.', NULL, 32, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(96, 'ME2.1c', 'For the purposes of prevention and health promotion, diagnosis and/or management, perform a focused physical examination that is relevant and accurate.', NULL, 32, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(97, 'ME2.1d', 'Select basic, medically appropriate investigative methods in an ethical manner.', NULL, 32, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(98, 'ME2.1e', 'Demonstrate effective clinical problem solving and judgment to address selected common patient presentations, including interpreting available data and integrating information to generate differential diagnoses and management plans.', NULL, 32, 4, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(99, 'ME2.2a', 'Demonstrate effective, appropriate and timely performance of selected diagnostic procedures.', NULL, 33, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(100, 'ME2.2b', 'Demonstrate effective, appropriate and timely performance of selected therapeutic procedures.', NULL, 33, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(101, 'ME3.xa', 'Identify and apply aspects of normal human structure and physiology relevant to the clinical presentation.', NULL, 34, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(102, 'ME3.xb', 'Identify pathologic or maladaptive processes that are active.', NULL, 34, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(103, 'ME3.xc', 'Develop a differential diagnosis for the clinical presentation.', NULL, 34, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(104, 'ME3.xd', 'Use history taking and physical examination relevant to the clinical presentation.', NULL, 34, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(105, 'ME3.xe', 'Use diagnostic tests or procedures appropriately to establish working diagnoses.', NULL, 34, 4, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(106, 'ME3.xf', 'Provide appropriate initial management for the clinical presentation.', NULL, 34, 5, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(107, 'ME3.xg', 'Provide evidence for diagnostic and therapeutic choices.', NULL, 34, 6, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(108, 'ME4.1a', 'Demonstrate awareness and respect for the Determinants of Health in identifying the needs of a patient.', NULL, 35, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(109, 'ME4.1b', 'Discover opportunities for health promotion and disease prevention as well as resources for patient care.', NULL, 35, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(110, 'ME4.1c', 'Formulate preventive measures into their management strategies.', NULL, 35, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(111, 'ME4.1d', 'Communicate with the patient, the patient''s family and concerned others with regard to risk factors and their modification where appropriate.', NULL, 35, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(112, 'ME4.1e', 'Describe programs for the promotion of health including screening for, and the prevention of, illness.', NULL, 35, 4, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(113, 'P1.1a', 'Defines the concepts of honesty, integrity, commitment, dependability, compassion, respect and altruism as applied to medical practice and correctly identifies examples of appropriate and inappropriate application.', NULL, 36, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(114, 'P1.1b', 'Applies these concepts in medical and professional encounters.', NULL, 36, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(115, 'P1.2a', 'Defines the concept of "standard of care".', NULL, 37, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(116, 'P1.2b', 'Applies diagnostic and therapeutic modalities in evidence based and patient centred contexts.', NULL, 37, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(117, 'P1.3a', 'Recognizes and acknowledges limits of personal competence.', NULL, 38, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(118, 'P1.3b', 'Is able to acquire specific knowledge appropriately to assist clinical management.', NULL, 38, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(119, 'P1.3c', 'Engages colleagues and other health professionals appropriately.', NULL, 38, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(120, 'P2.1a', 'Analyze ethical issues encountered in practice (such as informed consent, confidentiality, truth telling, vulnerable populations etc).', NULL, 39, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(121, 'P2.1b', 'Analyze legal issues encountered in practice (such as conflict of interest, patient rights and privacy, etc).', NULL, 39, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(122, 'P2.1c', 'Analyze the psycho-social, cultural and religious issues that could affect patient management.', NULL, 39, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(123, 'P2.1d', 'Define and implement principles of appropriate relationships with patients.', NULL, 39, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(124, 'P2.2a', 'Recognize the professional, legal and ethical codes and obligations required of current practice in a variety of settings, including hospitals, private practice and health care institutions, etc.', NULL, 40, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(125, 'P2.2b', 'Recognize and respond appropriately to unprofessional behaviour in colleagues.', NULL, 40, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(126, 'S1.1a', 'Use objective parameters to assess reliability of various sources of medical information.', NULL, 41, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(127, 'S1.1b', 'Are able to efficiently search sources of medical information in order to address specific clinical questions.', NULL, 41, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(128, 'S1.2a', 'Apply knowledge of research and statistical methodology to the review of medical information and make decisions for health care of patients and society through scientifically rigourous analysis of evidence.', NULL, 42, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(129, 'S1.2b', 'Apply to the review of medical literature the principles of research ethics, including disclosure, conflicts of interest, research on human subjects and industry relations.', NULL, 42, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(130, 'S1.2c', 'Identify the nature and requirements of organizations contributing to medical education.', NULL, 42, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(131, 'S1.2d', 'Balance scientific evidence with consideration of patient preferences and overall quality of life in therapeutic decision making.', NULL, 42, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(132, 'S2.1a', 'Formulates relevant research hypotheses.', NULL, 43, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(133, 'S2.1b', 'Develops rigorous methodologies.', NULL, 43, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(134, 'S2.1c', 'Develops appropriate collaborations in order to participate in research projects.', NULL, 43, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(135, 'S2.1d', 'Practice research ethics, including disclosure, conflicts of interest, research on human subjects and industry relations.', NULL, 43, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(136, 'S2.1e', 'Evaluates the outcomes of research by application of rigorous statistical analysis.', NULL, 43, 4, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(137, 'S2.2a', 'Report to students and faculty upon new knowledge gained from research and enquiry, using a variety of methods.', NULL, 44, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(138, 'S3.1a', 'Develop lifelong learning strategies through integration of the principles of learning.', NULL, 45, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(139, 'S3.1b', 'Self-assess learning critically, in congruence with others'' assessment, and address prioritized learning issues.', NULL, 45, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(140, 'S3.1c', 'Ask effective learning questions and solve problems appropriately.', NULL, 45, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(141, 'S3.1d', 'Consult multiple sources of information.', NULL, 45, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(142, 'S3.1e', 'Employ a variety of learning methodologies.', NULL, 45, 4, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(143, 'S3.1f', 'Learn with and enhance the learning of others through communities of practice.', NULL, 45, 5, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(144, 'S3.1g', 'Employ information technology (informatics) in learning, including, in clerkship, access to patient record data and other technologies.', NULL, 45, 6, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(145, 'CM1.1a', 'Apply the skills that develop positive therapeutic relationships with patients and their families, characterized by understanding, trust, respect, honesty and empathy.', NULL, 46, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(146, 'CM1.1b', 'Respect patient confidentiality, privacy and autonomy.', NULL, 46, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(147, 'CM1.1c', 'Listen effectively and be aware of and responsive to nonverbal cues.', NULL, 46, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(148, 'CM1.1d', 'Communicate effectively with individuals regardless of their social, cultural or ethnic backgrounds, or disabilities.', NULL, 46, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(149, 'CM1.1e', 'Effectively facilitate a structured clinical encounter.', NULL, 46, 4, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(150, 'CM1.2a', 'Gather information about a disease, but also about a patient''s beliefs, concerns, expectations and illness experience.', NULL, 47, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(151, 'CM1.2b', 'Seek out and synthesize relevant information from other sources, such as a patient''s family, caregivers and other professionals.', NULL, 47, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(152, 'CM1.3a', 'Provide accurate information to a patient and family, colleagues and other professionals in a clear, non-judgmental, and understandable manner.', NULL, 48, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(153, 'CM1.3b', 'Maintain clear, accurate and appropriate records of clinical encounters and plans.', NULL, 48, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(154, 'CM1.3c', 'Effectively present verbal reports of clinical encounters and plans.', NULL, 48, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(155, 'CM1.4a', 'Effectively identify and explore problems to be addressed from a patient encounter, including the patient''s context, responses, concerns and preferences.', NULL, 49, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(156, 'CM1.4b', 'Respect diversity and difference, including but not limited to the impact of gender, religion and cultural beliefs on decision making.', NULL, 49, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(157, 'CM1.4c', 'Encourage discussion, questions and interaction in the encounter.', NULL, 49, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(158, 'CM1.4d', 'Engage patients, families and relevant health professionals in shared decision making to develop a plan of care.', NULL, 49, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(159, 'CM1.4e', 'Effectively address challenging communication issues such as obtaining informed consent, delivering bad news, and addressing anger, confusion and misunderstanding.', NULL, 49, 4, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(160, 'CL1.1a', 'Clearly describe and demonstrate their roles and responsibilities under law and other provisions, to other professionals within a variety of health care settings.', NULL, 50, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(161, 'CL1.1b', 'Recognize and respect the diversity of roles and responsibilities of other health care professionals in a variety of settings, noting  how these roles interact with their own.', NULL, 50, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(162, 'CL1.1c', 'Work with others to assess, plan, provide and integrate care for individual patients.', NULL, 50, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(163, 'CL1.1d', 'Respect team ethics, including confidentiality, resource allocation and professionalism.', NULL, 50, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(164, 'CL1.1e', 'Where appropriate, demonstrate leadership in a healthcare team.', NULL, 50, 4, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(165, 'CL1.2a', 'Demonstrate a respectful attitude towards other colleagues and members of an interprofessional team members in a variety of settings.', NULL, 51, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(166, 'CL1.2b', 'Respect differences, and work to overcome misunderstandings and limitations in others, that may contribute to conflict.', NULL, 51, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(167, 'CL1.2c', 'Recognize one''s own differences, and work to overcome one''s own misunderstandings and limitations that may contribute to interprofessional conflict.', NULL, 51, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(168, 'CL1.2d', 'Reflect on successful interprofessional team function.', NULL, 51, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(169, 'CL1.3a', 'Identify the roles of patients and their family in prevention and management of illness.', NULL, 52, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(170, 'CL1.3b', 'Learn how to inform and involve the patient and family in decision-making and management plans.', NULL, 52, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(171, 'CL1.4a', 'Improve teaching through advice from experts in medical education.', NULL, 53, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(172, 'CL1.4b', 'Accept supervision and feedback.', NULL, 53, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(173, 'CL1.4c', 'Seek learning from others.', NULL, 53, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(174, 'A1.1a', 'Explain factors that influence health, disease, disability and access to care including non-biologic factors (cultural, psychological, sociologic, familial, economic, environmental, legal, political, spiritual needs and beliefs).', NULL, 54, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(175, 'A1.1b', 'Describe barriers to access to care and resources.', NULL, 54, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(176, 'A1.1c', 'Discuss health issues for special populations, including vulnerable or marginalized populations.', NULL, 54, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(177, 'A1.1d', 'Identify principles of health policy and implications.', NULL, 54, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(178, 'A1.1e', 'Describe health programs and interventions at the population level.', NULL, 54, 4, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(179, 'A2.1a', 'Identify the role of and method of access to services of community resources.', NULL, 55, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(180, 'A2.1b', 'Describe appropriate methods of communication about community resources to and on behalf of patients.', NULL, 55, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(181, 'A2.1c', 'Locate and analyze a variety of health communities and community health networks in the local Kingston area and beyond.', NULL, 55, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(182, 'A2.2a', 'Describe the role and examples of physicians and medical associations in advocating collectively for health and patient safety.', NULL, 56, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(183, 'A2.2b', 'Analyze the ethical and professional issues inherent in health advocacy, including possible conflict between roles of gatekeeper and manager.', NULL, 56, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(184, 'M1.1a', 'Outline strategies for effective practice in a variety of health care settings, including their structure, finance and operation.', NULL, 57, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(185, 'M1.1b', 'Outline the common law and statutory provisions which govern practice and collaboration within hospital and other settings.', NULL, 57, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(186, 'M1.1c', 'Recognizes one''s own personal preferences and strengths and uses this knowledge in career decisions.', NULL, 57, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(187, 'M1.1d', 'Identify career paths within health care settings.', NULL, 57, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(188, 'M2.1a', 'Identify and balance personal and educational priorities to foster future balance between personal health and a sustainable practice.', NULL, 58, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(189, 'M2.1b', 'Practice personal and professional awareness, insight and acceptance of feedback and peer review;  participate in peer review.', NULL, 58, 1, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(190, 'M2.1c', 'Implement plans to overcome barriers to health personal and professional behavior.', NULL, 58, 2, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(191, 'M2.1d', 'Recognize and respond to other educational/professional colleagues in need of support.', NULL, 58, 3, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(200, 'Clinical Learning Objectives', NULL, NULL, 0, 0, 1, 0, 0);
INSERT INTO `global_lu_objectives` VALUES(201, 'Pain, lower limb', NULL, NULL, 200, 113, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(202, 'Pain, upper limb', NULL, NULL, 200, 112, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(203, 'Fracture/disl''n', NULL, NULL, 200, 111, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(204, 'Scrotal pain', NULL, NULL, 200, 101, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(205, 'Blood in urine', NULL, NULL, 200, 100, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(206, 'Urinary obstruction/hesitancy', NULL, NULL, 200, 99, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(207, 'Nausea/vomiting', NULL, NULL, 200, 98, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(208, 'Hernia', NULL, NULL, 200, 97, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(209, 'Abdominal injuries', NULL, NULL, 200, 96, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(210, 'Chest injuries', NULL, NULL, 200, 95, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(211, 'Breast disorders', NULL, NULL, 200, 94, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(212, 'Anorectal pain', NULL, NULL, 200, 93, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(213, 'Blood, GI tract', NULL, NULL, 200, 92, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(214, 'Abdominal distension', NULL, NULL, 200, 91, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(215, 'Subs abuse/addic/wdraw', NULL, NULL, 200, 90, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(216, 'Abdo pain - acute', NULL, NULL, 200, 89, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(217, 'Psychosis/disord thoughts', NULL, NULL, 200, 88, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(218, 'Personality disorders', NULL, NULL, 200, 87, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(219, 'Panic/anxiety', NULL, NULL, 200, 86, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(221, 'Mood disorders', NULL, NULL, 200, 84, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(222, 'XR-Wrist/hand', NULL, NULL, 200, 83, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(223, 'XR-Chest', NULL, NULL, 200, 82, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(224, 'XR-Hip/pelvis', NULL, NULL, 200, 81, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(225, 'XR-Ankle/foot', NULL, NULL, 200, 80, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(226, 'Skin ulcers-tumors', NULL, NULL, 200, 79, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(228, 'Skin wound', NULL, NULL, 200, 77, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(233, 'Dyspnea, acute', NULL, NULL, 200, 72, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(234, 'Infant/child nutrition', NULL, NULL, 200, 71, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(235, 'Newborn assessment', NULL, NULL, 200, 70, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(236, 'Rash,child', NULL, NULL, 200, 69, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(237, 'Ped naus/vom/diarh', NULL, NULL, 200, 68, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(238, 'Ped EM''s-acutely ill', NULL, NULL, 200, 67, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(239, 'Ped dysp/resp dstres', NULL, NULL, 200, 66, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(240, 'Ped constipation', NULL, NULL, 200, 65, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(241, 'Fever in a child', NULL, NULL, 200, 64, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(242, 'Ear pain', NULL, NULL, 200, 63, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(257, 'Prolapse', NULL, NULL, 200, 48, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(258, 'Vaginal bleeding, abn', NULL, NULL, 200, 47, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(259, 'Postpartum, normal', NULL, NULL, 200, 46, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(260, 'Labour, normal', NULL, NULL, 200, 45, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(261, 'Labour, abnormal', NULL, NULL, 200, 44, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(262, 'Infertility', NULL, NULL, 200, 43, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(263, 'Incontinence-urine', NULL, NULL, 200, 42, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(264, 'Hypertension, preg', NULL, NULL, 200, 41, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(265, 'Dysmenorrhea', NULL, NULL, 200, 40, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(266, 'Contraception', NULL, NULL, 200, 39, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(267, 'Antepartum care', NULL, NULL, 200, 38, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(268, 'Weakness', NULL, NULL, 200, 37, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(269, 'Sodium-abn', NULL, NULL, 200, 36, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(270, 'Renal failure', NULL, NULL, 200, 35, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(271, 'Potassium-abn', NULL, NULL, 200, 34, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(272, 'Murmur', NULL, NULL, 200, 33, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(273, 'Joint pain, poly', NULL, NULL, 200, 32, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(274, 'Impaired LOC (coma)', NULL, NULL, 200, 31, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(275, 'Hypotension', NULL, NULL, 200, 30, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(276, 'Hypertension', NULL, NULL, 200, 29, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(277, 'H+ concentratn, abn', NULL, NULL, 200, 28, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(278, 'Fever', NULL, NULL, 200, 27, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(279, 'Edema', NULL, NULL, 200, 26, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(280, 'Dyspnea-chronic', NULL, NULL, 200, 25, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(281, 'Diabetes mellitus', NULL, NULL, 200, 24, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(282, 'Dementia', NULL, NULL, 200, 23, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(283, 'Delerium/confusion', NULL, NULL, 200, 22, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(284, 'Cough', NULL, NULL, 200, 21, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(286, 'Anemia', NULL, NULL, 200, 19, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(287, 'Chest pain', NULL, NULL, 200, 18, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(288, 'Abdo pain-chronic', NULL, NULL, 200, 17, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(289, 'Wk-rel''td health iss', NULL, NULL, 200, 16, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(290, 'Weight loss/gain', NULL, NULL, 200, 15, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(291, 'URTI', NULL, NULL, 200, 14, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(292, 'Sore throat', NULL, NULL, 200, 13, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(293, 'Skin rash', NULL, NULL, 200, 12, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(294, 'Pregnancy', NULL, NULL, 200, 11, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(295, 'Periodic health exam', NULL, NULL, 200, 10, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(296, 'Pain, spinal', NULL, NULL, 200, 9, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(299, 'Headache', NULL, NULL, 200, 6, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(300, 'Fatigue', NULL, NULL, 200, 5, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(303, 'Dysuria/pyuria', NULL, NULL, 200, 2, 1, 1257353646, 3499);
INSERT INTO `global_lu_objectives` VALUES(304, 'Fracture/dislocation', NULL, NULL, 200, 114, 1, 1261414735, 3499);
INSERT INTO `global_lu_objectives` VALUES(305, 'Pain', NULL, NULL, 200, 115, 1, 1261414735, 3499);
INSERT INTO `global_lu_objectives` VALUES(306, 'Preop Assess - anesthesiology', NULL, NULL, 200, 116, 1, 1261414735, 3499);
INSERT INTO `global_lu_objectives` VALUES(307, 'Preop Assess - surgery', NULL, NULL, 200, 117, 1, 1261414735, 3499);
INSERT INTO `global_lu_objectives` VALUES(308, 'Pain - spinal', NULL, NULL, 200, 118, 1, 1261414735, 3499);
INSERT INTO `global_lu_objectives` VALUES(309, 'MCC Objectives', NULL, NULL, 0, 0, 1, 1265296358, 3499);
INSERT INTO `global_lu_objectives` VALUES(310, 'Abdominal Distension', 'Abdominal distention is common and may indicate the presence of serious intra-abdominal or systemic disease.', '1-E', 309, 1, 1, 1271174177, 3499);
INSERT INTO `global_lu_objectives` VALUES(311, 'Abdominal Mass', 'If hernias are excluded, most other abdominal masses represent a significant underlying disease that requires complete investigation.', '2-E', 309, 2, 1, 1271174177, 3499);
INSERT INTO `global_lu_objectives` VALUES(312, 'Adrenal Mass', 'Adrenal masses are at times found incidentally after CT, MRI, or ultrasound examination done for unrelated reasons.  The incidence is about 3.5 % (almost 10 % of autopsies).', '2-1-E', 311, 1, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(313, 'Hepatomegaly', 'True hepatomegaly (enlargement of the liver with a span greater than 14 cm in adult males and greater than 12 cm in adult females) is an uncommon clinical presentation, but is important to recognize in light of potentially serious causal conditions.', '2-2-E', 311, 2, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(314, 'Hernia (abdominal Wall And Groin)', 'A hernia is defined as an abnormal protrusion of part of a viscus through its containing wall.  Hernias, in particular inguinal hernias, are very common, and thus, herniorrphaphy is a very common surgical intervention.', '2-4-E', 311, 3, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(315, 'Splenomegaly', 'Splenomegaly, an enlarged spleen detected on physical examination by palpitation or percussion at Castell''s point, is relatively uncommon.  However, it is often associated with serious underlying pathology.', '2-3-E', 311, 4, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(316, 'Abdominal Pain (children)', 'Abdominal pain is a common complaint in children.  While the symptoms may result from serious abdominal pathology, in a large proportion of cases, an identifiable organic cause is not found.  The causes are often age dependent.', '3-1-E', 309, 3, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(317, 'Abdominal Pain, Acute ', 'Abdominal pain may result from intra-abdominal inflammation or disorders of the abdominal wall.  Pain may also be referred from sources outside the abdomen such as retroperitoneal processes as well as intra-thoracic processes.  Thorough clinical evaluation is the most important "test" in the diagnosis of abdominal pain.', '3-2-E', 309, 4, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(318, 'Abdominal Pain, Anorectal', 'While almost all causes of anal pain are treatable, some can be destructive locally if left untreated.', '3-4-E', 309, 5, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(319, 'Abdominal Pain, Chronic', 'Chronic and recurrent abdominal pain, including heartburn or dyspepsia is a common symptom (20 - 40 % of adults) with an extensive differential diagnosis and heterogeneous pathophysiology.  The history and physical examination frequently differentiate between functional and more serious underlying diseases.', '3-3-E', 309, 6, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(320, 'Allergic Reactions/food Allergies Intolerance/atopy', 'Allergic reactions are considered together despite the fact that they exhibit a variety of clinical responses and are considered separately under the appropriate presentation.  The rationale for considering them together is that in some patients with a single response (e.g., atopic dermatitis), other atopic disorders such as asthma or allergic rhinitis may occur at other times.  Moreover, 50% of patients with atopic dermatitis report a family history of respiratory atopy. ', '4-E', 309, 7, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(321, 'Attention Deficit/hyperactivity Disorder (adhd)/learning Dis', 'Family physicians at times are the initial caregivers to be confronted by developmental and behavioural problems of childhood and adolescence (5 - 10% of school-aged population).  Lengthy waiting lists for specialists together with the urgent plight of patients often force primary-care physicians to care for these children.', '5-E', 309, 8, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(322, 'Blood From Gastrointestinal Tract', 'Both upper and lower gastrointestinal bleeding are common and may be life-threatening.  Upper intestinal bleeding usually presents with hematemesis (blood or coffee-ground material) and/or melena (black, tarry stools).  Lower intestinal bleeding usually manifests itself as hematochezia (bright red blood or dark red blood or clots per rectum).  Unfortunately, this difference is not consistent. Melena may be seen in patients with colorectal or small bowel bleeding, and hematochezia may be seen with massive upper gastrointestinal bleeding.  Occult bleeding from the gastrointestinal tract may also be identified by positive stool for occult blood or the presence of iron deficiency anemia.', '6-E', 309, 9, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(323, 'Blood From Gastrointestinal Tract, Lower/hematochezia', 'Although lower gastrointestinal bleeding (blood originating distal to ligament of Treitz) or hematochezia is less common than upper (20% vs. 80%), it is associated with 10 -20% morbidity and mortality since it usually occurs in the elderly.  Early identification of colorectal cancer is important in preventing cancer-related morbidity and mortality (colorectal cancer is second only to lung cancer as a cause of cancer-related death). ', '6-2-E', 322, 1, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(324, 'Blood From Gastrointestinal Tract, Upper/hematemesis', 'Although at times self-limited, upper GI bleeding always warrants careful and urgent evaluation, investigation, and treatment.  The urgency of treatment and the nature of resuscitation depend on the amount of blood loss, the likely cause of the bleeding, and the underlying health of the patient.', '6-1-E', 322, 2, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(325, 'Blood In Sputum (hemoptysis/prevention Of Lung Cancer)', 'Expectoration of blood can range from blood streaking of sputum to massive hemoptysis (&gt;200 ml/d) that may be acutely life threatening.  Bleeding usually starts and stops unpredictably, but under certain circumstances may require immediate establishment of an airway and control of the bleeding.', '7-E', 309, 10, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(326, 'Blood In Urine (hematuria)', 'Urinalysis is a screening procedure for insurance and routine examinations.  Persistent hematuria implies the presence of conditions ranging from benign to malignant.', '8-E', 309, 11, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(327, 'Hypertension', 'Hypertension is a common condition that usually presents with a modest elevation in either systolic or diastolic blood pressure.  Under such circumstances, the diagnosis of hypertension is made only after three separate properly measured blood pressures.  Appropriate investigation and management of hypertension is expected to improve health outcomes.', '9-1-E', 309, 12, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(328, 'Hypertension In Childhood', 'The prevalence of hypertension in children is&lt;1 %, but often results from identifiable causes (usually renal or vascular).  Consequently, vigorous investigation is warranted.', '9-1-1-E', 327, 1, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(329, 'Hypertension In The Elderly', 'Elderly patients (&gt;65 years) have hypertension much more commonly than younger patients do, especially systolic hypertension.  The prevalence of hypertension among the elderly may reach 60 -80 %.', '9-1-2-E', 327, 2, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(330, 'Malignant Hypertension', 'Malignant hypertension and hypertensive encephalopathies are two life-threatening syndromes caused by marked elevation in blood pressure.', '9-1-3-E', 327, 3, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(331, 'Pregnancy Associated Hypertension', 'Ten to 20 % of pregnancies are associated with hypertension.  Chronic hypertension complicates&lt;5%, preeclampsia occurs in slightly&gt;6%, and gestational hypertension arises in 6% of pregnant women.  Preeclampsia is potentially serious, but can be managed by treatment of hypertension and ''cured'' by delivery of the fetus.', '9-1-4-E', 327, 4, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(332, 'Hypotension/shock', 'All physicians must deal with life-threatening emergencies.  Regardless of underlying cause, certain general measures are usually indicated (investigations and therapeutic interventions) that can be life saving.', '9-2-E', 309, 13, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(333, 'Anaphylaxis', 'Anaphylaxis causes about 50 fatalities per year, and occurs in 1/5000-hospital admissions in Canada.  Children most commonly are allergic to foods.', '9-2-1-E', 332, 1, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(334, 'Breast Lump/screening', 'Complaints of breast lumps are common, and breast cancer is the most common cancer in women.  Thus, all breast complaints need to be pursued to resolution.  Screening women 50 - 69 years with annual mammography improves survival. ', '10-1-E', 309, 14, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(335, 'Galactorrhea/discharge', 'Although noticeable breast secretions are normal in&gt;50 % of reproductive age women, spontaneous persistent galactorrhea may reflect underlying disease and requires investigation.', '10-2-E', 309, 15, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(336, 'Gynecomastia', 'Although a definite etiology for gynecomastia is found in&lt;50% of patients, a careful drug history is important so that a treatable cause is detected.  The underlying feature is an increased estrogen to androgen ratio.', '10-3-E', 309, 16, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(337, 'Burns', 'Burns are relatively common and range from minor cutaneous wounds to major life-threatening traumas.  An understanding of the patho-physiology and treatment of burns and the metabolic and wound healing response will enable physicians to effectively assess and treat these injuries.', '11-E', 309, 17, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(338, 'Hypercalcemia', 'Hypercalcemia may be associated with an excess of calcium in both extracellular fluid and bone (e.g., increased intestinal absorption), or with a localised or generalised deficit of calcium in bone (e.g., increased bone resorption).  This differentiation by physicians is important for both diagnostic and management reasons.', '12-1-E', 309, 18, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(339, 'Hyperphosphatemia', 'Acute severe hyperphosphatemia can be life threatening.', '12-4-E', 309, 19, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(340, 'Hypocalcemia', 'Tetany, seizures, and papilledema may occur in patients who develop hypocalcemia acutely.', '12-2-E', 309, 20, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(341, 'Hypophosphatemia/fanconi Syndrome', 'Of hospitalised patients, 10-15% develop hypophosphatemia, and a small proportion have sufficiently profound depletion to lead to complications (e.g., rhabdomyolysis).', '12-3-E', 309, 21, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(342, 'Cardiac Arrest', 'All physicians are expected to attempt resuscitation of an individual with cardiac arrest. In the community, cardiac arrest most commonly is caused by ventricular fibrillation. However, heart rhythm at clinical presentation in many cases is unknown.  As a consequence, operational criteria for cardiac arrest do not rely on heart rhythm but focus on the presumed sudden pulse-less condition and the absence of evidence of a non-cardiac condition as the cause of the arrest.', '13-E', 309, 22, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(343, 'Chest Discomfort/pain/angina Pectoris', 'Chest pain in the primary care setting, although potentially severe and disabling, is more commonly of benign etiology.  The correct diagnosis requires a cost-effective approach.  Although coronary heart disease primarily occurs in patients over the age of 40, younger men and women can be affected (it is estimated that advanced lesions are present in 20% of men and 8% of women aged 30 to 34).  Physicians must recognise the manifestations of coronary artery disease and assess coronary risk factors.  Modifications of risk factors should be recommended as necessary.', '14-E', 309, 23, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(344, 'Bleeding Tendency/bruising', 'A bleeding tendency (excessive, delayed, or spontaneous bleeding) may signify serious underlying disease.  In children or infants, suspicion of a bleeding disorder may be a family history of susceptibility to bleeding.  An organised approach to this problem is essential.  Urgent management may be required.', '15-1-E', 309, 24, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(345, 'Hypercoagulable State', 'Patients may present with venous thrombosis and on occasion with pulmonary embolism. A risk factor for thrombosis can now be identified in over 80% of such patients.', '15-2-E', 309, 25, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(346, ' Adult Constipation', 'Constipation is common in Western society, but frequency depends on patient and physician''s definition of the problem.  One definition is straining, incomplete evacuation, sense of blockade, manual maneuvers, and hard stools at least 25% of the time along with&lt;3 stools/week for at least 12 weeks (need not be consecutive).  The prevalence of chronic constipation rises with age. In patients&gt;65 years, almost 1/3 complain of constipation.', '16-1-E', 309, 26, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(347, 'Pediatric Constipation', 'Constipation is a common problem in children.  It is important to differentiate functional from organic causes in order to develop appropriate management plans.', '16-2-E', 309, 27, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(348, 'Contraception', 'Ideally, the prevention of an unwanted pregnancy should be directed at education of patients, male and female, preferably before first sexual contact.  Counselling patients about which method to use, how, and when is a must for anyone involved in health care.', '17-E', 309, 28, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(349, 'Cough', 'Chronic cough is the fifth most common symptom for which patients seek medical advice.  Assessment of chronic cough must be thorough.  Patients with benign causes for their cough (gastro-esophageal reflux, post-nasal drip, two of the commonest causes) can often be effectively and easily managed.  Patients with more serious causes for their cough (e.g., asthma, the other common cause of chronic cough) require full investigation and management is more complex.', '18-E', 309, 29, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(350, 'Cyanosis/hypoxemia/hypoxia', 'Cyanosis is the physical sign indicative of excessive concentration of reduced hemoglobin in the blood, but at times is difficult to detect (it must be sought carefully, under proper lighting conditions).  Hypoxemia (low partial pressure of oxygen in blood), when detected, may be reversible with oxygen therapy after which the underlying cause requires diagnosis and management.', '19-E', 309, 30, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(351, 'Cyanosis/hypoxemia/hypoxia In Children', 'Evaluation of the patient with cyanosis depends on the age of the child.  It is an ominous finding and differentiation between peripheral and central is essential in order to mount appropriate management.', '19-1-E', 350, 1, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(352, 'Deformity/limp/pain In Lower Extremity, Child', '''Limp'' is a bumpy, rough, or strenuous way of walking, usually caused by weakness, pain, or deformity.  Although usually caused by benign conditions, at times it may be life or limb threatening. ', '20-E', 309, 31, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(353, 'Development Disorder/developmental Delay', 'Providing that normal development and behavior is readily recognized, primary care physicians will at times be the first physicians in a position to assess development in an infant, and recognize abnormal delay and/or atypical development.  Developmental surveillance and direct developmental screening of children, especially those with predisposing risks, will then be an integral part of health care.', '21-E', 309, 32, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(354, 'Acute Diarrhea', 'Diarrheal diseases are extremely common worldwide, and even in North America morbidity and mortality is significant.  One of the challenges for a physician faced with a patient with acute diarrhea is to know when to investigate and initiate treatment and when to simply wait for a self-limiting condition to run its course.', '22-1-E', 309, 33, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(355, 'Chronic Diarrhea', 'Chronic diarrhea is a decrease in fecal consistency lasting for 4 or more weeks.  It affects about 5% of the population.', '22-2-E', 309, 34, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(356, 'Pediatric Diarrhea', 'Diarrhea is defined as frequent, watery stools and is a common problem in infants and children.  In most cases, it is mild and self-limited, but the potential for hypovolemia (reduced effective arterial/extracellular volume) and dehydration (water loss in excess of solute) leading to electrolyte abnormalities is great.  These complications in turn may lead to significant morbidity or even mortality.', '22-3-E', 309, 35, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(357, 'Diplopia', 'Diplopia is the major symptom associated with dysfunction of extra-ocular muscles or abnormalities of the motor nerves innervating these muscles.  Monocular diplopia is almost always indicative of relatively benign optical problems whereas binocular diplopia is due to ocular misalignment.  Once restrictive disease or myasthenia gravis is excluded, the major cause of binocular diplopia is a cranial nerve lesion.  Careful clinical assessment will enable diagnosis in most, and suggest appropriate investigation and management.', '23-E', 309, 36, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(358, 'Dizziness/vertigo', '"Dizziness" is a common but imprecise complaint.  Physicians need to determine whether it refers to true vertigo, ''dizziness'', disequilibrium, or pre-syncope/ lightheadedness. ', '24-E', 309, 37, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(359, 'Dying Patient/bereavement', 'Physicians are frequently faced with patients dying from incurable or untreatable diseases. In such circumstances, the important role of the physician is to alleviate any suffering by the patient and to provide comfort and compassion to both patient and family. ', '25-E', 309, 38, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(360, 'Dysphagia/difficulty Swallowing', 'Dysphagia should be regarded as a danger signal that indicates the need to evaluate and define the cause of the swallowing difficulty and thereafter initiate or refer for treatment.', '26-E', 309, 39, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(361, 'Dyspnea', 'Dyspnea is common and distresses millions of patients with pulmonary disease and myocardial dysfunction.  Assessment of the manner dyspnea is described by patients suggests that their description may provide insight into the underlying pathophysiology of the disease.', '27-E', 309, 40, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(362, 'Acute Dyspnea (minutes To Hours)', 'Shortness of breath occurring over minutes to hours is caused by a relatively small number of conditions.  Attention to clinical information and consideration of these conditions can lead to an accurate diagnosis.  Diagnosis permits initiation of therapy that can limit associated morbidity and mortality.', '27-1-E', 361, 1, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(363, 'Chronic Dyspnea (weeks To Months)', 'Since patients with acute dyspnea require more immediate evaluation and treatment, it is important to differentiate them from those with chronic dyspnea.  However, chronic dyspnea etiology may be harder to elucidate.  Usually patients have cardio-pulmonary disease, but symptoms may be out of proportion to the demonstrable impairment.', '27-2-E', 361, 2, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(364, 'Pediatric Dyspnea/respiratory Distress', 'After fever, respiratory distress is one of the commonest pediatric emergency complaints.', '27-3-E', 361, 3, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(365, 'Ear Pain', 'The cause of ear pain is often otologic, but it may be referred.  In febrile young children, who most frequently are affected by ear infections, if unable to describe the pain, a good otologic exam is crucial. (see also <a href="objectives.pl?lang=english&amp;loc=obj&amp;id=40-E" title="Presentation 40-E">Hearing Loss/Deafness)', '28-E', 309, 41, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(366, ' Generalized Edema', 'Patients frequently complain of swelling.  On closer scrutiny, such swelling often represents expansion of the interstitial fluid volume.  At times the swelling may be caused by relatively benign conditions, but at times serious underlying diseases may be present.', '29-1-E', 309, 42, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(367, ' Unilateral/local Edema', 'Over 90 % of cases of acute pulmonary embolism are due to emboli emanating from the proximal veins of the lower extremities.', '29-2-E', 309, 43, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(368, 'Eye Redness', 'Red eye is a very common complaint.  Despite the rather lengthy list of causal conditions, three problems make up the vast majority of causes: conjunctivitis (most common), foreign body, and iritis.  Other types of injury are relatively less common, but important because excessive manipulation may cause further damage or even loss of vision.', '30-E', 309, 44, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(369, 'Failure To Thrive, Elderly ', 'Failure to thrive for an elderly person means the loss of energy, vigor and/or weight often accompanied by a decline in the ability to function and at times associated with depression.', '31-1-E', 309, 45, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(370, 'Failure To Thrive, Infant/child', 'Failure to thrive is a phrase that describes the occurrence of growth failure in either height or weight in childhood.  Since failure to thrive is attributed to children&lt;2 years whose weight is below the 5th percentile for age on more than one occasion, it is essential to differentiate normal from the abnormal growth patterns.', '31-2-E', 309, 46, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(371, 'Falls', 'Falls are common (&gt;1/3 of people over 65 years; 80% among those with?4 risk factors) and 1 in 10 are associated with serious injury such as hip fracture, subdural hematoma, or head injury.  Many are preventable.  Interventions that prevent falls and their sequelae delay or reduce the frequency of nursing home admissions.', '32-E', 309, 47, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(372, 'Fatigue ', 'In a primary care setting, 20-30% of patients will report significant fatigue (usually not associated with organic cause).  Fatigue&lt;1 month is ''recent'';&gt;6 months, it is ''chronic''.', '33-E', 309, 48, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(373, 'Fractures/dislocations ', 'Fractures and dislocations are common problems at any age and are related to high-energy injuries (e.g., motor accidents, sport injuries) or, at the other end of the spectrum, simple injuries such as falls or non-accidental injuries.  They require initial management by primary care physicians with referral for difficult cases to specialists.', '34-E', 309, 49, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(374, 'Gait Disturbances/ataxia ', 'Abnormalities of gait can result from disorders affecting several levels of the nervous system and the type of abnormality observed clinically often indicates the site affected.', '35-E', 309, 50, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(375, 'Genetic Concerns', 'Genetics have increased our understanding of the origin of many diseases.  Parents with a family history of birth defects or a previously affected child need to know that they are at higher risk of having a baby with an anomaly.  Not infrequently, patients considering becoming parents seek medical advice because of concerns they might have.  Primary care physicians must provide counseling about risk factors such as maternal age, illness, drug use, exposure to infectious or environmental agents, etc. and if necessary referral if further evaluation is necessary.', '36-E', 309, 51, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(376, 'Ambiguous Genitalia', 'Genetic males with 46, XY genotype but having impaired androgen sensitivity of varying severity may present with features that range from phenotypic females to ''normal'' males with only minor defects in masculinization or infertility.  Primary care physicians may be called upon to determine the nature of the problem.', '36-1-E', 375, 1, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(377, 'Dysmorphic Features', 'Three out of 100 infants are born with a genetic disorder or congenital defect.  Many of these are associated with long-term disability, making early detection and identification vital.  Although early involvement of genetic specialists in the care of such children is prudent, primary care physicians are at times required to contribute immediate care, and subsequently assist with long term management of suctients.', '36-2-E', 375, 2, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(378, 'Hyperglycemia/diabetes Mellitus', 'Diabetes mellitus is a very common disorder associated with a relative or absolute impairment of insulin secretion together with varying degrees of peripheral resistance to the action of insulin.  The morbidity and mortality associated with diabetic complications may be reduced by preventive measures.  Intensive glycemic control will reduce neonatal complications and reduce congenital malformations in pregnancy diabetes.', '37-1-E', 309, 52, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(379, 'Hypoglycemia', 'Maintenance of the blood sugar within normal limits is essential for health.  In the short-term, hypoglycemia is much more dangerous than hyperglycemia.  Fortunately, it is an uncommon clinical problem outside of therapy for diabetes mellitus. ', '37-2-E', 309, 53, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(380, 'Alopecia ', 'Although in themselves hair changes may be innocuous, they can be psychologically unbearable.  Frequently they may provide significant diagnostic hints of underlying disease.', '38-1-E', 309, 54, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(381, 'Nail Complaints ', 'Nail disorders (toenails more than fingernails), especially ingrown, infected, and painful nails, are common conditions.  Local nail problems may be acute or chronic.  Relatively simple treatment can prevent or alleviate symptoms.  Although in themselves nail changes may be innocuous, they frequently provide significant diagnostic hints of underlying disease.', '38-2-E', 309, 55, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(382, 'Headache', 'The differentiation of patients with headaches due to serious or life-threatening conditions from those with benign primary headache disorders (e.g., tension headaches or migraine) is an important diagnostic challenge.', '39-E', 309, 56, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(383, 'Hearing Loss/deafness ', 'Many hearing loss causes are short-lived, treatable, and/or preventable.  In the elderly, more permanent sensorineural loss occurs.  In pediatrics, otitis media accounts for 25% of office visits.  Adults/older children have otitis less commonly, but may be affected by sequelae of otitis.', '40-E', 309, 57, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(384, 'Hemiplegia/hemisensory Loss +/- Aphasia', 'Hemiplegia/hemisensory loss results from an upper motor neuron lesion above the mid-cervical spinal cord.  The concomitant finding of aphasia is diagnostic of a dominant cerebral hemisphere lesion.  Acute hemiplegia generally heralds the onset of serious medical conditions, usually of vascular origin, that at times are effectively treated by advanced medical and surgical techniques.</p>\r\n<p>If the sudden onset of focal neurologic symptoms and/or signs lasts&lt;24 hours, presumably it was caused by a transient decrease in blood supply rendering the brain ischemic but with blood flow restoration timely enough to avoid infarction.  This definition of transient ischemic attacks (TIA) is now recognized to be inadequate.  ', '41-E', 309, 58, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(385, 'Anemia', 'The diagnosis in a patient with anemia can be complex.  An unfocused or unstructured investigation of anemia can be costly and inefficient.  Simple tests may provide important information.  Anemia may be the sole manifestation of serious medical disease.', '42-1-E', 309, 59, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(386, 'Polycythemia/elevated Hemoglobin', 'The reason for evaluating patients with elevated hemoglobin levels (male 185 g/L, female 165 g/L) is to ascertain the presence or absence of polycythemia vera first, and subsequently to differentiate between the various causes of secondary erythrocytosis.', '42-2-E', 309, 60, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(387, 'Hirsutism/virilization', 'Hirsutism, terminal body hair where unusual (face, chest, abdomen, back), is a common problem, particularly in dark-haired, darkly pigmented, white women.  However, if accompanied by virilization, then a full diagnostic evaluation is essential because it is androgen-dependent.  Hypertrichosis on the other hand is a rare condition usually caused by drugs or systemic illness.', '43-E', 309, 61, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(388, 'Hoarseness/dysphonia/speech And Language Abnormalities', 'Patients with impairment in comprehension and/or use of the form, content, or function of language are said to have a language disorder.  Those who have correct word choice and syntax but have speech disorders may have an articulation disorder.  Almost any change in voice quality may be described as hoarseness.  However, if it lasts more than 2 weeks, especially in patients who use alcohol or tobacco, it needs to be evaluated.', '44-E', 309, 62, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(389, 'Hydrogen Ion Concentration Abnormal, Serum', 'Major adverse consequences may occur with severe acidemia and alkalemia despite absence of specific symptoms.  The diagnosis depends on the clinical setting and laboratory studies.  It is crucial to distinguish acidemia due to metabolic causes from that due to respiratory causes; especially important is detecting the presence of both.  Management of the underlying causes and not simply of the change in [H+] is essential.', '45-E', 309, 63, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(390, 'Infertility', 'Infertility, meaning the inability to conceive after one year of intercourse without contraception, affects about 15% of couples.  Both partners must be investigated; male-associated factors account for approximately half of infertility problems.  Although current emphasis is on treatment technologies, it is important to consider first the cause of the infertility and tailor the treatment accordingly.', '46-E', 309, 64, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(391, 'Incontinence, Stool', 'Fecal incontinence varies from inadvertent soiling with liquid stool to the involuntary excretion of feces.  It is a demoralizing disability because it affects self-assurance and can lead to social isolation.  It is the second leading cause of nursing home placement.', '47-1-E', 309, 65, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(392, 'Incontinence, Urine', 'Because there is increasing incidence of involuntary micturition with age, incontinence has increased in frequency in our ageing population.  Unfortunately, incontinence remains under treated despite its effect on quality of life and impact on physical and psychological morbidity.  Primary care physicians should diagnose the cause of incontinence in the majority of cases.', '47-2-E', 309, 66, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(393, 'Incontinence, Urine, Pediatric (enuresis)', 'Enuresis is the involuntary passage of urine, and may be diurnal (daytime), nocturnal (nighttime), or both.  The majority of children have primary nocturnal enuresis (20% of five-year-olds).  Diurnal and secondary enuresis is much less common, but requires differentiating between underlying diseases and stress related conditions.', '47-3-E', 309, 67, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(394, 'Impotence/erectile Dysfunction', 'Impotence is an issue that has a major impact on relationships.  There is a need to explore the impact with both partners, although many consider it a male problem.  Impotence is present when an erection of sufficient rigidity for sexual intercourse cannot be acquired or sustained&gt;75% of the time.', '48-E', 309, 68, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(395, 'Jaundice ', 'Jaundice may represent hemolysis or hepatobiliary disease.  Although usually the evaluation of a patient is not urgent, in a few situations it is a medical emergency (e.g., massive hemolysis, ascending cholangitis, acute hepatic failure).', '49-E', 309, 69, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(396, 'Neonatal Jaundice ', 'Jaundice, usually mild unconjugated bilirubinemia, affects nearly all newborns.  Up to 65% of full-term neonates have jaundice at 72 - 96 hours of age.  Although some causes are ominous, the majority are transient and without consequences.', '49-1-E', 395, 1, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(397, 'Joint Pain, Mono-articular (acute, Chronic)', 'Any arthritis can initially present as one swollen painful joint.  Thus, the early exclusion of polyarticular joint disease may be challenging.  In addition, pain caused by a problem within the joint needs to be distinguished from pain arising from surrounding soft tissues.', '50-1-E', 309, 70, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(398, 'Joint Pain, Poly-articular (acute, Chronic)', 'Polyarticular joint pain is common in medical practice, and causes vary from some that are self-limiting to others which are potentially disabling and life threatening.', '50-2-E', 309, 71, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(399, 'Periarticular Pain/soft Tissue Rheumatic Disorders', 'Pain caused by a problem within the joint needs to be distinguished from pain arising from surrounding soft tissues.', '50-3-E', 309, 72, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(400, 'Lipids Abnormal, Serum ', 'Hypercholesterolemia is a common and important modifiable risk factor for ischemic heart disease (IHD) and cerebro-vascular disease.  The relationship of elevated triglycerides to IHD is less clear (may be a modest independent predictor) but very high levels predispose to pancreatitis.  HDL cholesterol is inversely related to IHD risk.', '51-E', 309, 73, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(401, 'Liver Function Tests Abnormal, Serum', 'Appropriate investigation can distinguish benign reversible liver disease requiring no treatment from potentially life-threatening conditions requiring immediate therapy.', '52-E', 309, 74, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(402, 'Lump/mass, Musculoskeletal ', 'Lumps or masses are a common cause for consultation with a physician.  The majority will be of a benign dermatologic origin. Musculoskeletal lumps or masses are not common, but they represent an important cause of morbidity and mortality, especially among young people.', '53-E', 309, 75, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(403, 'Lymphadenopathy', 'Countless potential causes may lead to lymphadenopathy.  Some of these are serious but treatable.  In a study of patients with lymphadenopathy, 84% were diagnosed with benign lymphadenopathy and the majority of these were due to a nonspecific (reactive) etiology.', '54-E', 309, 76, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(404, 'Mediastinal Mass/hilar Adenopathy', 'The mediastinum contains many vital structures (heart, aorta, pulmonary hila, esophagus) that are affected directly or indirectly by mediastinal masses.  Evaluation of such masses is aided by envisaging the nature of the mass from its location in the mediastinum.</p>\r\n<p>', '54-1-E', 403, 1, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(405, 'Magnesium Concentration Serum, Abnormal/hypomagnesemia ', 'Although hypomagnesemia occurs in only about 10% of hospitalized patients, the incidence rises to over 60% in severely ill patients.  It is frequently associated with hypokalemia and hypocalcemia.', '55-E', 309, 77, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(406, 'Amenorrhea/oligomenorrhea', 'The average age of onset of menarche in North America is 11 to 13 years and menopause is approximately 50 years.  Between these ages, absence of menstruation is a cause for investigation and appropriate management.', '56-1-E', 309, 78, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(407, 'Dysmenorrhea', 'Approximately 30 - 50% of post-pubescent women experience painful menstruation and 10% of women are incapacitated by pain 1 - 3 days per month.  It is the single greatest cause of lost working hours and school days among young women.', '56-2-E', 309, 79, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(408, 'Pre-menstrual Syndrome (pms)', 'Pre-menstrual syndrome is a combination of physical, emotional, or behavioral symptoms that occur prior to the menstrual cycle and are absent during the rest of the cycle.  The symproms, on occasion, are severe enough to intefere significantly with work and/or home activities.', '56-3-E', 309, 80, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(409, 'Menopause ', 'Women cease to have menstrual periods at about 50 years of age, although ovarian function declines earlier.  Changing population demographics means that the number of women who are menopausal will continue to grow, and many women will live 1/3 of their lives after ovarian function ceases.  Promotion of health maintenance in this group of women will enhance physical, emotional, and sexual quality of life.', '57-E', 309, 81, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(410, 'Coma', 'Patients with altered level of consciousness account for 5% of hospital admissions.  Coma however is defined as a state of pathologic unconsciousness (unarousable).', '58-1-E', 309, 82, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(411, 'Delirium/confusion ', 'An acute confusional state in patients with medical illness, especially among those who are older, is extremely common.  Between 10 - 15% of elderly patients admitted to hospital have delirium and up to a further 30% develop delirium while in hospital.  It represents a disturbance of consciousness with reduced ability to focus, sustain, or shift attention (DSM-IV).  This disturbance tends to develop over a short period of time (hours to days) and tends to fluctuate during the course of the day.  A clear understanding of the differential diagnosis enables rapid and appropriate management.', '58-2-E', 309, 83, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(412, 'Dementia', 'Dementia is a problem physicians encounter frequently, and causes that are potentially treatable require identification.  Alzheimer disease is the most common form of dementia in the elderly (about 70%), and primary care physicians will need to diagnose and manage the early cognitive manifestations.', '58-3-E', 309, 84, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(413, 'Mood Disorders ', 'Depression is one of the top five diagnoses made in the offices of primary care physicians.  Depressed mood occurs in some individuals as a normal reaction to grief, but in others it is considered abnormal because it interferes with the person''s daily function (e.g., self-care, relationships, work, self-support).  Thus, it is necessary for primary care clinicians to detect depression, initiate treatment, and refer to specialists for assistance when required.', '59-E', 309, 85, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(414, 'Mouth Problems', 'Although many disease states can affect the mouth, the two most common ones are odontogenic infections (dental carries and periodontal infections) and oral carcinoma. Almost 15% of the population have significant periodontal disease despite its being preventable.  Such infections, apart from the discomfort inflicted, may result in serious complications.', '60-E', 309, 86, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(415, 'Movement Disorders,involuntary/tic Disorders', 'Movement disorders are regarded as either excessive (hyperkinetic) or reduced (bradykinetic) activity.  Diagnosis depends primarily on careful observation of the clinical features. ', '61-E', 309, 87, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(416, 'Diastolic Murmur', 'Although systolic murmurs are often "innocent" or physiological, diastolic murmurs are virtually always pathologic.', '62-1-E', 309, 88, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(417, 'Heart Sounds, Pathological', 'Pathological heart sounds are clues to underlying heart disease.', '62-2-E', 309, 89, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(418, 'Systolic Murmur', 'Ejection systolic murmurs are common, and frequently quite ''innocent'' (with absence of cardiac findings and normal splitting of the second sound).', '62-3-E', 309, 90, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(419, 'Neck Mass/goiter/thyroid Disease ', 'The vast majority of neck lumps are benign (usually reactive lymph nodes or occasionally of congenital origin).  The lumps that should be of most concern to primary care physicians are the rare malignant neck lumps.  Among patients with thyroid nodules, children, patients with a family history or history for head and neck radiation, and adults&lt;30 years or&gt;60 years are at higher risk for thyroid cancer.', '63-E', 309, 91, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(420, 'Newborn, Depressed', 'A call requesting assistance in the delivery of a newborn may be "routine" or because the neonate is depressed and requires resuscitation.  For any type of call, the physician needs to be prepared to manage potential problems.', '64-E', 309, 92, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(421, 'Non-reassuring Fetal Status (fetal Distress)', 'Non-reassuring fetal status occurs in 5 - 10% of pregnancies.  (Fetal distress, a term also used, is imprecise and has a low positive predictive value.  The newer term should be used.)  Early detection and pro-active management can reduce serious consequences and prepare parents for eventualities.', '65-E', 309, 93, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(422, 'Numbness/tingling/altered Sensation', 'Disordered sensation may be alarming and highly intrusive.  The physician requires a framework of knowledge in order to assess abnormal sensation, consider the likely site of origin, and recognise the implications.', '66-E', 309, 94, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(423, 'Pain', 'Because pain is considered a signal of disease, it is the most common symptom that brings a patient to a physician.  Acute pain is a vital protective mechanism.  In contrast, chronic pain (&gt;6 weeks or lasting beyond the ordinary duration of time that an injury needs to heal) serves no physiologic role and is itself a disease state.  Pain is an unpleasant somatic sensation, but it is also an emotion.  Although control of pain/discomfort is a crucial endpoint of medical care, the degree of analgesia provided is often inadequate, and may lead to complications (e.g., depression, suicide).  Physicians should recognise the development and progression of pain, and develop strategies for its control.', '67-E', 309, 95, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(424, ' Generalized Pain Disorders', 'Fibromyalgia, a common cause of chronic musculoskeletal pain and fatigue, has no known etiology and is not associated with tissue inflammation.  It affects muscles, tendons, and ligaments.  Along with a group of similar conditions, fibromyalgia is controversial because obvious sign and laboratory/radiological abnormalities are lacking.</p>\r\n<p>Polymyalgia rheumatica, a rheumatic condition frequently linked to giant cell (temporal) arteritis, is a relatively common disorder (prevalence of about 700/100,000 persons over 50 years of age).  Synovitis is considered to be the cause of the discomfort.', '67-1-2-1-E', 423, 1, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(425, 'Local Pain, Hip/knee/ankle/foot', 'With the current interest in physical activity, the commonest cause of leg pain is muscular or ligamentous strain.  The knee, the most intricate joint in the body, has the greatest susceptibility to pain.', '67-1-2-3-E', 423, 2, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(426, 'Local Pain, Shoulder/elbow/wrist/hand', 'After backache, upper extremity pain is the most common type of musculoskeletal pain.', '67-1-2-2-E', 423, 3, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(427, 'Local Pain, Spinal Compression/osteoporosis', 'Spinal compression is one manifestation of osteoporosis, the prevalence of which increases with age.  As the proportion of our population in old age rises, osteoporosis becomes an important cause of painful fractures, deformity, loss of mobility and independence, and even death.  Although less common in men, the incidence of fractures increases exponentially with ageing, albeit 5 - 10 years later.  For unknown reasons, the mortality associated with fractures is higher in men than in women.', '67-1-2-4-E', 423, 4, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(428, 'Local Pain, Spine/low Back Pain', 'Low back pain is one of the most common physical complaints and a major cause of lost work time.  Most frequently it is associated with vocations that involve lifting, twisting, bending, and reaching.  In individuals suffering from chronic back pain, 5% will have an underlying serious disease.', '67-1-2-6-E', 423, 5, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(429, 'Local Pain, Spine/neck/thoracic', 'Approximately 10 % of the adult population have neck pain at any one time.  This prevalence is similar to low back pain, but few patients lose time from work and the development of neurologic deficits is&lt;1 %.', '67-1-2-5-E', 423, 6, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(430, 'Central/peripheral Neuropathic Pain', 'Neuropathic pain is caused by dysfunction of the nervous system without tissue damage.  The pain tends to be chronic and causes great discomfort.', '67-2-2-E', 423, 7, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(431, 'Sympathetic/complex Regional Pain Syndrome/reflex Sympatheti', 'Following an injury or vascular event (myocardial infarction, stroke), a disorder may develop that is characterized by regional pain and sensory changes (vasomotor instability, skin changes, and patchy bone demineralization).', '67-2-1-E', 423, 8, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(432, 'Palpitations (abnormal Ecg-arrhythmia)', 'Palpitations are a common symptom.  Although the cause is often benign, occasionally it may indicate the presence of a serious underlying problem.', '68-E', 309, 96, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(433, 'Panic And Anxiety ', 'Panic attacks/panic disorders are common problems in the primary care setting.  Although such patients may present with discrete episodes of intense fear, more commonly they complain of one or more physical symptoms.  A minority of such patients present to mental health settings, whereas 1/3 present to their family physician and another 1/3 to emergency departments.  Generalized anxiety disorder, characterized by excessive worry and anxiety that are difficult to control, tends to develop secondary to other psychiatric conditions.', '69-E', 309, 97, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(434, 'Pap Smear Screening', 'Carcinoma of the cervix is a preventable disease.  Any female patient who visits a physician''s office should have current screening guidelines applied and if appropriate, a Pap smear should be recommended.', '70-E', 309, 98, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(435, 'Pediatric Emergencies  - Acutely Ill Infant/child', 'Although pediatric emergencies such as the ones listed below are discussed with the appropriate condition, the care of the patient in the pediatric age group demands special skills', '71-E', 309, 99, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(436, 'Crying/fussing Child', 'A young infant whose only symptom is crying/fussing challenges the primary care physician to distinguish between benign and organic causes.', '71-1-E', 435, 1, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(437, 'Hypotonia/floppy Infant/child', 'Infants/children with decreased resistance to passive movement differ from those with weakness and hyporeflexia.  They require detailed, careful neurologic evaluation. Management programs, often life-long, are multidisciplinary and involve patients, family, and community.', '71-2-E', 435, 2, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(438, 'Pelvic Mass', 'Pelvic masses are common and may be found in a woman of any age, although the possible etiologies differ among age groups.  There is a need to diagnose and investigate them since early detection may affect outcome.', '72-E', 309, 100, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(439, 'Pelvic Pain', 'Acute pelvic pain is potentially life threatening.  Chronic pelvic pain is one of the most common problems in gynecology.  Women average 2 - 3 visits each year to physicians'' offices with chronic pelvic pain.  At present, only about one third of these women are given a specific diagnosis.  The absence of a clear diagnosis can frustrate both patients and clinicians.  Once the diagnosis is established, specific and usually successful treatment may be instituted.', '73-E', 309, 101, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(440, 'Periodic Health Examination (phe) ', 'Periodically, patients visit physicians'' office not because they are unwell, but because they want a ''check-up''.  Such visits are referred to as health maintenance or the PHE. The PHE is an opportunity to relate to an asymptomatic patient for the purpose of case finding and screening for undetected disease and risky behaviour.  It is also an opportunity for health promotion and disease prevention.  The decision to include or exclude a medical condition in the PHE should be based on the burden of suffering caused by the condition, the quality of the screening, and effectiveness of the intervention.', '74-E', 309, 102, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(441, 'Infant And Child Immunization ', 'Immunization has reduced or eradicated many infectious diseases and has improved overall world health.  Recommended immunization schedules are constantly updated as new vaccines become available.', '74-2-E', 440, 1, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(442, 'Newborn Assessment/nutrition ', 'Primary care physicians play a vital role in identifying children at risk for developmental and other disorders that are threatening to life or long-term health before they become symptomatic.  In most cases, parents require direction and reassurance regarding the health status of their newborn infant.  With respect to development, parental concerns regarding the child''s language development, articulation, fine motor skills, and global development require careful assessment.', '74-1-E', 440, 2, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(443, 'Pre-operative Medical Evaluation', 'Evaluation of patients prior to surgery is an important element of comprehensive medical care.  The objectives of such an evaluation include the detection of unrecognized disease that may increase the risk of surgery and how to minimize such risk.', '74-3-E', 440, 3, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(444, 'Work-related Health Issues ', 'Physicians will encounter health hazards in their own work place, as well as in patients'' work place.  These hazards need to be recognised and addressed.  A patient''s reported environmental exposures may prompt interventions important in preventing future illnesses/injuries.  Equally important, physicians can not only play an important role in preventing occupational illness but also in promoting environmental health.', '74-4-E', 440, 4, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(445, 'Personality Disorders ', 'Personality disorders are persistent and maladaptive patterns of behaviour exhibited over a wide variety of social, occupational, and relationship contexts and leading to distress and impairment.  They represent important risk factors for a variety of medical, interpersonal, and psychiatric difficulties.  For example, patients with personality difficulties may attempt suicide, or may be substance abusers.  As a group, they may alienate health care providers with angry outbursts, high-risk behaviours, signing out against medical advice, etc.', '75-E', 309, 103, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(446, 'Pleural Effusion/pleural Abnormalities', NULL, '76-E', 309, 104, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(447, 'Poisoning', 'Exposures to poisons or drug overdoses account for 5 - 10% of emergency department visits, and&gt;5 % of admissions to intensive care units.  More than 50 % of these patients are children less than 6 years of age.', '77-E', 309, 105, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(448, 'Administration Of Effective Health Programs At The Populatio', 'Knowing the organization of the health care and public health systems in Canada as well as how to determine the most cost-effective interventions are becoming key elements of clinical practice. Physicians also must work well in multidisciplinary teams within the current system in order to achieve the maximum health benefit for all patients and residents. ', '78-4-E', 309, 106, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(449, 'Assessing And Measuring Health Status At The Population Leve', 'Knowing the health status of the population allows for better planning and evaluation of health programs and tailoring interventions to meet patient/community needs. Physicians are also active participants in disease surveillance programs, encouraging them to address health needs in the population and not merely health demands.', '78-2-E', 309, 107, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(450, 'Concepts Of Health And Its Determinants', 'Concepts of health, illness, disease and the socially defined sick role are fundamental to understanding the health of a community and to applying that knowledge to the patients that a physician serves. With advances in care, the aspirations of patients for good health have expanded and this has placed new demands on physicians to address issues that are not strictly biomedical in nature. These concepts are also important if the physician is to understand health and illness behaviour. ', '78-1-E', 309, 108, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(451, 'Environment', 'Environmental issues are important in medical practice because exposures may be causally linked to a patient''s clinical presentation and the health of the exposed population. A physician is expected to work with regulatory agencies to help implement the necessary interventions to prevent future illness.  Physician involvement is important in the promotion of global environmental health.', '78-6-E', 309, 109, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(452, 'Health Of Special Populations', 'Health equity is defined as each person in society having an equal opportunity for health. Each community is composed of diverse groups of individuals and sub-populations. Due to variations in factors such as physical location, culture, behaviours, age and gender structure, populations have different health risks and needs that must be addressed in order to achieve health equity.  Hence physicians need to be aware of the differing needs of population groups and must be able to adjust service provision to ensure culturally safe communications and care.', '78-7-E', 309, 110, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(453, 'Interventions At The Population Level', 'Many interventions at the individual level must be supported by actions at the community level. Physicians will be expected to advocate for community wide interventions and to address issues that occur to many patients across their practice. ', '78-3-E', 309, 111, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(454, 'Outbreak Management', 'Physicians are crucial participants in the control of outbreaks of disease. They must be able to diagnose cases, recognize outbreaks, report these to public health authorities and work with authorities to limit the spread of the outbreak. A common example includes physicians working in nursing homes and being asked to assist in the control of an outbreak of influenza or diarrhea.', '78-5-E', 309, 112, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(455, 'Hyperkalemia ', 'Hyperkalemia may have serious consequences (especially cardiac) and may also be indicative of the presence of serious associated medical conditions.', '79-1-E', 309, 113, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(456, 'Hypokalemia ', 'Hypokalemia, a common clinical problem, is most often discovered on routine analysis of serum electrolytes or ECG results.  Symptoms usually develop much later when depletion is quite severe.', '79-2-E', 309, 114, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(457, 'Antepartum Care ', 'The purpose of antepartum care is to help achieve as good a maternal and infant outcome as possible.  This means that psychosocial issues as well as biological issues need to be addressed.', '80-1-E', 309, 115, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(458, 'Intrapartum Care/postpartum Care ', 'Intrapartum and postpartum care means the care of the mother and fetus during labor and the six-week period following birth during which the reproductive tract returns to its normal nonpregnant state.  Of pregnant women, 85% will undergo spontaneous labor between 37 and 42 weeks of gestation.  Labor is the process by which products of conception are delivered from the uterus by progressive cervical effacement and dilatation in the presence of regular uterine contractions.', '80-2-E', 309, 116, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(459, 'Obstetrical Complications ', 'Virtually any maternal medical or surgical condition can complicate the course of a pregnancy and/or be affected by the pregnancy.  In addition, conditions arising in pregnancy can have adverse effects on the mother and/or the fetus.  For example, babies born prematurely account for&gt;50% of perinatal morbidity and mortality; an estimated 5% of women will describe bleeding of some extent during pregnancy, and in some patients the bleeding will endanger the mother.', '80-3-E', 309, 117, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(460, 'Pregnancy Loss', 'A miscarriage or abortion is a pregnancy that ends before the fetus can live outside the uterus.  The term also means the actual passage of the uterine contents.  It is very common in early pregnancy; up to 20% of pregnant women have a miscarriage before 20 weeks of pregnancy, 80% of these in the first 12 weeks.', '81-E', 309, 118, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(461, 'Prematurity', 'The impact of premature birth is best summarized by the fact that&lt;10% of babies born prematurely in North America account for&gt;50% of all perinatal morbidity and mortality.  Yet outcomes, although guarded, can be rewarding given optimal circumstances.', '82-E', 309, 119, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(462, 'Prolapse/pelvic Relaxation', 'Patients with pelvic relaxation present with a forward and downward drop of the pelvic organs (bladder, rectum).  In order to identify patients who would benefit from therapy, the physician should be familiar with the manifestations of pelvic relaxation (uterine prolapse, vaginal vault prolapse, cystocele, rectocele, and enterocele) and have an approach to management.', '83-E', 309, 120, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(463, 'Proteinuria ', 'Urinalysis is a screening procedure used frequently for insurance and routine examinations.  Proteinuria is usually identified by positive dipstick on routine urinalysis. Persistent proteinuria often implies abnormal glomerular function.', '84-E', 309, 121, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(464, 'Pruritus ', 'Itching is the most common symptom in dermatology.  In the absence of primary skin lesions, generalised pruritus can be indicative of an underlying systemic disorder.  Most patients with pruritus do not have a systemic disorder and the itching is due to a cutaneous disorder.', '85-E', 309, 122, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(465, 'Psychotic Patient/disordered Thought', 'Psychosis is a general term for a major mental disorder characterized by derangement of personality and loss of contact with reality, often with false beliefs (delusions), disturbances in sensory perception (hallucinations), or thought disorders (illusions). Schizophrenia is both the most common (1% of world population) and the classic psychotic disorder.  There are other psychotic syndromes that do not meet the diagnostic criteria for schizophrenia, some of them caused by general medical conditions or induced by a substance (alcohol, hallucinogens, steroids).  In the evaluation of any psychotic patient in a primary care setting all of these possibilities need to be considered.', '86-E', 309, 123, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(466, 'Pulse Abnormalities/diminished/absent/bruits', 'Arterial pulse characteristics should be assessed as an integral part of the physical examination.  Carotid, radial, femoral, posterior tibial, and dorsalis pedis pulses should be examined routinely on both sides, and differences, if any, in amplitude, contour, and upstroke should be ascertained.', '87-E', 309, 124, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(467, 'Pupil Abnormalities ', 'Pupillary disorders of changing degree are in general of little clinical importance.  If only one pupil is fixed to light, it is suspicious of the effect of mydriatics.  However, pupillary disorders with neurological symptoms may be of significance.', '88-E', 309, 125, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(468, 'Acute Renal Failure (anuria/oliguria/arf)', 'A sudden and rapid rise in serum creatinine is a common finding.  A competent physician is required to have an organised approach to this problem.', '89-1-E', 309, 126, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(469, 'Chronic Renal Failure ', 'Although specialists in nephrology will care for patients with chronic renal failure, family physicians will need to identify patients at risk for chronic renal disease, will participate in treatment to slow the progression of chronic renal disease, and will care for other common medical problems that afflict these patients.  Physicians must realise that patients with chronic renal failure have unique risks and that common therapies may be harmful because kidneys are frequently the main routes for excretion of many drugs.', '89-2-E', 309, 127, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(470, 'Scrotal Mass ', 'In children and adolescents, scrotal masses vary from incidental, requiring only reassurance, to acute pathologic events.  In adults, tumors of the testis are relatively uncommon (only 1 - 2 % of malignant tumors in men), but are considered of particular importance because they affect predominantly young men (25 - 34 years).  In addition, recent advances in management have resulted in dramatic improvement in survival rate.', '90-E', 309, 128, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(471, 'Scrotal Pain ', 'In most scrotal disorders, there is swelling of the testis or its adnexae.  However, some conditions are not only associated with pain, but pain may precede the development of an obvious mass in the scrotum.', '91-E', 309, 129, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(472, 'Seizures (epilepsy)', 'Seizures are an important differential diagnosis of syncope.  A seizure is a transient neurological dysfunction resulting from excessive/abnormal electrical discharges of cortical neurons.  They may represent epilepsy (a chronic condition characterized by recurrent seizures) but need to be differentiated from a variety of secondary causes.', '92-E', 309, 130, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(473, 'Sexual Maturation, Abnormal ', 'Sexual development is important to adolescent perception of self-image and wellbeing. Many factors may disrupt the normal progression to sexual maturation.', '93-1-E', 309, 131, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(474, 'Sexually Concerned Patient/gender Identity Disorder', 'The social appropriateness of sexuality is culturally determined.  The physician''s own sexual attitude needs to be recognised and taken into account in order to deal with the patient''s concern in a relevant manner.  The patient must be set at ease in order to make possible discussion of private and sensitive sexual issues.', '94-E', 309, 132, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(475, 'Skin Ulcers/skin Tumors (benign And Malignant)', NULL, '95-E', 309, 133, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(476, 'Skin Rash, Macules', NULL, '96-E', 309, 134, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(477, 'Skin Rash, Papules', NULL, '97-E', 309, 135, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(478, 'Childhood Communicable Diseases ', 'Communicable diseases are common in childhood and vary from mild inconveniences to life threatening disorders.  Physicians need to differentiate between these common conditions and initiate management.', '97-1-E', 477, 1, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(479, 'Urticaria/angioedema/anaphylaxis', NULL, '97-2-E', 477, 2, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(480, 'Sleep And Circadian Rhythm Disorders/sleep Apnea Syndrome/in', 'Insomnia is a symptom that affects 1/3 of the population at some time, and is a persistent problem in 10 % of the population.  Affected patients complain of difficulty in initiating and maintaining sleep, and this inability to obtain adequate quantity and quality of sleep results in impaired daytime functioning.', '98-E', 309, 136, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(481, 'Hypernatremia ', 'Although not extremely common, hypernatremia is likely to be encountered with increasing frequency in our ageing population.  It is also encountered at the other extreme of life, the very young, for the same reason: an inability to respond to thirst by drinking water.', '99-1-E', 309, 137, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(482, 'Hyponatremia ', 'Hyponatremia is detected in many asymptomatic patients because serum electrolytes are measured almost routinely.  In children with sodium depletion, the cause of the hyponatremia is usually iatrogenic.  The presence of hyponatremia may predict serious neurologic complications or be relatively benign.', '99-2-E', 309, 138, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(483, 'Sore Throat (rhinorrhea) ', 'Rhinorrhea and sore throat occurring together indicate a viral upper respiratory tract infection such as the "common cold".  Sore throat may be due to a variety of bacterial and viral pathogens (as well as other causes in more unusual circumstances).  Infection is transmitted from person to person and arises from direct contact with infected saliva or nasal secretions.  Rhinorrhea alone is not infective and may be seasonal (hay fever or allergic rhinitis) or chronic (vaso-motor rhinitis).', '100-E', 309, 139, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(484, 'Smell/taste Dysfunction ', 'In order to evaluate patients with smell or taste disorders, a multi-disciplinary approach is required.  This means that in addition to the roles specialists may have, the family physician must play an important role.', '100-1-E', 483, 1, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(485, 'Stature Abnormal (tall Stature/short Stature)', 'To define any growth point, children should be measured accurately and each point (height, weight, and head circumference) plotted.  One of the more common causes of abnormal growth is mis-measurement or aberrant plotting.', '101-E', 309, 140, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(486, 'Strabismus And/or Amblyopia ', 'Parental concern about children with a wandering eye, crossing eye, or poor vision in one eye makes it necessary for physicians to know how to manage such problems.', '102-E', 309, 141, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(487, 'Substance Abuse/drug Addiction/withdrawal', 'Alcohol and nicotine abuse is such a common condition that virtually every clinician is confronted with their complications.  Moreover, 10 - 15% of outpatient visits as well as 25 - 40% of hospital admissions are related to substance abuse and its sequelae.', '103-E', 309, 142, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(488, 'Sudden Infant Death Syndrome(sids)/acute Life Threatening Ev', 'SIDS and/or ALTE are a devastating event for parents, caregivers and health care workers alike.  It is imperative that the precursors, probable cause and parental concerns are extensively evaluated to prevent recurrence.', '104-E', 309, 143, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(489, 'Suicidal Behavior', 'Psychiatric emergencies are common and serious problems.  Suicidal behaviour is one of several psychiatric emergencies which physicians must know how to assess and manage.', '105-E', 309, 144, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(490, 'Syncope/pre-syncope/loss Of Consciousness  (fainting)', 'Syncopal episodes, an abrupt and transient loss of consciousness followed by a rapid and usually complete recovery, are common.  Physicians are required to distinguish syncope from seizures, and benign syncope from syncope caused by serious underlying illness.', '106-E', 309, 145, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(491, 'Fever In A Child/fever In A Child Less Than Three Weeks', 'Fever in children is the most common symptom for which parents seek medical advice.  While most causes are self-limited viral infections (febrile illness of short duration) it is important to identify serious underlying disease and/or those other infections amenable to treatment.', '107-3-E', 309, 146, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(492, 'Fever In The Immune Compromised Host/recurrent Fever', 'Patients with certain immuno-deficiencies are at high risk for infections.  The infective organism and site depend on the type and severity of immuno-suppression.  Some of these infections are life threatening.', '107-4-E', 309, 147, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(493, 'Fever Of Unknown Origin ', 'Unlike acute fever (&lt;2 weeks), which is usually either viral (low-grade, moderate fever) or bacterial (high grade, chills, rigors) in origin, fever of unknown origin is an illness of three weeks or more without an established diagnosis despite appropriate investigation.', '107-2-E', 309, 148, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(494, 'Hyperthermia ', 'Hyperthermia is an elevation in core body temperature due to failure of thermo-regulation (in contrast to fever, which is induced by cytokine activation).  It is a medical emergency and may be associated with severe complications and death.  The differential diagnosis is extensive (includes all causes of fever).', '107-1-E', 309, 149, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(495, 'Hypothermia ', 'Hypothermia is the inability to maintain core body temperature.  Although far less common than is elevation in temperature, hypothermia (central temperature ? 35Â°C) is of considerable importance because it can represent a medical emergency.  Severe hypothermia is defined as a core temperature of &lt;28Â°C.', '107-5-E', 309, 150, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(496, 'Tinnitus', 'Tinnitus is an awareness of sound near the head without an obvious external source.  It may involve one or both ears, be continuous or intermittent.  Although not usually related to serious medical problems, in some it may interfere with daily activities, affect quality of life, and in a very few be indicative of serious organic disease.', '108-E', 309, 151, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(497, 'Trauma/accidents', 'Management of patients with traumatic injuries presents a variety of challenges.  They require evaluation in the emergency department for triage and prevention of further deterioration prior to transfer or discharge.  Early recognition and management of complications along with aggressive treatment of underlying medical conditions are necessary to minimise morbidity and mortality in this patient population.', '109-E', 309, 152, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(498, 'Abdominal Injuries ', 'The major causes of blunt trauma are motor vehicles, auto-pedestrian injuries, and motorcycle/all terrain vehicle injuries.  In children, bicycle injuries, falls, and child abuse also contribute.  Assessment of a patient with an abdominal injury is difficult.  As a consequence, important injuries tend to be missed.  Rupture of a hollow viscus or bleeding from a solid organ may produce few clinical signs.', '109-1-E', 497, 1, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(499, 'Bites, Animal/insects ', 'Since so many households include pets, animal bite wounds are common.  Dog and cat bites account for about 1% of emergency visits, the majority in children.  Some can be serious and lead to limb damage, and at times permanent disability.</p>\r\n<p>Insect bites in Canada most commonly cause a local inflammatory reaction that subsides within a few hours and is mostly a nuisance.  In contrast, mosquitoes can transmit infectious disease to more than 700 million people in other geographic areas of the world (e.g., malaria, yellow fever, dengue, encephalitis and filariasis among others), as well as in Canada.  Tick-borne illness is also common.  On the other hand, systemic reactions to insect bites are extremely rare compared with insect stings.  The most common insects associated with systemic allergic reactions were blackflies, deerflies, and horseflies.', '109-2-E', 497, 2, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(500, 'Bone/joint Injury', 'Major fractures are at times associated with other injuries, and priorities must be set for each patient.  For example, hemodynamic stability takes precedence over fracture management, but an open fracture should be managed as soon as possible.  On the other hand, management of many soft tissue injuries is facilitated by initial stabilization of bone or joint injury. Unexplained fractures in children should alert physicians to the possibility of abuse.', '109-3-E', 497, 3, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(501, 'Chest Injuries ', 'Injury to the chest may be blunt (e.g., motor vehicle accident resulting in steering wheel blow to sternum, falls, explosions, crush injuries) or penetrating (knife/bullet).  In either instance, emergency management becomes extremely important to the eventual outcome.', '109-4-E', 497, 4, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(502, 'Drowning (near-drowning) ', 'Survival after suffocation by submersion in a liquid medium, including loss of consciousness, is defined as near drowning.  The incidence is uncertain, but likely it may occur several hundred times more frequently than drowning deaths (150,000/year worldwide).', '109-6-E', 497, 5, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(503, 'Facial Injuries ', 'Facial injuries are potentially life threatening because of possible damage to the airway and central nervous system.', '109-8-E', 497, 6, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(504, 'Hand/wrist Injuries ', 'Hand injuries are common problems presenting to emergency departments.  The ultimate function of the hand depends upon the quality of the initial care, the severity of the original injury and rehabilitation.', '109-9-E', 497, 7, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(505, 'Head Trauma/brain Death/transplant Donations', 'Most head trauma is mild and not associated with brain injury or long-term sequelae. Improved outcome after head trauma depends upon preventing deterioration and secondary brain injury.  Serious intracranial injuries may remain undetected due to failure to obtain an indicated head CT.', '109-10-E', 497, 8, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(506, 'Nerve Injury ', 'Peripheral nerve injuries often occur as part of more extensive injuries and tend to go unrecognized.  Evaluation of these injuries is based on an accurate knowledge of the anatomy and function of the nerve(s) involved.', '109-11-E', 497, 9, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(507, 'Skin Wounds/regional Anaesthesia', 'Skin and subcutaneous wounds tend to be superficial and can be repaired under local anesthesia.  Animal bite wounds are common and require special consideration.  Since so many households include pets, dog and cat bites account for about 1% of emergency visits, the majority in children.  Some can be serious and lead to limb damage, and at times permanent disability.', '109-12-E', 497, 10, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(508, 'Spinal Trauma', 'Most spinal cord injuries are a result of car accidents, falls, sports-related trauma, or assault with weapons.  The average age at the time of spinal injury is approximately 35 years, and men are four times more likely to be injured than are women.  The sequelae of such events are dire in terms of effect on patient, family, and community.  Initial immobilization and maintenance of ventilation are of critical importance.', '109-13-E', 497, 11, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(509, 'Urinary Tract Injuries ', 'Urinary tract injuries are usually closed rather than penetrating, and may affect the kidneys and/or the collecting system.', '109-14-E', 497, 12, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(510, 'Vascular Injury ', 'Vascular injuries are becoming more common.  Hemorrhage may be occult and require a high index of suspicion (e.g., fracture in an adjacent bone).', '109-15-E', 497, 13, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(511, 'Dysuria And/or Pyuria ', 'Patients with urinary tract infections, especially the very young and very old, may present in an atypical manner.  Appropriate diagnosis and management may prevent significant morbidity.  Dysuria may mean discomfort/pain on micturition or difficulty with micturition.  Pain usually implies infection whereas difficulty is usually related to distal mechanical obstruction (e.g., prostatic).', '110-1-E', 309, 153, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(512, 'Polyuria/polydipsia', 'Urinary frequency, a common complaint, can be confused with polyuria, a less common, but important complaint.  Diabetes mellitus is a common disorder with morbidity and mortality that can be reduced by preventive measures.  Intensive glycemic control during pregnancy will reduce neonatal complications.', '110-2-E', 309, 154, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(513, 'Urinary Obstruction/hesitancy/prostatic Cancer', 'Urinary tract obstruction is a relatively common problem.  The obstruction may be complete or incomplete, and unilateral or bilateral.  Thus, the consequences of the obstruction depend on its nature.', '111-E', 309, 155, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(514, 'Vaginal Bleeding, Excessive/irregular/abnormal', 'Vaginal bleeding is considered abnormal when it occurs at an unexpected time (before menarche or after menopause) or when it varies from the norm in amount or pattern (urinary tract and bowel should be excluded as a source).  Amount or pattern is considered outside normal when it is associated with iron deficiency anemia, it lasts&gt;7days, flow is&gt;80ml/clots, or interval is&lt;24 days.', '112-E', 309, 156, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(515, 'Vaginal Discharge/vulvar Itch/std ', 'Vaginal discharge, with or without pruritus, is a common problem seen in the physician''s office.', '113-E', 309, 157, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(516, 'Violence, Family', 'There are a number of major psychiatric emergencies and social problems which physicians must be prepared to assess and manage.  Domestic violence is one of them, since it has both direct and indirect effects on the health of populations.  Intentional controlling or violent behavior (physical, sexual, or emotional abuse, economic control, or social isolation of the victim) by a person who is/was in an intimate relationship with the victim is domestic violence.  The victim lives in a state of constant fear, terrified about when the next episode of abuse will occur.  Despite this, abuse frequently remains hidden and undiagnosed because patients often conceal that they are in abusive relationships.  It is important for clinicians to seek the diagnosis in certain groups of patients.', '114-E', 309, 158, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(517, 'Adult Abuse/spouse Abuse ', 'The major problem in spouse abuse is wife abuse (some abuse of husbands has been reported).  It is the abuse of power in a relationship involving domination, coercion, intimidation, and the victimization of one person by another.  Ten percent of women in a relationship with a man have experienced abuse.  Of women presenting to a primary care clinic, almost 1/3 reported physical and verbal abuse.', '114-3-E', 516, 1, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(518, 'Child Abuse, Physical/emotional/sexual/neglect/self-induced ', 'Child abuse is intentional harm to a child by the caregiver.  It is part of the spectrum of family dysfunction and leads to significant morbidity and mortality (recently sexual attacks on children by groups of other children have increased).  Abuse causes physical and emotional trauma, and may present as neglect.  The possibility of abuse must be in the mind of all those involved in the care of children who have suffered traumatic injury or have psychological or social disturbances (e.g., aggressive behavior, stress disorder, depressive disorder, substance abuse, etc.).', '114-1-E', 516, 2, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(519, 'Elderly Abuse ', 'Abuse of the elderly may represent an act or omission that results in harm to the elderly person''s health or welfare.  Although the incidence and prevalence in Canada has been difficult to quantitate, in one study 4 % of surveyed seniors report that they experienced abuse.  There are three categories of abuse: domestic, institutional, and self-neglect.', '114-2-E', 516, 3, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(520, 'Acute Visual Disturbance/loss', 'Loss of vision is a frightening symptom that demands prompt attention; most patients require an urgent ophthalmologic opinion.', '115-1-E', 309, 159, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(521, 'Chronic Visual Disturbance/loss ', 'Loss of vision is a frightening symptom that demands prompt attention on the part of the physician.', '115-2-E', 309, 160, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(522, 'Vomiting/nausea ', 'Nausea may occur alone or along with vomiting (powerful ejection of gastric contents), dyspepsia, and other GI complaints.  As a cause of absenteeism from school or workplace, it is second only to the common cold.  When prolonged or severe, vomiting may be associated with disturbances of volume, water and electrolyte metabolism that may require correction prior to other specific treatment.', '116-E', 309, 161, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(523, 'Weakness/paralysis/paresis/loss Of Motion', 'Many patients who complain of weakness are not objectively weak when muscle strength is formally tested.  A careful history and physical examination will permit the distinction between functional disease and true muscle weakness.', '117-E', 309, 162, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(524, 'Weight (low) At Birth/intrauterine Growth Restriction ', 'Intrauterine growth restriction (IUGR) is often a manifestation of congenital infections, poor maternal nutrition, or maternal illness.  In other instances, the infant may be large for the gestational age.  There may be long-term sequelae for both.  Low birth weight is the most important risk factor for infant mortality.  It is also a significant determinant of infant and childhood morbidity, particularly neuro-developmental problems and learning disabilities.', '118-3-E', 309, 163, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(525, 'Weight Gain/obesity ', 'Obesity is a chronic disease that is increasing in prevalence. The percentage of the population with a body mass index of&gt;30 kg/m2 is approximately 15%.', '118-1-E', 309, 164, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(526, 'Weight Loss/eating Disorders/anorexia ', 'Although voluntary weight loss may be of no concern in an obese patient, it could be a manifestation of psychiatric illness.  Involuntary clinically significant weight loss (&gt;5% baseline body weight or 5 kg) is nearly always a sign of serious medical or psychiatric illness and should be investigated.', '118-2-E', 309, 165, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(527, 'Lower Respiratory Tract Disorders ', 'Individuals with episodes of wheezing, breathlessness, chest tightness, and cough usually have limitation of airflow.  Frequently this limitation is reversible with treatment.  Without treatment it may be lethal.', '119-1-E', 309, 166, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(528, 'Upper Respiratory Tract Disorders ', 'Wheezing, a continuous musical sound&gt;1/4 seconds, is produced by vibration of the walls of airways narrowed almost to the point of closure.  It can originate from airways of any size, from large upper airways to intrathoracic small airways.  It can be either inspiratory or expiratory, unlike stridor (a noisy, crowing sound, usually inspiratory and resulting from disturbances in or adjacent to the larynx).', '119-2-E', 309, 167, 1, 1271174178, 3499);
INSERT INTO `global_lu_objectives` VALUES(529, 'White Blood Cells, Abnormalities Of', 'Because abnormalities of white blood cells (WBCs) occur commonly in both asymptomatic as well as acutely ill patients, every physician will need to evaluate patients for this common problem.  Physicians also need to select medications to be prescribed mindful of the morbidity and mortality associated with drug-induced neutropenia and agranulocytosis.', '120-E', 309, 168, 1, 1271174178, 3499);

CREATE TABLE `global_lu_provinces` (
  `province_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL,
  `province` varchar(200) NOT NULL,
  `abbreviation` varchar(200) NOT NULL,
  PRIMARY KEY (`province_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=64 ;

INSERT INTO `global_lu_provinces` VALUES(1, 39, 'Alberta', 'AB');
INSERT INTO `global_lu_provinces` VALUES(2, 39, 'British Columbia', 'BC');
INSERT INTO `global_lu_provinces` VALUES(3, 39, 'Manitoba', 'MB');
INSERT INTO `global_lu_provinces` VALUES(4, 39, 'New Brunswick', 'NB');
INSERT INTO `global_lu_provinces` VALUES(5, 39, 'Newfoundland and Labrador', 'NL');
INSERT INTO `global_lu_provinces` VALUES(6, 39, 'Northwest Territories', 'NT');
INSERT INTO `global_lu_provinces` VALUES(7, 39, 'Nova Scotia', 'NS');
INSERT INTO `global_lu_provinces` VALUES(8, 39, 'Nunavut', 'NU');
INSERT INTO `global_lu_provinces` VALUES(9, 39, 'Ontario', 'ON');
INSERT INTO `global_lu_provinces` VALUES(10, 39, 'Prince Edward Island', 'PE');
INSERT INTO `global_lu_provinces` VALUES(11, 39, 'Quebec', 'QC');
INSERT INTO `global_lu_provinces` VALUES(12, 39, 'Saskatchewan', 'SK');
INSERT INTO `global_lu_provinces` VALUES(13, 39, 'Yukon Territory', 'YT');
INSERT INTO `global_lu_provinces` VALUES(14, 227, 'Alabama', 'AL');
INSERT INTO `global_lu_provinces` VALUES(15, 227, 'Alaska', 'AK');
INSERT INTO `global_lu_provinces` VALUES(16, 227, 'Arizona', 'AZ');
INSERT INTO `global_lu_provinces` VALUES(17, 227, 'Arkansas', 'AR');
INSERT INTO `global_lu_provinces` VALUES(18, 227, 'California', 'CA');
INSERT INTO `global_lu_provinces` VALUES(19, 227, 'Colorado', 'CO');
INSERT INTO `global_lu_provinces` VALUES(20, 227, 'Connecticut', 'CT');
INSERT INTO `global_lu_provinces` VALUES(21, 227, 'Delaware', 'DE');
INSERT INTO `global_lu_provinces` VALUES(22, 227, 'Florida', 'FL');
INSERT INTO `global_lu_provinces` VALUES(23, 227, 'Georgia', 'GA');
INSERT INTO `global_lu_provinces` VALUES(24, 227, 'Hawaii', 'HI');
INSERT INTO `global_lu_provinces` VALUES(25, 227, 'Idaho', 'ID');
INSERT INTO `global_lu_provinces` VALUES(26, 227, 'Illinois', 'IL');
INSERT INTO `global_lu_provinces` VALUES(27, 227, 'Indiana', 'IN');
INSERT INTO `global_lu_provinces` VALUES(28, 227, 'Iowa', 'IA');
INSERT INTO `global_lu_provinces` VALUES(29, 227, 'Kansas', 'KS');
INSERT INTO `global_lu_provinces` VALUES(30, 227, 'Kentucky', 'KY');
INSERT INTO `global_lu_provinces` VALUES(31, 227, 'Louisiana', 'LA');
INSERT INTO `global_lu_provinces` VALUES(32, 227, 'Maine', 'ME');
INSERT INTO `global_lu_provinces` VALUES(33, 227, 'Maryland', 'MD');
INSERT INTO `global_lu_provinces` VALUES(34, 227, 'Massachusetts', 'MA');
INSERT INTO `global_lu_provinces` VALUES(35, 227, 'Michigan', 'MI');
INSERT INTO `global_lu_provinces` VALUES(36, 227, 'Minnesota', 'MN');
INSERT INTO `global_lu_provinces` VALUES(37, 227, 'Mississippi', 'MS');
INSERT INTO `global_lu_provinces` VALUES(38, 227, 'Missouri', 'MO');
INSERT INTO `global_lu_provinces` VALUES(39, 227, 'Montana', 'MT');
INSERT INTO `global_lu_provinces` VALUES(40, 227, 'Nebraska', 'NE');
INSERT INTO `global_lu_provinces` VALUES(41, 227, 'Nevada', 'NV');
INSERT INTO `global_lu_provinces` VALUES(42, 227, 'New Hampshire', 'NH');
INSERT INTO `global_lu_provinces` VALUES(43, 227, 'New Jersey', 'NJ');
INSERT INTO `global_lu_provinces` VALUES(44, 227, 'New Mexico', 'NM');
INSERT INTO `global_lu_provinces` VALUES(45, 227, 'New York', 'NY');
INSERT INTO `global_lu_provinces` VALUES(46, 227, 'North Carolina', 'NC');
INSERT INTO `global_lu_provinces` VALUES(47, 227, 'North Dakota', 'ND');
INSERT INTO `global_lu_provinces` VALUES(48, 227, 'Ohio', 'OH');
INSERT INTO `global_lu_provinces` VALUES(49, 227, 'Oklahoma', 'OK');
INSERT INTO `global_lu_provinces` VALUES(50, 227, 'Oregon', 'OR');
INSERT INTO `global_lu_provinces` VALUES(51, 227, 'Pennsylvania', 'PA');
INSERT INTO `global_lu_provinces` VALUES(52, 227, 'Rhode Island', 'RI');
INSERT INTO `global_lu_provinces` VALUES(53, 227, 'South Carolina', 'SC');
INSERT INTO `global_lu_provinces` VALUES(54, 227, 'South Dakota', 'SD');
INSERT INTO `global_lu_provinces` VALUES(55, 227, 'Tennessee', 'TN');
INSERT INTO `global_lu_provinces` VALUES(56, 227, 'Texas', 'TX');
INSERT INTO `global_lu_provinces` VALUES(57, 227, 'Utah', 'UT');
INSERT INTO `global_lu_provinces` VALUES(58, 227, 'Vermont', 'VT');
INSERT INTO `global_lu_provinces` VALUES(59, 227, 'Virginia', 'VA');
INSERT INTO `global_lu_provinces` VALUES(60, 227, 'Washington', 'WA');
INSERT INTO `global_lu_provinces` VALUES(61, 227, 'West Virginia', 'WV');
INSERT INTO `global_lu_provinces` VALUES(62, 227, 'Wisconsin', 'WI');
INSERT INTO `global_lu_provinces` VALUES(63, 227, 'Wyoming', 'WY');

CREATE TABLE `global_lu_schools` (
  `schools_id` int(11) NOT NULL AUTO_INCREMENT,
  `school_title` varchar(250) NOT NULL,
  PRIMARY KEY (`schools_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;

INSERT INTO `global_lu_schools` VALUES(1, 'University of Alberta');
INSERT INTO `global_lu_schools` VALUES(2, 'University of British Columbia');
INSERT INTO `global_lu_schools` VALUES(3, 'University of Calgary');
INSERT INTO `global_lu_schools` VALUES(4, 'Dalhousie University');
INSERT INTO `global_lu_schools` VALUES(5, 'Laval University');
INSERT INTO `global_lu_schools` VALUES(6, 'University of Manitoba');
INSERT INTO `global_lu_schools` VALUES(7, 'McGill University');
INSERT INTO `global_lu_schools` VALUES(8, 'McMaster University');
INSERT INTO `global_lu_schools` VALUES(9, 'Memorial University of Newfoundland');
INSERT INTO `global_lu_schools` VALUES(10, 'Universite de Montreal');
INSERT INTO `global_lu_schools` VALUES(11, 'Northern Ontario School of Medicine');
INSERT INTO `global_lu_schools` VALUES(12, 'University of Ottawa');
INSERT INTO `global_lu_schools` VALUES(13, 'Queen''s University');
INSERT INTO `global_lu_schools` VALUES(14, 'University of Saskatchewan');
INSERT INTO `global_lu_schools` VALUES(15, 'Universite de Sherbrooke');
INSERT INTO `global_lu_schools` VALUES(16, 'University of Toronto');
INSERT INTO `global_lu_schools` VALUES(17, 'University of Western Ontario');

CREATE TABLE `notices` (
  `notice_id` int(12) NOT NULL AUTO_INCREMENT,
  `target` varchar(32) NOT NULL DEFAULT '',
  `organisation_id` int(12) DEFAULT NULL,
  `notice_summary` text NOT NULL,
  `notice_details` text NOT NULL,
  `display_from` bigint(64) NOT NULL DEFAULT '0',
  `display_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`notice_id`),
  KEY `target` (`target`),
  KEY `display_from` (`display_from`),
  KEY `display_until` (`display_until`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `permissions` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `poll_answers` (
  `answer_id` int(12) NOT NULL AUTO_INCREMENT,
  `poll_id` int(12) NOT NULL DEFAULT '0',
  `answer_text` varchar(255) NOT NULL,
  `answer_order` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`answer_id`),
  KEY `poll_id` (`poll_id`),
  KEY `answer_order` (`answer_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `poll_questions` (
  `poll_id` int(12) NOT NULL AUTO_INCREMENT,
  `poll_target` varchar(32) NOT NULL DEFAULT 'all',
  `poll_question` text NOT NULL,
  `poll_from` bigint(64) NOT NULL DEFAULT '0',
  `poll_until` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`poll_id`),
  KEY `poll_target` (`poll_target`),
  KEY `poll_from` (`poll_from`),
  KEY `poll_until` (`poll_until`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `poll_results` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `quiz_contacts` (
  `qcontact_id` int(12) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`qcontact_id`),
  KEY `quiz_id` (`quiz_id`,`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `quiz_question_responses` (
  `qqresponse_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `qquestion_id` int(12) unsigned NOT NULL,
  `response_text` longtext NOT NULL,
  `response_order` int(3) unsigned NOT NULL,
  `response_correct` enum('0','1') NOT NULL DEFAULT '0',
  `response_is_html` enum('0','1') NOT NULL,
  `response_feedback` text NOT NULL,
  PRIMARY KEY (`qqresponse_id`),
  KEY `qquestion_id` (`qquestion_id`,`response_order`,`response_correct`),
  KEY `response_is_html` (`response_is_html`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `quiz_questions` (
  `qquestion_id` int(12) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(12) NOT NULL DEFAULT '0',
  `questiontype_id` int(12) NOT NULL DEFAULT '1',
  `question_text` longtext NOT NULL,
  `question_points` int(6) NOT NULL DEFAULT '0',
  `question_order` int(6) NOT NULL DEFAULT '0',
  `randomize_responses` int(1) NOT NULL,
  PRIMARY KEY (`qquestion_id`),
  KEY `quiz_id` (`quiz_id`,`questiontype_id`,`question_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `quizzes` (
  `quiz_id` int(12) NOT NULL AUTO_INCREMENT,
  `quiz_title` varchar(64) NOT NULL,
  `quiz_description` text NOT NULL,
  `quiz_active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`quiz_id`),
  KEY `quiz_active` (`quiz_active`),
  FULLTEXT KEY `quiz_title` (`quiz_title`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE `quizzes_lu_questiontypes` (
  `questiontype_id` int(12) NOT NULL AUTO_INCREMENT,
  `questiontype_title` varchar(64) NOT NULL,
  `questiontype_description` text NOT NULL,
  `questiontype_active` int(1) NOT NULL DEFAULT '1',
  `questiontype_order` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`questiontype_id`),
  KEY `questiontype_active` (`questiontype_active`,`questiontype_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

INSERT INTO `quizzes_lu_questiontypes` VALUES(1, 'Multiple Choice Question', '', 1, 0);

CREATE TABLE `quizzes_lu_quiztypes` (
  `quiztype_id` int(12) NOT NULL AUTO_INCREMENT,
  `quiztype_code` varchar(12) NOT NULL,
  `quiztype_title` varchar(64) NOT NULL,
  `quiztype_description` text NOT NULL,
  `quiztype_active` int(1) NOT NULL DEFAULT '1',
  `quiztype_order` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`quiztype_id`),
  KEY `quiztype_active` (`quiztype_active`,`quiztype_order`),
  KEY `quiztype_code` (`quiztype_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

INSERT INTO `quizzes_lu_quiztypes` VALUES(1, 'delayed', 'Delayed Quiz Results', 'This option restricts the learners ability to review the results of a quiz attempt (i.e. score, correct / incorrect responses, and question feedback) until after the time release period has expired.', 1, 0);
INSERT INTO `quizzes_lu_quiztypes` VALUES(2, 'immediate', 'Immediate Quiz Results', 'This option will allow the learner to review the results of a quiz attempt (i.e. score, correct / incorrect responses, and question feedback) immediately after they complete the quiz.', 1, 1);

CREATE TABLE `statistics` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `statistics` VALUES(1, 1, 1272911839, 'courses', 'view', 'course_id', '1', 1313380800);

CREATE TABLE `users_online` (
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

INSERT INTO `users_online` VALUES('7eb1674bf73b7c5b10dd89af13d2acf0', '::1', 1, 'admin', 'John', 'Doe', 1272912222);
