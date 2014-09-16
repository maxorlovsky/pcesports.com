<?php

class hearthstonelan extends System
{
	public $teamsCount;
	public $currentTournament;
	public $teamsPlaces;
	public $participants;
    public $pickedTournament;
	
	public function __construct($params = array()) {
        parent::__construct();
        
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
    
		$this->currentTournament = 0;
	}
    
    public function editPage() {
        if (!isset($_SESSION['participant']) && !$_SESSION['participant']->id) {
			go(_cfg('href').'/hearthstonelan');
		}
        $row = Db::fetchRow('SELECT `contact_info` '.
            'FROM `teams` '.
            'WHERE '.
            '`tournament_id` = 0 AND '.
            '`game` = "hslan" AND '.
            '`id` = '.(int)$_SESSION['participant']->id.' '
        );
        
        $editData = json_decode($row->contact_info);
        
        include_once _cfg('pages').'/'.get_class().'/edit.tpl';
    }
    
	public function participantPage() {
		$verified = 0;
		$regged = 0;
		
		if (isset($_GET['val3']) && $_GET['val3'] == 'exit') {
			unset($_SESSION['participant']);
			go(_cfg('href').'/hearthstonelan');
		}
		
		if (isset($_GET['val3']) && $_GET['val3'] == 'leave' && isset($_SESSION['participant']) && $_SESSION['participant']->id) {
            $this->leave();
		}
		
		if (!isset($_GET['val4']) && !$_GET['val4'] && !$_SESSION['participant'] && !$_SESSION['participant']->id) {
			go(_cfg('href').'/hearthstonelan');
		}
		
		if (isset($_SESSION['participant'])) {
			$id = $_SESSION['participant']->id;
			$code = $_SESSION['participant']->link;
		}
		else {
			$id = (int)$_GET['val3'];
			$code = $_GET['val4'];
		}
		
		$row = Db::fetchRow('SELECT * '.
			'FROM `teams` AS `t` '.
			'WHERE '.
			'`t`.`tournament_id` = '.(int)$this->currentTournament.' AND '.
			'`t`.`game` = "hslan" AND '.
			'`t`.`id` = '.Db::escape($id).' AND '.
			'`t`.`link` = "'.Db::escape($code).'" AND '.
			'`t`.`deleted` = 0 AND '.
			'`t`.`ended` = 0'
		);
		
		if ($row && $row->approved == 0 && $row->deleted == 0) {
			//Not approved, registration open, approving and adding to brackets
			$row->challonge_id = $this->approveRegisterPlayer($row);
			$verified = 1;
			$regged = 1;
		}
		else if ($row && $row->approved == 1 && $row->deleted == 0) {
			$verified = 1;
		}
        
        $participantsCountRow = Db::fetchRow(
            'SELECT COUNT(`id`) AS `count` '.
            'FROM `teams` '.
            'WHERE `tournament_id` = 0 AND '.
            '`game` = "hslan" AND '.
            '`approved` = 1 AND '.
            '`deleted` = 0 AND '.
			'`ended` = 0 '
        );
        $participantsCount = $participantsCountRow->count;
        
		
		if ($verified == 1) {
			$_SESSION['participant'] = $row;
			
			include_once _cfg('pages').'/'.get_class().'/participant-page.tpl';
		}
		else {
			include_once _cfg('pages').'/'.get_class().'/participant-error.tpl';
		}
	}
	
