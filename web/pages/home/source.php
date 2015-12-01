<?php

class home extends System
{
	public $blog;
	public $slider;
    public $streams;
    public $timezone = 0;
    public $tournamentData;
	
	public function __construct($params = array()) {
        parent::__construct();
    
        if (isset($this->data->user->timezone)) {
            $this->timezone = $this->data->user->timezone;
        }
		
		$this->slider = array(
            //array('http://www.pcesports.com/en/blog/29', _cfg('img').'/poster-grandfinals.jpg'),
			//array('http://www.pcesports.com/en/blog/27', _cfg('img').'/poster-vacation.jpg'),
		);
        
        $this->streams = Db::fetchRows('SELECT `id`, `name`, `display_name`, `featured`, `game`, `viewers`, `name` AS `link` '.
            'FROM `streams` '.
            'WHERE IF(`online` >= '.(time()-360).', 1, 0) = 1 AND '.
            '`approved` = 1 AND '.
            '`featured` = 1 '.
            'ORDER BY `viewers` DESC '
        );

        $rows = Db::fetchRows('SELECT * FROM `tournaments` WHERE '.
            '(`game` = "lol" AND `name` = '.(int)$this->data->settings['lol-current-number-euw'].' AND `server` = "euw") OR '.
            '(`game` = "lol" AND `name` = '.(int)$this->data->settings['lol-current-number-eune'].' AND `server` = "eune") OR '.
            '(`game` = "hs" AND `name` = '.(int)$this->data->settings['hs-current-number'].' AND `server` = "'.Db::escape($this->data->settings['tournament-season-hs']).'") '.
            'ORDER BY STR_TO_DATE(`dates_start`, "%d.%m.%Y") DESC '
        );
        
        if ($rows) {
            $i = 0;
            foreach($rows as $v) {
                $startTime = strtotime($v->dates_start.' '.$v->time);
                $regTime = strtotime($v->dates_registration.' '.$v->time);
                $time = $regTime;

                if ($v->game == 'hs') {
                    $checkInStatus = $this->data->settings['tournament-checkin-'.$v->game];
                    $checkLive = $this->data->settings['tournament-start-'.$v->game];
                    $checkReg = $this->data->settings['tournament-reg-'.$v->game];
                }
                else {
                    $checkInStatus = $this->data->settings['tournament-checkin-'.$v->game.'-'.$v->server];
                    $checkLive = $this->data->settings['tournament-start-'.$v->game.'-'.$v->server];
                    $checkReg = $this->data->settings['tournament-reg-'.$v->game.'-'.$v->server];
                    if ($i === 0) {
                        $lolPriority = 1;
                    }
                }

                if ($checkInStatus == 1) {
                    $v->status = t('check_in');
                    $time = $startTime;
                }
                else if ($checkLive == 1) {
                    $v->status = t('live');
                    $time = $startTime;
                }
                else if ($checkReg == 1) {
                    $v->status = t('registration');
                    $time = $startTime;
                }
                else if (strtolower($v->status) == 'start') {
                    $v->status = t('upcoming');
                }
                
                $additionalWhere = '';
                $name = '';
                if ($v->game == 'lol') {
                    $link = 'leagueoflegends/'.$v->server.'/'.$v->name;
                    if ($v->server == 'eune') {
                        $name = 'Europe East';
                    }
                    else {
                        $name = 'Europe West';
                    }
                    $additionalWhere = '`approved` = 1 AND ';
                }
                else if ($v->game == 'hs') {
                    $link = 'hearthstone/'.$v->server.'/'.$v->name;
                    $name = 'Hearthstone League Season 2';
                    $additionalWhere = '`approved` = 1 AND ';
                }

                //Fetching number of players for each tournament
                $row = Db::fetchRow('SELECT COUNT(`tournament_id`) AS `value`'.
                    'FROM `participants` '.
                    'WHERE `game` = "'.Db::escape($v->game).'" AND '.
                    '`server` = "'.Db::escape($v->server).'" AND ' .
                    $additionalWhere.
                    '`deleted` = 0 AND '.
                    '`tournament_id` = "'.Db::escape($v->name).'" '.
                    'LIMIT 1 '
                );

                $this->tournamentData[$v->game.''.$v->server] = array(
                    'order'     => ($lolPriority==1&&$v->game=='lol'?1:2),
                    'id'	    => $v->name,
                    'name'      => $name,
                    'status'    => $v->status,
                    'max_num'   => $v->max_num,
                    'prize'     => $v->prize,
                    'time'      => $time,
                    'link'      => $link,
                    'teams'     => $row->value,
                );
                asort($this->tournamentData);
            }
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