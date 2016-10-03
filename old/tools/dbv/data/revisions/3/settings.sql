UPDATE `tm_settings` SET `field` = 'Tournament number (League of Legends - EUW)' WHERE `tm_settings`.`setting` = 'lol-current-number-euw';
DELETE FROM `tm_settings` WHERE `tm_settings`.`setting` = 'hslan-current-number';
DELETE FROM `tm_settings` WHERE `tm_settings`.`setting` = 'tournament-reg-hslan';