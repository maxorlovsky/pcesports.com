<?php

class team
{
    public $team;
    
	public function __construct($params = array()) {
        
	}
    
    public function getHearthstone() {
        $rows = Db::fetchRows(
            'SELECT `id`, `name`, `avatar` FROM `users` '.
            'WHERE `id` = 490 ' //Frosten
        );
        
        $this->team = array(
            490 => array(
                'role' => 'Team Captain',
                'socials' => array(
                    'tw' => 'http://twitter.com/FrostenUniversa',
                    'tv' => 'http://www.twitch.tv/FrostenLive',
                ),
                'achievements' => 
                    '5x legend<br />'.
                    'World first legend in january 2015.<br />'.
                    '4th place in UniCon<br />'.
                    'ESL tournament 1st place and 2st place in king of the hill.'
            ),
        );
        
        if ($rows) {
            foreach($rows as $v) {
                $this->team[$v->id]['name'] = $v->name;
                $this->team[$v->id]['avatar'] = $v->avatar;
            }
        }
        
        include_once _cfg('pages').'/'.get_class().'/hearthstone.tpl';
    }
    
    public function getStaff() {
        $rows = Db::fetchRows(
            'SELECT `id`, `name`, `avatar` FROM `users` '.
            'WHERE `id` = 1 OR '. //max
            '`id` = 44 OR '. //serge
            '`id` = 112 OR '. //anya
            '`id` = 132 OR '. //arturs
            '`id` = 213 OR '. //aven
            '`id` = 126 OR '. //angel-ada
            '`id` = 491 ' //vanngarrd
        );
        
        $this->team = array(
            1 => array(
                'role' => 'Pentaclick Creator',
                'socials' => array(
                    'fb' => 'https://www.facebook.com/MaxOrlovsky',
                    'tw' => 'https://twitter.com/pentaclick',
                    'tv' => 'http://www.twitch.tv/pentaclick_tv',
                )
            ),
            44 => array(
                'role' => 'General Manager',
                'socials' => array(
                    'fb' => 'https://www.facebook.com/grobovsky',
                )
            ),
            112 => array(
                'role' => 'Creativity generator',
                'socials' => array(
                    'fb' => 'https://www.facebook.com/anya.orlovsky',
                )
            ),
            132 => array(
                'role' => 'Graphic designer',
                'socials' => array(
                    'fb' => 'https://www.facebook.com/artursofc',
                )
            ),
            126 => array(
                'role' => 'Community manager (VK)',
                'socials' => array(
                    'vk' => 'https://vk.com/victor.morozovvv',
                )
            ),
            213 => array(
                'role' => 'Community manager (FB)',
                'socials' => array(
                    'fb' => 'https://www.facebook.com/martin.petkevich',
                )
            ),
            491 => array(
                'role' => 'Smite tournament manager',
                'socials' => array(
                    'tw' => 'https://twitter.com/vanngarrd',
                )
            ),
        );
        
        if ($rows) {
            foreach($rows as $v) {
                $this->team[$v->id]['name'] = $v->name;
                $this->team[$v->id]['avatar'] = $v->avatar;
            }
        }
        
        include_once _cfg('pages').'/'.get_class().'/staff.tpl';
    }
    
    public function getAbout() {
        include_once _cfg('pages').'/'.get_class().'/index.tpl';
    }
	
	public function showTemplate() {
        if (isset($_GET['val2']) && $_GET['val2'] == 'hearthstone') {
			$this->getHearthstone();
		}
        else if (isset($_GET['val2']) && $_GET['val2'] == 'staff') {
			$this->getStaff();
		}
        else if (isset($_GET['val3']) && is_numeric($_GET['val3'])) {
			$this->getTournamentData($_GET['val3']);
		}
		else {
			$this->getAbout();
		}
	}
}