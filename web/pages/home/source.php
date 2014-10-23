<?php

class home
{
	public $news;
	public $slider;
    public $streams;
	
	public function __construct($params = array()) {
		
		$this->slider = array(
			array(_cfg('href').'/leagueoflegends/eune', _cfg('img').'/poster-eune.jpg'),
            array(_cfg('href').'/hearthstone', _cfg('img').'/poster-hl.jpg'),
		);
        
        $this->streams = Db::fetchRows(
            'SELECT `id`, `name`, `display_name`, IF(`online` >= '.(time()-360).', 1, 0) AS `onlineStatus` '.
            'FROM `streams` '.
            'WHERE `online` != 0 AND '.
            '`approved` = 1 AND '.
            '(`id` = 1 OR `id` = 2) '.
            'ORDER BY `onlineStatus` DESC, `featured` DESC, `viewers` DESC '
		);
		
		$rows = Db::fetchRows('SELECT `n`.`id`, `n`.`title`, `n`.`extension`, `n`.`short_english` AS `value`, `n`.`added`, `n`.`likes`, `n`.`comments`, `n`.`views`, `a`.`login`, `nl`.`ip` AS `active` '.
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