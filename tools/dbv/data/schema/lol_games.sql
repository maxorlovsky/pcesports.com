CREATE TABLE `lol_games` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `match_id` bigint(20) unsigned NOT NULL,
  `game_id` bigint(20) unsigned DEFAULT NULL,
  `participant_id1` int(10) unsigned DEFAULT NULL,
  `participant_id2` int(10) unsigned DEFAULT NULL,
  `message` text,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ended` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8