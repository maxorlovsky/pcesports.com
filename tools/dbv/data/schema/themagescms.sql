CREATE TABLE `themagescms` (
  `setting` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  UNIQUE KEY `setting` (`setting`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8