<?php

class leagueoflegends extends System
{
	public $teamsCount;
	public $currentTournament;
	public $teamsPlaces;
	public $participants;
    public $pickedTournament;
	
	public function __construct($params = array()) {
		parent::__construct();
    
		$this->currentTournament = $this->data->settings['lol-current-number'];
	}
    
    public function fightPage() {
        if (!isset($_SESSION['participant']) && !$_SESSION['participant']->id) {
			go(_cfg('href').'/leagueoflegends');
		}
        $row = Db::fetchRow('SELECT `id` FROM `players` WHERE '.
            '`tournament_id` = '.(int)$this->currentTournament.' AND '.
            '`game` = "lol" AND '.
            '`approved` = 1 AND '.
            '`deleted` = 0 AND '.
            '`ended` = 0 '
        );

        include_once _cfg('pages').'/'.get_class().'/fight.tpl';
    }
	
	public function getTournamentData($number) {
        $this->pickedTournament = (int)$number;
        
		if ($this->pickedTournament > 0 && $this->pickedTournament <= $this->currentTournament) {
			$rows = Db::fetchRows('SELECT `t`.`id`, `t`.`name`, `p`.`name` AS `player`, `p`.`player_id` '.
                'FROM `teams` AS `t` '.
				'JOIN  `players` AS  `p` ON  `p`.`team_id` =  `t`.`id` '.
				'WHERE `t`.`game` = "lol" AND `t`.`approved` = 1 AND `t`.`tournament_id` = '.(int)$this->pickedTournament.' AND `t`.`deleted` = 0 '.
				'ORDER BY `t`.`id` ASC, `p`.`player_num` ASC'
			);
			
			$participants = array();
			$i = 0 ;
			if ($rows) {
				foreach($rows as $v) {
					$participants[$v->id]['name'] = $v->name;
					$participants[$v->id][$i]['player'] = $v->player;
					$participants[$v->id][$i]['player_id'] = $v->player_id;
					++$i;
				}
			}

			$this->participants = $participants;
			
			include_once _cfg('pages').'/'.get_class().'/tournament.tpl';
		}
		else {
			include_once  _cfg('pages').'/404/error.tpl';
		}
	}
	
	public function getTournamentList() {
		$rows = Db::fetchRows('SELECT * '.
			'FROM `tournaments` '.
			'WHERE `game` = "lol" '.
            'AND `status` != "Registration" '.
			'ORDER BY `id` DESC'
		);
		foreach($rows as $v) {
			$this->tournamentData[$v->name] = (array)$v;
		}
		
		$rows = Db::fetchRows('SELECT `tournament_id`, COUNT(`tournament_id`) AS `value`'.
			'FROM `teams` '.
			'WHERE `game` = "lol" AND `approved` = 1 AND `deleted` = 0 '.
			'GROUP BY `tournament_id` '.
			'ORDER BY `id` DESC'
		);
		foreach($rows as $v) {
			$this->tournamentData[$v->tournament_id]['teamsCount'] = $v->value;
		}
		
		$rows = Db::fetchRows('SELECT `tournament_id`, `name`, `place` '.
			'FROM `teams` '.
			'WHERE `game` = "lol" AND `place` != 0 '.
			'ORDER BY `tournament_id`, `place`'
		);
		
		$previousTournamentId = 0;
		if ($rows) {
			foreach($rows as $v) {
				if ($v->tournament_id != $previousTournamentId) {
					$this->tournamentData[$v->tournament_id]['places'][$v->place] = $v->name;
				}
			}
		}
		
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
	
	public static function getSeo() {
		$seo = new stdClass();
		$seo->title = 'League of Legends';
		
		return $seo;
	}
	
	public function showTemplate() {
		if (isset($_GET['val3']) && $_GET['val3'] == 'fight') {
			$this->fightPage();
		}
		else if (isset($_GET['val2']) && $_GET['val2'] == 'participant') {
			$this->participantPage();
		}
        else if (isset($_GET['val2']) && is_numeric($_GET['val2'])) {
			$this->getTournamentData($_GET['val2']);
		}
		else {
			$this->getTournamentList();
		}
	}
}