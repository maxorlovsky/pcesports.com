<?php

class news
{
	public $news;
	
	function __construct($params = array()) {
		if (isset($_GET['val2'])) {
			$this->getArticle();
		}
		else {
			$this->getNewsList();
		}		
	}
	
	public function getNewsList() {
		$news = Db::fetchRows('SELECT `n`.`id`, `n`.`title`, `n`.`extension`, `n`.`english` AS `value`, `n`.`added`, `n`.`likes`, `a`.`login`, `nl`.`ip` AS `active` '.
			'FROM `news` AS `n` '.
			'LEFT JOIN `tm_admins` AS `a` ON `n`.`admin_id` = `a`.`id` '.
			'LEFT JOIN `news_likes` AS `nl` ON `n`.`id` = `nl`.`news_id` AND `nl`.`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'"'.
			'WHERE `able` = 1 '.
			'ORDER BY `id` DESC '.
			'LIMIT 10'
		);
		
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
	
	public function getArticle() {
		$row = Db::fetchRow('SELECT `n`.`id`, `n`.`title`, `n`.`extension`, `n`.`english` AS `value`, `n`.`added`, `n`.`likes`, `a`.`login`, `nl`.`ip` AS `active` '.
			'FROM `news` AS `n` '.
			'LEFT JOIN `tm_admins` AS `a` ON `n`.`admin_id` = `a`.`id` '.
			'LEFT JOIN `news_likes` AS `nl` ON `n`.`id` = `nl`.`news_id` AND `nl`.`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'"'.
			'WHERE `able` = 1 AND `n`.`id` = '.(int)$_GET['val2'].' '.
			'ORDER BY `id` DESC '.
			'LIMIT 1'
		);
		
		include_once _cfg('pages').'/'.get_class().'/article.tpl';
	}
}