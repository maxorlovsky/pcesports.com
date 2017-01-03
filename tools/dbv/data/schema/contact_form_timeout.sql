CREATE TABLE `contact_form_timeout` (
  `ip` varchar(20) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8