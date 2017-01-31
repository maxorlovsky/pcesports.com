<?php

class hearthstone extends System
{
	public $teamsCount;
	public $currentTournament;
	public $teamsPlaces;
	public $participants;
    public $pickedTournament;
    public $groups;
    public $server;
    public $winners;
    public $heroes;
    public $maxHeroes = 4;
	
	public function __construct($params = array()) {
        parent::__construct();
        
        if (in_array($_GET['val2'], array('s1', 's2'))) {
            $this->server = $_GET['val2'];
        }
        else {
            $this->server = 's2';
        }
        
        $this->heroes = array(
            1 => 'warrior',
            2 => 'hunter',
            3 => 'mage',
            4 => 'warlock',
            5 => 'shaman',
            6 => 'rogue',
            7 => 'druid',
            8 => 'paladin',
            9 => 'priest',
        );
        
        $this->groups = array(
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D',
            5 => 'E',
            6 => 'F',
            7 => 'G',
            8 => 'H'
        );
    
		$this->currentTournament = $this->data->settings['hs-current-number'];
	}

    public function getBattlefyTournamentData($number) {
        $this->pickedTournament = (int)$number;
        
        $tournamentRow = Db::fetchRow('SELECT `dates_start`, `dates_registration`, `time`, `status`, `prize`, `battlefy_id`, `battlefy_stage` '.
            'FROM `tournaments` '.
            'WHERE `game` = "hs" AND '.
            '`server` = "'.Db::escape($this->server).'" AND '.
            '`name` = '.(int)$this->pickedTournament.' '.
            'LIMIT 1'
        );
        
        if ($tournamentRow) {
            $tournamentTime['registration'] = $this->convertTime($tournamentRow->dates_registration.' '.$tournamentRow->time);
            $tournamentTime['checkin'] = $this->convertTime(strtotime($tournamentRow->dates_start.' '.$tournamentRow->time) - 3600);
            $tournamentTime['start'] = $this->convertTime($tournamentRow->dates_start.' '.$tournamentRow->time);

            include_once _cfg('pages').'/'.get_class().'/battlefy-tournament.html';
        }
        else {
            include_once  _cfg('pages').'/404/error.html';
        }
    }
	
	public function getTournamentData($number) {
        $this->pickedTournament = (int)$number;
        
        $rows = Db::fetchRows('SELECT `p`.`name` AS `battletag`, `p`.`place`, `u`.`name`, `p`.`user_id`, `p`.`seed_number`, `p`.`place`, `p`.`contact_info`, `p`.`approved`, `p`.`checked_in`, `p`.`verified`, `u`.`avatar` '.
            'FROM `participants` AS `p` '.
            'LEFT JOIN `users` AS `u` ON `p`.`user_id` = `u`.`id` '.
            'WHERE `p`.`game` = "hs" AND `server` = "'.$this->server.'" AND `p`.`tournament_id` = '.(int)$this->pickedTournament.' AND `deleted` = 0 '.
            'ORDER BY `p`.`user_id` DESC, `p`.`id` ASC'
        );
        if ($rows) {
            foreach($rows as &$v) {
                $v->contact_info = json_decode($v->contact_info);

                if ($v->place >= 1 && $v->place <= 3) {
                    $this->winners[$v->place] = ($v->name?$v->name:$v->battletag);
                }
            }
        }
        $this->participants = $rows;
        unset($v);
        
        $tournamentRow = Db::fetchRow('SELECT `dates_start`, `dates_registration`, `time`, `status`, `prize` '.
            'FROM `tournaments` '.
            'WHERE `game` = "hs" AND '.
            '`server` = "'.$this->server.'" AND '.
            '`name` = '.(int)$this->pickedTournament.' '.
            'LIMIT 1'
        );

        if ($tournamentRow) {
            $tournamentTime['registration'] = $this->convertTime($tournamentRow->dates_registration.' '.$tournamentRow->time);
            $tournamentTime['checkin'] = $this->convertTime(strtotime($tournamentRow->dates_start.' '.$tournamentRow->time) - 3600);
            $tournamentTime['start'] = $this->convertTime($tournamentRow->dates_start.' '.$tournamentRow->time);

            include_once _cfg('pages').'/'.get_class().'/tournament.html';
        }
        else {
            include_once  _cfg('pages').'/404/error.html';
        }
	}
    
