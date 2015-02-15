<?php

class tournaments extends System
{
	public $tournamentData;
	public $teamsPlaces;
	
	public function __construct($params = array()) {
		parent::__construct();
	}
	
	public function getTournamentList() {
		$rows = Db::fetchRows('SELECT * FROM `tournaments` WHERE '.
            '(`game` = "lol" AND `name` = '.(int)$this->data->settings['lol-current-number-euw'].' AND `server` = "euw") OR '.
            '(`game` = "lol" AND `name` = '.(int)$this->data->settings['lol-current-number-eune'].' AND `server` = "eune") OR '.
            '(`game` = "smite" AND `name` = '.(int)$this->data->settings['smite-current-number-na'].' AND `server` = "na") OR '.
            '(`game` = "smite" AND `name` = '.(int)$this->data->settings['smite-current-number-eu'].' AND `server` = "eu") OR '.
            '(`game` = "hs" AND `name` = '.(int)$this->data->settings['hs-current-number'].') '.
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
		
        if ($this->tournamentData) {
            $rows = Db::fetchRows('SELECT `tournament_id`, COUNT(`tournament_id`) AS `value`'.
                'FROM `participants` '.
                'WHERE `game` = "lol" AND '.
                '`server` = "'.Db::escape($this->server).'" AND ' .
                '`approved` = 1 AND '.
                '`deleted` = 0 '.
                'GROUP BY `tournament_id` '.
                'ORDER BY `id` DESC'
            );
            if ($rows) {
                foreach($rows as $v) {
                    if ($this->tournamentData[$v->tournament_id]) {
                        $this->tournamentData[$v->tournament_id]['teamsCount'] = $v->value;
                    }
                }
            }
        }
		
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
	
	public static function getSeo() {
		$seo = new stdClass();
		$seo->title = 'Pentaclick tournaments list';
        
		return $seo;
	}
	
	public function showTemplate() {
		$this->getTournamentList();
	}
}