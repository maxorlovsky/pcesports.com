ALTER TABLE `participants_external` ADD `project` VARCHAR(30) NULL AFTER `id`;
UPDATE `participants_external` SET `project` = 'unicon';