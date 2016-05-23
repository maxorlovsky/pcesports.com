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
            '(`game` = "hs" AND `name` = '.(int)$this->data->settings['hs-current-number'].' AND `server` = "'.Db::escape($this->data->settings['tournament-season-hs']).'") '.
            'ORDER BY `id` DESC '
        );
        
        if ($rows) {
            foreach($rows as $v) {
                $this->tournamentData[] = array(
                    'id'	=> $v->name,
                    'server'=> $v->server,
                    'game'  => $v->game,
                    'name' 	=> $v->name,
                    'status'=> str_replace('_', ' ', $v->status),
                    'max_num'=> $v->max_num,
                    'prize' => $v->prize,
                    'dates_start'=> $v->dates_start,
                    'link'  => $link,
                );
            }
        }
		
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
	
	public static function getSeo() {
		$seo = new stdClass();
		$seo->title = 'Tournaments list';
        
		return $seo;
	}
	
	public function showTemplate() {
		$this->getTournamentList();
	}
}