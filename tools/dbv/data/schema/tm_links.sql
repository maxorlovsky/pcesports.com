CREATE TABLE `tm_links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `link` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL,
  `main_link` int(10) unsigned NOT NULL DEFAULT '0',
  `position` tinyint(3) NOT NULL DEFAULT '0',
  `able` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `block` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `logged_in` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8