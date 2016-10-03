CREATE TABLE `blog_likes` (
  `blog_id` int(10) unsigned NOT NULL,
  `ip` varchar(20) NOT NULL,
  KEY `ip` (`ip`),
  KEY `index` (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8