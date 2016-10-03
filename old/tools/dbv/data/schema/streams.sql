CREATE TABLE `streams` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `participant_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(200) NOT NULL,
  `display_name` varchar(200) DEFAULT NULL,
  `featured` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `online` int(10) unsigned NOT NULL DEFAULT '0',
  `game` enum('lol','hs','dota','smite','cs','other','lolcup','smitecup') NOT NULL,
  `languages` enum('ru','en','both') NOT NULL,
  `viewers` int(10) unsigned NOT NULL DEFAULT '0',
  `approved` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `participant_id` (`participant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8