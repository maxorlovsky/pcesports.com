CREATE TABLE `tm_admins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(255) NOT NULL,
  `email` varchar(99) DEFAULT NULL,
  `password` varchar(99) NOT NULL,
  `level` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `custom_access` text,
  `last_login` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `login_count` int(10) unsigned NOT NULL DEFAULT '0',
  `last_ip` varchar(40) NOT NULL DEFAULT '',
  `language` char(2) NOT NULL DEFAULT 'en',
  `editRedirect` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8