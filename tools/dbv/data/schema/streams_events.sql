CREATE TABLE `streams_events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `participant_id` int(10) unsigned NOT NULL DEFAULT '0',
  `tournament_id` int(10) unsigned NOT NULL,
  `game` varchar(10) NOT NULL,
  `name` varchar(200) NOT NULL,
  `display_name` varchar(200) DEFAULT NULL,
  `online` int(10) unsigned NOT NULL DEFAULT '0',
  `viewers` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `participant_id` (`participant_id`),
  KEY `tournament_id` (`tournament_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8