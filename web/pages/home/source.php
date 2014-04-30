<?php

class home
{
	public $news;
	
	function __construct($params = array()) {
		
		$slider = array(
			array('#', _cfg('img').'/poster-lol.jpg'),
			array('#', _cfg('img').'/poster-hs.png'),
		);
		
		$rows = Db::fetchRows('SELECT `id`, `title`, `extension`, `english` AS `value`, `added` FROM `news` '.
			//'WHERE `able` = 1 '.
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