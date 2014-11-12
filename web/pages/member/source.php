<?php

class member extends System
{
    public $member;
    
	public function __construct($params = array()) {
		parent::__construct();
        
        if (isset($_GET['val2']) && $_GET['val2'] && !$this->member) {
            $row = Db::fetchRow(
                'SELECT `id`, `name`, `avatar`, `registration_date` '.
                'FROM `users` '.
                'WHERE `id` = "'.(int)$_GET['val2'].'" OR '.
                '`name` = "'.Db::escape($_GET['val2']).'" '.
                'LIMIT 1'
            );
            
            if ($row) {
                $rows = Db::fetchRows(
                    'SELECT `region`, `summoner_id`, `name` FROM `summoners` '.
                    'WHERE `user_id` = '.(int)$row->id.' AND '.
                    '`approved` = 1 '
                );
                $row->summoners = $rows;
                
                $rows = Db::fetchRows(
                    'SELECT `game`, `server`, `tournament_id`, `timestamp`, `name`, `contact_info`, `seed_number`, `place`, `checked_in` '.
                    'FROM `participants` '.
                    'WHERE `user_id` = '.(int)$row->id.' '//.AND
                    //'`approved` = 1 AND '.
                    //'`deleted` = 0 '
                );
                $row->tournaments = $rows;
                
                $this->member = $row;
            }
        }
	}
    
	public function getMember() {
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
	
	public static function getSeo() {
        $u = new self();
        
        $seo = new stdClass();
        if ($u->member) {
            $seo->title = $u->member->name.' | User Profile';
        }
        else {
            $seo->title = 'User Not Found';
        }

		return $seo;
	}
	
	public function showTemplate() {
		if (!$this->member) {
			include_once  _cfg('pages').'/404/error.tpl';
		}
		else {
            $this->getMember();
		}
	}
    
    
}