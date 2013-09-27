INSERT INTO `community_type_page_options` (`ctpoption_id`, `ctpage_id`, `option_title`, `option_value`, `proxy_id`, `updated_date`)
VALUES
	(1, 39, 'community_title', 1, 1, 0);


INSERT INTO `community_type_pages` (`type_id`, `type_scope`, `parent_id`, `page_order`, `page_type`, `menu_title`, `page_title`, `page_url`, `page_content`, `page_active`, `page_visible`, `allow_member_view`, `allow_troll_view`, `allow_public_view`, `lock_page`, `updated_date`, `updated_by`)
VALUES
	(3, 'global', 0, 0, 'default', 'Community Title', 'Community Title', '', ' ', 1, 1, 1, 1, 1, 0, 0, 1),
	(3, 'global', 0, 7, 'default', 'Credits', 'Credits', 'credits', '', 1, 1, 1, 1, 1, 0, 0, 1),
	(3, 'global', 0, 4, 'default', 'Formative Assessment', 'Formative Assessment', 'formative_assessment', '', 1, 1, 1, 1, 1, 0, 0, 1),
	(3, 'global', 0, 3, 'default', 'Foundational Knowledge', 'Foundational Knowledge', 'foundational_knowledge', '', 1, 1, 1, 1, 1, 0, 0, 1),
	(3, 'global', 0, 1, 'default', 'Introduction', 'Introduction', 'introduction', '', 1, 1, 1, 1, 1, 0, 0, 1),
	(3, 'global', 0, 2, 'default', 'Objectives', 'Objectives', 'objectives', '', 1, 1, 1, 1, 1, 0, 0, 1),
	(3, 'global', 0, 8, 'url', 'Print Version', 'Print Version', 'print_version', '', 1, 1, 1, 1, 1, 0, 0, 1),
	(3, 'global', 0, 6, 'default', 'Summary', 'Summary', 'summary', '', 1, 1, 1, 1, 1, 0, 0, 1),
	(3, 'global', 0, 5, 'default', 'Test your understanding', 'Test your understanding', 'test_your_understanding', '', 1, 1, 1, 1, 1, 0, 0, 1);

INSERT INTO `community_type_templates` (`template_id`, `type_id`, `type_scope`)
VALUES
	(4, 3, 'global'),
	(3, 3, 'global');

UPDATE `settings` SET `value` = '1502' WHERE `shortname` = 'version_db';