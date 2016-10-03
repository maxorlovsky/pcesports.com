CREATE TABLE `teams2users_queue` (
  `team_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `added_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `team-user` (`team_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8