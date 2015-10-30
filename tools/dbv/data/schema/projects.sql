CREATE TABLE `projects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) DEFAULT NULL,
  `team_name` varchar(100) DEFAULT NULL,
  `challonge_link` varchar(100) DEFAULT NULL,
  `challonge_key` varchar(100) DEFAULT NULL,
  `widget_url` varchar(100) DEFAULT NULL,
  `additional_mail_text` text,
  `smtp_config` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8