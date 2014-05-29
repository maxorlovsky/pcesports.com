<?php

class hearthstone extends System
{
	public $teamsCount;
	public $currentTournament;
	public $teamsPlaces;
	public $participants;
    public $pickedTournament;
	
	public function __construct($params = array()) {
        parent::__construct();
    
		$this->currentTournament = $this->data->settings['hs-current-number'];
	}
    
    public function fightPage() {
        if (!isset($_SESSION['participant']) && !$_SESSION['participant']->id) {
			go(_cfg('href').'/hearthstone');
		}
        $row = Db::fetchRow('SELECT `id` FROM `players` WHERE '.
            '`tournament_id` = '.(int)$this->currentTournament.' AND '.
            '`game` = "hs" AND '.
            '`approved` = 1 AND '.
            '`deleted` = 0 AND '.
            '`ended` = 0 '
        );

        include_once _cfg('pages').'/'.get_class().'/fight.tpl';
    }
	
	public function participantPage() {
		$verified = 0;
		$regged = 0;
		
		if (isset($_GET['val3']) && $_GET['val3'] == 'exit') {
			unset($_SESSION['participant']);
			go(_cfg('href').'/hearthstone');
		}
		
		if (isset($_GET['val3']) && $_GET['val3'] == 'leave' && isset($_SESSION['participant']) && $_SESSION['participant']->id) {
			Db::query('UPDATE `teams` SET `deleted` = 1 '.
			'WHERE `game` = "hs" AND '.
			'`id` = '.(int)$_SESSION['participant']->id.' AND '. 
			'`link` = "'.Db::escape($_SESSION['participant']->link).'" ');
			
			$apiArray = array(
				'_method' => 'delete',
			);
			$this->runChallongeAPI('tournaments/pentaclick-hs'.(int)$this->currentTournament.'/participants/'.$_SESSION['participant']->challonge_id.'.post', $apiArray);
            //$this->runChallongeAPI('tournaments/pentaclick-test1/participants/'.$_SESSION['participant']->challonge_id.'.post', $apiArray);
			
			$this->sendMail('info@pcesports.com',
			'Player deleted. PentaClick eSports.',
			'Participant was deleted!!!<br />
            Date: '.date('d/m/Y H:i:s').'<br />
			BattleTag: <b>'.$_SESSION['participant']->name.'</b><br>
            IP: '.$_SERVER['REMOTE_ADDR']);
			
			unset($_SESSION['participant']);
			
			go(_cfg('href').'/hearthstone');
		}
		
		if (isset($_GET['val3']) && $_GET['val3'] == 'surrender' && isset($_SESSION['participant']) && $_SESSION['participant']->id) {
			$row = Db::fetchRow('SELECT `f`.`match_id`, `f`.`player1_id`, `f`.`player2_id`, `t1`.`id` AS `id1`, `t1`.`name` AS `name1`, `t2`.`id` AS `id2`, `t2`.`name` AS `name2` '.
				'FROM `fights` AS `f` '.
				'LEFT JOIN `teams` AS `t1` ON `f`.`player1_id` = `t1`.`challonge_id` '.
				'LEFT JOIN `teams` AS `t2` ON `f`.`player2_id` = `t2`.`challonge_id` '.
				'WHERE (`f`.`player1_id` = '.(int)$_SESSION['participant']->challonge_id.' OR `f`.`player2_id` = '.(int)$_SESSION['participant']->challonge_id.') '.
				'AND `f`.`done` = 0 '
			);
			
			if (!$row) {
				go(_cfg('href').'/hearthstone');
			}
			
			if ($row->player1_id == $_SESSION['participant']->challonge_id) {
				$winner = $row->player2_id;
				$scores = '0-1';
			}
			else {
				$winner = $row->player1_id;
				$scores = '1-0';
			}
			
			$apiArray = array(
				'_method' => 'put',
				'match_id' => $row->match_id,
				'match[scores_csv]' => $scores,
				'match[winner_id]' => $winner,
			);
			$this->runChallongeAPI('tournaments/pentaclick-hs'.(int)$this->currentTournament.'/matches/'.$row->match_id.'.put', $apiArray);
            //$this->runChallongeAPI('tournaments/pentaclick-test1/matches/'.$row->match_id.'.put', $apiArray);
			
			Db::query('UPDATE `teams` SET `ended` = 1 '.
				'WHERE `game` = "hs" AND '.
				'`id` = '.(int)$_SESSION['participant']->id.' AND '. 
				'`link` = "'.Db::escape($_SESSION['participant']->link).'" '
			);
			
			Db::query('UPDATE `fights` SET `done` = 1 '.
				'WHERE `match_id` = '.(int)$row->match_id.' '
			);
			
			$fileName = $_SERVER['DOCUMENT_ROOT'].'/chats/'.$row->id1.'_vs_'.$row->id2.'.txt';
                
			$file = fopen($fileName, 'a');
			$content = '<p><span id="notice">('.date('H:i:s', time()).')</span> <b>'.$_SESSION['participant']->name.' surrendered</b></p>';
			fwrite($file, htmlspecialchars($content));
			fclose($file);
			
			unset($_SESSION['participant']);
			
			go(_cfg('href').'/hearthstone');
		}
		
		if (!isset($_GET['val4']) && !$_GET['val4'] && !$_SESSION['participant'] && !$_SESSION['participant']->id) {
			go(_cfg('href').'/hearthstone');
		}
		
		if (isset($_SESSION['participant'])) {
			$id = $_SESSION['participant']->id;
			$code = $_SESSION['participant']->link;
		}
		else {
			$id = (int)$_GET['val3'];
			$code = $_GET['val4'];
		}
		
		$row = Db::fetchRow(
			'SELECT * '.
			'FROM `teams` AS `t` '.
			'WHERE '.
			'`t`.`tournament_id` = '.(int)$this->currentTournament.' AND '.
			'`t`.`game` = "hs" AND '.
			'`t`.`id` = '.Db::escape($id).' AND '.
			'`t`.`link` = "'.Db::escape($code).'" AND '.
			'`t`.`deleted` = 0 AND '.
			'`t`.`ended` = 0'
		);
		if ($row && $row->approved == 0) {
			//Not approved, registration open, approving and adding to brackets
			$row->challonge_id = $this->approveRegisterPlayer($row);
			$verified = 1;
			$regged = 1;
			
			$_SESSION['participant'] = $row;
		}
		else if ($row && $row->approved == 1) {
			$verified = 1;
			
			$_SESSION['participant'] = $row;
		}
		
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
		else if (_cfg('env') == 'test') {
			$participant_id = $row->id + 50000;
		}
		else {
			$participant_id = $row->id;
		}
		
		Db::query('UPDATE `teams` '.
			'SET `approved` = 1 '.
			'WHERE `tournament_id` = '.(int)$this->currentTournament.' '.
			'AND `game` = "hs" '.
			'AND `id` = '.$row->id
		);
        
        Db::query('INSERT IGNORE INTO `subscribe` SET '.
            '`email` = "'.Db::escape($row->email).'", '.
            '`unsublink` = "'.sha1(Db::escape($row->email).rand(0,9999).time()).'"'
        );
		
		$apiArray = array(
			'participant_id' => $participant_id,
			'participant[name]' => $row->name,
		);
		
		//Adding team to Challonge bracket
		$this->runChallongeAPI('tournaments/pentaclick-hs'.(int)$this->currentTournament.'/participants.post', $apiArray);
        //$this->runChallongeAPI('tournaments/pentaclick-test1/participants.post', $apiArray);
		
		//Registering ID, becaus Challonge idiots not giving an answer with ID
		$answer = $this->runChallongeAPI('tournaments/pentaclick-hs'.(int)$this->currentTournament.'/participants.json');
        //$answer = $this->runChallongeAPI('tournaments/pentaclick-test1/participants.json');
		array_reverse($answer, true);
		
		foreach($answer as $f) {
			if ($f->participant->name == $row->name) {
				Db::query('UPDATE `teams` '.
					'SET `challonge_id` = '.(int)$f->participant->id.' '.
					'WHERE `tournament_id` = '.(int)$this->currentTournament.' '.
					'AND `game` = "hs" '.
					'AND `id` = '.$row->id
				);
				$challonge_id = (int)$f->participant->id;
				break;
			}
		}
		
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
        
		if ($this->pickedTournament > 0 && $this->pickedTournament <= $this->currentTournament) {
			$rows = Db::fetchRows('SELECT `name` '.
				'FROM `teams` '.
				'WHERE `game` = "hs" AND `approved` = 1 AND `tournament_id` = '.(int)$this->pickedTournament.' AND `deleted` = 0 '.
				'ORDER BY `id` ASC'
			);

			$this->participants = $rows;
			
			include_once _cfg('pages').'/'.get_class().'/tournament.tpl';
		}
		else {
			include_once  _cfg('pages').'/404/error.tpl';
		}
	}
	
	public function getTournamentList() {
		$rows = Db::fetchRows('SELECT * '.
			'FROM `tournaments` '.
			'WHERE `game` = "hs" '.
            'AND `status` != "Registration" '.
			'ORDER BY `id` DESC'
		);
		foreach($rows as $v) {
			$this->tournamentData[$v->name] = (array)$v;
		}
		
		$rows = Db::fetchRows('SELECT `tournament_id`, COUNT(`tournament_id`) AS `value`'.
			'FROM `teams` '.
			'WHERE `game` = "hs" AND `approved` = 1 AND `deleted` = 0 '.
			'GROUP BY `tournament_id` '.
			'ORDER BY `id` DESC'
		);
		foreach($rows as $v) {
			$this->tournamentData[$v->tournament_id]['teamsCount'] = $v->value;
		}
		
		$rows = Db::fetchRows('SELECT `tournament_id`, `name`, `place` '.
			'FROM `teams` '.
			'WHERE `game` = "hs" AND `place` != 0 '.
			'ORDER BY `tournament_id`, `place`'
		);
		
		$previousTournamentId = 0;
		if ($rows) {
			foreach($rows as $v) {
				if ($v->tournament_id != $previousTournamentId) {
					$this->tournamentData[$v->tournament_id]['places'][$v->place] = $v->name;
				}
			}
		}
		
		include_once _cfg('pages').'/'.get_class().'/index.tpl';
	}
	
	public static function getSeo() {
		$seo = new stdClass();
		$seo->title = 'Hearthstone';
		
		return $seo;
	}
	
	public function showTemplate() {
        if (isset($_GET['val3']) && $_GET['val3'] == 'fight') {
			$this->fightPage();
		}
		else if (isset($_GET['val2']) && $_GET['val2'] == 'participant') {
			$this->participantPage();
		}
        else if (isset($_GET['val2']) && is_numeric($_GET['val2'])) {
			$this->getTournamentData($_GET['val2']);
		}
		else {
			$this->getTournamentList();
		}
	}
}