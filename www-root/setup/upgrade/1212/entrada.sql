ALTER TABLE `pg_eval_response_rates` ADD COLUMN `gen_date` DATE NOT NULL AFTER `percent_complete`;

UPDATE `settings` SET `value` = '1212' WHERE `shortname` = 'version_db';