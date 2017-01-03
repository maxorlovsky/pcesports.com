ALTER TABLE `participants_external` ADD `checked_in` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `verified`;
ALTER TABLE `participants_external` ADD `challonge_id` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `contact_info`, ADD INDEX `challonge_id` (`challonge_id`) ;
ALTER TABLE `participants_external` ADD `online` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `deleted`;