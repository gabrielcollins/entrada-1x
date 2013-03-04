INSERT INTO `evaluations_lu_targets` (`target_shortname`, `target_title`, `target_description`, `target_active`)
VALUES
	('resident', 'Resident Evaluation', '', 1);

UPDATE `settings` SET `value` = '1326' WHERE `shortname` = 'version_db';
