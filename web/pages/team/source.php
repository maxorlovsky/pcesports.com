<?php

class team
{
    public $team;
    
	public function __construct($params = array()) {
        
	}

    public function getLol() {
        $rows = Db::fetchRows(
            'SELECT `id`, `name`, `avatar` FROM `users` '.
            'WHERE `id` = 760 OR '. //Veipper
            ' `id` = 862 OR '. //AnOldEnemy
            ' `id` = 811 OR '. //Cake
            ' `id` = 863 ' //Knight
        );
        
        $this->team = array(
            760 => array(
                'role' => 'Team Captain (Mid laner)',
                'socials' => array(
                    'fb' => 'http://www.facebook.com/Veipper',
                    'tw' => 'http://twitter.com/Ve1pper',
                    'tv' => 'http://www.twitch.tv/ve1pper',
                )
            ),
            862 => array(
                'role' => 'Top laner',
                'socials' => array(
                    'fb' => 'https://www.facebook.com/mihael.krpan',
                )
            ),
            811 => array(
                'role' => 'AD Carry',
                'socials' => array(
                    
                )
            ),
            863 => array(
                'role' => 'Support',
                'socials' => array(
                    
                )
            ),
        );
        
        if ($rows) {
            foreach($rows as $v) {
                $this->team[$v->id]['name'] = $v->name;
                $this->team[$v->id]['avatar'] = $v->avatar;
            }
        }
        
        include_once _cfg('pages').'/'.get_class().'/leagueoflegends.tpl';
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
            '`id` = 864 ' //ovy
        );
        
        $this->team = array(
            1 => array(
                'role' => 'Pentaclick Creator',
                'socials' => array(
                    'fb' => 'https://www.facebook.com/MaxOrlovsky',
                    'tw' => 'https://twitter.com/MaxOrlovsky',
                    'tv' => 'http://www.twitch.tv/pentaclick_tv',
                )
            ),
            44 => array(
                'role' => 'Godly Manager',
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
            864 => array(
                'role' => 'LoL Admin & Coach',
                'socials' => array(
                    'fb' => 'https://www.facebook.com/ovidijus.parsiunas',
                )
            )
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
        if (isset($_GET['val2']) && $_GET['val2'] == 'leagueoflegends') {
            $this->getLol();
        }
        else if (isset($_GET['val2']) && $_GET['val2'] == 'hearthstone') {
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