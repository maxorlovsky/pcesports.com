<?php

class leagueoflegends
{
	public $teamsCount;
	
	public function __construct($params = array()) {
		$this->teamsCount = Db::fetchRows('SELECT COUNT(`tournament_id`) AS `value`'.
			'FROM `teams` AS `n` '.
			'WHERE `game` = "lol" '.
			'GROUP BY `tournament_id` '.
			'ORDER BY `id`'
		);
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