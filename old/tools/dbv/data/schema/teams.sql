CREATE TABLE `teams` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id_captain` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `tag` varchar(5) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `facebook` varchar(50) DEFAULT NULL,
  `twitter` varchar(50) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_captain` (`user_id_captain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8