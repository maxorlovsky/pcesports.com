<?php

class home extends System
{
	public $blog;
	public $slider;
    public $streams;
    public $tournamentData;
	
	public function __construct($params = array()) {
        parent::__construct();
		
		$this->slider = array(
            //array('http://www.pcesports.com/en/blog/29', _cfg('img').'/poster-grandfinals.jpg'),
			//array('http://www.pcesports.com/en/blog/27', _cfg('img').'/poster-vacation.jpg'),
		);
        
        $rows = Db::fetchRows('SELECT * FROM `tournaments` WHERE '.
            '(`game` = "lol" AND `name` = '.(int)$this->data->settings['lol-current-number-euw'].' AND `server` = "euw") OR '.
            '(`game` = "lol" AND `name` = '.(int)$this->data->settings['lol-current-number-eune'].' AND `server` = "eune") OR '.
            '(`game` = "hs" AND `name` = '.(int)$this->data->settings['hs-current-number'].' AND `server` = "'.Db::escape($this->data->settings['tournament-season-hs']).'") '.
            'ORDER BY STR_TO_DATE(`dates_start`, "%d.%m.%Y") DESC '
        );

        //Variable to check if there is at least one started event
        $eventStreams = 0;

        if ($rows) {
            $i = 0;
            foreach($rows as $v) {
                if ($v->status == 'upcoming') {
                    $time = strtotime($v->dates_registration.' '.$v->time);
                }
                else {
                    $time = strtotime($v->dates_start.' '.$v->time);
                }

                $name = '';
                if ($v->game == 'lol') {
                    $link = 'leagueoflegends/'.$v->server.'/'.$v->name;
                    if ($v->server == 'eune') {
                        $name = 'Europe East #'.$v->name;
                    }
                    else {
                        $name = 'Europe West #'.$v->name;
                    }
                }
                else if ($v->game == 'hs') {
                    $link = 'hearthstone/'.$v->server.'/'.$v->name;
                    $name = 'Hearthstone League Season 2 Finals';
                }

                $this->tournamentData[$v->game.''.$v->server] = array(
                    'order'     => ($v->game == 'lol' ? 2 : 1),
                    'id'	    => $v->name,
                    'name'      => $name,
                    'status'    => str_replace('_', ' ', $v->status),
                    'max_num'   => $v->max_num,
                    'prize'     => $v->prize,
                    'time'      => $time,
                    'link'      => $link,
                );
                asort($this->tournamentData);
            }
            
            $this->streams = Db::fetchRows('SELECT `id`, `name`, `display_name`, `featured`, `game`, `viewers`, `name` AS `link` '.
                'FROM `streams` '.
                'WHERE IF(`online` >= '.(time()-360).', 1, 0) = 1 '.
                'AND `approved` = 1 '.
                ($eventStreams != 1?'AND `featured` = 1 ':'').
                'GROUP BY `name` '.
                'ORDER BY `viewers` DESC '
            );
        }
		
		$this->blog = Db::fetchRows('SELECT `n`.`id`, `n`.`title`, `n`.`extension`, `n`.`short_english` AS `value`, `n`.`added`, `n`.`likes`, `n`.`comments`, `n`.`views`, `a`.`login`, `nl`.`ip` AS `active` '.
			'FROM `blog` AS `n` '.
			'LEFT JOIN `tm_admins` AS `a` ON `n`.`admin_id` = `a`.`id` '.
			'LEFT JOIN `blog_likes` AS `nl` ON `n`.`id` = `nl`.`blog_id` AND `nl`.`ip` = "'.Db::escape(isset($_SERVER['HTTP_CF_CONNECTING_IP'])?$_SERVER['HTTP_CF_CONNECTING_IP']:$_SERVER['REMOTE_ADDR']).'"'.
			'WHERE `able` = 1 '.
			'ORDER BY `id` DESC '.
			'LIMIT 3'
		);
	}
	
	public function showTemplate() {
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
}