ALTER TABLE `apartments` ADD keys_firstname VARCHAR(32) NOT NULL AFTER `super_email`;
ALTER TABLE `apartments` ADD keys_lastname VARCHAR(32) NOT NULL AFTER `keys_firstname`;
ALTER TABLE `apartments` ADD keys_phone VARCHAR(32) NOT NULL AFTER `keys_lastname`;
ALTER TABLE `apartments` ADD keys_email VARCHAR(128) NOT NULL AFTER `keys_phone`;