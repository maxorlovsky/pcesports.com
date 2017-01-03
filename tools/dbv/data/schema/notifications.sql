CREATE TABLE `notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `game` varchar(10) NOT NULL,
  `tournament_name` varchar(50) NOT NULL,
  `delivered` tinyint(2) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `game` (`game`,`tournament_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8