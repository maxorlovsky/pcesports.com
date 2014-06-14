<?php

class home
{
	public $news;
	public $slider;
	
	public function __construct($params = array()) {
		
		$this->slider = array(
			//array(_cfg('href').'/leagueoflegends/4', _cfg('img').'/poster-lol-4.jpg'),
		);
		
		$rows = Db::fetchRows('SELECT `n`.`id`, `n`.`title`, `n`.`extension`, `n`.`short_english` AS `value`, `n`.`added`, `n`.`likes`, `n`.`views`, `a`.`login`, `nl`.`ip` AS `active` '.
			'FROM `news` AS `n` '.
			'LEFT JOIN `tm_admins` AS `a` ON `n`.`admin_id` = `a`.`id` '.
			'LEFT JOIN `news_likes` AS `nl` ON `n`.`id` = `nl`.`news_id` AND `nl`.`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'"'.
			'WHERE `able` = 1 '.
			'ORDER BY `id` DESC '.
			'LIMIT 7'
		);
		
		$this->news = new stdClass();
		$this->news->others = new stdClass();
		if ($rows) {
			$i = 0;
			foreach($rows as $k => $v) {
				if ($i == 0) {
					$this->news->top = $v;
					$i = 1;
				}
				else {
					$this->news->others->$k = $v;
				}
			}
		}
	}
	
	public function showTemplate() {
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
}