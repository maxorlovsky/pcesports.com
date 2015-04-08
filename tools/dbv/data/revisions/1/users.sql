ALTER TABLE `users` ADD `summer_time` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `timezone`;
UPDATE `users` SET `summer_time` = 1 WHERE `summer_time` = 0;