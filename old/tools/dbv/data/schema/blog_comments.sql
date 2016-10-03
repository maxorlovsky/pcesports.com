CREATE TABLE `blog_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `answer_to_id` int(10) unsigned NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  `ip` varchar(20) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - ok, 1 - deleted, 2 - reported',
  `edited` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `news_id` (`blog_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8