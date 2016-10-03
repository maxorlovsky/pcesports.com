CREATE TABLE `tm_user_auth` (
  `user_id` int(10) unsigned NOT NULL,
  `token` varchar(50) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8