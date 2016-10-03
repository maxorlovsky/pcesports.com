ALTER TABLE `tournaments` ADD `battlefy_id` VARCHAR(50) NULL AFTER `event_id`, ADD `battlefy_stage` VARCHAR(50) NULL AFTER `battlefy_id`;
ALTER TABLE `tournaments` DROP `reg_activated`;