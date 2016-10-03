CREATE TABLE `achievements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `name` varchar(250) NOT NULL,
  `requirement` int(6) unsigned NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `image` varchar(50) DEFAULT NULL,
  `points` tinyint(2) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`) COMMENT 'id',
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8