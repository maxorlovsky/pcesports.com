<?php

class hearthstone extends System
{
	public $teamsCount;
	public $currentTournament;
	public $teamsPlaces;
	public $participants;
    public $pickedTournament;
    public $groups;
    public $server;
	
	public function __construct($params = array()) {
        parent::__construct();
        
        $this->server = 's1';
        
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
    
		$this->currentTournament = $this->data->settings['hs-current-number'];
	}
    
    public function editPage() {
        if (!isset($_SESSION['participant']) && !$_SESSION['participant']->id) {
			go(_cfg('href').'/hearthstone/'.$this->server);
		}
        $row = Db::fetchRow('SELECT `email`, `contact_info` '.
            'FROM `participants` '.
            'WHERE '.
            '`tournament_id` = '.(int)$this->currentTournament.' AND '.
            '`game` = "hs" AND '.
            '`id` = '.(int)$_SESSION['participant']->id.' '
        );
        $row->contact_info = json_decode($row->contact_info);
        
        $editData = $row;
        
        include_once _cfg('pages').'/'.get_class().'/edit.tpl';
    }
    
	public function participantPage() {
		$verified = 0;
		
		if (isset($_GET['val4']) && $_GET['val4'] == 'exit') {
			unset($_SESSION['participant']);
			go(_cfg('href').'/hearthstone/'.$this->server);
		}
		
		if (isset($_GET['val4']) && $_GET['val4'] == 'leave' && isset($_SESSION['participant']) && $_SESSION['participant']->id) {
            $this->leave();
		}
		
        if (isset($_GET['val4']) && $_GET['val4'] == 'surrender' && isset($_SESSION['participant']) && $_SESSION['participant']->id) {
            $this->surrender();
		}
		
		if (!isset($_GET['val5']) && !$_GET['val5'] && !$_SESSION['participant'] && !$_SESSION['participant']->id) {
			go(_cfg('href').'/hearthstone/'.$this->server);
		}
		
		if (isset($_SESSION['participant'])) {
			$id = $_SESSION['participant']->id;
			$code = $_SESSION['participant']->link;
		}
		else {
			$id = (int)$_GET['val4'];
			$code = $_GET['val5'];
		}
        
        $row = Db::fetchRow(
			'SELECT * '.
			'FROM `participants` AS `t` '.
			'WHERE '.
			'`t`.`tournament_id` = '.(int)$this->currentTournament.' AND '.
			'`t`.`game` = "hs" AND '.
            '`t`.`server` = "'.Db::escape($this->server).'" AND ' .
			'`t`.`id` = '.Db::escape($id).' AND '.
			'`t`.`link` = "'.Db::escape($code).'" AND '.
			'`t`.`deleted` = 0 AND '.
			'`t`.`ended` = 0'
		);

		if ($row && $row->approved == 0) {
			//Not approved, registration open, approving
			$this->approveRegisterPlayer($row);
			$verified = 1;
			$regged = 1;
		}
		else if ($row && $row->approved == 1) {
			$verified = 1;
		}
        $checked_in = $row->checked_in;
        $regged = 1;
        
        $rows = Db::fetchRow('SELECT COUNT(`id`) AS `count` '.
            'FROM `participants` '.
            'WHERE `game` = "lol" AND '.
            '`server` = "'.Db::escape($this->server).'" AND' .
            '`approved` = 1 AND '.
            '`checked_in` = 1 AND '.
            '`tournament_id` = '.(int)$_SESSION['participant']->tournament_id.' AND '.
            '`deleted` = 0 '
        );
        
        $this->participantsCount = $rows->count;
		
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
			'AND `game` = "hs" '.
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
        
        //Cleaning up duplicates
        Db::query('UPDATE `participants` '.
            'SET `deleted` = 1 '.
            'WHERE `tournament_id` = '.(int)$this->currentTournament.' AND '.
            '`game` = "hs" AND '.
            '`server` = "'.Db::escape($this->server).'" AND '.
            '`id` != '.$row->id.' AND '.
            '`name` = "'.Db::escape($row->name).'" '
        );
		
		return true;
	}
	
	public function getTournamentData($number) {
        $this->pickedTournament = (int)$number;
        
        $rows = Db::fetchRows('SELECT `p`.`name` AS `battletag`, `u`.`name`, `p`.`user_id`, `p`.`seed_number`, `p`.`place`, `p`.`contact_info`, `p`.`approved`, `p`.`checked_in` '.
            'FROM `participants` AS `p` '.
            'LEFT JOIN `users` AS `u` ON `p`.`user_id` = `u`.`id` '.
            'WHERE `p`.`game` = "hs" AND `p`.`tournament_id` = '.(int)$this->pickedTournament.' AND `deleted` = 0 '.
            'ORDER BY `p`.`id` ASC'
        );
        if ($rows) {
            foreach($rows as &$v) {
                $v->contact_info = json_decode($v->contact_info);
            }
        }
        $this->participants = $rows;
        unset($v);
        
        $tournamentRows = Db::fetchRows('SELECT `dates_start`, `dates_registration`, `time`, `status` '.
            'FROM `tournaments` '.
            'WHERE `game` = "hs" AND '.
            '`name` = '.(int)$this->pickedTournament.' '
        );
        if ($tournamentRows) {
            foreach($tournamentRows as $v) {
                $tournamentTime['registration'] = $this->convertTime($v->dates_registration.' '.$v->time);
                $tournamentTime['start'] = $this->convertTime($v->dates_start.' '.$v->time);
            }
        }
        
        include_once _cfg('pages').'/'.get_class().'/tournament.tpl';
	}
    
