UPDATE `themagescms` SET `value` = '3.13' WHERE `setting` = 'version';
INSERT INTO `tm_settings` (`setting`, `value`, `field`, `type`, `position`) VALUES ('https', '0', 'HTTPS always', 'checkbox', '9');
ALTER TABLE `tm_settings` DROP INDEX setting;
ALTER TABLE `tm_settings` ADD UNIQUE(`setting`);