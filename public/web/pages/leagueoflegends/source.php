<?php

class leagueoflegends extends System
{
	public $teamsCount;
	public $currentTournament;
	public $teamsPlaces;
	public $participants;
    public $participantsCount = 0;
    public $pickedTournament;
    public $server;
    public $eventId = 0;
    public $winners;
	
	public function __construct($params = array()) {
		parent::__construct();
        
        if (in_array($_GET['val2'], array('eune', 'euw'))) {
            $this->server = $_GET['val2'];
        }
        else {
            $this->server = 'euw';
        }
		
		$this->currentTournament = $this->data->settings['lol-current-number-'.$this->server];
	}

    public function getBattlefyTournamentData($number) {
        $this->pickedTournament = (int)$number;
        
        $tournamentRow = Db::fetchRow('SELECT `dates_start`, `dates_registration`, `time`, `status`, `prize`, `event_id`, `battlefy_id`, `battlefy_stage` '.
            'FROM `tournaments` '.
            'WHERE `game` = "lol" AND '.
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
        
        $tournamentRow = Db::fetchRow('SELECT `dates_start`, `dates_registration`, `time`, `status`, `prize` '.
            'FROM `tournaments` '.
            'WHERE `game` = "lol" AND '.
            '`server` = "'.Db::escape($this->server).'" AND '.
            '`name` = '.(int)$this->pickedTournament.' '.
            'LIMIT 1'
        );
        
		if ($tournamentRow) {
            $tournamentTime['registration'] = $this->convertTime($tournamentRow->dates_registration.' '.$tournamentRow->time);
            $tournamentTime['checkin'] = $this->convertTime(strtotime($tournamentRow->dates_start.' '.$tournamentRow->time) - 3600);
            $tournamentTime['start'] = $this->convertTime($tournamentRow->dates_start.' '.$tournamentRow->time);
            
			$rows = Db::fetchRows('SELECT `t`.`id`, `t`.`name`, `t`.`checked_in`, `t`.`place`, `p`.`name` AS `player`, `p`.`player_id` '.
                'FROM `participants` AS `t` '.
				'JOIN  `players` AS  `p` ON  `p`.`participant_id` =  `t`.`id` '.
				'WHERE `t`.`game` = "lol" AND '.
                '`t`.`server` = "'.Db::escape($this->server).'" AND' .
                '`t`.`approved` = 1 AND '.
                '`t`.`tournament_id` = '.(int)$this->pickedTournament.' AND '.
                '`t`.`deleted` = 0 '.
				'ORDER BY `t`.`id` ASC, `p`.`player_num` ASC'
			);
			
			$participants = array();
			$i = 0 ;
			if ($rows) {
				foreach($rows as $v) {
					$participants[$v->id]['name'] = $v->name;
                    $participants[$v->id]['checked_in'] = $v->checked_in;
					$participants[$v->id][$i]['player'] = $v->player;
					$participants[$v->id][$i]['player_id'] = $v->player_id;
					++$i;
                    
                    if ($v->place >= 1 && $v->place <= 3) {
                        $this->winners[$v->place] = $v->name;
                    }
				}
			}

			$this->participants = $participants;
            
            $pickedSummoner = '';
            if ($this->data->user->summoners) {
                foreach($this->data->user->summoners as $v) {
                    if ($v->region == $this->server) {
                        $pickedSummoner = $v->name;
                        break;
                    }
                }
            }

            $eventId = Db::fetchRow(
                'SELECT `event_id` FROM `tournaments` '.
                'WHERE `name` = '.(int)$this->pickedTournament.' AND '.
                '`server` = "'.Db::escape($this->server).'" AND '.
                '`game` = "lol" '.
                'LIMIT 1'
            );
            $this->eventId = $eventId->event_id;
			
			include_once _cfg('pages').'/'.get_class().'/tournament.html';
		}
		else {
			include_once  _cfg('pages').'/404/error.html';
		}
	}
	
	public function getTournamentList() {
		$rows = Db::fetchRows('SELECT * '.
			'FROM `tournaments` '.
			'WHERE `game` = "lol" AND '.
            '`server` = "'.Db::escape($this->server).'" ' .
			'ORDER BY `id` DESC '.
            'LIMIT 10'
		);
        
        if ($rows) {
            foreach($rows as $v) {
                $v->status = str_replace('_', ' ', $v->status);
                $this->tournamentData[$v->name] = (array)$v;
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
                    if (isset($this->tournamentData[$v->tournament_id]) && $this->tournamentData[$v->tournament_id]) {
                        $this->tournamentData[$v->tournament_id]['teamsCount'] = $v->value;
                    }
                }
            }
        
            $rows = Db::fetchRows('SELECT `tournament_id`, `name`, `place` '.
                'FROM `participants` '.
                'WHERE `game` = "lol" AND '.
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
		$seo->title = 'League of Legends tournaments';
        
        $u = new self;
        
        if (isset($_GET['val3']) && is_numeric($_GET['val3'])) {
            $seo->title = 'League of Legends tournament '.strtoupper($u->server).'#'.$_GET['val3'];
            $seo->ogDesc = $seo->title;
        }
        
        $seo->ogImg = _cfg('img').'/lol-logo-big.png';
		
		return $seo;
	}
	
	public function showTemplate() {
        if (isset($_GET['val3']) && is_numeric($_GET['val3']) && (($_GET['val2'] == 'euw' && $_GET['val3'] < 30) || ($_GET['val2'] == 'eune' && $_GET['val3'] < 29))) {
			$this->getTournamentData($_GET['val3']);
		}
        else if (isset($_GET['val3']) && is_numeric($_GET['val3'])) {
            $this->getBattlefyTournamentData($_GET['val3']);
        }
		else {
			$this->getTournamentList();
		}
	}
}