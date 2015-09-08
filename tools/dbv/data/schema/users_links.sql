CREATE TABLE `users_links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(10) unsigned NOT NULL,
  `type` enum('pw','email') NOT NULL,
  `code` varchar(50) NOT NULL,
  `additional` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user_id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8