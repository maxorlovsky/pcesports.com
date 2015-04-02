CREATE TABLE `users_auth` (
  `user_id` int(10) unsigned NOT NULL,
  `token` varchar(50) NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8