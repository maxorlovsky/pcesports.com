CREATE TABLE `tm_settings` (
  `setting` varchar(255) NOT NULL,
  `value` varchar(1000) NOT NULL DEFAULT '',
  `field` varchar(99) DEFAULT NULL,
  `type` enum('text','checkbox','level') NOT NULL DEFAULT 'text',
  `position` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`setting`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8