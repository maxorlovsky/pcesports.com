<?php

class news
{
	public $news;
	
	public function __construct($params = array()) {
		
	}
	
	public function getNewsList() {
		$this->news = Db::fetchRows('SELECT `n`.`id`, `n`.`title`, `n`.`extension`, `n`.`short_english` AS `value`, `n`.`added`, `n`.`likes`, `n`.`views`, `a`.`login`, `nl`.`ip` AS `active` '.
			'FROM `news` AS `n` '.
			'LEFT JOIN `tm_admins` AS `a` ON `n`.`admin_id` = `a`.`id` '.
			'LEFT JOIN `news_likes` AS `nl` ON `n`.`id` = `nl`.`news_id` AND `nl`.`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'"'.
			'WHERE `able` = 1 '.
			'ORDER BY `id` DESC '.
			'LIMIT 5'
		);
		
		$rearangingNews = array();
		foreach($this->news as $v) {
			$v->value = $this->addImageResizer($v->value);
			$rearangingNews[] = $v;
		}
		$this->news = (object)$rearangingNews;
		
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
	
	public function getArticle() {
		if ($_SESSION['news_views'][$_GET['val2']] != 1) {
			Db::query('UPDATE `news` SET '.
				'`views` = `views` + 1 '.
				'WHERE `id` = '.(int)$_GET['val2']
			);
			$_SESSION['news_views'][$_GET['val2']] = 1;
		}

		$this->news = Db::fetchRow('SELECT `n`.`id`, `n`.`title`, `n`.`extension`, `n`.`english` AS `value`, `n`.`added`, `n`.`likes`, `n`.`views`, `a`.`login`, `nl`.`ip` AS `active` '.
			'FROM `news` AS `n` '.
			'LEFT JOIN `tm_admins` AS `a` ON `n`.`admin_id` = `a`.`id` '.
			'LEFT JOIN `news_likes` AS `nl` ON `n`.`id` = `nl`.`news_id` AND `nl`.`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'"'.
			'WHERE `able` = 1 AND `n`.`id` = '.(int)$_GET['val2'].' '.
			'ORDER BY `id` DESC '.
			'LIMIT 1'
		);
		
		$this->news->value = $this->addImageResizer($this->news->value);
		
		include_once _cfg('pages').'/'.get_class().'/article.tpl';
	}
	
	public function showTemplate() {
		if (isset($_GET['val2'])) {
			$this->getArticle();
		}
		else {
			$this->getNewsList();
		}
	}
	
	private function addImageResizer($text) {
		$matches = array();
		$urls = array();
		
		preg_match_all('/(<img[^>]+>)/i', $text, $matches);
		preg_match_all('/(src)=("[^"]*")/i',$text, $urls);
		
		foreach($matches[0] as $k => $v) {
			$replace = '<a href='.$urls[2][$k].' class="zoom-in" onclick="return hs.expand(this)">'.$v.'<span class="fader"></span></a>';
			$text = str_replace($v, $replace, $text);
		}
		
		return $text;
	}
}