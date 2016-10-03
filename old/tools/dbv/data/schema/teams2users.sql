CREATE TABLE `teams2users` (
  `team_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  UNIQUE KEY `unique` (`team_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8