CREATE TABLE `tm_modules` (
  `name` varchar(255) NOT NULL,
  `level` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `added_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8