ALTER TABLE `participants_external` ADD `deleted` TINYINT UNSIGNED NOT NULL DEFAULT '0' , ADD `update_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ;