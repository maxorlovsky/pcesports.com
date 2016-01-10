<?php

class teams extends System
{
    public $teams;
    public $pages;
    
	public function __construct($params = array()) {
		parent::__construct();
        
        $pagesData = array(
            'countPerPage'  => 20,
            'maxNumShow'    => 3,
            'tableName'     => 'teams',
            'pageNum'       => $_GET['val3'],
        );
        
        $this->pages = pages($pagesData);
        
        $this->teams = Db::fetchRows(
            'SELECT `t`.*, COUNT(`tu`.`user_id`) AS `members` '.
            'FROM `teams` AS `t` '.
            'LEFT JOIN `teams2users` AS `tu` ON `t`.`id` = `tu`.`team_id` '.
            'GROUP BY `t`.`id` '.
            'ORDER BY `registration_date` DESC '.
            'LIMIT '.(int)$this->pages->start.', '.(int)$this->pages->countPerPage
        );
	}
    
	public static function getSeo() {
        $u = new self();
        
        $seo = new stdClass();
        $seo->title = 'Teams List';

		return $seo;
	}
	
	public function showTemplate() {
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
    
    
}