<?php

class home extends System
{
	public $news;
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
			array(_cfg('href').'/leagueoflegends/eune', _cfg('img').'/poster-eune.jpg'),
            array(_cfg('href').'/hearthstone', _cfg('img').'/poster-hl.jpg'),
		);
        
        /*$this->streams = Db::fetchRows(
            'SELECT `id`, `name`, `display_name`, IF(`online` >= '.(time()-360).', 1, 0) AS `onlineStatus`, `viewers` '.
            'FROM `streams` '.
            'WHERE `online` != 0 AND '.
            '`approved` = 1 AND '.
            '`featured` = 1 '.
            'ORDER BY `onlineStatus` DESC, `featured` DESC, `viewers` DESC '
		);*/
        $this->streams = array();
        
        $rows = Db::fetchRows('SELECT * FROM `tournaments` WHERE '.
            '(`game` = "lol" AND `name` = '.(int)$this->data->settings['lol-current-number-euw'].' AND `server` = "euw") OR '.
            '(`game` = "lol" AND `name` = '.(int)$this->data->settings['lol-current-number-eune'].' AND `server` = "eune") OR '.
            '(`game` = "smite" AND `name` = '.(int)$this->data->settings['smite-current-number-na'].' AND `server` = "na") OR '.
            '(`game` = "smite" AND `name` = '.(int)$this->data->settings['smite-current-number-eu'].' AND `server` = "eu") OR '.
            '(`game` = "hs" AND `name` = '.(int)$this->data->settings['hs-current-number-s1'].') '.
            'ORDER BY `id` DESC '
        );
        
        if ($rows) {
            foreach($rows as $v) {
                $startTime = strtotime($v->dates_start.' '.$v->time);
                $regTime = strtotime($v->dates_registration.' '.$v->time);
                $time = $regTime;
                
                if ($v->server) {
                    $checkInStatus = $this->data->settings['tournament-checkin-'.$v->game.'-'.$v->server];
                    $checkLive = $this->data->settings['tournament-start-'.$v->game.'-'.$v->server];
                    $checkReg = $this->data->settings['tournament-reg-'.$v->game.'-'.$v->server];
                }
                else {
                    $checkInStatus = $this->data->settings['tournament-checkin-'.$v->game];
                    $checkLive = $this->data->settings['tournament-start-'.$v->game];
                    $checkReg = $this->data->settings['tournament-reg-'.$v->game];
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
                }
                else if ($v->status == 'Ended') {
                    $v->status = t('ended');
                }
                else {
                    $v->status = t('active');
                }
                
                if ($v->game == 'lol') {
                    $link = 'leagueoflegends/'.$v->server;
                }
                else if ($v->game == 'hs') {
                    $link = 'hearthstone';
                }
                else if ($v->game == 'smite') {
                    $link = 'smite/'.$v->server;
                }
                
                $this->tournamentData[] = array(
                    'id'	=> $v->name,
                    'server'=> $v->server,
                    'game'  => $v->game,
                    'name' 	=> $v->name,
                    'status'=> $v->status,
                    'max_num'=> $v->max_num,
                    'prize' => $v->prize,
                    'dates_start'=> $v->dates_start,
                    'link'  => $link,
                );
            }
        }
		
		$this->blog = Db::fetchRow('SELECT `n`.`id`, `n`.`title`, `n`.`extension`, `n`.`short_english` AS `value`, `n`.`added`, `n`.`likes`, `n`.`comments`, `n`.`views`, `a`.`login`, `nl`.`ip` AS `active` '.
			'FROM `blog` AS `n` '.
			'LEFT JOIN `tm_admins` AS `a` ON `n`.`admin_id` = `a`.`id` '.
			'LEFT JOIN `blog_likes` AS `nl` ON `n`.`id` = `nl`.`blog_id` AND `nl`.`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'"'.
			'WHERE `able` = 1 '.
			'ORDER BY `id` DESC '.
			'LIMIT 1'
		);
	}
	
	public function showTemplate() {
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
}