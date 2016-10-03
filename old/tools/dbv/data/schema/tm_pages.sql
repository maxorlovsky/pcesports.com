CREATE TABLE `tm_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `link` varchar(99) NOT NULL,
  `value` varchar(99) NOT NULL,
  `logged_in` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `text_russian` text,
  `text_english` text,
  `text_polish` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8