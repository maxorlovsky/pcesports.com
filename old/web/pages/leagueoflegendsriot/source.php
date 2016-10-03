<?php

class leagueoflegendsriot extends System
{
	public $teamsCount;
	public $currentTournament;
	public $teamsPlaces;
	public $participants;
    public $participantsCount = 0;
    public $pickedTournament;
    public $server;
	
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
    
	
	public function getTournamentData($number) {
        $this->pickedTournament = (int)$number;
        
        $tournamentRow = Db::fetchRow('SELECT `dates_start`, `dates_registration`, `time`, `status` '.
            'FROM `tournaments` '.
            'WHERE `game` = "lol" AND '.
            '`server` = "'.Db::escape($this->server).'" AND '.
            '`name` = '.(int)$this->pickedTournament.' '
        );
        
		if ($tournamentRow) {
            $tournamentTime['start'] = $this->convertTime($tournamentRow->dates_start.' '.$tournamentRow->time);
            
			$rows = Db::fetchRows('SELECT `t`.`id`, `t`.`name`, `t`.`checked_in`, `p`.`name` AS `player`, `p`.`player_id`, `t`.`place` '.
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
            $participantsIds = array();
            $participantsNames = array();
			$i = 0 ;
			if ($rows) {
				foreach($rows as $v) {
					$participants[$v->id]['name'] = $v->name;
                    $participants[$v->id]['checked_in'] = $v->checked_in;
                    $participants[$v->id]['place'] = $v->place;
                    $participants[$v->id]['id'] = $v->id;
					$participants[$v->id][$i]['player'] = $v->player;
					$participants[$v->id][$i]['player_id'] = $v->player_id;
					++$i;

                    if (!in_array($v->id, $participantsIds)) {
                        $participantsIds[] = $v->id;
                        $participantsNames[$v->id] = $v->name;
                    }
				}
			}

			$this->participants = $participants;

            $participantsWhere = '';
            foreach($participantsIds as $v) {
                $participantsWhere .= '`participant_id1` = '.$v.' OR `participant_id2` = '.$v.' OR ';
            }
            $participantsWhere = substr($participantsWhere, 0, -3);
            
            $this->battleLog = Db::fetchRows(
                'SELECT * FROM `lol_games` '.
                'WHERE '.$participantsWhere
            );
            
			include_once _cfg('pages').'/'.get_class().'/index.tpl';
		}
		else {
			exit('You broke it ;(');
		}
	}
	
	
	public static function getSeo() {
		$seo = new stdClass();
		$seo->title = 'League of Legends tournaments';
        
        $u = new self;
        
        if (is_numeric($_GET['val3'])) {
            $seo->title = 'RIOT SPECIALLY League of Legends tournament '.strtoupper($u->server).'#'.$_GET['val3'];
            $seo->ogDesc = $seo->title;
        }
        
        $seo->ogImg = _cfg('img').'/lol-logo-big.png';
		
		return $seo;
	}
	
	public function showTemplate() {
		$this->getTournamentData($_GET['val3']);
	}
}