    public function getTournamentList() {
		$rows = Db::fetchRows('SELECT * '.
			'FROM `tournaments` '.
			'WHERE `game` = "hs" '.
			'ORDER BY `id` DESC '.
            'LIMIT 10'
		);
        if ($rows) {
            foreach($rows as $v) {
                if ($v->status != 'Ended') {
                    $startTime = strtotime($v->dates_start.' '.$v->time);
                    $regTime = strtotime($v->dates_registration.' '.$v->time);
                    
                    if (time() > $startTime) {
                        $v->status = t('live');
                    }
                    else if (time() < $startTime && time() > $regTime) {
                        $v->status = t('registration');
                    }
                    else if ($v->status == 'ended') {
                        $v->status = t('ended');
                    }
                    else {
                        $v->status = t('active');
                    }
                }
                $this->tournamentData[$v->name] = (array)$v;
            }
        }
		
        if ($this->tournamentData) {
            $rows = Db::fetchRows('SELECT `tournament_id`, COUNT(`tournament_id`) AS `value`'.
                'FROM `participants` '.
                'WHERE `game` = "hs" AND '.
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
		$seo->title = 'Hearthstone League | S1';
        
        $u = new self;
        
        if (is_numeric($_GET['val2'])) {
            $seo->title = 'Hearthstone League | S1T'.$_GET['val2'];
            $seo->ogDesc = $seo->title;
        }
        
        $seo->ogImg = _cfg('img').'/hs-logo-big.png';
		
		return $seo;
	}
	
	public function showTemplate() {
        if (isset($_GET['val4']) && $_GET['val4'] == 'fight') {
			$this->fightPage();
		}
        else if (isset($_GET['val4']) && $_GET['val4'] == 'edit') {
			$this->editPage();
		}
        else if (isset($_GET['val3']) && $_GET['val3'] == 'participant') {
			$this->participantPage();
		}
        else if (isset($_GET['val3']) && is_numeric($_GET['val3'])) {
			$this->getTournamentData($_GET['val3']);
		}
		else {
			$this->getTournamentList();
		}
	}
    
    protected function surrender() {
        $row = Db::fetchRow('SELECT `f`.`match_id`, `f`.`player1_id`, `f`.`player2_id`, `t1`.`id` AS `id1`, `t1`.`name` AS `name1`, `t2`.`id` AS `id2`, `t2`.`name` AS `name2` '.
            'FROM `fights` AS `f` '.
            'LEFT JOIN `participants` AS `t1` ON `f`.`player1_id` = `t1`.`challonge_id` '.
            'LEFT JOIN `participants` AS `t2` ON `f`.`player2_id` = `t2`.`challonge_id` '.
            'WHERE (`f`.`player1_id` = '.(int)$_SESSION['participant']->challonge_id.' OR `f`.`player2_id` = '.(int)$_SESSION['participant']->challonge_id.') '.
            'AND `f`.`done` = 0 '
        );
        
        if (!$row) {
            go(_cfg('href').'/hearthstone/'.$this->server);
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
        if (_cfg('env') == 'prod') {
            $this->runChallongeAPI('tournaments/pentaclick-hs'.$this->server.(int)$this->currentTournament.'/matches/'.$row->match_id.'.put', $apiArray);
        }
        else {
            $this->runChallongeAPI('tournaments/pentaclick-test1/matches/'.$row->match_id.'.put', $apiArray);
        }
        
        Db::query('UPDATE `participants` SET `ended` = 1 '.
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
        
        go(_cfg('href').'/hearthstone/'.$this->server);
    }
    
    protected function leave() {
        Db::query('UPDATE `participants` SET `deleted` = 1 '.
        'WHERE `game` = "hs" AND '.
        '`id` = '.(int)$_SESSION['participant']->id.' AND '. 
        '`link` = "'.Db::escape($_SESSION['participant']->link).'" ');
        
        $row = Db::fetchRow('SELECT `challonge_id` '.
            'FROM `participants` '.
            'WHERE `game` = "hs" AND '.
            '`id` = '.(int)$_SESSION['participant']->id.' AND '. 
            '`link` = "'.Db::escape($_SESSION['participant']->link).'" '
        );
        
        if ($row->challonge_id != 0) {
            $apiArray = array(
                '_method' => 'delete',
            );
            if (_cfg('env') == 'prod') {
                $this->runChallongeAPI('tournaments/pentaclick-hs'.$this->server.(int)$this->currentTournament.'/participants/'.(int)$row->challonge_id.'.post', $apiArray);
            }
            else {
                $this->runChallongeAPI('tournaments/pentaclick-test1/participants/'.(int)$row->challonge_id.'.post', $apiArray);
            }
        }
        
        unset($_SESSION['participant']);
        
        go(_cfg('href').'/hearthstone/'.$this->server);
    }
}