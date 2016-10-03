CREATE TABLE `players` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `game` varchar(10) NOT NULL,
  `tournament_id` smallint(5) unsigned NOT NULL,
  `participant_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `player_num` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `player_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tournament_id` (`tournament_id`,`participant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8