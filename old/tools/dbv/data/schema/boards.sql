CREATE TABLE `boards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `category` enum('lol','hs','dota','smite','csgo','general') NOT NULL,
  `title` varchar(255) NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - ok, 1 - deleted, 2 - reported',
  `text` text,
  `ip` varchar(20) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comments` int(10) unsigned NOT NULL DEFAULT '0',
  `votes` int(10) NOT NULL DEFAULT '0',
  `activity` int(10) unsigned NOT NULL,
  `edited` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `activity` (`activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8