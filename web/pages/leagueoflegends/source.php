<?php

class leagueoflegends extends System
{
	public $teamsCount;
	public $currentTournament;
	public $teamsPlaces;
	public $participants;
    public $participantsCount = 0;
    public $pickedTournament;
    public $server;
    public $eventId = 0;
    public $winners;
	
	public function __construct($params = array()) {
		parent::__construct();
        
        if (in_array($_GET['val2'], array('eune', 'euw'))) {
            $this->server = $_GET['val2'];
        }
        else {
            $this->server = 'euw';
        }
		
		$this->currentTournament = $this->data->settings['lol-current-number-'.$this->server];
	}
    
    public function teamEditPage() {
        if (!isset($_SESSION['participant']) && !$_SESSION['participant']->id) {
			go(_cfg('href').'/leagueoflegends/'.$this->server);
		}
        $rows = Db::fetchRows('SELECT * '.
            'FROM `players` '.
            'WHERE '.
            '`tournament_id` = '.(int)$this->currentTournament.' AND '.
            '`game` = "lol" AND '.
            '`participant_id` = '.(int)$_SESSION['participant']->id.' '.
            'ORDER BY `player_num` '
        );
        
        $players = array();
        foreach($rows as $v) {
            $players[$v->player_num] = $v->name;
        }

        $row = Db::fetchRow('SELECT `name` '.
            'FROM `streams` '.
            'WHERE `participant_id` = '.(int)$_SESSION['participant']->id.' '.
            'AND `game` = "lol" '.
            'LIMIT 1'
        );

        if ($row) {
            $stream = $row->name;
        }

        include_once _cfg('pages').'/'.get_class().'/team.tpl';
    }
	
	public function participantPage() {
		$verified = 0;
		$regged = 0;
		
		if (isset($_GET['val4']) && $_GET['val4'] == 'exit') {
			unset($_SESSION['participant']);
			go(_cfg('href').'/leagueoflegends/'.$this->server);
		}
		
		if (isset($_GET['val4']) && $_GET['val4'] == 'leave' && isset($_SESSION['participant']) && $_SESSION['participant']->id) {
            $this->leave();
		}
		
		if (isset($_GET['val4']) && $_GET['val4'] == 'surrender' && isset($_SESSION['participant']) && $_SESSION['participant']->id) {
            $this->surrender();
		}
		
		if (!isset($_GET['val5']) && !$_GET['val5'] && !$_SESSION['participant'] && !$_SESSION['participant']->id) {
			go(_cfg('href').'/leagueoflegends/'.$this->server);
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
			'SELECT `p`.*, `t`.`status` AS `tournamentStatus` '.
			'FROM `participants` AS `p` '.
            'JOIN `tournaments` AS `t` ON `p`.`tournament_id` = `t`.`name` AND `p`.`server` = `t`.`server` AND `p`.`game` = `t`.`game` '.
			'WHERE `p`.`tournament_id` = '.(int)$this->currentTournament.' '.
			'AND `p`.`game` = "lol" '.
            'AND `p`.`server` = "'.Db::escape($this->server).'" ' .
			'AND `p`.`id` = '.Db::escape($id).' '.
			'AND `p`.`link` = "'.Db::escape($code).'" '.
			'AND `p`.`deleted` = 0 '.
			'AND `p`.`ended` = 0 '.
            'LIMIT 1'
		);

		if ($row && $row->approved == 0) {
			//Not approved, registration open, approving
			$this->approveRegisterPlayer($row);
            Achievements::give(array(15,16,17));//Let's try to beat'em! (Register on League of Legends tournament.)
			$verified = 1;
			$regged = 1;
		}
		else if ($row && $row->approved == 1) {
			$verified = 1;
		}
        
        $eventId = Db::fetchRow(
            'SELECT `event_id` FROM `tournaments` '.
            'WHERE `name` = '.(int)$this->currentTournament.' AND '.
            '`server` = "'.Db::escape($this->server).'" AND '.
            '`game` = "lol" '.
            'LIMIT 1'
        );
        $this->eventId = $eventId->event_id;
		
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
			'AND `game` = "lol" '.
			'AND `id` = '.$row->id
		);

        User::subscribe($data['email']);
        
