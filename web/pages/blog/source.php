<?php

class blog extends System
{
	public $news;
    public $pages;
	
	public function __construct($params = array()) {
		parent::__construct();
	}
	
	public function getNewsList() {
        $pagesData = array(
            'countPerPage'  => 5,
            'maxNumShow'    => 3,
            'tableName'     => 'blog',
            'pageNum'       => $_GET['val3'],
        );
        
        $this->pages = pages($pagesData);
        
		$this->news = Db::fetchRows('SELECT `n`.`id`, `n`.`title`, `n`.`extension`, `n`.`short_english` AS `value`, `n`.`added`, `n`.`likes`, `n`.`views`, `n`.`comments`, `a`.`login`, `nl`.`ip` AS `active` '.
			'FROM `blog` AS `n` '.
			'LEFT JOIN `tm_admins` AS `a` ON `n`.`admin_id` = `a`.`id` '.
			'LEFT JOIN `blog_likes` AS `nl` ON `n`.`id` = `nl`.`blog_id` AND `nl`.`ip` = "'.Db::escape(isset($_SERVER['HTTP_CF_CONNECTING_IP'])?$_SERVER['HTTP_CF_CONNECTING_IP']:$_SERVER['REMOTE_ADDR']).'"'.
			'WHERE `able` = 1 '.
			'ORDER BY `id` DESC '.
			'LIMIT '.(int)$this->pages->start.', '.(int)$this->pages->countPerPage
		);
		
		$rearangingNews = array();
        if ($this->news) {
            foreach($this->news as $v) {
                $v->value = $this->addImageResizer($v->value);
                $rearangingNews[] = $v;
            }
        }
		$this->news = (object)$rearangingNews;
		
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
	
	public function getArticle() {
        $row = Db::fetchRow('SELECT `id` FROM `blog` WHERE `able` = 1 AND `id` = '.(int)$_GET['val2'].' LIMIT 1');
        if (!$row) {
            go(_cfg('href').'/news');
        }
        
		if ($_SESSION['news_views'][$_GET['val2']] != 1) {
			Db::query('UPDATE `blog` SET '.
				'`views` = `views` + 1 '.
				'WHERE `id` = '.(int)$_GET['val2']
			);
			$_SESSION['news_views'][$_GET['val2']] = 1;
		}

		$this->news = Db::fetchRow('SELECT `n`.`id`, `n`.`title`, `n`.`extension`, `n`.`english` AS `value`, `n`.`added`, `n`.`likes`, `n`.`comments`, `n`.`views`, `a`.`login`, `nl`.`ip` AS `active` '.
			'FROM `blog` AS `n` '.
			'LEFT JOIN `tm_admins` AS `a` ON `n`.`admin_id` = `a`.`id` '.
			'LEFT JOIN `blog_likes` AS `nl` ON `n`.`id` = `nl`.`blog_id` AND `nl`.`ip` = "'.Db::escape(isset($_SERVER['HTTP_CF_CONNECTING_IP'])?$_SERVER['HTTP_CF_CONNECTING_IP']:$_SERVER['REMOTE_ADDR']).'"'.
			'WHERE `able` = 1 AND `n`.`id` = '.(int)$_GET['val2'].' '.
			'ORDER BY `id` DESC '.
			'LIMIT 1'
		);
		
		$this->news->value = $this->addImageResizer($this->news->value);
		
		include_once _cfg('pages').'/'.get_class().'/article.tpl';
	}
	
	public function showTemplate() {
		if (isset($_GET['val1']) && $_GET['val1'] == 'blog' && isset($_GET['val2']) && $_GET['val2'] != 'page') {
			$this->getArticle();
		}
		else {
			$this->getNewsList();
		}
	}
    
    public static function getSeo() {
        $news = Db::fetchRow('SELECT `id`, `title`, `extension`, `short_english` '.
			'FROM `blog` '.
			'WHERE `able` = 1 AND `id` = '.(int)$_GET['val2'].' '.
			'ORDER BY `id` DESC '.
			'LIMIT 1'
		);
        
        $seo = array(
            'title' => ($news->title?$news->title.' | ':null).'Blog',
            'ogImg' => ($news->extension?_cfg('imgu').'/news/small-'.$news->id.'.'.$news->extension:null),
            'ogDesc' => strip_tags($news->short_english),
        );
        
        return (object)$seo;
    }
	
	private function addImageResizer($text) {
		$matches = array();
		$urls = array();
		
		preg_match_all('/(<img[^>]+>)/i', $text, $matches);
		preg_match_all('/(src)=("[^"]*")/i',$text, $urls);
		
		foreach($matches[0] as $k => $v) {
			$style = array();
			preg_match_all('/(style)=("[^"]*")/i',$v, $style);
			$style = str_replace('display: block;', '', $style[2]);
			$style = 'style='.$style[0];
			
			$replace = '<a href='.$urls[2][$k].' '.$style.' class="zoom-in" onclick="return hs.expand(this)">'.$v.'<span class="fader"></span></a>';
			$text = str_replace($v, $replace, $text);
		}
		
		return $text;
	}
}