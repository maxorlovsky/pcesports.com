<?php

class bnb extends System
{
    public $tournamentData;
	public $teamsPlaces;
    public $allowedGames;
    public $project = 'bnb';
    
	public function __construct($params = array()) {
        parent::__construct();
        
        $this->allowedGames = array('leagueoflegends', 'hearthstone');
	}
    
    public function getTournamentList() {
		$rows = Db::fetchRows('SELECT * FROM `tournaments_external` WHERE '.
            '`project` = "'.$this->project.'" '.
            'ORDER BY `id` DESC, `status` DESC '
        );
        
        if ($rows) {
            foreach($rows as $v) {
                if ($v->game == 'lol') {
                    $link = 'leagueoflegends/'.$v->server;
                }
                else if ($v->game == 'hs') {
                    $link = 'hearthstone';
                }
                
                $this->tournamentData[] = array(
                    'id'	=> $v->id,
                    'server'=> $v->server,
                    'game'  => $v->game,
                    'name' 	=> $v->name,
                    'status'=> t($v->status),
                    'max_num'=> $v->max_num,
                    'prize' => $v->prize,
                    'dates_start'=> $v->dates_start,
                    'link'  => $link,
                );
            }
        }
	}
	
	public function showTemplate() {
        if (isset($_GET['val2']) && in_array($_GET['val2'], $this->allowedGames)) {
            include_once _cfg('widgets').'/'.$_GET['val2'].'/source.php';
            
            $game = new $_GET['val2']($this->project);
            $game->showTemplate();
        }
        else {
            $this->getTournamentList();
            
            include_once _cfg('widgets').'/'.$this->project.'/index.tpl';
        }
	}
}