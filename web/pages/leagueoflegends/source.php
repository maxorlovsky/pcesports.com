<?php

class leagueoflegends
{
	public $teamsCount;
	public $currentTournament = 3;
	
	public function __construct($params = array()) {
		$rows = Db::fetchRows('SELECT `tournament_id`, COUNT(`tournament_id`) AS `value`'.
			'FROM `teams` AS `n` '.
			'WHERE `game` = "lol" AND `approved` = 1 '.
			'GROUP BY `tournament_id` '.
			'ORDER BY `id` DESC'
		);
		foreach($rows as $v) {
			$this->teamsCount[$v->tournament_id] = $v->value;
		}
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