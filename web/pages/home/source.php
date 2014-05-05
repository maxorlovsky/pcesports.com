<?php

class home
{
	public $news;
	
	function __construct($params = array()) {
		
		$slider = array(
			array('#', _cfg('img').'/poster-lol.jpg'),
			array('#', _cfg('img').'/poster-hs.png'),
		);
		
		$rows = Db::fetchRows('SELECT `n`.`id`, `n`.`title`, `n`.`extension`, `n`.`english` AS `value`, `n`.`added`, `n`.`likes`, `a`.`login`, `nl`.`ip` AS `active` '.
			'FROM `news` AS `n` '.
			'LEFT JOIN `tm_admins` AS `a` ON `n`.`admin_id` = `a`.`id` '.
			'LEFT JOIN `news_likes` AS `nl` ON `n`.`id` = `nl`.`news_id` AND `nl`.`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'"'.
			'WHERE `able` = 1 '.
			'ORDER BY `id` DESC '.
			'LIMIT 7'
		);
		
		$news = new stdClass();
		$news->others = new stdClass();
		if ($rows) {
			$i = 0;
			foreach($rows as $k => $v) {
				if ($i == 0) {
					$news->top = $v;
					$i = 1;
				}
				else {
					$news->others->$k = $v;
				}
			}
		}
		
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
}