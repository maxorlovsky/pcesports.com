CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(250) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `name` varchar(32) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `timezone` varchar(4) NOT NULL DEFAULT '0',
  `avatar` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `battletag` varchar(30) DEFAULT NULL,
  `https` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8