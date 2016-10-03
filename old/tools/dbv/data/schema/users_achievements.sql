CREATE TABLE `users_achievements` (
  `user_id` int(10) unsigned NOT NULL,
  `achievement_id` int(10) unsigned NOT NULL,
  `done` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - no, 1 - yes',
  `current` int(6) unsigned NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniques` (`user_id`,`achievement_id`) COMMENT 'user and achv id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8