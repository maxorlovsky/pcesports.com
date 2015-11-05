CREATE TABLE IF NOT EXISTS `faq` (
  `id` int(10) unsigned NOT NULL,
  `question_english` varchar(255) NOT NULL,
  `answer_english` text,
  `weight` smallint(6) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;