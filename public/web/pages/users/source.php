<?php

class users extends System
{
    public $users;
    public $pages;
    
	public function __construct($params = array()) {
		parent::__construct();
        
        $pagesData = array(
            'countPerPage'  => 20,
            'maxNumShow'    => 3,
            'tableName'     => 'users',
            'pageNum'       => (isset($_GET['val3']) ? $_GET['val3'] : 1),
        );
        
        $this->pages = pages($pagesData);
        $this->pages->html = str_replace('/users/', 'legacy/users/', $this->pages->html);
        
        $this->users = Db::fetchRows(
            'SELECT `u`.*, `s`.`name` AS `summoner`, `s`.`division`, `s`.`league`, `s`.`approved` AS `summonerApproved` '.
            'FROM `users` AS `u` '.
            'LEFT JOIN `summoners` AS `s` ON `u`.`id` = `s`.`user_id` AND `s`.`approved` = 1 '.
            'GROUP BY `u`.`id` '.
            'ORDER BY `registration_date` DESC '.
            'LIMIT '.(int)$this->pages->start.', '.(int)$this->pages->countPerPage
        );
	}
    
	public function getMember() {
		include_once _cfg('pages').'/'.get_class().'/index.html';
	}
	
	public static function getSeo() {
        $u = new self();
        
        $seo = new stdClass();
        $seo->title = 'Users List';

		return $seo;
	}
	
	public function showTemplate() {
		include_once _cfg('pages').'/'.get_class().'/index.html';
	}
    
    
}