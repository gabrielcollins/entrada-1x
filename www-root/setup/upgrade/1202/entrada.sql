UPDATE `global_lu_countries` SET `country` = 'Aland Islands' WHERE `countries_id` = 2;
UPDATE `global_lu_countries` SET `country` = 'Cote D''Ivoire (Ivory Coast)' WHERE `countries_id` = 55;
UPDATE `global_lu_countries` SET `country` = 'Reunion' WHERE `countries_id` = 177;

UPDATE `settings` SET `value` = '1202' WHERE `shortname` = 'version_db';