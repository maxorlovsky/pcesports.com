CREATE TABLE `users_temp` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `code` varchar(50) NOT NULL,
  PRIMARY KEY (`id`) COMMENT 'id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8