CREATE TABLE `subscribe_sender` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(100) NOT NULL DEFAULT 'all',
  `subject` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `emails` smallint(6) unsigned NOT NULL DEFAULT '0',
  `timestamp` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8