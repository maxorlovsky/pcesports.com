<?php

class boards extends System
{
	public $boards;
    public $page;
    public $pages;
	
	public function __construct($params = array()) {
		parent::__construct();
	}
	
	public function getBoardsList() {
        $pagesData = array(
            'countPerPage'  => 10,
            'maxNumShow'    => 5,
            'tableName'     => 'boards',
            'pageNum'       => $_GET['val3'],
        );
        
        $this->pages = pages($pagesData);
        
        $additionalSelect = '';
        $additionalSql = '';
        if ($this->logged_in) {
            $additionalSelect .= ', `bv`.`direction`';
            $additionalSql .= 'LEFT JOIN `boards_votes` AS `bv` ON `b`.`id` = `bv`.`board_id` AND `bv`.`user_id` = '.(int)$this->data->user->id.' ';
        }
        
		$this->boards = Db::fetchRows('SELECT `b`.`id`, `b`.`title`, `b`.`category`, `b`.`added`, `b`.`votes`, `b`.`comments`, `b`.`user_id`, `u`.`name`, `u`.`avatar` '.$additionalSelect.
			'FROM `boards` AS `b` '.
            $additionalSql.
            'LEFT JOIN `users` AS `u` ON `b`.`user_id` = `u`.`id` '.
			'WHERE (`status` = 0 OR `status` = 2) '.
			'ORDER BY `activity` DESC '.
			'LIMIT '.(int)$this->pages->start.', '.(int)$this->pages->countPerPage
		);
        
        $currDate = new DateTime();
        
        foreach($this->boards as &$v) {
            $dbDate = new DateTime($v->added);
            $v->interval = $this->getAboutTime($currDate->diff($dbDate));
        }
        unset($v);
        
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
	
	public function getBoard() {
        $additionalSelect = '';
        $additionalSql = '';
        if ($this->logged_in) {
            $additionalSelect .= ', `bv`.`direction`';
            $additionalSql .= 'LEFT JOIN `boards_votes` AS `bv` ON `b`.`id` = `bv`.`board_id` AND `bv`.`user_id` = '.(int)$this->data->user->id.' ';
        }
        
        $row = Db::fetchRow('SELECT `b`.`id`, `b`.`title`, `b`.`text`, `b`.`category`, `b`.`added`, `b`.`votes`, `b`.`comments`, `b`.`user_id`, `u`.`name`, `u`.`avatar` '.$additionalSelect.
			'FROM `boards` AS `b` '.
			$additionalSql.
            'LEFT JOIN `users` AS `u` ON `b`.`user_id` = `u`.`id` '.
			'WHERE (`status` = 0 OR `status` = 2) '.
            'AND `b`.`id` = '.(int)$_GET['val2'].' '.
			'LIMIT 1'
		);
        if (!$row) {
            go(_cfg('href').'/boards');
        }
        
        $currDate = new DateTime();
        $dbDate = new DateTime($row->added);
        $row->interval = $this->getAboutTime($currDate->diff($dbDate));
        
		$this->comments = Db::fetchRows('SELECT `bc`.`id`, `bc`.`answer_to_id`, `bc`.`text`, `bc`.`added`, `bc`.`votes`, `u`.`name`, `u`.`avatar` '.
			'FROM `boards_comments` AS `bc` '.
            'LEFT JOIN `users` AS `u` ON `bc`.`user_id` = `u`.`id` '.
			'WHERE `bc`.`board_id` = '.(int)$_GET['val2'].' '.
            'AND (`bc`.`status` = 0 OR `bc`.`status` = 2) '.
			'ORDER BY `id` DESC '
		);
        
        if ($this->comments) {
            foreach($this->comments as &$v) {
                $dbDate = new DateTime($v->added);
                $v->interval = $this->getAboutTime($currDate->diff($dbDate));
                $v->text = $this->parseText($v->text);
            }
            unset($v);
        }
		
		include_once _cfg('pages').'/'.get_class().'/board.tpl';
	}
    
    public function submitPage() {
        include_once _cfg('pages').'/'.get_class().'/submit.tpl';
    }
	
	public function showTemplate() {
        
        if (isset($_GET['val2']) && $_GET['val2'] == 'submit' ) { //&& $this->logged_in
			$this->submitPage();
		}
		else if (isset($_GET['val1']) && $_GET['val1'] == 'boards' && isset($_GET['val2']) && $_GET['val2'] != 'page') {
			$this->getBoard();
		}
		else {
			$this->getBoardsList();
		}
	}
    
    public static function getSeo() {
        $board = Db::fetchRow('SELECT `id`, `title` '.
			'FROM `boards` '.
			'WHERE (`status` = 0 OR `status` = 2) AND `id` = '.(int)$_GET['val2'].' '.
			'ORDER BY `id` DESC '.
			'LIMIT 1'
		);
        
        $seo = array(
            'title' => ($board->title?strip_tags($board->title).' | ':null).'Boards',
            'ogDesc' => strip_tags($board->title),
        );
        
        return (object)$seo;
    }
}