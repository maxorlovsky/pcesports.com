CREATE TABLE `participants_external` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip` varchar(20) DEFAULT NULL,
  `name` varchar(60) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contact_info` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8