	protected function approveRegisterPlayer($row) {
		//Generating other IDs for different environment
		if (_cfg('env') == 'prod') {
			$participant_id = $row->id + 100000;
		}
		else {
			$participant_id = $row->id;
		}
		
		Db::query('UPDATE `teams` '.
			'SET `approved` = 1 '.
			'WHERE `tournament_id` = '.(int)$this->currentTournament.' '.
			'AND `game` = "hslan" '.
			'AND `id` = '.$row->id
		);
        
        $subscribeRow = Db::fetchRow(
            'SELECT * FROM `subscribe` WHERE '.
            '`email` = "'.Db::escape($row->email).'" '
        );
        
        if (!$subscribeRow) {
            Db::query('INSERT INTO `subscribe` SET '.
                '`email` = "'.Db::escape($row->email).'", '.
                '`unsublink` = "'.sha1(Db::escape($row->email).rand(0,9999).time()).'"'
            );
        }
		
		$apiArray = array(
			'participant_id' => $participant_id,
			'participant[name]' => $row->name,
		);
		
		//Adding team to Challonge bracket
        if (_cfg('env') == 'prod') {
            $this->runChallongeAPI('tournaments/pentaclick-dreamforge/participants.post', $apiArray);
        }
        else {
            $this->runChallongeAPI('tournaments/pentaclick-test1/participants.post', $apiArray);
        }
		
		//Registering ID, becaus Challonge idiots not giving an answer with ID
        if (_cfg('env') == 'prod') {
            $answer = $this->runChallongeAPI('tournaments/pentaclick-dreamforge/participants.json');
        }
        else {
            $answer = $this->runChallongeAPI('tournaments/pentaclick-test1/participants.json');
        }
        
		array_reverse($answer, true);
		
		foreach($answer as $f) {
			if ($f->participant->name == $row->name) {
				Db::query('UPDATE `teams` '.
					'SET `challonge_id` = '.(int)$f->participant->id.' '.
					'WHERE `tournament_id` = '.(int)$this->currentTournament.' '.
					'AND `game` = "hslan" '.
					'AND `id` = '.$row->id
				);
				$challonge_id = (int)$f->participant->id;
				break;
			}
		}
        
        //Cleaning up duplicates
        Db::query('UPDATE `teams` '.
            'SET `deleted` = 1 '.
            'WHERE `tournament_id` = '.(int)$this->currentTournament.' '.
            'AND `game` = "hslan" '.
            'AND `id` != '.$row->id.' '.
            'AND `name` = "'.Db::escape($row->name).'" '
        );
		
		$this->sendMail('info@pcesports.com',
		'Player added. PentaClick eSports.',
		'Participant was added!!!<br />
    	Date: '.date('d/m/Y H:i:s').'<br />
		BattleTag: <b>'.$row->name.'</b><br>
    	IP: '.$_SERVER['REMOTE_ADDR']);
		
		return $challonge_id;
	}
	
	public function getTournamentData($number) {
        $this->pickedTournament = (int)$number;
        
        $rows = Db::fetchRows('SELECT `name` '.
            'FROM `teams` '.
            'WHERE `game` = "hslan" AND `approved` = 1 AND `tournament_id` = '.(int)$this->pickedTournament.' AND `deleted` = 0 '.
            'ORDER BY `id` ASC'
        );

        $this->participants = $rows;
        
        include_once _cfg('pages').'/'.get_class().'/tournament.tpl';
		
	}
	
	public static function getSeo() {
		$seo = new stdClass();
		$seo->title = 'Hearthstone LAN';
		
		return $seo;
	}
	
	public function showTemplate() {
        if (isset($_GET['val3']) && $_GET['val3'] == 'edit') {
			$this->editPage();
		}
        else if (isset($_GET['val2']) && $_GET['val2'] == 'participant') {
			$this->participantPage();
		}
		else {
            $this->getTournamentData(0);
		}
	}
    
    protected function leave() {
        Db::query('UPDATE `teams` SET `deleted` = 1 '.
        'WHERE `game` = "hslan" AND '.
        '`id` = '.(int)$_SESSION['participant']->id.' AND '. 
        '`link` = "'.Db::escape($_SESSION['participant']->link).'" ');
        
        $apiArray = array(
            '_method' => 'delete',
        );
        if (_cfg('env') == 'prod') {
            $this->runChallongeAPI('tournaments/pentaclick-dreamforge/participants/'.$_SESSION['participant']->challonge_id.'.post', $apiArray);
        }
        else {
            $this->runChallongeAPI('tournaments/pentaclick-test1/participants/'.$_SESSION['participant']->challonge_id.'.post', $apiArray);
        }
        
        $this->sendMail('info@pcesports.com',
        'Player deleted. PentaClick eSports.',
        'Participant was deleted!!!<br />
        Date: '.date('d/m/Y H:i:s').'<br />
        BattleTag: <b>'.$_SESSION['participant']->name.'</b><br>
        IP: '.$_SERVER['REMOTE_ADDR']);
        
        unset($_SESSION['participant']);
        
        go(_cfg('href').'/hearthstonelan');
    }
}