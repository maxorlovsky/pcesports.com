<?php

class hearthstoneblizzard extends System
{
	public $currentTournament;
	public $participants;
    public $pickedTournament;
    public $server;
    public $winners;
    public $heroes;
 	
	public function __construct($params = array()) {
        parent::__construct();
        
        if (in_array($_GET['val2'], array('s1', 's2'))) {
            $this->server = $_GET['val2'];
        }
        else {
            $this->server = 's2';
        }
        
        $this->heroes = array(
            1 => 'warrior',
            2 => 'hunter',
            3 => 'mage',
            4 => 'warlock',
            5 => 'shaman',
            6 => 'rogue',
            7 => 'druid',
            8 => 'paladin',
            9 => 'priest',
        );
    
		$this->currentTournament = $this->data->settings['hs-current-number'];
	}
	
	public function getTournamentData($number) {
        $this->pickedTournament = (int)$number;

        $tournamentRow = Db::fetchRow('SELECT `dates_start`, `dates_registration`, `time`, `status` '.
            'FROM `tournaments` '.
            'WHERE `game` = "hs" AND '.
            '`server` = "'.Db::escape($this->server).'" AND '.
            '`name` = '.(int)$this->pickedTournament.' '
        );

        if ($tournamentRow) {
            $tournamentTime['start'] = $this->convertTime($tournamentRow->dates_start.' '.$tournamentRow->time);

            $rows = Db::fetchRows('SELECT `id`, `name`, `place`, `contact_info` '.
	            'FROM `participants` '.
	            'WHERE `game` = "hs" '.
	            'AND `server` = "'.Db::escape($this->server).'" '.
	            'AND `tournament_id` = '.(int)$this->pickedTournament.' AND '.
	            '`deleted` = 0 '
	        );

	        if ($rows) {
	            foreach($rows as &$v) {
	                $v->contact_info = json_decode($v->contact_info);

	                if ($v->place >= 1 && $v->place <= 4) {
	                    $this->winners[$v->place] = $v->name;
	                }
	            }

                ksort($this->winners);
	        }
	        $this->participants = $rows;
            
			include_once _cfg('pages').'/'.get_class().'/index.tpl';
		}
		else {
			exit('You broke it ;(');
		}
	}
	
	public static function getSeo() {
        $seo = new stdClass();
        $u = new self;
		
        $seo->title = 'BLIZZARD SPECIALLY Hearthstone League | '.strtoupper($u->server).'T'.$_GET['val2'];
        $seo->ogDesc = $seo->title;
        
        $seo->ogImg = _cfg('img').'/hs-logo-big.png';
		
		return $seo;
	}

	public function showTemplate() {
        $this->getTournamentData($_GET['val3']);
	}
}