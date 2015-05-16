<?php

class member extends System
{
    public $member;
    public $fullGameName = array();
    public $places = array();
    
	public function __construct($params = array()) {
		parent::__construct();
        
        $this->fullGameName = array(
            'lol' => 'leagueoflegends',
            'hs' => 'hearthstone',
            'smite' => 'smite',
        );
        $this->places = array(
            1 => 'gold',
            2 => 'silver',
            3 => 'bronze',
        );
        
        if (isset($_GET['val2']) && $_GET['val2'] && !$this->member) {
            $row = Db::fetchRow(
                'SELECT `id`, `name`, `avatar`, `registration_date`, `battletag` '.
                'FROM `users` '.
                'WHERE `name` = "'.Db::escape($_GET['val2']).'" '.
                'LIMIT 1'
            );
            
            if ($row) {
                $this->member = $row;
            }
        }
	}
    
	public function getMember() {
        //Summoners list
        $rows = Db::fetchRows(
            'SELECT `region`, `summoner_id`, `name`, `league`, `division` FROM `summoners` '.
            'WHERE `user_id` = '.(int)$this->member->id.' AND '.
            '`approved` = 1 '
        );
        
        if ($rows) {
            $this->member->summoners = $rows;
            
            foreach($this->member->summoners as &$v) {
                foreach(_cfg('lolRegions') as $k => $lr) {
                    if ($k == $v->region) {
                        $v->regionName = $lr;
                    }
                }
            }
            unset($v);
        }
        else {
            $this->member->summoners = array();
        }
        
        //Tournaments list
        $rows = Db::fetchRows(
            'SELECT `game`, `server`, `tournament_id`, `timestamp`, `name`, `contact_info`, `seed_number`, `place`, `checked_in` '.
            'FROM `participants` '.
            'WHERE `user_id` = '.(int)$this->member->id.' '//.AND
            //'`approved` = 1 AND '.
            //'`deleted` = 0 '
        );
        if ($rows) {
            $this->member->tournaments = $rows;
        }
        else {
            $this->member->tournaments = array();
        }

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