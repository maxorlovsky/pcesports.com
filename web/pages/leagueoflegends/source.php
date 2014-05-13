<?php

class leagueoflegends
{
	public $teamsCount;
	public $currentTournament = 4;
	public $teamsPlaces;
	public $participants;
	
	public function __construct($params = array()) {
		
	}
	
	public function getTournamentData($id) {
		if (file_exists(_cfg('pages').'/'.get_class().'/tournament-'.$id.'.tpl')) {
			$rows = Db::fetchRows('SELECT `t`.`id`, `t`.`name`, `p`.`name` AS `player`, `p`.`player_id` '.
				'FROM `teams` AS `t` '.
				'JOIN  `players` AS  `p` ON  `p`.`team_id` =  `t`.`id` '.
				'WHERE `t`.`game` = "lol" AND `t`.`approved` = 1 AND `t`.`tournament_id` = '.(int)$id.' AND `t`.`deleted` = 0 '.
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
			
			include_once _cfg('pages').'/'.get_class().'/tournament-'.$id.'.tpl';
			include_once _cfg('pages').'/'.get_class().'/footer.tpl';
		}
		else {
			include_once  _cfg('pages').'/404/error.tpl';
		}
	}
	
	public function getTournamentList() {
		$rows = Db::fetchRows('SELECT `tournament_id`, COUNT(`tournament_id`) AS `value`'.
			'FROM `teams` '.
			'WHERE `game` = "lol" AND `approved` = 1 '.
			'GROUP BY `tournament_id` '.
			'ORDER BY `id` DESC'
		);
		foreach($rows as $v) {
			$this->teamsCount[$v->tournament_id] = $v->value;
		}
		
		$rows = Db::fetchRows('SELECT `tournament_id`, `name`, `place` '.
			'FROM `teams` '.
			'WHERE `game` = "lol" AND `place` != 0 '.
			'ORDER BY `tournament_id`, `place`'
		);
		
		$placesArray = array();
		$previousTournamentId = 0;
		foreach($rows as $v) {
			if ($v->tournament_id != $previousTournamentId) {
				$placesArray[$v->tournament_id][$v->place] = $v->name;
			}
		}
		
		$this->teamsPlaces = $placesArray;
		
		$this->eventDates[1] = '01.02.2014';
		$this->eventDates[2] = '29.03.2014';
		$this->eventDates[3] = '10.05.2014';
		$this->eventDates[4] = '14.06.2014';
		
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
	
	public static function getSeo() {
		$seo = new stdClass();
		$seo->title = 'League of Legends';
		
		return $seo;
	}
	
	public function showTemplate() {
		if (isset($_GET['val2'])) {
			$this->getTournamentData((int)$_GET['val2']);
		}
		else {
			$this->getTournamentList();
		}
	}
}