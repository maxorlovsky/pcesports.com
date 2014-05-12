<?php

class hearthstone
{
	public $teamsCount;
	public $currentTournament = 4;
	public $teamsPlaces;
	public $participants;
	
	public function __construct($params = array()) {
		
	}
	
	public function getTournamentData($id) {
		if (file_exists(_cfg('pages').'/'.get_class().'/tournament-'.$id.'.tpl')) {
			$rows = Db::fetchRows('SELECT `name` '.
				'FROM `teams` '.
				'WHERE `game` = "hs" AND `approved` = 1 AND `tournament_id` = '.(int)$id.' AND `deleted` = 0 '.
				'ORDER BY `id` ASC'
			);

			$this->participants = $rows;
			
			include_once _cfg('pages').'/'.get_class().'/tournament-'.$id.'.tpl';
		}
		else {
			include_once  _cfg('pages').'/404/error.tpl';
		}
	}
	
	public function getTournamentList() {
		$rows = Db::fetchRows('SELECT `tournament_id`, COUNT(`tournament_id`) AS `value`'.
			'FROM `teams` '.
			'WHERE `game` = "hs" AND `approved` = 1 '.
			'GROUP BY `tournament_id` '.
			'ORDER BY `id` DESC'
		);
		foreach($rows as $v) {
			$this->teamsCount[$v->tournament_id] = $v->value;
		}
		
		$rows = Db::fetchRows('SELECT `tournament_id`, `name`, `place` '.
			'FROM `teams` '.
			'WHERE `game` = "hs" AND `place` != 0 '.
			'ORDER BY `tournament_id`, `place`'
		);
		
		$placesArray = array();
		$previousTournamentId = 0;
		if ($rows) {
			foreach($rows as $v) {
				if ($v->tournament_id != $previousTournamentId) {
					$placesArray[$v->tournament_id][$v->place] = $v->name;
				}
			}
		}
		
		$this->teamsPlaces = $placesArray;
		
		$this->eventDates[1] = '01.03.2014';
		$this->eventDates[2] = '15.03.2014';
		$this->eventDates[3] = '19.04.2014';
		$this->eventDates[4] = '24.05.2014';
		
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