        //Cleaning up duplicates
        Db::query('UPDATE `participants` '.
            'SET `deleted` = 1 '.
            'WHERE `tournament_id` = '.(int)$this->currentTournament.' AND '.
            '`game` = "lol" AND '.
            '`server` = "'.Db::escape($this->server).'" AND '.
            '`id` != '.$row->id.' AND '.
            '`name` = "'.Db::escape($row->name).'" '
        );
		
		return true;
	}
	
	public function getTournamentData($number) {
        $this->pickedTournament = (int)$number;
        
        $tournamentRow = Db::fetchRow('SELECT `dates_start`, `dates_registration`, `time`, `status`, `prize` '.
            'FROM `tournaments` '.
            'WHERE `game` = "lol" AND '.
            '`server` = "'.Db::escape($this->server).'" AND '.
            '`name` = '.(int)$this->pickedTournament.' '.
            'LIMIT 1'
        );
        
		if ($tournamentRow) {
            $tournamentTime['registration'] = $this->convertTime($tournamentRow->dates_registration.' '.$tournamentRow->time);
            $tournamentTime['checkin'] = $this->convertTime(strtotime($tournamentRow->dates_start.' '.$tournamentRow->time) - 3600);
            $tournamentTime['start'] = $this->convertTime($tournamentRow->dates_start.' '.$tournamentRow->time);
            
			$rows = Db::fetchRows('SELECT `t`.`id`, `t`.`name`, `t`.`checked_in`, `t`.`place`, `p`.`name` AS `player`, `p`.`player_id` '.
                'FROM `participants` AS `t` '.
				'JOIN  `players` AS  `p` ON  `p`.`participant_id` =  `t`.`id` '.
				'WHERE `t`.`game` = "lol" AND '.
                '`t`.`server` = "'.Db::escape($this->server).'" AND' .
                '`t`.`approved` = 1 AND '.
                '`t`.`tournament_id` = '.(int)$this->pickedTournament.' AND '.
                '`t`.`deleted` = 0 '.
				'ORDER BY `t`.`id` ASC, `p`.`player_num` ASC'
			);
			
			$participants = array();
			$i = 0 ;
			if ($rows) {
				foreach($rows as $v) {
					$participants[$v->id]['name'] = $v->name;
                    $participants[$v->id]['checked_in'] = $v->checked_in;
					$participants[$v->id][$i]['player'] = $v->player;
					$participants[$v->id][$i]['player_id'] = $v->player_id;
					++$i;
                    
                    if ($v->place >= 1 && $v->place <= 3) {
                        $this->winners[$v->place] = $v->name;
                    }
				}
			}

			$this->participants = $participants;
            
            $pickedSummoner = '';
            if ($this->data->user->summoners) {
                foreach($this->data->user->summoners as $v) {
                    if ($v->region == $this->server) {
                        $pickedSummoner = $v->name;
                        break;
                    }
                }
            }

            $eventId = Db::fetchRow(
                'SELECT `event_id` FROM `tournaments` '.
                'WHERE `name` = '.(int)$this->pickedTournament.' AND '.
                '`server` = "'.Db::escape($this->server).'" AND '.
                '`game` = "lol" '.
                'LIMIT 1'
            );
            $this->eventId = $eventId->event_id;
			
			include_once _cfg('pages').'/'.get_class().'/tournament.tpl';
		}
		else {
			include_once  _cfg('pages').'/404/error.tpl';
		}
	}
	
	public function getTournamentList() {
		$rows = Db::fetchRows('SELECT * '.
			'FROM `tournaments` '.
			'WHERE `game` = "lol" AND '.
            '`server` = "'.Db::escape($this->server).'" ' .
			'ORDER BY `id` DESC '.
            'LIMIT 10'
		);
        
        if ($rows) {
            foreach($rows as $v) {
                if ($v->status != 'ended') {
                    $startTime = strtotime($v->dates_start.' '.$v->time);
                    $regTime = strtotime($v->dates_registration.' '.$v->time);
                    
                    if ($this->data->settings['tournament-checkin-lol-'.$this->server] == 1 && $this->currentTournament == $v->name) {
                        $v->status = 'Check in';
                    }
                    else if (time() > $startTime) {
                        $v->status = t('live');
                    }
                    else if (time() < $startTime && time() > $regTime) {
                        $v->status = t('registration');
                    }
                    else if ($v->status == 'ended') {
                        $v->status = t('ended');
                    }
                    else {
                        $v->status = t('upcoming');
                    }
                }
                $this->tournamentData[$v->name] = (array)$v;
            }
        }
		
        if ($this->tournamentData) {
            $rows = Db::fetchRows('SELECT `tournament_id`, COUNT(`tournament_id`) AS `value`'.
                'FROM `participants` '.
                'WHERE `game` = "lol" AND '.
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
		$seo->title = 'League of Legends tournaments';
        
        $u = new self;
        
        if (is_numeric($_GET['val3'])) {
            $seo->title = 'League of Legends tournament '.strtoupper($u->server).'#'.$_GET['val3'];
            $seo->ogDesc = $seo->title;
        }
        
        $seo->ogImg = _cfg('img').'/lol-logo-big.png';
		
		return $seo;
	}
	
	public function showTemplate() {
        if (isset($_GET['val4']) && $_GET['val4'] == 'team') {
			$this->teamEditPage();
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
            go(_cfg('href').'/leagueoflegends/'.$this->server);
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
            $this->runChallongeAPI('tournaments/pentaclick-lol'.$this->server.(int)$this->currentTournament.'/matches/'.$row->match_id.'.put', $apiArray);
        }
        else {
            $this->runChallongeAPI('tournaments/pentaclick-test1/matches/'.$row->match_id.'.put', $apiArray);
        }
        
        Db::query('UPDATE `participants` SET `ended` = 1 '.
            'WHERE `game` = "lol" AND '.
            '`id` = '.(int)$_SESSION['participant']->id.' AND '. 
            '`link` = "'.Db::escape($_SESSION['participant']->link).'" '
        );
        
        Db::query('UPDATE `fights` SET `done` = 1 '.
            'WHERE `match_id` = '.(int)$row->match_id.' '
        );

        Db::query('DELETE FROM `streams` WHERE `participant_id` = '.(int)$_SESSION['participant']->id);
        
        $fileName = $_SERVER['DOCUMENT_ROOT'].'/chats/'.$row->id1.'_vs_'.$row->id2.'.txt';
            
        $file = fopen($fileName, 'a');
        
        $content = '<div class="manager">';
        $content .= '<div class="message">';
        $content .= 'Team <b>'.$_SESSION['participant']->name.'</b> surrendered';
        $content .= '</div>';
        $content .= '<span>System message</span>';
        $content .= '&nbsp;•&nbsp;<span id="notice">'.date('H:i', time()).'</span>';
        $content .= '</div>';

        fwrite($file, htmlspecialchars($content));
        fclose($file);
        
        unset($_SESSION['participant']);
        
        go(_cfg('href').'/leagueoflegends/'.$this->server);
    }
    
    protected function leave() {
        Db::query(
            'UPDATE `participants` SET `deleted` = 1 '.
            'WHERE `game` = "lol" AND '.
            '`id` = '.(int)$_SESSION['participant']->id.' AND '. 
            '`link` = "'.Db::escape($_SESSION['participant']->link).'" '.
            'LIMIT 1'
        );

        Db::query('DELETE FROM `streams` WHERE `participant_id` = '.(int)$_SESSION['participant']->id);
        
        $row = Db::fetchRow('SELECT `challonge_id` '.
            'FROM `participants` '.
            'WHERE `game` = "lol" AND '.
            '`id` = '.(int)$_SESSION['participant']->id.' AND '. 
            '`link` = "'.Db::escape($_SESSION['participant']->link).'" '
        );
        
        if ($row->challonge_id != 0) {
            $apiArray = array(
                '_method' => 'delete',
            );
            if (_cfg('env') == 'prod') {
                $this->runChallongeAPI('tournaments/pentaclick-lol'.$this->server.(int)$this->currentTournament.'/participants/'.(int)$row->challonge_id.'.post', $apiArray);
            }
            else {
                $this->runChallongeAPI('tournaments/pentaclick-test1/participants/'.(int)$row->challonge_id.'.post', $apiArray);
            }
        }
        
        unset($_SESSION['participant']);
        
        go(_cfg('href').'/leagueoflegends/'.$this->server);
    }

    public function checkIn() {
        if (!$_SESSION['participant']) {
            return '0;'.t('not_logged_in');
        }
        
        $server = $_SESSION['participant']->server;
        $currentTournament = (int)$this->data->settings['lol-current-number-'.$server];
        
        if ($this->data->settings['tournament-checkin-lol-'.$server] != 1) {
            return '0;Check in is not in progress';
        }
        
        //Generating other IDs for different environment
        if (_cfg('env') == 'prod') {
            $participant_id = $_SESSION['participant']->id + 100000;
        }
        else {
            $participant_id = $_SESSION['participant']->id;
        }
        
        $apiArray = array(
            'participant_id' => $participant_id,
            'participant[name]' => $_SESSION['participant']->name,
        );
        
        //Adding team to Challonge bracket
        if (_cfg('env') == 'prod') {
            $this->runChallongeAPI('tournaments/pentaclick-lol'.$server.$currentTournament.'/participants.post', $apiArray);
        }
        else {
            $this->runChallongeAPI('tournaments/pentaclick-test1/participants.post', $apiArray);
        }
        
        //Registering ID, because Challonge idiots not giving an answer with ID
        if (_cfg('env') == 'prod') {
            $answer = $this->runChallongeAPI('tournaments/pentaclick-lol'.$server.$currentTournament.'/participants.json');
        }
        else {
            $answer = $this->runChallongeAPI('tournaments/pentaclick-test1/participants.json');
        }
        
        array_reverse($answer, true);
        
        foreach($answer as $f) {
            if ($f->participant->name == $_SESSION['participant']->name) {
                Db::query('UPDATE `participants` '.
                    'SET `challonge_id` = '.(int)$f->participant->id.', '.
                    '`checked_in` = 1 '.
                    'WHERE `tournament_id` = '.(int)$currentTournament.' '.
                    'AND `game` = "lol" '.
                    'AND `id` = '.(int)$_SESSION['participant']->id.' '.
                    'AND `approved` = 1 '
                );
                
                $_SESSION['participant']->checked_in = 1;
                
                break;
            }
        }

        Achievements::give(array(18,19,20));//I am experienced! (Participate in League of Legends tournament.)
        
        return '1;1';
    }

    public function register($data) {
        $err = array();
        $suc = array();
        parse_str($data['form'], $post);
        
        if (in_array($post['server'], array('eune', 'euw'))) {
            $server = $post['server'];
        }
        else {
            $server = 'euw';
        }
        
        if ($this->data->settings['tournament-reg-lol-'.$server] != 1) {
            return '0;Server error!';
        }
        
        if (!$post['agree']) {
            $err['agree'] = '0;'.t('must_agree_with_rules');
        }
        else {
            $suc['agree'] = '1;'.t('approved');
        }
        
        $row = Db::fetchRow('SELECT * FROM `participants` WHERE '.
            '`tournament_id` = '.(int)$this->data->settings['lol-current-number-'.$server].' AND '.
            '`name` = "'.Db::escape($post['team']).'" AND '.
            '`server` = "'.Db::escape($server).'" AND '.
            '`game` = "lol" AND '.
            '`approved` = 1 AND '.
            '`deleted` = 0'
        );

        if (!$post['team']) {
            $err['team'] = '0;'.t('field_empty');
        }
        else if (strlen($post['team']) < 4) {
            $err['team'] = '0;'.t('team_name_small');
        }
        else if (strlen($post['team']) > 60) {
            $err['team'] = '0;'.t('team_name_big');
        }
        else if ($row) {
            $err['team'] = '0;'.t('team_name_taken');
        }
        else {
            $suc['team'] = '1;'.t('approved');
        }
        
        if (!$post['email']) {
            $err['email'] = '0;'.t('field_empty');
        }
        else if(!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
            $err['email'] = '0;'.t('email_invalid');
        }
        else {
            $suc['email'] = '1;'.t('approved');
        }
        
        $players = array();
        $checkForSame = array();
        $summonersNames = array();
        for($i=1;$i<=7;++$i) {
            $post['mem'.$i] = trim($post['mem'.$i]);
            
            if (!$post['mem'.$i] && $i < 6) {
                $err['mem'.$i] = '0;'.t('field_empty');    
            }
            else if (in_array($post['mem'.$i], $checkForSame)) {
                $err['mem'.$i] = '0;'.t('same_summoner');
            }
            else if ($post['mem'.$i]) {
                $summonersNames[] = rawurlencode(htmlspecialchars($post['mem'.$i]));
                $checkForSame[] = $post['mem'.$i];
            }
        }
        
        if (!$err) {
            $summonersNames = implode(',', $summonersNames);
            $response = $this->runRiotAPI('/'.$server.'/v1.4/summoner/by-name/'.$summonersNames, $server, true);
            for($i=1;$i<=7;++$i) {
                $name = str_replace(' ', '', mb_strtolower($post['mem'.$i]));
                
                if (isset($response->$name) && $response->$name) {
                    if ($response->$name->summonerLevel != 30) {
                        $err['mem'.$i] = '0;'.t('summoner_low_lvl');
                    }
                    else {
                        $players[$i]['id'] = $response->$name->id;
                        $players[$i]['name'] = $response->$name->name;
                        $suc['mem'.$i] = '1;'.t('approved');
                    }
                }
                else if ($post['mem'.$i] && !isset($response->$name)) {
                    $err['mem'.$i] = '0;'.t('summoner_not_found_'.$server);
                }
            }
        }

        $addStream = 0;
        if ($post['stream']) {
            $post['stream'] = str_replace(array('http://www.twitch.tv/', 'http://twitch.tv/'), array('',''), $post['stream']);
            
            $twitch = $this->runTwitchAPI($post['stream']);
            
            if (!$twitch) {
                $err['stream'] = '0;'.t('channel_not_found');
            }
            else {
                $addStream = 1;
                $suc['stream'] = '1;'.t('approved');
            }
        }
                
        if ($err) {
            $answer['ok'] = 0;
            if ($suc) {
                $err = array_merge($err, $suc);
            }
            $answer['err'] = $err;
        }
        else {
            $answer['ok'] = 1;
            $answer['err'] = $suc;
        
            $code = substr(sha1(time().rand(0,9999)).$post['team'], 0, 32);
            Db::query('INSERT INTO `participants` SET '.
                '`game` = "lol", '.
                '`user_id` = '.(int)$this->data->user->id.', '.
                '`server` = "'.$server.'", '.
                '`tournament_id` = '.(int)$this->data->settings['lol-current-number-'.$server].', '.
                '`timestamp` = NOW(), '.
                '`ip` = "'.Db::escape(isset($_SERVER['HTTP_CF_CONNECTING_IP'])?$_SERVER['HTTP_CF_CONNECTING_IP']:$_SERVER['REMOTE_ADDR']).'", '.
                '`name` = "'.Db::escape($post['team']).'", '.
                '`email` = "'.Db::escape($post['email']).'", '.
                '`contact_info` = "'.Db::escape($team).'", '.
                '`cpt_player_id` = '.(int)$players[1]['id'].', '.
                '`link` = "'.$code.'"'
            );
        
            $teamId = Db::lastId();
            
            foreach($players as $k => $v) {
                Db::query(
                    'INSERT INTO `players` SET '.
                    ' `game` = "lol", '.
                    ' `tournament_id` = '.(int)$this->data->settings['lol-current-number-'.$server].', '.
                    ' `participant_id` = '.(int)$teamId.', '.
                    ' `name` = "'.Db::escape($v['name']).'", '.
                    ' `player_num` = "'.(int)$k.'", '.
                    ' `player_id` = "'.(int)$v['id'].'"'
                );
            }

            if ($addStream == 1) {
                Db::query(
                    'INSERT INTO `streams` SET '.
                    '`user_id`  = '.(int)$this->data->user->id.', '.
                    '`participant_id`  = '.$teamId.', '.
                    '`tournament_id`  = '.(int)$this->data->settings['lol-current-number-'.$server].', '.
                    '`name` = "'.Db::escape($post['stream']).'", '.
                    '`game` = "lol" '
                );
            }
            
            $text = Template::getMailTemplate('reg-lol-team');
        
            $text = str_replace(
                array('%name%', '%teamId%', '%code%', '%url%', '%href%'),
                array($post['team'], $teamId, $code, _cfg('href').'/leagueoflegends/'.$server, _cfg('site')),
                $text
            );
        
            $this->sendMail($post['email'], 'Pentaclick League of Legends tournament participation', $text);
        }
         
        return json_encode($answer);
    }

    public function editParticipant($data) {
        $err = array();
        $suc = array();
        parse_str($data['form'], $post);
        
        if (in_array($post['server'], array('eune', 'euw'))) {
            $server = $post['server'];
        }
        else {
            $server = 'euw';
        }
        
        if ($this->data->settings['tournament-start-lol-'.$server] == 1) {
            $err['mem1'] = '0;'.t('tournament_in_progress');
        }
        else {
            $players = array();
            $checkForSame = array();
            $summonersNames = array();
            for($i=1;$i<=7;++$i) {
                $post['mem'.$i] = trim($post['mem'.$i]);
                
                if (!$post['mem'.$i] && $i < 6) {
                    $err['mem'.$i] = '0;'.t('field_empty');    
                }
                else if (in_array($post['mem'.$i], $checkForSame)) {
                    $err['mem'.$i] = '0;'.t('same_summoner');
                }
                else if ($post['mem'.$i]) {
                    $summonersNames[] = rawurlencode(htmlspecialchars($post['mem'.$i]));
                    $checkForSame[] = $post['mem'.$i];
                }
            }
        }
        
        if (!$err) {
            $summonersNames = implode(',', $summonersNames);
            $response = $this->runRiotAPI('/'.$server.'/v1.4/summoner/by-name/'.$summonersNames, $server, true);
            for($i=1;$i<=7;++$i) {
                $name = str_replace(' ', '', mb_strtolower($post['mem'.$i]));
                if (isset($response->$name) && $response->$name) {
                    if ($response->$name->summonerLevel != 30) {
                        $err['mem'.$i] = '0;'.t('summoner_low_lvl');
                    }
                    else {
                        $players[$i]['id'] = $response->$name->id;
                        $players[$i]['name'] = $response->$name->name;
                        $suc['mem'.$i] = '1;'.t('approved');
                    }
                }
                else if ($post['mem'.$i] && !isset($response->$name)) {
                    $err['mem'.$i] = '0;'.t('summoner_not_found_'.$server);
                }
            }
        }

        $addStream = 0;
        if ($post['stream']) {
            $post['stream'] = str_replace(array('http://www.twitch.tv/', 'http://twitch.tv/'), array('',''), $post['stream']);
            
            $twitch = $this->runTwitchAPI($post['stream']);
            
            if (!$twitch) {
                $err['stream'] = '0;'.t('channel_not_found');
            }
            else {
                $addStream = 1;
                $suc['stream'] = '1;'.t('approved');
            }
        }
    
        if ($err) {
            $answer['ok'] = 0;
            if ($suc) {
                $err = array_merge($err, $suc);
            }
            $answer['err'] = $err;
        }
        else {
            $answer['ok'] = 1;
            $answer['err'] = $suc;
        
            Db::query('UPDATE `participants` SET '.
                '`cpt_player_id` = "'.(int)$players[1]['id'].'" '.
                'WHERE `id` = '.(int)$_SESSION['participant']->id.' AND '.
                '`game` = "lol" AND '.
                '`tournament_id` = '.(int)$this->data->settings['lol-current-number-'.$server].' '
            );
            
            Db::query('DELETE FROM `players` '.
                'WHERE `participant_id` = '.(int)$_SESSION['participant']->id.' AND '.
                '`game` = "lol" AND '.
                '`tournament_id` = '.(int)$this->data->settings['lol-current-number-'.$server].' '
            );
            
            foreach($players as $k => $v) {
                Db::query(
                    'INSERT INTO `players` SET '.
                    ' `game` = "lol", '.
                    ' `tournament_id` = '.(int)$this->data->settings['lol-current-number-'.$server].', '.
                    ' `participant_id` = '.(int)$_SESSION['participant']->id.', '.
                    ' `name` = "'.Db::escape($v['name']).'", '.
                    ' `player_num` = "'.(int)$k.'", '.
                    ' `player_id` = "'.(int)$v['id'].'"'
                );
            }

            if ($addStream == 1) {
                //On duplicate won't work here, not many uniques keys in the table
                Db::query(
                    'DELETE FROM `streams` '.
                    'WHERE `participant_id`  = '.(int)$_SESSION['participant']->id.' '.
                    'AND `game` = "lol" '
                );

                Db::query(
                    'INSERT INTO `streams` SET '.
                    '`user_id`  = '.(int)$this->data->user->id.', '.
                    '`participant_id`  = '.(int)$_SESSION['participant']->id.', '.
                    '`tournament_id`  = '.(int)$this->data->settings['lol-current-number-'.$server].', '.
                    '`name` = "'.Db::escape($post['stream']).'", '.
                    '`game` = "lol" '
                );
            }
        }
         
        return json_encode($answer);
    }
}