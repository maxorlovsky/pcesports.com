CREATE TABLE `fights` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `match_id` bigint(20) unsigned NOT NULL,
  `player1_id` bigint(20) unsigned NOT NULL,
  `player2_id` bigint(20) unsigned NOT NULL,
  `done` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `screenshots` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8