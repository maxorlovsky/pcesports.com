<?php

class team extends system
{
    public $team;
    
	public function __construct($params = array()) {
        
	}

    public function getLol() {
        $rows = Db::fetchRows(
            'SELECT `u`.`id`, `u`.`name`, `u`.`avatar`, `s`.`name` AS `summonerName`, `s`.`league`, `s`.`division` '.
            'FROM `users` AS `u` '.
            'LEFT JOIN `summoners` AS `s` ON (`u`.`id` = `s`.`user_id` AND `s`.`approved` = 1 AND `s`.`region` = "eune")'.
            'WHERE `u`.`id` = 863 OR '. //Knight
            '`u`.`id` = 862 OR '. //AnOldEnemy
            '`u`.`id` = 811 ' //Cake

        );
        
        $this->team = array(
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
                'role' => 'Support (Team Captain)',
                'socials' => array(
                    
                )
            ),
        );
        
        if ($rows) {
            foreach($rows as $v) {
                $this->team[$v->id]['name'] = $v->name;
                $this->team[$v->id]['avatar'] = $v->avatar;
                $this->team[$v->id]['summonerName'] = $v->summonerName;
                $this->team[$v->id]['league'] = $v->league;
                $this->team[$v->id]['division'] = $v->division;
            }
        }
        
        include_once _cfg('pages').'/'.get_class().'/leagueoflegends.tpl';
    }
    
    public function getHearthstone() {
        go(_cfg('href').'/team');
        exit();

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
            '`id` = 864 OR '. //ovy
            '`id` = 760 ' //Veipper
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
            ),
            760 => array(
                'role' => 'CS:GO Admin',
                'socials' => array(
                    'fb' => 'http://www.facebook.com/Veipper',
                    'tw' => 'http://twitter.com/Ve1pper',
                    'tv' => 'http://www.twitch.tv/ve1pper',
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