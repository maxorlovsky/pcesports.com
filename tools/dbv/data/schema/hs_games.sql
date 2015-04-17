CREATE TABLE `hs_games` (
  `match_id` int(10) unsigned NOT NULL,
  `player1_ban` varchar(15) DEFAULT NULL,
  `player2_ban` varchar(15) DEFAULT NULL,
  UNIQUE KEY `match_id` (`match_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8