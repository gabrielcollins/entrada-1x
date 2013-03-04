ALTER TABLE `student_observerships` MODIFY `preceptor_prefix` VARCHAR(4) AFTER `site`;
ALTER TABLE `student_observerships` ADD `preceptor_email` VARCHAR(255) AFTER `preceptor_proxy_id`;
ALTER TABLE `student_observerships` ADD `status` ENUM('UNCONFIRMED','CONFIRMED','REJECTED') DEFAULT 'UNCONFIRMED' AFTER `preceptor_email`;
ALTER TABLE `student_observerships` ADD `unique_id` VARCHAR(64) AFTER `status`;
ALTER TABLE `student_observerships` ADD `notice_sent` INT(11) AFTER `unique_id`;
UPDATE `student_observerships` SET `status` = 'CONFIRMED', `notice_sent` = '1';
UPDATE `settings` SET `value` = '1313' WHERE `shortname` = 'version_db';