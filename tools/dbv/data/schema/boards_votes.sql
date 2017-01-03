CREATE TABLE `boards_votes` (
  `board_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `comment_id` int(10) unsigned NOT NULL DEFAULT '0',
  `direction` enum('plus','minus') NOT NULL,
  KEY `board_id` (`board_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8