    public function getTournamentList() {
		$rows = Db::fetchRows('SELECT * '.
			'FROM `tournaments` '.
			'WHERE `game` = "hs" AND '.
            '`server` = "'.Db::escape($this->server).'" '.
			'ORDER BY `id` DESC '.
            'LIMIT 10'
		);
        if ($rows) {
            foreach($rows as $v) {
                if ($v->status != 'ended') {
                    $startTime = strtotime($v->dates_start.' '.$v->time);
                    $regTime = strtotime($v->dates_registration.' '.$v->time);
                    
                    if (time() > $startTime) {
                        $v->status = t('live');
                    }
                    else if (time() < $startTime && time() > $regTime) {
                        $v->status = t('registration');
                    }
                    else if ($v->status == 'ended') {
                        $v->status = t('ended');
                    }
                    else {
                        $v->status = t('upcoming');
                    }
                }
                $this->tournamentData[$v->name] = (array)$v;
            }
        }
		
        if ($this->tournamentData) {
            $rows = Db::fetchRows('SELECT `tournament_id`, COUNT(`tournament_id`) AS `value`'.
                'FROM `participants` '.
                'WHERE `game` = "hs" AND '.
                '`server` = "'.Db::escape($this->server).'" AND '.
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
        
            $rows = Db::fetchRows('SELECT `tournament_id`, `name`, `place` '.
                'FROM `participants` '.
                'WHERE `game` = "hs" AND '.
                '`server` = "'.Db::escape($this->server).'" AND '.
                '`place` != 0 '.
                'ORDER BY `tournament_id`, `place`'
            );
            
            $previousTournamentId = 0;
            if ($rows) {
                foreach($rows as $v) {
                    if ($v->tournament_id != $previousTournamentId && $this->tournamentData[$v->tournament_id]) {
                        $this->tournamentData[$v->tournament_id]['places'][$v->place] = $v->name;
                    }
                }
            }
        }
		
		include_once _cfg('pages').'/'.get_class().'/index.html';
	}
	
	public static function getSeo() {
        $seo = new stdClass();
        $u = new self;
		$seo->title = 'Hearthstone League | '.strtoupper($u->server);
        
        if (is_numeric($_GET['val2'])) {
            $seo->title = 'Hearthstone League | '.strtoupper($u->server).'T'.$_GET['val2'];
            $seo->ogDesc = $seo->title;
        }
        
        $seo->ogImg = _cfg('img').'/hs-logo-big.png';
		
		return $seo;
	}
	
	public function showTemplate() {
        if (isset($_GET['val3']) && is_numeric($_GET['val3']) && $_GET['val2'] == 's2' && $_GET['val3'] > 8) {
            $this->getBattlefyTournamentData($_GET['val3']);
        }
        else if (isset($_GET['val3']) && is_numeric($_GET['val3'])) {
			$this->getTournamentData($_GET['val3']);
		}
		else {
			$this->getTournamentList();
		}
	}

    public function getNodeList() {
        $rows = Db::fetchRows('SELECT `challonge_id`, `name`, `contact_info` '.
            'FROM `participants` '.
            'WHERE `game` = "hs" '.
            //'AND `server` = "s1" '.
            //'AND `tournament_id` = 7 '.
            'AND `server` = "'.Db::escape($this->data->settings['tournament-season-hs']).'" '.
            'AND `tournament_id` = '.$this->currentTournament.' '.
            'AND `deleted` = 0 '.
            'AND `ended` = 0 '.
            'AND `approved` = 1 '.
            'AND `checked_in` = 1 '.
            'ORDER BY `name` ASC'
        );

        $bans = Db::fetchRows('SELECT `f`.`player1_id`, `f`.`player2_id`, `h`.`player1_ban`, `h`.`player2_ban` '.
            'FROM `fights` AS `f` '.
            'LEFT JOIN `hs_games` AS `h` ON `f`.`match_id` = `h`.`match_id` '.
            'WHERE `f`.`done` = 0 '
        );
    
        if ($rows) {
            foreach($rows as &$v) {
                $v->contact_info = json_decode($v->contact_info);
                if ($bans) {
                    foreach($bans as $b) {
                        if ($v->challonge_id == $b->player1_id) {
                            $v->contact_info->ban = array_search(strtolower($b->player2_ban), $this->heroes);
                        }
                        if ($v->challonge_id == $b->player2_id) {
                            $v->contact_info->ban = array_search(strtolower($b->player1_ban), $this->heroes);
                        }
                    }
                }
                
                if ($v->contact_info->ban) {
                    if ($v->contact_info->hero1 == $v->contact_info->ban) {
                        $v->contact_info->hero1 = $v->contact_info->hero2;
                        $v->contact_info->hero2 = $v->contact_info->hero3;
                        $v->contact_info->hero3 = $v->contact_info->hero4;
                    }
                    else if ($v->contact_info->hero2 == $v->contact_info->ban) {
                        $v->contact_info->hero2 = $v->contact_info->hero3;
                        $v->contact_info->hero3 = $v->contact_info->hero4;
                    }
                    else if ($v->contact_info->hero3 == $v->contact_info->ban) {
                        $v->contact_info->hero3 = $v->contact_info->hero4;
                    }

                    unset($v->contact_info->hero4);
                }
            }
        }

        return json_encode($rows);
    }
}