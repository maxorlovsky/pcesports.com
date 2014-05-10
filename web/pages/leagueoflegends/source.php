<?php

class leagueoflegends
{
	public $teamsCount;
	public $currentTournament = 3;
	public $teamsPlaces;
	
	public function __construct($params = array()) {
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
	}
	
	public static function getSeo() {
		$seo = new stdClass();
		$seo->title = 'League of Legends';
		
		return $seo;
	}
	
	public function showTemplate() {
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
}