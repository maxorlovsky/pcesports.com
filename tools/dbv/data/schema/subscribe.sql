CREATE TABLE `subscribe` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(99) NOT NULL,
  `unsublink` varchar(200) NOT NULL,
  `removed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `theme` enum('all','lol','hs','dota','smite','cs') NOT NULL DEFAULT 'all',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8