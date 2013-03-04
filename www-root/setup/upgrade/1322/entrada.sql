ALTER TABLE `ar_lu_contribution_roles` ADD `visible` INT( 1 ) NOT NULL DEFAULT '1';
ALTER TABLE `ar_lu_contribution_types` ADD `visible` INT( 1 ) NOT NULL DEFAULT '1';
ALTER TABLE `ar_scholarly_activity` ADD `location` VARCHAR( 25 ) NULL AFTER `description`;
ALTER TABLE `ar_clinical_education` ADD `research_percentage` INT( 1 ) NULL DEFAULT '0' AFTER `average_hours`;
ALTER TABLE `ar_non_peer_reviewed_papers` ADD `category` VARCHAR( 10 ) NULL AFTER `author_list`;
ALTER TABLE `ar_peer_reviewed_papers` ADD `category` VARCHAR( 10 ) NULL AFTER `author_list`;
ALTER TABLE `ar_book_chapter_mono` ADD `category` VARCHAR( 10 ) NULL AFTER `editor_list`;

UPDATE `settings` SET `value` = '1322' WHERE `shortname` = 'version_db';