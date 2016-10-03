ALTER TABLE `streams` ADD `participant_id` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `user_id`, ADD INDEX `participant_id` (`participant_id`);
ALTER TABLE `streams` CHANGE `user_id` `user_id` INT(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `streams` ADD `tournament_id` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `participant_id`, ADD INDEX `tournament_id` (`tournament_id`);
ALTER TABLE `streams` CHANGE `approved` `approved` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1';