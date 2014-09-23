<?php

class hearthstoneleague extends System
{
	public $teamsCount;
	public $currentTournament;
	public $teamsPlaces;
	public $participants;
    public $pickedTournament;
    public $groups;
	
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
        
        $this->groups = array(
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D',
            5 => 'E',
            6 => 'F',
            7 => 'G',
            8 => 'H'
        );
    
		$this->currentTournament = $this->data->settings['hslan-current-number'];
	}
    
    public function editPage() {
        if (!isset($_SESSION['participant']) && !$_SESSION['participant']->id) {
			go(_cfg('href').'/hearthstonelan');
		}
        $row = Db::fetchRow('SELECT `contact_info` '.
            'FROM `participants` '.
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
			'FROM `participants` AS `t` '.
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
            'FROM `participants` '.
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
		
		Db::query('UPDATE `participants` '.
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
				Db::query('UPDATE `participants` '.
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
        Db::query('UPDATE `participants` '.
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
        $this->pickedTournament = 0;
        
        $rows = Db::fetchRows('SELECT `name`, `seed_number`, `place`, `contact_info` '.
            'FROM `participants` '.
            'WHERE `game` = "hslan" AND `approved` = 1 AND `tournament_id` = '.(int)$this->pickedTournament.' AND `deleted` = 0 '.
            'ORDER BY `id` ASC'
        );
        foreach($rows as &$v) {
            $v->contact_info = json_decode($v->contact_info);
        }
        $this->participants = $rows;
        unset($v);
        
        include_once _cfg('pages').'/'.get_class().'/tournament.tpl';
	}
    
    public function getTournamentList() {
		$rows = Db::fetchRows('SELECT * '.
			'FROM `tournaments` '.
			'WHERE `game` = "hslan" AND '.
            '`server` = "'.Db::escape($this->server).'" AND' .
            '`status` != "Registration" '.
			'ORDER BY `id` DESC '.
            'LIMIT 5'
		);
        if ($rows) {
            foreach($rows as $v) {
                $this->tournamentData[$v->name] = (array)$v;
            }
        }
		
        if ($this->tournamentData) {
            $rows = Db::fetchRows('SELECT `tournament_id`, COUNT(`tournament_id`) AS `value`'.
                'FROM `participants` '.
                'WHERE `game` = "hslan" AND '.
                '`server` = "'.Db::escape($this->server).'" AND ' .
                '`approved` = 1 AND '.
                '`deleted` = 0 '.
                'GROUP BY `tournament_id` '.
                'ORDER BY `id` DESC'
            );
            if ($rows) {
                foreach($rows as $v) {
                    if ($this->tournamentData[$v->tournament_id]) {
                        $this->tournamentData[$v->tournament_id]['teamsCount'] = $v->value;
                    }
                }
            }
        
            $rows = Db::fetchRows('SELECT `tournament_id`, `name`, `place` '.
                'FROM `participants` '.
                'WHERE `game` = "lol" AND '.
                '`server` = "'.Db::escape($this->server).'" AND '.
                '`place` != 0 '.
                'ORDER BY `tournament_id`, `place`'
            );
            
            $previousTournamentId = 0;
            if ($rows) {
                foreach($rows as $v) {
                    if ($v->tournament_id != $previousTournamentId && $this->tournamentData[$v->tournament_id]) {
                        $this->tournamentData[$v->tournament_id]['places'][$v->place] = $v->name;
                    }
                }
            }
        }
		
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
	
	public static function getSeo() {
		$seo = new stdClass();
		$seo->title = 'Hearthstone League | Season 1';
		
		return $seo;
	}
	
	public function showTemplate() {
        if (isset($_GET['val3']) && $_GET['val3'] == 'edit') {
			$this->editPage();
		}
        else if (isset($_GET['val2']) && $_GET['val2'] == 'participant') {
			$this->participantPage();
		}
        else if (isset($_GET['val2']) && is_numeric($_GET['val2'])) {
			$this->getTournamentData($_GET['val3']);
		}
		else {
			$this->getTournamentList();
		}
	}
    
    protected function leave() {
        Db::query('UPDATE `participants` SET `deleted` = 1 '.
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