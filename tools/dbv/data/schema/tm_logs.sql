CREATE TABLE `tm_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module` varchar(99) DEFAULT NULL,
  `type` varchar(99) DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `date` datetime DEFAULT NULL,
  `ip` varchar(30) DEFAULT NULL,
  `info` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8