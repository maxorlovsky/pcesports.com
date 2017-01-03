CREATE TABLE `tournaments_external` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project` varchar(30) NOT NULL,
  `game` varchar(10) NOT NULL,
  `server` varchar(5) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `dates_registration` varchar(15) NOT NULL,
  `dates_start` varchar(15) NOT NULL,
  `time` varchar(20) DEFAULT NULL,
  `event_id` int(10) unsigned NOT NULL DEFAULT '0',
  `prize` varchar(50) NOT NULL,
  `max_num` smallint(3) unsigned NOT NULL DEFAULT '0',
  `status` enum('upcoming','registration','check_in','live','ended') NOT NULL,
  `reg_activated` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `finalized` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `game` (`game`),
  KEY `server` (`server`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8