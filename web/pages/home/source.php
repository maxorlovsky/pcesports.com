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
			//array(_cfg('href').'/leagueoflegends/eune', _cfg('img').'/poster-eune.jpg'),
            //array(_cfg('href').'/hearthstone', _cfg('img').'/poster-hl.jpg'),
		);

        $where = '';
        if ($this->data->settings['tournament-start-hs-s1'] == 1) {
            $where .= '`game` = "hs" AND `tournament_id` = '.(int)$this->data->settings['hs-current-number-s1'].' ';
        }
        if ($this->data->settings['tournament-start-lol-euw'] == 1 || $this->data->settings['tournament-start-lol-eune'] == 1) {
            $where .= '`game` = "lol" AND (`tournament_id` = '.(int)$this->data->settings['lol-current-number-euw'].' OR `tournament_id` = '.(int)$this->data->settings['lol-current-number-eune'].') ';
        }
        if ($this->data->settings['tournament-start-smite-na'] == 1 || $this->data->settings['tournament-start-smite-eu'] == 1) {
            $where .= '`game` = "smite" AND (`tournament_id` = '.(int)$this->data->settings['smite-current-number-na'].' OR `tournament_id` = '.(int)$this->data->settings['smite-current-number-eu'].') ';
        }

        if ($where) {
            $eventStreams = Db::fetchRows(
                'SELECT `id`, `name`, `display_name`, `game`, `viewers`, IF(`online` >= '.(time()-360).', 1, 0) AS `onlineStatus`, 1 AS `event`, `name` AS `link` '.
                'FROM `streams_events` '.
                'WHERE IF(`online` >= '.(time()-360).', 1, 0) = 1 AND '.
                $where.
                'ORDER BY `viewers` DESC '
            );
        }
        
        $this->streams = Db::fetchRows('SELECT `id`, `name`, `display_name`, `featured`, `game`, `viewers`, `name` AS `link` '.
            'FROM `streams` '.
            'WHERE IF(`online` >= '.(time()-360).', 1, 0) = 1 AND '.
            '`approved` = 1 AND '.
            '`featured` = 1 '.
            'ORDER BY `viewers` DESC '
        );

        if ($eventStreams && $this->streams) {
            $this->streams = (object)array_merge((array)$eventStreams, (array)$this->streams);
        }
        else if ($eventStreams) {
            $this->streams = (object)$eventStreams;
        }
        
        $rows = Db::fetchRows('SELECT * FROM `tournaments` WHERE '.
            '(`game` = "lol" AND `name` = '.(int)$this->data->settings['lol-current-number-euw'].' AND `server` = "euw") OR '.
            '(`game` = "lol" AND `name` = '.(int)$this->data->settings['lol-current-number-eune'].' AND `server` = "eune") OR '.
            '(`game` = "smite" AND `name` = '.(int)$this->data->settings['smite-current-number-na'].' AND `server` = "na") OR '.
            '(`game` = "smite" AND `name` = '.(int)$this->data->settings['smite-current-number-eu'].' AND `server` = "eu") OR '.
            '(`game` = "hs" AND `name` = '.(int)$this->data->settings['hs-current-number-s1'].') '
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
                    $time = $startTime;
                }
                else if (strtolower($v->status) == 'start') {
                    $v->status = t('active');
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
                    $v->priority = 2;
                }
                else if ($v->game == 'hs') {
                    $link = 'hearthstone/'.$v->server.'/'.$v->name;
                    $name = 'Hearthstone League';
                    $v->priority = 3;
                }
                else if ($v->game == 'smite') {
                    $link = 'smite/'.$v->server.'/'.$v->name;
                    if ($v->server == 'eu') {
                        $name = 'Europe';
                    }
                    else {
                        $name = 'North America';
                    }
                    $additionalWhere = '`approved` = 1 AND ';
                    $v->priority = 1;
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
                    'priority'  => $v->priority,
                    'id'	    => $v->name,
                    'name'      => $name,
                    'status'    => $v->status,
                    'max_num'   => $v->max_num,
                    'prize'     => $v->prize,
                    'time'      => $time,
                    'link'      => $link,
                    'teams'     => $row->value,
                );
            }
            asort($this->tournamentData);
        }
		
		$this->blog = Db::fetchRow('SELECT `n`.`id`, `n`.`title`, `n`.`extension`, `n`.`short_english` AS `value`, `n`.`added`, `n`.`likes`, `n`.`comments`, `n`.`views`, `a`.`login`, `nl`.`ip` AS `active` '.
			'FROM `blog` AS `n` '.
			'LEFT JOIN `tm_admins` AS `a` ON `n`.`admin_id` = `a`.`id` '.
			'LEFT JOIN `blog_likes` AS `nl` ON `n`.`id` = `nl`.`blog_id` AND `nl`.`ip` = "'.Db::escape($_SERVER['REMOTE_ADDR']).'"'.
			'WHERE `able` = 1 '.
			'ORDER BY `id` DESC '.
			'LIMIT 1'
		);
        
        $additionalSelect = '';
        $additionalSql = '';
        if ($this->logged_in) {
            $additionalSelect .= ', `bv`.`direction`';
            $additionalSql .= 'LEFT JOIN `boards_votes` AS `bv` ON `b`.`id` = `bv`.`board_id` AND `bv`.`user_id` = '.(int)$this->data->user->id.' ';
        }
        
        $this->boards = Db::fetchRows('SELECT `b`.`id`, `b`.`title`, `b`.`category`, `b`.`added`, `b`.`votes`, `b`.`comments`, `b`.`user_id`, `b`.`edited`, `b`.`status`, `u`.`name`, `u`.`avatar` '.$additionalSelect.
			'FROM `boards` AS `b` '.
            $additionalSql.
            'LEFT JOIN `users` AS `u` ON `b`.`user_id` = `u`.`id` '.
			'ORDER BY `activity` DESC '.
			'LIMIT 3 '
		);
        
        $currDate = new DateTime();
        
        foreach($this->boards as &$v) {
            $dbDate = new DateTime($v->added);
            $v->interval = $this->getAboutTime($currDate->diff($dbDate));
        }
        unset($v);
	}
	
	public function showTemplate() {
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
}